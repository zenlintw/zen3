<?php
    /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  : 修課記錄                                                                      *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       @version:$Id: course_record.php,v 1.1 2010/02/24 02:38:44 saly Exp $
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	function tableHead($title, &$RS)
	{
		global $sysSession, $MSG, $sortby, $order, $icon_up, $icon_dn;

		showXHTML_tr_B('class="cssTrHead"');
			showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(1);" title="' . $title . '"');
				echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
				echo $title;
				echo ($sortby == 1) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
				echo '</a>';
			showXHTML_td_E();

			showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(2);" title="' . $MSG['title101'][$sysSession->lang] . '"');
				echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
				echo $MSG['title101'][$sysSession->lang];
				echo ($sortby == 2) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
				echo '</a>';
			showXHTML_td_E();

			showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(3);" title="' . $MSG['title102'][$sysSession->lang] . '"');
				echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
				echo $MSG['title102'][$sysSession->lang];
				echo ($sortby == 3) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
				echo '</a>';
			showXHTML_td_E();

			showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(4);" title="' . $MSG['title103'][$sysSession->lang] . '"');
				echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
				echo $MSG['title103'][$sysSession->lang];
				echo ($sortby == 4) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
				echo '</a>';
			showXHTML_td_E();

			showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(5);" title="' . $MSG['title104'][$sysSession->lang] . '"');
				echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
				echo $MSG['title104'][$sysSession->lang];
				echo ($sortby == 5) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
				echo '</a>';
			showXHTML_td_E();

			showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(6);" title="' . $MSG['title105'][$sysSession->lang] . '"');
				echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
				echo $MSG['title105'][$sysSession->lang];
				echo ($sortby == 6) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
				echo '</a>';
			showXHTML_td_E();

			showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(7);" title="' . $MSG['title106'][$sysSession->lang] . '"');
				echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
				echo $MSG['title106'][$sysSession->lang];
				echo ($sortby == 7) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
				echo '</a>';
			showXHTML_td_E();
		showXHTML_tr_E();

		if ($RS && $RS->RecordCount() > 0)
		{
			while (!$RS->EOF)
			{
				$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
				// 抓取課程名稱
				$lang = getCaption($RS->fields['caption']);
				$csname = $lang[$sysSession->lang];
				showXHTML_tr_B('class="' . $col . '"');
					showXHTML_td_B('align="left" nowrap="noWrap"');
						echo '<div style="width: 200px; overflow:hidden;" title="' . $csname . '">' . $csname . '</div>';
					showXHTML_td_E();
					showXHTML_td('align="center" nowrap="noWrap"', $RS->fields['last_login']);
					showXHTML_td('align="center" nowrap="noWrap"', $RS->fields['login_times']);
					showXHTML_td('align="center" nowrap="noWrap"', $RS->fields['post_times']);
					showXHTML_td('align="center" nowrap="noWrap"', $RS->fields['dsc_times']);
					showXHTML_td('align="center" nowrap="noWrap"', $RS->fields['page']);
					showXHTML_td('align="right" nowrap', zero2gray(sec2timestamp(intval($RS->fields['rss']))));
				showXHTML_tr_E();
				$RS->MoveNext();
			}
		}
		else
		{
			showXHTML_tr_B('class="cssTrEvn" ');
				showXHTML_td('align="center" nowrap="noWrap" colspan="7"', $MSG['title107'][$sysSession->lang]);
			showXHTML_tr_E();
		}
	}

	$sortby  = min(7, max(1, $_POST['sortby']));	// 設定sortby
	$sortArr = array(
		''             , 'CS.course_id'  ,
		'MJ.last_login', 'MJ.login_times',
		'MJ.post_times', 'MJ.dsc_times'  ,
		'page'         , 'rss'
	);

	$username = $_POST['user'] ? preg_replace('/[^\w.-]+/', '', $_POST['user']) : $sysSession->username;
	//  查詢帳號的資料
	list($first_name, $last_name) = dbGetStSr('WM_user_account', 'first_name, last_name', 'username="'.$username.'"', ADODB_FETCH_NUM);

	$icon_up = '<img src="/theme/default/academic/dude07232001up.gif" border="0" align="absmiddl">';
	$icon_dn = '<img src="/theme/default/academic/dude07232001down.gif" border="0" align="absmiddl">';

	$_POST['user_role'] = intval($_POST['user_role']);

	showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="course_record" class="cssTable"');
		//  修課記錄(begin)
		showXHTML_tr_B('class="cssTrHead"');
			showXHTML_td_B('colspan="7"');
				// Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
				echo checkRealname($first_name, $last_name);
				if (isset($ACADEMIC_TEACHER))
				{
					echo $MSG['title28'][$sysSession->lang];
					$ary = array(
						$sysRoles['teacher']    => $MSG['title69'][$sysSession->lang],
						$sysRoles['assistant']  => $MSG['title67'][$sysSession->lang],
						$sysRoles['instructor'] => $MSG['title68'][$sysSession->lang]
					);
					echo $ary[$_POST['user_role']] . ' > ' . $MSG['teach_record'][$sysSession->lang];
				}
				elseif (!empty($ACADEMIC_CLASS_MEMBER))
				{
					$class_id = max(1000000, intval($_POST['class_id']));
					if ($class_id == 1000000)
					{
						$csname = $sysSession->school_name;
					}
					else
					{
						list($caption) = dbGetStSr('WM_class_main', 'caption', ' class_id = ' .  $class_id, ADODB_FETCH_NUM);
						$lang   = getCaption($caption);
						$csname = $lang[$sysSession->lang];
					}
					echo '<font color="#FF0000">', $MSG['title121'][$sysSession->lang], $csname, '</font><br>';
				}
				else
				{
					echo ' > ' . $MSG['title57'][$sysSession->lang];
				}
			showXHTML_td_E();
		showXHTML_tr_E();

		//  正在修的課 (begin)
		if (!empty($ACADEMIC_TEACHER))
		{ // 管理者 - 導師管理 - 教師查詢 - 授課記錄
			$sqls = str_replace(array('%USERNAME%', '%ROLE%'), array($username, $_POST['user_role']), $Sqls['get_teacher_courselist']);
		}
		else
		{
			$sqls = str_replace('%USERNAME%', $username, $Sqls['get_student_courselist']);
		}

		$cond  = $sortArr[$sortby] . ' ' .  $order;
		$sqls .= 'order by ' . $cond;
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sqls);

			// 管理者 - 導師管理 - 教師查詢 - 授課記錄
		$title = (!empty($ACADEMIC_TEACHER)) ? $MSG['title139'][$sysSession->lang] : $MSG['title99'][$sysSession->lang];
		tableHead($title, $RS);
		//  正在修的課 (end)

		// 修過的課 (begin)
			// 管理者 - 導師管理 - 教師查詢 - 授課記錄
		if (!empty($ACADEMIC_TEACHER))
		{ // 管理者 - 導師管理 - 教師查詢 - 授課記錄
			$sqls = str_replace(array('%USERNAME%', '%ROLE%'), array($username, $_POST['user_role']), $Sqls['get_teacher_end_courselist']);
		}
		else
		{
			$sqls = str_replace('%USERNAME%', $username, $Sqls['get_student_end_courselist']);
		}

		$cond  = $sortArr[$sortby] . ' ' .  $order;
		$sqls .= 'order by ' . $cond;
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sqls);

		$title = (!empty($ACADEMIC_TEACHER)) ? $MSG['title140'][$sysSession->lang] : $MSG['title100'][$sysSession->lang];
		tableHead($title, $RS);
        //  修課記錄(end)

		$col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
		//  回人員列表 (begin)
		showXHTML_tr_B('class="' . $col . ' font01"');
			showXHTML_td_B('colspan="7" align="center"');
				if (! empty($DIRECT_MEMBER))
					$btn = $MSG['btn_return_direct_member'][$sysSession->lang];
				else if (! empty($ENROLL_MEMBER))	//  導師 - 學員修課管理 - 修課指派 - 挑選人員 - 修課記錄
					$btn = $MSG['btn_direct_enroll_member'][$sysSession->lang];
				else if(! empty($ACADEMIC_CLASS_MEMBER)){	// 管理者 - 班級管理 - 成員管理 - 修課記錄
					$btn = $MSG['title98'][$sysSession->lang];
				}else if(! empty($ACADEMIC_DELETE_MEMBER))
					$btn = $MSG['title138'][$sysSession->lang];
				else if(! empty($ACADEMIC_TEACHER))	// 管理者 - 導師管理 - 教師查詢 - 授課記錄
					$btn = $MSG['title137'][$sysSession->lang];
				else{
					if (empty($_POST['del_user'])){
						$btn = $MSG['title130'][$sysSession->lang];
					}else{
						$btn = $MSG['title138'][$sysSession->lang];
					}
				}
				showXHTML_input('button', '', $btn, '', 'class="cssBtn" onclick="go_list(' . intval($_POST['class_id']) . ');"');
			showXHTML_td_E();
		showXHTML_tr_E();
		//  回人員列表 (end)

    showXHTML_table_E();
?>
