<?php
	/**
	 * 管理者 - 導師管理 - 新增 / 卸除 的 csv的 預覽
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
	 * @version     CVS: $Id: director_add_csv_preview.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2005-09-22
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/academic_director.php');

	if($_POST['type'] == 'remove'){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務
		$exec_func = '2400200200';
		$remove_type = 'type=' . trim($_GET['type']);
	}else{	// 新增：新增一個或多個導師(或助理)到某個班級中
		$exec_func = '2400200100';
	}
	$sysSession->cur_func=$exec_func;
	$sysSession->restore();
	if (!aclVerifyPermission($exec_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}
	// 變數宣告 begin
	$filename = tempnam(dirname($_FILES['cvsfile']['tmp_name']), 'impf');
	rename($_FILES['cvsfile']['tmp_name'], $filename);

	// 設定匯入檔案所使用的語系
	$lang = ($_POST['file_format'] ? $_POST['file_format'] : $sysSession->lang);
	// 讀取檔案
	$fp	= fopen($filename, 'r');

	// 變數宣告 end

	// 函數宣告 begin
	// 函數宣告 end

	// 主程式 begin

	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['title'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'Dnote', 'Dtable', 'action="director_save.php" method="post" style="display: inline"');
			if($_POST['type'] == 'remove'){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務
				$ticket = md5(sysTicketSeed . 'Delete_assistant' . $_COOKIE['idx'] . $sysSession->username);
				showXHTML_input('hidden', 'type', 'remove', '', '');
				showXHTML_input('hidden', 'action', 'DEL_CSV', '', '');
			}else{	// 新增：新增一個或多個導師(或助理)到某個班級中
				$ticket = md5(sysTicketSeed . 'Director_add' . $_COOKIE['idx'] . $sysSession->username);
				showXHTML_input('hidden', 'action', 'ADD_CSV', '', '');
			}
			showXHTML_input('hidden', 'ticket', $ticket, '', '');

			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
						showXHTML_td_B('colspan="6"');
							if($_GET['type'] == 'remove'){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務
								echo $MSG['title69'][$sysSession->lang];
							}else{	// 新增：新增一個或多個導師(或助理)到某個班級中
								echo $MSG['title34'][$sysSession->lang];
							}
						showXHTML_td_E();
					showXHTML_tr_E();
					showXHTML_tr_B('class="cssTrHead"');
						if($_GET['type'] == 'remove'){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務
							showXHTML_td('align="center"',$MSG['title36'][$sysSession->lang]);
							showXHTML_td('align="center"',$MSG['title66'][$sysSession->lang]);
							showXHTML_td('align="center"',$MSG['title40'][$sysSession->lang]);
							showXHTML_td('align="center"',$MSG['title67'][$sysSession->lang]);
						}else{
							showXHTML_td('align="center"',$MSG['title36'][$sysSession->lang]);
							showXHTML_td('align="center"',$MSG['title37'][$sysSession->lang]);
							showXHTML_td('align="center"',$MSG['title38'][$sysSession->lang]);
							showXHTML_td('align="center"',$MSG['title39'][$sysSession->lang]);
							showXHTML_td('align="center"',$MSG['title40'][$sysSession->lang]);
							showXHTML_td('align="center"',$MSG['title41'][$sysSession->lang]);
						}
					showXHTML_tr_E();
					$line1 = true;

					// 判斷匯入的格式正確的筆數
					$success_count = 0;

					while($csvdata=fgets($fp, 4096)){	// 取得一行文字
						//	去除UTF-8的檔頭 Begin
						if ($line1) {
							if ($lang == 'UTF-8' && strtolower(bin2hex(substr($csvdata, 0 , 3))) == 'efbbbf'){
								$csvdata = substr($csvdata, 3);
								$line1   = false;
							}
						}

						//	去除UTF-8的檔頭 End
						$csvdata             = iconv($lang, 'UTF-8', trim($csvdata));
						$csv_ary             = explode(',', str_replace(' ', '', $csvdata));

						$check_result        = '';
						$class_id            = '';
						$user                = '';
						$user_role           = '';
						// 判斷匯入的格式是否正確
						$success_flag_class  = true;
						$success_flag_member = true;
						$success_flag_id     = true;

						// 判斷班級代碼是否存在
						list($class_id,$class_name) = dbGetStSr('WM_class_main','class_id,caption','dep_id="' . $csv_ary[0]  . '"', ADODB_FETCH_NUM);
						if(strlen($class_name) > 0){
							$tmp_class_name = unserialize($class_name);
							$class_name1    = $tmp_class_name[$sysSession->lang];
						}else{
							$success_flag_class = false;
							$csv_ary[0]       = '<font color="red">' . $csv_ary[0] . '</font>';
							$class_name1      = '<font color="red">' . $MSG['title42'][$sysSession->lang] . '</font>';

							if($_GET['type'] == 'remove'){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務
								$class_id     = '<font color="red">' . $csv_ary[0] . '</font>';
								$check_result = '<font color="red">' . $MSG['title68'][$sysSession->lang] . '</font>';
							}
						}
						// 判斷帳號是否存在
						$RS = dbGetStSr('WM_class_member','username,role','username="'.htmlspecialchars($csv_ary[1]).'" and class_id="'.$class_id.'"', ADODB_FETCH_ASSOC);
						$user_exist = checkUsername( htmlspecialchars($csv_ary[1]));
						if($_GET['type'] == 'remove'){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務
							if(($user_exist == 2) and (count($RS['username']) != 0)) { // 帳號存在，且為該班級成員
								$user =  htmlspecialchars($csv_ary[1]);
							}
							else if (($user_exist == 2) and  (count($RS['username']) == 0)){ // 帳號存在，但不為該班級成員
								$success_flag_member = false;
								$user                = '<font color="red">' .  htmlspecialchars($csv_ary[1]) . '</font>';
								$check_result        = '<font color="red">' . $MSG['not_class_member'][$sysSession->lang] . '</font>';
							}
							else{ // 帳號根本不存在
								$success_flag_member = false;
								$user = '<font color="red">' .  htmlspecialchars($csv_ary[1]) . '</font>';

								if (strlen($check_result) == 0){
									$check_result = '<font color="red">' . $MSG['title68'][$sysSession->lang] . '</font>';
								}
							}
						}else{
							if($user_exist == 2) {
								// 查詢姓名
								$user_ary  = getUserDetailData( htmlspecialchars($csv_ary[1]));
								$user_name = $user_ary['realname'];
							}else{
								$success_flag_member = false;
								$csv_ary[1] = '<font color="red">' .  htmlspecialchars($csv_ary[1]) . '</font>';
								$user_name  = '<font color="red">' . $MSG['title43'][$sysSession->lang] . '</font>';
							}
						}
						// 身份
						if($_GET['type'] == 'remove'){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務
							$roleArray = explode(",",$RS['role']);
							switch(intval($csv_ary[2])){
								case 32:
								case 64:
								case 1024:
									$user_role = $csv_ary[2];
									break;
								default:
									$user_role = '<font color="red">' . $csv_ary[2] . '</font>';
									if (strlen($check_result) == 0){
										$check_result = '<font color="red">' . $MSG['wrong_id_number'][$sysSession->lang] . '</font>';
									}
									$success_flag_id = false;
									break;
							}
							if(strlen($check_result) == 0) {
								if (in_array($csv_ary[2],$roleArray)){
									$check_result = $MSG['title70'][$sysSession->lang];
								}
								else{
									// Bug#1108-若不在array中，則表示要變更身份 by Small 2006/11/9
									$success_flag_id = true;
									// $user_role = '<font color="red">' . $csv_ary[2] . '</font>';
									$user_role = $csv_ary[2];

									if (strlen($check_result) == 0){
										// $check_result = '<font color="red">' . $MSG['change_id_number'][$sysSession->lang] . '</font>';
										$check_result = $MSG['change_id_number'][$sysSession->lang];
									}
								}

							}
						}else{
							switch(intval($csv_ary[2])){
								case 64:
									$user_role = $MSG['title44'][$sysSession->lang];
									break;
								case 1024:
									$user_role = $MSG['title45'][$sysSession->lang];
									break;
								default:
									$success_flag_id = false;
									$csv_ary[2] = '<font color="red">' . $csv_ary[2] . '</font>';
									$user_role = '<font color="red">' . $MSG['title46'][$sysSession->lang] . '</font>';
									break;
							}
						}
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
						if($_GET['type'] == 'remove'){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務
							showXHTML_td('align="center"',$csv_ary[0]);
							showXHTML_td('align="center"',$user);
							showXHTML_td('align="center"',$user_role);
							showXHTML_td('align="center"',$check_result);
						}else{
							showXHTML_td('align="center"',$csv_ary[0]);
							showXHTML_td('align="center"',$class_name1);
							showXHTML_td('align="center"',$csv_ary[1]);
							showXHTML_td('align="center"',$user_name);
							showXHTML_td('align="center"',$csv_ary[2]);
							showXHTML_td('align="center"',$user_role);
						}
						if($success_flag_class and $success_flag_member and $success_flag_id) {
							$success_count++;
							$sysRoleMap = array_flip($sysRoles);
							$tmp_data = base64_encode($class_id . ',' .  htmlspecialchars($csv_ary[1]) . ',' . $sysRoleMap[$csv_ary[2]]);
							showXHTML_input('hidden', 'user[]', $tmp_data, '', '');
						}
						showXHTML_tr_E();
					}
					@unlink($_FILES['cvsfile']['tmp_name']);

					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="6" align="center"');
							showXHTML_input('button', '', $MSG['title16'][$sysSession->lang], '', 'class="cssBtn" onclick="javascript:window.location.href=\'director_add_csv.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '\';" ');
							showXHTML_input('submit', '', $MSG['title35'][$sysSession->lang], '', 'class="cssBtn"' . (($success_count > 0) ? '': ' disabled'));
						showXHTML_td_E();
					showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	// 主程式 end
?>
