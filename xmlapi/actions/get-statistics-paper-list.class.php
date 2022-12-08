<?php
/**
 * 取得課程三合一試卷
 * code:
 *  2. 沒有權限
 *  3. 試卷類型錯誤
 *  4. 課程編號錯誤
 *  5. xml parse 錯誤
 *  6. 發布狀態錯誤
 */
include_once(dirname(__FILE__) . '/action.class.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

class GetStatisticsPaperListAction extends baseAction
{
    // all 用來取測驗、問卷混合資料，homework 先暫不實作
    var $_qtiTypes = array('exam', 'questionnaire', 'all');
    var $_qtiPublishSet = array('action', 'prepare', 'close');

    var $_errorCode = 0,
        $_errorMsg = '',
        $_mysqlUsername = '',
        $_qtiType,
        $_courseId,
        $_offset,
        $_pageSize,
        $_keyword,
        $_forIRS,
        $_qtiPublish;

    function dataHandler ($data) {
        global $sysSession;

        $apacheVersion = getApacheVersion();
        if (strpos($apacheVersion, '2.4') !== false) {
            // apache2.4 已不會自動 decode，需做兩次解密
            $data['cid'] = rawurldecode(rawurldecode($data['cid']));
        } else {
            $data['cid'] = rawurldecode($data['cid']);
        }

        // 資料解密
        $aesCode = intval($data['aesCode']);
        if ($aesCode > 0) {
            $data['cid'] = APPEncrypt::decrypt(trim($data['cid']), $aesCode);
        }
        $data['cid'] = intval($data['cid']);

        // type 須符合 qti
        if (isset($data['type']) && !in_array($data['type'], $this->_qtiTypes)) {
            $this->_errorCode = 3;
            return false;
        }

        // 沒有設定 cid 或不符合規則的 cid
        if (!($data['cid'] > 10000000 && $data['cid'] <= 99999999) ) {
            $this->_errorCode = 4;
            return false;
        }

        // 驗證過濾發布狀態
        if (isset($data['publish']) && !in_array($data['publish'], $this->_qtiPublishSet)) {
            $this->_errorCode = 6;
            return false;
        }

        $this->_qtiType = isset($data['type']) ? $data['type'] : 'all';
        $this->_courseId = isset($data['cid']) ? $data['cid'] : 10000000;
        $this->_offset = isset($data['offset']) ? intval($data['offset']) : 0;
        $this->_pageSize = isset($data['size']) ? intval($data['size']) : 15;
        $this->_keyword = (isset($data['keyword']) && $data['keyword'] !== '') ? trim($data['keyword']) : '';
        $this->_forIRS = (isset($data['forIRS']) && $data['forIRS'] === '1') ? true : false;
        $this->_qtiPublish = isset($data['publish']) ? $data['publish'] : '';

        return true;
    }

    function aclCheck ($username, $courseId) {
        global $sysRoles;

        // 確認使用權限
        $aclCheck = aclCheckRole(
            $username,
            $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'],
            $courseId
        );
        if (!$aclCheck) {
            $this->_errorCode = 2;
            return false;
        }

        return true;
    }

    function getTestList($courseId, $qtiType, $conditions = array()) {
        global $sysConn, $sysSession;

        // 變數宣告
        $tests = array();
        $aesCode = APPEncrypt::makeAesCode();   // 產生一個 aesCode
        $unionTables = array();

        // query 條件
        $conditions[] = sprintf('ET.`course_id` = %d', $courseId);
        $where = implode(' AND ', $conditions);

        foreach ($this->_qtiTypes AS $type) {
            if ($type === 'all' ||
                    ($qtiType !== 'all' && $qtiType !== $type)) {
                continue;
            }

            $unionTables[] = sprintf(
                "SELECT
                    '{$type}' as qtiType,
                    ET.`exam_id`, ET.`title`, ET.`begin_time`, ET.`close_time`,
                    ET.`content`, ET.`type`, ET.`publish`,
                    MAX(ER.`score`) AS `highest`, AVG(ER.`score`) AS `average`,
                    MIN(ER.`score`) AS `lowest`, COUNT(ER.`examinee`) AS `count`
                FROM WM_qti_{$type}_test AS ET
                    LEFT JOIN `WM_qti_{$type}_result` ER
                        ON ET.`exam_id` = ER.`exam_id` AND ER.`status` != 'break' 
                WHERE  %s 
                GROUP BY `exam_id`",
                $where
            );
        }

        $unionSql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS * FROM (%s) AS UT
            ORDER BY
                IF(UT.`publish` = 'action', 0, 1),
                IF(UT.`close_time` = '9999-12-31 00:00:00' AND UT.`publish` = 'close', 1, 0),
                (CASE
                    WHEN UT.`publish` = 'prepare' THEN UT.`begin_time`
                    WHEN UT.`publish` = 'close' THEN UT.`close_time`
                END) DESC
            LIMIT %d, %d",
            implode(" UNION ", $unionTables),
            $this->_offset,
            $this->_pageSize
        );

        $testRs = $sysConn->Execute($unionSql);

        $itemTotalSize = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));
        if ($testRs) {
            while ($row = $testRs->FetchRow()) {
                // 取得試卷標題
                $titleAry = getCaption($row['title']);
                $title = $titleAry[$sysSession->lang];
                $itemCount = 0;
                // TODO: 取得試卷題目數(試卷由第一個學生試卷結果取得) PRO 會有修改試卷的問題，IRS通常為一次性的試卷直接取原試卷題目
                if ($row['content'] !== null && $row['content'] !== "" ) {
                    if (!$dom = domxml_open_mem(str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $row['content']))) {
                        $this->_errorCode = 5;
                        $this->_errorMsg = "Xml parser error: " . $row['exam_id'];
                        return false;
                    }
                    $ctx = xpath_new_context($dom);
                    $items = $ctx->xpath_eval("//questestinterop/item");
                    $itemCount = count($items->nodeset);
                }

                $tests[] = array(
                    'exam_id' => ($row['exam_id'] !== "") ? APPEncrypt::encrypt(base64_encode($row['exam_id']), $aesCode) : "",
                    'title' => $title,
                    // type 為 all 時，回傳資料庫取得的值
                    'type' => (isset($row['qtiType'])) ? $row['qtiType'] : $qtiType,
                    'paper_type' => intval($row['type']),
                    'duration' => $row['begin_time'] . "~" . $row['close_time'],
                    'status' => $row['publish'],
                    'statistics' => array(
                        'records' => intval($row['count']),
                        'quizzes' => $itemCount,
                        'highest_score' => round(floatval($row['highest']), 2),
                        'average_score' => round(floatval($row['average']), 2),
                        'lowest_score' => round(floatval($row['lowest']), 2)
                    )
                );
            }
        }
        return array(
            'total' => $itemTotalSize,
            'aesCode' => $aesCode,
            'list' => $tests
        );
    }

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        global $sysSession;

        // 變數宣告
        $code = 0;
        $message = 'success';
        $cond = array();

        // url 參數處理
        $inputData = $_GET;
        if (!$this->dataHandler($inputData)) {
            $this->returnHandler($this->_errorCode, 'fail');
        }

        // 確認使用權限
        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);
        if (!$this->aclCheck($this->_mysqlUsername, $this->_courseId)) {
            $this->returnHandler($this->_errorCode, 'fail');
        } else {
            // 有權限則進入課程
            $sysSession->course_id = $this->_courseId;
            $sysSession->restore();
        }

        // query 條件
        if ($this->_forIRS === true) {
            $cond[] = " `type` = 5 ";
        }
        if ($this->_keyword !== '') {
            $cond[] = sprintf("LOCATE('%s', `title`)", mysql_real_escape_string($this->_keyword));
        }
        if ($this->_qtiPublish !== '') {
            $cond[] = sprintf("`publish` = '%s'", mysql_real_escape_string($this->_qtiPublish));
        }

        // 取得該課試卷列表
        $testData = $this->getTestList($this->_courseId, $this->_qtiType, $cond);
        if ($testData === false) {
            // xml parse 錯誤
            $this->returnHandler($this->_errorCode, 'fail', array("errMsg" => $this->_errorMsg));
        }

        $this->returnHandler($code, $message, $testData);
    }
}