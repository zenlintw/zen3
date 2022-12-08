<style type="text/css">
{if $theme === 'black'}
    .company {ldelim}
        background-color: #abaaaa;
    {rdelim}
{elseif $theme === 'orange'}
    .company {ldelim}
        background-color: #ffcc00;
    {rdelim}
{elseif $theme === 'blue'}
    .company {ldelim}
        background-color: #5ad8d4;
    {rdelim}
{else}
    .company {ldelim}
        background-color: #abaaaa;
    {rdelim}
{/if}
</style>

<div id="ccompany" class="company" style="height:120px;">
    {*<div class="company-nav-tabs">
        <div class="company-img" style="background-color:#fff;width:220px; text-align: center;">
            <img src="{$appRoot}{$company.pic_path}" onerror="javascript:this.src='/theme/default/learn_mooc/default_contenter.png'">
        </div>
        <div class="company-content">
            <div style="border-left: 1px solid #9a9a9a;margin: 20px 0 0 25px;padding-left:25px;min-width:150px;background-color: #048E89;"><span>{$company.school_name}</span>
                <br>
                <img src="{$appRoot}/theme/default/learn_mooc/star.png">
                <img src="{$appRoot}/theme/default/learn_mooc/star.png">
                <img src="{$appRoot}/theme/default/learn_mooc/star.png">
                <img src="{$appRoot}/theme/default/learn_mooc/star.png">
                <img src="{$appRoot}/theme/default/learn_mooc/star.png">
            </div>   
        </div>
    </div>*}
    <div style="height: 100px;width:950px;margin: 0 auto;padding-top: 20px;">
        <div style="background-color: #fff;width: 190px;float: left;position: relative;height: 100px;">
            <img src="{$appRoot}{$company.pic_path}" onerror="javascript:this.src='/theme/default/learn_mooc/default_contenter.png'" border="0" style="height:100px;width:190px;">
        </div>
        <div style="background-color:#fff; float: left">
            <div style="background-color: #048e89;width: 760px;height: 30px;">
                <div style="position:relative;float:left;">
                    <div style="color:#fff;padding: 5px 22px;font-size: 1.17em;">{$company.school_name}</div>
                </div>
                <div style="position:relative;float:right;margin: 4px 10px;width: 130px;">
                    <img src="{$appRoot}/theme/default/learn_mooc/star.png" style="margin-right: 5px;">
                    <img src="{$appRoot}/theme/default/learn_mooc/star.png" style="margin-right: 5px;">
                    <img src="{$appRoot}/theme/default/learn_mooc/star.png" style="margin-right: 5px;">
                    <img src="{$appRoot}/theme/default/learn_mooc/star.png" style="margin-right: 5px;">
                    <img src="{$appRoot}/theme/default/learn_mooc/star.png" style="margin-right: 5px;">
                </div>
            </div>
                <div style="background-color: #dcdcdc;width: 760px;height: 70px;">
                    <div style="color:#5E5E5E;font-style: normal;font-size: 12px;padding: 5px 22px;">
                       {$bannerT3}
                    </div>
                </div>
        </div>
    </div>
</div>