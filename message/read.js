	/**
	 * read message javascript
	 *
	 * 建立日期：2003/05/14
	 * @author  ShenTing Lin
	 * @version $Id: read.js,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright 2003 SUNNET
	 **/

	/**
	 * write new message
	 **/
	function post(){
		remove_unload();
		location.replace('write.php');
	}

	/**
	 * delete selected message
	 * @param
	 * @return
	 **/
	function del() {
		var obj = null;
		var cnt = 0;

		obj = document.getElementById("mainFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		if ((folder_id == "sys_trash") || (folder_id == "sys_notebook_trash")) {
			if (!confirm(MSG_CONFIRM_DEL)) return false;
		} else {
			alert(MSG_DEL_ALERT);
		}
		obj.action = "del.php";
		remove_unload();
		obj.submit();
	}

	/**
	 * move message to another folder
	 **/
	function mv() {
		var obj = null, aobj = null;
		var msgFolder = "";

		obj = document.getElementById("mainFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;

		aobj = getTarget();
		if ((aobj == null) || (typeof(aobj.getSelFolder) != "function")) return false;
		msgFolder = aobj.getSelFolder();
		if (msgFolder == "") {
			alert(MSG_SEL_TARGET);
			return false;
		}
		if (msgFolder == folder_id) {
			alert(MSG_SAME_FOLDER);
			return false
		}
		remove_unload();
		obj.action = "move.php";
		obj.folder_id.value = msgFolder;
		obj.submit();
		return false;
	}

	/**
	 * reply message
	 **/
	function reply(){
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return '';
		remove_unload();
		obj.action = "reply.php";
		obj.submit();
	}

	/**
	 * forward message
	 **/
	function fw(){
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return '';
		remove_unload();
		obj.action = "forward.php";
		obj.submit();
	}

	/**
	 * modify message
	 **/
	function modify(){
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return '';
		remove_unload();
		obj.action = "modify.php";
		obj.submit();
	}

	/**
	 * collect to notebook
	 **/
	function cp2note(){
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return '';
		remove_unload();
		obj.action = "collect_note.php";
		obj.submit();
	}

	function go_page(n){
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return '';
		switch(n){
			case -1: obj.act.value = 'fp'; break;
			case -2: obj.act.value = 'pp'; break;
			case -3: obj.act.value = 'np'; break;
			case -4: obj.act.value = 'lp'; break;
			default:
				obj.act.value = 'lp';
		}
		remove_unload();
		obj.submit();
	}

	function remove_unload() {
		window.onunload = function () {};
	}

	function winColse() {
		var obj = null;
		obj = getTarget();
		if (obj != null) obj.location.replace("about:blank");
	}

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

	window.onload = function () {
		var obj1 = null, obj2 = null;

		obj1 = document.getElementById("tb1");
		obj2 = document.getElementById("tb2");
		if ((obj1 != null) && (obj2 != null))
			obj2.innerHTML = obj1.innerHTML;
	};

	window.onunload = winColse;
