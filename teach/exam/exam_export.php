<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/03/24                                                            *
	 *		work for  : export XML from a exam                                                *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/teach/exam/qti_xml_lib.php');

	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600100600';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700100600';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800100100';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
			
	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	if (!ereg('^[0-9]+$', $_SERVER['argv'][0])) {
	   wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][0] , 1, 'auto', $_SERVER['PHP_SELF'], 'exam_id incorrect');
	   die('exam_id incorrect.');
	}

	function setEncoding($xml, $encoding='UTF-8')
	{
		if (preg_match('/<\?xml\b[^>]*\?>/isU', $xml, $regs))
		{
			if (preg_match('/\bencoding\s*=\s*/isU', $regs[0]))
				return $xml;
			else
				return preg_replace('/\?>/', ' encoding="' . $encoding . '"?>', $xml, 1);
		}
		else
			return '<?xml version="1.0" encoding="UTF-8"?>' . $xml;
	}

	function selectSingleNode($xpath, $node=false)
	{
	    global $ctx, $xmlDoc;
	    
	    if ($node && ($dom = $node->owner_document()) !== $xmlDoc)
	    {
	        $cty = $dom->xpath_new_context();
	        $ctx->xpath_register_ns('wm','http://www.sun.net.tw/WisdomMaster');
	        $ret = $cty->xpath_eval($xpath, $node);
		}
	    else
	    {
	        $ret = $node ? $ctx->xpath_eval($xpath, $node) : $ctx->xpath_eval($xpath);
	    }

        if ($ret && is_array($ret->nodeset) && count($ret->nodeset))
            return $ret->nodeset[0];
		else
		    return null;
	}

	/**
	 * 將 <item> 的 name space 去掉
	 */
	function gemoveItemNS(&$dom){
		$nodes = $dom->get_elements_by_tagname('item');
		foreach($nodes as $node){
			$node->set_namespace('');
			$node->set_namespace('', 'wm');
		}
	}

	/**
	 * 將試卷的 id 換成 ident
	 */
	function id2ident(&$ctx){
	    $ret = $ctx->xpath_eval('//*[name()="section" or name()="assessment" or name()="objbank"][@id]');
		if ($ret && is_array($ret->nodeset) && count($ret->nodeset))
			foreach($ret->nodeset as $node){
				$node->set_attribute('ident', $node->get_attribute('id'));
				$node->remove_attribute('id');
			}
	}


	list($title, $content, $type) = dbGetStSr('WM_qti_' . QTI_which . '_test', 'title, content, type', 'exam_id=' . $_SERVER['argv'][0] . ' and course_id=' . $course_id, ADODB_FETCH_NUM);
	if ($content){
		if (strpos($content, '<wm_immediate_random_generate_qti') !== FALSE) {
			echo '<script language="javascript">alert("' . $MSG['immediate_random_generate_test'][$sysSession->lang] . '"); location.href = "exam_maintain.php";</script>';
			exit;
		}	
		if(!$xmlDoc = domxml_open_mem(preg_replace(array('/>\s+</', '/\s+xmlns="[^"]*"/'), array('><', ''), setEncoding($content)))) {
			error_log('Error while parsing the document.', 0);
			die('<errorlevel>1</errorlevel>');
		}
		$root = $xmlDoc->document_element();
		$ctx = $xmlDoc->xpath_new_context();
		$ctx->xpath_register_ns('wm','http://www.sun.net.tw/WisdomMaster');

		$ret = $ctx->xpath_eval('./wm:title', $root);
		if (is_array($ret->nodeset) && count($ret->nodeset))
		{
		    $title_node = $ret->nodeset[0];
		    remove_all_children($title_node);
		}
		else
		    $title_node = $root->insert_before($xmlDoc->create_element_ns('http://www.sun.net.tw/WisdomMaster', 'title', 'wm'), $root->first_child());
		    
	    $title_node->append_child($xmlDoc->create_text_node($title));
	    
	    $prev_n = $ctx->xpath_eval('//questestinterop');
		if (is_object($prev_n->nodeset[0]))
		{
		    $prev_n->nodeset[0]->set_attribute('use_type',$type);
		}

		$idents = 'ident in (';
		foreach($xmlDoc->get_elements_by_tagname('item') as $item){
			$idents .= "'" . $item->get_attribute('id') . "',";
		}
		$idents = ereg_replace(',$', ')', $idents);

		if ($idents != 'ident in (')
		{
			$RS = dbGetStMr('WM_qti_' . QTI_which . '_item', 'ident,content,attach', $idents, ADODB_FETCH_ASSOC);
			while(!$RS->EOF)
			{
				if ($item = selectSingleNode('//item[@id="' . $RS->fields['ident'] . '"]'))
				{
					$newdom = domxml_open_mem(preg_replace(array('/>\s+</', '/\s+xmlns="[^"]*"/'),
														   array('><', ''),
														   get_qti_item_xml($RS->fields['ident'], $RS->fields['content'], $RS->fields['attach'])
														  )
											 );
					if ($score = selectSingleNode('/item/resprocessing/outcomes/decvar[1]', $newdom->document_element()))
					{
						$score->set_attribute('defaultval', (float)$item->get_attribute('score'));
					}
					else
					    echo $newdom->dump_mem(true);

					$item->replace_node($newdom->document_element());
					unset($newdom, $score);
				}
				unset($item);
				$RS->MoveNext();
			}

			// gemoveItemNS($xmlDoc);	// 去掉每個 <item> 的 name space
			id2ident($ctx);		// 將 <section> 的 id 換成 ident
			$xmlstr = $xmlDoc->dump_mem(true);
			$replaces = array();
			if (preg_match_all('/<\w+:item ([^>]*)>/', $xmlstr, $regs, PREG_PATTERN_ORDER))
			{
				foreach($regs[0] as $i => $item)
				{
					$replaces[$item] = '<item xmlns:wm="http://www.sun.net.tw/WisdomMaster" ' . preg_replace('/\bxmlns(:\w+)?\s*=\s*"[^"]*"/', '', $regs[1][$i]) . '>';
				}
			}

            while (@ob_end_clean());
			$fname = sprintf('WM_qti_' . QTI_which . '_%s.xml', date('YmdHis'));
			header('Content-Disposition: attachment; filename="' . $fname . '"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Type: application/octet-stream; name="' . $fname . '"');
			echo count($replaces) ? preg_replace('!</\w+:item>!', '</item>', str_replace(array_keys($replaces), array_values($replaces), $xmlstr)) : $xmlstr;
		}
		else
		{
			echo '<script language="javascript">alert("' . $MSG['export_no_items'][$sysSession->lang] . '");</script>';
			exit;
		}
	}
	else {
		wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][0] , 2, 'auto', $_SERVER['PHP_SELF'], 'Exporting failure');
		die('<h2 align="center">Exporting failure.</h2>');
	}
?>
