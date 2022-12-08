function doSearch() {
    var
        pageSetStr,
        selectPage = $('#selectPage').val(),
        inputIssuesPerPage = $('#inputIssuesPerPage').val();

    $('#message').html('');
    pageSetStr = '&selectPage=' + selectPage + '&inputIssuesPerPage=' + inputIssuesPerPage + '&action=getnews_nologin';
    $.ajax({
        'url': '/mooc/controllers/forum_ajax.php',
        'type': 'POST',
        dataType:"text",
        'data': $('#formSearch').serialize() + pageSetStr,
        'success': function (res) {
            res =  JSON.parse(res);
            if (res.code > 0) {
                showMessage(0, res.message);
            } else if (res.code === -1) {
                // 無資料
                showMessage(1, res.message);
            } else if (res.code === 0) {
                // 有資料
                showSearchData(res);
               // showMessage(1, 'test');
//                showToolbar(res);
                $('.box').show();
                /* 處理 frame 內超連結無法連結外部網路問題 */
                $('.content').find("a").attr('target', '_blank');
            }
            $('#pageToolbar').paginate('refresh', {
                'total': res.total_rows,
                'pageSize': res.limit_rows
            });
            $('.icon-edit').on('click', doEdit);
            $('.icon-delete').on('click', doDelete);
        },
        'error': function () {
            $('#pageToolbar').paginate('refresh', {
                'total': 0
            });
            showMessage(0, 'Ajax Error!');
            $('.esn-box-append').show();
        }
    });
}

function showSearchData(res) {
    // 清空資料
    $('#searchResult .box2').remove();
    $.each(res.data, function (key, value) {
        var fileEnable = '';
        if (this.postfilelink === null || this.postfilelink ==='') {
            fileEnable = 'display: none;';
        }

        $('#searchResult').append(
            $('<div class="box2">')
                //title
                .append(
                    $('<div class="title"></div>')
                    .append('<div class="icon-blue-info"></div>'+value.subject.substr(0,55) )
                )
                //content text
                .append(
                    $('<div class="content"></div>')
                    .append(
                        $('<div class="data1">')
                        .append(
                            $('<div class="date"></div>')
                            .append(value.postdate)
                        )
                        .append(
                            $('<div class="content"><div>')
                            .append(value.postcontent)
                        )
                        .append(
                            $('<div class="file" style="'+ fileEnable +'"></div>')
                            .append('<div>'+ attach +'</div>')
                            .append(
                                $('<div></div>')
                                .append(value.postfilelink)
                            )
                        )
                    )
                )
        );
        
        $("img").addClass('img-responsive');
    });
    $('#pageToolbar').show();
}

function showMessage(type, msg) {
    var $alert;
    
    $('#searchResult .box2').remove();
    $alert = $('<div class="box2"></div>');
    if (typeof msg === 'string') {
        $alert.append('<div class="data4"><div class="message">' + msg + '</div></div>');
    }
    $('#message').html('').append($alert);
    $('#pageToolbar').hide();
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
        'onChangePageSize': function (pagesize) {
            $('#inputIssuesPerPage').val(pagesize);
            $('#selectPage').val(1);
            doSearch();
        },
        'onSelectPage': function (num, size) {
            $('#selectPage').val(num);
            doSearch();
        },
        'onRefresh': function (num, size) {
            $('#inputIssuesPerPage').val(size);
            $('#selectPage').val(num);
            doSearch();
        }
    });

    // Enter等同送出
    $("#searchBtn").click(function (e) {
            $('#inputIssuesPerPage').val(10);
            $('#pageToolbar').paginate('select', 1);
            $('#selectPage').val(1);
            // doSearch();
    });
    $("#inputKeyword").keypress(function (e) {
        if (e.keyCode == 13) {
            $("#searchBtn").click();
        }
    });
    $('#inputIssuesPerPage').val(10);
    $('#pageToolbar').paginate('select', 1);
    $('#selectPage').val(1);

});