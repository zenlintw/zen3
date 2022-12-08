<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
function isImage($filename){
	$file     = fopen($filename, "rb"); 
    $bin      = fread($file, 2);  // 只讀取兩個字元

    fclose($file); 
    $strInfo  = @unpack("C2chars", $bin); 
    $typeCode = intval($strInfo['chars1'].$strInfo['chars2']); 
    $fileType = ''; 

    if($typeCode == 255216 /*jpg*/ || $typeCode == 7173 /*gif*/ || $typeCode == 13780 /*png*/ || $typeCode == 6677 /*bmp*/) 
    { 
        return $typeCode; 
    }
    else
    { 
        return false; 
    } 
}

$uploads_dir = sprintf('%s/base/%05d/course/%08d/content/public', sysDocumentRoot, $sysSession->school_id, $sysSession->course_id); //存放上傳檔案資料夾

if (!is_dir($uploads_dir)) {
    @mkdir($uploads_dir, 0755);
}

$uploads_dir = sprintf('%s/base/%05d/course/%08d/content/public/temp', sysDocumentRoot, $sysSession->school_id, $sysSession->course_id); //存放上傳檔案資料夾

if (!is_dir($uploads_dir)) {
    @mkdir($uploads_dir, 0755);
}

if ($_POST['action']=='upload') {
	if (count($_FILES["file"]["error"])>0) {
		
		$uploads_dir = sprintf('%s/base/%05d/course/%08d/content/public/temp/item', sysDocumentRoot, $sysSession->school_id, $sysSession->course_id);
		
	    if(!is_dir($uploads_dir)){
		    mkdir($uploads_dir, 0755);
		}
		
		//exec('/bin/rm -rf '.$uploads_dir.'/');
		
		
		foreach ($_FILES["file"]["error"] as $key => $error) {
		    if ($error == UPLOAD_ERR_OK) {
		        $tmp_name = $_FILES["file"]["tmp_name"][$key];
		        $name = $_FILES["file"]["name"][$key];
		        
		        move_uploaded_file($tmp_name, "$uploads_dir/$name");
		        
		        $file_name = $name;
		        $file_src = str_replace(sysDocumentRoot,"","$uploads_dir/$name");

		        $image = 0;
		        if (isImage("$uploads_dir/$name")) {
		            $image = 1;
		        }
		        
		        $responseObject = array(
		            'name' => $file_name,
		            'src' => $file_src,
		            'isimage' => $image
		        );
		        
		        echo json_encode($responseObject);
		        
		        
		    }
		}
	}
} else if ($_POST['action']=='choice-upload') {
	if (count($_FILES["file"]["error"])>0) {
        $uploads_dir = sprintf('%s/base/%05d/course/%08d/content/public/temp/choice', sysDocumentRoot, $sysSession->school_id, $sysSession->course_id);
		
	    if(!is_dir($uploads_dir)){
		    mkdir($uploads_dir, 0755);
		}
		
		//exec('/bin/rm -rf '.$uploads_dir);
		
	    foreach ($_FILES["file"]["error"] as $key => $error) {
		    if ($error == UPLOAD_ERR_OK) {
		        $tmp_name = $_FILES["file"]["tmp_name"][$key];
		        $name = $_FILES["file"]["name"][$key];
		        
		        move_uploaded_file($tmp_name, "$uploads_dir/$name");
		        
		        $file_name = $name;
		        $file_src = str_replace(sysDocumentRoot,"","$uploads_dir/$name");

		        $image = 0;
		        if (isImage("$uploads_dir/$name")) {
		            $image = 1;
		        }
		        
		        $responseObject = array(
		            'name' => $file_name,
		            'src' => $file_src,
		            'isimage' => $image
		        );
		        
		        echo json_encode($responseObject);
		        
		        
		    }
		}
	}
}


?>