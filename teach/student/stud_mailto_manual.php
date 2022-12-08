<?php
	/**
     * 目  的 : 立即點名
     *
     * @since   2005/09/28
     * @author  Edi Chen
     * @version $Id: stud_mailto_manual.php,v 1.1 2010/02/24 02:40:31 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
	require_once(sysDocumentRoot . '/teach/student/stud_mailto_lib.php');

	$js = <<< BOF

	// 秀日曆的函數
	function Calendar_setup(ifd, fmt, btn, shtime) {
		Calendar.setup({
			inputField  : ifd,
			ifFormat    : fmt,
			showsTime   : shtime,
			time24      : true,
			button      : btn,
			singleClick : true,
			weekNumbers : false,
			step        : 1
		});
	}

	/* 如果是 Mozilla/Firefox 則加上 outerHTML/innerText 的支援 */
	if (navigator.userAgent.indexOf(' Gecko/') != -1)
	{
		HTMLElement.prototype.__defineSetter__('outerHTML', function(s){
		   var range = this.ownerDocument.createRange();
		   range.setStartBefore(this);
		   var fragment = range.createContextualFragment(s);
		   this.parentNode.replaceChild(fragment, this);
		});

		HTMLElement.prototype.__defineGetter__('outerHTML', function() {
		   return new XMLSerializer().serializeToString(this);
		});

		HTMLElement.prototype.__defineGetter__('innerText', function() {
		  return this.innerHTML.replace(/<[^>]+>/g, '');
		});
	}

	// ////////////////////////////////////////////////////////////////////////////
	function buildList(val) {

		var xslDoc = XmlDocument.create();
        try
        {
            xslDoc.async = false;
            xslDoc.load('pick_user.php');
        }
        catch(e)
        {
            // #47160 Chrome 瀏覽器不支援load方法載入XML檔案
            var xhr = new XMLHttpRequest();
            xhr.open("GET", 'pick_user.php', false);
            xhr.send(null);
            xslDoc = xhr.responseXML.documentElement;
        }        

		var obj = document.getElementById("resList");
		if (obj != null) obj.style.display = "block";
		obj = document.getElementById("resTable");
		if (obj == null) return false;
		obj.outerHTML = val.transformNode(xslDoc);

		synBtns('resList');   // 同步按鈕
		chgCheckbox('resList');
		document.getElementById('start_pick').disabled = false;
	}

	function getCondition() {
		var txt = "";
		var obj = null, obj1 = null, obj2 = null, obj3 = null, obj4 = null;
		var attr = null;

		obj = document.getElementById("role");
		if (obj == null) return false;

		txt  = "<manifest>";
		txt += "<ticket></ticket>";
		// 對象 (roles)
		txt += "<roles>";
		txt += "<role>" + obj.value + "</role>";
		txt += "</roles>";

		// 群組 (groups)
		txt += "<groups>";
		obj = document.getElementById("ckGroup");
		if ((obj != null) && (obj.checked)) {
			obj1 = document.getElementById("mtTeam");
			obj2 = document.getElementById("mtGroup");
			if ((obj1 != null) && (obj2 != null)) {
				txt += '<group team="' + obj1.value + '" group="' + obj2.value + '"></group>';
			}
		}
		txt += "</groups>";

		// 條件 (filters)
		txt += "<filters>";
		obj = document.getElementById("ckFilter");
		if ((obj != null) && (obj.checked)) {
			obj1 = document.getElementById("mtType");
			obj2 = document.getElementById("mtFilter");
			obj3 = document.getElementById("mtOP");
			obj4 = document.getElementById("mtVal");
			if ((obj1 != null) && (obj2 != null) && (obj3 != null) && (obj4 != null)) {
				attr = obj4.getAttribute("myattr");
				if ((attr != null) && (attr == "integer")) {
					// 檢查格式 (check format)
					if (obj4.value == "") {
						alert(MSG_NEED_DATA);
						obj4.focus();
						return false;
					}
					if (isNaN(parseInt(obj4.value))) {
						alert(MSG_FORMAT_ERROR);
						obj4.focus();
						return false;
					}
				}
				txt += '<filter type="' + obj1.value + '" filter="' + obj2.value + '" op="' + obj3.value + '">' + obj4.value + '</filter>';
			}
		}
		txt += "</filters>";

		txt += "</manifest>";
		res = xmlVars.loadXML(txt);
		if (!res) {
			return false;
		}

		try {
			xmlHttp.open("POST", "/teach/student/stud_mailto_condition.php", false);
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
		// Bug#1494 by Small 2006/11/3
		var start_pick = document.getElementById('start_pick');
		start_pick.disabled= true;
		buildList(xmlVars);
	}

// ////////////////////////////////////////////////////////////////////////////
	function chkdata() {
        /*
         * 修改Bug 1142 的 JS error，obj1應該是要抓tabFrame的名稱
         * 該名稱設定在stud_mailto.php中(fmMailto)
         * 而不是直接抓form的名稱
         * 否則會造成"obj1.role.value是null或不是一個物件"的錯誤
         * 修改者: Small on 2006/04/13
         */
		var obj1 = document.getElementById("fmMailto");
		// var obj1 = document.getElementById("fmAction");
        var obj2 = document.getElementById("fmResult");
		if ((obj1 == null) || (obj2 == null))
            return false;
        else{
            // alert (obj1.role.value);
            obj2.rs_role.value     = obj1.role.value;
		    obj2.rs_ckGroup.value  = obj1.ckGroup.checked;
		    obj2.rs_mtTeam.value   = obj1.mtTeam.value;
		    obj2.rs_mtGroup.value  = obj1.mtGroup.value;
		    obj2.rs_ckFilter.value = obj1.ckFilter.checked;
		    obj2.rs_mtType.value   = obj1.mtType.value;
		    obj2.rs_mtFilter.value = obj1.mtFilter.value;
		    obj2.rs_mtOP.value     = obj1.mtOP.value;
		    obj2.rs_mtVal.value    = obj1.mtVal.value;
		    return true;
        }

	}

	window.onload = function () {
		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
		if ((typeof(xmlTeam) != "object") || (xmlTeam == null)) xmlTeam = XmlDocument.create();

		getGroup();
		getFilter('login');
		selfunc('resList');
	};
BOF;
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', 'stud_mailto.js');
	showXHTML_script('inline', $js);
	$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
	$calendar->load_files();

	echo '<form id="fmAction" name="fmAction" style="display: inline;">';
	showXHTML_table_B('width="1000" align="center" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="tabsManual"');
		// 對象
		$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
		showXHTML_tr_B($col);
			showXHTML_td('width="80" align="right" valign="top"', $MSG['target_object'][$sysSession->lang]);
			showXHTML_td_B('width="20"');
				// showXHTML_input('checkbox', 'ckRole', '1', '', '');
				echo '&nbsp;';
			showXHTML_td_E();
			showXHTML_td_B('width="400"');
				showXHTML_input('select', 'role', $mt_roles, 'student', 'id="role" class="cssInput"');
			showXHTML_td_E();
			showXHTML_td('valign="top"', $MSG['pick_target_role'][$sysSession->lang]);
		showXHTML_tr_E();
		// 組次
		$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
		showXHTML_tr_B($col);
			showXHTML_td('align="right" valign="top"', $MSG['target_group'][$sysSession->lang]);
			showXHTML_td_B('width="20"');
				showXHTML_input('checkbox', 'ckGroup', '1', '', 'id="ckGroup"');
			showXHTML_td_E();
			showXHTML_td_B();
				echo '<span id="spanTeam"></span>&nbsp;<span id="spanGroup"></span>';
			showXHTML_td_E();
			showXHTML_td('valign="top"', $MSG['pick_target_group'][$sysSession->lang]);
		showXHTML_tr_E();
		// 篩選條件
		$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
		showXHTML_tr_B($col);
			showXHTML_td('align="right" valign="top"', $MSG['filter_condition'][$sysSession->lang]);
			showXHTML_td_B('width="20"');
				showXHTML_input('checkbox', 'ckFilter', '1', '', 'id="ckFilter"');
			showXHTML_td_E();
			showXHTML_td_B();
				showXHTML_input('select', 'mtType', $mt_type, 'login', 'id="mtType" class="cssInput" onchange="getFilter(this.value)"');
				echo '<span id="spanType">&nbsp;</span>&nbsp;<span id="spanOP">&nbsp;</span>';
			showXHTML_td_E();
			showXHTML_td('valign="top"', $MSG['call_hint2'][$sysSession->lang]);
		showXHTML_tr_E();
		// 按鈕
		$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
		showXHTML_tr_B($col);
			showXHTML_td_B('align="center" colspan="4"');
				showXHTML_input('button', 'start_pick', $MSG['start_pick'][$sysSession->lang], '', 'id="start_pick" class="cssBtn" onclick="getCondition();"');
			showXHTML_td_E();
		showXHTML_tr_E();
	showXHTML_table_E();
	echo '</form>';
	showXHTML_td_E();
	showXHTML_tr_E();
	showXHTML_tr_B();
	showXHTML_td_B();

	echo '<div id="resList" align="center" style="display:none;">';
		$ary = array();
		$ary[] = array($MSG['tabs_filter_condition_result'][$sysSession->lang], 'tabsResult');
                $display_css['table'] = 'width="1000"';
		showXHTML_tabFrame_B($ary, 1, 'fmResult', '', 'action="stud_mailto_mail.php" method="post" onsubmit="return chkdata();" style="display: inline;"', false, null, $display_css);
			showXHTML_input('hidden', 'rs_role'    , '', '', '');
			showXHTML_input('hidden', 'rs_ckGroup' , '', '', '');
			showXHTML_input('hidden', 'rs_mtTeam'  , '', '', '');
			showXHTML_input('hidden', 'rs_mtGroup' , '', '', '');
			showXHTML_input('hidden', 'rs_ckFilter', '', '', '');
			showXHTML_input('hidden', 'rs_mtType'  , '', '', '');
			showXHTML_input('hidden', 'rs_mtFilter', '', '', '');
			showXHTML_input('hidden', 'rs_mtOP'    , '', '', '');
			showXHTML_input('hidden', 'rs_mtVal'   , '', '', '');
			showXHTML_table_B('width="1000" align="center" border="0" cellspacing="1" cellpadding="3" id="resTable" class="cssTable"');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('align="left" colspan="3"');
						showXHTML_input('button', 'btnSel1', $MSG['select_all'][$sysSession->lang], '', 'id="btnSel1" onclick="selfunc(\'resList\')" class="cssBtn"');
					showXHTML_td_E();
					showXHTML_td_B('align="left" colspan="6"');
						showXHTML_input('submit', 'btnSend1', $MSG['rs_btn_send_mail'][$sysSession->lang], '', 'id="btnSend1" class="cssBtn" disabled="disabled"');
					showXHTML_td_E();
				showXHTML_tr_E();
				// 標題
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td_B('align="center" title="' . $MSG['rs_th_checkbox_title'][$sysSession->lang] . '"');
						showXHTML_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" checked="checked" onclick="selfunc(\'resList\')"');
						echo $MSG['rs_th_checkbox'][$sysSession->lang];
					showXHTML_td_E();
					showXHTML_td('width="30" align="center" title="' . $MSG['rs_th_no_title'][$sysSession->lang] . '"'        , $MSG['rs_th_no'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['rs_th_name_title'][$sysSession->lang] . '"'      , $MSG['rs_th_name'][$sysSession->lang]);
					showXHTML_td('width="120" align="center" title="' . $MSG['rs_th_last_login_title'][$sysSession->lang] . '"', $MSG['rs_th_last_login'][$sysSession->lang]);
					showXHTML_td('width="120" align="center" title="' . $MSG['rs_th_last_study_title'][$sysSession->lang] . '"', $MSG['rs_th_last_study'][$sysSession->lang]);
					showXHTML_td('width="66" align="center" title="' . $MSG['rs_th_login_title'][$sysSession->lang] . '"'     , $MSG['rs_th_login'][$sysSession->lang]);
					showXHTML_td('width="66" align="center" title="' . $MSG['rs_th_study_title'][$sysSession->lang] . '"'     , $MSG['rs_th_study'][$sysSession->lang]);
					showXHTML_td('width="66" align="center" title="' . $MSG['rs_th_post_title'][$sysSession->lang] . '"'      , $MSG['rs_th_post'][$sysSession->lang]);
					showXHTML_td('width="66" align="center" title="' . $MSG['rs_th_chat_title'][$sysSession->lang] . '"'      , $MSG['rs_th_chat'][$sysSession->lang]);
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
	echo '</div>';
?>
