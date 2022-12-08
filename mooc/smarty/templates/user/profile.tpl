{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<link href="/public/css/common.css" rel="stylesheet" />
<link href="/public/css/layout.css" rel="stylesheet" />
<link href="/public/css/component.css" rel="stylesheet" />
{literal}
<script>
function setMargin(){
  if($('#contentGroupWrap').hasClass('hidden-xs')){
      $('#mainContentTableXS').css('margin-left','0px');
    }else{
      $('#mainContentTableXS').css('margin-left','233px');
    }
}
{/literal}
{literal}
function showUserMenu() {
  $('#contentGroupWrap').toggleClass('hidden-xs');
  setMargin();
}

</script>
<!--
<header id="header" class="visible-xs hidden-sm hidden-md hidden-lg" data-plugin-options="{&quot;stickyEnabled&quot;: true, &quot;stickyEnableOnBoxed&quot;: true, &quot;stickyEnableOnMobile&quot;: true, &quot;stickyStartAt&quot;: 10, &quot;stickySetTop&quot;: &quot;-10px&quot;}" style="min-height: 25px;">
  <div class="header-body" style="top: 0px;min-height: 50px;">
    <div class="header-container container" style="height:30px;min-width: 299px;widht:299px;">
      <div class="header-row">
        <div class="header-column" align="center">
        <button id="btnCourseTree" class="btn btn-green" type="button" onclick="showUserMenu();" style="width:90%;">個人選單</button>
        </div>
      </div>
    </div>
  </div>
</header>
-->
{/literal}
<div class="box-content explorer" id="mainContent" >
    {include file = "user/user_menu.tpl"}
    {include file = "user/personal.tpl"}
</div>
{include file = "common/site_footer.tpl"}