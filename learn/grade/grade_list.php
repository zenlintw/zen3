<?php
/**************************************************************************************************
 *                                                                                                *
 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
 *                                                                                                *
 *		Programmer: Wiseguy Liang                                                         *
 *		Creation  : 2004/01/07                                                            *
 *		work for  : list all available exam(s)                                            *
 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
 *                                                                                                *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/lang/grade.php');

//define('TransAsia', true);

$sysSession->cur_func = '1400300100';
$sysSession->restore();
if (!aclVerifyPermission(1400300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

$sources = array(
    0 => $MSG['userdef'][$sysSession->lang],
    1 => $MSG['works'][$sysSession->lang],
    2 => $MSG['exams'][$sysSession->lang],
    3 => $MSG['quest'][$sysSession->lang],
    4 => $MSG['peer'][$sysSession->lang],
    9 => $MSG['userdef'][$sysSession->lang]
);


$sqls             = 'select L.grade_id,L.title,L.source,L.property,L.percent,I.score,I.comment,S.total,S.average,S.range ' . 'from WM_grade_list as L ' . 'left join WM_grade_item as I ' . "on L.grade_id=I.grade_id and I.username='{$sysSession->username}' " . 'left join WM_grade_stat as S ' . 'on L.course_id=S.course_id and I.username=S.username ' . "where L.course_id={$sysSession->course_id} " . 'and !(L.publish_begin="0000-00-00 00:00:00" and L.publish_end="0000-00-00 00:00:00") and L.publish_begin <= NOW() and L.publish_end > NOW() order by L.permute';
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
chkSchoolId('WM_grade_list');
$datalist         = array();
if ($rs = $sysConn->Execute($sqls)) {
    while ($fields = $rs->FetchRow()) {
        $titles            = getCaption($fields['title']);
        $fields['title'] = $titles[$sysSession->lang];
        if ($fields['source'] === '2') {
            $exam_content = $sysConn->GetOne('select content from WM_qti_exam_test where exam_id=' . $fields['property']);
            if (preg_match('/\bthreshold_score="([0-9]+(\.[0-9]+)?)"/U', $exam_content, $regs)) {
                $fields['pass_score'] = $regs[1];
                $fields['pass_adj'] = (floatval($regs[1]) > floatval($fields['score'])) ? '<span style="color: red">' . $MSG['title_nopass'][$sysSession->lang] . '</span>' : '<span style="color: green">' . $MSG['title_pass'][$sysSession->lang] . '</span>';
            } else {
                $fields['pass_score'] = $MSG['title_noblock'][$sysSession->lang];
                $fields['pass_adj'] = 'N/A';
            }
        } else {
            $fields['pass_score'] = '--';
            $fields['pass_adj'] = '--';
        }
        $fields['source'] = $sources[$fields['source']];
        $fields['comment'] = nl2br($fields['comment']);
        $fields['graphid'] = sprintf('%s%08u', md5(sysTicketSeed . $_COOKIE['idx'] . $fields['grade_id']), $fields['grade_id']);
        
        $datalist[]        = $fields;
    }
}

if (count($datalist)>0) {
	$total_percent = 0;
	$total = 0;
	foreach ($datalist as $key => $value) {
		$total_percent += $value['percent'];
		$tmp_score = $value['score'] * $value['percent']/100;
		$total += $tmp_score;
	}

	$datalist[] = array(
        'title' => '&nbsp;',
        'source' => '&nbsp;',
        'percent' => $total_percent,
        'score' => '&nbsp;',
        'pass_score' => '&nbsp;',
        'pass_adj' => $MSG['total_score'][$sysSession->lang],
        'comment' => sprintf('%.2f',$total),
        'graphid' => ''
    );
}
//echo '<pre>';
//var_dump($datalist);
//echo '</pre>';

// assign
//$smarty->assign('post', $_POST);
$smarty->assign('MSG', $MSG);
$smarty->assign('sysSession', $sysSession);
$smarty->assign('datalist', $datalist);

// output
if ($profile['isPhoneDevice']) {
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('common/course_header.tpl');
    $smarty->display('phone/learn/grade_list.tpl');
    $smarty->display('common/tiny_footer.tpl');
}else{
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('learn/grade_list.tpl');
    $smarty->display('common/tiny_footer.tpl');
}






























//showXHTML_head_B($MSG['grade_info'][$sysSession->lang]);
//    showXHTML_CSS('include', "/theme/{$sysSession->theme}/learn/wm.css");
//    showXHTML_script('inline', "
//    var teamWin;
//    function teamGraph(gid){
//            teamWin = window.open('grade_team.php?' + gid, '', 'width=470, height=320,status=0,menubar=0,toolbar=0,scrollbars=0,resizable=0');
//    }
//            ");
//showXHTML_head_E();
//
//showXHTML_body_B();
//    $ary[] = array(
//        $MSG['grade_info'][$sysSession->lang],
//        'tabsSet',
//        ''
//    );
//    echo '<div align="center">';
//    showXHTML_tabFrame_B($ary);
//        showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="760" style="border-collapse: collapse; word-wrap: break-word; word-break: break-all;" class="cssTable"');
//            showXHTML_tr_B('class="cssTrEvn"');
//            showXHTML_td((defined('TransAsia') ? 'colspan="8"' : 'colspan="6"') . ' style="color: red"', $MSG['msg_title'][$sysSession->lang]);
//            showXHTML_tr_E();
//            showXHTML_tr_B('class="cssTrHead"');
//            showXHTML_td('', $MSG['title'][$sysSession->lang]);
//            showXHTML_td('width="120"', $MSG['source'][$sysSession->lang]);
//            showXHTML_td('width="60"', $MSG['percent'][$sysSession->lang]);
//            showXHTML_td('width="35"', $MSG['score'][$sysSession->lang]);
//            if (defined('TransAsia')) {
//                showXHTML_td('width="60"', $MSG['pass_score'][$sysSession->lang]);
//                showXHTML_td('width="60"', $MSG['title_standard'][$sysSession->lang]);
//            }
//            showXHTML_td('width="200"', $MSG['comment'][$sysSession->lang]);
//            showXHTML_td('width="60"', $MSG['graph'][$sysSession->lang]);
//            showXHTML_tr_E();
//
//            $sqls             = 'select L.grade_id,L.title,L.source,L.property,L.percent,I.score,I.comment,S.total,S.average,S.range ' . 'from WM_grade_list as L ' . 'left join WM_grade_item as I ' . "on L.grade_id=I.grade_id and I.username='{$sysSession->username}' " . 'left join WM_grade_stat as S ' . 'on L.course_id=S.course_id and I.username=S.username ' . "where L.course_id={$sysSession->course_id} " . 'and !(L.publish_begin="0000-00-00 00:00:00" and L.publish_end="0000-00-00 00:00:00") and L.publish_begin <= NOW() and L.publish_end > NOW() order by L.permute';
//            $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
//            chkSchoolId('WM_grade_list');
//            $RS = $sysConn->Execute($sqls);
//            if ($RS) {
//                while (!$RS->EOF) {
//                    if (strpos($RS->fields['title'], 'a:') === 0)
//                        $titles = unserialize($RS->fields['title']);
//                    else
//                        $titles[$sysSession->lang] = $RS->fields['title'];
//
//                    $cls = $cls == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
//
//                    showXHTML_tr_B($cls);
//                    showXHTML_td('', $titles[$sysSession->lang]);
//                    showXHTML_td('', $sources[$RS->fields['source']]);
//                    showXHTML_td('align="right"', $RS->fields['percent'] . ' % ');
//                    showXHTML_td('align="right"', $RS->fields['score']);
//                    if (defined('TransAsia')) {
//                        if ($RS->fields['source'] == 2) {
//                            $exam_content = $sysConn->GetOne('select content from WM_qti_exam_test where exam_id=' . $RS->fields['property']);
//                            if (preg_match('/\bthreshold_score="([0-9]+(\.[0-9]+)?)"/U', $exam_content, $regs)) {
//                                showXHTML_td('align="center"', $regs[1]);
//                                showXHTML_td('align="center"', (floatval($regs[1]) > floatval($RS->fields['score'])) ? '<span style="color: red">' . $MSG['title_nopass'][$sysSession->lang] . '</span>' : '<span style="color: green">' . $MSG['title_pass'][$sysSession->lang] . '</span>');
//                            } else {
//                                showXHTML_td('align="center"', $MSG['title_noblock'][$sysSession->lang]);
//                                showXHTML_td('align="center"', 'N/A');
//                            }
//                        } else {
//                            showXHTML_td('align="center"', '--');
//                            showXHTML_td('align="center"', '--');
//                        }
//                    }
//                    showXHTML_td('', nl2br($RS->fields['comment']));
//                    showXHTML_td_B();
//                    showXHTML_input('button', '', $MSG['graph'][$sysSession->lang], '', 'class="cssBtn" style="cirsor: pointer; cursor: hand" onclick="teamGraph(\'' . sprintf('%s%08u', md5(sysTicketSeed . $_COOKIE['idx'] . $RS->fields['grade_id']), $RS->fields['grade_id']) . '\');"');
//                    showXHTML_td_E();
//                    showXHTML_tr_E();
//
//                    $total_score = $RS->fields['total'];
//                    $average     = $RS->fields['average'];
//                    $range       = $RS->fields['range'];
//                    $RS->MoveNext();
//                }
//            }
//
//        showXHTML_table_E();
//    showXHTML_tabFrame_E();
//    echo '</div>';
//showXHTML_body_E();