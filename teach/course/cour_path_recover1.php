<?php
	/**
     * 學習路徑備份還原 -- 刪除與還原動作
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     *          則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     *          照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      Edi Chen <edi@sun.net.tw>
     * @copyright   2000-2007 SunNet Tech. INC.
     * @version     CVS: $Id: cour_path_recover1.php,v 1.1 2010/02/24 02:40:23 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2007-01-03
     */

// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/teach_course.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
    
    # 47317 Chrome  點選記錄點選刪除下確定後出現亂碼文字，增加編碼程片段
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" >";
// }}} 函式庫引用 end

// {{{ 常數定義 begin

// }}} 常數定義 end

// {{{ 變數宣告 begin

// }}} 變數宣告 end

// {{{ 函數宣告 begin

// }}} 函數宣告 end

// {{{ 主程式 begin
	$get_cid = trim($_POST['cid']);
	$recover_cid = sysNewDecode($get_cid);

	if (empty($_POST['func']) || !in_array($_POST['func'], array('del', 'recover')))
		die('<script language="javascript">alert("Function Error!");location.replace("cour_path_recover.php");</script>');

	if ($_POST['func'] == 'del') {
		if (empty($_POST['sid'])  || !is_array($_POST['sid']) || count($_POST['sid']) === 0)
			die('<script language="javascript">alert("'.$MSG['no_select'][$sysSession->lang].'");location.replace("cour_path_recover.php");</script>');
		foreach ($_POST['sid'] as $key => $val)
		{
			$_POST['sid'][$key] = trim(sysNewDecode($val));
		}
		// list($max_sid) = dbGetStSr('WM_term_path', 'max(serial)', 'course_id = ' . $sysSession->course_id, ADODB_FETCH_NUM);
		list($max_sid) = dbGetStSr('WM_term_path', 'max(serial)', 'course_id = ' . $recover_cid, ADODB_FETCH_NUM);
		// dbDel('WM_term_path', 'course_id="'.$sysSession->course_id.'" and serial in ('.implode(',', $_POST['sid']).')');
		dbDel('WM_term_path', 'course_id="'.$recover_cid.'" and serial in ('.implode(',', $_POST['sid']).')');
		$result_msg = $sysConn->ErrorNo() == 0 ?
					$MSG['msg_del_success'][$sysSession->lang] :
					$MSG['msg_del_fail'][$sysSession->lang];

		// wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , $sysConn->ErrorNo(), 'teacher', $_SERVER['PHP_SELF'], $result_msg.':'.implode(',', $_POST['sid']));
		wmSysLog($sysSession->cur_func, $recover_cid , 0 , $sysConn->ErrorNo(), 'teacher', $_SERVER['PHP_SELF'], $result_msg.':'.implode(',', $_POST['sid']));
		echo '<script language="javascript">alert("' . $result_msg . '");location.replace("cour_path_recover.php?cid=' . $get_cid .(in_array($max_sid, $_POST['sid'])?'&cour_path_reload=true':'').'");</script>';
	}
	else {
		$_POST['rid'] = trim(sysNewDecode($_POST['rid']));
		if (empty($_POST['rid']))
			die('<script language="javascript">alert("'.$MSG['no_select'][$sysSession->lang].'");location.replace("cour_path_recover.php?cid=' . $get_cid . '");</script>');


		list($new_sid) = dbGetStSr('WM_term_path', 'max(IFNULL(serial, 0))+1', 'course_id = ' . $recover_cid, ADODB_FETCH_NUM);
		$sysConn->Execute(
			'insert into WM_term_path ' .
			'select `course_id`, ' . $new_sid . ', `content`, "' . $sysSession->username . '", now() ' .
			'from WM_term_path where course_id = ' . $recover_cid . ' and serial = ' . $_POST['rid']
		);

		$result_msg = $sysConn->ErrorNo() == 0 ?
					$MSG['msg_recover_success'][$sysSession->lang] :
					$MSG['msg_recover_fail'][$sysSession->lang];

		// wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , $sysConn->ErrorNo(), 'teacher', $_SERVER['PHP_SELF'], $result_msg.':'.$rid);
		wmSysLog($sysSession->cur_func, $recover_cid , 0 , $sysConn->ErrorNo(), 'teacher', $_SERVER['PHP_SELF'], $result_msg.':'.$rid);
		echo '<script language="javascript">alert("' . $result_msg . '");location.replace("cour_path_recover.php?cour_path_reload=true&cid='.$get_cid.'");</script>';
	}
// }}} 主程式 end
?>
