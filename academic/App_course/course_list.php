<?php
	/**
	 * 課程列表
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/academic/course/course_lib.php');
	require_once(sysDocumentRoot . '/lang/app_course_manage.php');

	// 取SQL檔建立所需table
    if(is_file(sysDocumentRoot. '/academic/App_course/course_picture.sql')) {
        $sysConn->Execute('USE '.sysDBprefix.$sysSession->school_id);
        $sql = file_get_contents(sysDocumentRoot. '/academic/App_course/course_picture.sql');
        $sysConn->Execute($sql);
    }
    
    // 若沒有圖檔暫存目錄，則建立一個出來，有則清除先前建立的暫存圖檔，新圖檔將於稍後課程撈出後重新建立
    $tmpPath = '/base/' . $sysSession->school_id . '/door/APP/course_repos/TMP/';
    if(is_dir(sysDocumentRoot.$tmpPath)) {
    	@exec('rm -f '.sysDocumentRoot.$tmpPath.'*');
    } else {
    	@mkdir(sysDocumentRoot.$tmpPath, 0755);
    }
    
	$keyword = $_POST['keyword'];
	$queryImageStatus = (isset($_POST['imageStatus']))? intval(trim($_POST['imageStatus'])) : 0;

	// 解碼 $gid
	$gid = 10000000;
	if (isset($_POST['ticket'])) {
		$enc = trim($_POST['ticket']);
		$gid = sysDecode($enc);
		$gid = intval($gid);
		$genc = '&ticket=' . $enc;
	} else if (isset($_GET['ticket'])) {
		$enc = trim($_GET['ticket']);
		$gid = sysDecode($enc);
		$gid = intval($gid);
		$genc = '&ticket=' . $enc;
	} else {
		$genc = '';
	}
	if ($gid == 0) $gid = 10000000;

	$schG = sysEncode(10000000);
	$nowG = sysEncode($gid);
	$js = <<< BOF
	var gpsch = "{$schG}", gpidx = "{$nowG}";

	/**
	 * 取得另外的視窗
	 * @return Object 另外的視窗 (other frame)
	 **/
	function getTarget() {
		var obj = null;
		switch (this.name) {
			case "s_main"   : obj = parent.s_catalog; break;
			case "c_main"   : obj = parent.c_catalog; break;
			case "main"     : obj = parent.catalog;   break;
			case "s_catalog": obj = parent.s_main; break;
			case "c_catalog": obj = parent.c_main; break;
			case "catalog"  : obj = parent.main;   break;
			case "s_sysbar" : obj = parent.s_main; break;
			case "c_sysbar" : obj = parent.c_main; break;
			case "sysbar"   : obj = parent.main;   break;
		}
		return obj;
	}

    /**
     * 圖片設定查詢
     **/
	function changeStatus() {
		var status = document.getElementById('imageStatus').value;
		var subForm = document.getElementById('actFm');
		subForm.imageStatus.value = status;
		subForm.submit();
	}

    /**
     * 變更圖片設定
     **/
	function editCourse(val) {
		var obj = document.getElementById("editFm");
		if (obj != null) {
			obj.course_id.value = val;
			obj.submit();
		}
		return true;
	}

	var aryBtns = ["btnSel", "btnPage", "btnFirst", "btnPrev", "btnNext", "btnLast", "btnUp", "btnDw", "btnAppend", "btnMove", "btnRemove"];
	window.onload = function () {
		var obj1 = null, obj2 = null;
		var txt = "";
		var re = /\/course_tree\.php$/ig;

		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
		txt = "<manifest></manifest>";
		xmlDocs.loadXML(txt);

		try {
			obj1 = getTarget();
			if (!re.test(obj1.location.href.toString())) {
				obj1.location.href = "./course_tree.php";
			} else {
				obj = getTarget();
				if (typeof(obj.winFolderExpand) == "function") obj.winFolderExpand(true);
			}
		} catch (e) {
			obj1.location.href = "./course_tree.php";
		}
		
		// 先隱藏詳細資料的列表
		obj = document.getElementById("DetailTable");
		if (obj != null) obj.style.display = "none";
	};

	/**
     * 瀏覽檔案
     */
    function browseFile()
    {
        appPictureBrowser = window.open('/lib/app_listfiles.php?from=default', 'appPictureBrowser', 'width=380,height=400,status=0,toolbar=0,menubar=0,scrollbars=1,resizable=1');
        if (appPictureBrowser.closed === false) {
            // 已經開啟，則focus就好
            appPictureBrowser.focus();
        }
    }

    /**
     * 取得瀏覽的檔名。※※※※ 函數名稱不可改 ※※※※
     * 取得後將檔名以POST method 送出
     *
     * @param {String} filename: 檔名
     * @param {String} classify: 分類(public, private)
     */
    function setPictureFilename (filename, classify) {
        var subForm = document.getElementById('actFm');
        subForm.returnValue.value = filename;
        subForm.submit();
    }

	window.onunload = function () {
		var obj = null;
		obj = getTarget();
		if (obj != null) {
			obj.location.href = "about:blank";
		}
	};
BOF;

    /**
     * 顯示課程名稱
     *
     * @param string $caption：課程名稱(複合式資料)
     * @return string：單一語系的課程名稱
     **/
	function showSubject($caption) 
	{
		global $sysSession;
		$lang = getCaption($caption);
		$caption = $lang[$sysSession->lang];
		return $caption;
	}
	
    /**
     * 顯示圖片
     *
     * @param int $courseId
     * @param blob $picture
     * @return string：圖片的路徑
     **/
	function showImage($courseId, $picture, $mime_type)
	{
		global $tmpPath;
        // 如果$picture是空的，則不需處理
		if (empty($picture)) {
			return false;
		}

        $picture = base64_decode($picture);

		$type = str_replace('image/', '', $mime_type);
        // 製作圖片的暫存路徑
		$tmpFile = base64_encode($courseId.time()).'.'.$type;
		$realFile = sysDocumentRoot.$tmpPath.$tmpFile;
		$fp = fopen($realFile,'w');
		fwrite($fp, $picture);
		fclose($fp);

        // 回傳圖片
		return "<img src='{$tmpPath}{$tmpFile}' width='143' height='100'>";
	}

    /**
     * 顯示圖片修改的圖示
     *
     * @param int $courseId
     * @return string：修改的icon路徑與動作function
     **/
	function showImageSetting($courseId) 
	{
		global $sysSession, $MSG;

        // 將課程編號編碼
		$val = sysEncode($courseId);
		$icon = '<img title="' . $MSG['msg_modify'][$sysSession->lang] . '" alt="' . $MSG['msg_modify'][$sysSession->lang] . '" src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/icon_property.gif" width="16" height="16" border="0">';

        // 回傳圖示與操作function
		return '<a href="javascript:;" onclick="editCourse(\'' . $val . '\'); return false;" class="cssAnchor">' . $icon . '</a>';
	}

	showXHTML_head_B($MSG['title_manage'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('include', '/academic/App_course/course_list.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');
		$ary = array();
		$ary[] = array($MSG['tabs_course_list'][$sysSession->lang], 'tabs');

		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'mainFm', 'ListTable', '" style="display: inline;"');
			showXHTML_input('hidden', 'folder_id');
			showXHTML_input('hidden', 'csids');

			$myTable = new table();
			$myTable->extra = 'width="600" border="0" cellspacing="1" cellpadding="3" id="" class="cssTable"';
			if (empty($keyword)) {
				if ($gid > 10000000) {
					$ary = getParents($gid, true);
					$ary[] = $gid;
					foreach ($ary as $key => $val) {
						if ($val <= 10000000) continue;
						$data = getCourseData($val);
						$lang = getCaption($data['caption']);
						$dir .= ' > ' . $lang[$sysSession->lang];
					}
				} else {
					$dir .= ' > ' . $MSG['msg_all_course'][$sysSession->lang];
				}
			} else {
				$dir .= ' > ' . $MSG['msg_query_result'][$sysSession->lang];
			}

			// 工具列
			// 新增一個預設圖片的toolbar
			$defaultImage = new toolbar();
			// 圖片路徑
			$pictureFile = '/theme/default/app/default-course-picture.jpg';
			// 取得圖片大小
			$imageSize = getimagesize(sysDocumentRoot.$pictureFile);
			// 讓高度控制在60px左右，避免右側的說明區塊因為高度不夠而蓋到下方的文字
			$multiplier = 60 / $imageSize[1];
			$pictureWidth = $imageSize[0] * $multiplier;
			$pictureHeight = $imageSize[1] * $multiplier;
			// 變更完畢要即時更換介面上的預覽圖片
			$src = $pictureFile . '?' . time();
			// 顯示圖片
			$picture = "<img src='{$src}' width='{$pictureWidth}' height='{$pictureHeight}'  align='middle'>";
			$defaultImage->add_caption($MSG['app_course_default_image'][$sysSession->lang]);
			$defaultImage->add_caption($picture);
			// 變更的按鈕
			$defaultImage->add_input('button', '', $MSG['app_course_default_image_button_change'][$sysSession->lang], '', 'class="cssBtn" onclick="browseFile(\'course\');"');
			// 說明文字區塊
			$defaultImage->add_caption('<span style="position: absolute; padding-left: 5%;">' . $MSG['item_image_remark'][$sysSession->lang].'<br>'.
					'<font color="red">'.$MSG['msg_chars_limit'][$sysSession->lang].'</font><br>'.
					$MSG['msg_filetype_limit'][$sysSession->lang] . '</span>');
			// 將工具列加進table
			$myTable->add_toolbar($defaultImage);

			$toolbar = new toolbar();
			$toolbar->add_caption($MSG['query_course'][$sysSession->lang]);
			$txt = empty($keyword) ? $MSG['query_string'][$sysSession->lang] : $keyword;
			$toolbar->add_input('text', 'queryTxt', $txt, '', 'id="queryTxt" class="cssInput" onmouseover="this.focus(); this.select();"');
			$toolbar->add_input('button', '', $MSG['btn_ok'][$sysSession->lang], '', 'class="cssBtn" onclick="queryCourse(event)"');
			$myTable->add_toolbar($toolbar);

			$sel = new toolbar();
			$sel->add_caption($MSG['query_image_status'][$sysSession->lang]);
			$sel->add_caption("<select name='imageStatus' id='imageStatus'  class='cssInput' onchange=changeStatus();>");
			$imageSettingStatus = array($MSG['image_setting_status_all'][$sysSession->lang],
									    $MSG['image_setting_status_set'][$sysSession->lang],
									    $MSG['image_setting_status_nonset'][$sysSession->lang]);
			for($i=0;$i<count($imageSettingStatus);$i++) {
				if (trim($queryImageStatus)==$i) {
					$selected = " selected=\'selected\'";
				} else {
				   $selected ='';
				}
				$sel->add_caption(" <option value='".$i."' $selected >".$imageSettingStatus[$i]."</option>");
			}
			$sel->add_caption("</select>");
			$myTable->add_toolbar($sel);

			// 翻頁
			$myTable->display['page_func'] = 'rmUnload("page"); return true';
			// 資料
			$myTable->add_field($MSG['td_course_name'][$sysSession->lang]  , '', 'caption'    , '%caption'   , 'showSubject'   , 'width="40%" wrap');
			$myTable->add_field($MSG['td_study'][$sysSession->lang]        , '', 'study_date' , '%st_begin %st_end', 'showDatetime'  , 'nowrap="noWrap"');
			$myTable->add_field($MSG['td_image'][$sysSession->lang]       , '', ''           , '%course_id %picture %mime_type'  , 'showImage'     , 'align="center"');
			$myTable->add_field($MSG['td_image_setting'][$sysSession->lang]       , '', ''           , '%course_id'   , 'showImageSetting', 'align="center" nowrap="noWrap"');

            // 查詢條件：課程關鍵字
			if(!empty($keyword)) {
				$qtxt   = trim($keyword);
				$qtxt   = escape_LIKE_query_str($qtxt);
				$cond[] = ' a.caption like ' . $sysConn->qstr("%{$qtxt}%", get_magic_quotes_gpc());
			}

            /**
             * 查詢條件：圖片是否設定
             **/
			if($queryImageStatus==1)
				$cond[] = ' b.picture is not null';
			if($queryImageStatus==2)
				$cond[] = ' b.picture is null';
			
            // 將查詢條件組成字串
			if(count($cond)>0)
				$conditions = ' and '.implode(' and ', $cond);

			if ($gid <= 10000000) {
                // 未選擇課程群組
				$table  = 'WM_term_course as a left JOIN CO_course_picture as b on a.course_id=b.course_id';
				$fields = 'a.course_id,a.caption,a.st_begin,a.st_end,b.picture,b.mime_type';
				$where  = "a.`course_id`!=10000000 AND a.`kind`='course' AND a.`status`<9 {$conditions} order by a.`course_id`";
			} else {
                // 有選擇課程群組
				$table  = 'WM_term_group as c LEFT JOIN `WM_term_course` as a ON c.`child`=a.`course_id` left join CO_course_picture as b on a.course_id=b.course_id';
				$fields = 'a.course_id,a.caption,a.st_begin,a.st_end,b.picture,b.mime_type';
				$where  = "c.`parent`={$gid} AND a.`kind`='course' AND a.`status`<9 {$conditions} order by a.`course_id`";
			}

			$myTable->set_sqls($table, $fields, $where);
			$myTable->show();
			$_POST['page']   = $myTable->get_page();
			$_POST['sortby'] = $myTable->display['sort'];
		showXHTML_tabFrame_E();
		echo '</div>';

        // 課程列表的submit form
		$ticket  = preg_replace('![^\w+/=]!', '', $_POST['ticket']);
		$page    = intval($_POST['page']);
		showXHTML_form_B('action="' . $_SERVER['PHP_SELF'] . '" method="post" style="display: none;"', 'actFm');
			showXHTML_input('hidden', 'page'   , $page);
			showXHTML_input('hidden', 'ticket' , $ticket);
			showXHTML_input('hidden', 'keyword', $keyword);
			showXHTML_input('hidden', 'imageStatus' , $queryImageStatus);
            showXHTML_input('hidden', 'returnValue' , '', '', '');
		showXHTML_form_E();

        // 變更圖片設定的submit form
        $ticket = md5($sysSession->school_id . $sysSession->school_name . 'Edit' . $sysSession->username);
        showXHTML_form_B('action="course_property.php" method="post" enctype="multipart/form-data" style="display:none"', 'editFm');
            showXHTML_input('hidden', 'ticket', $ticket);
            showXHTML_input('hidden', 'course_id'  , 0);
        showXHTML_form_E('');

	showXHTML_body_E('');
?>
