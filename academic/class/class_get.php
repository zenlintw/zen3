<?php
    /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/30                                                                       *
	*		work for  : �Z�Ÿs�պ޲z                                                                      *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: class_get.php,v 1.1 2010/02/24 02:38:14 saly Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/people_manager.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func = '2400100400';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{

	}

	// �w�����ˬd

	/**
	 * �إ� �Z�Ū� XML �� (class_id = 1000000)
	 * @pram $val
	 *  ( �m�W,�b��,�ʧO ����)
	 *
	 **/
	function buildClassXML($val) {
	    global $MSG, $sysSession, $sysConn, $Sqls;

		if (!is_array($val) || !is_array($val[0])) return '';

		$result = '';
		$cnt = count($val);
		// ��X�Z�Ū� XML (Begin)
		for ($i = 0; $i < $cnt; $i++) { // Begin for ($i = 0; $i < $cnt; $i++)
			// �m�W
			// Bug#1263 �u��m�W����ܤ����ӭӤH�y�t�A�ӫ��ӭӤH�m�W���]�w by Small 2006/12/28
			$real = htmlspecialchars(checkRealname(trim($val[$i]['first_name']),trim($val[$i]['last_name'])));
			// �ʧO
			if ($val[$i]['gender'] == 'M'){
				$gender = '/theme/default/academic/male.gif';
			}else{
				$gender = '/theme/default/academic/female.gif';
			}

			// ���ݦb���ǯZ��
			$class_sqls = str_replace('%USERNAME%',$val[$i]['username'],$Sqls['user_belong_class']);
			$class_RS = $sysConn->Execute($class_sqls);
			$belong_class = '';
			if ($class_RS){
				while ($class_RS1 = $class_RS->FetchRow()){
					$class_ary = unserialize($class_RS1['caption']);
					$belong_class .= htmlspecialchars($class_ary[$sysSession->lang]) . ',';
				}
			}
			if (strlen($belong_class) > 0){
				$belong_class = substr($belong_class,0,-1);
			}

			// ��X XML
			$result .= <<< BOF
	<class id="{$val[$i]['username']}" checked="false">
		<realname>{$real}</realname>
		<username>{$val[$i]['username']}</username>
		<gender>{$gender}</gender>
		<role>{$belong_class}</role>
	</class>
BOF;
		} // End for ($i = 0; $i < $cnt; $i++)
		// ��X�ҵ{�� XML (End)
		return $result;
	}

	/**
	* �إ� �Z�Ū� XML ��
	* (���p class_id > 1000000 )
	* @pram $val
	*  (�b��,����)
	*
	**/

	function buildClassXML_class_id($val) {
	    global $MSG,$sysSession,$sysRoles;

		if (!is_array($val) || !is_array($val[0])) return '';

		$result = '';
		$cnt = count($val);
		// ��X�Z�Ū� XML (Begin)
		for ($i = 0; $i < $cnt; $i++) { // Begin for ($i = 0; $i < $cnt; $i++)

		    // �m�W
            // Bug#1263 �u��m�W����ܤ����ӭӤH�y�t�A�ӫ��ӭӤH�m�W���]�w by Small 2006/12/28
            $real = htmlspecialchars(checkRealname(trim($val[$i]['first_name']),trim($val[$i]['last_name'])));

		    // �ʧO
		    if ($val[$i]['gender'] == 'M'){
		          $gender = '/theme/default/academic/male.gif';
		    }else{
		      $gender = '/theme/default/academic/female.gif';
		    }

		    // ����
		    if ($val[$i]['role'] == ''){
		        $role = $MSG['title73'][$sysSession->lang];
		    }else{

				if ($val[$i]['role'] >= $sysRoles['director'])
					$role = $MSG['title70'][$sysSession->lang];
				elseif ($val[$i]['role'] >= $sysRoles['assistant'])
					$role = $MSG['title67'][$sysSession->lang];
				elseif ($val[$i]['role'] >= $sysRoles['student'])
					$role = $MSG['title66'][$sysSession->lang];
				elseif ($val[$i]['role'] >= $sysRoles['paterfamilias'])
					$role = $MSG['title63'][$sysSession->lang];
				elseif ($val[$i]['role'] >= $sysRoles['senior'])
					$role = $MSG['title62'][$sysSession->lang];
				else
					$role = $MSG['title61'][$sysSession->lang];
		    }

			// ��X XML
			$result .= <<< BOF
	<class id="{$val[$i]['username']}" checked="false">
		<realname>{$real}</realname>
		<username>{$val[$i]['username']}</username>
		<gender>{$gender}</gender>
		<role>{$role}</role>
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
	<ticket></ticket>
	<classes_id></classes_id>     <- �d�ߪ� ��b���Ӹ`�I
	<page_serial></page_serial>   <- �ĴX��
	<page_num></page_num> <- �@����ܴX��
	<sby1></sby1>   <- �Ƨ����
</manifest>

**/

	// echo $GLOBALS['HTTP_RAW_POST_DATA'];
	// die();
	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			exit;
		}
		$cid = array();   // �s��n�d�ߪ��Z�� ID
		$data = array();

		// �s�սs��
        $group_id = intval(getNodeValue($dom, 'classes_id'));

		// �ĴX��
        $page_no = intval(getNodeValue($dom, 'page_serial'));

		// �@����ܴX��
        $page_num = intval(getNodeValue($dom, 'page_num'));

		//  �q�ĴX���}�l����
		$limit_begin = (($page_no-1)*$page_num);

		if ($page_no > 0)
		{
			$limit_str = ' limit ' . $limit_begin . ',' . $page_num;
		}

		// �Ƨ����
		$sby1 = getNodeValue($dom, 'sby1');

		// if ($group_id > 1000000) begin
		if ($group_id > 1000000) {
			/**
			* ���o ���Z�ũ��U ���Ҧ� �U�� & �ɮv �����
			**/
			$sqls = ' select A.class_id,A.username,A.role,B.first_name,B.last_name,B.gender' .
					' from WM_class_member  AS A left join WM_user_account as B ' .
					' on A.username = B.username ' .
					' where A.class_id = ' . $group_id .
					' order by ' . $sby1;

            chkSchoolId('WM_class_member');
			if ($page_no > 0)
			{
				$RS     = $sysConn->SelectLimit($sqls,$page_num,$limit_begin);
				$Row_RS = $sysConn->Execute($sqls);
				$total_row = ($Row_RS) ? $Row_RS->RecordCount() : 0;
			}
			else
			{
				$RS = $sysConn->Execute($sqls);
				$total_row = ($RS) ? $RS->RecordCount() : 0;
			}

			// $RS->RecordCount()
			if ($RS)
			{
				while (!$RS->EOF)
				{
					$data[] = $RS->fields;
					$RS->MoveNext();
				}
			}

			$result = buildClassXML_class_id($data);
		}
		else
		{
			// ���o���թҦ��H�����
			list($total_row) = dbGetStSr('WM_user_account', 'count(*)-1', '1', ADODB_FETCH_NUM);
			$RS = dbGetStMr('WM_user_account', 'username,first_name,last_name,gender', '1 order by ' . $sby1 . $limit_str, ADODB_FETCH_ASSOC);
			if ($RS)
			{
				while (!$RS->EOF)
				{
					if ($RS->fields['username'] == sysRootAccount) 
					{
						$RS->MoveNext();
						continue;
					}
					$data[] = $RS->fields;
					$RS->MoveNext();
				}
			}
			$result = buildClassXML($data);
		}
		$result = '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n" .
					'<manifest>' .
					'<total_row>' . $total_row . '</total_row>' .
					$result .
					'</manifest>';

        header("Content-type: text/xml");
		if (!empty($result)) {
			$result = str_replace('<manifest>', "<manifest><ticket>{$ticket}</ticket>", $result);
			echo $result;
		} else {
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n";
			echo "<manifest><ticket>{$ticket}</ticket></manifest>";
		}

		// if ($group_id > 1000000) end
	}
?>
