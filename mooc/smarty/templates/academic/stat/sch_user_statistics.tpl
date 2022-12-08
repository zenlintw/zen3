<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<link href="{$appRoot}/theme/default/learn_mooc/application.css" rel="stylesheet" />
<link href="{$appRoot}/theme/{$sysSession->theme}/teach/wm.css" rel="stylesheet" />
<link href="{$appRoot}/theme/{$sysSession->theme}/learn_mooc/peer.css" rel="stylesheet" />
<link rel="stylesheet" href="{$appRoot}/lib/jquery/css/jquery-ui-1.8.22.custom.css" >
<script src="/lib/common.js"></script>
<script src="/lib/popup/popup.js"></script>
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
    <h3><span>{'title5'|WM_Lang}</span></h3>
    <!-- search -->
    <div class="box box-padding-t-1 box-padding-lr-3" align="center">
        <form method="POST" action="{$appRoot}/academic/stat/sch_user_statistics.php">
            <table cellpadding="4" border="0" cellspacing="0" width="100%" style="font-weight: bold;">
                <tr>
                    <td width="150">
                       <h4><span>{'title56'|WM_Lang}</span></h4>
                    </td>
                    <td align="left">
                        <label>
                            <input type="radio" name="ck_course_rang" value='1' {if !isset($courseRange)||($courseRange=='1') }checked{/if}>
                            <span style="vertical-align: middle;">{'title58'|WM_Lang}</span>
                        <label>
                        <P />
                        <label>
                            <input type="radio" name="ck_course_rang" value='2' {if $courseRange=='2' }checked{/if}>
                            <span style="vertical-align: middle;">{'title59'|WM_Lang}</span>
                            <input id='single_group' type="text" class="span2" name="single_group" value='{$post.single_group}' style="height: 30px;" readonly>
                            <input type="hidden" name="single_group_id" id="single_group_id" value="{$post.single_group_id}" />
                            <button type='button' name="btnImp" class="btn btn-primary btn-blue add" onclick="select_group()">{'title60'|WM_Lang}</button>
                        <label>
                        <P />
                        <label>
                            <input type="radio" name="ck_course_rang" value='3' {if $courseRange=='3'}checked{/if}>
                            <span style="vertical-align: middle;">{'title62'|WM_Lang}</span>
                            <input id='single_course' type="text" class="span2" name="single_course" value='{$post.single_course}' style="height: 30px;" readonly>
                            <input id='single_course_id' type="hidden" name="single_course_id" value='{$post.single_course_id}'>
                            <button type='button' name="btnImp" class="btn btn-primary btn-blue add" onclick="select_course()">{'title63'|WM_Lang}</button>
                        <label>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="right"><button type="button" class="btn btn-default span2" style="float:right"><i class="icon-search"></i>{'search'|WM_Lang}</button></td>
                </tr>
            </table>
        </form>
    </div>
    <!-- search end-->
</div>

<br />
<div style="margin: auto auto; width: 1100px;">
    <div class="box box-padding-lr-3 box-padding-b-3" style="padding-top:3em;">
        <table style="position: relative;  left: -1.3em;">
            <tr>
                <td>
                    <div id="container0" style="min-width: 520px; height: 400px; margin: 0 auto"></div>
                    <div style="text-align: center; margin-bottom: 5px;">
                        <form name="GraphFm" id="GraphFm" action="user_gender_graph.php" method="POST" target="viewGraphWin" enctype="multipart/form-data" style="display: inline-block;">
                            <button type='button' class="btn btn-primary btn-blue add span2" onclick="viwGraph('{$action_gender}','{$x_gender}','{$y_gender}','','');">{'chart'|WM_Lang}</button>
                            <input type="hidden" name="x_scale" value="{$x_gender}">
                            <input type="hidden" name="y_scale" value="{$y_gender}">
                            <input type="hidden" name="max_val" value="">
                            <input type="hidden" name="period_date" value="">
                        </form>
                    </div>
                </td>
                <td>
                    <div id="container1" style="min-width: 520px; height: 400px; margin: 0 auto"></div>
                    <div style="text-align: center; margin-bottom: 5px;">
                        <form name="GraphFm" id="GraphFm" action="user_role_graph.php" method="POST" target="viewGraphWin" enctype="multipart/form-data" style="display: inline-block;">
                            <button type='button' class="btn btn-primary btn-blue add span2" onclick="viwGraph('{$action_role}','{$x_role}','{$y_role}','','');">{'chart'|WM_Lang}</button>
                            <input type="hidden" name="x_scale" value="{$x_role}">
                            <input type="hidden" name="y_scale" value="{$y_role}">
                            <input type="hidden" name="max_val" value="">
                            <input type="hidden" name="period_date" value="">
                        </form>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<script>
    var reportType = '{$reportType}';
    var title_name = '{$title_name}';
    var courseCount = '{$courseCount}';
    var dataCategories = ['{$dataCategories}'];
    var dataSeries = [{$dataSeries}];
    var genderP = {$genderP|@json_encode};
    var roleP = {$roleP|@json_encode};
    var nowlang = '{$nowlang}';
    var msg = {$msg|@json_encode};
    var rotation = 90;
    if (reportType === '1') {ldelim}
        rotation = 0;
    {rdelim}
        
{literal}
    // 性別比例
    var data_gender = [];
    if (genderP.F + genderP.M + genderP.N >= 1) {
        data_gender = [{name: msg.female[nowlang], y: parseInt(genderP.F)},
            {name: msg.male[nowlang], y: parseInt(genderP.M)},
            {name: msg.not_shown[nowlang], y: parseInt(genderP.N)}
        ];   
    }
    
    // 角色比例
    var data_role = [];
    if (roleP.teacher + roleP.instructor + roleP.assistant + roleP.student + roleP.auditor >= 1) {
        data_role = [{name: msg.teacher[nowlang], y: parseInt(roleP.teacher)},
            {name: msg.instructor[nowlang], y: parseInt(roleP.instructor)},
            {name: msg.assistant[nowlang], y: parseInt(roleP.assistant)},
            {name: msg.student[nowlang], y: parseInt(roleP.student)},
            {name: msg.auditor[nowlang], y: parseInt(roleP.auditor)}
        ];  
    }
    
    // 顯示課程名稱於 repost_course 中 (course_name)
    function showCourseCaption(idx,caption) {
        var field = document.getElementById(idx);
        if(!field) return;
        field.value = caption;
    }
    
    function select_group(){
        var ret = showDialog('pickGroup.php',true,window,true,0,0,'250px','250px','scrollbars=1');
        if (!ret)
        return;
    }

    function select_course(){
        var win = new WinCourseSelect('setCourseValue');
        win.run();
    }

    function setCourseValue(cid, courseName)
    {
        var field = document.getElementById('single_course_id');
        if(!field) return;
        field.value = cid;
        document.getElementById('single_course').value = courseName;
    }

    function generateGenderReport() {
        $('#container0').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: msg.gender_ratio[nowlang]
            },
            subtitle: {
                text: title_name+'('+courseCount+msg.course_unit[nowlang] + ')'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} % <br>('+msg.number_of_people[nowlang] + '{point.y} '+msg.member[nowlang] + ')',
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        }
                    }
                }
            },
            series: [{
                name: msg.percentage_of[nowlang] + '：',
                colorByPoint: true,
                data: data_gender
            }],
            exporting: { enabled: false }
        });
    }
    
    function generateRoleReport() {
        $('#container1').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: msg.course_role_proportion[nowlang]
            },
            subtitle: {
                text: title_name+'('+courseCount+msg.course_unit[nowlang] + ')'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} % <br>('+msg.number_of_people[nowlang] + '{point.y} '+msg.member[nowlang] + ')',
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        }
                    }
                }
            },
            series: [{
                name: msg.percentage_of[nowlang] + '：',
                colorByPoint: true,
                data: data_role
            }],
            exporting: { enabled: false }
        });
    }
    $(function () {
        // generateReport();
        generateGenderReport();
        generateRoleReport();
        
        // 無學員時
        if (genderP.F + genderP.M + genderP.N === 0) {
            $('#container0').append('<div style="text-align: center; position: relative; top: -13em; width: 60%; margin: auto auto;"><div class="alert alert-info" role="alert"><strong>' + msg.no_student[nowlang] + '</strong></div></div>');
        } 
        
        // 無成員時
        if (roleP.teacher + roleP.instructor + roleP.assistant + roleP.student + roleP.auditor === 0) {
            $('#container1').append('<div style="text-align: center; position: relative; top: -13em; width: 60%; margin: auto auto;"><div class="alert alert-info" role="alert"><strong>' + msg.no_member[nowlang] + '</strong></div></div>');
        } 
        
        // 搜尋前驗證
        $('.icon-search').parents('button').click(function() {
            $('#container0, #container1').empty();
            if ($("input[name='ck_course_rang']:checked").val() === '2' && $('#single_group_id').val() === '') {
                alert(msg.choose_group[nowlang]);
                return false;
            }
            if ($("input[name='ck_course_rang']:checked").val() === '3' && $('#single_course').val() === '') {
                alert(msg.choose_course[nowlang]);
                return false;
            }    
            $(this).parents('form').submit();
        });
    });
    
{/literal}
</script>
