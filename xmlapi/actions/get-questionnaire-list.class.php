<?php
define('API_QTI_which', 'questionnaire');
if (!defined('QTI_env')) {
    define('QTI_env', 'learn');
}

include_once(dirname(__FILE__) . '/action.class.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/exam_lib.php');

/**
 * 取得課程測驗列表
 */
class GetQuestionnaireListAction extends baseAction
{
    var $_mysqlUsername = '';
    function main()
    {
        global $sysSession, $sysRoles;

        $testTable = 'WM_qti_questionnaire_test';
        $unitList = array();
        $questionnaireListResult = array();
        $unitQuestionnaires = array();
        $unitInfo = array();
        $announce = array(
            'never'       => 0,
            'now'         => 1,
            'close_time'  => 2,
            'user_define' => 3
        );
        
        // 驗證 Ticket
        parent::checkTicket();

        $offset = (!isset($_GET['offset'])) ? 0 : intval($_GET['offset']);
        $size = (!isset($_GET['size'])) ? 15 : intval($_GET['size']);
        $keyword = (isset($_GET['keyword']) && !empty($_GET['keyword'])) ? charset2Utf8(trim($_GET['keyword'])) : '';

        // 取校務問卷，如果有則將school_id加入稍後取問卷的清單陣列
        $amount = dbGetOne($testTable, 'count(*)', "`course_id` = {$sysSession->school_id}");
        if ($amount > 0) {
            $unitList[] = $sysSession->school_id;
            $unitInfo[$sysSession->school_id] = array(
                'name' => strip_tags($sysSession->school_name),
                'period' => '0 ~ 0');
        }

        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);
        // 取得使用者的課程列表
        $rs = &dbGetCoursesWithQTI(
            'M.*, C.caption, C.teacher, C.st_begin, C.st_end',
            $this->_mysqlUsername,
            $sysRoles['auditor']|$sysRoles['student']|$sysRoles['teacher']|$sysRoles['assistant']|$sysRoles['instructor'],
            'course_id DESC',
            '',
            $testTable,
            $keyword
        );

        if ($rs['result']) {
            while ($row = $rs['result']->FetchRow()) {
                $unitList[] = $row['course_id'];
                $unitName = getCaption($row['caption']);
                $period = str_replace(
                    '-',
                    '/',
                    sprintf('%s%s',
                        ((!empty($row['st_begin']))?$row['st_begin']:'0').' ~ ',
                        ((!empty($row['st_end']))?$row['st_end']:'0'))
                );
                $unitInfo[$row['course_id']] = array(
                    'name' => strip_tags(nodeTitleStrip(htmlspecialchars_decode($unitName[$sysSession->lang]))),
                    'period' => $period
                );
            }
        }

        // 如果清單陣列有值，則表示要進行取得問卷的動作
        if (count($unitList) > 0) {
            $questionnaireListResult = getQTIListRecord(implode(',', $unitList), API_QTI_which, $this->_mysqlUsername, $offset, $size);
        }
        $questionnaireList = $questionnaireListResult['record'];
        $questionnaireListTotalSize = $questionnaireListResult['total'];

        // 有需要取出問卷
        if (count($questionnaireList) > 0) {
            while(!$questionnaireList->EOF){
                $unitId = $questionnaireList->fields['course_id'];
                $qtiId = $questionnaireList->fields['exam_id'];

                $aclVerified = verifyQTIPermission(API_QTI_which, $unitId, $qtiId, $sysSession->username);
                if (!$aclVerified) {
                    $questionnaireList->MoveNext();
                    continue;
                }
                $now = strtotime(date('Y-m-d H:i:s'));

                $times = getQTIAnswerCount(API_QTI_which, $questionnaireList->fields['exam_id'], $sysSession->username);
                $isTimeout = checkAPPExamWhetherTimeout($questionnaireList->fields, $now, $times, '', '', 'questionnaire');

                $unitQuestionnaires[] = array(
                    'unit_id' => (integer)$unitId,
                    'unit_name' => $unitInfo[$unitId]['name'],
                    'unit_type' => (strlen(strval($unitId)) === 5)? 'school' : 'course',
                    'unit_period' => $unitInfo[$unitId]['period'],
                    'examId' => (integer)$questionnaireList->fields['exam_id'],
                    'title' => qtiPaperTitle($questionnaireList->fields['title']),
                    'status' => $aclVerified && !$isTimeout ? 1 : 0,
                    'announce_type' => intval($announce[$questionnaireList->fields['announce_type']]),
                    'announce_time' => $questionnaireList->fields['announce_time'],
                    'amount' => getQTIContentItemCount($questionnaireList->fields['content']),
                    'modifiable' => ($questionnaireList->fields['modifiable'] === 'Y')? true : false,
                    'notice' => $questionnaireList->fields['notice'],
                    'beginTime' => $questionnaireList->fields['begin_time'] == '9999-12-31 00:00:00' ? '0000-00-00 00:00:00' : $questionnaireList->fields['begin_time'],
                    'closeTime' => $questionnaireList->fields['close_time'],
                    'upload' => ($questionnaireList->fields['setting'] == 'upload')? true : false,
                    'anonymity' => ($questionnaireList->fields['setting'] == 'anonymity')? true : false,
                    'done' => ($times > 0)? true : false,
                    'statistics_viewable' => false,
                    'pageFlow' => 0
                );
                $questionnaireList->MoveNext();
            }
        }

        $responseObject = array(
            'code' => 0,
            'message' => 'success',
            'data' => array(
                'total_size' => $questionnaireListTotalSize,
                'list' => $unitQuestionnaires
            )
        );
        $statusCode = '200';
        $this->header('application/json', $statusCode);
        echo JsonUtility::encode($responseObject);
        exit();
    }
}