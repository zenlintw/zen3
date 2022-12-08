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
class GetExamResultAction extends baseAction
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
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession, $sysConn, $sysRoles, $qtiId, $time_id;

        $type = (isset($_REQUEST['type']))? trim($_REQUEST['type']) : 'exam';
        $qtiId = abs(intval($_REQUEST['eid']));
        $time = (isset($_REQUEST['time'])) ? intval($_REQUEST['time']) : 1;

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
            exit();
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
            exit();
        }

        // 判斷此份不是愛上互動的試卷是否支援APP
        if (intval($exam['type']) !== 5 && $this->_QTI_WHICH === 'exam' && !checkSupportAPP($qtiId, $exam['course_id'], $this->_QTI_WHICH)) {
            $responseObject['code'] = 8;
            $responseObject['message'] = 'fail';
            $statusCode = '404';

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            exit();
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
            exit();
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
                exit();
            }

            // 檢查是否修課
            $level = dbGetOne('WM_term_major','role',"course_id=$unitId and username='{$sysSession->username}'");
            if (is_null($level)) {
                $responseObject['code'] = '!04'; // 未修課
                $responseObject['message'] = 'fail';
                $statusCode = '404';

                $this->header('application/json', $statusCode);
                echo JsonUtility::encode($responseObject);
                exit();
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
                exit();
            }

            // 將 course_id 設到 session 中
            $sysSession->course_id = $unitId;
            $sysSession->restore();
        }

        $responseObject['data'] = array(
            'exam_id' => $qtiId,
            'items'   => array()
        );

        if ($time === 0) {
            $orderBy = "ORDER BY `time_id` DESC LIMIT 1";
        } else {
            $limit = intval($time) - 1;
            $orderBy = "ORDER BY `time_id` ASC LIMIT {$limit}, 1";
        }

        list($content, $score) = dbGetStSr(
            '`WM_qti_' . $this->_QTI_WHICH . '_result`',
            'SQL_CALC_FOUND_ROWS `content`, `score`',
            "`exam_id` = {$qtiId} AND `examinee` = '{$sysSession->username}' " . $orderBy, ADODB_FETCH_NUM
        );

        $total = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));

        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        if (!$dom = domxml_open_mem(str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $content))) {
            $responseObject['code'] = '!0';
            $responseObject['message'] = 'fail';
            $statusCode = '404';

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            exit();
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
        $ctx = xpath_new_context($dom);

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
        $responseObject['data']['time_id'] = $time_id;
        $responseObject['data']['score_publish_type'] = $scorePublishType;
        $responseObject['data']['examType'] = intval($exam['type']);

        if ($this->_QTI_WHICH === 'questionnaire') {
            $responseObject['data']['threshold_score'] = null;
        } else {
            $responseObject['data']['totalRecords'] = intval($total);

            $matches = array();
            $thresholdScore = -1;
            if (preg_match('/\bthreshold_score="([^"]*)"/', $content, $matches)) {
                $thresholdScore = ($matches[1] == '') ? -1 : floatval($matches[1]);
            }
            $responseObject['data']['statistics'] = $qti->statistics($responseObject['data']['item'], $score, $thresholdScore);
        }

        $this->header('application/json', $statusCode);
        echo JsonUtility::encode($responseObject);
   }
}
