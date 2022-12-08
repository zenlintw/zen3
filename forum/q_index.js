function getNodeValue(node, tagname) {
	var nodes = null;
	if ((typeof(node) != "object") || (node == null)) return '-1';
	nodes = node.getElementsByTagName(tagname);
	if ((nodes == null) || (nodes.length <= 0)) return '-2';
	return (nodes[0].hasChildNodes()) ? nodes[0].firstChild.data : '-3';
}

function nl2br(val) {
	return val.replace(/\n/ig, "<br>");
}
function _goto(url) {
	var frm = document.getElementById('mainform');
	if(frm) {
			frm.action = url;
			frm.submit();
	} else
		location.replace(url);
}

// 變換頁數 ( 含計算目前所在頁數 )
function go_rowspage(n){
	var frm = document.getElementById('mainform');
	if(n=='-1') {
		new_page = cur_page;
	} else {
		cur_pos = cur_page*rows_page;	// 目前位置
		new_page= Math.ceil(cur_pos/n);	// 換算頁數
	}
	frm.rows_page.value = n;
	//alert('new_page=' + new_page + ',PostPerPage=' + n);

	_goto('560,'+board_id+','+ new_page +','+sortby+'.php');
}

function go_page(n){
	switch(n){
		case -1:	// 第一頁
			_goto('560,'+board_id+',1,'+sortby+'.php');
			break;
		case -2:	// 前一頁
			_goto('560,'+board_id+','+(cur_page-1)+','+sortby+'.php');
			break;
		case -3:	// 後一頁
			_goto('560,'+board_id+','+(cur_page+1)+','+sortby+'.php');
			break;
		case -4:	// 最末頁
			_goto('560,'+board_id+','+total_page+','+sortby+'.php');
			break;
		case '0':	// 全部列
			_goto('561,'+board_id+','+cur_page+','+sortby+'.php');
			break;
		default:	// 指定某頁
			_goto('560,'+board_id+','+n+','+sortby+'.php');
			break;
	}
}

function search(){
	var frm = document.getElementById('mainform');
	var keyword = frm.keyword.value
	var search_type = frm.search_type.value;
	if(keyword != '') {
		frm.is_search.value = 'true';
		go_page(-1);
	}
	else {
		unsearch();
	}
}
function unsearch(){
	var frm = document.getElementById('mainform');
	frm.is_search.value = 'false';
	go_page(-4);
}

function batch_default_state(obj, o1, o2, v1, v2, o) {
   	obj.selectedIndex = 0;
   	o1.value = v1;
	o2.value = v2;
	o.focus();
}

var from_post=0;
var to_post=0;
var q_folder_type = '';
// 供 Modal Dialog Callback 用
function do_notebook( folder) {
	if(folder != null)
		location.replace('591,'+board_id+','+from_post+','+to_post+'.php?folder='+folder);
	from_post = 0;
	to_post = 0;
}

// 供 (move) Modal Dialog Callback 用
function do_q_folder( folder, folder_id ) {
	if(folder != null && folder != path) {
		// alert('folder:' + folder + '\nfolder_id' + folder_id );
		location.replace('592,'+board_id+','+from_post+','+to_post+'.php?folder_id='+folder_id);
		//location.replace('q_batch.php?board='+board_id+'&node='+node+'&site='+site_id+'&path='+path+'&folder='+folder);
	}
}

function do_delete() {
	location.replace('590,'+board_id+','+from_post+','+to_post+'.php');
}

function batch(obj){
	var o1 = obj.previousSibling.previousSibling.previousSibling.previousSibling;
	var o2 = obj.previousSibling.previousSibling;
	var v1 = o1.value;
	var v2 = o2.value;

	if (total_post == 0){
		switch (obj.value){
			case '1':	// 刪除
				total_post_msg = DEL_ERROR.replace(/%ACTION%/,MSG_del);
				break;
			case '2':	// 收入個人筆記本
				total_post_msg = DEL_ERROR.replace(/%ACTION%/,MSG_nbook);
				break;
			case '3':	// 搬移 (move)
				total_post_msg = DEL_ERROR.replace(/%ACTION%/,MSG_move);
				break;
		}

	   	alert(total_post_msg);
	   	return;
	}

	if (v1.search(/^[0-9]+$/) < 0 ||
	    v2.search(/^[0-9]+$/) < 0 ||
	    parseInt(v1,10) > parseInt(v2,10)||
		parseInt(v1,10) < 1
	   ){
	   	alert(ErrorPostNo + total_post);
	   	batch_default_state(obj,o1,o2,v1,v2,o1);
	   	return;
	}
	v1 = parseInt(v1,10);
	v2 = parseInt(v2,10);
	if(v2>total_post) {
		alert(ErrorPostRange + total_post );
	   	batch_default_state(obj,o1,o2, v1, v2, o2);
		return;
	}

	switch(obj.value){
		case '1':	// 刪除
			var sFeatures="dialogHeight: 150px;dialogWidth: 250px;resizable:yes; scroll:yes;status:no;";
			from_post = v1;
			to_post   = v2;
			showDialog("q_folders_confirm.php?v1="+v1+"&v2=" + v2 + "&function=do_delete",
				true,window, true, 0, 0, '250px', '150px', 'resizable=yes;scroll=yes;status=no');
			// showModalDialog("q_folders_confirm.php?v1="+v1+"&v2=" + v2 + "&function=do_delete", window, sFeatures)
			batch_default_state(obj,o1,o2, '', '',o1);
			break;
		case '2':	// 收入個人筆記本
			var sFeatures="dialogHeight: 350px;dialogWidth: 300px;resizable:yes; scroll:yes;status:no;";
			from_post = v1;
			to_post   = v2;
			showDialog("batch_notebook.php?board="+ board_id+"&v1="+v1+"&v2=" + v2,
				true,window, true, 0, 0, '300px', '350px', 'resizable=yes;scroll=yes;status=no');
			// showModalDialog("batch_notebook.php?board="+ board_id+"&v1="+v1+"&v2=" + v2, window, sFeatures)
			//window.open("batch_notebook.php?board="+ board_id+"&v1="+v1+"&v2=" + v2);
			batch_default_state(obj,o1,o2, '', '',o1);
			//location.replace('591,'+board_id+','+v1+','+v2+'.php');
			break;

		case '3':	// 搬移
			var sFeatures="dialogHeight: 350px;dialogWidth: 350px;resizable:yes; scroll:yes;status:no;help:no;";
			from_post = v1;
			to_post   = v2;
			showDialog("batch_q_folder.php",
				true,window, true, 0, 0, '350px', '350px', 'resizable=yes;scroll=no;status=no;');
			// showModalDialog("batch_q_folder.php", window, sFeatures);
			batch_default_state(obj,o1,o2, '', '',o1);
			//location.replace('592,'+board_id+','+v1+','+v2+'.php');
			break;

		default:
			alert('type error !');
			return;
	}
}

function go_normal(){
	location.replace('index.php');
}

function sortBy(type){
	var ta = new Array('node',
			   'pt',
			   'subject',
			   'poster',
			   'hit',
			   'rank',
			   'node_r',
			   'pt_r',
			   'subject_r',
			   'poster_r',
			   'hit_r',
			   'rank_r'
			  );
	location.replace('562,'+board_id+','+cur_page+','+ta[type]+'.php');
}


function post(){
	location.replace('q_write.php?bTicket=' + bTicket);
}

function read(n){
	location.replace('570,'+board_id+','+n+'.php');
}

var act_dir  = '';
function do_dir(act, dir) {
	var txt  = "<manifest><ticket>" + ticket + "</ticket>";
	txt += "<action>" + act + "</action>";
	txt += "<dir>" + dir + "</dir>";
	txt += "</manifest>";

	xmlVars.loadXML(txt);
	xmlHttp.open("POST", "q_dirs.php", false);
	xmlHttp.send(xmlVars);
	xmlDoc.loadXML(xmlHttp.responseText);
	ticket = getNodeValue(xmlDoc, "ticket");
	err_code = parseInt(getNodeValue(xmlDoc, "err"));
	message = getNodeValue(xmlDoc, "message");

	if(err_code != 0)
		alert(message);
	else if(act == 'isemptydir') {
		var obj = document.getElementById("confirm_str");
		obj.innerHTML = nl2br(message);
		//alert(obj.innerHTML);
		dir_to_del = dir;
		displayFolderConfirm(true);
	} else {
		location.replace("q_index.php");
	}
}

function trim(s){
	return s.replace(/(^\s+)|(\s+$)/g, '');
}

function mkdir(){
	var dir = prompt(MsgNewFolder,'');
	if (dir == null) return;
	if (!(dir = trim(dir))) {
		alert(MSG_empty_dir);
		return;
	}

	var reg = /^[^\x00-\x2C\x2E\x2F\x3A-\x40\x5B-\x5E\x60\x7B-\x80]+$/;
	if (!reg.test(dir)) {
		alert(MsgIllegalChars);
		return;
	}

	do_dir('mkdir', dir);
}

function rmdir(dir){
	dir_to_del = '';
	do_dir('isemptydir', dir);
}

// 供 (mkdir)  Dialog Callback 用
function do_rmdir() {
	if(dir_to_del != '')
		do_dir('rmdir', dir_to_del);
	// 隱藏 Dialog
	displayFolderConfirm(false);
}

// Rename 目錄名稱
function rename_dir(folder_id, new_name) {
	var txt  = "<manifest><ticket>" + ticket + "</ticket>";
	txt += "<action>rename</action>";
	txt += "<node>" + folder_id + "</node>";
	txt += "<dir>" + new_name + "</dir>";
	txt += "</manifest>";

	xmlVars.loadXML(txt);
	xmlHttp.open("POST", "q_dirs.php", false);
	xmlHttp.send(xmlVars);
	xmlDoc.loadXML(xmlHttp.responseText);
	ticket = getNodeValue(xmlDoc, "ticket");
	err_code = parseInt(getNodeValue(xmlDoc, "err"));
	message = getNodeValue(xmlDoc, "message");

	//alert(xmlHttp.responseText);
	//return;

	if(err_code != 0)
		alert(MsgRenameAs + ' "' + new_name + '" ' + MsgFailed + '!\n\n' + message);
	else {
		if(folder_cell != null) {
			innerHTML = innerTemp.replace(/%1/g, folder_id);
			innerHTML = innerHTML.replace(/%2/g, new_name);
			folder_cell.innerHTML = innerHTML;
			// alert(MsgRenameAs + ' "' + new_name + '" ' + MsgSuccess + '!');
			folder_cell = null;
		}
		alert(MsgRenameAs + ' "' + new_name + '" ' + MsgSuccess + '!');
		location.replace("q_index.php");
	}
}

function chdir(dir){
	location.replace('562,'+board_id+','+cur_page+','+sortby+','+dir+'.php');
}


/**
 * show or hidden Layer UI
 * @pram string ui_name : layer name
 * @pram string on_btn  : trigger button
 * @pram boolean val :
 *               true  : show
 *               false : hidden
 * @return none
 **/
function displayHiddenUI(ui_name, on_btn, val) {
	var obj = document.getElementById(ui_name);
	var btn = document.getElementById(on_btn);
	var sclTop = 0, oHeight = 0;

	if (obj == null) return false;
	if (val) {
		sclTop = parseInt(document.body.scrollTop);
		oHeight = (isMZ) ? parseInt(window.innerHeight) : document.body.offsetHeight;
		if ((parseInt(obj.style.top) < sclTop) ||
			(parseInt(obj.style.top) > (sclTop + oHeight))) {
			obj.style.top = sclTop + 50;
		}
		btn.disabled = true;
	} else {
		btn.disabled = false;
	}
	layerAction(ui_name, val);
}


/**
 * show or hidden Import UI
 * @pram boolean val :
 *               true  : show
 *               false : hidden
 * @return none
 **/
function displayImportUI(val) {
/*
	var obj = document.getElementById("import_ui");
	var btnImport = document.getElementById("btnImport");
	var sclTop = 0, oHeight = 0;

	if (obj == null) return false;
	if (val) {
		sclTop = parseInt(document.body.scrollTop);
		oHeight = (isMZ) ? parseInt(window.innerHeight) : document.body.offsetHeight;
		if ((parseInt(obj.style.top) < sclTop) ||
			(parseInt(obj.style.top) > (sclTop + oHeight))) {
			obj.style.top = sclTop + 50;
		}
		btnImport.disabled = true;
	} else {
		btnImport.disabled = false;
	}
	layerAction("import_ui", val);
*/
	displayHiddenUI("import_ui", "btnImport", val);
}

function displayImportAllUI(val) {
	displayHiddenUI("importall_ui", "btnImportAll", val)
}

function OpenNamedWin(url,winname, w, h) {
  var wL = (screen.width-w)/2;
  var wT = (screen.height-h)/2;
  popWin = window.open(url,winname,"width="+w+",height="+h+",resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,scrollbars=yes");
  popWin.moveTo(wL, wT);	// 將視窗居中
}
/**
 * show Export All Dialog
 * @pram boolean val :
 *               true  : show
 *               false : hidden
 * @return none
 **/
function showExportAllDlg() {
		OpenNamedWin("export_all_dlg.php", "export_all", 300, 290);
}
function showImportAllDlg() {
		showDialog("import_all_dlg.php",true,window, true, 0, 0, '290px', '290px', 'status=0');
}

/**
 * Import UI user presses OK or Cancel
 * @pram boolean val :
 *               true  : user press OK
 *               false : user press Cancel
 * @return none
 **/
function OnImpOK(frm, type) {
	var v = frm.file_import.value;
	var len = v.length;
	if(v=='') {
		alert(MsgInputFile);
		frm.file_import.focus();
		return false;
	}  else if(type == 1 && v.toLowerCase().lastIndexOf(".tgz") != (len-4) && v.toLowerCase().lastIndexOf(".tar.gz") != (len-7)) {
		alert(MsgExt1);
		frm.file_import.focus();
		return false;
	} else if (type == 2 && v.toLowerCase().lastIndexOf(".zip") != (len-4)) {
		alert(MsgExt2);
		frm.file_import.focus();
		return false;
	}
	else {
		frm.submit();
		return true;
	}
}

/**
 * Import UI user presses OK or Cancel
 * @pram boolean val :
 *               true  : user press OK
 *               false : user press Cancel
 * @return none
 **/
function OnImportButton(val) {
	var f = document.getElementById("form_import");
	if(val) {
		//#47307 Chrome [全體/討論板/精華區/匯入] 功能說明是選擇zip來匯入，按下「匯入」後卻出現請選擇tgz、gz的錯誤訊息。：參數：2->1
        if(OnImpOK(f, 2))	displayImportUI(false);
	} else {
		displayImportUI(false);
	}
}

function OnImportAllButton(val) {
	var f = document.getElementById("form_importall");
	if(val) {
		if(OnImpOK(f, 2))	displayImportAllUI(false);
	} else {
		displayImportAllUI(false);
	}
}

/**
 * show or hidden Folder Del Confirm dialog
 * @pram boolean val :
 *               true  : show
 *               false : hidden
 * @return none
 **/
function displayFolderConfirm(val) {
	var obj = document.getElementById("folderConfirm");
	var sclTop = 0, oHeight = 0;

	if (obj == null) return false;
	if (val) {
		sclTop = parseInt(document.body.scrollTop);
		oHeight = (isMZ) ? parseInt(window.innerHeight) : document.body.offsetHeight;
		if ((parseInt(obj.style.top) < sclTop) ||
			(parseInt(obj.style.top) > (sclTop + oHeight))) {
			obj.style.top = sclTop + 50;
		}
	}
	layerAction("folderConfirm", val);
}
