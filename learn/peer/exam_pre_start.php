<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_learn.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

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
		$locale_title = unserialize($RS['title']);
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

    function rtnMsg($msg)
    {
        echo '<div class="container esn-container">
                  <div class="panel block-center">
                      <form class="well form-horizontal message-pull-center">
                          <fieldset>
                              <div class="input block-center">
                                  <div class="row">&nbsp;</div>
                                  <div class="control-group">
                                      <div class="message">
                                          <div id="message">
                                              <div>' . $msg. '</div>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="row">&nbsp;</div>
                                  <div class="control-group">
                                      <div class="controls">
                                          <div class="lcms-left">
                                              <a href="peer_list.php" class="btn btn-primary aNormal margin-right-10 btn-blue" id="btnForget">' . $MSG['back_to_list'][$sysSession->lang] . '</a>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </fieldset>
                      </form>
                  </div>
              </div>';
        die();
    }

    // 是否在繳交名單中
	$examinee_perm = array('peer' => 1710400200);
	$permit = false;
	list($roles) = dbGetStsr('WM_term_major','role',"course_id=$sysSession->course_id and username='{$sysSession->username}'", ADODB_FETCH_NUM);
	if($roles & $sysRoles['student']) $permit=true;

    $p = aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable');
    $aclVerified = aclVerifyPermission($examinee_perm[QTI_which], $p, $sysSession->course_id, $_SERVER['argv'][0]);//WM2預設值 true代表有另外設定對象
    if ($aclVerified === 'WM2') $aclVerified = $permit;

    // showXHTML_CSS('include', "/theme/default/bootstrap/css/bootstrap.min.css");
    showXHTML_CSS('include', "/theme/default/learn_mooc/application.css");

    // 不在繳交名單中時
    if ($aclVerified === false) {
        rtnMsg($MSG['pay_needless'][$sysSession->lang]);
    } else {
        if ($RS['modifiable'] === 'N') {
            // 取是否繳交過
            $payed = $sysConn->GetOne(sprintf('select examinee from WM_qti_peer_result where status="submit" AND  exam_id = %d and examinee =\'%s\' and time_id =%d', $_SERVER['argv'][0], $sysSession->username, $_SERVER['argv'][1]));
            if ($payed !== false) {
                rtnMsg($MSG['pay_needless_2'][$sysSession->lang]);
            }
        }
    }

	$school_q = ($RS['course_id'] && $RS['course_id'] == $sysSession->school_id) ? '?school' : '';

	$begin_time = $MSG['from'][$sysSession->lang] . (strpos($RS['begin_time'], '0000') === 0 ? $MSG['now'][$sysSession->lang]     : date('Y-m-d H:i', strtotime($RS['begin_time'])) );
	$close_time = $MSG['to2'][$sysSession->lang]  . (strpos($RS['close_time'], '9999') === 0 ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', strtotime($RS['close_time'])) );

	$ary_announce = array('never'       => $MSG['score_publish0'][$sysSession->lang],
					   	  'now'         => $MSG['score_publish1'][$sysSession->lang],
					      'close_time'  => $MSG['score_publish2'][$sysSession->lang],
					      'user_define' => $MSG['score_publish3'][$sysSession->lang]
					     );
	$announce_time = ($RS['announce_type'] == 'user_define' ? $RS['announce_time'] : $ary_announce[$RS['announce_type']]);
	$display_words = (QTI_which == 'peer' ?
					    array(
					    	array($MSG['exam_name'][$sysSession->lang]                 , htmlspecialchars($locale_title[$sysSession->lang])),
					    	array($MSG['total_score'][$sysSession->lang]               , $total_score.$MSG['minute'][$sysSession->lang]),
					    	array($MSG['exam_percent'][$sysSession->lang]              , $RS['percent'] . '%'),
					    	array($MSG['enable_duration'][$sysSession->lang]           , $begin_time . ' ' . $close_time),
					    	array($MSG['modifiable'][$sysSession->lang]                , ($RS['modifiable'] == 'Y' ? $MSG['can_modify'][$sysSession->lang] : $MSG['cannot_modify'][$sysSession->lang])),
					    	array($MSG['score_publish_' . QTI_which][$sysSession->lang], $announce_time),
					    	array($MSG['pre-notice'][$sysSession->lang]                , $RS['notice'])
					      ) :
					    array(
					    	array($MSG['exam_name'][$sysSession->lang]         , htmlspecialchars($locale_title[$sysSession->lang])),
					    	array($MSG['item_total_amount'][$sysSession->lang] , $total_items.$MSG['item'][$sysSession->lang]),
					    	array($MSG['enable_duration'][$sysSession->lang]   , $begin_time . ' ' . $close_time),
					    	array($MSG['anonymous or not'][$sysSession->lang]  , (strpos($RS['setting'], 'anonymity') === false ? $MSG['named'][$sysSession->lang] : $MSG['anonymous'][$sysSession->lang])),
					    	array($MSG['modifiable'][$sysSession->lang]        , ($RS['modifiable'] == 'Y' ? $MSG['can_modify'][$sysSession->lang] : $MSG['cannot_modify'][$sysSession->lang])),
					    	array($MSG['score_publish_hint'][$sysSession->lang], $announce_time),
					    	array($MSG['pre-notice'][$sysSession->lang]        , $RS['notice'])
					      )
					 );

	if (QTI_which == 'questionnaire' && defined('forGuestQuestionnaire'))
		$display_words[3] = array($MSG['access mode'][$sysSession->lang], $MSG['public access tip'][$sysSession->lang]);

	$QTI_which = QTI_which;
	$scr = <<< EOB
    var isPhoneDevice = '{$profile['isPhoneDevice']}'; 
	function doExam(bln) {
		if (bln) {
		    if (isPhoneDevice=='1') {
		        location.replace("/learn/peer/exam_start.php?{$_SERVER['argv'][0]}+{$_SERVER['argv'][1]}+{$_SERVER['argv'][2]}+{$_SERVER['argv'][3]}+{$_SERVER['argv'][4]}");
		    } else {
			    location.replace("/learn/peer/exam_start.php?{$_SERVER['argv'][0]}+{$_SERVER['argv'][1]}+{$_SERVER['argv'][2]}+{$_SERVER['argv'][3]}");
			}
	    } else
			location.replace("/learn/homework/homework_list.php{$school_q}");
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
	  	    showXHTML_table_B('width="'.(($profile['isPhoneDevice'])?'98%':'760').'" border="0" cellspacing="1" cellpadding="3" class="cssTable" style="border-collapse: collapse; word-wrap: break-word; word-break: break-all;"');
	  	      showXHTML_tr_B();
	  	        showXHTML_td('width="100%" class="cssTrHelp" colspan="2"', $MSG['respond_caption'][$sysSession->lang]);
	  	      	  showXHTML_tr_E();
	  	      	    foreach($display_words as $row){
	  	      	      $css_class = $css_class == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
	  	      	      showXHTML_tr_B($css_class);
	  	      	        showXHTML_td('width="30%" align="right"', $row[0] . '&nbsp;&nbsp;');
	  	      	        showXHTML_td('width="70%"', $row[1]);
			  	  	  showXHTML_tr_E();
			  	      }
	  	      	  showXHTML_tr_B();
	  	        showXHTML_td_B('width="100%" class="cssTrHelp" colspan="2" align="center"');
	  	          showXHTML_input('button', '', $MSG['start_to_respond'][$sysSession->lang], '', 'class="cssBtn" onclick="doExam(true);"');
	  	          showXHTML_input('button', '', $MSG['maybe_nexttime'][$sysSession->lang], '', 'class="cssBtn" onclick="doExam(false);"');
	  	        showXHTML_td_E();
	  	      showXHTML_tr_E();
		    showXHTML_table_E();
	  	  showXHTML_td_E();
	  	showXHTML_tr_E();

	  showXHTML_table_E();
	showXHTML_body_E('');