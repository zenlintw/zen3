<?php
	/**
	 * 檔案說明
	 *	公告與聯繫 -> 寄給群組 -> 編輯郵件 -> 寄信
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Edi Chen <edi@sun.net.tw>
	 * @copyright   2000-2007 SunNet Tech. INC.
	 * @version     CVS: $Id: course_group_mail1.php,v 1.1 2010/02/24 02:38:39 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2007-03-16(新版直接套用/lib/wm_mails.php)
	 */

// {{{ 函式庫引用 begin    
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/wm_mails.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lang/course_manage.php');
// }}} 函式庫引用 end

// {{{ 函數宣告 begin
	
// }}} 函數宣告 end

// {{{ 主程式 begin
	
	// 將課程 ID 解碼
	$ary = explode(',', $_POST['csid']);
	foreach ($ary as $key => $val) {
		$ary[$key] = intval(sysDecode($val));
	}
	$course_id = implode(',', $ary);
	
	// 取得收信者名單 Begin
	$reciver  = trim($_POST['to']);	// 額外輸入的Email
    //#47457 [Safari][管理者/公告與聯繫/寄給群組/寄信發送結果] 收件者多了一筆「email」：若無@則清空
    if (strpos($reciver, '@') === false) {
        $reciver = '';
    };
		
	// 取得角色
	$role = array('student', 'assistant', 'instructor', 'teacher');
	$sel  = preg_split('/[^\w.-]+/', $_POST['roles'], -1, PREG_SPLIT_NO_EMPTY);
	$role = array_intersect($role, $sel);
	
	if (is_array($role) && count($role)) {
		$intRole = 0;
		foreach($role as $r)
			$intRole |= $sysRoles[$r];
		$strRole = 'and (' . $intRole . ')&role';
		
		// 取得Email
		chkSchoolId('WM_term_major');
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$email = $sysConn->GetCol('select distinct(U.email) from WM_term_major as T left join WM_user_account as U on T.username = U.username' . 
	                              ' where T.course_id in (' . $course_id . ') and U.email is not NULL ' . $strRole . ' order by U.username');
	    if ($reciver != '') $reciver .= ';';
	    if (count($email))  $reciver .= implode(';', $email);
	}

	// 取得收信者名單 End
	$mail = new wmMailSender();
	$mail->reciver     = $reciver;	// 不用檢查Email格式, 因為在wmMailSender中會作檢查
	$mail->priority    = intval($_POST['priority']);
	$mail->subject     = trim($_POST['subject']);
	$mail->content     = trim($_POST['content']);	// 在wm_mails.php中去除所有的不必要 html
	$mail->isHTML      = (intval($_POST['isHTML']) > 0);
	$mail->tagline     = intval($_POST['tagline']);
	$mail->title       = $MSG['tabs_send_result'][$sysSession->lang];
	$mail->send_kind   = 'split';
	$mail->uri_target  = 'course_set.php';
	$mail->memsg['btn_return'] = $MSG['goto_msg_center'][$sysSession->lang];
	$mail->send();
// }}} 主程式 end
?>
