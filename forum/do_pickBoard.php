<?php
	/**
	 * �^�ǿz��᪺�ҵ{�s��( ���� /learn/mycourse/do_function.php �H�� lib.php )
	 *
	 * @since   2004/09/09
	 * @author  KuoYang Tsao
	 * @version $Id: do_pickBoard.php,v 1.1 2010/02/24 02:38:59 saly Exp $
	 * @copyright 2004 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lang/forum.php');

	$sysSession->cur_func = '700300400';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

//---------------------------------------------
// �禡�}�l
//---------------------------------------------

	function escape_xml_content($str)
	{
		return htmlspecialchars(mb_convert_encoding(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]+/', ' ', trim(stripslashes($str))), 'UTF-8', 'UTF-8'));
	}

	/**
	 * Group2XML()
	 * @param  Array  $group      : �ҵ{�s�ժ��}�C
	 * @param  Array  $group_name : �ҵ{�s�ժ��W��
	 * @param  string $group_id   : �s�ժ��s��
	 * @return string $result     : xml �榡���s�ո��
	 **/
	function Group2XML($group, $group_name, $group_id, $without_course=false) {
		global $sysSession;
		$result = '';

		if (!is_array($group) || !is_array($group[$group_id])) return $result;
		$child = $group[$group_id];
		ksort($child);
		reset($child);

		$locale = str_replace('_', '-', strtolower($sysSession->lang));
		foreach($child as $value) {
			if ($value <= 0) continue;
			//echo $value . '<br />';
			if (!array_key_exists($value, $group)) {
				$result .= $without_course ? '' : '<course id="' . $value . '"></course>';
			} else {
				$lang = getCaption($group_name[$value]);

				$result .= '<courses id="' . $value . '">';
				$result .= '<title default="' . $sysSession->lang . '">';
				$result .= "<{$locale}>" . $lang[$sysSession->lang] . "</{$locale}>"; // �u��ܤ��s��A���N�q�ثe�y�t�Y�i�A���������q
				$result .= '</title>';
				$result .= Group2XML($group, $group_name, $value, $without_course);
				$result .= '</courses>';
			}
		}
		return $result;
	}

	/**
	 * ���o�ҵ{�s��
	 *     1. ���o�]�w�����
	 *            xml
	 *            ��Ʈw
	 *     2. �̷ӧڪ��б½ҵ{�^�Ǹ��
	 * @return
	 **/
	function getCourseGroup() {
		global $sysSession, $sysConn, $sysRoles, $MSG;

		$xmlstr = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '><manifest></manifest>';

		$group = array();
		$group_name = array();
		// �q��Ʈw�����o���
		$role = ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);
		// �̨ΤƷj�M���нҵ{�Ω��ݸs�դ��𪬵��c begin
		// ���o�ثe user �ҥ��Ъ��ҵ{
		$latters = dbGetCol('WM_term_major AS M, WM_term_course AS C, WM_term_group AS G',
							'DISTINCT G.parent',
							"M.username = '{$sysSession->username}'
							AND M.role & {$role}
							AND M.course_id = C.course_id
							AND C.status <9
							AND M.course_id = G.child
							order by G.parent, G.permute");
		$keys = $latters;
		foreach ($latters as $g) $group[$g] = array(); // �̥��ݪ��s�խn�]���}�C (�}�C�����ӬO���нҵ{�A���]�S�Ψ�A�ҥH�����C)

		// �q�ҵ{���W�j�M�s�աA���� parent �s�լO 10000000 ����
		while (count($latters) && (
				$rs = dbGetStMr('WM_term_group',
								'parent,child',
								'child in (' . implode(',', $latters) . ') order by `parent`, `permute`',
								ADODB_FETCH_NUM))
			  )
		{
			$latters = array();
			while (list($parent,$child) = $rs->FetchRow())
			{
				$group[$parent][] = $child;
				if (!in_array($parent, $keys))
				{
					$latters[] = $parent;
					$keys[] = $parent;
				}
			}
			$latters = array_unique($latters);
		}
		// �̨ΤƷj�M���нҵ{�Ω��ݸs�դ��𪬵��c end

		$keys = array_unique($keys);
		$RS = dbGetStMr('WM_term_course', 'course_id, caption', 'course_id in (' . implode(',', $keys) . ') AND status<9', ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$group_name[$RS->fields['course_id']] = $RS->fields['caption'];
				$RS->MoveNext();
			}
		}

		// �إ� xml �ɮ�
		$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		$xmlstr .= '<manifest>';
		$xmlstr .= Group2XML($group, $group_name, 10000000, true);
		$xmlstr .= '</manifest>';

		// �����սҵ{
		$locale = str_replace('_', '-', strtolower($sysSession->lang));
		$undiv = '<courses id="99999999">' .
				 '   <title default="' . $sysSession->lang . '">' .
				 // �u��ܤ��s��A���N�q�ثe�y�t�Y�i�A���������q
				 "      <{$locale}>" . escape_xml_content($MSG['un_div_course'][$sysSession->lang]) . "</{$locale}>" .
				 '   </title>' .
				 '</courses>';

		return str_replace('</manifest>', $undiv . '</manifest>' , $xmlstr);
	}

	/**
	 * ���o�s�դ��ҵ{
	 *     1. ���o�]�w�����
	 *            xml
	 *            ��Ʈw
	 *     2. �̷ӧڪ��б½ҵ{�^�Ǹ��
	 * @return
	 **/
	function getCourse($group_id) {
		global $sysSession, $sysRoles, $sysConn;

		if ($group_id < 10000000 || $group_id > 99999999) return '<manifest />';

		$rtnAry  = array();
		if ($group_id == 99999999)  // �����սҵ{
		{
			$courses = $sysConn->GetAssoc('select B.course_id, B.caption ' .
										  'from WM_term_course as B ' .
										  'left join WM_term_group as G ' .
										  'on B.course_id=G.child ' .
										  'where G.child is NULL and B.kind="course" and B.status != 9');
		}
		else
		{
			$courses = getAllCourseInGroup($group_id);  // �Y�s�դU�Ҧ��ҵ{
		}
		$rs      = dbGetCourses('C.course_id', $sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']); // �Y�H�бª��ҵ{
		if ($rs) while ($row = $rs->FetchRow())
		{
			if ($courses[$row['course_id']]) $rtnAry[$row['course_id']] = $courses[$row['course_id']];
		}


		$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n" .
				   '<manifest><group_id>' . $group_id . '</group_id>';
		$locale = str_replace('_', '-', strtolower($sysSession->lang));
		foreach($rtnAry as $cid => $caption)
		{
			$lang = getCaption($caption);
			$xmlstr .= '<courses id="'    . $cid . '">' .
					   '<title default="' . $sysSession->lang        . '">'.
					   // �u��ܤ��s��A���N�q�ثe�y�t�Y�i�A���������q
					   "        <{$locale}>" . $lang[$sysSession->lang] . "</{$locale}>" .
					   '</title></courses>';
		}

		return $xmlstr . '</manifest>';
	}

	/**
	 * ���o�ҵ{�Ҧ��Q�ת�
	 *     1. ���o�]�w�����
	 *            xml
	 *            ��Ʈw
	 *     2. �̷ӧڪ��б½ҵ{�^�Ǹ��
	 * @return
	 **/
	function getCourseBoard($course_id) {
		global $sysSession, $sysConn, $Sqls;

		$tab     = 'WM_term_subject left join WM_bbs_boards on WM_bbs_boards.board_id = WM_term_subject.board_id';
		$fields  = 'distinct `WM_term_subject`.`board_id`, `permute`, `bname`';
		$where   = "`course_id`={$course_id} order by `permute`";
		$RS      = dbGetStMr($tab, $fields, $where, ADODB_FETCH_ASSOC);
		$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		$xmlstr .= '<manifest>';
		// $xmlstr .= '<sqls>'.$sqls.'</sqls>';
		$locale = str_replace('_', '-', strtolower($sysSession->lang));
		while (!$RS->EOF) {
				$lang = getCaption($RS->fields['bname']);
				$xmlstr .= '<boards id="'     . $RS->fields['board_id'] . '">' .
						   '<title default="' . $sysSession->lang       . '">' .
						   // �u��ܤ��s��A���N�q�ثe�y�t�Y�i�A���������q
						   "<{$locale}>" . $lang[$sysSession->lang] . "</{$locale}>" .
						   '</title>' .
						   '</boards>';
			$RS->MoveNext();
		}
		$xmlstr .= '</manifest>';
		return $xmlstr;
	}
//---------------------------------------------
// �禡����
//---------------------------------------------


//---------------------------------------------
// �D�{���}�l
//---------------------------------------------

	//echo $GLOBALS['HTTP_RAW_POST_DATA'];
	//die();
	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
			exit;
		}

		// �ˬd Ticket
		$ticket = md5(sysTicketSeed . $sysSession->username . 'pickboard' . $sysSession->board_id);
		if (getNodeValue($dom, 'ticket') != $ticket) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n";
			echo '<manifest>Access Fail.</manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], '�ڵ��s��!');
			exit;
		}

		$action = getNodeValue($dom, 'action');
		$result = '';
		switch ($action) {
			case 'list_group' :   // �^�ǿz��L���ҵ{�s��
				$result = getCourseGroup();
				break;

			case 'group'      :   // �^�ǽҵ{�s�դ����Ҧ��ҵ{
				$group_id = getNodeValue($dom, 'group_id');
                if (!empty($group_id) && !is_numeric($group_id)) {
                    header('HTTP/1.1 403 Forbidden');
                    exit;
                }
				$result   = getCourse($group_id);
				break;

			case 'list_board' :   // �^�ǽҵ{�Ҧ��Q�ת�
				$course_id = getNodeValue($dom, 'course_id');
                // XSS���@
                if (!empty($course_id) && !is_numeric($course_id)) {
                    header('HTTP/1.1 403 Forbidden');
                    exit;
                }
				$result    = getCourseBoard($course_id);
				break;
		}

		if (!empty($result)) {
			header("Content-type: text/xml");
			$result = str_replace('<manifest>', "<manifest><ticket>{$ticket}</ticket>", $result);
			echo $result;
		} else {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n";
			echo '<manifest></manifest>';
		}
	}

?>
