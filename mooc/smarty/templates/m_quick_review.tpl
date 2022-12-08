{include file = "common/tiny_header.tpl"}
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/third_party/timeliner/timeliner.min.css" rel="stylesheet" />
<link href="{$appRoot}/theme/default/fancybox/jquery.fancybox.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<style>
    {literal}
    body {
        background: #07AEB0;
    }
    .clear {
        clear: both;
    }
    .layout-hr {
        display: table;
        width: 100%;
    }
    .layout-hr > .layout-child {
        display: table-cell;
        vertical-align: top;
    }
    .timeline-event-content .media {
        width: initial;
    }
    .timeline-event-content .media img {
        border: 3px solid #FFFFFF;
    }
    .timeline-event-content .media .btn {
        margin: 4px 2px;
        color: #FFFFFF;
    }
    .timeline-event-content blockquote{
        color: #FFFFFF;
        font-size: 1.1em;
        border-left-width: 0px;
    }
    .timeline-series dt {
        font-size: 1.2em;
    }
    .timeline-series dt a {
        color: #FFFFFF;
    }
    .timeline-wrapper {
          margin: 25px 0;
    }
    .timeline-wrapper h2 {
          font-size: 2em;
    }
    .timeline-wrapper h2 span {
        background: #FFFFFF;
        color: #06A2A4;
        font-size: 0.8em;
        padding: 5px 10px;
    }
    .timeline-container {
        margin: auto 0;
        border-left: 0;
    }
    .timeline-toggle {
        position: absolute;
        bottom: 0;
        right: 30px;        
        padding: 8px 15px 0 15px;
        background: #07AEB0;
        border-width: 0;
        border-radius: 0;
        color: #FFFFFF;
        font-size: 1.2em;
    }
    .note-time {
        color: #FFFFFF;
        font-size: 12px;
        padding: 0 5px;
    }
    #searchFrm input {
        margin: 0;
        height: 25px;
    }
    #course-filter {
        color: #07AEB0;
        padding-bottom: 0;
    }
    #course-filter .filter-name {
        display: inline-block;
        max-width: 300px;
        min-width: 170px;
        overflow: hidden;
        text-align: left;
        font-weight: bold;
    }
    #course-filter .caret {
        border-top-color: #07AEB0;
    }
    /* override bootstrap dropdown */
    .dropdown-menu>li>a {
        color: #07AEB0;
        font-weight: bold;
    }
    .dropdown-menu>li>a:hover, .dropdown-menu>li>a:focus {
        background: #FF9E14;
    }
    
    {/literal}
</style>
<div style="background: #FF9E14; overflow: hidden; padding: 0 2em; text-align: right; line-height: 3.2em; height: 3.2em;">
    <div style="float: left; color: #FFFFFF; font-size: 1.5em; font-weight: bold;"><div class="icon-qkreview-logo" style="margin: 5px;"></div>{$curTitle}{*{'title_quick_review'|WM_Lang}*}</div>
    <form id="searchFrm" onsubmit="searchKW(this); return false;">
        <input class="input-large" type="text" name="keyword"/>
        <button type="submit" class="btn btn-small btn-danger"><i class="icon-search icon-white"></i></button>
    </form>
</div>
<div style="background: #FFFFFF; position: relative;">
    <div class="btn-group" style="display: inline-block; margin: 6px 28px;">
        <a href="#" id="course-filter" class="btn dropdown-toggle" data-toggle="dropdown" data-value="0"><div class="filter-name">{'all_course'|WM_Lang}&nbsp;</div><span class="caret"></span></a>
        <ul class="dropdown-menu pull-left" style=" max-height: 200px; overflow: auto; text-align: left;">
            <li><a href="javascript:;" onclick="chgCourse(this, 0);">{'all_course'|WM_Lang}</a></li>
            {foreach from=$noteCs key=k item=v}
                <li><a href="javascript:;" onclick="chgCourse(this, {$v});">{$k}</a></li>
            {/foreach}
        </ul>
    </div>
        <button class="timeline-toggle" onclick="expandTimeline(this);"><div class="icon-expand-s"></div>&nbsp;{'expand_all'|WM_Lang}</button>
    <br class="clear">
</div>
<div style="background: #FFFFFF;">
    <div class="layout-hr">
        <div class="layout-child" style="background: #07AEB0; min-width: 6em; width: 6em; border-radius: 0 1em 0 0;"></div>
        <div class="layout-child" style="background: #FFFFFF; min-width: 5px; width: 5px;"></div>
        <div class="layout-child" style="background: #07AEB0; width: 100%; border-radius: 1em 0 0 0;">
            <div id="timeline" class="timeline-container">
                <div id="timeline-data"></div>
                <button id="expand-btn" class="timeline-toggle" style="display: none;"><div class="icon-expand-s"></div>&nbsp;{'expand_all'|WM_Lang}</button>
            </div>
        </div>
    </div>
</div>
<iframe id="videoFrame" src="about:blank" style="display: none;" height="600" width="800"></iframe>
<div id="edit-div" style="display: none;">
    <form id="editFrm" action="{$appRoot}/mooc/controllers/user_ajax.php">
        <div class="note-title" style="margin-bottom: 10px; font-size: 1.4em;"></div>
        <input type="hidden" name="note_id" value=""/>
        <textarea name="content" style="height: 350px; width: 500px;"></textarea>
    </form>
    <button id="memo-btn">{'change'|WM_Lang}</button>
</div>
<form id="postFrm" method="post" target="discussFrame" action="/forum/m_write.php?bTicket={$bTicket}">
    <input type="hidden" name="cid" value=""/>
    <input type="hidden" name="from" value="review"/>
    <input type="hidden" name="imgsrc" value=""/>
    <input type="hidden" name="title" value=""/>
    <input type="hidden" name="note_id" value=""/>
</form>
<form id="subjectFrm" method="post" target="discussFrame" action="/forum/m_node_chain.php">
    <input type="hidden" name="cid" value=""/>
    <input type="hidden" name="bid" value=""/>
    <input type="hidden" name="nid" value=""/>
</form>
<iframe id="discussFrame" name="discussFrame" src="about:blank" style="display: none;" height="600" width="800"></iframe>


<script language="javascript" src="{$appRoot}/public/js/third_party/timeliner/timeliner.min.js"></script>
<script language="javascript" src="{$appRoot}/theme/default/fancybox/jquery.fancybox.pack.js"></script>
<script type="text/javascript">
    var introVideoPath  = '{$courseData.introVideo}';
    var noteDir         = '{$noteDir}';
    var approot         = '{$appRoot}';
    var MSGDELETE       = '{'delete'|WM_Lang}';
    var MSGEDIT         = '{'edit'|WM_Lang}';
    var MSGDISCUSS      = '{'discuss'|WM_Lang}';
    var MSGEXPAND       = '{'expand_all'|WM_Lang}';
    var MSGCOLLAPSE     = '{'collapse_all'|WM_Lang}';
</script>
<script language="javascript" src="{$appRoot}/public/js/learn/m_quick_review.js"></script>
