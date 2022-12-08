<?php
	/**
	 * 檔案說明
	 *	學校統計資料 - 抓取單一班級
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Edi Chen <edi@sun.net.tw>
	 * @copyright   2000-2007 SunNet Tech. INC.
	 * @version     CVS: $Id: pickClass.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2007-03-21
	 */
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/pickCourse.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	function parseTree($idx) {
		global $class_arr, $class_name, $sysSession, $MSG;
		
		echo '<ul>';
		foreach ($class_arr as $cid) {
			$caption = getCaption($class_name[$cid]);
			echo '<li><span class="cssTbBlur" onmouseover="chBgc(this,true);" onmouseout="chBgc(this,false);">', 
				 '<img src="/theme/default/learn/icon-ccc.gif" valign="absmiddle" border="0" style="margin-right: 0.5em"/>',
				 '<span onclick="setClick(\'',$cid,'\', this.innerHTML);">', $caption[$sysSession->lang], '</span></span></li>';
		}
		echo '</ul>';
	}
	
	function setClass($class_id) {
		global $class_arr, $csGpTree;
		if (is_array($csGpTree[$class_id]) && count($csGpTree[$class_id])) {
			foreach($csGpTree[$class_id] as $child) {
				$class_arr[] = $child;
				setClass($child);
			}
		}
	}
	
	$class_id = max(1000000, intval($_GET['gd']));
	
	// 抓取班級關聯
	$rs = dbGetStMr('WM_class_group', 'parent, child', '1 order by parent, permute', ADODB_FETCH_ASSOC);
	if ($rs) while ($row = $rs->FetchRow()) {
		$csGpTree[$row['parent']][] = $row['child'];
	}
	
	$class_arr = array();
	setClass($class_id);

	// 抓取班級名稱
	chkSchoolId('WM_class_main');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$class_name = $sysConn->GetAssoc('select class_id, caption from WM_class_main where class_id in ('.implode(',', $class_arr).') or class_id = ' . $class_id);
	
	foreach($class_arr as $k => $cid) {	// 去掉無用的資料
		if (empty($class_name[$cid]))
			unset($class_arr[$k]);
	}
	
	$sIndex    = $class_id;
	$caption   = getCaption($class_name[$class_id]);
	$showTitle = $caption[$sysSession->lang];
	$extra_js  = <<< BOF
	function setClick(id, name) {
		w = dialogArguments;
		if(w){
			w.showCourseCaption('single_class', name);
			w.showCourseCaption('single_class_id', id);
			self.close();
		}
	}
BOF;

	require_once(sysDocumentRoot . '/academic/stat/pickCommon.php');
?>
