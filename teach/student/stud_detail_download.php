<?php
    /**
     * 檔案說明
     *    辦公室 - 人員管理 - 到課統計 - 詳細資料 - 下載
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      Edi Chen <edi@sun.net.tw>
     * @copyright   2000-2007 SunNet Tech. INC.
     * @version     CVS: $Id: stud_detail_download.php,v 1.1 2010/02/24 02:40:30 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2007-07-13
     */
     
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/teach_student.php');
    require_once(sysDocumentRoot . '/lib/archive_api.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    
    if (!preg_match(Account_format, $_GET['user'])) die('Account Format Error!');
    
    switch($_GET['type'])
    {
        case 1 :    // 登入次數
            $type     = 'login_times';
            $contents = "\"{$MSG['login_time'][$sysSession->lang]}\",\"{$MSG['login_host'][$sysSession->lang]}\"\r\n";
            $rs       = dbGetStMr('WM_log_others', 
                                  'log_time, remote_address', 
                                  'username="'.$_GET['user'].'" and note="login success" order by log_time desc',
                                  ADODB_FETCH_ASSOC);
            break;
        case 2 :    // 上課次數
            $type     = 'study_times';
            $contents = "\"{$MSG['login_time'][$sysSession->lang]}\",\"{$MSG['login_host'][$sysSession->lang]}\"\r\n";
            $rs       = dbGetStMr('WM_log_classroom',
                                  'log_time, remote_address',
                                  'username="'.$_GET['user'].'" and note="Goto course course_id='.$sysSession->course_id.'" order by log_time desc',
                                  ADODB_FETCH_ASSOC);
            break;
        case 3 :    // 張貼篇數
            $type     = 'post_times';
            $contents = "\"{$MSG['login_time'][$sysSession->lang]}\",\"{$MSG['board_name'][$sysSession->lang]}\",\"{$MSG['subject'][$sysSession->lang]}\"\r\n";
            $rs       = dbGetStMr('WM_bbs_boards as B join WM_bbs_posts as P on B.board_id = P.board_id',
                                  'P.pt, B.bname, P.subject',
                                  'Left(B.owner_id, 8) = '.$sysSession->course_id.' and P.poster = "'.$_GET['user'].'" order by P.pt desc',
                                  ADODB_FETCH_ASSOC);
            break;
        case 4 :    // 閱讀時數
            $type     = 'read_time';
            $contents = "\"{$MSG['learn_page'][$sysSession->lang]}\",\"{$MSG['learn_time'][$sysSession->lang]}\"\r\n";
            $rs       = dbGetStMr('WM_record_reading',
                                  'activity_id,sum(UNIX_TIMESTAMP(over_time)-UNIX_TIMESTAMP(begin_time)+1) as st',
                                  'course_id='.$sysSession->course_id.' and username="'.$_GET['user'].'" group by activity_id order by st desc ',
                                  ADODB_FETCH_ASSOC);
            break;
        case 5 :    // 閱讀頁數
            $type     = 'read_page';
            $contents = "\"{$MSG['learn_page'][$sysSession->lang]}\",\"{$MSG['learn_times'][$sysSession->lang]}\"\r\n";
            $rs       = dbGetStMr('WM_record_reading',
                                  'activity_id,count(*) as times',
                                  'course_id='.$sysSession->course_id.' and username="'.$_GET['user'].'" group by activity_id order by times desc',
                                  ADODB_FETCH_ASSOC);
            break;
        case 6 :    // 上站動作
            $type     = 'actions';
            $contents = "\"{$MSG['login_date'][$sysSession->lang]}\",\"{$MSG['login_ip'][$sysSession->lang]}\",\"{$MSG['login_action'][$sysSession->lang]}\"\r\n";
            
            // MIS#41832 整合老師環境的goto office記錄
            /*$rs       = dbGetStMr('WM_acl_function as F join WM_log_classroom as L on F.function_id=L.function_id',
            'L.log_time,L.remote_address,F.caption',
            'L.username="' . $_GET['user'] . '" and L.department_id=' . $sysSession->course_id,
            ADODB_FETCH_ASSOC);*/
            
            $board_ary = dbGetCol('WM_bbs_boards as b, WM_bbs_posts as p', 'distinct b.board_id', "b.owner_id={$sysSession->course_id} and b.board_id = p.board_id and p.poster = '{$_GET['user']}'");
            // 學習環境的log
            $classLogQuery = sprintf("
            select L.log_time,L.remote_address,F.caption from
            WM_log_classroom as L left join WM_acl_function as F on L.function_id=F.function_id
            where L.username='%s' and (L.department_id=%d or L.department_id in ('%s'))",
            mysql_escape_string($_GET['user']),
            $sysSession->course_id,
            implode("','", $board_ary)
            );

            // 進教師環境的log
            $gotoOfficeLogQuery = sprintf("
            SELECT L.log_time, L.remote_address, F.caption 
            FROM WM_log_teacher AS L LEFT JOIN WM_acl_function AS F ON L.function_id = F.function_id
            WHERE L.function_id=2500200200 AND L.username ='%s' AND (L.department_id=%d)",
            mysql_escape_string($_GET['user']), $sysSession->course_id
            );

            $rs = $sysConn->Execute("select * from ({$classLogQuery} UNION {$gotoOfficeLogQuery}) as UNIONLOG order by log_time desc");
            
            break;
        default :
            die('Selection Error!');
    }
    
    if ($rs)
    {
        while($row = $rs->FetchRow())
        {
            if ($_GET['type'] == 3)
                $row['bname'] = fetchTitle($row['bname']);
            else if (($_GET['type'] == 4)||($_GET['type'] == 5)){
                
                list($val1)=dbGetStSr('WM_record_reading','title'," course_id={$sysSession->course_id} and username='{$_GET['user']}'  and activity_id = '{$row['activity_id']}' group by title order by over_time desc");
                $row['activity_id'] = $val1;
                
                if($_GET['type'] == 4){
                    $row['st'] = sec2timestamp($row['st']);
                }
            }else if ($_GET['type'] == 6)
                $row['caption'] = $row['caption'];
            $contents .= '"' . implode('","', $row) . '"' . "\r\n";
        }
    }
    
    $contents=str_replace('""','"',$contents);

    $fname = $sysSession->course_id . '-' . $_GET['user'] . '-' . $type;
    header('Content-Disposition: attachment; filename="' . $fname . '.zip"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Type: application/zip; name="' . $fname . '.zip"');
    $export_obj = new ZipArchive_php4($fname.'.zip');
    $export_obj->add_string(utf8_to_excel_unicode($contents), $fname . '.CSV');
    $export_obj->readfile();
    $export_obj->delete();
?>