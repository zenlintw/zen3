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
	
	.stastic {
	    text-align:center;
	    background-color: #E9E9E9;
	    height: 60px;
        line-height: 60px;
        font-size:22px;
        font-weight: bold;
        color:#FFFFFF;
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
	    background:#ffffff;
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
        padding:30px 0;    
	}
	
	.title_num {
	    color:#ce2016;
	    font-weight:bold;
	}
	
	.back {
	    background:#E9E9E9;
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
	
	body {
	    overflow:hidden;
        height:100%;
	}
	
	#p_submit,#p_nosubmit {
	    font-size: 48px;
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
    <div style="height:40px;">
	    <div class="row">
	        {*<div class="block-title-font col-md-12" style="">
	            &nbsp;<a href="/mooc/user/myteaching.php">{'mycourse'|WM_Lang}</a>&nbsp;>&nbsp;<a href="#" onclick="goManage('{$courseData.enc_course_id}');">{$courseData.caption|WM_Title}</a>&nbsp;>&nbsp;{$title}
	        </div>*}
	    </div>
    </div>

    <div id="back"> 
	    <div class="title"><a href="#" style="color:#FFFFFF" onclick="goManage('{$courseData.enc_course_id}');"><div style="float:left;position:absolute;">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fas fa-arrow-left"></i>&nbsp;&nbsp;{'return'|WM_Lang}</div></a><div>{$title}</div></div>
	    
	    <div id="main">
	        <div style="height:60px;"><a href="#" onclick="showStastic()";><div class="col-xs-6 stastic"><img src="/public/images/irs/btn-stastic.png" height="30" width="38" style="margin-top: -7px;" />&nbsp;&nbsp;{'title_stastic'|WM_Lang}</div></a><a href="#" onclick="showPeople()";><div class="col-xs-6 people_button"><img src="/public/images/irs/student_w_pad" height="38" width="38" style="margin-top: -7px;" />&nbsp;&nbsp;{'title_people'|WM_Lang}</div></a></div>
		    <div class="row">
		    {if $type=='exam'}
		    <div id="result" display:none>
		        <div class="col-md-3">提交人數&nbsp;&nbsp;<span class="num_font">{$score_data.submit}</span>&nbsp;&nbsp;人</div>
		        <div class="col-md-3">最高分數&nbsp;&nbsp;<span class="num_font">{$score_data.max|intval}</span>&nbsp;&nbsp;分</div>
		        <div class="col-md-3">平均分數&nbsp;&nbsp;<span class="num_font">{$score_data.avg|intval}</span>&nbsp;&nbsp;分</div>
		        <div class="col-md-3">最低分數&nbsp;&nbsp;<span class="num_font">{$score_data.min|intval}</span>&nbsp;&nbsp;分</div>
	        </div>
	        {else}
	        <div id="result" display:none>
		        <div class="col-md-6">提交人數&nbsp;&nbsp;<span class="num_font">{$score_data.submit}</span>&nbsp;&nbsp;人</div>
		        <div class="col-md-6">問題數量&nbsp;&nbsp;<span class="num_font">{$items}</span>&nbsp;&nbsp;題</div>
	        </div>
	        {/if}
	        </div>
	        <div id="content">
	        
		        <div id="stastic">
		            {include file = "irs/exam_stastic.tpl"}
		        </div>
		        <div id="people" class="scrollbar-primary">
		            {include file = "irs/exam_people.tpl"}
		        </div>
        
            </div>
	     </div>     
	   
    </div>
    
    
</div>

<form name="formGoManage" method="post" action="/mooc/user/activities.php">
<input type="hidden" name="course_id" value="" />
</form>
<script type="text/javascript">
    var course_id = '{$courseData.course_id}';
    var exam_id = '{$exam_id}';
    var exam_type = '{$type}';
    var items= {$items};
    var now_item = 1;
    var forGuest = '{$forGuest}';
    
    {literal}

    $(document).ready(function() {
        showStastic();
    });    
    
    $(window).resize(function() {
        adjust();
    });    
    
    function adjust() {
        var show = $("#result").css('display');
        if (show == 'none') {
            var back = $(window).height()-80;
        } else {
            var back = $(window).height()-172;
        }
        
        $("#back").css({"height":back});
        var scol =  back-97;
        $("#content").css({"height":scol});
        
        if (show == 'none') {
            $("#people").css({"height":scol});
        } else {
            var qq = $("#item"+now_item+" .back").outerHeight();
            var TEST = scol-qq;
            $(".items").css({"height":TEST,"margin-top":"0"});
        }
    }

    function next(now) {
        var next = now+1;        
        if (next<=items) {
            $('#item'+now).addClass('hidden');
            $('#item'+next).removeClass('hidden');
            $(window).scrollTop(0);
        }
        now_item = next;
        adjust();
    }

    function prev(now) {
        var prev = now-1;
        if (prev!=0) {
            $('#item'+now).addClass('hidden');
            $('#item'+prev).removeClass('hidden');
            $(window).scrollTop(0);
        }
        now_item = prev;
        adjust();
    }
    
    function goManage(course_id) {
        document.formGoManage.course_id.value = course_id;
        document.formGoManage.submit();
    }
    
    function edit(course_id) {
        document.formModify.cid.value = course_id;
        caption = $("#"+course_id).html();
        document.formModify.course_name.value = caption;
    }
    
    function modify()
	{
	    close_fancy();
	    var csid = document.formModify.cid.value;
        var name = document.formModify.course_name.value;
	    $.ajax({
            'url': '/mooc/user/modify.php',
            'type': 'POST',
            'data': {'action': 'modify','cid':csid,'course_name':name},
            'dataType': "json",
            'success': function (res) {
                if(res.code==1) {
                    $("#"+csid).html(name);
                }
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
        		
	}
	
	function del(course_id)
	{
	    document.formDel.cid.value = course_id;
	}
	
	function delCourse()
	{
	    close_fancy();
	    var csid = document.formDel.cid.value;
	    $.ajax({
            'url': '/mooc/user/modify.php',
            'type': 'POST',
            'data': {'action': 'delete','cid':csid},
            'dataType': "json",
            'success': function (res) {
                if(res.code==1) {
                    location.reload();
                }
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
        		
	}
	
	function add()
	{
	    close_fancy();
	    var name = document.formAdd.course_name.value;
	    $.ajax({
            'url': '/mooc/user/modify.php',
            'type': 'POST',
            'data': {'action': 'add','course_name':name},
            'dataType': "json",
            'success': function (res) {
                if(res.code==1) {
                    location.reload();
                }
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
        		
	}
    
    function close_fancy() {
        $.fancybox.close();
    }
    
    function doSearch(){
        document.getElementById("searchForm").submit();  
    }
    
    function showPeople() {
        getPeople();
        $('#stastic').hide();
        $('#people').show();
        
        $('.people_button').css({"background-color":"#7ED8BE","color":"#FFFFFF"});
        $('.stastic').css({"background-color":"#FFFFFF","color":"#767171"});
        $('#p_list').css({"margin-top":"0",});
        $('.stastic > img').attr('src','/public/images/irs/ic-stastic.svg');
        $('.people_button > img').attr('src','/public/images/irs/student_w_pad.png');
        $("#result").hide();
        adjust();
    }
    
    function showStastic() {
        get_result();
        $('#people').hide();
        $('#stastic').show();
        for (var i = 1; i <= items; i++) {
            $('#item'+i).removeClass('show');
            $('#item'+i).addClass('hidden');
        }
        $('#item1').removeClass('hidden');
        $('#item1').addClass('show');
        
        $('.item_s').css({"width":"5%"});
        $('.item_m').css({"width":"95%"});
        
        $('.prev').attr('src', '/public/images/irs/ic_pre_hover.png');
        $('.next').attr('src', '/public/images/irs/ic_next_hover.png');
        $('.fa-arrow-circle-left').css({"color":"#333333"}); 
        $('.fa-arrow-circle-right').css({"color":"#333333"}); 
        
        $('.people_button').css({"background-color":"#FFFFFF","color":"#767171"});
        $('.stastic').css({"background-color":"#7ED8BE","color":"#FFFFFF"});
        $('.stastic > img').attr('src','/public/images/irs/btn-stastic.png');
        $('.people_button > img').attr('src','/public/images/irs/student_g.png');
        $("#result").show();
        now_item = 1;
        adjust();
    }
    
    
    function getPeople() {

        $.ajax({
            'url': '/mooc/irs/irs_status.php',
            'type': 'POST',
            'data': {'action': 'get_people','course_id':course_id,'exam_id':exam_id,'qti_type':exam_type,'forGuest':forGuest},
            'dataType': "json",
            'success': function (res) {
                $('#p_submit').html(res.submit);
                $('#p_nosubmit').html(res.nosubmit); 
                $('#p_list').html(res.html);
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
    
    }
    
    function get_result() {

        $.ajax({
            'url': '/mooc/irs/irs_status.php',
            'type': 'POST',
            'data': {'action': 'get_result','course_id':course_id,'exam_id':exam_id,'qti_type':exam_type,'forGuest':forGuest},
            'dataType': "json",
            'success': function (res) {
                var items = res.stastic;
                if (items) {
	                Object.keys(items).forEach(function(key){
	                    for (var i = 0, len = items[key].length; i < len; i++) {
	                        $('#'+key+'_'+i+' .people_num').html(items[key][i]);
	                        
	                        var total = res.total[key];
	                        if (total>0) {
	                            var rate = Math.round(items[key][i]/total*100,1);
	                        } else {
	                            var rate = 0;
	                        }
	                        $('#'+key+'_'+i+' .progress-bar').css({"width":rate+"%"});
	                        $('#'+key+'_'+i+' .rate').html(rate+"&nbsp;&nbsp;%");
	                        
	                        
	                        
	                        var correct = res.correct[key];
                            if (correct) {
		                        if (correct.indexOf(i+1)!=-1) {
		                            $('#'+key+'_'+i+' .correct').html('<img src="/public/images/irs/correct.png" style="width:50px">');
		                            $('#'+key+'_'+i).css({"background":"rgba(125,216,189,0.1)"});
		                        } else if (correct == 'O') {
		                            $('#'+key+'_0 .correct').html('<img src="/public/images/irs/correct.png" style="width:50px">');
		                            $('#'+key+'_0').css({"background":"rgba(125,216,189,0.1)"});
		                        } else if (correct == 'X') {
		                            $('#'+key+'_1 .correct').html('<img src="/public/images/irs/correct.png" style="width:50px">');
		                            $('#'+key+'_1').css({"background":"rgba(125,216,189,0.1)"});
		                        }
	                        }
	                        
	                        
	                        
	                    }
	                });
                }
                
                var ans = res.ans;
                if (ans) {
	                Object.keys(ans).forEach(function(key){
	                    var txt = '';
	                    Object.keys(ans[key]).forEach(function(key1){
	                        ans[key][key1][0] = ans[key][key1][0].replace(/\r\n/g, "<br>");
	                        txt += '<div class="row" style="margin-top:20px;">';
                            txt += '<div class="col-md-1 img-circle people_small" style="background-color:'+ans[key][key1][3]+'">'+ans[key][key1][2]+'</div>';
                            txt += '<div class="col-md-11"><span style="color:'+ans[key][key1][3]+'">'+ans[key][key1][1]+'</span><br><span style="font-size:22px;">'+ans[key][key1][0]+'</span></div>';
	                        txt += '</div>';
	                        
	                    });
	
	                    $('#'+key+'').html(txt);
	
	                });
                }

            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
    
    }
    
    {/literal}
</script>