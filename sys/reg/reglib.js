/* $Id: reglib.js,v 1.1 2010/02/24 02:40:20 saly Exp $ */

	function checkData() {
		var re = null;
		var obj = document.getElementById("actForm");
		if (obj == null) return false;

		if (obj.username.value == "") {
			alert(MSG[1]);
			obj.username.focus();
			return false;
		}

		if ((obj.username.value.length < parseInt(sysAccountMinLen)) || (obj.username.value.length > parseInt(sysAccountMaxLen))) {
			alert(MSG[2]);
			obj.username.focus();
			return false;
		}

		/*
      	* compute the total number of underline or minus
      	*/
		var data3 = obj.username.value;

		var underline = 0;
		var minus = 0;
		for (var i =0;i<data3.length;i++){
			if (data3.charAt(i) == '_'){
				underline++;
			}
			if (data3.charAt(i) == '-'){
				minus++;
			}
      	}
      	var total_sum = underline+minus;

      	if ( (total_sum) > 1){
      		alert(MSG[3]);
			obj.username.focus();
			return false;
      	}

      	if (underline > 1){
			alert(MSG[3]);
			obj.username.focus();
			return false;
      	}

      	if (minus > 1){
      		alert(MSG[3]);
			obj.username.focus();
			return false;
      	}

		/*
      	* 檢查底線 and 減號 有無出現在 字尾
      	*/

      	if( (data3.substring(data3.length-1,data3.length) == '_') || (data3.substring(data3.length-1,data3.length) == '-')){
        	alert(MSG[3]);
			obj.username.focus();
			return false;
      	}

		re = Account_format;
		if (!re.test(obj.username.value)) {
			alert(MSG[3]);
			obj.username.focus();
			return false;
		}

		if (obj.password.value == "") {
			alert(MSG[4]);
			obj.password.focus();
			return false;
		}

		if (obj.password.value.length < 6) {
			alert(MSG[5]);
			obj.password.focus();
			return false;
		}

		if (obj.repassword.value == "") {
			alert(MSG[6]);
			obj.repassword.focus();
			return false;
		}

		if (obj.password.value != obj.repassword.value) {
			alert(MSG[7]);
			obj.password.focus();
			return false;
		}

		if (obj.last_name.value == "") {
			alert(MSG[9]);
			obj.last_name.focus();
			return false;
		}

		if (!Filter_Spec_char(obj.last_name.value)){
			obj.last_name.focus();
			alert(un_htmlspecialchars(MSG[14]));
			return false;
		}

		if (obj.first_name.value == "") {
			alert(MSG[8]);
			obj.first_name.focus();
			return false;
		}

		if (!Filter_Spec_char(obj.first_name.value)){
			obj.first_name.focus();
			alert(un_htmlspecialchars(MSG[13]));
			return false;
		}

		if (obj.email.value == "") {
			alert(MSG[10]);
			obj.email.focus();
			return false;
		}

		re = mail_rule;
		if (!re.test(obj.email.value)) {
			alert(MSG[11]);
			obj.email.focus();
			return false;
		}

		if ((obj.home_tel.value == "") && (obj.office_tel.value == "") && (obj.cell_phone.value == "")) {
			alert(MSG[12]);
			obj.home_tel.focus();
			return false;
		}

		return true;
	}

	function checkData2() {
		var re = null;
		var obj = document.getElementById("ModifyForm");
		if (obj == null) return false;

		if ((obj.password.value != "") && (obj.password.value.length < 6) ){
			alert(MSG[5]);
			obj.password.focus();
			return false;
		}

		if (obj.password.value != obj.repassword.value) {
			alert(MSG[7]);
			obj.password.focus();
			return false;
		}

		if (obj.last_name.value == "") {
			alert(MSG[9]);
			obj.last_name.focus();
			return false;
		}

		if (!Filter_Spec_char(obj.last_name.value)){
			obj.last_name.focus();
			alert(un_htmlspecialchars(MSG[13]));
			return false;
		}

		if (obj.first_name.value == "") {
			alert(MSG[8]);
			obj.first_name.focus();
			return false;
		}

		if (!Filter_Spec_char(obj.first_name.value)){
			obj.first_name.focus();
			alert(un_htmlspecialchars(MSG[12]));
			return false;
		}


		if (obj.email.value == "") {
		alert(MSG[10]);
			obj.email.focus();
			return false;
		}

		re = mail_rule;
		if (!re.test(obj.email.value)) {
			alert(MSG[11]);
			obj.email.focus();
			return false;
		}

		if ((obj.home_tel.value == "") && (obj.office_tel.value == "") && (obj.cell_phone.value == "")) {
			alert(MSG[12]);
			obj.home_tel.focus();
			return false;
		}

		return true;
	}
	
	function check_reg_username() {
		var xmlHttp = null;
		var xmlDocs = null;
		var txt = '';

		var obj = document.getElementById("actForm");
		if (obj == null) return false;
		
		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
		
		if(obj.username.value != '') {
			txt  = "<manifest>";		
			txt += "<exist_user>"+obj.username.value+"</exist_user>";		
			txt += "</manifest>";
			if (!xmlDocs.loadXML(txt)) {
				xmlDocs.loadXML("<manifest />");
				return false;
			}
			xmlHttp.open("POST", "check_user.php", false);
			xmlHttp.send(xmlDocs);
			// alert(xmlHttp.responseText);
			if (!xmlDocs.loadXML(xmlHttp.responseText)) {
				xmlDocs.loadXML(txt);
			}
			node = xmlDocs.selectSingleNode('//result');
			if (node.hasChildNodes()) {
				result_id = node.firstChild.nodeValue;	
				switch (result_id) {
				    case '0' : return true; break;
				    case '1' : 
				    case '4' : alert(obj.username.value + MSG[16]); break;
				    case '2' : alert(MSG[15]); break;
				    case '3' : alert(MSG[3] ); break;
				}
			    obj.username.value = '';
			    obj.username.focus();
			    return false;
			}	
		}		
	}

	function trimForm() {
		var objs = document.getElementsByTagName("input");
		var re = /[ ]+$/ig;
		for (i = 0; i < objs.length; i++) {
			//alert(objs[i].value);
			objs[i].value = objs[i].value.replace(re, "");
		}
	}

	window.onload = trimForm;
