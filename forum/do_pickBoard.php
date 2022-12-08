<?php
	/**
	 * 回傳篩選後的課程群組( 取自 /learn/mycourse/do_function.php 以及 lib.php )
	 *
	 * @since   2004/09/09
	 * @author  KuoYang Tsao
	 * @version $Id: do_pickBoard.php,v 1.1 2010/02/24 02:38:59 saly Exp $
	 * @copyright 2004 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lang/forum.php');

	$sysSession->cur_func = '700300400';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

//---------------------------------------------
// 函式開始
//---------------------------------------------

	function escape_xml_content($str)
	{
		return htmlspecialchars(mb_convert_encoding(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]+/', ' ', trim(stripslashes($str))), 'UTF-8', 'UTF-8'));
	}

	/**
	 * Group2XML()
	 * @param  Array  $group      : 課程群組的陣列
	 * @param  Array  $group_name : 課程群組的名稱
	 * @param  string $group_id   : 群組的編號
	 * @return string $result     : xml 格式的群組資料
	 **/
	function Group2XML($group, $group_name, $group_id, $without_course=false) {
		global $sysSession;
		$result = '';

		if (!is_array($group) || !is_array($group[$group_id])) return $result;
		$child = $group[$group_id];
		ksort($child);
		reset($child);

		$locale = str_replace('_', '-', strtolower($sysSession->lang));
		foreach($child as $value) {
			if ($value <= 0) continue;
			//echo $value . '<br />';
			if (!array_key_exists($value, $group)) {
				$result .= $without_course ? '' : '<course id="' . $value . '"></course>';
			} else {
				$lang = getCaption($group_name[$value]);

				$result .= '<courses id="' . $value . '">';
				$result .= '<title default="' . $sysSession->lang . '">';
				$result .= "<{$locale}>" . $lang[$sysSession->lang] . "</{$locale}>"; // 只顯示不編輯，那就秀目前語系即可，不必全部秀
				$result .= '</title>';
				$result .= Group2XML($group, $group_name, $value, $without_course);
				$result .= '</courses>';
			}
		}
		return $result;
	}

	/**
	 * 取得課程群組
	 *     1. 取得設定的資料
	 *            xml
	 *            資料庫
	 *     2. 依照我的教授課程回傳資料
	 * @return
	 **/
	function getCourseGroup() {
		global $sysSession, $sysConn, $sysRoles, $MSG;

		$xmlstr = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '><manifest></manifest>';

		$group = array();
		$group_name = array();
		// 從資料庫中取得資料
		$role = ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);
		// 最佳化搜尋任教課程及所屬群組之樹狀結構 begin
		// 取得目前 user 所任教的課程
		$latters = dbGetCol('WM_term_major AS M, WM_term_course AS C, WM_term_group AS G',
							'DISTINCT G.parent',
							"M.username = '{$sysSession->username}'
							AND M.role & {$role}
							AND M.course_id = C.course_id
							AND C.status <9
							AND M.course_id = G.child
							order by G.parent, G.permute");
		$keys = $latters;
		foreach ($latters as $g) $group[$g] = array(); // 最末端的群組要設為陣列 (陣列值應該是任教課程，但因沒用到，所以不必列)

		// 從課程往上搜尋群組，直到 parent 群組是 10000000 為止
		while (count($latters) && (
				$rs = dbGetStMr('WM_term_group',
								'parent,child',
								'child in (' . implode(',', $latters) . ') order by `parent`, `permute`',
								ADODB_FETCH_NUM))
			  )
		{
			$latters = array();
			while (list($parent,$child) = $rs->FetchRow())
			{
				$group[$parent][] = $child;
				if (!in_array($parent, $keys))
				{
					$latters[] = $parent;
					$keys[] = $parent;
				}
			}
			$latters = array_unique($latters);
		}
		// 最佳化搜尋任教課程及所屬群組之樹狀結構 end

		$keys = array_unique($keys);
		$RS = dbGetStMr('WM_term_course', 'course_id, caption', 'course_id in (' . implode(',', $keys) . ') AND status<9', ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$group_name[$RS->fields['course_id']] = $RS->fields['caption'];
				$RS->MoveNext();
			}
		}

		// 建立 xml 檔案
		$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		$xmlstr .= '<manifest>';
		$xmlstr .= Group2XML($group, $group_name, 10000000, true);
		$xmlstr .= '</manifest>';

		// 未分組課程
		$locale = str_replace('_', '-', strtolower($sysSession->lang));
		$undiv = '<courses id="99999999">' .
				 '   <title default="' . $sysSession->lang . '">' .
				 // 只顯示不編輯，那就秀目前語系即可，不必全部秀
				 "      <{$locale}>" . escape_xml_content($MSG['un_div_course'][$sysSession->lang]) . "</{$locale}>" .
				 '   </title>' .
				 '</courses>';

		return str_replace('</manifest>', $undiv . '</manifest>' , $xmlstr);
	}

	/**
	 * 取得群組中課程
	 *     1. 取得設定的資料
	 *            xml
	 *            資料庫
	 *     2. 依照我的教授課程回傳資料
	 * @return
	 **/
	function getCourse($group_id) {
		global $sysSession, $sysRoles, $sysConn;

		if ($group_id < 10000000 || $group_id > 99999999) return '<manifest />';

		$rtnAry  = array();
		if ($group_id == 99999999)  // 未分組課程
		{
			$courses = $sysConn->GetAssoc('select B.course_id, B.caption ' .
										  'from WM_term_course as B ' .
										  'left join WM_term_group as G ' .
										  'on B.course_id=G.child ' .
										  'where G.child is NULL and B.kind="course" and B.status != 9');
		}
		else
		{
			$courses = getAllCourseInGroup($group_id);  // 某群組下所有課程
		}
		$rs      = dbGetCourses('C.course_id', $sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']); // 某人教授的課程
		if ($rs) while ($row = $rs->FetchRow())
		{
			if ($courses[$row['course_id']]) $rtnAry[$row['course_id']] = $courses[$row['course_id']];
		}


		$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n" .
				   '<manifest><group_id>' . $group_id . '</group_id>';
		$locale = str_replace('_', '-', strtolower($sysSession->lang));
		foreach($rtnAry as $cid => $caption)
		{
			$lang = getCaption($caption);
			$xmlstr .= '<courses id="'    . $cid . '">' .
					   '<title default="' . $sysSession->lang        . '">'.
					   // 只顯示不編輯，那就秀目前語系即可，不必全部秀
					   "        <{$locale}>" . $lang[$sysSession->lang] . "</{$locale}>" .
					   '</title></courses>';
		}

		return $xmlstr . '</manifest>';
	}

	/**
	 * 取得課程所有討論版
	 *     1. 取得設定的資料
	 *            xml
	 *            資料庫
	 *     2. 依照我的教授課程回傳資料
	 * @return
	 **/
	function getCourseBoard($course_id) {
		global $sysSession, $sysConn, $Sqls;

		$tab     = 'WM_term_subject left join WM_bbs_boards on WM_bbs_boards.board_id = WM_term_subject.board_id';
		$fields  = 'distinct `WM_term_subject`.`board_id`, `permute`, `bname`';
		$where   = "`course_id`={$course_id} order by `permute`";
		$RS      = dbGetStMr($tab, $fields, $where, ADODB_FETCH_ASSOC);
		$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		$xmlstr .= '<manifest>';
		// $xmlstr .= '<sqls>'.$sqls.'</sqls>';
		$locale = str_replace('_', '-', strtolower($sysSession->lang));
		while (!$RS->EOF) {
				$lang = getCaption($RS->fields['bname']);
				$xmlstr .= '<boards id="'     . $RS->fields['board_id'] . '">' .
						   '<title default="' . $sysSession->lang       . '">' .
						   // 只顯示不編輯，那就秀目前語系即可，不必全部秀
						   "<{$locale}>" . $lang[$sysSession->lang] . "</{$locale}>" .
						   '</title>' .
						   '</boards>';
			$RS->MoveNext();
		}
		$xmlstr .= '</manifest>';
		return $xmlstr;
	}
//---------------------------------------------
// 函式結束
//---------------------------------------------


//---------------------------------------------
// 主程式開始
//---------------------------------------------

	//echo $GLOBALS['HTTP_RAW_POST_DATA'];
	//die();
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
			exit;
		}

		// 檢查 Ticket
		$ticket = md5(sysTicketSeed . $sysSession->username . 'pickboard' . $sysSession->board_id);
		if (getNodeValue($dom, 'ticket') != $ticket) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n";
			echo '<manifest>Access Fail.</manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], '拒絕存取!');
			exit;
		}

		$action = getNodeValue($dom, 'action');
		$result = '';
		switch ($action) {
			case 'list_group' :   // 回傳篩選過的課程群組
				$result = getCourseGroup();
				break;

			case 'group'      :   // 回傳課程群組中的所有課程
				$group_id = getNodeValue($dom, 'group_id');
                if (!empty($group_id) && !is_numeric($group_id)) {
                    header('HTTP/1.1 403 Forbidden');
                    exit;
                }
				$result   = getCourse($group_id);
				break;

			case 'list_board' :   // 回傳課程所有討論版
				$course_id = getNodeValue($dom, 'course_id');
                // XSS防護
                if (!empty($course_id) && !is_numeric($course_id)) {
                    header('HTTP/1.1 403 Forbidden');
                    exit;
                }
				$result    = getCourseBoard($course_id);
				break;
		}

		if (!empty($result)) {
			header("Content-type: text/xml");
			$result = str_replace('<manifest>', "<manifest><ticket>{$ticket}</ticket>", $result);
			echo $result;
		} else {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n";
			echo '<manifest></manifest>';
		}
	}

?>
