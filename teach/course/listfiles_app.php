<?php
    /**
     * 檔案瀏覽
     * $Id: listfiles_app.php,v 1.1 2009-06-25 09:27:41 edi Exp $
     *
     * define('fileCURRDIR', TRUE); = 只限本目錄下的檔案 (不能切換到其它目錄)
     * define('fileFOLDER', TRUE);  = 選目錄
     * define('fileCURRDIR', TRUE); = 只限本目錄下的檔案
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/file_api.php');
    require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
    require_once(sysDocumentRoot . '/lang/teach_statistics.php');
    if (defined('ADM_EXPERIENCE')) {
        include_once(sysDocumentRoot . '/lang/experience.php');
    }
    
    $sysSession->cur_func = '1200200100';
    $sysSession->restore();
    if (!aclVerifyPermission(1200200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    }

    $isContent = (basename($_SERVER['PHP_SELF']) == 'listcontent.php');

    if (!isset($baseUri)) {
        if ($isContent) {
            list($content_ref) = dbGetStSr('WM_term_course', 'content_id', "course_id={$sysSession->course_id}", ADODB_FETCH_NUM);
            if (empty($content_ref) && sysAutoGenContentDB) // 若課程無設定使用教材庫, 則到該教師個人教材庫
            {
                include_once(sysDocumentRoot . '/lib/character_class.php');
                $teachers = WMteacher::listMember($sysSession->course_id, $sysRoles['teacher']);
                if (is_array($teachers) && count($teachers) == 1 && isSet($teachers[$sysSession->username])) {
                    $content_ref = dbGetOne('WM_content_ta as TA join WM_content as CT on TA.content_id = CT.content_id',
                        'TA.content_id',
                        'TA.username="' . $sysSession->username . '"');
                }
            }
            $baseUri = sprintf('/base/%05d/content/%06u', $sysSession->school_id, $content_ref);
        } else {
            $baseUri = sprintf('/base/%05d/course/%08u/content', $sysSession->school_id, $sysSession->course_id);
        }
    }
    $basePath = sysDocumentRoot . $baseUri;

    $currPath = preg_replace(
        array('!\.\./!', '!/+!'),
        array('', '/'),
        '/' . base64_decode($_GET['P']) . '/'
    );

    // 偵測檔案大小是否超過限制，若超過則顯示『上傳失敗，檔案大小超過限制！』的訊息
    if (detectUploadSizeExceed() && defined('ADM_EXPERIENCE')) {
        $alertMsg = $MSG['msg_alert_filesize'][$sysSession->lang];
        echo <<< EOB
                <html>
                <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
                    <script>
                        alert('{$alertMsg}');
                        window.history.back()
                    </script>
                </head>
                <body/>
                </html>
EOB;
        die();
    }
    if ($_FILES['upload_file'] && is_uploaded_file($_FILES['upload_file']['tmp_name'])) {
        $dir = sprintf('%s/base/%05d/course/%08u/content/%s',
            sysDocumentRoot,
            $sysSession->school_id,
            $sysSession->course_id,
            iconv('UTF-8', $sysSession->lang, $_FILES['upload_file']['name'])
        );
        if (defined('ADM_EXPERIENCE')) {
            if (ADM_EXPERIENCE==='cover') {
            	// 若是封面圖片
                $location = 'experience_file_catalog.php';

                // 只允許gif, jpeg, png, bmp
                $allowFileType = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
                $alertMsg = $MSG['msg_filetype_limit'][$sysSession->lang];
            } else if (ADM_EXPERIENCE === 'video') {
            	// 若是URL
                $location = 'experience_file_url.php';
                
                // 只允許mov(quicktime)、avi、mp4(mpeg4)這三種格式
                $allowFileType = array('quicktime', 'avi', 'mpeg4', 'mp4', 'gif', 'jpg', 'jpeg', 'png', 'bmp');
                $alertMsg = $MSG['msg_filetype_video_limit'][$sysSession->lang];
            }
            
            // 檔案類型限制檢測
            $filetype = $_FILES['upload_file']['type'];
	        $filetypeFlag = false;
	        for($i=0;$i<count($allowFileType);$i++) {
	        	$thisFileType = $allowFileType[$i];
	        	if(strstr($filetype,$thisFileType)) {
	        		$filetypeFlag = true;
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

            // 檔名限制檢測
            $filenameRule = '/^[a-zA-Z0-9-_]+\.[a-zA-Z0-9]{3,4}$/';
            mkdirs(sprintf('%s/base/%05d/door/APP/wmmedia/%s', sysDocumentRoot, $sysSession->school_id, ADM_EXPERIENCE));
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

                $dir = sprintf('%s/base/%05d/door/APP/wmmedia/%s/%s',
                    sysDocumentRoot,
                    $sysSession->school_id,
                    ADM_EXPERIENCE,
                    mb_convert_encoding($_FILES['upload_file']['name'], 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win')
                );
        }
        if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $dir)) {
            die("
<script>
    window.onload = function() {
        if ((typeof(opener) != 'object') || (opener == null)) return false;
        if (typeof addEventListener === 'function') {
             if (typeof(opener.getReturnValue) == 'function') {
                window.addEventListener('beforeunload', opener.getReturnValue, true);
            }
        } else {
            if (typeof(opener.getReturnValue) == 'object' && opener.getReturnValue != null) {
                window.attachEvent('onunload', opener.getReturnValue);
            }
        }
        opener.setFilename('/{$_FILES['upload_file']['name']}');
        self.close();
    };
</script>");
        } else {
            wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 1, 'auto', $_SERVER['PHP_SELF'], $MSG['msg_upload'][$sysSession->lang]);
            die("
<script>
    alert('{$MSG['msg_upload'][$sysSession->lang]}');
    history.back();
</script>");
        }
    }

    function url_base64_encode($str)
    {
        return str_replace('+', '%252B', base64_encode($str));
    }


    /**
     * 取得目前目錄所有項目
     * return array [0]=所有目錄 [1]=所有檔案 (未排序)
     */
    function getAllEntry($dir)
    {
        $entries = array(array(), array());
        if (is_dir($dir) && is_readable($dir)) {
            if ($dp = opendir($dir)) {
                while (($item = readdir($dp)) !== false) {
                    if (is_file($dir . $item))
                        $entries[1][] = $item;
                    elseif (is_dir($dir . $item) && !preg_match('/^\.\.?$/', $item))
                        $entries[0][] = $item;
                }
                closedir($dp);
            } else
                return FALSE;
        }
        return $entries;
    }

    showXHTML_head_B('');
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_script('inline', "

window.onload = function() {
    if ((typeof(opener) != 'object') || (opener == null)) return false;
    if (typeof addEventListener === 'function') {
        if (typeof opener.getReturnValue == 'function') {
            window.addEventListener('beforeunload', opener.getReturnValue, true);
        }
    } else {
        if (typeof(opener.getReturnValue) == 'object' && opener.getReturnValue != null) {
            window.attachEvent('onunload', opener.getReturnValue);
        }
    }
};

function releaseUnload() {
    if ((typeof(opener) != 'object') || (opener == null)) return false;
    if (typeof removeEventListener === 'function') {
         if (typeof(opener.getReturnValue) == 'function') {
            window.removeEventListener('beforeunload', opener.getReturnValue, true);
        }
    } else {
        if (typeof(opener.getReturnValue) == 'object' && opener.getReturnValue != null) {
            window.detachEvent('onunload', opener.getReturnValue);
        }
    }
}

function checkData()
{
    var file = document.getElementById('upload_file').value;
    if (file.length==0) {
        return false;
    }
    var submitForm = document.getElementById('upForm');
    submitForm.submit();
}

");
    showXHTML_head_E();
    showXHTML_body_B();
    if (dirname($_SERVER['PHP_SELF']) == '/teach/course') {
        $ary = array(
            array($MSG['msg_course'][$sysSession->lang], 'tabsSet1', ($isContent ? 'location.replace("listfiles_app.php");' : '')),
            array($MSG['msg_course_data'][$sysSession->lang], 'tabsSet1', ($isContent ? '' : 'location.replace("listcontent.php");')),
            array($MSG['msg_upload_file'][$sysSession->lang], 'tabsSet2', '')
        );
        if ($isContent) $ary[0][1] = ''; else $ary[1][1] = '';
    } else if (defined('ADM_EXPERIENCE')) {
        $ary = array(
            array($MSG['msg_course'][$sysSession->lang], 'tabsSet1'),
            array($MSG['msg_upload_file'][$sysSession->lang], 'tabsSet2')
        );
    } else {
        $ary = array(array($MSG['msg_program'][$sysSession->lang]));
    }
    echo "<center>\n";
    showXHTML_tabFrame_B($ary, ($isContent ? 2 : 1), 'upForm', 'upTable', 'method="POST" enctype="multipart/form-data" style="display: inline"');
    showXHTML_table_B('id="tabsSet1" border="0" width="100%" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');
    showXHTML_tr_B('class="cssTr font01"');
    showXHTML_td('width="330" colspan="2" nowrap', htmlspecialchars($MSG['msg_dir'][$sysSession->lang] . adjust_char($currPath)));
    showXHTML_tr_E();
    if ($currPath != '/') {
        showXHTML_tr_B('class="cssTrOdd"');
        showXHTML_td('', '<img src="/theme/default/filetype/folder.gif" align="absmiddle"');
        showXHTML_td('width="300"', '<a href="' . $_SERVER['PHP_SELF'] . '?P=%2F" class="cssAnchor">/</a>');
        showXHTML_tr_E();
        showXHTML_tr_B('class="cssTrEvn"');
        showXHTML_td('', '<img src="/theme/default/filetype/folder.gif" align="absmiddle"');
        showXHTML_td('width="300"', '<a href="' . $_SERVER['PHP_SELF'] . '?P=' . url_base64_encode(str_replace('//', '/', dirname($currPath) . '/')) . '" class="cssAnchor">..</a>');
        showXHTML_tr_E();
    }

    $all = getAllEntry($basePath . $currPath);
    if (!empty($all[0]) && sort($all[0])) foreach ($all[0] as $category) {
        $cln = $cln == 'class="cssTrOdd font01"' ? 'class="cssTrEvn font01"' : 'class="cssTrOdd font01"';
        showXHTML_tr_B($cln);
        showXHTML_td('', '<img src="/theme/default/filetype/folder.gif" align="absmiddle"');
        // showXHTML_td('nowrap', '<span style="width: 300px; overflow: hidden"><a href="' . $_SERVER['PHP_SELF'] . '?P=' . url_base64_encode($currPath . $category) . '" onmousedown="releaseUnload();" class="cssAnchor">' . htmlspecialchars(adjust_char($category) ) . '</a></span>');
        showXHTML_td('width="300" nowrap', '<a href="' . $_SERVER['PHP_SELF'] . '?P=' . url_base64_encode($currPath . $category) . '" onmousedown="releaseUnload();" class="cssAnchor">' . htmlspecialchars(adjust_char($category)) . '</a>');
        showXHTML_tr_E(); //rawurlencode($currPath . $category)
    }

    if (!empty($all[1]) && sort($all[1])) foreach ($all[1] as $category) {
        $cln = $cln == 'class="cssTrOdd font01"' ? 'class="cssTrEvn font01"' : 'class="cssTrOdd font01"';
        showXHTML_tr_B($cln);
        showXHTML_td('', '<img src="/theme/default/filetype/txt.gif" align="absmiddle"');
        // showXHTML_td('nowrap', '<span style="width: 300px; overflow: hidden"><a href="javascript:opener.returnValue = \'' . (isset($content_ref)?"/$baseUri":'') . str_replace("'", "\\'", adjust_char($currPath . $category)) . '\'; self.close();" class="cssAnchor">' . htmlspecialchars(adjust_char($category) ) . '</a></span>');
        $returnValue = (isset($content_ref) ? "/$baseUri" : '') . str_replace("'", "\\'", adjust_char($currPath . $category));
        showXHTML_td('width="300" nowrap', '<a href="javascript:opener.setFilename(\'' . $returnValue . '\'); self.close();" class="cssAnchor">' . htmlspecialchars(adjust_char($category)) . '</a>');
        showXHTML_tr_E();
    }

    showXHTML_table_E();

    showXHTML_table_B('id="tabsSet2" border="0" width="100%" cellpadding="3" cellspacing="1" style="border-collapse: collapse; display: none" class="cssTable"');
    showXHTML_tr_B('class="cssTrEvn font01"');
    showXHTML_td_B('width="350"');
    showXHTML_input('file', 'upload_file', '', '', 'size="40" class="cssInput"');
    showXHTML_input('button', '', $MSG['msg_upload_file'][$sysSession->lang], '', 'class="cssBtn" onclick=checkData();');
    showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_tr_B('class="cssTrOdd font01"');
    showXHTML_td_B('width="350"');
        if (defined('ADM_EXPERIENCE')) {
            echo '<font color="red">'.$MSG['msg_chars_limit'][$sysSession->lang].'</font>';
            echo '<br>';
            echo sprintf('%s<span style="color: red; font-weight: bold">%s</span><br>%s<span style="color: red; font-weight: bold">%s</span>', $MSG['max_upload_filesize'][$sysSession->lang], ini_get('upload_max_filesize'), $MSG['max_upload_totalsize'][$sysSession->lang], ini_get('post_max_size'));
        }
    showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_table_E();

    showXHTML_tabFrame_E();
    showXHTML_body_E();
?>
