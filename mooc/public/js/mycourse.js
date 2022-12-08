if (!Object.keys) {
    Object.keys = function(obj) {
        var keys = [];

        for (var i in obj) {
            if (obj.hasOwnProperty(i)) {
                keys.push(i);
            }
        }

        return keys;
    };
}
// 物件轉陣列
if (!Array.prototype.forEach)
{
    Array.prototype.forEach = function(fun /*, thisp*/)
    {
        var len = this.length;
        if (typeof fun != "function")
            throw new TypeError();

        var thisp = arguments[1];
        for (var i = 0; i < len; i++)
        {
            if (i in this)
                fun.call(thisp, this[i], i, this);
        }
    };
}
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

function doSearch() {
    var
        selectPage = $('#selectPage').val(),
        inputIssuesPerPage = $('#inputIssuesPerPage').val(),
        pageSetStr = '&perpage=' + inputIssuesPerPage + '&action=getMyCourses' + '&role=' + kind;
    // 修正置頂位置
    var new_position = $('#mycourse-container').offset();
    window.scrollTo(new_position.left, new_position.top);
    $.ajax({
        'url': '/mooc/controllers/course_ajax.php'+'?page=' + selectPage,
        'type': 'POST',
        dataType:"text",
        'data': 'query='+$("#inputKeyword").val() + pageSetStr,
        'success': function (res) {
            res =  JSON.parse(res);
            if(res[1].num==0){
                $("#listtype_list_content").html('<div class="no-course"><div class="remind"><span>'+WM_Lang_search_no_courses+'</span></div></div>');
                $('#pageToolbar').hide();
            }else{
                $("#mycoure-checkall").prop("checked",false); //取消全選
                showSearchData(res[0]);
                $('#pageToolbar').paginate('refresh', {
                    'total':  res[1].num,
                    'pageSize': res[1].p2
                }).show();
            }
        },
        'error': function () {
            $('#pageToolbar').paginate('refresh', {
                'total': 0
            });
        }
    });
}

function showSearchData(data){
    var fbFlag = $.inArray("FB", socialShare)===-1 ? "display: none;":"",
        plkFlag = $.inArray("PLURK", socialShare)===-1 ? "display: none;":"",
        twFlag = $.inArray("TWITTER", socialShare)===-1 ? "display: none;":"",
        lnFlag = $.inArray("LINE", socialShare)===-1 ? "display: none;":"",
        wctFlag = $.inArray("WECHAT", socialShare)===-1 ? "display: none;":"";
    var returnHtml="";
    for (var key in data){
        var course=data[key];
        returnHtml += '<div class="mycourse">';
        returnHtml +='<table class="table table-bordered" '+(course.status==5?" style='filter: alpha(opacity=20);opacity:0.2;'":"")+'><thead><tr><th class="groupTd" colspan="2">&nbsp;&nbsp;' + WM_Lang_titel_type + '：';
        var groupCount=0;
        var tmpGroup=[];
        var tmpOtherGroup=[];
        if(!$.isArray(course.group)){ // assoc object with group
            for (var groupkey in course.group) {
                groupCount++;
                if(groupCount>2) {
                    tmpOtherGroup.push(course.group[groupkey].join('/'));
                }else{
                    tmpGroup.push(course.group[groupkey].join('/'));
                }
            }
            returnHtml += tmpGroup.join("&nbsp;|&nbsp;");
            if(groupCount>2) returnHtml += "&nbsp;|&nbsp;"+ '<a href="javascript: void(0);" class="groupOtherBtn">'+WM_Lang_other+'</a>'+'<span style="display: none;">'+tmpOtherGroup.join("&nbsp;|&nbsp;")+'</span>';
        }
        returnHtml += '</th></tr></thead><tbody>';
        returnHtml += '<tr><td class="courseimgTd" rowspan="2">';
        returnHtml += '<a href="javascript: void(0);" onclick="gotoCourse(\''+course.cid+'/'+course.sid+(curEnv==1?'':'/teach')+'\')">';
        returnHtml += '<img class="courseimg" src="' + appRoot + '/lib/app_show_course_picture.php?courseId=' + course.cpic +'&sId='+ course.spic+'"></a></td>';
        returnHtml += '<td><div style="position: relative">';
        returnHtml += '<a href="javascript: void(0);" onclick="gotoCourse(\''+course.cid+'/'+course.sid+(curEnv==1?'':'/teach')+'\')" class="coursecaption" title="'+course.caption+'">'+course.caption+'</a>';
        returnHtml += '<a href="javascript: void(0);" class="icon_share"></a>';
        returnHtml += "<div class='share' style='display: none;'>" +
            "<div class='pic' style='" + fbFlag + "'>" +
            "<a href='javascript: void(window.open(\"http://www.facebook.com/share.php?u=\".concat(encodeURIComponent(\"" + appRoot + "/info/"  + course.course_id + "/" + course.school_id + "?lang=" + nowlang + "\"))));'><div class='fb'></div></a>" +
            "</div>" +
            "<div class='pic' style='" + plkFlag + "'>" +
            "<a href='javascript: void(window.open(\"http://www.plurk.com/?qualifier=shares&status=\".concat(encodeURIComponent(\"" + course.caption + "\")).concat(\" \").concat(encodeURIComponent(\"" + appRoot + "/info/"  + course.course_id + "/" + course.school_id + "?lang=" + nowlang + "\"))));'><div class='plk'></div></a>" +
            "</div>" +
            "<div class='pic' style='" + twFlag + "'>" +
            "<a href='javascript: void(window.open(\"http://twitter.com/home/?status=\".concat(encodeURIComponent(\"" + course.caption + "\")) .concat(\" \").concat(encodeURIComponent(\"" + appRoot + "/info/"  + course.course_id + "/" + course.school_id + "?lang=" + nowlang + "\"))));'><div class='tw'></div></a>" +
            "</div>" +
            "<div class='pic' style='" + lnFlag + "'>" +
            "<a id='share-ln-"  + course.course_id + "' href='#inline-ln-"  + course.course_id + "' data-cid='"  + course.cid + "' data-sid='"  + course.sid + "' data-description='"  + course.content + "' title='" + note + "'><div class='ln'></div></a>" +
            "</div>" +
            "<div class='pic' style='" + wctFlag + "'>" +
            "<a id='share-wct-"  + course.course_id + "' href='#inline-wct-"  + course.course_id + "' title='" + wechatsharenote + "'><div class='wct'></div></a>" +
            "</div>" +
            "</div>" ;
        returnHtml += '</td></tr>';

        returnHtml += '<tr><td><div style="position: relative">';
        returnHtml += '<div class="teacherimg thumbnail pull-left"><img src="'+appRoot+'/co_showuserpic.php?a='+course.teacherPic+'"/></div>';
        returnHtml += '<div class="instructor lcms-td-limit-210" title="'+WM_Lang_instructor+'：'+course.teacher+'">'+WM_Lang_instructor+'：'+course.teacher+'</div>';
        returnHtml += '<div class="openingperiod"><i class="icon-lcms-classIng"></i>'+WM_Lang_openingperiod+'：'+(course.st_begin===null?WM_Lang_now:course.st_begin)+WM_Lang_to2+(course.st_end===null?WM_Lang_forever:course.st_end)+'</div>';
        if(curEnv==1) returnHtml +=   '<span class="progess-text">'+course.progress+'%'+'</span><div class="progress-container"><div class="progress"><div class="progress-bar progress-bar-warning progress-bar-striped" role="progressbar" aria-valuenow="'+course.progress+'" aria-valuemin="0" aria-valuemax="100" style="width: '+course.progress+'%"><span class="sr-only">'+course.progress+'% Complete (warning)</span></div></div></div>';
        if(curEnv==2) returnHtml +=   '<div class="student_number">'+WM_Lang_td_student_number+'：<span class="label label-warning">'+course.student_number+'</span></div>';
        returnHtml += '</div></td></tr>';

        returnHtml += '</tbody>';
        if(curEnv==1) {
            returnHtml += '<tfoot><tr><td colspan="2">';
            returnHtml += WM_Lang_td_nowrite_homework + '：&nbsp;' + '<a target="_top" href="'+appRoot+'/'+course.cid+'/'+course.sid+'/learn/homework">' + (course.QTI_undo.homework ? course.QTI_undo.homework + course.QTI_undo.peer : 0) + '</a>&nbsp;&nbsp;&nbsp;&nbsp;';
            returnHtml += WM_Lang_questionnaire + '：&nbsp;' + '<a target="_top" href="'+appRoot+'/'+course.cid+'/'+course.sid+'/learn/questionnaire">' + (course.QTI_undo.questionnaire ? course.QTI_undo.questionnaire : 0) + '</a>&nbsp;&nbsp;&nbsp;&nbsp;';
            returnHtml += WM_Lang_exam + '：&nbsp;' + '<a target="_top" href="'+appRoot+'/'+course.cid+'/'+course.sid+'/learn/exam">' + (course.QTI_undo.exam ? course.QTI_undo.exam : 0) + '</a>&nbsp;&nbsp;&nbsp;&nbsp;';
//            returnHtml += WM_Lang_peerassignment + '：&nbsp;' + '<a target="_top" href="'+appRoot+'/'+course.cid+'/'+course.sid+'/learn/peer">' + (course.QTI_undo.peer ? course.QTI_undo.peer : 0) + '</a>&nbsp;&nbsp;&nbsp;&nbsp;';
            returnHtml += '</td></tr></tfoot>';
        }
        returnHtml +='</table>';
        if( course.status==5 ) returnHtml +="<div class='teach-prepare'><span>"+WM_Lang_msg_cs_prepare+"</span></div><div class='teach-edit'><button type='button' class='btn btn-edit btn-blue' onclick='location.href=\""+appRoot+"/mooc/goedit.php?id="+course.cid+"\";'>"+WM_Lang_edit_course+"</button></div>";
        returnHtml +='</div>';
    }
    $("#listtype_list_content").html(returnHtml);
}

function listTypeLayout(type){
    if(type=="list"){
        $("#btnListTypeIcon,#listtype_list_container").show();
        $("#btnListTypeList,#listtype_icon_container").hide();
    }else{
        $("#btnListTypeIcon,#listtype_list_container").hide();
        $("#btnListTypeList,#listtype_icon_container").show();
    }
}

function json2array(json){
    var result = [];
    var keys = Object.keys(json);
    keys.forEach(function(key){
        result.push(json[key]);
    });
    return result;
}

function gotoCourse(csid)
{
    top.location.href = "/"+csid;
}

// 課程社群分享
$(document).click(function(event) {
    obj = event.srcElement ? event.srcElement  : $(event.target);
    if ($(obj).prop('class') === 'push') {
        $('.lcms-item>.author').show();
        $('.lcms-item>.share').hide();
        $(obj).parent().parent().children('.author').hide();
        $(obj).parent().parent().children('.share').fadeIn('slow');
    }else if ($(obj).prop('class') === 'icon_share') {
        $('#listtype_list_content').find('.icon_share').show();
        $(obj).hide().next().show();
    } else {
        $('#listtype_list_content').find('.icon_share').show();
        $('#listtype_list_content').find('.share').hide();
        $('.lcms-item>.share').hide();
        $('.lcms-item>.author').fadeIn();
    }
});

// 捲動捲軸觸發社群分享關閉
$(document).scroll(function() {
    $('#listtype_list_content').find('.icon_share').show();
    $('#listtype_list_content').find('.share').hide();
    $('.lcms-item>.share').hide();
    $('.lcms-item>.author').fadeIn();
});

// 點選課程WECHAT圖示
$('.lcms-item a[id^=share-wct-],.qrcode a[id^=share-wct-]').fancybox({
    maxWidth    : 800,
    maxHeight    : 600,
    fitToView    : false,
    width    : 100,
    height    : 100,
    autoSize    : false,
    closeClick    : false,
    openEffect    : 'none',
    closeEffect    : 'none'
});

// 點選課程LINE分享圖示
var lineShareList = function(){
    // 判斷式否為觸控裝置
    var touchable = isTouchDevice();
    if (touchable === false) {
        $(this).fancybox({
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
        var title = $(this).closest('table').find('.coursecaption').text();
        var description = $(this).parent().data('description');
        var cid = $(this).parent().data('cid');
        var sid = $(this).parent().data('sid');
        var url = title + '%0D%0A' + description + '%0D%0A' + appRoot + '/info/' + cid +'/'+ sid + '?lang=' + nowlang;
        top.location.href = 'http://line.naver.jp/R/msg/text/?' + url;
    }
}

function getCourseList(action, id, page, query){
    //取一頁幾筆
    if ($(document).height() > $(window).height()) {
        var win = $(window).width();
    }else{
        var win = $(window).width()-17; // scrollbar width is 17
    }
    var columns = Math.floor((win+2+93)/252),
    columns=columns - 1;
    var per_page = columns * 5;
    if(typeof page != 'undefined') {
        url = appRoot + '/mooc/controllers/course_ajax.php?page='+page;
    } else {
        url = appRoot + '/mooc/controllers/course_ajax.php';
    }

    // 取課程資料
    $.ajax({
        'type': 'POST',
        'dataType': 'json',
        'data': {'action' : action, 'id' : id, 'perpage' : per_page,'query':query,'role':kind},
        'url': url,
        'success': function (response) {
            rtn  = json2array(response);
            if (rtn[0] == null) {
                $('.lcms-items').html('<div class="no-course"><div class="remind"><span>'+WM_Lang_search_no_courses+'</span></div></div>');
                align = 'center';
                // 沒資料時不顯示查看更多按鈕
                $('.lcms-nav-bottom').html('');
                var options = {
                    autoResize: true, // This will auto-update the layout when the browser window is resized.
                    container: $('#main'), // Optional, used for some extra CSS styling
                    offset: 2, // Optional, the distance between grid items
                    itemWidth: 250, // Optional, the width of a grid item
                    align: align
                };
                var handler = $('#tiles li');
                handler.wookmark(options);

            } else {
                data = json2array(rtn[0]);
                html = '';
                var fbFlag = $.inArray("FB", socialShare)===-1 ? "display: none;":"",
                    plkFlag = $.inArray("PLURK", socialShare)===-1 ? "display: none;":"",
                    twFlag = $.inArray("TWITTER", socialShare)===-1 ? "display: none;":"",
                    lnFlag = $.inArray("LINE", socialShare)===-1 ? "display: none;":"",
                    wctFlag = $.inArray("WECHAT", socialShare)===-1 ? "display: none;":"";
                for (i = 0; i < data.length; i = i + 1) {
                    var isClassing = 'font-red';
                    var sid = "";
                    var sidurl = "";
                    var sidPic = "";
                    if (cursch != data[i].sid) {
                        sid = data[i].sid ;
                        sidurl = "/" + data[i].sid ;
                        sidPic = "&sId=" + data[i].spic;
                    }
                    
                    html = html +
                    "<li>" +
                    "<div class='lcms-item'"+(data[i].status==5 || data[i].isClassing === false?" style='filter: alpha(opacity=20);opacity:0.2;'":"")+">" +  
                    "<div class='cover'>" +
                    '<a href="javascript: void(0);" onclick="gotoCourse(\''+data[i].cid+'/'+data[i].sid+(curEnv==1?'':'/teach')+'\')">'+ "<img width='236' height='133' src='" + appRoot + "/lib/app_show_course_picture.php?courseId=" + data[i].cpic + sidPic + "'></a>"+
                    "</div>" +
                    "<div class='title'>" +
                    "<div class='lcms-td-limit-210' title='" + data[i].caption + "'>" + data[i].caption + "</div>" +
                    "</div>" +
                    "<div class='author'>" +
                    "<div class='pic'>" +
                    "<img src='" + appRoot + "/co_showuserpic.php?a=" + data[i].teacherPic + "'/>" +
                    "</div>" +
                    "<div class='user'>" +
                    "<div class='name' style='width:196px;'>" +
                    "<div class='lcms-table-td-text_gray lcms-td-limit-140' title='" + data[i].teacher + "'>" + data[i].teacher + "</div>" +
                    "</div>" +
                    "</div>" +
                    "<div class='push' data-id='"  + data[i].cid + "' data-id2='"  + sid + "' data-description='"  + data[i].content + "'></div>" +
                    "</div>" +
                    "<div class='share' style='display: none;'>" +
                    "<div class='pic' style='" + fbFlag + "'>" +
                    "<a href='javascript: void(window.open(\"http://www.facebook.com/share.php?u=\".concat(encodeURIComponent(\"" + appRoot + "/info/"  + data[i].cid + sidurl + "?lang=" + nowlang + "\"))));'><div class='fb'></div></a>" +
                    "</div>" +
                    "<div class='pic' style='" + plkFlag + "'>" +
                    "<a href='javascript: void(window.open(\"http://www.plurk.com/?qualifier=shares&status=\".concat(encodeURIComponent(\"" + data[i].caption + "\")).concat(\" \").concat(encodeURIComponent(\"" + appRoot + "/info/"  + data[i].cid + sidurl + "?lang=" + nowlang + "\"))));'><div class='plk'></div></a>" +
                    "</div>" +
                    "<div class='pic' style='" + twFlag + "'>" +
                    "<a href='javascript: void(window.open(\"http://twitter.com/home/?status=\".concat(encodeURIComponent(\"" + data[i].caption + "\")) .concat(\" \").concat(encodeURIComponent(\"" + appRoot + "/info/"  + data[i].cid + sidurl + "?lang=" + nowlang + "\"))));'><div class='tw'></div></a>" +
                    "</div>" +
                    "<div class='pic' style='" + lnFlag + "'>" +
                    "<a id='share-ln-"  + data[i].cid + "' href='#inline-ln-"  + data[i].cid + "' title='" + note + "'><div class='ln'></div></a>" +
                    "</div>" +
                    "<div class='pic' style='" + wctFlag + "'>" +
                    "<a id='share-wct-"  + data[i].cid + "' data-fancybox-type='iframe' href='" + data[i].qrcode_url + "' title='" + wechatsharenote + "'><div class='wct'></div></a>" + 
                    "</div>" +
                    "</div>" +
                    "<div class='info " + isClassing + "'>" +
                    "<i class='icon-lcms-classIng'></i>"+MSGopeningperiod+"：" + data[i].classPeriod +
                    "</div>" +
                    "<div id='inline-ln-"  + data[i].cid + "' class='inline-ln'>" +
                    "<form class='well'>" +
                    "<div>" + linesharenote + "</div>" +
                    "</form>" +
                    "</div>" +
                    /*
                    "<div style='width: auto; height: auto; overflow: auto; position: relative;'>" +
                    "<div id='inline-wct-"  + data[i].cid + "' class='inline-wct'>" +
                    "<img src='http://www.funco2de-tech.com/Encoder_Service/img.aspx?custid=1&username=public&codetype=QR&EClevel=0&data=" + appRoot + "/info/"  + data[i].cid + sidurl + "?lang=" + nowlang + "&choe=UTF-8'/>" +
                    "</div>" +
                    "</div>" +
                    */
                    "</div>" +
                    (data[i].status==5?"<div class='teach-prepare'><span>"+WM_Lang_msg_cs_prepare+"</span></div><div class='teach-edit'><button type='button' class='btn btn-edit btn-blue' onclick='location.href=\""+appRoot+"/mooc/goedit.php?id="+data[i].cid+"\";'>"+WM_Lang_edit_course+"</button></div>":"")+
                    "</li>";
                }

                $("#mycoure-checkall").prop("checked",false); //取消全選
                if(typeof page != 'undefined') {
                    $('.lcms-items').append(html);
                } else {
                    if(data.length==0){
                        html='<div class="no-course"><div class="remind"><span>'+WM_Lang_search_no_courses+'</span></div></div>';
                    }
                    $('.lcms-items').html(html);
                }

                if(typeof rtn[1] != 'undefined' && rtn[1].show === true) {
                    button = "<div class='narrow'><button type='button' class='btn btn-large btn-danger btnExplore btn-full' onclick='this.style.display=\"none\";getCourseList(\""+action+"\",\""+id+"\",\""+rtn[1].page+"\",\""+query+"\");'>查看更多</button></div>";
                    $('.lcms-nav-bottom').html(button);
                } else {
                    $('.lcms-nav-bottom').html('');
                }

                // 調整版面
                align = 'center';

                var options = {
                    autoResize: true, // This will auto-update the layout when the browser window is resized.
                    container: $('#main'), // Optional, used for some extra CSS styling
                    offset: 2, // Optional, the distance between grid items
                    itemWidth: 250, // Optional, the width of a grid item
                    align: align
                };

                var handler = $('#tiles li');
                handler.wookmark(options);

                // 社群分享
                // 點選課程WECHAT圖示
                $('.lcms-item a[id^=share-wct-],.qrcode a[id^=share-wct-]').fancybox({
                    maxWidth    : 800,
                    maxHeight    : 600,
                    fitToView    : false,
                    width    : 400,
                    height    : 400,
                    autoSize    : false,
                    closeClick    : false,
                    openEffect    : 'none',
                    closeEffect    : 'none'
                });

                // 點選課程LINE分享圖示
                var lineShare = function(){
                    // 判斷式否為觸控裝置
                    var touchable = isTouchDevice();
                    if (touchable === false) {
                        $(this).fancybox({
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
                        var addUrl = '';
                        var parentItem = $(this).parent().parent().parent().parent();
                        var title = parentItem.find('.title>div').text();
                        var pushdiv = parentItem.find('.author>.push');
                        var description = pushdiv.data('description');
                        if (null != pushdiv.data('id2')) {
                            var addUrl = '/' + pushdiv.data('id2');
                        }
                        var url = title + '%0D%0A' + description + '%0D%0A' + appRoot + '/info/' + pushdiv.data('id') + addUrl + '?lang=' + nowlang;
                        top.location.href = 'http://line.naver.jp/R/msg/text/?' + url;
                    }
                }

                $('.lcms-item .ln,.qrcode .ln').click(lineShare);
            }
        },
        'error': function () {
            alert('Ajax Error!');
        }
    });
}

// Enter等同送出
$("#searchBtn").click(function (e) {
    if(myCouseListType=='icon'){
        getCourseList('getMyCourses', '',undefined,$("#inputKeyword").val());
    }else{
        $('#pageToolbar').paginate('select', 1);
        $('#selectPage').val(1);
    }
});
$("#inputKeyword").keypress(function (e) {
    $("#mycourse-dropdown-btn").html(WM_Lang_msg_showallmycourse+' <span class="caret"></span>');
    if (e.keyCode == 13) {
        $("#searchBtn").click();
    }
});

$("#btnListTypeList").click(function (e) {
    myCouseListType='list';
    $.cookie('myCouseListType',myCouseListType);
    listTypeLayout(myCouseListType);
    $("#searchBtn").click();
});

$("#btnListTypeIcon").click(function (e) {
    myCouseListType='icon';
    $.cookie('myCouseListType',myCouseListType);
    listTypeLayout(myCouseListType);
    $("#searchBtn").click();
});

$("#listtype_list_content").on('click','.ln',function (e) {
    lineShareList.apply(this);
});

// 群組其他顯示
$("#listtype_list_content").on('click','.groupOtherBtn',function (e) {
    $(this).hide().next().show();
});


$("#mycourse-dropdown-menu").on('click', 'li a', function(){
    var target=$(this).data('target');
    if(target=="myLearningCourses"){
        
        document.location.href = '/mooc/mycourse.php?env=learn';    

        return;
    }else if(target=="myTeachingCourses"){

        document.location.href = '/mooc/mycourse.php?env=teach';    
        
        return;
    }
    var sid=target.toString().substring(0,5);
    var cid=target.toString().substring(5);
    if(curEnv==1)  gotoCourse(cid+'/'+sid);
    if(curEnv==2)  gotoCourse(cid+'/'+sid+'/teach');
});


$('.lcms-items').on('mouseover','.lcms-item .cover',function(){
    $(this).find('.dropbtn').show();
}).on('mouseout','.lcms-item .cover',function(){
    $(this).find('.dropbtn').hide();
});

// 退選課程
$('#mycourse-container').on('click', '.dropbtn', function(event) {
    var that=this;
    if (confirm(confirmwithdrawal)) {
        $.ajax({
            url: "/mooc/course_cancel.php",
            data: {cancelCourseId:$(this).data('cid'),sid:$(this).data('sid'), method:"ajax"},
            type: "POST",
            success: function(msg){
                switch (msg) {
                    case '0':
                    case '7':
                        if(myCouseListType=='icon') {
                            getCourseList('getMyCourses', '', undefined, $("#inputKeyword").val());
                        }else{
                            doSearch();
                        }
                        break;
                    default:
                        alert(withdrawalfail + 'error:' + msg);
                }
            }
        });
    }
});

// 畫面resize時重新計算list icon顯示數量
$(window).on('resize.wookmark', function(event) {
    if(myCouseListType=='icon') {
        getCourseList('getMyCourses', '', undefined, $("#inputKeyword").val());
    }
});

// 獨立校預設圖文模式,portal及內容商預設圖片模式
var myCouseListType=$.cookie('myCouseListType')?$.cookie('myCouseListType'):(is_independent?'list':'icon');
listTypeLayout(myCouseListType);
$("#searchBtn").click();
