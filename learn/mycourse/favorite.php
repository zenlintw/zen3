<?php
	/**
	 * 我的最愛
	 *
	 * @since   2003/06/12
	 * @author  ShenTing Lin
	 * @version $Id: favorite.php,v 1.2 2009-07-16 08:18:10 edi Exp $
	 * @copyright 2003 SUNNET
	 * @備註 : 此支程式專供/learn/mycourse/index.php中所引用
	 **/

	require_once(sysDocumentRoot . '/learn/mycourse/modules/mod_short_link_lib.php');

	if (!aclVerifyPermission(2500400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// 更新討論版未讀文章篇數
	checkFORUM($sysSession->username); // from /learn/mycourse/modules/mod_short_link_lib.php


	// 取得教師身份列表
	$teach = dbGetAssoc('WM_term_major',
	                    'course_id, role&' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) . ' as level',
	                    "username='{$sysSession->username}' and role&" . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']));

	$courses = array();
	// 取出群組中修課的課程編號

	getFavorite();
	$path = MakeUserDir($sysSession->username);
	$filename = $path . '/my_course_favorite.xml';
	if ($xmlvars = domxml_open_file($filename)) {
		$group_id = getSetting('group_id');
		if (($status == 0) || ($status == 2)) {
			$group_id = 10000000;
			saveSetting('group_id', $group_id);
			saveSetting('page_no', '1');
		}
		if (ereg('^USER_', $group_id)) {
			$xpath = '//courses[@id="' . $group_id . '"]/child::course/@id';
		} else {
			$xpath = '/manifest/course/@id';
		}

		$ctx = xpath_new_context($xmlvars);
		$nodes = xpath_eval($ctx, $xpath);

		$cnt = count($nodes->nodeset);

		$cids = array();
		for($i = 0; $i < $cnt; $i++)
		{
			$cids[] = $nodes->nodeset[$i]->value;
		}
		
        // #47282 本指令使得上移下移功能db有異動但畫面最後還是依照id排序
        rsort($cids);
        
		// 三合一的種類
		$type_array = array('homework','exam','questionnaire');

		for ($i = 0; $i < count($cids); $i++) {
			$csid = $cids[$i];
			list($post,$role) = dbGetStSr('WM_term_major', '`post`,`role`', "`username`='{$sysSession->username}' AND `course_id`={$csid}", ADODB_FETCH_NUM);
			$RS = dbGetStSr('WM_term_course', '*', "course_id={$csid}", ADODB_FETCH_ASSOC);
			$sta = intval($RS['status']);
			if (($sta == 0) || ($sta == 9) || ($RS['kind'] == 'group')) continue;
			$isTeach = ($role & $sysRoles['teacher']);
			$isTeach = ($isTeach || ($role & $sysRoles['instructor']));
			$isTeach = ($isTeach || ($role & $sysRoles['assistant']));
			if (($sta == 5) && !$isTeach) continue; // 判斷如果課程狀態為準備中則只限教師才可看到

			$isStudent = ($role & $sysRoles['student']); // 判斷是否為正式生

			// 儲存三合一的未繳作業  未寫考卷  未填問卷
			$QTI_undo = array();
			for ($q_i=0;$q_i < count($type_array);$q_i++){

				// 取得本門課 三合一的 施測中的試卷 的 exam_id
				$ary_action = dbGetCol('WM_qti_' . $type_array[$q_i] . '_test','exam_id','course_id=' . $csid . ' and publish="action"');

				// 判斷本門課是否有 「施測中」的 三合一 begin
				if (count($ary_action) > 0){
					// 取得學員已做的作業、測驗與問卷
					$arydo = array();
					$table = 'WM_qti_' . $type_array[$q_i] . '_result';
					$field = 'DISTINCT `exam_id`';
					$where = '`exam_id` in (' . implode(',',$ary_action) . ") and `examinee`='{$sysSession->username}'";

					$arydo = dbGetCol($table, $field, $where);

					$examinee_perm = array('homework' => 1700400200, 'exam' => 1600400200, 'questionnaire' => 1800300200);
					$p = aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable');

					// for begin
					for ($r_i=0;$r_i < count($ary_action);$r_i++){
						// if begin
						$aclVerified = aclVerifyPermission($examinee_perm[$type_array[$q_i]], $p, $csid, $ary_action[$r_i]);
						if (!$aclVerified || ($aclVerified === 'WM2' && !$isStudent )){
							continue;
						}
						// if end
						if (! in_array($ary_action[$r_i], $arydo)){
							$QTI_undo[$type_array[$q_i]]++;
						}else{
							$QTI_undo[$type_array[$q_i]] = $QTI_undo[$type_array[$q_i]] +0;
						}
					}
					// for end
				}else{
					$QTI_undo[$type_array[$q_i]] = 0;
				}
				// 判斷本門課是否有 「施測中」的 三合一 end
			}
			// 三合一 end

			$courses[] = array(
					$RS['course_id'], $RS['caption'],
					$RS['st_begin'] , $RS['st_end'] ,
					$post           ,
					($QTI_undo['homework']) ? $QTI_undo['homework'] : 0 ,
					($QTI_undo['exam']) ? $QTI_undo['exam'] : 0             ,
					($QTI_undo['questionnaire']) ? $QTI_undo['questionnaire'] : 0           ,
					$RS['status']   , $role         ,
					$isTeach
				);
		}
	}
	$cnt = count($courses);

	// 計算全部的課程數
	$total_course = $cnt;
	// 計算總共分幾頁
	$total_page = ceil($total_course / $lines);
	// 產生下拉換頁選單
	$all_page = range(0, $total_page);
	$all_page[0] = $MSG['page_all'][$sysSession->lang];
	// 設定下拉換頁選單顯示第幾頁
	$setting_no = intval(getSetting('page_no'));
	//$setting_no = empty($setting_no) ? $total_page : $setting_no;
	$page_no = isset($_POST['page']) ? intval($_POST['page']) : $setting_no;
	if (($page_no < 0) || ($page_no > $total_page))
		$page_no = $total_page;
	saveSetting('page_no', $page_no);  // 回存設定

	$js = "var total_page={$total_page};";
	$cols = 10;
	showXHTML_script('inline', $js);
	// 列出課程教室 (Begin)
	showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="tabsCourse"');
		showXHTML_tr_B('class="cssTrHead"');
			showXHTML_td('width="760" colspan="' . $cols . '"', $MSG['msg_help_favorite'][$sysSession->lang]);
		showXHTML_tr_E('');
		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td_B('nowrap="nowrap" colspan="' . $cols . '" id="tb1"');
				showXHTML_input('button', 'btnSel1', $MSG['msg_select_all'][$sysSession->lang], '', 'id="btnSel1" onclick="selfunc()"');
				echo '&nbsp;';
				$disable = ($cnt == 0) ? ' disabled="disabled"' : '';
				echo '&nbsp;' . $MSG['page1'][$sysSession->lang];
				showXHTML_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);" style="width: 50px"');
				echo $MSG['page2'][$sysSession->lang];
				showXHTML_input('button', 'fp', $MSG['btn_page_first'][$sysSession->lang], '', 'onclick="go_page(-1)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				showXHTML_input('button', 'pp', $MSG['btn_page_prev'][$sysSession->lang] , '', 'onclick="go_page(-2)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				showXHTML_input('button', 'np', $MSG['btn_page_next'][$sysSession->lang] , '', 'onclick="go_page(-3)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				showXHTML_input('button', 'lp', $MSG['btn_page_last'][$sysSession->lang] , '', 'onclick="go_page(-4)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				echo '&nbsp;&nbsp;' . $MSG['msg_group'][$sysSession->lang];
				showXHTML_input('button', '', $MSG['btn_append'][$sysSession->lang], '', 'class="cssBtn" onclick="do_func(\'append\')"' . $disable . ' title="' . $MSG['btn_alt_append'][$sysSession->lang] . '"');
				showXHTML_input('button', '', $MSG['btn_move'][$sysSession->lang], '', 'class="cssBtn" onclick="do_func(\'move\')"'   . $disable . ' title="' . $MSG['btn_alt_move'][$sysSession->lang] . '"');
				showXHTML_input('button', '', $MSG['btn_remove'][$sysSession->lang], '', 'class="cssBtn" onclick="do_func(\'delete\')"' . $disable . ' title="' . $MSG['btn_alt_remove'][$sysSession->lang] . '"');
				#47282 Chrome  強制依照id排序，因此上移下移功能移除
                // showXHTML_input('button', '', $MSG['btn_move_up'][$sysSession->lang], '', 'class="cssBtn" onclick="do_func(\'up\')"'     . $disable . ' title="' . $MSG['btn_alt_move_up'][$sysSession->lang] . '"');
				// showXHTML_input('button', '', $MSG['btn_move_down'][$sysSession->lang], '', 'class="cssBtn" onclick="do_func(\'down\')"'   . $disable . ' title="' . $MSG['btn_alt_move_down'][$sysSession->lang] . '"');
			showXHTML_td_E('');
		showXHTML_tr_E('');

		showXHTML_tr_B('class="cssTrHead"');
			showXHTML_td_B('nowrap="nowrap" align="center"');
				// showXHTML_input('checkbox', 'selBtn', '', '', 'id="ck" onclick="select_func(this.checked); event.cancelBubble=true;" title="' . $MSG['btn_alt_sel_all_cancel'][$sysSession->lang] . '"');
				showXHTML_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');
			showXHTML_td_E('');
			if ($sysSession->env == 'academic') {
				// 除了管理者環境外，一律不顯示課程編號
				showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_course_id'][$sysSession->lang]);
			}
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_course_name'][$sysSession->lang]);
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_study'][$sysSession->lang]);
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_status'][$sysSession->lang]);
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_new_post'][$sysSession->lang]);
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_nowrite_homework'][$sysSession->lang]);
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_nowrite_exam'][$sysSession->lang]);
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_nowrite_questionnaire'][$sysSession->lang]);
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_level'][$sysSession->lang]);
		showXHTML_tr_E('');

		if ($cnt <= 0) {
			$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
			showXHTML_tr_B($col);
				showXHTML_td_B('nowrap="nowrap" colspan="' . $cols . '"');
					echo $MSG['msg_no_favorite_course'][$sysSession->lang];
				showXHTML_td_E('');
			showXHTML_tr_E('');
		}

		if ($page_no == 0) {
			$begin = 0;
			$end   = $cnt;
		} else {
			$begin = intval($page_no - 1) * $lines;
			$end   = intval($page_no) * $lines;
			if ($begin < 0)  $begin = 0;
			if ($end > $cnt) $end   = $cnt;
		}

		$today = intval(date('Ymd'));
		for ($i = $begin; $i < $end; $i++) {
			$val = $courses[$i];
			$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
			showXHTML_tr_B($col);
				showXHTML_td_B('nowrap="nowrap" align="center"');
					showXHTML_input('checkbox', 'major_csid[]', $val[0], '', 'onclick="SelCourse(this); event.cancelBubble=true;"');
				showXHTML_td_E('');
				if ($sysSession->env == 'academic') {
					// 除了管理者環境外，一律不顯示課程編號
					showXHTML_td('nowrap="nowrap" align="center"', $val[0]);
				}
				$csid   = intval($val[0]);
				$lang   = getCaption($val[1]);
				$d1     = empty($val[2]) ? 0 : str_replace('-', '', $val[2]);
				$d2     = empty($val[3]) ? '99991231' : str_replace('-', '', $val[3]);
				$d1     = intval($d1);
				$d2     = intval($d2);
				$val[8] = intval($val[8]);
				$role   = intval($val[9]);
				$ary    = array(1, 2, 3, 4);
				$ready  = true; // 是否可進入課程
				// 檢查具不具備教師、講師或助教的身份
				$ready  = $val[10];
				// 檢查狀態
				switch ($val[8]) {
					case 1: // 課程狀態：可旁聽
						$ready = !empty($role);
						break;
					case 2: // 課程狀態：可旁聽，限時
						if (!$ready) {
							$ready = (($d1 <= $today) && ($today <= $d2));
						}
						break;
					case 3: // 課程狀態：不可旁聽
						if (!$ready) {
							$ready = ($role & $sysRoles['student']); // 檢查身份
						}
						break;
					case 4: // 課程狀態：不可旁聽，限時
						if (!$ready) {
							// 必須是正式生才可進入
							$ready = ($role & $sysRoles['student']);
							$ready = ($ready && ($d1 <= $today) && ($today <= $d2));
						}
						break;
					case 5: // 課程狀態：準備中
						break;
					default:
						$ready = false;
				}
				
				$caption = fetchTitle($lang);
				if ($ready) {
					$title = '<div style="width: 220px; overflow: hidden;" title="' . $caption . '"><a href="javascript:;" onclick="return false;" class="cssAnchor">' . $caption . '</a></div>';
					$nEnv = $sysSession->env == 'teach' ? 2 : 1;
					showXHTML_td('nowrap="nowrap" onclick="parent.chgCourse(' . $val[0] . ', '.$nEnv.', 1);"', $title);
				} else {
					$title = '<div style="width: 220px; overflow: hidden;" title="' . $caption . '">' . $caption . '</div>';
					showXHTML_td('nowrap="nowrap"', $title);
				}

				$st = $MSG['from2'][$sysSession->lang] . (empty($val[2]) ? $MSG['now'][$sysSession->lang] : $val[2]) . '<br>' .
					  $MSG['to2'][$sysSession->lang] . (empty($val[3]) ? $MSG['forever'][$sysSession->lang] : $val[3]);
				showXHTML_td('nowrap="nowrap"', $st);
				showXHTML_td('nowrap="nowrap"', divMsg(100, $cs_status[$val[8]]));
				showXHTML_td('nowrap="nowrap" align="center"', $ready ? ('<a href="/learn/my_forum.php" class="cssAnchor" >' . $val[4] . '</a>') : '--');
				// 作業
				showXHTML_td('nowrap="nowrap" align="center"', $ready ? ('<a href="/learn/my_homework.php" class="cssAnchor">' . $val[5] . '</a>') : '--');
				// 測驗
				showXHTML_td('nowrap="nowrap" align="center"', $ready ? ('<a href="/learn/my_exam.php" class="cssAnchor">' . $val[6] . '</a>') : '--');
				// 問卷
				showXHTML_td('nowrap="nowrap" align="center"', $ready ? $val[7] : '--');

				// 身份
				$ary = array();

				if ($role & $sysRoles['teacher'])    $ary[] = $MSG['teacher'][$sysSession->lang];
				if ($role & $sysRoles['instructor']) $ary[] = $MSG['instructor'][$sysSession->lang];
				if ($role & $sysRoles['assistant'])  $ary[] = $MSG['assistant'][$sysSession->lang];
				if ($role & $sysRoles['student'])    $ary[] = $MSG['student'][$sysSession->lang];
				if ($role & $sysRoles['auditor'])    $ary[] = $MSG['auditor'][$sysSession->lang];

				showXHTML_td('nowrap="nowrap"', implode(', ', $ary));
			showXHTML_tr_E('');
		}

		// 第二排的工具列
		$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
		showXHTML_tr_B($col);
			showXHTML_td_B('nowrap="nowrap" colspan="' . $cols . '" ');
				showXHTML_input('button', 'btnSel2', $MSG['msg_select_all'][$sysSession->lang], '', 'id="btnSel2" onclick="selfunc()"');
				echo '&nbsp;';
				$disable = ($cnt == 0) ? ' disabled="disabled"' : '';
				echo '&nbsp;' . $MSG['page1'][$sysSession->lang];
				showXHTML_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);" style="width: 50px"');
				echo $MSG['page2'][$sysSession->lang];
				showXHTML_input('button', 'fp', $MSG['btn_page_first'][$sysSession->lang], '', 'onclick="go_page(-1)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				showXHTML_input('button', 'pp', $MSG['btn_page_prev'][$sysSession->lang] , '', 'onclick="go_page(-2)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				showXHTML_input('button', 'np', $MSG['btn_page_next'][$sysSession->lang] , '', 'onclick="go_page(-3)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				showXHTML_input('button', 'lp', $MSG['btn_page_last'][$sysSession->lang] , '', 'onclick="go_page(-4)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				echo '&nbsp;&nbsp;' . $MSG['msg_group'][$sysSession->lang];
				showXHTML_input('button', '', $MSG['btn_append'][$sysSession->lang], '', 'class="cssBtn" onclick="do_func(\'append\')"' . $disable . ' title="' . $MSG['btn_alt_append'][$sysSession->lang] . '"');
				showXHTML_input('button', '', $MSG['btn_move'][$sysSession->lang], '', 'class="cssBtn" onclick="do_func(\'move\')"'   . $disable . ' title="' . $MSG['btn_alt_move'][$sysSession->lang] . '"');
				showXHTML_input('button', '', $MSG['btn_remove'][$sysSession->lang], '', 'class="cssBtn" onclick="do_func(\'delete\')"' . $disable . ' title="' . $MSG['btn_alt_remove'][$sysSession->lang] . '"');
				#47282 Chrome 強制依照id排序，因此上移下移功能移除
                // showXHTML_input('button', '', $MSG['btn_move_up'][$sysSession->lang], '', 'class="cssBtn" onclick="do_func(\'up\')"'     . $disable . ' title="' . $MSG['btn_alt_move_up'][$sysSession->lang] . '"');
				// showXHTML_input('button', '', $MSG['btn_move_down'][$sysSession->lang], '', 'class="cssBtn" onclick="do_func(\'down\')"'   . $disable . ' title="' . $MSG['btn_alt_move_down'][$sysSession->lang] . '"');

			showXHTML_td_E('');
		showXHTML_tr_E('');
	showXHTML_table_E('');
	// 列出課程教室 (End)
?>
