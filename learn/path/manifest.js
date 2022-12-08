var xmlDoc = XmlDocument.create();
var ndm, organization, resources, defaultOrgId = '';
var trackingActivityObj;
var first = true;
var myFrame;
var myPanel;
// var isIE = (navigator.userAgent.indexOf('MSIE') > -1);
var ua = navigator.userAgent.toLowerCase();
if (ua.match(/rv:([\d.]+)\) like gecko/) || ua.match(/msie ([\d.]+)/)) {
    var isIE = true;
} else {
    var isIE = false;
}
var learnRecord;
var elems = new Array();

/*
DT.NAVBoolean                     = 1 << 20;
DT.NAVVocabulary_event             = 1 << 21;

var _nav                                   = new Object();
    _nav['event']                           = new Array( W, DT.NAVVocabulary_event);
    _nav['control_mode_enabled']           = new Object();
    _nav['control_mode_enabled']['choice'] = new Array(R , DT.NAVBoolean);
    _nav['control_mode_enabled']['flow']   = new Array(R , DT.NAVBoolean);
    _nav['event_permitted']                = new Object();
    _nav['event_permitted']['continue']       = new Array(R , DT.NAVBoolean);
    _nav['event_permitted']['previous']       = new Array(R , DT.NAVBoolean);
*/

var ss                            = new Object();
    ss.controlMode                = new Object();
    ss.sequencingRules            = new Object();
    ss.limitConditions            = new Object();
    ss.auxiliaryResources        = new Object();
    ss.rollupRules                = new Object();
    ss.objectives                = new Object();
    ss.randomizationControls    = new Object();
    ss.deliveryControls            = new Object();

if (justPreview)
{
    window.onerror = function(){return true;};
    document.onclick = function(){return false;};
}

/**
 * 取得SCORM版本(預設1.2)
 */
function getSCORMVersion() {
    if (!xmlDoc) return;
    var manifest = xmlDoc.selectSingleNode('manifest');
    if (!manifest) return;
    var version = manifest.getAttribute('version');
    if (version) {
        return version;
    }
    else {
        var sn = xmlDoc.xml.indexOf('imsss:sequencing');
        return (sn > 0 ? '1.3' : '1.2');
    }
}

/**
 * 載入後啟動程序
 */
SCORM_VERSION = '1.2';

function xmlProcessor()
{
    window.status='';

    ndm = new NavigationDataModel();

    SCORM_VERSION = getSCORMVersion();

    // 取得預設路徑的 Organization
    xmlDoc.setProperty('SelectionLanguage', 'XPath');
    organization = xmlDoc.selectSingleNode('/manifest/organizations/organization[@identifier=../@default or position()=1]');
    // xmlDoc.setProperty('SelectionLanguage', 'XSLPattern');
    if (organization == null){
        alert('organization not found.'); return;
    }

    // 取得 resources 段
    resources = xmlDoc.selectSingleNode('/manifest/resources');
    // document.getElementById('fetchResourceForm').iv.value = resources.getAttribute('iv');

    // 產生教材路徑 HTML
    myPanel = document.getElementById('displayPanel');
    rm_whitespace(myPanel);
    myPanel.innerHTML = generateOrganization(organization);
    if (myPanel.getElementsByTagName('li').length > 0) {
            myPanel.getElementsByTagName('ul')[0].style.margin='0';
    } else {
            myPanel.innerHTML = '<h4 style="text-align: center;">'+MSG_NO_DATA+'</h4>';
    }
    var tmp = parent.parent.s_main.document.getElementsByTagName('H2');
    if (tmp && tmp.length > 0) tmp[0].parentNode.removeChild(tmp[0]); // tmp[0].innerHTML = '<Br />' + MSG_FINISH;

    // 拉開frame
    myFrame = parent.parent.document.getElementById('envClassRoom');
    parent.parent.document.getElementById('s_catalog').scrolling = 'no';
    if (myFrame.cols != '312,*')
        myFrame.cols = '312,*';

    // 調整路徑顯示區的大小
    // resizeDisplayPanel();

    parent.adjustFrameHeight();

    // 取得上一次離開的節點，若 沒有最後一次的節點 或 不存在 或 節點被設定隱藏
        var tmpidentifierref = '';
        var parent_node = '';
        var tmp_obj = organization.selectSingleNode('//item[@identifier="' + globalCurrentActivity + '"]')
    if (tmp_obj != null) {
        tmpidentifierref = tmp_obj.getAttribute('identifierref');
        parent_node = tmp_obj.parentNode;
    }

        if (globalCurrentActivity == '' || organization.selectSingleNode('//item[@identifier="' + globalCurrentActivity + '"]') == null || organization.selectSingleNode('//item[(@identifier="' + globalCurrentActivity + '") and (not(@isvisible) or @isvisible!="false") and (not(@disabled) or @disabled!="true")]') === null  || resources.selectSingleNode('//resource[@identifier="' + tmpidentifierref + '"]') == null || parent_node.getAttribute('isvisible')=="false"){

        // globalCurrentActivity = organization.selectSingleNode('//item[@identifierref]');
                // 取有 identifierref
                globalCurrentActivity = organization.selectSingleNode('//item[@identifierref][(not(@isvisible) or @isvisible!="false") and (not(@disabled) or @disabled!="true")]');
        if (globalCurrentActivity == null){
            parent.disable_control(false);
            var tmp = parent.parent.s_main.document.getElementsByTagName('H2');
            if (tmp && tmp.length > 0)
                tmp[0].parentNode.removeChild(tmp[0]); // tmp[0].innerHTML = '<BR />' + noavailable;
            // alert(noavailable);
            return;
            //alert(organization.xml); return;
        }
        globalCurrentActivity = globalCurrentActivity.getAttribute('identifier');
                
                var tmpidentifierref = globalCurrentActivity.substr(2);
                var tmphref = resources.selectSingleNode('./resource[@identifier="' + tmpidentifierref + '"]');
                if (tmphref == null) {
                    tmpCurrentActivity = resources.selectSingleNode('//resource[(not(@href) or @href!="aboult:blank")]');
                    tmpCurrentActivity = tmpCurrentActivity.getAttribute('identifier');
                    if (organization.selectSingleNode('//item[@identifierref="' + tmpCurrentActivity + '"]')) {
                        globalCurrentActivity = organization.selectSingleNode('//item[@identifierref="' + tmpCurrentActivity + '"]').getAttribute('identifier');
                    }
                }
    }

    // 進入第一個 Activity 或回到上次離開的 Activity
    parent.parent.globalState.CurrentActivity = globalCurrentActivity;
    launchActivity(null, globalCurrentActivity);
}

function serializeToJson(serializer){
    var _string = '{';
    for(var ix in serializer)
    {
        var row = serializer[ix];
        _string += '"' + row.name + '":"' + row.value + '",';
    }
    var end =_string.length - 1;
    _string = _string.substr(0, end);
    _string += '}';

    return JSON.parse(_string);
}

function doUnload() {
    parent.parent.document.getElementById('s_catalog').scrolling = 'auto';
    if ((myFrame && myFrame.cols !== undefined) && (myFrame.cols != '0,*'))
        myFrame.cols = '0,*';

    var objForm = document.getElementById('fetchResourceForm');

    if (objForm.href.value != 'about:blank') {
        objForm.href.value = 'about:blank';
        objForm.target = 'empty';
        //objForm.submit();

        var params = $(objForm).serializeArray();
        var last_data = serializeToJson(params);

        $.ajax({
            'type': 'POST',
            'dataType': 'json',
            'async': false,
            'data': last_data,
            'url': '/learn/path/SCORM_fetchResource.php'
        });
    }
}

// 多久回寫一次閱讀結束時間（1000為1秒）
var traceReadingIntervalTime = 60000;

window.onload=function(){
    if (typeof(_SYNC_NON_IMPLEMENTED) != "undefined") _SYNC_NON_IMPLEMENTED = true;
    xmlDoc.async = false;
    xmlDoc.resolveExternals = false;

    if(document.implementation.createDocument)
    {
/*
        xmlDoc.addEventListener('load', xmlProcessor, false);
        xmlDoc.onreadystatechange = function()
        {
            if (xmlDoc.readyState == 4)
            {
                xmlDoc.onreadystatechange = null;
                xmlProcessor();
            }
        };
*/
        // fix by lubo , chrome & safari begin
        //console.log("[debug] "+ typeof xmlDoc.load );
        if( typeof xmlDoc.load == "undefined" ){
            var    xmlHttp    = XmlHttp.create();
            xmlHttp.open('GET', 'SCORM_loadCA.php'+ser, xmlDoc.async);
            xmlHttp.send();
            var    ret    = xmlHttp.responseText;
            xmlDoc.loadXML(ret);
        }else{
            xmlDoc.load('SCORM_loadCA.php'+ser);
        }
        // xmlDoc.load('SCORM_loadCA.php'+ser);
        // fix by lubo , chrome & safari end
                xmlProcessor();
//        setTimeout('xmlProcessor()', 3000);
    }
    else
    {
        if (!xmlDoc.load('SCORM_loadCA.php'+ser)){
            alert('Loading XML file failure.'); return;
        }
        xmlProcessor();
    }
    
    window.setInterval("setReading('end', traceReadingIntervalTime);", traceReadingIntervalTime);
};

window.onbeforeunload=function(){
    var x ;
    try
    {
        if (typeof(x = parent.parent.s_main.document.body.onunload) == 'function')
        {
            x();
            parent.parent.s_main.document.body.onunload = null;
        }
    }
    catch(e)
    {
    }

    doUnload();
};

var isOnIOS = navigator.userAgent.match(/iPad/i)|| navigator.userAgent.match(/iPhone/i);

if (isOnIOS) {
    window.addEventListener("pagehide", function(evt){
        doUnload();
    }, false);
}


/**
 * 全展開或全收攏
 */
var expandingFlag = 'none';
function expandingAll() {
    var nodes = myPanel.getElementsByTagName('img');
    var icon = '/public/images/' + (expandingFlag ? 'icon_expand_inc.png' : 'icon_expand_dec.png');
    for (var i = 0; i < nodes.length; i++) {
        nodes[i].parentNode.parentNode.parentNode.lastChild.style.display = expandingFlag;
        nodes[i].src = icon;
    }
    expandingFlag = expandingFlag ? '' : 'none';
}

/**
 * 單一 Cluster 收攏或展開
 */
function expanding(obj, mode){
    var ulObj = obj.parentNode.parentNode.lastChild;
    var m = (typeof(mode) == 'undefined') ? ulObj.style.display : mode;

    if (m == 'none'){
        ulObj.style.display = '' ;
        obj.firstChild.src = '/public/images/icon_expand_dec.png';
    }
    else{
        ulObj.style.display = 'none' ;
        obj.firstChild.src = '/public/images/icon_expand_inc.png';
    }
    return false;
}

/**
 * 滑鼠滑過的顏色變換
 */
function chBgc(obj,mode){
    if (obj.style.backgroundColor == '#f0f0f0') return;
    obj.className = mode ? "cssTbFocus" : "cssTbBlur";
}

/**
 * 取得 <item> 的 <title> 節點內容，沒有 title 則 return ID
 */
function getTitle(node){
    var title = node.selectSingleNode('./title');
    if (title != null && title.firstChild != null){
        var a = title.firstChild.nodeValue.split('\t');
        switch(lang){
            case 'GB2312'        : return (a[1]?(a[1] != "" && a[1]!="undefined" && a[1]!="--=[unnamed]=--" ?a[1] : a[0]) : a[0]);/*Custom 2017-11-22 *049131 */
            case 'en'            : return (a[2]?(a[2] != "" && a[2]!="undefined" && a[2]!="--=[unnamed]=--" ?a[2] : a[0]) : a[0]);/*Custom 2017-11-22 *049131 */
            case 'EUC-JP'        : return (a[3]?(a[3] != "" && a[3]!="undefined" && a[3]!="--=[unnamed]=--" ?a[3] : a[0]) : a[0]);/*Custom 2017-11-22 *049131 */
            case 'user_define'    : return (a[4]?(a[4] != "" && a[4]!="undefined" && a[4]!="--=[unnamed]=--" ?a[4] : a[0]) : a[0]);/*Custom 2017-11-22 *049131 */
            default: return a[0];
        }
    }
    else
        return '--=[ ' + node.getAttribute('identifier') + ' ]=--';
}

function stripTags(str)
{
    return str.replace(/<[^>]+>/g, '').replace(/<(\w+)( [^>]*)?>([^<]*)<\/\\1>/ig, '$3');
}


function htmlspecialchars_decode(str)
{
    return str.replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&quot;/g, '"').replace(/&amp;/g, '&');
}

function escapeHtml(text) {
    var map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function generateOrganizationByXsl(node)
{
    var xslDoc = XmlDocument.create();
    xslDoc.async = false;
    try {    // 避免未更新MSXML時出錯, XP SP2以下只支援MSXML4.0
        xslDoc.setProperty("AllowDocumentFunction", true);
        xslDoc.setProperty("ResolveExternals",true);
        xslDoc.setProperty("AllowXsltScript", true);
    }
    catch(e) {}
    xslDoc.load('manifest.xsl.php'+slang);
    return htmlspecialchars_decode(node.ownerDocument.transformNode(xslDoc));
}

/**
 * 將 <organization> 轉為 HTML
 */
function generateOrganization(node){
    var htmlCode = '', ret = '', idRef = '', nodeTitle = '', htmlTitle = '', nodeID = '', hasRef = false, isDisable = false, newTarget;
    var iref, res, href;
    if (node === null) return '';
    window.status += '|';

    // 取得 <sequencing> 內中，轉成物件
    /*
    xmlDoc.setProperty('SelectionNamespaces', 'xmlns:imsss="http://www.imsglobal.org/xsd/imsss"');
    var sss = node.selectSingleNode('./imsss:sequencing');
    if (sss && sss != null){
        if((idRef = sss.getAttribute('IDRef')) != null){
            sss = xmlDoc.selectSingleNode('/manifest/imsss:sequencingCollection/imsss:sequencing[@ID="' + idRef + '"]');
        }
        if (sss && sss != null) wiseParseSequencingGuy(sss);
    }
    */

    var nodes = node.selectNodes('./item');
    
    
    for(var i=0; i<nodes.length; i++){
        if (nodes[i].getAttribute('isvisible') == 'false') continue;
        nodeTitle = getTitle(nodes[i]);
        htmlTitle = escapeHtml(stripTags(nodeTitle));
        nodeID    = nodes[i].getAttribute('identifier');
        hasRef    = ((iref = nodes[i].getAttribute('identifierref')) &&
                     (res = resources.selectSingleNode('./resource[@identifier="' + iref + '"]')) &&
                     (href = res.getAttribute('href')) &&
                     (href != 'about:blank')) ? true : false;
        isDisable = nodes[i].getAttribute('disabled') == 'true' ? true : false;
        newTarget = nodes[i].getAttribute('target');

        var children = nodes[i].childNodes;

        for (var j=0;j<children.length;j++) {
            var tmp_identifierref = children[j].tagName;
            if (tmp_identifierref == 'item') {
                elems.push(children[j].getAttribute('identifier'));
            }
        }

        

        if ((ret = generateOrganization(nodes[i])) == '') {
            var rtn = elems.indexOf(nodeID);

            if (rtn!=-1) {
                htmlCode += '<li id="' + nodeID + '" style="padding-left:3.5em;"><span>' +
                        '<div class="icon-node"></div>';
                        // '<img src="' + themePath + 'icon-ccc.gif" valign="absmiddle" border="0">&nbsp;' ;
            } else {
                htmlCode += '<li id="' + nodeID + '" style="padding-left:1.8em;"><span>' +
                        '<div class="icon-node"></div>';
                        // '<img src="' + themePath + 'icon-ccc.gif" valign="absmiddle" border="0">&nbsp;' ;
            }

            if (hasRef && !isDisable)
                htmlCode += '<a href="javascript:;" onclick="return launchActivity(this,\'' + nodeID + '\',\'' + newTarget + '\');" class="cssAnchor1" title="'+htmlTitle+'"><div style="margin:-25px 0 5px 30px;">' + nodeTitle + '</div></a>';
            else if (hasRef && isDisable)
                htmlCode += '<a href="javascript:;" onclick="return launchActivity(this,\'' + nodeID + '\',\'' + newTarget + '\');" disabled style="cursor:default" title="'+htmlTitle+'"><div style="margin:-25px 0 5px 30px;">' + nodeTitle + '</div></a>';
            else
                htmlCode += nodeTitle;
            htmlCode += '</span>' + ret + '</li>';
        }
        else {

            var rtn = elems.indexOf(nodeID);

            if (rtn!=-1) {
                htmlCode += '<li id="' + nodeID + '" class="title" style="padding-left:1.8em;"><span>' +
                        '<a href="javascript:;" onclick="return expanding(this);">' +
                        '<img src="/public/images/icon_expand_dec.png" valign="absmiddle" border="0"></a>&nbsp;' ;
            } else {
                htmlCode += '<li id="' + nodeID + '" class="title" ><span>' +
                        '<a href="javascript:;" onclick="return expanding(this);">' +
                        '<img src="/public/images/icon_expand_dec.png" valign="absmiddle" border="0"></a>&nbsp;' ;
            }
            
            
            if (hasRef && !isDisable)
                htmlCode += '<div class="icon-node"></div><a href="javascript:;" onclick="return launchActivity(this,\'' + nodeID + '\',\'' + newTarget + '\');" title="'+htmlTitle+'"><div style="margin:-25px 0 0 58px;">' + nodeTitle + '</div></a>';
            else if (hasRef && isDisable)
                htmlCode += '<a href="javascript:;" onclick="return launchActivity(this,\'' + nodeID + '\',\'' + newTarget + '\');" disabled style="cursor:default" title="'+htmlTitle+'"><div style="margin:-25px 0 0 58px;">' + nodeTitle + '</div></a>';
            else
                htmlCode += '<span class="node-folder">' + nodeTitle + '</span>';
            htmlCode += '</span>' + ret + '</li>';
        }
    }
    return (htmlCode ? ('<ul class="step-process2">' + htmlCode + '</ul>') : '');
}

/**
 * 取得本身的 Xpath
 */
function getMyXPath(node){
    if (node == null) return '';
    var curr = node;
    var xpath = '';
    while(curr != null){
        xpath = curr.tagName + '/' + xpath;
        curr = curr.parentNode;
    }
    return '/' + xpath;
}

/**
 * 比較兩個 Xpath
 */
function getLeadingSameXpath(xp1, xp2){
    var a1 = xp1.split('/');
    var a2 = xp2.split('/');
    var min_len = Math.min(a1.length, a2.length);
    for(var i = 0; i< min_len; i++){
        if (a1[i] != a2[i]){
            return '/' + a1.slice(0,i).join('/');
        }
    }
    return xp1 == xp2 ? xp1 : '';
}

/**
 * 檢查 controlMode 是否目前節點可存取
 */
function checkControlMode(node){
    if (globalCurrentActivity == ''){
    }
    else{
    }
}

/**
 * 開始一個 Activity
 */
var isLaunching = false; // 禁止重入旗標
function launchActivity(obj,id,target){
    
    if (isLaunching) return; else isLaunching = true;
    window.status = '';

    // 取得 Activity
    var item = xmlDoc.selectSingleNode('//item[@identifier="' + id + '"]');
    if (item == null ){
        alert('incorrect item id');
        isLaunching = false;
        return false;
    }

    //設定上一節點按鈕的顯示名稱
    /* 暫時拿掉上下節點按鈕
    var backTxt = item.selectSingleNode('preceding::item[@identifierref][1]');
    var nextTxt = item.selectSingleNode('following::item[@identifierref][1]');
    if (backTxt == null) {
        parent.document.getElementById('backNodeBtn1').setAttribute('class','disabled');
    } else {
        parent.document.getElementById('backNodeBtn1').setAttribute('class','');
    }
    if (nextTxt == null) {
        parent.document.getElementById('nextNodeBtn1').setAttribute('class','disabled');
    } else {
        parent.document.getElementById('nextNodeBtn1').setAttribute('class','');
    }    
    backTxt = backTxt == null ? '': getTitle(backTxt);
    nextTxt = nextTxt == null ? '': getTitle(nextTxt);
    parent.document.getElementById('backNodeBtn1').setAttribute('title',backTxt);
    // parent.document.getElementById('backNodeBtn2').setAttribute('title',backTxt);
    parent.document.getElementById('nextNodeBtn1').setAttribute('title',nextTxt);
    // parent.document.getElementById('nextNodeBtn2').setAttribute('title',nextTxt);
    */

    if (item.getAttribute('disabled') == 'true')  {
        isLaunching = false;
        return false;
    }

    // 如果沒設定target,則到該節點取得target
    if (target == null) {
        target = item.getAttribute('target');
    }

    var parent_id = item.parentNode.getAttribute('identifier');

    // 取得 Activity 之 href
    var resource_id = item.getAttribute('identifierref');
    
    
    // 閱讀 pathNodeTimeShortlimit 秒後才開始記錄閱讀時間
    setTimeout("setReading('start', 0);", pathNodeTimeShortlimit * 1000);
    
    if (typeof(resource_id) == 'undefined') { isLaunching = false; return false; }
    var resource = resources.selectSingleNode('./resource[@identifier="' + resource_id + '"]');

    /*
     * 如果是同一個，就看它是否為 SCO，不是就可以再 launch一次
     */
    if (resource != null && id == globalCurrentActivity && obj != null) {
        if (resource.getAttribute('scormtype') != 'sco') {
            var base = resource.getAttribute('xml:base') == null ? ' ' : resource.getAttribute('xml:base');
            var href = base + '@' + resource.getAttribute('href');
            var rr = /\.(html?|swf)$/i;
            if (!rr.test(href)) {
                objForm = document.getElementById('fetchResourceForm');
                objForm.href.value = href;
                objForm.target = (target == '_blank') ? '_blank' : 's_main';
                try {
                    if (target == '_blank') parent.parent.s_main.location = 'about:blank';
                } catch (e) {

                }
                objForm.submit();
                fetchServerTime();
            }
        }
        isLaunching = false;
        return false;
    }
    
    // 判斷上一個 Activity 的 PostCondition，如果有上一個 Activity
    if (globalCurrentActivity != '' && obj != null){
        if (typeof(ss.sequencingRules[globalCurrentActivity]) != 'undefined' &&
            typeof(ss.sequencingRules[globalCurrentActivity].postConditionRule) != 'undefined'
           ){
            // alert(ss.sequencingRules[globalCurrentActivity].preConditionRule);
            var ret = determineSeqRule(ss.sequencingRules[globalCurrentActivity].postConditionRule, globalCurrentActivity);
            for(var i=0; i<ret.length; i++){
                switch(ret[i]){
                    case 'exitParent':
                    case 'exitAll':
                    case 'retry':
                    case 'retryAll':
                    case 'continue':
                    case 'previous':
                }
            }
        }
        // 判斷父節點之 ExitCondition
        if (typeof(ss.sequencingRules[parent_id]) != 'undefined' &&
            typeof(ss.sequencingRules[parent_id].exitConditionRule) != 'undefined' &&
            obj != null){
            ret = determineSeqRule(ss.sequencingRules[parent_id].exitConditionRule, parent_id);
            if (ret.length) ; // Exit
        }

        //判斷自己的PreCondition
        if (typeof(ss.sequencingRules[id]) != 'undefined' &&
            typeof(ss.sequencingRules[id].preConditionRule) != 'undefined') {
                ret = determineSeqRule(ss.sequencingRules[id].preConditionRule, id);
                for (var i = 0; i < ret.length; i++) {
                    switch(ret[i]) {
                        case 'skip': break;
                        case 'disabled': break;
                        case 'hiddenFromChoice': break;
                        case 'stopForwardTraversal': break;
                    }
                }
        }
    }
    
    // 判斷是否可點選
    if (id != globalCurrentActivity){
        var controlMode = (typeof(parent_id) == 'undefined' ? 'choice1' : ss.controlMode[parent_id]);
        if (typeof controlMode == 'undefined') controlMode = 'choice1';
        xmlDoc.setProperty('SelectionLanguage', 'XPath');
        var xpath = '';
        switch(controlMode){
            case 'forwardOnly':
                xpath = 'preceding::item[@identifierref][1]';
                break;
            case 'flow':
                xpath = 'preceding::item[@identifierref][1]|following::item[@identifierref][1]';
                break;
            case 'choice0':
                xpath = 'preceding::item[@identifierref]|following::item[@identifierref]';
                break;
            default:
                xpath = 'preceding::item[@identifierref]|following::item[@identifierref]';
                fetchNextCluster = true;
                break;
        }
        var nodes = item.selectNodes(xpath);
        var allow = false;
        for(var i=0; i<nodes.length; i++){
            if (nodes[i].getAttribute('identifier') == globalCurrentActivity) {allow = true; break;}
        }
/*
        // 檢查是否違反 controlMode 存取設定
        if (!allow && (!fetchNextCluster || (id != NextClusterId))){
                xmlDoc.setProperty('SelectionLanguage', 'XSLPattern');
                window.status = id + ' == ' + NextClusterId;
                alert('Out of sequencing.');
                isLaunching = false;
                return false;
        }
*/
        // 判斷這個 Activity 的 PreCondition

        globalCurrentActivity = id;
        // 取下一個 Cluster 入口節點
        var NextCluster = item.selectSingleNode('ancestor::item/following::item/descendant-or-self::item[@identifierref]');
        NextClusterId = (NextCluster == null ? '' : NextCluster.getAttribute('identifier'));
        if (typeof(NextClusterId) == 'undefined') NextClusterId = '';

        // 設定下一次點選，可否點選下一個 Cluster 入口節點
        nodes = item.selectNodes('./following::item');
        fetchNextCluster = (nodes.length) ? false : true;
        // xmlDoc.setProperty('SelectionLanguage', 'XSLPattern');
    }

    var download = item.getAttribute('download');

    // 將教材頁送出
    parent.parent.globalState.PrevActivity = parent.parent.globalState.CurrentActivity;
    parent.parent.globalState.CurrentActivity = id;
    objForm = document.getElementById('fetchResourceForm');
    if (resource)
    base = resource.getAttribute('xml:base') == null ? ' ' : resource.getAttribute('xml:base');
    objForm.href.value = (resource == null) ? 'about:blank' : (base + '@' + resource.getAttribute('href'));
    objForm.target = (target == '_blank') ? '_blank' : 's_main';
    objForm.is_download.value = download;
    try{
        if (target == '_blank') parent.parent.s_main.location = 'about:blank';    
    }catch(e){
        
    }
    
    objForm.submit();
    fetchServerTime();
    objForm.prev_node_id.value = id;
        
        
    objForm.prev_href.value = objForm.href.value;
    objForm.prev_node_title.value = getTitle(item).replace(/(<[^>]*>|^ | $)/g, '').replace(/^&nbsp;/, '');
    
    trackingActivityObj = new initTMD(item);
    trackingActivityObj['Activity Progress Information']['Activity Attempt Count']++;
    trackingActivityObj['Activity Status Information']['Activity is Active'] = true;

    // 控制瀏覽按鍵
    //var tables = document.getElementById('displayContainer').getElementsByTagName('table');
    /*
    var imgs = parent.document.getElementsByTagName('img');
    switch((typeof(ss.controlMode[parent_id]) == 'undefined' ? '' : ss.controlMode[parent_id])){
    case 'forwardOnly':
            imgs[2].src = 'icon/4-2.gif';
            imgs[7].src = 'icon/4-2.gif';
            break;
        default:
            break;
    }
    if (!fetchNextCluster)
    {
        imgs[3].src = 'icon/3-2.gif';
        imgs[6].src = 'icon/3-2.gif';
    }
*/
    // Activity 變色
    /*
    var nodes = myPanel.getElementsByTagName('span');
    for(var i=0; i<nodes.length; i++)
    {
        nodes[i].style.backgroundColor = '';
        nodes[i].className = "cssTbBlur2";
    }
    */
   
   setTimeout(function(){
        // 取得學習節點記錄 
        getLearnRecord();
        $(".icon-node").attr("class", "icon-node");
        var nodes = myPanel.getElementsByTagName('li');
        for(var i=0; i<nodes.length; i++)
        {
            if((nodes[i].className).indexOf('title') != -1) {
                nodes[i].className = 'title';
            } else {
                nodes[i].className = '';
            }
        }
        // 修改節點已讀狀態 (目前先採用看過即完成)
        changeRead(learnRecord.path.item);
        
        var recordNode = $(obj).parent().find(".icon-node");
        if (recordNode.prop("class") == 'icon-node') {
            recordNode.addClass("icon-node-progress");
        }
        // 變更進度條
        // var aPercent = learnRecord.progress;
        var aPercent = Math.round(nodeReaded / nodeTotal * 100);
        $("#learn-progress", parent.document).find(".bar").css("width", aPercent+"%");
        $("#progressBar-text", parent.document).text(aPercent+"%");
        nodeTotal = 0;
        nodeReaded = 0; 
        
        // 判斷是否有閱讀（比較偵錯用，檢視是否節點載入閱讀紀錄完，才顯示下載頁面，這樣下載頁面才能取得指定節點閱讀狀態）
        if (window.console) {console.log('launchActivity');}
        if (window.console) {console.log(id);}
        if (window.console) {console.log($('#' + id).find('.icon-node').hasClass('node-finish'));}
        if (window.console) {console.log($('#' + id).find('.icon-node').hasClass('node-progress'));}

        if (obj == null)
        {
            var x = document.getElementById(id);
            if (x != null && x.firstChild != null) {
                var xcn = x.className;
                x.className = xcn + ' selected';
            }
        }
        else
        {
            if (obj.parentNode.parentNode != null) {
                var opcn = obj.parentNode.parentNode.className;
                obj.parentNode.parentNode.className = opcn + ' selected';
            }
        }
    }, 100);

    isLaunching = false;
    return false;
}

/**
    obj : sequencingRules
    id  : Activity Id
 */
function determineSeqRule(obj, id){
    var ret = new Array();
    var condition = '';
    var operator = '';
    var result = 0;

    if (obj == null) return;
    for(var i=0; i<obj.length; i++){
        for(var j=0; j<obj[i].conditions.length; j++){
            if ((operator = obj[i].conditions[j].condition.charAt(0)) == '!'){
                condition = obj[i].conditions[j].condition.substr(1);
                operator = true;
            }
            else{
                condition = obj[i].conditions[j].condition;
                operator = false;
            }

            switch(condition){
                case 'satisfied':
                    if (trackingActivityObj['Objective Progress Information']['Objective Progress Status'] &&
                        trackingActivityObj['Objective Progress Information']['Objective Satisfied Status']
                       )
                        result += 1;
                    else if(operator)
                        result += 1;
                    break;
                case 'objectiveStatusKnown':
                    if (trackingActivityObj['Objective Progress Information']['Objective Progress Status'])
                        result += 1;
                    else if(operator)
                        result += 1;
                    break;
                case 'objectiveMeasureKnown':
                    if (trackingActivityObj['Objective Progress Information']['Objective Progress Status'] &&
                        trackingActivityObj['Objective Progress Information']['Objective Measure Status']
                       )
                        result += 1;
                    else if(operator)
                        result += 1;
                    break;
                case 'objectiveMeasureGreaterThan':
                    if (trackingActivityObj['Objective Progress Information']['Objective Measure Status'] &&
                        trackingActivityObj['Objective Progress Information']['Objective Normalized Measure'] >
                        obj[i].conditions[j].measureThreshold
                       )
                        result += 1;
                    else if(operator)
                        result += 1;
                    break;
                case 'objectiveMeasureLessThan':
                    if (trackingActivityObj['Objective Progress Information']['Objective Measure Status'] &&
                        trackingActivityObj['Objective Progress Information']['Objective Normalized Measure'] <
                        obj[i].conditions[j].measureThreshold
                       )
                        result += 1;
                    else if(operator)
                        result += 1;
                    break;
                case 'completed':
                    if (trackingActivityObj['Attempt Progress Information']['Attempt Progress Status'] &&
                        trackingActivityObj['Attempt Progress Information']['Attempt Completion Status']
                       )
                        result += 1;
                    else if(operator)
                        result += 1;
                    break;
                case 'activityProgressKnown':
                    if (trackingActivityObj['Activity Progress Information']['Activity Progress Status'] &&
                        trackingActivityObj['Attempt Progress Information']['Attempt Progress Status']
                       )
                        result += 1;
                    else if(operator)
                        result += 1;
                    break;
                case 'attempted':
                    if (trackingActivityObj['Activity Progress Information']['Activity Progress Status'] &&
                        trackingActivityObj['Activity Progress Information']['Activity Attempt Count'] > 0
                       )
                        result += 1;
                    else if(operator)
                        result += 1;
                    break;
                case 'attemptLimitExceeded':
                    if (trackingActivityObj['Activity Progress Information']['Activity Progress Status'] &&
                        typeof(ss.limitConditions[id].attemptLimit) == 'number' &&
                        trackingActivityObj['Activity Progress Information']['Activity Attempt Count'] >=
                        ss.limitConditions[id].attemptLimit
                       )
                        result += 1;
                    else if(operator)
                        result += 1;
                    break;
                case 'timeLimitExceeded':
                    if (trackingActivityObj['Activity Progress Information']['Activity Progress Status'] &&
                        ( trackingActivityObj['Activity Progress Information']['Activity Absolute Duration'] >
                          ss.limitConditions[id].activityAbsoluteDurationLimit ||
                          trackingActivityObj['Activity Progress Information']['Activity Experienced Duration'] >
                          ss.limitConditions[id].activityExperiencedDurationLimit ||
                          trackingActivityObj['Attempt Progress Information']['Attempt Absolute Duration'] >
                          ss.limitConditions[id].attemptAbsoluteDurationLimit ||
                          trackingActivityObj['Attempt Progress Information']['Attempt Experienced Duration'] >
                          ss.limitConditions[id].attemptExperiencedDurationLimit
                        )
                       )
                        result += 1;
                    else if(operator)
                        result += 1;
                    break;
                case 'outsideAvailableTimeRange':
                    var currentTime = new Date().getTime();
                    if (currentTime < ss.limitConditions[id].beginTimeLimit ||
                        currentTime > ss.limitConditions[id].endTimeLimit
                       )
                        result += 1;
                    else if(operator)
                        result += 1;
                    break;
            }
        }
        if ( (obj[i].conditionCombination == 'all' && result == obj[i].conditions.length) ||
             (obj[i].conditionCombination == 'any' && result > 0)
           )
            ret[i] = obj[i].action;
        else
            ret[i] = '';
    }
    return ret;
}

/**
 * parse <sequencing> 內容，轉換為 Object
 */
function wiseParseSequencingGuy(sssNode){
    var item_id = sssNode.parentNode.getAttribute('identifier');
    var nodes = sssNode.childNodes;
    var PropertyValue = '', childs;

    for(var i=0; i<nodes.length; i++){
        if (nodes[i].nodeType != 1) continue;
        switch(nodes[i].tagName){
            case 'imsss:controlMode':
                if (nodes[i].getAttribute('forwardOnly') == 'true')
                    ss.controlMode[item_id] = 'forwardOnly';
                else if (nodes[i].getAttribute('flow') == 'true')
                    ss.controlMode[item_id] = 'flow';
                else if (nodes[i].getAttribute('choiceExit') != 'false')
                    ss.controlMode[item_id] = 'choice1';
                else
                    ss.controlMode[item_id] = 'choice0';

                break;

            case 'imsss:sequencingRules':
                parseSequencingRules(nodes[i], item_id);
                break;

            case 'imsss:limitConditions':
                if (nodes[i].attributes.length){
                    ss.limitConditions[item_id] = new Object();
                    if(typeof(PropertyValue = nodes[i].getAttribute('attemptLimit'))                     != 'undefined') ss.limitConditions[item_id].attemptLimit                     = parseInt(PropertyValue);
                    if(typeof(PropertyValue = nodes[i].getAttribute('attemptAbsoluteDurationLimit'))     != 'undefined') ss.limitConditions[item_id].attemptAbsoluteDurationLimit     = PropertyValue;
                    if(typeof(PropertyValue = nodes[i].getAttribute('attemptExperiencedDurationLimit'))  != 'undefined') ss.limitConditions[item_id].attemptExperiencedDurationLimit  = PropertyValue;
                    if(typeof(PropertyValue = nodes[i].getAttribute('activityAbsoluteDurationLimit'))    != 'undefined') ss.limitConditions[item_id].activityAbsoluteDurationLimit    = PropertyValue;
                    if(typeof(PropertyValue = nodes[i].getAttribute('activityExperiencedDurationLimit')) != 'undefined') ss.limitConditions[item_id].activityExperiencedDurationLimit = PropertyValue;
                    if(typeof(PropertyValue = nodes[i].getAttribute('beginTimeLimit'))                   != 'undefined') ss.limitConditions[item_id].beginTimeLimit                   = PropertyValue;
                    if(typeof(PropertyValue = nodes[i].getAttribute('endTimeLimit'))                     != 'undefined') ss.limitConditions[item_id].endTimeLimit                     = PropertyValue;
                }
                break;

            case 'imsss:auxiliaryResources':
                childs = nodes[i].childNodes;
                ss.auxiliaryResources[item_id] = '<ol>';
                for(var j=0; j<childs.length; j++){
                    if (childs[j].tagName != 'imsss:auxiliaryResource') continue;
                    ss.auxiliaryResources[item_id] += '<li><a href="' +
                                                      childs[j].getAttribute('auxiliaryResourceID') +
                                                      '" target="_blank">' +
                                                      childs[j].getAttribute('purpose') +
                                                      '</a></li>';
                }
                ss.auxiliaryResources[item_id] = '</ol>';
                break;

            case 'imsss:rollupRules':
                parseRollupRules(nodes[i], item_id);
                break;

            case 'imsss:objectives':
                parseObjectives(nodes[i], item_id);
                break;

            case 'imsss:randomizationControls':
                if (nodes[i].attributes.length){
                    ss.deliveryControls[item_id] = new Object();
                    if (typeof(PropertyValue = nodes[i].getAttribute('randomizationTiming')) != 'undefined') ss.deliveryControls[item_id].randomizationTiming = PropertyValue;
                    if (typeof(PropertyValue = nodes[i].getAttribute('selectCount'))         != 'undefined') ss.deliveryControls[item_id].selectCount = PropertyValue;
                    if (typeof(PropertyValue = nodes[i].getAttribute('reorderChildren'))     != 'undefined') ss.deliveryControls[item_id].reorderChildren = (PropertyValue == 'true' ? true : false) ;
                    if (typeof(PropertyValue = nodes[i].getAttribute('selectionTiming'))     != 'undefined') ss.deliveryControls[item_id].selectionTiming = PropertyValue;
                }
                break;

            case 'imsss:deliveryControls':
                if (nodes[i].attributes.length){
                    ss.limitConditions[item_id] = new Object();
                    ss.limitConditions[item_id].tracked                = ((PropertyValue = nodes[i].getAttribute('tracked'))                == 'false') ? false : true;
                    ss.limitConditions[item_id].completionSetByContent = ((PropertyValue = nodes[i].getAttribute('completionSetByContent')) == 'true')  ? true  : false;
                    ss.limitConditions[item_id].objectiveSetByContent  = ((PropertyValue = nodes[i].getAttribute('objectiveSetByContent'))  == 'true')  ? true  : false;
                }
                break;
        }
    }
}

/**
 * parse Sequencing Rules
 */
function parseSequencingRules(node, id){
    var condition, conditions, attribValue;
    var nodes = node.selectNodes('./imsss:preConditionRule');
    if (nodes.length){
        if (typeof(ss.sequencingRules[id]) == 'undefined') ss.sequencingRules[id] = new Object();
        ss.sequencingRules[id].preConditionRule = new Array();
        for(var i=0; i<nodes.length; i++){
            condition = nodes[i].selectSingleNode('./imsss:ruleConditions');
            conditions = condition.selectNodes('./imsss:ruleCondition');
            ss.sequencingRules[id].preConditionRule[i] = new Object();
            ss.sequencingRules[id].preConditionRule[i].conditions = new Array();
            for(var j=0; j<conditions.length; j++){
                ss.sequencingRules[id].preConditionRule[i].conditions[j] = new Object();
                ss.sequencingRules[id].preConditionRule[i].conditions[j].condition = (
                (conditions[j].getAttribute('operator') == 'not') ? '!' : '' ) +
                conditions[j].getAttribute('condition');
                if (typeof(attribValue = conditions[j].getAttribute('referencedObjective')) != 'undefined')
                    ss.sequencingRules[id].preConditionRule[i].conditions[j].referencedObjective = attribValue;
                ss.sequencingRules[id].preConditionRule[i].conditions[j].measureThreshold = (typeof(attribValue = conditions[j].getAttribute('referencedObjective')) != 'undefined') ? attribValue : 0.000;
            }
            ss.sequencingRules[id].preConditionRule[i].combination = (condition.getAttribute('conditionCombination') == 'all' ? 'all' : 'any');
            ss.sequencingRules[id].preConditionRule[i].action = nodes[i].selectSingleNode('./imsss:ruleAction').getAttribute('action');
        }
    }

    var nodes = node.selectNodes('./imsss:postConditionRule');
    if (nodes.length){
        if (typeof(ss.sequencingRules[id]) == 'undefined') ss.sequencingRules[id] = new Object();
        ss.sequencingRules[id].postConditionRule = new Array();
        for(var i=0; i<nodes.length; i++){
            condition = nodes[i].selectSingleNode('./imsss:ruleConditions');
            conditions = condition.selectNodes('./imsss:ruleCondition');
            ss.sequencingRules[id].postConditionRule[i] = new Object();
            ss.sequencingRules[id].postConditionRule[i].conditions = new Array();
            for(var j=0; j<conditions.length; j++){
                ss.sequencingRules[id].postConditionRule[i].conditions[j] = new Object();
                ss.sequencingRules[id].postConditionRule[i].conditions[j].condition = (
                (conditions[j].getAttribute('operator') == 'not') ? '!' : '' ) +
                conditions[j].getAttribute('condition');
                if (typeof(attribValue = conditions[j].getAttribute('referencedObjective')) != 'undefined')
                    ss.sequencingRules[id].postConditionRule[i].conditions[j].referencedObjective = attribValue;
                ss.sequencingRules[id].postConditionRule[i].conditions[j].measureThreshold = (typeof(attribValue = conditions[j].getAttribute('referencedObjective')) != 'undefined') ? attribValue : 0.000;
            }
            ss.sequencingRules[id].postConditionRule[i].combination = (condition.getAttribute('conditionCombination') == 'all' ? 'all' : 'any');
            ss.sequencingRules[id].postConditionRule[i].action = nodes[i].selectSingleNode('./imsss:ruleAction').getAttribute('action');
        }
    }

    var nodes = node.selectNodes('./imsss:exitConditionRule');
    if (nodes.length){
        if (typeof(ss.sequencingRules[id]) == 'undefined') ss.sequencingRules[id] = new Object();
        ss.sequencingRules[id].exitConditionRule = new Array();
        for(var i=0; i<nodes.length; i++){
            condition = nodes[i].selectSingleNode('./imsss:ruleConditions');
            conditions = condition.selectNodes('./imsss:ruleCondition');
            ss.sequencingRules[id].exitConditionRule[i] = new Object();
            ss.sequencingRules[id].exitConditionRule[i].conditions = new Array();
            for(var j=0; j<conditions.length; j++){
                ss.sequencingRules[id].exitConditionRule[i].conditions[j] = new Object();
                ss.sequencingRules[id].exitConditionRule[i].conditions[j].condition = (
                (conditions[j].getAttribute('operator') == 'not') ? '!' : '' ) +
                conditions[j].getAttribute('condition');
                if (typeof(attribValue = conditions[j].getAttribute('referencedObjective')) != 'undefined')
                    ss.sequencingRules[id].exitConditionRule[i].conditions[j].referencedObjective = attribValue;
                ss.sequencingRules[id].exitConditionRule[i].conditions[j].measureThreshold = (typeof(attribValue = conditions[j].getAttribute('referencedObjective')) != 'undefined') ? attribValue : 0.000;
            }
            ss.sequencingRules[id].exitConditionRule[i].combination = (condition.getAttribute('conditionCombination') == 'all' ? 'all' : 'any');
            ss.sequencingRules[id].exitConditionRule[i].action = nodes[i].selectSingleNode('./imsss:ruleAction').getAttribute('action');
        }
    }
}

/**
 * parse Rollup Rules
 */
function parseRollupRules(node, id){
    ss.rollupRules[id] = new Object();
    ss.rollupRules[id].rollupObjectiveSatisfied = (node.getAttribute('rollupObjectiveSatisfied') == 'false' ? false : true);
    ss.rollupRules[id].rollupProgressCompletion = (node.getAttribute('rollupProgressCompletion') == 'false' ? false : true);
    ss.rollupRules[id].objectiveMeasureWeight   = (node.getAttribute('objectiveMeasureWeight')   == 'false' ? false : true);

    var childActivitySet;
    var nodes = node.selectNodes('./rollupRule');
    if (nodes.length){
        ss.rollupRules[id].rules = new Array();
        for(var i=0; i<nodes.length; i++){
            childActivitySet = nodes[i].getAttribute('childActivitySet');
            switch(childActivitySet){
                case 'any':
                case 'none':
                    ss.rollupRules[id].rules[i].childActivitySet = childActivitySet;
                    break;
                case 'atLeastCount':
                    ss.rollupRules[id].rules[i].childActivitySet = 'atLeastCount';
                    ss.rollupRules[id].rules[i].minimumCount = parseInt(nodes[i].getAttribute('minimumCount'));
                    break;
                case 'atLeastPercent':
                    ss.rollupRules[id].rules[i].childActivitySet = 'atLeastPercent';
                    ss.rollupRules[id].rules[i].minimumPercent = parseFloat(nodes[i].getAttribute('minimumPercent'));
                    break;
                default:
                    ss.rollupRules[id].rules[i].childActivitySet = 'all';
                    break;
            }
            condition = nodes[i].selectSingleNode('./imsss:rollupConditions');
            conditions = condition.selectNodes('./imsss:rollupCondition');
            ss.rollupRules[id].rules[i].conditions = new Array();
            for(var j=0; j<conditions.length; j++){
                ss.rollupRules[id].rules[i].conditions[j] = (
                (conditions[j].getAttribute('operator') == 'not') ? '!' : '' ) +
                conditions[j].getAttribute('condition');
            }
            ss.rollupRules[id].rules[i].combination = (condition.getAttribute('conditionCombination') == 'all' ? 'all' : 'any');
            ss.rollupRules[id].rules[i].action = nodes[i].selectSingleNode('./imsss:rollupAction').getAttribute('action');
        }
    }
}

/**
 * parse Objectives
 */
function parseObjectives(node, id){
    var attributeValue = '', nodes, nodess, targetID = '';
    var primaryObj = node.selectSingleNode('./imsss:primaryObjective');
    ss.objectives[id] = new Object();
    ss.objectives[id].primaryObjective = new Object();
    if (typeof(attributeValue = primaryObj.getAttribute('objectiveID')) == 'string') ss.objectives[id].primaryObjective.objectiveID = attributeValue;
    ss.objectives[id].primaryObjective.satisfiedByMeasure   = (primaryObj.getAttribute('satisfiedByMeasure') == 'true' ? true : false );
    ss.objectives[id].primaryObjective.minNormalizedMeasure = ((nodes = primaryObj.selectSingleNode('./imsss:minNormalizedMeasure')) == null ? 1.0000 : parseFloat(nodes.firstChild.nodeValue));
    nodes = primaryObj.selectNodes('./imsss:mapInfo');
    if(nodes.length){
        ss.objectives[id].primaryObjective.mapInfo = new Object();
        for(var i=0; i<nodes.length; i++){
            targetID = nodes[i].getAttribute('targetObjectiveID');
            ss.objectives[id].primaryObjective.mapInfo[targetID] = new Object();
            ss.objectives[id].primaryObjective.mapInfo[targetID].readSatisfiedStatus    = ((arrtibuteValue = nodes[i].getAttribute('readSatisfiedStatus'))    == 'false' ? false : true );
            ss.objectives[id].primaryObjective.mapInfo[targetID].readNormalizedMeasure  = ((arrtibuteValue = nodes[i].getAttribute('readNormalizedMeasure'))  == 'false' ? false : true );
            ss.objectives[id].primaryObjective.mapInfo[targetID].writeSatisfiedStatus   = ((arrtibuteValue = nodes[i].getAttribute('writeSatisfiedStatus'))   == 'true' ? true : false );
            ss.objectives[id].primaryObjective.mapInfo[targetID].writeNormalizedMeasure = ((arrtibuteValue = nodes[i].getAttribute('writeNormalizedMeasure')) == 'true' ? true : false );
        }
    }

    var nodes = node.selectNodes('./imsss:objective');
    if (nodes.length){
        ss.objectives[id].objectives = new Array();
        for(var j=0; j<nodes.length; j++){
            ss.objectives[id].objectives[j] = new Object();
            if (typeof(attributeValue = nodes[j].getAttribute('objectiveID')) == 'string') ss.objectives[id].objectives[j].objectiveID = attributeValue;
            ss.objectives[id].objectives[j].satisfiedByMeasure   = (nodes[j].getAttribute('satisfiedByMeasure') == 'true' ? true : false );
            ss.objectives[id].objectives[j].minNormalizedMeasure = ((nodess = nodes[j].selectSingleNode('./imsss:minNormalizedMeasure')) == null ? 1.0000 : parseFloat(nodess.firstChild.nodeValue));
            nodess = nodes[j].selectNodes('./imsss:mapInfo');
            if(nodess.length){
                ss.objectives[id].objectives[j].mapInfo = new Object();
                for(var i=0; i<nodess.length; i++){
                    targetID = nodess[i].getAttribute('targetObjectiveID');
                    ss.objectives[id].objectives[j].mapInfo[targetID] = new Object();
                    ss.objectives[id].objectives[j].mapInfo[targetID].readSatisfiedStatus    = ((arrtibuteValue = nodess[i].getAttribute('readSatisfiedStatus'))    == 'false' ? false : true );
                    ss.objectives[id].objectives[j].mapInfo[targetID].readNormalizedMeasure  = ((arrtibuteValue = nodess[i].getAttribute('readNormalizedMeasure'))  == 'false' ? false : true );
                    ss.objectives[id].objectives[j].mapInfo[targetID].writeSatisfiedStatus   = ((arrtibuteValue = nodess[i].getAttribute('writeSatisfiedStatus'))   == 'true' ? true : false );
                    ss.objectives[id].objectives[j].mapInfo[targetID].writeNormalizedMeasure = ((arrtibuteValue = nodess[i].getAttribute('writeNormalizedMeasure')) == 'true' ? true : false );
                }
            }
        }
    }
}

/**
 * 從 Server 取回 Tracing Model Data，如果有的話。
 */

function initTMD(node){
//    var xmlTmd = XmlDocument.create();
//    xmlTmd.async = false;
//    xmlTmd.resolveExternals = false;
//    if (!xmlTmd.load('SCORM_loadTMD.php')){
        return (new TrackingModel(node));            // 取不到 (第一次執行)，則產生一個新的
//    }
//    else{
//        var tmpTmd = new TrackingModel(null);
//        restoreTMD(tmpTmd, xmlTmd.documentElement);
//        return tmpTmd;
//    }
}

/**
 * 將存成 XML 的 Tracing Model Data 轉成 JS 物件
 */
function restoreTMD(obj, xmlNode){
    if (xmlNode == null) return;
    var nodes = xmlNode.childNodes;
    for(var i=0; i< nodes.length; i++){
        if (nodes[i].nodeType == 3)
            obj[nodes[i].tagName] = nodes[i].nodeValue;
        else if (nodes[i].nodeType != 1 || typeof(obj[nodes[i].tagName]) == 'undefined') continue;
        restoreTMD(obj[nodes[i].tagName], nodes[i]);
    }
}

/**
 * Navigation Data Model 物件宣告
 */
function NavigationDataModel(){
    this['event']                          = '';
    this['control_mode_enabled']           = new Object();
    this['control_mode_enabled']['choice'] = true;
    this['control_mode_enabled']['flow']   = true;
    this['event_permitted']                = new Object();
    this['event_permitted']['continue']       = null;
    this['event_permitted']['previous']       = null;
}

/**
 * Activity Tracking Model 物件宣告
 */
function TrackingModel(node){
    this['Objective Progress Information'] = new Object();
    this['Objective Progress Information']['Objective Progress Status']    = false;
    this['Objective Progress Information']['Objective Satisfied Status']   = false;
    this['Objective Progress Information']['Objective Measure Status']     = false;
    this['Objective Progress Information']['Objective Normalized Measure'] = 0.0;

    this['Activity Progress Information'] = new Object();
    this['Activity Progress Information']['Activity Progress Status']      = false;
    this['Activity Progress Information']['Activity Absolute Duration']    = 0.0;
    this['Activity Progress Information']['Activity Experienced Duration'] = 0.0;
    this['Activity Progress Information']['Activity Attempt Count']        = 0;

    this['Attempt Progress Information'] = new Object();
    this['Attempt Progress Information']['Attempt Progress Status']      = false;
    this['Attempt Progress Information']['Attempt Completion Amount']    = 0.0;
    this['Attempt Progress Information']['Attempt Completion Status']    = false;
    this['Attempt Progress Information']['Attempt Absolute Duration']    = 0.0;
    this['Attempt Progress Information']['Attempt Experienced Duration'] = 0.0;

    this['Activity Status Information'] = new Object();
    this['Activity Status Information']['Activity is Active']    = false;
    this['Activity Status Information']['Activity is Suspended'] = false;

    var nodeList = new Array();
    if (node != null){
        var nodes = node.selectNodes('./item');
        for(var i=0; i< nodes.length; i++) nodeList[i] = nodes[i].getAttribute('identifier');
    }
    this['Activity Status Information']['Available Children'] = nodeList.join();
}

/**
 * 上一步/下一步按鈕....................................
 */
function nextStep(dir){
    var xpath = '';
    switch(dir){
        case 1:    // 往子代找，找不到往弟代找，找不到往叔代找...................................
            var node = organization.selectSingleNode('//item[@identifier="' + globalCurrentActivity + '"]//item[@identifierref][(not (@isvisible) or @isvisible!="false") and (not (@disabled) or @disabled!="true")]');
            if (node != null)
            {
                launchActivity(null, node.getAttribute('identifier')); return;
            }
            xmlDoc.setProperty('SelectionLanguage', 'XPath');
            node = organization.selectSingleNode('//item[@identifier="' + globalCurrentActivity + '"]/following::item[@identifierref][(not (@isvisible) or @isvisible!="false") and (not (@disabled) or @disabled!="true")][1]');
            // xmlDoc.setProperty('SelectionLanguage', 'XSLPattern');
            if (node == null)
                alert(MSG_TO_THE + (dir == 1 ? MSG_END : MSG_OUTSET));
            else
                launchActivity(null, node.getAttribute('identifier'));

            break;
        case -1:    // 往姪代找，找不到往兄代找，找不到往父代找................................
            var node = organization.selectSingleNode('//item[@identifier="' + globalCurrentActivity + '"]');
            if (node == null) return;
            var parent = node.parentNode;
            xmlDoc.setProperty('SelectionLanguage', 'XPath');
            node = node.selectNodes('preceding-sibling::item/descendant-or-self::item[@identifierref][(not (@isvisible) or @isvisible!="false") and (not (@disabled) or @disabled!="true")]');
            // xmlDoc.setProperty('SelectionLanguage', 'XSLPattern');
            if (node == null || node.length == 0)
            {
                if (parent.getAttribute('identifierref') &&
                    parent.getAttribute('isvisible') != 'false' &&
                    parent.getAttribute('disabled') != 'true')
                {
                    launchActivity(null, parent.getAttribute('identifier'));
                }
                else
                {
                    xmlDoc.setProperty('SelectionLanguage', 'XPath');
                    node = parent.selectNodes('preceding-sibling::item/descendant-or-self::item[@identifierref][(not (@isvisible) or @isvisible!="false") and (not (@disabled) or @disabled!="true")]');
                    // xmlDoc.setProperty('SelectionLanguage', 'XSLPattern');
                    if (node == null || node.length == 0)
                        alert(MSG_TO_THE + (dir == 1 ? MSG_END : MSG_OUTSET));
                    else
                        launchActivity(null, node[node.length-1].getAttribute('identifier'));
                }
            }
            else
                launchActivity(null, node[node.length-1].getAttribute('identifier'));

            break;
        default:
            return;
    }
}

/**
 * 輔助說明視窗
 */
function help(){
    helpWin = window.open('about:blank', 'helpWin', 'width=400,height=300,left=10,top=10,toolbar=0,status=0,resizable=0,scrollbars=0,menubar=0');
    helpWin.title = 'HELP';
    helpWin.document.write('<h1 align=center><br>&#x4EE5;&#x4F60;~~~&#x7684;&#x667A;&#x6167;<br>&#x9084;&#x9700;&#x8981;&#x8F14;&#x52A9;&#x8AAA;&#x660E;&#x55CE;</h1>');
    helpWin.document.write('<p align=center><br><br><input type="button" value="&#x5C11;&#x552C;&#x54E2;&#x6211;" onclick="self.close();">&nbsp;&nbsp;<input type="button" value="&#x8AAA;&#x5F97;&#x4E5F;&#x662F;" onclick="self.close();"></p>');
}

/**
 * 將目錄隱藏/恢復
 */
function minimize(){
    /*
    var obj = document.getElementById('displayContainer');

    alert(frameCol);

        obj.style.width = max_width + 10;
        myFrame.cols = '200,*';
        obj.firstChild.style.display = '';
        obj.firstChild.nextSibling.style.display = 'none';
        myPanel.firstChild.style.display = '';
    }
    else{
        myFrame.cols = '36,*';
        obj.firstChild.style.display = 'none';
        obj.firstChild.nextSibling.style.display = '';
        myPanel.firstChild.style.display = 'none';
        obj.style.width = '36px';
    }
    */
    
   

    var frameCol = myFrame.cols;
    var obj = parent.document.getElementsByTagName('table');

    if (frameCol == '312,*'){    // collecting
        parent.document.getElementById('toolbar').style.display = '';
        parent.document.getElementById('learn-progress').style.display = 'none';
        parent.document.getElementById('treePanel').style.display = 'none';
        parent.document.getElementById('minBtn').className = 'icon-open-hr';
        parent.document.getElementById('minBtn').title = MSG_BTN_MAX;
        myFrame.cols= '44,*';
    }
    else{                        // expanding
        parent.document.getElementById('toolbar').style.display = '';
        parent.document.getElementById('learn-progress').style.display = '';
        parent.document.getElementById('treePanel').style.display = '';
        parent.document.getElementById('minBtn').className = 'icon-close-hr';
        parent.document.getElementById('minBtn').title = MSG_BTN_MIN;
        myFrame.cols= '312,*';
    }
}

function notebook() {
    window.open("/message/write.php?commonuse=true", "", "width=800,height= 600,toolbar=0,menubar=0,scrollbars=1,resizable=0,status=0");
}

// 取學習節點進度
function getLearnRecord() {

    /**
     * 估計APP因為節點已平坦化，故僅需找第一層
     * 但PC仍有子孫結構，故增加 descendant參數（1表示找子孫節點） 供判斷，以相容之
     */
    $.ajax({
        url: '/xmlapi/index.php?action=my-course-path-info&onlyProgress=0&descendant=1&cid='+cid+'&ticket='+pTicket,
        datatype: 'json',
        async: false,
        success: function(res){
            if (res.code == 0) {
                if (res.message == "success") {
                    learnRecord = res.data;
                } else {
                    alert(res.message);
                }
            } else if (res.code == 1) {
                alert('Ticket illegeal!');
            } else {
                if (window.console) {console.log("Get path Error: " + '/xmlapi/index.php?action=my-course-path-info&cid='+cid+'&ticket='+pTicket);}
            }
        },
        error: function() {
            if (window.console) {console.log("Get path Error: " + '/xmlapi/index.php?action=my-course-path-info&cid='+cid+'&ticket='+pTicket);}
        }
    });
}
        
var nodeTotal = 0;
var nodeReaded = 0;
// 節點可無限往右縮，故使用遞迴方式修改節點狀態 
function changeRead(obj) {
    $.each(obj, function(i, v) {
        if (v.leaf === false) {
            if (v.readed === true) {
                $("#displayPanel")
                    .find("#" + v.identifier)
                    .children("span")
                    .find(".icon-node")
                    .addClass("node-finish");
                nodeReaded++;
            } 
            if (v.href !== 'about:blank' && v.itemDisabled === false) {
                nodeTotal++;
            }
            changeRead(v.item);
        } else {
//            if (window.console) {console.log('v', v);}
            // 非空節點 且 非未啟用節點
            if (v.href !== 'about:blank' && v.itemDisabled === false) {
                nodeTotal++;
            } else {
                // 空節點 或 未啟用節點
                $("#displayPanel")
                .find("#" + v.identifier)
                .css('color', '#a7a7a7')
                .children("span")
                .find(".icon-node")
                .addClass("node-disabled");
            }
            if (v.readed === true) {
                $("#displayPanel")
                .find("#" + v.identifier)
                .children("span")
                .find(".icon-node")
                .addClass("node-finish");
                nodeReaded++;
            }
        }
    });
}

// 設定閱讀時間起迄
function setReading(type, period) {
    
    objForm = document.getElementById('fetchResourceForm');
    
    if (type !== 'start' && type !== 'end') {
        return;
    }
    
    if ($(objForm).find("input[name='prev_node_id']").val() === '') {
        return;
    }
    
    // 嚴謹政策：判斷是否離開視窗
//    if (type === 'end') {
//        var pathtreeFrame = window.parent.document;  
//        if (document.hidden === true) {
//            if (window.console) {console.log('注意！偵測到您已離開視窗，閱讀記錄將暫時不會記錄');}  
//            $(pathtreeFrame).contents().find('#reading-online').hide(); 
//            $(pathtreeFrame).contents().find('#reading-offline').show();  
//            return;
//        } else {
//            if (window.console) {console.log('閱讀中');} 
//            $(pathtreeFrame).contents().find('#reading-offline').hide();    
//            $(pathtreeFrame).contents().find('#reading-online').show(); 
//        }
//    }
    
    // mooc\smarty\templates\learn\download_materials.tpl 也要一起改
    $.ajax({
        url:'/mooc/controllers/course_ajax.php?actype='  + type,
        type:'POST',
        dataType:'json',
        data: {
            action: 'setReading', 
            ticket: pTicket,
            type: type, 
            period: period, 
            enCid: $(objForm).find("input[name='course_id']").val(), 
            bt: $(objForm).find("input[name='begin_time']").val(),
            title: $(objForm).find("input[name='prev_node_title']").val().replace(/\"/g,""),
            enUrl: $(objForm).find("input[name='prev_href']").val(), 
            actid: $(objForm).find("input[name='prev_node_id']").val()
        },
        async: false,
        success: function(res) {
//            if (window.console) {console.log('res', res);}
            if (res.code <= -90) {
                if (window.console) {console.log(res.msg + '請點選確定，重新紀錄閱讀時間');}
                // alert 的缺點是在背景alert完，就直接跑下一個JS指令，使用者根本還沒有點選確定
//                alert(res.msg + '請點選確定，重新紀錄閱讀時間');
                location.reload();
            }
        },
        error: function() {
            if (window.console) {console.log('Get path error!');}
        }
    });
}