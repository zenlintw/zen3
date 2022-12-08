<?php
	/**
	 * ※ 匯出 - 課程統計
	 *
	 * @since   2016/04/15
	 * @author  Jeff Wang
	 * 
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (empty($_POST['dataCategories']) || empty($_POST['dataSeries'])) {
	    die('empty data.');
	}
	
	$fname = 'statistics.csv';
	$content = '';
	header('Content-Disposition: attachment; filename="' . $fname . '"');
	header('Content-Transfer-Encoding: binary');
	header('Content-Type: application/xml; name="' . $fname . '"');
	
    $categories = explode("','",stripslashes($_POST['dataCategories']));
    $values = explode(',',$_POST['dataSeries']);
    for ($i = 0, $size=count($categories); $i < $size; $i++) {
        $content .= sprintf("%s,%d\n",trim($categories[$i]),$values[$i]);
    }
    echo utf8_to_excel_unicode($content);
?>