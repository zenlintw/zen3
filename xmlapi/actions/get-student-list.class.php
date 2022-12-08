<?php
/**
 * 取得課程學生名單
 *
 * 回傳值
 * 2: 沒有權限
 * 3: 沒設定課程編號或不符合規則的課程編號
 */
include_once(dirname(__FILE__) . '/action.class.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

class GetStudentListAction extends baseAction
{
    var $_mysqlUsername = '',
        $_courseId,
        $_offset,
        $_pageSize,
        $_keyword;

    function dataHandler ($inputData) {
        $aesCode = intval($inputData['aesCode']);
        
        // appache 會自動解 urlencode ，故前端 encodeURIComponent 加密兩次再傳進來
        $apacheVersion = getApacheVersion();
        if (strpos($apacheVersion, '2.4') !== false) {
            // apache2.4 已不會自動 decode，需做兩次解密
            $cid = rawurldecode(rawurldecode($inputData['cid']));
        } else {
            $cid = rawurldecode($inputData['cid']);
        }
        if ($aesCode > 0) {
            $cid = APPEncrypt::decrypt($cid, $aesCode);
        }

        // 沒有設定 cid 或不符合規則的 cid
        $cid = intval($cid);
        if (!isset($inputData['cid']) || !($cid > 10000000 && $cid <= 99999999) ) {
            $this->returnHandler(3, 'fail');
        }

        $this->_courseId = $cid;
        $this->_offset = isset($inputData['offset']) ? intval($inputData['offset']) : 0;
        $this->_pageSize = isset($inputData['size']) ? intval($inputData['size']) : 0;
        $this->_keyword = (isset($inputData['keyword']) && $inputData['keyword'] !== '') ? trim($inputData['keyword']) : '';

    }
    function aclCheck ($username) {
        global $sysRoles;

        // 確認使用權限
        $aclCheck = aclCheckRole($username, $sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant'], $this->_courseId);
        if (!$aclCheck) {
            $this->returnHandler(2, 'fail');
        }
    }
    function getStudentData () {
        global $sysConn, $sysRoles;
        $students = array();
        $keywordCond = '';
        $sizeCond = '';

        // 變數宣告
        $aesCode = APPEncrypt::makeAesCode();   // 產生一個 aesCode

        if ($this->_keyword !== '') {
            $keywordCond = sprintf(
                " (LOCATE('%s', `first_name`) OR LOCATE('%s', `last_name`)) AND ",
                mysql_real_escape_string($this->_keyword),
                mysql_real_escape_string($this->_keyword)
            );
        }
        if ($this->_offset !== 0 && $this->_pageSize !== 0) {
            $sizeCond = sprintf(' Limit %d, %d ', $this->_offset, $this->_pageSize);
        }
        $studentRs = dbGetStMr(
            '`WM_term_major` AS TM LEFT JOIN `WM_user_account` AS UA ON TM.`username` = UA.`username` ',
            'SQL_CALC_FOUND_ROWS TM.`username`, UA.`first_name`, UA.`last_name`',
            sprintf(
                ' %s TM.`course_id` = %d AND TM.role&%d %s ',
                $keywordCond,
                $this->_courseId,
                $sysRoles['student'],   // 先不取旁聽生，避免與作答對象預設正式生衝突 | $sysRoles['auditor'],
                $sizeCond
            )
        );
        $rsTotalSize = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));

        if ($studentRs) {
            while ($row = $studentRs->FetchRow()) {
                $realName = checkRealname($row['first_name'], $row['last_name']);
                $aesUsername = APPEncrypt::encrypt(base64_encode($row['username']), $aesCode);
                $aesRealName = ($realName !== "") ? APPEncrypt::encrypt(base64_encode($realName), $aesCode) : $aesUsername;
                $students[] = array(
                    'username' => $aesUsername,
                    'realname' => $aesRealName
                );
            }
        }
        return array(
            'total' => $rsTotalSize,
            'aesCode' => $aesCode,
            'list' => $students
        );
    }
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;

        // url 參數處理
        $inputData = $_GET;
        $this->dataHandler($inputData);
        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);

        // 確認權限
        $this->aclCheck($this->_mysqlUsername);

        // 取得該課學生列表
        $studentData = $this->getStudentData();

        $this->returnHandler(0, 'success', $studentData);
    }
}