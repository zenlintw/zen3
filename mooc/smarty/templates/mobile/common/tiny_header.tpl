<!DOCTYPE html>
<html lang="" xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=10">
        
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 把 charset 的 meta tag 調到最前面 -->

    <!-- fb -->
    <meta name="title" content="{$metaTitle}">
    <meta name="description" property="og:description" content="{$metaDescription}"/>
    <meta property="og:title" content="{$metaTitle}">
    <meta property="og:site_name" content="{$metaSitename}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{$metaUrl}">
    <meta property="og:image" content="{$metaImage}">
    <link href="{$metaImage}" rel="image_src" type="image/jpeg">
    <!-- twitter -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{$metaTitle}">
    <meta name="twitter:description" content="{$metaDescription}">
    <meta name="twitter:url" content="{$metaUrl}">
    <meta name="twitter:image:src" content="{$metaImage}">

    <title>{$appTitle}</title>
    <link href="{$appRoot}/public/css/third_party/bootstrap-3.3.5/bootstrap.min.css" rel="stylesheet" />
    {*<link href="{$appRoot}/public/css/third_party/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.css" rel="stylesheet" />*}
    {*<link href="{$appRoot}/theme/default/learn_mooc/application.css" rel="stylesheet" />*}
    <link href="{$appRoot}/public/css/application.css?{$file_rev_num}" rel="stylesheet" />
    <link href="{$appRoot}/theme/default/fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css" media="screen" />
    <link rel="stylesheet" href="{$appRoot}/lib/jquery/jquery.mSimpleSlidebox.css">
    <link rel="icon" href="{$appRoot}/base/{$schoolId}/door/tpl/icon.ico">
    <link href="{$appRoot}/public/css/common.css?{$file_rev_num}" rel="stylesheet" />
    <link href="{$appRoot}/public/css/mobile/mobile.css?{$file_rev_num}" rel="stylesheet">
    {if $from_url !== '/learn/mooc_sysbar.php'}
        {if $theme !== 'black'}
            {* <link href="{$appRoot}/public/css/{$theme}/index.css" rel="stylesheet" /> *}
            <link href="{$appRoot}/public/css/{$theme}/index1.css?{$file_rev_num}" rel="stylesheet" />
        {else}
            <link href="{$appRoot}/public/css/index1.css?{$file_rev_num}" rel="stylesheet" />
        {/if}
    {/if}
    <script type="text/javascript" src="{$appRoot}/lib/modernizr/modernizr.min.js"></script>
    {*<script type="text/javascript" src="{$appRoot}/lib/jquery/jquery.min.js"></script>*}
    <script type="text/javascript" src="{$appRoot}/public/js/third_party/jquery-1.11.3/jquery-1.11.3.min.js"></script>
    {*<script type="text/javascript" src="{$appRoot}/public/js/third_party/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.js"></script>*}
    <script type="text/javascript" src="{$appRoot}/public/js/third_party/bootstrap-3.3.5/bootstrap.min.js"></script>
    <script type="text/javascript" src="{$appRoot}/theme/default/fancybox/jquery.fancybox.pack.js"></script>
    <script type="text/javascript" src="{$appRoot}/lib/jquery/jquery.mSimpleSlidebox.js"></script>
    <script type="text/javascript" src="{$appRoot}/lib/jquery/jquery.cookie.js"></script>
    <script type="text/javascript">
        var appRoot = '{$appRoot}',
            generalDomain = '{$generalDomain}';
    </script>
</head>
<body>