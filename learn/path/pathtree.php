<?php
    /**************************************************************************************************
     *                                                                                                *
     *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
     *                                                                                                *
     *      Programmer: Wiseguy Liang                                                                 *
     *      Creation  : 2003/09/23                                                                    *
     *      work for  :                                                                               *
     *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
     *                                                                                                *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/learn_path.php');
    require_once(sysDocumentRoot . '/lib/lib_lcms.php');
    $read_key = md5(time());

    $themePath = sprintf('/theme/%s/learn/', $sysSession->theme);

    if (isset($_GET['cid'])) {
        $cid = $_GET['cid'];
        $course_id = trim(sysNewDecode($_GET['cid']));
    } else {
        $cid = sysNewEncode($sysSession->course_id);
        $course_id = $sysSession->course_id;
    }
?>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=10, IE=8">
<style>
ul    {list-style-type: none; margin-left: 14px; padding-left: 0}
li    {cursor: default}
a    {text-decoration: none; font-size: 11pt}
</style>
<?php
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    
    $sbegin_time = date('Y-m-d H:i:s');
    
    $last_activity = dbGetOne('WM_record_reading', 'activity_id', 'course_id=' . $course_id . ' and username="' . $sysSession->username . '" order by over_time desc', ADODB_FETCH_NUM);
    /*Custom 2017-11-29 *049642 */
    list($co_warning_time) = dbGetStSr('WM_term_course', 'co_warning_time', "course_id = {$course_id}", ADODB_FETCH_NUM); // custom

        /****************************************************************************************************************************/
        // 設定 wm learning hash cooke 到 lcms
        $pathWmCookieHash2Lcms = setWmLearningHashCookie('forced');     
?>
<script>
var lang                    = '<?=$sysSession->lang;?>';
var noavailable             = '<?=$MSG['js_msg01'][$sysSession->lang];?>';
var globalCurrentActivity   = '<?=$last_activity;?>';
var globalSuspendedActivity = '';
var NextClusterId           = '';
var fetchNextCluster        = false;
var themePath               = '<?=$themePath;?>';
var ser                     = '<?=$pathSerial;?>';
var slang                   = '<?=$sLang;?>';
var justPreview             = '<?=$justPreview;?>';
var MSG_TO_THE              = '<?=$MSG['it is at the'][$sysSession->lang];?>';
var MSG_OUTSET              = '<?=$MSG['outset.'][$sysSession->lang];?>';
var MSG_END                 = '<?=$MSG['end.'][$sysSession->lang];?>';
var MSG_FINISH              = '<?=$MSG['load_finish'][$sysSession->lang];?>';
var MSG_NO_DATA             = '<?=$MSG['no_course_content'][$sysSession->lang];?>';
var MSG_BTN_MIN             = '<?=$MSG['btn_minimize'][$sysSession->lang];?>';
var MSG_BTN_MAX             = '<?=$MSG['btn_maximize'][$sysSession->lang];?>';
var cid                     = '<?=$course_id;?>';
var pTicket                 = '<?=$_COOKIE["idx"];?>';
var pathNodeTimeShortlimit  = '<?=pathNodeTimeShortlimit;?>';
</script>
<script type="text/javascript" language="javascript" lang="zh-tw" src="/lib/jquery/jquery.min.js"></script>
<script type="text/javascript" language="javascript" lang="zh-tw" charset="Big5" src="/lib/xmlextras.js?<?php echo getFileModifyTime('/lib/xmlextras.js');?>"></script>
<script type="text/javascript" language="javascript" lang="zh-tw" src="manifest.js?<?php getFileModifyTime('/learn/path/manifest.js');?>"></script>
<link href="/theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<link href="/public/css/common.css?<?php getFileModifyTime('/public/css/common.css');?>" rel="stylesheet" />
<link href="/public/css/cour_path.css?<?php getFileModifyTime('/public/css/cour_path.css');?>" rel="stylesheet" />
</head>
<body style="margin: 0; padding: 0 10px;">
<form style="display: inline">
    <!--
  <table>
    <tr>
      <td id="displayPanel" nowrap>Generating Path ...</td>
    </tr>
  </table>
    -->
    <div>
        <div id="displayPanel" style="width: 98%; height: 100%;margin-left: -7px;"><div style="text-align: center; width: 100%;"><div class="icon-loader-lg-bk"></div></div></div>
    </div>
</form>
<form id="fetchResourceForm" target="s_main" method="POST" action="SCORM_fetchResource.php" style="display: none">
<input type="hidden" name="is_player"      value="false">
<input type="hidden" name="href"            value="">
<input type="hidden" name="prev_href"       value="">
<input type="hidden" name="prev_node_id"    value="">
<input type="hidden" name="prev_node_title" value="">
<input type="hidden" name="is_download"     value="">
<input type="hidden" name="begin_time"      value="<?=$sbegin_time;?>">
<input type="hidden" name="course_id"       value="<?=$cid?>">
<input type="hidden" name="read_key"       value="<?=$read_key?>">
</form>
<img src="<?php echo $pathWmCookieHash2Lcms;?>" style="display: none;">
<script>
var xmlGetTime = XmlHttp.create();

function fetchServerTime()
{
    xmlGetTime.open('GET', 'getServerTime.php', false);
    xmlGetTime.send(null);
    if (xmlGetTime.responseText.search(/server_time="([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})"/)){
        document.getElementById('fetchResourceForm').begin_time.value = RegExp.$1;
    }
    else
        alert('get server time failure.');
}
fetchServerTime();
</script>

</body>

</html>