<?php
$qtiType = $_POST['exam_type'];
define('QTI_which', $qtiType);
define('API_QTI_which', $qtiType);

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/teach/exam/item_create_lib.php');
require_once(sysDocumentRoot . '/teach/exam/exam_create_lib.php');

/*print('<pre>');
print_r($_POST);
print('</pre>');
die;*/

function modifyData($examData, $modifyData) {
        $enableValue = array("close_time", "publish","title");
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
            	if ($key=='title') {
                    $lang['Big5']        = htmlspecialchars($modifyData[$key]);
			        $lang['GB2312']      = htmlspecialchars($modifyData[$key]);
			        $lang['en']          = htmlspecialchars($modifyData[$key]);
			        $lang['EUC-JP']      = htmlspecialchars($modifyData[$key]);
			        $lang['user_define'] = htmlspecialchars($modifyData[$key]);
			        $examData[$key] = $lang;
            	} else {
            		$examData[$key] = $modifyData[$key];
            	}
                
            }
        }
        // 將試卷測定為支援 APP
        $examData["qti_support_app"] = "Y";
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
function testDataTransform($data) {
    $returnData = array();
    $test = $data['exam_info'];
    $now = date("Y-m-d H:i:s");
    
    if ($test !== null) {
            // 測驗詳細資料
            $returnData = array(
                "title" => $test['title'],
                "ex_type" => 5,    // IRS(愛上互動) 為 5
                "notice" => "由 IRS 功能產生",
                "do_interval" => $test['interval'],
                "threshold_score" => $test['threshold_score'],
                "qti_support_app" => "Y",
                // 建立試卷，同時將開始時間設為現在
                "begin_time" => ($data['exam_id'] === 0) ? $now : "",
                "modifiable" => "Y"

            );
    }
    // 測驗設定
    $returnData['publish'] = $data['action'];
    return $returnData;
}

function itemDataTransform($item, $type = 'create') {
        global $sysSession;
        $itemData = array();
        if (intval($item['type']) == 1) {
            // webservice 傳值為 O、X，PRO處理為 T、F
            $item['answer'] = ($item['answer'] === "O") ? "T" : "F";
        }
        
        
        if (count($item) > 0) {
                // topic_(?)  (?)照題目類型編號
            $itemData = array(
                'isHTML'        => 1,
                'topic'         => $item['title'],
                // 複選題須將答案轉陣列
                'answer'        => $item['answer'],

                'type'          => $item['type'],
                'ticket'        => $sysSession->ticket
            );

            // 題目附檔
            if (count($item['attaches']) > 0) {
                $itemData['attaches']['topic_files'] = $item['attaches'];
            }
            
            if (count($item['attaches_rm']) > 0) {
                $itemData['topic_files_rm'] = $item['attaches_rm'];
            }
            
            if (count($item['choice_attaches_rm']) > 0) {
                $itemData['render_choice_files_rm'] = $item['choice_attaches_rm'];
            }
            
            // 選項在單選、多選、配合題型才有
            if (in_array($item['type'], array(2, 3, 6))) {
                for ($i = 0; $i < count($item['options']); $i++) {
                    $itemData['render_choices'][$i] = $item['options'][$i]['text'];
                    $itemData['attaches']['render_choice_files'][$i] = $item['options'][$i]['attaches'];
                }
            }
            if ($type === 'create') {
                // 新增
                $itemData['gets'] = '';
            } else if ($type === 'modify') {
                // TODO: 修改
                $itemData['origin'] = '';
                $itemData['ident']  = $item['item_id'];
                $itemData['ticket'] = md5($itemData['origin'] . $itemData['ident'] . sysTicketSeed . $sysSession->course_id . $_COOKIE['idx']);
            }
        }
        return $itemData;
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
                replace_content($nodes,$new_content);

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

if (isset($_POST['question_info'])) {

    
    foreach ($_POST['question_info'] as $item) {

        $curItemId = $item['item_id'];
        $itemMaintain = new itemMaintain();
        // 檢查需要新增的題目
        if ($curItemId == '') {
        	// 資料轉型成 lib 所需結構
            $itemData = itemDataTransform($item);
            $itemMaintain->isModify = false;
            // 引用 lib
            $itemResult = $itemMaintain->saveItem($itemData);
            $curItemId = $itemMaintain->ident;

        } else {
        	$itemData = itemDataTransform($item,'modify');
        	
            $itemMaintain->isModify = true;
        	$itemResult = $itemMaintain->saveItem($itemData);
        }
        
        // 測驗才需要分數
        if ($qtiType === "exam") {
            $itemScore[$curItemId] = $item['score'];
        }
        // 紀錄試卷裡的題目 item_id
        $itemIdAry[] = $curItemId;
       
    }
} else if (!isset($_POST['question_info']) || count($_POST['question_info']) === 0) {
    $data['code'] = 2;
	$msg = json_encode($data);
	if ($msg != '') {
	    echo $msg;
	    exit();
	}
}

$examMaintain = new examMaintain();
// 轉換試卷資料格式
$testData = testDataTransform($_POST);

// 由 exam_id 是否有設定來判斷是不是修改試卷
if ($_POST['exam_id'] != 0) {
    // 修改試卷
    $examMaintain->isModify = true;

    // 取得目前試卷的設定，saveExam 的 type 是看 ex_type 再取得原試卷時作個轉換
    $examData = dbGetRow(
                    sprintf("`WM_qti_%s_test`", $qtiType),
                    "*, `type` as `ex_type`",
                    sprintf("exam_id = %d", $_POST['exam_id'])
    );

    // 將已刪除的 item 從 content 移除
    //$examData['content'] = removeUnuseItem($examData['content'], $qtiType);
    
    $examData['threshold_score'] = preg_match('/\bthreshold_score="([0-9]*)"/', $examData['content'], $regs) ? $regs[1] : '';
    
    $createContentXml = $examMaintain->createContentXml($itemIdAry, $itemScore,$examData['content']);
    $examData['content'] = $createContentXml['data'];

    // 將修改部分加入試卷設定
    $testData = modifyData($examData, $testData);

} else {
    // 新增試卷
    $examMaintain->isModify = false;
    
    $lang['Big5']   = htmlspecialchars($testData['title']);
	$lang['GB2312'] = htmlspecialchars($testData['title']);
	$lang['en']     = htmlspecialchars($testData['title']);
	$lang['EUC-JP'] = htmlspecialchars($testData['title']);
	$lang['user_define'] = htmlspecialchars($testData['title']);
	$testData['title'] = $lang;

    // 建立試卷內容
    $createContentXml = $examMaintain->createContentXml($itemIdAry, $itemScore);
    $testData['content'] = ($createContentXml['code'] === 0) ? $createContentXml['data'] : '';
}                

$examResult = $examMaintain->saveExam($testData);

if ($_POST['forGuest'])
{
    $noGuestAcl = true;
    if ($_POST['exam_id'])
    {
        $re = dbGetOne('WM_acl_list AS L, WM_acl_member AS M',
                       'count(*)',
                       "L.function_id=1800300200 AND L.unit_id={$sysSession->course_id} AND L.instance={$_POST['exam_id']} AND L.acl_id=M.acl_id AND M.member='guest'");
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
                      $sysSession->course_id,
                      $examResult['data']['qti_id']
                     )
             );
        if ($sysConn->ErrorNo() === 0){
            $new_id = $sysConn->Insert_ID();
            dbNew('WM_acl_member', 'acl_id,member', $new_id . ',"guest"');
        }
    }
} else {
    if ($qtiType=='questionnaire') {
        $old_lists = aclGetAclIdByInstance(1800300200, $sysSession->course_id, $examResult['data']['qti_id']);
        $will_rm = implode(',', $old_lists);
        if ($will_rm != ''){
            dbDel('WM_acl_member', sprintf('acl_id in (%s)', $will_rm));
            dbDel('WM_acl_list', sprintf('acl_id in (%s)', $will_rm));
        }
    }
}


$data['code'] = 1;
$data['id'] = $examResult['data']['qti_id'];

	
$msg = json_encode($data);
if ($msg != '') {
    echo $msg;
}





?>