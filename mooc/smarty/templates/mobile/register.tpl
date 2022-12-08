<div id="wrap">
    {include file = "mobile/common/site_header.tpl"}
    <div class="titlebar">
        <span class="title">{'btn_register'|WM_Lang}</span>
        <span class="pull-right lcms-text-import">* {'required'|WM_Lang}</span>
    </div>
    <a href="{$appRoot}/mooc/login.php" class="btn btn-default pull-right" id="btnSignIn">{'login'|WM_Lang}</a>
    <form  name="formRegister" id="formRegister" action="{$appRoot}/mooc/register_p.php" method="post" class="well form-horizontal" onsubmit="return checkData();" accept-charset="UTF-8" lang="zh-tw">
        <input type="hidden" name="ticket" id="ticket" value="{$ticket}">
        <div id="message">{$message}</div>
        {if 'Y'|in_array:$canReg}
            <div class="form-group">
                <label for="username" class="control-label-c col-sm-2"><span class="lcms-red-starmark">* </span>{'th_username'|WM_Lang}</label>
                <div class="col-sm-10">
                    <input type="text" id="username" name="username" class="form-control" placeholder="" value="{$post.username}" onBlur="check_reg_username();">
                </div>
            </div>
            <div class="form-group">
                <label for="password" class="control-label-c col-sm-2"><span class="lcms-red-starmark">* </span>{'th_password'|WM_Lang}</label>
                <div class="col-sm-10">
                    <input type="password" id="password" name="password" class="form-control"  placeholder="">
                </div>
            </div>
            <div class="form-group">
                <label for="repassword" class="control-label-c col-sm-2"><span class="lcms-red-starmark">* </span>{'repassword'|WM_Lang}</label>
                <div class="col-sm-10">
                    <input type="password" id="repassword" name="repassword" class="form-control"  placeholder="">
                </div>
            </div>
            <div class="form-group">
                <label for="first_name" class="control-label-c col-sm-2"><span class="lcms-red-starmark">* </span>{'theading_realname'|WM_Lang}</label>
                <div class="col-sm-10">
                    <input type="text" id="first_name" name="first_name" class="form-control"  placeholder="" value="{$post.first_name}">
                </div>
            </div>
            <div class="form-group">
                <label for="birthday" class="control-label-c col-sm-2">{'birthday'|WM_Lang}</label>
                <div class="col-sm-10">
                    {assign var=year value=$smarty.now|date_format:"%Y"}
                    <select id="year" name="year" class="span1 narrow margin-right-5 form-control">
                        {section name=loop loop=118}
                            <option value="{$smarty.section.loop.index+$year-120}" {if ($post.year ge 1 && $post.year eq $smarty.section.loop.index+$year-120) || ($post.year eq '' && $year-34 eq $smarty.section.loop.index+$year-120)}selected{/if}>{$smarty.section.loop.index+$year-120}</option>
                        {/section}
                    </select>
                    <span class="lcms-input-label">{'year'|WM_Lang}</span>
                    <select id="month" name="month" class="span1 narrow margin-right-5 form-control">
                        <option value="">-</option>
                        {section name=loop loop=12}
                            <option value="{$smarty.section.loop.index+1}" {if $post.month eq $smarty.section.loop.index+1}selected{/if}>{$smarty.section.loop.index+1}</option>
                        {/section}
                    </select>
                    <span class="lcms-input-label">{'month'|WM_Lang}</span>
                    <select id="day" name="day" class="span1 narrow form-control">
                        <option value="">-</option>
                        {section name=loop loop=31}
                            <option value="{$smarty.section.loop.index+1}" {if $post.day eq $smarty.section.loop.index+1}selected{/if}>{$smarty.section.loop.index+1}</option>
                        {/section}
                    </select>
                    <span class="lcms-input-label" style="left: 0px;">{'day'|WM_Lang}</span>
                </div>
            </div>
            <div class="form-group">
                <label for="email" class="control-label-c col-sm-2"><span class="lcms-red-starmark">* </span>{'ex_email'|WM_Lang}</label>
                <div class="col-sm-10">
                    <input type="text" id="email" name="email" class="form-control" placeholder="" value="{$post.email}">
                </div>
            </div>
            <div class="form-group">
                <label for="lang" class="control-label-c col-sm-2"><span class="lcms-red-starmark">* </span>{'lang'|WM_Lang}</label>
                <div class="col-sm-10">
                    <select name="lang" class="form-control">
                        <{foreach from=$lang key=k_lang item=v_lang}>
                            <option value="{$k_lang}" {if $k_lang == $post.lang}selected{/if}>{$v_lang}</option>
                        <{/foreach}>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="is_agree" name="is_agree" value="Y">
                            <span>{'agreecomply'|WM_Lang}<a class="privacy-service" data-fancybox-type="iframe" href="{$appRoot}{$policy}">{'privacyservice'|WM_Lang}</a></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-primary btn-blue btnNormal" id="btnRegister">{'btn_register'|WM_Lang}</button>
                </div>
            </div>
        {else if}
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <span class="font-small">{'alreadyhaveaccount'|WM_Lang}</span>
                    <a href="{$appRoot}/mooc/login.php" class="btn aNormal margin-left-15" id="btnSignIn">{'login'|WM_Lang}</a>
                </div>
            </div>
        {/if}
        {if 'FB'|in_array:$canReg}
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    {include file = "fb_login_btn.tpl"}
                </div>
            </div>
        {/if}
    </form>
</div>
{include file = "mobile/common/site_footer.tpl"}
<script type="text/javascript">{$msg}var sysAccountMinLen = {$sysAccountMinLen}; var sysAccountMaxLen = {$sysAccountMaxLen}; var Account_format = {$Account_format}; var mail_rule = {$mail_rule}</script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/register.js"></script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/password_strong.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/xmlextras.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/filter_spec_char.js"></script>