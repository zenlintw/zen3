<?php
/**
 * �ը�½���B���D���Ψ禡
 *
 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
 *
 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
 *
 * @package     WM3
 * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
 * @copyright   2000-2005 SunNet Tech. INC.
 * @version     CVS: $Id: qti_util.php,v 1.1 2010/02/24 02:39:07 saly Exp $
 * @link        http://demo.learn.com.tw/1000110138/index.html
 * @since       2005-12-07
 */

/**
 * �h���ťդ�r�`�I
 *
 * @param XML_ELEMENT_NODE	$node	�q�`�O DOM ���� documentElement ����
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
 * ���o���Ƥ� private �B�i�禡
 *
 * @param	XML_NODE	$item			�D�ظ`�I
 * @param	integer		$page			����
 * @param	integer		$c				�D�ƭp�ƾ�
 * @param	integer		$item_per_page	�C���X�D
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
 * �p���`�@���X��
 *
 * @param	XML_DOCUMENT_NODE	$dom			XML_DOM ����
 * @param	integer				$item_per_page	�C���X�D (�p�G�p�� 1 �h����������)
 * @return	integer								�Ǧ^�X��
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
 * �p��Y�@�D�b�ĴX��
 *
 * @param	string				$item_id		�D�� identifier
 * @param	XML_DOCUMENT_NODE	$dom			XML_DOM ����
 * @param	integer				$item_per_page	�C���X�D (�p�G�p�� 1 �h����������)
 * @return	integer								�Ǧ^�b�ĴX��
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
 * ���o�Y�@�����Ĥ@�D
 *
 * @param	integer				$page_no		���X
 * @param	XML_DOCUMENT_NODE	$dom			XML_DOM ����
 * @param	integer				$item_per_page	�C���X�D (�p�G�p�� 1 �h����������)
 * @return	XML_ELEMENT_NODE					�Ǧ^�Ĥ@�Ӹ`�I
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
 * ���o�Y�@�����̫�@�D
 *
 * @param	string				$first_item_id	�Ĥ@�D�� identifier
 * @param	XML_DOCUMENT_NODE	$dom			XML_DOM ����
 * @param	integer				$item_per_page	�C���X�D (�p�G�p�� 1 �h����������)
 * @return	XML_ELEMENT_NODE					�Ǧ^�̫�@�Ӹ`�I
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
 * ���o�Y�`�I�b�Y�}�C�`�I�Ҧb��m�����ޭ�
 *
 * @param	XML_ELEMENT_NODE	$node	�`�I
 * @param	array				$lists	�`�I�}�C
 * @return	integer						�Ǧ^�}�C����
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
 * ���o�ָӰ��Y�Ӵ���/�@�~/�Ҹ�
 *
 * @param	int		$exam_id	����/�@�~/�Ҹ� ID
 * @param	string	$qti_which	{homework | exam | questionnaire}
 * @return  array				���ǤH�Ӱ�
 */
function get_who_should_attend($exam_id, $qti_which)
{
	if (!preg_match('/^(homework|exam|questionnaire)$/', $qti_which) || !preg_match('/^\d+$/', $exam_id))
		return false;

	$examinee_perm = array('homework' => 1700400200, 'exam' => 1600400200, 'questionnaire' => 1800300200);
	$GLOBALS['sysConn']->Execute('use ' . sysDBschool);

	// �ˬd�o�O for �ҵ{�٬O for �Ǯ�
	$cid = $GLOBALS['sysConn']->GetOne("select course_id from WM_qti_{$qti_which}_test where exam_id={$exam_id}");
	if ($cid > 10000000)
		$type = 'course';
	elseif ($cid > 1000000)
		$type = 'class';
	elseif ($cid > 10000)
		$type = 'school';
	else
		return false;

	// �ˬd�ӰѻP���H�O��
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
 * ���o�֩|���i��Y�Ӵ���/�@�~/�Ҹ�
 *
 * @param	int		$exam_id	����/�@�~/�Ҹ� ID
 * @param	string	$qti_which	{homework | exam | questionnaire}
 * @param	array	$people		�Ӱ����H (�� get_who_should_attend() ���o)
 * @return  array				���ǤH�٨S��
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
