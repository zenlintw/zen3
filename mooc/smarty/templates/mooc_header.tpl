<div class="wm-content">
    <header class="container lcms-header">
        <div class="title">
            <div class="narrow">
                {if $profile.username !== 'guest' && $toggleEnable === 'true'}
                <div id="toggle-arrow" class="toggle-arrow"></div>
                <div class="sidebar-toggle">
                    <a href="#" class="icon-toggle" id="toggle"></a>
                </div>
                {/if}
                <!-- 日後改為可根據課程改變LOGO圖片 -->
                {if $logoCss === 'Y'}
                    <style type="text/css">
                    .lcms-header .logo {ldelim}
                        background: transparent url("/base/{$schoolId}/door/tpl/logo.png") no-repeat;
                    {rdelim}
                    </style>
                {/if}
                <div class="logo" title="{$appTitle}"><a href="{$logo_target}" target="_top"></a></div>

                <div class="tools">
                    <div class="profile">
                        <ul class="nav">
                            {if $profile.username === 'guest'}
                                {if $moocFlow === 'Y'}
                                    <li><a href="{$appRoot}/mooc/login.php" target="_top">{'login'|WM_Lang}</a></li>
                                    <li class="divider"></li>
                                    {if $schoolcanReg !== 'N'}
                                    <li><a href="{$appRoot}/mooc/register.php" target="_top">{'btn_register'|WM_Lang}</a></li>
                                    <li class="divider"></li>
                                    {/if}
                                    <li><a href="{$appRoot}/mooc/forget.php">{'btn_query_password'|WM_Lang}</a></li>
                                    <li class="divider"></li>
                                {else}
                                    <li><a href="{$appRoot}/" target="_top">{'login'|WM_Lang}</a></li>
                                    <li class="divider"></li>
                                    <li><a href="{$appRoot}/sys/reg/index.php" target="_top">{'btn_register'|WM_Lang}</a></li>
                                    <li class="divider"></li>
                                    <li><a href="{$appRoot}/sys/pw_query.php" target="_top">{'btn_query_password'|WM_Lang}</a></li>
                                    <li class="divider"></li>
                                {/if}
                            {else}
                                <li>
                                    <select id="language" name="language" style="position: relative; top: 0.3em; width: 6.8em;">
                                        {foreach from=$languages key =k item =v}
                                            <option value="{$k}" {if $k eq $curLang}selected{/if}>{$v}</option>
                                        {/foreach}
                                    </select>
                                </li>
                                {if $smarty.cookies.persist_idx eq ''}
                                <li class="divider"></li>
                                <li><a href="#" onclick="logout(); return false;">{'btn_logout'|WM_Lang}</a></li>
                                {/if}
                                <li class="user">
                                    <div class="user-inner">
                                        <img src="/learn/personal/showpic.php?a={$profile.userPicId}" type="image/jpeg" borer="0" loop="0"/>
                                        <div class="name">{$profile.realname}&nbsp;</div>
                                    </div>
                                    <div class="divider"></div
                                </li>
                                {if $profile.isTeacher >= 1}
                                    <li class="divider teachEnvDiv" style="display:none;"></li>
                                    <li class="teachEnvDiv" style="display:none;"><a href="{$appRoot}/learn/mooc_teacher.php" target="_top">{'btn_go_teach'|WM_Lang}</a></li>
                                {/if}

                                {if $profile.isDirector >= 1}
                                    <li class="divider directEnvDiv" style="display:none;"></li>
                                    <li class="directEnvDiv" style="display:none;"><a href="{$appRoot}/direct/index.php" target="_top">{'btn_go_direct'|WM_Lang}</a></li>
                                {/if}

                                {if $profile.isManager >= 1}
                                    <li class="divider managerEnvDiv" style="display:none;"></li>
                                    <li class="managerEnvDiv" style="display:none;"><a href="{$appRoot}/academic/index.php" target="_top">{'btn_go_manager'|WM_Lang}</a></li>
                                {/if}

                                <li class="divider teachDiv" style="display:none;"></li>
                                <li class="teachDiv" style="display:none;"><a href="#" onclick="goTeach(); return false;">{'office'|WM_Lang}</a></li>

                                <li class="divider personalDiv"  style="display:none;"></li>
                                <li class="personalDiv" style="display:none;"><a href="#" onclick="goPersonal(); return false;">{'btn_go_learn'|WM_Lang}</a></li>

                                {if $isStudentMooc === true && $exploreEnable eq 'true'}
                                    <li class="divider"></li>
                                    {if $myCourseView == 'G'}
                                        <li><a href="{$appRoot}/mooc/explorer.php" target="_top">{'explorecourse'|WM_Lang}</a></li>
                                    {else}
                                        <li><a href="javascript:;" onclick="if($('.personalDiv').is(':visible')){ldelim}goPersonal();{rdelim}parent.mooc_sysbar.GotoSchoolCourses(0);return false;">{'explorecourse'|WM_Lang}</a></li>
                                    {/if}
                                {/if}
                                {if $profile.isTeacher >= 1 || $profile.isCsOpener >= 1}
                                    <li class="divider linktolcmsDiv" style="display:none;"></li>
                                    <li class="linktolcmsDiv" style="display:none;"><a href="javascript: void(0);" onclick="linktolcms();" target="_top">{'link_lcms'|WM_Lang}</a></li>
                                {/if}
                            {/if}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="clearboth"></div>
    <div class="nav-bottom-line"></div>
    {*課程迷失*}
    {if $courseBarEnable === true}
        <div style="min-width: 755px; margin: auto auto; padding-left: 2em; padding-right: 2em; margin-left: 3px;">
            <div class="courseinfo" style="display: none;">
                <div class="left">
                    <div class="coursename">{$courseName}</div>
                </div>
                <div class="right">
                    {if $isStudentMooc === true}
                        <div class="coursenum right">
                            {if $socialShare|@count neq 0 }
                                <div class="push right" style="display: none;"></div>
                            {/if}
                            <div class="coursecount right"></div>
                        </div>
                        <div class="share right">
                            {if 'WECHAT'|in_array:$socialShare}
                                <div class="pic">
                                    <a href="javascript:;" title="{'wechatsharenote'|WM_Lang}"><div class="wct"></div></a>
                                </div>
                            {/if}
                            {if 'LINE'|in_array:$socialShare}
                                <div class="pic">
                                    <a href="javascript:;" title="{'note'|WM_Lang}"><div class="ln"></div></a>
                                </div>
                            {/if}
                            {if 'TWITTER'|in_array:$socialShare}
                                <div class="pic">
                                    <a href="javascript: void(window.open('http://twitter.com/home/?status='.concat(encodeURIComponent('currcoursecaption')) .concat(' ').concat(encodeURIComponent('{$appRoot}/info/currcourseid?lang={$nowlang}'))));"><div class="tw"></div></a>
                                </div>
                            {/if}
                            {if 'PLURK'|in_array:$socialShare}
                                <div class="pic">
                                    <a href="javascript: void(window.open('http://www.plurk.com/?qualifier=shares&status='.concat(encodeURIComponent('currcoursecaption')).concat(' ').concat(encodeURIComponent('{$appRoot}/info/currcourseid?lang={$nowlang}'))));"><div class="plk"></div></a>
                                </div>
                            {/if}
                            {if 'FB'|in_array:$socialShare}
                                <div class="pic">
                                    <a href="javascript: void(window.open('http://www.facebook.com/share.php?u='.concat(encodeURIComponent('{$appRoot}/info/currcourseid?lang={$nowlang}'))));"><div class="fb"></div></a>
                                </div>
                            {/if}
                        </div>
                    {/if}
                    <div class="courseteacher right"></div>
                </div>
            </div>
        </div>
        <div class="courseinfo-bottom-line" style="display: none;">
            <div></div>
            <div></div>
        </div>
    {/if}
</div>
<form name="linktolcmsform" method="post" target="_blank" style="display: none;"></form>

<script language="JavaScript" src="{$appRoot}/mooc/public/js/site_header.js"></script>
<script language="JavaScript">
    var courseId = '{$courseId}', courseName = '{$courseName}', schoolName = '{$schoolName}',
        lineMSG = '{'linesharenote'|WM_Lang}', wctMSG = '{'wechatsharenote'|WM_Lang}', nowlang = '{$nowlang}',
        courseNumText = '{'number_of_class'|WM_Lang}' , courseTeacherText = '{'teacher_of_class'|WM_Lang}', fmDefault = "s_main";
</script>
<script>
{literal}
$(document).ready(function() {
    var useragent = navigator.userAgent;
    useragent = useragent.toLowerCase();
  if(useragent.indexOf('mac')!==-1){
      var $link;
      $link = $('.nav a');
      $link.click(function () {
       var tg, menuFrame, objFrame;

       $('#moocSidebar').find('li').removeClass('active');
       $(this).parent().addClass('active');

       if (fmDefault === 's_main') {
        tg = fmDefault.substring(0, fmDefault.lastIndexOf('_'));
        menuFrame = parent.frames[tg + '_catalog'];

        if ((menuFrame.location.pathname === '/learn/path/manifest.php')) {
         parent.FrameExpand(0, false, 0);
         objFrame = menuFrame.document.getElementById("pathtree");
         if (
          (objFrame != null) &&
          (objFrame.contentWindow.fetchResourceForm != null) &&
          (objFrame.contentWindow.fetchResourceForm.href !== undefined) &&
          (objFrame.contentWindow.fetchResourceForm.href.value !== 'about:blank')
         ) {
          objFrame.contentWindow.doUnload();
         }
        } else if (menuFrame.location.pathname === '/learn/scorm/InitialSCORM.php') {
         parent.FrameExpand(0, false, 0);
         menuFrame.doUnload();
        }
       }
      });

  }
});

// 課程社群分享
$(document).click(function(event) {
    obj = event.srcElement ? event.srcElement  : $(event.target);
    if ($(obj).prop('class') === 'push right') {
        $('.courseinfo .coursenum').show();
        $('.courseinfo .share').hide();
        $(obj).parent().hide();
        $(obj).parent().parent().children('.share').fadeIn('slow');
    } else {
        $('.courseinfo .share').hide();
        $('.courseinfo .coursenum').fadeIn();
    }
});


function logout() {
    var isOnIOS = navigator.userAgent.match(/iPad/i)|| navigator.userAgent.match(/iPhone/i);
    if (isOnIOS) {
        var objFrame = parent.s_catalog.document.getElementById('pathtree');
        if (
            (objFrame != null) &&
            (objFrame.contentWindow.fetchResourceForm != null) &&
            (objFrame.contentWindow.fetchResourceForm.href !== undefined) &&
            (objFrame.contentWindow.fetchResourceForm.href.value !== 'about:blank')
        ) {
            objFrame.contentWindow.doUnload();
        }
    }
    parent.location.href = "/logout.php";
}

// 捲動捲軸觸發社群分享關閉
$(document).scroll(function() {
    $('.courseinfo .share').hide();
    $('.courseinfo .coursenum').fadeIn();
});
{/literal}
var ticket = '{$ticket}';
// 寫入localStorage，以利 我的設定和其他頁面都能正常運作
localStorage.setItem('personal-info', '{$ticket}');
</script>
<script language="JavaScript" src="{$appRoot}/mooc/public/js/{$teachDirect}mooc_header.js"></script>