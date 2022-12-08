        <div class="box2">
            <div class="content" style="padding:1em 2em 0em 2em">
                <div class="data1">
                    <div class="content">
                        <table><tr>
                        <td>{'msg_course'|WM_Lang}</td>
                        <td><input type="text" name="cour_keyword" id="cour_keyword" value="{$course_name}" placeholder="{'msg_course1'|WM_Lang}" onmouseover="this.focus(); this.select();" style="font-size:1em;height:28px;" /></td>
                        <td><button class="btn btn-blue" onclick="queryCourse();">{'msg_course2'|WM_Lang}</button></td>
                        {if $course_name != ''}
                        <td><button class="btn" onclick="CancelQuery();">{'cancel_title'|WM_Lang}</button></td>
                        {/if}
                        </tr></table>
                    </div>
                </div>
            </div>
            <div class="title-bar2" style="padding:0em 2em 0em 2em">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td class="t9"><div class="text-left" style="margin-left: 0.5em;">{'td_course_name'|WM_Lang}</div></td>
                                <td class="t2 hidden-phone" style="width:8em;"><div class="text-center">{'td_enroll'|WM_Lang}</div></td>
                                <td class="t2 hidden-phone" style="width:8em;"><div class="text-center">{'td_study'|WM_Lang}</div></td>
                                <td class="t2 hidden-phone"><div class="text-center">{'td_teacher'|WM_Lang}</div></td>
                                <td class="t2"><div class="text-center">{'td_audit_help'|WM_Lang}</div></td>
                                <td class="t3"><div class="text-center" style="overflow: visible;">{'td_enroll_help'|WM_Lang}</div></td>
                                {*<td class="t3 hidden-phone"><div class="text-center" style="margin-right: 0.5em;">{'td_detail'|WM_Lang}</div></td>*}
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>            
            <div class="content" style="padding:0em 2em 0em 2em">
                <div class="data2">
                    <table class="table subject">
                        {if $datalist|@count > 0}
                        {foreach from=$datalist key=k item=v}
                            <tr>
                            <td class="t9">
                                <div class="text-left" style="margin-left: 0.5em; word-break: break-all;">{$v.caption}</div>
                            </td>
                            <td class="t2 hidden-phone" style="width:8em;">
                                <div class="text-center">{$v.enroll}</div>
                            </td>
                            <td class="t2 hidden-phone" style="width:8em;">
                                <div class="text-center">{$v.study}</div>
                            </td>
                            <td class="t2 hidden-phone" style="word-break: break-all;">
                                <div class="text-center">{$v.teacher}</div>
                            </td>
                            <td class="t2">
                                <div class="text-center">{$v.auditHelp}</div>
                            </td>
                            <td class="t3">
                                <div class="text-center">{$v.enrollHelp}</div>
                            </td>
                            {*<td class="t3 hidden-phone">
                                <div class="text-center" style="margin-right: 0.5em;"><button id="winCourseInfo_{$v.course_id}" class="btn btn-gray" data-fancybox-type="iframe" href="/mooc/course_info.php?cour_id={$v.course_id}" onclick="openCourseInfo({$v.course_id});">{'btn_alt_detail'|WM_Lang}</button></div>
                            </td>*}
                        </tr>
                        {/foreach}
                        {else}
                        <tr><td><div class="text-left" style="margin-left: 0.5em;">{'msg_no_course'|WM_Lang}</div></td></tr>
                        {/if}
                    </table>
                </div>
            </div>
            <div id="pageToolbar" class="paginate"></div>            
        </div>
<script type="text/javascript" src="{$appRoot}/lib/kc-paginate.js"></script>
{include file = "common/paginate_jsdeclare.tpl"}
<script type="text/javascript">
    {$inlineSchoolJS}
    {literal}
	function openAuditWindow(courseId) {
		$("#winAudit_"+courseId).fancybox({
			maxWidth	: 800,
			maxHeight	: 600,
			fitToView	: false,
			width	: 460,
			height	: 300,
			autoSize	: false,
			closeClick	: false,
			openEffect	: 'none',
			closeEffect	: 'none'
		});
	}
	
	function openEnployWindow(courseId) {
        $("#winEnploy_"+courseId).fancybox({
            maxWidth    : 800,
            maxHeight   : 600,
            fitToView   : false,
            width   : 460,
            height  : 300,
            autoSize    : false,
            closeClick  : false,
            openEffect  : 'none',
            closeEffect : 'none'
        });
    }
    
	function openCourseInfo(courseId) {
        $("#winCourseInfo_"+courseId).fancybox({
            fitToView   : true,
            width   : 1100,
            height  : 600,
            autoSize    : false,
            closeClick  : false,
            openEffect  : 'none',
            closeEffect : 'none'
        });
    }
    $(function () {
        // 分頁工具列
        $('#pageToolbar').paginate({
            'total': 0,
            'pageNumber': 1,
            'showPageList': false,
            'showRefresh': false,
            'showSeparator': false,
            'btnTitleFirst': btnTitleFirst,
            'btnTitlePrev': btnTitlePrev,
            'btnTitleNext': btnTitleNext,
            'btnTitleLast': btnTitleLast,
            'btnTitleRefresh': btnTitleRefresh,
            'beforePageText': beforePageText,
            'afterPageText': afterPageText,
            'beforePerPageText': beforePerPageText,
            'afterPerPageText': afterPerPageText,
            'displayMsg': displayMsg,
            'buttonCls': '',
            'onSelectPage': function (num, size) {
                if (page_no == 0) return;
                if (num == 0) return;
                if (num == page_no){
                    return;
                } 
                page_no = num;
                
                obj = document.getElementById("cour_keyword");
		        var cour_key = (obj == null) ? "" : obj.value;
		        if (cour_key != "")  {
		            document.actFm.course_name.value = cour_key;
		            document.actFm.isquery.value = "true";
		        }
                document.actFm.page.value = num;
                document.actFm.submit();
            }
        });

        $('#pageToolbar').paginate('refresh', {
                    'total': total_count,
                    'pageSize': page_size
                });
                
        $('#pageToolbar').paginate('select', page_no);
    });
    {/literal}
</script>