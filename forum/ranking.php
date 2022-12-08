<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '900100400';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	function ResponseMessage($title,$msgs){
		global $sysSession, $MSG;
		showXHTML_head_B('');
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		showXHTML_head_E();
		showXHTML_body_B('onload="setTimeout(\'self.close()\', 3000);"');
			$ary = array();
			$ary[] = array($MSG['rank3'][$sysSession->lang], 'tabs1');
			showXHTML_tabFrame_B($ary, 1);
				showXHTML_table_B('width="220" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td('', $msgs );
					showXHTML_tr_E();
					// 離開按鈕
					showXHTML_tr_B('class="cssTrOdd"');
						showXHTML_td_B('align="center"');
							showXHTML_input('button', '', $MSG['close_window'][$sysSession->lang], '', 'class="cssBtn" onclick="self.close();"');
						showXHTML_td_E();
					showXHTML_tr_E();
				showXHTML_table_E();
			showXHTML_tabFrame_E();
		showXHTML_body_E();
	}


	// 主程式開始
	$sco  = $_GET['sco'];
	$node = $_GET['node'];
	if (ereg('^[1-7]$',$sco) &&		// 分數在 1-7 分
	    ereg('^[0-9]{6,}$',$node)		// 看板節點判斷
	   ){
	   	// 寫入 WM_bbs_ranking 記錄
	   	dbNew('WM_bbs_ranking',
	   	      'board_id,node,site,username,score',
	   	      "{$sysSession->board_id},'$node',$sysSiteNo,'$sysSession->username',$sco"
	   	     );
		// 如果失敗
		if ($sysConn->Affected_Rows() < 1)
		{
			ResponseMessage($MSG['rank_failure'][$sysSession->lang], ('<font color="red">'.$MSG['already_rank'][$sysSession->lang].'</font>'));
			exit;
		}

		// 產生 Rank 計算資料
		list($rank, $rcnt) = dbGetStSr(
			'WM_bbs_ranking',
			'ROUND(sum(score)/count(*), 1) AS rank, count(*) AS cnt',
			"board_id={$sysSession->board_id} AND node='{$node}' AND site={$sysSiteNo} GROUP BY board_id, node, site");

		// 成功的話就更改該文章之星等
		dbSet('WM_bbs_posts', "rank={$rank},rcount={$rcnt}", "board_id=$sysSession->board_id and node='$node' and site=$sysSiteNo");
		/*
		dbSet('WM_bbs_posts',
		      "rank=if(isnull(rank),$sco,round((rank+$sco)/2,1)),".
		      "rcount=if(isnull(rcount),1,rcount+1)",
		      "board_id=$sysSession->board_id and node='$node' and site=$sysSiteNo");
		*/
		ResponseMessage('', $MSG['rank_success'][$sysSession->lang]);
		die('<script>setTimeout(function () {self.close();}, 6000);</script>');
	}
	// 分數、看板序號、節點不合法
	else{
		ResponseMessage($MSG['rank_failure'][$sysSession->lang], ('<font color="red">'.$MSG['rank_argu_err'][$sysSession->lang].'</font>'));
	}
?>
