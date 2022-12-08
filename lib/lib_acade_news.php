<?
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/acade_news.php');

	/**
	 * �إ߰Q�תO
	 * @param array  $bname : �Q�ת��W��(�U�y�t)
	 * @param array  $result: �����ηs��(news)�`�I���}�C
	 * @return string �Ҩ��o����
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
		// �إ߰Q�תO
		$extras = ($type=='news'?'rank=0;':'');
		$RS = dbNew('WM_bbs_boards', 'bname, title, owner_id, extras', "'{$boardName}', '{$bname[$sysSession->lang]}',{$sysSession->school_id},'{$extras}'");
		if ($RS) {
			$result['board_id'] = $sysConn->Insert_ID();

			// �إ߰Q�תO�s���ɪ��ؿ�
			$BoardPath ="/base/{$sysSession->school_id}/board/{$result['board_id']}";
			@mkdir(sysDocumentRoot . $BoardPath, 0755);
			
			if ($type != 'school')  // �[�J WM_term_subject
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
	 * ���o�̷s�����Q�ת���
	 * @param array  $bname : �Q�ת��W��(�U�y�t)
	 * @param array  $result: �����ηs��(news)�`�I���}�C
	 * @return string �Ҩ��o����
	 **/
	function dbGetNewsBoard(&$result, $type='news') {
		global $sysConn, $MSG;
		if($type=='' || empty($type))
			return false;

		// �����o�̷s��������( �Y�L�Ӫ��h�إߤ@�� )
		$RS = dbGetStMr('`WM_news_subject` AS N LEFT JOIN WM_bbs_boards AS B ON N.board_id = B.board_id',
                        'N.news_id, N.board_id, B.open_time, B.close_time, B.share_time',
						"N.type = '{$type}'",
						ADODB_FETCH_ASSOC);
		
		if(!$RS) {	// �d�ߥ���
			return false;
		}

		if($RS->EOF) { // �S���̷s�����Q�ת�
			// �إߤ@��
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
		        $result['ok'] = 0;  // �}��ɶ�����
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
                        $result['ok'] = 0;  // �}��ɶ��w�L�B������ɮɶ�
					}
					else
					{
					    $result['readonly'] = 1; // �}��ɶ��w�L�B�w����ɮɶ�
					}
				}
				else
				{
				    $result['ok'] = 1;   // �}��ɶ����L
				}
			}

			return true;
		}

		return false;
	}

?>
