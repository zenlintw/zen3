<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/02/26                                                            *
	 *		work for  : search Item                                                           *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	/**
	* 取某節點裡的最底層文字
	* param element $element 節點
	* return string 節點文字
	*/
	function getNodeContent($element) {
		if (!is_object($element))
			return '';
		$node = $element;
		while ($node->has_child_nodes()) {
			$node = $node->first_child();
		}
		return $node->node_value();
	}

	/**
	* 取出節點中的resprocessing標籤中的文字
	* return array 節點文字陣列
	*/
	function getFillContent($node) {
		global $ctx;
		$id = $node->get_attribute('ident');
		$ret = $ctx->xpath_eval("/item/resprocessing/respcondition/conditionvar/varequal[@respident='$id']"); //Evaluates the XPath Location Path in the given string->秀出答案與配分
		if (is_array($ret->nodeset) && count($ret->nodeset))//確認$ret是否為陣列並計算其元素數目
			return '((' . $ret->nodeset[0]->get_content() . '))';
		else
			return '(())';
	}

	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func = '1600100100';
	} else if (QTI_which == 'homework') {
		$sysSession->cur_func = '1700100100';
	} else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func = '1800100100';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {

	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	function change_escape($matches) {
		return sprintf('&#x%s;', $matches[1]);
	}

	header('Content-type: text/xml');
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			error_log('Error while parsing the document.', 0);
			die('<errorlevel>1</errorlevel>');
		}
		$root = $dom->document_element();
		if ($root->tagname() != 'form') {
			error_log('XML root tag must be <form>.', 0);
			die('<errorlevel>2</errorlevel>');
		}

		$ctx = xpath_new_context($dom);
		$nodes = $ctx->xpath_eval('//rowspage');
        $rowspage = getNodeContent($nodes->nodeset[0]);
		$snodes = $ctx->xpath_eval('//pages');
		$spage = getNodeContent($snodes->nodeset[0]);
        $extypeNodes = $ctx->xpath_eval('//exam_type');
        $exam_type = getNodeContent($extypeNodes->nodeset[0]);


		if ($rowspage <= 0) {
			$rows_page_share = sysPostPerPage;
			setcookie('SQrows_page', '', time() - 1, '/');
		} else {
			$rows_page_share = $rowspage;
			setcookie('SQrows_page', $rows_page_share, time() + 86400, '/');
		}

		$elements = array();
		foreach ($root->child_nodes() as $item) {
			if ($item->get_attribute('selected') == 'true') {
				$elements[$item->tagname()] = getNodeContent($item);
			} elseif ($item->tagname() == 'scope') {
				switch (intval(getNodeContent($item))) {
					case 3: $selectTable = 'WM_qti_share_item';
						$wheres = "category='" . QTI_which . "'";
						break;
					default: $selectTable = 'WM_qti_' . QTI_which . '_item';
						$wheres = "course_id=$course_id ";
						break;
				}
			}
		}

		foreach (array('type', 'version', 'volume', 'chapter', 'paragraph', 'section', 'level') as $item) {
			if (isset($elements[$item]) && ereg('^[0-9]+(,[0-9]+)*$', $elements[$item])) {
				$wheres .= ('and ' . $item . ' in (' . $elements[$item] . ') ');
			}
		}

		if (array_key_exists('myshare', $elements)) {
			$wheres .= sprintf('and course_id=%s ', $sysConn->qstr($sysSession->course_id));
		}

		if (isset($elements['fulltext'])) {
			list($elements['fulltext'], $elements['fulltext1']) = explode("\t", $elements['fulltext']);
			// 因改用FCKEditor，加上舊資料影響，因此轉碼兩次
			$elements['fulltext'] = htmlspecialchars(htmlspecialchars(preg_replace_callback('/%u([0-9A-Fa-f]{4})/', 'change_escape', $elements['fulltext'])));

			$wheres .= ('and (LOCATE("' . addslashes($elements['fulltext']) . '", content) or ' .
					'LOCATE("' . addslashes($elements['fulltext1']) . '", content)) ');
		}

        /* 愛上互動 不支援填充與配合題型 */
        if (intval($exam_type) == 5) {
            $wheres .= 'and (type != 4 and type !=6) ';
        }

		// MIS#23670 挑選題目排序要與題庫維護一致 by Small 2012/01/05
		// $limit = $wheres .  'limit ' . ($spage-1)*$rows_page_share . ',' . $rows_page_share;
		$limit = $wheres .  'order by ident asc limit ' . ($spage-1)*$rows_page_share . ',' . $rows_page_share;

		list($total) = dbGetStSr($selectTable, 'count(*)', $wheres, ADODB_FETCH_NUM);

		$RS = dbGetStMr($selectTable, 'ident,type,title,content,version,volume,chapter,paragraph,section,level,course_id', $limit, ADODB_FETCH_ASSOC);

		if ($RS === FALSE || $RS == -1) {
			die('<errorlevel>4</errorlevel>');
		}
		if ($RS->RecordCount() == 0) {
			die('<errorlevel>3</errorlevel>');
		}
		$xml = '<questestinterop>';
		$xml .= '<total>' . $total . '</total>';
		while ($fields = $RS->FetchRow()) {
			$item = '';
			$errorCode = 0;
			$isOwner = ($fields['course_id'] == $sysSession->course_id ? 1 : 0);
			unset($fields['course_id']); // 不顯示課程編號
//			$xml .= '<item>';
			foreach ($fields as $key => $val) {
				// if ($key == 'content') continue;
				if ($key == 'type') {
					$temp_type = $val;
				}
				if ($key == 'title') {
					$temp_title = $val;
					continue;
				}
				if ($key == 'content') {
					if (strstr($val, 'xmlns')) {
						$dom = @domxml_open_mem(preg_replace('/xmlns="[^"]+"/', '', $val));
						if ($dom) {
							$ctx = xpath_new_context($dom);
							$ret = $ctx->xpath_eval('/item/presentation//mattext');
							$nodes = is_array($ret->nodeset) ? $ret->nodeset : array(null);
							switch ($temp_type) {
								case 4://題型為填充題的話
									$topic = '';
									foreach ($nodes as $node) {
										$topic .= getNodeContent($node); //取節點(/item/presentation//mattext)裡的最底層文字
										$n = $node->parent_node(); //到父節點
										$n = $n->next_sibling(); //到旁節點
										if (is_object($n) && $n->node_name() == 'response_str') {
											$topic .= getFillContent($n); //'response_str->文字填充
										}
									}
									break;
								default:
									$topic = getNodeContent($nodes[0]); //取節點裡的最底層文字
									break;
							}
							$val = strip_tags($topic);
						} else {
							$errorCode = 1;
							$val = sprintf($MSG['msg_item_share_parse_error'][$sysSession->lang], strip_tags($temp_title));
						}
					}
					else {
						$val = strip_tags($temp_title);
					}
					$key = 'title';
				}
				$val = preg_replace('/[\x00-\x08\x0E-\x1F]+/', ' ', $val);
//				$xml .= sprintf('<%s>%s</%s>', $key, htmlspecialchars($val, ENT_NOQUOTES, 'UTF-8'), $key);
				$item .= sprintf('<%s>%s</%s>', $key, htmlspecialchars($val, ENT_NOQUOTES, 'UTF-8'), $key);
			}
			$xml .= sprintf(
				'<item owner="%d" code="%d">%s</item>',
				$isOwner, $errorCode, $item
			);
		}
		$xml .= '</questestinterop>';
		if ($xml == '<questestinterop></questestinterop>') {
			die('<errorlevel>3</errorlevel>');
		}
		die($xml);
	}
?>
<errorlevel>0</errorlevel>
