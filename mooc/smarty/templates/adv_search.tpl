<style type="text/css">
{if $banner_bgCss === 'Y'}
    {if $theme === 'orange'}
        {literal}
        .nav-adv-search {
            background: transparent url("/base/{$schoolId}/door/tpl/banner_bg.png") repeat;
            background-color:#ffcc00;
            background-image:none;
        }
        .slidebox {
            height: 267px;
            background-color: #ffcc00;
        }
        .slideboxContainer .slideboxThumbs .slideboxThumb {
            background: url("/public/images/point_1.png") no-repeat 0 0px;
            margin-right:20px;
        }
        .slideboxContainer .slideboxThumbs .slideboxThumb:hover,.slideboxContainer .slideboxThumbs .selectedSlideboxThumb {
            background: url("/public/images/point.png") no-repeat 0 0px;
        }
        .newButton {
            background-color:#e55756;
        }
        {/literal}
    {elseif $theme === 'blue'}
        {literal}
        .nav-adv-search {
            background: transparent url("/base/{$schoolId}/door/tpl/banner_bg.png") repeat;
            background-color: #5ad8d4;
            background-image:none;
        }
        .slidebox {
            height: 267px;
            background-color: #5ad8d4;
        }
        .slideboxContainer .slideboxThumbs .slideboxThumb {
            background:  url("/public/images/point_1.png") no-repeat 0 0px;
            margin-right:20px;
        }
        .slideboxContainer .slideboxThumbs .slideboxThumb:hover,.slideboxContainer .slideboxThumbs .selectedSlideboxThumb {
            background: url("/public/images/point.png") no-repeat 0 0px;
        }
        .newButton {
            background-color:#048e89;
        }
        {/literal}
    {elseif $theme === 'black'}
        {literal}
        .nav-adv-search {
            background: transparent url("/base/{$schoolId}/door/tpl/banner_bg.png") repeat;
            background-color: #abaaaa;
            background-image:none;
        }
        .slidebox {
            height: 267px;
            background-color: #abaaaa;
        }
        .slideboxContainer .slideboxThumbs .slideboxThumb {
            background: url("/public/images/point_1.png") no-repeat 0 0px;
            margin-right:20px;
        }
        .slideboxContainer .slideboxThumbs .slideboxThumb:hover,.slideboxContainer .slideboxThumbs .selectedSlideboxThumb {
            background: url("/public/images/point.png") no-repeat 0 0px;
        }
        .newButton {
            background-color:#494949;
        }
        {/literal}
    {else}
        {literal}
        .nav-adv-search {
            background: transparent url("/base/{$schoolId}/door/tpl/banner_bg.png") repeat;
            background-color: #abaaaa;
            background-image:none;
        }
        .slidebox {
            height: 267px;
            background-color: #abaaaa;
        }
        .slideboxContainer .slideboxThumbs .slideboxThumb {
            background: url("/public/images/point_1.png") no-repeat 0 0px;
            margin-right:20px;
        }
        .slideboxContainer .slideboxThumbs .slideboxThumb:hover,.slideboxContainer .slideboxThumbs .selectedSlideboxThumb {
            background: url("/public/images/point.png") no-repeat 0 0px;
        }
        .slideboxCaption {
            
        }
        .newButton {
            background-color:#494949;
        }
        {/literal}
    {/if}
{/if}
{literal}
.slideboxContainer {
    display:inline-block;
    z-index:1;
}

#news_show_right {
    z-index:1;
}
.one-edge-shadow {
	-webkit-box-shadow: 0 8px 6px -6px black;
    -moz-box-shadow: 0 8px 6px -6px black;
    box-shadow: 0 8px 6px -6px black;
}
{/literal}
</style>
<div class="nav-adv-search" style="padding-top: 10px;padding-bottom: 40px; height:227px;">
    {*{if $switch.content_sw.searchbar === 'true'}
    <div class="narrow">
        <div class="section-search">
            <div class="title">
                <span class="h1">
                    {$bannerT1}
                </span>
                <span class="h2">
                    {$bannerT2}
                </span>
            </div>
            <div class="search">
                <div class="input-append">
                    <input name="keyword" id='bar-keyword' type="text" value="{$keyword}" placeholder="{'searchcourse'|WM_Lang}">
                    <button class="btn btn-gray-light" type="button" onclick="adv_search();"><i class="icon-search icon-white"></i></button>
                </div>
            </div>
            <div class="h4">
                {$bannerT3}
            </div>
        </div>
        <div class="section-adv" style="background: transparent url('{$advimg}') no-repeat;"></div>
    </div>
    {/if}
    {if $switch.content_sw.searchbar === 'false'}*}
    {$ads}
    <form name="bnode_list" method="POST" target="bnewsFrame" style="display: none;">
	<input type="hidden" name="token" value="{$csrfToken}" />
        <input type="hidden" name="cid">
        <input type="hidden" name="bid">
        <input type="hidden" name="nid">
    </form>
    <iframe id="bnewsFrame" name="bnewsFrame" src="about:blank" style="display: none;" height="600" width="800"></iframe>
    {*{/if}*}
</div>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/search.js"></script>
<script src="{$appRoot}/lib/jquery/jquery-ui.custom.min.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/jquery/jquery.mSimpleSlidebox.js"></script>