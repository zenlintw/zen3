<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  : 人員 調動                                                                     *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       $Id: move_member.php,v 1.1 2010/02/24 02:38:15 saly Exp $
	*                                                                                                 *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
 	require_once(sysDocumentRoot . '/lang/people_manager.php');
 	require_once(sysDocumentRoot . '/lib/username.php');
 	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2400300300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{

	}

	$lang = strtolower($sysSession->lang);

	showXHTML_head_B($MSG['title27'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_head_E('');

	showXHTML_body_B('');

        showXHTML_table_B('width="760" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary = array();
					$ary[] = array($MSG['title116'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="transfer_member"');

					showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="ClassList" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td('align="left" nowrap="noWrap" colspan="4"', $MSG['title119'][$sysSession->lang]);
                        showXHTML_tr_E('');

                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td('align="center" nowrap="noWrap"', $MSG['title34'][$sysSession->lang]);
                            showXHTML_td('align="center" nowrap="noWrap"', $MSG['title33'][$sysSession->lang]);
                            showXHTML_td('align="center" nowrap="noWrap"', $MSG['title117'][$sysSession->lang]);
                            showXHTML_td('align="center" nowrap="noWrap"', $MSG['title118'][$sysSession->lang]);
                        showXHTML_tr_E('');


                        //  調動 (begin)
                        $student1 = preg_split('/[^\w.-]+/', $_POST['student'], -1, PREG_SPLIT_NO_EMPTY);
                        $s_num = count($student1);

                        $new_class1 = preg_split('/\D+/', $_POST['new_class'], -1, PREG_SPLIT_NO_EMPTY);
                        $c_num = count($new_class1);

                        //  if (begin)
                        if ($s_num > 0){
                            $_POST['old_class'] = intval($_POST['old_class']);

                            //  原屬的班級
                            list($caption) = dbGetStSr('WM_class_main', 'caption', 'class_id=' . $_POST['old_class'], ADODB_FETCH_NUM);
                            $lang = unserialize($caption);
	                        $csname = $lang[$sysSession->lang];

                            //  新屬的班級
	                        $captions = $sysConn->GetAssoc('select class_id, caption from WM_class_main where class_id in (' . implode(',',$new_class1) . ')');

                            //  student for (begin)
                            for ($i = 0;$i < $s_num;$i++){
                                //  查詢帳號 的 first_name & last_name 資料
                                list($last_name,$first_name) = dbGetStSr('WM_user_account', 'last_name,first_name', "username='". $student1[$i] . "'", ADODB_FETCH_NUM);

                                //  查詢 是存在 WM_class_member or WM_class_director 資料
								$role = $sysConn->GetOne('select role from WM_class_member where class_id=' . $_POST['old_class'] . " and username='". $student1[$i] . "'");
                                //  存放到資料庫是那個 table (end)

								$result = 0;
                                //  new_class for (begin)
                                for ($j = 0;$j < $c_num;$j++){

                                    $lang2 = unserialize($captions[$new_class1[$j]]);
		                            $csname2 = $lang2[$sysSession->lang];

									// 判斷 此人是否在 某個班級 俱有 導師助教
									$ta_num   = aclCheckRole($student1[$i], $sysRoles['director'] | $sysRoles['assistant'], $new_class1[$j]);
									$memb_num = aclCheckRole($student1[$i], $sysRoles['student'], $new_class1[$j]);

									if (!$ta_num && !$memb_num){
                                        $field = 'class_id,username,role';
                                        $value = $new_class1[$j] . ',' . "'" . $student1[$i] . "'," . $role;
                                        dbNew('WM_class_member',$field,$value);
                                        $result += $sysConn->Affected_Rows();

                                        // 記錄到 WM_log_manager
                                        $msg = $sysSession->username . ' move ' . $student1[$i] . ' from ' . $_POST['old_class'] . ' (old_class_id)  to  ' . $new_class1[$j]  . '  (new_class_id) , role = ' . $role;
                                        wmSysLog('2400300300',$sysSession->school_id,0,'0','manager',$_SERVER['SCRIPT_FILENAME'],$msg);
									}else{
										$csname2 .= '<font color="red">(' . $MSG['move_fail'][$sysSession->lang] . ')</font>';
									}

		                            $col=$col=='cssTrEvn'?'cssTrOdd':'cssTrEvn';

		                            showXHTML_tr_B('class="' . $col . ' "');
                                        showXHTML_td('align="center" nowrap="noWrap"', $student1[$i]);
                                        showXHTML_td('align="center" nowrap="noWrap"', checkRealname($first_name, $last_name));
                                        showXHTML_td('align="center" nowrap="noWrap"', $csname);
                                        showXHTML_td('align="center" nowrap="noWrap"', $csname2);
                                    showXHTML_tr_E('');

                                }
								// 刪除原屬的班級 SQL
                                if ($result) dbDel('WM_class_member', 'class_id=' . $_POST['old_class'] . " and username='". $student1[$i] . "'");
                                //  new_class for (end)
                            }
                            //  student for (end)

                        }else{
                            showXHTML_tr_B('class="cssTrHead"');
                                showXHTML_td('align="center" nowrap="noWrap" colspan="4"', $MSG['title120'][$sysSession->lang]);                ;
                            showXHTML_tr_E('');
                        }
                        //  if (end)
                    	//  調動 (end)

                        $col=$col=='cssTrEvn'?'cssTrOdd':'cssTrEvn';

                        //  回人員列表 (begin)
                        showXHTML_tr_B('class="' . $col . ' "');
                		    showXHTML_td_B('colspan="5" align="center"');
                				showXHTML_input('button', '', $MSG['title98'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'people_manager.php\');"');
                			showXHTML_td_E('');
                		showXHTML_tr_E('');
                		//  回人員列表 (end)

					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

    showXHTML_body_E('');
?>
