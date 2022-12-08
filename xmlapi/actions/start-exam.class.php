<?php
define('API_QTI_which', 'exam');
if (!defined('QTI_env')) {
    define('QTI_env', 'learn');
}

include_once(dirname(__FILE__) . '/action.class.php');
require_once(PATH_LIB . 'qti.php');

/**
 * 開始與取得試卷
 */
class StartExamAction extends baseAction
{
    var $_QTI_WHICH = '';

    function setEncoding($xml, $encoding = 'UTF-8')
    {
        $tmp = preg_replace(
            array('!\s*xmlns:wm="http://www.sun.net.tw/WisdomMaster"!', '!<questestinterop\b!'),
            array('', '<questestinterop xmlns:wm="http://www.sun.net.tw/WisdomMaster"'),
            $xml
        );

        $regs = array();
        if (preg_match('/<\?xml\b[^>]*\?>/isU', $tmp, $regs)) {
            if (preg_match('/\bencoding\s*=\s*/isU', $regs[0])) {
                return $tmp;
            }
            return preg_replace('/\?>/', ' encoding="' . $encoding . '"?>', $tmp, 1);
        }
        return '<?xml version="1.0" encoding="UTF-8"?>' . $tmp;
    }

    /**
     * 模擬 XMLDOM 之 swapNode METHOD 交換兩個子節點
     */
    function swapNode(&$node1, &$node2)
    {
        $newNode1 = $node1->clone_node(true);
        $newNode2 = $node2->clone_node(true);
        $node2->replace_node($newNode1);
        $node1->replace_node($newNode2);
    }

    /**
     * 將 node 節點下的 <tag> 子節點順序弄亂
     */
    function blockScramble($node, $tag){
        if ($node->has_child_nodes()) {
            $nodes = $node->child_nodes();
            $len = count($nodes);
            $tags = array();
            $otag = array();
            for ($i = 0; $i < $len; $i++) {
                if ($nodes[$i]->node_type() == XML_ELEMENT_NODE) $this->blockScramble($nodes[$i], $tag);
                if (method_exists($nodes[$i], 'tagname') && $nodes[$i]->tagname() == $tag) {
                    $otag[] = $i;
                    $tags[] = $i;
                }
            }
            $len = count($tags);
            shuffle($tags);

            for ($i = 0; $i < $len; $i++) {
                if ($otag[$i] != $tags[$i]) {
                    $nodes = $node->child_nodes();
                    $this->swapNode($nodes[$otag[$i]], $nodes[$tags[$i]]);
                }
            }
        }
    }

    /**
     * 將試卷的 id 換成 ident
     */
    function id2ident(&$doc)
    {
        if (is_null($doc)) return;
        $secTags = array('section', 'assessment', 'objbank');
        foreach ($secTags as $tag) {
            $nodes = $doc->get_elements_by_tagname($tag);
            if (is_array($nodes)) foreach ($nodes as $node) {
                $nn = $node->get_attribute('id');
                $node->set_attribute('ident', $nn);
                $node->remove_attribute('id');
            }
        }
    }

    /**
     * 將試卷中的 <selection_ordering> 取代掉
     **/
    function replaceSectionOrder(&$dom, &$root, &$ctx) {
        $sos = $dom->get_elements_by_tagname('selection_ordering');
        foreach ($sos as $node) {
            $pnode = $node->parent_node();
            $result = $ctx->xpath_eval('./selection/selection_number[1]/text()', $node);
            $cnt = (count($result->nodeset) > 0) ? $result->nodeset[0]->node_value() : 0;
            if ($cnt > 0) {
                $result = $ctx->xpath_eval('./order/@order_type', $node);
                $order = (count($result->nodeset) > 0) ? $result->nodeset[0]->node_value() : 'Sequential';
                $nodes = $pnode->get_elements_by_tagname('item');
                $cnt = min($cnt, count($nodes));
                if ($order == 'Random') {
                    // 亂數取題
                    shuffle($nodes);
                    for ($i = 0; $i < $cnt; $i++) {
                        // 將要顯示的試題先忽略
                        array_shift($nodes);
                    }
                    foreach ($nodes as $n) {
                        // 移除不要顯示的試題
                        $pnode->remove_child($n);
                    }
                } else if ($order == 'Sequential') {
                    // 依序取題
                    for ($i = count($nodes) - 1; $i >= $cnt; $i--) {
                        // 移除不要顯示的試題
                        $pnode->remove_child($nodes[$i]);
                    }
                }
            }
            // 移除 <selection_ordering> 區塊
            $pnode->remove_child($node);
        }
    }

    /**
     * 將試卷中的 <item> 轉換為真實題目
     */
    function replaceItemToComplete(&$dom, &$root, &$ctx, $apiQtiWhich)
    {
        global $sysConn;

        $ids = array();
        $nodes = $dom->get_elements_by_tagname('item');
        foreach ($nodes as $item) {
            $ids[] = $item->get_attribute('id');
            $item->set_attribute('score', $item->get_attribute('score')); // 強制設定一個 score 屬性
        }

        if ($ids) {
            $idents = 'ident in ("' . implode('","', $ids) . '")';

            $real_items = $sysConn->GetAssoc('select ident,content from WM_qti_' . $apiQtiWhich . '_item where ' . $idents);
            $ids = array_flip($ids);

            $result = $this->setEncoding($dom->dump_mem());
            $not_exist_idx = array();
            $regs = array();
            if (preg_match_all('!<item\b[^>]*\bid="([^"]+)"[^>]*(\bscore="([^"]+)"([^>]*)?)?(/>|>[^<]*</item>)!isU', $result, $regs)) {
                // 把配分收集起來
                $scores = array();
                foreach ($regs[1] as $k => $v) {
                    $scores[$v] = $regs[3][$k];
                }

                // 將分數填進真實 item xml 中
                foreach ($real_items as $k => $v) {
                    $real_items[$k] = preg_replace(
                        array('#<decvar[^>]*/>#isU', '/<%ITEM_ID%>/'),
                        array(sprintf('<decvar vartype="Integer" defaultval="%.2f" />', $scores[$k]), $k),
                        $v
                    );
                }

                // 用代換的方式，把試卷中的 item 代換為真實 xml
                $replaces = array();
                foreach ($regs[1] as $k) {
                    if (!$real_items[$k]) $not_exist_idx[] = $k;
                    $replaces[] = $real_items[$k];
                }

                $result = str_replace($regs[0], $replaces, $result);
            }
            $dom = domxml_open_mem($result);
            $root = $dom->document_element();
            $ctx = xpath_new_context($dom);

            if (is_array($not_exist_idx)) {
                $not_exist_idx = array_flip($not_exist_idx);
                foreach ($not_exist_idx as $id => $foo) {
                    $node = getElementById($root, $id);
                    if (!is_null($node)) {
                        $parent = $node->parent_node();
                        $parent->remove_child($node);
                    }
                }
            }
            $this->id2ident($dom);
        }
    }

    function main()
    {
        $ForGuestQuest  = '';
        if (isset($_GET['ForGuestQuest']) && $_GET['ForGuestQuest']==1) {
            $ForGuestQuest = 1;
        }

        // 驗證 Ticket
        if ($ForGuestQuest!=1) parent::checkTicket();

        global $sysSession, $sysConn, $sysRoles, $qtiId, $time_id, $examinee;

        $type = (isset($_REQUEST['type']))? trim($_REQUEST['type']) : 'exam';
        $qtiId = abs(intval($_REQUEST['eid']));

        $statusCode = '200';
        $responseObject = array(
            'code' => 0,
            'message' => 'success',
            'data' => array()
        );

        $qtiType = array('exam', 'questionnaire', 'homework');

        // 檢查測驗狀態 - 測驗編號不正確 或是 非三合一型態
        if ($qtiId < 100000001 || !in_array($type, $qtiType)) {
            $responseObject['code'] = 2;
            $responseObject['message'] = 'fail';
            $statusCode = '404';

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            return;
        }

        $this->_QTI_WHICH = $type;

        // 檢查測驗狀態 - 測驗不存在
        $exam = dbGetStSr(
            'WM_qti_' . $this->_QTI_WHICH . '_test',
            '*',
            "`exam_id`={$qtiId}",
            ADODB_FETCH_ASSOC
        );

        if (count($exam) <= 0) {
            $responseObject['code'] = 3;
            $responseObject['message'] = 'fail';
            $statusCode = '404';

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            return;
        }

        // 判斷此份不是愛上互動的試卷是否支援APP
        if (intval($exam['type']) !== 5 && $this->_QTI_WHICH === 'exam' && !checkSupportAPP($qtiId, $exam['course_id'], $this->_QTI_WHICH)) {
            $responseObject['code'] = 8;
            $responseObject['message'] = 'fail';
            $statusCode = '404';

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            return;
        }

        // 檢查測驗所屬的課程是否可用
        $unitId = abs(intval($exam['course_id']));

        // 驗證課程編號格式
        if (strlen(strval($unitId)) === 5) {
            // 5碼 <- 學校
            $unitId = checkSchoolID($unitId);
            $unitFlag = 'school';
        } else {
            // 8碼 <- 課程
            $unitId = checkCourseID($unitId);
            $unitFlag = 'course';
        }

        if ($unitId === false) {
            $responseObject['code'] = '!01';
            $responseObject['message'] = 'fail';
            $statusCode = '404';

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            return;
        }

        // 驗證課程存不存在，是否可以上課
        if ($unitFlag === 'course') {
            $rs = dbGetStSr(
                'WM_term_course',
                '`caption`,`st_begin`,`st_end`, `status`', "`course_id`={$unitId} AND `kind`='course'",
                ADODB_FETCH_ASSOC
            );
            $status = intval($rs['status']);

            if (!$rs || ($status === 9)) {
                $responseObject['code'] = '!02';
                $responseObject['message'] = 'fail';
                $statusCode = '404';

                $this->header('application/json', $statusCode);
                echo JsonUtility::encode($responseObject);
                return;
            }

            // 檢查是否修課
            $level = dbGetOne('WM_term_major','role',"course_id=$unitId and username='{$sysSession->username}'");
            if (is_null($level)) {
                $responseObject['code'] = '!04'; // 未修課
                $responseObject['message'] = 'fail';
                $statusCode = '404';

                $this->header('application/json', $statusCode);
                echo JsonUtility::encode($responseObject);
                return;
            }

            $isTeacher = $level & $sysRoles['teacher'];
            $stLimitStatus = array(2, 4);
            if ($isTeacher) {
                // 如果是教師，則"準備中"的課程也可以進入
                $arrayCourseStatus = array(1, 2, 3, 4, 5);
            } else {
                // 否則只有在開課中的課程才可以進入
                $arrayCourseStatus = array(1, 2, 3, 4);
            }

            $now = strtotime(date('Y-m-d'));
            if (
                !in_array($status, $arrayCourseStatus) ||
                (
                    !$isTeacher &&
                    in_array($status, $stLimitStatus) &&
                    (
                        (is_string($rs['st_begin']) && strtotime($rs['st_begin']) > $now) ||
                        (is_string($rs['st_end']) && strtotime($rs['st_end']) < $now)
                    )
                )
            ) {
                $responseObject['code'] = '!03';
                $responseObject['message'] = 'fail';
                $statusCode = '404';

                $this->header('application/json', $statusCode);
                echo JsonUtility::encode($responseObject);
                return;
            }

            // 將 course_id 設到 session 中
            $sysSession->course_id = $unitId;
            $sysSession->restore();
        }

        // 檢查測驗的 ACL
        $functionId = array('exam' => 1600400200, 'homework' => 1700400200, 'questionnaire' => 1800300200);

        $aclVerified = verifyQTIPermission($this->_QTI_WHICH, $unitId, $qtiId, $sysSession->username);

        // 愛上互動不作 QTI 允許身分判斷
        if (intval($exam['type']) !== 5 && !$aclVerified) {
            $responseObject['code'] = '!05'; // 沒有權限
            $responseObject['message'] = 'fail';
            $statusCode = '404';

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            return;
        }

        // 檢查測驗狀態 - 測驗未開放
        if ($exam['publish'] === 'prepare') {
            $responseObject['code'] = '4';
            $responseObject['message'] = 'fail';
            $statusCode = '404';

            APPLog::addLog($sysSession->username, $unitId, $qtiId, $this->_QTI_WHICH, date('Y-m-d H:i:s'), 'QTI is prepared.');

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            return;
        }

        // 檢查測驗狀態 - 測驗尚未開始 / 測驗已經結束
        $exceptTimes = array('0000-00-00 00:00:00', '1970-01-01 08:00:00', '9999-12-31 00:00:00');
        $now = time();
        $beginTime = strtotime($exam['begin_time']);
        $closeTime = strtotime($exam['close_time']);
        $re = '!^\d{4}([-/]\d{1,2}){2} \d{2}(:\d{2}){2}$!';

        // 檢查測驗狀態 - 測驗尚未開始
        if (
            preg_match($re, $exam['begin_time']) &&
            !in_array($exam['begin_time'], $exceptTimes) &&
            $beginTime > $now
        ) {
            $responseObject['code'] = '5';
            $responseObject['message'] = 'fail';
            $statusCode = '404';
            APPLog::addLog($sysSession->username, $unitId, $qtiId, $this->_QTI_WHICH, date('Y-m-d H:i:s'), 'QTI is prepared.');
            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            return;
        }

        // 檢查測驗狀態 - 測驗已經結束
        if (
            preg_match($re, $exam['close_time']) &&
            !in_array($exam['close_time'], $exceptTimes) &&
            $closeTime <= $now
        ) {
            $responseObject['code'] = '6';
            $responseObject['message'] = 'fail';
            $statusCode = '404';

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            return;
        }

        // 檢查測驗狀態 - 測驗作答次數已達上限
        // 取已作答總次數 (有測驗但是不一定完成測驗)
        $times = dbGetOne(
            'WM_qti_' . $this->_QTI_WHICH . '_result',
            'count(*)',
            "exam_id={$qtiId} and examinee='{$sysSession->username}'"
        );
        $times = intval($times);

        if ($ForGuestQuest == 1) {
            $times = 0;
        }

        // 取得最後測驗的狀態
        list($timeId, $content, $lastStat) = dbGetStSr(
            'WM_qti_' . $this->_QTI_WHICH . '_result',
            '`time_id`, `content`, `status`',
            "exam_id={$qtiId} and examinee='{$sysSession->username}' order by `time_id` desc",
            ADODB_FETCH_NUM
        );

        // 測驗關閉的條件
        $isContinue = false;
        $extra = array(
            'username'  => $sysSession->username,
            'last_stat' => $lastStat
        );
            // 檢查是否 Timeout
        $isTimeout = checkAPPExamWhetherTimeout($exam, $now, $times, $isContinue, $extra, $this->_QTI_WHICH);
        if ($isTimeout) {
            $responseObject['code'] = '7';
            $responseObject['message'] = 'fail';
            $statusCode = '404';

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            return;
        }

        // 取得測驗題目
        dbDel(
            'WM_save_temporary',
            "locate({$functionId[$this->_QTI_WHICH]}, function_id) and username='{$sysSession->username}'"
        );
        $responseObject['data'] = array(
            'exam_id' => $qtiId,
            'items'   => array()
        );

        $timeId = is_null($timeId) ? 1 : ++$timeId;
        $examinee = $sysSession->username;
        $item_cramble = $exam['item_cramble'];
        $random_pick = $exam['random_pick'];
        if (empty($content) || $this->_QTI_WHICH === 'exam') {
            $content = $exam['content'];
        }

        $sqls = '';
        // 亂數選題(非問卷才有亂數)
        if (strpos($content, '<wm_immediate_random_generate_qti') !== FALSE  && $this->_QTI_WHICH !== 'questionnaire') {
            preg_match_all('!<(score|amount)\s+selected="true">([^<]*)</\1>!isU', $content, $ss);
            // $keep: amount 題數; score 總分
            $keep = array();
            foreach ($ss[1] as $k => $v) {
                $keep[$v] = $ss[2][$k];
            }
            // 及格分數
            $threshold_score = preg_match('/\bthreshold_score="([0-9]*)"/', $content, $thresholdRegs) ? $thresholdRegs[1] : '';

            if (substr_count($content, '<condition>') > 1) {
                preg_match_all('!<condition>(.+)</condition>!isU', $content, $re);
                $a = $re[1];
                unset($re);
            } else {
                $a = array($content);
            }

            $irgs = array();
            foreach ($a as $context) {
                $immediate_random_pick = true;
                $irgs = $keep;
                if (preg_match_all('!<([^>]+)\s+selected="(true|false)">(.*)</[^>]+>!sU', $context, $regs)) {
                    foreach ($regs[1] as $k => $v) {
                        if ($regs[2][$k] == 'true') $irgs[$v] = $regs[3][$k];
                    }
                }

                $sqls .= sprintf('(select ident from WM_qti_%s_item where course_id=%u ', $this->_QTI_WHICH, $unitId);
                foreach (array('type', 'version', 'volume', 'chapter', 'paragraph', 'section', 'level') as $item) {
                    if (isset($irgs[$item])) {
                        if (empty($irgs[$item])) continue;
                        $sqls .= sprintf('and %s in (%s) ', $item, $irgs[$item]);
                    }
                }

                if (isset($irgs['fulltext'])) {
                    $fts = explode("\t", $irgs['fulltext'], 2);
                    if (!empty($fts[0])) {
                        $sqls .= sprintf('and (content like ("%%%s%%") or content like ("%%%s%%")) ', escape_sql($fts[0]), escape_sql($fts[1]));
                    }
                }

                $sqls .= sprintf('order by rand() limit %d) union ', $irgs['amount']);
            }
            if (count($a) > 1) {
                $sqls = preg_replace('/ union $/', sprintf(' order by rand() limit %d', $irgs['amount']), $sqls);
            } else {
                $sqls = substr($sqls, 0, -7);
            }

            $sqls = str_replace(' or content like ("%%")', '', $sqls);
            $sqls = str_replace('content like ("%%") or ', '', $sqls);
            $sqls = str_replace(' and (content like ("%%"))', '', $sqls);
            $sqls = preg_replace('/ in \(([\d]+)\)/', ' = \1', $sqls);

            $qs = $sysConn->GetCol($sqls);
            $content = '<?xml version="1.0" encoding="UTF-8"?><questestinterop xmlns:wm="http://www.sun.net.tw/WisdomMaster" wm:threshold_score=""><item id="' .
                @implode('" score="" /><item id="', $qs) .
                    '" score="" /></questestinterop>';
            $irgs['amount'] = max(1, min(count($qs), abs(intval($irgs['amount']))));
            $sc = $irgs['amount'] ? (floor($irgs['score'] * 100 / $irgs['amount']) / 100) : 0;
            $ts = sprintf('wm:threshold_score="%f"', $threshold_score);
            $o = sprintf('score="%f"', $sc);

            // 替換掉空item、及格分數及各題分數
            $content = str_replace(
                array('<item id="" score="" />', 'wm:threshold_score=""', 'score=""'),
                array('', $ts, $o),
                $content
            );
            // 各題分數未達總分，最後一題分數補上差額
            if (($remnant = $irgs['score'] - ($sc * $irgs['amount'])) != 0) {
                $xx = explode($o, $content);
                $xxx = array_pop($xx);
                $xx[count($xx) - 1] .= sprintf('score="%.2f"', $sc + $remnant) . $xxx;
                $content = implode($o, $xx);
            }
        }

        $item_cramble = explode(',', $item_cramble);
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        if (!$dom = domxml_open_mem(str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $content))) {
            $responseObject['code'] = '!0';
            $responseObject['message'] = 'fail';
            $statusCode = '404';

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            return;
        }

        $root = $dom->document_element();
        $ctx = xpath_new_context($dom);
        $this->replaceSectionOrder($dom, $root, $ctx);
        $this->replaceItemToComplete($dom, $root, $ctx, $this->_QTI_WHICH);
        $dom = @domxml_open_mem(
            preg_replace(
                array(
                    '/<item\s+xmlns="http:\/\/www\.imsglobal\.org\/question\/qtiv1p2\/qtiasiitemncdtd\/ims_qtiasiv1p2\.dtd"\s+xmlns:wm="http:\/\/www\.sun\.net\.tw\/WisdomMaster"\s+/',
                    '/<item\s+xmlns:wm="http:\/\/www\.sun\.net\.tw\/WisdomMaster"\s+/',
                    '/<item\s+xmlns="http:\/\/www\.imsglobal\.org\/question\/qtiv1p2\/qtiasiitemncdtd\/ims_qtiasiv1p2\.dtd"\s+/'
                ),
                array('<item ',
                    '<item ',
                    '<item '
                ),
                $this->setEncoding($dom->dump_mem())
            )
        );
        $root = $dom->document_element();
        $ctx = xpath_new_context($dom);

        // 如果有設定亂數排列，則混亂題目(非問卷才有亂數)
        if (in_array('enable', $item_cramble) && $this->_QTI_WHICH !== 'questionnaire') {
            if (in_array('random_pick', $item_cramble) && intval($random_pick) > 0) {
                $nodes = $root->get_elements_by_tagname('item');
                $total_item = count($nodes);
                while ($total_item > $random_pick) {
                    $nodes[rand(0, $total_item - 1)]->unlink_node();
                    $nodes = $root->get_elements_by_tagname('item');
                    $total_item = count($nodes);
                }
            }
            if (in_array('section', $item_cramble)) $this->blockScramble($root, 'section'); // 大題隨機
            if (in_array('item', $item_cramble)) $this->blockScramble($root, 'item'); // 題目隨機
            // 因為附檔問題，所以選項隨機放在 QTI_transformer.php 做
        }

        // 取得及格分數
        if ($this->_QTI_WHICH !== 'questionnaire') {
            // TODO: 隨機出題的及格分數不知為何要使用 wm:threshold_score，無特殊意義的話可合併為 threshold_score
            if ($immediate_random_pick) {
                $responseObject['data']['threshold_score'] = intval($root->get_attribute('wm:threshold_score'));
            } else {
                $responseObject['data']['threshold_score'] = intval($root->get_attribute('threshold_score'));
            }

            // 將愛上互動測驗的及格成績預設為 60 避免觸發快問快答 FIXME: 需在將快問快答加入模組之類的判斷
            if (intval($exam['type']) === 5 && $this->_QTI_WHICH === 'exam') {
                $responseObject['data']['threshold_score'] = 60;
            }
        } else {
            // 問卷
            $responseObject['data']['threshold_score'] = null;
        }

        $scorePublishType = '';
        if (intval($exam['type']) == 1 && preg_match('/\bscore_publish_type="(\w*)"/', $exam['content'], $matches)) {
            // 選擇作答完公布(now)或是到了自訂時間或是到了關閉時間就要提供s、sa、sar
            if ($exam['announce_type'] === 'now' ||
                    ($exam['announce_type'] === 'user_define' && (time() > strtotime($exam['announce_time']))) ||
                    ($exam['announce_type'] === 'close_time' && (time() > strtotime($exam['close_time'])))) {
                if ($matches[1] == 'simple') {
                    $scorePublishType = 's';    // score
                } else if ($matches[1] == 'detailed') {
                    $scorePublishType = 'sa';   // score+answer
                } else if ($matches[1] == 'complete') {
                    $scorePublishType = 'sar';  // score+answer+reference
                }
            }
        }

        // 轉換成 JSON 格式
        $qti = new Qti();
        $responseObject['data']['items'] = $qti->transformer($dom, $ctx, $this->_QTI_WHICH);
        $responseObject['data']['time_id'] = $timeId;
        $responseObject['data']['score_publish_type'] = $scorePublishType;
        $responseObject['data']['examType'] = intval($exam['type']);

        // 存入資料庫 (必須在 $qti->transformer 之後，因為要等 transformer 後產生 item_result 的資料)
        if (($this->_QTI_WHICH === 'exam') || ($this->_QTI_WHICH === 'questionnaire' && ($timeId === 1 || $ForGuestQuest == 1))) {
            // 測驗：每次都是新的紀錄；問卷，只有第一次是新的紀錄
            if ($ForGuestQuest == 1) {
                $maxlimit = 100;
                $map='0123456789';
                $timeId = '';
                for($i=0;$i<5;$i++)
                {
                    $timeId .= substr($map,mt_rand(0,9),1);
                }
                do {

                    $timeId = intval($timeId)+1;
                    dbNew('WM_qti_' . $this->_QTI_WHICH . '_result', 'exam_id,examinee,time_id,status,begin_time,content',
                        "{$qtiId},'{$sysSession->username}',{$timeId},'break',now(),'" .
                        mysql_real_escape_string($this->setEncoding($dom->dump_mem())) . "'");

                } while ($sysConn->ErrorNo() == 1062 && --$maxlimit); // 如果跟別人重覆則最多重試一百次

                $responseObject['data']['time_id'] = $timeId;
            } else {
                dbNew('WM_qti_' . $this->_QTI_WHICH . '_result', 'exam_id,examinee,time_id,status,begin_time,content',
                    "{$qtiId},'{$sysSession->username}',{$timeId},'break',now(),'" .
                    mysql_real_escape_string($this->setEncoding($dom->dump_mem())) . "'");
            }
            appSysLog($functionId[$this->_QTI_WHICH], $unitId, $qtiId , 0, 'auto', $_SERVER['PHP_SELF'], $this->_QTI_WHICH . ' start! Num of times: ' . $timeId);
        } else {
            appSysLog($functionId[$this->_QTI_WHICH], $unitId, $qtiId , 0, 'auto', $_SERVER['PHP_SELF'], $this->_QTI_WHICH . ' start! Num of times: modified');
        }

        $this->header('application/json', $statusCode);
        echo JsonUtility::encode($responseObject);
   }
}
