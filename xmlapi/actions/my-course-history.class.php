<?php
/**
 * 列出使用者的課程列表
 *
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @category    xmlapi
 * @package     WM25
 * @subpackage  WebServices
 * @author      Jeff Wang <jeff@sun.net.tw>
 * @copyright   2011 SunNet Tech. INC.
 * @version     1.0
 * @since       2012-12-19
 */
include_once(dirname(__FILE__) . '/action.class.php');

class MyCourseHistoryAction extends baseAction
{
    var $_username = null;

    function getUserReadHours($user, $cid)
    {
        global $sysConn;
        $sql  = 'SELECT SUM( UNIX_TIMESTAMP( over_time ) - UNIX_TIMESTAMP( begin_time )+1) AS cc ';
        $sql .= '    FROM `WM_record_reading` ';
        $sql .= '    WHERE course_id=%d AND username = \'%s\'';
        $rs = $sysConn->Execute(sprintf($sql, $cid, mysql_real_escape_string($user)));

        $result = $rs->FetchRow();
        if (isset($result['cc'])) {
            return intval($result['cc']);
        }
        return 0;
    }

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;
        
        $this->_username = mysql_real_escape_string($sysSession->username);
        
        // 取得使用者的課程列表
        global $sysConn;
        $sqls  = 'select M.*, C.caption, C.teacher, C.st_begin, C.st_end from WM_term_major AS M ';
        $sqls .= 'inner join WM_term_course AS C on C.course_id=M.course_id ';
        $sqls .= 'where M.username="' . $this->_username . '" AND C.kind="course" ORDER BY course_id DESC';

        chkSchoolId('WM_term_major');

        $rs = $sysConn->Execute($sqls);
        if ($rs) {
            while ($row = $rs->FetchRow()) {
                
                $cp = getCaption($row['caption']);

                $data[] = array(
                    'course_id' => intval($row['course_id']),
                    'title' => $cp['Big5'],
                    'teacher' => $row['teacher'],
                    'img_url' => '',
                    'update_datetime' => str_replace('-', '/', $row['last_login']),
                    'class_count' => intval($row['login_times']),
                    'read_hours' => intval($this->getUserReadHours($this->_username, $row['course_id'])),
                    'post_count' => intval($row['post_times']),
                    'discuss_count' => intval($row['dsc_times']),
                    'period' => str_replace(
                        '-', 
                        '/', 
                        sprintf('%s%s', ((!empty($row['st_begin']))?$row['st_begin'].' ~ ':''), $row['st_end'])
                    )
                );
            }
        }
        
        
        // make json
        $jsonObj = array(
            'code' => 0,
            'message' => 'success',
            'data' => array(
                'list' => $data,
            ),
        );

        $jsonEncode = JsonUtility::encode($jsonObj);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}