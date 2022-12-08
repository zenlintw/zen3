<nav id="button{$serinal}" class="navbar-fixed-bottom {if ($serinal!==1)}hidden{/if}">
    <div class="container-fluid" style="max-width:900px;background:#7DD8BD;">
        <div class="row" style="margin-top:1rem;margin-bottom:1rem;">
            <div class="col-xs-8">
                <div class="row">
                    <div class="col-xs-3">
                        {if ($serinal !== 1)}
                            <a href="#" onclick="prevnext({$serinal})" title="{'prev'|WM_Lang}"><div style="width:4rem;height:4rem;background:#2ABB9C;border-radius:4rem;background-repeat:no-repeat;background-size:1rem;background-image:url('/public/images/irs/ic_chevron_left.svg');background-position:50% 50%"></div></a>
                        {else}
                            <div style="width:4rem;height:4rem;background:#2ABB9C;border-radius:4rem;background-repeat:no-repeat;background-size:1rem;background-image:url('/public/images/irs/ic_chevron_left_hover.svg');background-position:50% 50%"></div>
                        {/if}
                    </div>
                    <div class="col-xs-6" style="font-size:2rem;color: #FFFFFF;line-height:4rem;text-align:center;">
                    {$serinal} / {$total}
                    </div>
                    <div class="col-xs-3">
                        {if ($serinal == $total)}
                            <div style="float:right;width:4rem;height:4rem;background:#2ABB9C;border-radius:4rem;background-repeat:no-repeat;background-size:1rem;background-image:url('/public/images/irs/ic_chevron_right_hover.svg');background-position:50% 50%"></div>
                        {else}
                            <a href="#" onclick="next({$serinal})" title="{'next'|WM_Lang}"><div style="float:right;width:4rem;height:4rem;background:#2ABB9C;border-radius:4rem;background-repeat:no-repeat;background-size:1rem;background-image:url('/public/images/irs/ic_chevron_right.svg');background-position:50% 50%"></div></a>
                        {/if}
                    </div>
                </div>
            </div>
            <div class="col-xs-4">
                {if ($serinal == $total)} 
                    <a href="#" onclick="submit_answer()"><div style="float:right;background:#2ABB9C;border-radius:3px;width:8.5rem;height:4rem;font-size: 2rem;color: #FFFFFF;letter-spacing: 0;line-height:4rem;text-align:center;">{'submit'|WM_Lang}</div></a>
                {/if}
            </div> 
        </div>
    </div>
</nav>