<link href="/public/css/school.css" rel="stylesheet" />
{literal}
    <style>
        .navbar {
            background: rgb(5,178,146);
            height: initial;
        }

        .container {
            height: auto !important;
        }

        #site_header {
            max-width: 1080px;
            width: 1080px;
            margin: auto;
            height:50px;
            background:#000000;
        }

        #search_course_keyword{
            width: 338px;
            height: 36px;
            font-size: 15px;
            margin-top: 15px;
            -webkit-border-radius: 5px 0px 0px 5px;
            -moz-border-radius: 5px 0px 0px 5px;
            border-radius: 5px 0px 0px 5px;
            border: 1px solid #ccc;
            border-style: solid none solid solid;
        }

        #btn_search {
            height: 36px;
            margin-left: -10px;
            margin-bottom: 3px;
            -webkit-border-radius: 0px 5px 5px 0px;
            -moz-border-radius: 0px 5px 5px 0px;
            border-radius: 0px 5px 5px 0px;
        }

        .navbar-nav-item-background {
            border-radius: 30px;
            width: 110px;
            background: rgba(100%,100%,100%,0.3);
        }

        /*平板直向、平板橫向*/
        @media (min-width: 768px) and (max-width: 992px) {
            .siteLogo {
                width: 100%;
            }
        }

        @media (max-width: 767px) {
            #site_header_content {
                width: 100%;
                max-width: 100%;
            }

            #site_header {
                max-width: 100%;
                width: 100%;
            }
        }

        /*大手機尺寸*/
        @media (min-width: 375px) and (max-width: 767px) {
            .siteHeaderOuterTable {
                width: 94%;
                height:50px;
            }

            .siteLogo {
                margin-top:0px;
            }
        }

        /*手機尺寸*/
        @media (max-width: 374px) {
            .siteHeaderOuterTable {
                width: 90%;
                height:50px;
            }

            .siteLogo {
                margin-top:0px;
            }
        }

    </style>
{/literal}

<div id="site_header">
    <table border="0" cellpadding="0" cellspacing="0" class="siteHeaderOuterTable">
        <tr>
            <td class="siteLogo"  style="vertical-align: middle;">
                <a href="/mooc/index.php"><img class="siteLogo" src="/base/{$schoolId}/door/tpl/logo.png?{$time}" alt="回首頁" title="回首頁" border="0"></a>
                <span style="width:0px" id="logo-bottom">&nbsp;</span>
            </td>
        </tr>
    </table>
</div>
<div>
    <nav id="navbar-course" class="navbar navbar-default navigation-clean-button" style="font-size:18px;margin-bottom: 0px;">
        <div class="container" style="min-width: initial;padding-left: 0px; padding-right: 0px;">
            <div class="visible-xs hidden-tablet hidden-desktop" style="padding-top:5px;">
                <div class="row col-xs-12" style="padding-right:0px;">
                    <div class="col-xs-1 text-center" style="padding:5px 0 0 5px;"><i class="fa fa-bars" style="color:#FFFFFF;font-size: 30px;" onclick="showPhoneMenu();"></i></div>
                    <div class="col-xs-11 text-left" style="padding-right: 0px;line-height: 38px;">{$course_name}</div>
                </div>
            </div>
        </div>
    </nav>
</div>
<div id="phoneMenu" style="display:none;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="visible-xs">
        <tr><td colspan="2" class="phoneMenuItem"><a href="/mooc/user/mycourse.php"><i class="fa fa-sign-out" aria-hidden="true"></i>&nbsp;&nbsp;離開課程</a></td></tr>
        {if $course_menus|@count > 0 }
        {foreach from=$course_menus key=k item=v}
        {if $v.level eq 1}
        <tr><td colspan="2" class="phoneMenuItemB">{$v.title}</td></tr>
        {else}
            {if $smarty.server.SCRIPT_NAME eq $v.href}
            <tr><td class="phoneMenuItemC">{$v.title}</td><td class="phoneMenuItemC" style="text-align: center;"><i class="fa fa-angle-right" aria-hidden="true" style="font-size: 20px;"></i></td></tr>
            {elseif $v.href eq 'javascript:goBoard(1);' && $nowBulletinBoard eq '1'}
            <tr><td class="phoneMenuItemC">{$v.title}</td><td class="phoneMenuItemC" style="text-align: center;"><i class="fa fa-angle-right" aria-hidden="true" style="font-size: 20px;"></i></td></tr>
            {else}
                {if $v.href neq '/mooc/course_info.php'}
                <tr><td class="phoneMenuItem"><a href="{$v.href}"{if $v.target neq "default"} target="_blank"{/if}>{$v.title}</a></td><td class="phoneMenuItem" style="text-align: center;"><i class="fa fa-angle-right" aria-hidden="true" style="font-size: 20px;"></i></td></tr>
                {/if}
            {/if}
        {/if}
        {/foreach}
        {/if}
    </table>
</div>
<form name="node_list" method="POST" style="display: none;">
    <input type="hidden" name="cid">
    <input type="hidden" name="bid">
    <input type="hidden" name="nid">
</form>
<script>
    var server_name = '{$smarty.server.SERVER_NAME}';
    var baseUri = '';
    {* 載入所需的提示訊息 *}
    {php}
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/sysbar.php');
    global $MSG;
    global $sysSession;

    $outJsVars = array(
        'courseId'              => $sysSession->course_id,
        'courseBulletin'        => intval(dbGetOne('WM_term_course','bulletin',sprintf('course_id=%d',$sysSession->course_id))),
        'MSG_SysError'          => $MSG['system_error'][$sysSession->lang],
        'MSG_NotSupportBrowser' => $MSG['not_support_browser'][$sysSession->lang],
        'MSG_CantLoadLib'       => $MSG['need_lib'][$sysSession->lang],
        'MSG_NoTitle'           => $MSG['no_title'][$sysSession->lang],
        'MSG_NEED_VARS'         => $MSG['msg_need_vars'][$sysSession->lang],
        'MSG_DATA_ERROR'        => $MSG['msg_data_error'][$sysSession->lang],
        'MSG_IP_DENY'           => $MSG['msg_ip_deny'][$sysSession->lang],
        'MSG_ADMIN_ROLE'        => $MSG['msg_admin_role'][$sysSession->lang],
        'MSG_DIRECTOR_ROLE'     => $MSG['msg_director_role'][$sysSession->lang],
        'MSG_TEACHER_ROLE'      => $MSG['msg_teacher_role'][$sysSession->lang],
        'MSG_STUEDNT_ROLE'      => $MSG['msg_student_role'][$sysSession->lang],
        'MSG_SLID_ERROR'        => $MSG['msg_sid_error'][$sysSession->lang],
        'MSG_CAID_ERROR'        => $MSG['msg_caid_error'][$sysSession->lang],
        'MSG_CSID_ERROR'        => $MSG['msg_csid_error'][$sysSession->lang],
        'MSG_CS_DELTET'         => $MSG['msg_course_delete'][$sysSession->lang],
        'MSG_CS_NOT_OPEN'       => $MSG['msg_course_close'][$sysSession->lang],
        'MSG_BAD_BOARD_ID'      => $MSG['msg_bad_board_id'][$sysSession->lang],
        'MSG_BAD_BOARD_RANGE'   => $MSG['msg_bad_board_range'][$sysSession->lang],
        'MSG_BOARD_NOTOPEN'     => $MSG['msg_board_notopen'][$sysSession->lang],
        'MSG_BOARD_CLOSE'       => $MSG['msg_board_closed'][$sysSession->lang],
        'MSG_BOARD_DISABLE'     => $MSG['msg_board_disable'][$sysSession->lang],
        'MSG_BOARD_TAONLY'      => $MSG['msg_board_taonly'][$sysSession->lang],
        'MSG_IN_CHAT_ROOM'      => $MSG['msg_in_chat'][$sysSession->lang]
    );
    foreach($outJsVars as $k => $v) {
        echo sprintf("var %s = '%s';\r\n", $k, $v);
    }
    {/php}

    {literal}
        var isPhoneMenuVisible = false;
        function showPhoneMenu() {
            if ($('#phoneMenu').is(":visible")) {
                $('#phoneMenu').hide();
            } else {
                $('#phoneMenu').show();
            }
            isPhoneMenuVisible = $('#phoneMenu').is(":visible");
        }

        function gotoExplorer(which) {
            document.frmGotoExp.id.value = which;
            document.frmGotoExp.submit();
        }

        function ExplorerCategory(rootGroupId, categoryId) {
            document.frmGotoExp.rootGroupId.value = rootGroupId;
            document.frmGotoExp.course_category.value = categoryId;
            document.frmGotoExp.submit();
        }

        function gotoLogin() {
            $('<form action="/mooc/login.php" method="post"></form>').appendTo('body').submit();
        }

        $(function () {
            $(window).resize(function () {
                var width = $('#realname').width();
                $('#realname').parent('td').width(width + 80 + 'px');
            }).resize();
            $('.dropdown-toggle').dropdown();
        });
    {/literal}
</script>
<script language="javascript" src="/public/js/common.js"></script>
<script language="javascript" src="/mooc/public/js/site_header.js"></script>
<script language="javascript" src="/mooc/public/js/course_header.js"></script>
<div width="100%" style="margin-top: -1px;width: 100%; height: 100%">
    <div class="clearboth"></div>