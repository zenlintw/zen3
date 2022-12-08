// ¸ü¤J awp ÀÉ
function loadwb(urlval){
	wbui.style.width = 670;
	wbui.style.height = 500;
	wbui.style.visibility='visible';		
	document.AnicamWB.LoadURL(urlval);
}
// --------------------------------------------------------------
function _getCookie() {
    return this._cookie;
}

function _setCookie(oCookie) {
	this._obj.WM_Cookie = oCookie;
    this._cookie = oCookie;
}

function _setCID(iCID) {
	this._obj.WM_CourseID = iCID;
    this._cid = iCID;
}

function _getCID() {
	return this._cid;    
}

function _setBID(iBID) {
	this._obj.WM_BoardID = iBID;
    this._bid = iBID;
}

function _getBID() {
	return this._bid;    
}

function _setWidth(width) {
    this._width = width;
}

function _getWidth() {
    return this._width;
}

function _setHeight(height) {
    this._height = height;
}

function _getHeight() {
    return this._height;
}

function _isLoaded(){
	return this._isLoaded;
}

function _setupGUI() {
	this._gui.style.width = this.getWidth();
	this._gui.style.height = this.getHeight();
	this._gui.style.visibility='visible';	    	
}

function _AddNew() {
	this._setupGUI();
/*
	if (!this.isLoaded()){
		//alert("http://" + document.location.host);
		this._obj.LoadURL("http://" + document.location.host); // EAccessViolation

		this._isLoaded = true;
	}
*/
    //var obj = this._obj;
	//obj.LoadURL();
}

function _openWB(url) {
	this._setupGUI();
	this._obj.LoadURL(url);    
}

function _getObject() {	
    return this._obj;
}

function _setURL(url) {
	this._obj.PostURI = url;
    this._url = url;
}

function _getURL(url) {
    return this._url;
}
// 
/*
var aWhiteboard = new CWhiteboard();
aWhiteboard.setURL("http://" + document.location.host + "/forum/wb_upload.php");

*/
function CWhiteboard() {
	this._obj = document.getElementById('AnicamWB');
	this._gui = document.getElementById('wbui');
	this._url = "";
	this._width = 670;
	this._height = 500;
	this._bid = -1;
	this._cid = -1;
	this._cookie = null;
	this._isLoaded = false;

// public method
	this.isLoaded = _isLoaded;
	this.addNew = _AddNew;
	this.openWB = _openWB;
	
	this.setCID = _setCID;
	this.getCID = _getCID;
	
	this.setBID = _setBID;
	this.getBID = _getBID;
	
	this.getCookie = _getCookie;
	this.setCookie = _setCookie;

	this.setURL = _setURL;
	this.getURL = _getURL;

	this.getObject = _getObject;

	this.getHeight = _getHeight;
	this.setHeight = _setHeight;

	this.getWidth = _getWidth;
	this.setWidth = _setWidth;

// private method:
	this._setupGUI = _setupGUI;
}