/**
 * 取得另外的視窗
 * @return Object 另外的視窗 (other frame)
 **/
function getTarget() {
    var obj = null;
    // alert (this.name);
    switch (this.name) {
        case "s_main":
            obj = parent.s_sysbar;
            break;
        case "c_main":
            obj = parent.c_sysbar;
            break;
        case "main":
            obj = parent.sysbar;
            break;
        default:
            obj = window;
            break;
    }
    return obj;
}

/**
 * 進入聊天室
 * @param string val : 聊天室編號
 **/
function goChat(val) {
    if (window.console) {console.log('chat_list.js goChat()');}
    
    var obj = getTarget();
    if ((obj == null) || (typeof obj != "object")) return false;
    // mooc/public/js/mooc_header.js
    if (typeof(obj.goChatroom) == "function") obj.goChatroom(val);
}

function goJoinnet(val) {
    var obj = document.getElementById("ifrm_joinnet");
    var url = "/webmeeting/joinmeeting.php";
    if (obj.src == url) {
        obj.contentWindow.location.reload();
    } else {
        obj.src = url;
    }
}

function goBreeze(scoid, urlpath) {
    var options = "toolbar=0,status=0,location=0,resizable=1";
    var url = "/breeze/JoinMeeting.php?scoid=" + scoid + "&urlpath=" + urlpath;
    var win = open(url, "", options);
}

/**
 * change page
 * @param integer n : action type or page number
 * @return
 **/
function go_page(n) {
    var obj = document.getElementById("actFm");
    if ((typeof(obj) != "object") || (obj == null)) return '';
    switch (n) {
        case -1: // 第一頁
            obj.page.value = 1;
            break;
        case -2: // 前一頁
            obj.page.value = parseInt(obj.page.value) - 1;
            if (parseInt(obj.page.value) == 0) obj.page.value = 1;
            break;
        case -3: // 後一頁
            obj.page.value = parseInt(obj.page.value) + 1;
            break;
        case -4: // 最末頁
            obj.page.value = parseInt(total_page);
            break;
        default: // 指定某頁
            obj.page.value = parseInt(n);
            break;
    }
    obj.submit();
}

function trim(val) {
    var re = /\s/g;
    val = val.replace(re, '');
    return val;
}

function sortBy(val) {
    var ta = new Array('',
        'admin', 'open', 'close',
        'status', 'media', 'visible'
    );
    var re = /asc/ig;

    var obj = document.getElementById("actFm");
    if ((typeof(obj) != "object") || (obj == null)) return '';

    if (trim(obj.sortby.value) == ta[val]) {
        obj.order.value = (re.test(obj.order.value)) ? 'desc' : 'asc';
    }
    obj.sortby.value = ta[val];
    obj.submit();
}

window.onload = function() {
//    document.getElementById("toolbar2").innerHTML = document.getElementById("toolbar1").innerHTML;
    
    if (detectIE() === 13) {
        $('.title-bar .subject td').css('border-radius', '0 0 0 0');
    }
};