<?php
    /**
     * 確認測驗是否支援APP
     *
     * @param integer $exam_id: 測驗編號
     * @param integer $course_id: 課程編號
     *
     * @return boolean true:支援, false:不支援
     **/
    function checkSupportAPP ($exam_id, $course_id, $qtiWhich) {
        $supportValue = dbGetOne('APP_qti_support_app', 'support', "exam_id={$exam_id} AND type='{$qtiWhich}' AND course_id={$course_id}", ADODB_FETCH_NUM);
        return $supportValue == 'Y';
    }

    /**
     * 清除錯誤或是試作的作答紀錄
     *
     * @param string $qti 三合一的哪一種 (exam|questionnaire|homework)
     * @param string $username 帳號
     **/
    function cleanQTIData ($qti, $username) {
        list($nullCount) = dbGetStSr(
            'WM_qti_' . $qti . '_result',
            'count(*)',
            "ISNULL(status) AND ISNULL(content) AND `examinee`='{$username}'",
            ADODB_FETCH_NUM
        );
        if ($nullCount > 0) {
            dbDel(
                'WM_qti_' . $qti . '_result',
                "ISNULL(status) AND ISNULL(content) and `examinee`='{$username}'"
            );
        }
        list($forTA) = dbGetStSr(
            'WM_qti_' . $qti . '_result',
            'count(*)',
            "status='forTA' AND `examinee`='{$username}'",
            ADODB_FETCH_NUM
        );
        if ($forTA > 0) {
            dbDel('WM_qti_' . $qti . '_result', "status='forTA' AND `examinee`='{$username}'");
        }
    }

    /**
     * 取得QTI的試卷列表
     *
     * @param string $unitList 單位代碼
     * @param string $qti 三合一的哪一種 exam|questionnaire|homework
     * @param string $username 帳號
     * @param integer $offset index
     * @param integer $size 筆數
     *
     * @return array 列表資料, 總筆數
     **/
    function getQTIListRecord ($unitList, $qti, $username, $offset = 0, $size = 0) {
        global $sysConn;

        $courseSort = 'ASC';
        if ($qti === 'exam') {
            $courseSort = 'DESC';
        }

        $username = mysql_real_escape_string($username);

        // 過濾 IRS 問卷
        $notIrsWhere = " AND `type` != 5 ";

        $testTable = 'WM_qti_' . $qti . '_test';
        $resultTable = 'WM_qti_' . $qti . '_result';
        $rs = dbGetStMr(
            $testTable .' AS T LEFT JOIN ' . $resultTable . ' AS R ON T.exam_id = R.exam_id AND R.examinee = "' . $username . '"',
            'SQL_CALC_FOUND_ROWS T.*, COUNT(R.exam_id) AS times, max(R.time_id) AS max_time_id',
            "course_id IN ({$unitList}) AND publish='action' {$notIrsWhere} GROUP BY T.exam_id ORDER BY course_id " . $courseSort . ", exam_id DESC, sort ASC, R.time_id DESC limit " . $offset . "," . $size,
            ADODB_FETCH_ASSOC
        );
        $total = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));

        return array('record' => $rs, 'total' => $total);
    }

    /**
     * 驗證是否為可作答的帳號
     *
     **/
    function verifyQTIPermission ($qti, $unitId, $qtiId, $username) {
        global $sysRoles, $sysConn;

        // 驗證三合一題卷是否有指定給該帳號去作答
        $functionId = array('exam' => 1600400200, 'homework' => 1700400200, 'questionnaire' => 1800300200);
        $p = aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable');
        $aclVerified = aclVerifyPermission($functionId[$qti], $p, $unitId, $qtiId, $username);
        $mysqlUsername = mysql_real_escape_string($username);

        if (strlen(strval($unitId)) === 5 && $qti === 'questionnaire') {
            // 是校務問卷則直接回傳true
            return true;
        } else {
            // 如果是課程的三合一，就要確認是否有課程成員的身分
            $level = dbGetOne('WM_term_major','role',"course_id={$unitId} AND username='{$mysqlUsername}'", ADODB_FETCH_NUM);
            $isTeacher = $level & ($sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor']);
            $permit = $level & ($sysRoles['student']);
            if ($aclVerified === 'WM2') {
                // 未指定acl，則具有教師、助教、講師或正式生的身分可以作答
                return ($isTeacher || $permit);
            } else {
                // 有指定acl且也不具教師、助教、講師身分，則直接回傳false
                if (!$aclVerified && !$isTeacher) {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }

    /**
     * 取得作答次數
     *
     * @param string $qti 三合一的哪一種 (exam|questionnaire|homework)
     * @param string $qtiId 試卷編號
     * @param string $username 帳號
     *
     * @return int 作答次數
     **/
    function getQTIAnswerCount ($qti, $qtiId, $username) {
        $times = dbGetOne(
            'WM_qti_' . $qti . '_result',
            'count(*)',
            "exam_id={$qtiId} AND examinee='{$username}'"
        );
        return intval($times);
    }

    /**
     * 取得試卷內題目數量
     *
     * @param string $content 試卷內容
     *
     * @return int 題目路量
     **/
    function getQTIContentItemCount ($content) {
        $totalItems = 0;
        if (strpos($content, '<wm_immediate_random_generate_qti') !== FALSE) {
            $regs = array();
            if (preg_match('!<amount [^>]*>(.*)</amount>!sU', $content, $regs)) {
                $totalItems = intval($regs[1]);
            }
        } else if (strpos($content, '<selection_ordering>') !== FALSE) {
            // 大題亂數取題
            if ($dom = domxml_open_mem(str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $content))) {
                $ctx = xpath_new_context($dom);
                // 1. 先計算全部有幾題
                $totalItems = substr_count($content, '<item '); // 共幾題
                // 2. 取得 section 內有 selection_ordering 的所有題數，並且扣掉
                $result = $ctx->xpath_eval('//section[child::selection_ordering]/item');
                $totalItems -= count($result->nodeset);
                // 3. 取出  selection_ordering 內所指定的題數，並且加入全部的題數
                $result = $ctx->xpath_eval('//section/selection_ordering/selection/selection_number/text()');
                if (count($result->nodeset) > 0) {
                    foreach ($result->nodeset as $node) {
                        $totalItems += intval($node->node_value());
                    }
                }
            }
        } else {
            $totalItems = substr_count($content, '<item '); // 共幾題
        }

        return $totalItems;
    }

    /**
     *  取出試卷標題
     *
     * @param string $title 試卷名稱 (複合式欄位)
     *
     * @return string 標題
     **/
    function qtiPaperTitle ($title) {
        global $sysSession;
        $paperTitle = (strpos($title, 'a:') === 0) ?
            unserialize($title) :
            array(
                'Big5'        => $title,
                'GB2312'      => $title,
                'en'          => $title,
                'EUC-JP'      => $title,
                'user_define' => $title
            );
        return htmlspecialchars(strip_tags($paperTitle[$sysSession->lang]));
    }

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
    function checkAPPExamWhetherTimeout($fields, $now, $times, $isContinue = false, $extra = array(), $appQtiType)
    {
        global $sysConn;

        if ($fields['ta_limit'] == '0') return false;

        static $except_times = array('0000-00-00 00:00:00', '1970-01-01 08:00:00', '9999-12-31 00:00:00');

        $times = intval($times);
        $begin_time = strtotime($fields['begin_time']);
        $close_time = strtotime($fields['close_time']);
        $re = '!^\d{4}([-/]\d{1,2}){2} \d{2}(:\d{2}){2}$!';
        $isTimeout = false;
        $examId = intval($fields['exam_id']);
        $mysqlUsername = mysql_real_escape_string($extra['username']);

        if (
            (preg_match($re, $fields['begin_time']) && !in_array($fields['begin_time'], $except_times) && $begin_time > $now) || // 時間還沒到
            (preg_match($re, $fields['close_time']) && !in_array($fields['close_time'], $except_times) && $close_time <= $now)
        ) {
            // 時間已經過
            return true;
        }

        // 測驗關閉的條件
        if ($appQtiType == 'exam') {
            list($lastTimeId, $itemPerPage) = $sysConn->GetRow(
                'select R.time_id,T.item_per_page from WM_qti_exam_test as T ,WM_qti_exam_result as R ' .
                "where T.exam_id={$examId} and R.exam_id=T.exam_id " .
                "and R.examinee='{$mysqlUsername}' and R.status='break'" .
                'order by R.time_id desc limit 1'
            );

            if ($itemPerPage > 0) {
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

    /**
     * 驗證是否通過測驗
     *
     * @param object $suchExamData 該筆測驗的資料
     * @param integer $times 測驗次數
     * @param integer $thresholdScore 及格分數
     * @param integer $announceType 公布狀態
     * @param string $announceTime 公布時間
     *
     * @return array 通過狀態
     **/
    function verifyPassStatus ($suchExamData, $times, $thresholdScore, $announceType, $announceTime) {
        global $sysSession;
        $passed = 0;
        $passStatus = 0;
        $mysqlUsername = mysql_real_escape_string($sysSession->username);

        if ($thresholdScore === -1) {
            return $passResult = array('passed' => 0, 'passStatus' => 2);
        }

        // 測驗的時間有時分秒
        $now = strtotime(date('Y-m-d H:i:s'));
        $closeTime = $suchExamData->fields['close_time'];

        if (in_array($suchExamData->fields['count_type'], array('first', 'last'))) {
            switch ($suchExamData->fields['count_type']) {
                case 'first':
                    list($scoreTmp) = dbGetStSr(
                        'WM_qti_exam_result',
                        'score',
                        "exam_id={$suchExamData->fields['exam_id']} AND examinee='{$mysqlUsername}' AND `status`!='break'" .
                        " ORDER BY time_id ASC LIMIT 1",
                        ADODB_FETCH_NUM
                    );
                    $score = round($scoreTmp, 2);
                    $passed = (($times > 0) && ($score >= $thresholdScore)) ? 1 : 0;
                    break;
                case 'last':
                    list($scoreTmp) = dbGetStSr(
                        'WM_qti_exam_result',
                        'score',
                        "exam_id={$suchExamData->fields['exam_id']} AND examinee='{$mysqlUsername}' AND `status`!='break'" .
                        " ORDER BY time_id DESC LIMIT 1",
                        ADODB_FETCH_NUM
                    );
                    $score = round($scoreTmp, 2);
                    $passed = (($times > 0) && ($score >= $thresholdScore)) ? 1 : 0;
                    break;
            }
        } else if (in_array($suchExamData->fields['count_type'], array('max', 'min', 'average'))) {
            list($scoreTimes, $scoreSum, $scoreMax, $scoreMin) = dbGetStSr(
                'WM_qti_exam_result',
                'count(*), SUM(score), MAX(score), MIN(score)',
                "exam_id={$suchExamData->fields['exam_id']} AND examinee='{$mysqlUsername}' AND `status`!='break'",
                ADODB_FETCH_NUM
            );

            switch ($suchExamData->fields['count_type']) {
                case 'max':
                    $score = round($scoreMax, 2);
                    $passed = (($times > 0) && ($score >= $thresholdScore)) ? 1 : 0;
                    break;
                case 'min':
                    $score = round($scoreMin, 2);
                    $passed = (($times > 0) && ($score >= $thresholdScore)) ? 1 : 0;
                    break;
                case 'average':
                    $score = round($scoreSum / max(1, $scoreTimes), 2);
                    $passed = (($times > 0) && ($score >= $thresholdScore)) ? 1 : 0;
                    break;
            }
        } else if ($suchExamData->fields['count_type'] === 'none') {
            $passed = 1;
        }

        if ($times === 0) {
            // 未測驗
            $passStatus = 1;
        } else if ($thresholdScore === 0) {
            // 未設定 (沒有設定及格分數)
            $passStatus = 2;
        } else if (
            $announceType === 0
            || ($announceType === 2 && strtotime($now) < strtotime($closeTime))
            || ($announceType === 3 && strtotime($now) < strtotime($announceTime))
        ) {
            // 未公布 (公布設定為不公布 或 關閉試卷公布要比對試卷結束時間 或 自訂時間)
            $passStatus = 3;
        } else if ($passed === 1) {
            // 及格
            $passStatus = 4;
        } else if ($passed === 0) {
            // 不及格
            $passStatus = 5;
        }

        $passResult = array('passed' => $passed, 'passStatus' => $passStatus);
        return $passResult;
    }