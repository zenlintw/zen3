<?php
    /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/30                                                                       *
	*		work for  : 查詢班級 底下的人員成績                                                                      *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: class_get_grade.php,v 1.1 2010/02/24 02:38:14 saly Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');
    
	$sysSession->cur_func = '2400100500';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{

	}
	// 安全性檢查

	/**
	 * 建立 班級的 XML 檔
	 * @pram $val
	 *  ( 姓名,帳號,性別 身份 Email)
	 *
	 **/
	function buildClassXML($val) {
		if (!is_array($val) || !is_array($val[0])) return '';

		$result = '';
		$cnt = count($val);
		// 輸出班級的 XML (Begin)
		for ($i = 0; $i < $cnt; $i++) { // Begin for ($i = 0; $i < $cnt; $i++)

			list($first_name,$last_name) = dbGetStSr('WM_user_account','first_name,last_name','username="' . $val[$i]['username'] . '"', ADODB_FETCH_NUM);

			// 姓名
			// Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
			$real_name = htmlspecialchars(checkRealname($first_name,$last_name));

			// 輸出 XML
			$result .= <<< BOF
	<class id="{$val[$i]['username']}" checked="false">
		<realname>{$real_name}</realname>
		<username>{$val[$i]['username']}</username>
		<total_course>{$val[$i]['total_course']}</total_course>
		<G60>{$val[$i]['greater']}</G60>
		<L60>{$val[$i]['smaller']}</L60>
		<total_avge>{$val[$i]['total_avg']}</total_avge>
	</class>
BOF;
		} // End for ($i = 0; $i < $cnt; $i++)
		// 輸出課程的 XML (End)
		return $result;
	}

/**
 * ========================================================================================
 *                                     主程式開始
 * ========================================================================================
 查詢的 XML

< ?xml version="1.0" encoding="UTF-8" ? >
<manifest>
	<classes_id></classes_id>     <- 查詢的 位在那個節點
	<page_serial></page_serial>   <- 第幾頁
	<page_num></page_num> <- 一頁顯示幾筆
	<sby1></sby1>   <- 排序欄位
</manifest>

**/

	//echo $GLOBALS['HTTP_RAW_POST_DATA'];
	//die();
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		header("Content-type: text/xml");
		echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n";

		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			echo '<manifest></manifest>';
			exit;
		}
		$cid  = array();   // 存放要查詢的班級 ID
		$data = array();

		// 群組編號
		$group_id = intval(getNodeValue($dom, 'classes_id'));

		// 第幾頁
		$page_no  = intval(getNodeValue($dom, 'page_serial'));

		// 一頁顯示幾筆
		$page_num = intval(getNodeValue($dom, 'page_num'));

		//  從第幾筆開始抓資料
		$limit_begin = (($page_no-1)*$page_num);

		// 排序欄位
		$sby1 = getNodeValue($dom, 'sby1');

		// if ($group_id > 1000000) begin
		if ($group_id > 1000000)
		{
			$table_alis = 'CM';
			$param = array(
				'%TABLE_ALIS%'      => $table_alis,
				'%TABLE_LEFT%'      => 'WM_class_member as ' . $table_alis . ' left join WM_term_major as TM  on ' . $table_alis . '.username = TM.username ',
				'%OTHER_CONDITION%' => $table_alis . '.class_id=' . $group_id,
				'%TOTAL_AVG%'       => ''
			);
			// 抓出總筆數
		}
		else
		{
			$table_alis = 'UA';
			$param = array(
				'%TABLE_ALIS%'      => $table_alis,
				'%TABLE_LEFT%'      => 'WM_user_account as ' . $table_alis . ' left join WM_term_major as TM  on ' . $table_alis . '.username = TM.username ',
				'%OTHER_CONDITION%' => $table_alis . '.username != "' . sysRootAccount . '"',
				'%TOTAL_AVG%'       => ''
			);
			if (substr_count($sby1,'username') !== 0) $sby1 = $table_alis . '.' . $sby1;
			// 取得全校所有人的資料 (end)
		}

		if(Grade_Calculate == 'Y')
		{ // Y : 以學分數為加權數
			$param['%TOTAL_AVG%'] = 'round(sum(IF((GS.total > 0) && (TC.credit > 0),TC.credit * GS.total,0)) / sum(if(GS.total > 0,TC.credit,0)),2)';
		}
		else
		{ // N : 不以學分數為加權數 sum(course score) / count(course 數)
			$param['%TOTAL_AVG%'] = 'round(sum(GS.total)/count(TM.course_id),2)';
		}
		$sqls  = str_replace(array_keys($param), $param, $Sqls['get_student_grade_list']);
		$sqls .= 'order by ' . $sby1;

		// 抓出總筆數
		chkSchoolId('WM_class_member');
		$RS = $sysConn->Execute($sqls);
		$total_row = $RS ? $RS->RecordCount() : 0;
		if ($page_no > 0) $RS = $sysConn->SelectLimit($sqls, $page_num, $limit_begin);


		if ($RS)
		{
			while (!$RS->EOF)
			{
				$data[] = $RS->fields;
				$RS->MoveNext();
			}
		}

		$result = '<manifest><total_row>' . $total_row . '</total_row>' .
			buildClassXML($data) .
			'</manifest>';

		if (!empty($result)) {
			$result = str_replace('<manifest>', "<manifest><ticket>{$ticket}</ticket>", $result);
			echo $result;
		} else {
			echo "<manifest><ticket>{$ticket}</ticket></manifest>";
		}

		// if ($group_id > 1000000) end
	}
?>
