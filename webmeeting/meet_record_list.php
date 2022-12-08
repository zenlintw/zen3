<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/online_chat_list.php');
	/*
	$tid = $_SERVER['argv'][0];
	$gid = $_SERVER['argv'][1];
	$bid = $_SERVER['argv'][2];
	*/
	require_once(sysDocumentRoot . '/webmeeting/include/hit_integration.php');
	require_once(sysDocumentRoot . '/webmeeting/global.php');
#======= class & functions ==============

	function print_reclist()
	{
		global $MMC_Server_addr, $MMC_Server_API_Port, $MMC_Server_API_RootURL;
		global $MSG, $sysSession, $WM3_Meeting_Owner;
		global $sysSession;
		$list = get_record_list($MMC_Server_addr, $MMC_Server_API_Port, $MMC_Server_API_RootURL, $WM3_Meeting_Owner);
		
		$arr = unserialize(base64_decode($list));
		if (!is_array($arr)) return '';
		if (count($arr) == 0) return '';
		foreach($arr as $ownerId => $meetArr)
		{
			for($i=0; $i<count($meetArr); $i++)
			{
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $meetArr[$i]['meeting-id']);
					showXHTML_td('', $meetArr[$i]['title']);
					showXHTML_td('', $meetArr[$i]['participants'][$ownerId]['display-name']);
					showXHTML_td('', date('Y-m-d H:i:s', $meetArr[$i]['time']));
					showXHTML_td('align="right" style="padding-right: 0.8em"', zero2gray(sec2timestamp($meetArr[$i]['duration'])) );
					showXHTML_td('align="center"', '<input type="button" name="btnPlay" value="'.$MSG['title8'][$sysSession->lang].'" onClick="PlayRec(\''.$WM3_Meeting_Owner.'\',\''.$meetArr[$i]['meeting-id'].'\',\''.$meetArr[$i]['recording-id'].'\')" class="cssBtn">');
					showXHTML_td('align="center"', '<input type="button" name="btnDownload" value="'.$MSG['title9'][$sysSession->lang].'" onClick="DownloadRec(\''.$WM3_Meeting_Owner.'\',\''.$meetArr[$i]['meeting-id'].'\',\''.$meetArr[$i]['recording-id'].'\')" class="cssBtn">');
					if ($sysSession->env == 'teach')
					{
						showXHTML_td('align="center"','<input type="button" name="btnRemove" value="'.$MSG['title10'][$sysSession->lang].'" onClick="RemoveRec(\''.$WM3_Meeting_Owner.'\',\''.$meetArr[$i]['meeting-id'].'\',\''.$meetArr[$i]['recording-id'].'\')" class="cssBtn">');
					}
				showXHTML_tr_E();
			}
		}
	}

#============ Main ============
	$js = <<< BOF
    function PlayRec(o, m, r)
	{
		document.frmMeeting.meetingId.value = m;
		document.frmMeeting.ownerId.value = o;
		document.frmMeeting.recordingId.value = r;
		document.frmMeeting.submit();
	}

	function DownloadRec(o, m, r)
	{
		document.frmDownload.meetingId.value = m;
		document.frmDownload.ownerId.value = o;
		document.frmDownload.recordingId.value = r;
		document.frmDownload.submit();
	}
BOF;

if ($sysSession->env == 'teach')
{
	$js .= <<< BOF
	
	function refreshThisWindow()
	{
		this.window.location.reload();
	}
	
	function RemoveRec(o, m, r)
	{
		if (confirm("{$MSG['delete_confirm'][$sysSession->lang]}"))
		{
			var div1 = document.getElementById('tbl_message');
			var div2 = document.getElementById('tbl_list');
			div1.style.display = 'block';
			div2.style.display = 'none';
			
			window.setTimeout('refreshThisWindow()', 1500);
			
			document.frmRemove.meetingId.value = m;
			document.frmRemove.ownerId.value = o;
			document.frmRemove.recordingId.value = r;
			document.frmRemove.submit();
		}
	}
BOF;
}

#======= html output ==============
showXHTML_head_B('Online Meeting');
showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
showXHTML_script('inline', $js);
showXHTML_head_E('');
showXHTML_body_B('');
showXHTML_table_B('align="center" border="0" cellpadding="0" cellspacing="0" width="90%" style="border-collapse: collapse"');
	showXHTML_tr_B();
    	showXHTML_td_B();
        	$ary[] = array($MSG['meet_record_list'][$sysSession->lang], 'tabsSet',  '');
        	showXHTML_tabs($ary, 1);
      	showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_tr_B();
		showXHTML_td_B('');
		/*#48381 chrome 教師辦公室-教室管理-歷史會議錄影列表-預設「刪除錄影中請稍後」的訊息應隱藏：一次寫了兩個style標籤，合併為一個*/
        showXHTML_table_B('id="tbl_message" width="100%" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse; display:none" class="cssTable"');
        		showXHTML_tr_B('class="cssTrHead"');
        		showXHTML_td('align="center"' , $MSG['deleting_record_list'][$sysSession->lang]);
		showXHTML_table_E();
		showXHTML_table_B('id="tbl_list" width="100%" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable" style="display:block"');
			showXHTML_tr_B('class="cssTrHead"');
				showXHTML_td('align="center"' , $MSG['title21'][$sysSession->lang]);
				showXHTML_td('align="center"' , $MSG['title25'][$sysSession->lang]);
				showXHTML_td('align="center"' , $MSG['anchorman'][$sysSession->lang]);
				showXHTML_td('align="center"' , $MSG['title2'][$sysSession->lang]);
				showXHTML_td('align="center"' , $MSG['title7'][$sysSession->lang]);
				showXHTML_td('align="center"' , $MSG['title8'][$sysSession->lang]);
				showXHTML_td('align="center"' , $MSG['title4'][$sysSession->lang]);
				if ($sysSession->env == 'teach')
				{
					showXHTML_td('align="center"' , $MSG['title5'][$sysSession->lang]);
				}
			showXHTML_tr_E();
			print_reclist();
		showXHTML_table_E();
		showXHTML_td_E();
    showXHTML_tr_E();
showXHTML_table_E();
showXHTML_form_B('method="post" action="/webmeeting/playrecording.php"', 'frmMeeting');
	showXHTML_input('hidden', 'task'       , 'launch');
	showXHTML_input('hidden', 'ownerId'    );
	showXHTML_input('hidden', 'meetingId'  );
	showXHTML_input('hidden', 'recordingId');
	showXHTML_input('hidden', 'password'   );
	showXHTML_input('hidden', 'ip'         , $MMC_Server_addr );
	showXHTML_input('hidden', 'portm'      , $MCU_Server_port );
	showXHTML_input('hidden', 'portm2'     , $MCU_Server_port1);
showXHTML_form_E();

$downURL = sprintf('http://%s/TinyMMC/download_recording.php',$MMC_Server_addr);

showXHTML_form_B('method="post" action="'.$downURL.'"', 'frmDownload');
	showXHTML_input('hidden', 'ownerId'   );
	showXHTML_input('hidden', 'meetingId'  );
	showXHTML_input('hidden', 'recordingId');
showXHTML_form_E();

if ($sysSession->env == 'teach')
{
$RemoveURL = sprintf('http://%s/TinyMMC/remove_recording.php',$MMC_Server_addr);
showXHTML_form_B('method="post" action="'.$RemoveURL.'" target="_blank"', 'frmRemove');
	showXHTML_input('hidden', 'seed'       );
	showXHTML_input('hidden', 'ownerId'    );
	showXHTML_input('hidden', 'meetingId'  );
	showXHTML_input('hidden', 'recordingId');
showXHTML_form_E();
}

showXHTML_body_E();
?>
