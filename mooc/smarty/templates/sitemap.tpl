{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<link href="/public/css/common.css" rel="stylesheet" />
<link href="/public/css/layout.css" rel="stylesheet" />
<style>
{literal}
li {
    list-style-type: none;
}

.fa-chevron-circle-right {
  color: #39a238;
}

.first-level-fontsize {
    font-size: 20px;
    font-weight: bold;
    line-height: 40px;
    padding-bottom: 2px;
    padding-top: 20px;
    border-bottom: 1px solid #e0e6e9;
}

.second-level-fontsize {
    font-size: 16px;
    line-height: 32px;
    padding-top: 10px;
}
{/literal}
</style>
<a name="content2"></a>
<div class="box1">
    <div class="title">{'map'|WM_Lang}</div>
    <div class="content">
        <div class="box2" style="padding-bottom: 30px;">
            <div class="container-fluid">
                <div class="row first-level-fontsize">
                    <div class="col-md-12"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i>{'top_area'|WM_Lang}</div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="row second-level-fontsize">
                            {if $profile.isPhoneDevice eq '0'}<div style="width:30px;display: inline-flex;float: left;">&nbsp;</div>{/if}
                            <div class="col-xs-6 col-sm-2 col-md-2"><i class="fa fa-angle-right">&nbsp;&nbsp;</i>{if $profile.isPhoneDevice eq '1'}<a href="{$footFaqUrl}">{else}<a href="javascript:;" onclick="gotoFaqList();return false;">{/if}{'link_faq'|WM_Lang}</a></div>
                            <div class="col-xs-6 col-sm-2 col-md-2"><i class="fa fa-angle-right">&nbsp;&nbsp;</i><a href="#">{'map'|WM_Lang}</a></div>
                            <div class="col-xs-6 col-sm-2 col-md-2"><i class="fa fa-angle-right">&nbsp;&nbsp;</i><a href="/mooc/explorer.php">{'searchcourse'|WM_Lang}</a></div>
                            <div class="col-xs-6 col-sm-2 col-md-2"><i class="fa fa-angle-right">&nbsp;&nbsp;</i><a href="/mooc/login.php">{'login'|WM_Lang}</a></div>
                            <div class="col-xs-6 col-sm-2 col-md-2"><i class="fa fa-angle-right">&nbsp;&nbsp;</i><a href="/mooc/register.php">{'btn_register'|WM_Lang}</a></div>
                        </div>
                    </div>
                </div>
                <div class="row first-level-fontsize">
                    <div class="col-md-3 col-sm-3"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i><a href="/mooc/index.php#slider">{'activity_links'|WM_Lang}</a></div>
                </div>
                <div class="row first-level-fontsize">
                    <div class="col-md-3 col-sm-3"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i><a href="/mooc/index.php#forumContainer">{'latestnews'|WM_Lang}</a></div>
                </div>
                <div class="row first-level-fontsize">
                    <div class="col-md-12"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i>{'courses_area'|WM_Lang}</div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="row second-level-fontsize">
                            {if $profile.isPhoneDevice eq '0'}<div style="width:30px;display: inline-flex;float: left;">&nbsp;</div>{/if}
                            <div class="col-xs-6 col-sm-2 col-md-2"><i class="fa fa-angle-right">&nbsp;&nbsp;</i><a href="/mooc/index.php#courseBlockContainer">{'latest_course'|WM_Lang}</a></div>
                            <div class="col-xs-6 col-sm-2 col-md-2"><i class="fa fa-angle-right">&nbsp;&nbsp;</i><a href="/mooc/index.php#course_tab_hot">{'popular_courses'|WM_Lang}</a></div>
                        </div>
                    </div>
                </div>
                <div class="row first-level-fontsize">
                    <div class="col-md-3 col-sm-3"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i><a href="/mooc/index.php#{$Carousel}">{'related_links'|WM_Lang}</a></div>
                </div>
                <div class="row first-level-fontsize">
                    <div class="col-md-12"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i><a href="/mooc/index.php#container-statistics">{'information_zone'|WM_Lang}</a></div>
                </div>
                <div class="row first-level-fontsize">
                    <div class="col-md-12"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i>{'bottom_area'|WM_Lang}</div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="row second-level-fontsize">
                            {if $profile.isPhoneDevice eq '0'}<div style="width:30px;display: inline-flex;float: left;">&nbsp;</div>{/if}
                            {if $nowlang=='en'}
                            <div class="col-xs-12 col-sm-2 col-md-2"><i class="fa fa-angle-right">&nbsp;&nbsp;</i><a href="/mooc/security.php">{'security_policy'|WM_Lang}</a></div>
                            {else}
                            <div class="col-xs-6 col-sm-2 col-md-2"><i class="fa fa-angle-right">&nbsp;&nbsp;</i><a href="/mooc/security.php">{'security_policy'|WM_Lang}</a></div>
                            {/if}
                            <div class="col-xs-6 col-sm-2 col-md-2"><i class="fa fa-angle-right">&nbsp;&nbsp;</i><a href="/mooc/privacy.php">{'privacy_policy2'|WM_Lang}</a></div>
                            <div class="col-xs-6 col-sm-2 col-md-2"><i class="fa fa-angle-right">&nbsp;&nbsp;</i><a href="/mooc/index.php#index-share-icons">{'share_area'|WM_Lang}</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

{include file = "common/site_footer.tpl"}