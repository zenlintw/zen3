<?php
	/**
	 * 【程式功能】
	 * 建立日期：2004/08/16
	 * @author  Wiseguy Liang
	 * @version $Id: cour_import.php,v 1.1 2010/02/24 02:40:23 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teach_course.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '700600100';
	$sysSession->restore();
	if (!aclVerifyPermission(700600100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	// showXHTML_script('include', '/lib/dragLayer.js');
	// showXHTML_script('include', '/lib/xmlextras.js');
	// showXHTML_script('include', '/lib/common.js');
	$js = <<< EOB

function switchMode(m)
{
	var b = (m == 1) ? false : true;
	var nodes = document.getElementById('contentType').getElementsByTagName('input');
	// #47321 [教師/課程管理/教材匯入] 教材來源選擇「直接處理目前教材目錄內的檔案」，學習路徑處理的兩個radiobutton不應該被disable
    for(var i=0, c = nodes.length; i<c; i++) nodes[i].disabled = b;
	document.getElementById('package_file').disabled = b;
}

EOB;
	showXHTML_script('inline', $js);
	showXHTML_head_E();

	showXHTML_body_B();

		$ary[] = array($MSG['content_import'][$sysSession->lang], 'tabs');
		showXHTML_tabFrame_B($ary, 1, 'mainForm', 'ListTable', 'action="cour_import1.php" method="POST" enctype="multipart/form-data" style="display:inline"');
				showXHTML_table_B('id ="mainTable" width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
					showXHTML_tr_B('class="font01 cssTrHead"');
						showXHTML_td('nowrap colspan="3"', sprintf('%s<span style="color: red; font-weight: bold">%s</span>%s<span style="color: red; font-weight: bold">%s</span>', $MSG['max_upload_filesize'][$sysSession->lang], ini_get('upload_max_filesize'), $MSG['max_upload_totalsize'][$sysSession->lang], ini_get('post_max_size')));
					showXHTML_tr_E();

					showXHTML_tr_B('class="font01 cssTrEvn"');
						showXHTML_td('nowrap', $MSG['content_source'][$sysSession->lang]);
						showXHTML_td_B('colspan="2" nowrap');
						  showXHTML_input('radio', 'package_source', array(1 => ($MSG['source_1'][$sysSession->lang] . ' <input type="file" name="package_file" id="package_file" size="60" class="box02">'),
						  												   2 => ('<span onmouseover="this.style.color=\'#0000FF\';" onmouseout="this.style.color=\'\';" title="' . $MSG['source_2_hint'][$sysSession->lang] . '">' . $MSG['source_2'][$sysSession->lang] . '</span>')
						  												  ), 1, 'onclick="switchMode(this.value);"', "<br />\n");
						showXHTML_td_E();
					showXHTML_tr_E();
					showXHTML_tr_B('class="font01 cssTrOdd"');
						showXHTML_td('nowrap', $MSG['kind_of_content'][$sysSession->lang]);
						showXHTML_td_B('nowrap id="contentType"');
                            $ary = array(
                                1 => $MSG['kind_1'][$sysSession->lang],
                                2 => $MSG['kind_2'][$sysSession->lang],
                                3 => $MSG['kind_3'][$sysSession->lang],
                                4 => $MSG['kind_4'][$sysSession->lang],
                                5 => $MSG['kind_5'][$sysSession->lang],
                                6 => $MSG['kind_6'][$sysSession->lang]
                            );
                            // 檢查是否啟用 LCMS
                            if (defined('sysLcmsEnable') && sysLcmsEnable) {
                                $ary[7] = $MSG['kind_7'][$sysSession->lang];
                            }
						    showXHTML_input('radio', 'package_kind', $ary, 1, '', "<br>\n");
						showXHTML_td_E();
						showXHTML_td('', $MSG['package_kind_hint'][$sysSession->lang]);
					showXHTML_tr_E();
					showXHTML_tr_B('class="font01 cssTrEvn"');
						showXHTML_td('nowrap', $MSG['process_path'][$sysSession->lang]);
						showXHTML_td_B('nowrap');
						  showXHTML_input('radio', 'condition', array(1 => $MSG['process_1'][$sysSession->lang],
						  											  2 => $MSG['process_2'][$sysSession->lang],
						  											  3 => $MSG['process_3'][$sysSession->lang]),3,'', "<br>\n");
						showXHTML_td_E();
						showXHTML_td('', $MSG['how_to process_manifest'][$sysSession->lang]);
					showXHTML_tr_E();
					showXHTML_tr_B('class="font01 cssTrEvn"');
						showXHTML_td_B('colspan="3" nowrap align="center"');
						  showXHTML_input('submit', '', $MSG['msg_ok'][$sysSession->lang], '', 'class="cssBtn"');
						showXHTML_td_E();
					showXHTML_tr_E();

				showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E();

?>