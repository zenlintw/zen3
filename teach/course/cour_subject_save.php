<?php
	/**
	 * 儲存討論板設定
	 *
	 * @since   2003/12/30
	 * @author  ShenTing Lin
	 * @version $Id: cour_subject_save.php,v 1.1 2010/02/24 02:40:23 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/teach_course.php');
	require_once(sysDocumentRoot . '/teach/course/cour_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$nid = trim($_POST['nid']);

	$sysSession->cur_func = empty($nid) ? '900100100' : '900100300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	// 檢查 ticket
	$ticket = md5(sysTicketSeed . 'setBorad' . $_COOKIE['idx'] . $nid);
	if (trim($_POST['ticket']) != $ticket) {
	   wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
	   die($MSG['access_deny'][$sysSession->lang]);
	}

	$isError = false;
	// 檢查日期
	if (($_POST['visibility'] == 'visible') && ($_POST['status'] != 'disable') && isset($_POST['ckopen']) && isset($_POST['ckclose'])) {
		// 為了避免組合起來的數值大於 PHP 限定的範圍，所以先比對日期，在比對時間
		$vO = trim($_POST['timeopen']);
		$vO = explode(' ' , $vO);
		$vC = trim($_POST['timeclose']);
		$vC = explode(' ' , $vC);

		$d1 = preg_replace('/\D+/', '', $vO[0]);
		$d1 = intval($d1);
		$d2 = preg_replace('/\D+/', '', $vC[0]);
		$d2 = intval($d2);

		$t1 = preg_replace('/\D+/', '', $vO[1]);
		$t1 = intval($t1);
		$t2 = preg_replace('/\D+/', '', $vC[1]);
		$t2 = intval($t2);

		// 比對日期
		if (($d1 > $d2) || (($d1 == $d2) && ($t1 >= $t2))) {
			$msg = $MSG['msg_date_error'][$sysSession->lang];
			$isError = true;
		}
	}

	if (($_POST['visibility'] == 'hidden') || ($_POST['status'] == 'disable')) {
		if (isset($_POST['ckopen']))  unset($_POST['ckopen']);
		if (isset($_POST['ckclose'])) unset($_POST['ckclose']);
		if (isset($_POST['cklook']))  unset($_POST['cklook']);
	}

	$open  = trim($_POST['timeopen'])  . ':00';
	$close = trim($_POST['timeclose']) . ':00';
	$share = trim($_POST['timelook'])  . ':00';

	$subject_open  = !isset($_POST['ckopen'])  ? '0000-00-00 00:00:00' : $open;
	$subject_close = !isset($_POST['ckclose']) ? '0000-00-00 00:00:00' : $close;
	$subject_share = !isset($_POST['cklook'])  ? '0000-00-00 00:00:00' : $share;

	$lang['Big5']        = stripslashes(trim($_POST['subject_name_big5']));
	$lang['GB2312']      = stripslashes(trim($_POST['subject_name_gb']));
	$lang['en']          = stripslashes(trim($_POST['subject_name_en']));
	$lang['EUC-JP']      = stripslashes(trim($_POST['subject_name_jp']));
	$lang['user_define'] = stripslashes(trim($_POST['subject_name_user']));

	$sw[]       = (trim($_POST['mailfollow']) == 'yes') ? 'mailfollow' : '';
	$switch     = implode(',' , $sw);
	$withattach = (empty($_POST['withattach'])?'no':trim($_POST['withattach']));
	$dd = array(
		'title'      => addslashes(serialize($lang)),
		'status'     => trim($_POST['status']),
		'visibility' => trim($_POST['visibility']),
		'help'       => strip_scr($_POST['help']),
		'mailfollow' => trim($_POST['mailfollow']),
		'withattach' => $withattach,
        	'sort'       => trim($_POST['defsort']),
        	'fb_comment' => trim($_POST['fb_comment'])
	);
	
	if (($_POST['status']=='open')&&($_POST['statusPublic']=='1')) {
	    $dd['status'] = 'public';
	}	

	$owner = $sysSession->course_id;
	if (empty($nid)) {
		// 先儲存到 WM_bbs_boards
		// 再儲存到 WM_term_subject

		dbNew('WM_bbs_boards',
			  '`bname`    , `manager`    , `title`     , `owner_id`, ' .
			  '`open_time`, `close_time` , `share_time`, ' .
			  '`switch`   , `with_attach`, `vpost`     , `default_order`, `fb_comment`',
			  "'{$dd['title']}' , '{$sysSession->username}'                  , '{$dd['help']}'   , '{$owner}', " .
			  "'{$subject_open}', '{$subject_close}'  , '{$subject_share}', " .
			  "'{$switch}'      ,'{$dd['withattach']}','{$_POST['vpost']}', '{$dd['sort']}','{$dd['fb_comment']}'"
		);
		if ($sysConn->Affected_Rows() > 0) {
			$bid = $sysConn->Insert_ID();
			
			dbNew('WM_term_subject',
				'`course_id`, `board_id`, `state`          , `visibility`',
				"'{$owner}' , {$bid}    , '{$dd['status']}', '{$dd['visibility']}'"
			);
			$nid = $sysConn->Insert_ID();
			dbSet('WM_term_subject', "`permute`={$nid}", "node_id={$nid}");
			$msg = ($sysConn->Affected_Rows() > 0) ? $MSG['msg_add_success'][$sysSession->lang] : $MSG['msg_add_fail'][$sysSession->lang];
			wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , 0, 'auto', $_SERVER['PHP_SELF'], 'new bbs boards:' .$bid. $msg);
		} else {
			$msg = $MSG['msg_add_fail'][$sysSession->lang];
			wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'new bbs boards:' .$bid. $msg);
		}
	} else {
		$nid = intval($nid);
		list($bid) = dbGetStSr('WM_term_subject', '`board_id`', "`node_id`={$nid}", ADODB_FETCH_NUM);
		dbSet('WM_term_subject',
			  "`state`='{$dd['status']}', `visibility`='{$dd['visibility']}'",
			  "`node_id`={$nid}"
		);
		$suc1 = $sysConn->Affected_Rows();
		
		// 代換學習路徑節點的 <title> (先取得原 title)
		$old_title = $sysConn->GetOne('select bname from WM_bbs_boards where board_id=' . $bid);
		
		$update = "`bname`='{$dd['title']}'     , `title`='{$dd['help']}'            , `owner_id`='{$owner}'          , " .
				  "`open_time`='{$subject_open}', `close_time`='{$subject_close}'    , `share_time`='{$subject_share}', " .
                  "`switch`='{$switch}'         , `with_attach`='{$dd['withattach']}', `vpost`='{$_POST['vpost']}'    , " .
                  "`default_order`='{$dd['sort']}', `fb_comment`='{$dd['fb_comment']}'";
        dbSet('WM_bbs_boards', $update,	"`board_id`={$bid}");
        $suc2 = $sysConn->Affected_Rows();

        // 代換學習路徑節點的 <title> begin
		if ($suc2 && ($new_title = stripslashes($dd['title'])) != $old_title)
		{
			$manifest = new SyncImsmanifestTitle(); // 本類別定義於 db_initialize.php
			$manifest->replaceTitleForImsmanifest(5, $nid, $manifest->convToNodeTitle($lang));
			$manifest->replaceTitleForImsmanifest(6, $bid, $manifest->convToNodeTitle($lang));
			$manifest->restoreImsmanifest();
		}
		// 代換學習路徑節點的 <title> end
		
		$msg = (($suc1 > 0) || ($suc2 > 0)) ? $MSG['msg_update_success'][$sysSession->lang] : $MSG['msg_update_fail'][$sysSession->lang];
		wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , 0, 'auto', $_SERVER['PHP_SELF'], 'update bbs boards:'.$bid.$msg);
	}

	$js = <<< BOF
	/**
	 * 回到管理列表
	 **/
	function goManage() {
		window.location.replace("cour_subject.php");
	}

	window.onload = function () {
		alert("{$msg}");
	};
BOF;

	showXHTML_head_B($MSG['subject_save_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1){
        echo '<style>';
        echo '#outerTable{margin:0 auto;width:96%;margin-top:15px;}';
        echo 'textarea {width:90%}';
        echo '.cssBtn {height:unset}';
        echo '</style>';
    }
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('');
		$ary = array();
		$ary[] = array($MSG['subject_save_title'][$sysSession->lang], 'tabs_host');
		showXHTML_tabFrame_B($ary, 1, '', 'outerTable');
			// 主持人設定 (Begin)
			$col = 'class="cssTrOdd"';
			if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1){
			    showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="cssTable"');
			} else {
				showXHTML_table_B('width="500" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="cssTable"');
			}
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('colspan="3"', $msg);
				showXHTML_tr_E();
				// 聊天室名稱
				$lang = old_getCaption(stripslashes($dd['title']));
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_subject'][$sysSession->lang]);
					showXHTML_td_B('');
						$multi_lang = new Multi_lang(true, $lang, $col); // 多語系輸入框
						$multi_lang->show(false);
					showXHTML_td_E();
				showXHTML_tr_E();
				
				// 說明
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
				    if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1){
					    showXHTML_td('', $MSG['title_help'][$sysSession->lang]);
				    } else {
				    	showXHTML_td('', "<pre>{$MSG['title_help'][$sysSession->lang]}</pre>");
				    }
					showXHTML_td('', nl2br($dd['help']));
				showXHTML_tr_E();
				// 顯示或隱藏
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['visibility'][$sysSession->lang]);
					showXHTML_td_B('');
						echo $dd['visibility'] == 'visible' ? $MSG['title_visible'][$sysSession->lang] : $MSG['title_hidden'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				if ($dd['visibility'] == 'visible') {
					// 狀態
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $MSG['title_status'][$sysSession->lang]);
						if ($dd['status'] == 'public') {
						    showXHTML_td('', $titleStatus['open'].','.$MSG['share_social_site'][$sysSession->lang]);
						}else{
						    showXHTML_td('', $titleStatus[$dd['status']]);
						}
					showXHTML_tr_E();
					if ($dd['status'] != 'disable') {
						// 啟用時間
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('', $MSG['title_open_time'][$sysSession->lang]);
							showXHTML_td_B('');
								echo !isset($_POST['ckopen']) ? $MSG['unlimit'][$sysSession->lang] : $open;
							showXHTML_td_E();
						showXHTML_tr_E();
						// 關閉時間
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('', $MSG['title_close_time'][$sysSession->lang]);
							showXHTML_td_B('');
								echo !isset($_POST['ckclose']) ? $MSG['unlimit'][$sysSession->lang] : $close;
							showXHTML_td_E();
						showXHTML_tr_E();
						// 開放參觀
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('', $MSG['title_share_time'][$sysSession->lang]);
							showXHTML_td_B('');
								echo !isset($_POST['cklook']) ? $MSG['unlimit'][$sysSession->lang] : $share;
							showXHTML_td_E();
						showXHTML_tr_E();
					}
				}
				// 自動轉寄
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_mailfollow'][$sysSession->lang]);
					showXHTML_td_B('');
						echo ($dd['mailfollow'] == 'yes' ? $MSG['title_yes'][$sysSession->lang] : $MSG['title_no'][$sysSession->lang]) ,
						     ($dd['withattach'] == 'yes' ? "&nbsp;({$MSG['with_attach'][$sysSession->lang]})" : '');
					showXHTML_td_E();
				showXHTML_tr_E();
				
				// 啟用fb留言
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
				showXHTML_td('', $MSG['title_fb_comment_flag'][$sysSession->lang]);
				showXHTML_td_B('');
				echo ($dd['fb_comment'] == 'Y' ? $MSG['title_yes'][$sysSession->lang] : $MSG['title_no'][$sysSession->lang]);
				showXHTML_td_E();
				showXHTML_tr_E();
				
				// 離開按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" align="center"');
						showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="goManage()"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
			// 主持人設定 (End)
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>
