<?php
	/**
	 * �ɮ׻���
	 *	�H���޲z - ��Ҳέp - �H�H���Ŀ�H��
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
	 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
	 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
	 *
	 * @package     WM3
	 * @author      Edi Chen <edi@sun.net.tw>
	 * @copyright   2000-2007 SunNet Tech. INC.
	 * @version     CVS: $Id: stud_mail.php,v 1.1 2010/02/24 02:40:31 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2007-04-16(�s�������M��/lib/wm_mails.php)
	 */
	 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/msg_center.php');
	require_once(sysDocumentRoot . '/lib/wm_mails.php');

	$sysMailsRule = sysMailsRule;
	
	$mail = new wmMailWritor();
	$mail->head                  = $MSG['tabs_new'][$sysSession->lang];
	$mail->title                 = $MSG['tabs_new'][$sysSession->lang];
	$mail->send_method           = 'email';
	$mail->reciver               = $_POST['to'];
	$mail->uri_target            = 'stud_mail1.php';
	$mail->form_extra            = 'method="post" enctype="multipart/form-data" onsubmit="return checkData();" style="display: inline"';
	$mail->generate();
	
	echo <<< EOF
		<script language="javascript">
		function checkData() {
			// step1 : �ˬd�O�_����ܦ����
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
			
			// step2 : �p�G���B�~��Jemail, �ˬd�榡�O�_���T
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
		</script>
EOF;
?>
