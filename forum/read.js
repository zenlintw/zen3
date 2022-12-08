// test
function go_post(n){
    switch(n){
        case -1:
            location.replace('510,'+board_id+',1.php');
            break;
        case -2:
            location.replace('510,'+board_id+','+(cur_post-1)+'.php');
            break;
        case -3:
            location.replace('510,'+board_id+','+(cur_post+1)+'.php');
            break;
        case -4:
            location.replace('510,'+board_id+','+total_post+'.php');
            break;
        default:
            location.replace('510,'+board_id+','+n+'.php');
            break;
    }
}

function edit(){
    document.getElementById('post4').subject.value = document.getElementById('o_subject').innerText;
    document.getElementById('post4').content.value = document.getElementById('o_content').innerHTML;
    document.getElementById('post4').submit();
}

//#47295 Chrome 點選刪除按鈕，按鈕會消失且文章沒有被刪除，因為函數名稱跟其他相同
function removeArticle(pid){
    if (confirm(MSG_DELETE)){
        location.replace('520,'+pid+'.php');
    }
}

function mail(obj){
    obj.disabled = true;

    var newEmail = prompt(MSG_EMAIL,email);
    if (newEmail == null) {
            obj.disabled = false;
            return;
    }
    if (!sysMailsRule.test(newEmail)){
            alert('E-mail format error !');
            obj.disabled = false;
            return;
    }
    
    // 改用 ajax 與 alert
//    url = 'mail.php?node=' + node +'&target='+newEmail;
    url = '/forum/' + 'mail.php?node=' + node +'&target='+newEmail;
    
    $.ajax({
        'url': url,
        'type': 'POST',
        'dataType': 'json',
        'success': function (res) {
            alert(res);
        },
        'error': function () {
            if (window.console) {
                console.log('email Ajax Error!');
            }
        }
    });
//    showDialog(url, false, window, true, 0, 0, '240px', '100px', 'resizable=0,scrollbars=0,status=0');
    obj.disabled = false;
}

function collect(type,obj){
    obj.disabled = true;
    if (obj.parentNode.id == 'p1')
        document.getElementById('p2').innerHTML = document.getElementById('p1').innerHTML;
    else
        document.getElementById('p1').innerHTML = document.getElementById('p2').innerHTML;

    url = '54'+type+','+board_id+','+node+','+site_id+'.php';
    showDialog(url, false, window, true, 0, 0, '240px', '100px', 'resizable=0,scrollbars=0,status=0');
}

function reply(){
    document.getElementById('post3').subject.value = document.getElementById('o_subject').innerHTML;
    document.getElementById('post3').content.value = 'At ' +
                             document.getElementById('pt').innerText +
                             document.getElementById('poster').innerText +
                             'wrote : <br />\n' +    // Bug#1538: say => wrote by Small 2006/12/13
                             document.getElementById('o_content').innerHTML;
    if (document.getElementById("awppath"))
    {
        document.getElementById('post3').awppathre.value = document.getElementById("awppath").value;
    }
    document.getElementById('post3').submit();
}

function ranking(){
    var btn = (typeof(event.srcElement) == "undefined") ? event.target : event.srcElement;
    btn.disabled = true;
    var obj = document.toolbar1.rank;
    var sco = 0;
    for(var i=0; i<obj.length; i++){
        if (obj[i].checked){
            sco = i+1; break;
        }
    }
    if (sco == 0) {
        obj.disabled = false;
         return;
    }
    url = 'ranking.php?sco='+sco+'&node='+node;
    showDialog(url, false, window, true, 0, 0, '240px', '120px', 'resizable=0,scrollbars=0,status=0');}

function chooseGroup(){
    ret = showDialog('pickBoard.php',true,window,true,0,0,'270px','250px','status=0;');
    if (!ret)
        return;

    boards = document.getElementById('repost_board');
    if(!boards)
        return;

    // Clear Boards
    for(var i=boards.options.length;i>=0;i--) {
        boards.options.remove(i);
    }

    items = ret.split('\t\t');
    for(var i=0;i<items.length;i++)
    {
        item_text = items[i];
        if(item_text != '') {
            the_item = item_text.split('\t');
            boards.options.add( new Option(the_item[1],the_item[0]) );
        }
    }
}

// 顯示課程名稱於 repost_course 中
function showCourseCaption( caption ) {
    field = document.getElementById('repost_course');
    if(!field) return;

    field.value = caption;
}

function repost(){
    boards = document.getElementById('repost_board');
    sIndex = boards.selectedIndex;
    if(sIndex>-1) {
        to_board = boards.options[sIndex].value;
        do_func('repost',to_board);
    }
}

/**
 * Export Options user presses OK or Cancel
 * @pram boolean val :
 *               true  : user press OK
 *               false : user press Cancel
 * @return none
 **/
function OnExportOptions(val) {
    if(val) {
        var f2 = document.getElementById("form_export");
        f2.submit();
    }
    displayExportOptions(false);
}

/**
 * show or hidden Layer dialog
 * @pram string layer_name
 * @pram string from_btn_name
 * @pram boolean val :
 *               true  : show
 *               false : hidden
 * @return none
 **/
function displayDialog(layer_name, from_btn_name, show_or_hide) {

    var obj = document.getElementById(layer_name);
    var btn = document.getElementById(from_btn_name);
    var sclTop = 0, oHeight = 0;

    if (obj == null)    return false;
    if (show_or_hide) {
        sclTop = parseInt(document.body.scrollTop);
        oHeight = (isMZ) ? parseInt(window.innerHeight) : document.body.offsetHeight;
        if ((parseInt(obj.style.top) < sclTop) ||
            (parseInt(obj.style.top) > (sclTop + oHeight))) {
            obj.style.top = sclTop + 50;
        }
        obj.style.visibility = "visible";
        if(btn) btn.disabled = true;
    } else {
        obj.style.visibility = "hidden";
        if(btn) btn.disabled = false;
    }
    layerAction(layer_name, show_or_hide);
    return true;
}

/**
 * show or hidden Export Options
 * @pram boolean val :
 *               true  : show
 *               false : hidden
 * @return none
 **/
function displayExportOptions(val) {
    return displayDialog("export_options", "btnExport", val);
}


/**
 * show or hidden Repost Dialog
 * @pram boolean val :
 *               true  : show
 *               false : hidden
 * @return none
 **/
function displayRepostDlg(val) {
    return displayDialog("RepostDialog", "btnRepost", val);
}

//////////////////////////////////////////
/*
    以下為轉貼文章功能
 */


var isIE = false, isMZ = false;
var bodyHeight = 0, bodyWidth = 0;
var xmlVars = null, xmlHttp = null, xmlDocs = null;
// var objCkbox = new Object;
// var majorCourse = new Object;

function chkBrowser() {
    var re = new RegExp("MSIE","ig");
    if (re.test(navigator.userAgent)) {
        isIE = true;
    }

    re = new RegExp("Gecko","ig");
    if (re.test(navigator.userAgent)) {
        isMZ = true;
    }
}

/**
 * 取得節點的值
 * @param object node : 要從哪個節點中取值
 * @param string tag  : 節點中的哪個 tag
 * @return string result : 取得的值。若無法取值，則回傳空字串
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
 * XML 回傳
 * @param
 * @return
 **/
function do_func(act, extra) {
    var txt = "";
    var res = 0;

    if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
    if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
    if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();

    cur_act = act;
    switch (act) {
    case "repost" :
            txt  = "<manifest>";
            txt += "<ticket>" + ticket + "</ticket>";
            txt += "<action>" + act + "</action>";
            txt += "<src_node>" + node + "</src_node>";
            txt += "<to_board>" + extra + "</to_board>";
            txt += "</manifest>";
            res = xmlVars.loadXML(txt);
            if (!res) {
                alert(MSG_SYS_ERROR);
                return false;
            }
            xmlHttp.open("POST", "do_repost.php", false);
            xmlHttp.send(xmlVars);
            // alert(xmlHttp.responseText);
            res = xmlDocs.loadXML(xmlHttp.responseText);
            if (!res) {
                alert(MSG_SYS_ERROR);
                return false;
            }
            ticket = getNodeValue(xmlDocs.documentElement, 'ticket');
            code = parseInt(getNodeValue(xmlDocs.documentElement, 'code'));
            message = getNodeValue(xmlDocs.documentElement, 'message');
            if(code==0) { // No Error
                displayRepostDlg(false);
            }
            alert(message);
            break;
    }
    return true;
}

