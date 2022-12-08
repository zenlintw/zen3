<?php
	/**
	 * �޲z�� - �ɮv�޲z
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
	 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
	 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
	 *
	 * @package     WM3
	 * @author      amm <amm@sun.net.tw>
	 * @copyright   2000-2005 SunNet Tech. INC.
	 * @version     CVS: $Id: director_main.php,v 1.1 2010/02/24 02:38:15 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2006-1-4
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/academic_director.php');

	// �ܼƫŧi begin
	
	// �O�_�w�إ߯Z��
	list($class_num) = dbGetStSr('WM_class_main','count(*) as num','class_id > 1000000', ADODB_FETCH_NUM);

	// �ɮv��ƺ޲z radio ����
	$hava_class_radio = array(1 => $MSG['title7'][$sysSession->lang],
							  2 => $MSG['title8'][$sysSession->lang],
							  3 => $MSG['title9'][$sysSession->lang]);
	// �ܼƫŧi end

	// ��ƫŧi begin

	// ��ƫŧi end

	// �D�{�� begin

	$js = <<< BOF
		function next_stage() {
			var act_val = '';
			var options = document.getElementsByTagName('input');
			for(var i = 0;i < options.length;i++){
				if ((options[i].type =="radio") && (options[i].checked)) {
					act_val = parseInt(options[i].value);
					break;
				}
			}
			var go_next = '';
			switch(act_val){
				case 1:
					go_next = 'director_add.php';
					break;
				case 2:
					go_next = 'director_add.php?type=remove';
					break;
				case 3:
					go_next = 'director_add.php?type=query';
					break;				
			}
			window.location.href=go_next;
		}
BOF;
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['title'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'Dnote', 'Dtable', 'style="display: inline"');
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				if($class_num == 0) {
					showXHTML_tr_B('class="cssTrHead"');
						showXHTML_td_B('');
							echo $MSG['title2'][$sysSession->lang];
						showXHTML_td_E();
					showXHTML_tr_E();
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('');
							echo $MSG['title3'][$sysSession->lang] . '<br>' .
								$MSG['title4'][$sysSession->lang];
						showXHTML_td_E();
					showXHTML_tr_E();
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('align="center"');
							showXHTML_input('button', '', $MSG['title5'][$sysSession->lang], '', 'onclick="javascript:window.location.replace(\'class_group.php\');" class="cssBtn"');
						showXHTML_td_E();
					showXHTML_tr_E();
				}else{
					showXHTML_tr_B('class="cssTrHead"');
						showXHTML_td_B('');
							if($_GET['type'] == 2){	// �ק�Ψ����G�ק�Ψ����w�s�b���ɮv(�ΧU�z)��¾��
								echo $MSG['title63'][$sysSession->lang];
							}else{	// �s�W�G�s�W�@�өΦh�Ӿɮv(�ΧU�z)��Y�ӯZ�Ť�
								echo $MSG['title6'][$sysSession->lang];
							}
						showXHTML_td_E();
					showXHTML_tr_E();
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('');
							showXHTML_input('radio', 'go_next', $hava_class_radio ,'1','', '<br>');
						showXHTML_td_E();
					showXHTML_tr_E();
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('align="center"');
							showXHTML_input('button', '', $MSG['title11'][$sysSession->lang], '', 'class="cssBtn" onclick="next_stage()" ');
						showXHTML_td_E();
					showXHTML_tr_E();
				}
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';

	// �D�{�� end
?>
