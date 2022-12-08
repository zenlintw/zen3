// 展開更多資訊
$('.more-info').on('click', function() {
    $(this).hide();
    $(this).parent().find('.info').fadeIn('slow');
    
    // 設定每個成員高度，以避免同列高度不一樣，下一列元素來補洞，造成版面亂掉
    setHeight($(this), 'auto');
});

// 收合更多資訊
$('.less-info').on('click', function() {
    $(this).parent('.info').hide();
    $(this).parent().parent().find('.more-info').show();
    
    // 設定每個成員高度
    setHeight($(this), '18em');
});

// 前往個人網站
$('.homepage').on('click', function() {
    window.open($(this).data('url'), 'homepage', 'height=500, width=950');
});

// 設定每個成員高度
function setHeight(obj, min_height) {
    var bix_width = $('.box1').width();
    var my_index = $('.col-sm-3').index(obj.parents('.col-sm-3'));
    if (window.console) {
        console.log(obj);
        console.log(my_index);
//        console.log(bix_width);
    }
    if (bix_width > 477.6) {
        var row = Math.floor((my_index + 1) / 4);
        var p1 = $('.col-sm-3').eq(row * 4);
        var p2 = $('.col-sm-3').eq((row * 4) + 1);
        var p3 = $('.col-sm-3').eq((row * 4) + 2);
        var p4 = $('.col-sm-3').eq((row * 4) + 3);
        
        if (window.console) {
            console.log(p1.find('.info').height());
            console.log(p2.find('.info').height());
            console.log(p3.find('.info').height());
            console.log(p4.find('.info').height());
        }
        
        if (min_height === 'auto') {
            min_height = (Math.max(p1.find('.info').height(), p2.find('.info').height(), p3.find('.info').height(), p4.find('.info').height()) + 270) + 'px';
        } 
        
        if (window.console) {
            console.log(min_height);
        }
        
        p1.css('min-height', min_height);
        p2.css('min-height', min_height);
        p3.css('min-height', min_height);
        p4.css('min-height', min_height);
    } else {
        var row = Math.floor((my_index + 1) / 2);
        var p1 = $('.col-sm-3').eq(row * 2);
        if (window.console) {
            console.log(p1);
        }
        var p2 = $('.col-sm-3').eq((row * 2) + 1);
        
        if (window.console) {
            console.log(p1.find('.info').height());
            console.log(p2.find('.info').height());
        }
        
        if (min_height === 'auto') {
            min_height = (Math.max(p1.find('.info').height(), p2.find('.info').height()) + 270) + 'px';
        } 
        
        if (window.console) {
            console.log(min_height);
        }
        
        p1.css('min-height', min_height);
        p2.css('min-height', min_height);
    }
}
