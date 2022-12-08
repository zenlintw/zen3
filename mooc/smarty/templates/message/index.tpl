<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/forum.css" rel="stylesheet" />
<script type="text/javascript" src="/lib/common.js"></script>
<script type="text/javascript" src="/public/js/common.js"></script>
<script type="text/javascript" src="index.js"></script>

<div class="box1">
    <div class="title">
        <div class="bread-crumb">
            <span class="home">{'title'|WM_Lang}</span>
            {if $folderPath neq ''}
            {foreach from=$folderPath key=fid item=fval}
            <span>&gt;</span>
            <span class="path"><a href="javascript:;" onclick="goFolderId('{$fval.0}');return false;">{$fval.1}</a></span>
            {/foreach}
            {/if}
            <span>&gt;</span>
            <span class="path2 now">{$folderPathNow}</span>
        </div>
    </div>
    <div class="operate" style="padding-top:0.5em;">
        <button class="btn" onclick="post();" style="width: auto;">{'func_send'|WM_Lang}</button>
        <button class="btn" onclick="del();" style="width: auto;">{'func_delete'|WM_Lang}</button>
        <button class="btn" onclick="mv();" style="width: auto;">{'func_move'|WM_Lang}</button>
    </div>
    <div class="content">
        <form name="mainFm" id="mainFm" action="" method="post" enctype="multipart/form-data" style="display: inline;">
            <input type="hidden" name="folder_id" value="" />
            <input type="hidden" name="is_search" value="" />
            <input type="hidden" name="sortby" value="{$sort}" />
            <input type="hidden" name="order" value="{$order}" />
            <input type="hidden" name="page" value="{$page_no}" />
            <input type="hidden" name="per_page" value="{$per_page}" />
            <div class="box2">
                <div class="title-bar">
                    <div class="data2">
                        <table class="table subject">
                            <tbody>
                                <tr>
                                    <td style="width:20px;">
                                        <div class="text-center"><input type="checkbox" id="ck" name="ck" exclude="true" onclick="selfunc()" /></div>
                                    </td>
                                    <td style="width:240px;">
                                        <div>{'subject'|WM_Lang}</div>
                                    </td>
                                    <td class="t4 hidden-phone">
                                        <div class="text-center">{'priority'|WM_Lang}</div>
                                    </td>
                                    <td class="t4">
                                        <div class="text-center">{'sender'|WM_Lang}</div>
                                    </td>
                                    <td class="t4 hidden-phone" style="width:160px;">
                                        <div class="text-center">{'send_time'|WM_Lang}</div>
                                    </td>
                                    <td style="width:50px;">
                                        <div class="text-center" style="margin-right: 0.5em;">{'attachment'|WM_Lang}</div>
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
                                <td style="width:20px;">
                                    <div class="text-center"><input type="checkbox" name="fid[]" value="{$v.1.0}" onclick="chgCheckbox(); event.cancelBubble=true;" /></div>
                                </td>
                                <td style="width:240px;">
                                    <div style="word-break: break-all;"><img src="/theme/default/learn/{$v.1.1}" />&nbsp;&nbsp;<a href="javascript:;" onclick="read({$v.1.0});return false;">{$v.1.2}</a></div>
                                </td>
                                <td class="t4 hidden-phone">
                                    <div class="text-center">{$v.2}</div>
                                </td>
                                <td class="t4">
                                    <div class="text-center">{$v.3}</div>
                                </td>
                                <td class="t4 hidden-phone" style="width:160px;">
                                    <div class="text-center">{$v.4}</div>
                                </td>
                                <td style="width:50px;">
                                    <div class="text-center" style="margin-right: 0.5em;">
                                        {if $v.5 eq 'Y'}
                                        <img src="/theme/default/learn/file.gif" width="9" height="14" border="0" align="absmiddle">
                                        {else}
                                        &nbsp;
                                        {/if}
                                    </div>
                                </td>
                            </tr>
                            {/foreach}
                        </table>
                    </div>
                </div>
                <div id="pageToolbar" class="paginate"></div>
            </div>
        </form>
    </div>
</div>
<form id="readFm" name="readFm" action="read.php" method="post" enctype="multipart/form-data" style="display:none">
<input type="hidden" name="serial" value="0" />
<input type="hidden" name="page" value="{$page_no}" />
</form>

<form id="sortFm" name="sortFm" action="index.php" method="post" style="display:none">
<input type="hidden" name="sortby" value="{$sort}" />
<input type="hidden" name="order" value="{$order}" />
<input type="hidden" name="page" value="{$page_no}" />
<input type="hidden" name="per_page" value="{$per_page}" />
</form>

<script type="text/javascript" src="{$appRoot}/lib/kc-paginate.js"></script>
{include file = "common/paginate_jsdeclare.tpl"}
<script type="text/javascript">
    var page_no={$page_no};
    var total_count=parseInt('{$total_count}');
    {$inlineJS}
    {literal}
    var page_size = 10;

    function goFolderId(fid) {
        aobj = getTarget();
        if ((aobj == null) || (typeof(aobj.gotoFolder) != "function")) return false;
        aobj.gotoFolder(fid);
    }

    function sort_data(val) {
        var obj = document.sortFm;
        if (obj.order.value == 'asc') {
            obj.order.value = 'desc';
        } else {
            obj.order.value = 'asc';
        }

        obj.sortby.value = val;
        obj.submit();
    }

    $(function() {
        // 分頁工具列
        $('#pageToolbar').paginate({
            'total': 0,
            'pageNumber': 1,
            'showPageList': true,
            'pageList': [10, 20, 50, 100, 200, 400],
            'showRefresh': false,
            'showSeparator': false,
            'btnTitleFirst': btnTitleFirst,
            'btnTitlePrev': btnTitlePrev,
            'btnTitleNext': btnTitleNext,
            'btnTitleLast': btnTitleLast,
            'btnTitleRefresh': btnTitleRefresh,
            'beforePageText': beforePageText,
            'afterPageText': afterPageText,
            'beforePerPageText': '&nbsp;' + beforePerPageText,
            'afterPerPageText': afterPerPageText,
            'displayMsg': displayMsg,
            'buttonCls': '',
            'onSelectPage': function(num, size) {
                if (page_no == 0) return;
                if (num == 0) return;
                if (num == page_no) {
                    return;
                }
                page_no = num;
                document.mainFm.page.value = num;
                document.mainFm.action = "index.php";
                document.mainFm.submit();
            },
            'onChangePageSize': function (pagesize) {
                document.mainFm.page.value = 1;
                document.mainFm.per_page.value = pagesize;
                document.mainFm.action = "index.php";
                document.mainFm.submit();
            }
        });

        $('#pageToolbar').paginate('refresh', {
            'total': total_count,
            'pageSize': document.mainFm.per_page.value
        });

        $('#pageToolbar').paginate('select', page_no);
        
        // 指定分頁
	    $(".paginate-number").keypress(function (e) {
	        if (e.keyCode == 13) {
	            $('#pageToolbar').paginate('select', $(this).val());
	        }
	    });
    });
    {/literal}
</script>