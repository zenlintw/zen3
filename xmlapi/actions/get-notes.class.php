<?php
/**
 * 取得筆記資訊
 */

include_once(dirname(__FILE__).'/action.class.php');
require_once(PATH_LIB . 'note.php');

class GetNotesAction extends baseAction
{
    /**
     * 帳號
     * @access private
     * @var string
     **/
    var $_username = '';

    /**
     * 避免SQL Injection 用的帳號
     * @access private
     * @var string
     **/
    var $_avoidSIUsername = '';

    var $_defaultDiffTime = 28800;

    function main()
    {
        // 先檢查ticket是否正確
        parent::checkTicket();
        global $sysSession;

        $notebooks = array();
        $syncTime = 0;

        // 設定之後要用的username(避免SQL Injection)
        $this->_avoidSIUsername = mysql_real_escape_string($sysSession->username);

        if (isset($_GET['sync_time']) && intval($_GET['sync_time']) > 0) {
            $getTime = intval(trim($_GET['sync_time'])) + $this->_defaultDiffTime;
            $syncTime = date('Y-m-d H:i:s', $getTime);
        }

        if (isset($_GET['type']) && trim($_GET['type']) === 'notes') {
            // 設定本程式之後要用的帳號
            $this->_username = $sysSession->username;
        }

        if (isset($_GET['extra']) && trim($_GET['extra']) === 'all') {
            // 設定本程式是否要擷取所有筆記本，即便底下沒有筆記
            $extra = 'all';
        } else {
            $extra = '';
        }
        $folderXML = dbGetOne('WM_msg_folder', 'content', "`username` = '{$this->_avoidSIUsername}'");

        if (!$folderXML) {
            // 可能因為新增帳號，導致尚未建立folder資料；故在此先新增一次
            $originalFolderXML = file(sysDocumentRoot . '/config/xml/msg_folder.xml');
            $originalFolderContent = implode('', $originalFolderXML);
            dbNew('WM_msg_folder', 'username, content', "'{$this->_avoidSIUsername}', '{$originalFolderContent}'");

            // 拿原始預設值去parse
            $folderXML = $originalFolderContent;
        }

        if ($folderXML) {
            if ($xmlDoc = domxml_open_mem($folderXML)) {
                // 可以parse
                $xpathBase = '/manifest//folder';
                $xmlXpathContext = xpath_new_context($xmlDoc);

                $notebooks = getNotebook($xpathBase, $xmlXpathContext, $syncTime, $this->_username, $extra);
                $code = 0;
                $message = 'success';
            } else {
                // 無法parse
                $code = 3;
                $message = 'fail';
            }
        } else {
            // 還是找不到folder XML
            $code = 2;
            $message = 'fail';
        }

        // code: 0(取得成功) | 2(取不到folder XML) | 3(folder XML 無法 parse)
        $responseObject = array(
            'code' => intval($code),
            'message' => $message,
            'data' => array(
                'notebooks' => $notebooks,
            )
        );

        $jsonEncode = JsonUtility::encode($responseObject);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}