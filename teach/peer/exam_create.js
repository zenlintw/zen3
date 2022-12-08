lang = lang.replace('-', '_').toLowerCase();
chkBrowser();
var t = 0;
var exam_default = 3;
/**
 * 將 html 的 tag 轉成 普通文字 顯示
 */
function htmlspecialchars(str) {
    var re = /</ig;
    var val = str;
    val = val.replace(/&/ig, "&amp;");
    val = val.replace(/</ig, "&lt;");
    val = val.replace(/>/ig, "&gt;");
    val = val.replace(/'/ig, "&#039;");
    val = val.replace(/"/ig, "&quot;");
    return val;
}

/**
 * 切換在列表上顯示或隱藏
 * @param string val : visable 或 hidden
 * @return void
 **/
function statListDateShow(val) {
    var obj = null;
    var v = (val != 1);
    obj = document.getElementById("trOpen");
    if (obj != null) obj.style.display = v ? "" : "none";
    obj = document.getElementById("trClose");
    if (obj != null) obj.style.display = v ? "" : "none";
}


// 秀日曆的函數(checkbox)
function showDateInput(objName, state) {
    var obj = document.getElementById(objName);
    if (obj != null) {
        obj.style.display = state ? "" : "none";
    }
}

// 秀日曆的函數
function Calendar_setup(ifd, fmt, btn, shtime) {
    Calendar.setup({
        inputField: ifd,
        ifFormat: fmt,
        showsTime: shtime,
        time24: true,
        button: btn,
        singleClick: true,
        weekNumbers: false,
        step: 1
    });
}

var editor = new Object();
editor.setHTML = function(x) {
    examDetail.loadXML(x);
};
var noSave = false;

window.onload = function() {
    Calendar_setup("begin_time", "%Y-%m-%d %H:%M", "begin_time", true);
    Calendar_setup("close_time", "%Y-%m-%d %H:%M", "close_time", true);
    Calendar_setup("announce_time", "%Y-%m-%d %H:%M", "announce_time", true);

    // 同儕互評日期啟用日曆模組
    if (qti_which == 'peer') {
        Calendar_setup("rating_begin_time", "%Y-%m-%d %H:%M", "rating_begin_time", true);
        Calendar_setup("rating_close_time", "%Y-%m-%d %H:%M", "rating_close_time", true);
        Calendar_setup("score_begin_time", "%Y-%m-%d %H:%M", "score_begin_time", true);
        Calendar_setup("score_close_time", "%Y-%m-%d %H:%M", "score_close_time", true);
    }

    hiddenRandomTab();
    if (isIE) releaseInputSelect();
    if (typeof(acl_lists) != 'undefined') {
        if (typeof(acl_lists[0]) != 'undefined' && acl_lists[0].length) generate_list(0);
        if (typeof(acl_lists[1]) != 'undefined' && acl_lists[1].length) generate_list(1);
    }
    xx();
    switchTab(cur_tab);
    
    if ($('#ck_peer_assessment').attr('checked') === 'checked' || $('#ck_self_assessment').attr('checked') === 'checked') {
    	$('#trOpen .strong-note').show();
    	$('#trClose .strong-note').show();
    }

    xajax_check_temp(st_id, 'FCK.editor');
    window.setInterval(function() {
        if (noSave) xajax_save_temp(st_id, examDetail.xml);
    }, 100000);
};

/**
 * 釋放 input 及 textarea 禁止拖曳、複製事件
 */
function releaseInputSelect() {
    var nodes = document.getElementsByTagName('INPUT');
    for (var i = 0; i < nodes.length; i++) {
        if (nodes.item(i).type == 'text') {
            nodes.item(i).onselectstart = cancelbubble;
            nodes.item(i).oncontextmenu = cancelbubble;
        }
    }
    nodes = document.getElementsByTagName('TEXTAREA');
    for (var i = 0; i < nodes.length; i++) {
        nodes.item(i).onselectstart = cancelbubble;
        nodes.item(i).oncontextmenu = cancelbubble;
    }
}

/**
 * 取消事件沸升 ( called by "releaseInputSelect()" )
 */
function cancelbubble() {
    event.cancelBubble = true;
}

/**
 * 切換 Tab 選單
 */
var cur_idx = -1;

function switchTab(n) {
    if (cur_idx == n) return;

    document.getElementById('srTable').style.display = (n == 1 && (qti_which == 'exam' ? document.getElementById('sysRadioBtn10').checked : true)) ? '' : 'none';

    if (cur_idx != -1) {
        // 同儕互評 TAB切第二個 但下方內容切第四個
        if (qti_which === 'peer' && n === 4) {
            var obj1 = document.getElementById('TitleID2');
        } else {
            var obj1 = document.getElementById('TitleID' + (n + 1));
        }
        tabsMouseEvent(obj1, 2);
    }

    for (var i = 0; i < 5; i++) {
        if (i == n) {
            if (n == 2) {
                genExamPaper();
                document.getElementById('toolPanel').style.top = 100;
            }
            if (n == 4) prevExamPaper();
            document.getElementById('tabContent' + i).style.display = '';
        } else {
            document.getElementById('tabContent' + i).style.display = 'none';
        }
    }
    cur_idx = n;

    // 關閉繳交對象視窗
    hide_acl_dialog();
}

/**
 * 產生 checkbox HTML (called by "travelNode()", "genExamPaper()" )
 */
function checkBox(word) {
    return '<input type="checkbox" value="' + word + '" title="' + word + '">';
}

/**
 * 畫出本節點左方樹狀線條
 */
function drawLine(isBlock, isnTail) {
    var ret = '';
    if (lineStr.length) {
        for (var i = 0; i < lineStr.length - (isBlock ? 1 : 0); i++) {
            ret += (lineStr.charAt(i) == '1') ?
                '<img src="/theme/' + theme + '/teach/vertline.gif" valign="absmiddle" border="0" width="16" height="18">' :
                '&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        ret += '<img src="/theme/' + theme + '/teach/' + (isnTail ? 'node' : 'lastnode') + '.gif" valign="absmiddle" border="0" width="16" height="18">' +
            '<img src="/theme/' + theme + '/teach/' + (isBlock ? 'icon_folder' : 'icon_all') + '.gif" valign="absmiddle" border="0" width="16" height="16">';
        return ret;
    } else {
        return '<img src="/theme/' + theme + '/teach/' + (isnTail ? 'node' : 'lastnode') + '.gif" valign="absmiddle" border="0" width="16" height="18">' +
            '<img src="/theme/' + theme + '/teach/' + (isBlock ? 'icon_folder' : 'icon_all') + '.gif" valign="absmiddle" border="0" width="16" height="16">';
    }
}

/**
 * 取得區塊文字
 */
function getSectionPM(node) {
    var nodes = node.getElementsByTagName('presentation_material');
    return (nodes.length < 1) ? '' : nodes[0].firstChild.firstChild.firstChild.firstChild.nodeValue;
}

/**
 * 取得題目題型文字
 */
function getItemType(words) {
    // words = '</td>' + words + '<td>';
    words = words.replace(/^<(\/)?td>/ig, "").replace(/<(\/)?td>$/ig, "");
    var ar = ('>' + words).split(/<\/td>\s*<td[^>]*/i); // 避免有些欄位是空的
    var type = qti_which == 'questionnaire' ? ar[ar.length - 6] : ar[ar.length - 7];
    return '<span style="color: gray">[' + type.substring(1) + ']</span>';
}

/**
 * 取得題目其它文字
 */
function getItemOther(words) {
    // words = '</td>' + words + '<td>';
    words = words.replace(/^<(\/)?td>/ig, "").replace(/<(\/)?td>$/ig, "");
    var ar = ('>' + words).split(/<\/td>\s*<td[^>]*/i); // 避免有些欄位是空的
    for (var i = 0; i < ar.length; i++) {
        ar[i] = ar[i].substring(1);
        if (i >= 2 && i <= 6 && ar[i] == '') ar[i] = 0;
        if (i == 7 && ar[i] == '') ar[i] = 3;
    }
    if (qti_which == 'questionnaire') {
        var title = ar.slice(0, ar.length - 6);
        var other = ar.slice(ar.length - 5);
        return title + '<span style="color: gray">[' + other + ']</span>';
    } else {
        var title = ar.slice(0, ar.length - 7);
        var other = ar.slice(ar.length - 6, ar.length - 1);
        var hard = hard_levels[ar[ar.length - 1]];
        return title + '<span style="color: gray">[' + other + '][' + hard + ']</span>';
    }
}

/**
 * 遞迴將試題 XML，轉換為大題操作畫面 ( called by "genExamPaper()" )
 */
function travelNode(node) {
    var ret = '';
    var item_serial = 1;
    var sec_serial = 1;

    var nodes = node.childNodes;
    for (var i = 0; i < nodes.length; i++) {
        rowStyle = rowStyle == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"';
        switch (nodes[i].tagName) {
            case 'section':
            case 'assessment':
                lineStr += (nodes[i].nextSibling == null) ? '0' : '1';
                ret += '<tr ' + rowStyle + '><td>' + drawLine(true, nodes[i].nextSibling) +
                    checkBox(nodes[i].getAttribute('id')) + '<span style="width: 3em; text-align: right; font-weight: bold">' + (sec_serial++) + '.</span>' +
                    '[<a href="javascript:;" onclick="collect(\'' + nodes[i].getAttribute('id') +
                    '\'); return false;" title="' + MSG_MV_SEC + '">' +
                    block_title[1] + '</a>]&nbsp;&nbsp;' + getSectionPM(nodes[i]) +
                    '</td></tr>' + travelNode(nodes[i]);
                lineStr = lineStr.replace(/.$/, '');
                break;
            case 'item':
                ret += '<tr ' + rowStyle + '><td>' + drawLine(false, nodes[i].nextSibling) +
                    checkBox(nodes[i].getAttribute('id')) + '<span style="width: 3em; text-align: right">' + (item_serial++) + '.</span>' +
                    getItemType(nodes[i].firstChild.nodeValue) +
                    (nodes[i].getAttribute('score') ? ('<span style="color: gray;">[' + nodes[i].getAttribute('score') + ']</span>') : '') +
                    getItemOther(nodes[i].firstChild.nodeValue) +
                    '</td></tr>';
                break;
            default:
                break;
        }
    }
    return ret;
}

/**
 * 將試題 XML，轉換為大題操作畫面 (called by "switchTab()" )
 */
function genExamPaper() {
    rowStyle = 'class="bg04 font01"';
    lineStr = '';
    var msgwin = window.open('javascript:document.write("<body leftMargin=0 topMargin=0 marginwidth=0 marginheight=0><table width=200 height=100><tr><td align=center><h3>Please wait moment ...</h3></td></tr></table></" + "body>")', '', 'width=200,height=100,top=250,left=400,status=0,resizable=0,scrollbars=0');
    var IH = '<table border="0" cellpadding="1" cellspacing="1" width="100%" style="border-collapse: collapse" class="box01">' +
        '<tr class="bg04 font01"><td>' + ('<img src="/theme/' + theme + '/teach/' +
            (examDetail.documentElement.hasChildNodes ? 'icon_folder' : 'icon_all') +
            '.gif" valign="absmiddle" border="0">') + // checkBox('qti') +
        '[<a href="javascript:;" onclick="collect(\'qti\'); return false;" title="' + MSG_MV_SEC + '">' +
        block_title[0] + '</a>]</td></tr>' +
        travelNode(examDetail.documentElement) +
        '</table>';
    document.getElementById('paperPanel').innerHTML = IH;
    msgwin.close();
}


/**
 * 試卷預覽
 */
var previewDisplayAnswer = true; // 是否要顯示標準答案
function prevExamPaper() {
    if (qti_which != 'questionnaire' && qti_which != 'peer') {
        var pt = document.getElementById('previewTable');
        pt.rows[0].cells[0].getElementsByTagName('input')[3].value = (previewDisplayAnswer ? hide_answer : show_answer);
        pt.rows[pt.rows.length - 1].cells[0].getElementsByTagName('input')[3].value = (previewDisplayAnswer ? hide_answer : show_answer);
    }

    var xmlHttp = XmlHttp.create();
    xmlHttp.open('POST', 'exam_preview.php' + (previewDisplayAnswer ? '?pda=1' : ''), false);

    var randomForm = document.getElementById('randomForm');
    var msgwin = window.open('javascript:document.write("<body leftMargin=0 topMargin=0 marginwidth=0 marginheight=0><table width=200 height=100><tr><td align=center><h3>Please wait moment ...</h3></td></tr></table></" + "body>")', '', 'width=200,height=100,top=250,left=400,status=0,resizable=0,scrollbars=0');

    if (randomForm.immediate_random_pick.checked) {
        var amount = parseInt(randomForm.immediate_random_pick_amount.value);
        if (isNaN(amount) || amount < 1 || amount > 200) amount = 50;
        var score = parseInt(randomForm.immediate_random_pick_score.value);
        if (isNaN(score)) score = 0;
        var prevRandom = XmlDocument.create();
        prevRandom.loadXML('<wm_immediate_random_generate_qti threshold_score=""><form>' +
            getSearchXml(qti_which == 'questionnaire') +
            '<amount    selected="true">' + amount + '</amount>' +
            '<score     selected="true">' + score + '</score>' +
            '</form></wm_immediate_random_generate_qti>');
        xmlHttp.send(prevRandom);
    } else
        xmlHttp.send(examDetail);
    document.getElementById('examPreview').innerHTML = xmlHttp.responseText;
    if (msgwin !== undefined) {
        msgwin.close();
    }
    if (qti_which != 'questionnaire') previewDisplayAnswer ^= true;
}

/**
 * main process of GetElementById() (called by GetElementById() )
 */
function travelAllElement(node, id) {
    var ret;
    if (node.nodeType == 1) {
        if (node.getAttribute('id') == id) return node;
        var nodes = node.childNodes;
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].nodeType == 1) {
                ret = travelAllElement(nodes[i], id);
                if (ret != null && typeof(ret) == 'object') return ret;
            }
        }
    }
    return null;
}

/**
 * 自定 XML_DOM 之 getElementById() 之 method
 */
function GetElementById(dom, id) {
    return travelAllElement(dom.documentElement, id);
}

/**
 * 將試題搬到 id=label 之大題下
 */
function collect(label) {
    var sectionNode = GetElementById(examDetail, label);
    var currNode;
    var objForm = document.getElementById('paperPanel');
    var nodes = objForm.getElementsByTagName('input');
    for (var i = nodes.length - 1; i >= 0; i--) {
        if (nodes[i].value == label) continue;
        if (nodes[i].type == 'checkbox' && nodes[i].checked) {
            currNode = GetElementById(examDetail, nodes[i].value);
            if (currNode != null && typeof(currNode) == 'object') {
                if (label == 'qti')
                    moveToSection(examDetail.documentElement, currNode);
                else
                    moveToSection(sectionNode, currNode);
            }
        }
    }
    genExamPaper();
}

/**
 * 判斷要搬至的目標目錄，是否為自己的子代目錄
 */
function isMyParent(self, node) {
    var curNode = self;
    while (node != curNode) {
        if ((curNode = curNode.parentNode) === null) return false;
    }
    return true;
}

/**
 * 將 procNode 節點搬到 parendNode 之下 (called by "collect()" )
 */
function moveToSection(parentNode, procNode) {
    if (isMyParent(parentNode, procNode)) {
        alert(MSG_X_MV_CHD);
        return;
    }
    if (procNode.tagName == 'questestinterop') return;
    var newNode = procNode.cloneNode(true);
    // 避免移入大題時，順序倒反，所以要插到第一個位置
    if (parentNode.hasChildNodes())
        parentNode.insertBefore(newNode, parentNode.firstChild);
    else
        parentNode.appendChild(newNode);
    procNode.parentNode.removeChild(procNode);
}

function moveNode(nodeName, dir) {
    var node = GetElementById(examDetail, nodeName);
    if (node == null) return;
    var parent = node.parentNode;

    if (dir == -1) {
        var prev = node.previousSibling;
        if (prev != null) {
            var newNode = node.cloneNode(true);
            parent.insertBefore(newNode, prev);
            parent.removeChild(node);
        }
    } else {
        var next = node.nextSibling;
        if (next != null) {
            var newNode = next.cloneNode(true);
            parent.insertBefore(newNode, node);
            parent.removeChild(next);
        }
    }
}

/**
 * 試卷大題控制
 */
function paperTuning(n) {
    var obj;
    switch (n) {
        case 1: // 新增大題
            while (examDetail.selectSingleNode("//section[@id='section-" + sectionIdx + "']")) sectionIdx++;
            var newNode = examDetail.createElement('section');
            newNode.setAttribute('id', 'section-' + sectionIdx++);
            examDetail.documentElement.appendChild(newNode);
            break;
        case 2:
        case 3:
            var nodes = document.getElementById('paperPanel').getElementsByTagName('input');
            if (n == 2) { // 刪除大題
                var c = 0;
                for (var i = nodes.length - 1; i >= 0; i--) {
                    if (nodes[i].type == 'checkbox' &&
                        nodes[i].checked &&
                        nodes[i].value.substr(0, 8) == 'section-'
                    ) {
                        obj = GetElementById(examDetail, nodes[i].value);
                        obj.parentNode.removeChild(obj);
                        c++;
                    }
                }
                if (c == 0) {
                    alert(MSG_SEL_SEC);
                    return;
                }
            } else { // 刪除題目
                var c = 0;
                for (var i = nodes.length - 1; i >= 0; i--) {
                    if (nodes[i].type == 'checkbox' &&
                        nodes[i].checked &&
                        nodes[i].value.substr(0, 8) != 'section-' &&
                        nodes[i].value != 'qti'
                    ) {
                        obj = GetElementById(examDetail, nodes[i].value);
                        obj.parentNode.removeChild(obj);
                        c++;
                    }
                }
                if (c == 0) {
                    alert(MSG_SEL_FIRST);
                    return;
                }
            }
            break;
        case 4: // 大題文字
            var nodes = document.getElementById('paperPanel').getElementsByTagName('input');
            var obj = document.getElementById('pmForm');
            obj.ident.value = '';
            for (var i = 0; i < nodes.length; i++) {
                if (nodes[i].type == 'checkbox' &&
                    nodes[i].checked &&
                    nodes[i].value.substr(0, 8) == 'section-'
                ) {
                    obj.ident.value = nodes[i].value;
                    var curNode = GetElementById(examDetail, nodes[i].value);
                    pmNodes = curNode.getElementsByTagName('presentation_material');
                    if (pmNodes.length > 0) {
                        obj.presentation_material.value = pmNodes[0].firstChild.firstChild.firstChild.firstChild.nodeValue;
                    } else
                        obj.presentation_material.value = '';
                    break;
                }
            }
            if (obj.ident.value == '') {
                alert(MSG_SEL_SEC);
                return;
            }
            displayDialogWindow('sectionTextDialog');
            break;
        case 5: // 上移
            var nodes = document.getElementById('paperPanel').getElementsByTagName('input');
            var reserves = new Array();
            var c = 0;
            for (var i = 0; i < nodes.length; i++) {
                if (nodes[i].type == 'checkbox' && nodes[i].checked) {
                    moveNode(nodes[i].value, -1);
                    reserves[reserves.length] = nodes[i].value;
                    c++;
                }
            }
            if (c == 0) {
                alert(MSG_SEL_FIRST);
                return;
            }
            break;
        case 6: // 下移
            var nodes = document.getElementById('paperPanel').getElementsByTagName('input');
            var reserves = new Array();
            var c = 0;
            for (var i = nodes.length - 1; i >= 0; i--) {
                if (nodes[i].type == 'checkbox' && nodes[i].checked) {
                    moveNode(nodes[i].value, 1);
                    reserves[reserves.length] = nodes[i].value;
                    c++;
                }
            }
            if (c == 0) {
                alert(MSG_SEL_FIRST);
                return;
            }
            break;
        case 7: // 指定分數
            var nodes = document.getElementById('paperPanel').getElementsByTagName('input');
            var ret = '';
            for (var i = 0; i < nodes.length; i++)
                if (nodes[i].type == 'checkbox' && nodes[i].checked)
                    ret += (i + ',');

            if (ret == '') {
                alert(MSG_SEL_BEFORE);
                return;
            }
            ret = ret.replace(/,$/, '');

            var score = prompt(MSG_INPUT_SCORE, '');
            if (score == '' || score == null || score.search(/^[0-9]+(\.[0-9]+)?$/) !== 0) return;
            score = parseFloat(score);
            var aa = ret.split(',');
            for (var i = 0; i < aa.length; i++)
                assign_score(nodes[aa[i]].value, score);

            break;
        case 8: // 平均配分
            var score = prompt(MSG_INPUT_TOTAL, '');
            if (score == '' || score == null || score.search(/^[0-9]+(\.[0-9]+)?$/) !== 0) return;
            score = parseFloat(score);
            var nodes = examDetail.getElementsByTagName('item');
            if (nodes == null || nodes.length == 0) return;
            var outcome = Math.floor((score / nodes.length) * Math.pow(10, 2)) / Math.pow(10, 2);
            var tail = score - (outcome * nodes.length);
            for (var i = 0; i < nodes.length - 1; i++)
                nodes[i].setAttribute('score', outcome);
            nodes[nodes.length - 1].setAttribute('score', outcome + tail);
            break;
        case 9: // 全選
        case 10: // 全消
            var nodes = document.getElementById('paperPanel').getElementsByTagName('input');
            var mode = n % 2 ? true : false;
            for (var i = 0; i < nodes.length; i++)
                if (nodes[i].type == 'checkbox') nodes[i].checked = mode;
            break;
    }

    // 重新產生列表
    if (n < 9) {
        noSave = true;
        if (n != 4) genExamPaper();
    }

    // 如果上下移，就恢復勾選
    if (typeof(reserves) != 'undefined' && reserves.length) {
        nodes = document.getElementById('paperPanel').getElementsByTagName('input');
        for (var j = 0; j < reserves.length; j++) {
            for (var i = nodes.length - 1; i >= 0; i--) {
                if (nodes[i].type == 'checkbox' && nodes[i].value == reserves[j]) nodes[i].checked = true;
            }
        }
    }
}

function assign_score(nodeName, score) {
    var node = examDetail.documentElement.selectSingleNode('//item[@id="' + nodeName + '"]');
    if (node != null) {
        node.setAttribute('score', score);
    }
}

/**
 * 輸入區塊文字
 */
function enterPM() {
    var obj = document.getElementById('pmForm');
    var ident = obj.ident.value;
    var node = GetElementById(examDetail, ident);
    if (node.tagName != 'section') {
        alert('node incorrect.');
        return;
    }

    var nodes = node.getElementsByTagName('presentation_material');
    if (nodes.length < 1) {
        var newNode = examDetail.createElement('presentation_material');
        var curNode = node.insertBefore(newNode, node.firstChild);
        newNode = examDetail.createElement('flow_mat');
        curNode = curNode.appendChild(newNode);
        newNode = examDetail.createElement('material');
        curNode = curNode.appendChild(newNode);
        newNode = examDetail.createElement('mattext');
        curNode = curNode.appendChild(newNode);
        newNode = examDetail.createTextNode(obj.presentation_material.value);
        curNode.appendChild(newNode);
        // alert(node.xml);
    } else
        nodes[0].firstChild.firstChild.firstChild.firstChild.nodeValue = obj.presentation_material.value;

    document.getElementById('sectionTextDialog').style.display = 'none';
    genExamPaper();
}

// ======================================================================================================================
/**
 * 產生 s 到 e 的 <option> 選項，其中 =k 則加 selected
 */
function genSerial(s, e, k) {
    for (var i = s; i <= e; i++) document.writeln('<option value="' + i + (k == i ? '" selected>' : '">') + i + '</option>');
}

/**
 * 自定日期顯示與否
 */
function customTime(n) {
    document.getElementById('customTimePal').style.display = (n == 'user_define') ? '' : 'none';
}

/**
 * 題目搜尋時，若 text 有填入資料則前面 checkbox 自動勾選
 */
function checkSelect(obj) {
    if (obj.tagName.toLowerCase() != 'select')
        obj.previousSibling.previousSibling.checked = (obj.value != '');
    else
        obj.parentNode.previousSibling.firstChild.checked = (obj.value != '');
}

/**
 * 題型、難易度
 */
function checkSelect2(obj) {
    var $chk = jQuery(obj).parent().prev().find('input:checkbox');
    if (obj.checked) {
        $chk.attr('checked', true);
    }
    if (jQuery(obj).parent().find('input:checkbox:checked').length <= 0) {
        $chk.removeAttr('checked');
    }
}
/**
 * 啟動亂數出題
 */
function randomCheck(obj) {
    var panel = document.getElementById('tabContent3');
    var nodes = panel.getElementsByTagName('input');
    for (var i = 0; i < 9; i++) {
        if ((nodes[i].type == 'checkbox' || nodes[i].type == 'text') && obj.name != nodes[i].name)
            nodes[i].disabled = !obj.checked;
    }

    if (!obj.checked) nodes[9].disabled = true;
    else if (nodes[8].checked) nodes[9].disabled = false;
}

/**
 * 啟動亂數出題
 */
function randomCheck2(obj) {
    /*
    var panel = document.getElementById('tabContent3');
    var nodes = panel.getElementsByTagName('input');
    for(var i=11; i<nodes.length; i++){
    */
    var panel = document.getElementById('randomForm');
    var nodes = panel.getElementsByTagName('input');
    for (var i = 0; i < nodes.length; i++) {
        if ((nodes[i].type == 'checkbox' || nodes[i].type == 'text') && obj.name != nodes[i].name)
            nodes[i].disabled = !obj.checked;
    }
    var nodes = panel.getElementsByTagName('select');
    for (var i = 0; i < nodes.length; i++) {
        nodes[i].disabled = !obj.checked;
    }
}


/**
 * 選擇或取消選擇所有選出的題目
 */
function checkAll(check) {
    var obj = document.getElementById('searchResult');
    var nodes = obj.getElementsByTagName('input');
    for (var i = nodes.length - 1; i >= 0; i--) {
        if (nodes[i].type != 'checkbox') continue;
        nodes[i].checked = check;
    }
}

/**
 * 將搜尋的題目加入試卷
 */
function pickItem() {
    var obj = document.getElementById('searchResult');
    var nodes = obj.getElementsByTagName('input');
    var ih = '',
        id = '',
        root = examDetail.documentElement,
        newNode, newText;
    var tableNode = document.getElementById('searchTable');
    var removes = new Array();
    var rmIndex = 0;
    var isExisted;
    var countdown = nodes.length - 1;

    NowPickedNum = root.childNodes.length;
    if (NowPickedNum >= MaxPickedNum) {
        alert(msg_overNumber);
        return;
    }

    if (NowPickedNum == 0) {
        alert(MSG_PICK_ITEM_CUE);
    }

    for (var i = 0; i < nodes.length; i++) {
        window.status = countdown--;
        if (nodes[i].type == 'checkbox' && nodes[i].checked && nodes[i].value != '@') {
            NowPickedNum = root.childNodes.length;
            if (NowPickedNum == MaxPickedNum) {
                alert(msg_overNumber);
                break;
            }

            isExisted = root.selectSingleNode('//item[@id="' + nodes[i].value + '"]');
            if (isExisted != null) {
                nodes[i].disabled = true;
                alert(MSG_IGN_REPEAT);
                continue;
            }

            // 刪除多餘的column
            if (nodes[i].parentNode.parentNode.childNodes.length == 11)
                nodes[i].parentNode.parentNode.removeChild(nodes[i].parentNode.parentNode.childNodes[10]);

            var tr = nodes[i].parentNode.parentNode.childNodes;
            ih = '<td>' + tr[3].innerHTML + '</td><td>' + tr[2].innerHTML + '</td>';
            for (var tmp = 4; tmp < tr.length; tmp++) {
                ih += '<td>' + tr[tmp].innerHTML + '</td>';
            }

            newNode = examDetail.createElement('item');
            newText = examDetail.createTextNode(ih);
            newNode.setAttribute('id', nodes[i].value);
            newNode.appendChild(newText);
            root.appendChild(newNode);
            removes[rmIndex++] = nodes[i].parentNode.parentNode.rowIndex;
            noSave = true;
        }
    }
    for (var i = rmIndex - 1; i >= 0; i--) tableNode.deleteRow(removes[i]);
}

/**
 * 向 server 取得搜尋題目
 */
function search_item() {
    //	var selTypes = [], selLevels = [];
    //	jQuery('.item_type input:checkbox:checked').each(function () {
    //		selTypes.push(jQuery(this).val());
    //	});
    //	jQuery('.hard_level input:checkbox:checked').each(function () {
    //		selLevels.push(jQuery(this).val());
    //	});
    //	var topPanel = document.getElementById('searchForm');
    //	var queryXml = '<form>' +
    //	                  '<version   selected="' + topPanel.isVersion.checked   + '">' + topPanel.version.value   + '</version>' +
    //	                  '<volume    selected="' + topPanel.isVolume.checked    + '">' + topPanel.volume.value    + '</volume>' +
    //	                  '<chapter   selected="' + topPanel.isChapter.checked   + '">' + topPanel.chapter.value   + '</chapter>' +
    //	                  '<paragraph selected="' + topPanel.isParagraph.checked + '">' + topPanel.paragraph.value + '</paragraph>' +
    //	                  '<section   selected="' + topPanel.isSection.checked   + '">' + topPanel.section.value   + '</section>' +
    //	                  '<type      selected="' + topPanel.isType.checked      + '">' + selTypes.join(',')      + '</type>' +
    //	                  (qti_which!='questionnaire'?('<level     selected="' + topPanel.isLevel.checked     + '">' + selLevels.join(',') + '</level>'):'') +
    //	                  '<fulltext  selected="' + topPanel.isFulltext.checked  + '">' + htmlspecialchars(topPanel.fulltext.value) + '\t' + htmlspecialchars(topPanel.fulltext.value) + '</fulltext>' +
    //	                  '<scope>' + topPanel.scope.value + '</scope>' +
    //	    			  '<rowspage>' + topPanel.rows_page_share.value + '</rowspage>'+
    //	    			  '<pages>' + topPanel.pages.value + '</pages>'+
    //                     '</form>';
    //	var xmlHttp = XmlHttp.create();
    //	var xmlVars = XmlDocument.create();
    //	xmlVars.loadXML(queryXml);
    //	xmlHttp.open('POST', 'item_search.php', false);
    //	xmlHttp.send(xmlVars);
    //	var ret = xmlVars.loadXML(xmlHttp.responseText);
    //	if (ret == false) { alert(MSG_NOT_XML); return;}
    //	var root = xmlVars.documentElement;
    //	if (root.tagName == 'errorlevel'){
    //		switch(root.firstChild.nodeValue){
    //			case '1':
    //				alert(MSG_INCR_XML); return;
    //				break;
    //			case '2':
    //				alert(MSG_INCR_FORM); return;
    //				break;
    //			case '3':
    //				if (/<\w+\s+selected="true">/.test(queryXml))
    //					alert(MSG_NO_RESULT);
    //				else
    //					alert(MSG_NO_ITEMS);
    //				return;
    //				break;
    //			default:
    //				alert(MSG_UNKNOW_ERR); return;
    //				break;
    //		}
    //	}
    //	if (root.tagName != 'questestinterop' ) { alert('Returning XML\'s root node nust <questestinterop>'); return;}
    //	var nodes = root.childNodes;
    //	var total_shareitem = nodes[0].firstChild.nodeValue;
    //	if (topPanel.rows_page_share.value =='-1'){var rows_page_now = '10';}else{var rows_page_now = topPanel.rows_page_share.value;}
    //	var pagelength = Math.ceil(total_shareitem/rows_page_now);
    //	var htm = '<form id="shareForm" style="display:inline"><table border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="box01" id="searchTable">' +
    //		      '<tr class="cssTrEvn font01">'+
    //		      '<td align="left">' +
    //		      '<input type="checkbox" name="search_ck" id="search_ck" onclick="checkAll(this.checked);" value="@">' +
    //		      '</td>' +
    //		      '<td colspan="' + (qti_which == 'questionnaire' ? 7 : 8) + '" align="center">' +
    //			  '<font class=font01>' + MSG_PAGE_NUM + '</font>'+
    //			  '<select onchange="search_page(this.value)">';
    //		      for(var s=1; s<pagelength+1; s++){
    //	    htm+= ('<option value="'+ s + '"');
    //	    	  if (s == topPanel.pages.value){
    //	    	  	  htm+= ('selected="selected"');
    //	    	  }
    //	    htm+= ('>' + s + '</option>');
    //	    	  }
    //	    htm+= '</select> '+
    //			  '<font class=font01>' + MSG_PAGE_EACH + '</font>'+
    //			  '<select name="rps" onchange="go_rowspage_share(this.value,'+
    //			   '' + total_shareitem + ')">';
    //		      for(var r=0; r<rowspages.length; r++){
    //	    htm+= ('<option value="'+ rowspages[r] +'"');
    //	    	  if (rowspages[r] == rows_page_now){
    //	    	  	  htm+= ('selected="selected"');
    //	    	  }
    //	    htm+= ('>' + rowspagesn[r] + '</option>');
    //
    //	          }
    //
    //	    htm+= '</select> '+
    //			  '<font class=font01>' + MSG_PAGE_ITEM + '</font> '+
    //		      '<input type="button" value="' + MSG_SEARCHPAGE_TOP + '"  onclick="search_page(1);" id="s_pagebtn1" class="cssBtn" ';
    //		if (topPanel.pages.value=='1'){
    //		       htm+= ('disabled');
    //			   }
    //		htm+= '> ' +
    //		      '<input type="button" value="' + MSG_SEARCHPAGE_UP + '"   onclick="search_page(' + topPanel.pages.value + '-1);" id="s_pagebtn2" class="cssBtn" ';
    //		if (topPanel.pages.value=='1'){
    //		       htm+= ('disabled');
    //			   }
    //		htm+= '> ' +
    //		      '<input type="button" value="' + MSG_SEARCHPAGE_DOWN + '" onclick="search_page(' + topPanel.pages.value + '+1);" id="s_pagebtn3" class="cssBtn" ';
    //		if (topPanel.pages.value==pagelength){
    //		       htm+= ('disabled');
    //			   }
    //		htm+= '> ' +
    //		      '<input type="button" value="' + MSG_SEARCHPAGE_END + '"  onclick="search_page(' + pagelength + ');" id="s_pagebtn4" class="cssBtn" ';
    //				if (topPanel.pages.value==pagelength){
    //		       htm+= ('disabled');
    //			   }
    //		htm+= '> ' +
    //		      '</td>'+
    //		      '<td align="right">' +
    //	          '<input type="button" value="' + btms[0] + '" onclick="pickItem();" class="cssBtn">&nbsp;&nbsp;&nbsp;' +
    //	          '</td></tr><tr class="bg02 font01">';
    //		  for(var i=0; i<srTables.length; i++) {
    //		  	if (qti_which != 'questionnaire' && i == 9)
    //		  		htm += ('<td style="display:none;">' + srTables[i] + '</td>');
    //		  	else
    //		  		htm += ('<td>' + srTables[i] + '</td>');
    //		  }
    //		  htm += '</tr>';
    //	var properties;
    //
    //
    //	var col = '';
    //	var serial_no = (topPanel.pages.value - 1) * rows_page_now + 1;
    //	for(var i=1; i<nodes.length; i++){
    //		if (nodes[i].tagName == 'item'){
    //			col = col == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"';
    //			htm += '<tr ' + col + '>';
    //			properties = nodes[i].childNodes;
    //			htm += '<td width="30"><input type="checkbox" name="pick[]" value="' + properties[0].firstChild.nodeValue + '" onclick="checkPick();"></td><td align="right" style="padding-right: 1em">' + (serial_no++) + '</td>';
    //			for(var j=1; j<properties.length; j++){
    //				switch(j){
    //					case 1:  htm += '<td width="40">' + types[properties[j].firstChild.nodeValue] + '</td>'; break;
    //					case 2:  htm += '<td width="300">' + properties[j].firstChild.nodeValue + '</td>'; break;
    //					case 8:  htm += (qti_which == 'questionnaire') ? '' : ('<td style="display:none">' + properties[j].firstChild.nodeValue + '</td><td width="50">' + hard_levels[properties[j].firstChild.nodeValue] + '</td>'); break;
    //					default: htm += '<td width="20">' + properties[j].firstChild.nodeValue + '</td>'; break;
    //				}
    //			}
    //			htm += '</tr>';
    //		}
    //	}
    //	htm += '<tr class="cssTrEvn font01">'+
    //		   '<td align="left">' +
    //		   // '<input type="checkbox" onclick="checkAll(true);" value="@">' +
    //		   '</td>' +
    //		   '<td colspan="' + (qti_which == 'questionnaire' ? 7 : 8) + '" align="center">' +
    //		   '</td>'+
    //		   '<td>'+
    //	       '<input type="button" value="' + btms[0] + '" onclick="pickItem();" class="cssBtn">&nbsp;&nbsp;&nbsp;' +
    //	       '</td></tr></table></form>';
    //
    //	document.getElementById('searchResult').innerHTML = htm;
    //	document.getElementById('srTable').style.display='';
    //	checkPick();
}

function go_rowspage_share(n, m) {
    var form = document.getElementById('searchForm');
    /*
    if (form.rows_page_share.value =='-1'){var rows_page_now = '10';}else{var rows_page_now = form.rows_page_share.value;}
    if (n =='-1'){var i = '10';}else{var i = n;}
    var h = rows_page_now * form.pages.value;
    var newpage = Math.ceil(h/i);
    var limpage = Math.ceil(m/i);
    if (h<m){
    	form.pages.value = newpage;
    	//form.document.getElementById('pages').value = newpage;
    }else{
    	form.pages.value = limpage;
    	// form.document.getElementById('pages').value = limpage;
    }
    */
    form.pages.value = 1;
    form.rows_page_share.value = n;
    // form.document.getElementById('rows_page_share').value = n;
    search_item();
}

function search_page(n) {
    var form = document.getElementById('searchForm');
    if (n > 0) {
        // form.document.getElementById('pages').value = n;
        form.pages.value = n;
        search_item();
    }
}

/**
 * 將試卷輸出
 */
function ExportContent() {
    var obj = document.getElementById("examPreview");
    var form = document.getElementById('exportForm');
    form.table_html.value = obj.innerHTML;
    form.submit();
}

/**
 * 將試卷存檔
 */
function saveContent(tab) {

    var obj = document.getElementById('saveForm');

    if (qti_which != 'questionnaire' && // 問卷就不必管分數
        qti_which != 'peer' &&
        obj.count_type.value != 'none' && // 如果設為不計分，也不管分數
        obj.percent.value && // 如果比例為 0 也不管分數
        document.getElementById('sysRadioBtn10').checked // 如果是手動選題
    ) {
        var sn = examDetail.getElementsByTagName('item');
        var ts = 0;
        for (var i = 0; i < sn.length; i++) {
            ts += parseInt(sn[i].getAttribute('score'));
        }
        if (sn.length && ts == 0 && !confirm(MSG_SCORE_REM)) return;
    }

    var nodes = obj.getElementsByTagName('input');

    // 檢查是否有輸入標題
    if (!chk_multi_lang_input(1, true, MSG_LANG_HINT, undefined, 254)) {
        tabsSelect(1);
        return;
    }
    
    if ($('#ck_peer_assessment').attr('checked') === 'checked' || $('#ck_self_assessment').attr('checked') === 'checked') {
    	if ($('#ck_rating_begin_time').attr('checked') === undefined && $('#ck_rating_close_time').attr('checked') === undefined){
    		alert(MSG_RATING_NEED);
            return;	
    	}
    }

    // 評分開始日期要大於作答結束日期
    if ($('#ck_rating_begin_time').prop('checked') === true) {
        var ratingBeginTime = ($('#rating_begin_time').val()).replace(/[\D]/ig, '');
        var EndTime = ($('#close_time').val()).replace(/[\D]/ig, '');
        if ((parseInt(ratingBeginTime) < parseInt(EndTime)) ||
            $('#ck_close_time').prop('checked') === false) {
            alert(MSG_RATING_NEED);
            return;
        }
    }

    // 公布開始日期要大於評分結束日期
    if ($('input:radio:checked[name="rdoScorePublish"]').val() === '2' && $('#ck_score_begin_time').prop('checked') === true && $('#ck_rating_close_time').prop('checked') === true) {
        var scoreBeginTime = ($('#score_begin_time').val()).replace(/[\D]/ig, '');
        var ratingEndTime = ($('#rating_close_time').val()).replace(/[\D]/ig, '');
        if (parseInt(scoreBeginTime) < parseInt(ratingEndTime)) {
            alert(MSG_PUB_GRE_RATING);
            return;
        }
    }

    // 公布開始日期要大於作答結束日期
    if ($('input:radio:checked[name="rdoScorePublish"]').val() === '2' && $('#ck_score_begin_time').prop('checked') === true && $('#ck_close_time').prop('checked') === true) {
        var scoreBeginTime = ($('#score_begin_time').val()).replace(/[\D]/ig, '');
        var endTime = ($('#close_time').val()).replace(/[\D]/ig, '');
        if (parseInt(scoreBeginTime) < parseInt(endTime)) {
            alert(MSG_PUB_GRE_ANS);
            return;
        }
    }

    // 作答結束日期要大於作答開放日期
    if ($('#ck_begin_time').prop('checked') === true && $('#ck_close_time').prop('checked') === true) {
        var beginTime = ($('#begin_time').val()).replace(/[\D]/ig, '');
        var endTime = ($('#close_time').val()).replace(/[\D]/ig, '');
        if (parseInt(beginTime) >= parseInt(endTime)) {
            alert(MSG_ANS_DATE_ERR);
            return;
        }
    }

    // 評分結束日期要大於開放日期
    if ($('#ck_rating_begin_time').prop('checked') === true && $('#ck_rating_close_time').prop('checked') === true) {
        var ratingBeginTime = ($('#rating_begin_time').val()).replace(/[\D]/ig, '');
        var ratingEndTime = ($('#rating_close_time').val()).replace(/[\D]/ig, '');
        if (parseInt(ratingBeginTime) >= parseInt(ratingEndTime)) {
            alert(MSG_RATE_DATE_ERR);
            return;
        }
    }

    // 成績公告結束日期要大於開放日期
    if ($('input:radio:checked[name="rdoScorePublish"]').val() === '2' && $('#ck_score_begin_time').prop('checked') === true && $('#ck_score_close_time').prop('checked') === true) {
        var scoreBeginTime = ($('#score_begin_time').val()).replace(/[\D]/ig, '');
        var scoreEndTime = ($('#score_close_time').val()).replace(/[\D]/ig, '');
        if (parseInt(scoreBeginTime) >= parseInt(scoreEndTime)) {
            alert(MSG_SCR_DATE_ERR);
            return;
        }
    }

    // 有發布並設定起迄時間,則要檢查
    for (var i = 0; i < obj.rdoPublish.length; i++) {
        if (obj.rdoPublish[i].checked)
            var rdo = obj.rdoPublish[i].value;
    }
    if (rdo == 2 && obj.ck_begin_time.checked && obj.ck_close_time.checked) {
        val1 = obj.begin_time.value.replace(/[\D]/ig, '');
        val2 = obj.close_time.value.replace(/[\D]/ig, '');
        if (parseInt(val1) >= parseInt(val2)) {
            alert(MSG_DATE_ERR);
            return;
        }
    }

    // 檢查評分標準說明有無填寫
    if (CKEDITOR.instances.rating_criteria_1.getData() == '') {
        alert(MSG_RATING_NOTICE_NEED);
        CKEDITOR.instances['rating_criteria_1'].focus();
        return;
    }
    
    // 檢查至少勾選自評或者互評
    if (window.console) {
        console.log($('#ck_peer_assessment').attr('checked') === undefined);
        console.log($('#ck_self_assessment').attr('checked') === undefined);
    }
    
    if ($('#ck_peer_assessment').attr('checked') === undefined && $('#ck_self_assessment').attr('checked') === undefined) {
        alert(MSG_PEERSELF_NEED);
        $('#ck_peer_assessment').focus();
        return;
    }

    // 檢查比重
    var percent_sum = 0;
    if ($('#ck_peer_assessment').prop('checked') === true) {
        if (parseInt($('#peer_percent').val())==0 || $('#peer_percent').val()=='') {
	    alert(MSG_PEER_PERCENT);
	    $('#peer_percent').focus();
	    return; 
	}
        percent_sum = percent_sum + parseInt($('#peer_percent').val(), 10);
    }
    if ($('#ck_self_assessment').prop('checked') === true) {
        if (parseInt($('#self_percent').val())==0 || $('#self_percent').val()=='') {
	    alert(MSG_SELF_PERCENT);
	    $('#self_percent').focus();
	    return; 
	}
        percent_sum = percent_sum + parseInt($('#self_percent').val(), 10);
    }
    if (percent_sum >= 101) {
        alert(MSG_MAX_100);
        return;
    }

    var als = new Array();

    for (var i = 0; i < acl_lists.length; i++)
        als[i] = (typeof(acl_lists[i]) == 'undefined') ? '' : acl_lists[i].join('\n');

    obj.content.value = examDetail.xml;
    obj.acl_lists.value = als.join('\f');

    var randomPanel = document.getElementById('tabContent3');
    nodes = randomPanel.getElementsByTagName('input');
    obj.item_cramble.value = '';
    for (var i = 0; i < 10; i++) {
        if (nodes[i].type.toLowerCase() == 'checkbox' && nodes[i].checked)
            obj.item_cramble.value += nodes[i].value + ',';
        else if (nodes[i].name == 'random')
            obj.random_pick.value = nodes[i].value;
    }
    obj.item_cramble.value = obj.item_cramble.value.replace(/,$/, '');

    var randomForm = document.getElementById('randomForm');

    if (randomForm.immediate_random_pick.checked) {
        if (randomForm.immediate_random_pick_amount.value == '') {
            tabsSelect(2);
            alert(MSG_IRGA_REQ);
            randomForm.immediate_random_pick_amount.focus();
            return;
        }
        if (obj.count_type.value != 'none' && // 如果設為不計分，也不管分數
            obj.percent.value && // 如果比例為 0 也不管分數
            randomForm.immediate_random_pick_score.value == '') {
            tabsSelect(2);
            alert(MSG_IRGS_REQ);
            randomForm.immediate_random_pick_score.focus();
            return;
        }

        var amount = parseInt(randomForm.immediate_random_pick_amount.value);
        if (isNaN(amount) || amount < 1 || amount > 200) amount = 50;
        var score = parseInt(randomForm.immediate_random_pick_score.value);
        if (isNaN(score)) score = 0;

        obj.content.value = '<wm_immediate_random_generate_qti threshold_score=""><form>' +
            getSearchXml(qti_which == 'questionnaire') +
            '<amount    selected="true">' + amount + '</amount>' +
            '<score     selected="true">' + score + '</score>' +
            '</form></wm_immediate_random_generate_qti>';
    }

    xajax_clean_temp(st_id);

    // 切換TAB或儲存
    if (tab !== undefined) {
        switchTab(tab);
    } else {
        obj.submit();
        $('.save-content').attr('disabled', true);
    }
}

/**
 * 啟始對話框的位置
 */
function displayDialogWindow(objName) {
    var obj = document.getElementById(objName);
    // 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(480)] 再左移 10 個 pixel
    obj.style.left = document.body.scrollLeft + document.body.offsetWidth - 796;
    // 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
    obj.style.top = document.body.scrollTop + 30;
    obj.style.display = '';
}

/**
 * 自動挑題的選項連動
 */
function selectRandomMode(v) {
    var x = document.getElementById('tabContent1');
    x.getElementsByTagName('table')[0].style.display = 'none';
    var y = x.getElementsByTagName('form');
    if (v == '1') {
        if (qti_which == 'exam') document.getElementById('sysRadioBtn10').checked = true;
        y[0].style.display = 'inline';
        y[1].style.display = 'none';
        document.getElementById('srTable').style.display = '';
        if (!examDetail.documentElement.hasChildNodes) search_item(); // 第一次選擇手動挑題自動搜尋一次
    } else {
        if (qti_which == 'exam') document.getElementById('sysRadioBtn11').checked = true;
        y[0].style.display = 'none';
        y[1].style.display = 'inline';
        y[1].immediate_random_pick.checked = true;
        var t = document.getElementsByTagName('table')[1];
        for (var i = 6; i < 12; i++) t.rows[0].cells[i].style.display = 'none';
        exam_default = 1;
    }
}

/**
 * 工具列自動隨拉動畫面而移動
 */
window.onscroll = function() {
    document.getElementById('toolPanel').style.top = document.body.scrollTop + 100;
};

/**
 * 將隨機選題的條件，由 Form 表單轉為 XML
 *
 * @param   bool    isQuestionnaire     是否為問卷 (若是則不處理難易度)
 * @return  string                      傳回 XML
 */
function getSearchXml(isQuestionnaire) {
    var randomForm = document.getElementById('randomForm');
    var td = randomForm.getElementsByTagName('table')[0].rows[3].cells[0];
    var l = td.childNodes.length,
        t, tx = '',
        inputs, condSwitch = /^is[A-Z][a-z]+$/;

    t = td.firstChild;
    while (t != null) {
        if (typeof(t.tagName) == 'undefined' || t.tagName.toLowerCase() != 'table') {
            t = t.nextSibling;
            continue;
        }

        tx += '<condition>';
        inputs = t.getElementsByTagName('input');
        selects = t.getElementsByTagName('select');
        for (var j = 0; j < inputs.length; j++) {
            if (condSwitch.test(inputs[j].name)) {
                n = inputs[j].name.substr(2).toLowerCase();
                if (n == 'type') {
                    tx += '<type selected="' + inputs[j].checked + '">' +
                        (inputs[j + 1].checked ? '1,' : '') +
                        (inputs[j + 2].checked ? '2,' : '') +
                        (inputs[j + 3].checked ? '3,' : '') +
                        (inputs[j + 4].checked ? '4,' : '') +
                        (inputs[j + 5].checked ? '5,' : '') +
                        (inputs[j + 6].checked ? '6' : '') + '</type>';
                } else if (n == 'isFulltext') {
                    tx += '<fulltext selected="' + inputs[j].checked + '">' + htmlspecialchars(inputs[j + 1].value) + '\t' + htmlspecialchars(inputs[j + 1].value) + '</fulltext>';
                } else if (n == 'level') {
                    if (qti_which == 'questionnaire') {
                        continue;
                    } else {
                        tx += '<level selected="' + inputs[j].checked + '">' +
                            (inputs[j + 1].checked ? '1,' : '') +
                            (inputs[j + 2].checked ? '2,' : '') +
                            (inputs[j + 3].checked ? '3,' : '') +
                            (inputs[j + 4].checked ? '4,' : '') +
                            (inputs[j + 5].checked ? '5' : '') + '</level>';
                    }
                } else {
                    tx += '<' + n + ' selected="' + inputs[j].checked + '">' + inputs[j + 1].value + '</' + n + '>';
                }
            }
        }
        tx += '</condition>';
        t = t.nextSibling;
    }

    return ('<conditions>' + tx.replace(/,<\//g, '</') + '</conditions>');
}

/**
 * 限定只能輸入整數
 */
function intOnly(e) {
    var evn = (navigator.userAgent.indexOf('MSIE') > -1) ? event : e;
    if (evn.keyCode != 8 && (evn.keyCode < 48 || evn.keyCode > 57)) evn.keyCode = 0;
}

/**
 * 限定只能輸入實數
 */
function floatOnly(e) {
    var evn = (navigator.userAgent.indexOf('MSIE') > -1) ? event : e;
    if (evn.keyCode != 8 && evn.keyCode != 46 && (evn.keyCode < 48 || evn.keyCode > 57)) evn.keyCode = 0;
}

/**
 * 限定只能輸入實數
 */
function float6Only(e, pnumber) {
    if (pnumber !== 0 && pnumber !== '') {
        var rs = /^[0-9]+([0-9\.]{1,6})?$/.exec(pnumber);
        if (rs === null) {
            e.value = '';
        }
    }
}

/**
 * 資料型別檢查，若型別不對則清除輸入
 *
 * @param   html_form_input(text)   element     欲檢查的表單欄位
 * @param   string                  type        {int|float}
 */
function typeCheck(element, type) {
    switch (type) {
        case 'int':
            var re = /^-?[0-9]*$/;
            if (!re.test(element.value)) element.value = '';
            break;
        case 'float':
            var re = /^-?[0-9]*(\.[0-9]*)?$/;
            if (!re.test(element.value)) element.value = '';
            break;
    }
}

/**
 * 同步上下兩個工具列的全選 checkbox
 */
function search_selectItem(selAll) {
    var obj = document.getElementById('searchTable');
    var nodes = obj.getElementsByTagName('input');
    for (var i = 0; i < nodes.length; i++) {
        if (nodes.item(i).type == 'checkbox')
            nodes.item(i).checked = selAll;
    }

    var obj = document.getElementById('searchTable');
    obj.rows[(obj.rows.length - 1)].cells[0].innerHTML = obj.rows[0].cells[0].innerHTML;
    obj.rows[(obj.rows.length - 1)].cells[1].innerHTML = obj.rows[0].cells[1].innerHTML;

}

/**
 * 檢查是否全選
 */
function checkPick() {
    var obj = document.getElementById('searchTable');
    var nodes = obj.getElementsByTagName('input');

    var on = 0,
        off = 0;
    for (var i = 1; i < nodes.length; i++) {
        if (nodes.item(i).type == 'checkbox' && nodes.item(i).name == 'pick[]')
            if (nodes.item(i).checked) on++;
            else off++;
    }

    if (on > 0 && off == 0) { // 全選
        search_selectItem(true);
    } else {

        if (off > 0) { //   未全選所有的 checkbox
            document.getElementById("search_ck").checked = false;
            obj.rows[(obj.rows.length - 1)].cells[0].innerHTML = obj.rows[0].cells[0].innerHTML.replace(/search_ck/g, 'search_ck2');
            obj.rows[(obj.rows.length - 1)].cells[1].innerHTML = obj.rows[0].cells[1].innerHTML;
            document.getElementById("search_ck2").checked = false;
        }
    }
}

/**
 * 切換開放型問卷
 */
function switchForGuest(forGuest) {
    var td1 = forGuest.parentNode;
    var tr1 = td1.parentNode;
    var table = tr1.parentNode.parentNode;
    var tr = table.rows[tr1.rowIndex + 1];
    var td = tr.cells[tr.cells.length - 1];

    if (forGuest.value == '1') {
        document.getElementById('addACLbtn').disabled = true;
        table.rows[tr.rowIndex].style.display =
            table.rows[tr.rowIndex - 4].style.display =
            table.rows[tr.rowIndex - 5].style.display = 'none';
        forGuest.form.modifiable.checked = false;
    } else {
        document.getElementById('addACLbtn').disabled = false;
        table.rows[tr.rowIndex].style.display =
            table.rows[tr.rowIndex - 4].style.display =
            table.rows[tr.rowIndex - 5].style.display = '';
    }
    checkedTab1();
}

/**
 * 將表格1間格化
 */
function checkedTab1() {
    var tab1Table = document.getElementById("tab1Table");
    var ii = tab1Table.rows.length - 1;
    var cc = 1;
    for (var i = 1; i < ii; i++)
        if (tab1Table.rows[i].style.display != "none") {
            cc ^= 1;
            tab1Table.rows[i].className = "bg0" + (cc + 3) + " font01";
        }
}