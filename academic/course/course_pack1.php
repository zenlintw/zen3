<?php
	/**
	 * 課程包裝
	 *
	 * 建立日期：2005/01/17
	 * @author  Wiseguy
	 * @version $Id: course_pack1.php,v 1.1 2010/02/24 02:38:20 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot .'/lib/acl_api.php');
	require_once(sysDocumentRoot .'/lang/course_pack_install.php');
	$sysSession->cur_func='700400500';
	$sysSession->restore();

	if (!aclVerifyPermission(700400500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
	if ($sysConn->GetOne('select count(*) from WM_term_course where course_id=' . intval($_POST['course_id'])))
	{
		list($foo, $mysql_basedir) = $sysConn->GetRow('show variables like "basedir"');
		list($foo, $mysql_version) = $sysConn->GetRow('show variables like "version"');

		$mysql = $mysql_basedir . 'bin/mysql';
		if (!file_exists($mysql) || !is_executable($mysql))
		{
			$mysql = exec("sh -c 'PATH=/usr/local/mysql/bin:/usr/bin:/usr/local/bin:/home/apps/mysql/bin which mysql'");
			if (empty($mysql) || strpos($mysql, 'which: no mysql')===0) die('"mysql" not found.');
		}
		if (!file_exists($mysql) || !is_executable($mysql)) die('"mysql" not found or not executable.');
		$mysql .= (sysDBhost == 'localhost' ? ' -S /tmp/mysql.sock' : (' -h ' . str_replace(':', ' -P ', sysDBhost))) .' -u ' . sysDBaccoount . ' -p' . sysDBpassword . ' -N -B';

		$mysqldump = $mysql_basedir . 'bin/mysqldump';
		if (!file_exists($mysqldump) || !is_executable($mysqldump))
		{
			$mysqldump = exec("sh -c 'PATH=/usr/local/mysql/bin:/usr/bin:/usr/local/bin:/home/apps/mysql/bin which mysqldump'");
			if (empty($mysqldump) || strpos($mysqldump, 'which: no mysqldump')===0) die('"mysqldump" not found or not executable.');
		}
		if (!file_exists($mysqldump) || !is_executable($mysqldump)) die('"mysqldump" not found.');

		$mysqldump .= ' -c -t -q -Q --extended-insert=false --lock-tables=false' .
					  (version_compare($mysql_version, '5.0.0') >= 0 ? ' -f --compatible=mysql40 -N --compact' : '') .
					  (sysDBhost == 'localhost' ? ' -S /tmp/mysql.sock' : (' -h ' . str_replace(':', ' -P ', sysDBhost))) .' -u ' . sysDBaccoount . ' -p' . sysDBpassword;

		$zip = exec("sh -c 'PATH=/usr/bin:/usr/sbin:/sbin:/bin:/usr/local/bin which zip'");
		if (empty($zip) || strpos($zip, 'which: no zip')===0) die('Can not found zip');

		$cmd = sprintf('sh %s/course_export.sh \'%s\' \'%s\' %s %08d :%s:', dirname(__FILE__), $mysql, $mysqldump, sysDBprefix . $sysSession->school_id, $_POST['course_id'], implode(':', $_POST['course_elements']));
		$output = `{$cmd}`;
		$fname = sprintf('WM3ContentPackage_%08d_%s.zip', $_POST['course_id'], date('Ymd'));

		$exclude_files = ' -x \'*.xml.bk*\' ';
		if (!in_array('course_files',  $_POST['course_elements'])) $exclude_files .= '\'content/*\' ';
		if (!in_array('course_board',  $_POST['course_elements']) &&
			!in_array('subject_board', $_POST['course_elements']))
		{
            $exclude_files .= '\'board/*\' \'quint/*\' ';
		}
		elseif (!in_array('course_board',  $_POST['course_elements']))
		{
		    list($b1, $b2) = $sysConn->GetRow('select discuss,bulletin from ' . sysDBprefix . $sysSession->school_id . '.WM_term_course where course_id=' . $_POST['course_id']);
		    $exclude_files .= "'board/$b1' 'quint/$b1' 'board/$b2' 'quint/$b2' ";
		}
		elseif (!in_array('subject_board', $_POST['course_elements']))
		{
		    list($b1, $b2) = $sysConn->GetRow('select discuss,bulletin from ' . sysDBprefix . $sysSession->school_id . '.WM_term_course where course_id=' . $_POST['course_id']);
		    $ls1 = `cd {$_SERVER['DOCUMENT_ROOT']}/base/{$sysSession->school_id}/course/{$_POST['course_id']} && ls -1 board | sed -e '/{$b1}/d' -e '/{$b2}/d'`;
		    $ls1 = preg_split('/\s+/', $ls1, -1, PREG_SPLIT_NO_EMPTY);
		    if ($i = count($ls1)) $exclude_files .= vsprintf(str_repeat("'board/%u' ", $i), $ls1);
		    $ls2 = `cd {$_SERVER['DOCUMENT_ROOT']}/base/{$sysSession->school_id}/course/{$_POST['course_id']} && ls -1 quint | sed -e '/{$b1}/d' -e '/{$b2}/d'`;
		    $ls2 = preg_split('/\s+/', $ls2, -1, PREG_SPLIT_NO_EMPTY);
		    if ($i = count($ls2)) $exclude_files .= vsprintf(str_repeat("'quint/%u' ", $i), $ls2);
		}
        if (!in_array('chatroom',      $_POST['course_elements'])) $exclude_files .= '\'chat/*\' ';
        if (!in_array('homework',	   $_POST['course_elements'])) $exclude_files .= '\'homework/*\' ';      elseif ($_POST['package_detail'] != 'full') $exclude_files .= '\'homework/A/*\' ';
        if (!in_array('exam',		   $_POST['course_elements'])) $exclude_files .= '\'exam/*\' ';          elseif ($_POST['package_detail'] != 'full') $exclude_files .= '\'exam/A/*\' ';
        if (!in_array('questionnaire', $_POST['course_elements'])) $exclude_files .= '\'questionnaire/*\' '; elseif ($_POST['package_detail'] != 'full') $exclude_files .= '\'questionnaire/A/*\' ';


		if ($_POST['package_how'] == 'download')
		{

		    while (@ob_end_clean());
			header('Content-Disposition: attachment; filename="' . $fname . '"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Type: application/zip; name="' . $fname . '"');
			// $z = new ZipArchive('', '*', true);
			passthru(sprintf('cd %s/base/%05d/course/%08d && ' . $zip . ' -r -q -7 - *',
							 sysDocumentRoot, $sysSession->school_id, $_POST['course_id']
							) . $exclude_files
					);
		}
		elseif ($_POST['package_how'] == 'putindoor')
		{
			exec(sprintf('cd %s/base/%05d/course/%08d && ' . $zip . ' -r -q -7 %s/base/%05d/door/%s *',
						 sysDocumentRoot, $sysSession->school_id, $_POST['course_id'],
						 sysDocumentRoot, $sysSession->school_id, $fname
						) . $exclude_files
				);
			printf('<body><h2 align="center"><br /><br />%s</h2></body>', $MSG['msg_package_ok'][$sysSession->lang]);
		}
	}
	else
		printf('<body><h2 align="center"><br /><br />%s</h2></body>', $MSG['msg_no_course'][$sysSession->lang]);

?>
