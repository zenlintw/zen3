<?php
/**
 * 取得banner列表.
 */

include_once(dirname(__FILE__).'/action.class.php');
include_once(dirname(__FILE__).'/my-course-history.class.php');
include_once(sysDocumentRoot . '/lib/course.php');
include_once(PATH_LIB . 'course.php');

class GetRollcallCourseListAction extends baseAction
{
    var $_mysqlUsername = null;
    var $username = null;

    function calculateYear () {
        // 從管理者>課程管理>校務系統資料同步設定>排程查詢撈出第一學期起始日期
        $today = date('Y-m-d');
        $where = " `status` = 'select' AND `term` = 1 AND `sdate` <= '{$today}' ";
        $order = '  ORDER BY  CAST(`years` AS DECIMAL(10, 2)) DESC ';
        $year = dbGetOne('`CO_crontab`', '`years`', $where . $order);
        
        return $year;
    }
	
    function main()
    {

        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession, $sysRoles;
        $query = array();

        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);
        $this->username = $sysSession->username;
        $courseYear = $this->calculateYear();

        // 網址參數處理
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        $pagesize = isset($_GET['size']) ? intval($_GET['size']) : 10;
        $keyword = (isset($_GET['keyword'])) ? trim(charset2Utf8($_GET['keyword'])) : "";

        // 取得使用者課程
        $courseHandler = new UserCourse();
        $roles = $sysRoles['teacher']|$sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['student']|$sysRoles['auditor'];
        //$courseHandler->_ignoreCourseImage = true;
        if ($keyword !== "") {
            $query[] = $courseHandler->getCaptionQuery($keyword);
        }
        $query[] = $courseHandler->getCaptionQuery($courseYear);
//        $query[] = "LOCATE('" . $courseYear . "', `caption`)";
        $courses = $courseHandler->getUserCourse($sysSession->username, $roles, $offset, $pagesize, implode(" AND ", $query), false);

        $totalSize = $courses['totalSize'];
        $courseList = array();
        if (count($courses['result']) > 0) {
            foreach ($courses['result'] as $course) {
                //課程總人數
                $student_number = dbGetOne(
                    'WM_term_major',
                    'count(*)',
                    sprintf(
                        "`course_id` = %d AND role & %d",
                        $course['course_id'],
                        $sysRoles['student']
                    )
                );

                // 取得學員最後一次簽到記錄
                $last_signed_time = dbGetOne(
                    '`APP_rollcall_base`AS B
                        INNER JOIN `APP_rollcall_record` AS R ON B.`rid` = R.`rid`',
                    'R.`rollcall_time`',
                    sprintf(
                        "B.`course_id` = %d AND R.`username` = '%s' ORDER BY R.`rollcall_time` DESC",
                        $course['course_id'],
                        $this->_mysqlUsername
                    )
                );

                $courseList[] = array(
                    'course_id' => $course['course_id'],
                    'title' => $course['title'],
                    'teacher' => $course['teacher'],
                    'img_src' => $course['img_src'],
                    'period' => $course['period'],
                    'role' => ($course['role'] === "manager") ? 'teacher' : 'student',
                    'student_number' => intval($student_number),
                    'last_signed_time' => $last_signed_time
                );
            }
        }

        // make json
        $jsonObj = array(
            'code' => 0,
            'message' => 'success',
            'data' => array(
                'total_size' => $totalSize,
                'list' => $courseList
            ),
        );

        $jsonEncode = JsonUtility::encode($jsonObj);
        
        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}