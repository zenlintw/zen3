<?php
/**
 * 新增筆記本
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');

class AddNotebookAction extends baseAction
{
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession, $sysConn;
        // 避免 SQL Injection 的 username
        $avoidSIUsername = mysql_real_escape_string($sysSession->username);
        // 預設回傳的message
        $message = 'fail';

        // 處理接收的資料 - Begin
        $inputData = file_get_contents('php://input');
        $postData = JsonUtility::decode($inputData);
        // 筆記本ID
        $folderId = mysql_real_escape_string(trim($postData['folder_id']));
        // 筆記本名稱
        $folderName = mysql_real_escape_string(trim($postData['folder_name']));
        // 處理接收的資料 - End

        if (isset($folderName)) {
            // 有提供筆記本名稱
            $table = 'WM_msg_folder';
            $where = "username = '{$avoidSIUsername}'";
            $folderXML = dbGetOne($table, 'content', $where);

            if (isset($folderXML)) {
                if ($xmlDoc = domxml_open_mem($folderXML)) {
                    $xpath= '/manifest/folder[@id="sys_notebook"]';
                    
                    $xmlXpathContext = xpath_new_context($xmlDoc);
                    $xpathResult = $xmlXpathContext->xpath_eval($xpath);
                    $sysNotebook = $xpathResult->nodeset[0];

                    if (isset($xmlDoc) && isset($sysNotebook)) {
                        $newFolderXML = trim($this->makeNewFolder($xmlDoc, $sysNotebook, $folderName, $folderId));

                        if ($newXmlDoc = domxml_open_mem($newFolderXML)) {
                            // 新增後的xml還可以parse的話
                            $saveXml = mysql_real_escape_string($newFolderXML);
                            $value = "`content` = '{$saveXml}'";
                            dbSet($table, $value, $where);

                            if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0){
                                $code = 7;
                            } else {
                                $code = 0;
                                $message = 'success';
                            }
                        } else {
                            // 新增後的xml不可以parse
                            $code = 6;
                        }
                    } else {
                        $code = 5;
                    }
                } else {
                    // 無法parse
                    $code = 4;
                }
            } else {
                // 找不到folder xml
                $code = 3;
            }
        } else {
            // 沒有筆記本名稱
            $code = 2;
        }

        appSysLog(999999010, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], 'Add Notebook:' . $message, $sysSession->username);

        // code: 0(新增成功) | 2(沒有筆記本名稱) | 3(在資料庫找不到folder xml) | 4(folder xml無法parse) | 5(xpath異常) | 6(處理新增後的xml無法parse) | 7(更新資料表錯誤)
        $responseObject = array(
            'code' => intval($code),
            'message' => $message,
            'data' => array()
        );

        $jsonEncode = JsonUtility::encode($responseObject);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }

    /**
     * 處理新增筆記本
     *
     * @param object $xmlDoc User的folder xml
     * @param object $sysNotebook 新增筆記本的root
     * @param string $folderName 新增筆記本的名稱
     * @param string $folderId 筆記本ID
     *
     * @return string folder的xml
     **/
    function makeNewFolder ($xmlDoc, $sysNotebook, $folderName, $folderId) {
        // create 新的 folder
        $newFolder = $xmlDoc->create_element('folder');
        $newFolder->set_attribute('id', $folderId);

        // create setting
        $newFolderSetting = $xmlDoc->create_element('setting');
        $newFolderSettingOrder = $xmlDoc->create_element('order');
        $newFolderSetting->append_child($newFolderSettingOrder);
        $newFolder->append_child($newFolderSetting);

        // create title
        $newFolderTitle = makeFolderTitle($xmlDoc, 'title', $folderName);
        $newFolder->append_child($newFolderTitle);

        // create help
        $newFolderHelp = makeFolderTitle($xmlDoc, 'help', $folderName);
        $newFolder->append_child($newFolderHelp);

        // 把新增的筆記本 append 到 sys_notebook 下
        $sysNotebook->append_child($newFolder);

        // 將處理後的xml dump出來並回傳
        return $xmlDoc->dump_mem(true);
    }
}