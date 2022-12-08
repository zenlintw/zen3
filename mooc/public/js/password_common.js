// 客製密碼
function passwordFormat(obj){
    var requiredError = false;
    var p = obj.value;
    var passVerObj = { 'lower' : 0, 'upper' : 0, 'number' : 0,'specal' :0 };
    // $('#password').tooltip('destroy');
    if(p.length < 8){
        $(obj)
            .attr('data-original-title', "至少8個字元以上")
            .tooltip('toggle')
            .addClass('alert-lcms-error');
        requiredError = true;
        $(obj).focus();

    }else if(p.length > 20){
        $(obj)
            .attr('data-original-title', "限制20字元以下")
            .tooltip('toggle')
            .addClass('alert-lcms-error');
        requiredError = true;
        $(obj).focus();
    }else{
        for(var i in p){
            unicodeC =  p.charCodeAt(i);
            if(unicodeC >= 97 && unicodeC <= 122){
                passVerObj.lower ++;
            }else if (unicodeC >= 65 && unicodeC <= 90){
                passVerObj.upper ++;
            }else if(unicodeC >= 48 && unicodeC <= 57){
                passVerObj.number ++;
            }else{
                passVerObj.specal ++;
            }
        }
        // console.log(passVerObj);
        var tmp = 0;
        for(var attr in passVerObj){
            if(passVerObj[attr] != 0){
                tmp ++;
            }
        }
        if(tmp < 3){
            $(obj)
                .attr('data-original-title', "密碼需包含大小寫字母、數字或特殊符號至少四選三")
                .tooltip('toggle')
                .addClass('alert-lcms-error');
            requiredError = true;
            $(obj).focus();
        }
    }
    return requiredError;
   
    
}

function passwordHistory(u, pobj){
    var requiredError;
    $.ajax({
        method : 'POST',
        async : false,
        url : '/mooc/controllers/user_ajax.php',
        data : {
            'u' : u,
            'p' : pobj.val(),
            'action' : 'checkPasswordresult'
        },
        dataType : 'json',
        success : function(res){
            if(res.code == 1){
                pobj
                .attr('data-original-title', "密碼與前五次密碼相同，請重新輸入")
                .tooltip('toggle')
                .addClass('alert-lcms-error');
                requiredError = true;
            }else if(res.code == 2){
                pobj
                .attr('data-original-title', "錯誤")
                .tooltip('toggle')
                .addClass('alert-lcms-error');
                requiredError = true;
            }else{
                requiredError = false;
            }
        },
        error : function(){
            requiredError = true;
             pobj
            .attr('data-original-title', "ajax error")
            .tooltip('toggle')
            .addClass('alert-lcms-error');

        }

    })
    return requiredError;
}