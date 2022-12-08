<?php
/**************************************************************************************************
 *                                                                                                 *
 *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
 *                                                                                                 *
 *        Programmer: Amm Lee                                                                       *
 *        Creation  : 2003/09/23                                                                    *
 *        work for  :  刪除不規則帳號                                                                     *
 *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
 *       $Id: stud_remove_abnormal.php,v 1.1 2010/02/24 02:38:45 saly Exp $                                                                                          *
 **************************************************************************************************/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/username.php');

$sysSession->cur_func = '1100400200';
$sysSession->restore();
if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

$icon_up = '<img src="/theme/default/academic/dude07232001up.gif" border="0" align="absmiddl">';
$icon_dn = '<img src="/theme/default/academic/dude07232001down.gif" border="0" align="absmiddl">';

$cond = '';
if (($searchkey != '') && isset($keyword)) {
    switch ($searchkey) {
        case 'real': // 姓名
            if ($sysSession->lang == 'Big5' || $sysSession->lang == 'GB2312')
                $cond = sprintf(' CONCAT(IFNULL(`last_name`,""), IFNULL(`first_name`,"")) like "%%%s%%"', escape_LIKE_query_str($keyword));
            else
                $cond = sprintf(' CONCAT(IFNULL(`first_name`,""), IFNULL(`last_name`,"")) like "%%%s%%"', escape_LIKE_query_str($keyword));
            break;
        case 'account': // 帳號
            $cond = ' username like "%' . escape_LIKE_query_str($keyword) . '%" ';
            break;
        case 'email': // email
            $cond = ' email like "%' . escape_LIKE_query_str($keyword) . '%" ';
            break;
    }
}

if (isset($_POST['searchkey'])) {
    $sType = trim($_POST['searchkey']);
} else {
    $sType = 'real';
}

if ($cond != '')
    $cond .= ' and ';
$cond = trim($cond . ' username != "' . sysRootAccount . '" ');

list($cnt) = dbGetStSr('WM_user_account', 'count(*)', $cond, ADODB_FETCH_NUM);
$total_page = ceil($cnt / $page_num);
$sqls       = $Sqls['get_all_student'] . (empty($cond) ? '' : ' where ' . $cond);

if (trim($_POST['p']) == '')
    $cur_page = min(1, max(0, $total_page));
else
    $cur_page = max(0, min($_POST['p'], $total_page));
$limit_begin = (($cur_page - 1) * $page_num);

$sort_by  = $_POST['sort_by'] ? $_POST['sort_by'] : ($_GET['sort_by'] ? $_GET['sort_by'] : 1);
$sort_by  = min(4, max(1, $sort_by));
$sort_arr = array(
    '',
    'username',
    'realname',
    'gender',
    'email'
);

$order_by = ($order_by == 'asc') ? 'asc' : 'desc';
if ($sort_arr[$sort_by] == 'realname')
    $sqls .= sprintf(' order by first_name %s,last_name %s ', $order_by, $order_by);
else
    $sqls .= " order by {$sort_arr[$sort_by]} {$order_by} ";

$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
if ($cur_page > 0)
    $RS = $sysConn->SelectLimit($sqls, $page_num, $limit_begin);
else if ($cur_page == 0)
    $RS = $sysConn->Execute($sqls);
if ($sysConn->ErrorNo() > 0)
    die($sysConn->ErrorMsg());


$js = <<< EOF
var cur_page   = {$cur_page};
var total_page = {$total_page};
var page_num   = {$page_num};    // 每頁顯示幾筆 (Row)
var searchkey  = "{$searchkey}";
var sort_by    = "{$sort_by}";
var order_by   = "{$order_by}";
var MSG_SELECT_CANCEL = "{$MSG['title77'][$sysSession->lang]}";
var MSG_SELECT_ALL    = "{$MSG['title78'][$sysSession->lang]}";

function page(n) {
    var obj = document.getElementById("actFm");
    if ((typeof(obj) != "object") || (obj == null)) return false;

    switch (n) {
        case -1:
            obj.p.value = 1;
            break;
        case -2:
            obj.p.value = (cur_page - 1);
            break;
        case -3:
            obj.p.value = (cur_page + 1);
            break;
        case -4:
            obj.p.value = (total_page);
            break;
        default:
            var p = parseInt(n);
            obj.p.value = p;
    }
    obj.order_by.value = order_by;
    obj.sort_by.value = sort_by;
    obj.page_num.value = page_num;
    obj.submit();
}

function Page_Row(row) {
    var obj = document.getElementById("actFm");
    obj.order_by.value = order_by;
    obj.sort_by.value = sort_by;
    obj.page_num.value = row;
    obj.submit();
}

/**
 * 單獨點選人員
 **/
function selPeo(val) {
    var nodes = null,
        attr = null;
    var isSel = "false";
    var cnt = 0;

    var obj = document.getElementById('learn_result');
    nodes = obj.getElementsByTagName('input');

    for (var i = 0, m = 0; i < nodes.length; i++) {
        attr = nodes[i].getAttribute("exclude");
        if ((nodes[i].type == "checkbox") && (attr == null)) {
            m++;
            if (nodes[i].checked) cnt++;
        }
    }

    // m = (m > 0) ? m - 1 : 0;
    document.getElementById("ck").checked = (m == cnt);

    /*  button 顯示 全選 或 全消 begin  */
    var btn1 = document.getElementById("btnSel1");

    if (m == cnt) {
        btn1.value = MSG_SELECT_ALL;
    } else {
        btn1.value = MSG_SELECT_CANCEL;
    }

    obj = document.getElementById("toolbar1");
    if ((typeof(obj) == "object") && (obj != null)) txt1 = obj.innerHTML;

    obj = document.getElementById("toolbar2");
    if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt1;
}

/**
 * 同步全選或全消的按鈕與 checkbox
 * @version 1.0
 **/
var nowSel = false;

function selected_box() {
    var obj = document.getElementById("ck");
    var btn1 = document.getElementById("btnSel1");
    if ((obj == null) || (btn1 == null)) return false;
    nowSel = !nowSel;

    obj.checked = nowSel;
    btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

    selFunc(obj.checked);
}

/**
 * 全選或全消
 **/
function selFunc(actType) {
    nodes = document.getElementsByTagName("input");
    for (var i = 0; i < nodes.length; i++) {
        attr = nodes[i].getAttribute("exclude");
        if ((nodes[i].type == "checkbox") && (attr == null)) {
            nodes[i].checked = actType;

            selPeo(nodes[i]);
        }
    }

    var btn1 = document.getElementById("btnSel1");

    if (actType) {
        btn1.value = MSG_SELECT_ALL
    } else {
        btn1.value = MSG_SELECT_CANCEL;
    }

    obj = document.getElementById("toolbar1");
    if ((typeof(obj) == "object") && (obj != null)) txt1 = obj.innerHTML;

    obj = document.getElementById("toolbar2");
    if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt1;

}

$(function() {
    // 控制核取方塊
    var class_name = 'acct_number';
    $("input[class='" + class_name + "'][type='checkbox']").on("click", function() {
        if ($("input[class='" + class_name + "'][type='checkbox']").index(this) === 0) {
            if ($(this).attr('checked') === 'checked') {
                $("input[class='" + class_name + "'][type='checkbox']").attr('checked', true);
            } else {
                $("input[class='" + class_name + "'][type='checkbox']").attr('checked', false);
            }
        } else {
            if ($(this).attr('checked') === 'checked') {
                if ($("input[class='" + class_name + "'][type='checkbox']:checked").length === ($("input[class='" + class_name + "'][type='checkbox']").length - 1)) {
                    $("input[class='" + class_name + "'][type='checkbox']").eq(0).attr('checked', true);
                }
            } else {
                $("input[class='" + class_name + "'][type='checkbox']").eq(0).attr('checked', false);
            }
        }
    });
});

/**
 * 顯示 基本資料
 **/
function showDetail(user, val) {
    var obj = document.getElementById("stud_info");

    if ((typeof(obj) != "object") || (obj == null)) return false;
    obj.user.value = user;
    obj.msgtp.value = val;

    obj.submit();
}

function del_account() {
    var obj = document.delFm2.elements;
    var ml = '';
    var aa;

    for (i = 0; i < (obj.length - 1); i++) {
        if (obj[i].type == 'checkbox' && obj[i].checked) {
            if (obj[i].value != '') {
                ml += obj[i].value + ',';
            }
        }
    }
    ml = ml.replace(/,$/ig, '');

    if (ml.length == 0) {
        alert("{$MSG['title59'][$sysSession->lang]}");
        return false;
    }

    if (confirm("{$MSG['title60'][$sysSession->lang]}")) {

        obj = document.getElementById("DelManualFm");

        obj.del_user.value = ml;

        obj.submit();

        return true;
    } else {
        return false;
    }
}

/*  標題排序  */
function chgPageSort(val) {
    var obj = document.getElementById("actFm");
    if ((typeof(obj) != "object") || (obj == null)) return false;

    if (order_by == 'asc') {
        obj.order_by.value = 'desc';
    } else {
        obj.order_by.value = 'asc';
    }
    obj.p.value = cur_page;
    obj.sort_by.value = val;
    obj.submit();
}

window.onload = function() {
    var txt1 = "";
    obj = document.getElementById("toolbar1");
    if ((typeof(obj) == "object") && (obj != null)) txt1 = obj.innerHTML;

    obj = document.getElementById("toolbar2");
    if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt1;

};
EOF;
showXHTML_script('include', '/lib/jquery/jquery.min.js');
showXHTML_script('inline', $js);
showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="learn_result" class="cssTable"');
    // 查詢搜尋
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td_B('colspan="9" ');
    echo $MSG['search_keyword'][$sysSession->lang];
    $ary = array(
        'real' => $MSG['realname'][$sysSession->lang],
        'account' => $MSG['username'][$sysSession->lang],
        'email' => $MSG['email'][$sysSession->lang]
    );
    showXHTML_input('select', 'searchkey', $ary, $sType, ' id="searchkey"');
    echo $MSG['inside'][$sysSession->lang];
    showXHTML_input('text', 'keyword', (isset($keyword1) && (strlen($keyword1) > 0) ? htmlspecialchars($keyword1) : $MSG['keyword'][$sysSession->lang]), '', 'id="keyword" size="20"  width="30" class="cssInput" onclick="this.value=\'\'"');
    echo $MSG['inside1'][$sysSession->lang];

    showXHTML_input('hidden', 'searchkey1', $_POST['searchkey'], '', '');
    showXHTML_input('hidden', 'keyword1', htmlspecialchars($keyword1), '', '');
    showXHTML_input('hidden', 'msgtp', '2', '', '');
    showXHTML_input('submit', '', $MSG['confirm'][$sysSession->lang], '', 'style="24" class="cssBtn"');
    showXHTML_td_E();
    showXHTML_tr_E();

    // 換頁與動作功能列
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td_B('colspan="8" nowrap  id="toolbar1"');
    showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
        showXHTML_tr_B('class="cssTrEvn"');
        showXHTML_td_B('nowrap ');

        echo $MSG['page'][$sysSession->lang];
        $P    = range(0, $total_page);
        $P[0] = $MSG['all'][$sysSession->lang];
        showXHTML_input('select', '', $P, $cur_page, 'size="1" onchange="page(this.value);"');

        // 每頁顯示幾筆
        echo $MSG['title92'][$sysSession->lang];
        $page_array = array(
            10 => $MSG['default_amount'][$sysSession->lang],
            20 => 20,
            50 => 50,
            100 => 100,
            200 => 200,
            400 => 400
        );
        showXHTML_input('select', 'page_num', $page_array, $page_num, 'class="cssInput" id="page_num" onChange="Page_Row(this.value)"');
        echo $MSG['title93'][$sysSession->lang];

        showXHTML_input('button', 'firstBtn1', $MSG['first1'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page == 1) || ($cur_page == 0)) ? 'disabled="true" ' : 'onclick="page(-1);"') . ' title=' . $MSG['title40'][$sysSession->lang]);
        showXHTML_input('button', 'prevBtn1', $MSG['prev'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page == 1) || ($cur_page == 0)) ? 'disabled="true" ' : 'onclick="page(-2);"') . ' title=' . $MSG['title41'][$sysSession->lang]);
        showXHTML_input('button', 'nextBtn1', $MSG['next'][$sysSession->lang], '', 'id="nextBtn1"  class="cssBtn" ' . ((($cur_page == 0) || ($cur_page == $total_page)) ? 'disabled="true" ' : 'onclick="page(-3);"') . ' title=' . $MSG['title42'][$sysSession->lang]);
        showXHTML_input('button', 'lastBtn1', $MSG['last1'][$sysSession->lang], '', 'id="lastBtn1"  class="cssBtn" ' . ((($cur_page == 0) || ($cur_page == $total_page)) ? 'disabled="true" ' : 'onclick="page(-4);"') . ' title=' . $MSG['title42'][$sysSession->lang]);
        showXHTML_input('button', 'del_acc', $MSG['delete_account'][$sysSession->lang], '', 'id="del_acc"   class="cssBtn" onclick="del_account()" ' . ' title=' . $MSG['del_account'][$sysSession->lang]);
        showXHTML_td_E();
        showXHTML_tr_E();
    showXHTML_table_E();
    showXHTML_td_E();
    showXHTML_tr_E();

    showXHTML_tr_B('class="cssTrHead"');
    showXHTML_td_B('align="center"');
    showXHTML_input('checkbox', 'ck', '', '', 'class="acct_number" exclude="true"' . 'title=' . $MSG['select_all'][$sysSession->lang]);
    showXHTML_td_E('');
    showXHTML_td_B('align="center" id="user_account" nowrap="noWrap" title="' . $MSG['username'][$sysSession->lang] . '"');
    echo '<a class="cssAnchor" href="javascript:chgPageSort(1);" >';
    echo $MSG['username'][$sysSession->lang];
    echo ($sort_by == 1) ? ($order_by == 'desc' ? $icon_dn : $icon_up) : '';
    echo '</a>';
    showXHTML_td_E();

    showXHTML_td_B('align="center" id="user_realname" nowrap="noWrap" title="' . $MSG['realname'][$sysSession->lang] . '"');
    echo '<a class="cssAnchor" href="javascript:chgPageSort(2);" >';
    echo $MSG['realname'][$sysSession->lang];
    echo ($sort_by == 2) ? ($order_by == 'desc' ? $icon_dn : $icon_up) : '';
    echo '</a>';
    showXHTML_td_E();

    showXHTML_td_B('align="center" id="user_gender" nowrap="noWrap" title="' . $MSG['gender'][$sysSession->lang] . '"');
    echo '<a class="cssAnchor" href="javascript:chgPageSort(3);" >';
    echo $MSG['gender'][$sysSession->lang];
    echo ($sort_by == 3) ? ($order_by == 'desc' ? $icon_dn : $icon_up) : '';
    echo '</a>';
    showXHTML_td_E();

    showXHTML_td_B('align="center" id="user_email" nowrap="noWrap" title="' . $MSG['email'][$sysSession->lang] . '"');
    echo '<a class="cssAnchor" href="javascript:chgPageSort(4);" >';
    echo $MSG['email'][$sysSession->lang];
    echo ($sort_by == 4) ? ($order_by == 'desc' ? $icon_dn : $icon_up) : '';
    echo '</a>';
    showXHTML_td_E();

    showXHTML_td('align="center" nowrap ', $MSG['title56'][$sysSession->lang]);
    showXHTML_td('align="center" nowrap ', $MSG['title57'][$sysSession->lang]);
    showXHTML_td('align="center" nowrap ', $MSG['title58'][$sysSession->lang]);
    showXHTML_tr_E();

    $i = (($cur_page - 1) * sysPostPerPage) + 1;

    //  判斷 $RS 是否有 record (begin)
    if ($RS->RecordCount() == 0) {
        showXHTML_tr_B('class="cssTrEvn"');
        showXHTML_td(' colspan="8" align="center"', $MSG['unfinded'][$sysSession->lang]);
        showXHTML_tr_E();
    } else {
        while (!$RS->EOF) {
            $real = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);

            $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($col);
            showXHTML_td_B('width="20" align="center"');
            showXHTML_input('checkbox', 'sel[]', $RS->fields['username'], '', ' class="acct_number"');
            showXHTML_td_E();
            showXHTML_td_B('align="left"');
            echo '<div style="width: 130px; overflow:hidden;" title="' . $RS->fields['username'] . '">' . $RS->fields['username'] . '</div>';
            showXHTML_td_E();

            showXHTML_td_B('align="left"');
            echo '<div style="width: 130px; overflow:hidden;" title="' . htmlspecialchars($real) . '">' . $real . '</div>';
            showXHTML_td_E();

            // 性別 (gender)
            if ($RS->fields['gender'] == 'M') {
                $gender = '/theme/default/academic/male.gif';
            } else {
                $gender = '/theme/default/academic/female.gif';
            }

            showXHTML_td_B('align="center"');
            echo '<img src="' . $gender . '" type="image/jpeg" align="absmiddle">';
            showXHTML_td_E();

            // email
            showXHTML_td_B('align="left"');
            echo '<div style="width: 200px; overflow:hidden;" title="' . $RS->fields['email'] . '">' . '<a href="mailto:' . $RS->fields['email'] . '">' . $RS->fields['email'] . '</a>' . '</div>';
            showXHTML_td_E();

            $icon = '<img src="/theme/' . $sysSession->theme . '/academic/icon_folder.gif" width="16" height="16" border="0" alt="' . $MSG['btn_alt_detail'][$sysSession->lang] . '" title="' . $MSG['btn_alt_detail'][$sysSession->lang] . '">';

            //  個人資料 (personal)
            $detail = '<a href="javascript:;" onclick="showDetail(\'' . $RS->fields['username'] . '\',1); return false;">' . $icon . '</a>';
            showXHTML_td('align="center"', $detail);

            //  修課記錄 (record)
            $detail = '<a href="javascript:;" onclick="showDetail(\'' . $RS->fields['username'] . '\',2); return false;">' . $icon . '</a>';
            showXHTML_td('align="center"', $detail);

            //  學習成果 (result)
            $detail = '<a href="javascript:;" onclick="showDetail(\'' . $RS->fields['username'] . '\',3); return false;">' . $icon . '</a>';
            showXHTML_td('align="center"', $detail);

            showXHTML_tr_E();

            $RS->MoveNext();
        }
    }
    //  判斷 $RS 是否有 record (end)

    // 換頁與動作功能列 (function line)
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td_B('colspan="8" nowrap id="toolbar2"');
    showXHTML_td_E('&nbsp;');
    showXHTML_tr_E();

showXHTML_table_E();
