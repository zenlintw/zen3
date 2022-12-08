<?php
/**
 * 群組作業公用 function
 *
 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @package     WM3
 * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
 * @copyright   2000-2006 SunNet Tech. INC.
 * @version     CVS: $Id: group_assignment_lib.php,v 1.1 2010/02/24 02:39:33 saly Exp $
 * @link        http://demo.learn.com.tw/1000110138/index.html
 * @since       2006-08-02
 */

/**
 * 判斷某作業是否是指派給群組的
 *
 * @param   int     $exam_id        作業 ID
 * @param   int     $course_id      課程 ID
 * @return  bool                    true=是；false=否
 */
function isAssignmentForGroup($exam_id, $course_id = null, $test_type = 'homework')
{
    global $sysSession;
    static $examsOfAssignmentForGroup;
    
    if (is_null($course_id))
        $course_id = $sysSession->course_id;
    
    if (!is_array($examsOfAssignmentForGroup) || !isset($examsOfAssignmentForGroup[$course_id])) {
        $examsOfAssignmentForGroup[$course_id] = getAssignmentsForGroup($course_id, $test_type);
    }
    
    return (is_array($examsOfAssignmentForGroup[$course_id]) && is_array($examsOfAssignmentForGroup[$course_id][$exam_id]) && count($examsOfAssignmentForGroup[$course_id][$exam_id]) > 0);
}

/**
 * 取得某課程中，指派給群組的作業
 *
 * @param   int     $course_id      課程 ID
 * @return  array(3)                array($exam_id => array($team_id => array($group_id, $group_id, ...),
 *                                                          $team_id => array($group_id, $group_id, ...),
 *                                                          :
 * 														   ),
 *                                        :
 * 										 );
 */
function getAssignmentsForGroup($course_id = null, $test_type = 'homework')
{
    global $sysConn, $ADODB_FETCH_MODE, $sysSession;
    
    switch ($test_type) {
        case 'peer':
            $test_type = 'peer';
            break;
        
        case 'homework':
        default:
            $test_type = 'homework';
            break;
    }
    if (is_null($course_id))
        $course_id = $sysSession->course_id;
    
    // 功能編號
    $examinee_perm = array(
        'homework' => 1700400200,
        'peer' => 1710400200,
    );
    
    $sqls = 'select distinct T.exam_id, SUBSTRING(M.member, 2) ' . 
            'from WM_qti_' . $test_type . '_test as T ' . 
            'left join WM_acl_list as L ' . 
            'on T.course_id=L.unit_id and T.exam_id=L.instance ' .
            'left join WM_acl_member as M ' . 
            'on L.acl_id=M.acl_id ' . 
            'where T.course_id=' . $course_id . 
            ' and L.function_id = \'' . $examinee_perm[$test_type] . '\'' .
            ' and M.member like "@%"';
    chkSchoolId('WM_qti_' . $test_type . '_test');
    $keep             = $ADODB_FETCH_MODE;
    $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
    $rs               = $sysConn->Execute($sqls);
    $ADODB_FETCH_MODE = $keep;
    $rets             = array();
    if ($rs)
        while ($fields = $rs->FetchRow()) {
            list($team_id, $group_id) = explode('.', $fields[1], 2);
            $rets[$fields[0]][$team_id][] = $group_id;
        }
    
    return $rets;
}

/**
 * 判斷某群組作業是否已繳交
 *
 * @param   int     $exam_id        作業 ID
 * @param   string  $username       如果有帳號，則判斷此帳號所在之組是否交了，如果沒有，則判斷這作業有沒有人交了
 * @param   int     $course_id      課程 ID
 * @return  bool                    true=是；false=否
 */
function isAlreadySubmittedAssignmentForGroup($exam_id, $username = null, $course_id = null, $test_type = 'homework')
{
    global $sysSession;
    static $alreadySubmittedAssignmentForGroup;
    if (is_null($course_id))
        $course_id = $sysSession->course_id;
    
    //if (!is_array($alreadySubmittedAssignmentForGroup) || !isset($alreadySubmittedAssignmentForGroup[$course_id])) {
        $alreadySubmittedAssignmentForGroup[$course_id] = getAlreadySubmittedAssignmentForGroup($course_id, $test_type);
    //}
    
    if (is_null($username))
        return (is_array($alreadySubmittedAssignmentForGroup[$course_id][$exam_id]) && count($alreadySubmittedAssignmentForGroup[$course_id][$exam_id]) > 0);
    elseif (is_array($alreadySubmittedAssignmentForGroup[$course_id][$exam_id])) {
        $my_groups = getMyGroups($username, $course_id);
        if (is_array($my_groups))
            foreach ($alreadySubmittedAssignmentForGroup[$course_id][$exam_id] as $t => $g) {
                if (isset($my_groups[$t]) && in_array($my_groups[$t], $g))
                    return true;
            }
    }
    
    return false;
}

/**
 * 取得某課程中，已有繳交的群組作業
 *
 * @param   int     $course_id      課程 ID
 * @return  array(3)                array($exam_id => array($team_id => array($group_id, $group_id, ...),
 *                                                          $team_id => array($group_id, $group_id, ...),
 *                                                          :
 * 														   ),
 *                                        :
 * 										 );
 */
function getAlreadySubmittedAssignmentForGroup($course_id = null, $test_type = 'homework')
{
    global $sysConn, $ADODB_FETCH_MODE, $sysSession;
    if (is_null($course_id))
        $course_id = $sysSession->course_id;
    
    $sqls = 'SELECT distinct T.exam_id, G.team_id, G.group_id ' .
            'FROM WM_qti_' . $test_type . '_test AS T ' . 
            'LEFT JOIN WM_acl_list AS L ON T.course_id = L.unit_id ' .
            'AND T.exam_id = L.instance ' . 
            'LEFT JOIN WM_acl_member AS M ON L.acl_id = M.acl_id ' .
            'LEFT JOIN WM_student_group G ON T.course_id = G.course_id ' .
            'AND CONCAT( "@", G.team_id, ".", G.group_id ) = M.member ' .
            'LEFT JOIN WM_student_div AS D ON G.course_id = D.course_id ' .
            'AND G.group_id = D.group_id ' . 'AND G.team_id = D.team_id ' .
            'LEFT JOIN WM_qti_' . $test_type . '_result AS R ON R.exam_id = T.exam_id ' .
            'AND D.username = R.examinee ' . 'WHERE T.course_id =' . $course_id .
            ' AND M.member LIKE "@%" ' . 'AND G.course_id =' . $course_id .
            ' AND D.course_id =' . $course_id . 
            ' AND R.examinee IS NOT NULL ' .
            'AND R.status != "break"';
    chkSchoolId('WM_qti_' . $test_type . '_test');
    $keep             = $ADODB_FETCH_MODE;
    $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
    $rs               = $sysConn->Execute($sqls);
    $ADODB_FETCH_MODE = $keep;
    $rets             = array();
    if ($rs)
        while ($fields = $rs->FetchRow())
            $rets[$fields[0]][$fields[1]][] = $fields[2];
    
    return $rets;
}

/**
 * 取得我(某人)所在的群組，所繳交的作業記錄
 *
 * @param   int     $exam_id        作業 ID
 * @param   string      $username   帳號
 * @param   int     $course_id      課程 ID
 * @return  hash_array              繳交記錄
 */
function getRecordOfAssignmentForGroup($exam_id, $username, $course_id = null, $test_type = 'homework')
{
    global $sysConn, $ADODB_FETCH_MODE, $sysSession;
    static $alreadySubmittedAssignmentForGroup;
    
    if (is_null($course_id))
        $course_id = $sysSession->course_id;
    
    if (!is_array($alreadySubmittedAssignmentForGroup) || !isset($alreadySubmittedAssignmentForGroup[$course_id])) {
        $alreadySubmittedAssignmentForGroup[$course_id] = getAlreadySubmittedAssignmentForGroup($course_id, $test_type);
    }
    
    if (is_array($alreadySubmittedAssignmentForGroup[$course_id][$exam_id])) {
        $my_groups = getMyGroups($username, $course_id);
        if (is_array($my_groups))
            foreach ($alreadySubmittedAssignmentForGroup[$course_id][$exam_id] as $t => $g) {
                if (isset($my_groups[$t]) && in_array($my_groups[$t], $g)) {
                    $sqls = 'SELECT D.team_id, D.group_id, R.* ' .
                            'FROM WM_student_div AS D ' . 
                            'INNER JOIN WM_qti_' . $test_type . '_result AS R ON D.username = R.examinee ' . 
                            'WHERE D.course_id =' . $course_id . 
                            ' AND D.group_id =' . $my_groups[$t] .
                            ' AND D.team_id =' . $t . 
                            ' AND R.exam_id =' . $exam_id;
                    chkSchoolId('WM_student_div');
                    $keep             = $ADODB_FETCH_MODE;
                    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
                    $rets             = $sysConn->GetRow($sqls);
                    $ADODB_FETCH_MODE = $keep;
                    
                    return $rets;
                }
            }
    }
    
    return false;
}

/**
 * 取某人在某課所分配到的組別
 *
 * @param   string      $username       帳號
 * @param   int         $course_id      課程
 * @return  array(2)                    傳回 array($team_id => $group_id, ...) 的二維陣列
 */
function getMyGroups($username, $course_id)
{
    global $sysConn;
    
    $sqls = 'SELECT G.team_id, G.group_id ' .
            'FROM WM_student_group AS G ' .
            'LEFT JOIN WM_student_div AS D ON G.course_id = D.course_id ' .
            'AND G.group_id = D.group_id ' . 'AND G.team_id = D.team_id ' . 
            'WHERE G.course_id =' . $course_id . 
            ' AND D.course_id =' . $course_id . 
            ' AND D.username = "' . $username . 
            '" order by G.team_id, G.group_id';
    chkSchoolId('WM_student_group');
    return $sysConn->GetAssoc($sqls);
}

/**
 * 取得我 (某人) 的同組組員
 *
 * @param 	int		$team_id        分組次
 * @param   string  $username       帳號
 * @param   int		$course_id      課程ID
 * @return  array                   組員帳號陣列
 */
function getMyGroupMates($team_id, $username = null, $course_id = null)
{
    global $sysConn, $sysSession;
    
    if (is_null($username))
        $username = $sysSession->username;
    if (is_null($course_id))
        $course_id = $sysSession->course_id;
    
    $sqls = 'SELECT D2.username ' .
            'FROM WM_student_div AS D1 ' .
            'LEFT JOIN WM_student_div AS D2 ON D1.course_id = D2.course_id ' . 
            'AND D1.group_id = D2.group_id ' . 'AND D1.team_id = D2.team_id ' . 
            'WHERE D1.course_id =' . $course_id . 
            ' AND D1.team_id =' . $team_id . 
            ' AND D1.username = "' . $username . 
            '" AND D2.course_id =' . $course_id .
            ' AND D2.team_id =' . $team_id;
    chkSchoolId('WM_student_div');
    return $sysConn->GetCol($sqls);
}

/**
 * 這們課有沒有分組
 *
 * @param   int		$course_id      課程ID
 * @return  bool                    true=有；false=沒有
 */
function hasGrouped($course_id = false)
{
    global $sysConn, $sysSession;
    
    if (!$course_id)
        $course_id = $sysSession->course_id;
    
    chkSchoolId('WM_student_group');
    return (bool) $sysConn->GetOne('select count(*) from WM_student_group where course_id=' . $course_id);
}