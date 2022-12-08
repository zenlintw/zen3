<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<style>
{literal}
.lbl-phone-title {
    width: 90px;
    background-color: #F3800F;
    border-radius: 0px ;
}

.lbl-phone-title > div {
    text-align: center;
    color: #FFFFFF;
}

.data2 .subject td:last-child {
    border-radius: 0px ;
}

.data2 .subject td:first-child {
    border-radius: 0px ;
}

.data2 .subject tr {
    border-bottom: 1px solid #FFFFFF;
}

{/literal}
</style>
<script type="text/javascript" src="/public/js/common.js"></script>
<a name="content2"></a>
<div class="box1">
    <div class="title">{'live_list'|WM_Lang}</div>
    <div class="content">
        <div class="box2" style="margin-top: 0.5em;">
            
            <div class="visible-xs content">
                <div class="data2">
                    {if $datalist|@count >= 1}
                    {foreach from=$datalist key=k item=v}
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td class="lbl-phone-title" style="border-top-left-radius:4px;"><div class="text-left" style="margin-left: 0.5em;">{'live_time'|WM_Lang}</div></td>
                                <td style="border-top-right-radius:4px;"><div class="text-left" style="margin-right: 0.5em;">{$v.begin_time}</div></td>
                            </tr>
                            <tr>
                                <td class="lbl-phone-title"><div class="text-left" style="margin-left: 0.5em;">{'live_name'|WM_Lang}</div></td>
                                <td><div class="text-left" style="margin-right: 0.5em;">{$v.name}</div></td>
                            </tr>
                            <tr>
                                <td class="lbl-phone-title"><div class="text-left" style="margin-left: 0.5em;">{'status'|WM_Lang}</div></td>
                                <td><div class="text-left" style="margin-right: 0.5em;">{if $v.status=='off'}{'off'|WM_Lang}{else}{'on'|WM_Lang}{/if}</div></td>
                            </tr>
                            <tr>
                                <td class="lbl-phone-title" style="border-bottom-left-radius:4px;"><div class="text-left" style="margin-left: 0.5em;">{'play'|WM_Lang}</div></td>
                                <td style="border-bottom-right-radius:4px;"><input type="button" value="{'play'|WM_Lang}" class="btn btn-gray" onclick="window.open('{$v.url}?rel=0&controls=1&showinfo=0&autoplay=1','youtube')"></td>
                            </tr>
                        </tbody>
                    </table>
                    {/foreach}
                    {else}
                        <tr>
                            <td colspan="4">
                                <div class="text-left" style="margin-left: 0.5em;margin-top: -0.5em;">{'msg_no_list'|WM_Lang}</div>
                            </td>
                        </tr>
                    {/if}
                </div>
            </div>
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
    {/literal}
</script>