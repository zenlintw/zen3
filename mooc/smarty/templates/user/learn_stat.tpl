{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<style type="text/css">
{literal}
    .block-title-font {
        font-size: 24px;
        color: #626262;
        font-weight: bold;
    }

    .block-input-title {
        font-size: 18px;
        line-height: 32px;
        color: #393939;
    }

    .block-fontQ {
        font-size: 16px;
        color: #000000;
        font-weight: bold;
    }

    .block-fontA {
        font-size: 14px;
        color: #393939;
        font-weight: bold;
    }

    #BlockSearch {
        background-color: #F5F5F5;
        padding:15px;
        margin-bottom: 15px;
    }

    #BlockContainer {
        min-height: 550px;
    }

    div.table-responsive > table > thead > tr > th{
        height: 50px;
        line-height: 32px;
        background-color: #2F3B8B;
        color: #FFFFFF;
        font-size: 16px;
        font-weight: bold;
    }

    /*手機尺寸*/
    @media (max-width: 767px) {
        .block-input-title {
            font-size: 16px;
            line-height: 24px;
        }

        #BlockSearch {
            padding-left: 0px;
            padding-right: 0px;
        }

        table.table-responsive > tbody > tr > th {
            height: 50px;
            line-height: 32px;
            background-color: #2F3B8B;
            color: #FFFFFF;
            font-size: 16px;
            font-weight: bold;
        }

        table.table-responsive > tbody > tr > td {
            font-size: 16px;
        }
    }

    /*平板直向、平板橫向*/
    @media (min-width: 768px) and (max-width: 992px) {
    }

    /*large Desktop*/
    @media (min-width: 1200px) {
    }
{/literal}
</style>
<div style="background-color:#FFFFFF;height:30px;">&nbsp;</div>

<div id="BlockContainer" class="container">
    <div class="row">
        <div class="block-title-font col-md-12" style=""><i class="fa fa-database" aria-hidden="true" style="font-size:26px;margin-left:0.5em;margin-right:0.5em;"></i>學習記錄</div>
    </div>
    <div class="row">
        <div class="col-md-12" style="padding-top: 5px;padding-bottom: 20px;">
            <div style="background-color:#F18E1E;height:3px;">&nbsp;</div>
        </div>
    </div>
    <div id="BlockSearch" class="container">
        <div class="row" style="">
            <div class="col-md-6 block-input-title"><i class="fa fa-play arrow" aria-hidden="true" style="font-size:12px;color:#F18E1E;margin-left:0.5em;margin-right:0.5em;"></i>{$profile.realname}{$loginInfo.count}</div>
            <div class="col-md-6 block-input-title"><i class="fa fa-play arrow" aria-hidden="true" style="font-size:12px;color:#F18E1E;margin-left:0.5em;margin-right:0.5em;"></i>{$loginInfo.last}</div>
            <div class="col-md-6 block-input-title"><i class="fa fa-play arrow" aria-hidden="true" style="font-size:12px;color:#F18E1E;margin-left:0.5em;margin-right:0.5em;"></i>{$loginInfo.from}</div>
            <div class="col-md-6 block-input-title"><i class="fa fa-play arrow" aria-hidden="true" style="font-size:12px;color:#F18E1E;margin-left:0.5em;margin-right:0.5em;"></i>{$loginInfo.sum}</div>
        </div>
    </div>

    <div class="table-responsive hidden-xs">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>課程名稱</th>
                <th>上課次數</th>
                <th>張貼篇數</th>
                <th>討論次數</th>
                <th>最後上課時間</th>
                <th>閱讀時數</th>
            </tr>
        </thead>
        <tbody>
            {if $datalist|@count eq 0}
                <td colspan="6" style="text-align: center;">您尚未選修任何課程</td>
            {/if}
            {foreach from=$datalist key=k item=v}
                <tr>
                    <td>{$v.caption}</td>
                    <td>{$v.login_times}</td>
                    <td>{$v.post_times}</td>
                    <td>{$v.dsc_times}</td>
                    <td>{$v.last_login}</td>
                    <td>{$v.rss}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
    </div>

    <div class="visible-xs">
        {if $datalist|@count eq 0}
            <div class="col-xs-12">您尚未選修任何課程</div>
        {else}
            {foreach from=$datalist key=k item=v}
                <table class="table table-responsive">
                    <tr><th colspan="2">{$v.caption}</th></tr>
                    <tr><td>上課次數</td><td>{$v.login_times}</td></tr>
                    <tr><td>張貼篇數</td><td>{$v.post_times}</td></tr>
                    <tr><td>討論次數</td><td>{$v.dsc_times}</td></tr>
                    <tr><td>最後上課時間</td><td>{$v.last_login}</td></tr>
                    <tr><td>閱讀時數</td><td>{$v.rss}</td></tr>
                </table>
                <P />
            {/foreach}
        {/if}
    </div>
</div>
<form name="exportFm" method="POST" action="/learn/co_learn_stat_exportCSV.php" target="_blank">
<input type="hidden" name="csrfToken" value="{$csrfToken}" />
</form>
{include file = "common/site_footer.tpl"}
<script type="text/javascript">
    {literal}
    {/literal}
</script>