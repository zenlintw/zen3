<?php
	/**
	 * 管理者 - 導師管理 - 新增 -  radio 選項
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Amm Lee <amm@sun.net.tw>
	 * @copyright   2000-2005 SunNet Tech. INC.
	 * @version     CVS: $Id: director_add.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2005-09-22
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/academic_director.php');
    
	if($_GET['type'] == 'remove'){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務
		$exec_func = '2400200200';
		$html_title = $MSG['title63'][$sysSession->lang];
	}elseif($_GET['type'] == 'query'){	// 管理者 - 導師管理 - 查詢某一個班級有哪些導師(或助理)
		$exec_func = '2400200300';
		$html_title = $MSG['title85'][$sysSession->lang];
	}else{	// 新增：新增一個或多個導師(或助理)到某個班級中
		$exec_func = '2400200100';
		$html_title = $MSG['title12'][$sysSession->lang];
	}
	
	$sysSession->cur_func=$exec_func;
	$sysSession->restore();
	if (!aclVerifyPermission($exec_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}
	// 變數宣告 begin

	// 導師資料管理 radio 的值
		if($_GET['type'] == 'remove'){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務
			$add_radio = array(1 => $MSG['title13'][$sysSession->lang],
							   3 => $MSG['title64'][$sysSession->lang],
							   2 => $MSG['title65'][$sysSession->lang]);
			$remove_type = '?type=' . trim($_GET['type']);
			$radio_default = 1;
		}elseif($_GET['type'] == 'query'){	// 管理者 - 導師管理 - 查詢某一個班級有哪些導師(或助理){
			$add_radio = array(2 => $MSG['title79'][$sysSession->lang],
							   3 => $MSG['title80'][$sysSession->lang]);
			$radio_default = 2;
			$remove_type = '?type=' . trim($_GET['type']);
		}else{	// 新增：新增一個或多個導師(或助理)到某個班級中
			$add_radio = array(1 => $MSG['title13'][$sysSession->lang],
							   2 => $MSG['title14'][$sysSession->lang],
							   3 => $MSG['title15'][$sysSession->lang]);
			$remove_type = '';
			$radio_default = 1;
		}
	// 變數宣告 end

	// 函數宣告 begin
	// 函數宣告 end

	// 主程式 begin

	// 主程式 end
	$js = <<< BOF
		function next_stage() {
			var act_val = '';
			var options = document.getElementsByTagName('input');
			for(var i = 0;i < options.length;i++){
				if ((options[i].type =="radio") && (options[i].checked))
					act_val = parseInt(options[i].value);
			}
			var go_next = '';
			switch(act_val){
				case 1:
					go_next = 'director_add_csv.php{$remove_type}';
					break;
				case 2:
					go_next = 'director_choose_class.php{$remove_type}';
					break;
				case 3:
					go_next = 'director_choose_director1.php{$remove_type}';
					break;
			}
			window.location.href=go_next;
		}
BOF;
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['title'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'Dnote', 'Dtable', 'action="list.php" method="post" style="display: inline"');
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
						showXHTML_td_B('');
							echo $html_title;
						showXHTML_td_E();
					showXHTML_tr_E();
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('');
							showXHTML_input('radio', 'add_act', $add_radio ,$radio_default,'', '<br>');
						showXHTML_td_E();
					showXHTML_tr_E();
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('align="center"');
							showXHTML_input('button', '', $MSG['title16'][$sysSession->lang], '', 'class="cssBtn" onclick="javascript:window.location.href=\'director_main.php\';" ');
							showXHTML_input('button', '', $MSG['title11'][$sysSession->lang], '', 'class="cssBtn" onclick="next_stage()" ');
						showXHTML_td_E();
					showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';

	// 主程式 end
?>