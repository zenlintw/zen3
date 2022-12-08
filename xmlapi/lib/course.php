<?php

define('XMLAPI', true);
include_once(dirname(__FILE__).'/../actions/my-course-history.class.php');
include_once(sysDocumentRoot . '/lib/course.php');

class UserCourse
{
    // 忽略課程圖片的取得
    var $_ignoreCourseImage = false;

    /**
     * 取得教授或選修中的課程 (會依身份而檢查開放日期與狀態) 會連帶回傳課程圖片
     * 取代原 initialize 的 dbGetCoursesWithPicture
     * @param string $fields
     * @param $where
     * @param $roles
     * @param bool $order
     * @param bool $limit
     * @return array
     */
    function getCourses($fields = 'count(*)', $where, $roles, $order = false, $limit = false, $gid = 10000000) {
        global $sysSession, $sysRoles, $sysConn, $ADODB_FETCH_MODE;
        $roleQuery = array();

        // 資料處理
        $username = mysql_real_escape_string($sysSession->username);
        $where = $where !== "" ? $where : "1 = 1";
        $roles = $roles ? $roles : $sysRoles['student'];

        // 角色權限處理
        if ($roles & $sysRoles['auditor']) {
            // 旁聽生
            $roleQuery[] = sprintf(
                "(M.role&%d AND 
                    (
                        C.status = 1 OR 
                        (
                            C.status = 2 AND 
                            (ISNULL(C.st_begin) OR C.st_begin <= CURDATE()) AND
                            (ISNULL(C.st_end) or C.st_end >= CURDATE())
                        )
                    )
                )",
                $sysRoles['auditor']
            );
        }
        if ($roles & $sysRoles['student']) {
            // 正式生
            $roleQuery[] = sprintf(
                "(M.role&%d AND
                    (
                        C.status = 1 OR
                        C.status = 3 OR 
                        (
                            (C.status=2 OR C.status=4) AND
                            (isnull(C.st_begin) or C.st_begin<=CURDATE()) AND
                            (isnull(C.st_end) or C.st_end>=CURDATE())
                        )
                    )
                )",
                $sysRoles['student']
            );
        }
        if ($roles & ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher'])) {
            // 教師、講師、助教
            $roleQuery[] = sprintf(
                "(M.role&%d AND (C.status BETWEEN 1 AND 5))",
                $roles & ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher'])
            );
        }

        $groupWhere  = '';
        if($gid != 10000000){
            $array_group = dbGetCol("WM_term_group","child","child <> 0 and parent = {$gid}");
            $array_group = implode(",",$array_group);
            $groupWhere  = " AND C.course_id IN({$array_group})";
        }

        // 標準取得 query
        $sql = sprintf(
            "SELECT 
                SQL_CALC_FOUND_ROWS %s
            FROM `WM_term_major` AS M
            INNER JOIN WM_term_course AS C
                ON C.`course_id` = M.`course_id`
            LEFT JOIN CO_course_picture AS P
                ON M.`course_id` = P.`course_id`
            WHERE M.`username` = '%s' AND C.kind = 'course' AND (%s) AND (%s)
            %s
            %s",
            $fields,
            $username,
            implode(" OR ", $roleQuery),
            $where . $groupWhere,
            ($order && preg_match('/^(\w+|`\w+`)(\.(\w+|`\w+`))*(\s+(asc|desc))?(\s*,\s*(\w+|`\w+`)(\.(\w+|`\w+`))*(\s+(asc|desc))?)*$/i', $order) ? ('ORDER BY ' . $order) : ''),
            ($limit && preg_match('/^\d+(\s*,\s*\d+)?$/', $limit) ? (' LIMIT ' . $limit) : '')
        );

        chkSchoolId('WM_term_major');
        $curr_mode = $ADODB_FETCH_MODE;
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $rs = $sysConn->Execute($sql);

        $ADODB_FETCH_MODE = $curr_mode;

        $totalSize = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));

        return array(
            'totalSize' => $totalSize,
            'result' => $rs
        );
    }

    /**
     * 取得課程名稱的 query
     * @param $keyword 關鍵字
     * @return string
     */
    function getCaptionQuery($keyword) {
        global $sysConn;

        $query = "";
        if ($keyword !== '') {
            $keyword = mysql_real_escape_string($keyword);
            $query = sprintf(
                "(C.caption LIKE '%%%s%%' OR C.caption like %s)",
                $keyword,
                $sysConn->qstr('a:2:{s:4:"Big5"%s:%:%s:6:"GB2312"%s:%:"%' . $keyword . '%"%}', get_magic_quotes_gpc())
            );
        }
        return $query;
    }

    function getUserCourse($username, $roles, $offset = null, $pagesize = null, $query = "", $getAll = true, $gid = 10000000) {
        global $sysRoles;
        $sizeWhere = '';
        $totalSize = 0;
        $userCourseData = array();

        if (isset($offset) && isset($pagesize)) {
            $sizeWhere = "{$offset}, {$pagesize}";
        }

        // 取得使用者的課程與圖片列表
//        $result = &dbGetCoursesWithPicture(
//            sprintf(
//                'M.*, C.`caption`, C.`teacher`, C.`st_begin`, C.`st_end`, P.`picture`, P.`mime_type`, M.`role`&%d as `is_teacher`',
//                $sysRoles['teacher']|$sysRoles['assistant']|$sysRoles['instructor']
//            ),
//            $username,
//            $roles,
//            'course_id DESC',
//            $sizeWhere,
//            $query
//        );
        $result = $this->getCourses(
            sprintf(
                'M.*, C.`caption`, C.`teacher`, C.`st_begin`, C.`st_end`, P.`picture`, P.`mime_type`, M.`role`&%d as `is_teacher`',
                $sysRoles['teacher']|$sysRoles['assistant']|$sysRoles['instructor']
            ),
            $query,
            $roles,
            'course_id DESC',
            $sizeWhere,
            $gid
        );
        if (!empty($result) && $result['totalSize'] > 0) {
            $totalSize = $result['totalSize'];

            while ($row = $result['result']->FetchRow()) {
                $userCourseData[] = $this->generalData($username, $row, $getAll);
            }
        }

        return array(
            "totalSize" => $totalSize,
            "result"    => $userCourseData
        );
    }

    /* 公用程式 */
    /**
     * 組合資料供顯示
     * @param $row 資料庫資料
     * @return array
     */
    function generalData($username, $row, $getAll) {
        global $sysSession;
        $read_hours = 0;
        $favorite = 0;
        $progress = 0;
        $imgSrc = "";

        // 課程名稱
        $cp = getCaption($row['caption']);
	    $title = $cp[$sysSession->lang];

        // 課程期間
        $period = str_replace(
            '-',
            '/',
            sprintf('%s%s',
                ((!empty($row['st_begin']))?$row['st_begin']:'0').' ~ ',
                ((!empty($row['st_end']))?$row['st_end']:'0'))
        );

        if ($this->_ignoreCourseImage === false) {
            // 取出課程圖片
            if (!is_null($row['picture']) && !is_null($row['mime_type'])) {
                $imgSrc = "data:{$row['mime_type']};base64," . $row['picture'];
            } else {
                // TODO: 預設圖片改由my-course-list 取得
                // 沒有設定就取預設圖片
                $imgSrc = "data:image/jpeg;base64," . base64_encode(file_get_contents(sysDocumentRoot.'/theme/default/app/default-course-picture.jpg'));
            }
        }
        if ($getAll === false) {

            // 只取得課程編號、課程名稱、圖片、和角色
            return array(
                'course_id' => intval($row['course_id']),
                'title' => $title,
                'teacher' => $row['teacher'],
                'img_src' => $imgSrc,
                'period' => $period,
                // 老師、講師、助教皆為可開課人員
                'role' => (intval($row['is_teacher']) > 0) ? 'manager' : 'member'
            );
        }
        // 我的課程列表才取得
        // 判斷最愛課程
        $userFavoriteCourses = $this->getUserFavoriteCourse($username);
        if (in_array($row['course_id'], $userFavoriteCourses)) {
            $favorite = intval(1);
        } else {
            $favorite = intval(0);
        }

        // 取得修課進度 (getProgress寫在wmpro的lib/course.php)
        $table = 'WM_term_path';
        $fields = 'content';
        $where = "course_id='{$row['course_id']}' ORDER by serial DESC";
        $courseXML = dbGetOne($table, $fields, $where);
        $getProgress = getProgress($row['course_id'], $courseXML, $username);
        $progress = intval($getProgress['progress']);

        // 閱讀時數
        $read_hours = MyCourseHistoryAction::getUserReadHours($username, $row['course_id']);

        return array(
            'course_id' => intval($row['course_id']),
            'title' => $title,
            'teacher' => $row['teacher'],
            'img_src' => $imgSrc,
            'update_datetime' => str_replace('-', '/', $row['last_login']),
            'class_count' => intval($row['login_times']),
            'read_hours' => intval($read_hours),
            'post_count' => intval($row['post_times']),
            'discuss_count' => intval($row['dsc_times']),
            'period' => $period,
            'bookmark' => intval($favorite),
            'progress' => intval($progress)
        );
    }
    /**
     *  getUserDir():取得取得學員的user目錄路徑
     *
     * @param $username string 學員帳號
     * @return string user directory path
     */
    function getUserDir($username)
    {
        $username = trim($username);
        // 取出前兩個字元
        $one = substr($username, 0, 1);
        $two = substr($username, 1, 1);

        // 檢查使用者的目錄存不存在
        $filename = sysDocumentRoot . DIRECTORY_SEPARATOR . 'user';
        if (!@is_dir($filename)) @mkdir($filename);
        $filename .= DIRECTORY_SEPARATOR . $one;
        if (!@is_dir($filename)) @mkdir($filename);
        $filename .= DIRECTORY_SEPARATOR . $two;
        if (!@is_dir($filename)) @mkdir($filename);
        $filename .= DIRECTORY_SEPARATOR . $username;
        if (!@is_dir($filename)) @mkdir($filename);

        return $filename;
    }

    /**
     * 取得我的最愛課程的XML
     *
     * @param string $username 學員帳號
     * @return string xml
     */
    function getUserFavoriteCourseXML($username)
    {
        $userDir = $this->getUserDir($username);
        $favoriteXML = $userDir.'/my_course_favorite.xml';
        if (!file_exists($favoriteXML)) {
            // 如果沒有檔案，則直接比照標準版，補一個空資料
            $xmlstr  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' .
                '<manifest>' .
                '<setting></setting>' .
                '</manifest>';
            touch($favoriteXML);
            if ($fp = fopen($favoriteXML, 'w')) {
                @fwrite($fp, $xmlstr);
            }
            fclose($fp);
        } else {
            // 若有，則直接取用
            $xml = file($favoriteXML);
            $xmlstr = implode('', $xml);
        }

        return $xmlstr;
    }

    /**
     * 我的最愛課程ID
     *
     * @param string $username 學員帳號
     * @return array course id 的 array
     */
    function getUserFavoriteCourse($username)
    {
        $courses = array();
        $favoriteXML = $this->getUserFavoriteCourseXML($username);
        $xmlDoc = domxml_open_mem($favoriteXML);
        $ctx = xpath_new_context($xmlDoc);
        $coursePath = '/manifest//course';
        $xrs = $ctx->xpath_eval($coursePath);
        if (is_array($xrs->nodeset)) {
            foreach ($xrs->nodeset as $course) {
                $courseId = $course->get_attribute('id');
                $courses[] = $courseId;
            }
        }

        return $courses;
    }

    /**
     * 驗證討論板是不是所屬課程的
     * @param integer $courseId
     * @param integer $boardId
     * @return boolean 是否為該課程的討論板
     **/
    function validCourseSubject($courseId, $boardId) {
        $cid = intval($courseId);
        $bid = intval($boardId);

        $exist = dbGetOne('WM_term_subject', 'COUNT(*)', "`course_id` = {$cid} AND `board_id` = {$bid}");

        return (intval($exist) > 0) ? $boardId : 0;
    }
}
