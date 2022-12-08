{include file = "common/tiny_header.tpl"}
{include file = "common/course_header.tpl"}
<link href="/public/css/common.css" rel="stylesheet" />
<link href="/public/css/cour_path.css" rel="stylesheet" />
<style type="text/css">
{literal}
    ul  {list-style-type: none; margin-left: 14px; padding-left: 0}
    li  {cursor: default}
    a   {text-decoration: none; font-size: 16px}
    .step-process2 > .title {
        background-color: #FFFFFF;
    }
    .material-container {
        position: relative;
        width: 100%;
        /*min-height: 686px;*/
        height:0;
        -webkit-overflow-scrolling: touch;
  	    overflow: scroll;
  	    /*background-color: #ececec;*/
    }
    .material-iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    /*手機尺寸*/
    @media (max-width: 767px) {
    }

    /*平板直向、平板橫向*/
    @media (min-width: 768px) and (max-width: 992px) {
    }

    /*large Desktop*/
    @media (min-width: 1200px) {
    }
{/literal}
</style>
<script>
var lang                    = '{$sysSession->lang}';
var noavailable             = '{'js_msg01'|WM_Lang}';
var globalCurrentActivity   = '{$last_activity}';
var globalSuspendedActivity = '';
var NextClusterId           = '';
var fetchNextCluster        = false;
var themePath               = '{$themePath}';
var ser                     = '{$pathSerial}';
var slang                   = '{$sLang}';
var justPreview             = '{$justPreview}';
var MSG_TO_THE              = '{'it is at the'|WM_Lang}';
var MSG_OUTSET              = '{'outset.'|WM_Lang}';
var MSG_END                 = '{'end.'|WM_Lang}';
var MSG_FINISH              = '{'load_finish'|WM_Lang}';
var MSG_NO_DATA             = '{'no_course_content'|WM_Lang}';
var MSG_BTN_MIN             = '{'btn_minimize'|WM_Lang}';
var MSG_BTN_MAX             = '{'btn_maximize'|WM_Lang}';
var cid                     = '{$sysSession->course_id}';
var pTicket                 = '{$pTicket}';
var first                   = true;
var pathNodeTimeShortlimit  = '{$pathNodeTimeShortlimit}';
</script>
<script type="text/javascript" language="javascript" lang="zh-tw" charset="Big5" src="/lib/xmlextras.js"></script>
<script type="text/javascript" language="javascript" lang="zh-tw" src="/learn/path/m_manifest.js?20190502"></script>
<nav id="navbar-pathtree" class="navbar navbar-default navigation-clean-button" style="font-size:18px;margin-bottom: 0px;min-height:36px;display:none;">
    <div class="container" style="min-width: initial;padding-left: 0px; padding-right: 0px;">
        <div class="visible-xs hidden-tablet hidden-desktop" style="padding-top:5px;">
            <div class="row col-xs-12" style="padding-right:0px;">
                <div class="col-xs-1 text-center" style="padding-left:5px;padding-right:0px;"><i class="fa fa-outdent" style="color:#FFFFFF;font-size: 16px;" onclick="showPathtree(true);"></i></div>
                <div id="material-node-title" class="col-xs-9 text-left" style="padding-right: 0px;line-height: 24px;">&nbsp;</div>
                <div id="btn-prev-node" class="col-xs-1 text-center" style="padding-left:5px;padding-right:0px;"><i class="fa fa-angle-left" style="color:#FFFFFF;font-size: 18px;" onclick="nextStep(-1);"></i></div>
                <div id="btn-next-node" class="col-xs-1 text-center" style="padding-left:5px;padding-right:0px;"><i class="fa fa-angle-right" style="color:#FFFFFF;font-size: 18px;" onclick="nextStep(1);"></i></div>
            </div>
        </div>
    </div>
</nav>
<form style="display: inline">
    <div id="div-pathtree" class="box1 container" style="margin-top:0px;">
        <div class="title" style="font-size: 18px;line-height: 32px;">課程內容</div>
        <div id="displayPanel" style="width: 100%; height: 100%; padding: 5px 5px 5px 10px;" class="well"><div style="text-align: center; width: 100%;"><div class="icon-loader-lg-bk"></div></div></div>
    </div>
</form>
<div class="material-container">
<iframe id="s_main" name="s_main" src="about:blank" style="display:none;" title="呈現教材內容的框架" class="material-iframe" frameborder="0" allowfullscreen></iframe>
</div>
<form id="fetchResourceForm" target="s_main" method="POST" action="/learn/path/SCORM_fetchResource.php" style="display: none">
<input type="hidden" name="is_player"      value="false">
<input type="hidden" name="href"            value="">
<input type="hidden" name="prev_href"       value="">
<input type="hidden" name="prev_node_id"    value="">
<input type="hidden" name="prev_node_title" value="">
<input type="hidden" name="is_download"     value="">
<input type="hidden" name="begin_time"      value="{$sbegin_time}">
<input type="hidden" name="course_id"       value="{$enc_course_id}">
<input type="hidden" name="read_key"       value="{$read_key}">
</form>
<script>
{literal}
var xmlGetTime = XmlHttp.create();
var YoutubeMovieHeight = 300;

/**
 * 顯示課程內容的學習路徑
 * @param  bollean bl
 */
function showPathtree(bl)
{
    if (bl) {
        $('#navbar-course').show();
        $('#div-pathtree').show();
        $('#navbar-pathtree').hide();
        $('#s_main').hide();
        $('.material-container').height(0);
        document.s_main.location = 'about:blank';
    }else{
        $('#navbar-course').hide();
        $('#div-pathtree').hide();
        $('#navbar-pathtree').show();
        $('#s_main').show();
    }
    
    $("input[name='prev_node_id']").val('');
}

function showPageHeader(bl)
{
    if (bl) {
        $('#site_header').show();
        $('#navbar-pathtree').show();
    }else{
        $('#site_header').hide();
        $('#navbar-pathtree').hide();
    }
}

window.addEventListener("orientationchange", function() {
    if (isYoutubeMovie && $('#s_main').is(':visible')) {
        if (screen.orientation.angle == 0) {
            showPageHeader(true);
            YoutubeMovieHeight = 300;
        }else{
            showPageHeader(false);
            YoutubeMovieHeight = $(window).innerHeight();
        }
        setMaterialFrameHeight();
    }
});

function fetchServerTime()
{
    xmlGetTime.open('GET', '/learn/path/getServerTime.php', false);
    xmlGetTime.send(null);
    if (xmlGetTime.responseText.search(/server_time="([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})"/)){
        document.getElementById('fetchResourceForm').begin_time.value = RegExp.$1;
    }
    else
        alert('get server time failure.');
}
fetchServerTime();

function setMaterialFrameHeight(h) {
    if (isYoutubeMovie) {
        h=YoutubeMovieHeight;
    }
    if (h > 0) {
        $('#s_main').height(h);
    }else{
        var deviceH = $(window).innerHeight();
        var h1 = $('#site_header').outerHeight();
        var h2 = $('#navbar-pathtree').outerHeight();
        if ($('#div-pathtree').is(':hidden')) {
            $('.material-container').height(deviceH-h1-h2-5);
        } else {
            $('.material-container').height(0);
        }
        $('#s_main').height(deviceH-h1-h2-5);
        
    }
}

$(function () {
    $(window).resize(function () {
        setMaterialFrameHeight();
    }).resize();
});


{/literal}
</script>

{include file = "common/tiny_footer.tpl"}