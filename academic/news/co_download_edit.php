<?php
	/**
     * 管理環境/公告與聯繫/下載專區管理/編輯
     *
     * PHP 4.4.7+, MySQL 4.0.21+, Apache 1.3.36+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM5
     * @author      panchih <a84155844@gmail.com>
     * @copyright   2000-2017 SunNet Tech. INC.
     * @version     SVN: $Id$
     * @since       2017-04-07
     * 
     * 備註：          
     */

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
	require_once(sysDocumentRoot . '/lang/co_download_manage.php');
	require_once(sysDocumentRoot . '/lib/editor.php');

	list($year, $mon, $mday) = explode('-',date("Y-m-d"),3);
	$dd = array(
		'openY'      => $year,
		'openM'      => $mon,
		'openD'      => $mday,
		'closeY'     => $year,
		'closeM'     => $mon,
		'closeD'     => $mday
		
	);
	$sortBy = isset($_POST['sortBy']) ? $_POST['sortBy'] : $_GET['sortby'];
	$order = isset($_POST['order']) ? $_POST['order'] : $_GET['order'];
	$page = isset($_POST['page'])? $_POST['page'] : $_GET['page'];
	if($_POST['act'] == 'modify'){
		$page_num = isset($_COOKIE['page']) ? $_COOKIE['page']: '';
		$postId = intval($_POST['id']);
		$RS =  dbGetStSr('CO_download', '*', "delete_flag=0 and id={$postId}");
		$ot = $sysConn->UnixTimeStamp($RS['open_date']);
		$ct = $sysConn->UnixTimeStamp($RS['close_date']);
		$oD = getdate($ot);
		$cD = getdate($ct);
		$dd['title']      = $RS['title'];
		$dd['epaperfile']    = $RS['epaperfile'];
		$dd['open_date_flag'] =$RS['open_date_flag'];
		if (!empty($ot)) {
			$dd['openY']      = $oD['year'];
			$dd['openM']      = $oD['mon'];
			$dd['openD']      = $oD['mday'];
			
		}
		if (!empty($ct)) {
			$dd['closeY']     = $cD['year'];
			$dd['closeM']     = $cD['mon'];
			$dd['closeD']     = $cD['mday'];
		}
	}
	function createImg($url){
		 $type = array('avi','bmp','doc','gif','htm','html','jpg','mp3','pdf','ppt','txt','wav','xls','zip');
		 $icon = '<img border="0" align="absmiddle" src="/theme/default/filetype/' .
                    ((($ext = strtolower(substr(strrchr($url, '.'), 1))) && in_array($ext, $type))?
                $ext : 'default') . '.gif">';
        return $icon;
	}
	function printFile($v,$k){
		global $sysSession,$MSG;
		if($v != ""){
			$file = strchr($v, '/base');
			$fileName = substr(strrchr($v, '/'), 1);
			echo $MSG['btn_removeNew'][$sysSession->lang];
			showXHTML_input('checkbox', 'del[]', $fileName, '', '');
			$imgHtml = createImg($v);
			echo "<a href='{$file}' class='cssAnchor' download>{$imgHtml} {$fileName}</a><br/>";
		}
	}
	$js = <<<EOF
		function statDateShow(val, objName) {
			var obj = document.getElementById(objName);
			if (obj != null) obj.style.visibility = (val) ? "visible" : "hidden";
		}
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
		function saveMessage(){
			var t = $("#title").val();
			if($('#ckopen').prop('checked') && $('#ckclose').prop('checked')){
				var topen = $('#timeopen').val();
				var tclose = $('#timeclose').val();
				if(tclose < topen){
					alert('結束時間不能小於開始時間');
					return;
				}
			}	
			if(t == ""){
				alert('{$MSG['titlenotempty'][$sysSession->lang]}');
			}else{
				$("#fomId").submit();
			}
		}
		function getManagerPage(){
			var sort = "{$sortBy}";
			var order = "{$order}";
			var page = "{$page}";
			location.assign("co_download_manage.php?sortby=" + sort + "&order=" + order + '&page=' + page);
		}
		window.onload = function () {
			Calendar_setup("timeopen" , "%Y-%m-%d ", "timeopen" , false);
			Calendar_setup("timeclose", "%Y-%m-%d ", "timeclose", false);
		};
		$(function(){

			$("#btnMoreAtt").click(function(){
				var fileInput = $("#upload_box").prop("outerHTML");
				$("#buttonTr").before(fileInput);
			});
		});
EOF;
	showXHTML_head_B($MSG['epaper_title'][$sysSession->lang]);
	$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
	$calendar->load_files();
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/forum/write.js');
	showXHTML_script("include","/lib/jquery/jquery.min.js");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['epaper_title'][$sysSession->lang], 'queryTable');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'fomId', 'tabProperty', 'action="co_download_handle.php" method="post" enctype="multipart/form-data" style="display:inline"');
			showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				$col = 'class="cssTrOdd"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['required'][$sysSession->lang]);
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['theme_required'][$sysSession->lang]);
					showXHTML_td_B();
					showXHTML_input('text', 'theme', (isset($dd['title'])) ? $dd['title'] : '', '', 'id="title" size="40"  class="cssInput" maxlength="255"');
					if($_POST['act'] == 'modify'){
						showXHTML_input('hidden', 'type', "update", '', '');
						showXHTML_input('hidden', 'id', $RS['id'], '', '');
					}else{
						showXHTML_input('hidden', 'type', "new", '', '');
					}
					showXHTML_input('hidden', 'sortBy', $sortBy , '', '');
					showXHTML_input('hidden', 'order', $order, '', '');
					showXHTML_input('hidden', 'page', $page, '', '');
					showXHTML_td_E();
						showXHTML_td('', $MSG['themenotice'][$sysSession->lang]);
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['begintime'][$sysSession->lang]);
					showXHTML_td_B();
						$val = sprintf('%04d-%02d-%02d', $dd['openY'], $dd['openM'], $dd['openD']);
						$ck = empty($ot) ? '' : ' checked="checked"';
						showXHTML_input('checkbox', 'ckopen', '1', '', 'id="ckopen" onclick="statDateShow(this.checked, \'spanopen\');"' . $ck);
						echo '<label for="ckopen">' . $MSG['limittime'][$sysSession->lang] . '</label>';
						$dis = empty($ot) ? ' style="visibility: hidden;"' : '';
						printf('<span id="spanopen" %s >%s',$dis,$MSG['msg_datetime'][$sysSession->lang]);
						showXHTML_input('text', 'timeopen', $val, '', 'id="timeopen" readonly="readonly" class="cssInput"');
						echo '</span>';
					showXHTML_td_E();
					showXHTML_td_B();
						echo $MSG['addnetnoticebegin'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['endtime'][$sysSession->lang]);
					showXHTML_td_B('');
						$val = sprintf('%04d-%02d-%02d', $dd['closeY'], $dd['closeM'], $dd['closeD']);
						$ck = empty($ct) ? '' : ' checked="checked"';
						showXHTML_input('checkbox', 'ckclose', '1', '', 'id="ckclose" onclick="statDateShow(this.checked, \'spanclose\');"' . $ck);
						echo '<label for="ckclose">' . $MSG['limittime'][$sysSession->lang] . '</label>';
						$dis = empty($ct) ? ' style="visibility: hidden;"' : '';
						printf('<span id="spanclose" %s >%s',$dis,$MSG['msg_datetime'][$sysSession->lang]);
						showXHTML_input('text', 'timeclose', $val, '', 'id="timeclose" readonly="readonly" class="cssInput"');
						echo '</span>';
					showXHTML_td_E();
					showXHTML_td_B();
						echo $MSG['addnetnoticeend'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				if($_POST['act'] == 'modify'){
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
						showXHTML_td('', $MSG['already_file_save'][$sysSession->lang]);
						$fileArr = explode(chr(9), $RS['attach_path']);
						showXHTML_td_B('colspan=2');
							array_walk($fileArr, printFile);
						showXHTML_td_E();
					showXHTML_tr_E();
				}
				
				 $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
	                showXHTML_tr_B($col . ' id="upload_box"');
	                    showXHTML_td('align="right" nowrap="nowrap"', $MSG['edit_file'][$sysSession->lang]);
	                    showXHTML_td_B('nowrap="nowrap" colspan=2');
	                        showXHTML_input('file', 'file[]', '', '', 'class="cssInput" size="76"');
	                    showXHTML_td_E('');
	                showXHTML_tr_E('');
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col . 'id="buttonTr"');
					showXHTML_td_B('colspan="3" align="center"');
						showXHTML_input('button', '', $MSG['edit_success'][$sysSession->lang]    , '', 'class="cssBtn" onclick="saveMessage()"');
						showXHTML_input('button', '', $MSG['edit_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="getManagerPage()"');
						 showXHTML_input('button', '', $MSG['more_att'][$sysSession->lang], '', 'id="btnMoreAtt" class="cssBtn"'); echo '&nbsp;&nbsp;';
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();				
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

