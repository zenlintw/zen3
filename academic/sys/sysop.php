<?php
	/**
	 * 管理者設定
	 *
	 * @since   2002/11/08
	 * @author  ShenTing Lin
	 * @version $Id: sysop.php,v 1.1 2009-06-25 09:26:05 edi Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/academic/sys/lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	


	$sysSession->cur_func = '100200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	/**
	 * 安全性檢查
	 *     1. 身份的檢查
	 *     2. 權限的檢查
	 *     3. .....
	 **/

	// 安全與權限檢查

	// 設定車票
	setTicket();

	// 各項排序依據
	$OB = array(
		'uname' => '`username`',   // 帳號
		'sid'   => '`school_id`',  // 學校編號
		'level' => '`level`'       // 等級
		   );

	// 取出操作此功能的管理者的等級
	$level = intval(getAllSchoolTopAdminLevel($sysSession->username));

	if ($level < $sysRoles['administrator']) $level = intval(getAdminLevel($sysSession->username, $sysSession->school_id));
	if ($level <= 0) {
		die($MSG['not_sysop'][$sysSession->lang]);
	}

	$sqls = '';
	// 設定取出管理者的條件
	switch ($level) {
		case $sysRoles['root']:    // 最高管理者
			$sqls = ' 1 ';
			break;

		case $sysRoles['administrator']:    // 進階管理者
			$sqls = ' username != "' . sysRootAccount . '" AND level <= ' . $level ;
			break;

		default:      // case 2048: 一般管理者
			$sqls = ' username != "' . sysRootAccount . '" AND school_id = ' . $sysSession->school_id . ' AND level <= ' . $level;
	}

	// 計算總共有幾筆資料
	list($total_msg) = dbGetStSr('WM_manager', 'count(*) AS total', $sqls, ADODB_FETCH_NUM);

	// 計算總共分幾頁
	$total_page = max(1, ceil($total_msg / $lines));

	// 產生下拉換頁選單
	$all_page = range(0, $total_page);
	$all_page[0] = $MSG['page_all'][$sysSession->lang];

	// 設定下拉換頁選單顯示第幾頁
	$page_no = isset($_POST['page']) ?  max(0, min($_POST['page'], $total_page)) : 1;

	// 取得排序的欄位
	$sb = '';
	$sortby = trim($_POST['sortby']);
	$sb = $OB[$sortby];
	if (empty($sb)) {
		$sortby = 'uname';
		$sb = '`username`';
	}

	// 取得排序的順序是遞增或遞減
	$order = trim($_POST['order']);
	if (empty($order)) $order = 'desc';
	$od = ($order == 'asc') ? 'DESC' : 'ASC';

	// 產生執行的 SQL 指令
	$sqls .= " order by {$sb} {$od} ";
	if (!empty($page_no)) {
		$limit = intval($page_no - 1) * $lines;
		$sqls .= " limit {$limit}, {$lines} ";
	}

	$sysop = array();
	$user = array();

	$RS = dbGetStMr('WM_manager', '*', $sqls, ADODB_FETCH_ASSOC);
	if ($RS) {
		while (!$RS->EOF) {
			$sysop[] = $RS->fields;
			$user[$RS->fields['username']] = $RS->fields['username'];
			$RS->MoveNext();
		}
	}

	$userlist = '\'' . implode('\', \'', $user) . '\'';
	$RS = dbGetStMr('WM_all_account', 'username, first_name, last_name', "username IN ({$userlist})", ADODB_FETCH_ASSOC);
	if ($RS) {
		while (!$RS->EOF) {
            // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
            $realname = checkRealname($RS->fields['first_name'],$RS->fields['last_name']);
            $user[$RS->fields['username']] = $realname;
			$RS->MoveNext();
		}
	}

	$sysRootAccount = sysRootAccount;
	$js = <<< BOF
	var MSG_INPUT_USERNAME = "{$MSG['msg_need_username'][$sysSession->lang]}";
	var MSG_INPUT_IP       = "{$MSG['msg_need_ip'][$sysSession->lang]}";
	var MSG_ADD_SUCCESS    = "{$MSG['msg_add_success'][$sysSession->lang]}";
	var MSG_UPDATE_SUCCESS = "{$MSG['msg_update_success'][$sysSession->lang]}";
	var MSG_SELECT_ADMIN   = "{$MSG['msg_need_select'][$sysSession->lang]}";

	var MSG_SELECT_ALL = "{$MSG['select_all'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['cancel_all'][$sysSession->lang]}";

	var total_page = "{$total_page}";
	var xmlDocs = null, xmlHttp = null, xmlVars = null;

	/**
	 * change page
	 * @param integer n : action type or page number
	 * @return
	 **/
	function go_page(n){
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return '';
		switch(n){
			case -1:	// 第一頁
				obj.page.value = 1;
				break;
			case -2:	// 前一頁
				obj.page.value = parseInt(obj.page.value) - 1;
				if (parseInt(obj.page.value) == 0) obj.page.value = 1;
				break;
			case -3:	// 後一頁
				obj.page.value = parseInt(obj.page.value) + 1;
				break;
			case -4:	// 最末頁
				obj.page.value = parseInt(total_page);
				break;
			default:	// 指定某頁
				obj.page.value = parseInt(n);
				break;
		}
		obj.submit();
	}

	function sortBy(val){
		var ta = new Array('',
					'uname',
					'sid',
					'level'
				);
		var re = /asc/ig;

		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return '';

		if (trim(obj.sortby.value) == ta[val]) {
			obj.order.value = (re.test(obj.order.value)) ? 'desc' : 'asc';
		}
		obj.sortby.value = ta[val];
		obj.submit();
	}

	function layProperty(val) {
		layerAction("tabProperty", val);
	}

	function getAdmin(uname, sid) {
		var txt = "";

		txt  = "<manifest>";
		txt += "<ticket></ticket>";
		txt += "<username>" + uname + "</username>";
		txt += "<sid>" + sid + "</sid>";
		txt += "</manifest>";
		if (!xmlVars.loadXML(txt)) {
			xmlVars = null;
			return false;
		}

		xmlHttp.open("POST", "sysop_get.php", false);
		xmlHttp.send(xmlVars);
		// alert(xmlHttp.responseText);
		if (!xmlDocs.loadXML(xmlHttp.responseText)) {
			return false;
		}
		return true;
	}

	var editMode = false;
	/**
	 * 新增管理者
	 **/
	function addAdmin() {
		var obj = document.getElementById("fmProperty");

		editMode = false;
		layProperty(true);
		obj.reset();
		obj.opnName.style.display = "";
		document.getElementById("lstName").innerHTML = "";
	}

	/**
	 * 修改管理者
	 * @param string  uname : 帳號
	 * @param integer sid   : 學校編號
	 **/
	var orgsid = 0;
	function editAdmin(uname, sid) {
		var obj = document.getElementById("fmProperty");
		var uPermit, uIP;
		var node = null;

		if (!getAdmin(uname, sid)) return false;

		editMode = true;
		orgsid   = sid;
		node = xmlDocs.selectSingleNode('//sysop');
		uPermit = parseInt(getNodeValue(node, 'permit'));
		uIP     = getNodeValue(node, 'limit_ip');

		layProperty(true);
		obj.reset();
		obj.opnName.value = uname;
		obj.opnName.style.display = "none";
		document.getElementById("lstName").innerHTML = uname;
		if (typeof(obj.opnSName) == "object") {
			for (var i = 0; i < obj.opnSName.options.length; i++) {
				if (parseInt(obj.opnSName.options[i].value) == parseInt(sid)) {
					obj.opnSName.selectedIndex = i;
				}
			}
		}
		if ((typeof(obj.opnPermit.length) == "number") && (uPermit < 3)) {
			obj.opnPermit[uPermit].checked = true;
		}
		for (var i = 0; i < obj.opnPermit.length; i++) {
			obj.opnPermit[i].disabled = (uname == "{$sysRootAccount}");
		}
		obj.opnIP.value = uIP;
	}

	/**
	 * 儲存資料
	 **/
	function saveAdmin() {
		var nodes = null;
		var obj = document.getElementById("fmProperty");
		var uname, sid, uPermit, uIP;
		var txt = "";

		uname = obj.opnName.value;
		if (uname == "") {
			alert(MSG_INPUT_USERNAME);
			return false;
		}
		uIP = obj.opnIP.value;
		if (uIP == "") {
			alert(MSG_INPUT_IP);
			return false;
		}
		if (typeof(obj.opnSName) == "object") {
			sid = obj.opnSName.value;
		} else {
			sid = 0;
		}
		if (typeof(obj.opnPermit.length) == "number") {
			for (var i = 0; i < obj.opnPermit.length; i++) {
				if (obj.opnPermit[i].checked) uPermit = obj.opnPermit[i].value;
			}
		} else {
			uPermit = obj.opnPermit.value;
		}

		txt  = "<manifest>";
		txt += "<ticket></ticket>";
		txt += "<mode>" + (editMode ? 'edit' : 'add') + "</mode>";
		txt += "<username>" + uname + "</username>";
		txt += "<sid>" + sid + "</sid>";
		txt += "<osid>" + orgsid + "</osid>";
		txt += "<permit>" + uPermit + "</permit>";
		txt += "<ip>" + uIP + "</ip>";
		txt += "</manifest>";

		if (!xmlVars.loadXML(txt)) {
			xmlVars = null;
			return false;
		}
		xmlHttp.open("POST", "sysop_save.php", false);
		xmlHttp.send(xmlVars);
		// alert(xmlHttp.responseText);
		if (!xmlDocs.loadXML(xmlHttp.responseText)) {
			alert(xmlHttp.responseText);
		} else {
			nodes = xmlDocs.getElementsByTagName("sysop");
			if ((nodes != null) && (nodes.length > 0)) {
				txt = editMode ? MSG_UPDATE_SUCCESS : MSG_ADD_SUCCESS;
				alert(txt);
				location.reload();
				return true;
			}
			layProperty(false);
		}
	}

	function delAdmin() {
		var obj = null, nodes = null, attr = null;
		var cnt = 0;
		obj = document.getElementById("tabAction");
		nodes = obj.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type == "checkbox") && nodes[i].checked && (nodes[i].name != 'ck')) {
				attr = nodes[i].getAttribute("explode");
				if (attr != null) continue;
				cnt++;
			}
		}
		if (cnt == 0) {
			alert(MSG_SELECT_ADMIN);
			return false;
		}
		return true;
	}

	function selUser(val){
		var obj = null,nodes = null;
		var total_num = 0,cnt = 0,attr = null;

		obj = document.getElementById("tabAction");
		nodes = obj.getElementsByTagName("input");

		for (var i = 0; i < nodes.length; i++) {
			attr = nodes[i].getAttribute("exclude");

			if ((nodes[i].type == "checkbox") && (attr == null)) {
				total_num++;
				if (nodes[i].checked) cnt++;
			}
		}

		nowSel = (total_num == cnt);
		document.getElementById('ck').checked = nowSel;

		var btn1 = document.getElementById("btnSel1");
		btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

		var btn_del_obj = document.getElementById('btn_del');
		if(cnt > 0){
			btn_del_obj.disabled = false;
		}else{
			btn_del_obj.disabled = true;
		}
		document.getElementById("tb2").innerHTML = document.getElementById("tb1").innerHTML.replace(/ id="btn\w+"/g, '');
	}

	var nowSel = false;
	function selfunc() {
		var obj  = document.getElementById("ck");
		var btn1 = document.getElementById("btnSel1");

		if (obj != null) {
			nowSel = !nowSel;
			obj.checked = nowSel;
		}
		if (btn1 != null) btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

		select_func('', obj.checked);

		var total_cnt = 0;
		obj = document.getElementById("tabAction");
		var nodes = obj.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			attr = nodes[i].getAttribute("explode");
			if ((nodes[i].type == "checkbox") && nodes[i].checked && (nodes[i].name != 'ck')) {
				total_cnt++;
			}
		}
		var btn_del_obj = document.getElementById('btn_del');
		if(total_cnt > 0){
			btn_del_obj.disabled = false;
		}else{
			btn_del_obj.disabled = true;
		}
		document.getElementById("tb2").innerHTML = document.getElementById("tb1").innerHTML.replace(/ id="btn\w+"/g, '');
	}

	window.onload = function () {
		var obj = null;
		document.getElementById("tb2").innerHTML = document.getElementById("tb1").innerHTML.replace(/ id="btn\w+"/g, '');

		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
	}
BOF;

	// 畫面輸出

    // SHOW_PHONE_UI 常數定義於 /mooc/academic/sys/sysop.php
    if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1) {
        for($i=0, $size=count($sysop); $i<$size; $i++) {
            $sysop[$i]['realname'] = $user[$sysop[$i]['username']];
            $sysop[$i]['levelshow'] = $sopLevel[$sysop[$i]['level']];
            $sysop[$i]['canModify'] = false;
            if ($level > intval($sysop[$i]['level'])) {
                $sysop[$i]['canModify'] = true;
            }
        }

        // assign
        
        $ticket = md5($sysSession->ticket . 'Create' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
        $smarty->assign('ticket', $ticket);
        $smarty->assign('datalist', $sysop);
        $smarty->assign('page_num', $lines);

        $smarty->assign('totalUserCount', $total_msg);
        $smarty->assign('current_page', $page_no);

        $smarty->display('common/tiny_header.tpl');
        $smarty->display('common/site_header.tpl');
        $smarty->display('phone/academic/sys/sysop.tpl');
        $smarty->display('common/tiny_footer.tpl');
        exit;
    }
	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_title'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'adminModify', null,'action="sysop_del.php" method="post" onsubmit="return delAdmin();" style="display: inline;"', false);
			$colspan = ($level == $sysRoles['manager']) ? 7 : 9;
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="tabAction"');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('nowrap="nowrap" colspan="' . $colspan . '" id="tb1"');
						showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
							showXHTML_tr_B('class="cssTrEvn"');
								showXHTML_td_B();
									showXHTML_input('button', 'btnSel1', $MSG['select_all'][$sysSession->lang], '', 'id="btnSel1" onclick="selfunc()" class="cssBtn"');
									echo '&nbsp;' . $MSG['page'][$sysSession->lang];
									showXHTML_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);"');
									showXHTML_input('button', 'fp', $MSG['btn_page_first'][$sysSession->lang],    '', 'onclick="go_page(-1)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
									showXHTML_input('button', 'pp', $MSG['btn_page_previous'][$sysSession->lang], '', 'onclick="go_page(-2)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
									showXHTML_input('button', 'np', $MSG['btn_page_next'][$sysSession->lang],     '', 'onclick="go_page(-3)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
									showXHTML_input('button', 'lp', $MSG['btn_page_last'][$sysSession->lang],     '', 'onclick="go_page(-4)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
								showXHTML_td_E();
								showXHTML_td_B('align="right"');
									showXHTML_input('button', 'bd', $MSG['btn_add_admin'][$sysSession->lang], '', 'class="cssBtn" onclick="addAdmin()"');
									showXHTML_input('submit', 'bd', $MSG['btn_delete'][$sysSession->lang], '', 'id="btn_del" class="cssBtn" disabled');
								showXHTML_td_E();
							showXHTML_tr_E();
						showXHTML_table_E();
					showXHTML_td_E('');
				showXHTML_tr_E('');

				$icon_up = '&nbsp;<img border="0" align="asbmiddle" src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/dude07232001up.gif">';
				$icon_dw = '&nbsp;<img border="0" align="asbmiddle" src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/dude07232001down.gif">';
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td_B('nowrap="nowrap" align="center" title="' . $MSG['select_all_msg'][$sysSession->lang] . '"');
						showXHTML_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc();"');
					showXHTML_td_E('');
					showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_number'][$sysSession->lang]);
					showXHTML_td_B('nowrap="nowrap" align="center" onclick="sortBy(1);" title="' . $MSG['th_alt_username'][$sysSession->lang] . '"');
						echo '<a href="javascript:;" class="cssAnchor" onclick="return false;">';
						echo $MSG['th_username'][$sysSession->lang];
						echo '</a>';
						echo ($sortby == 'uname') ? ($order == 'desc' ? $icon_up : $icon_dw) : '';
					showXHTML_td_E('');
					showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_name'][$sysSession->lang]);
					if ($level >= $sysRoles['administrator']) {
						showXHTML_td_B('nowrap="nowrap" align="center" onclick="sortBy(2);" title="' . $MSG['th_alt_school_id'][$sysSession->lang] . '"');
							echo '<a href="javascript:;" class="cssAnchor" onclick="return false;">';
							echo $MSG['th_school_id'][$sysSession->lang];
							echo '</a>';
							echo ($sortby == 'sid') ? ($order == 'desc' ? $icon_up : $icon_dw) : '';
						showXHTML_td_E('');
						showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_school_name'][$sysSession->lang]);
					}
					showXHTML_td_B('nowrap="nowrap" align="center" onclick="sortBy(3);" title="' . $MSG['th_alt_permit'][$sysSession->lang] . '"');
						echo '<a href="javascript:;" class="cssAnchor" onclick="return false;">';
						echo $MSG['th_permit'][$sysSession->lang];
						echo '</a>';
						echo ($sortby == 'level') ? ($order == 'desc' ? $icon_up : $icon_dw) : '';
					showXHTML_td_E('');
					showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_limit_ip'][$sysSession->lang]);
					showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_action'][$sysSession->lang]);
				showXHTML_tr_E('');

				$sname = getAllSchoolName();
				$idx = ($page_no == 0) ? 0 : intval($page_no - 1) * $lines;
				foreach ($sysop as $val) {
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('nowrap="nowrap" align="center"');

							if ($val['username'] == sysRootAccount) {
								echo '&nbsp;';
							} else if ($level > intval($val['level'])){
								showXHTML_input('checkbox', 'ckUname[]', $val['username'] . ',' . $val['school_id'], '', 'onclick="selUser(this.checked)"');
							}
						showXHTML_td_E('');
						showXHTML_td('nowrap="nowrap" align="center"', ++$idx);
						showXHTML_td_B('nowrap="nowrap"');
							echo $val['username'];
						showXHTML_td_E('');
						showXHTML_td('nowrap="nowrap"', $user[$val['username']]);
						if ($level >= $sysRoles['administrator']) {
							showXHTML_td('nowrap="nowrap" align="center"', $val['school_id']);
							showXHTML_td('nowrap="nowrap"', $sname[$val['school_id']]);
						}
						showXHTML_td('nowrap="nowrap"', $sopLevel[$val['level']]);
						showXHTML_td('', nl2br($val['allow_ip']));
						showXHTML_td_B('align="center"');

							if ($val['username'] == sysRootAccount) {
								echo '&nbsp;';
							} else if ($level > intval($val['level'])){
								showXHTML_input('button', '', $MSG['btn_modify'][$sysSession->lang], '', 'class="cssBtn" onclick="editAdmin(\'' . $val['username'] . '\', ' . $val['school_id'] . ')"');
							}

						showXHTML_td_E();
					showXHTML_tr_E('');
				}

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('nowrap="nowrap" colspan="' . $colspan . '" id="tb2"');
					showXHTML_td_E('');
				showXHTML_tr_E('');

		showXHTML_table_E('');
		showXHTML_tabFrame_E();
		echo '</div>';
		$ary = array();
		$ary[] = array($MSG['tabs_modify'][$sysSession->lang], 'tb2');
		showXHTML_tabFrame_B($ary, 1, 'fmProperty', 'tabProperty', 'style="display:inline;"', true);
			showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				$col = 'class="cssTrOdd"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['th_username'][$sysSession->lang]);
					showXHTML_td_B();
						showXHTML_input('text', 'opnName', '', '', 'class="cssInput" maxlength="32"');
						echo '<span id="lstName"></span>';
					showXHTML_td_E();
					showXHTML_td('', $MSG['th_help_username'][$sysSession->lang]);
				showXHTML_tr_E();
				if ($level >= $sysRoles['administrator']) {
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $MSG['th_school'][$sysSession->lang]);
						showXHTML_td_B();
							showXHTML_input('select', 'opnSName', $sname, '', 'class="cssInput"');
						showXHTML_td_E();
					showXHTML_td('', $MSG['th_help_school'][$sysSession->lang]);
					showXHTML_tr_E();
				}
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['th_permit'][$sysSession->lang]);
					showXHTML_td_B();
						$permit = array($sopLevel[$sysRoles['manager']]);
						if ($level == $sysRoles['administrator']) $permit[] = $sopLevel[$sysRoles['administrator']];
						if ($level == $sysRoles['root']) {
							$permit[] = $sopLevel[$sysRoles['administrator']];
							$permit[] = $sopLevel[$sysRoles['root']];
						}
						showXHTML_input('radio', 'opnPermit', $permit, '', '', '<br />');
					showXHTML_td_E();
					showXHTML_td('', $MSG['th_help_permit'][$sysSession->lang]);
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['th_limit_ip'][$sysSession->lang]);
					showXHTML_td_B();
						showXHTML_input('textarea', 'opnIP', '', '', 'class="cssInput" cols="25" rows="4"');
					showXHTML_td_E();
					showXHTML_td('', $MSG['th_help_limit_ip'][$sysSession->lang]);
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="3" align="center"');
						showXHTML_input('button', '', $MSG['btn_ok'][$sysSession->lang]    , '', 'class="cssBtn" onclick="saveAdmin()"');
						showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="layProperty(false)"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();

		showXHTML_form_B('action="sysop.php" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
			showXHTML_input('hidden', 'sortby', $sortby, '', '');
			showXHTML_input('hidden', 'order', $order, '', '');
			showXHTML_input('hidden', 'page', $page_no, '', '');
		showXHTML_form_E('');

		showXHTML_form_B('action="sysop_del.php" method="post"', 'adminDelete');
			showXHTML_input('hidden', 'username', '', '', '');
			$ticket = md5($sysSession->ticket . $sysSession->school_id . 'delete');
			showXHTML_input('hidden', 'ticket', $ticket, '', '');
		showXHTML_form_E('');
	showXHTML_body_E();
?>
