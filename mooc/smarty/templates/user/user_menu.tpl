<style type="text/css">
{literal}
.photo-3l {
  -moz-border-radius: 4px;
  -webkit-border-radius: 4px;
  border-radius: 4px;
  -moz-box-shadow: rgba(0, 0, 0, 0.05) 0 -1px 2px 0;
  -webkit-box-shadow: rgba(0, 0, 0, 0.05) 0 -1px 2px 0;
  box-shadow: rgba(0, 0, 0, 0.05) 0 -1px 2px 0;
  display: inline-block;
  width: 182px;
  height: 182px;
  padding: 7px;
  margin-top: 15px;
  position: relative;
  background-color: white;
  border: 1px solid #CACACA;
}
.photo-3l > img {
  width: 168px;
  height: 168px;
  max-width: initial;
  background-size: cover;
  background-position: 50% 50%;
  background-repeat: no-repeat no-repeat;
}
.id {
  font-size: 2em;
  line-height: 1.1em;
  font-weight: bold;
  /*white-space: nowrap;*/
  word-break: break-all;
}
.id .name {
  display: inline-block;
  color: #939393;
  font-size: 0.5em;
}

.realname {
  display: inline-block;
  font-size: 1.5em;
  padding: 10px;
  line-height: 20px;
}

.bs-sidebar {
  top: 0px;
  font-size: 16px;
}

.tooltip-inner {
    width: 350px;
    white-space:pre-wrap;
}
{/literal}
</style>
{if $smarty.server.REQUEST_URI eq '/mooc/user/personal.php'}
<form method="POST" enctype="multipart/form-data" action="/mooc/user/personal_save.php" style="display:none;">
<input type="hidden" name="action" value="changeMyPhoto" />
<input type="file" id="myphoto" name="myphoto" accept=".jpg,.jpeg,.gif,.png" onchange="checkUpload(this);" />
</form>
{/if}
<div id="contentGroupWrap" class="span3 bs-sidebar hidden-xs">
    <div class="box lcms-sidebar" id="contentGroup">
        <div class="group">
            <div class="filter">
                <table class="table tree">
                    <tbody>
                            <tr class="treegrid-photo root"><td>
                                <div class="photo-3l"><img src="/learn/personal/showpic.php?a={$profile.userPicId}" type="image/jpeg" id="showPic" borer="0" align="absmiddle"></div>
                                {if $smarty.server.REQUEST_URI eq '/mooc/user/personal.php'}
                                <div style="position:relative;float:right;margin-top:-27px;margin-right: 15px;font-size:24px;"><a id="photoA" href="javascript:;" data-toggle="tooltip" title="更換您的照片<BR />最佳尺寸：168X168<BR />副檔名：jpg, png, gif, jpeg" onclick="$('#myphoto').click();return false;" style="color:#777;" data-html="true"><i class="fa fa-camera" aria-hidden="true"></i></a></div>
                                {/if}
                                <div id="showRealName" class="realname">{$profile.realname}</div>
                            </td></tr>
                            <tr class="treegrid root"><td>&nbsp;</td></tr>
                            <tr class="treegrid root"><td>&nbsp;</td></tr>
                            <tr class="treegrid root">
                                {if $smarty.server.REQUEST_URI eq '/mooc/user/mycourse.php'}
                                <td class="active"><span class="treegrid-expander"></span><span class="link" style="cursor:initial;"><i class="fa fa-chevron-circle-right" style="color:white;">&nbsp;&nbsp;</i>我的課程<div></div></span></td>
                                {else}
                                <td><span class="treegrid-expander"></span><span class="link" style="" onclick="document.location.href='/mooc/user/mycourse.php';"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i>我的課程<div></div></span></td>
                                {/if}
                            </tr>
                            {if $profile.isTeacher}
                            <tr class="treegrid root">
                                {if $smarty.server.REQUEST_URI eq '/mooc/user/myteaching.php'}
                                <td class="active"><span class="treegrid-expander"></span><span class="link" style="cursor:initial;"><i class="fa fa-chevron-circle-right" style="color:white;">&nbsp;&nbsp;</i>授課管理<div></div></span></td>
                                {else}
                                <td><span class="treegrid-expander"></span><span class="link" style="" onclick="document.location.href='/mooc/user/myteaching.php';"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i>授課管理<div></div></span></td>
                                {/if}
                            </tr>
                            {/if}
                            <tr class="treegrid root">
                                {if $smarty.server.REQUEST_URI eq '/mooc/user/learn_stat.php'}
                                <td class="active"><span class="treegrid-expander"></span><span class="link" style="cursor:initial;"><i class="fa fa-chevron-circle-right" style="color:white;">&nbsp;&nbsp;</i>我的學習歷程<div></div></span></td>
                                {else}
                                <td><span class="treegrid-expander"></span><span class="link" style="" onclick="document.location.href='/mooc/user/learn_stat.php';"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i>我的學習歷程<div></div></span></td>
                                {/if}
                            </tr>
                            <tr class="treegrid root">
                                {if $smarty.server.REQUEST_URI eq '/mooc/user/election.php'}
                                <td class="active"><span class="treegrid-expander"></span><span class="link" style="cursor:initial;"><i class="fa fa-chevron-circle-right" style="color:white;">&nbsp;&nbsp;</i>選課結果<div></div></span></td>
                                {else}
                                <td><span class="treegrid-expander"></span><span class="link" style="" onclick="document.location.href='/mooc/user/election.php';"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i>選課結果<div></div></span></td>
                                {/if}
                            </tr>
                            <tr class="treegrid root">
                                {if $smarty.server.REQUEST_URI eq '/mooc/user/personal.php'}
                                <td class="active"><span class="treegrid-expander"></span><span class="link" style="cursor:initial;"><i class="fa fa-chevron-circle-right" style="color:white;">&nbsp;&nbsp;</i>個人資料<div></div></span></td>
                                {else}
                                <td><span class="treegrid-expander"></span><span class="link" style="" onclick="document.location.href='/mooc/user/personal.php';"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i>個人資料<div></div></span></td>
                                {/if}
                            </tr>
                            <tr class="treegrid root">
                                {if $smarty.server.REQUEST_URI eq '/mooc/user/tree_news.php'}
                                <td class="active"><span class="treegrid-expander"></span><span class="link" style="cursor:initial;"><i class="fa fa-chevron-circle-right" style="color:white;">&nbsp;&nbsp;</i>我的積點<div></div></span></td>
                                {else}
                                <td><span class="treegrid-expander"></span><span class="link" style="" onclick="document.location.href='/mooc/user/tree_news.php';"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i>我的積點<div></div></span></td>
                                {/if}
                            </tr>
                            {$profile.isCsApply.pass}
                            {if $profile.isCsApply >= 1}
                            <tr class="treegrid root">
                                <td><span class="treegrid-expander"></span><span class="link" style="" onclick="document.location.href='/mooc/course/open_course.php';"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i>開課申請<div></div></span></td>
                            </tr>
                            {/if}
                            {if in_array('pass', $profile.arr_menu_role)}
                            <tr class="treegrid root">
                                {if $smarty.server.REQUEST_URI eq '/mooc/report/pass_status.php'}
                                <td class="active"><span class="treegrid-expander"></span><span class="link" style="cursor:initial;"><i class="fa fa-chevron-circle-right" style="color:white;">&nbsp;&nbsp;</i>資教組查詢報表<div></div></span></td>
                                {else}
                                <td><span class="treegrid-expander"></span><span class="link" style="" onclick="document.location.href='/mooc/report/pass_status.php';"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i>資教組查詢報表<div></div></span></td>
                                {/if}
                            </tr>
                            {/if}
                            {if in_array('school', $profile.arr_menu_role)}
                            <tr class="treegrid root">
                                {if $smarty.server.REQUEST_URI eq '/mooc/report/school_status.php'}
                                <td class="active"><span class="treegrid-expander"></span><span class="link" style="cursor:initial;"><i class="fa fa-chevron-circle-right" style="color:white;">&nbsp;&nbsp;</i>單一學校查詢報表<div></div></span></td>
                                {else}
                                <td><span class="treegrid-expander"></span><span class="link" style="" onclick="document.location.href='/mooc/report/school_status.php';"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i>單一學校查詢報表<div></div></span></td>
                                {/if}
                            </tr>
                            {/if}
                            {if in_array('art', $profile.arr_menu_role)}
                            <tr class="treegrid root">
                                {if $smarty.server.REQUEST_URI eq '/mooc/report/art_status.php'}
                                <td class="active"><span class="treegrid-expander"></span><span class="link" style="cursor:initial;"><i class="fa fa-chevron-circle-right" style="color:white;">&nbsp;&nbsp;</i>藝教司查詢報表<div></div></span></td>
                                {else}
                                <td><span class="treegrid-expander"></span><span class="link" style="" onclick="document.location.href='/mooc/report/art_status.php';"><i class="fa fa-chevron-circle-right">&nbsp;&nbsp;</i>藝教司查詢報表<div></div></span></td>
                                {/if}
                            </tr>
                            {/if}

                </tbody></table>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<script>
{literal}
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip(); 
});
function checkUpload(obj){
  if(/^image/.test(obj.files[0].type)){
    obj.form.submit();
  }else{
    alert('僅允許上傳「jpg、jpeg、gif、png」檔案');
    obj.value='';
  }
}
{/literal}
</script>