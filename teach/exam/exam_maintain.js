$(function() {

    // 新增事件
    $('.add').click(function() {
        $('#form1 .lists').val('');
        $('#form1').attr('action', 'exam_create.php');
        $('#form1').submit();
    });

    // 編輯事件
    $('.edit').click(function() {
        var cur = $(this).parent().parent().data('id');
        if (cur === false || cur === '') {
            alert(MSG_SELECT_ONE_ITEM_FIRST);
            return;
        }
        $('#form1 .lists').val(cur);
        $('#form1').attr('action', 'exam_modify.php');
        $('#form1').submit();
    });

    // 刪除事件
    $('.delete').click(function() {
        var cur = $(this).parent().parent().data('id');
        if (cur === false || cur === '') {
            alert(MSG_SELECT_ONE_ITEM_FIRST);
            return;
        }
        if (!confirm(MSG_DELETE_CONFIRM)) return;
        $('#form1 .lists').val(cur);
        $('#form1').attr('action', 'exam_remove.php');
        $('#form1').submit();
    });

    // 清除事件
    $('.clear').click(function() {
        var cur = $(this).parent().parent().data('id');
        if (cur === false || cur === '') {
            alert(MSG_SELECT_ONE_ITEM_FIRST);
            return;
        }
        if (!confirm(MSG_RESET_CONFIRM)) return;
        $('#form1 .lists').val(cur);
        $('#form1').attr('action', 'exam_reset.php');
        $('#form1').submit();
    });
});