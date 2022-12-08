<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/lang/myteaching.php');
require_once(sysDocumentRoot . '/lang/activities.php');
require_once(sysDocumentRoot . '/lang/irs.php');

if ($sysSession->username == 'guest') {
    header('Location: /mooc/index.php');
    exit;
}

$role = $sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor'];
if (!aclCheckRole($sysSession->username, $role, $_POST['course_id'])){
    header('Location: /mooc/index.php');
    exit;
}



$course_id = $_POST['course_id'];
$exam_type = $_POST['exam_type'];
$exam_id   = $_POST['exam_id'];

require_once(sysDocumentRoot . '/lang/' . $exam_type . '_teach.php');
		     
$item_type = array(1=>$MSG['item_type1'][$sysSession->lang],2=>$MSG['item_type2'][$sysSession->lang],3=>$MSG['item_type3'][$sysSession->lang],5=>$MSG['item_type5'][$sysSession->lang],);		     

               

/*print('<pre>');	                
print_r($data);	
print('</pre>');
die;*/	                

$smarty->assign('csid', $course_id);
$smarty->assign('type', $exam_type);
$smarty->assign('exam_id', $exam_id);
$smarty->assign('item_type', $item_type);

$smarty->display('user/item_select.tpl');