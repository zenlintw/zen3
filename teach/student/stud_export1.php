<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/07/15                                                            *
	 *		work for  :                                                                       *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *      $Id: stud_export1.php,v 1.2 2010/03/01 03:56:29 small Exp $                                                                                          *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/archive_api.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/lang/teach_student.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	$sysSession->cur_func = '400400100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$course_id = $sysSession->course_id; //10000000;

// **************************************************************************************

    /**
     * get account all information
     * @param array RS : RecordSet array value
     * @param array ex_type : attach file name
     **/
    function get_file_result(&$RS, $ex_type) {
        global $_POST, $field_num;

        $temp = '';

        switch ($ex_type){
            case 'csv':
                while ($data = $RS->FetchRow()) {
                    $temp .= $data['username'];

                    for ($k = 0;$k < $field_num;$k++){
                        if ($_POST['fields'][$k] == 'first_name,last_name'){
                            $temp .= checkRealname($data['first_name'], $data['last_name']) . ',';
                        }else{
                            $temp .= $data[$_POST['fields'][$k]] . ',';
                        }
                    }

                    $temp = preg_replace('/,$/', "\r\n", $temp);
                }

                break;
            case 'html':
                while ($data = $RS->FetchRow()) {
                    $col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
                    $temp .= '<tr class="' . $col . '">';
                    for ($k = 0;$k < $field_num;$k++){

                        if ($_POST['fields'][$k] == 'first_name,last_name'){
                            $temp .= '<td>' . checkRealname($data['first_name'], $data['last_name']) . '</td>';
                        }else{
                            $temp .= '<td>' . $data[$_POST['fields'][$k]] . '</td>';
                        }

                        if ($k == ($fields_num -1)){
                            $temp .= '</tr>';
                        }
                    }
                }

                break;
            case 'xml':
                while ($data = $RS->FetchRow()) {
                    $c++;
                	$temp .= '<user>';
                    for ($k = 0;$k < $field_num;$k++){
                        //*************************
                        if ($_POST['fields'][$k] == 'first_name,last_name'){
                            $temp_str = checkRealname($data['first_name'], $data['last_name']);

                            if (strlen($temp_str) > 0){
                                $temp .= '<realname>' . htmlspecialchars($temp_str) . '</realname>';
                            }else{
                                $temp .= '<realname/>';
                            }

                        }else{

                            $temp_str = $data[$_POST['fields'][$k]];

                            if (strlen($temp_str) > 0){
                                $temp .= '<' . $_POST['fields'][$k] . '>' . htmlspecialchars($temp_str) . '</' . $_POST['fields'][$k] . '>';
                            }else{
                                $temp .= '<' . $_POST['fields'][$k] . '/>';
                            }
                        }
                        //*************************
                    }
                    $temp .= '</user>';
                }
                break;
        }

        return $temp;
    }
//  *******************************************************************************
    /**
     * get title
     * @param array c_name : class or course name
     * @param array ex_type : attach file name
     **/
    function get_title($ex_type){
        global $MSG,$sysSession,$_POST,$field_num;

        $MSG['ex_realname'] = $MSG['realname'];

        switch ($ex_type){
            case 'csv':
		        for ($k = 0;$k < $field_num;$k++){
		            $field = ($_POST['fields'][$k] == 'first_name,last_name') ? 'realname' : $_POST['fields'][$k];
		            $csv_data1 .= $MSG['ex_' . $field][$sysSession->lang] . ',';
		        }
                return $csv_data1;
                break;
            case 'html':
		        for ($k = 0;$k < $field_num;$k++){
		            $field = ($_POST['fields'][$k] == 'first_name,last_name') ? 'realname' : $_POST['fields'][$k];
		            $html_data1 .= '<td align="center">' . $MSG['ex_' . $field][$sysSession->lang]  . '</td>';
				}
				if (strlen($html_data1) > 0){
					$html_data1 .= '</tr>';
				}
                return $html_data1;
                break;
            case 'xml':
		        for ($k = 0;$k < $field_num;$k++){
		            $field = ($_POST['fields'][$k] == 'first_name,last_name') ? 'realname' : $_POST['fields'][$k];
		            $xml_data1 .= '<' . $field . '>' . htmlspecialchars($MSG['ex_' . $field][$sysSession->lang], ENT_NOQUOTES, 'UTF-8') . '</' . $field . ">\r\n";
				}
                return $xml_data1;
                break;
        }

    }
// *********************************************************************************************************************


    $roles1 = 0;
	if (is_array($_POST['role']) && count($_POST['role']) > 0){
	    while (list ($key,$val) = each ($_POST['role'])) {

	        switch ($key){
	            case 'teacher':
	            case 'instructor':
	            case 'assistant':
	            case 'senior':
	            case 'paterfamilias':
	            case 'student':
	            case 'auditor':
	                $roles1 |= $sysRoles[$key];
	                break;
	        }

	    }
	}

    // 查詢 WM_user_account
    chkSchoolId('WM_user_account');
	$field_num = 0;
	$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
    if (is_array($_POST['fields'])){
        $user_columns = $sysConn->GetCol('show columns from WM_user_account'); // 取得目前 WM_user_account 的所有欄位
        $user_columns[] = 'first_name,last_name';
        $_POST['fields'] = array_intersect($_POST['fields'], $user_columns);   // 用交集來過濾掉不存在的欄位
        $_POST['fields'][] = 'language';
		$field_num = count($_POST['fields']);
		$export_fields = str_replace(',last_name', ',U.last_name', 'U.' . implode(',U.',$_POST['fields']));
	}

	if (substr_count($export_fields, ',') < 1) die('<script>alert("select fields first."); history.back();</script>');

	$sqls = "select $export_fields
			from WM_term_major as M,WM_user_account as U
			where M.course_id={$course_id} and (role & $roles1)
			and M.username=U.username
			order by U.username";
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$RS2 = $sysConn->Execute($sqls);
	$total_users = $RS2->RecordCount();

	if (!is_array($_POST['ex_file'])) $_POST['ex_file'] = array();
    for ($i = 0, $temp_num = count($_POST['ex_file']); $i < $temp_num; $i++) {
		switch ($_POST['ex_file'][$i]){
			case 'csv':
				if ($total_users > 0){
					if ($RS2) $users = get_file_result($RS2, 'csv');
				}
				$title = get_title('csv');

				if (strlen($users) > 0){
					$result_array['csv']  = $MSG['course_name'][$sysSession->lang] . $sysSession->course_name . "\r\n" . $title . "\r\n" . $users;
				}
				break;

			case 'html':
				if ($total_users > 0) {
					if ($RS2) $users = get_file_result($RS2, 'html');
    			}

                $title = get_title('html');

                if (strlen($users) > 0) {
                    $sysCL = array('Big5'=>'zh-tw','en'=>'en','GB2312'=>'zh-cn');
                    $ACCEPT_LANGUAGE = $sysCL[$sysSession->lang];
                    if (empty($ACCEPT_LANGUAGE)) $ACCEPT_LANGUAGE = 'zh-tw';

                    $users = '<html> ' .
                                 ' <head> ' .
                                 '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >' .
                                 ' <meta http-equiv="Content-Language" content="$ACCEPT_LANGUAGE" > ' .
                                 '<title>' . $MSG['stud_export'][$sysSession->lang] . ' </title> ' .
                                '<style type="text/css">'.
                                '.bg01 {'.
                                ' background-color: #E3E9F2;'.
                                '}'.
                                '.bg02 {'.
                                '  background-color: #CCCCE6;'.
                                '}'.
                                ' .cssTrEvn {' .
                                ' background-color: #FFFFFF;' .
                                ' }' .
                                '.cssTrOdd {' .
                                ' background-color: #EAEAF4;' .
                                '}' .
                                '.font01 {' .
                                'font-size: 12px;' .
                                'line-height: 16px;' .
                                'color: #000000; ' .
                                'text-decoration: none ;'.
                                'letter-spacing: 2px;'.
                                '}' .
                                '</style>'.
                                '</head>' .
                                '<body>' .
                                '<table id="stud_list" width="100%" border="0" cellspacing="1" cellpadding="3" id="delTable2" style="display:block" class="box01" >' .
                                '<tr class="bg02"><td colspan="' . $field_num . '">' . $MSG['course_name'][$sysSession->lang] . $sysSession->course_name . '</td></tr>' .
                                $title .
                                $users .
                                '</table>' .
                                '</body>' .
                                '</html>';

                    $result_array['html']  = $users;
                }
	    		break;
	    	case 'xml':
				if ($total_users > 0) {
					if ($RS2) $users = get_file_result($RS2, 'xml');
				}
                $title = get_title('xml');

                if (strlen($users) > 0) {
                    $users = '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n" .
                                '<!--' .
                                ' every field tagname statement ' . "\r\n" .
                                '<title>' . $MSG['course_name'][$sysSession->lang] . $sysSession->course_name . '</title>' . "\r\n" .
                                '<user>' . "\r\n" .
                                $title .
                                '</user>' .
                                '-->' .
                                '<total_user> ' . "\r\n" .
                                    $users .
                                '</total_user>';
                    $result_array['xml']  = $users;

                }
	    		break;
	    }
   		$RS2->MoveFirst();
	}

    $ret1 = '';
    $subject = $sysSession->course_name . '-' . $MSG['student_export'][$sysSession->lang];
    $content = $MSG['detail_content'][$sysSession->lang];

    /*
     *寄一份到收件匣備份去(收件者為平台帳號,故內部寄信) (end)
    */

	/**
	 * 切割過濾收件者的email
	 **/
	if (($_POST['email'] != '') && ($_POST['email'] != $MSG['email_msg'][$sysSession->lang])){
	    $to = preg_split('/[^\w@.-]+/', $_POST['email'], -1, PREG_SPLIT_NO_EMPTY);
	    $to_tmp = implode(', ', $to);
    }

	/*
     *寄一份到收件匣備份去(收件者為平台帳號,故內部寄信) (begin)
    */

	if ($total_users > 0){
		// ** 產生 壓縮檔 begin **
		$temp_dir = sysDocumentRoot . '/base/' . $sysSession->school_id . '/course/' . $sysSession->course_id . '/';

		$temp = 'WM' . sprintf("%c%c%c%03d",mt_rand(97, 122),mt_rand(97, 122),mt_rand(97, 122),mt_rand(1, 999));

		// 壓縮檔名
		$zip_name = $temp . '.zip';

		if (!@is_dir($temp_dir)) @mkdir($temp_dir, 0755);
		chdir($temp_dir);

		// use ZipArchive
		$attach_file = '';
	    $zip_lib = new ZipArchive_php4($zip_name,'',false,'',$temp_dir);

    	while (list($key,$val)=each($result_array)){
		if (! (empty($key) && empty($val))){

	        	$temp_file = $sysSession->course_id . '.' . $key;
				$attach_file .= $temp_file . ',';
	        	$zip_lib->add_string($val,$temp_file);
	    	}
    	}
		$attach_file = substr($attach_file,0,-1);

		// 壓縮檔的內容
		$zip_content = file_get_contents($temp_dir. $zip_name);

		$zip_name = $sysSession->course_id . '.zip';

		@touch($temp_dir . $zip_name);

    	if ($fp = fopen($temp_dir . $zip_name, 'w')) {
      		@fwrite($fp, $zip_content);
    	}

    	// 夾 zip 檔 到 訊息中心 的某個使用者目錄下
    	$zip_name1 = cpAttach($sysSession->username, $temp_dir, "{$zip_name}\t{$zip_name}");

    	$ret1 .= "\t" . $zip_name1;
    	fclose($fp);

		// $content1 = '<img src="http://'.$_SERVER['HTTP_HOST'].'/mail_count.php?mailid='. $sysSession->school_id . '_' . $InsertID . '" style="display:none">'.$content;
		$content1 = $content;

	  if (!empty($ret1)){
			// 寄到 訊息中心 的收件夾
		   collect('sys_inbox', $sysSession->username, $sysSession->username, '', $subject, $content1, 'html', '', $ret1, '', '', '');
		}

		$mail = buildMail('', $subject, $content1, 'html', '', '', '', '', false);

		$mail->add_attachment($zip_content,$sysSession->course_id . '.zip');

		$mail->to = $to_tmp;	// 以收件者為to
		$mail->headers = 'Bcc: ' . $sysSession->email;  // 以 $sysSession->email 為 bcc
		$mail->send();

		// 刪除 產生的壓縮檔 及 被壓縮檔
		$zip_lib->delete();

		$delete_file = explode(',',$attach_file);

		for ($i = 0;$i < count($delete_file);$i++){
			@unlink($temp_dir . $delete_file[$i]);
		}

		@unlink($temp_dir . $sysSession->course_id . '.zip');

		// 回到 程式執行的目錄
		chdir(sysDocumentRoot . '/teach/student/');
	}

	$array_len = count($result_array);

	$js = <<< BOF
		var array_size = {$array_len};

		function check_array_size(){

			if (array_size == 0){
				alert("{$MSG['no_data'][$sysSession->lang]}");
				window.location.replace("stud_export.php");
			}
		}
BOF;
    showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('onLoad="check_array_size();"');
		if ($array_len > 0){
		showXHTML_table_B('width="500" border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary   = array();
					$ary[] = array($MSG['student_export'][$sysSession->lang], 'tabs1');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" class="cssTable"');
					showXHTML_table_B('width="500" border="0" cellspacing="1" cellpadding="3" ');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td('nowrap="nowrap" align="center"', $MSG['serial'][$sysSession->lang]);
							showXHTML_td('nowrap="nowrap" align="center"', $MSG['receiver'][$sysSession->lang]);
							showXHTML_td('nowrap="nowrap" align="center"', $MSG['result'][$sysSession->lang]);
						showXHTML_tr_E('');

						for ($i = 0, $c = count($to); $i < $c; $i++) {

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('nowrap="nowrap" align="center"', $i + 1);
								showXHTML_td('nowrap="nowrap"', $to[$i]);
								$res = $MSG['send_state'][$sysSession->lang];

								/*
								$to2 = explode("@", $to[$i]);

                                if ($to2[0] == $sysSession->username) {
									// 取出使用者設定的信件匣備份的名稱
									$name_ary = nowPos('sys_sent_backup');
									$index = count($name_ary) - 1;
									$folder_name = $name_ary[$index];

									$res = $MSG['no_self1'][$sysSession->lang] . $folder_name . $MSG['no_self2'][$sysSession->lang];
								}
								*/
								showXHTML_td('nowrap="nowrap"', $res);
							showXHTML_tr_E('');
						}

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="3" align="center"');
								$target = 'stud_export.php';
								showXHTML_input('button', '', $MSG['return_student_export'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'' . $target . '\')"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
		}
	showXHTML_body_E('');

//  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
