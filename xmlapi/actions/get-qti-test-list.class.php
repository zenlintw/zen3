<?php
/**
 * 取得課程三合一試卷
 */
include_once(dirname(__FILE__) . '/action.class.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

class GetQtiTestListAction extends baseAction
{
    var $_mysqlUsername = '',
        $_qtiTypes = array('questionnaire'),
        $_qtiType,
        $_qtiTable,
        $_courseId,
        $_offset,
        $_pageSize,
        $_keyword,
        $_forIRS;
        // exam 權限須更嚴謹，以防洩題問題，先實作取得 questionnaire 題庫
        // $_qtiType = array('exam', 'homework', 'questionnaire');

    function dataHandler ($type, $cid, $offset, $size, $keyword, $forIRS) {
        $this->_qtiType = in_array($type, $this->_qtiTypes) ? $type : 'questionnaire';
        $this->_qtiTable = sprintf('`WM_qti_%s_test`', $this->_qtiType);
        $this->_courseId = isset($cid) ? $cid : 10000000;
        $this->_offset = isset($offset) ? intval($offset) : 0;
        $this->_pageSize = isset($size) ? intval($size) : 15;
        $this->_keyword = (isset($keyword) && $keyword !== '') ? trim($keyword) : '';
        $this->_forIRS = (isset($forIRS) && $forIRS === '1') ? true : false;
    }
    function aclCheck ($username) {
        global $sysRoles;

        $code = 0;
        // 確認使用權限
        $aclCheck = aclCheckRole($username, $sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant'], $this->_courseId);
        if (!$aclCheck) {
            $code = 2;
        }
        return array('code' => $code);
    }
    function getTestList () {
        global $sysConn, $sysSession;
        $tests = array();
        $cond = array();

        // query 條件
        if ($this->_forIRS === true) {
            $cond[] = " `type` = 5 ";
        }
        if ($this->_keyword !== '') {
            $cond[] = sprintf("LOCATE('%s', `title`)", mysql_real_escape_string($this->_keyword));
        }
        // 取得該課試卷列表
        $testRs = dbGetStMr(
            $this->_qtiTable,
            'SQL_CALC_FOUND_ROWS `exam_id`, `title`, `begin_time`, `close_time`',
            (count($cond) > 0 ? implode(" AND ", $cond) . " AND " : "") .
            sprintf('`course_id` = "%s" ORDER BY `exam_id` DESC Limit %d, %d', $this->_courseId, $this->_offset, $this->_pageSize)
        );
        $itemTotalSize = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));

        if ($testRs) {
            while ($row = $testRs->FetchRow()) {
                $titleAry = getCaption($row['title']);
                $title = $titleAry[$sysSession->lang];
                $tests[] = array(
                    'test_id' => $row['exam_id'],
                    'title' => $title
                );
            }
        }
        return array(
            'total_size' => $itemTotalSize,
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

        // url 參數處理
        $inputData = $_GET;
        $this->dataHandler($inputData['type'], $inputData['cid'], $inputData['offset'], $inputData['size'], $inputData['keyword'], $inputData['forIRS']);
        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);

        // 確認使用權限
        $aclCheck = $this->aclCheck($this->_mysqlUsername);
        if ($aclCheck['code'] !== 0) {
            $this->returnHandler($aclCheck['code'], 'fail');
        }

        // 取得該課試卷列表
        $testData = $this->getTestList();

        $this->returnHandler($code, $message, $testData);
    }
}