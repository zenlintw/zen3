<?php
	/**
	 * 訊息中心共用的函式
	 *
	 * 建立日期：2003/04/24
	 * @author  ShenTing Lin
	 * @version $Id: lib.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	// 設定每頁顯示幾筆資料
	$lines = sysPostPerPage;

	$msgFuncID = array(
		'message'  => 2200000000,
		'notebook' => 2600000000
	);

	$xmlStrs = '';
	function getFolderXML($username='') {
		global $sysSession, $sysConn, $msgFuncID, $xmlStrs;
		$content = '';

		if (!empty($xmlStrs)) return $xmlStrs;
		$username = trim($username);
		if (empty($username)) $username = $sysSession->username;
		list($content) = dbGetStSr('WM_msg_folder', 'content', "username='{$username}'", ADODB_FETCH_NUM);
		if (!$content) {
			$xml = file(sysDocumentRoot . '/config/xml/msg_folder.xml');
			$content = implode('', $xml);
			dbNew('WM_msg_folder', 'username, content', "'{$sysSession->username}', '{$content}'");
		}
		$xmlStrs = $content;

		return $content;
	}

	/**
	 * 取得訊息中心的目錄設定值
	 * @return string 訊息中心的 XML 設定值
	 **/
	function getFolder() {
		global $sysSession, $sysConn, $msgFuncID;
		$content = '';

		$content = getFolderXML();
		if (!$xmlvars = domxml_open_mem($content)) {
			return $content;
		}

		$ctx = xpath_new_context($xmlvars);

		// 如果未啟用推播功能，則需要拿掉APP訊息中心
		if (!sysEnableAppServerPush) {
			$foo = xpath_eval($ctx, "//folder[@id='app_push_message']");
			if (count($foo->nodeset) > 0) {
				$node = $foo->nodeset[0];
				// 不顯示我的筆記本
				$parent = $node->parent_node();
				$parent->remove_child($node);
				$content = $xmlvars->dump_mem(true);
			}
		}

		$foo = xpath_eval($ctx, "//folder[@id='sys_notebook']");
		if (count($foo->nodeset) > 0) {
			$node = $foo->nodeset[0];
			if ($sysSession->cur_func == $msgFuncID['message']) {
				// 不顯示我的筆記本
				$parent = $node->parent_node();
				$parent->remove_child($node);
				$content = $xmlvars->dump_mem(true);
			} else {
				// 顯示我的筆記本
				$content  = '<manifest>';
				$fod = xpath_eval($ctx, "//setting");
				if (count($fod->nodeset) > 0) {
					$set = $fod->nodeset[0];
					$content .= $xmlvars->dump_node($fod->nodeset[0]);
				} else {
					$content .= '<setting id="notebook"></setting>';
				}
				$content .= $xmlvars->dump_node($node);
				$content .= '</manifest>';
			}
		}

		$xmlDcos = $content;
		return $content;
	}

	/**
	 * 儲存編修後的訊息中心的資料夾
	 * @param object $xmldoc : 整個要儲存的 XML 設定檔
	 * @return string 用 XML 包裝起來的訊息包含了成功或失敗的訊息
	 **/
	function saveFolder($xmldoc) {
		global $sysSession, $sysConn, $msgFuncID;
		$content = '';

		/* 清除不需要的資料 (Begin) */
			// 清除 ticket Node
		$nodes = $xmldoc->get_elements_by_tagname('ticket');
		$cnt = count($nodes);
		for ($i = 0; $i < $cnt; $i++) {
			$node = $nodes[$i]->parent_node();
			$node->remove_child($nodes[$i]);
		}
			// 清除 action Node
		$nodes = $xmldoc->get_elements_by_tagname('action');
		$cnt = count($nodes);
		for ($i = 0; $i < $cnt; $i++) {
			$node = $nodes[$i]->parent_node();
			$node->remove_child($nodes[$i]);
		}
		/* 清除不需要的資料 (End) */

		/* 更新資料夾 (Begin) */
		$sysID   = array('sys_notebook', 'sys_notebook_trash', 'sys_inbox', 'sys_sent_backup', 'sys_online_msg_backup', 'sys_trash');
		$newID   = array();
		$xmlData = null;

		// 取得原始資料 - 個人訊息中心的目錄設定
		$content = getFolderXML();
		$xmlvars = domxml_open_mem($content);

		// 取出筆記本的資料
		$nodeNB  = null;
		if ($sysSession->cur_func == $msgFuncID['notebook']) {
			// $content = $xmlvars->dump_mem(true);
			$xmlData = domxml_open_mem($content);
			$ctx     = xpath_new_context($xmldoc);
			$trash   = 'sys_notebook_trash';
		} else {
			$xmlData = $xmldoc;
			$ctx = xpath_new_context($xmlvars);
			$trash   = 'sys_trash';
		}
		$foo = xpath_eval($ctx, "//folder[@id='sys_notebook']");
		if (count($foo->nodeset) > 0) {
			$node   = $foo->nodeset[0];
			$nodeNB = $node->clone_node(true);
		}

		// 合併筆記本的資料
		//     訊息中心：舊 -> 新 (舊的取代新的)
		//       筆記本：新 -> 舊 (新的取代舊的)
		$ctx = xpath_new_context($xmlData);
		$foo = xpath_eval($ctx, "//folder[@id='sys_notebook']");
                if (is_array($foo->nodeset)) {
                    foreach ($foo->nodeset as $node) {
                            $pnode = $node->parent_node();
                            $pnode->remove_child($node);
                    }
                }
		$root = $xmlData->document_element();
		if ($nodeNB != null) $root->append_child($nodeNB);

		// 使用者新增的資料夾
		$nodes = $xmlData->get_elements_by_tagname('folder');
		$cnt = count($nodes);
		$attr = '';

		for ($i = 0; $i < $cnt; $i++) {
			$attr = $nodes[$i]->get_attribute('id');
			if (empty($attr) || in_array($attr, $newID)) {
				$attr = uniqid('USER_');
				$nodes[$i]->set_attribute('id', $attr);
			}
			/*
			// 保持回收筒在最後一個
			if ($attr == 'sys_notebook_trash') {
				$pnode = $nodes[$i]->parent_node();
				$pnode->remove_child($nodes[$i]);
				continue;
			}
			*/
			$newID[] = $attr;
		}

		// 檢查系統資料夾
		$xmlsysm = domxml_open_file(sysDocumentRoot . '/config/xml/msg_folder.xml');
		$ctx     = xpath_new_context($xmlsysm);

		foreach ($sysID as $val) {
			if (in_array($val, $newID)) continue;
			$nodes = xpath_eval($ctx, "//folder[@id='{$val}']");
			if (count($nodes->nodeset) > 0) {
				if ($val == 'sys_notebook_trash') {
					$nctx  = xpath_new_context($xmlData);
					$child = xpath_eval($nctx, "//folder[@id='sys_notebook']");
					if (count($child->nodeset) > 0) $root = $child->nodeset[0];
					else $root = $xmlData->document_element();
				} else {
					$root = $xmlData->document_element();
				}

				$node    = $nodes->nodeset[0];
				$nodeSys = $node->clone_node(true);
				$root->append_child($nodeSys);
				$newID[] = $val;
			}
		}

		// 被刪除的資料夾
		$childs = $xmlvars->get_elements_by_tagname('folder');
		$cnt = count($childs);
		for ($i = 0; $i < $cnt; $i++) {
			$attr = $childs[$i]->get_attribute('id');
			if (!empty($attr) && !in_array($attr, $newID)) {
				// 刪除訊息
				dbSet('WM_msg_message', "`folder_id`='{$trash}'", "`folder_id`='{$attr}' AND `receiver`='{$sysSession->username}'");
				// dbDel('WM_msg_message', "`folder_id`='{$attr}' AND `receiver`='{$sysSession->username}'");
			}
		}


		// 更新資料庫
		$content = $xmlData->dump_mem(true);
		$content = addcslashes($content, "\'");
		dbSet('WM_msg_folder', "content='{$content}'", "username='{$sysSession->username}'");

		// 回傳是否成功
		$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		if ($sysConn->Affected_Rows() > 0){
			$xmlstr .= '<manifest><result>1</result></manifest>';
		} else {
			$xmlstr .= '<manifest><result>0</result></manifest>';
		}
		return $xmlstr;
		/* 更新資料夾 (End) */
	}

	/**
	 * 儲存設定值到 XML 中
	 * @param
	 * @return
	 **/
	function saveSetting($nodeName, $nodeValue, $nodeID = '') {
		global $sysSession, $sysConn, $xmlStrs;

		$content = getFolderXML();
		if (!$xmlvars = domxml_open_mem($content)) {
			return '<manifest></manifest>';
		}

		$xpath  = (empty($nodeID)) ? '/manifest/setting' : "//folder[@id='{$nodeID}']/setting";
		$ctx = xpath_new_context($xmlvars);
		$node = xpath_eval($ctx, $xpath . '/' . $nodeName);
		if (count($node->nodeset) <= 0) {
			// 建立節點
			$foo = xpath_eval($ctx, $xpath);
			$node = $foo->nodeset[0];
			$new_node = $xmlvars->create_element($nodeName);
			$new_text = $xmlvars->create_text_node($nodeValue);
			$new_node->append_child($new_text);
			$node->append_child($new_node);
		} else {
			// 設定節點
			$foo = xpath_eval($ctx, $xpath . '/' . $nodeName);
			$node = $foo->nodeset[0];
			if ($node->has_child_nodes()) {
				$child = $node->first_child();
				$node->remove_child($child);
			}
			$new_text = $xmlvars->create_text_node($nodeValue);
			$node->append_child($new_text);
		}

		$content = $xmlvars->dump_mem(true);
		$xmlStrs = $content;
		$content = addcslashes($content, "\'");
		// saveFolder($xmlvars);
		dbSet('WM_msg_folder', "content='{$content}'", "username='{$sysSession->username}'");

		return '<manifest></manifest>';
	}

	/**
	 * 取得設定值中的設定
	 * @param
	 * @return
	 **/
	function getSetting($nodeName, $nodeID = '') {
		global $sysSession, $sysConn;

		$nodeValue = '';
		$content = getFolderXML();

		if (!$xmlvars = domxml_open_mem($content)) {
			return $nodeValue;
		}

		$xpath  = (empty($nodeID)) ? '/manifest/setting' : "//folder[@id='{$nodeID}']/setting";
		$ctx = xpath_new_context($xmlvars);
		$node = xpath_eval($ctx, $xpath . '/' . $nodeName);
		if (count($node->nodeset) > 0) {
			$foo = xpath_eval($ctx, $xpath . '/' . $nodeName);
			$node = $foo->nodeset[0];
			if ($node->has_child_nodes()) {
				$child = $node->first_child();
				$nodeValue = $child->node_value();
			}
		}

		return $nodeValue;
	}

	/**
	 * 取得設定值中的資料夾ID
	 * @param
	 * @return
	 **/
	function getFolderId() {
		$folder_id = getSetting('folder_id', '');
		if (empty($folder_id)) $folder_id = 'sys_inbox';

		return $folder_id;
	}

	/**
	 * 取得資料夾的名稱
	 * @param object $node : 資料夾那個 node
	 * @return string : 資料夾的名稱
	 **/
	function getFolderName($node) {
		global $sysSession;
		if (!is_object($node)) return '';
		$childs = $node->child_nodes();
		foreach ($childs as $node) {
			if (($node->node_type()) != XML_ELEMENT_NODE) continue;
			if (($node->node_name()) != 'title') continue;
			$val = getNodeValue($node, strtolower($sysSession->lang));
			if(trim($val) == "" || trim($val)== "undefined" || trim($val)== "--=[unnamed]=--"){
				$val = getNodeValue($node, strtolower(sysDefaultLang));
			}

			return $val;
		}
	}

	/**
	 * 從資料夾編號取得資料夾的名稱
	 * @param string $id : 資料夾的編號
	 * @param string $username : 帳號
	 * @return string : 資料夾的名稱
	 **/
	function getNameFromID($id='', $username='') {
		global $sysSession;

		$id = trim($id);
		if (empty($id)) return false;

		$username = trim($username);
		if (empty($username)) $username = $sysSession->username;

		$lang = strtolower($sysSession->lang);
		$query = '//folder[@id="' . $id . '"]/title/' . $lang . '/text()';

		$strs = getFolderXML();
		if (!$dom = domxml_open_mem($strs)) {
			return false;
		}
		$xpath = xpath_new_context($dom);
		$ary = xpath_eval($xpath, $query);
		$nodes = $ary->nodeset;
		if (count($nodes) > 0) {
			return $nodes[0]->node_value();
		} else {
			return '';
		}
	}

	/**
	 * 取出目前點選的資料夾的位置，例如：我的筆記本 > 新的資料夾 (2) > 新的資料夾 (1) > 新的資料夾 (3)
	 * @param string $fid : 資料夾的 ID
	 * @return array : 資料夾的陣列
	 **/
	function nowPos($fid, $includeId=false) {
		$ary = array();

		$content = getFolderXML();
		if (!$xmlvars = domxml_open_mem($content)) {
			return $ary;
		}

		$ctx = xpath_new_context($xmlvars);
		$folder = xpath_eval($ctx, "//folder[@id='{$fid}']");
		if (count($folder->nodeset) > 0) {
			$node = $folder->nodeset[0];
			do {
				if (($node->node_type() == XML_ELEMENT_NODE) && ($node->node_name() == 'folder')) {
				    if ($includeId) {
				        $ary[] = array($node->get_attribute('id'), getFolderName($node));
				    }else{
					   $ary[] = getFolderName($node);
				    }
				}
				$node = $node->parent_node();
			} while ($node != NULL);
		}
		return array_reverse($ary);
	}

	function setMessageID($fid) {
		global $_COOKIE, $sysSession, $msgFuncID;

		dbSet('WM_session', "cur_func={$msgFuncID['message']}", "idx='{$_COOKIE['idx']}'");
		$sysSession->cur_func = $msgFuncID['message'];
		saveSetting('folder_id', $fid, '');  // 回存設定
	}

	function setNotebookID($fid) {
		global $_COOKIE, $sysSession, $msgFuncID;

		dbSet('WM_session', "cur_func={$msgFuncID['notebook']}", "idx='{$_COOKIE['idx']}'");
		$sysSession->cur_func = $msgFuncID['notebook'];
		saveSetting('folder_id', $fid, '');  // 回存設定
	}

	/**
	 * 檢查資料夾有沒有在筆記本中
	 * @param
	 * @return
	 **/
	function ckNBFolder($fid) {
		global $sysSession, $msgFuncID;

		$isNB = false;
		$fid = trim($fid);
		if (($fid == 'sys_notebook') || ($fid == 'sys_notebook_trash')) {
			$isNB = true;
		} else {
			$content = getFolderXML();
			if (!$xmlvars = domxml_open_mem($content)) {
				$isNB = false;
			} else {
				$ctx = xpath_new_context($xmlvars);
				$folder = xpath_eval($ctx, "//folder[@id='{$fid}']/ancestor::folder[@id='sys_notebook']");
				$isNB = (count($folder->nodeset) > 0);
			}
		}

		return $isNB;
	}

	/**
	 * 取得資料夾key->value的陣列，用於方便產生select input
	 */
	function getFolderArray() {
		global $sysSession;
		$lang = strtolower($sysSession->lang);
		if ($lang == 'user_define') $lang = 'user-define';
		$rtnArr = array();

		if ($xmlVars = domxml_open_mem(getFolder())) {
			$ctx = xpath_new_context($xmlVars);
			$folders = $ctx->xpath_eval('//folder[@id]');
			if (count($folders->nodeset)) {
				foreach ($folders->nodeset as $folder) {
					$fid = $folder->get_attribute('id');
					$ftitle = '';
					$children = $folder->child_nodes();
					foreach ($children  as $child) {
						if ($child->node_type() != 1 || $child->tagname() != 'title') continue;
						$titles = $child->child_nodes();
						foreach ($titles as $title) {
							if ($title->node_type() != 1) continue;
							$ftitle = $title->get_content();
							if ($title->tagname == $lang) break;
						}
						break;
					}
					$rtnArr[$fid] = $ftitle;
				}
			}
		}
		return $rtnArr;
	}

	/**
	 * 將夾檔字串轉為 Link
	 * @param string $attach : 以 Tab 隔開的夾檔字串
	 * @param string $target : attach.php 所處的路徑
	 * @return string $r : 一串 Link
	 **/
	function gen_msg_attach_link($attach, $target=''){
		global $sysSession, $sysSiteNo;
		if (empty($attach)) return null;
		$type = array('avi','bmp','doc','gif','htm','html','jpg','mp3','pdf','ppt','txt','wav','xls','zip');

		$a    = explode(chr(9), trim($attach));
		$uDir = MakeUserDir($sysSession->username);
		$r    = '';
		for($i = 0; $i < count($a); $i += 2) {
			$filename = $uDir . '/' . $a[$i + 1];
			if (!@file_exists($filename)) continue;

			$str  = '';
			$leng = @filesize($filename);
			if (!empty($leng)) {
				if ($leng > 1024) {
					$leng = round($leng / 1024, 2);
					$str = ' (' . $leng . ' KB)';
				} else {
					$str = ' (' . $leng . ' Bytes)';
				}
			}
			$src = (ereg('\.([a-z]{3,4})$', $a[$i+1], $reg) && in_array(strtolower($reg[1]), $type)) ? strtolower($reg[1]) : 'default';
			$icon = '<img border="0" align="absmiddle" src="/theme/' . $sysSession->theme . '/filetype/' . $src . '.gif"' .
				' alt="' . $a[$i] . '" title="' . $a[$i] . '" />' . $a[$i] . $str;

			$ticket = md5($sysSiteNo . $sysSession->msg_serial . $sysSession->username . 'Attachment' . $sysSession->ticket . $sysSession->school_id . $a[$i+1]);
			$r .= '<a href="' . $target . 'attach.php?f=' . $a[$i+1] . '&t=' . $ticket . '" target="attach_win" class="cssAnchor" onclick="event.cancelBubble=true;">'. $icon .'</a><br />';
		}
		return $r;
	}
	
	/**
	 * 清除個人目錄多餘的訊息附檔
	 */
	function cleanRedundancyAttachments()
	{
	    global $sysSession;

        $files = array(); // 目前有訊息與之關連的檔案
	    $rs = dbGetCol('WM_msg_message', 'attachment', "receiver = '{$sysSession->username}' and attachment != ''");
	    if (is_array($rs))
	    {
	        // 讀出所有有效的檔案
	        foreach ($rs as $r)
	        {
				$columns = explode(chr(9), $r);
				$i = 1;
				while(isset($columns[$i]))
				{
				    $files[] = $columns[$i];
				    $i+=2;
				}
			}
		}

		// 讀出個人目錄中的訊息附檔
		$filepath = sysDocumentRoot . DIRECTORY_SEPARATOR . 'user' .
									  DIRECTORY_SEPARATOR . $sysSession->username[0] .
									  DIRECTORY_SEPARATOR . $sysSession->username[1] .
									  DIRECTORY_SEPARATOR . $sysSession->username . DIRECTORY_SEPARATOR;
		$cwd = getcwd();
		chdir($filepath);
		$fs = glob('WM*'); // 所有的附檔
		chdir($cwd);

		// 刪除多出來的檔案
		$a = array_diff($fs, $files);
		foreach ($a as $x) @unlink($filepath . $x);
	}

?>
