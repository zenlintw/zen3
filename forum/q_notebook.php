<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/forum/file_api.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '900201000';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 重組文章內容
	function nb_recompose(&$RS)
	{
		global $sysSession, $MSG;
		ob_start();

		showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" class="box01"');

		showXHTML_tr_B('class="bg04 font01"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['bname'][$sysSession->lang]);
			showXHTML_td('width="640"', $sysSession->board_name . ' - ' . $MSG['quint'][$sysSession->lang]);
		showXHTML_tr_E('');
		showXHTML_tr_B('class="bg03 font01"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', 'PATH :');
			showXHTML_td('width="640"', $RS['path']);
		showXHTML_tr_E('');

		showXHTML_tr_B('class="bg04 font01"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['posters'][$sysSession->lang]);
			showXHTML_td('width="640"', "<a href=\"mailto:{$RS['email']}\" class=\"link_fnt01\">{$RS['poster']}</a> ".($RS['homepage']?("<a href=\"{$RS['homepage']}\" target=\"_blank\">{$RS['realname']} </a>"):"({$RS['realname']} )"));
		showXHTML_tr_E();
		showXHTML_tr_B('class="bg03 font01"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['times'][$sysSession->lang]);
			showXHTML_td('width="640"', $RS['pt']);
		showXHTML_tr_E();
		showXHTML_tr_B('class="bg04 font01"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['subjs'][$sysSession->lang]);
			showXHTML_td('width="640"',$RS['subject']);
		showXHTML_tr_E();
		showXHTML_tr_B('class="bg03 font01"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['contents'][$sysSession->lang]);
			showXHTML_td('width="640"','<table><tr><td class="font01"><br />'.$RS['content'].'<p /></td></tr></table>');
		showXHTML_tr_E();
		showXHTML_table_E();

		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	// 自討論版複製實體檔案到筆記本資料夾中
	// 參數: $to_path   : 目的路徑   ( 尾端不含 '/' )
	//	 $attach    : 新夾檔字串 ( '名稱'[TAB]'實體檔名'[TAB]'名稱'[TAB]'實體檔名'[TAB]... )
	// 傳回值:
	//	   無
	function nb_rollback_files( $to_path, $attach ) {
		$files  = explode("\t", $attach);	// 原夾檔字串 Chr(9)
		if(count($files)==0) return;		// 無夾檔

		for($i=0;$i<count($files);$i+=2) {
			$path = $to_path . '/' . $files[$i+1];
			//echo "<!-- unlink($path) -->\r\n";
			//delete($path);
			unlink($path);
		}
	}

	function js_exit($bt_msg) {
		global $failed_msgs, $MSG,$sysSession;
		$js_txt = '';
		if(count($failed_msgs) > 0) {	// 有錯誤發生
			foreach($failed_msgs as $k=>$v)
				$js_txt .= $v . "\\n";
		} else {
			$js_txt = $MSG['notebook_success'][$sysSession->lang];
		}
        // #47301 Chrome [全體/討論板/精華區/文章內容/收入筆記本] 按下「收入筆記本」後，出現亂碼。：增加語言編碼
        showXHTML_head_B($MSG['notebook_success'][$sysSession->lang]);
        showXHTML_head_E();

		// #48440 chrome [全區]所有討論版的精華區，點選文章內的「收入筆記本」功能，均會重現兩個 Alert 視窗。：停用alert，改頁面直接顯示
        // $js = "alert('" . $js_txt . "');\r\n";
		// $js .= "window.close();\r\n";
		// showXHTML_script('inline',$js);
        
        echo $js_txt;
        echo '<p>';
        echo "<input type='button' name='close' value='".$bt_msg."' onClick='window.close();'>";
        
		exit();
	}

	/****************************************************
	 *	主要程式
	 *
	 ****************************************************/

	$board_id	= $_GET['board'];
	$node		= $_GET['node'];
	$site		= $_GET['site'];
	$path		= $_GET['path'];
	$folder		= $_GET['folder'];

	$failed_msgs = Array();	// 失敗訊息

   	if($board_id != $sysSession->board_id || !ereg('[0-9]{6,}',$node) || !ereg('[0-9]{10}',$site)
   		|| empty($folder) )
   	{
   		$failed_msgs[] = iconv('Big5','UTF-8', "收入筆記本失敗!");
   		wmSysLog($sysSession->cur_func, $board_id , $node , 1, 'auto', $_SERVER['PHP_SELF'], iconv('Big5','UTF-8', "收入筆記本失敗!"));
		js_exit($MSG['ok'][$sysSession->lang]);
   	}

	$RS = dbGetStSr('WM_bbs_collecting','*',"board_id={$board_id} and node='{$node}' and site='{$site}' and path='{$path}'", ADODB_FETCH_ASSOC);

	$base_path   = get_attach_file_path('quint', $sysSession->board_ownerid);
	$target_path = MakeUserDir($sysSession->username);

	if($RS)
	{
		$poster= $RS['poster'];

		$subject = mysql_escape_string($RS['subject']);
		$content = mysql_escape_string(nb_recompose($RS));
		$username= mysql_escape_string($sysSession->username);

		$from_path = $base_path . "/" . $node;

		$attach = '';

		// 複製檔案
		if(!b_copyfiles( $from_path , $target_path , trim($RS['attach']), $attach))
		{
			$failed_msgs[] = $MSG['copyfile_fail'][$sysSession->lang];
		} else {

			$fields = '`folder_id`, `sender`, `receiver`, `submit_time`, `receive_time`, `priority`, ' .
					  '`subject`, `content`, `attachment`, `note`, `content_type`';

			$values = "'{$folder}','{$username}', '{$username}', ".
					  "Now(), Now(), 0, '{$subject}', '{$content}', " .
					  "'{$attach}', '', 'html'";

			//echo "<!-- fields: {$fields} -->\r\n";
			//echo "<!-- values: {$values} -->\r\n";
			if(!dbNew('WM_msg_message', $fields, $values)) {
				$failed_msgs[] = $MSG['savedb_fail'][$sysSession->lang];
				wmSysLog($sysSession->cur_func, $board_id , $node , 2, 'auto', $_SERVER['PHP_SELF'], $MSG['savedb_fail'][$sysSession->lang]);
				nb_rollback_files( $target_path, $attach );
                            wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '新增到訊息中心失敗：' . $username . '|' . $username . '|' . $subject);
			}
			else
				wmSysLog($sysSession->cur_func, $board_id , $node , 0, 'auto', $_SERVER['PHP_SELF'], 'collect to notebook success!');
		}
	} else {
   		$failed_msgs[] = iconv('Big5','UTF-8', "無法取得本文章!");
   		wmSysLog($sysSession->cur_func, $board_id , $node , 3, 'auto', $_SERVER['PHP_SELF'], iconv('Big5','UTF-8', "無法取得本文章!"));
   		//$failed_msgs[] = iconv('Big5','UTF-8', "board_id={$board_id} and node={$node} and site={$site} and path={$path}");
	}

	js_exit($MSG['ok'][$sysSession->lang]);
?>
