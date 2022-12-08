<?php
/**
 * 取得檔案
 */
include_once(dirname(__FILE__).'/action.class.php');

class GetAttachmentAction extends baseAction
{
    var $_errorMsg = '';

    // 資料處理及驗證
    function dataHandler (&$data) {
        $data['url'] = trim($data['url']);

        // 避免使用 ../ 來取得其他父層資料夾的資訊
        if (strpos($data['url'], '/../') !== false) {
            $this->_errorMsg = 'Param url error!';
            return false;
        }

        // 確認網址參數
        $pattern = '/^\/base\/(\d{5})\/course\/(\d{8})\/content\//';
        preg_match($pattern, $data['url'], $matches);
        if (!$matches) {
            $this->_errorMsg = 'Param url error!';
            return false;
        }

        $schoolId = intval($matches[1]);
        $courseId = intval($matches[2]);

        // 沒有設定 sid 或不符合規則的 sid
        if (!($schoolId > 10000 && $schoolId <= 99999) ) {
            $this->_errorMsg = 'Param school_id error!';
            return false;
        }

        // 沒有設定 cid 或不符合規則的 cid
        $courseId = intval($courseId);
        if (!($courseId > 10000000 && $courseId <= 99999999) ) {
            $this->_errorMsg = 'Param course_id error!';
            return false;
        }
        
        $data['school_id'] = $schoolId;
        $data['course_id'] = $courseId;

        return true;
    }

    /**
     * 驗證使用者使用權限
     * @param $username
     */
    function aclCheck($username, $courseId) {
        global $sysRoles;

        // 確認使用權限
        $roles = $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'];
        $aclCheck = aclCheckRole($username, $roles, $courseId);
        if (!$aclCheck) {
            $this->_errorMsg = 'Access denied.';
            return false;
        }

        return true;
    }

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;

        // url 參數處理
        $inputData = $_GET;
        if (!$this->dataHandler($inputData)) {
            $this->returnHandler(3, 'fail', array('errorMsg' => $this->_errorMsg));
        }

        $username = mysql_real_escape_string($sysSession->username);
        $schoolId = $inputData['school_id'];
        $courseId = $inputData['course_id'];
        $file = sysDocumentRoot . trim($inputData['url']);

        // 確認學校編號
        if (intval($sysSession->school_id) !== $schoolId) {
            $this->returnHandler(4, 'fail', array('errorMsg' => 'Access denied.'));
        }

        // 確認使用權限
        if (!$this->aclCheck($username, $courseId)) {
            $this->returnHandler(2, 'fail', array('errorMsg' => $this->_errorMsg));
        }

        // 確認檔案是否存在
        if (!file_exists($file)) {
            $this->returnHandler(5, 'fail', array('errorMsg' => 'File not found.'));
        }

        set_time_limit(0);
        ignore_user_abort(false);
        ini_set('output_buffering', 0);
        ini_set('zlib.output_compression', 0);

        $chunk = 1 * 1024 * 1024; // bytes per chunk (10 MB)

        $handle = fopen($file, "rb");

        if ($handle === false) {
            $this->returnHandler(5, 'fail', array('errorMsg' => 'Unable open file.'));
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . getFileMimeType($file));
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));

        header('Content-Transfer-Encoding: binary');
        header('Content-Type: application/octet-stream;');

        // Repeat reading until EOF
        while (!feof($handle)) {
            echo fread($handle, $chunk);

            ob_flush();  // flush output
            flush();
        }
    }
}