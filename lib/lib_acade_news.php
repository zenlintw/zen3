<?
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/acade_news.php');

	/**
	 * 建立討論板
	 * @param array  $bname : 討論版名稱(各語系)
	 * @param array  $result: 版號及新知(news)節點號陣列
	 * @return string 所取得的值
	 **/
	function addNewsBoards($bname, &$result, $type='news') {
		global $sysConn, $sysSession;
		$RS = dbGetStSr('WM_bbs_boards', 'count(*) as cnt', '1', ADODB_FETCH_ASSOC);
		if ($RS['cnt'] == 0) {
			$RS = dbNew('WM_bbs_boards', 'board_id', '1000000000');
		}

		foreach(array('Big5','GB2312','en','EUC-JP','user_define') as $charset)
   			$bname[$charset] = stripslashes($bname[$charset]);

		$boardName = addslashes(serialize($bname));

		$result = Array('board_id'=>0, 'news_id'=>0);
		// 建立討論板
		$extras = ($type=='news'?'rank=0;':'');
		$RS = dbNew('WM_bbs_boards', 'bname, title, owner_id, extras', "'{$boardName}', '{$bname[$sysSession->lang]}',{$sysSession->school_id},'{$extras}'");
		if ($RS) {
			$result['board_id'] = $sysConn->Insert_ID();

			// 建立討論板存放夾檔的目錄
			$BoardPath ="/base/{$sysSession->school_id}/board/{$result['board_id']}";
			@mkdir(sysDocumentRoot . $BoardPath, 0755);
			
			if ($type != 'school')  // 加入 WM_term_subject
				$RS1 = dbNew('WM_news_subject','board_id,type',"{$result['board_id']},'{$type}'");
			else
				$RS1 = dbNew('WM_chat_records','board_id,type,owner_id',"{$result['board_id']},'school','$sysSession->school_id'");
			if($RS1) {
				$result['news_id'] = $sysConn->Insert_ID();
				return true;
			} else {
				return false;
			}
		} else
			return false;

	}

	/**
	 * 取得最新消息討論版號
	 * @param array  $bname : 討論版名稱(各語系)
	 * @param array  $result: 版號及新知(news)節點號陣列
	 * @return string 所取得的值
	 **/
	function dbGetNewsBoard(&$result, $type='news') {
		global $sysConn, $MSG;
		if($type=='' || empty($type))
			return false;

		// 先取得最新消息版號( 若無該版則建立一個 )
		$RS = dbGetStMr('`WM_news_subject` AS N LEFT JOIN WM_bbs_boards AS B ON N.board_id = B.board_id',
                        'N.news_id, N.board_id, B.open_time, B.close_time, B.share_time',
						"N.type = '{$type}'",
						ADODB_FETCH_ASSOC);
		
		if(!$RS) {	// 查詢失敗
			return false;
		}

		if($RS->EOF) { // 沒有最新消息討論版
			// 建立一個
			$bname = $MSG[$type];
			return addNewsBoards($bname, $result, $type);
		} else {
		    $now    = date('Y-m-d H:i:s');
			$result = Array('board_id'=>$RS->fields['board_id'],
							'news_id' =>$RS->fields['news_id'],
							'readonly'=>0,
							'ok'	  =>0);

		    if ($RS->fields['open_time'] != '' &&
		        $RS->fields['open_time'] != '0000-00-00 00:00:00' &&
				$RS->fields['open_time'] > $now)
		    {
		        $result['ok'] = 0;  // 開放時間未到
			}
			else
			{
		        if ($RS->fields['close_time'] != '' &&
					$RS->fields['close_time'] != '0000-00-00 00:00:00' &&
					$RS->fields['close_time'] < $now)
				{
			        if ($RS->fields['share_time'] != '' &&
						$RS->fields['share_time'] != '0000-00-00 00:00:00' &&
						$RS->fields['share_time'] > $now)
					{
                        $result['ok'] = 0;  // 開放時間已過且未到分享時間
					}
					else
					{
					    $result['readonly'] = 1; // 開放時間已過且已到分享時間
					}
				}
				else
				{
				    $result['ok'] = 1;   // 開放時間未過
				}
			}

			return true;
		}

		return false;
	}

?>
