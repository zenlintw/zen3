<div class="box2">
    <div class="content" style="padding:1em 2em 0em 2em">
        <div class="data1">
            <div class="content">
                {'msg_help_major'|WM_Lang}
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
                        <td class="t9">
                            <div class="text-left" style="margin-left: 0.5em;">
                                <a href="javascript:;" onclick="sort_data('stud');return false;">{'td_course_name'|WM_Lang}</a>
                                {if $sort == 'caption'}
                                    {if $order == 'desc'}
                                    <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                    {else}
                                    <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                    {/if}
                                {/if}
                            </div>
                        </td>
                        <td class="t2 hidden-phone">
                            <div class="text-center">
                                <a href="javascript:;" onclick="sort_data('stud');return false;">{'td_study_begin'|WM_Lang}</a>
                                {if $sort == 'st_begin'}
                                    {if $order == 'desc'}
                                    <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                    {else}
                                    <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                    {/if}
                                {/if}
                            </div>
                        </td>
                        <td class="t2 hidden-tablet hidden-phone">
                            <div class="text-center">
                                <a href="javascript:;" onclick="sort_data('audi');return false;">{'td_study_end'|WM_Lang}</a>
                                {if $sort == 'st_end'}
                                    {if $order == 'desc'}
                                    <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                    {else}
                                    <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                    {/if}
                                {/if}
                            </div>
                        </td>
                        <td class="t1 hidden-tablet hidden-phone">
                            <div class="text-center" style="overflow: visible;">
                                <a href="javascript:;" onclick="sort_data('login_times');return false;">{'td_new_post'|WM_Lang}</a>
                                {if $sort == 'post'}
                                    {if $order == 'desc'}
                                    <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                    {else}
                                    <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                    {/if}
                                {/if}
                            </div>
                        </td>
                        <td class="t1">
                            <div class="text-center">
                                <a href="javascript:;" onclick="sort_data('post_times');return false;">{'td_nowrite_homework'|WM_Lang}</a>
                                {if $sort == 'homework'}
                                    {if $order == 'desc'}
                                    <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                    {else}
                                    <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                    {/if}
                                {/if}
                            </div>
                        </td>
                        <td class="t1">
                            <div class="text-center">
                                <a href="javascript:;" onclick="sort_data('dsc_times');return false;">{'td_nowrite_exam'|WM_Lang}</a>
                                {if $sort == 'exam'}
                                    {if $order == 'desc'}
                                    <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                    {else}
                                    <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                    {/if}
                                {/if}
                            </div>
                        </td>
                        <td class="t1">
                            <div class="text-center">
                                <a href="javascript:;" onclick="sort_data('dsc_times');return false;">{'td_nowrite_questionnaire'|WM_Lang}</a>
                                {if $sort == 'questionnaire'}
                                    {if $order == 'desc'}
                                    <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                    {else}
                                    <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                    {/if}
                                {/if}
                            </div>
                        </td>
                        <td class="t1 hidden-tablet hidden-phone">
                            <div class="text-right" style="margin-right: 0.5em;">{'td_level'|WM_Lang}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="content" style="padding:0em 2em 0em 2em">
        <div class="data2">
            <table class="table subject">
                {foreach from=$datalist key=k item=v}
                <tr>
                    <td class="t9">
                        <div  class="text-left" style="margin-left: 0.5em; word-break: break-all;"><a href="javascript:;" onclick="parent.chgCourse({$v.0}, {$nEnv}, 1);return false;" class="cssAnchor">{$v.1}</a></div>
                    </td>
                    <td class="t2 hidden-phone">
                        <div class="text-center">{if $v.2 == ''}{'now'|WM_Lang}{else}{$v.2}{/if}</div>
                    </td>
                    <td class="t2 hidden-tablet hidden-phone">
                        <div class="text-center">{if $v.3 == ''}{'forever'|WM_Lang}{else}{$v.3}{/if}</div>
                    </td>
                    <td class="{if $sort == 'post'}t2{else}t1{/if} hidden-tablet hidden-phone">
                        <div class="text-center">{$v.4}</div>
                    </td>
                    <td class="{if $sort == 'homework'}t2{else}t1{/if}">
                        <div class="text-center"><a href="javascript:;" onclick="parent.chgCourse({$v.0}, {$nEnv}, 1,'SYS_04_02_001');return false;" class="cssAnchor">{$v.5}</a></div>
                    </td>
                    <td class="{if $sort == 'exam'}t2{else}t1{/if}">
                        <div class="text-center"><a href="javascript:;" onclick="parent.chgCourse({$v.0}, {$nEnv}, 1,'SYS_04_02_002');return false;" class="cssAnchor">{$v.6}</a></div>
                    </td>
                    <td class="{if $sort == 'questionnaire'}t2{else}t1{/if}">
                        <div class="text-center"><a href="javascript:;" onclick="parent.chgCourse({$v.0}, {$nEnv}, 1,'SYS_04_02_003');return false;" class="cssAnchor">{$v.7}</a></div>
                    </td>
                    <td class="t1 hidden-tablet hidden-phone">
                        <div class="text-right" style="margin-right: 0.5em; white-space: normal; overflow: visible;">{$v.9}</div>
                    </td>
                </tr>
                {/foreach}
            </table>
        </div>
    </div>
    <div id="pageToolbar" class="paginate"></div>
</div>
<script type="text/javascript" src="/lib/xmlextras.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/kc-paginate.js"></script>
{include file = "common/paginate_jsdeclare.tpl"}
<script type="text/javascript">
    {$inlineMajorJS}
    {literal}
	$(function() {
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
	        'onSelectPage': function(num, size) {
	            if (page_no == 0) return;
	            if (num == 0) return;
	            if (num == page_no) {
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

