<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/theme/default/learn_mooc/common.css" rel="stylesheet" />
<link href="{$appRoot}/theme/default/learn_mooc/forum.css" rel="stylesheet" />
{include file = "common/htmleditorlib.tpl"}
<style>
    @import "{$appRoot}/theme/default/learn_mooc/filelist.css";
</style>
<div style="width: auto; margin: auto; max-width: 1000px;">
    <ul class="title-bar" style="font-size: 1.1em;" id="forum-page-title">
        <li class="left bread-crumb" style="text-align: left; font-size: 1em; margin-bottom: 0.5em;">
            {if $isBreadCrumb eq '1'}
                {if $pathFlag eq '1'}
                    {if $isGroupForum}
                    <span class="pathGroup">{'group_discussed'|WM_Lang}</span>
                    {else}
                    <span class="path">{'topics_discussed'|WM_Lang}</span>
                    {/if}
                    <span>&gt;</span>
                {/if}
            {/if}
            <span class="path2">{$forumName}</span>
            <span>&gt;</span>
            <span class="now">{$actionName}</span>
        </li>
    </ul>
    <div class="row"></div>
    <div class="box box-padding-t-1">
        <div class="forum-write">
            <form id="baseFm" accept-charset="UTF-8" lang="zh-tw" method="POST" action="" onsubmit="return false;">
                <table class="forum-table" name="forum-table" style="font-size: 1em;">
                    <tbody>
                        <tr class="attach">
                            <th><span class="strong-note">*</span>{'topic'|WM_Lang}</th>
                            <td>
                                {if $isreply eq 1}
                                    <span title="{$subject}" class="subject">{$subject}</span>
                                    <input name="subject" type="hidden" value="{$subject}">
                                {else}
                                    <input name="subject" type="text" value="{$subject}" maxlength="255" >
                                {/if}
                                <input name="type" type="hidden" value="{$type}">
                                <input name="isHTML" type="hidden" value="1">
                                <input name="whoami" type="hidden" value="write.php">
                                <input name="ticket" type="hidden" value="{$ticket}">
                                <input name="mnode" type="hidden" value="{$mnode}">
                                <input name="node" type="hidden" value="{$node}">
                                <input name="etime" type="hidden" value="{$etime}">
                                <input name="isReply" type="hidden" value="{$isreply}">
                                <input name="img_src" type="hidden" value="">
                            </td>
                        </tr>
                        {if $isreply eq 1}
                        <tr id="topic-content">
                            <th></th>
                            <td style="padding-bottom: 1em;">
                                <div class="content" style="background-color: #F4F4F4;word-break: break-all;">
                                    {$main.postcontent}
                                </div>     
                            </td>
                        </tr>
                        {/if}
                        <tr class="htmleditor">
                            <th><span class="strong-note">*</span>{'content'|WM_Lang}</th>
                            <td>
                                {$content}
                                {*<textarea id="content" name="content" rows="10">{$content}</textarea>*}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="clear-both"></div>
                <div class="margin-bottom-15">&nbsp;</div>
            </form>
            <div style="margin-top: -25px;">
                <div>
                    <!--上傳-->
                    <table class="forum-table" style="font-size: 1.1em;">
                        <tbody>
                            <tr>
                                <th>{'upload_file'|WM_Lang}</th>
                                <td>
                                    {include file="file_upload.tpl"}
                                    {include file="file_list.tpl"}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!--簽名檔-->
                    <div class="horizontal" style="margin-left: 8.2em;">
                        <form id="extraFm" method="POST" name="extraFm">
                            {foreach from=$tagline key=k item=v}
                                <label class="radio">
                                    <input type="radio" name="tagline" value="{$k}" {if $k eq -1}checked{/if}>
                                    <span class="lcms-checkbox-text">{$v}</span>
                                </label>
                            {/foreach}
                        </form>
                    </div>
                    <div class="row"></div>
                    <div class="actions">
                        <button type="button" class="btn btn-info" id="btnSubmit">{'confirm'|WM_Lang}</button>
                        <button type="button" class="btn" id="btnCancel">{'cancel'|WM_Lang}</button>
                    </div>
                </div>
                <div class="margin-bottom-40">&nbsp;</div>
            </div>
            <form name="node_chain" method="POST" style="display: none;" action="">
                <input type="hidden" name="cid" value=""/>
                <input type="hidden" name="bid" value="{$bid}">
                <input type="hidden" name="enbid" value="{$enbid}">
                <input type="hidden" name="nid" value="{$nid}">
		<!--	043884(B) -->
                <input type="hidden" name="page" value="{$page}">
		<!--	043884(E) -->
            </form>
            <form id="formAction" method="post" action="" style="display: none;">
                <input type="hidden" name="cid" value=""/>
                <input type="hidden" name="bid" value=""/>
                <input type="hidden" name="nid" value=""/>
                <input type="hidden" name="mnode" value=""/>
                <input type="hidden" name="subject" value=""/>
                <input type="hidden" name="content" value=""/>
                <input type="hidden" name="awppathre" value=""/>
            </form>
        </div>
    </div>
    <div>&nbsp;</div>
</div>
<script type="text/javascript">
    var bltBid = '{$bltBid}',
        cid = '{$cid}',
        bid = '{$bid}',
        nowlang = '{$nowlang}',
        env = '{$env}';
        msg = {$msg|@json_encode};
        postFrom = '{$postFrom}';
        noteId = '{$noteId}';
</script>
<script type="text/javascript" src="{$appRoot}/public/js/forum/forum.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/forum/file.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/forum/edit.js?20170307"></script>