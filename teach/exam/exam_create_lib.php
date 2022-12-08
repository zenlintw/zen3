<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/teach/grade/grade_recal.php');

	class examMaintain {

		var
			$foo = '',
			$topDir   = '',
			$courseId = '',
			$isModify = false,
			$itemData = array(),
//			$ident    = '',
			$_QTI_which= '',
			$calanderSync = false,
			$exam_perm = array(),
			$courseExamQuestionsLimit = 200,
			$types = array();

		/**
		* 初始化
		*/
		function examMaintain() {
			global $sysSession, $MSG;

			// 設定路徑
			if (!defined('QTI_env')) {
				list($this->foo, $this->topDir, $this->foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
			} else {
				$this->topDir = QTI_env;
			}

			$this->_QTI_which = (defined('XMLAPI') && XMLAPI) ? API_QTI_which : QTI_which;
			$this->courseId = ($this->topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;
			$this->isModify = (strpos($_SERVER['PHP_SELF'], '/item_modify1.php') !== false);
			$this->courseExamQuestionsLimit = defined(CourseExamQuestionsLimit) ? CourseExamQuestionsLimit : 200;

			require_once(sysDocumentRoot . '/lang/' . API_QTI_which . '_teach.php');
			$this->types = array(
				1 => $MSG['item_type1'][$sysSession->lang],
		     	2 => $MSG['item_type2'][$sysSession->lang],
		     	3 => $MSG['item_type3'][$sysSession->lang],
		     	4 => $MSG['item_type4'][$sysSession->lang],
		     	5 => $MSG['item_type5'][$sysSession->lang],
		     	6 => $MSG['item_type6'][$sysSession->lang],
		     	7 => $MSG['item_type7'][$sysSession->lang]
			);
		}
		/**
		 * 驗證 ticket 是否正確
		 */
		function verify(){
			// TODO: ticket = sysTicketSeed . $_COOKIE['idx'] . $this->itemData['exam_id']
			if (empty($this->itemData['ticket'])) return false;
			return ($this->itemData['ticket'] == md5(sysTicketSeed . $_COOKIE['idx'] . $this->itemData['exam_id']) ?
					true :
					false) ;
		}
		/**
		* 檢查 ACL，並產生 ident
		*
		* @return boolean  true: 通過檢查, false: 未通過檢查
		*/
		function checkACL($origin, $ident, $ticket) {
			global $sysSession;

			$funcid = '';
			require_once(sysDocumentRoot . '/lang/' . $this->_QTI_which . '_teach.php');
			if ($this->_QTI_which == 'exam') {
				include_once(sysDocumentRoot . '/lib/lib_calendar.php');
				include_once(sysDocumentRoot . '/lang/exam_teach.php');
				$funcid = '1600200';
			}
			else if ($this->_QTI_which == 'homework') {
				$funcid = '1700200';
			}
			else if ($this->_QTI_which == 'questionnaire') {
				$funcid = '1800200';
			}

			if ($this->isModify === true) {
				$sysSession->cur_func = $funcid . '200';
				// 判斷 ticket 是否正確 (開始)
//				$newticket = md5($origin . $ident . sysTicketSeed . $this->courseId . $_COOKIE['idx']);
//				if ($newticket != $ticket) {
//					wmSysLog($sysSession->cur_func, $this->courseId, 0, 1, 'auto', $_SERVER['PHP_SELF'], 'Illegal Access!');
//					return false;
//				}
//				// 判斷 ticket 是否正確 (結束)
//				$this->ident = $ident;
			} else {
				$sysSession->cur_func = $funcid . '100';

			}
			$sysSession->restore();
			if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

			}

			return true;
		}

		/**
		 * 更新試卷
		 *
		 * @return array array(Error Code, Error Message)
		 */
		function updateExam () {
			global $sysConn;
			$type = array('homework' => 1700200200, 'exam' => 1600200200, 'questionnaire' => 1800200200);
			if (!aclVerifyPermission($type[$this->_QTI_which], 16)) aclPermissionDeny();
			// TODO: 功能運作後再決定要不要驗
//			if (!$this->verify()) die('Fake data !');

			$old_title = $sysConn->GetOne('select title from WM_qti_' . $this->_QTI_which . '_test where exam_id=' . $this->itemData['exam_id']);

			dbSet('WM_qti_' . $this->_QTI_which . '_test',
				sprintf("title = '%s', type  = %d, modifiable ='%s', publish ='%s', begin_time ='%s',
					close_time ='%s', count_type   ='%s', percent ='%s', do_times ='%s', do_interval  ='%s',
					item_per_page = '%s', ctrl_paging  = '%s', ctrl_window = '%s', ctrl_timeout = '%s', announce_type = '%s',
					announce_time = '%s', item_cramble = '%s', random_pick = %d, setting = '%s', notice = '%s',
					content = '%s'",
						$this->itemData['title'], $this->itemData['ex_type'], $this->itemData['modifiable'], $this->itemData['publish'], $this->itemData['begin_time'],
						$this->itemData['close_time'], $this->itemData['count_type'], $this->itemData['percent'], $this->itemData['do_times'], $this->itemData['do_interval'],
						$this->itemData['item_per_page'], $this->itemData['ctrl_paging'], $this->itemData['ctrl_window'], $this->itemData['ctrl_timeout'], $this->itemData['announce_type'],
						$this->itemData['announce_time'], $this->itemData['item_cramble'], $this->itemData['random_pick'], $this->itemData['setting'], $this->itemData['notice'],
						$this->itemData['content']),
				'exam_id=' . $this->itemData['exam_id']
			);

			$isModified = $sysConn->Affected_Rows();
			$instance = $this->itemData['exam_id'];

			// 代換學習路徑節點的 <title> begin
			if (($new_title = stripslashes($this->itemData['title'])) != $old_title)
			{
				$manifest = new SyncImsmanifestTitle(); // 本類別定義於 db_initialize.php
				$manifest->replaceTitleForImsmanifest(($this->_QTI_which == 'exam' ? 3 : ($this->_QTI_which == 'homework' ? 2 : 4 )),
						$instance,
						$manifest->convToNodeTitle($new_title));
				$manifest->restoreImsmanifest();
			}
			// 代換學習路徑節點的 <title> end
			return array(
				'isModified' => $isModified,
				'instance' => $instance,
				'ErrCode' => $sysConn->ErrorNo(),
				'ErrMsg'  => $sysConn->ErrorMsg()
			);
		}

		/**
		 * 更新試卷
		 *
		 * @return array array(Error Code, Error Message)
		 */
		function addExam () {
			global $sysConn;
			dbNew('WM_qti_' . $this->_QTI_which . '_test',
				"course_id, title, type, modifiable, publish,
			     begin_time, close_time, count_type, percent, do_times,
			     do_interval, item_per_page, ctrl_paging, ctrl_window, ctrl_timeout,
			     announce_type, announce_time, item_cramble, random_pick, setting,
			     notice, content",
				sprintf("%d, '%s', %d, '%s', '%s',
						'%s', '%s', '%s', '%s', '%s',
						'%s', '%s', '%s', '%s', '%s',
						'%s', '%s', '%s', %d, '%s',
						'%s', '%s'",
						$this->courseId, $this->itemData['title'], $this->itemData['ex_type'], $this->itemData['modifiable'], $this->itemData['publish'],
						$this->itemData['begin_time'], $this->itemData['close_time'], $this->itemData['count_type'], $this->itemData['percent'], $this->itemData['do_times'],
						$this->itemData['do_interval'], $this->itemData['item_per_page'], $this->itemData['ctrl_paging'], $this->itemData['ctrl_window'],$this->itemData['ctrl_timeout'],
						$this->itemData['announce_type'], $this->itemData['announce_time'], $this->itemData['item_cramble'], $this->itemData['random_pick'], $this->itemData['setting'],
						$this->itemData['notice'], $this->itemData['content'])

			);
			$isModified = $sysConn->Affected_Rows();
//			$type = array('homework' => 1700200100, 'exam' => 1600200100, 'questionnaire' => 1800200100);
			$instance = $sysConn->Insert_ID();
			return array(
				'isModified' => $isModified,
				'instance' => $instance,
				'ErrCode' => $sysConn->ErrorNo(),
				'ErrMsg'  => $sysConn->ErrorMsg()
			);

		}

		/**
		 *  轉換試題資料
		 * @param $data
		 * @return array code(0: 成功; 1:xml 解析錯誤) errMsg(錯誤訊息)
		 */
		function dataTransform ($data) {
			$transData = $data;
			// 可否修改
			$transData['modifiable'] = ($data['modifiable'] === 'Y') ? $data['modifiable'] : 'N';
			// 是否發布
			if ($data['rdoPublish'] == 1) {	// 不發布
				$transData['begin_time'] = '0000-00-00 00:00:00';
				$transData['close_time'] = '9999-12-31 00:00:00';
			} else {
				$transData['begin_time'] = (isset($data['begin_time']) && $data['begin_time'] !== "") ? $data['begin_time'] : '0000-00-00 00:00:00';
				$transData['close_time'] = (isset($data['close_time']) && $data['close_time'] !== "") ? $data['close_time'] : '9999-12-31 00:00:00';
			}
			$transData['announce_time'] = ($data['announce_type'] === 'user_define') ? $data['announce_time'].':00' : 'NULL';


			foreach(array('Big5','GB2312','en','EUC-JP','user_define') as $charset) {
				$data['title'][$charset] = stripslashes($data['title'][$charset]);
			}
			$transData['title'] = addslashes(serialize($data['title']));

			// 資料防駭
			foreach(array('do_times', 'do_interval', 'item_per_page', 'random_pick') as $i)$transData[$i] = intval($data[$i]);
			if (!in_array($data['qti_support_app'], array('N', 'Y')))                                 $transData['qti_support_app'] = 'N';
            if (!in_array($data['ex_type'],         array(1, 2, 3, 4, 5)))                               $transData['ex_type']         = 1;
			if (!in_array($data['modifiable']   , array('N', 'Y')))                                    $transData['modifiable']    = 'N';
			if (!in_array($data['publish']      , array('prepare','action','close')))                  $transData['publish']       = 'prepare';
			if (!in_array($data['count_type']   , array('none','first','last','max','min','average'))) $transData['count_type']    = 'first';
			if (!in_array($data['ctrl_paging']  , array('none','can_return','lock')))                  $transData['ctrl_paging']   = 'none';
			if (!in_array($data['ctrl_window']  , array('none','lock')))                               $transData['ctrl_window']   = 'none';
			if (!in_array($data['ctrl_timeout'] , array('none','mark','auto_submit')))                 $transData['ctrl_timeout']  = 'none';
			if (!in_array($data['announce_type'], array('never','now','close_time','user_define')))    $transData['announce_type'] = 'never';
			$transData['item_cramble'] = implode(',', array_intersect(array('enable','choice','item','section','random_pick'), explode(',', $data['item_cramble'])));
			$transData['percent'] = floatval($data['percent']);
			if (isset($data['threshold_score']) && !ereg('^([0-9]+(\.[0-9])?)?$', $data['threshold_score'])) {
				$transData['threshold_score'] = '';
			} else {
				$transData['threshold_score'] = $data['threshold_score'];
			}
			$transData['setting'] = preg_replace('/,$/', '', ($data['setting']['upload']    ? 'upload,'    : '') .
					($data['setting']['anonymity'] ? 'anonymity,' : ''));
			// 資料防駭 over
			$transData['content']= preg_replace('/[\r\n\t]+/', '', $data['content']);

			// 試題處理及格成績
			if ($this->_QTI_which == 'exam')
			{
				// 去除 html、xml tag
				$data['content'] = preg_replace('~<(?:!DOCTYPE|/?(?:html|body|\?xml))[^>]*>\s*~i', '', $data['content']);

				if (strpos($data['content'], 'threshold_score=') !== FALSE) {
					$data['content'] = preg_replace(array('/\bthreshold_score=\\\\"[^"]*\\\\"/', '/\bthreshold_score="[^"]*"/'),
							array('threshold_score=\\"' . $data['threshold_score'] . '\\"', 'threshold_score="' . $data['threshold_score'] . '"'),
							$data['content']
					);
				} else if (($i=strpos($data['content'], '>')) !== FALSE) {
					if (substr($data['content'], $i-1, 1) == '/') $i--;    // 如果 XML 只有 < ... /> 的話

					$data['content'] = substr($data['content'], 0, $i) .
							(' threshold_score=\\"' . $data['threshold_score'] . '\\"') .
							substr($data['content'], $i);
				}
			}

			if ($xmlstr = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]+/', ' ', stripslashes($data['content']))))
			{
				if (!$dom = domxml_open_mem($xmlstr))
					return array(
						'code'	=> -1,
						'errMsg' => 'XML containing incorrect char(s) ' . $xmlstr . ''
					);
				$transData['content'] = addslashes($xmlstr);
			}
			return $transData;
		}

		/**
		 *  for app 將 item 資訊組合成完整的 xml
		 * web 端在 js 實作不需使用
		 * @param array $items(itemid 陣列)
		 * @param array $answer 正確解答(exam 才需要)
		 */
		function createContentXml($items, $answer = array()) {
			//TODO: 參考 exam_create.js 的 item 新增 & item_search.php
			if (count($items) > $this->courseExamQuestionsLimit) {
				return array(
					'code' => 1,
					'message' => 'too many items'
				);
			}
			foreach ($items AS $v) {
				$assignItems[] = sprintf("`ident` = '%s'", mysql_real_escape_string($v));
			}
			$selectTable = 'WM_qti_' . $this->_QTI_which . '_item';
			$fields = '`ident`,`type`,`title`,`content`,`version`,`volume`,`chapter`,`paragraph`,`section`,`level`,`course_id`';
			$where = sprintf("`course_id` = %d AND (%s)", $this->courseId, implode('OR', $assignItems));
			$RS = dbGetStMr($selectTable, $fields, $where, ADODB_FETCH_ASSOC);

			if ($RS) {
				while ($row = $RS->fetchRow()) {
					$itemContent[$row['ident']] = sprintf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>",
							$row['title'],
							$this->types[$row['type']],
							$row['version'],
							$row['volume'],
							$row['chapter'],
							$row['paragraph'],
							$row['section']);
				}
			}

			// 組合 xml
			$docxml = domxml_new_doc("1.0");
			$questestinterop = $docxml->create_element("questestinterop");
			$docxml->append_child($questestinterop);

			if ($this->isModify === true) {
				// 修改
				// TODO: exam_create.php line: 67
			} else {
				$questestinterop->set_attribute("xmlns", "http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd");
				$questestinterop->set_attribute("xmlns:wm", "http://www.sun.net.tw/WisdomMaster");
			}

			foreach ($items AS $v) {
				if (isset($itemContent[$v])) {
					$newItem = $docxml->create_element("item");
					$contentText = $docxml->create_text_node($itemContent[$v]);
					$newItem->append_child($contentText);
					$newItem->set_attribute('xmlns', '');
					$newItem->set_attribute('id', $v);
					// 如果有傳入 $answer 將分數帶入
					if (count($answer) > 0) {
						$itemScore = (isset($answer[$v])) ? $answer[$v] : 0;
						$newItem->set_attribute('score', $itemScore);
					}
					$questestinterop->append_child($newItem);
				}
			}

			return array(
					'code' => 0,
					'message' => 'success',
					'data' => (string)$docxml->dump_mem(false, "UTF-8")
			);
		}

		function calanderSyncHandler ($instance) {
			global $sysConn, $sysSession, $MSG;
			//$sysConn->debug=true;
			$calendar_begin_type=$this->_QTI_which.'_begin';
			$calendar_end_type=$this->_QTI_which.'_end';
			if ( ($this->_QTI_which == 'homework'||$this->_QTI_which == 'exam') && $this->itemData['rdoPublish'] == '2') {
				$begin_cal_idx = $sysConn->GetOne("select idx from WM_calendar where relative_type='{$calendar_begin_type}' and relative_id={$instance}");
				$end_cal_idx = $sysConn->GetOne("select idx from WM_calendar where relative_type='{$calendar_end_type}' and relative_id={$instance}");
				$username=$sysSession->course_id;
				$type='course';
				$repeat = 'none';
				$repeat_begin='0000-00-00';
				$repeat_end='0000-00-00';
				$alertType="email";
				$alertBefore="3";
				$ishtml = "text";
				$date1 = getdate(strtotime($this->itemData['begin_time']));
				$date2 = getdate(strtotime($this->itemData['close_time']));
				if(strncmp($this->itemData['begin_time'], $this->itemData['close_time'], 10) == 0) { // 起始同一天
					if ( isset($this->itemData['exam_id']) && $begin_cal_idx) {
						dbDel('WM_calendar', 'idx=' . $begin_cal_idx);
					}
					if ( isset($this->itemData['exam_id']) && $end_cal_idx ) {
						dbDel('WM_calendar', 'idx=' . $end_cal_idx);
					}
					$memo_date=substr($this->itemData['begin_time'],0,10);
					$timeBegin = "'".substr($this->itemData['begin_time'],10)."'";
					$timeEnd   = "'".substr($this->itemData['close_time'],10)."'";
					$subject = $MSG['left_quote'][$sysSession->lang] . htmlspecialchars($this->itemData['title'][$sysSession->lang]) . $MSG['begin_to_'.$this->_QTI_which][$sysSession->lang];
					$content = $MSG['hold_something_today'][$sysSession->lang] .htmlspecialchars($this->itemData['title'][$sysSession->lang]) . $MSG[$this->_QTI_which.'_attention_please1'][$sysSession->lang];
					$fields = '`username`, `type`, `memo_date`, `time_begin`, `time_end`, '.
							'`repeat`, `repeat_freq`, `repeat_begin`, `repeat_end`, ' .
							'`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`,`relative_type`,`relative_id`';
					$values = "'{$username}', '{$type}','{$memo_date}', {$timeBegin}, {$timeEnd}" .
							", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" .
							", '{$alertType}', {$alertBefore}, '{$ishtml}', '{$subject}', '{$content}', NULL,'{$calendar_begin_type}','{$instance}'";
					dbNew('WM_calendar', $fields, $values);
				} else { // 起始不同天
					if ( !hasSetDate($this->itemData['begin_time']) && isset($this->itemData['exam_id']) && $begin_cal_idx) { //開放作答日期沒有限制時要刪除舊的行事曆
						dbDel('WM_calendar', 'idx=' . $begin_cal_idx);
					}
					if ( hasSetDate($this->itemData['begin_time']) && isset($this->itemData['ck_sync_begin_time'])) {
						if( isset($this->itemData['exam_id']) && $begin_cal_idx ) {
							//刪除舊的行事曆
							dbDel('WM_calendar', 'idx=' . $begin_cal_idx);
						}
						$memo_date=substr($this->itemData['begin_time'],0,10);
						$timeBegin = 'NULL';
						$timeEnd   = 'NULL';
						$subject = $MSG['left_quote'][$sysSession->lang] . htmlspecialchars($this->itemData['title'][$sysSession->lang]) . $MSG['begin_to_'.$this->_QTI_which][$sysSession->lang];
						$content = $MSG['hold_something_today'][$sysSession->lang] .htmlspecialchars($this->itemData['title'][$sysSession->lang]) . $MSG[$this->_QTI_which.'_attention_please1'][$sysSession->lang];
						$fields = '`username`, `type`, `memo_date`, `time_begin`, `time_end`, '.
								'`repeat`, `repeat_freq`, `repeat_begin`, `repeat_end`, ' .
								'`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`,`relative_type`,`relative_id`';
						$values = "'{$username}', '{$type}','{$memo_date}', {$timeBegin}, {$timeEnd}" .
								", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" .
								", '{$alertType}', {$alertBefore}, '{$ishtml}', '{$subject}', '{$content}', NULL,'{$calendar_begin_type}','{$instance}'";
						dbNew('WM_calendar', $fields, $values);
					}
					if ( !hasSetDate($this->itemData['close_time']) && isset($this->itemData['exam_id']) && $end_cal_idx ) { //關閉作答日期沒有限制時要刪除舊的行事曆
						dbDel('WM_calendar', 'idx=' . $end_cal_idx);
					}
					if ( hasSetDate($this->itemData['close_time']) && isset($this->itemData['ck_sync_end_time']) )
					{
						if( isset($this->itemData['exam_id']) && $end_cal_idx) {
							//刪除舊的行事曆
							dbDel('WM_calendar', 'idx=' . $end_cal_idx);
						}
						$memo_date=substr($this->itemData['close_time'],0,10);
						$timeBegin = 'NULL';
						$timeEnd   = 'NULL';
						$subject = $MSG['left_quote'][$sysSession->lang] . htmlspecialchars($this->itemData['title'][$sysSession->lang]) . $MSG['stop_to_'.$this->_QTI_which][$sysSession->lang];
						$content = $MSG['today_is'][$sysSession->lang] .htmlspecialchars($this->itemData['title'][$sysSession->lang]) . $MSG[$this->_QTI_which.'_attention_please2'][$sysSession->lang];
						$fields = '`username`, `type`, `memo_date`, `time_begin`, `time_end`, '.
								'`repeat`, `repeat_freq`, `repeat_begin`, `repeat_end`, ' .
								'`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`,`relative_type`,`relative_id`';
						$values = "'{$username}', '{$type}','{$memo_date}', {$timeBegin}, {$timeEnd}" .
								", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" .
								", '{$alertType}', {$alertBefore}, '{$ishtml}', '{$subject}', '{$content}', NULL,'{$calendar_end_type}','{$instance}'";
						dbNew('WM_calendar', $fields, $values);
					}
				}
			} elseif ( isset($this->itemData['exam_id']) && ($this->_QTI_which == 'homework'||$this->_QTI_which == 'exam') && $this->itemData['rdoPublish'] == '1') {
				$calendar_ids = $sysConn->GetCol("select idx from WM_calendar where (relative_type='{$calendar_begin_type}' or relative_type='{$calendar_end_type}') and relative_id='{$instance}' limit 2");
				if (is_array($calendar_ids) && count($calendar_ids)) dbDel('WM_calendar', 'idx in (' . implode(',', $calendar_ids) . ')');
			}
			//$sysConn->debug=end;
			// 與行事曆同步 end
		}

		/**
		 * 處理 test ACL
		 */
		// TODO: die 改為 return
		function forGuestAclHandler ($instance) {
			global $sysConn, $sysSession;
			// 如果是開放型問卷，則新增的時候，要順便加一個 ACL
			if ($this->itemData['forGuest'])
			{
				$noGuestAcl = true;
				if ($this->itemData['exam_id'])
				{
					$re = dbGetOne('WM_acl_list AS L, WM_acl_member AS M',
							'count(*)',
							"L.function_id=1800300200 AND L.unit_id={$this->courseId} AND L.instance={$this->itemData['exam_id']} AND L.acl_id=M.acl_id AND M.member='guest'");
					$noGuestAcl = !$re; // 如果已經有 ACL 了就不用再加
				}

				if ($noGuestAcl)
				{
					$t = array('Big5'        => 'for Guest',
							'GB2312'      => 'for Guest',
							'en'          => 'for Guest',
							'EUC-JP'      => 'for Guest',
							'user_define' => 'for Guest');
					$titles = serialize(array_reverse($t));
					dbNew('WM_acl_list', 'permission,caption,function_id,unit_id,instance',
							sprintf("'enable','%s',1800300200,%u,%u",
									addslashes($titles),
									$this->courseId,
									$instance
							)
					);
					if ($sysConn->ErrorNo() === 0){
						$new_id = $sysConn->Insert_ID();
						dbNew('WM_acl_member', 'acl_id,member', $new_id . ',"guest"');
					}
				}
			}
			else
			{
				// 計算有無修改新增或刪除 acl
				$acl_update_row = 0;

				foreach(explode(chr(12), get_magic_quotes_gpc() ? stripslashes($this->itemData['acl_lists']) : $this->itemData['acl_lists']) as $k => $acl_slice){
					$acl_lists = explode("\n", $acl_slice);

					// 取出 user 送過來的 acl
					$cur_lists = array();
					$new_lists = array();
					foreach($acl_lists as $item){
						$x = explode(chr(8), $item, 2);
						if (preg_match('/^[0-9]+$/', $x[0])) $cur_lists[] = intval($x[0]);
						elseif($x[0] == '*new*') $new_lists[] = $x[1];
					}
					// 取出資料庫的舊 acl
					$old_lists = aclGetAclIdByInstance($this->exam_perm[$k][$this->_QTI_which], $this->courseId, $instance);

					// 處理要刪掉的 ACL
					$will_rm = implode(',', array_diff($old_lists,$cur_lists));

					if ($will_rm != ''){
						dbDel('WM_acl_member', sprintf('acl_id in (%s)', $will_rm));
						dbDel('WM_acl_list', sprintf('acl_id in (%s)', $will_rm));

						if ($sysConn->Affected_Rows() > 0){
							++$acl_update_row;
						}
					}

					// 處理要新增的 ACL
					foreach($new_lists as $item){
						$elements = explode(chr(8), $item);
						$t = array();
						list($t['Big5'], $t['GB2312'], $t['en'], $t['EUC-JP'], $t['user_define']) = explode(chr(9), (get_magic_quotes_gpc() ? stripslashes($elements[0]) : $elements[0]));
						$titles = serialize(array_reverse($t));
						dbNew('WM_acl_list', 'permission,caption,function_id,unit_id,instance',
								sprintf("%d,'%s',%d,%d,%d",
										$elements[1],
										addslashes($titles),
										$this->exam_perm[$k][$this->_QTI_which],
										$this->courseId,
										$instance
								)
						);
						if ($sysConn->ErrorNo() === 0){
							$new_id = $sysConn->Insert_ID();
							$users = preg_split('/\s+/', $elements[3], -1, PREG_SPLIT_NO_EMPTY);
							foreach(explode(',', aclBitmap2Roles($elements[2])) as $role) if ($role) $users[] = '#' . $role;
							foreach($users as $user) dbNew('WM_acl_member', 'acl_id,member', $new_id . ',"' . $user . '"');
							// 若沒有對象，預設為本課程所有正式生
							if(!count($users)) dbDel('WM_acl_list', "acl_id={$new_id}");;

							if ($sysConn->Affected_Rows() > 0){
								++$acl_update_row;
							}
						}
						else {
							$errMsg = sprintf("Creating ACL Error: No=%d, Msg=%s", $sysConn->ErrorNo(), $sysConn->ErrorMsg());
							wmSysLog($sysSession->cur_func, $this->courseId, $instance , 2, 'auto', $_SERVER['PHP_SELF'], $errMsg);
							die($errMsg);
						}
					}

					// 修改仍存在的 ACL
					$still_works_list = array_intersect($old_lists,$cur_lists);
					if ($still_works_list){
						foreach($acl_lists as $item)
						{
							$x = explode(chr(8), $item, 2);
							if (in_array($x[0], $still_works_list))
							{
								$elements = explode(chr(8), $x[1]);
								$t = array();
								list($t['Big5'], $t['GB2312'], $t['en'], $t['EUC-JP'], $t['user_define']) = explode(chr(9), (get_magic_quotes_gpc() ? stripslashes($elements[0]) : $elements[0]));
								$titles = serialize(array_reverse($t));
								dbSet('WM_acl_list', sprintf("permission=%d,caption='%s'", $elements[1], addslashes($titles)), "acl_id={$x[0]}");

								if ($sysConn->ErrorNo() == 0){
									dbDel('WM_acl_member', "acl_id={$x[0]}");
									$users = preg_split('/\s+/', $elements[3], -1, PREG_SPLIT_NO_EMPTY);
									foreach(explode(',', aclBitmap2Roles($elements[2])) as $role) if ($role) $users[] = '#' . $role;
									foreach($users as $user) dbNew('WM_acl_member', 'acl_id,member', $x[0] . ',"' . $user . '"');
									// 若沒有對象，預設為本課程所有正式生
									if(!count($users)) dbDel('WM_acl_list', "acl_id={$x[0]}");;

									if ($sysConn->Affected_Rows() > 0){
										++$acl_update_row;
									}
								}
								else
									die(sprintf('ERROR: %u: %s', $sysConn->ErrorNo(), $sysConn->ErrorMsg()));
							}
						}
					}
					unset($cur_lists, $new_lists);
				}

				if ($acl_update_row > 0){
					// Flush (delete) any cached recordsets for the SQL statement
					$sysConn->CacheFlush();
				}
			}
		}

		/**
		 * 重新計算學員成績
		 */
		function reCalQTIGrade () {
			global $sysSession, $MSG;
			// MIS#26031 儲存試卷時，重新計算學員成績 - Begin by Small 2012/07/18
			$RS_results = dbGetStMr('WM_qti_' . $this->_QTI_which . '_result','examinee',"exam_id={$this->itemData['exam_id']}");
			if($RS_results && $this->_QTI_which != 'questionnaire')
			{
				while(!$RS_results->EOF)
				{
					$examinee = $RS_results->fields['examinee'];
					reCalculateQTIGrade($examinee, $this->itemData['exam_id'], $this->_QTI_which);
					$RS_results->MoveNext();
				}
			}
			// MIS#26031 儲存試卷時，重新計算學員成績 - End
		}
		/**
		* 儲存試題
		*
		* @param array $data 試題資料
		*
		* @return array array(Error Code, Error Message)<br \>
		*     Error Code => -1: 未通過 ACL 檢查, 0: 成功, > 0: 失敗<br \>
		*     Error Message => 錯誤訊息
		*/
		function saveExam($data) {
			global $sysSession;

			// 檢查 ACL
			$res = $this->checkACL($data['origin'], $data['ident'], $data['ticket']);
			if (!$res) {
				return array(
					'code' => -1,
					'message' => 'fail',
					'data'  => array("errMsg" => 'Illegal Access !')
				);
			}

			// 轉換試題資料
			$this->itemData = $this->dataTransform($data);
			if (isset($this->itemData['code']) && $this->itemData['code'] < 0) {
				return array(
						'code' => -2,
						'message' => 'fail',
						'data'  => array("errMsg" => $this->itemData['errMsg'])
				);
			}

			$this->exam_perm = array(
				array('homework' => 1700400200, 'exam' => 1600400200, 'questionnaire' => 1800300200),
				array('homework' => 1700300100, 'exam' => 1600300100, 'questionnaire' => 0)
			);


			// 儲存
			if ($this->isModify) {
				$saveRtn = $this->updateExam();
			} else {
				$saveRtn = $this->addExam();
				// 將試卷 ID 補回 item
				$this->itemData['exam_id'] = $saveRtn['instance'];
			}

			// 取得本測驗是否支援行動測驗 => 儲存 Begin
			if ($this->_QTI_which === 'exam' && sysEnableAppCourseExam == true) {
				$instance = $saveRtn['instance'];
				$qtiSupportAPP = $this->itemData['qti_support_app'];
				list($appSupportCount) = dbGetStSr('APP_qti_support_app', 'count(*)', "exam_id = {$instance} AND type='{$this->_QTI_which}' AND course_id={$this->courseId}", ADODB_FETCH_NUM);
				if ($appSupportCount == 0) {
					dbNew('APP_qti_support_app', "exam_id, type, course_id, support", "{$instance}, '{$this->_QTI_which}', {$this->courseId},'{$qtiSupportAPP}'");
				} else {
					dbSet('APP_qti_support_app', "support='{$qtiSupportAPP}'", "exam_id = {$instance} AND type='{$this->_QTI_which}' AND course_id={$this->courseId}");
				}
			}
			// 取得本測驗是否支援行動測驗 => 儲存 End
			if ($saveRtn['ErrCode']) {
				$errMsg = $saveRtn['ErrCode'] . ': ' . $saveRtn['ErrorMsg'];
				wmSysLog($sysSession->cur_func, $this->courseId , $saveRtn['instance'] , 1, 'auto', $_SERVER['PHP_SELF'], $errMsg);
			} else {
				if ($this->isModify === true) {
					wmSysLog($sysSession->cur_func, $this->courseId , $saveRtn['instance'] , 0, 'auto', $_SERVER['PHP_SELF'], 'Modify ' . $this->_QTI_which);
					// 與成績系統同步比例及公布日期 start
					if ($saveRtn['isModified'])
					{
						//TODO: function 化
						$grade_types = array('homework' => 1, 'exam' => 2, 'questionnaire' => 3);
						list($grade_pb) = dbGetStSr('WM_grade_list','publish_begin,publish_end','source=' . $grade_types[$this->_QTI_which] . ' and property=' . $this->itemData['exam_id']);
						switch($this->itemData['announce_type'])
						{
							case 'never':
								$d = '9999-12-31 00:00:00';
								break;
							case 'now':
								// $d = $begin_time;
								$d = '1970-01-01 00:00:00';
								break;
							case 'close_time':
								$d = $this->itemData['close_time'];
								break;
							case 'user_define':
								$d = $this->itemData['announce_time'];
								break;
						}
						if($this->itemData['announce_type']!='never')
						{
							// 若答案要公布，則『答案時間小於成績起始時間』或『成績原為不公布』，就要異動成績公布時間
							$exam_pb_time = strtotime($d);
							$grade_pb_time = strtotime($grade_pb);
							if(($exam_pb_time<=$grade_pb_time) || ($grade_pb=='0000-00-00 00:00:00'))
							{
								dbSet('WM_grade_list',
										"title = '{$this->itemData['title']}',percent={$this->itemData['percent']}, publish_begin='{$d}' , publish_end='9999-12-31 00:00:00'",
										'source=' . $grade_types[$this->_QTI_which] . ' and property=' . $this->itemData['exam_id']);
							}else{
								dbSet('WM_grade_list',
										"title = '{$this->itemData['title']}',percent={$this->itemData['percent']}",
										'source=' . $grade_types[$this->_QTI_which] . ' and property=' . $this->itemData['exam_id']);
							}
						}else{
							dbSet('WM_grade_list',
									"title = '{$this->itemData['title']}',percent={$this->itemData['percent']}",
									'source=' . $grade_types[$this->_QTI_which] . ' and property=' . $this->itemData['exam_id']);
						}
					}
					// 與成績系統同步比例及公布日期 end

				} else {
					wmSysLog($sysSession->cur_func, $this->courseId , $saveRtn['instance'] , 0, 'auto', $_SERVER['PHP_SELF'], 'New ' . $this->_QTI_which);
				}
				if ($this->calanderSync === true) {
					$this->calanderSyncHandler($saveRtn['instance']);
				}
			}

			// 處理開放型問卷
			$this->forGuestAclHandler($saveRtn['instance']);
			// 儲存試卷時，重新計算學員成績
			$this->reCalQTIGrade();

			// 回傳問卷資訊，及執行結果
			return array (
				'code' => 0,
				'message' => 'success',
				'data' => array('qti_id' => $saveRtn['instance'])
			);
		}
	}
