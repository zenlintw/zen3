<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/learn/chatrec/lib_chat_records.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/forum/lib_mailfollow.php');


	// 使用哪種語系張貼
	$lang_mapping = array(
		'Big5'   => 1,
		'en'	 => 2,
		'GB2312' => 3
	);

	/*************************

	新增討論區文章資料 db_new_post($RS)

	參數 : $RS ( dbGetStSr() 所取得的 WM_bbs_posts 陣列 )
		( 所需路徑 path 已放好在 $RS['path'] 中, 此新增不含 attach 欄位 )

	傳回值: 失敗傳回 0
		成功傳回節點編號 (node)

	 *************************/
	function db_new_post($RS) {
		global $sysConn,$sysSiteNo,$sysSession;

		// 取得目前討論板中最大的文章 node
		list($mnode) = dbGetStSr('WM_bbs_posts', 'MAX(node)',"board_id={$RS['board_id']} and length(node) = 9", ADODB_FETCH_NUM);
		// 產生本篇的 node
		$nnode = empty($mnode)?'000000001':sprintf("%09d", $mnode+1);

		// 加入資料庫
		// 換掉可能之引號
		// $username = mysql_escape_string($sysSession->username);
		// $realname = mysql_escape_string($sysSession->realname);
		foreach($RS as $k=>$v) {
			$RS[$k] = mysql_escape_string($v);
		}
		$fields = 'board_id,node,site,pt,poster,realname,email,homepage,subject,content,lang';
		/*
		$values = "{$RS['board_id']}, '$nnode',$sysSiteNo".
			      ", NOW(), '{$username}', '{$realname}', ".
			      "'{$sysSession->email}', '{$sysSession->homepage}', '{$RS['subject']}', '{$RS['content']}',".
			      $RS['lang'];
		*/
		$values = "{$RS['board_id']}, '$nnode',$sysSiteNo".
			      ", NOW(), '{$RS['poster']}', '{$RS['realname']}', ".
			      "'', '', '{$RS['subject']}', '{$RS['content']}',".
			      $RS['lang'];
		dbNew('WM_bbs_posts',$fields,$values );

		if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0)
			return 0;
		else
			return $nnode;
	}

	/* 產生討論板文章夾檔存放目錄 (不含 node) ( 改自 /lib/file_api.php )
	 * @param int $board_id : 討論版編號
	 * @param int $owner_id : 討論版擁有者編號 ( 學校編號 , 課程編號, 班級編號, ... )
	 * input : $owner_id = 課程編號或班級編號
	 * return: 傳回路徑
	 */
	function get_attach_path($board_id, $owner_id){
		global $sysSession;

		$ret = '/base/' . $sysSession->school_id;

		switch(strlen($owner_id)) {
			case 5:
				break;
			case 7:		// 班級
				$ret .= '/class/'.$owner_id;
				break;

			case 15:	// 班級群組
				$ret .= '/class/'.substr($owner_id,0,7);
				break;

			case 8:		// 課程
				$ret .= '/course/'.$owner_id;
				break;

			case 16:	// 課程群組
				$ret .= '/course/'.substr($owner_id,0,8);
				break;

			default:
				if ($sysSession->course_id){
					$ret .= '/course/' . $sysSession->course_id;
				} else if($sysSession->class_id) {
					$ret .= '/class/' . $sysSession->class_id;
				}
				break;
		}
		$ret .= "/board/" . $board_id ;
		return $ret;
	}

	/*************************

	複製文章夾檔 copyAttaches

	@param string $src_dir : 附檔來源路徑 ( WM root 以後路徑 )
	@param string $tar_dir : 附檔目標路徑 ( WM root 以後路徑 )
	@param string $files   : 附檔來源路徑
	 *************************/
	function copyAttaches( $src_dir, $tar_dir, &$files) {
		$file_arr    = explode(Chr(9), $files);
		$files_count = count($file_arr);
		$tar_dir     = sysDocumentRoot . $tar_dir;

		if($files_count%2 == 1)	{ // 夾檔格式錯誤( 需成對 )
			$files_count--;
			unset($file_arr[$files_count]);
		}

		for($i=0; $i<$files_count; $i+=2){
			$src_full = sysDocumentRoot. DIRECTORY_SEPARATOR . $src_dir. DIRECTORY_SEPARATOR .$file_arr[$i+1];
			$issc = false;
			if( is_file($src_full) && mkdirs($tar_dir)) {
				// $source = $src_dir . DIRECTORY_SEPARATOR . $file_arr[$i+1];
				$target_file = uniqid('WM') . strrchr($file_arr[$i+1], '.');
				$target = $tar_dir  . DIRECTORY_SEPARATOR . $target_file;
				if (copy($src_full, $target)) {	// SUCCESS
					$file_arr[$i+1] = $target_file;
					$issc = true;
				}
			}
			if (!$issc)
			{
				unset($file_arr[$i]);
				unset($file_arr[$i+1]);
			}
		}
		$files = implode(Chr(9), $file_arr);
		return true;
	}

	/**
	 * 儲存討論室紀錄
	 * @param int  $owner_id : 擁有者編號
	 * @param string $subject : 主旨
	 * @param string $content : 本文
	 * @param string $attach_dir : 夾檔來源路徑
	 * @param string $attach_files : 夾檔來源( {file_name}\t{system_name}\t..... )
	 * @param string $content : 本文
	 * @return int : 成功 >0(文章節點編號), 失敗 false
	 **/
	function saveRecord($owner_id, &$subject, &$content, $attach_dir='', $attach_files='') {
		global $sysConn, $sysSession, $lang_mapping, $MSG;

		// 先取得板號
		if(!dbGetRecordBoard($owner_id, $r))
			return false;

		$board_id = $r['board_id'];
		$post =  Array('board_id' => $r['board_id'],
					   'subject'  => $subject,
					   'content'  => $content,
					   'poster'   => 'sysop',
					   'realname' => 'sysop',
					   'lang'	  => $lang_mapping[$sysSession->lang] );
		$node = db_new_post($post);

		// 夾檔處理
		if(	$node ) {
			// 轉寄到討論版訂閱者 begin
			include_once(sysDocumentRoot . '/lang/forum.php');
			$MailData = Array();
			$MailData['subject']    = stripslashes($subject);
			$MailData['title']      = '==================' . $sysSession->school_name. "\t" .$_SERVER['HTTP_HOST'] . '==================' . "<br>\r\n";
			$MailData['course']     = $MSG['mail_cname'][$sysSession->lang]   . $sysSession->course_name ."<br>\r\n" ;
			$MailData['body']	    = $MSG['mail_board'][$sysSession->lang]   . $MSG['chat_records'][$sysSession->lang] . "<br>\r\n" .
							          $MSG['mail_poster'][$sysSession->lang]  . 'sysop(sysop)' . "<br>\r\n" .
							          $MSG['mail_ptime'][$sysSession->lang]   . Date("Y-m-d H:i:s") . "<br>\r\n" .
							          $MSG['mail_subject'][$sysSession->lang] . $subject . "<br><br>\r\n" .
							          $content;
			$MailData['attach']	    = $attach_files;
			$MailData['attach_dir']	= $attach_dir;
			MailFollow($MailData, $board_id);
			// 轉寄到討論版訂閱者 end
			
			if( $attach_files=='')
				return $node;

			$tar_dir = get_attach_path($r['board_id'], $owner_id) . DIRECTORY_SEPARATOR . $node ;
			if( copyAttaches( $attach_dir, $tar_dir, $attach_files) )	// 複製夾檔
				dbSet('WM_bbs_posts',"attach='{$attach_files}'","board_id='{$board_id}' and node='{$node}'");	// 更新資料庫
			return $node;
		} else
			return FALSE;

	}


//==================================================
// 以下為供外界呼叫之各 API
//
//==================================================

	/**
	 * 儲存學校討論室紀錄
	 * @param int  $school_id : 學校編號
	 * @param string $subject : 主旨
	 * @param string $content : 本文
	 * @param string $attach_dir : 夾檔來源路徑
	 * @param string $attach_files : 夾檔來源( {file_name}\t{system_name}\t..... )
	 * @return int : 成功 >0(文章節點編號), 失敗 false
	 **/
	function saveSchoolRecord($school_id, &$subject, &$content, $attach_dir='', $attach_files='') {
		global $sysConn;
		if( empty($school_id) || strlen($school_id)!=5 ) {
			return false;
		}
		return saveRecord($school_id, $subject, $content, $attach_dir, $attach_files);
	}

	/**
	 * 儲存課程討論室紀錄
	 * @param int  $course_id : 學校編號
	 * @param string $subject : 主旨
	 * @param string $content : 本文
	 * @param string $attach_dir : 夾檔來源路徑
	 * @param string $attach_files : 夾檔來源( {file_name}\t{system_name}\t..... )
	 * @return int : 成功 >0(文章節點編號), 失敗 false
	 **/
	function saveCourseRecord($course_id, &$subject, &$content, $attach_dir='', $attach_files='') {
		global $sysConn;
		if( empty($course_id) || strlen($course_id)!=8 ) return false;
		return saveRecord($course_id, $subject, $content, $attach_dir, $attach_files);
	}

	/**
	 * 儲存班級討論室紀錄
	 * @param int  $class_id : 班級編號
	 * @param string $subject : 主旨
	 * @param string $content : 本文
	 * @param string $attach_dir : 夾檔來源路徑
	 * @param string $attach_files : 夾檔來源( {file_name}\t{system_name}\t..... )
	 * @return int : 成功 >0(文章節點編號), 失敗 false
	 **/
	function saveClassRecord($class_id, &$subject, &$content, $attach_dir='', $attach_files='') {
		global $sysConn;
		if( empty($class_id) || strlen($class_id)!=7 ) return false;
		return saveRecord($class_id, $subject, $content, $attach_dir, $attach_files);
	}

	/**
	 * 儲存課程小組討論室紀錄
	 * @param int  $course_id : 課程編號
	 * @param int  $group_id  : 小組編號
	 * @param int  $team_id   : 組次編號
	 * @param string $subject : 主旨
	 * @param string $content : 本文
	 * @param string $attach_dir : 夾檔來源路徑
	 * @param string $attach_files : 夾檔來源( {file_name}\t{system_name}\t..... )
	 * @return int : 成功 >0(文章節點編號), 失敗 false
	 **/
	function saveCourseGrpRecord($course_id, $group_id, $team_id, &$subject, &$content, $attach_dir='', $attach_files='') {
		global $sysConn;
		if( empty($course_id) || strlen($course_id)!=8
			|| empty($group_id)	|| empty($team_id) ) return false;
		$owner_id = sprintf('%d%04d%04d', $course_id, $team_id, $group_id);
		return saveRecord($owner_id, $subject, $content, $attach_dir, $attach_files);
	}

	/**
	 * 儲存班級小組討論室紀錄
	 * @param int  $class_id : 班級編號
	 * @param int  $group_id  : 小組編號
	 * @param int  $team_id   : 組次編號
	 * @param string $subject : 主旨
	 * @param string $content : 本文
	 * @param string $attach_dir : 夾檔來源路徑
	 * @param string $attach_files : 夾檔來源( {file_name}\t{system_name}\t..... )
	 * @return int : 成功 >0(文章節點編號), 失敗 false
	 **/
	function saveClassGrpRecord($class_id, $group_id, $team_id, &$subject, &$content, $attach_dir='', $attach_files='') {
		global $sysConn;
		if( empty($class_id) || strlen($class_id)!=7
			|| empty($group_id)	|| empty($team_id) ) return false;
		$owner_id = sprintf('%d%04d%04d', $class_id, $team_id, $group_id);
		return saveRecord($owner_id, $subject, $content, $attach_dir, $attach_files);
	}
?>
