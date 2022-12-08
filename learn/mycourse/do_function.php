<?php
	/**
	 * �^�ǿz��᪺�ҵ{�s��
	 *
	 * @since   2003/06/06
	 * @author  ShenTing Lin
	 * @version $Id: do_function.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/learn/mycourse/lib.php');
	require_once(sysDocumentRoot . '/academic/review/review_lib.php');
	require_once(sysDocumentRoot . '/learn/review/review_init.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lang/review.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	// $sysSession->cur_func='700300200';
	// $sysSession->restore();
	if (!aclVerifyPermission(700300200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	/**
	 * ��P�ǭ�����
	 *
	 * @param   string      $username       �b��
	 * @param   int         $course_id      �ҵ{�N��
	 * @param   int         $permission     �����v��
	 */
	function assignLearnerPermission($username, $course_id, $permission)
	{
		global $sysConn, $sysRoles;
		static $mask;   // �O�d�Юv(�U�СB���v)�����A�òM���ǥ�(�����B��ť)����

		if (!isset($mask)) $mask = $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'];

		dbNew('WM_term_major', '`username`, `course_id`, `role`, `add_time`', "'{$username}', {$course_id}, {$permission}, NOW()");
		if ($sysConn->ErrorNo() == 1062) {
		    dbSet('WM_term_major', 'role=role&' . $mask . '|' . $permission , "username='{$username}' and course_id={$course_id}");
		}
	}

	/**
	 * �إ߽ҵ{�� XML ��
	 *     @pram $val (�ҵ{�s��, �ҵ{�W��, ���W��� (�}�l), ���W��� (����), �W�Ҥ�� (�}�l), �W�Ҥ�� (����), �w�ϥΪŶ�)
	 **/
	function buildCourseXML($val) {
		global $sysSession, $sysConn, $Sqls;

		if (!is_array($val)) return '';
		$result = '';
		// ��X�ҵ{�� XML (Begin)
		// �ҵ{�W��
		$lang = getCaption($val['caption']);
		// �Ч��W��
		$content_id = intval($val['content_id']);
		$RS = dbGetStSr('WM_content', 'caption', "content_id={$content_id}", ADODB_FETCH_ASSOC);
		if (!($RS === false)) {
			$content = getCaption($RS['caption']);
		}
		// ���W���
		$en_begin = $val['en_begin'];
		$en_end   = $val['en_end'];
		// �W�Ҥ��
		$st_begin = $val['st_begin'];
		$st_end   = $val['st_end'];
		// �w�ϥΪŶ�
		$val['quota_used']  = intval($val['quota_used']);
		$val['quota_limit'] = intval($val['quota_limit']);
		if (empty($val['quota_limit'])) $val['quota_limit'] = 1;
		$quota_used = round($val['quota_used'] / $val['quota_limit'], 4) * 100;
		// �ή榨�Z
		$val['fair_grade'] = intval($val['fair_grade']);

		// �f�ֳW�h
		$review = dbGetOne('WM_review_syscont as C join WM_review_sysidx as I on C.flow_serial = I.flow_serial', 'C.title', 'I.discren_id =' . $val['course_id']);
		if (!empty($review))
		{
			$review_title = getCaption($review);
			$review_title = $review_title[$sysSession->lang];
		}
		else
		{
			$review_title = '';
		}

		// ���X�Ҧb���ҵ{�s��
		$group = array();
		$sqls = str_replace('%COURSE_ID%', $val['course_id'], $Sqls['get_course_in_group']);
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sqls);
		while (!$RS->EOF) {
			$title = getCaption($RS->fields['caption']);
			$caption = trim($title[$sysSession->lang]);
			if (!empty($caption)) $group[] = $caption;
			$RS->MoveNext();
		}
		$group_list     = htmlspecialchars(implode(', ', $group), ENT_NOQUOTES, 'UTF-8');
		$val['texts']   = htmlspecialchars($val['texts'], ENT_NOQUOTES, 'UTF-8');
		$val['url']     = htmlspecialchars($val['url']);
		$val['content'] = htmlspecialchars($val['content'], ENT_NOQUOTES, 'UTF-8');
		$val['teacher'] = htmlspecialchars($val['teacher']);
		// ��X XML
		$result .= <<< BOF

	<course id="{$val['course_id']}">
		<title>
			<big5>{$lang['Big5']}</big5>
			<gb2312>{$lang['GB2312']}</gb2312>
			<en>{$lang['en']}</en>
			<euc_jp>{$lang['EUC-JP']}</euc_jp>
			<user_define>{$lang['user_define']}</user_define>
		</title>
		<teacher>{$val['teacher']}</teacher>
		<content_name>
			<title>
				<big5>{$content['Big5']}</big5>
				<gb2312>{$content['GB2312']}</gb2312>
				<en>{$content['en']}</en>
				<euc_jp>{$content['EUC-JP']}</euc_jp>
				<user_define>{$content['user_define']}</user_define>
			</title>
		</content_name>
		<enroll_begin>{$en_begin}</enroll_begin>
		<enroll_end>{$en_end}</enroll_end>
		<study_begin>{$st_begin}</study_begin>
		<study_end>{$st_end}</study_end>
		<status>{$val['status']}</status>
		<review>{$review_title}</review>
		<group>{$group_list}</group>
		<texts>{$val['texts']}</texts>
		<url>&lt;a href="{$val['url']}" target="_blank" class="cssAnchor"&gt;{$val['url']}&lt;/a&gt;</url>
		<content>{$val['content']}</content>
		<credit>{$val['credit']}</credit>
		<n_limit>{$val['n_limit']}</n_limit>
		<a_limit>{$val['a_limit']}</a_limit>
		<quota_used_percent>{$quota_used}</quota_used_percent>
		<quota_used>{$val['quota_used']}</quota_used>
		<quota_limit>{$val['quota_limit']}</quota_limit>
		<fair_grade>{$val['fair_grade']}</fair_grade>
	</course>
BOF;
		// ��X�ҵ{�� XML (End)
		return $result;
	}

	function getCourseDetail($csid) {
		$RS  = dbGetStSr('WM_term_course', '*', "`course_id`={$csid}", ADODB_FETCH_ASSOC);
		$res = buildCourseXML($RS);
		$res = '<manifest>' . $res . '</manifest>';
		$res = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n" . $res;
		return $res;
	}

	/**
	 * �x�s�s�׫᪺�ڪ��̷R����Ƨ�
	 * @param object $xmldoc : ��ӭn�x�s�� XML �]�w��
	 * @return string �� XML �]�˰_�Ӫ��T���]�t�F���\�Υ��Ѫ��T��
	 **/
	function saveFolder($xmldoc) {
		global $sysSession, $sysConn;
		$content = '';

		// �M�� ticket Node
		$nodes = $xmldoc->get_elements_by_tagname('ticket');
		$cnt = count($nodes);
		for ($i = 0; $i < $cnt; $i++) {
			$node = $nodes[$i]->parent_node();
			$node->remove_child($nodes[$i]);
		}
		// �M�� action Node
		$nodes = $xmldoc->get_elements_by_tagname('action');
		$cnt = count($nodes);
		for ($i = 0; $i < $cnt; $i++) {
			$node = $nodes[$i]->parent_node();
			$node->remove_child($nodes[$i]);
		}
		// ��Ƨ����ʧ@ (Begin)
		$nodes = $xmldoc->get_elements_by_tagname('courses');
		$cnt = count($nodes);
		$newID = array();
		$attr = '';

		// �s�W��Ƨ�
		for ($i = 0; $i < $cnt; $i++) {
			$attr = $nodes[$i]->get_attribute('id');
			if (empty($attr) || in_array($attr, $newID)) {
				$attr = uniqid('USER_');
				$nodes[$i]->set_attribute('id', $attr);
			}
			$newID[] = $attr;
		}

		// ���o�쥻�����
		$content = getFavorite();

		$path = MakeUserDir($sysSession->username);
		$filename = $path . '/my_course_favorite.xml';

		$xmlstr = $xmldoc->dump_mem(true);

		// �g�^�ɮפ� (Begin)
		touch($filename);
		if ($fp = fopen($filename, 'w')) {
			@fwrite($fp, $xmlstr);
			fclose($fp);
		}
		// �g�^�ɮפ� (End)

		$res = ($content != $xmlstr) ? '1' : '0';
		$xmlstr = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n" .
			'<manifest><result>' . $res . '</result></manifest>';

		return $xmlstr;
	}

	/**
	 * �N�ҵ{�[��ڪ��̷R��
	 * @param string $csid : �ҵ{�s��
	 * @return string �� XML �]�˰_�Ӫ��T���]�t�F���\�Υ��Ѫ��T��
	 **/
	function add_favorite($csid) {
		global $sysSession, $sysConn;

		$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";

		getFavorite();
		$path = MakeUserDir($sysSession->username);
		$filename = $path . '/my_course_favorite.xml';
		if (!$xmlvars = domxml_open_file($filename)) {
			$res .= '<manifest><result>1</result></manifest>';
			return $res;
		}

		$cnt = checkInFolder($xmlvars, '', $csid);
		if ($cnt > 0) {
			$res .= '<manifest><result>2</result></manifest>';
			return $res;
		}

		$root = $xmlvars->document_element();
		$node = $xmlvars->create_element('course');
		$node->set_attribute('id', $csid);
		$root->append_child($node);
		$xmlvars->dump_file($filename);

		$res .= '<manifest><result>0</result></manifest>';
		return $res;
	}

	/**
	 * �ˬd�o�ӽҵ{�O�_�w�g�b�o�Ӹ�Ƨ���
	 * @param
	 * @return
	 **/
	function checkInFolder($xmldocs, $gid, $csid) {
		$ctx = xpath_new_context($xmldocs);
		if (empty($gid)) {
			$xpath = '/manifest/course[@id="' . $csid . '"]';
		} else {
			$xpath = '//courses[@id="' . $gid . '"]/course[@id="' . $csid . '"]';
		}
		$nodes = xpath_eval($ctx, $xpath);
		return count($nodes->nodeset);
	}

	/**
	 * ���ݩηh���ҵ{
	 * @param
	 * @return
	 **/
	function moveCourse($act, $gid, $cid) {
		global $sysSession, $sysConn;

		$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";

		getFavorite();
		$path = MakeUserDir($sysSession->username);
		$filename = $path . '/my_course_favorite.xml';
		if (empty($gid) || empty($cid) || (!$xmlvars = domxml_open_file($filename))) {
			$res .= '<manifest><result>1</result></manifest>';
			return $res;
		}

		$ctx = xpath_new_context($xmlvars);
		$xpath = (intval($gid) == 10000000) ? '//manifest' : '//courses[@id="' . $gid . '"]';
		$nodes = xpath_eval($ctx, $xpath);
		if (count($nodes->nodeset) <= 0) {
			$res .= '<manifest><result>2</result></manifest>';
			return $res;
		}
		$target = $nodes->nodeset[0];

		$sgid = getSetting('group_id');
		$csid = explode(',', $cid);
		$cnt = count($csid);
		$hasSameCourse = false;
		for ($i = 0; $i < $cnt; $i++) {
			if ($sgid == '10000000') {
				$xpath = '/manifest/course[@id="' . intval($csid[$i]) . '"]';
			} else {
				$xpath = '//courses[@id="' . $sgid . '"]/child::course[@id="' . intval($csid[$i]) . '"]';
			}
			$nodes = xpath_eval($ctx, $xpath);
			if (count($nodes->nodeset) <= 0) continue;
			$source = $nodes->nodeset[0];
			$count = checkInFolder($xmlvars, $gid, $csid[$i]);
			if ($count > 0) {
				$hasSameCourse = true;
				continue;
			}
			$target->append_child($source->clone_node(true));
			if ($act == 'move') {
				$parent = $source->parent_node();
				$parent->remove_child($source);
			}
		}
		$xmlvars->dump_file($filename);
		if ($hasSameCourse) {
			$res .= '<manifest><result>-1</result></manifest>';
		} else {
			$res .= '<manifest><result>0</result></manifest>';
		}
		return $res;
	}

	/**
	 * �R���ҵ{
	 * @param
	 * @return
	 **/
	function deleteCourse($cid) {
		global $sysSession, $sysConn;

		$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		getFavorite();
		$path = MakeUserDir($sysSession->username);
		$filename = $path . '/my_course_favorite.xml';
		if (!$xmlvars = domxml_open_file($filename)) {
			$res .= '<manifest><result>1</result></manifest>';
			return $res;
		}
		$ctx = xpath_new_context($xmlvars);

		$gid = getSetting('group_id');
		$csid = explode(',', $cid);
		$cnt = count($csid);
		for ($i = 0; $i < $cnt; $i++) {
			if ($gid == '10000000') {
				$xpath = '/manifest/course[@id="' . intval($csid[$i]) . '"]';
			} else {
				$xpath = '//courses[@id="' . $gid . '"]/child::course[@id="' . intval($csid[$i]) . '"]';
			}
			$nodes = xpath_eval($ctx, $xpath);
			if (count($nodes->nodeset) <= 0) continue;
			$source = $nodes->nodeset[0];
			$parent = $source->parent_node();
			$parent->remove_child($source);
		}
		$xmlvars->dump_file($filename);

		$res .= '<manifest><result>0</result></manifest>';
		return $res;
	}

	/**
	 * �W��
	 * @param
	 * @return
	 **/
	function movePost($act, $cid) {
		global $sysSession, $sysConn;

		$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		getFavorite();
		$path = MakeUserDir($sysSession->username);
		$filename = $path . '/my_course_favorite.xml';
		if (!$xmlvars = domxml_open_file($filename)) {
			$res .= '<manifest><result>1</result></manifest>';
			return $res;
		}
		$ctx = xpath_new_context($xmlvars);

		$gid = getSetting('group_id');
		if ($gid == '10000000') {
			$xpath = '/manifest/course';
		} else {
			$xpath = '//courses[@id="' . $gid . '"]/child::course';
		}
		$nodes = xpath_eval($ctx, $xpath);
		$cnt = count($nodes->nodeset);
		if ($cnt <= 0) {
			$res .= '<manifest><result>2</result></manifest>';
			return $res;
		}

		$mcsid = explode(',', $cid);
		$parent = $nodes->nodeset[0]->parent_node();
		$hasMove = false;
		if ($act == 'up') {   // �W��
			for ($i = 0, $j = 0; $i < $cnt; $i++) {
				$node = $nodes->nodeset[$i];
				$csid[$i] = $node->get_attribute('id');
				if (($i > 0) && ($mcsid[$j] == $csid[$i])) {
					$tmp = $csid[$i - 1];
					$csid[$i - 1] = $csid[$i];
					$csid[$i] = $tmp;
					$j++;
					$hasMove = true;
				}
				$parent->remove_child($node);
			}
		} else {   // �U��
			for ($i = $cnt - 1, $j = count($mcsid) - 1; $i >= 0; $i--) {
				$node = $nodes->nodeset[$i];
				$csid[$i] = $node->get_attribute('id');
				if (($i < $cnt - 1) && ($mcsid[$j] == $csid[$i])) {
					$tmp = $csid[$i + 1];
					$csid[$i + 1] = $csid[$i];
					$csid[$i] = $tmp;
					$j--;
					$hasMove = true;
				}
				$parent->remove_child($node);
			}
		}

		// �^�s�Ƨǫ᪺�ҵ{
		for ($i = 0; $i < $cnt; $i++) {
			$node = $xmlvars->create_element('course');
			$node->set_attribute('id', $csid[$i]);
			$parent->append_child($node);
		}
		$xmlvars->dump_file($filename);

		if ($hasMove) {
			// ���ʦ��\
			$res .= '<manifest><result>0</result></manifest>';
		} else {
			// �L�k�W�U��
			$res .= '<manifest><result>-1</result></manifest>';
		}
		return $res;
	}

	/**
	 * �[�h��
	 * @param string $act : �[��ΰh��
	 * @param string $cid : �ҵ{�s���C�H�r�I�j�}���r��
	 * @return string �� XML �]�˰_�Ӫ��T���]�t�F���\(0) �Υ���(1) ���T��
	 **/
	function elective($act, $cid) {
		global $sysSession, $sysConn;

		$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		$filename = setElevtive(false);

		if (!$xmlvars = domxml_open_file($filename)) {
			$res .= '<manifest><result>1</result></manifest>';
			return $res;
		}
		$ctx = xpath_new_context($xmlvars);

		$csid = explode(',', $cid);
		$cnt = count($csid);
		if ($act == 'major_del') {
			for ($i = 0; $i < $cnt; $i++) {
				if (intval($csid[$i]) <= 10000000) continue;
				$xpath = '/manifest/course[@id="' . intval($csid[$i]) . '"]';
				$nodes = xpath_eval($ctx, $xpath);
				if (count($nodes->nodeset) > 0) {
					$parent = $nodes->nodeset[0]->parent_node();
					$parent->remove_child($nodes->nodeset[0]);
				}
			}
		} else {
			$root = $xmlvars->document_element();
			for ($i = 0; $i < $cnt; $i++) {
				if (intval($csid[$i]) <= 10000000) continue;
				$xpath = '/manifest/course[@id="' . intval($csid[$i]) . '"]';
				$nodes = xpath_eval($ctx, $xpath);
				if (count($nodes->nodeset) <= 0) {
					$node = $xmlvars->create_element('course');
					$node->set_attribute('id', intval($csid[$i]));
					$root->append_child($node);
				}
			}
		}
		$xmlvars->dump_file($filename);

		$res .= '<manifest><result>0</result></manifest>';
		return $res;
	}

	/**
	 * ���]��ҲM��
	 **/
	function elective_reset() {
		global $sysSession, $sysConn;

		$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		$filename = setElevtive(false);

		if (!$xmlvars = domxml_open_file($filename)) {
			$res .= '<manifest><result>1</result></manifest>';
			return $res;
		}
		$root = $xmlvars->document_element();
		$nodes = $root->get_elements_by_tagname('course');
		$cnt = count($nodes) - 1;
		for ($i = $cnt; $i >= 0; $i--) {
			$root->remove_child($nodes[$i]);
		}
		$xmlvars->dump_file($filename);

		$res .= '<manifest><result>0</result></manifest>';
		return $res;
	}

	/**
	 * ���o�̰��u���v���f�ֳW�h�s��
	 * @param array $ary   : �W�h���s��
	 * @param array $rules : �Ҧ����W�h
	 * @return string $rid : �s��
	 **/
	function getfwid($ary, $rules) {
		$rid = 0;
		$mut = -1;
		if (!is_array($ary)) return $rid;
		foreach ($ary as $idx) {
			$tmp = $rules[$idx][0];
			if (($mut < 0) || ($tmp < $mut)) {
				$rid = $idx;
				$mut = $tmp;
			}
		}
		return $rid;
	}

	/**
	 * �e�X��ҲM��
	 * @param string $cid : �ҵ{�C��s��
	 * @param string $username : �b���A�w�]�� sysSession->username
	 * @param boolean $reset : ���]���e���o���׽ҳW�h�P���
	 * @return
	 **/
	$enRules  = array();
	$enMaps   = array();
	$enResult = array();
	function elective_send($cid, $username='', $direct=FALSE, $reset=FALSE) {
		global $sysSession, $sysConn, $MSG, $enRules, $enMaps, $enResult, $sysRoles;

		// �ˬd�Ƕi�Ӫ���
		$cid = trim($cid);
		if (empty($cid)) {
			$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			$res .= '<manifest><result>1</result></manifest>';
			return $res;
		}
		$username = trim($username);
		if (empty($username)) $username = $sysSession->username;
		// �f�֪��u�����ǡG�ҵ{ -> �s�� -> �Ǯ�
		// �ҵ{���W�h
		// �s�ժ��W�h
		// �Ǯժ��W�h

		if ($reset) {
			$enRules  = array();
			$enMaps   = array();
			$enResult = array();
		}
		// �����X�Ҧ��W�h
		if (count($enRules) <= 0) {
			$RS = dbGetStMr('WM_review_syscont', '`flow_serial`, `content`, `permute`', "`kind`='course' order by `permute`", ADODB_FETCH_ASSOC);
			while (!$RS->EOF) {
				$enRules[$RS->fields['flow_serial']] = array($RS->fields['permute'], $RS->fields['content']);
				$RS->MoveNext();
			}
		}

		// ���X�Ҧ����������Y
		if (count($enMaps) <= 0) {
			$RS = dbGetStMr('WM_review_sysidx', '`discren_id`, `flow_serial`', '1 order by `discren_id`', ADODB_FETCH_ASSOC);
			while (!$RS->EOF) {
				$enMaps[$RS->fields['discren_id']][] = $RS->fields['flow_serial'];
				$RS->MoveNext();
			}
		}

		// �}�l�d�߳W�h
		$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		$res .= '<manifest><result>1</result></manifest>';
		$now  = time();
		$csid = preg_split('/\D+/', $cid);
		// ���o�ҿ�ҵ{�������ͻP��ť�ͼƶq����(�@�����o�A������b�j�餤�C���ҨӨ⦸ SQL query)
		$student_amount_limits = dbGetAssoc('WM_term_major',
											'course_id, sum(if(role&' . $sysRoles['student'] . ', 1, 0)) , sum(if(role&' . $sysRoles['auditor'] . ', 1, 0))',
											'course_id in (' . implode(',', $csid) . ') group by course_id',
											ADODB_FETCH_NUM);
		foreach ($csid as $val) {
			if (intval($val) <= 10000000) continue;
			// �ˬd���L�f�ֳW�h (Begin)
			$rid = 0;
			$mut = -1;
				// �ҵ{���W�h (Begin)
			if (isset($enMaps[$val])) {
				$ary = $enMaps[$val];
				$rid = getfwid($ary, $enRules);
			}
				// �ҵ{���W�h (End)
				// �s�ժ��W�h (Begin)
			if ($rid == 0) {
				$ary = array();
				$RS = dbGetStMr('WM_term_group', 'distinct `parent`', "`child`={$val}", ADODB_FETCH_ASSOC);
				if ($RS->RecordCount() > 0) {
					$ary = array_merge($ary, (array)$enMaps[$RS->fields['parent']]);
				}
				$rid = getfwid($ary, $enRules);
			}
				// �s�ժ��W�h (End)
				// �Ǯժ��W�h (Begin)
			if ($rid == 0) {
				$ary = $enMaps[10000000];
				$rid = getfwid($ary, $enRules);
			}
				// �Ǯժ��W�h (End)
			// �ˬd���L�f�ֳW�h (End)

			/**
			 * �򥻪��ҵ{����ˬd
			 *     �ҵ{���A      (�ˬd)
			 *     ���W�ɭ�      (���ˬd)
			 *     �W�Үɭ�      (���ˬd)
			 *     �����ͤH��    (���ˬd)
			 *     ��ť�ͤH��    (���ˬd)
			 **/
			$CS = dbGetStSr('WM_term_course', '`kind`, `en_begin`, `en_end`, `st_begin`, `st_end`, `status`, `n_limit`, `a_limit`', "course_id={$val}", ADODB_FETCH_ASSOC);
			// �ˬd�O�_�w�g�����
			$cnt = aclCheckRole($username, $sysRoles['student'], $val);
			if ($cnt > 0) {
				$txt = build_rule_xml($username, $sysSession->email, $val, '%cs_select', 'yes', $MSG['msg_cs_selected'][$sysSession->lang]);
				set_result($val, '%cs_select', 'yes', $txt);
				$enResult[$username][$val] = array('%cs_select', 'yes', $MSG['msg_cs_selected'][$sysSession->lang], $txt);
				continue;
			}
			// �ˬd�O�_���s��
			if ($CS['kind'] == 'group') {
				$txt = build_rule_xml($username, $sysSession->email, $val, '%cs_kind', 'differ', $MSG['msg_id_is_group'][$sysSession->lang]);
				set_result($val, '%cs_kind', 'group', $txt);
				$enResult[$username][$val] = array('%cs_kind', 'group', $MSG['msg_id_is_group'][$sysSession->lang], $txt);
				continue;
			}
			// �ˬd�ҵ{�O�_����
			$CS['status'] = intval($CS['status']);
			if ($CS['status'] == 0) {
				$txt = build_rule_xml($username, $sysSession->email, $val, '%cs_status', 'equal', $MSG['msg_cs_state_close'][$sysSession->lang]);
				set_result($val, '%cs_status', '0', $txt);
				$enResult[$username][$val] = array('%cs_status', '0', $MSG['msg_cs_state_close'][$sysSession->lang], $txt);
				continue;
			}
			// �ˬd�ҵ{�O�_�ǳƤ�
			if ($CS['status'] == 5) {
				$txt = build_rule_xml($username, $sysSession->email, $val, '%cs_status', 'equal', $MSG['msg_cs_state_not_ready'][$sysSession->lang]);
				set_result($val, '%cs_status', '5', $txt);
				$enResult[$username][$val] = array('%cs_status', '5', $MSG['msg_cs_state_not_ready'][$sysSession->lang], $txt);
				continue;
			}
			// �ˬd�ҵ{�O�_�w�g�R��
			if ($CS['status'] == 9) {
				$txt = build_rule_xml($username, $sysSession->email, $val, '%cs_status', 'equal', $MSG['msg_cs_state_delete'][$sysSession->lang]);
				set_result($val, '%cs_status', '9', $txt);
				$enResult[$username][$val] = array('%cs_status', '9', $MSG['msg_cs_state_delete'][$sysSession->lang], $txt);
				continue;
			}

			// �ˬd�����ͤH�ƻP��ť�ͤH��
            $n_cnt = (int)$student_amount_limits[$val][0];
            $a_cnt = (int)$student_amount_limits[$val][1];
			// echo 'n_cnt=', $n_cnt, ' ; a_cnt=' , $a_cnt, ' ; n_limit=', $CS['n_limit'], ' ; a_limit=', $CS['a_limit'];
			if ($CS['n_limit'] && $CS['a_limit'] && $n_cnt >= $CS['n_limit'] && $a_cnt >= $CS['a_limit']) {
				$txt = build_rule_xml($username, $sysSession->email, $val, '%cs_study', 'deny', $MSG['msg_cs_study_deny'][$sysSession->lang]);
				set_result($val, '%cs_study', 'deny', $txt);
				$enResult[$username][$val] = array('%cs_study', 'deny', $MSG['msg_cs_study_deny'][$sysSession->lang], $txt);
				continue;
			}

			if ($rid == 0) {
				// �S������f�ֳW�h�A�h�����N�ǭ��[�J�Ӫ���
				/**
				 * �򥻪��ҵ{����ˬd
				 *     �ҵ{���A      (�ˬd)
				 *     ���W�ɭ�      (���ˬd)
				 *     �W�Үɭ�      (���ˬd)
				 *     �����ͤH��    (���ˬd)
				 *     ��ť�ͤH��    (���ˬd)
				 **/
				// list($n_cnt) = dbGetStSr('WM_term_major', 'count(*)', 'role & 32');
				if (empty($CS['n_limit']) || ($n_cnt < $CS['n_limit'])) {
                    assignLearnerPermission($username, $val, $sysRoles['student']);
					$txt = build_rule_xml($username, $sysSession->email, $val, '%cs_study', 'ok', $MSG['msg_no_rule_ok_student'][$sysSession->lang]);
					set_result($val, '%cs_study', 'ok', $txt);
					$enResult[$username][$val] = array('%cs_study', 'ok', $MSG['msg_no_rule_ok_student'][$sysSession->lang], $txt);
					continue;
				}
				// list($a_cnt) = dbGetStSr('WM_term_major', 'count(*)', 'role & 16');
				if (empty($CS['a_limit']) || ($a_cnt < $CS['a_limit'])) {
				    assignLearnerPermission($username, $val, $sysRoles['auditor']);
					$txt = build_rule_xml($username, $sysSession->email, $val, '%student_full', 'auditor', $MSG['msg_no_rule_ok_auditor'][$sysSession->lang]);
					set_result($val, '%student_full', 'auditor', $txt);
					$enResult[$username][$val] = array('%student_full', 'auditor', $MSG['msg_no_rule_ok_auditor'][$sysSession->lang], $txt);
					continue;
				}
				$txt = build_rule_xml($username, $sysSession->email, $val, '%cs_study', 'deny', $MSG['msg_cs_study_deny'][$sysSession->lang]);
				set_result($val, '%cs_study', 'deny', $txt);
				$enResult[$username][$val] = array('%cs_study', 'deny', $MSG['msg_cs_study_deny'][$sysSession->lang], $txt);
			} else if ($rid > 0) {
				$xmlDocs = domxml_open_mem($enRules[$rid][1]);
				$cker = getChecker($xmlDocs);
				if ($cker !== false && empty($cker)) {
					$expr = "//activity[@id='WM_START']";
					$node = selectSingleNode($xmlDocs, $expr);
					$node->set_attribute('status', 'decide');
					// �]�w�M�w�����G
					$expr = "//activity[@id='WM_START']/to/feedback";
					$node = selectSingleNode($xmlDocs, $expr);
					$node->set_attribute('param', 'ok');

					$now = date('Y/m/d H:i:s', time());
					// �]�wŪ���ɶ�
					$expr = "//activity[@id='WM_START']/to/receive_time";
					$node = selectSingleNode($xmlDocs, $expr);
					$child = $xmlDocs->create_text_node($now);
					$node->append_child($child);
					// �]�w�M�w�ɶ�
					$expr = "//activity[@id='WM_START']/to/decide_time";
					$node = selectSingleNode($xmlDocs, $expr);
					$child = $xmlDocs->create_text_node($now);
					$node->append_child($child);
					$enRules[$rid][1] = $xmlDocs->dump_mem(true);

					// �[�J�ҵ{��
					if (empty($CS['n_limit']) || ($n_cnt < $CS['n_limit'])) {
						assignLearnerPermission($username, $val, $sysRoles['student']);
						$stat   = 'close';
						$param  = '#none';
						$result = 'ok';
					}
					else {
						assignLearnerPermission($username, $val, $sysRoles['auditor']);
						$stat   = 'close';
						$param  = '%student_full';
						$result = 'auditor';
					}

				} else {
					$stat   = 'open';
					$param  = '';
					$result = '';
				}

				init_rule($rid, $val, $username, $sysSession->email, $username, $sysSession->email, $enRules[$rid][1], $stat, $param, $result);
				// $enResult[$username][$val] = array('%cs_study', 'rule', $rid, '');
				if ($rid > 0 && $cker)
					$enResult[$username][$val] = array($stat, $result, $rid, '');
				else
					$enResult[$username][$val] = array($param, $result, $rid, '');
			}
			// echo "$val - 6\n";
		}   // End foreach ($csid as $val)
		$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		$res .= '<manifest><result>0</result></manifest>';
		// ���]��ҲM�� (�M����ҲM��)
		if (!$direct) elective('major_del', $cid);
		return $res;
	}

	/**
	 * �ت� : �����ͥ��f�ֽҵ{�h��Y�R���������
	 * @param string $cid : �ҵ{�s��
	 *
	 * @return int result : (0,�R�����\�B1,�R������)
	 */
	function drop_unelective_send($cid, $username='', $direct=FALSE, $reset=FALSE) {
		global $sysSession, $sysConn, $sysRoles, $MSG;
		// �ˬd�ҵ{�s��
		if (empty($username)) $username = $sysSession->username;
		$cid = trim($cid);
		if (empty($cid)) {
			$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			$res .= '<manifest><result>0</result></manifest>';
			return $res;
		}
		$rid = 0;
		$mut = -1;
		// �ҵ{���W�h (Begin)
		if (isset($enMaps[$cid])) {
			$ary = $enMaps[$cid];
			$rid = getfwid($ary, $enRules);
		}
		// �ҵ{���W�h (End)

		// �Ǯժ��W�h (Begin)
		if ($rid == 0) {
			$ary = $enMaps[10000000];
			$rid = getfwid($ary, $enRules);
		}
		// �Ǯժ��W�h (End)

		list($idx) = dbGetStSr('WM_review_flow', 'idx', "`username`='{$username}' AND `discren_id`={$cid} AND state='open'", ADODB_FETCH_NUM);
		if($idx) dbDel('WM_review_flow',"`username`='{$username}' AND `discren_id`={$cid} AND `idx`={$idx} ");
		$txt = build_rule_xml($username, $sysSession->email, $cid, '%cs_delete', '', $MSG['msg_cs_delete'][$sysSession->lang]);
		set_result($cid, '%cs_delete', '', $txt);
		$enResult[$username][$cid] = array('%cs_delete', '', $MSG['msg_cs_delete'][$sysSession->lang], $txt);

		$xmlDocs = domxml_open_mem($enRules[$rid][1]);
		$cker = getChecker($xmlDocs);
		if ($cker !== false && empty($cker)) {
		$expr = "//activity[@id='WM_START']";
		$node = selectSingleNode($xmlDocs, $expr);
		$node->set_attribute('status', 'decide');
		// �]�w�M�w�����G
		$expr = "//activity[@id='WM_START']/to/feedback";
		$node = selectSingleNode($xmlDocs, $expr);
		$node->set_attribute('param', 'ok');

		$now = date('Y/m/d H:i:s', time());
		// �]�wŪ���ɶ�
		$expr = "//activity[@id='WM_START']/to/receive_time";
		$node = selectSingleNode($xmlDocs, $expr);
		$child = $xmlDocs->create_text_node($now);
		$node->append_child($child);
		// �]�w�M�w�ɶ�
		$expr = "//activity[@id='WM_START']/to/decide_time";
		$node = selectSingleNode($xmlDocs, $expr);
		$child = $xmlDocs->create_text_node($now);
		$node->append_child($child);
		$enRules[$rid][1] = $xmlDocs->dump_mem(true);
		$stat   = 'close';
		$param  = '#delete';
		$result = '';
		}
		init_rule($rid, $cid, $username, $sysSession->email, $username, $sysSession->email, $enRules[$rid][1], $stat, $param, $result);
		$enResult[$username][$cid] = array($param, $result, $rid, '');
		$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		$res .= '<manifest><result>'.$cid.'</result></manifest>';
		// ���]��ҲM�� (�M����ҲM��)
		if (!$direct) elective('major_del', $cid);
		return $res;

	}

    /**
	 * �ت� : ��ť�Ͱh��Y�R���������
	 * @param string $cid : �ҵ{�s��
	 * @param string $str_homework_ids : �ҵ{�Ҧ����@�~�s��
	 *
	 * @return int result : (0,�R�����\�B1,�R������)
	 */
	function drop_elective_send($cid, $username=null) {
		global $sysSession, $sysConn, $sysRoles;
		// �ˬd�ҵ{�s��
		$cid = trim($cid);
		if (empty($cid)) {
			$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			$res .= '<manifest><result>0</result></manifest>';
			return $res;
		}

		$username = trim($username);
		if (empty($username)) $username = $sysSession->username;

		// �ˬd�O�_����ӽ�
		$isStudent = aclCheckRole($username, $sysRoles['student'] | $sysRoles['auditor'], $cid);
		if ($isStudent) {
			include_once(sysDocumentRoot . '/lib/lib_stud_rm.php');
			$rtn    = DelStudentAll($cid,$username,true);
			$result = ($rtn == 0)?1:0;
			dbDel('WM_term_major', 'role=0');
		}else
			$result = 0;

		$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		$res .= '<manifest><result>'.$result.'</result></manifest>';

		return $res;
	}

	function delete_evresult($xmlDocs) {
		global $sysConn, $sysSession;

		$nodes = $xmlDocs->get_elements_by_tagname('rid');
		$sc = 0;
		$fr = 0;
		for ($i = 0; $i < count($nodes); $i++) {
			if (!$nodes[$i]->has_child_nodes()) continue;
			$child = $nodes[$i]->child_nodes();
			$node  = $child[0];
			$rid   = $node->node_value();
			$rid   = intval(sysDecode(trim($rid)));
			dbDel('WM_review_flow', "`idx`={$rid} AND `username`='{$sysSession->username}'");
			($sysConn->Affected_Rows() > 0) ? $sc++ : $fr++;
		}
		echo $sc . ',' . $fr;
	}

	//echo $GLOBALS['HTTP_RAW_POST_DATA'];
	//die();
	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			exit;
		}

		$action = getNodeValue($dom, 'action');
		$result = '';
		switch ($action) {
			case 'detail'     :   // ���o�ҵ{���ԲӸ��
				$course_id = getNodeValue($dom, 'course_id');
				$result = getCourseDetail(intval($course_id));
				break;

			case 'list_group' :   // �^�ǿz��L���ҵ{�s��
			case 'manage_folder':
				$result = getCourseGroup();
				break;

			case 'group'      :   // �^�ǽҵ{�s�դ����Ҧ��ҵ{
				$group_id = getNodeValue($dom, 'group_id');
				$res = saveSetting('group_id', $group_id);
				$result = ($res) ? '<result>true</result>' : '<result>false</result>';
				$result = '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n<manifest>{$result}</manifest>";
				break;

			case 'favorite'   :   // ��ܩ����çڪ��̷R
				$res = getSetting('favorite');
				$res = (($res == 'true') || empty($res)) ? 'false' : 'true';
				saveSetting('favorite', $res);
				$result = '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n<manifest></manifest>";
				break;

			case 'add_favorite':  // �N�ҵ{�[��ڪ��̷R��
				$course_id = getNodeValue($dom, 'course_id');
				$result = add_favorite($course_id);
				break;

			case 'save'       :   // �x�s�ڪ��̷R
				$result = saveFolder($dom);
				//$result = '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n<manifest></manifest>";
				break;

			case 'append'     :   // ����
			case 'move'       :   // �h��
				$course_id = getNodeValue($dom, 'course_id');
				$group_id  = getNodeValue($dom, 'group_id');
				$result = moveCourse($action, $group_id, $course_id);
				break;

			case 'delete'     :   // �R��
				$course_id = getNodeValue($dom, 'course_id');
				$result = deleteCourse($course_id);
				break;

			case 'up'         :   // �W��
			case 'down'       :   // �U��
				$course_id = getNodeValue($dom, 'course_id');
				$result = movePOST($action, $course_id);
				break;

			case 'major_add'  :   // �[��
			case 'major_del'  :   // �h��
				$course_id = getNodeValue($dom, 'course_id');
				$result = elective($action, $course_id);
				break;

			case 'major_reset':   // ���]��ҲM��
				$result = elective_reset();
				break;

			case 'elective'   :   // �e�X��ҲM��
				$course_id = getNodeValue($dom, 'course_id');
				$result = elective_send($course_id);
				foreach($enResult as $uname => $cids) {
					$msg = '';
					foreach($cids as $cid => $data)
						$msg .= $cid . $data[2] . ',';
					wmSysLog('1100200100', $sysSession->school_id, 0, 0, 'classroom', $_SERVER['PHP_SELF'], $uname . ' major add:' . $msg);
				}
				break;
			case 'ev_delete'   :  // �R����ҵ��G
				echo delete_evresult($dom);
				die();
				break;
			case 'drop_elective'   :   // ��ť�Ͱh��ҵ{
				$course_id = getNodeValue($dom, 'course_id');
				$result = drop_elective_send($course_id);
				wmSysLog('1100200200', $sysSession->school_id, 0, 0, 'classroom', $_SERVER['PHP_SELF'], 'Major del:' . $course_id);
				break;
			case 'drop_unelective'	:	// �����Ͱh�良�f�ֽҵ{
				$course_id = getNodeValue($dom, 'course_id');
				$result = drop_unelective_send($course_id);
				wmSysLog('1100200200', $sysSession->school_id, 0, 0, 'classroom', $_SERVER['PHP_SELF'], 'Major del:' . $course_id);
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
