<?php
	/**
	 * 分組討論 - 寄信給小組
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Edi Chen <edi@sun.net.tw>
	 * @copyright   2000-2007 SunNet Tech. INC.
	 * @version     CVS: $Id: group_mail_write.php,v 1.1 2010/02/24 02:39:07 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2007-05-08(新版直接套用/lib/wm_mails.php)
	 */

// {{{ 函式庫引用 begin
	 require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	 require_once(sysDocumentRoot . '/lib/wm_mails.php');
	 require_once(sysDocumentRoot . '/lang/msg_center.php');
// }}} 函式庫引用 end

// {{{ 主程式 begin
	$mail = new wm5MailWritor();
	
	$sysMailsRule = sysMailsRule;
	$js = <<< BOF
	function checkData() {
		// step1 : 檢查是否有選擇收件者
		var sel = false;
		var nodes = document.getElementsByTagName('input');
		for(var i=0; i<nodes.length; i++) {
			if (nodes.item(i).getAttribute("type")=="checkbox" && nodes.item(i).checked) {
				sel = true;
				break;
			}
		}

		obj = document.getElementById("{$mail->form_id}");
		if (obj == null) return false;
		if (obj.to.value == "" && sel == false) {
			alert("{$MSG['need_to'][$sysSession->lang]}");
			obj.to.focus();
			return false;
		}
		
		// step2 : 如果有額外填入email, 檢查格式是否正確
		if (obj.to.value != "") {
			var emails_pattern = {$sysMailsRule};
			if (!emails_pattern.test(obj.to.value)) {
				alert('Incorrect E-mail(s) format.');
				obj.to.focus();
				return false;
			}
		}
		
		if (!chkMailData())
		{
			alert("{$MSG['wm_mails_empty_data'][$sysSession->lang]}");
			return false;
		}
		
		return true;
	}
BOF;
	$mail->form_extra  = 'method="post" enctype="multipart/form-data" onsubmit="return checkData();" style="display: inline"';
	$mail->send_method = 'email';
	$mail->uri_target  = 'group_mail_writing.php';
	$mail->reciver     = $_POST['to'];
	$mail->head        = $MSG['tabs_new'][$sysSession->lang];
	$mail->title       = $MSG['tabs_new'][$sysSession->lang];
	$mail->add_script('inline', $js);
	$mail->generate();
// {{{ 主程式 end
?>
