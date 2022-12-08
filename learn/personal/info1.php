<?php
	/**
	 * 儲存個人設定
	 *
	 * 建立日期：2002/02/25
	 * @author  ShenTing Lin
	 * @version $Id: info1.php,v 1.1 2010/02/24 02:39:10 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/breeze/global.php');
	require_once(sysDocumentRoot . '/breeze/doUpdatePwd.php');
	require_once(sysDocumentRoot . '/mooc/models/school.php');  //使用 getSchoolStudentMooc
	
	$sysSession->cur_func='400400500';
	$sysSession->restore();
	if (!aclVerifyPermission(400400500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	if (!isset($DIRECT_MEMBER) || empty($username)) {
		$username = $sysSession->username;
		$uri_target = 'info.php';
		$uri_parent = 'about:blank';
	}
	// 檢查 ticket 是不是吻合
	$ticket = md5($username . $sysSession->school_id . $sysSession->ticket);
	if ($ticket != trim($_POST['ticket'])) {
		echo 'Access deny.';
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'others', $_SERVER['PHP_SELF'], '拒絕存取!');
	    exit();
	}
	
	$rsSchool = new school();
	
	// 不能隱藏的欄位
	$not_hidden = array('last_name','first_name','email');

	$MyData = array(
			'password'       => '', 'last_name'      => '', 'first_name'     => '',
			'gender'         => '', 'birthday'       => '', 'personal_id'    => '',
			'picture'        => '', 'email'          => '', 'homepage'       => '',
			'home_tel'       => '', 'home_fax'       => '', 'home_address'   => '',
			'office_tel'     => '', 'office_fax'     => '', 'office_address' => '',
			'cell_phone'     => '', 'company'        => '', 'department'     => '',
			'title'          => '', 'language'       => '', 'theme'          => '',
			'msg_reserved'	 =>	'',	'hid'            => ''
		);

	$sqls = '';
	foreach($MyData as $key => $value) {
		if ($key == 'picture') continue;
		$val = '';
		switch ($key) {
			case 'password' :
				$val = trim($_POST[$key]);
				if (!empty($val)) $val = md5($val);
				break;
			case 'birthday' :
				$val = trim($_POST['birthday']);
				break;
			case 'hid' :
				if (is_array($_POST[$key])){
					$val = array_sum($_POST[$key]);
				}else{
					$val = 0;
				}
				$hid = $val;
				break;
			case 'last_name':
			case 'first_name':
				$val = Filter_Spec_char(trim($_POST[$key]));
				break;
			default :
				$val = trim($_POST[$key]);
		}
		if (($key == 'password') && empty($val)) continue;
		$sqls .= "{$key}='{$val}', ";
	}
	$sqls = substr($sqls, 0, -2);

	$val = trim($_POST['password']);
	if (!empty($val)) {
		$val = md5($val);
		$sqls = "password='{$val}', {$sqls}";
		if (breeze == 'Y')
		{
			doUpdateBreezePwd($username, substr($val,0,10));
		}
	}

	// 更新個人資料 (Begin)
	//$sysConn->BeginTrans();
	if ($username == sysRootAccount && $sysSession->username != sysRootAccount) {
	   wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 2, 'others', $_SERVER['PHP_SELF'], '"'.sysRootAccount .'" account only can be modified by himself.');
	   die('"'.sysRootAccount .'" account only can be modified by himself.');
	}
	$RS = dbSet('WM_user_account', $sqls, "username='{$username}'");
	wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 0, 'others', $_SERVER['PHP_SELF'], '更新個人設定!');
		// 更新個人照片 (Begin)
	$img_error = 0;
	if (is_uploaded_file($_FILES['picture']['tmp_name'])) {
		switch ($_FILES['picture']['type']) {
			case 'image/gif'   : case 'image/bmp' :
			case 'image/jpeg'  : case 'image/png' :
			case 'image/pjpeg' :
				if ($_FILES['picture']['size'] < 51200) {
					$filename = $_FILES['picture']['tmp_name'];

					$pic = file_get_contents($filename);
					dbNew('WM_user_picture', 'username, picture', "'{$username}', empty_blob()");
					dbNew('WM_user_picture', 'username, picture', "'{$username}', null");
					$sysConn->UpdateBlob('WM_user_picture', 'picture', $pic, "username='{$username}'");
				} else {
					$img_error = 2;
				}
				break;
			default :
				$img_error = 1;
		}
	}
		// 更新個人照片 (End)

	if ($RS) {
		$RS = dbSet('WM_all_account', $sqls, "username='{$username}'");
	}

	$blnReload = false;
	if ($_POST['language'] && in_array($_POST['language'], $sysAvailableChars)) {
		if ($_POST['language'] != $sysSession->lang) {
			$sysSession->lang = $_POST['language'];
			$sysSession->restore();
			$blnReload = true;
		}
	}
	//$sysConn->CommitTrans($RS);

	/*
	$userinfo = dbGetStSr('WM_user_account', '*', "username='{$username}'");
	// 移除舊的 sysSession
	dbDel('WM_session', "idx='{$_COOKIE['idx']}'");
	// 建立新的 sysSession
	$idx = $sysSession->init($userinfo);
	$_COOKIE['idx'] = $idx;
	$sysSession->restore();
	*/
	// 更新個人資料 (End)

	// 為了可以馬上看到更新後的結果，所以所有的訊息顯示皆移到底下，包含訊息的載入
	require_once(sysDocumentRoot .'/lang/personal.php');
	$MyData = array(
			'password'       => $MSG['password'][$sysSession->lang],
			'last_name'      => $MSG['last_name'][$sysSession->lang],
			'first_name'     => $MSG['first_name'][$sysSession->lang],
			'gender'         => $MSG['gender'][$sysSession->lang],
			'birthday'       => $MSG['birthday'][$sysSession->lang],
			'personal_id'    => $MSG['personal_id'][$sysSession->lang],
			'picture'        => $MSG['picture'][$sysSession->lang],
			'email'          => $MSG['email'][$sysSession->lang],
			'homepage'       => $MSG['homepage'][$sysSession->lang],
			'home_tel'       => $MSG['home_tel'][$sysSession->lang],
			'home_fax'       => $MSG['home_fax'][$sysSession->lang],
			'home_address'   => $MSG['home_address'][$sysSession->lang],
			'office_tel'     => $MSG['office_tel'][$sysSession->lang],
			'office_fax'     => $MSG['office_fax'][$sysSession->lang],
			'office_address' => $MSG['office_address'][$sysSession->lang],
			'cell_phone'     => $MSG['cell_phone'][$sysSession->lang],
			'company'        => $MSG['company'][$sysSession->lang],
			'department'     => $MSG['department'][$sysSession->lang],
			'title'          => $MSG['title'][$sysSession->lang],
			'language'       => $MSG['language'][$sysSession->lang],
			'msg_reserved'   => $MSG['msg_reserved'][$sysSession->lang],
			'theme'          => $MSG['theme'][$sysSession->lang],
			'hid'            => ''
		);

	$lang = strtolower($sysSession->lang);
	$js = <<< BOF
	function go() {
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.submit();
	}

	lang = "{$lang}";
	window.onload = function () {
		if ("{$blnReload}" == true) {
			var cid = ("{$sysSession->course_id}" == '' || "{$sysSession->course_id}" == '0') ? '10000000' :  "{$sysSession->course_id}";
			var gEnv = 1;
			switch("{$sysSession->env}") {
				case 'learn'   : gEnv = 1; break;
				case 'teach'   : gEnv = 2; break;
				case 'direct'  : gEnv = 3; break;
				case 'academic': gEnv = 4; break;
			}
			parent.chgCourse(cid, 0, gEnv, 'SYS_06_01_003');
		}
		// rebMenu(lang);
	};

BOF;

	showXHTML_head_B($MSG['tabs_personal'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/learn/personal/lib.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');
		showXHTML_table_B('width="760" align="center" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['tabs_personal_save'][$sysSession->lang], 'tabsSet');
					if (!isset($DIRECT_MEMBER)) {
						$ary[] = array($MSG['tabs_tagline'][$sysSession->lang], 'tabsTag', 'doFunc(2)');
						// student_mooc 為 0 時，才顯示我的學習中心
						if ($rsSchool->getSchoolStudentMooc($sysSession->school_id) == 0) {
							$ary[] = array($MSG['tabs_mycourse_manage'][$sysSession->lang], 'tabsMyCourse',  'doFunc(3)');
						}
					}
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" class="bg01"');

					showXHTML_table_B('width="760" align="center" border="0" cellspacing="1" cellpadding="3" id="MySet" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td_B('colspan="3"');
								$msg = ($RS) ? $MSG['update_success'][$sysSession->lang] : $MSG['update_fail'][$sysSession->lang];
								echo $sysSession->realname . '(' . $username . ') > ' . $MSG['msg_personal_update'][$sysSession->lang] . ' > '  . $msg;
							showXHTML_td_E('');
						showXHTML_tr_E('');

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('align="center" nowrap="noWrap"', $MSG['item'][$sysSession->lang]);
							showXHTML_td('align="center" nowrap="noWrap"', $MSG['hidden'][$sysSession->lang]);
							showXHTML_td('width="500" align="center" nowrap="noWrap"', $MSG['content'][$sysSession->lang]);
						showXHTML_tr_E('');

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('align="right" nowrap="noWrap"', $MSG['username'][$sysSession->lang]);
							showXHTML_td('align="center"', '&nbsp;');
							showXHTML_td('', $username);
						showXHTML_tr_E('');

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('align="right" nowrap="noWrap"', $MSG['account_deadline'][$sysSession->lang]);
							showXHTML_td('align="center"', '&nbsp;');
							//  帳號使用期限
							list($begin_time,$expire_time) = dbGetStSr('WM_sch4user', 'begin_time,expire_time', "school_id={$sysSession->school_id} and username='{$username}'", ADODB_FETCH_NUM);
							$temp = $MSG['from2'][$sysSession->lang] . ((empty($begin_time) || $begin_time == '0000-00-00') ?$MSG['now'][$sysSession->lang]:$begin_time) . '<br>' .
									$MSG['to2'][$sysSession->lang] . ((empty($expire_time) || $expire_time == '0000-00-00' )?$MSG['forever'][$sysSession->lang]:$expire_time);
							showXHTML_td('', $temp);
						showXHTML_tr_E('');


						$fhid = 1;
						reset($MyData);
						foreach($MyData as $key => $value) {

							if ($key == 'hid') continue;

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('align="right" nowrap="noWrap"', $value);
								showXHTML_td_B('align="center"');
									switch ($key) {
										case 'password' :
										case 'language' :
										case 'theme' :
											echo '&nbsp;';
											break;
										default:
											if (in_array($key,$not_hidden)){
												echo '&nbsp;';
											}else{
												echo ($hid&$fhid) ? 'V' : '&nbsp;';
											}
											$fhid = $fhid * 2;
									}
								showXHTML_td_E('');
								showXHTML_td_B('');
									switch ($key) {
										case 'password' :
											echo $MSG['not_open'][$sysSession->lang];
											break;
										case 'gender' :
											echo (trim($_POST[$key]) == 'M') ? $MSG['male'][$sysSession->lang] : $MSG['female'][$sysSession->lang];
											break;
										case 'birthday' :
											echo trim($_POST['birthday']);
											break;
										case 'picture' :
											if ($img_error > 0) {
												echo ($img_error == 2) ? $MSG['pic_size_large'][$sysSession->lang] : $MSG['pic_format_illegal'][$sysSession->lang];
												echo '<br />';
											}
											echo '<img src="showpic.php?' . uniqid('') . '" borer="0" align="absmiddle" onload="chkPic(this)">';
											break;
										case 'language' :
											$sel = array(
													'Big5'       =>$MSG['lang_big5'][$sysSession->lang],
													'en'         =>$MSG['lang_en'][$sysSession->lang],
													'GB2312'     =>$MSG['lang_gb'][$sysSession->lang],
													'EUC-JP'     =>$MSG['lang_jp'][$sysSession->lang],
													'user_define'=>$MSG['lang_user'][$sysSession->lang]
												);
											$val = trim($_POST[$key]);
											echo $sel[$val];
											break;
										case 'msg_reserved':
											echo (intval($_POST[$key]) == 1)? $MSG['reserved'][$sysSession->lang] : $MSG['not_reserved'][$sysSession->lang];
											break;
										default:
											echo stripslashes($_POST[$key]);
									}
								showXHTML_td_E('');
							showXHTML_tr_E('');
						}
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="3" align="center"');
								showXHTML_input('button', '', $MSG['return_personal'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'info.php\')"');
								if (isset($DIRECT_MEMBER)) {
									showXHTML_input('button' , '', $MSG['btn_return_member_detail'][$sysSession->lang], '', 'onclick="go();"');
								}
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');

				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
		if (isset($DIRECT_MEMBER)) {
			//  學員資訊
			showXHTML_form_B('action="' . $uri_parent . '" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
				showXHTML_input('hidden', 'msgtp', '1', '', '');
				showXHTML_input('hidden', 'user', $username, '', '');
			showXHTML_form_E();
		}
	showXHTML_body_E('');
?>
