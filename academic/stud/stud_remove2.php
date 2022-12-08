<?php
    /**
     * 刪除連續帳號
     * $Id: stud_remove2.php,v 1.1 2010/02/24 02:38:45 saly Exp $
     **/

    ignore_user_abort(true);
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/lang/stud_account.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    $actType = '';
    // 刪除連續帳號
    $ticket = md5($sysSession->username . 'AutoDelete' . $sysSession->ticket. $sysSession->school_id);
    if (trim($_POST['ticket']) == $ticket) {
        $actType      = 'Auto';
        $actMsg       = $MSG['del_serial_account'][$sysSession->lang];
        $act_back     = 'stud_remove.php?msgtp=1';
        $act_back_Msg = $MSG['title71'][$sysSession->lang];
        $function_id  = '0400300400';
    }

    // 刪除不規則帳號
    $ticket = md5('AddManual' . $sysSession->ticket . $sysSession->school_id . $sysSession->username);
    if (trim($_POST['ticket']) == $ticket) {
        $actType      = 'Manual';
        $actMsg       = $MSG['del_discrete_account'][$sysSession->lang];
        $act_back     = 'stud_remove.php?p=1&msgtp=2';
        $act_back_Msg = $MSG['title72'][$sysSession->lang];
        $function_id  = '0400300200';
    }

    // 刪除匯入帳號
    $ticket = md5($sysSession->ticket . 'DeleteImport' . $sysSession->school_id . $sysSession->username);
    if (trim($_POST['ticket']) == $ticket) {
        $actType      = 'Import';
        $actMsg       = $MSG['import_del_account'][$sysSession->lang];
        $act_back     = 'stud_remove.php?msgtp=3';
        $act_back_Msg = $MSG['title73'][$sysSession->lang];
        $function_id  = '0400300600';
    }

    if (empty($actType)) {
        die('Access Deny.');
    }

    $sysSession->cur_func = $function_id;
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }


    /**
     * 批次刪除指定帳號
     *
     * @param   string      $username   欲刪除帳號
     * @param   bool        $commit     是否執行 (置於迴圈之後，最後一次執行)
     */
    function removeUserAction($username, $commit=false)
    {
        global $sysConn;
        static $usernames; // 暫存所有欲刪除的帳號

        if ($commit)
        {
            if (!empty($username)) $usernames[] = $username;
            if (!is_array($usernames) || count($usernames) < 1) return;

            $u = sysDBaccoount;
            $p = sysDBpassword;

            list($foo, $mysql_basedir) = $sysConn->GetRow('show variables like "basedir"');

            $mysql = $mysql_basedir . 'bin/mysql';
            if (!file_exists($mysql) || !is_executable($mysql))
            {
                $mysql = exec("sh -c 'PATH=/usr/local/mysql/bin:/usr/bin:/usr/local/bin:/home/apps/mysql/bin which mysql'");
                if (empty($mysql) || strpos($mysql, 'which: no mysql')===0) die('"mysql" not found.');
            }
            if (!file_exists($mysql) || !is_executable($mysql)) die('"mysql" not found or not executable.');
            $mysql .= (sysDBhost == 'localhost' ? ' -S /tmp/mysql.sock' : (' -h ' . str_replace(':', ' -P ', sysDBhost)));

            // $fp = popen("{$mysql} -u {$u} -p{$p}",'w');
            $tempSQL = tempnam('/tmp', 'wm_batch_rm_user_');
            $fp = fopen($tempSQL, 'w');

            $all_username = implode("','", $usernames);

            // 備份到 history & 刪除相關資料
            fwrite($fp, 'use ' . sysDBname . ";\n");
            if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
                fwrite($fp, "delete from CO_fb_account where username in ('{$all_username}');\n");
            }
            
            // custom 刪除all_account之前先備份到history
            fwrite($fp, "insert IGNORE into ".sysDBschool.".WM_history_user_account select null,WM_all_account.* from ".sysDBname.".WM_all_account where username in ('{$all_username}');\n");
            
            fwrite($fp, "delete from WM_sch4user     where username in ('{$all_username}');\n");
            fwrite($fp, "delete from WM_all_account     where username in ('{$all_username}');\n");
            fwrite($fp, "delete from CO_mooc_account where username in ('{$all_username}');\n");
            fwrite($fp, "delete from WM_manager         where username in ('{$all_username}');\n");
            fwrite($fp, "delete from WM_auth_ftp     where userid   in ('{$all_username}');\n");
            fwrite($fp, 'use ' . sysDBschool . ";\n");
            foreach(array(// 'user_account'             => 'username', // custom
                          'term_major'               => 'username',
                          'qti_exam_result'          => 'examinee',
                          'qti_homework_result'      => 'examinee',
                          'qti_questionnaire_result' => 'examinee',
                          'grade_stat'               => 'username',
                          'record_reading'           => 'username',
                          'record_daily_personal'    => 'username',
                          'scorm_tracking'           => 'username',
                          'log_classroom'            => 'username',
                          'log_director'             => 'username',
                          'log_manager'              => 'username',
                          'log_others'               => 'username',
                          'log_teacher'              => 'username') as $table => $field)
            fwrite($fp, "insert IGNORE into WM_history_{$table} select null,WM_{$table}.* from WM_{$table} where {$field} in ('{$all_username}');\n");

            fwrite($fp, "update WM_student_group set captain=NULL where captain  in ('{$all_username}');\n");
            fwrite($fp, "update WM_bbs_boards    set manager=NULL where manager  in ('{$all_username}');\n");
            fwrite($fp, "delete from WM_acl_member                where member   in ('{$all_username}');\n");
            fwrite($fp, "delete from WM_msg_message               where receiver in ('{$all_username}');\n");
            fwrite($fp, "delete from WM_qti_exam_result           where examinee in ('{$all_username}');\n");
            fwrite($fp, "delete from WM_qti_homework_result       where examinee in ('{$all_username}');\n");
            fwrite($fp, "delete from WM_qti_questionnaire_result  where examinee in ('{$all_username}');\n");
            fwrite($fp, "delete from CO_user_verify               where username in ('{$all_username}');\n");

            foreach(array('user_picture',
                          'user_tagline',
                          'student_div',
                          'msg_folder',
                          'calendar',
                          'cal_setting',
                          'bbs_order',
                          'bbs_readed',
                          'grade_item',
                          'ipfilter',
                          'review_flow',
                          'term_teacher',
                          'class_director',
                          'class_member',
                          'content_ta',
                          'im_message',
                          'im_setting',
                          'chat_user_setting',
                          // 'user_account', // user_account是view不能直接刪除
                          'member_div',
                          'term_major',
                          'grade_stat',
                          'record_reading',
                          'record_daily_personal',
                          'scorm_cmi',
                          'scorm_tracking',
                          'log_classroom',
                          'log_director',
                          'log_manager',
                          'log_others',
                          'log_teacher') as $table)
            fwrite($fp, "delete from WM_{$table} where username in ('{$all_username}');\n");

            //pclose($fp);
            fclose($fp);
            // exec("{$mysql} -u {$u} -p{$p} < {$tempSQL} ; rm {$tempSQL} &");
            exec("{$mysql} -u {$u} -p{$p} < {$tempSQL} ;");

            // 刪除 user 目錄
            foreach ($usernames as $user)
            {
                $user_dir = sysDocumentRoot .
                            DIRECTORY_SEPARATOR . 'user' .
                            DIRECTORY_SEPARATOR . substr($user, 0, 1) .
                            DIRECTORY_SEPARATOR . substr($user, 1, 1) .
                            DIRECTORY_SEPARATOR . $user;
                if (is_dir($user_dir) && strlen($user_dir) > 10)
                    exec("rm -rf $user_dir &");
            }
            $usernames = array();
        }
        else
        {
            $usernames[] = $username;
        }
    }

    /**
     * 安全性檢查
     *     1. 身份的檢查
     *     2. 權限的檢查
     *     3. .....
     **/
    // 設定車票
    //setTicket();

    $user = array();
    // 刪除連續帳號
    if ($actType == 'Auto') {

        $header = preg_replace('/[^A-Za-z0-9-_.]/', '', $_POST['header']);
        $tail   = preg_replace('/[^A-Za-z0-9-_.]/', '', $_POST['tail']);
        $first  = min(99999,max(0,intval($_POST['first'])));
        $last   = min(99999,max(0,intval($_POST['last'])));
        $len    = min(5,max(1,intval($_POST['len'])));
        $fmt    = "{$header}%0{$len}d{$tail}";
        for($i = $first; $i <= $last; $i++) {
            $ac = sprintf($fmt, $i);
            $user[] = $ac;
        }
    }

    // 刪除不規則帳號
    if ($actType == 'Manual') {
        $user = preg_split('/[^\w.-]+/', $_POST['del_user'], -1, PREG_SPLIT_NO_EMPTY);
        
        // todo: 因應校務帳號匯入以空白開頭的帳號
//        $user = preg_split('/[^\w.-\s]+/', $_POST['del_user'], -1, PREG_SPLIT_NO_EMPTY);
        // 增加偵測點
        if ($_POST['del_user'] !== implode(',', $user)) {
            echo '<pre>';
            echo '帳號解析後再重組總長度與原長度不合，請確認，以避免誤刪帳號（^起始符號，$結束符號）';
            echo '</pre>';
            echo '<pre>';
            echo '原帳號（長度' . strlen($_POST['del_user']) . '）：^' . $_POST['del_user'] . '$';
            echo '</pre>';
            echo '解析後的帳號（長度' . strlen(implode(',', $user)) . '）：^' . implode(',', $user) . '$';
            die();
        }
    }

    // 刪除匯入帳號
    if ($actType == 'Import') {
        $user = preg_split('/[^\w.-]+/', implode(',', $_POST['nla']), -1, PREG_SPLIT_NO_EMPTY);
    }
        
        // 檢查帳號資料表欄位是否一致
        // 取兩資料表 WM_all_account, WM_history_user_account 共同欄位        
        function chk2AccountTableCols() {
            $rsAllAccount = dbGetStMr('INFORMATION_SCHEMA.`COLUMNS`', 'COLUMN_NAME', sprintf("TABLE_SCHEMA = '%s' and TABLE_NAME = 'WM_all_account'", sysDBname), ADODB_FETCH_ASSOC);
            $rowsAllAccount = array();
            if ($rsAllAccount) {        
                while (!$rsAllAccount->EOF) {
                    $rowsAllAccount[] = $rsAllAccount->fields['COLUMN_NAME'];

                    $rsAllAccount->MoveNext();
                }
                $rowsAllAccount[] = 'serial_no';
            }

            global $sysSession;
            $rsHistoryUserAccount = dbGetStMr('INFORMATION_SCHEMA.`COLUMNS`', 'COLUMN_NAME', sprintf("TABLE_SCHEMA = '%s' and TABLE_NAME = 'WM_history_user_account'", sysDBprefix . $sysSession->school_id), ADODB_FETCH_ASSOC);
            $rowsHistoryUserAccount = array();
            if ($rsHistoryUserAccount) {        
                while (!$rsHistoryUserAccount->EOF) {
                    $rowsHistoryUserAccount[] = $rsHistoryUserAccount->fields['COLUMN_NAME'];

                    $rsHistoryUserAccount->MoveNext();
                }
            } 
            $intersectAccountCols = array_intersect($rowsHistoryUserAccount, $rowsAllAccount);

            $errorAccountCols = FALSE;
            // WM_history_user_account 與 WM_all_account 欄位數目不一致，無法進行備份
            if (count(array_diff($rowsHistoryUserAccount, $rowsAllAccount)) + count(array_diff($rowsAllAccount, $rowsHistoryUserAccount)) >= 1) {
                $errorAccountCols = TRUE;

                if (empty($_COOKIE['show_me_info']) === FALSE) {
                    echo '<pre>';
                    var_dump('WM_history_user_account 多了 ' . implode(', ', array_diff($rowsHistoryUserAccount, $rowsAllAccount)). ' 欄位');
                    var_dump('WM_all_account 多了 ' . implode(', ', array_diff($rowsAllAccount, $rowsHistoryUserAccount)) . ' 欄位');
                    echo '</pre>';
                }
            }
            
            return $errorAccountCols;
        }

    // 開始刪除帳號
    $js = <<< BOF
    function listPrint() {
        var nodes = document.getElementsByTagName("input");
        var obj1 = document.getElementById("btn");
        obj1.style.display = "none";
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].style.visibility = "hidden";
        }
        window.print();
        obj1.style.display = "block";
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].style.visibility = "visible";
        }
    }

    /**
     * 回 刪除不規則帳號
    **/
    function go_list() {
        window.location.replace("{$act_back}");
    }

BOF;

    $arry[] = array($actMsg, 'delTable1');

    showXHTML_head_B($MSG['delete_account'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('inline', $js);
    showXHTML_head_E();
    showXHTML_body_B();
        showXHTML_table_B('border="0" cellspacing="0" cellpadding="0"');

            showXHTML_tr_B();
                showXHTML_td_B();
                    showXHTML_tabs($arry, 1);
                showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B();
                showXHTML_td_B('valign="top" ');
                    showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="delTable1" style="display:block" class="cssTable"');

                        showXHTML_tr_B('class="bg02"');
                            showXHTML_td('align="center" nowrap class="font01"', $MSG['username'][$sysSession->lang]);
                            showXHTML_td('align="center" nowrap class="font01"', $MSG['status'][$sysSession->lang]);
                        showXHTML_tr_E();

                        $suc = 0; $fau = 0; $noexist = 0; $del_num = count($user);

                        $teachers  = dbGetCol('WM_term_major',   'distinct username', 'role&' . ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']));
                        $directors = dbGetCol('WM_class_member', 'distinct username', 'role&' . ($sysRoles['assistant']|$sysRoles['director']));
                        $managers  = dbGetCol('WM_manager',      'distinct username', 'school_id=' . $sysSession->school_id . ' or level&8192');
            
                        // 檢查帳號資料表欄位是否一致
                        // 取兩資料表 WM_all_account, WM_history_user_account 共同欄位
                        $errorAccountCols = chk2AccountTableCols();
                        
                        for ($i = 0; $i < $del_num; $i++) {

                            $res = checkUsername($user[$i]);
                            if ($user[$i] == sysRootAccount) $res = 4;

                            if (($res == 0) || ($res == 1) || ($res == 3) || ($res == 4)) {
                                $v1 = 0;
                            } else {
                                list($v1) = dbGetStSr('WM_user_account', 'count(*)', "username='{$user[$i]}'", ADODB_FETCH_NUM);
                            }

                            if ($v1 > 0) {
                                if (in_array($user[$i], $teachers))
                                    $res = 11;
                                elseif (in_array($user[$i], $directors))
                                    $res = 12;
                                elseif (in_array($user[$i], $managers))
                                    $res = 13;
                                elseif ($errorAccountCols === TRUE)
                                    $res = 14;
                                else
                                    removeUserAction($user[$i]);
                            } elseif ($v1 == 0 && $res == 2) {
                                $res = 0;
                            } elseif ($v1 == 0 && $res == 0) {
                                list($user_count) = dbGetStSr('WM_user_account', 'count(*)', "username='{$user[$i]}'", ADODB_FETCH_NUM);
                                if ($user_count > 0) {
                                    removeUserAction($user[$i]);
                                    $res = 2;
                                }
                            }

                            if ($res == 2) {
                                $suc++;
                                $msg = $MSG['delete_success'][$sysSession->lang];
                            } elseif ($res == 0) {
                                $noexist++;
                                $msg = $MSG['account_not_exist'][$sysSession->lang] . ',' . $MSG['delete_fail'][$sysSession->lang];
                            } elseif ($res == 1) {
                                $noexist++;
                                $msg = $MSG['system_reserved'][$sysSession->lang];
                            } else {
                                $fau++;
                                if ($res == 3) {
                                    $msg = $MSG['format_not_match'][$sysSession->lang];
                                } elseif ($res == 4) {
                                    $msg = $MSG['system_reserved'][$sysSession->lang];
                                } elseif ($res == 11) {
                                    $msg = $MSG['is_teacher'][$sysSession->lang];
                                } elseif ($res == 12) {
                                    $msg = $MSG['is_director'][$sysSession->lang];
                                } elseif ($res == 13) {
                                    $msg = $MSG['is_administrator'][$sysSession->lang];
                                } elseif ($res == 14) {
                                    $msg = $MSG['error_account_cols'][$sysSession->lang];
                                } else {
                                    $msg = 'unknown error';
                                }
                            }

                            $log_msg .= $user[$i] . $msg . '; ';

                            $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

                            showXHTML_tr_B($col);
                            showXHTML_td('', $user[$i]);
                            showXHTML_td('', str_replace(' ', '', $msg));
                            showXHTML_tr_E();
                        }

                        wmSysLog($sysSession->cur_func,$sysSession->school_id,0,'0','manager',$_SERVER['SCRIPT_FILENAME'], $actMsg . '; ' . $log_msg);

                        $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        showXHTML_tr_B($col);
                            showXHTML_td('', $MSG['success'][$sysSession->lang]);
                            showXHTML_td('', $suc);
                        showXHTML_tr_E();

                        $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        showXHTML_tr_B($col);
                            showXHTML_td('', $MSG['noexist'][$sysSession->lang]);
                            showXHTML_td('', $noexist);
                        showXHTML_tr_E();

                        $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        showXHTML_tr_B($col);
                            showXHTML_td('', $MSG['fail'][$sysSession->lang]);
                            showXHTML_td('', $fau);
                        showXHTML_tr_E();

                        $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        showXHTML_tr_B($col);
                            showXHTML_td_B('');
                              //  showXHTML_input('button', '', $MSG['print'][$sysSession->lang], '', 'id="btn" class="button01" onclick="listPrint()"');
                              showXHTML_input('button', '', $act_back_Msg, '', 'id="btn" class="cssBtn" onclick="go_list()"');
                            showXHTML_td_E();
                        showXHTML_tr_E();

                    showXHTML_table_E();

                showXHTML_td_E();

            showXHTML_tr_E();

        showXHTML_table_E();

    showXHTML_body_E();

    flush();
    removeUserAction(null, true); // 真正執行資料庫刪除動作
?>
