<?php
	/**
	 * 儲存課程群組
	 *
	 * 建立日期：2002/12/11
	 * @author  ShenTing Lin
	 * @version $Id: content_group_save.php,v 1.1 2010/02/24 02:38:16 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');

	$sysSession->cur_func='2400100100';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}

	/**
	 * 檢查學校的系統資料夾有沒有建立，若沒有就自動建立
	 **/
	function checkSchSysDir() {
		global $sysSession;
		$dir = sysDocumentRoot . "/base/{$sysSession->school_id}/system";
		if (!@is_dir($dir)) @mkdir($dir, 0755);

		$dir .= '/default';
		if (!@is_dir($dir)) @mkdir($dir, 0755);
	}

	/**
	 * 備份原來的課程群組
	 *     保留十次的備份，編號越小的越新
	 **/
	function backupFile($fname) {
		@unlink("{$fname}.bk9");
		for ($i = 8; $i >= 0; $i--) {
			@rename("{$fname}.bk{$i}", "{$fname}.bk" . ($i + 1));
		}
		@rename($fname, "{$fname}.bk0");
	}

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
						case 'big5'       : $lang['Big5']        = Filter_Spec_char(stripslashes($child_value)); break;
						case 'gb2312'     : $lang['GB2312']      = Filter_Spec_char(stripslashes($child_value)); break;
						case 'en'         : $lang['en']          = Filter_Spec_char(stripslashes($child_value)); break;
						case 'euc-jp'     : $lang['EUC-JP']      = Filter_Spec_char(stripslashes($child_value)); break;
						case 'user-define': $lang['user_define'] = Filter_Spec_char(stripslashes($child_value)); break;
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

	function parseContentGroup($node) {
		global $sysConn, $sysSession, $_SERVER;
		// 檢查傳進來的參數是不是規定的物件
		if (!is_object($node) || !$node->has_child_nodes())
			return '';

		// 更新 WM_content (Begin)
		if ($node->node_name() == 'manifest') {
			$nodeID = 100000;
		} else {
			$nodeID  = intval($node->get_attribute('id'));
			$lang    = buildCaption($node);   // 取出語系
			$caption = addslashes(serialize($lang));
			$nodes   = $node->child_nodes();
            $cnt     = count($nodes);

			/**
			 * 新增或更新 WM_content 中群組的資料
			 *     新增：空的、不存在的 ID 或已有 ID 但 ID 重複
			 *     更新：已有 ID 且 ID 不重複
			 **/
		    if (empty($nodeID)) {
				// 新增
				dbNew('WM_content', 'caption,kind,path', "'$caption','group', ''");
				$nodeID = $sysConn->Insert_ID();
				wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], '新增WM_content中群組資料 content_id = ' . $nodeID);

    			// 將班級的目錄儲存到資料庫
    			dbSet('WM_content', "path='/base/{$sysSession->school_id}/content/{$nodeID}'", "content_id={$nodeID}");
			} else {
				// 更新
				dbSet('WM_content', "caption='{$caption}'", "content_id={$nodeID}");
			}
			$node->set_attribute('id', $nodeID);

		}
		// 更新 WM_content (End)


			// 更新 content_group (Begin)
			$order = 0;   // 群組中，子群組或課程中的順序
			$childs = $node->child_nodes();
			$cnt = count($childs);
			for ($i = 0; $i < $cnt; $i++) {
				$child = $childs[$i];
				if ($child->node_type() != 1) continue;

				if (($child->node_name() == 'contents') || ($child->node_name() == 'content')) {
					if ($child->node_name() == 'contents') {
						$childID = intval(parseContentGroup($child));
					} else {
						$childID = intval($child->get_attribute('id'));
					}
					if (empty($childID)) continue;
					dbNew('WM_content_group', '`parent`, `child`, `permute`', "{$nodeID}, {$childID}, {$order}");

					$order++;
				}
			}
			wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], '新增WM_content_group');
			//echo $cnt . '<br />';
			if ($order == 0) {
				dbNew('WM_content_group', '`parent`, `child`, `permute`', "{$nodeID}, 0, 0");
			}
			// 更新 WM_content_group (End)

		return $nodeID;
	}

////////////////////////////////////////////////////////////////////////
	header("Content-type: text/xml");
	echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if ($xmlDoc = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			// 清除不需要的 Tag
			$nodes = $xmlDoc->get_elements_by_tagname('ticket');
			for ($i = count($nodes) - 1; $i >= 0; $i--) {
				$pnode = $nodes[$i]->parent_node();
				$pnode->remove_child($nodes[$i]);
			}

			// 清除 WM_content_group
    		dbDel('WM_content_group', 1);

			parseContentGroup($xmlDoc->document_element());

			// 備份檔案
			$filename = sysDocumentRoot . "/base/{$sysSession->school_id}/system/default/content_group.xml";
			checkSchSysDir();
			backupFile($filename);
			// 回存 xml 檔
			$xmlDoc->dump_file($filename, false, true);

			die('<manifest><result>0</result></manifest>');
		}
	}

	die('<manifest><result>1</result></manifest>');
?>
