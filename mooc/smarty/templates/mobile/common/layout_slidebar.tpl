<link href="{$appRoot}/public/css/third_party/jquery-slidebars-0.10.3/slidebars.min.css" rel="stylesheet" />
<style>
{literal}
/* slidebars(B) */
#sb-site {
    height: 100%;
    height: auto !important;
    background-color: #ECECEC;
    overflow-y: hidden;
    transform: initial;
}
.sb-slidebar {
    background-color: #FFCC00;
    box-shadow: inset 7px 0px 10px #DFA804;    
}
.sb-slidebar .list-group {
    border-bottom: 1px solid #FFE65C;
}
.sb-slidebar .list-group .list-group-item {
    border-radius: 0;
    text-align: center;
    background-color: transparent;
    color: #646464;
    border-bottom: 1px solid #DD9D00;
    border-top: 1px solid #FFE65C;
    margin-bottom: 0;
    border-width: 1px 0;
}
.sb-slidebar .list-group .list-group-title {
    margin-top: -1px;
    background-color: #646464;
    color: #FFCC00;
}
.list-group-item:first-child, .list-group-item:last-child  {
    border-radius: 0;
}

.sb-width-fix {
    width: 200px;
}
/* slidebars(E) */
{/literal}
</style>
<!-- 側邊欄 -->
{* 使用時，主畫面需用 <div id="sb-site"></div> 框起 *}
<div id="sb-site">
    {include file = "$contentTpl"}
</div>
<div class="sb-slidebar sb-right sb-width-fix">
    <!-- Your left Slidebar content. -->
    <ul class="list-group">
        <li class="list-group-item list-group-title">{'sidebar_menu'|WM_Lang}</li>
        <a href="javascript:;" onclick="goPcWeb();"><li class="list-group-item">{'sidebar_pc_edition'|WM_Lang}</li></a>
        {if $profile.username === 'guest'}
            <a href="{$appRoot}/mooc/login.php"><li class="list-group-item">{'sidebar_sign_in'|WM_Lang}</li></a>
            {if 'FB'|in_array:$canReg || 'Y'|in_array:$canReg}
               <a href="{$appRoot}/mooc/register.php"> <li class="list-group-item">{'sidebar_register'|WM_Lang}</li></a>
            {/if}
            <a href="{$appRoot}/mooc/forget.php"><li class="list-group-item">{'sidebar_forgot'|WM_Lang}</li></a>
        {else}
            <a href="{$appRoot}/logout.php"><li class="list-group-item">{'sidebar_logout'|WM_Lang}</li></a>
        {/if}
    </ul>
</div>
    
{include file = "mobile/common/scroll_top.tpl"}
{include file = "common/tiny_footer.tpl"}
<script type="text/javascript" src="{$appRoot}/public/js/third_party/jquery-slidebars-0.10.3/slidebars.min.js"></script>
<script>
{literal}
    $(document).ready(new function() {
        $.slidebars();
    });
    function goPcWeb() {
        $.cookie("pcweb", 1, { expires: 1, path:'/', domain: generalDomain });
        location.reload();
    }
{/literal}
</script>