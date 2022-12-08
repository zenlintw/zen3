<div id="item{$serinal}" class="container {if ($serinal!==1)}hidden{/if}">

    <div class="type">
        Q{$serinal} | {'s_choice'|WM_Lang}
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
    
    {foreach from=$data.optionals key=k item=v}
        <div class="s_choice{$serinal}">
            <div class="col-md-12 choice_item" onclick="select(this,{$serinal})">
                <div class="radio_icon"></div>
                <div class="choice_text" style="margin:auto 0;width:95%;padding:10px;">{$v.text}</div>
                <input type="radio" value="{$k+1}" name="ans[{$data.item_id}][{$data.type}]" style="display:none;"/>
            </div>
            {if ($v.attaches|@count)>0}
                {foreach from=$v.attaches key=a_k item=a_v}
                    {if ($a_v.href|pathinfo:$smarty.const.PATHINFO_EXTENSION|in_array:$img_arr)}
                        {assign var=choice_show value=$a_v.href}
                    {else}
                        {assign var=choice_show value='/public/images/irs/general-word.png'}
                    {/if}

                    <div class="col-md-6 col-xs-6"><a href="{$a_v.href}" target="_blank"><div class="thumbnail attach" style="background-image:url('{$choice_show}');"></div></a></div>
                    <div style="clear:both"></div>
                {/foreach}
            {/if}
        </div>
    {/foreach}
    
</div>
