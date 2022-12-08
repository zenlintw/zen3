<?php
	/**
	 * 【程式功能】我的作業/考試
	 * 建立日期：2004/11/09
	 * @author  Wiseguy Liang
	 * @version $Id: my_exam.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/live_list.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/learn/mycourse/modules/mod_short_link_lib.php');
	require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
	require_once(sysDocumentRoot . '/mooc/common/common.php');

        
    $sysSession->cur_func = '1300500100';
    $sysSession->env = 'learn';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }
    
    
    $data = array();
	$data = dbGetAssoc('APP_live_activity', '*', 'course_id=' . $sysSession->course_id . ' order by begin_time desc');

	
	// assign
	$smarty->assign('datalist', $data);
	// output
	if ($profile['isPhoneDevice']) {
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('common/course_header.tpl');
        $smarty->display('phone/learn/live_list.tpl');
        $smarty->display('common/tiny_footer.tpl');
    }else{
	    $smarty->display('common/tiny_header.tpl');
	    $smarty->display('learn/live_list.tpl');
	    $smarty->display('common/tiny_footer.tpl');
    }
