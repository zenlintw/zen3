<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/online_chat_list.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$tid = $_SERVER['argv'][0];
	$gid = $_SERVER['argv'][1];
	$bid = $_SERVER['argv'][2];
	require_once(sysDocumentRoot . '/webmeeting/global.php');
	require_once(sysDocumentRoot . '/webmeeting/include/hit_integration.php');
#============ Function ============================

function getGroupName($t, $g)
{
	global $sysSession;
	$t = intval($t);
	$g = intval($g);
	list($cp) = dbGetStSr('WM_student_group', 'caption', "course_id={$sysSession->course_id} and team_id={$t} and group_id={$g}", ADODB_FETCH_NUM);
	$cp_lang = unserialize($cp);
	return $cp_lang[$sysSession->lang];

}
#============ Main ============
//var_dump(get_defined_vars());

    $MeetingEnableStatus = 0;	//系統所啟動線上會議的狀態
    if ($Anicam_enable) $MeetingEnableStatus += 1;
    if ($MMC_enable)    $MeetingEnableStatus += 2;

	$MeetingRuntimeStatus = 0;	//線上會議目前進行的狀態，0是啟始狀態

	if ($Anicam_enable)
	{
		if (isChatroomMMCExists($WM3_Meeting_ID, 'anicam', $foo))
		{
			$MeetingRuntimeStatus++;
		}
	}

	if ($MMC_enable)
	{
		$online_meeting_info = get_online_meeting($MMC_Server_addr, $MMC_Server_API_Port, $MMC_Server_API_RootURL, $WM3_Meeting_Owner);
		if (strcmp($online_meeting_info,'0') != 0)
		{
			list($meetingId, $ownerName) = explode(':',$online_meeting_info);
			$MeetingRuntimeStatus += 2;		//已啟動joinnet
		}else{
			$meetingId = $o_minfo->ID;
			$ownerName = $o_mowner->Name;
		}

	}

#======= html output ==============
	$js = <<< BOF
function entroom()
{
	// for joinnet
	var obj=document.getElementById("chk_joinnet");
	if (obj != null)
	{
		obj.disabled =false;
		if (obj.checked)
		{
			document.frmSend.action = "newmeeting_confirm.php";
			document.frmSend.submit();
			return;
		}
	}

	var obj=document.getElementById("meetingTitle");
	obj.disabled = false;
}

function showOption(val,bl)
{
	var obj1=document.getElementById("div_option1");
	var obj2=document.getElementById("div_option2");
	var obj3=document.getElementById("chk_anicam");
	var obj4=document.getElementById("chk_joinnet");

	obj1.style.display = "none";
	obj2.style.display = "none";

	switch(val)
	{
		case 1:
			obj1.style.display = (bl)?"block":"none";
			if (bl)	obj4.checked = false;
			break;
		case 2:
			obj2.style.display = (bl)?"block":"none";
			obj3.checked = false;
			break;
	}
}

BOF;

$meetingTitle = getGroupName($tid, $gid);

showXHTML_head_B('Online Meeting');
showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
showXHTML_script('inline', $js);
showXHTML_head_E('');
showXHTML_body_B('');
showXHTML_table_B('align="center" border="0" cellpadding="0" cellspacing="0" width="780" style="border-collapse: collapse"');
	showXHTML_tr_B();
    	showXHTML_td_B();
        	$ary[] = array($MSG['Caption'][$sysSession->lang], 'tabsSet',  '');
        	showXHTML_tabs($ary, 1);
      	showXHTML_td_E();
    showXHTML_tr_E();
	showXHTML_tr_B();
		showXHTML_td_B();
			showXHTML_form_B('method="post" action="" onsubmit="return chkData(this);"', 'frmSend');
			showXHTML_input('hidden', 'task'     , 'launch');
			showXHTML_input('hidden', 'sid'      , $sysSession->school_id);
			showXHTML_input('hidden', 'cid'      , $sysSession->course_id . '_' . $tid . '_' . $gid);
			showXHTML_input('hidden', 'meetingId', $meetingId);
			showXHTML_input('hidden', 'password');
			showXHTML_input('hidden', 'diskQuota');
			showXHTML_input('hidden', 'maxGuest'       , $o_minfo->MaxGuests);
			showXHTML_input('hidden', 'duration'       , $o_minfo->Duration);
			showXHTML_input('hidden', 'autoExtension'  , $o_minfo->AutoExtension);
			showXHTML_input('hidden', 'ownerId'        , $o_mowner->ID);
			showXHTML_input('hidden', 'ownerName'      , $ownerName);
			showXHTML_input('hidden', 'ownerEmail'     , $o_mowner->Email);
			showXHTML_input('hidden', 'ip'             , $o_mserver->IP);
			showXHTML_input('hidden', 'portm'          , $o_mserver->Portm);
			showXHTML_input('hidden', 'portm2'         , $o_mserver->Portm2);
   			showXHTML_input('hidden', 'api_port'       , $MMC_Server_API_Port);
			showXHTML_input('hidden', 'api_path'       , $MMC_Server_API_RootURL);
			showXHTML_input('hidden', 'CU_CNAME'       , $sysSession->course_name);
			showXHTML_input('hidden', 'CU_Recording'   , 1);
			showXHTML_input('hidden', 'CUID'           , $_GET['CUID']);
			showXHTML_input('hidden', 'AV'             , 3);
			showXHTML_input('hidden', 'CU_Teacher_NAME', $sysSession->realname);
			showXHTML_input('hidden', 'CU_Teacher_ID'  , $sysSession->username);
			showXHTML_input('hidden', 'protocol'       , 'TCP');

    		showXHTML_table_B('width="780" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');
        		showXHTML_tr_B('class="cssTrHead"');
        		showXHTML_td('align="center" width="120"' , $MSG['item'][$sysSession->lang]);
	            showXHTML_td('align="center" width="480"' , $MSG['content'][$sysSession->lang]);
	            showXHTML_td('align="center" width="160"' , $MSG['desc'][$sysSession->lang]);
        		showXHTML_tr_E();

        		$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
				showXHTML_td('align="center"',$MSG['title21'][$sysSession->lang]);
				showXHTML_td('',(empty($meetingId)?$WM3_Meeting_ID:$meetingId));
				showXHTML_td('align="center"',$MSG['desc_meeting_id'][$sysSession->lang]);
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
				showXHTML_td('align="center"',$MSG['title25'][$sysSession->lang]);
				showXHTML_td_B();
					echo '<input type="text" name="meetingTitle" size="35" value="'.$meetingTitle.'" '.((($MeetingEnableStatus == 1) || ($MeetingRuntimeStatus == 1))?'disabled':'').'>';
					if (($MeetingRuntimeStatus == 1) || ($MeetingRuntimeStatus == 3))
					{
						echo '<font color="red">'.$MSG['Anicam_is_running'][$sysSession->lang].'</font>';
					}
				showXHTML_td_E();
				showXHTML_td('align="center"',$MSG['desc_group_meeting_title'][$sysSession->lang]);
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
				showXHTML_td('align="center"',$MSG['anchorman'][$sysSession->lang]);
				showXHTML_td('',$sysSession->username.'('.$sysSession->realname.')');
				showXHTML_td('align="center"');
				showXHTML_tr_E('');


    			//  是否要使用 join net 的功能
			    if ($MMC_enable)
			    {
    				$tmps .= '<input name="chk_joinnet" type="checkbox" value="Y" '.(($MeetingRuntimeStatus != 1)?'checked':'').' onClick="'.(($MeetingEnableStatus==3)?'showOption(2,this.checked);':'').'"'.(($MeetingRuntimeStatus==1)?' disabled':'').'>'.$MSG['desc_choose3'][$sysSession->lang].'<br>' .
    				         '<div id="div_option2" style="display:block">' .
					         '&nbsp;&nbsp;&nbsp;'.$MSG['title26'][$sysSession->lang].'<input type="radio" name="recording" value="1" checked>YES' .
					         '<input type="radio" name="recording" value="0">NO<br>' .
					         '</div>';
    			}

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
				showXHTML_td('align="center" valign="top"',$MSG['title16'][$sysSession->lang]);
				showXHTML_td('',$tmps);
				showXHTML_td('align="center"');
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
				showXHTML_td('align="center" valign="top"',$MSG['desc'][$sysSession->lang]);
				$tmps = '<table>'.
				         '<tr '.$col.'><td valign="top">1.</td><td>'.$MSG['title17_2'][$sysSession->lang].'</td></tr>'.
				         '</table>';
				showXHTML_td('colspan="2"',$tmps);
				showXHTML_tr_E('');

				switch($MeetingRuntimeStatus)
				{
					case 0:
						$tmps = '<input type="button" name="SubBtn" value="'.$MSG['newmeeting'][$sysSession->lang].'" class="box01" onClick="this.disabled=true;entroom();">';
						break;
					case 1:
						$tmps = '<input type="button" name="SubBtn" value="'.$MSG['cancel_anicam_running'][$sysSession->lang].'" class="box01" onClick="this.form.task.value=\'cancel\';this.disabled=true;entroom();">';
						break;
					case 2:
						$tmps = '<font size="+1">'.$MSG['meeting_running'][$sysSession->lang].'</font>';
						break;
					case 3:
						$tmps = '<font size="+1">'.$MSG['meeting_running'][$sysSession->lang].'</font><br>';
						$tmps = '<input type="button" name="SubBtn" value="'.$MSG['cancel_anicam_running'][$sysSession->lang].'" class="box01" onClick="this.form.task.value=\'cancel\';this.disabled=true;entroom();">';
						break;
				}

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
				showXHTML_td('colspan="3" align="center"',$tmps);
				showXHTML_tr_E('');
    		showXHTML_table_E();
    		showXHTML_form_E();
    	showXHTML_td_E();
    showXHTML_tr_E();
showXHTML_table_E();
showXHTML_body_E();
?>
