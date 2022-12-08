<?php
	/**
	 * 教師環境的議題討論板的共用函式
	 *
	 * @since   2004/01/08
	 * @author  ShenTing Lin
	 * @version $Id: cour_lib.php,v 1.1 2009-06-25 09:27:40 edi Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/teach_course.php');

	// 議題狀態
	$titleStatus = array(
		'disable' => $MSG['type_disable'][$sysSession->lang],
		'open'    => $MSG['type_open'][$sysSession->lang],
		'taonly'  => $MSG['type_taonly'][$sysSession->lang]
	);

	// 預設排序的欄位
	$titleSort = array(
		'pt'      => $MSG['field_pt'][$sysSession->lang],
		'subject' => $MSG['field_subject'][$sysSession->lang],
		'poster'  => $MSG['field_poster'][$sysSession->lang],
		'rank'    => $MSG['field_rank'][$sysSession->lang],
		'hit'     => $MSG['field_hit'][$sysSession->lang]
	);
	
	/**
	 * 取得教師個人分享教材庫(若不存在自動產生一個)
	 * @param string $username 教師帳號
	 * @return int content_id (false 表示錯誤)
	 */
	function getTeacherContentDatabase($username='')
	{
		global $sysSession, $sysConn, $sysRoles;
		
		if (empty($username)) $username = $sysSession->username;
		if (!aclCheckRole($username, $sysRoles['teacher'])) return false; // 檢查是否有教師身分
			
		$content_id = dbGetOne('WM_content_ta', 'content_id', 'username="'.$username.'"');
		if (!empty($content_id)) return $content_id;
		
		$caption = array('Big5'        => 'REPOSITORY of ' . $username, 
		                 'GB2312'      => 'REPOSITORY of ' . $username,
		                 'en'          => 'REPOSITORY of ' . $username,
		                 'EUC-JP'      => 'REPOSITORY of ' . $username,
		                 'user_define' => 'REPOSITORY of ' . $username);
		$caption = serialize($caption);

		dbNew('WM_content', 'caption,quota_limit,status,kind,content_type,content_sn',
			  "'{$caption}', 204800, 'modifiable', 'content', 'digitization', '{$username}_".Date('Ymd')."'");
		if ($sysConn->Affected_Rows() > 0)
		{
			$InsertID    = $sysConn->Insert_ID();
			$contentPath = sysDocumentRoot . "/base/{$sysSession->school_id}/content/{$InsertID}";
			include_once(sysDocumentRoot . '/lib/file_api.php');
			mkdirs($contentPath);	                                                                   // 建立教材的目錄
			dbSet('WM_content', "path='{$contentPath}'", "content_id={$InsertID}");                    // 設定教材路徑
			dbNew('WM_content_ta', 'username, content_id', "'{$username}', '{$InsertID}'");            // 設定content_ta
			return $InsertID;
		}
		return false;
	}