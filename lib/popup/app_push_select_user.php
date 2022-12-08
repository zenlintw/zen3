<?php
/**
 * 選擇帳號 (推播用)
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lang/app_server_push.php');
require_once(sysDocumentRoot . '/lib/username.php');

if (($sysSession->username == "guest") || ($sysSession->env === 'learn')) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$lines = sysPostPerPage;

$where = 'AN.`device_token_abandon` = 0';

if ($sysSession->env === 'teach') {
    // 教師辦公室要補上跟課程成員的關係
    $majors = "SELECT `username` FROM `WM_term_major` WHERE `course_id` = {$sysSession->course_id}";
    $where .= " AND AN.`username` IN ({$majors})";
}
// 搜尋姓名或帳號
$sType = $_POST['searchkey'];
$sWord = trim($_POST['keyword']);
if ($sWord != '' && strcmp($sWord, $MSG['msg_title05'][$sysSession->lang]) != 0) {
    switch ($sType) {
        case 'real':
            if ($sysSession->lang == 'Big5' || $sysSession->lang == 'GB2312') {
                $where .= sprintf(' AND CONCAT(WU.`last_name`, WU.`first_name`) LIKE "%%%s%%"', escape_LIKE_query_str(addslashes($sWord)));
            } else {
                $where .= sprintf(' AND CONCAT(WU.`first_name`, " ", WU.`last_name`) LIKE "%%%s%%"', escape_LIKE_query_str(addslashes($sWord)));
            }
            break;
        case 'account':
            $where .= ' AND WU.`username` LIKE "%' . escape_LIKE_query_str(addslashes($sWord)) . '%" ';
            break;
    }
}

// 設定下拉換頁選單顯示第幾頁
$page_no = isset($_POST['page']) ? intval($_POST['page']) : 1;

if ($page_no > 0) {
    $limit = ' LIMIT ' . (($page_no - 1) * $lines) . ',' . $lines;
}

$table = '`APP_notification_device` as AN
          LEFT JOIN `WM_user_account` as WU ON AN.`username` = WU.`username`';
$fields = 'SQL_CALC_FOUND_ROWS AN.`username`, WU.`first_name`, WU.`last_name`';
$where .= ' GROUP BY WU.`username` ORDER BY AN.`username` ASC ' . $limit;

$RS = dbGetStMr($table, $fields, $where, ADODB_FETCH_ASSOC);

$total_msg = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));

// 計算總共分幾頁
$total_page = ceil($total_msg / $lines);

// 產生下拉換頁選單
$all_page    = range(0, $total_page);
$all_page[0] = $MSG['all'][$sysSession->lang];

// $RS = dbGetStMr('WM_user_account', 'username,first_name,last_name', 'username!="' . sysRootAccount . '" ' . $where . ' order by username asc ' . $limit, ADODB_FETCH_ASSOC);

$js = <<< BOF
var MSG_SELECT_ALL    = "{$MSG['msg_select'][$sysSession->lang]}";
var MSG_SELECT_CANCEL = "{$MSG['msg_cancel'][$sysSession->lang]}";
var PLZ_INPUT         = "{$MSG['input_keyword'][$sysSession->lang]}";
var KEY_WD            = "{$MSG['msg_title05'][$sysSession->lang]}";
var PLZ_CHECK         = "{$MSG['msg_title07'][$sysSession->lang]}";
var selTotal          = 0;

var total_page        = "{$total_page}";
var cnt               = "{$total_msg}";
function go_page(n) {
    var obj = document.getElementById("actFm");
    if ((typeof(obj) != "object") || (obj == null)) return '';
    switch (n) {
        case -1: // 第一頁
            obj.page.value = 1;
            break;
        case -2: // 前一頁
            obj.page.value = parseInt(obj.page.value) - 1;
            if (parseInt(obj.page.value) == 0) obj.page.value = 1;
            break;
        case -3: // 後一頁
            obj.page.value = parseInt(obj.page.value) + 1;
            break;
        case -4: // 最末頁
            obj.page.value = parseInt(total_page);
            break;
        default: // 指定某頁
            obj.page.value = parseInt(n);
            break;
    }
    obj.submit();
}

function ReturnWork() {
    var obj = document.getElementsByTagName('input');

    if (obj == null) return false;

    var i = 0;
    var total_len = obj.length;
    var temp = '';
    var user_ids = '';
    var user_names = '';
    var j = 0;

    for (i = 0; i < total_len; i++) {
        if ((obj[i].type == 'checkbox') && obj[i].checked) {
            if (obj[i].value.length > 0) {
                j++;
                temp = obj[i].value.split('***', 2);
                user_ids += temp[0] + ',';
                user_names += temp[1] + ',';
            }
        }
    }
    if (j > 0) {
        user_ids = user_ids.substring(0, user_ids.length - 1);

        user_names = user_names.substring(0, user_names.length - 1);

        var hwnd = opener.getHwnd("WinAPPPushUserSelect");
        
        if (hwnd != null) {
            var rtnArray = new Array(user_ids, user_names);
            hwnd.callback(rtnArray);
        }

        window.close();
    } else {
        alert(PLZ_CHECK);
    }
}

function selUser(sObj) {
    var nodes = null,
        attr = null,
        ary = new Array('');
    if (typeof(sObj) != 'object') {
        var sel = sObj;
    } else {
        var sel = sObj.options[sObj.selectedIndex].value;
    }
    var obj = document.getElementById('Instr_List');
    nodes = obj.getElementsByTagName('input');
    for (var i = 0, m = 0; i < nodes.length; i++) {
        attr = nodes[i].getAttribute("exclude");
        if ((nodes[i].type == "checkbox") && (attr == null)) {
            m++;
            ary = nodes[i].value.split('***', 2);
            /*alert('val=>'+ary[0]+' / sel=>'+sel);*/
            if (sel === true && !nodes[i].checked) {
                nodes[i].checked = true;
                if (typeof(nodes[i].parentNode.parentNode) == 'object') {
                    selTotal++;
                    nodes[i].parentNode.parentNode.className = 'cssTrSel';
                }
            } else if (sel === false && nodes[i].checked) {
                nodes[i].checked = false;
                if (typeof(nodes[i].parentNode.parentNode) == 'object') {
                    selTotal--;
                    nodes[i].parentNode.parentNode.className = (m % 2 == 0 ? 'cssTrOdd' : 'cssTrEvn');
                }
            } else if (ary[0] == sel) {
                if (typeof(sObj) == 'object') {
                    nodes[i].checked = !(nodes[i].checked);
                }
                if (typeof(nodes[i].parentNode.parentNode) == 'object') {
                    if (nodes[i].checked) {
                        selTotal++;
                        nodes[i].parentNode.parentNode.className = 'cssTrSel';
                    } else {
                        selTotal--;
                        nodes[i].parentNode.parentNode.className = (m % 2 == 0 ? 'cssTrOdd' : 'cssTrEvn');
                    }
                }
            }
        }
    }
    document.getElementById("ckbox").checked = (m == selTotal);
    if (m == selTotal) {
        obj.title = MSG_SELECT_CANCEL;
        nowSel = true;
    } else {
        obj.title = MSG_SELECT_ALL;
        nowSel = false;
    }
}

function chkForm(fm) {
    if (typeof(fm) == 'object') {
        var kwd = fm.keyword.value;
        if (kwd == '' || kwd == KEY_WD) {
            alert(PLZ_INPUT);
            fm.keyword.focus();
            return false;
        }
        return true;
    }
    return false;
}

window.onload = function() {
    if (cnt > 0) {
        var obj = document.getElementById('Instr_List');
        if (typeof(obj) == 'object')
            obj.rows[(obj.rows.length - 1)].cells[0].innerHTML = obj.rows[2].cells[0].innerHTML;
    }
};

BOF;
// 開始呈現 HTML
showXHTML_head_B($MSG['msg_title06'][$sysSession->lang]);
showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
echo "<style>
        .cssTrSel {
        font-size: 12px;
        line-height: 16px;
        text-decoration: none;
        letter-spacing: 2px;
        color: #000000;
        background-color: #F0EBF0;
        font-family: \"Tahoma\", \"PMingliu\", \"MingLiU\", \"Times New Roman\", \"Times\", \"serif\";
        }
    </style>";
showXHTML_script('inline', $js, false);
showXHTML_head_E();
showXHTML_body_B();
echo "<center>\n";
showXHTML_table_B('width="450" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
showXHTML_tr_B();
showXHTML_td_B();
$ary[] = array(
    $MSG['msg_title06'][$sysSession->lang],
    'tabs'
);
showXHTML_tabs($ary, 1);
showXHTML_td_E();
showXHTML_tr_E();
showXHTML_tr_B();
showXHTML_td_B('valign="top"');

showXHTML_form_B('style="display:inline;" action="' . $_SERVER['PHP_SELF'] . '" method="post" onSubmit="return chkForm(this);"', 'actFm');
showXHTML_input('hidden', 'page', $page_no, '', '');
showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="Instr_List" class="cssTable"');

showXHTML_tr_B('class="cssTrEvn"');
showXHTML_td('colspan="3"', $MSG['msg_title07'][$sysSession->lang]);
showXHTML_tr_E('');

// 查詢搜尋
showXHTML_tr_B('class="cssTrOdd"');
showXHTML_td_B('colspan="3"');
echo $MSG['msg_title02'][$sysSession->lang], $MSG['msg_title01'][$sysSession->lang];
$sAry = Array(
    'real' => $MSG['name'][$sysSession->lang],
    'account' => $MSG['account'][$sysSession->lang]
);
showXHTML_input('select', 'searchkey', $sAry, $_POST['searchkey'], 'id="searchkey" class="cssInput"');
showXHTML_input('text', 'keyword', ($sWord != '' ? $sWord : $MSG['msg_title05'][$sysSession->lang]), '', 'id="keyword" size="20" maxlength="30" class="cssInput" onclick="this.value=\'\'"');
showXHTML_input('submit', '', $MSG['btn_query'][$sysSession->lang], '', 'style="24" class="cssBtn"');
showXHTML_td_E('');
showXHTML_tr_E('');

if ($RS->RecordCount() > 0) {
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td_B('colspan="3"');
    echo $MSG['page'][$sysSession->lang];
    showXHTML_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);"');
    showXHTML_input('button', 'fp', $MSG['btn_page_first'][$sysSession->lang], '', 'onclick="go_page(-1)" class="cssBtn"' . (($page_no == 1) || ($page_no == 0) ? ' disabled="disabled"' : ''));
    showXHTML_input('button', 'pp', $MSG['btn_page_prev'][$sysSession->lang], '', 'onclick="go_page(-2)" class="cssBtn"' . (($page_no == 1) || ($page_no == 0) ? ' disabled="disabled"' : ''));
    showXHTML_input('button', 'np', $MSG['btn_page_next'][$sysSession->lang], '', 'onclick="go_page(-3)" class="cssBtn"' . (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
    showXHTML_input('button', 'lp', $MSG['btn_page_last'][$sysSession->lang], '', 'onclick="go_page(-4)" class="cssBtn"' . (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
    
    // 確定 & 關閉視窗
    echo '&nbsp;&nbsp;';
    showXHTML_input('button', '', $MSG['btn_confirm'][$sysSession->lang], '', 'class="cssBtn" onclick="ReturnWork();"');
    showXHTML_input('button', '', $MSG['btn_close'][$sysSession->lang], '', 'class="cssBtn" onclick="window.close();"');
    showXHTML_td_E();
    showXHTML_tr_E();
}

// html 標題
showXHTML_tr_B('class="font01 cssTrHead"');
showXHTML_td_B('width="20"');
showXHTML_input('checkbox', '', '', '', 'id="ckbox" onclick="selUser(this.checked);" exclude="true" title="' . $MSG['msg_select'][$sysSession->lang] . '"');
showXHTML_td_E('');
showXHTML_td('noWrap="noWrap" align="center"', $MSG['account'][$sysSession->lang]);
showXHTML_td('noWrap="noWrap" align="center"', $MSG['name'][$sysSession->lang]);
showXHTML_tr_E();

// 產生資料
if ($RS) {
    if ($RS->RecordCount() == 0) {
        $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
        showXHTML_td_B('class="font01" colspan="3"');
        echo $MSG['no_data'][$sysSession->lang];
        showXHTML_td_E();
        showXHTML_tr_E();
    } else {
        while ($RS1 = $RS->FetchRow()) {
            
            $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($col);
            // checkbox
            showXHTML_td_B('class="font01" ');
            // 帳號 & 姓名
            $realname = checkRealname($RS1['first_name'], $RS1['last_name']);
            showXHTML_input('checkbox', 'in_users[]', $RS1['username'] . '***' . htmlspecialchars($realname), '', ' onclick="selUser(\'' . $RS1['username'] . '\');"');
            showXHTML_td_E();
            
            // 帳號
            showXHTML_td_B('class="font01" nowrap');
            echo '<div style="width: 200px;" title="' . $RS1['username'] . '">' . $RS1['username'] . '</div>';
            showXHTML_td_E();
            
            // 姓名
            showXHTML_td_B('class="font01" nowrap');
            echo '<div style="width: 200px;" title="' . htmlspecialchars($realname) . '">' . $realname . '</div>';
            showXHTML_td_E();
            showXHTML_tr_E();
        }
        $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
        showXHTML_td('colspan="3"', '&nbsp;');
        showXHTML_tr_E();
    }
}

showXHTML_table_E();

showXHTML_form_E();
showXHTML_td_E();
showXHTML_tr_E();
showXHTML_table_E();
echo "</center>\n";

showXHTML_body_E();
