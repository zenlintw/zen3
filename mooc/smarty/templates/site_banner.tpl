<link rel="stylesheet" href="/lib/camera/css/camera.css" type="text/css" />
<style>
{literal}
.camera-slideshow {
    margin: 0 auto;
    width: 100%;
}
.camera_wrap .camera_pag .camera_pag_ul{
    text-align: center;
}
{/literal}
</style>
<script type='text/javascript' src='/lib/camera/scripts/jquery.mobile.customized.min.js'></script>
<script type='text/javascript' src='/lib/camera/scripts/jquery.easing.1.3.js'></script> 
<script type='text/javascript' src='/lib/camera/scripts/camera.min.js'></script> 

<div id="slider" class="camera-slideshow">
    <div class="camera_wrap" id="camera_wrap_1">
        {$siteBannerHTML}
    </div>
</div>
<script type="text/javascript">
    var ads_num = {$ads_num};
{literal}
    $(document).ready(function() {
        if (ads_num > 0) {
            if (ads_num==1) {
                jQuery('#camera_wrap_1').camera({
                    thumbnails: false,
                    height: '30%',
                    loader: 'bar',
                    autoAdvance: false,
                    mobileAutoAdvance: false,
                    navigation: false,
                    playPause: false
                });
            } else {
                jQuery('#camera_wrap_1').camera({
                    thumbnails: false,
                    height: '30%',
                    loader: 'bar'
                });
            }
        }
    });
{/literal}
</script>