<?php
	/**
	 * 討論版 Mail Follow 函式庫
	 *
	 * @since   2004/05/12
	 * @author  KuoYang Tsao
	 * @copyright 2004 SUNNET
	 * @modify from /message/collect.php
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	/**
	 * 1.1寄件者=課程名稱。
	 * 1.2收件者email= 課程名稱@學校domain name
	 * 1.3信件主旨=文章標題
	 * 1.4信件內容=文章文章內容
	 * 1.5信件附檔=文章附檔
	 * 1.6信件內容第一行請加上「本信件由系統轉寄，請無直接回覆本信件」
	 **/
	function MailFollow($MailData, $board_id='') {
		global $sysSession,$sysConn,$_SERVER;

		if ($_POST['switch'] == 'nix') return true; // #1342 張貼者可以取消自己本篇的 mail-follow

		if(!is_array($MailData)) return false;
		if ($board_id == '') $board_id = $sysSession->board_id;
		if(empty($board_id)) return false;

		$RS = dbGetStSr('WM_bbs_boards','owner_id,switch,with_attach',"board_id={$board_id}", ADODB_FETCH_ASSOC);
		if(!$RS) return false;

		$owner_id = $RS['owner_id'];
		switch(strlen($owner_id)) {
			case 5:	// 學校討論版
				// 暫不分學校跟系統討論版
				$MailData['body1'] = $MailData['title'].$MailData['body'];
				break;
			case 7:	// 班級討論版
				break;
			case 8:	// 課程討論版
				$MailData['body1'] = $MailData['title'] . $MailData['course'] . $MailData['body'];
				break;
			case 16:// 群組討論版
				$couse_id = intval(substr($owner_id,0,8));
				$team_id  = intval(substr($owner_id,8,4));
				$group_id = intval(substr($owner_id,12,4));
				$MailData['body1'] = $MailData['title'] . $MailData['course'] . $MailData['body'];
				break;
			default: // 其餘狀況不作轉寄
				return true;
		}
		$MailData['from'] = mailEncFrom($sysSession->realname, $sysSession->email);

		// 取得相關收件者
		$recps = Array();

		// 取得訂閱者清單
		if (!dbGetBoardOrders($board_id, $recps)) return false;

		if (strpos($RS['switch'], 'mailfollow') !== false) { // 需自動轉寄(含 'mailfollow' 設定 )
			if ((isset($_POST['switch']) ? $_POST['with_attach'] : $RS['with_attach']) != 'yes') {	// 轉寄是否要寄夾檔
				$MailData['attach']     = '';
				$MailData['attach_dir'] ='';
			}
			
			$owner_id = $RS['owner_id'];
			switch(strlen($owner_id)) {
				case 5: // 學校討論版
					// 暫不分學校跟系統討論版
					if (!dbGetSchoolUsers($sysSession->school_id, $recps)) return false;
					break;
				case 7: // 班級討論版
					if (!dbGetClassDirectors($sysSession->class_id, $recps)) return false;
					if (!dbGetClassMembers($sysSession->class_id, $recps)) return false;
					break;
				case 8: // 課程討論版
					$C_RS = dbGetStSr('WM_term_subject', 'state', "board_id={$board_id}", ADODB_FETCH_ASSOC);
					if (!$C_RS) return false;
					if (!dbGetCourseTeachers($sysSession->course_id,$recps)) return false;
					if ($C_RS['state'] == 'open') { // 對全部開放, 非教師專用
						if (!dbGetCourseStudents($sysSession->course_id,$recps)) return false;
					}
					break;
				case 16:// 群組討論版
					if (!dbGetGroupStudents($couse_id, $team_id, $group_id, $recps)) return false;
					break;
				default: // 其餘狀況不作轉寄
					return true;
			}
		}

		// 無收件者
		if(count($recps)==0) return true;

		// 建立 mime mail
		$mail = buildMail($MailData['from'], html_entity_decode($MailData['subject'], ENT_QUOTES) , $MailData['body1'],'html',
				'',$MailData['attach'],$MailData['attach_dir'],0,FALSE);
		$recps_ary = array();
		$mail_count = 0;
		foreach($recps as $k=>$v) {
			$m = explode("\t", $v);
		    if(!isset($recps_ary[$m[0]])){					
		        $recps_ary[$m[0]]=1;
				$mail_list .= $m[0] . ',';
                $mail_count++;
		    }
			//$mail->to = mailEncFrom($m[1], $m[0], 'utf-8');
			//$mail->send();
		    if ($mail_count % 90 == 0)
            {
                $mail_list = substr($mail_list, 0, -1);

                //$mail->to = $sysSession->email;	// 以寄件者為to
                $mail->headers = 'Bcc: ' . $mail_list;

                $mail->send();
                wmSysLog('199999908', $sysSession->course_id , '0' , 1, 'auto', $_SERVER['PHP_SELF'], $sysSession->username.' mail send :'.$mail_list);
                $mail_list  = '';
                $mail_count = 0;
           }
		}
	    if ($mail_count > 0)
        {
            $mail_list = substr($mail_list, 0, -1);
            //$mail->to = $sysSession->email;	// 以寄件者為to
            $mail->headers = 'Bcc: ' . $mail_list;
            $mail->send();
            wmSysLog('199999908', $sysSession->course_id , '0' , 1, 'auto', $_SERVER['PHP_SELF'], $sysSession->username.' mail send :'.$mail_list);
        }
		

		return true;
	}

/******************************************
	學校討論版部分
 ******************************************/
	/**
	 * dbGetSchoolUsers() 取得學校所有人員
	 *     將所有人員(收件者)放到陣列中，username 為索引，內容值為 Array(realname , email) ，並且過濾重複的人員
	 * @return
	 *     boolean false : 失敗
	 *     array  $recps : 收件者(修課學員)的陣列
	 **/
	function dbGetSchoolUsers($school_id, &$recps) {
		global $sysConn, $sysSession;
		if(!is_array($recps) || empty($school_id)) return false;

		$RS = dbGetStMr('WM_user_account', 'username,email,first_name,last_name', 'email IS NOT NULL OR email != ""', ADODB_FETCH_ASSOC);
		while(!$RS->EOF) {
			if(preg_match(sysMailRule, $RS->fields['email'])) {
				$realname = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);
				$recps[$RS->fields['username']] = $RS->fields['email'] . chr(9) . $realname;
			}
			$RS->MoveNext();
		}
		$tmp   = array_unique($recps);
		$recps = $tmp;
		return true;
	}

/******************************************
	課程討論版部分
 ******************************************/
	/**
	 * dbGetCourseStudents() 取得修課者
	 *     將修課者(收件者)放到陣列中，username 為索引，email 為內容值，並且過濾重複的人員
	 * @return
	 *     boolean false : 失敗
	 *     array  $student : 收件者(修課學員)的陣列
	 **/
	function dbGetCourseStudents($course_id, &$recps) {
		global $sysConn, $sysSession, $sysRoles;
		if(!is_array($recps) || empty($course_id)) return false;

		$course_id = intval($course_id);
		$RS = dbGetStMr('WM_term_major as t,WM_user_account as u',
						'u.username, u.email, u.first_name, u.last_name',
						't.course_id=' . $course_id . ' and t.username=u.username and t.role&' . ($sysRoles['auditor']|$sysRoles['student']),
						ADODB_FETCH_ASSOC);
		while(!$RS->EOF) {
			if(preg_match(sysMailRule, $RS->fields['email'])) {
				$realname = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);
				$recps[$RS->fields['username']] = $RS->fields['email'] . chr(9) . $realname;
			}
			$RS->MoveNext();
		}
		$tmp   = array_unique($recps);
		$recps = $tmp;
		return true;
	}

	/**
	 * dbGetCourseTeachers() 取得該課程教師
	 *     將教師(收件者)放到陣列中，username 為索引，email 為內容值，並且過濾重複的人員
	 * @return
	 *     boolean false : 失敗
	 *     array  $teacher : 收件者的陣列 $teacher[username]='[email]'
	 **/
	function dbGetCourseTeachers($course_id, &$teachers) {
		global $sysConn, $sysSession, $sysRoles;
		if(!is_array($teachers) || empty($course_id)) return false;

		$course_id = intval($course_id);
		$RS = dbGetStMr('WM_term_major as t,WM_user_account as u',
						'u.username, u.email, u.first_name, u.last_name',
						't.course_id=' . $course_id . ' and t.username=u.username and t.role&' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']),
						ADODB_FETCH_ASSOC);
		while(!$RS->EOF) {
			if(preg_match(sysMailRule, $RS->fields['email'])) {
				$realname = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);
				$teachers[$RS->fields['username']] = $RS->fields['email'] . chr(9) . $realname;
			}
			$RS->MoveNext();
		}
		$tmp      = array_unique($teachers);
		$teachers = $tmp;
		return true;
	}

/******************************************
	 班級討論版部分
 ******************************************/
	/**
	 * dbGetClassDirectors() 取得該班級導師
	 *     將導師(收件者)放到陣列中，username 為索引，email 為內容值，並且過濾重複的人員
	 * @return
	 *     boolean false : 失敗
	 *     array  $recps : 收件者的陣列 $recps[username]='[email]'
	 **/
	function dbGetClassDirectors($class_id, &$recps) {
		global $sysConn, $sysSession, $sysRoles;
		if(!is_array($recps) || empty($class_id)) return false;

		$class_id = intval($class_id);
		$RS = dbGetStMr('WM_class_member as t,WM_user_account as u',
						'u.username, u.email, u.first_name, u.last_name',
						't.class_id=' . $class_id . ' and t.username=u.username and t.role&' . ($sysRoles['director'] | $sysRoles['assistant']),
						ADODB_FETCH_ASSOC);
		while(!$RS->EOF) {
			if(preg_match(sysMailRule, $RS->fields['email'])) {
				$realname = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);
				$recps[$RS->fields['username']] = $RS->fields['email'] . chr(9) . $realname;
			}
			$RS->MoveNext();
		}
		$tmp   = array_unique($recps);
		$recps = $tmp;
		return true;
	}

	/**
	 * dbGetClassMembers() 取得該班級同學
	 *     將同學(收件者)放到陣列中，username 為索引，email 為內容值，並且過濾重複的人員
	 * @return
	 *     boolean false : 失敗
	 *     array  $recps : 收件者的陣列 $recps[username]='[email]'
	 **/
	function dbGetClassMembers($class_id, &$recps) {
		global $sysConn, $sysSession;
		if(!is_array($recps) || empty($class_id)) return false;

		$class_id = intval($class_id);
		$RS = dbGetStMr('WM_class_member as t,WM_user_account as u',
						'u.username, u.email, u.first_name, u.last_name',
						't.class_id=' . $class_id . ' and t.username=u.username and t.role&' . $sysRoles['student'],
						ADODB_FETCH_ASSOC);
		while(!$RS->EOF) {
			if(preg_match(sysMailRule, $RS->fields['email'])) {
				$realname = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);
				$recps[$RS->fields['username']] = $RS->fields['email'] . chr(9) . $realname;
			}
			$RS->MoveNext();
		}
		$tmp   = array_unique($recps);
		$recps = $tmp;
		return true;
	}

/******************************************
	群組討論版部分
 ******************************************/
	/**
	 * dbGetGroupStudents() 取得群組成員
	 *     將群組成員(收件者)放到陣列中，username 為索引，email 為內容值，並且過濾重複的人員
	 * @return
	 *     boolean false : 失敗
	 *     array  $student : 收件者(修課學員)的陣列
	 **/
	function dbGetGroupStudents($course_id,$team_id,$group_id,&$recps) {
		global $sysConn, $sysSession;
		if(!is_array($recps) || empty($course_id) || empty($team_id) || empty($group_id)) return false;

		$course_id = intval($course_id);
		$team_id   = intval($team_id);
		$group_id  = intval($group_id);
		$RS = dbGetStMr('WM_student_div as t,WM_user_account as u',
						'u.username, u.email, u.first_name, u.last_name',
						"t.course_id={$course_id} and t.group_id={$group_id} and t.team_id={$team_id} and t.username=u.username",
						ADODB_FETCH_ASSOC);
		while(!$RS->EOF) {
			if(preg_match(sysMailRule, $RS->fields['email'])) {
				$realname = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);
				$recps[$RS->fields['username']] = $RS->fields['email'] . chr(9) . $realname;
			}
			$RS->MoveNext();
		}
		$tmp   = array_unique($recps);
		$recps = $tmp;
		return true;
	}

/******************************************
	 訂閱者
******************************************/
	/**
	 * dbGetBoardOrders() 取得該版訂閱者
	 *     將訂閱者(收件者)放到陣列中，username 為索引，email 為內容值，並且過濾重複的人員
	 * @return
	 *     boolean false : 失敗
	 *     array  $recps : 收件者的陣列 $recps[username]='[email]'
	 **/
	function dbGetBoardOrders($board_id, &$recps) {
		global $sysConn, $sysSession;
		if(!is_array($recps) || empty($board_id)) return false;

		$board_id = intval($board_id);
		$RS = dbGetStMr('WM_bbs_order as t,WM_user_account as u',
						'u.username, u.email, u.first_name, u.last_name',
						"t.username = u.username and t.board_id=$board_id",
						ADODB_FETCH_ASSOC);
		while(!$RS->EOF) {
			if(preg_match(sysMailRule, $RS->fields['email'])) {
				$realname = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);
				$recps[$RS->fields['username']] = $RS->fields['email'] . chr(9) . $realname;
			}
			$RS->MoveNext();
		}
		$tmp   = array_unique($recps);
		$recps = $tmp;
		return true;
	}
?>
