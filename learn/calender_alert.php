<?php
	/**
	 * 登入時的 行事曆 提醒訊息
	 *
	 * @since   2004/07/21
	 * @author  Amm Lee
	 * @version $Id: calender_alert.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_cal_alert.php');
	require_once(sysDocumentRoot . '/lang/calendar.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func='2300100400';
	$sysSession->restore();
	if (!aclVerifyPermission(2300100400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	/*showXHTML_head_B($MSG['heml_title'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E('');
	
	showXHTML_body_B('leftmargin="7" topmargin="7"');
		$ary = array();
		$ary[] = array($MSG['heml_title'][$sysSession->lang], 'tabs');
		showXHTML_tabFrame_B($ary, 1,'','','style="display: inline;"', false, false);

			showXHTML_table_B('id ="mainTable" width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('width="15" align="center"',  $MSG['titel_type'][$sysSession->lang]);
					showXHTML_td('width="90" align="center"',  $MSG['title_date'][$sysSession->lang]);
					showXHTML_td('width="100" align="center"', $MSG['title_time'][$sysSession->lang]);
					showXHTML_td('width="260" align="center"', $MSG['title_subject'][$sysSession->lang]);
					showXHTML_td('width="260" align="center"', $MSG['title_content'][$sysSession->lang]);
				showXHTML_tr_E('');
				// calendar kind
				$cal_type = array('personal','course','class','school');
				$cal_num  = count($cal_type);
				$cal_msg  = GetCalendarAlert();
				$doc 	  = domxml_open_mem(preg_replace('/\sxmlns\s*=\s*"[^"]*"\s/U', ' ', $cal_msg));
				$xpath 	  = @xpath_new_context($doc);
				// for begin
				for ($i = 0;$i < $cal_num;$i++){
					$obj = xpath_eval($xpath, '/manifest/' . $cal_type[$i] . '[@num > 0]/memo');
					$p_nodeset = $obj->nodeset;
					$t_p_count = count($p_nodeset);

					$cal1 = 'flag_' . $cal_type[$i];
					$cal_img1 = '<img src="' . '/theme/' . $sysSession->theme . '/learn/cale_' . $cal_type[$i] . '.gif' . '" width="12" height="14" align="absmiddle" title="' . $MSG[$cal1][$sysSession->lang] .  '">';

					if ($t_p_count > 0){
						for ($j=0; $j< $t_p_count; $j++){
							$children = $p_nodeset[$j]->child_nodes();

							$time_begin = '';
							$time_end 	= '';
							$subject 	= '';
							$content 	= '';
							$date_range = '';
							$date_range1= '';
							$alert_type = '';

							foreach ($children as $child) {
								foreach ($child->child_nodes() as $sub){
									$p_node = $sub->parent_node();
									switch ($p_node->node_name()){
										case 'alert_type':
											$alert_type = $sub->content;
											break;
										case 'memo_date':
											$memo_date = $sub->content;
											break;
										case 'time_begin':
											$time_begin = date('H:i', strtotime($sub->content));
											break;
										case 'time_end':
											$time_end = date('H:i', strtotime($sub->content));
											break;
										case 'subject':
											$subject = htmlspecialchars($sub->content);
											break;
										case 'content':
											$type = $p_node->get_attribute('type');
											$content = ($type && $type == 'text') ? htmlspecialchars($sub->content) : $sub->content;
											break;
									}
								}

								if (empty($time_begin)){
									$date_range = '';
								}else{
									$date_range = $MSG['from'][$sysSession->lang] . $time_begin . '<br>';
								}

								if (empty($time_end)){
									$date_range1 = '';
								}else{
									$date_range1 = $MSG['to'][$sysSession->lang] . $time_end;
								}

							}

							//  判斷 是否有要登入顯示 (begin)
							if  (strpos($alert_type,"login")!==false){
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('width="15" align="center"',  $cal_img1);
									showXHTML_td('width="90" align="left"', $memo_date);
									showXHTML_td('width="100" align="left"', $date_range . $date_range1);
									showXHTML_td('width="260" align="left"', $subject);
									showXHTML_td('width="260" align="left"', $content);
								showXHTML_tr_E('');
							}
							//  判斷 是否有要登入顯示 (end)
						} 
					}// end of if
				} // end of foreach cal_type

				// 關閉按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="right" colspan="5" nowrap="nowrap"');
						showXHTML_input('button', '', $MSG['win_colse'][$sysSession->lang] , '', 'class="cssBtn" onclick="window.close()"');
						echo '&nbsp;';
					showXHTML_td_E();
				showXHTML_tr_E('');

			showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E('');*/
	$data = array();
	$cal_type = array('personal','course','school');
	$cal_num  = count($cal_type);
	$cal_msg  = GetCalendarAlert();
	$doc 	  = domxml_open_mem(preg_replace('/\sxmlns\s*=\s*"[^"]*"\s/U', ' ', $cal_msg));
	$xpath 	  = @xpath_new_context($doc);
	// for begin
	for ($i = 0;$i < $cal_num;$i++){
		$obj = xpath_eval($xpath, '/manifest/' . $cal_type[$i] . '[@num > 0]/memo');
		$p_nodeset = $obj->nodeset;
		$t_p_count = count($p_nodeset);

		$cal1 = 'flag_' . $cal_type[$i];
		$cal_img1 = '<img src="' . '/theme/' . $sysSession->theme . '/learn/cale_' . $cal_type[$i] . '.gif' . '" width="12" height="14" align="absmiddle" title="' . $MSG[$cal1][$sysSession->lang] .  '">';

		if ($t_p_count > 0){
			for ($j=0; $j< $t_p_count; $j++){
				$children = $p_nodeset[$j]->child_nodes();

				$time_begin = '';
				$time_end 	= '';
				$subject 	= '';
				$content 	= '';
				$date_range = '';
				$date_range1= '';
				$alert_type = '';
				$course_name= '';
				$during     = '';

				foreach ($children as $child) {
					foreach ($child->child_nodes() as $sub){
						$p_node = $sub->parent_node();
						switch ($p_node->node_name()){
							case 'alert_type':
								$alert_type = $sub->content;
								break;
							case 'memo_date':
								$memo_date = $sub->content;
								break;
							case 'time_begin':
								$time_begin = substr($sub->content,0,5);
								break;
							case 'time_end':
								$time_end = substr($sub->content,0,5);
								break;
							case 'subject':
								$subject = htmlspecialchars($sub->content);
								break;
							case 'course_name':
								$course_name = htmlspecialchars($sub->content);
								break;
							case 'during':
								$during = $sub->content;
								break;		
							case 'content':
								$type = $p_node->get_attribute('type');
								$content = ($type && $type == 'text') ? htmlspecialchars($sub->content) : $sub->content;
								break;
						}
					}

					/*if (empty($time_begin)){
						$date_range = '';
					}else{
						$date_range = $MSG['from'][$sysSession->lang] . $time_begin ;
					}

					if (empty($time_end)){
						$date_range1 = '';
					}else{
						$date_range1 = $MSG['to'][$sysSession->lang] . $time_end;
					}*/
					
					if (!empty($time_begin) && !empty($time_end)) {
						$date_range = $MSG['from'][$sysSession->lang] . $time_begin . $MSG['to'][$sysSession->lang] . $time_end ;
						if (($cal_type[$i] == 'course' || $cal_type[$i] == 'school') && $during!='') {
							$order_time = '23:59';
						} else {
						    $order_time = $time_begin;
						}
					} else {
						$order_time = '23:59';
						$order_time2 = '23:59';
					}
					if (!empty($time_begin)) {
                        $order_time2 = $time_begin;
					}

				}
                if ($cal_type[$i] == 'personal') $cal_type[$i] = 'person';
                if ($cal_type[$i] == 'person') $type = 1;
                if ($cal_type[$i] == 'course') $type = 2;
                if ($cal_type[$i] == 'school') $type = 3;
                $datalist[] = array('date'=>$memo_date,'time'=>$date_range,'subject'=>$subject,'content'=>$content,'course_name'=>$course_name,'during'=>$during,'type'=>$cal_type[$i],'order'=>$order_time,'order2'=>$order_time2,'otype'=>$type);
				
			} 
		}// end of if
	} // end of foreach cal_type

	
	function co_sort($a, $b) {
		global $datalist;
		$criteria = array('date'=>'asc','order'=>'asc','order2'=>'asc','otype'=>'asc');
		
		foreach($criteria as $what => $order){ 

			if($datalist[$a][$what] == $datalist[$b][$what]){  
				continue;  
			}							

			return (($order == 'desc')?-1:1) * (($datalist[$a][$what] < $datalist[$b][$what]) ? -1 : 1);
		}  
		return 0;  
    }
    
    if(is_array($datalist)){
					
		$sortfields = $datalist;
		uksort($sortfields, 'co_sort'); 
		
		$datalist = array_values($sortfields);  // 重建keys
		
		//array_multisort($years,SORT_DESC,$name,SORT_ASC, $teach);
	}
	
	$data = array();
	foreach ($datalist as $key => $val) {
		unset($val['order']);
		unset($val['order2']);
		unset($val['otype']);
		$date = $val['date'];
		unset($val['date']);
		$data[$date][] = $val;
	}
	unset($datalist);

	$smarty->assign('datalist', $data);
	
	if ($profile['isPhoneDevice']) {
		$smarty->display('learn/calender_mobile_alert.tpl');
	} else {
	    $smarty->display('learn/calender_alert.tpl');
	}
	$smarty->display('common/tiny_footer.tpl');
