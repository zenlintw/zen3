<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/online_chat_list.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/webmeeting/global.php');
	require_once(sysDocumentRoot . '/webmeeting/include/hit_integration.php');
	require_once(sysDocumentRoot . '/breeze/global.php');
#============ function ========
	/**
		取得Breeze Meeting的會議型態
		@name getBreezeMeetingType()
		@param $urlpath : meeting urlpath
	*/
	function getBreezeMeetingType($urlpath)
	{
		list($tmps) = dbGetStSr('WM_chat_mmc','extra',"meetingID like '%{$urlpath}%'", ADODB_FETCH_NUM);
		return $tmps;
	}
#============ Main ============
//var_dump(get_defined_vars());
    $MeetingEnableStatus = 0;	//系統所啟動線上會議的狀態
    if ($Anicam_enable) $MeetingEnableStatus += 1;
    if ($MMC_enable)    $MeetingEnableStatus += 2;
    if ($Breeze_enable) $MeetingEnableStatus += 4;

	$MeetingRuntimeStatus = 0;	//線上會議目前進行的狀態，0是啟始狀態

	if ($Anicam_enable)
	{
		if (isChatroomMMCExists($WM3_Meeting_ID, 'anicam', $foo))
		{
			$MeetingRuntimeStatus += 1;
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

	if ($Breeze_enable)
	{
		$arr = getBreezeMeetingList($sysSession->course_id);
		for($i=0, $size=count($arr); $i < $size; $i++)
		{
			if (trim(getBreezeMeetingType($arr[$i]->urlpath)) == 'eternal') continue;
			$MeetingRuntimeStatus += 4;
			break;
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
			return true;
		}
	}

	// for anicam
	var obj=document.getElementById("chk_anicam");
	if (obj != null)
	{
		obj.disabled =false;
		if (obj.checked)
		{
			document.frmSend.action = "settingwms.php";
			document.frmSend.submit();
			return true;
		}
	}

	// for breeze live
	var obj=document.getElementById("chk_breeze");
	if (obj != null)
	{
		obj.disabled =false;
		if (obj.checked)
		{
			document.frmSend.action = "/breeze/CreateMeeting.php";
			document.frmSend.target = "_blank";
			document.frmSend.submit();
			return true;
		}
	}

	alert("{$MSG['msg_select_sync_model'][$sysSession->lang]}");
	return false;
	// var obj=document.getElementById("meetingTitle");
	// obj.disabled = false;
}

function showOption(val,bl)
{
	var obj1=document.getElementById("div_option1");
	var obj2=document.getElementById("div_option2");
	var obj3=document.getElementById("chk_anicam");
	var obj4=document.getElementById("chk_joinnet");
	var obj5=document.getElementById("chk_breeze");

	if (obj1)	obj1.style.display = "none";
	if (obj2)	obj2.style.display = "none";
	switch(val)
	{
		case 1:
			obj1.style.display = (bl)?"block":"none";
			if ((bl)&&(obj4)) obj4.checked = false;
			if ((bl)&&(obj5)) obj5.checked = false;
			break;
		case 2:
			obj2.style.display = (bl)?"block":"none";
			if ((bl)&&(obj3)) obj3.checked = false;
			if ((bl)&&(obj5)) obj5.checked = false;
			break;
		case 3:
			if ((bl)&&(obj3)) obj3.checked = false;
			if ((bl)&&(obj4)) obj4.checked = false;
			break;
	}
}

BOF;

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
			showXHTML_input('hidden', 'task', "launch", '', '');
			showXHTML_input('hidden', 'sid', $sysSession->school_id, '', '');
			showXHTML_input('hidden', 'cid', $sysSession->course_id, '', '');
			showXHTML_input('hidden', 'meetingId', $meetingId, '', '');
			showXHTML_input('hidden', 'password', '', '', '');
			showXHTML_input('hidden', 'diskQuota', '', '', '');
			showXHTML_input('hidden', 'maxGuest', $o_minfo->MaxGuests, '', '');
			showXHTML_input('hidden', 'duration', $o_minfo->Duration, '', '');
			showXHTML_input('hidden', 'autoExtension', $o_minfo->AutoExtension, '', '');
			showXHTML_input('hidden', 'ownerId', $o_mowner->ID, '', '');
			showXHTML_input('hidden', 'ownerName', $ownerName, '', '');
			showXHTML_input('hidden', 'ownerEmail', $o_mowner->Email, '', '');
			showXHTML_input('hidden', 'ip', $o_mserver->IP, '', '');
			showXHTML_input('hidden', 'portm', $o_mserver->Portm, '', '');
			showXHTML_input('hidden', 'portm2', $o_mserver->Portm2, '', '');
   			showXHTML_input('hidden', 'api_port', $MMC_Server_API_Port, '', '');
			showXHTML_input('hidden', 'api_path', $MMC_Server_API_RootURL, '', '');
			showXHTML_input('hidden', 'CU_CNAME', $sysSession->course_name, '', '');
			showXHTML_input('hidden', 'CU_Recording', 1, '', '');
			showXHTML_input('hidden', 'CUID', BREEZE_SCHOOL_ID.'_'.$sysSession->course_id, '', '');
			showXHTML_input('hidden', 'AV', 3, '', '');
			showXHTML_input('hidden', 'CU_Teacher_NAME', $sysSession->realname, '', '');
			showXHTML_input('hidden', 'CU_Teacher_ID', $sysSession->username, '', '');
			showXHTML_input('hidden', 'protocol', 'TCP', '', '');

    		showXHTML_table_B('width="780" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');
        		showXHTML_tr_B('class="cssTrHead"');
        		showXHTML_td('align="center" width="120"' , $MSG['item'][$sysSession->lang]);
	            showXHTML_td('align="center" width="480"' , $MSG['content'][$sysSession->lang]);
	            showXHTML_td('align="center" width="160"' , $MSG['desc'][$sysSession->lang]);
        		showXHTML_tr_E();

        		$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
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
					echo '<input type="text" name="meetingTitle" id="meetingTitle" size="35" value="'.$o_minfo->Title.'" '.((($MeetingEnableStatus == 1) || ($MeetingRuntimeStatus == 1))?'disabled':'').'>';
					if (($MeetingRuntimeStatus == 1) || ($MeetingRuntimeStatus == 3))
					{
						echo '<font color="red">'.$MSG['Anicam_is_running'][$sysSession->lang].'</font>';
					}
				showXHTML_td_E();
				showXHTML_td('align="center"',$MSG['desc_meeting_title'][$sysSession->lang]);
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
				showXHTML_td('align="center"',$MSG['anchorman'][$sysSession->lang]);
				showXHTML_td('',$sysSession->username.'('.$sysSession->realname.')');
				showXHTML_td('align="center"');
				showXHTML_tr_E('');

				//  是否要使用 Anicam Live 的功能

                if ($Anicam_enable)
                {
					// Bug#1512-將checkbox改為radio by Small 2006/12/5
                	$tmps = sprintf('<input type="radio" name="chk_anicam" id="chk_anicam" value="Y" onClick="%s" %s>',(($MeetingEnableStatus==3)?'showOption(1,this.checked);':''),((($MeetingEnableStatus==1)||($MeetingRuntimeStatus==1))?'checked disabled':'')) .
                	        $MSG['chk_anicam_live'][$sysSession->lang] . '<br>' .
                	        '<div id="div_option1" style="'.((($MeetingEnableStatus==3)&&($MeetingRuntimeStatus != 1))?'display:none':'display:block').'">' .
                	        '&nbsp;&nbsp;&nbsp;<input type="radio" name="SET_AV" value="0">'.$MSG['desc_choose1'][$sysSession->lang].'<br>' .
                	        '&nbsp;&nbsp;&nbsp;<input type="radio" name="SET_AV" value="1" checked>'.$MSG['desc_choose2'][$sysSession->lang].'<br>' .
                	        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$MSG['desc_window_size'][$sysSession->lang] .
                            '&nbsp;&nbsp;&nbsp;<select name="Frame_size">
                              <option value="320*240">320*240</option>
                              <option value="640*480">640*480</option>
                              <option value="800*600" selected>800*600</option>
                              <option value="1024*768">1024*768</option>
                              </select></div>';
                }

    			//  是否要使用 join net 的功能
			    if ($MMC_enable)
			    {
					// Bug#1512-將checkbox改為radio by Small 2006/12/5
                	/*#48382 chrome 教師辦公室-教室管理-影音互動設定-建立新會議時，若有勾選互動模式，但系統卻誤判沒有勾選：增加屬性id，以利js判斷*/
    				$tmps .= '<input name="chk_joinnet" id="chk_joinnet" type="radio" value="Y" '.(($MeetingRuntimeStatus != 1)?'checked':'').' onClick="'.(($MMC_enable)?'showOption(2,this.checked);':'').'"'.(($MeetingRuntimeStatus==1)?' disabled':'').'>'.$MSG['desc_choose3'][$sysSession->lang].'<br>'.
    				         '<div id="div_option2" style="display:block">'.
					         '&nbsp;&nbsp;&nbsp;'.$MSG['title26'][$sysSession->lang].'<input type="radio" name="recording" value="1">YES'.
					         '<input type="radio" name="recording" value="0" checked>NO<br>'.
					         '</div>';
    			}

    			//  是否要使用 Breeze live 的功能
			    if ($Breeze_enable)
			    {
					// Bug#1512-將checkbox改為radio by Small 2006/12/5
    				$tmps .= '<input name="chk_breeze" id="chk_breeze" type="radio" value="Y" '.(($MeetingEnableStatus == 4)?'checked':'').' onClick="'.(($Breeze_enable)?'showOption(3,this.checked);':'').'">'.$MSG['desc_choose4'][$sysSession->lang].'<br>'.
    				         '<div id="div_option3" style="display:block">'.
					         '&nbsp;&nbsp;&nbsp;'.$MSG['label_breeze_meetype'][$sysSession->lang].'<br>'.
					         '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="breeze_meetype" value="1" checked>'.$MSG['rdo_breeze_meetype1'][$sysSession->lang].'<br>'.
					         '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="breeze_meetype" value="2">'.$MSG['rdo_breeze_meetype2'][$sysSession->lang].
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
				// MIS#18114 by Small 20100914
				/*
				$tmps = '<table>'.
				         '<tr '.$col.'><td valign="top">1.</td><td>'.$MSG['title17_0'][$sysSession->lang].'</td></tr>'.
				         '<tr '.$col.'><td valign="top">2.</td><td>'.$MSG['title17_1'][$sysSession->lang].'</td></tr>'.
				         '<tr '.$col.'><td valign="top">3.</td><td>'.$MSG['title17_2'][$sysSession->lang].'</td></tr>'.
				         '</table>';
				*/
				$tmps = '<table>'.
				         '<tr '.$col.'><td valign="top">1.</td><td>'.$MSG['title17_2'][$sysSession->lang].'</td></tr>'.
				         '</table>'; 
      
				showXHTML_td('colspan="2"',$tmps);
				showXHTML_tr_E('');
				switch($MeetingRuntimeStatus)
				{
					case 0:
					case 4:
						$tmps = '<input type="button" name="SubBtn" value="'.$MSG['newmeeting'][$sysSession->lang].'" class="box01" onClick="this.disabled=true;if (!entroom()){this.disabled=false;}">';
						break;
					case 1:
						$tmps = '<input type="button" name="SubBtn" value="'.$MSG['cancel_anicam_running'][$sysSession->lang].'" class="box01" onClick="this.form.task.value=\'cancel\';this.disabled=true;entroom();">';
						break;
					case 2:
						$tmps = '<font size="+1">'.$MSG['meeting_running'][$sysSession->lang].'</font>';
						break;
					case 3:
						$tmps = '<font size="+1">'.$MSG['joinnet_meeting_running'][$sysSession->lang].'</font><br>';
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
