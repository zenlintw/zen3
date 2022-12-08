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

	_goto('/forum/500,'+board_id+','+ new_page +','+sortby+'.php');
}

function go_page(n){
	switch(n){
		case -1:	// 第一頁
			_goto('/forum/500,'+board_id+',1,'+sortby+'.php'); // location.replace
			break;
		case -2:	// 前一頁
			_goto('/forum/500,'+board_id+','+(cur_page-1)+','+sortby+'.php');
			break;
		case -3:	// 後一頁
			_goto('/forum/500,'+board_id+','+(cur_page+1)+','+sortby+'.php');
			break;
		case -4:	// 最末頁
			_goto('/forum/500,'+board_id+','+total_page+','+sortby+'.php');
			break;
		case '0':	// 全部列
			_goto('/forum/501,'+board_id+','+cur_page+','+sortby+'.php');
			break;
		default:	// 指定某頁
			_goto('/forum/500,'+board_id+','+n+','+sortby+'.php');
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
	} else {
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
		location.replace('/forum/531,'+board_id+','+from_post+','+to_post+'.php?folder='+folder);
	from_post = 0;
	to_post = 0;
}

function do_q_folder( folder , folder_id) {
	if(folder_id != null && q_folder_type != '') {
		if(q_folder_type == 'copy')
			location.replace('/forum/532,'+board_id+','+from_post+','+to_post+'.php?folder_id='+folder_id);
		else if(q_folder_type == 'move')
			location.replace('/forum/533,'+board_id+','+from_post+','+to_post+'.php?folder_id='+folder_id);
	}
	from_post = 0;
	to_post = 0;
	q_folder_type = '';
}

function batch(obj){
	var o1 = obj.previousSibling.previousSibling.previousSibling.previousSibling;
	var o2 = obj.previousSibling.previousSibling;
	var v1 = o1.value;
	var v2 = o2.value;
	var total_post_msg = '';

	if (total_post == 0){
		switch (obj.value){
			case '1':	// 刪除
				total_post_msg = DEL_ERROR.replace(/%ACTION%/,MSG_del);
				break;
			case '2':	// 收入個人筆記本
				total_post_msg = DEL_ERROR.replace(/%ACTION%/,MSG_nbook);
				break;
			case '3':	// 收入 (copy)
				total_post_msg = DEL_ERROR.replace(/%ACTION%/,MSG_copy_q);
				break;
			case '4':	// 移入 (move)
				total_post_msg = DEL_ERROR.replace(/%ACTION%/,MSG_move_q);
				break;
		}

	   	alert(total_post_msg);
	   	return;
	}

	if (v1.search(/^[0-9]+$/) < 0 ||
	    v2.search(/^[0-9]+$/) < 0 ||
	    parseInt(v1,10) > parseInt(v2,10) ||
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
			if (!confirm(MsgSureDelFrom + v1+ MsgSureDelTo + v2+' ?')) {
	   			batch_default_state(obj,o1,o2, '', '',o1);
				return;
			}
			location.replace('/forum/530,'+board_id+','+v1+','+v2+'.php');
			break;
		case '2':	// 收入個人筆記本
			var sFeatures="dialogHeight: 350px;dialogWidth: 300px;resizable:yes; scroll:no;status:no;";
			from_post = v1;
			to_post   = v2;
			// window.showModalDialog("batch_notebook.php?board="+ board_id+"&v1="+v1+"&v2=" + v2, window, sFeatures)

			showDialog("/forum/batch_notebook.php?board="+ board_id+"&v1="+v1+"&v2=" + v2,
				true,window, true, 0, 0, '300px', '350px', 'resizable=yes;scroll=no;status=no');
			//window.open("batch_notebook.php?board="+ board_id+"&v1="+v1+"&v2=" + v2);
			batch_default_state(obj,o1,o2, '', '',o1);
			//location.replace('531,'+board_id+','+v1+','+v2+'.php');
			break;

		case '3':	// 收入 (copy)
		case '4':	// 移入 (move)
			var sFeatures="dialogHeight: 350px;dialogWidth: 300px;resizable:yes; scroll:no;status:no;help:no;";
			from_post = v1;
			to_post   = v2;
			q_folder_type = (obj.value=='3')?'copy':'move';

			// window.showModalDialog("batch_q_folder.php?board="+ board_id+"&v1="+v1+"&v2=" + v2, window, sFeatures)
			showDialog("/forum/batch_q_folder.php?board="+ board_id+"&v1="+v1+"&v2=" + v2,
				true,window, true, 0, 0, '300px', '350px', 'resizable=yes;scroll=no;status=no;');
			//window.open("batch_q_folder.php?board="+ board_id+"&v1="+v1+"&v2=" + v2);
			batch_default_state(obj,o1,o2, '', '',o1);
			//location.replace('532,'+board_id+','+v1+','+v2+'.php');
			break;
		default:
			alert('type error !');
			return;
	}
}

function go_quint(){
	 location.replace('/forum/q_index.php');
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
	location.replace('/forum/509,'+board_id+','+cur_page+','+ta[type]+'.php');
}

function post(){
	// location.replace('/forum/m_write.php?bTicket=' + bTicket);
    var postFrm = document.getElementById('board_post');
    postFrm.action = '/forum/m_write.php?bTicket=' + bTicket;
    postFrm.submit();
}

function read(n){
	location.replace('/forum/510,'+board_id+','+n+'.php');
}

function subscribe(evnt){
	var obj = (typeof(event) == "undefined") ? evnt.target : evnt.srcElement;

	obj.disabled = true;
	url = '/forum/subscribe.php';
	// showModalDialog(url,window,'dialogWidth=340px;dialogHeight=150px;status=0;help=0');
	showDialog(url,true,window, true, 0, 0, '340px', '150px', 'status=0');
	obj.disabled = false;
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
	displayHiddenUI("import_ui", "btnImport", val)
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
		// showDialog("export_all_dlg.php",true,window, true, 0, 0, '290px', '290px', 'status=0');
		OpenNamedWin("/forum/export_all_dlg.php", "export_all", 300, 290);
}
function showImportAllDlg() {
		showDialog("/forum/import_all_dlg.php",true,window, true, 0, 0, '290px', '290px', 'status=0');
		// OpenNamedWin("import_all_dlg.php", "import_all", 400, 150);
}

/**
 * Import UI user presses OK or Cancel
 * @param boolean val :
 *               true  : user press OK
 *               false : user press Cancel
 * @param int type :
 *                   1 : import
 *                   2 : import all
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
	} else {
		frm.submit();
		return true;
	}
}

function OnImportButton(val) {
	var f = document.getElementById("form_import");
	if(val) {
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

function go_setting() {
    var frm = document.getElementById('board_setting');
    frm.action = '/teach/course/m_cour_annt_property.php';
    frm.submit();
}
