<div id="courseTreePC" class="span3 bs-sidebar hidden-xs">
    <div class="box lcms-sidebar" id="contentGroup">
        <div class="group">
            <div class="filter">
                <h2 class="course_group_root_node hidden-xs" style="cursor: pointer;">{'collegetype'|WM_Lang}</h2>
                <table class="visible-xs" style="width:100%"><tr>
                    <td><h2 class="course_group_root_node" style="cursor: pointer;margin-bottom: 5px">{'collegetype'|WM_Lang}</h2></td>
                    <td><i class="fa fa-angle-double-up" style="font-size:18px" onclick="ExpandCourseGroup(false);"></i></td>
                </tr></table>
                <table class="table tree">
                    {$htmlCourseGroup}
                </table>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div id="courseTreePhone" class="span3 bs-sidebar visible-xs">
    <div class="box lcms-sidebar">
        <div class="group">
            <div class="filter">
                <table style="width:100%"><tr>
                    <td><h2 style="cursor: pointer;margin-bottom: 5px">{'collegetype'|WM_Lang}</h2></td>
                    <td><i class="fa fa-angle-double-down" style="font-size:18px" onclick="ExpandCourseGroup(true);"></i></td>
                </tr></table>
            </div>
        </div>
    </div>
</div>
<script>
{literal}
    function ExpandCourseGroup(bl){
        if (bl) {
            $('#courseTreePhone').toggleClass('visible-xs', false);
            $('#courseTreePhone').toggleClass('hidden-xs', true);
            $('#courseTreePC').toggleClass('visible-xs', true);
            $('#courseTreePC').toggleClass('hidden-xs', false);
        }else{
            $('#courseTreePC').toggleClass('visible-xs', false);
            $('#courseTreePC').toggleClass('hidden-xs', true);
            $('#courseTreePhone').toggleClass('visible-xs', true);
            $('#courseTreePhone').toggleClass('hidden-xs', false);
        }
    }
{/literal}
</script>