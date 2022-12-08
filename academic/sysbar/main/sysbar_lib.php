<?php
	/**
	 * 編輯 sysbar 的共用函數
	 *
	 * @since   2003/07/09
	 * @author  ShenTing Lin
	 * @version $Id: sysbar_lib.php,v 1.1 2010/02/24 02:38:46 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/username.php');  // 為了取得使用者的個人目錄
	require_once(sysDocumentRoot . '/lang/sysbar_config.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	if (!defined('SYSBAR_MENU') || !defined('SYSBAR_LEVEL')) {
		die('deny!');
	}
	$SYSBAR_MENU  = SYSBAR_MENU;
	$SYSBAR_LEVEL = SYSBAR_LEVEL;
///////////////////////////////////////////////////////////////////////////////
	$sysbarRoles = array(
		'admin'    => array(),
		'director' => array(),
		'teacher'  => array(),
		'student'  => array()
	);

	// 環境
	$sysbarEnv = array(
		 0 => $MSG['bar_env_all'][$sysSession->lang],               // 全部
		 1 => $MSG['bar_env_academic'][$sysSession->lang],          // 管理室
		 2 => $MSG['bar_env_teach'][$sysSession->lang],             // 教師辦公室
		 4 => $MSG['bar_env_direct'][$sysSession->lang],            // 導師辦公室
		 8 => $MSG['bar_env_course'][$sysSession->lang],            // 班級
		16 => $MSG['bar_env_class'][$sysSession->lang],             // 教室
	);

	$sysbarMenuNum = array(
		'academic' => 3,
		'teach'    => 4,
		'learn'    => 5,
		'direct'   => 6,
		'class'    => 9,
		'personal' => 7,
		'school'   => 8,
	);

	/**
	 * 產生檢查用的 Ticket
	 * @return array : $menu;
	 **/
	function genMenuTicket() {
		global $_COOKIE, $SYSBAR_LEVEL;
		$menu = array(
			'academic' => md5(sysTicketSeed . 'sysbar' . $_COOKIE['idx'] . $SYSBAR_LEVEL . 'academic'),
			'teach'    => md5(sysTicketSeed . 'sysbar' . $_COOKIE['idx'] . $SYSBAR_LEVEL . 'teach'),
			'learn'    => md5(sysTicketSeed . 'sysbar' . $_COOKIE['idx'] . $SYSBAR_LEVEL . 'learn'),
			'direct'   => md5(sysTicketSeed . 'sysbar' . $_COOKIE['idx'] . $SYSBAR_LEVEL . 'direct'),
			'class'    => md5(sysTicketSeed . 'sysbar' . $_COOKIE['idx'] . $SYSBAR_LEVEL . 'class'),
			'personal' => md5(sysTicketSeed . 'sysbar' . $_COOKIE['idx'] . $SYSBAR_LEVEL . 'personal'),
			'school'   => md5(sysTicketSeed . 'sysbar' . $_COOKIE['idx'] . $SYSBAR_LEVEL . 'school'),
		);
		return $menu;
	}

	/**
	 * 取得種類清單
	 * @param
	 * @return array : 清單陣列
	 **/
	function getSysbarKindList() {
		global $sysSession, $MSG, $SYSBAR_MENU, $SYSBAR_LEVEL;

		$func = spiltFuncId();
		$fid1 = intval($func[1]);
		$fid2 = intval($func[2]);

		$kind = array();
		if (in_array($SYSBAR_LEVEL, array('root', 'administrator'))) {
			$kind[0]  = $MSG['func_mainmenu'][$sysSession->lang];   // 選單
			$kind[1]  = $MSG['func_function'][$sysSession->lang];   // 功能
		}
		switch ($SYSBAR_MENU) {
			case 'academic' :   // 管理室
			case 'school' :   // 校園廣場
				$kind[5]  = $MSG['questionnaire'][$sysSession->lang];             // 問卷
				$kind[6]  = $MSG['topic_discussion'][$sysSession->lang];          // 議題討論
				$kind[7]  = $MSG['online_idscussion'][$sysSession->lang];         // 線上討論
				break;
			case 'teach' :   // 教師辦公室
			case 'learn' :   // 教室
				if ($SYSBAR_LEVEL == 'teacher')
				{
					$kind[2]  = $MSG['course_content'][$sysSession->lang];            // 教材
					$kind[3]  = $MSG['homework'][$sysSession->lang];                  // 作業
					$kind[4]  = $MSG['examination'][$sysSession->lang];               // 考試
				}
				$kind[5]  = $MSG['questionnaire'][$sysSession->lang];             // 問卷
				$kind[6]  = $MSG['topic_discussion'][$sysSession->lang];          // 議題討論
				if ($SYSBAR_LEVEL == 'teacher') $kind[9]  = $MSG['topic_discussion_group'][$sysSession->lang];    // [群組] 議題討論
				$kind[7]  = $MSG['online_idscussion'][$sysSession->lang];         // 線上討論
				if ($SYSBAR_LEVEL == 'teacher') $kind[10] = $MSG['online_idscussion_group'][$sysSession->lang];   // [群組] 線上討論
				break;
			case 'direct' :   // 導師辦公室
				$kind[5]  = $MSG['questionnaire'][$sysSession->lang];             // 問卷
				$kind[6]  = $MSG['topic_discussion'][$sysSession->lang];          // 議題討論
				// $kind[9]  = $MSG['topic_discussion_group'][$sysSession->lang];    // [群組] 議題討論
				$kind[7]  = $MSG['online_idscussion'][$sysSession->lang];         // 線上討論
				// $kind[10] = $MSG['online_idscussion_group'][$sysSession->lang];   // [群組] 線上討論
				break;
			case 'personal' :   // 個人區
				if ($fid2 < 3) {
					$kind[5]  = $MSG['questionnaire'][$sysSession->lang];             // 問卷
					$kind[6]  = $MSG['topic_discussion'][$sysSession->lang];          // 議題討論
					$kind[7]  = $MSG['online_idscussion'][$sysSession->lang];         // 線上討論
				}
				break;
			case 'class' :   // 班級
				/*
				$kind[2]  = $MSG['course_content'][$sysSession->lang];            // 教材
				$kind[3]  = $MSG['homework'][$sysSession->lang];                  // 作業
				$kind[4]  = $MSG['examination'][$sysSession->lang];               // 考試
				$kind[5]  = $MSG['questionnaire'][$sysSession->lang];             // 問卷
				$kind[6]  = $MSG['topic_discussion'][$sysSession->lang];          // 議題討論
				$kind[9]  = $MSG['topic_discussion_group'][$sysSession->lang];    // [群組] 議題討論
				$kind[7]  = $MSG['online_idscussion'][$sysSession->lang];         // 線上討論
				$kind[10] = $MSG['online_idscussion_group'][$sysSession->lang];   // [群組] 線上討論
				*/
				break;
			default:
		}
		$kind[8]  = $MSG['out_link'][$sysSession->lang];                  // 外部連結
		return $kind;
	}

	/**
	 * 取得存取設定的身份列表
	 * @version
	 * @param
	 * @return void
	 **/
	function getSysbarRoleList() {
		global $sysSession, $sysRoles, $MSG, $SYSBAR_MENU, $SYSBAR_LEVEL;

		// 身份
		$roles = array();
		$oegLevel = $SYSBAR_LEVEL;
		$level = getAdminLevel($sysSession->username);
		if ($level & $sysRoles['root'])
			$SYSBAR_LEVEL = 'root';
		else if ($level & $sysRoles['administrator'])
			$SYSBAR_LEVEL = 'administrator';

        $guest = dbGetOne('WM_school', 'guest', "school_host='{$_SERVER['HTTP_HOST']}'");

		switch ($SYSBAR_MENU) {
			case 'academic' :
				switch ($SYSBAR_LEVEL) {
					case 'root'           :
						$roles[0]    = $MSG['bar_role_all'][$sysSession->lang];           // 全部
						$roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
						$roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
						// $roles[8192] = $MSG['bar_role_root'][$sysSession->lang];          // 最高管理者 (一機只有一人)
						break;
					case 'administrator'  :
						$roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
						// $roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
						break;
				}
				break;
			case 'teach'    :
				switch ($SYSBAR_LEVEL) {
					case 'root'           :
					case 'administrator'  :
					case 'manager'        :
					case 'manager_course' :
						$roles[0]    = $MSG['bar_role_all'][$sysSession->lang];           // 全部
						$roles[64]   = $MSG['bar_role_assistant'][$sysSession->lang];     // 助教
						$roles[128]  = $MSG['bar_role_instructor'][$sysSession->lang];    // 講師
						$roles[512]  = $MSG['bar_role_teacher'][$sysSession->lang];       // 教師 (通常比講師多具有教材管理編修權)
						if ($SYSBAR_LEVEL == 'root') {
							$roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
							$roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
							// $roles[8192] = $MSG['bar_role_root'][$sysSession->lang];          // 最高管理者 (一機只有一人)
						}
						if ($SYSBAR_LEVEL == 'administrator') {
							$roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
							// $roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
						}
						/* 保留
						if (($SYSBAR_LEVEL == 'manager') || ($SYSBAR_LEVEL == 'manager_course')) {
							// $roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
						}
						*/
						break;
				}
				break;
			case 'learn'    :
				switch ($SYSBAR_LEVEL) {
					case 'root'           :
					case 'administrator'  :
					case 'manager'        :
					case 'manager_course' :
					case 'teacher'        :
					case 'instructor'     :
					case 'assistant'      :
						$roles[0]    = $MSG['bar_role_all'][$sysSession->lang];           // 全部
						if($guest == 'Y')
                            $roles[1]= $MSG['bar_role_guest'][$sysSession->lang];         // 參觀者
						// $roles[2]    = $MSG['bar_role_senior'][$sysSession->lang];        // 學長
						// $roles[4]    = $MSG['bar_role_paterfamilias'][$sysSession->lang]; // 家長
						// $roles[8]    = $MSG['bar_role_superintendent'][$sysSession->lang];// 長官/督學
						$roles[16]   = $MSG['bar_role_auditor'][$sysSession->lang];       // 旁聽生
						$roles[32]   = $MSG['bar_role_student'][$sysSession->lang];       // 正式生
						if ($SYSBAR_LEVEL == 'root') {
							$roles[64]   = $MSG['bar_role_assistant'][$sysSession->lang];     // 助教
							$roles[128]  = $MSG['bar_role_instructor'][$sysSession->lang];    // 講師
							$roles[512]  = $MSG['bar_role_teacher'][$sysSession->lang];       // 教師 (通常比講師多具有教材管理編修權)
							$roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
							$roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
							// $roles[8192] = $MSG['bar_role_root'][$sysSession->lang];          // 最高管理者 (一機只有一人)
						}
						if ($SYSBAR_LEVEL == 'administrator') {
							$roles[64]   = $MSG['bar_role_assistant'][$sysSession->lang];     // 助教
							$roles[128]  = $MSG['bar_role_instructor'][$sysSession->lang];    // 講師
							$roles[512]  = $MSG['bar_role_teacher'][$sysSession->lang];       // 教師 (通常比講師多具有教材管理編修權)
							$roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
							// $roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
						}
						if (($SYSBAR_LEVEL == 'manager') || ($SYSBAR_LEVEL == 'manager_course')) {
							$roles[64]   = $MSG['bar_role_assistant'][$sysSession->lang];     // 助教
							$roles[128]  = $MSG['bar_role_instructor'][$sysSession->lang];    // 講師
							$roles[512]  = $MSG['bar_role_teacher'][$sysSession->lang];       // 教師 (通常比講師多具有教材管理編修權)
							// $roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
						}
						if ($SYSBAR_LEVEL == 'teacher') {
							$roles[64]   = $MSG['bar_role_assistant'][$sysSession->lang];     // 助教
							$roles[128]  = $MSG['bar_role_instructor'][$sysSession->lang];    // 講師
							$roles[512]  = $MSG['bar_role_teacher'][$sysSession->lang];       // 教師 (通常比講師多具有教材管理編修權)
						}
						if ($SYSBAR_LEVEL == 'instructor') {
							$roles[64]   = $MSG['bar_role_assistant'][$sysSession->lang];     // 助教
							// $roles[128]  = $MSG['bar_role_instructor'][$sysSession->lang];    // 講師
						}
						/* 保留
						if ($SYSBAR_LEVEL == 'assistant') {
							// $roles[64]   = $MSG['bar_role_assistant'][$sysSession->lang];     // 助教
						}
						*/
						break;
				}
				break;
			case 'direct'   :
				switch ($SYSBAR_LEVEL) {
					case 'root'           :
						$roles[0]    = $MSG['bar_role_all'][$sysSession->lang];           // 全部
						$roles[64]   = $MSG['bar_role_assistant'][$sysSession->lang];     // 助教
						$roles[1024] = $MSG['bar_role_director'][$sysSession->lang];      // 導師 (學生人員管理)
						$roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
						$roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
						// $roles[8192] = $MSG['bar_role_root'][$sysSession->lang];          // 最高管理者 (一機只有一人)
						break;
					case 'administrator'  :
						$roles[0]    = $MSG['bar_role_all'][$sysSession->lang];           // 全部
						$roles[64]   = $MSG['bar_role_assistant'][$sysSession->lang];     // 助教
						$roles[1024] = $MSG['bar_role_director'][$sysSession->lang];      // 導師 (學生人員管理)
						$roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
						// $roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
						break;
					case 'manager'        :
						$roles[0]    = $MSG['bar_role_all'][$sysSession->lang];           // 全部
						$roles[64]   = $MSG['bar_role_assistant'][$sysSession->lang];     // 助教
						$roles[1024] = $MSG['bar_role_director'][$sysSession->lang];      // 導師 (學生人員管理)
						// $roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
						break;
					case 'director'       :
						$roles[0]    = $MSG['bar_role_all'][$sysSession->lang];           // 全部
						$roles[64]   = $MSG['bar_role_assistant'][$sysSession->lang];     // 助教
						// $roles[1024] = $MSG['bar_role_director'][$sysSession->lang];      // 導師 (學生人員管理)
						break;
					case 'assistant'      :
						// $roles[0]    = $MSG['bar_role_all'][$sysSession->lang];           // 全部
						// $roles[64]   = $MSG['bar_role_assistant'][$sysSession->lang];     // 助教
						break;
				}
				break;
			case 'class'    :
				break;
			case 'personal' :
			case 'school'   :
				switch ($SYSBAR_LEVEL) {
					case 'root'           :
					case 'administrator'  :
					case 'manager'        :
						$roles[0]    = $MSG['bar_role_all'][$sysSession->lang];           // 全部
						if($guest == 'Y')
						    $roles[1]= $MSG['bar_role_guest'][$sysSession->lang];         // 參觀者
						$roles[2]    = $MSG['bar_role_senior'][$sysSession->lang];        // 學長
						// $roles[4]    = $MSG['bar_role_paterfamilias'][$sysSession->lang]; // 家長
						// $roles[8]    = $MSG['bar_role_superintendent'][$sysSession->lang];// 長官/督學
						// $roles[16]   = $MSG['bar_role_auditor'][$sysSession->lang];       // 旁聽生
						// $roles[32]   = $MSG['bar_role_student'][$sysSession->lang];       // 正式生
						$roles[64]   = $MSG['bar_role_assistant'][$sysSession->lang];     // 助教
						$roles[128]  = $MSG['bar_role_instructor'][$sysSession->lang];    // 講師
						$roles[512]  = $MSG['bar_role_teacher'][$sysSession->lang];       // 教師 (通常比講師多具有教材管理編修權)
						$roles[1024] = $MSG['bar_role_director'][$sysSession->lang];      // 導師 (學生人員管理)
						if ($SYSBAR_LEVEL == 'root') {
							$roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
							$roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
							// $roles[8192] = $MSG['bar_role_root'][$sysSession->lang];          // 最高管理者 (一機只有一人)
						}
						if ($SYSBAR_LEVEL == 'administrator') {
							$roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
							// $roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
						}
						/*
						if ($SYSBAR_LEVEL == 'manager') {
							// $roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
						}
						*/
						break;
				}
				break;
			default:
		}
		$SYSBAR_LEVEL = $oegLevel;
		return $roles;
	}

	/**
	 * 用來決定可以編輯哪些選單
	 * @return string : JavaScript
	 **/
	function getChgTabJS() {
		global $SYSBAR_LEVEL;
		$js = '';
		switch ($SYSBAR_LEVEL) {
			case 'root'          :   // 最高管理者
				$js  = "\n";
				$js .= ' 			case 1 : turl = "sysbar_edit_racademic.php"; break;' . "\n";
				$js .= '			case 2 : turl = "sysbar_edit_rteach.php";    break;' . "\n";
				$js .= '			case 3 : turl = "sysbar_edit_rcourse.php";   break;' . "\n";
				$js .= '			case 4 : turl = "sysbar_edit_rdirect.php";   break;' . "\n";
				// $js .= '			case 5 : turl = "sysbar_edit_rclass.php";    break;' . "\n";
				$js .= '			case 6 : turl = "sysbar_edit_rpersonal.php"; break;' . "\n";
				$js .= '			case 7 : turl = "sysbar_edit_rcampus.php";   break;' . "\n";
				break;
			case 'administrator' :   // 超級管理者
				$js  = "\n";
				$js .= ' 			case 1 : turl = "sysbar_edit_academic.php"; break;' . "\n";
				$js .= '			case 2 : turl = "sysbar_edit_teach.php";    break;' . "\n";
				$js .= '			case 3 : turl = "sysbar_edit_course.php";   break;' . "\n";
				$js .= '			case 4 : turl = "sysbar_edit_direct.php";   break;' . "\n";
				// $js .= '			case 5 : turl = "sysbar_edit_class.php";    break;' . "\n";
				$js .= '			case 6 : turl = "sysbar_edit_personal.php"; break;' . "\n";
				$js .= '			case 7 : turl = "sysbar_edit_campus.php";   break;' . "\n";
				break;
			case 'manager'       :   // 一般管理者
				$js  = "\n";
				$js .= '			case 2 : turl = "sysbar_edit_teach.php";    break;' . "\n";
				$js .= '			case 3 : turl = "sysbar_edit_course.php";   break;' . "\n";
				$js .= '			case 4 : turl = "sysbar_edit_direct.php";   break;' . "\n";
				// $js .= '			case 5 : turl = "sysbar_edit_class.php";    break;' . "\n";
				$js .= '			case 6 : turl = "sysbar_edit_personal.php"; break;' . "\n";
				$js .= '			case 7 : turl = "sysbar_edit_campus.php";   break;' . "\n";
				break;
			case 'manager_course':   // 管理者(課程)
				$js  = "\n";
				$js .= '			case 2 : turl = "sysbar_edit_teach.php";    break;' . "\n";
				$js .= '			case 3 : turl = "sysbar_edit_course.php";   break;' . "\n";
				$js .= '			case 4 : turl = "sysbar_edit_direct.php";   break;' . "\n";
				// $js .= '			case 5 : turl = "sysbar_edit_class.php";    break;' . "\n";
				break;
			case 'director'      :   // 導師
				// $js  = "\n";
				// $js .= '			case 4 : turl = "sysbar_edit_direct.php";   break;' . "\n";
				// $js .= '			case 5 : turl = "sysbar_edit_class.php";    break;' . "\n";
				break;
			case 'teacher'       :   // 教師
				$js  = "\n";
				// $js .= '			case 2 : turl = "sysbar_edit_teach.php";    break;' . "\n";
				$js .= '			case 3 : turl = "sysbar_edit_course.php";   break;' . "\n";
				break;
			case 'personal'      :   // 個人
				$js  = "\n";
				$js .= '			case 6 : turl = "sysbar_edit_personal.php"; break;' . "\n";
				break;
			default:
				// $js  = "\n";
				// $js .= '			case 6 : turl = "sysbar_edit_personal.php"; break;' . "\n";
		}
		return $js;
	}
///////////////////////////////////////////////////////////////////////////////
	/**
	 * 備份原來的設定檔 (保留十次的備份，編號越小的越新)
	 **/
	function backupFile($fname) {
		@unlink("{$fname}.bk9");
		for ($i = 8; $i >= 0; $i--) {
			@rename("{$fname}.bk{$i}", "{$fname}.bk" . ($i + 1));
		}
		@rename($fname, "{$fname}.bk0");
	}

	/**
	 * 切割功能編號
	 * @return array $func : 切割後的功能編號
	 **/
	function spiltFuncId() {
		global $sysSession, $sysConn;

		$func = array(13, 7, 5);
		// $cur_func = strval($sysSession->cur_func);
		$cur_func = $sysSession->cur_func;
		$func[0] = intval(substr($cur_func, 0, 2));
		$func[1] = intval(substr($cur_func, 2, 3));
		$func[2] = intval(substr($cur_func, 8, 2));
		if (!(
			($func[0] == 13) &&
			((3 <= $func[1]) && ($func[1] <= 8)) &&
			((0 <= $func[2]) && ($func[2] <= 6))
		)) {
			dbSet('WM_session', 'cur_func=1300700005', "idx='{$_COOKIE['idx']}'");
			$sysSession->cur_func = 1300700005;  // 編輯個人區
			$func = array(13, 7, 5);
		}
		return $func;
	}

	/**
	 * 取得 Sysbar 設定檔
	 * @param integer $fid1 : 檔案名稱的編號
	 *     3 : academic.xml    (管理處)
	 *     4 : adm_course.xml  (教師辦公室)
	 *     5 : course.xml      (教室)
	 *     6 : adm_class.xml   (導師)
	 *     7 : personal.xml    (個人區)
	 *     8 : system.xml      (校園廣場)
	 *     9 : class.xml       (班級)
	 * @param integer $fid2 : 執行身份
	 *     1 : 管理者
	 *     2 : 管理者
	 *     3 : 導師
	 *     4 : 教師
	 *     5 : 個人
	 *     6 : 管理者
	 * @return string $filename : 完整的檔案路徑，含檔案名稱
	 **/
	function getSysbarSetFile($SYSBAR_MENU, $SYSBAR_LEVEL, $rev=false) {
		global $sysSession;

		// 課程編號
		$csid = $sysSession->course_id;
		if (empty($csid) || ($csid <= 10000000) || ($csid >= 100000000)) {
			$csid = 10000000;
		}
		// 教室編號
		$caid = $sysSession->class_id;
		if (empty($caid) || ($caid <= 1000000) || ($caid >= 10000000)) {
			$caid = 1000000;
		}
		// 學校編號
		$sid = $sysSession->school_id;
		if (empty($sid) || ($sid <= 10000) || ($sid >= 100000)) {
			$sid = 10001;
		}

		$fname = '';
		switch ($SYSBAR_MENU) {
			case 'academic' :
				if (in_array($SYSBAR_LEVEL, array('root', 'administrator')))
					$fname = 'academic.xml';
				break;
			case 'ep_academic' :
				if (in_array($SYSBAR_LEVEL, array('root', 'administrator')))
					$fname = 'ep_academic.xml';
				break;
			case 'teach'    :
				if (in_array($SYSBAR_LEVEL, array('root', 'administrator', 'manager', 'manager_course')))
					$fname = 'adm_course.xml';
				break;
			case 'learn'    :
				if (in_array($SYSBAR_LEVEL, array('root', 'administrator', 'manager', 'manager_course', 'teacher')))
					$fname = 'course.xml';
				break;
			case 'direct'   :
				if (in_array($SYSBAR_LEVEL, array('root', 'administrator', 'manager', 'manager_course')))
					$fname = 'adm_class.xml';
				break;
			case 'personal' :
				if (in_array($SYSBAR_LEVEL, array('root', 'administrator', 'manager', 'manager_course', 'personal')))
					$fname = 'personal.xml';
				break;
			case 'school'   :
				if (in_array($SYSBAR_LEVEL, array('root', 'administrator', 'manager')))
					$fname = 'system.xml';
				break;
			case 'class'    :
				if (in_array($SYSBAR_LEVEL, array('root', 'administrator', 'manager', 'manager_course', 'director')))
					$fname = 'class.xml';
				break;
			default:
				$fname = '';
		}

		if (empty($fname)) return '';

		$filename = '';
		switch ($SYSBAR_LEVEL) {
			case 'root'          :   /* 系統 */
				$filename = sysDocumentRoot . "/config/xml/{$fname}";
				break;
			case 'administrator' :   /* 管理者 */
				$dir = sysDocumentRoot . "/base/{$sid}/system";
				if (!@is_dir($dir)) @mkdir($dir);
				$dir = sysDocumentRoot . "/base/{$sid}/system/default";
				if (!@is_dir($dir)) @mkdir($dir);
				$filename = sysDocumentRoot . "/base/{$sid}/system/default/{$fname}";
				break;
			case 'manager'       :   /* 管理者 */
				$dir = sysDocumentRoot . "/base/{$sid}/system";
				if (!@is_dir($dir)) @mkdir($dir);
				$filename = sysDocumentRoot . "/base/{$sid}/system/{$fname}";
				break;
			case 'manager_course' :  /* 管理者 -> 教室與辦公室 */
				if (in_array($SYSBAR_MENU, array('direct', 'class'))) {   // 班級
					$filename = sysDocumentRoot . "/base/{$sid}/class/{$caid}/{$fname}";
				} else if (in_array($SYSBAR_MENU, array('teach', 'learn'))) {   // 教室
					$filename = sysDocumentRoot . "/base/{$sid}/course/{$csid}/{$fname}";
				} else if (in_array($SYSBAR_MENU, array('personal'))) {   // 個人
					$udir = MakeUserDir($sysSession->username);
					$filename = $udir . '/personal.xml';
				} else {
					$filename = '';
				}
				break;
			case 'director'      :  /* 導師 -> 班級 */
				$filename = sysDocumentRoot . "/base/{$sid}/class/{$caid}/class.xml";
				break;
			case 'teacher'       :  /* 教師 -> 教室 */
				$filename = sysDocumentRoot . "/base/{$sid}/course/{$csid}/course.xml";
				break;
			case 'personal'      :  /* 個人 */
				$udir = MakeUserDir($sysSession->username);
				$filename = $udir . '/personal.xml';
				break;
			default:
				$filename = '';
		}

		if ($rev) {
			if (empty($filename) || !@is_file($filename)) {
				$filename = ($SYSBAR_LEVEL == 'administrator') ? '' : sysDocumentRoot . "/base/{$sid}/system/{$fname}";
				if (empty($filename) || !@is_file($filename)) {
					$filename = sysDocumentRoot . "/base/{$sid}/system/default/{$fname}";
					if (!@is_file($filename)) {
						$filename = sysDocumentRoot . '/config/xml/' . $fname;
					}
				}
			}
		}
		return $filename;
	}

///////////////////////////////////////////////////////////////////////////////
	function getDefaultSet() {
		switch ($fid1) {
			case 'academic' : $env  = 1;  $role = 14336; break;   // 管理室
			case 'teach'    : $env  = 2;  $role = 704;   break;   // 教師辦公室
			case 'learn'    : $env  = 8;  $role = 752;   break;   // 教室
			case 'direct'   : $env  = 4;  $role = 1088;  break;   // 導師辦公室
			case 'class'    : $env  = 16; $role = 1136;  break;   // 班級
			case 'personal' : $env  = 31; $role = 16382; break;   // 個人區
			case 'school'   : $env  = 31; $role = 16383; break;   // 校園廣場
			default:
				$env  = 31; $role = 16382;
		}
		return array($env, $role);
	}

	/**
	 * 空白的 sysbar 設定值
	 * @return string : XML
	 **/
	function emptySysbar() {
		global $sysSession;

		$ary  = getDefaultSet();
		$env  = $ary[0];
		$role = $ary[1];

		$id = uniqid('USER_');

		$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		$xmlstr .= <<< BOF
<manifest>
	<items>
		<item id="{$id}" system="false" hidden="false" env="{$env}" role="{$role}">
			<title>
				<big5>undefined</big5>
				<gb2312>undefined</gb2312>
				<en>undefined</en>
				<euc-jp>undefined</euc-jp>
				<user-define>undefined</user-define>
			</title>
			<href target="default" kind="8">about:blank</href>
		</item>
	</items>
</manifest>
BOF;
		return $xmlstr;
	}

	/**
	 * 取得 sysbar 的 XML 設定選單
	 * @return string : XML
	 **/
	function getSysbar($SYSBAR_MENU, $SYSBAR_LEVEL) {
		global $sysSession;

		// 建立基本的空白設定值
		$xmlstr = emptySysbar();

		$filename = getSysbarSetFile($SYSBAR_MENU, $SYSBAR_LEVEL, true);
		if (!empty($filename) && @is_file($filename)) {
			$xml = file($filename);
			$xmlstr = implode('', $xml);
		}

		return $xmlstr;
	}
///////////////////////////////////////////////////////////////////////////////
	/**
	 * 儲存編修後的 sysbar
	 * @param object $xmlDocs : XML 物件
	 * @return string : 成功或失敗
	 **/
	function saveSysbar($xmlDocs, $sync_lost=false) {
		global $sysSession, $sysRoles, $SYSBAR_MENU, $SYSBAR_LEVEL;

		$filename = getSysbarSetFile($SYSBAR_MENU, $SYSBAR_LEVEL, false);
		$res = false;
		if (!is_object($xmlDocs) || empty($filename)) {
			$res = false;
		} else {
			// 清除不需要的 Tag (Begin)
			$nodes = $xmlDocs->get_elements_by_tagname('ticket');
			foreach ($nodes as $val) {
				$pnode = $val->parent_node();
				$pnode->remove_child($val);
			}
			// 清除不需要的 Tag (End)

			if ($SYSBAR_LEVEL != 'root') {
				// 除了系統設定值外，其餘需要跟系統同步選單
				$syslevel = ($SYSBAR_LEVEL == 'administrator') ? 'root' : 'administrator';
				$orgfile = getSysbarSetFile($SYSBAR_MENU, $syslevel, true); // 從學校預設選單開始讀取
				if ($xmlVars = domxml_open_file($orgfile)) {
					$xptr = xpath_new_context($xmlVars);
				}
			}

			touch($filename);
			backupFile($filename);
			$roles = array();
			switch ($SYSBAR_LEVEL) {
				case 'root'           :
					$roles[8192] = $MSG['bar_role_root'][$sysSession->lang];          // 最高管理者 (一機只有一人)
					break;
				case 'administrator'  :
					$roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
					$roles[8192] = $MSG['bar_role_root'][$sysSession->lang];          // 最高管理者 (一機只有一人)
					break;
				case 'manager'        :
				case 'manager_course' :
					$roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
					$roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
					$roles[8192] = $MSG['bar_role_root'][$sysSession->lang];          // 最高管理者 (一機只有一人)
					break;
				case 'teacher'        :
					$roles[512]  = $MSG['bar_role_teacher'][$sysSession->lang];       // 教師 (通常比講師多具有教材管理編修權)
					$roles[1024] = $MSG['bar_role_director'][$sysSession->lang];      // 導師 (學生人員管理)
					$roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
					$roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
					$roles[8192] = $MSG['bar_role_root'][$sysSession->lang];          // 最高管理者 (一機只有一人)
					break;
				case 'instructor'     :
					$roles[128]  = $MSG['bar_role_instructor'][$sysSession->lang];    // 講師
					$roles[512]  = $MSG['bar_role_teacher'][$sysSession->lang];       // 教師 (通常比講師多具有教材管理編修權)
					$roles[1024] = $MSG['bar_role_director'][$sysSession->lang];      // 導師 (學生人員管理)
					$roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
					$roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
					$roles[8192] = $MSG['bar_role_root'][$sysSession->lang];          // 最高管理者 (一機只有一人)
					break;
				case 'assistant'      :
				case 'director'       :
					$roles[64]   = $MSG['bar_role_assistant'][$sysSession->lang];     // 助教
					$roles[128]  = $MSG['bar_role_instructor'][$sysSession->lang];    // 講師
					$roles[512]  = $MSG['bar_role_teacher'][$sysSession->lang];       // 教師 (通常比講師多具有教材管理編修權)
					$roles[1024] = $MSG['bar_role_director'][$sysSession->lang];      // 導師 (學生人員管理)
					$roles[2048] = $MSG['bar_role_manager'][$sysSession->lang];       // 一般管理者
					$roles[4096] = $MSG['bar_role_administrator'][$sysSession->lang]; // 超級管理者
					$roles[8192] = $MSG['bar_role_root'][$sysSession->lang];          // 最高管理者 (一機只有一人)
					break;
			}
			$nodes = $xmlDocs->get_elements_by_tagname('item');
			foreach ($nodes as $val) {
				$id = $val->get_attribute('id');
				// 檢查必要的身份 (Begin)
				if ($xptr) $obj = xpath_eval($xptr, '//item[@id="' . $id . '"]/@role');
				if ($obj) {
					$node = $obj->nodeset[0];
					$role = ($node) ? $node->value : 0;
					$attr = intval($val->get_attribute('role'));
					reset($roles);
					foreach ($roles as $key => $v) {
						$orole += ($role & $key) ? $key : 0;
					}
					$level = 0;
					reset($sysRoles);
					foreach ($sysRoles as $key => $v) {
						// 跳掉 all 與 root，all 不需要，而 root 則都可以存取每一項
						if (($key == 'all') || ($key == 'root')) continue;
						$level += (($attr & $v) || ($orole & $v)) ? $v : 0;
					}
					$level += $sysRoles['root'];
					$val->set_attribute('role', $level);
				} else {
					$attr = intval($val->get_attribute('role'));
					if (!($attr & $sysRoles['root'])) {
						$attr += $sysRoles['root'];
						$val->set_attribute('role', $attr);
					}
				}
				// 檢查必要的身份 (End)
				// 同步連結 (Begin)
				$pos = strpos($id, 'SYS_');
				if ($pos !== false) {
					if ($xptr) $obj = xpath_eval($xptr, '//item[@id="' . $id . '"]/href/text()');
					if ($obj) {
						$node   = $obj->nodeset[0];
						$href   = ($node) ? $node->content : 'about:blank';
						$childs = $val->child_nodes();
						if ($childs) {
							foreach ($childs as $v) {
								if (($v->type != 1) || ($v->tagname != 'href')) continue;
								$p = $node->parent_node();
								$k = $p->get_attribute('kind');
								$v->set_attribute('kind', $k);
								$n = $xmlDocs->create_text_node($href);
								if ($v->has_child_nodes()) {
									$o = $v->first_child();
									$v->remove_child($o);
								}
								$v->append_child($n);
							}
						}
					}
				}
				// 同步連結 (End)
			}
			// 同步遺失的節點 (Begin)
			if ($sync_lost)
			{
				$nxptr = xpath_new_context($xmlDocs);
				$nodes = $xmlVars->get_elements_by_tagname('item');
				if ($nxptr)
				{
					foreach ($nodes as $val)
					{
						$id  = $val->get_attribute('id');
						$obj = xpath_eval($nxptr, '//item[@id="' . $id . '"]');
						if (count($obj->nodeset) <= 0)
						{
							$p   = $val->parent_node();
							$pid = $p->get_attribute('id');
							$obj = xpath_eval($nxptr, '//item[@id="' . $pid . '"]');
							if ($obj)
							{
								$node = $obj->nodeset[0];
								$n    = $val->clone_node(true);
								$node->append_child($n);
							}
						}
					}
				}
			}
			// 同步遺失的節點 (End)
			$xmlDocs->dump_file($filename);
			$res = true;
		}

		return $res;
	}
///////////////////////////////////////////////////////////////////////////////
	/**
	 * 恢復系統預設值
	 **/
	function defaultSysbar() {
		global $sysSession, $SYSBAR_MENU, $SYSBAR_LEVEL;

		$filename = getSysbarSetFile($SYSBAR_MENU, $SYSBAR_LEVEL, false);
		if (!empty($filename)) {
			backupFile($filename);
			@unlink($filename);
		}
		/*
		$xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		$xmlstr .= '<manifest></manifest>';
		return $xmlstr;
		*/
	}
///////////////////////////////////////////////////////////////////////////////

	function getQTIList($val) {
		global $sysSession, $MSG, $SYSBAR_LEVEL;

		$csid = $sysSession->course_id;
		if (empty($csid) || ($csid <= 10000000) || ($csid >= 100000000)) {
			$csid = 10000000;
		}

		$caid = $sysSession->class_id;
		if (empty($caid) || ($caid <= 1000000) || ($caid >= 10000000)) {
			$caid = 1000000;
		}

		$sid = $sysSession->school_id;
		if (empty($sid) || ($sid <= 10000) || ($sid >= 100000)) {
			$sid = 10001;
		}
		
		switch($SYSBAR_LEVEL)
		{
			case 'root' :
			case 'administrator' :
			case 'manager' :
			case 'manager_course' :
				$course_id = $sid;
				break;
			case 'teacher' :
			case 'instructor' :
			case 'assistant' :
				$course_id = $csid;
				break;
			default :
				$course_id = $caid;
		}
		
		// 測驗類型
		$exam_types = array(
			$MSG['qti_' . $val . '_type1'][$sysSession->lang],
			$MSG['qti_' . $val . '_type2'][$sysSession->lang],
			$MSG['qti_' . $val . '_type3'][$sysSession->lang],
			$MSG['qti_' . $val . '_type4'][$sysSession->lang],
			$MSG['qti_' . $val . '_type5'][$sysSession->lang]
		);

		$RS = null;
		$txt = '';
		$val = trim($val);
		switch ($val) {
			case 'exam':   // 測驗
			case 'homework':   // 作業
			case 'questionnaire':   // 問卷
				$RS = dbGetStMr('WM_qti_' . $val . '_test', '`exam_id`, `title`, `type`', "`course_id`={$course_id} order by `sort`, `exam_id`", ADODB_FETCH_ASSOC);
				if ($RS && ($RS->RecordCount() > 0)) {
					while (!$RS->EOF) {
						$qid   = $RS->fields['exam_id'];
						$title = getCaption($RS->fields['title']);
						$str   = '[' . $exam_types[$RS->fields['type']] . ']';
						$txt  .= '<option value="' . $qid . '">' . $str . $title[$sysSession->lang] . '</option>';
						$RS->MoveNext();
					}
				}
				break;
			default:
		}
		if (!empty($txt)) {
			$txt = '<select name="detail" id="detail" style="width: 160px" class="cssInput">' . $txt . '</select>';
		}
		return $txt;
	}

	/**
	 * 取得討論板的列表
	 * @param string $val : 哪個環境的討論板
	 * @return
	 **/
	function getSubjectList($val) {
		global $sysSession, $MSG;

		$csid = $sysSession->course_id;
		if (empty($csid) || ($csid <= 10000000) || ($csid >= 100000000)) {
			$csid = 10000000;
		}

		$caid = $sysSession->class_id;
		if (empty($caid) || ($caid <= 1000000) || ($caid >= 10000000)) {
			$caid = 1000000;
		}

		$sid = $sysSession->school_id;
		if (empty($sid) || ($sid <= 10000) || ($sid >= 100000)) {
			$sid = 10001;
		}

		if (($val == 'course') && ($csid == 10000000)) $val = 'school';

		// 議題狀態
		$stateary = array(
			'disable' => $MSG['type_disable'][$sysSession->lang],
			'open'    => $MSG['type_open'][$sysSession->lang],
			'taonly'  => $MSG['type_taonly'][$sysSession->lang]
		);
		// 隱藏或顯示
		$visary = array(
			'visible' => $MSG['title_visible'][$sysSession->lang],
			'hidden'  => $MSG['title_hidden'][$sysSession->lang]
		);

		$RS = null;
		$txt = '';
		$val = trim($val);
		switch ($val) {
			case 'school' :   // 學校
				$txt = '';
				$RS = dbGetStMr('WM_bbs_boards', '`board_id`, `bname`', "`owner_id`='{$sid}' and `board_id` != '1000000001'", ADODB_FETCH_ASSOC);
				if ($RS && ($RS->RecordCount() > 0)) {
					while (!$RS->EOF) {
						$bid     = $RS->fields['board_id'];
						$title   = getCaption($RS->fields['bname']);
						$txt .= '<option value="' . $bid . '">' . $title[$sysSession->lang] . '</option>';
						$RS->MoveNext();
					}
				}
				break;

			case 'course' :   // 課程
				$txt = '';
				$tab    = 'WM_term_subject left join WM_bbs_boards on WM_bbs_boards.board_id = WM_term_subject.board_id';
				$fields = '`WM_term_subject`.`board_id`, `state`, `visibility`, `bname`';
				$where  = "`course_id`={$csid} order by `permute`";
				$RS = dbGetStMr($tab, $fields, $where, ADODB_FETCH_ASSOC);
				if ($RS && ($RS->RecordCount() > 0)) {
					while (!$RS->EOF) {
						$bid     = $RS->fields['board_id'];
						$title   = getCaption($RS->fields['bname']);
						$state   = $stateary[$RS->fields['state']];
						$visible = $visary[$RS->fields['visibility']];
						$state   = '[' . $state . ']';
						$visible = '[' . $visible . ']';
						$txt .= '<option value="' . $bid . '">' . $state . $visible . $title[$sysSession->lang] . '</option>';
						$RS->MoveNext();
					}
				}
				break;

			case 'course_group' :   // 課程中的分組
				$tab    = "WM_student_separate,WM_student_group,WM_bbs_boards";
				$fields = '`WM_student_group`.`board_id`, `bname`, `team_name`';
				$where  = " WM_student_group.team_id=WM_student_separate.team_id and WM_student_group.course_id=WM_student_separate.course_id and WM_student_group.course_id={$sysSession->course_id} and WM_bbs_boards.board_id = WM_student_group.board_id order by WM_student_group.team_id,WM_student_group.group_id";
				$RS = dbGetStMr($tab, $fields, $where, ADODB_FETCH_ASSOC);
				if ($RS && ($RS->RecordCount() > 0)) {
					while (!$RS->EOF) {
						$bid   = $RS->fields['board_id'];
						$title = getCaption($RS->fields['bname']);
						$tname = getCaption($RS->fields['team_name']);
						$tname = '[' . $tname[$sysSession->lang] . ']';
						$txt .= '<option value="' . $bid . '">'. $tname . $title[$sysSession->lang] . '</option>';
						$RS->MoveNext();
					}
				}
				break;

			case 'class'  :   // 班級
				break;

			default:
		}
		if (!empty($txt)) {
			$txt = '<select name="detail" id="detail" style="width: 160px" class="cssInput">' . $txt . '</select>';
		}
		return $txt;
	}

	/**
	 * 取得聊天室的列表
	 * @param string $val : 哪個環境的聊天室
	 * @return
	 **/
	function getChatRoomList($val) {
		global $sysSession, $MSG;

		$csid = $sysSession->course_id;
		if (empty($csid) || ($csid <= 10000000) || ($csid >= 100000000)) {
			$csid = 10000000;
		}

		$caid = $sysSession->class_id;
		if (empty($caid) || ($caid <= 1000000) || ($caid >= 10000000)) {
			$caid = 1000000;
		}

		$sid = $sysSession->school_id;
		if (empty($sid) || ($sid <= 10000) || ($sid >= 100000)) {
			$sid = 10001;
		}

		if (($val == 'course') && ($csid == 10000000)) $val = 'school';
		$RS  = null;
		$txt = '';
		$val = trim($val);
		switch ($val) {
			case 'school' :   // 學校
				$RS = dbGetStMr('WM_chat_setting', '`rid`, `title`', "`owner`='{$sid}'", ADODB_FETCH_ASSOC);
				if ($RS && ($RS->RecordCount() > 0)) {
					while (!$RS->EOF) {
						$rid = $RS->fields['rid'];
						$title = getCaption($RS->fields['title']);
						$txt .= '<option value="' . $rid . '">' . $title[$sysSession->lang] . '</option>';
						$RS->MoveNext();
					}
				}
				break;

			case 'course' :   // 課程
				$txt = '';
				$RS = dbGetStMr('WM_chat_setting', '`rid`, `title`', "`owner`='{$csid}'", ADODB_FETCH_ASSOC);
				if ($RS && ($RS->RecordCount() > 0)) {
					while (!$RS->EOF) {
						$rid = $RS->fields['rid'];
						$title = getCaption($RS->fields['title']);
						$txt .= '<option value="' . $rid . '">' . $title[$sysSession->lang] . '</option>';
						$RS->MoveNext();
					}
				}
				break;

			case 'course_group' :   // 課程中的分組
				$txt = '';
				// $RS = dbGetStMr('WM_student_group,WM_chat_setting,WM_student_separate', 'WM_chat_setting.*,WM_student_separate.team_name', "CONCAT(WM_student_group.course_id,'_',WM_student_group.team_id,'_',WM_student_group.group_id)=WM_chat_setting.owner and WM_student_group.course_id=WM_student_separate.course_id and WM_student_group.team_id=WM_student_separate.team_id {$sqls}");
				$RS = dbGetStMr('WM_student_group,WM_chat_setting,WM_student_separate', 'WM_chat_setting.*,WM_student_separate.team_name', "WM_student_separate.course_id =" . $sysSession->course_id . " and WM_student_group.course_id=WM_student_separate.course_id and WM_student_group.team_id=WM_student_separate.team_id and CONCAT(WM_student_group.course_id,'_',WM_student_group.team_id,'_',WM_student_group.group_id)=WM_chat_setting.owner {$sqls}", ADODB_FETCH_ASSOC);
				if ($RS && ($RS->RecordCount() > 0)) {
					while (!$RS->EOF) {
						$rid = $RS->fields['rid'];
						$title = getCaption($RS->fields['title']);
						$tname = getCaption($RS->fields['team_name']);
						$txt .= '<option value="' . $rid . '">' . '[' . $tname[$sysSession->lang] . ']' . $title[$sysSession->lang] . '</option>';
						$RS->MoveNext();
					}
				}
				break;

			case 'class'  :   // 班級
				break;

			default:
		}
		if (!empty($txt)) {
			$txt = '<select name="detail" id="detail" style="width: 160px" class="cssInput">' . $txt . '</select>';
		}
		return $txt;
	}

	/**
	 * 取得各種選單的類型
	 * @param integer $val : 種類
	 *  1 => 功能
	 *  2 => 教材
	 *  3 => 作業
	 *  4 => 考試
	 *  5 => 問卷
	 *  6 => 議題討論
	 *  9 => 議題討論
	 *  7 => 線上討論
	 * 10 => 線上討論
	 *  8 => 外部連結
	 * @return
	 **/
	function getSysbarKind($val) {
		global $sysSession, $MSG, $SYSBAR_LEVEL, $SYSBAR_MENU;

		$html = '';
		switch (intval($val)) {
			case 1:   // 功能
				$html  = '<input type="text" name="detail" id="detail" value="" style="width: 120px" class="cssInput">';
				$html .= '&nbsp;';
				$html .= '<input type="button" value="' . $MSG['browser'][$sysSession->lang]. '" class="cssBtn" onclick="browseFile(\'sysbar_listfunc.php\')">';
				break;

			case 2:   // 教材
				//#47329[Chrome][教師/教室管理/功能列設定] 新增一個功能，選擇「教材檔案」，檔案路徑沒有傳回欄位中：調整屬性id值
                $html  = '<input type="text" name="detail" id="url" value="" style="width: 120px" class="cssInput">';
				$html .= '&nbsp;';
				$html .= '<input type="button" value="' . $MSG['browser'][$sysSession->lang]. '" class="cssBtn" onclick="browseFile(\'sysbar_listcour.php\')">';
				break;

			case 3:   // 作業
				$html = getQTIList('homework');
				break;

			case 4:   // 考試
				$html = getQTIList('exam');
				break;

			case 5:   // 問卷
				$html = getQTIList('questionnaire');
				break;

			case 6:   // 議題討論
				// 管理室 校園廣場
				if (($SYSBAR_LEVEL == 'administrator') || ($SYSBAR_LEVEL == 'manager')) $html = getSubjectList('school');
				// 教師 教室
				if (($SYSBAR_LEVEL == 'manager_course') || ($SYSBAR_LEVEL == 'teacher')) $html = getSubjectList('course');
				// 導師 班級
				if ($SYSBAR_LEVEL == 'director') $html = getSubjectList('class');
				break;

			case 9:   // 議題討論 (群組)
				// 教師 教室
				if (($SYSBAR_LEVEL == 'manager_course') || ($SYSBAR_LEVEL == 'teacher')) $html = getSubjectList('course_group');
				break;

			case 7:   // 線上討論
				// 管理室 校園廣場
				if (($SYSBAR_LEVEL == 'administrator') || ($SYSBAR_LEVEL == 'manager')) $html = getChatRoomList('school');
				// 教師 教室
				if (($SYSBAR_LEVEL == 'manager_course') || ($SYSBAR_LEVEL == 'teacher')) $html = getChatRoomList('course');
				// 導師 班級
				if ($SYSBAR_LEVEL == 'director') $html = getChatRoomList('class');
				break;
			case 10:   // 線上討論 (群組)
				// 教師 教室
				if (($SYSBAR_LEVEL == 'manager_course') || ($SYSBAR_LEVEL == 'teacher')) $html = getChatRoomList('course_group');
				break;
			case 8:   // 外部連結
				$html = '<input type="text" name="detail" value="" style="width: 160px" class="cssInput">';
				break;
			default:
		}
		if (empty($html)) $html = '<input type="text" name="detail" value="" style="width: 160px" class="cssInput">';
		return $html;
	}

	/**
	 * 取得 sysbar 的 XML 指定ID名稱
	 * @return string : XML
	 **/
	function getSysbarTitle($SYSBAR_MENU, $SYSBAR_LEVEL, $searchId) {
		global $sysSession;

		// php 5 >= 5.0.1 才可使用 simpleXML
		$xmlstr = getSysbar($SYSBAR_MENU, $SYSBAR_LEVEL);
		$simpleXmlSysbar = simplexml_load_string($xmlstr);
		$result = $simpleXmlSysbar->xpath('//items/item/item[@id="'.$searchId.'"]/title/'.strtolower($sysSession->lang));
		return (string) $result[0];
	}
?>
