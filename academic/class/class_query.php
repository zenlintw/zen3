<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/23                                                                      *
	*		work for  : 班級查詢                                                                        *
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
   查詢的 XML

< ?xml version="1.0" encoding="UTF-8" ? >
<manifest>
	<class_id></class_id>     <- 班級代碼
</manifest>
**/


	header("Content-type: text/xml");

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			$class_id	= intval(getNodeValue($dom, 'class_id'));										// 班級
			$fields 	= dbGetStSr('WM_class_main', '*', 'class_id='. $class_id, ADODB_FETCH_ASSOC);
			$lang 		= getCaption($fields['caption']);												// 班級名稱
			$dep_id		= $fields['dep_id'] 	== '' ? 'N' : htmlspecialchars($fields['dep_id']);		// 部門代碼
			$director	= $fields['director'] 	== '' ? 'N' : htmlspecialchars($fields['director']); 	// 導師
			$sing_start	= $fields['sing_start'] == '' ? 'N' : $fields['sing_start'];					// 開始報名
			$sing_end	= $fields['sing_end'] 	== '' ? 'N' : $fields['sing_end'];						// 截止報名
			$class_start= $fields['class_start']== '' ? 'N' : $fields['class_start'];					// 開始上課
			$class_end	= $fields['class_end'] 	== '' ? 'N' : $fields['class_end'];						// 終止上課

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
