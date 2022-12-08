<?php
    /**
     * 儲存課程
     *
     * 建立日期：2002/09/09
     * @author  ShenTing Lin
     * @version $Id: course_save.php,v 1.1 2010/02/24 02:38:20 saly Exp $
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    
    if ('add' == $_POST['action']) {
        require_once(sysDocumentRoot . '/academic/course/course_save.php');
    } else {
        define("ENV_TEACHER", true);
        require_once(sysDocumentRoot . '/teach/course/m_course_save.php');
    }
    exit;

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/academic/course/course_lib.php');
    require_once(sysDocumentRoot . '/lib/multi_lang.php');
    require_once(sysDocumentRoot . '/lang/course_manage.php');
    require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    if (sysEnableAppCoursePicture) {
        // APP課程圖片模組有啟用
        require_once(sysDocumentRoot . '/lang/app_course_manage.php');
    }

    $actType         = '';
    $title           = '';

    $lang            = array();
    $teacher         = '';
    $status          = 5;
    $cont_id         = '';
    $book            = '';    // 書籍
    $url             = 'http://';    // 參考連結
    $intro           = '';    // 簡介
    $credit          = '';    // 學分
    $n_limit         = '';    // 正式生人數
    $a_limit         = '';    // 旁聽生人數
    $usage           = 0;     // 使用率
    $quota           = '';    // Quota
    $ta_can_sets     = '';	// 允許教師更改的欄位
    $ta_can_sets_ary = array();	// 允許教師更改的欄位

    // 新增課程
    $ticket = md5('Create' . $sysSession->ticket . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
    if (trim($_POST['ticket']) == $ticket) {
        $actType = 'Create';
        $title = $MSG['title_add_course'][$sysSession->lang];

        /*** Bug.968開課限量(B) ***/
        if (defined("sysCourseLimit"))
        {
            if (intval(sysCourseLimit) > 0)
            {
                list($school_mail) = dbGetStSr('WM_school', 'school_mail', "school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);
                $schMail = '<a href="mailto:'.$school_mail.'">'.$MSG['title41'][$sysSession->lang].'</a>';
                list($cnt) = dbGetStSr('WM_term_course', 'count(*) as cnt', "status<9 and kind='course'", ADODB_FETCH_NUM);
                if (intval($cnt) > intval(sysCourseLimit))
                {
                    showXHTML_head_B($MSG['btn_add_course'][$sysSession->lang]);
                    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
                    showXHTML_head_E('');
                    showXHTML_body_B('');
                        $ary = array();
                        $ary[] = array($MSG['btn_add_course'][$sysSession->lang], 'tabCourseLimit');
                        echo '<div align="center">';
                        showXHTML_tabFrame_B($ary, 1, 'courseLimit', '', 'style="display:inline"');
                            showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
                                showXHTML_tr_B('class="cssTrEvn"');
                                    $str = str_replace(array('<%sysCourseLimit%>', '<%sysAdmin%>'),
                                                       array('&nbsp;<font color="#ff0000;">'.sysCourseLimit.'</font>&nbsp;', $schMail),
                                                       $MSG['title40'][$sysSession->lang]);
                                    showXHTML_td('align="left"', $str);
                                showXHTML_tr_E('');
                            showXHTML_table_E('');
                        showXHTML_tabFrame_E();
                        echo '</div>';
                    showXHTML_body_E('');
                    exit;
                }
            }
        }
        /*** Bug.968開課限量(E) ***/
    }

    // 修改課程
    $ticket = md5('Edit' . $sysSession->ticket . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
    if (trim($_POST['ticket']) == $ticket) {
        $actType = 'Edit';
        if (defined('ENV_TEACHER')) {
            $title = $MSG['tabs_course_set'][$sysSession->lang];
        } else {
            $title = $MSG['title_modify_course'][$sysSession->lang];
        }
    }

    if (empty($actType)) die($MSG['msg_access_deny'][$sysSession->lang]);
    if ($actType == 'Create')
        $sysSession->cur_func = '700400100';
    else
        $sysSession->cur_func = defined('ENV_TEACHER') ? '700400700' : '700400200';
    $sysSession->restore();

    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }

    // 目前所有的課程狀態，延續 WM2 的屬性
    $CourseStatusList = array(
            5 => $MSG['param_prepare'][$sysSession->lang],
            1 => $MSG['param_open_a'][$sysSession->lang],
            2 => $MSG['param_open_a_date'][$sysSession->lang],
            3 => $MSG['param_open_n'][$sysSession->lang],
            4 => $MSG['param_open_n_date'][$sysSession->lang],
            0 => $MSG['param_close'][$sysSession->lang]
            /* 6 => $MSG['param_adminstrator'][$sysSession->lang] */
        );

    /**
     * 儲存課程資訊的步驟
     *     1. 儲存課程資訊到     WM_term_course
     *     2. 建立第一個教材路徑 WM_term_path
     *     3. 建立課程群組關聯   WM_term_group
     *     4. 建立課程討論板     WM_bbs_boards
     *     5. 建立課程公告板     WM_bbs_boards
     *     6. 將課程討論板與課程公告板的 board_id 儲存到 WM_term_course
     *     7.
     **/
    while (list($key, $val) = each($_POST)) {
        if (preg_match('/^ckta_(.+)$/',$key, $regs)) {
            $ta_can_sets_ary[] = $regs[1];
        } else {
            if (is_string($_POST[$key]))
            {
                switch ($key)
                {
                    case 'credit' :
                        $_POST['credit'] = preg_match('/^\d+$/', $_POST['credit']) ? intval($_POST['credit']) : 'NULL';
                        break;
                    case 'fair_grade' :
                        break;
                    case 'status' :
                    case 'n_limit' :
                    case 'a_limit' :
                    case 'quota_limit' :
                    case 'content_id' :
                        $_POST[$key] = intval($val);
                        break;
                    case 'Big5'   :
                    case 'GB2312' :
                    case 'en'     :
                    case 'EUC-JP' :
                    case 'user_define' :
                        $lang[$key] = Filter_Spec_char(stripslashes(trim($val)));
                        break;
                    case 'texts':
                    case 'url':
                    case 'content':
                        break;
                    default:
                        $_POST[$key] = Filter_Spec_char(trim($val));
                }
            } // End if (is_string($_POST[$key]))
        }
    }

    if (count($ta_can_sets_ary) > 0) {
        $ta_can_sets = implode(',',$ta_can_sets_ary);
    } else if (defined('ENV_TEACHER')) {
        $tmp_id = sysDecode($_POST['csid']);
        list($ta_can_sets) = dbGetStSr('WM_term_course','ta_can_sets','course_id=' . $tmp_id, ADODB_FETCH_NUM);
        $ta_can_sets_ary = explode(',',$ta_can_sets);
    }

    // 教材編號
    if (defined('ENV_TEACHER')) {
        if(in_array('content_id',$ta_can_sets_ary)) {
            if (isset($_POST['ck_content_id'])) {
                $content_id = ($_POST['content_id'] == 0) ? '""' : $_POST['content_id'];
            } else {
                $content_id = '0';
            }
        }
    } else {
        if(isset($_POST['ck_content_id'])) {
            $content_id = ($_POST['content_id'] == 0) ? '""' : $_POST['content_id'];
        } else {
            $content_id = '0';
        }
    }

    // 多語系的欄位
    $caption = addslashes(serialize($lang));

    // 處理日期Begin
    $new = '';
    $modi = '';
    // 開始報名
    if (defined('ENV_TEACHER')) {
        if(in_array('en_begin',$ta_can_sets_ary)) {
            if (isset($_POST['ck_en_begin']) && trim($_POST['en_begin_date']) != '') {
                $en_begin = trim($_POST['en_begin_date']);
                $new .= "'{$en_begin}',";
                $modi .= "`en_begin`='{$en_begin}',";
            } else {
                $en_begin = 'NULL';
                $new .= "{$en_begin},";
                $modi .= "`en_begin`={$en_begin},";
            }
        } else {
            $en_begin = trim($_POST['en_begin']);
        }
    } else {
        if (isset($_POST['ck_en_begin']) && trim($_POST['en_begin_date']) != '') {
                $en_begin = trim($_POST['en_begin_date']);
                $new .= "'{$en_begin}',";
                $modi .= "`en_begin`='{$en_begin}',";
            } else {
                $en_begin = 'NULL';
                $new .= "{$en_begin},";
                $modi .= "`en_begin`={$en_begin},";
            }
    }
    // 報名截止
    if (defined('ENV_TEACHER')) {
        if(in_array('en_end',$ta_can_sets_ary)) {
            if (isset($_POST['ck_en_end']) && trim($_POST['en_end_date']) != '') {
                $en_end = trim($_POST['en_end_date']);
                $new .= "'{$en_end}',";
                $modi .= "`en_end`='{$en_end}',";
            } else {
                $en_end = 'NULL';
                $new .= "{$en_end},";
                $modi .= "`en_end`={$en_end},";
            }
        } else {
            $en_end = trim($_POST['en_end']);
        }
    } else {
        if (isset($_POST['ck_en_end']) && trim($_POST['en_end_date']) != '') {
            $en_end = trim($_POST['en_end_date']);
            $new .= "'{$en_end}',";
            $modi .= "`en_end`='{$en_end}',";
        } else {
            $en_end = 'NULL';
            $new .= "{$en_end},";
            $modi .= "`en_end`={$en_end},";
        }
    }

    if (defined('ENV_TEACHER')) {
        if(in_array('st_begin',$ta_can_sets_ary)) {
            if (isset($_POST['ck_st_begin']) && trim($_POST['st_begin_date']) != '') {
                $st_begin = trim($_POST['st_begin_date']);
                $new .= "'{$st_begin}',";
                $modi .= "`st_begin`='{$st_begin}',";
            } else {
                $st_begin = 'NULL';
                $new .= "{$st_begin},";
                $modi .= "`st_begin`={$st_begin},";
            }
        } else {
            $st_begin = trim($_POST['st_begin']);
        }
        if (in_array('st_end',$ta_can_sets_ary)) {
            if (isset($_POST['ck_st_end']) && trim($_POST['st_end_date']) != '') {
                $st_end = trim($_POST['st_end_date']);
                $new .= "'{$st_end}',";
                $modi .= "st_end='{$st_end}',";
            } else {
                $st_end = 'NULL';
                $new .= "{$st_end},";
                $modi .= "`st_end`={$st_end},";
            }
        } else {
            $st_end = trim($_POST['st_end']);
        }
    } else {
        if (isset($_POST['ck_st_begin']) && trim($_POST['st_begin_date']) != '') {
            $st_begin = trim($_POST['st_begin_date']);
            $new .= "'{$st_begin}',";
            $modi .= "`st_begin`='{$st_begin}',";
        } else {
            $st_begin = 'NULL';
            $new .= "{$st_begin},";
            $modi .= "`st_begin`={$st_begin},";
        }

        if (isset($_POST['ck_st_end']) && trim($_POST['st_end_date']) != '') {
            $st_end = trim($_POST['st_end_date']);
            $new .= "'{$st_end}',";
            $modi .= "st_end='{$st_end}',";
        } else {
            $st_end = 'NULL';
            $new .= "{$st_end},";
            $modi .= "`st_end`={$st_end},";
        }
    }

    $new  = substr($new, 0, -1);
    $modi = substr($modi, 0, -1);
    // 處理日期End

    $fair_grade = ($_POST['fair_grade'] == '') ? 60 : intval($_POST['fair_grade']);

    if ($actType == 'Create') {
    	if('ajax'==$_POST['method']) {
    		$ta_can_sets ='caption,content_id,st_begin,st_end,status,texts,url,content,n_limit,a_limit,fair_grade';
    	}
        // 預設通過條件的成績為啟用
//        $is_use = 'a:1:{i:0;s:12:"formal_score";}';
        
        // 先將資料儲存到資料庫中
        $fields = '`content_id`, `caption`, `teacher`, `kind`, `en_begin`, `en_end`, `st_begin`, `st_end`, `status`, ' .
                '`texts`, `url`, `content`, `credit`, `discuss`, `bulletin`, `n_limit`, `a_limit`, ' .
                '`quota_used`, `quota_limit`, `path`, `login_times`, `post_times`, `dsc_times`, `fair_grade`,`ta_can_sets`, `creator`';
        $value = "{$content_id}, '{$caption}', '{$_POST['teacher']}', 'course', " .
                "$new".", {$_POST['status']}, " .
                "'{$_POST['texts']}', '{$_POST['url']}', '{$_POST['content']}', " .
                "{$_POST['credit']}, NULL,  NULL, '{$_POST['n_limit']}', '{$_POST['a_limit']}', " .
                "0, '{$_POST['quota_limit']}', '', 0, 0, 0, {$fair_grade} ,'{$ta_can_sets}', '{$sysSession->username}'";

        $RS = dbNew('WM_term_course', $fields, $value);
        if ($sysConn->Affected_Rows()) {
            $csid = $sysConn->Insert_ID();
			
			/* 因 mysql5.7 重啟後會將auto_increment 變成1 加入防呆*/
			if($csid < 10000001){
				$csid_auto = $csid + 10000000;
				dbSet('WM_term_course',"course_id = '{$csid_auto}'","course_id = {$csid}");		
				$sysConn->Execute('ALTER TABLE WM_term_course AUTO_INCREMENT ='.($csid_auto+1));
				$csid = $csid_auto;
			}
			/* 因 mysql5.7 重啟後會將auto_increment 變成1 加入防呆*/
			
            // 建立課程目錄
            $SchCourse  = "/base/{$sysSession->school_id}/course";
            @mkdir (sysDocumentRoot . $SchCourse, 0755);
            $CoursePath = "/base/{$sysSession->school_id}/course/{$csid}";
            @mkdir (sysDocumentRoot . $CoursePath, 0755);
            @mkdir (sysDocumentRoot . $CoursePath . '/chat' , 0755);
            @mkdir (sysDocumentRoot . $CoursePath . '/board', 0755);
            @mkdir (sysDocumentRoot . $CoursePath . '/quint', 0755);
            @mkdir (sysDocumentRoot . $CoursePath . '/content', 0755);

            // 將課程的目錄儲存到資料庫
            dbSet('WM_term_course', "path='{$CoursePath}'", "course_id={$csid}");

            // 建立第一個教材路徑
            addTermPath($csid);

            // 建立課程討論板
            $bname = $MSG['discuss'];
            $board_id1 = addBoards($csid, $bname);
            if (!$board_id1) $board_id1 = 'NULL';

            // 建立課程公告板
            $bname = $MSG['bulletin'];
            $board_id2 = addBoards($csid, $bname);
            if (!$board_id2) $board_id2 = 'NULL';

            // 儲存討論板的 board_id
            dbSet('WM_term_course', "discuss={$board_id1}, bulletin={$board_id2}", "course_id={$csid}");

            // ********************** (BEGIN)
            // 新增預設討論室
            $chat_open     = '0000-00-00 00:00:00';
            $chat_close    = '0000-00-00 00:00:00';
            $chat_media    = 'disable';
            $chat_protocol = 'TCP';
            $chat_login    = 'N';

            $dd = array(
                    'title'      => serialize($MSG['sync_chat_room']), // Bug#1388 修改「新開討論室」為「同步討論室」 by Small 2006/09/07
                    'limit'      => 0,
                    'exitAct'    => 'forum',
                    'jump'       => 'deny',
                    'status'     => 'open',
                    'visibility' => 'visible',
                    'media'      => $chat_media,
                    'ip'         => '',
                    'port'       => 0,
                    'protocol'   => $chat_protocol,
                    'host'       => '',
                    'login'      => $chat_login
                );
            $rid = uniqid('');
            $owner = $csid;
            dbNew('WM_chat_setting',
                    '`rid`, `owner`, `title`, `host` , `get_host`, ' .
                    '`maximum`, `exit_action`, `jump`, `open_time`, `close_time`, ' .
                    '`state`, `visibility`, `media`, `ip`, `port`, `protocol`',
                    "'{$rid}', '{$owner}', '{$dd['title']}', '{$dd['host']}', '{$dd['login']}', " .
                    "{$dd['limit']}, '{$dd['exitAct']}', '{$dd['jump']}', '{$chat_open}', '{$chat_close}', " .
                    "'{$dd['status']}', '{$dd['visibility']}', '{$dd['media']}', '{$dd['ip']}', {$dd['port']}, '{$dd['protocol']}'"
                );
            // ********************** (END)
            $st_begin_str = $st_begin != 'NULL' && preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $st_begin) ? "'{$st_begin}'" : 'NULL';
            $st_end_str   = $st_end   != 'NULL' && preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $st_end)   ? "'{$st_end}'"   : 'NULL';
            // 新增的預設點名
            dbNew('WM_roll_call',
                'course_id, team_id, group_id, enable, role, mtType, mtFilter, mtOP, mtVal, frequence, freq_extra,' .
                'begin_time, end_time, mail_subject, mail_content, mail_attach, mail_cc',
                "{$csid}, 0, 0, 'disable', 'student','lesson', 'off', 'greater', '7', 'week', 'Saturday', {$st_begin_str}, {$st_end_str}, '{$MSG['roll_call_mail_subject_default1'][$sysSession->lang]}', '{$MSG['roll_call_mail_content_default1'][$sysSession->lang]}', '', 0"
              );

            dbNew('WM_roll_call',
                'course_id, team_id, group_id, enable, role, mtType, mtFilter, mtOP, mtVal, frequence, freq_extra,' .
                'begin_time, end_time, mail_subject, mail_content, mail_attach, mail_cc',
                "{$csid}, 0, 0, 'disable', 'student','exam', 'no', 'greater_equal', '1', 'week', 'Saturday', {$st_begin_str}, {$st_end_str}, '{$MSG['roll_call_mail_subject_default2'][$sysSession->lang]}', '{$MSG['roll_call_mail_content_default2'][$sysSession->lang]}', '', 0"
              );

            dbNew('WM_roll_call',
                'course_id, team_id, group_id, enable, role, mtType, mtFilter, mtOP, mtVal, frequence, freq_extra,' .
                'begin_time, end_time, mail_subject, mail_content, mail_attach, mail_cc',
                "{$csid}, 0, 0, 'disable', 'student','homework', 'no', 'greater_equal', '1', 'week', 'Saturday', {$st_begin_str}, {$st_end_str}, '{$MSG['roll_call_mail_subject_default3'][$sysSession->lang]}', '{$MSG['roll_call_mail_content_default3'][$sysSession->lang]}', '', 0"
              );

            dbNew('WM_roll_call',
                'course_id, team_id, group_id, enable, role, mtType, mtFilter, mtOP, mtVal, frequence, freq_extra,' .
                'begin_time, end_time, mail_subject, mail_content, mail_attach, mail_cc',
                "{$csid}, 0, 0, 'disable', 'student','questionnaire', 'no', 'greater_equal', '1', 'week', 'Saturday', {$st_begin_str}, {$st_end_str}, '{$MSG['roll_call_mail_subject_default4'][$sysSession->lang]}', '{$MSG['roll_call_mail_content_default4'][$sysSession->lang]}', '', 0"
              );
              
            if('ajax'==$_POST['method']) {
              	dbNew('WM_term_major', 'username,course_id,role,add_time', "'$sysSession->username',$csid,512,now()");
            }  

            $isSuccess = true;
        } else {
            $isSuccess = false;
        }
    } else if ($actType == 'Edit') {
        $csid = trim($_POST['csid']);
        $csid = sysDecode($csid);

        if (defined('ENV_TEACHER')) {
            $fields = "`content_id`='{$content_id}', `caption`='{$caption}', " .
                    ($modi == '' ? '' : "{$modi},").
                    "`status`={$_POST['status']}, `texts`='{$_POST['texts']}', " .
                    "`url`='{$_POST['url']}', `content`='{$_POST['content']}', " .
                    "`n_limit`='{$_POST['n_limit']}', `a_limit`='{$_POST['a_limit']}', " .
                    "`fair_grade`={$fair_grade}";

        } else {
            $fields = "`content_id`='{$content_id}', `caption`='{$caption}', `teacher`='{$_POST['teacher']}', " .
                    "{$modi}" . ", " .
                    "`status`={$_POST['status']}, `texts`='{$_POST['texts']}', " .
                    "`url`='{$_POST['url']}', `content`='{$_POST['content']}', " .
                    "`credit`={$_POST['credit']}, `n_limit`='{$_POST['n_limit']}', `a_limit`='{$_POST['a_limit']}', " .
                    "`quota_limit`='{$_POST['quota_limit']}', `fair_grade`={$fair_grade},`ta_can_sets`='{$ta_can_sets}'";
        }

        dbSet('WM_term_course', $fields, "course_id={$csid}");

        if ($sysConn->Affected_Rows() > 0) {
            $isSuccess = true;
        } else {
            $isSuccess = false;
        }

        // 由於直連課程功能會自動把 guest 加為旁聽生，因此若不允許旁聽則要把 guest 去除
        if ($_POST['status'] != '1' && $_POST['status'] != '2')
            dbDel('WM_term_major', 'username="guest" and course_id=' . $csid);
    }

    // 與行事曆同步 start
    //$sysConn->debug=true;
    $calendar_begin_type='course_begin';
    $calendar_end_type='course_end';
    $begin_cal_idx = $sysConn->GetOne("select idx from WM_calendar where relative_type='{$calendar_begin_type}' and relative_id={$csid}");
    $end_cal_idx = $sysConn->GetOne("select idx from WM_calendar where relative_type='{$calendar_end_type}' and relative_id={$csid}");
    $username=$csid;
    $type='course';
    $repeat = 'none';
    $repeat_begin='0000-00-00';
    $repeat_end='0000-00-00';
    if ($_POST['alert_check']=='1') {
        $alertType="email";
        $alertBefore= $_POST['alert_before'];
    } else {
        $alertType="";
        $alertBefore= 0;
    }
    if ($_POST['alert_check1']=='1') {
        $alertType1="email";
        $alertBefore1= intval($_POST['alert_before1']);
    } else {
        $alertType1="";
        $alertBefore1= 0;
    }
    $ishtml = "text";
    if ( !isset($_POST['ck_st_begin']) && $actType == 'Edit' && $begin_cal_idx ) { //開放作答日期沒有限制時要刪除舊的行事曆
        dbDel('WM_calendar', 'idx=' . $begin_cal_idx);
    }
    if ( isset($_POST['ck_st_begin']) && isset($_POST['ck_sync_st_begin'])) {
        if( $actType == 'Edit' && $begin_cal_idx ) {
            //刪除舊的行事曆
            dbDel('WM_calendar', 'idx=' . $begin_cal_idx);
        }
        $memo_date=trim($_POST['st_begin_date']);
        $timeBegin = 'NULL';
        $timeEnd   = 'NULL';
        $subject = $MSG['th_study_begin'][$sysSession->lang];
        $content = "";
        $fields = '`username`, `type`, `memo_date`, `time_begin`, `time_end`, '.
            '`repeat`, `repeat_freq`, `repeat_begin`, `repeat_end`, ' .
            '`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`,`relative_type`,`relative_id`';
        $values = "'{$username}', '{$type}','{$memo_date}', {$timeBegin}, {$timeEnd}" .
            ", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" .
            ", '{$alertType1}', {$alertBefore1}, '{$ishtml}', '{$subject}', '{$content}', NULL,'{$calendar_begin_type}','{$csid}'";
        dbNew('WM_calendar', $fields, $values);
    }

    if ( !isset($_POST['ck_st_end']) && $actType == 'Edit' && $end_cal_idx ) { //開放作答日期沒有限制時要刪除舊的行事曆
        dbDel('WM_calendar', 'idx=' . $end_cal_idx);
    }
    if ( isset($_POST['ck_st_end']) && isset($_POST['ck_sync_st_end'])) {
        if( $actType == 'Edit' && $end_cal_idx ) {
            //刪除舊的行事曆
            dbDel('WM_calendar', 'idx=' . $end_cal_idx);
        }
        $memo_date=trim($_POST['st_end_date']);
        $timeBegin = 'NULL';
        $timeEnd   = 'NULL';
        $subject = $MSG['th_study_end'][$sysSession->lang];
        $content = "";
        $fields = '`username`, `type`, `memo_date`, `time_begin`, `time_end`, '.
            '`repeat`, `repeat_freq`, `repeat_begin`, `repeat_end`, ' .
            '`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`,`relative_type`,`relative_id`';
        $values = "'{$username}', '{$type}','{$memo_date}', {$timeBegin}, {$timeEnd}" .
            ", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" .
            ", '{$alertType}', {$alertBefore}, '{$ishtml}', '{$subject}', '{$content}', NULL,'{$calendar_end_type}','{$csid}'";
        dbNew('WM_calendar', $fields, $values);
    }
    //$sysConn->debug=false;
    // 與行事曆同步 end

    if (sysEnableAppCoursePicture) {
        // APP課程圖片模組有啟用
        // 更新課程代表圖 (Begin)
        $appPictureInfoFile = sysDocumentRoot . sprintf('/user/%1s/%1s/%s/appCoursePictureData.txt', substr($sysSession->username, 0, 1), substr($sysSession->username, 1, 1), $sysSession->username);
        if ($actType === 'Create' && is_file($appPictureInfoFile) && file_get_contents($appPictureInfoFile) !== '') {
            $appCoursePictureInfo = explode('#', file_get_contents($appPictureInfoFile));

            if (is_file($appCoursePictureInfo[0])) {
                $pictureContent = addslashes(base64_encode(file_get_contents($appCoursePictureInfo[0])));

                $mimeFileType = $appCoursePictureInfo[1];

                $table = 'CO_course_picture';
                $fields = '`course_id`, `picture`, `mime_type`';
                $values = "{$csid}, '{$pictureContent}', '{$mimeFileType}'";
                dbNew($table, $fields, $values);

                // 新增完畢則刪除暫存檔
                unlink($appPictureInfoFile);
            }
        }
        // 更新課程代表圖 (End)
    }

    // 設定審核規則 (begin)
    if (getReviewSerial($csid) == -1) {
        dbNew('WM_review_sysidx', 'discren_id, flow_serial', "'{$csid}','{$_POST['review']}'");
    } else {
        dbSet('WM_review_sysidx', "flow_serial='{$_POST['review']}'", "discren_id={$csid}");
    }
    if ($sysConn->Affected_Rows() > 0 || $r === true) {
        $isSuccess = true || $isSuccess;
    } else {
        $isSuccess = false || $isSuccess;
    }
    // 設定審核規則 (end)

    $isGpSuccess = false;
    if (!defined('ENV_TEACHER') || in_array('cparent',$ta_can_sets_ary)) {
        // 儲存課程群組 (Begin)
        $cparent = explode(',', trim($_POST['cparent']));
        $arows = setCourse2Group($csid, $cparent);
        if ($arows > 0) {
            $isGpSuccess = true;
        } else {
            $isGpSuccess = false;
        }
        // 儲存課程群組 (End)
    }
    // 報名
    $en_begin = ($en_begin != 'NULL') ? $en_begin : $MSG['now'][$sysSession->lang];
    $en_end   = ($en_end   != 'NULL') ? $en_end   : $MSG['forever'][$sysSession->lang];
    // 上課
    $st_begin = ($st_begin != 'NULL') ? $st_begin : $MSG['now'][$sysSession->lang];
    $st_end   = ($st_end   != 'NULL') ? $st_end   : $MSG['forever'][$sysSession->lang];
    $contents = array(0 => $MSG['msg_not_use_content'][$sysSession->lang]);
    $contents += getContentList(0);
    $teacher = $_POST['teacher'];    // 教師
    $cont_id = $content_id; // 教材
    $status  = $_POST['status'];     // 狀態
    $review  = $_POST['review'];     // 狀態
    $book    = $_POST['texts'];      // 書籍
    $url     = $_POST['url'];        // 參考連結
    $intro   = $_POST['content'];    // 簡介
    $credit  = ($_POST['credit'] == 'NULL') ? '' : $_POST['credit'];     // 學分
    $n_limit = $_POST['n_limit'];    // 正式生人數
    $a_limit = $_POST['a_limit'];    // 旁聽生人數
    $usage   = $_POST['quota_used'];	 // 已使用空間
    $quota   = $_POST['quota_limit'];// 空間限制
    // 取得此課程屬於那些群組
    $csParent = getCourseParents($csid);
    $tmp  = array();
    foreach ($csParent as $key => $val) {
        $tmp[] = $val[$sysSession->lang];
    }
    $gps = implode(', ', $tmp);

    /* array(欄位型態, 長度限制, 標題, ID, 預設值, 說明) */
    $dd = array(
            array('title'   , 128, 25, $MSG['th_course_name'][$sysSession->lang]  , 'caption'    , $lang               , $MSG['th_alt_course_name'][$sysSession->lang]  ),
            array('text'    , 128, 25, $MSG['th_teacher'][$sysSession->lang]      , 'teacher'    , $teacher            , $MSG['th_alt_teacher'][$sysSession->lang]      ),
            array('select'  ,   0, 20, $MSG['th_content'][$sysSession->lang]      , 'content_id' , $contents           , '&nbsp;'      ),
            array('date'    ,  20, 20, $MSG['th_enroll_begin'][$sysSession->lang] , 'en_begin'   , $en_begin           , $MSG['th_alt_enroll_begin'][$sysSession->lang] ),
            array('date'    ,  20, 20, $MSG['th_enroll_end'][$sysSession->lang]   , 'en_end'     , $en_end             , $MSG['th_alt_enroll_begin'][$sysSession->lang]   ),
            array('date'    ,  20, 20, $MSG['th_study_begin'][$sysSession->lang]  , 'st_begin'   , $st_begin           , $MSG['th_alt_enroll_begin'][$sysSession->lang]  ),
            array('date'    ,  20, 20, $MSG['th_study_end'][$sysSession->lang]    , 'st_end'     , $st_end             , $MSG['th_alt_enroll_begin'][$sysSession->lang]    ),
            array('select'  ,   0,  0, $MSG['th_course_status'][$sysSession->lang], 'status'     , $CourseStatusList   , ''),
            array('select'  ,   0,  0, $MSG['th_review_name'][$sysSession->lang]  , 'review'     , getReviewRuleList($csid)   , ''),
            array('caption' ,   0,  0, $MSG['th_group'][$sysSession->lang]        , 'cparent'    , $gps                , ''        ),
            array('textarea',   0,  0, $MSG['th_book'][$sysSession->lang]         , 'texts'      , $book               , $MSG['th_alt_book'][$sysSession->lang]         ),
            array('text'    , 255, 40, $MSG['th_url'][$sysSession->lang]          , 'url'        , $url                , ''          ),
            array('textarea',   0,  0, $MSG['th_introduce'][$sysSession->lang]    , 'content'    , $intro              , $MSG['th_alt_introduce'][$sysSession->lang]    ),
            array('file'    ,   0,  0, $MSG['th_picture'][$sysSession->lang]      , 'picture'    , $intro              , $MSG['msg_picture'][$sysSession->lang]    ),
            array('text'    ,   3,  3, $MSG['th_credit'][$sysSession->lang]       , 'credit'     , $credit             , ''       ),
            array('text'    ,   6,  6, $MSG['th_student'][$sysSession->lang]      , 'n_limit'    , $n_limit            , $MSG['th_alt_student'][$sysSession->lang]      ),
            array('text'    ,   6,  6, $MSG['th_auditor'][$sysSession->lang]      , 'a_limit'    , $a_limit            , $MSG['th_alt_student'][$sysSession->lang]      ),
            array('caption' ,   0,  0, $MSG['th_usage'][$sysSession->lang]        , 'quota_used' , $usage              , ''        ),
            array('text'    ,  10, 10, $MSG['th_quota'][$sysSession->lang]        , 'quota_limit', $quota              , $MSG['th_alt_quota'][$sysSession->lang]        ),
            array('text'    ,  10, 10, $MSG['fair_grade'][$sysSession->lang]      , 'fair_grade' , $fair_grade         , ''   ),
        );
    if (!sysEnableAppCoursePicture) {
        // 如果APP課程圖片模組未啟用，則移除課程圖片這個項目
        array_splice($dd, 13, 1);
    }
    $msg  = ($actType == 'Edit') ? $MSG['title_modify_course'][$sysSession->lang] : $MSG['title_add_course'][$sysSession->lang];
    $msg .= ($isSuccess || $isGpSuccess) ? $MSG['save_successed'][$sysSession->lang] : $MSG['save_failed'][$sysSession->lang];
    wmSysLog($sysSession->cur_func, defined('ENV_TEACHER') ? $csid : $sysSession->school_id ,0 ,0, 'auto', $_SERVER['PHP_SELF'], $msg . $csid);

    $js = <<< BOF
    window.onload = function () {
        alert("{$msg}");
    };

    function go_list() {
        var obj = document.getElementById("listFm");
        if (obj != null) obj.submit();
    }
BOF;

    if('ajax'==$_POST['method']) {
    	$rtn['flag'] = $isSuccess;
    	$rtn['id'] = $csid;
    	$msg = json_encode($rtn);
    	echo $msg;
    } else {
	    // 開始呈現 HTML
	    showXHTML_head_B($title);
	    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		showXHTML_script('include', '/academic/stud/lib.js');
	    showXHTML_script('inline', $js);
	    showXHTML_head_E('');
	    showXHTML_body_B('');
	        $ary = array();
	        $ary[] = array($MSG['title_save_course'][$sysSession->lang], 'tabs1');
	        echo '<div align="center">';
	        showXHTML_tabFrame_B($ary, 1, 'actForm', '', 'style="display:inline" onsubmit="return false;"');
	            showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
	                showXHTML_tr_B('class="cssTrHead"');
	                    showXHTML_td('colspan="4"', $msg);
	                showXHTML_tr_E('');
	                showXHTML_tr_B('class="cssTrHead"');
	                    showXHTML_td('nowrap width="100" align="center" valign="top"',$MSG['title42'][$sysSession->lang]);
	                    if (!defined('ENV_TEACHER')) {
	                        showXHTML_td('nowrap width="100" align="center" valign="top"',$MSG['title43'][$sysSession->lang]);
	                    }
	                    showXHTML_td('nowrap align="center" valign="top"',$MSG['title44'][$sysSession->lang]);
	                showXHTML_tr_E('');
	
	                for ($i = 0; $i < count($dd); $i++) {
	                    if ( ($dd[$i][4] == 'quota_used') && ($actType == 'Create') ) continue;
	                    $extra = '';
	                    $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
	                    showXHTML_tr_B($col);
	                        $cols = '';
	                        $rows = '';
	
	                        showXHTML_td($rows . ' align="right" valign="top" class="cssTrHead"', $dd[$i][3]);
	
	                        if (!defined('ENV_TEACHER')) {
	                            showXHTML_td_B($rows . ' align="center"');
	                                if(in_array($dd[$i][4],$ta_can_sets_ary)) {
	                                    echo 'V';
	                                }
	                            showXHTML_td_E();
	                        }
	
	                        showXHTML_td_B($cols);
	                            switch ($dd[$i][0]) {
	                                case 'date' :
	                                    if (is_array($dd[$i][5])) {
	                                        echo implode("-", $dd[$i][5]);
	                                    } else {
	                                        echo $dd[$i][5];
	                                    }
	                                    break;
	
	                                case 'caption' :
	                                    if (($dd[$i][4] == 'quota_used') || ($dd[$i][4] == 'quota_limit'))
	                                        echo showUsage($dd[$i][5]);
	                                    else
	                                        echo $dd[$i][5];
	                                    break;
	
	                                case 'title' :
	                                    $multi_lang = new Multi_lang(true, $dd[$i][5]); // 多語系輸入框
	                                    $multi_lang->show(false);
	                                    break;
	
	                                case 'select' :
	                                    if ($dd[$i][4] == 'status') $val = $status;
	                                    if ($dd[$i][4] == 'review') $val = $review;
	                                    if ($dd[$i][4] == 'content_id') $val = $content_id;
	                                    echo $dd[$i][5][$val];
	                                    break;
	
									case 'file' :
	                                    $appCsid = base64_encode($csid);
	                                    echo '<span id="coursePictureArea"><img src="/lib/app_show_course_picture.php?courseId=' . $appCsid . '" id="coursePicture" name="coursePicture" borer="0" align="absmiddle" onload="picReSize()" loop="0" width="143" height="100"></span>';
										break;
	
	                                case 'textarea' :
	                                    echo nl2br($dd[$i][5]);
	                                    break;
	                                default :
	                                    if (($dd[$i][4] == 'quota_used') || ($dd[$i][4] == 'quota_limit'))
	                                        echo showUsage($dd[$i][5]);
	                                    else
	                                        echo $dd[$i][5];
	                                    if (($dd[$i][4] == 'n_limit') || ($dd[$i][4] == 'a_limit')) {
	                                        echo $MSG['people'][$sysSession->lang];
	                                    }
	
	                            }
	                            echo '&nbsp;';
	                        showXHTML_td_E('');
	
	                    showXHTML_tr_E('');
	                }
	                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
	                showXHTML_tr_B($col);
	                    showXHTML_td_B('colspan="4" align="center"');
	                        if (defined('ENV_TEACHER')) {
	                            showXHTML_input('button' , '', $MSG['btn_return_set'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\'course_property.php\')"');
	                        } else {
	                            showXHTML_input('button' , '', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="go_list();"');
	                        }
	                        if ($actType == 'Create') {
	                            showXHTML_input('button' , '', $MSG['btn_renew'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\'course_property.php\')"');
	                            showXHTML_input('button' , '', $MSG['btn_setTeacher'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\'course_setTeacher.php?csid='.$csid.'\')"');
	                        }
	                    showXHTML_td_E('');
	                showXHTML_tr_E('');
	            showXHTML_table_E('');
	        showXHTML_tabFrame_E();
	        echo '</div>';
	
	        showXHTML_form_B('action="course_list.php" method="post" enctype="multipart/form-data" style="display:none"', 'listFm');
	            showXHTML_input('hidden', 'ticket', $_POST['gid']   , '', '');
	            showXHTML_input('hidden', 'page'  , $_POST['page']  , '', '');
	            showXHTML_input('hidden', 'sortby', $_POST['sortby'], '', '');
                    showXHTML_input('hidden', 'keyword', $_POST['keyword']);
	        showXHTML_form_E('');
	    showXHTML_body_E('');
    }