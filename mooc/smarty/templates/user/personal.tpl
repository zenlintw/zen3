{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<style type="text/css">
{literal}
    #BlockContainer {
        padding-bottom: 48px;
    }

    #user-photo {
        padding-left: 15px;
        text-align: center;
    }

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
    .realname {
        font-size: 18px;
    }

    .form-group {
        height: 34px;
    }

    .form-horizontal .control-label {
        min-width: 200px;
        font-size: 14px;
    }

    .label-field {
        text-align: right;
        font-size: 14px;
        color: #393939;
    }

    label {
        font-weight: initial;
        line-height: 34px;
    }

    .text-field{
        font-size: 14px;
        color: #393939;
        line-height: 34px;
    }

    .comment-field {
        font-size: 14px;
        color: #7b7b7b;
    }

    #user-data-form {
        -moz-border-radius: 4px;
        -webkit-border-radius: 4px;
        border-radius: 4px;
        -moz-box-shadow: rgba(0, 0, 0, 0.05) 0 -1px 2px 0;
        -webkit-box-shadow: rgba(0, 0, 0, 0.05) 0 -1px 2px 0;
        box-shadow: rgba(0, 0, 0, 0.05) 0 -1px 2px 0;
        display: inline-block;
        background-color: white;
        border: 1px solid #CACACA;
        background-color: #F5F5F5;
    }

    /*手機尺寸*/
    @media (max-width: 767px) {
        .label-field {
            text-align: center;
            padding-right: 0px;
            white-space: nowrap;
        }

        label{
            padding-left: 10px;
        }

        .comment-field{
            text-align: right;
        }
    }

    /*平板直向、平板橫向*/
    @media (min-width: 768px) and (max-width: 992px) {
        .comment-field{
            text-align: right;
        }
    }

    /*large Desktop*/
    @media (min-width: 1200px) {
    }
{/literal}
</style>
<div style="background-color:#FFFFFF;height:30px;">&nbsp;</div>
<div id="BlockContainer" class="container">
    <div class="row">
        <div class="block-title-font col-md-12" style=""><i class="fa fa-address-card-o" aria-hidden="true" style="font-size:26px;margin-left:0.5em;margin-right:0.5em;"></i>{'personal_info'|WM_Lang}</div>
    </div>
    <div class="row">
        <div class="col-md-12" style="padding: 5px 0px 20px 15px;">
            <div style="background-color:#F18E1E;height:3px;">&nbsp;</div>
        </div>
    </div>
    <div class="row">
        <div id="user-photo" class="col-md-3 col-xs-12">
            <div class="photo-3l">
                <img src="/learn/personal/showpic.php?a={$profile.userPicId}" type="image/jpeg" id="showPic" borer="0" align="absmiddle">
                <div style="font-size:24px;float:right;position: relative;top:-20px;"><a id="photoA" href="javascript:;" data-toggle="tooltip" title="更換您的照片。最佳尺寸：168X168。副檔名：jpg, png, gif, jpeg" onclick="$('#myphoto').click();return false;" style="color:#777;" data-html="true"><i class="fa fa-camera" aria-hidden="true"></i></a></div>
            </div>
            <div class="row" style="line-height: 40px;">
                <div id="showRealName" class="realname col-md-12 col-xs-12">{$profile.realname}</div>
            </div>
        </div>
        <div id="user-data-form" class="col-md-9 col-xs-12">
            <form name="frmSetting" id="frmSetting" method="POST" action="" class="form-horizontal" onsubmit="return false;">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="ticket" value="{$ticket}">
                <div class="row" style="padding-top: 15px;">&nbsp;</div>
                <div class="form-group common-field row">
                    <div class="col-md-2 col-sm-4 col-xs-3 label-field"><label for="username">{'username'|WM_Lang}</label></div>
                    <div class="col-md-10 col-sm-8 col-xs-9 text-field">{$userDetailData.username}</div>
                </div>

                <div class="clearfix"></div>
                
                <div class="form-group common-field row">
                    <div class="col-md-2 col-sm-4 col-xs-3 label-field"><label for="password">{'password'|WM_Lang}</label></div>
                    <div class="col-md-10 col-sm-8 col-xs-9 text-field">
                        <input type="password" class="form-control" id="password" name="password" value="" autocomplete="off" autocapitalize="off" autocorrect="off" size="15" style="width:100px;">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 col-sm-4 col-xs-3">&nbsp;</div>
                    <div class="col-md-10 col-sm-8 col-xs-9" style="margin-top: -5px;">{'msg_password'|WM_Lang}</div>
                </div>
                
                <div class="clearfix"></div>
                <div class="form-group common-field row">
                    <div class="col-md-2 col-sm-4 col-xs-3 label-field"><label for="repassword">{'repassword'|WM_Lang}</label></div>
                    <div class="col-md-5 col-sm-8 col-xs-9 text-field">
                        <input type="password" class="form-control" id="repassword" name="repassword" value="" autocomplete="off" autocapitalize="off" autocorrect="off" size="15" style="width:100px;">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 col-sm-4 col-xs-3">&nbsp;</div>
                    <div class="col-md-10 col-sm-8 col-xs-9" style="margin-top: -5px;">{'msg_repassword'|WM_Lang}</div>
                </div>
                
                <div class="clearfix"></div>
                <div class="form-group common-field row">
                    <div class="col-md-2 col-sm-4 col-xs-3 label-field"><label for="first_name">{'first_name'|WM_Lang}<font color="red">*</font></label></div>
                    <div class="col-md-5 col-sm-8 col-xs-9 text-field">
                    <input type="text" class="form-control text-field" id="first_name" name="first_name" value="{$userDetailData.first_name}" autocomplete="off" autocapitalize="off" autocorrect="off"  style="max-width: 100px;">
                    </div>
                </div>
                {*<div class="row">
                    <div class="col-md-2 col-sm-4 col-xs-3">&nbsp;</div>
                    <div class="col-md-10 col-sm-8 col-xs-9" style="margin-top: -5px;">請填入中文姓名</div>
                </div>*}
                
                <div class="clearfix"></div>

                <div class="form-group common-field row">
                    <div class="col-md-2 col-sm-4 col-xs-3 label-field"><label>{'gender'|WM_Lang}</label></div>
                    <div class="col-md-5 col-sm-8 col-xs-9 text-field">
                        <input type="radio" id="sysRadioBtnM" name="gender" value="M"{if $userDetailData.gender eq 'M'} checked="checked"{/if}><label for="sysRadioBtnM">{'male'|WM_Lang}</label>
                        <input type="radio" id="sysRadioBtnF" name="gender" value="F"{if $userDetailData.gender eq 'F'} checked="checked"{/if}><label for="sysRadioBtnF">{'female'|WM_Lang}</label>
                    </div>
                </div>

                <div class="clearfix"></div>

                {*<div class="form-group common-field row">
                    <div class="col-md-2 col-sm-4 col-xs-3 label-field"><label>{'lbl_unit_inout'|WM_Lang}<font color="red">*</font></label></div>
                    <div class="col-md-5 col-sm-8 col-xs-9 text-field">
                    <input type="radio" id="CO_fda_memberY" name="CO_fda_member" value="Y"{if $userDetailData.CO_fda_member eq 'Y'} checked="checked"{/if} disabled="disabled"><label>食品藥物管理署內</label>
                    <input type="radio" id="CO_fda_memberN" name="CO_fda_member" value="N"{if $userDetailData.CO_fda_member eq 'N'} checked="checked"{/if} disabled="disabled"><label>署外，一般民眾</label>
                    </div>
                    <div class="col-md-5 col-sm-12 col-xs-12 comment-field">{'msg_fill_department'|WM_Lang}</div>
                </div>
                <div class="clearfix"></div>*}

                <div class="form-group common-field row">
                    <div class="col-md-2 col-sm-4 col-xs-3 label-field"><label for="email">{'email'|WM_Lang}<font color="red">*</font></label></div>
                    <div class="col-md-5 col-sm-8 col-xs-9 text-field">
                    <input type="text" class="form-control text-field" id="email" name="email" value="{$userDetailData.email}" autocomplete="off" autocapitalize="off" autocorrect="off"  style="max-width: 200px;">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 col-sm-4 col-xs-3">&nbsp;</div>
                    <div class="col-md-10 col-sm-8 col-xs-9" style="margin-top: -5px;">{'msg_email'|WM_Lang}</div>
                </div>
                
                <div class="clearfix"></div>

                <div class="form-group common-field row">
                    <div class="col-md-2 col-sm-4 col-xs-3 label-field"><label for="department">{'department'|WM_Lang}</label></div>
                    <div class="col-md-5 col-sm-8 col-xs-9 text-field">
                    <input type="text" class="form-control text-field" id="department" name="department" value="{$userDetailData.department}" autocomplete="off" autocapitalize="off" autocorrect="off"  style="max-width: 200px;">
                    </div>
                </div>
                <div class="clearfix"></div>

                <div class="form-group common-field row">
                    <div class="col-md-2 col-sm-4 col-xs-3 label-field"><label for="title">{'title'|WM_Lang}</label></div>
                    <div class="col-md-5 col-sm-8 col-xs-9 text-field">
                    <input type="text" class="form-control text-field" id="title" name="title" value="{$userDetailData.title}" autocomplete="off" autocapitalize="off" autocorrect="off"  style="max-width: 200px;">
                    </div>
                </div>
                <div class="clearfix"></div>

                <div class="form-group common-field row">
                    <div class="col-md-2 col-sm-4 col-xs-3 label-field"><label for="office_tel">{'office_tel'|WM_Lang}</label></div>
                    <div class="col-md-5 col-sm-8 col-xs-9 text-field">
                    <input type="text" class="form-control text-field" id="office_tel" name="office_tel" value="{$userDetailData.office_tel}" autocomplete="off" autocapitalize="off" autocorrect="off"  style="max-width: 180px;">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 col-sm-4 col-xs-3">&nbsp;</div>
                    <div class="col-md-10 col-sm-8 col-xs-9" style="margin-top: -5px;">{'msg_home_tel'|WM_Lang}</div>
                </div>
                
                <div class="clearfix"></div>

                <div class="form-group common-field row">
                    <div class="col-md-2 col-sm-4 col-xs-3 label-field"><label for="office_fax">{'office_fax'|WM_Lang}</label></div>
                    <div class="col-md-5 col-sm-8 col-xs-9 text-field">
                    <input type="text" class="form-control text-field" id="office_fax" name="office_fax" value="{$userDetailData.office_fax}" autocomplete="off" autocapitalize="off" autocorrect="off"  style="max-width: 180px;">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 col-sm-4 col-xs-3">&nbsp;</div>
                    <div class="col-md-10 col-sm-8 col-xs-9" style="margin-top: -5px;">{'msg_home_tel'|WM_Lang}</div>
                </div>
                
                <div class="clearfix"></div>

                <div class="form-group common-field row">
                    <div class="col-md-2 col-sm-4 col-xs-3 label-field"><label for="office_address">{'office_address'|WM_Lang}</label></div>
                    <div class="col-md-5 col-sm-8 col-xs-9 text-field">
                    <input type="text" class="form-control text-field" id="office_address" name="office_address" value="{$userDetailData.office_address}" autocomplete="off" autocapitalize="off" autocorrect="off"  style="max-width: 200px;">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 col-sm-4 col-xs-3">&nbsp;</div>
                    <div class="col-md-10 col-sm-8 col-xs-9" style="margin-top: -5px;">{'msg_home_address'|WM_Lang}</div>
                </div>
                
                <div class="clearfix"></div>

                <div class="form-group common-field row">
                    <div id="toolbar" class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <hr class="common-field" style="border-top: 1px solid #e0e0e0;margin:0px;">
                        <button type="button" class="btn btn-orange" id="apply-course" onclick="chkform();" style="margin:10px;">確定修改</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<form method="POST" enctype="multipart/form-data" action="/mooc/user/personal_save.php" style="display:none;">
<input type="hidden" name="action" value="changeMyPhoto" />
<input type="hidden" name="ticket" value="{$ticket}">
<input type="file" id="myphoto" name="myphoto" accept=".jpg,.jpeg,.gif,.png" onchange="checkUpload(this);" />
</form>

{include file = "common/site_footer.tpl"}
<script type="text/javascript" src="{$appRoot}/mooc/public/js/password_common.js?{$file_rev_num}"></script>
<script type="text/javascript">

var username = "{$userDetailData.username}";
{literal}
var requiredError = false;

//驗證表單欄位
function chkform(){
    requiredError  = false;
    // 清除所有輸入錯誤提示呈現
    $('.alert-lcms-error').removeClass('alert-lcms-error');

    //先檢查所有通用必填欄位
    var requireAry = ['first_name','email'];
    if ($('#CO_fda_memberY').is(':checked') && $('#CO_fda_memberY').val() == 'Y') {
        requireAry.push('department');
        requireAry.push('title');
    }

    
    $.each(requireAry, function (i, v) {
        var validInput = $("input[name="+v+"]");
        validInput.tooltip('destroy').removeClass('alert-lcms-error');
        if (('' == validInput.val() || null == validInput.val()) ) {
            validInput.attr('data-original-title', '此欄位為必填')
                        .tooltip('toggle')
                        .addClass('alert-lcms-error');
            if (false === requiredError) {
                validInput.focus();
            }
            requiredError = true;
        }
        if(v== 'email' && validInput.val() != ''){
            if(!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(validInput.val()))){
                validInput.attr('data-original-title', 'Email格式不正確')
                        .tooltip('toggle')
                        .addClass('alert-lcms-error');
                requiredError = true;
                validInput.focus();
            }
        }

        /*中文姓名 檢查
        if(v== 'first_name' && validInput.val() != ''){
            var s = validInput.val();
            for(var i = 0; i < s.length; i++) {
                if(s.charCodeAt(i) < 0x4E00 || s.charCodeAt(i) > 0x9FA5) {
                    validInput.attr('data-original-title', '填入值有非中文字元，請重新填入中文姓名')
                        .tooltip('toggle')
                        .addClass('alert-lcms-error');
                    requiredError = true;
                    validInput.focus();
                    break;
                }
            }
        }*/
    });

    if ($('#password').val() != '') {
        if ($('#password').val() != $('#repassword').val()) {
            $('#password').attr('data-original-title', '密碼不一致，請重新輸入')
                        .tooltip('toggle')
                        .addClass('alert-lcms-error');
            $('#password').val('');
            $('#repassword').val('');
            requiredError = true;
            $('#password').focus();
        }

        if(!requiredError){
            requiredError = passwordFormat($("#password").get(0));
        }
        if(!requiredError){
           requiredError = passwordHistory(username, $("#password"));
        }
        
    }
    if (requiredError) return false;
    // valid OK, submit

    // 移除disabled, 否則表單不會有值
    $('#CO_fda_memberY').removeAttr("disabled");
    $('#CO_fda_memberN').removeAttr("disabled");
    
    var $source = $('#frmSetting');
    
    $.ajax({
        'url' : '/mooc/user/personal_save.php',
        'type': 'POST',
        'data': $source.serialize(),
        'dataType': 'json',
        'success': function (data) {

            if (data.success === true) {
                alert('success');
                window.location.href = '/mooc/user/personal.php';
            } else {
                console.log(data);
                msg = data.error;
                
                // 清除所有輸入錯誤提示呈現
                $('.alert-input-error').removeClass('alert-input-error');
                $('input,textarea,div').tooltip('destroy');

                //後端驗證顯示訊息
                for (var i = 0; i < msg.length; i++) {
                    $("[name='" + msg[i].id + "']").attr('title', msg[i].message).tooltip('show');

                    $('[name="' + msg[i].id + '"]').addClass('alert-input-error');
                }
            }
            
        },
        'fail': function (data) {
            if (window.console) {console.log('post fail.');}
        }
    });

    return false;
    // document.frmSetting.submit();

}


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
