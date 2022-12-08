<?php
	/**
	 * �ɮ׻���
	 *	�Ǯղέp��� - ����Z�Ÿs��
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
	 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
	 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
	 *
	 * @package     WM3
	 * @author      Edi Chen <edi@sun.net.tw>
	 * @copyright   2000-2007 SunNet Tech. INC.
	 * @version     CVS: $Id: pickCGroup.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2007-03-21
	 */
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/pickCourse.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	function parseTree($idx) {
		global $sysSession, $class_name, $csGpTree;
		if ($csGpTree[$idx] && count($csGpTree[$idx])) {
			echo '<ul>';
			foreach($csGpTree[$idx] as $cid) {
				$caption = getCaption($class_name[$cid]);
				if (is_array($csGpTree[$cid]) && count($csGpTree[$cid])) {
					echo '<li><span class="cssTbBlur" onmouseover="chBgc(this,true);" onmouseout="chBgc(this,false);">', 
				     	 '<a href="javascript:;" onclick="return expanding(this);">', 
				     	 '<img src="/theme/default/learn/icon-cc.gif" valign="absmiddle" border="0" style="margin-right: 0.5em"/></a>',
				         '<span onclick="setClick(\'',$cid,'\', \'',$caption[$sysSession->lang],'\');">', $caption[$sysSession->lang], '</span></span></li>';
					parseTree($cid);
				}
				else
					echo '<li><span class="cssTbBlur" onmouseover="chBgc(this,true);" onmouseout="chBgc(this,false);">', 
				     	 '<img src="/theme/default/learn/icon-ccc.gif" valign="absmiddle" border="0" style="margin-right: 0.5em"/>',
				         '<span onclick="setClick(\'',$cid,'\', this.innerHTML);">', $caption[$sysSession->lang], '</span></span></li>';
			}
			echo '</ul>';
		}
	}
	
	// ����Ҧ��Z�ŦW��
	chkSchoolId('WM_class_main');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$class_name = $sysConn->GetAssoc('select class_id, caption from WM_class_main');
	
	// ����Z�����p
	$rs = dbGetStMr('WM_class_group', 'parent, child', '1 order by parent, permute', ADODB_FETCH_ASSOC);
	if ($rs) while ($row = $rs->FetchRow()) {
		$csGpTree[$row['parent']][] = $row['child'];
	}
	
	/*#48701 chrome �޲z��-�Ǯպ޲z-�Ǯղέp���-�ϥΪ̤H�Ʋέp-�Z�Ÿs�աA�S���]�w�Z�Ÿs�ծɡA���e���|���ýX�G�W�[����Ʈɤ~��ܪ��P�_*/
    if($csGpTree) {
        foreach($csGpTree as $gid => $cids) {	// �h���L�Ϊ����
            foreach($cids as $idx => $cid)
                if (empty($class_name[$cid]))
                    unset($csGpTree[$gid][$idx]);
        }
	}
    
	$sIndex = 1000000;
	$showTitle = $sysSession->school_name;
	$extra_js = <<< BOF
	function setClick(id, name) {
		w = dialogArguments;
		if(w){
			w.showClassCaption('single_cgroup', name);
			w.showClassCaption('single_cgroup_id', id);
			self.close();
		}
	}
BOF;
	require_once(sysDocumentRoot . '/academic/stat/pickCommon.php');
?>
