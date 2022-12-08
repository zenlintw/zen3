<?php
	/**
	 * 檢查帳號有無正在使用
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
	 * @copyright   2000-2005 SunNet Tech. INC.
	 * @version     CVS: $Id: check_user.php,v 1.1 2010/02/24 02:40:20 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2005-09-22
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '400200100';
	$sysSession->restore();

	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}
	// 變數宣告 begin
	// 變數宣告 end

	// 主程式 begin
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	header('Content-type: text/xml');
	echo '<' , '?xml version="1.0" encoding="UTF-8" ?' , '>' , "\n";
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$xmlDoc = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			echo '<manifest><result>1</result></manifest>';
			exit;
		}

		$user = getNodeValue($xmlDoc,'exist_user');

		$result_id = checkUsername($user);

		echo '<manifest><result>' , $result_id , '</result></manifest>';
	} else {
		echo '<manifest><result>F</result></manifest>';
	}

	// 主程式 end
?>