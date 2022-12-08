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
	$read_key = md5(time());

	$themePath = sprintf('/theme/%s/learn/', $sysSession->theme);
?>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=8">
<style>
ul	{list-style-type: none; margin-left: 14; padding-left: 0}
li	{cursor: default}
a	{text-decoration: none; font-size: 11pt}
</style>
<?php
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	
	$sbegin_time = date('Y-m-d H:i:s');
	
	$last_activity = dbGetOne('WM_record_reading', 'activity_id', 'course_id=' . $sysSession->course_id . ' and username="' . $sysSession->username . '" order by over_time desc', ADODB_FETCH_NUM);

    $asmt_num = dbGetOne('WM_term_course', 'exam_num', 'course_id=' . $sysSession->course_id , ADODB_FETCH_NUM);

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
var MSG_ASSET_ORDER         = '<?=$MSG['msg_asset_order'][$sysSession->lang];?>';
var MSG_NO_DATA             = '<?=$MSG['no_course_content'][$sysSession->lang];?>';
var cid                     = '<?=$sysSession->course_id;?>';
var pTicket                 = '<?=$_COOKIE["idx"];?>';
var asmtNum                 = '<?=$asmt_num;?>';

</script>
<script type="text/javascript" language="javascript" lang="zh-tw" src="/lib/jquery/jquery.min.js"></script>
<script type="text/javascript" language="javascript" lang="zh-tw" charset="Big5" src="/lib/xmlextras.js"></script>
<script type="text/javascript" language="javascript" lang="zh-tw" src="m_manifest2.js"></script>
<link href="/theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<link href="/public/css/common.css" rel="stylesheet" />
<link href="/public/css/cour_path.css" rel="stylesheet" />
</head>
<body style=" background: #FFFFFF; margin: 0; padding: 0">
<form style="display: inline">
    <div style="width: 230px; height: 200px;">
        <ul id="displayPanel" class="step-process">
            <div style="text-align: center; width: 100%;"><div class="icon-loader-lg-bk"></div></div>
        </ul>
    </div>
</form>
<form id="fetchResourceForm" target="viewframe" method="POST" action="SCORM_fetchResource.php" style="display: none">
<input type="hidden" name="href"            value="">
<input type="hidden" name="prev_href"       value="">
<input type="hidden" name="prev_node_id"    value="">
<input type="hidden" name="prev_node_title" value="">
<input type="hidden" name="begin_time"      value="<?=$sbegin_time;?>">
<input type="hidden" name="course_id"       value="<?=sysNewEncode($sysSession->course_id)?>">
<input type="hidden" name="read_key"       value="<?=$read_key?>">
</form>
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
