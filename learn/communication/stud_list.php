<?php
/**
 * 通訊錄
 * $Id: stud_list.php,v 1.2 2010-05-06 02:32:28 edi Exp $
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lang/stud_account.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');

$sysSession->cur_func = '400500100';
$sysSession->restore();
if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

if ($sysSession->username === 'guest') {
    die('Access deny: no permission.');
}

if ($sysSession->course_id === '0') {
    die('Access deny: no enter any course.');
}

$hid_ico = '<img src="/theme/' . $sysSession->theme . '/learn/communication/hide.gif" border="0" alt="' . $MSG['hide'][$sysSession->lang] . '">';

// 登入者是否為本門課老師 或 講師
$isTA = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] , $sysSession->course_id);

$icon_up = '<img src="/theme/default/learn/dude07232001up.gif" border="0" align="absmiddl">';
$icon_dn = '<img src="/theme/default/learn/dude07232001down.gif" border="0" align="absmiddl">';

$aryrole = array(
    'auditor',
    'student',
    'assistant',
    'instructor',
    'teacher',
    'all'
);

//  sql query (begin)
// 身份 (begin)
if (isset($_POST['status'])) {
    $status = $_POST['status'];
} elseif (isset($_GET['status'])) {
    $status = $_GET['status'];
}
$status = (in_array($status, $aryrole)) ? $status : 'all';
$role   = '(A.role & ' . $sysRoles[$status] . ')';
// 身份 (end)

/*
判斷是否有按過 查詢的按鈕   (begin)
*/
if ($_POST['flag'] != '') {
    $flag = trim($_POST['flag']);
} else if ($_GET['flag'] != '') {
    $flag = trim($_GET['flag']);
}
if (empty($flag))
    $flag = '0';

/*
判斷是否有按過 查詢的按鈕   (end)
*/
if ($flag != '0') {
    if ($_POST['searchkey'] != '') {
        $searchkey = $_POST['searchkey'];
    } else {
        $searchkey = $_GET['searchkey'];
    }
    
    if ($_GET['keyword'] != '') {
        $keyword  = escape_LIKE_query_str(addslashes(trim($_GET['keyword'])));
        $keyword1 = stripslashes(trim($_GET['keyword']));
    } else {
        $keyword = escape_LIKE_query_str(addslashes(trim($_POST['keyword'])));
        
        $keyword1 = stripslashes(trim($_POST['keyword']));
    }
    
    switch ($searchkey) { // 搜尋
        case 'real': // 姓名
            if (isset($keyword)) {
                if ($sysSession->lang == 'Big5' || $sysSession->lang == 'GB2312')
                    $cond = ' and ' . sprintf(' CONCAT(B.last_name, B.first_name) like "%%%s%%"', $keyword);
                else
                    $cond = ' and ' . sprintf(' CONCAT(B.first_name, B.last_name) like "%%%s%%"', $keyword);
            }
            break;
        case 'account': // 帳號
            if (isset($keyword)) {
                $cond = ' and B.username like "%' . $keyword . '%" ';
            }
            break;
        case 'email': // email
            if (isset($keyword)) {
                $cond = ' and B.email like "%' . $keyword . '%" ';
            }
            break;
    }
}

if ($role == '') {
    $sum_role = 0;
    $sum_role = ($sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher'] | $sysRoles['student'] | $sysRoles['auditor'] | $sysRoles['senior'] | $sysRoles['paterfamilias']);
    
    $role = '(A.role & ' . $sum_role . ')';
}

/*
 * 排序
 */
$sortby = trim($_POST['sortby']);
if (empty($sortby))
    $sortby = 'role';

$order = trim($_POST['order']);
if (empty($order))
    $order = 'desc';

if (($sortby == 'realname') && ($order == 'asc')) {
    $cond_order = ' order by B.first_name asc,B.last_name asc ';
} else if (($sortby == 'realname') && ($order == 'desc')) {
    $cond_order = ' order by B.first_name desc,B.last_name desc ';
} else if ($sortby == 'role') {
    $cond_order = ' order by A.' . "$sortby  $order";
} else {
    $cond_order = ' order by B.' . "$sortby  $order";
}

$sqls1 = str_replace('%COURSE_ID%', $sysSession->course_id, $Sqls['get_course_all_student2']);

$sqls1 .= ' and ' . $role . $cond;

//chkSchoolId('WM_term_major');
//$total_item = $sysConn->GetOne($sqls1);

//// 每頁顯示幾筆
//if (!isset($_POST['page_num'])) {
//    $page_num = sysPostPerPage;
//} else {
//    $page_num = intval($_POST['page_num']);
//}

//$total_page = ceil($total_item / $page_num);

$sqls = str_replace('%COURSE_ID%', $sysSession->course_id, $Sqls['get_course_all_student']);

$sqls .= $cond . ' ';

//if ($_POST['p'] == '') {
//    if ($total_page > 0) {
//        $cur_page = 1;
//    } else {
//        $cur_page = intval($_POST['p']);
//    }
//    
//    if ($cur_page < 0 || $cur_page > $total_page)
//        $cur_page = 1;
//    $limit_begin = (($cur_page - 1) * $page_num);
//    
//} else if ($_POST['p'] == 0) {
//    $cur_page = 0;
//} else if ($_POST['p'] != '') {
//    $cur_page    = intval($_POST['p']);
//    $limit_begin = (($cur_page - 1) * $page_num);
//}

if ($_GET['p'] != '') {
    
    if (intval($_GET['p']) > 0) {
        $sqls .= ' and ' . $role . $cond_order;
    } else {
        $sqls .= ' and ' . $role . $cond_order;
    }
} else {
    
    $sqls .= ' and ' . $role . $cond_order;
}

$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
//if ($cur_page > 0) {
//    $RS = $sysConn->SelectLimit($sqls, $page_num, $limit_begin);
//} else if ($cur_page == 0) {
    $RS = $sysConn->Execute($sqls);
//}

if ($sysConn->ErrorNo() > 0)
    die($sysConn->ErrorMsg());
//  sql query (end)

/**
 * 安全性檢查
 *     1. 身份的檢查
 *     2. 權限的檢查
 *     3. .....
 **/

// 設定車票
// setTicket();

//$sysCL           = array(
//    'Big5' => 'zh-tw',
//    'en' => 'en',
//    'GB2312' => 'zh-cn'
//);
//$ACCEPT_LANGUAGE = $sysCL[$sysSession->lang];
//if (empty($ACCEPT_LANGUAGE))
//    $ACCEPT_LANGUAGE = 'zh-tw';

//$js = <<< BOF
//var html_lang = "{$ACCEPT_LANGUAGE}";
//
//var MSG_SELECT_CANCEL = "{$MSG['title78'][$sysSession->lang]}";
//var MSG_SELECT_ALL = "{$MSG['title77'][$sysSession->lang]}";
//
//var MSG_more_setting = "{$MSG['more_setting'][$sysSession->lang]}";
//var MSG_original_setting = "{$MSG['original_setting'][$sysSession->lang]}";
//
//var sbv = 2;
//var cur_page = {$cur_page};
//var total_page = {$total_page};
//
//var searchkey = "{$searchkey}";
//
//var status = "{$status}";
//var sortby = "{$sortby}";
//var order = "{$order}";
//var flag = "{$flag}";
//
///* 如果是 Mozilla/Firefox 則加上 outerHTML/innerText 的支援 */
//if (navigator.userAgent.indexOf(' Gecko/') != -1) {
//    HTMLElement.prototype.__defineSetter__('outerHTML', function(s) {
//        var range = this.ownerDocument.createRange();
//        range.setStartBefore(this);
//        var fragment = range.createContextualFragment(s);
//        this.parentNode.replaceChild(fragment, this);
//    });
//
//    HTMLElement.prototype.__defineGetter__('outerHTML', function() {
//        return new XMLSerializer().serializeToString(this);
//    });
//
//    HTMLElement.prototype.__defineGetter__('innerText', function() {
//        return this.innerHTML.replace(/<[^>]+>/g, '');
//    });
//}
//
//function trim(val) {
//    var re = /\s/;
//    var result = '';
//    result = val.replace(re, "");
//    return result;
//}
//
//function page(n) {
//    var obj = document.getElementById("queryFm2");
//
//    switch (n) {
//        case -1:
//            obj.p.value = 1;
//            break;
//        case -2:
//            obj.p.value = (cur_page - 1);
//            break;
//        case -3:
//            obj.p.value = (cur_page + 1);
//            break;
//        case -4:
//            obj.p.value = parseInt(total_page);
//            break;
//        default:
//            obj.p.value = parseInt(n);
//
//            break;
//    }
//
//    obj.sortby.value = sortby;
//    obj.order.value = order;
//    obj.s.value = sbv;
//    obj.status.value = status;
//    obj.submit();
//}
//
//function Page_Row(val) {
//
//    var obj = document.getElementById("queryFm2");
//    obj.sortby.value = sortby;
//    obj.order.value = order;
//    obj.s.value = sbv;
//    //    	obj.keyword.value = keyword;
//    obj.status.value = status;
//
//    var nodes = document.getElementsByTagName('option');
//
//    for (var i = 0; i < nodes.length; i++) {
//
//        if (nodes[i].value == val) {
//            nodes[i].selected = true;
//        }
//    }
//
//    obj.submit();
//}
//
///**
// * 顯示 基本資料
// **/
//function showDetail(user, val) {
//    var obj = document.getElementById("actFm");
//
//    if ((typeof(obj) != "object") || (obj == null)) return false;
//    obj.user.value = user;
//    obj.msgtp.value = val;
//
//    obj.submit();
//}
//
///**
// * 全選 or 全消
// **/
//function selFunc(actType) {
//    var obj = document.getElementById('stud_list');
//    var nodes = obj.getElementsByTagName('input');
//    var txt = '';
//    for (var i = 0; i < nodes.length; i++) {
//        if (nodes.item(i).type == 'checkbox') {
//            nodes.item(i).checked = actType;
//        }
//    }
//
//    /*    全選    */
//    var btn1 = document.getElementById("btnSel1");
//    btn1.value = actType ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
//
//    var obj2 = document.getElementById("toolbar1");
//
//    if ((typeof(obj2) == "object") && (obj2 != null)) txt1 = obj2.innerHTML;
//
//    obj3 = document.getElementById("toolbar2");
//    if ((typeof(obj3) == "object") && (obj3 != null)) obj3.innerHTML = txt1;
//
//    obj = document.getElementsByName("more_set1");
//    if (obj[0] && obj[1])
//        obj[1].onclick = obj[0].onclick;
//}
//
///**
// * 單獨點選人員
// **/
//function selPeo(val) {
//    var nodes = null,
//        attr = null;
//    var isSel = "false";
//    var cnt = 0;
//    var txt1 = '';
//
//    var obj = document.getElementById('stud_list');
//    nodes = obj.getElementsByTagName('input');
//
//    for (var i = 0, m = 0; i < nodes.length; i++) {
//        attr = nodes[i].getAttribute("exclude");
//        if ((nodes[i].type == "checkbox") && (attr == null)) {
//            m++;
//            if (nodes[i].checked) cnt++;
//        }
//    }
//
//    // m = (m > 0) ? m - 1 : 0;
//    document.getElementById("ck").checked = (m == cnt);
//
//    /*    全選    */
//    var btn1 = document.getElementById("btnSel1");
//    if (m == cnt) {
//        btn1.value = MSG_SELECT_CANCEL;
//    } else {
//        btn1.value = MSG_SELECT_ALL;
//    }
//
//    var obj = document.getElementById("toolbar1");
//
//    if ((typeof(obj) == "object") && (obj != null)) {
//        txt1 = obj.innerHTML;
//
//        txt1.replace('btnSel1', 'btnSel2');
//
//        obj = document.getElementById("toolbar2");
//        if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt1;
//    }
//
//    obj = document.getElementsByName("more_set1");
//    if (obj[0] && obj[1])
//        obj[1].onclick = obj[0].onclick;
//}
//
//function send_mail() {
//    var obj = document.getElementById('stud_list');
//    var ml = '';
//    var ss = /,$/;
//    var aa;
//
//    var nodes = obj.getElementsByTagName('input');
//    for (var i = 0; i < nodes.length; i++) {
//        if ((nodes.item(i).type == 'checkbox') && (nodes.item(i).checked == true) && nodes.item(i).name != 'ck') {
//            ml += nodes.item(i).value + ',';
//        }
//    }
//
//    ml = ml.replace(ss, '');
//
//    if (ml.length == 0) {
//        alert("{$MSG['send_error'][$sysSession->lang]}");
//        return false;
//    }
//
//    // send mail stage
//    obj = document.getElementById("mailFm");
//    obj.send_user.value = ml;
//    obj.submit();
//}
//
//function send_mail2() {
//
//    var obj = document.getElementById("stud_list");
//    var col = '';
//    var total_rows = obj.rows.length - 1;
//
//    var temp_title = new Array("", "{$MSG['username'][$sysSession->lang]} ", "{$MSG['realname'][$sysSession->lang]} ", "{$MSG['gender'][$sysSession->lang]} ", "{$MSG['birthday'][$sysSession->lang]} ", "{$MSG['person_status'][$sysSession->lang]} ", "{$MSG['email'][$sysSession->lang]} ");
//
//    document.mailselfFm.mail_txt.value = '<html>' +
//        '<head>' +
//        '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >' +
//        '<meta http-equiv="Content-Language" content="' + html_lang + '" > ' +
//        '<title>' + "{$MSG['title69'][$sysSession->lang]}" + '</title>' +
//        '<style type="text/css">' +
//        '.cssTrHead {' +
//        '	font-size: 12px;' +
//        '	line-height: 16px;' +
//        '	text-decoration: none;' +
//        '	letter-spacing: 2px;' +
//        '	color: #000000;' +
//        '	background-color: #C7D8FA;' +
//        '	font-family: Tahoma, "Times New Roman", Times, serif;' +
//        '	}' +
//        '.cssTrEvn {' +
//        '	font-size: 12px;' +
//        '	line-height: 16px;' +
//        '	text-decoration: none;' +
//        '	letter-spacing: 2px;' +
//        '	color: #000000;' +
//        '	background-color: #FFFFFF;' +
//        '	font-family: Tahoma, "Times New Roman", Times, serif;' +
//        '}' +
//        '.cssTrOdd {' +
//        '	font-size: 12px;' +
//        '	line-height: 16px;' +
//        '	text-decoration: none;' +
//        '	letter-spacing: 2px;' +
//        '	color: #000000;' +
//        '	background-color: #ECF1F7;' +
//        '	font-family: Tahoma, "Times New Roman", Times, serif;' +
//        '}' +
//        '.cssTable {' +
//        'background-color: #E3E9F2; ' +
//        'border: 1px solid #5176D2; ' +
//        '} ' +
//        '</style>' +
//        '</head>' +
//        '<body  >';
//
//    document.mailselfFm.mail_txt.value += '<tr>';
//    document.mailselfFm.mail_txt.value += '<td valign="top" >';
//
//    document.mailselfFm.mail_txt.value += '<table id="stud_list" width="100%" border="0" cellspacing="1" cellpadding="3" style="display:block" class="cssTable" >';
//
//    for (var i = 2; i < total_rows; i++) {
//
//        col = col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
//
//        if (i == 2) {
//            col = 'class="cssTrHead"';
//
//            document.mailselfFm.mail_txt.value += '<tr ' + col + '>' +
//                '<td>' + temp_title[1] + '</td>' +
//                '<td>' + temp_title[2] + '</td>' +
//                '<td>' + temp_title[3] + '</td>' +
//                '<td>' + temp_title[4] + '</td>' +
//                '<td>' + temp_title[5] + '</td>' +
//                '<td>' + temp_title[6] + '</td>' +
//                '<td>' + obj.rows[i].cells[7].innerHTML + '</td>';
//
//            for (var j = 8; j < obj.rows[i].cells.length; j++) {
//                if (obj.rows[i].cells[j].style.display != 'none') {
//                    document.mailselfFm.mail_txt.value += '<td>' + obj.rows[i].cells[j].innerHTML + '</td>';
//                }
//            }
//
//            document.mailselfFm.mail_txt.value += '</tr>';
//
//        } else {
//            if (!obj.rows[i].cells[1]) {
//                document.mailselfFm.mail_txt.value += '<tr ' + col + '><td colspan="7" align="middle">' + obj.rows[i].cells[0].innerHTML + '</td></tr>';
//                continue;
//            }
//            document.mailselfFm.mail_txt.value += '<tr ' + col + '>' +
//                '<td>' + obj.rows[i].cells[1].innerHTML + '</td>' +
//                '<td>' + obj.rows[i].cells[2].innerHTML + '</td>';
//
//            /*
//                將 性別圖片的 hyperlink 移除 (remove hyperlink)
//            */
//
//            if ((obj.rows[i].cells[3].innerHTML.indexOf('female.gif') == -1) && (obj.rows[i].cells[3].outerHTML.indexOf('male.gif') == -1)) {
//                document.mailselfFm.mail_txt.value += '<td>' + obj.rows[i].cells[3].innerHTML + '</td>';
//            } else if (obj.rows[i].cells[3].innerHTML.indexOf('/theme/default/learn/male.gif') > -1) {
//                document.mailselfFm.mail_txt.value += '<TD class=cssTd align=center><img src="/theme/default/learn/male.gif" type="image/jpeg" align="absmiddle" border="0"></td>';
//            } else if (obj.rows[i].cells[3].innerHTML.indexOf('/theme/default/learn/female.gif') > -1) {
//                document.mailselfFm.mail_txt.value += '<TD class=cssTd align=center><img src="/theme/default/learn/female.gif" type="image/jpeg" align="absmiddle" border="0"></td>';
//            }
//
//            document.mailselfFm.mail_txt.value += '<td>' + obj.rows[i].cells[4].innerHTML + '</td>' +
//                '<td>' + obj.rows[i].cells[5].innerHTML + '</td>' +
//                '<td>' + obj.rows[i].cells[6].innerHTML + '</td>' +
//                '<td>' + obj.rows[i].cells[7].innerHTML + '</td>';
//
//            for (var j = 8; j < obj.rows[i].cells.length; j++) {
//                if (obj.rows[i].cells[j].style.display != 'none') {
//                    document.mailselfFm.mail_txt.value += '<td>' + obj.rows[i].cells[j].innerHTML + '</td>';
//                }
//            }
//            document.mailselfFm.mail_txt.value += '</tr>';
//
//        }
//    }
//
//    document.mailselfFm.mail_txt.value += '</table>' +
//        '</td>' +
//        '</tr>' +
//        '</' + 'body>' +
//        '</html>';
//
//    document.mailselfFm.submit();
//}
//
//function more_setting1() {
//
//    var obj = document.getElementById("stud_list");
//    if (obj.rows.length == 5 && obj.rows[3].cells.length == 1) return;
//
//    for (var i = 2; i < obj.rows.length - 1; i++) {
//        obj.rows[i].cells[8].style.display = "";
//        obj.rows[i].cells[9].style.display = "";
//        obj.rows[i].cells[10].style.display = "";
//        obj.rows[i].cells[11].style.display = "";
//        obj.rows[i].cells[12].style.display = "";
//    }
//
//    obj = document.getElementById("toolbar1");
//
//    if ((typeof(obj) == "object") && (obj != null)) txt1 = obj.innerHTML;
//
//    obj = document.getElementById("toolbar2");
//    if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt1;
//
//    obj = document.getElementsByName("more_set1");
//    for (var i = 0; i < obj.length; i++) {
//        obj[i].value = MSG_original_setting;
//
//        obj[i].onclick = function() {
//            org_setting2();
//        };
//    }
//
//}
//
//function org_setting2() {
//    var obj = document.getElementById("stud_list");
//    if (obj.rows.length == 5 && obj.rows[3].cells.length == 1) return;
//
//    for (var i = 2; i < obj.rows.length - 1; i++) {
//        obj.rows[i].cells[8].style.display = "none";
//        obj.rows[i].cells[9].style.display = "none";
//        obj.rows[i].cells[10].style.display = "none";
//        obj.rows[i].cells[11].style.display = "none";
//        obj.rows[i].cells[12].style.display = "none";
//    }
//
//    obj = document.getElementById("toolbar1");
//    if ((typeof(obj) == "object") && (obj != null)) txt1 = obj.innerHTML;
//
//    obj = document.getElementById("toolbar2");
//    if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt1;
//
//    obj = document.getElementsByName("more_set1");
//    for (var i = 0; i < obj.length; i++) {
//        obj[i].value = MSG_more_setting;
//
//        obj[i].onclick = function() {
//            more_setting1();
//        };
//    }
//}
///*
// *  顯示圖片的size
// */
//function picReSize() {
//    var orgW = 0,
//        orgH = 0;
//    var demagnify = 0;
//    var node = document.getElementById("MyPic");
//
//    if ((typeof(node) != "object") || (node == null)) return false;
//    orgW = parseInt(node.width);
//    orgH = parseInt(node.height);
//
//    if ((orgW > 110) || (orgH > 120)) {
//        demagnify = (((orgW / 110) > (orgH / 120)) ? parseInt(orgW / 110) : parseInt(orgH / 120)) + 1;
//        node.width = parseInt(orgW / demagnify);
//        node.height = parseInt(orgH / demagnify);
//    }
//    node.parentNode.style.height = node.height + 3;
//    // Mv2View("active_pic");
//}
//
//function OpenPic(nod, a) {
//    var obj = document.getElementById("active_pic");
//    if ((obj != null) && (obj.style.display == "")) return false;
//
//    var obj1 = document.getElementById("divPic");
//
//    /**
//     * 在 圖片 有用到 onload="picReSize(); Mv2View(\'active_pic\');"
//     */
//    var user_pic = '<img src="showpic.php?a=' + a + '" onload="picReSize();" type="image/jpeg" id="MyPic" name="MyPic" borer="0" align="absmiddle" loop="0">';
//
//    obj1.innerHTML = user_pic;
//
//    obj.style.left = nod.clientX + 30;
//    obj.style.top = (nod.clientY / 2) + parseInt(document.body.scrollTop);
//
//    if (obj != null) obj.style.display = "";
//
//}
//
//function ClosePic() {
//
//    var obj = document.getElementById("active_pic");
//    if (obj != null) obj.style.display = "none";
//}
//
///**
// * 同步全選或全消的按鈕與 checkbox
// * @version 1.0
// **/
//function selected_box() {
//    var obj = document.getElementById("ck");
//    selFunc(!obj.checked);
//}
//
///*
// * 標題排序
// */
//function chgPageSort(val) {
//
//    var obj = document.getElementById("queryFm2");
//
//    var re = /asc/ig;
//
//    switch (val) {
//        case 1:
//            obj.sortby.value = 'username';
//            break;
//        case 2:
//            obj.sortby.value = 'realname';
//            break;
//        case 3:
//            obj.sortby.value = 'gender';
//            break;
//        case 4:
//            obj.sortby.value = 'birthday';
//            break;
//        case 5:
//            obj.sortby.value = 'role';
//            break;
//        case 6:
//            obj.sortby.value = 'email';
//            break;
//    }
//
//    if ((typeof(obj) != "object") || (obj == null)) return false;
//
//    if (trim(obj.order.value) == 'asc') {
//        obj.order.value = 'desc';
//    } else if (trim(obj.order.value) == 'desc') {
//        obj.order.value = 'asc';
//    }
//
//    obj.searchkey.value = searchkey;
//    obj.status.value = status;
//    // obj.keyword.value = keyword;
//    obj.flag.value = flag;
//    obj.p.value = cur_page;
//    document.queryFm2.submit();
//}
//
//window.onload = function() {
//    var txt = "",
//        txt1 = "";
//
//    var obj = document.getElementById("toolbar1");
//
//    if ((typeof(obj) == "object") && (obj != null)) txt1 = obj.innerHTML;
//
//    txt1.replace('btnSel1', 'btnSel2');
//
//    obj = document.getElementById("toolbar2");
//    if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt1;
//
//};
//BOF;

//showXHTML_head_B($MSG['title69'][$sysSession->lang]);
//    showXHTML_CSS('include', "/theme/{$sysSession->theme}/learn/wm.css");
//    showXHTML_script('inline', $js);
//showXHTML_head_E();
    
// 取通訊錄
if ($RS->RecordCount() == 0) {
//    showXHTML_tr_B('class="cssTrEvn"');
//    showXHTML_td_B(' colspan="13" align="center"');
//    echo $MSG['unfinded'][$sysSession->lang];
//    showXHTML_td_E();
//    showXHTML_tr_E();
} else {

    $sRoles = array_reverse($sysRoles);
    array_shift($sRoles);
    array_shift($sRoles);
    array_shift($sRoles);
    array_shift($sRoles);
    array_pop($sRoles);
//    
//    
//    echo '<pre>';
//    var_dump($sRoles);
//    echo '</pre>';
    
    $teacherRoles = array(
        'assistant' => array(64, 'assistant'),
        'instructor' => array(128, 'teacher'),
        'teacher' => array(512, 'teacher')
    );
    
    
//    echo '<pre>';
//    var_dump($sRoles);
//    echo '</pre>';
    
    
    
    $i = 0;
    if (empty($_COOKIE['show_me_info']) === false) {
        echo '<pre>';
        var_dump($isTA, 'homepage');
        echo '</pre>';
    }
    while (!$RS->EOF) {
        
        /**
         * 過濾擁有管理者身份的助教人員
         * ----------------------------
         * 由於管理者在查詢某門課的問題時，會以加入"助教"身份來查詢，
         * 但會被學生認為是此門課的助教，來信詢問課程相關內容，造成彼此困擾。
         * ----------------------------
         **/
        if ($RS->fields['role']&$sysRoles['assistant']) {
            if (aclCheckRole($RS->fields['username'], ($sysRoles['manager'] | $sysRoles['administrator'] | $sysRoles['root']), $sysSession->school_id)) {
                $RS->MoveNext();
                continue;
            }
        }
        
        $datalist[$i]['username'] = $RS->fields['username'];
        $datalist[$i]['hid'] = $RS->fields['hid'];
        $datalist[$i]['gender'] = $RS->fields['gender'];
        
        // 本筆帳號是不是本門課老師 或 講師
        $isTeacher = aclCheckRole($RS->fields['username'], $sysRoles['teacher'] | $sysRoles['instructor'] , $sysSession->course_id);
        
        // 個人網站
        $web = array(
            'fb' => 'www.facebook.com',
            'gg' => 'plus.google.com',
            'plk' => 'www.plurk.com',
            'tw' => 'twitter.com'
        );
        if (empty($_COOKIE['show_me_info']) === false) {
            echo '<pre>';
            var_dump($RS->fields['username'], $isTeacher, $RS->fields['hid'] & 128);
            echo '</pre>';
        }
        // $RS->fields['hid'] & x -> 0是顯示, >=1隱藏
        // 顯示：是本課老師且該筆資料不是老師 或者 直接設定顯示
        if (($isTA && $isTeacher === '0') || ($RS->fields['hid'] & 128) === 0) {
            foreach ($web as $k => $v) {
                if (preg_match("/" . $v . "/", $RS->fields['homepage']) === 1) {
                    $homepage = '<div class="icon-small-circle-' . $k . ' homepage" data-url="' . $RS->fields['homepage'] . '" style="position: absolute; top: 6.3em; left: 10.7em; cursor: pointer;" title="' . $MSG['homepage'][$sysSession->lang] . '"></div>';
                    break;
                } else {
                    $homepage = '';
                }
            }
        } else {
            $homepage = '';
        }
        $datalist[$i]['homepage'] = $homepage;

        // 真實姓名
        $realname = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);        
        $datalist[$i]['realname'] = $realname;

        // 相片
        if (($isTA && $isTeacher === '0') || ($RS->fields['hid'] & 32) === 0) {
            $photo = '<img class="img-circle" src="showpic.php?a=' . $RS->fields['username'] . '" name="ohoto" style="width: 8em; height: 8em;" title="' . $MSG['photo'][$sysSession->lang] . '">';
        } else {
            $photo = '<img class="img-circle" src="/public/images/icon_personal_pic.png" name="photo" style="width: 8em; height: 8em;" title="' . $MSG['photo'][$sysSession->lang] . '">';
        }
        $datalist[$i]['photo'] = $photo;
        
        //  生日
        if (($isTA && $isTeacher === '0') || ($RS->fields['hid'] & 8) === 0) {
            $birthday = $RS->fields['birthday'];
        } else {
            $birthday = '';
        }
        $datalist[$i]['birthday'] = $birthday;

        // 身份
        $Role = intval($RS->fields['role']);
        // 身份-文字
        $temp_role = '';
        foreach ($sRoles as $k => $v) {
            if ($Role & $v){
                $temp_role .= $MSG[$k][$sysSession->lang] . ' & ';
            }
        }
        $temp_role = htmlspecialchars(substr($temp_role, 0, -3));
        $datalist[$i]['role'] = $temp_role;
        
        // 身份-圖片
        $hasRole = array();
        foreach ($teacherRoles as $k => $v) {
            if ($Role & $v[0]){
                $hasRole[] = $v[1];
            }
        }
        $hasRole = array_unique($hasRole);
        $imgRole = '';
        foreach ($hasRole as $v) {
            $imgRole .= '<div class="icon-small-circle-' . $v . '"></div>';
        }
        $datalist[$i]['img_role'] = $imgRole;
       
        // email
        if (strlen($RS->fields['email']) > 0) {
            $email = $RS->fields['email'];
        } else {
            $email = '';
        }
        $datalist[$i]['email'] = $email;

        //  行動電話 (personal)
        if (($isTA && $isTeacher === '0') || ($RS->fields['hid'] & 16384) === 0) {
            $cellPhone = htmlspecialchars($RS->fields['cell_phone']);
        } else {
            $cellPhone = '';
        }
        $datalist[$i]['cell'] = $cellPhone;

        //  電話 (家)
        if (($isTA && $isTeacher === '0') || ($RS->fields['hid'] & 256) === 0) {
            $homeTel = htmlspecialchars($RS->fields['home_tel']);
        } else {
            $homeTel = '';
        }
        $datalist[$i]['home_tel'] = $homeTel;

        //  電話 (公司)
        if (($isTA && $isTeacher === '0') || ($RS->fields['hid'] & 2048) === 0) {
            $officeTel = htmlspecialchars($RS->fields['office_tel']);
        } else {
            $officeTel = '';
        }
        $datalist[$i]['office_tel'] = $officeTel;

        //  地址 (家)
        if (($isTA && $isTeacher === '0') || ($RS->fields['hid'] & 1024) === 0) {
            $homeAddress = htmlspecialchars($RS->fields['home_address']);
        } else {
            $homeAddress = '';
        }
        $datalist[$i]['home_address'] = $homeAddress;

        //  地址 (公司)
        if (($isTA && $isTeacher === '0') || ($RS->fields['hid'] & 8192) === 0) {
            $officeAddress = htmlspecialchars($RS->fields['office_address']);
        } else {
            $officeAddress = '';
        }
        $datalist[$i]['office_address'] = $officeAddress;

        //  傳真 (家)
        if (($isTA && $isTeacher === '0') || ($RS->fields['hid'] & 512) === 0) {
            $homeFax = htmlspecialchars($RS->fields['home_fax']);
        } else {
            $homeFax = '';
        }
        $datalist[$i]['home_fax'] = $homeFax;
        
        $i += 1;
        $RS->MoveNext();
    }
}    
//echo '<pre>';
//var_dump($datalist);
//echo '</pre>';
// assign
//$smarty->assign('post', $_POST);
//$smarty->assign('msg', $MSG);
//$smarty->assign('sysSession', $sysSession);

// 防止XSS攻擊
foreach ($datalist as $s => $stud) {
    foreach ($stud as $k => $v) {
        if (!(in_array($k, array('photo', 'img_role', 'homepage')))) {
            $datalist[$s][$k] = htmlspecialchars($v);
        }
    }
}

        
if (empty($_COOKIE['show_me_info']) === FALSE) {
    echo '<pre>';
    var_dump($datalist);
    echo '</pre>';
}
$smarty->assign('datalist', $datalist);

// output
// output
if ($profile['isPhoneDevice']) {
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('common/course_header.tpl');
    $smarty->display('learn/stud_list.tpl');
    $smarty->display('common/tiny_footer.tpl');
}else{
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('learn/stud_list.tpl');
    $smarty->display('common/tiny_footer.tpl');
}
































//showXHTML_body_B();
//    $arry[] = array(
//        $MSG['title69'][$sysSession->lang],
//        'queryTable'
//    );
//
//    echo '<div align="center">';
//    showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="mt" ');
//    showXHTML_tr_B();
//    showXHTML_td_B();
//    showXHTML_tabs($arry, 1);
//    showXHTML_td_E();
//    showXHTML_tr_E();
//    showXHTML_tr_B();
//    showXHTML_td_B('valign="top"');
//    showXHTML_form_B('action="stud_list.php" method="post" style="display:inline"', 'queryFm2');
//        showXHTML_input('hidden', 'sortby', $sortby, '', 'id="sortby"');
//        showXHTML_input('hidden', 'order', $order, '', 'id="order"');
//        showXHTML_input('hidden', 'flag', $flag, '', 'id="flag"');
//        showXHTML_input('hidden', 'p', '', '', 'id="p"');
//        showXHTML_input('hidden', 's', '', '', 'id="s"');
//        showXHTML_table_B('id="stud_list" width="100%" border="0" cellspacing="1" cellpadding="3" id="delTable2" style="display:block" class="cssTable"');
//        // 查詢搜尋
//        showXHTML_tr_B('class="cssTrEvn"');
//        showXHTML_td_B('id="cols" colspan="13" ');
//        // 搜尋
//        echo "{$MSG['search_keyword'][$sysSession->lang]} ";
//
//        showXHTML_input('select', 'status', array(
//            'auditor' => $MSG['auditor'][$sysSession->lang],
//            'student' => $MSG['student'][$sysSession->lang],
//            'assistant' => $MSG['assistant'][$sysSession->lang],
//            'instructor' => $MSG['instructor'][$sysSession->lang],
//            'teacher' => $MSG['teacher'][$sysSession->lang],
//            'all' => $MSG['all'][$sysSession->lang]
//        ), $status, 'class="cssInput" id="status"');
//
//        echo '&nbsp;&nbsp;', '<select name="searchkey" class="cssInput" id="searchkey">', '<option value="real" ', (($searchkey == 'real') ? ' selected' : ''), '>', $MSG['realname'][$sysSession->lang], '</option>', '<option value="account" ', (($searchkey == 'account') ? ' selected' : ''), '>', $MSG['username'][$sysSession->lang], '</option>', '<option value="email" ', (($searchkey == 'email') ? ' selected' : ''), '>', $MSG['email'][$sysSession->lang], '</option>', '</select>', '&nbsp;', $MSG['inside'][$sysSession->lang];
//
//        if (($keyword1 == '') && ($flag == 0)) {
//            $keyword1 = $MSG['keyword'][$sysSession->lang];
//        }
//        showXHTML_input('text', 'keyword', htmlspecialchars($keyword1), '', 'id="keyword" size="20"  width="30" class="cssInput" onclick="this.value=\'\'"');
//        echo $MSG['inside1'][$sysSession->lang];
//
//        showXHTML_input('button', '', $MSG['confirm'][$sysSession->lang], '', 'class="cssBtn" onclick="document.getElementById(\'flag\').value = \'1\';document.getElementById(\'queryFm2\').submit();"');
//        showXHTML_td_E();
//        showXHTML_tr_E();
//
//        // 換頁與動作功能列
//        showXHTML_tr_B('class="cssTrEvn"');
//        showXHTML_td_B('colspan="13" nowrap id="toolbar1"');
//        showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" ');
//        showXHTML_tr_B('class="cssTrEvn"');
//        showXHTML_td_B();
//        showXHTML_input('button', '', $MSG['title77'][$sysSession->lang], '', ' id="btnSel1" class="cssBtn" onclick="selected_box();"');
//        echo $MSG['page'][$sysSession->lang];
//
//        for ($j = 0; $j <= $total_page; $j++) {
//            if ($j == 0) {
//                $P[$j] = $MSG['all'][$sysSession->lang];
//            } else {
//                $P[$j] = $j;
//            }
//        }
//        showXHTML_input('select', '', $P, $cur_page, 'size="1" onchange="page(this.value);"');
//
//        // 每頁顯示幾筆
//        echo $MSG['title92'][$sysSession->lang];
//        $page_array = array(
//            10 => $MSG['default_amount'][$sysSession->lang],
//            20 => 20,
//            50 => 50,
//            100 => 100,
//            200 => 200,
//            400 => 400
//        );
//        showXHTML_input('select', 'page_num', $page_array, $page_num, 'class="cssInput" id="page_num" onchange="Page_Row(this.value)"');
//        echo $MSG['title93'][$sysSession->lang];
//
//        showXHTML_input('button', 'firstBtn1', $MSG['first1'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page == 1) || ($cur_page == 0)) ? 'disabled="true" ' : 'onclick="page(-1);"') . ' title=' . $MSG['title40'][$sysSession->lang]);
//        showXHTML_input('button', 'prevBtn1', $MSG['prev'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page == 1) || ($cur_page == 0)) ? 'disabled="true" ' : 'onclick="page(-2);"') . ' title=' . $MSG['title41'][$sysSession->lang]);
//        showXHTML_input('button', 'nextBtn1', $MSG['next'][$sysSession->lang], '', 'id="nextBtn1"  class="cssBtn" ' . ((($cur_page == 0) || ($cur_page == $total_page)) ? 'disabled="true" ' : 'onclick="page(-3);"') . ' title=' . $MSG['title42'][$sysSession->lang]);
//        showXHTML_input('button', 'lastBtn1', $MSG['last1'][$sysSession->lang], '', 'id="lastBtn1"  class="cssBtn" ' . ((($cur_page == 0) || ($cur_page == $total_page)) ? 'disabled="true" ' : 'onclick="page(-4);"') . ' title=' . $MSG['title42'][$sysSession->lang]);
//        showXHTML_input('button', 'sendmail1', $MSG['send_mail'][$sysSession->lang], '', 'id="sendmail1" class="cssBtn" onclick="send_mail();"');
//        showXHTML_input('button', 'sendmailtome', $MSG['thispage_sendme'][$sysSession->lang], '', 'id="sendmailtome" class="cssBtn" onclick="send_mail2();"');
//        showXHTML_input('button', 'more_set1', $MSG['more_setting'][$sysSession->lang], '', 'id="more_set1" name="more_set1" class="cssBtn" onclick="more_setting1();"' . (($RS->RecordCount() == 0) ? ' disabled' : ''));
//
//        showXHTML_td_E();
//        showXHTML_tr_E();
//        showXHTML_table_E();
//        showXHTML_td_E();
//        showXHTML_tr_E();
//
//        showXHTML_tr_B('class="cssTrHead"');
//        showXHTML_td_B('align="center"');
//        showXHTML_input('checkbox', 'ck', '', '', '" id="ck" onclick="selFunc(this.checked);" exclude="true"' . 'title=' . $MSG['select_all'][$sysSession->lang]);
//        showXHTML_td_E();
//        showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(1);" title="' . $MSG['username'][$sysSession->lang] . '"');
//        echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
//        echo $MSG['username'][$sysSession->lang];
//        echo ($sortby == 'username') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
//        echo '</a>';
//        showXHTML_td_E();
//        showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(2);" title="' . $MSG['realname'][$sysSession->lang] . '"');
//        echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
//        echo $MSG['realname'][$sysSession->lang];
//        echo ($sortby == 'realname') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
//        echo '</a>';
//        showXHTML_td_E();
//
//        showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(3);" title="' . $MSG['gender'][$sysSession->lang] . '"');
//        echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
//        echo $MSG['gender'][$sysSession->lang];
//        echo ($sortby == 'gender') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
//        echo '</a>';
//        showXHTML_td_E();
//
//        showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(4);" title="' . $MSG['birthday'][$sysSession->lang] . '"');
//        echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
//        echo $MSG['birthday'][$sysSession->lang];
//        echo ($sortby == 'birthday') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
//        echo '</a>';
//        showXHTML_td_E();
//
//        showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(5);" title="' . $MSG['person_status'][$sysSession->lang] . '"');
//        echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
//        echo $MSG['person_status'][$sysSession->lang];
//        echo ($sortby == 'role') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
//        echo '</a>';
//        showXHTML_td_E();
//
//        showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(6);" title="' . $MSG['email'][$sysSession->lang] . '"');
//        echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
//        echo $MSG['email'][$sysSession->lang];
//        echo ($sortby == 'email') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
//        echo '</a>';
//        showXHTML_td_E();
//
//        showXHTML_td('align="center" nowrap ', $MSG['cell_phone'][$sysSession->lang]);
//        showXHTML_td('align="center" nowrap  style="display:none"', $MSG['home_tel'][$sysSession->lang]);
//        showXHTML_td('align="center" nowrap  style="display:none"', $MSG['office_tel'][$sysSession->lang]);
//        showXHTML_td('align="center" nowrap  style="display:none"', $MSG['home_address'][$sysSession->lang]);
//        showXHTML_td('align="center" nowrap  style="display:none"', $MSG['office_address'][$sysSession->lang]);
//        showXHTML_td('align="center" nowrap  style="display:none"', $MSG['home_fax'][$sysSession->lang]);
//        showXHTML_tr_E();
//
//        $i = (($cur_page - 1) * sysPostPerPage) + 1;
//
//        //  判斷 $RS 是否有 record (begin)
//        if ($RS->RecordCount() == 0) {
//            showXHTML_tr_B('class="cssTrEvn"');
//            showXHTML_td_B(' colspan="13" align="center"');
//            echo $MSG['unfinded'][$sysSession->lang];
//            showXHTML_td_E();
//            showXHTML_tr_E();
//        } else {
//
//            $sRoles = array_reverse($sysRoles);
//            array_shift($sRoles);
//            array_shift($sRoles);
//            array_shift($sRoles);
//            array_shift($sRoles);
//            array_pop($sRoles);
//
//            while (!$RS->EOF) {
//                // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
//                $real = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);
//                $col  = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
//                showXHTML_tr_B($col);
//                showXHTML_td_B('align="center"');
//                showXHTML_input('checkbox', 'sel[]', $RS->fields['username'], '', ' onclick="selPeo(this.checked);" ');
//                showXHTML_td_E();
//                showXHTML_td_B('align="left"');
//                echo '<div style="width: 100px; overflow:hidden;" title="' . $RS->fields['username'] . '">' . $RS->fields['username'] . '</div>';
//                showXHTML_td_E();
//
//                showXHTML_td_B('align="left"');
//                echo '<div style="width: 100px; overflow:hidden;" title="' . htmlspecialchars($real) . '">' . $real . '</div>';
//                showXHTML_td_E();
//
//                showXHTML_td_B('align="center"');
//                // 性別,相片
//                if (($RS->fields['hid'] & 4) && (!$isTA))
//                    $imsSrc = '/theme/' . $sysSession->theme . '/learn/communication/hide.gif';
//                else
//                    $imsSrc = '/theme/' . $sysSession->theme . '/learn/' . ($RS->fields['gender'] == 'M' ? 'male.gif' : 'female.gif');
//
//                if (($RS->fields['hid'] & 32) && (!$isTA))
//                    $extra = 'alt="' . $MSG['hide'][$sysSession->lang] . '"';
//                else
//                    $extra = 'onmouseout="ClosePic();" onMouseMove="OpenPic(event, \'' . $RS->fields['username'] . '\');" style="cursor: pointer; cursor: hand;"';
//
//                echo '<img src="' . $imsSrc . '" type="image/jpeg" align="absmiddle" border="0" ' . $extra . '>';
//
//                showXHTML_td_E();
//
//                //  生日
//                showXHTML_td_B('align="left" nowrap');
//
//                if (($RS->fields['hid'] & 8) && !$isTA) {
//                    echo $hid_ico;
//
//                } else {
//                    echo $RS->fields['birthday'];
//                }
//
//                showXHTML_td_E();
//
//                // 身份
//                showXHTML_td_B('align="left" nowrap');
//                $temp_role = '';
//
//                $Role = intval($RS->fields['role']);
//                foreach ($sRoles as $k => $v)
//                    if ($Role & $v)
//                        $temp_role .= $MSG[$k][$sysSession->lang] . ' & ';
//                $temp_role = htmlspecialchars(substr($temp_role, 0, -3));
//
//                echo '<div style="width: 100px; overflow:hidden;" title="' . $temp_role . '">' . $temp_role . '</div>';
//                showXHTML_td_E();
//                // email
//                showXHTML_td_B('align="left" width="100" ');
//                if (strlen($RS->fields['email']) > 0) {
//                    // echo '<div id="email[]" style="width: 100px; overflow:hidden;" title="' . $RS->fields['email'] . '">' . '<a href="mailto:' . $RS->fields['email'] . '">' . $RS->fields['email'] . '</a>' . '</div>';
//                    echo '<a href="mailto:' . $RS->fields['email'] . '">' . $RS->fields['email'] . '</a>';
//                } else {
//                    echo '&nbsp;';
//                }
//                showXHTML_td_E();
//
//                //  行動電話 (personal)
//                showXHTML_td_B('align="left"');
//
//                if (($RS->fields['hid'] & 16384) && !$isTA) {
//                    echo $hid_ico;
//                } else {
//                    echo '<div style="width: 100px; overflow:hidden;" title="' . $RS->fields['cell_phone'] . '">' . $RS->fields['cell_phone'] . '</div>';
//                }
//
//                showXHTML_td_E();
//
//                //  電話 (家)
//                showXHTML_td_B('align="left" style="display:none"');
//
//                if (($RS->fields['hid'] & 256) && !$isTA) {
//                    echo $hid_ico;
//                } else {
//                    echo '<div style="width: 100px; overflow:hidden;" title="' . $RS->fields['home_tel'] . '">' . $RS->fields['home_tel'] . '</div>';
//                }
//
//                showXHTML_td_E();
//
//                //  電話 (公司)
//                showXHTML_td_B('align="left" style="display:none"');
//
//                if (($RS->fields['hid'] & 2048) && !$isTA) {
//                    echo $hid_ico;
//                } else {
//                    echo '<div style="width: 100px; overflow:hidden;" title="' . $RS->fields['office_tel'] . '">' . $RS->fields['office_tel'] . '</div>';
//                }
//
//                showXHTML_td_E();
//
//                //  地址 (家)
//                showXHTML_td_B('align="left" nowrap style="display:none"');
//
//                if (($RS->fields['hid'] & 1024) && !$isTA) {
//                    echo $hid_ico;
//                } else {
//                    echo '<div style="width: 200px; overflow:hidden;" title="' . $RS->fields['home_address'] . '">' . $RS->fields['home_address'] . '</div>';
//                }
//
//                showXHTML_td_E();
//
//                //  地址 (公司)
//                showXHTML_td_B('align="left" nowrap style="display:none"');
//
//                if (($RS->fields['hid'] & 8192) && !$isTA) {
//                    echo $hid_ico;
//                } else {
//                    echo '<div style="width: 200px; overflow:hidden;" title="' . $RS->fields['office_address'] . '">' . $RS->fields['office_address'] . '</div>';
//                }
//
//                showXHTML_td_E();
//
//                //  傳真 (家)
//                showXHTML_td_B('align="left" style="display:none"');
//
//                if (($RS->fields['hid'] & 512) && !$isTA) {
//                    echo $hid_ico;
//                } else {
//                    echo '<div style="width: 100px; overflow:hidden;" title="' . $RS->fields['home_fax'] . '">' . $RS->fields['home_fax'] . '</div>';
//                }
//
//                showXHTML_td_E();
//
//                showXHTML_tr_E();
//
//                $RS->MoveNext();
//            }
//        }
//        //  判斷 $RS 是否有 record (end)
//        // 換頁與動作功能列 (function line)
//        showXHTML_tr_B('class="cssTrEvn"');
//        showXHTML_td_B('colspan="13" nowrap  id="toolbar2"');
//        showXHTML_td_E();
//        showXHTML_tr_E();
//
//        showXHTML_table_E();
//    showXHTML_form_E();
//    showXHTML_td_E();
//    showXHTML_tr_E();
//    showXHTML_table_E();
//    echo '</div>';
//
//    //  寄信
//    $ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit' . $sysSession->username);
//    showXHTML_form_B('action="send_mail.php" method="post" enctype="multipart/form-data" style="display:none"', 'mailFm');
//        showXHTML_input('hidden', 'ticket', $ticket, '', '');
//        showXHTML_input('hidden', 'send_user', '', '', '');
//    showXHTML_form_E();
//
//    //  將本頁寄給自己
//    $ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit' . $sysSession->username);
//    showXHTML_form_B('action="send_mailself.php" method="post" enctype="multipart/form-data" style="display:none"', 'mailselfFm');
//        showXHTML_input('hidden', 'ticket', $ticket, '', '');
//        showXHTML_input('hidden', 'send_user', '', '', '');
//        showXHTML_input('hidden', 'mail_txt', '', '', '');
//    showXHTML_form_E();
//
//    // 顯示圖片
//    $ary2   = array();
//    $ary2[] = array(
//        $MSG['show_pic'][$sysSession->lang],
//        'active_pic'
//    );
//
//    showXHTML_tabFrame_B($ary2, 1, 'fmSetting', 'active_pic', ' style="position:absolute;" ', true);
//        showXHTML_table_B('width="110" border="0" cellspacing="1" cellpadding="3" class="cssInput"');
//            showXHTML_tr_B('class="cssTrOdd"');
//            showXHTML_td_B('align="center" valign="middle" nowrap="nowrap" width="110"');
//            echo '<span id="divPic" align="center" valign="middle"></span>';
//            showXHTML_td_E();
//            showXHTML_tr_E();
//        showXHTML_table_E();
//    showXHTML_tabFrame_E();
//
//showXHTML_body_E();
