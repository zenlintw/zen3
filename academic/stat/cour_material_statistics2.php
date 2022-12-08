<?php
	/**
	 * 學校統計資料 - 教材閱讀統計 (course)
	 *
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: cour_material_statistics2.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$ticket = md5(sysTicketSeed . $sysSession->username . 'cour_material_stat' . $sysSession->ticket);

	if ($_POST['ticket'] != $ticket)      die ($MSG['illegal_access'][$sysSession->lang]);
	if (empty($_POST['course_id']))       die ($MSG['illegal_access'][$sysSession->lang]);
	if (intval($_POST['course_id']) == 0) die ($MSG['illegal_access'][$sysSession->lang]);

	list($caption) = dbGetStSr('WM_term_course','caption','course_id=' . $_POST['course_id'], ADODB_FETCH_NUM);

	$lang               = unserialize($caption);
	$export_course_name = $lang[$sysSession->lang];
	$html_title         = $MSG['title104'][$sysSession->lang] . htmlspecialchars($export_course_name);

	$topics             = array($MSG['title113'][$sysSession->lang],/* '第一次讀', '最近一次讀', */$MSG['title114'][$sysSession->lang], $MSG['title115'][$sysSession->lang], $MSG['title116'][$sysSession->lang], $MSG['title117'][$sysSession->lang], $MSG['title118'][$sysSession->lang]);

	$n_key              = array('maxi', 'mini', 'amount', 'sec', 'average');
	$sk                 = array(null, 'title', /* 'first', 'last', */ 'maxi', 'mini', 'amount', 'sec', 'average');

	$i                  = min(max(intval($_POST['i']), 1), 8);
	$d                  = intval($_POST['d']) ^ 1;

	// 數值排序
	function num_sort($a, $b)
	{
		global $order_idx;
    	return $a[$order_idx] - $b[$order_idx];
	}

	// 字串排序
	function str_sort($a, $b)
	{
		global $order_idx;
    	return strcmp($a[$order_idx], $b[$order_idx]);
	}

	// 產生 URL
	function gen_url($url)
	{
		global $sysSession;

		if (strpos($url, 'javascript:') === 0)
			return implode(':;" onclick="', explode(':', $url, 2)) . '; return false;"';
		elseif (eregi('^([a-z]+:(//)?|/|\.\.)', $url))
			return $url . '" target="_blank"';
		else
			return sprintf('/base/%05d/course/%08d/content/%s', $sysSession->school_id, $sysSession->course_id, $url) . '" target="_blank"';
	}

	$js = <<< EOF

	var MSG_SYS_ERROR = "{$MSG['msg_system_error'][$sysSession->lang]} ";
	var theme         = "{$sysSession->theme}";
	var ticket        = "{$ticket}";
	var lang          = "{$sysSession->lang}";

	function s(i){
		var obj = document.getElementById("selfQuery");
		obj.i.value = i;
		obj.action = 'cour_material_statistics2.php';
		obj.submit();
	}

	function fetchWMinstance(type, id){
		var instance = "{$MSG['title122'][$sysSession->lang]}";
		switch(type){
			case 2: instance = "{$MSG['title123'][$sysSession->lang]} "; break;
			case 3: instance = "{$MSG['title124'][$sysSession->lang]} "; break;
			case 4: instance = "{$MSG['title125'][$sysSession->lang]} "; break;
			case 5: instance = "{$MSG['title126'][$sysSession->lang]} "; break;
			case 6: instance = "{$MSG['title127'][$sysSession->lang]} "; break;
			case 7: instance = "{$MSG['title128'][$sysSession->lang]} "; break;
		}
		alert(instance);
		return false;
	}

	function go_back(){
	    var obj = document.getElementById("selfQuery");
		obj.action = 'cour_material_statistics1.php';

		window.onunload = function () {};

		obj.submit();
	}

	//  Export data

	function displayDialog(name){
		var obj = document.getElementById(name);

		//  對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(480)] 再左移 10 個 pixel (pixel)

		obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 460;

		// 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel (pixel)

		obj.style.top   = document.body.scrollTop  + 30;
		obj.style.display = '';
	}

	function hiddenDialog(name){
		document.getElementById(name).style.display = 'none';
	}

	function checkExport(){

		var csv_content = '',htm_content = '',xml_content = '',col='';
		var obj2 = document.getElementById('exportForm');
		var total_rows = 0;
		var title = "{$html_title} ";
		var temp_title = new Array("{$MSG['title113'][$sysSession->lang]} ","{$MSG['title114'][$sysSession->lang]} ","{$MSG['title115'][$sysSession->lang]} ","{$MSG['title116'][$sysSession->lang]} ","{$MSG['title117'][$sysSession->lang]} ","{$MSG['title118'][$sysSession->lang]} ");

		var obj = document.getElementById('mainTable');
		total_rows = obj.rows.length;

		for (var i = 0; i < total_rows; i++) {

			col = col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

			if (i == 0){
				csv_content += title + "<br>";

				htm_content += '<tr '+ col + '><td colspan="6">'+ title + '</td></tr>';

				xml_content += '<summary>'+title + '</summary>';

			}else if (i == 1){
				for (var k=0;k < 6;k++){
					csv_content += temp_title[k] + ',';
				}
				csv_content = csv_content.replace(/,$/,'') + "<br>";

				htm_content += '<tr '+ col + '>';
				for (var k=0;k < 6;k++){
					htm_content += '<td>'+ temp_title[k] + '</td>';
				}
				htm_content += "</tr>";

				xml_content += '<record>'  +
							   '<title>'   + temp_title[0] + '</title>'   +
							   '<maxi>'    + temp_title[1] + '</maxi>'    +
							   '<mini>'    + temp_title[2] + '</mini>'    +
							   '<amount>'  + temp_title[3] + '</amount>'  +
							   '<sec>'     + temp_title[4] + '</sec>'     +
							   '<average>' + temp_title[5] + '</average>' +
							   '</record>';

			}else{
			    
				for (var j=0;j < 6;j++){
					csv_content += obj.rows[i].cells[j].innerHTML + ',';
				}
				csv_content += "<br>";
				
				


				htm_content += '<tr '+ col + '>';

				for (var j=0;j < 6;j++){
					htm_content += '<td>'+ obj.rows[i].cells[j].innerHTML +'</td>';
				}
				htm_content += "<tr>";

				xml_content += '<record>'  +
							   '<title>'   + obj.rows[i].cells[0].innerHTML + '</title>' +
							   '<maxi>'    + obj.rows[i].cells[1].innerHTML + '</maxi>' +
							   '<mini>'    + obj.rows[i].cells[2].innerHTML + '</mini>' +
							   '<amount>'  + obj.rows[i].cells[3].innerHTML + '</amount>' +
							   '<sec>'     + obj.rows[i].cells[4].innerHTML + '</sec>' +
							   '<average>' + obj.rows[i].cells[5].innerHTML + '</average>' +
							   "</record>";
			}
		}

		var obj2 = document.getElementById('exportForm');

		var nodes = obj2.getElementsByTagName('input');

		var sel_count = 0;

		for (var i = 0 ; i < nodes.length ; i++){
			if ((nodes[i].type == "checkbox") && (nodes[i].checked)){
				switch (nodes[i].value){
					case 'csv':
						obj2.csv_content.value = csv_content;
						sel_count++;
						break;
					case 'htm':
						obj2.htm_content.value = htm_content;
						sel_count++;
						break;
				case 'xml':
						obj2.xml_content.value = xml_content;
						sel_count++;
						break;
				}
			}
		}

		if (sel_count > 0){
			hiddenDialog('exportTable');
			obj2.submit();
			document.getElementById('btn_export').disabled = false;
		}
		else {
			alert("{$MSG['no_select_data_format'][$sysSession->lang]}");
		}
	}

EOF;

	showXHTML_head_B($MSG['title4'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('inline', $js);
	showXHTML_script('include', 'sch_statistics.js');
	
		echo '<style>';
echo '		
		@media print {
		    #ImgL1,#ImgR1 {
                content: url("");
            }
		}
	
}
';
echo '</style>';
	showXHTML_head_E('');

	showXHTML_body_B('');

		showXHTML_table_B('width="600" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['title101'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');

			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');

					showXHTML_table_B('id ="mainTable" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						showXHTML_tr_B('class="cssTrEvn font01"');
							showXHTML_td_B('align="cetner" colspan="7"');
								echo $MSG['title104'][$sysSession->lang] . $export_course_name;
								showXHTML_input('button', 'go_back', $MSG['title109'][$sysSession->lang], '', 'id="go_back" class="cssBtn" ' .'onclick="javascript:go_back();"' . ' title=' . $MSG['title109'][$sysSession->lang]);
								showXHTML_input('button', 'btnImp' , $MSG['export'][$sysSession->lang]  ,'' , 'onclick="this.disabled=true; displayDialog(\'exportTable\');" id="btn_export"');
								showXHTML_input('button', 'btnImp' , $MSG['print'][$sysSession->lang]   ,'' , 'onclick="javascript:window.print();"');
							showXHTML_td_E('');
		  				showXHTML_tr_E();

						showXHTML_tr_B('class="cssTrHead font01"');
		  					foreach($topics as $x => $item){
		  					  showXHTML_td('align="center" style="font-weight: bold" nowrap', sprintf('<a href="javascript:s(%d)">%s%s</a>', $x+1, $item, ($x+1==$i ? sprintf('<img src="/theme/default/academic/dude07232001%s.gif" border="0" align="absmiddl">', $d ? 'up' : 'down'):'')));
		  					}
		  				showXHTML_tr_E();

						/*!4 FORCE INDEX(idx2) */
						$sqls = 'select title,url,activity_id,count(title) as amount,' .
								 'max(UNIX_TIMESTAMP(over_time) - UNIX_TIMESTAMP(begin_time) + 1) as maxi,' .
								 'min(UNIX_TIMESTAMP(over_time) - UNIX_TIMESTAMP(begin_time) + 1) as mini,' .
								 'sum(UNIX_TIMESTAMP(over_time) - UNIX_TIMESTAMP(begin_time) + 1) as sec ' .
								 'from WM_record_reading  where course_id=' . $_POST['course_id'] .
								 ' group by url';
						$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
						chkSchoolId('WM_record_reading');
						if ($all = $sysConn->CacheGetArray($sqls))
						{
							foreach($all as $x => $item) $all[$x]['average'] = round($item['sec'] / max(1, $item['amount']), 1); // 求出平均
							$order_idx = $sk[$i];
							if (in_array($order_idx, $n_key))
								usort($all, 'num_sort');
							else
								usort($all, 'str_sort');
							if ($d == 0) $all = array_reverse($all);
						}

						if (is_array($all)){
							if (count($all) > 0){
							  	foreach($all as $fields){
							  	  $cln = $cln == 'class="cssTrEvn font01"' ? 'class="cssTrOdd font01"' : 'class="cssTrEvn font01"';

								  showXHTML_tr_B($cln);
								    // showXHTML_td('nowrap', '<span style="width: 300px; overflow: hidden"><a href="' . gen_url($fields['url']) . ' title="' . htmlspecialchars($fields['activity_id']) . '" class="link_fnt01">' . htmlspecialchars($fields['title']) . '</a></span>');
								    showXHTML_td('nowrap', htmlspecialchars($fields['title']));
								    // showXHTML_td('nowrap style="display: none"', $fields['first']);
								    // showXHTML_td('nowrap style="display: none"', $fields['last']);
								    showXHTML_td('width="80" align="right" nowrap', zero2gray(sec2timestamp($fields['maxi'])));
								    showXHTML_td('width="80" align="right" nowrap', zero2gray(sec2timestamp($fields['mini'])));
								    showXHTML_td('width="80" align="right" nowrap', $fields['amount']);
								    showXHTML_td('width="80" align="right" nowrap', zero2gray(sec2timestamp($fields['sec'])));
							    	showXHTML_td('width="80" align="right" nowrap', zero2gray(sec2timestamp($fields['average'])));
								  showXHTML_tr_E();
							  	}
							}else{
								showXHTML_tr_B('class="cssTrOdd font01"');
					      			showXHTML_td('align="cetner" colspan="7"', $MSG['title120'][$sysSession->lang]);
					    		showXHTML_tr_E();
					    	}

					  }else{
					    	showXHTML_tr_B('class="cssTrOdd font01"');
					      		showXHTML_td('align="cetner" colspan="7"', $MSG['title120'][$sysSession->lang]);
					    	showXHTML_tr_E();
					  }

					showXHTML_table_E('');

				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

		// 回上一頁 & 翻頁
		showXHTML_form_B('action="cour_material_statistics1.php" method="post" enctype="multipart/form-data" style="display:none" target="main"', 'selfQuery');
			showXHTML_input('hidden', 'ticket', $ticket, '', '');
			showXHTML_input('hidden', 'cour_query', htmlspecialchars(stripslashes(trim($_POST['cour_query']))), '', '');
			showXHTML_input('hidden', 'tea_query', trim($_POST['tea_query']), '', '');
			showXHTML_input('hidden', 'page_num', $_POST['page_num'], '', '');
			showXHTML_input('hidden', 'page_no', $_POST['page_no'], '', '');
			showXHTML_input('hidden', 'course_id', $_POST['course_id'], '', '');
			showXHTML_input('hidden', 'i', $_POST['i'], '', '');
			showXHTML_input('hidden', 'd', $d, '', '');
		showXHTML_form_E('');

		// 匯出
		$ary = array(array($MSG['export'][$sysSession->lang], '', ''));
		showXHTML_tabFrame_B($ary, 1, 'exportForm', 'exportTable', 'action="sch_cour_export1.php" method="POST" style="display: inline" target="empty"', true);
		  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable"');

		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td('', $MSG['title20'][$sysSession->lang]);
		      showXHTML_td_B();
		        showXHTML_input('checkboxes', 'type_kinds[]', array('csv' => 'Excel (.csv)',
		        													'htm' => 'HTML table (.htm)',
		        													'xml' => 'XML (.xml)'), array('csv'), '', '<br>');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrOdd"');
		      showXHTML_td('', $MSG['title21'][$sysSession->lang]);
		      showXHTML_td_B();
		        showXHTML_input('text', 'download_name', 'cour_material_stat.zip', '', 'maxlength="60" size="40" class="box02"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td_B('colspan="2" align="center"');
		      	showXHTML_input('hidden', 'sel_type', '', '', '');
		        showXHTML_input('hidden', 'csv_content', '', '', '');
		  		showXHTML_input('hidden', 'htm_content', '', '', '');
    	  		showXHTML_input('hidden', 'xml_content', '', '', '');
    	  		showXHTML_input('hidden', 'function_name', $MSG['title101'][$sysSession->lang], '', '');
		        showXHTML_input('hidden', 'adv_file', 'cour_material_stat', '', '');
		        showXHTML_input('button', '', $MSG['title11'][$sysSession->lang], '', 'class="cssBtn" onclick="checkExport();"');
		        showXHTML_input('button', '', $MSG['title22'][$sysSession->lang], '', 'class="cssBtn" onclick="hiddenDialog(\'exportTable\'); document.getElementById(\'btn_export\').disabled = false;"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		showXHTML_table_E();

	showXHTML_body_E('');

?>
