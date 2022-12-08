<?php
	/**
     * 輪播廣告管理
     *
     * @since   2012/02/08
     * @author  Kuko Wang
     * @version $Id: adv_manage.php $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lang/co_adv_manage.php');
	require_once(sysDocumentRoot . '/lang/co_lang_manage.php');

	//引入表單後端動作程式
	require_once('adv_handler.php');

		 //接收資料處理動作
	switch ($_POST['act']) {
	    //刪除
	    case 'rm':
	        $errorCode=rmData($_POST['nodeid']);
	        break;

        //更新輪播廣告自動換圖秒數
	    case 'sa':
	        $updateSuccess='';
	        $errorCode=updateAdvSec(intval(trim($_POST['adv_sec'])));
	        if ($errorCode=='') {
	            $updateSuccess=true;
	        } else {
	            $updateSuccess=false;
	        }
	        break;
	}

	/**
	 * Div顯示設定
	 *
	 * @param int $width, string $caption, string $title, boolean $without_title
	 * @return string
	 */
	function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
		if (empty($title)) $title = $caption;
		return $without_title ? ('<div style="width: ' . $width . 'px; white-space:normal;word-wrap:break-word;">' . $caption . '</div>') : ('<div style="width: ' . $width . 'px; white-space:normal;word-wrap:break-word;" title="' . $title . '">' . $caption . '</div>');
	}

	/**
	 * 顯示廣告名稱
	 *
	 * @param string $name, int $advId
	 * @return string
	 */
	function showAdvName($name, $advId) {
		return '<div style="white-space:normal;word-wrap:break-word" id="adv_name_'.$advId.'" title="'.$name.'">'.$name.'</div>';
	}

	/**
	 * 顯示啟用日期資訊
	 *
	 * @param date $date, int $flag
	 * @return string
	 */
	function showOpenDate($openDate, $openFlag) {
	    global $sysSession, $MSG;
	    if ($openFlag=='0') {
	        return $MSG['now_date'][$sysSession->lang];
	    } else {
		    return $openDate;
	    }
	}

	/**
	 * 顯示關閉日期資訊
	 *
	 * @param date $date, int $flag
	 * @return string
	 */
	function showCloseDate($closeDate, $closeFlag) {
	    global $sysSession, $MSG;
	    if ($closeFlag=='0') {
	        return $MSG['unlimit_date'][$sysSession->lang];
	    } else {
		    return $closeDate;
	    }
	}
	/**
	 * 顯示順序編號
	 *
	 * @return string
	 */
    function showNum() {
		global $myTable;
		return $myTable->get_index();
	}
	/**
	 * 顯示按鈕資訊
	 *
	 * @param int $advId, string $url
	 * @return string
	 */
	function showAction($advId, $url) {
		global $MSG,$sysSession;
		return '<input type="button" value="' . $MSG['btn_modify'][$sysSession->lang] .
               '" class="cssBtn" onclick="location=\'adv_edit.php?edit=1&adv_id='.$advId.'\'"/> ' .
               '<input type="button" value="' . $MSG['btn_url'][$sysSession->lang] .
               '" class="cssBtn" onclick="window.open(\''.$url.'\')" />';
	}

	$js = <<< EOB
	var MSG_CAN_NOT_UP    = "{$MSG['msg_not_move_up'][$sysSession->lang]}";
	var MSG_CAN_NOT_DOWN  = "{$MSG['msg_not_move_down'][$sysSession->lang]}";
	var MSG_PERMUTE_SELECT  = "{$MSG['msg_permute_select'][$sysSession->lang]}";
	var MSG_RM_CONFIRM  = "{$MSG['rm_confirm'][$sysSession->lang]}";
	var MSG_CONFIRM_ALERT  = "{$MSG['confirm_alert'][$sysSession->lang]}";
	var resWin = null;

	/**
	 * 提交輪播廣告設定
	 **/
	function setAdvSec(){
	    var obj =document.getElementById('mainFm');

        if (parseInt(obj.adv_sec.value)>20 || parseInt(obj.adv_sec.value)==0) {
            alert('{$MSG['int_0_to_20_errMsg'][$sysSession->lang]}');
            return false;
        }

	    obj.adv_sec.value=obj.adv_sec.value;

	    obj.action='';
	    obj.target='';
	    obj.act.value='sa';
	    obj.submit();
	}

	function chgTab(tabId) {
		if (tabId == 2) {
			document.location.href = '/academic/co_links/links_manage.php';
		}
	}
EOB;


	// 開始頁面展現

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/co_common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
			$ary = array(
					array($MSG['manage_title'][$sysSession->lang], 'tabsSet1',  '')
				);
			echo '<div align="center">';
			showXHTML_tabFrame_B($ary, 1, 'mainFm', '', 'action=""  method="post" style="display:inline;" target="resWin"');
			showXHTML_input('hidden', 'nodeids', '', '', 'id="nodeids"');
			showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'savePermute' . $_COOKIE['idx']), '', '');
			showXHTML_input('hidden', 'act', '', '', 'id="act"');
			$myTable = new table();
			$myTable->display['page'] = false;
			$myTable->extra = 'width="760" border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable"';

			// 工具列
			$toolbar = new toolbar();
            $toolbar->add_input('button', '', $MSG['btn_add'][$sysSession->lang]  , '', 'class="cssBtn" onclick="location=\'adv_edit.php\'" id="btn_new"');
			$toolbar->add_input('button', '', $MSG['btn_rm'][$sysSession->lang]  , '', 'class="cssBtn" onclick="rmConfirm(\'mainFm\', \'adv_name\');" ');

			$toolbar->add_caption('&nbsp;&nbsp;');
			$toolbar->add_input('button', 'up', '&uarr;'     ,   '', 'class="cssBtn" onclick="permute(0,2)"');
			$toolbar->add_input('button', 'dw', '&darr;'   ,   '', 'class="cssBtn" onclick="permute(1,3)"');
			$toolbar->add_input('button', 'sv', $MSG['btn_save'][$sysSession->lang],   '', 'class="button01" onclick="savePermute(\'mainFm\', \'nodeids\', \'adv\')"');
			$myTable->set_def_toolbar($toolbar);

			// 資料
			$ck1 = new toolbar();
			$ck1->add_caption($MSG['select_all'][$sysSession->lang].'<br />');
			$ck1->add_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc();"');

			$ck2 = new toolbar();
			$ck2->add_input('checkbox', 'nodeid[]'  , '%adv_id', '', 'onclick="chgCheckbox(); event.cancelBubble=true;"');
			$ck2->add_input('hidden'  , 'pmutes[]', '%permute', '', '');

			$myTable->add_field($ck1                                       , $MSG['select_all_msg'][$sysSession->lang], '', $ck2, ''             , 'width="30px" align="center"');
			$myTable->add_field($MSG['permute'][$sysSession->lang] , $MSG['alt_serial'][$sysSession->lang]    , 'serial', ''     , 'showNum'    , 'align="center" nowrap="noWrap"');
			$myTable->add_field($MSG['adv_name'][$sysSession->lang]  , '', ''    , '%1 %0'   , 'showAdvName'   , 'width="160px"');
			$myTable->add_field($MSG['open_date'][$sysSession->lang]  , '', ''    , '%2 %7'   , 'showOpenDate'   , 'nowrap="noWrap"');
			$myTable->add_field($MSG['close_date'][$sysSession->lang]  , '', ''    , '%3 %8'   , 'showCloseDate'   , 'nowrap="noWrap"');
			$myTable->add_field($MSG['img_path'][$sysSession->lang], '', '', '%5', 'divMsg(240,this.value)' , 'nowrap="noWrap"');
			$myTable->add_field($MSG['btn_modify'][$sysSession->lang]    , '', '', '%0 %4' , 'showAction'  , 'align="center" nowrap="noWrap"' );

			$tab    = 'CO_adv';
			$fields = 'adv_id, name, open_date, close_date, url, img_path, permute, open_date_flag, close_date_flag';
			$where  = sprintf('school_id=%d order by permute asc, name asc', $sysSession->school_id);
			$myTable->set_sqls($tab, $fields, $where);
			$myTable->show();
		showXHTML_tabFrame_E();
		echo '</div>';


		//adv_handler.php錯誤訊息框
        if ($errorCode) {
            $ary = array(array($MSG['error_msg_title'][$sysSession->lang],'', ''));
    		showXHTML_tabFrame_B($ary, 1, 'errorForm', 'errorTable', 'style="display: inline;"', true);
    		  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable" width="200" height="120" ');
    		    showXHTML_tr_B('class="cssTrOdd"');
    		       showXHTML_td('', $MSG[$errorCode][$sysSession->lang]);
    		    showXHTML_tr_E();

    		    showXHTML_tr_B('class="cssTrEvn"');
		           showXHTML_td_B('align="center"');
		               showXHTML_input('button', '', $MSG['submit'][$sysSession->lang], '', 'class="cssBtn" onclick="hiddenDialog(\'errorTable\');"');
		           showXHTML_td_E();
		        showXHTML_tr_E();
		      showXHTML_table_E();
            showXHTML_tabFrame_E();
            showXHTML_script('inline', "displayErrorDialog('errorTable')");
        }

        //刪除確認序息框
        $ary = array(array($MSG['rm_msg_title'][$sysSession->lang],'', ''));
		showXHTML_tabFrame_B($ary, 1, 'rmForm', 'rmTable', 'style="display: inline;"', true);
		  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable" width="250"  ');
		    showXHTML_tr_B('class="cssTrOdd"');
		       showXHTML_td('', $MSG['confirm_alert'][$sysSession->lang].'<div align="center"><br><div id="rmContents"></div><div id="rmDefaultMsg" style="display:none">'.$MSG['rm_confirm'][$sysSession->lang].'</div></div>');
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrEvn"');
	           showXHTML_td_B('align="center"');
	               showXHTML_input('button', '', $MSG['submit'][$sysSession->lang], '', 'class="cssBtn" onclick="hiddenDialog(\'rmTable\'); rmData(\'mainFm\');"');
	               showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="hiddenDialog(\'rmTable\');"');
	           showXHTML_td_E();
	        showXHTML_tr_E();
	      showXHTML_table_E();
        showXHTML_tabFrame_E();

        if ($updateSuccess==true) {
            showXHTML_script('inline', "alert('".$MSG['update_success'][$sysSession->lang]."');location.href = '/academic/co_adv/adv_manage.php';");
        }
	showXHTML_body_E();
?>
