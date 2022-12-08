<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/23                                                                      *
	*		work for  : �Z�Ŭd��                                                                        *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: class_query.php,v 1.1 2010/02/24 02:38:14 saly Exp $                                                                                          *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func='2400100400';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}

/**
   �d�ߪ� XML

< ?xml version="1.0" encoding="UTF-8" ? >
<manifest>
	<class_id></class_id>     <- �Z�ťN�X
</manifest>
**/


	header("Content-type: text/xml");

	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			$class_id	= intval(getNodeValue($dom, 'class_id'));										// �Z��
			$fields 	= dbGetStSr('WM_class_main', '*', 'class_id='. $class_id, ADODB_FETCH_ASSOC);
			$lang 		= getCaption($fields['caption']);												// �Z�ŦW��
			$dep_id		= $fields['dep_id'] 	== '' ? 'N' : htmlspecialchars($fields['dep_id']);		// �����N�X
			$director	= $fields['director'] 	== '' ? 'N' : htmlspecialchars($fields['director']); 	// �ɮv
			$sing_start	= $fields['sing_start'] == '' ? 'N' : $fields['sing_start'];					// �}�l���W
			$sing_end	= $fields['sing_end'] 	== '' ? 'N' : $fields['sing_end'];						// �I����W
			$class_start= $fields['class_start']== '' ? 'N' : $fields['class_start'];					// �}�l�W��
			$class_end	= $fields['class_end'] 	== '' ? 'N' : $fields['class_end'];						// �פ�W��

			echo <<< BOF
<?xml version="1.0" encoding="UTF-8" ?>
<manifest>
	<class>
		<class_id>{$class_id}</class_id>
		<big5_name>{$lang['Big5']}</big5_name>
		<gb2312_name>{$lang['GB2312']}</gb2312_name>
		<en_name>{$lang['en']}</en_name>
		<jp_name>{$lang['EUC-JP']}</jp_name>
		<user_define>{$lang['user_define']}</user_define>
		<director>{$director}</director>
		<dep_id>{$dep_id}</dep_id>
		<sing_start>{$sing_start}</sing_start>
		<sing_end>{$sing_end}</sing_end>
		<class_start>{$class_start}</class_start>
		<class_end>{$class_end}</class_end>
		<status>{$fields['status']}</status>
		<people_limit>{$fields['people_limit']}</people_limit>
		<quota_limit>{$fields['quota_limit']}</quota_limit>
	</class>
</manifest>
BOF;
	exit();
		}
	} 

	echo '<?xml version="1.0" encoding="UTF-8" ?><manifest />';

?>
