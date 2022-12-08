// 檢查重發驗證信輸入資料
function checkData() {

    var re = null;
    var obj = document.getElementById("formResend");
    if (obj == null) return false;
    $('#resendto,#email').tooltip('destroy');

    if (obj.resendto.value == "") {
        $('#resendto')
            .attr('title', MSG[1])
            .tooltip('toggle');
        obj.resendto.focus();
        return false;
    }

    if ((obj.resendto.value.length < parseInt(sysAccountMinLen)) || (obj.resendto.value.length > parseInt(sysAccountMaxLen))) {
        $('#resendto')
            .attr('title', MSG[2])
            .tooltip('toggle');
        obj.resendto.focus();
        return false;
    }

    /*
    * compute the total number of underline or minus
    */
    var data3 = obj.resendto.value;

    var underline = 0;
    var minus = 0;
    for (var i =0;i<data3.length;i++){
        if (data3.charAt(i) == '_'){
            underline++;
        }
        if (data3.charAt(i) == '-'){
            minus++;
        }
    }
    var total_sum = underline+minus;

    if ( (total_sum) > 1){
        $('#resendto')
            .attr('title', MSG[3])
            .tooltip('toggle');
        obj.resendto.focus();
        return false;
    }

    if (underline > 1){
        $('#resendto')
            .attr('title', MSG[3])
            .tooltip('toggle');
        obj.resendto.focus();
        return false;
    }

    if (minus > 1){
        $('#resendto')
            .attr('title', MSG[3])
            .tooltip('toggle');
        obj.resendto.focus();
        return false;
    }

    /*
    * 檢查底線 and 減號 有無出現在 字尾
    */
    if( (data3.substring(data3.length-1,data3.length) == '_') || (data3.substring(data3.length-1,data3.length) == '-')){
        $('#resendto')
            .attr('title', MSG[3])
            .tooltip('toggle');
        obj.resendto.focus();
        return false;
    }

    re = Account_format;
    if (!re.test(obj.resendto.value)) {
        $('#resendto')
            .attr('title', MSG[3])
            .tooltip('toggle');
        obj.resendto.focus();
        return false;
    }

    if (obj.email.value == "") {
        $('#email')
            .attr('title', MSG[10])
            .tooltip('toggle');
        obj.email.focus();
        return false;
    }

    re = mail_rule;
    if (!re.test(obj.email.value)) {
        $('#email')
            .attr('title', MSG[11])
            .tooltip('toggle');
        obj.email.focus();
        return false;
    }

    return true;
}

function trimForm() {
    var objs = document.getElementsByTagName("input");
    var re = /[ ]+$/ig;
    for (i = 0; i < objs.length; i++) {
        objs[i].value = objs[i].value.replace(re, "");
    }
}

$(document).ready( function() {
	// 檢查電子信箱有沒有重複
    $('#email').change(function() {
        $('#resendto,#email').tooltip('destroy');
        $('#email').parent().children('.icon-ok,.icon-remove').remove();

        $.ajax({
            'type': 'POST',
            'dataType': 'json',
            'data': {'action' : 'getEmailDuplicate', 'email' : $('#email').val()},
            'url': appRoot + '/mooc/controllers/user_ajax.php',
            'success': function (data) {
                switch(data) {
                    case 0:
                        $('#email').after('<i class="icon-ok"></i>');
                        break;

                    case 2:
                        $('#email')
                            .attr('title', "("+$('#email').val()+")"+MSG[17])
                            .tooltip('toggle')
                            .after('<i class="icon-remove"></i>');
							 document.getElementById('email').value='';
                        break;

                    case 3:
                        $('#email')
                            .attr('title', "("+$('#email').val()+")"+MSG[11])
                            .tooltip('toggle')
                            .after('<i class="icon-remove"></i>');
							 document.getElementById('email').value='';
                        break;

                    case 4:
                        $('#email')
                            .attr('title', "("+$('#email').val()+")"+MSG[10])
                            .tooltip('toggle')
                            .after('<i class="icon-remove"></i>');
							 document.getElementById('email').value='';
                        break;
                }
            },
            'error': function () {
                alert('Ajax Error!');
            }
        });
    });
	
});

window.onload = trimForm;