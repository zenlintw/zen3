<?php
/**************************************************************************************************
 *                                                                                                *
 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
 *                                                                                                *
 *		Programmer: Wiseguy Liang                                                         *
 *		Creation  : 2002/09/26                                                            *
 *		work for  : Create Item                                                           *
 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
 *                                                                                                *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/quota.php');
require_once(sysDocumentRoot . '/teach/exam/item_create_lib.php');
require_once(sysDocumentRoot . '/lib/file_api.php');

if (detectUploadSizeExceed()) {
    showXHTML_script('inline', 'alert("'.$MSG['upload_file_error'][$sysSession->lang].'");location.replace("item_maintain.php");');
    die();
}

$item = new itemMaintain();
$res  = $item->saveItem($_POST);

if ($res['ErrCode'] < 0) {
    die('Illegal Access !');
}

if ($item->isModify) {
    // 更新試題
    if ($res['ErrCode'] == 0) {
        print <<< EOB
	<script type="text/javascript">
		alert('{$MSG['save_complete'][$sysSession->lang]}');
		location.replace('item_maintain.php?{$item->itemData['origin']}');
	</script>
EOB;
        wmSysLog($sysSession->cur_func, $item->courseId, 0, 0, 'auto', $_SERVER['PHP_SELF'], 'Modify ' . QTI_which . ' Item ' . $item->ident . ' success:');
    } else {
        $errMsg = implode(' : ', $res);
        wmSysLog($sysSession->cur_func, $item->courseId, 0, 2, 'auto', $_SERVER['PHP_SELF'], 'Modify ' . QTI_which . ' Item ' . $item->ident . ' fail:' . $errMsg);
        die($errMsg);
    }
} else {
    // 新增試題
    if ($res['ErrCode'] == 0) {
        wmSysLog($sysSession->cur_func, $item->courseId, 0, 0, 'auto', $_SERVER['PHP_SELF'], 'Create ' . QTI_which . ' Item ' . $item->ident);
        print <<< EOB
	<html>
	<head>
	<script type="text/javascript">
		alert('{$MSG['save_complete'][$sysSession->lang]}');
	</script>
	</head>
	<body onload="document.getElementById('retForm').submit();">
EOB;
        if ($item->itemData['repeat']) {
            print <<< EOB
		<form id="retForm" action="item_create.php?{$item->itemData['type']}" method="POST">
			<input type="hidden" name="gets" value="{$item->itemData['gets']}">
			<input type="hidden" name="ticket" value="{$item->itemData['ticket']}">
		</form>
EOB;
        } else {
            print <<< EOB
		<form id="retForm" action="item_maintain.php?{$item->itemData['gets']}" method="POST"></form>
EOB;
        }
    } else {
        $errMsg = implode(' : ', $res);
        wmSysLog($sysSession->cur_func, $item->courseId, 0, 2, 'auto', $_SERVER['PHP_SELF'], 'Create ' . QTI_which . ' Item ' . $item->ident . ' fail:' . $errMsg);
        echo $errMsg;
    }
    print <<< EOB
	</body>
	</html>
EOB;
}
