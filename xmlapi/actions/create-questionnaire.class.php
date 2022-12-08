<?php
/**
 * 新增 QTI 題目
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/teach/exam/item_create_lib.php');
require_once(sysDocumentRoot . '/teach/exam/exam_create_lib.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

// 避免timeout
set_time_limit(0);
// 避免連線斷線後，後端處理也中斷
ignore_user_abort(false);

class CreateQuestionnaireAction extends baseAction
{
    var $_courseId,
        $_mysqlUsername,
        $_validWebService = false;
    /**
     * QTI題目結構轉換
     * @param $item array QTI題目
     * @param $type string 新增或修改(create | modify)
     * @return array
     */
    function itemDataTransform($item, $type = 'create') {
        $itemData = array();
        if (count($item) > 0) {
                // topic_(?)  (?)照題目類型編號
            $itemData = array(
//                'topic_1'       => '',
                'isHTML'        => 1,
                'topic'         => $item['title']['text'],
                'answer'        => '',   //預設答案
                // 分類應該不需要
//                'version'       => '',// 版
//                'volume'        => '',// 冊
//                'chapter'       => '',// 章
//                'paragraph'     => '',// 節
//                'section'       => '',// 段
                'repeat'        => '',   // 是否連續新增
                /*
                 * 題目類型?
                 * 是非: 1; 單選: 2; 多選: 3; 填充: 4; 申論: 5; 配合: 6;
                 */
                'type'          => $item['type'],
                'ticket'        => $item['ticket']
            );
            // 題目附檔
            if (count($item['title']['attaches']) > 0) {
                $itemData['files']['topic_files'] = $item['title']['attaches'];
            }
            // 單選、多選、配合才有
            if (in_array($item['type'], array(2, 3, 6))) {
                for ($i = 0; $i < count($item['optionals']); $i++) {
                    $itemData['render_choices'][$i] = $item['optionals'][$i]['text'];
                    $itemData['files']['render_choice_files'][$i] = $item['optionals'][$i]['attaches'];
                }
            }
            if ($type === 'create') {
                // 新增
                $itemData['gets'] = '';
            } else if ($type === 'modify') {
                // 修改
//                $itemData['origin'];
//                $itemData['ident']; // item_id

            }
        }
        return $itemData;
    }
    function aclCheck ($username) {
        global $sysRoles;
        $code = 0;

        $aclCheck = aclCheckRole($username, $sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant'], $this->_courseId);
        if (!$aclCheck) {
            $code = 2;
        }

        return array('code' => $code);
    }
    function dataHandler (&$data) {
        global $sysSession;
        // 處理接收的資料 - Begin
        $this->_courseId = $data['cid'];
        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);

        if (isset($data['validWebService']) && intval($data['validWebService']) === 1) {
            $this->_validWebService = true;
            // 如果是 jasmine 測試，在試卷標題留下標記
            $data['question']['test']['title']['Big5'] .= "_[IRS)_{test)";
        }
        // 定義 lib 會使用到的常數
        define('API_QTI_which', $data['question']['type']);
        if (!defined('QTI_env')) {
            define('QTI_env', 'teach');
        }
    }
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;

        $itemIdAry = array();
        $code = 0;
        $message = 'success';

        $inputData = file_get_contents('php://input');
        $postData = JsonUtility::decode($inputData);
        $postData['ticket'] = $_REQUEST['ticket'];
        $this->dataHandler($postData);

        // 確認傳進來的 course_id 是否有新增權限
        $aclCheck = $this->aclCheck($this->_mysqlUsername);
        if ($aclCheck['code'] !== 0) {
            $this->returnHandler($aclCheck['code'], 'fail');
        }

        // 儲存 course_id
        $sysSession->course_id = $this->_courseId;
        $sysSession->restore();

        // 指定題目製作問卷
        $itemIdAry = (count($postData['question']['assign_items']) > 0) ? $postData['question']['assign_items'] : $itemIdAry;

        // 新增題目
        if (count($postData['question']['item']) > 0) {
            if (isset($this->_validWebService) && $this->_validWebService === true) {
                // 如果是 jasmine 測試，在題目留下標記
                $postData['question']['item']['title']['text'] .= "_[IRS)_{test)";
            }
            // 資料轉型
            $itemData = $this->itemDataTransform($postData['question']['item']);
            $item = new itemMaintain();
            // 目前沒用到修改，直接指定為新增 mode
            $item->isModify = false;
            $itemResult = $item->saveItem($itemData);

            if ($item->isModify) {
                $logMsg = 'Modify ' . API_QTI_which . ' Item: [' . $item->ident . ']';
            } else {
                $logMsg = 'Create ' . API_QTI_which . ' Item: [' . $item->ident . ']';
            }
            if ($itemResult['ErrCode'] == 0) {
                $itemIdAry[] = $item->ident;
                if (isset($this->_validWebService) && $this->_validWebService === true) {
                    $data["item_id"] = $item->ident;
                }
                // 更新成功
                appSysLog($sysSession->cur_func, $item->courseId, 0, 0, 'auto', $_SERVER['PHP_SELF'], $logMsg . ' success');
            } else {
                // 更新失敗
                $errMsg = implode(' : ', $itemResult);
                appSysLog($sysSession->cur_func, $item->courseId, 0, 2, 'auto', $_SERVER['PHP_SELF'], $logMsg . ' fail:' . $errMsg);

                $code = 2;
                $message = 'fail';
                $data['errMsg'] = $errMsg;
            }
        }
        // 新增測驗
        if ($code === 0) {
            // TODO: type 為 exam 時，instance 為0 ，新增試卷失敗
            $testData = $postData['question']['test'];
            $exam = new examMaintain();
            $exam->isModify = false;

            $createContentXml = $exam->createContentXml($itemIdAry);
            $testData['content'] = ($createContentXml['code'] === 0) ? $createContentXml['data'] : '';
            $examResult = $exam->saveExam($testData);
            appSysLog(999999017, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], 'Add QTI test:' . $message, $sysSession->username);

            $data['qti_id'] = intval($examResult['data']['qti_id']);
        }

        $this->returnHandler($code, $message, $data);
    }
}