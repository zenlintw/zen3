{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<script type="text/javascript">
    var commencementcourse = '{'commencementcourse'|WM_Lang}',
        historycourse = '{'historycourse'|WM_Lang}',
        opening = '{'opening'|WM_Lang}',
        nocourses = '{'nocourses'|WM_Lang}',
        MSGopeningperiod = '{'openingperiod'|WM_Lang}',
        isGuest = {$isGuest|@json_encode},
        NewCalendarDispalyType = {$MyCalendarSettings|@json_encode},
        newCalendarTicket = '{$newCalendarTicket}',
        MSGSHOWMORECOURSE = '{'show_more'|WM_Lang}',
        cal_alert = '{$cal_alert}';
</script>
{include file = "site_banner.tpl"}
{include file = "forum.tpl"}
{include file = "courselist.tpl"}
{include file = "thumbnail_carousel.tpl"}
{include file = "site_statistics.tpl"}
{include file = "common/site_footer.tpl"}
<script type="text/javascript" src="{$appRoot}/mooc/public/js/index.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/forum/forum.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/forum/index_forum.js"></script>