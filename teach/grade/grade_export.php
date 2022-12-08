<?php
	/**
	 * �� �ץX���Z
	 *
	 * @since   2004/08/19
	 * @author  Wiseguy Liang
	 * @version $Id: grade_export.php,v 1.1 2010/02/24 02:40:26 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/archive_api.php');
	require_once(sysDocumentRoot . '/lang/grade.php');
	
	$sysSession->cur_func = '1400300100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}


	if (!ereg('^[0-9]+(,[0-9]+)*$', $_POST['lists'])) {
	   wmSysLog($sysSession->cur_func, $sysSession->course_id , $source_id , 1, 'auto', $_SERVER['PHP_SELF'], 'Incorrect GRADE_ID(s):' . $_POST['lists']);
	   die('Incorrect GRADE_ID(s).');
	}
	list($course_caption) = dbGetStSr('WM_term_course', 'caption', "course_id={$sysSession->course_id}", ADODB_FETCH_NUM);
	$course_titles = unserialize($course_caption);

	$sqls = 'select L.grade_id,L.title,A.username,A.first_name,A.last_name,A.email,I.score,I.comment ' .
			'from WM_term_major as M left join WM_user_account as A ' .
			'on M.username=A.username left join WM_grade_list as L ' .
			"on M.course_id=L.course_id and L.grade_id in ({$_POST['lists']}) left join WM_grade_item as I " .
			'on L.grade_id=I.grade_id and A.username=I.username ' .
			"where M.course_id ={$sysSession->course_id} and M.role & {$sysRoles['student']} " .
			'order by L.grade_id,A.username';

	$prev_grade_id = 0;
	$wm_body = sprintf('<manifest course_caption="%s" course_id="%s">',
					htmlspecialchars($course_titles[$sysSession->lang], ENT_QUOTES, 'UTF-8'),
					$sysSession->course_id);
	// ���� XML �ҩl
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	if ($rs = $sysConn->Execute($sqls))
	{
		while($fields = $rs->FetchRow())
		{
			if ($prev_grade_id == $fields['grade_id'])
			{
				$wm_body .= sprintf('<student><student_id>%s</student_id><student_name>%s</student_name><score>%.2f</score><comment>%s</comment></student>',
								 $fields['username'],
								 htmlspecialchars(checkRealname($fields['first_name'], $fields['last_name']), ENT_QUOTES, 'UTF-8'),
								 $fields['score'],
								 htmlspecialchars($fields['comment'], ENT_QUOTES, 'UTF-8')
								);
			}
			else
			{
				$captions = unserialize($fields['title']);
				if ($prev_grade_id) $wm_body .= '</grade>';
				$wm_body .= sprintf('<grade caption="%s" id="%d"><student><student_id>%s</student_id><student_name>%s</student_name><score>%.2f</score><comment>%s</comment></student>',
								 htmlspecialchars($captions[$sysSession->lang], ENT_QUOTES, 'UTF-8'),
								 $fields['grade_id'],
								 $fields['username'],
								 htmlspecialchars(checkRealname($fields['first_name'], $fields['last_name']), ENT_QUOTES, 'UTF-8'),
								 $fields['score'],
								 htmlspecialchars($fields['comment'], ENT_QUOTES, 'UTF-8')
								);
			}
			$prev_grade_id = $fields['grade_id'];
		}
	}
	if ($prev_grade_id) $wm_body .= '</grade>';
	$wm_body .= '</manifest>';
	// ���� XML ����

	$pattern = array('!^<manifest course_caption="(.*)" course_id="(.*)">!sU',
					 '!</manifest>$!sU',
					 '!<student><student_id>(.*)</student_id><student_name>(.*)</student_name><score>(.*)</score><comment>(.*)</comment></student>!sU',
					 '!<grade caption="(.*)" id="(.*)">(.*)</grade>!sU'
					);

	if (in_array('csv', $_POST['grade_kinds']))
	{
		$csv_body = utf8_to_excel_unicode(
		            strip_tags(
					preg_replace($pattern,
								 array("\"\\1\" (\\2)\r\n",
								 	   '',
								 	   "\"\\1\",\"\\2\",\\3,\"\\4\"\r\n",
								 	   "\"\\1\" (\\2)\n".$MSG['student_id'][$sysSession->lang].",".$MSG['student_name'][$sysSession->lang].",".$MSG['score'][$sysSession->lang].",".$MSG['comment'][$sysSession->lang]."\n\\3\r\n"
								      ),
								 $wm_body
								)
						)
					);
	}

	if (in_array('htm', $_POST['grade_kinds']))
	{
		$htm_body = preg_replace($pattern,
								 array('<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>\1 (\2)</title><style>body{font-family: serif} td{font-family: serif}</style></head><body>',
								 	   '</body></html>',
								 	   '<tr><td nowrap>\1</td><td nowrap>\2</td><td nowrap>\3</td><td nowrap>\4</td></tr>',
								 	   '<table border="1" cellpadding="3" cellspacing="0" style="border-collapse: collapse"><thead><tr><td colspan="4">\1 (\2)</td></tr><tr><td>'.$MSG['student_id'][$sysSession->lang].'</td><td>'.$MSG['student_name'][$sysSession->lang].'</td><td>'.$MSG['score'][$sysSession->lang].'</td><td>'.$MSG['comment'][$sysSession->lang].'</td></tr></thead><tbody>\3</tbody></table>'
								      ),
								 $wm_body
								);
	}

	if (in_array('xml', $_POST['grade_kinds']))
	{
		$xml_body = '<?xml version="1.0" ?>' . $wm_body;
	}

	$download_name = preg_replace(array('!\.\./!U', sprintf('!%s!U', preg_quote(DIRECTORY_SEPARATOR))),
								  array('', ''),
								  stripslashes($_POST['download_name']));
	$fname = $download_name ? $download_name : ($sysSession->course_id . '.zip');
	
	if (substr($fname,-4)!='.zip') {
		$fname = $fname . '.zip';
	}
	$export_obj = new ZipArchive_php4($fname);
	if ($csv_body) $export_obj->add_string($csv_body, $sysSession->course_id . '.csv');
	if ($htm_body) $export_obj->add_string($htm_body, $sysSession->course_id . '.htm');
	if ($xml_body) $export_obj->add_string($xml_body, $sysSession->course_id . '.xml');

	header('Content-Disposition: attachment; filename="' . $fname . '"');
	header('Content-Transfer-Encoding: binary');
	header('Content-Type: application/zip; name="' . $fname . '"');
	$export_obj->readfile();
	$export_obj->delete();
?>
