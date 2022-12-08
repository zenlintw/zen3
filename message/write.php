<?php
    /**
     * 發送新訊息
     *
     * 建立日期：2003/05/08
     * @author  ShenTing Lin
     * @version $Id: write.php,v 1.4 2010-06-17 08:07:11 lst Exp $
     * @copyright 2003 SUNNET
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/editor.php');
    require_once(sysDocumentRoot . '/lang/msg_center.php');
    require_once(sysDocumentRoot . '/message/lib.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    
    // $sysSession->cur_func = '2200200100';
    // $sysSession->restore();
    if (!aclVerifyPermission(2200200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    if ($sysSession->username == 'guest') {
        die('Access Deny!');
    }

    if (isSet($_GET['commonuse']) && $_GET['commonuse'] == 'true')
    {
        // #47340 Chrome 點選鉛筆圖示卻跳出傳送新訊息視窗-->設定目前所在目錄參數
        setNotebookID('sys_notebook');
        $isCommUse = true;
    }
    else
        $isCommUse = false;
    if (!isset($to) && isset($_POST['to']))             $to       = strip_scr($_POST['to']);
    if (!isset($priority) && isset($_POST['priority'])) $priority = intval($_POST['priority']);
    if (!isset($subject) && isset($_POST['subject']))   $subject  = strip_scr($_POST['subject']);
    if (!isset($content) && isset($_POST['content']))   $content  = strip_scr($_POST['content']);
    if (!isset($isHTML) && isset($_POST['isHTML']))     $isHTML   = ($_POST['isHTML'] == 'text' ? 'text' : 'html');
        $target    = 'index.php';
    if (!isset($title)) {
        if ($sysSession->cur_func == $msgFuncID['notebook']) {
            $head      = $MSG['tabs_notebook_title'][$sysSession->lang];
            $target    = 'notebook.php';
            $isNB      = true;
            $st_serial = 1;
        } else if ($sysSession->cur_func == $msgFuncID['message']) {
            $head      = $MSG['title'][$sysSession->lang];
            $isNB      = false;
            $st_serial = 2;
        } else {
            $isNB      = false;
            $st_serial = 3;
        }
    }

    // 改只取第一個簽名檔
    $RS      = dbGetStMr('WM_user_tagline', '`serial`, `title`, `ctype`', "username='{$sysSession->username}' LIMIT 0,1", ADODB_FETCH_ASSOC);
    $tagline = array('-1' => $MSG['not_use_tagline'][$sysSession->lang]);
    while (!$RS->EOF) {
        $tagline[$RS->fields['serial']] = $MSG['use_tagline'][$sysSession->lang];   // $RS->fields['title'];
        $RS->MoveNext();
    }
    $msg       = $MSG['need_to'][$sysSession->lang];
    $msg_empty = $isNB ? $MSG['msg_nb_no_data'][$sysSession->lang] : $MSG['msg_no_data'][$sysSession->lang];
    $js = <<< BOF
    var files = 1;
    var col = '';
    var MSG_TO = "{$msg}";
    var MSG_DATA_EMPTY = "{$msg_empty}";

    /**
     * Add a attachement
     **/
    function more_attachs() {
        if (files >= 10) {
            alert("{$MSG['msg_total_max'][$sysSession->lang]}");
            return;
        }
        var curNode = document.getElementById('upload_box');
        var newNode = curNode.cloneNode(true);
        newNode.className = col;
        curNode.parentNode.appendChild(newNode);
        newNode.getElementsByTagName("input")[0].value = "";
        files++;
    }

    /**
     * 取得最近一個 <TR> 兄節點
     */
    function getPrevSiblingTr(node){
        var cur = node;
        while(cur.previousSibling != null){
            cur = cur.previousSibling;
            if (cur.tagName == 'TR') return cur;
        }
        return null;
    }

    /**
     * delete a attachment
     **/
    function cut_attachs() {
        var curNode = document.getElementById('upload_box');
            
        if (files <= 1) {
            $('#uploads').remove();
            $('#upload_box').append('<input type="file" name="uploads[]" id="uploads" size="60" />');
            return;
        }
        var idx = curNode.parentNode.childNodes.length-1;
        curNode.parentNode.removeChild(curNode.parentNode.childNodes[idx]);
        files--;
    }

    /**
     * delete org attachment
     * @param
     * @return
     **/
    function del_attach(obj, val) {
        var nodes = null, node = null;

        node = document.getElementById("upload_base");
        if ((typeof(node) != "object") || (node == null)) return false;
        nodes = node.getElementsByTagName("input");
        for (var i = 0; i < nodes.length; i++) {
            if ((nodes[i].type != "hidden") || (nodes[i].name == "attachment")) continue;
            if (trim(nodes[i].value) == trim(val)) {
                obj.parentNode.parentNode.parentNode.removeChild(obj.parentNode.parentNode);
                nodes[i].parentNode.removeChild(nodes[i]);
            }
        }
    }

    /**
     * return message list
     **/
    function goList() {
        location.replace("{$target}");
    }

    function chkData() {
        var obj = document.getElementById("post1");
        var re = /\s/;
        var val = "";
        if ((typeof(obj) != "object") || (obj == null)) return false;
        if (typeof(obj.to) == "object") {
            val = obj.to.value.replace(re, "");
            if (val == "") {
                alert(MSG_TO);
                obj.to.focus();
                return false;
            }
        }
        re = /<[^<>]+>|[\s]+/ig;
        val = obj.content.value.replace(re, "");
        if ((obj.subject.value == "") && (val == "")) {
            if (!confirm(MSG_DATA_EMPTY)) return false;
        }
        remove_unload();
        xajax_clean_temp('{$sysSession->cur_func}{$st_serial}');
        obj.submit();
    }

    function remove_unload() {
        window.onunload = function () {};
    }

    function winColse() {
        var obj = null;
        obj = getTarget();
        if (obj != null) obj.location.replace("about:blank");
    }

    function getTarget() {
        var obj = null;
        switch (this.name) {
            case "s_main": obj = parent.s_catalog; break;
            case "c_main": obj = parent.c_catalog; break;
            case "main"  : obj = parent.catalog;   break;
            case "s_catalog": obj = parent.s_main; break;
            case "c_catalog": obj = parent.c_main; break;
            case "catalog"  : obj = parent.main;   break;
        }
        return obj;
    }

    window.onload = function () {
        var obj = document.getElementById("post1");
        if (obj != null) {
            if (typeof(obj.to) == "object") {
                obj.to.focus();
            } else {
                obj.subject.focus();
            }
        }
BOF;
    
$js .= <<< BOF
    };

    window.onunload = winColse;
BOF;


    // 開始呈現 HTML
    ob_start();
    $xajax_save_temp->printJavascript('/lib/xajax/');
    $tmpHtml = ob_get_contents();
    ob_end_clean();
    $smarty->assign('inlineJS', $js);
    $smarty->assign('inlineXajaxJS', $tmpHtml);

    $smarty->assign('refw', isset($refw)?$refw:'');
    $smarty->assign('isCommUse', $isCommUse);
    

    // 顯示資料夾選項
    if ($isCommUse) {
        ob_start();
        showXHTML_input('select', 'folder', getFolderArray(), '', 'class="cssInput"');
        $tmpHtml = ob_get_contents();
        ob_end_clean();
        $smarty->assign('folderHtml', $tmpHtml);
    }

    //優先權
    ob_start();
        if (isset($msg_id)) showXHTML_input('hidden', 'serial', $msg_id, '', '');
        $ary = array(
            '-2' => $MSG['priority_lowest'][$sysSession->lang],
            '-1' => $MSG['priority_low'][$sysSession->lang],
            '0'  => $MSG['priority_normal'][$sysSession->lang],
            '1'  => $MSG['priority_high'][$sysSession->lang],
            '2'  => $MSG['priority_highest'][$sysSession->lang]
        );
        showXHTML_input('select', 'priority', $ary, '0', 'class="cssInput"');
    $tmpHtml = ob_get_contents();
    ob_end_clean();
    $smarty->assign('priorityHtml', $tmpHtml);

    //主旨
    $smarty->assign('subject', $subject);

    //內容編輯
    ob_start();
        $oEditor = new wmEditor;
        $oEditor->setValue(stripslashes($content));
        $oEditor->addContType('isHTML', 1);
        $oEditor->generate('content');
        $tmpHtml = ob_get_contents();
    ob_end_clean();
    $smarty->assign('contentEditor', $tmpHtml);

    //簽名
    ob_start();
    showXHTML_input('select', 'tagline', $tagline, '', 'class="cssInput"');
    $tmpHtml = ob_get_contents();
    ob_end_clean();
    $smarty->assign('taglineHtml', $tmpHtml);

    // 顯示舊的夾檔 (Begin)
    if (($title == 'forward') || ($title == 'modify')) {
        $attach = explode("\t", $attachment);
        $tmp = gen_msg_attach_link($attachment);
        $ath = explode('<br />', $tmp);
        if ((count($attach) == 1) && empty($attach[0])) $attach = array();
        for ($i = 0, $j = 0; $i < count($attach); $i = $i + 2, $j++) {
            ob_start();
            showXHTML_input('button', '', $MSG['write_delete'][$sysSession->lang], '', 'class="cssBtn" onclick="del_attach(this, \'' . $attach[$i + 1] . '\')"');
            echo $ath[$j];
            $tmpHtml = ob_get_contents();
            ob_end_clean();
            $smarty->assign('delAttachHtml', $tmpHtml);
        }
    }
    // 顯示舊的夾檔 (End)

    $msgAry = array('%MIN_SIZE%'    =>     '<span style="color: red; font-weight:bold">' . ini_get('upload_max_filesize') . '</span>',
                     '%MAX_SIZE%'    =>    '<span style="color: red; font-weight:bold">' . ini_get('post_max_size') . '</span>'
                    );
    $smarty->assign('write_attachment_msg', strtr($MSG['write_attachment_msg'][$sysSession->lang], $msgAry));


    // assign templates vars
    // $smarty->assign('inlineJS', $js);
    $smarty->assign('title', $title);
    $smarty->assign('jsVar_st_id', "{$sysSession->cur_func}{$st_serial}");
    
    // output
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('message/write.tpl');
    $smarty->display('common/tiny_footer.tpl');
?>
