<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/forum/lib_collect.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '900200900';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (ereg('^54([01]),([0-9]{10}),([0-9]{6,}),([0-9]{10})\.php$', basename($_SERVER['PHP_SELF']), $reg)){

		$type     = $reg[1];	// �h���νƻs
		$board_id = $reg[2];
		$node     = $reg[3];
		$site     = $reg[4];

		// �p�G�O������A�h����
		if ($board_id != $sysSession->board_id) {
		   wmSysLog($sysSession->cur_func, $sysSession->class_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], 'Error Board id');
		   die('Error Board id: '.$sysSession->board_id);
		}

		// ���J��ذ�
		$ret = do_collect($board_id, $node, $site, $new_node, DIRECTORY_SEPARATOR, $type);
		wmSysLog($sysSession->cur_func, $board_id , $node, $ret, 'auto', $_SERVER['PHP_SELF'], ($type ? 'Move to Essential:' : 'Copy to Essential:') . $new_node);
		$str = $ret!=$err_id['sucecss']?($MSG['collect'][$sysSession->lang] . $MSG['fail_to'][$sysSession->lang].
						'(' . $err_msg[$ret]. ',' . $MSG['try_later'][$sysSession->lang] . ')'):$err_msg[$ret];
		//$str .= "\\nret=$ret\\n";
		do_exit($str, $MSG['ok'][$sysSession->lang]);
	}
	else {
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], ' error !');
		die('<h2 align="center"><br />error !</h2>');
	}

	function do_exit($js_msg, $bt_msg) {
		$js = 'window.close();';
		if ($js_msg != '')
			// $js = "alert('{$js_msg}');\n{$js}";
            $js = "";
            
            // #47296 Chrome �T����ܶýX�A�W�[�s�X���q
            // #48437 chrome�u���ϡv�Q�ת��峹�I��u���J��ذϡv��A�X�{��U Alert �����C�G����alert�A�諾����ܦb�����W
            showXHTML_head_B($MSG['read'][$sysSession->lang]);
            showXHTML_script('inline',$js);
            showXHTML_head_E();
            echo $js_msg;
            echo '<p>';
            echo "<input type='button' name='close' value='".$bt_msg."' onClick='window.close();'>";
            
		exit();
	}

	do_exit('nothing', $MSG['ok'][$sysSession->lang]);
?>
