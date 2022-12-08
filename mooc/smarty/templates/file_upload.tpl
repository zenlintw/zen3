<style>
    {literal}
    #upload.dragover {
        border: 3px dashed red;
    }
    {/literal}
</style>
<div class="tab-pane active dropzone" id="upload">
    <a id="btnBrowse" class="btn btn-small btn-gray fileinput-button">{'choose_files'|WM_Lang}</a><span class="multifile-upload-note">{'directly_drag'|WM_Lang}</span>&nbsp;&nbsp;&nbsp;<span class="color-orange">({'single_file_limit'|WM_Lang}{$upload_max_filesize})</span>
    <form id="uploadfile" data-url="" action="{$appRoot}/forum/m_upload.php" method="POST" enctype="multipart/form-data" class="form-inline">
        <div class="fileupload-buttonbar">
            <input id="files" type="file" name="files[]" multiple/>
        </div>
        <input type="hidden" name="tmp" value="{$tmpDir}"/>
        <input type="hidden" name="mnode" value="{$mnode}">
        <input type="hidden" name="bid" value="{$bid}">
    </form>
    <script type="text/javascript" src="{$appRoot}/theme/default/jqueryfileupload/vendor/jquery.ui.widget.js"></script>
    <script type="text/javascript" src="{$appRoot}/theme/default/jqueryfileupload/jquery.iframe-transport.js"></script>
    <script type="text/javascript" src="{$appRoot}/theme/default/jqueryfileupload/jquery.fileupload.js"></script>
</div>