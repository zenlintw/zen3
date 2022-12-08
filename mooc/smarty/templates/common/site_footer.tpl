</div>
<style type="text/css">
    {literal}
        .footer {
            background-image: none;
            background-color: #1A1A1A;
            height: 180px;
            padding-top:1px;
            margin-top: 0px;
        }

        .footer > .container > .top {
            padding-top: 15px;
        }

        .infourl {
            font-size: 18px;
            color: #FFFFFF;
        }

        .online {
            right: 0px;
            color:#FFFFFF;border-bottom: 1px solid rgb(51, 51, 51);
            line-height: 42px;
        }

        .fa-facebook {
            font-size: 21px;
            color: #FFFFFF;
            padding-top: 3px;
            padding-right: 2px;
        }


        /*手機尺寸*/
        @media (max-width: 767px) {
            .footer {
                min-width: 320px;
                height: auto;
            }

            .footer > .narrow > .top > .share {
                margin-right: 0px;
                float: left;
                top: 180px;
            }
        }

        /*平板直向、平板橫向*/
        @media (min-width: 768px) and (max-width: 992px) {
            .footer {
                min-width: 320px;
                height: 240px;
            }

            .footer > .narrow > .top > .online {
                padding-left: 30px;
            }

            .footer > .narrow > .bottom > .info > div {
                padding-left: 30px;
            }

            .footer > .narrow > .top > .share {
                margin-right: 35px;
            }
        }

{/literal}

{if $smarty.server.SCRIPT_NAME eq '/mooc/index.php'}
{literal}
.share {
    float: right;
}

.share > .pic > a > .fb {
        background: transparent url(/public/images/fb-link.png) no-repeat;
        width: 27px;
        height: 27px;
        background-size: cover;
    }

.share > .pic > a > .plk {
  background: transparent url("/public/images/pa-link.png") no-repeat;
  width: 27px;
  height: 27px;
  background-size: cover;
}

.share > .pic > a > .tw {
  background: transparent url("/public/images/wt-link.png") no-repeat;
  width: 27px;
  height: 27px;
  background-size: cover;
}

.share > .pic > a > .ln {
  background: transparent url("/public/images/line-link.png") no-repeat;
  background-size: cover;
  width: 27px;
  height: 27px;
  background-size: cover;
}

.share > .pic > a > .wct {
  background: transparent url("/public/images/wa-link.png") no-repeat;
  width: 27px;
  height: 27px;
  background-size: cover;
}

.share .pic {
    width: 38px;
    height: 38px;
    margin-right: 14px;
    text-align: center;
    padding: 5px;
    border-radius: 50%;
    background-color: #000;
}

#inline-ln > .well div {
  line-height:unset;
}

#inline-ln > .well {
  height: auto;
}

@media (max-width: 414px) {
    .share {
        display:inline-flex;
    }
}
{/literal}
{/if}

</style>
<footer class="footer navbar-fixed-bottom">
    <div class="container">
        <div class="top row">
            <div class="col-md-12">
                <div class="online">
                    <span class="infourl"><a href="/mooc/security.php" style="color: #E9D35A;">{'security_policy'|WM_Lang}</a>&nbsp;|&nbsp;</span>
                    <span class="infourl"><a href="/mooc/privacy.php" style="color: #E9D35A;">{'privacy_policy2'|WM_Lang}</a></span>
                </div>
            </div>
            <div class="col-md-7 col-xs-12" style="margin-top:15px;">
                <div class="t14_White">{'addr_info'|WM_Lang}</div>
                <div class="t14_White">{'phone_info'|WM_Lang}</div>
                <div class="t14_White">Email /  <a href="mailto:sales@mail.elearn.com.tw">sales@mail.elearn.com.tw</a></div>
            </div>
            <div id="index-share-icons" class="col-md-5 col-xs-12" style="margin-top:15px;">
                <div class="share row">
                    {$schooShareIcon}
                </div>
            </div>
        </div>
    </div>
</footer>

<div id="inline-ln" class="inline-ln">
    <form class="well">
        <div>{'linesharenote'|WM_Lang}</div>
    </form>
</div>
<!--
<div style="width: auto; height: auto; overflow: auto; position: relative;">
    <div id="inline-wct" class="inline-wct">
        <img src="https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl={$appRoot}&choe=UTF-8"/>
    </div>
</div>

<!-- Current Page Vendor and Views -->
<script src="/sys/tpl/vendor/rs-plugin/js/jquery.themepunch.tools.min.js"></script>
<script src="/sys/tpl/vendor/rs-plugin/js/jquery.themepunch.revolution.min.js?20170620"></script>

<script language="javascript">
    var metaDescription = '{$metaDescription|escape}';
    var metaSitename = encodeURIComponent('{$metaSitename|escape}');
    var iosAppStoreUrl = '{$sysAppIosUrl}';
    var androidPlayStoreUrl = '{$sysAppAndroidUrl}';
    var appPortalRoot = '{$smarty.const.SYS_PORTAL_HOME_URL}';
    {literal}
        $(function () {
            //alert('window:'+$(window).width()+"\n"+'footer:'+$('footer').width());
        });
    {/literal}
</script>
<script language="javascript" src="/mooc/public/js/site_footer.js?20180607"></script>