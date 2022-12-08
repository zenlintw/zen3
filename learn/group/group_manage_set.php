<?php
	/**
	 * 群組進階設定
	 * $Id: group_manage_set.php,v 1.1 2010/02/24 02:39:07 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/teach_student.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
	require_once(sysDocumentRoot . '/mooc/common/common.php');
	
	$sysSession->cur_func='2000100300';
	$sysSession->restore();
	if (!aclVerifyPermission(2000100300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$tid = intval($_SERVER['argv'][0]);
	$gid = intval($_SERVER['argv'][1]);
	$bid = intval($_SERVER['argv'][2]);

	// 取得小組名稱
	list($captions) = dbGetStSr('WM_student_group', 'caption', "course_id={$sysSession->course_id} and group_id=$gid and team_id=$tid", ADODB_FETCH_NUM);
	$ctions = old_getCaption($captions);

	$sqls1 = 'select D.username,G.captain,A.first_name,A.last_name ' .
			 'from WM_student_div as D left join WM_student_group as G ' .
			 'on D.course_id=G.course_id and D.group_id=G.group_id and D.team_id=G.team_id ' .
                         'left join WM_user_account as A on D.username=A.username join WM_term_major as M on M.username=A.username and M.course_id=D.course_id ' .
			 "where D.course_id={$sysSession->course_id} and D.group_id=$gid and D.team_id=$tid " .
			 'order by G.permute';
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$RS = $sysConn->Execute($sqls1);

	// 取得小組討論板相關設定
	list($bname,$titles,$switchs,$with_attach,$vpost,$def_order) =
		dbGetStSr('WM_bbs_boards as B,WM_student_group as G',
				  'B.bname,B.title,B.switch,B.with_attach,B.vpost,B.default_order',
				  "B.board_id={$bid} and B.board_id=G.board_id and G.course_id={$sysSession->course_id}",
				  ADODB_FETCH_NUM);
	$bnames      = old_getCaption($bname);
	$ps          = strpos($switchs, 'mailfollow');
	$mailfollows = ($ps === false) ? 'no' : 'yes';
	$def_sort    = (isset($def_order) ? trim($def_order):'pt');

	// 取得小組討論室相關設定
	$owners = $sysSession->course_id.'_'.$tid.'_'.$gid;
	list($rid) = dbGetStSr('WM_chat_setting', 'rid', "owner='{$owners}'", ADODB_FETCH_NUM);
	if(empty($rid)){
		// 取得rid
		$newrid = uniqid('');
		dbNew('WM_chat_setting','rid,owner,title', "'$newrid','$owners','$captions'");
		// $rid = $sysConn->Insert_ID();
		$rid = $newrid;
		wmSysLog($sysSession->cur_func, $sysSession->course_id , $rid , 0, 'auto', $_SERVER['PHP_SELF'], 'new chat setting!');
	}
	list($rname,$e_act,$jumps) = dbGetStSr('WM_chat_setting', 'title,exit_action,jump', "owner='{$owners}' and rid='{$rid}'", ADODB_FETCH_NUM);
	$rnames  = old_getCaption($rname);
	$exitAct = trim($e_act);
	$jump    = trim($jumps);

	/**
	 * 安全性檢查
	 *     1. 身份的檢查
	 *     2. 權限的檢查
	 *     3. .....
	 **/

	// 設定車票
	setTicket();

	$js = <<< BOF
	var msgNote      = "{$MSG['notice'][$sysSession->lang]}";
	var msgTitleHelp = "{$MSG['title_help'][$sysSession->lang]}";
	var msgCurLength = "{$MSG['current_length'][$sysSession->lang]}";
	var msgDontExceed= "{$MSG['dont_exceed'][$sysSession->lang]}";

	window.onload=function(){
		TaskWatchLength();
	};


	/**
	 * 取消,回分組討論畫面
	 * m:回第幾組次
	 **/
	function goBack(m) {
		window.location.replace("group_list.php?tid="+m);
	}

	
	/**
	 * 檢查討論版主旨長度
	 **/
	function getTxtLength(n) {
		v = n.value;
		j =0;
		for(i=0;i<v.length;i++) {
			c = v.charCodeAt(i);
			j+=(c>127?3:1);
		}
		return j;
	}
	
	function chgTitle(n) {
		var tl = document.getElementById('TxtLen');
		l = getTxtLength(n);
		if(l<200) {
			color='blue';
			msg = "";
		} else {
			color = l<255?'#BBBB00':'red';
			msg = msgNote + msgDontExceed;
		}

		tl.innerHTML = msgCurLength + "<font color=" + color + ">" + l + "</font>&nbsp;&nbsp;" + msg;
	}
	timerID = 0;
	function TaskWatchLength() {
		timerID = setInterval("chgTitle(document.addFm2.help)",500);
	}

	function chkBoard() {
		var frm = document.getElementById('addFm2');
		if(!frm) return false;
		// 主旨不能超出 255 bytes
		if(getTxtLength(frm.help)>255) {
			alert(msgNote + msgTitleHelp + "\\n" + msgDontExceed);
			frm.help.focus();
			return false;
		}
		return true;
	}

	function chgWithAttach(wa) {
		if (wa == true)
			document.forms[1].mailfollow[0].checked = wa;
		else if (wa == 'no')
			document.forms[1].withattach.checked = false;
	}

BOF;

	$smarty->assign('inlineJS', $js);
	$smarty->assign('tid', $tid);
	$smarty->assign('gid', $gid);
	$smarty->assign('ctions', $ctions);
	
	ob_start();
	if ($RS->EOF) echo $MSG['set_chief_memo'][$sysSession->lang].'<br>';
	$i = 1;
	while(!$RS->EOF){
	    echo '<input type="radio" name="chief" value="'.$RS->fields['username'].'"'.(($RS->fields['username']==$RS->fields['captain'])?' checked':'').'>';
	    echo $RS->fields['last_name'].$RS->fields['first_name'].'('.$RS->fields['username'].')&nbsp;';
	    if (($i % 3)==0) echo '<br>';
	    $RS->MoveNext();
	    $i++;
	}
	$radioChief = ob_get_contents();
	ob_end_clean();
	$smarty->assign('radioChief', $radioChief);
	$ticket = md5('AddManual' . $sysSession->ticket . $sysSession->school_id . $sysSession->username);
	$smarty->assign('ticket', $ticket);
	
	// 小組討論板設定	
	$smarty->assign('bid', $bid);
	$smarty->assign('bnames', $bnames);
	$smarty->assign('helpTitles', $titles);
	$smarty->assign('mailfollows', $mailfollows);
	$smarty->assign('with_attach', $with_attach);

	// 小組討論室設定
	// 結束時的動作
	$exitHost = array(
		'none'     => $MSG['exit_act_none'][$sysSession->lang],
		'notebook' => $MSG['exit_act_notebook'][$sysSession->lang],
		'forum'    => $MSG['exit_act_forum'][$sysSession->lang]
	);

	$smarty->assign('rid', $rid);
	$smarty->assign('rnames', $rnames);
	$smarty->assign('jump', $jump);
	
    ob_start();
	showXHTML_input('select', 'host_exit', $exitHost, $exitAct, '');
	$selChatActions = ob_get_contents();
	ob_end_clean();
	$smarty->assign('selChatActions', $selChatActions);

	// output
	$smarty->display('common/tiny_header.tpl');
	$smarty->display('learn/group/group_manage_set.tpl');
	$smarty->display('common/tiny_footer.tpl');