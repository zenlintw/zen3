// 是否顯示附註（留言）的開關
note = function() {
    if ($(this).hasClass("narrow")) {
        $(this).removeClass("narrow");

        if ($(this).parent().find('.note').length === 1) {
            var bid = $(this).parents('.reply').data('bid'),
                nid = $(this).parents('.reply').data('nid');

            getWhisper(bid, nid.replace('_', ''));
        }

        $(this).parent().find(".divider-horizontal").show();
        $(this).parent().find(".note").show();

        $(this).find(".whisper-collapse")
            .removeClass('whisper-collapse')
            .addClass('whisper-expand');

        if (postFlag === 0) {
            $("section[class='note']").hide();
            $('.doNote').remove();
        }
    } else {
        $(this).addClass("narrow");
        $(this).parent().find(".divider-horizontal").hide();
        $(this).parent().find(".divider-horizontal").eq(1).show();
        $(this).parent().find(".note").hide();

        $(this).find(".whisper-expand")
            .removeClass('whisper-expand')
            .addClass('whisper-collapse');
    }
};

// 編輯主題、回覆
doEdit = function() {

    // 清空表單
    resetForm($('#formAction'));

    var obj = $(this).parents('.node-info');
    $('#formAction')
        //    043884(B)
        .prop('action', '/forum/m_edit.php?bTicket=' + $('input[name="bTicket"]').val()+'&page='+$('#selectPage').val())
        //    043884        (E)
        .find('input[name="cid"]').val(cid).end()
        .find('input[name="bid"]').val(obj.data('bid')).end()
        .find('input[name="mnode"]').val((obj.data('nid')).substr(1)).end()
        .submit();
}

// 顯示編輯留言區塊
showNoteEdit = function() {

    var obj = $(this).parents('.note').eq(0).find('.content'),
        note = obj.text();

    if ($(obj).find('textarea').length === 0) {
        obj.empty()
            .append('<textarea>'+note+'</textarea>');
        obj.append($('<button class="btn btn-gray doNoteEdit">'+msg['modify_finished'][nowlang]+'</button>').on('click', doNoteEdit));
    } else {
        $(obj).find('button').remove();
        note = obj.text();
        obj.empty()
            .text(note);
    }
}

// 編輯留言
doNoteEdit = function() {

    var wid = $(this).parents('.note').eq(0).data('wid'),
        obj = $(this).parents('.content').eq(0),
        content = obj.find('textarea').val();

    $.ajax({
        'url': '/mooc/controllers/forum_ajax.php',
        'type': 'POST',
        'dataType': 'json',
        'data': {action: 'modWhisper', wid: wid, content: content},
        'success': function (res) {
//            if (window.console) {
//                console.log(res);
//            }
            if (res.code === 1) {

                // 移除TEXTAREA
                obj.empty()
                    .hide()
                    .html(res.data.content).fadeIn('slow');
            } else {
                alert(msgDelFail);
            }
        },
        'error': function () {
            if (window.console) {
                console.log('doNoteEdit Ajax Error!');
            }
        }
    });
};

// 刪除文章
doDelete = function() {

    if (confirm(msgDelPost +'?')) {

        // 往上找有沒有note class，來判斷式否為留言區
        var noteFlag = $(this).parents('.note').length;

        // 留言區
        if (noteFlag === 1) {
            var wid = $(this).parents('.note').eq(0).data('wid');

            $.ajax({
                'url': '/mooc/controllers/forum_ajax.php',
                'type': 'POST',
                'dataType': 'json',
                'data': {action: 'delWhisper', wid: wid},
                'success': function (res) {
//                    if (window.console) {
//                        console.log(res);
//                    }
                    if (res.code === 1) {

                        // 移除畫面指定留言區塊
                        $("div[data-wid='" + wid + "']")
                            .next()
                            .remove();

                        $("div[data-wid='" + wid + "']")
                            .hide('slow', function() {$("div[data-wid='" + wid + "']").remove();});

                        // 減少留言數字
                        var total = $("div[data-wid='" + wid + "']").parents('.bottom-tmp').find('.show-note').find('.total').text();
                        $("div[data-wid='" + wid + "']").parents('.bottom-tmp').find('.show-note').find('.total').text(parseInt(total, 10) - 1);
                    } else {
                        alert(msgDelFail);
                    }
                },
                'error': function () {
                    if (window.console) {
                        console.log('delWhisper Ajax Error!');
                    }
                }
            });
        // 主題區、回覆區
        } else {
            var obj = $(this).parents('.node-info');
            var a = bid + ','+ (obj.data('nid')).substr(1) +',' + obj.data('sid');
//            if (window.console) {
//                console.log(a);
//            }
            $.ajax({'url': '/forum/520,'+ a +'.php',// /forum/delete.php
                    'type': 'POST',
                    'data': {bid: bid},
                    dataType: "text",
                    'success': function () {
                        alert(msgDelSuccess);

                        // 刪除主題回檢視單一討論區，否則原頁面AJAX新資料
                        if ((obj.data('nid')).substr(1).length === 9) {
                            goForum();
                        } else {
                            // 重整
                            doSearch();
                        }
                    },
                    'error': function () {
                        if (window.console) {
                            console.log('delTopic Ajax Error!');
                        }
                    }
            });
        }
    }
}

// 留言
doNote = function() {
    var obj = $(this).parents('.note').eq(0),
        sid = $(obj).parents('.reply').eq(0).data('sid'),
        bid = $(obj).parents('.reply').eq(0).data('bid'),
        nid = $(obj).parents('.reply').eq(0).data('nid').replace('_', ''),
        content = $(obj).find('.content textarea').val(),
        subject = $('.main').attr('data-title');

    // 判斷有無內容
    if (content === '') {
        alert(whisperNothingFail);
        return false;
    }

    $.ajax({
        'url': '/mooc/controllers/forum_ajax.php',
        'type': 'POST',
        'dataType': 'json',
        'data': {action: 'setWhisper', sid: sid, bid: bid, nid: nid, content: content, subject:subject},
        'success': function (res) {
//            if (window.console) {
//                console.log(res);
//            }

            // 留言數增加
            var note_total = $(obj).parent().find('.show-note').eq(0).find('a>.total');
            $(note_total).text(parseInt($(note_total).text(), 10) + 1);

            // 頁面上增加留言，預設隱藏
            obj.before($('<div class="note" style="display: none;" data-wid="' + res.data.wid + '"></div>')
                .append($('<div class="author-pic"></div>')
                    .append($('<div class="photo-s" style="height:auto"></div>')
                        .append('<img class="img-responsive" src="' + appRoot + '/co_showuserpic.php?a=' + res.data.cpic + '" onerror="javascript:this.src=\'' + appRoot + '/theme/default/learn/co_pic.gif\'">')
                    )
                )
                .append($('<div class="top-tmp"></div>')
                    .append($('<div class="author-name"></div>')
                        .text(res.data.creator + ' ( ' + res.data.realname + ' )')
                    )
                    .append($('<div class="post-time" title="' + msg['post_time'][nowlang] + '：' + res.data.create_time + '"></div>')
                        .text(res.data.create_time)
                    )
                    .append($('<div class="operate"></div>')
                        .append('<div class="icon-edit" title="' + msg['btn_edit'][nowlang] + '"></div>')
                        .append('<div class="icon-delete" title="' + msg['del'][nowlang] + '"></div>')
                    )
                )
                .append($('<div class="bottom-tmp"></div>')
                    .append($('<div class="content"></div>')
                        .html(res.data.content)
                    )
                )
            )
            .before('<div class="divider-horizontal" style="display: none;"></div>');


            // 清空留言輸入框
            $(obj).find('.content textarea').val('');

            // 顯示留言
            var newObj = $("div[data-wid='" + res.data.wid + "']");
            $(newObj)
                .fadeIn('slow')
                .next()
                .fadeIn('slow');

            // 圖示綁定事件（讚、分享、寄信、編輯、刪除）
            $(newObj).find('.icon-edit').on('click', showNoteEdit);
            $(newObj).find('.icon-delete').on('click', doDelete);
        },
        'error': function () {
            if (window.console) {
                console.log('doNote Ajax Error!');
            }
        }
    });
}

// 轉貼文章
doRepost = function() {
    var obj = $(this).parents('.node-info').eq(0),
        sid = $(obj).data('sid'),
        bid = $(obj).data('bid'),
        nid = $(obj).data('nid').replace('_', ''),
        to_sid = $(obj).data('sid'),
        to_cid = $('#to_cid').val(),
        to_bid = $('#to_bid').val();

    if (window.console) {console.log('to_bid', to_bid);}
    if (to_bid.length !== 12) {
        return;
    }

//    if (window.console) {console.log(sid, bid, nid, to_sid, to_cid, to_bid);}

    // 進行轉貼
    $.ajax({
        'url': '/mooc/controllers/forum_ajax.php',
        'type': 'POST',
        'dataType': 'json',
        'data': {action: 'setRepost', sid: sid, bid: bid, nid: nid, to_sid: to_sid, to_cid: to_cid, to_bid: to_bid},
        'success': function (res) {
//            if (window.console) {
//                console.log(res);
//            }

            if (res.code === 1) {
                $('.alert-success').show();
                $('.alert-danger').hide();
            } else {
                $('.alert-danger').show();
                $('.alert-success').hide();
            }
            $('#repost').prop('disabled', true);
        },
        'error': function () {
            if (window.console) {
                console.log('doNote Ajax Error!');
            }
        }
    });
};

function getWhisper(bid, nid) {
    // 取附註內容
    $.ajax({
        'url': '/mooc/controllers/forum_ajax.php',
        'type': 'POST',
        'dataType': 'json',
        'data': {action: 'getWhisper', bid: bid, nid: nid},
        'success': function (res) {
//            if (window.console) {
//                console.log(res);
//            }
            var k = bid + '|' + nid;
            if (res[k]) {
                showWhisper(k, res[k]);
            }
        },
        'error': function () {
            if (window.console) {
                console.log('getWhisper Ajax Error!');
            }
        }
    });
}

// 回覆文章分頁資料搜尋
function doSearch() {
    //    043884 (B)
    if(page>0){
        $('#selectPage').val(page);
    }
    //    043884 (E)
    var
        pageSetStr,
        selectPage = $('#selectPage').val(),
        inputPerPage = 10,
        nid;

    if (getURLParameter('nid') === null) {
        nid = $('.main').data('nid').replace('_', '');
    // 檢視單一回覆
    } else {
        nid = getURLParameter('nid');
    }

    pageSetStr = '&action=getReply&bid=' + $('.main').data('bid') + '&nid=' + nid + '&selectPage=' + selectPage + '&inputPerPage=' + inputPerPage;
//    if (window.console) {
//        console.log(pageSetStr);
//    }

    $.ajax({
        'url': '/mooc/controllers/forum_ajax.php',
        'type': 'POST',
        'dataType': 'json',
        'data': pageSetStr,
        'success': function (res) {
//            if (window.console) {
//                console.log(res);
//            }

            // 無資料
            if (res.code === -1 || res.total_rows === '0') {
                $('.message').empty();
                $('.reply').remove();

                $('#searchResult')
                .append($('<div></div>')
                    .append($('<div class="bottom-tmp"></div>')
                        .append($('<div class="content">'+msg['no_reply'][nowlang]+'</div>')
                        )
                    )
                );
            } else {
                var bnid = $('.main').data('bid') + '|' + nid;

//                if (window.console) {
//                    console.log(res[bnid].data);
//                }
                // 有資料
                showSearchData(res[bnid].data);

                $('#pageToolbar').show();

                // 主題文章內容收摺或展開
                if (selectPage >= 2) {
                    $('.main').find('.bottom-tmp .content').addClass('disappear');
                    $('.show-content').show();
                } else {
                    $('.main').find('.bottom-tmp .content').removeClass('disappear');
                    $('.show-content').hide();
                }
            }

            if (parseInt(res.total_rows, 10) > parseInt(res.limit_rows, 10)) {
                // 如果點選主題才更新分頁工具列
                // $('#pageToolbar').paginate('refresh', {
                        // 'total': res.total_rows,
                        // 'pageSize': res.limit_rows
                    // });

                //    043884 (B)
                if(page>0){
                    $('#pageToolbar').paginate('refresh', {
                        'total': res.total_rows,
                        'pageSize': res.limit_rows,
                        'pageNumber':page
                    });
                    page=0;
                }else{

                    $('#pageToolbar').paginate('refresh', {
                        'total': res.total_rows,
                        'pageSize': res.limit_rows
                    });
                }
                //    043884 (E)

            } else {
                $('#pageToolbar').hide();
            }

            // 檢視更多回覆
            var message = $('.message').text().match(/\d+/),
                total_rows = 0;
            if (message) {
                total_rows = message[0];
            }

            if (!(getURLParameter('nid') === null) && total_rows >= 2) {
                $('#searchResult').
                    append($('<div class="more"></div>')
                        .text('(↓) ' + msg['view_more_replies'][nowlang])
                );

                // 查看更多回覆
                $('#searchResult .more').click(function() {

                    // 清空表單
                    resetForm($('#formAction'));

                    $('#formAction').prop('action', '/forum/m_node_chain.php');
                    $('#formAction').find('input[name="bid"]').val($('.main').data('bid'));
                    $('#formAction').find('input[name="nid"]').val($('.main').data('nid').replace('_', ''));

                    $('#formAction').submit();
                });
            }
        },
        'error': function () {
            $('#pageToolbar').paginate('refresh', {
                'total': 0
            });
            if (window.console) {
                console.log('doSearch Ajax Error!');
            }
        }
    });
}

function showSearchData(data) {

    // 清空舊資料
    $('.reply').remove();

    var fbFlag = (socialShare.indexOf("FB") === -1) ? "display: none;":"",
        plkFlag = (socialShare.indexOf("PLURK") === -1) ? "display: none;":"",
        twFlag = (socialShare.indexOf("TWITTER") === -1) ? "display: none;":"",
        lnFlag = (socialShare.indexOf("LINE") === -1) ? "display: none;":"",
        wctFlag = (socialShare.indexOf("WECHAT") === -1) ? "display: none;":"";

    $.each(data, function (key, value) {
		/*過濾APP加的whisper*/
		if(key==""){
			return;
		}

        // 附件檔
        var fileFlag = "display: none;";
        if (value.postfilelink === null) {
            value.postfilelink = '&nbsp;';
            fileFlag = "display: none;";
        } else {
            fileFlag = "";
        }

        // 登入者有沒有按過讚
        var iconLikeClass = "icon-unlike";
        var titleLikeClass = msg['push'][nowlang];
        if (value.pushflag === '1') {
            iconLikeClass = "icon-like";
            titleLikeClass = msg['cancel'][nowlang] + msg['push'][nowlang];
        }

        if (postFlag === 1) {
            // 按讚數
            if (!value.push) {
                value.push = '0';
            }
        }

        // 留言數
        if (!value.whispercnt) {
            value.whispercnt = 0;
        }

        // 有無權限（作者、特權）
        if ((username === value.poster && username !== 'guest') || updRight === '1' && username !== 'guest') {
            var func = '<div class="icon-edit" title="' + msg['btn_edit'][nowlang] + '"></div><div class="icon-delete" title="' + msg['del'][nowlang] + '"></div>';
        }

        var url = encodeURIComponent(encodeURIComponent(appRoot + '/forum/m_node_chain.php?bid=' + value.boardid + '&nid=' + value.n));

        // 社群分享
        var iconShare = '';
        if (socialShare.length >= 1) {
            iconShare = '<div class="icon-share" title="' + msg['mooc_share'][nowlang] + '"></div>';
        }

        // 回覆文章
        $('#searchResult')
        .append($('<div class="reply node-info" data-sid="' + value.s + '" data-bid="' + value.boardid + '" data-nid="_' + value.n + '" data-encnid="' + value.encnid + '"></div>')
            .append($('<div class="author-pic"></div>')
                .append($('<div class="photo-l"></div>')
                    .append('<img src="' + appRoot + '/co_showuserpic.php?a=' + value.cpic + '" onerror="javascript:this.src=\'' + appRoot + '/theme/default/learn/co_pic.gif\'">')
                )
            )
            .append($('<div class="top-tmp"></div>')
                .append($('<div class="author-name"></div>')
                    .text(value.poster + ' ( ' + value.realname + ' )')
                )
                .append($('<div class="post-time" title="' + msg['post_time'][nowlang] + '：' + value.postdate + '"></div>')
                    .text(value.postdate)
                )
                .append($('<div class="operate"></div>')
                    .append($('<div class="default"></div>')
                        .append($('<div class="like" style="line-height: 1em;"></div>')
                            .append('<div class="' + iconLikeClass + '" title="' + titleLikeClass + '"></div>')
                            .append($('<div style="display:inline-block;" class="cnt"></div>')
                                .text(value.push)
                            )
                        )
                        .append(iconShare)
                        .append('<div class="icon-mailto" title="' + msg['send_mail'][nowlang] + '"></div>')
                        .append(func)
                        .append('<div class="floor" title="' + msg['floor_number'][nowlang] + '">#' + value.floor + msg['floor'][nowlang] + '</div>')
                    )
                    .append($('<div class="share" style="display: none;"></div>')
                        .append($('<div class="pic" style="' + fbFlag + '"></div>')
                            .append($('<a href="javascript: void(window.open(\'http://www.facebook.com/share.php?u=\'.concat(encodeURIComponent(\'' + appRoot + '/forum/m_node_chain.php?bid=' + value.boardid + '&nid=' + value.n + '\'))));"></a>')
                                .append('<div class="fb"></div>')
                            )
                        )
                        .append($('<div class="pic" style="' + plkFlag + '"></div>')
                            .append($('<a href="javascript: void(window.open(\'http://www.plurk.com/?qualifier=shares&status=\'.concat(encodeURIComponent(\'\')).concat(\' \').concat(encodeURIComponent(\'' + appRoot + '/forum/m_node_chain.php?bid=' + value.boardid + '&nid=' + value.n + '\'))));"></a>')
                                .append('<div class="plk"></div>')
                            )
                        )
                        .append($('<div class="pic" style="' + twFlag + '"></div>')
                            .append($('<a href="javascript: void(window.open(\'http://twitter.com/home/?status=\'.concat(encodeURIComponent(\'\')) .concat(\' \').concat(encodeURIComponent(\'' + appRoot + '/forum/m_node_chain.php?bid=' + value.boardid + '&nid=' + value.n + '\'))));"></a>')
                                .append('<div class="tw"></div>')
                            )
                        )
                        .append($('<div class="pic" style="' + lnFlag + '"></div>')
                            .append($('<a id="share-ln-' + value.n + '" href="#inline-ln-' + value.n + '" title="' + msg['attension'][nowlang] + '"></a>')
                                .append('<div class="ln"></div>')
                            )
                        )
                        .append($('<div class="pic" style="' + wctFlag + '"></div>')
                            .append($('<a id="share-wct-' + value.n + '" data-fancybox-type="iframe" href="' + value.qrcode_url + '"  title="' + msg['open_wct'][nowlang] + '"></a>')
                                .append('<div class="wct"></div>')
                            )
                        )
                    )
                    .append($('<div id="inline-ln-' + value.n + '" class="inline-ln" style="display: none;">')
                        .append($('<form class="well"></form>')
                            .append('<div>' + msg['line_supports_mobile'][nowlang] + '</div>')
                        )
                    )

                )
            )
            .append($('<div class="bottom-tmp"></div>')
                .append($('<div class="content"></div>')
                    .html(value.postcontent)
                )
                .append($('<div class="file" style="' + fileFlag + '"></div>')
                    .append('<div>' + msg['attached_file'][nowlang] + '</div>')
                    .append($('<div></div>')
                        .append(value.postfilelink)
                    )
                )
                .append($('<div class="show-note narrow"></div>')
                    .append($('<a href="javascript:;"></a>').
                        html('<span class="whisper-collapse"></span><span class="total">' + value.whispercnt + '</span> ' + msg['notes'][nowlang])
                    )
                )
                .append('<div class="divider-horizontal"></div>')
                .append($('<section class="note" style="display: none;"></section>')
                    .append($('<div class="author-pic"></div>')
                        .append($('<div class="photo-s" style="height:auto"></div>')
                            .append('<img class="img-responsive" src="' + appRoot + '/co_showuserpic.php?a=' + loginCpic + '" onerror="javascript:this.src=\'' + appRoot + '/theme/default/learn/co_pic.gif\'">')
                        )
                    )
                    .append($('<div class="top-tmp"></div>')
                        .append($('<div class="author-name"></div>')
                            .text(username + ' ( ' + realname + ' )')
                        )
                    )
                    .append($('<div class="bottom-tmp"></div>')
                        .append($('<div class="content"></div>')
                            .append('<textarea name="user-reply"></textarea>')
                        )
                        .append($('<button class="btn btn-gray doNote"></button>')
                            .text(msg['leave'][nowlang])
                        )
                    )
                )
            )
        );
    });

    $("img").addClass('img-responsive');

    // 開啟或關閉留言
    $('#searchResult').find('.show-note').on("click", note);

    // 須指定那個分頁，以避免相同名稱被BIND多次
//    $('#searchResult').find('.icon-like, .icon-unlike').bind("click", doPush);

    // 圖示綁定事件（讚、分享、寄信、編輯、刪除）
    $('#searchResult .icon-like, #searchResult .icon-unlike').bind("click", doPush);
    $('#searchResult .icon-mailto').on('click', doEmail);
    $('#searchResult .icon-edit').on('click', doEdit);
    $('#searchResult .icon-delete').on('click', doDelete);

    // LINE 分享
    $('.ln').click(lineShare);

    // 留言
    $('.doNote').on('click', doNote);

    /* 處理 frame 內超連結無法連結外部網路問題 */
    $('#searchResult .content').find("a").attr('target', '_blank');

    //查看全部切換功能
    // $('.show-content').text(msg['view_all'][nowlang]);


}


function showWhisper(k, data) {

    $.each(data, function (key, value) {
        var bnid = k.split('|'),
            obj = $("div[data-bid=" + bnid[0] + "][data-nid=_" + bnid[1] + "]").find('.show-note');

        // 有無權限（作者、特權）
        if ((username === value.creator && username !== 'guest') || updRight === '1' && username !== 'guest') {
            var func = '<div class="icon-edit" title="' + msg['btn_edit'][nowlang] + '"></div><div class="icon-delete" title="' + msg['del'][nowlang] + '"></div>';
        }

        obj.after($('<div class="note" data-wid="' + value.wid + '"></div>')
                .append($('<div class="author-pic"></div>')
                    .append($('<div class="photo-s" style="height:auto"></div>')
                        .append('<img class="img-responsive" src="' + appRoot + '/co_showuserpic.php?a=' + value.cpic + '" onerror="javascript:this.src=\'' + appRoot + '/theme/default/learn/co_pic.gif\'">')
                    )
                )
                .append($('<div class="top-tmp"></div>')
                    .append('<div class="author-name">' + value.creator + '(' + value.creator_realname + ')</div>')
                    .append($('<div class="post-time" title="' + msg['post_time'][nowlang] + '：' + value.create_time + '"></div>')
                        .text(value.create_time)
                    )
                    .append($('<div class="operate"></div>')
                        .append(func)
                    )
                )
                .append($('<div class="bottom-tmp"></div>')
                    .append($('<div class="content"></div>')
                        .html(value.content)
                    )
                )
            )
            .after('<div class="divider-horizontal"></div>'
        );
    });

    // 指定該留言區所有圖示綁定事件（編輯、刪除）
    $("div[data-nid='_" + data[0].node + "']").find('.note')
        .find('.icon-edit').on('click', showNoteEdit)
        .end()
        .find('.icon-delete').on('click', doDelete);

    // 移除圖示
    if (username >= '0' && username === 'guest') {

        // 按讚
        $('.like').remove();

//        // 留言
//        $("section[class='note']").remove();

        // 留言功能區
        $('.note .operate').remove();
    }
}

function goAnotherTopic(cid, bid, nid) {
    $('#formAction').attr('action', 'm_node_chain.php');
    var oForm = document.getElementById('formAction');
    oForm.cid.value = cid;
    oForm.bid.value = bid;
    oForm.nid.value = nid;
    oForm.submit();
}

$(function () {
    // 分頁工具列
    $('#pageToolbar').paginate({
        'total': 0,
        'showPageList': false,
        'showRefresh': false,
        'showSeparator': false,
        'btnTitleFirst': btnTitleFirst,
        'btnTitlePrev': btnTitlePrev,
        'btnTitleNext': btnTitleNext,
        'btnTitleLast': btnTitleLast,
        'btnTitleRefresh': btnTitleRefresh,
        'beforePageText': beforePageText,
        'afterPageText': afterPageText,
        'beforePerPageText': beforePerPageText,
        'afterPerPageText': afterPerPageText,
        'displayMsg': displayMsg,
        'buttonCls': '',
        'onSelectPage': function (num, size) {
            $('#selectPage').val(num);
            doSearch();
            // scroll artical top
            $('html,body').animate({
                    scrollTop: $(".box1").offset().top
                }, 0
            );
        }
    });

    $('.show-content').click(function() {
            if ($(this).parent().find('.content').hasClass('disappear')) {
                $(this).parent().find('.content').removeClass('disappear');
                $(this).text(msg['view_part'][nowlang]);
            } else {
                $(this).parent().find('.content').addClass('disappear');
                $(this).text(msg['view_all'][nowlang]);
            }
    });

    $('#pageToolbar').paginate('select', 1);

    // 回覆主文按鈕
    $('.doReply').on('click', doReply);

    // 轉貼
    $('#repost').on('click', doRepost);

    // 轉貼-選擇課程
    $('#to_cid').on('change', getCourseBids);

    // 圖示綁定事件（讚、分享、寄信、編輯、刪除）
    $('.icon-like, .icon-unlike').bind("click",doPush);
    $('.icon-mailto').on('click', doEmail);
    $('.icon-edit').on('click', doEdit);
    $('.icon-delete').on('click', doDelete);

    /* 處理 frame 內超連結無法連結外部網路問題 */
    $('.data3 .content').find("a").attr('target', '_blank');

    /* 提供 mp3 直接播放 */
    if (Modernizr.audio.mp3) {
        if ($('.attach-file-link').length > 0) {
            var attachHref = '';
            for(var i=0; i<$('.attach-file-link').length; i++) {
                attachHref = $('.attach-file-link')[i].href.toLocaleLowerCase();
                if ((attachHref.substr(attachHref.length-4) == '.mp3')) {
                    $('.filePlayer').eq(i).html('&nbsp;&nbsp;<audio controls><source src="'+$('.attach-file-link')[i].href+'" type="audio/mpeg"></audio>');
                }
            }
        }
    }

    // 關閉轉貼視窗
    $('#repostModal #close').on('click', function () {
        $('.alert-danger').hide();
        $('.alert-success').hide();

        $('#repost').prop('disabled', false);
    })
});

// 寄信
doEmail = function(obj) {
    obj.disabled = true;

    var newEmail = prompt(MSG_EMAIL, email);

    // chrome 回傳 null、safari 回傳 空值
    if (newEmail == null || newEmail == '') {
        obj.disabled = false;
        return;
    }

    if (!sysMailsRule.test(newEmail)) {
        alert('E-mail format error !');
        obj.disabled = false;
        return;
    }

    // 改用 ajax 與 alert
//    url = 'mail.php?cid=' + cid + '&bid=' + $(this).parents('.node-info').data('bid') + '&node=' + $(this).parents('.node-info').data('nid').substr(1) + '&target=' + newEmail;
    url = appRoot + '/forum/' + 'mail.php?cid=' + cid + '&bid=' + $(this).parents('.node-info').data('bid') + '&node=' + $(this).parents('.node-info').data('nid').substr(1) + '&target=' + newEmail;

    $.ajax({
        'url': url,
        'type': 'POST',
        'dataType': 'json',
        'success': function (res) {
            alert(res);
        },
        'error': function () {
            if (window.console) {
                console.log('email Ajax Error!');
            }
        }
    });
//    showDialog(url, false, window, true, 0, 0, '240px', '100px', 'resizable=0, scrollbars=0, status=0');
    obj.disabled = false;
};

getCourseBids = function() {
    // 取課程討論版列表
    $.ajax({
        'url': '/mooc/controllers/forum_ajax.php',
        'type': 'POST',
        'dataType': 'json',
        'data': {action: 'getCourseForumList', cid: $(this).val()},
        'success': function (res) {
//            if (window.console) {
//                console.log(res);
//            }

            $('#to_bid option').remove();

            // 課程無討論版時
            if (res.length === 0) {
                // 關閉按鈕
                $('#repost').prop('disabled', true);
            } else {
                $.each(res, function(index, obj){
                    $('#to_bid').append($('<option>', {
                        value: index,
                        text : obj['board_name']
                    }));
                });

                // 開放按鈕
                $('#repost').prop('disabled', false);
            }
        },
        'error': function () {
            if (window.console) {
                console.log('getCourseBids Ajax Error!');
            }
        }
    });
};
