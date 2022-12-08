<?php
	/**
	 * 校園廣場 - 學習榮譽榜
	 * $Id: school_ranking.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/learn_ranking.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/lib_logs.php');

    // 排序時需要的顯示圖案 by Small
	$icon_up = '<img src="/theme/' . $sysSession->theme . '/academic/dude07232001up.gif" border="0" align="absmiddl">';
	$icon_dn = '<img src="/theme/' . $sysSession->theme . '/academic/dude07232001down.gif" border="0" align="absmiddl">';

	$sysSession->cur_func = '1500500100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500500100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

#============ main ========================
	/*
    * 排序
    */
	$cour_sort = array(
		''              , 'username',
		'total_course'  , 'total_grade',
		'login_times'   , 'post_times',
		'dsc_times'     ,
		'total_readtime', 'total_readpages'
	);
	if (!isSet($_POST['sortby'])) $_POST['sortby'] = 3;
	$sortby = $cour_sort[intval($_POST['sortby'])];
	$order  = trim($_POST['order']);

	if (empty($sortby)) $sortby = 'total_grade';

	$order = ($_POST['order'] == 'asc') ? 'asc' : 'desc';
	$sqls  = 'select * from WM_record_learn_record ';
	$sqls .= ' order by ' . " {$sortby} {$order}";

    //取得使用者的資料
    $NullUserData = false;		//是否捉不到資料
    $self_data = dbGetStSr("WM_record_learn_record","*","username='{$sysSession->username}'", ADODB_FETCH_ASSOC);
    if (empty($self_data)) $NullUserData=true;

    if (!$NullUserData)
    {
    	if ($order == 'desc')
	    {
    		list($ct) = dbGetStSr("WM_record_learn_record","count(*) as ct","{$sortby} > '{$self_data[$sortby]}'", ADODB_FETCH_NUM);
    	}else{
    		list($ct) = dbGetStSr("WM_record_learn_record","count(*) as ct","{$sortby} < '{$self_data[$sortby]}'", ADODB_FETCH_NUM);
    	}
    	$self_rank_value = intval($ct)+1;
    }

    //計算筆數與頁數
	if ($_POST['page_num'] != ''){
		$page_num = intval($_POST['page_num']);
	}else if ($_GET['page_num'] != ''){
		$page_num = intval($_GET['page_num']);
	}
	if (empty($page_num)) $page_num = sysPostPerPage;
	
	$total_item  = (int)dbGetOne('WM_user_account', 'count(*)', '1');

	if ($sysSession->username == 'guest')	        // guest只允許看第一頁資料
	{
		$total_item       = min(10, $total_item);	// 總筆數
		$total_page       = 1;                      // 總頁數
		$cur_page         = 1;                      // 目前頁數
		$limit_begin      = 0;                      // 從第幾筆列起
		$_POST['page_no'] = 1;                      // 目前頁數 (0代表全列)
		$off_set          = 10;						// 每頁列幾筆
	}
	else if ($sysSession->env == 'learn')	        // 學習環境下只能看前1/10名次的人
	{
		if (!isset($_POST['page_no'])) $_POST['page_no']=1;
		$old_item    = $total_item;
		$total_item  = $total_item > 10 ? ((int)$total_item / 10) : 1;
		$total_page  = max(1,ceil($total_item / $page_num));
		$cur_page    = min($total_page, max(1, (int)$_POST['page_no']));
		$limit_begin = (($cur_page-1) * $page_num);
		$off_set     = $cur_page == $total_page ? ($total_item - $limit_begin) : $page_num;
		if ($order == 'asc') $limit_begin = $old_item - $total_item + ($cur_page-1) * $page_num;
	}
	else                                            // 其它環境正常顯示
	{
		if (!isset($_POST['page_no'])) $_POST['page_no']=1;
		$total_page  = max(1,ceil($total_item / $page_num));
		$cur_page    = min($total_page, max(1, (int)$_POST['page_no']));
		$limit_begin = (($cur_page-1) * $page_num);
		$off_set     = $page_num;
	}
	

    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	if ($_POST['page_no'] == 0){
		$RS = $sysConn->SelectLimit($sqls, $total_item, 0);
	}else{
		$RS = $sysConn->SelectLimit($sqls,$off_set,$limit_begin);
	}

	$js = <<< BOF

	var orderby    = "{$orderby}";
	var cur_page   = {$cur_page};
	var total_page = {$total_page};

	function act(val) {

		var obj = document.sortFm;

		switch(val){
    		case -1:
    			obj.page_no.value = 1;
    			break;
    		case -2:
    			obj.page_no.value = (cur_page-1);
    			break;
    		case -3:
    			obj.page_no.value = (cur_page+1);
    			break;
    		case -4:
    			obj.page_no.value = (total_page);
    			break;
    		default:
    			obj.page_no.value = parseInt(val);
		}

		obj.submit();
	}

	function page_row(page_num) {

		var obj = document.sortFm;

		obj.page_num.value = page_num;

		obj.submit();
	}

	function sort_data(val){
		var obj = document.sortFm;
		var curr_sortby = "{$_POST['sortby']}";
		var curr_order = "{$order}";

		obj.sortby.value = val;
		if (obj.sortby.value == curr_sortby)
		{
			obj.order.value = (curr_order == 'desc')?'asc':'desc';
		}else{
			obj.order.value = "desc";
		}
		obj.submit();
	}

	window.onload = function () {
		var obj = document.getElementById('toolbar1');
		var obj2 = document.getElementById('toolbar2');

		obj2.innerHTML = obj.innerHTML;

	};

BOF;

	showXHTML_script('inline', $js);
	showXHTML_head_B($MSG['school_learning'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
	  $ary[] = array($MSG['school_learning'][$sysSession->lang], 'divSettings');
	  echo "<center>\n";
	  showXHTML_tabFrame_B($ary);
	  // showXHTML_tabFrame_B($ary, 3, 'mymanage', '', 'action="mycourse_manage_save.php" method="post" enctype="multipart/form-data" style="display: inline;" onsubmit="return chkSelect();"', false);
	    showXHTML_table_B('id="school_rank_table" border="0" cellpadding="3" cellspacing="1" width="760" class="cssTable"');
	    //呈現更新資訊
	    $lasttime = getCronDailyLastExecuteTime();
		showXHTML_tr_B('class="cssTrEvn"');
		showXHTML_td_B('nowrap colspan="9"');
		if ($lasttime == 0)
		{
			echo '<font color="red">' . $MSG['msg_cron_daily_fail'][$sysSession->lang] . '</font>';
		}else{
			if ($sysSession->username != 'root')
			{
				if (!$NullUserData)
				{
					echo $MSG['title16'][$sysSession->lang].$MSG['msg_last_updated_time'][$sysSession->lang].'<font color="red">'.$lasttime.'</font>';
				}else{
					echo $MSG['title17'][$sysSession->lang].$MSG['msg_last_updated_time'][$sysSession->lang].'<font color="red">'.$lasttime.'</font>';
				}
			}else{
				echo $MSG['msg_last_updated_time'][$sysSession->lang].'<font color="red">'.$lasttime.'</font>';
			}
		}
		if ($sysSession->env == 'learn') echo '<br />', $MSG['msg_learn_limit'][$sysSession->lang];
		showXHTML_td_E();
		showXHTML_tr_E();
		//呈現該名使用者的排名
		$lasttime = getCronDailyLastExecuteTime();
		if (($sysSession->username != sysRootAccount) && (!$NullUserData))
		{
			showXHTML_tr_B('class="cssTrOdd"');
	    	showXHTML_td('nowrap align="center"', $self_rank_value);
	    	showXHTML_td('nowrap', $sysSession->username.'('.$sysSession->realname.')');
	    	showXHTML_td('style="right-padding: 0.8em" align="right" nowrap', $self_data['total_course']);
	    	showXHTML_td('style="right-padding: 0.8em" align="right" nowrap', $self_data['total_grade']);
	    	showXHTML_td('style="right-padding: 0.8em" align="right" nowrap', $self_data['login_times']);
	    	showXHTML_td('style="right-padding: 0.8em" align="right" nowrap', $self_data['post_times']);
	    	showXHTML_td('style="right-padding: 0.8em" align="right" nowrap', $self_data['dsc_times']);
	    	showXHTML_td('style="right-padding: 0.8em" align="right" nowrap', sec2timestamp($self_data['total_readtime']));
	    	showXHTML_td('style="right-padding: 0.8em" align="right" nowrap', $self_data['total_readpages']);
	    	showXHTML_tr_E();
		}
	      showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td_B('colspan="9"  id="toolbar1"');

					echo $MSG['page'][$sysSession->lang] , '&nbsp;';

					$P = range(0, $total_page);
					$P[0] = $MSG['all'][$sysSession->lang];

            		showXHTML_input('select', '', $P, $_POST['page_no'], 'size="1" onchange="act(this.value);" class="cssInput"');

					echo '&nbsp;';

            		$page_array = array(sysPostPerPage=> $MSG['page_default'][$sysSession->lang],20 => 20,50 => 50,100 => 100,200 => 200,400 => 400);

					echo $MSG['page_row'][$sysSession->lang];
            		showXHTML_input('select', '', $page_array, $page_num, 'size="1" onchange="page_row(this.value);" class="cssInput"');

					showXHTML_input('button', 'firstBtn1', $MSG['first'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . (($cur_page==1 			 || $_POST['page_no']==0) ? 'disabled="true" ' : 'onclick="act(-1);"'));
					showXHTML_input('button', 'prevBtn1',  $MSG['prev'][$sysSession->lang],  '', 'id="firstBtn1" class="cssBtn" ' . (($cur_page==1 			 || $_POST['page_no']==0) ? 'disabled="true" ' : 'onclick="act(-2);"'));
					showXHTML_input('button', 'nextBtn1',  $MSG['next'][$sysSession->lang],  '', 'id="firstBtn1" class="cssBtn" ' . (($cur_page==$total_page || $_POST['page_no']==0) ? 'disabled="true" ' : 'onclick="act(-3);"'));
					showXHTML_input('button', 'lastBtn1',  $MSG['last'][$sysSession->lang],  '', 'id="firstBtn1" class="cssBtn" ' . (($cur_page==$total_page || $_POST['page_no']==0) ? 'disabled="true" ' : 'onclick="act(-4);"'));

					showXHTML_td_E('');
		  showXHTML_tr_E('');

	      showXHTML_tr_B('class="cssTrHead "');
	      	showXHTML_td_B('align="center" nowrap');
	      		echo $MSG['title15'][$sysSession->lang];
	      	showXHTML_td_E();

	      	$cour_sort = array('',
							'username',
							'total_course',
							'total_grade',
							'login_times',
							'post_times',
							'dsc_times',
							'total_readtime',
							'total_readpages'
						);

	      	$title_lists = array(1 => $MSG['title1'][$sysSession->lang],
                                 2 => $MSG['learn_courses'][$sysSession->lang],
                                 3 => $MSG['total_average'][$sysSession->lang],
                                 4 => $MSG['total_login_times'][$sysSession->lang],
                                 5 => $MSG['total_posts_times'][$sysSession->lang],
                                 6 => $MSG['total_dsc_times'][$sysSession->lang],
                                 7 => $MSG['total_rss'][$sysSession->lang],
                                 8 => $MSG['total_page'][$sysSession->lang]
                                );

            if ($sysSession->username == 'guest')
            {
            	echo '<td align="center" nowrap>', implode('</td><td align="center" nowrap>', $title_lists), '</td>';
            }	
            else
            {
		      	foreach($title_lists as $k => $v)
		      	{
		      		showXHTML_td_B('align="center" onclick="sort_data(' . $k . ');" nowrap');
		      		echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';

		      		if ($sortby == $cour_sort[$k]){
		      			echo '<b><font class="font04">', $v, ($order == 'desc' ? $icon_dn : $icon_up), '</font></b>';
		      		}else{
		      			echo $v;
		      		}
					echo '</a>';
		      		showXHTML_td_E('');
	            }
			}
		  showXHTML_tr_E();

		  	// 名次
		  	$count = ($cur_page-1)*$page_num+1;

			if ($RS){
				while($fields = $RS->FetchRow()){
					$cln = $cln == 'class="cssTrEvn "' ? 'class="cssTrOdd "' : 'class="cssTrEvn "';
					// 有在排名之內
					if ($sysSession->username == $fields['username']){
				  		$self_ranking = ' id="self_ranking"';
				  	}
				  	showXHTML_tr_B($cln . $self_ranking);
				  	showXHTML_td('align="center" nowrap', $count++);
				  	showXHTML_td('nowrap', $fields['username'].'('.$fields['realname'].')');
					showXHTML_td('style="right-padding: 0.8em" align="right" nowrap', $fields['total_course']);
					showXHTML_td('style="right-padding: 0.8em" align="right" nowrap', $fields['total_grade']);
					showXHTML_td('style="right-padding: 0.8em" align="right" nowrap', $fields['login_times']);
					showXHTML_td('style="right-padding: 0.8em" align="right" nowrap', $fields['post_times']);
					showXHTML_td('style="right-padding: 0.8em" align="right" nowrap', $fields['dsc_times']);
					showXHTML_td('style="right-padding: 0.8em" align="right" nowrap', sec2timestamp($fields['total_readtime']));
					showXHTML_td('style="right-padding: 0.8em" align="right" nowrap', $fields['total_readpages']);
					showXHTML_tr_E();
				}
				// while end
		  	}else{
		    	showXHTML_tr_B('class="cssTrOdd "');
		      		showXHTML_td('colspan="9" align="center"', $MSG['title8'][$sysSession->lang] . $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());
		    	showXHTML_tr_E();
		  	}

		  	$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
			showXHTML_tr_B($col);
				showXHTML_td_B('colspan="9"  id="toolbar2"');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E();
	  showXHTML_tabFrame_E();
	  echo "</center>\n";

	//  排序
	showXHTML_form_B('action="school_ranking.php" method="post" style="display:inline"', 'sortFm');
		showXHTML_input('hidden', 'sortby'  , $_POST['sortby']  , '', '');
		showXHTML_input('hidden', 'order'   , $order   , '', '');
		showXHTML_input('hidden', 'page_no' , $cur_page, '', '');
		showXHTML_input('hidden', 'page_num', $page_num, '', '');
	showXHTML_form_E('');
	showXHTML_body_E();

?>
