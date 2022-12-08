<?php
/**************************************************************************************************
 *                                                                                                *
 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
 *                                                                                                *
 *		Programmer: Saly Lin                                                         *
 *		Creation  : 2004/01/08                                                            *
 *		work for  :                                                                       *
 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.4                                *
 *                                                                                                *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lang/teach_student.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');

$sysSession->cur_func = '2000200100';
$sysSession->restore();
if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

$action = htmlspecialchars($_GET['action']);
$tid = intval($_GET['team_id']);
$gid = intval($_GET['group_id']);

// 取得小組名稱
list($captions) = dbGetStSr('WM_student_group', 'caption', "course_id={$sysSession->course_id} and group_id=$gid and team_id=$tid", ADODB_FETCH_NUM);
$ctions = getCaption($captions);

$sqls1            = 'select D.username,G.captain,A.first_name,A.last_name,A.email ' . 'from WM_student_div as D left join WM_student_group as G ' . 'on D.course_id=G.course_id and D.group_id=G.group_id and D.team_id=G.team_id ' . 'left join WM_user_account as A on D.username=A.username join WM_term_major as M on M.username=A.username and M.course_id=D.course_id ' . "where D.course_id={$sysSession->course_id} and D.group_id=$gid and D.team_id=$tid " . 'order by G.permute';
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$RS               = $sysConn->Execute($sqls1);

$i = 1;
while (!$RS->EOF) {
    $groups[$i]['email'] = $RS->fields['email'];
    $groups[$i]['username'] = $RS->fields['username'];
    $groups[$i]['serial'] = $i;
    
    $realname = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);
    $groups[$i]['realname'] = $realname;
    
    $groups[$i]['captain'] = $RS->fields['captain'];
    
    $RS->MoveNext();
    $i++;
}

// assign
$smarty->assign('msg', $MSG);
$smarty->assign('nowlang', $sysSession->lang);
$smarty->assign('datalist', $groups);
$smarty->assign('team_id', $tid);
$smarty->assign('group_id', $gid);
$smarty->assign('group_name', $ctions[$sysSession->lang]);
$smarty->assign('action', $action);

// output
$smarty->display('common/tiny_header.tpl');
$smarty->display('learn/group_listmem.tpl');
$smarty->display('common/tiny_footer.tpl');





























//showXHTML_body_B();
//    showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" width="520" style="border-collapse: collapse"');
//        showXHTML_tr_B();
//        showXHTML_td_B();
//        $ary[] = array(
//            $MSG['mem_list'][$sysSession->lang],
//            'tabsSet',
//            ""
//        );
//        showXHTML_tabs($ary, 1);
//        showXHTML_td_E();
//        showXHTML_tr_E();
//        showXHTML_tr_B();
//        showXHTML_td_B('valign="top" class="bg01"');
//        showXHTML_form_B('style="display: inline"', 'groupMemList');
//
//        showXHTML_table_B('id="studentListTable" border="0" cellpadding="3" cellspacing="1" width="520" style="border-collapse: collapse" class="cssTable"');
//        showXHTML_tr_B('class="cssTrHelp"');
//        showXHTML_td('class="cssTd" colspan="5" nowrap', $MSG['group_name'][$sysSession->lang] . '>' . $ctions[$sysSession->lang]);
//        showXHTML_tr_E();
//        showXHTML_tr_B('class="bg03 font01"');
//        showXHTML_td_B('class="cssTd" colspan="5" nowrap', $MSG['chose_mem'][$sysSession->lang]);
//        showXHTML_tr_E();
//        showXHTML_tr_B('class="cssTrHead"');
//        showXHTML_td_B('width="20" nowrap');
//        showXHTML_input('checkbox', '', '', '', 'onclick="selectItem(this.checked);" title="' . $MSG['td_alt_sel'][$sysSession->lang] . '"');
//        showXHTML_td_E();
//        showXHTML_td('nowrap width="50"', $MSG['serial'][$sysSession->lang]);
//        showXHTML_td('nowrap width="200"', $MSG['realname'][$sysSession->lang]);
//        showXHTML_td('nowrap width="200"', $MSG['account'][$sysSession->lang]);
//        showXHTML_td('nowrap width="50"', $MSG['capacity'][$sysSession->lang]);
//        showXHTML_tr_E();
//        $i = 1;
//        while (!$RS->EOF) {
//            $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
//            showXHTML_tr_B($col);
//            showXHTML_td_B('width="20" nowrap');
//            showXHTML_input('checkbox', 'target[]', $RS->fields['username'], '', empty($RS->fields['email']) ? 'disabled' : '');
//            showXHTML_td_E();
//            showXHTML_td('nowrap width="50"', $i);
//            showXHTML_td_B('nowrap width="200"');
//            $realname = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);
//            if (strlen($RS->fields['email']) > 0)
//                echo '<a class="cssAnchor" href="mailto:' . $RS->fields['email'] . '">' . $realname . '</a>';
//            else
//                echo $realname;
//            showXHTML_td_E();
//            showXHTML_td('nowrap width="200"', $RS->fields['username']);
//            showXHTML_td_B('nowrap width="50"');
//            if ($RS->fields['username'] == $RS->fields['captain'])
//                echo $MSG['captain'][$sysSession->lang];
//            else
//                echo $MSG['members'][$sysSession->lang];
//            showXHTML_td_E();
//            showXHTML_tr_E();
//            $RS->MoveNext();
//            $i++;
//        }
//        $col = $col == 'class="bg03 font01"' ? 'class="bg04 font01"' : 'class="bg03 font01"';
//        showXHTML_tr_B($col);
//        showXHTML_td_B('colspan="5" nowrap');
//        showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', "class=\"cssBtn\" onclick=\"goBack($tid);\"");
//        showXHTML_input('button', '', $MSG['step1'][$sysSession->lang], '', 'class="cssBtn" onclick="mailTo();"');
//        showXHTML_td_E();
//        showXHTML_tr_E();
//        showXHTML_form_E();
//
//        showXHTML_form_B('method="POST" action="group_mail_write.php"', 'mailForm');
//            showXHTML_input('hidden', 'to');
//            showXHTML_input('hidden', 'tid', $tid);
//            showXHTML_input('hidden', 'gid', $gid);
//        showXHTML_form_E();
//        
//        showXHTML_td_E();
//        showXHTML_tr_E();
//    showXHTML_table_E();
//showXHTML_body_E();