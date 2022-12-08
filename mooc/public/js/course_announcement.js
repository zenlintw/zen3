function doSearch() {
    var
        pageSetStr,
        selectPage = $('#selectPage').val(),
        inputIssuesPerPage = $('#inputIssuesPerPage').val();
    $('.box').hide();
    // 修正置頂位置
    var new_position = $('#top').offset();
    window.scrollTo(new_position.left, new_position.top);

    $('#message').html('');
    pageSetStr = '&selectPage=' + selectPage + '&inputIssuesPerPage=' + inputIssuesPerPage + '&action=getAnnouncement';
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
    $('#searchResult .margin-bottom-15').remove();
    var editBtn = '';
    if (res.editEnable === '0') {
        editBtn = 'style="display: none;"';
    }
    $.each(res.data, function (key, value) {
        var fileEnable = '';
        if (this.postfilelink === null || this.postfilelink ==='') {
            fileEnable = 'display: none;';
        }
        $('#searchResult').append(
            $('<div class="margin-bottom-15"></div>')
                .append(
                $('<div></div>')
                    .append('<div class="icon-announcement-info"></div>')
                    .append('<div class="rating-title breakword" style="width:90%;" data-s="' + value.s + '" data-n="n' + value.n +'">'+value.subject+'</div>')
                    .append(
                    $('<div class="rating-require board-manage" style="top: -34px; right: 0;"></div>')
                        .append('<div class="icon-edit left" ' + editBtn + '></div>')
                        .append('<div class="icon-delete right" ' + editBtn + '></div>')
                    )
                    .append('<div style="clear: both;"></div>')
                )
                .append(
                $('<div class="board-annt all-radius bkcolor-palegray"></div>')
                    .append('<div class="date right">' + value.postdate + '</div>')
                    .append('<div class="content">' + value.postcontent + '</div>')
                    .append(
                    $('<div class="file" style="'+fileEnable+'"></div>')
                        .append('<div>' + attach + '</div>')
                        .append('<div>' + value.postfilelink + '</div>')
                    )
                )
        );
    });

}

function showMessage(type, msg) {
    var $alert, kind, new_position, txt, c, i, j;

    new_position = $('#top').offset();
    window.scrollTo(new_position.left, new_position.top);
    $alert = $('<div class="box box-padding-lr-3"></div>');
    if (typeof msg === 'string') {
        $alert.append('<div class="margin-top-15 margin-bottom-15" style="font: bold 16px/3 \'\';">' + msg + '</div>');
    }
    $('#message').html('').append($alert);
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