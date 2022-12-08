<?php
    /**************************************************************************************************
     *                                                                                                *
     *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
     *                                                                                                *
     *        Programmer: Wiseguy Liang                                                         *
     *        Creation  : 2003/06/09                                                            *
     *        work for  : 人員管理 - 學員統計 - 上站動作                                                                      *
     *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
     *      $Id: stud_log.php,v 1.1 2010/02/24 02:40:31 saly Exp $:                                                                                          *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/teach_student.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/username.php');

    $sysSession->cur_func = '1500400100';
    $sysSession->restore();
    if (!aclVerifyPermission(1500400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    }

    $course_id = defined('Course_ID') ? Course_ID : $sysSession->course_id;

    $cnt = aclCheckRole($_GET['user'], $sysRoles['all'], $course_id);
    if ($cnt <= 0) die('Access Deny!');

    $board_ary = dbGetCol('WM_bbs_boards as b, WM_bbs_posts as p', 'distinct b.board_id', "b.owner_id={$course_id} and b.board_id = p.board_id and p.poster = '{$_GET['user']}'");

    // MIS#41832 整合老師環境的goto office記錄
    /*$rs = dbGetStMr('WM_log_classroom as L left join WM_acl_function as F on L.function_id=F.function_id',
    'L.function_id,F.caption,L.log_time,L.remote_address',
    "L.username='" . $_GET['user'] . "' and (L.department_id=" . $course_id. " or L.department_id in ('". implode("','", $board_ary). "'))",
    ADODB_FETCH_ASSOC);*/
    
    // 學習環境的log
    $classLogQuery = sprintf("
        select L.function_id,F.caption,L.log_time,L.remote_address from 
        WM_log_classroom as L left join WM_acl_function as F on L.function_id=F.function_id
        where L.username='%s' and (L.department_id=%d or L.department_id in ('%s'))",
        mysql_escape_string($_GET['user']),
        $course_id,
        implode("','", $board_ary)
    );

    // 進教師環境的log
    $gotoOfficeLogQuery = sprintf("
        SELECT L.function_id, F.caption, L.log_time, L.remote_address 
        FROM WM_log_teacher AS L LEFT JOIN WM_acl_function AS F ON L.function_id = F.function_id 
        WHERE L.function_id=2500200200 AND L.username ='%s' AND (L.department_id=%d)",
        mysql_escape_string($_GET['user']), $course_id
    );

    $rs = $sysConn->Execute("select * from ({$classLogQuery} UNION {$gotoOfficeLogQuery}) as UNIONLOG order by log_time desc");

        // #47205 Chrome 頁面顯示亂碼
    $sysCL = array('Big5'=>'zh-tw','en'=>'en','GB2312'=>'zh-cn');
        $ACCEPT_LANGUAGE = $sysCL[$sysSession->lang];
        if (empty($ACCEPT_LANGUAGE)) $ACCEPT_LANGUAGE = 'zh-tw';

    // 開始 output HTML
    showXHTML_head_B($MSG['student_info'][$sysSession->lang]);

    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_head_E();
    showXHTML_body_B();
        showXHTML_table_B('width="400" border="0" cellspacing="0" cellpadding="0" id="mt" align="center"');
            showXHTML_tr_B();
              showXHTML_td_B();
                $ary[] = array($_GET['user'] . '-' . $MSG['online_operate'][$sysSession->lang], 'tabsSet',  '');
                showXHTML_tabs($ary, 1);
              showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_tr_B();
                showXHTML_td_B('valign="top" ');

                    showXHTML_table_B('width="400" id="studentListTable" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');

                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td('', $MSG['login_date'][$sysSession->lang]);

                            showXHTML_td('', $MSG['login_ip'][$sysSession->lang]);

                            showXHTML_td('', $MSG['login_action'][$sysSession->lang]);
                        showXHTML_tr_E();
                        if ($rs->RecordCount() > 0){
                            while ($rec = $rs->FetchRow()) {

                                $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

                                showXHTML_tr_B($col);

                                    showXHTML_td('', $rec['log_time']);

                                    showXHTML_td('', $rec['remote_address']);

                                    showXHTML_td('', $rec['caption']);

                                showXHTML_tr_E();
                            }
                        }else{
                            $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

                            showXHTML_tr_B($col);

                                showXHTML_td_B('colspan="3" align="center"', $MSG['no_login_data'][$sysSession->lang]);

                            showXHTML_tr_E();
                        }

                        $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

                        showXHTML_tr_B($col);

                            showXHTML_td_B('colspan="3" align="center"');
                                showXHTML_input('button', '', $MSG['close'][$sysSession->lang]       , '', ' id="btnSel1" class="cssBtn" onclick="window.close();"');
                                showXHTML_input('button', '', $MSG['download_all'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\'stud_detail_download.php?type=6&user='.$_GET['user'].'\');"');
                            showXHTML_td_E();

                        showXHTML_tr_E();

                    showXHTML_table_E();

                showXHTML_td_E();

            showXHTML_tr_E();
        showXHTML_table_E();

    showXHTML_body_E('');


?>
