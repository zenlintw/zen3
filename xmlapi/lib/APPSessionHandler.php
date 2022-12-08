<?php
class APPSessionHandler {
    // device傳過來的資料
    var $_deviceUsername;
    var $_deviceIDX;

    // WM_session的資料
    var $_sessionIDX;
    var $_sessionRealName;
    var $_sessionUsername;
    var $_sessionLanguage;

    // MySQL 用在 where 條件的資料
    var $_mysqlUsername;
    var $_mysqlIDX;

    var $_skipUsers = array('root', 'sunnet', 'suntest');

    /**
     * 驗證資料表(WM_session)是否找得到相同的session
     *
     * @return integer 0:沒有任何session存在；1:符合當下session；2:不符合當下session(有其他裝置登入了)；3: 帳號不存在
     **/
    function isSessionExist () {
        if (!$this->isUsernameExist()) {
            return 3;
        }
        // 取得平台重複登入的設定
        $table = '`WM_school`';
        $field = '`multi_login`';
        $where = "`school_host` = '{$_SERVER['HTTP_HOST']}'";
        $multiLoginSetting = dbGetOne($table, $field, $where);

        chkSchoolId('WM_session');
        // 取得這個帳號的session
        $table = '`WM_session`';
        $field = '`idx`, `realname`';
        $where = "`username` = '{$this->_mysqlUsername}'";
        $sessionRS = dbGetStMr($table, $field, $where);

        $sessions = array();
        if ($sessionRS) {
            while ($session = $sessionRS->FetchRow()) {
                $sessions[] = $session['idx'];
		$this->_sessionRealName = $session['realname'];
            }
        }

        if (COUNT($sessions) === 0) {
            // 此帳號沒有任何session
            return 0;
        } else if (in_array($this->_deviceIDX, $sessions)) {
            // 與裝置session相同，順便將chance歸零
	    $this->_sessionIDX = $this->_deviceIDX; 
            dbSet($table, '`chance` = 0', "`idx` = '{$this->_deviceIDX}'");
            return 1;
        } else {
            // 有其他裝置登入的session

            if (in_array($this->_deviceUsername, $this->_skipUsers) || $multiLoginSetting == 'Y') {
                // 平台允許重複登入 或是 允許重複登入的帳號直接回傳0
                return 0;
            }

            return 2;
        }
    }

    /**
     * 踢掉session
     *
     * @return boolean 是否確實已移除
     **/
    function kickOff () {
        global $sysConn;

        $table = '`WM_session`';
        $where = "`username` = '{$this->_deviceUsername}'";
        dbDel($table, $where);

        // 根據影響的列數判定是否確實移除
        return ($sysConn->Affected_Rows() > 0);
    }

    /**
     * 透過帳號建立session
     *
     * @param string $username
     * @return string session idx
     **/
    function create ($username) {
        global $sysSession, $_COOKIE;

        $username = mysql_real_escape_string($username);
        $sessionIDX = '';

        $table = '`WM_all_account`';
        $field = '*';
        $where = "`username` = '{$username}'";

        $userData = dbGetStSr($table, $field, $where);
        if ($userData) {
            $this->_sessionRealName = $userData['first_name'];
            $this->_sessionLanguage = $userData['language'];
            $sessionIDX = $sysSession->init($userData);

            // 建立完畢後移除guest session
            $guestCookieIdx = mysql_real_escape_string($_COOKIE['idx']);
            dbDel('`WM_session`', "`idx` = '{$guestCookieIdx}'");
        }

        return $sessionIDX;
    }

    function isUsernameExist () {
        // 帳號在WM_all_account
        $table = '`WM_all_account`';
        $field = '`username`';
        $where = "`username` = '{$this->_mysqlUsername}'";

        $this->_sessionUsername = dbGetOne($table, $field, $where);

        return ($this->_sessionUsername !== '');
    }

    /**
     * 設定需要的參數資料，包含之後資料庫會用到的(避免SQL Injection)
     *
     * @param string $idx session idx
     * @param string $username 加密的帳號
     **/
    function setSessionData ($idx, $username) {
        $this->_deviceIDX = trim($idx);
        $this->_mysqlIDX = mysql_real_escape_string($this->_deviceIDX);

        $this->_deviceUsername = $username;
        $this->_mysqlUsername = mysql_real_escape_string($this->_deviceUsername);
    }

    /**
     * 分析session狀態並回傳相對應訊息
     *
     * @param string $idx session idx
     * @param string $username 加密的帳號
     * @param boolean $kickOff 是否需要踢掉前者
     *
     * @return array 相對應的資訊
     **/
    function mainHandler ($idx, $username, $kickOff) {
        $message = '';

        // 設定所需要的資料
        $this->setSessionData($idx, $username);

        if ($kickOff) {
            // 要踢掉前者
            $this->kickOff();
        }

        $sessionExistCode = $this->isSessionExist();
        switch ($sessionExistCode) {
            // 此帳號沒有任何session存在
            case 0:
                $message = 'create new session';
                // 直接建立session
                $this->_sessionIDX = $this->create($this->_deviceUsername);
                break;
            // 此帳號與session符合當下裝置
            case 1:
                $message = 'session exist';
                break;
            // 此帳號已有其他裝置登入
            case 2:
                $message = 'other session';
                break;
            case 3:
                $message = 'no such user';
                break;
        }

        $data = array(
            'session_idx' => $this->_sessionIDX,    // 回傳WM_session的idx
            'session_username' => encryptImmediately($this->_deviceUsername),   // 重新加密
            'session_realname' => ($this->_sessionRealName === '') ? encryptImmediately($this->_deviceUsername) : encryptImmediately($this->_sessionRealName),
            'session_language' => $this->_sessionLanguage
        );

        return array(
            'code' => $sessionExistCode,
            'message' => $message,
            'data' => $data
        );
    }
}