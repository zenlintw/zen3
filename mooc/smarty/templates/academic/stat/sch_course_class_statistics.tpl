<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<link href="{$appRoot}/theme/default/learn_mooc/application.css" rel="stylesheet" />
<link href="{$appRoot}/theme/{$sysSession->theme}/teach/wm.css" rel="stylesheet" />
<link href="{$appRoot}/theme/{$sysSession->theme}/learn_mooc/peer.css" rel="stylesheet" />

<link rel="stylesheet" href="{$appRoot}/lib/jquery/css/jquery-ui-1.8.22.custom.css" >
<script src="{$appRoot}/lib/jquery/jquery.min.js"></script>
<script src="{$appRoot}/lib/jquery/jquery-ui-1.8.22.custom.min.js"></script>

<script type="text/javascript" src="{$appRoot}/lib/Stupid-Table-Plugin/stupidtable.min.js" ></script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/sch_course_class_statistics.js"></script>
<script type="text/javascript" src="{$appRoot}/academic/stat/sch_statistics.js"></script>
{if $user_lang === 'GB2312'}
    <script type="text/javascript" src="{$appRoot}/lib/jquery/ui/jquery.ui.datepicker-zh-CN.js"></script>
{elseif $user_lang === 'Big5'}
    <script type="text/javascript" src="{$appRoot}/lib/jquery/ui/jquery.ui.datepicker-zh-TW.js"></script>
{elseif $user_lang === 'EUC-JP'}
    <script type="text/javascript" src="{$appRoot}/lib/jquery/ui/jquery.ui.datepicker-ja.js"></script>
{/if}
<style>
{literal}
.ui-datepicker select.ui-datepicker-year { width: 38%;}

label {
    font-size: 1em;
}
{/literal}
</style>

<div style="width: 1100px; margin: auto auto;">
    <h3><span>{'course_class_statistics'|WM_Lang}</span></h3>
    <!-- search -->
    <div class="box box-padding-t-1 box-padding-lr-3 box-padding-b-3" align="center">
        <form method="POST" action="{$appRoot}/academic/stat/sch_course_class_statistics.php">
            <table cellpadding="4" cellspacing="0" width="100%" style="font-weight: bold;">
                <tr>
                    <td width="10%"></td>
                    <td width="10%"></td>
                    <td width="20%"></td>
                    <td width="20%"></td>
                    <td width="20%"></td>
                    <td width="20%"></td>
                </tr>
                <tr>
                    <td colspan="6">
                        <label>
                            <input type="checkbox" name="pre_switch_st_during" value='1' {if $post.switch_st_during != ''}checked{/if}>
                            <span style="vertical-align: middle;">{'interval_end_of_the_course'|WM_Lang} : </span>
                            <input id='st_begin' type="text" class="span2" name="pre_st_begin" value='{$post.st_begin}' style="height: 30px;"><span style="vertical-align: middle;">~</span>
                            <input id='st_end' type="text" class="span2" name="pre_st_end" value='{$post.st_end}' style="height: 30px;">
                        </label>
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        <label>
                            <input type="radio" name="pre_course_stat" value="1" {if $post.course_stat == 1}checked{/if}><span style="vertical-align: middle;">{'show_only_now_commenced_courses'|WM_Lang}</span>
                        </label>
                        <label>
                            <input type="radio" name="pre_course_stat" value="2" {if $post.course_stat == 2}checked{/if}><span style="vertical-align: middle;">{'show_only_the_end_of_the_course'|WM_Lang}</span>
                        </label>
                        <label>
                            <input type="radio" name="pre_course_stat" value="3" {if $post.course_stat == 3}checked{/if}><span style="vertical-align: middle;">{'show_all_courses'|WM_Lang}</span>
                        </label>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td colspan="3"></td>
                    <td colspan="2" align="right">{'keywords'|WM_Lang} : <input type="text" class="span2" name="pre_courseName" value='{$post.courseName}' style="height: 30px;" placeholder="{'course_title'|WM_Lang}">&nbsp&nbsp&nbsp<button type="submit" class="btn btn-default"><i class="icon-search"></i>{'search'|WM_Lang}</button></td>
                </tr>
            </table>
        </form>
    </div>
    <!-- search end-->
</div>

<br />
<div style="margin: auto auto; width: 1100px;">
    <div class="pull-right" style="margin-bottom: 5px;">
        <form action="/academic/stat/sch_course_class_statistics_exportdata.php" method="POST">
            <button type='submit' class="btn btn-primary btn-blue add span2">{'export'|WM_Lang}</button>
        </form>
        {*<form action="" method="">
            <button type='submit' class="btn btn-primary btn-blue add span2">{'export'|WM_Lang}XML</button>
        </form>*}
    </div>
    <br />
    <div class="clearfix"></div>
    <div class="box box-padding-lr-3 box-padding-b-3" style="padding:3em">
        <div class="div-border">
            <table class="bttable" cellpadding="5">
                <thead>
                    <tr>
                        <th class="text-left" data-sort="string" width="43%">{'course_title'|WM_Lang}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                        <th class="text-right" data-sort="int" width="10%">{'enrollment'|WM_Lang}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                        <th class="text-right" data-sort="int" width="10%">{'after_the_number_of_classes'|WM_Lang}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                        <th class="text-right" data-sort="string" width="10%">{'after_class_rate'|WM_Lang}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                        <th class="text-right" data-sort="string" width="10%">{'by_number_of_people'|WM_Lang}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                        <th class="text-right" data-sort="string" width="10%">{'by_rate'|WM_Lang}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                        <th class="text-center" width="7%">{'detail'|WM_Lang}</th>
                    </tr>
                </thead>

                <tbody>
                    {if $courseDataList|@count > 0}
                    {foreach from=$courseDataList key=k item=v}
                    <form action="/academic/stat/sch_course_class_statistics_detail.php" method="POST">
                        <input type="hidden" name="pre_select_page" value='{$pos.select_page}'>
                        <input type="hidden" name='pre_switch_st_during' value='{$post.switch_st_during}'>
                        <input type="hidden" name='pre_st_begin' value='{$post.st_begin}'>
                        <input type="hidden" name='pre_st_end' value='{$post.st_end}'>
                        <input type="hidden" name='pre_course_stat' value='{$post.course_stat}'>
                        <input type="hidden" name='pre_courseName' value='{$post.courseName}'>

                        <tr>
                            <td class="text-left breakword" style="width:480px;">{$v.caption}</td>
                            <td class="text-right">{$v.studentCnt}</td>
                            <td class="text-right">{$v.finishCount}</td>
                            <td class="text-right">{$v.finishPercent}</td>
                            <td class="text-right">{$v.passCount}</td>
                            <td class="text-right">{$v.passPercent}</td>
                            <td class="text-center"><button type="submit" class="btn btn-primary btn-blue detailBtn" cid={$v.cid}>{'detail'|WM_Lang}</button></td>
                        </tr>
                        <input type="hidden" name="cid" value={$v.cid}>
                        <input type="hidden" name="className" value={$v.caption}>
                        <input type="hidden" name="passCnt" value={$v.passCount}>
                        <input type="hidden" name="finishCnt" value={$v.finishCount}>
                        <input type="hidden" name="studentCnt" value={$v.studentCnt}>
                    </form>
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
            <form id='frmPager' action="{$appRoot}/academic/stat/sch_course_class_statistics.php" method="POST">
                <input id='page_act' type="hidden" name="page_act" value=''>

                <input type="hidden" name='pre_switch_st_during' value='{$post.switch_st_during}'>
                <input type="hidden" name='pre_st_begin' value='{$post.st_begin}'>
                <input type="hidden" name='pre_st_end' value='{$post.st_end}'>
                <input type="hidden" name='pre_course_stat' value='{$post.course_stat}'>
                <input type="hidden" name='pre_courseName' value='{$post.courseName}'>
                <input type="hidden" name='pre_select_page' value='{$post.select_page}'>
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
                            <span class="paginate-number-before"></span><input id='mv_type_page' data-act='mv_type_page' type="text" class="paginate-number" name="pre_select_page" onchange="$(#frmPager).submit" value={$pos.select_page}><span class="paginate-number-after">/ {$pos.total_page}</span>
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
                            <a id='mv_last_page' class="undefined" data-act="mv_last_page" href="javascript:;" title="{'last_page'|WM_Lang}">
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

<script>
    var total_page = {$pos.total_page};
{literal}
    $('#mv_first_page, #mv_pre_page, #mv_next_page, #mv_last_page').click(function() {
        console.log($(this).data('act'));
        $('#page_act').attr('name', 'pageAct' );
        $('#page_act').val( $(this).data('act') );
        $('#frmPager').submit();
    });

    $(function() {
        var radios = $('input:radio[name=pre_course_stat]');
        if(radios.is(':checked') === false) {
            radios.filter('[value=1]').prop('checked', true);
        }
    });
    // BUG TODO
    $(function(){
        $('#st_begin').datepicker({

            changeMonth: true,
            changeYear: true,
            //minDate: "",
            //maxDate: "",
            numberOfMonths:1,
            onSelect: function(selected){
                $("#edate").datepicker("option","minDate", selected)
            }
        });
        $('#st_end').datepicker({
            changeMonth: true,
            changeYear: true,
            //minDate: new Date($('sdate').val()),
            //maxDate: "",
            numberOfMonths: 1,
            onSelect: function(selected){
                $("#sdate").datepicker("option","maxDate", selected)
            }
        });
    });
{/literal}
</script>
