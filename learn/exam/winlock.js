/**
 * 視窗鎖定
 *
 * Usage: 在 wondow.onload 中執行 init_winlock()，如果有使用 DHTML，之後必須再執行一次
 *          在 window.onbeforeunload 中執行 free_winlock()
 */

var focusFlag   = false;
var pauseFlag    = false;
var detect_spot = null;
var hasSwitched = false;

/*
// for debug
function debug_winlock(e) {
    var obj = document.getElementById("msgWinlock");
    var target = (typeof window.addEventListener == "undefined") ? e.srcElement : e.target;
    obj.innerHTML += "<div>" + (target ? target.tagName : "null") + ": " + e.type + "</div>";
}

function debug_box_winlock() {
    var node = document.createElement("div");
    document.body.appendChild(node);
    node.setAttribute("id", "msgWinlock")
    node.style.position = "absolute";
    node.style.top = "0px";
    node.style.left = "0px";
    node.style.zIndex = "1000";
    node.style.border = "1px solid #FF8";
    node.style.width = "300px";
}
*/

function free_winlock()
{
    if (detect_spot != null) {
        clearTimeout(detect_spot);
        detect_spot = null;
    }
}

function alert_warning_window()
{
    $( "#dialog-message" ).dialog({
      title: window_warning_title,
      modal: true,
      resizable: false,
      draggable: false,
      width: dia_width,
      buttons: [{
        text: button_ok_text,
        click: function() {
          $( this ).dialog( "close" );
        }
      }]
    });
}

function init_winlock()
{
    if (typeof winlock_warning == 'undefined') winlock_warning = "Please don't switch the window.";

    var browser = 'ie';
    if(navigator.userAgent.indexOf('MSIE')>0){
        browser = 'ie';
    }else if(navigator.userAgent.indexOf('Firefox')>0){
        browser = 'ff';
    }else if(navigator.userAgent.indexOf('Chrome')>0){
        browser = 'chr';
    }else if(navigator.userAgent.indexOf('Safari')>0){
        browser = 'sf';
    }else{
        browser = 'op';
    }
    
    if (typeof window.addEventListener == "undefined") {
        /*
        debug_box_winlock();
        window.attachEvent("onfocus", function (e) { debug_winlock(e); });
        window.attachEvent("onblur", function (e) { debug_winlock(e); });
        document.attachEvent("onfocusout", function (e) { debug_winlock(e); });
        document.attachEvent("onfocusin", function (e) { debug_winlock(e); });
        */

        document.attachEvent("onfocusout", function (e) {
            focusFlag = true;
            detect_spot = setTimeout(function () {
                if (!focusFlag) return;
                window.focus();
                if (hasSwitched || locktype=='lock2') {
                    free_winlock();
                    // examOver();
                    // 改為切換視窗用的function by Small 2012/02/03
                    examChgWinOver();
                    return;
                }
                alert_warning_window();
                hasSwitched = true;
            }, 500);
        });
        document.attachEvent("onfocusin", function (e) {
            focusFlag = false;
            free_winlock();
        });
    } else {
        window.addEventListener("blur", function (e) {
            if (focusFlag) return;
            focusFlag = true;

            detect_spot = setTimeout(function () {
                window.focus();
                if (hasSwitched || locktype=='lock2') {
                    free_winlock();
                    // examOver();
                    // 改為切換視窗用的function by Small 2012/02/03
                    examChgWinOver();
                    return;
                }
                alert_warning_window();
                focusFlag = false;
                hasSwitched = true;
            }, 500);
        }, false);
        window.addEventListener("focus", function (e) {
            focusFlag = false;
            free_winlock();
        }, false);
    }
}
/*
function xFocus()
{
    focusFlag = false;
}

function xBlur()
{
    focusFlag = true;
}

function detect_switch()
{
    if (pauseFlag) return;
    if (typeof(winlock_warning) == 'undefined') winlock_warning = "Please don't switch the window.";

    if (focusFlag)
    {
        focusFlag = false;
        window.focus();

        if (hasSwitched || !confirm(winlock_warning))
        {
            free_winlock();
            examOver();
            return;
        }

        hasSwitched = true;
    }
    if (window.screenTop != myTop || window.screenLeft != myLeft) window.moveTo(0, 0);
}

function init_winlock()
{
    var hasFOCUS  = /^(a|acronym|address|applet|area|b|bdo|big|blockquote|button|caption|center|cite|custom|dd|del|dfn|dir|div|dl|dt|em|embed|fieldset|font|form|frame|frameset|hn|hr|i|iframe|img|input|ins|isindex|kbd|label|legend|li|listing|marquee|menu|object|ol|p|plaintext|pre|q|rt|ruby|s|samp|select|small|span|strike|strong|sub|sup|table|tr|th|td|textarea|u|ul|var|xmp)$/i;
    var isEMBED   = /^(applet|embed|object)$/i;
    var isRADIO   = /^(radio|checkbox)$/i;
    var x, xx, i=0;
    var allElements = document.getElementsByTagName('*');
    for(x in allElements)
    {
        if (allElements[x].tagName != null && hasFOCUS.test(allElements[x].tagName))
        {
            if (isEMBED.test(allElements[x].tagName))
            {
                allElements[x].onmousemove = allElements[x].onmouseover = function(){pauseFlag = true;};
                allElements[x].onmouseout  = function(){window.focus(); focusFlag = false; pauseFlag = false;};
            }
            else
            {
                allElements[x].onfocus = xFocus;
                allElements[x].onblur  = xBlur;
            }
        }
    }

    var inputs = document.getElementsByTagName('input');
    for(var i=0; i<inputs.length; i++)
    {
        if (isRADIO.test(inputs[i].type))
        {
            inputs[i].onfocus = xFocus;
            inputs[i].onblur  = xBlur;
        }
    }

    window.onfocus = xFocus;
    window.onblur  = xBlur;
    stop_focusFlag = false;
    if (detect_spot == null) detect_spot = setInterval('detect_switch()', 500);
}

function free_winlock()
{
    clearInterval(detect_spot);
    detect_spot = null;
}
*/