<?php
	/**
	 * ±H«H
	 *
	 * @since   2004/06/25
	 * @author  ShenTing Lin
	 * @version $Id: lib_mail.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/wm_mails.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '500200200';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$mail = new wmMailWritor();
	$mail->head       = $head;
	$mail->title      = $title;
	$mail->reciver    = implode(';', $_POST['user']);
	$mail->uri_target = $target_url;
	$mail->form_extra = 'method="post" enctype="multipart/form-data" onsubmit="return checkData();" style="display: inline"';

	$js = <<< BOF
	function checkData() {
		var obj = document.getElementById("userTable");
		var nodes = null;
		var cnt = 0;
		var txt = "";
		if (obj != null) {
			nodes = obj.getElementsByTagName("input");
			for (var i = 0; i < nodes.length; i++) {
				if ((nodes[i].type == "checkbox") && nodes[i].checked) {
					cnt++;
				}
			}
		}
		obj = document.getElementById("{$mail->form_id}");
		if (obj == null) return false;
		if ((obj.to.value == "") && (cnt <= 0)) {
			alert(MSG_ME_TO);
			obj.to.focus();
			return false;
		}
		
		if (!chkMailData())
		{
			alert("{$MSG['wm_mails_empty_data'][$sysSession->lang]}");
			return false;
		}
		return true;
	}
BOF;

	$mail->add_script('inline', $js);
	$mail->generate();
?>
