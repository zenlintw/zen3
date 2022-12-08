<?php
	/**
	 * 精華區收入筆記本顯示筆記本目錄函式 (改自訊息中心共用的函式 /message/lib.php)
	 *
	 * 建立日期：2004/08/13
	 * @author  Kuo Yang Tsao
	 * @copyright 2004 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	/*************************
	 * nb_getFolderXML()
	 * @return string 所取得資料夾內容值(XML 格式)
	 *************************/
	function nb_getFolderXML() {
		global $sysSession, $sysConn;//, $xmlStrs;
		$content = '';

		if (!empty($xmlStrs)) return $xmlStrs;
		$RS = dbGetStSr('WM_msg_folder', 'content', "username='{$sysSession->username}'", ADODB_FETCH_ASSOC);
		if (!$RS) {
			$xml = file(sysDocumentRoot . '/config/xml/msg_folder.xml');
			$content = implode('', $xml);
			dbNew('WM_msg_folder', 'username, content', "'{$sysSession->username}', '{$content}'");
		} else {
			$content = $RS['content'];
		}

		return $content;
	}

	/**
	 * 取得訊息中心的目錄設定值
	 * @return string 訊息中心的 XML 設定值
	 **/
	function nb_getFolder() {
		global $sysSession, $sysConn;
		$content = '';

		$content = nb_getFolderXML();
		if (!$xmlvars = domxml_open_mem($content)) {
			return $content;
		}

		$ctx = xpath_new_context($xmlvars);
		$foo = xpath_eval($ctx, "//folder[@id='sys_notebook']");
		if (count($foo->nodeset) > 0) {
			$node = $foo->nodeset[0];
			// 顯示我的筆記本
			$content  = '<manifest>';
			$fod = xpath_eval($ctx, '//setting');
			if (count($fod->nodeset) > 0) {
				$set = $fod->nodeset[0];
				$content .= $xmlvars->dump_node($fod->nodeset[0]);
			} else {
				$content .= '<setting id="notebook"></setting>';
			}
			$content .= $xmlvars->dump_node($node);
			$content .= '</manifest>';
		}

		//$xmlDcos = $content;
		return $content;
	}

	/**
	 * 取得設定值中的設定
	 * @param
	 * @return
	 **/
	function nb_getSetting($nodeName, $nodeID = '') {
		global $sysSession, $sysConn;

		$nodeValue = '';
		$content = nb_getFolderXML();

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
	function nb_getFolderId() {
		$folder_id = nb_getSetting('folder_id', '');
		if (empty($folder_id)) $folder_id = 'sys_inbox';

		return $folder_id;
	}
?>
