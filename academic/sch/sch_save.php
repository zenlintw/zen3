<?php
	/**
	 * 儲存學校設定
	 *
	 * 建立日期：2003
	 * @author  ShenTing Lin
	 * @version $Id: sch_save.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/sch_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');

	/**
	 * 1. 檢查車票是否正確
  	 * 2. 檢查車票的種類，是新增還是修改
	 **/
	$actType     = '';
	$title       = '';
	$isSingle    = '';
	$message     = '';
	$isError     = false;
	$mustRestart = false;

	$_POST['ticket'] = trim($_POST['ticket']);
	$ticket = md5('Create' . $sysSession->ticket .  $sysSession->username);
	if ($_POST['ticket'] == $ticket) {
		$actType = 'Create';
		$title   = $MSG['btn_create_school'][$sysSession->lang];
	}

	$ticket = md5('Edit' . $sysSession->ticket . $sysSession->username . $_POST['sid'] . $_POST['shost']);
	if ($_POST['ticket'] == $ticket) {
		$actType = 'Edit';
		$title   = $MSG['tabs_modify_school'][$sysSession->lang];
	}

	$ticket = md5('Single' . 'Edit' . $sysSession->ticket . $sysSession->username . $_POST['sid'] . $_POST['shost']);
	if ($_POST['ticket'] == $ticket) {
		$actType  = 'Edit';
		$isSingle = 'Single';
		$title    = $MSG['tabs_modify_school'][$sysSession->lang];
	}

	if ($actType == '') {
	    die($MSG['access_deny'][$sysSession->lang]);
	}

	/**
	 * 參數檢查
	 **/
	foreach ($_POST as $key => $val)
	{
		switch ($key)
		{
			case 'sid':
			case 'guestLimit':
			case 'courseQuota':
			case 'doorQuota':
				$_POST[$key] = intval($val);
				break;
			case 'serhost':
				$res = preg_match('/^[\w-]+(\.[\w-]+)+$/', $val, $match);
				if (count($match) == 0)
				{
					$isError = true;
					$message = $MSG['access_deny'][$sysSession->lang];
				}
				break;
			case 'schname':
				$_POST[$key] = Filter_Spec_char($val, 'title');
				break;
			case 'lang':
				if (!in_array($val, $sysAvailableChars)) $_POST[$key] = $sysAvailableChars[0];
				break;
			case 'theme':
				$_POST[$key] = 'default';
				break;
			case 'allow_guest':
			case 'multi_login':
				$_POST[$key] = ($val == 'N') ? 'N' : 'Y';
				break;
			case 'canReg':
				$_POST[$key] = in_array($val, array('Y', 'N', 'C')) ? $val : 'Y';
				break;
			case 'instructRequire':
				$_POST[$key] = 'admonly';
				break;
			case 'share':
			    if (is_array($_POST[$key])){
			        $_POST[$key] = implode(',', $_POST[$key]);
			    }else{
			        $_POST[$key] = '';
			    }
			    break;
			case 'mycourse_view':
			    $mycourse_view = in_array($_POST['mycourse_view'],array('T','G'))?$_POST['mycourse_view']:'T';
			    break;
			default:
				$_POST[$key] = trim($val);
		}
	}

	$sysSession->cur_func = ($actType == 'Edit') ? '100300200' : '100300100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 設定車票
	setTicket();
	if (!isset($_POST['instructRequire'])) $_POST['instructRequire'] = 'admonly';

	if (!$isError) {
                $doorQuota = $_POST['doorQuota'] * 1024;
                $courseQuota = $_POST['courseQuota'] * 1024;
		switch ($actType) {
			case 'Create':
				// 檢查網域名稱是否已經有人使用了
				list($host_cnt) = dbGetStSr('WM_school', 'count(*)', "school_host='{$_POST[serhost]}'", ADODB_FETCH_NUM);
				if ($host_cnt > 0) {
					$message = $MSG['msg_domain_used'][$sysSession->lang];
					$isError = true;
				}

				// 取出 school_id
				list($sid) = dbGetStSr('WM_school', 'MAX(school_id) + 1 AS sid', '1', ADODB_FETCH_NUM);
				if ($sid <= 10001) $sid = 10002;
				// 開始新增一所學校

				// 複製相關的檔案

				// 將學校的設定寫到資料庫中
				$_POST['allow_guest'] = 'N';
				if ($isSingle == 'Single') {
					$fields = 'school_id, school_host, school_name, language, ' .
							  'theme, guest, multi_login, canReg, instructRequire, guestLimit, courseQuota,school_mail';
					$values = "'{$sid}', '{$_POST['serhost']}', '{$_POST['schname']}', " .
							  "'{$_POST['lang']}', '{$_POST['theme']}', '{$_POST['allow_guest']}', '{$_POST['multi_login']}', " .
							  "'{$_POST['canReg']}', '{$_POST['instructRequire']}', '{$_POST['guestLimit']}', '{$courseQuota}','{$_POST['school_mail']}'";
				} else {
					$fields = 'school_id, school_host, school_name, language, ' .
							  'theme, guest, multi_login, canReg, instructRequire, guestLimit, courseQuota, quota_limit, school_mail';
					$values = "'{$sid}', '{$_POST['serhost']}', '{$_POST['schname']}', " .
							  "'{$_POST['lang']}', '{$_POST['theme']}', '{$_POST['allow_guest']}', '{$_POST['multi_login']}', " .
							  "'{$_POST['canReg']}', '{$_POST['instructRequire']}', '{$_POST['guestLimit']}', '{$courseQuota}', '{$doorQuota}', '{$_POST['school_mail']}'";
				}

				dbNew('WM_school', $fields, $values);
				if (!$sysConn->Affected_Rows()) {
					$message = $MSG['msg_create_sch_fail'][$sysSession->lang];
					$isError = true;
				} else {
					$message = $MSG['msg_create_sch_success'][$sysSession->lang];
				}

				// 取得 mysql 外部執行檔 begin
				list($foo, $mysql_basedir) = $sysConn->GetRow('show variables like "basedir"');

				$mysql = $mysql_basedir . 'bin/mysql';
				if (!file_exists($mysql) || !is_executable($mysql))
				{
					$mysql = exec("sh -c 'PATH=/usr/local/mysql/bin:/usr/bin:/usr/local/bin:/home/apps/mysql/bin which mysql'");
					if (!preg_match('!^(/\w+)+$!', $mysql)) die('"mysql" not found.');
				}
				if (!file_exists($mysql) || !is_executable($mysql)) die('"mysql" not found or not executable.');
				$mysql .= (sysDBhost == 'localhost' ? ' -S /tmp/mysql.sock' : (' -h ' . sysDBhost)) .' -u ' . sysDBaccoount . ' -p' . sysDBpassword . ' -B -r --set-variable=max_allowed_packet=64M -f';
				// 取得 mysql 外部執行檔 end

				// 將目前學校的 table schema 複製給新學校 begin
				if ($fp = popen($mysql, 'w'))
				{
					$keep = $ADODB_FETCH_MODE;
					$ADODB_FETCH_MODE = ADODB_FETCH_NUM;

					$cur_school = sysDBprefix . $sysSession->school_id;
					$new_school = sysDBprefix . $sid;
					$sysConn->Execute('use ' . $cur_school);
					list(,$DB) = $sysConn->GetRow("SHOW CREATE DATABASE {$cur_school};");
					fwrite($fp, str_replace($cur_school, $new_school, $DB . "; use {$new_school};"));
					echo '<!--create school DB : ', $new_school, "\n";

					if ($tables = $sysConn->GetCol("SHOW TABLES FROM {$cur_school};"))
					{
						foreach ($tables as $table)
						{
						    list($t,$TL) = $sysConn->GetRow("SHOW CREATE TABLE {$table};");
						    fwrite($fp, preg_replace('/ AUTO_INCREMENT=\d+/i', '', $TL) . ';');
						    echo 'create table : ', $t, "\n";
						}
					}
					else
						echo $sysConn->ErrorNo(), ': ', $sysConn->ErrorMsg();

                    $ADODB_FETCH_MODE = $keep;
					fwrite($fp, "INSERT INTO `{$new_school}`.`WM_user_account` select * from `{$cur_school}`.`WM_user_account` where username in ('root','{$sysSession->username}');");
					fwrite($fp, "INSERT INTO `{$new_school}`.`WM_acl_bindfile` select * from `{$cur_school}`.`WM_acl_bindfile`;");
					fwrite($fp, "INSERT INTO `{$new_school}`.`WM_acl_function` select * from `{$cur_school}`.`WM_acl_function`;");
					fwrite($fp, "INSERT INTO `{$new_school}`.`WM_review_syscont` select * from `{$cur_school}`.`WM_review_syscont`;");
					fwrite($fp, "INSERT INTO `{$new_school}`.`WM_bbs_boards` select * from `{$cur_school}`.`WM_bbs_boards` where board_id=1000000001;");
					fwrite($fp, "INSERT INTO `{$new_school}`.`WM_news_subject` (news_id, board_id, type, visibility) VALUES (1, 1000000001, 'suggest', 'visible');");
					fwrite($fp, "INSERT INTO `{$new_school}`.`WM_term_subject` (course_id,board_id) VALUES ({$sid}, 1000000001);");
					fwrite($fp, "ALTER TABLE `{$new_school}`.`WM_class_main`             AUTO_INCREMENT = 1000001;");
					fwrite($fp, "ALTER TABLE `{$new_school}`.`WM_content`                AUTO_INCREMENT = 100001;");
					fwrite($fp, "ALTER TABLE `{$new_school}`.`WM_qti_exam_test`          AUTO_INCREMENT = 100000001;");
					fwrite($fp, "ALTER TABLE `{$new_school}`.`WM_qti_homework_test`      AUTO_INCREMENT = 100000001;");
					fwrite($fp, "ALTER TABLE `{$new_school}`.`WM_qti_questionnaire_test` AUTO_INCREMENT = 100000001;");
					fwrite($fp, "ALTER TABLE `{$new_school}`.`WM_term_course`            AUTO_INCREMENT = 10000001;");
					fclose($fp);
					echo 'db creation is complete. -->';
				}
				else
					echo 'mysql pipe open failure.';
				// 將目前學校的 table schema 複製給新學校 end

				include_once sysDocumentRoot . '/lib/wm3_config_class.php';
				$wm3_config = new WM3config;
				$wm3_config->reGenerateVirtualHostConfig();

				break;

			case 'Remove':
			    // 刪除目錄			 exec('rm -rf ' . sysDocumentRoot . '/base/' . $sid);
			    // 刪除資料庫   		$sysConn->Execute('drop database ' . sysDBprefix . $sid);
			    // 刪除 MASTER 相關  dbDel('WM_school', 'school_id=' . $sid);
			    // 刪除 MASTER 相關  dbDel('WM_master', 'school_id=' . $sid);
			    // 刪除 MASTER 相關  dbDel('WM_sch4user', 'school_id=' . $sid);
				break;

			case 'Edit':
			    // 判斷是否開放註冊
			    $canReg = (in_array($_POST['canReg'], array('Y','C')))? 'Y': 'N';
			    $canFbReg = ($_POST['fbregist']=='Y')? 'FB': '';
			    // FB 修改id, secret
			    $FBAPI = sprintf("c.canReg_fb_id='%s', c.canReg_fb_secret='%s', ",$_POST['FB_id'] ,$_POST['FB_secret']);
			    
				$sid = intval($_POST['sid']);
				$sqls = "w.school_host='{$_POST['serhost']}', w.school_name='{$_POST['schname']}', " .
				        "w.theme='{$_POST['theme']}', w.language='{$_POST['lang']}', " .
				        "w.guest='{$_POST['allow_guest']}', w.multi_login='{$_POST['multi_login']}', " .
				        "w.canReg='{$_POST['canReg']}', w.instructRequire='{$_POST['instructRequire']}' , ".
				        "w.guestLimit='{$_POST['guestLimit']}', w.courseQuota='{$courseQuota}', w.quota_limit='{$doorQuota}' , w.school_mail='{$_POST['school_mail']}', " .
				        $FBAPI .
				        "c.social_share='{$_POST['share']}', c.canReg_ext='{$canFbReg}',c.mycourse_view='{$mycourse_view}' ";
				        
				dbSet('WM_school w, CO_school c', $sqls, "w.school_id = c.school_id and w.school_id='{$sid}' and w.school_host='{$_POST['shost']}'");
				if (!$sysConn->Affected_Rows()) {
					$message = $MSG['msg_update_sch_fail'][$sysSession->lang];
					$isError = true;
				} else {
					$message = $MSG['msg_update_sch_success'][$sysSession->lang];
					if ($_POST['serhost'] != $_POST['shost'])
					{
						include_once sysDocumentRoot . '/lib/wm3_config_class.php';
						$wm3_config = new WM3config;
						$wm3_config->reGenerateVirtualHostConfig();
						$mustRestart = true;
					}
				}
				
				// 上傳圖片至 base/{sid}/door/tpl
				$picUpdate = 0;
				$fileArray = array(
    				'logo'          => 'logo.png',
    				'icon'          => 'icon.ico'
				);
				
				foreach ($fileArray as $k => $v) {
    				if (is_uploaded_file($_FILES[$k]["tmp_name"])) {
    				    $fileType=$_FILES[$k]["type"];
    				    if ($_FILES[$k]["error"] > 0 ) {
    				        $imgMsg = $MSG['msg_image_upload_fail'][$sysSession->lang];
    				        $isError = true;
    				    }
    				    if ($_FILES[$k]["type"] == 'image/png' || $_FILES[$k]["type"] == 'image/x-png' || $_FILES[$k]["type"] == 'image/pjpeg' || $_FILES[$k]["type"] == 'image/x-icon') {
    				        move_uploaded_file($_FILES[$k]["tmp_name"], sysDocumentRoot . "/base/" . $sid . '/door/tpl/' . $v);
    				        $picUpdate++;
    				    } else {
    				        $imgMsg = $MSG['msg_image_format_fail'][$sysSession->lang];
    				        $isError = true;
    				    }
    				}
				}
				if ($picUpdate > 0) {
				    $message = $MSG['msg_update_sch_success'][$sysSession->lang];
				}
				break;

			default:
				die($MSG['access_deny'][$sysSession->lang]);
		} // End switch ($actType)

		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], $message . ' school_id=' . $sid);
	} // End if (!$isError)
	// 檢查學校目錄
	if (@mkdir(sysDocumentRoot . "/base/{$sid}"        , 0755)) {	// 建立學校的主目錄
		@mkdir(sysDocumentRoot . "/base/{$sid}/door"   , 0755);		// 建立學校的 door
		@mkdir(sysDocumentRoot . "/base/{$sid}/board"  , 0755);		// 建立學校討論版夾檔存放的目錄
		@mkdir(sysDocumentRoot . "/base/{$sid}/quint"  , 0755);		// 建立學校經華區夾檔存放的目錄
		@mkdir(sysDocumentRoot . "/base/{$sid}/system" , 0755);		// 建立選單存放資料的地方
		@mkdir(sysDocumentRoot . "/base/{$sid}/door"   , 0755);		// 建立學校的 door
		@mkdir(sysDocumentRoot . "/base/{$sid}/course" , 0755);		// 建立課程存放的目錄 (期別)
		@mkdir(sysDocumentRoot . "/base/{$sid}/content", 0755);		// 建立教材存放的目錄
		@mkdir(sysDocumentRoot . "/base/{$sid}/class"  , 0755);		// 建立班級(部門)存放的目錄
		if ($fp = fopen(sysDocumentRoot . "/base/{$sid}/system/faq.xml", 'w'))
		{
		    fwrite($fp, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<allfaq date=\"2004-10-10 10:10\" />\n");
		    fclose($fp);
		}
		if ($fp = fopen(sysDocumentRoot . "/base/{$sid}/system/news.xml", 'w'))
		{
		    fwrite($fp, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<allnews date=\"2004-10-10 10:10\" />\n");
		    fclose($fp);
		}
	}

	$allow = array(
		'Y' => $MSG['status_allow'][$sysSession->lang],
		'N' => $MSG['status_deny'][$sysSession->lang]
	);

	$reg_allow = array(
		'Y' => $MSG['status_allow'][$sysSession->lang],
		'N' => $MSG['status_deny'][$sysSession->lang],
		'C' => $MSG['reg_check'][$sysSession->lang]
	);

	$require = array(
		'noncheck' => $MSG['cs_allow'][$sysSession->lang],
		'check'    => $MSG['cs_check'][$sysSession->lang],
		'admonly'  => $MSG['cs_deny'][$sysSession->lang]
	);

	// array(型態, 長度, 名稱, id, value, default value, extra, 說明);
	$school = array(
		 0 => array($MSG['item_school_name'][$sysSession->lang], 'schname'),
		 1 => array('Domain Name'                              , 'serhost'),
		 2 => array($MSG['school_mail'][$sysSession->lang]     , 'school_mail'),
		//  3 => array($MSG['school_academic'][$sysSession->lang]    , 'manager'),
		 4 => array($MSG['item_theme'][$sysSession->lang]      , 'theme'),
		 5 => array($MSG['item_language'][$sysSession->lang]   , 'lang'),
		 6 => array($MSG['item_guest'][$sysSession->lang]      , 'allow_guest'),
		 7 => array($MSG['item_guest_limit'][$sysSession->lang], 'guestLimit'),
		 8 => array($MSG['item_multi_login'][$sysSession->lang], 'multi_login'),
		 9 => array($MSG['item_register'][$sysSession->lang]   , 'canReg'),
		// 10 => array($MSG['item_require'][$sysSession->lang]    , 'instructRequire'),
		11 => array($MSG['item_quota'][$sysSession->lang]      , 'courseQuota'),
		12 => array($MSG['item_door_quota'][$sysSession->lang] , 'doorQuota')
	);

	showXHTML_head_B($MSG['html_title_save'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_head_E();
	showXHTML_body_B('');

		$ary = array();
		$ary[] = array($title, 'tabsTag');
		// $colspan = 'colspan="2"';
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'actForm', '', 'method="post" action="sch_priority.php" style="display: inline;"');
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHelp"');
					showXHTML_td('colspan="2"', $message);
				showXHTML_tr_E();

				//reset ($_POST);
				foreach ($school as $key => $val) {
					if (empty($val)) continue;
					//if ($key == 'ticket') $val = md5($actType . $sysSession->ticket .  $sysSession->username . $sid . $shost);
					$value = $_POST[$val[1]] . '&nbsp;';
					if ( ($val[1] == 'allow_guest')
						|| ($val[1] == 'multi_login')
						|| ($val[1] == 'canReg') ) {
						$value = $allow[$_POST[$val[1]]] . '&nbsp;';
					}
					if ($val[1] == 'canReg') {
						$value = $reg_allow[$_POST[$val[1]]] . '&nbsp;';
					}
					if ($val[1] == 'instructRequire') $value = $require[$_POST[$val[1]]] . '&nbsp;';
					if ($val[1] == 'courseQuota') $value .= 'MB';
					if ($val[1] == 'doorQuota')   $value .= 'MB';

					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $val[0]);
						showXHTML_td('width="70%"', $value);
					showXHTML_tr_E();
				}

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" align="center"');
						if ($isError) {
							reset ($_POST);
							while ( list($key, $val) = each($_POST) ) {
								if ($key == 'ticket') {
									if ($isSingle == 'Single')
										$val = md5($isSingle . 're' . $actType . $sysSession->ticket .  $sysSession->username . $_POST[sid] . $_POST[shost]);
									else
										$val = md5('re' . $actType . $sysSession->ticket .  $sysSession->username . $_POST[sid] . $_POST[shost]);
								}
								showXHTML_input('hidden', $key, $val, '', '');
							}
							showXHTML_input('submit', '', $MSG['btn_return'][$sysSession->lang], '', 'class="button01"');
						}
						if ($isSingle == 'Single') {
							$location = 'sch_single.php';
							$msg = $MSG['btn_school_setting'][$sysSession->lang];
						} else {
							$location = 'sch_list.php';
							$msg = $MSG['btn_return_list'][$sysSession->lang];
						}
						//$location = ($isSingle == 'Single') ? 'sch_single.php' : 'sch_list.php';
						showXHTML_input('button', '', $msg, '', 'class="cssBtn" onclick="window.location.replace(\'' . $location . '\')"');
						if ($actType == 'Create' || $mustRestart)
						showXHTML_input('button', '', $MSG['restart_web_server'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'sch_restart.php?restart\')"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>
