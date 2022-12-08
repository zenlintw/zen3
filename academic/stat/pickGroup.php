<?php
	/**
	 * 檔案說明
	 *	學校統計資料 - 抓取課程群組
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Edi Chen <edi@sun.net.tw>
	 * @copyright   2000-2007 SunNet Tech. INC.
	 * @version     CVS: $Id: pickGroup.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2007-03-20
	 */
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/pickCourse.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	/**
	 * parsing course group tree
	 * @param int $idx course group id
	 */
	function parseTree($idx) {
		global $gp_name, $sysSession, $csGpTree;
		if ($csGpTree[$idx] && count($csGpTree[$idx])) {
			echo '<ul>';
			foreach($csGpTree[$idx] as $cid) {
				$caption = getCaption($gp_name[$cid]);
				if (count($csGpTree[$cid]) && $cid != 10000000)
					echo '<li><span class="cssTbBlur" onmouseover="chBgc(this,true);" onmouseout="chBgc(this,false);">', 
				     	 '<a href="javascript:;" onclick="return expanding(this);">', 
				     	 '<img src="/theme/default/learn/icon-cc.gif" valign="absmiddle" border="0" style="margin-right: 0.5em"/></a>',
				         '<span onclick="setClick(\'',$cid,'\', \'',$caption[$sysSession->lang],'\');">', $caption[$sysSession->lang], '</span></span></li>';
				else
					echo '<li><span class="cssTbBlur" onmouseover="chBgc(this,true);" onmouseout="chBgc(this,false);">', 
				     	 '<img src="/theme/default/learn/icon-ccc.gif" valign="absmiddle" border="0" style="margin-right: 0.5em"/>',
				         '<span onclick="setClick(\'',$cid,'\', this.innerHTML);">', $caption[$sysSession->lang], '</span></span></li>';
				if ($cid != 10000000) {
					parseTree($cid);
				}
			}
			echo '</ul>';
		}
	}
	
	// 抓取課程群組名稱
	chkSchoolId('WM_term_course');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$gp_name = $sysConn->GetAssoc('select course_id, caption from WM_term_course where kind="group"');
	$gp_name[10000000] = $MSG['un_div_course'][$sysSession->lang];	// 未分組課程
	
	// 抓取課程群組關聯
	$csGpTree = array();
	$rs = dbGetStMr('WM_term_group', '*', '1 order by parent, `permute`', ADODB_FETCH_ASSOC);
	if ($rs) while ($row = $rs->FetchRow()) {
		$csGpTree[$row['parent']][] = $row['child'];
	}
	$csGpTree[10000000][] = 10000000;	// 未分組課程
	
	foreach($csGpTree as $gid => $cids) {	// 去掉無用的資料
		foreach($cids as $idx => $cid)
			if (empty($gp_name[$cid]))
				unset($csGpTree[$gid][$idx]);
	}
	
	$sIndex = 10000000;
	$showTitle = $sysSession->school_name;
	$extra_js = <<< BOF
	function setClick(id, name) {
		w = dialogArguments;
		if(w){
			w.showCourseCaption('single_group', name);
			w.showCourseCaption('single_group_id', id);
			self.close();
		}
	}
BOF;

	require_once(sysDocumentRoot . '/academic/stat/pickCommon.php');
?>
