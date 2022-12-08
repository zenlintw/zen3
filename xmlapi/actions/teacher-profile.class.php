<?php
/**
 * 取得個人profile
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
 * @since       2012-12-28
 */

include_once(dirname(__FILE__).'/action.class.php');

class TeacherProfileAction extends baseAction
{
    var $username = '';

    function getUserMajorCoursesCount()
    {
        global $sysRoles;

        $rs = &dbGetCourses(
            'count(*) as majorCount',
            $this->username,
            $sysRoles['auditor']|$sysRoles['student']|$sysRoles['teacher']|$sysRoles['assistant']
        );

        if ($rs) {
           while ($row = $rs->FetchRow()) {
               $majorCount = $row['majorCount'];
           }
        }
        return $majorCount;
    }
    /**
     *
     * @return string
     */
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        global $sysConn;

        // 判斷是否為老師相關角色：課程助教、講師、班級助理、教師、導師
        $sysRoles = array('guest' =>  1, // 參觀者
            'senior'           =>     2, // 學長
            'paterfamilias'    =>     4, // 家長
            'superintendent'   =>     8, // 長官/督學
            'auditor'          =>    16, // 旁聽生
            'student'          =>    32, // 正式生
            'assistant'        =>    64, // 課程助教
            'instructor'       =>   128, // 講師
            'class_instructor' =>   256, // 班級助理
            'teacher'          =>   512, // 教師 (通常比講師多具有教材管理編修權)
            'director'         =>  1024, // 導師 (學生人員管理)
            'manager'          =>  2048, // 一般管理者
            'administrator'    =>  4096, // 超級管理者
            'root'             =>  8192, // 最高管理者 (一機只有一人)
            'all'              => 16127  // 所有之 mask
           );

        // 取得老師群的profile
        chkSchoolId('WM_user_picture');
        $sql  = 'SELECT wua.username, CONCAT(IFNULL(last_name, ""), IFNULL(first_name, "")) AS realname, gender AS sex, ';
        $sql .= 'birthday, email, office_tel AS tel, cell_phone AS mobile, wup.picture, department, office_address ';
        $sql .= 'FROM WM_user_account AS wua LEFT JOIN WM_user_picture AS wup ON wua.username=wup.username ';
        $sql .= ' WHERE wua.username in (select wtm.username from WM_term_major as wtm';
        $sql .= ' WHERE (wtm.role & ' . $sysRoles['assistant'] . ' or wtm.role & ' . $sysRoles['instructor'];
        $sql .= ' or wtm.role & ' . $sysRoles['class_instructor'] . ' or wtm.role & ' . $sysRoles['teacher'];
        $sql .= ' or wtm.role & ' . $sysRoles['director'] . '))';

        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

        $rows = $sysConn->Execute($sql);
        $row = $rows->FetchRow();

        $data = array();

        if ($rows) {
            while ($row = $rows->FetchRow()) {
                //convert picture to base64
                if (isset($row['picture']) && !empty($row['picture'])) {
                    $row['picture'] = 'data:image/jpeg;base64,'.base64_encode($row['picture']);
                }

                $data[] = array(
                    'username' => $row['username'],
                    'realname' => $row['realname'],
                    'sex' => $row['sex'],
                    'birthday' => $row['birthday'],
                    'email' => $row['email'],
                    'tel' => $row['tel'],
                    'mobile' => $row['mobile'],
                    'major_cnt' => $this->getUserMajorCoursesCount(),
                    'picture' => $row['picture'],
                    'department' => $row['department'],
                    'office_address' => $row['office_address']
                 );
            }
        }

        // make json
        $jsonObj = array(
            'code' => 0,
            'message' => 'success',
            'data' => $data,
        );

        $jsonEncode = JsonUtility::encode($jsonObj);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}