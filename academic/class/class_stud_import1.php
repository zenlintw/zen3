<?php
	/**
	 * �\��W�� �פJ�Z�Ŧ���
	 * @since   2005/02/21
	 * @author  Amm Lee
 	 * @version $Id: class_stud_import1.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/class_stud_import.php');

	$sysSession->cur_func='2400500100';
	$sysSession->restore();
	if (!aclVerifyPermission(2400500100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	if ($_FILES['cvsfile']['name'] == ''){
		echo "<script language='javascript'>alert('" . $MSG['must_select_filename'][$sysSession->lang] . "');</script>";
		echo "<script language='javascript'>location.replace('class_stud_import.php')</script>";
		exit();
	}

$js = <<< BOF
	function go_previous(){
		window.location.replace('class_stud_import.php');
	}

BOF;
	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");

	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('leftmargin="7" topmargin="7"');
		$ary = array();
		$ary[] = array($MSG['title'][$sysSession->lang], 'tabs');
		showXHTML_tabFrame_B($ary, 1, 'stud_import', '', 'style="display:inline"', false);
			showXHTML_table_B('id ="mainTable" width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

				$col = 'class="cssTrHead"';
				showXHTML_tr_B($col);
					showXHTML_td('align="center" nowrap="nowrap"', $MSG['username'][$sysSession->lang]);	// �b��
					showXHTML_td('align="center" nowrap="nowrap"', $MSG['dep_id'][$sysSession->lang]);		// �Z�ťN�X
					showXHTML_td('align="center" nowrap="nowrap"', $MSG['dep_name'][$sysSession->lang]);	// �Z�ŦW��
					showXHTML_td('align="center" nowrap="nowrap"', $MSG['result'][$sysSession->lang]);		// �פJ���G
				showXHTML_tr_E('');

				// �ɦW
				$filename = tempnam(dirname($_FILES['cvsfile']['tmp_name']), 'impf');
    			rename($_FILES['cvsfile']['tmp_name'], $filename);
    			$lang = ($_POST['file_format'] ? $_POST['file_format'] : $sysSession->lang);	// �]�w�פJ�ɮשҨϥΪ��y�t

    			$fp	= fopen($filename, 'r');

				// �t�γ����N�X
				if ((strlen($_POST['class_id']) == 7) && is_numeric($_POST['class_id'])){
					$class_id = intVal($_POST['class_id']);

					list($caption,$db_dep_id) = dbGetStSr('WM_class_main','caption,dep_id','class_id=' . $class_id, ADODB_FETCH_NUM);

					$dep_lang = unserialize($caption);

					// �u�곡���W��
					$dep_name = $dep_lang[$sysSession->lang];

				}else{
					$class_id = '';

					// �u�곡���N�X
					$db_dep_id = '';

					// �u�곡���W��
					$dep_name = '';
				}

				// ���X�k���b�����~�N�X
				$illege_result = array(0,1,3,4,5);

    			// Ū���ɮ�
    			$line1 = true;
    			while($lines=fgets($fp, 1024)){
    				//	�h��UTF-8�����Y Begin
					if ($line1) {
						if ($lang == 'UTF-8' && strtolower(bin2hex(substr($lines, 0 , 3))) == 'efbbbf')
							$lines = substr($lines, 3);
						$line1 = false;
					}
					//	�h��UTF-8�����Y End

					$temp_data = explode(',',$lines);

					// �b��
					$user = '';
					$user = ($lang == 'Big5' || $lang == 'GB2312') ? iconv($lang,'UTF-8',trim($temp_data[0])) : trim($temp_data[0]);

					// �P�_�b���O�_�s�b
					$result = checkUsername($user);

					if ($user == sysRootAccount) $result = 4;	// �ˬd�t�γ̰��޲z�̱b��

					// �פJ�������N�X
					$dep_no = '';
					$dep_no = ($lang == 'Big5' || $lang == 'GB2312') ? iconv($lang,'UTF-8',trim($temp_data[1])) : trim($temp_data[1]);

					list($temp_class_id,$temp_caption) = dbGetStSr('WM_class_main','class_id,caption','dep_id="' . $dep_no . '"', ADODB_FETCH_NUM);

					$temp_lang = unserialize($temp_caption);

					// �פJ�������W��
					$temp_dep_name = '';
					$temp_dep_name = $temp_lang[$sysSession->lang];

					if (strlen($dep_no) > 0){
						$show_dep_name = $temp_dep_name;
						$show_dep_no = $dep_no;

					}else if (strlen($class_id) > 0){
						$show_dep_name = $dep_name;
						$show_dep_no = $db_dep_id;
					}

					if (in_array($result,$illege_result)){  // �P�_�b���O�_�s�b
						switch ($result){
							case 0:
								$account_msg = $MSG['account_error0'][$sysSession->lang];
								break;
							case 1:
								$account_msg = $MSG['account_error1'][$sysSession->lang];
								break;
							case 3:
								$account_msg = $MSG['account_error3'][$sysSession->lang];
								break;
							case 4:
								$account_msg = $MSG['account_error4'][$sysSession->lang];
								break;
						    case 5:
								$account_msg = $MSG['account_error5'][$sysSession->lang];
								break;		
						}

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('align="center" nowrap="nowrap"', $user);
							showXHTML_td('align="center" nowrap="nowrap"', $show_dep_no);
							showXHTML_td('align="center" nowrap="nowrap"', $show_dep_name);
							showXHTML_td('align="center" nowrap="nowrap"', $MSG['result_fail2'][$sysSession->lang]  . $account_msg);
						showXHTML_tr_E('');

					}else{

						$import_class_id = '';

						if (strlen($dep_no) > 0){
							if (strlen($temp_class_id) > 0){
								$import_class_id = $temp_class_id;
							}else{
								$export_msg = $MSG['result_fail2'][$sysSession->lang] . $MSG['result_dep_no_fail'][$sysSession->lang];
							}
						}else if (strlen($class_id) > 0){
							$import_class_id = $class_id;
						}else{
							$export_msg = $MSG['result_fail2'][$sysSession->lang] . $MSG['result_dep_no_fail2'][$sysSession->lang];
						}
						// if begin
						if (strlen($import_class_id) > 0){
							// if begin
							if ((strlen($import_class_id) == 7) && is_numeric($import_class_id)){
								// �P�_ ���L�s�b WM_class_director
								$ta_num = aclCheckRole($user, $sysRoles['director'], $import_class_id);
								// if begin
								if (!$ta_num){
									$sqls_value = $import_class_id . ',"' . $user . '",' . $sysRoles['student'];
									$RS = dbNew('WM_class_member','class_id,username,role',$sqls_value);
									if ($sysConn->ErrorNo() == 0){
										$export_msg = $MSG['result_success'][$sysSession->lang];
									}else{
										$export_msg = $MSG['result_account_fail'][$sysSession->lang];
									}
								}else{
									$export_msg = $MSG['result_account_fail'][$sysSession->lang];
								}
								// if end
							}
							// if end
						}
						// if end
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('align="center" nowrap="nowrap"', $user);
							showXHTML_td('align="center" nowrap="nowrap"', $show_dep_no);
							showXHTML_td('align="center" nowrap="nowrap"', $show_dep_name);
							showXHTML_td('align="center" nowrap="nowrap"', $export_msg);
						showXHTML_tr_E('');
					}
				}

				fclose($fp);
				// �R���W�Ǫ��ɮ�
				@unlink($_FILES['cvsfile']['tmp_name']);

				// �פJ
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center" colspan="4" nowrap="nowrap"');
						showXHTML_input('button', '', $MSG['return_preivous'][$sysSession->lang] , '', 'class="cssBtn" onclick="go_previous()"');
					showXHTML_td_E();
				showXHTML_tr_E('');

			showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E('');

?>
