<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<link href="{$appRoot}/theme/default/learn_mooc/application.css" rel="stylesheet" />
<link href="{$appRoot}/theme/{$sysSession->theme}/teach/wm.css" rel="stylesheet" />
<link href="{$appRoot}/theme/{$sysSession->theme}/learn_mooc/peer.css" rel="stylesheet" />
<link rel="stylesheet" href="{$appRoot}/lib/jquery/css/jquery-ui-1.8.22.custom.css" >
<script src="{$appRoot}/lib/jquery/jquery.min.js"></script>
<script src="{$appRoot}/lib/jquery/jquery-ui-1.8.22.custom.min.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/Stupid-Table-Plugin/stupidtable.min.js" ></script>
<script type="text/javascript" src="{$appRoot}/academic/stat/sch_statistics.js"></script>
<script src="{$appRoot}/public/js/third_party/highcharts/highcharts.js"></script>
<script src="{$appRoot}/public/js/third_party/highcharts/exporting.js"></script>


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
    <h3><span>{'title2'|WM_Lang}</span></h3>
    <!-- search -->
    <div class="box box-padding-t-1 box-padding-lr-3 box-padding-b-3" align="center">
        <form method="POST" action="{$appRoot}/academic/stat/sch_statistics.php">
            <table cellpadding="4" cellspacing="0" width="100%" style="font-weight: bold;">
                <tr>
                    <td colspan="6">
                        
                            <input type="radio" name="type_report" value='1' {if !isset($reportType)||($reportType=='1') }checked{/if}>
                            <span style="vertical-align: middle;">{'title7'|WM_Lang}</span>
                        
                    </td>
                </tr>
                <tr>
                    <td colspan="6">
                        
                            <input type="radio" name="type_report" value='2' {if ($reportType=='2') }checked{/if}>
                            <span style="vertical-align: middle;">{'title8'|WM_Lang}&nbsp;{'from'|WM_Lang}</span>
                            <input id='en_begin_date' type="text" class="span2" name="en_begin_date" value='{if empty($post.en_begin_date)}{$defaultStartWeekDate}{else}{$post.en_begin_date}{/if}' style="height: 30px;">
                            <span style="vertical-align: middle;">&nbsp;{'to'|WM_Lang}</span>
                            <input id='en_end_date' type="text" class="span2" name="en_end_date" value='{if empty($post.en_end_date)}{$defaultEndWeekDate}{else}{$post.en_end_date}{/if}' style="height: 30px;">
                        
                    </td>
                </tr>
                <tr>
                    <td colspan="6">
                        
                            <input type="radio" name="type_report" value='3'{if ($reportType=='3') }checked{/if}>
                            <span style="vertical-align: middle;">{'title9'|WM_Lang}&nbsp;{'from'|WM_Lang}{'year1'|WM_Lang}</span>
                            <select name="month_year" class="cssInput" id="month_year" style="width:75px;">
                            {section name=sYear start=2004 loop=$thisYear+1 step=1}
                            {if $post.month_year > 0}
                                <option value="{$smarty.section.sYear.index}"{if $smarty.section.sYear.index == $post.month_year} selected{/if}>{$smarty.section.sYear.index}</option>
                            {else}
                                <option value="{$smarty.section.sYear.index}"{if $smarty.section.sYear.index == $thisYear} selected{/if}>{$smarty.section.sYear.index}</option>
                            {/if}
                            {/section}
                            </select>
                            <span style="vertical-align: middle;">{'year'|WM_Lang}{'month1'|WM_Lang}</span>
                            <select name="month" class="cssInput" id="month" style="width:75px;">
                            {section name=sMonth start=1 loop=13 step=1}
                            {if $post.month > 0}
                                <option value="{$smarty.section.sMonth.index}"{if $smarty.section.sMonth.index == $post.month} selected{/if}>{$smarty.section.sMonth.index}</option>
                            {else}
                                <option value="{$smarty.section.sMonth.index}"{if $smarty.section.sMonth.index == 1} selected{/if}>{$smarty.section.sMonth.index}</option>
                            {/if}
                            {/section}
                            </select>
                            <span style="vertical-align: middle;">{'month'|WM_Lang}&nbsp;{'to'|WM_Lang}{'year1'|WM_Lang}</span>
                            <select name="month_year1" class="cssInput" id="month_year1" style="width:75px;">
                            {section name=eYear start=2004 loop=$thisYear+1 step=1}
                            {if $post.month_year > 0}
                                <option value="{$smarty.section.eYear.index}"{if $smarty.section.eYear.index == $post.month_year1} selected{/if}>{$smarty.section.eYear.index}</option>
                            {else}
                                <option value="{$smarty.section.eYear.index}"{if $smarty.section.eYear.index == $thisYear} selected{/if}>{$smarty.section.eYear.index}</option>
                            {/if}
                            {/section}
                            </select>
                            <span style="vertical-align: middle;">{'year'|WM_Lang}{'month1'|WM_Lang}</span>
                            <select name="month1" class="cssInput" id="month1" style="width:75px;">
                            {section name=eMonth start=1 loop=13 step=1}
                            {if $post.month > 0}
                                <option value="{$smarty.section.eMonth.index}"{if $smarty.section.eMonth.index == $post.month1} selected{/if}>{$smarty.section.eMonth.index}</option>
                            {else}
                                <option value="{$smarty.section.eMonth.index}"{if $smarty.section.eMonth.index == 1} selected{/if}>{$smarty.section.eMonth.index}</option>
                            {/if}
                            {/section}
                            </select>
                            <span style="vertical-align: middle;">{'month'|WM_Lang}</span>
                        
                    </td>
                </tr>
                <tr>
                    <td colspan="6">
                        
                            <input type="radio" name="type_report" value='4' {if ($reportType=='4') }checked{/if}>
                            <span style="vertical-align: middle;">{'title10'|WM_Lang}&nbsp;{'from'|WM_Lang}{'year1'|WM_Lang}</span>
                            <select name="year_year" class="cssInput" id="year_year" style="width:75px;">
                            {section name=sYear start=2004 loop=$thisYear+1 step=1}
                            {if $post.year_year > 0}
                                <option value="{$smarty.section.sYear.index}"{if $smarty.section.sYear.index == $post.year_year} selected{/if}>{$smarty.section.sYear.index}</option>
                            {else}
                                <option value="{$smarty.section.sYear.index}"{if $smarty.section.sYear.index == $thisYear} selected{/if}>{$smarty.section.sYear.index}</option>
                            {/if}
                            {/section}
                            </select>
                            <span style="vertical-align: middle;">{'year'|WM_Lang}</span>
                            <span style="vertical-align: middle;">&nbsp;{'to'|WM_Lang}{'year1'|WM_Lang}</span>
                            <select name="year_year1" class="cssInput" id="year_year" style="width:75px;">
                            {section name=eYear start=2004 loop=$thisYear+1 step=1}
                            {if $post.year_year1 > 0}
                                <option value="{$smarty.section.eYear.index}"{if $smarty.section.eYear.index == $post.year_year1} selected{/if}>{$smarty.section.eYear.index}</option>
                            {else}
                                <option value="{$smarty.section.eYear.index}"{if $smarty.section.eYear.index == $thisYear} selected{/if}>{$smarty.section.eYear.index}</option>
                            {/if}
                            {/section}
                            </select>
                            <span style="vertical-align: middle;">{'year'|WM_Lang}</span>
                        
                    </td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td colspan="3"></td>
                    <td colspan="2" align="right"><button type="submit" class="btn btn-default"><i class="icon-search"></i>{'search'|WM_Lang}</button></td>
                </tr>
            </table>
        </form>
    </div>
    <!-- search end-->
</div>

<br />
<div style="margin: auto auto; width: 1100px;">
    {if $reportType > 1}
    <div class="box box-padding-lr-3 box-padding-b-3" style="padding:3em">
        
            <div id="container0" style="min-width: 760px; height: 400px; max-width: 447px; margin: 0 auto"></div>
        
    </div>
    <br /> 
    <div class="pull-right" style="margin-bottom: 5px;">
        <form name="exportForm" id="exportForm" action="sch_cour_export.php" method="POST" target="empty">
            <button type='submit' class="btn btn-primary btn-blue add span2">{'export'|WM_Lang}</button>
            <input type="hidden" name="dataCategories" value="{$dataCategories}">
            <input type="hidden" name="dataSeries" value="{$dataSeries}">
        </form>
    </div>
    <div class="pull-right" style="margin-bottom: 5px;">
        <form name="GraphFm" id="GraphFm" action="{$action}" method="POST" target="viewGraphWin" enctype="multipart/form-data">
            <button type='button' class="btn btn-primary btn-blue add span2" onclick="viwGraph('{$action}','{$x_scale}','{$dataSeries}','{$dataMax}','{$choice_date}');">{'chart'|WM_Lang}</button>
            <input type="hidden" name="x_scale" value="{$x_scale}">
            <input type="hidden" name="y_scale" value="{$dataSeries}">
            <input type="hidden" name="max_val" value="{$dataMax}">
            <input type="hidden" name="period_date" value="{$choice_date}">
        </form>
    </div>
    {/if}
    <br />
    <div class="clearfix"></div>
    <div class="box box-padding-lr-3 box-padding-b-3" style="padding:3em">
        <div class="div-border">
            <table class="bttable" cellpadding="5">
                {if $reportType == 1}
                    <tbody><tr>
                           <td class="text-left breakword" style="width:480px;">{$reportResult}</td>
                    </tr></tbody>
                {else}
                    {$reportResult}
                {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    var reportType = '{$reportType}';
    var choice_date = '{$choice_date}';
    var total_courses = '{$total_courses}'
    var dataCategories = ['{$dataCategories}'];
    var dataSeries = [{$dataSeries}];
    var captionWeekly = '{'title2'|WM_Lang} - {'weekly_report'|WM_Lang}';
    var captionMonthly = '{'title2'|WM_Lang} - {'monthly_report'|WM_Lang}';
    var captionAnnual = '{'title2'|WM_Lang} - {'annual_report'|WM_Lang}';
    var titleTotal = '{'total'|WM_Lang}';
    var titleCoursesOpened = '{'courses_opened'|WM_Lang}';
    var titleCourseOpenedAmount = '{'course_opened_amount'|WM_Lang}';
    var titleCourseOpenedAmount2 = '{'course_opened_amount2'|WM_Lang}';
    var rotation = 90;
    if (reportType === '1') {ldelim}
        rotation = 0;
    {rdelim}
        
{literal}
    $(function(){
        $('#en_begin_date').datepicker({
            changeMonth: true,
            changeYear: true,
            numberOfMonths:1,
            dateFormat: 'yy-mm-dd'
        });
        $('#en_end_date').datepicker({
            changeMonth: true,
            changeYear: true,
            numberOfMonths: 1,
            dateFormat: 'yy-mm-dd'
        });
    });
    
    function generateReport() {
        var titleText = '';
        // if (window.console) {console.log('reportType', reportType);}
        switch(reportType) {
            case '2': titleText = captionWeekly; break;
            case '3': titleText = captionMonthly; break;
            case '4': titleText = captionAnnual; break;
        }

        $('#container0').highcharts({
            chart: {type: 'line'},
            title: {
                text: titleText,
                x: -20 //center
            },
            subtitle: {
                text: choice_date + ' '  + titleTotal + ' ' + total_courses + ' ' + titleCoursesOpened,
                x: -20
            },
            xAxis: {
                categories: dataCategories, 
                labels: {
                    step:1,
                    rotation: rotation
                }
            },
            yAxis: {
                title: {
                    text: titleCourseOpenedAmount
                },
                min: 0,
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                valueSuffix: '門課'
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            plotOptions: {
                line: {
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            series: [{
                name: titleCourseOpenedAmount2,
                data: dataSeries
            }],
            exporting: { enabled: false }
        });
    }
    
    $(function () {
        switch(reportType) {
            case '2':
            case '3':
            case '4':
                generateReport();
                break;
        }
    });
    
{/literal}
</script>
