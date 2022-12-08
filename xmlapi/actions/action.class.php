<?php
require_once(PATH_LIB . 'JsonUtility.php');
require_once(PATH_LIB . 'APPSessionHandler.php');

class baseAction
{
    var $validRules = array();
    
    var $userSessionId = '';

    var $statusList = array(
        '400' => 'HTTP/1.1 400 Bad Request',
        '401' => 'HTTP/1.1 401 Unauthorized',
        '404' => 'HTTP/1.1 404 Not Found'
    );

    var $_tokenEffectiveTime = 180;     // token有效時間預設3分鐘
    var $_whoGetFile = array('GoOgLe', 'WmPrO');
    
    function baseAction()
    {
        global $sysSession, $sysSiteNo;

        $_SERVER['REMOTE_ADDR'] = wmGetUserIp();

        // 不須驗證 ticket 的 api，先把 ticket 清空 initialize 才會建 Guest
        $jsonUtility = new JsonUtility();
        $inputData = file_get_contents('php://input');
        $postData = $jsonUtility->decode($inputData);

        if (!isset($postData['session_idx']) || !isset($postData['session_username'])) {
            $notAllowTicketActions = array('login', 'get-server-settings');
            if (in_array($_REQUEST['action'], $notAllowTicketActions)) {
                unset($_REQUEST['ticket']);
                unset($_COOKIE['idx']);
            }
        }

        // 產生 Session 實體
        $sysSession = new APPSessionInfo();
        $sysSiteNo = $sysSession->school_id + sysSiteUID;
        if (empty($sysSession->env)) $sysSession->env = 'app';    // add by lst : 預設使用學生環境

        // 抑制 proxy / IE 保留住舊網頁
        header('Pragma: no-cache');
        header('Cache-Control: no-cache, no-store, private, must-revalidate, post-check=0, pre-check=0');
        header('Expires: -1');
        header('Content-type: text/html;');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Allow-Origin: *');

        if (!isset($postData['session_idx']) || !isset($postData['session_username'])) {
            if (isset($_REQUEST['ticket'])) {
                $this->iniSessionData($_REQUEST['ticket']);
            } else if (isset($_COOKIE['idx'])) {
                $this->iniSessionData($_COOKIE['idx']);
            }
        }
    }
    
    function denyAccessHandler()
    {
        header('Content-Type: application/json');
        echo '{"code":1,"message":"Access denied(session not found)","data":{}}';
        exit();
    }

    function iniSessionData($idx)
    {
        if (isSessionIdExists($idx) !== FALSE) {
            $ticket = mysql_real_escape_string($idx);
            dbSet('WM_session', '`chance` = 0', "idx = '{$ticket}'");
        } else {
            $this->denyAccessHandler();
        }
    }

    // 驗證 Ticket
    function checkTicket()
    {
        $jsonUtility = new JsonUtility();
        $inputData = file_get_contents('php://input');
        $postData = $jsonUtility->decode($inputData);

        if (isset($postData['session_idx']) && isset($postData['session_username'])) {
            // 新的驗證機制：驗session idx與session username
            $sessionUsername = decryptImmediately(trim($postData['session_username']));
            $sessionHandler = new APPSessionHandler();
            $result = $sessionHandler->mainHandler($postData['session_idx'], $sessionUsername, $postData['kickOff']);
            $_COOKIE['idx'] = $result['data']['session_idx'];
            $_COOKIE['url'] = WM_SERVER_HOST;
            $response = array(
                'code' => $result['code'],
                'message' => $result['message'],
                'data' => array (
                    'cookie_data' => $_COOKIE,
                    'session_data' => array (
                        'ticket' => $result['data']['session_idx'],
                        'username' => $sessionUsername
                    ),
                    'idx_data' => $result['data']
                )
            );
            echo $jsonUtility->encode($response);

            if ($result['code'] === 2 || $result['code'] === 3) {
                // 此帳號已有其他裝置登入-2 | 查無此人-3
                exit();
            }
        } else {
            // 舊的驗證機制：拿URL的ticket參數驗證
            $idx = isset($_REQUEST['ticket'])? trim($_REQUEST['ticket']) : trim($_COOKIE['idx']);
            $sqlIdx = mysql_real_escape_string($idx);
            $session = dbGetOne('WM_session', 'count(*)', "idx='{$sqlIdx}'");

            if ($session === 0) {
                $this->denyAccessHandler();
            } else {
                dbSet('WM_session', '`chance` = 0', "`idx` = '{$sqlIdx}'");
                return $idx;
            }
        }
    }

    // 驗證 Token
    function checkToken()
    {
        // token = device UUID + '#SUNNET#' + timestamp
        $token = decryptImmediately(trim($_REQUEST['token']));
        $tokenSplitData = explode('#SUNNET#', $token);
        $tokenFrom = trim($tokenSplitData[0]);
        $tokenTimestamp = intval($tokenSplitData[1]);
        if (in_array($tokenFrom, $this->_whoGetFile) && (time() - $tokenTimestamp) <= $this->_tokenEffectiveTime) {
            return $tokenFrom;
        } else {
            header('Content-Type: application/json');
            echo '{"code":1,"message":"Access denied(invalid token)","data":{}}';
            exit();
        }
    }

    function header($contentType, $statusCode = '200')
    {
        if (array_key_exists($statusCode, $this->statusList)) {
            header($this->statusList[$statusCode], true, $statusCode);
        }
        header('Content-Type: ' . $contentType);
    }

    /**
     * 處理回傳資訊
     * @param $code
     * @param $message
     * @param $data
     */
    function returnHandler($code, $message, $data = array(), $statusCode = '200') {
        $responseObject = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );

        $this->header('application/json', $statusCode);
        echo json_encode($responseObject);
        exit();
    }
}