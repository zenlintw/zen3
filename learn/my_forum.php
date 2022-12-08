<?php
	/**
	 * 【程式功能】我的文章
	 * 建立日期：2005/02/25
	 * @author  Saly Lin
	 * @version $Id: my_forum.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 * @copyright 2005 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/my_forum.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/learn/mycourse/modules/mod_short_link_lib.php');
	require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
	
	$sysSession->cur_func='2000100100';
	if (!aclVerifyPermission(2500500100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	$label = 'SYS_04_01_005';
	$sysSession->goto_label = $label;
	$sysSession->restore();
	$smarty->assign('label', $label);
	// 更新討論版未讀文章篇數
	checkFORUM($sysSession->username); // from /learn/mycourse/modules/mod_short_link_lib.php

	$RS = dbGetCourses('C.course_id, C.caption, M.post',
					   $sysSession->username,
					   $sysRoles['auditor']|$sysRoles['student'],
					   'C.course_id');
	$datalist = array();
	if ($RS) {
	    while (!$RS->EOF) {
	        // 改為只顯示具有未讀文章的課程 by Small 2007/2/15 (B)
	        if ($RS->fields['post'] != 0)
	        {
	            $titles = getCaption($RS->fields['caption']);
	            $RS->fields['caption'] = $titles[$sysSession->lang];
	            $datalist[] = $RS->fields;
	        }
	        // 改為只顯示具有未讀文章的課程 by Small 2007/2/15 (B)
	        $RS->MoveNext();
	    }
	}

	// assign
	$smarty->assign('post', $_POST);
	$smarty->assign('MSG', $MSG);
	$smarty->assign('sysSession', $sysSession);
	$nEnv = $sysSession->env == 'teach' ? 2 : 1;
	$smarty->assign('nEnv', (($sysSession->env == 'teach')?2:1));
	$smarty->assign('datalist', $datalist);
	// output
	
	$smarty->display('common/tiny_header.tpl');
	$smarty->display('learn/my_forum.tpl');
	$smarty->display('common/tiny_footer.tpl');
