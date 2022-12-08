<?php
    /**
     * 寄信
     *
     * @since   2004/06/25
     * @author  ShenTing Lin
     * @version $Id: wm_mails.php,v 1.1 2010/02/24 02:39:34 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/file_api.php');
    require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
    require_once(sysDocumentRoot . '/lib/editor.php');
    require_once(sysDocumentRoot . '/lib/lstable.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/lang/wm_mails.php');
    require_once(sysDocumentRoot . '/message/lib.php');
    require_once(sysDocumentRoot . '/message/collect.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');

    /**
     * 處理資料，過長的部份隱藏
     * @param integer $width   : 要顯示的寬度
     * @param string  $caption : 顯示的文字
     * @param string  $title   : 浮動的提示文字，若沒有設定，則跟 $caption 依樣
     * @return string : 處理後的文字
     **/
    function meDivMsg($width=100, $caption='&nbsp;', $title='') {
        if (empty($title)) $title = $caption;
        return '<div style="width: ' . $width . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>';
    }

    function showAdvanceReciver($col, $head, $data, $note) {
        global $sysSession, $MSG;

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
            showXHTML_td('align="right" valign="top" nowrap="nowrap"', $head);
            showXHTML_td_B('nowrap="nowrap"');
                $user = parseTo($data);
                if (count($user) > 0) {
                    echo '<div style="width: 455px; height: 85px; overflow: auto;">';
                    showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="0" id="userTable"');
                        $cols = ($cols == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        showXHTML_tr_B($cols);
                        for ($i = 0; $i < count($user); $i++) {
                            if (($i > 0) && (($i % 2) == 0)) {
                                $cols = ($cols == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                                showXHTML_tr_E();
                                showXHTML_tr_B($cols);
                            }
                            $val = $user[$i];
                            $p = getUserDetailData($val);
                            showXHTML_td_B('nowrap');
                                $alt  = htmlspecialchars("{$val} ({$p['realname']})");
                                $txt  = '<input type="checkbox" name="user[]" value="' . $val . '" id="user_' . $val . '" checked="checked" >';
                                $txt .= '<label for="user_' . $val . '">' . $alt . '</label>';
                                echo meDivMsg(200, $txt, $alt);
                            showXHTML_td_E();
                        }
                        if (($i % 2) != 0) showXHTML_td('', '&nbsp;');
                        showXHTML_tr_E();
                    showXHTML_table_E();
                    echo '</div>';
                }

                echo $MSG['me_mailto_other_user'][$sysSession->lang];
                showXHTML_input('text', 'to', '', '', 'class="cssInput" size="64"');
            showXHTML_td_E('');
            showXHTML_td('valign="top"', $note);
        showXHTML_tr_E('');
    }
    
    function showWm5AdvanceReciver($col, $head, $data, $note) {
        global $sysSession, $MSG;
        
        $reciver = '';
        $reciver .= sprintf('<div class="key layout-child">%s</div>',$head);
        $user = parseTo($data);
        if (count($user) > 0) {
            $txt = '';
            for ($i = 0; $i < count($user); $i++) {
                $val = $user[$i];
                $p = getUserDetailData($val);
                $alt  = htmlspecialchars("{$val} ({$p['realname']})");
                $txt  .= '<input type="checkbox" name="user[]" value="' . $val . '" id="user_' . $val . '" checked="checked" >';
                $txt .= '<label for="user_' . $val . '">' . $alt . '</label><BR />';
            }
        }
        $reciver .= sprintf('<div class="value layout-child">%s%s<input type="text" name="to" value="" size="64"></div>',$txt, $MSG['me_mailto_other_user'][$sysSession->lang]);
        $reciver .= sprintf('<div class="comment layout-child">%s</div>',$note);
        return $reciver;
    }
    

    class wmMailWritor {
        var $head;       // HTML 的 Head 文字
        var $title;      // 標題文字
        var $sender;     // 寄件者
        var $reciver;    // 收件者
        var $priority;   // 優先順序
        var $subject;    // 標題
        var $content;    // 內容
        var $isHTML;     // 內容是否為 HTML
        var $tagline;    // 簽名檔
        var $send_method;// 傳送方式
        var $maxFiles;   // 設定夾檔最高數量
        var $memsg;      // 訊息
        var $layout;     // 顯示設定
        var $user_js;    // 使用者自訂的函式
        var $user_func;  // 使用者自訂的顯示函式
        var $uri_parent; // 來源 URL
        var $uri_target; // 目的 URL
        var $form_id;    // 表單的 id
        var $form_extra; // 表單的其他資料

        function wmMailWritor() {
            global $sysSession, $MSG;
            $this->head        = '';
            $this->title       = '';

            $this->sender      = "{$sysSession->username} ({$sysSession->realname})";
            $this->reciver     = '';
            $this->priority    = '0';
            $this->subject     = '';
            $this->content     = '';
            $this->tagline     = -1;
            $this->send_method = 'default';
            $this->isHTML      = true;

            // $this->language = '';
            $this->user_js     = array();
            $this->user_func   = array(
                'sender'      => '',
                'reciver'     => 'showAdvanceReciver',
                'priority'    => '',
                'subject'     => '',
                'content'     => '',
                'send_method' => '',
                'tagline'     => '',
                'toolbar'     => ''
            );

            $this->maxFiles    = 10;

            $this->uri_parent  = '';
            $this->uri_target  = '';
            $this->form_id     = 'mailpost';
            $this->form_extra  = '';

            $this->memsg       = array(
                'reciver_empty'       => $MSG['me_reciver_empty'][$sysSession->lang],
                'file_min'            => $MSG['me_file_min'][$sysSession->lang],
                'file_max'            => $MSG['me_file_max'][$sysSession->lang],
                'send_th'             => $MSG['me_send_th'][$sysSession->lang],
                'send_msg'            => '',
                'recive_th'           => $MSG['me_recive_th'][$sysSession->lang],
                'recive_msg'          => $MSG['me_recive_msg'][$sysSession->lang],
                'priority_th'         => $MSG['me_priority_th'][$sysSession->lang],
                'priority_msg'        => '',
                'subject_th'          => $MSG['me_subject_th'][$sysSession->lang],
                'subject_msg'         => $MSG['me_subject_msg'][$sysSession->lang],
                'content_th'          => $MSG['me_content_th'][$sysSession->lang],
                'content_msg'         => '&nbsp;',
                'not_use_tagline'     => $MSG['me_not_use_tagline'][$sysSession->lang],
                'mail_method_email'   => 'E-mail',
                'mail_method_message' => $MSG['me_mail_method_message'][$sysSession->lang],
                'mail_method_both'    => $MSG['me_mail_method_both'][$sysSession->lang],
                'send_method_th'      => $MSG['me_send_method_th'][$sysSession->lang],
                'send_method_msg'     => $MSG['me_send_method_msg'][$sysSession->lang],
                'tagline_th'          => $MSG['me_tagline_th'][$sysSession->lang],
                'tagline_msg'         => $MSG['me_tagline_msg'][$sysSession->lang],
                'attachement_th'      => $MSG['me_attachement_th'][$sysSession->lang],
                'attachement_msg'     => $MSG['me_attachement_msg'][$sysSession->lang],
                'priority_lowest'     => $MSG['me_priority_lowest'][$sysSession->lang],
                'priority_low'        => $MSG['me_priority_low'][$sysSession->lang],
                'priority_normal'     => $MSG['me_priority_normal'][$sysSession->lang],
                'priority_high'       => $MSG['me_priority_high'][$sysSession->lang],
                'priority_highest'    => $MSG['me_priority_highest'][$sysSession->lang],
                'btn_submit'          => $MSG['me_btn_submit'][$sysSession->lang],
                'btn_more_attache'    => $MSG['me_btn_more_attache'][$sysSession->lang],
                'btn_del_attache'     => $MSG['me_btn_del_attache'][$sysSession->lang],
                'use_tagline'          => $MSG['me_use_tagline'][$sysSession->lang],
            );

            $this->layout      = array(
                'showReciver'    => 'none',      // visible: 顯示，hidden: 隱藏，none: 不處理
                'showSendMethod' => 'none',      // visible: 顯示，hidden: 隱藏，none: 不處理
                'showTagline'    => 'visible',   // visible: 顯示，hidden: 隱藏，none: 不處理
                'showOldAttache' => 'none',
            );
        }


        function add_script($type, $content, $extra=true) {
            $this->user_js[] = array($type, $content, $extra);
        }

        function gen_js() {
            $js = <<< BOF
    var meAttacheFiles = 1;
    var meCol = '';
    var MSG_ME_TO       = "{$this->memsg['reciver_empty']}";
    var MSG_ME_FILE_MIN = "{$this->memsg['file_min']}";
    var MSG_ME_FILE_MAX = "{$this->memsg['file_max']}";

    function trim(val) {
        var re = /\s/g;
        val = val.replace(re, '');
        return val;
    }
// ////////////////////////////////////////////////////////////////////////////
    /**
     * Add a attachement
     **/
    function more_attachs(){
        if (meAttacheFiles >= {$this->maxFiles}){
            alert(MSG_ME_FILE_MAX);
            return;
        }
        var curNode = document.getElementById('upload_box');
        var nxtNode = document.getElementById('upload_base');
        var newNode = curNode.cloneNode(true);
        if (meCol == '') meCol = curNode.className;
        meCol = (meCol == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
        newNode.className = meCol;
        curNode.parentNode.insertBefore(newNode, nxtNode);
        newNode.getElementsByTagName("input")[0].value = "";
        meAttacheFiles++;
    }

    /**
     * delete a attachment
     **/
    function cut_attachs(){
        var curNode = document.getElementById('upload_base');
        var delNode = curNode.previousSibling;

        if (meAttacheFiles <= 1){

            /*#47206 chrome 修正縮減附檔失敗*/
            /*#47371 [教師/人員管理/到課統計/寄信給本頁系選人員] 只有挑選一個檔時，按下「縮減附檔」，不會把欄位清空。*/
            /*47462 [Safari][管理者/公告與聯繫/寄給群組] 只有一組上傳欄位時，選擇一個檔案，下「縮減附檔」不會把上傳欄位清空。*/
            var browser = 'ie';
            if(navigator.userAgent.indexOf('MSIE')>0){
                browser = 'ie';
            }else if(navigator.userAgent.indexOf('Firefox')>0){
                browser = 'ff';
            }else if(navigator.userAgent.indexOf('Chrome')>0){
                browser = 'chr';
            }else if(navigator.userAgent.indexOf('Safari')>0){
                browser = 'sf';
            }else{
                browser = 'op';
            }
        
            if(browser == 'chr' || browser == 'sf' || detectIE() === 11 || browser === 'ff') {
                var curNode = document.getElementById('uploads[]');
                curNode.value = '';
            } else {
                var newNode = delNode.cloneNode(true);    // 若原本有選定檔案則清空
                delNode.parentNode.replaceChild(newNode, delNode);
            }

            return;
        }

        delNode.parentNode.removeChild(delNode);
        meCol = (meCol == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
        meAttacheFiles--;
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
// ////////////////////////////////////////////////////////////////////////////
    function mefm_ckfunc() {
        var obj = document.getElementById("{$this->form_id}");
        var re = /\s/;
        var val = "";
        if ((typeof(obj) != "object") || (obj == null)) return false;
        if ((typeof(obj.to) == "object") && (obj.to.type == "text")) {
            val = obj.to.value.replace(re, '');
            if (val == "") {
                alert(MSG_ME_TO);
                obj.to.focus();
                return false;
            }
        }
        return true;
    }

    /**
     * 檢查信件是否有填寫主旨與內容
     * @return boolean 是否都有填寫
     */
    function chkMailData()
    {
        var mail_content, tmp_content = editor.getHTML();
        // 去掉 HTML 中的 tag (begin)
           do
        {
            mail_content = tmp_content;
            tmp_content = tmp_content.replace(/<(\w+)\b[^>]*>(.*?)<\/\1>/, '$2');
        }
        while(tmp_content != mail_content);
        mail_content = tmp_content.replace(/<[^>]+>|^\s+|\s+$/g, '');
        // 去掉 HTML 中的 tag (end)

        var obj = document.getElementById("{$this->form_id}");
        if (trim(obj.subject.value) == '' || trim(mail_content) == '' || mail_content == '&nbsp;')
            return false;

        return true;
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
    };
BOF;
            return $js;
        }

        function generate() {
            global $sysSession;

            showXHTML_head_B($this->head);
            showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
            showXHTML_script('include', '/message/hotkey.js');
            $js = $this->gen_js();
            showXHTML_script('include', '/public/js/common.js');
            showXHTML_script('inline' , $js);
            foreach ($this->user_js as $val) {
                showXHTML_script($val[0] , $val[1], $val[2]);
            }

            showXHTML_body_B('');
                $ary = array();
                $ary[] = array($this->title, 'tabs1');
                if (empty($this->form_extra)) {
                    $this->form_extra = 'method="post" action="' . $this->uri_target . '" enctype="multipart/form-data" onsubmit="return mefm_ckfunc(this);"';
                } else {
                    $this->form_extra .= ' action="' . $this->uri_target . '"';
                }
                echo '<div align="center">';
                showXHTML_tabFrame_B($ary, 1, $this->form_id, '', $this->form_extra, false);
                    showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="tabs1"');
                        // 寄件者 (Begin)
                            // 呼叫使用者自訂的函式
                        if (!empty($this->user_func['sender']) && function_exists($this->user_func['sender'])) {
                            echo call_user_func_array($this->user_func['sender'], array(&$col, $this->memsg['send_th'], $this->sender, $this->memsg['send_msg']));
                        }
                            // 預設的寄件者
                        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        showXHTML_tr_B($col);
                            showXHTML_td('align="right" valign="top" nowrap="nowrap"', $this->memsg['send_th']);
                            showXHTML_td('', $this->sender);
                            showXHTML_td('valign="top"', $this->memsg['send_msg']);
                        showXHTML_tr_E('');
                        // 寄件者 (End)
                        // 收件者 (Begin)
                            // 呼叫使用者自訂的函式
                        if (!empty($this->user_func['reciver']) && function_exists($this->user_func['reciver'])) {
                            echo call_user_func_array($this->user_func['reciver'], array(&$col, $this->memsg['recive_th'], $this->reciver, $this->memsg['recive_msg']));
                        }
                            // 預設的收件者
                        if ($this->layout['showReciver'] == 'visible') {
                            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                            showXHTML_tr_B($col);
                                showXHTML_td('align="right" valign="top" nowrap="nowrap"', $this->memsg['recive_th']);
                                showXHTML_td_B('nowrap="nowrap"');
                                    showXHTML_input('text', 'to', $this->reciver, '', 'class="cssInput" size="64"');
                                showXHTML_td_E('');
                                showXHTML_td('valign="top"', $this->memsg['recive_msg']);
                            showXHTML_tr_E('');
                        } else if ($this->layout['showReciver'] == 'hidden') {
                            showXHTML_tr_B('style="display: none;"');
                                showXHTML_td_B('colspan="3"');
                                    showXHTML_input('hidden', 'to', $this->reciver, '', 'class="cssInput"');
                                showXHTML_td_E('');
                            showXHTML_tr_E('');
                        }
                        // 收件者 (End)
                        // 優先順序
                            // 呼叫使用者自訂的函式
                        if (!empty($this->user_func['priority']) && function_exists($this->user_func['priority'])) {
                            echo call_user_func_array($this->user_func['priority'], array(&$col, $this->memsg['priority_th'], $this->priority, $this->memsg['priority_msg']));
                        }
                        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        showXHTML_tr_B($col);
                            showXHTML_td('align="right" valign="top" nowrap="nowrap"', $this->memsg['priority_th']);
                            showXHTML_td_B('nowrap="nowrap"');
                                $ary = array(
                                    '-2' => $this->memsg['priority_lowest'],
                                    '-1' => $this->memsg['priority_low'],
                                    '0'  => $this->memsg['priority_normal'],
                                    '1'  => $this->memsg['priority_high'],
                                    '2'  => $this->memsg['priority_highest']
                                );
                                showXHTML_input('select', 'priority', $ary, $this->priority, 'class="cssInput"');
                            showXHTML_td_E('');
                            showXHTML_td('valign="top"', $this->memsg['priority_msg']);
                        showXHTML_tr_E('');
                        // 主旨
                            // 呼叫使用者自訂的函式
                        if (!empty($this->user_func['subject']) && function_exists($this->user_func['subject'])) {
                            echo call_user_func_array($this->user_func['subject'], array(&$col, $this->memsg['subject_th'], $this->subject, $this->memsg['subject_msg']));
                        }
                        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        showXHTML_tr_B($col);
                            showXHTML_td('align="right" valign="top" nowrap="nowrap"', $this->memsg['subject_th']);
                            showXHTML_td_B('nowrap="nowrap"');
                                showXHTML_input('text', 'subject', $this->subject, '', 'class="cssInput" size="64" maxlength="200"');
                            showXHTML_td_E('');
                            showXHTML_td('valign="top"', $this->memsg['subject_msg']);
                        showXHTML_tr_E('');
                        // 內容
                            // 呼叫使用者自訂的函式
                        if (!empty($this->user_func['content']) && function_exists($this->user_func['content'])) {
                            echo call_user_func_array($this->user_func['content'], array(&$col, $this->memsg['content_th'], $this->content, $this->memsg['content_msg']));
                        }
                        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        showXHTML_tr_B($col);
                            showXHTML_td('align="right" valign="top" nowrap="nowrap"', $this->memsg['content_th']);
                            showXHTML_td_B('nowrap="nowrap"');
                                $oEditor = new wmEditor;
                                $oEditor->setValue(stripslashes($this->content));
                                $oEditor->addContType('isHTML', 1);
                                $oEditor->generate('content');
                            showXHTML_td_E('');
                            showXHTML_td('valign="top"', $this->memsg['content_msg']);
                        showXHTML_tr_E('');
                        // 傳送方式
                            // 呼叫使用者自訂的函式
                        if (!empty($this->user_func['send_method']) && function_exists($this->user_func['send_method'])) {
                            echo call_user_func_array($this->user_func['send_method'], array(&$col, $this->memsg['send_method_th'], $this->tagline, $this->memsg['send_method_msg']));
                        }
                        if ($this->layout['showSendMethod'] == 'visible') {
                            $ary = array(
                                'email'   => $this->memsg['mail_method_email'],
                                'message' => $this->memsg['mail_method_message'],
                                'both'    => $this->memsg['mail_method_both']
                            );
                            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                            showXHTML_tr_B($col);
                                showXHTML_td('align="right" valign="top" nowrap="nowrap"', $this->memsg['send_method_th']);
                                showXHTML_td_B('nowrap="nowrap"');
                                    showXHTML_input('radio', 'method', $ary, $this->send_method, '', '<br />');
                                showXHTML_td_E('');
                                showXHTML_td('valign="top"', $this->memsg['send_method_msg']);
                            showXHTML_tr_E('');
                        } else if ($this->layout['showTagline'] == 'hidden') {
                            showXHTML_tr_B('style="display: none;"');
                                showXHTML_td_B('colspan="3"');
                                    showXHTML_input('hidden', 'method', $this->send_method, '', '');
                                showXHTML_td_E('');
                            showXHTML_tr_E('');
                        }
                        // 簽名檔
                            // 呼叫使用者自訂的函式
                        if (!empty($this->user_func['tagline']) && function_exists($this->user_func['tagline'])) {
                            echo call_user_func_array($this->user_func['tagline'], array(&$col, $this->memsg['tagline_th'], $this->tagline, $this->memsg['tagline_msg']));
                        }

                        if ($this->layout['showTagline'] == 'visible') {
                            $RS = dbGetStMr('WM_user_tagline', '`serial`, `title`, `ctype`', "username='{$sysSession->username}' LIMIT 0,1", ADODB_FETCH_ASSOC);
                            // $RS = dbGetStMr('WM_user_tagline', '`serial`, `title`, `ctype`', "username='{$sysSession->username}'", ADODB_FETCH_ASSOC);
                            $tagline = array();
                            $tagline[-1] = $this->memsg['not_use_tagline'];
                            if ($RS) {
                                while (!$RS->EOF) {
                                    $tagline[$RS->fields['serial']] = $this->memsg['use_tagline'];
                                    // $tagline[$RS->fields['serial']] = $RS->fields['title'];
                                    $RS->MoveNext();
                                }
                            }
                            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                            showXHTML_tr_B($col);
                                showXHTML_td('align="right" valign="top" nowrap="nowrap"', $this->memsg['tagline_th']);
                                showXHTML_td_B('nowrap="nowrap"');
                                    showXHTML_input('select', 'tagline', $tagline, $this->tagline, 'class="cssInput"');
                                showXHTML_td_E('');
                                showXHTML_td('valign="top"', $this->memsg['tagline_msg']);
                            showXHTML_tr_E('');
                        } else if ($this->layout['showTagline'] == 'hidden') {
                            showXHTML_tr_B('style="display: none;"');
                                showXHTML_td_B('colspan="3"');
                                    showXHTML_input('hidden', 'tagline', $this->tagline, '', '');
                                showXHTML_td_E('');
                            showXHTML_tr_E('');
                        }
                        // 顯示舊的夾檔 (Begin)
                        /*
                        if (($title == 'forward') || ($title == 'modify')) {
                            $attach = explode("\t", $attachment);
                            $tmp = gen_msg_attach_link($attachment);
                            $ath = explode('<br />', $tmp);
                            if ((count($attach) == 1) && empty($attach[0])) $attach = array();
                            for ($i = 0, $j = 0; $i < count($attach); $i = $i + 2, $j++) {
                                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                                showXHTML_tr_B($col);
                                    showXHTML_td('align="right" nowrap="nowrap"', $MSG['write_attachement'][$sysSession->lang]);
                                    showXHTML_td_B('nowrap="nowrap"');
                                        showXHTML_input('button', '', $MSG['write_delete'][$sysSession->lang], '', 'class="button01" onclick="del_attach(this, \'' . $attach[$i + 1] . '\')"');
                                        echo $ath[$j];
                                    showXHTML_td_E('');
                                    showXHTML_td('class="font06"', $MSG['write_delete_msg'][$sysSession->lang]);
                                showXHTML_tr_E('');
                            }
                        }
                        */
                        // 顯示舊的夾檔 (End)
                        // 附件
                        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        showXHTML_tr_B($col . ' id="upload_box"');
                            showXHTML_td('align="right" valign="top" nowrap="nowrap"', $this->memsg['attachement_th']);
                            showXHTML_td_B('nowrap="nowrap"');
                                showXHTML_input('file', '', '', '', 'class="cssInput" size="60"');
                            showXHTML_td_E('');
                            $msgAry = array('%MIN_SIZE%'    =>     '<span style="color: red; font-weight:bold">' . ini_get('upload_max_filesize') . '</span>',
                                             '%MAX_SIZE%'    =>    '<span style="color: red; font-weight:bold">' . ini_get('post_max_size') . '</span>'
                                            );
                            showXHTML_td('valign="top"', strtr($this->memsg['attachement_msg'], $msgAry));
                        showXHTML_tr_E('');
                        // 按鈕
                        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        showXHTML_tr_B($col . ' id="upload_base"');
                            showXHTML_td_B('nowrap="nowrap" colspan="3"');
                                showXHTML_input('submit', '', $this->memsg['btn_submit']      , '', 'class="cssBtn"');
                                showXHTML_input('button', '', $this->memsg['btn_more_attache'], '', 'class="cssBtn" onclick="more_attachs();"');
                                showXHTML_input('button', '', $this->memsg['btn_del_attache'] , '', 'class="cssBtn" onclick="cut_attachs();" data-file="/lib/wm_mails"');
                            showXHTML_td_E('');
                        showXHTML_tr_E('');
                    showXHTML_table_E('');
                showXHTML_tabFrame_E();
                echo '</div>';
            showXHTML_body_E('');
        }
    }

    /**
     * 
     * @author jeff
     * wmpro5 寄信共用介面
     *
     */
    class wm5MailWritor extends wmMailWritor {
        function wm5MailWritor() {
            wmMailWritor::wmMailWritor();
            $this->user_func['reciver'] = 'showWm5AdvanceReciver';
        }
        
        function gen_js() {
            $js = <<< BOF
    var meAttacheFiles = 1;
    var meCol = '';
    var MSG_ME_TO       = "{$this->memsg['reciver_empty']}";
    var MSG_ME_FILE_MIN = "{$this->memsg['file_min']}";
    var MSG_ME_FILE_MAX = "{$this->memsg['file_max']}";
        
    function trim(val) {
        var re = /\s/g;
        val = val.replace(re, '');
        return val;
    }
// ////////////////////////////////////////////////////////////////////////////
    /**
     * Add a attachement
     **/
    function more_attachs(){
       if (meAttacheFiles >= {$this->maxFiles}) {
            alert(MSG_ME_FILE_MAX);
            return;
        }
        var curNode = document.getElementById('upload_box');
        var newNode = curNode.cloneNode(true);
        curNode.parentNode.appendChild(newNode);
        newNode.getElementsByTagName("input")[0].value = "";
        meAttacheFiles++;
    }
        
    /**
     * delete a attachment
     **/
    function cut_attachs(){
       var curNode = document.getElementById('upload_box');
        if (meAttacheFiles <= 1) {
            curNode.getElementsByTagName("input")[0].value = "";
            return;
        }
        var idx = curNode.parentNode.childNodes.length-1;
        curNode.parentNode.removeChild(curNode.parentNode.childNodes[idx]);
        meAttacheFiles--;
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
// ////////////////////////////////////////////////////////////////////////////
    function mefm_ckfunc() {
        var obj = document.getElementById("{$this->form_id}");
        var re = /\s/;
        var val = "";
        if ((typeof(obj) != "object") || (obj == null)) return false;
        if ((typeof(obj.to) == "object") && (obj.to.type == "text")) {
            val = obj.to.value.replace(re, '');
            if (val == "") {
                alert(MSG_ME_TO);
                obj.to.focus();
                return false;
            }
        }
        return true;
    }
        
    /**
     * 檢查信件是否有填寫主旨與內容
     * @return boolean 是否都有填寫
     */
    function chkMailData()
    {
        var mail_content, tmp_content = editor.getHTML();
        // 去掉 HTML 中的 tag (begin)
           do
        {
            mail_content = tmp_content;
            tmp_content = tmp_content.replace(/<(\w+)\b[^>]*>(.*?)<\/\1>/, '$2');
        }
        while(tmp_content != mail_content);
        mail_content = tmp_content.replace(/<[^>]+>|^\s+|\s+$/g, '');
        // 去掉 HTML 中的 tag (end)
        
        var obj = document.getElementById("{$this->form_id}");
        if (trim(obj.subject.value) == '' || trim(mail_content) == '' || mail_content == '&nbsp;')
            return false;
        
        return true;
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
    };
BOF;
            return $js;
        }
        
         
        
        function generate() {
            global $sysSession, $smarty;

            // javascript
            ob_start();
            $js = $this->gen_js();
            showXHTML_script('include', '/public/js/common.js');
            showXHTML_script('inline' , $js);
            foreach ($this->user_js as $val) {
                showXHTML_script($val[0] , $val[1], $val[2]);
            }
            $scr = ob_get_contents();
            ob_end_clean();
            $smarty->assign('inlineJS', $scr);
            
            // 寄信表單屬性
            if (empty($this->form_extra)) {
                $this->form_extra = 'method="post" action="' . $this->uri_target . '" enctype="multipart/form-data" onsubmit="return mefm_ckfunc(this);"';
            } else {
                $this->form_extra .= ' action="' . $this->uri_target . '"';
            }
            $smarty->assign('mailFormId', $this->form_id);
            $smarty->assign('mailFormExtra', $this->form_extra);

            // 寄件者 (Begin)
            // 呼叫使用者自訂的函式
            $sender = '';
            if (!empty($this->user_func['sender']) && function_exists($this->user_func['sender'])) {
                $sender .= call_user_func_array($this->user_func['sender'], array(&$col, $this->memsg['send_th'], $this->sender, $this->memsg['send_msg']));
            }
            
            // 預設的寄件者            
            $sender .= sprintf('<div class="key layout-child">%s</div>',$this->memsg['send_th']);
            $sender .= sprintf('<div class="value layout-child">%s</div>',$this->sender);
            $sender .= sprintf('<div class="comment layout-child">%s</div>',$this->memsg['send_msg']);
            $smarty->assign('senderInfo', $sender);
            // 寄件者 (End)
            
            // 收件者 (Begin)
            $reciver = '';
            // 呼叫使用者自訂的函式
            if (!empty($this->user_func['reciver']) && function_exists($this->user_func['reciver'])) {
                $reciver .= call_user_func_array($this->user_func['reciver'], array(&$col, $this->memsg['recive_th'], $this->reciver, $this->memsg['recive_msg']));
            }
            // 預設的收件者
            if ($this->layout['showReciver'] == 'visible') {
                $reciver .= sprintf('<div class="key layout-child">%s</div>',$this->memsg['recive_th']);
                $reciver .= sprintf('<div class="value layout-child"><input type="text" name="to" value="%s" size="64"></div>',$this->reciver);
                $reciver .= sprintf('<div class="comment layout-child">%s</div>',$this->memsg['recive_msg']);
            } else if ($this->layout['showReciver'] == 'hidden') {
                $reciver .= sprintf('<div class="key layout-child">%s</div>',$this->memsg['recive_th']);
                $reciver .= sprintf('<div class="value layout-child"><input type="hidden" name="to" value="%s"></div>',$this->reciver);
                $reciver .= '<div class="comment layout-child"></div>';
            }

            $smarty->assign('reciverInfo', $reciver);
            // 收件者 (End)
            
            // 優先順序
            $priority = ''; 
            // 呼叫使用者自訂的函式
            if (!empty($this->user_func['priority']) && function_exists($this->user_func['priority'])) {
                $priority .= call_user_func_array($this->user_func['priority'], array(&$col, $this->memsg['priority_th'], $this->priority, $this->memsg['priority_msg']));
            }
            
            ob_start();
            $ary = array(
                '-2' => $this->memsg['priority_lowest'],
                '-1' => $this->memsg['priority_low'],
                '0'  => $this->memsg['priority_normal'],
                '1'  => $this->memsg['priority_high'],
                '2'  => $this->memsg['priority_highest']
            );
            showXHTML_input('select', 'priority', $ary, $this->priority, 'class="cssInput"');
            $selPriority = ob_get_contents();
            ob_end_clean();
            
            $priority .= sprintf('<div class="key layout-child">%s</div>',$this->memsg['priority_th']);
            $priority .= sprintf('<div class="value layout-child">%s</div>',$selPriority);
            $priority .= sprintf('<div class="comment layout-child">%s</div>',$this->memsg['priority_msg']);
            $smarty->assign('priorityInfo', $priority);
            
            // 主旨
            $subject = '';
            // 呼叫使用者自訂的函式
            if (!empty($this->user_func['subject']) && function_exists($this->user_func['subject'])) {
                $subject .= call_user_func_array($this->user_func['subject'], array(&$col, $this->memsg['subject_th'], $this->subject, $this->memsg['subject_msg']));
            }
            $subject .= sprintf('<div class="key layout-child">%s</div>',$this->memsg['subject_th']);
            $subject .= sprintf('<div class="value layout-child"><input type="text" name="subject" value="%s" size="64"  maxlength="200"></div>',$this->subject);
            $subject .= sprintf('<div class="comment layout-child">%s</div>',$this->memsg['subject_msg']);
            $smarty->assign('subjectInfo', $subject);

            //內容編輯
            ob_start();
            $oEditor = new wmEditor;
            $oEditor->setValue(stripslashes($this->content));
            $oEditor->addContType('isHTML', 1);
            $oEditor->generate('content');
            $tmpHtml = ob_get_contents();
            ob_end_clean();
            $smarty->assign('contentEditor', $tmpHtml);
            
            // 傳送方式
            $method = '';
            // 呼叫使用者自訂的函式
            if (!empty($this->user_func['send_method']) && function_exists($this->user_func['send_method'])) {
                $method .= call_user_func_array($this->user_func['send_method'], array(&$col, $this->memsg['send_method_th'], $this->tagline, $this->memsg['send_method_msg']));
            }
            if ($this->layout['showSendMethod'] == 'visible') {
                $ary = array(
                    'email'   => $this->memsg['mail_method_email'],
                    'message' => $this->memsg['mail_method_message'],
                    'both'    => $this->memsg['mail_method_both']
                );
                
                $method .= sprintf('<div class="key layout-child">%s</div>',$this->memsg['send_method_th']);
                $method .= '<div class="value layout-child">';
                foreach($ary as $k => $v) {
                    $method .= sprintf('<input type="radio" name="method" value="%s" %s />%s<br />',$k,(($k==$this->send_method)?'checked':''),$v);
                }
                $method .= '</div>';
                $method .= sprintf('<div class="comment layout-child">%s</div>',$this->memsg['send_method_msg']);
            } else if ($this->layout['showSendMethod'] == 'hidden') {
                $method = '<input type="hidden" name="method" value="'.$this->send_method.'" />';
            }
            $smarty->assign('showSendMethod', $this->layout['showSendMethod']);
            $smarty->assign('methodInfo', $method);
            
            // 簽名檔
            $taglineInfo = '';
            // 呼叫使用者自訂的函式
            if (!empty($this->user_func['tagline']) && function_exists($this->user_func['tagline'])) {
                $taglineInfo .= call_user_func_array($this->user_func['tagline'], array(&$col, $this->memsg['tagline_th'], $this->tagline, $this->memsg['tagline_msg']));
            }
             
            if ($this->layout['showTagline'] == 'visible') {
                $RS = dbGetStMr('WM_user_tagline', '`serial`, `title`, `ctype`', "username='{$sysSession->username}' LIMIT 0,1", ADODB_FETCH_ASSOC);
                $tagline = array();
                $tagline[-1] = $this->memsg['not_use_tagline'];
                if ($RS) {
                    while (!$RS->EOF) {
                        $tagline[$RS->fields['serial']] = $this->memsg['use_tagline'];
                        $RS->MoveNext();
                    }
                }
                
                ob_start();
                showXHTML_input('select', 'tagline', $tagline, $this->tagline, 'class="cssInput"');
                $selTagline = ob_get_contents();
                ob_end_clean();
                 
                $taglineInfo .= sprintf('<div class="key layout-child">%s</div>',$this->memsg['tagline_th']);
                $taglineInfo .= sprintf('<div class="value layout-child">%s</div>',$selTagline);
                $taglineInfo .= sprintf('<div class="comment layout-child">%s</div>',$this->memsg['tagline_msg']);
            } else if ($this->layout['showTagline'] == 'hidden') {
                $taglineInfo = '<input type="hidden" name="tagline" value="'.$this->tagline.'" />';
            }
            $smarty->assign('showTagline', $this->layout['showTagline']);
            $smarty->assign('taglineInfo', $taglineInfo);
            
            //附檔的訊息
            $msgAry = array('%MIN_SIZE%'    =>     '<span style="color: red; font-weight:bold">' . ini_get('upload_max_filesize') . '</span>',
            '%MAX_SIZE%'    =>    '<span style="color: red; font-weight:bold">' . ini_get('post_max_size') . '</span>'
            );
            $smarty->assign('write_attachment_msg', strtr($this->memsg['attachement_msg'], $msgAry));
             
            // output
            $smarty->display('common/tiny_header.tpl');
            $smarty->display('common/mail/write.tpl');
            $smarty->display('common/tiny_footer.tpl');
        }
    }
    
    class wmMailSender {
        var $head;       // HTML 的 Head 文字
        var $title;      // 標題文字

        var $sender;     // 寄件者
        var $email;      // 寄件者的 mail
        var $reciver;    // 收件者
        var $priority;   // 優先順序
        var $subject;    // 標題
        var $content;    // 內容
        var $isHTML;     // 內容是否為 HTML
        var $tagline;    // 簽名檔

        var $toolbar;    // 工具列
        var $user_js;    // 使用者自訂的函式
        var $send_kind;  // 傳送種類
        var $use_split;  // 是否切割收件者
        var $spilt_unit; // 切割的人數
        var $send_backup;// 寄件備份
        var $fail_back;  // 假如有錯誤，是否回到上一頁
        var $default_btn;// 預設的按鈕

        var $uri_parent; // 來源 URL
        var $uri_target; // 目的 URL
        var $memsg;      // 訊息

        function wmMailSender() {
            global $sysSession, $MSG;
            $this->head        = '';
            $this->title       = '';

            $this->sender      = "{$sysSession->username} ({$sysSession->realname})";
            $this->email       = $sysSession->email;
            $this->reciver     = '';
            $this->priority    = '0';
            $this->subject     = '';
            $this->content     = '';
            $this->isHTML      = true;
            $this->tagline     = -1;

            $this->toolbar     = new toolbar();
            $this->user_js     = array();
            $this->send_kind   = 'normal';   // normal, split, notebook
            $this->use_split   = true;
            $this->spilt_unit  = 1000;
            $this->send_backup = true;
            $this->fail_back   = false;
            $this->default_btn = true;

            $this->uri_parent  = '';
            $this->uri_target  = '';
            $this->memsg    = array(
                'th_serial'      => $MSG['me_th_serial'][$sysSession->lang],
                'th_reciver'     => $MSG['me_th_reciver'][$sysSession->lang],
                'th_result'      => $MSG['me_th_result'][$sysSession->lang],
                'sended'         => $MSG['me_sended'][$sysSession->lang],
                'user_not_exist' => $MSG['me_user_not_exist'][$sysSession->lang],
                'no_self1'       => $MSG['me_no_self1'][$sysSession->lang],
                'no_self2'       => $MSG['me_no_self2'][$sysSession->lang],
                'btn_return'     => $MSG['me_btn_return'][$sysSession->lang],
            );
        }

        function send() {
            global $sysSession, $sysConn, $_SERVER;

            // 處理收件者，並且過濾重複的人員
            $reciver = parseTo($this->reciver);
            // 備註
            $tmp = implode(', ', $reciver);
            $note  = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>'; // 確保 PHP 不會辨識錯誤
            $note .= '<manifest><to>' . $tmp . '</to></manifest>';
            // 優先權
            $priority = intval($this->priority);
            // 標題不許使用 html
            $subject = htmlspecialchars($this->subject, ENT_QUOTES);
            // 內文的型態
            $type = (!$_POST['isHTML']) ? 'text' : 'html';
            // 內文去除所有的不必要 html
            $content = strip_scr($this->content);
            // 取出簽名檔
            $tagline = '';
            $this->tagline = intval($this->tagline);
            if ($this->tagline > 0) {
                list($tagline) = dbGetStSr('WM_user_tagline', 'tagline', "serial={$this->tagline} AND username='{$sysSession->username}'", ADODB_FETCH_NUM);
            }
            // 儲存夾檔。如果有的話，儲存夾檔到寄件者的目錄去
            $orgdir = MakeUserDir($sysSession->username);
            $ret = trim(save_upload_file($orgdir, 0, 0));
            $list_tmp = explode("\t", $ret);
            // 建立複製檔案的清單 (Begin)
            $file_list = array();
            for ($i = 0, $j = 0; $i < count($list_tmp); $i = $i + 2, $j++) {
                $filename = $orgdir . DIRECTORY_SEPARATOR . $list_tmp[$i + 1];
                if (!file_exists($filename) || !is_file($filename)) continue;

                $message = implode('', file($filename));
                $file_list[] = $list_tmp[$i + 1];
            }
            // 建立複製檔案的清單 (End)

            // 開始送信 (Begin)
            if ($this->send_kind == 'normal') {
                $mail = buildMail('', $subject, $content, $type, $tagline, $ret, $orgdir, $priority, false);
            }
            $no_user    = array();
            $mail_list  = '';
            $mail_count = 0;
            foreach ($reciver as $username) {
                $username = trim($username);

                // 檢查是不是 email (這個檢查很簡單，看需不需要做仔細一點的檢查)
                if (preg_match(sysMailRule, $username)) {
                    switch ($this->send_kind) {
                        case 'normal' :
                            $mail->to = $username;
                            $mail->send();
                            continue;
                            break;
                        case 'split'  :
                            $mail_list .= $username . ',';
                            $mail_count++;
                            // 以1000人為一單位 發信出去
                            if ($mail_count % $this->spilt_unit == 0) {
                                $mail_list = substr($mail_list, 0, -1);
                                dbNew('WM_mails',
                                    '`function_id`, `froms`,`tos`,`submit_time`,`send_status`',
                                    "0, '{$sysSession->username}', '{$mail_list}', NOW(), '1'"
                                );
                                $InsertID = $sysConn->Insert_ID();
                                // $content1 = '<img src="http://' . $_SERVER['HTTP_HOST'] . '/mail_count.php?mailid=' . $sysSession->school_id . '_' . $InsertID . '" style="display:none">' . $content;
                                $content1 = $content;
                                $mail = buildMail('', $subject, $content1, $type, $tagline, $ret, $orgdir, $priority, false);
                                $mail->to = $sysSession->email;    // 以寄件者為to
                                $mail->headers = 'Bcc: ' . $mail_list;
                                $mail->send();
                                dbSet('WM_mails', "`send_status`='2'", "`mail_serial`={$InsertID}");
                                $mail_list  = '';
                                $mail_count = 0;
                                continue;
                            }
                            continue;
                            break;
                        default:
                    }
                } else {
                    // 檢查這個使用者有沒有存在
                    $res = checkUsername($username);
                    $range = array(2, 4);
                    if (!in_array($res, $range)) {
                        $no_user[] = $username;
                        continue;
                    }

                    // 若有寄件備份，就不寄給自己了
                    if (($username == $sysSession->username) && $this->send_backup) {
                        $no_user[] = $username;
                        continue;
                    }

                    // 寄到訊息中心
                    collect('sys_inbox', $sysSession->username, $username, '', $subject, $content, $type, $tagline, $ret, $priority, '', $note);
                    // 存放附檔
                    if (!$isNB || $forward) {
                        $userdir = MakeUserDir($username);
                        for ($i = 0; $i < count($file_list); $i++) {
                            @copy("{$orgdir}/{$file_list[$i]}", "$userdir/{$file_list[$i]}");
                        }
                    }
                }
            } // End foreach ($reciver as $username)
            // 開始送信 (End)
            if (($this->send_kind == 'split') && ($mail_count > 0)) {
                // 剩下不到1000人的收件者,要寄信出去
                $mail_list = substr($mail_list, 0, -1);
                dbNew('WM_mails',
                    '`function_id`, `froms`,`tos`,`submit_time`,`send_status`',
                    "0, '{$sysSession->username}', '{$mail_list}', NOW(), '1'"
                );
                $InsertID = $sysConn->Insert_ID();
                // $content1 = '<img src="http://' . $_SERVER['HTTP_HOST'] . '/mail_count.php?mailid=' . $sysSession->school_id . '_' . $InsertID . '" style="display:none">' . $content;
                $content1 = $content;
                $mail = buildMail('', $subject, $content1, $type, $tagline, $ret, $orgdir, $priority, false);
                $mail->to = $sysSession->email;    // 以寄件者為to
                $mail->headers = 'Bcc: ' . $mail_list;
                $mail->send();
                dbSet('WM_mails', "`send_status`='2'", "`mail_serial`={$InsertID}");
            }

            // 寄件匣備份
            if ($this->send_backup) {
                collect('sys_sent_backup', $sysSession->username, $sysSession->username, '', $subject, $content, $type, $tagline, $ret, $priority, 'read', $note);
            }

            // 介面輸出
            $this->output($reciver, $no_user);
            
        } // End function send()
        
        /**
         * 輸出寄信的結果列表
         * @param array $reciver : 收件者
         * @param array $no_user : 收件者名單中，不存在wmpro5的使用者
         */
        function output($reciver, $no_user) {
            global $sysSession, $sysConn, $_SERVER;
            
            // 介面輸出 (Begin)
            showXHTML_head_B($this->head);
            showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
            foreach ($this->user_js as $val) {
                showXHTML_script($val[0] , $val[1], $val[2]);
            }
            showXHTML_head_E();
            showXHTML_body_B();
            $ary = array();
            $ary[] = array($this->title, 'tabs1');
            echo '<div align="center">';
            showXHTML_tabFrame_B($ary, 1); //, form_id, table_id, form_extra, isDragable);
            showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
            showXHTML_tr_B('class="cssTrHead"');
            showXHTML_td('width="40" nowrap="nowrap" align="center"' , $this->memsg['th_serial']);
            showXHTML_td('width="80" nowrap="nowrap" align="center"' , $this->memsg['th_reciver']);
            showXHTML_td('width="250" nowrap="nowrap" align="center"', $this->memsg['th_result']);
            showXHTML_tr_E('');
            
            reset($reciver);
            $i = 0;
            foreach ($reciver as $val) {
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                showXHTML_td('nowrap="nowrap" align="center"', ++$i);
                showXHTML_td('nowrap="nowrap"', $val);
                $res = in_array($val, $no_user) ? $this->memsg['user_not_exist'] : $this->memsg['sended'];
                if (($val == $sysSession->username) && $this->send_backup) {
                    // 取出使用者設定的信件匣備份的名稱
                    $name_ary = nowPos('sys_sent_backup');
                    $folder_name = end($name_ary);
                    $res = $this->memsg['no_self1'] . $folder_name . $this->memsg['no_self2'];
                }
                showXHTML_td('nowrap="nowrap"', $res);
                showXHTML_tr_E('');
            }
            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($col);
            showXHTML_td_B('colspan="3" align="center"');
            if ($this->default_btn) {
                showXHTML_input('button', '', $this->memsg['btn_return'], '', 'class="cssBtn" onclick="window.location.replace(\'' . $this->uri_target . '\')"');
            }
            $this->toolbar->show();
            showXHTML_td_E('');
            showXHTML_tr_E('');
            showXHTML_table_E();
            showXHTML_tabFrame_E();
            echo '</div>';
            showXHTML_body_E();
            // 介面輸出 (End)            
        }
    }
    
    class wm5MailSender extends wmMailSender{
        function wm5MailSender() {
            wmMailSender::wmMailSender();
        }
        
        /**
         * 輸出寄信的結果列表
         * @param array $reciver : 收件者
         * @param array $no_user : 收件者名單中，不存在wmpro5的使用者
         */
        function output($reciver, $no_user) {
            global $sysSession, $sysConn, $_SERVER, $smarty;
        
            // javascript
            ob_start();
            foreach ($this->user_js as $val) {
                showXHTML_script($val[0] , $val[1], $val[2]);
            }
            $scr = ob_get_contents();
            ob_end_clean();
            $smarty->assign('inlineJS', $scr);
            
            
            $smarty->assign('pageTitle', $this->title);
            $smarty->assign('FieldTitle1', $this->memsg['th_serial']);
            $smarty->assign('FieldTitle2', $this->memsg['th_reciver']);
            $smarty->assign('FieldTitle3', $this->memsg['th_result']);
            
            $i = 0;
            $datalist = array();
            for ($i = 0, $size=count($reciver); $i < $size; $i++) {
                $res = in_array($reciver[$i], $no_user) ? $this->memsg['user_not_exist'] : $this->memsg['sended'];
                if (($reciver[$i] == $sysSession->username) && $this->send_backup) {
                    // 取出使用者設定的信件匣備份的名稱
                    $name_ary = nowPos('sys_sent_backup');
                    $folder_name = end($name_ary);
                    $res = $this->memsg['no_self1'] . $folder_name . $this->memsg['no_self2'];
                }
                 
                $datalist[] = array(
                    'seq' => $i+1,
                    'mail' => $reciver[$i],
                    'result' => $res
                );
            }
            $smarty->assign('datalist', $datalist);
            
            $smarty->assign('btnText', $this->memsg['btn_return']);
            $smarty->assign('btnAction', 'onclick="window.location.replace(\'' . $this->uri_target . '\')"');
            
            // output
            $smarty->display('common/tiny_header.tpl');
            $smarty->display('common/mail/send_result_list.tpl');
            $smarty->display('common/tiny_footer.tpl');
        }
    }
?>
