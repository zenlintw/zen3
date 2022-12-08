<link rel="stylesheet" type="text/css" href="{$appRoot}/lib/Elastislide/css/elastislide.css" />
<link rel="stylesheet" type="text/css" href="{$appRoot}/lib/Elastislide/css/custom.css" />
<style type="text/css">
    {if $theme === 'black'}
        .franchisee {ldelim}
            background-color:  #dcdcdc;
        {rdelim}
        .elastislide-wrapper {ldelim}
            background-color: #898989;
        {rdelim}
        .elastislide-horizontal nav span.elastislide-next {ldelim}
            background: url(../theme/default/learn_mooc/black_a_R.png) no-repeat;
        {rdelim}
        .elastislide-wrapper nav span {ldelim}
            background: url(../theme/default/learn_mooc/black_a_L.png) no-repeat;
        {rdelim}
    {elseif $theme === 'orange'}
        .franchisee {ldelim}
            background-color: #fbced2;
        {rdelim}
        .elastislide-wrapper {ldelim}
              background-color: #ff7f7e;
        {rdelim}
        .elastislide-horizontal nav span.elastislide-next {ldelim}
            background: url(../theme/default/learn_mooc/orange_a_R.png) no-repeat;
        {rdelim}
        .elastislide-wrapper nav span {ldelim}
            background: url(../theme/default/learn_mooc/orange_a_L.png) no-repeat;
        {rdelim}
    {elseif $theme === 'blue'}
        .franchisee {ldelim}
            background-color: #97fffb;
        {rdelim}
        .elastislide-wrapper {ldelim}
              background-color: #30c3bb;
        {rdelim}
        .elastislide-horizontal nav span.elastislide-next {ldelim}
            background: url(../theme/default/learn_mooc/blue_a_R.png) no-repeat;
        {rdelim}
        .elastislide-wrapper nav span {ldelim}
            background: url(../theme/default/learn_mooc/blue_a_L.png) no-repeat;
        {rdelim}
    {else}
        .franchisee {ldelim}
            background-color: #fff;
        {rdelim}
        .elastislide-wrapper {ldelim}
              background-color: #ff7f7e;
        {rdelim}
    {/if}
    .elastislide-wrapper {ldelim}
          padding: 14px 16px;
    {rdelim}
    .elastislide-horizontal nav span {ldelim}
        left: -8px;
    {rdelim}
    .elastislide-horizontal nav span.elastislide-next {ldelim}
        right: -9px;
    {rdelim}
</style>

<div id="franchisee" class='franchisee' style="height:130px;">
    <div class="brand-nav-tabs">
		<ul id="carousel" class="elastislide-list">
			{$brand}			
		</ul>
	</div>
</div>

<script src="{$appRoot}/lib/Elastislide/js/modernizr.custom.17475.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/Elastislide/js/jquerypp.custom.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/Elastislide/js/jquery.elastislide.js"></script>
<script type="text/javascript">
{literal}
//$(window).load(function() {
    $( '#carousel' ).elastislide();
//});
{/literal}
</script>