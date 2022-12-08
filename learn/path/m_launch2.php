<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/learn_path.php');
    require_once(sysDocumentRoot . '/lang/mooc_notebook.php');

    // 短期課程不使用 SSS
    /*
	if (sysEnable3S &&
		(bool)dbGetOne('WM_term_path', 'content REGEXP "<manifest.*version=[\'\\"]1.3[\'\\"]"', 'course_id=' . $sysSession->course_id . ' order by serial desc'))
	{
		echo '<script language="javascript">location.replace("/learn/scorm/index.php");</script>';
		exit();
	}
     * 
     */

    // 判斷 LCMS 是否開啟，及課程節點類型
    if (!sysLcmsEnable){
        die("Access denid.");
    } else {
        if (dbGetOne('`WM_term_course`', '`path_type`', 'course_id=' . $sysSession->course_id ) != 2) {
             header("LOCATION: /learn/path/launch.php");
        }
    }
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=8">
<script type="text/javascript" language="javascript" lang="zh-tw" src="/lib/jquery/jquery.min.js"></script>
<script type="text/javascript" language="javascript">
    /* 筆記用 */
    var cticket = '<?php echo $_COOKIE['idx']?>',
        username = '<?php echo $sysSession->username?>',
        nowlang = '<?php echo $sysSession->lang?>',
        msg = <?php echo json_encode($MSG)?>;
</script>    
<script type="text/javascript" language="javascript" src="/public/js/notebook/gotoclass.js"></script>
<link href="/theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<link href="/public/css/common.css" rel="stylesheet" />
<link href="/public/css/cour_path.css" rel="stylesheet" />
<script>
	parent.API = new parent.APIObject();
	parent.API_1484_11 = new parent.API_Adapter();
	/*
	if (parent.document.getElementById('envClassRoom').cols != '200,*')
		parent.FrameExpand(1,true,0);
	*/
    /* parent.s_catalog.location.replace('/learn/path/manifest.php'); */
/* 控制是否提供上下移動 */
function disable_control(val) {
}
</script>
</head>
<body style="height: 100%;">
<div style="height: 100%;">
    <!-- 短期課程觀看 -->
    <div class="box1" style="height: 100%; max-width: none;">
        <div class="operate components">
            <div class="component">
                <span style="color: #707070;"><?=$MSG['complete_schedule'][$sysSession->lang];?></span>
            </div>
            <div class="component" style="width: 100%;">
                <div class="progress progress-warning progress-striped">
                    <div id="progressBar" class="bar" style="width: 0"></div>
                </div>
            </div>
            <div class="component">
                <span id="progressBar-text" style="font-weight: bold; color: #242424;">0%</span>
            </div>
            <?php
                if (defined('enableQuickReview') && enableQuickReview == true) {
                    echo '<div class="component">
                        <button class="btn btn-blue" style="min-width:8em;" onclick="parent.s_sysbar.goPersonal(\'SYS_06_01_013\');">'.$MSG['quick_review'][$sysSession->lang].'</button>
                    </div>';
                }
            ?>
            <div id="self-assessment" class="component" style="display: none;">
                <button class="btn btn-blue" style="min-width:8em;" onclick="document.getElementById('pathtree').contentWindow.showExam(); return false;"><?=$MSG['self_assessment'][$sysSession->lang];?></button>
            </div>
            <div class="component">
                <!--
                <button class="btn btn-blue" style="min-width:8em;" onclick="pathtree.notebook();"><?=$MSG['btn_note'][$sysSession->lang];?></button>
                -->
                <a name="course_notebook" title="<?php echo $MSG['btn_note'][$sysSession->lang]; ?>" class="btn btn-blue" style="min-width:5.7em;"><?=$MSG['btn_note'][$sysSession->lang]?></a>
                <form name="goto" target="course_notebook" action="/message/m_notebook.php" method="POST" style="display: none;">
                    <input type="hidden" name="cid" value="<?php echo $sysSession->course_id;?>">
                    <input type="hidden" name="cname" value="<?php echo $sysSession->course_name;?>">
                    <input type="hidden" name="fid">
                    <input type="hidden" name="fname">
                </form>
            </div>
        </div>
        <div class="content abreast">
            <div class="abreast-cell">
                <h2 align="center"></h2>
                <iframe name="viewframe" id="viewframe" src="" width="100%" height="100%" scrolling="NO" frameborder="0" marginwidth="0" marginheight="0"></iframe>
            </div>
            <div class="abreast-cell" style="width: 250px;">
                <iframe width="100%" height="100%" frameborder="0" border="0" scrolling="auto" name="pathtree" id="pathtree" src="m_pathtree2.php"></iframe>
            </div>
        </div>
    </div>
    
    <script>
        var f= document.getElementById("viewframe");
        f.width="100%";
    </script>
</div>
</body>
</html>
