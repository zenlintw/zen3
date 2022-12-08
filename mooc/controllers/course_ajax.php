<?php
/*
* 邏輯層：功能處理
* 接收中介層參數經處理後，傳回中介層
*
* @since   2014/2/17
* @author  cch
*/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/controllers/JSON.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');

if (!empty($_POST['action'])) {
    if (!preg_match('/^[a-zA-Z0-9\_]+$/', $_POST['action'])) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }
}

if (!empty($_POST['course_type'])) {
    if (!preg_match('/^[a-zA-Z0-9\_]+$/', $_POST['course_type'])) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }
}
/*
* $json->encode, $json->decode 宣告，以利後續使用
*/
if (!function_exists('json_encode')) {

    function json_encode($val)
    {
        $json = new Services_JSON();
        return $json->encode($val);
    }

    function json_decode($val)
    {
        $json = new Services_JSON();
        return $json->decode($val);
    }
}

function page($count,$page_num) {
        global $_GET;

        if (intval($page_num) == 0) {
            $page_num = 10;
        }

        //總共頁數
        $total_page =  ceil( $count/$page_num );
        //目前頁
        $now_page = (intval($_GET['page']) == 0? 1:intval($_GET['page']));
        //SQL範圍
        $p1  = ( ($now_page - 1) ) * $page_num;
        $p2  = $page_num;

        if ($total_page > $now_page) {
            $rtn ['moreUrl'] = $now_page+1;
        }else{
            $rtn ['moreUrl'] = '';
        }
        $rtn ['p1']  = $p1;
        $rtn ['p2']  = $p2;
        $rtn ['p3']  = $total_page;
        $rtn ['p4']  = $now_page;
        return $rtn;
}


switch($_POST['action']) {

    /*
    * 探索課程-取得樹狀項目課程資料
    * @param string $_POST['id']:群組編號
    *
    * @return array $arr:
    */
    case "getMyCourses":
        switch($_POST['role']) {
            case 'teach':
                $role=$sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher'];
            break;
            case 'learn':
                $role=$sysRoles['auditor']|$sysRoles['student'];
            break;
            default:
                $role=$sysRoles['auditor']|$sysRoles['student'];
        }
        
        $rsCourse = new course();
        $num = $rsCourse->getUserCoursesDetail($sysSession->username, $role, true, $_POST['query']);
        $page_data = page($num,$_POST['perpage']);
        $page_data['num']=$num;
        $stack = array();
        $course = $rsCourse->getUserCoursesDetail($sysSession->username, $role, false, $_POST['query'], $page_data);
        array_push($stack, $course);
        if (($page_data['p3'] > 1) && (!empty($page_data['moreUrl']))){
            $page_data['show'] = true;
            $page_data['page'] = $page_data['moreUrl'];
        }
        array_push($stack, $page_data);
        $msg = json_encode($stack);
        break;
        
    case "getTreeCourses":
        // $sysConn->debug=true;
        // 取報名中的課程：課程編號、課程名稱、課程狀態、老師群姓名、老師大頭照
        $rsCourseTree = new course();
        $num = $rsCourseTree->getCourseTreeFamilyNum(array(array($_POST['id'])));
        $page_data = page($num,$_POST['perpage']);
        $stack = array();
        $courseTree = $rsCourseTree->getCourseTreeFamily(array(array($_POST['id'])),$page_data);
        array_push($stack, $courseTree);
        if (($page_data['p3'] > 1) && (!empty($page_data['moreUrl']))){
        	$arr_page['show'] = true; 
            $arr_page['page'] = $page_data['moreUrl'];
            array_push($stack, $arr_page);     
        }
        $msg = json_encode($stack);
        break;

    case "getSigningCourses":
        // 取報名中的課程：課程編號、課程名稱、課程狀態、老師群姓名、老師大頭照
        $rsCourse = new course();
        $num = $rsCourse->getAllCourseNum('signing');
        $page_data = page($num,$_POST['perpage']);
        $stack = array();
        if ($_POST['id'] == 'hot') {    // 熱門課程
            $course = $rsCourse->getAllCourse('signing', $CourseIdSet, $keyword, $page_data, 'hot');
        }else{
            $course = $rsCourse->getAllCourse('signing','','',$page_data);
        }

        array_push($stack, $course);
        if (($page_data['p3'] > 1) && (!empty($page_data['moreUrl']))){
        	$arr_page['show'] = true; 
            $arr_page['page'] = $page_data['moreUrl'];
            array_push($stack, $arr_page);     
        }   
        $msg = json_encode($stack);
        break;

    case "getHistoryCourses":
        // 取不可報名的課程：課程編號、課程名稱、課程狀態、老師群姓名、老師大頭照
        $rsCourse = new course();
        $num = $rsCourse->getAllCourseNum('history');
        $page_data = page($num,$_POST['perpage']);
        $stack = array();
        $course = $rsCourse->getAllCourse('history','','',$page_data);
        array_push($stack, $course);
        if (($page_data['p3'] > 1) && (!empty($page_data['moreUrl']))){
        	$arr_page['show'] = true; 
            $arr_page['page'] = $page_data['moreUrl'];
            array_push($stack, $arr_page);     
        }  
        $msg = json_encode($stack);
        break;
    
    case "getCourseInfo":
        // 取單一門課程資訊：課程名稱、課程人數、老師群姓名、老師大頭照
        $rsCourse = new course();
        $course = $rsCourse->getCourseInfo(intval($_POST['cid']));

        $msg = json_encode($course);
        break;

    case "getSearchCourses":
        // 取搜尋的課程：課程編號、課程名稱、課程狀態、老師群姓名、老師大頭照
        // VIP#92931 搜尋只能呈現可報名的課程
        $rsCourse = new course();
        $num = $rsCourse->getAllCourseNum('signing', '', $_POST['id']);
        $page_data = page($num,$_POST['perpage']);
        $stack = array();
        $course = $rsCourse->getAllCourse('signing', '', $_POST['id'],$page_data);
        array_push($stack, $course);
        if (($page_data['p3'] > 1) && (!empty($page_data['moreUrl']))){
        	$arr_page['show'] = true; 
            $arr_page['page'] = $page_data['moreUrl'];
            array_push($stack, $arr_page);     
        }   
        $msg = json_encode($stack);
        break; 
    
    // 取課程容量資訊
    case "getCourseQuota":   
        $rsCourse = new course();
        $data = $rsCourse->getCourseQuota((String)$_POST['isUpdate']);
        
        $msg = json_encode($data);
        break; 

    /*
    * 刪除課程代表圖
    *
    * @return array $arr:
    */
    case "delCoursePic":
        $rsCourse = new course();
        $rtn = $rsCourse->delCoursePic($_POST['csid']);

        $msg = json_encode($rtn);
        break;

    /*
    * 刪除課程影片
    *
    * @return array $arr:
    */
    case "delCourseMv":
        $rsCourse = new course();
        $rtn = $rsCourse->delCourseMv($_POST['csid']);

        $msg = json_encode($rtn);
        break;

    /*
    * 有無作答紀錄
    *
    * @return array $arr:
    */
    case "getQTIResultNum":
        $rsCourse = new course();
        $rtn = $rsCourse->getQTIResultNum(htmlspecialchars($_POST['type']), htmlspecialchars($_POST['exam_ids']), htmlspecialchars($_POST['examinee']));

        $msg = json_encode($rtn);
        break;
    /**
     * 審核學員
     */
    case "reviewStudentMajor":
        // 此動作需要管理者或是該門課的老師與助教的身份
        if (!aclCheckRole($sysSession->username, $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $sysSession->school_id)){
            if (!aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $sysSession->course_id)) {
                die(json_encode('illegeal Access.'));
           }
        }
        
        // 檢查參數
        if (!(in_array($_POST['pass'], array('ok', 'deny')))) {
            die(json_encode('Parameter error.'));
        }

        require_once(sysDocumentRoot . '/lang/review.php');
        // 建立所需的傳參，以導向原wmpro5的程式
        $_POST['ticket'] = md5(sysTicketSeed . intval($_POST['did']) . 'singledoReviews' . $_COOKIE['idx']);
        $_POST['caption'] = $MSG['msg_' . $_POST['pass'] . '_title'][$sysSession->lang];
        $_POST['note'] = $MSG['msg_' . $_POST['pass'] . '_text'][$sysSession->lang];
        $_POST['ctype'] = 'html';
        $_POST['method'] = 'mail';

        ob_start();
        include(sysDocumentRoot . '/academic/review/review_actmail1.php');
        ob_end_clean();
        $msg = json_encode('FINISH');

        break;
        
    case 'addAssistant':
        // 此動作需要管理者或是該門課的老師身份
        if (!aclCheckRole($sysSession->username, $sysRoles['teacher'], $sysSession->course_id)) {
            die(json_encode('illegeal Access.'));
        }
        if (empty($_POST['username'])) {
            die(json_encode('Error Params.'));
        }
        if (!in_array($_POST['level'],array('assistant','instructor'))) {
            die(json_encode('Error Params.'));
        }
        $username = trim($_POST['username']);

        // 載入語系
        require_once(sysDocumentRoot . '/lang/teacher_settutor.php');

        // 保留帳號
        $reserved    = @file(sysDocumentRoot . '/config/reserve_username.txt');
        for($i = 0; $i < count($reserved); $i++)
            $reserved[$i] = trim(str_replace('*', '', $reserved[$i]));
        if (!in_array(sysRootAccount, $reserved))
            $reserved[] = sysRootAccount;
        if (in_array($username, $reserved)) {
            die(json_encode($MSG['system_reserved'][$sysSession->lang]));
        }

        // 是否已存在
        $current_tas = $sysConn->GetCol("select username from WM_term_major where course_id={$sysSession->course_id} and role&" . ($sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant']));
        if (in_array($username, $current_tas)) {
            die(json_encode($MSG['th_existed'][$sysSession->lang]));
        }

        // 帳號是否不存在
        $rtn = checkUsername($username);
        if ($rtn !== 2) {
            die(json_encode($MSG['title38'][$sysSession->lang]));
        }

        // 新增助教或講師
        require_once(sysDocumentRoot . '/lib/character_class.php');

        $role = $sysRoles[$_POST['level']];
        if (($role & ($sysRoles['instructor'] | $sysRoles['assistant'])) == 0) {
            die(json_encode('Error Params.'));
        }

        $a = array($sysRoles['teacher']    => "{$MSG['teacher'][$sysSession->lang]}",
               $sysRoles['instructor'] => "{$MSG['instructor'][$sysSession->lang]}",
               $sysRoles['assistant']  => "{$MSG['assistant'][$sysSession->lang]}"
        );

        $tech_obj = new WMteacher();
        $tech_obj->assign($username, $role, $sysSession->course_id);   // 呼叫指定身份 API
        $msg = $username . "\\n" . $MSG['assign_to'][$sysSession->lang] .$a[$role];
        // 設定功能編號
        dbSet('WM_session', 'cur_func=0300100400', "idx='{$_COOKIE['idx']}'");
        // 記錄到 WM_log_manager
        wmSysLog($sysSession->cur_func,$sysSession->school_id,0,'0','teacher',$_SERVER['SCRIPT_FILENAME'],$msg);

        $isSuccess = dbGetOne('WM_term_major','count(*)',sprintf("course_id=%d and username='%s' and role&%d > 0", $sysSession->course_id, $username, $sysRoles['instructor']|$sysRoles['assistant']));
        if ($isSuccess > 0) {
            die(json_encode('ok'));
        }else{
            die(json_encode('Fail'));
        }
        break;
        
    case 'removeAssistant':
        // 此動作需要管理者或是該門課的老師身份
        if (!aclCheckRole($sysSession->username, $sysRoles['teacher'], $sysSession->course_id)) {
            die(json_encode('illegeal Access.'));
        }
        if (empty($_POST['username'])) {
            die(json_encode('Error Params.'));
        }
        if (!in_array($_POST['level'],array('assistant','instructor'))) {
            die(json_encode('Error Params.'));
        }
        $username = trim($_POST['username']);

        // 是否存在
        $isExists = dbGetOne('WM_term_major','count(*)',sprintf("course_id=%d and username='%s' and role&%d > 0", $sysSession->course_id, $username, $sysRoles['instructor']|$sysRoles['assistant']));
        if ($isExists == 0) {
            die(json_encode('Error Params.'));
        }

        // 移除助教或講師
        require_once(sysDocumentRoot . '/lib/character_class.php');

        $role = $sysRoles[$_POST['level']];
        if (($role & ($sysRoles['instructor'] | $sysRoles['assistant'])) == 0) {
            die(json_encode('Error Params.'));
        }

        $a = array($sysRoles['teacher']    => "{$MSG['teacher'][$sysSession->lang]}",
               $sysRoles['instructor'] => "{$MSG['instructor'][$sysSession->lang]}",
               $sysRoles['assistant']  => "{$MSG['assistant'][$sysSession->lang]}"
        );

        $tech_obj = new WMteacher();
        $tech_obj->remove($username, $sysRoles[$role], $sysSession->course_id); // 呼叫移除身份 API
        $users[$user] = $MSG['revoke'][$sysSession->lang] . $a[$sysRoles[$role]] . $MSG['status'][$sysSession->lang];
        $msg .= $user . $MSG['revoke'][$sysSession->lang] . $a[$sysRoles[$role]] . $MSG['status'][$sysSession->lang] ."\\n";
        // 設定功能編號
        dbSet('WM_session', 'cur_func=0300100500', "idx='{$_COOKIE['idx']}'");

        // 記錄到 WM_log_manager
        wmSysLog($sysSession->cur_func,$sysSession->school_id,0,'0','teacher',$_SERVER['SCRIPT_FILENAME'],$msg);

        $isStillExists = dbGetOne('WM_term_major','count(*)',sprintf("course_id=%d and username='%s' and role&%d > 0", $sysSession->course_id, $username, $role));
        if ($isStillExists > 0) {
            die(json_encode('Fail'));
        }else{
            die(json_encode('ok'));
        }

        break;
    
    case "setReadLcmsVideoLog":
        $rsCourse = new course();
        $rtn = $rsCourse->setReadLcmsVideoLog(($_POST['msg']));

        $msg = json_encode($rtn);
        break;
    
    case "setReading":
        $rsCourse = new course();
        $rtn = $rsCourse->setReading(htmlspecialchars($_POST['type']), htmlspecialchars($_POST['period']), htmlspecialchars($_POST['ticket']), htmlspecialchars($_POST['enCid']), htmlspecialchars($_POST['bt']), htmlspecialchars($_POST['title']), htmlspecialchars($_POST['enUrl']), htmlspecialchars($_POST['actid']));

        $msg = json_encode($rtn);
        break;    
    
    default:
        $val = "無此動作";
        $msg = json_encode($val);
        break;
}

if ($msg != '') {
    echo $msg;
}