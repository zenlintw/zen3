// 檢查忘記密碼輸入資料
function checkData() {

    var re = null;
    var obj = document.getElementById("formForget");
    if (obj == null) return false;
    $('#username,#email').tooltip('destroy');

    if (obj.username.value == ""){
        $('#username')
            .attr('title', MSG[1])
            .tooltip('toggle');
        obj.username.focus();
        return false;
    }

    if (obj.email.value == "") {
        $('#email')
            .attr('title', MSG[1])
            .tooltip('toggle');
        obj.email.focus();
        return false;
    }

    /* 帳號檢查 */
    if ((obj.username.value.length < parseInt(sysAccountMinLen)) || (obj.username.value.length > parseInt(sysAccountMaxLen))) {
        $('#username')
            .attr('title', MSG[2])
            .tooltip('toggle');
        obj.username.focus();
        return false;
    }

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

    /* Email 格式檢查 */
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

window.onload = trimForm;