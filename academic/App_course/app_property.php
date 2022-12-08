<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/lstable.php');
    require_once(sysDocumentRoot . '/lang/app_course_manage.php');
    require_once(sysDocumentRoot . '/academic/course/course_lib.php');

    /**
     * 取得圖檔的MIME TYPE
     *
     * @param string $filePath 圖檔的實際路徑
     * @return string 圖檔的MIME TYPE
     */
    function getImageFileType ($filePath) {
        preg_match("|\.([a-z0-9]{2,4})$|i", $filePath, $fileSuffix);
        switch (strtolower($fileSuffix[1])) {
            case 'jpg' :
            case 'jpeg' :
            case 'jpe' :
                return 'jpg';
            case 'png' :
                return strtolower($fileSuffix[1]);
        }
    }

    /**
     * 取得圖檔名稱
     *
     * @param string $kind iphone、ipad或是logo
     * @return string 圖檔在os下的完整路徑
     */
    function getImageFilename ($kind) {
        global $sysSession;
        $image = glob(sprintf(sysDocumentRoot . '/base/%5d/door/APP/home/' . $kind . '.*', $sysSession->school_id));
        $filename = str_replace(sysDocumentRoot, '', $image[0]);

        return $filename;
    }

    // 從上傳檔案收到的參數
    if (!empty($_POST['returnValue'])) {
    	$returnValue = trim($_POST['returnValue']);
    	$aryValue = explode('#',$returnValue);
    	$filename = $aryValue[0];
    	$device = $aryValue[1];
    }

    // 處理 iPhone的檔案名稱
    if ($device==='iphone') {
    	$getIPhoneFilename = $filename;
    } else if (!empty($_POST['iphone_filename'])) {
    	$getIPhoneFilename = trim($_POST['iphone_filename']);
    } else {
    	$getIPhoneFilename = null;
    }
    
    // 處理 iPad的檔案名稱
    if ($device==='ipad') {
    	$getIPadFilename = $filename;
    } else if (!empty($_POST['ipad_filename'])) {
    	$getIPadFilename = trim($_POST['ipad_filename']);
    } else {
    	$getIPadFilename = null;
    }
    
    // 處理 logo的檔案名稱
    if ($device==='logo') {
    	$getLogoFilename = $filename;
    } else if (!empty($_POST['logo_filename'])) {
    	$getLogoFilename = trim($_POST['logo_filename']);
    } else {
    	$getLogoFilename = null;
    }

    // 處理 中央logo的檔案名稱
    if ($device==='background-logo') {
        $getBackgroundLogoFilename = $filename;
    } else if (!empty($_POST['background_logo_filename'])) {
        $getBackgroundLogoFilename = trim($_POST['background_logo_filename']);
    } else {
        $getBackgroundLogoFilename = null;
    }

    $actionType = (empty($_POST['actionType']))? 'preview' : trim($_POST['actionType']);
    $actualPath = sprintf('/base/%5d/door/APP/home/',$sysSession->school_id);

    // 有收到圖檔名稱，則去取目錄下的圖檔
    if(!is_null($getIPhoneFilename)) {
        // 將取到的檔名做".."或"斜線"的字串轉換
        $getIPhoneFilename = preg_replace(
            array('/\.\.+/', '/[\/\\\\]{2,}/'),
            array('', '/'),
            $getIPhoneFilename
        );

        // 取得上傳圖檔的副檔名
        $fileType = getImageFileType($getIPhoneFilename);

        // 網頁實際讀取的檔案路徑
        $pictureIPhoneFile = sprintf('/base/%5d/door/APP/home/tmp/%s',
                                $sysSession->school_id,$getIPhoneFilename);

        if(!is_file(sysDocumentRoot.$pictureIPhoneFile)) {
            // 如果轉換檔名後發現找不到檔案，則顯示錯誤訊息
            echo <<< EOB
            <script>
                alert('iPhone:'+'{$MSG['msg_alert_select'][$sysSession->lang]}');
            </script>
EOB;
            $pictureIPhoneFile=null;
        }
        
        // 儲存圖片檔案
        if ($actionType==='save') {
            // 先刪除就的splash-iphone.*檔案
            unlink(sysDocumentRoot . getImageFilename('splash-iphone'));

        	// 將圖檔從暫存區複製至正式區
        	copy(sysDocumentRoot.$pictureIPhoneFile, sysDocumentRoot.$actualPath.'/splash-iphone.'.$fileType);
        	$pictureIPhoneFile = getImageFilename('splash-iphone');
        	$getIPhoneFilename='';
        }
    } else {
        $pictureIPhoneFile = getImageFilename('splash-iphone');
    }

    $imageSize = getimagesize(sysDocumentRoot.$pictureIPhoneFile);
	$pictureWidth = $imageSize[0]*0.2;
	$pictureHeight = $imageSize[1]*0.2;
    $src = $pictureIPhoneFile . '?' . time();
    $iPhonePicture = "<img src='{$src}' width='{$pictureWidth}' height='{$pictureHeight}'>";
    
    // 有收到圖檔名稱，則去取目錄下的圖檔
    if(!is_null($getIPadFilename)) {
        // 將取到的檔名做".."或"斜線"的字串轉換
        $getIPadFilename = preg_replace(
            array('/\.\.+/', '/[\/\\\\]{2,}/'),
            array('', '/'),
            $getIPadFilename
        );

        // 取得上傳圖檔的副檔名
        $fileType = getImageFileType($getIPadFilename);

        // 網頁實際讀取的檔案路徑
        $pictureIPadFile = sprintf('/base/%5d/door/APP/home/tmp/%s',
                                $sysSession->school_id,$getIPadFilename);

        if(!is_file(sysDocumentRoot.$pictureIPadFile)) {
            // 如果轉換檔名後發現找不到檔案，則顯示錯誤訊息
            echo <<< EOB
            <script>
                alert('iPad:'+'{$MSG['msg_alert_select'][$sysSession->lang]}');
            </script>
EOB;
            $getIPadFilename=null;
        }
        
        // 儲存圖片檔案
    	if ($actionType==='save') {
            // 先刪除就的splash-iphone.*檔案
            unlink(sysDocumentRoot . getImageFilename('splash-ipad'));

    		// 將圖檔從暫存區複製至正式區
        	copy(sysDocumentRoot.$pictureIPadFile, sysDocumentRoot.$actualPath.'/splash-ipad.'.$fileType);
        	$pictureTmpIPadFile = $pictureIPadFile;
            $pictureIPadFile = getImageFilename('splash-ipad');
        	$getIPadFilename='';
        }
    } else {
        $pictureIPadFile = getImageFilename('splash-ipad');
    }

    $imageSize = getimagesize(sysDocumentRoot.$pictureIPadFile);
	$pictureWidth = $imageSize[0]*0.2;
	$pictureHeight = $imageSize[1]*0.2;
    $src = $pictureIPadFile . '?' . time();
    $iPadPicture = "<img src='{$src}' width='{$pictureWidth}' height='{$pictureHeight}'>";
    
    // 有收到圖檔名稱，則去取目錄下的圖檔
    if(!is_null($getLogoFilename)) {
        // 將取到的檔名做".."或"斜線"的字串轉換
        $getLogoFilename = preg_replace(
            array('/\.\.+/', '/[\/\\\\]{2,}/'),
            array('', '/'),
            $getLogoFilename
        );

        // 取得上傳圖檔的副檔名
        $fileType = getImageFileType($getLogoFilename);


        // 網頁實際讀取的檔案路徑
        $pictureLogoFile = sprintf('/base/%5d/door/APP/home/tmp/%s',
                                $sysSession->school_id,$getLogoFilename);

        if(!is_file(sysDocumentRoot.$pictureLogoFile)) {
            // 如果轉換檔名後發現找不到檔案，則顯示錯誤訊息
            echo <<< EOB
            <script>
                alert('Logo:'+'{$MSG['msg_alert_select'][$sysSession->lang]}');
            </script>
EOB;
            $getLogoFilename=null;
        }
        
        // 儲存圖片檔案
    	if ($actionType==='save') {
            // 先刪除就的splash-iphone.*檔案
            unlink(sysDocumentRoot . getImageFilename('logo'));

    		// 將圖檔從暫存區複製至正式區
        	copy(sysDocumentRoot.$pictureLogoFile, sysDocumentRoot.$actualPath.'/logo.'.$fileType);
        	$pictureTmpLogoFile = $pictureLogoFile;
            $pictureLogoFile = getImageFilename('logo');
        	$getLogoFilename='';
        }
    } else {
        $pictureLogoFile = getImageFilename('logo');
    }

    $imageSize = getimagesize(sysDocumentRoot.$pictureLogoFile);
	$pictureWidth = $imageSize[0]*0.5;
	$pictureHeight = $imageSize[1]*0.5;
    $src = $pictureLogoFile . '?' . time();
    $logoPicture = "<img src='{$src}' width='{$pictureWidth}' height='{$pictureHeight}'>";

    // 中央logo有開啟
    if (sysEnableAppBackgroundLogo) {
        // 有收到圖檔名稱，則去取目錄下的圖檔
        if(!is_null($getBackgroundLogoFilename)) {
            // 將取到的檔名做".."或"斜線"的字串轉換
            $getBackgroundLogoFilename = preg_replace(
                array('/\.\.+/', '/[\/\\\\]{2,}/'),
                array('', '/'),
                $getBackgroundLogoFilename
            );

            // 取得上傳圖檔的副檔名
            $fileType = getImageFileType($getBackgroundLogoFilename);


            // 網頁實際讀取的檔案路徑
            $pictureBackgroundLogoFile = sprintf('/base/%5d/door/APP/home/tmp/%s',
                $sysSession->school_id,$getBackgroundLogoFilename);

            if(!is_file(sysDocumentRoot.$pictureBackgroundLogoFile)) {
                // 如果轉換檔名後發現找不到檔案，則顯示錯誤訊息
                echo <<< EOB
                    <script>
                        alert('Logo:'+'{$MSG['msg_alert_select'][$sysSession->lang]}');
                    </script>
EOB;
                $getBackgroundLogoFilename=null;
            }

            // 儲存圖片檔案
            if ($actionType==='save') {
                // 先刪除就的background-log.*檔案
                unlink(sysDocumentRoot . getImageFilename('background-logo'));

                // 將圖檔從暫存區複製至正式區
                copy(sysDocumentRoot.$pictureBackgroundLogoFile, sysDocumentRoot.$actualPath.'/background-logo.'.$fileType);
                $pictureTmpBackgroundLogoFile = $pictureBackgroundLogoFile;
                $pictureBackgroundLogoFile = getImageFilename('background-logo');
                $getBackgroundLogoFilename='';
            }
        } else {
            $pictureBackgroundLogoFile = getImageFilename('background-logo');
        }

        $imageSize = getimagesize(sysDocumentRoot.$pictureBackgroundLogoFile);
        $pictureWidth = $imageSize[0]*0.5;
        $pictureHeight = $imageSize[1]*0.5;
        $src = $pictureBackgroundLogoFile . '?' . time();
        $backgroundLogoPicture = "<img src='{$src}' width='{$pictureWidth}' height='{$pictureHeight}'>";

    }
    
    if ($actionType==='save') {
    	// 刪除暫存區的圖檔，並跳出儲存成功的訊息
    	$pictureTmpPath = sprintf('/base/%5d/door/APP/home/tmp',$sysSession->school_id);
        if (is_dir(sysDocumentRoot.$pictureTmpPath)) {
            exec('rm -rf '.sysDocumentRoot.$pictureTmpPath.'/*');
        }
	    echo <<< EOB
	    <script>
	    	alert('{$MSG['msg_save_success'][$sysSession->lang]}');
	    </script>
EOB;
    }
$js = <<< BOF
    var appPictureBrowser;

    /**
     * 取得瀏覽的檔名。※※※※ 函數名稱不可改 ※※※※
     * 取得後將檔名以POST method 送出
     *
     * @param {String} filename: 檔名
     * @param {String} classify: 分類(public, private)
     */
    function setPictureFilename (filename, classify) {
        var subForm = document.getElementById('submitForm');
        subForm.returnValue.value = filename;
        subForm.actionType.value = 'preview';
        subForm.submit();
    }

    /**
     * 瀏覽檔案
     */
    function browseFile(device)
    {
        appPictureBrowser = window.open('/lib/app_listfiles.php?from=app'+'&device='+device, 'appPictureBrowser', 'width=380,height=400,status=0,toolbar=0,menubar=0,scrollbars=1,resizable=1');
        if (appPictureBrowser.closed === false) {
            // 已經開啟，則focus就好
            appPictureBrowser.focus();
        }
    }

    /**
     * 儲存圖片
     */
    function save() 
    {
        var subForm = document.getElementById('submitForm');
        subForm.actionType.value = 'save';
        subForm.submit();
    }
    
    /**
	 * 取消
	 */
    function cancel() 
    {
		window.location="app_property.php";
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
header('Cache-control: no-cache, no-store, private, must-revalidate, post-check=0, pre-check=0');
showXHTML_head_E();
showXHTML_body_B();
    $ary = array();
    $ary[] = array($MSG['tab_app'][$sysSession->lang], 'tabs1');
    echo '<div align="center">';
    showXHTML_tabFrame_B($ary, 1,'propertyFrame','','style="display: inline"'); //, form_id, table_id, form_extra, isDragable);
        showXHTML_table_B('width="900" border="0" cellspacing="1" cellpadding="3" id="dataTb" class="cssTable"');
            showXHTML_tr_B('class="cssTrHead"');
                showXHTML_td('align="center"',$MSG['item'][$sysSession->lang]);
                showXHTML_td('align="center"',$MSG['td_image_setting'][$sysSession->lang]);
                showXHTML_td('align="center"',$MSG['item_remark'][$sysSession->lang]);
            showXHTML_tr_E();
            // iPhone
            $cssTR = ($cssTR == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($cssTR);
                showXHTML_td('align="right"',$MSG['item_iphone'][$sysSession->lang]);
                showXHTML_td_B('align="left" nowrap');
                echo '<span>'.$iPhonePicture.'</span>';
                showXHTML_input('button', '', $MSG['btn_image_browse'][$sysSession->lang], '', 'class="button01" onclick="browseFile(\'iphone\');"');
                showXHTML_td('align="left"',$MSG['item_iphone_remark'][$sysSession->lang]);
            showXHTML_tr_E();
            // iPad
            $cssTR = ($cssTR == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($cssTR);
                showXHTML_td('align="right"',$MSG['item_ipad'][$sysSession->lang]);
                showXHTML_td_B('align="left" nowrap');
                echo '<span>'.$iPadPicture.'</span>';
                showXHTML_input('button', '', $MSG['btn_image_browse'][$sysSession->lang], '', 'class="button01" onclick="browseFile(\'ipad\');"');
                showXHTML_td('align="left"',$MSG['item_ipad_remark'][$sysSession->lang]);
            showXHTML_tr_E();
            // logo
            $cssTR = ($cssTR == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($cssTR);
                showXHTML_td('align="right"',$MSG['item_logo'][$sysSession->lang]);
                showXHTML_td_B('align="left" nowrap');
                echo '<span>'.$logoPicture.'</span>';
                showXHTML_input('button', '', $MSG['btn_image_browse'][$sysSession->lang], '', 'class="button01" onclick="browseFile(\'logo\');"');
                showXHTML_td('align="left"',$MSG['item_logo_remark'][$sysSession->lang]);
            showXHTML_tr_E();
            // 中央logo
            if (sysEnableAppBackgroundLogo) {
                $cssTR = ($cssTR == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($cssTR);
                    showXHTML_td('align="right"',$MSG['item_background_logo'][$sysSession->lang]);
                    showXHTML_td_B('align="left" nowrap');
                    echo '<span>'.$backgroundLogoPicture.'</span>';
                    showXHTML_input('button', '', $MSG['btn_image_browse'][$sysSession->lang], '', 'class="button01" onclick="browseFile(\'background-logo\');"');
                    showXHTML_td('align="left"',$MSG['item_background_logo_remark'][$sysSession->lang]);
                showXHTML_tr_E();
            }
            $cssTR = ($cssTR == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B('class="cssTrHead"');
                showXHTML_td_B('align="center" colspan="3"');
                showXHTML_input('button', 'btnAgree', $MSG['btn_app_save'][$sysSession->lang], '', "onclick='save();'");
                showXHTML_input('button', 'btnDeny' , $MSG['btn_cancel'][$sysSession->lang] , '', "onclick='cancel()'");
                showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_table_E();
    showXHTML_tabFrame_E();
    echo '</div>';
    showXHTML_form_B('method="post" action="app_property.php" enctype="multipart/form-data"', 'submitForm');
        showXHTML_input('hidden', 'returnValue', '', '', '');
        showXHTML_input('hidden', 'ipad_filename', $getIPadFilename, '', '');
        showXHTML_input('hidden', 'iphone_filename', $getIPhoneFilename, '', '');
        showXHTML_input('hidden', 'logo_filename', $getLogoFilename, '', '');
        showXHTML_input('hidden', 'background_logo_filename', $getBackgroundLogoFilename, '', '');
        showXHTML_input('hidden', 'actionType', '', '', '');
    showXHTML_form_E();
showXHTML_body_E();
?>