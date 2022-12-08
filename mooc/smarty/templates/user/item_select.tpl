{include file = "common/tiny_header.tpl"}
<style type="text/css">
{literal}

.input-search {
    /*background-image: linear-gradient(-180deg, #F2F2F2 0%, #F9F9F9 100%);*/
    border: 0 solid #FFFFFF;
    box-shadow: inset 0 1px 2px 0 #989898;
    border-radius: 6px 0px 0px 6px;
}

.search_btn {
    position:relative;
    top:13px;
    height:38px;
    background:#666666;
    border-radius: 0px 6px 6px 0px;
    border: 0px;
    margin-left: -4px;
    
}

input[type=text]::-ms-clear {  
    display: none; 
    width : 0; 
    height: 0; 
}

input[type=text]::-ms-reveal {  
    display: none; 
    width : 0; 
    height: 0; 
}

.fancybox-inner::-webkit-scrollbar-track {
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0);
	background-color: #F9F9F9;
}

.fancybox-inner::-webkit-scrollbar {
	width: 6px;
	background-color: #F9F9F9;
}

.fancybox-inner::-webkit-scrollbar-thumb {
	background-color: #7F7F7F;
}

.scrollbar-primary::-webkit-scrollbar {
    width: 8px;
    background: rgba(255,255,255,0.60);
    border-radius: 15px; 
}

.scrollbar-primary::-webkit-scrollbar-thumb {
    border-radius: 10px;
    -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.1);
    
    background: #afabaa;
     
}

#item_content {
    height:200px;
}
        
{/literal}
</style>

<div class="box1" style="width:800px;padding: 0 20px 10px 20px;">
    
    <div class="row" style="height: 60px;line-height:60px;">
    <div class="col-md-6">
	{foreach from=$item_type key=k item=v}
	    <input type="radio" name="type" value="{$k}" onclick="do_search();"><label for="type"></label>{$v}
	{/foreach}
	</div>
	<div class="col-md-6">
	    <div style="display:inline-table;">
	    <input name="keyword" id="keyword" type="text" value="{$keyword}" placeholder="" class="input-search" maxlength="20" style="width:300px;height:38px">
	    <a href="#"><i class="fas fa-times" title="{'clear'|WM_Lang}" style="font-size:22px;color:#666666;margin-left:-30px;position: relative;top: 4px;display:none"></i></a>
	    </div>
	    <button class="search_btn" onclick="do_search();"><i class="fa fa-search" title="{'course_search'|WM_Lang}" style="color: #ffffff;font-size:22px;line-height: 35px;position: relative;top: -8px;"></i></button>
	</div>
    </div>
    
    <div id="item_content" class="scrollbar-primary">         
    </div>

</div>
<script type="text/javascript">
    var sysGotoLabel = '{$label}';
    var csid = '{$csid}';
    var exam_type = '{$type}';
    {literal}

         function do_search() {
             type = $("input[name=type]:checked").val();
             keyword = $("#keyword").val();

             $.ajax({
	            'url': '/mooc/user/item_ajax.php',
	            'type': 'POST',
	            'data': {'exam_type':exam_type,'csid':csid,'item_type':type,'fulltext':keyword},
	            'dataType': "json",
	            'success': function (res) {
	                if(res.code==1) {
	                    $('#item_content').html(res.html);    
	                }
	            },
	            'error': function () {
	                alert('push Ajax Error.');
	            }
	        });
         }
         
         $(document).ready(function() {
             $("input[name=type][value='1']").attr('checked',true); 
             do_search(1);
         });
    {/literal}
</script>