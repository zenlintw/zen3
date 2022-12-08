<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/learn_path.php');
    require_once(sysDocumentRoot . '/lang/mooc_notebook.php');

    // 一般課程不使用 SSS
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
        if (dbGetOne('`WM_term_course`', '`path_type`', 'course_id=' . $sysSession->course_id ) != 3) {
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
        
    /* list 與 view-show 之間的切換， 1: list, 2: view-show */
    function showPanel(item){
        if (item === 1) {
            $('#session-view').hide();
            $('#session-list').show();
        } else if (item === 2) {
            $('#session-list').hide();
            $('#session-view').show();
        }
    }
    /* 控制是否提供上下移動 */
    function disable_control(val) {
        document.getElementById('backNodeBtn1').style.display  = val ? '' : 'none';
        document.getElementById('nextNodeBtn1').style.display  = val ? '' : 'none';
    }
</script>
</head>
<body style="height: 100%;">
<div id="session-list" style="height: 99%;">
    <iframe width="100%" height="100%" frameborder="0" border="0" scrolling="auto" name="pathtree" id="pathtree" src="m_pathtree3.php"></iframe>
</div>
<div id="session-view" style="display: none; height: 100%;">
<!-- 觀看一般課程 -->
    <div class="box1" style="height: 100%; max-width: none;">
        <div class="title">
        </div>
        <div class="operate components">
            <div id="backNodeBtn1" class="component" style="min-width:2em;" onclick="pathtree.nextStep(-1);"><div class="icon-prevpage"></div></div>
            <div id="nextNodeBtn1" class="component" style="min-width:2em;" onclick="pathtree.nextStep(1);"><div class="icon-nextpage"></div></div>
            <div id="session-msg" class="message component"  style="width:100%;">
                <div></div>
            </div>
            <?php
                if (defined('enableQuickReview') && enableQuickReview == true) {
                    echo '<div class="component">
                        <button class="btn btn-blue" style="min-width:8em;" onclick="parent.s_sysbar.goPersonal(\'SYS_06_01_013\');">'.$MSG['quick_review'][$sysSession->lang].'</button>
                    </div>';
                }
            ?>
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
            <div class="component">
                <button class="btn btn-blue" style="min-width:8em;" onclick="showPanel(1)"><?=$MSG['back_to_list'][$sysSession->lang]?></button>
            </div>
        </div>
        <div class="content" style="padding: 4px; height: 100%;">
            <h2 align="center"><br><?=$MSG['wait_msg'][$sysSession->lang]?></h2>
            <iframe name="viewframe" id="viewframe" src="" height="100%" scrolling="Auto" frameborder="0" marginwidth="0" marginheight="0"></iframe>
        </div>
    </div>
    <script>
        var f= document.getElementById("viewframe");
        f.width="100%";
        $("#backNodeBtn1, #nextNodeBtn1").hover(function(e) {
            var stext = $(this).attr("unit");
            $("#session-msg div").text(stext);
        }, function() {
            $("#session-msg div").text("");
        });
        $("#backNodeBtn1, #nextNodeBtn1").click(function(e) {
            var stext = $(this).attr("unit");
            $("#session-msg div").text(stext);
        });
    </script>
</div>
</body>
</html>
