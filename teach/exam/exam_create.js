lang = lang.replace('-', '_').toLowerCase();
chkBrowser();
var t=0;
var exam_default=3;
var j=1;

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
    obj = document.getElementById("trDelay");
    if (obj != null) obj.style.display = v ? "" : "none";
}


// 秀日曆的函數(checkbox)
function showDateInput(objName, state) {
    var obj = document.getElementById(objName);
    if (obj != null) {
        obj.style.display = state ? "" : "none";
    }
        
        // 作業
        if (qti_which === 'homework') {
            // 關閉作答日期
            if (objName === 'span_close_time') { 
                // 有設定才開啟 開放補繳列
                if (state === true) {
                    $('#trDelay').show();
                } else {
                    $('#ck_delay_time').attr('checked', false);
                    $('#delay_time').val('');
                    $('#trDelay').hide();
                }
            }
        }     
}

// 秀日曆的函數
function Calendar_setup(ifd, fmt, btn, shtime) {
    Calendar.setup({
        inputField  : ifd,
        ifFormat    : fmt,
        showsTime   : shtime,
        time24      : true,
        button      : btn,
        singleClick : true,
        weekNumbers : false,
        step        : 1
    });
}

var editor = new Object();
editor.setHTML = function(x)
{
    examDetail.loadXML(x);
};
var noSave = false;

var y=0;

window.onload = function () {
    Calendar_setup("begin_time" , "%Y-%m-%d %H:%M", "begin_time" , true);
    Calendar_setup("close_time" , "%Y-%m-%d %H:%M", "close_time" , true);
        if ($('#delay_time').length === 1) {
            Calendar_setup("delay_time" , "%Y-%m-%d %H:%M", "delay_time" , true);
        }
    Calendar_setup("announce_time" , "%Y-%m-%d %H:%M", "announce_time" , true);
    if (qti_which === 'homework') {
        Calendar_setup("delay_time" , "%Y-%m-%d %H:%M", "delay_time" , true);
    }
    hiddenRandomTab();
    if (isIE) releaseInputSelect();
    if (typeof(acl_lists) != 'undefined'){
        if (typeof(acl_lists[0]) != 'undefined' && acl_lists[0].length) generate_list(0);
        if (typeof(acl_lists[1]) != 'undefined' && acl_lists[1].length) generate_list(1);
    }
    xx();
    switchTab(cur_tab);

    xajax_check_temp(st_id, 'FCK.editor');
    window.setInterval(function(){if (noSave) xajax_save_temp(st_id, examDetail.xml);}, 100000);
    
    // 若是愛上互動的試卷則要做試卷設定的處理
    if (examType == 5 && sysEnableAppISunFuDon == 1) {
        if (qti_which == 'exam') {
            iSunFuDonSupportExam(examType);
        } else if (qti_which == 'questionnaire') {
            iSunFuDonSupportQuestionnaire(examType);
        }
    }
    
    // 行動測驗的預設值
    if ($('#qti_support_app').length){
        supportApp($("#qti_support_app").prop("checked"));
    }
    //進入編輯 設定刪除更多題目的按鈕與刪除欄位
    var sum=document.getElementsByName("randomForm")[0].getElementsByTagName("table")[0].getElementsByTagName("input").length;
    for(var x=0;x<sum;x++){
        if(document.getElementsByName("randomForm")[0].getElementsByTagName("table")[0].getElementsByTagName("input")[x].value==MSG_CUT){
            // document.getElementsByName("randomForm")[0].getElementsByTagName("table")[0].getElementsByTagName("input")[x].value=i+MSG_CUT;        
            $(document.getElementsByName("randomForm")[0].getElementsByTagName("table")[0].getElementsByTagName("input")[x].parentNode.parentNode.parentNode.parentNode).attr('id','t'+j);
             if(navigator.userAgent.indexOf("MSIE")>0){             
                 var kk = "t" + j;
                document.getElementsByName("randomForm")[0].getElementsByTagName("table")[0].getElementsByTagName("input")[x].onclick = Function("cutRadomItem('"+('t'+j)+"');");  

                j=j+1;
                //console.log(document.getElementsByName("randomForm")[0].getElementsByTagName("table")[0].getElementsByTagName("input")[x].onclick);
             }else{
                 document.getElementsByName("randomForm")[0].getElementsByTagName("table")[0].getElementsByTagName("input")[x].setAttribute("onclick","cutRadomItem('"+('t'+j)+"');");
                j=j+1;
            }
        }
    }
    
    if (document.getElementsByName("num")[1] && document.getElementsByName("num")[1].value == '' && randomForm.immediate_random_pick.checked) {
        document.getElementsByName("num")[1].value = document.getElementsByName("immediate_random_pick_amount")[0].value;
        document.getElementsByName("randomForm")[0].getElementsByTagName("table")[3].style.display = 'none';
        document.getElementsByName("randomForm")[0].getElementsByTagName("table")[5].style.display = 'none';
    }
        
        if (document.getElementsByName("num")[1]) {
            document.getElementsByTagName("hr")[1].parentNode.parentNode.setAttribute("style", "display:none;");
        }

    //檢查新舊版本隨機出題
//    checkRandomVersion();
        
};

/**
 * 釋放 input 及 textarea 禁止拖曳、複製事件
 */
function releaseInputSelect(){
    var nodes = document.getElementsByTagName('INPUT');
    for(var i=0; i<nodes.length; i++){
        if (nodes.item(i).type == 'text'){
            nodes.item(i).onselectstart = cancelbubble;
            nodes.item(i).oncontextmenu = cancelbubble;
        }
    }
    nodes = document.getElementsByTagName('TEXTAREA');
    for(var i=0; i<nodes.length; i++){
        nodes.item(i).onselectstart = cancelbubble;
        nodes.item(i).oncontextmenu = cancelbubble;
    }
}

/**
 * 取消事件沸升 ( called by "releaseInputSelect()" )
 */
function cancelbubble(){
    event.cancelBubble = true;
}

/**
 * 切換 Tab 選單
 */
var cur_idx = -1;
function switchTab(n){
    

//    var x = document.getElementById('sysRadioBtn11');
//    if(n==4 && qti_which == 'exam' && x.checked==true){
//        if(document.getElementById('immediate_random_pick_score').value==''){
//            alert(CO_TOTAL_SORCE);return;
//        }
//        
//    }
        
    if (cur_idx == n) return;
    
    if (n > 0) {
    	var obj = document.getElementById('saveForm');
    	
	    // 檢查是否有輸入標題
	    if (!chk_multi_lang_input(1, true, MSG_LANG_HINT)) {
	        tabsSelect(1);
	        return;
	    }
	
	    // 有發布並設定起迄時間,則要檢查
	    for (var i = 0; i < obj.rdoPublish.length; i++) {
	        if (obj.rdoPublish[i].checked)
	            var rdo = obj.rdoPublish[i].value;
	    }
	    if (rdo == 2 && obj.ck_begin_time.checked && obj.ck_close_time.checked) {
	        var val1 = obj.begin_time.value.replace(/[\D]/ig, '');
	        var val2 = obj.close_time.value.replace(/[\D]/ig, '');
	        if (parseInt(val1) >= parseInt(val2)) {
	            alert(MSG_DATE_ERR);
	            return;
	        }
	    }
	    
	    // 有設定補繳日期
	    if (qti_which === 'homework' && obj.ck_delay_time.checked) {
	        // 關閉作答日期也必須設定
	        if (obj.ck_close_time.checked === false) {
	            alert(MSG_DATE_ERR2);
	            return;
	        }

	        // 必須大於關閉作答日期
	        if (window.console) {console.log(val1);}if (window.console) {console.log(val2);}
	        var val1 = obj.close_time.value.replace(/[\D]/ig, '');
	        var val2 = obj.delay_time.value.replace(/[\D]/ig, '');
	        if (parseInt(val1) >= parseInt(val2)) {
	            alert(MSG_DATE_ERR3);
	            tabsSelect(1);
	            return;
	        }
	    }
    
    }
    /* 愛上互動不提供隨機排列 */
    if (qti_which == 'exam'){
        if ($('#ex_type').val()=='5'){
            if ((cur_idx == 2)&&(n==3)){
                switchTab(4);
                return;
            }

            if ((cur_idx == 4)&&(n==3)){
                switchTab(2);
                return;
            }
        }
    }
        // 測驗-隨機-試卷預覽（randomMode=2）時，若沒有給予總分，則提示「請輸入總分」
    if (n == 4 && qti_which === 'exam' && $("input[type='radio'][name='randomMode']:checked").val() === '2') {
            var ts = document.getElementsByName('immediate_random_pick_score')[0].value;

            if (ts == '') {
                alert(MSG_INPUT_TOTAL2);
                return false;
            }
    }
    
    document.getElementById('srTable').style.display = (n == 1 && (qti_which == 'exam' ? document.getElementById('sysRadioBtn10').checked : true)) ? '':'none';

    if (cur_idx != -1)
    {
        var obj1 = document.getElementById('TitleID'+(n+1));
        tabsMouseEvent(obj1, 2)
    }

    for(var i=0; i<5; i++){
        if (i == n){
            if (n == 2) { genExamPaper(); document.getElementById('toolPanel').style.top = 100; }
            if (n == 4) prevExamPaper();
            
            document.getElementById('tabContent' + i).style.display = '';
        }
        else{
            document.getElementById('tabContent' + i).style.display = 'none';
        }
    }
    cur_idx = n;
}

/**
 * 產生 checkbox HTML (called by "travelNode()", "genExamPaper()" )
 */
function checkBox(word){
    return '<input type="checkbox" value="' + word + '" title="' + word + '">';
}

/**
 * 畫出本節點左方樹狀線條
 */
function drawLine(isBlock, isnTail){
    var ret = '';
    if (lineStr.length){
        for(var i=0; i<lineStr.length-(isBlock?1:0); i++){
            ret += (lineStr.charAt(i) == '1') ?
                '<img src="/theme/' + theme + '/teach/vertline.gif" valign="absmiddle" border="0" width="16" height="18">' :
                '&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        ret += '<img src="/theme/' + theme + '/teach/' + (isnTail?'node':'lastnode') + '.gif" valign="absmiddle" border="0" width="16" height="18">' +
               '<img src="/theme/' + theme + '/teach/' + (isBlock?'icon_folder':'icon_all') + '.gif" valign="absmiddle" border="0" width="16" height="16">';
        return ret;
    }
    else{
            return '<img src="/theme/' + theme + '/teach/' + (isnTail?'node':'lastnode') + '.gif" valign="absmiddle" border="0" width="16" height="18">' +
                   '<img src="/theme/' + theme + '/teach/' + (isBlock?'icon_folder':'icon_all') + '.gif" valign="absmiddle" border="0" width="16" height="16">';
    }
}

/**
 * 取得區塊文字
 */
function getSectionPM(node){
    var nodes = node.getElementsByTagName('presentation_material');
    return (nodes.length < 1) ? '' : nodes[0].firstChild.firstChild.firstChild.firstChild.nodeValue;
}

/**
 * 取得題目題型文字
 */
function getItemType(words){
    // words = '</td>' + words + '<td>';
    words = words.replace(/^<(\/)?td>/ig, "").replace(/<(\/)?td>$/ig, "");
    var ar = ('>' + words).split(/<\/td>\s*<td[^>]*/i);    // 避免有些欄位是空的
    var type = qti_which=='questionnaire' ? ar[ar.length-6] : ar[ar.length-7];
    return '<span style="color: gray">[' + type.substring(1) + ']</span>';
}

/**
 * 取得題目其它文字
 */
function getItemOther(words){
    // words = '</td>' + words + '<td>';
    words = words.replace(/^<(\/)?td>/ig, "").replace(/<(\/)?td>$/ig, "");
    var ar = ('>' + words).split(/<\/td>\s*<td[^>]*/i);     // 避免有些欄位是空的
    for(var i = 0; i < ar.length; i++)
    {
        ar[i] = ar[i].substring(1);
        if (i >= 2 && i <= 6 && ar[i] == '') ar[i] = 0;
        if (i == 7 && ar[i] == '')  ar[i] = 3;
    }
    if (qti_which == 'questionnaire')
    {
        var title = ar.slice(0, ar.length-6);
        var other = ar.slice(ar.length-5);
        return title + '<span style="color: gray">[' + other + ']</span>';
    }
    else
    {
        var title = ar.slice(0, ar.length-7);
        var other = ar.slice(ar.length-6, ar.length-1);
        var hard  = hard_levels[ar[ar.length-1]];
        return title + '<span style="color: gray">[' + other + '][' + hard + ']</span>';
    }
}

/**
 * 遞迴將試題 XML，轉換為大題操作畫面 ( called by "genExamPaper()" )
 */
function travelNode(node){
    var ret = '';
    var item_serial = 1;
    var sec_serial = 1;

    var nodes = node.childNodes;
    for(var i=0; i<nodes.length; i++){
        rowStyle = rowStyle == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"' ;
        switch(nodes[i].tagName){
            case 'section' :
            case 'assessment' :
                lineStr += (nodes[i].nextSibling == null) ? '0' : '1';
                ret += '<tr ' + rowStyle + '><td>' + drawLine(true, nodes[i].nextSibling) +
                       checkBox(nodes[i].getAttribute('id')) + '<span style="width: 3em; text-align: right; font-weight: bold">' + (sec_serial++) + '.</span>' +
                       '[<a href="javascript:;" onclick="collect(\'' + nodes[i].getAttribute('id') +
                       '\'); return false;" title="' + MSG_MV_SEC + '">' +
                       block_title[1] + '</a>]&nbsp;&nbsp;' + getSectionPM(nodes[i]) +
                       '</td></tr>' + travelNode(nodes[i]);
                lineStr = lineStr.replace(/.$/, '');
                break;
            case 'item' :
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
function genExamPaper(){
    rowStyle = 'class="bg04 font01"';
    lineStr = '';
        $('#paperPanel').append('Please wait moment ...');
    var IH = '<table border="0" cellpadding="1" cellspacing="1" width="100%" style="border-collapse: collapse" class="box01">' +
             '<tr class="bg04 font01"><td>' + ('<img src="/theme/' + theme + '/teach/' +
             (examDetail.documentElement.hasChildNodes?'icon_folder':'icon_all') +
             '.gif" valign="absmiddle" border="0">') + // checkBox('qti') +
             '[<a href="javascript:;" onclick="collect(\'qti\'); return false;" title="' + MSG_MV_SEC + '">' +
             block_title[0] + '</a>]</td></tr>' +
             travelNode(examDetail.documentElement) +
             '</table>';
    document.getElementById('paperPanel').innerHTML = IH;
}


/**
 * 試卷預覽
 */
var previewDisplayAnswer = true; // 是否要顯示標準答案
function prevExamPaper()
{
    if (qti_which != 'questionnaire')
    {
        var pt = document.getElementById('previewTable');
        pt.rows[       0        ].cells[0].getElementsByTagName('input')[3].value = (previewDisplayAnswer ? hide_answer : show_answer);
        pt.rows[pt.rows.length-1].cells[0].getElementsByTagName('input')[3].value = (previewDisplayAnswer ? hide_answer : show_answer);
    }

    var xmlHttp = XmlHttp.create();
    xmlHttp.open('POST', 'exam_preview.php' + (previewDisplayAnswer ? '?pda=1' : ''), false);

    var randomForm = document.getElementById('randomForm');
    // var msgwin = window.open('javascript:document.write("<body leftMargin=0 topMargin=0 marginwidth=0 marginheight=0><table width=200 height=100><tr><td align=center><h3>Please wait moment ...</h3></td></tr></table></" + "body>")', '', 'width=200,height=100,top=250,left=400,status=0,resizable=0,scrollbars=0');

    if (randomForm.immediate_random_pick.checked)
    {
        var amount = parseInt(randomForm.immediate_random_pick_amount.value);
        if (isNaN(amount) || amount < 1 || amount > 200) amount = 50;
        var score  = parseInt(randomForm.immediate_random_pick_score.value);
        if (isNaN(score)) score = 0;
        var prevRandom = XmlDocument.create();
        prevRandom.loadXML('<wm_immediate_random_generate_qti threshold_score=""><form>' +
                              getSearchXml(qti_which=='questionnaire') +
                              '<amount    selected="true">' + amount + '</amount>' +
                              '<score     selected="true">' + score  + '</score>' +
                            '</form></wm_immediate_random_generate_qti>');
        xmlHttp.send(prevRandom);
    }
    else
        xmlHttp.send(examDetail);
    document.getElementById('examPreview').innerHTML = xmlHttp.responseText;
    // msgwin.close();
    if (qti_which != 'questionnaire') previewDisplayAnswer ^= true;
}

/**
 * main process of GetElementById() (called by GetElementById() )
 */
function travelAllElement(node, id){
    var ret;
    if (node.nodeType == 1){
        if (node.getAttribute('id') == id) return node;
        var nodes = node.childNodes;
        for(var i=0; i<nodes.length; i++){
            if (nodes[i].nodeType == 1){
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
function GetElementById(dom, id){
    return travelAllElement(dom.documentElement, id);
}

/**
 * 將試題搬到 id=label 之大題下
 */
function collect(label){
    var sectionNode = GetElementById(examDetail,label);
    var currNode;
    var objForm = document.getElementById('paperPanel');
    var nodes = objForm.getElementsByTagName('input');
    for(var i=nodes.length-1; i>=0; i--){
        if (nodes[i].value == label) continue;
        if (nodes[i].type == 'checkbox' && nodes[i].checked){
            currNode = GetElementById(examDetail,nodes[i].value);
            if (currNode != null && typeof(currNode) == 'object'){
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
function isMyParent(self, node){
    var curNode = self;
    while(node != curNode){
        if ((curNode = curNode.parentNode) === null) return false;
    }
    return true;
}

/**
 * 將 procNode 節點搬到 parendNode 之下 (called by "collect()" )
 */
function moveToSection(parentNode, procNode){
    if (isMyParent(parentNode, procNode)) {alert(MSG_X_MV_CHD); return;}
    if (procNode.tagName == 'questestinterop') return;
    var newNode = procNode.cloneNode(true);
    // 避免移入大題時，順序倒反，所以要插到第一個位置
    if (parentNode.hasChildNodes())
        parentNode.insertBefore(newNode, parentNode.firstChild);
    else
        parentNode.appendChild(newNode);
    procNode.parentNode.removeChild(procNode);
}

function moveNode(nodeName, dir){
    var node = GetElementById(examDetail, nodeName);
    if (node == null) return;
    var parent = node.parentNode;

    if (dir == -1){
        var prev = node.previousSibling;
        if (prev != null){
            var newNode = node.cloneNode(true);
            parent.insertBefore(newNode, prev);
            parent.removeChild(node);
        }
    }
    else{
        var next = node.nextSibling;
        if(next != null){
            var newNode = next.cloneNode(true);
            parent.insertBefore(newNode, node);
            parent.removeChild(next);
        }
    }
}

/**
 * 試卷大題控制
 */
function paperTuning(n){
    
    
    var obj;
    switch(n){
        case 1: // 新增大題
            while (examDetail.selectSingleNode("//section[@id='section-"+sectionIdx+"']")) sectionIdx++;
            var newNode = examDetail.createElement('section');
            newNode.setAttribute('id', 'section-' + sectionIdx ++ );
            examDetail.documentElement.appendChild(newNode);
            break;
        case 2:
        case 3:
            var nodes = document.getElementById('paperPanel').getElementsByTagName('input');
            if (n == 2){ // 刪除大題
                var c=0;
                for(var i=nodes.length-1; i>=0; i--){
                    if(nodes[i].type == 'checkbox' &&
                       nodes[i].checked &&
                       nodes[i].value.substr(0, 8) == 'section-'
                      ){
                        obj = GetElementById(examDetail, nodes[i].value);
                        obj.parentNode.removeChild(obj);
                        c++;
                    }
                }
                if (c == 0) {alert(MSG_SEL_SEC); return ;}
            }
            else{ // 刪除題目
                var c=0;
                for(var i=nodes.length-1; i>=0; i--){
                    if(nodes[i].type == 'checkbox' &&
                       nodes[i].checked &&
                       nodes[i].value.substr(0, 8) != 'section-' &&
                       nodes[i].value != 'qti'
                      ){
                        obj = GetElementById(examDetail, nodes[i].value);
                        obj.parentNode.removeChild(obj);
                        c++;
                    }
                }
                if (c == 0) {alert(MSG_SEL_FIRST); return ;}
            }
            break;
        case 4: // 大題文字
            var nodes = document.getElementById('paperPanel').getElementsByTagName('input');
            var obj = document.getElementById('pmForm');
            obj.ident.value = '';
            for(var i=0; i<nodes.length; i++){
                if(nodes[i].type == 'checkbox' &&
                   nodes[i].checked &&
                   nodes[i].value.substr(0, 8) == 'section-'
                  ){
                      obj.ident.value = nodes[i].value;
                      var curNode = GetElementById(examDetail, nodes[i].value);
                      pmNodes = curNode.getElementsByTagName('presentation_material');
                      if (pmNodes.length > 0){
                          obj.presentation_material.value = pmNodes[0].firstChild.firstChild.firstChild.firstChild.nodeValue;
                      }
                      else
                          obj.presentation_material.value = '';
                      break;
                }
            }
            if (obj.ident.value == ''){alert(MSG_SEL_SEC); return ;}
            displayDialogWindow('sectionTextDialog');
            break;
        case 5: // 上移
            var nodes = document.getElementById('paperPanel').getElementsByTagName('input');
            var reserves = new Array();
            var c=0;
            for(var i=0; i< nodes.length; i++){
                if (nodes[i].type == 'checkbox' && nodes[i].checked) { moveNode(nodes[i].value, -1); reserves[reserves.length] = nodes[i].value; c++;}
            }
            if (c == 0) {alert(MSG_SEL_FIRST); return ;}
            break;
        case 6: // 下移
            var nodes = document.getElementById('paperPanel').getElementsByTagName('input');
            var reserves = new Array();
            var c=0;
            for(var i=nodes.length-1; i>=0; i--){
                if (nodes[i].type == 'checkbox' && nodes[i].checked) { moveNode(nodes[i].value, 1); reserves[reserves.length] = nodes[i].value; c++;}
            }
            if (c == 0) {alert(MSG_SEL_FIRST); return ;}
            break;
        case 7: // 指定分數
            var nodes = document.getElementById('paperPanel').getElementsByTagName('input');
            var ret = '';
            for (var i = 0; i < nodes.length; i++)
                if (nodes[i].type == 'checkbox' && nodes[i].checked)
                    ret += (i + ',');

            if (ret == '') {alert(MSG_SEL_BEFORE); return ;}
            ret = ret.replace(/,$/, '');

                        var score = $("#sectionScoreDialog input[name='score']").val();
            if (score == '' || score == null || score.search(/^[0-9]+(\.[0-9]+)?$/) !== 0) return;
            score = parseFloat(score);
            var aa = ret.split(',');
            for (var i = 0; i < aa.length; i++)
                assign_score(nodes[aa[i]].value, score);
                        
                        // 清空數值與關閉視窗
                        $("#sectionScoreDialog input[name='score']").val('');
                        $("#sectionScoreDialog input[name='action']").val('');
                        $("#sectionScoreDialog").hide();
            break;
        case 8: // 平均配分
                        var score = $("#sectionScoreDialog input[name='score']").val();
            if (score == '' || score == null || score.search(/^[0-9]+(\.[0-9]+)?$/) !== 0) return;
            score = parseFloat(score);
            var nodes = examDetail.getElementsByTagName('item');
            if (nodes == null || nodes.length == 0) return;
            var outcome = Math.floor((score / nodes.length) * Math.pow(10,2)) / Math.pow(10,2);
            var tail = score - (outcome * nodes.length);
            for(var i=0; i<nodes.length-1; i++)
                nodes[i].setAttribute('score', outcome);
            nodes[nodes.length-1].setAttribute('score', outcome + tail);
                        
                        // 清空數值與關閉視窗
                        $("#sectionScoreDialog input[name='score']").val('');
                        $("#sectionScoreDialog input[name='action']").val('');
                        $("#sectionScoreDialog").hide();
            break;
        case 9: // 全選
        case 10 : // 全消
            var nodes = document.getElementById('paperPanel').getElementsByTagName('input');
            var mode = n%2 ? true : false;
            for(var i=0; i< nodes.length; i++)
                if (nodes[i].type == 'checkbox') nodes[i].checked = mode;
            break;
    }

    // 重新產生列表
    if (n < 9) {noSave = true; if (n != 4) genExamPaper(); }

    // 如果上下移，就恢復勾選
    if (typeof(reserves) != 'undefined' && reserves.length)
    {
        nodes = document.getElementById('paperPanel').getElementsByTagName('input');
        for (var j = 0; j < reserves.length; j++)
        {
            for(var i=nodes.length-1; i>=0; i--){
                if (nodes[i].type == 'checkbox' && nodes[i].value == reserves[j]) nodes[i].checked = true;
            }
        }
    }
}

function assign_score(nodeName, score){
    var node = examDetail.documentElement.selectSingleNode('//item[@id="' + nodeName + '"]');
    if (node != null){
        node.setAttribute('score', score);
    }
}

/**
 * 輸入區塊文字
 */
function enterPM(){
    var obj = document.getElementById('pmForm');
    var ident = obj.ident.value;
    var node = GetElementById(examDetail, ident);
    if (node.tagName != 'section'){
        alert('node incorrect.'); return;
    }

    var nodes = node.getElementsByTagName('presentation_material');
    
    if (obj.presentation_material.value.length === 0) {
        obj.presentation_material.value = ' ';
    }
    
    if (nodes.length < 1){
        var newNode = examDetail.createElement('presentation_material');
        var curNode = node.insertBefore(newNode, node.firstChild);
        newNode = examDetail.createElement('flow_mat'); curNode = curNode.appendChild(newNode);
        newNode = examDetail.createElement('material'); curNode = curNode.appendChild(newNode);
        newNode = examDetail.createElement('mattext');  curNode = curNode.appendChild(newNode);
        newNode = examDetail.createTextNode(obj.presentation_material.value);
        curNode.appendChild(newNode);
        // alert(node.xml);
    }
    else
        nodes[0].firstChild.firstChild.firstChild.firstChild.nodeValue = obj.presentation_material.value;

    document.getElementById('sectionTextDialog').style.display='none';
    genExamPaper();
}

// ======================================================================================================================
/**
 * 產生 s 到 e 的 <option> 選項，其中 =k 則加 selected
 */
function genSerial(s,e,k){
    for(var i=s; i<=e; i++) document.writeln('<option value="' + i + (k == i?'" selected>':'">') + i + '</option>');
}

/**
 * 自定日期顯示與否
 */
function customTime(n){
    document.getElementById('customTimePal').style.display = (n == 'user_define')?'':'none';
    document.getElementById('score_publish_type').style.display = (n == 'never')?'none':'block';
}

/**
 * 題目搜尋時，若 text 有填入資料則前面 checkbox 自動勾選
 */
function checkSelect(obj){
    if (obj.tagName.toLowerCase() != 'select')
        obj.previousSibling.previousSibling.checked = (obj.value != '');
    else
        obj.parentNode.previousSibling.firstChild.checked = (obj.value != '');
}

/**
 * 題型、難易度
 */
function checkSelect2(obj) {
    var $chk = jQuery(obj).parent().parent().prev().find('input:checkbox'), i = 0;

    if (obj.checked) {
        $chk.attr('checked', true);
    }
    if (jQuery(obj).parent().parent().find('input:checkbox:checked').length <= 0) {
        $chk.removeAttr('checked');
    }
}
/**
 * 啟動亂數出題
 */
function randomCheck(obj){
    var panel = document.getElementById('tabContent3');
    var nodes = panel.getElementsByTagName('input');
    for(var i=0; i<9; i++){
        if ((nodes[i].type == 'checkbox' || nodes[i].type == 'text') && obj.name != nodes[i].name )
            nodes[i].disabled = !obj.checked;
    }

    if (!obj.checked) nodes[9].disabled = true;
    else if (nodes[8].checked) nodes[9].disabled = false;
}

/**
 * 啟動亂數出題
 */
function randomCheck2(obj){
    /*
    var panel = document.getElementById('tabContent3');
    var nodes = panel.getElementsByTagName('input');
    for(var i=11; i<nodes.length; i++){
    */
    var panel = document.getElementById('randomForm');
    var nodes = panel.getElementsByTagName('input');
    for(var i=0; i<nodes.length; i++){
        if ((nodes[i].type == 'checkbox' || nodes[i].type == 'text') && obj.name != nodes[i].name )
            nodes[i].disabled = !obj.checked;
    }
    var nodes = panel.getElementsByTagName('select');
    for(var i=0; i<nodes.length; i++){
        nodes[i].disabled = !obj.checked;
    }
}


/**
 * 選擇或取消選擇所有選出的題目
 */
function checkAll(check){
    var obj = document.getElementById('searchResult');
    var nodes = obj.getElementsByTagName('input');
    for(var i=nodes.length-1; i>=0; i--){
        if (nodes[i].type != 'checkbox') continue;
        nodes[i].checked = check;
    }
}

/**
 * 將搜尋的題目加入試卷
 */
function pickItem(){
    var obj = document.getElementById('searchResult');
    var nodes = obj.getElementsByTagName('input');
    var ih = '', id = '', root = examDetail.documentElement, newNode, newText;
    var tableNode = document.getElementById('searchTable');
    var removes = new Array(); var rmIndex = 0;
    var isExisted; var countdown = nodes.length-1;

    NowPickedNum = root.childNodes.length;
    if (NowPickedNum >= MaxPickedNum)
    {
        alert(msg_overNumber);
        return;
    }

    if (NowPickedNum == 0)
    {
        alert(MSG_PICK_ITEM_CUE);
    }

    for(var i=0; i<nodes.length; i++){
        window.status = countdown--;
        if (nodes[i].type == 'checkbox' && nodes[i].checked && nodes[i].value != '@' && $(nodes[i]).attr('disabled') !== 'disabled') {
               NowPickedNum = root.childNodes.length;
               if (NowPickedNum == MaxPickedNum)
            {
                alert(msg_overNumber);
                break;
               }

            isExisted = root.selectSingleNode('//item[@id="' + nodes[i].value + '"]');
            if (isExisted != null)
            {
                nodes[i].disabled = true;
                alert(MSG_IGN_REPEAT);
                continue;
            }

            // 刪除多餘的column
            if (nodes[i].parentNode.parentNode.childNodes.length == 11)
                nodes[i].parentNode.parentNode.removeChild(nodes[i].parentNode.parentNode.childNodes[10]);

            var tr = nodes[i].parentNode.parentNode.childNodes;
            ih = '<td>' + tr[3].innerHTML + '</td><td>' + tr[2].innerHTML +'</td>';
            for(var tmp = 4; tmp < tr.length; tmp++)
            {
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
    for(var i=rmIndex-1; i>=0; i--) tableNode.deleteRow(removes[i]);
}

/**
 * 向 server 取得搜尋題目
 */
function search_item(){
    var selTypes = [], selLevels = [];
    jQuery('.item_type input:checkbox:checked').each(function () {
        selTypes.push(jQuery(this).val());
    });
    jQuery('.hard_level input:checkbox:checked').each(function () {
        selLevels.push(jQuery(this).val());
    });
    var topPanel = document.getElementById('searchForm');
    var queryXml = '<form>' +
                      '<version   selected="' + topPanel.isVersion.checked   + '">' + topPanel.version.value   + '</version>' +
                      '<volume    selected="' + topPanel.isVolume.checked    + '">' + topPanel.volume.value    + '</volume>' +
                      '<chapter   selected="' + topPanel.isChapter.checked   + '">' + topPanel.chapter.value   + '</chapter>' +
                      '<paragraph selected="' + topPanel.isParagraph.checked + '">' + topPanel.paragraph.value + '</paragraph>' +
                      '<section   selected="' + topPanel.isSection.checked   + '">' + topPanel.section.value   + '</section>' +
                      '<type      selected="' + topPanel.isType.checked      + '">' + selTypes.join(',')      + '</type>' +
                      (qti_which!='questionnaire'?('<level     selected="' + topPanel.isLevel.checked     + '">' + selLevels.join(',') + '</level>'):'') +
                      '<fulltext  selected="' + topPanel.isFulltext.checked  + '">' + htmlspecialchars(topPanel.fulltext.value) + '\t' + htmlspecialchars(topPanel.fulltext.value) + '</fulltext>' +
                          (qti_which=='exam'?('<num>' + topPanel.num.value + '</num>'):'') +
                      '<scope>' + topPanel.scope.value + '</scope>' +
                      '<rowspage>' + topPanel.rows_page_share.value + '</rowspage>'+
                      '<pages>' + topPanel.pages.value + '</pages>'+
                      '<exam_type>' + $('#ex_type').val() + '</exam_type>'+
                     '</form>';
    var xmlHttp = XmlHttp.create();
    var xmlVars = XmlDocument.create();
    xmlVars.loadXML(queryXml);
    xmlHttp.open('POST', 'item_search.php', false);
    xmlHttp.send(xmlVars);
    var ret = xmlVars.loadXML(xmlHttp.responseText);
    if (ret == false) { alert(MSG_NOT_XML); return;}
    var root = xmlVars.documentElement;
    if (root.tagName == 'errorlevel'){
        switch(root.firstChild.nodeValue){
            case '1':
                alert(MSG_INCR_XML); return;
                break;
            case '2':
                alert(MSG_INCR_FORM); return;
                break;
            case '3':
                if (/<\w+\s+selected="true">/.test(queryXml))
                    alert(MSG_NO_RESULT);
                else
                    alert(MSG_NO_ITEMS);
                return;
                break;
            default:
                alert(MSG_UNKNOW_ERR); return;
                break;
        }
    }
    if (root.tagName != 'questestinterop' ) { alert('Returning XML\'s root node nust <questestinterop>'); return;}
    var nodes = root.childNodes;
    var total_shareitem = nodes[0].firstChild.nodeValue;
    if (topPanel.rows_page_share.value =='-1'){var rows_page_now = '10';}else{var rows_page_now = topPanel.rows_page_share.value;}
    var pagelength = Math.ceil(total_shareitem/rows_page_now);
    var htm = '<form id="shareForm" style="display:inline"><table border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="box01" id="searchTable">' +
              '<tr class="cssTrEvn font01">'+
              '<td align="left">' +
              '<input type="checkbox" name="search_ck" id="search_ck" onclick="checkAll(this.checked);" value="@">' +
              '</td>' +
              '<td colspan="' + (qti_which == 'questionnaire' ? 7 : 8) + '" align="center">' +
              '<font class=font01>' + MSG_PAGE_NUM + '</font>'+
              '<select onchange="search_page(this.value)">';
              for(var s=1; s<pagelength+1; s++){
        htm+= ('<option value="'+ s + '"');
              if (s == topPanel.pages.value){
                    htm+= ('selected="selected"');
              }
        htm+= ('>' + s + '</option>');
              }
        htm+= '</select> '+
              '<font class=font01>' + MSG_PAGE_EACH + '</font>'+
              '<select name="rps" onchange="go_rowspage_share(this.value,'+
               '' + total_shareitem + ')">';
              for(var r=0; r<rowspages.length; r++){
        htm+= ('<option value="'+ rowspages[r] +'"');
              if (rowspages[r] == rows_page_now){
                    htm+= ('selected="selected"');
              }
        htm+= ('>' + rowspagesn[r] + '</option>');

              }

        htm+= '</select> '+
              '<font class=font01>' + MSG_PAGE_ITEM + '</font> '+
              '<input type="button" value="' + MSG_SEARCHPAGE_TOP + '"  onclick="search_page(1);" id="s_pagebtn1" class="cssBtn" ';
        if (topPanel.pages.value=='1'){
               htm+= ('disabled');
               }
        htm+= '> ' +
              '<input type="button" value="' + MSG_SEARCHPAGE_UP + '"   onclick="search_page(' + topPanel.pages.value + '-1);" id="s_pagebtn2" class="cssBtn" ';
        if (topPanel.pages.value=='1'){
               htm+= ('disabled');
               }
        htm+= '> ' +
              '<input type="button" value="' + MSG_SEARCHPAGE_DOWN + '" onclick="search_page(' + topPanel.pages.value + '+1);" id="s_pagebtn3" class="cssBtn" ';
        if (topPanel.pages.value==pagelength){
               htm+= ('disabled');
               }
        htm+= '> ' +
              '<input type="button" value="' + MSG_SEARCHPAGE_END + '"  onclick="search_page(' + pagelength + ');" id="s_pagebtn4" class="cssBtn" ';
                if (topPanel.pages.value==pagelength){
               htm+= ('disabled');
               }
        htm+= '> ' +
              '</td>'+
              '<td align="right">' +
              '<input type="button" value="' + btms[0] + '" onclick="pickItem();" class="cssBtn">&nbsp;&nbsp;&nbsp;' +
              '</td></tr><tr class="bg02 font01">';
          for(var i=0; i<srTables.length; i++) {
              if (qti_which != 'questionnaire' && i == 9)
                  htm += ('<td style="display:none;">' + srTables[i] + '</td>');
              else
                  htm += ('<td>' + srTables[i] + '</td>');
          }
          htm += '</tr>';
    var properties;


    var col = '';
    var serial_no = (topPanel.pages.value - 1) * rows_page_now + 1;
    var nodeValue;
    for(var i=1; i<nodes.length; i++){
        if (nodes[i].tagName == 'item'){
            col = col == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"';
            htm += '<tr ' + col + '>';
            properties = nodes[i].childNodes;
            htm += '<td width="30"><input type="checkbox" name="pick[]" value="' + properties[0].firstChild.nodeValue + '" onclick="checkPick();"></td><td align="right" style="padding-right: 1em">' + (serial_no++) + '</td>';
            for(var j=1; j<properties.length; j++){
                nodeValue = properties[j].firstChild !== null ? properties[j].firstChild.nodeValue : '';
                switch(j){
                    case 1:  htm += '<td width="40">' + types[nodeValue] + '</td>'; break;
                    case 2:  htm += '<td width="300">' + nodeValue + '</td>'; break;
                    case 8:  htm += (qti_which == 'questionnaire') ? '' : ('<td style="display:none">' + nodeValue + '</td><td width="50">' + hard_levels[nodeValue] + '</td>'); break;
                    default: htm += '<td width="20">' + nodeValue + '</td>'; break;
                }
            }
            htm += '</tr>';
        }
    }
    htm += '<tr class="cssTrEvn font01">'+
           '<td align="left">' +
           // '<input type="checkbox" onclick="checkAll(true);" value="@">' +
           '</td>' +
           '<td colspan="' + (qti_which == 'questionnaire' ? 7 : 8) + '" align="center">' +
           '</td>'+
           '<td>'+
           '<input type="button" value="' + btms[0] + '" onclick="pickItem();" class="cssBtn">&nbsp;&nbsp;&nbsp;' +
           '</td></tr></table></form>';

    document.getElementById('searchResult').innerHTML = htm;
    document.getElementById('srTable').style.display='';
    checkPick();
}

function go_rowspage_share(n,m){
    var form = document.getElementById('searchForm');
    form.pages.value = 1;
    form.rows_page_share.value = n;
    // form.document.getElementById('rows_page_share').value = n;
    search_item();
}

function search_page(n){
    var form = document.getElementById('searchForm');
    if(n>0){
        // form.document.getElementById('pages').value = n;
        form.pages.value = n;
        search_item();
    }
}

/**
 * 將試卷輸出
 */
function ExportContent()
{
    var obj = document.getElementById("examPreview");
    var form = document.getElementById('exportForm');
    form.table_html.value = obj.innerHTML;
    form.submit();
}

/**
 * 將試卷存檔
 */
function saveContent(){
    var obj = document.getElementById('saveForm');

    if (qti_which == 'exam') {
        prevExamPaper(); //取得試卷預覽題目
        // var item_length = 0;    //題目數量
        // var obj_tr = document.getElementsByTagName('tr');    //取得所有tr
        // for(var i=0; i<obj_tr.length; i++){
        // if(obj_tr[i].className.match(/item/)){    //找出題目
        // item_length++;
        // }
        // }
        // if(item_length===0){    //若等於0代表此試卷無題目
        // alert(MSG_NO_ITEMS_IN_EXAM);
        // return;
        // }
        var obj_from = document.getElementById("responseForm");

        if (obj_from == null) {
            alert(MSG_NO_ITEMS_IN_EXAM);
            return;
        }
        
        // 提示計分方式有變動
        if ($('#count_type').data('old-value') !== $('#count_type').val()) {
            if (!(confirm(MSG_COUNT_TYPE_CHANGE))) {
                return;
            }
        }
    }
    
   
    if ($('#ex_type').val()=='5'){
        var type4 = $("#previewTable [item-type=4]").length;
        var type5 = $("#previewTable [item-type=2]").length;
        var total = parseInt(type4+type5);
        if (total > 0) {
        	alert(MSG_ITEM_TYPE_ERROR);
        	tabsSelect(3);
            return;	
        }
    }

    var checkgroup = true;
    for (var i = 0; i < acl_lists.length; i++) {
        var str = acl_lists[i] + '';
        if (str != '') {
            if (str.indexOf(',') > -1) {
                if (str.indexOf('@1.') > -1) {
                    checkgroup = false;
                }
            }
        }
    }
    if (!checkgroup) {
        alert(MSG_GROUP_REQ);
        return;
    }

    //    if (qti_which == 'exam'){
    //            prevExamPaper();    //取得試卷預覽題目
    //            var item_length = 0;    //題目數量
    //            var obj_tr = document.getElementsByTagName('tr');    //取得所有tr
    //            for(var i=0; i<obj_tr.length; i++){
    //                if(obj_tr[i].className.match(/item/)){    //找出題目
    //                    item_length++;
    //                }
    //            }
    //            if(item_length===0){    //若等於0代表此試卷無題目
    //                alert(MSG_NO_ITEMS_IN_EXAM);
    //                return;
    //            }
    //    }

    if (qti_which != 'questionnaire' && // 問卷就不必管分數
        obj.count_type.value != 'none' && // 如果設為不計分，也不管分數
        obj.percent.value && // 如果比例為 0 也不管分數
        $("input[type='radio'][name='randomMode']:checked").val() === '1' // 如果是手動選題
    ) {
        var sn = examDetail.getElementsByTagName('item');
        var ts = 0;
        for (var i = 0; i < sn.length; i++) {
            // Redmine#3531 單題配分全部小於1，會跳出總配分0分的訊息視窗 by Small 2012/07/31
            // ts += parseInt(sn[i].getAttribute('score'));
            ts += parseFloat(sn[i].getAttribute('score'));
        }

        if (isNaN(ts)) {
            if (!confirm(MSG_SCORE_REM)) {
                return;
            }
        }
        if (sn.length && ts == 0 && !confirm(MSG_SCORE_REM)) return;
    }

    if (qti_which == 'exam' && obj.do_interval.value <= 0) {
        tabsSelect(1);
        alert(DO_INTERVAL_TIP);
        return;
    }

    var nodes = obj.getElementsByTagName('input');

    // 檢查是否有輸入標題
    if (!chk_multi_lang_input(1, true, MSG_LANG_HINT)) {
        tabsSelect(1);
        return;
    }

    // 有發布並設定起迄時間,則要檢查
    for (var i = 0; i < obj.rdoPublish.length; i++) {
        if (obj.rdoPublish[i].checked)
            var rdo = obj.rdoPublish[i].value;
    }
    if (rdo == 2 && obj.ck_begin_time.checked && obj.ck_close_time.checked) {
        var val1 = obj.begin_time.value.replace(/[\D]/ig, '');
        var val2 = obj.close_time.value.replace(/[\D]/ig, '');
        if (parseInt(val1) >= parseInt(val2)) {
            alert(MSG_DATE_ERR);
            return;
        }
    }
    
    // 有啟用提醒
    if (obj.alert_check.checked) {
    	if (obj.alert_login.checked === false && obj.alert_email.checked === false) {
            alert(MSG_ALERT_FILL);
            return;
        }
    }

    if (parseInt(val1).toString().substr(0,8) != parseInt(val2).toString().substr(0,8) && obj.alert_check1.checked) {
    	if (obj.alert_login1.checked === false && obj.alert_email1.checked === false) {
            alert(MSG_ALERT_FILL1);
            return;
        }
    }

    // 有設定補繳日期
    if (qti_which === 'homework' && obj.ck_delay_time.checked) {
        // 關閉作答日期也必須設定
        if (obj.ck_close_time.checked === false) {
            alert(MSG_DATE_ERR2);
            return;
        }

        // 必須大於關閉作答日期
        if (window.console) {console.log(val1);}if (window.console) {console.log(val2);}
        var val1 = obj.close_time.value.replace(/[\D]/ig, '');
        var val2 = obj.delay_time.value.replace(/[\D]/ig, '');
        if (parseInt(val1) >= parseInt(val2)) {
            alert(MSG_DATE_ERR3);
            tabsSelect(1);
            return;
        }
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
        if (parseInt(randomForm.immediate_random_pick_amount.value) === 0) {
            tabsSelect(2);
            alert(MSG_IRGA_REQ_ZERO);
            randomForm.immediate_random_pick_amount.focus();
            return;
        }
        if (parseInt(randomForm.immediate_random_pick_amount.value) === 0) {
            tabsSelect(2);
            alert(MSG_IRGA_REQ_ZERO);
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

    if (obj.announce_type.value == 'close_time' && obj.ck_close_time.checked == false) {
        alert(MSG_CLOSE_TIME);
        return false;
    }

    xajax_clean_temp(st_id);
    obj.submit();
    $('.save-content').attr('disabled', true);
}

/**
 * 啟始對話框的位置
 */
function displayDialogWindow(objName){
    var obj = document.getElementById(objName);
    // 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(480)] 再左移 10 個 pixel
    obj.style.left  = parseInt($('.box01').prop('width'), 10) + 20;
    // 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
    obj.style.top   = document.body.scrollTop  + 10;
        $("#sectionScoreDialog").hide();
        
    obj.style.display='';
}

/**
 * 啟始分數對話框的位置
 */
function displayDynamicDialogWindow(objName, titleName, action){
    
    var obj = document.getElementById(objName);
    // 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(480)] 再左移 10 個 pixel
    obj.style.left  = parseInt($('.box01').prop('width'), 10) + 910;
        
    // 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
    obj.style.top   = document.body.scrollTop  + 9;
                        
        // 清空數值與關閉視窗
        $("#sectionScoreDialog input[name='score']").val('');
        $("#sectionScoreDialog input[name='action']").val('');
        $("#sectionTextDialog").hide();
        
        // 設定輸入框標題、事件編號
        $('#' + objName).find('td[id^=TitleID]').text(titleName);
        $('#' + objName).find('#action').val(action);
        
    obj.style.display='';
        $("input[name='score']").focus();
}

/**
 * 自動挑題的選項連動
 */
function selectRandomMode(v)
{
    if (qti_which == 'exam'){
        document.getElementById('sum_view').style.display="none";
    }
    var x = document.getElementById('tabContent1');
    if (v == '0'){
	    x.getElementsByTagName('table')[0].style.display = '';
    }else{
        x.getElementsByTagName('table')[0].style.display = 'none';
    }
    var y = x.getElementsByTagName('form');
    if (v == '1')
    {
        if (qti_which == 'exam') document.getElementById('sysRadioBtn10').checked = true;
        y[0].style.display = 'inline';
        y[1].style.display = 'none';
        document.getElementById('srTable').style.display = '';
//        document.getElementById('test').style.display = 'none';
        if (!examDetail.documentElement.hasChildNodes) search_item();    // 第一次選擇手動挑題自動搜尋一次
	}else if (v == '0'){
        if (qti_which == 'exam') document.getElementById('sysRadioBtn10').checked = false;
        if (qti_which == 'exam') document.getElementById('sysRadioBtn11').checked = false;
        y[0].style.display = 'none';
        y[1].style.display = 'none';
        document.getElementById('srTable').style.display = 'none';
	}else{
        if (qti_which == 'exam') document.getElementById('sysRadioBtn11').checked = true;
        y[0].style.display = 'none';
        y[1].style.display = 'inline';
        y[1].immediate_random_pick.checked = true;
        var t = document.getElementsByTagName('table')[1];
        for(var i=6; i<12; i++) t.rows[0].cells[i].style.display ='none';
        exam_default=1;
        y[1].immediate_random_pick_amount.style.display = 'inline';
        document.getElementById('srTable').style.display = 'none';
    }
    

    document.getElementsByName("cutRad")[0].style.display='none';
    document.getElementsByName("cutRad")[1].style.display='none';
}

/**
 * 工具列自動隨拉動畫面而移動
 */
window.onscroll=function()
{
    document.getElementById('toolPanel').style.top   = document.body.scrollTop  + 100;
};

/**
 * 將隨機選題的條件，由 Form 表單轉為 XML
 *
 * @param   bool    isQuestionnaire     是否為問卷 (若是則不處理難易度)
 * @return  string                      傳回 XML
 */
function getSearchXml(isQuestionnaire)
{
    var randomForm = document.getElementById('randomForm');
    var td = randomForm.getElementsByTagName('table')[0].rows[3].cells[0];
    var l = td.childNodes.length, t, tx='', inputs, condSwitch = /^is[A-Z][a-z]+$/;

    // 檢查個別隨機數字
//    checkRandomAmount();
    
    t = td.firstChild;
    while (t != null)
    {
        if (typeof(t.tagName) == 'undefined' || t.tagName.toLowerCase() != 'table')
        {
            t = t.nextSibling;
            continue;
        }

        tx += '<condition>';
        inputs = t.getElementsByTagName('input');
        selects = t.getElementsByTagName('select');
        for (var j=0; j<inputs.length; j++)
        {
            if (condSwitch.test(inputs[j].name))
            {
                n = inputs[j].name.substr(2).toLowerCase();
                if (n == 'type')
                {
                    tx += '<type selected="' + inputs[j].checked + '">' +
                          (inputs[j+1].checked ? '1,' : '') +
                          (inputs[j+2].checked ? '2,' : '') +
                          (inputs[j+3].checked ? '3,' : '') +
                          (inputs[j+4].checked ? '4,' : '') +
                          (inputs[j+5].checked ? '5,' : '') +
                          (inputs[j+6].checked ? '6' : '') + '</type>';
                }
                else if (n == 'isFulltext')
                {
                    tx += '<fulltext selected="' + inputs[j].checked + '">' + htmlspecialchars(inputs[j+1].value) + '\t' + htmlspecialchars(inputs[j+1].value) + '</fulltext>';
                }
                else if (n == 'num')
                {
                    tx += '<num selected="true">' + htmlspecialchars(inputs[j+1].value) + '</num>';
                }
                else if (n == 'level')
                {
                    if (qti_which == 'questionnaire')
                    {
                        continue;
                    }
                    else
                    {
                        tx += '<level selected="' + inputs[j].checked + '">' +
                          (inputs[j+1].checked ? '1,' : '') +
                          (inputs[j+2].checked ? '2,' : '') +
                          (inputs[j+3].checked ? '3,' : '') +
                          (inputs[j+4].checked ? '4,' : '') +
                          (inputs[j+5].checked ? '5' : '') + '</level>';
                    }
                }
                else
                {
                    tx += '<' + n + ' selected="' + inputs[j].checked + '">' + inputs[j+1].value + '</' + n + '>';
                }
            }
            
//            if (inputs[j].name == 'eachRandomAmount')
//            {
//                tx += '<each_random_amount selected="'+((parseInt(inputs[j].value) >= 0)?'true':'false')+'">'+parseInt(inputs[j].value)+'</each_random_amount>';
//            }
            
            if(inputs[j].name=='num')
                {
                    if (qti_which == "exam")
                        tx += '<num selected="true">' + htmlspecialchars(inputs[j].value) + '</num>';
                    
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
function intOnly(e)
{
    var evn = (event) ? event : e;
    if (evn.keyCode != 8 && (evn.keyCode < 48 || evn.keyCode > 57)) return;
}

/**
 * 限定只能輸入實數
 */
function floatOnly(e)
{
    var evn = (event) ? event : e;
    if (evn.keyCode != 8 && evn.keyCode != 46 && (evn.keyCode < 48 || evn.keyCode > 57)) return;
}

/**
 * 資料型別檢查，若型別不對則清除輸入
 *
 * @param   html_form_input(text)   element     欲檢查的表單欄位
 * @param   string                  type        {int|float}
 */
function typeCheck(element, type)
{
    switch(type)
    {
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
function search_selectItem(selAll){
    var obj = document.getElementById('searchTable');
    var nodes = obj.getElementsByTagName('input');
    for(var i=0; i<nodes.length; i++){
        if (nodes.item(i).type == 'checkbox')
            nodes.item(i).checked = selAll;
    }

    var obj = document.getElementById('searchTable');
    obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[0].cells[0].innerHTML;
    obj.rows[(obj.rows.length-1)].cells[1].innerHTML = obj.rows[0].cells[1].innerHTML;

}

/**
 * 檢查是否全選
 */
function checkPick()
{
    var obj = document.getElementById('searchTable');
    var nodes = obj.getElementsByTagName('input');

    var on=0, off=0;
    for(var i=1; i<nodes.length; i++){
        if (nodes.item(i).type == 'checkbox' && nodes.item(i).name == 'pick[]')
            if (nodes.item(i).checked) on++; else off++;
    }

    if (on > 0 && off == 0){  // 全選
        search_selectItem(true);
    }else{

        if (off > 0){        //   未全選所有的 checkbox
            document.getElementById("search_ck").checked = false;
            obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[0].cells[0].innerHTML.replace(/search_ck/g, 'search_ck2');
            obj.rows[(obj.rows.length-1)].cells[1].innerHTML = obj.rows[0].cells[1].innerHTML;
            document.getElementById("search_ck2").checked = false;
        }
    }
}

/**
 * 切換開放型問卷
 */
function switchForGuest(forGuest)
{
    var td1   = forGuest.parentNode;
    var tr1   = td1.parentNode;
    var table = tr1.parentNode.parentNode;
    var tr    = table.rows[tr1.rowIndex+1];
    var td    = tr.cells[tr.cells.length-1];

    if (forGuest.value == '1')
    {
        document.getElementById('addACLbtn').disabled = true;
        table.rows[tr.rowIndex].style.display         =
        table.rows[tr.rowIndex-4].style.display       =
        table.rows[tr.rowIndex-5].style.display       = 'none';
        forGuest.form.modifiable.checked              = false;
    }
    else
    {
        document.getElementById('addACLbtn').disabled = false;
        table.rows[tr.rowIndex].style.display         =
        table.rows[tr.rowIndex-4].style.display       =
        table.rows[tr.rowIndex-5].style.display       = '';
    }
    checkedTab1();
}

/**
 * 將表格1間格化
 */
function checkedTab1()
{
    var tab1Table = document.getElementById("tab1Table");
    var ii = tab1Table.rows.length-1;
    var cc = 1;
    for(var i=1; i<ii; i++)
        if (tab1Table.rows[i].style.display != "none")
        {
            cc ^= 1;
            tab1Table.rows[i].className = "bg0" + (cc + 3) + " font01";
        }
}

function setAllowAttachment() {
    if ($('#ck_attachment_required').prop('checked') === true) {
        $("input[name='setting[upload]'][value='1']").prop('checked', true);
    }
}

function setAllowAttachment2() {
    if ($("input[name='setting[upload]'][value='1']").prop('checked') === true) {
        $('#ck_attachment_required').prop('checked', true)
    }
}

/**
 * 加入新的隨機出題條件
 */

 function createRadomItem(obj) {
     var node = obj.parentNode.parentNode.parentNode.parentNode.rows[3].cells[0].lastChild.cloneNode(true);

     $(node).attr("id", "t" + j);
     var kk = "t" + j;

     node.getElementsByTagName("input")[0].style.display = 'inline';
     if (navigator.userAgent.indexOf("MSIE") > 0) {
         node.getElementsByTagName("input")[0].onclick = function() {
             cutRadomItem(kk);
         };
     } else {
         node.getElementsByTagName("input")[0].setAttribute("onclick", "cutRadomItem('" + kk + "');");
     }

     obj.parentNode.parentNode.parentNode.parentNode.rows[3].cells[0].appendChild(node);
     node.getElementsByTagName("input")[23].value = 0;

     var nodes = node.getElementsByTagName('input');
     for(var i=0; i<nodes.length; i++){
         if (nodes.item(i).type == 'checkbox')
             nodes.item(i).checked = false;
         
         if (nodes.item(i).type == 'text') 
        	 nodes.item(i).value = '';
     }

     j = j + 1;
     calculation();

 }


/**
 * 減少新的隨機出題條件
 */
 
function cutRadomItem(times) {
    // alert(times);
    $("#"+times).remove();
        calculation();
    return;
}

/**
 * 計算總和數量
 */
function calculation(){
    document.getElementById("immediate_random_pick_amount").value=0;
    var sum=document.getElementsByName("randomForm")[0].getElementsByTagName("table")[0].getElementsByTagName("input").length;
    for(var x=0;x<sum;x++){
        if(document.getElementsByName("randomForm")[0].getElementsByTagName("table")[0].getElementsByTagName("input")[x].name=="num"){
            if(document.getElementsByName("randomForm")[0].getElementsByTagName("table")[0].getElementsByTagName("input")[x].value=='')
                document.getElementsByName("randomForm")[0].getElementsByTagName("table")[0].getElementsByTagName("input")[x].value=0;
            document.getElementById("immediate_random_pick_amount").value=parseInt(document.getElementById("immediate_random_pick_amount").value)+parseInt(document.getElementsByName("randomForm")[0].getElementsByTagName("table")[0].getElementsByTagName("input")[x].value);
        }
    }
    
}

/**
 * 變更試卷用途
 *
 * @param {Boolean} supportValue: true(支援)，false(不支援)
 **/
function supportApp(supportValue) {
    if (!supportValue &&
        (qti_which == 'exam') &&
        ($('#ex_type').val()=='5')
    ){
        $("#qti_support_app").prop("checked", true);
        return false;
    }

    if (supportValue && $('#ex_type').val()=='5') {
        // 如果是行動測驗，則要直接限制1. 每頁1題，2. 不限制切換視窗，3. 逾時處理直接交卷
        // 每頁1題
        top.frames['c_main'].document.getElementById('item_per_page').value = 1;
        top.frames['c_main'].document.getElementById('item_per_page').disabled = true;
        // 不限制切換視窗
        top.frames['c_main'].document.getElementById('ctrl_window').value = 'none';
        top.frames['c_main'].document.getElementById('ctrl_window').disabled = true;
        // 逾時處理直接交卷
        top.frames['c_main'].document.getElementById('ctrl_timeout').value = 'auto_submit';
        top.frames['c_main'].document.getElementById('ctrl_timeout').disabled = true;
    } else {
        // 如果不是，則不需要限制
        // 每頁1題
        top.frames['c_main'].document.getElementById('item_per_page').disabled = false;
        // 不限制切換視窗
        top.frames['c_main'].document.getElementById('ctrl_window').disabled = false;
        // 逾時處理直接交卷
        top.frames['c_main'].document.getElementById('ctrl_timeout').disabled = false;
    }
}

$(function() {
    // 開放附檔作答，選否，則取消勾選附檔必須
    $("input[name='setting[upload]']").click(function() {
        if ($(this).val() === '0') {
            $('#ck_attachment_required').prop('checked', false);
        }
    });
});

/**
 * 愛上互動的操作預設值設定
 **/
function iSunFuDonSupportExam (type) {
	if (type== 5) {
        // 如果是愛上互動，則要直接限制1. 每頁1題，2. 不限制切換視窗，3. 逾時處理直接交卷
        // 發佈改為準備中且不允許變更
        if (prog_type == 'exam_create.php'){
            top.frames['c_main'].document.getElementById('sysRadioBtn6').checked = true;
            top.frames['c_main'].document.getElementById('sysRadioBtn7').disabled = true;
            top.frames['c_main'].document.getElementById('begin_time').value = '';
            top.frames['c_main'].document.getElementById('close_time').value = '';
            statListDateShow(1);
        }else{
            if (top.frames['c_main'].document.getElementById('sysRadioBtn6').checked){
                top.frames['c_main'].document.getElementById('sysRadioBtn7').disabled = true;
            }else if (top.frames['c_main'].document.getElementById('sysRadioBtn7').checked){
                top.frames['c_main'].document.getElementById('sysRadioBtn6').disabled = true;
            }
        }

        // 不允許變更開放作答時間
        top.frames['c_main'].document.getElementById('ck_begin_time').checked = false;
        top.frames['c_main'].document.getElementById('ck_begin_time').disabled = true;
        // 不允許變更關閉作答時間
        top.frames['c_main'].document.getElementById('ck_close_time').checked = false;
        top.frames['c_main'].document.getElementById('ck_close_time').disabled = true;
        // 不允許變更計分方式，且預設為取第一次
        top.frames['c_main'].document.getElementById('count_type').value = 'first';
        top.frames['c_main'].document.getElementById('count_type').disabled = true;
        // 不允許變更測驗對象
        top.frames['c_main'].document.getElementById('addACLbtn').disabled = true;
        // 不允許變更作答次數，且預設一次
        top.frames['c_main'].document.getElementById('do_times').value = '1';
        top.frames['c_main'].document.getElementById('do_times').disabled = true;
        // 不允許變更每頁顯示題數，且預設為一頁一題
        top.frames['c_main'].document.getElementById('item_per_page').value = '1';
        top.frames['c_main'].document.getElementById('item_per_page').disabled = true;
        // 不限制切換視窗
        top.frames['c_main'].document.getElementById('ctrl_window').value = 'none';
        top.frames['c_main'].document.getElementById('ctrl_window').disabled = true;
        // 不限制"翻頁控制"
        top.frames['c_main'].document.getElementById('ctrl_paging').value = 'none';
        top.frames['c_main'].document.getElementById('ctrl_paging').disabled = true;
        // 不允許變更逾時處理，且預設為強制繳卷
        top.frames['c_main'].document.getElementById('ctrl_timeout').value = 'auto_submit';
        top.frames['c_main'].document.getElementById('ctrl_timeout').disabled = true;

        // 結果公布
        var objAnnounceType = top.frames['c_main'].document.getElementById('announce_type');
        if ((objAnnounceType.value != 'never')&&(objAnnounceType.value != 'now')){
            top.frames['c_main'].document.getElementById('announce_type').value = 'never';
        }
        customTime('never');
        objAnnounceType.options[2].style.display = 'none';
        objAnnounceType.options[3].style.display = 'none';

        // 愛上互動的測驗，必須為行動測驗，因此要將行動測試打勾，並套用其預設控制 - 每頁1題、不限制切換視窗、逾時處理
        if ($('#qti_support_app').length){
            if (!$("#qti_support_app").prop("checked")){
                $("#qti_support_app").prop("checked", true);
            }
        }
        supportApp(true);

        if ((_tab4 = document.getElementById('TitleID4')) != null) {
            var x = _tab4.cellIndex;
            _tab4.style.display='none';
            _tab4.parentNode.cells[x-1].style.display='none';
            _tab4.parentNode.cells[x+1].style.display='none';
        }

        /* 隱藏填充與配合 */
        $('.item_type_4').hide();
        $('.item_type_6').hide();

        /* 新增測驗試卷時，愛上互動 只允許自行挑題 */
        if (prog_type == 'exam_create.php'){
            selectRandomMode(1);
            $('#srTable').hide();
        }

	} else {
        // 發佈
        top.frames['c_main'].document.getElementById('sysRadioBtn7').disabled = false;
        // 開放作答時間
        top.frames['c_main'].document.getElementById('ck_begin_time').disabled = false;
        // 關閉作答時間
        top.frames['c_main'].document.getElementById('ck_close_time').disabled = false;
        // 計分方式
        top.frames['c_main'].document.getElementById('count_type').disabled = false;
        // 測驗對象
        top.frames['c_main'].document.getElementById('addACLbtn').disabled = false;
        // 作答次數
        top.frames['c_main'].document.getElementById('do_times').disabled = false;
        // 每頁顯示題數
        top.frames['c_main'].document.getElementById('item_per_page').disabled = false;
        // 逾時處理
        top.frames['c_main'].document.getElementById('ctrl_timeout').disabled = false;
        // 公布答案
        top.frames['c_main'].document.getElementById('announce_type').disabled = false;
        // 切換視窗
        top.frames['c_main'].document.getElementById('ctrl_window').disabled = false;
        // 翻頁控制
        top.frames['c_main'].document.getElementById('ctrl_paging').disabled = false;

        // 結果公布
        var objAnnounceType = top.frames['c_main'].document.getElementById('announce_type');
        objAnnounceType.options[2].style.display = '';
        objAnnounceType.options[3].style.display = '';

        if ((_tab4 = document.getElementById('TitleID4')) != null) {
            var x = _tab4.cellIndex;
            _tab4.style.display='';
            _tab4.parentNode.cells[x-1].style.display='';
            _tab4.parentNode.cells[x+1].style.display='';
        }

        /* 隱藏填充與配合 */
        $('.item_type_4').show();
        $('.item_type_6').show();

        /* 新增測驗試卷時，愛上互動 只允許自行挑題 */
        if (prog_type == 'exam_create.php'){
            selectRandomMode(0);
        }

	}
}

function iSunFuDonSupportQuestionnaire (type) {
    if (type == 5) {
        // 發佈改為準備中且不允許變更
        if (prog_type == 'exam_create.php'){
            top.frames['c_main'].document.getElementById('sysRadioBtn6').checked = true;
            top.frames['c_main'].document.getElementById('sysRadioBtn7').disabled = true;
            top.frames['c_main'].document.getElementById('begin_time').value = '';
            top.frames['c_main'].document.getElementById('close_time').value = '';
            statListDateShow(1);
        }else{
            if (top.frames['c_main'].document.getElementById('sysRadioBtn6').checked){
                top.frames['c_main'].document.getElementById('sysRadioBtn7').disabled = true;
            }else if (top.frames['c_main'].document.getElementById('sysRadioBtn7').checked){
                top.frames['c_main'].document.getElementById('sysRadioBtn6').disabled = true;
            }
        }

        // 不以附檔作答
        top.frames['c_main'].document.getElementById('sysRadioBtn9').checked = true;
        top.frames['c_main'].document.getElementById('sysRadioBtn8').disabled = true;
        // 記名
        top.frames['c_main'].document.getElementById('sysRadioBtn10').checked = true;
        top.frames['c_main'].document.getElementById('sysRadioBtn11').disabled = true;
        // 重複作答
        top.frames['c_main'].document.getElementById('modifiable').checked = false;
        top.frames['c_main'].document.getElementById('modifiable').disabled = true;
        // 封閉
        top.frames['c_main'].document.getElementById('sysRadioBtn12').checked = true;
        top.frames['c_main'].document.getElementById('sysRadioBtn12').disabled = true;
        top.frames['c_main'].document.getElementById('sysRadioBtn13').disabled = true;
        // 不允許變更測驗對象
        top.frames['c_main'].document.getElementById('addACLbtn').disabled = true;

        // 結果公布
        var objAnnounceType = top.frames['c_main'].document.getElementById('announce_type');
        if ((objAnnounceType.value != 'never')&&(objAnnounceType.value != 'now')){
            top.frames['c_main'].document.getElementById('announce_type').value = 'never';
        }
        objAnnounceType.options[2].style.display = 'none';
        objAnnounceType.options[3].style.display = 'none';
        customTime('never');

        /* 隱藏填充與配合 */
        $('.item_type_4').hide();
        $('.item_type_6').hide();

	} else {
        // 發佈
        top.frames['c_main'].document.getElementById('sysRadioBtn7').disabled = false;
        // 以附檔作答
        top.frames['c_main'].document.getElementById('sysRadioBtn8').disabled = false;
        // 記名
        top.frames['c_main'].document.getElementById('sysRadioBtn11').disabled = false;
        // 重複作答
        top.frames['c_main'].document.getElementById('modifiable').disabled = false;
        // 封閉
        top.frames['c_main'].document.getElementById('sysRadioBtn12').disabled = false;
        top.frames['c_main'].document.getElementById('sysRadioBtn13').disabled = false;
        // 測驗對象
        top.frames['c_main'].document.getElementById('addACLbtn').disabled = false;
        // 公布答案
        top.frames['c_main'].document.getElementById('announce_type').disabled = false;

        // 結果公布
        var objAnnounceType = top.frames['c_main'].document.getElementById('announce_type');
        objAnnounceType.options[2].style.display = '';
        objAnnounceType.options[3].style.display = '';

        /* 隱藏填充與配合 */
        $('.item_type_4').show();
        $('.item_type_6').show();

	}
}