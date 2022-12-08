<?
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func = '900300300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	/**
	 * q_mkdir()
	 *     建立資料夾
	 * @pram string $dir : 要建立的資料夾名稱
	 * @return string (xml)
	 **/
	function q_mkdir($dir) {
		global $sysSession, $sysConn, $sysSiteNo, $ticket, $MSG, $_SERVER;

		$RS = dbGetStSr('WM_bbs_collecting','count(*) as total' ,"board_id='{$sysSession->board_id}' and path='{$sysSession->q_path}' and subject='{$dir}'", ADODB_FETCH_ASSOC);
		$err_code = 0;	// 0 為成功 , 其餘失敗
		$message = '';
		$extra = '';
		if($RS) {
			if($RS['total']>0) {
				$message = $MSG['folder'][$sysSession->lang] .' "' .$dir .'" ' .$MSG['already_existed'][$sysSession->lang];
				$err_code = 1;
			} else {
				$dir = mysql_escape_string($dir);
				$node = md5(uniqid(""));

				dbNew('WM_bbs_collecting',
				      'board_id,node,site,subject,picker,ctime,path,type',
				      "$sysSession->board_id,'{$node}',$sysSiteNo,'$dir','$sysSession->username',now(),'$sysSession->q_path','D'"
				     );				
				if($sysConn->Affected_Rows()==0) {
					$err_code = 3;
					$message  = $MSG['db_busy'][$sysSession->lang];
					$extra    = "<sql>$sysSession->board_id,'{$node}',$sysSiteNo,'$dir','$sysSession->username',now(),'$sysSession->q_path','D'</sql>";
				}
		    }
		} else {
			$message = $MSG['query_fail'][$sysSession->lang].$MSG['try_later'][$sysSession->lang];
			$err_code = 2;
		}
		
		wmSysLog('0900300100', $sysSession->board_id , 0 , $err_code, 'auto', $_SERVER['PHP_SELF'], 'Essential mkdir:' . $dir . $message);
		
		return '<?xml version="1.0" encoding="UTF-8"?><manifest>' .
			   '<ticket>'  . $ticket   . '</ticket>'  .
			   '<err>'     . $err_code . '</err>'     .
			   '<message>' . $message  . '</message>' .
			   $extra .
			   '</manifest>';
	}


	/**
	 * q_rmdir()
	 *     刪除資料夾
	 * @pram string $dir : 要刪除的資料夾名稱
	 * @return string (xml)
	 **/
	function q_rmdir($dir) {
		global $sysSession, $sysConn, $ticket, $_SERVER;

		$path = ($sysSession->q_path=='/'?'':$sysSession->q_path) . "/{$dir}";
		DelFolder($sysSession->board_id, $path);
		wmSysLog('900300200', $sysSession->board_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], 'Essential rmdir:' . $dir);
		return '<?xml version="1.0" encoding="UTF-8"?><manifest>' .
			   '<ticket>' . $ticket . '</ticket>' .
			   '<err>0</err>' .
			   '<message/>' .
			   '</manifest>';
	}

	/**
	 * q_IsEmptyDir()
	 *     檢查資料夾是否為空
	 * @pram string $dir : 要刪除的資料夾名稱
	 * @return string (xml)
	 **/
	function q_IsEmptyDir($dir) {
		global $sysSession, $sysConn, $ticket, $MSG;

		$path = ($sysSession->q_path=='/'?'':$sysSession->q_path) . '/' . $dir;
		$err_code = 0;
		$message  = '';
		if(!IsFolderEmpty($sysSession->board_id, $path))
		{
			//$message = '"' . $dir . '"' .$MSG['folder'][$sysSession->lang].' '.$MSG['not_empty'][$sysSession->lang]. "!\n\n";
			$message = sprintf($MSG['folder_del_confirm1'][$sysSession->lang], $dir);
		}
		//$message .= $MSG['ok'][$sysSession->lang].$MSG['del'][$sysSession->lang].$MSG['folder'][$sysSession->lang].'"'. $dir . '" ?';
		$message .= sprintf($MSG['folder_del_confirm2'][$sysSession->lang], $dir);

		return '<?xml version="1.0" encoding="UTF-8"?><manifest>' .
			   '<ticket>'  . $ticket   . '</ticket>'  .
			   '<err>'     . $err_code . '</err>'     .
			   '<message>' . $message  . '</message>' .
			   '</manifest>';
	}


	/**
	 * q_rendir()
	 *     更名資料夾
	 * @pram string $folder_id : 資料夾編號
	 * @pram string $dir : 資料夾新名稱
	 * @return string (xml)
	 **/
	function q_rendir($folder_id, $dir) {
		global $sysSession, $sysConn, $ticket, $MSG;
		list($subject) = dbGetStSr('WM_bbs_collecting','subject' ,"board_id='{$sysSession->board_id}' and node='{$folder_id}' and type='D'", ADODB_FETCH_NUM);
		$err_code = 0;	// 0 為成功 , 其餘失敗
		$message  = '';
		if(empty($subject)) {
			$err_code = 1;
			$message  = 'Can not find folder!';
		} else {

			$RS = dbGetStSr('WM_bbs_collecting','count(*) as total' ,"board_id='{$sysSession->board_id}' and path='{$sysSession->q_path}' and subject='{$dir}'", ADODB_FETCH_ASSOC);
			if($RS) {
				if($RS['total']>0) {
					$message = $MSG['folder'][$sysSession->lang] .' "' .$dir .'" ' .$MSG['already_existed'][$sysSession->lang];
					$err_code = 1;
				} else {

					$path = ($sysSession->q_path=='/'?'':$sysSession->q_path) . "/{$subject}";
					$len_path = strlen($path);
					//$dir_lang = iconv('UTF-8',$sysSession->lang, $dir);
					//$dir  = iconv($sysSession->lang , 'UTF-8', $dir );
					$new_path = ($sysSession->q_path=='/'?'':$sysSession->q_path) . "/{$dir}";
					$message .= "\\n{$new_path}\\n";
					//$message .= "\\nmd5:" . md5($dir)."\\n";
					if(dbSet('WM_bbs_collecting',"subject='{$dir}'","board_id='{$sysSession->board_id}' and node='{$folder_id}' and type='D'"))
					{
						$RS1 = dbGetStMr('WM_bbs_collecting',"node,path,subject","board_id='{$sysSession->board_id}' and (path='{$path}' or path like '{$path}/%') ", ADODB_FETCH_ASSOC);
						while(!$RS1->EOF)
						{
							$node = $RS1->fields['node'];
							$folder_path = $RS1->fields['path'];
							//$subject = $RS->fields['subject'];
							$folder_new_path = $new_path . substr($folder_path , $len_path);

							if(!dbSet('WM_bbs_collecting',"path='{$folder_new_path}'","board_id='{$sysSession->board_id}' and node='{$node}' "))
							{
								$err_code = 4;
								$message = 'Update children folder failed!';
								$message .= "\\npath='{$folder_new_path}'\\n";
								break;
							}
							$RS1->MoveNext();
						}
					} else {
						$err_code = 3;
						$message = 'Update the folder failed!';
					}
				}
			} else {
				$message = $MSG['query_fail'][$sysSession->lang].$MSG['try_later'][$sysSession->lang];
				$err_code = 2;
			}
		}
		//DelFolder($sysSession->board_id, $path);
		wmSysLog('0900300300', $sysSession->board_id , 0 , $err_code, 'auto', $_SERVER['PHP_SELF'], 'Essential rename dir:' . $folder_id . ' to ' . $dir . $message);
		return '<?xml version="1.0" encoding="UTF-8"?><manifest>' .
			   '<ticket>'  . $ticket   . '</ticket>'  .
			   '<err>'     . $err_code . '</err>'     .
			   '<message>' . $message  . '</message>' .
			   '</manifest>';
	}

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {

		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
			echo '<manifest></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
			exit;
		}

		// 檢查 Ticket
		$ticket = md5($sysSession->username . 'quint' . $sysSession->ticket . $sysSession->school_id);
		if (getNodeValue($dom, 'ticket') != $ticket) {
			header("Content-type: text/xml");
			echo '<?xml version="1.0" encoding="UTF-8" ?>', "\n",
			     '<manifest>',
			     '<clientticket>', getNodeValue($dom, 'ticket'), '</clientticket>',
			     '<serverticket>', $ticket, '</serverticket>',
			     '<message>Access Fail.</message></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 4, 'auto', $_SERVER['PHP_SELF'], 'Access Fail!');
			exit;
		}

		// 重新建立 Ticket
		setTicket();
		$ticket = md5($sysSession->username . 'quint' . $sysSession->ticket . $sysSession->school_id);

		$action = getNodeValue($dom, 'action');
		if($action=='rename') {	// rename 跟其他的(rmdir, mkdir) 不一樣
			$folder_id = getNodeValue($dom, 'node');
		}
		$dir    = getNodeValue($dom, 'dir');

		if(!$sysSession->q_right) {
			//header("Location:q_index.php");
			header("Content-type: text/xml");
			echo '<?xml version="1.0" encoding="UTF-8"?><manifest>',
				 '<ticket>', $ticket, '</ticket>',
				 '<err>255</err>',
				 '<message>Permission deny</message>',
				 '</manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 5, 'auto', $_SERVER['PHP_SELF'], '拒絕存取!');
			exit();
		}

		$result = '';
		switch ($action) {
			case 'mkdir'      : $result = q_mkdir($dir)             ; break;
			case 'rmdir'      : $result = q_rmdir($dir)             ; break;
			case 'isemptydir' : $result = q_IsEmptyDir($dir)        ; break;
			case 'rename'     : $result = q_rendir($folder_id, $dir); break;
		}
		if (!empty($result)) {
			header("Content-type: text/xml");
			echo $result;
		}
	}
?>
