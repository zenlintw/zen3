<?php
	/**
	 * 課程共用函數
	 *
	 * @since   2004/11/16
	 * @author  ShenTing Lin
	 * @version $Id: course_lib.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/course_manage.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	if (!function_exists('divMsg')) {
		/**
		 * 處理資料，過長的部份隱藏
		 * @param integer $width   : 要顯示的寬度
		 * @param string  $caption : 顯示的文字
		 * @param string  $title   : 浮動的提示文字，若沒有設定，則跟 $caption 依樣
		 * @return string : 處理後的文字
		 **/
		function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
			if (empty($title)) $title = $caption;
			$wd = is_numeric($width) ? intval($width) . 'px' : $width;
			return $without_title ? ('<div style="width: ' . $wd . '; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $wd . '; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
		}
	}

	if (!function_exists('addTermPath')) {
		function addTermPath($course_id) {
			$course_id = checkCourseID($course_id);
			list($serial) = dbGetStSr('WM_term_path', 'MAX(serial) AS serial', "course_id={$course_id}", ADODB_FETCH_NUM);
			if (empty($serial)) {
				$serial = 1;
			} else {
				$serial++;
			}
			$RS = dbNew('WM_term_path', 'course_id, serial, content', "{$course_id}, {$serial}, ''");
			return $RS;
		}
	}

	if (!function_exists('addBoards')) {
		/**
		 * 建立討論板
		 **/
		function addBoards($course_id, $bname) {
			global $sysConn, $sysSession;

			$course_id = checkCourseID($course_id);
			list($cnt) = dbGetStSr('WM_bbs_boards', 'count(*) as cnt', '1', ADODB_FETCH_NUM);
			if ($cnt == 0) {
				$RS = dbNew('WM_bbs_boards', 'board_id', '1000000000');
			}

			$boardName = addslashes(serialize($bname));
			$board_id = 0;
			// 建立討論板
			$RS = dbNew('WM_bbs_boards', 'bname, owner_id', "'{$boardName}', {$course_id}");
			if ($RS) {
				$board_id = $sysConn->Insert_ID();

				// 建立討論板存放夾檔的目錄
				$CoursePath ="/base/{$sysSession->school_id}/course/{$course_id}/board/{$board_id}";
				@mkdir(sysDocumentRoot . $CoursePath, 0755);

				// 加入 WM_term_subject
				dbNew('WM_term_subject','course_id,board_id',"$course_id, $board_id");
			}
			return $board_id;
		}
	}


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
				foreach ($ary as $val) {
					if ($val[2] == 'group') {
						$xmlStrs .= '<courses id="' . $val[0] . '" childs="' . $val[3] . '">';
						$xmlStrs .= '<title>' . $val[1][$sysSession->lang] . '</title>';
						$xmlStrs .= '</courses>';
					} else {
						$xmlStrs .= '<course id="' . $val[0] . '" childs="' . $val[3] . '">';
						$xmlStrs .= '<title>' . $val[1][$sysSession->lang] . '</title>';
						$xmlStrs .= '</course>';
					}
				}
			}
			return $xmlStrs;
		}
	}

	/**
	 * 將所有課程群組轉換成 XML
	 * @param integer $csid : 課程群組的編號
	 * @return
	 **/
	function allGroup2XML($csid, $enc=true, $kind='all') {
		global $sysSession;
		$res = '';
		$gp = getCoursesList($csid, $kind,'',false,false,true);
		$locale = str_replace('_', '-', strtolower($sysSession->lang));
		if (is_array($gp)) {
			foreach ($gp as $val) {
				$id = ($enc) ? sysEncode($val[0]) : $val[0];
				if ($val[2] == 'group') {
					$res .= '<courses id="' . $id . '">';
					$res .= '<title default="' . $sysSession->lang . '">';
					$res .= '<big5>' . $val[1]['Big5'] . '</big5>';
					$res .= '<gb2312>' . $val[1]['GB2312'] . '</gb2312>';
					$res .= '<en>' . $val[1]['en'] . '</en>';
					$res .= '<euc-jp>' . $val[1]['EUC-JP'] . '</euc-jp>';
					$res .= '<user-define>' . $val[1]['user_define'] . '</user-define>';
					$res .= '</title>';
					$res .= allGroup2XML($val[0], $enc, $kind);
					$res .= '</courses>';
				} else {
					$res .= '<course id="' . $id . '">';
					$res .= '<title default="' . $sysSession->lang . '">';
					$res .= '<big5>' . $val[1]['Big5'] . '</big5>';
					$res .= '<gb2312>' . $val[1]['GB2312'] . '</gb2312>';
					$res .= '<en>' . $val[1]['en'] . '</en>';
					$res .= '<euc-jp>' . $val[1]['EUC-JP'] . '</euc-jp>';
					$res .= '<user-define>' . $val[1]['user_define'] . '</user-define>';
					$res .= '</title>';
					$res .= '</course>';
				}
			}
		}
		return $res;
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
	 * @param boolean $selPortal : 是否抓取portal群組
	 * @return 該群組底下的群組與課程
	 **/
	function getCoursesList($csid, $kind='group', $extra='', $onlyCount=false, $selPortal=false, $edit=false ) {
		global $sysConn,$sysSession;
 
        if ($selPortal)	{//抓取portal的課程群組
            $db = sysDBprefix.portal_school_id;
        } else {//抓取本校的課程群組
            $db = sysDBprefix.$sysSession->school_id;
        }	
		
		$csid = checkCourseID($csid);
		if ($csid === false) $csid = 10000000;
		$kind = trim($kind);

		$table = ''.$db.'.WM_term_group LEFT JOIN '.$db.'.`WM_term_course` ON `WM_term_group`.`child`=`WM_term_course`.`course_id`';
		// $field = '`WM_term_group`.`child`, `WM_term_course`.`caption`, `WM_term_course`.`kind`';
		$field = '`WM_term_group`.`child`, `WM_term_course`.*';
		switch ($kind) {
			case 'group':
				$where = "`WM_term_group`.`parent`={$csid} AND `WM_term_course`.`course_id`!=10000000 AND `WM_term_course`.`kind`='group'";
				break;
			case 'course':
				$where = "`WM_term_group`.`parent`={$csid} AND `WM_term_course`.`course_id`!=10000000 AND `WM_term_course`.`kind`='course'";
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
					if($edit){
						$caption = old_getCaption($RS->fields['caption']);
					}else{
						$caption = getCaption($RS->fields['caption']);
					}
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
				$cid = checkCourseID($RS->fields['course_id']);
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
	 * 取得不在任何群組中的課程
	 * @return void
	 **/
	function getNoneGPCourse() {
		$cs = getAllCourses();
		$RS = dbGetStMr('WM_term_group', '`child`', "1", ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$cs[$RS->fields['child']] = null;
				$RS->MoveNext();
			}
		}
		$res = array();
		foreach ($cs as $key => $val) {
			if (is_null($val)) continue;
			$res[$key] = $val;
		}
		return $res;
	}

	/**
	 * 取得指定的課程或群組處於那個群組中
	 * @param integer $gid : 課程或群組的編號
	 * @param boolean $rec  : 是否要遞迴取出父群組
	 * @return array $res : 父群組列表，為一個陣列，排列方式從最外層到最內層
	 **/
	function getParents($gid, $rec=FALSE) {
		$gid = checkCourseID($gid);
		if (($gid === false) || ($gid == 10000000)) return array();
		list($pid) = dbGetStSr('WM_term_group', '`parent`', "`child`={$gid}", ADODB_FETCH_NUM);
		if ($rec) {
			$res = getParents($pid, $rec);
			if ($res === false) $res = array();
			$res[] = $pid;
			return $res;
		} else {
			return array($pid);
		}
	}

	/**
	 * 取得指定的課程處於那些群組中
	 * @param integer $csid : 課程編號
	 * @return array $res : 父群組列表，為一個陣列
	 **/
	function getCourseParents($csid, $enc=FALSE) {
		global $sysSession;

		$gp = array();
		$csid = checkCourseID($csid);
		if ($csid === false) return $gp;
		$RS = dbGetStMr('WM_term_group', '`parent`', "`child`={$csid}", ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$pid = ($enc) ? sysEncode($RS->fields['parent']) : $RS->fields['parent'];
				$ary = getCourseData($RS->fields['parent']);
				if (($ary['kind'] == 'group') && (intval($ary['status']) < 9)) {
					$t = getCaption($ary['caption']);
					$gp[$pid] = $t;
				}
				$RS->MoveNext();
			}
		}
		return $gp;
	}
	
	/**
	 * 取得指定的課程處於那些平台群組中
	 * @param integer $csid : 課程編號
	 * @return array $res : 父群組列表，為一個陣列
	 **/
	function getCoursePortalParents($csid, $enc=FALSE) {
		global $sysSession;

		$gp = array();
		$csid = checkCourseID($csid);
		if ($csid === false) return $gp;
		$RS = dbGetStMr(''.sysDBname.'.CO_all_group', '`parent`', "`child`={$csid} AND school={$sysSession->school_id} ", ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$pid = ($enc) ? sysEncode($RS->fields['parent']) : $RS->fields['parent'];
				$ary = getCourseData($RS->fields['parent'],true);
				if (($ary['kind'] == 'group') && (intval($ary['status']) < 9)) {
					$t = getCaption($ary['caption']);
					$gp[$pid] = $t;
				}
				$RS->MoveNext();
			}
		}
		return $gp;
	}

	/**
	 * 將指定的課程放到指定的群組中
	 * @param integer $csid : 課程編號
	 * @param array   $gids : 課程群組編號
	 * @return integer $cnt : 更新了幾個群組
	 **/
	function setCourse2Group($csid, $gids) {
		global $_SERVER, $sysConn, $sysSession;

		$csid = checkCourseID($csid);
		if ($csid === false) return false;
		if (!is_array($gids)) return false;

		$org = array(); // 已經存在的
		$ary = getCourseParents($csid);
		$cnt = 0;
		// 先移除
		foreach ($ary as $key => $val) {
			if (!in_array($key, $gids))
			{
				dbDel('WM_term_group', "`parent`={$key} AND `child`={$csid}");
				if ($sysConn->Affected_Rows()) $cnt++;
			}
			else
				$org[] = $key;
		}
		// 在新增
		foreach ($gids as $val) {
			if (in_array($val, $org)) continue;
			// 取出最大的 permute 並且加一
			list($permute) = dbGetStSr('WM_term_group', 'MAX(`permute`) as cnt', "parent={$val}", ADODB_FETCH_NUM);
			$permute++;
			// 儲存
			dbNew('WM_term_group', '`parent`, `child`, `permute`', "{$val}, {$csid}, {$permute}");
			if ($sysConn->Affected_Rows()) $cnt++;
		}
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], '儲存課程跟群組的關係到資料庫中');
		return $cnt;
	}
	
	/**
	 * 將指定的課程放到指定的平台群組中
	 * @param integer $csid : 課程編號
	 * @param array   $gids : 課程群組編號
	 * @return integer $cnt : 更新了幾個群組
	 **/
	function setCourse2pGroup($csid, $gids) {
		global $_SERVER, $sysConn, $sysSession;

		$csid = checkCourseID($csid);
		if ($csid === false) return false;
		if (!is_array($gids)) return false;

		$org = array(); // 已經存在的
		$ary = getCoursePortalParents($csid);
		$cnt = 0;
		// 先移除
		foreach ($ary as $key => $val) {
			if (!in_array($key, $gids))
			{
				dbDel(''.sysDBname.'.CO_all_group', "`parent`={$key} AND `child`={$csid} AND school={$sysSession->school_id}");
				if ($sysConn->Affected_Rows()) $cnt++;
			}
			else
				$org[] = $key;
		}
		// 在新增
		foreach ($gids as $val) {
			if (in_array($val, $org)) continue;
			// 取出最大的 permute 並且加一
			list($permute) = dbGetStSr(''.sysDBname.'.CO_all_group', 'MAX(`permute`) as cnt', "parent={$val}", ADODB_FETCH_NUM);
			$permute++;
			// 儲存
			dbNew(''.sysDBname.'.CO_all_group', '`parent`, `child`, `permute`, `school`', "{$val}, {$csid}, {$permute},{$sysSession->school_id}");
			if ($sysConn->Affected_Rows()) $cnt++;
		}
		//wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], '儲存課程跟平台群組的關係到資料庫中');
		return $cnt;
	}

	/**
	 * 取得課程的詳細資料
	 * @param integer $csid : 課程編號
	 * @return array $csCourseData : 課程資料
	 **/
	$csCourseData = array();
	function getCourseData($csid,$selPortal=false) {
		global $csCourseData, $sysRoles, $sysSession;
        
	    if ($selPortal)	{//抓取portal的課程
            $db = sysDBprefix.portal_school_id;
        } else {//抓取本校的課程
            $db = sysDBprefix.$sysSession->school_id;
        }
		
		$csid = checkCourseID($csid);
		if (($csid === false) || ($csid == 10000000)) return array();
		if (isset($csCourseData[$csid])) return $csCourseData[$csid];
		$csCourseData[$csid] = dbGetStSr(''.$db.'.WM_term_course', '*', "`course_id`={$csid}", ADODB_FETCH_ASSOC);

		// 開課教師 (Begin)
		$teach = array(
			'teacher'    => array(),
			'instructor' => array(),
			'assistant'  => array(),
		);
		$RS = dbGetStMr('WM_term_major', 'username, role&' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) . ' as level', "`course_id`={$csid} group by username having level > 0", ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$user = getUserDetailData($RS->fields['username']);
				$detl  = (!empty($user['email'])) ? ("<a href=\"mailto:{$user['email']}\" class=\"cssAnchor\" onclick=\"event.cancelBubble=true;\">{$RS->fields['username']}</a>") : $RS->fields['username'];
				$detl .= '&nbsp;';
				$detl .= (!empty($user['homepage'])) ? ("(<a href=\"{$user['homepage']}\" class=\"cssAnchor\" target=\"_blank\" onclick=\"event.cancelBubble=true;\">{$user['realname']}</a>)") : '(' . $user['realname'] . ')';
				$teach[array_search($RS->fields['level'],$sysRoles)][] = $detl;
				$RS->MoveNext();
			}
		}
		$csCourseData[$csid]['real_teacher'] = $teach;
		// 開課教師 (End)
		return $csCourseData[$csid];
	}

	/**
	 * getContentList()
	 *     取得所有教材的列表
	 * @param boolean $val : 要不要取得被 disable 的教材
	 * @return 教材列表
	 **/
	function getContentList($val=FALSE) {
		global $sysConn, $sysSession;
		$where = ' AND status!="disable"';
		if ($val) $where = '';
		$RS = dbGetStMr('WM_content', 'content_id, caption,content_sn', 'content_id>100000' . $where, ADODB_FETCH_ASSOC);
		$content = array();
		if ($RS) {
			while (!$RS->EOF) {
				$lang = getCaption($RS->fields['caption']);
				$content[$RS->fields['content_id']] = "({$RS->fields['content_sn']}) " . $lang[$sysSession->lang];
				$RS->MoveNext();
			}
		}
		return $content;
	}

	/**
	 * 顯示開始與結束日期
	 * @param string  $val1   : 開始日期
	 * @param string  $val2   : 結束日期
	 * @param string  $div    : 兩個日期之間要用什麼樣的切割字元
	 * @param boolean $divMsg : 是否要隱藏過長的字串。true: 要，false: 不要
	 * @param string  $width  : 要顯示多長的字串，需開啟 $divMsg 才有作用
	 * @return string 課程資料
	 **/
	function showDatetime($val1, $val2, $div='<br />') {
		global $sysSession, $sysConn, $MSG;

		$time1 = $sysConn->UnixTimeStamp($val1);
		$time2 = $sysConn->UnixTimeStamp($val2);
		if ($val1 == '0000-00-00') $val1 = 0;
		if ($val2 == '0000-00-00') $val2 = 0;

		$res = $MSG['from2'][$sysSession->lang] . (empty($val1) ? $MSG['now'][$sysSession->lang]     : date('Y-m-d', $time1)) . $div .
				 $MSG['to2'][$sysSession->lang]   . (empty($val2) ? $MSG['forever'][$sysSession->lang] : date('Y-m-d', $time2));

		return $res;
	}

	function showRemain($val, $limit) {
	    if ($limit == 0)
	        $str = $per = 0;
		else
		{
			$remain = intval($limit) - intval($val);
			$per    = round($remain / $limit, 2) * 100;
			$str    = number_format($remain);
		}
		return ($str > 0 ? $str : 0) . 'KB('. ($per > 0 ? $per : 0) .'%)';
	}

    function showRemainByGb($val, $limit) {
	    if ($limit == 0)
	        $str = $per = 0;
		else
		{
			$remain = intval($limit) - intval($val);
			$per    = round($remain / $limit, 2) * 100;
            $remain = number_format($remain/1024/1024, 2);
			$str    = (($remain<0)?0:$remain).'/'.number_format($limit/1024/1024, 2);
		}
		return ($str !== 0 ? $str : 0) . 'GB('. ($per > 0 ? $per : 0) .'%)';
	}
	
	function showRemainByMb($val, $limit) {
	    if ($limit == 0)
	        $str = $per = 0;
	    else
	    {
	        $remain = intval($limit) - intval($val);
	        $per    = round($remain / $limit,2) * 100;
	        $remain = number_format($remain/1024);
	        $str    = (($remain<0)?0:$remain).'/'.number_format($limit/1024);
	    }
	    return ($str !== 0 ? $str : 0) . 'MB('. ($per > 0 ? $per : 0) .'%)';
	}

	function showUsage($val) {
		$str  = number_format($val);
		$str .= ' KB';
		return $str;
	}
	
        function showUsageByMb($val)
        {
            $val = number_format($val / 1024);
            $str = (($val < 0) ? 0 : $val);

            return ($str !== 0 ? $str : 0) . 'MB';
        }

	/**
	 * 取得 XML 格式的課程資料
	 * @param integer $csid : 課程編號
	 * @return string 課程資料
	 **/
	function getXMLCourseData($csid) {
		global $sysSession, $MSG;
		$csid = checkCourseID($csid);
		if ($csid === FALSE) return '<manifest></manifest>';
		$data = getCourseData($csid);
		// 課程名稱
		$lang = getCaption($data['caption']);
		// 教材名稱
		$cid = intval($data['content_id']);
		list($caption) = dbGetStSr('WM_content', 'caption', "content_id={$cid}", ADODB_FETCH_NUM);
		$content = unserialize($caption);
		// 報名日期
		$enroll = htmlspecialchars(showDatetime($data['en_begin'], $data['en_end'], ' '));
		// 上課日期
		$study = htmlspecialchars(showDatetime($data['st_begin'], $data['st_end'], ' '));
		// 已使用空間
		$quota_limit = intval($data['quota_limit']);
		if (empty($quota_limit)) $quota_limit = 1;
		$quota_used = intval($data['quota_used']);
		$quota_used_percent = round($quota_used / $quota_limit, 4) * 100;

		$data['quota_remain_percent'] = showRemain($data['quota_used'], $data['quota_limit']);
		$data['quota_used']  = showUsage(intval($data['quota_used']));
		$data['quota_limit'] = showUsage(intval($data['quota_limit']));

		// 審核規則
		$review    = getReviewRuleList($csid);
		$review_id = getReviewSerial($csid);

		$data['teacher'] = htmlspecialchars($data['teacher']);
		$data['texts']   = htmlspecialchars($data['texts']);
		$data['content'] = htmlspecialchars($data['content']);
		$data['url']     = htmlspecialchars($data['url']);

		//  及格成績
		$data['fair_grade'] = floatval($data['fair_grade']);
		// 所屬群組
		$gp = array();
		$RS = dbGetStMr('WM_term_group', '`parent`', "`child`={$csid}", ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$ary = getCourseData($RS->fields['parent']);
				if (($ary['kind'] == 'group') && (intval($ary['status']) < 9)) {
					$t = getCaption($ary['caption']);
					$gp[] = $t[$sysSession->lang];
				}
				$RS->MoveNext();
			}
		}
		$gps = htmlspecialchars(@implode(', ', $gp));
		// 開課教師
		$teach      = $data['real_teacher'];
		$teacher    = htmlspecialchars(implode(', ', $teach['teacher']));
		$instructor = htmlspecialchars(implode(', ', $teach['instructor']));
		$assistant  = htmlspecialchars(implode(', ', $teach['assistant']));

		$csid = sysEncode($data['course_id']);
		// 輸出 XML
		$result .= <<< BOF
	<course id="{$csid}" checked="false">
		<title>
			<big5>{$lang['Big5']}</big5>
			<gb2312>{$lang['GB2312']}</gb2312>
			<en>{$lang['en']}</en>
			<euc_jp>{$lang['EUC-JP']}</euc_jp>
			<euc-jp>{$lang['EUC-JP']}</euc-jp>
			<user_define>{$lang['user_define']}</user_define>
			<user-define>{$lang['user_define']}</user-define>
		</title>
		<teacher_view>{$data['teacher']}</teacher_view>
		<content_name>
			<title>
				<big5>{$content['Big5']}</big5>
				<gb2312>{$content['GB2312']}</gb2312>
				<en>{$content['en']}</en>
				<euc_jp>{$content['EUC-JP']}</euc_jp>
				<euc-jp>{$content['EUC-JP']}</euc-jp>
				<user_define>{$content['user_define']}</user_define>
				<user-define>{$content['user_define']}</user-define>
			</title>
		</content_name>
		<enroll>{$enroll}</enroll>
		<study>{$study}</study>
		<status>{$data['status']}</status>
		<review>{$review[$review_id]}</review>
		<texts>{$data['texts']}</texts>
		<url>&lt;a href="{$data['url']}" class="cssAnchor" target="_blank"&gt;{$data['url']}&lt;/a&gt;</url>
		<content>{$data['content']}</content>
		<credit>{$data['credit']}</credit>
		<n_limit>{$data['n_limit']}</n_limit>
		<a_limit>{$data['a_limit']}</a_limit>
		<quota_used>{$data['quota_used']}</quota_used>
		<quota_used_percent>{$quota_used_percent}</quota_used_percent>
		<quota_remain_percent>{$data['quota_remain_percent']}</quota_remain_percent>
		<quota_limit>{$data['quota_limit']}</quota_limit>
		<fair_grade>{$data['fair_grade']}</fair_grade>
		<group>{$gps}</group>
		<teacher>{$teacher}</teacher>
		<instructor>{$instructor}</instructor>
		<assistant>{$assistant}</assistant>
	</course>
BOF;
		// 輸出課程的 XML (End)
		return $result;
	}
// //////////////////////////////////////////////////
	class modCGTree {
		var $encode_id;
		var $js_callback;

		function modCGTree() {
			$this->encode_id = false;
		}

		function add_js_callback($val) {
			$this->js_callback = $val;
		}

		function parseCGList($pid, $indent) {
			global $sysSession, $MSG;

			$txt = '';
			$ary = getCoursesList($pid, 'group');
			if (count($ary) > 0) {
				$ind = '';
				for ($j = ($indent + 1); $j > 0 ; $j--)
					$ind .= '<pre style="width: 15px; display: inline;">&nbsp;&nbsp;</pre>';

				foreach ($ary as $key => $val) {
					$eid = $this->encode_id ? sysEncode($key) : $key;
					$icon = '<img src="/theme/' . $sysSession->theme. '/' . $sysSession->env . '/dot.gif" align="absmiddle" border="0">';
					if ($val[2] == 'group') {
						$c = '[G] ';
						if (intval($val[3]) > 0) $icon = '<img src="/theme/' . $sysSession->theme. '/' . $sysSession->env . '/plus.gif" align="absmiddle" border="0" id="icon_' . $eid . '" alt="' . $MSG['cs_tree_expand'][$sysSession->lang] . '" title="' . $MSG['cs_tree_expand'][$sysSession->lang] . '" onclick="modCGTreeExpand(\'' . $eid . '\');">';
					} else {
						$c = '[C] ';
						$icon = '';
					}

					$uid = uniqid('gp_');
					$txt .= $ind . $icon . '<input type="checkbox" value="' . $eid . '" name="gids[]" id="' . $uid . '"><label for="' . $uid . '"><span class="cssTbBlur" onmouseover="this.className=\'cssTbFocus\'" onmouseout="this.className=\'cssTbBlur\'">' . $c . '<span id="span_' . $eid . '">' . $val[1][$sysSession->lang] . '</span>' . '</span></label><br />';
					if ($val[2] == 'group') {
						$txt .= $this->parseCGList($val[0], $indent + 1);
					}
				}
			}
			if (!empty($txt)) {
				$eid = $this->encode_id ? sysEncode($pid) : $pid;
				$display = ($indent == 0) ? '' : ' style="display: none;"';
				$txt = '<div id="Group_' . $eid . '"' . $display . '>' . $txt . '</div>';
			}
			return $txt;

		}

		function get_result() {
			return $this->parseCGList(10000000, 0);
		}

		function show() {
			global $sysSession, $MSG;

			$modJSGpList = <<< BOF
	var csThemePath = "/theme/{$sysSession->theme}/{$sysSession->env}/";

	var MSG_EXPAND    = "{$MSG['expend'][$sysSession->lang]}";
	var MSG_COLLECT   = "{$MSG['collect'][$sysSession->lang]}";

	function modCGTreeExpand(pid) {
		var obj = document.getElementById("Group_" + pid);
		if (obj != null) {
			if (obj.style.display == "none") {
				obj.style.display = "";
				obj = document.getElementById("icon_" + pid);
				if (obj != null) {
					obj.src = csThemePath + "minus.gif";
					obj.alt = MSG_COLLECT;
					obj.title = MSG_COLLECT;
				}
			} else {
				obj.style.display = "none";
				obj = document.getElementById("icon_" + pid);
				if (obj != null) {
					obj.src = csThemePath + "plus.gif";
					obj.alt = MSG_EXPAND;
					obj.title = MSG_EXPAND;
				}
			}
		}
	}

	function modCGTreeExpandAll(bol) {
		var obj = document.getElementById("tabs_gplist");
		var nodes = null;
		var sta = "";
		var o1 = null, o2 = null;
		sta =

		nodes = obj.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].name == "csgpck")) continue;
			o1 = document.getElementById("Group_" + nodes[i].value);
			if (o1) o1.style.display = (bol) ? "" : "none";
			o2 = document.getElementById("icon_" + nodes[i].value);
			if (o2) {
				if (bol) {
					o2.src = csThemePath + "minus.gif";
					o2.alt = MSG_COLLECT;
					o2.title = MSG_COLLECT;
				} else {
					o2.src = csThemePath + "plus.gif";
					o2.alt = MSG_EXPAND;
					o2.title = MSG_EXPAND;
				}
			}
		}
	}

	/**
	 * 顯示課程群組
	 * @param Array ary : 哪些項目要勾選
	 * @return void
	 **/
	function modCGTreeShow(ary) {
		var sObj = new Object();
		var obj = document.getElementById("tabs_gplist");
		if (obj == null) return false;

		if (!ary instanceof Array) {
			alert("param is not Array");
			return false;
		}
		// Convert Array to Object
		for (var i = 0; i < ary.length; i++) {
			sObj[ary[i]] = true;
		}
		// check item
		var nodes = obj.getElementsByTagName("input");
		if ((nodes != null) && (nodes.length > 0)) {
			for (var i = 0; i < nodes.length; i++) {
				if ((nodes[i].type != "checkbox") || (nodes[i].name == "csgpck")) continue;
				nodes[i].checked = ((typeof sObj[nodes[i].value] != "undefined") && sObj[nodes[i].value]);
			}
		}
		layerAction("tabs_gplist", true);
	}

	function modCGTreeHidden(val) {
		layerAction("tabs_gplist", false);
		if (!val) return;

		var spa = {};
		var ary = [], a1 = [], a2 = [];
		var obj = document.getElementById("tabs_gplist");
		if (obj == null) return;
		var nodes = obj.getElementsByTagName("input");
		if ((nodes != null) && (nodes.length > 0)) {
			for (var i = 0; i < nodes.length; i++) {
				if ((nodes[i].type != "checkbox") || (nodes[i].name == "csgpck")) continue;
				if (nodes[i].checked) {
					spa = document.getElementById("span_" + nodes[i].value);
					a1[a1.length] = nodes[i].value;
					a2[a2.length] = [nodes[i].value, (spa ? spa.innerHTML : "")];
				}
			}
		}
		ary = [a1, a2];
		if (typeof modCGTreeCallBack == "function") modCGTreeCallBack(ary);
		return ary;
	}
BOF;
			showXHTML_script('inline', $modJSGpList . $this->js_callback);
			$ary = array();
			$ary[] = array($MSG['title_group_list'][$sysSession->lang], 'tabs_gplist');
			// $colspan = 'colspan="2"';
			showXHTML_tabFrame_B($ary, 1, 'modGpList', 'tabs_gplist', 'style="display: inline;"', true);
				showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" class="cssTable"');
					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td_B();
							showXHTML_input('button', 'btnExpand1' , $MSG['expend'][$sysSession->lang]    , '', 'onclick="modCGTreeExpandAll(true)" class="cssBtn"');
							showXHTML_input('button', 'btnCollect1', $MSG['collect'][$sysSession->lang]   , '', 'onclick="modCGTreeExpandAll(false)" class="cssBtn"');
							showXHTML_input('button', 'btnOK1'     , $MSG['btn_ok'][$sysSession->lang]    , '', 'onclick="modCGTreeHidden(true)" class="cssBtn"');
							showXHTML_input('button', 'btnCancel1' , $MSG['btn_cancel'][$sysSession->lang], '', 'onclick="modCGTreeHidden(false)" class="cssBtn"');
						showXHTML_td_E();
					showXHTML_tr_E();
					showXHTML_tr_B('class="cssTbBlur"');
						showXHTML_td_B('nowrap="nowrap"');
							echo $this->get_result();
						showXHTML_td_E();
					showXHTML_tr_E();
					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td_B();
							showXHTML_input('button', 'btnExpand2' , $MSG['expend'][$sysSession->lang]    , '', 'onclick="modCGTreeExpandAll(true)" class="cssBtn"');
							showXHTML_input('button', 'btnCollect2', $MSG['collect'][$sysSession->lang]   , '', 'onclick="modCGTreeExpandAll(false)" class="cssBtn"');
							showXHTML_input('button', 'btnOK2'     , $MSG['btn_ok'][$sysSession->lang]    , '', 'onclick="modCGTreeHidden(true)" class="cssBtn"');
							showXHTML_input('button', 'btnCancel2' , $MSG['btn_cancel'][$sysSession->lang], '', 'onclick="modCGTreeHidden(false)" class="cssBtn"');
						showXHTML_td_E();
					showXHTML_tr_E();
				showXHTML_table_E();
			showXHTML_tabFrame_E();
			}
	}

	/**
	 * 取得這門課設定的審核設定
	 * @param int id : 課程代號
	 * @return int 審核序號
	 **/
	function getReviewSerial($id)
	{
		list($flow_serial) = dbGetStSr('WM_review_sysidx', 'flow_serial', 'discren_id="' . $id . '"', ADODB_FETCH_NUM);
		return isSet($flow_serial) ? $flow_serial : -1;
	}

	/**
	 * 取得目前系統設定的審核規則
	 * @param int id : 課程代號
	 * @return array 審核規則列表
	 **/
	function getReviewRuleList($id)
	{
		global $sysConn, $sysSession;
		$syscont = array();
		$RS = dbGetStMr('WM_review_syscont', '*', '1 order by permute ASC', ADODB_FETCH_ASSOC);
		if ($RS)
		{
			if ($RS->RecordCount() != 0)
			{
				while ($RS1 = $RS->FetchRow())
				{
					$tlt_lang = unserialize($RS1['title']);
					$syscont[$RS1['flow_serial']] = $tlt_lang[$sysSession->lang];
				}
			}
		}
		return $syscont;
	}

?>
