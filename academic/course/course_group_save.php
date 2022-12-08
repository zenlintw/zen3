<?php
	/**
	 * 儲存課程群組
	 *
	 * 建立日期：2002/12/11
	 * @author  ShenTing Lin
	 * @version $Id: course_group_save.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func = '700300100';
	$sysSession->restore();

	if (!aclVerifyPermission(700300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// PS：如何減少對 DB 的存取
	/**
	 * 公用變數
	 **/
	$dbGroup = array();

	/**
	 * 取得課程群組的標題
	 * @parm $node Object 要取得標題的節點
	 * @return array 五種語系的陣列
	 **/
	function buildCaption($node) {
		// 檢查傳進來的參數是不是規定的物件
		if (!is_object($node) || !$node->has_child_nodes())
			return '';

		$lang = array('Big5'=>'', 'GB2312'=>'', 'en'=>'', 'EUC-JP'=>'', 'user_define'=>'');

		// 尋找 title 節點 (Begin)
		$nodes = $node->child_nodes();
		$cnt = count($nodes);
		for ($i = 0; $i < $cnt; $i++) {
			// 判斷是不是 title 節點 (Begin)
			if (($nodes[$i]->node_type() == 1) && ($nodes[$i]->node_name() == 'title')) {
			$childs = $nodes[$i]->child_nodes();
				$count = count($childs);

				// 取出各個語系的字串 (Begin)
				for ($j = 0; $j < $count; $j++) {
					if ($childs[$j]->node_type() != 1) continue;

					if ($childs[$j]->has_child_nodes()) {
						$child = $childs[$j]->first_child();
						$child_value = $child->node_value();
					} else {
						$child_value = '';
					}

					switch ($childs[$j]->node_name()) {
						case 'big5'  : $lang['Big5']   = stripslashes($child_value); break;
						case 'gb2312': $lang['GB2312'] = stripslashes($child_value); break;
						case 'en':     $lang['en']     = stripslashes($child_value); break;
						case 'euc-jp': $lang['EUC-JP'] = stripslashes($child_value); break;
						case 'user-define': $lang['user_define'] = stripslashes($child_value); break;
					}
				}   // End for ($j = 0; $j < $count; $j++)
				// 取出各個語系的字串 (End)
				break;
			}   // End if (($nodes[$i]->node_type() == 1) && ($nodes[$i]->node_name() == 'title'))
			// 判斷是不是 title 節點 (End)
		}   // End for ($i = 0; $i < $cnt; $i++)
		// 尋找 title 節點 (End)
		return $lang;
	}

	function parseCourseGroup($node) {
		global $sysConn, $dbGroup, $new_log_msg, $update_log_msg;
		// 檢查傳進來的參數是不是規定的物件
		if (!is_object($node) || !$node->has_child_nodes())
			return '';

		// 更新 WM_term_course (Begin)
		if ($node->node_name() == 'manifest') {
			$nodeID = 10000000;
		} else {
			$nodeID = '';
			$id = trim($node->get_attribute('id'));
			if (!empty($id))
				$nodeID = sysDecode($id);
			$lang = buildCaption($node);   // 取出語系
			$caption = addslashes(serialize($lang));

			/**
			 * 新增或更新 WM_term_course 中群組的資料
			 *     新增：空的、不存在的 ID 或已有 ID 但 ID 重複
			 *     更新：已有 ID 且 ID 不重複
			 **/
			if (empty($nodeID) || !isset($dbGroup[$nodeID]) || $dbGroup[$nodeID]) {
				// 新增
				dbNew('WM_term_course', '`caption`, `kind`, `status`', "'{$caption}', 'group', 1");
				$nodeID = $sysConn->Insert_ID();
				
				/* 因 mysql5.7 重啟後會將auto_increment 變成1 加入防呆*/
				if($nodeID < 10000001){
					$nodeID_auto = $nodeID + 10000000;
					dbSet('WM_term_course',"course_id = '{$nodeID_auto}'","course_id = {$nodeID}");		
					$sysConn->Execute('ALTER TABLE WM_term_course AUTO_INCREMENT ='.($nodeID_auto+1));
					$nodeID = $nodeID_auto;
				}
				/* 因 mysql5.7 重啟後會將auto_increment 變成1 加入防呆*/
				
				$new_log_msg .= $new_log_msg == '' ? $nodeID : (', ' . $nodeID);
			} else {
				// 更新
				dbSet('WM_term_course', "caption='{$caption}'", "course_id={$nodeID}");
				if ($sysConn->Affected_Rows())
					$update_log_msg .= $update_log_msg == '' ? $nodeID : (', ' . $nodeID);
			}
			$dbGroup[$nodeID] = true;
			$node->set_attribute('id', $nodeID);
		}
		// 更新 WM_term_course (End)

		// 更新 WM_term_group (Begin)
		$order = 0;   // 群組中，子群組或課程中的順序
		$childs = $node->child_nodes();
		$cnt = count($childs);
		for ($i = 0; $i < $cnt; $i++) {
			$child = $childs[$i];
			if ($child->node_type() != 1) continue;
			if (($child->node_name() == 'courses') || ($child->node_name() == 'course')) {
				if ($child->node_name() == 'courses') {
					$childID = parseCourseGroup($child);
				} else {
					$id = trim($child->get_attribute('id'));
					$childID = (empty($id)) ? '' : sysDecode($id);
				}
				if (empty($childID)) continue;
				dbNew('WM_term_group', '`parent`, `child`, `permute`', "{$nodeID}, {$childID}, {$order}");
				$order++;
			}
		}

		//echo $cnt . '<br />';
		if ($order == 0)
			dbNew('WM_term_group', '`parent`, `child`, `permute`', "{$nodeID}, 0, 0");
		// 更新 WM_term_group (End)

		return $nodeID;
	}
////////////////////////////////////////////////////////////////////////
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$xmlDoc = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest><result>1</result></manifest>';
			exit;
		}

		// 取出目前所有課程群組的 ID
		$RS = dbGetStMr('WM_term_group', 'distinct parent', '1', ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$dbGroup[$RS->fields['parent']] = false;
				$RS->MoveNext();
			}
		}
		$RS = dbGetStMr('WM_term_course', '`course_id`', '`kind`="group"', ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$dbGroup[$RS->fields['course_id']] = false;
				$RS->MoveNext();
			}
		}

		// 如果只是處理課程群組，則保留原始群組 begin
	    $RS = dbGetStMr('WM_term_group as G,WM_term_course as C',
						'G.parent,G.child',
						'G.child=C.course_id and C.kind="course" order by G.parent,G.permute,G.child',
						ADODB_FETCH_ASSOC);
	    $origin_groups = array();
		if ($RS)
		    while ($fields = $RS->FetchRow())
			    $origin_groups[$fields['parent']][] = $fields['child'];
        // 如果只是處理課程群組，則保留原始群組 end

		// 清除不需要的 Tag
		$nodes = $xmlDoc->get_elements_by_tagname('ticket');
		for ($i = count($nodes) - 1; $i >= 0; $i--) {
			$pnode = $nodes[$i]->parent_node();
			$pnode->remove_child($nodes[$i]);
		}

		// 清除 WM_term_group
		dbDel('WM_term_group', 1);
		
		$new_log_msg = '';
		$update_log_msg = '';
		parseCourseGroup($xmlDoc->document_element());
		if ($new_log_msg != '')
			wmSysLog('0700300100', $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], 'new course group: '. $new_log_msg);
		if ($update_log_msg != '')
			wmSysLog('0700300200', $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], 'update course group: '. $update_log_msg);
		// 清除 WM_term_course 沒有用的 group
		reset($dbGroup);
		$del_log_msg = '';
		if (is_array($dbGroup)) {
			foreach ($dbGroup as $key => $val) {
				if ($val) {
				    // 把課程補回來
				    if (is_array($origin_groups[$key]))
				    {
				        $order = 10000;
				        foreach ($origin_groups[$key] as $child)
				        {
				            dbNew('WM_term_group', '`parent`, `child`, `permute`', "{$key}, {$child}, {$order}");
							$order++;
						}
					}
				} else {
					dbDel('WM_term_course', "course_id={$key}");
					if ($key != '10000000') $del_log_msg .= $del_log_msg == '' ? $key : (', ' . $key);
				}
			}
		}
		if ($del_log_msg != '')
			wmSysLog('0700300300', $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], 'delete course group: '. $del_log_msg);
		
		header("Content-type: text/xml");
		$result = "<manifest><ticket>{$ticket}</ticket><result>0</result></manifest>";
		echo $result;
	} else {
		header("Content-type: text/xml");
		$result = "<manifest><ticket>{$ticket}</ticket><result>1</result></manifest>";
		echo $result;
	}
?>
