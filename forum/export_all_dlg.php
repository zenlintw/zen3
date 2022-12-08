<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
    
	require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
		
		
		
		
	require_once(sysDocumentRoot . '/lib/common.php');
	
	
	$sysSession->cur_func = '900100700';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

    /**
     * 計算某目錄底下所有檔案大小
     * @param string $path
     * @return int 檔案大小(bytes)
     * 備註：不以du -sk的方式計算，因為計算出來結果為佔硬碟容量的大小
     */
    function get_size($path) {
        if (!is_dir($path) && !is_file($path)) return 0;
        if(!is_dir($path))return filesize($path);

        $dir = opendir($path);
        while (($file = readdir($dir)) !== FALSE) {
            if(is_file($path.'/'.$file))$size+=filesize($path.'/'.$file);
            if(is_dir($path.'/'.$file) && $file!='.' && $file !='..')$size +=get_size($path.'/'.$file);
       }
       return $size;
   }
   if($_GET['id']!=''){
		$co_vid=intval($_GET['id']);
		$co_own_id=dbGetOne('WM_bbs_boards','owner_id','board_id="'.$co_vid.'"');
		$board_name = dbGetOne('WM_bbs_boards','bname','board_id="'.$co_vid.'"');
		$arr=getCaption($board_name);
		$board_name=$arr[$sysSession->lang];
				
		$sysSession->q_right         = ChkRight($co_vid);
        $sysSession->b_right         = $sysSession->q_right; // 目前兩者一樣
		$sysSession->board_name		 = $board_name;
		
	}else{
		$co_vid=$sysSession->board_id;
		$co_own_id=$sysSession->board_ownerid;
	}

	/*
	 *	取得目前所有附檔大小 Quota( 學校, 班級, 課程, 小組 )
	 * @param int $owner_id : 討論板 owner_id , 若不給則抓 $sysSession->board_ownerid
	 * @return bool: 成功 >=0 失敗 -1
	 */
	function getAttachesSize($owner_id='') {
		global $sysSession, $sysConn,$co_vid;
		if(empty($owner_id)) {
			if(empty($sysSession->board_ownerid))
				return -1;
			$owner_id = $sysSession->board_ownerid;
		}

		switch(strlen($owner_id)) {
			case 5:// 學校討論版
				$path = sysDocumentRoot . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR . $owner_id;
				break;

			case 7:// 班級
			case 15:// 班級小組
				$path = sysDocumentRoot . DIRECTORY_SEPARATOR . 'base'. DIRECTORY_SEPARATOR . $sysSession->school_id. DIRECTORY_SEPARATOR . 'class'. DIRECTORY_SEPARATOR . $sysSession->class_id;
				break;

			case 8:// 課程
			case 16:// 課程小組
				$path = sysDocumentRoot . DIRECTORY_SEPARATOR . 'base'. DIRECTORY_SEPARATOR . $sysSession->school_id . DIRECTORY_SEPARATOR . 'course'. DIRECTORY_SEPARATOR . $sysSession->course_id;
				break;
			default:
				return -1;
		}

		if(is_dir($path)) {
            $size = get_size($path. DIRECTORY_SEPARATOR . 'board' . DIRECTORY_SEPARATOR.$co_vid) +
                    get_size($path. DIRECTORY_SEPARATOR . 'quint' . DIRECTORY_SEPARATOR. $co_vid);

            return round($size / 1024, 2);
		} else
			return 0;
	}
	$ticket = md5(sysTicketSeed . 'BoardExp' . $_COOKIE['idx'] . $co_vid);
	
	
	if($_GET['id']!=''){
		$co_js="obj.boaid.value='{$co_vid}';obj.ticket.value='{$ticket}';";
	}
	
	
	$js = <<<EOB
	function exportAll(){
		if (opener){
			var obj = opener.document.getElementById('export_all_form');
			{$co_js}
		}else
			var obj = document.getElementById('mainform');
		obj.action = '/forum/export_all.php';
		obj.submit();
		window.close();
	}
EOB;

	// 開始呈現 HTML
	showXHTML_head_B($sysSession->board_name .' - '.$MSG['export_all'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');
	showXHTML_form_B('action="export_all.php" method="post"', 'mainform');
		showXHTML_input('hidden','ticket',$ticket);
		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" align="center"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary = array();
					$ary[] = array($MSG['export_all'][$sysSession->lang], 'tabs1');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top"');

					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td('colspan="7"', '&nbsp;' . (($_GET['id']!='')?$sysSession->course_name:$sysSession->school_name ). '&nbsp;>&nbsp;' . $sysSession->board_name);
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('valign="top" align="right"', $MSG['board'][$sysSession->lang].':');
							if($_GET['id']!=''){
							showXHTML_td('colspan="6"', sprintf($MSG['total_posts'][$sysSession->lang],getTotalPost('', 'board',$co_vid)) );
							}else{
							showXHTML_td('colspan="6"', sprintf($MSG['total_posts'][$sysSession->lang],getTotalPost('', 'board')) );
							}
						showXHTML_tr_E('');
						/*
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="right"', $MSG['quint'][$sysSession->lang].':');
							if($_GET['id']!=''){
							showXHTML_td('colspan="6"', sprintf($MSG['total_posts'][$sysSession->lang],getTotalPost('', 'quint',$co_vid)) );
							}else{
							showXHTML_td('colspan="6"', sprintf($MSG['total_posts'][$sysSession->lang],getTotalPost('', 'quint')) );
							}
							
						showXHTML_tr_E('');
*/
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="right"', $MSG['attaches_size'][$sysSession->lang]);
							showXHTML_td('colspan="6"', getAttachesSize($co_own_id) . ' KB');
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('colspan="7"', $MSG['sure_export_all'][$sysSession->lang] );
						showXHTML_tr_E('');

						showXHTML_tr_B('');
							showXHTML_td_B('colspan="7" class="cssTrEvn"');
								showXHTML_input('button','',$MSG['ok'][$sysSession->lang],'','class="cssBtn" id="btnExportAll" onClick="exportAll();"');
								showXHTML_input('button','',$MSG['cancel'][$sysSession->lang],'','class="cssBtn" id="btnCancel" onClick="window.close();"');
							showXHTML_td_E('');
						showXHTML_tr_E('');

					showXHTML_table_E('');

				showXHTML_td_E('');
			showXHTML_tr_E('');

		showXHTML_table_E('');
	showXHTML_form_E('');
	showXHTML_body_E('');
?>
