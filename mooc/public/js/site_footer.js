// 頁尾
// 點選WECHAT圖示
$('.footer #share-wct').fancybox({
    'titlePosition': 'inline',
    'transitionIn': 'none',
    'transitionOut': 'none',
    'type': 'iframe',
    'width': 350,
    'height': 350,
    'autoSize': false,
    helpers : {
        overlay : {
            locked : false
        }
    }
});

// 點選LINE分享圖示
$('.footer .ln').click(function() {

    // 判斷式否為觸控裝置
    var touchable = isTouchDevice();
    if (touchable === false) {
        $(".footer #share-ln").fancybox({
            'titlePosition': 'inline',
            'transitionIn': 'none',
            'transitionOut': 'none',
            helpers : {
                overlay : {
                    locked : false
                }
            }
        });
    } else {
        location.href = 'http://line.naver.jp/R/msg/text/?' + metaDescription + '%0D%0A' + appRoot;
    }
});
$(document).ready(function () {
    // 內容太短，footer會往上縮
    // if ($(window).height()-$('body').height() > 50){
    //     $('.footer').css({position: 'fixed', left: 0, bottom: 0});
    // }
});