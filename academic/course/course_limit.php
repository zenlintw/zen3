<?php
	/**
	 * 【程式功能】: 顯示目前系統已達開課的上限(常數:sysCourseLimit)
	 * @author  Jeff Wang
	 * @version $Id: course_limit.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 * @copyright 2003 SUNNET
	 * @since 2006-01-05
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/course_manage.php');

	if(isset($_GET['type'])) {
		$sysSession->cur_func = '700400600';
		$sysSession->restore();

		$html_title = $MSG['title_install'][$sysSession->lang];
	}else{
		$sysSession->cur_func = '700300200';
		$sysSession->restore();
		
		$html_title = $MSG['title_add_course'][$sysSession->lang];
	}
	
	showXHTML_head_B($html_title);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_head_E();
	showXHTML_body_B($html_title);
		echo '<div align="center">';
		$ary = array(array($html_title));
		showXHTML_tabFrame_B($ary, 1, '', 'ListTable');
				showXHTML_table_B('id ="mainTable" width="680" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
					showXHTML_tr_B('class="font01 cssTrEvn"');
				  	  list($school_email) = dbGetStSr('WM_school', 'school_mail',"school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);
					  showXHTML_td('', str_replace(array('%sysCourseLimit%', '%admin_email%'),
												   array(sysCourseLimit, 'mailto:' . $school_email),
						  						   $MSG['course_limit_desc'][$sysSession->lang]));
					showXHTML_tr_E();
				showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();

?>
