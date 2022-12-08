<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/23                                                                      *
	*		work for  : 附屬人員                                                                        *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: attach_content.php,v 1.1 2010/02/24 02:38:15 saly Exp $                                                                                          *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/content_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func='2400300100';
	$sysSession->restore();
	if (!aclVerifyPermission(2400300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}

	header("Content-type: text/xml");
	echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			$content_group_id  = getNodeValue($dom, 'contents');
			$content           = getNodeValue($dom, 'content');

            $content_group_id1 = preg_split('/\D+/', $content_group_id, -1, PREG_SPLIT_NO_EMPTY);
            $content1          = preg_split('/\D+/', $content, -1, PREG_SPLIT_NO_EMPTY);
            
            $c_num = count($content_group_id1);
            $s_num = count($content1);

			for($i=0; $i<$c_num; $i++)
			{
				for($j=0; $j< $s_num; $j++)
				{
				    dbNew('WM_content_group', 'parent, child', "{$content_group_id1[$i]},{$content1[$j]}");
				}
			}
	            $show_msg = $MSG['msg_attach_success'][$sysSession->lang];
            // for i loop (end)
			echo '<manifest><result>', $show_msg, '</result></manifest>';

        }
   }else {
		die("<manifest />\n");
   }

?>
