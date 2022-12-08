<?php
	/**
	 * 搬移訊息
	 *
	 * 建立日期：2003/05/15
	 * @author  ShenTing Lin
	 * @version $Id: move.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/message/lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	// $sysSession->cur_func = '2200200300';
	// $sysSession->restore();
	if (!aclVerifyPermission(2200200300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if ($sysSession->cur_func == $msgFuncID['notebook']) {
		$target = 'notebook.php';
	} else {
		$target = 'index.php';
	}

	$error  = 0;
	$result = array();

	if (!isset($_POST['fid'])) {
		$error = 1;
	} else {
		$fid = $_POST['fid'];
		if (!is_array($fid) || count($fid) <= 0) {
			$error = 2;
		} else {
			// 將訊息移至指定的資料夾中
			$folder_id = trim($_POST['folder_id']);
            /*
              1. 原本WMPRO內建的資料夾
              2. 雲端筆記建立的資料夾
             */
			if ((ereg('^(sys|USER)_[a-zA-Z0-9_]+$', $folder_id)) || ($folder_id == 'app_push_message') ||
                (ereg('^APP_[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}', $folder_id))) {
                $moveTime = date('Y-m-d H:i:s', time());
				for ($i = 0; $i < count($fid); $i++) {
                    $action = 'M';
					$val = intval($fid[$i]);
                    // 取得筆記原先的folder_id
                    $fromFolder = dbGetOne('WM_msg_message', 'folder_id', "`msg_serial`='{$val}' AND `receiver`='{$sysSession->username}'");

					dbSet('WM_msg_message', "`folder_id`='{$folder_id}', `submit_time` = '{$moveTime}', `receive_time` = '{$moveTime}'", "`msg_serial`='{$val}' AND `receiver`='{$sysSession->username}'");

                    if ($sysSession->cur_func == $msgFuncID['notebook']) {
                        // 如果是筆記本的功能，要處理雲端筆記的log - begin
                        $logTime = strtotime($moveTime);
                        if ($fromFolder == 'sys_notebook_trash') {
                            // 如果是從回收筒搬回來，則改為新增(A)
                            $action = 'A';
                        }
                        dbNew('APP_note_action_history',
                            '`username`, `log_time`, `action`, `folder_id`, `msg_serial`, `from`',
                            "'{$sysSession->username}', {$logTime}, '{$action}', '{$folder_id}', {$val}, 'server'");
                        // 如果是筆記本的功能，要處理雲端筆記的log - end
                    }
				}
				header('Location: ' . $target);
			}
		}
	}
