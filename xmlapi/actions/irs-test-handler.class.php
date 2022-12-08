<?php
/**
 * 新增 QTI 題目
 *
 *  回傳值
 *     2: 使用者在此課程沒有教師、講師、助教權限
 *     3: type 須符合 qti
 *     4: 沒設定課程編號或不符合規則的課程編號
 *     5: 沒設定試卷編號或不符合規則的試卷編號
 *     6: 新建試卷沒有帶試卷資訊或題目資訊，或解密失敗 exam_id 變為 0
 *     7: 題目新增失敗
 *     8: 取不到試卷
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/teach/exam/item_create_lib.php');
require_once(sysDocumentRoot . '/teach/exam/exam_create_lib.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

// 避免timeout
set_time_limit(0);
// 避免連線斷線後，後端處理也中斷
ignore_user_abort(false);

class IrsTestHandlerAction extends baseAction
{
    var $_courseId,
        $_mysqlUsername,
        $_validWebService = false,
        $_actions = array('prepare', 'action', 'close', 'republish', 'copy'),
        $_qtiTypes = array('exam', 'questionnaire'),
        $_qtiType,
        $_qtiId;

    /**
     * 關閉課程中正在進行的愛上互動
     * @param $courseId
     */
    function closeProgressingTests ($courseId) {
        global $sysSession;

        if (!$courseId) {
            $courseId = $sysSession->courseId;
        }
        $irsWhere = ' (`type` = 5 || `type` = 6) ';

        foreach($this->_qtiTypes AS $qtiType) {
            dbSet(
                sprintf('WM_qti_%s_test', $qtiType),
                '`publish` = "close", `close_time` = NOW()',
                sprintf(
                    '`course_id` = %d AND `publish` = "action" AND %s',
                    $courseId,
                    $irsWhere
                )
            );
        }
    }
    /**
     * 複製試卷
     * @param integer $exam_id
     * @param array $settings 複製的試卷要修改的設定
     */
    function copyExam($exam_id, $settings) {
        // TODO: 若未來要複製到其他課程，需參考 exam_copy.php 的程式，加到 exam_create_lib.php 來引用
        global $sysConn;
        $newExamId = 0;

        $record = dbGetRow(
            sprintf('WM_qti_%s_test', $this->_qtiType),
            '*',
            sprintf('exam_id = %d',  intval($exam_id))
        );

        if ($record) {
            // 開始拷貝試卷
            // 清除原試卷編號
            $record['exam_id'] = 'NULL';

            // 試卷名稱補上 COPY 字眼
            $title = unserialize($record['title']);
            foreach ($title AS $lang => $caption) {
                $title[$lang] = 'COPY_' . trim($caption);
            }
            $record['title'] = $this->captionArray2Serialize($title);

            // 其他設定
            foreach ($settings AS $column => $value) {
                if ($column === 'title') {
                    $record[$column] = $value;
                } else {
                    $record[$column] = mysql_real_escape_string($value);
                }
            }

            $sysConn->AutoExecute('WM_qti_' . $this->_qtiType . '_test', $record, 'INSERT');
            $newExamId = $sysConn->Insert_ID();
        }
        return $newExamId;
    }

    /**
     * 將語系的陣列轉為 serialize 並統一輸出格式
     * @param $title
     * @return string
     */
    function captionArray2Serialize ($title) {
        $langList = array('Big5', 'GB2312', 'en', 'EUC-JP', 'user_define');
        $caption = array();

        foreach($langList AS $lang) {
            $caption[$lang] = empty($title[$lang]) ? '' : mysql_real_escape_string(trim($title[$lang]));
        }

        return serialize($caption);
    }
    /**
     * 修改試卷
     * @param $examData
     * @param $modifyData
     * @return mixed
     */
    function modifyData($examData, $modifyData) {
        $enableValue = array("begin_time", "close_time", "publish");
        foreach($examData AS $key => $val) {
            // 原資料預先處理
            switch($key) {
                case 'title':
                    $examData[$key] = getCaption($val);
                    break;
                case 'setting':
                    $examData[$key] = array(
                        "upload" => strpos($examData['setting'], 'upload') ? true : false,
                        "anonymity" => strpos($examData['setting'], 'anonymity') ? true : false
                    );
                    break;
            }
            // TODO: 目前只提供開關問卷使用，如未來要開放修改其他則刪除下面判斷
            if (!in_array($key, $enableValue)) {
                continue;
            }
            // 修改資料
            if (isset($modifyData[$key])) {
                $examData[$key] = $modifyData[$key];
            }
        }
        // 將試卷測定為支援 APP
        $examData["qti_support_app"] = "Y";
        return $examData;
    }
    /**
     * 替換節點內容
     * @param $node
     * @param $new_content
     */
    function replace_content( &$node, $new_content )
    {
        $kids = &$node->child_nodes();
        foreach ( $kids as $kid )
            if ( $kid->node_type() == XML_TEXT_NODE )
                $node->remove_child ($kid);
        $node->set_content($new_content);
    }
    /**
     * 移除試卷中已被刪除的題目
     * @param $examDetail
     * @param $examType
     * @return mixed|string
     */
    function removeUnuseItem($examDetail, $examType) {
        global $sysConn;
        $dom = @domxml_open_mem(preg_replace('/xmlns="[^"]+"/', '', $examDetail));
        if ($dom) {
            $ctx = xpath_new_context($dom);
            $node = $ctx->xpath_eval('/questestinterop/item');

            foreach($node->nodeset as $nodes){
                $nodes_2 = $nodes->get_content();
                $pattern = "/<td>(.*?)<\/td>/i";
                preg_match($pattern,$nodes_2,$out);
                list($id_content)=$sysConn->GetCol(sprintf('select content from WM_qti_%s_item where ident in ("%s")', $examType, $nodes->get_attribute('id')));
                $id_content = @domxml_open_mem(preg_replace('/xmlns="[^"]+"/', '', $id_content));
                $ctx1 = xpath_new_context($id_content);
                $id_node = $ctx1->xpath_eval('/item');
                foreach($id_node->nodeset as $id_nodes){
                    $id_nodes_2 = $id_nodes->get_content();
                    $pattern = "/<p>(.*?)<\/p>/i";
                    preg_match($pattern,$id_nodes_2,$out2);
                }
                $new_content =  str_replace($out[0],'<td>'.htmlspecialchars($out2[1]).'</td>',$nodes_2);
                $this->replace_content($nodes,$new_content);

            }
            $examDetail = $dom->dump_mem(true);
        }

        // 將已刪除的題目，自卷中移除
        if (preg_match_all('/<item [^>]*id="(\w+)"/U', $examDetail, $regs, PREG_PATTERN_ORDER))
        {
            $exists_item = $sysConn->GetCol(sprintf('select ident from WM_qti_%s_item where ident in ("%s")', $examType, implode('","', $regs[1])));
            $removed = array_diff($regs[1], $exists_item);
            if (count($removed))
            {
                $pattern = explode(chr(9), '!<item [^>]*id="' . implode('"[^>]*>[^<]*</item>!isU' . chr(9) . '!<item [^>]*id="', $removed) . '"[^>]*>[^<]*</item>!isU');
                $replace = array_pad(array(), count($pattern), '');
                $examDetail = preg_replace($pattern, $replace, $examDetail);
            }
        }

        $examDetail = strtr(
            $examDetail,
            array(
                "'"  => "&#39;",
                "\n" => '',
                "\r" => '',
                '\\' => '\\\\',
                '//' => '\/\/',
                'item id' => 'item xmlns="" id',
                'section id' => 'section xmlns="" id'
            )
        );
        $examDetail = str_replace('item id','item xmlns="" id',$examDetail);
        return $examDetail;
    }
    /**
     * 清除 jasmine 測試資料
     * @param $examList
     * @param $itemList
     */
    function deleteTestDatas($qtiType) {
        $testString = "_[IRS)_{test)";
        // 資料處理
        // 刪除試卷
        dbDel(
            sprintf("`WM_qti_%s_test`", $qtiType),
            sprintf("LOCATE('%s', `title`)", $testString)
        );
        // 刪除題目
        dbDel(
            sprintf("`WM_qti_%s_item`", $qtiType),
            sprintf("LOCATE('%s', `title`)", $testString)
        );
    }
    /**
     * 將 api 的 QTI 試卷轉換成 lib 處理所需要的結構
     * @param $data array QTI 試卷
     * @return array
     */
    function testDataTransform($data) {
        $returnData = array();
        $now = date("Y-m-d H:i:s");
        $unlimitedTime = "9999-12-31 00:00:00";

        $test = $data['exam_info'];
        if ($test !== null) {
            // 測驗詳細資料
            $returnData = array(
                "title" => $test['title'],
                "ex_type" => 5,    // IRS(愛上互動) 為 5
                "notice" => "由 APP IRS 功能產生",
                "do_interval" => $test['interval'],
                "threshold_score" => $test['threshold_score'],
                "qti_support_app" => "Y",
                "modifiable" => "Y"
            );
        }

        // 依據 action 補上起訖時間
        switch ($data['action']) {
            case 'close':
                $returnData['close_time'] = $now;
                break;
            default:
                // action、prepare
                $returnData['begin_time'] = $now;
                $returnData['close_time'] = $unlimitedTime;
        }

        // 測驗設定
        $returnData['publish'] = $data['action'];
        return $returnData;
    }
    /**
     * 將 api 的 QTI 題目轉換成 lib 處理所需要的結構
     * @param $item array QTI題目
     * @param $type string 新增或修改(create | modify)
     * @return array
     */
    function itemDataTransform($item, $type = 'create') {
        global $sysSession;
        $itemData = array();
        if (intval($item['type']) === 1) {
            // webservice 傳值為 O、X，PRO處理為 T、F
            $item['answer'] = ($item['answer'] === "O") ? "T" : "F";
        }
        if (count($item) > 0) {
                // topic_(?)  (?)照題目類型編號
            $itemData = array(
//                'topic_1'       => '',
                'isHTML'        => 1,
                'topic'         => $item['title'],
                // 複選題須將答案轉陣列
                'answer'        => (intval($item['type']) === 3) ? explode(",", $item['answer']) : $item['answer'],   //預設答案
                // 分類應該不需要
//                'version'       => '',// 版
//                'volume'        => '',// 冊
//                'chapter'       => '',// 章
//                'paragraph'     => '',// 節
//                'section'       => '',// 段
//                'repeat'        => '',   // 是否連續新增
                /*
                 * 題目類型?
                 * 是非: 1; 單選: 2; 多選: 3; 填充: 4; 申論: 5; 配合: 6;
                 */
                'type'          => $item['type'],
                'ticket'        => $sysSession->ticket
            );
            // 題目附檔
            if (count($item['attaches']) > 0) {
                $itemData['files']['topic_files'] = $item['attaches'];
            }
            // 選項在單選、多選、配合題型才有
            if (in_array($item['type'], array(2, 3, 6))) {
                for ($i = 0; $i < count($item['options']); $i++) {
                    $itemData['render_choices'][$i] = $item['options'][$i]['text'];
                    $itemData['files']['render_choice_files'][$i] = $item['options'][$i]['attaches'];
                }
            }
            if ($type === 'create') {
                // 新增
                $itemData['gets'] = '';
            } else if ($type === 'modify') {
                // TODO: 修改
//                $itemData['origin'];
//                $itemData['ident']; // item_id
            }
        }
        return $itemData;
    }
    /**
     * 驗證使用者使用權限
     * @param $username
     */
    function aclCheck ($username) {
        global $sysSession, $sysRoles;

        $aclCheck = aclCheckRole($username, $sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant'], $this->_courseId);
        if (!$aclCheck) {
            $this->returnHandler(2, "fail");
        }
        // 有權限則進入課程
        $sysSession->course_id = $this->_courseId;
        $sysSession->restore();
    }
    /**
     * 處理接收的資料
     * @param $data
     */
    function dataHandler (&$data) {
        global $sysSession;

        // 資料解密
        $aesCode = intval($data['aesCode']);
        $data['course_id'] = intval(APPEncrypt::decrypt(trim($data['course_id']), $aesCode));
        $data['exam_id'] = (isset($data['exam_id'])) ? intval(APPEncrypt::decrypt(trim($data['exam_id']), $aesCode)) : 0;

        // type 須符合 qti
        if (!in_array($data['exam_type'], $this->_qtiTypes)) {
            $this->returnHandler(3, 'fail');
        }
        // 沒有設定 cid 或不符合規則的 cid
        if (!($data['course_id'] > 10000000 && $data['course_id'] <= 99999999) ) {
            $this->returnHandler(4, 'fail');
        }
        // 沒有設定 eid 或不符合規則的 eid
        if ($data['exam_id'] !== 0 && !($data['exam_id'] > 100000000 && $data['exam_id'] <= 999999999)) {
            $this->returnHandler(5, 'fail');
        }
        // exam_id 為 0 時新建試卷，需判斷 exam_info、question_info 是否有值
        if ($data['exam_id'] === 0 &&
            (!isset($data['exam_info']) || !isset($data['question_info']) || count($data['question_info']) === 0)) {
            $this->returnHandler(6, 'fail');
        }

        // 判斷 action 是否正確
        if (!in_array($data['action'], $this->_actions)) {
            $this->returnHandler(9, 'fail');
        }

        $this->_qtiType = $data['exam_type'];
        $this->_qtiId = $data['exam_id'];
        $this->_courseId = $data['course_id'];
        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);

        // jasmine 測試
        if (isset($data['validWebService']) && intval($data['validWebService']) === 1) {
            $this->_validWebService = true;
            // 在試卷標題留下標記，以供後面刪除使用
            $data['exam_info']['title'] .= "_[IRS)_{test)";
        }
        // 定義 lib 會使用到的常數
        define('API_QTI_which', $data['exam_type']);
        if (!defined('QTI_env')) {
            define('QTI_env', 'teach');
        }
    }
    /**
     * 主程式執行
     */
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;

        $itemIdAry = array();
        $itemScore = array();
        $now = date("Y-m-d H:i:s");
        $unlimitedTime = "9999-12-31 00:00:00";

        // 取得 post 資料
        $postData = file_get_contents('php://input');
        $inputData = JsonUtility::decode($postData);
//        $inputData['ticket'] = $_REQUEST['ticket'];

        // 處理傳進來的資料
        $this->dataHandler($inputData);

        // 確認傳進來的課程，使用者是否有新增權限
        $this->aclCheck($this->_mysqlUsername);

        // 引用 lib
        $itemMaintain = new itemMaintain();
        $examMaintain = new examMaintain();

        // 處理要加進測驗的題目
        if (isset($inputData['question_info'])) {
            foreach ($inputData['question_info'] as $item) {
                $curItemId = $item['item_id'];
                // 檢查需要新增的題目
                if ($curItemId === '') {
                    // 如果是 jasmine 測試，在題目留下標記
                    if ($this->_validWebService === true) {
                        $item['title'] .= "_[IRS)_{test)";
                    }
                    // 資料轉型成 lib 所需結構
                    $itemData = $this->itemDataTransform($item);
                    // 新增題目
                    $itemResult = $itemMaintain->saveItem($itemData);

                    // 紀錄 log
                    $logMsg = sprintf("%s IRS %s Item: [%s] %s",
                        ($itemMaintain->isModify) ? "Modify" : "Create",
                        API_QTI_which,
                        $itemMaintain->ident,
                        ($itemResult['ErrCode'] == 0) ? "success." : "fail: " . $itemResult['ErrMsg'] . "."
                    );

                    appSysLog($sysSession->cur_func, $itemMaintain->courseId, 0, $itemResult['ErrCode'], 'auto', $_SERVER['PHP_SELF'], $logMsg);

                    if ($itemResult['ErrCode'] == 0) {
                        $curItemId = $itemMaintain->ident;
                    } else {
                        // 有一題新增失敗即回傳錯誤訊息
                        $this->returnHandler(7, 'fail', array('errMsg' => implode(' : ', $itemResult)));
                    }
                }
                // 測驗才需要分數
                if ($this->_qtiType === "exam") {
                    $itemScore[ $curItemId] = $item['score'];
                }
                // 紀錄試卷裡的題目 item_id
                $itemIdAry[] = $curItemId;
            }
        }

        // 舊版 republish 流程會設定 action，為相容問題新增 copy 來處理新版流程
        if ($this->_qtiId !== 0 && in_array($inputData['action'], array('republish', 'copy'))) {
            // 複製後的試卷設定
            $otherSettings = array(
                'begin_time' => $now,
                'close_time' => $unlimitedTime,
                'publish' => ($inputData['action'] === 'republish') ? 'action' : 'prepare',
                'modifiable' => "Y"
            );

            // 設定試卷名稱
            $title = $inputData['exam_info']['title'];
            if ($title && is_array($title)) {
                $otherSettings['title'] = $this->captionArray2Serialize($title);
            }

            // 複製試卷
            $newQtiId = $this->copyExam($this->_qtiId, $otherSettings);

            // 取得原測驗是否支援行動測驗，有支援就將新試卷設定為可支援
            if ($this->_qtiType === 'exam' && sysEnableAppCourseExam == true) {
                $qtiSupportAPP = "Y";
                list($appSupportCount) = dbGetStSr('APP_qti_support_app', 'count(*)', "exam_id = {$this->_qtiId} AND type='{$this->_qtiType}' AND course_id={$this->_courseId}", ADODB_FETCH_NUM);
                if (intval($appSupportCount) !== 0) {
                    dbNew('APP_qti_support_app', "exam_id, type, course_id, support", "{$newQtiId}, '{$this->_qtiType}', {$this->_courseId},'{$qtiSupportAPP}'");
                }
            }

            $this->_qtiId = $newQtiId;
        } else {
            // TODO: type 為 exam 時，instance 為0 ，新增試卷失敗

            // 轉換試卷資料格式
            $testData = $this->testDataTransform($inputData);

            // 由 exam_id 是否有設定來判斷是不是修改試卷
            if ($this->_qtiId !== 0) {
                // 修改試卷
                $examMaintain->isModify = true;

                // 取得目前試卷的設定，saveExam 的 type 是看 ex_type 再取得原試卷時作個轉換
                $examData = dbGetRow(
                    sprintf("`WM_qti_%s_test`", $this->_qtiType),
                    "*, `type` as `ex_type`",
                    sprintf("exam_id = %d", $this->_qtiId)
                );
                if (!$examData) {
                    $this->returnHandler(8, "fail", array('errMsg' => "No test!"));
                }

                // 將已刪除的 item 從 content 移除
                $examData['content'] = $this->removeUnuseItem($examData['content'], $this->_qtiType);
                $examData['threshold_score'] = preg_match('/\bthreshold_score="([0-9]*)"/', $examData['content'], $regs) ? $regs[1] : '';

                // 將修改部分加入試卷設定
                $testData = $this->modifyData($examData, $testData);

                // 如果是 jasmine 測試，將前面測試資料都刪除
                if (isset($validWebService) && $validWebService === true) {
                    // 刪除測試資料
                    $this->deleteTestDatas($this->_qtiType);
                }
            } else {
                // 新增試卷
                $examMaintain->isModify = false;

                // 建立試卷內容
                $createContentXml = $examMaintain->createContentXml($itemIdAry, $itemScore);
                $testData['content'] = ($createContentXml['code'] === 0) ? $createContentXml['data'] : '';
            }

            // 如果此試卷為發佈將其餘進行中的愛上互動關閉
            if ($inputData['action'] === 'action') {
                $this->closeProgressingTests($this->_courseId);
            }

            $examResult = $examMaintain->saveExam($testData);
            $this->_qtiId = intval($examResult['data']['qti_id']);
            appSysLog(999999017, $sysSession->school_id, 0, 1, 'other', $_SERVER['PHP_SELF'], (($examMaintain->isModify) ? "Modify" : "Add") . ' IRS test:' . $examResult['message'] . "<" . $examResult['data']['errMsg'] . ">", $this->_mysqlUsername);
        }
        $data = array(
            "exam_id" =>  $this->_qtiId,
            "exam_type" => API_QTI_which,
            "action" => $inputData['action']
        );

        $this->returnHandler(0, 'success', $data);
    }
}