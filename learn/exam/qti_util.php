<?php
/**
 * 試卷翻頁、取題公用函式
 *
 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @package     WM3
 * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
 * @copyright   2000-2005 SunNet Tech. INC.
 * @version     CVS: $Id: qti_util.php,v 1.1 2010/02/24 02:39:07 saly Exp $
 * @link        http://demo.learn.com.tw/1000110138/index.html
 * @since       2005-12-07
 */

/**
 * 去掉空白文字節點
 *
 * @param XML_ELEMENT_NODE	$node	通常是 DOM 物件的 documentElement 物件
 *
*/
// if (!function_exists('rm_whitespace'))
if ($_SERVER['PHP_SELF'] == '/learn/exam/test.php')
{
	function rm_whitespace(&$node)
	{
		if ($node->node_type() == XML_ELEMENT_NODE)
			foreach($node->child_nodes() as $child)
			{
				switch($child->node_type())
				{
					case XML_ELEMENT_NODE:
						if ($child->has_child_nodes()) rm_whitespace($child);
						break;

					case XML_TEXT_NODE:
						if (preg_match('/^\s+$/', $child->node_value())) $node->remove_child($child);
						break;
				}
			}
	}
}

/**
 * 取得頁數之 private 步進函式
 *
 * @param	XML_NODE	$item			題目節點
 * @param	integer		$page			頁數
 * @param	integer		$c				題數計數器
 * @param	integer		$item_per_page	每頁幾題
 *
 */
function _getPage(&$item, &$page, &$c, $item_per_page)
{
	$c++;
	$next = $item->next_sibling();
	while(!is_null($next) && $next->tagname() != 'item')
	{
		if (is_null($next) || $next->tagname() == 'section')
		{
			if ($c) $page++;
			$c = 0;
			break;
		}
		$next = $next->next_sibling();
	}

	if (is_null($next))
	{
		if ($c)
		{
			$page++;
			$c = 0;
		}
	}
	elseif ($c >= $item_per_page)
	{
		$page++;
		$c = 0;
	}
}

/**
 * 計算總共有幾頁
 *
 * @param	XML_DOCUMENT_NODE	$dom			XML_DOM 物件
 * @param	integer				$item_per_page	每頁幾題 (如果小於 1 則視為不分頁)
 * @return	integer								傳回幾頁
 */
function count_page($dom, $item_per_page)
{
	$items = $dom->get_elements_by_tagname('item');
	$first = true;
	$page  = 0;
	$c     = 0;

	if ($item_per_page < 2) return count($items);

	if (is_array($items) && count($items))
	{
		foreach($items as $item)
		{
			_getPage($item, $page, $c, $item_per_page);
		}
		return $page;
	}
	else
		return 1;
}

/**
 * 計算某一題在第幾頁
 *
 * @param	string				$item_id		題目 identifier
 * @param	XML_DOCUMENT_NODE	$dom			XML_DOM 物件
 * @param	integer				$item_per_page	每頁幾題 (如果小於 1 則視為不分頁)
 * @return	integer								傳回在第幾頁
 */
function which_page($item_id, $dom, $item_per_page)
{
	$items = $dom->get_elements_by_tagname('item');
	$first = true;
	$page  = 0;
	$c     = 0;

	if ($item_per_page < 2)
	{
		foreach($items as $i => $item) if ($item->get_attribute('ident') == $item_id) return $i+1;
		return 0;
	}

	if (is_array($items) && count($items))
	{
		foreach($items as $item)
		{
			if ($item->get_attribute('ident') == $item_id) return $page+1;

			_getPage($item, $page, $c, $item_per_page);
		}
	}

	return 0;
}


/**
 * 取得某一頁的第一題
 *
 * @param	integer				$page_no		頁碼
 * @param	XML_DOCUMENT_NODE	$dom			XML_DOM 物件
 * @param	integer				$item_per_page	每頁幾題 (如果小於 1 則視為不分頁)
 * @return	XML_ELEMENT_NODE					傳回第一個節點
 */
function find_first_item_of_page($page_no, $dom, $item_per_page)
{
	$items = $dom->get_elements_by_tagname('item');
	$first = true;
	$page  = 0;
	$c     = 0;

	if ($item_per_page < 2) return $items[$page_no-1];
	if ($page_no < 2)       return $items[0];

	if (is_array($items) && count($items))
	{
		foreach($items as $item)
		{
			if ($page == $page_no-1 && $c == 0) return $item;

			_getPage($item, $page, $c, $item_per_page);
		}
	}

	return null;
}

/**
 * 取得某一頁的最後一題
 *
 * @param	string				$first_item_id	第一題的 identifier
 * @param	XML_DOCUMENT_NODE	$dom			XML_DOM 物件
 * @param	integer				$item_per_page	每頁幾題 (如果小於 1 則視為不分頁)
 * @return	XML_ELEMENT_NODE					傳回最後一個節點
 */
function find_last_item_by_first_item($first_item_id, $dom, $item_per_page)
{
	$ctx = xpath_new_context($dom);
	$ret = $ctx->xpath_eval("//item[@ident='{$first_item_id}'] | //item[@ident='{$first_item_id}']/following-sibling::*[name()='item' or name()='section']");
	if ($ret)
	{
		$c = 0;
		$prev = $ret->nodeset[0];
		foreach($ret->nodeset as $node)
		{
			if ($node->tagname == 'item')
			{
				if (++$c >= $item_per_page)
				{
					return $node;
				}
			}
			elseif ($node->tagname == 'section')
			{
				return $prev;
			}
			$prev = $node;
		}
		return $prev;
	}
	return null;
}

/**
 * 取得某節點在某陣列節點所在位置的索引值
 *
 * @param	XML_ELEMENT_NODE	$node	節點
 * @param	array				$lists	節點陣列
 * @return	integer						傳回陣列索引
 */
function get_index(&$node, &$lists)
{
    if (PHP_VERSION >= '5') {
		foreach($lists as $k => $v) {
			if ($node->myDOMNode === $v->myDOMNode) {
				return $k;
			}
		}
	} else {
		foreach($lists as $k => $v) {
			if ($node === $v) return $k;
		}
	}
	return null;
}


/**
 * 取得誰該做某個測驗/作業/考試
 *
 * @param	int		$exam_id	測驗/作業/考試 ID
 * @param	string	$qti_which	{homework | exam | questionnaire}
 * @return  array				哪些人該做
 */
function get_who_should_attend($exam_id, $qti_which)
{
	if (!preg_match('/^(homework|exam|questionnaire)$/', $qti_which) || !preg_match('/^\d+$/', $exam_id))
		return false;

	$examinee_perm = array('homework' => 1700400200, 'exam' => 1600400200, 'questionnaire' => 1800300200);
	$GLOBALS['sysConn']->Execute('use ' . sysDBschool);

	// 檢查這是 for 課程還是 for 學校
	$cid = $GLOBALS['sysConn']->GetOne("select course_id from WM_qti_{$qti_which}_test where exam_id={$exam_id}");
	if ($cid > 10000000)
		$type = 'course';
	elseif ($cid > 1000000)
		$type = 'class';
	elseif ($cid > 10000)
		$type = 'school';
	else
		return false;

	// 檢查該參與的人是誰
	$acls = aclGetAclIdByInstance($examinee_perm[$qti_which], $cid, $exam_id);
	if (is_array($acls) && count($acls))
	{
		$people = array();
		foreach($acls as $acl) $people = array_merge($people, aclGetMembersByAcl($acl, $cid));
	}
	else
	{
		if ($type == 'course')
		{
				$people = $GLOBALS['sysConn']->GetCol('select username from WM_term_major where course_id=' . $cid . ' and role&' . $GLOBALS['sysRoles']['student']);
		}
		elseif ($type == 'class')
		{
				$people = $GLOBALS['sysConn']->GetCol('select username from WM_term_major where course_id=' . $cid . ' and role&' . $GLOBALS['sysRoles']['student']);
		}
		else
			return false;
	}

	return $people;
}

/**
 * 取得誰尚未進行某個測驗/作業/考試
 *
 * @param	int		$exam_id	測驗/作業/考試 ID
 * @param	string	$qti_which	{homework | exam | questionnaire}
 * @param	array	$people		該做的人 (用 get_who_should_attend() 取得)
 * @return  array				哪些人還沒做
 */
function get_who_not_yet_attend($exam_id, $qti_which, $people)
{

	if (is_array($people) && count($people))
	{
		$sqls = 'select distinct examinee from WM_qti_' . $qti_which . '_result where exam_id=' . $exam_id . ' and examinee in ("' . implode('","', $people) . '")';
		$examinee = $GLOBALS['sysConn']->GetCol($sqls);
		if (is_array($examinee) && count($examinee))
		{
			return array_diff($people, $examinee);
		}
	}

	return array();
}

?>
