$(function() {

    // 點選進入評分
    $('.mooc-process .rating.active').click(function(event) {
        if ($(this).find('.level1').attr('class') === 'level1 active') {
            $(this).find('.level1').hide().removeClass('active');
            $(this).find('.level2').fadeIn().addClass('active');
        }
    });

    /* 點選非進度列區域 */
    $(document).click(function(event) {
        obj = event.srcElement ? event.srcElement : $(event.target);
        if ($(obj).prop('class').indexOf('process-title') === -1 &&
            $(obj).prop('class').indexOf('process-period') === -1 &&
            $(obj).prop('class').indexOf('rating active') === -1) {

            $('.level2').hide().removeClass('active');
            $('.level1').fadeIn().addClass('active');
        }
    });
});