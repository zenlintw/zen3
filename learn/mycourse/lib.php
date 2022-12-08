<?php
	/**
	 * 共用函數
	 *
	 * @since   2003/06/06
	 * @author  ShenTing Lin
	 * @version $Id: lib.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	// 設定每頁顯示幾筆資料
	$lines = 10;

	// 我的課程的系統功能編號
	$sys_func_id = array(
			10010101,   /* 我的教室 */
			10010102,   /* 我的辦公室 */
			10010103,   /* 全校課程 */
			10010104,   /* 我的最愛 */
			10010105,   /* 選課清單 */
			10010106,   /* 選課清單結果 */
		);

	/**
	 * 檢查我的課程的設定檔存不存在
	 * @param
	 * @return
	 **/
	function chkSetting() {
		global $sysSession;

		$path = MakeUserDir($sysSession->username);
		$filename = $path . '/my_course_setting.xml';
		// 檢查設定檔存不存在
		if (!file_exists($filename)) {
			$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' .
			           '<manifest>' .
			           '<favorite>false</favorite>' .
			           '<group_id>10000000</group_id>' .
			           '<page_no>1</page_no>' .
			           '</manifest>';
			// 寫回檔案中
			touch($filename);
			if ($fp = fopen($filename, 'w')) {
				@fwrite($fp, $xmlstr);
			}
			fclose($fp);
		}
	}

	/**
	 * 檢查選課清單存不存在
	 * @return string $filename : 選課清單存放的路徑
	 **/
	function setElevtive($isRes = false) {
		global $sysSession;
		$path = MakeUserDir($sysSession->username);
		$filename = $path . ($isRes ? '/my_course_elective_result.xml' : '/my_course_elective.xml');

		if (!file_exists($filename)) {
			$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>';
			$xmlstr .= '<manifest></manifest>';
			// 寫回檔案中
			touch($filename);
			if ($fp = fopen($filename, 'w')) {
				@fwrite($fp, $xmlstr);
			}
			fclose($fp);
		}
		return $filename;
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

		foreach($child as $value) {
			if ($value <= 0) continue;
			//echo $value . '<br />';
			if (!array_key_exists($value, $group)) {
				$result .= $without_course ? '' : '<course id="' . $value . '"></course>';
			} else {
				$lang = getCaption($group_name[$value]);

				$result .= '<courses id="'    . $value               . '">'             .
				           '<title default="' . strtolower($sysSession->lang)    . '">'             .
				           '<big5>'           . $lang['Big5']        . '</big5>'        .
				           '<gb2312>'         . $lang['GB2312']      . '</gb2312>'      .
				           '<en>'             . $lang['en']          . '</en>'          .
				           '<euc-jp>'         . $lang['EUC-JP']      . '</euc-jp>'      .
				           '<user-define>'    . $lang['user_define'] . '</user-define>' .
				           '</title>'         .
				           Group2XML($group, $group_name, $value) .
				           '</courses>';
			}
		}
		return $result;
	}


	/**
	 * 取得所有群組的 XML 樹狀結構
	 *
	 * @return  xml             群組樹狀XML
	 */
	function getAllGroupsXml()
	{
	    global $sysSession;
	
		$group = array();
		$group_name = array();
		// 從資料庫中取得資料
		$RS = dbGetStMr('`WM_term_group`', '*', '1 order by `parent`, `permute`', ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$group[$RS->fields['parent']][$RS->fields['permute']] = $RS->fields['child'];
				$RS->MoveNext();
			}
		}

		$keys = array_keys($group);
		$RS = dbGetStMr('WM_term_course', 'course_id, caption', 'course_id in (' . implode(',', $keys) . ') AND status<9', ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$group_name[$RS->fields['course_id']] = $RS->fields['caption'];
				$RS->MoveNext();
			}
		}

		// 建立 xml 檔案
		$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		// $xmlstr .= '<manifest>';
		$xmlstr .= '<manifest id="' . $sysSession->cur_func . '">' .
				   Group2XML($group, $group_name, 10000000, true) .
				   '</manifest>';
		return $xmlstr;
	}


	/**
	 * 取得【課程教室】或【課程辦公室】的課程群組
	 *
	 * @param   bool    $isTA   是否取【課程辦公室】？否則取【課程教室】
	 * @return  xml             群組樹狀XML
	 */
	function getSpecificGroupsXml($isTA=false)
	{
	    global $sysSession, $sysRoles;

		$group = array();
		$group_name = array();
		// 從資料庫中取得資料
		$role = $isTA ? ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) :
						($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'] | $sysRoles['student'] | $sysRoles['auditor']);
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

		return $xmlstr;
	}

	/**
	 * 取得課程群組
	 *     1. 取得設定的資料
	 *            xml
	 *            資料庫
	 *     2. 依照我的課程、我的教授課程與全校課程的不同回傳不同的資料
	 * @return
	 **/
	function getCourseGroup() {
		global $sysSession, $sysConn, $sys_func_id, $sysRoles;

		$filename = sysDocumentRoot . "/base/{$sysSession->school_id}/system/course_group.xml";
		$xmlstr = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '><manifest></manifest>';

		// 切到我的最愛去
		// echo $sysSession->cur_func;
		if ($sysSession->cur_func == $sys_func_id[3]) return getFavorite();

/* 翻遍所有程式，找不到會存 course_group.xml 檔的地方，到底什麼時候會生出 course_group.xml 這支XML讓這邊來讀？

		// 從檔案中取得課程群組的設定 (Begin)
		$infile = false;
		if (file_exists($filename)) {
			$xml    = file($filename);
			$tmpstr = implode('', $xml);
			$tmp    = trim($tmpstr);
			if (!empty($tmp)) {
				$xmlstr = $tmpstr;
				$infile = true;
			}
		}
		// 從檔案中取得課程群組的設定 (End)
*/

		// 篩選必要的資料 (Begin)
		if (!in_array($sysSession->cur_func, $sys_func_id)) {
			$sysSession->cur_func = $sys_func_id[0];
			dbSet('WM_session', "cur_func='{$sysSession->cur_func}'", "idx='{$_COOKIE['idx']}'");
		}

		switch ($sysSession->cur_func) {
			case $sys_func_id[0] :    /* 我的教室 */
			    return getSpecificGroupsXml();
				break;

			case $sys_func_id[1] :    /* 我的辦公室 */
			    return getSpecificGroupsXml(true);
				break;

			case $sys_func_id[2] :    /* 全校課程 */
				/* 全列，所以不需要篩選 */
			    return getAllGroupsXml();
				break;

			default :
				$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n" .
						   '<manifest></manifest>';
		}
		// 篩選必要的資料 (End)

		return $xmlstr;
	}

	/**
	 * 取得訊息中心的目錄設定值
	 * @return string 訊息中心的 XML 設定值
	 **/
	function getFavorite() {
		global $sysSession, $sysConn;
		$content = '';

		$path = MakeUserDir($sysSession->username);
		$filename = $path . '/my_course_favorite.xml';

		if (!file_exists($filename)) {
			$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' .
					   '<manifest>' .
					   '<setting></setting>' .
					   '</manifest>';
			// 寫回檔案中
			touch($filename);
			if ($fp = fopen($filename, 'w')) {
				@fwrite($fp, $xmlstr);
			}
			fclose($fp);
		} else {
			$xml = file($filename);
			$xmlstr = implode('', $xml);
		}

		return $xmlstr;
	}

	/**
	 * 儲存設定值到 XML 中
	 * @param string $nodeName  : TAG 的名稱
	 * @param string $nodeValue : 要儲存的資料s
	 * @return boolean
	 *     true  : 成功
	 *     false : 失敗
	 **/
	function saveSetting($nodeName, $nodeValue) {
		global $sysSession;

		$path = MakeUserDir($sysSession->username);
		$filename = $path . '/my_course_setting.xml';
		if (!file_exists($filename)) return false;

		if (!$xmlvars = @domxml_open_file($filename)) {
			@unlink($filename);
			chkSetting();
			if (!$xmlvars = @domxml_open_file($filename)) {
				return false;
			}
		}

		$xpath = '/manifest/' . $nodeName;
		$ctx   = xpath_new_context($xmlvars);
		$nodes = xpath_eval($ctx, $xpath);

		if (count($nodes->nodeset) > 0) {
			// 移除舊節點
			$node = $nodes->nodeset[0];
			$parent = $node->parent_node();
			$parent->remove_child($node);
		}

		$foo = xpath_eval($ctx, '/manifest');
		$node = $foo->nodeset[0];
		$new_node = $xmlvars->create_element($nodeName);
		$new_text = $xmlvars->create_text_node($nodeValue);
		$new_node->append_child($new_text);
		$node->append_child($new_node);

		$xmlvars->dump_file($filename);
		return true;
	}

	/**
	 * 取得設定值中的設定
	 * @param string $nodeName : 要取值的 TAG
	 * @return 該 TAG 的值
	 **/
	function getSetting($nodeName) {
		global $sysSession, $sysConn;
		$nodeValue = '';

		$path = MakeUserDir($sysSession->username);
		$filename = $path . '/my_course_setting.xml';
		if (!file_exists($filename)) return false;

		$xml = file($filename);
		$content = implode('', $xml);
		if (!$xmlvars = @domxml_open_mem($content)) {
			return false;
		}

		$xpath = '/manifest/' . $nodeName;
		$ctx   = xpath_new_context($xmlvars);
		$nodes = xpath_eval($ctx, $xpath);
		if (count($nodes->nodeset) > 0) {
			$node = $nodes->nodeset[0];
			if ($node->has_child_nodes()) {
				$child = $node->first_child();
				$nodeValue = $child->node_value();
				return $nodeValue;
			}
		}

		return false;
	}

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
			$wd = is_numeric($width) ? intval($width) . 'px' : $width;
			return $without_title ? ('<div style="width: ' . $wd . 'px; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $wd . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
		}
	}

	if (!function_exists('getReviewSerial')) {
		/**
		 * 取得這門課設定的審核設定
		 * @param int id : 課程代號
		 * @return int 審核序號
		 **/
		function getReviewSerial($id) {
		    return dbGetOne('WM_review_sysidx', 'flow_serial', "discren_id = {$id}");
		}
	}


	if (!function_exists('getReviewRuleList')) {
		/**
		 * 取得目前系統設定的審核規則
		 * @param int id : 課程代號
		 * @return array 審核規則列表
		 **/
		function getReviewRuleList($id) {
			global $sysConn, $sysSession;
			$syscont = array();
			$RS = dbGetStMr('WM_review_syscont', '*', 'order by permute ASC', ADODB_FETCH_ASSOC);
			if ($RS) {
				if ($RS->RecordCount() != 0) {
					while ($RS1 = $RS->FetchRow())
					{
						$tlt_lang = unserialize($RS1['title']);
						$syscont[$RS1['flow_serial']] = $tlt_lang[$sysSession->lang];
					}
				}
			}
			return $syscont;
		}
	}
?>
