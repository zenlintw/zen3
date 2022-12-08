<?php
/**
 * 取得 QTI 試卷統計結果
 *
 *  回傳值
 *     2: 使用者在此課程沒有教師、講師、助教權限
 *     3: type 須符合 qti
 *     4: 沒設定試卷編號或不符合規則的試卷編號
 *     5: 取不到試卷
 *     6: 有設定 checkNum 前端筆數與資料庫一樣時不回傳資料
 */
include_once(dirname(__FILE__) . '/action.class.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(PATH_LIB . 'qti.php');

/**
 * 取得課程測驗結果統計
 */
class GetQuestionnaireResultAction extends baseAction
{
    var $_mysqlUsername = '',
        $_qtiTypes = array('exam', 'questionnaire'),    // 'homework'
        $_qtiType,
        $_qtiId,
        $_courseId,
        $_checkNum,
        $_textCloudTmp = array(),
        $_shortAnswer = array(
            'size' => 20,
            'length' => 15,
            'list' => array()
        );

    function dataHandler($data) {
        global $sysSession;

        // appache 會自動解 urlencode ，故前端 encodeURIComponent 加密兩次再傳進來
        $apacheVersion = getApacheVersion();
        if (strpos($apacheVersion, '2.4') !== false) {
            // apache2.4 已不會自動 decode，需做兩次解密
            $data['eid'] = rawurldecode(rawurldecode($data['eid']));
        } else {
            $data['eid'] = rawurldecode($data['eid']);
        }

        // 資料解密
        $aesCode = intval($data['aesCode']);
        if ($aesCode > 0) {
            $data['eid'] = APPEncrypt::decrypt(trim($data['eid']), $aesCode);
        }

        // type 須符合 qti
        if (!in_array($data['type'], $this->_qtiTypes)) {
            $this->returnHandler(3, 'fail');
        }
        // 沒有設定 eid 或不符合規則的 eid
        $data['eid'] = intval($data['eid']);
        if (!($data['eid'] > 100000000 && $data['eid'] <= 999999999)) {
            $this->returnHandler(4, 'fail');
        }

        // 取得課程編號
        $courseId = dbGetOne(
            sprintf("`WM_qti_%s_test`", $data['type']),
            "`course_id`",
            sprintf("`exam_id` = '%s'", $data['eid'])
        );

        if ($courseId === false) {
            // 試卷不存在
            $this->returnHandler(5, 'fail');
        }

        $this->_courseId = $courseId;
        $this->_qtiType = $data['type'];
        $this->_qtiId =  $data['eid'];
        $this->_checkNum =  intval($data['checkNum']);
        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);

        define('API_QTI_which', $this->_qtiType);
        define('QTI_env', 'teach');
        require_once(PATH_LIB . 'qti.php');
    }

    function aclCheck($username) {
        global $sysRoles, $sysSession;

        // 確認使用者權限
        $aclCheck = aclCheckRole($username, $sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant'], $this->_courseId);
        if (!$aclCheck) {
            $this->returnHandler(2, 'fail');
        }

        // 紀錄進入課程編號
        $sysSession->course_id = intval($this->_courseId);
        $sysSession->restore();
    }

    /**
     * 將使用者答案統計到試卷內
     * NOTICE: 為減少 parse 試卷，先將文字雲的收集作在 setAnswerIntoTest，修改須注意
     * @param $test
     * @param $examinee
     * @param $userAnswer
     */
    function setAnswerIntoTest(&$test, $examinee = "", $userAnswer = array()) {
        $answer = array();
        $isFirstSet = $examinee === "";

        // 取出使用者itemid與答案
        foreach ($userAnswer as $item) {
            // 將答案轉為字串
            if (isset($item["userAnswer"])) {
                foreach($item['userAnswer'] AS $k => $v) {
                    $item['userAnswer'][$k] = trim($v);
                }
            }
            $answer[$item["item_id"]] = $item["userAnswer"];
        }

        foreach ($test as $itemKey => $itemVal) {
            if ($isFirstSet) {
                // 文字雲增加文字 (只在 parser 試卷的時候紀錄)
                $this->_textCloudTmp[] = $itemVal['text'];

                // 將解答轉為字串
                if (isset($itemVal['quizAnswer'])) {
                    foreach($itemVal['quizAnswer'] AS $k => $v) {
                        $test[$itemKey]['quizAnswer'][$k] = trim($v);
                    }
                }

                // 去除試卷統計的使用者答案
                if (isset($itemVal['userAnswer'])) {
                    unset($test[$itemKey]['userAnswer']);
                }

                // 去除配合題答案選項
                if (isset($itemVal['prompt'])) {
                    unset($test[$itemKey]['prompt']);
                }

            }

            // 是非、單選、複選要比對使用者作答與正確解答
            if (in_array($itemVal['type'], array(1, 2, 3))) {
                if ($isFirstSet) {
                    // 紀錄正確作答人數
                    $test[$itemKey]['correct_num'] = 0;
                } else {
                    if ($itemVal['quizAnswer'] === $answer[$itemVal['item_id']]) {
                        $test[$itemKey]['correct_num']++;
                    }
                }
            }

            switch($itemVal['type']) {
                case 1:
                    // 是非題
                    if (!isset($itemVal['optionals'])) {
                        // 建立是非選項
                        $itemVal['optionals'] =  array(
                            array('text' => 'O'),
                            array('text' => 'X')
                        );
                        $test[$itemKey]['optionals'] =  $itemVal['optionals'];
                    }
                    foreach ($itemVal['optionals'] as $opKey => $opVal) {
                        if ($isFirstSet) {
                            $test[$itemKey]['optionals'][$opKey]['select_amount'] = 0;
                            $test[$itemKey]['optionals'][$opKey]['select_list'] = array();
                        }

                        if ($opVal['text'] === $answer[$itemVal['item_id']][0]) {
                            // 選項計數加 1
                            $test[$itemKey]['optionals'][$opKey]['select_amount']++;
                            $test[$itemKey]['optionals'][$opKey]['select_list'][] = $examinee;
                        }
                    }
                    break;
                case 2:
                case 3:
                    // 單選題、複選題
                    foreach ($itemVal['optionals'] as $opKey => $opVal) {
                        if ($isFirstSet) {
                            $test[$itemKey]['optionals'][$opKey]['select_amount'] = 0;
                            $test[$itemKey]['optionals'][$opKey]['select_list'] = array();
                        }

                        if (in_array($opKey + 1, $answer[$itemVal['item_id']])) {
                            // 選項計數加 1
                            $test[$itemKey]['optionals'][$opKey]['select_amount']++;
                            $test[$itemKey]['optionals'][$opKey]['select_list'][] = $examinee;
                        }
                    }
                    break;
                case 4:
                    // TODO: 填充題處理
                    break;
                case 5:
                    // 簡答題
                    if (!$isFirstSet) {
                        // 文字雲增加文字
                        $this->_textCloudTmp[] = $answer[$itemVal['item_id']][0];
                        $this->recordShortAnswer($answer[$itemVal['item_id']][0]);

                        $test[$itemKey]['short_answer'][] = array(
                            'username' => $examinee,
                            'content' =>  $answer[$itemVal['item_id']][0]
                        );
                    }
                    break;
                case 6:
                    // TODO: 配合題處理
                    break;
            }
        }
    }

    function recordShortAnswer($text) {
        $size = $this->_shortAnswer['size'];
        $maxLength = $this->_shortAnswer['length'];
        if (count($this->_shortAnswer['list']) > $size) {
            return false;
        }
        if (mb_strlen($text) > $maxLength) {
            $this->_shortAnswer['list'][] = mb_substr($text, 0, $maxLength, 'utf8');
        } else {
            $this->_shortAnswer['list'][] = $text;
        }

        return true;
    }

    function getQtiResult() {
        global $sysSession;

        $examinees = array();
        $threshold_list = array();
        $textCloud = array();

        $qtiLib = new Qti();
        // 取得 qti 資訊
        $qtiDetail = new QtiResult();
        $qtiDetail->init($this->_qtiType);
        $qtiDetail->getQtiDetail($this->_qtiId);

        // 解析試卷
        $itemInfo = $qtiLib->transformer($qtiDetail->qtiData['dom'], $qtiDetail->qtiData['ctx'], $this->_qtiType);

        // 移除正確解答
        $getAnswer = ($this->_qtiType === 'exam') ? true : false;
        foreach($itemInfo as $key => $item) {
            if (!$getAnswer) {
                $itemInfo[$key]['quizAnswer'] = array();
                $itemInfo[$key]['userAnswer'] = array();
            }
        }
	    // 先跑一次設定將是非題的選項補上
        $this->setAnswerIntoTest($itemInfo);
        // 及格成績採用當前試卷資訊
        $threshold_score = intval($qtiDetail->qtiData['root']->get_attribute('threshold_score'));

        // 統計學生作答資訊
        $studentData = $qtiDetail->getUserQtiData();
        $RS = $studentData['data']['list'];

        // checkNum 大於 0 才作判斷，
        $hasNewRow = !($this->_checkNum > 0 && $this->_checkNum === $studentData['data']['total']);
        if ($hasNewRow === false) {
            // 前端筆數與資料庫相同，不作回傳
            $this->returnHandler(6, 'success', array('msg' => 'There is no new data.'));
        } else if ($RS && $studentData['data']['total'] > 0) {
            while ($fields = $RS->FetchRow()) {
                $examinee = $fields["examinee"];
                $content_xml = $fields["content"];
                $score = intval($fields["score"]);

                $content = mb_convert_encoding($content_xml, 'UTF-8', 'UTF-8');
                if (!$dom = domxml_open_mem(str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $content))) {
//                    $this->returnHandler(5, 'fail', array("errMsg" => "xml parser error: " . $examinee));
                    $errMsg = sprintf("QTI: %s-%d xml parser error: %s", $this->_qtiType, $this->_qtiId, $examinee);
                    appSysLog(999999023, $sysSession->school_id, 0, 1, 'other', $_SERVER['PHP_SELF'], $errMsg, $this->_mysqlUsername);
                    continue;
                }
                $ctx = xpath_new_context($dom);

                // 紀錄作答學生
                $examinees[] = $examinee;

                // 紀錄作答人員及格狀態
                if ($threshold_score !== "") {
                    $threshold_list[] = array(
                        "examinee" => $examinee,
                        "score" => $score,
                        "passed" => ($score >= $threshold_score)
                    );
                }

                // 將 xml 轉成 json array
                $userResult = $qtiLib->transformer($dom, $ctx, $this->_qtiType);

                // 紀錄使用者答案
                $this->setAnswerIntoTest($itemInfo, $examinee, $userResult);
            }
        }

        // 製作文字雲
//        $textCloud = $this->wordFreq(implode(" ", $this->_textCloudTmp));

        // 取得簡答作答文字，並加上權重
        foreach ($this->_shortAnswer['list'] AS $shortAnswer) {
            $textCloud[] = array($shortAnswer, 1);
        }

        return array(
            'examinees' => $examinees,
            'threshold_score' => $threshold_score,
            'threshold_list' => $threshold_list,
            'items' => $itemInfo,
            'text_cloud' => $textCloud
        );
    }

    /**
     * 使用 linux 安裝的 wordFreq 作文字雲的計算
     */
    function wordFreq ($text) {
        $textCloud = array();

        // TODO: 確認 單雙引號正常
        $text = addslashes($text);
        exec('echo "'. $text .'" | /usr/bin/wordfreq --max-length=4 -', $output, $return_var);
        if ($return_var === 0) {
            // 有些系統設定超過一百行的輸出會被省略，且在最後一行顯示 "... 1xx more item ]"
            if (count($output) > 70) {
                $output[count($output) - 1] = " ]";
                $output[count($output) - 2] = str_replace("],", "]", $output[count($output) - 2]);
            }
            $textCloud = implode('', $output);
            $textCloud = str_replace('\'', '"', $textCloud);
            $textCloud = json_decode($textCloud);
        }

        return $textCloud;
    }
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        // 處理網址參數
        $inputData = $_GET;
        $this->dataHandler($inputData);
        // 驗證使用權限
        $this->aclCheck($this->_mysqlUsername);
        // 取得統計
        $returnData = $this->getQtiResult();

        $this->returnHandler(0, "success", $returnData);
    }
}