<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<script type="text/javascript" src="/public/js/common.js"></script>
<a name="content2"></a>
<div class="box1">
    <div class="title">
    {'live_list'|WM_Lang}
    </div>
    <div class="content">
        <div class="box2">
            <div class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td class="t6">
                                    <div class="text-center">{'live_time'|WM_Lang}</div>
                                </td>
                                <td class="text-left">
                                    <div class="text-center">{'live_name'|WM_Lang}</div>
                                </td>
                                <td class="t3">
                                    <div class="text-center">{'status'|WM_Lang}</div>
                                </td>
                                <td class="t3">
                                    <div class="text-center">{'play'|WM_Lang}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>            
            <div class="content">
                <div class="data2">
                    <table class="table subject">
                        {if $datalist|@count >= 1}
                        {foreach from=$datalist key=k item=v}
                            <tr data-bid="{$k}">
                            <td class="t6">
                                <div class="text-center">{$v.begin_time}</div>
                            </td>
                            <td>
                                <div class="text-left" style=" word-wrap: break-word; word-break: break-all;">{$v.name}</div>
                            </td>
                            <td class="t3">
                                <div class="text-center">{if $v.status=='off'}{'off'|WM_Lang}{else}{'on'|WM_Lang}{/if}</div>
                            </td>
                            <td class="t3">
                                <div class="text-center"><button class="btn btn-gray" onclick="window.open('{$v.url}?rel=0&controls=1&showinfo=0&autoplay=1','youtube')">{'play'|WM_Lang}</button></div>
                            </td>
                        </tr>
                        {/foreach}
                        {else}
                        <tr>
                            <td colspan="4">
                                <div class="text-left" style="margin-left: 0.5em;">{'msg_no_list'|WM_Lang}</div>
                            </td>
                        </tr>
                        {/if}
                    </table>
                </div>
            </div>
            <div id="pageToolbar" class="paginate"></div>            
        </div>
    </div>
</div>
<script type="text/javascript">
    var sysGotoLabel = '{$label}';
    {literal}
    window.onload = function() {
        if (detectIE() === 13) {
            $('.title-bar, .content .subject td').css('border-radius', '0 0 0 0');
        }
    };
    
    function gotoLive(live) {
        // 組加密的資料
        var encrypt_data;
        var key = 'readlcmsvideolog';
        key = format_key(key);
        var iv = 'KXyFiQCfgiKcyuVNCGoILQ==';
        key = CryptoJS.enc.Utf8.parse(key);
        iv = CryptoJS.enc.Base64.parse(iv);

        var msg = {r: 1, l: live};

        var ciphertext = CryptoJS.AES.encrypt(JSON.stringify(msg), key, {iv: iv});
        var encrypt_data = ciphertext.toString();

        if (window.console) {console.log('encrypt_data', encrypt_data);}
        action = '/learn/chat/live.php?data=' + encrypt_data.replace('+', '!@#$');
        if (window.console) {console.log('encrypt_data', encrypt_data.replace('+', '!@#$'));}
        action = '/learn/chat/live.php?data=' + encodeURIComponent(encodeURIComponent(encrypt_data.replace('+', '!@#$')));

        chatWin = window.open(action, '_blank', 'width=' + (window.screen.availWidth) + ',height=' + (window.screen.availHeight) + ',left=0,top=0,toolbar=0,location=0,status=0,menubar=0,directories=0,resizable=1');
        chatWin.moveTo(0, 0);
    }
    {/literal}
</script>