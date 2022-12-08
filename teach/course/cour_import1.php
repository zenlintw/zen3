<?php
/**
 * 【程式功能】
 * 建立日期：2004/09/16
 * @author  Wiseguy Liang
 * @version $Id: cour_import1.php,v 1.1 2010/02/24 02:40:23 saly Exp $
 * @copyright 2004.09 SUNNET
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/archive_api.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lang/teach_course.php');
require_once(sysDocumentRoot . '/teach/course/import_imsmanifest.lib.php');

$sysSession->cur_func = '700600100';
$sysSession->restore();
if (!aclVerifyPermission(700600100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

/**
 * 目的 : 遞迴改變目錄權限
	 * @param string $path 目錄位置
	 * @param int $filename 權限
	 */
	function chmod_R($path, $filemode) {
   		if (!is_dir($path))
       		return;

   		$dh = opendir($path);
   		while (($file = readdir($dh)) !== FALSE) {
        if ($file != '.' && $file != '..') {
            $fullpath = $path . '/' . $file;
            if (is_dir($fullpath)) {
                chmod($fullpath, $filemode);
                chmod_R($fullpath, $filemode);
            }
        }
    }
    
    closedir($dh);
}

/**
	 * 主程式開始
	 */
	$my_dir = sprintf('%s/base/%05d/course/%08d/content/',
					  sysDocumentRoot,
					  $sysSession->school_id,
					  $sysSession->course_id);
	if ($_POST['package_source'] == '1' &&
		ereg('^[1-7]$', $_POST['package_kind']) &&
		is_uploaded_file($_FILES['package_file']['tmp_name'])
	   )
	{
		$arc = new Archive();
		if (is_dir($my_dir) && is_writable($my_dir))
		{
			if (strpos($filename, '.tar.gz') !== FALSE)
				$ext = '.tar.gz';
			elseif (strpos($filename, '.tar.bz2') !== FALSE)
				$ext = '.tar.bz2';
			elseif (strpos($filename, '.tar.Z') !== FALSE)
				$ext = '.tar.Z';
			else
				$ext = strrchr($_FILES['package_file']['name'], '.');

			if ($_POST['package_kind'] == '1') {	// 對WM2 課程包 特別處理,先解壓縮到暫存目錄取得 [0-9]{5}.php
				$tmp_dir = sprintf('%s/base/%05d/course/%08d/tmp_content/',
				  					sysDocumentRoot,
				 					$sysSession->school_id,
				  					$sysSession->course_id);
				if (@is_dir($tmp_dir)) exec("rm -rf {$tmp_dir}");
				mkdir($tmp_dir);
				$ret = $arc->extract_it($_FILES['package_file']['tmp_name'], $tmp_dir, $ext);
				$filename = basename(exec("ls {$tmp_dir}[0-9][0-9][0-9][0-9][0-9].php|head -1"));
                if (is_dir($tmp_dir)) {
                    exec("cp -R {$tmp_dir}* {$my_dir} --reply=yes");
                    exec("rm -rf {$tmp_dir}");
                }
			}
			else
				$ret = $arc->extract_it($_FILES['package_file']['tmp_name'], $my_dir, $ext);
			// 改變目錄權限,以免檔案刪不掉
			chmod_R($my_dir, 0755);

			if ($ret != 0)
			{
				switch($ret)
				{
					case '-1' : $msg = $MSG['error5'][$sysSession->lang]; break;
					case '-2' : $msg = $MSG['error6'][$sysSession->lang]; break;
					case '-3' : $msg = $MSG['error7'][$sysSession->lang]; break;
					default:    $msg = $MSG['error8'][$sysSession->lang]; break;
				}
    			wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'error: ' . $msg);
				die('error: ' . $msg);
			}
		}
		else
		{
			unlink($_FILES['package_file']['tmp_name']);
			wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], '錯誤！課程目錄路徑錯誤或該目錄沒有寫入權限！');
			die($MSG['error9'][$sysSession->lang]);
		}

                // 由於MAC電腦會多一層子目錄，故解壓縮完成後要刪除之
                $user_agent = getenv("HTTP_USER_AGENT");
                if (strpos($user_agent, "Win") !== FALSE)
                    $os = "Windows";
                elseif (strpos($user_agent, "Mac") !== FALSE)
                    $os = "Mac";

                if ($os === 'Mac') {
                    $tmp_mac_dir = sprintf('%s/base/%05d/course/%08d/content/__MACOSX/',
                                                      sysDocumentRoot,
                                                      $sysSession->school_id,
                                                      $sysSession->course_id);


                    function _deleteDirectory($dir)
                    {
                        if (!file_exists($dir)) return TRUE;
                        if (!is_dir($dir) || is_link($dir)) {
                            return unlink($dir);
                        }
                        foreach (scandir($dir) as $item) {
                            if ($item == '.' || $item == '..') continue;
                            if (!(_deleteDirectory($dir . "/" . $item))) {
                                chmod($dir . "/" . $item, 0777);
                                if (!(_deleteDirectory($dir . "/" . $item))) return FALSE;
                            };
                        }
                        return rmdir($dir);
                    }    
                    if (file_exists($tmp_mac_dir)) {
                        _deleteDirectory($tmp_mac_dir);
                    }  
                } 
	}
/*
else
{
unlink($_FILES['package_file']['tmp_name']);
wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], '錯誤！課程壓縮包無法辨識！');
die($MSG['error10'][$sysSession->lang]);
}
*/
$msg = '';
if (($_POST['package_source'] == '1' || $_POST['package_source'] == '2') && ($_POST['condition'] == '2' || $_POST['condition'] == '3')) {
    // 路徑似乎有兩種格式
    $xml_filename = '';
    if (file_exists($my_dir . 'imsmanifest.xml')) {
        $xml_filename = $my_dir . 'imsmanifest.xml';
    } else if (file_exists($my_dir . 'imsmanifest_' . $sysSession->course_id . '.xml')) {
        $xml_filename = $my_dir . 'imsmanifest_' . $sysSession->course_id . '.xml';
    } 
    switch ($_POST['package_kind']) {
        case '1': // WM2 課程包
            // 轉換 WM2 路徑為 imsmanifest.xml
            if ($filename) {
                define('CONTENT_FILE', $filename);
                include_once(sysDocumentRoot . '/teach/course/WM2toIM.php');
                processNewImsmanifest($xml_filename, $_POST['condition'] == '2');
            } else
                $msg = $MSG['no_php_file'][$sysSession->lang];
            break;
        case '5': // AICC 課程包
            // 轉換  AICC 為 imsmanifest.xml
            include_once(sysDocumentRoot . '/teach/course/AICC_import.php');
            processNewImsmanifest($xml_filename, $_POST['condition'] == '2');
            break;
        default:
            processNewImsmanifest($xml_filename, htmlspecialchars($_POST['condition']) === '2');
            break;
    }
}

if ($_POST['package_source'] == '1' && !is_uploaded_file($_FILES['package_file']['tmp_name']))
    $msg = $MSG['no_upload_file'][$sysSession->lang];
else if ($_POST['package_source'] == '2' && !file_exists($my_dir . 'imsmanifest.xml') && !file_exists($my_dir . 'imsmanifest_' . $sysSession->course_id . '.xml'))
    $msg = $MSG['no_imsmanifest_file'][$sysSession->lang];
else if ($msg == '')
    $msg = $MSG['import_complete'][$sysSession->lang];

echo <<< EOB
<script>
	alert('{$msg}');
	location.replace('cour_import.php');
</script>
EOB;
