<?php
/**
 * 修改 QTI 試卷
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/teach/exam/exam_create_lib.php');

set_time_limit(0);
// 避免連線斷線後，後端處理也中斷
ignore_user_abort(false);

class ModifyQuestionnaireAction extends baseAction
{
    var $_qtiTypes = array("exam", "homework", "questionnaire"),
        $_mysqlUsername = "";

    function modifyData($examData, $modifyData) {
        $enableValue = array("close_time", "publish");
        foreach($examData AS $key => $val) {
            // 原資料處理
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
        return $examData;
    }
    function replace_content( &$node, $new_content )
    {
        $kids = &$node->child_nodes();
        foreach ( $kids as $kid )
            if ( $kid->node_type() == XML_TEXT_NODE )
                $node->remove_child ($kid);
        $node->set_content($new_content);
    }
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
    function deleteTestDatas($qtiType, $examList, $itemList) {
        $testString = "_[IRS)_{test)";
        // 資料處理
        foreach($examList AS $k => $v) {
            $examList[$k] = intval($v);
        }
        foreach($itemList AS $k => $v) {
            $itemList[$k] = mysql_real_escape_string($v);
        }
        // 刪除試卷
        dbDel(
            sprintf("`WM_qti_%s_test`", $qtiType),
            sprintf("exam_id in (%s) AND LOCATE('%s', `title`)", implode(",", $examList), $testString)
        );
        // 刪除題目
        dbDel(
            sprintf("`WM_qti_%s_item`", $qtiType),
            sprintf("ident in ('%s') AND LOCATE('%s', `title`)", implode("','", $itemList), $testString)
        );
    }

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession, $sysRoles;

        // 處理接收的資料 - Begin
        $inputData = file_get_contents('php://input');
        $postData = JsonUtility::decode($inputData);
        $qtiType =  $postData['question']['type'];
        $qtiId = intval($postData['question']['eid']);
        $testData = $postData['question']['test'];
        $jasmineData = $postData['jasmine'];
        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);
        if (isset($postData['validWebService']) && intval($postData['validWebService']) === 1) {
            $validWebService = true;
        }
        // 處理接收的資料 - End

        if (!in_array($qtiType, $this->_qtiTypes)) {
            $this->returnHandler(2, "fail", array('errMsg' => "type error!"));
        }
        // 定義 lib 會使用到的常數
        define('API_QTI_which', $qtiType);
        if (!defined('QTI_env')) {
            define('QTI_env', 'teach');
        }

        // 取得目前試卷的設定
        $examData = dbGetAssoc(
            sprintf("`WM_qti_%s_test`", $qtiType),
            "*",
            sprintf("exam_id = %d", $qtiId));
        if (!$examData) {
            $this->returnHandler(3, "fail", array('errMsg' => "No test!"));
        }
        // 將修改部分加入試卷設定
        $examData = $this->modifyData($examData[$qtiId], $testData);
        $examData['exam_id'] = $qtiId;

        // 確認編輯權限
        $aclCheck = aclCheckRole($this->_mysqlUsername, $sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant'], intval($examData['course_id']));
        if (!$aclCheck) {
            $this->returnHandler(4, "fail", array('errMsg' => "Access Denied!"));
        }

        // 設定 course_id
        $sysSession->course_id = intval($examData['course_id']);
        $sysSession->restore();

        // 將已刪除的 item 從 content 移除
        $examData['content'] = $this->removeUnuseItem($examData['content'], $qtiType);
        // 修改問卷
        $exam = new examMaintain();
        $exam->isModify = true;
        $examResult = $exam->saveExam($examData);

        appSysLog(999999018, $sysSession->course_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], 'MODIFY QTI test:' . $examResult['message'], $this->_mysqlUsername);

        // 如果是 jasmine 測試，將前面測試資料都刪除
        if (isset($validWebService) && $validWebService === true) {
            // 刪除測試資料
            $this->deleteTestDatas($qtiType, $jasmineData["testList"], $jasmineData["itemList"]);
        }

        $this->returnHandler($examResult['code'], $examResult['message'], $examResult['data']);
    }
}