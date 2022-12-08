<?php
/**
 * 判斷三合一的 instance 是否可以存取(日期與次數)
 *
 * @param   array       $fields     三合一 instance 的 record
 * @param   int         $now        目前時間的 unix_timestamp 值
 * @param   int         $times      限定次數
 * @param   bool        $isContinue 能否繼續續考 true: 可以續考，false: 不可續考
 * @param   array       $extra
 *         username   帳號
 *         last_stat  最後測驗的狀態
 * @return  bool                    true=已經不能存取；false=可以存取
 */
function checkExamWhetherTimeout($fields, $now, $times, $isContinue = false, $extra = array())
{
	global $sysConn,$isContinue;

	if ($fields['ta_limit'] == '0') return false;

	static $except_times = array('0000-00-00 00:00:00', '1970-01-01 08:00:00', '9999-12-31 00:00:00');

	$times = intval($times);
	
	// print($extra['isgroup'].'_'.$times.'<br>');
	$begin_time = strtotime($fields['begin_time']);
	$close_time = strtotime($fields['close_time']);
    $delay_time = strtotime($fields['delay_time']);
        
    // 繳交日期
    $do_time    = strtotime($fields['do_time']);

    // 群組作業抓取作答時間
    if ( $extra['isgroup'] && $times > 0 && empty($do_time)) {
    	
        list($do_time) = dbGetStsr('WM_qti_homework_result', 'begin_time', "exam_id=$fields[exam_id]", ADODB_FETCH_NUM);
        $fields['do_time'] = $do_time;
        $do_time = strtotime($do_time);

    }
        
    // 遇到群組作業改為取logtime，才是真正的do_time
    global $sysSession;
    if (empty($_COOKIE['show_me_info']) === false) {
        echo '<pre>';
        var_dump(QTI_which);
        var_dump($extra['isgroup']);
        var_dump($extra['function_id']);
        var_dump($sysSession->course_id);
        echo '</pre>';
    }
        
	$re = '!^\d{4}([-/]\d{1,2}){2} \d{2}(:\d{2}){2}$!';
        
        /*if (QTI_which === 'homework' && $extra['isgroup'] === TRUE) {

            global $sysSession;
            if (empty($_COOKIE['show_me_info']) === false) {
                echo '<pre>';
                var_dump('群組作業');
                var_dump('繳交時間（資料表記錄的群組資料）', $fields['do_time']);
                echo '</pre>';
            }   

            // 老師最後一次清除作業的時間
            list($clearHomeworkTime) = $sysConn->GetRow(
                    'SELECT MAX(log_time) log_time FROM WM_log_teacher ' .
                    "WHERE function_id = '1700200300' " .
                    "AND department_id = '{$sysSession->course_id}' " .
                    "AND instance = '0' " .
                    "AND note = 'remove homework results and grades:{$fields['exam_id']}' " .
                    "AND script_name = '/teach/homework/exam_reset.php' " .
                    'ORDER BY log_time DESC'
            );
            if (empty($_COOKIE['show_me_info']) === false) {
                echo '<pre>';
                var_dump('log老師最後一次清除作業的時間', $clearHomeworkTime);
                echo '</pre>';
            }   
            $conditionEffectiveDate = '';
            if (preg_match($re, $clearHomeworkTime)) {
                $conditionEffectiveDate = "AND log_time > '{$clearHomeworkTime}'";
            }

            // 正常作答區間
            list($normalSubmitTime) = $sysConn->GetRow(
                    'SELECT log_time FROM WM_log_classroom ' .
                    "WHERE function_id = '{$extra['function_id']}' " .
                    "AND username = '{$sysSession->username}' " .
                    "AND log_time >= '{$fields['begin_time']}' " .
                    "AND log_time <= '{$fields['close_time']}' " .
                    $conditionEffectiveDate .
                    "AND department_id = '{$sysSession->course_id}' " .
                    "AND instance = '{$fields['exam_id']}' " .
                    "AND note = 'homework finish!' " .
                    "AND script_name = '/learn/homework/save_answer.php' " .
                    'ORDER BY log_time ASC LIMIT 1'
            );
            if (empty($_COOKIE['show_me_info']) === false) {
                echo '<pre>';
                var_dump('log正常作答區間的繳交時間', $normalSubmitTime);
                echo '</pre>';
            }   

            // 補繳區間
            list($delaySubmitTime) = $sysConn->GetRow(
                    'SELECT log_time FROM WM_log_classroom ' .
                    "WHERE function_id = '{$extra['function_id']}' " .
                    "AND username = '{$sysSession->username}' " .
                    "AND log_time >= '{$fields['close_time']}' " .
                    "AND log_time <= '{$fields['delay_time']}' " .
                    $conditionEffectiveDate .
                    "AND department_id = '{$sysSession->course_id}' " .
                    "AND instance = '{$fields['exam_id']}' " .
                    "AND note = 'homework finish!' " .
                    "AND script_name = '/learn/homework/save_answer.php' " .
                    'ORDER BY log_time ASC LIMIT 1'
            );
            if (empty($_COOKIE['show_me_info']) === false) {
                echo '<pre>';
                var_dump('log補繳區間的繳交時間', $delaySubmitTime);
                echo '</pre>';
            }   

            if (empty($normalSubmitTime) === FALSE) {
                $actualTime = $normalSubmitTime;
            } else {
                $actualTime = $delaySubmitTime;
            }
            if (empty($_COOKIE['show_me_info']) === false) {
                echo '<pre>';
                var_dump('log繳交時間（真實狀況）', $actualTime);
                echo '</pre>';
            }   
            
            // 驗證是否符合時間格式
            if (preg_match($re, $actualTime)) {
                $fields['do_time'] = $actualTime;
                $do_time = strtotime($actualTime);
            } else {
                $fields['do_time'] = 0;
                $do_time = strtotime($fields['do_time']);
            }
        }*/

    // 現在是否在補繳期間，如果是更換起迄換成 結束日～補繳期限
    if ($now > $close_time && $now <= $delay_time) {
        $begin_time = $close_time;
        $close_time = $delay_time;
    }
	$isTimeout = false;

	if (
		(preg_match($re, $fields['begin_time']) && !in_array($fields['begin_time'], $except_times) && $begin_time > $now) || // 時間還沒到
		(preg_match($re, $fields['close_time']) && !in_array($fields['close_time'], $except_times) && $close_time <= $now) ||
		(preg_match($re, $fields['do_time']) && $do_time <= strtotime($fields['close_time']) && strtotime($fields['close_time']) <= $now && !in_array($fields['delay_time'], $except_times))
	) {
		// 時間已經過
		$isTimeout = true;
	}

	// 測驗關閉的條件
    $QTI_which = (defined('XMLAPI') && XMLAPI) ? API_QTI_which : QTI_which;
	if ($QTI_which == 'exam') {
		list($lastTimeId, $itemPerPage) = $sysConn->GetRow(
			'select R.time_id,T.item_per_page from WM_qti_exam_test as T ,WM_qti_exam_result as R ' .
			"where T.exam_id={$fields['exam_id']} and R.exam_id=T.exam_id " .
			"and R.examinee='{$extra['username']}' and R.status='break'" .
			'order by R.time_id desc limit 1'
		);

		if ($itemPerPage > 0 && $fields['do_times'] > 1) {
			// 啟用續考
			if (!$isTimeout && ($extra['last_stat'] != 'break')) { // 在考試期間，就看考試的次數與狀態
				$isTimeout = ($fields['do_times'] && $times >= intval($fields['do_times']));
			}
		} else {
			// 無續考
			$isTimeout |= ($fields['do_times'] && $times >= intval($fields['do_times'])); // 已經考過了？
		}

		$isContinue = (!$isTimeout && $times && ($lastTimeId == $times) && ($itemPerPage > 0));
	} else {
		$isTimeout |= ($times && $fields['modifiable'] == 'N'); // 已經做過且不可修改
	}

	return $isTimeout;
}

function isNowOverHomeworkEndTime($exam_id) {
    
    $fields = dbGetRow('WM_qti_homework_test','*',sprintf('exam_id=%d',$exam_id),ADODB_FETCH_ASSOC);
    $except_times = array('0000-00-00 00:00:00', '1970-01-01 08:00:00', '9999-12-31 00:00:00');
    $re = '!^\d{4}([-/]\d{1,2}){2} \d{2}(:\d{2}){2}$!';
    
    if ((preg_match($re, $fields['close_time'])) && !in_array($fields['close_time'], $except_times)
    ) {
        $close_time = strtotime($fields['close_time']);
        $now = time();
        //已逾期
        if ($close_time < $now) {
            return true;
        }
    }
    
    return false;
}
