<link href="/public/css/common.css" rel="stylesheet" />
<link href="/public/css/forum.css" rel="stylesheet" />

<script type="text/javascript">
    {literal}
    /*Custom(B) VIP_68579 修正[IE]-最新消息右邊的捲顯示成2條問題*/
    $(function(){
        if(/msie/.test(navigator.userAgent.toLowerCase())){
            $('tr[data-bid]').click(function(){
                setTimeout(function(){
                    $('.fancybox-inner').css('overflow', 'hidden');
                }, 100);
            });
        }
    });
    /*Custom(E)*/
    {/literal}
</script>
<style type="text/css">
    {literal}
        #forumContainer {
            width: 100%;
            margin: auto;
            padding-top: 20px;
            padding-bottom: 20px;
            background-color: #FFFFFF;
        }

        .fancybox-inner{
            overflow:hidden;
        }
        .nav-forum-orange > li.active > a {
              background-color: #339F32;
              color: #FFFFFF;
        }
        .nav-forum-orange {
            color: #FFFFFF;
            border-color: #339F32;
        }
        .nav-forum-orange > li > a:hover {
            background-color: #339F32;
            color: #FFFFFF;
        }
        .nav-forum-orange > li > a:focus {
            background-color: #339F32;
            color: #FFFFFF;
        }
        .nav-forum-orange > li.active > a:hover {
            color: #FFFFFF;
            background-color: #339F32;
        }
        .nav-forum-orange > li.active > a:focus {
            color: #FFFFFF;
            background-color: #339F32;
        }

        #forum {
            text-align:center;
        }

        .box1 {
            padding: 0px;
        }

        .data8 {
            width: 570px;
            height: 263px;
        }

        .newsDataRow {
            vertical-align: middle;
            font-size: 15px;
            border-style: none none dashed none;
            border-color: #C9C9C9;
            border-bottom-width: 1px;
        }

        .news_title {
            white-space: nowrap; 
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .newsMoreDataRow {
            height: 70px;
        }

        .newsMoreDataRow > button {
            height: 47px;
            font-size: 18px !important;
        }

        .data9 {
            width: 241px;
            height: 263px;
        }

        /*手機尺寸*/
        @media (max-width: 767px) {
            #forum {
                width:100%;
            }

            .news_title {
                white-space: initial;
            }

            .data8 {
               min-width: 299px;
               width: 299px;
               height: 196px;
            }

            .data9 {
                display:none;
            }

            .newsDataRow {
                height: initial;
                line-height: initial;
                font-size: 13px;
            }

            .newsMoreDataRow {
                height: 47px;
            }

            .newsMoreDataRow > button {
                font-size:14px !important;
            }
        }

        /*平板直向、平板橫向*/
        @media (min-width: 768px) and (max-width: 992px) {
            #forum {
               width:940px;
            }

            .data8 {
               width: 666px;
            }

            .newsDataRow {
                height: 56px;
                font-size: 17.5px;
            }

            .newsMoreDataRow {
               height: 87px;
            }

            .newsMoreDataRow > button {
                height: 47px;
                margin-top:10px;
                font-size:21px !important;
            }
        }

		/*large Desktop*/
        @media (min-width: 1200px) {
		}
    {/literal}
</style>
<div id="forumContainer">
    <div class="container">
        <div class="row subtitle heading-bottom-border" style="margin-left: 15px;margin-right:15px;">
            <div class="col-md-7 col-xs-7" style="padding-left: 0px;"><h1>{'latestnews'|WM_Lang}</h1></div>
            <div class="t16_gary_right col-md-5 col-xs-5" style="text-align:right;padding-right: 0px;"><div id="newsmoreBtn" data-cid="{$schoolId}" data-bid="{$news.board_id}" data-nid="" data-sid="{$schoolId}{$schoolId}" data-reply="0" class="node-info" style="cursor: pointer;">{'more_news'|WM_Lang}<i class="fa fa-angle-double-right" aria-hidden="true" style="font-size:20px;margin-left:5px;margin-right:0.5em;"></i></div></div>
        </div>
        <div class="row" id="forum" style="margin: 0px;padding:0px;">
            {if $news_forumData.total_rows eq 0}
            <div class="col-md-12" style="padding-top: 5px;">
                <div class="title">{'no_article'|WM_Lang}</div>
            </div>
            {else}
            {foreach from=$news_forumData.data key=k item=v name=news_data}
            {* 最多呈現六筆 *}
            {if $smarty.foreach.news_data.index <= 5}
                <div class="col-md-6 col-xs-12" style="text-align: left">
                <div class="newsDataRow node-info" data-cid="{$v.cid}" data-bid="{$v.boardid}" data-nid="_{$v.node}" data-sid="{$v.s}" data-reply="{if $v.node|count_characters eq 18}1{else}0{/if}" style="cursor:pointer">
                    <div class="t14_green">{$v.postdate|date_format:"%Y-%m-%d"}</div>
                    <div class="news_title t16_b">{$v.subject}</div>
                </div>
                </div>
            {/if}
            {/foreach}
            {/if}
        </div>
        <div style="clear: both;  margin-bottom: 10px;"></div>
    </div>
</div>
<form name="node_list" method="POST" target="newsFrame" style="display: none;">
    <input type="hidden" name="token" value="{$newsReadToken}">
    <input type="hidden" name="cid">
    <input type="hidden" name="bid">
    <input type="hidden" name="nid">
</form>
<iframe id="newsFrame" name="newsFrame" src="about:blank" style="display: none;" title="呈現最新消息的框架" height="600" width="100%"></iframe>
<script type="text/javascript">
    var nowlang = '{$nowlang}',
        cid = '{$cid}',
        username = '{$profile.username}',
        hasForum = '{$hasForum}',
        hasNews = '{$hasNews}',
        hasCalendar = '{$hasCalendar}',
        msg = {$msg|@json_encode};
</script>
<script type="text/javascript" src="/public/js/forum/forum.js"></script>