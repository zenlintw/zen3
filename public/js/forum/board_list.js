// 點選下半部-討論區列表，前往單一討論區
goNodeList = function() {
	if ($(this).data('bid') == -1) {
		alert(msg['cant_read'][nowlang]);
		return;
	}
    $("form[name='node_list']")
        .prop('action', appRoot + '/forum/m_node_list.php')
        .find("input[name='cid']")
            .val(cid).end()
        .find("input[name='bid']")
            .val($(this).data('bid'));

    $("form[name='node_list']").submit();

    // parent.s_sysbar.goBoard($(this).data('bid'));
}

$(function(){
    // 點選下半部-討論區列表，前往單一討論區
    $('.content>.data2>.subject tr').bind("click", goNodeList);
    
    if (detectIE() === 13) {
        $('.title-bar .subject td').css('border-radius', '0 0 0 0');
    }
});