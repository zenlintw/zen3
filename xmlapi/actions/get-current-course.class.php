<?php
/**
 * 取得該學生可用課程(IRS)
 *
 * 回傳值
 *     code 2 沒有權限
 *     code 3 沒有課程編號
 */
include_once(dirname(__FILE__) . '/action.class.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

class GetCurrentCourseAction extends baseAction
{
    var $_mysqlUsername = '',
        $_courseIds;

    function dataHandler ($inputData) {
        global $sysSession;
        if (!isset($inputData['cid'])) {
            $this->returnHandler(3, 'fail');
        }
        if (isset($inputData['aesCode'])) {
            $inputData['username'] = APPEncrypt::decrypt($inputData['username'], intval($inputData['aesCode']));
        }
        $this->_courseIds = isset($inputData['cid']) ? mysql_real_escape_string($inputData['cid']) : '';
        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);
    }

    function getStudentData ($username, $courseIds) {
        global $sysConn;
        $courseRows = dbGetStMr(
            "`WM_term_major` LEFT JOIN `WM_term_course` ON `WM_term_major`.`course_id` = `WM_term_course`.`course_id` ",
            "`WM_term_major`.`course_id`, `WM_term_course`.`caption`",
            sprintf("`username` = '%s' AND `WM_term_major`.`course_id` in(%s)", $username, $courseIds)
        );

        $currentCourses = array();
        if ($courseRows) {
            while ($row = $courseRows->FetchRow()) {

                $currentCourse = array();
                $currentCourse['courseId'] = $row['course_id'];
                $currentCourse['title'] = $this->getBig5CourseName($row['caption']);
                $currentCourses[] = $currentCourse;
            }
        }
        return $currentCourses;
    }

    /**
     *  取出課程標題
     *
     * @param string $title 試卷名稱 (複合式欄位)
     *
     * @return string 標題
     **/
    function getBig5CourseName ($title) {
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
        return htmlspecialchars($paperTitle['Big5']);
    }

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        // url 參數處理
        $inputData = $_GET;
        $this->dataHandler($inputData);

        // 取得該課學生列表
        $studentData = $this->getStudentData($this->_mysqlUsername, $this->_courseIds);

        $this->returnHandler(0, 'OK', $studentData);
    }
}