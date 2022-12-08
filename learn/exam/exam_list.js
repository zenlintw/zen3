var examWin;
$(function() {
//    setTimeout('reloadPage()', '60000');

    $('.sparkpie').sparkline('html', {
        type: 'pie',
        sliceColors: ['#f3800f', '#dadada'],
        offset: -90,
        width: '23px',
        height: '23px',
        disableHighlight: true,
        borderColor: '#000000',
        disableTooltips: true
    });
    $('.exam-percent-tips,.exam-type-tips').tooltip('hide');
    $('.data5 .pay.active').click(function(event) {
        if ($(this).find('.level1').attr('class') === 'level1 active') {
            $(this).find('.level1').hide().removeClass('active');
            $(this).find('.level2').fadeIn().addClass('active');
            
            event.stopPropagation();            
        }
    });
    $(document).click(function(event) {
        obj = event.srcElement ? event.srcElement : $(event.target);
        if ($(obj).prop('class').indexOf('main-text') === -1 &&
            $(obj).prop('class').indexOf('sub-text') === -1 &&
            $(obj).prop('class').indexOf('pay active') === -1) {
            $('.pay .level2').hide().removeClass('active');
            $('.pay .level1').fadeIn().addClass('active');
        }
    });
    // 點選進入評分
    $('.mooc-process .rating.active').click(function(event) {
        if ($(this).find('.level1').attr('class') === 'level1 active') {
            $(this).find('.level1').hide().removeClass('active');
            $(this).find('.level2').fadeIn().addClass('active');
        }
    });

//    /* 點選非進度列區域 */
//    $(document).click(function(event) {
//        obj = event.srcElement ? event.srcElement : $(event.target);
//        if ($(obj).prop('class').indexOf('process-title') === -1 &&
//            $(obj).prop('class').indexOf('process-period') === -1 &&
//            $(obj).prop('class').indexOf('rating active') === -1) {
//
//            $('.level2').hide().removeClass('active');
//            $('.level1').fadeIn().addClass('active');
//        }
//    });
});
function reloadPage() {
    if (!examWin || examWin.closed) // 測驗中不reload page
    {
        location.reload();
    }
}
function viewResult(eid) {
    window.open('view_result.php?' + eid, '', 'width=990, height=480, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
}
function viewExemplar(eid, obj) {
    var test_type = $(obj).parents('.box2').data('type');
    if (test_type === 'peer') {
        window.open('/learn/peer/exemplar_list.php?' + eid, '', 'width=980, height=480, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
    } else {
        window.open('exemplar_list.php?' + eid, '', 'width=980, height=480, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
    }
}
function viewStat(eid) {
    window.open('exam_statistics_result.php?' + eid, '', 'width=810, height=480, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
}
function homework_preview(eid, obj) {
    var test_type = $(obj).parents('.box2').data('type');
    if (test_type === 'peer') {
        window.open('/learn/peer/homework_preview.php?' + eid, '', 'width=800, height=480, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
    } else {
        window.open('homework_preview.php?' + eid, '', 'width=980, height=480, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
    }
}
function check_homework_itme($content) {
    var examDetail = XmlDocument.create();
    var xmlHttp = XmlHttp.create();
    examDetail.loadXML($content);
    xmlHttp.open('POST', 'homework_display.php?preview=true', false);
    var ret = xmlHttp.send(examDetail);
    if (ret == false) {
    } else {
        if (xmlHttp.responseText.length == 1)
            return 1;
        else
            return 0;
    }
    return 1;
}