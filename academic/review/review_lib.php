<?php
	/**
	 * 審核的共用
	 *
	 * @since   2004/03/17
	 * @author  ShenTing Lin
	 * @version $Id: review_lib.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	if (!function_exists('divMsg')) {
		/**
		 * 處理資料，過長的部份隱藏
		 * @param integer $width   : 要顯示的寬度
		 * @param string  $caption : 顯示的文字
		 * @param string  $title   : 浮動的提示文字，若沒有設定，則跟 $caption 依樣
		 * @return string : 處理後的文字
		 **/
		function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
			if (empty($title)) $title = $caption;
			return $without_title ? ('<div style="width: ' . $width . 'px;overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $width . 'px;overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
		}
	}

	function selectSingleNode(&$dom, $expr) {
		$xpth = $dom->xpath_new_context();
		$xnode = xpath_eval($xpth, $expr);
		$value = null;
		if (isset($xnode->nodeset[0])) {
			$value = $xnode->nodeset[0];
		}
		return $value;
	}

	/**
	 * 顯示時間
	 * @param string $val : 時間格式的字串
	 * @return
	 **/
	function showDatetime($val) {
		global $sysSession, $sysConn, $MSG;
		$time = $sysConn->UnixTimeStamp($val);
		return (empty($time)) ? $MSG['unlimit'][$sysSession->lang] : date('Y-m-d H:i:s', $time);
	}

	/**
	 * 從 XML 中取得審核者的身份
	 * @param object $dom : xml dom 的物件
	 * @param object $id  : 哪個編號的審核者
	 * @return string : 審核者
	 **/
	function getChecker($dom, $id='WM_START') {
		if (!is_object($dom)) return false;
		$ctx   = xpath_new_context($dom);
		$xpath = '//activity[@id="' . $id . '"]/to/@account';
		$nodes = xpath_eval($ctx, $xpath);
		return $nodes->nodeset[0]->value;
	}

	/**
	 * 將從資料庫中取得的資料轉成 xml dom 的物件
	 * @param string $val : 要轉的字串
	 * @return object : xml dom 的物件
	 **/
	function loadRule($val) {
		if(!$dom = domxml_open_mem($val)) {
			$dom =null;
		}
		return $dom;
	}

//////////////////////////////////
	function showNum() {
		global $myTable;
		return $myTable->get_index();
	}

	$rv_user = array();
	function getUser($username) {
		global $rv_user;
		if (!isset($rv_user[$username])) {
			list($fn, $ln, $email) = dbGetStSr('WM_user_account', '`first_name`, `last_name`, `email`', "`username`='{$username}'", ADODB_FETCH_NUM);
			$name = checkRealname($fn,$ln);
			$rv_user[$username] = array($name, $email);
		}
		return $rv_user[$username];
	}

	function getEmail($username) {
		$user = getUser($username);
		return $user[1];
	}

	function getRealname($username) {
		$user = getUser($username);
		return $user[0];
	}

	function showUsername($username) {
		return divMsg(120, $username);
	}

	function showRealname($username) {
		$value = getRealname($username);
		return divMsg(120, $value);
	}

	$rv_course = array();
	function getCourse($csid) {
		global $sysSession, $MSG, $rv_course, $sysRoles;
		// 取得課程名稱
		if (!isset($rv_course[$csid])) {
			list($caption, $eb, $ee, $sb, $se, $n_limit, $a_limit) = dbGetStSr('WM_term_course', '`caption`, `en_begin`, `en_end`, `st_begin`, `st_end`, `n_limit`, `a_limit`', "`course_id`={$csid}", ADODB_FETCH_NUM);
			$lang = getCaption($caption);
			if (empty($n_limit)) $n_limit = $MSG['msg_unlimit'][$sysSession->lang];
			if (empty($a_limit)) $a_limit = $MSG['msg_unlimit'][$sysSession->lang];
			list($n_cnt,$a_cnt) = dbGetStSr('WM_term_major',
											'sum(if(role & ' . $sysRoles['student'] . ', 1, 0)) , sum(if(role & ' . $sysRoles['auditor'] . ', 1, 0))',
											'`course_id`=' . intval($csid),
											ADODB_FETCH_NUM);
			$rv_course[$csid] = array($lang[$sysSession->lang], $n_limit, $a_limit, (int)$n_cnt, (int)$a_cnt, $eb, $ee, $sb, $se);
		}
		return $rv_course[$csid];
	}

	function showCourseName($csid) {
		$cs = getCourse($csid);
		return divMsg(120, $cs[0]);
	}

	function showNLimit($csid) {
		$cs = getCourse($csid);
		return $cs[3] . '/' . $cs[1];
	}

	function showALimit($csid) {
		$cs = getCourse($csid);
		return $cs[4] . '/' . $cs[2];
	}

?>
