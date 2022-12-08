	var xmlDocs = null, xmlTeam = null, xmlVars = null, xmlHttp = null;
	var CacheFilter = new Object();

	/**
	 * 取得單一節點的值
	 **/
	function getNodeValue(node, tag) {
		var childs = null;

		if ((typeof(node) != "object") || (node == null)) return "";
		childs = node.getElementsByTagName(tag);
		if ((childs == null) || (childs.length <= 0)) return "";
		if (childs[0].hasChildNodes()) {
			return childs[0].firstChild.data;
		} else {
			return "";
		}
	}

	/**
	 * 建立組次 (build Team)
	 **/
	function buildTeam() {
		var obj = document.getElementById("spanTeam");
		var nodes = null, aid = null, ane = null;
		var txt = "";

		if ((xmlTeam == null) || (obj == null)) return false;

		nodes = xmlTeam.getElementsByTagName("team");
		txt  = '<select name="mtTeam" id="mtTeam" class="cssInput" onchange="buildGroup(this.value);">';
		txt += '<option value="all">' + MSG_ALL + '</option>';
		for (var i = 0; i < nodes.length; i++) {
			aid = nodes[i].getAttribute("id");
			ane = nodes[i].getAttribute("name");
			txt += '<option value="' + aid + '">' + ane + '</option>';
		}
		txt += '</select>';

		obj.innerHTML = txt;
	}

	/**
	 * 建立學員分組的下拉選單
	 **/
	function buildGroup(val) {
		var obj = null;
		var nodes = null, aid = null, ane = null;
		var txt = "";

		if (xmlTeam == null) return false;
		if (val == '') {
			obj = document.getElementById("mtTeam");
			if (obj == null) return false;
			val = obj.value;
		}
		obj = document.getElementById("spanGroup");
		if (obj == null) return false;

		nodes = xmlTeam.selectNodes('//team[@id="' + val + '"]/group');
		txt  = '<select name="mtGroup" id="mtGroup" class="cssInput">';
		txt += '<option value="all">' + MSG_ALL + '</option>';
		for (var i = 0; i < nodes.length; i++) {
			aid = nodes[i].getAttribute("id");
			ane = nodes[i].getAttribute("name");
			txt += '<option value="' + aid + '">' + ane + '</option>';
		}
		txt += '</select>';
		obj.innerHTML = txt;
	}

	/**
	 * 取得學員分組的資料
	 **/
	function getGroup() {
		var txt = "";
		var res = false;

		txt = "<manifest><ticket></ticket></manifest>";
		res = xmlVars.loadXML(txt);
		if (!res) {
			return false;
		}
		xmlHttp.open("POST", "/teach/student/stud_mailto_group.php", false);
		xmlHttp.send(xmlVars);
		// alert(xmlHttp.responseText);
		res = xmlTeam.loadXML(xmlHttp.responseText);
		// alert('res='+res);
		if (!res) {
			xmlTeam = XmlDocument.create();
			return false;
		}

		buildTeam();
		buildGroup('');
	}

	function buildOP(val1, val2) {
		var obj = null, node = null, nodes = null, attr = null;
		var txt = "", str = "";

		if (CacheFilter[val1] == null) return false;
		else if (!xmlVars.loadXML(CacheFilter[val1])) return false;

		obj = document.getElementById("spanOP");

		nodes = xmlVars.selectNodes('//filter[@key="' + val2 + '"]/operators/operator');
		txt = '<select name="mtOP" id="mtOP" class="cssInput">';
		for (var i = 0; i < nodes.length; i++) {
			attr = nodes[i].getAttribute("value");
			str  = (nodes[i].hasChildNodes()) ? nodes[i].firstChild.nodeValue : "";
			txt += '<option value="' + attr + '">' + str + '</option>';
		}
		txt += '</select>';

		node = xmlVars.selectSingleNode('//filter[@key="' + val2 + '"]/values');
		attr = node.getAttribute("type");
		switch (attr) {
			case "list":
				txt += '&nbsp;<select name="mtVal" id="mtVal" class="cssInput" style="width: 140px" myattr="' + attr + '">';
				nodes = node.getElementsByTagName("value");
				for (var i = 0; i < nodes.length; i++) {
					attr = nodes[i].getAttribute("id");
					str  = (nodes[i].hasChildNodes()) ? nodes[i].firstChild.nodeValue : "";
					txt += '<option value="' + attr + '">' + str + '</option>';
				}
				txt += '</select>';
				break;
			case "date":
			case "datetime":
				txt += '&nbsp;<input type="text" value="" name="mtVal" id="mtVal" class="cssInput" readonly="readonly" myattr="' + attr + '">';
				// txt += '&nbsp;<button id="btnVal" class="cssBtn">&rArr;</button>';
				break;
			default:
				txt += '&nbsp;<input type="text" name="mtVal" id="mtVal" class="cssInput" myattr="' + attr + '">' + (val1 == 'progress' && val2 == 'total' ? MSG_progress_minute:'');
		}
		obj.innerHTML = txt;
		if (attr == "date") {
			Calendar_setup("mtVal", "%Y-%m-%d", "mtVal", false);
			Calendar_setup("mtVal", "%Y-%m-%d", "btnVal", false);
		}
		if (attr == "datetime") {
			Calendar_setup("mtVal", "%Y-%m-%d %H:%M", "mtVal", true);
			Calendar_setup("mtVal", "%Y-%m-%d %H:%M", "btnVal", true);
		}
	}

	function buildFilter(val,val2) {
		var obj = null, nodes = null, attr = null, childs = null;
		var txt = "", str = "";
		var va = "";
		if (CacheFilter[val] == null) return false;
		else if (!xmlVars.loadXML(CacheFilter[val])) return false;
		obj = document.getElementById("spanType");

		txt = '<select name="mtFilter" id="mtFilter" class="cssInput" onchange="buildOP(\'' + val + '\', this.value);">';
		nodes = xmlVars.getElementsByTagName("filter");
		for (var i = 0; i < nodes.length; i++) {
			attr = nodes[i].getAttribute("key");
			str = getNodeValue(nodes[i], "title");
			txt += '<option value="' + attr + '"' + (val2 == attr ? ' selected':'') + '>' + str + '</option>';
			if (va == "") va = attr;
		}
		txt += '</select>';

		obj.innerHTML = txt;
		if (val2 == undefined)
		  buildOP(val, va);
		else
		  buildOP(val, val2);
	}

	/**
	 * 取得不同的類型資料
	 * @param string val : 不同種類的資料
	 **/
	function getFilter(val, val2) {
		var txt = "";
		var res = false;

		if (CacheFilter[val] != null) {
			buildFilter(val);
            // #47161 Chrome 因為點名條件只能切換一回，因此暫時將 return false 關閉
			// return false;
		}

		txt  = "<manifest>";
		txt += "<ticket></ticket>";
		txt += "<filter>" + val + "</filter>";
		txt += "</manifest>";
		res = xmlVars.loadXML(txt);
		if (!res) {
			return false;
		}

		try {
			xmlHttp.open("POST", "/teach/student/stud_mailto_filter.php", false);
			xmlHttp.send(xmlVars);
		} catch (e) {
			return false;
		}
		// alert(xmlHttp.responseText);
		res = xmlVars.loadXML(xmlHttp.responseText);
		if (!res) {
			xmlVars = XmlDocument.create();
			return false;
		}

		CacheFilter[val] = xmlHttp.responseText;
		if (typeof val2 == "undefined")
		{
		  buildFilter(val);
		  // 給予預設的值
		  var oSubject = document.getElementById("mail_subject");
		  if (oSubject)
		  {
			  switch (val)
			  {
				case 'lesson':
					oSubject.value = MSG_SUBJECT_Lesson;
					editor.setHTML(MSG_CONTENT_Lesson);
					break;
				case 'exam':
					oSubject.value = MSG_SUBJECT_EXAM;
					editor.setHTML(MSG_CONTENT_EXAM);
					break;
				case 'homework':
					oSubject.value = MSG_SUBJECT_HW;
					editor.setHTML(MSG_CONTENT_HW);
					break;
				case 'questionnaire':
					oSubject.value = MSG_SUBJECT_Questionnaire;
					editor.setHTML(MSG_CONTENT_Questionnaire);
					break;
			  }
		  }
		}else
		  buildFilter(val, val2);
	}
        
    function IE(v) {
      return RegExp('msie' + (!isNaN(v)?('\\s'+v):''), 'i').test(navigator.userAgent);
    }        
	
    // #47450 [教師/人員管理/寄信與點名/自動點名設定/新增] 只選取一個檔案，按下「縮減附檔」，不會把檔案清掉：改仿審核學員的操作方式
    var files = 1;
	// #47203 修正chrome點選「更多附件」失敗
    function more_file(td){
        /*
        var is_chrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1; 
        if(is_chrome || IE(10)) {
            var curNode = document.getElementById('upload_box');
            var nxtNode = document.getElementById('upload_base');
            var newNode = curNode.cloneNode(true);
            curNode.parentNode.insertBefore(newNode, nxtNode);
        } else {
            td = td.parentNode.parentNode.previousSibling.firstChild.nextSibling;
            td.appendChild(td.lastChild.cloneNode(true));
        }
        */
        // #56039 原方式在更多附件將 chrome 分流，縮減檔案未分流，造成IE8會砍表格
        // 單純使用 firstChild lastChild nextSibling 等會取到 TEXTNODE ，新增getFirstChild()、getLastChild()、getNextSiblingTd() 來處理 NODE
        var curNode = getNextSiblingTd(getFirstChild(document.getElementById('upload_box')));
        curNode.appendChild(getLastChild(curNode).cloneNode(true));
        files++;
	}
    
	/**
	 * 取得第一個子節點 (避開 TextNodes)
	 */
    function getFirstChild(node){
        var firstChild = node.firstChild;
        while(firstChild != null && firstChild.nodeType == 3){ // skip TextNodes
          firstChild = firstChild.nextSibling;
        }
        return firstChild;
    }
    
    /**
	 * 取得最後一個子節點 (避開 TextNodes)
	 */
    function getLastChild(node){
        var lastChild = node.lastChild;
        while(lastChild != null && lastChild.nodeType == 3){ // skip TextNodes
          lastChild = lastChild.previousSibling;
        }
        return lastChild;
    }
    
    /**
	 * 取得最近一個 <SPAN> 弟節點
	 */
	function getNextSiblingTd(node){
		var cur = node;
		while(cur.nextSibling != null){
			cur = cur.nextSibling;
			if (cur.tagName == 'TD') return cur;
		}
		return null;
	}