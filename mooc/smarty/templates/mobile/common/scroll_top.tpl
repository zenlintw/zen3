<style>
{literal}
.scroll-top {
    display: none;
    position: fixed;
    bottom: 20%;
    right: 10px;
    z-index: 10;
    cursor: pointer;
}
.icon-scroll-top {
    height: 54px;
    width: 54px;
    background: transparent url('/public/images/mobile/icon_scroll_top.png') no-repeat;
    background-size: contain;
}
{/literal}
</style>
<!-- 置頂按鈕 -->
<div id="scroll-top" class="scroll-top" title="top">
    <div class="icon-scroll-top"></div>
</div>
<script>
{literal}
    $(document).ready(function() {
        // scroll list top
        $('#scroll-top').on('click', function() {
            console.log('top');
            $('html, body').animate({
                scrollTop: 0
            }, 'slow');
        });
        
        $(window).scroll(function() {
            if ( $(this).scrollTop() > 300){
                $('#scroll-top').fadeIn("fast");
            } else {
                $('#scroll-top').stop().fadeOut("fast");
            }
        });
    });
{/literal}
</script>