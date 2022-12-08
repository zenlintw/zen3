{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<link href="/public/css/mycourse/site.css" rel="stylesheet">
<link href="/public/css/mycourse/tooltip.css" rel="stylesheet">
<link href="/public/css/mycourse/mycourse.css" rel="stylesheet">
<style type="text/css">
{literal}

#BlockContainer {
    margin-top: 40px;
    min-height: 55px;
}

.fancybox-margin {
    margin-right: 0px;
}
/* 暫時CSS */
.div_course_class {
    color: #3A3A3A;
    margin-top: 15px;
    font-size: 1em;
    line-height: 1.2em;
}

@media (max-width: 414px) {
    #divCourseBlock {
        padding-top: 34px;
        padding-bottom: 34px;
    }
    .block_course_class {
        min-height: initial;
        font-size: 14px;
    }
    .div_course_class {
        padding-bottom: 10px;
    }
}
.review_text{
    margin-left: 13px;
    margin-bottom: 5px;
    vertical-align: middle;
}
.review_text .label{
    padding: 5px;
}
{/literal}
</style>
<style id="style-1-cropbar-clipper">
/* Copyright 2014 Evernote Corporation. All rights reserved. */
{literal}
.en-markup-crop-options {
    top: 18px !important;
    left: 50% !important;
    margin-left: -100px !important;
    width: 200px !important;
    border: 2px rgba(255, 255, 255, .38) solid !important;
    border-radius: 4px !important;
}

.en-markup-crop-options div div:first-of-type {
    margin-left: 0px !important;
}
{/literal}
</style>
<div id="BlockContainer" class="container">
    <div class="row">
        <div class="block-title-font col-md-12" style=""><i class="fa fa-magic" aria-hidden="true" style="font-size:26px;margin-left:0.5em;margin-right:0.5em;"></i>我教的課</div>
    </div>
    <div class="row">
        <div class="col-md-12" style="padding-top: 5px;padding-bottom: 20px;">
            <div style="background-color:#F18E1E;height:3px;">&nbsp;</div>
        </div>
    </div>
</div>
<div class="content main-wrap container">
    <div class="row">
        <div id="mycourse-container">
            <div id="listtype_list_container">
                <div id="listtype_list_content">
                    {if $courseList|@count eq 0 }
                    <div class="container" style="width:510px;">
                        <div class="message" style="z-index:100; position:relative; top:95px; color: #FFFFFF;font-size: 20px;font-weight: bold;">您目前無任何課程</div>
                        <img src="/theme/default/learn/find_courses.png" />
                    </div>
                    {else}
                        <!--<div class="col-xs-12">顯示50筆課程</div>-->
                        {foreach from=$courseList key=k item=v}
                        <div class="mycourse-table">
                            <div class="tb-row" style="background-color: #F8F8F8;">
                                <div class="cell col-xs-12 col-md-4 col-sm-4 center" style="min-width: 245px;">
                                    <div class="col-md-12" style="padding-bottom: 15px">
                                    <div class="row" style="margin: 0px;padding:0px;width:100%;border: 1px solid #C9C9C9">
                                        <div class="col-md-12" style="margin: 0px;padding:0px;"><a href="javascript: void(0);" onclick="gotoCourse(10001, {$v.course_id});return false;"><img src="/lib/app_show_course_picture.php?courseId={$v.cpic}&now={$smarty.now|date_format:"%Y%m%d%H"}{*整點更新一次*}" alt="課程代表圖" style="width:100%" /></a>
                                            {*{if $v.category eq 1}
                                            <div class="course-category-title-blue">醫療器材</div>
                                            {elseif $v.category eq 2}
                                            <div class="course-category-title-green">化粧品</div>
                                            {else}
                                            <div class="course-category-title-orange">其他</div>
                                            {/if}*}
                                        </div>
                                    </div>
                                    </div>
                                </div>
                                <div class="cell col-xs-12 col-md-8 col-sm-8">
                                    <div class="col-md-12">
                                        <h3 class="ml15"><a href="javascript: void(0);" onclick="gotoCourse(10001, {$v.course_id});return false;">{$v.caption}</a></h3>
                                        <hr />
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12 ">
                                        <div class="cell col-xs-12 col-sm-6 col-md-5">
                                            <div>
                                                <ul style="margin-left: 0; margin-top: 0.7em;">講師：{$v.teacher}</ul>
                                            </div>
                                        </div>
                                        <div class="cell col-xs-12 col-sm-6 col-md-5" style="padding: 0;">
                                            <div class="openingperiod" style="margin-top: 0.5em;"><i class="fa fa-clock-o ic-clock" aria-hidden="true"></i>開課期間：{$v.classPeriod}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/foreach}
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>
<form name="formMyCourseQuery" method="POST" action="/mooc/user/myteaching.php">
    <input type="hidden" name="course_type" value="{$CourseType}" />
    <input type="hidden" name="keyword" value="{$keyword}" />
</form>
<form name="formMyClassmates" method="POST" action="/mooc/user/myclassmates.php">
<input type="hidden" name="course_id" value="" />
</form>
<script type="text/javascript">
{literal}

/*分頁元件使用到的語系 E*/
function gotoCourse(schoolId, csid) {
    document.location.href = "/" + csid ;
}

{/literal}
</script>
{include file = "common/site_footer.tpl"}