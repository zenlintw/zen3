<?php
	/**
	 * �w�˽ҵ{
	 *
	 * @since   2005/05/17
	 * @author  ShenTing Lin
	 * @version $Id: course_install1.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/

	set_time_limit(0);
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lang/course_pack_install.php');

	/**
		�O�_�w�F�ҵ{���q
		@return bollean
	*/
	function isReachCourseLimit()
	{
		if (sysCourseLimit == 0) return false;		//�L�ҵ{�W��
		list($nowCourseNum) = dbGetStSr('WM_term_course','count(*)','kind = "course" and status != 9', ADODB_FETCH_NUM);
		if ($nowCourseNum >= sysCourseLimit) return true;
		return false;
	}

	if (isReachCourseLimit())
	{
		header("Location: /academic/course/course_limit.php");
		exit;
	}

	$unzip = exec("sh -c 'PATH=/usr/bin:/usr/sbin:/sbin:/bin:/usr/local/bin which unzip'");
    if (empty($unzip) || strpos($unzip, 'which: no unzip')===0) die('"unzip" not found.');
	$rm    = exec("sh -c 'PATH=/usr/bin:/usr/sbin:/sbin:/bin:/usr/local/bin which rm'");

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

	$pathDoor   = sysDocumentRoot . sprintf('/base/%05d/door/', $sysSession->school_id);
	$pathCourse = sysDocumentRoot . sprintf('/base/%05d/course/', $sysSession->school_id);
	$pathTemp   = 'install_' . date('YmdHis') . '/';
	$pkgCourse  = basename(trim($_POST['course_package']));

	if (empty($pkgCourse) || !file_exists("{$pathDoor}{$pkgCourse}")) die('Course Package file not found.');

	// �����Y

	$logUnzip   = `$unzip '{$pathDoor}{$pkgCourse}' -d '{$pathCourse}{$pathTemp}'`;

	// �]�����]�Ч��ɮסA�|�S�� content �ؿ��A�ҥH�p�G�S�����ܴN���@��
	if (!is_dir($pathCourse . $pathTemp . 'content')) @mkdir($pathCourse . $pathTemp . 'content');

	// ���F�[�ֳt�סA���o������W�����ɥؿ� by wiseguy
	$tmpdir = $pathCourse . $pathTemp . 'homework/Q/';
	$previous['homework']      = preg_split('/\s+/', `find $tmpdir -type d -maxdepth 1 -exec basename {} \\;`, -1, PREG_SPLIT_NO_EMPTY);
	$tmpdir = $pathCourse . $pathTemp . 'exam/Q/';
	$previous['exam']          = preg_split('/\s+/', `find $tmpdir -type d -maxdepth 1 -exec basename {} \\;`, -1, PREG_SPLIT_NO_EMPTY);
	$tmpdir = $pathCourse . $pathTemp . 'questionnaire/Q/';
	$previous['questionnaire'] = preg_split('/\s+/', `find $tmpdir -type d -maxdepth 1 -exec basename {} \\;`, -1, PREG_SPLIT_NO_EMPTY);
	$tmpdir = $pathCourse . $pathTemp . 'board/';
	$previous['board']         = preg_split('/\s+/', `find $tmpdir -type d -maxdepth 1 -exec basename {} \\;`, -1, PREG_SPLIT_NO_EMPTY);
	// ���F�[�ֳt�סA���o������W�����ɥؿ� END

	// ���o install.sql
	$sqlInstall = file_get_contents($pathCourse . $pathTemp . 'install.sql');
	// �ഫ��ƪ� key
	// WM_chat_setting

	if (($a = @file($pathCourse . $pathTemp . 'chat.lst')) !== false)
	{
		$s = array();
		$r = array();
		foreach ($a as $key => $v) {
			$v = trim($v);
			if (empty($v)) continue;
			$s[] = $v;
			$r[] = uniqid('');
		}
	}
	// $sqlInstall = str_replace($s, $r, $sqlInstall);
	$chat = array($s, $r);
		// WM_QTI_*
	$l = array(
		'exam'          => 'qti_exam.lst',
		'homework'      => 'qti_hw.lst'  ,
		'questionnaire' => 'qti_qs.lst'
	);
	$qti = array(
		'exam'          => array(),
		'homework'      => array(),
		'questionnaire' => array()
	);

	// $s = array();
	// $r = array();
	$course_id = dbGetOne('WM_term_course', 'max(course_id) + 1', '1', ADODB_FETCH_NUM);
	foreach ($l as $qti_which => $val) {
		$x = preg_split('/\s+/', @file_get_contents($pathCourse . $pathTemp . $val), -1, PREG_SPLIT_NO_EMPTY);
		if (!is_array($x) || count($x) < 1) continue;

		$t = split('[. ]', microtime()); $ts = intval(substr($t[1],0,6));
		$ident = sprintf('WM_ITEM1_%s_%u_%s_', sysSiteUID, $course_id, $t[2]);

		foreach($x as $old_id)
		{
			$s[] = $old_id;
			$r[] = $qti[$qti_which][$old_id] = $ident . $ts++;
		}
	}

	if (count($s)) $sqlInstall = str_replace($s, $r, $sqlInstall);

	$filename = sysTempPath . '/' . str_replace('/', '.sql', $pathTemp);
	if ($fp = fopen($filename, 'w')) {
		if (fwrite($fp, $sqlInstall) !== FALSE) {
			fclose($fp);
			chmod($filename, 0666); // ������s�v���A���M�L�k�פJ���
			$u = sysDBaccoount;
			$p = sysDBpassword;
			$logSQL = sysTempPath . '/' . str_replace('/', '.log', $pathTemp);
			touch($logSQL);
			chmod($logSQL, 0666); // ������s�v���A���M�L�k�פJ���

			// �N��Ƽg�J��Ʈw
			$dbPreFix = sysDBprefix;
			// echo "{$mysql} -u {$u} -p{$p} -N -D {$dbPreFix}{$sysSession->school_id} < {$filename} > {$logSQL}<br />";
			$log = `{$mysql} -u {$u} -p{$p} -B -r -f -D {$dbPreFix}{$sysSession->school_id} < {$filename} > {$logSQL}`;

			// ��s�ؿ��W��
			$a = file($logSQL);
			$pathNewCourse = $pathCourse . $pathTemp;
			$newCourseID = '';
			$termPath = '';
			foreach ($a as $key => $val) {
				$val = trim($val);
				if ($val == 'NULL') continue;
				$r = explode(':', $val);
				switch ($r[0]) {
					case 'course_id' :
						rename($pathCourse . str_replace('/', '', $pathTemp), $pathCourse . $r[1]);
						$pathNewCourse = $pathCourse . $r[1] . '/';
						$newCourseID = intval($r[1]);
						// ��s�ҵ{�W�١A�[�W [�ƻs]
						list($caption) = dbGetStSr('WM_term_course', '`caption`', "`course_id`={$newCourseID}", ADODB_FETCH_NUM);
						$lang = unserialize($caption);
						foreach ($lang as $key => $val) {
							$lang[$key] = $MSG['msg_copy'][$key] . $val;
						}
						$caption = serialize($lang);
						// Bug#1549-�w�ˡy�]�˽ҵ{�z�Alogin_times��post_times�n��0�A�H�K�v�T�ҵ{�Ʀ� by Small 2006/12/25
                        // Bug#1549-����post_times�A�[�Wdsc_time by Small 2007/1/10
						// dbSet('WM_term_course', "`caption`='{$caption}'", "`course_id`={$newCourseID}");
						dbSet('WM_term_course', "`caption`='{$caption}',`login_times`=0,`dsc_times`=0", "`course_id`={$newCourseID}");
						// ���X�ǲ߸��|
						list($serial) = dbGetStSr('WM_term_path', 'max(serial)', "course_id={$newCourseID}", ADODB_FETCH_NUM);
						$serial = max($serial, 1);
						list($termPath) = dbGetStSr('WM_term_path', '`content`', "`course_id`={$newCourseID} and `serial`={$serial}", ADODB_FETCH_NUM);
						
						$sysConn->Execute('use ' . sysDBprefix . $sysSession->school_id);
                        $sysConn->Execute("insert into WM_review_sysidx (discren_id, flow_serial) values ('{$newCourseID}','2')");
						
						break;

					case 'acl_id_mapping' :
						break;

					case 'board_id_mapping' :
						$t = explode(',', $r[1]);
						$csbd = array();
						if (!empty($newCourseID)) {
							$csbd = dbGetStSr('WM_term_course', '`discuss`, `bulletin`', "`course_id`={$newCourseID}", ADODB_FETCH_ASSOC);
						}
						if (is_array($t)) {
							foreach ($t as $v) {
								$b = explode('=', $v);
								if (in_array($b[0], $previous['board']))
								{
									// �Y���Q�תO�n�ܧ�s���A�h�W�[��ذϪ��s���P�B���� by Small 2012/02/07
									@rename($pathNewCourse . 'board/' . $b[0], $pathNewCourse . 'board/' . $b[1]);
									@rename($pathNewCourse . 'quint/' . $b[0], $pathNewCourse . 'quint/' . $b[1]);
								}
								// ��sĳ�D�Q��
								if (!empty($newCourseID)) {
									dbSet('WM_term_subject', "`board_id`={$b[1]}", "`course_id`={$newCourseID} AND `board_id`={$b[0]}");
									if (($k = array_search($b[0], $csbd)) !== FALSE) {
										dbSet('WM_term_course', "`{$k}`={$b[1]}", "`course_id`={$newCourseID}");
									}
								}
								$termPath = str_replace("(6,{$b[0]})", "(6,{$b[1]})", $termPath);
							}
						}
						break;

					case 'ex_id_mapping' :
						$t = explode(',', $r[1]);
						if (is_array($t)) {
							foreach ($t as $v) {
								$b = explode('=', $v);
								@rename($pathNewCourse . 'exam/A/' . $b[0], $pathNewCourse . 'exam/A/' . $b[1]);
								$termPath = str_replace("(3,{$b[0]})", "(3,{$b[1]})", $termPath);
							}
						}
						if (is_array($qti['exam'])) {
							foreach ($qti['exam'] as $a => $b) {
								if (in_array($a, $previous['exam'])) @rename($pathNewCourse . 'exam/Q/' . $a, $pathNewCourse . 'exam/Q/' . $b);
								$termPath = str_replace("(3,{$a})", "(3,{$b})", $termPath);
							}
						}
						break;

					case 'hw_id_mapping' :
						$t = explode(',', $r[1]);
						if (is_array($t)) {
							foreach ($t as $v) {
								$b = explode('=', $v);
								@rename($pathNewCourse . 'homework/A/' . $b[0], $pathNewCourse . 'homework/A/' . $b[1]);
								$termPath = str_replace("(2,{$b[0]})", "(2,{$b[1]})", $termPath);
							}
						}
						if (is_array($qti['homework'])) {
							foreach ($qti['homework'] as $a => $b) {
								if (in_array($a, $previous['homework'])) @rename($pathNewCourse . 'homework/Q/' . $a, $pathNewCourse . 'homework/Q/' . $b);
								$termPath = str_replace("(2,{$a})", "(2,{$b})", $termPath);
							}
						}
						break;

					case 'qu_id_mapping' :
						$t = explode(',', $r[1]);
						if (is_array($t)) {
							foreach ($t as $v) {
								$b = explode('=', $v);
								@rename($pathNewCourse . 'questionnaire/A/' . $b[0], $pathNewCourse . 'questionnaire/A/' . $b[1]);
								$termPath = str_replace("(4,{$b[0]})", "(4,{$b[1]})", $termPath);
							}
						}
						if (is_array($qti['questionnaire'])) {
							foreach ($qti['questionnaire'] as $a => $b) {
								if (in_array($a, $previous['questionnaire'])) @rename($pathNewCourse . 'questionnaire/Q/' . $a, $pathNewCourse . 'questionnaire/Q/' . $b);
								$termPath = str_replace("(4,{$a})", "(4,{$b})", $termPath);
							}
						}
						break;

					case 'subject_id_mapping' :
						$t = explode(',', $r[1]);
						if (is_array($t)) {
							foreach ($t as $v) {
                                                            if ($v >= '0') {
								$b = explode('=', $v);
								if (in_array($b[0], $previous['board'])) @rename($pathNewCourse . 'board/' . $b[0], $pathNewCourse . 'board/' . $b[1]);
								// ��sĳ�D�Q��
								if (!empty($newCourseID)) {
									dbSet('WM_term_subject', "`board_id`={$b[1]}", "`course_id`={$newCourseID} AND `board_id`={$b[0]}");
								}
								$termPath = str_replace("(5,{$b[0]})", "(5,{$b[1]})", $termPath);
                                                            }
							}
						}
                                                // Bug#1549-���]�Q�תO�ɡApost_times�n�k�s by Small 2007/1/11
                                                // ���o�s�ҵ{�Ҧ���board_id
                                                $array_board = dbGetCol('WM_term_subject', '`board_id`', "`course_id`={$newCourseID}", ADODB_FETCH_ASSOC);
						if (is_array($array_board) && count($array_board))
						{
                                                        $board_list = implode(',',$array_board);
                                                        // ���o�s�ҵ{�Q�תO���峹��
                                                        list($post_count) = dbGetStSr('WM_bbs_posts', 'count(*)', "board_id in (".$board_list.")", ADODB_FETCH_NUM);
                                                        // �Y�峹�Ƭ��s�A�h���]�s�ҵ{��post_times�A�_�h������P
                                                        ($post_count==0)? dbSet('WM_term_course', "`post_times`=0", "`course_id`={$newCourseID}") : '';
                                                }
						break;
					default:
				}

			}
		}
		// �^�s�ǲ߸��|
		//temp marked by jeff: 2006-07-13, for wm25towm3
		dbSet('WM_term_path', "`content`='{$termPath}'", "`course_id`={$newCourseID} and `serial`={$serial}");
		dbSet('WM_term_course', "path='/base/{$sysSession->school_id}/course/{$newCourseID}'", "`course_id`={$newCourseID}");
		wmSysLog('0700400600', $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], 'Install course: ' . $newCourseID);
	}
	// �����Ȧs��
	@unlink($filename);
	@unlink($logSQL);

	// �ɨ�ҵ{�]�w
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['title_install'][$sysSession->lang], 'tabs1');
		// $colspan = 'colspan="2"';
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'actFm', '', 'action="/teach/course/m_course_property.php" method="post" enctype="multipart/form-data" style="display: inline;"');
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="center"', $MSG['msg_success'][$sysSession->lang]);
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center"');
						$ticket = md5($sysSession->school_id . $sysSession->school_name . 'Edit' . $sysSession->username);
						showXHTML_input('hidden', 'ticket', $ticket, '', '');
						$csid = sysEncode($newCourseID);
						showXHTML_input('hidden', 'csid', $csid, '', '');
						showXHTML_input('button', 'btnReturn', $MSG['btn_return'][$sysSession->lang], '', 'onclick="window.location.replace(\'course_install.php\')" class="cssBtn"');
						showXHTML_input('submit', 'btnSet', $MSG['btn_property'][$sysSession->lang], '', 'class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>
