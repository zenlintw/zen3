<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/notebook.css?{$smarty.now}" rel="stylesheet" />
<link href="{$appRoot}/public/css/cour_path.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/third_party/mCustomScrollbar/jquery.mCustomScrollbar.min.css" rel="stylesheet" />
{include file = "common/htmleditorlib.tpl"}
{include file = "notebook/search.tpl"}
{include file = "notebook/hr.tpl"}
 <style>
{* 上傳附件樣式修正 *}
{literal}
    #upload.dragover {
        border: 3px dashed red;
    }
    /*上傳元件*/
    #uploadfileSubtitle , #uploadfileCover {
        display: none;
    }

    #btnBrowse {
        display: none;
    }

    /* webkit and opera */
    @media all and (min-width:0){
        #btnBrowse {
            display: inline-block;
        }
        #uploadfile {
            display: none;
        }
    }

    /* webkit */
    @media screen and (-webkit-min-device-pixel-ratio:0) {
        #btnBrowse {
            display: inline-block;
        }
        #uploadfile {
            display: none;
        }
    }

    /*FireFox*/
    @-moz-document url-prefix() {
        #btnBrowse {
            display: inline-block;
        }
        #uploadfile {
            display: none;
        }
    }

    /*IE9+*/
    @media all and (min-width:0) {
        #btnBrowse {
            display: inline-block;
        }
        #uploadfile {
            display: none;
        }
    }

    /*IE10+*/
    @media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {
        #btnBrowse {
            display: inline-block;
        }
        #uploadfile {
            display: none;
        }
    }
{/literal}
{* 分頁樣式修改 *}
{literal}
        .paginate-toolbar input {
            width: 40px !important;
            text-align: center;
            height: 24px;
            line-height: 24px;
            margin-top: 0.2em;
        }
        .paginate-number-after {
            margin-left: 0.3em;
        }              
        .paginate-first {
            background: transparent url("/public/images/icon_nb_first.png") no-repeat 0px 0px;
            width: 15px;
            height: 13px;
            margin-left: 0.5em;
            margin-right: 0.5em;                                
        }                          
        .paginate-first:hover {
            background: transparent url("/public/images/icon_nb_first.png") no-repeat 0px 0px;
        }    
        .disabled .paginate-first {
            background: transparent url("/public/images/icon_nb_first.png") no-repeat 0px 0px;
        }                
        .paginate-prev {
            background: transparent url("/public/images/icon_nb_prev.png") no-repeat 0px 0px;
            width: 15px;
            height: 13px;
            margin-left: 0.5em;
            margin-right: 0.5em; 
        }                            
        .paginate-prev:hover {
            background: transparent url("/public/images/icon_nb_prev.png") no-repeat 0px 0px;
        }         
        .disabled .paginate-prev {
            background: transparent url("/public/images/icon_nb_prev.png") no-repeat 0px 0px;
        }                  
        .paginate-next {
            background: transparent url("/public/images/icon_nb_next.png") no-repeat 0px 0px;
            width: 15px;
            height: 13px;
            margin-left: 1em;
            margin-right: 0.5em; 
        }                            
        .paginate-next:hover {
            background: transparent url("/public/images/icon_nb_next.png") no-repeat 0px 0px;
        }  
        .disabled .paginate-next {
            background: transparent url("/public/images/icon_nb_next.png") no-repeat 0px 0px;
        }         
        .paginate-last {
            background: transparent url("/public/images/icon_nb_last.png") no-repeat 0px 0px;
            width: 15px;
            height: 13px;
            margin-left: 0.5em;
            margin-right: 0.5em; 
        }                          
        .paginate-last:hover {
            background: transparent url("/public/images/icon_nb_last.png") no-repeat 0px 0px;
        }               
        .disabled .paginate-last {
            background: transparent url("/public/images/icon_nb_last.png") no-repeat 0px 0px;
        }
{/literal}
</style>
<div class="box3">
    <div class="row-fluid">
        <div class="pull-left">
            <div class="title">{'notebook'|WM_Lang}</div>        
        </div>
        <div class="pull-right">
            <div class="operate">
                {* <a class="btn btn-blue back">{'back'|WM_Lang}</a> *}
                <a class="btn btn-blue back-list">{'backlist'|WM_Lang}</a>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="box3-main" data-id="ajax">
            <div class="paper">
                <div class="top"> 
                    <div class="row-fluid">
                        <div class="pull-left">
                            <div class="main-title" style="">
                                <div class="title visible-phone breakword" title="{$fname}">
                                    {$fname}
                                </div>
                                <div class="title sm-title hidden-phone breakword" title="{$fname}">
                                    {$fname}
                                </div>
                                <div class="stereo"></div>
                            </div> 
                        </div>
                        {* 功能按鈕 *}
                        <div class="pull-right">
                            <div class="operate">
                                <div class="msg-annouce" title="{'system_message'|WM_Lang}">
                                    <div>{'editing'|WM_Lang}</div>
                                    <div>{'not_save_yet'|WM_Lang}</div>
                                </div>
                                <div class="icon-save" title="{'save_note'|WM_Lang}"></div>
                                <div class="icon-new" title="{'add_notebook'|WM_Lang}"></div>
                                <div class="tab-pane active dropzone" id="upload" title="{'upload_file'|WM_Lang}">
                                    <div class="fileinput-button">        
                                    </div>      
                                    <span class="badge badge-important">6+</span>          
                                    <form id="uploadfile" data-url="" action="{$appRoot}/message/m_upload.php" method="POST" enctype="multipart/form-data" class="form-inline">
                                        <div class="fileupload-buttonbar">
                                            <input id="files" type="file" name="files[]" style="filter:alpha(opacity: 0); width: 37px; border-width: 1px; border-color: #000; position: absolute; top: 0px; height: 37px;" multiple/>
                                        </div>
                                    </form>
                                    <script type="text/javascript" src="{$appRoot}/theme/default/jqueryfileupload/vendor/jquery.ui.widget.js"></script>
                                    <script type="text/javascript" src="{$appRoot}/theme/default/jqueryfileupload/jquery.iframe-transport.js"></script>
                                    <script type="text/javascript" src="{$appRoot}/theme/default/jqueryfileupload/jquery.fileupload.js"></script>
                                </div>
                                <a href="#del-group-box" class="icon-delete" title="{'delete_notebook'|WM_Lang}"></a>
                                <div class="share" style="display: none;">
                                    <div class="share-icon" title="{'share_notebook'|WM_Lang}"></div>
                                    <div class="share-link"></div>
                                    <div class="share-list">
                                        {if 'FB'|in_array:$socialShare}
                                        <div class="fb"></div>
                                        {/if}  
                                        {if 'LINE'|in_array:$socialShare}
                                        <a id="share-ln" href="#inline-ln" title="LINE">
                                            <div class="line"></div>
                                        </a>
                                        {/if}  
                                        {if 'WECHAT'|in_array:$socialShare}
                                        <a id="share-wct" data-fancybox-type="iframe" href="#inline-wct" title="{'open_wct'|WM_Lang}">
                                            <div class="wct"></div>
                                        </a>   
                                        {/if}                                     
                                    </div>
                                </div>
                            </div>         
                        </div>
                    </div>            
                    <div class="subtitle"></div>         
                    <div class="subtitle-mod">
                        <input type="text" name="subject" value="" size="64" maxlength="200" placeholder="{'enter_note_title'|WM_Lang}">
                        {*<div class="hidden-phone"></div>*}
                    </div>
                    <div class="mod-time pull-right hidden-phone"></div>
                    <div class="clearfix"></div>
                </div>
                <div class="hr hidden-phone"></div>
                <div class="page-controller">
                    <span class="prev pull-left">{'prev_note'|WM_Lang}</span>
                    <span class="next pull-right">{'next_note'|WM_Lang}</span>
                    <div class="clearfix"></div>
                </div>
                <div class="content">   
                    <div class="share-info pull-left hidden-phone">  
                        <div class="share-icon"></div>
                        <div class="share-caption">{'sharepeople'|WM_Lang}: </div>
                        <div class="share-text"></div>      
                    </div>
                    <div class="clearfix"></div>
                    <div class="notebook-article" data-mcs-theme="dark-2"></div>
                    <div class="notebook-mod">
                        {$notebook_mod_content}
                    </div>
                    <div class="times">0</div>
                    {* 分頁 *}
                    <div class="row-fluid" style="margin-top: 0.8em;">
                        <div id="pageToolbar" class="paginate" style="display: none;"></div>
                    </div>
                    {* 附檔顯示 *}
                    <div class="file-list"></div>                    
                </div> 
            </div>
            {* 頁底書本效果 *}
            <div class="efficacy">
                <div class="efficacy_m"></div>
                <div class="efficacy_l"></div>
                <div class="efficacy_r"></div>
            </div>
        </div>
        {* 側邊筆記列表 *}
        <div class="box3-sidebar">
            <div class="sidebar-expand">
                <div class="icon"></div>
            </div>
            <div class="sidebar-list">       
                <div class="items">
                    {*
                    <div class="item" data-id="" style="display: flex; width: 93%;">
                        <div class="icon"style="width: 13px; height: 15px; background-image: url(/public/images/icon_nb_item.png); display: table;"></div>
                        <div class="title" style="position: relative; top: -0.1em; margin-left: 0.4em; font-size: 1.1em;">2長板玩法長板玩法長板玩法長板玩法長板玩法長板玩法長板玩法長板玩法長板玩法長板玩法長板玩法長板玩法長板玩法長板玩法長板玩法長板玩法</div>
                    </div>
                    *}
                </div>
            </div>
        </div>
    </div>
    {* 刪除筆記對話框 *}
    <div class="group-box" id="del-group-box">
        <div class="group-box-title">{'delete_notebook'|WM_Lang}</div>
        <div class="group-box-content">
            {*<div class="span4"></div>*}
            <p></p>  
        </div>
        <div class="group-box-button">
            <button type="button" class="btn btn-warning btnNormal btn-del">{'btn_del'|WM_Lang}</button>
            <button type="button" class="btn aNormal btnNormal margin-left-15 btn-close">{'btn_cancel'|WM_Lang}</button>
        </div>
    </div>
    {* 分享的 fancybox *}
    <div id="inline-ln" class="inline-ln" style="display: none;">
        <form class="well">
            <div>{'line_supports_mobile'|WM_Lang}</div>
        </form>
    </div>
    <div style="width: auto; height: auto; overflow: auto; position: relative;">
        <div id="inline-wct" class="inline-wct" style="display: none;">
            <img src=""/>
        </div>
    </div>
    <div class=""></div>
</div>
{* 儲存中的動態提示 *}
<span class="isloading-wrapper isloading-show isloading-overlay" style="display: none;">
    <i class="icon-loader icon-spin"></i>{'msg_saving'|WM_Lang}
</span>
<form name="notebook" method="POST" style="display: none;">
    <input type="hidden" name="fid">
</form>  
<form>
    <input type="hidden" id="selectPage" name="selectPage" value="1">
    <input type="hidden" id="inputPerPage" name="inputPerPage" value="1"/>
</form>
<div style="color: #ececec; display: none;" alt="remove this dom"><a name="go_result" href="m_result.php">.</a></div>
<script type="text/javascript">
    var nowlang = '{$nowlang}',
        username = '{$profile.username}',
        cticket = '{$cticket}',
        fid = '{$fid}',
        assignId = '{$id}',
        path = '{$userPath}',
        msg = {$msg|@json_encode};
</script>
<script src="{$appRoot}/public/js/third_party/is-loading/jquery.isloading.js"></script>
<script src="{$appRoot}/public/js/third_party/mCustomScrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/kc-paginate.js"></script>
{include file = "common/paginate_jsdeclare.tpl"}
<script type="text/javascript" src="{$appRoot}/public/js/common.js?{$smarty.now}"></script>
<script type="text/javascript" src="{$appRoot}/public/js/notebook/common.js?{$smarty.now}"></script>
<script type="text/javascript" src="{$appRoot}/public/js/notebook/notebook.js?{$smarty.now}"></script>
<script type="text/javascript" src="{$appRoot}/public/js/notebook/file.js?{$smarty.now}"></script>
<script type="text/javascript" src="{$appRoot}/lib/json2.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/md5.min.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/base64.js"></script>