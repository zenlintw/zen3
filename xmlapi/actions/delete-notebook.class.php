<?php
/**
 * 刪除筆記本
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');

class DeleteNotebookAction extends baseAction
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
        // 筆記本ID
        $folderId = mysql_real_escape_string(trim($_GET['folder_id']));

        if (isset($folderId)) {
            // 有提供筆記本名稱
            $table = 'WM_msg_folder';
            $where = "`username` = '{$avoidSIUsername}' AND LOCATE('{$folderId}', `content`)";
            $folderXML = dbGetOne($table, 'content', $where);

            $noteTable = 'WM_msg_message';

            if (isset($folderXML)) {
                if ($xmlDoc = domxml_open_mem($folderXML)) {
                    $xmlXpathContext = xpath_new_context($xmlDoc);

                    // 取得筆記本的回收桶
                    $notebookTrashXpath = '/manifest/folder[@id="sys_notebook"]//folder[@id="sys_notebook_trash"]';
                    $notebookTrashResult = $xmlXpathContext->xpath_eval($notebookTrashXpath);
                    $notebookTrash = $notebookTrashResult->nodeset[0];

                    // 取得欲刪除筆記本的parent
                    $deleteFolderParentXpath = '/manifest/folder[@id="sys_notebook"]//folder[@id="' . $folderId. '"]//ancestor::folder[1]';
                    $deleteFolderParentResult = $xmlXpathContext->xpath_eval($deleteFolderParentXpath);
                    $deleteFolderParent = $deleteFolderParentResult->nodeset[0];

                    // 取得欲刪除的筆記本
                    $deleteFolderXpath = '/manifest/folder[@id="sys_notebook"]//folder[@id="' . $folderId. '"]';
                    $deleteFolderResult = $xmlXpathContext->xpath_eval($deleteFolderXpath);
                    $deleteFolder = $deleteFolderResult->nodeset[0];

                    if (isset($xmlDoc) && isset($deleteFolderParent) && isset($deleteFolder)) {
                        // 移除parent底下的節點
                        $deleteFolderParent->remove_child($deleteFolder);
                        // 改接在回收桶底下
                        $notebookTrash->append_child($deleteFolder);
                        $newFolderXML = trim($xmlDoc->dump_mem(true));

                        if ($newXmlDoc = domxml_open_mem($newFolderXML)) {
                            // 刪除後的xml還可以parse的話
                            $saveXml = mysql_real_escape_string($newFolderXML);
                            $value = "`content` = '{$saveXml}'";
                            dbSet($table, $value, $where);

                            if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0){
                                // update 失敗
                                $code = 7;
                            } else {
                                // update 成功
                                $code = 0;
                                $message = 'success';
                            }
                        } else {
                            // 新增後的xml不可以parse
                            $code = 6;
                        }
                    } else {
                        // xpath 異常
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
            // 沒有筆記本ID
            $code = 2;
        }

        appSysLog(999999013, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], 'Delete Notebook:' . $message, $sysSession->username);

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
}