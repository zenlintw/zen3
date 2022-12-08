<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/learn_path.php');
    
    $file = $_GET['file'];
    if(empty($file))    die('params error');
    
    $ft = @explode('.', $file);
    if(!is_array($ft))  die('file format error');
    //取附檔名
    $len = count($ft);
    $ft = strtolower($ft[$len-1]);
    //popcorn jplayer support : http://jplayer.org/2.0.0/developer-guide/
    switch($ft){
        case 'mp3':
            $type = 'mp3';
        break;
        case 'mp4':
            $type = 'm4v';
        break;
        case 'mov':
            $type = 'mov';
        break;
        case 'flv':
            $type = 'flv';
        break;
        case 'webmv':
            $type = 'webm';
        break;
        case 'ogv':
            $type = 'ogv';
        break;
        case 'wav':
            $type = 'wav';
        break;
        default:
            die('File Type unsupported');
    }

    $protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) !== 'off')) ? 'https' : 'http';
    
    $playContent = array(
        $type => $file
    );
    
    $playContent = json_encode($playContent);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8">
    <title>mixplayer</title>
    <link rel="stylesheet" href="mixplayer/css/jplayer/black.skin/skin.css">
    <script type="text/javascript" src="mixplayer/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="mixplayer/popcorn.ie8.js"></script>
    <script type="text/javascript" src="mixplayer/popcorn.js"></script>
    <script type="text/javascript" src="mixplayer/popcorn.parser.js"></script>
    <script type="text/javascript" src="mixplayer/popcorn.subtitle.js"></script>
    <script type="text/javascript" src="mixplayer/popcorn.parserSRT.js"></script>
    <script type="text/javascript" src="mixplayer/jquery.jplayer.cus.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="mixplayer/popcorn.player.js"></script>
    <script type="text/javascript" src="mixplayer/popcorn.jplayer.js"></script>
    <script type="text/javascript" src="/public/js/common.js"></script>
    <script type="text/javascript">
        var msg = <?php echo json_encode($MSG);?>;
        var nowlang = '<?php echo $sysSession->lang;?>';
    </script>
    <script type="text/javascript" src="mixplayer/mixplayer.js?<?php echo time();?>"></script>
</head>
<body>
    <div id="jp_container_1" class="jp-video jp-video-360p" style="width: 100%; height: 100%">
        <div class="jp-type-single">
            <div class="jp-jplayer">
                <video preload="metadata" autoplay="false"></video>
            </div>
            <div class="jp-gui">
                <div class="jp-video-play">
                    <a href="javascript:;" class="jp-video-play-icon" tabindex="1">play</a>
                </div>
                <div class="jp-interface">
                    <div class="jp-progress">
                        <div style="width: 100%;" class="jp-seek-bar">
                            <div style="overflow: hidden; width: 0%;" class="jp-play-bar"></div>
                        </div>
                    </div>
                    <div class="jp-details">
                        <ul>
                            <li><span class="jp-title"></span></li>
                        </ul>
                    </div>
                    <div class="jp-controls-holder">
                        <ul class="jp-controls">
                                <li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
                                <li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
                                <li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
                                <li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
                                <li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
                            </ul>
                            <div class="jp-volume-bar">
                                <div class="jp-volume-bar-value"></div>
                            </div>
                            <div class="jp-time-bar">
                            <span class="jp-current-time"></span> / <span class="jp-duration"></span>
                            </div>
                            <!--
                            <div class="jp-custom-buttons">
                                <span class="jp-btn-cc jp-subtitle-menu-btn"><img src="mixplayer/css/jplayer/black.skin/cc.png" /></span>
                                <span class="jp-btn-cc-touched jp-subtitle-menu-btn-touched" style="display:none"><img src="mixplayer/css/jplayer/black.skin/cc_touched.png" /></span>
                                <span class="jp-btn-set jp-resolution-menu-btn"><img src="mixplayer/css/jplayer/black.skin/set.png" /></span>
                                <span class="jp-btn-set-touched jp-resolution-menu-btn-touched"><img src="mixplayer/css/jplayer/black.skin/set_touched.png" /></span>
                            </div>
                            -->
                            <ul class="jp-toggles">
                                <li><a href="javascript:;" class="jp-full-screen" tabindex="1" title="full screen">full screen</a></li>
                                <li><a href="javascript:;" class="jp-restore-screen" tabindex="1" title="restore screen">restore screen</a></li>
                            </ul>

                    </div>
                </div>
                
                <div class="jp-resolution-menu jp-custom-menu" style="display:none">
                    <ul>
                        <?
                            foreach($resolutionData as $v){
                        ?>
                            <li custom_val="<?=$v?>" class="<?=($v == $defaultResolution ? 'selected':'')?>"><?=$v?></li>
                        <?
                            }
                        ?>
                    </ul>
                </div>
                <div class="jp-subtitle-menu jp-custom-menu" style="display:none;width:80px">
                    <ul>
                        <?
                            foreach($subtitleData as $k=>$v){
                        ?>
                            <li custom_val="<?=$k?>" class="<?=($k == $defaultSubtitle ? 'selected':'')?>"><?=$v?></li>
                        <?
                            }
                        ?>
                        <li custom_val="disable">關閉</li>
                    </ul>
                </div>
            </div>
            <div style="display: none;" class="jp-no-solution">
                <span class="jp-no-solution-title">Update Required</span>
                <span class="jp-no-solution-content">To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.</span>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var
            vod,
            posterImg = '';
        jQuery(function() {
            mixPlayer.options({
                subtitle: true,
                smoothPlayBar: true,
                poster:posterImg
            });
            mixPlayer.setVideo(
                '720p',
                {
                    '720p': <?=$playContent?>
                }
            );
            
            mixPlayer.init('#jp_container_1');

            //custom jplayer buttons
            $('.jp-btn-cc').click(function(){
                showSubtitle(this, true);
            });
            $('.jp-btn-set').click(function(){
                showResolution(this, true);
            });
            $('.jp-btn-cc-touched').click(function(){
                showSubtitle(this, false);
            });
            $('.jp-btn-set-touched').click(function(){
                showResolution(this, false);
            });
            $('.jp-custom-menu li').click(function(){
                $(this).parent().find('li').removeClass('selected');
                $(this).addClass('selected'); 
                var cls = $(this).parent().parent().attr('class');
                if( cls.indexOf('jp-subtitle-menu')  >= 0){
                    showSubtitle(null, false);
                    switchSubtitle($(this).attr('custom_val'));
                }else if(cls.indexOf('jp-resolution-menu') >= 0 ){
                    showResolution(null, false);
                    switchResolution($(this).attr('custom_val'));
                }
                    
            });
            $('.jp-custom-menu li').mouseover(function(){
                $(this).parent().find('.over').removeClass('over');
                $(this).addClass('over');
                
            });
            $('.jp-custom-menu li').mouseout(function(){
                $(this).parent().find('.over').removeClass('over');
            })

            // 智慧財產權保護
            document.onbeforecopy =
            document.onbeforecut  =
            document.oncontextmenu =
            document.oncopy =
            document.oncut =
            document.ondragstart =
            document.onhelp =
            function(evnt)
            {
                return false;
            };

            $(".jp-full-screen").click(function(){
                touchfullscreen();
            });

            $(".jp-restore-screen").click(function(){
                exitFullscreen();
            });

            var content_height = $("#jquery_jplayer_1").height()-$(".jp-interface").height();
            $("#jquery_jplayer_1").css({'height':content_height});
            
        });
     
        function switchSubtitle(val){
            mixPlayer.chgSubtitle(val);
        }
        function switchResolution(val){
            mixPlayer.chgVideo(val);
        }
        function showSubtitle(el, isShow){
            if( isShow ){
                showResolution(null, false);
                $('.jp-subtitle-menu').show();
                var pos = $(el).offset();
                var menuHeight = $('.jp-subtitle-menu').height();
                $('.jp-subtitle-menu').offset({
                    top: pos.top-menuHeight-25,
                    left: pos.left-20
                });
                $('.jp-btn-cc').hide();
                $('.jp-btn-cc-touched').show();
            }else{
                $('.jp-subtitle-menu').hide();
                $('.jp-btn-cc-touched').hide();
                $('.jp-btn-cc').show();
            }
        }
        
        function showResolution(el, isShow){
            if( isShow ){
                showSubtitle(null,false);
                $('.jp-resolution-menu').show();
                var pos = $(el).offset();
                var menuHeight = $('.jp-resolution-menu').height();
                $('.jp-resolution-menu').offset({
                    top: pos.top-menuHeight-25,
                    left: pos.left-20
                });
                $('.jp-btn-set').hide();
                $('.jp-btn-set-touched').show();
            }else{
                $('.jp-resolution-menu').hide();
                $('.jp-btn-set-touched').hide();
                $('.jp-btn-set').show();
            }
        }
    
        function touchfullscreen() {
            if (!top.document.fullscreenElement &&
                !top.document.mozFullScreenElement && !top.document.webkitFullscreenElement && !top.document.msFullscreenElement ) {
                launchFullscreen();
            } else {
                exitFullscreen();
            }
        }

        function launchFullscreen() {
            if (top.document.documentElement.requestFullscreen) {
                top.document.documentElement.requestFullscreen();
            } else if (top.document.documentElement.mozRequestFullScreen) {
                top.document.documentElement.mozRequestFullScreen();
            } else if (top.document.documentElement.webkitRequestFullscreen) {
                top.document.documentElement.webkitRequestFullScreen();
            } else if (top.document.documentElement.msRequestFullscreen) {
                top.document.documentElement.msRequestFullscreen();
            }
        }

        function exitFullscreen() {
            if (top.document.exitFullscreen) {
                top.document.exitFullscreen();
            } else if (top.document.mozCancelFullScreen) {
                top.document.mozCancelFullScreen();
            } else if (top.document.webkitExitFullscreen) {
                top.document.webkitExitFullscreen();
            } else if (top.document.msExitFullscreen) {
                top.document.msExitFullscreen();
            }
        }

        top.document.addEventListener('webkitfullscreenchange', fullscreenChange);
        top.document.addEventListener('mozfullscreenchange', fullscreenChange);
        top.document.addEventListener('fullscreenchange', fullscreenChange);
        top.document.addEventListener('MSFullscreenChange', fullscreenChange);

        function fullscreenChange() {
            if (!top.document.fullscreenElement &&
                !top.document.mozFullScreenElement && !top.document.webkitFullscreenElement && !top.document.msFullscreenElement ) {
                parent.document.getElementById("envClassRoom").cols = "266,*";
                parent.document.getElementById("envStudent").rows = "93,*";
                parent.document.getElementById("envMooc").cols = "250,*";
                $(".jp-restore-screen").click();
                var content_height = $("#jquery_jplayer_1").height()-$(".jp-interface").height();
                $("#jquery_jplayer_1").css({'height':content_height});
            } else {
                parent.document.getElementById("envMooc").cols = "0,*";
                parent.document.getElementById("envStudent").rows = "0,*";
                parent.document.getElementById("envClassRoom").cols = "0,*";
            }
        }
    
    </script>
    
</body>
</html>