function more_attachs(){
	if(freeQuota<=0) {
		alert(MsgQuota);
		event.srcElement.disabled = true;
		return;
	}

	col = col == "cssTrOdd" ? "cssTrEvn" : "cssTrOdd";
	if (files >= 10){
		alert(MSG_MAX_FILES);
		return;
	}
	var curNode = document.getElementById('upload_box');
	var nxtNode = document.getElementById('upload_base');
	var newNode = curNode.cloneNode(true);
	newNode.className = col;
	curNode.parentNode.insertBefore(newNode, nxtNode);
    newNode.getElementsByTagName("input")[0].value = "";
	files++;
}

function cut_attachs(){
	var curNode = document.getElementById('upload_base');
	var delNode = curNode.previousSibling;
	if (files <= 1){
		var newNode = delNode.cloneNode(true);	// 若原本有選定檔案則清空
		delNode.parentNode.replaceChild(newNode, delNode);
        //#47306 Chrome [全體/討論板/張貼] 張貼畫面中只剩一組附檔欄位，選擇檔案後按下縮減附檔，沒有把已選的檔案清掉。：清空指定欄位
        document.getElementById('uploads[]').value = '';
		return;
	}

	delNode.parentNode.removeChild(delNode);
	col = col == "cssTrOdd" ? "cssTrEvn" : "cssTrOdd";
	files--;
}

function chkform()
{
	if(TimeLeft<=0) {	// 逾時
		displayTimeoutUI( MsgTimeOut, true );
		return false;
	}

	frm = document.getElementById('post1');
	var str = remove_blank(frm.subject.value);
	if(!chkField(str, MsgSubject,255)) {
		frm.subject.focus();
		frm.subject.select();
		return false;
	}
	if(IsNews) { // 是最新消息
		if(!checkNewsTime()) return false;
	}
    /* 如果不是用editor 當text處理 */
    if (typeof(editor) == "undefined") {
        var str = remove_blank(frm.content.value);
        if(!chkField(str, MsgContent)) {
            frm.content.focus();
            frm.content.select();
            return false;
        }
    } else {
        str = editor.getHTML();
        str = remove_blank(str);
        str = remove_tag(str);

        if(!chkField(str, MsgContent)) {
            editor.focus();
            return false;
        }
        frm.content.value = editor.getHTML();
    }

	// S.A by yakko, for whiteboard
	if (typeof(IsUseAudio) != 'undefined' && IsUseAudio && freeQuota > 0)
	{
		if (! (MyRecList.SendData()) ) return false;
	}

    xajax_clean_temp(st_id);

	return true;
}

function submit_form()
{
	return chkform();
}

function remove_blank(s) {
	var re = /[ |\n|\r]+/g;
	return s.replace(re, '');
}

function remove_tag(s) {
	var re = /<(?!.*(iframe))[^>]+>/g;
	return s.replace(re, '');
}

// 檢驗是否為特殊 HTML 字元
var html_chars = new Array(
			"'".charCodeAt(0),"&#039;",
			'"'.charCodeAt(0),"&quot;",
			"<".charCodeAt(0),"&lt;",
			">".charCodeAt(0),"&gt;");

function LengthHtmlChar(c) {
	for(n=0;n<html_chars.length;n+=2) {
		if(c==html_chars[n])
			return html_chars[n+1].length;
	}
	return 1;
}

// 判斷(中)文字長度
function getTxtLength(v) {
	j =0;

	for(i=0;i<v.length;i++) {
		c = v.charCodeAt(i);
		if(c>127) {
			j+= 3;
		}
		else {
			l = LengthHtmlChar(c);
			j+= l;
		}
	}
	return j;
}

// Check Text field : can not be empty
function chkField(str, name, max_len)
{
	if(typeof(max_len)=="undefined") max_len = 0;
	len = getTxtLength(str);
	if(len==0) {
		alert(MsgPlsFill + name + MsgField + "!");
		return false;
	}
	if(max_len > 0 && len>max_len) {
		alert(MsgPlsDontExceed + max_len + MsgCharacters + "!");
		return false;
	}
	return true;
}


// Post Timer check
function calcTimeExpire() {
	TimeLeft -= timerInterval;
}

function make2dig(n) {
	if(n<10) return '0' + n;
	else return n;
}

function TimeFormatStr(secs) {
	var str='';
	mi = Math.floor( secs /60 );
	str = (mi>0?(make2dig(mi) + ':'):'00:') + make2dig(secs - mi*60);
	return str;
}

function showTime() {
	var spn = document.getElementById('warnTimer');
	var str = '';
	var warn_str = '';
	var color = "red";
	if(TimeLeft > TimeToWarn) {
		color="brown";
		str = '';
		warn_str = '';
	} else if(TimeLeft > 0) {
		str=TimeFormatStr(TimeLeft);
		warn_str = MsgYourTime.replace('%s', str);
		str = MsgTimeLeft + ':&nbsp;' + str;	// TimeFormatStr(TimeLeft);
	} else {
		warn_str = MsgTimeOut;
		str = MsgTimeLeft + ':&nbsp;' + MsgTimeOut;
	}

	spn.innerHTML = '<font color="' + color + '">' + str + '</font>';
	var msg_obj = document.getElementById('timeout_note');
	if(msg_obj) {
		msg_obj.innerHTML = warn_str;
	}
}

// 檢查是否已有超過起訖時間
function checkTime() {
	if(CloseTime) {
		timerID = setTimeout('checkTime()', timerInterval*1000);
		calcTimeExpire();
		showTime();
		var warn_str='';
		if( TimeLeft < TimeToWarn && TimeLeft>0 ) {	// 時間快到了且未警告
			var str=TimeFormatStr(TimeLeft);
			warn_str = MsgYourTime.replace('%s', str);
		} else if( TimeLeft<=0 ) {
			var btn = document.getElementById('btnPost');
			if(btn)
				btn.disabled = true;

			warn_str = MsgTimeOut;
			clearTimeout(timerID);
		}

		if(!TimeWarned && warn_str != '') {
			// alert(warn_str);
			displayTimeoutUI(warn_str, true);
			TimeWarned = true;
		}
	}
}

// 檢查最新消息欄位
function checkNewsTime() {
	var node = document.getElementById("post1");
	// 截止日及開始日都要選擇
	if (node.ck_open_time.checked || node.ck_close_time.checked) {
		// 截止日要大於開始日
		if (node.ck_open_time.checked && node.ck_close_time.checked) {
			val1 = node.open_time.value.replace(/[\D]/ig, '');
			val2 = node.close_time.value.replace(/[\D]/ig, '');
			if (parseInt(val1) >= parseInt(val2)) {
				alert(MsgDateError);
				node.open_time.focus();
				return false;
			}
		}
		else {
			alert(MsgNewsTime);
			return false;
		}
	}
	return true;
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
var debug_flag=true;
function debug(s) {
	if(debug_flag)	debug_flag = confirm(s);
}

function displayHiddenTimeUI(ui_name, msg_id, msg, val) {
	var obj = document.getElementById(ui_name);
	var sclTop = 0, oHeight = 0;

	if (obj == null) return false;
	if (msg!='') {
		var msg_obj = document.getElementById(msg_id);
		if(msg_obj) {
			msg_obj.innerHTML = msg;
		} else
			debug('msg_obj is null');
	}

	if (val) {
		sclTop = parseInt(document.body.scrollTop);
		oHeight = (isMZ) ? parseInt(window.innerHeight) : document.body.offsetHeight;
		if ((parseInt(obj.style.top) < sclTop) ||
			(parseInt(obj.style.top) > (sclTop + oHeight))) {
			obj.style.top = sclTop + 50;
		}
	}

	layerAction(ui_name, val);
}

/**
 * show or hidden Timeout UI
 * @pram string msg : text to show
 * @pram boolean val :
 *               true  : show
 *               false : hidden
 * @return none
 **/
function displayTimeoutUI(msg, val) {
	displayHiddenTimeUI("timeout_ui",'timeout_note', msg, val);
}
