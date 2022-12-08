{* 已上傳素材 *}
{if $filesInTable|@count >= 1}
    {assign var=display value=""}
{else}
    {assign var=display value="display: none;"}
{/if}
<div class="esn-box" id="fileList" style="{$display}">
    <div id="message" style="display: none">
        <div class="alert alert-lcms-error">
            <button type="button" class="close">&times;</button>
            <span></span>
        </div>
    </div>
    <form id="fileupload" action="" method="POST" enctype="multipart/form-data" class="form-inline">
        <table role="presentation" id="upload-result" style="font-size: 1em; font-weight: bold;">
            <thead>
            <tr>
                <th class="span2 left-radius-4">{'title_file_name'|WM_Lang}</th>
                <th class="span2"></th>
                <th class="right-radius-4">{'title_action'|WM_Lang}</th>
            </tr>
            </thead>
            <tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery">
                {foreach from=$filesInTable key=k item=v}
                    <tr class="template-download">
                        <td style="word-break: break-all;" colspan="2">
                            <div class="title"><a href="{$v.path}" target="_blank">{$v.filename|escape} ({$v.file_size})</a></div>
                            <input type="hidden" name="originalFilename[]" value="{$v.filename}">
                            <input type="hidden" name="diskFilename[]" value="{$v.disk_filename}">
                            <input type="hidden" name="deleteFlag[]" value="N" class="deleteFlag">
                            <input type="hidden" name="title[]" value="{$v.filename}">
                        </td>
                        <td class="action">
                            <a href="javascript:void(0);" class="lcms-icon-delete delete" data-type="DELETE" data-url="{$v.disk_filename}"></a>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </form>
</div>