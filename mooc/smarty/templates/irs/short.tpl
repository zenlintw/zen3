<div id="item{$serinal}" class="container {if ($serinal!==1)}hidden{/if}">

    <div class="type">
        Q{$serinal} | {'short'|WM_Lang}
    </div>
    
    <div class="row quest">
        <div class="col-md-12">{$data.text}</div>
    </div>
    
    {foreach from=$data.attaches key=k item=v}
        {if ($v.href|pathinfo:$smarty.const.PATHINFO_EXTENSION|in_array:$img_arr)}
            {assign var=show value=$v.href}
        {else}
            {assign var=show value='/public/images/irs/general-word.png'}
        {/if}

        {if ($k==0)}
            {if ($odd)}
                <div class="row">
                    <div class="col-md-12 col-xs-12"><a href="{$v.href}" target="_blank"><div class="thumbnail attach" style="background-image:url('{$show}');"></div></a></div>
                </div>
                <div class="row" style="margin-top:30px">
            {else}
                    <div class="row">
                    <div class="col-md-6 col-xs-6"><a href="{$v.href}" target="_blank"><div class="thumbnail attach" style="background-image:url('{$show}');"></div></a></div>
            {/if}
        {else}
            <div class="col-md-6 col-xs-6"><a href="{$v.href}" target="_blank"><div class="thumbnail attach" style="background-image:url('{$show}');"></div></a></div>
        {/if}
    {/foreach}
    
    {if ($data.attaches|count!=0)}
        </div>
    {/if}
    
    <hr>

    <div class="col-md-12">
        <textarea name="ans[{$data.item_id}][{$data.type}]"></textarea>
    </div>
    
    <div class="col-md-12" style="height:20px;"></div>
</div>
