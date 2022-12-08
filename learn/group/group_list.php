<?php
/**************************************************************************************************
 *                                                                                                *
 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
 *                                                                                                *
 *		Programmer: Saly Lin                                                         *
 *		Creation  : 2004/01/08                                                            *
 *		work for  :                                                                       *
 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.4                                *
 *		identifier: $Id: group_list.php,v 1.1 2010-02-24 02:39:07 saly Exp $
 *                                                                                                *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/lang/teach_student.php');
require_once(sysDocumentRoot . '/webmeeting/include/hit_integration.php');
require_once(sysDocumentRoot . '/webmeeting/global.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');

$sysSession->cur_func = '2000200100';
$sysSession->restore();
if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

// 小組 acl function id
$acl_function   = 1000400300;
// 兩種 ACL 權限需求
$acl_permission = Array(
    0 => aclPermission2Bitmap('visible,readable,writable,modifiable,uploadable,removable'),
    1 => aclPermission2Bitmap('visible,readable,writable,modifiable,uploadable,removable,manageable')
);

$minTid = intval($_GET['tid']);
if (empty($minTid)) {
    $minTid = dbGetOne('WM_student_separate', 'team_id', "course_id={$sysSession->course_id} order by permute,team_id");
    if ($minTid == '') {
        $tean_name = array(
            'Big5' => 'NEW_TEAM',
            'GB2312' => 'NEW_TEAM',
            'en' => 'NEW_TEAM',
            'EUC-JP' => 'NEW_TEAM',
            'user_define' => 'NEW_TEAM'
        );
        $tean_name = serialize($tean_name);
        dbNew('WM_student_separate', 'course_id,team_id,team_name', "{$sysSession->course_id},1,'{$tean_name}'");
        $minTid = 1;
    }
}

// 得到身份(老師/講師/助教)
$is_tea           = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $sysSession->course_id);
$sqls1            = 'select D.group_id,D.username,G.board_id,G.caption,G.captain,A.first_name,A.last_name ' . 'from WM_student_div as D left join WM_student_group as G ' . 'on D.course_id=G.course_id and D.group_id=G.group_id and D.team_id=G.team_id ' . 'left join WM_user_account as A on D.username=A.username join WM_term_major as M on M.username=A.username and M.course_id=D.course_id ' . "where D.course_id={$sysSession->course_id} and D.group_id and D.team_id=$minTid " . 'order by G.permute,D.group_id';
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$RS               = $sysConn->Execute($sqls1);
$cur_gid          = '';
$group_count      = 1;
$leader           = '';
$mem_list         = '';
$groups           = array();
$i                = 0;

// 取分組列資料
if ($RS) {
    while (!$RS->EOF) {
        if ($RS->fields['group_id'] != $cur_gid) {
            if ($cur_gid != '') {
                // $groups[$i] = $tt[$sysSession->lang].','.$leader.','.$group_count.','.$gid.','.$g_bid.','.$cap.','.$mem_list;
                $groups[$i][] = $tt[$sysSession->lang];
                $groups[$i][] = $leader;
                $groups[$i][] = $group_count;
                $groups[$i][] = $gid;
                $groups[$i][] = $g_bid;
                $groups[$i][] = $cap;
                $groups[$i][] = $mem_list;
                $groups[$i]['group_name'] = $tt[$sysSession->lang];
                $groups[$i]['leader_name'] = $leader;
                $groups[$i]['peoples'] = $group_count;
                $groups[$i]['group_id'] = $gid;
                $groups[$i]['board_id'] = $g_bid;
                $groups[$i]['leader_id'] = $cap;
                $groups[$i]['send'] = $mem_list;
                $owner = sprintf('%d_%d_%d', $sysSession->course_id, $minTid, $gid);
                list($rid) = dbGetStSr('WM_chat_setting', '`rid`', "`owner`='{$owner}'", ADODB_FETCH_NUM);
                $groups[$i]['rid'] = $rid;
                $group_count  = 1;
                $leader       = '';
                $mem_list     = '';
                $i += 1;
            }
            $tt      = getCaption($RS->fields['caption']);
            $cur_gid = $RS->fields['group_id'];
        } else
            $group_count += 1;
        // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
        if ($RS->fields['username'] == $RS->fields['captain'])
            $leader = checkRealname($RS->fields['first_name'], $RS->fields['last_name']) . '(' . $RS->fields['username'] . ')';
        $gid   = $RS->fields['group_id'];
        $g_bid = $RS->fields['board_id'];
        $cap   = $RS->fields['captain'];
        $mem_list .= $RS->fields['username'] . ':';
        $RS->MoveNext();
    }
}
if (strlen($tt[$sysSession->lang]) > 0) {
    // $groups[$i]=$tt[$sysSession->lang].','.$leader.','.$group_count.','.$gid.','.$g_bid.','.$cap.','.$mem_list;
    $groups[$i][] = $tt[$sysSession->lang];
    $groups[$i][] = $leader;
    $groups[$i][] = $group_count;
    $groups[$i][] = $gid;
    $groups[$i][] = $g_bid;
    $groups[$i][] = $cap;
    $groups[$i][] = $mem_list;
    $groups[$i]['group_name'] = $tt[$sysSession->lang];
    $groups[$i]['leader_name'] = $leader;
    $groups[$i]['peoples'] = $group_count;
    $groups[$i]['group_id'] = $gid;
    $groups[$i]['board_id'] = $g_bid;
    $groups[$i]['leader_id'] = $cap;
    $groups[$i]['send'] = $mem_list;
    $owner = sprintf('%d_%d_%d', $sysSession->course_id, $minTid, $gid);
    list($rid) = dbGetStSr('WM_chat_setting', '`rid`', "`owner`='{$owner}'", ADODB_FETCH_NUM);
    $groups[$i]['rid'] = $rid; 
}

// 取分組
$RS = dbGetStMr('WM_student_separate', 'team_id, team_name', "course_id={$sysSession->course_id} order by permute, team_id", ADODB_FETCH_ASSOC);
if ($sysConn->ErrorNo() > 0)
    die($sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());
while (!$RS->EOF) {
    $tn = getCaption($RS->fields['team_name']);
    $team_name[$RS->fields['team_id']] = $tn[$sysSession->lang];
    if ($minTid == $RS->fields['team_id'])
        $cur_team = $tn;
    $RS->MoveNext();
}

// javascript
$scr  = <<< EOB
    // ID, group_name, captain, member_array
    var curGroups = new Array(
        new Array(0, '{$MSG['never_grouping'][$sysSession->lang]}', '', new Array(
EOB;

$sqls             = 'select M.username,A.first_name,A.last_name ' . 'from WM_term_major as M left join WM_user_account as A ' . "on M.username=A.username where M.course_id={$sysSession->course_id} and (M.role & {$sysRoles['student']}) " . 'order by M.username';
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$RS               = $sysConn->Execute($sqls);
$allStudents      = array();

//	preg_replace('/([\xA1-\xF9]\x5c)\x5c/', '\\1',preg_replace('/([\x5c\x27])/', '\\\\\\1',$tmp1[0]))
while (!$RS->EOF) {
    $allStudents[$RS->fields['username']] = htmlspecialchars(checkRealname($RS->fields['first_name'], $RS->fields['last_name']), ENT_QUOTES, 'UTF-8');
    $RS->MoveNext();
}

$cur_gid          = '';
$grouping         = '';
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$RS               = $sysConn->Execute($sqls1);
while (!$RS->EOF) {
    $realname = $allStudents[$RS->fields['username']];
    unset($allStudents[$RS->fields['username']]);
    if ($RS->fields['group_id'] != $cur_gid) {
        $tt = getCaption($RS->fields['caption']);
        $grouping .= (empty($cur_gid) ? '' : "'')),\n") . "\t\t\t  new Array({$RS->fields['group_id']},'{$tt[$sysSession->lang]}','{$RS->fields['captain']}', new Array('{$RS->fields['username']}\\t{$realname}',";
        $cur_gid = $RS->fields['group_id'];
    } else
        $grouping .= "'{$RS->fields['username']}\\t{$realname}',";
    $RS->MoveNext();
}

foreach ($allStudents as $k => $v){
    $scr .= "'$k\\t$v',";
}
$scr .= "'')),\n" . $grouping . (empty($grouping) ? '' : "'')),\n");
$scr .= <<< EOB
''
  );
 /* curGroups.pop(); */
 curGroups.pop();
 for(var i=0; i<curGroups.length; i++) curGroups[i][3].pop();
EOB;

// 權限控制
for ($i = 0; $i < count($groups); $i++) {
    $lists        = $groups[$i];
    $acl_instance = sprintf("%4u%04u", $minTid, $lists[3]);
    
    // ACL 判斷
    for ($xx = 0; $xx < count($acl_permission); $xx++) {
        $per_verify[$xx] = aclVerifyPermission($acl_function, $acl_permission[$xx], $sysSession->course_id, $acl_instance);
        $per_ok[$xx]     = ((($per_verify[$xx] === 'WM2' || $per_verify[$xx] === false) && ($is_tea || ($xx == 1 ? ($lists[5] == $sysSession->username) : strstr($lists[6], $sysSession->username)))) || $per_verify[$xx] === true) ? '1' : '0';
    }
    // 基本權限（討論版、討論室、寄給組員）
    $groups[$i]['basic_permission'] = $per_ok[0];
    // 進階權限（管理）
    $groups[$i]['adv_permission'] = $per_ok[1];
}

// assign
$smarty->assign('MSG', $MSG);
$smarty->assign('inlineJS', $scr);
$smarty->assign('datalist', $groups);
$smarty->assign('cid', $sysSession->course_id);
$smarty->assign('teams', $team_name);
$smarty->assign('assign_team', $minTid);



// output
if ($profile['isPhoneDevice']) {
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('common/course_header.tpl');
    $smarty->display('learn/group_list.tpl');
    $smarty->display('common/tiny_footer.tpl');
}else{
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('learn/group_list.tpl');
    $smarty->display('common/tiny_footer.tpl');
}