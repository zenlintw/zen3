<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/lstable.php');
    require_once(sysDocumentRoot . '/lang/app_course_manage.php');
    require_once(sysDocumentRoot . '/academic/course/course_lib.php');

    $getActId = trim($_POST['activity_id']);
    if(!empty($getActId)) {
        $activityId = sysDecode($getActId);
    } else {
        $activityId = null;
    }

    if(!empty($_POST['filename'])) {
        $getFilename = trim($_POST['filename']);
    } else {
        $getFilename = null;
    }
    
    $caption = trim($_POST['caption']);
    
    $status = $_POST['status'];
    
    if(empty($_POST['actionType'])) {
        $actionType = null;
    } else {
        $actionType = trim($_POST['actionType']);
    }
    
    $pictureFlag = false;

    // 有收到圖檔名稱，則去取目錄下的圖檔
    if(!is_null($getFilename)) {
        // 將取到的檔名做".."或"斜線"的字串轉換
        $getFilename = preg_replace(
            array('/\.\.+/', '/[\/\\\\]{2,}/'),
            array('', '/'),
            $getFilename
        );

        if(!is_file(sysDocumentRoot.$getFilename)) {
            // 如果轉換檔名後發現找不到檔案，則顯示錯誤訊息
            echo <<< EOB
            <script>
                alert('{$MSG['msg_alert_select'][$sysSession->lang]}');
            </script>
EOB;
            $getFilename=null;
        }
        $pictureFile = $getFilename;
    }
    
    if ($actionType==='save') {
        // 儲存圖片設定
        list($exist) = dbGetStSr('CO_activities','count(*)',"act_id={$activityId}",ADODB_FETCH_NUM);

        if (is_file(sysDocumentRoot.$pictureFile)) {
            if ($exist>0) {
                // 變更
                dbSet('CO_activities',"picture='{$pictureFile}',status='{$status}',caption='{$caption}'","act_id={$activityId}");
            } else {
                // 新增
                list($maxPermute) = dbGetStSr('CO_activities','max(permute)','1',ADODB_FETCH_NUM);
                dbNew('CO_activities','caption,status,permute,picture',"'{$caption}','{$status}',{$maxPermute}+1,'{$pictureFile}'");
            }
            // 圖片設定成功
            $alertMsg = $MSG['msg_activity_save_success'][$sysSession->lang];            
        } else {
            // 圖片設定未異動
            $alertMsg = $MSG['msg_activity_save_fail'][$sysSession->lang];
        }
        // 顯示異動結果訊息後，回到課程列表去
        echo <<< EOB
        	<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	            <script>
	                alert('{$alertMsg}');
	                location.replace("activity_list.php");
	            </script>
            </head>
            <body/>
            </html>
EOB;
    } else if ($actionType==='remove') {
        // 刪除圖片設定
        dbSet('CO_activities','picture=""',"act_id={$activityId} limit 1");
    }
    
    $removeButtonDisable = 'disabled';  // 移除的按鈕預設為disabled
    // 取出資料庫的圖片設定
    if ($activityId>0) {
        $table = 'CO_activities';
        $fields = 'caption,status,picture';
        $where = "act_id={$activityId}";
        list($caption,$status,$picture) = dbGetStSr($table,$fields,$where,ADODB_FETCH_NUM);
        
        if($actionType!=='reload') {
            $pictureFile = $picture;
        }
        
        if(strlen($picture)===0) {
            $btnCancelFlag = 'lock';
        } else {
            $removeButtonDisable = '';
        }
    }
    
    $previewButtonDisable = 'disabled';  // 移除的按鈕預設為disabled

    if(is_file(sysDocumentRoot.$pictureFile)) {
        $imageSize = getimagesize(sysDocumentRoot.$pictureFile);
        $pictureWidth = $imageSize[0]*0.2;
        $pictureHeight = $imageSize[1]*0.2;
        $picture = "<img src='{$pictureFile}' width='{$pictureWidth}' height='{$pictureHeight}'>";
        $previewButtonDisable = '';
    }

$js = <<< BOF
    var schoolId = {$sysSession->school_id};
    var filePath = '/base/'+schoolId+'/door/APP/advs/';
    var MSG_ALERT_REMOVE = "{$MSG['msg_alert_remove'][$sysSession->lang]}";
    var MSG_ALERT_NO_FILE = "{$MSG['msg_no_file'][$sysSession->lang]}";
    var MSG_STRING_OVER_LIMIT = "{$MSG['msg_string_over_limit'][$sysSession->lang]}";
    var MSG_UNABLE_CANCEL = "{$MSG['msg_unable_cancel'][$sysSession->lang]}";
    var BTN_CANCEL_FLAG = "{$btnCancelFlag}";

    /**
     * 取得瀏覽的檔名。※※※※ 函數名稱不可改 ※※※※
     * 取得後將檔名以POST method 送出
     *
     * return string returnVale ：檔案名稱
     */
    function getReturnValue()
    {
        if (typeof(window.returnValue) === 'undefined') return;
        var subForm = document.getElementById('submitForm');
        var fileName = window.returnValue.substr(1);
        var caption = document.getElementById('caption').value;
        subForm.filename.value = filePath+fileName;
        subForm.caption.value = caption;
        subForm.actionType.value = 'reload';
        subForm.submit();

    }

    /**
     * 瀏覽檔案
     */
    function browseFile()
    {
        window.open('/lib/app_listfiles.php?from=activity', '',
                    'width=380,height=400,status=0,toolbar=0,menubar=0,scrollbars=1,resizable=1');
    }

    /**
     * 取消並回課程列表
     */
    function cancel() 
    {
        if(BTN_CANCEL_FLAG=='lock') {
            var msg = MSG_UNABLE_CANCEL + "\\n\\n" + MSG_ALERT_NO_FILE;
            
            alert(msg);
            return false;
        }
        window.location="activity_list.php";
    }

    /**
     * 儲存圖片
     */
    function save() 
    {
        var subForm = document.getElementById('submitForm');
        var caption = document.getElementById('caption').value;
        var status = document.getElementById('status').value;
        var filename = document.getElementById('filename').value;
        if(filename.length<=0) {
            alert(MSG_ALERT_NO_FILE);
            return false;
        }
        if(caption.length>100) {
            alert(MSG_STRING_OVER_LIMIT);
            return false;
        } else {
            subForm.caption.value = caption;
            subForm.status.value = status;  
            subForm.actionType.value = 'save';
            subForm.submit();
        }
    }
    
    /**
     * 刪除圖片
     */
    function remove() 
    {
        if(!confirm(MSG_ALERT_REMOVE)) {
            return false;
        }
        var subForm = document.getElementById('submitForm');
        subForm.actionType.value = 'remove';
        subForm.submit();
    }
    
    /**
     * 實際大小預覽
     */
    function realPreview() 
    {
        var filename = stringToBase64(document.getElementById('filename').value);
        var caption = stringToBase64(document.getElementById('caption').value);
        window.open('activity_preview.php?picture='+filename+'&caption='+caption);
    }

BOF;

showXHTML_head_B('');
showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
showXHTML_script('include', '/lib/common.js');
showXHTML_script('include', '/lib/xmlextras.js');
showXHTML_script('include', '/lib/filter_spec_char.js');
showXHTML_script('include', '/lib/base64.js');
showXHTML_CSS('include', '/lib/jquery/css/jquery-ui-1.8.22.custom.css');
showXHTML_script('include', '/lib/jquery/jquery.min.js');
showXHTML_script('include', '/lib/jquery/jquery-ui-1.8.22.custom.min.js');
showXHTML_script('inline', $js);
showXHTML_head_E();
showXHTML_body_B();
        $ary = array();
        $ary[] = array($MSG['tab_activity_setting'][$sysSession->lang], 'tabs1');
        echo '<div align="center">';
        showXHTML_tabFrame_B($ary, 1,'propertyFrame','','style="display: inline"'); //, form_id, table_id, form_extra, isDragable);
            showXHTML_table_B('width="900" border="0" cellspacing="1" cellpadding="3" id="dataTb" class="cssTable"');
                showXHTML_tr_B('class="cssTrHead"');
                    showXHTML_td('align="left" nowrap'." colspan='3'",$MSG['star'][$sysSession->lang].$MSG['required'][$sysSession->lang]);
                  showXHTML_tr_E();
                $cssTR = ($cssTR == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($cssTR);
                      showXHTML_td('align="right"',$MSG['item_picture'][$sysSession->lang].$MSG['star'][$sysSession->lang]);
                      showXHTML_td_B('align="left" nowrap');
                        echo '<span>'.$picture.'</span>';
                        showXHTML_input('button', '', $MSG['btn_image_browse'][$sysSession->lang], '', 'class="button01" onclick="browseFile();"');
                        showXHTML_input('button', '', $MSG['btn_image_delete'][$sysSession->lang], '', 'class="button01" '.$removeButtonDisable.' onclick="remove();"');
                        showXHTML_input('button', '', $MSG['btn_image_preview'][$sysSession->lang], '', 'class="button01" '.$previewButtonDisable.' onclick="realPreview();"');
                      showXHTML_td_E();
                      showXHTML_td('align="left"',$MSG['item_image_act_remark'][$sysSession->lang].'<br>'.
                                                  '<font color="red">'.$MSG['msg_chars_limit'][$sysSession->lang].'</font><br>'.
                                                  $MSG['msg_filetype_limit'][$sysSession->lang]);
                  showXHTML_tr_E();
                  $cssTR = ($cssTR == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($cssTR);
                      showXHTML_td('align="right"',$MSG['item_remark'][$sysSession->lang]);
                      showXHTML_td_B();
                          showXHTML_input('text', 'caption', $caption, '', 'id="caption" size="100" class="cssInput"');
                      showXHTML_td_E();
                      showXHTML_td('align="left"',$MSG['item_remark_remark'][$sysSession->lang]);
                  showXHTML_tr_E();
                  $cssTR = ($cssTR == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($cssTR);
                $activityStatus = array('Y'=>$MSG['item_status_on'][$sysSession->lang],
                                        'N'=>$MSG['item_status_down'][$sysSession->lang]);
                      showXHTML_td('align="right"',$MSG['item_status'][$sysSession->lang]);
                      showXHTML_td_B();
                          showXHTML_input('select', 'status', $activityStatus , $status , 'id="status" class="cssInput"');
                      showXHTML_td_E();
                      showXHTML_td('align="left"',$MSG['item_course_remark'][$sysSession->lang]);
                  showXHTML_tr_E();
                $cssTR = ($cssTR == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B('class="cssTrHead"');
                      showXHTML_td_B('align="center" colspan="3"');
                          showXHTML_input('button', 'btnAgree', $MSG['btn_ok'][$sysSession->lang], '', "onclick='save();'");
                          showXHTML_input('button', 'btnDeny' , $MSG['btn_cancel'][$sysSession->lang] , '', "onclick='cancel()'");
                      showXHTML_td_E();
                showXHTML_tr_E();
            showXHTML_table_E();
        showXHTML_tabFrame_E();
        echo '</div>';
        showXHTML_form_B('method="post" action="activity_property.php" enctype="multipart/form-data"', 'submitForm');
            showXHTML_input('hidden', 'activity_id', $getActId, '', '');
            showXHTML_input('hidden', 'filename', $pictureFile, '', '');
            showXHTML_input('hidden', 'caption', $caption, '', '');
            showXHTML_input('hidden', 'status', '', '', '');
            showXHTML_input('hidden', 'actionType', '', '', '');
        showXHTML_form_E();
showXHTML_body_E();
    
?>