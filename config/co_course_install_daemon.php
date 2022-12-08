<?php
    set_time_limit(0);
    define('IS_CLI_RUN',true);
    
    require_once(dirname(__FILE__) . '/console_initialize.php');
    require_once(sysDocumentRoot . '/lang/co_course_pack_install.php');
    require_once(sysDocumentRoot . '/lib/quota.php');
	require_once(sysDocumentRoot . '/lib/mime_mail.php');
    
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
    
    //$sysConn->debug=true;
	/**
	 * 處理三合一
	 */
	function _processQTI($src_school_id,$src_course_id, $tar_school_id,$tar_course_id)
	{
		global $sysConn,$course_elements;
		
		foreach(array('exam', 'homework', 'questionnaire') as $qti_which)
		{
		    if (!in_array($qti_which, $course_elements)) continue;
		
			$i = 0;
			$old_ident = array();
			$new_ident = array();
			$old_path  = sprintf('%s/base/%05d/course/%08d/%s/Q/', sysDocumentRoot, $src_school_id, $src_course_id, $qti_which);
			$new_path  = sprintf('%s/base/%05d/course/%08d/%s/Q/', sysDocumentRoot, $tar_school_id, $tar_course_id, $qti_which);
			
			$t     = split('[. ]', microtime());
			$ident = sprintf('WM_ITEM1_%s_%u_%s_', sysSiteUID, $tar_school_id, $t[2]);
			$count = intval(substr($t[1],0,6));
            
			// 複製題目
            $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
            $qti_data = $sysConn->GetArray("select * from WM_qti_{$qti_which}_item where course_id=$src_course_id");
			$sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
			foreach($qti_data as $row)
			{
				$old_ident[$i]    = $row['ident'];
				$row['ident']     = $ident . ($count++);
				$new_ident[$i]    = $row['ident'];
				$row['content']   = str_replace($old_ident[$i], $new_ident[$i], $row['content']);
				$row['course_id'] = $tar_course_id;
				
				if ($sysConn->AutoExecute('WM_qti_' . $qti_which . '_item', $row, 'INSERT')) // 複製夾檔
				{
					if (is_dir("{$old_path}/{$old_ident[$i]}"))
					{
						if (!is_dir($new_path)) exec('mkdir -p ' . $new_path);
						exec("cp -Rf {$old_path}/{$old_ident[$i]} {$new_path}/{$new_ident[$i]}");
					}
				}
				
				$i++;
			}
			
			// 複製試卷
            $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
            $test_data = $sysConn->GetArray("select * from WM_qti_{$qti_which}_test where course_id=$src_course_id");
			$sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
			foreach($test_data as $row)
			{
				$row['exam_id']   = 'NULL';
				$row['course_id'] = $tar_course_id;
				$row['content']   = str_replace($old_ident, $new_ident, $row['content']);
				$sysConn->AutoExecute('WM_qti_' . $qti_which . '_test', $row, 'INSERT');
			}
		}
	}
    
    function _processChatroom($src_school_id,$src_course_id, $tar_school_id,$tar_course_id){
        global $sysConn,$course_elements;
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
        // WM_chat_setting.owner
        $chat_setting = $sysConn->GetArray("select * from WM_chat_setting where owner like '{$src_course_id}%' ");
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
        if(!empty($chat_setting)){
            foreach($chat_setting as $c){
                $c['owner'] = $tar_course_id;
                $c['rid'] = uniqid('');
                $sysConn->AutoExecute('WM_chat_setting', $c, 'INSERT');
            }
        }
        
        // file course/$course_id/chat
        $old_path = sysDocumentRoot . "/base/{$src_school_id}/course/{$src_course_id}/chat/*";
        $new_path = sysDocumentRoot . "/base/{$tar_school_id}/course/{$tar_course_id}/chat/";
        if (!is_dir($new_path)) exec('mkdir -p ' . $new_path);
        $cmd = "cp -Rf {$old_path} {$new_path}";
        exec($cmd);
        
        
    }
	
	function _processBD($src_school_id,$src_course_id, $tar_school_id,$tar_course_id)
	{
		global $sysConn,$course_elements;
        
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
        //list($discuss,$bulletin) = $sysConn->GetRow("select discuss,bulletin from WM_term_course where course_id=$src_course_id");
		// list($discuss, $bulletin) = dbGetStSr('WM_term_course', 'discuss,bulletin', "course_id=$old_cid");
        $tmp = $sysConn->GetRow("select discuss,bulletin from WM_term_course where course_id=$src_course_id");
        $discuss = $tmp['discuss'];
        $bulletin = $tmp['bulletin'];
        echo "discuss=$discuss,bulletin=$bulletin\n";
		$bbs_data = $sysConn->GetArray("select * from WM_bbs_boards where owner_id=$src_course_id");
        foreach($bbs_data as $fields)
		{   
            echo "\n board_id={$fields['board_id']}";
            
            $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
            
			$bbs_sql = 'insert into WM_bbs_boards (bname, manager, title, owner_id, open_time, close_time, share_time, switch, with_attach, vpost, default_order, post_times, extras) values '.
									 "('".addslashes($fields['bname'])."','{$fields['manager']}','".addslashes($fields['title'])."','{$tar_course_id}','{$fields['open_time']}','{$fields['close_time']}','{$fields['share_time']}',".
									 "'{$fields['switch']}','{$fields['with_attach']}','{$fields['vpost']}','{$fields['default_order']}','{$fields['post_times']}','{$fields['extras']}')";
			// echo $bbs_sql."<br>";				 
			$sysConn->Execute($bbs_sql);
			// echo $sysConn->ErrorMsg();
			$board_id = $sysConn->Insert_ID();
            
            $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
            //get bbs posts
            $posts_data = $sysConn->GetArray("select * from WM_bbs_posts where board_id={$fields['board_id']}");
            if(!empty($posts_data)){
                //insert to target
                $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
                foreach($posts_data as $p){
                    $p['board_id'] = $board_id;
                    $sysConn->AutoExecute('WM_bbs_posts', $p, 'INSERT');
                }
                //copy posts files
                // /home/WM3_MOOC_LITE_Lubo/base/10001/course/10000111/board/1000000149/000000001/
                $old_path = sysDocumentRoot . "/base/{$src_school_id}/course/{$src_course_id}/board/{$fields['board_id']}/*";
                $new_path = sysDocumentRoot . "/base/{$tar_school_id}/course/{$tar_course_id}/board/$board_id/";
                if (!is_dir($new_path)) exec('mkdir -p ' . $new_path);
                $cmd = "cp -Rf {$old_path} {$new_path}";
                exec($cmd);
            }
            $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
            $subject_data = $sysConn->GetArray("select * from WM_term_subject where course_id=$src_course_id and board_id ={$fields['board_id']}");
			
            // ----------------------------------------------------------
            //chat posts
            // WM_chat_records.owner_id / board_id
            if (in_array("chatroom", $course_elements)) {
                $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
                $chat_records = $sysConn->GetArray("select board_id,type,owner_id from WM_chat_records where board_id={$fields['board_id']} and owner_id = {$src_course_id}");
                
                if(!empty($chat_records)){
                    foreach($chat_records as $c){
                        $c['owner_id'] = $tar_course_id;
                        $c['board_id'] = $board_id;
                        
                        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
                        $sysConn->AutoExecute('WM_chat_records', $c, 'INSERT');
                        // WM_bbs_posts.board_id
                        /* if(is_numeric($board_id)){
                            $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
                            $posts_data = $sysConn->GetArray("select * from WM_bbs_posts where board_id={$fields['board_id']}");
                            if(!empty($posts_data)){
                                foreach($posts_data as $p){
                                    $p['board_id'] = $board_id;
                                    $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
                                    $sysConn->AutoExecute('WM_bbs_posts', $p, 'INSERT');
                                }
                            }
                        } */
                    }
                }
            }
            // ----------------------------------------------------------
            
            $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
            //set discuss,bulletin
            if( $fields['board_id'] == $discuss ) {
                $sysConn->Execute("update WM_term_course set discuss=$board_id where course_id=$tar_course_id");
            }else if( $fields['board_id'] == $bulletin ) {
                $sysConn->Execute("update WM_term_course set bulletin=$board_id where course_id=$tar_course_id");
            }
			foreach($subject_data as $fields1)
			{
				$sub_sql = 'insert into WM_term_subject (course_id, board_id, state, visibility, permute) values '.
									 "('{$tar_course_id}','{$board_id}','{$fields1['state']}','{$fields1['visibility']}','{$fields1['permute']}')";
				// echo $sub_sql."<br>";
				$sysConn->Execute($sub_sql);
				// echo $sysConn->ErrorMsg();
			}
		}
	}
	
	/**
	 * 處理學習路徑
	 */
	function _processTermPath($src_school_id,$src_course_id, $tar_school_id,$tar_course_id)
	{
	    global $sysConn,$course_elements;
	    echo "\n _processTermPath($src_school_id,$src_course_id, $tar_school_id,$tar_course_id)"; 
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
        
        $content = $sysConn->GetOne("select content from WM_term_path where course_id={$src_course_id} order by serial desc");
        $content = $sysConn->qstr($content);
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
        $sysConn->Execute("insert into WM_term_path (course_id,serial,content,update_time) value ($tar_course_id, 1, $content, now() )");
	}
     
	/**
	 * 處理教材
	 */
	function _processContent($src_school_id,$src_course_id, $tar_school_id,$tar_course_id)
	{
		global $sysConn,$course_elements;
        echo "\n _processContent";
		$old_path = sysDocumentRoot . "/base/{$src_school_id}/course/{$src_course_id}";
		$new_path = sysDocumentRoot . "/base/{$tar_school_id}/course/{$tar_course_id}";
		if (!is_dir($old_path)) exec('mkdir -p ' . $old_path);
		if (!is_dir($new_path)) exec('mkdir -p ' . $new_path);
        $cmd = "cp -Rf {$old_path}/content {$new_path}";
        echo $cmd;
        echo "\n";
		echo exec($cmd);
        echo "\n";
	}
    
    function setCourseOnline($src_school_id,$src_course_id, $tar_school_id,$tar_course_id)
    {
        global $sysConn,$course_elements;
        
        $state  = 'finish';
        $err_msg = '';
        
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
        $src_data = $sysConn->GetRow("select * from WM_term_course where course_id=$src_course_id");
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
        $path = "/base/{$tar_school_id}/course/{$tar_course_id}";
        $sysConn->Execute("update WM_term_course set status=4,caption='{$src_data['caption']}',kind='{$src_data['kind']}',content='{$src_data['content']}',path='$path',ta_can_sets='{$src_data['ta_can_sets']}' where course_id=$tar_course_id");
        
        //檢查匯入成功與否
        $check['content'] = _checkContent($src_school_id,$src_course_id, $tar_school_id,$tar_course_id);
        $check['course'] = _checkCourse($src_school_id,$src_course_id, $tar_school_id,$tar_course_id);
        $check['qti'] = _checkQTI($src_school_id,$src_course_id, $tar_school_id,$tar_course_id);
        $check['bd'] = _checkBD($src_school_id,$src_course_id, $tar_school_id,$tar_course_id);
        $check['chat'] = _checkChat($src_school_id,$src_course_id, $tar_school_id,$tar_course_id);
        echo "\n---- check ----";
        var_dump($check);
        echo "\n---- check end----";
        $err_msg = array();
        foreach($check as $process_item => $result){
            if($result === false){
                $err_msg []= $process_item . '_FAIL';
                $state = 'fail';
            }else{
                $err_msg []= $process_item . '_OK';
            }
        }
        return array('err_msg'=> implode(',', $err_msg) , 'state'=> $state);
    }
    
    function folderSize($dir){
        if (!is_dir($dir)) return false;
        $io = popen('/usr/bin/du -sb '.$dir, 'r');
        $size = intval(fgets($io,80));
        pclose($io);
        return $size;
    }
    
    function _checkContent($src_school_id,$src_course_id, $tar_school_id,$tar_course_id){
        global $sysConn,$course_elements;
        echo "\n [_checkContent] $src_school_id,$src_course_id, $tar_school_id,$tar_course_id";
        $old_path = sysDocumentRoot . "/base/{$src_school_id}/course/{$src_course_id}/content";
		$new_path = sysDocumentRoot . "/base/{$tar_school_id}/course/{$tar_course_id}/content";
        
        //比對檔案大小
        $s = folderSize($old_path);
        $t = folderSize($new_path);
        
        if( $s == $t )    return true;
        return false;
    }
    
    function _checkCourse($src_school_id,$src_course_id, $tar_school_id,$tar_course_id){
        global $sysConn,$course_elements;
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
        $tmp = $sysConn->GetRow("select * from WM_term_course where course_id=$tar_course_id");
        if( $tmp['status'] == 4 ){
            return true;
        }
        return false;
    }
    
    function _checkQTI($src_school_id,$src_course_id, $tar_school_id,$tar_course_id){
        global $sysConn,$course_elements;
         
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
        $src_total = 0;
        $tar_total = 0;
        foreach(array('exam', 'homework', 'questionnaire') as $qti_which){
            //來源qti數量
            $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
            $qti_data1 = $sysConn->GetArray("select * from WM_qti_{$qti_which}_item where course_id=$src_course_id");
            $src_total += count($qti_data1);
            //目的qti數量
            $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
            $qti_data2 = $sysConn->GetArray("select * from WM_qti_{$qti_which}_item where course_id=$tar_course_id");
            $tar_total += count($qti_data2);
        }
        if( $src_total == $tar_total )  return true;
        return false;
    }
    
    function _checkBD($src_school_id,$src_course_id, $tar_school_id,$tar_course_id){
        global $sysConn,$course_elements;
        //來源板量
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
        $bbs_data1 = $sysConn->GetArray("select * from WM_bbs_boards where owner_id=$src_course_id");
        //目的板量
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
        $bbs_data2 = $sysConn->GetArray("select * from WM_bbs_boards where owner_id=$tar_course_id");
        if( count($bbs_data1) == count($bbs_data2) )    return true;
        return false;
    }
    
    function _checkChat($src_school_id,$src_course_id, $tar_school_id,$tar_course_id){
        global $sysConn,$course_elements;
        //來源板量
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
        $bbs_data1 = $sysConn->GetArray("select * from WM_chat_setting where owner like '{$src_course_id}%'");
        //目的板量
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
        $bbs_data2 = $sysConn->GetArray("select * from WM_chat_setting where owner like '{$tar_course_id}%'");
        if( count($bbs_data1) == count($bbs_data2) )    return true;
        return false;
    }
    
    function getQueueImport($flag = 'wait'){
        global $sysConn,$sysSession;
        //判斷是否已存在queue
        $data = $sysConn->GetArray(sprintf('select * from %s.CO_course_install where state in ("%s")',sysDBname,$flag));
        return $data;
    }
    
    function processImport($d){
        global $sysConn,$course_elements;
        
        if (in_array("subject_board", $course_elements)) // 議題討論板 有勾選
            _processBD($d['src_school_id'],$d['src_course_id'], $d['tar_school_id'],$d['tar_course_id']);				 // 議題討論  

		if (in_array("chatroom", $course_elements))          // chatroom 有勾選
		{
    		_processChatroom($d['src_school_id'],$d['src_course_id'], $d['tar_school_id'],$d['tar_course_id']);
		}
        _processQTI($d['src_school_id'],$d['src_course_id'], $d['tar_school_id'],$d['tar_course_id']);                 // 三合一
		
		if (in_array("course_path", $course_elements))          // 教材節點 有勾選
		{
    		_processTermPath($d['src_school_id'],$d['src_course_id'], $d['tar_school_id'],$d['tar_course_id']);        // 學習路徑
		}
		if (in_array("course_files", $course_elements))          // 教材Content 有勾選
		{
    		_processContent($d['src_school_id'],$d['src_course_id'], $d['tar_school_id'],$d['tar_course_id']);         // 教材
		}
        
        $result = setCourseOnline($d['src_school_id'],$d['src_course_id'], $d['tar_school_id'],$d['tar_course_id']);
        
        //計算課程使用量
        setUsedQuotaByCourseId($d['tar_school_id'], $d['tar_course_id']);
        
        //send email
        //$result['err_msg'] content_OK,course_OK,qti_OK,bd_OK,chat_OK
        sendEmail($result ,$d['owner'], $d['src_school_id'],$d['src_course_id'], $d['tar_school_id'],$d['tar_course_id']);
        
        return $result ;
    }
    
    function setUsedQuotaByCourseId($schoolId, $courseId){
        global $sysConn;
        $path = sysDocumentRoot . '/base/'.$schoolId.'/course/' . $courseId;
        if(is_dir($path)) {
			$ph = popen("du -sk '$path'",'r');
			$buffer = fgets($ph, 256);
			pclose($ph);
			$bu = split("[ \t]+",$buffer);
			$quota_used = $bu[0];
			if (empty($quota_used) || $quota_used < 0) $quota_used = 0;
		}
        
        $field = 'quota_used=' . $quota_used;
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$schoolId ));
        $sysConn->Execute("update WM_term_course set $field where course_id = $courseId");
    }
    
    function sendEmail($result,$owner, $src_school_id,$src_course_id, $tar_school_id,$tar_course_id){
        global $sysConn,$course_elements,$MSG;
        
        $title = '';    //學校名-課程上線成功通知
        $content = '';  //郵件內容
        
        //mail data
        $sysConn->Execute('use '.sysDBname);
        $tar_school = $sysConn->GetRow("select * from WM_school where school_id=$tar_school_id");
        $src_school = $sysConn->GetRow("select * from WM_school where school_id=$src_school_id");
        
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$tar_school_id ));
        $tar_course = $sysConn->GetRow("select * from WM_term_course where course_id=$tar_course_id");
        
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
        $src_course = $sysConn->GetRow("select * from WM_term_course where course_id=$src_course_id");
        
        $tar_course_name = unserialize($tar_course['caption']);
        $src_course_name = unserialize($src_course['caption']);
        
        $mailData = array(
            '%TAR_SCHOOL_NAME%' => $tar_school['school_name'],
            '%TAR_SCHOOL_URL%' =>  "http://{$tar_school['school_host']}",
            '%TAR_COURSE_NAME%' => $tar_course_name['Big5'],
            
            '%SRC_SCHOOL_NAME%' => $src_school['school_name'],
            '%SRC_SCHOOL_URL%' => "http://{$src_school['school_host']}",
            '%SRC_COURSE_NAME%' => $src_course_name['Big5'],
        );

        if( $result['state'] == 'finish' ){   //成功訊息
            $content = $MSG['email_content_ok']['Big5'];
            $title = $MSG['email_title_ok']['Big5'];
        }else{  //失敗訊息
            $content = $MSG['email_content_err']['Big5'];
            $title = $MSG['email_title_err']['Big5'];
        }
        echo "\n[Mail template]".$title."\n------\n".$content;
        
        $title = str_replace(array_keys($mailData),array_values($mailData) , $title);
        $content = str_replace(array_keys($mailData),array_values($mailData) , $content);
        echo "\n[Mail result]".$title."\n------\n".$content;
        //查詢使用者的email
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
        $owner_email = $sysConn->GetOne("select email from WM_user_account where username='$owner'");
        if( empty($owner_email) ) return false;
        
        // 查詢學校的email
        $school_mail = $tar_school['school_mail'];
        $school_host = $tar_school['school_host'];
        $school_name = $tar_school['school_name'];
        
        // 寄件者
        if (empty($school_mail)){
            $school_mail = 	'webmaster@'. $school_host;
        }
        $send_from = mailEncFrom($school_name,$school_mail);
        
        $mail = new mime_mail;
        $mail->subject = mailEncSubject($title, 'utf-8');
        $mail->from = $send_from;
        // $mail->body = iconv('UTF-8', $sysSession->lang, $body);
        $mail->body = $content;
        $mail->body_type = 'text/html';
        $mail->to = $owner_email;
        $mail->charset = 'utf-8';
        $mail->send();
        echo "\n[send email] $owner_email ";
    }
    
	/**
	 * 將 mail 的標題編碼
	 * @param string $from    : 顯示的名稱
	 * @param string $email   : Email
	 * @param string $charset : 字集
	 * @return string : 編碼後的 from
	 **/
	function mailEncFrom($from='', $email='', $charset='utf-8') {
		if (empty($email)) return false;
		if (empty($from)) return $email;

		$cset = strtolower($charset);
		$str  = '=?' . $cset . '?B?' . base64_encode($from) . '?= <' . $email . '>';
		return $str;
	}

    /**
	 * 將 mail 的標題編碼
	 * @param string $subject : 標題
	 * @param string $charset : 字集
	 * @return string : 編碼後的標題
	 **/
	function mailEncSubject($subject='', $charset='utf-8') {
		if (empty($subject)) return false;
		$cset = strtolower($charset);
		$str  = '=?' . $cset . '?B?' . base64_encode($subject) . '?=';
		return $str;
	}
	
    
    //-------- Main ----------//
    $runMaxCnt = 10;
    while($runMaxCnt > 0){  //執行10次後將daemon 消毀
        echo date('Y/m/d H:i:s')."\n";
        $data = getQueueImport();
        if(!empty($data)){
            foreach($data as $v){
                print_r($d);
                /**
                    [id] => 8
                    [src_school_id] => 10001
                    [tar_school_id] => 10002
                    [src_course_id] => 10000006
                    [tar_course_id] => 10000003
                    [import_params] => course_intro,course_path,course_files,course_board,subject_board,chatroom,homework,exam,questionnaire
                    [state] => wait
                    [reg_time] => 2014-07-01 21:53:27
                    [finish_time] => 
                    [err_msg] => 
                    [owner] => 
                **/
                //get data
                $sysConn->Execute('use '.sysDBname);
                $sysConn->Execute("update CO_course_install set state='running' where id={$v['id']}");
                $course_elements = explode(',',$v['import_params']);
                
                //import course
                $result = processImport($v);
                $sysConn->Execute('use '.sysDBname);
                $sysConn->Execute("update CO_course_install set state='{$result['state']}',err_msg='{$result['err_msg']}',finish_time=NOW() where id={$v['id']}");
                
            }
        }
        sleep(10);   //10 sec check
        $runMaxCnt--;
    }
?>