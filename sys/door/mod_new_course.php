<?php
	/**
	 * 新開課程
	 *
	 *     所需樣板名稱：new_course.htm
	 *
	 * @since   2004/10/27
	 * @author  ShenTing Lin
	 * @version $Id: mod_new_course.php,v 1.1 2010/02/24 02:40:20 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '1300200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	function mod_course_id($val) {
		return sysEncode($val);
	}

	function mod_course_caption($val) {
		global $sysSession;
		$lang = getCaption($val);
		$caption = $lang[$sysSession->lang];
		if (empty($caption)) $caption = '&nbsp;&nbsp;';
		return $caption;
	}

	function mod_course_teacher($val) {
		global $sysSession, $MSG;
		$val = trim($val);
		if (!empty($val)) $val = sprintf($MSG['msg_course_teacher'][$sysSession->lang], $val);
		return $val;
	}

	function mod_course_status($val) {
		global $sysSession, $MSG;
		$val = intval($val);
		if (($val == 1) || ($val == 2)) return $MSG['msg_new_course_audit'][$sysSession->lang];
		if (($val == 3) || ($val == 4)) return $MSG['msg_new_course_sign'][$sysSession->lang];
	}

	function mod_new_course() {
		global $sysSession, $ADODB_FETCH_MODE, $MSG, $sysCourseList;

		$ary = array('<%COURSE_ID%>', '<%COURSE_CAPTION%>', '<%TEACHER%>', '<%MSG_COURSE_WELCOME%>');
		$oper = array(
			'course_id' => 'mod_course_id("%s")',
			'caption'   => 'mod_course_caption(\'%s\')',
			'teacher'   => 'mod_course_teacher("%s")',
			'status'    => 'mod_course_status("%s")'
		);

		$tpl = getTemplate('new_course.htm');
		$myTemplate = new Wise_Template($tpl);
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = dbGetStMr('WM_term_course', '`course_id`, `caption`, `teacher`, `status`', "`kind`='course' AND `status` in (1, 2, 3, 4) order by `course_id` desc limit {$sysCourseList}", ADODB_FETCH_ASSOC);
		if ($RS) {
			if ($RS->RecordCount() <= 0) {
				$myTemplate->add_replacement('<%DIV_COURSE_BEGIN%>+<%DIV_COURSE_END%>', $MSG['msg_no_course'][$sysSession->lang], true);
				$myTemplate->add_replacement('<%SHOW_MORE_BEGIN%>+<%SHOW_MORE_END%>', '');
			} else {
				$myTemplate->add_recordset('<%COURSE_ITEM_BEGIN%>+<%COURSE_ITEM_END%>', $RS, $ary, $oper);
    			if ($sysSession->username != 'guest') {
        			$myTemplate->add_replacement('<%SHOW_MORE_BEGIN%>+<%SHOW_MORE_END%>', '', true);
                } else {
    				$myTemplate->add_replacement('<%SHOW_MORE_BEGIN%>' , '');
    				$myTemplate->add_replacement('<%SHOW_MORE_END%>'   , '');
				}
				$myTemplate->add_replacement('<%DIV_COURSE_BEGIN%>', '');
				$myTemplate->add_replacement('<%DIV_COURSE_END%>'  , '');
			}
		} else {
			$myTemplate->add_replacement('<%DIV_COURSE_BEGIN%>+<%DIV_COURSE_END%>', $MSG['msg_no_course'][$sysSession->lang], true);
		}

		$myTemplate->add_replacement('<%MSG_NEW_COURSE1%>'    , $MSG['msg_new_course1'][$sysSession->lang]);
		$myTemplate->add_replacement('<%MSG_NEW_COURSE2%>'    , $MSG['msg_new_course2'][$sysSession->lang]);
		$myTemplate->add_replacement('<%TITLE_NEW_COURSE%>'   , $MSG['title_new_course'][$sysSession->lang]);
		$myTemplate->add_replacement('<%MSG_NO_LIMIT%>'       , $MSG['msg_no_limit'][$sysSession->lang]);

		$myTemplate->add_replacement('<%COURSE_ID%>'          , $MSG['th_course_id'][$sysSession->lang]);
		$myTemplate->add_replacement('<%COURSE_NAME%>'        , $MSG['th_course_name'][$sysSession->lang]);
		$myTemplate->add_replacement('<%COURSE_TEACHER%>'     , $MSG['th_course_teacher'][$sysSession->lang]);
		$myTemplate->add_replacement('<%COURSE_ENROLL_DATE%>' , $MSG['th_enroll_date'][$sysSession->lang]);
		$myTemplate->add_replacement('<%COURSE_STUDY_DATE%>'  , $MSG['th_study_date'][$sysSession->lang]);
		$myTemplate->add_replacement('<%COURSE_INTRODUCTION%>', $MSG['th_introduction'][$sysSession->lang]);
		$myTemplate->add_replacement('<%COURSE_LIMIT%>'       , $MSG['th_limit'][$sysSession->lang]);
		$myTemplate->add_replacement('<%BTN_CLOSE%>'          , $MSG['btn_close'][$sysSession->lang]);
		$myTemplate->add_replacement('<%STUDENT%>'            , $MSG['rule_student'][$sysSession->lang]);
		$myTemplate->add_replacement('<%AUDITOR%>'            , $MSG['rule_auditor'][$sysSession->lang]);
		$myTemplate->add_replacement('<%PEOPLE%>'             , $MSG['people'][$sysSession->lang]);
		$myTemplate->add_replacement('<%LANG%>'               , $sysSession->lang);

		genDefaultTrans($myTemplate);
		$ADODB_FETCH_MODE = ADODB_FETCH_DEFAULT;
		return $myTemplate->get_result(false);
	}

?>
