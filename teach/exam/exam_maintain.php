<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/03/21                                                            *
	 *		work for  : exam sub-system maintain interface                                    *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	if (sysEnableAppServerPush) {
        require_once(sysDocumentRoot . '/lang/app_server_push.php');
	}
	if (sysEnableAppISunFuDon) {
		require_once(sysDocumentRoot . '/lang/app_exam.php');
	}
	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600200200';
		$sysSession->restore();
		if (!aclVerifyPermission(1600200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

		}
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700200200';
		$sysSession->restore();
		if (!aclVerifyPermission(1700200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

		}
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800200200';
		$sysSession->restore();
		if (!aclVerifyPermission(1800200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

		}
	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	$ticket = md5(sysTicketSeed . $course_id . $_SERVER['QUERY_STRING']);
	$exam_types = array($MSG['exam_type1'][$sysSession->lang],
					    $MSG['exam_type2'][$sysSession->lang],
					    $MSG['exam_type3'][$sysSession->lang],
					    $MSG['exam_type4'][$sysSession->lang],
					    $MSG['exam_type5'][$sysSession->lang],
                        $MSG['exam_type_isunfudon'][$sysSession->lang]
					   );

	$publishes = array('prepare' => $MSG['publish_state1'][$sysSession->lang],
					   'action'  => $MSG['publish_state2'][$sysSession->lang],
					   'close'   => $MSG['publish_state3'][$sysSession->lang]
					  );

	$count_types = array('none'    => $MSG['count_type0'][$sysSession->lang],
					     'first'   => $MSG['count_type1'][$sysSession->lang],
					     'last'    => $MSG['count_type2'][$sysSession->lang],
					     'max'     => $MSG['count_type3'][$sysSession->lang],
					     'min'     => $MSG['count_type4'][$sysSession->lang],
					     'average' => $MSG['count_type5'][$sysSession->lang]
					    );
	$announce_types = array('never'       => $MSG['announce_type1'][$sysSession->lang],
					        'now'         => $MSG['announce_type2'][$sysSession->lang],
					        'close_time'  => $MSG['announce_type3'][$sysSession->lang],
					        'user_define' => $MSG['announce_type4'][$sysSession->lang]
					       );

	$which_qti = QTI_which;

    chkSchoolId('WM_qti_' . QTI_which . '_test');
	$already_exameds = $sysConn->GetCol('select distinct T.exam_id from WM_qti_' . QTI_which .
										'_test as T inner join WM_qti_' . QTI_which .
										'_result as R on T.exam_id=R.exam_id where T.course_id=' .
										$course_id . ' order by T.exam_id');
	$aes = 'var already_exameds = new Array();';
	if (is_array($already_exameds) && count($already_exameds))
	{
		 $aes .= vsprintf(str_repeat(' already_exameds[%u]=true;', count($already_exameds)), $already_exameds);
	}


	function genForGuestLink($instance)
	{
	    global $course_id;
	    
	    $salt = rand(100000, 999999);
	    $url  = sprintf('/Q/%u/%u/%u/1/', $course_id, $instance, $salt);
	    return $url . md5($_SERVER['HTTP_HOST'] . $url);
	}


	// 開始 output HTML
	showXHTML_head_B($MSG['exam_maintain'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/wm.css");
	  showXHTML_script('include', '/lib/dragLayer.js');
	  showXHTML_script('include', '/lib/jquery/jquery.min.js', true, null, 'UTF-8');

$sysEnableAppServerPush = sysEnableAppServerPush;
	  $scr = <<< EOB
$aes
var isIE            = (navigator.userAgent.search('MSIE') == -1) ? false : true;
var _GSE_MODE_FIRST	= 1;
var _GSE_MODE_LAST	= 2;
var _GSE_MODE_BOTH	= 3;
var _GSE_MODE_ALL	= 4;
var notSave         = false;
var MSG_EXIT        = "{$MSG['changed_but_not_saved'][$sysSession->lang]}";
var _ENV            = "{$topDir}";
var appPushQTIWhich = "{$which_qti}";
var appConfirmPushMessage = "{$MSG['app_push_message_confirm'][$sysSession->lang]}";
var appAlertSuccessMessage = "{$MSG['app_push_message_success'][$sysSession->lang]}";
var appAlertFailMessage = "{$MSG['app_push_message_fail'][$sysSession->lang]}";
var sysEnableAppServerPush = "{$sysEnableAppServerPush}";

window.onbeforeunload = function() {
    if (notSave) return MSG_EXIT;
};

/**
 * 手動推播QTI
 * @param integer courseID 試卷編號
 **/
function QTINotify(examID) {

    var alertMessage = '', result;
    var pushObject = new Object();
    var resultObject = null;

    if (!confirm(appConfirmPushMessage)) {
        return;
    }
    
    pushObject = {
        type: appPushQTIWhich,
        id: examID
    };
    
    $.ajax({
        url: '../../lib/app_course_push_handler.php',
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(pushObject),
        error: function(xhr) {
            alert(appAlertFailMessage);
        },
        success: function(result) {
            alert(appAlertSuccessMessage);
        }
    });
}

/**
 * 刪除 Mozilla 讀入 XML 時產生的空節點
 */
function rm_whitespace(node) {
    switch (node.nodeType) {
        case 1:
            for (var i = node.childNodes.length - 1; i >= 0; i--) rm_whitespace(node.childNodes[i]);
            break;
        case 3:
            if (node.nodeValue.search(/^\s+$/) === 0) node.parentNode.removeChild(node);
            break;
    }
}

/*
 * 取得勾選的項目
 */
function getSelElement(mode) {
    var obj = document.getElementById('displayPanel').getElementsByTagName('tbody')[0];
    var nodes = obj.getElementsByTagName('input');
    var ret = '';

    for (var i = 0; i < nodes.length; i++) {
        switch (mode) {
            case _GSE_MODE_FIRST: // 取第一個
                if (nodes.item(i).checked) return i;
                break;
            case _GSE_MODE_LAST: // 取最後一個
            case _GSE_MODE_BOTH: // 取第一個和最後一個
            case _GSE_MODE_ALL: // 取全部有勾選的
                if (nodes.item(i).checked) ret += (i + ',');
                break;
        }
    }
    ret = ret.replace(/,$/, '');
    var aa = ret.split(',');
    if (aa.length < 2 && (mode == _GSE_MODE_LAST || mode == _GSE_MODE_BOTH)) {
        alert('{$MSG['Least_two_selected_elements'][$sysSession->lang]}');
        return false;
    }
    switch (mode) {
        case _GSE_MODE_LAST:
            return aa[aa.length - 1];
        case _GSE_MODE_BOTH:
            return (aa[0] + ',' + aa[aa.length - 1]);
        default:
            return ret;
    }
}

/*
 * 執行功能
 */
function executing(idx) {
    if ((idx == 1 || idx == 2 || idx == 3 || idx == 6 || idx == 7 || idx == 8 || idx == 11) && notSave) {
        if (confirm(MSG_EXIT))
            notSave = false;
        else
            return;
    }
    switch (idx) {
        case 1: // 新增
            if (this.name == 'main')
                parent.document.getElementById('workarea').cols = '0,*';
            else
                parent.document.getElementById('envCourse').cols = '0,*';
            var obj = document.getElementById('procform');
            obj.action = 'exam_create.php';
            obj.submit();
            closeToolMenuWindow();
            break;
        case 2: // 修改
            var cur = getSelElement(_GSE_MODE_FIRST);
            if (cur === false || cur === '') {
                alert('{$MSG['select_one_item_first'][$sysSession->lang]}');
                return;
            }
            var obj = document.getElementById('displayPanel').getElementsByTagName('tbody')[0];
            var nodes = obj.getElementsByTagName('input');
            if (typeof(already_exameds[parseInt(nodes.item(parseInt(cur)).value)]) != 'undefined' &&
                !confirm('{$MSG['already_examed'][$sysSession->lang]}')
            ) {
                nodes.item(parseInt(cur)).checked = false;
                return;
            }

            var thisLineHtml = $(":checkbox[value="+nodes.item(parseInt(cur)).value+"]").parent().parent().html();
            if (thisLineHtml.indexOf("show_deny_edit_irs") > -1){
                show_deny_edit_irs();
                return;
            }

            if (this.name == 'main')
                parent.document.getElementById('workarea').cols = '0,*';
            else
                parent.document.getElementById('envCourse').cols = '0,*';
            obj = document.getElementById('procform');
            obj.action = 'exam_modify.php';
            obj.lists.value = nodes.item(parseInt(cur)).value;
            obj.submit();
            closeToolMenuWindow();
            break;
        case 3: // 刪除
            var cur = getSelElement(_GSE_MODE_ALL);
            if (cur === false || cur === '') {
                alert('{$MSG['select_one_item_first'][$sysSession->lang]}');
                return;
            }
            /*    
            if (!confirm('{$MSG['delete_confirm'][$sysSession->lang]}')) return;
            */
            var obj = document.getElementById('displayPanel').getElementsByTagName('tbody')[0];
            var nodes = obj.getElementsByTagName('input');
            var aa = cur.split(',');
            var ii = 0,
                tmp = ''; 
            
            for (var i = (aa.length - 1); i >= 0; i--) {
                ii = parseInt(aa[i], 10);
                tmp += nodes.item(ii).value + ',';
            }
            obj = document.getElementById('procform');
            obj.action = 'exam_remove.php';
            obj.lists.value = tmp.replace(/,$/, '');
            
            // 判斷有無作答記錄，如果有則提醒老師
            $.ajax({
                'url': '/mooc/controllers/course_ajax.php',
                'data': 'action=getQTIResultNum&type={$which_qti}&exam_ids=' + obj.lists.value,
                'type': 'POST',
                'async': false,
                'dataType': 'json',
                'success': function(res) {
                    if (window.console) {console.log(res);} 
                    if (res.data >= 1) {
                        alert('{$MSG['clear_before'][$sysSession->lang]}');
                    } else {
                        if (confirm('{$MSG['confirm_delete'][$sysSession->lang]}')) {
                            self.onunload = null;
                            obj.submit();
                        } 
                    }
                }
            });
            break;
        case 4: // 權限
            alert('Not yet provided.');
            return;
            break;
        case 5: // 儲存
            notSave = false;
            var obj = document.getElementById('displayPanel').getElementsByTagName('tbody')[0];
            var exams = obj.getElementsByTagName('input');
            var lists = [];
            for (var i = 0; i < exams.length; i++) {
                if (exams[i].type == 'checkbox') lists[lists.length] = exams[i].value;
            }
            if (lists.length <= 0) return;
            
            obj = document.getElementById('procform');
            obj.lists.value = lists.join(",");
            
            
            // 背景存檔
            $.ajax({
                'url': 'exam_order.php',
                'data': $("#procform").serialize(),
                'type': 'POST',
                'dataType': 'json',
                'success': function(res) {
                    if (window.console) {console.log(res);} 
                }
            });            
            break;
        case 6: // 批改
            var cur = getSelElement(_GSE_MODE_FIRST);
            if (cur === false || cur === '') {
                alert('{$MSG['select_one_item_first'][$sysSession->lang]}');
                return;
            }
            if (this.name == 'main')
                parent.document.getElementById('workarea').cols = '0,*';
            else
                parent.document.getElementById('envCourse').cols = '0,*';
            var obj = document.getElementById('displayPanel').getElementsByTagName('tbody')[0];
            var nodes = obj.getElementsByTagName('input');
            obj = document.getElementById('procform');
            obj.action = 'exam_correct.php';
            obj.lists.value = nodes.item(parseInt(cur)).value;
            obj.submit();
            break;
        case 7: // 發布
            var cur = getSelElement(_GSE_MODE_ALL);
            if (cur === false || cur === '') {
                alert('{$MSG['select_one_item_first'][$sysSession->lang]}');
                return;
            }
            var obj = document.getElementById('displayPanel').getElementsByTagName('tbody')[0];
            var nodes = obj.getElementsByTagName('input');
            var aa = cur.split(',');
            var ii = 0,
                tmp = '';
            for (var i = (aa.length - 1); i >= 0; i--) {
                ii = parseInt(aa[i], 10);
                tmp += nodes.item(ii).value + ',';
            }
            obj = document.getElementById('procform');
            obj.action = 'exam_publish.php';
            obj.lists.value = tmp.replace(/,$/, '');
            if (window.console) {console.log(obj.lists.value);}               
            self.onunload = null;
            obj.submit();
            break;
        case 8: // 清除作答記錄
            var cur = getSelElement(_GSE_MODE_ALL);
            if (cur === false || cur === '') {
                alert('{$MSG['select_one_item_first'][$sysSession->lang]}');
                return;
            }
            if (!confirm("{$MSG['reset_confirm'][$sysSession->lang]}")) return;
            var obj = document.getElementById('displayPanel').getElementsByTagName('tbody')[0];
            var nodes = obj.getElementsByTagName('input');
            var aa = cur.split(',');
            var ii = 0,
                tmp = '';
            for (var i = (aa.length - 1); i >= 0; i--) {
                ii = parseInt(aa[i], 10);
                tmp += nodes.item(ii).value + ',';
            }
            obj = document.getElementById('procform');
            obj.action = 'exam_reset.php';
            obj.lists.value = tmp.replace(/,$/, '');
            self.onunload = null;
            obj.submit();
            break;
        case 9: // 上移
            var cur = getSelElement(_GSE_MODE_ALL);
            if (cur === false || cur === '') {
                alert('{$MSG['select_one_item_first'][$sysSession->lang]}');
                return;
            }
            var obj = document.getElementById('displayPanel');
            var aa = cur.split(',');
            var ii = 0,
                tmp;
            for (var i = 0; i < aa.length; i++) {
                if (sysEnableAppServerPush) {
                    // 因為有開啟推播，多了一個推播按鈕，所以編號要重新計算
                    ii = parseInt(aa[i], 10) / 2;
                } else {
                    ii = parseInt(aa[i], 10);
                }
                if (ii == 0) continue;
                tmp = obj.rows[ii].cloneNode(true);
                obj.rows[ii].parentNode.removeChild(obj.rows[ii]);
                if (ii + 1 == obj.rows.length)
                    obj.rows[ii].parentNode.appendChild(tmp);
                else
                    obj.rows[ii].parentNode.insertBefore(tmp, obj.rows[ii + 1]);
            }
            notSave = false;
            executing(5);
            break;
        case 10: // 下移
            var cur = getSelElement(_GSE_MODE_ALL);
            if (cur === false || cur === '') {
                alert('{$MSG['select_one_item_first'][$sysSession->lang]}');
                return;
            }
            var obj = document.getElementById('displayPanel');
            var aa = cur.split(',');
            var ii = 0,
                tmp;
            for (var i = (aa.length - 1); i >= 0; i--) {
                ii = parseInt(aa[i], 10);
                if (sysEnableAppServerPush) {
                    // 因為有開啟推播，多了一個推播按鈕，所以編號要重新計算
                    ii = parseInt(aa[i], 10) / 2;
                } else {
                    ii = parseInt(aa[i], 10);
                }
                if ((ii + 1) == (obj.rows.length - 1)) continue;
                tmp = obj.rows[ii + 2].cloneNode(true);
                obj.rows[ii + 2].parentNode.removeChild(obj.rows[ii + 2]);
                obj.rows[ii + 1].parentNode.insertBefore(tmp, obj.rows[ii + 1]);
            }
            notSave = false;
            executing(5);
            break;
        case 11: // 匯出
            var cur = getSelElement(_GSE_MODE_FIRST);
            if (cur === false || cur === '') {
                alert('{$MSG['select_one_item_first'][$sysSession->lang]}');
                return;
            }
            var obj = document.getElementById('displayPanel').getElementsByTagName('tbody')[0];
            var nodes = obj.getElementsByTagName('input');
            var tmp = nodes.item(parseInt(cur, 10)).value;
            if (tmp.search(/^\d+$/) == 0) {
                
                if (window.console) {console.log(tmp);}               
                
                parent.empty.location.href = 'exam_export.php?' + tmp;
                executing(14);
            } else
                alert('exam_id incorrect.');
            break;
        case 12: // 匯入
            displayDialog('ImportTable');
            break;
        case 13: // 全選
        case 14: // 全消
            var obj = document.getElementById('displayPanel').getElementsByTagName('tbody')[0];
            var nodes = obj.getElementsByTagName('input');
            for (var i = 0; i < nodes.length; i++)
                if (nodes.item(i).getAttribute('type') == 'checkbox')
                    nodes.item(i).checked = (idx & 1) ? true : false;
            break;
        case 15: // 複製
            var cur = getSelElement(_GSE_MODE_ALL);
            if (cur === false || cur === '') {
                alert('{$MSG['select_one_item_first'][$sysSession->lang]}');
                return;
            }
            var obj = document.getElementById('displayPanel').getElementsByTagName('tbody')[0];
            var nodes = obj.getElementsByTagName('input');
            var aa = cur.split(',');
            var ii = 0,
                tmp = '';
            for (var i = (aa.length - 1); i >= 0; i--) {
                ii = parseInt(aa[i], 10);
                tmp += nodes.item(ii).value + ',';
            }
            document.getElementById('CopyForm').lists.value = tmp.replace(/,$/, '');
            if (_ENV == 'academic') {
                self.onunload = null;
                document.getElementById('CopyForm').submit();
            } else {
                displayDialog('CopyTable');
            }
            break;
        case 50: // 進階功能
            var mainFrame = window.parent.frames[1].document; // 另外一個FRAME
            var iconAdvanced = $(mainFrame).find("td[style='display: block;']").find('img');
            var tools = $(mainFrame).find('#Tools');
                
            if ($(iconAdvanced).data('status') === undefined || $(iconAdvanced).data('status') === 'collapse') {
                // 更換圖示
                $(iconAdvanced).attr('src', '/theme/default/academic/icon-cc.gif');
                
                $(iconAdvanced).data('status', 'expand');
                // if (window.console) {console.log($(tools).find("img[src='/theme/default/academic/icon_all_d.gif']").parent().parent().parent());}                  
                    
                // $(tools).find("td[style='padding-left: 0.4em;']").parent().show();// IE8 失敗，所以用以下方法><
                $(tools).find("img[src='/theme/default/academic/icon_all_d.gif']").parent().parent().parent().show();
                $(tools).find("img[src='/theme/default/academic/icon_delete.gif']").parent().parent().parent().show();
                $(tools).find("img[src='/theme/default/academic/icon_up.gif']").parent().parent().parent().show();
                $(tools).find("img[src='/theme/default/academic/icon_down.gif']").parent().parent().parent().show();
                $(tools).find("img[src='/theme/default/academic/icon_import.gif']").parent().parent().parent().show();
                $(tools).find("img[src='/theme/default/academic/icon_export.gif']").parent().parent().parent().show();
            } else {
                // 更換圖示
                $(iconAdvanced).attr('src', '/theme/default/academic/icon-c.gif');
                
                $(iconAdvanced).data('status', 'collapse');            
                // $(tools).find("td[style='padding-left: 0.4em;']").parent().hide();// IE8 失敗，所以用以下方法><
                $(tools).find("img[src='/theme/default/academic/icon_all_d.gif']").parent().parent().parent().hide();
                $(tools).find("img[src='/theme/default/academic/icon_delete.gif']").parent().parent().parent().hide();
                $(tools).find("img[src='/theme/default/academic/icon_up.gif']").parent().parent().parent().hide();
                $(tools).find("img[src='/theme/default/academic/icon_down.gif']").parent().parent().parent().hide();
                $(tools).find("img[src='/theme/default/academic/icon_import.gif']").parent().parent().parent().hide();
                $(tools).find("img[src='/theme/default/academic/icon_export.gif']").parent().parent().parent().hide();
            }
                
            break;
    }
}

function getTarget() {
    var obj = null;
    switch (this.name) {
        case "s_main":
            obj = parent.s_catalog;
            break;
        case "c_main":
            obj = parent.c_catalog;
            break;
        case "main":
            obj = parent.catalog;
            break;
        case "s_catalog":
            obj = parent.s_main;
            break;
        case "c_catalog":
            obj = parent.c_main;
            break;
        case "catalog":
            obj = parent.main;
            break;
    }
    return obj;
}

window.onload = function() {
    rm_whitespace(document.documentElement);

    var obj = getTarget();
    if ((typeof(obj) == 'object') && (obj != null))
        obj.location.replace('exam_maintain_toolbar.php');
};

window.onunload = function() {
    var obj = getTarget();
    if ((typeof(obj) == 'object') && (obj != null))
        obj.location.replace('about:blank');
};

function closeToolMenuWindow() {
    var obj = getTarget();
    if ((typeof(obj) == 'object') && (obj != null))
        obj.location.replace('about:blank');
}

function selectRang(from, to) {
    var objTable = document.getElementById('displayPanel');
    if (from > to) {
        var swap = from;
        from = to;
        to = swap;
    }
    from = Math.max(from, 1);
    to = Math.min(objTable.rows.length - 1, to);
    for (var i = from; i <= to; i++)
        objTable.rows[i].cells[0].getElementsByTagName('input')[0].checked ^= true;
}

function edit_item(obj) {
    document.getElementById('form1').reset();
    obj.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.cells[0].getElementsByTagName('input')[0].checked = true;
    executing(2);
}

function displayDialog(obj_id) {
    var obj = document.getElementById(obj_id);
    if (obj == null) return;
    // 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(500)] 再左移 10 個 pixel
    obj.style.left = document.body.scrollLeft + document.body.offsetWidth - 550;
    // 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
    obj.style.top = document.body.scrollTop + 10;
    obj.style.display = '';
}

function checkCopyTo(option) {
    var c = !(parseInt(option.value) && option.checked);

    var elements = option.form.getElementsByTagName('input');
    for (var i = 0; i < elements.length; i++) {
        if (elements[i].type == 'checkbox') elements[i].disabled = c;
    }
}

function sureCopy(form) {
    document.getElementById('CopyTable').style.display = 'none';
    if (form.which_copy_to[1].checked) {
        var elements = form.getElementsByTagName('input');
        var c = 0;
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].type == 'checkbox') c++;
        }
        if (c < 1) {
            alert('{$MSG['select_one_item_first'][$sysSession->lang]}');
            return false;
        }
    }

    self.onunload = null;
    return true;
}

$(function(){
    // 控制核取方塊
    var class_name = 'examid';
    $("input[class='" + class_name + "'][type='checkbox']").on("click", function(){
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
EOB;

if (sysEnableAppISunFuDon) {
$scr .= <<< EOB

var winISunFunDon = null;
var windowIrsResult = null;

/*觀看愛上互動的統計結果*/
function doViewIrsResult(exam_id){
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    var w = 980;
    var h = 500;
    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((height / 2) - (h / 2)) + dualScreenTop;

    window.open('about:blank', 'windowIrsResult', 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
    var obj = document.getElementById('procForm');
    obj.action = 'exam_statistics_result.php';
    obj.lists.value = exam_id;
    obj.target = 'windowIrsResult';
    obj.submit();
}

/*發佈，進行互動*/
function doPublishIRS(goto){
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    var w = parseInt(screen.availWidth/3*2);
    if (w < 1280) w = 1280;
    var h = parseInt(screen.availHeight/3*2);
    if (h < 668) h = 668;
    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((height / 2) - (h / 2)) + dualScreenTop;

    winISunFunDon = window.open('/mooc/irs/exam_publish.php?goto='+goto, 'iSunFunDo', 'scrollbars=yes,resizable=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

}

/*觀看愛上互動的投影畫面*/
function doReviewPublishIRS(goto){
    if (winISunFunDon == null){
        doPublishIRS(goto);
    }else if (winISunFunDon.closed){
        winISunFunDon = null;
        doPublishIRS(goto);
    }else{
        winISunFunDon.focus();
    }
}

function show_deny_edit_irs(){
    alert('{$MSG['msg_deny_edit_active_irs'][$sysSession->lang]}');
}
EOB;
}

	  showXHTML_script('inline', $scr, false);
	showXHTML_head_E();
	showXHTML_body_B();
	  showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse"');
	    showXHTML_tr_B();
	      showXHTML_td_B();
	        $ary[] = array($MSG['exam_maintain'][$sysSession->lang], 'tabsSet',  '');
	        showXHTML_tabs($ary, 1);
	      showXHTML_td_E();
	    showXHTML_tr_E();
	    showXHTML_tr_B();
	      showXHTML_td_B('valign="top" class="bg01"');
		showXHTML_form_B('style="display:inline"', 'form1');
		  showXHTML_table_B('id="displayPanel" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="box01"');
                  echo '<thead>';
		    showXHTML_tr_B('class="bg02 font01"');
		      showXHTML_td_B();
		        showXHTML_input('checkbox', '', '', '', "class='examid'");
		      showXHTML_td_E();
		      showXHTML_td('align="center" width="250"', $MSG['exam_name'][$sysSession->lang]);
		      showXHTML_td('align="center" width="50" ', $MSG['exam_publish'][$sysSession->lang]);
              // VIP#81332 愛上互動 - 問卷列表加欄位
		      if (QTI_which == 'exam' || (sysEnableAppISunFuDon && QTI_which == 'questionnaire' && $topDir == 'teach')) showXHTML_td('align="center" width="80" ', $MSG['exam_use'][$sysSession->lang]);

		      if (QTI_which == 'exam' || QTI_which == 'homework') showXHTML_td('align="center" width="50" ', $MSG['exam_percent'][$sysSession->lang]);
		      if (QTI_which == 'exam') showXHTML_td('align="center" width="80" ', $MSG['count_type'][$sysSession->lang]);
		      showXHTML_td('align="center" width="160"', $MSG['exam_duration'][$sysSession->lang]);
		      showXHTML_td('align="center" width="80" ', $MSG['score_publish_' . QTI_which][$sysSession->lang]);

              // VIP#81332 愛上互動 - 問卷、測驗列表加欄位
              if (sysEnableAppISunFuDon && (QTI_which == 'exam' || QTI_which == 'questionnaire') && $topDir == 'teach') {
                showXHTML_td('align="center" width="80" ', $MSG['exam_participant'][$sysSession->lang]);
                showXHTML_td('align="center" width="120" ', $MSG['exam_ISunFuDon'][$sysSession->lang]);
              }
	      if (sysEnableAppServerPush) {
				    showXHTML_td('align="center"  width="180"', $MSG['app_qti_push_item_' . QTI_which][$sysSession->lang]);
				}
		    showXHTML_tr_E();
                    echo '</thead>';

	if (QTI_which == 'exam')
	    $random_generatings = dbGetCol('WM_qti_exam_test', 'exam_id', 'course_id=' . $course_id . ' and LOCATE("<wm_immediate_random_generate_qti", content)');
	else
	    $random_generatings = array();

	if (QTI_which == 'questionnaire')
	    $forGuestQuests = aclGetForGuestQuest($course_id);
	else
	    $forGuestQuests = array();

	$RS = dbGetStMr('WM_qti_' . QTI_which . '_test',
					'exam_id,title,type,publish,begin_time,close_time,count_type,percent,announce_type,announce_time',
					"course_id=$course_id and type!=5 order by sort,exam_id desc", ADODB_FETCH_ASSOC);
	if ($sysConn->ErrorNo() > 0) {echo $sysConn->ErrorMsg() ;}

    //取得本課程的課程狀態與上課起迄時間
    if (strlen($course_id) == 8){
        $thisCourseData = dbGetRow('WM_term_course','status, st_begin, st_end', sprintf('course_id=%d', $course_id), ADODB_FETCH_ASSOC);
    }
	if ($RS)
	while(!$RS->EOF){
		$col = $col == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"';
		    showXHTML_tr_B($col);
		      showXHTML_td_B();
		        showXHTML_input('checkbox', '', $RS->fields['exam_id'], '', "class='examid'");
		      showXHTML_td_E();
		      $title = (strpos($RS->fields['title'], 'a:') === 0) ?
		               getCaption($RS->fields['title']):
		               array('Big5'		   => $RS->fields['title'],
		                     'GB2312'	   => $RS->fields['title'],
		                     'en'		   => $RS->fields['title'],
		                     'EUC-JP'	   => $RS->fields['title'],
		                     'user_define' => $RS->fields['title']
		               	    );

              if (sysEnableAppISunFuDon && 
                 (intval($RS->fields['type']) == 5) && 
                 ($RS->fields['publish'] != 'prepare')){
                    showXHTML_td('nowrap', '<table width="100%"><tr><td><a href="javascript:;" onclick="show_deny_edit_irs(); return false;" class="cssAnchor">' . $title[$sysSession->lang] . '</a>' . (in_array($RS->fields['exam_id'], $random_generatings) ? '<span title="random generate" style="position: relative; top: -5px">&#174;</span>' : '') . (in_array($RS->fields['exam_id'], $forGuestQuests) ? ('</td><td align="right"><a title="' . $MSG['public access tip'][$sysSession->lang] . '" href="'.genForGuestLink($RS->fields['exam_id']).'" target="_blank" class="cssAnchor">' . $MSG['public access type'][$sysSession->lang] . '</a>') : '') . '</td></table>' );
              }else{
                    showXHTML_td('nowrap', '<table width="100%"><tr><td><a href="javascript:;" onclick="edit_item(this); return false;" class="cssAnchor">' . $title[$sysSession->lang] . '</a>' . (in_array($RS->fields['exam_id'], $random_generatings) ? '<span title="random generate" style="position: relative; top: -5px">&#174;</span>' : '') . (in_array($RS->fields['exam_id'], $forGuestQuests) ? ('</td><td align="right"><a title="' . $MSG['public access tip'][$sysSession->lang] . '" href="'.genForGuestLink($RS->fields['exam_id']).'" target="_blank" class="cssAnchor">' . $MSG['public access type'][$sysSession->lang] . '</a>') : '') . '</td></table>' );
              }

              $nowDbTime = intval($sysConn->GetOne("select UNIX_TIMESTAMP(NOW())"));

              if (sysEnableAppISunFuDon && 
                 (intval($RS->fields['type']) == 5) && 
                 (!empty($RS->fields['close_time'])) && 
                 ($nowDbTime >= strtotime($RS->fields['close_time']))
              ){
                showXHTML_td('', $publishes['close']);
              }else{
                showXHTML_td('', $publishes[$RS->fields['publish']]);
              }
			  if (QTI_which == 'exam') {
			  	showXHTML_td('', $exam_types[$RS->fields['type']]);
              } else if (QTI_which == 'questionnaire' && sysEnableAppISunFuDon && $topDir == 'teach') {
			  	if (intval($RS->fields['type']) != 5) {
                    showXHTML_td('', $exam_types[2]);
				} else {
                    showXHTML_td('', $exam_types[5]);
				}
			  }
		      if (QTI_which == 'exam' || QTI_which == 'homework') showXHTML_td('', $RS->fields['percent'] . '%');
		      if (QTI_which == 'exam') showXHTML_td('', $count_types[$RS->fields['count_type']]);

		      showXHTML_td('style="font-size: 10px"', ($MSG['from'][$sysSession->lang] . (strpos($RS->fields['begin_time'], '0000') === 0 ? $MSG['now'][$sysSession->lang] : date('Y-m-d H:i', strtotime($RS->fields['begin_time'])) ) . '<br>' . $MSG['to2'][$sysSession->lang] . (strpos($RS->fields['close_time'], '9999') === 0 ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', strtotime($RS->fields['close_time'])) )));
		      showXHTML_td('', ($RS->fields['announce_type']=='user_define'?substr($RS->fields['announce_time'], 0, 16):$announce_types[$RS->fields['announce_type']]));
              // VIP#81332 愛上互動 - 問卷、測驗列表加欄位
              if (sysEnableAppISunFuDon && (QTI_which == 'exam' || QTI_which == 'questionnaire') && $topDir == 'teach') {
                //繳交人數
                $participants = dbGetCol('WM_qti_' . QTI_which . '_result','count(*)',sprintf("exam_id=%d and status in ('submit','revised') group by examinee", $RS->fields['exam_id']));
                if (is_array($participants)){
                    showXHTML_td('align="center" width="80" ', count($participants));
                }else{
                    showXHTML_td('align="center" width="80" ', 0);
                }

                // 互動按鈕
                if (intval($RS->fields['type']) == 5) {
                    // 結束時間已到
                    $goto = sysNewEncode(serialize(array('course_id'=>$sysSession->course_id, 'type'=>QTI_which, 'exam_id'=>$RS->fields['exam_id'])), 'wm5IRS');
                    if (!empty($RS->fields['close_time']) && (time()>=strtotime($RS->fields['close_time']))){
                        showXHTML_td_B('align="center" width="120"');
                        echo '<button class="cssBtn" onclick="doViewIrsResult('.$RS->fields['exam_id'].');">'.$MSG['exam_ISunFuDon_result_view'][$sysSession->lang].'</button>';
                        showXHTML_td_E();
                    }else if (($RS->fields['publish'] == 'prepare') && (empty($RS->fields['begin_time']) || $RS->fields['begin_time'] == '0000-00-00 00:00:00')){
                        //尚未開始, 可進行發佈
                        showXHTML_td_B('align="center" width="120"');

                        // 課程是否為準備中，或已到上課截止日
                        $coursePrepareOrExpired = false;
                        if (isset($thisCourseData)) {
                            switch (intval($thisCourseData['status'])) {
                                 case 2:
                                 case 4:
                                    if (!is_null($thisCourseData['st_end']) && (time()>strtotime(date('Y-m-d 23:59:59',strtotime($thisCourseData['st_end']))))){
                                        $coursePrepareOrExpired = true;
                                    }
                                    break;
                                 case 5:
                                    $coursePrepareOrExpired = true;
                                    break;
                             }
                        }

                        if ($coursePrepareOrExpired){
                            echo '<button class="cssBtn" onclick="alert(\''.$MSG['msg_deny_publish_irs'][$sysSession->lang].'\');">'.$MSG['exam_ISunFuDon_publish'][$sysSession->lang].'</button>';
                        }else{
                            echo '<button class="cssBtn" onclick="doPublishIRS(\''.$goto.'\');">'.$MSG['exam_ISunFuDon_publish'][$sysSession->lang].'</button>';
                        }

                        showXHTML_td_E();
                    }else if (($RS->fields['publish'] == 'action') && (!empty($RS->fields['begin_time']))){
                        // 正發佈中
                        showXHTML_td_B('align="center" width="120"');
                        echo '<button class="cssBtn" onclick="doReviewPublishIRS(\''.$goto.'\');">'.$MSG['exam_ISunFuDon_publishing'][$sysSession->lang].'</button>';
                        showXHTML_td_E();
                    }else{
                        showXHTML_td('align="center" width="120" ', '--');
                    }

                }else{
                    showXHTML_td('align="center" width="120" ', '--');
                }
            }
			
			if (sysEnableAppServerPush) {
                  showXHTML_td_B('align="center"');
                    showXHTML_input('button', '', $MSG['app_push_button'][$sysSession->lang], '', 'onclick="QTINotify(' . $RS->fields['exam_id'] . ');"');
                  showXHTML_td_E();
			}
		    showXHTML_tr_E();

		$RS->MoveNext();
	}

		  showXHTML_table_E();
		showXHTML_form_E();
	      showXHTML_td_E();
	    showXHTML_tr_E();
	  showXHTML_table_E();

	  showXHTML_form_B('method="POST" action=""', 'procform');
	    showXHTML_input('hidden', 'ticket', $ticket);
	    showXHTML_input('hidden', 'referer', $_SERVER['QUERY_STRING']);
	    showXHTML_input('hidden', 'lists', '');
	  showXHTML_form_E();

		$ary = array(array($MSG['exam_import'][$sysSession->lang]));
		showXHTML_tabFrame_B($ary, 1, 'ImportForm', 'ImportTable', 'action="exam_import.php" method="POST" enctype="multipart/form-data" style="display: inline" onsubmit="if (this.import_file.value == \'\') return false;"', true);
			showXHTML_table_B('width="680" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('', $MSG['upload_file'][$sysSession->lang]);
					showXHTML_td_B();
						showXHTML_input('file', 'import_file', '', '', 'class="cssInput" size="40" maxlength="254"');
					showXHTML_td_E();
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td('', $MSG['file_type'][$sysSession->lang]);
					showXHTML_td_B();
						showXHTML_input('select', 'import_type', array(1 => 'QTI_xml&nbsp;'), 1, 'class="cssInput"');
					    showXHTML_input('hidden', 'ticket', $ticket);
					    showXHTML_input('hidden', 'referer', $_SERVER['QUERY_STRING']);
					    showXHTML_input('hidden', 'lists', '');
					showXHTML_td_E();
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('colspan="2" align="center"');
						showXHTML_input('submit', '', $MSG['sure_import'][$sysSession->lang], '', 'class="cssBtn"');
						showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]     , '', 'class="cssBtn" onclick="document.getElementById(\'ImportTable\').style.display=\'none\';"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();

		$ary = array(array($MSG['copy to'][$sysSession->lang]));
		showXHTML_tabFrame_B($ary, 1, 'CopyForm', 'CopyTable', 'action="exam_copy.php" method="POST" style="display: inline"', true);
			showXHTML_table_B('width="380" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('align="right"');
						showXHTML_input('submit', '', $MSG['toolbtm15'][$sysSession->lang], '', 'class="cssBtn" onclick="return sureCopy(this.form);"');
						showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]   , '', 'class="cssBtn" onclick="document.getElementById(\'CopyTable\').style.display=\'none\';"');
					showXHTML_td_E();
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td_B('nowrap');
					    chkSchoolId('WM_term_major');
					    $courses = $sysConn->GetAssoc('select M.course_id, C.caption ' .
													  'from WM_term_major as M inner join WM_term_course as C ' .
													  'on M.course_id = C.course_id ' .
													  'where M.username="' . $sysSession->username .
													  '" and M.role&' .
													  ($sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher']) .
													  ' and C.status between 1 and 5 and ' .
													  '(isnull(C.st_begin) or C.st_begin<=CURDATE()) and (isnull(C.st_end) or C.st_end>=CURDATE()) and ' .
													  'C.quota_used < C.quota_limit');
						unset($courses[$sysSession->course_id]); // 把本課去掉
						foreach ($courses as $k => $v)
						{
							$x = unserialize($v);
							if (($courses[$k] = trim($x[$GLOBALS['sysSession']->lang])) == '') 	// 取本語系的課名
							{
								$x = explode(chr(9), trim(implode(chr(9), $x)));	// 如果本語系課名是空的，就取第一個有名字的
								$courses[$k] = $x[0];
							}
						}
                        showXHTML_input('radio', 'which_copy_to', array($MSG['search_scope1'][$sysSession->lang], $MSG['search_scope2'][$sysSession->lang] . $MSG['copy conditions'][$sysSession->lang]), 0, 'onclick="checkCopyTo(this);"', '<br>');
						showXHTML_input('checkboxes', 'target_courses[]', $courses, array(), 'style="margin-left: 2em" disabled', '<br>');
					    showXHTML_input('hidden', 'ticket', $ticket);
					    showXHTML_input('hidden', 'referer', $_SERVER['QUERY_STRING']);
					    showXHTML_input('hidden', 'lists', '');
					showXHTML_td_E();
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('align="right"');
						showXHTML_input('submit', '', $MSG['toolbtm15'][$sysSession->lang], '', 'class="cssBtn" onclick="return sureCopy(this.form);"');
						showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]   , '', 'class="cssBtn" onclick="document.getElementById(\'CopyTable\').style.display=\'none\';"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();

if (sysEnableAppISunFuDon) {
    $random_seat = md5(uniqid(rand(), true));
    $ticket = md5(sysTicketSeed . $sysSession->course_id . $random_seat);
    showXHTML_form_B('method="POST" action="exam_correct.php"', 'procForm');
        showXHTML_input('hidden', 'ticket', $ticket);
        showXHTML_input('hidden', 'referer', $random_seat);
        showXHTML_input('hidden', 'lists', '');
    showXHTML_form_E();
}

	showXHTML_body_E();
?>
