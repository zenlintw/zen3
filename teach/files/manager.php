<?php
/**************************************************************************************************
 *                                                                                                *
 *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
 *                                                                                                *
 *        Programmer: Wiseguy Liang                                                         *
 *        Creation  : 2003/02/19                                                            *
 *        work for  :                                                                       *
 *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
 *                                                                                                *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/editor.php');
require_once(sysDocumentRoot . '/lang/files_manager.php');
require_once(sysDocumentRoot . '/lib/archive_api.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
require_once(sysDocumentRoot . '/lib/quota.php');
require_once(sysDocumentRoot . '/lib/file_api.php');
require_once(sysDocumentRoot . '/lib/lib_encrypt.php');

$sysSession->cur_func = '1200100100';
$sysSession->restore();
if (!aclVerifyPermission(1200100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

$tabs = isset($_POST['tabs']) ? $_POST['tabs'] : ($_GET['tabs'] ? $_GET['tabs'] : 1);
list($foo, $wmTopDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF']);

/**
 *    變數說明：
 *        $basePath : 系統實體路徑，指向目前課程的 content 目錄
 *        $currPath : 檔案總管路徑，USER 可存取的目錄
 *        $fullPath : $basePath . $currPath
 *        $withPath : 目前目錄的 URI (即 fullPath 去掉前面的 $DOCUMENT_ROOT)
 *        $entry    : $entry[0] 為 $currPath 目錄下的子目錄
 *              : $entry[1] 為 $currPath 目錄下的檔案
 *              : 使用前至少要呼叫一次 getAllEntry() 來建立本陣列
 *        $resultMsg      : 顯示在「檔案總管Ⅲ」下方的操作訊息
 *
 *        $_POST['op']        : 動作代碼
 *        $_POST['currPath']    : $currPath 的 rawurlencode()
 *        $_POST['target']    : 要處理的檔案/目錄編號
 *        $_POST['newdir']    : 新建目錄名稱
 *        $_POST['newfile']    : 新建檔案名稱
 *        $_POST['upload']    : 上傳檔案 object
 */

$urlchars = array(
    '%' => '%2525',
    ' ' => '%20',
    '"' => '%22',
    '&' => '%26',
    "'" => '%27',
    '+' => '%2B',
    '=' => '%3D',
    '?' => '%3F',
    '%2F' => '/'
);

$arc = new Archive();

if (strpos($_SERVER['REQUEST_URI'], '?tabs=') !== FALSE)
    $_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];

if (!function_exists('mime_content_type')) {
    function mime_content_type($x)
    {
        return 'text/plain';
    }
}

/**
 *
 */
function displayQuota()
{
    global $MSG, $sysSession;

    $real_used   = format_size($GLOBALS['real_used']);
    $quota_limit = format_size($GLOBALS['quota_limit']);
    if ($sysSession->lang != 'en') {
        if ($GLOBALS['real_used'] < $GLOBALS['quota_limit'])
            return "{$MSG['current_used'][$sysSession->lang]} {$real_used} ({$MSG['quota_limit'][$sysSession->lang]} {$quota_limit})";
        else
            return "<span style='color: red'>{$MSG['current_used'][$sysSession->lang]} {$real_used} ({$MSG['quota_limit'][$sysSession->lang]} {$quota_limit})</span>";
    } else {
        if ($GLOBALS['real_used'] < $GLOBALS['quota_limit'])
            return "{$quota_limit} {$MSG['quota_limit'][$sysSession->lang]} {$MSG['current_used'][$sysSession->lang]} {$real_used}";
        else
            return "<span style='color: red'> {$quota_limit} {$MSG['quota_limit'][$sysSession->lang]} {$MSG['current_used'][$sysSession->lang]} {$real_used}</span>";
    }
}

/**
 *
 */
function escapeSingleQuote($str)
{
    static $pattern = array('\\' => '\\\\', "'" => "\\'");
    return strtr($str, $pattern);
}

/**
 *
 */
function deltree($path)
{
    if (substr($path, -1) != DIRECTORY_SEPARATOR)
        $path .= DIRECTORY_SEPARATOR;
    if ($dp = @dir($path)) {
        while ($entry = $dp->read()) {
            if ($entry == '.' || $entry == '..')
                continue;
            elseif (wm_is_file($path . $entry))
                @unlink($path . $entry);
            elseif (wm_is_dir($path . $entry))
                deltree($path . $entry);
        }
        $dp->close();
        @rmdir($path);
    }
}

/**
 *
 */
function getTicket()
{
    $argvs = func_get_args();
    return md5(sysTicketSeed . implode('', $argvs) . sysTicketSeed);
}

/**
 * 取得所有目錄，並產生樹狀結構
 */
function getAllDir($path)
{
    global $allDir, $sysSession, $basePath;

    $first = true;
    if ($dir = @opendir($path)) {
        while (($file = readdir($dir)) !== false) {
            if (strpos($file, '.') === 0)
                continue;
            $fullfile = $path . $file . '/';
            if (wm_is_dir($fullfile)) {
                if ($first) {
                    $allDir .= '<ul>';
                    $first = false;
                }
                $word    = adjust_char(substr($fullfile, strlen($basePath)));
                $dirname = $word;
                $allDir .= '<li><img src="/theme/' . $sysSession->theme . '/teach/minus.gif" align="absmiddle"><a href="javascript:to(\'' . escapeSingleQuote($dirname) . '\')" class="link_fnt01">' . basename($dirname) . "</a></li>\n";
                getAllDir($fullfile);
            }
        }
        closedir($dir);
        if (!$first) {
            $allDir .= "</ul>\n";
        }
    }
}


/**
 * 取得現行目錄下所有目錄、檔案
 */
function getAllEntry()
{
    global $basePath, $currPath, $fullPath, $entry, $sysSession;
    $entry = array(
        array(),
        array()
    );
    clearstatcache();

    if ($dir = @opendir($fullPath)) {
        while (($file = readdir($dir)) !== false) {
            $fullfile = $fullPath . $file;
            if (wm_is_dir($fullfile) && strpos($file, '.') !== 0) {
                $entry[0][] = $file;
            } elseif (wm_is_file($fullfile)) {
                $entry[1][] = $file;
            }
        }
        closedir($dir);
    }
    sort($entry[0]);
    sort($entry[1]);
}

/**
 * 顯示操作畫面
 */
function display_currpath($msgs)
{
    global $currPath, $ticket, $MSG, $sysSession, $arc, $tabs;
    showXHTML_head_B($MSG['fm_caption'][$sysSession->lang]);
        showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$GLOBALS['wmTopDir']}/wm.css");
        $scr = <<< EOB

var MSG_SELECT_ALL = "{$MSG['select_all'][$sysSession->lang]}";
var MSG_CANCEL_ALL = "{$MSG['unselect_all'][$sysSession->lang]}";
var exec_env = "{$sysSession->env}";

var obj = window.scrollbars;
if (typeof(obj) == "object") obj.visible = true;

function chdir(n) {
    var obj = document.getElementById('mainform');
    obj.target.value = n;
    obj.op.value = 'cd';
    obj.submit();
}

/**
 * 切換全選或全消的 checkbox
 **/
var nowSel = false;

function selFile() {
    var bol = true;
    var nodes = document.getElementsByTagName("input");
    var obj = document.getElementById("ckbox");
    var btn1 = document.getElementById("btnSel1");
    if ((nodes == null) || (nodes.length <= 0)) return false;
    for (var i = 0; i < nodes.length; i++) {
        if ((nodes[i].type != "checkbox") || (nodes[i].id == "ckbox")) continue;

        if (nodes[i].name.indexOf('_entry[]') != -1) {
            if (nodes[i].checked == false) {
                bol = false;
                break;
            }
        }
    }
    nowSel = bol;

    if (obj != null) obj.checked = bol;

    xchg_words(nowSel);
}

function selectAll(mode) {
    var nodes = document.getElementById('tab01').getElementsByTagName('input');
    for (var i = 0; i < nodes.length; i++)
        if (nodes[i].type == 'checkbox') nodes[i].checked = mode;
    nowSel = mode;
    xchg_words(nowSel);
}

function selectAll2(button) {
    nowSel = !nowSel;
    xchg_words(nowSel);
    selectAll(nowSel);
}

function xchg_words(mode) {
    if (mode) {
        document.getElementById('toolbar1').getElementsByTagName('input')[0].value =
            document.getElementById('toolbar2').getElementsByTagName('input')[0].value = '{$MSG['unselect_all'][$sysSession->lang]}';
    } else {
        document.getElementById('toolbar1').getElementsByTagName('input')[0].value =
            document.getElementById('toolbar2').getElementsByTagName('input')[0].value = '{$MSG['select_all'][$sysSession->lang]}';
    }
}

function checkname(name) {
    if (name == '') return false;

    if (name.search(/[\\\/:\*\?"<>\|]/g) > -1) {
        alert('{$MSG['include_illegal_char'][$sysSession->lang]}');
        return false;
    }

    return true;
}

function rename(n) {
    var newname = prompt('{$MSG['newfilename'][$sysSession->lang]}', '');
    if (newname != null && checkname(newname)) {
        var obj = document.getElementById('mainform');
        obj.target.value = n;
        obj.newname.value = newname;
        obj.op.value = 'rn';
        obj.submit();
    }
}

function edit(n) {
    var obj = document.getElementById('mainform');
    obj.target.value = n;
    obj.op.value = 'ed';
    obj.submit();
}

function copy(op) {
    var obj = document.getElementById('mainform');
    var nodes = obj.getElementsByTagName('input');
    var list = '';
    var msg = (op == "mv") ? "{$MSG['errormsg2'][$sysSession->lang]}" : "{$MSG['errormsg1'][$sysSession->lang]}";
    var alt_msg = (op == "mv") ? "{$MSG['pick_item_move'][$sysSession->lang]}" : "{$MSG['pick_item_copy'][$sysSession->lang]}";

    for (var i = 0; i < nodes.length; i++) {
        if (nodes[i].type == 'checkbox') {
            if (nodes[i].name.indexOf('dir_') == 0 && nodes[i].checked) {
                alert(msg);
                nodes[i].checked = false;
            } else if (nodes[i].name.indexOf('file_') == 0 && nodes[i].checked) {
                list += (nodes[i].value + ',');
            }
        }
    }
    obj.target.value = list.replace(/,$/, '');
    if (obj.target.value == '') {
        alert(alt_msg);
        return;
    }
    obj.op.value = op;
    obj.submit();
}

// #47147 檔案總管3刪除功能失效
function removeFile() {
    var obj = document.getElementById('mainform');
    var nodes = obj.getElementsByTagName('input');
    var list = '';

    for (var i = 0; i < nodes.length; i++) {
        if (nodes[i].type == 'checkbox') {
            if (nodes[i].name.indexOf('dir_') == 0 && nodes[i].checked) {
                list += (nodes[i].value + ',');
            } else if (nodes[i].name.indexOf('file_') == 0 && nodes[i].checked) {
                list += ('-' + nodes[i].value + ',');
            }
        }
    }
    obj.target.value = list.replace(/,$/, '');
    if (obj.target.value == '') {
        alert('{$MSG['pick_item'][$sysSession->lang]}');
        return;
    }
    if (!confirm('{$MSG['are_you_sure'][$sysSession->lang]}')) return;
    obj.op.value = 'rm';
    obj.submit();
}

function disableAll() {
    var obj = document.getElementById('mainform');
    var nodes = obj.getElementsByTagName('input');
    var c = 0;
    switch (obj.op.value) {
        case 'ul':
            for (var i = 0; i < nodes.length; i++) {
                if (nodes[i].type == 'file' && nodes[i].name == 'upload[]' && nodes[i].value != '') c++;
            }
            if (c == 0) {
                alert('{$MSG['pick_file_first'][$sysSession->lang]}');
                return false;
            }
            break;
        case 'uz':
            if (obj.uploadz.value == '') {
                alert('{$MSG['pick_file_first'][$sysSession->lang]}');
                return false;
            }
            break;
    }

    var nodes = document.getElementsByTagName('a');
    for (var i = 0; i < nodes.length; i++) nodes[i].onmousedown = new function() {
        return false;
    };
    nodes = document.getElementsByTagName('input');
    for (var i = 0; i < nodes.length; i++)
        if (nodes[i].type == 'button' || nodes[i].type == 'submit') nodes[i].disabled = true;
}

function addfile(olObj) {
    var newNode, nodes = olObj.getElementsByTagName('tr');
    newNode = nodes[nodes.length - 2].cloneNode(true);
    var object = newNode.getElementsByTagName('input');
    for (var i = 0; i < object.length; i++) {
        if (object[i].type == 'file')
        {
            object[i].value = '';
            object[i].outerHTML = object[i].outerHTML;
        }
    }
    newNode = olObj.insertBefore(newNode, nodes[nodes.length - 1]);
}

function rmfile(obj) {
    var nodes = obj.parentNode.getElementsByTagName('input');
    var c = 0;
    for (var i = 0; i < nodes.length; i++) {
        if (nodes[i].type == 'file') c++;
    }
    if (c > 1) obj.parentNode.removeChild(obj);
    else {
        $("#fileUploadTraditional input[type='file']").remove();
        $('#fileUploadTraditional .btnRmfile').before('<input type="file" name="upload[]" id="upload[]" size="50" class="cssInput" />');
    }
}

function OpenFile(fileUrl) {
    window.top.opener.SetUrl(fileUrl);
    window.top.close();
    window.top.opener.focus();
}

function chgTab(val) {
    if ((val == 1) && jfileUploaded) {
        isReloadPage();
        return;
    }
    document.getElementById('tabs').value = val;
    document.getElementById('tab0' + val).rows[0].style.display = 'none';
    document.cookie = 'fileManager=' + val + ';';
}

function baseName(str)
{
   var base = new String(str).substring(str.lastIndexOf('\\\\') + 1);
   if(base.lastIndexOf(".") != -1){
        base = base.substring(0, base.lastIndexOf("."));
   }
   if (base.length == 0) {
      base = new String(str).substring(str.lastIndexOf('/') + 1);
      if(base.lastIndexOf(".") != -1){
        base = base.substring(0, base.lastIndexOf("."));
      }
   }
   return base;
}

function validUploadFilename() {
    var elms = document.getElementsByName('upload[]');
    var patt = new RegExp('[\\\\/:*?"<>|]');

    for(var i=0; i<elms.length; i++) {
        res = patt.test(baseName(elms[i].value));
        if (res) {
            alert('{$MSG['include_illegal_char'][$sysSession->lang]}');
            elms[i].focus();
            return false;
        }
    }

    document.getElementById('mainform').op.value='ul';
    document.getElementById('mainform').submit();
}

var jfileUploaded = false;

function isReloadPage() {
    if (jfileUploaded) {
        document.mainform.op.value = '';
        document.mainform.tabs.value = 1;
        document.mainform.submit();
    }
}

window.onload = function() {
    document.getElementById('toolbar2').innerHTML = document.getElementById('toolbar1').innerHTML;
    document.body.scroll = 'yes';
    if ({$GLOBALS['real_used']} >= {$GLOBALS['quota_limit']})
        alert("{$GLOBALS['msgQuota']}");
    if ((exec_env == 'teach') && window.File && window.FileReader && window.FileList && window.Blob) {
        document.getElementById('fileUploadTraditional').style.display = 'none';
        document.getElementById('fileUploadHtml5').style.display = 'block';
    }
}
EOB;

        showXHTML_script('include', '/lib/jquery/jquery.min.js');
        showXHTML_script('inline', $scr);
    showXHTML_head_E();

    showXHTML_body_B('');
        showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse"');
        showXHTML_tr_B();
        showXHTML_td_B();
        $ary = array(
            array(
                $MSG['fm_caption'][$sysSession->lang],
                'tab01',
                'chgTab(1);'
            ),
            array(
                $MSG['create_dir'][$sysSession->lang],
                'tab02',
                'chgTab(2);'
            ),
            array(
                $MSG['create_file'][$sysSession->lang],
                'tab03',
                'chgTab(3);'
            ),
            array(
                $MSG['upload_file'][$sysSession->lang],
                'tab04',
                'chgTab(4);'
            ),
            array(
                $MSG['upload_archive'][$sysSession->lang],
                'tab05',
                'chgTab(5);'
            )
        );
        showXHTML_tabs($ary, $tabs);
        showXHTML_td_E();
        showXHTML_tr_E();
        showXHTML_tr_B();
        showXHTML_td_B('valign="top" class="bg01"');
        showXHTML_form_B('action="' . $_SERVER['REQUEST_URI'] . '" method="POST" enctype="multipart/form-data" onsubmit="return disableAll();" style="display:inline"', 'mainform');
        showXHTML_input('hidden', 'tabs', $tabs, '', 'id="tabs"');
        $currPathx = rawurlencode($currPath);
        showXHTML_input('hidden', 'ticket', $ticket);
        showXHTML_input('hidden', 'op', 'md');
        showXHTML_input('hidden', 'target', '');
        showXHTML_input('hidden', 'newname', '');
        showXHTML_input('hidden', 'currPath', $currPathx);

        showXHTML_table_B('id="tab01" width="760" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse;display: ' . ($tabs == 1 ? '' : 'none') . ';" class="cssTable"');
        showXHTML_tr_B('class="cssTrEvn font01"');
        showXHTML_td('colspan="6" style="font-weight: bold; color: red"', $msgs);
        showXHTML_tr_E();

        showXHTML_tr_B('class="cssTrOdd font01"');
        showXHTML_td('colspan="6"', $MSG['cur_path'][$sysSession->lang] . ' ' . adjust_char($currPath));
        showXHTML_tr_E();

        showXHTML_tr_B('class="cssTrEvn font01"');
        showXHTML_td_B('colspan="3" id="toolbar1"');
        showXHTML_input('button', '', $MSG['select_all'][$sysSession->lang], '', 'class="cssBtn" id="btnSel1" onclick="selectAll2(this);"');
        echo '&nbsp;&nbsp;';
        showXHTML_input('button', '', $MSG['copy'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['real_used'] < $GLOBALS['quota_limit'] ? 'onclick="copy(\'cp\');"' : 'disabled'));
        showXHTML_input('button', '', $MSG['move'][$sysSession->lang], '', 'class="cssBtn" onclick="copy(\'mv\');"');
        showXHTML_input('button', '', $MSG['remove'][$sysSession->lang], '', 'class="cssBtn" onclick="removeFile();"');
        showXHTML_td_E();
        showXHTML_td('colspan="3" align="right"', displayQuota());
        showXHTML_tr_E();

        showXHTML_tr_B('class="cssTrHead font01"');
        showXHTML_td('align="center" width="20"  nowrap', '<input type="checkbox" id="ckbox" onclick="selectAll(this.checked);">');
        showXHTML_td('align="center" width="40"  nowrap', $MSG['type'][$sysSession->lang]);
        showXHTML_td('align="left"   width="390" nowrap', $MSG['filename'][$sysSession->lang]);
        showXHTML_td('align="center" width="80"  nowrap', $MSG['filesize'][$sysSession->lang]);
        showXHTML_td('align="center" width="160" nowrap', $MSG['filetime'][$sysSession->lang]);
        showXHTML_td('align="left"   width="70"  nowrap', $MSG['single_operating'][$sysSession->lang]);
        showXHTML_tr_E();

        display_entry();

        showXHTML_tr_B('class="cssTrEvn font01"');
        showXHTML_td('colspan="6" id="toolbar2"');
        showXHTML_tr_E();
        showXHTML_table_E();

        showXHTML_table_B('id="tab02" width="760" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse; display: ' . ($tabs == 2 ? '' : 'none') . '" class="cssTable"');
        showXHTML_tr_B('class="cssTrEvn font01"');
        showXHTML_td('style="font-weight: bold; color: red"', $msgs);
        showXHTML_tr_E();

        showXHTML_tr_B('class="cssTrOdd font01"');
        showXHTML_td('', $MSG['cur_path'][$sysSession->lang] . ' ' . adjust_char($currPath));
        showXHTML_tr_E();

        showXHTML_tr_B('class="cssTrEvn font01"');
        showXHTML_td('align="right"', displayQuota());
        showXHTML_tr_E();

        showXHTML_tr_B('class="cssTrOdd font01"');
        showXHTML_td_B();
        echo $MSG['create_dir'][$sysSession->lang];
        showXHTML_input('text', 'newdir', '', '', 'size="40" class="cssInput"');
        showXHTML_input('submit', '', $MSG['sure_create'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['real_used'] < $GLOBALS['quota_limit'] ? 'onclick="return checkname(this.form.newdir.value);"' : 'disabled'));
        showXHTML_td_E();
        showXHTML_tr_E();
        showXHTML_table_E();

        showXHTML_table_B('id="tab03" width="760" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse; display: ' . ($tabs == 3 ? '' : 'none') . '" class="cssTable"');
        showXHTML_tr_B('class="cssTrEvn font01"');
        showXHTML_td('style="font-weight: bold; color: red"', $msgs);
        showXHTML_tr_E();

        showXHTML_tr_B('class="cssTrOdd font01"');
        showXHTML_td('', $MSG['cur_path'][$sysSession->lang] . ' ' . adjust_char($currPath));
        showXHTML_tr_E();

        showXHTML_tr_B('class="cssTrEvn font01"');
        showXHTML_td('align="right"', displayQuota());
        showXHTML_tr_E();

        showXHTML_tr_B('class="cssTrOdd font01"');
        showXHTML_td_B();
        echo $MSG['create_file'][$sysSession->lang];
        showXHTML_input('text', 'newfile', '', '', 'size="40" class="cssInput"');
        showXHTML_input('checkbox', 'html', 1, '', 'class="cssInput"');
        echo 'HTML template';
        showXHTML_input('submit', '', $MSG['sure_create'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['real_used'] < $GLOBALS['quota_limit'] ? 'onclick="document.getElementById(\'mainform\').op.value=\'nf\'; return checkname(this.form.newfile.value);"' : 'disabled'));
        showXHTML_td_E();
        showXHTML_tr_E();
        showXHTML_table_E();



        showXHTML_table_B('id="tab04" width="760" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse; display: ' . ($tabs == 4 ? '' : 'none') . '" class="cssTable"');

        showXHTML_tr_B('class="cssTrEvn font01"');
        showXHTML_td('style="font-weight: bold; color: red"', $msgs);
        showXHTML_tr_E();

        showXHTML_tr_B('class="cssTrOdd font01"');
        showXHTML_td('', $MSG['cur_path'][$sysSession->lang] . ' ' . adjust_char($currPath));
        showXHTML_tr_E();

        showXHTML_tr_B('class="cssTrEvn font01" id="total-filesize"');
        showXHTML_td('align="right"', displayQuota());
        showXHTML_tr_E();

        echo '<tr class="cssTrOdd"><td><div id="fileUploadTraditional" style="display:block">';
        showXHTML_table_B('width="100%"');
        showXHTML_tr_B('class="font01 cssTrHead"');
        showXHTML_td('nowrap', sprintf('%s<span style="color: red; font-weight: bold">%s</span>%s<span style="color: red; font-weight: bold">%s</span>', $MSG['max_upload_filesize'][$sysSession->lang], ini_get('upload_max_filesize'), $MSG['max_upload_totalsize'][$sysSession->lang], ini_get('post_max_size')));
        showXHTML_tr_E();
        showXHTML_tr_B('class="cssTrEvn"');
        showXHTML_td('', $MSG['upload_hint'][$sysSession->lang]);
        showXHTML_tr_E();
        showXHTML_tr_B('class="cssTrOdd"');
        showXHTML_td_B();
        echo $MSG['upload_file'][$sysSession->lang];
        showXHTML_input('file', 'upload[]', '', '', 'size="50" class="cssInput"');
        showXHTML_input('button', '', $MSG['cede_file'][$sysSession->lang], '', 'class="cssBtn btnRmfile" ' . ($GLOBALS['real_used'] < $GLOBALS['quota_limit'] ? 'onclick="rmfile(this.parentNode.parentNode);"' : 'disabled'));
        showXHTML_td_E();
        showXHTML_tr_E();
        showXHTML_tr_B('class="cssTrEvn"');
        showXHTML_td_B();
        showXHTML_input('button', '', $MSG['more_file'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['real_used'] < $GLOBALS['quota_limit'] ? 'onclick="addfile(this.parentNode.parentNode.parentNode);"' : 'disabled'));
        showXHTML_input('button', '', $MSG['upload'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['real_used'] < $GLOBALS['quota_limit'] ? 'onclick="validUploadFilename();"':'disabled'));
        showXHTML_td_E();
        showXHTML_tr_E();
        showXHTML_table_E();
        echo '</div></td></tr>';

        echo '<tr class="cssTrOdd"><td><div id="fileUploadHtml5" style="display:none;"><h2 class="lead font01" style="margin-bottom: 0.5em;">' . $MSG['multi_file_upload_interface'][$sysSession->lang] . '</h2>';
        echo '<iframe id="iframeFileUpload" src="/teach/files/basic.php?currPath=' . $currPathx . '" width="750" height="400"></iframe>';
        echo '</div></td></tr>';

        showXHTML_table_E();
        showXHTML_table_B('id="tab05" width="760" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse; display: ' . ($tabs == 5 ? '' : 'none') . '" class="cssTable"');
        showXHTML_tr_B('class="cssTrEvn font01"');
        showXHTML_td('style="font-weight: bold; color: red"', $msgs);
        showXHTML_tr_E();

        showXHTML_tr_B('class="cssTrOdd font01"');
        showXHTML_td('', $MSG['cur_path'][$sysSession->lang] . ' ' . adjust_char($currPath));
        showXHTML_tr_E();

        showXHTML_tr_B('class="cssTrEvn font01"');
        showXHTML_td('align="right"', displayQuota());
        showXHTML_tr_E();

        showXHTML_tr_B('class="font01 cssTrHead"');
        /*Chrome*/
        showXHTML_td('nowrap', sprintf('%s<span style="color: red; font-weight: bold">%s</span>%s<span style="color: red; font-weight: bold">%s</span><br><span style="color: white; font-weight: bold;">%s</span>', $MSG['max_upload_filesize'][$sysSession->lang], ini_get('upload_max_filesize'), $MSG['max_upload_totalsize'][$sysSession->lang], ini_get('post_max_size'), $MSG['upload_trans_type'][$sysSession->lang]));
        showXHTML_tr_E();
        showXHTML_tr_B('class="cssTrEvn"');
        showXHTML_td('', $MSG['upload_arc_hint'][$sysSession->lang]);
        showXHTML_tr_E();
        showXHTML_tr_B('class="cssTrOdd"');
        showXHTML_td_B();
        echo $MSG['upload_archive'][$sysSession->lang];
        showXHTML_input('file', 'uploadz', '', '', 'size="50" class="cssInput"');
        showXHTML_input('submit', '', $MSG['upload_archive1'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['real_used'] < $GLOBALS['quota_limit'] ? 'onclick="document.getElementById(\'mainform\').op.value=\'uz\';"' : 'disabled'));
        showXHTML_td_E();
        showXHTML_tr_E();
        showXHTML_tr_B('class="cssTrEvn"');
        $t = '';
        foreach ($arc->arc_kinds as $kind => $m) {
            $col = $col == 'cssTrOdd' ? 'cssTrEvn' : 'cssTrOdd';
            $t .= sprintf("  <tr class=%s>
        <td>%s</td>
        <td>%s</td>
        <td align=center>%s</td>
      </tr>
    ", $col, $kind, $m[1], ($m[0] ? 'O' : 'X'));
        }
        showXHTML_td('', "
    <table border=1 cellspacing=0 cellpadding=3 style=\"border-collapse: collapse; display: none\" class=cssTable id=arcSuppList>
      <tr class=cssTrHead>
        <td>" . $MSG['support_kinds'][$sysSession->lang] . "</td>
        <td>" . $MSG['required_software'][$sysSession->lang] . "</td>
        <td align=center>" . $MSG['support'][$sysSession->lang] . "</td>
      </tr>
    " . $t . '</table>');
        showXHTML_tr_E();
        showXHTML_table_E();

        showXHTML_form_E();
        showXHTML_td_E();
        showXHTML_tr_E();
        showXHTML_table_E();
    showXHTML_body_E();
}

function getFileType($fname)
{
    $ext = @strtolower(strrchr($fname, '.'));
    switch ($ext) {
        case '.avi':
        case '.bmp':
        case '.doc':
        case '.docx':
        case '.gif':
        case '.htm':
        case '.html':
        case '.jpg':
        case '.mht':
        case '.mp3':
        case '.pdf':
        case '.ppt':
        case '.pptx':
        case '.swf':
        case '.txt':
        case '.wav':
        case '.xls':
        case '.xlsx':
        case '.zip':
        case '.png':
        case '.rar':
            return substr($ext, 1);
        default:
            return 'default';
    }
}

function isHTML($filename)
{
    return preg_match('/\.(htm|html)$/i', $filename);
}

function isText($filename)
{
    return preg_match('/\.(txt|htm|html|css|js|bat|c|cpp|h|ini|bas|pas|asm|sql|vbs|java|php|sh)$/i', $filename);
}

/**
 * 取代不合法字元
 * @param string $word : 要檢查的字串
 * @param string $r : 要代換的字串
 * @return string : 取代後的字串
 **/
function replaceIllegalChar($word, $r = '_')
{
    return preg_replace('/[\\/:\*\?"<>\|]+/i', $r, $word);
}

/**
 * 檢查路徑 (系統路徑)
 * @param string $base : root path
 * @param string $curr : 要檢查的路徑
 * @return boolean : true : 合法，false : 不合法
 **/
function checkRealPath($base, $curr)
{
    return (strpos(realpath($curr), realpath($base)) === 0);
}

function display_file_link($withPath, $entry, $urlchars)
{
    $f = str_replace(array_keys($urlchars), array_values($urlchars), adjust_char($withPath . $entry));

    if (defined('wmhelp')) {
        return 'javascript:;" onclick="OpenFile(\'' . $f . '\'); return false;" target="_blank" class="link_fnt01">';
    } else {
        return $f . '" target="_blank" class="link_fnt01">';
    }
}

/**
 * 印出目錄與檔案
 */
function display_entry()
{
    global $currPath, $fullPath, $entry, $MSG, $sysSession, $urlchars;
    $withPath = substr($fullPath, strlen(sysDocumentRoot));

    if ($currPath != '/') {
?>
                <tr class="cssTrEvn font01">
                  <td align="center"><img src="/theme/<?php
        echo $sysSession->theme;
?>/filetype/folder.gif" valign="absmiddle" width="20" height="20"></td>
                  <td align="center"><a href="javascript:chdir('/')" title="<?= $MSG['return_root'][$sysSession->lang]; ?>" class="cssAnchor"><big><b>&nbsp;/&nbsp;</b></big></a></td>
                  <td colspan="4"><a href="javascript:chdir('..')" title="<?= $MSG['return_parent'][$sysSession->lang]; ?>" class="cssAnchor"><big><b>. .</b></big></a></td>
                </tr>

<?php
    }

    $c = 0;
    for ($i = 0; $i < 2; $i++) {
        for ($j = 0; $j < count($entry[$i]); $j++) {
            $fullfile = $fullPath . $entry[$i][$j];
            $cla      = $cla == 'cssTrOdd font01' ? 'cssTrEvn font01' : 'cssTrOdd font01';
            $w = mb_convert_encoding(adjust_char($entry[$i][$j]), 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win');
?>
                <tr class="<?= $cla; ?>">
                  <td align="center"><input type="checkbox" onclick="selFile()" name="<?= ($i ? 'file' : 'dir'); ?>_entry[]" value="<?= ($j + 1); ?>"></td>
                  <td align="center"><img src="/theme/<?php
            echo $sysSession->theme;
?>/filetype/<?= ($i ? getFileType($entry[$i][$j]) : 'folder'); ?>.gif" valign="absmiddle" width="20" height="20"></td>
                  <td><a href="<?= ($i ? (display_file_link($withPath, $entry[$i][$j], $urlchars)) : ('javascript:chdir(' . ($j + 1) . ')" title="' . $MSG['chdir'][$sysSession->lang] . '" class="link_fnt01">')) . $w; ?></a></td>
                  <td align="right" style=""><p title="<?= number_format(wm_filesize($fullfile)); ?>"><?= format_size(wm_filesize($fullfile) / 1024); ?></p></td>
                  <td align="center"><?= date('Y-m-d H:i:s', wm_filemtime($fullfile)); ?></td>
                  <td><a href="javascript:rename(<?= ($i ? '-' : '') . ($j + 1) . ')" class="link_fnt01"><img src="/theme/default/teach/rename.gif" border="0" align="absmiddle" width="20" height="20" title="' . $MSG['rename'][$sysSession->lang] . '"></a> ' . (($i && isText($entry[$i][$j])) ? ('<a href="javascript:edit(' . ($j + 1) . ')" class="link_fnt01"><img src="/theme/default/teach/edit.gif" border="0" align="absmiddle" width="20" height="20" title="' . $MSG['edit'][$sysSession->lang] . '"></a>') : ''); ?></td>
                </tr>

<?php
        }
    }
}

/**
 * 編輯檔案畫面
 */
function editFile($thisfile)
{
    global $fullPath, $currPath, $ticket, $MSG, $sysSession;

    showXHTML_head_B($MSG['fm_caption'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$GLOBALS['wmTopDir']}/wm.css");
    showXHTML_head_E();
    showXHTML_body_B();
    showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse"');
    showXHTML_tr_B();
    showXHTML_td_B();
    $ary[] = array(
        $MSG['fm_caption'][$sysSession->lang],
        'tab01',
        ''
    );
    showXHTML_tabs($ary, 1);
    showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_tr_B();
    showXHTML_td_B('valign="top" class="bg01"');
    showXHTML_form_B('action="' . $_SERVER['REQUEST_URI'] . '" method="POST" style="display: inline"', 'mainform');

    showXHTML_table_B('id="tab01" width="1000" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');

    $filename = str_replace('//', '/', $currPath . $thisfile);
    $ticket   = md5(sysTicketSeed . $currPath . $thisfile);

    if ($_SERVER['SCRIPT_NAME'] == '/academic/course/filemanager.php') {
        showXHTML_tr_B('class="cssTrEvn font01"');
        showXHTML_td('', adjust_char(str_replace('//', '/', $fullPath . $thisfile)));
        showXHTML_tr_E();
    }

    showXHTML_tr_B('class="cssTrOdd font01"');
    showXHTML_td('', $MSG['cur_path'][$sysSession->lang] . ' ' . adjust_char($filename));
    showXHTML_tr_E();

    showXHTML_tr_B('class="cssTrEvn font01"');
    showXHTML_td_B();
    if ($_POST['op'] == 'ed') {
        $word    = implode('', file($fullPath . $thisfile));
        $content = isUTF8($word) ? $word : adjust_char($word);
        if (isHTML($thisfile)) {
            $oEditor = new wmEditor;
            $oEditor->setValue(stripslashes($content));
            $oEditor->addContType('isHTML', 1);
            $oEditor->generate('content', 979, 450);
        } else {
            showXHTML_input('textarea', 'content', stripslashes($content), '', 'cols="140" rows="25" class="cssInput"');
        }
    } else if ($_POST['html']) {
        $content = <<< EOB
<!DOCTYPE HTML>
    <html>
    </html>
EOB;
        $oEditor = new wmEditor;
        $oEditor->setValue(stripslashes($content));
        $oEditor->addContType('isHTML', 1);
        $oEditor->generate('content', 640, 450);
    } else {
        showXHTML_input('textarea', 'content', stripslashes($content), '', 'cols="140" rows="25" class="cssInput"');
    }
    showXHTML_td_E();
    showXHTML_tr_E();

    showXHTML_tr_B('class="cssTrOdd font01"');
    showXHTML_td_B();
    showXHTML_input('hidden', 'ticket', $ticket);
    showXHTML_input('hidden', 'op', 'md');
    showXHTML_input('hidden', 'thisfile', rawurlencode($thisfile));
    showXHTML_input('hidden', 'newname', '');
    showXHTML_input('hidden', 'currPath', rawurlencode($currPath));
    showXHTML_input('submit', '', $MSG['btn_save'][$sysSession->lang], '', 'class="cssBtn" onclick="document.getElementById(\'mainform\').op.value=\'save\';"');
    showXHTML_input('submit', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="document.getElementById(\'mainform\').op.value=\'\';"');
    showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_table_E();
    showXHTML_form_E();
    showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_table_E();
    showXHTML_body_E();
}

/**
 * 選擇複製/搬移檔案畫面
 */
function selectTarget($mode)
{
    global $basePath, $currPath, $allDir, $ticket, $MSG, $sysSession;
    $allDir = '';
    $text   = $mode ? $MSG['move_file'][$sysSession->lang] : $MSG['copy_file'][$sysSession->lang];
    $op     = $mode ? 'mv1' : 'cp1';

    showXHTML_head_B($MSG['fm_caption'][$sysSession->lang]);
        showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$GLOBALS['wmTopDir']}/wm.css");
        showXHTML_CSS('inline', "ul    {list-style-type: none; margin-left: 14; padding-left: 0}\n");
        $scr = <<< EOB

    function to(p){
        var obj = document.getElementById('mainform');
        obj.toPath.value = p;
        obj.submit();
    }
EOB;
        showXHTML_script('inline', $scr);
    showXHTML_head_E();

    showXHTML_body_B();
        showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse"');
            showXHTML_tr_B();
            showXHTML_td_B();
            $ary[] = array(
                $MSG['fm_caption'][$sysSession->lang],
                'tab01',
                ''
            );
            showXHTML_tabs($ary, 1);
            showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_tr_B();
            showXHTML_td_B('valign="top" class="bg01"');
            showXHTML_form_B('action="' . $_SERVER['REQUEST_URI'] . '" method="POST" style="display: inline"', 'mainform');

            showXHTML_table_B('id="tab01" width="760" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');
            showXHTML_tr_B('class="cssTrEvn font01"');
            showXHTML_td('', $MSG['sel_dir1'][$sysSession->lang] . $text . $MSG['sel_dir2'][$sysSession->lang]);
            showXHTML_tr_E();

            showXHTML_tr_B('class="cssTrOdd font01"');
            showXHTML_td('', $MSG['cur_path'][$sysSession->lang] . ' ' . adjust_char($currPath));
            showXHTML_tr_E();

            showXHTML_tr_B('class="cssTrEvn font01"');
            showXHTML_td_B();
            echo '<ul><li><img src="/theme/', $sysSession->theme, '/teach/minus.gif" align="absmiddle"><a href="javascript:to(\'%2F\');" class="link_fnt01">/root</a></li>';
            getAllDir($basePath . '/');
            echo $allDir;
            $currPathx = rawurlencode($currPath);
            echo '</ul>';
            showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="cssTrOdd font01"');
            showXHTML_td_B();
            $currPathx = rawurlencode($currPath);
            showXHTML_input('hidden', 'ticket', $ticket);
            showXHTML_input('hidden', 'op', $op);
            showXHTML_input('hidden', 'toPath', '');
            showXHTML_input('hidden', 'target', $_POST['target']);
            showXHTML_input('hidden', 'currPath', $currPathx);
            showXHTML_input('submit', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="document.getElementById(\'mainform\').op.value=\'\';"');
            showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_table_E();
            showXHTML_form_E();
            showXHTML_td_E();
            showXHTML_tr_E();
        showXHTML_table_E();
    showXHTML_body_E();
}


/*************************************************************************************************/
/**
 * 主程式開始
 */
// 偵測上傳檔案是否超過限制
if (detectUploadSizeExceed()) {
    // 切到上傳檔案這個分頁
    $tabs      = (isset($_COOKIE['fileManager']) && in_array($_COOKIE['fileManager'], array(
        1,
        2,
        3,
        4,
        5
    ))) ? $_COOKIE['fileManager'] : 4;
    $resultMsg = $MSG['upload_file_error'][$sysSession->lang];
}

$curr_mode        = $ADODB_FETCH_MODE;
$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
$quota_limit      = $quota_used = 0;

if (defined('basePath') && wm_is_dir(basePath)) {
    $basePath = basePath;
    foreach (array(
        '/([0-9]{5})/door/.*$',
        '/content/([0-9]{6})/?$',
        '/course/([0-9]{8})/content/?$'
    ) as $reg) {
        if (ereg($reg, basePath, $regs)) {
            getQuota($regs[1], $real_used, $quota_limit);
            define('QUOTA_ID', $regs[1]);
        }
    }
} else {
    $basePath = sprintf('%s/base/%05d/course/%08d/content', sysDocumentRoot, $sysSession->school_id, $sysSession->course_id);
    getQuota($sysSession->course_id, $real_used, $quota_limit);
    define('QUOTA_ID', $sysSession->course_id);
}

$ADODB_FETCH_MODE = $curr_mode;

$msgQuota = str_replace('%TYPE%', $MSG[getQuotaType(QUOTA_ID)][$sysSession->lang], $MSG['quota_full'][$sysSession->lang]);

$currPath = rawurldecode($_POST['currPath']);
if (preg_match('!(\.\./)+!', $currPath))
    die('access denied.');

if ($currPath == '')
    $currPath = '/';
$errno = 0;
if (!checkRealPath($basePath, $basePath . $currPath)) {
    $errno    = 1; // 目前只給 rm 使用而已
    $currPath = '/';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //if ($_POST['ticket'] != getTicket(ereg_replace('^http://' . $_SERVER['HTTP_HOST'] . '(:[0-9]+)?',
    //                                               '',
    //                                               $_SERVER['HTTP_REFERER']
    //                                              ),
    //                                  $basePath,
    //                                  $currPath
    //                                 )
    //   ) die('Access Denied.');

    // 如果是切換目錄
    if ($_POST['op'] == 'cd') {
        $resultMsg = $MSG['cd_failure'][$sysSession->lang];
        switch ($_POST['target']) {
            case '/':
                $currPath  = '/';
                $resultMsg = $MSG['cd_success'][$sysSession->lang];
                break;
            case '..':
                if ($currPath != '/')
                    $currPath = escapeSingleQuote(dirname($currPath) . '/');
                if ($currPath == '//')
                    $currPath = '/';
                $resultMsg = $MSG['cd_success'][$sysSession->lang];
                break;
            default:
                $target = intval($_POST['target']);
                if ($target-- > 0) {
                    $fullPath = $basePath . $currPath;
                    getAllEntry();
                    if ($target < count($entry[0])) {
                        $targetPath = $basePath . $currPath . $entry[0][$target];
                        if (wm_is_dir($targetPath)) {
                            $currPath .= ($entry[0][$target] . '/');
                            $resultMsg = $MSG['cd_success'][$sysSession->lang];
                        }
                    }
                }
                break;
        }

        $fullPath = $basePath . $currPath;
        getAllEntry();

    }
    // 其他操作
    elseif (isset($_POST['op'])) {
        $fullPath = $basePath . $currPath;
        switch ($_POST['op']) {
            case 'rn': // rename
                getAllEntry();
                $target  = intval($_POST['target']);
                $word    = replaceIllegalChar(stripslashes($_POST['newname']));
                if (strpos($word, '.php') !== FALSE ) {
                    wmSysLog(900100199, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'change FileName:'.mysql_escape_string($_POST['newname']));
                    $word = str_replace('.php','.phps',$word);
                }
                $newname = (($w = ($word)) === FALSE) ? $word : $w;
                $newname = trim($newname);
                if (wm_is_dir($fullPath . $newname) || wm_is_file($fullPath . $newname)) {
                    if ($newname != '' && mb_ereg('[\\/:*?"<>|]', $newname) === FALSE)
                        $resultMsg = $word . $MSG['msg_same_name'][$sysSession->lang];
                    else
                        $resultMsg = $word . $MSG['include_unrecognized_char'][$sysSession->lang];
                } elseif (strpos($word, '.') === 0) {
                    $resultMsg = $word . $MSG['name_can_not_dot_start'][$sysSession->lang];
                } else if (abs($target) > 0) {
                    chdir($fullPath);
                    $resultMsg = rename($entry[($target > 0 ? 0 : 1)][abs($target) - 1], $newname) ? $MSG['rn_success'][$sysSession->lang] : $MSG['rn_failure'][$sysSession->lang];
                }
                break;
            case 'cp':
                selectTarget(false);
                exit;
                break;
            case 'mv': // move
                selectTarget(true);
                exit;
                break;
            case 'cp1':
                $word    = ereg_replace('[/]{2,}', '/', stripslashes($_POST['toPath']));
                $toPath = (( $w = ($word)) === FALSE) ? $word : $w;
                $toPaths = $basePath . $toPath;
                if (checkRealPath($basePath, $toPaths)) {
                    if ($toPath != '' && mb_ereg('\.\.', $toPath) === FALSE && wm_is_dir($toPaths) && is_writable($toPaths)) {
                        getAllEntry();
                        $fs = explode(',', $_POST['target']);
                        if ($fullPath == $toPaths)
                            $toPaths .= 'new_';
                        foreach ($fs as $i) {
                            $file = $fullPath . $entry[1][$i - 1];
                            if (wm_is_file($file)) {
                                copy($file, $toPaths . $entry[1][$i - 1]);
                            }
                        }
                    }
                } else { // 複製失敗
                    $resultMsg = $MSG['msg_copy_failure'][$sysSession->lang];
                }
                break;
            case 'mv1':
                $word    = stripslashes($_POST['toPath']);
                $toPath = (( $w = ($word)) === FALSE) ? $word : $w;
                $toPaths = $basePath . $toPath;
                if (checkRealPath($basePath, $toPaths)) {
                    if ($toPath != '' && mb_ereg('\.\.', $toPath) === FALSE && wm_is_dir($toPaths) && is_writable($toPaths)) {
                        getAllEntry();
                        $fs    = explode(',', $_POST['target']);
                        $files = '';
                        if ($fullPath != $toPaths) {
                            foreach ($fs as $i)
                                $files .= '"' . $entry[1][$i - 1] . '" ';
                            if (is_dir($fullPath)) {
                                @exec(sprintf("cd '%s' && mv %s '%s'", $fullPath, $files, $toPaths));
                            }
                        }
                    }
                } else { // 移動失敗
                    $resultMsg = $MSG['msg_move_failure'][$sysSession->lang];
                }
                break;
            case 'rm': // remove
                if ($errno == 0) {
                    getAllEntry();
                    $fs = explode(',', $_POST['target']);
                    foreach ($fs as $i) {
                        if (abs($i) > 0) {
                            if ($i > 0) {
                                deltree($fullPath . $entry[0][$i - 1]);
                            } else
                                @unlink($fullPath . $entry[1][abs($i) - 1]);
                        }
                    }

                    // 更新quota資訊
                    getCalQuota(QUOTA_ID, $real_used, $quota_limit);
                    setQuota(QUOTA_ID, $real_used);
                    header('LOCATION: /teach/files/manager.php');
                } else { // 刪除失敗
                    $resultMsg = $MSG['msg_rm_failure'][$sysSession->lang];
                }
                break;
            case 'md': // mkdir
                $word      = stripslashes($_POST['newdir']);
                $newDir = (($w = ($word)) === FALSE) ? $word : $w;
                $newDir = trim($newDir);
                $resultMsg = $MSG['mkdir'][$sysSession->lang] . $newDir . $MSG['failure'][$sysSession->lang];
                if (wm_is_dir($fullPath . $newDir)) {
                    if ($newDir != '' && mb_ereg('[\\/:*?"<>|]', $newDir) === FALSE)
                        $resultMsg = $word . $MSG['files_exist'][$sysSession->lang];
                    else
                        $resultMsg = $word . $MSG['include_unrecognized_char'][$sysSession->lang];
                } elseif (strpos($newDir, '.') === 0) {
                    $resultMsg = $newDir . $MSG['name_can_not_dot_start'][$sysSession->lang];
                } elseif (@chdir($fullPath) && @mkdir($newDir, 0755)) {
                    $resultMsg = $MSG['mkdir'][$sysSession->lang] . $word . $MSG['success'][$sysSession->lang];
                }
                break;
            case 'ul': // 上傳檔案
                $a         = count($_FILES['upload']['tmp_name']);
                $resultMsg = $MSG['upload_complete'][$sysSession->lang];
                for ($i = 0; $i < $a; $i++) {
                    $word     = stripslashes($_FILES['upload']['name'][$i]);
                    $filename = (($w = ($word)) === FALSE) ? $word : $w;
                    $filename = trim($filename);
                    if ($filename != '' && mb_ereg('[\\/:*?"<>|]', $filename) === FALSE) {
                        if (is_uploaded_file($_FILES['upload']['tmp_name'][$i])){
                            /* #55054 (B) [MOOCs] 如果上傳的是課程介紹影片，則存放到 user 目錄底下的 public 資料夾下 By Spring */
                            if ($_FILES['upload']['name'][$i] == 'course_introduce.mp4' ){
                                if (trim(get_mime_type($_FILES['upload']['tmp_name'][$i])) === 'video/mp4') {
                                    if(!is_dir($basePath . '/public')){
                                        mkdir($basePath . '/public', 0755);
                                    }
                                    move_uploaded_file($_FILES['upload']['tmp_name'][$i], $basePath . '/public/' . $filename);

                                    // 擷取影片畫面
                                    $ffmpeg = exec("sh -c 'PATH=/usr/bin:/usr/sbin:/sbin:/bin:/usr/local/bin which ffmpeg'");
                                    system($ffmpeg . ' -ss 00:00:05 -i '. $basePath . '/public/course_introduce.mp4 -y -f image2 -vframes 1 '. $basePath . '/public/course_introduce.jpg', $ScreenshotsRtn1);
                                    system($ffmpeg . ' -ss 00:00:10 -i '. $basePath . '/public/course_introduce.mp4 -y -f image2 -vframes 1 '. $basePath . '/public/course_introduce_2.jpg', $ScreenshotsRtn2);
                                    system($ffmpeg . ' -ss 00:00:15 -i '. $basePath . '/public/course_introduce.mp4 -y -f image2 -vframes 1 '. $basePath . '/public/course_introduce_3.jpg', $ScreenshotsRtn3);
                                    // $ScreenshotsRtn2、$ScreenshotsRtn3 為預備截圖，暫不作判斷
                                    if ($ScreenshotsRtn1 !== 0) {
                                        $resultMsg .= '; Taking screenshots from "'.$filename.'" is failed!';
                                    }
                                } else {
                                        $resultMsg .= '; File ["'.$filename.'"] format is error!';
                                }
                            }
                            /* #55054 (E) [MOOCs] */
                            else
                            {
                                //檔名轉為utf8
                                $filename = mb_convert_encoding($filename, 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win');
                                move_uploaded_file($_FILES['upload']['tmp_name'][$i], $fullPath . $filename);
                            }
                        }
                    } else {
                        $unrecognized_str .= "[{$word}], ";
                    }
                }

                if (isset($unrecognized_str)) {
                    $unrecognized_str = ereg_replace(', $', '', $unrecognized_str);
                    $resultMsg .= '; ' . str_replace('%s', $unrecognized_str, $MSG['upload_include_unrecognized_char'][$sysSession->lang]);
                }
                break;
            case 'uz':
                $word      = stripslashes($_FILES['uploadz']['name']);
                $filename = (($w = ($word)) === FALSE) ? $word : $w;
                $ext       = strrchr($_FILES['uploadz']['name'], '.');
                $resultMsg = $MSG['extract'][$sysSession->lang] . $word . ($arc->extract_it($_FILES['uploadz']['tmp_name'], $fullPath, $ext) ? $MSG['failure'][$sysSession->lang] : $MSG['success'][$sysSession->lang]);
                @unlink($_FILES['uploadz']['tmp_name']);
                break;
            case 'nf':
                $word     = stripslashes($_POST['newfile']);
                $filename = (($w = ($word)) === FALSE) ? $word : $w;
                $filename = trim($filename);
                if ($filename != '' && mb_ereg('[\\/:*?"<>|]', $filename) === FALSE) {
                    if (wm_is_dir($fullPath . $filename) || wm_is_file($fullPath . $filename)) {
                        $resultMsg = $word . $MSG['msg_same_name'][$sysSession->lang];
                    } else {
                        editFile($filename);
                        exit;
                    }
                } else if (mb_ereg('[\\/:*?"<>|]', $filename) !== FALSE) {
                    //die('<script>alert("' . $MSG['include_illegal_char'][$sysSession->lang] . '"); location.href="manager.php";</script>');
                    //exit;
                    $resultMsg = $word . $MSG['include_illegal_char'][$sysSession->lang];
                } else {
                    //die('<script>alert("' . $MSG['include_unrecognized_char'][$sysSession->lang] . '"); location.href="manager.php";</script>');
                    //exit;
                    $resultMsg = $word . $MSG['include_unrecognized_char'][$sysSession->lang];
                }
                break;
            case 'ed': // 編輯檔案
                getAllEntry();
                $target = intval($_POST['target']);
                if ($target > 0 && $target <= count($entry[1])) {
                    editFile($entry[1][$target - 1]);
                }
                exit;
                break;
            case 'save': // 存檔
                $filename = rawurldecode($_POST['thisfile']);
                if ($_POST['ticket'] != md5(sysTicketSeed . $currPath . $filename))
                    die('incorrect ticket.');
                $resultMsg = $MSG['msg_save_failure'][$sysSession->lang];
                if (($fp = fopen($fullPath . $filename, 'w')) != NULL) {
                    $word = stripslashes($_POST['content']);
                    // fwrite($fp, ((($w = ($word)) === FALSE) ? $word : $w));
                    fwrite($fp, "\xEF\xBB\xBF" . ((($w = ($word)) === FALSE) ? $word : $w));
                    fclose($fp);
                    $resultMsg = $MSG['msg_save_success'][$sysSession->lang];
                }
                break;
            default:
                break;
        }
    } else {
        $fullPath = $basePath . $currPath;
    }
} else {
    $fullPath = $basePath . $currPath;
}

// 更新quota資訊
getCalQuota(QUOTA_ID, $real_used, $quota_limit);
setQuota(QUOTA_ID, $real_used);

getAllEntry();

$ticket = getTicket(ereg_replace('^http://' . $_SERVER['HTTP_HOST'] . '(:[0-9]+)?', '', $_SERVER['HTTP_REFERER']), $basePath, $currPath);
display_currpath($resultMsg);

//ob_end_flush(); // 正式版刪除
?>
