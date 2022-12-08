<?php
    /**
     * �ҵ{�]��
     *
     * PHP 4.4.7+, MySQL 4.0.21+, Apache 1.3.36+
     *
     * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
     * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
     * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
     *
     * @package     WM3
     * @author      SHIH JUNG YEH <yea@sun.net.tw>
     * @copyright   2000-2008 SunNet Tech. INC.
     * @version     CVS: $Id$
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2008-06-20
     * 
     * �Ƶ��G          
     */

// {{{ �禡�w�ޥ� begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/quota.php');
	require_once(sysDocumentRoot . '/lang/course_copy.php');
	require_once(sysDocumentRoot . '/teach/course/import_imsmanifest.lib.php');
// }}} �禡�w�ޥ� end

// {{{ �`�Ʃw�q begin
    define('path_amount_limit', 49);
// }}} �`�Ʃw�q end
    
// {{{ �ܼƫŧi begin
	$course_elements = $_POST['course_elements']; // �]�ˤ��e�ﶵ    
// }}} �ܼƫŧi end

// {{{ ��ƫŧi begin
	function cloneCourse($old_cid, $new_cid)
	{
	    global $course_elements;
	
		$old_cid = intval($old_cid);
		$new_cid = intval($new_cid);
		
		if (in_array("subject_board", $course_elements)) // ĳ�D�Q�תO ���Ŀ�
            _processBD($old_cid, $new_cid);				 // ĳ�D�Q��  

        _processQTI($old_cid, $new_cid);                 // �T�X�@
		
		if (in_array("node", $course_elements))          // �Ч��`�I ���Ŀ�
		{
    		_processTermPath($old_cid, $new_cid);        // �ǲ߸��|
    		_processContent($old_cid, $new_cid);         // �Ч�
		}
		
		// ���s�p��quota
		getCalQuota($new_cid, $quota_used, $quota_limit);
		setQuota($new_cid, $quota_used);
	}
	
	/**
	 * �B�z�T�X�@
	 * @param int $old_cid �½ҵ{course_id
	 * @param int $new_cid �s�ҵ{course_id
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
			// �ƻs�D��
			$rs = dbGetStMr('WM_qti_' . $qti_which . '_item', '*', 'course_id=' . $old_cid, ADODB_FETCH_ASSOC);
			if ($rs) while ($row = $rs->FetchRow())
			{
				$old_ident[$i]    = $row['ident'];
				$row['ident']     = $ident . ($count++);
				$new_ident[$i]    = $row['ident'];
				$row['content']   = str_replace($old_ident[$i], $new_ident[$i], $row['content']);
				$row['course_id'] = $new_cid;
				
				if ($sysConn->AutoExecute('WM_qti_' . $qti_which . '_item', $row, 'INSERT')) // �ƻs����
				{
					if (is_dir("{$old_path}/{$old_ident[$i]}"))
					{
						if (!is_dir($new_path)) @exec('mkdir -p ' . $new_path);
						@exec("cp -Rf {$old_path}/{$old_ident[$i]} {$new_path}/{$new_ident[$i]}");
					}
				}
				
				$i++;
			}
			
			// �ƻs�ը�
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
	 * �B�zĳ�D�Q��
	 * @param int $old_cid �½ҵ{course_id
	 * @param int $new_cid �s�ҵ{course_id
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
	 * �B�z�ǲ߸��|
	 * @param int $old_cid �½ҵ{course_id
	 * @param int $new_cid �s�ҵ{course_id
	 */
	function _processTermPath($old_cid, $new_cid)
	{
	    global $sysConn;
	    
        /*** CUSTOM (B) by Yea for MIS#9336 ***/
        list($new_content, $path_amount) = dbGetStSr('WM_term_path', 'content, serial', "course_id={$new_cid} order by serial DESC limit 1", ADODB_FETCH_NUM);

        if (count($path_amount) > path_amount_limit) // �p�G�s�W�L 50 �Ӹ��|
        {
            // �R�� 50 �H�e��
            dbDel('WM_term_path', 'course_id=' . $new_cid . ' and serial in (' . implode(',', array_slice($path_amount, path_amount_limit)) . ')');
            // ���̪� 50 ��
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

        /*** CUSTOM (B) by Yea for mis#11939 �ǲ߸��|�O�걵�b��Ӥ��O�л\ ***/
        // ���o�½ҵ{���ǲ߸��|
        $content = dbGetOne('WM_term_path', 'content', "course_id={$old_cid} order by serial desc");
        processNewImsmanifest($content,false,false);  //��3�ӰѼƬO�Ȼs��by lubo
        /*** CUSTOM (E) by Yea ***/
	}
    
	/**
	 * �B�z�Ч�
	 * @param int $old_cid �½ҵ{course_id
	 * @param int $new_cid �s�ҵ{course_id
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
// }}} ��ƫŧi end

// {{{ �D�{�� begin
	if ($sysConn->GetOne('select count(*) from WM_term_course where course_id=' . intval($_POST['course_id'])))
	{
		$old_cid = intval($_POST['course_id']);
		$new_cid = $sysSession->course_id;
		
		if(is_array($course_elements)) cloneCourse($old_cid, $new_cid);
		printf('<body><h2 align="center"><br /><br />%s</h2></body>', $MSG['co_msg_pack'][$sysSession->lang]);
	} 
// }}} �D�{�� end

?>