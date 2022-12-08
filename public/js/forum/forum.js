// 讚被點選後的事件
doPush = function(e) {
    e.stopPropagation();

    if (username === 'guest') {
        return false;
    }

//    if (window.console) {
//        console.log('push event');
//    }

    var obj = $(this);
    $(obj).parents('.operate').find('.like').attr('disabled', true);
//        console.log(obj);
//    }

    var bid = obj.parents('.node-info').data('bid'),
        nid = obj.parents('.node-info').data('nid').replace('_', ''),// $nid 會被自動轉成數字，所以前面有加底線
        sid = obj.parents('.node-info').data('sid'),
        first = '0';

    // 未讚
    if (obj.hasClass('icon-unlike')) {
        first = '1';
    } else {
        first = '0';
    }

    $.ajax({
        'url':      "/mooc/controllers/forum_ajax.php",
        'type':     'POST',
        'dataType': "json",
        'async': false,
        'data':      {action: "setPush", bid: bid, nid: nid, sid: sid, firstPush: first},
        'success': function(res) {
            if (res.code === 1) {
                var push_cnt;
                if (first === '1') {
                    obj.hide();// 針對按鈕
                    push_cnt = (obj.parents('.node-info').find('.like').find('.cnt').text() === '') ? 0 : parseInt(obj.parents('.node-info').find('.like').find('.cnt').text());
                    obj.parents('.node-info').find('.like').find('.cnt').text(push_cnt + 1);// 讚數+1
                    obj.parents('.node-info').find('.icon-unlike').prop('title', msg['cancel'][nowlang] + msg['push'][nowlang]);// 變更圖示
                    obj.parents('.node-info').find('.icon-unlike').prop('class', 'icon-like');// 變更圖示
                    obj.parents('.node-info').find('.like').show().find('.icon-like').show();// 顯示圖示
                } else {
                    push_cnt = (obj.next('.cnt').text() === '') ? 0 : parseInt(obj.next('.cnt').text());
                    obj.next('.cnt').text(push_cnt - 1);
                    // obj.prev('.like').find('span').text(push_cnt - 1);
//                    if (push_cnt - 1 === 0) {
//                        // 顯示按鈕
//                        obj.parent().next('.first-push').show();
//                        obj.parent().hide();
//                    } else {
                        obj.prop('class', 'icon-unlike');
                        obj.prev('.like').show();
                        obj.prop('title', msg['push'][nowlang]);
//                    }
                }
            } else {
                alert(msg['have_pushed'][nowlang]);
            }
            $(obj).parents('.operate').find('.like').attr('disabled', false);
        },
        'error': function() {
            alert('push Ajax Error.');
        }
    });
}

// 回覆文章
doReply = function(e) {
    e.stopPropagation();

    var obj = $('.main');

    // 清空表單
    resetForm($('#formAction'));

    $('#formAction').prop('action', '/forum/m_reply.php?bTicket=' + $('input[name="bTicket"]').val());
    $('#formAction').find('input[name="cid"]').val(cid);
    $('#formAction').find('input[name="bid"]').val(obj.data('bid'));
    $('#formAction').find('input[name="mnode"]').val((obj.data('nid')).substr(1));
    $('#formAction').find('input[name="subject"]').val(obj.attr('data-title'));
//    $('#formAction').find('input[name="content"]').val('At ' + obj.find('.top-tmp .post-time').prop('title').match(/[0-9- :]{19}/) + ' ' + trim(obj.find('.top-tmp .author-name').text()) + ' wrote :<br />&nbsp;' + obj.find('.bottom-tmp .content').html());
    $('#formAction').find('input[name="awppathre"]').val('');

    $('#formAction').submit();
}

// 回到首頁
goHome = function(e) {
    // 清空表單
    resetForm($('#formAction'));

    var page = '/';

    location.href = page;
}

// 回到討論表列表
goBoardList = function(e) {
    // 清空表單
    resetForm($('#formAction'));

    var page = '/forum/m_board_list.php';

    $('#formAction').prop('action', page);
    $('#formAction').find('input[name="cid"]').val(cid);

    $('#formAction').submit();
}

//回到分組討論表列表
goGroupBoardList = function(e) {
    // 清空表單
    resetForm($('#formAction'));

    var page = '/learn/group/group_list.php';

    $('#formAction').prop('action', page);
    $('#formAction').find('input[name="cid"]').val(cid);

    $('#formAction').submit();
}

// 回到單一討論版
goForum = function(e) {
    // 清空表單
    resetForm($('#formAction'));

    // 判斷是否為公告版
    var page = '/forum/m_node_list.php';

    $('#formAction').prop('action', page);
    $('#formAction')
        .find('input[name="cid"]').val(cid).end()
        .find('input[name="bid"]').val(bid).end()
        .find('input[name="selectPage"]').val(selectPage);

    $('#formAction').submit();
}

// 回到討論表列表
$('.bread-crumb .home').click(goHome);

// 回到討論表列表
$('.bread-crumb .path').click(goBoardList);

//回到討論表列表
$('.bread-crumb .pathGroup').click(goGroupBoardList);

// 回到單一討論版
$('.bread-crumb .path2').click(goForum);

// 文章社群分享
$(document).click(function(event) {
    var obj = event.srcElement ? event.srcElement  : $(event.target);
    var like = $('.operate>.like').find('span');
    var j = like.length;

    if ($(obj).prop('class') === 'icon-share') {

        // 全頁按鈕先回復沒有點選的狀態
        // 按讚
//        for (i = 0; i < j; i = i + 1)
//        {
//            if (like.eq(i).text() === '0') {
//                $('.operate>.like').eq(i).hide();
//                $('.operate>.first-push').eq(i).show();
//            } else {
//                $('.operate>.like').eq(i).show();
//                $('.operate>.first-push').eq(i).hide();
//            }
//        }
        $('.operate>.default').show();

//        $("div[class^='icon-']").show();
        $('.operate>.share').hide();

        // 隱藏功能列、顯示社群分享
//        $(obj).parents('.operate').find('.like').hide();
//        $(obj).parents('.operate').find('.first-push').hide();
//        $(obj).parents('.operate').find("div[class^='icon-']").hide();
        $(obj).parents('.operate').find('.default').hide();
        $(obj).parents('.operate').find('.share').fadeIn('slow');
    } else {
        $('.operate>.default').fadeIn();
        // 隱藏社群分享
        $('.operate>.share').hide();

//        // 按讚
//        for (i = 0; i < j; i = i + 1)
//        {
//            if (like.eq(i).text() === '0') {
//                $('.operate>.like').eq(i).hide();
//                $('.operate>.first-push').eq(i).fadeIn();
//            } else {
//                $('.operate>.like').eq(i).fadeIn();
//                $('.operate>.first-push').eq(i).hide();
//            }
//        }

        // 顯示功能列
//        $('.operate').find("div[class^='icon-']").fadeIn();
    }
});

// 捲動捲軸觸發社群分享關閉
$(document).scroll(function() {
    $('.lcms-item>.share').hide();
    $('.lcms-item>.author').fadeIn();
});

// 點選課程WECHAT圖示
$('a[id^=share-wct-]').fancybox({
    maxWidth	: 800,
    maxHeight	: 600,
	fitToView	: false,
    /*width	: 450,
    height	: 450,*/
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
        var description = $(this).parent().parent().parent().parent().find('.icon-share').data('description');
        var url = title + '%0D%0A' + description + '%0D%0A' + appRoot + '/info/' + $(this).parent().parent().parent().parent().find('.push').data('id') + '?lang=' + nowlang;
        top.location.href = 'http://line.naver.jp/R/msg/text/?' + url;
    }
}

$('.node-info .ln').click(lineShare);

// 取得 URL 上的參數
function getURLParameter(name) {
  return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null
}

function resetForm(obj) {
    obj.find('input').each(function(){
        $(this).val('');
    });
}

/* 訂閱 */
function doSubscribe() {

    $.ajax({
        'url': '/forum/subscribe.php',
        'type': 'POST',
        'data': {bid: bid},
        'dataType': "text",
        'success': function () {
            if ($('#subscribe').text().trim() === msg['subscribe'][nowlang].trim()) {
                $('#subscribe').text(msg['unsubscribe'][nowlang]);
                alert(msg['successful'][nowlang] + msg['subscribe'][nowlang]);
            } else {
                $('#subscribe').text(msg['subscribe'][nowlang]);
                alert(msg['successful'][nowlang] + msg['unsubscribe'][nowlang]);
            }
        },
        'error': function () {
            alert($('#subscribe').text() + msg['failed'][nowlang]);
        }
    });
}

if (parent.parent.document.getElementById('s_sysbar')) {
    $('.bread-crumb .home, .bread-crumb .home+span').hide();
}