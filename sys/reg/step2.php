<?php
	/**
	 * 註冊 - 將填寫的個人資料寫到相關的 Database 與 Table
	 *     1. 本程式目前只允許 /sys/reg/step1.php 呼叫
	 *
	 * @todo
	 *     1. 紀錄 Log
	 *
	 * @author  ShenTing Lin
	 * @version $Id: step2.php,v 1.1 2010/02/24 02:40:20 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/register.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/sys/syslib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/xajax/xajax.inc.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');

    // mooc 模組開啟的話將網頁導向index.php
    if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
        header('Location: /mooc/index.php');
        exit;
    }

	$xajax_rgk = new xajax('/sys/door/re_gen_loginkey.php');
	$xajax_rgk->registerFunction('reGenLoginKey');

	$sysSession->cur_func = '400200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	if (defined('sysEnableCaptcha') && sysEnableCaptcha)
	{
	    session_start();
	    if (empty($_POST['captcha']) || $_SESSION['captcha'] != $_POST['captcha'])
	    {
	        if (session_id()) session_destroy();
	        die('<script>alert("' . $MSG['incorrect_captcha'][$sysSession->lang] . '"); history.back();</script>');
		}
	}
	if (session_id()) session_destroy();

	$ticket = md5($sysSession->ticket . 'WriteUserData' . $sysSession->username . $sysSession->school_id . $sysSession->school_host);

	if (trim($_POST['ticket']) != $ticket) {
	    wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 5, 'others', $_SERVER['PHP_SELF'], $MSG['msg_js_13'][$sysSession->lang]);
		die($MSG['msg_js_13'][$sysSession->lang]);
	}

	$js = <<< BOF
	var MSG_NEED_USERNAME = "{$MSG['msg_fill_username'][$sysSession->lang]}";
	var MSG_NEED_PASSWORD = "{$MSG['msg_fill_password'][$sysSession->lang]}";

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

	// 檢查帳號是否已經有人使用了
	$error_no = checkUsername($_POST['username']);

	if ($error_no > 0) {
		if ($error_no == 1){
			$message = $MSG['system_reserved'][$sysSession->lang];
		}else if ($error_no == 2){
			$message = $MSG['used'][$sysSession->lang];
		}else if ($error_no == 3){
			$message = $MSG['msg_js_03'][$sysSession->lang];
		}else if ($error_no == 4){
			$message = $MSG['system_reserved'][$sysSession->lang];
		}
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , $error_no, 'others', $_SERVER['PHP_SELF'], $message, $_POST['username']);
	} else {
		// 將資料寫進資料庫中
		foreach ($_POST as $key => $val) {
			if ($key == 'password')
				$data[$key] = md5(trim($val));
			else
				$data[$key] = trim($val);
		}

        // 郵件的原始信件檔案
        $target	= sysDocumentRoot . "/base/$sysSession->school_id/add_account.mail";
		if (file_exists($target)){
	        // 郵件的原始信件檔案  的夾檔路徑
	        $att_file_path	= sysDocumentRoot . "/base/$sysSession->school_id/attach/add_account";

	        // ========== 1.先讀取檔案中的原始信件檔案(每封信件共用資訊) ==========
	    	// 先讀取 郵件的原始信件檔案 資料
	        $fd = fopen($target, 'r');

	        // 讀取標題
	        $temp = fgets($fd, 1024);
	        $tmp_subject = $temp;

	    	/*
	    	  讀取內容
	    	  $tmp_body 為尚未置換特殊符號的本文
	    	**/
	    	while (!feof ($fd)) {
	    		$tmp_body .= fgets($fd, 4096);
	    	}

	        fclose($fd);
		}else{
			// 讀取標題
			$tmp_subject = $MSG['add_account_subject'][$sysSession->lang];
			// 讀取內容
			$tmp_body = $MSG['add_account_body'][$sysSession->lang];

		}

        // ========== 2.取出信件夾檔名稱(每封信件共用資訊) ==========
        if (is_dir($att_file_path)){
	        // 取得所有附加檔案名稱
		    $att_files		= getAllFile($att_file_path);
		}
		$res_no = addUser($_POST['username'], $data);
		if ($res_no <= 0) {
			// 送信

			if ($res_no == 0) {  // 註冊成功，不需要管理者審核
			//	$body = $MSG['letter_01'][$sysSession->lang];
			    $body = $tmp_body;
				$message = $MSG['msg_23'][$sysSession->lang];

			    $subject = $tmp_subject . '(' . $sysSession->school_name . ')';

				// 每次進入都必須重新宣告一個新的 mail 類別
			    $mail = buildMail('', $subject, $body, 'html', '', '', '', '', false);

				$real_name = checkRealname($_POST['first_name'], $_POST['last_name']);

			    $body = strtr($body,
				      array(
					    '%SCHOOL_NAME%' =>  $sysSession->school_name,
					    '%USERNAME%'    =>  trim($_POST['username']),
					    '%PASSWORD%'    =>  trim($_POST['password']),
					    '%SCHOOL_HOST%' =>  $_SERVER['HTTP_HOST']
				           )
				     );

                // ========== 處理附加檔案 ==========
            	$att_count		= count($att_files);

            	for ($j=0; $j<$att_count; $j++){
            		$attach		= $att_file_path . DIRECTORY_SEPARATOR . $att_files[$j];
            		$data = file_get_contents($attach);
            		// 5.信件夾檔
            		$mail->add_attachment($data,$att_files[$j]);
            	}

			} else {  // 註冊成功，但需要管理者審核
				$body = $MSG['letter_02'][$sysSession->lang];
				$message = $MSG['need_confirm'][$sysSession->lang];
				$subject = $sysSession->school_name . $MSG['letter_subject_02'][$sysSession->lang];

				$body = strtr($body,
				      array(
					    '%%NAME%%'        =>  trim($_POST['last_name'] . ' ' . $_POST['first_name']),
					    '%%SCHOOL_NAME%%' =>  $sysSession->school_name,
					    '%%USERNAME%%'    =>  trim($_POST['username']),
					    '%%PASSWORD%%'    =>  trim($_POST['password']),
					    '%%SERVER_NAME%%' =>  $_SERVER['HTTP_HOST']
				           )
				     );

				// 每次進入都必須重新宣告一個新的 mail 類別
				$mail = buildMail('', $subject, $body, 'html', '', '', '', '', false);

			}

			list($school_name,$school_mail) = dbGetStSr('WM_school', 'school_name,school_mail', "school_id={$sysSession->school_id} and school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);

			if (empty($school_mail)){
				$school_mail = 	'webmaster@'. $_SERVER['HTTP_HOST'];
			}
			$mail->from = mailEncFrom($school_name,$school_mail);

			$mail->body = $body;
			$mail->to = trim($_POST['email']);
			$mail->send();
			wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'others', $_SERVER['PHP_SELF'], 'new user account', $_POST['username']);
		} else {
			// 新增使用者到資料庫中失敗
			$message = $MSG['msg_24'][$sysSession->lang];
			$error_no = 1;
			wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , $error_no, 'others', $_SERVER['PHP_SELF'], $message, $_POST['username']);
		}
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
							showXHTML_tr_B('class="bgColor05"');
								showXHTML_td_B('colspan="2" style="color : #FF0000"');
									if ($error_no > 0) {
										echo $message;
										$ticket = md5('EditData' . $sysSession->ticket . $sysSession->username . $sysSession->school_id . $sysSession->school_host);
										showXHTML_input('hidden', 'ticket', $ticket, '', '');
										for ($i = 0; $i < count($dd); $i++) {
											showXHTML_input('hidden', $dd[$i][3], trim($_POST[$dd[$i][3]]), '', '');
										}
									} else {
										echo $message;
									}
								showXHTML_td_E('');
							showXHTML_tr_E('');

							for ($i = 0; $i < count($dd); $i++) {
								$col = ($col == 'bgColor03') ? 'bgColor05' : 'bgColor03';

								switch ($dd[$i][3]) {
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
				showXHTML_td_B('width="100%" colspan="2" align="center" valign="middle" nowrap class="bgColor02"');
					$image = "/theme/{$sysSession->theme}/sys/button_02_b.gif";
					if ($error_no > 0) {
						echo showButton('button', $MSG['modify'][$sysSession->lang], $image, 'class="cssBtn1" onclick="GoReg();"');
						echo '&nbsp;';
					}else if ($res_no == 0) {	// 不需管理者審核 才顯示 進入學習環境
						echo showButton('button', $MSG['btn_learn'][$sysSession->lang], $image, 'class="cssBtn1" onclick="document.getElementById(\'loginForm\').submit();"');
					}
					echo showButton('button', $MSG['home'][$sysSession->lang], $image, 'class="cssBtn1" onclick="window.location.replace(\'/logout.php\');"');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

		showXHTML_form_B('method="post" action="' . (defined('WM_SSL') ? ('https://' . $_SERVER['HTTP_HOST']) : '') . '/login.php" style="display: none;"', 'loginForm');
			showXHTML_input('hidden', 'username', $_POST['username'], '', '');
			$uid = md5(uniqid(rand(),1));
			$login_key = md5(sysSiteUID . sysTicketSeed . $uid);
			dbDel('WM_prelogin', 'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(log_time) > 1200');
			dbNew('WM_prelogin', 'login_seed,uid,log_time', "'{$login_key}','{$uid}',NOW()");

			$md5key = MD5($_POST['password']);
			$cypkey = substr($md5key,0,4) . substr($login_key,0,4);
			$encrypt_pwd = base64_encode(mcrypt_encrypt(MCRYPT_DES, $cypkey, $_POST['password'], MCRYPT_MODE_ECB));

			showXHTML_input('hidden', 'login_key', $login_key, '', '');
			showXHTML_input('hidden', 'encrypt_pwd', $encrypt_pwd, '', '');
		showXHTML_form_E('');

		echo $xajax_rgk->getJavascript('/lib/xajax/') . '<script>function init_rlk(){window.setInterval("xajax_reGenLoginKey()", 1200000);} if (document.attachEvent) window.attachEvent("onload", init_rlk); else window.addEventListener("load", init_rlk, false);</script>';
		$content = ob_get_contents();
	ob_end_clean();

	layout($MSG['html_title'][$sysSession->lang], $content);
?>
