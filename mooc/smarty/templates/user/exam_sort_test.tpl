{include file = "common/tiny_header.tpl"}
<style type="text/css">
{literal}

    .input-search {
        background-image: linear-gradient(-180deg, #F2F2F2 0%, #F9F9F9 100%);
        border: 0 solid #FFFFFF;
        box-shadow: inset 0 1px 2px 0 #989898;
        border-radius: 6px;

    }
    
    .over-box {
        width:500px;
        height:230px;
        display:none;
        font-size:20px;
    } 

    .item-box {
        width: 65%;
        margin: 10px auto;
        background: rgba(255,255,255,0.5);
        border-radius: 10px;
        border: 1px solid #dddddd;
        cursor: ns-resize;    
    }

    .tip {
        width: 60%;
        margin: auto;
        font-size: 20px;
    }
    
    .icon_warn {
        background-image:url('/public/images/irs/ic-warning.png');
        background-repeat:no-repeat;
        width:95px;height:95px;
        background-position: center center;
        margin:28px auto;
    }
    
    .fancybox-inner {
        background: #FFFFFF;
    }
    
    .true_button {
        color:#FFFFFF;
        background:#E8483F;
        margin-top:20px;
        margin-bottom:28px;
        margin-left:117px;
        border-radius: 4px;
        width:128px;
        height:38px;
        line-height:38px;
        float:left;
        text-align:center;
    }
    
    .add_button {
        color:#FFFFFF;
        background:#E8483F;
        border-radius: 30px;
        width:128px;
        height:38px;
        line-height:38px;
        text-align:center;
    }
    
    .false_button {
        color:#FFFFFF;
        background:#B5B5B5;
        margin-top:20px;
        margin-bottom:28px;
        margin-left:10px;
        border-radius: 4px;
        width:128px;
        height:38px;
        line-height:38px;
        float:left;
        text-align:center;
    }
    
    /*手機尺寸*/
    @media (max-width: 767px) {
        .item-box {
            width: 90%;
            margin:0;
            margin-bottom: 10px;
        }
        .tip {
            width: 75%;
        }
    }

    /*平板直向、平板橫向*/
    @media (min-width: 768px) and (max-width: 992px) {
    }

    /*large Desktop*/
    @media (min-width: 1200px) {
    }
    
    /* unvisited link */
    a:link {
        color: #333333;
    }
    
    /* visited link */
    a:visited {
        color: #333333;
    }
    
    /* mouse over link */
    a:hover {
        color: #333333;
    }
    
    /* selected link */
    a:active {
        color: #333333;
    }
    
    .title {
        background-color: #455868;
        text-align:center;
        color:#FFFFFF;
        border-radius: 10px 10px 0px 0px;
        height:48px;
        line-height:48px;
        font-size: 22px;
        font-weight: bold;

    }
    
    .exam {
        text-align:left;
        background-color: #E9E9E9;
        height: 60px;
        line-height: 60px;
        font-size:22px;
        font-weight: bold;
        color:#333333;
        padding-left: 55px;
    }
    
    .exam_info {
        text-align:right;
        background-color: #E9E9E9;
        height: 60px;
        line-height: 60px;
        font-size:22px;
        font-weight: bold;
        color:#333333;
        padding-right: 55px;
    }
    
    .people_button {
        text-align:center;
        background-color: #7ED8BE;
        height: 60px;
        line-height: 60px;
        font-size:22px;
        font-weight: bold;
        color:#FFFFFF;
    }
    
    .people {
        line-height:150px;
        font-size:26px;
        text-align:center;
        width:150px;
        height:150px;
        margin:20px 20px;
        background-color:#767171;
        overflow : hidden;
        text-overflow : ellipsis;
        white-space : nowrap;
        color:#FFFFFF;
        font-weight: bold;
    }

    #people { 
        line-height:40px;
        padding: 20px;
        height: 600px;
        overflow-y: auto;
        overflow-x: hidden;
        width: 100%;
    }
    
    #stastic {
        line-height:30px;
        
    }
    
    .select_font {
        font-size:20px;
        font-weight:bold;
    }

    .people_small {
        line-height:80px;
        font-size:50px;
        text-align:center;
        width:80px;
        height:80px;
        background-color:#767171;
    }
    
    .progress {
        margin: 10px auto;
        overflow: hidden;
        background-color: #f5f5f5;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
        box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
        height:20px;
        background:#afabaa;
    }
    
    .question {
        line-height:30px;
        font-size:24px;
        font-weight:Bold;
        padding:15px;
        word-break: break-all;
    }
    
    .tool {
        padding:10px;
    }
    
    .row {
        margin-left:0;
        margin-right:0;
    }
    
    .scrollbar-primary::-webkit-scrollbar {
        width: 8px;
        background: rgba(255,255,255,0.60);
        border-radius: 15px; 
    }
    
    .scrollbar-primary::-webkit-scrollbar-thumb {
        border-radius: 10px;
        -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.1);
        
        background: #afabaa;
         
    }

    #back {
        background: rgba(255,255,255,0.80);
        border-radius: 15px;
    }
    
    #main {
        /*margin:10px 20px;*/
    }
    
    #content {
        color:#333333;
        /*height: 600px;
        overflow-y: auto;
        overflow-x: hidden;
        width: 100%;*/
    }
    
    .items {
        overflow-y: auto;
        overflow-x: hidden;
        width: 100%;
        /*padding:30px 0;*/    
    }
    
    .title_num {
        color:#ce2016;
        font-weight:bold;
    }
        
    .correct {
        text-align: right;
        margin: auto 0;
        height:100%;
    }
    
    #result {
        height:80px;
        line-height:80px;
        font-weight:bold;
        font-size:20px;
        color:#333333;
        /*border-bottom: 2px solid #cdcdcd;*/
    }
    
    #result>div {
        text-align: center;
    }
    
    .num_font {
        font-size:48px;
    }
    
    .title_num {
        text-align:right;
        padding: 15px;
    }
    
    .edit_button {
        width: 70px;
        background: #ce2016;
        height: 40px;
        margin: 4px auto;
        border-radius: 25px;
        position: absolute;
        right: 30px;
    }
    
    .pen {
        color: #000000;
        position: absolute;
        top: 10px;
        left: 25px;
    }
    
    body {
        overflow:hidden;
        height:100%;
    }
    
    #p_submit,#p_nosubmit {
        font-size: 48px;
    }
    
    .type {
        background:#E8483F;
        width:9.83rem;
        height:3.75rem;
        font-size: 1.67rem;
        color: #FFFFFF;
        line-height:3.75rem;
        text-align:center;
    }
    
    .attach {
        height:15rem;
        background-repeat:no-repeat;
        background-position: center center;
        background-size: contain;
    }
    
    ul { 
        list-style-type: none;
    }

    @media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {
        .number {
            height:100%;
        }
        
        .tool {
            height:100%;
        }
    }
    
{/literal}
</style>


    <div id="back"> 
        <form method="post" action="" name="sortForm" id="sortForm" style="margin-bottom: 0px;">
        <input type="hidden" name="exam_id"  value="{$exam_id}">
        <input type="hidden" name="exam_type"  value="{$type}">
        <div id="main">

            <div id="content">
            
                <div id="stastic" class="items scrollbar-primary">
                    <div class="tip">可拖曳調整順序</div>
                    <ul id="sortable">
                    {assign var=num value=$item|@count}
                    {foreach from=$item key=k item=v}
                        <li><div id="item{$k+1}" class="item-box">
                            <input type="hidden" name="item_id[]" value="{$v.item_id}">
                            <input type="hidden" name="item_score[{$v.item_id}]" value="{$v.score}">
                            <div style="font-size:30px">

                                <div class="row back" style="">
                                    <div class="col-md-2" style="padding:15px;">
                                        <div class="type">
                                            <span class="item_num">Q{$k+1}</span> |
                                            {if $v.type eq 3}
                                                {'m_choice'|WM_Lang}
                                            {elseif $v.type eq 2}
                                                {'s_choice'|WM_Lang}
                                            {elseif $v.type eq 1}
                                                {'correct'|WM_Lang}
                                            {else}
                                                {'short'|WM_Lang}
                                            {/if}
                                        </div>
                                    </div>
                                    <div class="col-md-10 question">
                                         {$v.text|strip_tags:true}
                                    </div>
                                </div>

                            </div>
                        </div></li>
                    {/foreach}
                    </ul>
                </div>

            </div>
         </div>     
       
    </div>
    
    


<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript">
    var course_id = '{$courseData.course_id}';
    var exam_id = '{$exam_id}';
    var exam_type = '{$type}';
    var items= {$items};
    var now_item = 1;
    
    {literal}

    $(document).ready(function() {
        // showStastic();
        adjust();
        
        $( "#sortable" ).sortable({
            revert: true,
            sort: function(event, ui) {
                $(ui.item).find(".item-box").css({'border':'2px solid #E8483F'});
            },
            stop: function(evt, ui) {
                $(ui.item).find(".item-box").css({'border':'1px solid #dddddd'});
                $('.item_num').each(function (index, val) {
                    var num = index+1;
                    $(this).html('Q'+num);
                }); 
            }
        });
    
        $( "ul, li" ).disableSelection();

    });    
    
    $(window).resize(function() {
        adjust();
    });    
    
    function adjust() {
        var back = $(window).height();

        $("#back").css({"height":back});
        var scol =  back-55;
        $("#content").css({"height":scol});
        $("#stastic").css({"height":scol});

    }
    
    function goManage(course_id) {
        document.formGoManage.course_id.value = course_id;
        document.formGoManage.submit();
    }

    function close_fancy() {
        $.fancybox.close();
    }
    
    function goEdit(id,type) {
    
        $.ajax({
            'url': '/mooc/user/sort_save.php',
            'type': 'POST',
            'data': $('#sortForm').serialize(),
            'dataType': "json",
            'success': function (res) {
                if(res.code==1) {
                    document.formGoEdit.exam_id.value = id;
                    document.formGoEdit.exam_type.value = type;
                    document.formGoEdit.submit();
                }
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
    
        
    }

    

    
    {/literal}
</script>