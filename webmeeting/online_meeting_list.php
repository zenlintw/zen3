<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/online_chat_list.php');
	require_once(sysDocumentRoot . '/webmeeting/include/hit_integration.php');
	require_once(sysDocumentRoot . '/webmeeting/global.php');
#======= class & functions ==============
	class mcustatususer
	{
		var $ip;
		var $name;
		var $userId;
		var $meetingId;
		var $preparationMode;
	}
	
	function getMeetingTitle($id)
	{
		list($rtns) = dbGetStSr('WM_chat_mmc', 'title', "meetingID='{$id}'", ADODB_FETCH_NUM);
		return $rtns;
	}
	
	function print_list()
	{
		global $MMC_Server_addr, $MMC_Server_API_Port, $MMC_Server_API_RootURL;
		global $MSG, $sysSession, $WM3_Meeting_Owner;
		
		$rtns = '';
		$list = get_online_meeting_list($MMC_Server_addr, $MMC_Server_API_Port, $MMC_Server_API_RootURL);
		$arr = unserialize(base64_decode($list));
		if (count($arr) == 0) return '';
		
	
		for($i=0; $i<count($arr); $i++)
		{
			$status = $arr[$i];
			if (strpos($status->userId, $WM3_Meeting_Owner) === FALSE) continue;
			$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
			showXHTML_tr_B($col);
			showXHTML_td('nowrap',getMeetingTitle($status->meetingId));
			showXHTML_td('',$status->name);
			showXHTML_td('',getMeetingName($status->userId, $status->meetingId));
			showXHTML_td('align="center"','<input type="button" name="btn_join" value="'.$MSG['title24'][$sysSession->lang].'" onClick="joinMeeting(\''.$status->meetingId.'\',\''.$status->userId.'\',\''.$status->name.'\')" class="cssBtn">');
			showXHTML_tr_E('');
		}
	}
#============ Main ============
	$js = <<< BOF
	function joinMeeting(m,u,n)
	{
		document.frmMeeting.meetingId.value = m;
		document.frmMeeting.ownerId.value   = u;
		document.frmMeeting.ownerName.value = n;
		document.frmMeeting.submit();
	}
BOF;


#======= html output ==============
showXHTML_head_B('Online Meeting');
showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
showXHTML_script('inline', $js);
showXHTML_head_E('');
showXHTML_body_B('');
showXHTML_table_B('align="center" border="0" cellpadding="0" cellspacing="0" width="780" style="border-collapse: collapse"');
	showXHTML_tr_B();
    	showXHTML_td_B();
        	$ary[] = array($MSG['online_chat'][$sysSession->lang], 'tabsSet',  '');
        	showXHTML_tabs($ary, 1);
      	showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_tr_B();
		showXHTML_td_B();
		showXHTML_table_B('width="780" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');
        		showXHTML_tr_B('class="cssTrHead"');
        		showXHTML_td('align="center" width="120"' , $MSG['title25'][$sysSession->lang]);
        		showXHTML_td('align="center" width="120"' , $MSG['anchorman'][$sysSession->lang]);
	            showXHTML_td('align="center" width="480"' , $MSG['title21'][$sysSession->lang]);
	            showXHTML_td('align="center" width="160"' , $MSG['title28'][$sysSession->lang]);
        		showXHTML_tr_E();
        		print_list();
        showXHTML_table_E();
    	showXHTML_td_E();
    showXHTML_tr_E();
showXHTML_table_E();
showXHTML_form_B('method="post" action="/webmeeting/joinmeeting_confirm.php"', 'frmMeeting');
	showXHTML_input('hidden', 'task'     , 'launch');
	showXHTML_input('hidden', 'meetingId');
	showXHTML_input('hidden', 'ownerName');
	showXHTML_input('hidden', 'ownerId'  );
	showXHTML_input('hidden', 'duration' , 0);
	showXHTML_input('hidden', 'password' );
	showXHTML_input('hidden', 'ip'       , $o_mserver->IP       );
	showXHTML_input('hidden', 'portm'    , $o_mserver->Portm    );
	showXHTML_input('hidden', 'portm2'   , $o_mserver->Portm2   );
	showXHTML_input('hidden', 'guestId'  , $sysSession->username);
	showXHTML_input('hidden', 'guestName', $sysSession->realname);
	showXHTML_input('hidden', 'invited'  , 1);
showXHTML_form_E();
showXHTML_body_E();
?>
