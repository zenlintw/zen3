// 前往討論板
goNodeList = function() {
    var cid= $(this).data('cid');
    var bid= $(this).data('bid');

    $("form[name='node_list']")
        .prop('action', '/forum/m_node_list.php')
        .find("input[name='cid']")
            .val(cid).end()
        .find("input[name='bid']")
            .val(bid);

    $("form[name='node_list']").submit();
}

$(function(){
    // 前往討論板
    $('.forum').on("click", goNodeList);
    
    // 人數、寄給組員
    $('.list-peoples').fancybox({
        maxWidth: 800,
        maxHeight: 600,
        fitToView: false,
        width: 770,
        height: 400,
        autoSize: false,
        closeClick: false,
        openEffect: 'none',
        closeEffect: 'none'
    });
    
    if (detectIE() === 13) {
        $('.title-bar .subject td').css('border-radius', '0 0 0 0');
    }
});