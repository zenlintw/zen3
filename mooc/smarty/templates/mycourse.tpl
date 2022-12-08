<link href="{$appRoot}/public/css/mycourse.css" rel="stylesheet" />
<div id="mycourse-container">
    <table class="table table-bordered mycouse-searchbar">
        <tbody>
        <tr>
            <td>
                {if $profile.isTeacher >= 1}
                <div id="mycourse-dropdown" class="dropdown pull-left">
                    <button class="btn btn-default dropdown-toggle" type="button" id="mycourse-dropdown-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        {if $kind == 'learn'}{'msg_showallmycourse'|WM_Lang}{/if}
                        {if $kind == 'teach'}{'msg_showallmyteachcourse'|WM_Lang}{/if}
                        <span class="caret"></span>
                    </button>
                    <ul id="mycourse-dropdown-menu" class="dropdown-menu" aria-labelledby="mycourse-dropdown-btn">
                        <li><a href="javascript: void(0);" data-target="myLearningCourses">
                                {'msg_showallmycourse'|WM_Lang}
                        </a></li>
                        <li><a href="javascript: void(0);" data-target="myTeachingCourses">
                                {'msg_showallmyteachcourse'|WM_Lang}
                        </a></li>
                    </ul>
                </div>
                {/if}
                
                <div class="pull-right" style="white-space:nowrap;">
                    <input id="inputKeyword" name="query" type="text" value="{$query}" placeholder="{'searchcourse'|WM_Lang}">
                    <input type="hidden" id="selectPage" name="selectPage" value="1">
                    <input type="hidden" id="inputIssuesPerPage" name="inputIssuesPerPage" value="5" />
                    <a href="javascript: void(0);" id="searchBtn" ><i class="icon-search"></i></a>
                    <a href="javascript: void(0);" title="{'list_type_icon'|WM_Lang}" id="btnListTypeIcon" class="listtype_icon"><img src="/public/images/icon_listtype_icon.png" /></a>
                    <a href="javascript: void(0);" title="{'list_type_list'|WM_Lang}" id="btnListTypeList" class="listtype_list"> <img src="/public/images/icon_listtype_list.png" /></a>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <div id="listtype_list_container">
        <div id="listtype_list_content"></div>
        <div id='pageToolbar'></div>
    </div>
    <div id="listtype_icon_container">
        <div id="main">
            <div style="height:100%">
                <ul id="tiles">
                    <div class="lcms-items"></div>
                </ul>
            </div>
            <div class="lcms-nav-bottom"></div>
        </div>
    </div>
<script type="text/javascript" src="{$appRoot}/lib/jquery.cookie.js"></script>
<script type="text/javascript" src="{$appRoot}/theme/default/wookmark/jquery.wookmark.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/kc-paginate.js"></script>
{include file = "common/paginate_jsdeclare.tpl"}
<script type="text/javascript">
    var curEnv='1';
    var kind='{$kind}';
    var appRoot = '{$appRoot}';
    var is_independent = '{$is_independent}'; //是否為獨立校
    var WM_Lang_titel_type='{'titel_type'|WM_Lang}';
    var WM_Lang_td_nowrite_homework='{'td_nowrite_homework'|WM_Lang}';
    var WM_Lang_questionnaire='{'questionnaire'|WM_Lang}';
    var WM_Lang_exam='{'exam'|WM_Lang}';
    var WM_Lang_peerassignment='{'peerassignment'|WM_Lang}';
    var WM_Lang_instructor='{'instructor'|WM_Lang}';
    var WM_Lang_openingperiod='{'openingperiod'|WM_Lang}';
    var WM_Lang_now='{'now'|WM_Lang}';
    var WM_Lang_to2='{'to2'|WM_Lang}';
    var WM_Lang_forever='{'forever'|WM_Lang}';
    var WM_Lang_other='{'other'|WM_Lang}';
    var WM_Lang_msg_showallmycourse='{'msg_showallmycourse'|WM_Lang}';
    var WM_Lang_btn_drop_elective='{'btn_drop_elective'|WM_Lang}';
    var WM_Lang_msg_cs_prepare='{'msg_cs_prepare'|WM_Lang}';
    var WM_Lang_edit_course='{'edit_course'|WM_Lang}';
    var WM_Lang_msg_select_course='{'msg_select_course'|WM_Lang}';
    var WM_Lang_msg_mod_success='{'msg_mod_success'|WM_Lang}';
    var WM_Lang_disable='{'disable'|WM_Lang}';
    var WM_Lang_confirmwithdisable='{'confirmwithdisable'|WM_Lang}';
    var WM_Lang_search_no_courses='{'search_no_courses'|WM_Lang}';
    var WM_Lang_td_student_number='{'td_student_number'|WM_Lang}';
    var MSGopeningperiod = '{'openingperiod'|WM_Lang}';
    var confirmwithdrawal = '{'confirmwithdrawal'|WM_Lang}'
    var withdrawalfail = '{'withdrawalfailure'|WM_Lang}'
    var note = '{'note'|WM_Lang}',
            wechatsharenote = '{'wechatsharenote'|WM_Lang}',
            nowlang = '{$nowlang}',
            linesharenote = '{'linesharenote'|WM_Lang}',
            socialShare = {$socialShare|@json_encode},
            cursch = '{$schoolId}';
    {literal}
    if (kind === 'teach') {
        curEnv = '2';
    }
    {/literal}
</script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/mycourse.js?{$smarty.now}"></script>
