{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<link href="/public/css/common.css" rel="stylesheet" />
<link href="/public/css/layout.css" rel="stylesheet" />
<style>
{literal}

.data1 > .content {
    padding: 0px;
    font-size: 1em;
    line-height: 1.5em;
    word-wrap: break-word;
    background-color: white;
}

.box1 > .content {
    padding: 1em 1em 1em 2em;
}

.data1 {
    -moz-border-radius: 4px;
    -webkit-border-radius: 4px;
    border-radius: 4px;
    background: #F4F4F4;
    padding: 0;
    margin-left: -8px;
}

.paginate-toolbar a {
    display:block;
}

#mainContentTable {
    max-width: 1080px;
    min-height: calc(100vh - 300px);
}
.text-left{
	word-break: break-all;
}

.data2 .subject {
    table-layout: initial;
}

/*手機尺寸*/
@media (max-width: 767px) {
	.lcms-nav-tabs {
		min-width: 0;
		margin-left: 0px;
		margin-right: 10px;
	}

	.box-content {
		min-width: initial;
	}
	
	.box1 > .content {
		padding: 1em 0 0 2em;
		margin-bottom: 10px;
	}
	
	}
	.well-title {
		padding: 10px;
		background: #F3800F;
		color: white;
		font-size:1.2em;
		line-height: 1.4em;
		margin-bottom: 0px;
	}

	.phone{
		width: 100%;
	}
	
}

/*平板直向、平板橫向*/
@media (min-width: 768px) and (max-width: 992px) {
}

{/literal}
</style>
<script type="text/javascript">
{literal}
	$(function(){
		$("#btn1,#btn2").click(function(){
			if($(this).attr('id') == 'btn1'){
				var data = document.getElementById('cour_keyword').value;
			}else {
				var data = document.getElementById('cour_keyword2').value;
			}
			var f = document.getElementById('actFm');
			f.keyword.value = data;
			f.submit();
		});
	});
{/literal}
</script>
<div class="box1 hidden-xs container"  id="mainContentTable">
    <div class="title">{'epaper_title'|WM_Lang}</div>
    <div id="contentWrap" class="content">
		<div class="data1">
            <div class="content">
                <table><tr>
                <td><label for="cour_keyword">{'front_name'|WM_Lang}&nbsp;</label></td>
                <td><input type="text" name="cour_keyword" id="cour_keyword" value="{$download_keyword}" placeholder="{'input_tie'|WM_Lang}" onmouseover="this.focus(); this.select();" onfocus="this.focus(); this.select();" title="{'input_tie'|WM_Lang}" style="font-size:1em;height:28px;" />&nbsp;</td>
                <td><button class="btn btn-blue" id="btn1">{'search_btn'|WM_Lang}</button></td>
                </tr></table>
            </div>
        </div>
		<div class="box2">
		    <div class="title-bar">
		    	<div class="data2">
		    	<table class="table subject row">
                <tbody>
                    <tr>
                        <td class="col-sm-3 col-md-3"><div class="text-center">{'date'|WM_Lang}</div></td>
		    			<td class="col-sm-3 col-md-3"><div class="text-center">{'epaper_theme'|WM_Lang}</div></td>
		    			<td class="col-sm-6 col-md-6"><div class="text-center">{'attache'|WM_Lang}</div></td>
                    </tr>
                </tbody>
            	</table>
		    	</div>
		    </div>
		    <div class="content">
		        <div class="data2">
		        <table class="table subject row">
		        <tbody>
		        {if $downloadList|@count eq 0 }
                	<div class="message" style="color: #07aeb0;font-size: 1.2em;font-weight: bold;">{'data_null'|WM_Lang}</div>
            {else}
			        {foreach from=$downloadList key=k item=v}
			        <tr>
               			<td class="col-sm-3 col-md-3"><div class="text-center">
               				{if $v.close_date eq "0000-00-00"}
	               				{'date_limit_null'|WM_Lang}
	               				{else}
	               				{$v.close_date}
               				{/if}
               			</div></td>
  		    			<td class="col-sm-3 col-md-3"><div class="text-center">{$v.title}</div></td>
  		    			<td class="col-sm-6 col-md-6"><div class="text-left">{$v.attach_path}</div></td>
	            	</tr>
			        {/foreach}
		        {/if}
		        </tbody>
		        </table>
	    	    </div>
	    	</div>
		</div>
		<div id="pageToolbar2" class="paginate"></div>
    </div>
</div>

<div id="mainContentTableXS" class="box1 lcms-nav-tabs hidden-lg hidden-md hidden-sm visible-xs">
	<div class="title">{'epaper_title'|WM_Lang}</div>
	<table><tr>
                <td><label for="cour_keyword2">{'front_name'|WM_Lang}&nbsp;</label></td>
                <td><input type="text" name="cour_keyword2" id="cour_keyword2" value="{$keyword}" placeholder="{'input_tie'|WM_Lang}" onmouseover="this.focus(); this.select();" onfocus="this.focus(); this.select();" title="{'input_tie'|WM_Lang}" style="font-size:1em;height:28px;" />&nbsp;</td>
                <td><button class="btn btn-blue" id='btn2'>{'search_btn'|WM_Lang}</button></td>
            
              
    </tr></table>
   
    {if $downloadList|@count eq 0 }
        <div class="message" style="color: #07aeb0;font-size: 1.2em;font-weight: bold;">{'data_null'|WM_Lang}</div>
    {else}
		{foreach from=$downloadList key=k item=v}
		<div class="content">
		<table class="table subject row">
	    <tbody>
	    <tr>
	        <td class="col-xs-3" style="border-top:0px;"><div class="text-right">{'date'|WM_Lang}</div></td>
	        <td class="col-xs-9" style="border-top:0px;"><div class="text-left">
	        	{if $v.close_date eq "0000-00-00"}
	        		{'date_limit_null'|WM_Lang}
	        	{else}
	        		{$v.close_date}
	        	{/if}
	        </div></td>
	    </tr>
	    <tr>
	        <td class="col-xs-3"><div class="text-right">{'epaper_theme'|WM_Lang}</div></td>
	        <td class="col-xs-9"><div class="text-left">{$v.title}</div></td>
	    </tr>
	    <tr>
	        <td class="col-xs-3"><div class="text-right">{'attache'|WM_Lang}</div></td>
	        <td class="col-xs-9"><div class="text-left">{$v.attach_path}</div></td>
	    </tr>
	    
	    </tbody>
	    </table>

		</div>
		{/foreach}
	{/if}
	<div id="pageToolbar" class="paginate"></div>
</div>
<form name="actFm" id="actFm" action="download.php" method="post" enctype="multipart/form-data" style="display:none">
    <input type="hidden" name="token" value="{$csrfToken}" />
	<input type="hidden" name="page" value="{$page}" />
	<input type="hidden" name="keyword" value="{$keyword}" />
</form>
{include file = "common/paginate_jsdeclare.tpl"}
{include file = "common/site_footer.tpl"}
<script type="text/javascript" src="/lib/xmlextras.js"></script>
<script type="text/javascript" src="/lib/kc-paginate.js"></script>
<script type="text/javascript">
    {$inlineMajorJS}
    {literal}
	$(function() {
	    $('.paginate').paginate({
	        'total': 0,
	        'pageNumber': 1,
	        'showPageList': false,
	        'showRefresh': false,
	        'showSeparator': false,
	        'btnTitleFirst': btnTitleFirst,
	        'btnTitlePrev': btnTitlePrev,
	        'btnTitleNext': btnTitleNext,
	        'btnTitleLast': btnTitleLast,
	        'btnTitleRefresh': btnTitleRefresh,
	        'beforePageText': beforePageText,
	        'afterPageText': afterPageText,
	        'beforePerPageText': beforePerPageText,
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
	            document.actFm.page.value = num;
	            document.actFm.submit();
	        }
	    });

	    $('.paginate').paginate('refresh', {
	        'total': total_count,
	        'pageSize': page_size
	    });

	    $('.paginate').paginate('select', page_no);
		$('.paginate a').not('.disabled').attr( 'tabIndex', 0 );
	});
    {/literal}
</script>