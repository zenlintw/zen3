<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<link href="{$appRoot}/theme/default/learn_mooc/application.css" rel="stylesheet" />
<link href="{$appRoot}/theme/{$sysSession->theme}/teach/wm.css" rel="stylesheet" />
<link href="{$appRoot}/theme/{$sysSession->theme}/learn_mooc/peer.css" rel="stylesheet" />
<script type="text/javascript" src="{$appRoot}/lib/jquery/jquery.min.js" ></script>
<script type="text/javascript" src="{$appRoot}/lib/Stupid-Table-Plugin/stupidtable.min.js" ></script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/sch_course_class_statistics_detail_content.js"></script>
<script type="text/javascript" src="{$appRoot}/academic/stat/sch_statistics.js"></script>

<div style="width: 1100px; margin: auto auto;">
    <h3>
        <span>
            <form id="myForm" method="POST" action="{$appRoot}/academic/stat/sch_course_class_statistics_detail.php">
                <a href="javascript:;" onclick="$('#prefrm').submit();">{'detailed_statistics_class_curriculum'|WM_Lang}</a>
                {" / "}<a href="#" onclick="document.getElementById('myForm').submit();">{$post.className}</a>{" / "}{'student_list'|WM_Lang}
                <input type="hidden" name="cid" value={$post.cid}>
                <input type="hidden" name="className" value={$post.className}>
                <input type="hidden" name="passCnt" value={$post.passCnt}>
                <input type="hidden" name="finishCnt" value={$post.finishCnt}>
                <input type="hidden" name="studentCnt" value={$post.studentCnt}>

                <input type="hidden" name="pre_select_page" value='{$post.pre_select_page}'>
                <input type="hidden" name='pre_switch_st_during' value='{$post.pre_switch_st_during}'>
                <input type="hidden" name='pre_st_begin' value='{$post.pre_st_begin}'>
                <input type="hidden" name='pre_st_end' value='{$post.pre_st_end}'>
                <input type="hidden" name='pre_course_stat' value='{$post.pre_course_stat}'>
                <input type="hidden" name='pre_courseName' value='{$post.pre_courseName}'>
            </form>
        </span>
    </h3>
        <form method="POST" action="{$appRoot}/academic/stat/sch_course_class_statistics_detail_content_exportdata.php">
            <div class="pull-right" style="margin-bottom: 5px;"><button type="submit" class="btn btn-primary btn-blue add span2">{'export'|WM_Lang}</button></div>
            <input type="hidden" name="cid" value={$post.cid}>
            <input type="hidden" name="className" value={$post.className}>
        </form>
        <!-- search -->
        <form method="POST" action="/academic/stat/sch_course_class_statistics_detail_content.php">

            <div class="pull-right" style="margin-bottom: 5px;"><button type="submit" class="btn btn-primary btn-blue add span2" onclick="">{'search'|WM_Lang}</button></div>
            <div class="pull-right" style="margin-bottom: 5px;"><input type="text" name="std_id" value="{$post.std_id}" style="height:30px"></div>
            <div class="pull-right" style="margin-bottom: 5px;">
                <select name="search_opt" style="height: 30px; width: 80px;">
                    <option value="srbyId" {if $post.search_opt == 'srbyId'}selected{/if}>{'msg_username'|WM_Lang}</option>
                    <option value="srbyName" {if $post.search_opt == 'srbyName'}selected{/if}>{'msg_realname'|WM_Lang}</option>
                </select>
            </div>
            <input type="hidden" name="cid" value={$post.cid}>
            <input type="hidden" name="className" value={$post.className}>
            <input type="hidden" name="passCnt" value={$post.passCnt}>
            <input type="hidden" name="finishCnt" value={$post.finishCnt}>
            <input type="hidden" name="studentCnt" value={$post.studentCnt}>

            <input type="hidden" name="pre_select_page" value='{$post.pre_select_page}'>
            <input type="hidden" name='pre_switch_st_during' value='{$post.pre_switch_st_during}'>
            <input type="hidden" name='pre_st_begin' value='{$post.pre_st_begin}'>
            <input type="hidden" name='pre_st_end' value='{$post.pre_st_end}'>
            <input type="hidden" name='pre_course_stat' value='{$post.pre_course_stat}'>
            <input type="hidden" name='pre_courseName' value='{$post.pre_courseName}'>
        </form>
        <!-- search end-->
        <div class="clearfix"></div>
        
        <div class="box box-padding-lr-3 box-padding-b-3" style="padding:3em">
            <div class="div-border">
                <table class="bttable" cellpadding="5">
                    <thead>
                        <tr>
                            <th class="text-left" data-sort="string" width="35%">{'account_name'|WM_Lang}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-center" data-sort="int" width="7%">{'sex'|WM_Lang}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-center" data-sort="int" width="7%">{'age'|WM_Lang}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-center" data-sort="string" width="7%">{'educational_background'|WM_Lang}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-center" data-sort="string" width="7%">{'identity'|WM_Lang}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-center" data-sort="string"  width="12%">{'source_region_country'|WM_Lang}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-center" width="9%">{'it_has_completed'|WM_Lang}</th>
                            <th class="text-center" width="9%">{'it_has_been_through'|WM_Lang}</th>
                        </tr>
                    </thead>

                    <tbody>
                        {if $courseStudentData|@count != 0}
                            {foreach from=$courseStudentData key=k item=v}
                                <tr>
                                    <td class="text-left" style="width:480px;">{$v.userName}{" ( "}{$v.last_name} {$v.first_name}{" )"}</td>
                                    <td class="text-center">
                                        {if $v.gender == "M"}
                                            {'male'|WM_Lang}
                                        {elseif $v.gender == "F"}
                                            {'female'|WM_Lang}
                                        {else}
                                            {'not_marked'|WM_Lang}
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        {if $v.age === '' || $v.age === '0000-00-00' || $v.age === NULL}
                                            {'not_marked'|WM_Lang}
                                        {elseif $v.age === 0}
                                            {'under_one_year_old'|WM_Lang}
                                        {else}
                                            {$v.age}
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        {if $v.education == "P"}
                                            {'elementary_school'|WM_Lang}
                                        {elseif $v.education == "H"}
                                            {'junior_high_school'|WM_Lang}
                                        {elseif $v.education == "S"}
                                            {'high_school'|WM_Lang}
                                        {elseif $v.education == "U"}
                                            {'university'|WM_Lang}
                                        {elseif $v.education == "M"}
                                            {'masters_degree'|WM_Lang}
                                        {elseif $v.education == "D"}
                                            {'doctoral_degree'|WM_Lang}
                                        {elseif $v.education == "O"}
                                            {'other'|WM_Lang}
                                        {else}
                                            {'not_marked'|WM_Lang}
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        {if $v.user_status == "S"}
                                            {'student'|WM_Lang}
                                        {elseif $v.user_status == 'W'}
                                            {'at_work'|WM_Lang}
                                        {else}
                                            {'not_marked'|WM_Lang}
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        {if $v.country=="TW"}
                                            {'TW'|WM_Lang}
                                        {elseif $v.country=="CH"}
                                            {'CH'|WM_Lang}
                                        {elseif $v.country=="JA"}
                                            {'JA'|WM_Lang}
                                        {elseif $v.country=="IN"}
                                            {'IN'|WM_Lang}
                                        {elseif $v.country=="US"}
                                            {'US'|WM_Lang}
                                        {elseif $v.country=="AS"}
                                            {'AS'|WM_Lang}
                                        {elseif $v.country=="O"}
                                            {'other'|WM_Lang}
                                        {else}
                                            {'not_marked'|WM_Lang}
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        {if $v.userFinish == "1"}
                                            <font color="#00DB37">{'already_finish'|WM_Lang}</font>
                                        {else}
                                            <font color="#F5003D">{'no_finish'|WM_Lang}</font>
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        {if $v.userPass == "1"}
                                            <font color="#00DB37">{'already_pass'|WM_Lang}</font>
                                        {else}
                                            <font color="#F5003D">{'no_pass'|WM_Lang}</font>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}

                        {else}
                            <tr>
                                <td colspan="8">{"( "}{'msg_empty_data'|WM_Lang}{" ) "}</td>
                            </tr>
                        {/if}
                     </tbody>
            </table>
         </div>
         {if $data_count > 1}
            <div align="center">
            <form id='frmPager' action="{$appRoot}/academic/stat/sch_course_class_statistics_detail_content.php" method="POST">
                <input type="hidden" name="cid" value={$post.cid}>
                <input type="hidden" name="className" value={$post.className}>
                <input type="hidden" name="passCnt" value={$post.passCnt}>
                <input type="hidden" name="finishCnt" value={$post.finishCnt}>
                <input type="hidden" name="studentCnt" value={$post.studentCnt}>

                <input type="hidden" name="pre_select_page" value='{$post.pre_select_page}'>
                <input type="hidden" name='pre_switch_st_during' value='{$post.pre_switch_st_during}'>
                <input type="hidden" name='pre_st_begin' value='{$post.pre_st_begin}'>
                <input type="hidden" name='pre_st_end' value='{$post.pre_st_end}'>
                <input type="hidden" name='pre_course_stat' value='{$post.pre_course_stat}'>
                <input type="hidden" name='pre_courseName' value='{$post.pre_courseName}'>

                <input id='page_act' type="hidden" name="page_act" value=''>

                <table style="text-align: center; padding-top: 20px;">
                    <tr>
                        <td>
                            {if $pos.select_page == 1}
                            <a class="undefined disabled" title="{'first_page'|WM_Lang}">
                            {else}
                            <a id='mv_first_page' class="undefined" data-act="mv_first_page" href="javascript:;" title="{'first_page'|WM_Lang}">
                            {/if}
                                <i class="paginate-first"></i>
                            </a>
                        </td>
                        <td>
                            {if $pos.select_page == 1}
                            <a class="undefined disabled" title="{'prev_page'|WM_Lang}">
                            {else}
                            <a id='mv_pre_page' class="undefined" data-act="mv_pre_page" href="javascript:;" title="{'prev_page'|WM_Lang}">
                            {/if}
                                <i class="paginate-prev"></i>
                            </a>
                        </td>
                        <td>
                            <span class="paginate-number-before"></span><input id='mv_type_page' data-act='mv_type_page' type="text" class="paginate-number" name="select_page" onchange="$(#frmPager).submit" value='{$pos.select_page}'><span class="paginate-number-after">/ {$pos.total_page}</span>
                        </td>
                        <td>
                            {if $pos.select_page == $pos.total_page}
                            <a class="undefined disabled" title="{'next_page'|WM_Lang}">
                            {else}
                            <a id='mv_next_page' class="undefined" data-act="mv_next_page" type="submit" href="javascript:;" title="{'next_page'|WM_Lang}">
                            {/if}
                                <i class="paginate-next"></i>
                            </a>
                        </td>
                        <td>
                            {if $pos.select_page == $pos.total_page}
                            <a class="undefined disabled" title="{'last_page'|WM_Lang}">
                            {else}
                            <a id='mv_last_page' class="undefined" data-act="mv_last_page"  href="javascript:;" title="{'last_page'|WM_Lang}">
                            {/if}
                                <i class="paginate-last"></i>
                            </a>
                        </td>
                    </tr>
                </table>
            </form>
            </div>
            {/if}
    </div>
</div>
<form id='prefrm' action="{$appRoot}/academic/stat/sch_course_class_statistics.php" method="POST">
    <input type="hidden" name="pre_select_page" value='{$post.pre_select_page}'>
    <input type="hidden" name='pre_switch_st_during' value='{$post.pre_switch_st_during}'>
    <input type="hidden" name='pre_st_begin' value='{$post.pre_st_begin}'>
    <input type="hidden" name='pre_st_end' value='{$post.pre_st_end}'>
    <input type="hidden" name='pre_course_stat' value='{$post.pre_course_stat}'>
    <input type="hidden" name='pre_courseName' value='{$post.pre_courseName}'>
</form>

<script>
    var total_page = {$pos.total_page};
{literal}
    $('#mv_first_page, #mv_pre_page, #mv_next_page, #mv_last_page').click(function() {
        //console.log($(this).data('act'));
        $('#page_act').attr('name', 'pageAct' );
        $('#page_act').val( $(this).data('act') );
        $('#frmPager').submit();
    });

{/literal}
</script>