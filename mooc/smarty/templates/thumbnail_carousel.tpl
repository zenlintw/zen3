<style>
{literal}
/* Removes the default 20px margin and creates some padding space for the indicators and controls */
.left {
	float: none;
}

.well1 {
	background-color: #FFFFFF;
	border: 0px;
	padding: 0px;
	margin-bottom: 20px;
}

.carouselbottom {
	margin: 0px auto;
    margin-top: 15px;
	margin-bottom: 15px;
    background-color: #FFFFFF;
}

.thumbnail {
	margin-bottom: 0px;
}
.carousel {
    margin-bottom: 0;
	// padding: 0 40px 30px 40px;
	padding: 0px;
	padding-left: 20px;
}
/* Reposition the controls slightly */
.carousel-control {
	left: -12px;
	height: 70px;
	width: 16px;
	margin-top: 0px;
	border-radius: 3px;
	-webkit-border-radius: 3px;
	background-color: #C0C0C0;
	color: #A0A0A0;
}

.carousel-control:focus, .carousel-control:hover {
	color: #A0A0A0;
}

.carousel-control.left {
	background-image : none;
}

.carousel-control.left img{
	margin-top:25px;
}

.carousel-control.right {
	right: -12px;
	background-image : none;
}

.carousel-control.right img{
	margin-top:25px;
}

/* Changes the position of the indicators */
.carousel-indicators {
	right: 50%;
	top: auto;
	bottom: 0px;
	margin-right: -19px;
}

/* Changes the colour of the indicators */
.carousel-indicators li {
	background: #c0c0c0;
}

.carousel-indicators .active {
	background: #e88e00;
}

.row-fluid .span3 {
	width: 14.8%;
	display: inline-table;
	float: left;
}

/*手機尺寸*/
@media (max-width: 767px) {
	.container {
		min-width: 320px;
	}

	.carouselbottom {
		margin-bottom: 49px;
	}
	
	.carousel {
		padding-left: 15px;
	}
	.span12 {
		width: 279px;
	}
	
	.row-fluid .span3 {
		width: 45%;
	}
	
	.row-fluid [class*="span"] {
		margin-left: 4.2%
	}
	.carousel-control {
		height: 52px;
	}
	
	.carousel-control.left img{
		margin-top:15px;
	}

	.carousel-control.right img{
		margin-top:15px;
	}
}

/*平板直向、平板橫向*/
@media (min-width: 768px) and (max-width: 992px) {
	.container {
		min-width: 768px;
		width: 768px;
		height: 52px;
	}
	
	.span12 {
		width: 903px;
	}
	
	.row-fluid .span3 {
		width: 22.425%;
	}
	
	.row-fluid [class*="span"] {
		margin-left: 2.6%
	}
	.carousel-control {
		height: 79px;
	}
	
	.carousel-control.left img{
		margin-top:30px;
	}

	.carousel-control.right img{
		margin-top:30px;
	}
}
{/literal}
</style>
<div class="container carouselbottom">
	<div class="row">
		<div class="">
    	    <div class="well1"> 
                
                    {if $is_ipad}
                    <div id="myCarouselPad" class="carousel slide visible-md visible-lg hidden-sm hidden-xs">
                        <ol class="carousel-indicators" style="left:initial;top:120px;width:initial;">
						{section name=indi start=0 loop=$links_pad_count}
						<li data-target="#myCarouselPad" data-slide-to="{$smarty.section.indi.index}" {if $smarty.section.indi.index eq 0} class="active"{/if}></li>
						{/section}
						</ol>
		                <!-- Carousel items -->
		                <div id="thumbnail-sm" class="carousel-inner">
		                {$links_pad}
		                </div><!--/carousel-inner-->
                    {else}
                    <div id="myCarousel" class="carousel slide visible-md visible-lg hidden-sm hidden-xs">
	                <ol class="carousel-indicators" style="top:120px;padding-left:30px;">
	                 	{section name=indi start=0 loop=$links_desktop_count}
					    <li data-target="#myCarousel" data-slide-to="{$smarty.section.indi.index}" {if $smarty.section.indi.index eq 0} class="active"{/if}></li>
					    {/section}
					</ol>
	                <!-- Carousel items -->
	                <div id="thumbnail-lg" class="carousel-inner">
	                {$links_desktop}
	                </div><!--/carousel-inner-->
	                {/if}
                </div><!--/myCarousel-->

                {* 平板 *}
                <div id="myCarouselPad" class="carousel slide visible-sm hidden-md hidden-lg hidden-xs">
					<ol class="carousel-indicators" style="left:initial;top:80px;width:initial;">
						{section name=indi start=0 loop=$links_pad_count}
					<li data-target="#myCarouselPad" data-slide-to="{$smarty.section.indi.index}" {if $smarty.section.indi.index eq 0} class="active"{/if}></li>
					{/section}
					</ol>
	                <!-- Carousel items -->
	                <div id="thumbnail-sm" class="carousel-inner">
	                {$links_pad}
	                </div><!--/carousel-inner-->
                </div><!--/myCarouselPad-->

                 {* 手機 *}
                <div id="myCarouselPhone" class="carousel slide hidden-md hidden-lg hidden-sm visible-xs">
					<ol class="carousel-indicators" style="top:55px;margin-right:0px;">
					{section name=indi start=0 loop=$links_phone_count}
					<li data-target="#myCarouselPhone" data-slide-to="{$smarty.section.indi.index}" {if $smarty.section.indi.index eq 0} class="active"{/if}></li>
					{/section}
					</ol>
	                <div id="thumbnail-xs" class="carousel-inner">
	                {$links_phone}
	                </div><!--/carousel-inner-->
                </div><!--/myCarouselPhone-->
            </div><!--/well-->   
		</div>
	</div>
</div>
<script>
{literal}
$(document).ready(function() {
    $('#myCarousel').carousel({
	    interval: 10000
	});
	$('#myCarouselPad').carousel({
	    interval: 10000
	})
	$('#myCarouselPhone').carousel({
	    interval: 10000
	})
});
{/literal}
</script>