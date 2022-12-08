// 首頁-點選開課中課程
// $('.lcms-nav-tabs .lcms-nav-link button.btn-blue').last().click(function() {
$('#btnSigning').click(function() {
    var id = $(this).prop('id'), action = 'getHistoryCourses';
    switch (id) {
        case 'btnHistory':
            action = 'getHistoryCourses';

            // 更換按鈕文字、主標題
            //$(this).prop('id', 'btnSigning');
            // $(this).find('img').attr('src', '../theme/default/learn_mooc/top_explore.png');
            // $(this).text(commencementcourse);
            $('.lcms-nav-tabs>.lcms-nav-group').hide();
            $('.lcms-nav-tabs>.lcms-nav-welcome').show();
            $('.lcms-nav-tabs>.lcms-nav-welcome').text(historycourse);
            $('.lcms-nav-bottom').html('');
            $('nav').find('a').removeClass('active');
            $('a[href="#1"]').addClass('active');
            break;

        default:
        case 'btnSigning':
            action = 'getSigningCourses';

            // 更換按鈕文字、主標題
            //$(this).prop('id', 'btnHistory');
            // $(this).find('img').attr('src', '../theme/default/learn_mooc/top_history.png');
            // $(this).text(historycourse);
            $('.lcms-nav-tabs>.lcms-nav-welcome').hide();
            $('.lcms-nav-tabs>.lcms-nav-group').show();
            $('.lcms-nav-bottom').html('');
            $('nav').find('a').removeClass('active');
            $('a[href="#1"]').addClass('active');
            break;
    }

    // 取該類別課程資料
    getCourseList(action, this.id);
});

// 讓懸浮的歷史課程和開課中課程按鈕變色效果
$(function() {
    if (cal_alert=='true') {
		$.fancybox({
		    margin:40,
	        href: "/learn/calender_alert.php",
	        type: "ajax",
	        ajax: {
	            type: "POST",
	            data: {
	            }
	        },
	        helpers: {
	            overlay : {closeClick: false}
	        }
	    });
    }
    
    var url = window.location.toString();
    var id = url.split('#')[1];
    if (id) {
    	setTimeout(function(){ 
    		var t = $('#' + id).offset().top;
    		$(window).scrollTop(t); 
    	}, 500);
    }    
});