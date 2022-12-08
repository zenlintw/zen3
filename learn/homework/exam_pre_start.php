<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/Mobile_Detect.php');
    // 判斷使用者是否使用行動裝置
    $detect = new Mobile_Detect;
    $profile['isPhoneDevice'] = false;
    $profile['isPhoneDevice'] = ($detect->isMobile() && !$detect->isTablet());
    require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
    require_once(sysDocumentRoot . '/lang/' . QTI_which . '_learn.php');

	if ($_SERVER['argc'] < 3) die('Arguments Error!.');		// 沒有三個參數則執行失敗
	if (!ereg('^[0-9]+$', $_SERVER['argv'][0]) ||			// 檢查 exam_id 格式
		!ereg('^[0-9]+$', $_SERVER['argv'][1]) ||			// 檢查 times_id 格式
	    !eregi('^[a-z0-9]{32}$', $_SERVER['argv'][2])		// 檢查 ticket 格式
	   ) {
        wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'classroom', $_SERVER['PHP_SELF'], 'Argument format incorrect!');
		die('Argument format incorrect.');
	}
	$ticket = md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . $_COOKIE['idx']);
	if ($ticket != $_SERVER['argv'][2]) { // 檢查 ticket 正確與否
	   wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 2, 'classroom', $_SERVER['PHP_SELF'], 'Fake Ticket!');
	   die('Fake Ticket !');
	}
	
	if (isset($_SERVER['argv'][4]) && $_SERVER['argv'][4] == 'phone') {
        $profile['isPhoneDevice']=true;
    }


	// 由資料庫取出資料
	$RS = dbGetStSr('WM_qti_' . QTI_which . '_test', '*', 'exam_id=' . $_SERVER['argv'][0], ADODB_FETCH_ASSOC);
	if (strpos($RS['title'], 'a:') === 0)
		$locale_title = getCaption($RS['title']);
	else
		$locale_title[$sysSession->lang] = $RS['title'];

	if ($RS['content'])	{
		$total_items = substr_count($RS['content'], '<item ');			// 共幾題
		preg_match_all('/ score="([0-9.]+)">/', $RS['content'], $out, PREG_PATTERN_ORDER); // 計算總分
		if (count($out[1]) > $total_items)
			$total_score = array_sum(array_splice($out[1], 0, $total_items));
		else
			$total_score = isset($out[1]) ? array_sum($out[1]) : 0;
	}

	$school_q = ($RS['course_id'] && $RS['course_id'] == $sysSession->school_id) ? '?school' : '';

	$begin_time = $MSG['from'][$sysSession->lang] . (strpos($RS['begin_time'], '0000') === 0 ? $MSG['now'][$sysSession->lang]     : date('Y-m-d H:i', strtotime($RS['begin_time'])) );
	$close_time = $MSG['to2'][$sysSession->lang]  . (strpos($RS['close_time'], '9999') === 0 ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', strtotime($RS['close_time'])) );
        
        // 補繳起迄
	$close_time2 = $MSG['from'][$sysSession->lang]  . (strpos($RS['close_time'], '9999') === 0 ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', strtotime($RS['close_time']) + 60) );
	$delay_time = $MSG['to2'][$sysSession->lang]  . (strpos($RS['delay_time'], '9999') === 0 ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', strtotime($RS['delay_time'])) );

	$ary_announce = array('never'       => $MSG['score_publish0'][$sysSession->lang],
					   	  'now'         => $MSG['score_publish1'][$sysSession->lang],
					      'close_time'  => $MSG['score_publish2'][$sysSession->lang],
					      'user_define' => $MSG['score_publish3'][$sysSession->lang]
					     );
	$announce_time = ($RS['announce_type'] == 'user_define' ? $RS['announce_time'] : $ary_announce[$RS['announce_type']]);
        
        // 開放補繳與補繳起迄（類別為作業 且 有設定補繳期限）
        if (QTI_which === 'homework' && isset($RS['delay_time']) === true && $RS['delay_time']!== '0000-00-00 00:00:00' && $RS['delay_time'] !== '9999-12-31 00:00:00') {
            $isPayback = $MSG['paybacknote'][$sysSession->lang];
            $paybackDuration = $close_time2 . ' ' . $delay_time;
        } else {
            $isPayback = $MSG['notopen'][$sysSession->lang];
            $paybackDuration = $MSG['notopen'][$sysSession->lang];
        }
        
	$display_words = (QTI_which == 'homework' ?
					    array(
					    	'exam_name' => array($MSG['exam_name'][$sysSession->lang]                 , $locale_title[$sysSession->lang]),
					    	'total_score' => array($MSG['total_score'][$sysSession->lang]               , $total_score.$MSG['minute'][$sysSession->lang]),
					    	'exam_percent' => array($MSG['exam_percent'][$sysSession->lang]              , $RS['percent'] . '%'),
					    	'item_total_amount' => array($MSG['item_total_amount'][$sysSession->lang]         , $total_items.$MSG['item'][$sysSession->lang]),
					    	'enable_duration' => array($MSG['enable_duration'][$sysSession->lang]           , $begin_time . ' ' . $close_time),
					    	'modifiable' => array($MSG['modifiable'][$sysSession->lang]                , ($RS['modifiable'] == 'Y' ? $MSG['can_modify'][$sysSession->lang] : $MSG['cannot_modify'][$sysSession->lang])),
					    	'ispayback' => array($MSG['ispayback'][$sysSession->lang]                , $isPayback),
					    	'payback_duration' => array($MSG['payback_duration'][$sysSession->lang]           , $paybackDuration),
					        'isupload' => array($MSG['response by attachment'][$sysSession->lang]           , (strpos($RS['setting'], 'upload') !== FALSE) ? $MSG['true'][$sysSession->lang] : $MSG['false'][$sysSession->lang]),
					    	'score_publish_' => array($MSG['score_publish_' . QTI_which][$sysSession->lang], $announce_time),
					    	'pre-notice' => array($MSG['pre-notice'][$sysSession->lang]                , $RS['notice'])
					      ) :
					    array(
					    	array($MSG['exam_name'][$sysSession->lang]         , $locale_title[$sysSession->lang]),
					    	array($MSG['item_total_amount'][$sysSession->lang] , $total_items.$MSG['item'][$sysSession->lang]),
					    	array($MSG['enable_duration'][$sysSession->lang]   , $begin_time . ' ' . $close_time),
					    	array($MSG['anonymous or not'][$sysSession->lang]  , (strpos($RS['setting'], 'anonymity') === false ? $MSG['named'][$sysSession->lang] : $MSG['anonymous'][$sysSession->lang])),
					    	array($MSG['modifiable'][$sysSession->lang]        , ($RS['modifiable'] == 'Y' ? $MSG['can_modify'][$sysSession->lang] : $MSG['cannot_modify'][$sysSession->lang])),
					    	array($MSG['score_publish_hint'][$sysSession->lang], $announce_time),
					    	array($MSG['pre-notice'][$sysSession->lang]        , $RS['notice'])
					      )
					 );
        
        // 914：不開放補繳，則不顯示補繳起迄
        if ($paybackDuration === $MSG['notopen'][$sysSession->lang]) {
            unset($display_words['payback_duration']);
        } 
	
	if (QTI_which == 'questionnaire' && defined('forGuestQuestionnaire')) 
		$display_words[3] = array($MSG['access mode'][$sysSession->lang], $MSG['public access tip'][$sysSession->lang]);
	
	$QTI_which = QTI_which;
	$scr = <<< EOB
    var isPhoneDevice = '{$profile['isPhoneDevice']}'; 
	function doExam(bln) {
		if (bln) {
		    if (isPhoneDevice=='1') {
		        location.replace("exam_start.php?{$_SERVER['argv'][0]}+{$_SERVER['argv'][1]}+{$_SERVER['argv'][2]}+{$_SERVER['argv'][3]}+{$_SERVER['argv'][4]}");
		    } else {
			    location.replace("exam_start.php?{$_SERVER['argv'][0]}+{$_SERVER['argv'][1]}+{$_SERVER['argv'][2]}+{$_SERVER['argv'][3]}");
			}
		} else
		    if (isPhoneDevice=='1') {
		        window.close();
		    } else {
			    location.replace("{$QTI_which}_list.php{$school_q}");
			}
	}

EOB;

	showXHTML_head_B($MSG['ready2test'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
        if ($profile['isPhoneDevice']) {
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo '<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">';
            echo '<link href="/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />';
            echo '<link rel="stylesheet" href="/sys/tpl/vendor/font-awesome/css/font-awesome.css" />';
            echo '<script type="text/javascript" src="/lib/jquery/jquery.new.min.js?20200113"></script>';
            echo '<script type="text/javascript" src="/theme/default/bootstrap336/js/bootstrap.min.js"></script>';
            require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
            $smarty->display('phone/learn/exam_style.tpl');
        }

		showXHTML_script('inline', $scr);
	showXHTML_head_E();

	showXHTML_body_B('');

	  showXHTML_table_B('align="center" border="0" cellpadding="0" cellspacing="0" width="'.(($profile['isPhoneDevice'])?'100%':'780').'" style="border-collapse: collapse"');
	    showXHTML_tr_B();
	      showXHTML_td_B();
	        $ary[] = array($MSG['prepare_to_exam'][$sysSession->lang], 'tabsSet',  '');
	        showXHTML_tabs($ary, 1);
	      showXHTML_td_E();
	    showXHTML_tr_E();

	  	showXHTML_tr_B();
	  	  showXHTML_td_B();
	  	    showXHTML_table_B('width="'.(($profile['isPhoneDevice'])?'98%':'760').'" border="0" cellspacing="1" cellpadding="3" class="cssTable" style="border-collapse: collapse"');
	  	      showXHTML_tr_B();
	  	        showXHTML_td('width="100%" class="cssTrHelp" colspan="2"', $MSG['respond_caption'][$sysSession->lang]);
	  	      	  showXHTML_tr_E();
	  	      	    foreach($display_words as $row){
	  	      	      $css_class = $css_class == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"' ;
	  	      	      showXHTML_tr_B($css_class);
	  	      	        showXHTML_td('width="30%" align="right"', $row[0] . '&nbsp;&nbsp;');
	  	      	        showXHTML_td('width="70%"', $row[1]);
			  	  	  showXHTML_tr_E();
			  	      }
	  	      	  showXHTML_tr_B();
	  	        showXHTML_td_B('width="100%" class="cssTrOdd" colspan="2" align="center"');
	  	          showXHTML_input('button', '', $MSG['start_to_respond'][$sysSession->lang], '', 'class="cssBtn" onclick="doExam(true);"');
	  	          showXHTML_input('button', '', $MSG['maybe_nexttime'][$sysSession->lang], '', 'class="cssBtn" onclick="doExam(false);"');
	  	        showXHTML_td_E();
	  	      showXHTML_tr_E();
		    showXHTML_table_E();
	  	  showXHTML_td_E();
	  	showXHTML_tr_E();

	  showXHTML_table_E();
	showXHTML_body_E('');
?>
