<?php
	/**
	 * 建立或修改課程
	 *
	 * @todo 建立多語系的複合欄位
	 * 建立日期：2002/08/23
	 * @author  ShenTing Lin
	 * @version $Id: course_property.php,v 1.1 2010/02/24 02:38:20 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	// mooc 模組開啟的話將網頁導向
	if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
	    header('LOCATION: /teach/course/m_course_property.php');
	    exit;
	}
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/academic/course/course_lib.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/course_manage.php');
    if (sysEnableAppCoursePicture) {
        // APP課程圖片模組有啟用
        require_once(sysDocumentRoot . '/lang/app_course_manage.php');
    }
	require_once(sysDocumentRoot . '/lib/quota.php');

	$sysSession->cur_func = '700300200';
	$sysSession->restore();
#======== function ===========
	/**
		是否已達課程限量
		@return bollean
	*/
	function isReachCourseLimit()
	{
		if (sysCourseLimit == 0) return false;		//無課程上限
		list($nowCourseNum) = dbGetStSr('WM_term_course','count(*)','kind = "course" and status != 9', ADODB_FETCH_NUM);
		if ($nowCourseNum >= sysCourseLimit) return true;
		return false;
	}
#======== main ===============
	if (!aclVerifyPermission(700300200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$actType = '';
	$today   = date('Y-m-d');
	$title   = '';
	$teacher = '';
	$status  = 5;
	$cont_id = '';
	$book    = '';    // 書籍
	$url     = 'http://';    // 參考連結
	$intro   = '';    // 簡介
	$credit  = '';    // 學分
	$n_limit = '';    // 正式生人數
	$a_limit = '';    // 旁聽生人數
	$usage   = 0;     // 使用率
	$quota   = '';    // Quota
	if (!isset($contents)) $contents = '';
	$default_ta_can_sets = array('caption','content_id','en_begin','en_end','st_begin','st_end','status',
                          	     'texts','url','content','n_limit',
	                             'a_limit','fair_grade','review','cparent');	// 允許教師更改的欄位

	$ta_can_sets = array();	// 允許教師更改的欄位

    if (sysEnableAppCoursePicture) {
        // APP課程圖片模組有啟用
        $appPictureInfoFile = sysDocumentRoot . sprintf('/user/%1s/%1s/%s/appCoursePictureData.txt', substr($sysSession->username, 0, 1), substr($sysSession->username, 1, 1), $sysSession->username);
        if (is_file($appPictureInfoFile)) {
            unlink($appPictureInfoFile);
        }
    }

	// 新增課程
	if (empty($_POST['ticket'])) {
		$actType = 'Create';
		if (isReachCourseLimit())
		{
			header("Location: /academic/course/course_limit.php");
			exit;
		}
		$title = $MSG['title_add_course'][$sysSession->lang];

		$quota = getDefaultQuota();
		$lang = array(
			'Big5'        => '',
			'GB2312'      => '',
			'en'          => '',
			'EUC-JP'      => '',
			'user_define' => ''
		);
		$fair_grade   = 60;

		$ta_can_sets = array('caption','content_id','st_begin','st_end','status',
							 'texts','url','content','n_limit',
							 'a_limit','fair_grade');

        if (sysEnableAppCoursePicture) {
            // APP課程圖片模組有啟用
            // 課程圖片設定 - Begin
            $appPictureInfoFile = sysDocumentRoot . sprintf('/user/%1s/%1s/%s/appCoursePictureData.txt', substr($sysSession->username, 0, 1), substr($sysSession->username, 1, 1), $sysSession->username);
            if (is_file($appPictureInfoFile)) {
                unlink($appPictureInfoFile);
            }
            $csid = 99999999;                   // 建立新課程給予一個假的課程編號
            $frameId = 'main';                  // 管理者的frame id 是 main，用於後續jQuery
            // 課程圖片設定 - End
        }
        $appNewCourse = true;               // 建立新課程
	}

	// 修改課程
	$ticket = md5($sysSession->school_id . $sysSession->school_name . 'Edit' . $sysSession->username);
	if (trim($_POST['ticket']) == $ticket) {
		$actType = 'Edit';
		if (defined('ENV_TEACHER')) {
			$title = $MSG['tabs_course_set'][$sysSession->lang];
			$csid = $sysSession->course_id;
			$_POST['csid'] = sysEncode($csid);

            if (sysEnableAppCoursePicture) {
                // APP課程圖片模組有啟用
                $frameId = 'c_main';        // 教師辦公室的frame id 是 c_main，用於後續jQuery
            }
		} else {
			$title = $MSG['title_modify_course'][$sysSession->lang];
			$csid = trim($_POST['csid']);
			$csid = intval(sysDecode($csid));

            if (sysEnableAppCoursePicture) {
                // APP課程圖片模組有啟用
                $frameId = 'main';          // 管理者的frame id 是 main，用於後續jQuery
            }
		}
        $appNewCourse = false;            // 非建立新課程

		$RS = getCourseData($csid);

		$ta_can_sets = explode(',',$RS['ta_can_sets']);	// 允許教師更改的欄位
		// 報名開始
		if (defined('ENV_TEACHER')) {
			if(in_array('en_begin',$ta_can_sets)) {
				$en_begin = explode('-', $RS['en_begin']);
			}else{
				$en_begin = (intval($RS['en_begin']) == 0) ? $MSG['now'][$sysSession->lang] : $RS['en_begin'];
			}
		}else{
			$en_begin = explode('-', $RS['en_begin']);
		}

		// 報名結束
		if (defined('ENV_TEACHER')) {
			if(in_array('en_end',$ta_can_sets)) {
				$en_end = explode('-', $RS['en_end']);
			}else{
				$en_end = (intval($RS['en_end']) == 0) ? $MSG['forever'][$sysSession->lang] : $RS['en_end'];
			}
		}else{
			$en_end = explode('-', $RS['en_end']);
		}

		// 上課開始
		if (defined('ENV_TEACHER')) {
			if(in_array('st_begin',$ta_can_sets)) {
				$st_begin = explode('-', $RS['st_begin']);
			}else{
				$st_begin = (intval($RS['st_begin']) == 0) ? $MSG['now'][$sysSession->lang] : $RS['st_begin'];
			}
		}else{
			$st_begin = explode('-', $RS['st_begin']);
		}
		// 上課結束
		if (defined('ENV_TEACHER')) {
			if(in_array('st_end',$ta_can_sets)) {
				$st_end = explode('-', $RS['st_end']);
			}else{
				$st_end = (intval($RS['st_end']) == 0) ? $MSG['forever'][$sysSession->lang] : $RS['st_end'];
			}
		}else{
			$st_end = explode('-', $RS['st_end']);
		}


		$lang    = old_getCaption($RS['caption']);	// 課程名稱
		$teacher = $RS['teacher'];	            // 教師

		// 查詢教材的狀態不為 disable
		list($content_exist) = dbGetStSr('WM_content','count(*)','content_id=' . $RS['content_id'] . ' and status!="disable"', ADODB_FETCH_NUM);
		if($content_exist > 0)
			$cont_id     = $RS['content_id'];  // 教材
		else
			$cont_id     = '';  // 教材

		$status      = $RS['status'];      // 狀態
		$book        = $RS['texts'];       // 書籍
		$url         = $RS['url'];         // 參考連結
		$intro       = $RS['content'];     // 簡介
		$credit      = $RS['credit'];      // 學分
		$n_limit     = $RS['n_limit'];     // 正式生人數
		$a_limit     = $RS['a_limit'];     // 旁聽生人數
		$usage       = $RS['quota_used'];
		$quota       = $RS['quota_limit'];
		$rteacher    = is_array($RS['real_teacher']['teacher'])    ? implode(', ', $RS['real_teacher']['teacher'])    : '&nbsp;'; // 教師
		$rinstructor = is_array($RS['real_teacher']['instructor']) ? implode(', ', $RS['real_teacher']['instructor']) : '&nbsp;'; // 講師
		$rassistant  = is_array($RS['real_teacher']['assistant'])  ? implode(', ', $RS['real_teacher']['assistant'])  : '&nbsp;'; // 助教
		$fair_grade  = $RS['fair_grade'];  // 及格成績

		// 取得此課程屬於那些群組
		$csParent = getCourseParents($csid);
		$tmp  = array();
		foreach ($csParent as $key => $val) {
			$tmp[] = $val[$sysSession->lang];
		}
		$gps = implode(', ', $tmp);
		$selgpids = '"' . implode('","', array_keys($csParent)) . '"';

	}

    if (sysEnableAppCoursePicture) {
        // APP課程圖片模組有啟用
        $appCsid = base64_encode($csid);    // 編碼課號給後續app設定用
    }

	if (empty($actType)) die($MSG['msg_access_deny'][$sysSession->lang]);

	// 設定車票
	// setTicket();
	$ticket = md5($actType . $sysSession->ticket . $sysSession->school_id . $sysSession->school_name . $sysSession->username);

    if ($appNewCourse) {
        $appNewCourseForJS = 'true';
    } else {
        $appNewCourseForJS = 'false';
    }

	$js = <<< BOF
	var act      = "{$actType}";
	var lang     = "{$sysSession->lang}";
	var xmlDocs  = null, xmlHttp = null;
	var selGpIDs = [{$selgpids}];

	var MSG_DATE_ERROR      = "{$MSG['msg_date_error'][$sysSession->lang]}";
	var MSG_DATE_ERROR2      = "{$MSG['msg_date_error2'][$sysSession->lang]}";
	var MSG_CREATE_CONTENT  = "{$MSG['msg_need_content'][$sysSession->lang]}";
	var MSG_CONTENT_NOT_USE = "{$MSG['msg_not_use_content'][$sysSession->lang]}";

	var appFrameId = "{$frameId}";
	var MSG_COURSE_PICTURE_FAIL = "{$MSG['msg_save_fail'][$sysSession->lang]}";
	var MSG_COURSE_PICTURE_SUCCESS = "{$MSG['msg_save_success'][$sysSession->lang]}";
	var appCoursePictureBrowser;
	var appNewCourse = {$appNewCourseForJS};

	var MSG_COURSE_NAME_ERROR = "{$MSG['msg_course_name_style_error'][$sysSession->lang]}";
 // //////////////////////////////////////////////////////////////////////////
 	function showGroup() {
		modCGTreeShow(selGpIDs);
	}
// //////////////////////////////////////////////////////////////////////////
	function checkData() {
		if(!chk_multi_lang_input(1, true, "{$MSG['msg_input_course_name'][$sysSession->lang]}", un_htmlspecialchars(MSG_COURSE_NAME_ERROR)))
			return false;
		var node = document.getElementById("actForm");
		if (node == null) return false;

		// 報名截止日要大於開始報名日 (en_begin)
		if (typeof(node.ck_en_begin) != 'undefined'){
			if ((typeof(node.ck_en_begin) != 'undefined') && (typeof(node.ck_en_end) != 'undefined') &&  node.ck_en_begin.checked === true && node.ck_en_end.checked === true) {
				val1 = node.en_begin_date.value.replace(/[\D]/ig, '');
				val2 = node.en_end_date.value.replace(/[\D]/ig, '');
				if (parseInt(val1) >= parseInt(val2)) {
					alert(MSG_DATE_ERROR);
					node.en_begin_date.focus();
					return false;
				}
			}
		}

		// 課程結束日要大於開始上課日 (st_begin)
		if ((typeof(node.ck_st_begin) != 'undefined') && (typeof(node.ck_st_end) != 'undefined') && node.ck_st_begin.checked === true && node.ck_st_end.checked === true) {
			val1 = node.st_begin_date.value.replace(/[\D]/ig, '');
			val2 = node.st_end_date.value.replace(/[\D]/ig, '');
			if (parseInt(val1) >= parseInt(val2)) {
				alert(MSG_DATE_ERROR);
				node.st_begin_date.focus();
				return false;
			}
		}

		// 報名截止日要大於課程結束日 (st_end, en_end)
		if ((typeof(node.ck_en_end) != 'undefined') && (typeof(node.ck_st_end) != 'undefined') && node.ck_en_end.checked === true && node.ck_st_end.checked  === true) {
			val1 = node.en_end_date.value.replace(/[\D]/ig, '');
			val2 = node.st_end_date.value.replace(/[\D]/ig, '');
			if (parseInt(val1) >= parseInt(val2)) {
				alert(MSG_DATE_ERROR2);
				node.st_end_date.focus();
				return false;
			}
		}

		re = /^[0-9]+$/;
		var ary = ['credit', 'n_limit', 'a_limit', 'quota_limit', 'fair_grade'];
		for (var i = 0; i < ary.length; i++) {
			eval("var obj = node." + ary[i] + ";");
			if ((typeof(obj) != "object") || (obj == null)) continue;
			if (obj.type == "hidden") continue;
			val = obj.value;
			if ((val.match(re) == null) || ( (val.length > 0) && isNaN(parseInt(val)) )) {
				if (val != "") {
					alert("{$MSG['msg_only_digital'][$sysSession->lang]}");
					obj.value = "";
					obj.focus();
					return false;
				}
			}
		}
		node.cparent.value = selGpIDs.toString();
		
		// 課本&教材
		tmp_texts = node.texts.value;
		var c = tmp_texts.match(/[^ -~]/g);  
		var texts_length = tmp_texts.length + (c ? (c.length*2) : 0);
		if(texts_length > 254) {
			alert("{$MSG['th_alt_book'][$sysSession->lang]}");
			return false;
		}
		
		return true;
	}

	/**
	 * 顯示或隱藏日期輸入框
	 * @param string  objName : 物件 ID
	 * @param boolean state : 顯示 (true) 或隱藏 (false)
	 * @return void
	 **/
	function showDateInput(objName, state) {
		var obj = document.getElementById(objName);
		if (obj != null) {
			obj.style.display = state ? "" : "none";
		}
	}

	function Calendar_setup(ifd, fmt, btn, shtime) {
		Calendar.setup({
			inputField  : ifd,
			ifFormat    : fmt,
			showsTime   : shtime,
			time24      : true,
			button      : btn,
			singleClick : true,
			step        : 1
		});
	}

	function go_list() {
		var obj = document.getElementById("listFm");
		if (obj != null) obj.submit();
	}

	// 教材類別 (content)
	function select_content(){
		var win = new WinContentSelect('setContentValue');
		win.run();
	}

	function setContentValue(arr){
		if (typeof(arr[1]) != 'undefined'){
			document.actForm.QueryTxt.value = arr[1];
		}
		if (typeof(arr[0]) != 'undefined'){
			document.actForm.content_id.value = arr[0];
		}
	}

    /* APP - 課程圖片設定 - Begin */
	/**
	 * 課程圖片設定
     * 取得瀏覽的檔名。※※※※ 函數名稱不可改 ※※※※
     */
    function setPictureFilename (filename, classify) {
        appCoursePictureAction("{$appCsid}" , 'setup', filename, classify);
    };

    /**
     * 開啟圖片檔案列表
     **/
    function appCoursePictureBrowseFile () {
        var browserWidth = parseInt(screen.width * 0.6);
        var browserHeight = parseInt(screen.height * 0.6);
        var browserStyle = 'width=' + browserWidth + ',height=' + browserHeight + ',status=0,toolbar=0,menubar=0,scrollbars=1,resizable=1';

        appCoursePictureBrowser = window.open('/lib/app_listfiles.php?from=course', 'appCoursePictureBrowser', browserStyle);
        if (appCoursePictureBrowser.closed === false) {
            // 已經開啟，則focus就好
            appCoursePictureBrowser.focus();
        }
    }

    /**
     * 處理課程圖片的AJAX動作
     * @param {String} courseId: 編碼後的課程ID
     * @param {String} action: setup / remove
     * @param {String} filename: 圖片檔案名稱
     * @param {String} classify: 分類：公用/私人
     **/
    function appCoursePictureAction(courseId, action, filename, classify) {
        var xmlDoc = null, txt;

        if (courseId != '' && action != '') {
            // 課程編號與動作代號不為空，才可以處理圖片設定
            if ((typeof(xmlHttp) === "undefined") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
            if ((typeof(xmlVar) === "undefined") || (xmlDoc === null)) xmlVar = XmlDocument.create();

            txt = "<manifest>";
            txt += "<cid>" + courseId + "</cid>";
            txt += "<file>" + filename + "</file>";
            txt += "<action>" + action + "</action>";
            txt += "<classify>" + classify + "</classify>";
            txt += "</manifest>";

            xmlHttp = XmlHttp.create();
            xmlVar.loadXML(txt);
            xmlHttp.open("POST", "../../lib/app_course_picture_ajax.php", false);
            xmlHttp.send(xmlVar);

            if (typeof appCoursePictureBrowser !== 'undefined' && appCoursePictureBrowser.closed === false) {
                // 如果瀏覽檔案的對話視窗沒有關閉，則關閉之
                appCoursePictureBrowser.close();
            }

            if (xmlHttp.responseText === 'fail') {
                alert(MSG_COURSE_PICTURE_FAIL);
            } else {
                var timestamp = new Date().getTime();
                top.frames[appFrameId].$('#coursePicture').attr('src', '/lib/app_show_course_picture.php?courseId=' + courseId + '&timestamp=' + timestamp);
                if (action === 'remove') {
                    // 成功移除圖片後，disable移除的按鈕
                    top.frames[appFrameId].$('#appCourseImageRemove').attr('disabled', 'disabled');
                } else {
                    // 成功設定圖片後，將移除按鈕的disable拿掉
                    top.frames[appFrameId].$('#appCourseImageRemove').removeAttr('disabled');
                }
                if (appNewCourse === false) {
                    alert(MSG_COURSE_PICTURE_SUCCESS);
                }
            }
        }

        xmlDoc = null;
        txt = null;
    }
    /* APP - 課程圖片設定 - End */

	window.onload = function () {
		var obj = document.getElementById("tbGpCs");
		if (obj != null) {
			obj.style.left = 230;
			obj.style.top  = 415;
		}

		obj = document.getElementById("en_begin_date");
		if (obj) Calendar_setup("en_begin_date", "%Y-%m-%d", "en_begin_date", false);
		obj = document.getElementById("en_end_date");
		if (obj) Calendar_setup("en_end_date"  , "%Y-%m-%d", "en_end_date"  , false);
		obj = document.getElementById("st_begin_date");
		if (obj) Calendar_setup("st_begin_date", "%Y-%m-%d", "st_begin_date", false);
		obj = document.getElementById("st_end_date");
		if (obj) Calendar_setup("st_end_date"  , "%Y-%m-%d", "st_end_date"  , false);
	};

	window.onunload = function () {
	    // 離開設定頁面時，關閉未關閉的瀏覽檔案視窗
	    if (typeof appCoursePictureBrowser === 'undefined') {
	        return false;
	    }
	    if (appCoursePictureBrowser.closed === false) {
		    appCoursePictureBrowser.close();
		}
	};
BOF;

	// 目前所有的課程狀態，延續 WM2 的屬性
	$CourseStatusList = array(
			5 => $MSG['param_prepare'][$sysSession->lang],
			1 => $MSG['param_open_a'][$sysSession->lang],
			2 => $MSG['param_open_a_date'][$sysSession->lang],
			3 => $MSG['param_open_n'][$sysSession->lang],
			4 => $MSG['param_open_n_date'][$sysSession->lang],
			/* 6 => iconv('Big5', 'UTF-8', '限管理員') */
		);

	/* array(欄位型態, 長度限制, 標題, ID, 預設值, 說明) */
	$dd = array();
	if (defined('ENV_TEACHER')) {
		$dd[] = array('title'   , 128, 25, $MSG['th_course_name'][$sysSession->lang]  , 'caption'    , $lang               , $MSG['th_alt_course_name'][$sysSession->lang]  );
		$dd[] = array('caption' , 128, 60, $MSG['th_teacher'][$sysSession->lang]      , 'teacher'    , $teacher            , $MSG['th_alt_teacher'][$sysSession->lang]      );
		if ($actType == 'Edit') {
				$dd[] = array('caption' ,   0,  0, $MSG['teacher'][$sysSession->lang]         , 'rteacher'   , $rteacher           , ''                                             );
				$dd[] = array('caption' ,   0,  0, $MSG['instructor'][$sysSession->lang]      , 'rinstructor', $rinstructor        , ''                                             );
				$dd[] = array('caption' ,   0,  0, $MSG['assistant'][$sysSession->lang]       , 'rassistant' , $rassistant         , ''                                             );
		}
		if(in_array('content_id',$ta_can_sets)) {
			$dd[] = array('text'   , 0, 20, $MSG['th_content'][$sysSession->lang]      , 'content_id' , $contents           , '&nbsp;'      );
		}else{
			list($content_caption) = dbGetStSr('WM_content','caption','content_id=' . $cont_id, ADODB_FETCH_NUM);
			$content_ary = unserialize($content_caption);
			$dd[] = array('caption', 0, 20, $MSG['th_content'][$sysSession->lang]      , 'content_id' , $content_ary[$sysSession->lang]           , '&nbsp;'      );
		}
		$dd[] = array((in_array('en_begin',$ta_can_sets) ? 'date'     : 'caption'),  20, 20, $MSG['th_enroll_begin'][$sysSession->lang] , 'en_begin', $en_begin               , $MSG['th_alt_enroll_begin'][$sysSession->lang]);
		$dd[] = array((in_array('en_end',$ta_can_sets)   ? 'date'     : 'caption'),  20, 20, $MSG['th_enroll_end'][$sysSession->lang]   , 'en_end'  , $en_end                 , $MSG['th_alt_enroll_begin'][$sysSession->lang]);
		$dd[] = array((in_array('st_begin',$ta_can_sets) ? 'date'     : 'caption'),  20, 20, $MSG['th_study_begin'][$sysSession->lang]  , 'st_begin', $st_begin               , $MSG['th_alt_enroll_begin'][$sysSession->lang]."<br />".$MSG['sync_to_calendar_msg'][$sysSession->lang]);
		$dd[] = array((in_array('st_end',$ta_can_sets)   ? 'date'     : 'caption'),  20, 20, $MSG['th_study_end'][$sysSession->lang]    , 'st_end'  , $st_end                 , $MSG['th_alt_enroll_begin'][$sysSession->lang]."<br />".$MSG['sync_to_calendar_msg'][$sysSession->lang]);
		$dd[] = array((in_array('status',$ta_can_sets)   ? 'select'   : 'caption'),   0,  0, $MSG['th_course_status'][$sysSession->lang], 'status'  , $CourseStatusList       , '');
		$dd[] = array((in_array('review',$ta_can_sets)   ? 'select'   : 'caption'),   0,  0, $MSG['th_review_name'][$sysSession->lang]  , 'review'  , getReviewRuleList($csid), '');
		$dd[] = array((in_array('cparent',$ta_can_sets)  ? 'caption'  : 'caption'),   0,  0, $MSG['th_group'][$sysSession->lang]        , 'cparent' , $gps                    , in_array('cparent',$ta_can_sets) ? ('<input type="button" class="cssBtn" value="' . $MSG['btn_select_group'][$sysSession->lang] . '" onclick="showGroup();">') : '');
		$dd[] = array((in_array('texts',$ta_can_sets)    ? 'textarea' : 'caption'),   0,  0, $MSG['th_book'][$sysSession->lang]         , 'texts'   , $book                   , $MSG['th_alt_book'][$sysSession->lang]);
		$dd[] = array((in_array('url',$ta_can_sets)      ? 'text'     : 'caption'), 255, 40, $MSG['th_url'][$sysSession->lang]          , 'url'     , $url                    , '');
		$dd[] = array((in_array('content',$ta_can_sets)  ? 'textarea' : 'caption'),   0,  0, $MSG['th_introduce'][$sysSession->lang]    , 'content' , $intro                  , $MSG['th_alt_introduce'][$sysSession->lang]);
        if (sysEnableAppCoursePicture) {
            // APP課程圖片模組有啟用
            //APP - 課程圖片設定 - Begin
            $dd[] = array('coursePic',                                                    0,  0, $MSG['th_picture'][$sysSession->lang]      , 'picture' , $MSG['btn_image_browse'][$sysSession->lang] , $MSG['item_image_remark'][$sysSession->lang].'<br>'.'<font color="red">'.$MSG['msg_chars_limit'][$sysSession->lang].'</font><br>'.$MSG['msg_filetype_limit'][$sysSession->lang]);
            //APP - 課程圖片設定 - End
        }
		$dd[] = array('caption' ,                                                     3,  3, $MSG['th_credit'][$sysSession->lang]       , 'credit'     , $credit             , ''       );
		$dd[] = array((in_array('n_limit',$ta_can_sets)  ? 'text'     : 'caption'),   6,  6, $MSG['th_student'][$sysSession->lang]      , 'n_limit' , $n_limit                , $MSG['th_alt_student'][$sysSession->lang]);
		$dd[] = array((in_array('a_limit',$ta_can_sets)  ? 'text'     : 'caption'),   6,  6, $MSG['th_auditor'][$sysSession->lang]      , 'a_limit' , $a_limit                , $MSG['th_alt_student'][$sysSession->lang]);
		$dd[] = array('caption' ,                                                     0,  0, $MSG['th_usage'][$sysSession->lang]        , 'quota_used' , $usage              , ''        );
		$dd[] = array('caption' ,                                                    10, 10, $MSG['th_quota'][$sysSession->lang]        , 'quota_limit', $quota              , $MSG['th_alt_quota'][$sysSession->lang]        );
		$dd[] = array((in_array('fair_grade',$ta_can_sets)  ? 'text'  : 'caption'),  10, 10, $MSG['fair_grade'][$sysSession->lang]      , 'fair_grade' , $fair_grade         , ''   );
	} else {
		$CourseStatusList[0] = $MSG['param_close'][$sysSession->lang];
		$dd[] = array('title'   , 128, 25, $MSG['th_course_name'][$sysSession->lang]  , 'caption'    , $lang               , $MSG['th_alt_course_name'][$sysSession->lang]  );
		$dd[] = array('text'    , 128, 60, $MSG['th_teacher'][$sysSession->lang]      , 'teacher'    , $teacher            , $MSG['th_alt_teacher'][$sysSession->lang]      );
		if ($actType == 'Edit') {
				$dd[] = array('caption' ,   0,  0, $MSG['teacher'][$sysSession->lang]         , 'rteacher'   , $rteacher           , ''                                             );
				$dd[] = array('caption' ,   0,  0, $MSG['instructor'][$sysSession->lang]      , 'rinstructor', $rinstructor        , ''                                             );
				$dd[] = array('caption' ,   0,  0, $MSG['assistant'][$sysSession->lang]       , 'rassistant' , $rassistant         , ''                                             );
		}
		$dd[] = array('text'    ,   0, 20, $MSG['th_content'][$sysSession->lang]      , 'content_id' , $contents           , '&nbsp;'      );
		$dd[] = array('date'    ,  20, 20, $MSG['th_enroll_begin'][$sysSession->lang] , 'en_begin'   , $en_begin           , $MSG['th_alt_enroll_begin'][$sysSession->lang] );
		$dd[] = array('date'    ,  20, 20, $MSG['th_enroll_end'][$sysSession->lang]   , 'en_end'     , $en_end             , $MSG['th_alt_enroll_begin'][$sysSession->lang]   );
		$dd[] = array('date'    ,  20, 20, $MSG['th_study_begin'][$sysSession->lang]  , 'st_begin'   , $st_begin           , $MSG['th_alt_enroll_begin'][$sysSession->lang]."<br />".$MSG['sync_to_calendar_msg'][$sysSession->lang]  );
		$dd[] = array('date'    ,  20, 20, $MSG['th_study_end'][$sysSession->lang]    , 'st_end'     , $st_end             , $MSG['th_alt_enroll_begin'][$sysSession->lang]."<br />".$MSG['sync_to_calendar_msg'][$sysSession->lang]    );
		$dd[] = array('select'  ,   0,  0, $MSG['th_course_status'][$sysSession->lang], 'status'     , $CourseStatusList   , '');
		$dd[] = array('select'  ,   0,  0, $MSG['th_review_name'][$sysSession->lang]  , 'review'     , getReviewRuleList($csid)   , $MSG['title39'][$sysSession->lang]);
		$dd[] = array('caption' ,   0,  0, $MSG['th_group'][$sysSession->lang]        , 'cparent'    , $gps                , '<input type="button" class="cssBtn" value="' . $MSG['btn_select_group'][$sysSession->lang] . '" onclick="showGroup();">');
		$dd[] = array('textarea',   0,  0, $MSG['th_book'][$sysSession->lang]         , 'texts'      , $book               , $MSG['th_alt_book'][$sysSession->lang]         );
		$dd[] = array('text'    , 255, 40, $MSG['th_url'][$sysSession->lang]          , 'url'        , $url                , ''          );
		$dd[] = array('textarea',   3,  3, $MSG['th_introduce'][$sysSession->lang]    , 'content'    , $intro              , $MSG['th_alt_introduce'][$sysSession->lang]    );
        if (sysEnableAppCoursePicture) {
            // APP課程圖片模組有啟用
            //APP - 課程圖片設定 - Begin
            $dd[] = array('coursePic',  0,  0, $MSG['th_picture'][$sysSession->lang]      , 'picture'    , $MSG['btn_image_browse'][$sysSession->lang] , $MSG['item_image_remark'][$sysSession->lang].'<br>'.'<font color="red">'.$MSG['msg_chars_limit'][$sysSession->lang].'</font><br>'.$MSG['msg_filetype_limit'][$sysSession->lang]);
            //APP - 課程圖片設定 - End
        }
		$dd[] = array('text'    ,   3,  3, $MSG['th_credit'][$sysSession->lang]       , 'credit'     , $credit             , ''       );
		$dd[] = array('text'    ,   6,  6, $MSG['th_student'][$sysSession->lang]      , 'n_limit'    , $n_limit            , $MSG['th_alt_student'][$sysSession->lang]      );
		$dd[] = array('text'    ,   6,  6, $MSG['th_auditor'][$sysSession->lang]      , 'a_limit'    , $a_limit            , $MSG['th_alt_student'][$sysSession->lang]      );
		$dd[] = array('caption' ,   0,  0, $MSG['th_usage'][$sysSession->lang]        , 'quota_used' , $usage              , ''        );
		$dd[] = array('text'    ,  10, 10, $MSG['th_quota'][$sysSession->lang]        , 'quota_limit', $quota              , $MSG['th_alt_quota'][$sysSession->lang]        );
		$dd[] = array('text'    ,  10, 10, $MSG['fair_grade'][$sysSession->lang]      , 'fair_grade' , $fair_grade         , ''   );
	}

    if (sysEnableAppCoursePicture) {
        // APP課程圖片模組有啟用
        // 課程圖片 -- Begin
        $removeButtonDisable = 'disabled';
        if (isset($csid)) {
            list($isExistPicture) = dbGetStSr('CO_course_picture', 'count(*)', "course_id = '{$csid}'", ADODB_FETCH_NUM);

            if ($isExistPicture > 0) {
                $removeButtonDisable = '';
            }
        }
        // 課程圖片 -- End
    }

	// 開始呈現 HTML
	showXHTML_head_B($title);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_CSS('inline' , $csStyle);
	// 產生萬年曆的物件，並且設定所需的語系
	$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
	// 載入萬年曆所需的程式
	$calendar->load_files();
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/popup/popup.js');
    if (sysEnableAppCoursePicture) {
        // APP課程圖片模組有啟用
        showXHTML_script('include', '/lib/jquery/jquery.min.js', true, null, 'UTF-8');
    }
	showXHTML_script('include', '/academic/stud/lib.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');
		$ary = array();
		$ary[] = array($title, 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'actForm', '', 'action="course_save.php" method="post" style="display:inline" onsubmit="return checkData();" enctype="multipart/form-data"');
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('nowrap align="center" valign="top"',$MSG['title42'][$sysSession->lang]);
					if (! defined('ENV_TEACHER')) {
						showXHTML_td('nowrap align="center" valign="top"',$MSG['title43'][$sysSession->lang]);
					}
					showXHTML_td('nowrap width="340" align="center" valign="top"',$MSG['title44'][$sysSession->lang]);
					showXHTML_td('nowrap width="180" align="center" valign="top"',$MSG['title45'][$sysSession->lang]);
				showXHTML_tr_E('');
				for ($i = 0; $i < count($dd); $i++) {
					if ( ($dd[$i][4] == 'quota_used') && ($actType == 'Create') ) continue;
					$extra = '';
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						if ($dd[$i][0] == 'title') {
							$cols = '';
							$rows = '';
						} elseif ($dd[$i][0] == 'acl') {
							$cols = 'id="aclDisplayPanel_0" colspan="2"';
							$rows = '';
						}
						else {
							$cols = '';
							$rows = '';
						}
						showXHTML_td($rows . 'nowrap=nowrap align="right" valign="top" class="cssTrHead"', $dd[$i][3]);

						if (! defined('ENV_TEACHER')) {
							showXHTML_td_B($rows . ' align="center"');
								$ck_val = '';
								if(in_array($dd[$i][4],$default_ta_can_sets)) {
									if(in_array($dd[$i][4],$ta_can_sets)) {
										$ck_val = " checked";
									}else{
										$ck_val = '';
									}
									showXHTML_input('checkbox', 'ckta_' . $dd[$i][4], '', '', $ck_val);
								}
							showXHTML_td_E('');
						}
						showXHTML_td_B($cols);
							switch ($dd[$i][0]) {
								case 'date_cpt' :
									$s = intval(date('Y')) - 3;
									$m = intval(date('Y'));
									$e = intval(date('Y')) + 3;

									if ($dd[$i][5][0] == 0) {
										echo $MSG['unlimit'][$sysSession->lang];
									} else {
										echo $MSG['year_1'][$sysSession->lang] . '&nbsp;';
										echo ($dd[$i][5][0] > 0) ? $dd[$i][5][0] : $MSG['unlimit'][$sysSession->lang];
										echo $MSG['year_2'][$sysSession->lang];

										$val = $dd[$i][5][1];
										echo ($dd[$i][5][1] > 0) ? $dd[$i][5][1] : $MSG['unlimit'][$sysSession->lang];
										echo $MSG['month'][$sysSession->lang];

										$val = $dd[$i][5][2];
										echo ($dd[$i][5][2] > 0) ? $dd[$i][5][2] : $MSG['unlimit'][$sysSession->lang];
										echo $MSG['day'][$sysSession->lang];
									}

									showXHTML_input('hidden', $dd[$i][4] . '_year' , $dd[$i][5][0], '', '');
									showXHTML_input('hidden', $dd[$i][4] . '_month', $dd[$i][5][1], '', '');
									showXHTML_input('hidden', $dd[$i][4] . '_day'  , $dd[$i][5][2], '', '');
									break;

								case 'date' :
									if ($actType = 'Edit') {
										if (is_array($dd[$i][5]) && (count($dd[$i][5]) > 0)) {
											$tmp = implode('', $dd[$i][5]);
											$isCheck = (intval($tmp) <= 0) ? false : true;
											$val = implode('-', $dd[$i][5]);
												if ($val == '0000-00-00' || $val == '') $val = $today;
										} else {
											$isCheck = false;
											$val = $today;
										}
									} else {
										$isCheck = false;
										$val = $today;
									}
									if (defined('ENV_TEACHER')) {
										if(in_array($dd[$i][4],$ta_can_sets)) {
											$ck = $isCheck ? ' checked' : '';
											$ds = $isCheck ? '' : ' style="display: none;"';
											showXHTML_input('checkbox', 'ck_' . $dd[$i][4], $dd[$i][4], '', 'id="ck_' . $dd[$i][4] . '" onclick="showDateInput(\'span_' . $dd[$i][4] . '\', this.checked)"' . $ck);
											echo $MSG['btn_enable'][$sysSession->lang];
											// $dd[$i][5][0];
											echo '<span id="span_' . $dd[$i][4] .'"' . $ds . '>' . $MSG['msg_enable_date'][$sysSession->lang];
											showXHTML_input('text', $dd[$i][4] . '_date', $val, '', 'id="' . $dd[$i][4] . '_date" class="cssInput" readonly="readonly"');
											echo '</span>';
										}else{
											echo $val;
											showXHTML_input('hidden', $dd[$i][4] . '_date', $val, '', 'id="' . $dd[$i][4] . '_date" ');
										}
									}else{
										$ck = $isCheck ? ' checked' : '';
										$ds = $isCheck ? '' : ' style="display: none;"';
										showXHTML_input('checkbox', 'ck_' . $dd[$i][4], $dd[$i][4], '', 'id="ck_' . $dd[$i][4] . '" onclick="showDateInput(\'span_' . $dd[$i][4] . '\', this.checked)"' . $ck);
										echo $MSG['btn_enable'][$sysSession->lang];
										// $dd[$i][5][0];
										echo '<span id="span_' . $dd[$i][4] .'"' . $ds . '>' . $MSG['msg_enable_date'][$sysSession->lang];
										showXHTML_input('text', $dd[$i][4] . '_date', $val, '', 'id="' . $dd[$i][4] . '_date" class="cssInput" readonly="readonly"');
                                        if( $dd[$i][4]=="st_begin" || $dd[$i][4]=="st_end"){
                                            echo '<br />';
                                            showXHTML_input('checkbox', 'ck_sync_'.$dd[$i][4], 1, 1, ' class="cssInput"');
                                            echo $MSG['sync_to_calendar'][$sysSession->lang];
                                        }
										echo '</span>';
									}
									break;

								case 'caption' :
									echo '<div id="div_' . $dd[$i][4] . '">';
									if (($dd[$i][4] == 'quota_used') || ($dd[$i][4] == 'quota_limit')) {
										showXHTML_input('hidden', $dd[$i][4], $dd[$i][5], '', 'id="' . $dd[$i][4] . '"');
										echo showUsage($dd[$i][5]);
										if (($dd[$i][4] == 'quota_used') && $csid != ''){
										    if (($quota = intval($quota)) > 0)
												echo '&nbsp;( ' . round($usage / $quota, 4) * 100 . ' %)';
											else
											    echo '&nbsp;( 0 %)';
										}
										echo '</div>';
									} else {
										if($dd[$i][4] == 'review') {
											$review_val = getReviewSerial($csid);
											echo $dd[$i][5][$review_val];
											showXHTML_input('hidden', $dd[$i][4], $review_val, '', 'id="' . $dd[$i][4] . '"');
										}else{
											echo $dd[$i][4] == 'status' ? $dd[$i][5][$status] : $dd[$i][5];
											echo '</div>';
											$hidden_ary = array('content_id','en_begin','en_end','status','texts','url','content','n_limit','a_limit', 'st_begin', 'st_end');
											if(in_array($dd[$i][4],$hidden_ary)) {
												if($dd[$i][4] == 'status')
													showXHTML_input('hidden', $dd[$i][4], $status, '', 'id="' . $dd[$i][4] . '"');
												else
													showXHTML_input('hidden', $dd[$i][4], $dd[$i][5], '', 'id="' . $dd[$i][4] . '"');
											}else{
												showXHTML_input('hidden', $dd[$i][4], '' , '', 'id="' . $dd[$i][4] . '"');
											}
										}
									}
									break;

								case 'title' :
									$multi_lang = new Multi_lang(false, $dd[$i][5]); // 多語系輸入框
									$multi_lang->show(true, null, defined('ENV_TEACHER') && !in_array('caption', $ta_can_sets) ? 'readonly' : '');
									break;

								case 'select' :
								case 'text' :
									if ($dd[$i][4] == 'status')
									{
										$val = $status;
									}else if ($dd[$i][4] == 'review'){
										 $val = getReviewSerial($csid);
									}else $val = 0;

									if ($dd[$i][4] == 'content_id') {
										$val = $cont_id;
										list($content_caption) = dbGetStSr('WM_content','caption','content_id=' . $val, ADODB_FETCH_NUM);
										$content_lang = unserialize($content_caption);
										$content_val = $content_lang[$sysSession->lang];

										$ck = ((intval($cont_id) != 0)? ' checked' : '');
										$ds = ((intval($cont_id) != 0)? '' : ' style="display: none;"');

										showXHTML_input('checkbox', 'ck_' . $dd[$i][4], $dd[$i][4], '', 'id="ck_' . $dd[$i][4] . '" onclick="showDateInput(\'span_' . $dd[$i][4] . '\', this.checked)"' . $ck);
										echo  $MSG['btn_enable'][$sysSession->lang];
										echo '<span id="span_' . $dd[$i][4] .'"' . $ds . '>';
											showXHTML_input('text', 'QueryTxt', $content_val, '', 'class="cssInput" id="QueryTxt" readonly');
											showXHTML_input('button', '', $MSG['btn_find_content'][$sysSession->lang], '', 'id="btnCQuery" class="cssBtn" onclick="select_content()"');
										echo '</span>';
									}

									if ($dd[$i][1] > 0) {
										$extra = ' class="cssInput" maxlength="' . $dd[$i][1] . '" size="' . $dd[$i][2] . '"';
									} else {
										$extra = ' class="cssInput"';
									}

									if ($dd[$i][4] == 'content_id') {
										$extra = ' style="display:none" ';
										showXHTML_input($dd[$i][0], $dd[$i][4], $val, $content_val, $extra);

									}else{
										if (defined('ENV_TEACHER')) {
											if(in_array($dd[$i][4],$ta_can_sets)) {
												showXHTML_input($dd[$i][0], $dd[$i][4], $dd[$i][5], $val, $extra);
											}else{
												echo $val;
												showXHTML_input('hidden', $dd[$i][4], '', $val, '');
											}
										}else{
											showXHTML_input($dd[$i][0], $dd[$i][4], $dd[$i][5], $val, $extra);
										}
									}

									if (($dd[$i][4] == 'n_limit') || ($dd[$i][4] == 'a_limit')) {
										echo $MSG['people'][$sysSession->lang];
									}
									if (($dd[$i][4] == 'quota_used') || ($dd[$i][4] == 'quota_limit')) {
										echo '&nbsp;KB';
									}
									break;
                                //APP - 課程圖片設定 - Begin
								case 'coursePic' :
                                    echo '<span id="coursePictureArea"><img src="/lib/app_show_course_picture.php?courseId=' . $appCsid . '" id="coursePicture" name="coursePicture" borer="0" align="absmiddle" onload="picReSize()" loop="0" width="143" height="100"></span>';
                                    showXHTML_input('button', '', $MSG['btn_image_browse'][$sysSession->lang], '', 'class="cssInput" onclick="appCoursePictureBrowseFile();"');
                                    showXHTML_input('button', '', $MSG['btn_image_delete'][$sysSession->lang], '', 'id="appCourseImageRemove" class="cssInput" '.$removeButtonDisable.' onclick="appCoursePictureAction(\'' . $appCsid . '\', \'remove\', \'\');"');
									break;
                                //APP - 課程圖片設定 - End
								case 'textarea' :
									showXHTML_input('textarea', $dd[$i][4], $dd[$i][5], '', 'cols="46" rows="10" class="cssInput"');
									break;
								case 'acl':
									echo $dd[$i][4];
									break;
							}
						showXHTML_td_E('');

						showXHTML_td_B('valign="top"');
							echo $dd[$i][6];
						showXHTML_td_E('&nbsp;');
					showXHTML_tr_E('');
				}

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="5" align="center"');
						showXHTML_input('submit', '', $MSG['btn_save'][$sysSession->lang] , '', 'class="cssBtn"');
						showXHTML_input('reset' , '', $MSG['btn_reset'][$sysSession->lang], '', 'class="cssBtn"');
						if (!defined('ENV_TEACHER')) {
							showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="go_list();"');
						}
						showXHTML_input('hidden', 'ticket', $ticket, '', '');
						showXHTML_input('hidden', 'gid' , $_POST['gid'] , '', '');
						showXHTML_input('hidden', 'page', $_POST['page'], '', '');
						showXHTML_input('hidden', 'sortby', $_POST['sortby'], '', '');
						showXHTML_input('hidden', 'csid', trim($_POST['csid']), '', '');
						showXHTML_input('hidden', 'keyword', $_POST['query_btn']== '0' ? "": $_POST['keyword']);
					showXHTML_td_E('');
				showXHTML_tr_E('');
			showXHTML_table_E('');
		showXHTML_tabFrame_E();
		echo '</div>';

		$csParent = getCourseParents($csid, true);
		$tmp  = array();
		foreach ($csParent as $key => $val) {
			$tmp[] = $val[$sysSession->lang];
		}
		$gps = implode(', ', $tmp);
		$selgpids = '"' . implode('","', array_keys($csParent)) . '"';

// ============================================================================
		$modJSGpList = <<< BOF
	function modCGTreeCallBack(ary) {
		if (!ary instanceof Array) {
			alert("param is not Array");
			return false;
		}
		selGpIDs = ary[0];
		var obj = document.getElementById("div_cparent");
		var tmp = [];
		var txt = "";
		if (obj) {
			for (var i = 0; i < ary[1].length; i++) {
				tmp[tmp.length] = ary[1][i][1];
			}
			obj.innerHTML = tmp.join(", ");
		}
	}
BOF;

		$modTree = new modCGTree();
		$modTree->add_js_callback($modJSGpList);
		$modTree->show();
// ============================================================================
		showXHTML_form_B('action="course_list.php" method="post" enctype="multipart/form-data" style="display:none"', 'listFm');
			showXHTML_input('hidden', 'ticket', $_POST['gid']   , '', '');
			showXHTML_input('hidden', 'page'  , $_POST['page']  , '', '');
			showXHTML_input('hidden', 'sortby', $_POST['sortby'], '', '');
			showXHTML_input('hidden', 'keyword', $_POST['query_btn']== '0' ? "": $_POST['keyword']);
		showXHTML_form_E('');

	showXHTML_body_E('');
?>
