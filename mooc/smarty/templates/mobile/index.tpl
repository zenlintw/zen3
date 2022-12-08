<style>
{literal}
.lcms-nav-tabs {
    min-width: initial;
    margin-top: 0;
    margin-bottom: 10px;
}
.lcms-nav-tabs .lcms-nav-group {
    white-space: nowrap;
    overflow-x: scroll;
    width: 100%;
}
.lcms-nav-tabs .lcms-nav-group nav ul {
    padding: 0;
}
.lcms-nav-tabs nav ul li {
    margin-bottom: 0;
}
#tiles {
    padding: 0;
}
.tabs-fixed {
    position: fixed;
    top: 0;
    z-index: 10;
}
{/literal}
</style>

<div id="wrap">
    {include file = "mobile/common/site_header.tpl"}
    {include file = "mobile/common/app_install_tip.tpl"}
    <!-- 課程群組 -->
    <div id="course-tabs" class="lcms-nav-tabs">
        <div class="text-left lcms-nav-group">
            {$group}
        </div>
        <div class="text-left lcms-nav-welcome hide"></div>
    </div>
    <!-- 課程列表 -->
    <div id="mainContent">
        {include file = "common/index_course_list.tpl"}
        <div class="lcms-nav-bottom">

        </div>
    </div>
</div>
{include file = "mobile/common/site_footer.tpl"}
{*<script type="text/javascript" src="{$appRoot}/mooc/public/js/course_share.js"></script>*}
<script type="text/javascript">
    var commencementcourse = '{'commencementcourse'|WM_Lang}',
        historycourse = '{'historycourse'|WM_Lang}',
        opening = '{'opening'|WM_Lang}',
        nocourses = '{'nocourses'|WM_Lang}',
        MSGopeningperiod = '{'openingperiod'|WM_Lang}',
        NewCalendarDispalyType = {$MyCalendarSettings|@json_encode},
        newCalendarTicket = '{$newCalendarTicket}',
        MSGSHOWMORECOURSE = '{'show_more'|WM_Lang}';
        forMobile = true;
{literal}


// 頁面下捲時，保持課程群組在上方
$(window).scroll(function(){
    $('#course-tabs .lcms-nav-group').toggleClass('tabs-fixed', $(window).scrollTop() > $('#course-tabs').offset().top);
});
$(window).on( 'resize', setItemCoverHeight);
// 課程列表設定參數
$(document).ready(new function() {
    // 沒有開啟課程列表會沒有 getCourseList() ，加上判斷
    if ('undefined' !== typeof getCourseList) {
        getCourseList('getSigningCourses', 'btnSigning');
    }
});

{/literal}
</script>