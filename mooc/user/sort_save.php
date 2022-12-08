<?php
$qtiType = $_POST['exam_type'];
define('QTI_which', $qtiType);
define('API_QTI_which', $qtiType);

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/teach/exam/item_create_lib.php');
require_once(sysDocumentRoot . '/teach/exam/exam_create_lib.php');


$examMaintain = new examMaintain();

// 由 exam_id 是否有設定來判斷是不是修改試卷
if ($_POST['exam_id'] !== 0) {
    // 修改試卷
    $examMaintain->isModify = true;

    // 取得目前試卷的設定，saveExam 的 type 是看 ex_type 再取得原試卷時作個轉換
    $examData = dbGetRow(
                    sprintf("`WM_qti_%s_test`", $qtiType),
                    "*, `type` as `ex_type`",
                    sprintf("exam_id = %d", $_POST['exam_id'])
    );

    $examData['threshold_score'] = preg_match('/\bthreshold_score="([0-9]*)"/', $examData['content'], $regs) ? $regs[1] : '';
    
    $createContentXml = $examMaintain->createContentXml($_POST['item_id'], $_POST['item_score'],$examData['content']);
    $testData['content'] = $createContentXml['data'];
    
    dbSet('WM_qti_' . $qtiType . '_test',sprintf("content = '%s'",$testData['content']),'exam_id=' . $_POST['exam_id']);

}   

//$examResult = $examMaintain->saveExam($testData);

$data['code'] = 1;
	
$msg = json_encode($data);
if ($msg != '') {
    echo $msg;
}





?>