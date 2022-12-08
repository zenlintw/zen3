<?php
	/**
     * 檔案說明 
     *	實體課程刪除
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      Edi Chen <edi@sun.net.tw>
     * @copyright   2000-2005 SunNet Tech. INC.
     * @version     CVS: $Id: course_real_remove.php,v 1.1 2010/02/24 02:38:20 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2008-04-09
     */
// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lang/course_manage.php');
	require_once(sysDocumentRoot . '/lib/common.php');
// }}} 函式庫引用 end

// {{{ 變數宣告 begin
	$sWord = isSet($_POST['sWord']) ? $_POST['sWord'] : (isSet($_GET['sWord']) ? base64_decode($_GET['sWord']) : $MSG['query_string'][$sysSession->lang]);
	$where = array('`status` = 9');
	if ((isSet($_POST['sWord']) || isSet($_GET['sWord'])) && trim($sWord) != '' && $sWord != $MSG['query_string'][$sysSession->lang])
	{
		$where[] = 'caption like "%' . escape_LIKE_query_str($sWord) . '%"';
		$qstr    = '&sWord=' . base64_encode($sWord);
	}
// {{{ 變數宣告 end

// }}} 函數宣告 begin
	/**
	 * 顯示課程名稱
	 * @param string $val 課程名稱
	 */
	function showCaption($val)
	{
		global $sysSession;
		$val = getCaption($val);
		return $val[$sysSession->lang];
	}
	
	/**
	 * 顯示開始與結束日期
	 * @param string  $val1 開始日期
	 * @param string  $val2 結束日期
	 **/
	function showDatetime($val1, $val2) {
		global $sysSession, $MSG;

		return $MSG['from2'][$sysSession->lang] . (empty($val1) || $val1 == '0000-00-00' ? $MSG['now'][$sysSession->lang]     : date('Y-m-d', $time1)) . '<br />' .
		       $MSG['to2'][$sysSession->lang]   . (empty($val2) || $val2 == '0000-00-00' ? $MSG['forever'][$sysSession->lang] : date('Y-m-d', $time2));

	}
	
	/**
	 * 顯示剩餘可用空間
	 * @param int $used used quota
	 * @param int $limit limit quota
	 */
	function showRemain($used, $limit)
	{
		if ($limit > 0)
		{
			$remain = $limit - $used;
			return $remain . 'KB(' . round($remain / $limit, 2) * 100 . '%)';
		}
		else
		{
			return '0KB(0%)';
		}
	}
	
	/**
	 * 顯示checkbox
	 * @param int $course_id 課程編號
	 */
	function showCheckBox($course_id)
	{
		showXHTML_input('checkbox', 'cids[]', sysEncode($course_id), '', 'onclick="chgCheckbox(); event.cancelBubble=true;"');
	}
	
// }}} 函數宣告 end

// {{{ 主程式 begin

	// 刪除實體課程 begin
	if (isSet($_POST['act']) && $_POST['act'] == 'DEL' && isSet($_POST['cids'])) 
	{
		$cids = array();
		foreach($_POST['cids'] as $v)	// decode並檢查是否是已被刪除課程(status=9)
		{
			$cids[] = sysDecode($v);
		}
		$cids = preg_split('/[^\d-]+/', implode(',', $cids), -1, PREG_SPLIT_NO_EMPTY);
		$cids = dbGetCol('WM_term_course', 'course_id', 'course_id in ('.implode(',', $cids).') and status=9');
		
		if (is_array($cids) && count($cids))
		{
			$str_course_ids = implode(',', $cids);
			$bids           = dbGetAssoc('WM_bbs_boards', 'board_id, owner_id', 'owner_id in (' . $str_course_ids . ')');
			$str_board_ids  = implode(',', array_keys($bids));
			
			// 記log所需資料
			$log_field      = 'function_id, username, log_time, department_id,instance,result_id, note, remote_address, user_agent, script_name';
			$address        = wmGetUserIp();				// 取IP函式，宣告在 "config/db_initialize.php"
			$headers        = apache_request_headers();
			$agent          = $headers['User-Agent'];      // User Agent
			$agentID        = getUserAgent($agent);        // User Agent ID
			$scname         = $_SERVER['PHP_SELF'];
				
			if (count($bids))
			{
				dbDel('WM_bbs_posts'     , 'board_id  in (' . $str_board_ids  . ')');
				dbDel('WM_bbs_collecting', 'board_id  in (' . $str_board_ids  . ')');
				dbDel('WM_bbs_boards'    , 'owner_id  in (' . $str_course_ids . ')');
				$note     = 'remove boards (course_id,board_id) in' . substr(vsprintf(vsprintf(str_repeat('(%s, %%s),', count($bids)), $bids), array_keys($bids)), 0, -1);
				$log_time = date('Y-m-d H:i:s');
				dbNew('WM_log_manager', $log_field, "'{$sysSession->cur_func}', '{$sysSession->username}', '{$log_time}', {$sysSession->school_id}, 0, 0, '{$note}', '{$address}', {$agentID}, '{$scname}'");
			}
			
			dbDel('WM_term_course'   , 'course_id in (' . $str_course_ids . ')');
			$result   = $sysConn->ErrorNo() === 0 ? $MSG['msg_delete_course_success'][$sysSession->lang] : $MSG['msg_delete_course_fail'][$sysSession->lang];
			$note     = 'remove course entity course_id in (' . $str_course_ids . ')';
			$log_time = date('Y-m-d H:i:s', time() +1);
			dbNew('WM_log_manager', $log_field, "'{$sysSession->cur_func}', '{$sysSession->username}', '{$log_time}', {$sysSession->school_id}, 0, 0, '{$note}', '{$address}', {$agentID}, '{$scname}'");
			
			// 刪除實體目錄
			chdir(sprintf('%s/base/%d/course/', sysDocumentRoot, $sysSession->school_id));
			exec('rm -rf ' . implode(' ', $cids));
		}
		else
		{
			$result   = $MSG['msg_delete_course_fail'][$sysSession->lang];
		}
		
		$onload = 'alert("'.$result.'")';
	}
	// 刪除實體課程 end
	
	$js = <<< EOF
	window.onload=function()
	{
		{$onload}
	};
	
	/**
	 * 刪除課程
	 */
	function delCourse()
	{
		var blnSelect = false;
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return;
		for (var i = 0; i < nodes.length; i++)                         // 判斷是否有選擇欲刪除課程
		{
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked) 
			{
				blnSelect = true;
				break;
			}
		}
		
		if (blnSelect)
		{
			if (confirm("{$MSG['msg_delete_course'][$sysSession->lang]}"))
			{
				document.getElementById('act').value = 'DEL';
				document.getElementById('mainFm').submit();
			}
		}
		else
		{
			alert("{$MSG['msg_sel_del'][$sysSession->lang]}");
		}
	}
	
	/**
	 * 用下拉選單跳頁數
	 */
	function chgPage() 
	{
		return '{$qstr}';
	}
	
	/**
	 * 切換全選或全消的 checkbox
	 **/
	function chgCheckbox() {
		var bol = true;
		var nodes = document.getElementsByTagName("input");
		var obj  = document.getElementById("ck");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked == false) 
			{
				bol = false;
				break;
			}
		}
		nowSel = bol;
		if (obj  != null) obj.checked = bol;
	}
	
	/**
	 * 全選/全消的checkbox
	 */
	var nowSel = false;
	function selfunc() {
		nowSel = !nowSel;
		var obj  = document.getElementById("ck");
		if (obj == null) return false;
		obj.checked = nowSel;
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			nodes[i].checked = nowSel;
		}
	}
EOF;
	showXHTML_head_B($MSG['title_manage'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		echo '<div align="center">';
		showXHTML_tabFrame_B(array(array($MSG['tabs_course_list'][$sysSession->lang])), 1, 'mainFm', '', 'action="course_real_remove.php" method="post" style="display: inline;"');
			// Chrome 必須給予id才能讓 chrome 取到
			showXHTML_input('hidden', 'act', '', '', 'id=\'act\'');
			
			$myTable = new table();
			$myTable->extra = 'width="760" border="0" cellspacing="1" cellpadding="3" id="" class="cssTable"';
			
			// 搜尋關鍵字
			$toolbar = new toolbar();
			$toolbar->add_caption($MSG['query_course'][$sysSession->lang]);
			$toolbar->add_input('text', 'sWord', htmlspecialchars(stripslashes($sWord)), '', 'id="sWord" size="20"  class="cssInput" onclick="this.value=\'\'"');
			$toolbar->add_input('submit', '', $MSG['btn_ok'][$sysSession->lang], '', 'class="cssBtn"');
			$myTable->add_toolbar($toolbar);
			
			$toolbar = new toolbar();
			$toolbar->add_caption('&nbsp;&nbsp;');
			$toolbar->add_input('button', 'btnDel', $MSG['btn_del_course'][$sysSession->lang], '', 'id="btnDel"  class="cssBtn" onclick="delCourse()"');
			$myTable->set_def_toolbar($toolbar);
			
			$myTable->set_page(true, 1, sysPostPerPage, 'chgPage();');
			
			$ck1 = new toolbar();
			$ck1->add_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');
			
			$myTable->add_field($ck1, $MSG['select_all_msg'][$sysSession->lang], ''           , '%course_id'   , 'showCheckBox'  , 'width="20" align="center"');
			$myTable->add_field($MSG['td_course_id'][$sysSession->lang]    , '', 'csid'       , '%course_id'   , ''              , 'align="center" nowrap="noWrap"');
			$myTable->add_field($MSG['td_course_name'][$sysSession->lang]  , '', 'caption'    , '%caption'   , 'showCaption'   , 'nowrap="noWrap"');
			$myTable->add_field($MSG['td_study'][$sysSession->lang]        , '', 'study_date' , '%st_begin %st_end', 'showDatetime'  , 'nowrap="noWrap"');
			$myTable->add_field($MSG['td_remain'][$sysSession->lang]       , '', ''           , '%quota_used %quota_limit'  , 'showRemain'     , 'align="right" nowrap="noWrap"');
			
			$myTable->set_sqls('WM_term_course', 'course_id, caption, st_begin, st_end, quota_used, quota_limit', implode(' and ', $where) . ' order by course_id');
			$myTable->show();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
// {{{ 主程式 end
?>
