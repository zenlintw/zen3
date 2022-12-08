<?php
	/**
	 * 儲存課程群組
	 *
	 * 建立日期：2002/12/11
	 * @author  ShenTing Lin
	 * @version $Id: class_group_save.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/class_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');

	$sysSession->cur_func='2400100100';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}

	// PS：如何減少對 DB 的存取
	/**
	 * 公用變數
	 **/
	$dbGroup = array();
	
	/**
	 * 建立討論板
	 **/
	function addBoards($class_id, $bname) {
		global $sysConn;
		$RS = dbGetStSr('WM_bbs_boards', 'count(*) as cnt', '1', ADODB_FETCH_ASSOC);
		if ($RS['cnt'] == 0) {
			$RS = dbNew('WM_bbs_boards', 'board_id', '1000000000');
		}
		$boardName = addslashes(serialize($bname));
		$board_id = 0;
		// 建立討論板
		$RS = dbNew('WM_bbs_boards', 'bname, owner_id', "'{$boardName}', {$class_id}");

		if ($RS) {
			$board_id = $sysConn->Insert_ID();

			// 加入 WM_term_subject
			dbNew('WM_term_subject','course_id,board_id',"$class_id, $board_id");
		}

		return $board_id;
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
						$child_value = trim($child->node_value());
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

	function parseClassGroup($node) {
		global $sysConn, $dbGroup,$sysSession,$MSG, $_SERVER;
		// 檢查傳進來的參數是不是規定的物件
		if (!is_object($node) || !$node->has_child_nodes())
			return '';

		// 更新 WM_class_main (Begin)
		if ($node->node_name() == 'manifest') {
			$nodeID = 1000000;
		} else {
			$nodeID  = $node->get_attribute('id');
			$lang    = buildCaption($node);   // 取出語系
			$caption = addslashes(serialize($lang));

			$nodes   = $node->child_nodes();

            $cnt     = count($nodes);
            // for begin
            for ($i = 0; $i < $cnt; $i++) {

            	// if begin
                if (($nodes[$i]->node_type() == 1) && ($nodes[$i]->node_name() != 'title')) {

					// switch begin
                    switch ($nodes[$i]->node_name()){
                        case 'dep_id':
                                if ($nodes[$i]->has_child_nodes()){
                                    $child = $nodes[$i]->first_child();
                		            $dep_id_value = $child->node_value();
                                }else{
                                    $dep_id_value = '';
                                }
                                break;
                        case 'director':
                                if ($nodes[$i]->has_child_nodes()){
                                    $child = $nodes[$i]->first_child();
                		            $director_value = $child->node_value();
                                }else{
                                    $director_value = '';
                                }
                                break;
                        case 'people_limit':
                                if ($nodes[$i]->has_child_nodes()){
                                    $child = $nodes[$i]->first_child();
                		            $people_limit_value = $child->node_value();
            		            }else{
            		                $people_limit_value = 0;
            		            }
                                break;
                        case 'quota_limit':
                                if ($nodes[$i]->has_child_nodes()){
                                    $child = $nodes[$i]->first_child();
                		            $quota_limit_value = $child->node_value();
                                }else{
                                    $quota_limit_value = 102400;
                                }
                                break;
                    }
                    // switch end

                }
                // if end

                if ( ($dep_id_value == '') && ($director_value == '') && ($people_limit_value == '') && ($people_limit_value == '')){
                	$people_limit_value = 0;
                	$quota_limit_value = 102400;
	            }
			}
			// for end

			/**
			 * 新增或更新 WM_class_main 中群組的資料
			 *     新增：空的、不存在的 ID 或已有 ID 但 ID 重複
			 *     更新：已有 ID 且 ID 不重複
			 **/
		    if (empty($nodeID)) {
				// 新增
				dbNew('WM_class_main', 'caption,dep_id,director,people_limit,quota_limit', "'$caption','$dep_id_value','$director_value',$people_limit_value,$quota_limit_value");
				$nodeID = $sysConn->Insert_ID();
				
								
				/* 因 mysql5.7 重啟後會將auto_increment 變成1 加入防呆*/
				if($nodeID < 1000001){
					$nodeID_auto = $nodeID + 1000000;
					dbSet('WM_class_main',"class_id = '{$nodeID_auto}'","class_id = {$nodeID}");		
					$sysConn->Execute('ALTER TABLE WM_class_main AUTO_INCREMENT ='.($nodeID_auto+1));
					$nodeID = $nodeID_auto;
				}
				/* 因 mysql5.7 重啟後會將auto_increment 變成1 加入防呆*/
				
				wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], '新增WM_class_main中群組資料 class_id = ' . $nodeID);

    			// 將班級的目錄儲存到資料庫
    			$RS = dbSet('WM_class_main', "path=''", "class_id={$nodeID}");

    			// 建立班級討論板
    			$bname['Big5']        = stripslashes($MSG['discuss']['Big5']);
    			$bname['en']          = stripslashes($MSG['discuss']['en']);
    			$bname['EUC-JP']      = stripslashes($MSG['discuss']['EUC-JP']);
    			$bname['user_define'] = stripslashes($MSG['discuss']['user_define']);
    			$bname['GB2312']      = stripslashes($MSG['discuss']['GB2312']);

    			$board_id1            = addBoards($nodeID, $bname);

    			// 建立班級公告板
    			$bname['Big5']        = stripslashes($MSG['bulletin']['Big5']);
    			$bname['en']          = stripslashes($MSG['bulletin']['en']);
    			$bname['EUC-JP']      = stripslashes($MSG['bulletin']['EUC-JP']);
    			$bname['user_define'] = stripslashes($MSG['bulletin']['user_define']);
    			$bname['GB2312']      = stripslashes($MSG['bulletin']['GB2312']);

    			$board_id2            = addBoards($nodeID, $bname);

    			if (!$board_id2) $board_id2 = 'NULL';

                // 儲存討論板的 board_id
			    dbSet('WM_class_main', "discuss={$board_id1}, bulletin={$board_id2}", "class_id={$nodeID}");
			} else {
				// 更新
				$update_sqls = 'update WM_class_main ' .
							   " set  caption='" . $caption . "'," .
							   "dep_id='$dep_id_value'," .
							   "director='$director_value'," .
							   "people_limit=$people_limit_value," .
							   "quota_limit=$quota_limit_value" .
							   " where class_id={$nodeID}";

				$sysConn->Execute($update_sqls);

				wmSysLog('2400100200', $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], '更新WM_class_main中群組資料 class_id = ' . $nodeID);
			}
			$dbGroup[$nodeID] = true;
			$node->set_attribute('id', $nodeID);

		}
		// 更新 WM_class_main (End)

// 2005-2-22 begin

			// 更新 WM_class_group (Begin)
			$order = 0;   // 群組中，子群組或課程中的順序
			$childs = $node->child_nodes();
			$cnt = count($childs);
			for ($i = 0; $i < $cnt; $i++) {
				$child = $childs[$i];
				if ($child->node_type() != 1) continue;
				if (($child->node_name() == 'classes') || ($child->node_name() == 'class')) {
					if ($child->node_name() == 'classes') {
						$childID = parseClassGroup($child);
					} else {
						$childID = $child->get_attribute('id');
					}
					if (empty($childID)) continue;
					dbNew('WM_class_group', '`parent`, `child`, `permute`', "{$nodeID}, {$childID}, {$order}");

					$order++;
				}
			}
			wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], '新增WM_class_group');
			//echo $cnt . '<br />';
			if ($order == 0) {
				dbNew('WM_class_group', '`parent`, `child`, `permute`', "{$nodeID}, 0, 0");
			}
			// 更新 WM_class_group (End)

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
		$RS = dbGetStMr('WM_class_group', 'distinct parent', '1', ADODB_FETCH_ASSOC);
		while (!$RS->EOF) {
			$dbGroup[$RS->fields['parent']] = false;
			$RS->MoveNext();
		}

		// 清除不需要的 Tag
		$nodes = $xmlDoc->get_elements_by_tagname('ticket');
		for ($i = count($nodes) - 1; $i >= 0; $i--) {
			$pnode = $nodes[$i]->parent_node();
			$pnode->remove_child($nodes[$i]);
		}

		// 清除 WM_class_group
    	dbDel('WM_class_group', 1);

		parseClassGroup($xmlDoc->document_element());

		// 清除 WM_class_main 沒有用的 group
		reset($dbGroup);

		header("Content-type: text/xml");
		echo '<manifest><result>0</result></manifest>';
	} else {
		header("Content-type: text/xml");
		echo '<manifest><result>1</result></manifest>';
	}
?>
