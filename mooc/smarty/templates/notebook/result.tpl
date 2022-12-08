<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/notebook.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/third_party/mCustomScrollbar/jquery.mCustomScrollbar.min.css" rel="stylesheet" />
{include file = "notebook/search.tpl"}
{include file = "notebook/hr.tpl"}
<div class="box3" style="padding: 0.8em 2em 2em 2em;">
    <div class="row-fluid" style="margin-bottom: 0.4em;">
        <div class="left">
            <div class="title" style="
            font-size: 1.8em;
            color: #000000;
            line-height: normal;
            font-weight: bold;">{'notebook'|WM_Lang}</div>        
        </div>
        <div class="right">
            <div class="operate">
                <a class="btn btn-blue back-list" style="
        font-size: 1.1em;
         ">{'backlist'|WM_Lang}</a>
            </div>
        </div>
    </div>
   
    <div>
        <div class="box3-main" style="
background: #FFFFFF;
padding: 1em 1em 1em 1em;
-moz-border-radius: 4px;
-webkit-border-radius: 4px;
border-radius: 4px;
float: left;
width: 96.6%;
border: 1px solid #C4C4C4;
    " data-id="ajax">
            <div class="paper" style="
padding: 1em 1em 0em 1em;
-moz-border-radius: 4px;
-webkit-border-radius: 4px;
border-radius: 4px;
min-height: 565px;
    ">
                <div class="top">    
                    <div class="bar" style=" background-color: #F6AC1F;
  width: 100%;
  color: #FFFFFF;
  position: relative;
  left: -2em;
  font-size: 1.5em;
  line-height: 1.9em;
  padding-right: 1em;
  padding-left: 1em;">  
                        <div class="row-fluid">
                            <div class="left">   
                                <div class="keyword-section"> 
                                    <span>{'search'|WM_Lang}：</span>
                                    <span class="keyword"></span>
                                </div>  
                            </div>
                            <div class="right">
                                <div class="total-section">
                                    <span>{'total'|WM_Lang}</span>
                                    <span class="total">0</span>
                                    <span>{'items'|WM_Lang}</span>
                                </div>       
                            </div>
                        </div>  
                    </div>  
                    <div class="stereo" style="width: 0;
  height: 0;
  border-style: solid;
  border-width: 0 13px 13px 0;
  border-color: transparent #f3800f transparent transparent;
  position: relative;
  left: -40.6px;
  top: 0px;">        
                    </div>
                </div>
                <div class="result">   
                    <div class="items" data-mcs-theme="minimal-dark" style="
height: 512px;
overflow: auto;">
                        {*
                        <div class="notebook">          
                            <div class="cover">       
                                <div class="icon" style="width: 13px; height: 15px; background-image: url(/public/images/icon_nb_item.png); display: block; float: left; margin-top: 0.2em;"></div>     
                                <div class="title" style="font-size: 1.4em;
  color: rgb(0, 119, 122);
  font-weight: bold;
  height: initial;
  margin-left: 1.5em;">1        
                                </div>      
                            </div>   
                            <div class="content" style="padding-left: 1.7em;
  margin-top: 0.7em;
  font-size: 1.2em;
  height: initial;">    
                                <div class="item" style="margin-bottom: 1em;">  
                                    <div class="row-fluid">
                                        <div class="title left span2">2        
                                        </div>            
                                        <div class="fit right span10" style="margin-left: 0;">3
                                        </div>
                                    </div>
                                </div>     
                                <div class="item" style="margin-bottom: 0;">     
                                    <div class="row-fluid">
                                        <div class="title left span2">4  
                                        </div>            
                                        <div class="fit right span10" style="margin-left: 0;">5        
                                        </div>   
                                    </div> 
                                </div>    
                            </div>    
                            <div class="hr" style="border-bottom: rgb(190, 192, 192) dotted 1px;
  margin-bottom: 0.3em;"> 
                            </div>
                        </div>
                        *}
                    </div> 
                </div> 
            </div>
        </div>
        
    </div>    
    <div class="">        
    </div>
</div>
<form name="notebook" method="POST" style="display: none;">
    <input type="hidden" name="fid">
    <input type="hidden" name="fname">
    <input type="hidden" name="id">
</form>  
<script type="text/javascript">
    var nowlang = '{$nowlang}',
        username = '{$profile.username}',
        cticket = '{$cticket}',
        fid = '{$fid}',
        path = '{$userPath}',
        msg = {$msg|@json_encode};
</script>
<script src="{$appRoot}/public/js/third_party/mCustomScrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/notebook/common.js"></script>