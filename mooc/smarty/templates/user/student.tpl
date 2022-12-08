{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}

<style type="text/css">
{literal}
    .table-fixed thead {
        width: 100%;
    }
    .table-fixed tbody {
        height: 600px;
        overflow-y: auto;
        width: 100%;
    }
    .table-fixed thead, .table-fixed tbody, .table-fixed tr, .table-fixed td, .table-fixed th {
        display: block;
    }
    .table-fixed tbody td, .table-fixed thead > tr> th {
        float: left;
        border-bottom-width: 0;
    }
    
    .input-search {
        /*background-image: linear-gradient(-180deg, #F2F2F2 0%, #F9F9F9 100%);*/
        border: 0 solid #FFFFFF;
        box-shadow: inset 0 1px 2px 0 #989898;
        border-radius: 6px 0px 0px 6px;
    }
    
    .go {
        background: #9AC83D;
        border-radius: 30px;
        text-align:center;
        font-size: 15px;
        width:113px;
        height:34px;
        line-height:34px;
        color:#FFFFFF;
        margin: 0 auto;
    }
    
    .active {
        background: #FB5442;
        border-radius: 30px;
        text-align:center;
        font-size: 15px;
        width:113px;
        height:34px;
        line-height:34px;
        color:#FFFFFF;
        margin: 0 auto;
    }
    
    .over {
        background: #7DD8BD;
        border-radius: 6px;
        text-align:center;
        font-size: 15px;
        width:113px;
        height:34px;
        line-height:34px;
        color:#FFFFFF;
        margin: 0 auto;
    }
    
    .add_q {
        background: #A1CC37;
        border-radius: 4.5px;
        font-size: 15px;
        color: #FFFFFF;
        text-align:center;
        width:113px;
        height:34px;
        line-height:34px;
        float:right;
    }
    
    .add_e {
        background: #455868;
        border-radius: 4.5px;
        font-size: 15px;
        color: #FFFFFF;
        text-align:center;
        width:113px;
        height:34px;
        line-height:34px;
        float:right;
        margin-left: 10px;
    }
    
    .over-box {
        width:500px;
        height:260px;
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
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
    }
    
    .fancybox-skin {
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
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
    
    .alert_button {
        color:#FFFFFF;
        background:#E8483F;
        border-radius: 4px;
        width:128px;
        height:38px;
        line-height:38px;
        text-align:center;
        margin: 20px auto;
        text-decoration:none;
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
    
    .start {
        color:#00AF50;
        font-weight:bold;
    }
    
    .nostart {
        color:#D95155;
        font-weight:bold;
    }
    
    .rate {
        color:#455868;
        font-weight:bold;
    }

    /*手機尺寸*/
    @media (max-width: 767px) {
        .table-fixed tbody {
          height: 400px;
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
	    color: #455868;
	}
	
	/* visited link */
	a:visited {
	    color: #455868;
	}
	
	/* mouse over link */
	a:hover {
	    color: #455868;
	}
	
	/* selected link */
	a:active {
	    color: #455868;
	}
	
	.scrollbar-primary::-webkit-scrollbar {
	    width: 12px;
	    background: rgba(255,255,255,0.60);
	    border-radius: 15px; 
	}
	
	.scrollbar-primary::-webkit-scrollbar-thumb {
	    border-radius: 10px;
	    -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.1);
	    background-color: #A6A6A6; 
	}

	#back {
	    background: rgba(255,255,255,0.60);
	    border-radius: 15px;
	}
	
	.search_btn {
	    position:relative;
	    top:3px;
	    height:38px;
	    background:#666666;
	    border-radius: 0px 6px 6px 0px;
	    border: 0px;
	    margin-left: -4px;
	}
	
	input[type=text]::-ms-clear {  
	    display: none; 
	    width : 0; 
	    height: 0; 
	}

    input[type=text]::-ms-reveal {  
        display: none; 
        width : 0; 
        height: 0; 
    }
    
    a:hover {
	    text-decoration: none;
	}
	
{/literal}
</style>
<div id="BlockContainer" class="container">
    <div style="height:40px;line-height:40px;margin-bottom:20px;">

        <div class="block-title-font" style="float:left;">
           &nbsp;<a href="/mooc/user/myteaching.php">{'mycourse'|WM_Lang}</a>&nbsp;>&nbsp;{$courseData.caption|WM_Title}&nbsp;>&nbsp;{'student'|WM_Lang}
        </div>

	    <div style="float:right;">
	        <div style="display:flex">
	        <form method="post" action="{$appRoot}/mooc/user/student.php" id="searchForm" name="searchForm" style="margin-bottom: 0px;">
	            <input type="hidden" name="course_id" value="{$courseData.enc_course_id}">
	            <div style="display:inline-table"><input name="keyword" type="text" value="{$keyword}" placeholder="{'search_student'|WM_Lang}" class="input-search" maxlength="20" style="width:300px;height:38px"><a href="#"><i class="fas fa-times" title="{'clear'|WM_Lang}" style="font-size:22px;color:#666666;margin-left:-30px;position: relative;top: 4px;display:none"></i></a></div>
	            
	            <button class="search_btn" onclick="doSearch()"><i class="fa fa-search" title="{'search_student'|WM_Lang}" style="color: #ffffff;font-size:22px;line-height: 35px;"></i></button>
	        </form>
	        </div> 
	    </div>
    </div>
    <div id="back"> 
      <div class="table-responsive">
        <table class="table table-fixed">
          <thead>
            <tr>
              <th style="border-radius: 15px 0px 0px 0px;" class="col-xs-1 head1">NO</th>
              <th class="col-xs-2 head2"><a href="#" onclick="sort_data(1);" style="color:#FFFFFF">{'student_name'|WM_Lang}&nbsp;<i id="sort1" class="fas fa-sort-amount-up"></i></a></th>
              <th class="col-xs-4 head3"><a href="#" onclick="sort_data(2);" style="color:#FFFFFF">{'school'|WM_Lang}&nbsp;<i id="sort2" class="fas"></i></a></th>
              <th class="col-xs-2 head4">{'join_time'|WM_Lang}</th>
              <th class="col-xs-2 head5">{'last_time'|WM_Lang}</th>
              <th style="border-radius: 0px 15px 0px 0px;" class="col-xs-1 head6">{'delete'|WM_Lang}</th>
            </tr>
          </thead>
          <tbody class="scrollbar-primary">
            {if $data|@count eq 0}
                <td class="col-xs-12" colspan="6" style="text-align: center;"><div style="margin-top:50px;color:#C0C4C7"><i class="fas fa-users" style="font-size:100px;"></i><br>{$tip_msg}</div></td>
            {else}
            {foreach from=$data key=index item=value}
                <tr>
                        <td class="col-xs-1">{$index+1}</td>
                        <td class="col-xs-2" style="text-align:left;"><span id="{$value.username}">{$value.first_name}</span></td>
                        <td class="col-xs-4" style="text-align:left;">{$value.company}</td>
                        <td class="col-xs-2">{$value.add_time}</td>
                        <td class="col-xs-2">{if $value.exam_time == ''}{'NO'|WM_Lang}{else}{$value.exam_time}{/if}</td>
                        <td class="col-xs-1">{if $active==1}<a class="alert" href="#alert-box">{else}<a class="del" href="#del-box" onclick="del('{$value.username}');">{/if}<i class="fas fa-trash-alt" aria-hidden="true" title="{'student_del'|WM_Lang}"></i></a></td>
 
                </tr>
            {/foreach}
            {*<td class="col-xs-12" colspan="6" style="color:#0088D2">{'no_data'|WM_Lang}</td>*}
            {/if}
          </tbody>
        </table>
      </div>
    </div>
</div>

<div class="over-box" id="del-box">
    <form name="formDel" id="formDel" target="empty" style="margin-bottom: 0px;">
    <input type="hidden" name="course_id" value="{$courseData.course_id}">
    <input type="hidden" name="username" value="">
    <div style="background: #FFFFFF;border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;height: 287px;">
    <div style="background:#E8483F;height:6px;"></div>
    <div class="icon_warn"></div>
    <div style="padding: 0px 10px;"><span id="del_tip"></span></div>
    <div>
        <a href="#" onclick="delQuiz();"><div class="true_button">{'ok'|WM_Lang}</div></a><a href="#" onclick="close_fancy();"><div class="false_button">{'cancel'|WM_Lang}</div></a>
    </div>
    </div>
    </form>
</div>

<div class="over-box" id="alert-box">
    <div style="background: #FFFFFF;border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;height: 260px;">
	    <div style="background:#E8483F;height:6px;"></div>
	    <div class="icon_warn"></div>
	    <div style="text-align:center;">{'alert_del'|WM_Lang}</div>
	    <div>
	        <a href="#" onclick="close_fancy();"><div class="alert_button">{'ok'|WM_Lang}</div></a>
	    </div>
    </div>
</div>


<form name="formGoResult" method="post" action="/mooc/user/result.php">
<input type="hidden" name="course_id" value="{$courseData.course_id}" />
<input type="hidden" name="exam_id" value="" />
<input type="hidden" name="exam_type" value="" />
</form>

<form id="sortFm" name="sortFm" action="/mooc/user/student.php" method="post" style="display:inline">
<input type="hidden" name="course_id" value="{$courseData.enc_course_id}">
<input type="hidden" name="sortby" value="{$sort}" />
<input type="hidden" name="order" value="{$order}" />
</form>
<script type="text/javascript">
    var keyword ='{$keyword}';
    var sortby = '{$sort}';
    var order = '{$order}';
    var ticket = '{$ticket}';
    var referer = '{$referer}';
    var msg_del_tip = "{'delete_tip'|WM_Lang}";
    {literal}
    var winISunFunDon = null;
    
    $(document).ready(function() {

        $(".del").fancybox({
            'padding': 0,
            'margin': 0,
            'modal': true
        });
        
        $(".alert").fancybox({
            'padding': 0,
            'margin': 0,
            'modal': true
        });
        
        if (sortby==1) {
            $("#sort2").hide();
            if (order=='asc') {
                $("#sort1").removeClass("fa-sort-amount-down");
                $("#sort1").addClass("fa-sort-amount-up");
            } else {
                $("#sort1").removeClass("fa-sort-amount-up");
                $("#sort1").addClass("fa-sort-amount-down");
            }
        }
        
        if (sortby==2) {
            $("#sort1").hide();
            if (order=='asc') {
                $("#sort2").removeClass("fa-sort-amount-down");
                $("#sort2").addClass("fa-sort-amount-up");
            } else {
                $("#sort2").removeClass("fa-sort-amount-up");
                $("#sort2").addClass("fa-sort-amount-down");
            }
        }
        
        adjust();
        
        $('.input-search').on('keyup', function() {
            if ($('.input-search').val()=='') {
                $('.fa-times').hide();
            } else {
		        $('.fa-times').show();
		    }
	    });
	    
	    if (keyword!='') {
	        $('.fa-times').show();
	    }
	    
	    $('.fa-times').on('click', function() {
	        $('.input-search').val('');
	        $('.fa-times').hide();
	        if (keyword!='') {
	            doSearch();
	        }
	    });
	    
	    var ret = $('.scrollbar-primary').hasScrollBar();
        
        if(ret) {
           $('.head1').css({'padding-right':'8px'});
           $('.head2').css({'padding-right':'12px'});
           $('.head3').css({'padding-right':'20px'});
           $('.head4').css({'padding-right':'24px'});
           $('.head5').css({'padding-right':'28px'});
           $('.head6').css({'padding-right':'32px'});
        }
        

    }); 
    
    $(window).resize(function() {
        adjust();
    });    
    
    (function($) {
	    $.fn.hasScrollBar = function() {
	        return this.get(0).scrollHeight > this.height();
	    }
	})(jQuery);
    
    function adjust() {
        var back = $(window).height()-170;
        $("#back").css({"height":back});
        var scol =  back-60;
        $(".table-fixed tbody").css({"height":scol});
    }

    function sort_data(val){
        var obj = document.sortFm;
        if (obj.order.value == 'asc'){
            obj.order.value = 'desc';
        }else{
            obj.order.value = 'asc';
        }

        obj.sortby.value = val;
        obj.submit();
    } 

	function del(id)
	{
        document.formDel.username.value = id;
        caption = $("#"+id).html();
	    caption = msg_del_tip.replace("#name#", '<font color="red">'+caption+'</font>');
	    $("#del_tip").html(caption);
	}
	
	function delQuiz()
	{
	    close_fancy();
        var username = document.formDel.username.value;
        var course_id = document.formDel.course_id.value;
	    $.ajax({
            'url': '/mooc/user/modify.php',
            'type': 'POST',
            'data': {'action': 'delete_student','course_id':course_id,'username':username,'ticket':ticket,'referer':referer},
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
    {/literal}
</script>