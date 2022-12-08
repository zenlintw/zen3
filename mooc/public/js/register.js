// 檢查註冊資料
function checkData() {

    var re = null;
    var obj = document.getElementById("formRegister");
    if (obj == null) return false;
    $('#username,#password,#repassword,#first_name,#email,#is_agree').tooltip('destroy');

    if (obj.username.value == "") {
        $('#username')
            .attr('title', MSG[1])
            .tooltip('toggle');
        obj.username.focus();
        return false;
    }

    if ((obj.username.value.length < parseInt(sysAccountMinLen)) || (obj.username.value.length > parseInt(sysAccountMaxLen))) {
        $('#username')
            .attr('title', MSG[2])
            .tooltip('toggle');
        obj.username.focus();
        return false;
    }

    /*
    * compute the total number of underline or minus
    */
    var data3 = obj.username.value;

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
        $('#username')
            .attr('title', MSG[3])
            .tooltip('toggle');
        obj.username.focus();
        return false;
    }

    if (underline > 1){
        $('#username')
            .attr('title', MSG[3])
            .tooltip('toggle');
        obj.username.focus();
        return false;
    }

    if (minus > 1){
        $('#username')
            .attr('title', MSG[3])
            .tooltip('toggle');
        obj.username.focus();
        return false;
    }

    /*
    * 檢查底線 and 減號 有無出現在 字尾
    */
    if( (data3.substring(data3.length-1,data3.length) == '_') || (data3.substring(data3.length-1,data3.length) == '-')){
        $('#username')
            .attr('title', MSG[3])
            .tooltip('toggle');
        obj.username.focus();
        return false;
    }

    re = Account_format;
    if (!re.test(obj.username.value)) {
        $('#username')
            .attr('title', MSG[3])
            .tooltip('toggle');
        obj.username.focus();
        return false;
    }

    if (obj.password.value == "") {
        $('#password')
            .attr('title', MSG[4])
            .tooltip('toggle');
        obj.password.focus();
        return false;
    }

    if (obj.password.value.length < 6) {
        $('#password')
            .attr('title', MSG[5])
            .tooltip('toggle');
        obj.password.focus();
        return false;
    }

    if (obj.repassword.value == "") {
        $('#repassword')
            .attr('title', MSG[6])
            .tooltip('toggle');
        obj.repassword.focus();
        return false;
    }

    if (obj.password.value != obj.repassword.value) {
        $('#repassword')
            .attr('title', MSG[7])
            .tooltip('toggle');
        obj.repassword.focus();
        return false;
    }

    if (obj.first_name.value == "") {
        $('#first_name')
            .attr('title', MSG[8])
            .tooltip('toggle');
        obj.first_name.focus();
        return false;
    }

    if (!Filter_Spec_char(obj.first_name.value)){
        obj.first_name.focus();
        $('#first_name')
            .attr('title', un_htmlspecialchars(MSG[13]))
            .tooltip('toggle');
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

    if (obj.captcha.value == "") {
        $('#captcha')
            .attr('title', MSG[20])
            .tooltip('toggle');
        obj.captcha.focus();
        return false;
    }

    return true;
}

// 即時檢查帳號
function check_reg_username() {
    var xmlHttp = null;
    var xmlDocs = null;
    var txt = '';

    var obj = document.getElementById("formRegister");
    if (obj == null) return false;

    if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
    if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();

    if(obj.username.value != '') {

        $('#username').parent().children('.icon-ok,.icon-remove').remove();

        txt  = "<manifest>";
        txt += "<exist_user>"+obj.username.value+"</exist_user>";
        txt += "</manifest>";
        if (!xmlDocs.loadXML(txt)) {
            xmlDocs.loadXML("<manifest />");
            return false;
        }
        xmlHttp.open("POST", 　appRoot + '/sys/reg/check_user.php', false);
        xmlHttp.send(xmlDocs);
        if (!xmlDocs.loadXML(xmlHttp.responseText)) {
            xmlDocs.loadXML(txt);
        }
        node = xmlDocs.selectSingleNode('//result');
        if (node.hasChildNodes()) {
            result_id = node.firstChild.nodeValue;
            switch (result_id) {
                case '0':
                    $('#username')
                        .tooltip('destroy')
                        .after('<i class="icon-ok"></i>');
                    return true;
                    break;

                case '1':
                case '4':
                    $('#username')
                        .attr('title', obj.username.value + MSG[16])
                        .tooltip('toggle')
                        .after('<i class="icon-remove"></i>');
                    break;
                case '2':
                    $('#username')
                        .attr('title', '( ' + obj.username.value + ' )' + MSG[15])
                        .tooltip('toggle')
                        .after('<i class="icon-remove"></i>');
                    break;
                case '3':
                    $('#username')
                        .attr('title', MSG[3])
                        .tooltip('toggle')
                        .after('<i class="icon-remove"></i>');
                    break;
            }
            obj.username.value = '';
            obj.username.focus();
            return false;
        }
    }
}

function trimForm() {
    var objs = document.getElementsByTagName("input");
    var re = /[ ]+$/ig;
    for (i = 0; i < objs.length; i++) {
        objs[i].value = objs[i].value.replace(re, "");
    }
}

$(document).ready( function() {

    // 確認密碼
    $('#repassword').change(function() {
        $('#username,#password,#repassword,#first_name,#email,#is_agree').tooltip('destroy');
        $('#repassword').parent().children('.icon-ok,.icon-remove').remove();

        if ($('#password').val() != $('#repassword').val()) {
            $('#repassword')
                .attr('title', MSG[7])
                .tooltip('toggle')
                .after('<i class="icon-remove"></i>')
                .focus();
        } else {
            $('#repassword').after('<i class="icon-ok"></i>');
        }
    });

    $(function () {
        // 姓名
        $('#first_name').change(function() {
            $('#username,#password,#repassword,#first_name,#email,#is_agree').tooltip('destroy');
            $('#first_name').parent().children('.icon-ok,.icon-remove').remove();

            if (!Filter_Spec_char($('#first_name').val())){
                $('#first_name')
                    .attr('title', un_htmlspecialchars(MSG[13]))
                    .tooltip('show')
                    .after('<i class="icon-remove"></i>')
                    .focus();
            } else {
                $('#first_name').after('<i class="icon-ok"></i>');
            }
        });
    });

    // 檢查電子信箱有沒有重複
    $('#email').change(function() {
        $('#username,#password,#repassword,#first_name,#email,#is_agree').tooltip('destroy');
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

//                    case 2:
//                        $('#email')
//                            .attr('title', MSG[18])
//                            .tooltip('toggle')
//                            .after('<i class="icon-remove"></i>');
//                        break;

                    case 3:
                        $('#email')
                            .attr('title', MSG[11])
                            .tooltip('toggle')
                            .after('<i class="icon-remove"></i>');
                        break;

                    case 4:
                        $('#email')
                            .attr('title', MSG[10])
                            .tooltip('toggle')
                            .after('<i class="icon-remove"></i>');
                        break;
                }
            },
            'error': function () {
                alert('Ajax Error!');
            }
        });
    });

    // 載入時預設焦點
    $('#username').focus();

    // 偵測密碼強度
    $("#password").passStrength({
        shortPass: 		"top_shortPass",
        badPass:		"top_badPass",
        goodPass:		"top_goodPass",
        strongPass:		"top_strongPass",
        baseStyle:		"top_testresult",
        userid:			"#username",
        messageloc:		0
    });

    // 密碼
    $('#password').change(function() {
        $('#username,#password,#repassword,#first_name,#email,#is_agree').tooltip('destroy');
        $('#password').parent().children('.icon-ok,.icon-remove').remove();

        if (this.value.length < 6) {
            $('#password').attr('title', MSG[5])
                .tooltip('toggle')
                .after('<i class="icon-remove"></i>')
                .focus();
            return false;
        } else {
            $('#password').after('<i class="icon-ok"></i>');
        }
    });

    $('.privacy-service').fancybox({
        maxWidth	: 890,
        maxHeight	: 600,
        fitToView	: false,
        width		: '80%',
        height		: '80%',
        autoSize	: false,
        closeClick	: false,
        openEffect	: 'none',
        closeEffect	: 'none',
        helpers : {
            overlay : {
                locked : false
            }
        }
    });
});

window.onload = trimForm;