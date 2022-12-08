<?php
	set_time_limit(300);
	// echo "<pre>QTI_which ";
	// var_dump(QTI_which);
	// echo "</pre>";
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	// require_once(sysDocumentRoot . '/teach/exam/QTI_parser.php');
	if (!defined('QTI_DISPLAY_ANSWER')  ) define('QTI_DISPLAY_ANSWER',   QTI_which == 'questionnaire' ? false : ($_GET['pda'] ? true : false));
	if (!defined('QTI_DISPLAY_OUTCOME') ) define('QTI_DISPLAY_OUTCOME',  false);
	if (!defined('QTI_DISPLAY_RESPONSE')) define('QTI_DISPLAY_RESPONSE', false);
	// echo "<pre>QTI_DISPLAY_ANSWER ";
	// var_dump(QTI_DISPLAY_ANSWER );
	// echo "</pre>";
	// echo "<pre>_GET['pda']";
	// var_dump($_GET['pda']);
	// echo "</pre>";
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/teach/exam/m_QTI_transformer.php');
	//require_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600400100';
		$sysSession->restore();
		if (!aclVerifyPermission(1600400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

		}
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700400100';
		$sysSession->restore();
		if (!aclVerifyPermission(1700400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

		}
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800300400';
		$sysSession->restore();
		if (!aclVerifyPermission(1800300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

		}
	}
	else if (QTI_which == 'peer') {
		$sysSession->cur_func='1710400100';
		$sysSession->restore();
		if (!aclVerifyPermission(1710400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

		}
	}
	//ACL end

	function escape_sql($str){
		return str_replace(array('\\', '"', '#', '_'), array('\\\\', '\\"', '\\#', '\\_'), $str);
	}

	if (!function_exists('strip_score'))
	{
    	function strip_score($data)
    	{
    	    return preg_replace('!<b>[^<>]*\[[\d.]+\]</b>!isU', '', $data);
    	}
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

	/**
	 * 摩擬 XMLDOM 之 getElementById() method.
	 */
	function getElementById(&$node, $id){
		if ($node->node_type() == XML_ELEMENT_NODE){
			if ($node->get_attribute('id') == $id)
				return $node;
			else
				foreach($node->child_nodes() as $child){
					$ret = getElementById($child, $id);
					if ($ret != null) return $ret;
				}
		}
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
	function id2ident(&$dom){
		$secTags = array('section', 'assessment', 'objbank');
		foreach($secTags as $tag){
			$nodes = $dom->get_elements_by_tagname($tag);
			if (is_array($nodes)) foreach($nodes as $node){
				$nn = $node->get_attribute('id');
				$node->set_attribute('ident', $nn);
				$node->remove_attribute('id');
			}
		}
	}

	/**
	 * ==============================   主程式開始   =====================================
	 */
	header('Content-type: text/xml');
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {

		$content = stripslashes($GLOBALS['HTTP_RAW_POST_DATA']);
		$sqls = '';
		if(strpos($content, '<wm_immediate_random_generate_qti') !== FALSE)
		{
			preg_match_all('!<(score|amount)\s+selected="true">([^<]*)</\1>!isU', $content, $ss);
			foreach($ss[1] as $k => $v) $keep[$v] = $ss[2][$k];

			if(substr_count($content, '<condition>') > 1)
			{
				preg_match_all('!<condition>(.+)</condition>!isU', $content, $re);
				$a = $re[1];
				unset($re);
			}
			else
				$a = array($content);

			foreach($a as $context)
			{
				$immediate_random_pick = true; $irgs = $keep;
				if (preg_match_all('!<([^>]+)\s+selected="(true|false)">(.*)</[^>]+>!sU', $context, $regs))
					foreach($regs[1] as $k => $v)
						if ($regs[2][$k]=='true') $irgs[$v] = $regs[3][$k];

				$sqls .= sprintf('(select ident from WM_qti_%s_item where course_id=%u ', QTI_which, $sysSession->course_id);
				foreach(array('type','version','volume','chapter','paragraph','section','level') as $item){
					if (isset($irgs[$item]))
					{
						if (empty($irgs[$item])) continue;
						$sqls .= sprintf('and %s in (%s) ', $item, $irgs[$item]);
					}
				}

				if (isset($irgs['fulltext'])){
					$fts = explode("\t", $irgs['fulltext'], 2);
					if (!empty($fts[0]))
						$sqls .= sprintf('and (content like ("%%%s%%") or content like ("%%%s%%")) ', escape_sql($fts[0]), escape_sql($fts[1]));
				}

				$sqls .= sprintf('order by rand() limit %d) union ', $irgs['amount']);
			}
			if (count($a) > 1)
				$sqls = preg_replace('/ union $/', sprintf(' order by rand() limit %d', $irgs['amount']), $sqls);
			else
				$sqls = substr($sqls, 0, -7);

			// 若將三行字串取代合成一行會有取代不完全的問題，導致 SQL 錯誤，故分成三行處理。
			$sqls = str_replace(' or content like ("%%")', '', $sqls);
			$sqls = str_replace('content like ("%%") or ', '', $sqls);
			$sqls = str_replace(' and (content like ("%%"))', '', $sqls);
			$sqls = preg_replace('/ in \(([\d]+)\)/', ' = \1', $sqls);

			chkSchoolID('WM_qti_'.QTI_which.'_item');
			$content = '<questestinterop xmlns:wm="http://www.sun.net.tw/WisdomMaster"><item id="' .
					   @implode('" score="" /><item id="', $sysConn->GetCol($sqls)) .
					   '" score="" /></questestinterop>';
            $irgs['amount'] = abs(intval($irgs['amount']));
			$sc = $irgs['amount'] ? (floor($irgs['score'] * 10 / $irgs['amount']) / 10) : 0;
            $o = sprintf('score="%f"', $sc);
			$content = str_replace(array('<item id="" score="" />', 'score=""'),
								   array('', $o),
								   $content
								  );
			// 確定有搜尋到題目，才能處理多餘題目的分數，不然會產生錯誤的xml
			if (strpos($content, $o) !== false)
			{
	            if (($remnant = $irgs['score'] - ($sc * $irgs['amount'])) != 0)
	            {
	                $xx = explode($o, $content);
	                $xxx = array_pop($xx);
	                $xx[count($xx)-1] .= sprintf('score="%.2f"', $sc+$remnant) . $xxx;
	                $content = implode($o, $xx);
	            }
			}
    	}

        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
		if(!$xmlDoc = domxml_open_mem($content)) {
			error_log('Error while parsing the document.', 0);
			die('<errorlevel>1</errorlevel>');
		}

		$ctx = xpath_new_context($xmlDoc);
		$root = $xmlDoc->document_element();

		$ids = array();
		$nodes = $xmlDoc->get_elements_by_tagname('item');
		foreach($nodes as $item)
		{
			$ids[] = $item->get_attribute('id');
			$item->set_attribute('score', $item->get_attribute('score'));	// 強制設定一個 score 屬性
		}

		if ($ids){
			$idents = 'ident in ("' . implode('","', $ids) . '")';
			// $RS = dbGetStMr('WM_qti_' . QTI_which . '_item', 'ident,content,attach', $idents);
			$real_items = $sysConn->GetAssoc('select ident,content from WM_qti_' . QTI_which . '_item where ' . $idents);
			$ids = array_flip($ids);

			// while(!$RS->EOF){
			// 	$node = getElementById($root, $RS->fields['ident']);
			// 	if (!is_null($node))
			// 	{
			// 		unset($ids[$RS->fields['ident']]);
			// 		// $newdom = domxml_open_mem(str_replace('<decvar vartype="Integer" defaultval="0" />', sprintf('<decvar vartype="Integer" defaultval="%u" />', $node->get_attribute('score')), $RS->fields['content']));
			// 		$newdom = domxml_open_mem(preg_replace('#<decvar[^>]*/>#isU', sprintf('<decvar vartype="Integer" defaultval="%.2f" />', $node->get_attribute('score')), $RS->fields['content']));
			// 		$node->replace_node($newdom->document_element());
			// 		if (preg_match('/^a:[0-9]+:{/', $RS->fields['attach']))
			// 			$GLOBALS['attachments'][$RS->fields['ident']] = unserialize($RS->fields['attach']);
			// 	}
			// 	$RS->MoveNext();
			// }

			//if (preg_match_all('!<item [^>]*\bid="([^"]+)" score="([^"]*)">[^<]*</item>!isU', $xmlDoc->dump_mem(), $regs))

			// die(htmlspecialchars($xmlDoc->dump_mem()));
			if (preg_match_all('!<item\b[^>]*\bid="([^"]+)"[^>]*(\bscore="([^"]+)"([^>]*)?)?(/>|>[^<]*</item>)!isU', $xmlDoc->dump_mem(), $regs))
			{
            	// 把配分收集起來
				$scores = array();
				foreach($regs[1] as $k => $v)
				{
					$scores[$v] = $regs[3][$k];
				}

				// 將分數填進真實 item xml 中
                if (is_array($real_items)) {
                    foreach($real_items as $k => $v)
                    {
                        $real_items[$k] = preg_replace('#<decvar[^>]*/>#isU', sprintf('<decvar vartype="Integer" defaultval="%.2f" />', $scores[$k]), $v);
                    }
                }

				// 用代換的方式，把試卷中的 item 代換為真實 xml
				$replaces = array();
				foreach($regs[1] as $k)
				{
					$replaces[] = $real_items[$k];
				}

				$result = setEncoding(str_replace($regs[0], $replaces, $xmlDoc->dump_mem()));

				// 最後把夾檔準備好
				$a = $sysConn->GetAssoc('select ident,attach from WM_qti_' . QTI_which . '_item where ' . $idents);
                if (is_array($a)) {
                    foreach($a as $k => $v)
                        if (preg_match('/^a:[0-9]+:{/', $v))
                            $GLOBALS['attachments'][$k] = unserialize($v);
                }

            }

			if (QTI_which == 'questionnaire')
			{
		    	ob_start('strip_score');
				parseQuestestinterop($result);
				ob_end_flush();
			}
			else
				parseQuestestinterop($result);

/*
			foreach($ids as $id => $foo)
			{
				$node = getElementById($root, $id);
				if (!is_null($node))
				{
					$parent = $node->parent_node();
					$parent->remove_child($node);
				}
			}
			id2ident($xmlDoc);					// 將 <section> 的 id 換成 ident
			// travelQuestestinterop($root);	// 交由 QTI_parser 產生試卷
			parseQuestestinterop($xmlDoc->dump_mem(false));
*/
		}
		// echo '<pre>' . htmlspecialchars($xmlDoc->dump_mem(true)) . '</pre>';

	}
