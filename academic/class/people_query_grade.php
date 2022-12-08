<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/23                                                                      *
	*		work for  : �d�� �Z�Ŧ��Z                                                                        *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: people_query_grade.php,v 1.1 2010/02/24 02:38:15 saly Exp $                                                                                           *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/people_manager.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '2400300600';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{

	}

	/**
	 * �إ� �Z�Ū� XML ��
	 * @pram $val
	 *  ( �m�W,�b��,�ʧO ���� Email)
	 *
	 **/
	function buildClassXML($val) {
	    global $MSG,$sysSession;

		if (!is_array($val) && !is_array($val[0])) return '';

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
   �d�ߪ� XML

< ?xml version="1.0" encoding="UTF-8" ? >
<manifest>
	<gpName></gpName>     <- �d�ߪ� ��b���Ӹ`�I
	<searchkey></searchkey> <- �j�M (�b�� �B �m�W �B email)
	<keyword></keyword> <-  ����r
	<sdate></sdate> <-  �׽Ҵ��� (begin)
	<edate></edate> <-  �׽Ҵ��� (end)
</manifest>
**/

	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {

			$group_id    = intval(getNodeValue($dom, 'classes_id'));                               // �s�սs��
			$searchkey   = getNodeValue($dom, 'searchkey');                                        // searchkey
			$keyword     = escape_LIKE_query_str(addslashes(trim(getNodeValue($dom, 'keyword')))); // ����r
			$sdate       = addslashes(trim(getNodeValue($dom, 'sdate')));                          //  �}�l���
			$edate       = addslashes(trim(getNodeValue($dom, 'edate')));                          //  �������
			$page_no     = intval(getNodeValue($dom, 'page_serial'));                              // �ĴX��
			$page_num    = intval(getNodeValue($dom, 'page_num'));                                 // �@����ܴX��
			$limit_begin = (($page_no-1)*$page_num);                                               //  �q�ĴX���}�l����
			$sby1        = preg_replace('/[^\w, `]+/', '', getNodeValue($dom, 'sby1'));            // �Ƨ����

	        switch ($searchkey){    // �j�M
                case 'real':      // �m�W
                	if (isset($keyword)){
						$other_condition = ' if(TABLE_ALIS.first_name REGEXP "^[0-9A-Za-z _-]$" && TABLE_ALIS.last_name REGEXP "^[0-9A-Za-z _-]$", concat(TABLE_ALIS.first_name, " ", TABLE_ALIS.last_name), concat(TABLE_ALIS.last_name, TABLE_ALIS.first_name)) LIKE "%' . $keyword . '%" ';
					}

                    break;
                case 'account':      // �b��
                	 if (isset($keyword)){
                	 	$other_condition = ' TABLE_ALIS.username like "%' . $keyword . '%" ';
                     }
                     break;
                case 'email':      // email
                     if (isset($keyword)){
                     	$other_condition = ' TABLE_ALIS.email like "%' . $keyword . '%" ';
	                 }
                     break;
            }

			// �}�l���
			if ($sdate != ''){
				$other_condition .= " and TM.add_time >= '" . $sdate . "' ";
			}
			// �������
			if ($edate != ''){
				$other_condition .= " and TM.add_time <= '" . $edate . "' ";
			}

			$other_condition .= ' and TABLE_ALIS.username != "' . sysRootAccount . '" ';

			//  ���o �H������� (begin)
        if ($group_id == 1000000){ // ���o���թҦ��H�����

			$table_alis = 'UA';

			$table_cond = 'WM_user_account as ' . $table_alis . ' left join WM_term_major as TM  on ' . $table_alis . '.username = TM.username ';

			$sqls = $Sqls['get_student_grade_list'];

			if(Grade_Calculate == 'Y') {	// Y : �H�Ǥ��Ƭ��[�v��
				$sqls = str_replace('%TOTAL_AVG%','round(sum(IF((GS.total > 0) && (TC.credit > 0),TC.credit * GS.total,0)) / sum(if(GS.total > 0,TC.credit,0)),2)',$sqls);
			}else{	// N : ���H�Ǥ��Ƭ��[�v�� sum(course score) / count(course ��)
				$sqls = str_replace('%TOTAL_AVG%','round(sum(GS.total)/count(TM.course_id),2)',$sqls);
			}

			$sqls = str_replace('%TABLE_LEFT%',$table_cond,$sqls);

			$sqls = str_replace('%OTHER_CONDITION%',$other_condition,$sqls);

			$sqls = str_replace('%TABLE_ALIS%',$table_alis,$sqls);

			$sqls = str_replace('TABLE_ALIS',$table_alis,$sqls);

			if (substr_count($sby1,'username') > 0) $sby1 = $table_alis . '.' . $sby1;

			$sqls .= 'order by ' . $sby1;

		}else{
			// �Z��
			$table_alis = 'CM';

			$table_cond = 'WM_user_account as UA inner join ' .
			              ' WM_class_member as ' . $table_alis .
			              ' on UA.username = ' . $table_alis . '.username ' .
						  ' left join WM_term_major as TM  on ' . $table_alis . '.username = TM.username ';

			$other_condition2 = $other_condition . ' and ' . $table_alis . '.class_id=' . $group_id;

			$sqls = str_replace('%TABLE_ALIS%',$table_alis,$Sqls['get_student_grade_list']);

			$sqls = str_replace('%TABLE_LEFT%',$table_cond,$sqls);

			$sqls = str_replace('%OTHER_CONDITION%',$other_condition2,$sqls);

			$sqls = str_replace('TABLE_ALIS','UA',$sqls);

			if(Grade_Calculate == 'Y') {	// Y : �H�Ǥ��Ƭ��[�v��
				$sqls = str_replace('%TOTAL_AVG%','round(sum(IF((GS.total > 0) && (TC.credit > 0),TC.credit * GS.total,0)) / sum(if(GS.total > 0,TC.credit,0)),2)',$sqls);
			}else{	// N : ���H�Ǥ��Ƭ��[�v�� sum(course score) / count(course ��)
				$sqls = str_replace('%TOTAL_AVG%','round(sum(GS.total)/count(TM.course_id),2)',$sqls);
			}

			$sqls .= 'order by ' . $sby1;
		}

		// ��X�`����
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sqls);
		$total_row = $RS ? $RS->RecordCount() : 0;

		if ($page_no > 0) $RS = $sysConn->SelectLimit($sqls,$page_num,$limit_begin);
        if ($RS){   //  if ($RS) begin
        	while (!$RS->EOF) {
               	$data[] = $RS->fields;
               	$RS->MoveNext();
            }
        }   //  if ($RS) end

        $result = buildClassXML($data);

        echo '<', '?xml version="1.0" encoding="UTF-8" ?', ">\n",
    		 '<manifest>',
    		 '<total_row>', $total_row, '</total_row>',
    		 $result,
    		 '</manifest>';

        //  ���o �H������� (end)
	} else {

	    die('<?xml version="1.0" encoding="UTF-8" ?>' . "\n". "<manifest></manifest>\n");

	}
}
?>
