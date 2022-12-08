$(document).ready(function() {
    // tab切換css
    $("#set-tab li a").click(function() {
        $("#set-tab li").removeClass('set-li-active');
        $(this).parent().addClass('set-li-active');
    });

    // 預設顯示 課程介紹 頁
    show_page('intro');
    
    // QR Code
    $("#qr-link").fancybox({
        maxWidth: 800,
        maxHeight: 600,
        fitToView: false,
        width: 400,
        height: 400,
        autoSize: false,
        closeClick: false,
        openEffect: 'none',
        closeEffect: 'none'
    });
});

// 顯示tabs
function show_page(tab) {
    $(".course_info").hide();
    $("#" + tab).show();
    
    // 設定老師資訊欄高度
//    if (window.console) {
//        console.log($('#course_detail').height() + 30);
//        console.log($('#teachers-info').height());
//    }
    
    if ($('#course_detail').height() + 30 > $('#teachers-info').height()) {
        $('#teachers-info').css('height', $('#course_detail').height() + 30);
    }
}