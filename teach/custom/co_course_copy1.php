<?php
    /**
     * 課程包裝
     *
     * PHP 4.4.7+, MySQL 4.0.21+, Apache 1.3.36+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      SHIH JUNG YEH <yea@sun.net.tw>
     * @copyright   2000-2008 SunNet Tech. INC.
     * @version     CVS: $Id$
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2008-06-20
     * 
     * 備註：          
     */

// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/quota.php');
	require_once(sysDocumentRoot . '/lang/course_copy.php');
	require_once(sysDocumentRoot . '/teach/course/import_imsmanifest.lib.php');
// }}} 函式庫引用 end

// {{{ 常數定義 begin
    define('path_amount_limit', 49);
// }}} 常數定義 end
    
// {{{ 變數宣告 begin
	$course_elements = $_POST['course_elements']; // 包裝內容選項    
// }}} 變數宣告 end

// {{{ 函數宣告 begin
	function cloneCourse($old_cid, $new_cid)
	{
	    global $course_elements;
	
		$old_cid = intval($old_cid);
		$new_cid = intval($new_cid);
		
		if (in_array("subject_board", $course_elements)) // 議題討論板 有勾選
            _processBD($old_cid, $new_cid);				 // 議題討論  

        _processQTI($old_cid, $new_cid);                 // 三合一
		
		if (in_array("node", $course_elements))          // 教材節點 有勾選
		{
    		_processTermPath($old_cid, $new_cid);        // 學習路徑
    		_processContent($old_cid, $new_cid);         // 教材
		}
		
		// 重新計算quota
		getCalQuota($new_cid, $quota_used, $quota_limit);
		setQuota($new_cid, $quota_used);
	}
	
	/**
	 * 處理三合一
	 * @param int $old_cid 舊課程course_id
	 * @param int $new_cid 新課程course_id
	 */
	function _processQTI($old_cid, $new_cid)
	{
		global $sysConn, $sysSession, $course_elements;

        $old_cid = intval($old_cid);
        $new_cid = intval($new_cid);
		
		foreach(array('exam', 'homework', 'questionnaire') as $qti_which)
		{
		    if (!in_array($qti_which, $course_elements)) continue;
		
			$i = 0;
			$old_ident = array();
			$new_ident = array();
			$old_path  = sprintf('%s/base/%05d/course/%08d/%s/Q/', sysDocumentRoot, $sysSession->school_id, $old_cid, $qti_which);
			$new_path  = sprintf('%s/base/%05d/course/%08d/%s/Q/', sysDocumentRoot, $sysSession->school_id, $new_cid, $qti_which);
			
			$t     = split('[. ]', microtime());
			$ident = sprintf('WM_ITEM1_%s_%u_%s_', sysSiteUID, $new_cid, $t[2]);
			$count = intval(substr($t[1],0,6));
			// 複製題目
			$rs = dbGetStMr('WM_qti_' . $qti_which . '_item', '*', 'course_id=' . $old_cid, ADODB_FETCH_ASSOC);
			if ($rs) while ($row = $rs->FetchRow())
			{
				$old_ident[$i]    = $row['ident'];
				$row['ident']     = $ident . ($count++);
				$new_ident[$i]    = $row['ident'];
				$row['content']   = str_replace($old_ident[$i], $new_ident[$i], $row['content']);
				$row['course_id'] = $new_cid;
				
				if ($sysConn->AutoExecute('WM_qti_' . $qti_which . '_item', $row, 'INSERT')) // 複製夾檔
				{
					if (is_dir("{$old_path}/{$old_ident[$i]}"))
					{
						if (!is_dir($new_path)) @exec('mkdir -p ' . $new_path);
						@exec("cp -Rf {$old_path}/{$old_ident[$i]} {$new_path}/{$new_ident[$i]}");
					}
				}
				
				$i++;
			}
			
			// 複製試卷
			$rs = dbGetStMr('WM_qti_' . $qti_which . '_test', '*', 'course_id=' . $old_cid, ADODB_FETCH_ASSOC);
			if ($rs) while ($row = $rs->FetchRow())
			{
				$row['exam_id']   = 'NULL';
				$row['course_id'] = $new_cid;
				$row['content']   = str_replace($old_ident, $new_ident, $row['content']);
				$sysConn->AutoExecute('WM_qti_' . $qti_which . '_test', $row, 'INSERT');
			}
		}
	}
	
	/**
	 * 處理議題討論
	 * @param int $old_cid 舊課程course_id
	 * @param int $new_cid 新課程course_id
	 */	
	function _processBD($old_cid, $new_cid)
	{
		global $sysConn, $sysSession;
		list($discuss,$bulletin) = dbGetStSr('WM_term_course', 'discuss,bulletin', "course_id=$old_cid");
		
		$RS = dbGetStMr('WM_bbs_boards','*',"owner_id=$old_cid and board_id not in ($discuss,$bulletin)");
		while ($fields = $RS->FetchRow())
		{
			$bbs_sql = 'insert into WM_bbs_boards (bname, manager, title, owner_id, open_time, close_time, share_time, switch, with_attach, vpost, default_order, post_times, extras) values '.
									 "('".addslashes($fields['bname'])."','{$fields['manager']}','".addslashes($fields['title'])."','{$new_cid}','{$fields['open_time']}','{$fields['close_time']}','{$fields['share_time']}',".
									 "'{$fields['switch']}','{$fields['with_attach']}','{$fields['vpost']}','{$fields['default_order']}','{$fields['post_times']}','{$fields['extras']}')";
			// echo $bbs_sql."<br>";				 
			$sysConn->Execute($bbs_sql);
			// echo $sysConn->ErrorMsg();
			$board_id = $sysConn->Insert_ID();
			$RS1 = dbGetStMr('WM_term_subject','*',"course_id=$old_cid and board_id = '{$fields['board_id']}'");
			while ($fields1 = $RS1->FetchRow())
			{
				$sub_sql = 'insert into WM_term_subject (course_id, board_id, state, visibility, permute) values '.
									 "('{$new_cid}','{$board_id}','{$fields1['state']}','{$fields1['visibility']}','{$fields1['permute']}')";
				// echo $sub_sql."<br>";
				$sysConn->Execute($sub_sql);
				// echo $sysConn->ErrorMsg();
			}
		}
	}
	
	/**
	 * 處理學習路徑
	 * @param int $old_cid 舊課程course_id
	 * @param int $new_cid 新課程course_id
	 */
	function _processTermPath($old_cid, $new_cid)
	{
	    global $sysConn;
	    
        /*** CUSTOM (B) by Yea for MIS#9336 ***/
        list($new_content, $path_amount) = dbGetStSr('WM_term_path', 'content, serial', "course_id={$new_cid} order by serial DESC limit 1", ADODB_FETCH_NUM);

        if (count($path_amount) > path_amount_limit) // 如果存超過 50 個路徑
        {
            // 刪除 50 以前的
            dbDel('WM_term_path', 'course_id=' . $new_cid . ' and serial in (' . implode(',', array_slice($path_amount, path_amount_limit)) . ')');
            // 更改最近 50 個
            for($i=path_amount_limit-1; $i>=0; $i--)
            {
                dbSet('WM_term_path', 'serial=' . (path_amount_limit - $i), 'course_id=' . $new_cid . ' and serial=' . $path_amount[$i]);
            }
        }
        /*** CUSTOM (E) by Yea ***/	    
	    
		//$serial  = max(1, dbGetOne('WM_term_path', 'max(serial)', 'course_id=' . $new_cid, ADODB_FETCH_NUM) );
		//$content = 'replace(replace(content, \'default="Course'.$old_cid.'"\', \'default="Course'.$new_cid.'"\'), \'identifier="Course'.$old_cid.'"\', \'identifier="Course'.$new_cid.'"\')';
        /*** CUSTOM (B) by Yea for MIS#9336 ***/
        //$sysConn->Execute("insert into WM_term_path select '{$new_cid}', " . ($serial+1) . ", {$content}, username, NOW() from WM_term_path where course_id={$old_cid} order by serial desc limit 1");
        /*** CUSTOM (E) by Yea ***/

        /*** CUSTOM (B) by Yea for mis#11939 學習路徑是串接在後而不是覆蓋 ***/
        // 取得舊課程的學習路徑
        $content = dbGetOne('WM_term_path', 'content', "course_id={$old_cid} order by serial desc");
        processNewImsmanifest($content,false,false);  //第3個參數是客製的by lubo
        /*** CUSTOM (E) by Yea ***/
	}
    
	/**
	 * 處理教材
	 * @param int $old_cid 舊課程course_id
	 * @param int $new_cid 新課程course_id
	 */
	function _processContent($old_cid, $new_cid)
	{
		global $sysSession;
        $old_cid = intval($old_cid);
        $new_cid = intval($new_cid);
		$old_path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$old_cid}";
		$new_path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$new_cid}";
		if (!is_dir($old_path)) exec('mkdir -p ' . $old_path);
		if (!is_dir($new_path)) exec('mkdir -p ' . $new_path);
		@exec("cp -Rf {$old_path}/content {$new_path}");
	}    	
// }}} 函數宣告 end

// {{{ 主程式 begin
	if ($sysConn->GetOne('select count(*) from WM_term_course where course_id=' . intval($_POST['course_id'])))
	{
		$old_cid = intval($_POST['course_id']);
		$new_cid = $sysSession->course_id;
		
		if(is_array($course_elements)) cloneCourse($old_cid, $new_cid);
		printf('<body><h2 align="center"><br /><br />%s</h2></body>', $MSG['co_msg_pack'][$sysSession->lang]);
	} 
// }}} 主程式 end

?>