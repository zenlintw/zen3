<link href="/public/css/school.css" rel="stylesheet" />
{literal}
    <style>
        .container {
            height: auto !important;
        }

        #site_header {
            width: 100%;
            margin: auto;
        }

        #site_header_content {
            max-width: 1080px;
            width: 1080px;
            margin: auto;
        }

        .siteLogo {
            height: 50px;
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

        .nav .open>a, .nav .open>a:focus, .nav .open>a:hover {
            background-color: transparent;
        }

        .nav>li>a:focus, .nav>li>a:hover {
            background-color: transparent;
        }

        .site_keyword {
            font-size: 14px;
            color: #393939;
            padding-left:5px;
            padding-right:5px;
            cursor: pointer;
        }

        .button_search {
            font-family: "微軟正黑體";
            font-size: 16px;
            color: #fff;
            height: 40px;
            width: 40px;
            border: 1px rgba(255,255,255,0.5) solid;
            border-radius: 3px;
            background-color: rgba(255,255,255,0);
        }

        #div_username {
            padding-right: 0px;
            line-height: 32px;
        }

        .show-username {
            white-space: nowrap;
            overflow: hidden;
            overflow:-moz-hidden-unscrollable;
            text-overflow: ellipsis;
            max-width: 120px;
            font-size:1.2em;
            color:#FFFFFF;
            padding:0px 6px 6px 6px;
            display: inline-flex;
        }
        
        @supports (-webkit-overflow-scrolling: touch) {
            
            .fa-search {
                margin-left:-5px;
            }
            
		    .adjust {
		        margin-left:-7px;
		    }   
		}

        /* desktop */
        @media (min-width: 1320px) {
        }

        .search_box::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
            color: white;
            opacity: 1; /* Firefox */
        }

        .search_box:-ms-input-placeholder { /* Internet Explorer 10-11 */
            color: white;
        }

        .search_box::-ms-input-placeholder { /* Microsoft Edge */
            color: white;
        }
       
        #menu_lang {
            z-index: 1000;
            position: absolute;
            border: 1px solid #ccc;
            border: 1px solid rgba(0,0,0,.15);
            border-radius: 4px;
            background-color: #fff;
            left: 30%;
            margin-top: -10px;
        }

        #menu_lang .lang {
            height: 42px;       
            line-height:42px;
            border-bottom: 1px #E3E3E3 solid;
            font-size: 16px;
            padding:0 25px;
        } 

        /*平板直向、平板橫向*/
        @media (min-width: 768px) and (max-width: 992px) {

        }

        @media (max-width: 767px) {
            #site_header_content {
                width: 100%;
                max-width: 100%;
            }

            .but_login {
                width: initial;
            }

            .but_registered {
                width: initial;
            }

            #search_bar_phone {
                border: 5px solid rgb(5,178,146);
                margin: 0 auto;
                padding-left: 0px;
                background: #ffffff;
            }

            .search_box {
                color: #666666 !important;
                float: left;
            }

            .search_box::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
                color: #666666;
                opacity: 1; /* Firefox */
            }

            .search_box:-ms-input-placeholder { /* Internet Explorer 10-11 */
                color: #666666;
            }

            .search_box::-ms-input-placeholder { /* Microsoft Edge */
                color: #666666;
            }

        }

        /*大手機尺寸*/
        @media (min-width: 375px) and (max-width: 767px) {
            .nav_search {
                height: 50px;
            }

            #site_header_login_bar_phone {
                margin-top: 5px;
            }

            .siteHeaderOuterTable {
                width: 94%;
                height:85px;
            }

            .siteLogo {
                margin-top:0px;
            }

            #site_header {
                width: 100%;
            }

            .search_box {
                min-width: 300px;
            }
        }

        /*手機尺寸*/
        @media (max-width: 374px) {
            .nav_search {
                height: 50px;
            }

            #site_header_login_bar_phone {
                margin-top: 5px;
            }

            .siteHeaderOuterTable {
                width: 90%;
                height:85px;
            }

            .siteLogo {
                margin-top:0px;
            }

            #site_header {
                width: 100%;
            }

            .search_box {
                min-width: 250px;
            }

            #div_username {
                padding-left: 0px;
                padding-right: 0px;
            }

            #imgGotoExplorer {
                max-width: initial;
            }
        }

    </style>
{/literal}

<div id="site_header" class="bgBlack">
    <nav class="navbar navbar-default navigation-clean-button" style="font-size:18px;margin-bottom: 0px;">
        <div class="container" style="min-width: initial;padding-left: 0px; padding-right: 0px;">
            <div class="navbar-collapse navbar-left hidden-xs hidden-phone">
                <a href="/mooc/index.php"><img class="siteLogo" src="/base/10001/door/tpl/logo.png" alt="{'return_home'|WM_Lang}" title="{'return_home'|WM_Lang}" border="0"></a>
            </div>
            <div class="navbar-text navbar-right actions hidden-xs hidden-phone">
                <ul class="nav navbar-nav">
                    <li><a href="javascript:;" onclick="gotoFaqList();">{'tool_faq'|WM_Lang}</a></li>
                    <li><a href="/mooc/download.php">{'downloads'|WM_Lang}</a></li>
                    <li><a href="/mooc/sitemap.php">{'map'|WM_Lang}</a></li>
                    {if $show_lang}
                    <li>
                        <div class="collapse navbar-collapse" style="padding-left: 0px;">
                            <ul class="nav navbar-nav navbar-right"> 
                                <li class="dropdown">
                                {$lang_dropdown}
                                </li> 
                            </ul>
                        </div>
                    </li>
                    {/if}
                </ul>
            </div>
            {*手機介面 - 選單*}
            <div class="row visible-xs hidden-tablet hidden-desktop">
                <div class="col-xs-9 text-left"><a href="/mooc/index.php"><img class="siteLogo" src="/base/10001/door/tpl/logo.png" alt="{'return_home'|WM_Lang}" title="{'return_home'|WM_Lang}" border="0"></a></div>
                <div class="col-xs-3 text-center" style="line-height: 50px;">
                    <i class="fa fa-bars" style="color:#FFFFFF;font-size: 26px;padding-right: 15px;line-height: 50px;" onclick="showPhoneMenu();"></i>
                </div>
            </div>
        </div>
    </nav>
</div>

<div class="nav nav_search">
    <div class="container" style="min-width: initial;padding-left: 0px; padding-right: 0px;height:60px !important;">
        {*手機介面*}
        <div id="site_header_login_bar_phone" class="visible-xs hidden-tablet hidden-desktop">
            <div class="row col-xs-12">
                <div class="col-xs-2 text-left" style="padding-left: 5px;">
                    <a href="javascript:;" onclick="gotoExplorer();return false;"><img id="imgGotoExplorer" src="/theme/default/learn_mooc/coursesorts.png" width="40" height="40"></a>
                </div>
                <div class="col-xs-2 text-left" style="padding-left: 5px;">
                    <button  class="button_search" onclick="showSearchBar();"><i class="fa fa-search" aria-hidden="true" style="font-size:20px;color:#FFFFFF;"></i></button>
                </div>
                <div id="div_username" class="col-xs-8 text-right">
                    <table border="0" cellspacing="0" cellpadding="0" style="float:right;font-size:16px;color:#FFFFFF;">
                        {if $profile.username === 'guest'}
                            <tr>
                                <td class="visible-xs" style="font-size:1em;padding-right:10px;"><input type="button" value="{'login'|WM_Lang}" class="but_login" onclick="gotoLogin();" /></td>
                                {if 'Y'|in_array:$canReg || 'C'|in_array:$canReg}
                                <td class="visible-xs" style="font-size:1em;padding:0px;"><input type="button" value="{'btn_register'|WM_Lang}" class="but_registered" onclick="gotoRegister();"/></td>
                                {/if}
                            </tr>
                        {else}
                            <tr>
                                <td class="visible-xs show-username" colspan="2" style="padding-top:3px;">
                                    <i class="fa fa-user-circle" aria-hidden="true" style="color:#FFFFFF;margin-right:0.3em;"></i>{$profile.realname}
                                </td>
                                <td class="visible-xs" style="font-size:1.2em;padding:3px 6px 6px 6px;"><a href="/logout.php" target="_top" style="font-size:16px;color:#FFFFFF;"><i class="fa fa-sign-out" aria-hidden="true" style="margin-right:0.3em;"></i>{'btn_logout'|WM_Lang}</a></td>
                            </tr>
                        {/if}
                    </table>
                </div>
            </div>
            <div id="search_bar_phone" class="row col-xs-12" style="display: {if $smarty.server.SCRIPT_NAME eq '/mooc/explorer.php' && $keyword neq ''}block{else}none{/if};">
                <input type="text" id="search_box_phone" value="{$keyword}" class="search_box" placeholder="{'searchcourse'|WM_Lang}" onchange="syncSearchBoxVal(this);">
                <button class="button_search" onclick="doSearch_course();" style="border-color:#FF720A;width:36px;height: 36px;margin-top:2px;float:right;"><i class="fa fa-search adjust" aria-hidden="true" style="font-size:20px;color:#FF720A;"></i></button>
            </div>
        </div>

        {*PC介面*}
        <table border="0" cellpadding="0" cellspacing="0" class="nav_box hidden-xs hidden-phone">
        <tbody><tr>
          <td width="40"><a href="javascript:;" onclick="gotoExplorer();return false;"><img src="/theme/default/learn_mooc/coursesorts.png" width="40" height="40" title="{'collegetype'|WM_Lang}"></a></td>
          <td width="20">&nbsp;</td>
          {if $profile.username === 'guest'}
          <td width="80%" style="text-align: left">
            <input type="text" id="search_box_pc" value="{$keyword}" class="search_box"  style="width:700px;margin-bottom: 2px;" placeholder="{'searchcourse'|WM_Lang}" onchange="syncSearchBoxVal(this);">
            <button  class="button_search" onclick="doSearch_course();"><i class="fa fa-search" aria-hidden="true" style="font-size:20px;color:#FFFFFF;"></i></button>
          </td>
          <td width="20"></td>
          <td><input type="button" value="{'login'|WM_Lang}" class="but_login" onclick="gotoLogin();"></td>
          <td width="10">&nbsp;</td>
          <td width="20"></td>
          {if 'Y'|in_array:$canReg || 'C'|in_array:$canReg}
          <td align="right"><input type="button" value="{'btn_register'|WM_Lang}" class="but_registered" onclick="gotoRegister();"></td>
          {/if}
          {else}
          <td style="text-align: left">
            <input type="text" id="search_box_pc" value="{$keyword}" class="search_box" style="width:600px;" placeholder="{'searchcourse'|WM_Lang}" onchange="syncSearchBoxVal(this);">
            <button  class="button_search" onclick="doSearch_course();"><i class="fa fa-search" aria-hidden="true" style="font-size:20px;color:#FFFFFF;"></i></button>
          </td>
          <td>
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-right"> 
                    <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="color:#FFFFFF;width:160px;padding:5px;" title="{$profile.u}"><i class="fa fa-user-circle" aria-hidden="true" style="color:#FFFFFF;margin-right:0.3em;"></i><span class="show-username">{$profile.realname}</span><i class="fa fa-caret-down" aria-hidden="true"></i></a>
                    <ul class="dropdown-menu">
                        {if $profile.isManager}
                            <li style="height: 36px;"><a href="/academic/index.php"><i class="fa fa-cogs" aria-hidden="true"></i>&nbsp;&nbsp;{'managers'|WM_Lang}</a></li>
                        {/if}
                        {if $profile.isTeacher}
                        <li style="height: 36px;"><a href="/learn/mooc_teacher.php"><i class="fa fa-magic" aria-hidden="true"></i>&nbsp;&nbsp;{'offcie'|WM_Lang}</a></li>
                        {/if}
                        <li style="height: 36px;"><a href="/learn/mooc_personal.php"><i class="fa fa-university" aria-hidden="true"></i>&nbsp;&nbsp;{'explore'|WM_Lang}</a></li>
                    </ul>
                    </li> 
                </ul>
            </div>
          </td>
          <td class="" style="font-size:1.2em;padding:0px 6px 6px 6px;"><a href="/logout.php" target="_top" style="font-size:16px;color:#FFFFFF;"><i class="fa fa-sign-out" aria-hidden="true" style="margin-right:0.3em;"></i>{'btn_logout'|WM_Lang}</a></td>
          {/if}
        </tr>
        </tbody></table>
    </div>
</div>

<div id="phoneMenu" style="display:none;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="visible-xs">
        <tr><td class="phoneMenuItem"><a href="{$footFaqUrl}">{'tool_faq'|WM_Lang}</a></td></tr>
        <tr><td class="phoneMenuItem"><a href="/mooc/download.php">{'downloads'|WM_Lang}</a></td></tr>
        <tr><td class="phoneMenuItem"><a href="/mooc/sitemap.php">{'map'|WM_Lang}</a></td></tr>
        <tr><td class="phoneMenuItem">{$p_lang_dropdown}</td></tr>
        {if $profile.username neq 'guest'}
            <tr><td class="phoneMenuItemB">{'personal_menu'|WM_Lang}</td></tr>
            <tr><td class="phoneMenuItem"><a href="/mooc/user/personal.php"><i class="fa fa-address-card-o" aria-hidden="true"></i>&nbsp;&nbsp;{'personal_info'|WM_Lang}</a></td></tr>
            <tr><td class="phoneMenuItem"><a href="/mooc/user/mycourse.php"><i class="fa fa-university" aria-hidden="true"></i>&nbsp;&nbsp;{'my_courses'|WM_Lang}</a></td></tr>
            {if $profile.isTeacher}
            <tr><td class="phoneMenuItem"><a href="/mooc/user/myteaching.php"><i class="fa fa-magic" aria-hidden="true"></i>&nbsp;&nbsp;{'my_lesson'|WM_Lang}</a></td></tr>
            {/if}
            <tr><td class="phoneMenuItem"><a href="/learn/newcalendar/calendar.php"><i class="fa fa-calendar" aria-hidden="true"></i>&nbsp;&nbsp;{'my_calendar'|WM_Lang}</a></td></tr>
            <tr><td class="phoneMenuItem"><a href="/mooc/user/learn_stat.php"><i class="fa fa-database" aria-hidden="true"></i>&nbsp;&nbsp;{'my_record'|WM_Lang}</a></td></tr>
            <tr><td class="phoneMenuItem"><a href="/learn/questionnaire/questionnaire_list.php?school"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>&nbsp;&nbsp;{'learn_questionnaire'|WM_Lang}</a></td></tr>
            {/if}
            {if $profile.isManager}
            <tr><td class="phoneMenuItemB">{'manager_menu'|WM_Lang}</td></tr>
            <tr><td class="phoneMenuItem"><a href="/mooc/academic/stud/stud_query.php"><i class="fa fa-users" aria-hidden="true"></i>&nbsp;&nbsp;{'query_student'|WM_Lang}</a></td></tr>
            <tr><td class="phoneMenuItem"><a href="/mooc/academic/review/review_review.php"><i class="fa fa-user-plus" aria-hidden="true"></i>&nbsp;&nbsp;{'audit_account'|WM_Lang}</a></td></tr>
            <tr><td class="phoneMenuItem"><a href="/mooc/academic/news/index_news.php"><i class="fa fa-newspaper-o" aria-hidden="true"></i>&nbsp;&nbsp;{'latestnews'|WM_Lang}</a></td></tr>
            <tr><td class="phoneMenuItem"><a href="/mooc/academic/calendar/calendar_ed.php"><i class="fa fa-calendar" aria-hidden="true"></i>&nbsp;&nbsp;{'cale_school_title'|WM_Lang}</a></td></tr>
            {if $profile.isAdvManager}
            <tr><td class="phoneMenuItem"><a href="/mooc/academic/sys/sysop.php"><i class="fa fa-user-secret" aria-hidden="true"></i>&nbsp;&nbsp;{'manager_settings'|WM_Lang}</a></td></tr>
            {/if}
        {/if}
    </table>
</div>
<form name="faq_node_list" id="faq_node_list" method="POST" target="faqFrame" style="display: none;">
    <input type="hidden" name="token" value="{$csrfToken}">
    <input type="hidden" name="cid">
    <input type="hidden" name="bid">
    <input type="hidden" name="nid">
</form>
<form id="siteHeaderSearchForm" name="siteHeaderSearchForm" action="/mooc/explorer.php" method="post" style="display: none;">
    <input type="hidden" name="keyword" value="">
</form>

<iframe id="faqFrame" name="faqFrame" src="about:blank" style="display: none;" title="呈現常見問題的框架" height="600" width="100%"></iframe>

<script>
    var server_name = '{$smarty.server.SERVER_NAME}';
    var faq_url = '{$footFaqUrl}';
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

        function syncSearchBoxVal(obj) {
            if ($('#search_box_pc').val() != obj.value){
                $('#search_box_pc').val(obj.value);
            }
            if ($('#search_box_phone').val() != obj.value){
                $('#search_box_phone').val(obj.value);
            }
        }

        function doSearch_course(){
            if (isPhoneDevice == '1') {
                adv_search($('#search_box_phone').val());
            }else{
                adv_search($('#search_box_pc').val());
            }
        }

        function gotoExplorer() {
            $('<form action="/mooc/explorer.php" method="post"></form>').appendTo('body').submit();
        }

        function gotoLogin() {
            top.document.location.href = '/mooc/login.php';
        }

        function gotoRegister() {
            top.document.location.href = '/mooc/register.php';
        }

        function showSearchBar() {
            if ($('#search_bar_phone').is(":visible")){
                $('#search_bar_phone').hide();
            }else{
                $('#search_bar_phone').show();
            }
        }

        function showLang() {
            if ($('#menu_lang').is(":visible")){
                $('#menu_lang').hide();
            }else{
                $('#menu_lang').show();
            }
        }

        function gotoFaqList() {
            
            
            if (navigator.userAgent.match(/(iPad)/i)) {
                $("form[name='faq_node_list']").prop('action', faq_url);
    		    $("form[name='faq_node_list']").prop('target', '_blank');
    		    $("form[name='faq_node_list']").submit();
    	    } else {
    	        var objForm = document.getElementById('faq_node_list');
    	        objForm.action = faq_url;
	            $.fancybox.open("#faqFrame", {
	                maxWidth: 960,
	                fitToView: false,
	                width: '100%',
	                autoSize: false,
	                'titlePosition': 'inline',
	                'transitionIn': 'none',
	                'transitionOut': 'none',
	                'closeBtn': true,
	                'scrolling': 'no',
	                beforeShow: function(){
	                    $("body").css({'overflow-y':'hidden'});
	                    $("#faqFrame").css({'width' : '100%'});
	                },
	                afterClose: function(){
	                    $("body").css({'overflow-y':'visible'});
	                },
	                helpers : {
	                    overlay : {
	                        locked : false
	                    }
	                }
	            });
	            objForm.submit();
            }
            
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
<script language="javascript" src="/mooc/public/js/search.js?1234"></script>
<script language="javascript" src="/mooc/public/js/site_header.js"></script>
