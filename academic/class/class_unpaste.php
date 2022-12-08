<?php
	/**
	 * �B�z ������ [�ŤU�Z��] �� �� [�K�W�Z��]
	 *
	 * �إߤ���G2002/12/11
	 * @author  ShenTing Lin
	 * @version $Id: class_unpaste.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func='2400100100';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}

	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest><result>1</result></manifest>';
			exit;
		}

		// �Z�ťN�X
        $class_id = getNodeValue($dom, 'class_unpaste');
        $class_ary = preg_split('/\D+/', $class_id, -1, PREG_SPLIT_NO_EMPTY);

		// �s�W���\���ƥ�
		$succ_class_num = 0;
		for ($i =0;$i < count($class_ary);$i++){
			list($class_num) = dbGetStSr('WM_class_main','count(*) as num','class_id=' . $class_ary[$i], ADODB_FETCH_NUM);
			if ($class_num > 0){
				list($max_permute) = dbGetStSr('WM_class_group','max(permute) as permute','parent = 1000000', ADODB_FETCH_NUM);
				/*
				 �s������ = �̤j���� + 1 (new)
				 */
				$new_permute = intval($max_permute) + 1;
				dbNew('WM_class_group','parent,child,permute','1000000,' . $class_ary[$i] . ',' . $new_permute);
				if ($sysConn->Affected_Rows() > 0){
					dbNew('WM_class_group','parent,child,permute',$class_ary[$i] . ',0,0');
					if ($sysConn->Affected_Rows() > 0){
						$succ_class_num++;
					}
				}
			}
		}
		if ($succ_class_num > 0)
			$result = "<manifest><ticket>{$ticket}</ticket><result>0</result></manifest>";
		else
			$result = "<manifest><ticket>{$ticket}</ticket><result>1</result></manifest>";

		echo $result;
	}
?>
