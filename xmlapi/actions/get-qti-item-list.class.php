<?php
/**
 * 取得三合一題目
 *
 * return code
 * 2: 使用者在此課程沒有教師、講師、助教權限
 * 3: type 須符合 qti
 * 4: 沒設定課程編號或不符合規則的課程編號
 * 5: 指定題目題型不符合規則
 *
 */
include_once(dirname(__FILE__) . '/action.class.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(PATH_LIB . 'qti.php'); // 解析 item 用

class GetQtiItemListAction extends baseAction
{
    var $_mysqlUsername = '',
        $_qtiTypes = array('exam', 'questionnaire'),
        // 1: 是非；2: 單選；3: 多選；4: 填充；5: 簡答/申論；6: 配合
        $_itemTypes = array(1, 2, 3, 4, 5, 6),
        $_qtiType,
        $_qtiTable,
        $_itemType,
        $_courseId,
        $_offset,
        $_pageSize,
        $_keyword;
        // exam 權限須更嚴謹，以防洩題問題，先實作取得 questionnaire 題庫
        // $_qtiType = array('exam', 'homework', 'questionnaire');

    function dataHandler ($data) {
        // type 須符合 qti
        if (!in_array($data['type'], $this->_qtiTypes)) {
            $this->returnHandler(3, 'fail');
        }

        // appache 會自動解 urlencode ，故前端 encodeURIComponent 加密兩次再傳進來
        $cid = rawurldecode($data['cid']);

        // 解密
        $aesCode = intval($data['aesCode']);
        if ($aesCode > 0) {
            $cid = intval(APPEncrypt::decrypt($cid, $aesCode));
        }

        // 沒設定課程編號或不符合規則的課程編號
        if (!isset($cid) || !($cid > 10000000 && $cid < 99999999)) {
            $this->returnHandler(4, 'fail');
        }

        // 指定題型，可指定多個類型
        if (isset($data['itemType'])) {
            if (is_string($data['itemType'])) {
                // 相容舊規格，如果是單一字串就轉為陣列
                $data['itemType'] = array($data['itemType']);
            }
            foreach($data['itemType'] AS $itemType) {
                if (!in_array(intval($itemType), $this->_itemTypes)) {
                    $this->returnHandler(5, 'fail');
                }
            }
        }

        $this->_qtiType = $data['type'];
        $this->_qtiTable = sprintf('`WM_qti_%s_item`', $this->_qtiType);
        $this->_itemType = (isset($data['itemType'])) ? $data['itemType'] : array();
        $this->_courseId = $cid;
        $this->_offset = isset($data['offset']) ? intval($data['offset']) : 0;
        $this->_pageSize = isset($data['size']) ? intval($data['size']) : 15;
        $this->_keyword = (isset($data['keyword']) && $data['keyword'] !== '') ? trim($data['keyword']) : '';
    }
    function aclCheck ($username) {
        global $sysRoles, $sysSession;

        // 確認使用權限
        $aclCheck = aclCheckRole($username, $sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant'], $this->_courseId);
        if (!$aclCheck) {
            $this->returnHandler(2, 'fail');
        }
        // 有權限則進入課程
        $sysSession->course_id = $this->_courseId;
        $sysSession->restore();
    }
    function getItemList () {
        global $sysConn;
        $items = array();
        $cond = array();

        // Select 條件
        if ($this->_keyword !== '') {
            $cond[] = sprintf("locate('%s', `content`)", mysql_real_escape_string($this->_keyword));
        }

        // 指定題型的條件
        if (count($this->_itemType) !== 0) {
            $cond[] = sprintf("`type` IN (%s)", implode(', ', $this->_itemType));
        }

        $cond[] = sprintf('`course_id` = %d', $this->_courseId);

        $itemRs = dbGetStMr(
            $this->_qtiTable,
            'SQL_CALC_FOUND_ROWS `ident`, `content`',
            sprintf(" %s ", implode(" AND ", $cond)) .
            sprintf(' ORDER BY `ident` DESC Limit %d, %d ', $this->_offset, $this->_pageSize)
        );
        $itemTotalSize = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));

        if ($itemRs && $itemTotalSize > 0) {
            while ($row = $itemRs->FetchRow()) {
                $content = str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $row['content']);
                if ($dom = domxml_open_mem($content)) {
                    $ctx = xpath_new_context($dom);
                    // 將 item 轉換成 JSON 格式
                    $qti = new Qti();
                    $needAnswer = ($this->_qtiType === 'exam');
                    $unitItems = $qti->transformer($dom, $ctx, $this->_qtiType);
                    $unitItem = $unitItems[0];
                } else {
                    // xml parse 失敗
                    $unitItem = array(
                        "item_id" => $row['ident'],
                        "errMsg" => "Xml parse error!!"
                    );
                }
                $items[] = $unitItem;
            }
        }

        return array(
            'total' => $itemTotalSize,
            'data' => $items
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

        // url 參數處理
        $inputData = $_GET;
        $this->dataHandler($inputData);
        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);

        // 確認使用權限
        $this->aclCheck($this->_mysqlUsername);

        // 取得該課題目列表
        $itemData = $this->getItemList();

        $data = array(
            'total_size' => $itemData['total'],
            'list' => $itemData['data']
        );

        $this->returnHandler($code, $message, $data);
    }
}