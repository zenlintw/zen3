<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/lib/lib_newsfaq.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/lang/app_server_push.php');
	require_once(sysDocumentRoot . '/lib/quota.php');
	require_once(sysDocumentRoot . '/forum/lib_mailfollow.php');
	require_once(sysDocumentRoot . '/forum/order.inc.php');
	require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/xmlapi/config.php');
	require_once(sysDocumentRoot . '/xmlapi/lib/JsonUtility.php');
	require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/DatabaseHandler.php');


	if (!defined('BOARD_TYPE')) define('BOARD_TYPE', 'board');
	define('USE_TABLE' , BOARD_TYPE == 'board' ? 'WM_bbs_posts' : 'WM_bbs_collecting');

	if (BOARD_TYPE == 'board')
		$sysSession->cur_func = $_POST['etime'] ? '900200600' : '900200500';
	else
		$sysSession->cur_func = $_POST['etime'] ? '900300500' : '900300400';

	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}
	
	$alert_str = mb_convert_encoding('不具刊登權限', 'UTF-8','BIG5' );

	// APP 課程公告 PUSH 用 - Begin
	$newPostFlag = false;
	// APP 課程公告 PUSH 用 - End
	
	// 是否具刊登權限(含張貼, 修改, 刪除)
	if ($sysSession->board_readonly) {
	    if(!ChkRight($sysSession->board_id)){
							$js = <<< BOF
	window.onload = function ()
	{
		alert("{$alert_str}");
		location.replace("/forum/index.php");
	};
BOF;
			showXHTML_script('inline', $js);
				//header('Location:' . $referurl);
				wmSysLog($sysSession->cur_func, $sysSession->class_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], $alert_str);
				exit();
	    }
	}
	
	// 檢查上傳檔案是否超過限制
	if (detectUploadSizeExceed())
	{
		showXHTML_script('inline', 'alert("'.$MSG['upload_file_error'][$sysSession->lang].'");location.replace("'.(BOARD_TYPE=='board' ? 'index.php' : 'q_index.php').'");');
	}

	// 檢查 ticket
	$ticket = md5(sysTicketSeed . BOARD_TYPE . $_COOKIE['idx'] . $sysSession->board_id);
	if (trim($_POST['ticket']) != $ticket) {
	   wmSysLog($sysSession->cur_func, $sysSession->class_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
	   die($MSG['access_deny'][$sysSession->lang]);
	}

	// 各項排序依據
	$OB = $OrderBy[BOARD_TYPE];

	function ErrorExit($_msg='') {
		global $_POST, $sysSession, $_SERVER;
		echo "<body onload=\"document.getElementById('firstForm').submit();\">\n",
		     "<div style=\"display: none\"><form action=\"",$_POST['whoami'],"\" method=\"POST\" id=\"firstForm\">\n";
		foreach($_POST as $k => $v) echo "<textarea name=\"$k\">", stripslashes($v), "</textarea>\n";
		echo '<input type="hidden" name="writeError" value="', $_msg, "\"\n",
		     "</form></div></body>\n";
		wmSysLog($sysSession->cur_func, $sysSession->class_id , $sysSession->board_id , 2, 'auto', $_SERVER['PHP_SELF'], $_msg);
		exit;
	}

	// 標題不許使用 html
	$subject = htmlspecialchars($_POST['subject'], ENT_QUOTES);
	// 取出簽名檔
	$tag_serial = intval($_POST['tagline']);
	list($ctype, $tagline) = dbGetStSr('WM_user_tagline', 'ctype, tagline', "serial={$tag_serial} AND username='{$sysSession->username}'", ADODB_FETCH_NUM);
	if ($ctype == 'text') {
		$patterns = array("/(http:\/\/[^\s]+)/","/([\w\d]+(\.[_\w\d]+)*@[\w\d]+(\.[\w\d]+){1,4})/");
		$replace  = array("<a href=\"\\1\" target=\"_blank\">\\1</a>","<a href=\"mailto:\\1\">\\1</a>");
		$tagline  = nl2br(preg_replace($patterns, $replace, htmlspecialchars($tagline, ENT_QUOTES)));
	}

	// 取學校最新消息編號
	$schoolNewsBoard = dbGetOne('`WM_news_subject`', '`board_id`', '`type` = "news"');
    // 取公告版編號
    $annBid = dbGetOne('`WM_term_course`', 'bulletin', '`course_id` = ' . $sysSession->course_id);

	// 本文去除所有的不必要 html
	$content = strip_scr($_POST['content']);
	// 如果貼的是純文字，轉換 url 為 link
	if (!$_POST['isHTML']){
		$patterns = array("/(http:\/\/[^\s]+)/","/([\w\d]+(\.[_\w\d]+)*@[\w\d]+(\.[\w\d]+){1,4})/");
		$replace = array("<a href=\"\\1\" target=\"_blank\">\\1</a>","<a href=\"mailto:\\1\">\\1</a>");
		$content = nl2br(preg_replace($patterns, $replace, htmlspecialchars($content, ENT_QUOTES)));
	}
	// 加上張貼者 IP
	// $content .= "\n<br />\n<br />--\n<br />Posting from $sysSession->ip\n<br />\n<br />====================\n<br />{$tagline}";
	if ($tagline) $content .= "\n<br />\n<br />{$tagline}";
	$content = trimHtml($content);

    if (!empty($_POST['mnode'])) {
        $isNodeExists = intval(dbGetOne(
            USE_TABLE,'count(*)',
            sprintf("board_id=%d and site=%d and node='%s'",
                $sysSession->board_id, $sysSiteNo, $_POST['mnode']
            )
        ));

        if (!$isNodeExists) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
    }

	if ($_POST['isReply']){
		$node = trim($_POST['node']);
		// 取得 node 的最大子 node
		list($mnode) = dbGetStSr(USE_TABLE, 'MAX(node)', "board_id={$sysSession->board_id} and node like '" . substr($node, 0, 9) . "%'", ADODB_FETCH_NUM);
		// 產生本篇的 node
		// 雙層架構
		$nnode = (strlen($mnode) == 9) ? ($node . '000000001') : sprintf('%s%09d', substr($mnode, 0, 9), intval(substr($mnode, -9))+1);
	}
	elseif(empty($_POST['mnode'])){
		// 取得目前板中最大的 node
		list($mnode) = dbGetStSr(USE_TABLE, 'MAX(node)', "board_id={$sysSession->board_id} and length(node) = 9", ADODB_FETCH_NUM);
		// 產生本篇的 node
		$nnode = empty($mnode)?'000000001':sprintf("%09d", $mnode+1);
	}

	// 本篇是使用哪種語系張貼
	$ll = array(
		'Big5'   => 1,
		'en'	 => 2,
		'GB2312' => 3,
		'EUC-JP' => 4,
		'user_define' => 5
	);

	$base_path = get_attach_file_path(BOARD_TYPE, $sysSession->board_ownerid);

	// 換掉可能之引號
	$username = mysql_escape_string($sysSession->username);
	$realname = mysql_escape_string($sysSession->realname);

	$node_id = '';

	// 取得 Quota 資訊開始
	$freeQuota = getRemainQuota($sysSession->board_ownerid);
	$type      = getQuotaType($sysSession->board_ownerid);
	$msgQuota  = str_replace(array('%TYPE%', '%OWNER%'), 
	                         array($MSG[$type][$sysSession->lang], $MSG[$type . '_owner'][$sysSession->lang]), 
	                         $MSG['quota_full'][$sysSession->lang]);

	if (!empty($_FILES))
		$freeQuota = $freeQuota - (array_sum( $_FILES['uploads']['size'] )/1024);
	if (($_POST['mp3path'] = basename(trim($_POST['mp3path']))) != '') {
		$srcfile   = $_SERVER['DOCUMENT_ROOT']."/base/{$sysSession->school_id}/board/{$sysSession->board_id}/".$_POST['mp3path'];
		$freeQuota = $freeQuota - (filesize($srcfile) / 1024);
	}
	if (($_POST['wbpath'] = basename(trim($_POST['wbpath']))) != '') {
		$srcfile   = $_SERVER['DOCUMENT_ROOT']."/base/{$sysSession->school_id}/board/{$sysSession->board_id}/".$_POST['wbpath'];
		$freeQuota = $freeQuota - (filesize($srcfile) / 1024);
	}
	if ($freeQuota <= 0 && (!empty($_FILES)|| $_POST['mp3path'] != '' || $_POST['wbpath'])) ErrorExit($msgQuota);
	// 取得Quota資訊結束

	// Add by yakko. for anicam sound rec
	if ($_POST['mp3path'] != '')
	{
		$srcfile = $_SERVER['DOCUMENT_ROOT']."/base/{$sysSession->school_id}/board/{$sysSession->board_id}/".$_POST['mp3path'];
		if ($_POST['etime']){	// 如果是重編輯的話
			$destdir = $base_path.DIRECTORY_SEPARATOR.trim($_POST['mnode']);
		}else{
			$destdir = $base_path.DIRECTORY_SEPARATOR.$nnode;
		}
		if (!is_dir($destdir)) {
			if (strpos($_ENV['OS'], 'Windows') !== false) {
				exec('cmd.exe /Q /D /U /E:ON /C mkdir "' . $destdir . '"');
			}else{
				exec("mkdir -pm 755 '$destdir'");
			}
		}

		$destfile = $destdir.DIRECTORY_SEPARATOR.$_POST['mp3path'];
		if (copy($srcfile, $destfile))
		{
			unlink($srcfile);
		}
	}


	if ($_POST['wbpath'] != '')
	{
		$srcfile = $_SERVER['DOCUMENT_ROOT']."/base/{$sysSession->school_id}/board/wb_temp/".$_POST['wbpath'];
		if ($_POST['etime']){	// 如果是重編輯的話
			$destdir = $base_path.DIRECTORY_SEPARATOR.trim($_POST['mnode']);
		}else{
			$destdir = $base_path.DIRECTORY_SEPARATOR.$nnode;
		}
		if (!is_dir($destdir)) {
			if (strpos($_ENV['OS'], 'Windows') !== false) {
				exec('cmd.exe /Q /D /U /E:ON /C mkdir "' . $destdir . '"');
			}else{
				exec("mkdir -pm 755 '$destdir'");
			}
		}

		$destfile = $destdir.DIRECTORY_SEPARATOR.$_POST['wbpath'];
		if (copy($srcfile, $destfile))
		{
			unlink($srcfile);
		}
	}

	// END Add by yakko. for anicam sound rec


	// 如果是重編輯的話
	if ($_POST['etime']){
		// 去掉夾檔中，被標示刪除的檔案 & 儲存夾檔
		$attach = trim(remove_previous_uploaded($base_path . DIRECTORY_SEPARATOR . trim($_POST['mnode']), trim($_POST['o_att'])).
			    chr(9).
			    save_upload_file($base_path . DIRECTORY_SEPARATOR . trim($_POST['mnode']), $quota_limit, $quota_used)
			   );

		if ($_POST['mp3path'] != '')
		{
			if (strpos($_POST['o_att'],$_POST['mp3path']) === false)
			{
				if (!empty($attach)) $attach .= chr(9);
				$attach .= $_POST['mp3path'].chr(9).$_POST['mp3path'];
			}
		}


		if ($_POST['wbpath'] != '')
		{
			if (strpos($_POST['o_att'],$_POST['wbpath']) === false)
			{
				if (!empty($attach)) $attach .= chr(9);
				$attach .= $_POST['wbpath'].chr(9).$_POST['wbpath'];
			}
		}

		// 修改資料庫
		dbSet(USE_TABLE,
		      "pt='{$_POST['etime']}',
		      poster='$username',
		      realname='$realname ',
		      email='$sysSession->email',
		      homepage='$sysSession->homepage',
		      subject='$subject ',
		      content='$content ',
		      attach=" . ($attach?"'$attach'":"NULL"),
		      "board_id=$sysSession->board_id and site=$sysSiteNo".
		      " and node='".trim($_POST['mnode'])."'"
		     );
		$node_id = trim($_POST['mnode']);
		wmSysLog($sysSession->cur_func, $sysSession->board_id , $_POST['mnode'] , 0, 'auto', $_SERVER['PHP_SELF'], 'Edit '.BOARD_TYPE.' post');
	}
	// 張貼或回覆
	else{
	    if (dbGetOne(USE_TABLE, 'count(*)', "board_id=$sysSession->board_id and poster='$username' and pt > DATE_SUB(NOW(), INTERVAL 1 DAY) and content='$content '"))
	    {
	    	if (BOARD_TYPE == 'board') {
	    	    echo '<script type="text/javascript">alert("'.$MSG['repeat_post'][$sysSession->lang].'");location.replace("', $sysSession->post_no?"/forum/510,{$sysSession->board_id},{$sysSession->post_no}.php":"/forum/500,{$sysSession->board_id},{$sysSession->page_no},{$sysSession->sortby}.php", '");</script>';
	        } else {
	            echo '<script type="text/javascript">alert("'.$MSG['repeat_post'][$sysSession->lang].'");location.replace("', $sysSession->q_post_no?"/forum/570,{$sysSession->board_id},{$sysSession->q_post_no}.php":"/forum/560,{$sysSession->board_id},{$sysSession->q_page_no},{$sysSession->q_sortby}.php", '");</script>';
	        }
	        exit;

		}
	
		// 儲存夾檔。如果有的話。
		$attach = trim(save_upload_file($base_path . DIRECTORY_SEPARATOR . $nnode, $quota_limit, $quota_used));
		if ($_POST['mp3path'] != '')
		{
			if (!empty($attach)) $attach .= chr(9);
			$attach .= $_POST['mp3path'].chr(9).$_POST['mp3path'];
		}
		if ($_POST['wbpath'] != '')
		{
			if (!empty($attach)) $attach .= chr(9);
			$attach .= $_POST['wbpath'].chr(9).$_POST['wbpath'];
		}

		// 加入資料庫
		if (BOARD_TYPE == 'board')
		{
			$fields = 'board_id,node,site,pt,poster,realname,email,homepage,subject,content,attach,lang';
			$values = "$sysSession->board_id, '$nnode',$sysSiteNo".
			      	  ", NOW(), '$username', '$realname ', ".
			      	  "'$sysSession->email', '$sysSession->homepage ', '$subject ', '$content ',".
			      	  ($attach?"'$attach'":"NULL") . "," . $ll[$sysSession->lang];
            // APP 課程公告 PUSH 用 - Begin
            $newPostFlag = true;
            // APP 課程公告 PUSH 用 - End
		}
		else
		{
			$fields = "board_id,node,site,pt,poster,realname,email,homepage,subject,content,attach,lang,".
				  	  "ctime,picker,path,type,post_node";
			$values = "$sysSession->board_id, '$nnode',$sysSiteNo".
			          ", NOW(), '$username', '$realname ', ".
			      	  "'$sysSession->email', '$sysSession->homepage ', '$subject ', '$content ',".
			      	  ($attach?"'$attach'":"NULL") . "," . $ll[$sysSession->lang].",Now(),'".
			      	  "$username','{$sysSession->q_path}','F',''";
		}
		dbNew(USE_TABLE, $fields, $values );
		if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0){
			ErrorExit('Error:'.$sysConn->ErrorNo().' = '. $sysConn->ErrorMsg().'"');
		}
		$node_id = $nnode;
		wmSysLog($sysSession->cur_func, $sysSession->board_id , $node_id , 0, 'auto', $_SERVER['PHP_SELF'], 'new '.BOARD_TYPE.' post');

		// 遞增自己跟課程的張貼數
		dbSet('WM_term_major',  'post_times=post_times+1', "username='{$username}' and course_id='{$sysSession->course_id}'");
		dbSet('WM_term_course', 'post_times=post_times+1', "course_id='{$sysSession->course_id}'");
		
		$school_name	= $sysSession->school_name;					// 學校名稱
        $school_host    = $_SERVER['HTTP_HOST'];

		// Mail follow
		$MailData = Array();
		$MailData['attach']	= $attach;
		//$MailData['from']	= mailEncFrom($sysSession->course_name,' ');
		$MailData['subject']	= stripslashes($_POST['subject']); //$subject;
		$MailData['title']	= '==================' . $school_name. "\t" .$school_host . '==================' . "<br>\r\n";
		$MailData['course']	= $MSG['mail_cname'][$sysSession->lang]. $sysSession->course_name ."<br>\r\n" ;
		$MailData['body']	= $MSG['mail_board'][$sysSession->lang] . $sysSession->board_name . "<br>\r\n" .
							  $MSG['mail_poster'][$sysSession->lang] . $username. '(' .$realname .')' . "<br>\r\n" .
							  $MSG['mail_ptime'][$sysSession->lang] . Date("Y-m-d H:i:s") . "<br>\r\n" .
							  $MSG['mail_subject'][$sysSession->lang] . $subject . "<br><br>\r\n" .
							  $content;
		$MailData['attach_dir']	= get_attach_file_path(BOARD_TYPE, $sysSession->board_ownerid) . DIRECTORY_SEPARATOR.$nnode;

		MailFollow($MailData);
	}

	// 更新quota資訊
	getCalQuota($sysSession->board_ownerid, $quota_used, $quota_limit);
	setQuota($sysSession->board_ownerid, $quota_used);

	// 是否為最新消息類型
	if(IsNewsBoard('news', $sysSession->board_id))
	{
		$open_time   = (isset($_POST['ck_open_time']))? $_POST['open_time']:'0000-00-00 00:00:00';
		$close_time  = (isset($_POST['ck_close_time']))? $_POST['close_time']:'0000-00-00 00:00:00';
		$NEWS = dbGetStSr('WM_news_subject','news_id',"board_id='{$sysSession->board_id}'", ADODB_FETCH_ASSOC);
		dbSet('WM_news_posts', "open_time='{$open_time}',close_time='{$close_time}',news_id='{$NEWS['news_id']}'",
				"board_id={$sysSession->board_id} and node='{$node_id}'");
		if($sysConn->Affected_Rows() == 0) {
			dbNew('WM_news_posts','news_id,board_id,node,open_time,close_time',
				"{$NEWS['news_id']},{$sysSession->board_id},'{$node_id}','{$open_time}','{$close_time}'");
			createNewsXML($sysSession->school_id, 'news');
		}

		// APP 訊息推播 - Begin：未設定啟用時間，表示即時發佈、即時推播
		if ($open_time === '0000-00-00 00:00:00') {
			$dbHandler = new DatabaseHandler();
			$channels = $dbHandler->getAllUsers();

			$pushData = JsonUtility::encode(
				array(
					'sender' => $sysSession->username,
					'content' => $subject,
					'alert' => $sysSession->school_name . $MSG['school_post_news'][$sysSession->lang],
					'channel' => $channels,
					'alertType' => 'NEWS',
					'messageID' => $sysSession->board_id . '#' . $nnode
				)
			);

			require_once(sysDocumentRoot . '/xmlapi/push-handler.php');
		}
		// APP 訊息推播 - End
	}
	else if (BOARD_TYPE == 'quint' && IsNewsBoard('faq'))
	{
		createFAQXML($sysSession->school_id, 'faq');
	}

    // APP 課程公告 PUSH - Begin
    if (sysEnableAppServerPush && (intval($sysSession->board_id) === intval($annBid)) && $newPostFlag) {
        $pushData = JsonUtility::encode(
            array(
                'type' => 'bulletin',
                'id' => $sysSession->board_id . '#' . $nnode
            )
        );

        require_once(sysDocumentRoot . '/lib/app_course_push_handler.php');
    }
    // APP 課程公告 PUSH -End
	// 回到閱讀或列表
        /*
	if ($_REQUEST['threadList'] == 1)
		header('Location: t_index.php?' . $_SERVER["QUERY_STRING"]);
	if ($_REQUEST['threadSequence'] == 1)
		header('Location: thread.php?node=' . $node . '&' . $_SERVER["QUERY_STRING"]);
	else*/if (BOARD_TYPE == 'board')
		header('Location: '.($sysSession->post_no?"/forum/510,{$sysSession->board_id},{$sysSession->post_no}.php":"/forum/500,{$sysSession->board_id},{$sysSession->page_no},{$sysSession->sortby}.php"));
	else
		header('Location: '.($sysSession->q_post_no?"/forum/570,{$sysSession->board_id},{$sysSession->q_post_no}.php":"/forum/560,{$sysSession->board_id},{$sysSession->q_page_no},{$sysSession->q_sortby}.php"));
?>
