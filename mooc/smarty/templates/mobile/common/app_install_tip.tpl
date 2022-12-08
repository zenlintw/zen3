<style>
{literal}
.app-tips {
    display: -webkit-flex;
    display: flex;
    -webkit-flex-align: center;
    -ms-flex-align: center;
    -webkit-align-items: center;
    align-items: center;
    /* margin-top: -20px; */
    padding: 0.5em 1em;
    background-color: #2FC3BD;
    font-size: 1em;
    color: #FFFFFF;
    
}
.app-tips:after {
    content: '';
    display: block;
    clear: both;
}
/*
.app-tips:after {
    content:'';
    width:0;
    height:100%;
    display:inline-block;
    vertical-align:middle;
}
*/
.app-tips .tip, .app-tips button {
    /* display:inline-block; */
    height: 100%;
    vertical-align:middle;
}
.app-tips .tip {
    -webkit-flex: 1;
    -ms-flex: 1;
    flex: 1;
}
.app-tip button {
    -webkit-flex: none;
    -ms-flex: none;
    flex: none;
    
}
.app-tips .tip:after {
    content:'';
    width:0;
    height:100%;
    display:inline-block;
    vertical-align:middle;
}
.icon-tip {
    display: inline-block;
    width: 19px;
    height: 19px;
    background: transparent url("/public/images/mobile/icon_tip.png") no-repeat;
    background-size: contain;
    vertical-align: middle;
    margin: 0 5px 0 0;
}
.app-tips button {
    font-size: 1em;
}
.app-tips .close {
    color: #FFFFFF;
    margin-left: 10px;
    opacity: 0.8;
}

{/literal}
</style>
<!-- 安裝 APP 提示訊息 -->
{if $noInstallAppTip neq '1'}
    <div class="app-tips">
        <div class="tip">
            <i class="icon-tip"></i>
            {'msg_install_tip'|WM_Lang}
        </div>
        <button id="app-install-btn" class="btn btn-orange pull-right">{'msg_install_app'|WM_Lang}</button>
        <button type="button" class="close">×</button>
</div>
{/if}
<script>
    var iosAppStoreUrl = '{$sysAppIosUrl}';
    var androidPlayStoreUrl = '{$sysAppAndroidUrl}';
{literal}
    var openAPP = function () {
        /* 判斷裝置類型 */
        if (navigator.userAgent.match(/Windows Phone/i)) {
            // Windows Phone
        } else if (navigator.userAgent.match(/Android/i)) {
            window.location = androidPlayStoreUrl;
        } else if (navigator.userAgent.match(/(iPhone|iPad|iPod)/i)) {
            // iOS
            document.location = iosAppStoreUrl;
        } else {
            // 其他
        }
    }
    var removeInstallTip = function () {
        $.cookie("noInstallAppTip", 1, { expires: 1, path:'/' });
        $('.app-tips').hide();
    }
    $(document).ready(new function () {
        $('#app-install-btn').on('click', openAPP);
        $('.app-tips .close').on('click', removeInstallTip);
    });
{/literal}
</script>