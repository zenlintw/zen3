<?php
	/**
	 * ¡° ­×§ï¯Ü®z±K½X
	 *
	 * @since   2004/09/21
	 * @author  Wiseguy Liang
	 * @version $Id: weak_passwd.php,v 1.1 2010/02/24 02:38:55 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/weak_passwd.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/sys/syslib.php');

	if (strlen($_SERVER['argv'][1]) != 32 ||
	    $_SERVER['argv'][1] != md5(sysTicketSeed . $_SERVER['argv'][0])
	   ){
	   	wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,1, 'others', $_SERVER['PHP_SELF'], 'Illegal Access (Fake ticket)!');
		die('Illegal Access (Fake ticket).');
	}

	$js = <<< EOB
function Check(){
	var obj = document.forms[0];
	obj.passwd1.value = trim(obj.passwd1.value);
	obj.passwd2.value = trim(obj.passwd2.value);

	if (obj.passwd1.value != obj.passwd2.value){
		alert('{$MSG['differ_id'][$sysSession->lang]}');
		obj.reset();
		obj.passwd1.focus();
		return false;
	}

	if (obj.passwd1.value.length < 6){
		alert('{$MSG['shortId'][$sysSession->lang]}');
		obj.reset(); obj.passwd1.focus(); return false;
	}

	if (obj.passwd1.value.search(/^(\w)\1*$/) === 0){
		alert('{$MSG['duplicateId'][$sysSession->lang]}');
		obj.reset();
		obj.passwd1.focus();
		return false;
	}

	var pattern1 = '0123456789';
	var pattern2 = '9876543210';

	if (obj.passwd1.value.search(/^\w{6,}$/) != -1){
		if (pattern1.search(obj.passwd1.value) > -1 ||
		    pattern2.search(obj.passwd1.value) > -1
		   ){
			alert('{$MSG['numberId'][$sysSession->lang]}');
			obj.reset(); obj.passwd1.focus(); return false;
		}

		pattern1 = 'abcdefghijklmnopqrstuvwxyz';
        pattern2 = 'zyxwvutsrqponmlkjihgfedcba';
		var pattern3 = pattern1.toUpperCase();
		var pattern4 = pattern2.toLowerCase();

		if (pattern1.search(obj.passwd1.value) > -1 ||
		    pattern2.search(obj.passwd1.value) > -1 ||
		    pattern3.search(obj.passwd1.value) > -1 ||
		    pattern4.search(obj.passwd1.value) > -1
		   ){
			alert('{$MSG['errorId'][$sysSession->lang]}');
			obj.reset();
			obj.passwd1.focus();
			return false;
		}
	}

	if (obj.passwd1.value == 'john@19781010' ||
	    obj.passwd1.value == '650531#mary'
	   ){
		alert('{$MSG['dummyId'][$sysSession->lang]}');
		obj.reset();
		obj.passwd1.focus();
		return false;
	}

	return true;
}

EOB;

	ob_start();
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/sys/sys.css");
		showXHTML_script('inline', $js);
		showXHTML_script('include', '/lib/common.js');
		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" align="center"');
			showXHTML_tr_B('class="bgColor01"');
				showXHTML_td('width="100%" nowrap class="font04" style="font-size: 16pt"', '&nbsp;&nbsp;&nbsp;&nbsp;' . $MSG['title'][$sysSession->lang]);
			showXHTML_tr_E();

			showXHTML_tr_B('class="bgColor03"');
				showXHTML_td_B('width="90%" class="bgColor04"');
					showXHTML_form_B('method="post" action="weak_passwd1.php" onsubmit="return Check();"', 'pinfo');
						showXHTML_table_B('width="100%" cellpadding="5" border="0" cellspacing="1"');
							showXHTML_tr_B('class="bgColor03"');
								showXHTML_td_B('class="font06"');
									echo <<< EOB
{$MSG['line1'][$sysSession->lang]}<font color="#FF0000">{$MSG['line2'][$sysSession->lang]}</font>
<p>{$MSG['line3'][$sysSession->lang]}</p>
<ul>
  <li>{$MSG['example1'][$sysSession->lang]}</li>
  <li>{$MSG['example2'][$sysSession->lang]}</li>
  <li>{$MSG['example3'][$sysSession->lang]}</li>
  <li>{$MSG['example4'][$sysSession->lang]}<br>
      {$MSG['example5'][$sysSession->lang]}</li>
</ul>
<p>{$MSG['suggest'][$sysSession->lang]}</p>
<ul>
  <li>{$MSG['suggest1'][$sysSession->lang]}</li>
  <li>{$MSG['suggest2'][$sysSession->lang]}<br>
      {$MSG['suggest3'][$sysSession->lang]}<font size="4" color="#FF00FF"><b>
      john@19781010</b></font>
      {$MSG['suggest5'][$sysSession->lang]}<font size="4" color="#FF00FF"><b>
      650531#mary</b></font>
      {$MSG['suggest7'][$sysSession->lang]}</li>
  <li>{$MSG['suggest8'][$sysSession->lang]}</li>
</ul>
EOB;
								showXHTML_td_E();
							showXHTML_tr_E();
							showXHTML_tr_B('class="bgColor05"');
								showXHTML_td_B('width="80%"');
									echo '<p><font color="#000080" size="4">', $MSG['newId'][$sysSession->lang], '</font>';
									showXHTML_input('password', 'passwd1', '', '', 'size="20"'); echo '</p>';
									echo '<p><font color="#000080" size="4">', $MSG['enterId'][$sysSession->lang], '</font>';
									showXHTML_input('password', 'passwd2', '', '', 'size="20"'); echo '</p>';
                					showXHTML_input('hidden', 'username', $_SERVER['argv'][0], '', 'size="20"'); echo '</p>';
                					showXHTML_input('hidden', 'ticket', md5($_SERVER['argv'][0] . sysTicketSeed), '', 'size="20"'); echo '</p>';
								showXHTML_td_E();
							showXHTML_tr_E();
							showXHTML_tr_B('class="bgColor03"');
								showXHTML_td_B('width="80%"');
									showXHTML_table_B();
										showXHTML_tr_B();
											showXHTML_td('width="114" height="24" background="/theme/default/academic/button_01.gif" align="center" onclick="if (Check()) document.getElementById(\'pinfo\').submit();" class="font02" style="cursor: pointer; cursor: hand; "',  $MSG['save'][$sysSession->lang]);
											showXHTML_td('width="114" height="24" background="/theme/default/academic/button_01.gif" align="center" onclick="document.getElementById(\'pinfo\').reset();"  class="font02" style="cursor: pointer; cursor: hand; "', $MSG['reset'][$sysSession->lang]);
										showXHTML_tr_E();
									showXHTML_table_E();
								showXHTML_td_E();
							showXHTML_tr_E();

						showXHTML_table_E();
					showXHTML_form_E();
				showXHTML_td_E();
			showXHTML_tr_E();

		showXHTML_table_E();

	$content = ob_get_contents();
	ob_end_clean();
	layout($MSG['title'][$sysSession->lang], $content);

?>
