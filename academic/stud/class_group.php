<?php
	/**
	 * �ɮ׻���
	 *	�Z�� - �ץX�H�����
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
	 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
	 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
	 *
	 * @package     WM3
	 * @author      Edi Chen <edi@sun.net.tw>
	 * @copyright   2000-2007 SunNet Tech. INC.
	 * @version     CVS: $Id: class_group.php,v 1.1 2010/02/24 02:38:44 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2007-06-05
	 */
	 
// {{{ �禡�w�ޥ� begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/class_group.php');
	require_once(sysDocumentRoot . '/lib/common.php');
// }}} �禡�w�ޥ� end

// {{{ ��ƫŧi begin
	/**
	 * �N�Z���ഫ���𪬹�
	 * @param int $idx class_id
	 */
	function parseTree($idx) {
		global $sysSession, $class_name, $csGpTree;
		if ($csGpTree[$idx] && count($csGpTree[$idx])) 
		{
			echo '<ul>';
			foreach($csGpTree[$idx] as $cid) 
			{
				$caption = getCaption($class_name[$cid]);
				if ($cid == 1000000)
				{
					echo '<li><span class="cssTbBlur" onmouseover="chBgc(this,true);" onmouseout="chBgc(this,false);">', 
				         '<span><input type="checkbox" style="height: 17px;" id="ckgp_',$cid,'" value="',$cid,'" onclick="selectFlag=!this.checked;selectAll();"><label for="chk_',$cid,'">', $caption[$sysSession->lang], '</label></span></span></li>';
					parseTree($cid);
				}
				else if (is_array($csGpTree[$cid]) && count($csGpTree[$cid])) 
				{
					echo '<li><span class="cssTbBlur" onmouseover="chBgc(this,true);" onmouseout="chBgc(this,false);">', 
				     	 '<a href="javascript:;" onclick="return expanding(this);">', 
				     	 '<img src="/theme/default/learn/icon-cc.gif" valign="absmiddle" border="0" style="margin-right: 0.5em"/></a>',
				         '<span><input type="checkbox" style="height: 17px;" id="ckgp_',$cid,'" value="',$cid,'" onclick="selectNode();"><label for="chk_',$cid,'">', $caption[$sysSession->lang], '</label></span></span></li>';
					parseTree($cid);
				}
				else
				{
					echo '<li><span class="cssTbBlur" onmouseover="chBgc(this,true);" onmouseout="chBgc(this,false);">', 
				     	 '<a href="javascript:;" onclick="return expanding(this);">', 
				     	 '<img src="/theme/default/learn/icon-ccc.gif" valign="absmiddle" border="0" style="margin-right: 0.5em"/></a>',
				         '<span><input type="checkbox" style="height: 17px;" id="ckclass_',$cid,'" value="',$cid,'" onclick="selectNode();"><label for="chk_',$cid,'">', $caption[$sysSession->lang], '</label></span></span></li>';
				}
			}
			echo '</ul>';
		}
	}
// {{{ ��ƫŧi end

// {{{ �D�{�� begin
	// ����Ҧ��Z�ŦW��
	$class_name = dbGetAssoc('WM_class_main', 'class_id, caption', '1', ADODB_FETCH_ASSOC);
	$class_name[1000000] = $MSG['school'][$sysSession->lang] . $sysSession->school_name; // // �ǮզW��
	
	// ����Z�����p
	$csGpTree = array();
	$rs = dbGetStMr('WM_class_group', 'parent, child', '1 order by parent, permute', ADODB_FETCH_ASSOC);
	if ($rs) while ($row = $rs->FetchRow()) {
		$csGpTree[$row['parent']][] = $row['child'];
	}
	$csGpTree[0][] = 1000000;	// �]�w�Ĥ@��node���Ǯ�
	
	foreach($csGpTree as $gid => $cids) {	// �h���L�Ϊ����
		foreach($cids as $idx => $cid)
			if (empty($class_name[$cid]))
				unset($csGpTree[$gid][$idx]);
	}
	
	
	$sIndex    = 0;
	$showTitle = $MSG['stud_export'][$sysSession->lang];
	$extra_js  = <<< BOF
	xmlHttp = XmlHttp.create();
	xmlVars = XmlDocument.create();
	/**
	 * �ץX
	 */
	function do_export()
	{
		var temp_class = '',temp_group = '',temp = '';
		
	    var nodes = document.getElementsByTagName("input");
	    for (var i = 0; i < nodes.length; i++) {
            if ((nodes[i].type == "checkbox") && (nodes.value != '')) {
				if ((nodes[i].checked)){
                	temp = nodes[i].id;
    			    if (temp.indexOf('ckclass') != -1){
    			    	temp_class += parseInt(nodes[i].value) + ',';
    			    }else if (temp.indexOf('ckgp') != -1){
    			        temp_group += parseInt(nodes[i].value) + ',';
    			    }
    			}
            }
        }
        if (temp_group.length > 0){
			temp_group = temp_group.replace(/,$/,'');
    	    txt  = "<manifest><class_id>" + temp_group.toString() + "</class_id></manifest>";
			if (! xmlVars.loadXML(txt))
				return false;
			xmlHttp.open("POST", "query_class.php", false);
			xmlHttp.send(xmlVars);
            xmlVars.loadXML(xmlHttp.responseText);
	       	nodes = xmlVars.getElementsByTagName("class_id");
			if ((nodes != null) && (nodes.length > 0)) {
	       		if (nodes[0].hasChildNodes()){
	            	temp_group += ','+ nodes[0].firstChild.nodeValue;
	            }
    	    }
        }
		
		temp_class = temp_class + temp_group;
		temp_class = temp_class.replace(/,$/,'');

        if (temp_class.length == 0){
            alert("{$MSG['export_class_error'][$sysSession->lang]}");
            return false;
        }

        var obj = document.getElementById("export_class");
        obj.class_id.value = temp_class;
		obj.submit();
	}
	
	/**
	 * ����/����
	 */
	selectFlag = false;
	function selectAll()
	{
		var nodes = document.getElementsByTagName('input');
		selectFlag = !selectFlag;
		for(var i=0; i <nodes.length; i++)
		{
			if (nodes[i].type == 'checkbox')
				nodes[i].checked = selectFlag;
		}
		document.all.btnSelect[0].value = selectFlag ? "{$MSG['cancel_all'][$sysSession->lang]}" : "{$MSG['title54'][$sysSession->lang]}";
		document.all.btnSelect[1].value = document.all.btnSelect[0].value;
	}
	
	// ��@�`�I���
	function selectNode()
	{
		var nodes = document.getElementsByTagName('input');
		var blnSelectAll = true;
		for(var i=0; i <nodes.length; i++)
		{
			if (nodes[i].type != 'checkbox' || nodes[i].id == 'ckgp_1000000') continue;
			if (!nodes[i].checked)
			{
				blnSelectAll = false;
				break;
			}
		}
		
		document.getElementById('ckgp_1000000').checked = blnSelectAll;
		selectFlag = blnSelectAll;
		document.all.btnSelect[0].value = selectFlag ? "{$MSG['cancel_all'][$sysSession->lang]}" : "{$MSG['title54'][$sysSession->lang]}";
		document.all.btnSelect[1].value = document.all.btnSelect[0].value;
	}
	
BOF;
	// �]�w�B�~���ާ@
	ob_start();
	showXHTML_input('button', '', $MSG['title54'][$sysSession->lang]    , '', 'onclick="selectAll();" class="cssBtn" name="btnSelect"');
	showXHTML_input('button', '', $MSG['export_btn'][$sysSession->lang] , '', 'onclick="do_export();" class="cssBtn"');
	showXHTML_input('button', '', $MSG['title86'][$sysSession->lang]    , '', 'onclick="window.location.href=\'stud_export.php\'" class="cssBtn"');
	$extra_btn = ob_get_contents();
	ob_end_clean();
    
    showXHTML_script('include', '/lib/xmlextras.js');
	require_once(sysDocumentRoot . '/academic/stat/pickCommon.php');	// �M�μ˪O����
	
	//  �ץX
    showXHTML_form_B('action="stud_field.php" method="post" enctype="multipart/form-data" style="display:none"', 'export_class');
	    $ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'stud_export' . $sysSession->username);
	    showXHTML_input('hidden', 'class_id', '', '', '');
	    showXHTML_input('hidden', 'ticket', $ticket, '', '');
    showXHTML_form_E();
    
// {{{ �D�{�� end
?>
