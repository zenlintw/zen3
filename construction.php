<?php
    /**
     * 停機公告
     *
     * @since   2018/04/10
     * @author  Jeff Wang
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/sys_config.php');
    require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');
    require_once(sysDocumentRoot . '/lib/lib_encrypt.php');

    // 停機公告
    $system_pause_file = sysDocumentRoot . '/base/10001/system_pause.txt';
    $isSystemPause = false;
    if (file_exists($system_pause_file)){
        $fp = @fopen($system_pause_file, "r");
        // 讀出整個檔案內容
        $dec_content = @fread($fp,filesize($system_pause_file));
        // 解開編碼
        $system_pause_data = unserialize(other_dec($dec_content));
        if (is_array($system_pause_data)){
            if ((time()> strtotime($system_pause_data['start_time'])) && (time()<strtotime($system_pause_data['end_time']))){
                $systemPauseAllowIps = explode(";", $system_pause_data['allow_ip']);
                if (!in_array($_SERVER['REMOTE_ADDR'], $systemPauseAllowIps)) {
                    $isSystemPause = true;
                }
            }
        }
    }

    // 系統沒有停機，導向首頁
    if (!$isSystemPause) {
        header("LOCATION: /mooc/index.php");
        exit;
    }
?>

<html>
<head>
<meta http-equiv="Content-Language" content="zh-tw">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>系統維修檢護</title>
</head>
<body bgcolor="#0000FF">
<p align="center">
<img border="0" src="/theme/SunnetMaintainImage/maintainLine.gif" width="533" height="25"></p>
<p align="center"><b><font size="7" face="標楷體" color="#FFFFFF">
<img border="0" src="/theme/SunnetMaintainImage/maintainPic.gif" width="136" height="84"></font></b></p>
<p align="center"><b><font size="7" face="標楷體" color="#FFFFFF"><?php echo nl2br(str_replace('[newline]',"\n",$system_pause_data['content']));?></font></b></p>
<p align="center"><b><font size="7" face="標楷體" color="#FFFFFF">結束後將立即開放</font></b></p>
<p align="center">
<img border="0" src="/theme/SunnetMaintainImage/maintainLine.gif" width="533" height="25"></p>
</body>
</html>