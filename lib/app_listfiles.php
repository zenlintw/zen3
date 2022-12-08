<?php
    /**
     * 檔案瀏覽
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
    require_once(sysDocumentRoot . '/lang/app_course_manage.php');

    $aryFrom =  array('course', 'activity', 'app', 'default');
    if (!isset($_COOKIE['idx']) || $sysSession->username == 'guest' || !in_array($_GET['from'],$aryFrom)) {
        // 沒有登入 = 沒有idx，帳號也會是guest；此外from若非限定，則停止運作
        die('Access Denied.');
    }
    
    /**
     * 檢查檔案大小是否超過限制
     *
     * @return bool, true(超過)、false(未超過)
     **/
    function detectUploadSizeExceed($limit = 400)// 單位：KB。Default：400K
    {
        global $_POST, $_FILES;

        if ($_SERVER['CONTENT_LENGTH'] > $limit*1024) {
            return true;
        }
        return false;
    }

    function url_base64_encode($str)
    {
        return str_replace('+', '%252B', base64_encode($str));
    }


    /**
     * 取得目前目錄所有項目
     * return array [0]=所有目錄 [1]=所有檔案 (未排序)
     */
    function getAllEntry($dir){
        $entries = array(array(), array());
        if (is_dir($dir) && is_readable($dir)){
            if($dp = opendir($dir)){
                while(($item = readdir($dp)) !== false){
                    if (is_file($dir . $item))
                        $entries[1][] = $item;
                    elseif(is_dir($dir . $item) && !preg_match('/^\.\.?$/', $item))
                        $entries[0][] = $item;
                }
                closedir($dp);
            }
            else
                return FALSE;
        }
        return $entries;
    }
    
    $sysSession->cur_func = '1200200100';
    $sysSession->restore();
    if (!aclVerifyPermission(1200200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }

    if($_GET['from']==='course') {
        $basePath = sysDocumentRoot . '/base/'.$sysSession->school_id.'/door/APP/course_repos';
        $displayPath = '/APP/course_repos/';
        $userBasePath = sysDocumentRoot . sprintf('/user/%1s/%1s/%s/app/', substr($sysSession->username, 0, 1), substr($sysSession->username, 1, 1), $sysSession->username);
        if (!is_dir($userBasePath)) {
            mkdir($userBasePath, 0700);
        }
        $userDisplayPath = sprintf('/user/%1s/%1s/%s/app/', substr($sysSession->username, 0, 1), substr($sysSession->username, 1, 1), $sysSession->username);
    } else if($_GET['from']==='activity') {
        $basePath = sysDocumentRoot . '/base/'.$sysSession->school_id.'/door/APP/advs';
        $displayPath = '/APP/advs/';
    } else if($_GET['from']==='app') {
    	$basePath = sysDocumentRoot . '/base/'.$sysSession->school_id.'/door/APP/home';
    	$displayPath = '/APP/home/';
    } else if ($_GET['from'] === 'default') {
        $basePath = sysDocumentRoot . '/theme/default/app/';
        $displayPath = 'app';
    }

    $currPath = preg_replace(array('!\.\./!', '!/+!'),
                             array('','/'),
                             '/' . base64_decode($_GET['P']) . '/'
                            );

    if ($_FILES['upload_file'] && is_uploaded_file($_FILES['upload_file']['tmp_name']))
    {
        $filenameRule = '/^[a-zA-Z0-9-_]+\.[a-zA-Z]{3,4}$/';
        $location = 'app_listfiles.php?from='.$_GET['from'].'&device='.$_GET['device'];
        
        if($_GET['from']!=='app') {
        	$allowFileType = array('gif', 'jpeg', 'jpg', 'png', 'bmp');
        	$alertMsg = $MSG['msg_filetype_limit'][$sysSession->lang];
        } else {
        	$allowFileType = array('png', 'jpeg', 'jpg');
        	$alertMsg = $MSG['msg_filetype_png_jpg_limit'][$sysSession->lang];
        }

        $filetype = $_FILES['upload_file']['type'];
        $filetypeFlag = false;
        for($i = 0; $i < count($allowFileType); $i++) {
        	$thisFileType = $allowFileType[$i];
        	if(strstr($filetype, $thisFileType)) {
        		$filetypeFlag = true;
                $uploadFileType = $thisFileType;
        		continue;
        	}
        }
        if (!$filetypeFlag) {
            echo  <<< EOB
            <html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	            <script>
	                alert('{$alertMsg}');
	                location.replace('{$location}');
	            </script>
	        </head>
            <body/>
            </html>
EOB;
        	die();
        }
	    if (!preg_match($filenameRule, $_FILES['upload_file']['name'])) {
            $alertMsg = $MSG['msg_chars_limit'][$sysSession->lang];

            echo  <<< EOB
            <html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	            <script>
	                alert('{$alertMsg}');
	                location.replace('{$location}');
	            </script>
            </head>
            <body/>
            </html>
EOB;
        	die();
		}
        // 偵測檔名是否不合法：不得有".."，也不得有正反斜線
        if((preg_match('/\.\.+/',$filename)) || (preg_match('/[\/\\\\]{2,}/',$_FILES['upload_file']['name']))) {
            $alertMsg = $MSG['msg_alert_filename'][$sysSession->lang];

            echo <<< EOB
            <html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	            <script>
	                alert('{$alertMsg}');
	                location.replace('{$location}');
	            </script>
	        </head>
            <body/>
            </html>
EOB;
            die();
        }
        // 偵測檔案大小是否超過限制，若超過則顯示『上傳失敗，檔案大小超過限制！』的訊息
        if (($_GET['from']==='course') && (detectUploadSizeExceed(400))) {
            $alertMsg = $MSG['msg_alert_filesize'][$sysSession->lang];
            $location = 'app_listfiles.php?from='.$_GET['from'];
            echo <<< EOB
            <html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	            <script>
	                alert('{$alertMsg}');
	                location.replace('{$location}');
	            </script>
            </head>
            <body/>
            </html>
EOB;
            die();
        }
        
        // 上傳檔案，偵測是『課程圖片』還是『活動看板』
        $returnValue = $_FILES['upload_file']['name'];
        if($_GET['from']==='course') {
            $newFilename = date('YmdHis',time()).'.'.$uploadFileType;
            $fileDestination = $userBasePath . $newFilename;
        } else if($_GET['from']==='activity') {
            $fileDestination = sprintf('%s/base/%05d/door/APP/advs/%s',
                                        sysDocumentRoot,
                                        $sysSession->school_id,
                                        $_FILES['upload_file']['name']
                                    );
        } else if($_GET['from']==='app') {
            $appTmpFilePath = sprintf('%s/base/%05d/door/APP/home/tmp/',
                                       sysDocumentRoot,
                                       $sysSession->school_id);
            $fileDestination = $appTmpFilePath . $_FILES['upload_file']['name'];
            // 檢查暫存目錄如果不存在，則建立一個出來
            if (!is_dir($appTmpFilePath)) {
            	mkdir($appTmpFilePath, 0700);
            }
            $newFilename = $_FILES['upload_file']['name'].'#'.$_GET['device'];
        } else if($_GET['from'] === 'default') {
            $fileDestination = sysDocumentRoot . '/theme/default/app/default-course-picture.jpg';
            $newFilename = 'default-course-picture.jpg';
        }
        if (move_uploaded_file($_FILES['upload_file']['tmp_name'],$fileDestination)) {
            die("
<script>
    window.onload = function() {
        opener.setPictureFilename('/{$newFilename}', 'private');
        self.close();
    };
</script>");
        }
    }
$js = <<< BOF
var MSG_DELETE_CONFIRM = "{$MSG['msg_confirm_delete'][$sysSession->lang]}";
var MSG_DELETE_FAIL_NO_DIR = "{$MSG['msg_delete_fail_no_dir'][$sysSession->lang]}";
var MSG_DELETE_FAIL_NO_FILE = "{$MSG['msg_delete_fail_no_file'][$sysSession->lang]}";
var MSG_DELETE_FAIL = "{$MSG['msg_delete_fail'][$sysSession->lang]}";
var MSG_DELETE_SUCCESS = "{$MSG['msg_delete_success'][$sysSession->lang]}";

$(document).ready(function(){
    if ($.browser.msie) { 
        $(".courseImageDeleteBtn").css('background', 'transparent');
        $(".courseImageBtn").css('background', 'transparent');
    } else {
        $(".courseImageDeleteBtn").css('background-size', 'cover');
        $(".courseImageBtn").css('background-size', 'cover');
    }
});

/**
 * 檢查有無選取檔案
 **/
function checkData()
{
	var file = $(':file')[0].value;
	if (file.length==0) {
		return false;
	}
	var submitForm = document.getElementById('upForm');
	submitForm.submit();
}
/**
 * 將檔名與分類回傳
 * @param {String} filename 檔名
 * @param {String} classify 分類(public, private)
 **/
function setPictureFile (filename, classify) {
    opener.setPictureFilename(filename, classify);
}

/**
 * 切換頁籤
 **/
function chgTab(n) {
    if (n === 1) {
        top.$('#appPictureList').show();
    } else if (n === 2){
        top.$('#appPictureList').hide();
    }
}

$(document).ready (
    function () {
        // 點選圖片，會回傳檔案資料
        $(".app-course-image-button").click(function() {
           opener.setPictureFilename($(this).attr('btn-value'), 'private');
        });

        // 刪除圖片
        $(".courseImageDeleteBtn").click(function() {
            if (confirm(MSG_DELETE_CONFIRM)) {
                var xmlDoc = null, txt, filename, msg;

                filename = $(this).attr('btn-value');

                if (filename.length === 0) {
                    return false;
                }

                if ((typeof(xmlHttp) === "undefined") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
                if ((typeof(xmlVar) === "undefined") || (xmlDoc === null)) xmlVar = XmlDocument.create();

                txt = "<manifest>";
                txt += "<filename>" + filename + "</filename>";
                txt += "</manifest>";

                xmlHttp = XmlHttp.create();
                xmlVar.loadXML(txt);
                xmlHttp.open("POST", "../../lib/app_course_picture_remove.php", false);
                xmlHttp.send(xmlVar);

                switch (xmlHttp.responseText) {
                case 'success':
                    // 刪除成功
                    msg = MSG_DELETE_SUCCESS;
                    break;
                case 'noDir':
                    // 沒有使用者目錄
                    msg = MSG_DELETE_FAIL_NO_DIR;
                    break;
                case 'noFile':
                    // 找不到圖片檔案
                    msg = MSG_DELETE_FAIL_NO_FILE;
                    break;
                default:
                    // 刪除失敗
                    msg = MSG_DELETE_FAIL;
                    break;
                }

                alert(msg);
                if (xmlHttp.responseText === 'success') {
                    $(this).parent().remove();
                }
            } else {
                return false;
            }
        });
    }
);
BOF;

    showXHTML_head_B('');
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_head_E();
    showXHTML_body_B('');
    showXHTML_CSS('include', '/lib/jquery/css/jquery-ui-1.8.22.custom.css');
	showXHTML_CSS('include', '/theme/default/app/style.css');
    showXHTML_script('include', '/lib/xmlextras.js');
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('include', '/lib/jquery/jquery-ui-1.8.22.custom.min.js');
    showXHTML_script('inline', $js);
    if (dirname($_SERVER['PHP_SELF']) == '/lib')
    {
    	// 根據課程圖片或活動看板顯示標籤
    	if ($_GET['from']==='course') {
    		$tabTitle = $MSG['tab_course_picture_public'][$sysSession->lang];
    		$tabUpload = $MSG['tab_course_picture_private'][$sysSession->lang];
    	} else if ($_GET['from']==='activity') {
    		$tabTitle = $MSG['tab_title_activity'][$sysSession->lang];
    		$tabUpload = $MSG['tab_title_activity'][$sysSession->lang].$MSG['msg_upload_file'][$sysSession->lang];
    	}
        $ary = array(array($tabTitle , 'tabsSet1', 'chgTab(1);'),
                     array($tabUpload, 'tabsSet2', 'chgTab(2);'));
    }
    echo "<center>\n";
    if($_GET['from'] !=='app' && $_GET['from'] !== 'default') {
    	showXHTML_tabFrame_B($ary, 1, 'upForm', 'upTable', 'method="POST" enctype="multipart/form-data" style="display: inline"');
        echo '<div id="appPictureList">';
        showXHTML_table_B('id="tabsSet1" border="0" width="100%" cellpadding="3" cellspacing="1" class="cssTrEvn"');
          $all = getAllEntry($basePath . $currPath);
          if (!empty($all[0]) && sort($all[0])) foreach($all[0] as $category){
          	// TMP目錄是活動圖片暫存用，所以不要顯示出來
          	if ($category!=='TMP'){
              $cln = $cln == 'class="cssTrOdd font01"' ? 'class="cssTrEvn font01"' : 'class="cssTrOdd font01"';
            showXHTML_tr_B($cln);
              showXHTML_td('', '<img src="/theme/default/filetype/folder.gif" align="absmiddle"');
              showXHTML_td('width="300" nowrap', '<a href="' . $_SERVER['PHP_SELF'] . '?P=' . url_base64_encode($currPath . $category) . '" onmousedown="releaseUnload();" class="cssAnchor">' . htmlspecialchars(adjust_char($category) ) . '</a>');
            showXHTML_tr_E();
          	}
          }

          if (!empty($all[1]) && sort($all[1])) {
              for ($i = 0; $i < count($all[1]); $i++) {
                  $returnValue = (isset($content_ref)?"/$baseUri":'') . str_replace("'", "\\'", adjust_char($currPath . $all[1][$i]));
                  $filename = sprintf('/base/%5d/door/APP/course_repos/%s', $sysSession->school_id, $returnValue);
                  if (is_file(sysDocumentRoot . $filename)) {
                      $pictureFile = '<span style="width="150" height="105""><img src="' . $filename . '" style="float:center; padding:5px;" width="143" height="100"></span>';
                      $imagePreview = '<a href="javascript:opener.setPictureFilename(\'' . $returnValue . '\', \'public\'); self.close();" class="cssAnchor">' . $pictureFile . '</a>';
                      echo $imagePreview;
                  }
              }
          }
        showXHTML_table_E();
        echo '</div>';

        echo '<div id="appPictureUpload">';
        showXHTML_table_B('id="tabsSet2" border="0" width="100%" cellpadding="3" cellspacing="1" style="display: none" class="cssTrEvn"');
          showXHTML_tr_B('class="cssTrEvn font01"');
            showXHTML_td_B('width="100%"');
              showXHTML_input('file', 'upload_file', '', '', 'style="width:100%" class="cssInput"');
            showXHTML_td_E();
          showXHTML_tr_E();
          showXHTML_tr_B('class="cssTrOdd font01"');
            showXHTML_td_B('width="100%"');
              showXHTML_input('button', '', $MSG['msg_upload_file'][$sysSession->lang], '', 'class="cssBtn" onclick=checkData();');
              echo '<div style="margin:5px; border-width:4px 0 4px 0; border-style:double; border-color:gray">'.$MSG['item_image_remark'][$sysSession->lang].'<br>'.'<font color="red">'.$MSG['msg_chars_limit'][$sysSession->lang].'</font><br>'.$MSG['msg_filetype_limit'][$sysSession->lang].'</div>';
          showXHTML_tr_E();
        showXHTML_tr_B('class="cssTrHead font01"');
            showXHTML_td_B('width="100%" colspan="2"');
        $userAll = getAllEntry($userBasePath . $currPath);
        if (!empty($userAll[1]) && sort($userAll[1])) {
            $deleteFile = '/theme/default/app/course-picture-delete.png';
            echo '<div>';
            for ($i = 0; $i < count($userAll[1]); $i++) {
                $returnValue = (isset($content_ref)?"/$baseUri":'') . str_replace("'", "\\'", adjust_char($currPath . $userAll[1][$i]));
                $filename = $userBasePath.$returnValue;
                if (is_file($filename)) {
                    $deleteButton = '<input type="button" class="courseImageDeleteBtn" btn-value="'. $returnValue .'" style="filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' . $deleteFile . '\',sizingMethod=\'scale\'); background:url(' . $deleteFile . ') no-repeat scroll 0 0 transparent;">'; //onclick=deleteFile("'.$returnValue.'");
                    $srcName = $userDisplayPath.$returnValue;
                    $courseImageBtnStyle = 'width: 143px; height: 100px; background:url(' . $srcName . ') no-repeat center;';
                    $courseImageButton = '<input type="button" class="app-course-image-button" type-value="private" btn-value="'. $returnValue .'" style="' . $courseImageBtnStyle . '">';
                    echo '<div class="courseImageContainer">';
                        echo $courseImageButton;
						echo $deleteButton;
                    echo '</div>';
                }
            }
            echo '</div>';
        }
            showXHTML_td_E('');
        showXHTML_tr_E('');
        showXHTML_table_E();
        showXHTML_tabFrame_E();

        echo '</div>';
    } else {
        if ($_GET['from'] === 'app') {
            $tabUpload = $MSG['tab_app'][$sysSession->lang].$MSG['msg_upload_file'][$sysSession->lang];
        } else {
            $tabUpload = $MSG['msg_upload_file'][$sysSession->lang];
        }
    	$aryUpload[] = array($tabUpload, 'tabs1');
    	showXHTML_tabFrame_B($aryUpload, 1, 'upForm', 'upTable', 'method="POST" enctype="multipart/form-data" style="display: inline"');
        showXHTML_table_B('id="upload" border="0" width="100%" cellpadding="3" cellspacing="1" class="cssTable"');
          showXHTML_tr_B('class="cssTrEvn font01"');
            showXHTML_td_B('width="350"');
              showXHTML_input('file', 'upload_file', '', '', 'size="40" class="cssInput"');
            showXHTML_td_E();
          showXHTML_tr_E();
          showXHTML_tr_B('class="cssTrOdd font01"');
            showXHTML_td_B('width="350"');
              showXHTML_input('button', '', $MSG['msg_upload_file'][$sysSession->lang], '', 'class="cssBtn" onclick=checkData();');
              echo '<font color="red">'.$MSG['msg_chars_limit'][$sysSession->lang].'</font>';
            showXHTML_td_E();
          showXHTML_tr_E();
        showXHTML_table_E();

      showXHTML_tabFrame_E();
    }
    showXHTML_body_E();
?>