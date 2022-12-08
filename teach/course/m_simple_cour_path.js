var _GSE_MODE_FIRST = 1;
var _GSE_MODE_LAST  = 2;
var _GSE_MODE_BOTH  = 3;
var _GSE_MODE_ALL   = 4;

var copyBuffer = new Array();
var xmlFile = 'imsmanifest.xml';
lang = lang.replace('-', '_').toLowerCase();
chkBrowser();

var xmlDoc, root, organization, resources, sequencingCollection;
var itemCounter = 1;
var new_item_counter = 1;
var new_resource_counter = 1;
var notSave = false;
var lcmsData = {};
var insertPosition = [0, 0];
var insertFirst = false;


/**
 * 產生一個新的節點 ID
 */
function getCurrentId(){
	var d = new Date();
	return 'SCO_' + course_id + '_' + d.getTime() + '' + Math.ceil(Math.random() * 1000);
}

/**
function initid(){
	// 取得目前最大的 SCO_ID
	var maxIdent = '';
	var nodes = xmlDoc.selectNodes('/manifest/organizations/organization//item/@identifier');
	for(var i=0; i<nodes.length; i++){
		// if (nodes[i].nodeValue.search(/^I_SCO([0-9])+$/) > -1 &&
		if (nodes[i].nodeValue.search(/^WM_ITEM_([0-9])+$/) > -1 &&
			nodes[i].nodeValue > maxIdent){
				maxIdent = nodes[i].nodeValue;
		}
	}
	new_item_counter = (maxIdent != '') ? (parseInt(RegExp.$1) + 1) : 1;

	// 取得目前最大的 RESOURCE_ID
	maxIdent = '';
	nodes = xmlDoc.selectNodes('/manifest/resources/resource/@identifier');
	for(var i=0; i<nodes.length; i++){
		// if (nodes[i].nodeValue.search(/^SCO([0-9])+$/) > -1 &&
		if (nodes[i].nodeValue.search(/^WM_RESOURCE_([0-9])+$/) > -1 &&
			nodes[i].nodeValue > maxIdent){
				maxIdent = nodes[i].nodeValue;
		}
	}
	new_resource_counter = (maxIdent != '') ? (parseInt(RegExp.$1) + 1) : 1;
}
*/

function getTarget() {
	var obj = null;
	switch (this.name) {
		case "s_main": obj = parent.s_catalog; break;
		case "c_main": obj = parent.c_catalog; break;
		case "main"  : obj = parent.catalog;   break;
		case "s_catalog": obj = parent.s_main; break;
		case "c_catalog": obj = parent.c_main; break;
		case "catalog"  : obj = parent.main;   break;
	}
	return obj;
}

/**
 * windows.onload() 事件處理
 */
var editor = new Object();
editor.setHTML = function(x)
{
    if(!xmlDoc.loadXML(x)) {
		alert('Loading XML Failure.'); return;
	}
	xmlDoc.setProperty("SelectionNamespaces", "xmlns:imsss='http://www.imsglobal.org/xsd/imsss'");
	xmlDoc.setProperty("SelectionLanguage"  , "XPath");
	root = xmlDoc.documentElement;
	// organization = root.getElementsByTagName('organizations').item(0).firstChild;
	organization = xmlDoc.selectSingleNode('/manifest/organizations/organization');
	// resources = root.getElementsByTagName('resources').item(0);
	resources = xmlDoc.selectSingleNode('/manifest/resources');
	try{
		sequencingCollection = xmlDoc.selectSingleNode('/manifest/imsss:sequencingCollection');
	}
	catch(e) {}

	displayLayout();

};
function setHeight() {
    var minHeight = 400;
    var windowHeight = $(document).height()>minHeight ? $(document).height() : minHeight;
    var toolbarHeight = $(".box1 > .title").eq(0).height();
    $("#showFrame2").attr('height', windowHeight-toolbarHeight*4);
    $("#displayPanel").css('height', windowHeight-toolbarHeight*4);
    $("#path-view").css('height', 'initial');    
}
$( window ).resize(function() {
    setHeight();
});
var st_id = cur_function + course_id;

window.onload = function(){
    /* 因使用100%無效，修正 IE FIREFOX 高度 */
    setHeight();
	if (isMZ) rm_whitespace(document.getElementById('ssSetupPanel'));

	var obj = getTarget();
	if ((typeof(obj) == 'object') && (obj != null))
	//	obj.location.replace('cour_path_toolbar.php');

	_SYNC_NON_IMPLEMENTED = true;
	xmlDoc = XmlDocument.create();
	xmlDoc.async = false;
	// fix chrome & safari begin
	if (typeof xmlDoc.load === "undefined") {
		var xmlHttp = XmlHttp.create();
		xmlHttp.open('GET', 'cour_path_load.php', xmlDoc.async);
		xmlHttp.send(null);
		if(!xmlDoc.loadXML(xmlHttp.responseText)) {
			alert('Loading XML Failure.'); return;
		}
	} else {
		xmlDoc.resolveExternals = false;
		if(!xmlDoc.load('cour_path_load.php')) {
			alert('Loading XML Failure.'); return;
		}
	}
	// fix chrome & safari end
	xmlDoc.setProperty("SelectionNamespaces", "xmlns:imsss='http://www.imsglobal.org/xsd/imsss'");
	xmlDoc.setProperty("SelectionLanguage"  , "XPath");
	root = xmlDoc.documentElement;
	// organization = root.getElementsByTagName('organizations').item(0).firstChild;
	organization = xmlDoc.selectSingleNode('/manifest/organizations/organization');
	// resources = root.getElementsByTagName('resources').item(0);
	resources = xmlDoc.selectSingleNode('/manifest/resources');
	try{
		sequencingCollection = xmlDoc.selectSingleNode('/manifest/imsss:sequencingCollection');
	}
	catch(e) {}

	displayLayout();
    $('#displayPanel li').eq(1).find('.title').click();

    xajax_check_temp(st_id, 'FCK.editor');

	setExpandEvent(document.getElementById('ssPanel'));

	window.setInterval(function(){if (notSave) xajax_save_temp(st_id, xmlDoc.xml);}, 100000);

	var elems = document.getElementsByTagName('input');
	for (var i=0; i<elems.length; i++)
	    if (elems[i].type == 'text')
	        elems[i].onchange = escape_control_chars;

	var elems = document.getElementsByTagName('textarea');
	for (var i=0; i<elems.length; i++)
        elems[i].onchange = escape_control_chars;

	var elems = document.getElementById('ssSetupPanel').getElementsByTagName('select');
	for (var i=0; i<elems.length; i++)
        elems[i].exclude = "true";
    
    // 判斷是否開啟自我評量，並儲存
    $("#asmt_enable").change(function() {
        if ($(this).is(":checked")) {
            asmtNum = $("#self-asmt").val();
        } else {
            asmtNum = 0;
        }
        showSaveTip(true);
        executing(5);
    });
    $("#self-asmt").change(function() {
        asmtNum = $(this).val();
        if ($("#asmt_enable").is(":checked")) {
            showSaveTip(true);
            executing(5);
        }
    });
};

function escape_control_chars()
{
	this.value = this.value.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F]/g, ' ').replace(/^\s+|\s+$/g, '');
}


/**
 * 離開本功能時，把功能列關閉
 */
window.onunload = function(){
    $('#showFrame2').attr('src', '');
	var obj = getTarget();
	if ((typeof(obj) == 'object') && (obj != null))
		obj.location.replace('about:blank');
};

function trim(val) {
	var re = /\s/g;
	val = val.replace(re, '');
	return val;
}

/**
 * 取得 <item> 節點的 <title> 內容
 */
function getTitle(node){
	var title = node.selectSingleNode('./title');
	if (title != null && title.firstChild != null && trim(title.firstChild.nodeValue)!= ''){
		var a = title.firstChild.nodeValue.split('\t');
		switch(lang){
			case 'gb2312':
				return (typeof(a[1]) != 'undefined') ? a[1] : a[0] ;
			case 'en':
				return (typeof(a[2]) != 'undefined') ? a[2] : a[0] ;
			case 'euc_jp':
				return (typeof(a[3]) != 'undefined') ? a[3] : a[0] ;
			case 'user_define':
				return (typeof(a[4]) != 'undefined') ? a[4] : a[0] ;
			default:
				return a[0] ;
		}
	}
	else
		return '--=[ ' + node.getAttribute('identifier') + ' ]=--';
}

/**
 * 將 SS 的 clustor 以顏色標示
 */
function viewCluster(obj,mode){
	var parent = obj.parentNode;
	var childs = parent.nextSibling.childNodes;
	parent.style.backgroundColor = (mode ? '#CEE79C' : '');
	for(var i=0; i<childs.length; i++)
		if (childs.item(i).tagName == 'LI')
			childs.item(i).style.backgroundColor = (mode ? '#CEE79C' : '');
}

/**
 * 遞迴尋找 <item>
 */
var checkered = 0;
function getChildItem(node){
	var nodes = node.childNodes;
	var idValue = node.getAttribute('identifier');
	var IH = '', childItems, nodeDuration='';
    // 如果節點沒有資料就顯示新增素材訊息
    if (node.getElementsByTagName('item').length == 0) {
        document.getElementById('operates').style.display = 'none';
        document.getElementById('path-view').style.display = 'none';        
        document.getElementById('no-data-msg').style.display = '';
    } else {
        document.getElementById('operates').style.display = '';
        document.getElementById('path-view').style.display = '';        
        document.getElementById('no-data-msg').style.display = 'none';
    }
	for(var i=0, insertNew = "", itemCnt =0; i<nodes.length; i++){
		switch(nodes.item(i).tagName){
			case 'item':
				checkered ^= 1;
				extraStyle = '';
				if (nodes.item(i).getAttribute('isvisible') == 'false') extraStyle = 'text-decoration: line-through;';
				if (nodes.item(i).getAttribute('disabled') == 'true') extraStyle += 'color: gray;';
				idValue = nodes.item(i).getAttribute('identifier');
                // 判斷新增單元的位置，insertPosition[0]=-1為最上方； nodes第一個元素為 title ，所以使用 itemCnt 來取單元的第一個
                if (insertPosition[1] == nodes.item(i).getAttribute('identifier')) {
                    insertNew = "";
                    itemCnt++;
                } else if (itemCnt == 0 && -1 == insertPosition[0]) {
                    insertNew = '<div class="icon-new-tag-s"></div>';
                    itemCnt++;
                }
                nodeDuration = ((iref = nodes.item(i).getAttribute('identifierref')) &&
					 (res = resources.selectSingleNode('./resource[@identifier="' + iref + '"]')) &&
					 (nodeDuration = res.getAttribute('duration')) &&
					 (nodeDuration != 'undefined' && nodeDuration != '00:00:00')) ? nodeDuration : '';
				IH += '<li>' +
                        '<div class="point"></div>' +
                        '<div class="data" style="width: 190px;">' +
                            '<input type="checkbox" name="' + idValue + '" style="display:none;">' +
                            '<div class="section" onclick="selectSection(this); showLCMS(\'' + idValue + '\', \'preview\'); return false;">'+insertNew+ MSG_ASSET_ORDER.replace('%num%', (itemCounter++)) + '</div>' +
                            '<div class="title" onclick="selectSection(this); showLCMS(\'' + idValue + '\', \'preview\'); return false;">' + getTitle(nodes.item(i)) + '</div>' +
                            ((nodeDuration != '') ? '<div class="time">' + nodeDuration + '</div>' : '') +
                            '<div class="operate btn-group">'+
                                '<button class="btn btn-plane-white" onclick="selectSection(this.parentNode); selectLcmsContent(); return false;" title="'+MSG_BTN_INSERT+'"><div class="icon-insert-s"></div></button>'+
                                '<button class="btn btn-plane-white" onclick="selectSection(this.parentNode); showLCMS(\'' + idValue + '\', \'edit\'); return false;" title="'+MSG_BTN_EDIT+'"><div class="icon-edit-s"></div></button>'+
                                '<button class="btn btn-plane-white" onclick="selectSection(this.parentNode); executing(4); return false;" title="'+MSG_BTN_DELETE+'"><div class="icon-delete-s"></div></button>' +
                            '</div>' +
                        '</div>';
				childItems = nodes.item(i).getElementsByTagName('item');
				if (childItems.length > 0){
					IH += '&nbsp;&nbsp;&nbsp;.&nbsp;.&nbsp;.&nbsp;<a href="javascript:;" onclick="CollectExpand(this.parentNode.nextSibling); return false;" onmouseover="viewCluster(this,true);" onmouseout="viewCluster(this,false);"><img src="/theme/' + cur_theme +
						  '/teach/ss.gif" border="0" align="absmiddle" title="' +
						  'Collecting / Expanding' +
						  '"></a></li><ul >' +
						  getChildItem(nodes.item(i)) +
						  '</ul>';
				}
				else
					IH += '</li>';
                
                if (insertPosition[0] == nodes.item(i).getAttribute('identifier')) {
                    insertNew = '<div class="icon-new-tag-s"></div>';
                }
				break;
		}
	}
	return IH;
}

function CollectExpand(obj){
	if (obj.tagName == 'UL')
	{
		var img = obj.previousSibling.lastChild.lastChild;
		if (obj.style.display == '')
		{
			obj.style.display = 'none';
			img.src = img.src.replace(/ss\.gif$/, 'ss1.gif');
		}
		else
		{
			obj.style.display = '';
			img.src = img.src.replace(/ss1\.gif$/, 'ss.gif');
		}
	}
}
/**
 * 將 XML 轉譯成 HTML 顯示出來
 */
function displayLayout(){
	var nodes = organization.childNodes;
	itemCounter = 1;
	/* 舊版的WM2toIM.php在匯入課程時未加上organization的identifier attribute,在這裡判斷若是沒設定則強制加上 */
	if(organization.getAttribute('identifier') == null || organization.getAttribute('identifier') == '')
		organization.setAttribute('identifier', "Course_" + course_id);
    // 短期課程拿掉SSS
	if (isEnableSSS && false) {
		var IH ='<ul><li><a href="javascript:;" onclick="sequencingProperty(\'' + organization.getAttribute('identifier') + '\', false); return false;">' + getTitle(organization) + '</a>&nbsp;&nbsp;' +
			 	'<a href="javascript:;" onclick="globSeqProperty(); return false;" class="link_fnt01">Global Sequencing Setup</a>' +
			 	'</li><ul>' + getChildItem(organization) + '</ul></ul>';
	}
	else {
        // 插入第一個節點前的按鈕
        var firstInsertBtn = '<li style="border-width: 0; margin: 0; padding-bottom: 10px;">'+
                '<button class="btn btn-plane-white" onclick="insertFirst = true; selectLcmsContent(); return false;" style="line-height: 1em; padding: 0 1.3em;" title="'+MSG_BTN_INSERT+'"><div class="icon-insert-s"></div></button>'+
                '</li>';
		var IH = firstInsertBtn + getChildItem(organization);
	}
	document.getElementById('displayPanel').innerHTML = IH;
}

/**
 * 取得勾選的項目
 */
function getSelElement(mode){
	var obj = document.getElementById('displayPanel');
	var nodes = obj.getElementsByTagName('input');
	var ret = '';

	for(var i=0; i<nodes.length; i++){
		switch(mode){
			case _GSE_MODE_FIRST:   // 取第一個
				if (nodes.item(i).checked) return i;
				break;
			case _GSE_MODE_LAST :   // 取最後一個
			case _GSE_MODE_BOTH :   // 取第一個和最後一個
			case _GSE_MODE_ALL  :   // 取全部有勾選的
				if (nodes.item(i).checked) ret += (i + ',');
				break;
		}
	}
	ret = ret.replace(/,$/, '');
	var aa = ret.split(',');
	if (aa.length < 2 && (mode == _GSE_MODE_LAST || mode == _GSE_MODE_BOTH) ){
		alert(MSG_NEED2);
		return false;
	}
	switch(mode){
		case _GSE_MODE_LAST:
			return aa[aa.length-1];
		case _GSE_MODE_BOTH:
			return (aa[0] + ',' + aa[aa.length-1]);
		default:
			return ret;
	}
}

/**
 * 設定勾選的項目
 */
function setSelElementById(ret)
{
    var form = document.getElementById('mainForm');
    var aa = ret.split(',');
	for (var i = 0; i < aa.length; i++) {
        eval('form.' + aa[i] + '.checked = true;');
	}
}

/**
 * 弟節點轉為子節點
 */
function Brother2child(node){
	var cur = node.nextSibling;
	var newNode;
	while(cur != null && cur.tagName == 'item'){
		newNode = cur.cloneNode(true);
		node.appendChild(newNode);
		node.parentNode.removeChild(cur);
		cur = node.nextSibling;
	}
}

/**
 * 子節點轉為弟節點
 */
function Child2Brother(node){
	var nodes = node.getElementsByTagName('item');
	var newNode, ref;
	if (nodes.length == 0) return;
	nodes = node.childNodes;
	for(var i=(nodes.length-1); i>=0; i--){
		if (nodes.item(i).tagName == 'item'){
			newNode = nodes.item(i).cloneNode(true);
			node.removeChild(nodes.item(i));
			ref = node.nextSibling;
			if (ref == null)
				node.parentNode.appendChild(newNode);
			else
				node.parentNode.insertBefore(newNode, ref);
		}
	}
}

/**
 * 取得最近一個 <item> 兄節點
 */
function getPrevSibling(node){
	var cur = node;
	while(cur.previousSibling != null){
		cur = cur.previousSibling;
		if (cur.tagName == 'item') return cur;
	}
	return null;
}

/**
 * 取得最近一個 <item> 弟節點
 */
function getNextSibling(node){
	var cur = node;
	while(cur.nextSibling != null){
		cur = cur.nextSibling;
		if (cur.tagName == 'item') return cur;
	}
	return null;
}


/**
 * 取得第一個 <item> 節點
 */
function getFirstItem(node){
	var cur = node.firstChild;
    if (cur.tagName == 'item') {
        return cur;
    } else {
        while(cur.nextSibling != null){
            cur = cur.nextSibling;
            if (cur.tagName == 'item') return cur;
        }
    }
	return null;
}

/**
 * 初始化一個新節點
 */
function newNodeInit(newNode, extra){
	var new_id = getCurrentId();
	var title = newNode.getElementsByTagName('title')[0];
	if (title != null) title.firstChild.nodeValue = 'undefined';
	newNode.setAttribute('identifier', 'I_' + new_id);
	newNode.setAttribute('identifierref', new_id);
	// newNode.setAttribute('isvisible', 'true');

	var nNode = xmlDoc.createElement('resource');
	nNode = resources.appendChild(nNode);
	nNode.setAttribute('identifier', new_id);
	nNode.setAttribute('adlcp:scormtype', 'asset');
    if (extra === undefined) {
        nNode.setAttribute('type', 'webcontent');
        nNode.setAttribute('href', 'about:blank');
    } else {
        title.firstChild.nodeValue = extra.title;
        nNode.setAttribute('type', extra.kind);
        nNode.setAttribute('href', extra.href);
        nNode.setAttribute('duration', extra.duration);
        nNode.setAttribute('uid', extra.uid);
    }

	return 'I_' + new_id; // 傳回新節點 ID
}

/**
 * 移除某個 resource
 */
function removeResource(rid){

	var refs = organization.selectNodes("//item[@identifierref='" + rid + "']");
	if (refs.length > 1) return; // 如果參考這個 rid 的 item 超過 1 個，那就不必刪掉
	var rNode = xmlDoc.selectSingleNode("/manifest/resources/resource[@identifier='" + rid + "']");
	if (rNode != null) resources.removeChild(rNode);

}

/**
 * 取消匯入的功能
 */
function CancelImport() {
    var
        elem = document.getElementById('msgSelectContent'),
        btn = document.getElementById('btnSelectLcms');

    elem.innerHTML = '&nbsp;';
    btn.value = MSG_BTN_SELECT;

    document.getElementById('importPanel').style.display = 'none';
}

/**
 * 執行功能
 */
var cut_or_copy = '';
var recoveryWin = null;
function executing(idx){
	document.getElementById('nodeSetupPanel').style.display = 'none';
	switch(idx){
		case  1: // 新增(複製最後一個節點)
			var newNode = organization;
			newNode = newNode.appendChild(xmlDoc.createElement('item'));
			newNode = newNode.appendChild(xmlDoc.createElement('title'));
			newNode.appendChild(xmlDoc.createTextNode('undefined'));
			NodeProperty( newNodeInit(newNode.parentNode) , idx);
			notSave = true;
			break;
		case  2: // 插入(在第一個選取節點位置，複製該節點)
			var cur = getSelElement(_GSE_MODE_FIRST);
			if (cur === false || cur === '') return;
			var nodes = organization.getElementsByTagName('item');
			var curItem = nodes.item(parseInt(cur,10));
			var newNode = curItem.parentNode.insertBefore(xmlDoc.createElement('item'),curItem);
			newNode = newNode.appendChild(xmlDoc.createElement('title'));
			newNode.appendChild(xmlDoc.createTextNode('undefined'));
			NodeProperty( newNodeInit(newNode.parentNode) );
			notSave = true;
			break;
		case  3: // 節點內容編輯
			var cur = getSelElement(_GSE_MODE_FIRST);
			if (cur === false || cur === '') return;
			var nodes = organization.getElementsByTagName('item');
			NodeProperty(nodes.item(parseInt(cur,10)).getAttribute('identifier'), idx);
			break;
		case  4: // 刪除節點(刪除所有勾選的節點)
			var cur = getSelElement(_GSE_MODE_ALL);
			if (cur === false || cur === '' || !confirm(MSG_DELETE)) return;
			var nodes = organization.getElementsByTagName('item');
			var aa = cur.split(',');
			var ii = 0, childs;
            // 刪除倒插入的位置判斷節點時，變更 insertPosition
            var firstNode = nodes.item(parseInt(aa[0],10));
            var lastNode = nodes.item(parseInt(aa[aa.length-1],10));
            if (insertPosition[0] == firstNode.getAttribute("identifier")) {
                if (getPrevSibling(firstNode) !== null) {
                    insertPosition[0] = getPrevSibling(firstNode).getAttribute("identifier")
                } else {
                    insertPosition[0] = -1;
                }
            }
            if (insertPosition[1] == lastNode.getAttribute("identifier")) {
                if (getNextSibling(lastNode) !== null) {
                    insertPosition[1] = getNextSibling(lastNode).getAttribute("identifier")
                } else {
                    insertPosition[1] = 0;
                }
            }
			for(var i=(aa.length-1); i>=0; i--){
				ii = parseInt(aa[i],10);
				removeResource(nodes.item(ii).getAttribute('identifierref')); // 移除該節點所參考的 resource
				childs = nodes.item(ii).getElementsByTagName('item');
				if (childs.length > 0){ // 如果有子節點，則長子取代父親
					Brother2child(childs.item(0));
					newNode = childs.item(0).cloneNode(true);
					nodes.item(ii).parentNode.replaceChild(newNode,nodes.item(ii));
				}
				else{ // 沒有子節點則直接刪除
					nodes.item(ii).parentNode.removeChild(nodes.item(ii));
				}
			}
			notSave = true;
			break;
		case  5: // 存檔
			if (copyBuffer.length != 0 && cut_or_copy == 'cut')
				if (!confirm(MSG_CONFIRM)) return;
			copyBuffer.length = 0;
			xajax_clean_temp(st_id);
			var xmlHttp = XmlHttp.create();
			xmlHttp.open('POST', 'cour_path_save.php?cid='+MOD_COURSE_ID+'&typep=2'+'&num='+asmtNum, false);
			xmlHttp.send(xmlDoc);

			var rspTxt = xmlHttp.responseText;
			var msg_result = rspTxt.substr(0,5);
			var msg_same_cid = rspTxt.substr(5,1);

			// alert(msg_result);
			if(msg_same_cid=='N')
				alert(MSG_SAME_CID);
			// alert(xmlHttp.responseText);
			notSave = false;
			break;
		case  6: // 複製
		case  7: // 剪下
			var cur = getSelElement(_GSE_MODE_ALL);
			if (cur === false || cur === '') return;
			if (!confirm(idx==6?MSG_COPY:MSG_CUT)) return;
			copyBuffer.length = 0;
			var nodes = organization.getElementsByTagName('item');
			var aa = cur.split(',');
			var ii = 0;
			for(var i=(aa.length-1); i>=0; i--){
				ii = parseInt(aa[i],10);
				copyBuffer[i] = nodes.item(ii).cloneNode(true);
				if (idx == 7) nodes.item(ii).parentNode.removeChild(nodes.item(ii)); // 剪下的話則畫面中也要移除
			}
			for(var i=0; i<aa.length; i++) aa[i]++;
			alert(idx==6?MSG_NODE+aa.join()+MSG_COPY1:MSG_NODE+aa.join()+MSG_CUT1);
			cut_or_copy = (idx == 6 ? 'copy' : 'cut');
			executing(16); // 全消
			break;
		case  8: // 貼上
			if (copyBuffer.length == 0){
				alert(MSG_EMPTY);
				return;
			}
			var cur = getSelElement(_GSE_MODE_FIRST);
			if (cur === false || cur === '') return;
			var nodes = organization.getElementsByTagName('item');
			var curItem = nodes.item(parseInt(cur,10));
			var new_resource;

			if (cut_or_copy == 'cut') {
				for(var i=0; i<copyBuffer.length; i++)
					curItem.parentNode.insertBefore(copyBuffer[i], curItem);
			}
			else { // 如果是複製節點,則該子節點下的所有節點都要更改identifier與identifierref
				var new_id = getCurrentId();
				var id_1 = new_id.substring(0, new_id.lastIndexOf('_'));
				var id_2 = parseInt(new_id.substring(new_id.lastIndexOf('_')+1));
				for(var i=0; i<copyBuffer.length; i++){
					var tmp_nodes = copyBuffer[i].selectNodes('//item[@identifier] | .'); // 取得自己與自己的子節點
					for (var j = 0; j < tmp_nodes.length; j++) {
						nid = id_1 + '_' + (id_2++);
						tmp_nodes[j].setAttribute('identifier', 'I_' + nid);
						identifierref = tmp_nodes[j].getAttribute('identifierref');			// 判斷該節點是否有resource,有的話則複製一份新的resource
						if (identifierref) {
							tmp_nodes[j].setAttribute('identifierref', nid);
							old_resource = xmlDoc.selectSingleNode('/manifest/resources/resource[@identifier="' + identifierref + '"]');
							if (old_resource == null) {	// 如果舊節點的resource不存在則建立一份
								old_resource = resources.appendChild(xmlDoc.createElement('resource'));
								old_resource.setAttribute('identifier', identifierref);
								old_resource.setAttribute('adlcp:scormtype', 'asset');
								old_resource.setAttribute('type', 'webcontent');
								old_resource.setAttribute('href', 'about:blank');
							}
							new_resource = old_resource.cloneNode(true);
							new_resource.setAttribute('identifier', nid);
							resources.appendChild(new_resource);
						}
					}
					curItem.parentNode.insertBefore(copyBuffer[i], curItem);
				}
			}
			copyBuffer.length = 0;
			notSave = true;
			cut_or_copy = '';
			break;
		case  9: // 左移
			var cur = getSelElement(_GSE_MODE_ALL);
			if (cur === false || cur === '') return;
			var nodes = organization.getElementsByTagName('item');
			var aa = cur.split(',');
			var ii=0, prev, newNode, childs, c=0, ret = '';
			for(var i=(aa.length-1); i>=0; i--){
				ii = parseInt(aa[i],10);
				if (nodes.item(ii).parentNode.tagName == 'item'){
					Brother2child(nodes.item(ii));
					prev = nodes.item(ii).parentNode;
					newNode = nodes.item(ii).cloneNode(true);
					nodes.item(ii).parentNode.removeChild(nodes.item(ii));
					if (prev.nextSibling == null){
						prev.parentNode.appendChild(newNode);
					}
					else{
						prev.parentNode.insertBefore(newNode, prev.nextSibling);
					}
					ret += (newNode.getAttribute('identifier') + ',');
				}
				else {
					alert('No. ' + (ii+1) + ' ' + MSG_EDGE);
					ret += (nodes.item(ii).getAttribute('identifier') + ',');
				}
			}
			notSave = true;
			break;
		case 10: // 右移
			var cur = getSelElement(_GSE_MODE_ALL);
			if (cur === false || cur === '') return;
			var nodes = organization.getElementsByTagName('item');
			var aa = cur.split(',');
			var ii=0, prev, newNode, ret = '';
			for(var i=(aa.length-1); i>=0; i--){
				ii = parseInt(aa[i],10);
				prev = getPrevSibling(nodes.item(ii));
				if (prev == null) {
					alert('No. ' + (ii+1) + ' ' + MSG_EDGE);
					ret += (nodes.item(ii).getAttribute('identifier') + ',');
				}
				else{
					newNode = nodes.item(ii).cloneNode(true);
					nodes.item(ii).parentNode.removeChild(nodes.item(ii));
					Child2Brother(prev.appendChild(newNode));
					ret += (newNode.getAttribute('identifier') + ',');
				}
			}
			notSave = true;
			break;
		case 11: // 上移
			var cur = getSelElement(_GSE_MODE_ALL);
			if (cur === false || cur === '') return;
			var nodes = organization.getElementsByTagName('item');
			var aa = cur.split(',');
			var ii=0, prev, newNode, ret = '';
			for(var i=0; i<aa.length; i++){
				ii = parseInt(aa[i],10);
				prev = getPrevSibling(nodes[ii]);
				if (prev != null){
					newNode = nodes[ii].cloneNode(true);
					nodes[ii].parentNode.removeChild(nodes[ii]);
					prev.parentNode.insertBefore(newNode, prev);
					ret += (newNode.getAttribute('identifier') + ',');
				}
				else
					alert('Node.' + (ii+1) + ' ' + MSG_ENDS);
			}
			notSave = true;
			break;
		case 12: // 下移
			var cur = getSelElement(_GSE_MODE_ALL);
			if (cur === false || cur === '') return;
			var nodes = organization.getElementsByTagName('item');
			var aa = cur.split(',');
			var ii=0, prev, newNode, ret = '';
			for(var i=(aa.length-1); i>=0; i--){
				ii = parseInt(aa[i],10);
				prev = getNextSibling(nodes.item(ii));
				if (prev != null){
					newNode = prev.cloneNode(true);
                    // #49218 chrome 辦公室-課程管理-學習路徑-新增節點後，節點移動功能有問題 b
                    nowNode = nodes.item(ii);
					nodes.item(ii).parentNode.insertBefore(newNode, nodes.item(ii));
					nodes.item(ii).parentNode.removeChild(prev);
					ret += (nowNode.getAttribute('identifier') + ',');
                    // #49218 chrome 辦公室-課程管理-學習路徑-新增節點後，節點移動功能有問題 e
				}
				else
					alert('Node.' + (ii+1) + ' ' + MSG_ENDS2);
			}
			notSave = true;
			break;
		case 13: // 匯出
			if (notSave)
			{
				alert(MSG_SAVE);
				return;
			}
			obj = document.getElementById('exportForm');
			obj.target = 'empty';
			obj.submit();
			obj.target = '_self';
			// document.getElementById('exportForm').submit();
            break;
        case 14: // 匯入
            cancelLcmsImport();
            if (notSave)
			{
                alert(MSG_SAVE);
                return;
            }
			var importObj = document.getElementById('importPanel');
			// 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(500)] 再左移 10 個 pixel
			importObj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 510;
			// 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
			importObj.style.top   = document.body.scrollTop  + 10;
			importObj.style.display = '';
			break;
		case 15: // 全選取
		case 16: // 全消除選取
			var obj = document.getElementById('displayPanel');
			var nodes = obj.getElementsByTagName('input');
			for(var i=0; i<nodes.length; i++)
				if (nodes.item(i).getAttribute('type') == 'checkbox')
					nodes.item(i).checked = (idx & 1) ? true : false;
			break;
		case 17: // 批次隱藏、顯示切換
			var cur = getSelElement(_GSE_MODE_ALL);
			if (cur === false || cur === '') return;
			var nodes = organization.getElementsByTagName('item');
			var aa = cur.split(',');
			var ii=0, prev, newNode, ret = '';
			for(var i=(aa.length-1); i>=0; i--){
				ii = parseInt(aa[i],10);
                if (nodes.item(ii).getAttribute('isvisible') == 'false')
                    nodes.item(ii).removeAttribute('isvisible');
                    // nodes.item(ii).setAttribute('isvisible', 'true');
				else
				    nodes.item(ii).setAttribute('isvisible', 'false');
			}
			notSave = true;
			break;
		case 18: // 學習路徑備份還原
			if (recoveryWin == null || recoveryWin.closed)
				recoveryWin = window.open('cour_path_recover.php?cid='+MOD_COURSE_ID, '', 'top=150px,left=200px,width=660px,height=450px,toolbar=0,menubar=0,scrollbars=1,resizable=1,status=0');
			else
				recoveryWin.focus();
			break;
        case 19: // LCMS 教材匯入
            if (!lcmsEnable) {
                return;
            }
            CancelImport();
            var importObj = document.getElementById('lcmsSetupPanel');
            // 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(500)] 再左移 10 個 pixel
            importObj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 560;
            // 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
            importObj.style.top   = document.body.scrollTop  + 10;
            importObj.style.display = '';
            break;
	}

	// if ((idx > 0 && idx < 6) || (idx > 7 && idx < 14)) displayLayout();
    // 因應自動儲存，idx=4(刪除)動作後會再作儲存，儲存不作 displayLayout
	if ((idx > 0 && idx < 14 && idx != 3 && idx != 1 && idx != 5) || idx == 17) displayLayout();
	if (idx >= 9 && idx <= 12 && ret && ret != '') {
		ret = ret.replace(/,$/, '');
		setSelElementById(ret);
	}
    if (idx == 4) {
        /* 配合自動儲存，在刪除後同時作儲存 */
        showSaveTip(true);
        executing(5);        
    }
    if (idx == 5) {
        showSaveTip(false);
    }
}

/**
 * 輸入選擇區間
 */
function selectRang(from, to){
	var re = new RegExp(/^\d+$/);
	if (!re.test(from) || !re.test(to) || from < 1 || to < 1 || from >= itemCounter || to >= itemCounter)
	{
		alert(MSG_OVER);
		return;
	}

	from--;
	to--;

	if (to < from){ from ^= to; to ^= from; from ^= to; }
	var obj = document.getElementById('displayPanel');
	var nodes = obj.getElementsByTagName('input');
	for(var i=0; i<nodes.length; i++)
		if (i >= from && i <= to)
			nodes.item(i).checked ^= 1;
}

/**
 * 回存controlMode到Sequencing Setup Form中
 */
function restoreControlMode(node, objForm) {
    objForm.controlMode.click();
    if (node.getAttribute('choice') && node.getAttribute('choice') == 'false') objForm.choice.checked = false;
    if (node.getAttribute('choiceExit') && node.getAttribute('choiceExit') == 'false') objForm.choiceExit.checked = false;
    if (node.getAttribute('flow') && node.getAttribute('flow') == 'true') objForm.flow.checked = true;
    if (node.getAttribute('forwardOnly') && node.getAttribute('forwardOnly') == 'true') objForm.forwardOnly.checked = true;
    if (node.getAttribute('useCurrentAttemptObjectiveInfo') && node.getAttribute('useCurrentAttemptObjectiveInfo') == 'false') objForm.useCurrentAttemptObjectiveInfo.checked = false;
    if (node.getAttribute('useCurrentAttemptProgressInfo') && node.getAttribute('useCurrentAttemptProgressInfo') == 'false')  objForm.useCurrentAttemptProgressInfo.checked = false;
}

/**
 * 回存constrainedChoiceConsiderations到Sequencing Setup Form中
 */
function restoreConstrainedChoice(node, objForm) {
    objForm.constrainedChoiceConsiderations.click();
    if (node.getAttribute('constrainChoice') && node.getAttribute('constrainChoice') == 'true') objForm.constrainChoice.checked = true;
    if (node.getAttribute('preventActivation') && node.getAttribute('preventActivation') == 'true') objForm.preventActivation.checked = true;
}

/**
 * 回存SequencingRules到Sequencing Setup Form中
 */
function restoreSequencingRules(node, objForm) {

	objForm.sequencingRules.click();

	var inputs, selects, ruleConditions, conditionRules, ruleCondition, condition, operator, referenceObjective, measureThreshold;

	conditionRules = node.childNodes;

	for (var i = 0; i < conditionRules.length - 1; i++)
		addSibling(objForm.sequencingRules.parentNode.getElementsByTagName('a')[0]);

	var childs = objForm.sequencingRules.parentNode.lastChild.childNodes;
	for(var i = 0; i < conditionRules.length; i++){
		if(childs[i].tagName != 'LI') continue;
		inputs  = childs[i].getElementsByTagName('input');
		selects = childs[i].getElementsByTagName('select');
		inputs[0].click();
		switch (conditionRules[i].tagName) {
			case 'imsss:preConditionRule' : selects[0].value = 'preConditionRule'; break;
			case 'imsss:postConditionRule' : selects[0].value = 'postConditionRule'; break;
			case 'imsss:exitConditionRule' : selects[0].value = 'exitConditionRule'; break;
		}
		selects[0].onchange();

		ruleConditions = conditionRules[i].selectSingleNode('./imsss:ruleConditions');
		if (ruleConditions)
			(ruleConditions.getAttribute('conditionCombination') == 'any') ? inputs[1].checked = true : inputs[2].checked = true;
		else
			continue;
		ruleCondition = ruleConditions.selectNodes('./imsss:ruleCondition');
		if (ruleCondition) {
			for (j = 0; j < ruleCondition.length; j++) {
				condition = ruleCondition[j].getAttribute('condition');
				operator = (ruleCondition[j].getAttribute('operator') == 'not' ? true : false);
				referenceObjective = ruleCondition[j].getAttribute('referenceObjective');
				measureThreshold = ruleCondition[j].getAttribute('measureThreshold');
				switch (condition) {
					case 'satisfied' :
						inputs[3].click();
						if (operator) inputs[4].checked = true;
						if (referenceObjective) {inputs[5].checked = true; inputs[6].value = referenceObjective;}
						break;
					case 'objectiveStatusKnown' :
						inputs[7].click();
						if (operator) inputs[8].checked = true;
						if (referenceObjective) {inputs[9].checked = true; inputs[10].value = referenceObjective;}
						break;
					case 'objectiveMeasureKnown' :
						inputs[11].click();
						if (operator) inputs[12].checked = true;
						if (referenceObjective) {inputs[13].checked = true; inputs[14].value = referenceObjective;}
						break;
					case 'objectiveMeasureGreaterThan' :
						inputs[15].click();
						if (operator) inputs[16].checked = true;
						if (referenceObjective) {inputs[17].checked = true; inputs[18].value = referenceObjective;}
						if (measureThreshold) {inputs[19].checked = true; inputs[20].value = measureThreshold;}
						break;
					case 'objectiveMeasureLessThan' :
						inputs[21].click();
						if (operator) inputs[22].checked = true;
						if (referenceObjective) {inputs[23].checked = true; inputs[24].value = referenceObjective;}
						if (measureThreshold) {inputs[25].checked = true; inputs[26].value = measureThreshold;}
						break;
					case 'completed' :
						inputs[27].click();
						if (operator) inputs[28].checked = true;
						break;
					case 'activityProgressKnown' :
						inputs[29].click();
						if (operator) inputs[30].checked = true;
						break;
					case 'attempted' :
						inputs[31].click();
						if (operator) inputs[32].checked = true;
						break;
					case 'attemptLimitExceeded' :
						inputs[33].click();
						if (operator) inputs[34].checked = true;
						break;
					case 'timeLimitExceeded' :
						inputs[35].click();
						if (operator) inputs[36].checked = true;
						break;
					case 'outsideAvailableTimeRange' :
						inputs[37].click();
						if (operator) inputs[38].checked = true;
						break;
					case 'always' :
						inputs[39].click();
						if (operator) inputs[40].checked = true;
						break;
				}
			}
		}

		action = conditionRules[i].selectSingleNode('./imsss:ruleAction');
		if (action) {
			action = action.getAttribute('action');
			switch (conditionRules[i].tagName) {
				case 'imsss:preConditionRule' : selects[1].value = action; break;
				case 'imsss:postConditionRule' : selects[2].value = action;break;
				case 'imsss:exitConditionRule' : selects[3].value = action;break;
			}
		}
	}
}

/**
 * 回存limitConditions到Sequencing Setup Form中
 */
function restoreLimitConditions(node, objForm) {
	objForm.limitConditions.click();
    if (node.getAttribute('attemptLimit')) {
    	objForm.attemptLimit.click();
    	objForm.attemptLimitValue.value = node.getAttribute('attemptLimit');
    }
    if (node.getAttribute('attemptAbsoluteDurationLimit')) {
    	objForm.attemptAbsoluteDurationLimit.click();
    	tmp = node.getAttribute('attemptAbsoluteDurationLimit').split(':');
    	objForm.attemptAbsoluteDurationLimit_hour.value = parseFloat(tmp[0]);
    	objForm.attemptAbsoluteDurationLimit_minute.value = parseFloat(tmp[1]);
    	objForm.attemptAbsoluteDurationLimit_second.value = parseFloat(tmp[2].substring(0,2));
    }
    if (node.getAttribute('attemptExperiencedDurationLimit')) {
    	objForm.attemptExperiencedDurationLimit.click();
    	tmp = node.getAttribute('attemptExperiencedDurationLimit').split(':');
    	objForm.attemptExperiencedDurationLimit_hour.value = parseFloat(tmp[0]);
    	objForm.attemptExperiencedDurationLimit_minute.value = parseFloat(tmp[1]);
    	objForm.attemptExperiencedDurationLimit_second.value = parseFloat(tmp[2].substring(0,2));
    }
    if (node.getAttribute('activityAbsoluteDurationLimit')) {
    	objForm.activityAbsoluteDurationLimit.click();
    	tmp = node.getAttribute('activityAbsoluteDurationLimit').split(':');
    	objForm.activityAbsoluteDurationLimit_hour.value = parseFloat(tmp[0]);
    	objForm.activityAbsoluteDurationLimit_minute.value = parseFloat(tmp[1]);
    	objForm.activityAbsoluteDurationLimit_second.value = parseFloat(tmp[2].substring(0,2));
    }
    if (node.getAttribute('activityExperiencedDurationLimit')) {
    	objForm.activityExperiencedDurationLimit.click();
    	tmp = node.getAttribute('activityExperiencedDurationLimit').split(':');
    	objForm.activityExperiencedDurationLimit_hour.value = parseFloat(tmp[0]);
    	objForm.activityExperiencedDurationLimit_minute.value = parseFloat(tmp[1]);
    	objForm.activityExperiencedDurationLimit_second.value = parseFloat(tmp[2].substring(0,2));
    }
    if (node.getAttribute('beginTimeLimit')) {
    	objForm.beginTimeLimit.click();
    	tmp = node.getAttribute('beginTimeLimit').split('T');
    	day = tmp[0].split(':'); tm = tmp[1].split(':');
    	objForm.beginTimeLimit_year.value = parseFloat(day[0]);
    	objForm.beginTimeLimit_month.value = parseFloat(day[1]);
    	objForm.beginTimeLimit_day.value = parseFloat(day[2]);
    	objForm.beginTimeLimit_hour.value = parseFloat(tm[0]);
    	objForm.beginTimeLimit_minute.value = parseFloat(tm[1]);
    	objForm.beginTimeLimit_second.value = parseFloat(tm[2].substring(0,2));
    }
    if (node.getAttribute('endTimeLimit')) {
    	objForm.endTimeLimit.click();
    	tmp = node.getAttribute('endTimeLimit').split('T');
    	day = tmp[0].split(':'); tm = tmp[1].split(':');
    	objForm.endTimeLimit_year.value = parseFloat(day[0]);
    	objForm.endTimeLimit_month.value = parseFloat(day[1]);
    	objForm.endTimeLimit_day.value = parseFloat(day[2]);
    	objForm.endTimeLimit_hour.value = parseFloat(tm[0]);
    	objForm.endTimeLimit_minute.value = parseFloat(tm[1]);
    	objForm.endTimeLimit_second.value = parseFloat(tm[2].substring(0,2));
    }
}

/**
  * 回存auxiliaryResources到Sequencing Setup Form中
  */
function restoreAuxiliaryResources(node, objForm) {
	objForm.auxiliaryResources.click();
    auxNodes = node.childNodes;
    for (var j = 0; j < auxNodes.length - 1; j++)
    	objForm.auxiliaryResources.parentNode.getElementsByTagName('a')[0].click();
    child = objForm.auxiliaryResources.parentNode.lastChild.childNodes;
    for(j = 0; j < auxNodes.length; j++){
        if(child[j].tagName != 'LI') continue;
        childs = child[j].getElementsByTagName('input');
        childs[0].click();
        childs[1].value = auxNodes[j].getAttribute('auxiliaryResourceID');
        childs[2].value = auxNodes[j].getAttribute('purpose');
    }
}

/**
 * 回存RollupRules到Sequencing Setup Form中
 */
function restoreRollupRules(node, objForm) {
	objForm.rollupRules.click();
	if (node.getAttribute('rollupObjectiveSatisfied') == 'false')  objForm.rollupObjectiveSatisfied.checked = false;
	if (node.getAttribute('rollupProgressCompletion') == 'false')  objForm.rollupProgressCompletion.checked = false;
    if (node.getAttribute('ObjectiveMeasureWeight')) {
    	objForm.ObjectiveMeasureWeight.click();
    	objForm.ObjectiveMeasureWeightValue.value = node.getAttribute('ObjectiveMeasureWeight');
    }

    rollupNodes = node.selectNodes('./imsss:rollupRule');

	for (var i = 0; i < rollupNodes.length - 1; i++)
		addSibling(objForm.rollupRules.parentNode.getElementsByTagName('a')[0]);

	child = objForm.rollupRules.parentNode.lastChild.childNodes;
	for(i = 3, j = 0; i < rollupNodes.length + 3; i++, j++){
		if(child[i].tagName != 'LI') continue;
		inputs = child[i].getElementsByTagName('input');
		selects = child[i].getElementsByTagName('select');
		inputs[0].click();
		if (rollupNodes[j].getAttribute('childActivitySet')) {selects[0].value = rollupNodes[j].getAttribute('childActivitySet'); selects[0].onchange();}
		if (rollupNodes[j].getAttribute('minimunCount')) inputs[1].value = rollupNodes[j].getAttribute('minimunCount');
		if (rollupNodes[j].getAttribute('minimunPercent')) inputs[2].value = rollupNodes[j].getAttribute('minimunPercent');

		rollupConditions = rollupNodes[j].selectSingleNode('./imsss:rollupConditions');
		if (rollupConditions) {
			(rollupConditions.getAttribute('conditionCombination') == 'all') ? (inputs[3].checked = true) : (inputs[4].checked = true);
			rollupConditions = rollupConditions.selectNodes('./imsss:rollupCondition');
			if (rollupConditions) {
				for (k = 0; k < rollupConditions.length; k++) {
					operator = rollupConditions[k].getAttribute('operator') == 'not' ? true : false;
					switch (rollupConditions[k].getAttribute('condition')) {
						case 'satisfied' : inputs[5].checked = true;  if (operator) inputs[6].checked = true; break;
						case 'objectiveStatusKnown' : inputs[7].checked = true;  if (operator) inputs[8].checked = true; break;
						case 'objectiveMeasureKnown' : inputs[9].checked = true;  if (operator) inputs[10].checked = true; break;
						case 'completed' : inputs[11].checked = true;  if (operator) inputs[12].checked = true; break;
						case 'activityProgressKnown' : inputs[13].checked = true;  if (operator) inputs[14].checked = true; break;
						case 'attempted' : inputs[15].checked = true;  if (operator) inputs[16].checked = true; break;
						case 'attemptLimitExceeded' : inputs[17].checked = true;  if (operator) inputs[18].checked = true; break;
						case 'timeLimitExceeded' : inputs[19].checked = true;  if (operator) inputs[20].checked = true; break;
						case 'outsideAvailableTimeRange' : inputs[21].checked = true;  if (operator) inputs[22].checked = true; break;
					}
				}
			}
		}

		rollupAction = rollupNodes[j].selectSingleNode('./imsss:rollupAction');
		if (rollupAction) {
			selects[1].value = rollupAction.getAttribute('action');
		}

	}
}

/**
 * 回存RollupConsiderations到Sequencing Setup Form中
 */
function restoreRollupConsiderations(node, objForm) {
	objForm.rollupConsiderations.click();
	if (node.getAttribute('measureSatisfactionIfActive') && node.getAttribute('measureSatisfactionIfActive') == 'false') objForm.measureSatisfactionIfActive.checked = false;
	if (node.getAttribute('requiredForSatisfied')) {
		objForm.requiredForSatisfied.click();
		objForm.requiredForSatisfiedvalue.value = node.getAttribute('requiredForSatisfied');
	}
	if (node.getAttribute('requiredForNotSatisfied')) {
		objForm.requiredForNotSatisfied.click();
		objForm.requiredForNotSatisfiedvalue.value = node.getAttribute('requiredForNotSatisfied');
	}
	if (node.getAttribute('requiredForCompleted')) {
		objForm.requiredForCompleted.click();
		objForm.requiredForCompletedvalue.value = node.getAttribute('requiredForCompleted');
	}
	if (node.getAttribute('requiredForIncomplete')) {
		objForm.requiredForIncomplete.click();
		objForm.requiredForIncompletevalue.value = node.getAttribute('requiredForIncomplete');
	}
}

/**
 * 回存Objectives到Sequencing Setup Form中
 */
function restoreObjectives(node, objForm) {
	objForm.Objectives.click();

	priNode = node.selectSingleNode('./imsss:primaryObjective');
	priMapInfo = priNode.selectNodes('./mapInfo');
	for (i = 0; i < priMapInfo.length - 1; i++)
		objForm.Objectives.parentNode.getElementsByTagName('a')[0].click();

    objNode = node.selectNodes('./imsss:objective');
    aNum = priMapInfo.length * 2;
	for (i = 0; i < objNode.length; i++) {
		if (i != objNode.length - 1) objForm.Objectives.parentNode.getElementsByTagName('a')[aNum].click();
		objMapInfo = objNode[i].selectNodes('./mapInfo');
		for (j = 0; j < objMapInfo.length - 1; j++) {
			objForm.Objectives.parentNode.getElementsByTagName('a')[aNum+2].click();
		}
		aNum = objMapInfo.length * 2 + 2 + aNum;
	}

	nodes = objForm.Objectives.parentNode.lastChild.childNodes;
	objNode = node.childNodes;
	for(i = 0; i < nodes.length; i++){
		if (nodes[i].tagName != 'LI') continue;
		inputs = nodes[i].getElementsByTagName('input');
		if (i) inputs[0].click();
		if (objNode[i] == null) continue;
		if (objNode[i].getAttribute('objectiveID')) inputs[1].value = objNode[i].getAttribute('objectiveID');
		if (objNode[i].getAttribute('satisfiedByMeasure') && objNode[i].getAttribute('satisfiedByMeasure') == 'true') inputs[2].checked = true;

		childs = nodes[i].lastChild.childNodes;
		mapInfoNode = objNode[i].selectNodes('./mapInfo');
		mapIdx = 0;
		minNormalizedMeasureNode = objNode[i].selectSingleNode('./minNormalizedMeasure');
		for(j = 0; j < childs.length; j++){
			if (childs[j].tagName != 'LI') continue;
			inputs = childs[j].getElementsByTagName('input');
			if (inputs[0].name == 'minNormalizedMeasure') {
				if (minNormalizedMeasureNode) {
					 inputs[0].click();
					 inputs[1].value = minNormalizedMeasureNode.firstChild.nodeValue;
				}
			}
			else if (inputs[0].name == 'mapInfo' && mapInfoNode.length > mapIdx) {
				inputs[0].click();
				if (mapInfoNode[mapIdx].getAttribute('targetObjectiveID')) inputs[1].value = mapInfoNode[mapIdx].getAttribute('targetObjectiveID');
				if (mapInfoNode[mapIdx].getAttribute('readSatisfiedStatus') == null || mapInfoNode[mapIdx].getAttribute('readSatisfiedStatus') == 'true')  inputs[2].checked = true;
				if (mapInfoNode[mapIdx].getAttribute('readNormalizedMeasure') == null || mapInfoNode[mapIdx].getAttribute('readNormalizedMeasure') == 'true')  inputs[3].checked = true;
				if (mapInfoNode[mapIdx].getAttribute('writeSatisfiedStatus') && mapInfoNode[mapIdx].getAttribute('writeSatisfiedStatus') == 'true')  inputs[4].checked = true;
				if (mapInfoNode[mapIdx].getAttribute('writeNormalizedMeasure') && mapInfoNode[mapIdx].getAttribute('writeNormalizedMeasure') == 'true')  inputs[5].checked = true;
				mapIdx++;
			}
		}
	}
}

/**
 * 回存randomizationControls到Sequencing Setup Form中
 */
function restoreRandomizationControls(node, objForm) {
	objForm.randomizationControls.click();
	if (node.getAttribute('randomizationTiming')) {
    	objForm.randomizationTiming.click();
    	objForm.RandomizationTimingValue.value = node.getAttribute('randomizationTiming');
    }
    if (node.getAttribute('reorderChildren') && node.getAttribute('reorderChildren') == 'true') objForm.reorderChildren.checked = true;
    if (node.getAttribute('selectCount') || node.getAttribute('selectionTiming')) {
    	objForm.selectCount.click();
    	objForm.selectCountValue.value = node.getAttribute('selectCount');
    	objForm.selectionTimingValue.value = node.getAttribute('selectionTiming');
    }
}

/**
 * 回存deliveryControls到Sequencing Setup Form中
 */
function restoreDeliveryControls(node, objForm) {
	objForm.deliveryControls.click();
    if (node.getAttribute('tracked') && node.getAttribute('tracked') == 'false') objForm.tracked.checked = false;
    if (node.getAttribute('completionSetByContent') && node.getAttribute('completionSetByContent') == 'true') objForm.completionSetByContent.checked = true;
    if (node.getAttribute('objectiveSetByContent') && node.getAttribute('objectiveSetByContent') == 'true') objForm.objectiveSetByContent.checked = true;
}

/**
 * 設定 Sequencing
 */
function sequencingProperty(node_id, isGlobal){
    /*Add By Edi Start*/
    var objForm = document.getElementById('ssSetupForm');
   initSsSetupForm(objForm);

    if (isGlobal) {
    	if (node_id != '') {
    		var sssNode = sequencingCollection.selectSingleNode('//imsss:sequencing[@ID="'+node_id+'"]');
    		document.getElementById('sequencing_id').value = node_id;
    	}
    }
    else {
    	// 加入Global Sequencing IDRef
    	var sel = document.getElementById('sequencing_idref');
    	sel.options.length = 0;
    	if (sequencingCollection) {
    		var collection = sequencingCollection.childNodes;
    		if (collection) {
    			for (var i = 0; i < collection.length; i++)
    				sel.options[i+1] = new Option(collection[i].getAttribute('ID'),collection[i].getAttribute('ID'));
    		}
    	}
    	var item = xmlDoc.selectSingleNode('//item[@identifier="' + node_id + '"] | //organization[@identifier="'+node_id+'"]');
    	var sssNode = item ? item.selectSingleNode('./imsss:sequencing') : null;
    }

    if (sssNode && sssNode != null) {
    	var IDRef = sssNode.getAttribute('IDRef');
    	// &#21442;考到Sequencing公用區的Sequencing
    	if (IDRef && IDRef != null) {
    		document.getElementById('ss_id').checked = true;
    		document.getElementById('ss_id').onclick();
    		document.getElementById('sequencing_idref').value = IDRef;
    	}

    	var nodes = sssNode.childNodes;
    	if (nodes.length) {
    	    for(var i = 0; i < nodes.length; i++){
    	    	switch(nodes[i].tagName){
    	    	    case 'imsss:controlMode':
    	    	    	restoreControlMode(nodes[i], objForm);
    	    	    	break;
    	    	    case 'adlseq:constrainedChoiceConsiderations' :
    	    	    	restoreConstrainedChoice(nodes[i], objForm);
    	    	    	break;
    	    	    case 'imsss:sequencingRules':
    	    	    	restoreSequencingRules(nodes[i], objForm);
    	    	    	break;
    	    	    case 'imsss:limitConditions':
    	    	    	restoreLimitConditions(nodes[i], objForm);
    	    	    	break;
    	    	    case 'imsss:auxiliaryResources':
    	    	    	restoreAuxiliaryResources(nodes[i], objForm);
    	    	    	break;
    	    	    case 'imsss:rollupRules':
    	    	    	restoreRollupRules(nodes[i], objForm);
    	    	    	break;
    	    	    case 'adlseq:rollupConsiderations':
    	    	    	restoreRollupConsiderations(nodes[i], objForm);
    	    	    	break;
    	    	    case 'imsss:Objectives':
    	    	    	restoreObjectives(nodes[i], objForm);
    	    	    	break;
    	    	    case 'imsss:randomizationControls':
						restoreRandomizationControls(nodes[i], objForm);
    	    	    	break;
    	    	    case 'imsss:deliveryControls':
						restoreDeliveryControls(nodes[i], objForm);
    	    	    	break;
    	    	}
    	    }
    	}
    }
	/*Add By Edi End*/
	if (isGlobal) {
		document.getElementById('localSeq').style.display='none';
		document.getElementById('globSeq').style.display='';
	}
	else {
		document.getElementById('localSeq').style.display='';
		document.getElementById('globSeq').style.display='none';
	}

	document.getElementById('ssSetupForm').item_id.value = node_id;
	document.getElementById('ssSetupForm').isGlobal.value = isGlobal;

	layerAction('ssSetupPanel', true);
}

/**
 * 初始化Sequencing Setup Form
 */
function initSsSetupForm(objForm) {
	objForm.reset();

	document.getElementById('ssPanel').firstChild.style.display = '';
	document.getElementById('sequencing_idref').disabled = true;

	// controlMode
	objForm.controlMode.parentNode.lastChild.style.display = 'none';

	// constrainedChoiceConsiderations
	objForm.constrainedChoiceConsiderations.parentNode.lastChild.style.display = 'none';


	// sequencingRules
	var childsLength = objForm.sequencingRules.parentNode.lastChild.childNodes.length;
	for (i = 0; i < childsLength - 1; i++)
		rmSibling(objForm.sequencingRules.parentNode.getElementsByTagName('a')[1]);

	var child = objForm.sequencingRules.parentNode.lastChild.childNodes[0];
	objForm.sequencingRules.parentNode.lastChild.style.display = 'none';
	var inputs  = child.getElementsByTagName('input');
	var selects = child.getElementsByTagName('select');
	selects[0].onchange();
	selects[0].disabled = true;
	inputs[0].checked = false;
	inputs[1].disabled = true;
	inputs[2].disabled = true;

	var step = 4;
	for (var i = 3; i < 40; i+=step) {
		if (i > 26) step = 2;
		else if (i > 14) step = 6;
		inputs[i].checked = false;
		inputs[i+1].checked = false;
		inputs[i+1].disabled = true;
		if (i < 27) {
			inputs[i+2].checked = false;
			inputs[i+3].value = '';
			if (i > 14) {
				inputs[i+4].checked = false;
				inputs[i+5].value = '0.000';
			}
		}
	}

	uls = child.getElementsByTagName('UL');
	for (var i = 2; i < uls.length; i++)
		 uls[i].style.display = 'none';
	uls[0].style.display = 'none';;

	// limitConditions
    child = objForm.limitConditions.parentNode.lastChild;
	child.style.display = 'none';
	inputs = child.getElementsByTagName('input');
	selects = child.getElementsByTagName('select');
	for (var i = 1; i <=9; i+=2)
		inputs[i].disabled = true;
	for (var i = 0; i < selects.length; i++)
		selects[i].disabled = true;

	// auxiliaryResources
	childsLength = objForm.auxiliaryResources.parentNode.lastChild.childNodes.length;
	for (var i = 0; i < childsLength - 1; i++)
		rmSibling(objForm.auxiliaryResources.parentNode.getElementsByTagName('a')[1]);

	var uls = objForm.auxiliaryResources.parentNode.getElementsByTagName('UL');
	for (var i = 0; i < uls.length; i++)
		uls[i].style.display = 'none';
	uls[0].getElementsByTagName('input')[0].checked = false;
	uls[0].getElementsByTagName('input')[1].disabled = true;

	// rollupRules
	objForm.rollupRules.parentNode.lastChild.style.display = 'none';
	objForm.ObjectiveMeasureWeightValue.disabled = true;
	childLength = objForm.rollupRules.parentNode.lastChild.childNodes.length;
	for (i = 4; i < childLength; i++)
		rmSibling(objForm.rollupRules.parentNode.getElementsByTagName('a')[1]);

	child = objForm.rollupRules.parentNode.lastChild.childNodes;
	child[3].lastChild.style.display = 'none';
	inputs = child[3].getElementsByTagName('input');
	selects = child[3].getElementsByTagName('select');
	selects[0].disabled = true;
	selects[0].value = 'all';
	selects[0].onchange();
	selects[1].value = 'satisfied';

	inputs[0].checked = false;
	inputs[4].checked = true; // 任一條件(預設值)
	for (var i = 5; i <= 21; i+=2) {
		inputs[i].checked = false;
		inputs[i+1].checked = false;
	}

	// rollupConsiderations
	objForm.rollupConsiderations.parentNode.lastChild.style.display = 'none';
	selects = objForm.rollupConsiderations.parentNode.lastChild.getElementsByTagName('select');
	for (var i = 0; i < selects.length; i++)
		selects[i].disabled = true;


	// Objectives
	// 記錄 個數
	childLength = objForm.Objectives.parentNode.lastChild.childNodes.length;
 	for (var i = 2; i < childLength; i++)
 		rmSibling(objForm.Objectives.parentNode.lastChild.childNodes[1].getElementsByTagName('a')[1]);

	// 主記錄中mapInfo個數
	childLength = objForm.Objectives.parentNode.lastChild.childNodes[0].getElementsByTagName('LI').length;
	for (var i = 2; i < childLength; i++)
		rmSibling(objForm.Objectives.parentNode.lastChild.childNodes[0].getElementsByTagName('a')[1]);

	// 記錄中mapInfo個數
	childLength = objForm.Objectives.parentNode.lastChild.childNodes[1].getElementsByTagName('LI').length;
	for (var i = 2; i < childLength; i++)
		rmSibling(objForm.Objectives.parentNode.lastChild.childNodes[1].getElementsByTagName('a')[3]);

	// 設定控制項的enable, disable, default value, checked, unchecked, invisible
	child = objForm.Objectives.parentNode.lastChild.childNodes;
	for (var i = 0; i <= 1; i++) {
		inputs = child[i].getElementsByTagName('input');
		selects = child[i].getElementsByTagName('select');
		if (i) {
			inputs[0].checked = false;
			child[1].lastChild.style.display = 'none';
			inputs[1].disabled = true;
			inputs[2].disabled = true;
		}
		inputs[1].value = '';
		inputs[2].checked = false;
		inputs[3].checked = false;
		inputs[4].disabled = true;
		inputs[4].value = '1.000';
		inputs[5].checked = false;
		inputs[6].value = '';
		for (var j = 6; j <= 10; j++)
			inputs[j].disabled = true;
		inputs[7].checked = true;
		inputs[8].checked = true;
		inputs[9].checked = false;
		inputs[10].checked = false;
	}

	objForm.Objectives.parentNode.lastChild.style.display = 'none';

	// randomizationControls
	objForm.randomizationControls.parentNode.lastChild.style.display = 'none';
	objForm.RandomizationTimingValue.disabled = true;
	objForm.selectCountValue.disabled = true;
	objForm.selectionTimingValue.disabled = true;

	// deliveryControls
	objForm.deliveryControls.parentNode.lastChild.style.display = 'none';
}

/**
 * 設定節點內容
 */
function NodeProperty(node_id, idx){
    cancelLcmsImport();
	document.getElementById('globSeqForm').style.display = 'none';
	document.getElementById('ssSetupPanel').style.display = 'none';

	var obj = document.getElementById('nodeSetupPanel');
	// 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(550)] 再左移 10 個 pixel
	obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 550;
	// 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
	obj.style.top   = document.body.scrollTop  + 10;

	var node_kinds = new Array('', '', 'node_homework', 'node_exam', 'node_questionnaire', 'node_subject', 'node_forum', 'node_discuss');
	var currNode = xmlDoc.selectSingleNode('//item[@identifier="' + node_id + '"]');
	if (currNode.getElementsByTagName('title')[0] && currNode.getElementsByTagName('title')[0].firstChild && trim(currNode.getElementsByTagName('title')[0].firstChild.nodeValue) != '')
		var title = currNode.getElementsByTagName('title')[0].firstChild.nodeValue.split('\t');
	else{
		var title = new Array('--=[ ' + currNode.getAttribute('identifier') + ' ]=--');
		title[4]= title[3]= title[2]= title[1]= title[0];
	}
	var resRef    = currNode.getAttribute('identifierref');
	var isvisible = currNode.getAttribute('isvisible');
	var isenable  = currNode.getAttribute('disabled');
	var isNewWin  = currNode.getAttribute('target') == '_blank';
	var resObj = xmlDoc.selectSingleNode('//resource[@identifier="' + resRef + '"]');
	if (resObj) {
		var base = resObj.getAttribute('xml:base') == null ? '' : resObj.getAttribute('xml:base');
		var url = base + resObj.getAttribute('href');
	}
	else var url = '';
	// var url = (resObj == null) ? '' : resObj.getAttribute('href');

	obj.style.display = '';

	obj = document.getElementById('nodeSetupForm');
	obj.reset();

	if (url.search(/\bfetchWMinstance\(([0-9]+),'?([0-9A-Za-z]+)'?\)/) != -1){
		changeNodeType(RegExp.$1);
		obj.node_type[parseInt(RegExp.$1)-1].checked = true;
		if (typeof(node_kinds[RegExp.$1]) != 'undefined')
			eval('obj.' + node_kinds[RegExp.$1] + '.value = "' + RegExp.$2 + '";');
	}
	else{
		changeNodeType('1');
		obj.node_type[0].checked = true;
		obj.url.value = url;
		var nodes = obj.getElementsByTagName('input');
		for(var i=0; i<nodes.length; i++){
			if (nodes[i].type != 'text') continue;
			switch(nodes[i].name){
				case 'title[Big5]':        nodes[i].value = title[0]; break;
				case 'title[GB2312]':      nodes[i].value = typeof(title[1])=='undefined'?title[0]:title[1]; break;
				case 'title[en]':          nodes[i].value = typeof(title[2])=='undefined'?title[0]:title[2]; break;
				case 'title[EUC-JP]':      nodes[i].value = typeof(title[3])=='undefined'?title[0]:title[3]; break;
				case 'title[user_define]': nodes[i].value = typeof(title[4])=='undefined'?title[0]:title[4]; break;
			}
		}
	}

	obj.node_hidden.checked = (isvisible === 'false');
	obj.node_enable.checked = !(isenable === 'true');
	obj.node_enable.disabled = obj.node_hidden.checked;
	obj.newWin.checked = isNewWin;

	obj.func.value = idx;
	obj.item_id.value = node_id;
	obj.resource_id.value = resRef;
	dragObj = obj;
	getHideObjList();
	hideShowCovered();
}

/**
 * 切換節點種類
 */
function changeNodeType(n){
	var obj = document.getElementById('nodeSetupTable');
	switch(n){
		case '1':
			obj.rows[2].style.display = '';	// Multi_lang input
			obj.rows[3].style.display = '';	// URL
			for(var i=4; i<10; i++) obj.rows[i].style.display = 'none';
			break;
		default:
			var idx = parseInt(n) + 2;
			for(var i=2; i<10;  i++) obj.rows[i].style.display = (idx==i?'':'none');
			break;
	}
}

/**
 * 取得 Radio 所選的值 ( Radio 就是這點比 select 爛)
 */
function getRadioValue(objRadio) {
	if(objRadio.checked)
		 return objRadio.value;

	for(var i=0;i<objRadio.length;i++)
		 if(objRadio[i].checked)
			  return objRadio[i].value;
	return false;
}

/**
 * 節點設定完畢。mode = false 是「取消」，mode=true 是「確定」
 */
function nodeSetupDone(mode){
	var obj = document.getElementById('nodeSetupForm');
	var node_kinds = new Array('', '', 'node_homework', 'node_exam', 'node_questionnaire', 'node_subject', 'node_forum', 'node_discuss');
	var node_type = getRadioValue(obj.node_type);
	var nodes = obj.getElementsByTagName('input');
	var newNode, newText;

	if (mode){
		if (node_type != '1'){
			if (eval('obj.' + node_kinds[node_type] + '.value == "0";')){
				alert(MSG_REQUEST);
				return;
			}
		}

		var title = new Array(5);
		for(var i=0; i<nodes.length; i++){
			if (nodes[i].type != 'text') continue;
			switch(nodes[i].name){
				case 'title[Big5]':        title[0] = nodes[i].value.replace(/^\s+|\s+$/g, ''); break;
				case 'title[GB2312]':      title[1] = nodes[i].value.replace(/^\s+|\s+$/g, ''); break;
				case 'title[en]':          title[2] = nodes[i].value.replace(/^\s+|\s+$/g, ''); break;
				case 'title[EUC-JP]':      title[3] = nodes[i].value.replace(/^\s+|\s+$/g, ''); break;
				case 'title[user_define]': title[4] = nodes[i].value.replace(/^\s+|\s+$/g, ''); break;
			}
		}

		var node_id = obj.item_id.value;
		var resRef = obj.resource_id.value;
		var currNode = xmlDoc.selectSingleNode('//item[@identifier="' + node_id + '"]');
        // #47315 [教師/課程管理/學習路徑管理] 點選一個節點，把「隱藏」打勾，按下「確定」，結果所有節點都被設為隱藏。-->改為僅隱藏該節點
		if (obj.node_hidden.checked)	currNode.setAttribute('isvisible', 'false');	else currNode.removeAttribute('isvisible');
		if (!obj.node_enable.checked)	currNode.setAttribute('disabled', 'true' );		else currNode.removeAttribute('disabled');
		if (obj.newWin.checked && node_type == 1)		currNode.setAttribute('target', '_blank');		else currNode.removeAttribute('target');
		var resObj = xmlDoc.selectSingleNode('//resource[@identifier="' + resRef + '"]');

		if (node_type == '1'){
			if (title.join('') == '' ||
				(lang == 'big5'        && title[0] == '') ||
				(lang == 'gb2312'      && title[1] == '') ||
				(lang == 'en'          && title[2] == '') ||
				(lang == 'euc_jp'      && title[3] == '') ||
				(lang == 'user_define' && title[4] == '')
			   )
			{
				alert(MSG_REQ_T);
				return;
			}


			if (titleNode = currNode.selectSingleNode('./title'))
				currNode.removeChild(titleNode);

			newNode = xmlDoc.createElement('title');
			newNode = currNode.insertBefore(newNode, currNode.firstChild);
			newText = xmlDoc.createTextNode(title.join('\t'));
			newNode.appendChild(newText);

			var href = obj.url.value;
		} // End of node_type = 1
		else{
			var instance        = node_kinds[node_type];
			var instance_id     = eval('obj.' + instance + '.value');
			var instance_title = selectInstance[instance.substr(instance.lastIndexOf('_')+1)][instance_id];
			if (currNode.getElementsByTagName('title').length)
			{
			    if (currNode.getElementsByTagName('title')[0].firstChild != null)
				currNode.getElementsByTagName('title')[0].firstChild.nodeValue = instance_title;
				else
				    currNode.getElementsByTagName('title')[0].appendChild(xmlDoc.createTextNode(instance_title));
			}
			else{
				newNode = xmlDoc.createElement('title');
				newNode = currNode.insertBefore(newNode, currNode.firstChild);
				newText = xmlDoc.createTextNode(instance_title);
				newNode.appendChild(newText);
			}

			if (node_type == 7)
				var href = 'javascript:fetchWMinstance(' + node_type + ',\'' + instance_id + '\')';
			else
				var href = 'javascript:fetchWMinstance(' + node_type + ',' + instance_id + ')';
		}

		if (href) { // 有設定url
			if (!currNode.getAttribute('identifierref')) { // Node本身沒有設identifierref
				var new_id = getCurrentId();
				var id_1 = new_id.substring(0, new_id.lastIndexOf('_'));
				var id_2 = parseInt(new_id.substring(new_id.lastIndexOf('_')+1));
				resRef = 'R' + id_1 + '_' + id_2;
				currNode.setAttribute('identifierref', resRef);
			}
			if (resObj == null) {	// 沒有對應的Resource
				var nNode = xmlDoc.createElement('resource');
				resObj = resources.appendChild(nNode);
				resObj.setAttribute('identifier', resRef);
				resObj.setAttribute('adlcp:scormtype', 'asset');
				resObj.setAttribute('type', 'webcontent');
			}

			if (href.search(/^\w+:\/\/./i) == -1 && (idx = href.lastIndexOf('/')) != -1) {
				resObj.setAttribute('xml:base', href.substring(0, idx + 1));
				href = href.substring(idx+1);
			}
			else resObj.removeAttribute('xml:base');
			resObj.setAttribute('href', href);

			if (node_type == '1'){ // 第一個型態節點有  /resource/file
				var fileNode = xmlDoc.selectSingleNode('//resource[@identifier="' + resRef + '"]/file');
				if (fileNode == null){
					fileNode = xmlDoc.createElement('file');
					fileNode = resObj.appendChild(fileNode);
				}
				fileNode.setAttribute('href', href);
			}
			else { // 其他節點無/resource/file
				var fileNode = xmlDoc.selectSingleNode('//resource[@identifier="' + resRef + '"]/file');
				if (fileNode)
					resObj.removeChild(fileNode);
			}
		}
		else { // 沒有設定url
			if (resRef = currNode.getAttribute('identifierref')) {
				currNode.removeAttribute('identifierref');
				if (xmlDoc.selectNodes('//item[@identifierref="'+resRef+'"]').length == 0) { // 沒有其他節點引用到同一個Resource則移除
					resource = xmlDoc.selectSingleNode('//resource[@identifier="' + resRef + '"]');
					if (resource != null) resources.removeChild(resource);
				}
			}
		}

		displayLayout();
		notSave = true;

	}
	else if (!mode && obj.func.value == '1'){	// 當點選取消時，刪掉xmldoc中已經新增上去的node
		var node_id = obj.item_id.value;
		var r_node = xmlDoc.selectSingleNode('//item[@identifier="'+node_id+'"]');
		if (typeof(r_node) == 'object')
			organization.removeChild(r_node);
	}

	// 清除對話框裡，所有 input 的值
	obj.reset();

	/* 將 IE 的 applet iframe select embed 物件顯示 begin */
	if(!mode){
		dragObj = obj;
		dragObj.hidden = true;
		hideShowCovered();
	}
	dragObj.hidden = false;
	/* 將 IE 的 applet iframe select embed 物件顯示 end */

	/*
	for(var i=0; i<nodes.length; i++){
		switch(nodes[i].type){
			case 'text':
			case 'hidden':
				nodes[i].value = ''; break;
			case 'checkbox':
				nodes[i].checked = (nodes[i].name == 'node_enable') ? true : false; break;
			case 'radio':
				nodes[i].checked = false; break;
			case 'select':
				nodes[i].selectedIndex = 0; break;
		}
	}
	*/
	/* 把對話框隱藏 */
	document.getElementById('nodeSetupPanel').style.display = 'none';
}

/**
 * 取得 FORM 的 item_id 值
 */
function getItemId(obj){

	var node = obj;
	while(node !== null){
		if (node.tagName == 'FORM')
			return node.item_id.value;
		else if(node.tagName == 'BODY' || node.tagName == 'HTML')
			return null;
		else
			node = node.parentNode;
	}
	return null;
}

/**
 * 取得瀏覽的檔名。※※※※ 函數名稱不可改 ※※※※
 */
function getReturnValue(){
	if (typeof(window.returnValue) == 'undefined') return;
	document.getElementById('nodeSetupForm').url.value = window.returnValue.substr(1);
}

function browseFile(){
	window.open('listfiles.php', '', 'width=380,height=400,status=0,toolbar=0,menubar=0,scrollbars=1,resizable=1');
}

window.onbeforeunload=function()
{
	if (notSave) return MSG_EXIT;
};

/**
 * 設定Global Sequencing
 */
function globSeqProperty(){
	setGlobalSequencing();
	var obj = document.getElementById('globSeqForm');
	// 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(500)] 再左移 10 個 pixel
	obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 510;
	// 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
	obj.style.top   = document.body.scrollTop  + 10;
	obj.style.display = '';

	document.getElementById('nodeSetupPanel').style.display = 'none';
	document.getElementById('ssSetupPanel').style.display = 'none';
}

/**
 * 取得Global Sequencing中的所有Sequencing ID列表
 */
function getCollection(node) {
	IH = ''; checkered = 0;
	if (node) {
		collection = node.childNodes;
		if (collection) {
			itemCounter = 1;
			for (i = 0; i < collection.length; i++) {
				checkered ^= 1;
				idValue = collection[i].getAttribute('ID');
				IH += '<li class="cssTr' + (checkered ? 'Odd' : 'Evn') + '">' +
					  '<input type="checkbox" name="' + idValue + '">' +
					  '<span class="item">' +
					  (itemCounter++) +
					  '.</span>&nbsp;&nbsp;' +
					  '<a href="javascript:;" onclick="sequencingProperty(\'' +
					  idValue +
					  '\', true); return false;" class="link_fnt01">' +
					  idValue + '</a>';
			}
		}
	}
	return IH;
}

/**
 * 設定Global Sequencing列表的HTML
 */
function setGlobalSequencing() {
	var nodes = organization.childNodes;
	itemCounter = 1;
	var IH = '<ul><li>' + getTitle(organization) + '</li><ul>' + getCollection(sequencingCollection) + '</ul></ul>';
	document.getElementById('globSeqPanel').innerHTML = IH;
}

/**
 * Global Sequencing列表中的按鈕
 */
function GlobSeqSetupDone(type) {
	switch (type) {
		case 'new' : sequencingProperty('', true); break;
		case 'del' : sequencingDel(); break;
		case 'complete' : document.getElementById('globSeqForm').style.display = 'none'; break;
	}
}

/* 刪除公用區的Sequencing */
function sequencingDel() {
	inputs = document.getElementById('globSeqTable').getElementsByTagName('UL')[1].getElementsByTagName('input');
	for (i = 0; i < inputs.length; i++) {
		if(inputs[i].checked) {
			node = sequencingCollection.selectSingleNode('./imsss:sequencing[@ID="'+inputs[i].name+'"]');
			sequencingCollection.removeChild(node);
		}
	}
	setGlobalSequencing();
}

/* 切換Sequencing設定或者是&#21442;考公用區Sequencing設定 */
function switchIDRef(value) {
	document.getElementById('sequencing_idref').disabled = value ? false : true;
}

/**
 * 取消匯入 LCMS 的功能
 */
function cancelLcmsImport() {
    // document.getElementById('lcmsSetupPanel').style.display = 'none';
}

/**
 * 匯入 LCMS 的功能
 */
function confirmLcmsImport() {
    if (!lcmsEnable) {
        return;
    }

    var fm, replace, pnode, nodes, i, c, newNode;

    // cancelLcmsImport();
    // 檢查匯入模式
    fm = document.getElementById('lcmsSetupForm');
    replace = fm.condition.checked;
    if (replace) {
        // 清空所有節點
        nodes = xmlDoc.selectNodes('/manifest/organizations/organization/item');
        for (i = nodes.length - 1; i >= 0; i -= 1) {
            organization.removeChild(nodes[i]);
        }

        pnode = resources.parentNode;
        pnode.removeChild(resources);
        pnode.appendChild(xmlDoc.createElement('resources'));
        resources = xmlDoc.selectSingleNode('/manifest/resources');
    }

    if ((lcmsData.unit === undefined) || (lcmsData.asset === undefined)) {
        return;
    }
    /* 如果有選擇教材，則插在選取下方 */
    var cur = getSelElement(_GSE_MODE_FIRST);
    var nodes = organization.getElementsByTagName('item');
    var curItem = null;
    insertPosition = [0, 0];
    if (cur !== null && cur !== "") {
        curItem = nodes.item(parseInt(cur,10));
        var nextItem = getNextSibling(curItem);
        insertPosition = [curItem.getAttribute('identifier'), (nextItem !== null)?nextItem.getAttribute('identifier'):0];
    }
    if (insertFirst) {
        var firstItem = getFirstItem(organization);
        insertPosition = [-1, firstItem.getAttribute('identifier')];
    }
    // 短期只能上傳素材，註解掉
    for (i = 0, c = lcmsData.unit.length; i < c; i += 1) {
        if (curItem !== null && curItem.nextSibling === null){
            var newNode = curItem.parentNode.appendChild(xmlDoc.createElement('item'));
        } else if (cur === false || cur === '') {
            var newNode = organization.appendChild(xmlDoc.createElement('item'));
        } else {
            var newNode = curItem.parentNode.insertBefore(xmlDoc.createElement('item'),curItem.nextSibling);
        }
        newNode = newNode.appendChild(xmlDoc.createElement('title'));
        newNode.appendChild(xmlDoc.createTextNode(lcmsData.unit[i].caption));
        newNodeInit(newNode.parentNode, {
            title: lcmsData.unit[i].caption,
            kind: 'lcms',
            href: lcmsData.unit[i].extra.url
        });
    }
    for (i = 0, c = lcmsData.asset.length; i < c; i += 1) {
        if (insertFirst) {
            var newNode = organization.insertBefore(xmlDoc.createElement('item'), firstItem);
        } else if (curItem !== null && curItem.nextSibling === null){
            var newNode = curItem.parentNode.appendChild(xmlDoc.createElement('item'));
        } else if (cur === false || cur === '') {
            var newNode = organization.appendChild(xmlDoc.createElement('item'));
            insertPosition = [-1, 0];
        } else {
            var newNode = curItem.parentNode.insertBefore(xmlDoc.createElement('item'),curItem.nextSibling);
        }
        newNode = newNode.appendChild(xmlDoc.createElement('title'));
        newNode.appendChild(xmlDoc.createTextNode(lcmsData.asset[i].caption));
        newNodeInit(newNode.parentNode, {
            title: lcmsData.asset[i].caption,
            kind: 'lcms',
            href: lcmsData.asset[i].extra.url,
            duration: lcmsData.asset[i].extra.video_duration,
            uid: lcmsData.asset[i].extra.unit_id
        });
    }
    notSave = true;
    insertFirst = false;
    lcmsData = {};

    displayLayout();
    /* 配合自動儲存，在插入素材後同時作儲存 */
    showSaveTip(true);
    executing(5);
}

function addLcmsContent(data) {
    if (!lcmsEnable) {
        return;
    }

    var
        elem = document.getElementById('msgSelectContent'),
        btn = document.getElementById('btnSelectLcms');

    lcmsData = JSON.parse(data);
    /*
    var elemText = MSG_SELECTED_COURSE
        .replace('%unit%', lcmsData.unit.length)
        .replace('%asset%', lcmsData.asset.length);
    if (confirm("確定匯入'"+elemText+"'?")) {
        confirmLcmsImport();
    } else {
        cancelLcmsImport();
    }
    */
    confirmLcmsImport();
    btn.value = MSG_BTN_RESELECT;
}

function selectLcmsContent() {
    if (!lcmsEnable) {
        return;
    }

    window.open('lcms.php?action=import&type=asset', 'lcmsDialog', 'height=650,width=1000,resizable=1,scrollbars=1');
}

function showLCMS(id, type) {
    var item = xmlDoc.selectSingleNode('//item[@identifier="' + id + '"]');
    if (item == null ){
		alert('incorrect item id');
        return false;
    }
    // 取得 Activity 之 href                    .
	var resource_id = item.getAttribute('identifierref');
	if (typeof(resource_id) == 'undefined') {
        return false;
    }
    if (type == 'preview') {
        document.getElementById('showFrame2').src = '/learn/path/lcms.php?screenshot=0&rid=' + resource_id;
    } else if (type == 'edit') {
        var resource = resources.selectSingleNode('./resource[@identifier="' + resource_id + '"]');
        window.open((resource.getAttribute('href')).replace('detail', 'edit') + '/1', 'lcmsDialog', 'top=0,left=0,width=1100,height=750,location=no,scrollbars=yes');
    }
}

function showSaveTip(bol) {
    if (bol) {
        $("#displayPanel").isLoading({
            'text':       MSG_SAVING,
            'class':      "icon-loader",
            'position':   "overlay"
        });
    } else {
        $("#displayPanel").isLoading( "hide");
    }
}