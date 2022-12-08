<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/23                                                                      *
	*		work for  : 移除 教材                                                                        *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: content_remove.php,v 1.1 2010/02/24 02:38:19 saly Exp $                                                                                          *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/content_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func='2400300200';
	$sysSession->restore();

	if (!aclVerifyPermission(2400300200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}

/**
   查詢的 XML

< ?xml version="1.0" encoding="UTF-8" ? >
<manifest>
	<contents></contents>     <- 班級代碼
	<content></content>   <- 學生們的帳號
</manifest>
**/


	header("Content-type: text/xml");
	echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			$content_group_id = intval(getNodeValue($dom, 'contents'));
			$content          = getNodeValue($dom, 'content');

            $content1 = preg_split('/\D+/', $content, -1, PREG_SPLIT_NO_EMPTY);
            $s_num    = count($content1);

			for($i=0; $i< $s_num; $i++)
			{
			    dbDel('WM_content_group', "parent={$content_group_id} and child = {$content1[$i]}");
			}
			
			die('<manifest><result>' . $MSG['title123'][$sysSession->lang] . '</result></manifest>');
        }
   }

	die('</manifest>');
?>
