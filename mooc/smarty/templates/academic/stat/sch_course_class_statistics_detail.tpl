<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<link href="{$appRoot}/theme/default/learn_mooc/application.css" rel="stylesheet" />
<link href="{$appRoot}/theme/{$sysSession->theme}/teach/wm.css" rel="stylesheet" />
<link href="{$appRoot}/theme/{$sysSession->theme}/learn_mooc/peer.css" rel="stylesheet" />
<script type="text/javascript" src="{$appRoot}/lib/jquery/jquery.min.js" ></script>
<script type="text/javascript" src="{$appRoot}/lib/Stupid-Table-Plugin/stupidtable.min.js" ></script>
<script src="{$appRoot}/public/js/third_party/highcharts/highcharts.js"></script>
<script src="{$appRoot}/public/js/third_party/highcharts/exporting.js"></script>
<script type="text/javascript" src="{$appRoot}/academic/stat/sch_statistics.js"></script>

<div style="width: 1100px; margin: auto auto;">
<h3><span><a href="javascript:;" onclick="$('#prefrm').submit();">{'detailed_statistics_class_curriculum'|WM_Lang}</a>{" / "}{$post.className}</span></h3>
    <div class="box box-padding-t-1 box-padding-lr-3 box-padding-b-3">
        <form action="/academic/stat/sch_course_class_statistics_detail_content.php" method="POST">
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
        <table cellpadding="15" cellspacing="0">
                <tr>
                    <td>{'number_of_teachers'|WM_Lang} : {$teachNum}</td>
                    <td>{'the_number_of_assistants'|WM_Lang} : {$asistNum}</td>
                    <td>{'number_of_instructors'|WM_Lang} : {$lecturersNum}</td>
                    <td></td>
                </tr>
                <tr>
                    <td>{'the_number_of_students'|WM_Lang} : {$post.studentCnt}</td>
                    <td>{'complete_number'|WM_Lang} : {$post.finishCnt}</td>
                    <td>{'by_number_of_people'|WM_Lang} : {$post.passCnt}</td>
                    <td><button type="submit" class="btn btn-primary btn-blue detailBtn">{'detail'|WM_Lang}</button></td>
                </tr>
            </table>
        </form>
    </div>
</div>
<br >
<div style="width: 1100px; margin: auto auto;">
        <div class="clearfix"></div>
        <div class="box box-padding-t-1 box-padding-lr-3 box-padding-b-3">
            <div class="div-border" >
                <table class="bttable" cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr>
                            <td>{'sex'|WM_Lang}</td>
                            <td>{'age'|WM_Lang}</td>
                        </tr>
                        <tr>
                            {if $post.studentCnt != 0}
                            <td>
                                <div id="container0" style="min-width: 310px; height: 300px; max-width: 447px; margin: 0 auto"></div>
                            </td>
                            <td>
                                <div id="container1" style="min-width: 310px; height: 300px; max-width: 447px; margin: 0 auto"></div>
                            </td>
                            {else}
                            <td>
                                {"( "}{'msg_empty_data'|WM_Lang}{" ) "}
                            </td>
                            <td>
                                {"( "}{'msg_empty_data'|WM_Lang}{" ) "}
                            </td>
                            {/if}
                        </tr>
                        <tr>
                            <td>{'identity'|WM_Lang}</td>
                            <td>{'educational_background'|WM_Lang}</td>
                        </tr>
                        <tr>
                            {if $post.studentCnt != 0}
                            <td>
                                <div id="container2" style="min-width: 310px; height: 300px; max-width: 447px; margin: 0 auto"></div>
                            </td>
                            <td>
                                <div id="container3" style="min-width: 310px; height: 300px; max-width: 447px; margin: 0 auto"></div>
                            </td>
                            {else}
                            <td>
                                {"( "}{'msg_empty_data'|WM_Lang}{" ) "}
                            </td>
                            <td>
                                {"( "}{'msg_empty_data'|WM_Lang}{" ) "}
                            </td>
                            {/if}
                        </tr>
                        <tr>
                            <td>{'the_status'|WM_Lang}</td>
                            <td>{'source_region_country'|WM_Lang}</td>
                            <td></td>
                        </tr>
                        <tr>
                            {if $post.studentCnt != 0}
                            <td>
                                <div id="container4" style="min-width: 310px; height: 300px; max-width: 447px; margin: 0 auto"></div>
                            </td>
                            <td>
                                <div id="container5" style="min-width: 310px; height: 300px; max-width: 447px; margin: 0 auto"></div>
                            </td>
                            {else}
                            <td>
                                {"( "}{'msg_empty_data'|WM_Lang}{" ) "}
                            </td>
                            <td>
                                {"( "}{'msg_empty_data'|WM_Lang}{" ) "}
                            </td>
                            {/if}
                        </tr>
                     </tbody>
            </table>
         </div>
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
    // 給圖用
    var genderP = {$genderP|@json_encode};
    var statusP = {$statusP|@json_encode};
    var educationP = {$educationP|@json_encode};
    var countryP = {$countryP|@json_encode};
    var ageP = {$ageP|@json_encode};
    var roleP = {$roleP|@json_encode};

    // chart列印 多國語系用 
    var dJPEG = '{'download_JPEG'|WM_Lang}';
    var dPDF = '{'download_PDF'|WM_Lang}';
    var dPNG = '{'download_PNG'|WM_Lang}';
    var dSVG = '{'download_SVG'|WM_Lang}';
    var pChart = '{'print_chart'|WM_Lang}';

    // chart 國籍 多國語系用 
    var TWp = '{'TW'|WM_Lang}';
    var CHp = '{'CH'|WM_Lang}';
    var JAp = '{'JA'|WM_Lang}';
    var INp = '{'IN'|WM_Lang}';
    var USp = '{'US'|WM_Lang}';
    var ASp = '{'AS'|WM_Lang}';
    var Op = '{'other'|WM_Lang}';

    // chart 身分統計圖 多國語系用 
    var student = '{'student'|WM_Lang}';
    var teacher = '{'teacher'|WM_Lang}';
    var teach_asis = '{'teaching_assistant'|WM_Lang}';
    var teach_instr = '{'teaching_instructor'|WM_Lang}';

    // chart 學歷統計圖 多國語系用 
    var elementary_school = '{'elementary_school'|WM_Lang}';
    var junior_high_school = '{'junior_high_school'|WM_Lang}';
    var high_school = '{'high_school'|WM_Lang}';
    var university = '{'university'|WM_Lang}';
    var masters_degree = '{'masters_degree'|WM_Lang}';
    var doctoral_degree = '{'doctoral_degree'|WM_Lang}';

    // chart 角色統計圖 多國語系用 
    var at_work = '{'at_work'|WM_Lang}';

    // chart 性別性別統計圖 多國語系用 
    var female = '{'female'|WM_Lang}';
    var male = '{'male'|WM_Lang}';

    // chart 年齡統計圖 多國語系用 
    var year_old = '{'year_old'|WM_Lang}';
    var year_under = '{'year_under'|WM_Lang}';
    var year_above = '{'year_above'|WM_Lang}';
    var not_mark = '{'not_marked'|WM_Lang}';

    // 統計圖未標示區塊顯示 多國語係用
    var not_mark = '{'not_marked'|WM_Lang}';

</script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/sch_course_class_statistics_detail.js"></script>