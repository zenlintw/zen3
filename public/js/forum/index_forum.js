// 點選上半部-文章TOP N，前往檢視單一討論串或回覆
goNodeChain = function() {
    if ($(this).data('reply') === 1) {
        $("form[name='node_list']")
            .prop('action', appRoot + '/forum/m_node_chain.php')
            .find("input[name='cid']")
                .val($(this).data('cid')).end()
            .find("input[name='bid']")
                .val($(this).data('bid')).end()
            .find("input[name='nid']")
                .val($(this).data('nid').replace('_', ''));
    } else if($(this).data('bid')!='' && $(this).data('nid') === ''){
        $("form[name='node_list']")
            .prop('action', appRoot + '/mooc/m_forum_news_annt.php')
            .find("input[name='cid']")
                .val($(this).data('cid')).end()
            .find("input[name='bid']")
                .val($(this).data('bid')).end()
            .find("input[name='nid']")
                .val($(this).data('nid').replace('_', ''));
    } else {
    	if ($("form[name='node_list'] input[name='cid']").length == 0) {
    		$("form[name='node_list']").append('<input type="hidden" name="cid"><input type="hidden" name="bid"><input type="hidden" name="nid">');    	
    	}
        $("form[name='node_list']")
            .prop('action', appRoot + '/forum/m_node_chain.php')
            .find("input[name='cid']")
                .val($(this).data('cid')).end()
            .find("input[name='bid']")
                .val($(this).data('bid')).end()
            .find("input[name='nid']")
                .val($(this).data('nid').replace('_', ''));
    }

    // isPhoneDevice 在tiny_header.tpl設定: 此裝置是否為手機
    if (isPhoneDevice != '1') {
    	if (navigator.userAgent.match(/(iPad)/i)) {
    		$("form[name='node_list']").prop('target', '_blank');
    	} else {
	        //fancybox 固定
	        $.fancybox.open("#newsFrame", {
	        maxWidth: 960,
	        fitToView: false,
	        width: '100%',
	        autoSize: false,
	        'titlePosition': 'inline',
	        'transitionIn': 'none',
	        'transitionOut': 'none',
	        'closeBtn': true,
	        'scrolling': 'no',
	        beforeShow: function(){
	            $("body").css({'overflow-y':'hidden'});
	            $("#newsFrame").css({'width' : '100%'});
	        },
	        afterClose: function(){
	            $("body").css({'overflow-y':'visible'});
	        },
	        helpers : {
	            overlay : {
	                locked : false
	            }
	        }
	        });
    	}
    } else {
        $("form[name='node_list']").prop('target', '_self');
    }

    $("form[name='node_list']").submit();
}

// 點選下半部-討論區列表，前往單一討論區
goNodeList = function() {
    $("form[name='node_list']")
        .prop('action', appRoot + '/forum/m_node_list.php')
        .find("input[name='cid']")
            .val(cid).end()
        .find("input[name='bid']")
            .val($(this).data('bid'));

    $("form[name='node_list']").submit();
    // parent.s_sysbar.goBoard($(this).data('bid'));
}

// 切換分頁
$('.select-show').click(function() {
    $(this).parent().parent().find('li').removeClass('active');
    $(this).parent().addClass('active');
    $(this).parent().parent().parent().find('[id$="-art"]').hide();

    var specialTable = $(this).data('id');
    var bid = $(this).data('bid');
    if ( $(specialTable).html() == "" ) {

        // 取動作、是否僅顯示主題
        var action, onlyTopic = '0';
        switch (specialTable) {
            case '#news-art':
                action = 'getNews';
                break;

            case '#hot-art':
                action = 'getHot';
                break;

            case '#push-art':
                action = 'getPush';
                break;

            case '#0-art':
            case '#1-art':
            case '#2-art':
            case '#3-art':
            case '#4-art':
            case '#5-art':
                bid = $(this).data('bid');
                action = 'getAssign';
                break;
        }
        // 取資料
        $.ajax({
            'url':      "/mooc/controllers/forum_ajax.php",
            'type':     'POST',
            'dataType': "json",
            'data':     {action: action, tpc: onlyTopic, selectPage: 1, inputPerPage: 5, cid: cid, bid: bid},
            'success': function(res) {
                if (res.code === -1 || res.data === null) {
                    // 無資料
                    $(specialTable)
                    .append($('<tr></tr>')
                        .append($('<td></td>')
                            .append($('<div class="title">' + msg['no_article'][nowlang] + '</div>')
                            )
                        )
                    );
                } else {
                    
                    $.each(res.data, function (key, value) {
                        var likestr, classstr, isReply;

                        if (typeof(value.push) === 'undefined') {
                            value.push = 0;
                        }

                        // 如果按讚數為0 需變為按鈕
                        if (value.push > 0 || username === 'guest') {
                            likestr = '<div class="like"><div class="icon-like"></div><span>' + value.push + '</span></div>';
                        } else {
                            likestr = '<button class="btn btn-gray first-push">' + msg['first_push'][nowlang] + '</button>';
                        }

                        // 是否為回覆
                        if (value.n.length === 18) {
                            classstr = '<span style="color: #F06839;">[' + msg['reply'][nowlang] + ']</span> ';
                            isReply = '1';
                        } else {
                            classstr = '';
                            isReply = '0';
                        }

                        // 讀取數
                        if (value.read === null || value.read === undefined) {
                            value.read = 0;
                        }

                        // 組精選文章列表
                        $(specialTable)
                        .append($('<tr data-cid="' + value.cid + '" data-bid="' + value.boardid + '" data-nid="_' + value.node + '" data-sid="' + value.s + '" data-reply="' + isReply + '" class="node-info"></tr>')
                            .append($('<td></td>')
                                .append($('<div class="title"></div>')
                                    .append(classstr + value.subject)
                                ).append('<div class="summary">' + value.postcontenttext + '</div>')
                            )
                            .append($('<td class="t1 hidden-phone"></td>')
                                .append('<div class="author">' + value.poster + '(' + value.realname + ')</div>')
                                .append('<div class="hits">' + msg['clicks'][nowlang] + ': ' + value.hit + '</div>')
                            )
                            .append($('<td class="t1 hidden-phone"></td>')
                                .append('<div>&nbsp;</div>')
                                .append('<div class="readed">' + msg['readed'][nowlang] + ': ' + value.read + '</div>')
                            )
                            .append($('<td class="t1 hidden-phone"></td>')
                                .append('<div class="date" title="' + msg['post_time'][nowlang] + '：' + value.postdate + '">' + value.postdatelen + '</div>')
                                .append(likestr)
                            )
                        );
                    });

                    // 左半部，點選上半部-文章TOP N，前往檢視單一討論串或回覆
                    $('.special tr').on("click", goNodeChain);

                    // 點選下半部-討論區列表，前往單一討論區
                    $('.subject tr').on("click", goNodeList);
                }
            },
            'error': function() {
                alert('Featured Articles Ajax Error');
            }
        });
    }
    $($(this).data('id')).show();
});

// 行事曆切換type
$("#btn-calendardisplay").find('li').on('click',function(){
    if(isGuest) return; // 沒有登入guest不能切換顯示行事曆
    $(this).toggleClass('grey');
    var type=$(this).data('target');
    if($(this).hasClass('grey')){
        var index = $.inArray(type, NewCalendarDispalyType);
        NewCalendarDispalyType.splice(index, 1);
        $(".calendar-list li.today."+type+",.calendar-list li.week."+type).hide();
    }else{
        if( $.inArray(type, NewCalendarDispalyType)===-1 ) NewCalendarDispalyType.push(type);
        $(".calendar-list li.today."+type+",.calendar-list li.week."+type).show();
    }
    $.ajax({
        type: "POST",
        url: "/learn/newcalendar/cale_memo.php",
        dataType: "json",
        data: {ticket:newCalendarTicket,action:'setting',type:NewCalendarDispalyType}
    });
});

// index banner 點選前往單一討論串
goNodeChain_banner = function() {
    if ($(this).data('reply') === 1) {
        $("form[name='node_list']")
            .find("input[name='cid']")
                .remove().end()
            .find("input[name='bid']")
                .remove().end()
            .find("input[name='nid']")
                .remove().end()
            .prop('action', appRoot + '/forum/m_node_chain.php?cid=' + $(this).data('cid') + '&bid=' + $(this).data('bid') + '&nid=' + $(this).data('nid').replace('_', ''));
    } else if($(this).data('bid')!='' && $(this).data('nid') === ''){
        $("form[name='node_list']")
            .prop('action', appRoot + '/mooc/m_forum_news_annt.php')
            .find("input[name='cid']")
                .val($(this).data('cid')).end()
            .find("input[name='bid']")
                .val($(this).data('bid')).end()
            .find("input[name='nid']")
                .val($(this).data('nid').replace('_', ''));
    } else {
        $("form[name='bnode_list']")
            .prop('action', appRoot + '/mooc/m_forum_one_new_annt.php')
            .find("input[name='cid']")
                .val($(this).data('cid')).end()
            .find("input[name='bid']")
                .val($(this).data('bid')).end()
            .find("input[name='nid']")
                .val($(this).data('nid').replace(/_/, ''));
    }
            //fancybox 固定
            $.fancybox.open("#bnewsFrame",{
                'titlePosition': 'inline',
                'transitionIn': 'none',
                'transitionOut': 'none',
                'closeBtn': true,
                helpers : {
                    overlay : {
                        locked : false
                    }
                }
            });
    $("form[name='bnode_list']").submit();
}

//Index Banner News 前往單一文章頁面
$(function(){
    // 點選上半部-文章TOP N，前往檢視單一討論串或回覆
    $('#news_show_content tr').bind("click", goNodeChain_banner);
    
    // 點選上半部-文章TOP N，前往檢視單一討論串或回覆
    $('#newsmoreBtn').bind("click", goNodeChain);

    // 按讚
    $('.first-push, .icon-like, .icon-unlike').bind("click",doPush);

    // 點選上半部-文章TOP N，前往檢視單一討論串或回覆
    $('.special tr').bind("click", goNodeChain);

    // 客製
    $('.newsDataRow').bind("click", goNodeChain);

    // 點選下半部-討論區列表，前往單一討論區
    $('.subject tr').bind("click", goNodeList);
    
    //Index Banner News 點選上半部-文章TOP N，前往檢視單一討論串或回覆
    $('#news_show_content tr').bind("click", goNodeChain_banner);

    // 行事曆不顯示的type隱藏
    if( $.inArray('person', NewCalendarDispalyType)===-1 )  $(".calendar-list li.today."+'person'+",.calendar-list li.week."+'person').hide();
    if( $.inArray('course', NewCalendarDispalyType)===-1 )  $(".calendar-list li.today."+'course'+",.calendar-list li.week."+'course').hide();
    if( $.inArray('school', NewCalendarDispalyType)===-1 )  $(".calendar-list li.today."+'school'+",.calendar-list li.week."+'school').hide();
    
    // 版面控制
    if (hasCalendar === 'true' && !(hasForum === 'true' && hasNews === 'true')) {
        var allA = $(".data8 ul li a");
        $.each(allA, function(i,v) {
            var $obj = $(this);
            if($obj.data('id') === '#5-art')  {
                $obj.click();
            }
        });
    }
});