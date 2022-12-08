<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/learn_path.php');
    require_once(sysDocumentRoot . '/lib/Mobile_Detect.php');

    // 若不是行動裝置，導回到jplayer
    $detect = new Mobile_Detect;
    if(!$detect->isMobile()) {
        header('LOCATION: player.php?file='.$_GET['file']);
        exit;
    }

    $file = $_GET['file'];
    if(empty($file))    die('params error');
    
    $ft = @explode('.', $file);
    if(!is_array($ft))  die('file format error');
    //取附檔名
    $len = count($ft);
    $ft = strtolower($ft[$len-1]);

    switch($ft){
        case 'mp4':
            $type = 'm4v';
            break;
        case 'mov':
            $type = 'mov';
            break;
        case 'webmv':
            $type = 'webm';
            break;
        case 'ogg':
        case 'ogv':
            $type = 'ogv';
            break;
        case 'mp3':
            $type = 'mp3';
            break;
        case 'wav':
            $type = 'wav';
            break;
        default:
            die('File Type unsupported');
    }

?>
<html lang="zh-Hant-TW" xmlns="http://www.w3.org/1999/xhtml" prefix='og: http://ogp.me/ns#' xmlns:og="http://ogp.me/ns#" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>mobile player</title>
</head>
<body>
<?php
    if (in_array($type, array('mp3','wav'))) {
        // 音訊
        echo sprintf('<audio id="mobileMediaPlayer" src="%s" width="100%%" controls controlsList="nodownload"></audio>',htmlspecialchars($file));
    }else{
        // 視訊
        echo sprintf('<video id="mobileMediaPlayer" src="%s" width="100%%" height="240" controls controlsList="nodownload"></video>',htmlspecialchars($file));
    }
?>
</body>
</html>
