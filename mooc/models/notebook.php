<?php
/**
 * 提供與筆記本相關的函數
 *
 * 建立日期：2015/4/24
 * @author spring
 *
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once(sysDocumentRoot . '/lib/file_api.php');
require_once(sysDocumentRoot . '/lang/mooc_notebook.php');
require_once(sysDocumentRoot . '/lang/chatroom.php');
require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');

class notebook
{
    /**
     * 取指定筆記本下所有筆記標題
     *
     * @param String $fid 代號
     */
    function getNotebookTitleByFid($fid)
    {
        global $sysSession;
        
        // 沒有指定就回傳第1則
        $whr =  sprintf(
                    "`folder_id` = '%s' AND `receiver` = '%s' order by `priority`",
                    $fid,
                    $sysSession->username
                );

        $rs = dbGetStMr(
            'WM_msg_message',
            '`msg_serial`, `priority`, `subject`',
            $whr,        
            ADODB_FETCH_ASSOC
        );

        $data = array();
        if ($rs) {
            while (!$rs->EOF) {
                $id = sprintf("%d", mysql_real_escape_string($rs->fields['msg_serial']));
                
                $data[$id]['msg_serial'] = $rs->fields['msg_serial'];
                $data[$id]['subject'] = htmlspecialchars($rs->fields['subject']);// $rs->fields['subject'];

                $rs->MoveNext();
            }
        }
        
        return $data;
    }
    
    /**
     * 取一則筆記資訊(有指定回傳指定，沒指定回傳第一則)
     *
     * @param String $folderId 筆記本代號
     * @param String $serialId 筆記代號
     */
    function getNotebookDtl($folderId, $serialId = '')
    {
        global $sysSession;
        
        // 取筆記本總筆數
        $rsTotal = dbGetStMr(
            'WM_msg_message',
            '`msg_serial`',
            sprintf(
                "`folder_id` = '%s' AND `receiver` = '%s'",
                $folderId,
                $sysSession->username
            ),
            ADODB_FETCH_ASSOC
        );
        if ($rsTotal) {
            $total = $rsTotal->RecordCount();
        } else {
            $total = 0;
        }
        
        // 沒有指定就回傳最後一則閱讀紀錄，若仍無，則回傳第1則
        if ($serialId === '') {
            $read = $this->getNotebookLastRead($folderId);
            
            if (is_array($read) && $read['id'] >= 1) {
                $serialId = $read['id'];
                $whr =  sprintf(
                            "`folder_id` = '%s' AND `msg_serial` = '%s' AND `receiver` = '%s'",
                            $folderId,
                            $serialId,
                            $sysSession->username
                        );
            } else {
                $whr =  sprintf(
                            "`folder_id` = '%s' AND `receiver` = '%s' order by `msg_serial` limit 1, 0",
                            $folderId,
                            $sysSession->username
                        );
            }
        } else {
            $whr =  sprintf(
                        "`folder_id` = '%s' AND `msg_serial` = '%s' AND `receiver` = '%s'",
                        $folderId,
                        $serialId,
                        $sysSession->username
                    );
        }
        
        $rs = dbGetStMr(
            'WM_msg_message',
            '`msg_serial`, `sender`, `receiver`, `submit_time`, `status`, `priority`, `subject`, `content`, `attachment`, `note`',
            $whr,        
            ADODB_FETCH_ASSOC
        );
        
        $data = array();
        if ($rs) {
            while (!$rs->EOF) {
                $id = sprintf("%d", mysql_real_escape_string($rs->fields['msg_serial']));
                
                $data[$id]['msg_serial'] = $rs->fields['msg_serial'];
                $data[$id]['sender'] = $rs->fields['sender'];
                $data[$id]['receiver'] = $rs->fields['receiver'];
                $data[$id]['submit_time'] = $rs->fields['submit_time'];
                $data[$id]['status'] = $rs->fields['status'];
                $data[$id]['priority'] = $rs->fields['priority'];
                $data[$id]['subject'] = mb_substr($rs->fields['subject'], 0, 15, 'UTF-8');
                $data[$id]['full_subject'] = $rs->fields['subject'];
                $data[$id]['content'] = $rs->fields['content'];
                
                $attachment = parseFileList($rs->fields['attachment']);
                $data[$id]['attachment'] = $attachment;
                
                $data[$id]['note'] = $rs->fields['note'];
            
                $data[$id]['total_rows'] = $total;// 全部則數
                $data[$id]['limit_rows'] = 1;// 一頁幾則
                
                // 取目前筆記是第幾則
                $rsSeq = dbGetStMr(
                    'WM_msg_message',
                    '`msg_serial`',
                    sprintf(
                        "`folder_id` = '%s' AND `msg_serial` <= '%s' AND `receiver` = '%s'",
                        $folderId,
                        $id,
                        $sysSession->username
                    ),
                    ADODB_FETCH_ASSOC
                );
                if ($rsSeq) {
                    $seq = $rsSeq->RecordCount();
                } else {
                    $seq = 0;
                }
                $data[$id]['current_page'] = $seq;

                $rs->MoveNext();
            }
        } 
        
        return $data;
    }
    
    /**
     * 取指定筆記本最後一次閱讀紀錄
     *
     * @param String $fid 筆記本代號
     */
    function getNotebookLastRead($fid)
    {
        global $sysSession;
        
        $whr =  sprintf(
                    "`username` = '%s' AND `fid` = '%s' AND `mid` IN (SELECT `msg_serial` FROM `WM_msg_message` WHERE folder_id = '%s' AND `receiver` = '%s')",
                    $sysSession->username,
                    $fid,
                    $fid,
                    $sysSession->username
                );
        
        $rs = dbGetStMr(
            'WM_notebook_log',
            '`mid`, `upd_time`',
            $whr,        
            ADODB_FETCH_ASSOC
        );

        $data = array();
        if ($rs) {
            while (!$rs->EOF) {
                $data['id']       = $rs->fields['mid'];
                $data['upd_time'] = $rs->fields['upd_time'];

                $rs->MoveNext();
            }
        }
        
        return $data;
    }
    
    /**
     * 設定指定筆記本最後一次閱讀紀錄
     *
     * @param String $fid 筆記本代號
     * @param Integer $id 筆記流水號
     */
    function setNotebookLastRead($fid, $id)
    {
        global $sysSession;
        
        $read = $this->getNotebookLastRead($fid);
        if (is_array($read) && $read['id'] >= 1) {
            dbSet(
                'WM_notebook_log',
                sprintf(
                    "`mid` = %d, `operator` = '%s', `upd_time` = now()",
                    $id,
                    $sysSession->username
                ),
                sprintf(
                    "`username` = '%s' and `fid` = '%s'",
                    $sysSession->username,
                    $fid
                )
            );
        } else {
            dbNew(
                '`WM_notebook_log`',
                '`username`, `fid`, `mid`, `creator`, `create_time`, `operator`, `upd_time`',
                "'{$sysSession->username}', '{$fid}', $id, '{$sysSession->username}', now(), '{$sysSession->username}', now()"
            );
        }
        
        return '1';
    }
    
    /**
     * 給予關鍵字搜尋筆記
     *
     * @param String $keyword 關鍵字
     * @param String $fid 指定筆記本編號，可以不指定
     */
    function searchNotebooks($keyword, $fid = '')
    {
        global $sysSession;
        
        $contentKeyword = htmlspecialchars($keyword);
        for($x = 0; $x < mb_strlen($contentKeyword, 'UTF-8'); $x++){
            $searchKeyword .= mb_substr($contentKeyword, $x, 1, 'UTF-8') . '%';
        }        
        
        if (isset($fid) && $fid >= '0') {
            $whr =  sprintf(
                "`receiver` = '%s' AND `folder_id` = '%s' AND (`subject` LIKE '%s' OR `content` LIKE '%s') AND `folder_id` != 'sys_notebook_trash' order by `folder_id`, `msg_serial`",
                $sysSession->username,
                $fid,
                '%' . mysql_real_escape_string($keyword) . '%',
                '%' . mysql_real_escape_string($searchKeyword)
            );
        } else {
            $whr =  sprintf(
                "`receiver` = '%s' AND (`subject` LIKE '%s' OR `content` LIKE '%s') AND `folder_id` != 'sys_notebook_trash' order by `folder_id`, `msg_serial`",
                $sysSession->username,
                '%' . mysql_real_escape_string($keyword) . '%',
                '%' . mysql_real_escape_string($searchKeyword)
            );
        }
        
        $rs = dbGetStMr(
            'WM_msg_message',
            '`msg_serial`, `receiver`, `folder_id`, `submit_time`, `subject`, `content`',
            $whr,        
            ADODB_FETCH_ASSOC
        );

        $data = array();
        if ($rs) {
            while (!$rs->EOF) {
            
                if (strpos(($rs->fields['subject']), $keyword) !== false || strpos(strip_tags($rs->fields['content']), $keyword) !== false) {
                    $id = sprintf("%d", mysql_real_escape_string($rs->fields['msg_serial']));
//                    echo '<pre>';
//                    var_dump($rs->fields['content']);
//                    var_dump(strip_tags($rs->fields['content']));
//                    echo '</pre>';

                    $data[$id]['msg_serial'] = $rs->fields['msg_serial'];
                    $data[$id]['folder_id'] = $rs->fields['folder_id'];
                    $data[$id]['receiver'] = $rs->fields['receiver'];
                    $data[$id]['submit_time'] = $rs->fields['submit_time'];
               
                    if (strpos(($rs->fields['subject']), $keyword) !== false) {
                        // 醒目的關鍵字-簡化標題
                        $subject = str_replace(htmlspecialchars($keyword), '<span class="strong" style="color: red; font-weight: bold;">' . htmlspecialchars($keyword) . '</span>', mb_substr(htmlspecialchars($rs->fields['subject']), 0, 15, 'UTF-8'));
                        $data[$id]['subject'] = $subject;

                        // 醒目的關鍵字-完整標題
                        $full_subject = str_replace(htmlspecialchars($keyword), '<span class="strong" style="color: red; font-weight: bold;">' . htmlspecialchars($keyword) . '</span>', (htmlspecialchars($rs->fields['subject'])));
                        $data[$id]['full_subject'] = $full_subject;
                    } else {
                        $data[$id]['subject'] = mb_substr(htmlspecialchars(($rs->fields['subject'])), 0, 15, 'UTF-8');
                        $data[$id]['full_subject'] = htmlspecialchars(($rs->fields['subject']));
                    }
                    
                    $wordLen = array(200, 201, 202);
                    if (strpos(strip_tags($rs->fields['content']), $keyword) !== false) {
                        // 醒目的關鍵字-筆記內文
                        // 關鍵字前半段（不含關鍵字）
                        if (strpos(strip_tags($rs->fields['content']), $keyword) <= 202) {
                            $data[$id]['content'] = substr(strip_tags($rs->fields['content']), 0, strpos(strip_tags($rs->fields['content']), $keyword));
                        } else {
                            foreach ($wordLen as $v) {
                                $contentHead = substr(strip_tags($rs->fields['content']), strpos(strip_tags($rs->fields['content']), $keyword) - $v, $v);
                                $jsonContentHead = json_encode($contentHead);
                                if ($jsonContentHead !== 'null') {
                                    $data[$id]['content'] = htmlspecialchars($contentHead, ENT_QUOTES);
                                    break;
                                }
                            }
                        }
                        
                        // 後半段（含關鍵字）
                        foreach ($wordLen as $v) {
                            $contentEnd = substr(strip_tags($rs->fields['content']), strpos(strip_tags($rs->fields['content']), $keyword), $v);
                            $jsonContentEnd = json_encode($contentEnd);
                            if ($jsonContentEnd !== 'null') {
                                $data[$id]['content'] .= htmlspecialchars($contentEnd, ENT_QUOTES);
                                break;
                            }
                        }
                        
                        $content = $data[$id]['content'];
                        // 變色
                        $content = str_replace($keyword, '<span class="strong" style="color: red; font-weight: bold;">' . $keyword . "</span>", $content);
                        $data[$id]['content'] = $content;
                    // 都沒有，顯示前面200字
                    } else {
                        foreach ($wordLen as $v) {
                            $content = mb_substr(strip_tags($rs->fields['content']), 0, $v);
                            $jsonContent = json_encode($content);
                            if ($jsonContent !== 'null') {
                                $data[$id]['content'] .= $content;
                                break;
                            }
                        }
                    }
                }

                $rs->MoveNext();
            }
        }
        
        return $data;
    }
    
    /**
     * 取討論室記錄的多語系，用以讓雲端筆記做過濾掉
     * 2016/7/27 1440 浦元表示筆記本仍要顯示討論室記錄
     */
    function getChatroomMultiLang() {
        global $MSG;
        
        $keywords = array_filter(array_merge(array_values($MSG['chat_log']), array_values($MSG['chat_log_attachment'])));
        foreach($keywords as $v) {
            $data[] = "'" . $v . "'";
        }
        $chatroom = implode(',', $data);
        
        return $chatroom;
    }
}
