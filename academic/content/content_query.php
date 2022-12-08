<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/23                                                                      *
	*		work for  : �Z�Ŭd��                                                                        *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: content_query.php,v 1.1 2010/02/24 02:38:19 saly Exp $                                                                                          *
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
	<content_id></content_id>     <- �Ч��N�X
</manifest>
**/

	header("Content-type: text/xml");
	echo '<?xml version="1.0" encoding="UTF-8" ?>', "\n";
	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			$content_id = intval(getNodeValue($dom, 'content_id'));	// �Z��
			
			list($caption) = dbGetStSr('WM_content', 'caption', 'content_id=' . $content_id, ADODB_FETCH_NUM);
			if ($caption) {
				$lang = getCaption($caption);
				echo <<< BOF
<manifest>
	<content>
		<content_id>{$content_id}</content_id>
		<big5_name>{$lang['Big5']}</big5_name>
		<gb2312_name>{$lang['GB2312']}</gb2312_name>
		<en_name>{$lang['en']}</en_name>
		<jp_name>{$lang['EUC-JP']}</jp_name>
		<user_define>{$lang['user_define']}</user_define>
	</content>
</manifest>
BOF;
				exit();
			}
        }
	}
	
	die('<manifest />');
?>
