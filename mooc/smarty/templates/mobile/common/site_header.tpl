<style>
{literal}
/*
.mobile-header .logo {
    display: inline-block;
    height: 42px;
    width: 242px;
    background-size: contain;
}
.mobile-header .logo a {
    display: block;
    width: 242px;
    height: 42px;
}
.mobile-header .container {
    min-width: initial;
    width: auto;
    height: 42px;
}
.mobile-header .logo a {
    display: block;
    width: 242px;
    height: 42px;
}
.mobile-header .navbar .navbar-inner {
    padding-left: 10px;
    padding-right: 10px;
}
/* Lastly, apply responsive CSS fixes as necessary */
@media (max-width: 767px) {
    .mobile-header {
        /*margin-left: 0;
        margin-right: 0;
        padding-left: 0;
        padding-right: 0;*/
    }
}
*/
{/literal}
</style>
<header class="mobile-header">
    {*<div class="container">
        <div class="navbar">
            <div class="navbar-inner">
                {include file = "common/logo.tpl"}
                <form class="navbar-form pull-right">
                    <button type="submit" class="btn">電腦版</button>
                </form>
            </div>
        </div>
    </div>*}
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                {include file = "common/logo.tpl"}
                <button type="button" class="btn btn-default icon-toggle navbar-btn pull-right navbar-toggle collapsed sb-toggle-right"></button>
            </div>
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <button type="button" class="btn btn-default icon-toggle navbar-btn pull-right sb-toggle-right"></button>
            </div>
        </div>
    </nav>
</header>
<script language="javascript" src="{$appRoot}/mooc/public/js/site_header.js"></script>
<div class="clearboth"></div>