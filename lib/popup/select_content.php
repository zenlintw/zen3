<?php
	/**
	 * �ɮ׻���
	 *	���o�Ч����O
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
	 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
	 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
	 *
	 * @package     WM3
	 * @author      Edi Chen <edi@sun.net.tw>
	 * @copyright   2000-2007 SunNet Tech. INC.
	 * @version     CVS: $Id: select_content.php,v 1.1 2010/02/24 02:40:09 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2007-05-02
	 */
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/popup_lang.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	/**
	 * parsing content group tree
	 * @param int $idx content group id
	 */
	function parseTree($idx)
	{
		global $content_name, $sysSession, $csCtTree;
		if ($csCtTree[$idx] && count($csCtTree[$idx])) {
			echo '<ul>';
			foreach($csCtTree[$idx] as $cid)
			{
				$caption = getCaption($content_name[$cid]);
				if (count($csCtTree[$cid]) && $cid != 100000) 
					echo '<li><span class="cssTbBlur" onmouseover="chBgc(this,true);" onmouseout="chBgc(this,false);">', 
				     	 '<a href="javascript:;" onclick="return expanding(this);">', 
				     	 '<img src="/theme/default/learn/icon-cc.gif" valign="absmiddle" border="0" style="margin-right: 0.5em"/></a>',
				     	 '<input type="radio" value="',$cid,'" name="gids" id="',$cid,'" style="height: 17px;"><label for="',$cid,'">',
				         '[G] ', $caption[$sysSession->lang], '</label></span></li>';
				else
					echo '<li><span class="cssTbBlur" onmouseover="chBgc(this,true);" onmouseout="chBgc(this,false);">', 
				     	 '<a href="javascript:;" onclick="return expanding(this);">', 
				     	 '<img src="/theme/default/learn/icon-ccc.gif" valign="absmiddle" border="0" style="margin-right: 0.5em"/></a>',
				     	 '<input type="radio" value="',$cid,'" name="gids" id="',$cid,'" style="height: 17px;"><label for="',$cid,'">',
				         '[G] ',$caption[$sysSession->lang], '</label></span></li>';
				if ($cid != 100000) {
					parseTree($cid);
				}
			}
			echo '</ul>';
		}
	}
	
	// ����Ч��s�զW��
	chkSchoolId('WM_content');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$content_name = $sysConn->GetAssoc('select content_id, caption from WM_content where kind="group"');
	$content_name[100000] = $MSG['title_root_group_list'][$sysSession->lang];	// �������Ч�
	
	// ����Ч��s�����p
	$csCtTree = array();
	$csCtTree[100000][] = 100000;	// �������Ч�
	$rs = dbGetStMr('WM_content_group', '*', '1 order by parent, `permute`', ADODB_FETCH_ASSOC);
	if ($rs) while ($row = $rs->FetchRow()) 
	{
		$csCtTree[$row['parent']][] = $row['child'];
	}
	
	foreach($csCtTree as $gid => $cids) // �h���L�Ϊ����
	{	
		foreach($cids as $idx => $cid)
			if (empty($content_name[$cid]))
				unset($csCtTree[$gid][$idx]);
	}

	$sIndex    = 100000;
	$showTitle = $MSG['title_group_list'][$sysSession->lang];
	
	$extra_js = <<< BOF
	function nextstep()
	{
		var val = 100000;
		var nodes = document.getElementsByName("gids");
		if ((nodes != null) && (nodes.length > 0)) {
			for (var i = 0; i < nodes.length; i++) {
				if (nodes[i].checked) {
					val = nodes[i].value;
					break;
				}
			}
		}
		document.location.replace("select_content1.php?content_id="+val);
	}
BOF;
	// �]�w�B�~���ާ@
	ob_start();
	showXHTML_input('button', 'btnCollect2', $MSG['next_step'][$sysSession->lang]  , '', 'onclick="nextstep()"      class="cssBtn"');
	showXHTML_input('button', 'btnCollect2', $MSG['btn_cancel'][$sysSession->lang] , '', 'onclick="window.close();" class="cssBtn"');
	$extra_btn = ob_get_contents();
	ob_end_clean();
	
	require_once(sysDocumentRoot . '/academic/stat/pickCommon.php');
?>