 var isIE = (navigator.userAgent.indexOf(' MSIE ') > -1) ? true : false;

/*檢查表單*/
function checkData() {
    var obj = document.getElementById("actForm");

    // 清除所有輸入錯誤提示呈現
    var error_flag = false;
    $('.alert-lcms-error').removeClass('alert-lcms-error');
    $('input').tooltip('destroy');
    $('textarea').tooltip('destroy');

    // 名稱未輸入或超過256字元
    if (obj.checklist_name.value == ''){
        $("input[name='checklist_name']").addClass('alert-lcms-error');
        $("input[name='checklist_name']").attr('title', nameMsg_empty).tooltip('toggle');
        error_flag = true;
    } else if (obj.checklist_name.value.length > 256) {
        $("input[name='checklist_name']").addClass('alert-lcms-error');
        $("input[name='checklist_name']").attr('title', nameMsg_limit).tooltip('toggle');
        error_flag = true;
    }

    // 級距
    var highest_score = 0;
    var compareLevel = 0;
    var r = /^[0-9]*[1-9][0-9]*$/;// 正整數正規表示式
    $("input[name^='level[']").each(function() {
        if (compareLevel == 0) {
            compareLevel = $(this).val();
        }
        if ($(this).val() == '') {
            $(this).addClass('alert-lcms-error');
            $(this).attr('title', levelMsg_empty).tooltip('toggle');
            error_flag = true;
        } else if (parseInt($(this).val(),10) > compareLevel) {
            $(this).addClass('alert-lcms-error');
            $(this).attr('title', levelMsg_morethanprev).tooltip('toggle');
            error_flag = true;
        }  else if (parseInt($(this).val(),10) <= 0 || parseInt($(this).val(),10) >= 100) {
            $(this).attr('title', levelMsg_limit).tooltip('toggle');
            error_flag = true;
        // 判斷正整數
        }  else if (r.test($(this).val()) === false) {
            $(this).attr('title', levelMsg_integer).tooltip('toggle');
            error_flag = true;
        } else if (parseInt($(this).val(),10) >= highest_score) {
            highest_score = parseInt($(this).val(),10);
        }
        compareLevel = parseInt($(this).val(),10);
    });

    // 級距*指標數量=100
    if (highest_score * $("textarea[name^='point_name[']").length !== 100) {
        if (highest_score !== 0 && $("textarea[name^='point_name[']").length !== 0) {
            $("input[name^='level[']").each(function(){
                if($(this).val()==highest_score){
                    $(this).addClass('alert-lcms-error');
                    $(this).attr('title', highScoreMsg_limit).tooltip('toggle');
                }
            });
            error_flag = true;
        }
    }

    // 級距名稱
    $("input[name^='levelName[']").each(function(i) {
        if ($(this).val() == '') {
            $(this).addClass('alert-lcms-error');
            $(this).attr('title', lNameMsg_empty).tooltip('toggle');
            error_flag = true;
        } else if ($(this).val().length > 256) {
            $(this).addClass('alert-lcms-error');
            $(this).attr('title', lNameMsg_limit).tooltip('toggle');
            error_flag = true;
        }
    });

    // 指標名稱
    $("textarea[name^='point_name[']").each(function(i) {
        if ($(this).val() == '') {
            $(this).addClass('alert-lcms-error');
            $(this).attr('title', pNameMsg_empty).tooltip('toggle');
            error_flag = true;
        } else if ($(this).val().length > 256) {
            $(this).addClass('alert-lcms-error');
            $(this).attr('title', pNameMsg_limit).tooltip('toggle');
            error_flag = true;
        }
    });

    // 級距X指標名稱說明
    $("textarea[name^='point_note']").each(function(i) {
        if ($(this).val() == '') {
            $(this).addClass('alert-lcms-error');
            $(this).attr('title', pNoteMsg_empty).tooltip('toggle');
            error_flag = true;
        }
    });

    if (error_flag === true) {
        return false;
    }

   /*
    disable submit button
   */
   var obj2 = document.getElementById("btn_submit");
   obj2.disabled = true;
}

// 暫存
function tempSave() {
    if (checkData() === false) {
        return;
    } else {
        $('#enable').val(0);
        document.actForm.submit();
    }
}

// 直接儲存
function save() {
    if (checkData() === false) {
        return;
    } else {
        if ($('#enable').val() === '0') {
            if (confirm(saveMsg)) {
                $('#enable').val(1);
                document.actForm.submit();
            } else {
                var obj2 = document.getElementById("btn_submit");
                obj2.disabled = false;
            }
        } else {
            $('#enable').val(1);
            document.actForm.submit();
        }
    }
}

window.onload = function () {
    var obj = document.getElementById("actForm");
    obj.checklist_name.focus();
};

// 限制輸入正整數
function rtnInt(e, pnumber) {
    if (!/^\d+$/.test(pnumber)) {
        var newValue = /^\d+/.exec(e.value);
        if (newValue != null) {
            e.value = newValue;
        } else {
            e.value = '';
        }
    }
}