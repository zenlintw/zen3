<?php
	/**
	 * 回傳篩選後的課程群組
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
	 * 賦與學員身份
	 *
	 * @param   string      $username       帳號
	 * @param   int         $course_id      課程代號
	 * @param   int         $permission     身份權限
	 */
	function assignLearnerPermission($username, $course_id, $permission)
	{
		global $sysConn, $sysRoles;
		static $mask;   // 保留教師(助教、講師)身份，並清除學生(正式、旁聽)身份

		if (!isset($mask)) $mask = $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'];

		dbNew('WM_term_major', '`username`, `course_id`, `role`, `add_time`', "'{$username}', {$course_id}, {$permission}, NOW()");
		if ($sysConn->ErrorNo() == 1062) {
		    dbSet('WM_term_major', 'role=role&' . $mask . '|' . $permission , "username='{$username}' and course_id={$course_id}");
		}
	}

	/**
	 * 建立課程的 XML 檔
	 *     @pram $val (課程編號, 課程名稱, 報名日期 (開始), 報名日期 (結束), 上課日期 (開始), 上課日期 (結束), 已使用空間)
	 **/
	function buildCourseXML($val) {
		global $sysSession, $sysConn, $Sqls;

		if (!is_array($val)) return '';
		$result = '';
		// 輸出課程的 XML (Begin)
		// 課程名稱
		$lang = getCaption($val['caption']);
		// 教材名稱
		$content_id = intval($val['content_id']);
		$RS = dbGetStSr('WM_content', 'caption', "content_id={$content_id}", ADODB_FETCH_ASSOC);
		if (!($RS === false)) {
			$content = getCaption($RS['caption']);
		}
		// 報名日期
		$en_begin = $val['en_begin'];
		$en_end   = $val['en_end'];
		// 上課日期
		$st_begin = $val['st_begin'];
		$st_end   = $val['st_end'];
		// 已使用空間
		$val['quota_used']  = intval($val['quota_used']);
		$val['quota_limit'] = intval($val['quota_limit']);
		if (empty($val['quota_limit'])) $val['quota_limit'] = 1;
		$quota_used = round($val['quota_used'] / $val['quota_limit'], 4) * 100;
		// 及格成績
		$val['fair_grade'] = intval($val['fair_grade']);

		// 審核規則
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

		// 取出所在的課程群組
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
		// 輸出 XML
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
		// 輸出課程的 XML (End)
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
	 * 儲存編修後的我的最愛的資料夾
	 * @param object $xmldoc : 整個要儲存的 XML 設定檔
	 * @return string 用 XML 包裝起來的訊息包含了成功或失敗的訊息
	 **/
	function saveFolder($xmldoc) {
		global $sysSession, $sysConn;
		$content = '';

		// 清除 ticket Node
		$nodes = $xmldoc->get_elements_by_tagname('ticket');
		$cnt = count($nodes);
		for ($i = 0; $i < $cnt; $i++) {
			$node = $nodes[$i]->parent_node();
			$node->remove_child($nodes[$i]);
		}
		// 清除 action Node
		$nodes = $xmldoc->get_elements_by_tagname('action');
		$cnt = count($nodes);
		for ($i = 0; $i < $cnt; $i++) {
			$node = $nodes[$i]->parent_node();
			$node->remove_child($nodes[$i]);
		}
		// 資料夾的動作 (Begin)
		$nodes = $xmldoc->get_elements_by_tagname('courses');
		$cnt = count($nodes);
		$newID = array();
		$attr = '';

		// 新增資料夾
		for ($i = 0; $i < $cnt; $i++) {
			$attr = $nodes[$i]->get_attribute('id');
			if (empty($attr) || in_array($attr, $newID)) {
				$attr = uniqid('USER_');
				$nodes[$i]->set_attribute('id', $attr);
			}
			$newID[] = $attr;
		}

		// 取得原本的資料
		$content = getFavorite();

		$path = MakeUserDir($sysSession->username);
		$filename = $path . '/my_course_favorite.xml';

		$xmlstr = $xmldoc->dump_mem(true);

		// 寫回檔案中 (Begin)
		touch($filename);
		if ($fp = fopen($filename, 'w')) {
			@fwrite($fp, $xmlstr);
			fclose($fp);
		}
		// 寫回檔案中 (End)

		$res = ($content != $xmlstr) ? '1' : '0';
		$xmlstr = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n" .
			'<manifest><result>' . $res . '</result></manifest>';

		return $xmlstr;
	}

	/**
	 * 將課程加到我的最愛中
	 * @param string $csid : 課程編號
	 * @return string 用 XML 包裝起來的訊息包含了成功或失敗的訊息
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
	 * 檢查這個課程是否已經在這個資料夾中
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
	 * 附屬或搬移課程
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
	 * 刪除課程
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
	 * 上移
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
		if ($act == 'up') {   // 上移
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
		} else {   // 下移
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

		// 回存排序後的課程
		for ($i = 0; $i < $cnt; $i++) {
			$node = $xmlvars->create_element('course');
			$node->set_attribute('id', $csid[$i]);
			$parent->append_child($node);
		}
		$xmlvars->dump_file($filename);

		if ($hasMove) {
			// 移動成功
			$res .= '<manifest><result>0</result></manifest>';
		} else {
			// 無法上下移
			$res .= '<manifest><result>-1</result></manifest>';
		}
		return $res;
	}

	/**
	 * 加退選
	 * @param string $act : 加選或退選
	 * @param string $cid : 課程編號。以逗點隔開的字串
	 * @return string 用 XML 包裝起來的訊息包含了成功(0) 或失敗(1) 的訊息
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
	 * 重設選課清單
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
	 * 取得最高優先權的審核規則編號
	 * @param array $ary   : 規則的編號
	 * @param array $rules : 所有的規則
	 * @return string $rid : 編號
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
	 * 送出選課清單
	 * @param string $cid : 課程列表編號
	 * @param string $username : 帳號，預設為 sysSession->username
	 * @param boolean $reset : 重設之前取得的修課規則與資料
	 * @return
	 **/
	$enRules  = array();
	$enMaps   = array();
	$enResult = array();
	function elective_send($cid, $username='', $direct=FALSE, $reset=FALSE) {
		global $sysSession, $sysConn, $MSG, $enRules, $enMaps, $enResult, $sysRoles;

		// 檢查傳進來的值
		$cid = trim($cid);
		if (empty($cid)) {
			$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			$res .= '<manifest><result>1</result></manifest>';
			return $res;
		}
		$username = trim($username);
		if (empty($username)) $username = $sysSession->username;
		// 審核的優先順序：課程 -> 群組 -> 學校
		// 課程的規則
		// 群組的規則
		// 學校的規則

		if ($reset) {
			$enRules  = array();
			$enMaps   = array();
			$enResult = array();
		}
		// 先取出所有規則
		if (count($enRules) <= 0) {
			$RS = dbGetStMr('WM_review_syscont', '`flow_serial`, `content`, `permute`', "`kind`='course' order by `permute`", ADODB_FETCH_ASSOC);
			while (!$RS->EOF) {
				$enRules[$RS->fields['flow_serial']] = array($RS->fields['permute'], $RS->fields['content']);
				$RS->MoveNext();
			}
		}

		// 取出所有的對應關係
		if (count($enMaps) <= 0) {
			$RS = dbGetStMr('WM_review_sysidx', '`discren_id`, `flow_serial`', '1 order by `discren_id`', ADODB_FETCH_ASSOC);
			while (!$RS->EOF) {
				$enMaps[$RS->fields['discren_id']][] = $RS->fields['flow_serial'];
				$RS->MoveNext();
			}
		}

		// 開始查詢規則
		$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		$res .= '<manifest><result>1</result></manifest>';
		$now  = time();
		$csid = preg_split('/\D+/', $cid);
		// 取得所選課程的正式生與旁聽生數量限制(一次取得，不必放在迴圈中每門課來兩次 SQL query)
		$student_amount_limits = dbGetAssoc('WM_term_major',
											'course_id, sum(if(role&' . $sysRoles['student'] . ', 1, 0)) , sum(if(role&' . $sysRoles['auditor'] . ', 1, 0))',
											'course_id in (' . implode(',', $csid) . ') group by course_id',
											ADODB_FETCH_NUM);
		foreach ($csid as $val) {
			if (intval($val) <= 10000000) continue;
			// 檢查有無審核規則 (Begin)
			$rid = 0;
			$mut = -1;
				// 課程的規則 (Begin)
			if (isset($enMaps[$val])) {
				$ary = $enMaps[$val];
				$rid = getfwid($ary, $enRules);
			}
				// 課程的規則 (End)
				// 群組的規則 (Begin)
			if ($rid == 0) {
				$ary = array();
				$RS = dbGetStMr('WM_term_group', 'distinct `parent`', "`child`={$val}", ADODB_FETCH_ASSOC);
				if ($RS->RecordCount() > 0) {
					$ary = array_merge($ary, (array)$enMaps[$RS->fields['parent']]);
				}
				$rid = getfwid($ary, $enRules);
			}
				// 群組的規則 (End)
				// 學校的規則 (Begin)
			if ($rid == 0) {
				$ary = $enMaps[10000000];
				$rid = getfwid($ary, $enRules);
			}
				// 學校的規則 (End)
			// 檢查有無審核規則 (End)

			/**
			 * 基本的課程資料檢查
			 *     課程狀態      (檢查)
			 *     報名時限      (不檢查)
			 *     上課時限      (不檢查)
			 *     正式生人數    (不檢查)
			 *     旁聽生人數    (不檢查)
			 **/
			$CS = dbGetStSr('WM_term_course', '`kind`, `en_begin`, `en_end`, `st_begin`, `st_end`, `status`, `n_limit`, `a_limit`', "course_id={$val}", ADODB_FETCH_ASSOC);
			// 檢查是否已經有選課
			$cnt = aclCheckRole($username, $sysRoles['student'], $val);
			if ($cnt > 0) {
				$txt = build_rule_xml($username, $sysSession->email, $val, '%cs_select', 'yes', $MSG['msg_cs_selected'][$sysSession->lang]);
				set_result($val, '%cs_select', 'yes', $txt);
				$enResult[$username][$val] = array('%cs_select', 'yes', $MSG['msg_cs_selected'][$sysSession->lang], $txt);
				continue;
			}
			// 檢查是否為群組
			if ($CS['kind'] == 'group') {
				$txt = build_rule_xml($username, $sysSession->email, $val, '%cs_kind', 'differ', $MSG['msg_id_is_group'][$sysSession->lang]);
				set_result($val, '%cs_kind', 'group', $txt);
				$enResult[$username][$val] = array('%cs_kind', 'group', $MSG['msg_id_is_group'][$sysSession->lang], $txt);
				continue;
			}
			// 檢查課程是否關閉
			$CS['status'] = intval($CS['status']);
			if ($CS['status'] == 0) {
				$txt = build_rule_xml($username, $sysSession->email, $val, '%cs_status', 'equal', $MSG['msg_cs_state_close'][$sysSession->lang]);
				set_result($val, '%cs_status', '0', $txt);
				$enResult[$username][$val] = array('%cs_status', '0', $MSG['msg_cs_state_close'][$sysSession->lang], $txt);
				continue;
			}
			// 檢查課程是否準備中
			if ($CS['status'] == 5) {
				$txt = build_rule_xml($username, $sysSession->email, $val, '%cs_status', 'equal', $MSG['msg_cs_state_not_ready'][$sysSession->lang]);
				set_result($val, '%cs_status', '5', $txt);
				$enResult[$username][$val] = array('%cs_status', '5', $MSG['msg_cs_state_not_ready'][$sysSession->lang], $txt);
				continue;
			}
			// 檢查課程是否已經刪除
			if ($CS['status'] == 9) {
				$txt = build_rule_xml($username, $sysSession->email, $val, '%cs_status', 'equal', $MSG['msg_cs_state_delete'][$sysSession->lang]);
				set_result($val, '%cs_status', '9', $txt);
				$enResult[$username][$val] = array('%cs_status', '9', $MSG['msg_cs_state_delete'][$sysSession->lang], $txt);
				continue;
			}

			// 檢查正式生人數與旁聽生人數
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
				// 沒有任何審核規則，則直接將學員加入該門課
				/**
				 * 基本的課程資料檢查
				 *     課程狀態      (檢查)
				 *     報名時限      (不檢查)
				 *     上課時限      (不檢查)
				 *     正式生人數    (不檢查)
				 *     旁聽生人數    (不檢查)
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
					// 設定決定的結果
					$expr = "//activity[@id='WM_START']/to/feedback";
					$node = selectSingleNode($xmlDocs, $expr);
					$node->set_attribute('param', 'ok');

					$now = date('Y/m/d H:i:s', time());
					// 設定讀取時間
					$expr = "//activity[@id='WM_START']/to/receive_time";
					$node = selectSingleNode($xmlDocs, $expr);
					$child = $xmlDocs->create_text_node($now);
					$node->append_child($child);
					// 設定決定時間
					$expr = "//activity[@id='WM_START']/to/decide_time";
					$node = selectSingleNode($xmlDocs, $expr);
					$child = $xmlDocs->create_text_node($now);
					$node->append_child($child);
					$enRules[$rid][1] = $xmlDocs->dump_mem(true);

					// 加入課程中
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
		// 重設選課清單 (清除選課清單)
		if (!$direct) elective('major_del', $cid);
		return $res;
	}

	/**
	 * 目的 : 正式生未審核課程退選即刪除相關資料
	 * @param string $cid : 課程編號
	 *
	 * @return int result : (0,刪除成功、1,刪除失敗)
	 */
	function drop_unelective_send($cid, $username='', $direct=FALSE, $reset=FALSE) {
		global $sysSession, $sysConn, $sysRoles, $MSG;
		// 檢查課程編號
		if (empty($username)) $username = $sysSession->username;
		$cid = trim($cid);
		if (empty($cid)) {
			$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			$res .= '<manifest><result>0</result></manifest>';
			return $res;
		}
		$rid = 0;
		$mut = -1;
		// 課程的規則 (Begin)
		if (isset($enMaps[$cid])) {
			$ary = $enMaps[$cid];
			$rid = getfwid($ary, $enRules);
		}
		// 課程的規則 (End)

		// 學校的規則 (Begin)
		if ($rid == 0) {
			$ary = $enMaps[10000000];
			$rid = getfwid($ary, $enRules);
		}
		// 學校的規則 (End)

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
		// 設定決定的結果
		$expr = "//activity[@id='WM_START']/to/feedback";
		$node = selectSingleNode($xmlDocs, $expr);
		$node->set_attribute('param', 'ok');

		$now = date('Y/m/d H:i:s', time());
		// 設定讀取時間
		$expr = "//activity[@id='WM_START']/to/receive_time";
		$node = selectSingleNode($xmlDocs, $expr);
		$child = $xmlDocs->create_text_node($now);
		$node->append_child($child);
		// 設定決定時間
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
		// 重設選課清單 (清除選課清單)
		if (!$direct) elective('major_del', $cid);
		return $res;

	}

    /**
	 * 目的 : 旁聽生退選即刪除相關資料
	 * @param string $cid : 課程編號
	 * @param string $str_homework_ids : 課程所有的作業編號
	 *
	 * @return int result : (0,刪除成功、1,刪除失敗)
	 */
	function drop_elective_send($cid, $username=null) {
		global $sysSession, $sysConn, $sysRoles;
		// 檢查課程編號
		$cid = trim($cid);
		if (empty($cid)) {
			$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			$res .= '<manifest><result>0</result></manifest>';
			return $res;
		}

		$username = trim($username);
		if (empty($username)) $username = $sysSession->username;

		// 檢查是否有選該課
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
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
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
			case 'detail'     :   // 取得課程的詳細資料
				$course_id = getNodeValue($dom, 'course_id');
				$result = getCourseDetail(intval($course_id));
				break;

			case 'list_group' :   // 回傳篩選過的課程群組
			case 'manage_folder':
				$result = getCourseGroup();
				break;

			case 'group'      :   // 回傳課程群組中的所有課程
				$group_id = getNodeValue($dom, 'group_id');
				$res = saveSetting('group_id', $group_id);
				$result = ($res) ? '<result>true</result>' : '<result>false</result>';
				$result = '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n<manifest>{$result}</manifest>";
				break;

			case 'favorite'   :   // 顯示或隱藏我的最愛
				$res = getSetting('favorite');
				$res = (($res == 'true') || empty($res)) ? 'false' : 'true';
				saveSetting('favorite', $res);
				$result = '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n<manifest></manifest>";
				break;

			case 'add_favorite':  // 將課程加到我的最愛中
				$course_id = getNodeValue($dom, 'course_id');
				$result = add_favorite($course_id);
				break;

			case 'save'       :   // 儲存我的最愛
				$result = saveFolder($dom);
				//$result = '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n<manifest></manifest>";
				break;

			case 'append'     :   // 附屬
			case 'move'       :   // 搬移
				$course_id = getNodeValue($dom, 'course_id');
				$group_id  = getNodeValue($dom, 'group_id');
				$result = moveCourse($action, $group_id, $course_id);
				break;

			case 'delete'     :   // 刪除
				$course_id = getNodeValue($dom, 'course_id');
				$result = deleteCourse($course_id);
				break;

			case 'up'         :   // 上移
			case 'down'       :   // 下移
				$course_id = getNodeValue($dom, 'course_id');
				$result = movePOST($action, $course_id);
				break;

			case 'major_add'  :   // 加選
			case 'major_del'  :   // 退選
				$course_id = getNodeValue($dom, 'course_id');
				$result = elective($action, $course_id);
				break;

			case 'major_reset':   // 重設選課清單
				$result = elective_reset();
				break;

			case 'elective'   :   // 送出選課清單
				$course_id = getNodeValue($dom, 'course_id');
				$result = elective_send($course_id);
				foreach($enResult as $uname => $cids) {
					$msg = '';
					foreach($cids as $cid => $data)
						$msg .= $cid . $data[2] . ',';
					wmSysLog('1100200100', $sysSession->school_id, 0, 0, 'classroom', $_SERVER['PHP_SELF'], $uname . ' major add:' . $msg);
				}
				break;
			case 'ev_delete'   :  // 刪除選課結果
				echo delete_evresult($dom);
				die();
				break;
			case 'drop_elective'   :   // 旁聽生退選課程
				$course_id = getNodeValue($dom, 'course_id');
				$result = drop_elective_send($course_id);
				wmSysLog('1100200200', $sysSession->school_id, 0, 0, 'classroom', $_SERVER['PHP_SELF'], 'Major del:' . $course_id);
				break;
			case 'drop_unelective'	:	// 正式生退選未審核課程
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
