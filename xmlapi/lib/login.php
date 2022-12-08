<?php
/**
 * Class QRCode_Login
 * 提供 QRCode 登入認證功能
 * TODO: 多語系
 */
class QRCode_Login {
    // 加解密用的 key
    var $TICKET_KEY = 'SunWm51';
    // Ticket 過期時間
    var $EXPIRE_SECOND = 180;
    // 紀錄錯誤訊息用
    var $errorMsg = '';

    function constructor () {
        require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
        require_once(sysDocumentRoot . '/lang/mooc.php');
    }

    /**
     * 建立 QRCode 資訊供掃碼登入
     * TODO: 行動裝置上好像用不到
     * @param $idx {String} session 編號
     * @param $username {String} 帳號
     * @return string
     */
    function createQRCodeTicket($idx, $username) {
        return sysNewEncode(serialize(array($idx, $username, time())), $this->TICKET_KEY);
    }

    /**
     * 解析 QRCode Ticket
     * @param $qrCodeTicket {String} 加密的 QRCode Ticket
     * @return array|bool 解析有誤回傳 false，反之回傳解析後的資料
     */
    function parseQRCodeTicket($qrCodeTicket) {
        $decodeTicket = sysNewDecode($qrCodeTicket, $this->TICKET_KEY);
        // 無效的 Ticket
        if ($decodeTicket === false || empty($decodeTicket)) {
            $this->errorMsg = '錯誤的 QRCode 資訊';
            return false;
        }

        $qrCodeParams = unserialize($decodeTicket);
        // 過期的 Ticket
        if (time() - intval($qrCodeParams[2]) > $this->EXPIRE_SECOND) {
            $this->errorMsg = 'QRCode 已過期，請重新產生';
            return false;
        }

        // 取得 QRCode 身分的資料
        $targetUsername = dbGetOne(
            'WM_session',
            '`username`',
            sprintf("`idx` = '%s'", mysql_real_escape_string($qrCodeParams[0]))
        );
        // QRCode 內的 username 與 WM_session 所存的不相符
        if ($qrCodeParams[1] !== $targetUsername) {
            $this->errorMsg = '無效的 QRCode 資訊';
            return false;
        }

        return array(
            'idx' => $qrCodeParams[0],
            'username' => $qrCodeParams[1]
        );
    }
    /**
     * 利用 QRCode 取得的資訊登入
     * WEB 命名: movelight
     * @param $qrCodeParams
     * @return bool
     */
    function chgMySessionByQRCode($qrCodeParams) {
        global $sysSession;

        // 取得 QRCode 身分的資料
        $targetUserData = dbGetRow(
            'WM_session',
            '`username`, `realname`, `email`',
            sprintf("`idx` = '%s'", mysql_real_escape_string($qrCodeParams['idx']))
        );

        // QRCode 的 session 是未登入身份
        if (empty($targetUserData['username'])) {
            $this->errorMsg = 'QRCode 資訊有誤，請重新載入頁面再產生 QRCode';
            return false;
        }

        // QRCode 的 session 是已被登出
        if ($targetUserData['username'] === 'guest') {
            $this->errorMsg = 'QRCode 的身分已登出，請重新登入再進行操作';
            return false;
        }

        // 自己已登入或與 QRCode 取得的帳號相同
        if ($sysSession->username !== 'guest') {
            if ($sysSession->username === $targetUserData['username']) {
                $this->errorMsg = '已登入與 QRCode 相同的身分';
            } else {
                $this->errorMsg = '您已是登入身份，若要切換身份，請先登出後，再進行掃Qrcode登入操作。';
            }
            return false;
        }

        // 將自己的 Session 資訊更改為 QRCode 得到的身分資訊
        dbSet(
            'WM_session',
            sprintf(
                "`username` = '%s', `realname` = '%s', `email` = '%s'",
                $targetUserData['username'],
                $targetUserData['realname'],
                $targetUserData['email']
            ),
            sprintf("idx = '%s'", mysql_escape_string($sysSession->ticket))
        );

        // 更新 sysSession
        $sysSession->refresh();

        return true;
    }
    /**
     * 利用自己的資訊幫 QRCode 取得的身分登入
     * WEB 命名: spotlight
     * @param $qrCodeParams
     * @return bool
     */
    function chgOtherSessionByMe($qrCodeParams) {
        global $sysSession;

        // 自己為登入
        if ($sysSession->username == 'guest') {
            return false;
        }

        // 取得 QRCode 的 Session 資訊
        $targetUsername = dbGetOne('WM_session','`username`',sprintf("`idx` = '%s'",mysql_escape_string($qrCodeParams['idx'])));

        // QRCode 的 session 已是登入身份或空值
        if (empty($targetUsername) || $targetUsername !== 'guest') {
            return false;
        }

        // 將 QRCode 的 Session 資訊改為自己的身分資訊
        dbSet(
            'WM_session',
            sprintf(
                "`username` = '%s', `realname` = '%s', `email` = '%s'",
                $sysSession->username,
                $sysSession->realname,
                $sysSession->email
            ),
            sprintf("`idx` = '%s'",mysql_escape_string($qrCodeParams['idx']))
        );

        return true;
    }
}
?>
