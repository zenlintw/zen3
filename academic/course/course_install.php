<?php
	/**
	 * 【程式功能】
	 * 建立日期：2004/08/16
	 * @author  Wiseguy Liang
	 * @version $Id: course_install.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/course_pack_install.php');

	$sysSession->cur_func = '700400600';
	$sysSession->restore();
	/**
		是否已達課程限量
		@return bollean
	*/
	function isReachCourseLimit()
	{
		if (sysCourseLimit == 0) return false;		//無課程上限
		list($nowCourseNum) = dbGetStSr('WM_term_course','count(*)','kind = "course" and status != 9', ADODB_FETCH_NUM);
		if ($nowCourseNum >= sysCourseLimit) return true;
		return false;
	}

	if (isReachCourseLimit())
	{
		header("Location: /academic/course/course_limit.php?type=cour_install");
		exit;
	}


	$js = <<< EOF

EOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	// showXHTML_script('include', '/lib/dragLayer.js');
	// showXHTML_script('include', '/lib/xmlextras.js');
	// showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();

	showXHTML_body_B();
		echo "<center>\n";
		$ary = array(array($MSG['title_install'][$sysSession->lang]));
		showXHTML_tabFrame_B($ary, 1, '', 'ListTable', 'action="course_install1.php" method="POST" style="display: inline"');
				showXHTML_table_B('id ="mainTable" width="680" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

					showXHTML_tr_B('class="font01 cssTrEvn"');
						showXHTML_td('', $MSG['th_course_package_file'][$sysSession->lang]);
						showXHTML_td_B();

						$items = array();
						$doorPath = sysDocumentRoot . sprintf('/base/%05d/door/', $sysSession->school_id);
						$unzip = exec("sh -c 'PATH=/usr/bin:/usr/sbin:/sbin:/bin:/usr/local/bin which unzip'");
                        if (empty($unzip) || strpos($unzip, 'which: no unzip')===0) die('"unzip" not found.');
						if ($dp = dir($doorPath))
						{
							while (false !== ($entry = $dp->read()))
							{
								if (preg_match('/\.zip$/', $entry))
								{
									$tmp = `$unzip -p '{$doorPath}{$entry}' install.sql|head -1`;
									if (preg_match('/^INSERT INTO `WM_term_course` .*(a:[0-9]+:\{.*\})/isU', $tmp, $regs))
									{
										$t = getCaption(stripslashes($regs[1]));
										if (preg_match('/_(\d{4})(\d{2})(\d{2})\./', $entry, $regs)) {
											$items[$entry] = sprintf('(%04d-%02d-%02d) %s', $regs[1], $regs[2], $regs[3], $t[$sysSession->lang]);
										} else {
											$items[$entry] = $t[$sysSession->lang];
										}
									}
								}
							}
							$dp->close();
						}
							showXHTML_input('select', 'course_package', $items, '', 'class="cssInput"');
						showXHTML_td_E();
						showXHTML_td('', $MSG['msg_course_package_file'][$sysSession->lang]);
					showXHTML_tr_E();

					showXHTML_tr_B('class="font01 cssTrOdd"');
						showXHTML_td_B('colspan="3" align="center"');
							showXHTML_input('submit', '', $MSG['btn_install'][$sysSession->lang], '', 'onsubmit="this.disabled=true;"');
						showXHTML_td_E();
					showXHTML_tr_E();

				showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo "</center>\n";
	showXHTML_body_E();

?>
