<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<a name="content2"></a>
<div class="box1" style="width:540px;">
    <div class="title">{'learn_list'|WM_Lang}</div>
    <div class="content">
        <div class="box2">
            <div class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td class="t4">
                                    <div class="text-left" style="margin-left: 0.5em;">{'learn_node'|WM_Lang}</div>
                                </td>
                                <td class="t3">
                                    <div class="text-right" style="margin-right: 0.5em;">{'title6'|WM_Lang}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>            
            <div class="content">
                <div class="data2">
                    <table class="table subject">
                        {foreach from=$datalist key=k item=v}
                        <tr>
                            <td class="t4">
                            <div class="text-left" style="margin-left: 0.5em;">{$v.title}</div>
                            </td>
                            <td class="t3">
                                <div class="text-right" style="margin-right: 0.5em;">{$v.bt}</div>
                            </td>
                        </tr>
                        {/foreach}
                    </table>
                </div>
            </div>
            <div class="text-right" style="margin-right: 0.5em;">
                <button class="btn btn-blue" onclick="window.close();">{'btnClose'|WM_Lang}</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var sysGotoLabel = '{$label}';
</script>