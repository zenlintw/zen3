<?php
	/**
	 * 檔案說明
	 *	公告與聯繫 -> 寄給群組 -> 編輯郵件
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Edi Chen <edi@sun.net.tw>
	 * @copyright   2000-2007 SunNet Tech. INC.
	 * @version     CVS: $Id: course_group_mail.php,v 1.1 2010/02/24 02:38:39 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2007-03-16(新版直接套用/lib/wm_mails.php)
	 */

// {{{ 函式庫引用 begin    
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/wm_mails.php');
	require_once(sysDocumentRoot . '/lang/course_manage.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
// }}} 函式庫引用 end

// {{{ 函數宣告 begin
	/**
	 * 顯示收件群組
	 */
	function showGroupInfo($col, $head, $data, $note) {
		global $MSG, $sysSession, $course_name, $_POST;
		
		$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
		showXHTML_input('hidden', 'csid', $_POST['csid'], '', '');
        // #47349 Chrome[管理者/公告與聯繫/寄給群組] 主旨、內容沒有填寫時，應該要出現alert「主旨跟內容都要填寫」-->給予屬性id
		showXHTML_input('hidden', 'roles', '', '', 'id="roles"');
		showXHTML_tr_B($col);
			showXHTML_td('align="right" valign="top" nowrap="nowrap"', $MSG['accept'][$sysSession->lang]);
			foreach($course_name as $i =>  $name) {
				$lang = getCaption($name);
				$course_name[$i] = $lang[$sysSession->lang];
			}
			showXHTML_td('nowrap="nowrap"', implode(', ', $course_name));
			showXHTML_td('valign="top"', '&nbsp;');
		showXHTML_tr_E('');
	}
	
	/**
	 * 顯示收件者
	 */
	function showReciver($col, $head, $data, $note) {
		global $MSG, $sysSession;
		$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
		showXHTML_tr_B($col);
			showXHTML_td('align="right" valign="top" nowrap="nowrap"', $head);
			showXHTML_td_B('nowrap="nowrap"');
				showXHTML_input('checkbox', 'student', 'student', '1');
				echo $MSG['student'][$sysSession->lang];
				showXHTML_input('checkbox', 'assistant', 'assistant', '1');
				echo $MSG['assistant'][$sysSession->lang];
				showXHTML_input('checkbox', 'instructor', 'instructor', '1');
				echo $MSG['instructor'][$sysSession->lang];
				showXHTML_input('checkbox', 'teacher', 'teacher', '1');
				echo $MSG['teacher'][$sysSession->lang], '<br />';
				showXHTML_input('text', 'to', $MSG['mail_txt'][$sysSession->lang], '', 'class="cssInput" size="64" onclick="if (this.value == \''.$MSG['mail_txt'][$sysSession->lang].'\')this.value=\'\'"');
			showXHTML_td_E('');
			showXHTML_td('valign="top"', $note);
		showXHTML_tr_E('');
	}
// }}} 函數宣告 end

// {{{ 主程式 begin
	
	// 將課程 ID 解碼
	$ary = explode(',', $_POST['csid']);
	foreach ($ary as $key => $val) {
		$ary[$key] = intval(sysDecode($val));
	}
	
	// 取得課程名稱
	chkSchoolId('WM_term_course');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$course_name = $sysConn->GetCol('select caption from WM_term_course where course_id in (' . implode(',', $ary) . ')');
	
	$sysMailsRule = sysMailsRule;
	$mail = new wmMailWritor();
	$mail->head                 = $MSG['title'][$sysSession->lang];
	$mail->title                = $MSG['title'][$sysSession->lang];
	$mail->user_func['sender']  = 'showGroupInfo';
	$mail->user_func['reciver'] = 'showReciver';
	$mail->send_method          = 'email';
	$mail->uri_target           = 'course_group_mail1.php';
	$mail->form_extra           = 'method="post" enctype="multipart/form-data" onsubmit="return checkData();" style="display: inline"';

	$js = <<< BOF
	function checkData() {
		var cnt = 0;
		var nodes = document.getElementsByTagName('input');
		var roles = document.getElementById('roles');
		roles.value = '';
		for(var i=1; i<nodes.length; i++){
			if (nodes.item(i).getAttribute("type")=="checkbox"){
				if (nodes.item(i).checked) {
					roles.value += nodes.item(i).value + ',';
					cnt++;
				}
			}
		}

		obj = document.getElementById("{$mail->form_id}");
		if (obj == null) return false;
		if ((obj.to.value == "" || trim(obj.to.value) == "{$MSG['mail_txt'][$sysSession->lang]}") && (cnt <= 0)) {
			alert("{$MSG['need_to1'][$sysSession->lang]}");
			obj.to.focus();
			return false;
		}
        
        /*#47458 [Safari][管理者/公告與聯繫/寄給群組] 主旨、內容沒有填寫時，應該要出現alert「主旨跟內容都要填寫」：遇到safari不顯示fckeditor，判斷有沒有輸入值*/
        var browser = 'ie';
        if(navigator.userAgent.indexOf('MSIE')>0){
            browser = 'ie';
        }else if(navigator.userAgent.indexOf('Firefox')>0){
            browser = 'ff';
        }else if(navigator.userAgent.indexOf('Chrome')>0){
            browser = 'chr';
        }else if(navigator.userAgent.indexOf('Safari')>0){
            browser = 'sf';
        }else{
            browser = 'op';
        }
        
        if(browser == 'sf' && (obj.subject.value == '' || obj.content.value == '')) {
            alert("{$MSG['wm_mails_empty_data'][$sysSession->lang]}");
			return false;
        }
        
		if (obj.to.value != "" && trim(obj.to.value) != "{$MSG['mail_txt'][$sysSession->lang]}")
		{
			var emails_pattern = {$sysMailsRule};
			if (!emails_pattern.test(obj.to.value))
			{
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
		
		if (trim(obj.to.value) == "{$MSG['mail_txt'][$sysSession->lang]}") obj.to.value = '';
		return true;
	}
BOF;
	
	$mail->add_script('include', '/lib/common.js');
	$mail->add_script('inline', $js);
	$mail->generate();

// }}} 主程式 end
?>
