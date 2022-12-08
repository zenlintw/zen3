function getCourseList(action, id, page){
    if (typeof isPhoneDevice == 'undefined') {
        forMobile = false;
    }else{
        forMobile = (isPhoneDevice == '1')?true:false;
    }

    per_page = (forMobile)?5:20;

    if(typeof page != 'undefined') {
        url = appRoot + '/mooc/controllers/course_ajax.php?page='+page;
    } else {
        url = appRoot + '/mooc/controllers/course_ajax.php';
    }

    // 取課程資料
    $.ajax({
        'type': 'POST',
        'dataType': 'json',
        'data': {'action' : action, 'id' : id, 'perpage' : per_page},
        'url': url,
        'success': function (response) {
            rtn  = json2array(response);

            // 課程區塊的物件
            var $lcmsItems = $('.lcms-items');

            if (rtn[0] == null) {
                if (location.pathname === '/mooc/explorer.php') {
                    align = 'left';
                }else{
                    align = 'center';
                }
                $lcmsItems.html('<div class="message">' + nocourses + '</div>');
                // 沒資料時不顯示查看更多按鈕
                $('.lcms-nav-bottom').html('');
            } else {
                data = json2array(rtn[0]);
                html = '';

                var dateTitle = MSGopeningperiod + "：" ;
                for (i = 0; i < data.length; i = i + 1) {
                    if (data[i].isClassing === true) {
                        isClassing = 'font-red';
                    } else {
                        isClassing = 'font-gray';
                    }
                    var sid = "";
                    var sidurl = "";
                    var sidPic = "";
                    if (cursch != data[i].sid) {
                        sid = data[i].sid ;
                        sidurl = "/" + data[i].sid ;
                        sidPic = "&sId=" + data[i].spic;
                    }
                    var coursePicPath = '';
                    if (data[i].hasCoursePic == 'Y') {
                    	coursePicPath = appRoot + "/lib/app_show_course_picture.php?courseId=" + data[i].cpic + sidPic;
                    }else{
                    	coursePicPath = "/theme/default/app/default-course-picture.jpg";
                    }

                    html = html + 
                    '<div class="div_course_item_outer '+((location.pathname === '/mooc/explorer.php')?'col-md-4 col-sm-6':'col-md-3 col-sm-4') +' col-xs-12">' +
                    '<div class="div_course_item">' +
                    '<table width="100%" border="0" cellspacing="4" cellpadding="0">' +
                    '<tbody><tr>' +
                    '<td><a href="/info/' + data[i].cid + sidurl + '"><img src="'+coursePicPath+'" style="width:100%;max-width:472px;'+((forMobile)?'max-height:162px;':'max-height:125px;')+'"></a></td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td class="t20_black_bold"><table width="100%" border="0" cellspacing="4" cellpadding="0">' +
                    '<tbody><tr>' +
                    '<td class="t20_black_bold course_caption">'+data[i].caption+'</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td><table width="100%" border="0" cellspacing="0" cellpadding="0">' +
                    '<tbody><tr>' +
                    '<td width="34" height="44"><img src="/co_showuserpic.php?a='+data[i].teacherPic+'" width="30" height="30" class="circle30"></td>' +
                    '<td class="t16_gary course_teacher">'+data[i].teacher+'</td>' +
                    '</tr>' +
                    '</tbody></table></td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td height="1" bgcolor="#dfdfdf"></td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td height="32" class="course_between t13_b">'+dateTitle + data[i].classPeriod+'</td>' +
                    '</tr>' +
                    '</tbody></table></td>' +
                    '</tr>' +
                    '</tbody></table>' +
                    '</div>' +
                    '</div>';
                }
                if(typeof page != 'undefined') {
                    $lcmsItems.append(html);
                } else {
                    $lcmsItems.html(html);
                }

                if(typeof rtn[1] != 'undefined' && rtn[1].show === true) {
                    if (forMobile) {
                        button = "<div  class='div_course_item_more' style='text-align:center;'>"+
                                    "<button type='button' class='btn btn-primary btn-large btn-blue btn-full' onclick='this.style.display=\"none\";getCourseList(\""+action+"\",\""+id+"\",\""+rtn[1].page+"\", true);'>"+
                                        MSGSHOWMORECOURSE+
                                    "</button>"+
                                "</div>";
                    } else {
                        button = "<div class='div_course_item_more col-md-12' style='text-align:center;font-size:16px;'>"+
                                    "<button type='button' class='btn btn-large btn-blue btnExplore btn-full' style='' onclick='this.style.display=\"none\";getCourseList(\""+action+"\",\""+id+"\",\""+rtn[1].page+"\");'>"+
                                        MSGSHOWMORECOURSE+
                                    "</button>"+
                                "</div>";
                    }
                    $('.lcms-nav-bottom').html(button);
                } else {
                    $('.lcms-nav-bottom').html('');
                }

                // 調整版面
                align = 'center';
                if (location.pathname === '/mooc/explorer.php') {
                    align = 'left';
                }
            }
        },
        'error': function () {
            if (window.console) {
                console.log('Ajax Error!');
            }
        }
    });
}