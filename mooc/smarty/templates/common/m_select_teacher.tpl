<link rel="stylesheet" href="{$appRoot}/public/js/third_party/tagmanager/bootstrap-tagsinput.css">
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link rel="stylesheet" href="/public/css/settings.css">
<style>
{literal}
/* Tags input
--------------------------------- */
.bootstrap-tagsinput {
	max-width: 300px;
    line-height: 40px;
    box-shadow:none;
    border:none;
}

.bootstrap-tagsinput input {
    display:none;
}

.bootstrap-tagsinput .tag {
    background-color: #ffffff;
    height: 27px;
    line-height: 27px;
    color:#353535;
    font-size:13px;
    border: 1px solid #d3d3d3;
}

.bootstrap-tagsinput .tag [data-role='remove']::after {
    color:#d3d3d3;
}
.user-selected {
    width: 250px;
}
#Instr_List td {
    word-break: break-all;
}
{/literal}
</style>
<div class="box1">
    <div class="title">{'msg_set_staff'|WM_Lang}</div>
    <div class="content">
        <div class="box2">
            <div class="title" style="width: 50%;">
                <input type="radio" name="select" value="0" {if $sType eq 0}checked="checked"{/if} onchange="showDiv();"/> {'msg_students'|WM_Lang}
                &nbsp;<input type="radio" name="select" value="1" {if $sType eq 1}checked="checked"{/if} onchange="showDiv();"/> {'msg_all_accounts'|WM_Lang}
            </div>
            <div class="operate">
                <!-- 搜尋 -->
                <form class="search-form" data-refer="N" onsubmit="return false;" _lpchecked="1">
                    <input type="text" id="search-key" class="search-keyword" name="keyword" value="{$sWord}" placeholder="{'msg_enter_query'|WM_Lang}" style="width: 95%;">
                    <button class="search-btn" type="button" onclick="doSearch();">
                    <i class="icon-search"></i>
                    </button>
                </form>
            </div>
            <div class="content">
                <form id="actFm" name="actFm" action="{$frmAction}" method="post" onSubmit="return chkForm(this);">
                    <input type="hidden" name="page" value="{$page_no}">
                    <input type="hidden" name="keyword" value="{$sWord}">
                    <input type="hidden" name="stype" value="{$sType}">
                    <input type="hidden" id="save_tags" name="save_tags" value="{$saveTags}">
                    <div class="layout-hr resp">
                        <div id="acc-list" class="layout-child" style="min-width: 300px;">
                            <!-- 帳號列表 -->
                            {if $data|@count eq 0}
                            <div class="alert"  style="text-align: center;">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                {if $sWord eq null}
                                {if $sType eq 0 || $isManager eq 'true' }
                                <strong>{'msg_enter_query_name'|WM_Lang}</strong>
                                {elseif $sType eq 1}
                                <strong>{'msg_enter_full'|WM_Lang}</strong>
                                {/if}
                                {else}
                                {if $sType eq 0 || $isManager eq 'true' }
                                <strong>{'msg_search_null'|WM_Lang}{'msg_enter_query_name'|WM_Lang}</strong>
                                {elseif $sType eq 1}
                                <strong>{'msg_search_null'|WM_Lang}{'msg_enter_full'|WM_Lang}</strong>
                                {/if}
                                {/if}
                            </div>
                            {else}
                            <div id="select-div">
                                <table class="table table-bordered" id="Instr_List">
                                    <thead style="background-color: #0DB9BB; color: white;">
                                        <tr>
                                            <th style="width: 30px; text-align: center;">
                                                <input type="checkbox" id="ckbox" name="ckbox" onclick="selUser(this.checked);" exclude="true" title="{'msg_select'|WM_Lang}">
                                            </th>
                                            <th>{'account'|WM_Lang}</th>
                                            <th>{'name'|WM_Lang}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach from=$data key=key item=val}
                                            {if ($val.chkFlag eq '1')}
                                                {if $selectedUser|@count gt 0 && $val.username|in_array:$selectedUser}
                                                <tr class="warning">
                                                    <td style="text-align: center;">
                                                        <input type="checkbox" name="in_users[]" value="{$val.username}***{$val.realname}" onclick="selUser('{$val.username}');" checked="checked">
                                                {else}
                                                <tr>
                                                    <td style="text-align: center;">
                                                        <input type="checkbox" name="in_users[]" value="{$val.username}***{$val.realname}" onclick="selUser('{$val.username}');">
                                                {/if}
                                            {else}
                                            <tr>
                                                <td style="text-align: center;">
                                            {/if}
                                                </td>
                                                <td title="{$val.username}">{$val.username}</td>
                                                <td title="{$val.realname}">{$val.realname}</td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            {/if}
                            {* 撐開寬度用 *}
                            <div></div>
                        </div>
                        <div class="layout-child" style="width: 10px;"></div>
                        <!-- 使用者 -->
                        <div class="layout-child user-selected">
                            <div style="border: 1px solid #ddd; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; margin-bottom: 20px;">
                                <div style="background-color: #0DB9BB; color: #FFFFFF; line-height: 1.6em; border-radius: 4px 4px 0 0; padding: 0.5em;">
                                    {'msg_user'|WM_Lang}
                                </div>
                                <div style="height: 100%; min-height: 150px;">
                                    <input id="sel_users" type="hidden" name="sel_users" >
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- 頁碼 -->
                    {if $data|@count neq 0}
                    <div id="pageToolbar" class="paginate">
                        <table cellpadding="0" cellspacing="0" border="0" class="paginate-toolbar">
                            <tbody>
                                <tr>
                                    <td>
                                        <a class="undefined {if $page_no eq 1 || $page_no eq 0 } disabled {else}" onclick="go_page(-1); {/if}" title="{'btn_page_first'|WM_Lang}"><i class="paginate-first"></i></a>
                                    </td>
                                    <td>
                                        <a class="undefined {if $page_no eq 1 || $page_no eq 0 } disabled {else}" onclick="go_page(-2); {/if}" title="{'btn_page_prev'|WM_Lang}"><i class="paginate-prev"></i></a>
                                    </td>
                                    <td>
                                        <span class="paginate-number-before"></span><input type="text" class="paginate-number" name="ap" value="{$page_no}" onchange="go_page(this.value);"><span class="paginate-number-after">/ {$total_page}</span>
                                    </td>
                                    <td>
                                        <a class="undefined {if $page_no eq $total_page || $page_no eq 0 } disabled {else}" onclick="go_page(-3); {/if}" title="{'btn_page_next'|WM_Lang}"><i class="paginate-next"></i></a>
                                    </td>
                                    <td>
                                        <a class="undefined {if $page_no eq $total_page || $page_no eq 0 } disabled {else}" onclick="go_page(-4);{/if}"  title="{'btn_page_last'|WM_Lang}"><i class="paginate-last"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="paginate-message"></div>
                        <div style="clear: both;"></div>
                    </div>
                    {/if}
                </form>
                <div style="text-align: right;">
                    <button class="btn btn-blue" onclick="ReturnWork();">{'btn_confirm'|WM_Lang}</button>
                    <button class="btn" onclick="window.close();">{'btn_close'|WM_Lang}</button>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript" src="{$appRoot}/public/js/third_party/tagmanager/bootstrap-tagsinput.js"></script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/site_header.js"></script>
<script>
var MSG_SELECT_ALL      = "{'msg_select'|WM_Lang}";
var MSG_SELECT_CANCEL   = "{'msg_cancel'|WM_Lang}";
var PLZ_INPUT           = "{'input_keyword'|WM_Lang}";
var KEY_WD              = "{'msg_title05'|WM_Lang}";
var PLZ_CHECK           = "{'msg_need_select'|WM_Lang}";
var selTotal            = {$data|@count};
var total_page          = "{$total_page}";
var cnt                 = "{$total_msg}";
var items               = '{$saveTags}';
var isManager           = '{$isManager}';
var MSG_ENTER_QUERY     = "{'msg_enter_query_name'|WM_Lang}";
var MSG_ENTER_FULL      = "{'msg_enter_full'|WM_Lang}";
var MSG_TEACHER_TO_ASSISTANT_ERROR    = "{'msg_teacher_to_assistant_error'|WM_Lang}";
var MSG_INSTRUCTOR_TO_ASSISTANT_ERROR = "{'msg_instructor_to_assistant_error'|WM_Lang}";
var MSG_TEACHER_TO_INSTRUCTOR_ERROR   = "{'msg_teacher_to_instructor_error'|WM_Lang}";
var nowSel               = false;
{literal}
// 如果是所有學生 ajax 驗證是否有該帳號
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

    var items = $("#sel_users").tagsinput('items');
    $.each(items, function(i, v) {
        j++;
        user_ids += v.value + ',';
        user_names += v.text + ',';
    });
    if (j > 0) {
        user_ids = user_ids.substring(0, user_ids.length - 1);
        user_names = user_names.substring(0, user_names.length - 1);
        
        // 如果是設定講師，則移除已被設定為講師的助教元素
        if (getURLParameter('func') === 'setInstructorValue' && $('#tagsAssistant', opener.document).find('#assistant_auth').val() > '') {
            // 取彈出視窗中有勾選的講師            
            var users = user_ids.split(',');       
            // 取母視窗已被設定的助教
            var exists = $('#tagsAssistant', opener.document).find('#assistant_auth').val().split(',');
            $.each(users, function(key, value) {
                // if (window.console) {console.log('講師：', value);}
                $.each(exists, function(k, v) {
                    // 取第幾個，來決定點選哪個元素移除
                    // if (window.console) {console.log('助教：', value);}
                    if (v === value) {
                        $('#tagsAssistant', opener.document).find('.label-info span').eq(k).click();
                    }
                });   
            });                                   
        }

        var hwnd = opener.getHwnd("WinMTeacherSelect");
        if (hwnd != null) {
            var rtnArray = new Array(user_ids, user_names);
            hwnd.callback(rtnArray);
        }

        window.close();
    } else {
        alert(PLZ_CHECK);
    }
}

/* 辦公室/課程設定/權限設定/挑選教師、講師或助教 */
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
    
    // 已被選取的使用者
    var selItems = ($('#sel_users').val()).split(",");
    for (var i = 0, m = 0; i < nodes.length; i++) {
        attr = nodes[i].getAttribute("exclude");
        if ((nodes[i].type == "checkbox") && (attr == null)) {
            m++;
            ary = nodes[i].value.split('***', 2);
            /*alert('val=>'+ary[0]+' / sel=>'+sel);*/
            // 變更勾選狀態
            if (sel === true && !nodes[i].checked) {
                nodes[i].checked = true;
                if (typeof(nodes[i].parentNode.parentNode) == 'object') {
                    selTotal++;
                    nodes[i].parentNode.parentNode.className = 'warning';
                }
            } else if (sel === false && nodes[i].checked) {
                nodes[i].checked = false;
                if (typeof(nodes[i].parentNode.parentNode) == 'object') {
                    selTotal--;
                    nodes[i].parentNode.parentNode.className = '';
                }
            } else if (ary[0] == sel) {
                if (typeof(sObj) == 'object') {
                    nodes[i].checked = !(nodes[i].checked);
                }
                if (typeof(nodes[i].parentNode.parentNode) == 'object') {
                    if (nodes[i].checked) {
                        // console.log(getURLParameter('func'));
                        switch(getURLParameter('func')) {
                            case 'setAssistantValue':
                                // console.log($('#tagsTeacher', opener.document).find('#teach_auth').val());
                                if ($('#tagsTeacher', opener.document).find('#teach_auth').val() > '') {
                                    var exists = $('#tagsTeacher', opener.document).find('#teach_auth').val().split(',');
                                    var is_repeat = exists.indexOf(ary[0]);
                                    // console.log(exists);
                                    // console.log(is_repeat);
                                    if (is_repeat >= 0) {
                                        alert(MSG_TEACHER_TO_ASSISTANT_ERROR);
                                        nodes[i].checked = false;
                                        return false;
                                    }
                                }
                                // console.log($('#tagsInstructor', opener.document).find('#instructor_auth').val());
                                if ($('#tagsInstructor', opener.document).find('#instructor_auth').val() > '') {
                                    var exists = $('#tagsInstructor', opener.document).find('#instructor_auth').val().split(',');
                                    var is_repeat = exists.indexOf(ary[0]);
                                    // console.log(exists);
                                    // console.log(is_repeat);
                                    if (is_repeat >= 0) {
                                        alert(MSG_INSTRUCTOR_TO_ASSISTANT_ERROR);
                                        nodes[i].checked = false;
                                        return false;
                                    }
                                }
                                // console.log(ary[0]);
                                break;
                            
                            // 設定講師
                            case 'setInstructorValue':
                                // console.log($('#tagsTeacher', opener.document).find('#teach_auth').val());
                                
                                if ($('#tagsTeacher', opener.document).find('#teach_auth').val() > '') {
                                    var exists = $('#tagsTeacher', opener.document).find('#teach_auth').val().split(',');
                                    var is_repeat = exists.indexOf(ary[0]);
                                    // console.log(exists);
                                    // console.log(is_repeat);
                                    if (is_repeat >= 0) {
                                        alert(MSG_TEACHER_TO_INSTRUCTOR_ERROR);
                                        nodes[i].checked = false;
                                        return false;
                                    }
                                }
                               
                                // console.log(ary[0]);
                                break;
                        }
                        selTotal++;
                        nodes[i].parentNode.parentNode.className = 'warning';
                    } else {
                        selTotal--;
                        nodes[i].parentNode.parentNode.className = '';
                    }
                }
            }
            
            // 顯示 tags
            var text = ary[0];
            if (ary[1] !== '') {
                // 顯示 帳號 (姓名)
                text = ary[0] + ' (' + ary[1] + ')';
            }
            if (nodes[i].checked) {
                $('#sel_users').tagsinput('add', {
                    "value": ary[0],
                    "text": text
                });
            } else {
                if ($.inArray(ary[0], selItems) > -1) {
                    $('#sel_users').tagsinput('remove', {
                        "value": ary[0],
                        "text": text
                    });
                }
            }
        }
    }
    document.getElementById("ckbox").checked = (m == selTotal);
    if (m == selTotal) {
        document.getElementById("ckbox").title = MSG_SELECT_CANCEL;
        nowSel = true;
    } else {
        document.getElementById("ckbox").title = MSG_SELECT_ALL;
        nowSel = false;
    }
    saveTags();
}

function chkForm(fm) {
    if (typeof(fm) == 'object') {
        var kwd = fm.keyword.value;
        if (kwd == KEY_WD) {
            alert(PLZ_INPUT);
            fm.keyword.focus();
            return false;
        }
        return true;
    }
    return false;
}

function showDiv() {
    var error;
    var selDiv = $("input[name=select]:checked").val();
    $("#actFm input[name=stype]").val(selDiv);
    $("#select-div").hide();
    $("#pageToolbar").hide();
    $("#acc-list .alert").remove();
    if ('0' === selDiv || isManager === 'true') {
        error = $('<div class="alert" style="text-align: center;"></div>')
            .append('<button type="button" class="close" data-dismiss="alert">×</button>')
            .append('<strong>' + MSG_ENTER_QUERY + '</strong>');
    } else if ('1' === selDiv) {
        error = $('<div class="alert" style="text-align: center;"></div>')
            .append('<button type="button" class="close" data-dismiss="alert">×</button>')
            .append('<strong>' + MSG_ENTER_FULL + '</strong>');
    }
    $("#acc-list").append(error);
}

function doSearch() {
    var key = $("#search-key").val();
    $("#actFm input[name=keyword]").val(key);
    $("#actFm input[name=page]").val(1);
    $("#actFm").submit();
}

function saveTags() {
    var items = $("#sel_users").tagsinput('items');
    items = JSON.stringify(items);
    $("#save_tags").val(encodeURIComponent(items));
}

window.onload = function() {
    /*
        if (cnt > 0){
          var obj = document.getElementById('Instr_List');
          if (typeof(obj) == 'object')
                obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[2].cells[0].innerHTML;
        }
*/
    // 選取的使用者tags
    $('#sel_users').tagsinput({
        itemValue: 'value',
        itemText: 'text'
    });
    // 還原之前選取過的帳號
    if (items != null && items != '') {
        var saveItems = JSON.parse(decodeURIComponent(items));
        $.each(saveItems, function(i, v) {
            $('#sel_users').tagsinput('add', {
                "value": v.value,
                "text": v.text
            });
        });
    }
    // 判斷是否全選
    var chCnt = $('#select-div table tbody input:checkbox:not(:checked)').length;
    if (chCnt === 0) {
        nowSel = true;
        $('#ckbox').prop('checked', true);
    }
    selTotal = selTotal - chCnt;
    $("#search-key").on('keydown', function(event) {
        if (event.which == 13) {
            doSearch();
        }
    });

    // tags 移除後連動
    $('#sel_users').on('itemRemoved', function(event) {
        var selItems = ($(this).val()).split(",");
        console.log(selItems);
        var allChk = $('#Instr_List input[type="checkbox"]:checked');
        $.each(allChk, function(v) {
            var obj = $(this);
            var val = (obj.val()).split('***')[0];
            if ($.inArray(val, selItems) == -1) {
                obj.prop('checked', false);
                obj.parent().parent().removeClass("warning");
            }
        });
        selTotal = $('#Instr_List input[type="checkbox"]:checked').length;
        saveTags();
    });
};
{/literal}
</script>