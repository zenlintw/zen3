var sessionTicket = '';
var sessionInterval = 60000;
var sessionTimer = "";
var counter = 0, total = 1;

/**
 * 啟動 Session
 **/
function sessionStart() {
    /*touchSession();*/
    setTimeout("touchSession()", 30000);
    sessionTimer = window.setInterval("touchSession()", sessionInterval);
}

/**
 * 停止 Session
 **/
function sessionStop() {
    if (sessionTimer != null) clearInterval(sessionTimer);
}

function touchSession() {
    var xml = "";
    var val = null;
    var res = 0;
    var xmlDoc;

    total = parseInt(total);
    if (isNaN(val) || (total == 0)) total = 1;
    res  = counter % total;
    if (res == 0) counter = 0;
    counter++;
    reqXML  = "<manifest>";
    reqXML += "<ticket>123</ticket>";
    reqXML += "<erase>" + res + "</erase>";
    reqXML += "</manifest>";

    try {
        $.ajax({
            type: 'POST',
            cache: false,
            url: "/online/session.php",
            data: reqXML,
            dataType: 'xml'
        }).done(function(resXML){
        });
    } catch (e) {
        // alert(e);
    }
}

$(document).ready(function () {
    sessionStart();
});