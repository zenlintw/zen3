<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap336/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
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
            <div class="hidden-xs title-bar" style="margin-top: 2.3em;">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="text-left" style="margin-left: 0.5em;">{'title'|WM_Lang}</div>
                                </td>
                                <td class="t4 hidden-xs">
                                    <div class="text-center">{'source'|WM_Lang}</div>
                                </td>
                                <td class="t4 hidden-xs">
                                    <div class="text-center">{'percent'|WM_Lang}</div>
                                </td>
                                <td class="t4">
                                    <div class="text-center">{'score'|WM_Lang}</div>
                                </td>
                                <td class="t3 hidden-xs">
                                    <div class="text-center">{'pass_score'|WM_Lang}</div>
                                </td>
                                <td class="t3 hidden-xs">
                                    <div class="text-center">{'title_standard'|WM_Lang}</div>
                                </td>
                                <td class="hidden-xs">
                                    <div class="text-left">{'comment'|WM_Lang}</div>
                                </td>
                                <td class="t4 hidden-xs">
                                    <div class="text-right" style="margin-right: 0.5em;">{'graph'|WM_Lang}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="hidden-xs content">
                <div class="data2">
                    <table class="table subject">
                        {foreach from=$datalist key=k item=v}
                            <tr>
                            <td>
                                <div class="text-left" style="margin-left: 0.5em;white-space:normal">{$v.title}</div>
                            </td>
                            <td class="t4 hidden-xs">
                                <div class="text-center" style="white-space: normal; overflow: visible;">{$v.source}</div>
                            </td>
                            <td class="t4 hidden-xs">
                                <div class="text-center">{$v.percent} %</div>
                            </td>
                            <td class="t4">
                                <div class="text-center">{$v.score}</div>
                            </td>
                            <td class="t3 hidden-xs">
                                <div class="text-center">{$v.pass_score}</div>
                            </td>
                            <td class="t3 hidden-xs">
                                <div class="text-center">{$v.pass_adj}</div>
                            </td>
                            <td class="hidden-xs">
                                <div class="text-left" style="white-space:normal">{$v.comment}</div>
                            </td>
                            <td class="t4 hidden-xs">
                                <div class="text-right" style="margin-right: 0.5em;">
                                    {if $v.graphid!=''}<input type="button" value="{'view'|WM_Lang}" class="btn btn-gray" onclick="teamGraph('{$v.graphid}');">{/if}
                                </div>
                            </td>
                        </tr>
                        {/foreach}
                    </table>
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