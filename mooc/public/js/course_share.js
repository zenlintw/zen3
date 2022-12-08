// 課程社群分享
$(document).click(function(event) {
    obj = event.srcElement ? event.srcElement  : $(event.target);
    if ($(obj).prop('class') === 'push') {
        $('.lcms-item>.author').show();
        $('.lcms-item>.share').hide();
        $(obj).parent().parent().children('.author').hide();
        $(obj).parent().parent().children('.share').fadeIn('slow');
    } else {
        $('.lcms-item>.share').hide();
        $('.lcms-item>.author').fadeIn();
    }
});

// 捲動捲軸觸發社群分享關閉
$(document).scroll(function() {
    $('.lcms-item>.share').hide();
    $('.lcms-item>.author').fadeIn();
});

// 點選課程WECHAT圖示
$('.lcms-item a[id^=share-wct-],.qrcode a[id^=share-wct-]').fancybox({
    maxWidth	: 800,
    maxHeight	: 600,
    fitToView	: false,
    /*width	: 400,
    height	: 400,*/
    autoSize	: false,
    closeClick	: false,
    openEffect	: 'none',
    closeEffect	: 'none',
    'beforeShow': function () {
        this.width = ($('.fancybox-iframe').contents().find('img').width())+20;
        this.height = ($('.fancybox-iframe').contents().find('img').height())+20;
    }
});

// 點選課程LINE分享圖示
var lineShare = function(){
    // 判斷式否為觸控裝置
    var touchable = isTouchDevice();
    if (touchable === false) {
        $('a[id^=share-ln-]').fancybox({
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
        var title = $(this).parent().parent().parent().parent().find('.title>div').text();
        var description = $(this).parent().parent().parent().parent().find('.push').data('description');

        if (title) {
            var url = title + '%0D%0A' + description + '%0D%0A' + appRoot + '/info/' + $(this).parent().parent().parent().parent().find('.push').data('id') + '?lang=' + nowlang;
        } else {
            var url = appRoot + '/info/' + csid;
        }
        top.location.href = 'http://line.naver.jp/R/msg/text/?' + url;
    }
}

$('.lcms-item .ln,.qrcode .ln').click(lineShare);