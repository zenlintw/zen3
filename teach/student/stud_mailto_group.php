<?php
	/**
	 * 取得學員分組
	 *
	 * @since   2004/06/17
	 * @author  ShenTing Lin
	 * @version $Id: stud_mailto_group.php,v 1.1 2010/02/24 02:40:31 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func = '1000300400';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}
	
	header("Content-type: text/xml");
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
			exit;
		}

		// 取得所有的組次列表
		$teams = array(); $group = array();
		$RS = dbGetStMr('WM_student_separate as S left join WM_student_group as G on S.course_id=G.course_id and S.team_id=G.team_id',
						'S.team_id, S.team_name, G.group_id, G.caption',
						'S.course_id=' . $sysSession->course_id . ' order by S.permute,S.team_id,G.permute,G.group_id',
						ADODB_FETCH_ASSOC);
		if ($RS)
			while ($fields = $RS->FetchRow())
			{
                $teams[$fields['team_id']] = getCaption($fields['team_name']);
                if ($fields['group_id'])
                    $group[$fields['team_id']][] = array($fields['group_id'], getCaption($fields['caption']));
			}

		echo '<' , '?xml version="1.0" encoding="UTF-8" ?' , '>' , "\n",
		     '<manifest><ticket></ticket>';
		foreach ($teams as $team_id => $team_name) {
			echo '<team id="' . $team_id . '" name="' . htmlspecialchars($team_name[$sysSession->lang]) . '">';
			if (isset($group[$team_id]))
			{
				foreach ($group[$team_id] as $gva) {
					echo '<group id="' . $gva[0] . '" name="' . htmlspecialchars($gva[1][$sysSession->lang]) . '"></group>';
				}
			}
			echo '</team>';
		}
		echo '</manifest>';
	}
?>
