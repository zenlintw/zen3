function checkLogin()
{
    var node = document.getElementById("loginForm");
    if (node == null) return false;

    if (node.username.value == "") {
        $('#username')
            .attr('title', MSG_NEED_USERNAME)
            .tooltip('toggle');
        node.username.focus();
        return false;
    }

    if (node.password.value == "") {
        $('#password')
            .attr('title', MSG_NEED_PASSWORD)
            .tooltip('toggle');
        node.password.focus();
        return false;
    }

    if (node.captcha && node.captcha.value == "") {
        $('#captcha')
            .attr('title', MSG_NEED_CAPTCHA)
            .tooltip('toggle');
        node.captcha.focus();
        return false;
    }

    var pwdmask = "********************************";
    var md5key  = MD5(node.password.value);
    var cypkey  = md5key.substr(0,4) + node.login_key.value.substr(0,4);
    node.encrypt_pwd.value = stringToBase64(des(cypkey, node.password.value, 1));
    node.password.value    = pwdmask.substr(0,node.password.value.length);

    return true;
}