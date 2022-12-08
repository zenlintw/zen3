<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/learn_path.php');
    require_once(sysDocumentRoot . '/lib/Mobile_Detect.php');
    // 判斷使用者是否使用行動裝置
    $detect = new Mobile_Detect;
    if($detect->isMobile() && !$detect->isTablet()){
        header("LOCATION: /learn/path/m_pathtree.php");
        exit;
    }
    /* [MOOC](B) # 依據學習路徑類型變更顯示方式 2014/12/30 By Spring */
    // 學習路徑type: 0:未設定；1:自訂課程；2:短期課程；3:一般課程
    /*if (sysLcmsEnable) {
        switch (dbGetOne('`WM_term_course`', '`path_type`', 'course_id=' . $sysSession->course_id )) {
            case 2:
                header("LOCATION: /learn/path/m_launch2.php");
                exit;
                break;
            case 3:
                header("LOCATION: /learn/path/m_launch3.php");
                exit;
                break;
            default:
                break;
        }
    }*/
    /* [MOOC](B) # 儲存學習路徑類型 2014/12/30 By Spring */
    // 不使用 scorm 2004 的學習路徑
    /*
    if (sysEnable3S &&
        (bool)dbGetOne('WM_term_path', 'content REGEXP "<manifest.*version=[\'\\"]1.3[\'\\"]"', 'course_id=' . $sysSession->course_id . ' order by serial desc'))
    {
        echo '<script language="javascript">location.replace("/learn/scorm/index.php");</script>';
        exit();
    }
     * 
     */
    if (strlen(intval($sysSession->course_id))!=8) {
            die('Access Denied.');
    }

    $cid = sysNewEncode($sysSession->course_id);
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<script>
    parent.API = new parent.APIObject();
    parent.API_1484_11 = new parent.API_Adapter();
    
    if (parent.document.getElementById('envClassRoom').cols != '266,*')
        parent.FrameExpand(1,true,0);
    
    parent.s_catalog.location.replace('/learn/path/manifest.php?cid=<?=$cid?>');

</script>
</head>
<body>
<h2 align="center"><br><?=$MSG['wait_msg'][$sysSession->lang]?></h2>
</body>
</html>
