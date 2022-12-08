/**
 * 聊天室 JavaScript
 *
 * @since   2003/11/27
 * @author  ShenTing Lin
 * @version $Id: chat.js,v 1.1 2010/02/24 02:39:05 saly Exp $
 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
 **/
var isIE = false,
    isMZ = false;
var xmlDocs = null,
    xmlVars = null,
    xmlHttp = null;
var curList = 0;
var crOrder = false,
    crScroll = true,
    crLine = true,
    crSend = false,
    crMute = false;
var crConts = null,
    crInOut = null,
    userLst = null;
var crHost = "";
var defSubW = 0,
    defSubH = 0;


// 黑名單 (deny user list)
var denyLst = new Object();

/**
 * 檢查瀏覽器的種類
 **/
function chkBrowser() {
    var re = new RegExp("MSIE", "ig");
    if (re.test(navigator.userAgent)) {
        isIE = true;
    }

    re = new RegExp("Gecko", "ig");
    if (re.test(navigator.userAgent)) {
        isMZ = true;
    }
}

/**
 * 取得 Node 底下某一個 TAG 的資料
 * @pram node : 要取得資料的 node
 * @pram tag : 要取得哪一個 TAG 的資料
 **/
function getNodeValue(node, tag) {
    var childs = null;
    if ((typeof(node) != "object") || (node == null))
        return "";
    childs = node.getElementsByTagName(tag);
    if ((childs == null) || (childs.length <= 0)) return "";
    if (childs[0].hasChildNodes()) {
        return childs[0].firstChild.data;
    } else {
        return "";
    }
}

/**
 * 刪除字串的空白節點
 * @param string val : 要清除的字串
 * @return string result : 清除後的字串
 **/
function trim(val) {
    var re = /[\r\n\t]*/ig;
    return val.replace(re, "");
}

/**
 * 調整視窗至最佳大小
 **/
function winResize() {
    chat_style();
}

/**
 * 變更圖示
 * @param object obj  : 物件
 * @param string icon : 圖示的來源
 **/
function chgImg(obj, icon) {
    if ((typeof(obj) != "object") || (obj == null)) return true;
    obj.src = theme + icon;
}

/**
 * 加入粗體，斜體與底線的 Tag
 * @param string sty : 字體的樣式
 *     b : 粗體
 *     i : 斜體
 *     u : 底線
 **/
function chgFontStyle(sty) {
    var obj = null;
    var str1 = "",
        str2 = "",
        str3 = "",
        txt = "";
    var bs = 0;
    be = 0;

    if (isMZ) {
        obj = document.getElementById("chatInput");
        bs = obj.selectionStart;
        be = obj.selectionEnd;
        if (bs == obj.value.length) bs = 0;
        str1 = obj.value.substring(0, bs);
        str2 = obj.value.substring(bs, be);
        str3 = obj.value.substring(be, obj.value.length);
    }
    if (isIE) {
        obj = document.selection.createRange();
        str2 = obj.text;
        if (str2 == "") str2 = document.getElementById("chatInput").value;
    }
    switch (sty) {
        case "b":
            txt = str1 + "<b>" + str2 + "</b>" + str3;
            break;
        case "i":
            txt = str1 + "<i>" + str2 + "</i>" + str3;
            break;
        case "u":
            txt = str1 + "<u>" + str2 + "</u>" + str3;
            break;
        default:
    }

    if (isMZ) {
        obj.value = txt;
        obj.selectionStart = bs;
        obj.selectionEnd = be + 7;
        obj.focus();
    }
    if (isIE) {
        if (obj.text == "") {
            document.getElementById("chatInput").value = txt;
        } else {
            obj.text = txt;
        }
        document.getElementById("chatInput").focus();
    }
}

/**
 * 變更聊天室的樣式
 **/
function chat_style() {
    if (window.console) {console.log('chat_style');}
    
    if (isPhoneDevice == 'true') return;
    var objCont = document.getElementById("tabCont");
    var objUser = document.getElementById("divUser");
    var objRoom = document.getElementById("divRoom");
    var resizeW = false,
        resizeH = false;
    var obj = null;
    var v = 0,
        oW = 0,
        oH = 0;
    var nodes = document.getElementsByTagName("html");
    if (nodes && nodes.length > 0) {
        oW = isIE ? parseInt(nodes[0].offsetWidth) : parseInt(window.innerWidth);
        oH = isIE ? parseInt(nodes[0].offsetHeight) : parseInt(window.innerHeight);
    }
    // 調整視窗大小至 640x480 的大小 (Begin)
    try {
        if ((oW - defSubW) < 630) {
            oW = 686 + defSubW;
            resizeW = true;
        }
        if (userPref["inout"]) {
            if ((oH - defSubH) < 266) {
                oH = 322 + defSubH;
                resizeH = true;
            }
        } else {
            if ((oH - defSubH) < 206) {
                oH = 262 + defSubH;
                resizeH = true;
            }
        }
        if (resizeH && !resizeW) oW += 10;
        if (resizeW && !resizeH) oH += 10;
        if (resizeW || resizeH) {
            if (typeof(window.dialogWidth) == "undefined") {
                window.resizeTo(oW, oH);
            } else {
                window.dialogWidth = oW + "px";
                window.dialogHeight = oH + "px";
            }
        }
    } catch (e) {} finally {
        resizeW = false;
        resizeH = false;
    }
    // 調整視窗大小至 640x480 的大小 (End)

    if (userPref["inout"]) {
        if (objCont != null) objCont.style.top = "66px";
        // if (crConts != null) crConts.style.height = "366px";
        // if (objUser != null) objUser.style.height = "214px";
        // if (objRoom != null) objRoom.style.height = "214px";
        if (crConts != null) {
            crConts.style.width = (oW - defSubW - 110) + "px";
            crConts.style.height = (oH - defSubH - 114) + "px";
        }
        // #48379 [Sarafi]教室-學習互動區-線上討論，調整討論區彈出視窗大小，目前有遮到輸入區域：修改計算原則
        if (objUser != null) objUser.style.height = (oH - defSubH - 376) + "px";
        if (objRoom != null) objRoom.style.height = (oH - defSubH - 266) + "px";
    } else {
        if (objCont != null) objCont.style.top = "6px";
        // if (crConts != null) crConts.style.height = "426px";
        // if (objUser != null) objUser.style.height = "274px";
        // if (objRoom != null) objRoom.style.height = "274px";
        if (crConts != null) {
            crConts.style.width = (oW - defSubW - 105) + "px";
            crConts.style.height = (oH - defSubH - 54) + "px";
        }
        // #48379 [Sarafi]教室-學習互動區-線上討論，調整討論區彈出視窗大小，目前有遮到輸入區域：修改計算原則
        if (objUser != null) objUser.style.height = (oH - defSubH - 306) + "px";
        if (objRoom != null) objRoom.style.height = (oH - defSubH - 206) + "px";
        obj = document.getElementById("divTools");
        if (obj != null) {
            obj.style.top = (oH - defSubH - 24) + "px";
            if (window.console) {console.log('userPref obj.style.top', obj.style.top);}
            obj.style.width = oW + "px";
        }
    }
    obj = document.getElementById("chatInOut");
    if (obj != null) {
        obj.style.width = (oW - defSubW - 104) + "px";
    }

    obj = document.getElementById("divTools");
    if (obj != null) {
        // #48379 [Sarafi]教室-學習互動區-線上討論，調整討論區彈出視窗大小，目前有遮到輸入區域：修改計算原則
        var a = parseInt(chatInOut.style.height.substr(0, (chatInOut.style.height).length - 2)) * 2;
        var b = parseInt(chatCont.style.height.substr(0, (chatCont.style.height).length - 2));
        if (a + b + 60 > oH) {
            if ((oH - a - b) < 60) {
                b = oH - a - 60;
            } else {
                b = 246;
            }
            chatCont.style.height = b + 'px';
        }
        if (window.console) {console.log('divTools oH', oH);}
        if (oH < 480) {
            obj.style.top = (a + b + 15) + "px";
        } else {
            obj.style.top = (a + b + 15 + 434) + "px";
        }
        if (window.console) {console.log('divTools obj.style.top', obj.style.top);}
        obj.style.width = oW + "px";
    }
    scrollContent();
}

/**
 * 開啟聊天室設定視窗 (open setting window)
 **/
var winSet = null;

function chat_set() {
    var st = "width=440,height=440,toolbar=0,scrollbars=1,location=0,status=0,menubar=0,resizable=1,dependent=1";
    if ((winSet != null) && !winSet.closed) {
        winSet.focus();
    } else {
        winSet = window.open(nowPath + "chat_setting.php", "ChatSet", st);
    }
}

function chat_help() {
    var node = null;

    node = document.createElement("div");
    node.innerHTML = MSG_CHAT_HELP;
    node.style.paddingTop = "1px";
    node.style.paddingLeft = "24px";
    node.style.textIndent = "-20px";
    if (crOrder) {
        if (crConts.firstChild == null) {
            crConts.appendChild(node);
        } else {
            crConts.insertBefore(node, crConts.firstChild);
        }
    } else {
        crConts.appendChild(node);
    }
    timerScroll = setTimeout('scrollContent()', 500); // 由於 IE5 的關係，所以 delay 一下才捲動 (IE5....)
}
// ////////////////////////////////////////////////////////////////////////////
/**
 * 傳送內容
 **/
function send() {
    var obj = null,
        pnode = null,
        tnode = null;
    var txt = "",
        recr = "",
        recn = "";
    var tone = 0;

    if (crMute) return false;
    obj = document.getElementById("chatTone");
    if (obj != null) tone = parseInt(obj.value);

    obj = document.getElementById("selUser");
    if (obj != null) recr = (obj.value == 0) ? "" : obj.value;

    txt = "<manifest>"
    txt += "<ticket></ticket>";
    txt += "<reciver>" + recr + "</reciver>";
    txt += "<dsc_times>" + dsc_times + "</dsc_times>";
    txt += "<tone>" + tone + "</tone>";
    txt += "</manifest>";
    if (!xmlVars.loadXML(txt)) {
        xmlVars = null;
        return false;
    }

    recn = (recr == "") ? " " : userLst[recr][0];
    pnode = xmlVars.createElement("reciver_name");
    tnode = xmlVars.createTextNode(recn);
    pnode.appendChild(tnode);
    xmlVars.documentElement.appendChild(pnode);

    txt = "";
    obj = document.getElementById("chatInput");
    if (obj != null) {
        txt = obj.value;
        if (txt == "") return false;
        obj.value = "";
    }
    pnode = xmlVars.createElement("content");
    tnode = xmlVars.createTextNode(txt);
    pnode.appendChild(tnode);
    xmlVars.documentElement.appendChild(pnode);

    xmlHttp = XmlHttp.create();
    xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == 4) {
            dsc_times++;
            // alert(xmlHttp.responseText);
            // if (!xmlDocs.loadXML(xmlHttp.responseText)) {
            //	xmlDocs.loadXML(txt);
            // }
            if (xmlHttp.responseText != "") eval(xmlHttp.responseText);
            crSend = false;
            session();
            crSend = true;
        }
    };
    xmlHttp.open("POST", nowPath + "chat_send.php", true);
    xmlHttp.send(xmlVars);
}

/**
 * 說悄悄話
 * @param string username : 要跟誰說話 (talk who)
 **/
function talk(username) {
    var obj = document.getElementById("fmTalk");
    var st = "width=440,height=440,toolbar=0,scrollbars=1,location=0,status=0,menubar=0,resizable=1,dependent=1";
    if (username == "") return false;
    if ((typeof(obj) != "object") || (obj == null)) return false;
    winSet = window.open("about:blank", "talkWin", st);
    obj.reciver.value = username;
    obj.reciver_name.value = userLst[username][0];
    obj.submit();
}

/**
 * 跟 Server 同步被禁止發言的清單
 **/
function syncMute(voice, lst) {
    var obj = null,
        node = null;
    var ary = new Array();

    if (lst == "") return false;
    ary = lst.split(",");
    for (var i = 0; i < ary.length; i++) {
        obj = document.getElementById("U_" + ary[i]);
        if (obj != null) obj.checked = (voice == "deny");
    }
}

/**
 * 禁止發言
 **/
function mute() {
    var obj = null,
        nodes = null;
    var aryA = new Array(),
        aryD = new Array();
    var txt = "",
        txtA = "",
        txtD = "";

    obj = document.getElementById("tableUser");
    if ((typeof(obj) != "object") || (obj == null)) return false;
    nodes = obj.getElementsByTagName("input");
    for (var i = 0; i < nodes.length; i++) {
        if (nodes[i].type != "checkbox") continue;
        if (nodes[i].checked)
            aryD[aryD.length] = nodes[i].value;
        else
            aryA[aryA.length] = nodes[i].value;
    }
    txtA = aryA.toString();
    txtD = aryD.toString();
    if ((txtA == "") && (txtD == "")) return false;

    txt = "<manifest>";
    txt += "<allow>" + txtA + "</allow>";
    txt += "<deny>" + txtD + "</deny>";
    txt += "</manifest>";
    if (!xmlVars.loadXML(txt)) {
        xmlVars = null;
        return false;
    }

    xmlHttp = XmlHttp.create();
    xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == 4) {
            alert(xmlHttp.responseText);
        }
    };
    xmlHttp.open("POST", nowPath + "chat_mute.php", true);
    xmlHttp.send(xmlVars);
}

/**
 * 我要發言
 **/
function say() {
    txt = "<manifest>";
    txt += "<ticket></ticket>";
    txt += "</manifest>";
    if (!xmlVars.loadXML(txt)) {
        xmlVars = null;
        return false;
    }

    xmlHttp = XmlHttp.create();
    xmlHttp.open("POST", nowPath + "chat_say.php", true);
    xmlHttp.send(xmlVars);
    // alert(xmlHttp.responseText);
}

/**
 * 建立使用者列表
 * @param boolean isHost : 是否為主持人
 **/
function buildUserList(isHost) {
    var objSel = null,
        objTab = null,
        oOtion = null,
        oRow = null,
        oCell = null;
    var newUser = null,
        obj = null;
    var val = "",
        col = "cssTrOdd",
        icon = "";
    var idx = 0;
    var isLost = true;

    newUser = new Object();
    for (var i in userLst) {
        newUser[i] = userLst[i];
        if (i == mySelf) isLost = false;
    }
    if (isLost) {
        if (timer) clearInterval(timer);
        isLost = false;
        alert(MSG_LOST_COECT);
        window.onunload = function() {};
        logout(false);
        window.close();
    }

    // 成員下拉選單 (User select list)
    // 顯示成員列表 (display user list)
    objSel = document.getElementById("selUser");
    objTab = document.getElementById("tableUser");
    if ((typeof(objSel) != "object") || (objSel == null) ||
        (typeof(objTab) != "object") || (objTab == null))
        return false;

    // 移除已經離線的帳號，並列出新上線的帳號 (remove offline user and get new online user)
    newUser[mySelf] = null;
    for (var i = objSel.length - 1; i > 0; i--) {
        val = objSel.options[i].value;
        if ((typeof(newUser[val]) == "undefined") || (newUser[val] == null)) {
            objSel.remove(i);
            objTab.deleteRow(i);
            denyLst[val] == null;
        } else {
            newUser[val] = null;
        }
    }

    icon = '<img width="28" height="17" align="absmiddle" border="0" src="' + theme + "/icon_airplane.gif" + '">';
    // 顯示新上線的帳號 (display new online user)
    for (var i in newUser) {
        if ((typeof(newUser[i]) == "undefined") || (newUser[i] == null)) continue;
        idx = objTab.rows.length;

        denyLst[i] == true;

        // 下拉選單 (select list)
        if (i != mySelf) {
            oOption = document.createElement("option");
            objSel.options.add(oOption);
            oOption.text = newUser[i][0] + " (" + i + ")";
            oOption.value = i;
        }

        // 表格 (table)
        // 顯示成員列表 (display user list)
        oRow = objTab.insertRow(-1);

        oCell = oRow.insertCell(-1);
        oCell.width = "120";
        if (i != mySelf) {
            oCell.innerHTML = '<a href="javascript:void(null);" onclick="toUser(\'' + i + '\'); event.cancelBubble=true;" class="cssAnchor">' + newUser[i][0] + " (" + i + ")</a>";
        } else {
            oCell.innerHTML = newUser[i][0] + " (" + i + ")";
        }
        if (crHost == i) {
            oCell.innerHTML = "* " + oCell.innerHTML;
            oCell.style.color = "#FF0000";
        }

        oCell = oRow.insertCell(-1);
        oCell.width = "30";
        oCell.innerHTML = (i == mySelf) ? "&nbsp;" : '<a href="javascript:void(null)" onclick="talk(\'' + i + '\');">' + icon + '</a>';

        oCell = oRow.insertCell(-1);
        oCell.width = "30";
        if (!isHost || (i == mySelf)) {
            oCell.innerHTML = "&nbsp;";
        } else {
            oCell.innerHTML = (newUser[i][2] == "allow") ? '<input type="checkbox" value="' + i + '" id="U_' + i + '">' : '<input type="checkbox" value="' + i + '" id="U_' + i + '" checked="checked">';
        }

        oCell = oRow.insertCell(-1);
        oCell.width = "17";
        oCell.innerHTML = "&nbsp;";

    }

    // 更新自己的資訊 (update myself data) (Begin)
    objTab.deleteRow(0);
    oRow = objTab.insertRow(0);

    oCell = oRow.insertCell(-1);
    oCell.width = "120";
    oCell.innerHTML = myName + " (" + mySelf + ")";
    if (isHost) {
        oCell.innerHTML = "* " + oCell.innerHTML;
        oCell.style.color = "#FF0000";
    }

    oCell = oRow.insertCell(-1);
    oCell.width = "77";
    oCell.colSpan = "3";
    oCell.innerHTML = "&nbsp;";

    for (var i = 0; i < objTab.rows.length; i++) {
        col = (col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
        objTab.rows[i].className = col;
    }
    // 禁止發言 (Mute)
    if (typeof(userLst[mySelf]) == "undefined") {
        crMute = false;
    } else {
        crMute = (userLst[mySelf][2] == "deny");
        if (crHost == mySelf) crMute = false;
    }
    obj = document.getElementById("chatSend");
    if (obj != null) obj.style.display = crMute ? "none" : "";
    obj = document.getElementById("chatRequest");
    if (obj != null) obj.style.display = crMute ? "" : "none";
    // 更新自己的資訊 (update myself data) (End)
    document.getElementById("mLstCnt").innerHTML = ' (' + objTab.rows.length + ')';
}

/**
 * 取得人員列表
 **/
function getUserList() {
    var nodes = null,
        objSel = null,
        objTab = null;
    var txt = "",
        user = "",
        host = "";
    var ary = new Array();
    var isHost = false;

    host = getNodeValue(xmlDocs.documentElement, "host");
    // 切換主持人時，重新更新線上使用者 (change host, rebuild user list) (Begin)
    if (crHost != host) {
        objSel = document.getElementById("selUser");
        objTab = document.getElementById("tableUser");
        if ((objSel != null) && (objTab != null)) {
            for (var i = objSel.length - 1; i > 0; i--) {
                objSel.remove(i);
                objTab.deleteRow(i);
            }
        }
        crHost = host;
    }
    // 切換主持人時，重新更新線上使用者 (change host, rebuild user list) (End)
    isHost = (crHost == mySelf);
    obj = document.getElementById("thMute");
    if (obj != null) obj.style.display = isHost ? "" : "none";
    userLst = new Object();
    nodes = xmlDocs.getElementsByTagName("user");
    for (var i = 0; i < nodes.length; i++) {
        if (!nodes[i].hasChildNodes()) continue;

        // 建立目前待在聊天室的成員列表 (Create user list)
        ary = new Array();
        user = getNodeValue(nodes[i], "username");
        ary[0] = getNodeValue(nodes[i], "realname");
        ary[1] = getNodeValue(nodes[i], "is_host");
        ary[2] = getNodeValue(nodes[i], "say");
        userLst[user] = ary;
    }
    buildUserList(isHost);
}

/**
 * 取得討論室列表
 **/
function getRoomList() {
    var nodes = null,
        attr = null,
        obj = null,
        chg = null,
        inr = null;
    var txt = "",
        str = "",
        col = "cssTrOdd";
    var bol = false;

    if (xmlDocs == null) return false;
    obj = document.getElementById("divRoom");
    if ((typeof(obj) != "object") || (obj == null)) return false;

    nodes = xmlDocs.selectNodes("//rooms/room");
    txt = '<table width="100%" border="0" cellspacing="0" celpadding="0">';
    for (var i = 0; i < nodes.length; i++) {
        col = (col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
        str = (nodes[i].hasChildNodes()) ? nodes[i].firstChild.nodeValue : "&nbsp;";
        txt += '<tr class="' + col + '">';

        chg = nodes[i].getAttribute("change");
        inr = nodes[i].getAttribute("in");
        if ((inr != null) && (inr == "true")) {
            txt += '<td class="cssTd" style="color: #FF0000">';
            txt += '*' + str;
            document.getElementById("chatName").innerHTML = str;
        } else if ((chg != null) && (chg == "deny")) {
            txt += '<td class="cssTd">';
            txt += str;
        } else {
            attr = nodes[i].getAttribute("id");
            txt += '<td class="cssTd" onclick="room_change(\'' + attr + '\');">';
            txt += '<a href="javascript:void(null);" class="cssAnchor">' + str + '</a>';
        }
        txt += '</td>';
        txt += '</tr>';
    }
    txt += '</table>';
    obj.innerHTML = txt;
    document.getElementById("rLstCnt").innerHTML = " (" + nodes.length + ")";
}

/**
 * 定時更新資料
 **/
function session() {
    var txt = "";
    var node = null;

    if (crSend) { // 延後 10 秒更新資料 (Delay 10s, update chat content)
        crSend = false;
        return false;
    }

    txt = "<manifest>";
    txt += "<ticket></ticket>";
    txt += "<line>" + curList + "</line>";
    txt += "</manifest>";

    if (!xmlVars.loadXML(txt)) {
        xmlVars = null;
        return false;
    }

    xmlHttp = XmlHttp.create();
    xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == 4) {
            // alert(xmlHttp.responseText);
            if (!xmlDocs.loadXML(xmlHttp.responseText)) return false;

            // 人員列表 (user list)
            getUserList();
            // 討論室列表 (Room list)
            getRoomList();
            // 聊天內容 (chat content)
            if (crOrder && !crScroll) return true; // 由大到小顯示，並且暫停畫面，則不讀取更新後的資料 (Order by Desc and pause scroll)
            if (isMZ) {
                // 由於 Mozilla 在 XML DOM 上不明的原因，導致無法取得完整的資料
                // 所以直接將文字部分如此處理
                node = xmlHttp.responseText.split("<content>");
                if (node.length > 1) {
                    txt = node[1].replace("</content></manifest>", "");
                    txt = txt.replace(/&lt;/ig, "<");
                    txt = txt.replace(/&gt;/ig, ">");
                    txt = txt.replace(/&quot;/ig, '"');
                }
            } else {
                txt = getNodeValue(xmlDocs.documentElement, "content");
            }
            curList = getNodeValue(xmlDocs.documentElement, "seq");
            dispatch(txt);
        }
    };
    try {
        xmlHttp.open("POST", nowPath + "chat_session.php", true);
        xmlHttp.send(xmlVars);
    } catch (ex) {
        if (isMZ) window.location.reload();
    }
}

function room_change(val) {
    logout(false);
    xmlVars.loadXML("<manifest><chat_id>" + val + "</chat_id></manifest>");
    xmlHttp = XmlHttp.create();
    xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == 4) {
            curList = 0;
            crCnts = 1;
            crConts.innerHTML = "";
            crInOut.innerHTML = "";
            session();
        }
    };
    xmlHttp.open("POST", nowPath + "chat_change.php", true);
    xmlHttp.send(xmlVars);
}

function logout(val) {
    if (window.console) {console.log('chat.js logout');}
    
    if ((winSet != null) && !winSet.closed) winSet.close();
    if ((winFile != null) && !winFile.closed) winFile.close();

    if (!xmlVars.loadXML("<manifest><exit>" + userPref["exit"] + "</exit></manifest>")) {
        xmlVars = null;
        xmlVars = XmlDocument.create();
        xmlVars.loadXML("<manifest><exit></exit></manifest>");
    }
  
    xmlHttp.open("POST", nowPath + "chat_logout.php", false);
    xmlHttp.send(xmlVars);
    if (window.console) {console.log('val', val, xmlHttp.responseText);}
    if (val && xmlHttp.responseText !== '' && xmlHttp.responseText !== MSG_LOGOUT_OK) alert(xmlHttp.responseText);

    // 文字討論室+直播 離開討論室按鈕
    if (parent.opener) {
        parent.opener.parent.s_main.location.reload();
    } else {
        opener.parent.s_main.location.reload();
    }
}
// ////////////////////////////////////////////////////////////////////////////
/**
 * 解析檔案格式的資料
 * @param string val : XML 的資料
 * @return string : 連結
 **/
function parseFileXML(val) {
    var res = false;
    var str = "",
        rname = "",
        fname = "",
        fsize = "",
        ename = "",
        notes = "";
    var nodes = null;
    var ary = new Array();
    res = xmlVars.loadXML(val);
    if (!res) return false;

    notes = getNodeValue(xmlVars.documentElement, "note");
    str += MSG_FILE_SHARE;
    str += '<div class="cssChatFile">';
    if (notes != "") {
        str += MSG_FILE_NOTE;
        str += notes;
        str += "<br />";
    }

    nodes = xmlVars.getElementsByTagName("file");
    for (var i = 0; i < nodes.length; i++) {
        rname = getNodeValue(nodes[i], "real_name");
        fname = getNodeValue(nodes[i], "file_name");
        ename = getNodeValue(nodes[i], "rawe_name");
        fsize = getNodeValue(nodes[i], "file_size");

        str += '<a href="/learn/chat/chat_file.php?real=' + rname + '&name=' + ename + '" target="_blank" class="cssAnchor">';
        str += '<img width="20" height="20" border="0" align="absmiddle" src="' + theme + '/icon_file.gif">&nbsp;';
        str += fname + " (" + fsize + ") ";
        str += "</a>";
        str += "<br />";
    }
    str += "</div>";

    return str;
}

function getFile(val1, val2) {
    var obj = document.getElementById("fmFile");
    if (obj == null) return false;
    obj.filename.value = val1;
    obj.realname.value = val2;
    obj.submit();
}

function toUser(val) {
    var obj = document.getElementById("selUser");
    if (obj) {
        if (val == "") {
            obj.selectedIndex = 0;
        } else {
            for (var i = 0; i < obj.length; i++) {
                if (obj[i].value == val) {
                    obj.selectedIndex = i;
                    break;
                }
            }
        }
    }
    obj = document.getElementById("chatInput");
    if (obj != null) obj.focus();
}

/**
 * 上線或離線紀錄
 * @param array ary : 單一筆訊息
 **/
function toInOut(ary) {
    var node = null;
    var txt = "";

    node = document.createElement("div");
    switch (parseInt(ary[0])) {
        case -2:
        case -1:
            txt = "*** " + MSG_LOGOUT + ary[2] + " (" + ary[1] + ")" + MSG_EXIT + "(" + ary[5] + ") ***";
            node.style.color = "#FF0000";
            break;
        case 0:
            txt = "*** " + MSG_LOGOUT + ary[2] + " (" + ary[1] + ")" + MSG_EXIT + "(" + ary[5] + ") ***";
            node.style.color = "#00FF00";
            break;
        case 1:
            txt = "*** " + MSG_LOGIN + ary[2] + " (" + ary[1] + ")" + MSG_ENTER + "(" + ary[5] + ") ***";
            node.style.color = "#0000FF";
            break;
        case 5:
        case 6:
            txt = "&lt;&lt;&lt; " + ary[4] + " (" + ary[3] + ") " + ary[8] + " &gt;&gt;&gt;";
            node.style.color = "#FF0000";
            break;
        default:
    }
    node.innerHTML = txt;

    if (crOrder) {
        if (crInOut.firstChild == null) {
            crInOut.appendChild(node);
        } else {
            crInOut.insertBefore(node, crInOut.firstChild);
        }
    } else {
        crInOut.appendChild(node);
    }
}

/**
 * 聊天內容
 * @param array ary : 單一筆訊息
 **/
var crCnts = 1;

function toCont(ary) {
    var node = null;
    var txt = "",
        str = "";

    if (denyLst[ary[1]]) {
        crCnts++;
        return false;
    }

    node = document.createElement("div");
    txt = crLine ? crCnts + ". " : "";

    switch (parseInt(ary[0])) {
        case 2:
        case 3:
            if (ary[1] != mySelf) {
                txt += '<a href="javascript:void(null);" onclick="toUser(\'' + ary[1] + '\')" class="cssAnchor">' + ary[2] + " (" + ary[1] + ")</a>";
            } else {
                txt += ary[2] + " (" + ary[1] + ")";
            }
            if (ary[3] != mySelf) {
                txt += (ary[3] != "") ? " --&gt; " + '<a href="javascript:void(null);" onclick="toUser(\'' + ary[3] + '\')" class="cssAnchor">' + ary[4] + " (" + ary[3] + ")</a> : " : " : ";
            } else {
                txt += (ary[3] != "") ? " --&gt; " + ary[4] + " (" + ary[3] + ") : " : " : ";
            }
            txt += (ary[6] != "") ? "&lt;" + ary[6] + "&gt;" : "";

            txt += (parseInt(ary[0]) == 2) ? ary[8] : parseFileXML(ary[8]);
            break;

        case 4:
            if ((ary[1] != mySelf) && (crHost != mySelf)) return false;
            // if (crHost != mySelf) return false;
            txt += ary[2] + " (" + ary[1] + ") : " + ary[8];
            break;

        case 5:
        case 6:
            txt += ary[4] + " (" + ary[3] + ") : " + ary[8];
            break;
        default:
    }

    node.innerHTML = txt + " (" + (ary[5].split(" "))[1] + ")";
    node.className = "cssChatLine";
    node.style.color = ary[7];
    if ((ary[1] == mySelf) || (ary[3]) == mySelf) node.style.backgroundColor = "#E6EEFD";
    if (crOrder) {
        if (crConts.firstChild == null) {
            crConts.appendChild(node);
        } else {
            crConts.insertBefore(node, crConts.firstChild);
        }
    } else {
        crConts.appendChild(node);
    }
    crCnts++;
}

/**
 * 分派訊息並顯示訊息
 * @param string str : 讀取的訊息內容
 *     狀態
 *     -1：系統強制下線
 *      0：下線紀錄
 *      1：上線紀錄
 *      2：一般內容
 *      3：檔案
 *      4：請求發言
 *      5：允許發言
 *      6：禁止發言
 * @return
 **/
var timerScroll = null;

function dispatch(str) {
    var cont = null,
        line = null;

    if (crConts == null) crConts = document.getElementById("chatCont");
    if (crInOut == null) crInOut = document.getElementById("chatInOut");
    if ((typeof(str) != "string") || (str == "")) return true;

    cont = str.split("\n");
    for (var i = 0; i < cont.length; i++) {
        if (trim(cont[i]) == "") continue;
        line = cont[i].split("\t");
        switch (parseInt(line[0])) {
            case -2:
            case -1:
            case 0:
            case 1:
                toInOut(line);
                break;

            case 2:
            case 3:
            case 4:
                toCont(line);
                break;

            case 5:
            case 6:
                toInOut(line);
                toCont(line);
                break;
            default:
        }
    }
    timerScroll = setTimeout('scrollContent()', 500); // 由於 IE5 的關係，所以 delay 一下才捲動 (IE5....)
}

function scrollContent() {
    clearTimeout(timerScroll);
    if (!crScroll) return true;
    if (crOrder) {
        if (crConts && crConts.firstChild != null) crConts.firstChild.scrollIntoView(true);
        if (crInOut && crInOut.firstChild != null) crInOut.firstChild.scrollIntoView(true);
    } else {
        if (crConts && crConts.lastChild != null) crConts.lastChild.scrollIntoView(false);
        if (crInOut && crInOut.lastChild != null) crInOut.lastChild.scrollIntoView(false);
    }
}

// ////////////////////////////////////////////////////////////////////////////
var cLst = "mLst";

function lstOut(obj) {
    if (cLst == obj.id) return true;
    obj.className = "tabsOff";
}

function lstOver(obj) {
    if (cLst == obj.id) return true;
    obj.className = "tabsOn";
}

function lstClick(obj) {
    var node = null;
    if (cLst == obj.id) return true;
    node = document.getElementById(cLst);
    if (node != null) node.className = "tabsOff";
    chgLst(cLst, false);

    obj.className = "tabsOn";
    cLst = obj.id;
    chgLst(cLst, true);
}

function chgLst(idx, val) {
    var obj = null;
    switch (idx) {
        case "mLst":
            obj = document.getElementById("chatUser");
            break;
        case "rLst":
            obj = document.getElementById("chatRoom");
            break;
        default:
    }
    if (obj != null) obj.style.display = val ? "" : "none";
}
// ////////////////////////////////////////////////////////////////////////////
/**
 * 開啟檔案上傳的視窗
 **/
var winFile = null;

function fileUpload() {
    if ((winFile != null) && !winFile.closed) {
        winFile.focus();
    } else {
        winFile = window.open("/learn/chat/chat_upload.php", "_blank", "width=400,height=250,toolbar=0,location=0,status=0,menubar=0,directories=0,resizable=1,scrollbars=1");
    }
}

// ////////////////////////////////////////////////////////////////////////////
function pauseScroll() {
    var obj = null;
    crScroll = !crScroll;
    obj = document.getElementById("btnPause");
    if (obj != null) obj.value = (crScroll) ? MSG_BTN_PAUSE : MSG_BTN_CANCEL;
}

function hiddenQFC(obj) {
    if ((typeof(obj) != "object") || (obj == null)) return false;
    obj.style.display = "none";
}

function showOrder() {
    crOrder = !crOrder;
}

function showLines() {
    crLine = !crLine;
}

/**
 * 設定快速鍵
 *
 **/
document.onkeydown = function(evnt) {
        var key_code = 0;
        var idx = 0;

        if (isIE) evnt = event;
        evnt.cancelBubble = true;
        key_code = evnt.keyCode;
        switch (key_code) {
            case 83: // send message
            case 115: // send message
                if (evnt.altKey) send();
                break;

            case 13: // send message
                send();
                break;

            case 65: // send message to all
            case 97:
                if (evnt.altKey) toUser("");
                break;

            case 66: // Bold
            case 98:
                if (evnt.altKey) chgFontStyle("b");
                break;

            case 73: // Italic
            case 105:
                if (evnt.altKey) chgFontStyle("i");
                break;

            case 85: // Under Line
            case 117:
                if (evnt.altKey) chgFontStyle("u");
                break;

            case 79: // order asc or desc
            case 111:
                if (evnt.altKey) showOrder();
                break;

            case 80: // pause or scroll
            case 112:
                if (evnt.altKey) pauseScroll();
                break;

            case 90: // show or hidden line number
            case 122:
                if (evnt.altKey) showLines();
                break;

            case 116:
                window.onunload = function() {};
                break;

            case 123: // show online help
                chat_help();
                break;
        }
        // alert(key_code);
        return true;
    }
    /*
     * 變更 css 圖片
     */
function chgCssImg(obj, mouse) {
    var url = $(obj).css('background-image');
    if (mouse == 'over') {
        url = url.replace(/0.gif/, '1.gif');
    } else if (mouse == 'out') {
        url = url.replace(/1.gif/, '0.gif');
    }
    $(obj).css('background-image', url);
}

if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();