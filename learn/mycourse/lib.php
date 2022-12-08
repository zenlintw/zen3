<?php
	/**
	 * �@�Ψ��
	 *
	 * @since   2003/06/06
	 * @author  ShenTing Lin
	 * @version $Id: lib.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	// �]�w�C����ܴX�����
	$lines = 10;

	// �ڪ��ҵ{���t�Υ\��s��
	$sys_func_id = array(
			10010101,   /* �ڪ��Ы� */
			10010102,   /* �ڪ��줽�� */
			10010103,   /* ���սҵ{ */
			10010104,   /* �ڪ��̷R */
			10010105,   /* ��ҲM�� */
			10010106,   /* ��ҲM�浲�G */
		);

	/**
	 * �ˬd�ڪ��ҵ{���]�w�ɦs���s�b
	 * @param
	 * @return
	 **/
	function chkSetting() {
		global $sysSession;

		$path = MakeUserDir($sysSession->username);
		$filename = $path . '/my_course_setting.xml';
		// �ˬd�]�w�ɦs���s�b
		if (!file_exists($filename)) {
			$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' .
			           '<manifest>' .
			           '<favorite>false</favorite>' .
			           '<group_id>10000000</group_id>' .
			           '<page_no>1</page_no>' .
			           '</manifest>';
			// �g�^�ɮפ�
			touch($filename);
			if ($fp = fopen($filename, 'w')) {
				@fwrite($fp, $xmlstr);
			}
			fclose($fp);
		}
	}

	/**
	 * �ˬd��ҲM��s���s�b
	 * @return string $filename : ��ҲM��s�񪺸��|
	 **/
	function setElevtive($isRes = false) {
		global $sysSession;
		$path = MakeUserDir($sysSession->username);
		$filename = $path . ($isRes ? '/my_course_elective_result.xml' : '/my_course_elective.xml');

		if (!file_exists($filename)) {
			$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>';
			$xmlstr .= '<manifest></manifest>';
			// �g�^�ɮפ�
			touch($filename);
			if ($fp = fopen($filename, 'w')) {
				@fwrite($fp, $xmlstr);
			}
			fclose($fp);
		}
		return $filename;
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

		foreach($child as $value) {
			if ($value <= 0) continue;
			//echo $value . '<br />';
			if (!array_key_exists($value, $group)) {
				$result .= $without_course ? '' : '<course id="' . $value . '"></course>';
			} else {
				$lang = getCaption($group_name[$value]);

				$result .= '<courses id="'    . $value               . '">'             .
				           '<title default="' . strtolower($sysSession->lang)    . '">'             .
				           '<big5>'           . $lang['Big5']        . '</big5>'        .
				           '<gb2312>'         . $lang['GB2312']      . '</gb2312>'      .
				           '<en>'             . $lang['en']          . '</en>'          .
				           '<euc-jp>'         . $lang['EUC-JP']      . '</euc-jp>'      .
				           '<user-define>'    . $lang['user_define'] . '</user-define>' .
				           '</title>'         .
				           Group2XML($group, $group_name, $value) .
				           '</courses>';
			}
		}
		return $result;
	}


	/**
	 * ���o�Ҧ��s�ժ� XML �𪬵��c
	 *
	 * @return  xml             �s�վ�XML
	 */
	function getAllGroupsXml()
	{
	    global $sysSession;
	
		$group = array();
		$group_name = array();
		// �q��Ʈw�����o���
		$RS = dbGetStMr('`WM_term_group`', '*', '1 order by `parent`, `permute`', ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$group[$RS->fields['parent']][$RS->fields['permute']] = $RS->fields['child'];
				$RS->MoveNext();
			}
		}

		$keys = array_keys($group);
		$RS = dbGetStMr('WM_term_course', 'course_id, caption', 'course_id in (' . implode(',', $keys) . ') AND status<9', ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$group_name[$RS->fields['course_id']] = $RS->fields['caption'];
				$RS->MoveNext();
			}
		}

		// �إ� xml �ɮ�
		$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		// $xmlstr .= '<manifest>';
		$xmlstr .= '<manifest id="' . $sysSession->cur_func . '">' .
				   Group2XML($group, $group_name, 10000000, true) .
				   '</manifest>';
		return $xmlstr;
	}


	/**
	 * ���o�i�ҵ{�Ыǡj�Ρi�ҵ{�줽�ǡj���ҵ{�s��
	 *
	 * @param   bool    $isTA   �O�_���i�ҵ{�줽�ǡj�H�_�h���i�ҵ{�Ыǡj
	 * @return  xml             �s�վ�XML
	 */
	function getSpecificGroupsXml($isTA=false)
	{
	    global $sysSession, $sysRoles;

		$group = array();
		$group_name = array();
		// �q��Ʈw�����o���
		$role = $isTA ? ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) :
						($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'] | $sysRoles['student'] | $sysRoles['auditor']);
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

		return $xmlstr;
	}

	/**
	 * ���o�ҵ{�s��
	 *     1. ���o�]�w�����
	 *            xml
	 *            ��Ʈw
	 *     2. �̷ӧڪ��ҵ{�B�ڪ��б½ҵ{�P���սҵ{�����P�^�Ǥ��P�����
	 * @return
	 **/
	function getCourseGroup() {
		global $sysSession, $sysConn, $sys_func_id, $sysRoles;

		$filename = sysDocumentRoot . "/base/{$sysSession->school_id}/system/course_group.xml";
		$xmlstr = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '><manifest></manifest>';

		// ����ڪ��̷R�h
		// echo $sysSession->cur_func;
		if ($sysSession->cur_func == $sys_func_id[3]) return getFavorite();

/* ½�M�Ҧ��{���A�䤣��|�s course_group.xml �ɪ��a��A�쩳����ɭԷ|�ͥX course_group.xml �o��XML���o���Ū�H

		// �q�ɮפ����o�ҵ{�s�ժ��]�w (Begin)
		$infile = false;
		if (file_exists($filename)) {
			$xml    = file($filename);
			$tmpstr = implode('', $xml);
			$tmp    = trim($tmpstr);
			if (!empty($tmp)) {
				$xmlstr = $tmpstr;
				$infile = true;
			}
		}
		// �q�ɮפ����o�ҵ{�s�ժ��]�w (End)
*/

		// �z�沈�n����� (Begin)
		if (!in_array($sysSession->cur_func, $sys_func_id)) {
			$sysSession->cur_func = $sys_func_id[0];
			dbSet('WM_session', "cur_func='{$sysSession->cur_func}'", "idx='{$_COOKIE['idx']}'");
		}

		switch ($sysSession->cur_func) {
			case $sys_func_id[0] :    /* �ڪ��Ы� */
			    return getSpecificGroupsXml();
				break;

			case $sys_func_id[1] :    /* �ڪ��줽�� */
			    return getSpecificGroupsXml(true);
				break;

			case $sys_func_id[2] :    /* ���սҵ{ */
				/* ���C�A�ҥH���ݭn�z�� */
			    return getAllGroupsXml();
				break;

			default :
				$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n" .
						   '<manifest></manifest>';
		}
		// �z�沈�n����� (End)

		return $xmlstr;
	}

	/**
	 * ���o�T�����ߪ��ؿ��]�w��
	 * @return string �T�����ߪ� XML �]�w��
	 **/
	function getFavorite() {
		global $sysSession, $sysConn;
		$content = '';

		$path = MakeUserDir($sysSession->username);
		$filename = $path . '/my_course_favorite.xml';

		if (!file_exists($filename)) {
			$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' .
					   '<manifest>' .
					   '<setting></setting>' .
					   '</manifest>';
			// �g�^�ɮפ�
			touch($filename);
			if ($fp = fopen($filename, 'w')) {
				@fwrite($fp, $xmlstr);
			}
			fclose($fp);
		} else {
			$xml = file($filename);
			$xmlstr = implode('', $xml);
		}

		return $xmlstr;
	}

	/**
	 * �x�s�]�w�Ȩ� XML ��
	 * @param string $nodeName  : TAG ���W��
	 * @param string $nodeValue : �n�x�s�����s
	 * @return boolean
	 *     true  : ���\
	 *     false : ����
	 **/
	function saveSetting($nodeName, $nodeValue) {
		global $sysSession;

		$path = MakeUserDir($sysSession->username);
		$filename = $path . '/my_course_setting.xml';
		if (!file_exists($filename)) return false;

		if (!$xmlvars = @domxml_open_file($filename)) {
			@unlink($filename);
			chkSetting();
			if (!$xmlvars = @domxml_open_file($filename)) {
				return false;
			}
		}

		$xpath = '/manifest/' . $nodeName;
		$ctx   = xpath_new_context($xmlvars);
		$nodes = xpath_eval($ctx, $xpath);

		if (count($nodes->nodeset) > 0) {
			// �����¸`�I
			$node = $nodes->nodeset[0];
			$parent = $node->parent_node();
			$parent->remove_child($node);
		}

		$foo = xpath_eval($ctx, '/manifest');
		$node = $foo->nodeset[0];
		$new_node = $xmlvars->create_element($nodeName);
		$new_text = $xmlvars->create_text_node($nodeValue);
		$new_node->append_child($new_text);
		$node->append_child($new_node);

		$xmlvars->dump_file($filename);
		return true;
	}

	/**
	 * ���o�]�w�Ȥ����]�w
	 * @param string $nodeName : �n���Ȫ� TAG
	 * @return �� TAG ����
	 **/
	function getSetting($nodeName) {
		global $sysSession, $sysConn;
		$nodeValue = '';

		$path = MakeUserDir($sysSession->username);
		$filename = $path . '/my_course_setting.xml';
		if (!file_exists($filename)) return false;

		$xml = file($filename);
		$content = implode('', $xml);
		if (!$xmlvars = @domxml_open_mem($content)) {
			return false;
		}

		$xpath = '/manifest/' . $nodeName;
		$ctx   = xpath_new_context($xmlvars);
		$nodes = xpath_eval($ctx, $xpath);
		if (count($nodes->nodeset) > 0) {
			$node = $nodes->nodeset[0];
			if ($node->has_child_nodes()) {
				$child = $node->first_child();
				$nodeValue = $child->node_value();
				return $nodeValue;
			}
		}

		return false;
	}

	if (!function_exists('divMsg')) {
		/**
		 * �B�z��ơA�L������������
		 * @param integer $width   : �n��ܪ��e��
		 * @param string  $caption : ��ܪ���r
		 * @param string  $title   : �B�ʪ����ܤ�r�A�Y�S���]�w�A�h�� $caption �̼�
		 * @return string : �B�z�᪺��r
		 **/
		function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
			if (empty($title)) $title = $caption;
			$wd = is_numeric($width) ? intval($width) . 'px' : $width;
			return $without_title ? ('<div style="width: ' . $wd . 'px; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $wd . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
		}
	}

	if (!function_exists('getReviewSerial')) {
		/**
		 * ���o�o���ҳ]�w���f�ֳ]�w
		 * @param int id : �ҵ{�N��
		 * @return int �f�֧Ǹ�
		 **/
		function getReviewSerial($id) {
		    return dbGetOne('WM_review_sysidx', 'flow_serial', "discren_id = {$id}");
		}
	}


	if (!function_exists('getReviewRuleList')) {
		/**
		 * ���o�ثe�t�γ]�w���f�ֳW�h
		 * @param int id : �ҵ{�N��
		 * @return array �f�ֳW�h�C��
		 **/
		function getReviewRuleList($id) {
			global $sysConn, $sysSession;
			$syscont = array();
			$RS = dbGetStMr('WM_review_syscont', '*', 'order by permute ASC', ADODB_FETCH_ASSOC);
			if ($RS) {
				if ($RS->RecordCount() != 0) {
					while ($RS1 = $RS->FetchRow())
					{
						$tlt_lang = unserialize($RS1['title']);
						$syscont[$RS1['flow_serial']] = $tlt_lang[$sysSession->lang];
					}
				}
			}
			return $syscont;
		}
	}
?>
