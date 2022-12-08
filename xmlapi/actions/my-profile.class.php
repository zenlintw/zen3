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

class MyProfileAction extends baseAction
{
    var $username = '';
    
    function getUserMajorCoursesCount()
    {
        global $sysConn, $sysRoles;

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
        
        global $sysConn, $sysSession;

        $this->username = mysql_real_escape_string($sysSession->username);
        
        // 取得使用者的profile
        chkSchoolId('WM_user_picture');
        $sql  = 'SELECT wua.username, CONCAT(IFNULL(last_name, ""), IFNULL(first_name, "")) AS realname, gender AS sex, ';
        $sql .= '    birthday, email, office_tel AS tel, cell_phone AS mobile, wup.picture, department, office_address ';
        $sql .= 'FROM WM_user_account AS wua LEFT JOIN WM_user_picture AS wup ON wua.username=wup.username ';
        $sql .= 'WHERE wua.username=\'' . $this->username . '\'';

        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $rows = $sysConn->Execute($sql);
        $row = $rows->FetchRow();
        
        $row1 = dbGetStSr(
            'WM_sch4user',
            'login_times, last_login, total_time',
            sprintf(
                "username='%s' AND school_id='%s'", 
                $this->username,
                $sysSession->school_id
            )
        );

        //convert picture to base64
        if (isset($row['picture']) && !empty($row['picture'])) {
            $row['picture'] = 'data:image/jpeg;base64,'.base64_encode($row['picture']);
        }
        
        // make json
        $jsonObj = array(
            'code' => 0,
            'message' => 'success',
            'data' => array(
                'username' => $row['username'],
                'realname' => $row['realname'],
                'sex' => $row['sex'],
                'birthday' => $row['birthday'],
                'email' => $row['email'],
                'tel' => $row['tel'],
                'mobile' => $row['mobile'],
                'major_cnt' => $this->getUserMajorCoursesCount(),
                'login_times' => $row1['login_times'],
                'last_login' => $row1['last_login'],
                'total_time' => $row1['total_time'],
                'picture' => $row['picture'],
                'department' => $row['department'],
                'office_address' => $row['office_address']
            ),
        );
        
        $jsonEncode = JsonUtility::encode($jsonObj);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}