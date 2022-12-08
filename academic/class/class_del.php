<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/23                                                                      *
	*		work for  : 班級查詢  - 刪除班級                                                                      *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: class_del.php,v 1.1 2010/02/24 02:38:14 saly Exp $                                                                                          *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/class_group.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(PEAR_INSTALL_DIR . '/System.php');

	$sysSession->cur_func = '2400100300';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{
	}

	function destroyBoard($bid)
	{
		global $sysSession;
		// 刪除夾檔 (Begin)
		$path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$sysSession->course_id}/board/{$bid}";
		if (is_dir($path)) @System::rm("-rf {$path}");
		$path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$sysSession->course_id}/quint/{$bid}";
		if (is_dir($path)) @System::rm("-rf {$path}");
		$path = sysDocumentRoot . "/base/{$sysSession->school_id}/board/{$bid}";
		if (is_dir($path)) @System::rm("-rf {$path}");
		$path = sysDocumentRoot . "/base/{$sysSession->school_id}/quint/{$bid}";
		if (is_dir($path)) @System::rm("-rf {$path}");
		// 刪除夾檔 (End)
		// 刪除張貼
		dbDel('WM_bbs_posts', "`board_id`={$bid}");
		dbDel('WM_bbs_order', "`board_id`={$bid}");
		dbDel('WM_bbs_collecting', "`board_id`={$bid}");
		dbDel('WM_bbs_ranking', "`board_id`={$bid}");
		dbDel('WM_bbs_readed', "`board_id`={$bid}");
	}

/**
   查詢的 XML

< ?xml version="1.0" encoding="UTF-8" ? >
<manifest>
	<class_id></class_id>     <- 班級代碼
</manifest>
**/

		// echo  $GLOBALS['HTTP_RAW_POST_DATA'];
		// exit();

	$query = '';
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {

			$class_ids = getNodeValue($dom, 'class_id'); // 取出班級代碼

			// 原使用 explide 切割，改用 preg_split 來切割，同時也濾掉不合法的字元
			$class_id = preg_split('/[^\d@]+/', $class_ids, -1, PREG_SPLIT_NO_EMPTY);
			$num = count($class_id);

			$msg = '';
			for($i=0;$i < $num;$i++){   // for begin

				$class_id2 = explode('@',$class_id[$i]);

				// parent_id ($class_id2[0]) & child_id ($class_id2[1])

				// 班級名稱 & discuss & bulletin
				list($v1,$discuss,$bulletin) = dbGetStSr('WM_class_main','caption,discuss,bulletin','class_id=' . $class_id2[1], ADODB_FETCH_NUM);
				$lang = unserialize($v1);
				$class_name = $lang[$sysSession->lang];

				// 判斷 此 班級 底下  是否 有 子節點 (begin)
				list($child) = dbGetStSr('WM_class_group','child','parent=' . $class_id2[1], ADODB_FETCH_NUM);

				if ($child == 0){
					// 判斷 此 班級 底下 是否 有 老師或助教 (begin)
					list($v3, $v4) = $sysConn->GetRow('SELECT SUM(IF(role&' . ($sysRoles['director'] | $sysRoles['assistant']) .
														',1,0)), SUM(IF(role&' . $sysRoles['student'] .
														',1,0)) FROM `WM_class_member` WHERE `class_id` =' . $class_id2[1]);

					if ($v3 == 0){
						// 判斷 此 班級 底下 是否 有 學生 (begin)

						if ($v4 == 0){
							// 判斷 此 班級 底下  是否 隸屬於 在 子節點 (begin)

							list($v3) = dbGetStSr('WM_class_group','count(*)','child=' . $class_id2[1], ADODB_FETCH_NUM);

							if ($v3 == 1) { // 如果 只有 隸屬在 1 個 子節點

								dbDel('WM_class_group', 'parent=' . $class_id2[1] . ' and child=0');

								dbSet('WM_class_group', 'child=0', 'parent=' . $class_id2[0] . ' and child=' . $class_id2[1]);

								dbDel('WM_bbs_boards','board_id in (' . $discuss . ',' . $bulletin . ')');
								destroyBoard($discuss);
								destroyBoard($bulletin);

								$RS = dbDel('WM_class_main', 'class_id=' . $class_id2[1]);

								if ($RS) {
									wmSysLog($sysSession->cur_func, $sysSession->school_id ,$class_id2[1] ,0, 'manager', $_SERVER['PHP_SELF'], '刪除班級!');

									$msg .= '[ ' . $class_name . ' ] ' . $MSG['title79'][$sysSession->lang] . "\r\n";
								}else{
									$msg .= '[ ' . $class_name . ' ] ' . $MSG['title80'][$sysSession->lang] . "\r\n";
								}


							}else{
								// 只刪除 勾選 要刪除的 節點

								dbDel('WM_class_group', 'parent=' . $class_id2[0] . ' and child=' . $class_id2[1]);
								$msg .= '[ ' . $class_name . ' ] '  . $MSG['title79'][$sysSession->lang] . "\r\n";

							}
							// 判斷 此 班級 底下  是否 隸屬於 在 子節點 (end)

						}else{
							$msg .= '[ ' . $class_name . ' ] '  . $MSG['title78'][$sysSession->lang] . "\r\n";
						}
						// 判斷 此 班級 底下 是否 有 學生 (end)
					}else{
						$msg .= '[ ' . $class_name . ' ] '  . $MSG['title78'][$sysSession->lang] . "\r\n";
					}

					// 判斷 此 班級 底下 是否 有 老師或助教 (end)

				}else{
					$msg .= '[ ' . $class_name . ' ] '  . $MSG['title77'][$sysSession->lang] . "\r\n";
				}

				// 判斷 此 班級 底下  是否 有 子節點 (end)

			}   // for  end
			header("Content-type: text/xml");
			$result = "<manifest><result>$msg</result></manifest>";
			echo $result;
		}else{
			header("Content-type: text/xml");
			$result = "<manifest><result>1</result></manifest>";
			echo $result;
		}

	} else {
		header("Content-type: text/xml");
		$result = "<manifest><result>1</result></manifest>";
		echo $result;
	}

?>
