<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.12/css/all.css">
<div class="row">&nbsp;</div>
<div class="container esn-container">
    <div class="block-center">
        <form class="well" style="height: 18em;">
            <div style="text-align: center; margin-top: 3em; font-size: 1.2em;">
            {if $filesize ne ''}
                {$filename} ( {$filesize} )
            {else}
                {$message}
            {/if}
            </div>
            {if $filesize ne ''} 
                {if $candown}
                <div class="row">&nbsp;</div>
                <div style="text-align: center;">
                    <button onClick="download('{$path}'); return false;" class="btn btn-large btn-blue" style="font-size: 1.2em;"><i class="fa fa-download fa-lg" style="color: black; margin-right: 0.5em;" aria-hidden="true"></i>{'download'|WM_Lang}</button>
                </div>
                {else}
                <div style="text-align: center;color:red;height: 100px;;font-size: 40px;line-height: 100px;"><i class="fas fa-times-circle"></i>&nbsp;<span style="font-size: 26px;position: relative;top: -5px;">不支援</span></div>
                {/if}
            {/if}
        </form>
    </div>
</div>
        
<script type="text/javascript">
    var nowlang = '{$nowlang}',
        username = '{$profile.username}',
        pTicket = $.cookie('idx');
    {literal}
        
    // 應慢於 launchActivity，才能取到正確的閱讀記錄
    if (window.console) {console.log('materials');}
    
    // var smainFrame = window.parent.frames[0].document;
    var pathtreeFrame = $(window.parent.document);
    var prev_node_id = $(pathtreeFrame).contents().find("input[name='prev_node_id']").val();
    if (window.console) {console.log(prev_node_id);}
    var node = $(pathtreeFrame).contents().find('#' + prev_node_id);
    if (window.console) {console.log($(node).find('.icon-node').hasClass('node-finish'));}
    if (window.console) {console.log($(node).find('.icon-node').hasClass('node-progress'));}
    
    // 判斷有無讀取，沒有則清空目前節點ISCO，等實際點選下載按鈕才設定目前節點ISCO與目前時間；有讀取過，則不清空，依原WM邏輯，有開頁面就算開始閱讀了
    if ($(node).find('.icon-node').hasClass('node-finish') === false && $(node).find('.icon-node').hasClass('node-progress') === false) {
        // $(pathtreeFrame).contents().find("input[name='prev_node_id']").data('prev-node-id', prev_node_id);
        // if (window.console) {console.log($(pathtreeFrame).contents().find("input[name='prev_node_id']").data('prev-node-id'));}
        $(pathtreeFrame).contents().find("input[name='prev_node_id']").val('');
    } 
    
    function download(path) {
        if (window.console) {console.log(path);}
        
        if (window.console) {console.log($(node).find('.icon-node').hasClass('node-finish'));}
        if (window.console) {console.log($(node).find('.icon-node').hasClass('node-progress'));}

        // 判斷有無讀取，第一次閱讀起算點從點選下載按鈕才開始計算，第二次以後從進入本頁面開始算
        if ($(node).find('.icon-node').hasClass('node-finish') === false && $(node).find('.icon-node').hasClass('node-progress') === false) {
            var myDate = new Date();
            var nowtime = myDate.getFullYear() + '-' + (('0' + (myDate.getMonth() + 1 )).slice(-2)) + '-' + (('0' + (myDate.getDate())).slice(-2)) + ' ' + (('0' + (myDate.getHours())).slice(-2)) + ':' + (('0' + (myDate.getMinutes())).slice(-2)) + ':' + (('0' + (myDate.getSeconds())).slice(-2));
            // 寫入實際點選下載的時間
            $(pathtreeFrame).contents().find("input[name='begin_time']").val(nowtime);
            // 設定目前節點名稱，以利記錄閱讀時數
            $(pathtreeFrame).contents().find("input[name='prev_node_id']").val(prev_node_id);

            // learn\path\manifest.js 也要一起改
            $.ajax({
                url:'/mooc/controllers/course_ajax.php',
                type:'POST',
                dataType:'json',
                data: 'action=setReading' +
                    '&ticket=' + pTicket + 
                    '&type=start' +
                    '&period=0' + 
                    '&enCid=' + $(pathtreeFrame).contents().find("input[name='course_id']").val() + 
                    '&bt=' + $(pathtreeFrame).contents().find("input[name='begin_time']").val() + 
                    '&title=' + $(pathtreeFrame).contents().find("input[name='prev_node_title']").val().replace(/\"/g,"") + 
                    '&enUrl=' + $(pathtreeFrame).contents().find("input[name='prev_href']").val() + 
                    '&actid=' + $(pathtreeFrame).contents().find("input[name='prev_node_id']").val(),
                async: false,
                success: function(res){
                    if (window.console) {console.log('res', res);}
                    if (res.code <= -90) {
                        location.reload();
                    }
                },
                error: function() {
                    if (window.console) {console.log('Get path error!');}
                }
            });
        }
        // 前往實際下載頁面
        location.href = 'download.php?path=' + encodeURIComponent(path);
    }
    {/literal}
</script>