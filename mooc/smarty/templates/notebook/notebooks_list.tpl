<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/notebook.css" rel="stylesheet" />
{include file = "notebook/search.tpl"}
{include file = "notebook/hr.tpl"}
<div class="box1" style="padding: 0.8em 3em 0 3em; max-width: inherit;">
    <div class="title">
        {'notebook'|WM_Lang}
    </div>
    <div class="operate">
        <a href="#add-group-box" class="btn btn-blue" id="add-notebook">
            {'add_notebooks'|WM_Lang}
        </a>
    </div>    
    <div class="content">
        <div class="data9">
            <div class="items">
            </div>
        </div>
    </div>        
    <div class="group-box" id="add-group-box">
        <div class="group-box-title">{'add_notebooks'|WM_Lang}</div>
        <div class="group-box-content">
            <div>
                <input type="text" name="title" class="span5" value="">
            </div>
            <div class="input-note">{'title_note'|WM_Lang}</div>   
            <div class="alert alert-error" style="display: none;">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{'title_error'|WM_Lang}</strong>
            </div>            
        </div>
        <div class="group-box-button">
            <button type="button" class="btn btn-warning btnNormal btn-ok">{'btn_ok'|WM_Lang}</button>
            <button type="button" class="btn aNormal btnNormal margin-left-15 btn-close">{'btn_cancel'|WM_Lang}</button>
        </div>
    </div>           
    <div class="group-box" id="mod-group-box">
        <div class="group-box-title">{'mod_notebook'|WM_Lang}</div>
        <div class="group-box-content">
            <div>
                <input type="text" name="title" class="span5" value="">
            </div>
            <div class="input-note">{'title_note'|WM_Lang}</div>   
            <div class="alert alert-error" style="display: none;">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{'title_error'|WM_Lang}</strong>
            </div>                       
        </div>
        <div class="group-box-button">
            <button type="button" class="btn btn-warning btnNormal btn-mod">{'btn_ok'|WM_Lang}</button>
            <button type="button" class="btn aNormal btnNormal margin-left-15 btn-close">{'btn_cancel'|WM_Lang}</button>
        </div>
    </div>     
    <div class="group-box" id="del-group-box">
        <div class="group-box-title">{'delete_notebooks'|WM_Lang}</div>
        <div class="group-box-content">
            {*<div class="span4"></div>*}
            <p></p>  
        </div>
        <div class="group-box-button">
            <button type="button" class="btn btn-warning btnNormal btn-del">{'btn_del'|WM_Lang}</button>
            <button type="button" class="btn aNormal btnNormal margin-left-15 btn-close">{'btn_cancel'|WM_Lang}</button>
        </div>
    </div>
</div>
<form name="notebook" method="POST" style="display: none;">
    <input type="hidden" name="fid">
    <input type="hidden" name="fname">
</form>        
<script type="text/javascript" src="{$appRoot}/theme/default/wookmark/jquery.wookmark.js"></script>
<script type="text/javascript">
    var nowlang = '{$nowlang}',
        username = '{$profile.username}',
        msg = {$msg|@json_encode};
</script>
<script type="text/javascript" src="{$appRoot}/public/js/notebook/common.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/notebook/notebooks_list.js"></script>