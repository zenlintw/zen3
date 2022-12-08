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
        padding-left: 40px;
	}
	
	.exam_info {
	    text-align:right;
	    background-color: #E9E9E9;
	    height: 60px;
        line-height: 60px;
        font-size:22px;
        font-weight: bold;
        color:#333333;
        padding-right: 40px;
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
        padding:15px 25px;    
	}
	
	.title_num {
	    color:#ce2016;
	    font-weight:bold;
	}
	
	.back {
	    padding: 15px;
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
	    font-size: 1.67rem;
	    padding: 0 30px;
	    background: #ce2016;
	    height: 32px;
	    margin: 8px auto;
	    border-radius: 32px;
	    position: absolute;
	    right: 35px;
	    line-height: 32px;
	}
	
	.pen {
	    color: #fff;
        position: absolute;
        top: 8px;
        left: 20px;
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
        border-radius: 5px;
    }
    
    .attach {
        height:15rem;
        background-repeat:no-repeat;
        background-position: center center;
        background-size: contain;
    }

    .paper {
        border-radius: 4px;
        box-shadow: 0 15px 35px rgba(50, 50, 90, .1), 0 5px 15px rgba(0, 0, 0, .07);
        transition: .6s ease;
        height:100%;
        background: #fff;
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
<div id="BlockContainer" class="container">
    {*<div style="height:40px;">
	    <div class="row">
	        <div class="block-title-font col-md-12" style="">
	            &nbsp;<a href="/mooc/user/myteaching.php">{'mycourse'|WM_Lang}</a>&nbsp;>&nbsp;<a href="#" onclick="goManage('{$courseData.enc_course_id}');">{$courseData.caption|WM_Title}</a>&nbsp;>&nbsp;{$title}
	        </div>
	    </div>
    </div>*}

    <div id="back"> 
	    <div class="title">
	        <div class="col-xs-6">
	        <a href="#" style="color:#FFFFFF" onclick="goManage('{$courseData.enc_course_id}');"><div style="float:left;position:absolute;">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fas fa-arrow-left"></i>&nbsp;&nbsp;{'return'|WM_Lang}</div></a>
	        </div>
	        <div class="col-xs-6">
	        {if $status == 'start'}<a href="#" style="color:#FFFFFF" onclick="goEdit('{$exam_id}','{$type}');"><div class="edit_button"><i class="fas fa-pencil-alt pen" title="編輯"></i></div></a>{/if}
	        </div>
	    </div>
	    
	    <div id="main">
	        <div style="height:60px;">
	        <div class="col-xs-6 exam">{if $type == 'exam'}<img src="/theme/default/irs/icon_q1.svg" style="width:32px;height:32px;" />{else}<img src="/theme/default/irs/icon_quiz.png" style="width:32px;height:32px;" />{/if}&nbsp;{$title}</div>
	        <div class="col-xs-6 exam_info"><i class="fas fa-stop" style="color:#FFC000;font-size:20px;"></i>&nbsp;題數：{$items}{if $type == 'exam'}&nbsp;&nbsp;<i class="fas fa-stop" style="color:#FFC000;font-size:20px;"></i>&nbsp;總分：{$total_score}{/if}</div>
	        </div>
		    
	        <div id="content">
	        
		        <div id="stastic" class="items scrollbar-primary">
		            {assign var=num value=$item|@count}
					{foreach from=$item key=k item=v}

					    {if $k%2==0}<div class="row" {if $num%2==1 && $k+1==$num}{else}style="display:flex"{/if}>{/if}
					    <div id="item{$k+1}" class="col-md-6 col-xs-6" style="margin:10px auto;">
					        <div class="paper" style="font-size:30px">
					        {assign var=att_num value=0}
					        {if $v.type eq 2 || $v.type eq 3}
					            <div class="row back" style="">
						            <div class="type">
	                                    Q{$k+1} | {if $v.type eq 3}{'m_choice'|WM_Lang}{else}{'s_choice'|WM_Lang}{/if}
	                                </div>
						            <div class="col-md-12 question">
						                 {$v.text|strip_tags:true}{if $v.score!=0}({$v.score|floatval}分){/if}
						            </div>
                                    {if ($v.attaches|@count)>0}
							            {foreach from=$v.attaches key=a_k item=a_v}
									        {if ($a_v.href|pathinfo:$smarty.const.PATHINFO_EXTENSION|in_array:$img_arr)}
	                                            <div class="col-md-6 col-xs-6"><a href="{$a_v.href}" target="_blank"><div class="thumbnail attach" style="background-image:url('{$a_v.href}');"></div></a></div>
									        {/if}
									    {/foreach}
									    <div style="clear:both"></div>
									    <div style="font-size:20px;margin-left: 10px;">
										    {assign var=word value=0}
										    {foreach from=$v.attaches key=a_k item=a_v}
										        {if !($a_v.href|pathinfo:$smarty.const.PATHINFO_EXTENSION|in_array:$img_arr)}
										        {*{if $word == 0}<img src="/public/images/icon_file.png">{/if}*}
										        <a href="{$a_v.href}"><img src="/public/images/icon_file.png">{$a_v.filename}</a>&nbsp;&nbsp;
										        {assign var=word value=$word+1}
										        {/if}
										    {/foreach}
									    </div>
								    {/if}
								    
					            </div>
						        <div class="row">
						            {assign var=i value=0}
						            {foreach from=$v.optionals key=k1 item=v1}
						            {if $i eq 10}
						            {assign var=i value=0}
						            {/if}
						            <div id="{$v.item_id}_{$k1}" class="row" style="padding:10px 30px;">
							            <div class="col-md-12 select_font" style="display:block;">
							                <div class="item_s" style="float:left;width:5%;">{$k1+1} .</div><div class="item_m" style="float:left;width:95%;">{$v1.text}</div>
							            </div>
						            </div>
						            {if ($v1.attaches|@count)>0}
							            {foreach from=$v1.attaches key=a_k1 item=a_v1}
									        {if ($a_v1.href|pathinfo:$smarty.const.PATHINFO_EXTENSION|in_array:$img_arr)}
	                                            <div class="col-md-6 col-xs-6" style="margin-left: 75px;"><a href="{$a_v1.href}" target="_blank"><div class="thumbnail attach" style="background-image:url('{$a_v1.href}');"></div></a></div>
									        {else}
									            <div class="upload_file" style="font-size:20px;margin-left: 40px;"><img src="/public/images/icon_file.png"><a href="{$a_v1.href}">{$a_v1.filename}</a></div>
									        {/if}
									    {/foreach}								   
								    {/if}
						            {assign var=i value=$i+1}
						            {/foreach}
						        </div>
					        {elseif $v.type eq 1}
					            <div class="row back" style="">
						            <div class="type">
	                                    Q{$k+1} | {'correct'|WM_Lang}
	                                </div>
						            <div class="col-md-12 question">
						                 {$v.text|strip_tags:true}{if $v.score!=0}({$v.score|floatval}分){/if}
						            </div>
						            {if ($v.attaches|@count)>0}
							            {foreach from=$v.attaches key=a_k item=a_v}
									        {if ($a_v.href|pathinfo:$smarty.const.PATHINFO_EXTENSION|in_array:$img_arr)}
	                                            <div class="col-md-6 col-xs-6"><a href="{$a_v.href}" target="_blank"><div class="thumbnail attach" style="background-image:url('{$a_v.href}');"></div></a></div>
									        {/if}
									    {/foreach}
									    <div style="clear:both"></div>
									    <div style="font-size:20px;margin-left: 10px;">
										    {assign var=word value=0}
										    {foreach from=$v.attaches key=a_k item=a_v}
										        {if !($a_v.href|pathinfo:$smarty.const.PATHINFO_EXTENSION|in_array:$img_arr)}
										        {*{if $word == 0}<img src="/public/images/icon_file.png">{/if}*}
										        <a href="{$a_v.href}"><img src="/public/images/icon_file.png">{$a_v.filename}</a>&nbsp;&nbsp;
										        {assign var=word value=$word+1}
										        {/if}
										    {/foreach}
									    </div>
								    {/if}
					            </div>
						        <div class="row">
						            <div id="{$v.item_id}_0" class="row" style="padding:10px 30px;">
							            <div class="col-md-12 select_font" style="display:block;">
							                <div style="float:left;width:5%;">1 .</div><div style="float:left;width:95%;">{'yes'|WM_Lang}</div> 
							                <div style="clear:both"></div>                           
							            </div>
						            </div>
						            <div id="{$v.item_id}_1" class="row" style="padding:10px 30px;">
							            <div class="col-md-12 select_font" style="display:block;">
							                <div style="float:left;width:5%;">2 .</div><div style="float:left;width:95%;">{'no'|WM_Lang}</div>
							                <div style="clear:both"></div>                             
							            </div>
						            </div>
						        </div>
					        {elseif $v.type eq 5}
					            <div class="row back" style="">
						            <div class="type">
	                                    Q{$k+1} | {'short'|WM_Lang}
	                                </div>
						            <div class="col-md-12 question">
						                 {$v.text|strip_tags:true}{if $v.score!=0}({$v.score|floatval}分){/if}
						            </div>
						            {if ($v.attaches|@count)>0}
							            {foreach from=$v.attaches key=a_k item=a_v}
									        {if ($a_v.href|pathinfo:$smarty.const.PATHINFO_EXTENSION|in_array:$img_arr)}
	                                            <div class="col-md-6 col-xs-6"><a href="{$a_v.href}" target="_blank"><div class="thumbnail attach" style="background-image:url('{$a_v.href}');"></div></a></div>
									        {/if}
									    {/foreach}
									    <div style="clear:both"></div>
									    <div style="font-size:20px;margin-left: 10px;">
										    {assign var=word value=0}
										    {foreach from=$v.attaches key=a_k item=a_v}
										        {if !($a_v.href|pathinfo:$smarty.const.PATHINFO_EXTENSION|in_array:$img_arr)}
										        {*{if $word == 0}<img src="/public/images/icon_file.png">{/if}*}
										        <a href="{$a_v.href}"><img src="/public/images/icon_file.png">{$a_v.filename}</a>&nbsp;&nbsp;
										        {assign var=word value=$word+1}
										        {/if}
										    {/foreach}
									    </div>
								    {/if}
					            </div>
						        <div class="row" style="margin-top:20px;padding-left: 20px;">    
						            <div id="{$v.item_id}" style="padding:10px 30px;">
						
						            </div>
						        </div>    
					        {/if}
					        </div>
					    </div>
					    {if $k%2==1}</div><div style="clear:both"></div>{/if}
					{/foreach}
		        </div>

            </div>
	     </div>     
	   
    </div>
    
    
</div>

<form name="formGoManage" method="post" action="/mooc/user/activities.php">
<input type="hidden" name="course_id" value="" />
</form>

<form name="formGoEdit" method="post" action="/mooc/user/exam_edit.php">
<input type="hidden" name="course_id" value="{$courseData.course_id}" />
<input type="hidden" name="exam_id" value="" />
<input type="hidden" name="exam_type" value="" />
</form>

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
    });    
    
    $(window).resize(function() {
        adjust();
    });    
    
    function adjust() {
        var back = $(window).height()-40;

        $("#back").css({"height":back});
        var scol =  back-115;
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
        document.formGoEdit.exam_id.value = id;
        document.formGoEdit.exam_type.value = type;
        document.formGoEdit.submit();
    }

    

    
    {/literal}
</script>