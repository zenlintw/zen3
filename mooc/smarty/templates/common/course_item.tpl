<div class="div_course_item_outer col-md-3 col-sm-4 col-xs-12">
    <div class="div_course_item">
        <table width="100%" border="0" cellspacing="4" cellpadding="0">
        <tbody><tr>
        <td>
            <a href="{$appRoot}/info/{$v.cid}?lang={$nowlang}">
                <img src="{$appRoot}/lib/app_show_course_picture.php?courseId={$v.cpic}" style="width:100%;max-width:472px;{if $profile.isPhoneDevice neq 1}max-height:125px;{else}max-height:162px;{/if}">
            </a></td>
        </tr>
        <tr>
        <td class="t20_black_bold"><table width="100%" border="0" cellspacing="4" cellpadding="0">
        <tbody><tr>
        <td class="t20_black_bold course_caption" title="{$v.caption}">{$v.caption}</td>
        </tr>
        <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tbody><tr>
        <td width="34" height="44"><img src="{$appRoot}/co_showuserpic.php?a={$v.teacherPic}" width="30" height="30" class="circle30"></td>
        <td class="t16_gary course_teacher" title="{$v.teacher}">{$v.teacher}</td>
        </tr>
        </tbody></table></td>
        </tr>
        <tr>
        <td height="1" bgcolor="#dfdfdf"></td>
        </tr>
        <tr>
        <td height="32" class="course_between t13_b">{'openingperiod'|WM_Lang}：{$v.classPeriod}</td>
        </tr>
        </tbody></table></td>
        </tr>
        </tbody></table>
    </div>
</div>
