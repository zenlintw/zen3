<?php
	/**
	 * 筆記本函式 (改自訊息中心共用的函式 /message/lib.php)
	 *
	 * 建立日期：2004/08/05
	 * @author  Kuo Yang Tsao
	 * @copyright 2004 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/forum/file_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$langList   = Array('big5', 'gb2312', 'en', 'euc-jp', 'user-define');

	$nb_dom     = null;		// 筆記本 DOM 物件
	$nodeTARGET = null;	    // 將複製過去的目標資料夾 DOM_XML 節點
	$sysLang    = strtolower($sysSession->lang);

	/********************************
	 * 產生一個 筆記本 資料夾節點(僅在XML上處理)
	 * 參數:
	 *		@param object &$parent_node	: 父節點
	 *		@param string $name			: 資料夾名稱
	 *		@param object &$node		: 產生之節點 ( 外面可以用 $node->get_attribute('id'); 取得其 folder_id )
	 *		@return boolean				: true 成功 false 失敗
	 ********************************/
	function nb_makeXMLFolder(&$parent_node, $name , &$node)
	{
		global $langList, $sysSession, $nb_dom, $sysLang;

		$node = null;
		if(!is_object($parent_node)){
			echo "<!-- nb_makeXMLFolder(): parent_node not exists -->\r\n";
			return false;
		}

		if(!$nb_dom) {
			return false;
		}

		$node = $nb_dom->create_element('folder');			// 建立一個節點
		$node_id = uniqid('USER_');						// 產生唯一之 folder_id
		$node->set_attribute('id', $node_id);			// 將 folder_id 設給 $node

		$node_set = $nb_dom->create_element("setting");
		$node->append_child($node_set);

		$node_title = $nb_dom->create_element("title");
		$node_title->set_attribute("default", $sysLang);
		for ($i = 0; $i < count($langList); $i++) {	// $node 各語系標題
			$node3 = $nb_dom->create_text_node($name);
			$node1 = $nb_dom->create_element($langList[$i]);
			$node1->append_child($node3);
			$node_title->append_child($node1);
		}
		$node->append_child($node_title);

		$node_help = $nb_dom->create_element("help");
		for ($i = 0; $i < count($langList); $i++) {
			$node3 = $nb_dom->create_text_node($name);
			$node1 = $nb_dom->create_element($langList[$i]);
			$node1->append_child($node3);
			$node_help->append_child($node1);
		}
		$node->append_child($node_help);

		$parent_node->append_child($node);
		return true;
	}

	function nb_getFolderXML() {
		global $sysSession, $sysConn, $xmlStrs;
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
	 * 建立子資料夾(供下面 nb_makeFolders() 呼叫
	 * @param object $node : 目前筆記本 XML Node 物件
	 * @return boolean 用 XML 包裝起來的訊息包含了成功或失敗的訊息
	 **/
	function nb_makeSubFolders(&$node, $q_pathname, $subfolder_name){
		global $sysSession, $sysConn, $nb_dom, $sysLang;

		// 先判斷是否該資料夾存在
		$ctx    = xpath_new_context($node);
		$filter = "/folder/title/{$sysLang}[text()='{$subfolder_name}']/parent::*/parent::*";
		$foo    = xpath_eval($ctx, $filter);
		if(count($foo->nodeset)==0) {	// 無該子資料夾, 建立!
			nb_makeXMLFolder($node, $subfolder_name , $cur_node);
		} else {
			$cur_node = $foo->nodeset[0];
		}

		// 再繼續建立下一層
		$fullpath = ($q_pathname=='/'?'':$q_pathname) . '/'.$subfolder_name;
		$q_RS = dbGetStMr('WM_bbs_collecting', 'path,subject',"board_id={$sysSession->board_id} and path='{$fullpath}' and type='D'", ADODB_FETCH_ASSOC);
		while(!$q_RS->EOF)
		{
			nb_makeSubFolders($cur_node, $fullpath, $q_RS->fields['subject']);
			$q_RS->MoveNext();
		}
	}

	/**
	 * 儲存編修後的訊息中心的資料夾
	 * @param object $xmldoc : 整個要儲存的 XML 設定檔
	 * @return string 用 XML 包裝起來的訊息包含了成功或失敗的訊息
	 **/
	function nb_makeFolders(&$RS, $target_folder_id) {
		global $sysSession, $sysConn, $nb_dom, $nodeTARGET;
		$content = '';

		/* 更新資料夾 (Begin) */
		$sysID   = array('sys_notebook', 'sys_notebook_trash', 'sys_inbox', 'sys_sent_backup', 'sys_online_msg_backup', 'sys_trash');
		$newID   = array();
		//$xmlData = null;

		// 取得原始資料 - 個人訊息中心的目錄設定
		$content = nb_getFolderXML();
		$nb_dom  = domxml_open_mem($content);
		$ctx     = xpath_new_context($nb_dom);
		$trash   = 'sys_notebook_trash';

		// 查看目標資料夾是否存在
		$nodeTARGET  = null;
		$chk_ctx     = xpath_new_context($nb_dom); //nodeNB);
		if ($target_folder_id == 'sys_notebook')
			$foo = xpath_eval($chk_ctx, "//folder[@id='{$target_folder_id}']");
		else
			$foo = xpath_eval($chk_ctx, "//folder[@id='sys_notebook']//folder[@id='{$target_folder_id}']");
		if (count($foo->nodeset) > 0) {
			$nodeTARGET   = $foo->nodeset[0];
		} else {	// 找不到該節點
			return false;
		}

		while(!$RS->EOF)
		{
			if($RS->fields['type']=='D') {
				nb_makeSubFolders($nodeTARGET, $sysSession->q_path, $RS->fields['subject']);
			}

			$RS->MoveNext();
		}

		// 更新資料庫

		$content = $nb_dom->dump_mem(true);
		$content = addcslashes($content, "\'");
		dbSet('WM_msg_folder', "content='{$content}'", "username='{$sysSession->username}'");

		/* 更新資料夾 (End) */
	}

	/**
	 * 複製精華區某資料夾下的所有檔案到筆記本中
	 * @param string $nb_file_path : 筆記本檔案存放實體路徑
	 * @param string $q_file_basepath  : 精華區檔案存放實體基礎路徑
	 * @param string $q_path : 精華區目錄
	 * @param object $nb_folder_node : 筆記本目標資料夾 DOM_XML 節點
	 * @return boolean
	 **/
	function nb_copyFolderFiles(&$nb_file_path, &$q_file_basepath, $q_path, &$nb_folder_node) {
		global $sysSession,$sysConn, $MSG, $nb_dom, $nodeTARGET, $sysLang;

		$qRS = dbGetStMr('WM_bbs_collecting','*', "board_id={$sysSession->board_id} and path='{$q_path}'", ADODB_FETCH_ASSOC);
		while(!$qRS->EOF) {
			$node    = $qRS->fields['node'];
			$site    = $qRS->fields['site'];
			$poster  = $qRS->fields['poster'];
			$type    = $qRS->fields['type'];
			$subject = mysql_escape_string($qRS->fields['subject']);

			if($type=='D')	{ // 處理資料夾
				$q_path1 = $q_path . '/' . $qRS->fields['subject'];
				$ctx     = xpath_new_context($nb_folder_node);
				$filter  = "/folder/title/{$sysLang}[text()='{$qRS->fields['subject']}']/parent::*/parent::*";
				$foo     = xpath_eval($ctx, $filter);
				if(count($foo->nodeset)==0) {
					return false;
				}

				if(!nb_copyFolderFiles($nb_file_path, $q_file_basepath, $q_path1, $foo->nodeset[0])) {
					return false;
				}

				$qRS->MoveNext();
				continue;
			}
			$nb_folder_id = $nb_folder_node->get_attribute('id');
			$content      = mysql_escape_string(nb_recompose($qRS));

			$from_path    = $q_file_basepath . DIRECTORY_SEPARATOR . $node;
			$attach       = '';

			// 複製檔案
			if(!b_copyfiles( $from_path , $nb_file_path , trim($qRS->fields['attach']), $attach))
			{
				//echo "<!-- after b_copyfiles -->\r\n";
				return false;
			} else {
				$fields = '`folder_id`, `sender`, `receiver`, `submit_time`, `receive_time`, `priority`, ' .
						  '`subject`, `content`, `attachment`, `note`, `content_type`';
				$values = "'{$nb_folder_id}','{$sysSession->username}', '{$sysSession->username}', ".
						  "Now(), Now(), 0, '{$subject}', '{$content}', " .
						  "'{$attach}', '', 'html'";

				if(!dbNew('WM_msg_message', $fields, $values)) {
					nb_rollback_files( $nb_file_path, $attach );
				//echo "<!-- after dbNew('WM_msg_message') -->\r\n";
                                    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '新增到訊息中心失敗(nb_copyFolderFiles)：' . $sysSession->username . '|' . $sysSession->username . '|' . $subject);
					return false;
				}
			}
			$qRS->MoveNext();
		}
		return true;
	}

	/**
	 * 重組文章內容
	 * @param ADO RS $RS : 文章資料集(RecordSet)
	 * @return string : 組合好的 HTML 語法
	 **/
	function nb_recompose(&$RS)
	{
		global $sysSession, $MSG;
		ob_start();

		showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" class="box01"');

		showXHTML_tr_B('class="bg04 font01"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['bname'][$sysSession->lang]);
			showXHTML_td('width="640"', $sysSession->board_name . ' - ' . $MSG['quint'][$sysSession->lang]);
		showXHTML_tr_E('');
		showXHTML_tr_B('class="bg03 font01"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', 'PATH :');
			showXHTML_td('width="640"', $RS->fields['path']);
		showXHTML_tr_E('');

		showXHTML_tr_B('class="bg04 font01"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['posters'][$sysSession->lang]);
			showXHTML_td('width="640"', "<a href=\"mailto:{$RS->fields['email']}\" class=\"link_fnt01\">{$RS->fields['poster']}</a> ".($RS->fields['homepage']?("<a href=\"{$RS->fields['homepage']}\" target=\"_blank\">{$RS->fields['realname']} </a>"):"({$RS->fields['realname']} )"));
		showXHTML_tr_E();
		showXHTML_tr_B('class="bg03 font01"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['times'][$sysSession->lang]);
			showXHTML_td('width="640"', $RS->fields['pt']);
		showXHTML_tr_E();
		showXHTML_tr_B('class="bg04 font01"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['subjs'][$sysSession->lang]);
			showXHTML_td('width="640"',$RS->fields['subject']);
		showXHTML_tr_E();
		showXHTML_tr_B('class="bg03 font01"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['contents'][$sysSession->lang]);
			showXHTML_td('width="640"','<table><tr><td class="font01"><br />'.$RS->fields['content'].'<p /></td></tr></table>');
		showXHTML_tr_E();
		showXHTML_table_E();

		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/****************************************
	 * 自討論版複製實體檔案到筆記本資料夾中
	 * @param string $to_path   : 目的路徑   ( 尾端不含 '/' )
	 * @param string $attach    : 新夾檔字串 ( '名稱'[TAB]'實體檔名'[TAB]'名稱'[TAB]'實體檔名'[TAB]... )
	 * @return void
	 ****************************************/
	function nb_rollback_files( $to_path, $attach ) {
		$files  = explode(Chr(9), $attach);	// 原夾檔字串
		if(count($files)==0) return;		// 無夾檔

		for($i=0;$i<count($files);$i+=2) {
			$path = $to_path . "/" . $files[$i+1];
			unlink($path);
		}
	}
?>
