<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<script type="text/javascript" src="/public/js/common.js"></script>
<a name="content2"></a>
<div class="box1">
    <div class="title">
    {if $isHw == 'homework'}{'my_homework'|WM_Lang}{else}{'my_exam'|WM_Lang}{/if}
    </div>
    <div class="content">
        <div class="box2">
            <div class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td class="t4">
                                    <div class="text-left" style="margin-left: 0.5em;">{'course_no'|WM_Lang}</div>
                                </td>
                                <td class="text-left">
                                    <div class="text-left">{'course_name'|WM_Lang}</div>
                                </td>
                                <td class="t4 hidden-phone">
                                    <div class="text-center">{if $isHw == 'homework'}{'should_do_homework'|WM_Lang}{else}{'should_do_exam'|WM_Lang}{/if}</div>
                                </td>
                                <td class="t4 hidden-phone">
                                    <div class="text-center">{if $isHw == 'homework'}{'not_do_homework'|WM_Lang}{else}{'not_do_exam'|WM_Lang}{/if}</div>
                                </td>
                                <td class="t3">
                                    <div class="text-right" style="margin-right: 0.5em;">{if $isHw == 'homework'}{'submit_homework'|WM_Lang}{else}{'do_exam'|WM_Lang}{/if}</div>
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
                            <td class="t4">
                            <div class="text-left" style="margin-left: 0.5em;">{$k}</div>
                            </td>
                            <td>
                                <div class="text-left">{$v.caption}</div>
                            </td>
                            <td class="t4 hidden-phone">
                                <div class="text-center">{$v.total_do}</div>
                            </td>
                            <td class="t4 hidden-phone">
                                <div class="text-center">{$v.total_undo}</div>
                            </td>
                            <td class="t3">
                                <div class="text-right" style="margin-right: 0.5em;">
                                    <button class="btn btn-gray" onclick="parent.chgCourse({$k},{$nEnv},1,'{$label}')"> Go </button>
                                </div>
                            </td>
                        </tr>
                        {/foreach}
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
    {/literal}
</script>