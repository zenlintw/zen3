<?php
   /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/18                                                                      *
	*		work for  : 寄給管理者做備份                                                                        *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       @version $Id: send_adm_regmail.php,v 1.1 2010/02/24 02:38:44 saly Exp $
	*                                                                                                 *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/archive_api.php');
	require_once(sysDocumentRoot . '/lang/send_register_mail.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');

	$sysSession->cur_func = '500100100';
	$sysSession->restore();
	if (!aclVerifyPermission(500100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 郵件的原始信件檔案
	$target	= sysDocumentRoot . "/base/{$sysSession->school_id}/add_account.mail";

	// 郵件的原始信件檔案  的夾檔路徑
	$att_file_path	= sysDocumentRoot . "/base/{$sysSession->school_id}/attach/add_account";

	$ticket = md5($sysSession->ticket . 'sendMail' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);

	if (trim($_POST['ticket']) != $ticket) {
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['illege_access'][$sysSession->lang]);
	}

	// 寄件者

	// ========== 1.先讀取檔案中的原始信件檔案(每封信件共用資訊) ==========
	if (file_exists($target)){
		// 先讀取 郵件的原始信件檔案 資料
		$fd = fopen($target, "r");

		// 讀取標題
		$temp = fgets($fd, 1024);
		$tmp_subject = $temp;

		fclose($fd);
	}else{
		// 讀取標題
		$tmp_subject = $MSG['add_account_subject'][$sysSession->lang];
	}

	// ========== 2.從資料庫中取出必要的資訊(每封信件共用資訊) ==========
	$school_name	   = $sysSession->school_name;			// 學校名稱
	$school_host	   = $_SERVER['HTTP_HOST'];				// 學校網址
	list($school_mail) = dbGetStSr('WM_school','school_mail',"school_id='{$sysSession->school_id}' and school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);

	if (empty($school_mail)){
		$school_mail = 	'webmaster@'. $school_host;
	}
	$from = mailEncFrom($school_name, $school_mail);
	// ========== 3.取出信件夾檔名稱(每封信件共用資訊) ==========

	// ** 產生 壓縮檔 begin **
	$temp_dir = sysDocumentRoot . '/base/' . $sysSession->school_id . '/';
	$temp     = 'WM' . sprintf("%c%c%c%03d",mt_rand(97, 122),mt_rand(97, 122),mt_rand(97, 122),mt_rand(1, 999));
	// 壓縮檔名
	$zip_name = $temp . '.zip';
	chdir($temp_dir);

	// 給 ZipArchive 函數使用的變數
	$zip_lib = new ZipArchive_php4($zip_name,"",false,'',$temp_dir);

	$mail_txt = str_replace(array('/theme/default/academic/icon_currect.gif', '/theme/default/academic/icon_wrong.gif'),
							array('icon_currect.gif', 'icon_wrong.gif'),
							stripslashes($_POST['mail_txt']));
	//  夾檔 (begin)
	$icon_currect = file_get_contents(sysDocumentRoot . '/theme/default/academic/icon_currect.gif');
	$icon_wrong   = file_get_contents(sysDocumentRoot . '/theme/default/academic/icon_wrong.gif');

	$zip_lib->add_string($icon_currect, 'icon_currect.gif');
	$zip_lib->add_string($icon_wrong  , 'icon_wrong.gif');
	$zip_lib->add_string($mail_txt    , 'register_result.htm');

	// *******
	// 取得所有附加檔案名稱
	if (is_dir($att_file_path))
		$att_files = getAllFile($att_file_path);

	// 信件的夾檔
	for ($i = 0; $i < count($att_files); $i++) {
		// file name
		$file_name = $att_files[$i];

		// file content
		$file_content = file_get_contents($att_file_path . DIRECTORY_SEPARATOR . $att_files[$i]);
		$zip_lib->add_string($file_content,$file_name);
	}

	// ※※※※※※ 進入信件處理過程 ※※※※※※

	// 1.讀取標題
	$subject = trim($tmp_subject) . '(' . $sysSession->school_name .')';
	$subject = htmlspecialchars($subject, ENT_QUOTES);

	// 壓縮檔的內容
	$zip_content = file_get_contents($zip_name);
	// ** 產生 壓縮檔 end **

	$zip_name = 'register_result.zip';
	@touch(sysTempPath . DIRECTORY_SEPARATOR . $zip_name);

	if ($fp = fopen(sysTempPath . DIRECTORY_SEPARATOR . $zip_name, 'w')) {
		@fwrite($fp, $zip_content);
		fclose($fp);
	}

	// 夾 zip 檔 到 訊息中心 的某個使用者目錄下
	$ret1 = cpAttach($sysSession->username, sysTempPath, "{$zip_name}\t{$zip_name}");
	unlink(sysTempPath . DIRECTORY_SEPARATOR . $zip_name);

	// 寄一份到收件匣備份去(收件者為平台帳號,故內部寄信)
	collect('sys_inbox', $sysSession->username, $sysSession->username, '', $subject, $content, 'html', '', $ret1, '', '', '');
	// 寄到管理者的信箱

	// 每次進入都必須重新宣告一個新的 mail 類別
	$mail = buildMail('', $subject, '&nbsp;', 'html', '', '', '', '', false);

	// 2.寄件者
	$mail->from = $from;
	$mail->to   = $sysSession->email;
	$mail->add_attachment($zip_content,'register_result.zip');
	$mail->send();
	//   夾檔 (end)

	// 刪除 產生的壓縮檔 及 被壓縮檔
	$zip_lib->delete();

	// 回到 程式執行的目錄
	chdir(sysDocumentRoot . '/academic/stud/');

	$msg = $MSG['send_to'][$sysSession->lang] . $sysSession->email;

	echo <<< EOF
		<script language="javascript">
			alert('{$msg}');
			location.replace('stud_account.php?msgtp=3');
		</script>
EOF;
?>