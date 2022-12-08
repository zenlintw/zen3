<?php
	/**
	 * 課程共用函式
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

	// 課程中完整的欄位名稱
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
		 * 將課程群組轉換成 XML
		 * @param array $ary : 課程群組的陣列
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
	 * 取得所指定的群組底下的群組列表
	 * @param integer $csid : 課程群組的編號
	 * @param string  $kind : 群組、課程或兩者
	 *     group  : 群組
	 *     course : 課程
	 *     all    : 兩者
	 * @param string $extra : 額外的 SQL 指令
	 * @param boolean $onlyCount : 只需要筆數
	 * @return 該群組底下的群組與課程
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
			// 只回傳總筆數
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
						// 計算此群組底下有無包含群組或課程
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
	 * 取得所有的課程
	 * @param string $kind : 群組、課程或兩者
	 *     group  : 群組
	 *     course : 課程
	 *     all    : 兩者
	 * @param string $extra : 額外的 SQL 指令
	 * @param boolean $onlyCount : 只需要筆數
	 * @return 全部的群組課程
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
			// 只回傳總筆數
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
	 * 取得指定的課程或群組處於那個群組中
	 * @param integer $csid : 課程或群組的編號
	 * @param boolean $rec  : 是否要遞迴取出父群組
	 * @return array $res : 父群組列表，為一個陣列，排列方式從最外層到最內層
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
