<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/exam_teach.php');
	require_once(sysDocumentRoot . '/lib/attach_link.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='1600400300';
	$sysSession->restore();
	if (!aclVerifyPermission(1600400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	define('QTI_DISPLAY_ANSWER',   true);
	define('QTI_DISPLAY_OUTCOME',  true);
	define('QTI_DISPLAY_RESPONSE', true);
	//require_once(sysDocumentRoot . '/teach/exam/exam_preview.php');
	require_once(sysDocumentRoot . '/teach/exam/m_exam_preview.php');
	header('Content-type: text/html'); // 因為 exam_preview.php 會輸出 text/xml header 所以在此糾正回來 #1239

	showXHTML_head_B('');
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		showXHTML_CSS('include', "/theme/default/bootstrap/css/bootstrap.min.css");
		showXHTML_CSS('include', "/theme/default/bootstrap//css/bootstrap-responsive.css");
		showXHTML_CSS('include', "/public/css/exam/m_exam_start.css");
		showXHTML_CSS('include', "/public/css/exam/m_view_result.css");
	    showXHTML_CSS('include', "/public/css/common.css");
		showXHTML_CSS('include', "/public/css/qti_list.css");
		showXHTML_script('include', '/lib/jquery/jquery.min.js');
	showXHTML_head_E();
	showXHTML_body_B();

	if ($_SERVER['argv'][2] != md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . $_COOKIE['idx']))
	{
	   	wmSysLog($sysSession->cur_func, 1, 'auto', $_SERVER['PHP_SELF'], 'Fake ticket');
		die('Fake ticket !');
	}

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;
	// 取得最小的time_id 
    $tid = dbGetStSr('WM_qti_exam_result','min(time_id) as min ',sprintf('exam_id=%u and examinee="%s"', $_SERVER['argv'][0], $sysSession->username), ADODB_FETCH_ASSOC);
	$minid = $tid['min'];
	$a = dbGetStSr('WM_qti_exam_result', 'score, comment, content, ref_url', sprintf('exam_id=%u and examinee="%s" and time_id=%d', $_SERVER['argv'][0], $sysSession->username, $_SERVER['argv'][1]), ADODB_FETCH_NUM);

	list($score, $comment, $content, $ref_url) = $a;
	// 如果$content是空的，表示沒有time_id=1的測驗分數 => 被教師刪掉第一次的測驗結果 
	// $content便會是最小的time_id的測驗結果 
    if (empty($content))
       list($score, $comment, $content, $ref_url) = dbGetStSr('WM_qti_exam_result', 'score, comment, content, ref_url', sprintf('exam_id=%u and examinee="%s" and time_id=%d', $_SERVER['argv'][0], $sysSession->username, $minid), ADODB_FETCH_NUM);

	if ($topDir == 'academic')
		$save_path = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09u/%s/ref/%09u/',
		  					 $sysSession->school_id,
		  					 QTI_which,
		  					 $_SERVER['argv'][0],
		  					 $sysSession->username,
		  					 $_SERVER['argv'][1]);
	else
		$save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/ref/%09u/',
		  					 $sysSession->school_id,
		  					 $sysSession->course_id,
		  					 QTI_which,
		  					 $_SERVER['argv'][0],
		  					 $sysSession->username,
		  					 $_SERVER['argv'][1]);

	$save_uri = substr($save_path, strlen(sysDocumentRoot));
	$ref_files = '';
	if ($d = @dir($save_path))
	{
		while (false !== ($entry = $d->read()))
		{
			if (is_file($save_path . $entry))
			{
				$ref_files .= genFileLink($save_uri, $entry);
			}
		}
		$d->close();
	}
	if ($ref_files || $ref_url || $comment)
	{   
            echo '<div style="margin: auto; width:886px;">';
		showXHTML_tabFrame_B(array(array($MSG['ref_data'][$sysSession->lang])));
			showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="886" style="border-collapse: collapse" class="box01"');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('', $MSG['reference_file'][$sysSession->lang]);
					showXHTML_td('', $ref_files);
				showXHTML_tr_E();

				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td('', $MSG['reference_url'][$sysSession->lang]);
					showXHTML_td('', sprintf('<a href="%s" target="_blank">%s</a>', $ref_url, $ref_url));
				showXHTML_tr_E();
				
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('', $MSG['tech_comments'][$sysSession->lang]);
					showXHTML_td('', $comment);
				showXHTML_tr_E();
				
			showXHTML_table_E();
		showXHTML_tabFrame_E();
                echo '</div>';
	}

	if (QTI_which == 'exam')
	{
		//showXHTML_tabFrame_B(array(array($MSG['tab_exam_times'][$sysSession->lang])));
			showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="760" class="box01" style="display:none;" ');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('');
					  echo $MSG['title_exam_time1'][$sysSession->lang];
					  $t = dbGetCol('WM_qti_exam_result', 'time_id', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}' order by time_id");
					  $tt = array();
					  foreach($t as $v)
					  {
					  	$tt[$v] = sprintf('%d+%d+%s', $_SERVER['argv'][0], $v, md5(sysTicketSeed . $_SERVER['argv'][0] . $v . $_COOKIE['idx']));
					  	if ($v == $_SERVER['argv'][1]) $x = $tt[$v];
					  }
					  showXHTML_input('select', '', array_flip($tt), $x, 'class="cssInput" onchange="location.replace(\'m_view_result.php?\' +this.value)"');
					  echo $MSG['title_exam_time2'][$sysSession->lang];
					showXHTML_td_E('');
				showXHTML_tr_E();
			showXHTML_table_E();
		//showXHTML_tabFrame_E();
		//
		// 及格標準
		$ec = $sysConn->GetOne("select content from WM_qti_exam_test where exam_id={$_SERVER['argv'][0]}");
		if (preg_match('/\bthreshold_score="([^"]*)"/', $ec, $regs))
			$threshold_score = ($regs[1] == '') ? 'false' : floatval($regs[1]);
		else
			$threshold_score = 'false';
		// 及格標準 end

		// 觀看成績
		echo <<<EOB
		<br>
		<div style='background-color: #ffffff;width: 886px;margin: 0 auto;'>
			<div style='display: inline-block;'>
				<img src="/theme/default/learn_mooc/test_result_icon.png" style="">
				<span style="font-size:17.5px;vertical-align:middle;"><strong>{$MSG['watch_results'][$sysSession->lang]}</strong></span>
			</div>
			<div style='display: inline-block; float: right; padding-top: 8px; padding-right: 14px;'>
EOB;
		echo $MSG['title_exam_time1'][$sysSession->lang];
		$t = dbGetCol('WM_qti_exam_result', 'time_id', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}' order by time_id");
		$tt = array();
		foreach($t as $v)
		{
			$tt[$v] = sprintf('%d+%d+%s', $_SERVER['argv'][0], $v, md5(sysTicketSeed . $_SERVER['argv'][0] . $v . $_COOKIE['idx']));
			if ($v == $_SERVER['argv'][1]) $x = $tt[$v];
		}
		showXHTML_input('select', '', array_flip($tt), $x, ' style="vertical-align: -webkit-baseline-middle;" onchange="location.replace(\'m_view_result.php?\' +this.value)"');
		echo $MSG['title_exam_time2'][$sysSession->lang];
		echo <<<EOB
			</div>
		</div>
		<div style='background-color: #F8F8F8 ;width: 886px;margin: 0 auto; padding-bottom: 12px;'>
EOB;
		// 考試通過圖示，顯示資訊

		// 及格標準logic
		$pass_level = ($threshold_score === false)?$MSG['title_noblock'][$sysSession->lang]:$threshold_score;
	
		if($threshold_score!= false && $score >= $pass_level){
			$exam_pass_img_url = "/theme/default/learn_mooc/test_result_pass.png";
                        $isPass = 'Pass';
		} else {
			$exam_pass_img_url = "/theme/default/learn_mooc/test_result_fail.png";
                        $isPass = 'Fail';
		}
		// 考試分數
		$exam_score = number_format($score, 2);
		
		echo <<<EOB

                <div style="display: inline-block; margin-bottom: 1em;">
                    <img src="{$exam_pass_img_url}" style='padding-left:56px;'>
                    <div style="color: #FFFFFF; font-size: 0.9em; position: relative; top: -1.9em; left: -3.4em; display: inline-block; font-weight: bold; font-family: Arial, Helvetica, sans-serif;">{$isPass}</div>
                    <br />
                </div>
                <div style="display: inline-block; vertical-align: bottom; padding-left: 30px;">
                    <strong>
                        <span>
                            <font color="#353535" style="font-size: 18px;">
                            {$MSG['earn'][$sysSession->lang]}
                            </font>
                            <span style="font-size: 60px;">{$exam_score}</span>
                            <!--<span style="font-size: 60px;">25.00</span>-->
                        </span>
                        <span><font color="#707070" style="font-size: 13px;">{$MSG['score_depend'][$sysSession->lang]}</font></span>
                    </strong>
                </div>
                <div style='padding-left: 45px; padding-bottom: 10px;'>
                    <!-- <a href="/learn/exam/exam_statistics_result.php" target=_blank> -->
                    <!-- <font color="#f3800f" style="font-size: 16px; padding-left: 10px; cursor:pointer" id='show_btn'> {$MSG['see_my_ankings'][$sysSession->lang]}></font> -->
                </div>
                <div id='show_range' style='display:none;'>
                    <img src='/theme/default/learn_mooc/exam_statistics_result.PNG' style="width: 100%;">
                </div>
		<script>
			$("#show_btn").click(function(){
			    $("#show_range").slideToggle('', function(){});
			});
			
			<!--
			// 置換圖案，用在考試結果，詳細解答的收合切換效果
			-->
			$(document).ready(function () {
				$(".show_ans_btn").click(function(){
					$(this).parent().find(".show_ans").slideToggle('', function(){});
					if($(this).parent().find(".imgClickAndChange").attr('src') == "/theme/default/learn_mooc/icon_a_close.png"){
						$(this).parent().find(".imgClickAndChange").attr('src', "/theme/default/learn_mooc/icon_a_open.png");
					} else {
						$(this).parent().find(".imgClickAndChange").attr('src', "/theme/default/learn_mooc/icon_a_close.png");
					}
				});
			});
		</script>
EOB;
		echo <<<EOB
		</div>
		<br>
EOB;

	}

	if (empty($content)) {
		$errMsg = $sysConn->ErrorMsg();
	   wmSysLog($sysSession->cur_func, 2, 'auto', $_SERVER['PHP_SELF'], $errMsg);
	   die($errMsg);
	}

	// 準備好夾檔
	if ($xmlDoc = domxml_open_mem($content)) {		
		$ids = array();
		$nodes = $xmlDoc->get_elements_by_tagname('item');	
		foreach($nodes as $item)		
			$ids[] = $item->get_attribute('ident');			
		if ($ids) {
			$idents = 'ident in ("' . implode('","', $ids) . '")';
			$a = dbGetAssoc('WM_qti_' . QTI_which . '_item', 'ident, attach', $idents);
			foreach($a as $k => $v)
				if (preg_match('/^a:[0-9]+:{/', $v))
					$GLOBALS['attachments'][$k] = unserialize($v);
		}
	}
	$save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/Q/',
		  					 $sysSession->school_id,
		  					 $sysSession->course_id,
		  					 QTI_which);
	$save_uri = substr($save_path, strlen(sysDocumentRoot));
	  ob_start();
 	  parseQuestestinterop($content);
 	  $exam_content = ob_get_contents();
 	  ob_end_clean();
	echo preg_replace('/<form [^>]* action="save_answer.php".*<!/isU', '<form style="display: inline;"><!', $exam_content);

	if (QTI_which == 'exam')
	{
		// 及格標準
		$ec = $sysConn->GetOne("select content from WM_qti_exam_test where exam_id={$_SERVER['argv'][0]}");
		if (preg_match('/\bthreshold_score="([^"]*)"/', $ec, $regs))
			$threshold_score = ($regs[1] == '') ? 'false' : floatval($regs[1]);
		else
			$threshold_score = 'false';

		// MSG 評量結果 =
		$m1 = $MSG['title_exam_result'][$sysSession->lang];
		// MSG 未設定
		$m2 = $MSG['title_noblock'][$sysSession->lang];
		// MSG 及格
		$m3 = '<span style="color: green">'.$MSG['title_pass'][$sysSession->lang].'</span>';
		// MSG 不及格
		$m4 = '<span style="color: red">'.$MSG['title_nopass'][$sysSession->lang].'</span>';
		// MSG 及格標準 =
		$m5 = $MSG['title_standard'][$sysSession->lang];
		showXHTML_script('inline', "
var correct_score = '{$score}';
var ss = document.getElementsByTagName('input');
var total_score = 0.0;
var threshold_score = {$threshold_score};

if (correct_score == '')
{
	for(var i=0; i<ss.length; i++)
	{
		if (ss[i].type=='text' && ss[i].name.indexOf('item_scores[') === 0) total_score += parseFloat(ss[i].value);
	}
}
else
    total_score = parseFloat(correct_score);

var tb = document.getElementsByTagName('table')[2];
tb.insertRow(0);
tb.rows[0].insertCell(0);
tb.rows[0].cells[0].colSpan = '4';
tb.rows[0].cells[0].className = 'cssTrHead';
tb.rows[0].cells[0].innerHTML = '{$MSG['total_score'][$sysSession->lang]} = ' +
								total_score + '<br>{$m5}' +
								(threshold_score === false ? '$m2' : threshold_score) + '<br>{$m1}' +
								(threshold_score === false ? 'N/A' : (threshold_score <= total_score ? '$m3' : '$m4')) +
								'<br>{$MSG['score_depend'][$sysSession->lang]}';

");
	}
	showXHTML_body_E();
?>
