<?php
if (!defined('QTI_DISPLAY_RESPONSE')) {
    define('QTI_DISPLAY_RESPONSE', false);
}
if (!defined('QTI_DISPLAY_OUTCOME')) {
    define('QTI_DISPLAY_OUTCOME', false);
}
if (!defined('QTI_DISPLAY_ANSWER')) {
    define('QTI_DISPLAY_ANSWER', false);
}

require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/exam_lib.php');
include_once(sysDocumentRoot . '/lang/' . API_QTI_which . '_learn.php');
include_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
require_once(PATH_LIB . 'emoji.php');

class QtiDomXml
{
    /**
     * 設定 xml 的編碼
     * @param $xml
     * @param string $encoding
     * @return mixed|string
     */
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
     * 移除節點內所有子節點
     * @param mixed $node 節點
     */
    function removeAllChild(&$node)
    {
        while ($node->has_child_nodes()) {
            $node->remove_child($node->last_child());
        }
    }

    function removeAllNode(&$node)
    {
        if (is_array($node)) {
            for ($i = count($node) - 1; $i >= 0; $i--) {
                $pnode = $node[$i]->parent_node();
                $pnode->remove_child($node[$i]);
            }
        } else {
            $pnode = $node->parent_node();
            $pnode->remove_child($node);
        }
    }

    /**
     * 取代節點內的資料
     * @param DomDocument $dom  DomDocument
     * @param mixed  $node    節點
     * @param string $content 內容
     */
    function replaceContent(&$dom, &$node, $content = '')
    {
        $this->removeAllChild($node);
        $text = $dom->create_text_node($content);
        $node->append_child($text);
    }

    /**
     * 附加子節點
     * @param DomDocument $dom  DomDocument
     * @param DomNode     $node 節點
     * @param string      $tag  Tag
     * @param string      $val  內容
     */
    function appendChild(&$dom, &$node, $tag, $val = '')
    {
        $child = $dom->create_element($tag);
        $this->replaceContent($dom, $child, $val);
        $node->append_child($child);
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
}

class Qti
{
    /**
     * 檢查試卷狀態
     * @param int    $eid      試卷編號
     * @param string $idx      session id
     * @param string $username 帳號
     * @param mixed  $exam     試卷資料
     * @param string $qtiType  三合一型態
     * @param boolean $enforceSubmit 強制交卷(For IRS)
     * @return int 狀態編號
     *     0 : 無任何錯誤
     *     1 : 試卷編號錯誤
     *     2 : 試卷不存在
     *     3 : 課程編號錯誤
     *     4 : 課程不存在
     *     5 : 課程已刪除
     *     6 : 課程已關閉
     *     7 : 未選修本門課
     *     8 : 沒有進行測驗的權限
     *     9 : 測驗未開放
     *     10: 測驗尚未開始
     *     11: 測驗已經結束
     *     12: 超過測驗次數與其它測驗結束的狀態
     *     13: 測驗次數與狀態異常
     */
    function checkExamStat($eid, $idx, $username, &$exam, $qtiType, $enforceSubmit = false)
    {
        global $sysRoles, $sysSession, $sysConn;

        $qti = array('exam', 'questionnaire', 'homework');

        if ($username === '') {
            $username = $sysSession->username;
        }

        $eid = intval($eid);

        // 檢查測驗狀態 - 測驗編號不正確 或是 非三合一型態
        if ($eid < 100000001 || !in_array($qtiType, $qti)) {
            return 1;
        }

        // 檢查測驗狀態 - 測驗不存在
        $exam = dbGetStSr(
            'WM_qti_' . $qtiType . '_test',
            '*',
            "`exam_id`={$eid}",
            ADODB_FETCH_ASSOC
        );

        if (count($exam) <= 0) {
            return 2;
        }

        $unitId = intval($exam['course_id']);
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
            return 3;
        }
        $exam['course_id'] = $unitId;
        $mysqlUsername = mysql_real_escape_string($username);

        // 驗證課程存不存在，是否可以上課
        if ($unitFlag === 'course') {
            $rs = dbGetStSr(
                'WM_term_course',
                '`caption`,`st_begin`,`st_end`, `status`', "`course_id`={$unitId} AND `kind`='course'",
                ADODB_FETCH_ASSOC
            );
            $status = intval($rs['status']);

            if (!$rs) {
                return 4;
            }
            if ($status === 9) {
                return 5;
            }

            // 檢查是否修課
            list($level) = dbGetStsr('WM_term_major','role',"course_id=$unitId AND username='{$mysqlUsername}'", ADODB_FETCH_NUM);
            if (is_null($level)) {
                return 7;
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
                return 6;
            }

            // 將 course_id 設到 session 中
            dbSet('WM_session', sprintf('course_id=%d', $unitId), sprintf("idx='%s'", $idx));
            $sysSession->course_id = $unitId;
        }


        // 檢查測驗的 ACL
        $aclVerified = verifyQTIPermission($qtiType, $unitId, $eid, $sysSession->username);

        // 愛上互動不作 QTI 允許身分判斷
        if (intval($exam['type']) !== 5 && !$aclVerified) {
            return 8;
        }

        // 檢查測驗狀態 - 測驗未開放
        if ($exam['publish'] === 'prepare') {
            return 9;
        }

        // 檢查測驗狀態 - 測驗尚未開始 / 測驗已經結束
        $exceptTimes = array('0000-00-00 00:00:00', '1970-01-01 08:00:00', '9999-12-31 00:00:00');
        $now = strtotime(date('Y-m-d H:i:s'));
        $beginTime = strtotime($exam['begin_time']);
        $closeTime = strtotime($exam['close_time']);
        $re = '!^\d{4}([-/]\d{1,2}){2} \d{2}(:\d{2}){2}$!';

        // 檢查測驗狀態 - 測驗尚未開始
        if (
            preg_match($re, $exam['begin_time']) &&
            !in_array($exam['begin_time'], $exceptTimes) &&
            $beginTime > $now
        ) {
            return 10;
        }

        // 非IRS要檢查測驗狀態 - 測驗已經結束
        if (
            (intval($exam['type']) !== 5 || !$enforceSubmit) &&
            preg_match($re, $exam['close_time']) &&
            !in_array($exam['close_time'], $exceptTimes) &&
            $closeTime <= $now
        ) {
            return 11;
        }

        // 檢查測驗狀態 - 測驗作答次數已達上限
        // 取已作答總次數 (有測驗但是不一定完成測驗)
        list($times) = dbGetStSr(
            'WM_qti_' . $qtiType . '_result',
            'count(*)',
            "exam_id={$eid} AND examinee='{$mysqlUsername}'",
            ADODB_FETCH_NUM
        );
        $times = intval($times);
        // 取得最後測驗的狀態
        list($timeId, $lastStat, $resContent) = dbGetStSr(
            'WM_qti_' . $qtiType . '_result',
            '`time_id`, `status`, `content`',
            "exam_id={$eid} AND examinee='{$mysqlUsername}' ORDER BY `time_id` DESC",
            ADODB_FETCH_NUM
        );
        $exam['time_id'] = intval($timeId);
        $exam['last_stat'] = $lastStat;
        $exam['result_content'] = $resContent;

        // 檢查測驗次數與狀態是否異常
        if ($qtiType === 'exam') {
            if ($exam['time_id'] <= 0 || $exam['last_stat'] !== 'break') {
                return 13;
            }
        }

        // 測驗關閉的條件
        $isContinue = false;
        $extra = array(
            'username'  => $username,
            'last_stat' => $lastStat
        );
        // 非IRS檢查是否 Timeout
        if (intval($exam['type']) !== 5 || !$enforceSubmit) {
            $isTimeout = checkAPPExamWhetherTimeout($exam, $now, $times - 1, $isContinue, $extra, $qtiType);
            if ($isTimeout) {
                return 12;
            }
        }

        // 無任何錯誤
        return 0;
    }

    /**
     * 取得 ISO8601 格式之時間日期
     */
    function getISO8601_datetime($now = null)
    {
        return (is_null($now)) ? date('Y-m-d\TH:i:s') : date('Y-m-d\TH:i:s', $now);
    }

    /**
     * 在 $brother 這個 <item> 節點後增加一個 <item_result> 節點
     */
    function appendItemResult(&$curItem)
    {
        global $dom, $ctx;

        $next = $curItem->next_sibling();

        if ($curItem->tagname() != 'item' || (!is_null($next) && $next->tagname() == 'item_result')) return;
        $item_id = $curItem->get_attribute('ident');
        $now = $this->getISO8601_datetime();

        $item_result = <<< EOB
<?xml version="1.0" encoding="UTF-8"?>
<item_result ident_ref="$item_id">
	<date>
		<type_label>Item Creation Time</type_label>
		<datetime>$now</datetime>
	</date>
	<duration />
EOB;

        // 把 <item_result> 要參考 <item> 的資訊備妥
        $ret = $ctx->xpath_eval("//item[@ident='$item_id']//*[starts-with(name(),'response_')]");
        for ($i = 0; $i < count($ret->nodeset); $i++) {
            if ($ret->nodeset[$i]->tagname() == 'response_label') continue;
            $v1 = $ret->nodeset[$i]->get_attribute('ident');
            $v2 = (($tmp = $ret->nodeset[$i]->get_attribute('rcardinality')) == '') ? '' : " cardinality=\"$tmp\"";
            $v3 = (($tmp = $ret->nodeset[$i]->get_attribute('rtiming')) == '') ? '' : " timing=\"$tmp\"";
            $response_type = substr($ret->nodeset[$i]->tagname(), 9);

            // 取得題目類型
            $ret2 = $ctx->xpath_eval("//item[@ident='$item_id']//*[starts-with(name(),'response_')]/*[starts-with(name(),'render_')]");
            if (is_null($ret2)) die('it must have a &lt;render_???&gt; in &lt;response_???&gt;.');
            $v4 = substr($ret2->nodeset[0]->tagname(), 7);

            // 取得標準答案  ($everyAns 表示一題中，可能有多個答案 (複選、填空) )
            $ret3 = $ctx->xpath_eval("//item[@ident='$item_id']/resprocessing/respcondition[1]/varsubset");
            if (empty($ret3->nodeset))
                $ret3 = $ctx->xpath_eval("//item[@ident='$item_id']/resprocessing/respcondition[1]/conditionvar/varequal");

            if (is_array($ret3->nodeset))
                foreach ($ret3->nodeset as $everyAns) {
                    $ans[$everyAns->get_attribute('respident')][] = $everyAns->get_content();
                }
            else
                $ans[$v1][$ret2->nodeset[0]->get_attribute('ident')] = '';

            if (is_array($ans))
                foreach ($ans as $respident => $realAns) {
                    if ($respident != $v1) continue;
                    $item_result .= <<< EOB
	<response ident_ref="$respident">
		<response_form{$v2} render_type="$v4"{$v3} response_type="{$response_type}">
EOB;
                    foreach ($realAns as $piece) {
                        $item_result .= '			<correct_response>' . htmlspecialchars($piece) . '</correct_response>';
                    }
                    $item_result .= <<< EOB
		</response_form>
		<num_attempts>1</num_attempts>
		<response_value />
	</response>
EOB;
                }
            unset($ans);
        }
        $item_result .= <<< EOB
	<outcomes>
		<score varname="SCORE" vartype="Integer">
			<score_value />
			<score_min />
			<score_max />
			<score_cut />
		</score>
	</outcomes>
</item_result>
EOB;
        $newNode = domxml_open_mem(preg_replace('/>\s+</', '><', $item_result));
        $item_result_root = $newNode->document_element();
        $parent = $curItem->parent_node();

        // 先附加一個空節點
        $x = $dom->create_element('item_result');
        if (is_null($next))
            $nn = $parent->append_child($x);
        else
            $nn = $parent->insert_before($x, $next);
        if (is_null($nn)) die('cannot append item_result.');
        // 再用完整的 <item_result> 取代
        $nn->replace_node($item_result_root);
    }

    /**
     * 將不是本頁要秀的題目(item)、區塊(section)、評量(assessment) 加上 visable='invisible' 的屬性
     * 在 QTI_parser 中不會處裡這些區塊
     */
    function hidUnnecessary(&$node, $start, $end, $cur_page = 1, $qtiType)
    {
        global $dom, $ctx;
        //將start到end之間的節點加上item_result
        $ident = '';
        for ($i = $start; $i <= $end; $i++) {
            $ident .= '"' . $node[$i]->get_attribute('ident') . '",';
            $this->appendItemResult($node[$i], $dom, $ctx);
        }
        // 處理題目附檔
        if ($ident != '') {
            $ident = 'ident in (' . preg_replace('/,$/', '', $ident) . ')';
            $rs = dbGetStMr('WM_qti_' . $qtiType . '_item', 'ident, attach, type', $ident, ADODB_FETCH_ASSOC);
            if ($rs)
                while ($row = $rs->FetchRow()) {
                    if (preg_match('/^a:[0-9]+:{/', $row['attach']))
                        $GLOBALS['attachments'][$row['ident']] = unserialize($row['attach']);
                    $GLOBALS['item_types'][$row['ident']] = $row['type'];
                }
        }

        // 將所有start以前及end以後的節點的節點設定隱藏
        $xpath = '//item[@ident="' .
            $node[$start]->get_attribute('ident') .
            '"]/preceding::*[name()="item" or name()="section" or name()="assessment"] | //item[@ident="' .
            $node[$end]->get_attribute('ident') .
            '"]/following::*[name()="item" or name()="section" or name()="assessment"]';
        $ret = $ctx->xpath_eval($xpath);
        if (is_array($ret->nodeset))
            foreach ($ret->nodeset as $item)
                $item->set_attribute('visable', 'invisible');

        // 當在第一頁時，而且第一個大題內沒有任何題目時，取消隱藏 (Begin)
        if ($cur_page == 1) {
            $xpath = '//questestinterop/section[1]//item';
            $ret = $ctx->xpath_eval($xpath);
            if (is_null($ret->nodeset) || (count($ret->nodeset) <= 0)) {
                $xpath = '//questestinterop/section[1]';
                $ret = $ctx->xpath_eval($xpath);
                if (is_array($ret->nodeset)) {
                    foreach ($ret->nodeset as $item) {
                        $item->set_attribute('visable', '');
                    }
                }
            }
        }
        // 當在第一頁時，而且第一個大題內沒有任何題目時，取消隱藏 (End)
    }

    function genLink($id, $fname, $qtiType)
    {
        global $sysSession;
        $save_uri = sprintf('/base/%05d/course/%08d/%s/Q/',
                    $sysSession->school_id,
                    $sysSession->course_id,
                    $qtiType);

        return sprintf('%s%s%s/%s', WM_SERVER_HOST, $save_uri, $id, $fname);
    }

    /**
     * 選擇題 (是非、單選、複選)
     * @param string $itemId
     * @param string $node
     * @param integer $type 題目類型
     * @param string $qtiType 三合一類型
     *
     * @return array 題目結果
     */
    function generateCHOICE($itemId, $node, $type, $qtiType)
    {
        global $ctx, $attachments;

        $ret = '';
        $text = '';
        $attaches = array();
        $answerAttaches = array();
        $opts = array();
        $quizAnswer = array();
        $userAnswer = array();
        $referenceURLs = array();

        foreach ($node->child_nodes() as $subnode) {
            switch ($subnode->tagname()) {
                case 'flow':
                    $text .= $this->generateFILL($itemId, $subnode, $type, $qtiType);
                    break;
                case 'material':
                    $text .= travelMaterial($subnode);
                    break;
                case 'material_ref':
                    $ref = $ctx->xpath_eval('//material[@label="' . $subnode->get_attribute('linkrefid') . '"]');
                    if (count($ref->nodeset)) {
                        $text .= travelMaterial($ref->nodeset[0]);
                    }
                    break;
                case 'response_lid':
                    // 產生題目夾檔
                    if (is_array($attachments[$itemId]['topic_files'])) {
                        foreach($attachments[$itemId]['topic_files'] as $key => $value) {
                            $attaches[] = array(
                                'filename' => $key,
                                'href' => $this->genLink($itemId, $value, $qtiType)
                            );
                        }
                    }
                    // 產生詳解夾檔
                    if (is_array($attachments[$itemId]['ans_files'])) {
                        foreach($attachments[$itemId]['ans_files'] as $key => $value) {
                            $answerAttaches[] = array(
                                'filename' => $key,
                                'href' => $this->genLink($itemId, $value, $qtiType)
                            );
                        }
                    }

                    foreach ($subnode->child_nodes() as $subnode2) {
                        switch ($subnode2->tagname()) {
                            case 'material':
                                $ret .= travelMaterial($subnode2);
                                break;
                            case 'material_ref':
                                $ref = $ctx->xpath_eval('//material[@label="' . $subnode2->get_attribute('linkrefid') . '"]');
                                if (count($ref->nodeset)) {
                                    $ret .= travelMaterial($ref->nodeset[0]);
                                }
                                break;
                            case 'render_choice':
                                // 產生選項
                                $keys = @array_keys($attachments[$itemId]['render_choice_files']);
                                $values = @array_values($attachments[$itemId]['render_choice_files']);
                                foreach ($subnode2->child_nodes() as $subnode3) {
                                    if ($subnode3->tagname() == 'flow_label') $subnode3 = $subnode3->first_child();
                                    switch ($subnode3->tagname()) {
                                        case 'material':
                                            $ret .= travelMaterial($subnode3);
                                            break;
                                        case 'material_ref':
                                            $ref = $ctx->xpath_eval('//material[@label="' . $subnode3->get_attribute('linkrefid') . '"]');
                                            if (count($ref->nodeset)) {
                                                $ret .= travelMaterial($ref->nodeset[0]);
                                            }
                                            break;
                                        case 'response_label':
                                            $subnode3Ident = $subnode3->get_attribute('ident');
                                            $att = array();
                                            if (!is_null($keys)) {
                                                $k = @array_shift($keys);
                                                $v = @array_shift($values);
                                                if ($k !== '' && $v !=='' && !is_null($v)) {
                                                    $att = array(
                                                        'filename' => $k,
                                                        'href' => $this->genLink($itemId, $v, $qtiType)
                                                    );
                                                }
                                            }
                                            if (count($att) > 0) {
                                                $opts[] = array(
                                                    'text' => travelResponselabel($subnode3, $subnode3Ident),
                                                    'attaches' => array($att)
                                                );
                                            } else {
                                                $opts[] = array(
                                                    'text' => travelResponselabel($subnode3, $subnode3Ident)
                                                );
                                            }
                                            break;
                                    }
                                }
                                break;
                        }
                    }
                    break;
            }
        }

        $result = $ctx->xpath_eval('./*[substring-before(name(), "_") = "response"]', $node);
        $response_id = $result->nodeset[0]->get_attribute('ident');

        // 正確答案
        $co = $va = $ctx->xpath_eval("//item[@ident='$itemId']/resprocessing//varsubset");
        if (empty($va->nodeset)) {
            $co = $ctx->xpath_eval("//item[@ident='$itemId']/resprocessing//varequal");
        }
        if (count($co->nodeset)) {
            foreach($co->nodeset as $piece) {
                if ($piece->get_content() === 'T') {
                    $quizAnswer[] = 'O';
                } else if ($piece->get_content() === 'F') {
                    $quizAnswer[] = 'X';
                } else {
                    $quizAnswer[] = intval($piece->get_content());
                }
            }
        }

        // 學員答案
        $re = $ctx->xpath_eval("//item_result[@ident_ref='$itemId']/response[@ident_ref='$response_id']//response_value");
        if (count($re->nodeset)) {
            foreach($re->nodeset as $piece) {
                $nd = $ctx->xpath_eval('./num_attempts/text()', $piece->parent_node());
                if (intval($nd->nodeset[0]->node_value()) <= 1 && $piece->get_content()=='') {
                    continue;
                }
                if ($piece->get_content() === 'T') {
                    $userAnswer[] = 'O';
                } else if ($piece->get_content() === 'F') {
                    $userAnswer[] = 'X';
                } else {
                    $userAnswer[] = intval($piece->get_content());
                }
            }
        }

        $ref = $ctx->xpath_eval("//item[@ident='$itemId']/itemfeedback/solution/wm:refurl");
        if (count($ref->nodeset))  {
            foreach($ref->nodeset as $piece) {
                if ($piece->get_content() !== 'http://' && $piece->get_content() !== '') {
                    // 如果不只有http:// 就是有設定值
                    $referenceURLs = explode(' ', $piece->get_content());
                }
            }
        }

        $detailAnswer = dbGetOne('WM_qti_' . $qtiType . '_item', '`answer`', "`ident` = '{$itemId}'");
        return array(
            'text' => $text,
            'attaches' => $attaches,
            'answerAttaches' => $answerAttaches,
            'optionals' => $opts,
            'detailAnswer' => html_entity_decode($detailAnswer),
            'quizAnswer' => $quizAnswer,
            'userAnswer' => $userAnswer,
            'reference' => $referenceURLs
        );
    }

    /**
     * 填充題、問答
     * @param string $itemId
     * @param string $node
     * @param integer $type 題目類型
     * @param string $qtiType 三合一類型
     *
     * @return array 題目結果
     */
    function generateFILL($itemId, $node, $type, $qtiType) {
        global $ctx, $attachments;

        $text = '';
        $attaches = array();
        $answerAttaches = array();
        $opts = array();

        $quizAnswer = array();
        $userAnswer = array();
        $referenceURLs = array();

        $emojiHandler = new EmojiHandler();

        // 詳解
        $detailAnswer = dbGetOne('WM_qti_' . $qtiType . '_item', '`answer`', "`ident` = '{$itemId}'");

        // 正確答案
        if ($type === 4) {
            // 填充
            $co = $va = $ctx->xpath_eval("//item[@ident='$itemId']/resprocessing//varsubset");
            if (empty($va->nodeset)) {
                $co = $ctx->xpath_eval("//item[@ident='$itemId']/resprocessing//varequal");
            }
            if (count($co->nodeset)) {
                foreach($co->nodeset as $piece) {
                    $quizAnswer[] = strip_tags(htmlspecialchars_decode(stripslashes($piece->get_content())));
                }
            }
        } else {
            // 簡答題
            $quizAnswer[] = html_entity_decode($detailAnswer);
        }

        // 學員答案
        $re = $ctx->xpath_eval("//item_result[@ident_ref='$itemId']/response/response_value");
        if (count($re->nodeset)) {
            foreach($re->nodeset as $piece) {
                // 原本是以下寫法，但填充題可能會出現沒有填入答案的部分，故不判斷是否為空
                $userAnswer[] = html_entity_decode($emojiHandler->unicodeToMb4($piece->get_content()));
//                if ($piece->get_content() !== '') {
//                    $userAnswer[] = html_entity_decode($emojiHandler->unicodeToMb4($piece->get_content()));
//                }
            }
        }

        $ref = $ctx->xpath_eval("//item[@ident='$itemId']/itemfeedback/solution/wm:refurl");
        if (count($ref->nodeset))  {
            foreach($ref->nodeset as $piece) {
                if ($piece->get_content() !== 'http://' && $piece->get_content() !== '') {
                    // 如果不只有http:// 就是有設定值
                    $referenceURLs = explode(' ', $piece->get_content());
                }
            }
        }

        // 產生題目夾檔
        if (is_array($attachments[$itemId]['topic_files'])) {
            foreach($attachments[$itemId]['topic_files'] as $key => $value) {
                $attaches[] = array(
                    'filename' => $key,
                    'href' => $this->genLink($itemId, $value, $qtiType)
                );
            }
        }
        // 產生詳解夾檔
        if (is_array($attachments[$itemId]['ans_files'])) {
            foreach($attachments[$itemId]['ans_files'] as $key => $value) {
                $answerAttaches[] = array(
                    'filename' => $key,
                    'href' => $this->genLink($itemId, $value, $qtiType)
                );
            }
        }

        foreach ($node->child_nodes() as $subnode) {
            switch ($subnode->tagname()) {
                case 'flow':
                    $text .= $this->generateFILL($itemId, $subnode, $type, $qtiType);
                    break;
                case 'material':
                    $text .= travelMaterial($subnode);
                    break;
                case 'material_ref':
                    $ref = $ctx->xpath_eval('//material[@label="' . $subnode->get_attribute('linkrefid') . '"]');
                    if (count($ref->nodeset)) {
                        $text .= travelMaterial($ref->nodeset[0]);
                    }
                    break;
                case 'response_str':
                case 'response_num':
                case 'response_extension':
                    foreach ($subnode->child_nodes() as $subnode2) {
                        switch ($subnode2->tagname()) {
                            case 'material':
                                $text .= travelMaterial($subnode2);
                                break;
                            case 'material_ref':
                                break;
                            case 'render_fib':
                                if ($type === 4) {
                                    $text .= '(())';
                                }
                                break;
                            case 'render_extension':
                                break;
                        } // switch
                    } //foreach2
                    break;
            } //switch
        }

        return array(
            'text' => $text,
            'attaches' => $attaches,
            'answerAttaches' => $answerAttaches,
            'optionals' => $opts,
            'detailAnswer' => html_entity_decode($detailAnswer),
            'quizAnswer' => $quizAnswer,
            'userAnswer' => $userAnswer,
            'reference' => $referenceURLs
        );
    }

    /**
     * 配合題
     * @param string $itemId
     * @param string $node
     * @param int $type 題目類型
     * @param string $qtiType 三合一類型
     *
     * @return array 題目結果
     */
    function generatePAIR($itemId, $node, $type, $qtiType)
    {
        global $ctx, $attachments;

        $ret = '';
        $text = '';
        $attaches = array();
        $answerAttaches = array();
        $opts = array();
        $prompt = array();

        $quizAnswer = array();
        $userAnswer = array();
        $referenceURLs = array();

        $result = $ctx->xpath_eval('./*[substring-before(name(), "_") = "response"]', $node);
        $response_id = $result->nodeset[0]->get_attribute('ident');
        // 正確答案
        $co = $va = $ctx->xpath_eval("//item[@ident='$itemId']/resprocessing//varsubset");
        if (empty($va->nodeset)) {
            $co = $ctx->xpath_eval("//item[@ident='$itemId']/resprocessing//varequal");
        }
        if (count($co->nodeset)) {
            foreach($co->nodeset as $piece) {
                $quizAnswer[] = intval($piece->get_content());
            }
        }

        // 學員答案
        $re = $ctx->xpath_eval("//item_result[@ident_ref='$itemId']/response[@ident_ref='$response_id']/response_value");
        if (count($re->nodeset))  {
            foreach($re->nodeset as $piece) {
                if (intval($piece->get_content()) > 0) {
                    $userAnswer[] = intval($piece->get_content());
                }
            }
        }

        $ref = $ctx->xpath_eval("//item[@ident='$itemId']/itemfeedback/solution/wm:refurl");
        if (count($ref->nodeset))  {
            foreach($ref->nodeset as $piece) {
                if ($piece->get_content() !== 'http://' && $piece->get_content() !== '') {
                    // 如果不只有http:// 就是有設定值
                    $referenceURLs = explode(' ', $piece->get_content());
                }
            }
        }

        foreach ($node->child_nodes() as $subnode) {
            switch ($subnode->tagname()) {
                case 'flow':
                    $text .= $this->generateFILL($itemId, $subnode, $type, $qtiType);
                    break;
                case 'material':
                    $text .= travelMaterial($subnode);
                    break;
                case 'material_ref':
                    $ref = $ctx->xpath_eval('//material[@label="' . $subnode->get_attribute('linkrefid') . '"]');
                    if (count($ref->nodeset)) {
                        $text .= travelMaterial($ref->nodeset[0]);
                    }
                    break;
                case 'response_grp':
                    // 產生題目夾檔
                    if (is_array($attachments[$itemId]['topic_files'])) {
                        foreach($attachments[$itemId]['topic_files'] as $key => $value) {
                            if ($key !== '' && $value !== '') {
                                $attaches[] = array(
                                    'filename' => $key,
                                    'href' => $this->genLink($itemId, $value, $qtiType)
                                );
                            }
                        }
                    }
                    // 產生詳解夾檔
                    if (is_array($attachments[$itemId]['ans_files'])) {
                        foreach($attachments[$itemId]['ans_files'] as $key => $value) {
                            $answerAttaches[] = array(
                                'filename' => $key,
                                'href' => $this->genLink($itemId, $value, $qtiType)
                            );
                        }
                    }

                    $res_id = $subnode->get_attribute('ident');
                    foreach ($subnode->child_nodes() as $subnode2) {
                        switch ($subnode2->tagname()) {
                            case 'material':
                                $ret .= travelMaterial($subnode2);
                                break;
                            case 'material_ref':
                                $ref = $ctx->xpath_eval('//material[@label="' . $subnode2->get_attribute('linkrefid') . '"]');
                                if (count($ref->nodeset)) {
                                    $ret .= travelMaterial($ref->nodeset[0]);
                                }
                                break;
                            case 'render_extension':
                                $x = $subnode2->first_child();
                                $response_label_count = 0;
                                if ($x->tagname() == 'ims_render_object') $subnode2 = $x;
                                $keys1 = @array_keys($attachments[$itemId]['render1_choice_files']);
                                $values1 = @array_values($attachments[$itemId]['render1_choice_files']);
                                $keys2 = @array_keys($attachments[$itemId]['render2_choice_files']);
                                $values2 = @array_values($attachments[$itemId]['render2_choice_files']);
                                foreach ($subnode2->child_nodes() as $subnode3) {
                                    if ($subnode3->tagname() == 'flow_label') $subnode3 = $subnode3->first_child();
                                    switch ($subnode3->tagname()) {
                                        case 'material':
                                            $ret .= travelMaterial($subnode3);
                                            break;
                                        case 'material_ref':
                                            $ref = $ctx->xpath_eval('//material[@label="' . $subnode3->get_attribute('linkrefid') . '"]');
                                            if (count($ref->nodeset)) $ret .= travelMaterial($ref->nodeset[0]);
                                            break;
                                        case 'response_label':
                                            if ($subnode3->get_attribute('match_max')) {
                                                $att = array();
                                                if (!is_null($keys1)) {
                                                    $k = @array_shift($keys1);
                                                    $v = @array_shift($values1);
                                                    if ($k !== '' && $v !== '' && !is_null($v)) {
                                                        $att = array(
                                                            'filename' => $k,
                                                            'href' => $this->genLink($itemId, $v, $qtiType)
                                                        );
                                                    }
                                                }
                                                if (count($att) > 0) {
                                                    $prompt[] = array(
                                                        'text' => travelResponselabel($subnode3),
                                                        'attaches' => array($att)
                                                    );
                                                } else {
                                                    $prompt[] = array(
                                                        'text' => travelResponselabel($subnode3)
                                                    );
                                                }
                                            } else {
                                                $att = array();
                                                if (!is_null($keys2)) {
                                                    $k = @array_shift($keys2);
                                                    $v = @array_shift($values2);
                                                    if ($v !== '' && !is_null($v)) {
                                                        $att = array(
                                                            'filename' => $k,
                                                            'href' => $this->genLink($itemId, $v, $qtiType)
                                                        );
                                                    }
                                                }
                                                if (count($att) > 0) {
                                                    $opts[] = array(
                                                        'text' => travelResponselabel($subnode3),
                                                        'attaches' => array($att)
                                                    );
                                                } else {
                                                    $opts[] = array(
                                                        'text' => travelResponselabel($subnode3)
                                                    );
                                                }
                                            }
                                            $response_label_count++;
                                            break;
                                    } // switch
                                } // foreach3
                                break;
                        } // switch
                    } //foreach2
                    break;
            } //switch
        } //foreach

        $detailAnswer = dbGetOne('WM_qti_' . $qtiType . '_item', '`answer`', "`ident` = '{$itemId}'");

        return array(
            'text' => $text,
            'attaches' => $attaches,
            'answerAttaches' => $answerAttaches,
            'optionals' => $opts,
            'prompt' => $prompt,
            'detailAnswer' => html_entity_decode($detailAnswer),
            'quizAnswer' => $quizAnswer,
            'userAnswer' => $userAnswer,
            'reference' => $referenceURLs
        );
    }

    /**
     * 轉換 QTI XML 的 Item 為 Array
     * @param $dom
     * @param $ctx
     * @param $qtiType
     * @return array
     */
    function transformer($dom, $ctx, $qtiType)
    {
        global $attachments;

        // 設定為全域變數，QTI_transformer.php 需要的資料
        $GLOBALS['ctx'] = $ctx;
        $GLOBALS['dom'] = $dom;

        $attachments = array();
        $nodes = array();

        $items = $dom->get_elements_by_tagname('item');
        $total_item = count($items);
        // 沒有測驗就回傳
        if ($total_item == 0) {
            return $nodes;
        }

        $this->hidUnnecessary($items, 0, $total_item - 1, 1, $qtiType);

        if (!is_array($items)) {
            return $nodes;
        }

        for ($i = 0; $i < $total_item; $i++) {
            $item = $items[$i];
            $node = array();

            // 題目編號
            $itemId = $item->get_attribute('ident');
            $node['item_id'] = $itemId;

            // 檢查題目
            $result = $ctx->xpath_eval('./presentation/flow', $item);
            if (count($result->nodeset) == 0) {
                continue;
            }

            // 取得題目資料
            list($type, $attach) = dbGetStSr(
                'WM_qti_' .$qtiType . '_item',
                '`type`, `attach`',
                'ident = "' . $itemId . '"',
                ADODB_FETCH_NUM
            );
            if (!isset($attachments[$itemId]) && ($attach != '')) {
                $attachments[$itemId] = unserialize($attach);
            }
            $type = intval($type);

            // 題目內容
            $kind = detect_item_type($item);
            if ($kind == 1) {
                // 選擇題 (是非、單選、複選)
                $res = $this->generateCHOICE($itemId, $result->nodeset[0], $type, $qtiType);
                $node['text'] = $res['text'];
                if (count($res['attaches']) > 0) {
                    $node['attaches'] = $res['attaches'];
                }
                if (count($res['answerAttaches']) > 0) {
                    $node['answerAttaches'] = $res['answerAttaches'];
                }
                $node['optionals'] = $res['optionals'];
            } else if (in_array($kind, array(2, 3, 9))) {
                // 填充題、問答 (字串、數值)
                $res = $this->generateFILL($itemId, $result->nodeset[0], $type, $qtiType);
                $node['text'] = $res['text'];
                if (count($res['attaches']) > 0) {
                    $node['attaches'] = $res['attaches'];
                }
                if (count($res['answerAttaches']) > 0) {
                    $node['answerAttaches'] = $res['answerAttaches'];
                }
                $node['optionals'] = $res['optionals'];
            } else if ($kind == 4) {
                // 配合題
                $res = $this->generatePAIR($itemId, $result->nodeset[0], $type, $qtiType);
                $node['text'] = $res['text'];
                if (count($res['attaches']) > 0) {
                    $node['attaches'] = $res['attaches'];
                }
                if (count($res['answerAttaches']) > 0) {
                    $node['answerAttaches'] = $res['answerAttaches'];
                }
                $node['optionals'] = $res['optionals'];
                $node['prompt'] = $res['prompt'];
            } else {
                continue;
            }

            // 題目類型
            $node['type'] = intval($type);
            if ($node['type'] === 1) {
                unset($node['optionals']);
            }

            // 配分
            if ($qtiType !== 'questionnaire') {
                $result = $ctx->xpath_eval('./resprocessing/outcomes/decvar[@defaultval]', $item);
                $score = count($result->nodeset) ? floatval($result->nodeset[0]->get_attribute('defaultval')) : 0;
                $node['score'] = sprintf('%.2f', $score);
            }

            // 設定題目解答及使用者答案
            $node['detailAnswer'] = $res['detailAnswer'];
            $node['quizAnswer'] = $res['quizAnswer'];
            $node['userAnswer'] = $res['userAnswer'];
            $node['reference'] = $res['reference'];

            $nodes[] = $node;
        }

        return $nodes;
    }

    function saveAnswerByItem(&$dom, &$ctx, $item)
    {
        $itemId = $item['item_id'];
        $itemType = intval($item['type']);
        $answer = $item['answer'];

        $qtixml = new QtiDomXml();
        $emojiHandler = new EmojiHandler();

        // 取得作答結果節點
        $ret = $ctx->xpath_eval("//item_result[@ident_ref='{$itemId}']");
        $nodeset = $ret->nodeset;
        if (!is_array($nodeset) || count($nodeset) <= 0) {
            return false;
        }

        // 存入解答時間
        $cur = $nodeset[0];
        $elems = $ctx->xpath_eval('date', $cur);
        if (is_array($elems->nodeset) && (count($elems->nodeset) > 0)) {
            $qtixml->removeAllNode($elems->nodeset);
        }
        $one = $cur->append_child($dom->create_element('date'));
        $qtixml->appendChild($dom, $one, 'type_label', 'submit answer');
        $qtixml->appendChild($dom, $one, 'datetime', $this->getISO8601_datetime());

        // 增加作答次數
        $elems = $ctx->xpath_eval('response/num_attempts', $nodeset[0]);
        if (is_array($elems->nodeset) && count($elems->nodeset) > 0) {
            $num = intval($elems->nodeset[0]->get_content()) + 1;
            $qtixml->replaceContent($dom, $elems->nodeset[0], $num);
        }

        switch ($itemType) {
            case 1: // 是非
                $elems = $ctx->xpath_eval('response/response_value', $nodeset[0]);
                if (count($elems->nodeset) > 0) {
                    $qtixml->removeAllNode($elems->nodeset);
                }

                $elems = $ctx->xpath_eval('response', $nodeset[0]);
                if (is_array($elems->nodeset) && count($elems->nodeset) > 0 && is_array($answer) && !is_null($answer[0])) {
                    $ans = '';
                    if ($answer[0] === 'O') {
                        $ans = 'T';
                    } else if ($answer[0] === 'X') {
                        $ans = 'F';
                    }
                    $qtixml->appendChild($dom, $elems->nodeset[0], 'response_value', $ans);
                }
                break;
            case 2: // 單選
            case 3: // 多選
            case 6: // 配合
                $elems = $ctx->xpath_eval('response/response_value', $nodeset[0]);
                if (count($elems->nodeset) >= 1) {
                    $qtixml->removeAllNode($elems->nodeset);
                }

                $elems = $ctx->xpath_eval('response', $nodeset[0]);
                if (is_array($elems->nodeset) && (count($elems->nodeset) > 0) && is_array($answer) && !is_null($answer[0])) {
                    foreach ($answer as $v) {
                        $qtixml->appendChild($dom, $elems->nodeset[0], 'response_value', $v);
                    }
                }
                break;
            case 4: // 填充
            case 5: // 簡答/申論
                $elems = $ctx->xpath_eval('response/response_value', $nodeset[0]);
                if (count($elems->nodeset) >= 1) {
                    $qtixml->removeAllNode($elems->nodeset);
                }

                $elems = $ctx->xpath_eval('response', $nodeset[0]);

                if (is_array($elems->nodeset) && (count($elems->nodeset) > 0) && is_array($answer) && !is_null($answer[0])) {
                    foreach ($answer as $k => $v) {
                        if (isset($elems->nodeset[$k])) {
                            // 處理文字中 4 字符的 emoji
                            $v =  $emojiHandler->mb4ToUnicode($v);
                            if ($itemType === 4) {
                                // 填充
                                $qtixml->appendChild($dom, $elems->nodeset[$k], 'response_value', $v);
                            } else {
                                // 簡答，簡答只會有一個輸入框
                                $qtixml->appendChild($dom, $elems->nodeset[0], 'response_value', $v);
                            }
                        }
                    }
                }
                break;
        }
        return true;
    }

    function saveAnswer($exam, $data)
    {
        if (is_null($data)) {
            return false;
        }

        // 取得測驗結果
        $content = $exam['result_content'];
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        if (!$dom = domxml_open_mem(str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $content))) {
            return false;
        }
        $ctx = xpath_new_context($dom);

        if (is_array($data)) {
            for ($i = 0, $c = count($data); $i < $c; $i++) {
                $this->saveAnswerByItem($dom, $ctx, $data[$i]);
            }
        }

        $qtixml = new QtiDomXml();
        return $qtixml->setEncoding($dom->dump_mem());
    }

    /**
     *  統計作答對錯題數
     * @param Object $examResultJSON
     * @param float $score 得分
     * @param float $thresholdScore 及格分數
     * @return Array 對錯題數、及格與否
     */
    function statistics($examResultJSON, $score = 0, $thresholdScore = 0) {
        $correct = 0;
        $wrong = 0;

        foreach ($examResultJSON as $key => $value) {
            if ($value['quizAnswer'] === $value['userAnswer']) {
                $correct++;
            } else {
                $wrong++;
            }
        }

        if ($thresholdScore !== -1) {
            $pass = ($score >= $thresholdScore) ? true : false;
        } else {
            $pass = 0;
        }
        return array('correct' => $correct, 'wrong' => $wrong, 'score' => floatval($score), 'thresholdScore' => $thresholdScore, 'pass' => $pass);
    }
}

class QtiResult {
    var $qit_types = array('exam', 'homework', 'questionnaire'),
        $_QTI_which = 'questionnaire',
        $qtiId = 0,
        $courseId = 0,
        $forGuest = false,
        $qtiData = array();
    function init($QTI_which) {
        global $sysSession;
        // 設定路徑
        if (!defined('QTI_env')) {
            list($this->foo, $this->topDir, $this->foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
        } else {
            $this->topDir = QTI_env;
        }
        // TODO: 是否使用 QTI_WHICH
        $this->_QTI_which = (in_array($QTI_which, $this->qit_types)) ? $QTI_which : $this->_QTI_which;

        $this->courseId = ($this->topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;
    }
    function getQtiDetail ($qtiId) {
        $this->qtiId = intval($qtiId);
        $this->qtiData = $this->getQtiData();

        if (count($this->qtiData) > 0) {
            //取得學生的答案資料，並統計
            $this->forGuest = aclCheckWhetherForGuestQuest($this->courseId, $this->qtiId);
        }
    }
    function getQtiData() {
        $testData = array();
        $RS = dbGetStMr('WM_qti_' . $this->_QTI_which . '_test', 'title, begin_time, close_time, setting, content', "exam_id={$this->qtiId}", ADODB_FETCH_NUM);

        if ($RS)
        {
            /*[FLM] NO414,NO422,NO433 衍生問題
             * 原因:測驗試卷有亂數出題的功能，導致匯出是以第一份考卷的題目為依據輸出
             * 處理方式:先取最初的所有題目出來
             */
            list($title, $begin_time, $close_time, $setting, $content) = $RS->FetchRow();
            $anonymity = strpos($setting, 'anonymity') !== FALSE;
            $Title = (strpos($title, 'a:') === 0) ?
                unserialize($title):
                array('Big5'			=> $title,
                    'GB2312'		=> $title,
                    'en'			=> $title,
                    'EUC-JP'		=> $title,
                    'user_define'	=> $title
                );
            if(!$dom = domxml_open_mem(str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $content))) {
                die('Error while parsing the document.');
            }

            $root = $dom->document_element();

            $ctx = xpath_new_context($dom);

            $sos = $dom->get_elements_by_tagname('selection_ordering');

            foreach ($sos as $node) {
                $pnode = $node->parent_node();
                $pnode->remove_child($node);
            }

            $this->replaceItemToComplete($dom, $root, $ctx);

            $dom = @domxml_open_mem(preg_replace(array('/<item\s+xmlns="http:\/\/www\.imsglobal\.org\/question\/qtiv1p2\/qtiasiitemncdtd\/ims_qtiasiv1p2\.dtd"\s+xmlns:wm="http:\/\/www\.sun\.net\.tw\/WisdomMaster"\s+/',
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

            $testData = array(
                'title' => $Title,
                'anonymity' => $anonymity,
                'root' => $root,
                'ctx' => $ctx,
                'dom' => $dom
            );
        }
        return $testData;
    }
    function getUserQtiData() {
        global $sysConn, $sysSession;
        $RS = dbGetStMr('WM_qti_' . $this->_QTI_which . '_result',
            ($this->forGuest ? 'concat(examinee,time_id) AS examinee' : 'examinee') . ', content, score',
            "exam_id={$this->qtiId} and status != 'break' order by submit_time",
            ADODB_FETCH_ASSOC);
        if ($sysConn->ErrorNo()) {
            appSysLog($sysSession->cur_func, 4, 'auto', $_SERVER['PHP_SELF'], $sysConn->ErrorMsg());
            return array(
                'code' => 0,
                'data' => array(
                    'errMsg' => $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg()
                )
            );
        }
        return array(
            'code' => 0,
            'data' => array(
                'list' => $RS,
                'total' => $RS->RecordCount()
            )
        );
    }
    /**
     * 將試卷中的 <item> 轉換為真實題目
     */
    function replaceItemToComplete(&$dom, &$root, &$ctx){
        global $sysConn;

        $ids = array();
        $nodes = $dom->get_elements_by_tagname('item');
        foreach($nodes as $item)
        {
            $ids[] = $item->get_attribute('id');
            $item->set_attribute('score', $item->get_attribute('score'));	// 強制設定一個 score 屬性
        }

        if ($ids){
            $idents = 'ident in ("' . implode('","', $ids) . '")';

            $real_items = $sysConn->GetAssoc('select ident,content from WM_qti_' . $this->_QTI_which . '_item where ' . $idents);
            $ids = array_flip($ids);

            $result = $this->setEncoding($dom->dump_mem());
            $not_exist_idx = array();
            $regs = array();
            if (preg_match_all('!<item\b[^>]*\bid="([^"]+)"[^>]*(\bscore="([^"]+)"([^>]*)?)?(/>|>[^<]*</item>)!isU', $result, $regs))
            {
                // 把配分收集起來
                $scores = array();
                foreach($regs[1] as $k => $v)
                {
                    $scores[$v] = $regs[3][$k];
                }

                // 將分數填進真實 item xml 中
                foreach($real_items as $k => $v)
                {
                    $real_items[$k] = preg_replace('#<decvar[^>]*/>#isU', sprintf('<decvar vartype="Integer" defaultval="%.1f" />', $scores[$k]), $v);
                }

                // 用代換的方式，把試卷中的 item 代換為真實 xml
                $replaces = array();
                foreach($regs[1] as $k)
                {
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
                foreach($not_exist_idx as $id => $foo)
                {
                    $node = getElementById($root, $id);
                    if (!is_null($node))
                    {
                        $parent = $node->parent_node();
                        $parent->remove_child($node);
                    }
                }
            }
            $this->id2ident($dom);
        }
    }

    /**
     * 將試卷的 id 換成 ident
     */
    function id2ident(&$doc){
        if (is_null($doc)) return;
        $secTags = array('section', 'assessment', 'objbank');
        foreach($secTags as $tag){
            $nodes = $doc->get_elements_by_tagname($tag);
            if (is_array($nodes)) foreach($nodes as $node){
                $nn = $node->get_attribute('id');
                $node->set_attribute('ident', $nn);
                $node->remove_attribute('id');
            }
        }
    }

    /*
 * 以下function是取至/learn/exam/item_fetch.php裡面的
 * 由於裡面參數許多是global變數，所以先copy過來這邊單純使用，避免發生不必要的問題
 * [FLM] NO414,NO422,NO433
 */
    function setEncoding($xml, $encoding='UTF-8')
    {
        $tmp = preg_replace(array('!\s*xmlns:wm="http://www.sun.net.tw/WisdomMaster"!', '!<questestinterop\b!'),
            array('', '<questestinterop xmlns:wm="http://www.sun.net.tw/WisdomMaster"'),
            $xml);

        $regs = array();
        if (preg_match('/<\?xml\b[^>]*\?>/isU', $tmp, $regs))
        {
            if (preg_match('/\bencoding\s*=\s*/isU', $regs[0]))
                return $tmp;
            else
                return preg_replace('/\?>/', ' encoding="' . $encoding . '"?>', $tmp, 1);
        }
        else
            return '<?xml version="1.0" encoding="UTF-8"?>' . $tmp;
    }
}
