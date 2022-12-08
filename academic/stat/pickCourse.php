<?php
	/**
	 * 檔案說明
	 *	學校統計資料 - 抓取單一課程
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Edi Chen <edi@sun.net.tw>
	 * @copyright   2000-2007 SunNet Tech. INC.
	 * @version     CVS: $Id: pickCourse.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2007-03-20
	 */
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/pickCourse.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	function parseTree($idx) {
		global $data, $sysSession, $MSG;
		
		if (count($data) == 0) {
			echo $MSG['no_course'][$sysSession->lang];
			return;
		}
		echo '<ul>';
		foreach($data as $cid => $name) {
			if ($cid == $idx) continue;
			$caption = getCaption($name);
			echo '<li><span class="cssTbBlur" onmouseover="chBgc(this,true);" onmouseout="chBgc(this,false);">', 
				 '<img src="/theme/default/learn/icon-ccc.gif" valign="absmiddle" border="0" style="margin-right: 0.5em"/>',
				 '<span onclick="setClick(\'',$cid,'\', this.innerHTML);">', $caption[$sysSession->lang], '</span></span></li>';
		}
		echo '</ul>';
	}
	
	$group_id = max(10000000, intval($_GET['gd']));
	
	if ($group_id != 10000000) 
	{
		$data      = getAllCourseInGroup($group_id);
		$caption   = dbGetOne('WM_term_course', 'caption', 'course_id='.$group_id);
	    $caption   = getCaption($caption);
	    $showTitle = $caption[$sysSession->lang];
	}
	else 
	{
		chkSchoolId('WM_term_course');
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$data = $sysConn->GetAssoc('select B.course_id, B.caption ' .
		                           'from WM_term_course as B ' .
		                           'left join WM_term_group as G ' .
		                           'on B.course_id=G.child ' .
		                           'where G.child is NULL and B.kind="course" and B.status != 9');
		$showTitle = $MSG['un_div_course'][$sysSession->lang];
	}
	                           
	$sIndex    = $group_id;
	
	$extra_js = <<< BOF
	function setClick(id, name) {
		w = dialogArguments;
		if(w){
			w.showCourseCaption('single_course', name);
			w.showCourseCaption('single_course_id', id);
			self.close();
		}
	}
BOF;

	require_once(sysDocumentRoot . '/academic/stat/pickCommon.php');
?>
