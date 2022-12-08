<?php
	/**
	 * 到課統計詳細資料
	 * $Id: stud_detail.php,v 1.1 2010/02/24 02:40:30 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teach_student.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$course_id = defined('Course_ID') ? Course_ID : $sysSession->course_id;
	$sysSession->cur_func='1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	switch ($_GET['type']){
		case 1:
			$sqls = "select log_time,remote_address from WM_log_others where username='".$_GET['user']."' and note='login success' order by log_time desc limit 0,10";
			break;
		case 2:
			$sqls = "select log_time,remote_address from WM_log_classroom where username='".$_GET['user']."' and note='Goto course course_id=".$course_id."' order by log_time desc limit 0,10";
			break;
		case 3:
			$sqls = "select a.pt,b.bname,a.subject from WM_bbs_boards as b join WM_bbs_posts as a on a.board_id=b.board_id where Left(b.owner_id, 8) = '".$course_id."' and a.poster = '" . $_GET['user'] . "' order by a.pt desc limit 0,10";
			break;
		case 4:
			$sqls = 'select activity_id,sum(UNIX_TIMESTAMP(over_time)-UNIX_TIMESTAMP(begin_time)+1) as st '.
					"from WM_record_reading where course_id='".$course_id."' and username='".$_GET['user']."' ".
					'group by activity_id order by st DESC limit 0,10';
			break;
		case 5:
			$sqls = 'select activity_id,count(*) as times '.
					"from WM_record_reading where course_id='".$course_id."' and username='".$_GET['user']."' ".
					'group by activity_id order by times DESC limit 0,10';
			break;
	}

	showXHTML_head_B($MSG['student_sysbar'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
	  $head = ($_GET['type'] == 4 || $_GET['type'] == 5)?$_GET['user'].$MSG['learn_10'][$sysSession->lang]:$_GET['user'].$MSG['last_10'][$sysSession->lang];
	  $ary[] = array($head, 'divSettings');
	  echo "<center>\n";
	  showXHTML_tabFrame_B($ary);
	    showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="640" style="border-collapse: collapse" class="cssTable"');
	      showXHTML_tr_B('class="cssTrHead font01"');

		      	showXHTML_td_B('align="center" style="font-weight: bold" title=""');
		      		$title1 = ($_GET['type'] == 4 || $_GET['type'] == 5)?$MSG['learn_page'][$sysSession->lang]:$MSG['login_time'][$sysSession->lang];
		      		echo $title1;
		      	showXHTML_td_E('');

				showXHTML_td_B('align="center" style="font-weight: bold" title=""');
					switch (intval($_GET['type']))
					{
						case 3 : echo $MSG['board_name'][$sysSession->lang] ; break;
						case 4 : echo $MSG['learn_time'][$sysSession->lang] ; break;
						case 5 : echo $MSG['learn_times'][$sysSession->lang]; break;
						default: echo $MSG['login_host'][$sysSession->lang] ;
					}
				showXHTML_td_E('');

			if($_GET['type'] == 3){
				showXHTML_td('align="center" style="font-weight: bold" title=""', $MSG['subject'][$sysSession->lang]);
			}
		showXHTML_tr_E();

		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		chkSchoolId('WM_record_reading');
		$RS = $sysConn->Execute($sqls);
		if($RS)
		while (!$RS->EOF){
			if($_GET['type']==3){
				list($val1,$val2,$val3) = $RS->FetchRow();
				$filename_lang = unserialize($val2);
				$val2 = $filename_lang[$sysSession->lang];
			}else{
				list($val1,$val2) = $RS->FetchRow();
			}
			$cln = $cln == 'class="cssTrEvn font01"' ? 'class="cssTrOdd font01"' : 'class="cssTrEvn font01"';
			showXHTML_tr_B($cln);
				if(($_GET['type'] == 4)||($_GET['type'] == 5)){
					list($val1)=dbGetStSr('WM_record_reading','title'," course_id={$course_id} and username='{$_GET['user']}'  and activity_id = '{$val1}' group by title order by over_time desc");
				}
				showXHTML_td('align="center" nowrap', $val1);
					
				showXHTML_td('align="center" nowrap', ($_GET['type']==4)?zero2gray(sec2timestamp(intval($val2))):$val2);
				if($_GET['type'] == 3) showXHTML_td('align="center" nowrap', $val3);
			showXHTML_tr_E();
		}
		
		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td_B('colspan="3" align="center"');
				showXHTML_input('button', '', $MSG['close'][$sysSession->lang]       , '', 'onclick="window.close();" class="cssBtn"');
				showXHTML_input('button', '', $MSG['download_all'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\'stud_detail_download.php?type='.$_GET['type'].'&user='.$_GET['user'].'\');"');
			showXHTML_td_E();
		showXHTML_tr_E();
		
		showXHTML_table_E();
	  showXHTML_tabFrame_E();
	  echo "</center>\n";
	showXHTML_body_E();

?>
