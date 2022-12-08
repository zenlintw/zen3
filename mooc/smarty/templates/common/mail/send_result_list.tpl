<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<a name="content2"></a>
<div class="box1">
    <div class="title">{$pageTitle}</div>
    <div class="content">
        <div class="box2">
            <div class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td class="t4">
                                    <div class="text-left" style="margin-left: 0.5em;">{$FieldTitle1}</div>
                                </td>
								<td>
                                    <div class="text-left">{$FieldTitle2}</div>
                                </td>
                                <td class="t3">
                                    <div class="text-right" style="margin-right: 0.5em;">{$FieldTitle3}</div>
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
                            <div class="text-left" style="margin-left: 0.5em;">{$v.seq}</div>
                            </td>
							<td>
                                <div class="text-left">{$v.mail}</div>
                            </td>
                            <td class="t3">
                                <div class="text-right" style="margin-right: 0.5em;">{$v.result}</div>
                            </td>
                        </tr>
                        {/foreach}
                    </table>
                </div>
            </div>
            <div class="text-right" style="margin-right: 0.5em;">
                <button class="btn btn-blue" {$btnAction}>{$btnText}</button>
            </div>
        </div>
    </div>
</div>
{$inlineJS}
<script type="text/javascript">
</script>