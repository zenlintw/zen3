<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lang/app_course_manage.php');
	require_once(sysDocumentRoot . '/academic/course/course_lib.php');

	$getCourseId = trim($_POST['course_id']);
    $getFilename = (!empty($_POST['filename']))? trim($_POST['filename']) : null ;
    $actionType = (empty($_POST['actionType']))? 'preview' : trim($_POST['actionType']);
    $classify = (empty($_POST['classify']))? 'public' : trim($_POST['classify']);
    $pictureFlag = false;

    /**
     * 取得圖檔的MIME TYPE
     *
     * @param string $filePath 圖檔的實際路徑
     * @return string 圖檔的MIME TYPE
     */
    function getFileMimeType ($filePath) {
        preg_match("|\.([a-z0-9]{2,4})$|i", $filePath, $fileSuffix);
        switch (strtolower($fileSuffix[1])) {
            case 'jpg' :
            case 'jpeg' :
            case 'jpe' :
                return 'image/jpeg';
            case 'png' :
            case 'gif' :
            case 'bmp' :
                return 'image/'.strtolower($fileSuffix[1]);
        }
    }

    // 有收到圖檔名稱，則去取目錄下的圖檔
    if(!empty($getFilename)) {
    	// 將取到的檔名做".."或"斜線"的字串轉換
    	$getFilename = preg_replace(
			array('/\.\.+/', '/[\/\\\\]{2,}/'),
			array('', '/'),
			$getFilename
		);

        // 網頁實際讀取的檔案路徑
        if ($classify === 'public') {
            // 公用圖庫
            $pictureFile = sprintf('/base/%5d/door/APP/course_repos/%s',
                $sysSession->school_id,$getFilename);
        } else {
            // 私人圖庫
            $pictureFile = sprintf('/user/%1s/%1s/%s/app/%s',
                    substr($sysSession->username, 0, 1), substr($sysSession->username, 1, 1), $sysSession->username, $getFilename);
        }
        if(!is_file(sysDocumentRoot.$pictureFile)) {
        	// 如果轉換檔名後發現找不到檔案，則顯示錯誤訊息
            echo <<< EOB
            <script>
                alert('{$MSG['msg_alert_select'][$sysSession->lang]}');
            </script>
EOB;
			$getFilename=null;
        }
    }
    
	$courseId = (!empty($getCourseId))? sysDecode($getCourseId) : '';

	if ($actionType==='save') {
        // 儲存圖片設定
        $mimeFileType = getFileMimeType(sysDocumentRoot.$pictureFile);
		$pictureContent = addslashes(base64_encode(file_get_contents(sysDocumentRoot.$pictureFile)));
		list($exist) = dbGetStSr('CO_course_picture','count(*)',"course_id={$courseId}",ADODB_FETCH_NUM);

		if(!empty($pictureContent)) {
			if($exist > 0) {
	            // 變更
				dbSet('CO_course_picture',"`picture`='{$pictureContent}', `mime_type`='{$mimeFileType}'","`course_id`={$courseId}");
	        } else {
	            // 新增
				dbNew('CO_course_picture','`course_id`,`picture`,`mime_type`',"{$courseId},'{$pictureContent}','{$mimeFileType}'");
	        }
	        // 圖片設定成功
	        $alertMsg = $MSG['msg_save_success'][$sysSession->lang];

		} else {
			// 圖片設定未異動
			$alertMsg = $MSG['msg_save_fail'][$sysSession->lang];
		}
		// 顯示異動結果訊息後，回到課程列表去
		echo <<< EOB
            <script>
	            alert('{$alertMsg}');
	            location.replace("course_list.php");
            </script>;
EOB;
	} else if ($actionType==='remove') {
        // 刪除圖片設定
		dbDel('CO_course_picture',"course_id={$courseId} limit 1");
	}
	
    // 取出資料庫的課程圖片設定
	if (isset($courseId)) {
		$table = 'WM_term_course as a LEFT JOIN CO_course_picture as b on a.course_id=b.course_id';
		$fields = 'a.caption,b.picture,b.mime_type';
		$where = "a.course_id={$courseId}";
		list($captions,$pictureDB, $pictureMimeType) = dbGetStSr($table,$fields,$where, ADODB_FETCH_NUM);

        $pictureDB = base64_decode($pictureDB);
        $pictureType = str_replace('image/', '', $pictureMimeType);
	}

	$removeButtonDisable = 'disabled';  // 移除的按鈕預設為disabled
	$picture = '';
	
    if (!is_null($getFilename)) {
        // 如果有圖檔名稱，則圖片顯示是取圖檔
        $picture = "<img src='{$pictureFile}' width='143' height='100'>";
    } else if (strlen($pictureDB)>0) {
        // 圖檔名稱為null，又從資料庫取出的圖片資訊不是空的
        $tmpPath = '/base/' . $sysSession->school_id . '/door/APP/course_repos/TMP/';
		$tmpFile = base64_encode($courseId.time()).'.'.$pictureType;
		$realFile = sysDocumentRoot.$tmpPath.$tmpFile;
		
		// 刪除原先建立的圖片暫存檔案
		if(is_file($realFile)) {
			@unlink($realFile);
		}
		
		$fp = fopen($realFile,'w');
		fwrite($fp, $pictureDB);
		fclose($fp);
		
		$picture = "<img src='{$tmpPath}{$tmpFile}' width='143' height='100'>";

        // 移除的按鈕可以點擊
		$removeButtonDisable = '';
    }
    
	$lang = getCaption($captions);
	$caption = $lang[$sysSession->lang];

$js = <<< BOF
    var schoolId = {$sysSession->school_id};
    var MSG_ALERT_REMOVE = "{$MSG['msg_alert_remove'][$sysSession->lang]}";

	/**
	 * 課程圖片設定
     * 取得瀏覽的檔名。※※※※ 函數名稱不可改 ※※※※
     * 取得後將檔名以POST method 送出
     *
     * @param {String} filename: 檔名
     * @param {String} classify: 分類(public, private)
     */
    function setPictureFilename (filename, classify) {
        var subForm = top.frames['main'].document.getElementById('submitForm');
        subForm.filename.value = filename;
        subForm.actionType.value = 'preview';
        subForm.classify.value = classify;
        subForm.submit();
        appCoursePictureBrowser.close();
    }

    /**
	 * 瀏覽檔案
	 */
	function browseFile()
    {
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
	 * 取消並回課程列表
	 */
    function cancel() 
    {
		window.location="course_list.php";
	}

    /**
	 * 儲存圖片
	 */
    function coursePictureSave()
    {
    	var subForm = top.frames['main'].document.getElementById('submitForm');
        subForm.actionType.value = 'save';
        subForm.submit();
	}
	
	/**
	 * 刪除圖片
	 */
    function coursePictureRemove()
    {
    	if(!confirm(MSG_ALERT_REMOVE)) {
    		return false;
    	}
    	var subForm = document.getElementById('submitForm');
        subForm.actionType.value = 'remove';
        subForm.submit();
	}

BOF;

showXHTML_head_B('');
showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
showXHTML_script('include', '/lib/common.js');
showXHTML_script('include', '/lib/xmlextras.js');
showXHTML_script('include', '/lib/filter_spec_char.js');
showXHTML_CSS('include', '/lib/jquery/css/jquery-ui-1.8.22.custom.css');
showXHTML_script('include', '/lib/jquery/jquery.min.js');
showXHTML_script('include', '/lib/jquery/jquery-ui-1.8.22.custom.min.js');
showXHTML_script('inline', $js);

showXHTML_head_E();
showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['td_image_setting'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1,'propertyFrame','','style="display: inline"'); //, form_id, table_id, form_extra, isDragable);
			showXHTML_table_B('width="600" border="0" cellspacing="1" cellpadding="3" id="dataTb" class="cssTable"');
                showXHTML_tr_B('class="cssTrHead"');
                    showXHTML_td('align="left" nowrap'." colspan='3'",$MSG['star'][$sysSession->lang].$MSG['required'][$sysSession->lang]);
  				showXHTML_tr_E();
                $cssTR = ($cssTR == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($cssTR);
                      showXHTML_td('align="right"',$MSG['th_course_name'][$sysSession->lang]);
					  showXHTML_td('width="30%" wrap',htmlspecialchars($caption));
                      showXHTML_td('align="left"',$MSG['item_course_remark'][$sysSession->lang]);
  				showXHTML_tr_E();
                $cssTR = ($cssTR == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($cssTR);
                      showXHTML_td('align="right"',$MSG['td_image'][$sysSession->lang].$MSG['star'][$sysSession->lang]);
                      showXHTML_td_B('align="left" nowrap');
                        echo '<span>'.$picture.'</span>';
						showXHTML_input('button', '', $MSG['btn_image_browse'][$sysSession->lang], '', 'class="button01" onclick="browseFile();"');
						showXHTML_input('button', '', $MSG['btn_image_delete'][$sysSession->lang], '', 'class="button01" '.$removeButtonDisable.' onclick="coursePictureRemove();"');
                      showXHTML_td_E();
                      showXHTML_td('align="left"',$MSG['item_image_remark'][$sysSession->lang].'<br>'.
                      							  '<font color="red">'.$MSG['msg_chars_limit'][$sysSession->lang].'</font><br>'.
                      							  $MSG['msg_filetype_limit'][$sysSession->lang]);
  				showXHTML_tr_E();
                $cssTR = ($cssTR == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B('class="cssTrHead"');
      				showXHTML_td_B('align="center" colspan="3"');
      					showXHTML_input('button', 'btnAgree', $MSG['btn_ok'][$sysSession->lang], '', "onclick='coursePictureSave();'");
      					showXHTML_input('button', 'btnDeny' , $MSG['btn_cancel'][$sysSession->lang] , '', "onclick='cancel()'");
      				showXHTML_td_E();
    		    showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
		showXHTML_form_B('method="post" action="course_property.php" enctype="multipart/form-data"', 'submitForm');
			showXHTML_input('hidden', 'course_id', $getCourseId, '', '');
			showXHTML_input('hidden', 'filename', $_POST['filename'], '', '');
            showXHTML_input('hidden', 'actionType', '', '', '');
            showXHTML_input('hidden', 'classify', $_POST['classify'], '', '');
		showXHTML_form_E();
showXHTML_body_E();
	
?>