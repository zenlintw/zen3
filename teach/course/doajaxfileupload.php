<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lang/mooc_teach.php');

$msg = "";
$csid = intval(trim(sysNewDecode($_POST['basePath'])));

$basePath = sprintf('%s/base/%05d/course/%08d/content', sysDocumentRoot, $sysSession->school_id, $csid);

if(!is_dir($basePath)){
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$wmproCourseDir = realpath(sprintf('%s/base/%05d/course', sysDocumentRoot, $sysSession->school_id));
if (substr(realpath($basePath), 0, strlen($wmproCourseDir)) !== $wmproCourseDir) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

if(!is_dir($basePath . '/public')){
    mkdir($basePath . '/public', 0755);
}

move_uploaded_file($_FILES['intro_file']['tmp_name'], $basePath . '/public/course_introduce.mp4');
$msg .= ' ' .  $MSG['upload_success'][$sysSession->lang] . '\n';

if (file_exists($basePath . '/public/course_introduce.mp4')) {
    $ffmpeg = exec("sh -c 'PATH=/usr/bin:/usr/sbin:/sbin:/bin:/usr/local/bin which ffmpeg'");
    if (!empty($ffmpeg)) {
        system($ffmpeg . ' -ss 00:00:05 -i '. $basePath . '/public/course_introduce.mp4 -y -f image2 -vframes 1 '. $basePath . '/public/course_introduce.jpg', $ScreenshotsRtn1);
        system($ffmpeg . ' -ss 00:00:10 -i '. $basePath . '/public/course_introduce.mp4 -y -f image2 -vframes 1 '. $basePath . '/public/course_introduce_2.jpg', $ScreenshotsRtn2);
        system($ffmpeg . ' -ss 00:00:15 -i '. $basePath . '/public/course_introduce.mp4 -y -f image2 -vframes 1 '. $basePath . '/public/course_introduce_3.jpg', $ScreenshotsRtn3);
    }else{
        $msg = ' ' . $MSG['ffmpeg_not_found'][$sysSession->lang];
    }
}

// $ScreenshotsRtn2、$ScreenshotsRtn3 為預備截圖，暫不作判斷
if ($ScreenshotsRtn1 !== 0) {
    $msg .= $MSG['screenshots_failed'][$sysSession->lang];
}

echo "{\"msg\": \"{$msg}\"}";
die();