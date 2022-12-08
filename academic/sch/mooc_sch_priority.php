<?php
	/**
	 * @todo
  	 *     JavaScript 檢查輸入的資料
  	 *     新增校門
	 *     語系分離
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/sch_manage.php');
    require_once(sysDocumentRoot . '/lib/editor.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lang/mooc.php');
	require_once(sysDocumentRoot . '/lib/lib_acade_news.php');
    
	$sysSession->cur_func='100300500';
	$sysSession->restore();
	if (!aclVerifyPermission(100300500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	
	/**
	 * getTheme()
	 *     取得系統所有的佈景
	 * @return array 佈景
	 **/
	function getTheme() {
		$theme = '';
		$dp = opendir(sysDocumentRoot . '/theme/');
		while ( ($entry = readdir($dp)) !== false ) {
			if ( !ereg("(^\.+)|(^CVS$)", $entry) ) {
				if (is_dir(sysDocumentRoot . '/theme/' . $entry)) $theme[$entry] = $entry;
			}
		}
		closedir($dp);
		return $theme;
	}
	
	/**
	 * 1. 檢查車票是否正確
  	 * 2. 檢查車票的種類，是新增還是修改
	 **/
	$actType         = '';
	$title           = '';
	$isSingle        = '';
	$location        = 'sch_list.php';
	$_POST['ticket'] = trim($_POST['ticket']);
	$_POST['sid']    = intval($_POST['sid']);
	$_POST['shost']  = trim($_POST['shost']);
        
	// 修改學校
	$ticket = md5($sysSession->ticket . 'moocEdit' . $sysSession->username . $_POST['sid'] . $_POST['shost']);
	if ($_POST['ticket'] == $ticket) {
		$actType = 'Edit';
		$title   = $MSG['tabs_modify_school'][$sysSession->lang];

		$RS = dbGetStSr('`WM_school` w  NATURAL LEFT JOIN `CO_school` c',
                                '*', "w.school_id='{$_POST['sid']}' and w.school_host='{$_POST['shost']}'", ADODB_FETCH_ASSOC);
		if (!$RS) {
			$errMsg = $sysConn->ErrorMsg();
			wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], $errMsg);
			die($errMsg);
		}
	}

	// 單一修改學校
	$ticket = md5('Single' . $sysSession->ticket . 'moocEdit' . $sysSession->username . $_POST['sid'] . $_POST['shost']);
	if ($_POST['ticket'] == $ticket) {
		$actType  = 'Edit';
		$isSingle = 'Single';
		$location = 'sch_single.php';
		$title    = $MSG['tabs_modify_school'][$sysSession->lang];

		$RS = dbGetStSr('`WM_school` w  NATURAL LEFT JOIN `CO_school` c', 
                                '*', "w.school_id='{$_POST['sid']}' and w.school_host='{$_POST['shost']}'", ADODB_FETCH_ASSOC);
		if (!$RS) {
			$errMsg = $sysConn->ErrorMsg();
		    wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 2, 'manager', $_SERVER['PHP_SELF'], $errMsg);
			die($errMsg);
		}
	}

        
	$ticket = md5('reMoocEdit' . $sysSession->ticket .  $sysSession->username . $_POST['sid'] . $_POST['shost']);
	if ($_POST['ticket'] == $ticket) {
		$actType = 'reEdit';
		$title   = $MSG['tabs_modify_school'][$sysSession->lang];
	}

	$ticket = md5('Single' . 'reMoocEdit' . $sysSession->ticket .  $sysSession->username . $_POST['sid'] . $_POST['shost']);
	if ($_POST['ticket'] == $ticket) {
		$actType  = 'reEdit';
		$isSingle = 'Single';
		$location = 'sch_single.php';
		$title = $MSG['tabs_modify_school'][$sysSession->lang];
	}
        
	/*
	if ($actType == '') {
	    die($MSG['access_deny'][$sysSession->lang]);
	}
	*/

	// 取得系統所有的佈景
	$theme = getTheme();
	// 設定車票
	setTicket();
    // 取得各個常見問題標題及網址
    // 取得常見問題版
    if (dbGetNewsBoard($result, 'faq')) {
        $faqbid = $result['board_id'];
    }
    $rsQandA = dbGetStMr('`'.sysDBprefix.intval($_POST['sid']).'`.`WM_bbs_collecting`', '`content`, `subject`', "board_id=".$faqbid, ADODB_FETCH_ASSOC);
    if ($rsQandA) {
        while(!$rsQandA->EOF) {
            $listQandA[strip_tags($rsQandA->fields['content'])] = $rsQandA->fields['subject'];
            $rsQandA->MoveNext();
        }
    }
    // 取得課程標題及網址
    $rsCourseAll = dbGetStMr('`'.sysDBprefix.intval($_POST['sid']).'`.`WM_term_course`', '`course_id`, `caption`', "`kind` = 'course' order by course_id desc limit 0,10", ADODB_FETCH_ASSOC);
    if ($rsCourseAll) {
        while(!$rsCourseAll->EOF) {
            $multi_caption = getCaption($rsCourseAll->fields['caption']);
            $listCourse[$rsCourseAll->fields['course_id']] = $multi_caption[$sysSession->lang];
            $rsCourseAll->MoveNext();
        }
    }
    // 取得公開討論區、會員討論區
    
    // 取得 portal 的值
    $rsPortal = dbGetStMr('`'.sysDBprefix.intval($_POST['sid']).'`.`WM_portal`', '*', "1", ADODB_FETCH_ASSOC);
    if ($rsPortal) {
        while(!$rsPortal->EOF) {
            $listPl[$rsPortal->fields['portal_id']][$rsPortal->fields['key']] = $rsPortal->fields['value'];
            $rsPortal->MoveNext();
        }
    }
    
    // 取得當前設定之學校常數
    $curDa = getConstatnt(intval($_POST['sid']));
    // 管理員編輯權限，內容商的一般及超級管理員不可編輯 head 及 foot
    $editAcl = true;
    if ($curDa['is_portal'] == '0' && $curDa['is_independent'] == '0') {
        $editAcl = (aclCheckRole($sysSession->username, $sysRoles['root'], intval($_POST['sid'])) == '1')?true:false;
    }
    
    // 如果是入口網校true，才顯示 所有學院設定 (品牌大街)
    $isPsch = false;
    if ($curDa['is_portal'] == '1'){
        $isPsch = true;
    } else {
        if ($curDa['is_independent'] == '1') {
            $isIndsch = true;
        }
    }
    
    // 取討論版資料
    dbGetNewsBoard($newsresult);   // 取得最新消息版號
    $removeBid[] = $newsresult['board_id'];
    $removeBid[] = $faqbid;
     //取得新聞板號
    $forumStateData = $sysConn->GetArray('SELECT b.board_id, b.`bname`, s.`state` FROM `'.sysDBprefix.intval($_POST['sid']).'`.`WM_term_subject` s  LEFT JOIN `'.sysDBprefix.intval($_POST['sid']).'`.`WM_bbs_boards` b on b.board_id = s.board_id WHERE `course_id` = ' . intval($_POST['sid']) . ';');
    if (count($forumStateData) > 0) {
        foreach($forumStateData as $k => $v) {
            if (in_array($v['board_id'], $removeBid)) {
                // 濾掉最新消息及常見問題版
                continue;
            }
            $title = getCaption($v['bname']);
            switch($v['state']) {
                case 'open':
                    $forumOpenList[] = $title[$sysSession->lang];
                    break;
                case 'taonly':
                    $forumTaonlyList[] = $title[$sysSession->lang];
                    break;
            }
        }
    }
    // 取得學校個數 
    $schCnt = dbGetOne('`'.sysDBprefix.'MASTER`.`WM_school`', 'count(`school_id`)', '1');
    
    $reset = md5('reset' . $sysSession->ticket . $sysSession->username . $_POST['sid'] . $_POST['shost']);
    $sysMailRule = sysMailRule;
	$js = <<< BOF
	/**
	 * checkData()
	 *     check input data
	 *
	 * @return
	 **/
	function checkData() {
		var obj = document.getElementById(usingForm);
		var txt = "";
		var re  = /\s+/g;
                var pngRe = /\.(png)$/i;
		var em  = {$sysMailRule};
        var error = false;

		if (obj != null) {
            if (usingForm == 'headerForm') {
                txt = obj.schname.value.replace(re, '');
                if (!txt.length) {
                    alert("{$MSG['msg_need_sch_name'][$sysSession->lang]}");
                    obj.schname.value = txt;
                    obj.schname.focus();
                    error = true;
                } else if (txt.length > 64) {
                    alert("{$MSG['over_64_char'][$sysSession->lang]}");
                    obj.schname.value = txt;
                    obj.schname.focus();
                    error = true;
                }

                txt = obj.serhost.value.replace(re, '');
                if (!txt.length) {
                    alert("{$MSG['msg_need_domain'][$sysSession->lang]}");
                    obj.serhost.value = txt;
                    obj.serhost.focus();
                    error = true;
                } else if (txt.length > 64) {
                    alert("{$MSG['over_64_char'][$sysSession->lang]}");
                    obj.serhost.value = txt;
                    obj.serhost.focus();
                    error = true;
                }

                txt = obj.school_mail.value;
                if (txt.length > 0) {
                    if (txt.search(em) == -1) {
                        alert("{$MSG['error_school_mail'][$sysSession->lang]}");
                        obj.school_mail.focus();
                        error = true;
                    } else if (txt.length > 64) {
                        alert("{$MSG['over_64_char'][$sysSession->lang]}");
                        obj.school_mail.focus();
                        error = true;
                    }
                } else {
                    alert("{$MSG['fill_default_mail'][$sysSession->lang]}");
                    obj.school_mail.focus();
                    error = true;
                }

                txt = obj.courseQuota.value.replace(re, '');
                if (txt.length > 0) {
                    em = /^[0-9]+$/ig;
                    if (txt.match(em) == null) {
                        alert("{$MSG['msg_only_number'][$sysSession->lang]}");
                        obj.courseQuota.select();
                        obj.courseQuota.focus();
                        error = true;
                    }
                }

                txt = obj.doorQuota.value.replace(re, '');
                if (txt.length > 0) {
                    em = /^[0-9]+$/ig;
                    if (txt.match(em) == null) {
                        alert("{$MSG['msg_only_number'][$sysSession->lang]}");
                        obj.doorQuota.select();
                        obj.doorQuota.focus();
                        error = true;
                    }
                }
                /*
                txt = obj.icon.value;
                if (txt.length > 0) {
                    var icoRe = /\.(ico)$/i;
                    if (!icoRe.test(txt)) {
                        alert("{$MSG['file_limit_ico'][$sysSession->lang]}");
                        obj.icon.focus();
                        error = true;
                    }
                }
                */
                txt = obj.logo.value;
                if (txt.length > 0) {
                    if (!pngRe.test(txt)) {
                        alert("{$MSG['file_limit_png'][$sysSession->lang]}");
                        obj.logo.focus();
                        error = true;
                    }
                }

                txt = obj.FB_id.value.replace(re, '');
                if (txt.length > 0) {
                    em = /^[0-9]+$/ig;
                    if (txt.match(em) == null) {
                        alert("{$MSG['msg_only_number'][$sysSession->lang]}");
                        obj.FB_id.select();
                        obj.FB_id.focus();
                        error = true;
                    }      
                }

                txt = obj.FB_secret.value.replace(re, '');
                if (txt.length > 0) {
                    em = /^[a-zA-Z0-9]+$/ig;
                    if (txt.match(em) == null) {
                        alert("{$MSG['msg_only_number_eng'][$sysSession->lang]}");
                        obj.FB_secret.select();
                        obj.FB_secret.focus();
                        error = true;
                    }   
                }
            } else if (usingForm == 'bannerForm') {
                                        
                txt = obj.main_title.value.replace(re, '');
                if (!txt.length) {
                    alert("{$MSG['msg_need_main_title'][$sysSession->lang]}");
                    obj.main_title.value = txt;
                    obj.main_title.focus();
                    error = true;
                } else if (txt.length > 128) {
                    alert("{$MSG['over_128_char'][$sysSession->lang]}");
                    obj.main_title.value = txt;
                    obj.main_title.focus();
                    error = true;
                }

                txt = obj.sub_title.value.replace(re, '');
                if (!txt.length) {
                    alert("{$MSG['msg_need_sub_title'][$sysSession->lang]}");
                    obj.sub_title.value = txt;
                    obj.sub_title.focus();
                    error = true;
                } else if (txt.length > 128) {
                    alert("{$MSG['over_128_char'][$sysSession->lang]}");
                    obj.sub_title.value = txt;
                    obj.sub_title.focus();
                    error = true;
                }
                txt = obj.bottom_title.value.replace(re, '');
                if (!txt.length) {
                    alert("{$MSG['msg_need_bottom_title'][$sysSession->lang]}");
                    obj.bottom_title.value = txt;
                    obj.bottom_title.focus();
                    error = true;
                } else if (txt.length > 255) {
                    alert("{$MSG['over_255_char'][$sysSession->lang]}");
                    obj.bottom_title.value = txt;
                    obj.bottom_title.focus();
                    error = true;
                }
                /*
                obj.bottom_title.value = getEditorInstance('bottom_title').GetHTML();
                txt = obj.bottom_title.value.replace(re, '');
                if (!txt.length) {
                    alert("{$MSG['msg_need_bottom_title'][$sysSession->lang]}");
                    obj.bottom_title.value = txt;
                    obj.bottom_title.focus();
                    error = true;
                } else if (txt.length > 255) {
                    alert("{$MSG['over_255_char'][$sysSession->lang]}");
                    obj.bottom_title.value = txt;
                    obj.bottom_title.focus();
                    error = true;
                }
                */


                txt = obj.rep_img.value;
                if (txt.length > 0) {
                    if (!pngRe.test(txt)) {
                        alert("{$MSG['file_limit_png'][$sysSession->lang]}");
                        obj.rep_img.focus();
                        error = true;
                    }
                }
                txt = obj.brand_img.value;
                if (txt.length > 0) {
                    if (!pngRe.test(txt)) {
                        alert("{$MSG['file_limit_png'][$sysSession->lang]}");
                        obj.brand_img.focus();
                        error = true;
                    }
                }
                txt = obj.poster_1_pic.value;
                if (txt.length > 0) {
                    if (!pngRe.test(txt)) {
                        alert("{$MSG['file_limit_png'][$sysSession->lang]}");
                        obj.poster_1_pic.focus();
                        error = true;
                    }
                }
                txt = obj.poster_2_pic.value;
                if (txt.length > 0) {
                    if (!pngRe.test(txt)) {
                        alert("{$MSG['file_limit_png'][$sysSession->lang]}");
                        obj.poster_2_pic.focus();
                        error = true;
                    }
                }
                txt = obj.poster_3_pic.value;
                if (txt.length > 0) {
                    if (!pngRe.test(txt)) {
                        alert("{$MSG['file_limit_png'][$sysSession->lang]}");
                        obj.poster_3_pic.focus();
                        error = true;
                    }
                }
                /*
                txt = obj.bg_img_1.value;
                if (txt.length > 0) {
                    if (!pngRe.test(txt)) {
                        alert("{$MSG['file_limit_png'][$sysSession->lang]}");
                        obj.bg_img_1.focus();
                        error = true;
                    }
                }
                txt = obj.feature_img.value;
                if (txt.length > 0) {
                    if (!pngRe.test(txt)) {
                        alert("{$MSG['file_limit_png'][$sysSession->lang]}");
                        obj.bg_img.focus();
                        error = true;
                    }
                }
                */

            } else if (usingForm == 'contentForm') {
                if (obj.all_brand_priority != null) {
                    txt = obj.all_brand_priority.value.replace(re, '');
                    if (txt.length > 0) {
                        em = /^[0-9]+$/ig;
                        if (txt.match(em) == null) {
                            alert("{$MSG['msg_only_number'][$sysSession->lang]}");
                            obj.all_brand_priority.select();
                            obj.all_brand_priority.focus();
                            error = true;
                        }
                    }
                            
                    txt = obj.all_brand_name.value.replace(re, '');
                    if (txt.length > 64) {
                        alert("{$MSG['over_64_char'][$sysSession->lang]}");
                        obj.all_brand_name.value = txt;
                        obj.all_brand_name.focus();
                        error = true;
                    }
                }
                txt = obj.course_list_priority.value.replace(re, '');
                if (txt.length > 0) {
                    em = /^[0-9]+$/ig;
                    if (txt.match(em) == null) {
                        alert("{$MSG['msg_only_number'][$sysSession->lang]}");
                        obj.course_list_priority.select();
                        obj.course_list_priority.focus();
                        error = true;
                    }
                }
                txt = obj.pubilc_forum_priority.value.replace(re, '');
                if (txt.length > 0) {
                    em = /^[0-9]+$/ig;
                    if (txt.match(em) == null) {
                        alert("{$MSG['msg_only_number'][$sysSession->lang]}");
                        obj.pubilc_forum_priority.select();
                        obj.pubilc_forum_priority.focus();
                        error = true;
                    }
                }
                txt = obj.custom_1_priority.value.replace(re, '');
                if (txt.length > 0) {
                    em = /^[0-9]+$/ig;
                    if (txt.match(em) == null) {
                        alert("{$MSG['msg_only_number'][$sysSession->lang]}");
                        obj.custom_1_priority.select();
                        obj.custom_1_priority.focus();
                        error = true;
                    }
                }
                txt = obj.custom_2_priority.value.replace(re, '');
                if (txt.length > 0) {
                    em = /^[0-9]+$/ig;
                    if (txt.match(em) == null) {
                        alert("{$MSG['msg_only_number'][$sysSession->lang]}");
                        obj.custom_2_priority.select();
                        obj.custom_2_priority.focus();
                        error = true;
                    }
                }
                
                txt = obj.course_list_name.value.replace(re, '');
                if (txt.length > 64) {
                    alert("{$MSG['over_64_char'][$sysSession->lang]}");
                    obj.course_list_name.value = txt;
                    obj.course_list_name.focus();
                    error = true;
                }
                txt = obj.pubilc_forum_name.value.replace(re, '');
                if (txt.length > 64) {
                    alert("{$MSG['over_64_char'][$sysSession->lang]}");
                    obj.pubilc_forum_name.value = txt;
                    obj.pubilc_forum_name.focus();
                    error = true;
                }
                txt = obj.custom_1_name.value.replace(re, '');
                if (txt.length > 64) {
                    alert("{$MSG['over_64_char'][$sysSession->lang]}");
                    obj.custom_1_name.value = txt;
                    obj.custom_1_name.focus();
                    error = true;
                }
                txt = obj.custom_2_name.value.replace(re, '');
                if (txt.length > 64) {
                    alert("{$MSG['over_64_char'][$sysSession->lang]}");
                    obj.custom_2_name.value = txt;
                    obj.custom_2_name.focus();
                    error = true;
                }
                txt = obj.custom_1_pic.value;
                if (txt.length > 0) {
                    if (!pngRe.test(txt)) {
                        alert("{$MSG['file_limit_png'][$sysSession->lang]}");
                        obj.custom_1_pic.focus();
                        error = true;
                    }
                }
                txt = obj.custom_2_pic.value;
                if (txt.length > 0) {
                    if (!pngRe.test(txt)) {
                        alert("{$MSG['file_limit_png'][$sysSession->lang]}");
                        obj.custom_2_pic.focus();
                        error = true;
                    }
                }
                txt = obj.custom_1_pic.value.replace(re, '');
                if (txt.length > 128) {
                    alert("{$MSG['over_128_char'][$sysSession->lang]}");
                    obj.custom_1_pic.value = txt;
                    obj.custom_1_pic.focus();
                    error = true;
                }
                    txt = obj.custom_2_pic.value.replace(re, '');
                if (txt.length > 128) {
                    alert("{$MSG['over_128_char'][$sysSession->lang]}");
                    obj.custom_2_pic.value = txt;
                    obj.custom_2_pic.focus();
                    error = true;
                }
            } else if (usingForm == 'quickForm') {
                txt = obj.online_chat_priority.value.replace(re, '');
                if (txt.length > 0) {
                    em = /^[0-9]+$/ig;
                    if (txt.match(em) == null) {
                        alert("{$MSG['msg_only_number'][$sysSession->lang]}");
                        obj.online_chat_priority.select();
                        obj.online_chat_priority.focus();
                        error = true;
                    }
                }
                txt = obj.online_chat_name.value.replace(re, '');
                if (txt.length > 64) {
                    alert("{$MSG['over_64_char'][$sysSession->lang]}");
                    obj.online_chat_name.value = txt;
                    obj.online_chat_name.focus();
                    error = true;
                }
            } else if (usingForm == 'footerForm') {
                                
                txt = obj.about_us.value.replace(re, '');
                if (txt.length > 128) {
                    alert("{$MSG['over_128_char'][$sysSession->lang]}");
                    obj.about_us.value = txt;
                    obj.about_us.focus();
                    error = true;
                }

                txt = obj.contact_us.value.replace(re, '');
                if (txt.length > 128) {
                    alert("{$MSG['over_128_char'][$sysSession->lang]}");
                    obj.contact_us.value = txt;
                    obj.contact_us.focus();
                    error = true;
                }

                txt = obj.faq.value.replace(re, '');
                if (txt.length > 128) {
                    alert("{$MSG['over_128_char'][$sysSession->lang]}");
                    obj.faq.value = txt;
                    obj.faq.focus();
                    error = true;
                }

                txt = obj.other_info.value.replace(re, '');
                if (txt.length > 255) {
                    alert("{$MSG['over_255_char'][$sysSession->lang]}");
                    obj.other_info.value = txt;
                    obj.other_info.focus();
                    error = true;
                }
            }
		} else {
            error = true;
        }
        if (error === false) {        
            return true;
        } else {
            return false;
        }
	}
    var osaveblock = '';
    $(function() {
        var options = {
            dataType: 'json',
            beforeSubmit: showRequest,
            success: showResponse
        };
        $('.edit-form').ajaxForm(options);
        previewStyle();

        /* 移除海報 */
        $(".pic-dropbtn").on('click', function() {
            osaveblock = $('#' + usingForm)[0].saveblock.value;
            var pic = $(this).data('pic');
            var curTicket = $("input[name='ticket']").eq(0).attr('value');
            $('#' + usingForm)[0].saveblock.value = 'picdrop';
            $('#' + usingForm)[0].selectedpic.value = pic;
            $('#' + usingForm).find('input[type=submit]').click();
        });
        /* 搜尋 bar 設定值顯示 */
        $("#bannerForm input[name='search_bar']").on('change', function() {
            var obj = $(this);
            if (obj.is(":checked")) {
                $("#search-settings").css('display', 'inline-block');
            } else {
                $("#search-settings").css('display', 'none');
            }
        });
    });

    function showRequest(formData, jqForm, options) {
        if (window.console != null) {
            /* console.log(formData); */
        }
        return checkData();

    }

    function showResponse(responseText, statusText, xhr, form) {
        if (window.console != null) {
            /* console.log(responseText); */
        }
        if (responseText.restart == "1") {
             $('#'+showBlock).find("#restartBtn").show();
        }
        alert(responseText.msg);
        /* 刪除圖片會改變saveblock，submit 後改回原本 */            
        $('#' + usingForm)[0].saveblock.value = osaveblock;
        /* 隱藏已刪除的海報 */
        if (responseText.delpicid != null) {
            $("#"+ responseText.delpicid +"_picshow").hide();
        }
        /* 顯示已新增的海報 */
        if (responseText.addpicid != null) {
            $.each(responseText.addpicid, function(i,v) {
                $("#"+ v +"show").show();
            });
        }    
        /* 重拿ticket */
        $("input[name='ticket']").attr('value', function(i, val) {
            return responseText.ticket;
        });

        /* 重抓圖檔 */
        $('#'+showBlock + ' img').attr('src', function(i, val) {
            return val+Math.floor((Math.random() * 10));
        });
        
                    
        previewStyle();
    }

	/**
	 * guestNum()
	 *     show or hidden limit online guest number
	 *
	 * @param string val : allow or deny guest login
	 * @return
	 **/
	function guestNum(val) {
		var obj = document.getElementById("guestLimit");
		if (obj != null) {
			if (val == 'Y') {
				obj.style.visibility = "visible";
			} else {
				obj.style.visibility = "hidden";
			}
		}
	}

	function cleanHistory() {
		window.location.replace("{$location}");
	}
         
    function resetPriority(){
        document.getElementById("actForm").reset.value = "{$reset}";
        if (checkData()) {
            document.getElementById("actForm").submit();
        }
    }

    // FB checkbox 是否勾選，id, secret enable
    function checkFB(ob) {
        if (ob.value === 'FB') {
            if (ob.checked) {
                $('#fbInfo').show();
            } else {
                $('#fbInfo').hide();
            }
        }
    }
        
    /* 設定fancybox */ 
    $('.editBtn').fancybox({ 
        'titlePosition': 'inline',
        'transitionIn': 'none',
        'transitionOut': 'none',
        'modal': true,
        'closeBtn': false,
        helpers : {
            overlay : {
                locked : false
            }
        },
        afterLoad : function() {
            $('.cssTable').hide();
            $('#'+showBlock).show();
            $('.block-btn').show();
        },
        afterClose : function() {
            /* $('#'+usingForm)[0].actReset.click(); */
        }
    });
        
    /* 隱藏顯示資料表 */
    var showBlock = '';
    var usingForm = '';
    function showEdit(me) {
        showBlock = $(me).data('id');
        osaveblock = showBlock;
        usingForm = $(me).data('form');
        /* 把 savetype 的值改為 $(me).data('id') */
        $('#' + usingForm)[0].saveblock.value = showBlock;
   };
        
   function previewStyle() {
        /* reload 圖片 */
        $(".preview-well img").attr('src', function(i, val) {
            return val+Math.floor((Math.random() * 10));
        });
        $(".preview-br").css('background-image', function(i, val) {
            return val.replace(")", Math.floor((Math.random() * 10))+')');
        });
        /* 判斷 search 開關 */
        if ($('#bannerForm')[0].search_bar.checked == true) {
            $(".search-bar").show();
            /* $(".preview-br img").css("padding-left", "233px"); */
        } else {
            $(".search-bar").hide();
            $(".preview-br img").css("padding-left", "0");
        }
        /* 判斷主題色系 */
        $(".preview-well").removeClass('orange blue');
        switch ($("#headerForm input[name=style]:checked").val()) {
            case 'black':
                break;
            case 'orange':
                $(".preview-well").addClass('orange');
                break;
            case 'blue':
                $(".preview-well").addClass('blue');
                break;
        }
        /* 判斷內容順序 */
        
        /* 判斷 banner 敘述字 */
        var mtText = $("#bannerForm")[0].main_title.value;
        $("#bt1").text(mtText);
        var mtText = $("#bannerForm")[0].sub_title.value;
        $("#bt2").text(mtText);
        var mtText = $("#bannerForm")[0].bottom_title.value;
        $("#bt3").text(mtText);
        
        /* 判斷 footer 敘述字 */
        if (document.getElementById("footerForm") != null) {
            var crText = $("#footerForm")[0].other_info.value;
        }
        $(".preview-copyright").text(crText);
    }

                
BOF;

	$lang = array(
			'Big5'       =>$MSG['lang_big5'][$sysSession->lang],
			'en'         =>$MSG['lang_en'][$sysSession->lang],
			'GB2312'     =>$MSG['lang_gb'][$sysSession->lang],
			'EUC-JP'     =>$MSG['lang_jp'][$sysSession->lang],
			'user_define'=>$MSG['lang_user'][$sysSession->lang]
		);
	removeUnAvailableChars($lang);

	$allow = array(
		'Y' => $MSG['status_allow'][$sysSession->lang],
		'N' => $MSG['status_deny'][$sysSession->lang]
	);

	$reg_allow = array(
		'Y' => $MSG['status_allow'][$sysSession->lang],
		'N' => $MSG['status_deny'][$sysSession->lang],
		'C' => $MSG['reg_check'][$sysSession->lang]
	);

	$require = array(
		'noncheck' => $MSG['cs_allow'][$sysSession->lang],
		'check'    => $MSG['cs_check'][$sysSession->lang],
		'admonly'  => $MSG['cs_deny'][$sysSession->lang]
	);
        
    $share = array(
        'FB'      => '/theme/default/learn_mooc/co_icon_fb_1.png',
        'PLURK'   => '/theme/default/learn_mooc/co_icon_plurk_1.png',
        'TWITTER' => '/theme/default/learn_mooc/co_icon_twitter_1.png',
        'LINE'    => '/theme/default/learn_mooc/co_icon_line_1.png',
        'WECHAT'  => '/theme/default/learn_mooc/co_icon_wchat_1.png'
	);
        
    // 如果有設定 FB 常數值才顯示
    $register = array(
		'GENERAL'   =>  $MSG['free_registion'][$sysSession->lang],
		'FB'        =>  array(
                            'name'      => $MSG['use_fb_reg'][$sysSession->lang],
                            'id'        => '',
                            'secret'    => ''
                        )
	);
    
    // 樣式
    $style = array(
		'black'     => $MSG['theme_elegant_gray'][$sysSession->lang],
        'orange'    => $MSG['theme_lively_orange'][$sysSession->lang],
        'blue'      => $MSG['theme_steady_blue'][$sysSession->lang]
	);
    
    // 寬度
    $customWidth = array(
        'full'  => $MSG['show_full'][$sysSession->lang],
		'fixed' => $MSG['show_fixed'][$sysSession->lang].'(1000px)'
    );
    
	// array(型態, 長度, 名稱, id, value, default value, extra, 說明, 必填, 顯示區塊(css) );
	$school = array(
        // 29 => array('title'   ,   20, $MSG['mooc_school_schematic'][$sysSession->lang], ''               , ''                                                     , '', ''                              , ''),
        // 30 => array('img'     ,    0, $MSG['mooc_school_schematic'][$sysSession->lang]                   , 'schematic'      , '/theme/default/learn_mooc/index_schematic.png'        , '', ''                              , ''),
         0 => array('title'   ,   20, $MSG['mooc_infomation'][$sysSession->lang]      , ''                , ''         , '', ''                                                                                      , '*'.$MSG['required'][$sysSession->lang]       , '' , 'block-hd'),
         1 => array('text'    ,   40, $MSG['mooc_school_name'][$sysSession->lang]     , 'schname'         , ''         , '', 'placeholder="'.$MSG['over_64_char'][$sysSession->lang].'" '                            , ''                                            , 'Y', 'block-hd'),
         2 => array('text'    ,   40, $MSG['mooc_school_website'][$sysSession->lang]  , 'serhost'         , ''         , '', 'placeholder="'.$MSG['over_64_char'][$sysSession->lang].'" '                            , ''                                            , 'Y' , 'block-hd'),
         3 => array('text'    ,   40, $MSG['school_mail'][$sysSession->lang]          , 'school_mail'     , ''         , '', 'placeholder="'.$MSG['msg_school_mail'][$sysSession->lang].'" '                         , ''                                            , 'Y' , 'block-hd'),
        // 4 => array('text'  ,   20, $MSG['school_academic'][$sysSession->lang]      , 'manager'        , ''        , '', ''                              , '&nbsp;'),
        // 5 => array('select'  ,    0, $MSG['item_theme'][$sysSession->lang]         , 'theme'          , $theme    , '', ''                              , '&nbsp;'),
         6 => array('select'  ,    0, $MSG['item_language'][$sysSession->lang]        , 'lang'            , $lang      , '', ''                                                                                      , '&nbsp;'                                      , '', 'block-hd'),
        // 7 => array('radio'   ,    0, $MSG['item_guest'][$sysSession->lang]         , 'allow_guest'    , $allow    , '', 'onclick="guestNum(this.value)"', '&nbsp;'),
        // 8 => array('text'    ,    6, $MSG['item_guest_limit'][$sysSession->lang]   , 'guestLimit'     , ''        , '', 'id="guestLimit" maxlength="9"' , ''),
        // 9 => array('radio'   ,    0, $MSG['item_register'][$sysSession->lang]      , 'canReg'         , $reg_allow, '', ''                              , '&nbsp;'),
        // 10 => array('radio' ,    0, $MSG['item_require'][$sysSession->lang]        , 'instructRequire', $require  , '', ''                              , '&nbsp;'),
        11 => array('text'    ,   20, $MSG['item_quota'][$sysSession->lang]           , 'courseQuota'     , ''         , '', 'maxlength="10" placeholder="'.$MSG['msg_quota'][$sysSession->lang].'" '                , ''                                            , '' , 'block-hd'),
        12 => array('text'    ,   20, $MSG['item_door_quota'][$sysSession->lang]      , 'doorQuota'       , ''         , '', 'maxlength="10" placeholder="'.$MSG['msg_quota'][$sysSession->lang].'" '                , ''                                            , '' , 'block-hd'),
        14 => array('file'    ,   10, 'LOGO'                                          , 'logo'            , ''         , '', 'maxlength="10"'                                                                        , $MSG['msg_img_limit_logo'][$sysSession->lang] , '', 'block-hd'),
        46 => array('radio'   ,   10, $MSG['theme_style'][$sysSession->lang]          , 'style'           , $style    , '', 'maxlength="10"'                                                                        , ''                                            , '', 'block-hd'),
        47 => array('radio'   ,   10, $MSG['sub_theme_style'][$sysSession->lang]      , 'sub_style'       , $style    , '', 'maxlength="10"'                                                                        , ''                                            , '', 'block-hd'),
        13 => array('file'    ,   10, 'ICON'                                          , 'icon'            , ''         , '', 'maxlength="10"'                                                                        , $MSG['msg_img_limit_icon'][$sysSession->lang] , '', 'block-hd'),
        15 => array('checkbox',   10, $MSG['mooc_share'][$sysSession->lang]           , 'share'           , $share     , '', ''                                                                                      , '&nbsp;'                                      , '', 'block-hd'),
        16 => array('checkbox',   10, $MSG['mooc_register'][$sysSession->lang]        , 'canReg1'         , $register  , '', 'onclick=checkFB(this);'                                                                , '&nbsp;'                                      , '', 'block-hd'),
        17 => array('radio'   ,    0, $MSG['item_multi_login'][$sysSession->lang]     , 'multi_login'     , $allow     , '', ''                                                                                      , '&nbsp;'                                      , '', 'block-hd'),
        18 => array('title'   ,   20, $MSG['mooc_banner_area'][$sysSession->lang]     , ''                , ''         , '', ''                                                                                      , '*'.$MSG['required'][$sysSession->lang]       , '', 'block-br'),
        19 => array('text'    ,   40, $MSG['mooc_main_title'][$sysSession->lang]      , 'main_title'      , ''         , '', 'placeholder="'.$MSG['over_128_char'][$sysSession->lang].'" '                           , ''                                            , 'Y', 'block-br'),
        20 => array('text'    ,   40, $MSG['mooc_sub_title'][$sysSession->lang]       , 'sub_title'       , ''         , '', 'placeholder="'.$MSG['over_128_char'][$sysSession->lang].'" '                           , ''                                            , '', 'block-br'),
        21 => array('textarea',   80, $MSG['mooc_bottom_title'][$sysSession->lang]    , 'bottom_title'    , ''         , '', 'cols="80" placeholder="'.$MSG['over_128_char'][$sysSession->lang].'" '                 , ''                                            , '', 'block-br'),
        22 => array('file'    ,   10, $MSG['mooc_rep_feat_figure'][$sysSession->lang] , 'rep_img'         , ''         , '', 'maxlength="10"'                                                                        , $MSG['msg_img_limit_rep'][$sysSession->lang]  , '', 'block-br'),
        23 => array('file'    ,   10, $MSG['mooc_ban_feat_figure'][$sysSession->lang] , 'brand_img'       , ''         , '', 'maxlength="10"'                                                                        , $MSG['msg_img_limit_ban'][$sysSession->lang]  , '', 'block-br'),
        24 => array('title'   ,   20, $MSG['mooc_footer_area'][$sysSession->lang]     , ''                , ''         , '', ''                                                                                      , '*'.$MSG['required'][$sysSession->lang]       , '', 'block-ft'),
        25 => array('text'    ,   60, $MSG['mooc_about_us'][$sysSession->lang]        , 'about_us'        , ''         , '', 'placeholder="'.$MSG['mooc_need_http'][$sysSession->lang].'" '                          , ''                                            , '', 'block-ft'),
        26 => array('text'    ,   60, $MSG['mooc_contact_us'][$sysSession->lang]      , 'contact_us'      , ''         , '', 'placeholder="'.$MSG['mooc_need_http'][$sysSession->lang].'" '                          , ''                                            , '', 'block-ft'),
        27 => array('text'    ,   60, $MSG['mooc_faq'][$sysSession->lang]             , 'faq'             , ''         , '', 'placeholder="'.$MSG['mooc_need_http'][$sysSession->lang].'" '                          , ''                                            , '', 'block-ft'),
        28 => array('text'    ,   80, $MSG['mooc_other_info'][$sysSession->lang]      , 'other_info'      , ''         , '', 'placeholder="'.$MSG['over_255_char'][$sysSession->lang].'" '                           , ''                                            , '', 'block-ft'),
        31 => array('checkbox',   80, ''                                              , 'search_bar'      , 'true'     , '', ''                                                                                      , $MSG['show_search'][$sysSession->lang]        , '', 'block-br'),
        30 => array('checkbox',   80, ''                                              , 'news_bar'      , 'true'     , '', ''                                                                                      , $MSG['show_news'][$sysSession->lang]        , '', 'block-br'),
        32 => array('piclink' ,   80, $MSG['mooc_poster'][$sysSession->lang].'1'      , 'poster_1'        , 'ads001'   , '', ''                                                                                      , $MSG['msg_img_limit_post'][$sysSession->lang] , 'Y', 'block-br'),
        33 => array('piclink' ,   80, $MSG['mooc_poster'][$sysSession->lang].'2'      , 'poster_2'        , 'ads002'   , '', ''                                                                                      , $MSG['msg_img_limit_post'][$sysSession->lang] , '', 'block-br'),
        34 => array('piclink' ,   80, $MSG['mooc_poster'][$sysSession->lang].'3'      , 'poster_3'        , 'ads003'   , '', ''                                                                                      , $MSG['msg_img_limit_post'][$sysSession->lang] , '', 'block-br'),
        // 35 => array('file'    ,   10, $MSG['mooc_bg_img'][$sysSession->lang]          , 'bg_img_1'        , ''         , '', 'maxlength="10"'                                                                        , $MSG['msg_img_limit_bg'][$sysSession->lang]   , 'Y', 'block-br'),
        36 => array('title'   ,   20, $MSG['mooc_content'][$sysSession->lang]         , ''                , ''         , '', ''                                                                                      , '*'.$MSG['required'][$sysSession->lang]       , '', 'block-ct'),
        37 => array('column'  ,   20, ''                                              , ''                , ''         , '', ''                                                                                      , ''                                            , '', 'block-ct'),
        38 => array('text'    ,   80, $MSG['mooc_all_brand'][$sysSession->lang]       , 'all_brand'       , 'franchisee', '', ''                                                                                     , ''                                            , '', 'block-ct'),
        39 => array('link'    ,   80, $MSG['mooc_start_courses'][$sysSession->lang]   , 'course_list'     , 'courselist', '', ''                                                                                     , ''                                            , '', 'block-ct'),
        // 自訂array(型態, 長度, 名稱, id, 公開列表, 會員列表, extra, sysid, 必填, 顯示區塊(css) );
        40 => array('textlink',   80, $MSG['mooc_forums'][$sysSession->lang]          , 'pubilc_forum'    , 'forum'    , '', ''                                                                                      , 'SYS_01_05_010'                               , '', 'block-ct'),
        41 => array('piclink' ,   80, $MSG['mooc_custom_area'][$sysSession->lang].'1' , 'custom_1'        , 'custom1'  , '', ''                                                                                      , $MSG['msg_img_limit_cus'][$sysSession->lang]  , '', 'block-ct'),
        42 => array('piclink' ,   80, $MSG['mooc_custom_area'][$sysSession->lang].'2' , 'custom_2'        , 'custom2'  , '', ''                                                                                      , $MSG['msg_img_limit_cus'][$sysSession->lang]  , '', 'block-ct'),
        43 => array('title'   ,   20, $MSG['mooc_shortcut_editor'][$sysSession->lang] , ''                , ''         , '', ''                                                                                      , '*'.$MSG['required'][$sysSession->lang]       , '', 'block-qk'),
        44 => array('column'  ,   20, ''                                              , ''                , ''         , '', ''                                                                                      , ''                                            , '', 'block-qk'),
        // 自訂array(型態, 長度, 名稱, id, 公開列表, 會員列表, extra, sysid, 必填, 顯示區塊(css) );
        45 => array('textlink',   80, $MSG['mooc_online_disc'][$sysSession->lang]     , 'online_chat'     , 'onlinehelp', '', ''                                                                                     , 'SYS_01_05_011'                               , '', 'block-qk'),
        
	);

	$sid   = '';
	$shost = '';
        /*
	if ($actType == 'reEdit') {
		$school[0][4]  = trim($_POST['schname']);
		$school[1][4]  = trim($_POST['serhost']);
		$school[2][4]  = trim($_POST['school_mail']);
		$school[4][5]  = trim($_POST['theme']);
		$school[5][5]  = trim($_POST['lang']);
		$school[6][5]  = trim($_POST['allow_guest']);
		$school[7][4]  = trim($_POST['guestLimit']);
		$school[8][5]  = trim($_POST['multi_login']);
		$school[9][5]  = trim($_POST['canReg']);
		$school[10][5] = trim($_POST['instructRequire']);
		$school[11][4] = trim($_POST['courseQuota']);
		$school[12][4] = trim($_POST['doorQuota']);
	}
        */
	switch ($actType) {
		case 'Edit':
                        $sid   = $_POST['sid'];
                        $shost = $_POST['shost'];

                        // value
                        $school[1][4]  = $RS['school_name'];
                        $school[2][4]  = $RS['school_host'];
                        $school[3][4]  = $RS['school_mail'];
                        // $school[8][4]  = $RS['guestLimit'];
                        $school[11][4] = $RS['courseQuota'];
                        $school[12][4] = $RS['quota_limit'];
                        $school[16][4]['FB']['id'] = $RS['canReg_fb_id'];
                        $school[16][4]['FB']['secret'] = $RS['canReg_fb_secret'];
                        $school[19][4] = $RS['banner_title1'];
                        $school[20][4] = $RS['banner_title2'];
                        $school[21][4] = $RS['banner_title3'];
                        $school[25][4] = $RS['footer_about'];
                        $school[26][4] = $RS['footer_contact'];
                        $school[27][4] = $RS['footer_faq'];
                        $school[28][4] = $RS['footer_info'];
                        // default
                        // $school[5][5]  = $RS['theme'];
                        $school[6][5]  = $RS['language'];
                        // $school[7][5]  = $RS['guest'];
                        // $school[9][5]  = $RS['canReg'];
                        // $school[10][5] = $RS['instructRequire'];
                        $school[15][5]  = $RS['social_share'];
                        if ($RS['canReg'] == 'Y') {
                            $school[16][5]  = 'GENERAL,'.$RS['canReg_ext'];
                        } else {
                            $school[16][5]  = $RS['canReg_ext'];
                        }
                        $school[17][5]  = $RS['multi_login'];
                        $school[31][5]  = ($listPl['content_sw']['searchbar'] == 'true')? true : false;
                        $school[30][5]  = ($listPl['content_sw']['news_bar'] == 'true')? true : false;
                        $school[46][5]  = in_array($listPl['theme']['style'], array('black', 'orange', 'blue')) ? $listPl['theme']['style'] : (($isPsch == true)?'orange':'black');
                        if (true == $isPsch) {
                            $school[47][5]  = (null !== $listPl['theme']['sub_style']) ? $listPl['theme']['sub_style'] : 'blue';
                        } else {
                            unset($school[47]);
                        }
                        break;

		case 'reEdit':
                        // value
                        $school[1][4]  = trim($_POST['schname']);;
                        $school[2][4]  = trim($_POST['serhost']);;
                        $school[3][4]  = trim($_POST['school_mail']);
                        $school[11][4] = trim($_POST['courseQuota']);
                        $school[12][4] = trim($_POST['doorQuota']);
                        $school[16][4]['FB']['id'] = trim($_POST['FB_id']);
                        $school[16][4]['FB']['secret'] = trim($_POST['FB_secret']);
                        $school[19][4] = trim($_POST['main_title']);
                        $school[20][4] = trim($_POST['sub_title']);
                        $school[21][4] = trim($_POST['bottom_title']);
                        $school[25][4] = trim($_POST['about_us']);
                        $school[26][4] = trim($_POST['contact_us']);
                        $school[27][4] = trim($_POST['faq']);
                        $school[28][4] = trim($_POST['other_info']);
                        // default
                        // $school[5][5]  = trim($_POST['theme']);
                        $school[6][5]  = trim($_POST['language']);
                        // $school[7][5]  = trim($_POST['guest']);
                        //$school[9][5]  = trim($_POST['canReg']);
                        // $school[10][5] = $RS['instructRequire']);
                        $school[15][5]  = $_POST['share'];
                        $school[16][5]  = trim($_POST['canReg1']);

                        $school[17][5]  = trim($_POST['multi_login']);
                                        
                        // set
                        $actType = 'Edit';
                        $sid     = $_POST['sid'];
                        $shost   = $_POST['shost'];
                        break;

		default:
			die($MSG['access_deny'][$sysSession->lang]);
	}
    
    $blockary = array(
        'headerForm' => 'block-hd',
        'bannerForm' => 'block-br',
        'contentForm' => 'block-ct',
        'quickForm' => 'block-qk',
        'footerForm' => 'block-ft'        
    );


	// 開始呈現 HTML
	showXHTML_head_B($MSG['html_title_modify'][$sysSession->lang], '8');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_CSS('include', "/theme/default/fancybox/jquery.fancybox.css");
    showXHTML_script('include', "/lib/jquery/jquery.min.js");
    showXHTML_script('include', "/lib/jquery.form.min.js");
    showXHTML_script('include', "/theme/default/fancybox/jquery.fancybox.pack.js");
    showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');
        // css 
        echo '<style>
            select {
                width: 250px;
                /*
                overflow: hidden;
                word-wrap: break-all;
                text-overflow: ellipsis;
                display: inline-block;
                */
                box-sizing: border-box;
            }
        </style>';
		$ary = array();
		$ary[] = array($title, 'tabsTag');
		// $colspan = 'colspan="2"'; 
       // echo '<iframe src="/mooc/index.php" width="950" height="600"></iframe>';
       // echo '<img src="/theme/default/learn_mooc/index_schematic.png" alt="'.$MSG['mooc_school_schematic'][$sysSession->lang].'" height="400px">';
		echo '<div align="center">';
        // 預覽圖
            // css
        echo '<style>
            .cssBtn { padding: 2px 6px; }
            .preview-well { height: auto; width: 800px; border: 1px solid #000; display: inline-block; font-size: 12px; }
            .preview-well .wchild { padding: 0 60px; overflow: hidden; }
            .wchild .tips-word { display: none; }
            .wchild:hover > .tips-word { display: block;}
            .preview-hd { background-color: black; height: 30px; color: white; height: 30px; line-height: 30px;}
            .preview-hd .item { float: right; padding: 0 5px; height: 20px; line-height: 20px; }
            .preview-hd .item-up { border-bottom: 2px solid #FCAE3F; }
            .preview-hd .item-down { border-top: 2px solid #FCAE3F; margin-top: 8px; }
            .preview-hd .tab { background: #FB9504; color: #FFFFFF; }
            .preview-hd .tab:hover { background: #04B0B2; border-color: #40C3C4;}
            .preview-hd .btn { border: 1px solid #5A5A5A; border-top: 0; height: 21px; background: #242424; margin: 0 0 0 4px; }
            .preview-hd .btn-active { background: #03A385; }
            .preview-hd .info {  }
            .preview-br { /*background: transparent url(\'/base/'.$sid.'/door/tpl/banner_bg.png?'.time().'\') repeat;*/ position: relative; background-color: #FB9504; background-size: 100% 100%; height:100px; }
            .preview-br .search-bar { z-index: 10; position: absolute; bottom: 0; left: 200px; padding: 20px; text-align: left; }
            .preview-ct { background: #ECECEC; }
            .preview-ct .cchild { text-align: center; line-height: 70px; height: 70px; border: 1px solid #000; margin: 2px; background: #FFFFFF; }
            .preview-ft {background-image: linear-gradient(to bottom, #898989 50px, #282828 1px, #9e9584 28px); filter: progid:DXImageTransform.Microsoft.Gradient(gradientType=0,startColorStr=#FF898989,endColorStr=#FF9e9584); height: 80px; color: white; overflow: hidden; }
            .preview-ft .share img { margin-top: 20px; }
            .preview-ft .preview-copyright { float: right; color: #FFFFFF; }
            /* 色系 */
            .orange .preview-hd { background-color: white; height: 30px; color: #898989; height: 30px; line-height: 30px;}
            .orange .preview-hd .tab:hover { background: #E55756; border-color: #FF7F7E;}
            .orange .preview-hd .btn { background: #CCCCCC; color: #ECECEC; }
            .orange .preview-hd .btn-active { background: #048E89; color: #FFFFFF; }
            .blue .preview-hd { background-color: white; height: 30px; color: #898989; height: 30px; line-height: 30px;}
            .blue .preview-hd .tab:hover { background: #E55756; border-color: #FF7F7E;}
            .blue .preview-hd .btn { background: #CCCCCC; color: #ECECEC; }
            .blue .preview-hd .btn-active { background: #048E89; color: #FFFFFF; }
            .blue .preview-br {background-color: #2FC3BD;}
        </style>';
            // html
        
        echo '<div>
            <div class="preview-well">
                <div class="wchild preview-hd">
                    <div style="float: left;">
                        <img src="/base/'.$sid.'/door/tpl/logo.png?'.time().'" height="25" />
                    </div>
                    <div class="item item-up btn btn-active">'.'繁'.'</div>
                    <div class="item item-up btn">'.'简'.'</div>';
                    // <div class="item item-up">'.$sysSession->username.'　'.$MSG['btn_logout'][$sysSession->lang].'</div>
                    echo '<div class="item item-up tab" style="border-left: 1px dashed #FFFFFF;">'.$MSG['btn_query_password'][$sysSession->lang].'</div>
                    <div class="item item-up tab" style="border-left: 1px dashed #FFFFFF;">'.$MSG['btn_register'][$sysSession->lang].'</div>
                    <div class="item item-up tab">'.$MSG['login'][$sysSession->lang].'</div>';
                    // <div class="item item-down">'.$MSG['explorecourse'][$sysSession->lang].'</div>
                echo '</div>
                <div class="wchild preview-br">
                    <div class="search-bar">'.
                        // <div style="color: white; text-align: left; padding: 0 0 5px 0;"><span id="bt1" style="font-size: 16px;">'.$RS['banner_title1'].'</span>&nbsp;<span id="bt2">'.$RS['banner_title2'].'</span></div>'.
                        // <div style="-webkit-border-radius: 2px; -moz-border-radius: 2px; -ms-border-radius: 2px; -o-border-radius: 2px; border-radius: 2px;background-color: #DCDCDC; width:200px;">
                            '<div>
                                <input type="text" placeholder="'.$MSG['searchcourse'][$sysSession->lang].'" style="height: 15px; height: 10px\9; box-shadow: 0 0 0 0;"/>
                                <button style="height: 15px; vertical-align: top;">&nbsp;</button>
                            </div>'.
                        // </div>
                        // '<div  id="bt3" style="color: white; text-align: left; padding: 5px 0; width: 400px; height: 2em; overflow: hidden;">'.strip_tags($RS['banner_title3']).'</div>
                    '</div>
                    <img src="/base/'.$sid.'/door/tpl/ad001.png?'.time().'" height="80" width="400" style="margin-top: 10px;" />
                </div>';
                if ($isPsch === true || 1) {
                    echo '<div class="wchild preview-ct">
                        <div class="cchild">'.$listPl['franchisee']['title'].'</div>
                    </div>';
                }
                echo '<div class="wchild preview-ct">
                    <div class="cchild">'.$listPl['courselist']['title'].'</div>
                </div>
                <div class="wchild preview-ct">
                    <div class="cchild">'.$listPl['forum']['title'].'</div>
                </div>
                <div class="wchild preview-ct">
                    <div class="cchild">'.$listPl['custom1']['title'].'</div>
                </div>
                <div class="wchild preview-ct">
                    <div class="cchild">'.$listPl['custom2']['title'].'</div>
                </div>
                <div class="wchild preview-ft">
                    <div style="height: 50px;">
                        <div style="float: left;">
                            <div style="display: table-cell; vertical-align: middle; height: 50px; "><img src="/theme/default/learn_mooc/co_icon_people.png?" height="25" /></div>
                            <div style="height: 50px; line-height: 50px; display: table-cell; vertical-align: middle;">&nbsp;'.$MSG['visiter'][$sysSession->lang].': 1 人</div>
                        </div>
                        <div class="share" style="float: right;">
                            <img src="/theme/default/learn_mooc/co_icon_fb.png?" height="15" />
                            <img src="/theme/default/learn_mooc/co_icon_plurk.png?" height="15" />
                            <img src="/theme/default/learn_mooc/co_icon_twitter.png?" height="15" />
                            <img src="/theme/default/learn_mooc/co_icon_line.png?" height="15" />
                            <img src="/theme/default/learn_mooc/co_icon_wchat.png?" height="15" />
                        </div>
                    </div>
                    <div style="height: 30px; line-height: 30px;">
                        <div style="float: left;">'.$MSG['tool_about'][$sysSession->lang].' <span style="color: #626262;">|</span> '.$MSG['contactus'][$sysSession->lang].' <span style="color: #626262;">|</span> '.$MSG['tool_faq'][$sysSession->lang].'</div>
                        <div class="preview-copyright">'.$RS['footer_info'].'</div>
                    </div>
                </div>
            </div>';
            echo '<div style="display: inline-block; height: 515px; right: 45px; bottom: 60px; position: relative;">
                <div style="height: 30px;">';
                if ($editAcl == true) {
                    echo '<a class="cssBtn editBtn" href="#sch-priority" data-id="block-hd" data-form="headerForm" onclick="showEdit(this);">'.$MSG['btn_edit'][$sysSession->lang].'</a>';
                } else {
                    echo '<a class="cssBtn editBtn" href="javascript:;" disabled>'.$MSG['btn_edit'][$sysSession->lang].'</a>';
                }
                echo '</div>
                <div style="height: 100px;"><a class="cssBtn editBtn" href="#sch-priority" data-id="block-br" data-form="bannerForm" onclick="showEdit(this);">'.$MSG['btn_edit'][$sysSession->lang].'</a></div>
                <div style="height: 270px;"><a class="cssBtn editBtn" href="#sch-priority" data-id="block-ct" data-form="contentForm" onclick="showEdit(this);">'.$MSG['btn_edit'][$sysSession->lang].'</a></div>
                <div  style="height: 110px;"><a class="cssBtn editBtn" href="#sch-priority" data-id="block-qk" data-form="quickForm" onclick="showEdit(this);">'.$MSG['btn_edit'][$sysSession->lang].'</a></div>
                <div style="height: 0px;">';
                if ($editAcl == true) {
                    echo '<a class="cssBtn editBtn" href="#sch-priority" data-id="block-ft" data-form="footerForm" onclick="showEdit(this);">'.$MSG['btn_edit'][$sysSession->lang].'</a>';
                } else {
                    echo '<a class="cssBtn editBtn" href="javascript:;" disabled>'.$MSG['btn_edit'][$sysSession->lang].'</a>';
                }
            echo '</div></div>
        </div>';
        
        // 隱藏 form
        echo '<div id="sch-priority" style="display: none;">';
		
        foreach ($blockary as $keyo => $valo) {
            // 內容商的一般及超級管理員不可編輯 head 及 foot
            if (($valo == 'block-hd' || $valo == 'block-ft') &&  $editAcl !== true){
                continue;
            }
            showXHTML_form_B('method="post" action="mooc_sch_save.php" class="edit-form" style="display: inline;" enctype="multipart/form-data"', $keyo);
                showXHTML_table_B('width="950px" border="0" cellspacing="1" cellpadding="3" id="'.$valo.'" class="cssTable" style="table-layout: fixed;"');
                    echo '<col width="150px" />
                    <col width="250px" />
                    <col width="550px" />';
                    foreach ($school as $key => $val) {
                        if (empty($val[0])) continue;
                        if ($val[9] !== $valo)  continue;
                        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        // 標題
                        if ($val[0] == 'title') {
                            showXHTML_tr_B('class="cssTrHead"');
                                showXHTML_td('colspan="3" valign="top"', '<span style="font-weight: bold;">' . $val[2] . '</span>' . '<span style="color:#FF0000; float:right">' . $val[7] . '</span>');
                            showXHTML_tr_E('');
                            continue;
                        }
                        // 圖片
                        if ($val[0] == 'img') {

                            showXHTML_tr_B($col);
                                showXHTML_td_B('colspan=3 align="center"');
                                echo '<a href="'.$val[4].'" title="'.$val[2].'" target="_blank"><img src="'.$val[4].'" alt="'.$val[3].'" height="400px"></a>';
                                echo '<div style="width: auto; height: auto; overflow: auto; position: relative;">
                                    <div id="'.$val[3].'_img" style="display: none;">
                                        <img src="'.$val[4].'"/>
                                    </div>
                                </div>';
                                showXHTML_td_E();
                            showXHTML_tr_E('');
                            continue;
                        }
                        
                        $extra  = ($val[0] != 'radio') ? 'class="cssInput" ' : '';
                        $extra .= $val[6];
                        
                        // 內容區塊需可排序，另外寫
                        if ($val[9] == 'block-ct' || $val[9] == 'block-qk') {
                            if ($val[9] == 'block-ct') {
                                $curBlock = 'content';
                            } else if ($val[9] == 'block-qk') {
                                $curBlock = 'quick';
                            }
                            // 品牌大街只有入口網站才顯示
                            if ($val[3] == 'all_brand' && $curDa['is_portal'] != '1') {
                                continue;
                            }
                            if ($val[0] == 'column') {
                                showXHTML_tr_B($col);
                                    showXHTML_td('width="50"', $MSG['column_order'][$sysSession->lang]);
                                    showXHTML_td('', $MSG['column_main_block'][$sysSession->lang]);
                                    showXHTML_td('', $MSG['column_set_up'][$sysSession->lang]);
                                showXHTML_tr_E('');
                            } else {
                                showXHTML_tr_B($col);
                                    if ($val[3] == 'pubilc_forum') {
                                        showXHTML_td_B('align="center" rowspan="3"');
                                    } else {
                                        showXHTML_td_B('align="center"');
                                    }
                                        showXHTML_input('text', $val[3] . '_priority', $listPl[$curBlock.'_pri'][$val[4]], $val[5], 'style="width: 20px; text-align: center;"');
                                    showXHTML_td_E();
                                    showXHTML_td_B();
                                        showXHTML_input('checkbox', $val[3] . '_enable', 'true', $listPl[$curBlock.'_sw'][$val[4]]==='true', $extra);
                                        echo $val[2].'<br>';
                                        if ($val[3]=='course_list') {
                                            echo '<span style="color: red;">('. $MSG['msg_courselist'][$sysSession->lang] .')</span><br>';
                                        }
                                        showXHTML_input('text', $val[3] . '_name', $listPl[$val[4]]['title'], $val[5], $extra);
                                        if ($val[3]=='custom_1' || $val[3]=='custom_2') {
                                            // 寬度設定
                                            echo '<br>';
                                            showXHTML_input('radio', $val[3].'_pictype', $customWidth, $listPl[$val[4]]['pic_style'], $extra);
                                        }
                                    showXHTML_td_E();
                                    if ($val[0] == 'piclink') {
                                        showXHTML_td_B();
                                            showXHTML_input('file', $val[3].'_pic', $val[4], $val[5], $extra);
                                            echo $val[7] . '<br>';
                                            echo '<input type="radio" id="'.$val[3].'_linktype1" name="'.$val[3].'_linktype" value="1" '.(($listPl[$val[4]]['url_type']=='1')?'checked="checked"':'').'><label for="'.$val[3].'_linktype1">'.$MSG['link_course_info'][$sysSession->lang].' </label>';
                                            // /info/10000001
                                            showXHTML_input('select', $val[3].'_link[]', $listCourse, $val[5], $extra);
                                            echo '<br><input type="radio" id="'.$val[3].'_linktype2" name="'.$val[3].'_linktype" value="2" '.(($listPl[$val[4]]['url_type']=='2')?'checked="checked"':'').'><label for="'.$val[3].'_linktype2">'.$MSG['link_faq'][$sysSession->lang].' </label>';
                                            // 取常見問題內容當網址
                                            showXHTML_input('select', $val[3].'_link[]', $listQandA, $val[5], $extra);
                                            echo '<br><input type="radio" id="'.$val[3].'_linktype3" name="'.$val[3].'_linktype" value="3" '.(($listPl[$val[4]]['url_type']=='3')?'checked="checked"':'').'><label for="'.$val[3].'_linktype3">'.$MSG['link_other'][$sysSession->lang].' </label>';
                                            showXHTML_input('text', $val[3].'_link[]', (($listPl[$val[4]]['url_type']=='3')?$listPl[$val[4]]['url']:''), $val[5], $extra);
                                        showXHTML_td_E();
                                    } else if ($val[3]=='course_list') {
                                        showXHTML_td_B();
                                            echo '<a href="javascript:;" onclick="parent.sysbar.chgMenuItem(\'SYS_01_02_003\')">'.$MSG['link_course_set'][$sysSession->lang].'</a>　';
                                            echo '<a href="javascript:;" onclick="parent.sysbar.chgMenuItem(\'SYS_01_02_004\')">'.$MSG['link_group_set'][$sysSession->lang].'</a>';
                                        showXHTML_td_E();
                                    } else if ($val[3]=='pubilc_forum' || $val[3]=='online_chat') {
                                        if ($val[3]=='pubilc_forum') {
                                                showXHTML_td_B();
                                                    // 利用沒用到的array 欄位 儲存 連結ID  公開及會員列表
                                                    echo $MSG['status_public'][$sysSession->lang].':<div style="word-wrap: break-word;">' . ((is_array($forumOpenList))?implode(',', $forumOpenList):'') . '</div><br>';
                                                    echo $MSG['status_member'][$sysSession->lang].':<div style="word-wrap: break-word;">' . ((is_array($forumTaonlyList))?implode(',', $forumTaonlyList):'') . '</div><br>';
                                                    echo '<a href="javascript:;" onclick="parent.sysbar.chgMenuItem(\''.$val[7].'\')">'.$MSG['advanced_settings'][$sysSession->lang].'</a>';
                                                showXHTML_td_E();
                                            showXHTML_tr_E();
                                            showXHTML_tr_B($col);
                                                showXHTML_td_B();
                                                    showXHTML_input('checkbox', 'news_enable', 'true', $listPl[$curBlock.'_sw']['news']==='true', $extra);
                                                    echo$MSG['title_latest_news'][$sysSession->lang].'<br>';
                                                    showXHTML_input('text', 'news_name', $listPl['news']['title'], '', $extra);
                                                showXHTML_td_E();
                                                showXHTML_td_B();
                                                    echo '<a href="javascript:;" onclick="parent.sysbar.chgMenuItem(\'SYS_01_06_001\')">'./*$MSG['link_group_set'][$sysSession->lang]*/'設定'.'</a>';
                                                showXHTML_td_E();
                                            showXHTML_tr_E();
                                            showXHTML_tr_B($col);
                                                showXHTML_td_B();
                                                    showXHTML_input('checkbox', 'calendar_enable', 'true', $listPl[$curBlock.'_sw']['calendar']==='true', $extra);
                                                    echo $MSG['title_calendar'][$sysSession->lang].'<br>';
                                                    showXHTML_input('text', 'calendar_name', $listPl['calendar']['title'], $val[5], $extra);
                                                showXHTML_td_E();
                                                showXHTML_td_B();
                                                    echo '<a href="javascript:;" onclick="parent.sysbar.chgMenuItem(\'SYS_01_06_003\')">'./*$MSG['link_group_set'][$sysSession->lang]*/'設定'.'</a>';
                                                showXHTML_td_E();
                                        } else {
                                            showXHTML_td_B();
                                                // 利用沒用到的array 欄位 儲存 連結ID  公開及會員列表
                                                echo $MSG['status_public'][$sysSession->lang].':' . '' . '<br>';
                                                echo $MSG['status_member'][$sysSession->lang].':' . '' . '<br>';
                                                echo '<a href="javascript:;" onclick="parent.sysbar.chgMenuItem(\''.$val[7].'\')">'.$MSG['advanced_settings'][$sysSession->lang].'</a>';
                                            showXHTML_td_E();
                                        }
                                    } else {
                                        showXHTML_td();
                                    }
                                showXHTML_tr_E();
                            }
                            continue;
                        }
                        
                        // 圖片代連結
                        if ($val[0] == 'piclink') {
                            showXHTML_tr_B($col);
                                if ($val[8] == 'Y') {
                                    showXHTML_td('align="right" valign="top"', '<span style="color:#FF0000;">*</span>'.$val[2]);
                                } else {
                                    showXHTML_td('align="right" valign="top"', $val[2]);
                                }
                                showXHTML_td_B('colspan=2');
                                    echo '<div style="float:left; width: 300px; padding: 10px; background: #FFFFFF; border: 1px solid #B5B5B5; text-align: center; min-height: 65px;">';
                                        if (is_file(sysDocumentRoot.$listPl[$val[4]]['pic_path'])) {
                                            echo '<div id="'.$val[3].'_picshow" style="position: relative;">';
                                        } else {
                                            echo '<div id="'.$val[3].'_picshow" style="position: relative; display: none;">';
                                        }
                                            echo '<img src="'.$listPl[$val[4]]['pic_path'].'?'.time().'" alt="'.$val[3].'" style="max-height:80px; max-width: 100%;">' .
                                            '<a href="javascript:;" class="pic-dropbtn" style="position: absolute;top: -7px;right: -7px;" data-pic="'.$val[3].'"><img src="/theme/default/learn_mooc/drop_1.png?"></a>' .
                                        '</div>
                                    </div>';
                                    // echo '<div style="float:left;"><img src="/base/'.$sid.'/door/tpl/logo.png?'.time().'" alt="'.$val[3].'" height="75px" ></div>';
                                    echo '<div style="float: left; display: inline-block; max-width: 50%;">';
                                    showXHTML_input('file', $val[3].'_pic', $val[4], $val[5], $extra);
                                        echo $val[7] . '<br>';
                                        echo '<input type="radio" id="'.$val[3].'_linktype1" name="'.$val[3].'_linktype" value="1" '.(($listPl[$val[4]]['url_type']=='1')?'checked="checked"':'').'><label for="'.$val[3].'_linktype1">'.$MSG['link_course_info'][$sysSession->lang].' </label>';
                                        // /info/10000001
                                        showXHTML_input('select', $val[3].'_link[]', $listCourse, $listPl[$val[4]]['url_default'], $extra);
                                        echo '<br><input type="radio" id="'.$val[3].'_linktype2" name="'.$val[3].'_linktype" value="2" '.(($listPl[$val[4]]['url_type']=='2')?'checked="checked"':'').'><label for="'.$val[3].'_linktype2">'.$MSG['link_faq'][$sysSession->lang].' </label>';
                                        // 取常見問題內容當網址
                                        showXHTML_input('select', $val[3].'_link[]', $listQandA, $listPl[$val[4]]['url_default'], $extra);
                                        echo '<br><input type="radio" id="'.$val[3].'_linktype3" name="'.$val[3].'_linktype" value="3" '.(($listPl[$val[4]]['url_type']=='3')?'checked="checked"':'').'><label for="'.$val[3].'_linktype3">'.$MSG['link_other'][$sysSession->lang].' </label>';
                                        showXHTML_input('text', $val[3].'_link[]', (($listPl[$val[4]]['url_type']=='3')?$listPl[$val[4]]['url']:''), $val[5], $extra);
                                    echo '</div>';
                                showXHTML_td_E();
                            showXHTML_tr_E('');
                            continue;
                        }

                        if ($val[1] > 0) $extra = 'size="' . $val[1] . '" ' . $extra;

                        showXHTML_tr_B($col);
                            if ($val[8] == 'Y') {
                                showXHTML_td('align="right" valign="top"', '<span style="color:#FF0000;">*</span>'.$val[2]);
                            } else {
                                showXHTML_td('align="right" valign="top"', $val[2]);
                            }
                            showXHTML_td_B('colspan=2');
                            if ($val[3] == 'icon') {
                                echo '<div style="float:left; padding-top:5px"><img src="/base/'.$sid.'/door/tpl/icon.ico?'.time().'" / alt="'.$val[3].'" ></div>';
                            } else if ($val[3] == 'logo') {
                                echo '<div style="float:left;"><img src="/base/'.$sid.'/door/tpl/logo.png?'.time().'" alt="'.$val[3].'" height="50px" ></div>';
                            } else if ($val[3] == 'bg_img_1') {
                                echo '<div style="float:left;"><img src="/base/'.$sid.'/door/tpl/banner_bg.png?'.time().'" alt="'.$val[3].'" height="50px" ></div>';
                            } else if ($val[3] == 'brand_img') {
                                echo '<div style="float:left;"><img src="/base/'.$sid.'/door/tpl/brand_logo.png?'.time().'" alt="'.$val[3].'" height="50px" onerror="javascript:this.src=\'/theme/default/learn_mooc/default_brand.png?\'"></div>';
                            } else if ($val[3] == 'rep_img') {
                                echo '<div style="float:left;"><img src="/base/'.$sid.'/door/tpl/rep_img.png?'.time().'" alt="'.$val[3].'" height="50px" onerror="javascript:this.src=\'/theme/default/learn_mooc/default_brand.png?\'"></div>';
                            }
                            if ($val[3] == 'canReg1') {
                                foreach($val[4] as $k => $v) {
                                    showXHTML_input($val[0], $val[3]."[]", $k, preg_match("/".$k."/i", trim($val[5])), $extra);
                                    if (is_array($v)) {
                                        echo $v['name'];
                                        $fbAble = (preg_match("/".$k."/i", trim($val[5])))?'' :'display:none;';
                                        echo ' <span id="fbInfo" style="'.$fbAble.'">(<span style="color:#FF0000;">*</span>ID:';
                                        showXHTML_input('text', $k."_id", $v['id'], '', '');
                                        echo '<span style="color:#FF0000;">*</span>Secret:';
                                        showXHTML_input('text', $k."_secret", $v['secret'], '', '');
                                        echo '<a href="/theme/default/learn_mooc/FB APP setting document.pdf" target=_blank><img src="/theme/default/learn_mooc/help.gif?"></a>)</span> ';
                                    } else {
                                        echo $v;
                                    }
                                }
                            } else if ($val[3] == 'share') {
                                foreach($val[4] as $k => $v) {
                                    showXHTML_input($val[0], $val[3]."[]", $k, preg_match("/".$k."/i", trim($val[5])), $extra);
                                    echo '<img src="'.$v.'?" / alt="'.$k.'" >';
                                }
                            } else if ($val[3] == 'bottom_title1') {
                                $oEditor = new wmEditor;
                                $oEditor->setValue(stripslashes($val[4]));
                                $oEditor->addContType('isHTML', 1);
                                $oEditor->generate($val[3], '700', '450');
                            } else {
                                showXHTML_input($val[0], $val[3], $val[4], $val[5], $extra);
                            }
                            if (($val[3] == 'courseQuota') || ($val[3] == 'doorQuota')) 
                            {
                                showXHTML_td_E('KB');
                            } else if (($val[3] == 'icon') || ($val[3] == 'logo') || ($val[3] == 'bg_img') || ($val[3] == 'feature_img')) {
                                showXHTML_td_E('</br>'.$val[7]);
                            } else if ($val[3] == 'search_bar') {
                                // 位置設定
                                $sSettings =    '<div id="search-settings" style="'.($val[5] == 'true' ?'display: inline-block;':'display: none;').'">'.
                                                    '(X:<input name="search_x" type="text" placeholder="0-472" value="'.$listPl['searchbar']['x'].'">, '.
                                                    'Y:<input name="search_y" type="text" placeholder="0-190" value="'.$listPl['searchbar']['y'].'">)'.
                                                '</div>';
                                // 搜尋bar
                                showXHTML_td_E($val[7].$sSettings);
                            }else {
                                showXHTML_td_E($val[7]);
                            }
                            // showXHTML_td('', $val[7]);
                        showXHTML_tr_E('');
                    }
                    $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                    showXHTML_tr_B($col . ' block-btn"');
                        showXHTML_td_B('colspan="3" align="center"');
                            // showXHTML_input('button', '', $MSG['btn_save'][$sysSession->lang], '', 'class="cssBtn" onclick="$(\'#actForm\').submit();"');
                            $ticket = md5($isSingle . 'mooc'.$actType . $sysSession->ticket .  $sysSession->username . $sid . $shost);
                            showXHTML_input('hidden', 'sid', $sid, '', '');
                            showXHTML_input('hidden', 'shost', $shost, '', '');
                            showXHTML_input('hidden', 'ticket', $ticket, '', '');
                            showXHTML_input('hidden', 'saveblock', '', '', '');
                            showXHTML_input('hidden', 'selectedpic', '', '', '');
                            showXHTML_input('submit', 'propapply', $MSG['btn_apply'][$sysSession->lang], '', 'class="cssBtn"');
                            // showXHTML_input('reset', '', '還原', '', 'name="actReset" class="cssBtn"');
                            showXHTML_input('button', '', $MSG['btn_close'][$sysSession->lang], '', 'class="cssBtn" onclick="$.fancybox.close();"');
                            showXHTML_input('button', '', $MSG['restart_web_server'][$sysSession->lang], '', 'id="restartBtn" style="display: none;" class="cssBtn" onclick="window.location.replace(\'sch_restart.php?restart\')"');
                        showXHTML_td_E('');
                    showXHTML_tr_E('');
                showXHTML_table_E();
            showXHTML_form_E();
        }
        /*
        showXHTML_form_B('method="post" action="" style="display: inline;" onsubmit="return checkData()" enctype="multipart/form-data"', 'actForm');
            $ticket = md5($isSingle . 'mooc'.$actType . $sysSession->ticket .  $sysSession->username . $sid . $shost);
            showXHTML_input('hidden', 'sid', $sid, '', '');
            showXHTML_input('hidden', 'shost', $shost, '', '');
            showXHTML_input('hidden', 'ticket', $ticket, '', '');
            showXHTML_input('hidden', 'reset', '', '', '');
    showXHTML_input('hidden', 'saveblock', '', '', '');
            // showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="cleanHistory()"');
            
            // showXHTML_input('button', '', $MSG['mooc_btn_reset'][$sysSession->lang], '', 'class="cssBtn" onclick="resetPriority()"');
            // showXHTML_input('reset', '', '取消', '', 'id="actReset" style="display:none;" class="cssBtn"');
        showXHTML_form_E();
         * 
         */
        echo '</div>';
        echo '<button class="cssBtn" onclick="cleanHistory()">'. $MSG['btn_return'][$sysSession->lang] .'</button>';
        echo '<button class="cssBtn" onclick="previewStyle()">' . $MSG['btn_preview'][$sysSession->lang] . '</button>';
       // echo '<button class="cssBtn" onclick="resetPriority()">'. $MSG['mooc_btn_reset'][$sysSession->lang] .'</button>';
		echo '</div>';
	showXHTML_body_E('');
?>
