<?php
/**
 * 取得平台設定
 * 3: 不在允許取得的設定名單內
 * 4: 程式錯誤->未設定值
 */
include_once(dirname(__FILE__).'/action.class.php');

class GetServerSettingsAction extends baseAction
{
    var $_validWhiteList = array("multi-login-functions");
    var $_allowSettings = array(
        "irs-socket-url" => WM_IRS_SOCKET_URL,
        "multi-login-functions" => "",
    );

    /**
     * @param $prop
     * @return mixed|string
     */
    function getSetting ($prop) {
        switch ($prop) {
        case "multi-login-functions":
            // 取得 FB 設定
            $fbSettings = $this->getFBSettings();
            $fbAuthUrl = sprintf(
                'https://www.facebook.com/dialog/oauth?client_id=%s&redirect_uri=%s&scope=email',
                $fbSettings['canReg_fb_id'],
                sprintf('https://%s/mooc/fb_login.php', $_SERVER['SERVER_NAME'])
            );

            // TODO: 確定多元登入設定位置，目前先寫死程式內
            return json_encode(array(
                // array(
                //     'text' => 'Google+',
                //     'type' => 'google',
                //     'color' => '.google',
                //     'url' => ''
                // ),
                // array(
                //     'text' => 'Facebook',
                //     'type' => 'web',
                //     'color' => '.facebook',
                //     'url' => $fbAuthUrl
                // ),
                // array(
                //     'text' => 'ECPA',
                //     'type' => 'web',
                //     'color' => '#FF0000',
                //     'url' => 'https://ecpa.dgpa.gov.tw/uIAM/clogin.asp?destid=CrossHRD'
                // ),
                // array(
                //     'text' => '我的e政府',
                //     'type' => 'web',
                //     'color' => '.line',
                //     'url' => 'https://www.cp.gov.tw/portal/Clogin.aspx?ReturnUrl=https://elearn.hrd.gov.tw/egov_login.php'
                // ),
                // array(
                //     'text' => 'QRCode登入',
                //     'type' => 'qrcode',
                //     'color' => '#00FF00',
                //     'url' => ''
                // )
            ));
        default:
            // 檢查程式設定是否正確
            if (!isset($this->_allowSettings[$prop])) {
                $this->returnHandler(4, 'fail');
            }
            return $this->_allowSettings[$prop];
        }
    }

    function getFBSettings () {
        global $sysSession;

        return dbGetStSr(
            '`CO_school`',
            '`canReg_fb_id`, `canReg_fb_secret`',
            sprintf('`school_id` = %d', intval($sysSession->school_id)),
            ADODB_FETCH_ASSOC
        );
    }

    function dataHandler (&$data) {
        $prop = $data["prop"];

        // 檢查 prop 是否為允許取得的設定
        if (!array_key_exists($prop, $this->_allowSettings)) {
            $this->returnHandler(3, 'fail');
        }
    }

    /**
     * 驗證使用者使用權限
     */
    function aclCheck () {}

    function main()
    {
        // 從 GET 取得參數
        $inputData = $_GET;

        // 資料處理
        $this->dataHandler($inputData);

        // 根據項目來決定是否驗 ticket
        if (!in_array($inputData["prop"], $this->_validWhiteList)) {
            // 驗證 Ticket
            parent::checkTicket();
        }

        // 取得設定值
        $settingValue = $this->getSetting($inputData["prop"]);

        // 資料加密
        $APPEncrypt = new APPEncrypt();
        $aesCode = $APPEncrypt->makeAesCode();
        $settingValue = $APPEncrypt->encrypt(base64_encode(trim($settingValue)), $aesCode);

        // 回傳資訊
        $this->returnHandler(0, 'success', array(
            'ac' => $aesCode,
            "value" => $settingValue
        ));
    }
}