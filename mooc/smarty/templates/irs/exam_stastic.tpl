{assign var=num value=$item|@count}
{foreach from=$item key=k item=v}
    <div class="hidden" id="item{$k+1}">
        <div style="font-size:30px">
        <div class="row back" style="display:flex">
            <div class="col-md-1 title_num">
                Q{$k+1}.
            </div>
            <div class="col-md-8 question">
                 {$v.text|strip_tags:true}
            </div>

            <div class="col-md-3 tool" style="text-align:center;margin:auto;">
                {if $k==0}
                    <img src="/public/images/irs/chevron-circle-legt_1.png" style="margin-right:20px;" title="{'prev'|WM_Lang}">
                {else}
                    <a href="#" onclick="prev({$k+1});" style="margin-right:20px;"><img src="/public/images/irs/chevron-circle-legt.png" title="{'prev'|WM_Lang}"></a>
                {/if}
                {*<a href="#" onclick="get_result();" style="margin-right:20px;"><img src="/public/images/irs/ic_refresh.png" class="refresh"></a>*}
                &nbsp;&nbsp;
                {if $num==$k+1}
                    <img src="/public/images/irs/chevron-circle-right_1.png" title="{'next'|WM_Lang}">
                {else}
                    <a href="#" onclick="next({$k+1});"><img src="/public/images/irs/chevron-circle-right.png" title="{'next'|WM_Lang}"></a>
                {/if}
            </div>
        </div>
        {if $v.type eq 2 || $v.type eq 3}
	        <div class="row items scrollbar-primary" style="margin-top:20px;">
	            {assign var=i value=0}
	            {foreach from=$v.optionals key=k1 item=v1}
	            {if $i eq 10}
	            {assign var=i value=0}
	            {/if}
	            <div id="{$v.item_id}_{$k1}" class="row" style="display:flex;">
                    <div class="col-md-1 correct"></div>
		            <div class="col-md-8 select_font" style="display:block;">
		                <div class="item_s" style="float:left;width:7%;">{$k1+1} .</div><div class="item_m" style="float:left;width:93%;">{$v1.text}</div>
		                <div style="clear:both"></div>
		                <div class="progress">
		                    <div class="progress-bar" style="width: 0%;background-color:{$color[$i]}"></div>
		                </div>
		            </div>
		            <div class="col-md-3 number" style="text-align:center;font-weight:bold;margin:auto;">
		                <div class="col-md-6" style="text-align:right;"><font color="{$color[$i]}" class="rate">0&nbsp;&nbsp;%</font></div>
		                <div class="col-md-6" style="text-align:right;padding-right:0;"><span class="people_num">0</span>&nbsp;&nbsp;{'people_num'|WM_Lang}</div>
		            </div>
	            </div>
	            {assign var=i value=$i+1}
	            {/foreach}
	        </div>
        {elseif $v.type eq 1}
	        <div class="row items scrollbar-primary" style="margin-top:20px;">
	            <div id="{$v.item_id}_0" class="row" style="display:flex;">
                    <div class="col-md-1 correct"></div>
	            <div class="col-md-8 select_font" style="display:block;">
	                <div style="float:left;width:5%;">1 .</div><div style="float:left;width:95%;">{'yes'|WM_Lang}</div> 
	                <div style="clear:both"></div>                           
	                <div class="progress">
	                    <div class="progress-bar" style="width: 0%;background-color:#815cb4"></div>
	                </div>
	            </div>
	            <div class="col-md-3 number" style="text-align:center;font-weight:bold;margin:auto;">
	                <div class="col-md-6" style="text-align:right;"><font color="#815cb4" class="rate">0&nbsp;&nbsp;%</font></div>
		            <div class="col-md-6" style="text-align:right;padding-right:0;"><span class="people_num">0</span>&nbsp;&nbsp;{'people_num'|WM_Lang}</div>
	            </div>
	            </div>
	            <div id="{$v.item_id}_1" class="row" style="display:flex;">
                    <div class="col-md-1 correct"></div>
	            <div class="col-md-8 select_font" style="display:block;">
	                <div style="float:left;width:5%;">2 .</div><div style="float:left;width:95%;">{'no'|WM_Lang}</div>
	                <div style="clear:both"></div>                             
	                <div class="progress">
	                    <div class="progress-bar" style="width: 0%;background-color:#3aabdd"></div>
	                </div>
	            </div>
	            <div class="col-md-3 number" style="text-align:center;font-weight:bold;margin:auto;">
	                <div class="col-md-6" style="text-align:right;"><font color="#3aabdd" class="rate">0&nbsp;&nbsp;%</font></div>
		            <div class="col-md-6" style="text-align:right;padding-right:0;"><span class="people_num">0</span>&nbsp;&nbsp;{'people_num'|WM_Lang}</div>
	            </div>
	            </div>
	        </div>
        {elseif $v.type eq 5}
	        <div class="row items scrollbar-primary" style="margin-top:20px;padding-left: 20px;">    
	            <div id="{$v.item_id}">
	
	            </div>
	        </div>    
        {/if}
        </div>
    </div>
{/foreach}