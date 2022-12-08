// test
function go_post(n){
	switch(n){
		case -1:
			location.replace('570,'+board_id+','+ (total_dir+1) +'.php');
			break;
		case -2:
			location.replace('570,'+board_id+','+(cur_post-1)+'.php');
			break;
		case -3:
			location.replace('570,'+board_id+','+(cur_post+1)+'.php');
			break;
		case -4:
			location.replace('570,'+board_id+','+total_post+'.php');
			break;
		default:
			location.replace('570,'+board_id+','+n+'.php');
			break;
	}
}

function edit(){
    var oSubject = document.getElementById('o_subject');
    if(typeof oSubject.innerText == 'undefined') {
        document.getElementById('post4').subject.value = oSubject.textContent;
    } else {
        document.getElementById('post4').subject.value = oSubject.innerText;
    }	
	document.getElementById('post4').content.value = document.getElementById('o_content').innerHTML;
	document.getElementById('post4').submit();
}

//#56428 Chrome 點選刪除按鈕，按鈕會消失且文章沒有被刪除，因為函數名稱跟其他相同
function removeArticle(pid){
	if (confirm(MSG_DELETE)){
		location.replace('580,'+pid+'.php');
	}
}

function mail(obj){
    obj.disabled = true;
    var newEmail = prompt('Target E-mail :',email);
    if (newEmail == null) return;
    if (isIllegalEmail(newEmail)){
            alert('E-mail format error !');
            return;
    }
    
    // 改用 ajax 與 alert
//    url = 'mail.php?type=quint&node=' + node +'&target='+newEmail;
    url = appRoot + '/forum/' + 'mail.php?type=quint&node=' + node +'&target='+newEmail;
    
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

function reply(){
	document.getElementById('post3').subject.value = document.getElementById('o_subject').innerText;
	document.getElementById('post3').content.value = 'At ' +
							 document.getElementById('pt').innerText +
							 document.getElementById('poster').innerText +
							 'say : <br />\n' +
							 document.getElementById('o_content').innerHTML;
	document.getElementById('post3').submit();
}

function forward(){
	// ret = showModalDialog('pickBoard.php','','dialogWidth=360px;dialogHeight=100px;status=0;help=0');
	ret = showDialog('pickBoard.php', true, window, true, 0, 0, '360px', '100px', 'resizable=0,scrollbars=0,status=0');
	if (ret)
		alert('target='+ret);
	else
		alert('cancel .');
}

function nbook(){
	var sFeatures="resizable:yes; scroll:yes;status:no;help:no;";
	showDialog('batch_notebook.php', true, window, true, 0, 0, '330px', '300px', sFeatures);
}

function move(){
	var sFeatures="resizable:yes; scroll:yes;status:no;";
	showDialog('batch_q_folder.php', true, window, true, 0, 0, '330px', '300px', sFeatures);
}

// 供 Modal Dialog Callback 用
function do_notebook( folder) {
	if(folder != null) {
		var sFeatures="resizable:yes; scroll:yes;status:no;help:no;";
		showDialog('q_notebook.php?board='+board_id+'&node='+node+'&site='+site_id+'&path='+path+'&folder='+folder, true, window, true, 0, 0, '350px', '300px', sFeatures);
	}
}

// 供 (move) Modal Dialog Callback 用
function do_q_folder( folder, folder_id) {
	if(folder_id != null && folder != path) {
		// alert(folder_id);
		// location.replace('q_move.php?board='+board_id+'&node='+node+'&site='+site_id+'&path='+path+'&folder_id='+folder_id);
		location.replace('q_move.php?ticket='+ticket+'&node='+node+'&site='+site_id+'&folder_id='+folder_id);
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
 * show or hidden Export Options
 * @pram boolean val :
 *               true  : show
 *               false : hidden
 * @return none
 **/
function displayExportOptions(val) {
	var obj = document.getElementById("export_options");
	var btnExport = document.getElementById("btnExport");
	var sclTop = 0, oHeight = 0;

	if (obj == null) return false;
	if (val) {
		sclTop = parseInt(document.body.scrollTop);
		oHeight = (isMZ) ? parseInt(window.innerHeight) : document.body.offsetHeight;
		if ((parseInt(obj.style.top) < sclTop) ||
			(parseInt(obj.style.top) > (sclTop + oHeight))) {
			obj.style.top = sclTop + 50;
		}
		obj.style.visibility = "visible";
		btnExport.disabled = true;
	} else {
		obj.style.visibility = "hidden";
		btnExport.disabled = false;
	}
	layerAction('export_options',val);
}
