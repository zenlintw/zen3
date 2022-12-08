<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	//ACL begin
	
	$sysSession->cur_func = QTI_which == 'homework' ? '1700400300' : '1800300100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}
	
	//ACL end


	define('QTI_DISPLAY_ANSWER',   false);
	define('QTI_DISPLAY_OUTCOME',  false);
	define('QTI_DISPLAY_RESPONSE', true);
	if ($_SERVER['argv'][0] == 'school') $topDir = 'academic';
	
	// Microsoft Edge 會多送 \xfeff的字元
	$GLOBALS['HTTP_RAW_POST_DATA'] = preg_replace('/\x{FEFF}/u', '', $GLOBALS['HTTP_RAW_POST_DATA']);
	
	if (strpos($GLOBALS['HTTP_RAW_POST_DATA'], '<itemfeedback ') === FALSE)
	{
		ob_start();
		include_once(sysDocumentRoot . '/teach/exam/exam_preview.php');
		$html = ob_get_contents();
		ob_end_clean();
		// 作業預覽時不秀填寫的表單欄位
		echo isSet($_GET['preview']) && $_GET['preview'] == 'true' 
		     ? preg_replace(array('/<input\s*[^>]*>/isU',
		                          '/<textarea\s*[^>]*>.*<\/textarea[^>]*>/isU',
		                          '/<select\s*[^>]*>.*<\/select[^>]*>/isU'), 
		                    '', $html)
		     : $html;
	}
	else
	{
		include_once(sysDocumentRoot . '/lang/' . QTI_which . '_learn.php');
		include_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
		if (preg_match_all('/<item .*\bident="([^"]+)"/sU', $GLOBALS['HTTP_RAW_POST_DATA'], $regs))
		{
			$rs = dbGetStMr('WM_qti_' . QTI_which . '_item', 'ident, attach, type', 'ident in ("' . implode('","', $regs[1]) . '")', ADODB_FETCH_ASSOC);
			if ($rs)
				while ($row = $rs->FetchRow()) {
					if (preg_match('/^a:[0-9]+:{/', $row['attach']))
						$GLOBALS['attachments'][$row['ident']] = unserialize($row['attach']);
					$GLOBALS['item_types'][$row['ident']] = $row['type'];
				}
		}
		parseQuestestinterop($GLOBALS['HTTP_RAW_POST_DATA']);
	}

?>

