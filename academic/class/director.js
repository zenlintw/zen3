	var xmlHttp = null, xmlDocs = null, xmlVars = null;
	
// ////////////////////////////////////////////////////////////////////////////
	var synBtns = ["btnStep"];
	/**
	 * 同步按鈕的狀態
	 **/
	function synBtn() {
		var btn = null;
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0, j = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked) j++;
		}

		for (var i = 0; i < synBtns.length; i++) {
			btn = document.getElementById(synBtns[i] + "1");
			if (btn != null) btn.disabled = !(j > 0);
			btn = document.getElementById(synBtns[i] + "2");
			if (btn != null) btn.disabled = !(j > 0);
		}

	}

	/**
	 * 切換全選或全消的 checkbox
	 * @version 1.0
	 **/
	function chgCheckbox(chbox) {
		var disable_val = '';
		if(chbox.checked)
			disable_val = false;
		else
			disable_val = true;

		var select_option = document.getElementsByTagName('select');
		for(var i=0; i< select_option.length; i++) {
			if(chbox.value == select_option[i].id) {
				select_option[i].disabled = disable_val;
			}
		}

		var bol = true;
		var nodes = document.getElementsByTagName("input");
		var obj  = document.getElementById("ck");
		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0, j = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked == false) bol = false;
			else j++;
		}

		nowSel = bol;
		if (obj  != null) obj.checked = bol;
		if (btn1 != null) btn1.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		if (btn2 != null) btn2.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		
		synBtn();
	}

	/**
	 * 同步全選或全消的按鈕與 checkbox
	 * @version 1.0
	 **/
	var nowSel = false;
	function selfunc() {
		var obj  = document.getElementById("ck");
		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");
		if ((obj == null) || (btn1 == null) || (btn2 == null)) return false;
		nowSel = !nowSel;
		obj.checked = nowSel;
		btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		btn2.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

		select_func('', obj.checked);

		sys_Select(nowSel);
		synBtn();
	}

	/**
	 * 同步全選或全消的按鈕 與 選單
	 * val => true 下拉選單 打開
		      false 下拉選單 disabled
	 */
	 function sys_Select(val) {
	 	var select_option = document.getElementsByTagName('select');
		for(var i=0; i< select_option.length; i++) {
			if(select_option[i].name == 'select_role[]') {
				select_option[i].disabled = (! val);
			}
		}
	 }
// ////////////////////////////////////////////////////////////////////////////
