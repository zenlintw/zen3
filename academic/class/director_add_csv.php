<?php
	/**
	 * �޲z�� - �ɮv�޲z - �s�W / ���� -  csv
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
	 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
	 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
	 *
	 * @package     WM3
	 * @author      Amm Lee <amm@sun.net.tw>
	 * @copyright   2000-2005 SunNet Tech. INC.
	 * @version     CVS: $Id: director_add_csv.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2006-01-05
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/academic_director.php');
    
	if($_GET['type'] == 'remove'){	// �ק�Ψ����G�ק�Ψ����w�s�b���ɮv(�ΧU�z)��¾��
		$exec_func = '2400200200';
		$remove_type = 'type=' . trim($_GET['type']);
	}else{	// �s�W�G�s�W�@�өΦh�Ӿɮv(�ΧU�z)��Y�ӯZ�Ť�
		$exec_func = '2400200100';
	}
	
	$sysSession->cur_func=$exec_func;
	$sysSession->restore();
	if (!aclVerifyPermission($exec_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}

	// �ܼƫŧi begin
	// �ܼƫŧi end

	// ��ƫŧi begin
	// ��ƫŧi end

	// �D�{�� begin
	$js = <<< BOF
	function next_stage() {
		var fobj = document.Dnote;
		if(fobj.cvsfile.value == ''){
			alert("{$MSG['title33'][$sysSession->lang]}");
			return false;
		}
		fobj.submit();
	}
BOF;
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['title'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'Dnote', 'Dtable', 'action="director_add_csv_preview.php" method="post" encType="multipart/form-data" style="display: inline"');
				if($_GET['type'] == 'remove'){
					showXHTML_input('hidden', 'type', $_GET['type'] , '', '');
				}
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
						showXHTML_td_B('colspan="3"');
							echo $MSG['title17'][$sysSession->lang];
						showXHTML_td_E();
					showXHTML_tr_E();
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('');
							showXHTML_input('file', 'cvsfile', '', '', 'id="csvfile" size="27" class="cssInput"');
						showXHTML_td_E();
						showXHTML_td_B('');
							echo $MSG['title18'][$sysSession->lang];
						showXHTML_td_E();
						showXHTML_td_B('');
							showXHTML_input('button', '', $MSG['title19'][$sysSession->lang], '', 'id="btn_csv_example" class="cssBtn" onclick="OpenNamedWin(\'director_add_csvhelp.php\',\'csvhelpwin\',800,480)"');
						showXHTML_td_E();
					showXHTML_tr_E();
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('');
							$file_type = array(
									'Big5'	  => $MSG['title20'][$sysSession->lang],
									'GB2312'  => $MSG['title21'][$sysSession->lang],
									'en'      => $MSG['title22'][$sysSession->lang],
				//	   �����B�z��� 'EUC-JP'  => $MSG['title23'][$sysSession->lang],
									'UTF-8'	  => $MSG['title24'][$sysSession->lang],
									);
							showXHTML_input('select', 'file_format', $file_type, ($sysSession->lang == 'user_define' ? 'UTF-8' : $sysSession->lang), 'class="cssInput" style="width: 158px"');
						showXHTML_td_E();
						showXHTML_td_B('colspan="2"');
							echo $MSG['title25'][$sysSession->lang];
						showXHTML_td_E();
					showXHTML_tr_E();

					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="3" align="center"');
							showXHTML_input('button', '', $MSG['title16'][$sysSession->lang], '', 'class="cssBtn" onclick="javascript:window.location.href=\'director_add.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '\';" ');
							showXHTML_input('button', '', $MSG['title11'][$sysSession->lang], '', 'class="cssBtn" onclick="next_stage()" ');
						showXHTML_td_E();
					showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	// �D�{�� end
?>
