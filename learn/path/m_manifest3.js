var xmlDoc = XmlDocument.create();
var ndm, organization, resources, defaultOrgId = '';
var trackingActivityObj;
var first = true;
var myFrame;
var myPanel;
var isIE = (navigator.userAgent.indexOf('MSIE') > -1);
var learnRecord;

/*
DT.NAVBoolean					 = 1 << 20;
DT.NAVVocabulary_event			 = 1 << 21;

var _nav                                   = new Object();
    _nav['event']						   = new Array( W, DT.NAVVocabulary_event);
    _nav['control_mode_enabled']           = new Object();
    _nav['control_mode_enabled']['choice'] = new Array(R , DT.NAVBoolean);
    _nav['control_mode_enabled']['flow']   = new Array(R , DT.NAVBoolean);
    _nav['event_permitted']                = new Object();
    _nav['event_permitted']['continue']	   = new Array(R , DT.NAVBoolean);
    _nav['event_permitted']['previous']	   = new Array(R , DT.NAVBoolean);
*/

var ss							= new Object();
	ss.controlMode				= new Object();
	ss.sequencingRules			= new Object();
	ss.limitConditions			= new Object();
	ss.auxiliaryResources		= new Object();
	ss.rollupRules				= new Object();
	ss.objectives				= new Object();
	ss.randomizationControls	= new Object();
	ss.deliveryControls			= new Object();

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
 * 載入後啟動程序                    .
 */
SCORM_VERSION = '1.2';

function xmlProcessor()
{
	window.status='';

	ndm = new NavigationDataModel();

	SCORM_VERSION = getSCORMVersion();

	// 取得預設路徑的 Organization                    .
	xmlDoc.setProperty('SelectionLanguage', 'XPath');
	organization = xmlDoc.selectSingleNode('/manifest/organizations/organization[@identifier=../@default or position()=1]');
	// xmlDoc.setProperty('SelectionLanguage', 'XSLPattern');
	if (organization == null){
		alert('organization not found.'); return;
	}

	// 取得 resources 段                    .
	resources = xmlDoc.selectSingleNode('/manifest/resources');
	// document.getElementById('fetchResourceForm').iv.value = resources.getAttribute('iv');

	// 產生教材路徑 HTML                    .
	myPanel = document.getElementById('displayPanel');
	rm_whitespace(myPanel);
	myPanel.innerHTML = isIE ? generateOrganizationByXsl(organization) : generateOrganization(organization);
	if (myPanel.getElementsByTagName('div').length <= 0) myPanel.innerHTML = '<h4 style="text-align: center;">'+MSG_NO_DATA+'</h4>';
	var tmp = parent.parent.s_main.document.getElementsByTagName('H2');
	if (tmp && tmp.length > 0) tmp[0].parentNode.removeChild(tmp[0]); // tmp[0].innerHTML = '<Br />' + MSG_FINISH;
	
	// 拉開frame      
/*                        .
	myFrame = parent.parent.document.getElementById('envClassRoom');
	parent.parent.document.getElementById('s_catalog').scrolling = 'no';
	if (myFrame.cols != '200,*')
		myFrame.cols = '200,*';
*/
	// 調整路徑顯示區的大小                    .
	// resizeDisplayPanel();

	// parent.adjustFrameHeight();
	
	// 取得上一次離開的節點                    .
	if (globalCurrentActivity == '' || organization.selectSingleNode('//item[@identifier="' + globalCurrentActivity + '"]') == null){
		// globalCurrentActivity = organization.selectSingleNode('//item[@identifierref]');
		globalCurrentActivity = organization.selectSingleNode('//item[(not(@isvisible) or @isvisible!="false") and (not(@disabled) or @disabled!="true")]');
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
	}

	// 進入第一個 Activity 或回到上次離開的 Activity
	parent.parent.globalState.CurrentActivity = globalCurrentActivity;
	launchActivity(null, globalCurrentActivity);
}

function doUnload()
{
    /*
	parent.parent.document.getElementById('s_catalog').scrolling = 'auto';
	if ((myFrame.cols !== undefined) && (myFrame.cols != '0,*'))
		myFrame.cols = '0,*';
*/
	var objForm = document.getElementById('fetchResourceForm');

	if (objForm.href.value != 'about:blank')
	{
		objForm.href.value = 'about:blank';
		objForm.target = 'empty';
		objForm.submit();
	}
}

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
			var	xmlHttp	= XmlHttp.create();
			xmlHttp.open('GET', 'SCORM_loadCA.php'+ser, xmlDoc.async);
			xmlHttp.send();
			var	ret	= xmlHttp.responseText;
			xmlDoc.loadXML(ret);
		}else{
			xmlDoc.load('SCORM_loadCA.php'+ser);
		}
		// xmlDoc.load('SCORM_loadCA.php'+ser);
		// fix by lubo , chrome & safari end
		setTimeout('xmlProcessor()', 3000);
	}
	else
	{
		if (!xmlDoc.load('SCORM_loadCA.php'+ser)){
			alert('Loading XML file failure.'); return;
		}
		xmlProcessor();
	}
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


/**
 * 全展開或全收攏                    .
 */
var expandingFlag = 'none';
function expandingAll(){
	var nodes = myPanel.getElementsByTagName('img');
	var icon = themePath + (expandingFlag ? 'icon-c.gif' : 'icon-cc.gif') ;
	for(var i=0; i<nodes.length; i++)
		if (nodes[i].parentNode.tagName.toLowerCase() == 'a' &&
			nodes[i].src.search(/icon-cc?\.gif$/) > -1)
		{
			nodes[i].parentNode.parentNode.parentNode.lastChild.style.display = expandingFlag;
			nodes[i].src = icon;
		}
	expandingFlag = expandingFlag ? '' : 'none';
}

/**
 * 單一 Cluster 收攏或展開                    .
 */
function expanding(obj, mode){
	var ulObj = obj.parentNode.parentNode.lastChild;
	var m = (typeof(mode) == 'undefined') ? ulObj.style.display : mode;
	
	if (m == 'none'){
		ulObj.style.display = '' ;
		obj.firstChild.src = themePath + 'icon-cc.gif';
	}
	else{
		ulObj.style.display = 'none' ;
		obj.firstChild.src = themePath + 'icon-c.gif';
	}
	return false;
}

/**
 * 滑鼠滑過的顏色變換                    .
 */
function chBgc(obj,mode){
	if (obj.style.backgroundColor == '#f0f0f0') return;
	obj.className = mode ? "cssTbFocus" : "cssTbBlur";
}

/**
 * 取得 <item> 的 <title> 節點內容，沒有 title 則 return ID                    .
 */
function getTitle(node){
	var title = node.selectSingleNode('./title');
	if (title != null && title.firstChild != null){
		var a = title.firstChild.nodeValue.split('\t');
		switch(lang){
			case 'GB2312'		: return (a[1]?a[1] : a[0]);
			case 'en'			: return (a[2]?a[2] : a[0]);
			case 'EUC-JP'		: return (a[3]?a[3] : a[0]);
			case 'user_define'	: return (a[4]?a[4] : a[0]);
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
	try {	// 避免未更新MSXML時出錯, XP SP2以下只支援MSXML4.0
		xslDoc.setProperty("AllowDocumentFunction", true);
		xslDoc.setProperty("ResolveExternals",true);
		xslDoc.setProperty("AllowXsltScript", true);
	}
	catch(e) {}
	xslDoc.load('m_manifest3.xsl.php'+slang);
	return htmlspecialchars_decode(node.ownerDocument.transformNode(xslDoc));
}

/**
 * 將 <organization> 轉為 HTML                    .
 */
function generateOrganization(node){
	var htmlCode = '', ret = '', idRef = '', nodeTitle = '', htmlTitle = '', nodeID = '', hasRef = false, isDisable = false, newTarget, nodeIntro = '';
	var iref, res, href;
	if (node === null) return '';
	window.status += '|';

	// 取得 <sequencing> 內中，轉成物件                    .
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
        nodeIntro    = ((iref) && (res) &&
					 (nodeIntro = res.getAttribute('intro')) &&
					 (nodeIntro != 'undefined')) ? nodeIntro : '';
		isDisable = nodes[i].getAttribute('disabled') == 'true' ? true : false;
		newTarget = nodes[i].getAttribute('target');
        if ((ret = generateOrganization(nodes[i])) == '') {
			htmlCode += '<div class="box2 selector2" id="' + nodeID + '">' ;
			if (hasRef && !isDisable)
				htmlCode += '<div class="title" style="max-width: 75%; cursor:pointer;" onclick="parent.showPanel(2); return launchActivity(this,\'' + nodeID + '\',\'' + newTarget + '\');" title="'+htmlTitle+'">'+
                            '<div class="icon-node"></div>&nbsp;'+ MSG_UNIT_ORDER.replace('%num%', (i+1)) + ': ' + nodeTitle + '</div>';
			else if (hasRef && isDisable)
				htmlCode += '<div class="title" style="max-width: 75%; cursor:default" onclick="parent.showPanel(2); return launchActivity(this,\'' + nodeID + '\',\'' + newTarget + '\');" disabled title="'+htmlTitle+'">'+
                            '<div class="icon-node"></div>&nbsp;'+ MSG_UNIT_ORDER.replace('%num%', (i+1)) + ': ' + nodeTitle + '</div>';
			else
				htmlCode += '<div class="title" style="max-width: 75%;"><div class="icon-node"></div>&nbsp;'+ MSG_UNIT_ORDER.replace('%num%', (i+1)) + ': '  + nodeTitle + '</div>';
			htmlCode += '<div class="operate"><button class="btn btn-plane-white" style="display: none;" onclick="showExam(\''+nodeID+'\'); return false;">'+MSG_SELF_ASSESSMENT+'</button></div>'+
                        '<div class="content"><div class="data1"><div class="content">' + nodeIntro + '</div></div></div>' +
                        // '<div class="content"><div class="data1"><div class="content">' + ret + '</div></div></div>' +
                        '</div>';
		}
		else {
			htmlCode += '<li id="' + nodeID + '"><span class="cssTbBlur" onmouseover="chBgc(this,true);" onmouseout="chBgc(this,false);">' +
						'<a href="javascript:;" onclick="return expanding(this);">' +
						'<img src="' + themePath + 'icon-cc.gif" valign="absmiddle" border="0"></a>&nbsp;' ;
			if (hasRef && !isDisable)
				htmlCode += '<a href="javascript:;" onclick="expanding(this.previousSibling.previousSibling);return launchActivity(this,\'' + nodeID + '\',\'' + newTarget + '\');" class="cssAnchor" title="'+htmlTitle+'">' + nodeTitle + '</a>';
			else if (hasRef && isDisable)
				htmlCode += '<a href="javascript:;" onclick="expanding(this.previousSibling.previousSibling);return launchActivity(this,\'' + nodeID + '\',\'' + newTarget + '\');" disabled style="cursor:default" class="cssAnchor" title="'+htmlTitle+'">' + nodeTitle + '</a>';
			else
				htmlCode += nodeTitle;
			htmlCode += '</span>' + ret + '</li>';
		}
	}
    return (htmlCode ? (htmlCode) : '');
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
 * 檢查 controlMode 是否目前節點可存取                           .
 */
function checkControlMode(node){
	if (globalCurrentActivity == ''){
	}
	else{
	}
}

/**
 * 開始一個 Activity                    .
 */
var isLaunching = false; // 禁止重入旗標                                                .
function launchActivity(obj,id,target){
	if (isLaunching) return; else isLaunching = true;
	window.status = '';

    // 取得 Activity                    .
	var item = xmlDoc.selectSingleNode('//item[@identifier="' + id + '"]');
	if (item == null ){
		alert('incorrect item id');
		isLaunching = false;
		return false;
	}
    //設定上一節點按鈕的顯示名稱
    var backTxt = item.selectSingleNode('preceding::item[@identifierref][1]');
    var nextTxt = item.selectSingleNode('following::item[@identifierref][1]');
    if (backTxt == null) {
        parent.document.getElementById('backNodeBtn1').setAttribute('class','component disabled');
    } else {
        parent.document.getElementById('backNodeBtn1').setAttribute('class','component');
    }
    if (nextTxt == null) {
        parent.document.getElementById('nextNodeBtn1').setAttribute('class','component disabled');
    } else {
        parent.document.getElementById('nextNodeBtn1').setAttribute('class','component');
    }
    backTxt = backTxt == null ? '': MSG_PREV_UNIT+getTitle(backTxt);
    nextTxt = nextTxt == null ? '': MSG_NEXT_UNIT+getTitle(nextTxt);
    parent.document.getElementById('backNodeBtn1').setAttribute('unit', backTxt);
    parent.document.getElementById('nextNodeBtn1').setAttribute('unit', nextTxt);

	if (item.getAttribute('disabled') == 'true')  {
		isLaunching = false;
		return false;
	}

	// 如果沒設定target,則到該節點取得target
	if (target == null) {
		target = item.getAttribute('target');
	}
	var parent_id = item.parentNode.getAttribute('identifier');
	// 取得 Activity 之 href                    .
	var resource_id = item.getAttribute('identifierref');
	if (typeof(resource_id) == 'undefined') { isLaunching = false; return false; }
	var resource = resources.selectSingleNode('./resource[@identifier="' + resource_id + '"]');
	/*  
	 * 如果是同一個，就看它是否為 SCO，不是就可以再 launch一次
	 */
	if (resource != null && id == globalCurrentActivity && obj != null)
	{
		if (resource.getAttribute('scormtype') != 'sco')
		{
			var base = resource.getAttribute('xml:base') == null ? ' ' : resource.getAttribute('xml:base');
			var href = base + '@' + resource.getAttribute('href');
			var rr = /\.(html?|swf)$/i;
			if (!rr.test(href))
			{
				objForm = document.getElementById('fetchResourceForm');
				objForm.href.value = href;
				objForm.target = (target == '_blank') ? '_blank' : 'viewframe';
				if (target == '_blank') parent.viewframe.location = 'about:blank';
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

	// 判斷是否可點選                    .
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
		// 檢查是否違反 controlMode 存取設定                    .
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
		// 取下一個 Cluster 入口節點                    .
		var NextCluster = item.selectSingleNode('ancestor::item/following::item/descendant-or-self::item[@identifierref]');
		NextClusterId = (NextCluster == null ? '' : NextCluster.getAttribute('identifier'));
		if (typeof(NextClusterId) == 'undefined') NextClusterId = '';

		// 設定下一次點選，可否點選下一個 Cluster 入口節點                    .
		nodes = item.selectNodes('./following::item');
		fetchNextCluster = (nodes.length) ? false : true;
		// xmlDoc.setProperty('SelectionLanguage', 'XSLPattern');
	}


	// 將教材頁送出                    .
	parent.parent.globalState.PrevActivity = parent.parent.globalState.CurrentActivity;
	parent.parent.globalState.CurrentActivity = id;
	objForm = document.getElementById('fetchResourceForm');
	if (resource)
	base = resource.getAttribute('xml:base') == null ? ' ' : resource.getAttribute('xml:base');
	objForm.href.value = (resource == null) ? 'about:blank' : (base + '@' + resource.getAttribute('href'));
	objForm.target = (target == '_blank') ? '_blank' : 'viewframe';
	if (target == '_blank') parent.viewframe.location = 'about:blank';
	objForm.submit();
	fetchServerTime();
	objForm.prev_node_id.value = id;
	objForm.prev_href.value = objForm.href.value;
	objForm.prev_node_title.value = getTitle(item).replace(/(<[^>]*>|^ | $)/g, '').replace(/^&nbsp;/, '');
	trackingActivityObj = new initTMD(item);
	trackingActivityObj['Activity Progress Information']['Activity Attempt Count']++;
	trackingActivityObj['Activity Status Information']['Activity is Active'] = true;
	
    setTimeout(function() {
        // 取得節點狀態
        getLearnRecord();
        $('#displayPanel .icon-node').prop("class", "icon-node");
        $.each(learnRecord.path.item, function(i, v){
            if (v.readed === true) {
                // 修改節點已讀狀態(目前先採用看過即完成)
                $("#" + v.identifier).find(".icon-node").prop("class", "icon-node node-finish");
                // 已完成的單元顯示自我評量按鈕
                if (asmtNum != 0) {
                    $("#" + v.identifier).find(".operate .btn-plane-white").show();
                }
            }
        });

        // 目前進行中節點
        if ($(obj).find('.icon-node').prop("class") == 'icon-node') {
            $(obj).find('.icon-node').prop("class", "icon-node node-progress");
        }

        // 變更進度條
        var aPercent = learnRecord.progress;
        // var aPercent = parseInt((readActivities.length/organization.selectNodes('./item').length)*100);
        $("#progressBar").css("width", aPercent+"%");
        $("#progressBar-text").text(aPercent+"%");
        // Activity 變色
        $(".title").prop("class", "title");
        if (obj == null)
        {
            if(document.getElementById(id) != null) {
                if (!document.getElementsByClassName) {
                    var x = document.getElementById(id).querySelectorAll(".title");
                } else {
                    var x = document.getElementById(id).getElementsByClassName('title');
                }
                if (x != null)
                    x[0].className = "title selected";
            }
        }
        else
        {
            if (obj.parentNode != null)
                obj.className = "title selected";
        }
    },100);

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
 * parse <sequencing> 內容，轉換為 Object                    .
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
 * 從 Server 取回 Tracing Model Data，如果有的話。                       .
 */

function initTMD(node){
//	var xmlTmd = XmlDocument.create();
//	xmlTmd.async = false;
//	xmlTmd.resolveExternals = false;
//	if (!xmlTmd.load('SCORM_loadTMD.php')){
		return (new TrackingModel(node));			// 取不到 (第一次執行)，則產生一個新的                .
//	}
//	else{
//		var tmpTmd = new TrackingModel(null);
//		restoreTMD(tmpTmd, xmlTmd.documentElement);
//		return tmpTmd;
//	}
}

/**
 * 將存成 XML 的 Tracing Model Data 轉成 JS 物件                      .
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
 * Navigation Data Model 物件宣告                    .
 */
function NavigationDataModel(){
	this['event']                          = '';
	this['control_mode_enabled']           = new Object();
	this['control_mode_enabled']['choice'] = true;
	this['control_mode_enabled']['flow']   = true;
	this['event_permitted']                = new Object();
	this['event_permitted']['continue']	   = null;
	this['event_permitted']['previous']	   = null;
}

/**
 * Activity Tracking Model 物件宣告                    .
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
		case 1:	// 往子代找，找不到往弟代找，找不到往叔代找...................................
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
		case -1:	// 往姪代找，找不到往兄代找，找不到往父代找................................
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
	if (frameCol == '200,*'){	// collecting
		parent.document.getElementById('toolbar').style.display = 'none';
		parent.document.getElementById('toolbar1').style.display = '';
		parent.document.getElementById('treePanel').style.display = 'none';
		myFrame.cols= '30,*';
	}
	else{						// expanding
		parent.document.getElementById('toolbar').style.display = '';
		parent.document.getElementById('toolbar1').style.display = 'none';
		parent.document.getElementById('treePanel').style.display = '';
		myFrame.cols= '200,*';
	}
}

function notebook() {
	window.open("/message/write.php?commonuse=true", "", "width=800,height= 600,toolbar=0,menubar=0,scrollbars=1,resizable=0,status=0");
}

// 取得學習記錄
function getLearnRecord() {
    $.ajax({
        url: '/xmlapi/index.php?action=my-course-path-info&cid='+cid+'&ticket='+pTicket,
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
                alert("Get path Error!!");
            }
        },
        error: function() {
            alert("Get path Error!!");
        }
    });
}

// 顯示自我評量
function showExam(nId) {
    nId = nId.replace("I_", "");
    window.open("/learn/path/lcms.php?motion=exam&type=u&rid="+nId+"&num="+asmtNum, "lcmsDialog", "height=" + screen.height + ", width=" + screen.width + "resizable=1,toolbar=0,menubar=0,scrollbars=1,status=0");
}