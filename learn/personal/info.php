<?php
	/**
	 * 設定個人資料
	 *
	 * 建立日期：2003/02/21
	 * @author  ShenTing Lin
	 * @version $Id: info.php,v 1.1 2010/02/24 02:39:10 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
	require_once(sysDocumentRoot . '/lang/personal.php');
	require_once(sysDocumentRoot . '/lang/register.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/mooc/models/school.php');  //使用 getSchoolStudentMooc
    require_once(sysDocumentRoot . '/lib/editor.php');

	$sysSession->cur_func='400400500';
	$sysSession->restore();
	if (!aclVerifyPermission(400400500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	if (!isset($DIRECT_MEMBER) || empty($username)) {
		$username = $sysSession->username;
		$uri_target = 'info1.php';
		$uri_parent = 'about:blank';
	}

	$rsSchool = new school();

	// 不能隱藏的欄位
	$not_hidden = array('last_name','first_name','email');

	// mail 規則
	$mail_Rule = sysMailRule;

    setTicket();
    $ticket = md5($username . $sysSession->school_id . $sysSession->ticket);

    // 取得所有公司或學校，給 autocomplete 使用
    $companyRS = dbGetStMr('`'.sysDBname.'`.`WM_all_account`', 'distinct(`company`)', '1', ADODB_FETCH_ASSOC);
    $companyArray = array();
    if ($companyRS) {
        while (!$companyRS->EOF) {
            $companyArray[] = str_replace(array("\r","\n"),array('',''),addslashes($companyRS->fields['company']));
            $companyRS->MoveNext();
        }
    }
    $companyArray = implode("','", $companyArray);
    
    $headers = apache_request_headers();
    $isIE11 = preg_match('/Trident\/(\d+)/', $headers['User-Agent'], $regs) && intval($regs[1])> 6;
    
	$js = <<< BOF
    var iTicket           = "{$ticket}";
    /* 寫入localStorage，以利 我的設定和其他頁面都能正常運作 */
    localStorage.setItem('personal-info', '{$ticket}');
    var companies         = ['{$companyArray}'];
    var inputChange = false;
	var MSG_NotLoad       = "{$MSG['not_load'][$sysSession->lang]}";
	var MSG_TooLarge      = "{$MSG['too_large'][$sysSession->lang]}";
	var MSG_CheckPassword = "{$MSG['check_password'][$sysSession->lang]}";
	var MSG_FirstName     = "{$MSG['fill_first_name'][$sysSession->lang]}";
	var MSG_LastName      = "{$MSG['fill_last_name'][$sysSession->lang]}";
	var MSG_Email         = "{$MSG['fill_email'][$sysSession->lang]}";
	var MSG_Illegal       = "{$MSG['email_illegal'][$sysSession->lang]}";
	var MSG_PW_ILLEGAL    = "{$MSG['msg_passwd_illegal'][$sysSession->lang]}";

	var MSG_FN_ERROR      = "{$MSG['msg_first_name_error'][$sysSession->lang]}";
	var MSG_LN_ERROR      = "{$MSG['msg_last_name_error'][$sysSession->lang]}";
	var MSG_TEL           = "{$MSG['fill_tel'][$sysSession->lang]}";

    var MSG_OK            = "{$MSG['ok'][$sysSession->lang]}";
    var MSG_RETURN_INFO   = "{$MSG['btn_return_personal_info'][$sysSession->lang]}";
    var MSG_SAVING        = "{$MSG['msg_saving'][$sysSession->lang]}";
    var MSG_SAVE_SUCCESS  = "{$MSG['msg_save_success'][$sysSession->lang]}";
    var MSG_REQUIRED      = "{$MSG['msg_required'][$sysSession->lang]}";

    var MSG_DELETE_SUCCESS = "{$MSG['msg_delete_success'][$sysSession->lang]}";
    var MSG_DELETE_FAIL    = "{$MSG['msg_delete_fail'][$sysSession->lang]}";
    var MSG_DELETE_NOTHING = "{$MSG['msg_delete_nothing'][$sysSession->lang]}";
    var isIE11 = "{$isIE11}";

	function go() {
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.submit();
	}
	function chgPic() {
		var re;
		var obj1 = document.getElementById("PicRoom");
		var obj2 = document.getElementById("picture");
		var node = null;
		var txt = "";

		if ((obj1 == null) || (obj2 == null)) return false;
		txt = trim(obj2.value);
		if (txt == "") return false;
		re = /^[a-zA-Z]:/i;
		if (txt.match(re) != null) {
			re = /\\\\/ig;
			txt = "file:///" + txt.replace(re, "/");
		}
		obj1.innerHTML = "";

		node = document.createElement("img");
		node.setAttribute("id", "MyPic");
		node.setAttribute("borer", "0");
		node.setAttribute("align", "absmiddle");
		node.setAttribute("loop", "0");
		node.onload = picReSize;
		node.onerror = function () {
			if (obj1.innerHTML != "") {
				obj1.innerHTML = "";
				alert(MSG_NotLoad);
			}
		};
		obj1.appendChild(node);
		node.src = txt;
	}

    var QrcodeLoginTimer = null;
    var QrcodeLoginTries = 0;
    function QrcodeLoginTimerCheck() {
        QrcodeLoginTries++;
        if (QrcodeLoginTries > 36) {
            clearInterval(QrcodeLoginTimer);
            $.fancybox.close(true);
            return false;
        }
    }

    function getMyLoginQrcodeUrl() {
        var rtn = '';
        $.ajax({
            'type': 'POST',
            'dataType': 'json',
            'data': {'action' : 'getMyLoginQrcodeUrl'},
            'url': '/mooc/controllers/user_ajax.php',
            'async': false,
            'success': function (data) {
                if (parseInt(data.code) == 1){
                    rtn = data.data;
                }else{
                    rtn = 'about:blank';
                }
            },
            'error': function () {
                rtn = 'about:blank';
            }
        });
        return rtn;
    }
    window.onload = function() {
            $('#birthday').datepicker({
                changeMonth: true,
                changeYear: true,
                numberOfMonths:1,
                dateFormat: 'yy-mm-dd',
                yearRange: '-100:+0'
            });
            $("#pager-switch a").click(function() {
                $("#pager-switch li").removeClass('active');
                $(this).parent().addClass('active');
            });
            /* 公司名稱的自動完成 */
            $("#dataFrm2 input[name^='company']").autocomplete({
                minLength: 1,
                source: companies,
                focus: function(event, ui) {
                    event.preventDefault();
                },
                select: function(event, ui) {
                    event.preventDefault();
                    $(this).val(ui.item.value);
                },
                autoFocus: true
            });
            /* 用 icon 來控制資料是否顯示的 checkbox */
            $(".chk-icon").on('click', function() {
                var obj = $(this);
                var chk = obj.parent().find('input');
                inputChange = true;
                 if (chk.is(":checked")) {
                    chk.prop('checked', false);
                    obj.attr('class', 'icon-check-on');
                } else {
                    chk.prop('checked', true);
                    obj.attr('class', 'icon-check-off');
                }
                chk.trigger("change");
            });
            /* 偏好設定 */
            $("#preferFrm input, #preferFrm select").on("blur", function(){
                /* 清除全部提示 無法清除....晚點解決 ， blur 造成問題 */
                $(this).tooltip('destroy');
                var jvalue = {};
                if ($(this).attr('type') == 'checkbox') {
                    jvalue[$(this).attr('name')] = ($(this).attr('checked') == 'checked') ? $(this).val() : 0;
                } else {
                    jvalue[$(this).attr('name')] = $(this).val();
                }
                jvalue['ticket'] = iTicket;
                $.ajax({
                    url: 'm_info1.php',
                    data: jvalue,
                    type: 'post',
                    dataType: 'json',
                    success: function(res){
                        if (res.error != '' && res.error != null ) {
                             msg = res.error;
                            /* 後端驗證顯示訊息 */
                            for (var i = 0; i < msg.length; i++) {
                                $("[name='" + msg[i].id + "']").attr('title', msg[i].message)
                                                                .tooltip('toggle')
                                                                .addClass('alert-lcms-error')
                                                                .focus();
                            }
                        } else {
                            if (res.reload === true) {
                                reloadPage();
                            }
                        }
                    }
                });
            });
            if(isIE11) {
            $( "textarea" ).each(function( index ) {
				CKEDITOR.inline( $( this ).attr('id'), {
					extraAllowedContent: 'a(documentation);abbr[title];code',
					toolbar: 'EXAM'
				});
			});
            }
            /* 簽名檔 */
            $("#taglineFrm").on("submit", function(){
                $(this).find('input, textarea').tooltip('destroy');
                /* 從 editor 取得簽名檔的值 */
                $("#taglineFrm [name^='tagline[']").each(function (){
                    
                    if(isIE11) {
                        var editContent = CKEDITOR.instances.tagline.getData();
                    } else {
                        var editContent = getEditorInstance($(this).attr('name')).GetHTML();
                    }
                    
                    $(this).val(editContent);
                });
                $.ajax({
                    url: 'm_info1.php',
                    data: $("#taglineFrm").serialize() + "&ticket=" + iTicket,
                    type: 'post',
                    dataType: 'json',
                    success: function(res){
                        if (res.error != '' && res.error != null ) {
                             msg = res.error;
                            /* 後端驗證顯示訊息 */
                            for (var i = 0; i < msg.length; i++) {
                                $("#taglineFrm [name^='tagtitle']").eq(msg[i].id).attr('data-original-title', msg[i].message)
                                                                    .tooltip('toggle')
                                                                    .addClass('alert-lcms-error')
                                                                    .focus();
                            }
                            return;
                        }
                        alert(MSG_SAVE_SUCCESS);
                    },
                    error: function() {
                        if (window.console) {
                            console.log('ajax error!');
                        }
                    }
                });
                return false;
            });
            /* 密碼設定 */
            $("#pwdFrm").on("submit", function(){
                $(this).find('input').tooltip('destroy');
                $.ajax({
                    url: 'm_info1.php',
                    data: $("#pwdFrm").serialize() + "&ticket=" + iTicket,
                    type: 'post',
                    dataType: 'json',
                    success: function(res){
                        if (res.error != '' && res.error != null ) {
                             msg = res.error;
                            var firstObj, errorObj;
                            /* 後端驗證顯示訊息 */
                            for (var i = 0; i < msg.length; i++) {
                                errorObj = $("#pwdFrm [name='" + msg[i].id + "']");
                                if (i == 0) {
                                    firstObj = errorObj;
                                }
                                errorObj.attr('data-original-title', msg[i].message)
                                        .tooltip('toggle')
                                        .addClass('alert-lcms-error');
                            }
                            firstObj.focus();
                        } else {
                            alert(MSG_SAVE_SUCCESS);
                            $("#pwdFrm input").val('');
                        }
                    }
                });
                return false;
            });

            $("#dataFrm1 input, #dataFrm2 input, #dataFrm2 select, #picFrm input[type!=file]").on("change", function(){
                inputChange = true;
                if (!checkRequireData() && ($.inArray($(this).attr('name'),requireAry)>-1) && ($(this).val().length == 0)) {
                    return false;
                }
                var jvalue = {};
                var hidCnt = 0;
                if (($(this).attr('name')).indexOf('hid') > -1) {
                    /* hid 另外處理 */
                    $("#dataFrm1 input[name^='hid['], #dataFrm2 input[name^='hid['], #picFrm input[name^='hid[']").each(function() {
                        if ($(this).attr('checked') == 'checked') {
                            jvalue[$(this).attr('name')] = $(this).val();
                            hidCnt++;
                        }
                    });
                    if (hidCnt == 0) {
                        jvalue['hid[0]'] = 0;
                    }
                } else {
                    jvalue[$(this).attr('name')] = $(this).val();
                }
                jvalue['ticket'] = iTicket;
                $.ajax({
                    url: 'm_info1.php',
                    data: jvalue,
                    type: 'post',
                    dataType: 'json',
                    success: function(res){
                        if (res.error != '' && res.error != null) {
                            var msg = res.error;
                            /* 後端驗證顯示訊息 */
                            for (var i = 0; i < msg.length; i++) {
                                $("[name='" + msg[i].id + "']").attr('data-original-title', msg[i].message)
                                                                .tooltip('toggle')
                                                                .addClass('alert-lcms-error')
                                                                .focus();
                            }
                        } else {
                            $( "#saveLoadingDiv" ).isLoading({
                                'text':       MSG_SAVING,
                                'class':      "icon-loader",
                                'position':   "overlay",
                                'disableSource': false
                            });
                            updateData(res.show);
                            $( "#saveLoadingDiv" ).isLoading( "hide" );

                        }
                    }
                });
            });

            $("#dataFrm1 input, #dataFrm2 input, #dataFrm2 select, #picFrm input[type!=file]").on("blur", function(){
                if(inputChange == false && !(($(this).attr('name')).indexOf('birthday') > -1)) {
                    return;
                }
                inputChange = false;

                /* 清除全部提示 無法清除....晚點解決 ， blur 造成問題 */
                setTimeout(function() {
                    $(this).tooltip('destroy');
                },500);
            });

            /* 上傳圖片 */
            var options = {
                'dataType':       'json',
                'enctype':        'multipart/form-data',
                'beforeSubmit':   showRequest,
                'success':        showResponse
            };
            $('#picFrm input[type=file]').on('change', function() {
                $("#picFrm input[name=ticket]").attr('value', iTicket);
                $("#picFrm").submit();
            });
            $('#picFrm').ajaxForm(options);

            function showRequest(formData, jqForm, options) {
                $("#MyPic").tooltip('destroy');
                return true;
            }
            function showResponse(res, statusText, xhr, form)  {
                if (res.imgerror != '' && res.imgerror != null) {
                    var msg = res.imgerror;
                    /* 後端驗證顯示訊息 */
                    for (var i = 0; i < msg.length; i++) {
                        $("#MyPic").attr('data-original-title', msg[i].message).tooltip('toggle').addClass('alert-lcms-error');
                        $('#MyPic').parents('div:first').addClass('alert-lcms-error');
                    }
                } else {
                    var osrc = $("#MyPic").attr('src');
                    var posrc = $(window.parent.frames[1].document).find('.user-inner img').attr('src');
                    var timestamp = new Date().getTime();
                    if (osrc.indexOf('&timestamp=') === -1) {
                        $('#MyPic').attr('src', osrc + '&timestamp=' + timestamp);
                        $("#showPic").attr('src', osrc + '&timestamp='+timestamp);
                        $(window.parent.frames[1].document).find('.user-inner img').attr('src', posrc + '&timestamp=' + timestamp);
                    } else {
                        $('#MyPic').attr('src', osrc.substr(0, osrc.indexOf('&timestamp=')) + '&timestamp=' + timestamp);
                        $('#showPic').attr('src', osrc.substr(0, osrc.indexOf('&timestamp=')) + '&timestamp=' + timestamp);
                        $(window.parent.frames[1].document).find('.user-inner img').attr('src', posrc.substr(0, posrc.indexOf('&timestamp=')) + '&timestamp=' + timestamp);
                    }
                    $('#MyPic').parents('div:first').removeClass('alert-lcms-error');
                }
            }

            $("#qr-login").fancybox({
                maxWidth: 800,
                maxHeight: 600,
                fitToView: false,
                width: 400,
                height: 400,
                autoSize: false,
                closeClick: false,
                openEffect: 'none',
                closeEffect: 'none',
                beforeLoad: function(){
                    var url = getMyLoginQrcodeUrl();
                    this.href=url;
                },
                afterLoad: function(){
                    QrcodeLoginTimer = setInterval(function(){ QrcodeLoginTimerCheck() }, 5000);
                    QrcodeLoginTries = 0;
                },
                afterClose:function(){
                    clearInterval(QrcodeLoginTimer);
                }
           });


    };

    var activeFrm = "taglineFrm";
    /* 變更頁籤 */
    function showPage(obj, bid) {
        /* $(".box2").hide(); */
        if ($(obj).parent().hasClass('active')) {
            return;
        }
        $(".data6 .content").hide();
        $("#"+bid).show();
        switch(bid) {
            case 'preferDiv':
                $(".data6 .operate").show();
                $(".data6 > .title").css('background-image', 'url("/public/images/icon_setup_title_1.png")');
                $("#frmSubmit").unbind("click").attr('onclick', '$(\"#taglineFrm\").submit();').text(MSG_OK);
                break;
            case 'pwdDiv':
                $(".data6 .operate").show();
                $(".data6 > .title").css('background-image', 'url("/public/images/icon_setup_title_2.png")');
                $("#frmSubmit").unbind("click").attr('onclick', '$(\"#pwdFrm\").submit();').text(MSG_OK);
                break;
            case 'dataDiv1':
                $(".data6 .operate").hide();
                $(".data6 > .title").css('background-image', 'url("/public/images/icon_setup_title_3.png")');
                $("#frmSubmit").unbind("click").attr('onclick', 'showEditData(false);').text(MSG_RETURN_INFO);
                break;
        }
    }

    /* 顯示編輯個人資料頁 */
    function showEditData(bol) {
        if (bol) {
            $("#dataDiv").show();
            $("#dataDiv1").hide();
            $(".data6 .operate").show();
        } else {
            $("#dataDiv").hide();
            $("#dataDiv1").show();
            $(".data6 .operate").hide();
        }
    }


   /* 更新顯示資料 */
    function updateData(data) {
        $('#personalData').empty();
        $.each(data, function(i, v) {
            if (v.name == 'picture') {
                if ('' !== v.value) {
                    $("#showPic").attr('src', v.value);
                } else {
                    $("#showPic").attr('src', '/public/images/icon_personal_pic.png');
                }
                return;
            }
            if (v.name == 'realname') {
                $("#showRealName").html('( '+v.value+' )');
                return;
            }
            if (v.name == 'email') {
                $("#showEmail").html(v.value);
                // email不返回，基本資料還要用到
            }
            $('#personalData')
            .append($('<div class="layout-hr resp">')
                .append('<div class="property layout-child">' + v.title + '</div>')
                .append('<div class="value layout-child">' + v.value + '</div>')
            );
        });

   }

    /* 重整頁面 */
    function reloadPage() {
        var cid = ("{$sysSession->course_id}" == '' || "{$sysSession->course_id}" == '0') ? '10000000' :  "{$sysSession->course_id}";
        var gEnv = 1;
        switch("{$sysSession->env}") {
            case 'learn'   : gEnv = 1; break;
            case 'teach'   : gEnv = 2; break;
            case 'direct'  : gEnv = 3; break;
            case 'academic': gEnv = 4; break;
        }
        parent.chgCourse(cid, 0, gEnv, 'SYS_06_01_003');
    }

    /* 驗證個人資料必填欄位 */
    var requireAry = ['last_name', 'first_name','email'];
    var hasCheck = false;
    function checkRequireData() {
        var requiredError = false;
        $.each(requireAry, function (i, v) {
            var \$validInput = $("input[name="+v+"]");
            \$validInput.tooltip('destroy').removeClass('alert-lcms-error');;
            if ('' == \$validInput.val() || null == \$validInput.val()) {
                \$validInput.attr('data-original-title', MSG_REQUIRED)
                            .tooltip('toggle')
                            .addClass('alert-lcms-error');
                if (false === requiredError && false === hasCheck) {
                    \$validInput.focus();
                    hasCheck = true;
                }
                requiredError = true;
            }
        });
        if (requiredError) {
            return false;
        } else {
            hasCheck = false;
            return true;
        }

    }

BOF;

	$RS = getUserDetailData($username);
    // 簽名檔最多筆數
    $maxTagline = 1;
    $taglineRS = dbGetStMr('`WM_user_tagline`', '`serial`, `title`, `tagline`', "`username`='{$sysSession->username}'", ADODB_FETCH_ASSOC);
    // 新版的簽名檔沒有text，ctype 改為儲存時直接寫入 html，日後有需要再加回
    if ($taglineRS) {
        while (!$taglineRS->EOF) {
            $taglineList[] = array(
                'serial'    =>  $taglineRS->fields['serial'],
                'title'     =>  $taglineRS->fields['title'],
                'tagline'   =>  $taglineRS->fields['tagline']
            );
            $taglineRS->MoveNext();
        }
    }

	//(欄位型態，長度，最大長度，隱藏，欄位名稱，預設值，名稱，備註，顯示，必填)
    // 偏好設定
    $dd1 = array(
        array('checkbox', 15, 20, 0, 'msg_reserved'  , $RS['msg_reserved']  , $MSG['msg_reserved'][$sysSession->lang]  , $MSG['msg_msg_reserved'][$sysSession->lang]    ,0      ,0)
    );

    // 修改密碼
    $dd2 = array(
        array('password', 15, 20, 0, 'opassword'     , ''                   , $MSG['old_password'][$sysSession->lang]      , $MSG['msg_password_old'][$sysSession->lang]        ,0      ,1),
        array('password', 15, 20, 0, 'password'      , ''                   , $MSG['new_drowssap'][$sysSession->lang] , $MSG['msg_password_new'][$sysSession->lang]        ,0      ,1),
        array('password', 15, 20, 0, 'repassword'    , ''                   , $MSG['repassword2'][$sysSession->lang]    , $MSG['msg_repassword2'][$sysSession->lang]      ,0      ,1)
    );

    // 個人資料
    $dd3_1 = array(
        array('text'    , 15, 20, 1, 'last_name'     , $RS['last_name']     , $MSG['last_name'][$sysSession->lang]     , ''                                             ,0      ,1),
        array('text'    , 15, 20, 1, 'first_name'    , $RS['first_name']    , $MSG['first_name'][$sysSession->lang]    , ''                                             ,0      ,1),
        array('radio'   , 15, 20, 1, 'gender'        , $RS['gender']        , $MSG['gender'][$sysSession->lang]        , ''                                             ,4      ,0),
        array('date'    , 15, 20, 1, 'birthday'      , $RS['birthday']      , $MSG['birthday'][$sysSession->lang]      , $MSG['msg_birthday'][$sysSession->lang]        ,8      ,0)
    );

    $dd3_2 = array(
        array('file'    , 30, 50, 1, 'picture'       , ''                   , $MSG['picture'][$sysSession->lang]       , $MSG['msg_picture'][$sysSession->lang]         ,32     ,0)
    );

    $dd3_3 =array(
        array('text'    , 30, 60, 1, 'company'       , $RS['company']       , $MSG['company'][$sysSession->lang]       , ''                                             ,32768  ,0),
        array('text'    , 30, 60, 1, 'department'    , $RS['department']    , $MSG['department'][$sysSession->lang]    , ''                                             ,65536  ,0),
        array('text'    , 15, 30, 1, 'title'         , $RS['title']         , $MSG['title'][$sysSession->lang]         , ''                                             ,131072 ,0)
    );

    $dd3_4 =array(
        array('text'    , 30, 50, 1, 'email'         , $RS['email']         , $MSG['email'][$sysSession->lang]         , $MSG['msg_email'][$sysSession->lang]           ,0      ,1),
        array('text'    , 30, 255, 1, 'homepage'      , $RS['homepage']      , $MSG['homepage'][$sysSession->lang]      , $MSG['msg_email'][$sysSession->lang]           ,128    ,0),
        array('text'    , 15, 20, 1, 'home_tel'      , $RS['home_tel']      , $MSG['home_tel'][$sysSession->lang]      , $MSG['msg_home_tel'][$sysSession->lang]        ,256    ,0),
        array('text'    , 15, 20, 1, 'home_fax'      , $RS['home_fax']      , $MSG['home_fax'][$sysSession->lang]      , $MSG['msg_home_tel'][$sysSession->lang]        ,512    ,0),
        array('text'    , 30, 60, 1, 'home_address'  , $RS['home_address']  , $MSG['home_address'][$sysSession->lang]  , $MSG['msg_home_address'][$sysSession->lang]    ,1024   ,0),
        array('text'    , 15, 20, 1, 'office_tel'    , $RS['office_tel']    , $MSG['office_tel'][$sysSession->lang]    , $MSG['msg_home_tel'][$sysSession->lang]        ,2048   ,0),
        array('text'    , 15, 20, 1, 'office_fax'    , $RS['office_fax']    , $MSG['office_fax'][$sysSession->lang]    , $MSG['msg_home_tel'][$sysSession->lang]        ,4096   ,0),
        array('text'    , 30, 60, 1, 'office_address', $RS['office_address'], $MSG['office_address'][$sysSession->lang], $MSG['msg_home_address'][$sysSession->lang]    ,8192   ,0),
        array('text'    , 15, 17, 1, 'cell_phone'    , $RS['cell_phone']    , $MSG['cell_phone'][$sysSession->lang]    , ''                                             ,16384  ,0)
    );

    $hidd = $RS['hid'];
    $defaultCountry =   array(
                            "NULL"=>$MSG['please_select'][$sysSession->lang],
                            "TW"=>$MSG['TW'][$sysSession->lang],
                            "CH"=>$MSG['CH'][$sysSession->lang],
                            "JA"=>$MSG['JA'][$sysSession->lang],
                            "IN"=>$MSG['IN'][$sysSession->lang],
                            "US"=>$MSG['US'][$sysSession->lang],
                            "AS"=>$MSG['AS'][$sysSession->lang],
                            "O"=>$MSG['other'][$sysSession->lang]
                        );
    $defaultEdu =   array(
                        "NULL"=>$MSG['please_select'][$sysSession->lang],
                        "P"=>$MSG['elementary_school'][$sysSession->lang],
                        "H"=>$MSG['junior_high_school'][$sysSession->lang],
                        "S"=>$MSG['high_school'][$sysSession->lang],
                        "U"=>$MSG['university'][$sysSession->lang],
                        "M"=>$MSG['masters_degree'][$sysSession->lang],
                        "D"=>$MSG['doctoral_degree'][$sysSession->lang],
                        "O"=>$MSG['other'][$sysSession->lang]
                    );
    $defaultUserStatus =    array(
                                "NULL"=>$MSG['please_select'][$sysSession->lang],
                                "S"=>$MSG['student'][$sysSession->lang],
                                "W"=>$MSG['at_work'][$sysSession->lang]
                            );
    // 組合表單 html
    function displayFormLayout($data) {
        global $MSG, $sysSession, $hidd, $defaultCountry, $defaultEdu, $defaultUserStatus;
        $totalHidd = 0;
        $totalCheck = true;
        echo '<div class="layout-hr">
            <div class="data layout-child">';
                for ($i = 0; $i < count($data); $i++) {
                    $Required = ($data[$i][9] == 1) ? '<span style="color: red;">*</span>' : '';
                    echo '<div class="layout-hr">
                        <div class="key layout-child" for="'.$data[$i][4].'"'.(($data[$i][4]=='picture')?' style="vertical-align:middle;"':'').'>' . $Required . $data[$i][6] . '</div>
                        <div class="value layout-child" style="padding-left:10px;">';

                    // 隱藏欄位
                    if ($data[$i][8] > 0) {
                        echo '<div class="func layout-child" style="display:inline">';
                        echo '<div class="chk-icon '.(($hidd&$data[$i][8])?'icon-check-off':'icon-check-on').'" style="padding-right:10px;"></div>';
                        echo '<input type="checkbox" name="hid['.$data[$i][4].']" value="'.$data[$i][8].'" '.(($hidd&$data[$i][8])?'checked="checked"':'').' style="display:none;" />';
                        echo '</div>';
                    }else{
                        echo '<div style="display:inline;padding-left:50px;">&nbsp;</div>';
                    }

                            switch ($data[$i][0]) {
                                case 'password':
                                case 'text':
                                    showXHTML_input($data[$i][0], $data[$i][4], htmlspecialchars($data[$i][5]), '', 'maxlength="' . $data[$i][2] . '" size="' . $data[$i][1] . '" placeholder="' . $data[$i][7] . '" ');
                                    break;
                                case 'file':
                                    $enc = @mcrypt_encrypt(MCRYPT_DES, sysTicketSeed . $_COOKIE['idx'], $username, 'ecb');
                                    $ids = base64_encode($enc);
                                    echo '<div class="photo-xl">
                                              <img src="showpic.php?a=' . $ids . '&' . uniqid('') . '" type="image/jpeg" id="MyPic" name="MyPic" borer="0" align="absmiddle" onload="picReSize()" loop="0">
                                          </div>
                                          <a href="javascript:;" class="dropbtn" style="position: relative; top: -2.2em; left: -1.9em; display: inline-block;">
                                              <img src="/theme/default/learn_mooc/drop_1.png">
                                          </a>';
                                    echo '<div style="display: inline-table; position: relative; top: 0.5em; left: -0.8em;">';
                                        showXHTML_input($data[$i][0], $data[$i][4], $data[$i][5], '', 'id="' . $data[$i][4] . '"');
                                        if($data[$i][4]== 'picture') echo '<div style="line-height: 2em; color: #F3800F;">(' . $MSG['head_shot'][$sysSession->lang] . ')</div>';
                                    echo '</div>';
                                    break;
                                case 'radio':
                                    if ($data[$i][4] == 'gender')
                                    {
                                        $sel = array(
                                                'M'=>$MSG['male'][$sysSession->lang],
                                                'F'=>$MSG['female'][$sysSession->lang],
                                                'N'=>$MSG['not_marked'][$sysSession->lang]
                                            );
                                    }else if ($data[$i][4] == 'msg_reserved'){
                                        $sel = array(
                                                '0'=>$MSG['not_reserved'][$sysSession->lang],
                                                '1'=>$MSG['reserved'][$sysSession->lang]
                                            );
                                    }
                                    showXHTML_input($data[$i][0], $data[$i][4], $sel, $data[$i][5], 'class="radio inline"');
                                    break;
                                case 'date':
                                    showXHTML_input('text', 'birthday', $data[$i][5], '', 'id="birthday" readonly="readonly" class="cssInput" style="cursor:pointer;"');
                                    break;
                                case 'select':
                                    $sel = array();
                                    $val = '';
                                    if ($data[$i][4] == 'theme') {
                                        $sel = array('default'=>'default');
                                        $val = empty($data[$i][5]) ? $sysSession->theme : $data[$i][5];
                                    } else if($data[$i][4] == 'country') {
                                        $sel = $defaultCountry;
                                        $val= empty($data[$i][5]) ? 'NULL' : $data[$i][5];

                                    } else if($data[$i][4] == 'user_status') {
                                        $sel = $defaultUserStatus;
                                        $val = empty($data[$i][5]) ? "NULL" : $data[$i][5];
                                    } else if($data[$i][4] == 'education') {
                                        $sel = $defaultEdu;
                                        $val = empty($data[$i][5]) ? "NULL" : $data[$i][5];
                                    }
                                    showXHTML_input('select', $data[$i][4], $sel, $val, '');
                                    break;
                                case 'checkbox':
                                    showXHTML_input($data[$i][0], $data[$i][4], 1, $data[$i][5], 'class="inline '.$data[$i][5].'"');
                                    echo $data[$i][7];
                                    break;
                                default:
                                    echo '&nbsp;';
                            }
                            if ($data[$i][8] != 0) {
                                $totalHidd += $data[$i][8];
                                // 判斷舊的值有沒有全部都打勾，有的話才check
                                if (!($hidd&$data[$i][8])) {
                                    $totalCheck = false;
                                }
                                // echo '<input type="checkbox" name="hid['.$data[$i][4].']" value="'.$data[$i][8].'" '.(($hidd&$data[$i][8])?'checked="checked"':'').'/>';
                            }
                    echo '</div></div>';
                }
            echo '</div>';
        echo '</div>';
    }

    // 組簽名檔 html
    function displayTaglineLayout() {
        global $maxTagline, $taglineList, $isIE11;
        $taglineCnt = 0;
        echo '<div id="tagline-display">';
        if (!empty($taglineList)) {
            for ($i = 0, $newBtn=0; $i < $maxTagline; $i++) {
                echo '<div class="tagline-edit">
                    
                    <input type="hidden" name="serial[]" value="' . ($taglineList[$i]['serial']) . '"/>
                    <input type="hidden" name="tagtitle[]" value="'.'tagline1'/* stripslashes($taglineList[$i]['title'])*/.'" style="margin-bottom: 1em;"/>';
                    if($isIE11) {
                        echo '<textarea name="tagline['.$i.']" id="tagline" rows="5" cols="10">'.stripslashes($taglineList[$i]['tagline']).'</textarea>';	
                    }  else {
	                    $oEditor = new wmEditor;
	                    $oEditor->setValue(stripslashes($taglineList[$i]['tagline']));
	                    $oEditor->addContType('isHTML', 1);
	                    $oEditor->generate('tagline['.$i.']', '500', '450');
                    }
                echo '</div>';
                $taglineCnt++;
            }
        }
        for ($i = $taglineCnt, $newBtn=0; $i < $maxTagline; $i++) {
            echo '<div class="tagline-edit">
                
                <input type="hidden" name="serial[]" value="-' . ($i+1) . '"/>
                <input type="hidden" name="tagtitle[]" value="'.'tagline1'/* stripslashes($taglineList[$i]['title']) */.'" style="margin-bottom: 1em;"/>';
                if($isIE11) {
                    echo '<textarea name="tagline['.$i.']" id="tagline" rows="5" cols="10">'.stripslashes($taglineList[$i]['tagline']).'</textarea>';	
                }  else {
	                $oEditor = new wmEditor;
	                $oEditor->setValue(stripslashes($taglineList[$i]['tagline']));
	                $oEditor->addContType('isHTML', 1);
	                $oEditor->generate('tagline['.$i.']', '500', '450');
                }
            echo '</div>';
        }

        echo '</div>';

    }

    // 顯示的值對應
    $enableValue = array(
        'picture'        => 32,
        'gender'         => 4,
        'email'          => 0,
        'birthday'       => 8,
        'company'        => 32768,
        'department'     => 65536,
        'title'          => 131072,
        'cell_phone'     => 16384,
        'homepage'       => 128,
        'home_tel'       => 256,
        'home_fax'       => 512,
        'home_address'   => 1024,
        'office_tel'     => 2048,
        'office_fax'     => 4096,
        'office_address' => 8192,
    );
    /* 顯示公開資訊 */
    function displayUserData($data) {
        global $MSG, $sysSession, $hidd, $enableValue, $defaultCountry, $defaultEdu, $defaultUserStatus;
        $data['gender'] = ($data['gender'] == 'N') ? $MSG['not_marked'][$sysSession->lang] : (($data['gender'] == 'M') ? $MSG['male'][$sysSession->lang] : $MSG['female'][$sysSession->lang]);
        $data['birthday'] = ($data['birthday'] == '0000-00-00' || $data['birthday'] == '') ? $MSG['not_marked'][$sysSession->lang] : $data['birthday'];
        $data['country'] = $defaultCountry[$data['country']];
        $data['education'] = $defaultEdu[$data['education']];
        $data['user_status'] = $defaultUserStatus[$data['user_status']];
        foreach($enableValue as $k => $v) {
            if ($k == 'picture') continue;
            if (!($hidd&$v)) {
                // 防止XSS攻擊
                echo '<div class="layout-hr resp">
                    <div class="layout-child property">' . $MSG[$k][$sysSession->lang] . '</div>
                    <div class="layout-child value" style="word-break: break-all;">' . htmlspecialchars($data[$k]) . '</div>
                </div>';
            }
        }
    }

	showXHTML_head_B($MSG['tabs_personal'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/bootstrap/css/bootstrap.min.css");
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/bootstrap/css/bootstrap-responsive.min.css");
    showXHTML_CSS('include', "/lib/jquery/css/jquery-ui-1.8.22.custom.css");
    showXHTML_CSS('include', "/public/css/common.css");
    showXHTML_CSS('include', "/public/css/layout.css");
    showXHTML_CSS('include', "/public/css/component.css");
    showXHTML_CSS('include', "/public/css/settings.css");
    showXHTML_CSS('include', "/theme/default/fancybox/jquery.fancybox.css");

    echo '<style>
    .form-horizontal input {
        vertical-align: bottom;
    }
    
    .cke_textarea_inline
		{
			padding: 10px;
			height: 200px;
			width : 525px;
			overflow: auto;

			border: 1px solid gray;
			-webkit-appearance: textfield;
		}
    </style>';

    showXHTML_script('include', '/lib/jquery/jquery.min.js', true, null, 'UTF-8');
    showXHTML_script('include', '/lib/jquery/jquery-ui-1.8.22.custom.min.js', true, null, 'UTF-8');
    showXHTML_script('include', '/lib/jquery.form.min.js');
    showXHTML_script('include', '/lib/ckeditor/ckeditor.js');
    showXHTML_script('include', '/theme/default/fancybox/jquery.fancybox.pack.js');
    showXHTML_script('include', '/lib/filter_spec_char.js');
    showXHTML_script('include', '/learn/personal/lib.js');
    showXHTML_script('include', '/lib/common.js');
    showXHTML_script('include', "/public/js/third_party/is-loading/jquery.isloading.js");
    showXHTML_script('include', "/theme/default/bootstrap/js/bootstrap-tooltip.js");
	$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
	$calendar->load_files();
	showXHTML_script('inline', $js, false);
	showXHTML_head_E('');
	showXHTML_body_B('');
        $spacingHtml = '<!-- 撐開距離用 -->
                        <div class="layout-hr">
                            <div class="data layout-child">
                                <div class="layout-hr">
                                    <div class="key layout-child" style="padding: 0.8em 2em;"></div>
                                    <div class="value layout-child" style="padding: 0.8em 2em;"></div>
                                </div>
                            </div>
                        </div>';
        $spacingHtml2 = '<!-- 撐開距離用 -->
                        <div class="layout-hr">
                            <div class="data layout-child">
                                <div class="layout-hr">
                                    <div class="key key-lg layout-child" style="padding: 0.8em 2em;"></div>
                                    <div class="value layout-child" style="padding: 0.8em 2em;"></div>
                                </div>
                            </div>
                        </div>';
        echo '<div class="box1">
            <div class="content" style="padding: 0;">
                <div class="data6">
                    <div class="title">
                        <ul id="pager-switch" class="nav nav-tabs nav-orange">
                            <li class="active"><a href="#" onclick="showPage(this, \'dataDiv1\');">'.$MSG['msg_personal_profile'][$sysSession->lang].'</a></li>
                            <li><a href="#" onclick="showPage(this, \'preferDiv\');">'.$MSG['msg_preferences'][$sysSession->lang].'</a></li>
                            <li><a href="#" onclick="showPage(this, \'pwdDiv\');">'.$MSG['msg_change_pwd'][$sysSession->lang].'</a></li>
                        </ul>
                    </div>';
                    // 修改偏好
                    echo '<div id="preferDiv" class="content" style="display:none;">' .
                        $spacingHtml .
                        '<form id="preferFrm" name="preferFrm" class="form-horizontal">';
                            displayFormLayout($dd1);
                        echo '</form>
                        <form id="taglineFrm" name="taglineFrm" class="form-horizontal">
                            <div class="layout-hr">
                                <div class="data layout-child">
                                    <div class="layout-hr">
                                        <div class="key layout-child" for="o_pwd">' . $MSG['tabs_tagline'][$sysSession->lang] . '</div>
                                        <div class="value layout-child">';
                                            displayTaglineLayout();
                                        echo '</div>
                                    </div>
                                </div>
                                <div class="func layout-child">
                                </div>
                            </div>
                        </form>' .
                        $spacingHtml .
                    '</div>';
                    // 修改密碼
                    echo '<div id="pwdDiv" class="content" style="display:none;">' .
                        $spacingHtml .
                        '<form id="pwdFrm" name="pwdFrm" class="form-horizontal" action="m_info1.php" method="post" enctype="multipart/form-data">';
                            displayFormLayout($dd2);
                        echo '</form>' .
                        $spacingHtml .
                    '</div>';
                    // 修改個人資料
                    echo '<div id="dataDiv" class="content" style="display:none;">
                        <div id="saveLoadingDiv" class="layout-hr">
                            <div class="data layout-child">
                                <div class="layout-hr">
                                <div class="key layout-child" style="padding: 0.8em 2em;"></div>
                                <div class="value layout-child" style="padding: 0.8em 2em;"></div>
                            </div>
                            </div>
                        </div>
                        <form id="dataFrm1" name="dataFrm1" class="form-horizontal">
                            <div class="layout-hr">
                                <div class="data layout-child">
                                    <div class="layout-hr">
                                        <div class="key layout-child" for="last_name">'.$MSG['username'][$sysSession->lang].'</div>
                                        <div class="value layout-child" style="padding-left:70px;">'.$username.'</div>
                                    </div>
                                </div>
                                <div class="func layout-child">
                                </div>
                            </div>';
                            displayFormLayout($dd3_1);
                        echo '</form>
                        <div class="divider-horizontal"></div>
                        <form id="picFrm" name="picFrm" class="form-horizontal" action="m_info1.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="ticket" value=""/><input type="hidden" name="change_pic" value=""/>';
                            
                            displayFormLayout($dd3_2);
                            echo '<div class="divider-horizontal"></div>';
                        echo '</form>
                        <form id="dataFrm2" name="dataFrm2" class="form-horizontal">';
                            displayFormLayout($dd3_3);
                            echo '<div class="divider-horizontal"></div>';
                            displayFormLayout($dd3_4);
                        echo '</form>' .
                        $spacingHtml .
                    '</div>';
                    // 顯示個人資料
                    echo '<div id="dataDiv1" class="content">' .
                        $spacingHtml2 .
                        '<div class="layout-hr">
                            <div class="data layout-child">
                                <div class="layout-hr">
                                    <div class="key key-lg layout-child">
                                        <div class="photo-3l">';
                                            if (!($hidd&$enableValue['picture'])) {
                                                echo '<img src="showpic.php?a=' . $ids . '&' . uniqid('') . '" type="image/jpeg" id="showPic" borer="0" align="absmiddle" onload="picReSize()" loop="0">';
                                            } else {
                                                echo '<img src="/public/images/icon_personal_pic.png" type="image/jpeg" id="showPic" borer="0" align="absmiddle" onload="picReSize()" loop="0">';
                                            }
                                        echo '</div>
                                    </div>
                                    <div class="value layout-child">
                                        <div class="main-info">
                                            <div class="id">' .
                                                $sysSession->username .
                                                '<div id="showRealName" class="name">( ' . htmlspecialchars($sysSession->realname) . ')</div>
                                            </div>
                                            <div id="showEmail" class="mail">' . htmlspecialchars($sysSession->email) . '</div>
                                            <button class="btn btn-blue" onclick="showEditData(true);">'.$MSG['msg_edit_profile'][$sysSession->lang].'</button>';
                                            if (!empty($_COOKIE["persist_idx"]) && ($sysSession->username != 'guest')){
                                                $persistRow = dbGetRow('WM_persist_login','*',sprintf("persist_idx='%s' and expire_time>NOW()",mysql_escape_string($_COOKIE["persist_idx"])));
                                                // 仍是有效的cookie, 導向登入頁
                                                if (is_array($persistRow) && ($persistRow['username'] == $sysSession->username)){
                                                    echo '<button class="btn" onclick="top.document.location.href=\'/logout.php\';return false;" style="margin-left:10px;">'.$MSG['btn_logout'][$sysSession->lang].'</button>';
                                                }
                                            }
                                            $multiLogin = dbGetOne('WM_school', 'multi_login', "school_id={$sysSession->school_id} AND school_host='{$_SERVER['HTTP_HOST']}'");
                                            if ($multiLogin == 'Y'){
                                                if (($sysSession->username != 'guest')&&($sysSession->username != sysRootAccount)){
                                                    // QrCode登入
                                                    echo '<a id="qr-login" data-fancybox-type="iframe" href="about:blank" style="margin-left:10px;" class="btn" title="">'.$MSG['btn_show_my_qrcode'][$sysSession->lang].'</a>';
                                                }
                                            }
                                        echo '</div>
                                    </div>
                                </div>
                            </div>
                            <div class="func layout-child">
                            </div>
                        </div>
                        <div class="divider-horizontal"></div>
                        <div>
                            <div class="layout-hr">
                                <div class="data layout-child" style="width:100%">
                                    <div class="layout-hr">
                                        <div class="key key-lg layout-child"></div>
                                        <div class="value layout-child">
                                            <div id="personalData" class="sub-info">';
                                                displayUserData($RS);
                                            echo '</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="func layout-child">
                                </div>
                            </div>
                        </div>' .
                        $spacingHtml2 .
                    '</div>
                    <div class="operate" style="display:none">
                        <button id="frmSubmit" class="btn btn-blue" onclick="location.reload();">'.$MSG['btn_return_personal_info'][$sysSession->lang].'</button>' .
                        // <button class="btn">取消</button>
                    '</div>
                </div>
            </div>
        </div>
    </div>';
showXHTML_body_E('');
?>
