	var days = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

	var CalAdvWin = null;

	function ckLeapYear() {
		if (( ((theYear % 4) == 0) && ((theYear % 100) != 0) ) || ((theYear % 400) == 0))
			days[1] = 29;
		else
			days[1] = 28;
	}

	var preTD = null;
	function chgTdBg(obj) {
		if (preTD != null) {
		    preTD.className = preTD.className.replace('cssTrOdd', 'cssTrEvn');
		}
		if (typeof(obj) == "object") {
			preTD = obj;
			preTD.className = preTD.className.replace('cssTrEvn', 'cssTrOdd');
		}
	}

    function calendar() {
    	var obj = document.getElementById("tabsMonth");
		var dateObj = new Date(theYear, theMonth, 1);
		var firstWeek = dateObj.getDay();
		var lastDay = 0, idx1 = 0, idx2 = 0, cnt = 0;
		var nD = parseInt(orgDay) + firstWeek;
		var col = "";
		var res1 = "", res2 = "";
		var nodes = null, node = null;

		if (obj == null) return false;
		nodes = xmlDoc.getElementsByTagName("date");

		ckLeapYear();
		lastDay = days[theMonth] + firstWeek;
		for (var i = 1, j = 2, k = 0; i <= 42; i++, k++) {
			if (k == 0) col = "cssCaleFont01";
			else if (k == 6) col = "cssCaleFont02";
			else col = "";
			col += ((orgYear == theYear) && (orgMonth == theMonth) && (nD == i)) ? " cssTrHead" : " cssTrEvn";
			obj.rows[j].cells[k].className = col;

			if ((i > firstWeek) && (i <= lastDay)) {
				idx1 = i - firstWeek;
				obj.rows[j].cells[k].innerHTML = idx1;
				obj.rows[j].cells[k].setAttribute("value", idx1);
				obj.rows[j].cells[k].setAttribute("id", "day" + idx1);
				obj.rows[j].cells[k].onclick = function () {
					theDay = parseInt(this.getAttribute("value"));
					chgTdBg(this);
					displayEditMemo(false);
					displayDelMsgBox(false);
					do_func("day", 0);
					if(isDock) {
    						var objTemp = document.getElementById("TitleID2");
    						tabsMouseEvent(objTemp, 2);
					}
				}

				res1 = "";
				res2 = "";
				obj.rows[j].cells[k].title = msgNoMemo;
				idx2 = parseInt(idx1) - 1;
				if (nodes[idx2] != null) {
					node = nodes[idx2].getElementsByTagName("school");
					cnt = parseInt(node[0].getAttribute("num"));
					if (cnt > 0) {
						res1 += msgSchool + cnt + msgMemoNum;
						res2 += '<img src="' + theme + 'cale_school.gif" width="12" height="14" align="absmiddle">';
					}

					node = nodes[idx2].getElementsByTagName("class");
					cnt = parseInt(node[0].getAttribute("num"));
					if (cnt > 0) {
						res1 += ((res1.length > 0) ? "\n" : "") +msgClass + cnt + msgMemoNum;
						res2 += '<img src="' + theme + 'cale_class.gif" width="12" height="14" align="absmiddle">';
					}

					node = nodes[idx2].getElementsByTagName("course");
					cnt = parseInt(node[0].getAttribute("num"));
					if (cnt > 0) {
						res1 += ((res1.length > 0) ? "\n" : "") +msgCourse + cnt + msgMemoNum;
						res2 += '<img src="' + theme + 'cale_course.gif" width="12" height="14" align="absmiddle">';
					}

					node = nodes[idx2].getElementsByTagName("personal");
					cnt = parseInt(node[0].getAttribute("num"));
					if (cnt > 0) {
						res1 += ((res1.length > 0) ? "\n" : "") + msgPersonal + cnt + msgMemoNum;
						res2 += '<img src="' + theme + 'cale_personal.gif" width="12" height="14" align="absmiddle">';
					}
					if (res1.length > 0) obj.rows[j].cells[k].title = res1;
					if (res2.length > 0) obj.rows[j].cells[k].innerHTML += '<br />' + res2;
				}
			} else {
				obj.rows[j].cells[k].innerHTML = "&nbsp;";
			}

			if ((i % 7) == 0) {
				k = -1;
				j++;
			}
		}
	}

	var col = "cssTrEvn";
	var rowIdx = 2;
	function parseMemo(node, obj, memoType) {
		var cnt = 0;
		var nodes = null, childs = null, attr = null, childattr = null;
		var txt = "", timestr = "";

		if ((typeof(node) != "object") || (node == null)) return false;

		nodes = node.getElementsByTagName("memo");
		if ((nodes == null) && (nodes.length <= 0)) return false;

		cnt = nodes.length;
		for (var i = 0; i < cnt; i++) {
			attr = nodes[i].getAttribute("id");
			un = nodes[i].getElementsByTagName("username");
			if ((un != null) && (un.length > 0)) {
				username=un[0].firstChild.data;
			} else {
				username='';
			}
			col = (col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
			obj.insertRow(rowIdx);
			obj.rows[rowIdx].className = col;
			for (var j = 0; j < colCnt; j++) {
				obj.rows[rowIdx].insertCell(0);
				switch (j){
					case 2:
						obj.rows[rowIdx].cells[j].width = 100;
						break;
					case 3:
						obj.rows[rowIdx].cells[j].width = (calLmt=='N'?260:310);
						break;
					case 4:
					case 5:
						obj.rows[rowIdx].cells[j].width = 25;
						break;
					default:
						obj.rows[rowIdx].cells[j].width = 20;
						break;
				}
			}
			obj.rows[rowIdx].cells[0].align = 'center';
			obj.rows[rowIdx].cells[0].height = 40;
			obj.rows[rowIdx].cells[0].innerHTML = parseInt(rowIdx / 2);
			obj.rows[rowIdx].cells[1].align = 'center';
			if (calLmt=='N')
			{
				obj.rows[rowIdx].cells[4].align = 'center';
				obj.rows[rowIdx].cells[5].align = 'center';
				obj.rows[rowIdx].cells[4].innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;';
				obj.rows[rowIdx].cells[5].innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			switch (memoType) {
				case "School" :
					obj.rows[rowIdx].cells[1].innerHTML = '<img src="' + theme + 'cale_school.gif" width="12" height="14" align="absmiddle">';
					if(env=='academic' && ownerid==username && calLmt=='N') {
						obj.rows[rowIdx].cells[4].innerHTML = '<a href="javascript:;" class="cssAnchor" onclick="do_func(\'edit\', ' + attr + '); return false;">' + msgEdit + '</a>';
						obj.rows[rowIdx].cells[5].innerHTML = '<a href="javascript:;" class="cssAnchor" onclick="do_func(\'delete\', ' + attr + '); return false;">' + msgDelete + '</a>';
					}
					break;
				case "Class" :
					obj.rows[rowIdx].cells[1].innerHTML = '<img src="' + theme + 'cale_class.gif" width="12" height="14" align="absmiddle">';
					if(env=='direct' && ownerid==username && calLmt=='N') {
						obj.rows[rowIdx].cells[4].innerHTML = '<a href="javascript:;" class="cssAnchor" onclick="do_func(\'edit\', ' + attr + '); return false;">' + msgEdit + '</a>';
						obj.rows[rowIdx].cells[5].innerHTML = '<a href="javascript:;" class="cssAnchor" onclick="do_func(\'delete\', ' + attr + '); return false;">' + msgDelete + '</a>';
					}
					break;
				case "Course" :
					obj.rows[rowIdx].cells[1].innerHTML = '<img src="' + theme + 'cale_course.gif" width="12" height="14" align="absmiddle">';
					if(env=='teach' && ownerid==username && calLmt=='N') {
						obj.rows[rowIdx].cells[4].innerHTML = '<a href="javascript:;" class="cssAnchor" onclick="do_func(\'edit\', ' + attr + '); return false;">' + msgEdit + '</a>';
						obj.rows[rowIdx].cells[5].innerHTML = '<a href="javascript:;" class="cssAnchor" onclick="do_func(\'delete\', ' + attr + '); return false;">' + msgDelete + '</a>';
					}
					break;
				case "Personal" :
					obj.rows[rowIdx].cells[1].innerHTML = '<img src="' + theme + 'cale_personal.gif" width="12" height="14" align="absmiddle">';
					if(env=='learn' && calLmt=='N') {
						obj.rows[rowIdx].cells[4].innerHTML = '<a href="javascript:;" class="cssAnchor" onclick="do_func(\'edit\', ' + attr + '); return false;">' + msgEdit + '</a>';
						obj.rows[rowIdx].cells[5].innerHTML = '<a href="javascript:;" class="cssAnchor" onclick="do_func(\'delete\', ' + attr + '); return false;">' + msgDelete + '</a>';
					}
					break;
			}

			col = (col == "cssTrEvn") ? "cssTrOdd" : "cssTeEvn";
			obj.insertRow(rowIdx + 1);
			obj.rows[rowIdx + 1].id = "contTD" + attr;
			obj.rows[rowIdx + 1].className = col;
			//obj.rows[rowIdx + 1].style.display = "none";
			obj.rows[rowIdx + 1].insertCell(0);
			obj.rows[rowIdx + 1].insertCell(0);
			obj.rows[rowIdx + 1].cells[0].height = '40px';
			obj.rows[rowIdx + 1].cells[0].colSpan = 2;
			obj.rows[rowIdx + 1].cells[0].align = 'center';
			obj.rows[rowIdx + 1].cells[0].innerHTML = msgContent;
			obj.rows[rowIdx + 1].cells[1].colSpan = (calLmt=='N'?4:2);
			obj.rows[rowIdx + 1].cells[1].innerHTML = '&nbsp;';

			childs = nodes[i].childNodes;
			caption = '';
			for (var j = 0; j < childs.length; j++) {
				if (childs[j].nodeType != 1) continue;
				switch (childs[j].nodeName) {
					case "time_begin" :
						timestr = (childs[j].hasChildNodes()) ? msgFrom + childs[j].firstChild.data : '';
						break;
					case "time_end" :
						if (timestr.length > 0) {
							obj.rows[rowIdx].cells[2].innerHTML = (childs[j].hasChildNodes()) ? timestr + "<br />" + msgTo + childs[j].firstChild.data : '&nbsp;';
						}
						break;
					case "caption" :
						caption += (childs[j].hasChildNodes()) ?  childs[j].firstChild.data : '';
						caption = (caption==''?'':'('+caption+')');
						break;
					case "subject" :
						txt = (childs[j].hasChildNodes()) ?childs[j].firstChild.data : '';
						txt = htmlspecialchars(caption + txt);
						// obj.rows[rowIdx].cells[3].innerHTML =  htmlspecialchars(caption+txt); //(childs[j].hasChildNodes()) ? childs[j].firstChild.data : '&nbsp;';
						obj.rows[rowIdx].cells[3].innerHTML = (txt == "") ? "&nbsp;" : txt;
						break;
					case "content" :
						childattr = childs[j].getAttribute("type");
						if (!childs[j].hasChildNodes()) {
							obj.rows[rowIdx + 1].cells[1].innerHTML = "&nbsp;";
						} else {
							txt = childs[j].firstChild.data;
							if ((childattr != null) && (childattr == "text"))
								txt = htmlspecialchars(txt);
							obj.rows[rowIdx + 1].cells[1].innerHTML = txt;
						}
						break;
				} // End switch (childs[i].nodeName)
			} // End for (var j = 0; j < childs.length; j++)
			rowIdx = rowIdx + 2;
		}// End for (var i = 0; i < cnt; i++, idx++)
		col = (col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
		obj.rows[rowIdx].className = col;
	}

	function memoDayList() {
		var obj = null;
		var cnt = 0;
		var node = null, nodes = null, childs = null, attr = null;

		obj = document.getElementById("TodayCaption");
		if (obj != null) obj.innerHTML = theYear + msgYear + (theMonth + 1) + msgMonth + theDay + msgDay;
		obj = document.getElementById("tabsList");
		if (obj == null) return false;

		// clean table
		rowIdx = 2;
		cnt = parseInt(obj.rows.length) - 1;
		for (var i = 2; i < cnt; i++) {
			obj.deleteRow(2);
		}

		col = "cssTrOdd";
		nodes = xmlVars.getElementsByTagName("personal");
		if ((nodes != null) && (nodes.length > 0)) {
			parseMemo(nodes[0], obj, "Personal");
		}
		col = "cssTrOdd";
		nodes = xmlVars.getElementsByTagName("course");
		if ((nodes != null) && (nodes.length > 0)) {
			parseMemo(nodes[0], obj, "Course");
		}
		col = "cssTrOdd";
		nodes = xmlVars.getElementsByTagName("class");
		if ((nodes != null) && (nodes.length > 0)) {
			parseMemo(nodes[0], obj, "Class");
		}
		col = "cssTrOdd";
		nodes = xmlVars.getElementsByTagName("school");
		if ((nodes != null) && (nodes.length > 0)) {
			parseMemo(nodes[0], obj, "School");
		}
	}

	function chgYearSel() {
		var obj = document.getElementById("listYear");
		var cnt = 0, idx = 0;
		if (obj == null) return false;
		cnt = obj.options.length;
		idx = theYear - 3;
		for (var i = 0; i < cnt; i++, idx++) {
			obj.options[i].text = idx;
			obj.options[i].value = parseInt(idx);
		}
		obj.selectedIndex = 3;
	}

	function chgMonthSel() {
		var obj = document.getElementById("listMonth");
		if (obj == null) return false;
		obj.selectedIndex = theMonth;
	}

	function chgYear(val) {
		theYear = val;
		chgYearSel(val);
		do_func("month", 0);
	}

	/**
	 * Chang Month
	 * @pram integer val : month  value 0 ~ 11
	 **/
	function chgMonth(val) {
		theMonth = parseInt(val) - 1;
		do_func("month", 0);
	}

	/**
	 * Goto Today
	 **/
	function Today() {
		theYear = orgYear;
		theMonth = orgMonth;
		theDay = orgDay;
		chgYearSel();
		chgMonthSel();
		do_func("month", 0);
		do_func("day", 0);
	}
////////////////////////////////////////////////////////////////////////////
// load your memo
	var xmlDoc = null;
	var xmlHttp = null;
	var xmlVars = null;
	var xmlSetting = null;

	function resetEditFm() {
		var obj = document.getElementById("editFm");
		if (obj == null) return false;
		obj.idx.value = "";
		obj.bhour.selectedIndex = 0;
		obj.bminute.selectedIndex = 0;
		obj.ehour.selectedIndex = 0;
		obj.eminute.selectedIndex = 0;
		obj.subject.value = "";
		obj.content.value = "";
		obj.ishtml.checked = false;
		obj.repeat_choice[0].checked = true;
		//obj.alertType.selectedIndex = 0;
		obj.alertTypeLogin.checked = false;
		obj.alertTypeEmail.checked = false;
		obj.before.selectedIndex = 0;
		loopLimit(0);
	}

	function do_setting(act) {
		var obj=null;
		var also_show='';
		var txt  = "<manifest><ticket>" + ticket + "</ticket>";
		txt += "<action>" + act + "</action>";

		switch(act) {
			case "set_load":
				txt += "<calEnv>" + env + "</calEnv>";
				txt += "</manifest>";
				xmlSetting.loadXML(txt);
				xmlHttp.open("POST", "/learn/calendar/cale_memo.php", false);
				xmlHttp.send(xmlSetting);
				xmlDoc.loadXML(xmlHttp.responseText);
				ticket = getNodeValue(xmlDoc, "ticket");
				also_show = getNodeValue(xmlDoc, "also_show");
				 	//alert('txt 392='+txt + "\n\nresponse:" + xmlHttp.responseText + "\n\nalso_show:" + also_show);
				break;
			case "set_save":
				/*
				var showArr = new Array();
				if(obj.show_course.checked) showArr[showArr.length] = "course";
				if(obj.show_class.checked)  showArr[showArr.length] = "class";
				if(obj.show_school.checked) showArr[showArr.length] = "school";
				also_show = showArr.join(",");
				txt += "<also_show>" + also_show + "</also_show></manifest>";
				xmlSetting.loadXML(txt);
				xmlHttp.open("POST", "/learn/calendar/cale_memo.php", false);
				xmlHttp.send(xmlSetting);
				xmlDoc.loadXML(xmlHttp.responseText);
				ticket = getNodeValue(xmlDoc, "ticket");
				alert('request:\n'+txt+'\n\nresponse:\n'+xmlHttp.responseText);
				val = getNodeValue(xmlVars, "status");
				alert('val 408='+val);
				alert(msgSetSaved);
				do_func("month", 0);
				do_func("day", 0);
				do_func("month", 0);
				do_func("day", 0);
				*/
				break;
		}
	}

	function parseTimeInt(s) { if(s.length==2 && s.charAt(0)=='0') return parseInt(s.charAt(1)); else return parseInt(s); }

	function DelMemo(sure) {
		if(sure) {
			frm = document.getElementById("delFm");
			delete_choice = (frm.delete_choice[0].checked)?frm.delete_choice[0].value:frm.delete_choice[1].value;

			txt  = "<manifest><ticket>" + ticket + "</ticket>";
			txt += "<action>delete</action>";
			txt += "<calEnv>" + env + "</calEnv>";
			txt += "<idx>" + frm.idx.value + "</idx>";
			txt += "<type>" + delete_choice + "</type>";
			txt += "</manifest>";
			xmlVars.loadXML(txt);
			xmlHttp.open("POST", "/learn/calendar/cale_memo.php", false);
			xmlHttp.send(xmlVars);

			xmlVars.loadXML(xmlHttp.responseText);
			ticket = getNodeValue(xmlVars, "ticket");
			val = getNodeValue(xmlVars, "status");
			if (val > 0)
				alert(msgDelFail);
			else
				alert(msgDelSucc);

	//alert("DelMemo:" + txt + "\nResponse:" + xmlHttp.responseText);
			displayDelMsgBox(false);
			do_func("month",0);
			do_func("day",0);
			obj = document.getElementById("day" + theDay);
			if (obj != null) chgTdBg(obj);
		} else
			displayDelMsgBox(false);
	}

	function do_func(act, idx) {
		var obj = null;
		var txt = "";
		var node = null, nodes = null, childs = null, attr = null;
		var ary = null, nDate1 = null, nDate2 = null;
		var cnt = 0, val = 0;

		switch (act) {
			case "month":
			case "day" :
				txt  = "<manifest><ticket>" + ticket + "</ticket>";
				txt += "<action>" + act + "</action>";
				txt += "<calEnv>" + env + "</calEnv>";
				txt += "<year>" + theYear + "</year>";
				txt += "<month>" + theMonth + "</month>";
				txt += "<day>" + theDay + "</day></manifest>";

				xmlVars.loadXML(txt);

				xmlHttp.open("POST", "/learn/calendar/cale_memo.php", false);
				xmlHttp.send(xmlVars);

				if (act == "month") {
					xmlDoc.loadXML(xmlHttp.responseText);
					ticket = getNodeValue(xmlDoc, "ticket");
				  //    alert('do_func 454(\'' + act + '\'):request:\n' + txt + '\nresponse:\n' + xmlHttp.responseText);
					calendar();
				} else if (act == "day") {
					xmlVars.loadXML(xmlHttp.responseText);
					ticket = getNodeValue(xmlVars, "ticket");
					memoDayList();
					fixTabs();
					//  alert('do_func(\'day\') 459:response:\n' + xmlHttp.responseText);
				}
				break;

			case "add" :
				resetEditFm();

/** Add in Bug.569 **/
				chk_email_remind();
/** End **/

				obj = document.getElementById("editTitle1");
				if (obj != null) obj.innerHTML = msgAdd;
				obj = document.getElementById("editTitle2");
				if (obj != null) obj.innerHTML = theYear + msgYear + (theMonth + 1) + msgMonth + theDay + msgDay + '<br />' + msgMemoAdd;
				displayEditMemo(true);
				break;

			case "edit" :
				resetEditFm();

/** Add in Bug.569 **/
				chk_email_remind();
/** End **/

				obj = document.getElementById("editTitle1");
				if (obj != null) obj.innerHTML = msgModify;
				obj = document.getElementById("editTitle2");
				if (obj != null) obj.innerHTML = theYear + msgYear + (theMonth + 1) + msgMonth + theDay + msgDay + '<br />' + msgMemoEdit;
				obj = document.getElementById("editFm");
				if (obj == null) return false;

				nodes = xmlVars.getElementsByTagName(editable);
				if ((nodes == null) || (nodes.length <= 0))
						return false;

				node = nodes[0];

				nodes = node.getElementsByTagName("memo");
				if ((nodes == null) || (nodes.length <= 0)) return false;
				cnt = nodes.length;
				for (var i = 0; i < cnt; i++) {
					attr = nodes[i].getAttribute("id");
					if (parseInt(attr) == parseInt(idx)) {
						obj.idx.value = parseInt(idx);
						childs = nodes[i].childNodes;
						cnt = childs.length;
						for (var j = 0; j < cnt; j++) {
							if (childs[j].nodeType != 1) continue;
							switch (childs[j].nodeName) {
								case "time_begin" :
									if (childs[j].hasChildNodes()) {
										ary = childs[j].firstChild.data.split(":");
										obj.bhour.selectedIndex = parseTimeInt(ary[0]) + 1;
										if (parseInt(ary[1] / 5) <= obj.bminute.options.length) obj.bminute.selectedIndex = parseInt(ary[1] / 5) + 1;
									}
									break;
								case "time_end" :
									if (childs[j].hasChildNodes()) {
										ary = childs[j].firstChild.data.split(":");
										obj.ehour.selectedIndex = parseTimeInt(ary[0]) + 1;
										if (parseInt(ary[1] / 5) <= obj.eminute.options.length) obj.eminute.selectedIndex = parseInt(ary[1] / 5) + 1;
									}
									break;
								case "repeat":
									if (childs[j].hasChildNodes()) {
										rpt = childs[j].firstChild.data;
										if(rpt=='none')
											obj.repeat_choice[0].checked=true;
										else {
											obj.repeat_choice[1].checked=true;
											obj.repeat_type.value=rpt;
										}
									}
									break;
								case "repeat_end":
									if (childs[j].hasChildNodes()) {
										ary = childs[j].firstChild.data.split("-");
										baseYear = parseInt(obj.repeatEndYear.options[0].value);
										obj.repeatEndYear.selectedIndex  = Math.max(-1, parseTimeInt(ary[0])-baseYear);
										obj.repeatEndMonth.selectedIndex = Math.max(-1, parseTimeInt(ary[1])-1);
										obj.repeatEndDay.selectedIndex   = Math.max(-1, parseTimeInt(ary[2])-1);
									}
									break;
								case "subject" :
									obj.subject.value = (childs[j].hasChildNodes()) ? childs[j].firstChild.data : "";
									break;
								case "content" :
									obj.content.value = (childs[j].hasChildNodes()) ? childs[j].firstChild.data : "";
									attr = childs[j].getAttribute("type");
									if ((attr != null) && (attr == "html")) obj.ishtml.checked = true;
									break;
								case "alert_type" :
									if (childs[j].hasChildNodes()) {
										str = childs[j].firstChild.data;
										if (str.indexOf("login")>-1)
											obj.alertTypeLogin.checked = true;
										if (str.indexOf("email")>-1)
											obj.alertTypeEmail.checked = true;
									}
									break;
								case "alert_before" : // need modify
									if (childs[j].hasChildNodes()) {
										val = parseInt(childs[j].firstChild.data); //.split("-");
										obj.before.selectedIndex = val;
										/*
										ary[1] = parseInt(ary[1]) - 1;
										nDate2 = new Date(ary[0], ary[1], ary[2]);
										nDate1 = new Date(theYear, theMonth, theDay);
										val = parseInt((nDate1 - nDate2) / 86400000);
										if (val <= 7) {
											obj.before.selectedIndex = val;
										} else if (val == 14) {
											obj.before.selectedIndex = 8;
										} else if (val == 21) {
											obj.before.selectedIndex = 9;
										} else if (val == 28) {
											obj.before.selectedIndex = 10;
										}
										*/
									}
									break;
								case "access" :
									if (obj.access)
									{
										if (childs[j].hasChildNodes()) {
											val = childs[j].firstChild.data;
											var nd = document.getElementById("access_" + val);
											if (nd != null)
											{
												nd.checked = true;
												nd.click();
											}
										}
									}
									break;
							}
						}
						break;
					}
				}

				displayEditMemo(true);
				break;

			case "save" :
				obj = document.getElementById("editFm");
				if (obj == null) {
					alert("Save fail!");
					return false;
				}

/** Add in Bug.569 **/
				if (!chk_repeat()){
					alert(msgErrRep);
					return false;
				}
/** End **/

				if (obj.subject.value.length <= 0) {
					alert(msgSubject);
					return false;
				}
				
				if (obj.bhour.value != -1 && obj.bminute.value != -1 &&
					obj.ehour.value != -1 && obj.eminute.value != -1 &&
					( (parseInt(obj.ehour.value) < parseInt(obj.bhour.value)) || 
					  (parseInt(obj.ehour.value) == parseInt(obj.bhour.value) && parseInt(obj.eminute.value) < parseInt(obj.bminute.value)) 
					)
				   )
				{
					alert(msgTimeError);
					return false;
				}
				
				repeat = 'none';
				if(obj.repeat_choice[1].checked)
					repeat = obj.repeat_type.value;

				alertType = '';
				if(obj.alertTypeLogin.checked)
					alertType = 'login';
				if(obj.alertTypeEmail.checked) {
					if(alertType == '')
						alertType += "email";
					else
						alertType += ",email";
				}
				displayEditMemo(false);

				txt  = "<manifest><ticket>" + ticket + "</ticket>";
				txt += "<action>" + act + "</action>";
				txt += "<calEnv>" + env + "</calEnv>";
				txt += "<year>" + theYear + "</year>";
				txt += "<month>" + theMonth + "</month>";
				txt += "<day>" + theDay + "</day>";
				txt += "<idx>" + obj.idx.value + "</idx>";
				txt += '<time_begin>' + obj.bhour.value + ":" + obj.bminute.value + '</time_begin>';
				txt += '<time_end>' + obj.ehour.value + ":" + obj.eminute.value + '</time_end>';
				txt += '<repeat>' + repeat + '</repeat>';
				txt += '<repeat_endY>' + obj.repeatEndYear.value + '</repeat_endY>';
				txt += '<repeat_endM>' + obj.repeatEndMonth.value + '</repeat_endM>';
				txt += '<repeat_endD>' + obj.repeatEndDay.value + '</repeat_endD>';
				txt += '<subject>' + htmlspecialchars(obj.subject.value) + '</subject>';
				txt += '<alert_type>' + alertType + '</alert_type>';
				txt += '<alert_before>' + obj.before.value + '</alert_before>';
				txt += '<content type="' + (obj.ishtml.checked ? 'html' : 'text') +  '">';
				txt += htmlspecialchars(obj.content.value) + ' </content>';
				if (obj.access)
				{
					for (var i = 0, c = obj.access.length; i < c; i++)
					{
						if (!obj.access[i].checked) continue;
						txt += '<access>' + obj.access[i].value + '</access>';
					}
					txt += '<access_pw>' + obj.access_pw.value + '</access_pw>';
				}
				txt += "</manifest>";

				xmlVars.loadXML(txt);
				xmlHttp.open("POST", "/learn/calendar/cale_memo.php", false);
				xmlHttp.send(xmlVars);

				xmlVars.loadXML(xmlHttp.responseText);
				ticket = getNodeValue(xmlVars, "ticket");
				val = getNodeValue(xmlVars, "status");

				//alert('do_func(\'save\') request:\n' + txt + '\nresponse:\n' + xmlHttp.responseText);

				switch (parseInt(val)) {
					case 1 : alert(msgAddSucc); break;
					case 2 : alert(msgAddFail); break;
					case 3 : alert(msgUpdSucc); break;
					case 4 : alert(msgUpdFail); break;
					case 5 : alert(getNodeValue(xmlVars, "env")); break;
				}
				do_func('month', 0);
				do_func('day', 0);
				obj = document.getElementById("day" + theDay);
				if (obj != null) chgTdBg(obj);
				break;

			case "delete" :
				nodes = xmlVars.getElementsByTagName(editable);
				if ((nodes == null) || (nodes.length <= 0)) {
				//alert('in do_func("delete"): cann\'t find '+editable);
						return false;
				}

				node = nodes[0];

				nodes = node.getElementsByTagName("memo");
				if ((nodes == null) || (nodes.length <= 0)) {
				//alert('in do_func("delete"): cann\'t find nodes in "memo"');
					return false;
				}
				cnt = nodes.length;
				repeat= '';
				for(i=0;i<cnt;i++) {
					attr = nodes[i].getAttribute("id");
					if(parseInt(attr) == parseInt(idx)) {
						node = nodes[i].getElementsByTagName('repeat');
						if(node ==null || node.length<=0) return false;
						repeat = node[0].firstChild.data;
						break;
					}
				}
				//alert('in do_func("delete")2: repeat='+repeat);
				frm = document.getElementById("delFm");
				frm.idx.value = idx;	// 將編號放到 form 的 idx input 中, 使 DelMemo() 可以取得編號

				if(repeat=='day' || repeat=='week' || repeat=='month') {	// 是週期事件
					//alert('displayDelMsgBox(true)');
					displayDelMsgBox(true);
				} else {
					if (!confirm(msgSureDel))
						return false;
					else
						DelMemo(true);
				}

				//alert('do_func(\'delete\') request:\n' + txt + '\nresponse:\n' + xmlHttp.responseText);
				break;

			case "set_load":
			case "set_save":
				do_setting(act);
				break;
			case "adv_func":

				displayAdv(true);

				break;
		}
	}
////////////////////////////////////////////////////////////////////////////

	/**
	 * Dock Layer
	 * @pram boolean val :
	 *               true  : dock
	 *               false : undock
	 * @return none
	 **/

	var isDock = false;
	function dockMemo(val) {
		var obj = document.getElementById("memoTable");
		var obj1 = document.getElementById("flag1");
		var obj2 = document.getElementById("flag2");
		var tabs1 = document.getElementById("TitleID1");
		var tabs2 = document.getElementById("TitleID2");
		var ImgL2 = document.getElementById("ImgL2");
		var ImgR2 = document.getElementById("ImgR2");
		var tabsMonth = document.getElementById("tabsMonth");
		var tabsHelp = document.getElementById('tabsTitle').rows[0].getElementsByTagName('a');
		
		if ((obj == null) || (tabs1 == null) || (tabs2 == null)) return false;
		if (val) {
			//tabsMouseEvent(tabs2, 1);
			tabsMouseEvent(tabs2, 2);
			if (obj1 != null) obj1.style.display = "none";
			if (obj2 != null) obj2.style.display = "block";
			if (tabsHelp && tabsHelp[0]) tabsHelp[0].style.display = 'none';
			obj.style.left = "10px";
			ImgL2.style.visibility = "visible";
			tabs2.style.visibility = "visible";
			ImgR2.style.visibility = "visible";
			tabsMonth.style.display = "none";
			tabsMouseEvent(tabs1, 0);
		} else {
			tabsMouseEvent(tabs1, 1);
			//tabsMouseEvent(tabs1, 2);
			if (obj1 != null) obj1.style.display = "block";
			if (obj2 != null) obj2.style.display = "none";
			if (tabsHelp && tabsHelp[0]) tabsHelp[0].style.display = 'block';
			obj.style.left = "320px";
			tabsMouseEvent(tabs2, 2);
			ImgL2.style.visibility = "hidden";
			tabs2.style.visibility = "hidden";
			ImgR2.style.visibility = "hidden";
			tabsMonth.style.display = "block";
		}
		isDock = val;
	}

	/**
	 * Dock Layer need lib
	 * @pram boolean val :
	 *               true  : show
	 *               false : hidden
	 * @return none
	 **/
	function displayMemo(val) {
		var obj = document.getElementById("memoTable");
		if ((!isDock) || (obj == null)) return false;
		obj.style.visibility = (val) ? "visible" : "hidden";

		obj = document.getElementById("flag1");
		if (obj != null) {
			obj.style.display = (val) ? "none" : "block";
		}
		
		// 如果是dock狀態下,只顯示一個Help
		var tabsHelp = document.getElementById('tabsTitle').rows[0].getElementsByTagName('a');
		if (isDock) 
			if (tabsHelp && tabsHelp[0]) tabsHelp[0].style.display = val ? 'none' : 'block';				
	}

	/**
	 * show or hidden Memo Edit
	 * @pram boolean val :
	 *               true  : show
	 *               false : hidden
	 * @return none
	 **/
	function displayEditMemo(val) {
		var obj = document.getElementById("memoEdit");
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
		} else {
			obj.style.visibility = "hidden";
		}

/** Add in Bug.569 **/
		remindType(val);
/** End **/
	}
	/**
	 * show or hidden Memo DelMessageBox
	 * @pram boolean val :
	 *               true  : show
	 *               false : hidden
	 * @return none
	 **/
	function displayDelMsgBox(val) {
		var obj = document.getElementById("memoCycDel");
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
		} else {
			obj.style.visibility = "hidden";
		}
	}

	function CalendarImported() {
		do_func('month',0);
		do_func('day',0);
	}

	/**
	 * show or hidden advance setting
	 * @pram boolean val :
	 *               true  : show
	 *               false : hidden
	 * @return none
	 **/
	var CalAdvWin = '';
	function displayAdv(val) {
		if (val){
			var wL = (screen.width-600)/2;
	  		var wT = (screen.height-480)/2;
	  		var CalAdvWin = window.open('/learn/calendar/calender_adv.php?calEnv='+env,'cal_advset',"width=400,height=300,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,scrollbars=yes");
	  		CalAdvWin.moveTo(wL, wT);	// 將視窗居中
		}
	}

	function loopLimit(arg){
		val = 0;
		sobj = document.getElementById("before");
		if (typeof(sobj)!='object' || sobj==null) return false;
		sIdx = sobj.selectedIndex;
		robj = document.getElementById("repeat_type");
		if (typeof(robj)!='object' || robj==null) return false;
		if (robj.options[robj.selectedIndex].value == 'day' && parseInt(arg) == 1){
			val = 1;
		}else{
			val = 8;
		}
		// 8=>1 or 1=>8 just need rebuild select object
		if ((sobj.length == 8 && val == 1) || (sobj.length == 1 && val == 8)){
			for (lp = sobj.length-1; lp >= 0; lp--) {
				sobj.options[lp] = null;
			}
			for (lp = 0; lp < val; lp++) {
				sobj.options[lp] = new Option(beforeAry[lp], lp);
				if (parseInt(lp) == parseInt(sIdx)) sobj.options[lp].selected = true;
			}
		}
	}

	function remindType(arg){
		eobj = document.getElementById("email_remind");
		if (typeof(eobj)!='object' || eobj==null) return false;

		var cnt = 0;
		obj = document.getElementById("alertTypeLogin");
		if (typeof(obj)=='object' || obj!=null) {
			if (obj.checked) cnt++;
		}
		obj = document.getElementById("alertTypeEmail");
		if (typeof(obj)=='object' || obj!=null) {
			if (obj.checked) cnt++;
		}

		if (parseInt(cnt) > 0 && arg){
			eobj.style.visibility = 'visible';
		}else{
			eobj.style.visibility = 'hidden';
		}
	}

	function chk_email_remind(){
		aobj = document.getElementById("alertTypeEmail");
		if (typeof(aobj)!='object' || aobj==null) return false;
		var tody = '';
		var rwdy = '';
		var dd = new Date();
   	tody = getRightStr('0000'+dd.getYear(), 4)+getRightStr('00'+(dd.getMonth() + 1), 2)+getRightStr('00'+dd.getDate(), 2);
   	rwdy = getRightStr('0000'+theYear, 4)+getRightStr('00'+(theMonth + 1), 2)+getRightStr('00'+theDay, 2);
   	if (parseInt(rwdy) > parseInt(tody)){
   		aobj.disabled = false;
   	} else {
   		aobj.disabled = true;
   	}
	}

	function chk_repeat(){
		robj = document.getElementById("repeat_choice");
		if (typeof(robj)!='object' || robj==null) return true;
		if (robj.checked && robj.value == 0) return true;

		var yy, mm, dd;
		robj = document.getElementById("repeatEndYear");
		if (typeof(robj)!='object' || robj==null) {
			yy = '0000';
		}else{
			val = robj.selectedIndex>=0?robj.options[robj.selectedIndex].value:'';
			yy = getRightStr('0000'+val, 4);
		}
		robj = document.getElementById("repeatEndMonth");
		if (typeof(robj)!='object' || robj==null) {
			mm = '00';
		}else{
			val = robj.selectedIndex>=0?robj.options[robj.selectedIndex].value:'';
			mm = getRightStr('00'+val, 2);
		}
		robj = document.getElementById("repeatEndDay");
		if (typeof(robj)!='object' || robj==null) {
			dd = '00';
		}else{
			val = robj.selectedIndex>=0?robj.options[robj.selectedIndex].value:'';
			dd = getRightStr('00'+val, 2);
		}
		rwdy = getRightStr('0000'+theYear, 4)+getRightStr('00'+(theMonth + 1), 2)+getRightStr('00'+theDay, 2);
		if (parseInt(yy+mm+dd) >= parseInt(rwdy)) return true;
		else return false;
	}

	function getRightStr(vStr,vLen){
		if (vStr == '' || parseInt(vLen) <= 0) return '';
		var sObj = new String(vStr);
		var nStr = '', i;
		for (i = sObj.length-1; i >= sObj.length-vLen; i--){
			nStr = sObj.substr(i,1) + nStr;
		}
		return nStr;
	}

	function newChgCSS(argURL, argWin, argWt, argHt){
		var Wt = (screen.width-argWt)/2;
	  var Ht = (screen.height-argHt)/2;
	  var sWin = window.open(argURL, argWin, "width="+argWt+",height="+argHt+",resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,scrollbars=yes");
	  obj = sWin.document.getElementById('csslnk');
	  if (obj != null) obj.href = '/theme/default/'+env+'/wm.css';
	  sWin.moveTo(Wt, Ht);
	}

	/**
	 * 固定計事列表欄位寬度
	 **/
	function fixTabs(){
		obj = document.getElementById("tabsList");
		if (typeof(obj) != 'object' || obj != null){
			if (typeof(obj.rows[0]) == 'object'){
				if (typeof(obj.rows[0].cells[0]) == 'object') obj.rows[0].cells[0].colSpan = colCnt;
				if (typeof(obj.rows[0].cells[0]) == 'object') obj.rows[0].cells[0].width = 450;
			}
			if (typeof(obj.rows[1]) == 'object'){
				if (typeof(obj.rows[1].cells[0]) == 'object') obj.rows[1].cells[0].width = 20;
				if (typeof(obj.rows[1].cells[1]) == 'object') obj.rows[1].cells[1].width = 20;
				if (typeof(obj.rows[1].cells[2]) == 'object') obj.rows[1].cells[2].width = 100;
				if (typeof(obj.rows[1].cells[3]) == 'object') obj.rows[1].cells[3].width = (calLmt=='N'?260:310);
				if (calLmt == 'N'){
					if (typeof(obj.rows[1].cells[4]) == 'object') obj.rows[1].cells[4].colSpan = 2;
					if (typeof(obj.rows[1].cells[4]) == 'object') obj.rows[1].cells[4].align = 'center';
					if (typeof(obj.rows[1].cells[4]) == 'object') obj.rows[1].cells[4].width = 50;
				}
			}
		}
	}
