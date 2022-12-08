<?php
    /**
     * APP訊息推播
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/editor.php');
    require_once(sysDocumentRoot . '/lang/app_server_push.php');
    require_once(sysDocumentRoot . '/lang/teach_student.php');

    $appStudentPushAlert = str_replace('%COURSE_NAME%', $sysSession->course_name, $MSG['app_student_push_alert'][$sysSession->lang]);
    $appMessageID = uniqid('COURSE-' . $sysSession->course_id . '-');

    $js = <<< BOF
    var MSG_DATA_EMPTY = "{$MSG['app_push_message_data_empty'][$sysSession->lang]}";
    var MSG_CONFIRM_PUSH = "{$MSG['app_push_message_confirm'][$sysSession->lang]}";
    var MSG_APP_PUSH_COMPLETE = "{$MSG['app_push_message_complete'][$sysSession->lang]}";
    var APP_STUDENT_ALERT = "{$appStudentPushAlert}";
    var APP_SENDER = "{$sysSession->username}";
    var APP_MESSAGE_ID = "{$appMessageID}";

    // 清除內容
    function cleanContent() {
        top.frames['c_main'].$('#app_push_content').val('');
    }

    // 檢查內容
    function checkContent() {
        var content = top.frames['c_main'].$('#app_push_content').val();
        var pushForm = top.frames['c_main'].$('#pushForm');

        if (content.length === 0) {
            alert(MSG_DATA_EMPTY);
            return false;
        }

        return true;
    }
    
    function appPushUserSelect() {
        var win = new WinAPPPushUserSelect('doAppPush');
        if (checkContent()) {
            win.run();
        }
    }
    
    function doAppPush(arr) {
        var pushObject = new Object(),
            content = top.frames['c_main'].$('#app_push_content').val();
        
        user_ids = arr[0];
                        
        pushObject = {
            data: {
                alert: APP_STUDENT_ALERT,
                content: content,
                sender: APP_SENDER,
                channel: user_ids.split(','),
                alertType: 'COURSE',
                messageID: APP_MESSAGE_ID
            }
        };
        
        $.ajax({
            url: '../../xmlapi/push-handler.php',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify(pushObject)
        });
        top.frames['c_main'].$('#app_push_content').val('');
        alert(MSG_APP_PUSH_COMPLETE);
    }
BOF;
    showXHTML_head_B($MSG['app_server_push'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_script('include', '/lib/app_json.js');
    showXHTML_script('include', '/lib/xmlextras.js');
    showXHTML_script('include', '/lib/common.js');
    showXHTML_script('include', '/lib/popup/popup.js');
    showXHTML_script('include', '/lib/jquery/jquery.min.js', true, null, 'UTF-8');
    showXHTML_script('inline', $js);
    showXHTML_head_E('');

    showXHTML_body_B('');
        echo '<div align="center">';
            showXHTML_form_B('method="post" id="app_server_push_form" action="app_server_push.php" enctype="multipart/form-data"', 'app_server_push_form');
            showXHTML_table_B('width="760" align="center" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="appServerPushTable"');
                showXHTML_tr_B('class="cssTrHead"');
                    showXHTML_td('colspan="3"', $MSG['app_push_description'][$sysSession->lang]);
                showXHTML_tr_E();
                // 內容
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('class="cssTrHead" align="right"', $MSG['app_push_content'][$sysSession->lang]);
                    showXHTML_td_B('');
                        showXHTML_input('textarea', 'app_push_content', '', '', 'id="app_push_content" class="cssInput" rows="20" cols="70" style="resize:none"');
                    showXHTML_td_E();
                    showXHTML_td('');
                showXHTML_tr_E();
                showXHTML_tr_B('class="cssTrEvn"');
                    showXHTML_td_B('colspan="3" align="right"');
                        showXHTML_input('button','btnAppPush',$MSG['app_push_button'][$sysSession->lang],'','onclick="appPushUserSelect();"');
                        showXHTML_input('button', '', $MSG['clean'][$sysSession->lang], '', 'onclick="cleanContent();" class="cssBtn"');
                    showXHTML_td_E();
                showXHTML_tr_E();
            showXHTML_table_E();
            showXHTML_form_E();
        echo '</div>';
    showXHTML_body_E('');
?>
