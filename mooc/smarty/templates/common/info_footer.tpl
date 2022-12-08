<div class="bottom">
    <div class="info">
        {if $footAboutUrl !== ''}
            <div><a href="{$footAboutUrl}">{'tool_about'|WM_Lang}</a></div>
        {/if}
        {if $footContactUrl !== ''}
            {if $footAboutUrl !== ''}
                <span class="divider">|</span>
            {/if}
            <div><a href="{$footContactUrl}">{'contactus'|WM_Lang}</a></div>
        {/if}
        {if $footFaqUrl !== ''}
            {if $footAboutUrl !== '' ||  $footAboutUrl !== ''}
                <span class="divider">|</span>
            {/if}
            <div><a href="{$footFaqUrl}">{'tool_faq'|WM_Lang}</a></div>
        {/if}
    </div>
    <div class="copyright" style="right: 0;text-align:right;">{$footInfo}<br>
    {if $is_independent == 1 && $is_portal == 0}
        <script language="javascript">
          {literal}
             $('.copyright').css('bottom','-45px');
          {/literal}
        </script>
    {/if}
    </div>
</div>