var xmlDoc = XmlDocument.create();
var ndm, organization, resources, defaultOrgId = '';
var trackingActivityObj;
var first = true;
var myFrame;
var myPanel;
var isIE = (navigator.userAgent.indexOf('MSIE') > -1);
var learnRecord;

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
	
	// 產生教材路徑 HTML                    .
	myPanel = document.getElementById('course_set');
	rm_whitespace(myPanel);
	myPanel.innerHTML = isIE ? generateOrganizationByXsl(organization) : generateOrganization(organization);
	
}

window.onload=function(){
	if (typeof(_SYNC_NON_IMPLEMENTED) != "undefined") _SYNC_NON_IMPLEMENTED = true;
	xmlDoc.async = false;
	xmlDoc.resolveExternals = false;

	if(document.implementation.createDocument) {
		if( typeof xmlDoc.load == "undefined" ){
			var	xmlHttp	= XmlHttp.create();
			xmlHttp.open('GET', '/learn/path/SCORM_loadCA.php?'+ser+'+'+csid, xmlDoc.async);
			xmlHttp.send();
			var	ret	= xmlHttp.responseText;
			xmlDoc.loadXML(ret);
		}else{

			xmlDoc.load('/learn/path/SCORM_loadCA.php?'+ser+'+'+csid);
		}
		setTimeout('xmlProcessor()', 3000);
	} else {
		if (!xmlDoc.load('/learn/path/SCORM_loadCA.php?'+ser+'+'+csid)){
			alert('Loading XML file failure.'); return;
		}
		xmlProcessor();
	}
};



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
	xslDoc.load('/learn/path/course_info.xsl.php'+slang);
	return htmlspecialchars_decode(node.ownerDocument.transformNode(xslDoc));
}

/**
 * 將 <organization> 轉為 HTML                    .
 */
function generateOrganization(node){
	var htmlCode = '', ret = '', idRef = '', nodeTitle = '', htmlTitle = '', nodeID = '', hasRef = false, isDisable = false, newTarget;
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
	var num = nodes.length;
	
	for(var i=0; i<nodes.length; i++){

		if ('false'  == nodes[i].getAttribute('isvisible')) continue;
		nodeTitle = getTitle(nodes[i]);

        htmlTitle = escapeHtml(stripTags(nodeTitle));
		nodeID    = nodes[i].getAttribute('identifier');
		hasRef    = ((iref = nodes[i].getAttribute('identifierref')) &&
					 (res = resources.selectSingleNode('./resource[@identifier="' + iref + '"]')) &&
					 (href = res.getAttribute('href')) &&
					 (href != 'about:blank')) ? true : false;
		isDisable = nodes[i].getAttribute('disabled') == 'true' ? true : false;
		newTarget = nodes[i].getAttribute('target');

		htmlCode += '<li style="margin-top: 10px;"><div class="course-set-circle"></div>';
	    htmlCode += '<div class="course-set breakword">'+nodeTitle+'</div>';
		htmlCode += '' + ret + '</li>';
		
		if(i != num-1) {
		    htmlCode += '<div class="course-set-line"></div>';
		}
		
	}

	return (htmlCode ? ('<ul style="list-style: none;">' + htmlCode + '</ul>') : '');
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
