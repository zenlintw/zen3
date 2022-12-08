<?php
	/**
	 * 儲存個人設定
	 *
	 * 建立日期：2002/02/25
	 * @author  ShenTing Lin
	 * @version $Id: modify_stud_info2.php,v 1.1 2010/02/24 02:38:44 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/lang/personal.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	$sysSession->cur_func = '400400300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$username = $_POST['username'] ? preg_replace('/[^\w.-]+/', '', $_POST['username']) : $sysSession->username;

	// 檢查 ticket 是不是吻合
	$ticket = md5($username . $sysSession->school_id . $sysSession->ticket);


	if ($ticket != trim($_POST['ticket'])) {
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['illege_access'][$sysSession->lang]);
	}

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
	$passwd_chgd = false; // 判斷是否有變更密碼
	foreach($MyData as $key => $value) {
		if ($key == 'picture') continue;
		$val = '';
		switch ($key) {
			case 'password' :
				$val = trim($_POST[$key]);
				if (!empty($val)) {
					$val = md5($val);
					$passwd_chgd = true;
				}
				break;
			case 'birthday' :
				$val = trim($_POST['birthday']);
				break;
			case 'hid' :
                            // 避免全部都顯示時，array_sum(null)錯誤
                            if ($_POST[$key] === null) {
                                $_POST[$key] = array();
                            }
				$val = array_sum($_POST[$key]);
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


	// 更新個人資料 (Begin)
	//$sysConn->BeginTrans();
	if ($username == sysRootAccount && $sysSession->username != sysRootAccount) {
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 2, 'manager', $_SERVER['PHP_SELF'], '"' . sysRootAccount . '" account only can be modified by himself.');
		die( '"'. sysRootAccount . '" account only can be modified by himself.');
	}
	$RS2 = dbSet('WM_user_account', $sqls, "username='{$username}'");

	$update_num = $sysConn->Affected_rows();

	// 修改帳號使用期限
	$begin_time = 'NULL';
	if (isset($_POST['ck_begin_date'])) {
		$begin_time = trim($_POST['begin_date']);
	}

	$expire_time   = 'NULL';
	if (isset($_POST['ck_end_date'])) {
		$expire_time = trim($_POST['end_date']);
	}

    dbSet('WM_sch4user',"begin_time='$begin_time',expire_time='$expire_time'", 'school_id=' . $sysSession->school_id . " and username='" . $username . "'");

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

					list($pic_num) = dbGetStSr('WM_user_picture', 'count(*)', "username='{$username}'", ADODB_FETCH_NUM);

					if ($pic_num == 0){
					    dbNew('WM_user_picture', 'username, picture', "'{$username}', empty_blob()");
					    dbNew('WM_user_picture', 'username, picture', "'{$username}', null");
					    $sysConn->UpdateBlob('WM_user_picture', 'picture', $pic, "username='{$username}'");
					}else{
				        $sysConn->UpdateBlob('WM_user_picture', 'picture', $pic, "username='{$username}'");
				    }

				} else {
					$img_error = 2;
				}
				break;
			default :
				$img_error = 1;
		}
	}
	// 更新個人照片 (End)

	if ($RS2) {
		$RS = dbSet('WM_all_account', $sqls, "username='{$username}'");
	}
	//$sysConn->CommitTrans($RS);

	$userinfo = dbGetStSr('WM_user_account', '*', "username='{$username}'", ADODB_FETCH_ASSOC);

	wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], '更新使用者資料:' . $username);
	// 更新個人資料 (End)

	// 若是有變更密碼欄位,則寄信給該使用者 (Begin)
	if ($passwd_chgd && !empty($_POST['email'])) {
		$passwd_chg_mail_title = $MSG['passwd_changed_mail_title'][$sysSession->lang];
		$passwd_chg_mail_body = $MSG['passwd_changed_mail_body'][$sysSession->lang];

		$real_name = checkRealname($_POST['first_name'], $_POST['last_name']);

		list($school_name,$school_mail) = dbGetStSr('WM_school', 'school_name,school_mail', "school_id={$sysSession->school_id} and school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);

		if (empty($school_mail)){
			$school_mail = 	'webmaster@'. $_SERVER['HTTP_HOST'];
		}

		$passwd_chg_mail_title = str_replace('%SCHOOL_NAME%', $sysSession->school_name, $passwd_chg_mail_title);
		$passwd_chg_mail_body = strtr($passwd_chg_mail_body,
										array('%USERNAME%' 		=> $username,
											  '%REALNAME%' 		=> $real_name,
											  '%SCHOOL_NAME%'	=> $sysSession->school_name,
											  '%PASSWORD%'		=> $_POST['password'],
											  '%SCHOOL_HOST%'	=> $_SERVER['HTTP_HOST']
										)
								);
		$mail = buildMail('', $passwd_chg_mail_title, $passwd_chg_mail_body, 'html', '', '', '', '', false);
		$mail->from = mailEncFrom($school_name,$school_mail);
		$mail->to = trim($_POST['email']);
		$mail->send();
	}
	// 若是有變更密碼欄位,則寄信給該使用者 (End)

	// 帳號使用期限
	$begin_time = ($begin_time != 'NULL' && $begin_time != '0000-00-00') ? $begin_time : $MSG['now'][$sysSession->lang];
	$expire_time= ($expire_time != 'NULL' && $expire_time != '0000-00-00') ? $expire_time   : $MSG['forever'][$sysSession->lang];

	// 為了可以馬上看到更新後的結果，所以所有的訊息顯示皆移到底下，包含訊息的載入
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

	lang = "{$lang}";
	window.onload = function () {
		rebMenu(lang);
	};

BOF;

	showXHTML_head_B($MSG['tabs_personal'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/academic/stud/lib.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		showXHTML_table_B('width="700" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['tabs_personal_save'][$sysSession->lang], 'tabsSet');					;
					showXHTML_tabs($ary, 1);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B();
				showXHTML_td_B('valign="top" id="CGroup"');

					showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="MySet" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td_B('colspan="3"');
								$msg = ($update_num > 0) ? $MSG['update_success'][$sysSession->lang] : $MSG['not_update_data'][$sysSession->lang];
								list($last_name,$first_name) = dbGetStSr('WM_user_account', 'last_name,first_name','username="' . $username . '"', ADODB_FETCH_NUM);
								echo checkRealname($first_name,$last_name), ' (', $username, ') > ', $MSG['msg_personal_update'][$sysSession->lang], ' > ', $msg;
							showXHTML_td_E('');
						showXHTML_tr_E('');

						$col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
						showXHTML_tr_B('class="' . $col . ' "');
							showXHTML_td('align="center" nowrap="noWrap"', $MSG['item'][$sysSession->lang]);
							showXHTML_td('align="center" nowrap="noWrap"', $MSG['hidden'][$sysSession->lang]);
							showXHTML_td('align="center" nowrap="noWrap"', $MSG['content'][$sysSession->lang]);
						showXHTML_tr_E();

						$col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
						showXHTML_tr_B('class="' . $col . ' "');
							showXHTML_td('align="right" nowrap="noWrap"', $MSG['username'][$sysSession->lang]);
							showXHTML_td('align="center"', '&nbsp;');
							showXHTML_td('', $username);
						showXHTML_tr_E();

                        $col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
						showXHTML_tr_B('class="' . $col . '"');
						    showXHTML_td('align="right"', $MSG['account_deadline'][$sysSession->lang]);
						    showXHTML_td('', '&nbsp');
                            showXHTML_td_B('');
                                echo $MSG['from2'][$sysSession->lang], $begin_time, '<br />',
                                     $MSG['to2'][$sysSession->lang]  , $expire_time;
                            showXHTML_td_E();
                        showXHTML_tr_E();

						$fhid = 1;
						reset($MyData);
						foreach($MyData as $key => $value) {
							if ($key == 'hid') continue;

							$col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
							showXHTML_tr_B('class="' . $col . ' "');
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
								showXHTML_td_E();
								showXHTML_td_B();
									switch ($key) {
										case 'password' :
											echo $MSG['not_open'][$sysSession->lang];
											//echo trim($_POST[$key]);
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

											// $enc = @mcrypt_encrypt(MCRYPT_DES, sysTicketSeed . $_COOKIE['idx'], $username, 'ecb');
											$enc = sysEncode($username);
											$ids = base64_encode(urlencode($enc));
											echo '<span id="PicRoom"><img src="showpic.php?a=' . $ids . '&timestamp=' . uniqid('') . '" type="image/jpeg" id="MyPic" name="MyPic" borer="0" align="absmiddle" onload="picReSize()" loop="0"></span>';

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
										case 'email' :
										    echo '<a href="mailto:' . $_POST[$key] . '">' .  $_POST[$key] . '</a>';
										    break;
										case 'msg_reserved':
											echo (intval($_POST[$key]) == 1)? $MSG['reserved'][$sysSession->lang] : $MSG['not_reserved'][$sysSession->lang];
											break;
										default:
											echo stripslashes($_POST[$key]);
									}
								showXHTML_td_E();
							showXHTML_tr_E();
						}
						$col = ($col == 'cssTrEvn') ? 'cssTrEvn' : 'cssTrEvn';
						showXHTML_tr_B('class="' . $col . ' "');
							showXHTML_td_B('colspan="3" align="center"');
								if (! empty($ACADEMIC_CLASS_MEMBER))
									showXHTML_input('button', '', $MSG['btn_return_people_manage'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'people_manager.php\')"');
								else
									showXHTML_input('button', '', $MSG['return_query_people'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'stud_query.php\')"');
							showXHTML_td_E();
						showXHTML_tr_E();
					showXHTML_table_E();

				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
	showXHTML_body_E();
?>
