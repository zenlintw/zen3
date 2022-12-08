<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<style>
{literal}
.lbl-phone-title {
    width: 90px;
    background-color: #F3800F;
    border-radius: 0px !important;
}

.lbl-phone-title > div {
    text-align: center;
    color: #FFFFFF;
}

{/literal}
</style>
<script type="text/javascript" src="/public/js/common.js"></script>
<a name="content2"></a>
<div class="box1">
    <div class="title">{'grade_info'|WM_Lang}</div>
    <div class="content">
        <div class="box2" style="margin-top: 1.7em;">
            <div class="content">
                <div class="data1">
                    <div class="content">
                        <table border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <span style="margin: 0.3em;"><img src="/theme/default/learn/my_left.png" width="9" height="11" border="0" align="absmiddle" /></span><span style="line-height: 2em;">{'msg_title_1'|WM_Lang}</span>
                            </td>
                        </tr><tr>
                            <td>
                                <span style="margin: 0.3em;"><img src="/theme/default/learn/my_left.png" width="9" height="11" border="0" align="absmiddle" /></span><span style="line-height: 2em;">{'msg_title_2'|WM_Lang}</span>
                            </td>
                        </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="visible-xs content" style="margin-top: 2.3em;">
                <div class="data2">
                    {foreach from=$datalist key=k item=v name=grades}
                    <table class="table subject" style="border-collapse: initial;">
                        <tbody>
                            <tr>
                                <td class="lbl-phone-title"><div class="text-left" style="margin-left: 0.5em;">{'title'|WM_Lang}</div></td>
                                <td><div class="text-left" style="margin-right: 0.5em;">{$v.title}</div></td>
                            </tr>
                            <tr>
                                <td class="lbl-phone-title"><div class="text-left" style="margin-left: 0.5em;">{'source'|WM_Lang}</div></td>
                                <td><div class="text-left" style="margin-right: 0.5em;">{$v.source}</div></td>
                            </tr>
                            <tr>
                                <td class="lbl-phone-title"><div class="text-left" style="margin-left: 0.5em;">{'percent'|WM_Lang}</div></td>
                                <td><div class="text-left" style="margin-right: 0.5em;">{$v.percent}</div></td>
                            </tr>
                            <tr>
                                <td class="lbl-phone-title"><div class="text-left" style="margin-left: 0.5em;">{'score'|WM_Lang}</div></td>
                                <td><div class="text-left" style="margin-right: 0.5em;">{$v.score}</div></td>
                            </tr>
                            <tr>
                                <td class="lbl-phone-title"><div class="text-left" style="margin-left: 0.5em;">{'pass_score'|WM_Lang}</div></td>
                                <td><div class="text-left" style="margin-right: 0.5em;">{$v.pass_score}</div></td>
                            </tr>
                            <tr>
                                <td class="lbl-phone-title"><div class="text-left" style="margin-left: 0.5em;">{'title_standard'|WM_Lang}</div></td>
                                <td><div class="text-left" style="margin-right: 0.5em;">{$v.pass_adj}</div></td>
                            </tr>
                            <tr>
                                <td class="lbl-phone-title"><div class="text-left" style="margin-left: 0.5em;">{'comment'|WM_Lang}</div></td>
                                <td><div class="text-left" style="margin-right: 0.5em;">{$v.comment}</div></td>
                            </tr>
                            <tr>
                                <td class="lbl-phone-title"><div class="text-left" style="margin-left: 0.5em;">{'graph'|WM_Lang}</div></td>
                                <td>{if $v.graphid!=''}<input type="button" value="{'view'|WM_Lang}" class="btn btn-gray" onclick="teamGraph('{$v.graphid}');">{/if}</td>
                            </tr>
                        </tbody>
                    </table>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    {literal}
    var teamWin;
    function teamGraph(gid) {
        teamWin = window.open('grade_team.php?' + gid, '', 'width=470, height=320,status=0,menubar=0,toolbar=0,scrollbars=0,resizable=0');
    }

    window.onload = function() {
        if (detectIE() === 13) {
            $('.title-bar, .content .subject td').css('border-radius', '0 0 0 0');
        }
    };
    {/literal}
</script>