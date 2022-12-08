<?php
    /**
     * 試卷 (作業、問卷) 匯入
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
     * @copyright   2000-2006 SunNet Tech. INC.
     * @version     CVS: $Id: exam_import.php,v 1.1 2009-06-25 09:27:42 edi Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2006-06-16
     */

   	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/teach/exam/irs_xml_lib.php');

    function getCourseIDs () {
        $courses = array();
        $table = '`CO_course`';
        $column = 'WM_course_id';
        $where = "syear = '1061'";

//        $table = '`WM_term_course`';
//        $column = 'course_id';
//        $where = "course_id in (10053322, 10045798, 10045465)";

        $courseIDs = dbGetStMr($table, $column, $where);
        if ($courseIDs) {
            while (!$courseIDs->EOF) {
                $courses[] = $courseIDs->fields[$column];
                $courseIDs->MoveNext();
            }
        }

        return $courses;
    }

    $ticket = md5(sysTicketSeed . $sysSession->course_id . $_SERVER['QUERY_STRING']);

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array(array($MSG['import_result'][$sysSession->lang]));
		showXHTML_tabFrame_B($ary, 1, '', 'ListTable', 'action="exam_modify.php", method="POST" style="display: inline"');
			showXHTML_table_B('id ="mainTable" width="680" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

			$courses = getCourseIDs();
			$courseCount = count($courses);
			if ($courseCount > 0) {
                for ($i = 0; $i < $courseCount; $i++) {
                    $course_id = $courses[$i];

                    $dom = false;
                    parseXML(); // 匯入題目
                }
            }

			  showXHTML_tr_B('class="cssTrEvn"');
                showXHTML_td_B('align="right" colspan="2" style="padding-right: 1em"');
				  showXHTML_input('button', '', $MSG['return list'][$sysSession->lang],   '', 'class="cssBtn" onclick="location.replace(\'exam_maintain.php\');"');
				  showXHTML_input('submit', '', $MSG['edit instance'][$sysSession->lang], '', 'class="cssBtn"');
				  showXHTML_input('hidden', 'ticket', $ticket);
	    		  showXHTML_input('hidden', 'referer', $_SERVER['QUERY_STRING']);
				  showXHTML_input('hidden', 'lists', $sysConn->Insert_ID());
                showXHTML_td_E();
			  showXHTML_tr_E();

			showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E();