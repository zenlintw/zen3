<?php

/**
 * 檢查三合一未做的項目
 * @param string $username : 帳號，假如沒有指定的話就使用目前的身份
 * @param string $type     : 指定測驗、作業或問卷
 *     exam          : 測驗
 *     homework      : 作業
 *     questionnaire : 問卷
 * @return int
 **/
function checkQTI($username = '', $type = 'exam', $course_id=0)
{
    global $sysRoles, $sysSession;
    
    if ($username == '')
        $username = $sysSession->username;
    if ($type == 'homework' || $type == 'peer')
        include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');
    
    $examinee_perm = array(
        'homework' => 1700400200,
        'exam' => 1600400200,
        'questionnaire' => 1800300200,
        'peer' => 1710400200
    );
    $p             = aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable');
    
    $exam_ary = array();
    $RS       = dbGetCourses('C.course_id, C.caption, M.role, C.status', $username, $sysRoles['auditor'] | $sysRoles['student'], 'C.course_id');
    // if begin
    if ($RS) {
        if ($RS->RecordCount() > 0) {
            while ($RS1 = $RS->FetchRow()) {
                // 指定特定課程編號，就只計算此門課
                if ($course_id > 0) {
                    if ($RS1['course_id'] != $course_id) continue;
                }
                
                $stas      = intval($RS1['status']);
                $isTeach   = ($RS1['role'] & ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'])); // 判斷是否有教師身分
                $isStudent = ($RS1['role'] & $sysRoles['student']); // 判斷是否為正式生
                
                if (($stas == 5) && !$isTeach) // 判斷如果課程狀態為準備中則只限教師才可看到
                    {
                    continue;
                }
                
                
                $lang    = getCaption($RS1['caption']);

                // IRS 客製，過濾 IRS 的測驗問卷
                $notIrsWhere = " AND `type` != 5 ";

                // 總測試數
                $exam_RS = dbGetStMr('WM_qti_' . $type . '_test', 'exam_id', 'course_id=' . $RS1['course_id'] . " and publish='action'" . $notIrsWhere, ADODB_FETCH_ASSOC);
                
                if ($exam_RS) {
                    // if begin
                    if ($exam_RS->RecordCount() > 0) {
                        // 課程名稱
                        $exam_ary[$RS1['course_id']]['caption'] = $lang[$sysSession->lang];
                        $blnExam                                = false;
                        $exam_ary[$RS1['course_id']]['total_undo'] = 0;
                        while ($exam_RS1 = $exam_RS->FetchRow()) {
                            $aclVerified = aclVerifyPermission($examinee_perm[$type], $p, $RS1['course_id'], $exam_RS1['exam_id']);
                            if (!$aclVerified || ($aclVerified === 'WM2' && !$isStudent)) {
                                continue; // 如果這個三合一不是指派給我的，就不秀
                            } else {
                                $blnExam = true;
                                // 應做考試
                                $exam_ary[$RS1['course_id']]['total_do']++;
                                
                                if (($type == 'homework'  || $type == 'peer') && isAssignmentForGroup($exam_RS1['exam_id'], $RS1['course_id'])) {
                                    if (!isAlreadySubmittedAssignmentForGroup($exam_RS1['exam_id'], $username, $RS1['course_id']))
                                        $exam_ary[$RS1['course_id']]['total_undo']++;
                                } else {
                                    list($temp_undo) = dbGetStSr('WM_qti_' . $type . '_result', 'count(*)', 'exam_id=' . $exam_RS1['exam_id'] . ' and examinee="' . $username . '"', ADODB_FETCH_NUM);
                                    if ($temp_undo == 0)
                                        $exam_ary[$RS1['course_id']]['total_undo']++;
                                }
                            }
                        }
                        if (!$blnExam)
                            unset($exam_ary[$RS1['course_id']]);
                    } else {
                        continue; // 此門課尚未有試卷
                    }
                    // if end
                }
            }
        }
    }
    return $exam_ary;
}

/** 
 * 取得未作QTI數量
 * @param string $username username
 * @param string $type homework, exam, questionnaire
 * @return int count
 */
function getQTIUndoCount($username, $type)
{
		global $sysSession;

		// 撈出上次統計的資料：未做作業份數、未做測驗份數、上次確認時間
		list($homeworkUndo, $examUndo, $checktime) = dbGetStSr('WM_qti_check_undo','homework,exam,check_time',"username='{$sysSession->username}'",ADODB_FETCH_NUM);

		$check_flag = false;
		$now = time();

		if(empty($checktime))
		{
			// 空值則新增資料
			dbNew('WM_qti_check_undo',"username,check_time","'{$sysSession->username}','{$now}'");
			$check_flag = true;
		}
		else
		{
			// 非空值，若現在與先前確認時間差距過大，則進行重新計算
			$hour = 1;	// 此參數異動時，需同步更新mod_short_link.php的值
			$hour_time = 3600 * $hour;
			if (($now - $checktime) >= $hour_time) {
                $check_flag = true;
            }
		}

		if ($check_flag)
		{
			// 需要重新計算，並回傳數值
            $types = array('exam', 'homework');
            foreach ($types as $t) {
                $ary = checkQTI($username, $t);
                $undo = 0;
                $now = time();
                if (is_array($ary) && count($ary))
                {
                    foreach($ary as $cids)
                        $undo += $cids['total_undo'];
                }

                if ($t == 'exam') {
                    $examUndo = $undo;
                } else if($t == 'homework') {
                    $peer_ary = checkQTI($sysSession->username, 'peer');
                    foreach ($peer_ary as $k => $v) {
                        $undo += $v['total_undo'];
                    }
                    $homeworkUndo = $undo;
                }
            }

            dbSet('WM_qti_check_undo',"exam={$examUndo},homework={$homeworkUndo},check_time='{$now}'","username='{$sysSession->username}'");
		}

        // 不需要重新計算，則直接回傳數值
        if ($type == 'exam') {
            return $examUndo;
        } else if($type == 'homework') {
            return $homeworkUndo;
        }
	}

/**
 * 檢查訊息
 * @param string $username : 帳號
 * @return array
 **/
function checkMessage($username = '')
{
    global $sysSession;
    
    $username = trim($username);
    if (empty($username))
        $username = $sysSession->username;
    // 取得幾封新訊息
    list($cnt) = dbGetStSr('WM_msg_message', 'count(*)', "`folder_id`='sys_inbox' AND `receiver`='{$username}' AND `status`=''", ADODB_FETCH_NUM);
    // 取得收件匣的名稱
    $name = getNameFromID('sys_inbox', $username);
    return array(
        $name,
        $cnt
    );
}

/**
 * 檢查線上訊息
 * @param string $username : 帳號
 * @return array
 **/
function checkIM($username = '')
{
    global $sysSession;
    
    $username = trim($username);
    if (empty($username))
        $username = $sysSession->username;
    // 取得幾則新留言
    list($cnt) = dbGetStSr('WM_im_message', 'count(*)', "`username`='{$sysSession->username}' AND `sorder`=0 AND `talk`=0 AND `saw`='N'", ADODB_FETCH_NUM);
    return $cnt;
}

	/**
	 * 檢查文章
	 * @param string $username : 帳號，假如沒有指定的話就使用目前的身份
	 * @return array
	 **/
	function checkFORUM($username='') {
		global $sysSession, $sysRoles;
			
			list($board,$checktime) = dbGetStSr('WM_board_check_undo','board,check_time',"username='{$sysSession->username}'",ADODB_FETCH_NUM);

			$check_flag = false;
			$now = time();
			if(empty($checktime))
			{
				// 空值則新增資料
				dbNew('WM_board_check_undo',"username,check_time","'{$sysSession->username}','{$now}'");
				$check_flag = true;
			}
			else
			{
				// 非空值，若現在與先前確認時間差距過大，則進行重新計算
				$hour = 1;	// 此參數異動時，需同步更新mod_short_link.php的值
				$hour_time = 3600 * $hour;
				if (($now - $checktime) >= $hour_time) {
					$check_flag = true;
				}
			}
			$total_cnt = 0;
		if ($check_flag)
		{
					$username = trim($username);
					if (empty($username)) $username = $sysSession->username;

					// 取得該學員研讀的課程
					$ary = array();
					$RS = dbGetCourses('C.course_id, C.caption',
									   $username,
									   $sysRoles['auditor']|$sysRoles['student'],
									   'C.course_id');
					if ($RS) {
						while (!$RS->EOF) {
							$RS1 = dbGetStMr('WM_bbs_boards as B left join WM_chat_records as R on B.board_id = R.board_id and B.owner_id = R.owner_id',
											 'B.board_id, B.owner_id',
											 'B.owner_id = "' . $RS->fields['course_id'] .'" and R.board_id is null order by B.owner_id',
											 ADODB_FETCH_ASSOC);
							if ($RS1) {
								// 取得各課程的所有看板(包含議題,群組及討論室記錄版,不包含學校及班級的討論版)
								while (!$RS1->EOF) {
									if (strlen($RS1->fields['owner_id']) == 8)
										$ary[$RS->fields['course_id']]['b'][] = $RS1->fields['board_id'];
									else { // 群組討論版
										$tid = intval(substr($RS1->fields['owner_id'],8,4));
										$gid = intval(substr($RS1->fields['owner_id'],12,4));
										$ary[$RS->fields['course_id']]['q'][] = $RS1->fields['board_id'].','.$tid.','.$gid;
									}
									$RS1->MoveNext();
								}
							}
							$RS->MoveNext();
						}
					}
					// 判斷每門課的討論版內有那些是新文章(for 議題討論及討論室記錄板)
					$table_b = "WM_bbs_posts as a left join WM_bbs_readed as b ON a.board_id=b.board_id and a.node=b.node and b.username='{$username}' and b.type='b'";
					// $table_q = "WM_bbs_collecting as a left join WM_bbs_readed as b ON a.board_id=b.board_id and a.node=b.node and b.username='{$username}' and b.type='q'";
					$total_cnt = 0;
					foreach ($ary as $key => $val) {
						$cntsum = 0;
						if (isset($val['b'])) {	// 有議題討論版
							$num = count($val['b']);
							if ($num > 0) {
								$cnt = 0;
								// 每一個板的新文章
								for ($i=0;$i<$num;$i++) {
									// 判斷此板是否是限定教師助教專用
									list($state) = dbGetStSr('WM_term_subject', 'state', sprintf('course_id=%d and board_id=%d',$key, $val['b'][$i]), ADODB_FETCH_NUM);
									if ($state=='taonly') { // 此板為教師助教專用
										$tcnt = aclCheckRole($username, $sysRoles['teacher'] | $sysRoles['assistant'], $key);	// 是否為教師助教身份
										if (!$tcnt)	continue;
									}
									$where_b = 'a.board_id='.$val['b'][$i] .' and length(a.node)<=18 and b.node is null';
									$where_q = 'a.board_id='.$val['b'][$i] ." and a.node REGEXP '^[0-9]*$' and b.node is null";
									list($bcnt) = dbGetStSr($table_b, 'count(*)', $where_b, ADODB_FETCH_NUM);	// 一般區新文章
									// list($qcnt) = dbGetStSr($table_q, 'count(*)', $where_q, ADODB_FETCH_NUM);	// 精華區新文章
									$qcnt = 0;
									$cnt = $cnt + intval($bcnt) + intval($qcnt);
									// echo $key. ' = '.$val['b'][$i].'  ---'.$bcnt.'  ---'.$qcnt.'<br>';
								}
								$cntsum += $cnt;
								$total_cnt = $total_cnt + $cnt;
								// echo '+++<br>'.$cnt.'<br>';
								// echo 'TOTAL='.$total_cnt.'------------END<br>';
							}
						}
						if (isset($val['q'])) {	// 有群組討論版
							$num = count($val['q']);
							if ($num > 0) {
								$cnt = 0;
								// 每一個板的新文章
								for ($i=0;$i<$num;$i++) {
									$tmp = explode(',',$val['q'][$i]); // 依序是bid,tid及gid
									// 判斷學員是否可以有在此群組討論版中
									list($gcnt) = dbGetStSr('WM_student_div', 'count(*)', "course_id={$key} and group_id=$tmp[2] and team_id=$tmp[1] and username='{$username}'", ADODB_FETCH_NUM);
									if ($gcnt == 0)	continue;
									$where_b = "a.board_id=$tmp[0] and length(a.node)<=18 and b.node is null";
									$where_q = "a.board_id=$tmp[0] and a.node REGEXP '^[0-9]*$' and b.node is null";
									list($bcnt) = dbGetStSr($table_b, 'count(*)', $where_b, ADODB_FETCH_NUM);	// 一般區新文章
									// list($qcnt) = dbGetStSr($table_q, 'count(*)', $where_q, ADODB_FETCH_NUM);	// 精華區新文章
									$qcnt = 0;
									$cnt = $cnt + intval($bcnt) + intval($qcnt);
									// echo 'GROUP== '.$key. ' = '. "$tmp[0]" .'  ---'.$bcnt.'  ---'.$qcnt.'<br>';
								}
								$cntsum += $cnt;
								$total_cnt = $total_cnt + $cnt;
								// echo '+++<br>'.$cnt.'<br>';
								// echo 'TOTAL='.$total_cnt.'------------END<br>';
							}
						}
						/**Modify by Hubert
						 * 我的學習中心->新文章來源：
						 * 一、議題討論（課程公告、課程討論、自訂討論版）的文章；
						 * 二、分組討論的文章；
						 * 三、線上討論結束，轉入討論室紀錄的文章；
						 * 四、討論版管理->議題討論版中的（教師、助教專用）。
						 **/
						dbSet('WM_term_major', "post=$cntsum", "username='{$username}' and course_id={$key}");
						$board=$total_cnt;
						dbSet('WM_board_check_undo',"board={$board},check_time='{$now}'","username='{$sysSession->username}'");
				}
		}
		return $board;
	}


/**
 * 檢查文章
 * @param string $username : 帳號
 * @return array
 **/
function checkPost($username = '')
{
    global $sysSession;
    
    $cnt      = 0;
    $username = trim($username);
    if (empty($username))
        $username = $sysSession->username;
    $cnt = checkFORUM($username);
    return $cnt;
}