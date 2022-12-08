<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<a name="content2"></a>
<div class="box1">
    <div class="title">
    {'breeze_record_list'|WM_Lang}
    </div>
    <div class="content">
        <div class="box2">
            <div class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td class="t6">
                                    <div class="text-left" style="margin-left: 0.5em;">{'title21'|WM_Lang}</div>
                                </td>
                                <td class="text-left">
                                    <div class="text-left">{'title25'|WM_Lang}</div>
                                </td>
                                <td class="t6">
                                    <div class="text-center">{'title2'|WM_Lang}</div>
                                </td>
                                <td class="t6">
                                    <div class="text-center">{'title7'|WM_Lang}</div>
                                </td>
                                <td class="t4">
                                    <div class="text-right" style="margin-right: 0.5em;">{'title8'|WM_Lang}</div>
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
                            <tr data-bid="{$k}">
                            <td class="t6">
                            <div class="text-left" style="margin-left: 0.5em;">{$k+1}</div>
                            </td>
                            <td>
                                <div class="text-left">{$v->name}</div>
                            </td>
                            <td class="t6">
                                <div class="text-center">{$v->date_begin}</div>
                            </td>
                            <td class="t6">
                                <div class="text-center">{$v->duration}</div>
                            </td>
                            <td class="t4">
                                <div class="text-right" style="margin-right: 0.5em;">
                                    <button class="btn btn-gray" name="btnPlay" onclick="PlayRecord('{$v->urlpath}','{$v->scoId}')">{'title8'|WM_Lang} </button>
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
    {$inlineJS}
</script>