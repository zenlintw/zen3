<?php
/**
 * 提供 Google 驗證
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 */
require_once(sysDocumentRoot . '/config/login.config');
require_once(sysDocumentRoot . '/lib/login/login.inc');
require_once(sysDocumentRoot . '/lib/username.php'); // 新增平台帳號用
require_once(sysDocumentRoot . '/lib/google/src/Google_Client.php');

class AccountCenter_Google
{
    const ACCOUNT_PREFIX = 'gp_';
    const REDIRECT_URL = '/mooc/google_login.php';

    var $client;

    /**
     * 建構子
     * @param $createClient {boolean} 是否直接建立 google client
     */
	function __construct($createClient = true)
	{
        if ($createClient) {
            $this->createGoogleClient();
        }
    }

    /**
     * 建立 google client 供存取 google api
     *
     * @param array $customConfig
     * @return object google client
     */
    function createGoogleClient ($customConfig) {
        global $sysSession;

        // 取得平台 google 設定
        $schoolInfo = dbGetRow(
            '`CO_school`',
            '`canReg_google_id`, `canReg_google_secret`',
            sprintf(
                "`WM_school`.school_id= %d AND school_host = '%s'",
                $sysSession->school_id,
                $_SERVER['HTTP_HOST']
            )
        );
        $config = array(
            'application_name' => '',
            'oauth2_client_id' => $schoolInfo['canReg_google_id'],	
            'oauth2_client_secret' => $schoolInfo['canReg_google_secret'],	
            'oauth2_redirect_uri' => 'https://' . $_SERVER['SERVER_NAME'] . self::REDIRECT_URL
        );

        if (isset($customConfig)) {
            $config = array_merge($config, $customConfig);
        }

        // 建立驗證連線
        $this->client =  new Google_Client($config);
    }

    /**
     * 透過 id token 取得使用者在 google 的個人資訊
     *
     * @param string $idToken
     * @return mixed google 個人資訊 (沒抓到資訊的話回傳 false)
     */
    function getPayloadByIdToken($idToken) {
        try {
            $loginTicket = $this->client->verifyIdToken($idToken);
            if ($loginTicket) {
                $data = $loginTicket->getAttributes();
                return $data['payload'];
            }
        } catch (Exception $e) {
            // TODO: 紀錄登入失敗的 log
            // var_dump($e->getMessage());
            return false;
        }
        return false;
    }

    /**
     * 確認 google id 在平台上是否已註冊
     * @param $googlePlusId {String} 帳號
     * @return boolean 驗證結果 (true:可用；false：不可用)
     */
    function isRegisterByGoogleId($googleId) {
        return 0 < dbGetOne(
            '`CO_google_account`',
            'COUNT(`username`)',
            sprintf('`id` = "%s"', mysql_real_escape_string($googleId))
        );
    }

    /**
     * 取得 google id 在平台上對應的 username
     *
     * @param string $googleId Google id
     * @return mixed 有資料回傳 username 沒資料回傳 false
     */
    function getUsernameByGoogleId($googleId) {
        return dbGetOne(
            '`CO_google_account`',
            '`username`',
            sprintf('`id` = "%s"', mysql_real_escape_string($googleId))
        );
    }

    /**
     * 取得 username 在平台上對應的 google id
     *
     * @param string $username 帳號
     * @return mixed 有資料回傳 google id 沒資料回傳 false
     */
    function getGoogleIdByUsername($username) {
        return dbGetOne(
            '`CO_google_account`',
            '`id`',
            sprintf('`username` = "%s"', mysql_real_escape_string($username))
        );
    }

    /**
     * 建立 google id 平台對應帳號
     *
     * @param array $payload google 使用者資訊 form client->verifyIdToken
     * @param array $customColumn 其他使用者資訊
     * @return void
     */
    function addGoogleAccount($payload, $customColumn = array()) {
        $googleId = trim($payload['sub']);
        // 給予帳號前綴
        $username = self::ACCOUNT_PREFIX . $googleId;

        $data = array(
            'first_name' => $payload['name'],
            'email'      => $payload['email']
            // TODO: 可將使用者大頭照 (picture) 撈回使用
        );

        // 合併客製欄位設定
        if (isset($customColumn)) {
            $data = array_merge($data, $customColumn);
        }

        // 在平台上加入帳號
        addUser($username, $data, 'Y');

        // 建立關聯
        $this->addUsernameAssociation($googleId, $username);
    }

    /**
     * 建立 google 與平台帳號的關聯
     *
     * @param string $googleId
     * @param string $username
     * @return void
     */
    function addUsernameAssociation($googleId, $username) {
        // 建立 Username 與 Google id 的關聯
        dbNew(
            '`CO_google_account`',
            '`id`, `username`',
            sprintf(
                '"%s", "%s"',
                mysql_real_escape_string($googleId),
                mysql_real_escape_string($username)
            )
        );
    }

    /**
     * 透過 google id 取得平台使用者資訊
     *
     * @param string $googleId
     * @return mixed 使用者資訊 (or false) 
     */
    function getUserInfoByGoogleId($googleId) {
        // 取得帳號
        $username = $this->getUsernameByGoogleId($googleId);

        // 確認是否平台已有帳號
        if ($username) {
            // 撈取使用者資訊
            $username = mysql_real_escape_string($username);
            return dbGetStSr(
                '`WM_user_account`',
                '*',
                sprintf('`username` = "%s"', $username)
            );
        } else {
            return false;
        }
    }
}