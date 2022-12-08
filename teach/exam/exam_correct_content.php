<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/04/11                                                            *
	 *		work for  : 取得某考生對某次測驗的答案卷                                          *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	define('QTI_DISPLAY_ANSWER',   true);   // 定義常數；顯示標準答案
	define('QTI_DISPLAY_OUTCOME',  true);   // 定義常數；顯示批改結果及得分
	define('QTI_DISPLAY_RESPONSE', true);   // 定義常數；顯示學生答案
	require_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
	require_once(sysDocumentRoot . '/lib/attach_link.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/quota.php');
	if (QTI_which == 'homework') include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');

	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600300100';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700300100';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800300100';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	$curr_mode = $ADODB_FETCH_MODE;
	$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
	if ($topDir == 'academic')
		$isQuotaExceed = getRemainQuota($sysSession->school_id) > 0 ? 0 : 1;
	else
		$isQuotaExceed = getRemainQuota($sysSession->course_id) > 0 ? 0 : 1;
	$ADODB_FETCH_MODE = $curr_mode;



	if (!isset($_SERVER['argv'][0])) {	// 檢查 ticket 是否存在
	   wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
	   die('Access denied.');
	}
	$ticket_head = sysTicketSeed . $course_id . $_SERVER['argv'][1] . $_SERVER['argv'][2] . $_SERVER['argv'][3];
	if (md5($ticket_head) != $_SERVER['argv'][0]) die('Fake ticket.');			// 檢查 ticket

	$keep = $ADODB_FETCH_MODE;
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	if (QTI_which == 'homework' && isAssignmentForGroup($_SERVER['argv'][1]))
	{
        $sqls = 'SELECT D.team_id, D.group_id, R.* ' .
				'FROM WM_student_div AS D ' .
				'INNER JOIN WM_qti_homework_result AS R ON D.username = R.examinee ' .
				'WHERE D.course_id =' . $sysSession->course_id .
				' AND D.group_id =' . $_SERVER['argv'][2] .
				' AND D.team_id =' . $_SERVER['argv'][4] .
				' AND R.exam_id =' . $_SERVER['argv'][1];
	    $fields = $sysConn->GetRow($sqls);
	    $isGroupingAssignment = $_SERVER['argv'][4];
            $username = $_SERVER['argv'][2];
	    $_SERVER['argv'][2] = $fields['examinee'];
	}
	else
	{
		$fields = dbGetStSr('WM_qti_' . QTI_which . '_result',
		                    'status, score, comment, content, ref_url',
		                    "exam_id={$_SERVER['argv'][1]} and examinee='{$_SERVER['argv'][2]}' and time_id={$_SERVER['argv'][3]}",
						    ADODB_FETCH_ASSOC);
        $isGroupingAssignment = false;
	}
    $ADODB_FETCH_MODE = $keep;

	$ticket = md5(sysTicketSeed . $_COOKIE['idx'] . $_SERVER['argv'][1] . $_SERVER['argv'][2] . $_SERVER['argv'][3]);

	if ($topDir == 'academic')
		$saved_path = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09u/%s/',
		  					 $sysSession->school_id,
		  					 QTI_which,
		  					 $_SERVER['argv'][1],
		  					 $_SERVER['argv'][2]);
	else
		$saved_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/',
		  					 $sysSession->school_id,
		  					 $sysSession->course_id,
		  					 QTI_which,
		  					 $_SERVER['argv'][1],
		  					 $_SERVER['argv'][2]);

	$saved_uri = substr($saved_path, strlen(sysDocumentRoot));

	if (empty($fields['content']))
	{
       $xml_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/C/%09u/%s/',
		  					 $sysSession->school_id,
		  					 $sysSession->course_id,
		  					 QTI_which,
		  					 $_SERVER['argv'][1],
		  					 $_SERVER['argv'][2]);
       $file = 	$_SERVER['argv'][3].'.xml';	  	

       $full_path = $xml_path.$file;

       if (is_file($full_path)) {
           $fields['content'] = file_get_contents($full_path);
       } else {
	       wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 2, 'auto', $_SERVER['PHP_SELF'], 'Reading ' . QTI_which . ' content faliure.');
	       die('Reading ' . QTI_which . ' content faliure.');
       }
	}

	$fields['content'] = str_replace('<item xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd" xmlns:wm="http://www.sun.net.tw/WisdomMaster" ', '<item ', $fields['content']);
	$fields['content'] = str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $fields['content']);
                                $fields['content'] = str_replace(array("\n", "\r"),
                                                                  array('', ''),
                                                                  $fields['content']
                                                                 );
			  
                                
	$fields['content'] = preg_replace('/&lt;body&gt;/', '', $fields['content']);
	$fields['content'] = preg_replace('/&lt;\/body&gt;/', '', $fields['content']);           
                                
	if(!$dom = domxml_open_mem($fields['content'])) {
		wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 3, 'auto', $_SERVER['PHP_SELF'], 'Error while parsing the document.');
		die('Error while parsing the document.');
	}
	$ctx = xpath_new_context($dom);
	$root = $dom->document_element();

	// 開始 output HTML
	showXHTML_head_B('Answer detail');
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/wm.css");

	  $msgQuota = str_replace('%TYPE%', $MSG[$topDir == 'academic' ? 'school' : 'course'][$sysSession->lang], $MSG['quota_exceed'][$sysSession->lang]);
	  $scr = <<< EOB
function scoreAdd(num1, num2)
{
	var r1, r2, m;
	try{ r1 = num1.toString().split(".")[1].length; } catch(e){ r1 = 0; }
	try{ r2 = num2.toString().split(".")[1].length; } catch(e){ r2 = 0; }
	m = Math.pow(10,Math.max(r1,r2));
	return (num1 * m + num2 * m) / m;
}
function send_save(){
	var forms = document.getElementsByTagName('form');
	var scores = forms[0].getElementsByTagName('input');
	var total_score = 0.0;

	if(forms[0].total_score.value==0){
		for(var i=0; i< scores.length; i++){
			if (scores[i].name.indexOf('item_scores[') === 0) total_score = scoreAdd(total_score, parseFloat(scores[i].value));
		}
		forms[0].total_score.value = total_score;
	}
	forms[0].submit();
}
function reload_score(){
	var forms = document.getElementsByTagName('form');
	var scores = forms[0].getElementsByTagName('input');
	var total_score = 0.0;

	for(var i=0; i< scores.length; i++){
		if (scores[i].name.indexOf('item_scores[') === 0) total_score = scoreAdd(total_score, parseFloat(scores[i].value));
	}
	forms[0].total_score.value = total_score;
}

window.onload=function()
{
	var score_input = document.getElementsByTagName('form')[0];
	if (score_input.total_score.value == '' || score_input.total_score.value == 0) reload_score();
	if ({$isQuotaExceed}) alert('{$msgQuota}');
};

EOB;
	  showXHTML_script('inline', $scr);
	showXHTML_head_E();
	showXHTML_body_B();
	  showXHTML_form_B('method="POST" action="exam_correct_content1.php" enctype="multipart/form-data"');
	    //showXHTML_table_B('border="0" cellpadding="3" cellspacing="0" width="800" style="border-collapse: collapse; border-style: solid; border-width: 1px; border-color: red"');
	    showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable" style="width:1000;"');

		  showXHTML_tr_B('class="bg03 font01" ');
		  	list($f, $l) = dbGetStSr('WM_user_account', 'first_name, last_name', 'username="' . $_SERVER['argv'][2] . '"', ADODB_FETCH_NUM);
		  	showXHTML_td('colspan="2"', sprintf('(%s) %s', $_SERVER['argv'][2], checkRealname($f, $l)));
		  showXHTML_tr_E();

	      showXHTML_tr_B('class="cssTrOdd"');
	        showXHTML_td('align="right" width="80"', $MSG['total_score'][$sysSession->lang]);
	        showXHTML_td_B();
	          showXHTML_input('text',   'total_score', $fields['score'], '', 'maxlength="5" size="10" class="cssInput"');
	          showXHTML_input('button', '', $MSG['re-count'][$sysSession->lang], '', 'onclick="reload_score();" class="cssBtn"');
	          echo '<div style="color: red">', $MSG['re-count_note'][$sysSession->lang], '</div>';
	        showXHTML_td_E();
	      showXHTML_tr_E();

	      showXHTML_tr_B('class="cssTrEvn"');
	        showXHTML_td('align="right"', $MSG['comments'][$sysSession->lang]);
	        showXHTML_td_B();
	          showXHTML_input('textarea', 'comment', $fields['comment'], '', 'rows="6" cols="60" class="cssInput"');
	        showXHTML_td_E();
	      showXHTML_tr_E();

	      showXHTML_tr_B('class="cssTrOdd"');
	        showXHTML_td('align="right"', $MSG['reference_url'][$sysSession->lang]);
	        showXHTML_td_B();
	          showXHTML_input('text', 'refurl', $fields['ref_url'], '', 'maxlength="80" size="40" class="cssInput"');
	        showXHTML_td_E();
	      showXHTML_tr_E();

	      showXHTML_tr_B('class="cssTrEvn"');
	        showXHTML_td('align="right"', $MSG['reference_file'][$sysSession->lang]);
	        showXHTML_td_B();

	        if($username!=''){
	        	$RS1=dbGetStMr('WM_student_div','username','group_id = '.$username.' and course_id = '.$sysSession->course_id .' and team_id = '.$_SERVER['argv'][4] );
	        	
	        	
		        while(!$RS1->EOF)
				{
						$g_username = trim($RS1->fields['username']);
	                                        // echo $username . '<BR>';
						$ref_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/ref/%09u/',
									 $sysSession->school_id,
									 $sysSession->course_id,
									 QTI_which,
									 $_SERVER['argv'][1],
									 $g_username,$_SERVER['argv'][3]);
						 $ref_uri = substr($ref_path, strlen(sysDocumentRoot));
					if ($d = @dir($ref_path))
					{
						while (false !== ($entry = $d->read()))
						{
	
						if (is_file($ref_path . $entry))
						{
							showXHTML_input('checkbox', 'rmfiles[]', $g_username.'0_0'.rawurlencode($entry));
							echo genFileLink($ref_uri, $entry);
						}
						}
						$d->close();
					}
					 $RS1->MoveNext();
				}
	        	
	        } else {
	        
		$save_ref_path = sprintf($saved_path . 'ref/%09u/', $_SERVER['argv'][3]);
		$save_ref_uri = substr($save_ref_path, strlen(sysDocumentRoot));
		if ($d = @dir($save_ref_path))
		{
			while (false !== ($entry = $d->read()))
			{
				if (is_file($save_ref_path . $entry))
				{
					showXHTML_input('checkbox', 'rmfiles[]', $_SERVER['argv'][2].'0_0'.rawurlencode($entry));
					echo genFileLink($save_ref_uri, $entry);
				}
			}
			$d->close();
		}
	        }

	          showXHTML_input('file', 'reffile', '', '', 'size="30" class="cssInput"' . ($isQuotaExceed ? ' disabled' : ''));
	        showXHTML_td_E();
	      showXHTML_tr_E();

			$css = QTI_which == 'homework' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
	      showXHTML_tr_B($css . (QTI_which=='homework' ? '' : ' style="display:none"'));
	        showXHTML_td('align="right"', $MSG['exemplary'][$sysSession->lang]);
        showXHTML_td_B();
	          showXHTML_input('checkbox', 'exemplary', 1, '', 'class="cssInput"' . ($fields['status']=='publish' ? ' checked' : ''));
	          echo $MSG['exemplary_hint'][$sysSession->lang];
	        showXHTML_td_E();
	      showXHTML_tr_E();

			$css = $css == 'class="cssTrOdd"' ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
	      showXHTML_tr_B($css);
	        showXHTML_td_B('colspan="2" align="center"');
	         showXHTML_input('button', '', $MSG['save_correct'][$sysSession->lang], '', ' class="cssBtn" onclick="send_save();"');
	          showXHTML_input('hidden', 'examinee', $_SERVER['argv'][2]);
	          showXHTML_input('hidden', 'exam_id',  $_SERVER['argv'][1]);
	          showXHTML_input('hidden', 'time_id',  $_SERVER['argv'][3]);
	          showXHTML_input('hidden', 'ticket',   $ticket);
	          if ($isGroupingAssignment) showXHTML_input('hidden', 'team_id', $fields['team_id']);
	        showXHTML_td_E();
	      showXHTML_tr_E();

	    showXHTML_table_E();
		/***   BUG 027621	 begin mars 2013/01/21          **/
        if (QTI_which == 'homework') {
	    echo '<div style="height: 1em;"></div>';
		if($username!=''){
			showXHTML_tabFrame_B(array(
		        array(
		            $MSG['uploaded_files'][$sysSession->lang]
		        )
		    ));
			$RS=dbGetStMr('WM_student_div','username','group_id = '.$username.' and course_id = '.$sysSession->course_id .' and team_id = '.$_SERVER['argv'][4] );
			showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable" style="width:1000;"');
    
		    showXHTML_tr_B('class="cssTrHead"');
		    showXHTML_td('class="cssTd" align="center"', $MSG['serial_no'][$sysSession->lang]);
		    if ($isGroupingAssignment) {
		        showXHTML_td('class="cssTd" align="center"', $MSG['uploaded_man'][$sysSession->lang]);
		    }
		    showXHTML_td('class="cssTd" align="center"', $MSG['uploaded_time'][$sysSession->lang]);
		    showXHTML_td('class="cssTd"', $MSG['uploaded_filename'][$sysSession->lang]);
		    showXHTML_td('class="cssTd" align="right"', $MSG['uploaded_filesize'][$sysSession->lang]);
		
		    showXHTML_tr_E();
	    
	        $i = 1;
			 while(!$RS->EOF)
			{
					$username = trim($RS->fields['username']);
                                        // echo $username . '<BR>';
					$saved_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/',
								 $sysSession->school_id,
								 $sysSession->course_id,
								 QTI_which,
								 $_SERVER['argv'][1],
								 $username);
					 $saved_uri = substr($saved_path, strlen(sysDocumentRoot));
				if ($d = @dir($saved_path))
				{
					while (false !== ($entry = $d->read()))
					{

						if (is_file($saved_path . $entry))
						{
							showXHTML_tr_B('class="cssTrEvn"');
		                    // 序號
		                    showXHTML_td('class="cssTd" align="center"', $i);
		                    
		                    //                                    // 上傳者
		                    $homework_uri = substr($v, strlen(sysDocumentRoot));
		                    if ($isGroupingAssignment) {
		                    	$RS1       = dbGetStSr('WM_user_account', 'first_name, last_name', "username='" . $username . "'", ADODB_FETCH_ASSOC);
		                        $realname = checkRealname($RS1['first_name'], $RS1['last_name']);
		                        showXHTML_td('class="cssTd" align="left"', $realname . ' (' . $username . ')');
		                    }
		                    
		                    // 上傳時間
		                    showXHTML_td('class="cssTd" align="center"', date("Y/m/d H:i:s", filemtime(sysDocumentRoot . $saved_uri . $entry)));
		                    
		                    // 上傳檔名
		                    $filesize = @filesize(sysDocumentRoot . $saved_uri . $entry);
		                    showXHTML_td('class="cssTd"', sprintf('<a target="_blank" href="%s" class="cssAnchor">%s</a>', $saved_uri . $entry, $entry) . (($filesize === 0) ? '&nbsp;<span style="color: red;">(' . $MSG['file_content_blank'][$sysSession->lang] . ')</span>' : ''));
		                    if ($filesize === 0) {
		                        $filesizeColor = 'red';
		                    } else {
		                        $filesizeColor = 'black';
		                    }
		                    
		                    // 檔案大小
		                    showXHTML_td('class="cssTd" align="right"', '<span style="color: ' . $filesizeColor . ';">' . FileSizeConvert($filesize) . '</span>');

		                    showXHTML_tr_E();
		                    $i++;
						}
					}
					$d->close();
				}
				 $RS->MoveNext();
			}
			showXHTML_table_E();
		showXHTML_tabFrame_E();	
		/***   BUG 027621	 end mars 2013/01/21          **/
		}else{
			if ($d = @dir($saved_path))
			{
				showXHTML_tabFrame_B(array(
			        array(
			            $MSG['uploaded_files'][$sysSession->lang]
			        )
			    ));
				
				showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable" style="width:1000;"');
			    showXHTML_tr_B('class="cssTrHead"');
			    showXHTML_td('class="cssTd" align="center"', $MSG['serial_no'][$sysSession->lang]);
			    showXHTML_td('class="cssTd" align="center"', $MSG['uploaded_time'][$sysSession->lang]);
			    showXHTML_td('class="cssTd"', $MSG['uploaded_filename'][$sysSession->lang]);
			    showXHTML_td('class="cssTd" align="right"', $MSG['uploaded_filesize'][$sysSession->lang]);
			    showXHTML_tr_E();
			    $i = 1;
			    $saved_uri = substr($saved_path, strlen(sysDocumentRoot));
				while (false !== ($entry = $d->read()))
				{
					if (is_file($saved_path . $entry))
					{
						showXHTML_tr_B('class="cssTrEvn"');
		                    // 序號
		                    showXHTML_td('class="cssTd" align="center"', $i);
		                    
		                    // 上傳時間
		                    showXHTML_td('class="cssTd" align="center"', date("Y/m/d H:i:s", filemtime(sysDocumentRoot . $saved_uri . $entry)));
		                    
		                    // 上傳檔名
		                    $filesize = @filesize(sysDocumentRoot . $saved_uri . $entry);
		                    showXHTML_td('class="cssTd"', sprintf('<a target="_blank" href="%s" class="cssAnchor">%s</a>', $saved_uri . $entry, $entry) . (($filesize === 0) ? '&nbsp;<span style="color: red;">(' . $MSG['file_content_blank'][$sysSession->lang] . ')</span>' : ''));
		                    if ($filesize === 0) {
		                        $filesizeColor = 'red';
		                    } else {
		                        $filesizeColor = 'black';
		                    }
		                    
		                    // 檔案大小
		                    showXHTML_td('class="cssTd" align="right"', '<span style="color: ' . $filesizeColor . ';">' . FileSizeConvert($filesize) . '</span>');

		                showXHTML_tr_E();
		                    $i++;
					}
				}
				$d->close();
				showXHTML_table_E();
				showXHTML_tabFrame_E();
			}
		}
        }
	echo '<div style="height: 1em;"></div>';		
	// 準備好夾檔
	if ($xmlDoc = domxml_open_mem($fields['content'])) {
		$ids = array();
		$nodes = $xmlDoc->get_elements_by_tagname('item');
		foreach($nodes as $item)
			$ids[] = $item->get_attribute('ident');
		if ($ids) {
			$idents = 'ident in ("' . implode('","', $ids) . '")';
			$a = $sysConn->GetAssoc('select ident,attach from WM_qti_' . QTI_which . '_item where ' . $idents);
			foreach($a as $k => $v)
				if (preg_match('/^a:[0-9]+:{/', $v))
					$GLOBALS['attachments'][$k] = unserialize($v);
		}
	}
	$examinee = $_SERVER['argv'][2];
	$exam_id  = $_SERVER['argv'][1];
	$time_id  = $_SERVER['argv'][3];
	ob_start();
        
        // 產生試卷
        // 內嵌在frame中
        //define('SINGLE', '0');
	parseQuestestinterop($dom->dump_mem(false));
	$exam_content = ob_get_contents();
	ob_end_clean();
	echo preg_replace(array('/<form .*<!/isU', '!</form>!i'),
	                array('<!', ''),
					$exam_content);

	  showXHTML_form_E();
	showXHTML_body_E();
?>
