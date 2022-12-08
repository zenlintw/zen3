<?php
	/**
	 * 管理者 - 導師管理 - 先找出特定的班級-再新增班級導師 / 卸除 或 修改 - 預覽
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Amm Lee <amm@sun.net.tw>
	 * @copyright   2000-2005 SunNet Tech. INC.
	 * @version     CVS: $Id: director_choose_class_preview.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2005-09-22
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/academic_director.php');

	// 變數宣告 begin
	$sType    = trim($_POST['searchkey']);
	$sWord    = trim($_POST['keyword']);
	$page_num = intval($_POST['page_num']);
	$page     = intval($_POST['page']);
	$class_id = trim($_POST['class_id']);

	list($class_caption) = dbGetStSr('WM_class_main','caption','class_id=' . base64_decode($class_id), ADODB_FETCH_NUM);

	if(strlen($class_caption) == 0) {
		header('Location: director_choose_class.php?type=remove');
	}
	$class_name = unserialize($class_caption);

	if($_GET['type'] == 'remove'){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務
		$exec_func = '2400200200';
		$remove_type = 'type=' . trim($_GET['type']);
		$html_title = str_replace('%CLASS%','<font color="#0000FF">' . $class_name[$sysSession->lang] . '</font>',$MSG['title74'][$sysSession->lang]);
		$btn_confirm = $MSG['title75'][$sysSession->lang];
	}else{	// 新增：新增一個或多個導師(或助理)到某個班級中
		$exec_func = '2400200100';
		$html_title = str_replace('%CLASS%','<font color="#0000FF">' . $class_name[$sysSession->lang] . '</font>',$MSG['title90'][$sysSession->lang]);
		$btn_confirm = $MSG['title59'][$sysSession->lang];
	}
	$pre_chk_user = array();	// 上一頁有勾選的帳號 array

	$sysSession->cur_func=$exec_func;
	$sysSession->restore();
	if (!aclVerifyPermission($exec_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}

	// 變數宣告 end

	// 函數宣告 begin
	// 函數宣告 end

	// 主程式 begin
	$js = <<< BOF
	function Pre_Page() {
		var fobj = document.DirectFm;
		fobj.submit();
	}
BOF;
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['title'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'Dnote', 'Dtable', 'action="director_save.php" method="post" style="display: inline"');
			if($_GET['type'] == 'remove'){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務
				$ticket = md5(sysTicketSeed . 'Delete_assistant' . $_COOKIE['idx'] . $sysSession->username);
				showXHTML_input('hidden', 'type', 'remove', '', '');
				showXHTML_input('hidden', 'action', 'DEL_CHOOSE_CLASS', '', '');
			}else{
				$ticket = md5(sysTicketSeed . 'Director_add' . $_COOKIE['idx'] . $sysSession->username);
				showXHTML_input('hidden', 'action', 'ADD_CHOOSE_CLASS', '', '');
			}
			showXHTML_input('hidden', 'ticket', $ticket, '', '');
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('colspan="3"', $html_title);
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="3" align="center"');
						showXHTML_input('button', '', $MSG['title16'][$sysSession->lang], '', 'class="cssBtn" onclick="Pre_Page();" ');
						showXHTML_input('submit', '', $btn_confirm, '', 'class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="center"',$MSG['title38'][$sysSession->lang]);
					showXHTML_td('align="center"',$MSG['title39'][$sysSession->lang]);
					showXHTML_td('align="center"',$MSG['title41'][$sysSession->lang]);
				showXHTML_tr_E();
				if (is_array($_POST['select_role']))
				{
					foreach($_POST['select_role'] as $key=>$val) {
						$tmp_data = base64_decode($val);
						$tmp_ary = explode('_',$tmp_data);
						$user_ary = getUserDetailData($tmp_ary[0]);

						$user_role = '';
						$role_value = '';
						switch($tmp_ary[2]){
							case 'director':
								$user_role = $MSG['title45'][$sysSession->lang];
								break;
							case 'assistant':
								$user_role = $MSG['title44'][$sysSession->lang];
								break;
							case 'DEL':
								$user_role = $MSG['title73'][$sysSession->lang];
								break;
						}
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
						showXHTML_td('align="left"',$tmp_ary[0]);
						showXHTML_td('align="left"',$user_ary['realname']);
						showXHTML_td('align="left"',$user_role);
						$tmp_data = base64_encode($tmp_ary[1] . ',' . $tmp_ary[0] . ',' . $tmp_ary[2]);
						showXHTML_input('hidden', 'user[]', $tmp_data, '', '');
						$pre_chk_user[] = $tmp_ary[0];
					}
				}
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
		if(count($pre_chk_user) > 0) {
			$tmp_chk_user = base64_encode(implode(',',$pre_chk_user));
		}else{
			$tmp_chk_user = '';
		}
		showXHTML_form_B('action="director_choose_director.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" enctype="multipart/form-data" style="display:none"', 'DirectFm');
			showXHTML_input('hidden', 'class_id'  , $class_id, '', 'id="class_id"');
			showXHTML_input('hidden', 'pre_chk_user'  , $tmp_chk_user, '', 'id="class_id"');
		showXHTML_form_E('');
	// 主程式 end
?>
