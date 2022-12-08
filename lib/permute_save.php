<?php
	/**
	 * 儲存列表管理順序
	 *
	 * @since   2012/03/01
	 * @author  Kuko Wang
	 * @version $Id: forum_type_permute_save.php $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/review.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$ticket = md5(sysTicketSeed . 'savePermute' . $_COOKIE['idx']);
	if (trim($_POST['ticket']) != $ticket) {
		$js = <<< BOF
    	    window.onload = function () {
        		alert("{$MSG['access_deny'][$sysSession->lang]}");
        		window.close();
    	    };
BOF;
		showXHTML_script('inline', $js);
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$nids = preg_split('/\D+/', $_POST['nodeids'], -1, PREG_SPLIT_NO_EMPTY);
	$min = is_array($_POST['pmutes']) ? min($_POST['pmutes']) : 0;

	//資料庫更新
	
	switch($_GET['type']) {
	    case 'adv':
	        $table='CO_adv';
	        $cond="`adv_id`=";
	        break;
	    case 'links':
	        $table='CO_links';
	        $cond="`links_id`=";
	    	break;
	}

	foreach ($nids as $val) {
		dbSet($table, "permute={$min}", $cond.trim($val));
		$min++;
	}
        
	//完成訊息框
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		echo '<div align="center">';
		showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
			showXHTML_tr_B('class="cssTrHelp"');
				showXHTML_td('align="center"', $MSG['msg_permute_save'][$sysSession->lang]);
			showXHTML_tr_E();
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td_B('align="center"');
					showXHTML_input('button', 'id', $MSG['btn_close'][$sysSession->lang], '', 'class="cssBtn" onclick="top.window.close();"');
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
		echo '</div>';
	showXHTML_body_E();
?>
