<?php
/**************************************************************************************************
 *                                                                                                *
 *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
 *                                                                                                *
 *        Programmer: Wiseguy Liang                                                         *
 *        Creation  : 2004/02/26                                                            *
 *        work for  : 儲存某考生對某次測驗的評分                                          *
 *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
 *        identifier: $Id: exam_correct_content1.php,v 1.1 2010/02/24 02:40:25 saly Exp $
 *                                                                                                *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/quota.php');
require_once(sysDocumentRoot . '/teach/grade/grade_recal.php');

/**
 * 在 XML 中插入分數
 *
 * @param   string  $score_id   題目的identifier
 * @param    float   $item_score 分數
 */
function setScore(&$score_id, $item_score)
{
    global $dom, $xpath;
    
    $ret = $xpath->xpath_eval('//item_result[@ident_ref="' . $score_id . '"]');
    if (is_array($ret->nodeset) && count($ret->nodeset)) {
        $item_result = $ret->nodeset[0];
        $ret         = $xpath->xpath_eval('./outcomes', $item_result);
        if (is_array($ret->nodeset) && count($ret->nodeset))
            $outcomes = $ret->nodeset[0];
        else
            $outcomes = $item_result->append_child($dom->create_element('outcomes'));
        
        $ret = $xpath->xpath_eval('./score', $outcomes);
        if (is_array($ret->nodeset) && count($ret->nodeset))
            $score = $ret->nodeset[0];
        else
            $score = $outcomes->append_child($dom->create_element('score'));
        $score->set_attribute('varname', 'SCORE');
        $score->set_attribute('vartype', 'Integer');
        
        $ret = $xpath->xpath_eval('./score_value', $score);
        if (is_array($ret->nodeset) && count($ret->nodeset)) {
            $score_value = $ret->nodeset[0];
            foreach ($score_value->child_nodes() as $child)
                $score_value->remove_child($child);
        } else {
            $score_value = $score->append_child($dom->create_element('score_value'));
        }
        $score_value->append_child($dom->create_text_node($item_score));
        
        // 記錄批改
        $date       = $item_result->append_child($dom->create_element('date'));
        $type_label = $date->append_child($dom->create_element('type_label'));
        $type_label->append_child($dom->create_text_node('assign the score'));
        $datetime = $date->append_child($dom->create_element('datetime'));
        $datetime->append_child($dom->create_text_node(date('Y-m-d\TH:i:s')));
        
        return true;
    } else
        return false;
}

//ACL begin
if (QTI_which == 'exam') {
    $sysSession->cur_func = '1600300100';
} else if (QTI_which == 'homework') {
    include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');
    
    $sysSession->cur_func = '1700300100';
} else if (QTI_which == 'questionnaire') {
    $sysSession->cur_func = '1800300100';
}
$sysSession->restore();
if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    
}
//ACL end

$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id;

if ($_POST['ticket'] != md5(sysTicketSeed . $_COOKIE['idx'] . $_POST['exam_id'] . $_POST['examinee'] . $_POST['time_id'])) {
    wmSysLog($sysSession->cur_func, $course_id, $_POST['exam_id'], 1, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
    die('Fake ticket !');
}

if ($topDir == 'academic')
    $ans_save_path = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09u/%s/ref/%09u/',
        $sysSession->school_id,
        QTI_which,
        $_POST['exam_id'],
        $_POST['examinee'],
        $_POST['time_id']);
else
    $ans_save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/ref/%09u/',
        $sysSession->school_id,
        $sysSession->course_id,
        QTI_which,
        $_POST['exam_id'],
        $_POST['examinee'],
        $_POST['time_id']);

// 刪除舊檔
if (is_array($_POST['rmfiles']))
    foreach ($_POST['rmfiles'] as $item) {
        $item = rawurldecode($item);
        $arr_rm = explode('0_0',$item);
        $final_ans_save_path = str_replace($_POST['examinee'],$arr_rm[0],$ans_save_path);     
        if (file_exists($final_ans_save_path . $arr_rm[1])) @unlink($final_ans_save_path . $arr_rm[1]);
    }

// 儲存新檔
if (is_uploaded_file($_FILES['reffile']['tmp_name'])) {
    // 若目錄不存在則建立目錄
    if (!is_dir($ans_save_path)) exec("mkdir -p '$ans_save_path'");
    // 將參考檔案搬到該目錄
    mb_convert_encoding($_FILES['reffile']['name'], 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win');
    move_uploaded_file($_FILES['reffile']['tmp_name'], $ans_save_path . $_FILES['reffile']['name']);
}

// 更新quota資訊
$tmp_id = $topDir == 'academic' ? $sysSession->school_id : $sysSession->course_id;
getCalQuota($tmp_id, $quota_used, $quota_limit);
setQuota($tmp_id, $quota_used);

// 是否要作觀模範本
$status = $_POST['exemplary'] ? 'publish' : 'revised';

$where = sprintf('exam_id=%d and examinee="%s" and time_id=%d limit 1',
    $_POST['exam_id'], $_POST['examinee'], $_POST['time_id']);

// 儲存每一題給分 begin
list($content) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'content', $where, ADODB_FETCH_NUM);
$is_file = false;
	if (empty($content))
	{
       $xml_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/C/%09u/%s/',
		  					 $sysSession->school_id,
		  					 $sysSession->course_id,
		  					 QTI_which,
		  					 $_POST['exam_id'],
		  					 $_POST['examinee']);
       $file = 	$_POST['time_id'].'.xml';	  	

       $full_path = $xml_path.$file;
       if (is_file($full_path)) {
           $content = file_get_contents($full_path);
           $is_file = true;
       }
	}
if ($dom = @domxml_open_mem($content)) {
    if (is_array($_POST['item_scores']) && count($_POST['item_scores'])) {
        $xpath = xpath_new_context($dom);
        foreach ($_POST['item_scores'] as $score_id => $item_score) {
            setScore($score_id, sprintf('%.2f', $item_score));
        }
    }
} else
    die('Result XML incorrect !');
// 儲存每一題給分 end

    if($is_file) {
    	
    	 $update = sprintf('status="%s",score=%.2f,comment="%s",ref_url="%s"',
	                          $status, $_POST['total_score'], escape_percent_sign($_POST['comment']),
	    					  escape_percent_sign($_POST['refurl']));
    	
    	$file = fopen($full_path,"w");
        fwrite($file,$dom->dump_mem());
        fclose($file);
    } else {    
    
$update = sprintf('status="%s",score=%.2f,comment="%s",ref_url="%s",content=%s',
    $status, $_POST['total_score'], escape_percent_sign($_POST['comment']),
    escape_percent_sign($_POST['refurl']), $sysConn->qstr($dom->dump_mem()));
    }

// 儲存本卷
dbSet('WM_qti_' . QTI_which . '_result', unescape_percent_sign($update), $where);

if ($sysConn->ErrorNo()) {
    $results = $MSG['save_grade_failure'][$sysSession->lang] . $sysConn->ErrorMsg();
    wmSysLog($sysSession->cur_func, $course_id , $_POST['exam_id'] , 2, 'auto', $_SERVER['PHP_SELF'],
        $results . sprintf('exam_id=%d and examinee="%s" and time_id=%d',
        $_POST['exam_id'], $_POST['examinee'], $_POST['time_id']));
} elseif ($sysConn->Affected_Rows()) {
    // 同步儲存到成績系統
    // 批改作業，強制更新該學員成績
    if (reCalculateQTIGrade($_POST['examinee'], $_POST['exam_id'], QTI_which, $_POST['comment'], $_POST['team_id']))
        reCalculateGrades($sysSession->course_id);
    
    $results = $MSG['save_grade_success'][$sysSession->lang];
    wmSysLog($sysSession->cur_func, $course_id, $_POST['exam_id'], 0, 'auto', $_SERVER['PHP_SELF'], $results . sprintf('exam_id=%d and examinee="%s" and time_id=%d', $_POST['exam_id'], $_POST['examinee'], $_POST['time_id']));
} else {
    $results = $MSG['grade_no_mdify'][$sysSession->lang];
    wmSysLog($sysSession->cur_func, $course_id , $_POST['exam_id'] , 0, 'auto', $_SERVER['PHP_SELF'], $results . sprintf('exam_id=%d and examinee="%s" and time_id=%d',
        $_POST['exam_id'], $_POST['examinee'], $_POST['time_id']));
}

echo <<< EOB
<h2 aign="center">{$results}<br>{$MSG['continue_correct'][$sysSession->lang]}</h2>
<script>
    setTimeout('parent.correctOne()', 1500);
</script>
EOB;
