<!DOCTYPE html>
<html lang="" xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=11">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- 把 charset 的 meta tag 調到最前面 -->

    <!-- fb -->
    <meta name="title" content="{$metaTitle}">
    <meta name="description" property="og:description" content="{$metaDescription|escape}"/>
    <meta property="og:site_name" content="{$metaSitename}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{$metaTitle}">
    <meta property="og:url" content="{$metaUrl}">
    <meta property="og:image" content="{$metaImage}">
    <meta property="og:description" content="{$metaDescription|escape}">
    <link href="{$metaImage}" rel="image_src" type="image/jpeg">
    <!-- twitter -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{$metaTitle}">
    <meta name="twitter:description" content="{$metaDescription|escape}">
    <meta name="twitter:url" content="{$metaUrl}">
    <meta name="twitter:image:src" content="{$metaImage}">

    <title>{$appTitle}</title>
    {if $profile.isPhoneDevice}
        <link href="/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />
    {else}
        {if strpos($smarty.server.REQUEST_URI, '/mooc/user/') !== false }
            <link href="/public/css/school_irs.css" rel="stylesheet" />
            <link href="/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />
        {elseif strpos($smarty.server.REQUEST_URI, '/learn/') !== false || 
            strpos($smarty.server.REQUEST_URI, '/academic/') !== false || 
            strpos($smarty.server.REQUEST_URI, '/teach/') !== false || 
            strpos($smarty.server.REQUEST_URI, '/direct/') !== false || 
            strpos($smarty.server.REQUEST_URI, '/message/') !== false ||
            strpos($smarty.server.REQUEST_URI, '/forum/') !== false}
        <link href="/theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
        {else}
        <link href="/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />
        {/if}
    {/if}
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.12/css/all.css">
    <link rel="stylesheet" href="/sys/tpl/vendor/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" href="/sys/tpl/css/theme.css">
    {*<link href="/theme/default/learn_mooc/application.css" rel="stylesheet" />*}
    <link href="/public/css/application.css?{$file_rev_num}" rel="stylesheet" />
    <link href="/theme/default/fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css" media="screen" />
    <link rel="stylesheet" href="/lib/jquery/jquery.mSimpleSlidebox.css">
    <link rel="icon" href="/base/{$schoolId}/door/tpl/icon.ico">
    <link href="/public/css/common.css?{$file_rev_num}" rel="stylesheet" />
    {if $from_url !== '/learn/mooc_sysbar.php'}
        {if $theme !== 'black'}
            {* <link href="{$appRoot}/public/css/{$theme}/index.css" rel="stylesheet" /> *}
            <link href="{$appRoot}/public/css/{$theme}/index1.css?{$file_rev_num}" rel="stylesheet" />
        {else}
            <link href="{$appRoot}/public/css/index1.css?{$file_rev_num}" rel="stylesheet" />
        {/if}
            
    {/if}
    <script type="text/javascript" src="/lib/modernizr/modernizr.min.js"></script>
    {if $profile.isPhoneDevice}
        <script type="text/javascript" src="/lib/jquery/jquery.new.min.js?20200113"></script>
        <script type="text/javascript" src="/theme/default/bootstrap336/js/bootstrap.min.js"></script>
    {else}
            {if strpos($smarty.server.REQUEST_URI, '/mooc/user/') !== false }
            <script type="text/javascript" src="/lib/jquery/jquery.new.min.js?20200113"></script>
            <script type="text/javascript" src="/theme/default/bootstrap336/js/bootstrap.min.js"></script>
        {elseif strpos($smarty.server.REQUEST_URI, '/learn/') !== false || 
            strpos($smarty.server.REQUEST_URI, '/academic/') !== false || 
            strpos($smarty.server.REQUEST_URI, '/teach/') !== false || 
            strpos($smarty.server.REQUEST_URI, '/direct/') !== false || 
            strpos($smarty.server.REQUEST_URI, '/message/') !== false}
            <script type="text/javascript" src="/lib/jquery/jquery.min.js"></script>
            <script type="text/javascript" src="/theme/default/bootstrap/js/bootstrap.min.js"></script>
        {else}
            <script type="text/javascript" src="/lib/jquery/jquery.new.min.js?20200113"></script>
            <script type="text/javascript" src="/theme/default/bootstrap336/js/bootstrap.min.js"></script>
        {/if}
    {/if}
    <script type="text/javascript" src="/theme/default/fancybox/jquery.fancybox.pack.js"></script>
    <script type="text/javascript" src="/lib/jquery/jquery.mSimpleSlidebox.js"></script>
    <script type="text/javascript" src="/public/js/third_party/jquery-placeholder/jquery.placeholder.js"></script>
    <script type="text/javascript" src="/public/js/third_party/jquery-message-box/messagebox.min.js"></script>
    <script type="text/javascript" src="/public/js/third_party/bootbox/bootbox.min.js"></script>
    <script type="text/javascript" src="/lib/jquery/jquery.cookie.js"></script>
    <script type="text/javascript">
        var appRoot = '{$appRoot}',
            portalUrl = '{$smarty.const.SYS_PORTAL_HOME_URL}',
            generalDomain = '{$generalDomain}',
            metaTitle = '{$metaTitle}',
            metaDescription = '{$metaDescription|escape}',
            isPhoneDevice = '{$profile.isPhoneDevice}';
    </script>
</head>
<body {if strpos($smarty.server.REQUEST_URI, '/mooc/user/') !== false }style="height:100%;"{/if}>
{if $profile.isPhoneDevice}
    {if $profile.username != 'guest'}
        <script type="text/javascript" src="/mooc/public/js/session.js"></script>
    {/if}
{/if}