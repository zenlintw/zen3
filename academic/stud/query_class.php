<?php

    /**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: amm lee                                                         *
	 *		Creation  : 2004/01/08                                                            *
	 *		work for  : 匯出人員資料 (第二步驟 -> 查詢 某一課程群組 的 課程代碼)                                                                      *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.4                                *
	 *      $Id: query_class.php,v 1.1 2010/02/24 02:38:44 saly Exp $                                                                                          *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400500200';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$csGpTree    = array();    // 群組 Tree 的結構
	$csGpCsList  = array();    // 群組與已分組課程的清單
	$csGpCsData  = array();    // 課程詳細資料
	$temp_result = array();

//  *************************************************************************
    /**
	 * 初始化
	 **/
    function csDataInit() {
		global $csGpTree, $csGpCsList, $csGpCsData;

		// 從資料庫中取得資料 (Begin)
		$RS = dbGetStMr('`WM_class_group`', '*', '1 order by `parent`, `permute`', ADODB_FETCH_ASSOC);
		while (!$RS->EOF) {
			$csGpTree[$RS->fields['parent']][$RS->fields['permute']] = $RS->fields['child'];
			$csGpCsList[] = $RS->fields['parent'];
			$csGpCsList[] = $RS->fields['child'];
			$RS->MoveNext();
		}
		$csGpCsList = array_unique($csGpCsList);

		$RS = dbGetStMr('WM_class_main', 'class_id', 'class_id > 1000000', ADODB_FETCH_ASSOC);
		while (!$RS->EOF) {
			$csGpCsData[$RS->fields['class_id']] = $RS->fields['class_id'];
			$RS->MoveNext();
		}
		// 從資料庫中取得資料 (Begin)
	}
//  *************************************************************************
	/*
		*  查詢 課程群組 的 所屬 課程 id
		*  @param gid : 課程群組代碼
		*/
	function group_class($gid){
		global $csGpTree, $csGpCsList, $csGpCsData, $sysConn;
		$child = array();
		chkSchoolId('WM_class_group');
		if ($gid > 1000000) {            // 群組中的課程
			$sqls = 'select distinct G1.child from WM_class_group as G1 ' .
					'left join WM_class_group as G2 ' .
					'on G1.child=G2.parent and G2.child > 1000000 ' .
					'where G1.parent=' . $gid .
					' and G2.parent is null ' .
					'order by G2.permute';
			$child = $sysConn->GetCol($sqls);
		} else {                          // 全校班級 或 未分組班級
			$child = dbGetCol('WM_class_main','class_id','class_id in (' . implode(',', $csGpCsData) . ')');
		}

		return $child;
	}


	header("Content-type: text/xml");
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if ($dom = @domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			$group_id  = getNodeValue($dom, 'class_id');
			$group_ids = preg_split('/\D+/', $group_id, -1, PREG_SPLIT_NO_EMPTY);
			csDataInit();
			$temp_result = group_class($group_id);
			if (count($temp_result))
				die('<?xml version="1.0" encoding="UTF-8"?><manifest><class_id>' . implode(',', $temp_result) . '</class_id></manifest>');
		}
	}
	die('<?xml version="1.0" encoding="UTF-8"?><manifest />');

?>
