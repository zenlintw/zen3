var mixPlayer = (function ($, Popcorn) {
    var
        isInit = false,
        videoStatus = 'stop',
        mixplayer = {},
        jplayer = {},
        $target,
        config = { // 預設值
            playIcon: true,
            controller: [
                'play', 'pause', 'stop', 'mute', 'repeat',
                'progress', 'volume-max', 'volume-bar', 'full-screen'
            ],
            noSolution: {
                show: false,
                title: 'Update Required',
                content: 'To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.'
            },
            videoId: 'jquery_jplayer_1',
            video: {
                width: '100%',
                height: '100%'
            },
            subtitle: false,
            //swfPath: 'js/jplayer/'
            swfPath: 'mixplayer/'
        },
        video = {
            quality: '',
            list: {},
            ext: '',
            ieLess11: '0',
            hasFlash: false
        },
        subtitle = {
            code: '',
            list: {}
        },
        options = {}, // 使用者設定值
        vod
    /**
     * 版本
     * @type {string}
     */
    mixplayer.version = '1.0';

    /**
     * 設定參數
     * @param {object} opts
     */
    mixplayer.options = function (opts) {
        if (opts !== undefined) {
            options = $.extend({}, config, opts);
        }
        return mixplayer;
    };

    /**
     * 設定影片清單
     * @param {string} def  預設值
     * @param {object} list 清單
     */
    mixplayer.setVideo = function (def, list) {
        video.quality = def;
        video.list = list;

        // 副檔名
        var exts = Object.keys(video.list[video.quality]);
        var ext = exts[0];
        if (window.console) {console.log('ext:', ext);}
        video.ext = ext;
        
        // 瀏覽器
        // IE小於等於10
        var ieLess11 = '0';
        if (detectIE() && detectIE() <= 10) {
            ieLess11 = '1';
        }
        if (window.console) {console.log('ieLess11:', ieLess11);}
        video.ieLess11 = ieLess11;
        
        // 偵測flash有沒有開啟
        var hasFlash = false;
        try {
            hasFlash = Boolean(new ActiveXObject('ShockwaveFlash.ShockwaveFlash'));
        } catch(exception) {
            hasFlash = ('undefined' != typeof navigator.mimeTypes['application/x-shockwave-flash']);
        }
        if (window.console) {console.log('hasFlash:', hasFlash);}
        video.hasFlash = hasFlash;
        
        // 檢查是否缺少FLASH套件
        if (ext === 'flv' && hasFlash === false) {
            if (window.console) {console.log('缺少Flash套件');}
            // https://get.adobe.com/tw/flashplayer/
            alert(msg['need_flash'][nowlang]);
        }
        
        // 播放器順序
        if (window.console) {console.log('player seq:', ((video.ext === 'flv' || video.ieLess11 === '1') && video.hasFlash === true) ? 'flash,html':'html,flash');}
    };

    mixplayer.chgVideo = function (sel) {
        var media, cTime, stat;
        if (!isInit) return;
        if (video.list.hasOwnProperty(sel)) {
            stat = videoStatus;
            jplayer.jPlayer('pause');
            cTime = vod.currentTime();
            media = $.extend({}, video.list[sel]);
            media.poster = options.poster;
            jplayer.jPlayer('setMedia', media);
            if (stat === 'play') {
                jplayer.jPlayer('play', cTime);
                jplayer.jPlayer('pause');
                setTimeout( function(){ jplayer.jPlayer('play'); },1000);
            } else {
                jplayer.jPlayer('pause', cTime);
            }
        }
    };

    /**
     * 設定字幕
     * @param {string} def  預設值
     * @param {object} list 清單
     */
    mixplayer.setSubtitle = function (def, list) {
        subtitle.code = def;
        subtitle.list = list;
    };

    /**
     * 切換字幕
     * @param {string} sel
     * sel === disable 為隱藏字幕
     */
    mixplayer.chgSubtitle = function (sel) {
        var sub;
        if (!isInit) return;
        if (sel === 'disable') {
            vod.disable('subtitle');
            return;
        } else {
            vod.enable('subtitle');
        }

        sub = vod.subtitle('options');
        $(sub.container).children().remove();

        if (subtitle.list.hasOwnProperty(sel)) {
            vod.parseSRT(subtitle.list[sel]);
        }
    };
    
    mixplayer.getTime = function (sel) {
        var sub;
        return vod.media.currentTime;
    };
    
    mixplayer.init = function (target) {
        var $elem;

        $target = $(target);
        if ($target.length > 0) {
            // $elem = $(html.join(''));
            $elem = $($target.find('.jp-type-single').get(0));

            // 設定影片長寬
            $elem.find('.jp-jplayer')
                .attr('id', options.videoId)
                .css({
                    'width': options.video.width,
                    'height': options.video.height
                })
                .find('video').css({
                    'width': options.video.width,
                    'height': options.video.height
                });

            // 設定控制按鈕
            if (options.controller === false) {
                $elem.find('.jp-progress, .jp-controls-holder').remove();
            } else {
                $(config.controller).each(function (idx, val) {
                    if (options.controller.indexOf(val) < 0) {
                        if (val === 'mute') {
                            $elem.find('.jp-mute, .jp-unmute').remove();
                        } else if (val === 'full-screen') {
                            $elem.find('.jp-full-screen, .jp-restore-screen').remove();
                        } else if (val === 'repeat') {
                            $elem.find('.jp-repeat, .jp-repeat-off').remove();
                        } else {
                            $elem.find('.jp-' + val).remove();
                        }
                    }
                });
            }

            //$target.append($elem);

            // 載入 popcorn
            vod = Popcorn.jplayer('#' + options.videoId, {
                media: {
//                    m4v: 'http://192.168.10.160:1935/vod/mp4:Disney_Frozen_Let_It_Go_720p.mp4/playlist.m3u8',
//                    rtmpv: 'rtmp://192.168.10.160:1935/vod/mp4:Disney_Frozen_Let_It_Go_720p.mp4',
                    poster: options.poster
                },
                options: {
                    ready: function () {
                        jplayer = $(this);
                        window.myjp = jplayer;
                        // console.log('jplayer ready!');
                        // 載入預設的影片
                        mixplayer.chgVideo(video.quality);

                        // 啟用預設字幕檔
                        if (options.subtitle) {
                            mixplayer.chgSubtitle(subtitle.code);
                        }
                    },
                    swfPath: options.swfPath,
                    //solution: 'flash,html',
                    
                    //  IE11 以上 chrome ff use html5 first; IE10 以下 flash first
                    //  flv must use flash                    
                    solution: ((video.ext === 'flv' || video.ieLess11 === '1') && video.hasFlash === true) ? 'flash,html':'html,flash',
                    
                    //supplied: 'rtmpv,m3u8v,m4v,flv,mov',
                    supplied: 'webmv,ogv,mp4,m4v,fla,flv,rtmpv,webma,oga,mp3,m4a,wav,rtmpa,mpdv',
                    size: {
                        width: options.video.width,
                        height: options.video.height
                    },
                    smoothPlayBar: true,
                    keyEnabled: true
                }
            });

            vod.listen('play', function () {
                videoStatus = 'play';
            });

            vod.listen('pause', function () {
                videoStatus = 'pause';
            });

            vod.listen('ended', function () {
                videoStatus = 'stop';
            });

            window.vod = vod;
            isInit = true;
            // console.log('init OK!');
        }
    };

    return mixplayer;
})(jQuery, Popcorn);
