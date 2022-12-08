// 寄信給群組
function mailTo() {
    var obj = document.getElementById('groupMemList');
    var nodes = obj.getElementsByTagName('input');
    var receiver = '';
    for (var i = 1; i < nodes.length; i++) {
        if (nodes[i].type == 'checkbox' && nodes[i].checked) {
            receiver += nodes[i].value + ',';
        }
    }
    if (receiver) {
        obj = document.getElementById('mailForm');
        obj.to.value = receiver.replace(/,$/, '');
        obj.submit();
    } else {
        alert(msg.please_select_member[nowlang]);
    }
}

$(function(){
    // 控制核取方塊
    var class_name = 'member';
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