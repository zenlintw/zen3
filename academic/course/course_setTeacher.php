<?php
   /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Jeff Wang                                                                         *
	*		Creation  : 2004/11/19                                                                      *
	*		work for  :                                                                       *
	*       $Id: course_setTeacher.php                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/course_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/character_class.php');
	$sysSession->cur_func='800300100';
	$sysSession->restore();

	if (!aclVerifyPermission(800300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}

#=========== Function =================
	// 取得目前教師列表
	function getTeahcerListHTML($col)
	{
		global $csid, $MSG, $sysSession, $sysRoles;
		$i = 1; $rtn = '';
		$members = WMteacher::listMember($csid);
		foreach($members as $username => $role) {
			$col   = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
			$udata = getUserDetailData($username);
			$rtns .= '<tr class="' . $col . '">' .
			         '<td align="center">' . ($i++)             . '</td>' .
			         '<td align="center">' . $username          . '</td>' .
			         '<td align="center">' . $udata['realname'] . '</td>' .
			         '<td align="center">' . $MSG[array_search($role, $sysRoles)][$sysSession->lang] . '</td>' .
			         '<td align="center"><input type="button" name="btn_delete" value="' . $MSG["btn_delete_teacher"][$sysSession->lang] . '" onClick="doDelete(\''.$username.'\',\'' . $role . '\');" class="cssBtn"></td>';
			         '</tr>';
		}
		return $rtns;
    }

    //此帳號是否存在
    function isAccountExisted($user)
    {
    	list($count) = dbGetStSr("WM_user_account","count(*) as ct","username='{$user}'", ADODB_FETCH_NUM);
    	return $count ? true : false;
    }

    //此帳號是否為教師身份
    function isTeacher($user, $csid)
    {
    	global $sysRoles;
    	return aclCheckRole($user, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $csid);
    }

#=========== MAIN    =================
	$icon_up = '<img src="/theme/default/academic/dude07232001up.gif" border="0" align="absmiddl">';
	$icon_dn = '<img src="/theme/default/academic/dude07232001down.gif" border="0" align="absmiddl">';
	$title = $MSG['add_teacher'][$sysSession->lang];
	$server_response = '';

	if (!empty($_POST['ticket']))
	{
		$ticket = md5($sysSession->ticket . 'Create' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
		if (trim($_POST['ticket']) == $ticket)
		{
			if (isAccountExisted($_POST['username']))
			{
				if ($_POST['op'] == 'delete')
				{
					WMteacher::remove($_POST['username'], $sysRoles[$_POST['del_level']], intval($_POST['csid']));
					wmSysLog('300100200', $sysSession->school_id , 0, 0, 'manager',$_SERVER['PHP_SELF'], 'remove teacher (username, course_id)=('.$_POST['username'].','.$_POST['del_level'].')');
					$server_response = $MSG['server_response4'][$sysSession->lang];
				}else{
					if (!isTeacher($_POST['username'], $_POST['csid']))
					{
						WMteacher::assign($_POST['username'], $sysRoles[$_POST['level']], intval($_POST['csid']));
						wmSysLog('300100100', $sysSession->school_id , 0, 0, 'manager',$_SERVER['PHP_SELF'], 'assign teacher(username, course_id, level)=('.$_POST['username'].','.intval($_POST['csid']).','.$_POST['level'].')');
						$server_response = $MSG['server_response1'][$sysSession->lang];
					}else{
						$server_response = $MSG['server_response3'][$sysSession->lang];
					}
				}

			}else{
				$server_response = $MSG['server_response2'][$sysSession->lang];
			}
		}
	}

	$actType = 'Create';
	$csid = (!empty($_GET['csid']))? intval($_GET['csid']) : intval($_POST['csid']);

//製作Script
	$js = <<< BOF

	function init()
	{
		if (document.forms[0].server_response.value.length > 0)
		{
			alert(document.forms[0].server_response.value);
			document.forms[0].server_response.value = '';

		}
	}

	function valid()
	{
		obj = document.getElementById("username");
		if (obj.value.length == 0)
		{
			alert("{$MSG['alert_error'][$sysSession->lang]}");
			return false;
		}
		document.forms[0].submit();
	}


	function doDelete(user,role)
	{
		if (confirm("{$MSG['confirm_delete'][$sysSession->lang]}"))
		{
			document.forms[0].op.value = 'delete';
			document.forms[0].username.value = user;
			document.forms[0].del_level.value = role;
			document.forms[0].submit();
		}
	}

BOF;

	//輸出表格
	showXHTML_head_B($title);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B(' onLoad="init();"');
	showXHTML_table_B('border="0" width="600" cellspacing="0" cellpadding="0"  id="ListTable"');
		showXHTML_tr_B('');
			showXHTML_td_B('');
				$ary[] = array($title, 'tabs');
				showXHTML_tabs($ary, 1);
			showXHTML_td_E('');
		showXHTML_tr_E('');

        $col = 'cssTrEvn';

		showXHTML_tr_B('');
			showXHTML_td_B('valign="top" id="CGroup" ');
				showXHTML_form_B('method="post" action="course_setTeacher.php" style="display:inline;" ', 'actForm');
				$ticket = md5($sysSession->ticket . $actType . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
				showXHTML_input('hidden', 'ticket'         , $ticket         , '', '');
				showXHTML_input('hidden', 'csid'           , $csid           , '', '');
				showXHTML_input('hidden', 'op'             , ''              , '', '');
				showXHTML_input('hidden', 'del_level'      , ''              , '', '');
				showXHTML_input('hidden', 'server_response', $server_response, '', '');
				showXHTML_table_B('id ="mainTable" width="600" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

				$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
				showXHTML_tr_B('class="'. $col . '"');
   					showXHTML_td_B('colspan="5"');
   						echo $MSG['td_course_name'][$sysSession->lang] . ':&nbsp;&nbsp;';
   						$cname = WMteacher::getLocaleCaption(array($csid));
   						echo $cname[$csid]; 
   					showXHTML_td_E('');
   				showXHTML_tr_E('');

				$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
				showXHTML_tr_B('class="'. $col . '"');
   					showXHTML_td_B('colspan="5"');
   					    echo $MSG['theading_account'][$sysSession->lang] . '&nbsp;&nbsp;';
                              showXHTML_input('text', 'username', $tmp2, '', 'id="username" size="20" width="30" class="cssInput"');
                              echo $MSG['status'][$sysSession->lang], '&nbsp;&nbsp;',
                                   '<select name="level" class="cssInput">',
                                   '<option value="teacher">'   , $MSG['teacher'][$sysSession->lang]   , '</option>',
                                   '<option value="assistant">' , $MSG['assistant'][$sysSession->lang] , '</option>',
                                   '<option value="instructor">', $MSG['instructor'][$sysSession->lang], '</option>',
                                   '</select>';
                        showXHTML_input('button', 'button', $MSG['btn_add_teacher'][$sysSession->lang], '', 'onClick="valid();"');
					showXHTML_td_E('');
				   showXHTML_tr_E('');

				$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

				showXHTML_tr_B('class="'. $col . '"');
				   showXHTML_td('align="center" nowrap colspan="5"', $MSG['teacher_list'][$sysSession->lang]);
				showXHTML_tr_E('');

				$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

                showXHTML_tr_B('class="'. $col . '"');
					showXHTML_td('align="center" nowrap ', $MSG['theading_seq'][$sysSession->lang]);
					showXHTML_td('align="center" nowrap ', $MSG['theading_account'][$sysSession->lang]);
					showXHTML_td('align="center" nowrap ', $MSG['theading_realname'][$sysSession->lang]);
					showXHTML_td('align="center" nowrap ', $MSG['theading_level'][$sysSession->lang]);
					showXHTML_td('align="center" nowrap ', $MSG['theading_delete'][$sysSession->lang]);
				showXHTML_tr_E('');

				echo getTeahcerListHTML($col);
			showXHTML_tr_B($col);
				showXHTML_td_B('colspan="5" align="center"');
						showXHTML_input('button' , '', $MSG['btn_renew'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\'course_property.php\')"');
				showXHTML_td_E('');
			showXHTML_tr_E('');

			showXHTML_table_E('');
			showXHTML_form_E('');
		showXHTML_td_E('');
	showXHTML_tr_E('');
showXHTML_table_E('');

showXHTML_body_E('');
?>
