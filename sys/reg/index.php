<?php
	/**
	 * 註冊
	 *     1. 檢查此校允不允許註冊，若不允許則拒絕註冊
	 *     2. 若可註冊，則導向到填寫個人資料的部分
	 *
	 * @todo
	 *     1. 紀錄 Log
	 *
	 * @author  ShenTing Lin
	 * @version $Id: index.php,v 1.1 2010/02/24 02:40:20 saly Exp $
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
    
/***** 檢查註冊人數是否已達上限 *******/
    if (sysMaxUser != 0)  // 0表示無限制
    {
        list($now_maxuser) = dbGetStSr('WM_user_account','count(*)','1', ADODB_FETCH_NUM);
        if ($now_maxuser >= sysMaxUser)
        {
            header("Location: /sys/max_user.php");
            exit;
        }
    }
    
	$content = '';

	$js = <<< BOF
	function GoHome() {
		window.location.replace("/");
	}

BOF;

	list($canReg) = dbGetStSr('WM_school', 'canReg', "school_id={$sysSession->school_id} AND school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);

	if ($canReg == 'N') {
		$js .= <<< BOF

	function showMsg() {
		var msg = "{$MSG['not_open_reg'][$sysSession->lang]}";
		alert(msg);
		GoHome();
	}

	window.onload = showMsg;
BOF;

		ob_start();
			showXHTML_script('inline', $js);
			showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" align="center"');
				showXHTML_tr_B('');
					showXHTML_td('width="100%" align="center" valign="middle" nowrap class="bgColor01"', '&nbsp;');
				showXHTML_tr_E('');
				showXHTML_tr_B('');
					showXHTML_td('width="100%" height="200" align="center" valign="middle" nowrap style="color : #FF0000"', $MSG['not_open_reg'][$sysSession->lang]);
				showXHTML_tr_E('');
				showXHTML_tr_B('');
					$image = "/theme/{$sysSession->theme}/sys/button.gif";
					$btn = showButton('button', $MSG['home'][$sysSession->lang], $image, 'onclick="GoHome();"');
					showXHTML_td('width="100%" align="center" valign="middle" nowrap class="bgColor02"', $btn);
				showXHTML_tr_E('');
			showXHTML_table_E('');
			$content = ob_get_contents();
		ob_end_clean();
	} else {
		$js .= <<< BOF

	function reSize() {
		var obj = document.getElementById("MyLicense");
		var availableHeight = 796;
		if (obj == null) return false;
		if (typeof(window.innerHeight) == "number") {
			availableHeight = window.innerHeight;
		} else {
			availableHeight = document.body.clientHeight;
		}
		if (availableHeight > 439) obj.height = availableHeight - 174;
	}

	function GoReg() {
		var obj = document.getElementById("actForm");
		if (obj == null) return false;
		obj.action = "step1.php";
		obj.submit();
	}

	window.onresize = reSize;
	window.onload = reSize;
BOF;
		
		$src = substr(getTemplate('agree.' . $sysSession->lang . '.htm'), strlen(sysDocumentRoot));
		// 設定車票
		setTicket();
		setcookie("Ticket", $sysSession->ticket, time()+3600);

		ob_start();
			showXHTML_script('inline', $js);
			showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" align="center"');
				showXHTML_tr_B('class="bgColor01"');
					showXHTML_td('width="100%" colspan="1" align="left" valign="middle" nowrap class="font01"', '&nbsp;&nbsp;&nbsp;&nbsp;');
				showXHTML_tr_E('');
				showXHTML_tr_B('');
					showXHTML_td('width="100%" height="265" colspan="1" align="center" valign="middle" nowrap id="MyLicense"', '<iframe src="' . $src . '" width="710" height="100%" frameborder="0"></iframe>');
				showXHTML_tr_E('');
				showXHTML_tr_B('');
				showXHTML_form_B('method="post"', 'actForm');
					showXHTML_td_B('width="49%" align="center" valign="middle" nowrap class="bgColor02"');
						$image = "/theme/{$sysSession->theme}/sys/button_02_b.gif";
						echo showButton('button', $MSG['agree'][$sysSession->lang], $image, 'class="cssBtn1" onclick="GoReg();"');
						echo '&nbsp;';
						echo showButton('button', $MSG['not_agree'][$sysSession->lang], $image, 'class="cssBtn1" onclick="GoHome();"');
						$ticket = md5('AddUser' . $sysSession->ticket . $sysSession->username . $sysSession->school_id . $sysSession->school_host);
						showXHTML_input('hidden', 'ticket', $ticket, '', '');
					showXHTML_td_E('');
				showXHTML_form_E('');
				showXHTML_tr_E('');
			showXHTML_table_E('');
			$content = ob_get_contents();
		ob_end_clean();
	}

	layout($MSG['html_title'][$sysSession->lang], $content);
?>
