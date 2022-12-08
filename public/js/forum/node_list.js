// 按讚
$(function(){
    $('.icon-like, .icon-unlike').bind("click",doPush);
    $('.special tr,.subject tr').bind("click", read);
});

// 張貼
doPost = function() {

    var obj = $('#formAction');

    // 清空表單
    resetForm(obj);

    $(obj).prop('action', '/forum/m_write.php?bTicket=' + ticket)
        .find('input[name="cid"]').val(cid)
        .end()
        .find('input[name="bid"]').val(bid)
        .end()
        .submit();
}

// 點選文章
read = function() {

    var bid = $(this).data('bid'),
        nid = $(this).data('nid').replace('_', '');
    var nowpage = $('.paginate-number').val();

    if ($(this).data('reply') === 1) {
        $('#formAction')
            .find("input[name='bid']")
                .remove().end()
            .find("input[name='nid']")
                .remove().end()
            .prop('action', appRoot + '/forum/m_node_chain.php?bid=' + bid + '&nid=' + nid + '&nowpage=' + nowpage);
    } else {
        $('#formAction')
            .prop('action', appRoot + '/forum/m_node_chain.php')
            .find("input[name='cid']")
                .val(cid).end()
            .find("input[name='bid']")
                .val(bid).end()
            .find("input[name='nid']")
                .val(nid).end()
            .find("input[name='nowpage']")
                .val(nowpage);
    }

//    $("form[name='node']")
//        .prop('action', appRoot + '/forum/m_node_chain.php')
//        .find("input[name='bid']")
//            .val(bid).end()
//        .find("input[name='nid']")
//            .val(nid);

    $('#formAction').submit();
};

// 切換分頁
$('.select-show').click(function() {
    $(this).parent().parent().find('li').removeClass('active');
    $(this).parent().addClass('active');
    $(this).parent().parent().parent().find('table').hide();

    // 記錄目前點到的分頁ID
    var specialTable = $(this).data('id');
    $('#curtab').val(specialTable);
    
//    var obj = $($(this).data('id'));
//    console.log(obj);
//    console.log(obj.data('firstpage'));
//    console.log(obj.data('prevpage'));
//    console.log(obj.data('nextpage'));
//    console.log(obj.data('lastpage'));

    if ($(specialTable).find('tbody').html() === null) {

        $('#pageToolbar').paginate('select', 1);

    } else {
        // 取該分頁當時的分頁資訊 設定在 paginate 工具列中
        var obj = $($(this).data('id'));
        $('.paginate-number').val(obj.data('curpage'));
        if (obj.data('firstpage') === true) {
            $('.paginate-first').parent().addClass('disabled');
            $('.paginate-first').parent().prop('disabled', true);
        } else {
            $('.paginate-first').parent().removeClass('disabled');
            $('.paginate-first').parent().prop('disabled', false);
        }
        if (obj.data('prevpage') === true) {
            $('.paginate-prev').parent().addClass('disabled');
            $('.paginate-prev').parent().prop('disabled', true);
        } else {
            $('.paginate-prev').parent().removeClass('disabled');
            $('.paginate-prev').parent().prop('disabled', false);
        }
        if (obj.data('nextpage') === true) {
            $('.paginate-next').parent().addClass('disabled');
            $('.paginate-next').parent().prop('disabled', true);
        } else {
            $('.paginate-next').parent().removeClass('disabled');
            $('.paginate-next').parent().prop('disabled', false);
        }
        if (obj.data('lastpage') === true) {
            $('.paginate-last').parent().addClass('disabled');
            $('.paginate-last').parent().prop('disabled', true);
        } else {
            $('.paginate-last').parent().removeClass('disabled');
            $('.paginate-last').parent().prop('disabled', false);
        }
    }

    $($(this).data('id')).show();
});

// 主題文章分頁資料搜尋
function doSearch() {
    if($('#nowpage').val()>0) {
        $('#selectPage').val($('#nowpage').val());
    }
    
    var
        pageSetStr,
        selectPage = $('#selectPage').val(),
        inputPerPage = $('#inputPerPage').val(),
        specialTable,
        keyword = '';

    // 是否啟用關鍵字查詢
    if ($('.search-form').data('refer')) {
        keyword = $('.search-keyword').val();
    }

    // 取目前active tab
    if ($('#curtab').val() === '') {
        specialTable = $('.subject').prev().find('.active').find('a').data('id');
    } else {
        specialTable = $('#curtab').val();
    }

    // 取動作、是否僅顯示主題
    var action, onlyTopic = '0';
    switch (specialTable) {
//    case '#news-art':
//            action = 'getNews';
//            selectPage = 1;
//            inputPerPage = 5;
//            break;
//
//    case '#hot-art':
//            action = 'getHot';
//            selectPage = 1;
//            inputPerPage = 5;
//            break;
//
//    case '#push-art':
//            action = 'getPush';
//            selectPage = 1;
//            inputPerPage = 5;
//            break;

    case '#news-tpc':
            action = 'getNews';
            onlyTopic = '1';
            break;

//    case '#hot-tpc':
//            action = 'getHot';
//            onlyTopic = '1';
//            break;
//
//    case '#push-tpc':
//            action = 'getPush';
//            onlyTopic = '1';
//            break;
    }

    pageSetStr = '&action=' + action + '&tpc=' + onlyTopic + '&selectPage=' + selectPage + '&inputPerPage=' + inputPerPage +
        '&inputKeyword=' + keyword;
//    if (window.console) {
//        console.log(pageSetStr);
//    }

    $.ajax({
        'url': '/mooc/controllers/forum_ajax.php',
        'type': 'POST',
        'dataType': 'json',
        'data': $('#formSearch').serialize() + pageSetStr,
        'success': function (res) {
//            if (window.console) {
//                console.log(res);
//            }

            if (res.code === -1 || res.data === null) {

                // 清空舊資料
                $(specialTable).empty();
                $(specialTable).removeClass('no-data');
                $(specialTable).addClass('no-data');
               
                // 無資料
                $(specialTable)
                .append($('<tr style="background: 0; border-bottom: 0.1em solid #ECECEC; cursor: default;"></tr>')
                    .append($('<td></td>')
                        .append($('<div class="title" style="line-height: 36px;">' + msg['no_article'][nowlang] + '</div>')
                        )
                    )
                );

                // 隱藏分頁列
                $('#pageToolbar').hide();
            } else {
//                if (window.console) {
//                    console.log(res.data);
//                    console.log(onlyTopic);
//                    console.log(specialTable);
//                }
                // 有資料
                showSearchData(res.data, onlyTopic, specialTable);
                
                if($('#nowpage').val()>0){
                    $('#pageToolbar').paginate('refresh', {
                        'total': res.total_rows,
                        'pageSize': res.limit_rows,
                        'pageNumber':$('#nowpage').val()
                    });
                    $('#nowpage').val(0);
                }
            }

            // 如果點選主題才更新分頁工具列
            if (onlyTopic === '1') {
                $('#pageToolbar').paginate('refresh', {
                    'total': res.total_rows,
                    'pageSize': res.limit_rows
                });

                // 背景設定目前該分頁的 paginate工具列資訊
                $(specialTable).data('curpage', res.current_page);
                $(specialTable).data('firstpage', $('.paginate-first').parent().hasClass('disabled'));
                $(specialTable).data('prevpage', $('.paginate-prev').parent().hasClass('disabled'));
                $(specialTable).data('nextpage', $('.paginate-next').parent().hasClass('disabled'));
                $(specialTable).data('lastpage', $('.paginate-last').parent().hasClass('disabled'));
            }
        },
        'error': function () {
            $('#pageToolbar').paginate('refresh', {
                'total': 0
            });
            if (window.console) {
                console.log('Ajax Error!');
            }
        }
    });
}

function showSearchData(data, onlyTopic, specialTable) {

    // 清空舊資料
    $(specialTable).empty();
    $(specialTable).removeClass('no-data');

    $.each(data, function (key, value) {
        var replystr, pushstr, readstr;

        // 讀取數
        if (value.read === null || value.read === undefined) {
            value.read = 0;
        }
        readstr = '<div class="text-center">' + value.read + '</div>';

        if (typeof(value.reply) === 'undefined' || value.reply === null) {
            value.reply = 0;
        }

        // 按讚
        if (typeof(value.push) === 'undefined') {
            value.push = 0;
        }
        if (value.push > 0) {
        } else {
            value.push = 0;
        }
        pushstr = '<div class="text-center">' + value.push + '</div>';

        // 如果回覆數為0 需變為按鈕
        if (value.reply > 0) {
            value.reply = value.reply + value.whisper;
        } else {
            value.reply = 0;
        }
        replystr = '<div class="text-center"> ' + value.reply + '</div>';

        var postRoles = [];
        var posterInfo = '';
        // 下半部 文章主題列表
        if (onlyTopic === '1') {

            // 登入者是否閱讀過整個討論串
            var newFlag = '';

            if ( value.readflag === 0) {
                newFlag = 'new';
            }
            

            if ( value.postRoles.length > 0) {
            	postRoles = value.postRoles.split(',');
            	for(var i=0; i<postRoles.length; i++) {
            		posterInfo += '<img src="/theme/default/learn/forum/'+postRoles[i]+'.gif" border="0" style="margin-right: 0.3em;"/>';
            	}
            }
            
            posterInfo += value.poster + '('+value.realname+')';
            
            // 組精選文章主題列表
            if ((isPhoneDevice != 'undefined') && (isPhoneDevice == '1')) {
                // 手機版
                $(specialTable)
                    .append($('<tr data-bid="' + value.boardid + '" data-nid="_' + value.node + '" data-sid="' + value.s + '" data-reply="0" class="node-info" data-title="' + value.subject + '" style="border: 1px solid #ECECEC;"></tr>')
                        // .append($('<td class="t2"></td>')
                        //     .append($('<div class="status ' + newFlag + '"></div>')
                        //     )
                        // )
                        .append($('<td></td>')
                            .append($('<div class="row"></div>')
                                .append('<div class="col-xs-3 author-pic"><div class="photo-l"><img src="/co_showuserpic.php?a='+value.cpic+'" onerror=""></div></div>')
                                .append($('<div class="col-xs-9 row" style="padding-top:10px;"></div>')
                                    .append('<div class="col-xs-12"><span class="post-lable">'+msg['poster'][nowlang]+'</span><span>'+value.poster + '('+value.realname+')'+'</span></div>')
                                    .append('<div class="col-xs-12"><span class="post-lable">'+msg['time'][nowlang]+'</span><span>'+value.postdate+'</span></div>')
                                )
                            )
                            .append($('<div class="title" style="padding-left:5px;padding-top:15px;padding-bottom:30px;"></div>')
                                .append('<span>'+value.subject+'</span>')
                            )
                            .append($('<div style="float:right;"></div>')
                                .append('<span class="post-lable">'+msg['hit'][nowlang]+'</span><span style="color:#337ab7;margin-right:10px;">'+value.hit+'</span>')
                                .append('<span class="post-lable">'+msg['push'][nowlang]+'</span><span style="color:#337ab7;margin-right:10px;">'+value.push+'</span>')
                                .append('<span class="post-lable">'+msg['response'][nowlang]+'</span><span style="color:#337ab7;margin-right:10px;">'+value.reply+'</span>')
                            )
                        )
                    );
                $(specialTable).append('<tr><td style="background:#FFFFFF;line-height:1px;padding:10px;">&nbsp;</td></tr>');
            }else{
                $(specialTable)
                    .append($('<tr data-bid="' + value.boardid + '" data-nid="_' + value.node + '" data-sid="' + value.s + '" data-reply="0" class="node-info" data-title="' + value.subject + '" style="background: 0; border-bottom: 0.1em solid #ECECEC;"></tr>')
                        .append($('<td class="t2"></td>')
                            .append($('<div class="status ' + newFlag + '"></div>')
                            )
                        )
                        .append($('<td></td>')
                            .append($('<div class="title"></div>')
                                .append(value.subject)
                            )
                        )
                        .append($('<td class="t1 hidden-phone"></td>')
                            .append('<div class="text-center">'+value.hit+'</div>')
                        )
                        .append($('<td class="t1 hidden-phone"></td>')
                            .append(pushstr)
                        )
                        .append($('<td class="t1 hidden-phone"></td>')
                            .append(replystr)
                        )
                        .append($('<td class="t5 hidden-phone"></td>')
                            .append($('<div></div>')
                                .append('<div>' + posterInfo + '</div>')
                                .append('<div>' + value.postdate + '</div>')
                            )
                        )
                        .append($('<td class="t3 hidden-phone"></td>')
                            .append('<div class="icon-subject-go"></div>')
                        )
                    );

            }
        // 上半部 精選文章列表
        } else {
//            // 是否為回覆
//            if (value.n.length === 18) {
//                classstr = '<span style="color: #F06839;">[' + msg['reply'][nowlang] + ']</span> ';
//                isReply = '1';
//            } else {
//                classstr = '';
//                isReply = '0';
//            }
//
//            // 讀取數
//            if (value.read === null || value.read === undefined) {
//                value.read = 0;
//            }
//
//            // 組精選文章列表
//            $(specialTable)
//            .append($('<tr data-bid="' + value.boardid + '" data-nid="_' + value.node + '" data-sid="' + value.s + '" data-reply="' + isReply + '" class="node-info"></tr>')
//                .append($('<td></td>')
//                    .append($('<div class="title"></div>')
//                        .append(classstr + value.subject)
//                    ).append('<div class="summary">' + value.postcontenttext + '</div>')
//                )
//                .append($('<td class="t1 hidden-phone"></td>')
//                    .append('<div class="author">' + value.poster + '(' + value.realname + ')</div>')
//                    .append('<div class="hits">' + msg['clicks'][nowlang] + ': ' + value.hit + '</div>')
//                )
//                .append($('<td class="t1 hidden-phone"></td>')
//                    .append('<div>&nbsp;</div>')
//                    .append('<div class="readed">' + msg['readed'][nowlang] + ': ' + value.read + '</div>')
//                )
//                .append($('<td class="t1 hidden-phone"></td>')
//                    .append('<div class="date" title="' + msg['post_time'][nowlang] + '：' + value.postdate + '">' + value.postdatelen + '</div>')
//                    .append(pushstr)
//                )
//            );
        }
    });
    
    $("img").addClass('img-responsive');

    $(specialTable).find('tr').bind("click", read);

    // 須指定那個分頁，以避免相同名稱被BIND多次
    $(specialTable).find('.icon-like,.icon-unlike').bind("click", doPush);

    // 回覆主文按鈕
    $(specialTable).find('.first-reply').on('click', doReply);

    // 顯示分頁列
    $('#pageToolbar').show();
    
    // 復原搜尋列
    $('.search-keyword').css('visibility', 'hidden');

    $('.data2 #news-tpc tr').hover(
        function() {
            $(this).find('.title').css('text-decoration', 'underline');
        },
        function() {
            $(this).find('.title').css('text-decoration', 'none');
        }
    );
}

$(function () {
	
	var show_perpage = true;
	if (isPhoneDevice == '1' ) show_perpage = false;
	
    // 分頁工具列
    $('#pageToolbar').paginate({
        'total': 0,
        'showPageList': show_perpage,
        'pageList': [10, 20, 50, 100, 200, 400],
        'showRefresh': false,
        'showSeparator': false,
        'btnTitleFirst': btnTitleFirst,
        'btnTitlePrev': btnTitlePrev,
        'btnTitleNext': btnTitleNext,
        'btnTitleLast': btnTitleLast,
        'btnTitleRefresh': btnTitleRefresh,
        'beforePageText': beforePageText,
        'afterPageText': afterPageText,
        'beforePerPageText': '&nbsp;' + beforePerPageText,
        'afterPerPageText': afterPerPageText,
        'displayMsg': displayMsg,
        'buttonCls': '',
        'onSelectPage': function (num, size) {
            $('#selectPage').val(num);
            doSearch();
            // scroll list top
            $('html,body').animate({
                    scrollTop: ((isPhoneDevice != 'undefined') && (isPhoneDevice == '1'))?0:$(".box1").offset().top
                }, 0
            );
        },
        'onChangePageSize': function (pagesize) {
            $('#inputPerPage').val(pagesize);
            $('#selectPage').val(1);
            doSearch();
        }
    });

    $('#pageToolbar').paginate('select', 1);

    // 張貼
    $('.add-article').bind("click", doPost);

    // 指定分頁
    $(".paginate-number").keypress(function (e) {
        if (e.keyCode == 13) {
            $('#pageToolbar').paginate('select', $(this).val());
        }
    });
//    if (window.console) {
//        console.log($(window).width());
//    }
    // 偵測捲動
    $(document).scroll(function(e) {
//        if (window.console) {
//            console.log($(this).scrollTop());
//        }
        if ($(this).scrollTop() > 0) {
            $('.navbar-fixed-top').addClass('scroll-shadow');
        } else {
            $('.navbar-fixed-top').removeClass('scroll-shadow');
        }
        if ($(this).scrollTop() > 185 && $(window).width() >= 1022) {
            $('.title-bar').addClass('title-bar-fixed');
        } else {
            $('.title-bar').removeClass('title-bar-fixed');
        }
    });  
});

// 搜尋按鈕
$(".search-btn").click(function () {
    
    if ($('.search-keyword').css('visibility') === 'hidden') {
        $('.search-keyword').css('visibility', 'visible');
        $('.search-keyword').focus();
        return;
    } else {
        // 設定有啟用關鍵字查詢
        $('.search-form').data('refer', 'Y');

        // 清空舊資料
        $('.special,.content>.data2>.subjec').empty();

//        // 上半部
//        $('.select-show').parent().removeClass('active');
//        $("a[data-id='#news-art']").parents('li').addClass('active');
//        $('#curtab').val('#news-art')
//        doSearch();
//
//        $('#news-art').show();

        // 下半部
        $('#curtab').val('')
        $("a[data-id='#news-tpc']").parent().addClass('active');
        $('#pageToolbar').paginate('select', 1);
        doSearch();

        $('#news-tpc').show();
    }
});

// 搜尋框
$(".search-keyword").keypress(function (e) {
    if (e.keyCode == 13) {
        $(".search-btn").click();
    }
});

$(function(){
    if (detectIE() === 13) {
        $('.title-bar .subject td').css('border-radius', '0 0 0 0');
    }
});