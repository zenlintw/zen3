<?php
    /**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: amm lee                                                         *
	 *		Creation  : 2004/01/08                                                            *
	 *		work for  : 匯出人員資料 (第四步驟 -> 匯出 => 全校 )                                                                      *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.4                                *
	 *      $Id: stud_field1.php,v 1.1 2010/02/24 02:38:44 saly Exp $                                                                                          *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/archive_api.php');
	require_once(sysDocumentRoot . '/lang/stud_export.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400400200';
	$sysSession->restore();
	if (!aclVerifyPermission(400400200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (!defined('EXPORT_TYPE')) define('EXPORT_TYPE', 'single');

	$ticket = md5($sysSession->ticket . 'export_data' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
	if ($_POST['ticket'] != $ticket) die($MSG['msg_access_deny'][$sysSession->lang]);

	/**
	* 取得 帳號
	*
	* @param array $ RS : RecordSet array value
	*/
	function get_user($table_name, $query_cond)
	{
		global $sysConn;

		chkSchoolId($table_name);
		$ary   = $sysConn->GetCol('select username from ' . $table_name . ' where ' . $query_cond);
		$users = array_unique($ary);
		if (array_search(sysRootAccount, $users) !== false) unset($users[$key]);
		return count($users) ? ('"' . implode('","', $users) . '"') : '';
	}

	/**
	* get account all information
	*
	* @param array $ RS : RecordSet array value
	* @param array $ ex_type : attach file name
	*/
	function get_file_result(&$RS, $ex_type)
	{
		global $fieldSet;

		$res = array();
		$ary = $fieldSet;
		$key = array_search('first_name,last_name', $ary);
		if ($key !== false) $ary[$key] = 'realname';
		while ($data = $RS->FetchRow())
		{
			$data['realname'] = checkRealname($data['first_name'], $data['last_name']);
			foreach ($ex_type as $key)
			{
				switch ($key)
				{
					case 'csv' :
						foreach ($ary as $val)
						{
							$res[$key] .= $data[$val] . ',';
						}
						$res[$key] = substr($res[$key], 0, -1) . "\r\n";
						break;

					case 'html' :
						$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
						$res[$key] .= '<tr class="' . $col . '">';
						foreach ($ary as $val)
						{
							$res[$key] .= '<td>' . $data[$val] . '&nbsp;</td>';
						}
						$res[$key] .= '</tr>' . "\r\n";
						break;

					case 'xml' :
						$res[$key] .= '<user>';
						foreach ($ary as $val)
						{
							$res[$key] .= sprintf('<%s>%s</%s>', $val, htmlspecialchars($data[$val]), $val);
						}
						$res[$key] .= '</user>' . "\r\n";
						break;
				}
			}
		}
		return $res;
	}

	/**
	* get title
	*
	* @param array $ c_name : class or course name
	* @param array $ ex_type : attach file name
	*/
	function get_title($ex_type)
	{
		global $fieldAry, $fieldTitle, $fieldSet;

		$res = array();
		$ary = $fieldSet;
		foreach ($ex_type as $ext)
		{
			$str = '';
			switch ($ext)
			{
				case 'csv' :
					foreach ($ary as $val)
						$str .= $fieldTitle[$val] . ',';
					$str = substr($str, 0, -1);
					break;
				case 'html' :
					foreach ($ary as $val)
						$str .= '<td align="center">' . $fieldTitle[$val] . '</td>';
					if (!empty($str)) $str = '<tr>' . $str . '</tr>';
					break;
				case 'xml' :
					$key = array_search('first_name,last_name', $ary);
					if ($key !== false) $ary[$key] = 'realname';
					foreach ($ary as $val)
						$str .= sprintf("<%s>%s</%s>\r\n", $val, $fieldTitle[$val], $val);
					break;
			}
			$res[$ext] = $str;
		}
		return $res;
	}

	function get_content(&$RS, $ex_file, $title='')
	{
		global $sysSession, $MSG, $fieldSet, $resTitle;

		$resAry = array();
		$users  = get_file_result($RS, $ex_file);
		foreach ($ex_file as $val)
		{
			switch ($val)
			{
				case 'csv':
					if (!empty($users[$val]))
					{
						$resAry[$val] = $title . "\r\n" .
							$resTitle[$val] . "\r\n" . $users[$val];
					}
					break;

				case 'html':
					if (!empty($users[$val]))
					{
						$sysCL = array('Big5' => 'zh-tw', 'en' => 'en', 'GB2312' => 'zh-cn');
						$lang  = isset($sysCL[$sysSession->lang]) ? $sysCL[$sysSession->lang] : 'zh-tw';
						$num   = count($fieldSet);
						$resAry[$val] = <<< BOF
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Content-Language" content="{$lang}" />
		<title>{$MSG['title'][$sysSession->lang]}</title>
		<style type="text/css">
			.bg01 { background-color: #E3E9F2; }
			.bg02 { background-color: #CCCCE6; }
			.cssTrEvn { background-color: #FFFFFF; }
			.cssTrOdd { background-color: #EAEAF4; }
			.font01 { font-size: 12px; line-height: 16px; color: #000000; text-decoration: none ; letter-spacing: 2px; }
		</style>
	</head>
	<body>
		<table id="stud_list" width="100%" border="0" cellspacing="1" cellpadding="3" id="delTable2" class="box01">
			<tr class="bg02">
				<td colspan="{$num}">{$title}</td>
			</tr>
			{$resTitle[$val]}
			{$users[$val]}
		</table>
	</body>
</html>
BOF;
					}
					break;

				case 'xml':
					if (!empty($users[$val]))
					{
						$resAry[$val]  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>';
						$resAry[$val] .= <<< BOF

<!--
	every field tagname statement
	<title>{$title}</title>
	<user>
		{$resTitle[$val]}
	</user>
-->
<manifest>
{$users[$val]}
</manifest>
BOF;
					}
					break;
			}
		}
		return $resAry;
	}

	// 檢查有無收件者 (Begin)
	$to = '';
	if (($_POST['send_email'] != '') && ($_POST['send_email'] != $MSG['title44'][$sysSession->lang]))
	{
		$tmp = preg_split('/[^\w.@-]+/', $_POST['send_email'], -1, PREG_SPLIT_NO_EMPTY);
		$to  = implode(', ', $tmp);
	}

	if (empty($to))
	{
		echo <<< BOF
<script>
	alert("{$MSG['title61'][$sysSession->lang]}");
	window.location.replace("{$back_href}");
</script>
BOF;
		die();
	}
	// 檢查有無收件者 (End)

	$fieldAry = array(
		'', 'username', 'first_name,last_name', 'gender', 'birthday', 'personal_id', 'email', 'homepage',
		'cell_phone', 'home_tel', 'home_fax', 'home_address', 'office_tel', 'office_fax', 'office_address',
		'company', 'department', 'title'
	);
	$fieldTitle = array();
	for ($i = 1, $c = count($fieldAry); $i < $c; $i++)
	{
		$idx = sprintf('title%d', 21 + $i);
		$fieldTitle[$fieldAry[$i]] = $MSG[$idx][$sysSession->lang];
	}
	$fieldTitle['realname'] = $fieldTitle['first_name,last_name']; // 匯出 XML 時需要用到

	$fieldSet = array();
	if (is_array($_POST['field']))
	{
		$field_num = count($_POST['field']);
		foreach ($_POST['field'] as $val)
		{
			$val        = intval($val);
			$fieldSet[] = $fieldAry[$val];
		}
	}
	else
	{
		// 未指定欄位則全部列出
		unset($fieldAry[0]); // 去掉第一個空的資料
		$fieldSet  = $fieldAry;
		$field_num = count($fieldAry);
	}
	// $fieldSet[]    = 'language';
	$export_fields = implode(',', $fieldSet);

	$class_data    = preg_split('/\D+/', $_POST['class_data'] , -1, PREG_SPLIT_NO_EMPTY);
	$course_data   = preg_split('/\D+/', $_POST['course_data'], -1, PREG_SPLIT_NO_EMPTY);

	if (count($course_data) > 0)
		$back_href = 'course_set.php';
	elseif (count($class_data) > 0)
		$back_href = 'class_group.php';
	else
		die($MSG['msg_access_deny'][$sysSession->lang]);

	$resAry        = array();
	$resTitle      = get_title($_POST['ex_file']);

	if (in_array(1000000, $class_data) || in_array(10000000, $course_data))
	{
		// 匯出全部的人員
		$RS    = dbGetStMr('WM_user_account', $export_fields, ' 1 order by username asc', ADODB_FETCH_ASSOC);
		$title = $MSG['title59'][$sysSession->lang] . $sysSession->school_name;
		if ($RS) $resAry = get_content($RS, $_POST['ex_file'], $title);
	}
	else
	{
		$roles = 0;
		if (is_array($_POST['role']) && count($_POST['role']))
		{
			foreach ($_POST['role'] as $key => $val)
			{
				$roles |= intval($sysRoles[$key]);
			}
		}
		if ($roles == 0)
		{
			wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], 'roles id can not empty!');
			die($MSG['msg_access_deny'][$sysSession->lang]);
		}

		if (EXPORT_TYPE == 'single')
		{
			// *** 匯出成一個檔案 (Begin) ***
			$total_users = '';

			if (count($class_data) > 0)
			{
				// 班級
				$query_table = 'WM_class_member';
				$cond        = 'class_id in (' . implode(',', $class_data) . ') and (role & ' . $roles . ')';
			}
			elseif (count($course_data) > 0)
			{
				// 課程
				$query_table = 'WM_term_major';
				$cond        = 'course_id in (' . implode(',', $course_data) . ') and (role & ' . $roles . ')';
			}
			else
			{
				wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], 'course id and class id can not empty!');
				die($MSG['msg_access_deny'][$sysSession->lang]);
			}
			// 匯出部份人員
			$total_users = get_user($query_table, $cond);
			$RS          = dbGetStMr('WM_user_account', $export_fields, 'username in (' . $total_users . ') order by username asc', ADODB_FETCH_ASSOC);
			$title       = $MSG['title59'][$sysSession->lang] . $sysSession->school_name;
			if ($RS) $resAry = get_content($RS, $_POST['ex_file'], $title);
			// *** 匯出成一個檔案 (End) ***
		}
		else
		{
			// *** 匯出成多個檔案 (Begin) ***
			if (count($class_data) > 0)
			{
				// 班級
				$idents = array($class_data,  'WM_class_member', ' class_id = ',  'WM_class_main',  'title58');
			}
			elseif (count($course_data) > 0)
			{
				// 課程
				$idents = array($course_data, 'WM_term_major' ,  ' course_id = ', 'WM_term_course', 'title57');
			}
			else
			{
				wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], 'course id and class id can not empty!');
				die($MSG['msg_access_deny'][$sysSession->lang]);
			}

			$object_data1    = $idents[0];
			$object_data_num = count($object_data1);
			$object_data2    = array();

			for($i = 0, $c = count($object_data1); $i < $c; $i++)
			{
				$cond  = $idents[2] . $object_data1[$i] . ' and (role & ' . $roles . ')';
				$users = get_user($idents[1], $cond);
				$object_data2[$object_data1[$i]] = $users;
			}

			while (list($key, $val) = each($object_data2))
			{
				$resdata = array();
				$q_table = $idents[3];
				$q_where = $idents[2] . $key;

				if (substr($val, -1) == ',')
					$val = substr($val, 0, -1);

				list($c_names) = dbGetStSr($q_table, 'caption', $q_where, ADODB_FETCH_NUM);
				$lang   = unserialize($c_names);
				$title  = $MSG[$idents[4]][$sysSession->lang] . $lang[$sysSession->lang];
				$RS     = dbGetStMr('WM_user_account', $export_fields, 'username in (' . $val . ') order by username asc', ADODB_FETCH_ASSOC);
				if ($RS) $resdata = get_content($RS, $_POST['ex_file'], $title);
				if (count($resdata) > 0)
				{
					foreach ($resdata as $idx => $val)
					{
						$resAry[$key . '_' . $idx] = $val;
					}
				}
			}
			// *** 匯出成多個檔案 (End) ***
		}
	}

	if (count($resAry) <= 0)
	{
		$scr =<<< BOF
			alert("{$MSG['title62'][$sysSession->lang]}");
			window.location.replace("{$back_href}");
BOF;
		showXHTML_script('inline', $scr);
		die();
	}

	// ***********************************************************************************
	$ret1    = '';
	$subject = $sysSession->school_name . ' - ' . $MSG['title'][$sysSession->lang];
	// $content = '<img src="http://' . $_SERVER['HTTP_HOST'] . '/mail_count.php?mailid=' .
	//	$sysSession->school_id . '_' . $InsertID . '" style="display:none">' . $MSG['title60'][$sysSession->lang];
	$content = $MSG['title60'][$sysSession->lang];

	// ** 產生 壓縮檔 begin **
	$temp_dir = sysDocumentRoot . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR . $sysSession->school_id . DIRECTORY_SEPARATOR;
	$temp     = 'WM' . sprintf("%c%c%c%03d", mt_rand(97, 122), mt_rand(97, 122), mt_rand(97, 122), mt_rand(1, 999));
	$zip_name = $temp . '.zip'; // 壓縮檔名

	chdir($temp_dir);
	$zip_lib = new ZipArchive_php4($zip_name, '', false, '', $temp_dir);

	$attach_file = '';
	while (list($key, $val) = each($resAry))
	{
		if (!(empty($key) && empty($val)))
		{
			$temp_file = (EXPORT_TYPE == 'single') ? 'school.' . $key : str_replace('_', '.', $key);
			$attach_file .= $temp_file . ',';
			$zip_lib->add_string($val, $temp_file);
		}
	}
	$attach_file = substr($attach_file, 0, -1);

	// 若沒有夾檔則不寄送信件
	if (empty($attach_file))
	{
		echo <<< BOF
<script>
	alert("{$MSG['not_send_email'][$sysSession->lang]}");
	window.location.replace("{$back_href}");
</script>
BOF;
		die();
	}

	// 匯出資料，備份一份至個人收件夾中 (Begin)
	$ret1 = cpAttach($sysSession->username, $temp_dir, array('school.zip', $zip_name));
	if (!empty($ret1))
	{
		collect('sys_inbox', $sysSession->username, $sysSession->username, '', $subject, $content, 'html', '', $ret1, '', '', '');
	}
	// 匯出資料，備份一份至個人收件夾中 (End)

	$zip_content = file_get_contents($temp_dir . $zip_name); // 壓縮檔的內容
	$mail = buildMail('', $subject, $content, 'html', '', '', '', '', false);
	$mail->add_attachment($zip_content, 'school.zip');
	$mail->to = $to; // 以寄件者為to
	// $mail->headers = 'Bcc: ' . $sysSession->email;
	$mail->send();

	// 刪除 產生的壓縮檔 及 被壓縮檔
	$zip_lib->delete();
	// 回到 程式執行的目錄
	chdir(sysDocumentRoot . '/academic/stud/');
	// ** 產生 壓縮檔 end **

    showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E('');
	showXHTML_body_B('');
		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary   = array();
					$ary[] = array($MSG['title45'][$sysSession->lang], 'tabs1');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" class="cssTable"');
					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" ');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td('nowrap="nowrap" align="center"', $MSG['title46'][$sysSession->lang]);
							showXHTML_td(' nowrap="nowrap" align="center"', $MSG['title47'][$sysSession->lang]);
							showXHTML_td(' nowrap="nowrap" align="center"', $MSG['title48'][$sysSession->lang]);
						showXHTML_tr_E('');

						$tmp = explode(',', $to);
						$i = 1;
						foreach ($tmp as $val)
						{
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('width="40" nowrap="nowrap" align="center"', $i++);
								showXHTML_td('width="80" nowrap="nowrap"', trim($val));
								showXHTML_td('nowrap="nowrap"', $MSG['title49'][$sysSession->lang]);
							showXHTML_tr_E('');
						}

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="3" align="center"');
								showXHTML_input('button', '', $MSG['title50'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'/academic/stud/stud_export.php\')"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_body_E('');
?>
