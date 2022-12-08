<?php
	/**
	 * �ҵ{�@�Ψ禡
	 *
	 * @since   2004/07/20
	 * @author  ShenTing Lin
	 * @version $Id: course_lib.php,v 1.1 2010/02/24 02:38:57 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	// require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lang/course_tree.php');

	// �ҵ{�����㪺���W��
	$csFullFileds = array(
		'`course_id`', '`content_id`' , '`caption`'   , '`teacher`'    ,
		'`kind`'     , '`en_begin`'   , '`en_end`'    , '`st_begin`'   ,
		'`st_end`'   , '`status`'     , '`texts`'     , '`url`'        ,
		'`content`'  , '`credit`'     , '`discuss`'   , '`bulletin`'   ,
		'`n_limit`'  , '`a_limit`'    , '`quota_used`', '`quota_limit`',
		'`path`'     , '`login_times`', '`post_times`', '`dsc_times`'  ,
		'`fair_grade`'
	);

	if (!function_exists('Group2XML')) {
		/**
		 * �N�ҵ{�s���ഫ�� XML
		 * @param array $ary : �ҵ{�s�ժ��}�C
		 * @return
		 **/
		function Group2XML($ary) {
			global $sysSession;
			$xmlStrs = '';
			if (is_array($ary)) {
				foreach ($ary as $key => $val) {
					if ($val[2] == 'group') {
						$xmlStrs .= '<courses id="' . $val[0] . '" childs="' . $val[3] . '">' .
						            '<title>' . $val[1][$sysSession->lang] . '</title>' .
						            '</courses>';
					} else {
						$xmlStrs .= '<course id="' . $val[0] . '" childs="' . $val[3] . '">' .
						            '<title>' . $val[1][$sysSession->lang] . '</title>' .
						            '</course>';
					}
				}
			}
			return $xmlStrs;
		}
	}

	/**
	 * ���o�ҫ��w���s�թ��U���s�զC��
	 * @param integer $csid : �ҵ{�s�ժ��s��
	 * @param string  $kind : �s�աB�ҵ{�Ψ��
	 *     group  : �s��
	 *     course : �ҵ{
	 *     all    : ���
	 * @param string $extra : �B�~�� SQL ���O
	 * @param boolean $onlyCount : �u�ݭn����
	 * @return �Ӹs�թ��U���s�ջP�ҵ{
	 **/
	function getCoursesList($csid, $kind='group', $extra='', $onlyCount=false) {
		global $sysConn;

		$csid = checkCourseID($csid);
		if ($csid === false) $csid = 10000000;
		$kind = trim($kind);

		$table = 'WM_term_group LEFT JOIN `WM_term_course` ON `WM_term_group`.`child`=`WM_term_course`.`course_id`';
		// $field = '`WM_term_group`.`child`, `WM_term_course`.`caption`, `WM_term_course`.`kind`';
		$field = '`WM_term_group`.`child`, `WM_term_course`.*';
		switch ($kind) {
			case 'group':
				$where = "`WM_term_group`.`parent`={$csid} AND `WM_term_course`.`kind`='group'";
				break;
			case 'course':
				$where = "`WM_term_group`.`parent`={$csid} AND `WM_term_course`.`kind`='course'";
				break;
			default:
				$where = "`WM_term_group`.`parent`={$csid}";
		}
		if ($onlyCount) {
			// �u�^���`����
			list($cnt) = dbGetStSr($table, 'count(*)', $where . $extra, ADODB_FETCH_NUM);
			return intval($cnt);
		} else {
			$order = ' order by `permute` ASC, `kind` ASC';
			$RS = dbGetStMr($table, $field, $where . $extra . $order, ADODB_FETCH_ASSOC);
		}

		$GroupList = array();
		$CourseList = array();
		if ($RS) {
			while (!$RS->EOF) {
				$cid = checkCourseID($RS->fields['child']);
				if ($cid !== false) {
					$caption = getCaption($RS->fields['caption']);
					if ($RS->fields['kind'] == 'group') {
						// �p�⦹�s�թ��U���L�]�t�s�թνҵ{
						switch ($kind) {
							case 'group':
								$where = "`WM_term_group`.`parent`={$cid} AND `WM_term_course`.`kind`='group'";
								break;
							case 'course':
								$where = "`WM_term_group`.`parent`={$cid} AND `WM_term_course`.`kind`='course'";
								break;
							default:
								$where = "`WM_term_group`.`parent`={$cid} AND `WM_term_course`.`kind` IS NOT NULL";
						}
						list($cnt) = dbGetStSr($table, 'count(*)', $where, ADODB_FETCH_NUM);
						$GroupList[$cid] = array($cid, $caption, $RS->fields['kind'], $cnt, $RS->fields);
					} else {
						if (intval($RS->fields['status']) < 9) {
							$CourseList[$cid] = array($cid, $caption, $RS->fields['kind'], 0, $RS->fields);
						}
					}
				}
				$RS->MoveNext();
			}
		}
		return $GroupList + $CourseList;
	}

	/**
	 * ���o�Ҧ����ҵ{
	 * @param string $kind : �s�աB�ҵ{�Ψ��
	 *     group  : �s��
	 *     course : �ҵ{
	 *     all    : ���
	 * @param string $extra : �B�~�� SQL ���O
	 * @param boolean $onlyCount : �u�ݭn����
	 * @return �������s�սҵ{
	 **/
	function getAllCourses($kind='course', $extra='', $onlyCount=false) {
		$kind = trim($kind);
		switch ($kind) {
			case 'group':
				$where = "`kind`='group' AND `status`<9 order by course_id";
				break;
			case 'course':
				$where = "`kind`='course' AND `status`<9 order by course_id";
				break;
			default:
				$where = "`status`<9";
		}
		$GroupList = array();
		$CourseList = array();
		if ($onlyCount) {
			// �u�^���`����
			list($cnt) = dbGetStSr('WM_term_course', 'count(*)', $where . $extra, ADODB_FETCH_NUM);
			return intval($cnt);
		} else {
			$RS = dbGetStMr('WM_term_course', '*', $where . $extra, ADODB_FETCH_ASSOC);
		}
		if ($RS) {
			while (!$RS->EOF) {
				$caption = getCaption($RS->fields['caption']);
				$cid = intval($RS->fields['course_id']);
				if ($RS->fields['kind'] == 'group') {
					$GroupList[$cid] = array($cid, $caption, $RS->fields['kind'], 0, $RS->fields);
				} else {
					$CourseList[$cid] = array($cid, $caption, $RS->fields['kind'], 0, $RS->fields);
				}
				$RS->MoveNext();
			}
		}
		return $GroupList + $CourseList;
	}

	/**
	 * ���o���w���ҵ{�θs�ճB�󨺭Ӹs�դ�
	 * @param integer $csid : �ҵ{�θs�ժ��s��
	 * @param boolean $rec  : �O�_�n���j���X���s��
	 * @return array $res : ���s�զC��A���@�Ӱ}�C�A�ƦC�覡�q�̥~�h��̤��h
	 **/
	function getParents($csid, $rec=FALSE) {
		$csid = checkCourseID($csid);
		if (($csid === false) || ($csid == 10000000)) return array();
		list($pid) = dbGetStSr('WM_term_group', '`parent`', "`child`={$csid}", ADODB_FETCH_NUM);
		if ($rec) {
			$res = getParents($pid, $rec);
			if ($res === false) $res = array();
			$res[] = $pid;
			return $res;
		} else {
			return array($pid);
		}
	}

	$csCourseData = array();
	function getCourseData($csid) {
		global $csCourseData;

		$csid = checkCourseID($csid);
		if (($csid === false) || ($csid == 10000000)) return array();
		if (isset($csCourseData[$csid])) return $csCourseData[$csid];
		$csCourseData[$csid] = dbGetStSr('WM_term_course', '*', "`course_id`={$csid}", ADODB_FETCH_ASSOC);
		return $csCourseData[$csid];
	}
?>
