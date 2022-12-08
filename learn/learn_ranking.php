<?php
	/**
	 * 學習統計
	 * $Id: learn_ranking.php,v 1.2 2011-01-20 01:42:04 small Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/lib_logs.php');
	require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
	require_once(sysDocumentRoot . '/mooc/common/common.php');
	require_once(sysDocumentRoot . '/lang/learn_ranking.php');

	
    // 排序時需要的顯示圖案 by Small
    $img_up_src = "/theme/{$sysSession->theme}/academic/dude07232001up.gif";
    $img_dn_src = "/theme/{$sysSession->theme}/academic/dude07232001down.gif";
	$icon_up = '<img src=' . $img_up_src . ' border="0" align="absmiddl">';
	$icon_dn = '<img src=' . $img_dn_src . ' border="0" align="absmiddl">';

	$sysSession->cur_func='1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	// 判斷是否為本門課的老師
	$is_TA = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $sysSession->course_id);

	$cour_sort = array(
		'',
		'M.username',
		'M.role',
		'M.last_login',
		'M.login_times',
		'M.post_times',
		'M.dsc_times',
		'rss',
		'page'
	);

	/*
    * 排序
    */
	$_POST['sortby'] = intval($_POST['sortby']);
    $sortby = $cour_sort[$_POST['sortby']];
    if (empty($sortby)) $sortby = 'M.post_times';   // Bug#1390，修改預設排序為張貼篇數，而不是M.username by Small 2006/9/7

    $order = trim($_POST['order']);
    if (!in_array($order, array('asc', 'desc'))) $order = 'desc'; // Bug#1390，修改預設排序為desc，而非asc by Small 2006/9/7

	switch ($_POST['role'])
	{
		case 'assistant':       // 助教
		case 'instructor':      // 講師
		case 'teacher':         // 教師
		case 'student':         // 正式生
		case 'auditor':         // 旁聽生
		case 'senior':
		case 'paterfamilias':
			$role_condition = ' and (M.role & ' . $sysRoles[$_POST['role']] . ') ';
			break;
		case 'all':
			$role_condition = ' and (M.role & ' . ($sysRoles['student'] | $sysRoles['auditor']) . ')';
			break;
		default:      // 正式生
			$role_condition = ' and (M.role & ' . $sysRoles['student'] . ')';
			$_POST['role']  = 'student';
			break;
    }
    $role = $_POST['role'];

	$sqls = str_replace(array('%COURSEID%', '%CONDITION%'), array($sysSession->course_id, $role_condition), $Sqls['learn_ranking']);
	$sqls .= ' order by ' . $sortby . " $order";


  	// 名次
  	$count = 1;
	$datalist = array();
    $userLearnData = array();
	chkSchoolId('WM_term_major');
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	if ($rs = $sysConn->Execute($sqls)){
	  while($fields = $rs->FetchRow()){
            $fields['rank'] = $count++;
            $user = getUserDetailData($fields['username']);
            $temp_show = $fields['username'] . ' (' . $user['realname'] . ')';
            $fields['userShow'] = $temp_show;
            $fields['last_login'] = substr($fields['last_login'],0,16);
            $fields['rss'] = zero2gray(sec2timestamp($fields['rss']));
            $datalist[] = $fields;
            if ($fields['username'] == $sysSession->username) {
                $userLearnData = $fields;
            }
	  	}
  	}

    $lasttime = getCronDailyLastExecuteTime();
    if ($lasttime == 0)
    {
    	$msgUpdate = '<font color="red">' . $MSG['msg_cron_daily_fail'][$sysSession->lang] . '</font>';
    }else{
    	$msgUpdate = $MSG['msg_last_updated_time'][$sysSession->lang].'<font color="red">'.$lasttime.'</font>';
    }

    $smarty->assign('sort', $sortby);
    $smarty->assign('order', $order);

    // assign
    $smarty->assign('post', $_POST);
    $smarty->assign('MSG', $MSG);
    $smarty->assign('sysSession', $sysSession);
    $smarty->assign('msgUpdate', $msgUpdate);
    $smarty->assign('inlineJS', $js);
    $smarty->assign('datalist', $datalist);
    $smarty->assign('userLearnData', $userLearnData);

    // output
    if ($profile['isPhoneDevice']) {
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('common/course_header.tpl');
        $smarty->display('phone/learn/learn_ranking.tpl');
        $smarty->display('common/tiny_footer.tpl');
    }else{
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('learn/learn_ranking.tpl');
        $smarty->display('common/tiny_footer.tpl');
    }

