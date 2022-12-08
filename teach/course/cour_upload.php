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
//require_once(sysDocumentRoot . '/teach/course/cour_lib.php');
require_once(sysDocumentRoot . '/lib/lib_browser.php');

$sysSession->cur_func = '1200100100';
$sysSession->restore();
if (!aclVerifyPermission(1200100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

function detect_service($mode)
{
    global $sysSession;
    
    switch ($mode) {
        case 'webdav':
            if (($fp = @fsockopen('127.0.0.1', $_SERVER['SERVER_PORT'])) == false)
                return false;
            fputs($fp, "OPTIONS /{$sysSession->school_id}_{$sysSession->course_id}/ HTTP/1.1\n");
            fputs($fp, "Host: {$_SERVER['HTTP_HOST']}\n");
            fputs($fp, "Content-type: text/xml\n");
            fputs($fp, "Content-length: 0\n");
            fputs($fp, "User-Agent: MSIE\n");
            fputs($fp, "Cookie: idx={$_COOKIE['idx']}\n");
            fputs($fp, "Connection: close\n\n");
            $result = false;
            while (!feof($fp)) {
                $buf = fgets($fp, 4096);
                // if(preg_match('/^DAV: /', $buf)) $result = true;
                if (preg_match('/^Allow: /', $buf))
                    $result = true; // edit by lst
            }
            fclose($fp);
            return $result;
            break;
        case 'ftp':
            if (($fp = @fsockopen('127.0.0.1', 21)) == false)
                return false;
            if (preg_match('/ProFTPD ([0-9.]+) Server/', fread($fp, 128), $ver)) {
                if (version_compare('1.2.10', $ver[1]) > 0) {
                    echo $MSG['msg_ftp'][$sysSession->lang];
                    fclose($fp);
                    return false;
                }
            }
            fclose($fp);
            return true;
            break;
        /*
        case 'netbios':
        if (($fp = @fsockopen('localhost', 139)) == false) return false;
        fclose($fp);
        return true;
        break;
        */
        default:
            return false;
    }
}

$realpath = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/content', $sysSession->school_id, $sysSession->course_id);

// 建立課程目錄
if (!is_dir($realpath)) {
    if ((int) phpversion() === 5) {
        mkdir($realpath, 0770, true);
    } else {
        function create_folders($realpath)
        {
            return is_dir($realpath) or (create_folders(dirname($realpath)) and mkdir($realpath, 0777));
        }
        create_folders($realpath);
    }
}

$url  = "{$_SERVER['HTTP_HOST']}/{$sysSession->school_id}_{$sysSession->course_id}/";
$urlx = $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] == '80' ? '' : ":{$_SERVER['SERVER_PORT']}") . "/{$sysSession->school_id}_{$sysSession->course_id}/";

$isQuotaExceed = getRemainQuota($sysSession->course_id) <= 0 ? 1 : 0;

$displaylang = ($sysSession->lang == 'Big5' ? '&displaylang=zh-tw' : ($sysSession->lang == 'GB2312' ? '&displaylang=zh-cn' : '&displaylang=en'));

if ($_SERVER['argv']) {
    if ($_SERVER['argv'][0] == '1') {
        $headers = apache_request_headers();
        
        setcookie('forum_sortby', '', time() - 3600, '/');
        setcookie('idx', $_COOKIE['idx'], time() + 21600, "/");
        setcookie('idx', $_COOKIE['idx'], time() + 21600, "/{$sysSession->school_id}_{$sysSession->course_id}/");
        
        echo <<< EOB
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
        <style>
            .hFolder{ behavior:url(#default#httpFolder); }
        </style>
        <script>
            function fnNavigate() {
                oViewFolder.navigateFrame("http://$urlx", "_self");
            }
            window.onload = function() {
                document.getElementById("oViewFolder").click();
                self.close();
            }
        </script>
    </head>
    <body>
        <a id="oViewFolder" class="hFolder" onclick="fnNavigate()">web資料夾開啟中，請稍後.....</a>
    </body>
</html>




EOB;
        exit;
    } else if ($_SERVER['argv'][0] == '4') {
        list($content_id, $status, $owner) = dbGetStSr('WM_term_course as TC
                                                            join WM_content as CT on TC.content_id = CT.content_id
                                                            left join WM_content_ta as TA on CT.content_id = TA.content_id', 'TC.content_id, CT.status, TA.username', 'TC.course_id=' . $sysSession->course_id, ADODB_FETCH_NUM);
        if ($status == 'modifiable' && (empty($owner) || $owner == $sysSession->username)) // 檢查該課程是否有引用教材庫
            {
            define('CONTENT', true);
            $_SERVER['argv'][0] = $sysSession->school_id . '_' . $content_id;
            include_once(sysDocumentRoot . '/academic/sch/open_web_folder.php');
            exit;
        }
    }
}

dbSet('WM_auth_ftp', "home='" . sprintf(sysDocumentRoot . '/base/%05d/course/%08d/content', $sysSession->school_id, $sysSession->course_id) . "'", "userid='{$sysSession->username}'");
// dbSet('WM_auth_samba', "home_dir='" . sprintf('\\\\WM3\\%05d_%08d', $sysSession->school_id, $sysSession->course_id) . "'", "username='{$sysSession->username}'");

$canOpenWebFolder = preg_match('/\bMSIE [^;]*; Windows NT 5/', $_SERVER['HTTP_USER_AGENT']);

/*
 * 偵測各項方法
 */
// webdav
$service[1] = detect_service('webdav');
$service[2] = detect_service('ftp');
$service[3] = detect_service('netbios');


$tabs = isset($_POST['tabs']) ? $_POST['tabs'] : ($_GET['tabs'] ? $_GET['tabs'] : 4);
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
    '#' => '%2523',
    ' ' => '%20',
    '"' => '%22',
    '&' => '%26',
    "'" => '%27',
    '+' => '%2A',
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
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    
    $scr         = <<< EOB

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

    if (name.search(/[\\\/:\*\?"<>\|%#+]/g) > -1) {
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
    if (obj.target.value == '') return;
    obj.op.value = op;
    obj.submit();
}

function launch_target(m) {
    switch (m) {
        case 1:
            window.open('{$_SERVER['PHP_SELF']}?' + m, 'upload_win', 'width=600,height=375,status=0,toolbar=1,menubar=1,resizable=1');
            break;
        case 2:
            window.open('cour_ftp_upload_{$sysSession->lang}.html', '', 'width=820, height=450, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
            break;
        case 3:
            window.open('webclient_{$sysSession->lang}.html', '', 'width=820, height=450, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
            break;

    }
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
    newNode = nodes[nodes.length - 3].cloneNode(true);
    //    var newNode = olObj.lastChild.previousSibling.cloneNode(true);
    //    newNode = olObj.insertBefore(newNode, olObj.lastChild);
    newNode = olObj.insertBefore(newNode, nodes[nodes.length - 2]);
    /*
    if (olObj.childNodes.length == 2){
        newNode.innerHTML += '&nbsp;<a href="javascript:;" onclick="rmfile(this.parentNode);" style="font-size: 14pt; text-decoration: none">-</a>';
    }
    */
}

function rmfile(obj) {
    var nodes = obj.parentNode.getElementsByTagName('input');
    var c = 0;
    for (var i = 0; i < nodes.length; i++) {
        if (nodes[i].type == 'file') c++;
    }
    if (c > 1) {
        $('#uploads').remove();
        $('#upload_box').append('<input type="file" name="upload[]" id="upload" size="60" />');
    } else {
        var newNode = obj.cloneNode(true); // 若原本有選定檔案則清空
        obj.parentNode.replaceChild(newNode, obj);
    }
}

function OpenFile(fileUrl) {
    window.top.opener.SetUrl(fileUrl);
    window.top.close();
    window.top.opener.focus();
}

function chgTab(val) {
    document.getElementById('tabs').value = val;
    document.getElementById('tab0' + val).rows[0].style.display = 'none';
    document.cookie = 'fileManager=' + val + ';';
}

window.onload = function() {
    //    document.getElementById('toolbar2').innerHTML = document.getElementById('toolbar1').innerHTML;
    document.body.scroll = 'yes';
    if ({$GLOBALS['real_used']} >= {$GLOBALS['quota_limit']})
        alert("{$GLOBALS['msgQuota']}");
        
    if ((exec_env == 'teach') && window.File && window.FileReader && window.FileList && window.Blob) {
        document.getElementById('fileUploadTraditional').style.display = 'none';
        // document.getElementById('fileUploadHtml5').style.display = 'block';
        $('#fileUploadHtml5').show('slow');
        document.getElementById('fileUploadHtml5Header').style.display = '';
    } else {
        document.getElementById('fileUploadTraditional').style.display = 'block';
        document.getElementById('fileUploadTraditional5Header').style.display = '';
        document.getElementById('fileUploadHtml5').style.display = 'none';
    }
}

EOB;
    $quota_value = true;
    if ($GLOBALS['real_used'] >= $GLOBALS['quota_limit']) {
        $quota_value = false;
    }
    showXHTML_script('inline', $scr);
    showXHTML_head_E('<meta http-equiv="X-UA-Compatible" content="IE=8" >');
    
    showXHTML_body_B('');
    echo "<div>\n";
    showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" width="760"');
    showXHTML_tr_B();
    showXHTML_td_B();
    $ary = array(
        array(
            $MSG['content_upload'][$sysSession->lang],
            'tab04',
            'chgTab(4);'
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
    
    
    
    showXHTML_table_B('id="tab04" width="760" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse; display: ' . ($tabs == 4 ? '' : 'none') . '" class="cssTable"');
    showXHTML_tr_B('class="cssTrEvn font01"');
    showXHTML_td('style="font-weight: bold; color: red"', $msgs);
    showXHTML_tr_E();
    
    showXHTML_tr_B('class="cssTrEvn font01" id="total-filesize"');
    showXHTML_td('align="right"', displayQuota());
    showXHTML_tr_E();
    
    showXHTML_tr_B('class="font01 cssTrHead"');
    showXHTML_td_B('class="cssTd" nowrap align="left"');
    echo '<div id="fileUploadTraditionalHeader" style="display:none;">'.$MSG['upload_file'][$sysSession->lang] . '(' . $MSG['file_limit'][$sysSession->lang] . '<span style="color: red; font-weight: bold">' . ini_get('post_max_size') . '</span>)'.'</div>';
    echo '<div id="fileUploadHtml5Header" style="display:none;">' . $MSG['upload_file'][$sysSession->lang] . '</div>';
    showXHTML_td_E();
    showXHTML_tr_E();
    
    
    showXHTML_tr_B('class="cssTrEvn" style="height: 5.8em;"');
    showXHTML_td_B();
    
        // 傳統上傳方式
        showXHTML_table_B('id="fileUploadTraditional" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; display:none;"');
            showXHTML_tr_B('class="cssTrEvn"');
            showXHTML_td('class="cssTd" colspan="2" height="15"');
            showXHTML_tr_E();

            showXHTML_tr_B('class="cssTrEvn"');
            showXHTML_td('class="cssTd" width="45"');
            showXHTML_td_B('class="cssTd" colspan="2" height="15"');
            showXHTML_input('file', 'upload[]', '', '', 'size="50" class="cssInput"');
            showXHTML_input('button', '', $MSG['cede_file'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['real_used'] < $GLOBALS['quota_limit'] ? 'onclick="rmfile(this.parentNode.parentNode);"' : 'disabled'));
            showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="cssTrEvn"');
            showXHTML_td('class="cssTd" width="45"');
            showXHTML_td_B('class="cssTd" align="left"');
            showXHTML_input('button', '', $MSG['more_file'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['real_used'] < $GLOBALS['quota_limit'] ? 'onclick="addfile(this.parentNode.parentNode.parentNode);"' : 'disabled'));
            showXHTML_input('submit', '', $MSG['upload'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['real_used'] < $GLOBALS['quota_limit'] ? 'onclick="document.getElementById(\'mainform\').op.value=\'ul\';"' : 'disabled'));
            showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="cssTrEvn"');
            showXHTML_td('class="cssTd" colspan="2" height="15"');
            showXHTML_tr_E();

        showXHTML_table_E();

        // 多檔上傳
        echo '<div id="fileUploadHtml5" style="display:none;">';
        echo '<iframe id="iframeFileUpload" src="/teach/files/basic.php?currPath=' . $currPathx . '" width="750" height="320"></iframe>';
        echo '</div>';
        
    showXHTML_td_E();
    showXHTML_tr_E();
    
    
    // showXHTML_tr_B('class="font01 cssTrHead"');
    // showXHTML_td('class="cssTd" nowrap align="left"', $MSG['upload and unlock'][$sysSession->lang]);
    // showXHTML_tr_E();
    
    // showXHTML_tr_B('class="cssTrEvn"');
    // showXHTML_td_B();
    // showXHTML_table_B();
    // showXHTML_tr_B('class="cssTrEvn"');
    // showXHTML_td('class="cssTd" colspan="2" height="15"');
    // showXHTML_tr_E();
    
    // showXHTML_tr_B('class="cssTrEvn"');
    // showXHTML_td('class="cssTd" width="45"');
    // showXHTML_td_B('class="cssTd" colspan="2" height="15"');
    // showXHTML_input('file', 'uploadz', '', '', 'size="50" class="cssInput"');
    // showXHTML_td_E();
    // showXHTML_tr_E();
    
    // showXHTML_tr_B('class="cssTrEvn"');
    // showXHTML_td('class="cssTd" width="45"');
    // showXHTML_td_B('class="cssTd" align="left"');
    // showXHTML_input('submit', '', $MSG['upload_archive1'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['real_used'] < $GLOBALS['quota_limit'] ? 'onclick="document.getElementById(\'mainform\').op.value=\'uz\';"' : 'disabled'));
    // showXHTML_td_E();
    // showXHTML_tr_E();
    
    // showXHTML_tr_B('class="cssTrEvn"');
    // showXHTML_td('class="cssTd" colspan="2" height="15"');
    // showXHTML_tr_E();
    
    // showXHTML_table_E();
    // showXHTML_td_E();
    // showXHTML_tr_E();
    
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
    
    showXHTML_tr_B('class="font01 cssTrHead"');
    showXHTML_td('class="cssTd" nowrap align="left"', $MSG['ohter_way'][$sysSession->lang]);
    showXHTML_tr_E();
    
    $bw               = getBrowserInfo();
    $canOpenWebFolder = (!$isQuotaExceed && ((($bw['browser'] == 'msie') && version_compare($bw['fullVersion'], '7.0', '>=')) || ($bw['browser'] == 'trident')));
    
    //$canOpenWebFolder = true;
    
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td_B();
    showXHTML_table_B('style="margin-top: 30px; margin-bottom: 30px;"');
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td_B('align="center" width="250"');
    showXHTML_table_B('class="table table-bordered table-striped mooc-process" align="center" width="150" ');
    showXHTML_tr_B('class=""');
    if (sysLcmsEnable) {
        showXHTML_td('align="center" height="80" style="background-color: #06A2A4;color: white;font-size: 1.5em;font-weight: bold;cursor:hand;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;" onclick="document.lcmsFrm.submit();"', $MSG['libary'][$sysSession->lang]);
    } else {
        showXHTML_td('align="center" height="80" style="background-color: #ddd;color: #707070;font-size: 1.5em;font-weight: bold;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;" ', $MSG['libary'][$sysSession->lang]);
    }
    showXHTML_tr_E();
    
    showXHTML_table_E();
    
    showXHTML_td_E();
    
    showXHTML_td_B('align="center" width="250"');
    showXHTML_table_B('class="table table-bordered table-striped mooc-process" align="center" width="150" ');
    showXHTML_tr_B('class=""');
    
    if ($canOpenWebFolder && $quota_value) {
        showXHTML_td('align="center" height="80" style="background-color: #06A2A4;color: white;font-size: 1.5em;font-weight: bold;cursor:hand;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;" onclick="launch_target(1);" ', $MSG['web_folder'][$sysSession->lang]);
    } else {
        showXHTML_td('align="center" height="80" style="background-color: #ddd;color: #707070;font-size: 1.5em;font-weight: bold;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;" ', $MSG['web_folder'][$sysSession->lang]);
    }
    showXHTML_tr_E();
    showXHTML_table_E();
    showXHTML_td_E();
    showXHTML_tr_E();
    
    
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td('nowrap align="center" width="250" ', $MSG['class_share'][$sysSession->lang]);
    
    $txt = ($canOpenWebFolder) ? "{$MSG['co_notXP'][$sysSession->lang]} <a href='javascript:launch_target(3);'>{$MSG['instruction'][$sysSession->lang]}</a>" : "";
    
    showXHTML_td('nowrap align="center" width="250" ', $MSG['ie_limit'][$sysSession->lang] . '<br>' . $txt);
    
    showXHTML_tr_E();
    
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td_B('colspan="2"');
    echo '&nbsp;';
    showXHTML_td_E();
    showXHTML_tr_E();
    
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td_B('colspan="2"');
    showXHTML_table_B();
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td('class="cssTd" width="45"');
    showXHTML_td('style="background-color: #FFFFFF;color: #000000;font-size: 1.5em;font-weight: bold;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;"', $MSG['upload_max'][$sysSession->lang] . '<a href="javascript:launch_target(2);">' . $MSG['ftp_introduce'][$sysSession->lang]) . '</a>';
    showXHTML_tr_E();
    showXHTML_table_E();
    showXHTML_td_E();
    showXHTML_tr_E();
    
    
    
    showXHTML_table_E();
    
    showXHTML_form_E();
    showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_table_E();
    // lcmslibary
    if (sysLcmsEnable) {
        echo '<form action="/teach/course/lcms.php?action=resources" name="lcmsFrm" method="post" target="_blank" style="display: none;"></form>';
    }
    echo <<< EOB
</div>
<a id="method1" href="javascript:;"></a>
<a id="method2" href="ftp://{$sysSession->username}@{$_SERVER['HTTP_HOST']}" target="upload_win"></a>
<a id="method3" href="file://$url" target="upload_win"></a>
EOB;
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
    return preg_replace('/[\\/:\*\?"<>\|%#+]+/i', $r, $word);
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
    
    showXHTML_table_B('id="tab01" width="760" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');
    
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
            $oEditor->generate('content', 640, 450);
        } else {
            showXHTML_input('textarea', 'content', stripslashes($content), '', 'cols="140" rows="25" class="cssInput"');
        }
    } else if ($_POST['html']) {
        $content = <<< EOB
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>New Page</title>
</head>
<body>

</body>
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
                $newname = (($w = un_adjust_char($word)) === FALSE) ? $word : $w;
                if (wm_is_dir($fullPath . $newname) || wm_is_file($fullPath . $newname)) {
                    if ($newname != '' && mb_ereg('[\\/:*?"<>|]', $newname) === FALSE)
                        $resultMsg = $word . $MSG['msg_same_name'][$sysSession->lang];
                    else
                        $resultMsg = $word . $MSG['include_unrecognized_char'][$sysSession->lang];
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
                $toPath  = (($w = un_adjust_char($word)) === FALSE) ? $word : $w;
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
                $toPath  = (($w = un_adjust_char($word)) === FALSE) ? $word : $w;
                $toPaths = $basePath . $toPath;
                if (checkRealPath($basePath, $toPaths)) {
                    if ($toPath != '' && mb_ereg('\.\.', $toPath) === FALSE && wm_is_dir($toPaths) && is_writable($toPaths)) {
                        getAllEntry();
                        $fs    = explode(',', $_POST['target']);
                        $files = '';
                        if ($fullPath != $toPaths) {
                            foreach ($fs as $i)
                                $files .= "'" . $entry[1][$i - 1] . "' ";
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
                } else { // 刪除失敗
                    $resultMsg = $MSG['msg_rm_failure'][$sysSession->lang];
                }
                break;
            case 'md': // mkdir
                $word      = stripslashes($_POST['newdir']);
                $newDir    = (($w = un_adjust_char($word)) === FALSE) ? $word : $w;
                $resultMsg = $MSG['mkdir'][$sysSession->lang] . $newDir . $MSG['failure'][$sysSession->lang];
                if (wm_is_dir($fullPath . $newDir)) {
                    if ($newDir != '' && mb_ereg('[\\/:*?"<>|]', $newDir) === FALSE)
                        $resultMsg = $word . $MSG['files_exist'][$sysSession->lang];
                    else
                        $resultMsg = $word . $MSG['include_unrecognized_char'][$sysSession->lang];
                } elseif (@chdir($fullPath) && @mkdir($newDir, 0755)) {
                    $resultMsg = $MSG['mkdir'][$sysSession->lang] . $word . $MSG['success'][$sysSession->lang];
                }
                break;
            case 'ul': // 上傳檔案
                $a         = count($_FILES['upload']['tmp_name']);
                $resultMsg = $MSG['upload_complete'][$sysSession->lang];
                for ($i = 0; $i < $a; $i++) {
                    $word     = stripslashes($_FILES['upload']['name'][$i]);
                    $filename = (($w = adjust_char($word)) === FALSE) ? $word : $w;
                    if ($filename != '' && mb_ereg('[\\/:*?"<>|]', $filename) === FALSE) {
                        if (is_uploaded_file($_FILES['upload']['tmp_name'][$i])) {
                            $filename = mb_convert_encoding($filename, 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win');
                            move_uploaded_file($_FILES['upload']['tmp_name'][$i], $fullPath . $filename);
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
                $filename  = (($w = un_adjust_char($word)) === FALSE) ? $word : $w;
                $ext       = strrchr($_FILES['uploadz']['name'], '.');
                $resultMsg = $MSG['extract'][$sysSession->lang] . $word . ($arc->extract_it($_FILES['uploadz']['tmp_name'], $fullPath, $ext) ? $MSG['failure'][$sysSession->lang] : $MSG['success'][$sysSession->lang]);
                @unlink($_FILES['uploadz']['tmp_name']);
                break;
            case 'nf':
                $word     = stripslashes($_POST['newfile']);
                $filename = (($w = un_adjust_char($word)) === FALSE) ? $word : $w;
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
                    fwrite($fp, ((($w = un_adjust_char($word)) === FALSE) ? $word : $w));
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
