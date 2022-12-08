<style>
{literal}

#container-statistics {
	background-color: #3b3b3b;
	color: #FFFFFF;
    width: 100%;
    border-bottom: 5px solid #05ab70;
}

#container-statistics .row {
	height: 150px;
}

#container-statistics .row div{
	height: 150px;
}

.statistics-text {
	text-align: center;
	vertical-align: middle;
	display:inline-block;
	color: #FFFFFF;
	background-color: #3b3b3b;
	font-size:18px;
	padding-top:34px;
}

.t48_green_bold {
    line-height: 60px;
}

.statistics-number {
	color: #FFFFFF;
	font-size:23px;
	text-weight:blod;
}
/*手機尺寸*/
@media (max-width: 767px) {

	#container-statistics .row {
		height: 64px;
	}

	#container-statistics .row div{
		height: 64px;
	}

	.statistics-text {
		font-size:14px;
		padding-top:10px;
	}

	.statistics-number {
		font-size:17px;
	}

    .t48_green_bold {
        font-size: 24px;
        line-height: 28px;
    }
}

/*平板直向、平板橫向*/
@media (min-width: 768px) and (max-width: 992px) {

	#container-statistics .row {
		height: 64px;
	}

	#container-statistics .row div{
		height: 64px;
	}
}
{/literal}
</style>
<div id="container-statistics" class="container-fluid">
    <div class="container">
        <div class="row visible-md visible-lg visible-sm hidden-xs">
            <div class="col-xs-6 col-md-3 statistics-text"><span class="t48_green_bold counter">{$statTimes.members}</span><BR /><span class="t14_w_bold">{'platform_number'|WM_Lang}</span></div>
            <div class="col-xs-6 col-md-2 statistics-text"><span class="t48_green_bold counter">{$statTimes.course}</span><BR /><span class="t14_w_bold">{'course_number'|WM_Lang}</span></div>
            <div class="col-xs-12 col-md-4 statistics-text"><span class="t48_green_bold counter">{$counter}</span><BR /><span class="t14_w_bold">{'accumulated_visits'|WM_Lang}</span></div>
            <div class="col-xs-6 col-md-3 statistics-text"><span class="t48_green_bold counter">{$statTimes.online}</span><BR /><span class="t14_w_bold">{'online_number'|WM_Lang}</span></div>
        </div>
        <div class="row hidden-md hidden-lg hidden-sm visible-xs">
            <div class="col-xs-6 col-md-3 statistics-text"><span class="t48_green_bold counter">{$statTimes.members}</span><BR /><span class="t14_w_bold">{'platform_number'|WM_Lang}</span></div>
            <div class="col-xs-6 col-md-2 statistics-text"><span class="t48_green_bold counter">{$statTimes.course}</span><BR /><span class="t14_w_bold">{'course_number'|WM_Lang}</span></div>
            <div class="col-xs-6 col-md-4 statistics-text"><span class="t48_green_bold counter">{$counter}</span><BR /><span class="t14_w_bold">{'accumulated_visits'|WM_Lang}</span></div>
            <div class="col-xs-6 col-md-3 statistics-text"><span class="t48_green_bold counter">{$statTimes.online}</span><BR /><span class="t14_w_bold">{'online_number'|WM_Lang}</span></div>
        </div>
    </div>
</div>
<script type="text/javascript" src="/lib/jquery/counterup/waypoints.min.js"></script>
<script type="text/javascript" src="/lib/jquery/counterup/jquery.counterup.min.js"></script>
<script>
{literal}
    jQuery(document).ready(function( $ ) {
        $('.counter').counterUp({
            delay: 10,
            time: 1000
        });
    });
{/literal}
</script>