<?php
	/**
	 * 活動看板列表
	 *
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lang/app_course_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	// 檢測是否有資料表
    if (is_file(sysDocumentRoot. '/academic/App_course/activity_list.sql')) {
    	$table = 'CO_activities';
    	// 切換資料庫
    	$sysConn->Execute('USE '.sysDBprefix.$sysSession->school_id);
    	
    	// 檢查資料表
    	$sqlExistTable = sprintf("SHOW TABLES WHERE Tables_in_%s='%s'", sysDBschool, $table);
    	if (!$sysConn->GetOne($sqlExistTable)) {
    		// 若沒有資料表，則讀取SQL建立
    		$sql = file_get_contents(sysDocumentRoot. '/academic/App_course/activity_list.sql');
    		$sysConn->Execute($sql);
    		$fields = '`caption`, `status`, `permute`, `picture`';
    		
    		// 塞入預設的資料
    		for ($i=1;$i<4;$i++) {
    			dbNew($table, $fields, "'Acivity {$i}', 'Y', {$i}, '/base/10001/door/APP/advs/default_adv_{$i}.png'");    			
    		}
    	}
    }

	$js = <<< BOF
	$str
	var MSG_NO_SELECT    = "{$MSG['msg_no_select'][$sysSession->lang]}";
	var MSG_CONFIRM_DELETE   = "{$MSG['msg_confirm_delete'][$sysSession->lang]}";

	var MSG_CAN_NOT_UP    = "{$MSG['msg_not_move_up'][$sysSession->lang]}";
	var MSG_CAN_NOT_DOWN  = "{$MSG['msg_not_move_down'][$sysSession->lang]}";
	var MSG_SELECT_ALL    = "{$MSG['msg_select_all'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['msg_select_cancel'][$sysSession->lang]}";

	/**
	 * 切換全選或全消的 checkbox
	 * @version 1.0
	 **/
	function chgCheckbox() {
		var bol = true;
		var nodes = document.getElementsByTagName("input");
		var obj  = document.getElementById("ck");
		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked == false) bol = false;
		}
		nowSel = bol;
		if (obj  != null) obj.checked = bol;
		if (btn1 != null) btn1.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		if (btn2 != null) btn2.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
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
	}
// ////////////////////////////////////////////////////////////////////////////
	/**
	 * 交換節點
	 * @param object node1 : 節點
	 * @param object node2 : 節點
	 **/
	function swapNode(node1, node2) {
		var pnode1 = null, pnode2 = null, tnode1 = null, tnode2 = null;
		var attr1 = null, attr2 = null;

		if ((typeof(node1) != "object") || (node1 == null)
			|| (typeof(node2) != "object") || (node2 == null))
		{
			return false;
		}
		if (isIE && (BVER == "5.0")) {
			var style1 = node1.className;
			var style2 = node2.className;
			node1.swapNode(node2);
			node1.className = style2;
			node2.className = style1;
		} else {
			pnode1 = node1.parentNode;
			pnode2 = node2.parentNode;
			tnode1 = node1.cloneNode(true);
			tnode2 = node2.cloneNode(true);
			tnode1.className = node2.className;
			tnode2.className = node1.className;
			pnode1.replaceChild(tnode2, node1);
			pnode2.replaceChild(tnode1, node2);
		}
		return true;
	}

	/**
	 * 排序
	 * @param integer val :
	 *     0 : 向上
	 *     1 : 向下
	 * @return
	 **/
	function permute(val) {
		var node1 = null, node2 = null;
		var pnode = null;
		var activityIds = new Array();
		var idx = 0;
		activityIds = getCkVal();
		if (activityIds.length <= 0) {
			alert("{$MSG['msg_select_activity'][$sysSession->lang]}");
			return false;
		}
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		activityIds = new Array();
		if (val == 0) {
			for (var i = 0; i < nodes.length; i++) {
				if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
				if (nodes[i].checked) {
					node1 = nodes[i].parentNode.parentNode;
					if (node1.rowIndex == 2) {
						alert(MSG_CAN_NOT_UP);
						return false;
					}
					activityIds[activityIds.length] = idx;
					swapNode(node1, node1.parentNode.rows[node1.rowIndex - 1]);
				}
				idx = i;
			}
		} else {
			for (var i = nodes.length - 1; i >= 0 ; i--) {
				if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
				if (nodes[i].checked) {
					node1 = nodes[i].parentNode.parentNode;
					if (node1.rowIndex == node1.parentNode.rows.length - 2) {
						alert(MSG_CAN_NOT_DOWN);
						return false;
					}
					activityIds[activityIds.length] = idx;
					swapNode(node1, node1.parentNode.rows[node1.rowIndex + 1]);
				}
				idx = i;
			}
		}
		if (isIE) {
			for (var i = 0; i < activityIds.length; i++) {
				nodes[activityIds[i]].checked = true;
			}
		}
	}

	/**
	 * 儲存順序
	 **/
	var resWin = null;
	function savePermute() {
		var activityIds = new Array();
		
		nowSel = false;
		selfunc();
		activityIds = getCkVal();
		
		var actVal = stringToBase64(activityIds.toString());
		window.open('activity_permute_save.php?activityIds='+actVal);
		
		location.replace('activity_list.php');
	}
// ////////////////////////////////////////////////////////////////////////////
	/**
	 * 取得勾選的活動
	 * @return array activityIds
	 **/
	function getCkVal() {
		var activityIds = new Array();
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return activityIds;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked) {
				activityIds[activityIds.length] = nodes[i].value;
			}
		}
		return activityIds;
	}

	/**
	 * 新增/編輯活動
	 * @param string val : 活動編號
	 **/
	function editActivity(val) {
		var obj = document.getElementById("editForm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.activity_id.value = val;
		obj.submit();
	}

	/**
	 * 刪除勾選的活動
	 **/
	function delActivity() {
		var activityIds = new Array();
		var obj = null;
		activityIds = getCkVal();
		if (activityIds.length <= 0) {
			alert(MSG_NO_SELECT);
			return false;
		} else {
			if(confirm(MSG_CONFIRM_DELETE)) {
				obj = document.getElementById("manageForm");
				obj.action = 'activity_delete.php';
				obj.activityIds.value = stringToBase64(activityIds.toString());
				obj.submit();
			} else {
				return false;
			}
		}
	}

	window.onload = function () {
		chkBrowser();
	};
BOF;

	function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
		if (empty($title)) $title = $caption;
		return $without_title ? ('<div style="width: ' . $width . 'px; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $width . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
	}

	function showNum() {
		global $myTable;
		return $myTable->get_index();
	}

	/**
     * 顯示圖片
     *
     * @param string $picture
     * @return 圖片的路徑
     **/
	function showActivityImage($picture)
	{
        // 如果$picture是空的，則不需處理
		if (empty($picture)) {
			return false;
		}
		$pictureFileBig5 = iconv('UTF-8', 'BIG5', $picture);
        $imageSize = getimagesize(sysDocumentRoot.$pictureFileBig5);
	    $pictureWidth = $imageSize[0]*0.2;
	    $pictureHeight = $imageSize[1]*0.2;

        // 回傳圖片
        return "<img src='{$picture}' width='{$pictureWidth}' height='{$pictureHeight}'>";
	}
	
	/**
     * 顯示活動修改的圖示
     *
     * @param int $courseId
     * @return string：修改的icon路徑與動作function
     **/
	function showActivitySetting($actId) 
	{
		global $sysSession, $MSG;

        // 將課程編號編碼
		$val = sysEncode($actId);
		$icon = '<img title="' . $MSG['msg_modify'][$sysSession->lang] . 
				'" alt="' . $MSG['msg_modify'][$sysSession->lang] . 
				'" src="/theme/' . $sysSession->theme . '/' . $sysSession->env . 
				'/icon_property.gif" width="16" height="16" border="0">';

        // 回傳圖示與操作function
		return '<a href="javascript:;" onclick="editActivity(\'' . $val . '\'); return false;" 
		       class="cssAnchor">' . $icon . '</a>';
	}
	
	/**
	 * 顯示上架或下架
	 * 
	 *  @param string $status
	 *  return 上架狀態，(Y：上架中，N：下架)
	 **/
	function showStatus($status)
	{
		global $sysSession, $MSG;
		if($status==='Y') {
			
			return $MSG['item_status_on'][$sysSession->lang];
		} else {
			return $MSG['item_status_down'][$sysSession->lang];
		}
	}

	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('include', '/lib/base64.js');
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tab_activity_list'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'mainFm', '', 'action="review_permute_save.php" target="resWin" method="post" enctype="multipart/form-data" style="display:inline"');
			showXHTML_input('hidden', 'nodeids', '', '', 'id="nodeids"');
			showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'savePermute' . $_COOKIE['idx']), '', '');

			$myTable = new table();
			$myTable->extra = 'width="900" border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable"';

			// 工具列
			$toolbar = new toolbar();
			$toolbar->add_caption('&nbsp;&nbsp;');
			$editActivityId = sysEncode(0);
			$toolbar->add_input('button', 'po', $MSG['btn_new'][$sysSession->lang],   '', 'class="button01" onclick="editActivity(\''.$editActivityId.'\')"');
			$toolbar->add_input('button', 'dl', $MSG['btn_del'][$sysSession->lang],   '', 'class="button01" onclick="delActivity()"');
			$toolbar->add_caption('&nbsp;&nbsp;');
			$toolbar->add_input('button', 'up', '&uarr;'.$MSG['btn_up'][$sysSession->lang]     ,   '', 'class="button01" onclick="permute(0)"');
			$toolbar->add_input('button', 'dw', '&darr;'.$MSG['btn_down'][$sysSession->lang]   ,   '', 'class="button01" onclick="permute(1)"');
			$toolbar->add_input('button', 'sv', $MSG['btn_save_permute'][$sysSession->lang],   '', 'class="button01" onclick="savePermute()"');
			$myTable->set_def_toolbar($toolbar);

			// 排序
			$myTable->add_sort('serial', '`flow_serial` ASC', '`flow_serial` DESC');
			$myTable->add_sort('kind'  , '`kind` ASC'       , '`kind` DESC');
			$myTable->add_sort('title' , '`title` ASC'      , '`title` DESC');
			// $myTable->set_sort(true, 'user', 'asc');

			// 全選全消的按鈕
			$myTable->set_select_btn(true, 'btnSel', $MSG['msg_select_all'][$sysSession->lang], 'onclick="selfunc()"');

			// 資料
			$ck1 = new toolbar();
			$ck1->add_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');

			$ck2 = new toolbar();
			$ck2->add_input('checkbox', 'rfid[]'  , '%act_id', '', 'onclick="chgCheckbox(); event.cancelBubble=true;"');
			$ck2->add_input('hidden'  , 'pmutes[]', '%permute', '', '');

			// 欄位
			$myTable->add_field($ck1, $MSG['select_all_msg'][$sysSession->lang], '', $ck2, '','align="center"');
			$myTable->add_field($MSG['item_permute'][$sysSession->lang] , '', '', '%permute', '', 'align="center" nowrap="noWrap"');
			$myTable->add_field($MSG['item_picture'][$sysSession->lang], '', '', '%picture', 'showActivityImage', 'width="25%"');
			$myTable->add_field($MSG['item_status'][$sysSession->lang], '', '', '%status', 'showStatus', 'align="center"');
			$myTable->add_field($MSG['item_remark'][$sysSession->lang], '', '', '%caption', 'showSubject', 'width="40%" wrap');
			$myTable->add_field($MSG['item_activity_setting'][$sysSession->lang], '', ''           , '%act_id'   , 'showActivitySetting', 'align="center" nowrap="noWrap"');

			$myTable->set_page(false, 1, 10);

			// SQL 查詢指令
			$table    = 'CO_activities';
			$field = '*';
			$where  = "1 order by `permute` ASC";
			$myTable->set_sqls($table, $field, $where);
			$myTable->show();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();

	showXHTML_form_B('action="activity_property.php" method="post" enctype="multipart/form-data" style="display:none"', 'editForm');
		showXHTML_input('hidden', 'activity_id', '', '', '');
		showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'setRule' . $_COOKIE['idx']), '', '');
	showXHTML_form_E();

	showXHTML_form_B('action="" method="post" enctype="multipart/form-data" style="display:none"', 'manageForm');
		showXHTML_input('hidden', 'activityIds', '', '', '');
		showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'delRule' . $_COOKIE['idx']), '', '');
	showXHTML_form_E();
?>
