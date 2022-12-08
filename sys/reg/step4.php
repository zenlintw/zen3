<?php
	/**
	 * 註冊 - 將填寫的個人資料寫到相關的 Database 與 Table
	 *     1. 本程式目前只允許 /sys/reg/step3.php 呼叫
	 *
	 * @todo
	 *     1. 紀錄 Log
	 *
	 * @author  ShenTing Lin
	 * @version $Id: step4.php,v 1.1 2010/02/24 02:40:21 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/register.php');
	require_once(sysDocumentRoot . '/sys/syslib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	

    // mooc 模組開啟的話將網頁導向index.php
    if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
        header('Location: /mooc/index.php');
        exit;
    }
    
	$sysSession->cur_func = '400200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$ticket = md5($sysSession->ticket . 'WriteUserData' . $sysSession->username . $sysSession->school_id . $sysSession->school_host);

	if (trim($_POST['ticket']) != $ticket) {
		header('Location: /');
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'others', $_SERVER['PHP_SELF'], $MSG['msg_js_13'][$sysSession->lang]);
		die($MSG['msg_js_13'][$sysSession->lang]);
	}

	setTicket();
	setcookie("Ticket", $sysSession->ticket, time()+3600);

	$js = <<< BOF
	function GoReg() {
		var obj = document.getElementById("actForm");
		if (obj == null) return false;
		obj.submit();
	}
BOF;

	$dd = array(
			array('fix',      20, $MSG['username'][$sysSession->lang],    'username',       1, 0, $MSG['msg_01'][$sysSession->lang]),
			array('password', 20, $MSG['password'][$sysSession->lang],    'password',       1, 0, $MSG['msg_02'][$sysSession->lang]),
			array('password', 20, $MSG['repassword'][$sysSession->lang],  'repassword',     1, 0, $MSG['msg_03'][$sysSession->lang]),
			array('text',     20, $MSG['last_name'][$sysSession->lang],   'last_name',      1, 1, '&nbsp;'),
			array('text',     20, $MSG['first_name'][$sysSession->lang],  'first_name',     1, 1, '&nbsp;'),
			array('radio',     2, $MSG['gender'][$sysSession->lang],      'gender',         0, 1, '&nbsp;'),
			array('date',     20, $MSG['birthday'][$sysSession->lang],    'birthday',       0, 1, $MSG['msg_07'][$sysSession->lang]),
			array('text',     20, $MSG['personal_id'][$sysSession->lang], 'personal_id',    0, 1, '&nbsp;'),
			array('text',     50, 'E-mail Address',                       'email',          1, 1, $MSG['msg_09'][$sysSession->lang]),
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

	$MyData = array(
			'password'       => '', 'first_name'     => '', 'last_name'      => '',
			'gender'         => '', 'birthday'       => '', 'personal_id'    => '',
			'email'          => '', 'homepage'       => '',
			'home_tel'       => '', 'home_fax'       => '', 'home_address'   => '',
			'office_tel'     => '', 'office_fax'     => '', 'office_address' => '',
			'cell_phone'     => '', 'company'        => '', 'department'     => '',
			'title'          => '', 'language'       => '', 'theme'          => ''
		);

	$sqls = '';
	foreach($MyData as $key => $value) {
		$val = '';
		switch ($key) {
			case 'password' :
				$val = trim($_POST[$key]);
				if (!empty($val)) $val = md5($val);
				break;
			case 'gender' :
				$val = (trim($_POST[$key]) == 'F') ? 'F' : 'M';
				break;
			case 'language' :
				$language = array('Big5', 'GB2312', 'en', 'EUC-JP', 'user_define');
				$val  = (in_array(trim($_POST[$key]), $language)) ? trim($_POST[$key]) : 'Big5';
				break;
			case 'theme';
				$val = 'default';
				break;
			default :
				$val = trim($_POST[$key]);
		}
		if (($key == 'password') && empty($val)) continue;
		$sqls .= "{$key}='{$val}', ";
	}

	$sqls = substr($sqls, 0, -2);

	$RS = dbSet('WM_user_account', $sqls, "username='" . $sysSession->username . "'");
	$RS = dbSet('WM_sch4user', 'login_times=1', "username='" . $sysSession->username . "'");

	if ($RS){
		$RS = dbSet('WM_all_account', $sqls, "username='{$sysSession->username}'");
	}

	ob_start();
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/sys/sys.css");
		showXHTML_script('inline', $js);
		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" align="center"');
			showXHTML_tr_B('class="bgColor01"');
				showXHTML_td('width="100%" colspan="2" align="left" valign="middle" nowrap class="font01"', '&nbsp;&nbsp;&nbsp;&nbsp;' . $MSG['reg_result'][$sysSession->lang]);
			showXHTML_tr_E('');
			showXHTML_tr_B('class="bgColor03"');
				showXHTML_td('', '&nbsp;&nbsp;&nbsp;&nbsp;');
				showXHTML_td_B('width="90%" class="bgColor04"');
					showXHTML_table_B('width="100%" cellpadding="5" border="0" cellspacing="1"');
						showXHTML_form_B('method="post" action="step1.php"', 'actForm');

						for ($i = 0; $i < count($dd); $i++) {
							$col = ($col == 'bgColor03') ? 'bgColor05' : 'bgColor03';

							switch ($dd[$i][3]) {
								case 'username' :
									showXHTML_tr_B('class="'.$col.'"');
									showXHTML_td('', $dd[$i][2]);
									showXHTML_td('', $sysSession->username);
									showXHTML_tr_E(''); 
									break;
								case 'password' :
									showXHTML_tr_B("class=\"$col\"");
										showXHTML_td('', $dd[$i][2]);
										showXHTML_td('', $MSG['hidden_field'][$sysSession->lang]);
									showXHTML_tr_E('');
									break;
								case 'repassword' :
									$col = ($col == 'bgColor03') ? 'bgColor05' : 'bgColor03';
									break;
								case 'gender' :
									if (trim($_POST[$dd[$i][3]]) == 'M')
										$sex = $MSG['male'][$sysSession->lang];
									else
										$sex = $MSG['female'][$sysSession->lang];

									showXHTML_tr_B("class=\"$col\"");
										showXHTML_td('', $dd[$i][2]);
										showXHTML_td('', $sex);
									showXHTML_tr_E('');
									break;
								case 'language' :
									$language = array('Big5'=>$MSG['msg_js_14'][$sysSession->lang],'en'=>$MSG['msg_js_15'][$sysSession->lang],'GB2312'=>$MSG['msg_js_16'][$sysSession->lang],'EUC-JP'=>$MSG['msg_js_17'][$sysSession->lang],'user_define'=>$MSG['msg_js_18'][$sysSession->lang]);
									$selLang = trim($_POST[$dd[$i][3]]);
									showXHTML_tr_B("class=\"$col\"");
										showXHTML_td('', $dd[$i][2]);
										showXHTML_td('', $language[$selLang]);
									showXHTML_tr_E('');
									break;
								default :
									showXHTML_tr_B("class=\"$col\"");
										showXHTML_td('', $dd[$i][2]);
										showXHTML_td('', trim($_POST[$dd[$i][3]]));
									showXHTML_tr_E('');
							}
						}
						showXHTML_form_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');

			showXHTML_tr_B('');
				showXHTML_td_B('width="49%" colspan="2" align="center" valign="middle" nowrap class="bgColor02"');
					$image = "/theme/{$sysSession->theme}/sys/button_02_b.gif";					
					echo showButton('button', $MSG['btn_learn'][$sysSession->lang], $image, 'class="cssBtn1" onclick="window.location.replace(\'/learn/index.php\');"'),
					     showButton('button', $MSG['home'][$sysSession->lang]     , $image, 'class="cssBtn1" onclick="window.location.replace(\'/logout.php\');"');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
		$content = ob_get_contents();
	ob_end_clean();

	layout($MSG['html_title'][$sysSession->lang], $content);
?>
