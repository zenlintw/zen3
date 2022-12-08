var moocSysbar, isLoaded = false, loadCnt = 0, baseUri = '';

/**
 * 切換課程
 */
function goCourse(csid, env, func) {
    var goSection = func;
    if ((isLoaded === undefined) || !isLoaded) {
        return;
    }
    var txt  = '<course_id>' + csid + '</course_id>';
    txt += '<env>' + env + '</env>';
    moocSysbar.setSelCourseIdVal(csid);
    moocSysbar.loadSysbar(baseUri + 'goto_course.php', txt, function (cid, cname, isTeach, isStudent) {
        courseId = cid;
        parent.document.title = (cname === '') ? schoolName : cname + ' - ' + schoolName;
        if (typeof func === 'function') {
            func.call(null);
        }
        /* 顯示個人學習中心、各環境連結 */
        showPersonal(cid > 10000000);
        showTeachEnv(cid <= 10000000);
        showDirectorEnv(cid <= 10000000);
        showManagerEnv(cid <= 10000000);
        showTeach(isTeach);
        /* 顯示課程資訊 */
        showCourseInfo(cid > 10000000, cid);
    }, goSection);// goSection 要去哪個頁面
}

function goBoard(val, target) {
    if ((isLoaded === undefined) || !isLoaded) {
        return;
    }

    moocSysbar.goBoard(val, target);
}

function goChatroom(val) {
    if (window.console) {console.log('mooc_header.js goChatroom()', val);}
    if ((isLoaded === undefined) || !isLoaded) {
        return;
    }
    
    // mooc/smarty/templates/mooc_sysbar.tpl
    moocSysbar.goChatroom(val);
}

function logoutChatroom() {
    if ((isLoaded === undefined) || !isLoaded) {
        return;
    }

    moocSysbar.logoutChatroom();
}

/**
 * 辦公室/教室 切換環境
 */
function goEnv(csid, env, func) {
    if ((isLoaded === undefined) || !isLoaded) {
        return;
    }

    moocSysbar.goEnv(csid, env, func);
}

/**
 * 取得目的地的 Frame
 * @return 目的地的 Frame
 **/
function getTargetName() {
	var txt = "_blank";
	switch (this.name) {
		case "s_sysbar": txt = "s_main"; break;
		case "c_sysbar": txt = "c_main"; break;
		case "sysbar"  : txt = "main";   break;
	}
	return txt;
}

/**
 * 顯示辦公室
 * @param {boolean} bol 顯示或隱藏
 */
function showTeach(bol) {
    if (bol) {
        $('.teachDiv').show();
    } else {
        $('.teachDiv').hide();
    }
}

/**
 * 進入辦公室
 */
function goTeach() {
    parent.chgCourse(courseId, 1, 2);
}

function showCancel(bol) {
    if (bol) {
        $('.cancelDiv').show();
    } else {
        $('.cancelDiv').hide();
    }
}

function showPersonal(bol) {
    if (bol) {
        $('.personalDiv').show();
    } else {
        $('.personalDiv').hide();
    }
}

/* label 要切換到的功能選項 */
function goPersonal(label) {
    var objFrame, menuFrame = parent.frames['s_catalog'], time = 500;
    if (menuFrame.location.pathname === '/learn/path/manifest.php') {
        parent.FrameExpand(0, false, 0);
        objFrame = menuFrame.document.getElementById('pathtree');
        if (
            (objFrame != null) &&
                (objFrame.contentWindow.fetchResourceForm != null) &&
                (objFrame.contentWindow.fetchResourceForm.href !== undefined) &&
                (objFrame.contentWindow.fetchResourceForm.href.value !== 'about:blank')
            ) {
            objFrame.contentWindow.doUnload();
        }
    } else if (menuFrame.location.pathname === '/learn/scorm/InitialSCORM.php') {
        parent.FrameExpand(0, false, 0);
        menuFrame.doUnload();
    } else {
        time = 1;
    }
    setTimeout(function () {
        $.ajax(
            '/learn/mooc_personal.php',
            {
                'type': 'GET',
                'success': function () {
                    goCourse(0, 'learn', label);
                }
            }
        );
    }, time);
    //showPersonal(false);
}

/**
 * 初始化 MOOC Sysbar
 */
function initSysbar() {
    var t;
    if (moocSysbar === undefined) {
        return;
    }
    isLoaded = moocSysbar.isLoaded;
    if ((isLoaded !== undefined) && isLoaded) {
        // 從 sysbar 取得課程名稱
        if (moocSysbar.courseName !== '' && typeof(moocSysbar.courseName) !== 'undefined') {
            courseName = moocSysbar.courseName;
        }
        if (courseName !== '') {
            parent.document.title = courseName + ' - ' + schoolName;
        }
        t = window.location.pathname.split('/');
        t[t.length - 1] = '';
        baseUri = t.join('/');
    } else if (loadCnt < 11) {
        loadCnt += 1;
        setTimeout(function () {
            initSysbar();
        }, 500);
    }
}

$(function () {
    var sysbar = parent.document.getElementById('moocSysbar');

    if ((sysbar !== null) && (typeof sysbar === 'object')) {
        moocSysbar = sysbar.contentWindow;
        initSysbar();
    }
    $('#toggle').click(function () {
        parent.toggleSidebar();
        showToggleArrow();
        return false;
    });
    // setTimeout("loadsmain()",1000);
    
    // 變更語系
    $('#language').change(function(){
        // 取自localStorage，以利 我的設定和其他頁面都能正常運作
        var ticket = localStorage.getItem('personal-info');
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {'language' : $(this).val(), 'ticket' : ticket},
            url:"/learn/personal/m_info1.php",
            success:function(result){
                if (result.reload === true) {
                    parent.location.href = parent.location.href;
                }
            }
        });
    });
});
// 載入個人中心時，隱藏(個人學習中心)LINK
function loadsmain(){
    var smainurl = parent.document.getElementById('s_main').contentWindow.location.href;
    if (smainurl.match("/mooc/mycourse.php") != null) {
        showPersonal(false);
    }
}

/**
 * 顯示教師環境
 * @param {boolean} bol 顯示或隱藏
 */
function showTeachEnv(bol) {
    if (bol) {
        $('.teachEnvDiv').show();
    } else {
        $('.teachEnvDiv').hide();
    }
}

/**
 * 顯示導師環境
 * @param {boolean} bol 顯示或隱藏
 */
function showDirectorEnv(bol) {
    if (bol) {
        $('.directEnvDiv').show();
    } else {
        $('.directEnvDiv').hide();
    }
}

/**
 * 顯示管理者環境
 * @param {boolean} bol 顯示或隱藏
 */
function showManagerEnv(bol) {
    if (bol) {
        $('.managerEnvDiv').show();
    } else {
        $('.managerEnvDiv').hide();
    }
}

var isCourseInfoBarShow = false;
/**
 * 顯示課程資訊BAR
 * @param {boolean} bol 顯示或隱藏
 */
function showCourseInfo(bol, cid) {
    ua = navigator.userAgent.toLowerCase();
    if (bol) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {'action' : 'getCourseInfo', 'cid' : cid},
            url:"/mooc/controllers/course_ajax.php",
            success:function(result){
               if (result !== null && result !== '') {
                    var teachers = new Array();
                    result = json2array(result);
                    var ccaption = result[0]['caption'];
                    var ccontent = result[0]['content'];
                    var cnum = result[0]['number'];
                    // console.log(result);
                    $('.coursename').html(ccaption);
                    $('.push').attr('data-description', ccontent);
                    $('.push').attr('data-id', cid);
                    $('.coursecount').html('<span class="icon-self"></span> <span>' + courseNumText + ': ' + cnum + '</sapn>');
                    // console.log(result[0]['teachers']);
                    var j = 0;
                    for(i = 0; i < result[0]['teachers'].length; i++) {
                        if (result[0]['teachers'][i]['name'] >= '0') {
                            teachers[j] = result[0]['teachers'][i]['name'];
                            j++;
                        }
                    }
                    $('.courseteacher').html('<span class="icon-teacher"></span> <span>' + courseTeacherText + ': ' + teachers.join(',') + '</span>');
                    $('.courseteacher').attr('title', courseTeacherText + ': ' + teachers.join(','));
                    $('.push').show();
                    var sharehtml = $('.share').html();
                    var replacesharehtml = sharehtml.replace(/currcourseid/gi, cid);
                    replacesharehtml = replacesharehtml.replace(/currcoursecaption/gi, ccaption);
                    $('.share').html(replacesharehtml);
                    // 社群分享
                    // 點選課程WECHAT圖示
                    var wctShare = function(){
                        var wctWindow = window.open("", "wctShare", "toolbar=no, scrollbars=no, resizable=no, width=420, height=520");
                        wctWindow.document.write('<h2>' + wctMSG + '</h2>');
                        // 點擊後才產生交易
                        wctWindow.document.write('<img src="https://chart.googleapis.com/chart?chs=390x390&cht=qr&chl=' + appRoot + '/info/' + cid + '?lang=' + nowlang + '&choe=UTF-8"/>');
                        wctWindow.document.title = 'wechat share';
                    }
                    $('.courseinfo .wct,.qrcode .wct').click(wctShare);

                    // 點選課程LINE分享圖示
                    var lineShare = function(){
                        // 判斷式否為觸控裝置
                        var touchable = isTouchDevice();
                        if (touchable === false) {
                            // 此處改為 alert 提示
                            alert(lineMSG);
                        } else {
                            var title = ccaption;
                            var description = ccontent;
                            var url = title + '%0D%0A' + description + '%0D%0A' + appRoot + '/info/' + cid + '?lang=' + nowlang;
                            top.location.href = 'http://line.naver.jp/R/msg/text/?' + url;
                        }
                    }

                    $('.courseinfo .ln,.qrcode .ln').click(lineShare);
                }
            }
        });
        setTimeout(function () {
        	if (!isCourseInfoBarShow) {
                parent.sysbar_zoom = parent.sysbar_zoom*1 + 43;
                $('.courseinfo').show();
                isCourseInfoBarShow = true;
        	}
            if( ~ua.indexOf('chrome') ){
                parent.refresh();
            } else {
                parent.document.getElementById('envStudent').setAttribute('rows', parent.sysbar_zoom + ', *');
            }
            $('.courseinfo-bottom-line').show();
        },0);
    } else {
        parent.sysbar_zoom = parent.original_sysbar_zoom;
        $('.courseinfo').hide();
        isCourseInfoBarShow = false;
        if( ~ua.indexOf('chrome') ){
            parent.refresh();
        } else {
            parent.document.getElementById('envStudent').setAttribute('rows', parent.sysbar_zoom + ', *');
        }
    }
}

/**
 * 顯示 toggle 箭頭
 * @param {boolean} bol 顯示或隱藏
 */
function showToggleArrow() {
    if (parent.document.getElementById("envMooc").cols === '0,*') {
        $('#toggle-arrow').hide();
    } else {
        $('#toggle-arrow').show();
    }
}