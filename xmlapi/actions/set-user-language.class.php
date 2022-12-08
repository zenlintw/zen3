<?php
/**
 * 設定使用者語系
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');

class SetUserLanguageAction extends baseAction
{
    var $username = null;
    var $language = array('Zhtw' => 'Big5', 'Zhcn' => 'GB2312', 'Enus' => 'en');
    
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;

        $code = 0;
        $message = 'success';

        $this->username = mysql_real_escape_string($sysSession->username);

        // 從網址取得參數
        $getLanguage = trim($_GET['language']);
        if (!array_key_exists($getLanguage, $this->language)) {
            $code = 2;
            $message = 'fail';
        }

        $userLanguage = $this->language[$getLanguage];
        $sysSession->lang = $userLanguage;
        $sysSession->restore();

        dbSet('WM_user_account', "language = '{$userLanguage}'", "username = '{$this->username}'");
        dbSet('WM_all_account', "language = '{$userLanguage}'", "username = '{$this->username}'");

        appSysLog(999999019, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], 'Change Language:' . $userLanguage, $this->username);
        
        // make json
        $jsonObj = array(
            'code' => $code,
            'message' => $message,
            'data' => array()
        );

        $jsonEncode = JsonUtility::encode($jsonObj);
        
        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}