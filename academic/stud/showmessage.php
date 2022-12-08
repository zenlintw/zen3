<?php
	/**
	 * 審核帳號
	 * $Id: showmessage.php,v 1.1 2010/02/24 02:38:44 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/stud_authorisation.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/mooc/models/user.php');
	require_once(sysDocumentRoot . '/lib/login/login.inc');

	$sysSession->cur_func = '400300700';
	$sysSession->restore();
	if (!aclVerifyPermission(400300700, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$allow_account_array = preg_split('/[^\w.-]+/', $_POST['userarray'] , -1, PREG_SPLIT_NO_EMPTY);
	$account_num         = count($allow_account_array);					// 全部需要核可的人數

	if ($account_num == 0)
	{
		die('<script language="javascript">
				alert("'.$MSG['message1'][$sysSession->lang].'");
				window.location.href="stud_authorisation.php";
		     </script>');
	}

	/**
	 * 將 check_in 與 check_out 流程整合成一個
	 *
	 * @param   $status
	 * @param   $mode
	 * @param   $messages
	 */
	function check_main($mode)
	{
	    global $sysSession, $MSG, $allow_account_array, $account_num;

		$messages = $mode == 'check_out' ?
		            array('deline4','deline5','deline','fail_account') :
		            array('pass2','pass4','pass','verify_account');

		$mail_pattern	= sysDocumentRoot . "/base/$sysSession->school_id/{$messages[3]}_{$sysSession->lang}.mail";	// 信件範本
		$att_file_path	= sysDocumentRoot . "/base/$sysSession->school_id/attach/{$messages[3]}";				// 附檔路徑

		$subject  = '';
		$tmp_body = '';
		// ========== 1.先讀取檔案中的原始信件檔案(每封信件共用資訊) ==========
		if (file_exists($mail_pattern))
		{
			$fd = fopen ($mail_pattern, "r");
			// 信件標題
			$subject = fgets($fd, 1024);
			// 讀取信件內文
			while (!feof ($fd)) {
				$tmp_body .= fgets($fd, 4096);
			}
			fclose($fd);
		}
		else
		{
			// 信件標題
			$subject = $MSG[$messages[3] . '_subject'][$sysSession->lang];
			// 信件內文
			$tmp_body = $MSG[$messages[3] . '_body'][$sysSession->lang];
		}

		// ========== 2.從資料庫中取出必要的資訊(每封信件共用資訊) ==========
		$school_name       = $sysSession->school_name;			// 學校名稱
		$school_host       = $_SERVER['HTTP_HOST'];				// 學校網址
		list($school_mail) = dbGetStSr('WM_school','school_mail',"school_id='{$sysSession->school_id}' and school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);

		// ========== 3.取出信件夾檔名稱(每封信件共用資訊) ==========
		// 取得所有附加檔案名稱
		if (is_dir($att_file_path)){
			$att_files = getAllFile($att_file_path);
		}

		// HTML 頁面輸出
		showXHTML_head_B($MSG['edit_mail1'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
		showXHTML_script('inline', $js);
		showXHTML_head_E();
		showXHTML_body_B();
		showXHTML_table_B('width="600" border="0" cellspacing="0" cellpadding="0"');
			// 顯示 tab 的標記
			showXHTML_tr_B();
				showXHTML_td_B();
					$arry[] = array($MSG[$mode][$sysSession->lang], 'addTable1');
					showXHTML_tabs($arry, 1);
				showXHTML_td_E();
			showXHTML_tr_E();
			// 顯示 tab 的標記
		showXHTML_table_E();
		showXHTML_table_B('width="600" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
			showXHTML_tr_B('class="cssTrHead"');
				showXHTML_td('align="center"  width="40"' , $MSG['sn'][$sysSession->lang]);
				showXHTML_td('align="center"  width="80"' , $MSG['account'][$sysSession->lang]);
				showXHTML_td('align="center"  width="280"', $MSG['email'][$sysSession->lang]);
				showXHTML_td('align="center"  width="200"', $MSG['status'][$sysSession->lang]);
			showXHTML_tr_E();

			// ※※※※※※ 進入信件處理過程 ※※※※※※

			// 寄件者
			if (empty($school_mail))
				$school_mail = 'webmaster@' . $school_host;
			$from = mailEncFrom($school_name,$school_mail);

			for ($i = 0; $i < $account_num; $i++)
			{
				// ========== 從資料庫中取出必要的資訊(每封信件個別資訊) ==========
                                $RS_1      = dbGetStSr(sysDBname.'.CO_mooc_account AS T1 LEFT JOIN '.sysDBprefix . $sysSession->school_id.'.CO_user_verify AS T2 ON T1.username=T2.username','T1.first_name,T1.last_name,T1.email,T2.reg_time', "T1.username='{$allow_account_array[$i]}'", ADODB_FETCH_ASSOC);		// 取出該使用者的姓名、帳號及email資料
				$user_name = checkRealname($RS_1['first_name'], $RS_1['last_name']);
				// 學員帳號
				$user_id   = $allow_account_array[$i];
				// 3.收件者
				$u_email   = strtolower($RS_1['email']);

				showXHTML_tr_B('class="' . ($bg = $bg == 'cssTrEvn' ? 'cssTrOdd' : 'cssTrEvn') . '"');
					// 序號
					showXHTML_td('align="center" width="40"' , $i + 1);
					showXHTML_td('align="center" width="80"' , $user_id);
					showXHTML_td('align="center" width="280"', $RS_1['email']);

					
					
					// 刪除帳號 (check_out 才需要)
					if ($mode == 'check_out') {
					    $username = $user_id;
				        $rsUser = new user();
				        // 刪除註冊暫存資料
                            $aryUser[] = array('username' => $username, 'email' => $u_email);
                            $rsUser->delExpiredTmpUsers($aryUser);
		            }
									// 信件內容
									$mail_body  = str_replace(array('%SCHOOL_NAME%', '%SCHOOL_HOST%', '%REAL_NAME%', '%USERNAME%'),
																array($school_name   , $school_host   , $user_name   , $user_id),
																$tmp_body);
									// 每次進入都必須重新宣告一個新的 mail 類別
									$mail       = buildMail('', $subject, $mail_body, 'html', '', '', '', '', false);
									// 2.寄件者
									$mail->from	= $from;
									$mail->to   = $u_email;
									// ========== 處理附加檔案 ==========
									$att_count  = count($att_files);
									for ($j = 0; $j < $att_count; $j++)
									{
										$data = file_get_contents($att_file_path . DIRECTORY_SEPARATOR . $att_files[$j]);
										// 5.信件夾檔
										$mail->add_attachment($data, $att_files[$j]);
									}
									$mail->send();
									$result_msg = $MSG[$messages[2]][$sysSession->lang];
					

					showXHTML_td('nowrap ',$result_msg);
				showXHTML_tr_E();

				if ($mode == 'check_in')
				{
					// ※※※※※※ 帳號核可 ※※※※※※
					//dbSet('WM_user_account', "enable='Y'", "username='" . trim($allow_account_array[$i]) . "'");
					$username = $user_id;
					$rsUser = new user();
					
				    // 判斷 wm_all_account有沒有資料
                    $account = $rsUser->getSimpleProfileByUsername($username);
                    if (count($account) === 0) {
                         // 取暫存資料
                         $data = $rsUser->getTmpProfileByUsername($username);
                         if (!empty($RS_1['reg_time'])) $data['reg_time'] = $RS_1['reg_time'];
                         addUser($username, $data, 'N');
                         dbSet(sysDBname.'.`WM_sch4user`',sprintf("`reg_time` = '%s'",$RS_1['reg_time']),"`username` = '" . $username . "'");
                         // 刪除註冊暫存資料
                         $aryUser[] = array('username' => $username, 'email' => $u_email);
                         $rsUser->delExpiredTmpUsers($aryUser);
                    }

                    // 判斷是否需要管理者審核，如果是自由註冊，則連動將帳號啟用
                    // 取自由註冊設定值Y自由N不開放C開放但是需要管理者審核
                    $regStatus = getSchoolRegStatus();
                    if ($regStatus === 'Y' || $regStatus === 'C') {
                        $rsUser->setUserEnable($username);
                    }
					
				}
			}

			wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], ($mode == 'check_in' ? '審核通過:' : '帳號不核可:') . implode(',' , $allow_account_array));
			showXHTML_tr_B('class="'.($bg = $bg == 'cssTrEvn'?'cssTrOdd':'cssTrEvn').'"');
				showXHTML_td_B(' colspan="4" align="center"');
					showXHTML_input('button', '', $MSG['back'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'stud_authorisation.php\');"');
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
	}

	if (in_array($_POST['mode'], array('check_in', 'check_out')))
		check_main($_POST['mode']);
?>
