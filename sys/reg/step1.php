<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1
	*       @version $Id: step1.php,v 1.1 2010/02/24 02:40:20 saly Exp $:                                                                             *
	*                                                                                                 *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
	require_once(sysDocumentRoot . '/lang/register.php');
	require_once(sysDocumentRoot . '/sys/syslib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

    // mooc �Ҳն}�Ҫ��ܱN�����ɦVindex.php
    if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
        header('Location: /mooc/index.php');
        exit;
    }

	header('Cache-Control: ');
	header('Pragma: ');
	header('Expires: ' . date('r', time() + 600)); // �Q�����ᥢ��

	$sysSession->cur_func = '400200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$act = '';
	$ticket = md5('AddUser' . $_COOKIE["Ticket"] . $sysSession->username . $sysSession->school_id . $sysSession->school_host);
	if (trim($_POST['ticket']) == $ticket) {
		$act = 'AddUser';
	}

	$ticket = md5('EditData' . $_COOKIE["Ticket"] . $sysSession->username . $sysSession->school_id . $sysSession->school_host);
	if (trim($_POST['ticket']) == $ticket) {
		$act = 'EditData';
	}

	$ActLeng = array(sysAccountMinLen, sysAccountMaxLen);

	// mail �W�h
	$mail_Rule = sysMailRule;

	// �b���W�h
	$Account_format = Account_format;

	// �b�����������
	$user_limit  = str_replace(array('%MIN%', '%MAX%', '%FIRSTCHR%'),
	                           array(sysAccountMinLen, sysAccountMaxLen, $MSG['msg_account_firstchr_' . Account_firstchr][$sysSession->lang]),
	                           $MSG['msg_01'][$sysSession->lang]);

	$user_limit2 = str_replace(array('%MIN%', '%MAX%'),
	                           array(sysAccountMinLen, sysAccountMaxLen),
	                           $MSG['msg_js_02'][$sysSession->lang]);

	$user_limit3 = str_replace('%FIRSTCHR%', $MSG['msg_account_firstchr_' . Account_firstchr][$sysSession->lang], $MSG['msg_js_03'][$sysSession->lang]);

	$js = <<< BOF
	var sysAccountMinLen = {$ActLeng[0]}, sysAccountMaxLen = {$ActLeng[1]};
	var mail_rule = {$mail_Rule};
	var Account_format = {$Account_format};

	var MSG = new Array(
		"",
		"{$MSG['empty_account'][$sysSession->lang]}",
		"{$user_limit2}",
		"{$user_limit3}",
		"{$MSG['msg_js_04'][$sysSession->lang]}",
		"{$MSG['msg_js_05'][$sysSession->lang]}",
		"{$MSG['msg_js_06'][$sysSession->lang]}",
		"{$MSG['msg_js_07'][$sysSession->lang]}",
		"{$MSG['msg_js_08'][$sysSession->lang]}",
		"{$MSG['msg_js_09'][$sysSession->lang]}",
		"{$MSG['msg_js_10'][$sysSession->lang]}",
		"{$MSG['msg_js_11'][$sysSession->lang]}",
		"{$MSG['msg_js_12'][$sysSession->lang]}",
		"{$MSG['msg_first_name_error'][$sysSession->lang]}",
		"{$MSG['msg_last_name_error'][$sysSession->lang]}",
		"{$MSG['msg_account_reduplicate'][$sysSession->lang]}",
		"{$MSG['system_reserved'][$sysSession->lang]}"
	);

	// �q��䪺���
	function Calendar_setup(ifd, fmt, btn, shtime) {
		Calendar.setup({
			inputField  : ifd,
			ifFormat    : fmt,
			showsTime   : shtime,
			time24      : true,
			button      : btn,
			singleClick : true,
			weekNumbers : false,
			step        : 1
		});
	}

BOF;

	$dd = array(
			array('fix',      20, $MSG['username'][$sysSession->lang],    'username',       1, 0, $user_limit),
			array('password', 20, $MSG['password'][$sysSession->lang],    'password',       1, 0, $MSG['msg_02'][$sysSession->lang]),
			array('password', 20, $MSG['repassword'][$sysSession->lang],  'repassword',     1, 0, $MSG['msg_03'][$sysSession->lang]),
			array('text',     20, $MSG['last_name'][$sysSession->lang],   'last_name',      1, 1, '&nbsp;'),
			array('text',     20, $MSG['first_name'][$sysSession->lang],  'first_name',     1, 1, '&nbsp;'),
			array('radio',     2, $MSG['gender'][$sysSession->lang],      'gender',         0, 1, '&nbsp;'),
			array('date',     20, $MSG['birthday'][$sysSession->lang],    'birthday',       0, 1, $MSG['msg_07'][$sysSession->lang]),
			array('text',     20, $MSG['personal_id'][$sysSession->lang], 'personal_id',    0, 1, '&nbsp;'),
			array('text',     50, 'E-mail Address'                      , 'email',          1, 1, $MSG['msg_09'][$sysSession->lang]),
			array('text',     50, 'Homepage',                             'homepage',       0, 1, '&nbsp;'),
			array('text',     20, $MSG['home_tel'][$sysSession->lang],    'home_tel',       3, 1, $MSG['msg_11'][$sysSession->lang]),
			array('text',     20, $MSG['home_fax'][$sysSession->lang],    'home_fax',       0, 1, $MSG['msg_11'][$sysSession->lang]),
			array('text',     50, $MSG['home_addr'][$sysSession->lang],   'home_address',   0, 1, $MSG['msg_13'][$sysSession->lang]),
			array('text',     20, $MSG['office_tel'][$sysSession->lang],  'office_tel',     3, 1, $MSG['msg_11'][$sysSession->lang]),
			array('text',     20, $MSG['office_fax'][$sysSession->lang],  'office_fax',     0, 1, $MSG['msg_11'][$sysSession->lang]),
			array('text',     50, $MSG['office_addr'][$sysSession->lang], 'office_address', 0, 1, $MSG['msg_13'][$sysSession->lang]),
			array('text',     20, $MSG['cell_phone'][$sysSession->lang],  'cell_phone',     3, 1, '&nbsp;'),
			array('text',     50, $MSG['company'][$sysSession->lang],     'company',        0, 1, '&nbsp;'),
			array('text',     50, $MSG['department'][$sysSession->lang],  'department',     0, 1, '&nbsp;'),
			array('text',     50, $MSG['title'][$sysSession->lang],       'title',          0, 1, '&nbsp;'),
			array('lang',      0, $MSG['language'][$sysSession->lang],    'language',       0, 0, '&nbsp;'),
			array('theme',     0, $MSG['theme'][$sysSession->lang],       'theme',          0, 0, '&nbsp;')
		);

	if (defined('sysEnableCaptcha') && sysEnableCaptcha)
	    $dd[] = array('text', 5, '<img src="captcha.php" align="absmiddle" onclick="this.src=this.src;">', 'captcha', 1, 0, $MSG['captcha_text'][$sysSession->lang]);

	ob_start();
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/sys/sys.css");
		showXHTML_script('include', '/lib/xmlextras.js');
		showXHTML_script('include', '/lib/filter_spec_char.js');
		$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
		$calendar->load_files();
		showXHTML_script('inline', $js);
		showXHTML_script('include', 'reglib.js');
		showXHTML_form_B('method="post" action="step2.php" onsubmit="return checkData();"', 'actForm');
		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" align="center"');
			showXHTML_tr_B('class="bgColor01"');
				showXHTML_td('width="100%" colspan="2" align="left" valign="middle" nowrap class="font01"', '&nbsp;&nbsp;&nbsp;&nbsp;' . $MSG['fill_out'][$sysSession->lang]);
			showXHTML_tr_E('');
		showXHTML_tr_B('class="bgColor03"');
			showXHTML_td('class="font01"', '&nbsp;&nbsp;&nbsp;&nbsp;');
			showXHTML_td_B('width="90%" class="bgColor04"');
				showXHTML_table_B('width="100%" cellpadding="5" border="0" cellspacing="1"');
					showXHTML_tr_B('class="bgColor05"');
						showXHTML_td_B('align="left" colspan="3" valign="middle" nowrap');
							echo '<span class="font04"> ** </span>: ' . $MSG['must_input'][$sysSession->lang] . '<br>';
							echo '<span class="font04"> * </span>: ' . $MSG['sel_input'][$sysSession->lang];
						showXHTML_td_E('');
					showXHTML_tr_E('');
					for ($i = 0; $i < count($dd); $i++) {
						$col = ($col == 'bgColor03') ? 'bgColor05' : 'bgColor03';
						$val = '';
						if ($act == 'EditData')
							$val = trim($_POST[$dd[$i][3]]);

						showXHTML_tr_B("class=\"$col\"");
							$title = $dd[$i][2];
							if ($dd[$i][4] == 1) $title .= '<span class="font04"> ** </span>';
							if ($dd[$i][4] == 3) $title .= '<span class="font04"> * </span>';
							showXHTML_td('nowrap="nowrap" class="font03"', $title);
							showXHTML_td_B('');
								switch($dd[$i][0]){
									case 'fix':
									case 'text':
									case 'password':
										$isUsername = false;
										if ($dd[$i][0] == 'fix') {
											$dd[$i][0] = 'text';
											$isUsername = true;
										}
										if($dd[$i][3] == 'username') {
											$user_check = ' onBlur="check_reg_username();"';
										}else{
											$user_check = '';
										}
										showXHTML_input($dd[$i][0], $dd[$i][3], $val, '', 'class="box02" size="'. (($dd[$i][1] / 2) + 5).'" maxlength="'.$dd[$i][1].'"' . $user_check);
										if ($isUsername) {
											//showXHTML_input('button', '', $MSG['query'][$sysSession->lang], '', 'class="box02"');
										}
										break;
									case 'radio':
										if ($act != 'EditData') $val = 'F';
										$gender = array('F'=>$MSG['female'][$sysSession->lang],'M'=>$MSG['male'][$sysSession->lang]);
										showXHTML_input('radio', $dd[$i][3], $gender, $val, '');
										break;
									case 'lang':
										if ($act != 'EditData') $val = $sysSession->lang;
										$chars = array('Big5'=>$MSG['msg_js_14'][$sysSession->lang],'en'=>$MSG['msg_js_15'][$sysSession->lang],'GB2312'=>$MSG['msg_js_16'][$sysSession->lang],'EUC-JP'=>$MSG['msg_js_17'][$sysSession->lang],'user_define'=>$MSG['msg_js_18'][$sysSession->lang]);
										removeUnAvailableChars($chars);
										showXHTML_input('select', $dd[$i][3], $chars, $val, '');
										break;
									case 'theme':
										if ($act != 'EditData') $val = $sysSession->theme;
										showXHTML_input('select', $dd[$i][3], array('default'=>'default'), $val, '');
										break;
									case 'date':
										showXHTML_input('text', $dd[$i][3], $val, '', 'id="'.$dd[$i][3].'" readonly="readonly" class="cssInput"');
										break;
								}
							showXHTML_td_E('');
							showXHTML_td('class="font05"', $dd[$i][6]);
						showXHTML_tr_E('');
					}
					showXHTML_tr_B('');
						showXHTML_td_B('colspan="3" align="center" valign="middle" nowrap class="bgColor02"');
							$ticket = md5($sysSession->ticket . 'WriteUserData' . $sysSession->username . $sysSession->school_id . $sysSession->school_host);

							showXHTML_input('hidden', 'ticket', $ticket, '', '');
							$image = "/theme/{$sysSession->theme}/sys/button_02_b.gif";
							echo showButton('submit', $MSG['ok'][$sysSession->lang], $image, 'class="cssBtn1"');
							echo '&nbsp;';
							echo showButton('reset', $MSG['reset'][$sysSession->lang], $image, 'class="cssBtn1"');
							echo '&nbsp;';
						showXHTML_td_E('');
					showXHTML_tr_E('');
				showXHTML_table_E('');
			showXHTML_td_E('');
		showXHTML_tr_E('');
		showXHTML_table_E('');

		$content = ob_get_contents();
	ob_end_clean();

	$content .= "<script language='javascript'>Calendar_setup('birthday' , '%Y-%m-%d', 'birthday' , false);</script>";
	layout($MSG['html_title'][$sysSession->lang], $content);
?>
