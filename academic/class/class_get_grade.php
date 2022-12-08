<?php
    /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/30                                                                       *
	*		work for  : �d�߯Z�� ���U���H�����Z                                                                      *
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
	// �w�����ˬd

	/**
	 * �إ� �Z�Ū� XML ��
	 * @pram $val
	 *  ( �m�W,�b��,�ʧO ���� Email)
	 *
	 **/
	function buildClassXML($val) {
		if (!is_array($val) || !is_array($val[0])) return '';

		$result = '';
		$cnt = count($val);
		// ��X�Z�Ū� XML (Begin)
		for ($i = 0; $i < $cnt; $i++) { // Begin for ($i = 0; $i < $cnt; $i++)

			list($first_name,$last_name) = dbGetStSr('WM_user_account','first_name,last_name','username="' . $val[$i]['username'] . '"', ADODB_FETCH_NUM);

			// �m�W
			// Bug#1263 �u��m�W����ܤ����ӭӤH�y�t�A�ӫ��ӭӤH�m�W���]�w by Small 2006/12/28
			$real_name = htmlspecialchars(checkRealname($first_name,$last_name));

			// ��X XML
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
		// ��X�ҵ{�� XML (End)
		return $result;
	}

/**
 * ========================================================================================
 *                                     �D�{���}�l
 * ========================================================================================
 �d�ߪ� XML

< ?xml version="1.0" encoding="UTF-8" ? >
<manifest>
	<classes_id></classes_id>     <- �d�ߪ� ��b���Ӹ`�I
	<page_serial></page_serial>   <- �ĴX��
	<page_num></page_num> <- �@����ܴX��
	<sby1></sby1>   <- �Ƨ����
</manifest>

**/

	//echo $GLOBALS['HTTP_RAW_POST_DATA'];
	//die();
	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		header("Content-type: text/xml");
		echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n";

		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			echo '<manifest></manifest>';
			exit;
		}
		$cid  = array();   // �s��n�d�ߪ��Z�� ID
		$data = array();

		// �s�սs��
		$group_id = intval(getNodeValue($dom, 'classes_id'));

		// �ĴX��
		$page_no  = intval(getNodeValue($dom, 'page_serial'));

		// �@����ܴX��
		$page_num = intval(getNodeValue($dom, 'page_num'));

		//  �q�ĴX���}�l����
		$limit_begin = (($page_no-1)*$page_num);

		// �Ƨ����
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
			// ��X�`����
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
			// ���o���թҦ��H����� (end)
		}

		if(Grade_Calculate == 'Y')
		{ // Y : �H�Ǥ��Ƭ��[�v��
			$param['%TOTAL_AVG%'] = 'round(sum(IF((GS.total > 0) && (TC.credit > 0),TC.credit * GS.total,0)) / sum(if(GS.total > 0,TC.credit,0)),2)';
		}
		else
		{ // N : ���H�Ǥ��Ƭ��[�v�� sum(course score) / count(course ��)
			$param['%TOTAL_AVG%'] = 'round(sum(GS.total)/count(TM.course_id),2)';
		}
		$sqls  = str_replace(array_keys($param), $param, $Sqls['get_student_grade_list']);
		$sqls .= 'order by ' . $sby1;

		// ��X�`����
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
