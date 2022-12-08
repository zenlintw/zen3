<style type="text/css">
    {literal}
        #courseBlockContainer {
            width: 100%;
            padding-top: 20px;
            padding-bottom: 20px;
            margin: auto;
            background-color: #EDEDED;
        }

        .course_categroy_tab {
            cursor: pointer;
        }


        /*平板直向、平板橫向*/
        @media (min-width: 768px) and (max-width: 992px) {
        }

        /*large Desktop*/
        @media (min-width: 1200px) {
            .div_course_item img {
                max-height: 125px;
            }
        }

        /*手機尺寸*/
        @media (max-width: 767px) {
            .subtitle h1 {
                font-size: 28px;
            }
        }

        /*手機尺寸*/
        @media (max-width: 374px) {
            
            .subtitle h1 {
                font-size: 26px;
            }
            
        }
    {/literal}
</style>
<div id="courseBlockContainer">
    <div class="container">
        <div id="course-tabs" class="row subtitle heading-bottom-border" style="margin-left: 15px;margin-right:15px;">
            <div class="col-md-12" style="padding-left: 0px;">
                <h1 id="course_tab_new" class="course_categroy_tab active" onclick="showCouseBlock('new');">{'latest_course'|WM_Lang}</h1>
                <h1 id="course_tab_hot" class="course_categroy_tab" style="margin-left: 1em;" onclick="showCouseBlock('hot');">{'popular_courses'|WM_Lang}</h1>
            </div>
        </div>
        <div id="mainContent">
            {include file = "common/course_list.tpl"}
            <div class="lcms-nav-bottom"></div>
        </div>
        <div style="clear: both;  margin-bottom: 10px;"></div>
    </div>
</div>
<script type="text/javascript">
    var nowShowCourseBlockTab = 'new';
{literal}
function showCouseBlock(which){
    if (which == nowShowCourseBlockTab) return;

    // 隱藏現在的tab與block
    $('.course_categroy_tab.active').removeClass('active');
    // 顯示點選的block
    $('#course_tab_'+which).addClass('active');
    // 儲存目前所點選的tab
    nowShowCourseBlockTab = which;

    getCourseList('getSigningCourses', which);

}
// 課程列表設定參數
$(document).ready(new function() {
    // 沒有開啟課程列表會沒有 getCourseList() ，加上判斷
    if ('undefined' !== typeof getCourseList) {
        if (document.location.hash == '#course_tab_hot') {
            showCouseBlock('hot');
        }else{
            getCourseList('getSigningCourses', 'new');
        }
    }

});
{/literal}
</script>