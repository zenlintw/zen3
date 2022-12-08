// 檢查註冊資料
function checkData() {

    var re = null;
    var obj = document.getElementById("formResetPwd");
    if (obj == null) return false;

    if (obj.username.value == "") {
        alert(MSG[1]);
        obj.username.focus();
        return false;
    }

    if ((obj.username.value.length < parseInt(sysAccountMinLen)) || (obj.username.value.length > parseInt(sysAccountMaxLen))) {
        alert(MSG[2]);
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
        alert(MSG[3]);
        obj.username.focus();
        return false;
    }

    if (underline > 1){
        alert(MSG[3]);
        obj.username.focus();
        return false;
    }

    if (minus > 1){
        alert(MSG[3]);
        obj.username.focus();
        return false;
    }

    /*
    * 檢查底線 and 減號 有無出現在 字尾
    */
    if( (data3.substring(data3.length-1,data3.length) == '_') || (data3.substring(data3.length-1,data3.length) == '-')){
        alert(MSG[3]);
        obj.username.focus();
        return false;
    }

    re = Account_format;
    if (!re.test(obj.username.value)) {
        alert(MSG[3]);
        obj.username.focus();
        return false;
    }

    if (obj.password.value == "") {
        alert(MSG[4]);
        obj.password.focus();
        return false;
    }

    if (obj.password.value.length < 6) {
        alert(MSG[5]);
        obj.password.focus();
        return false;
    }

    if (obj.repassword.value == "") {
        alert(MSG[6]);
        obj.repassword.focus();
        return false;
    }

    if (obj.password.value != obj.repassword.value) {
        alert(MSG[7]);
        obj.password.focus();
        return false;
    }
    
    var rtn_check = $("#check").val();
    if (rtn_check == 'top_shortPass' || rtn_check == 'top_badPass') {
        var rtn_check_msg = $("#check_msg").val();
        alert(rtn_check_msg);
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

    // 載入時預設焦點
    if (pwdFocus === 'true') {
        $('#password').focus();
    } else {
        $('#username').focus();
    }
    
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
});

window.onload = trimForm;