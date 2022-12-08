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
include_once(dirname(__FILE__).'/action.class.php');
include_once(PATH_LIB . 'course.php');

class TeacherCourseListAction extends baseAction
{
    var $_mysqlUsername = '';
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession, $sysRoles;

        // 參數處理
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? intval($_GET['offset']) : 0;
        $pagesize = (isset($_GET['pagesize']) && !empty($_GET['pagesize'])) ? intval($_GET['pagesize']) : 10;
        $keyword = (isset($_GET['keyword']) && !empty($_GET['keyword'])) ? trim($_GET['keyword']) : '';
        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);

        $courseHandler = new UserCourse();
        $courseHandler->_ignoreCourseImage = true;
        $roles = $sysRoles['teacher']|$sysRoles['assistant']|$sysRoles['instructor'];
        $query = $courseHandler->getCaptionQuery($keyword);
        $result = $courseHandler->getUserCourse($sysSession->username, $roles, $offset, $pagesize, $query, false);

        // make json
        $jsonObj = array(
            'code' => 0,
            'message' => 'success',
            'data' => array(
                'total_size' => $result['totalSize'],
                'list' => $result['result']
            ),
        );

        $jsonEncode = JsonUtility::encode($jsonObj);
        
        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}