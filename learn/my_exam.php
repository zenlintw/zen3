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
	require_once(sysDocumentRoot . '/lang/my_exam.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/learn/mycourse/modules/mod_short_link_lib.php');
	require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');

        // 我的作業、測驗（TODO：暫時和 瀏覽老師出的作業）
        $sysSession->cur_func = '1700400100';
        $sysSession->restore();
        if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
        {
        }

	$isHw = (($which = substr(basename($_SERVER['PHP_SELF']), 3, -4)) == 'homework');
	$label = $isHw ? 'SYS_04_02_001' : 'SYS_04_02_002';
	$sysSession->goto_label = $label;
	$sysSession->restore();
	$smarty->assign('isHw', $isHw);
	$smarty->assign('label', $label);
	
	$exam_ary = checkQTI($sysSession->username, $which);
	
	// 增加取同儕互評數量
	if ($which === 'homework') {
	    $peer_ary = checkQTI($sysSession->username, 'peer');
	    foreach ($peer_ary as $k => $v) {
	        if (is_array($exam_ary[$k]) === true) {
	            $exam_ary[$k]['total_do'] = $exam_ary[$k]['total_do'] + $v['total_do'];
	            $exam_ary[$k]['total_undo'] = $exam_ary[$k]['total_undo'] + $v['total_undo'];
	        } else {
	            $exam_ary[$k]['caption'] = $v['caption'];
	            $exam_ary[$k]['total_do'] = $v['total_do'];
	            $exam_ary[$k]['total_undo'] = $v['total_undo'];
	        }
	    }
	}
        
        function array_sort($array,$keys,$type='asc'){
            $keysvalue = $new_array = array();

            foreach ($array as $k=>$v){
                $keysvalue[$k] = $v[$keys];
            }
            if($type == 'asc'){
                asort($keysvalue);
            }else{
                arsort($keysvalue);
            }
            foreach ($keysvalue as $k=>$v){
                $new_array[$k] = $array[$k];
            }
            return $new_array;
        }        
       $exam_ary = array_sort($exam_ary,'total_undo','desc');
	
	// assign
	$smarty->assign('post', $_POST);
	$smarty->assign('MSG', $MSG);
	$smarty->assign('sysSession', $sysSession);
	$nEnv = $sysSession->env == 'teach' ? 2 : 1;
	$smarty->assign('nEnv', (($sysSession->env == 'teach')?2:1));
	$smarty->assign('datalist', $exam_ary);
	// output
	
	$smarty->display('common/tiny_header.tpl');
	$smarty->display('learn/my_exam.tpl');
	$smarty->display('common/tiny_footer.tpl');
