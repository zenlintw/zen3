<?php

/** 
 * 提共統計相關的函數
 *
 * 建立日期:2015/05/01
 * @author Sean
 * 
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once(sysDocumentRoot . '/lang/mooc.php');
require_once(sysDocumentRoot . '/lib/course.php');

class Statistics {

    function getAllCourseInfo_Stat($p = '', $pos=''){
        global $sysRoles, $sysSession, $MSG;

        $subWhere="";

        // search by course name.
        if($p['courseName'] != ''){
            $subWhere .= sprintf(" AND `caption` LIKE '%s'",
                    '%'.$p['courseName'].'%'
                );
        }

        // 課程結束區間
        if($p['switch_st_during'] != '') {
            if($p['st_begin']!='' || $p['st_end'] ){
                $subWhere .= sprintf(" AND `st_end` BETWEEN '%s' AND '%s'", 
                        mysql_real_escape_string($p['st_begin']),
                        mysql_real_escape_string($p['st_end'])
                    );
            }
        }

        // 課程 開課 結束 所有課程 條件搜雲
        if($p['course_stat'] != ''){
            switch ($p['course_stat']) {
                case '1':
                    // 只顯示目前開課課程
                    $subWhere .= sprintf(" AND `st_end` >= CURDATE()");
                    break;
                case '2':
                    // 只顯示已結束課程
                    $subWhere .= sprintf(" AND `st_end` < CURDATE()");
                    break;
                case '3':
                    // 顯示所有課程
                    $subWhere .= " ";
                    break;

                default:
                    # code...
                    break;
            }
        } else {
            // 預設為只顯示目前開課課程
            $subWhere .= sprintf(" AND `st_end` >= CURDATE()");
        }

        $tb='WM_term_course';
        $cols='`course_id`,`caption`';
        $where='`kind` = "course" AND `status` in (1,2,3,4) ' .
            $subWhere .
            'ORDER BY `course_id` DESC, `st_begin` DESC';

        // 每頁筆數
        if($pos['select_page']!=''){
            $where .= " LIMIT ".($pos['start_row']).",".$pos['per_page'];
        }

        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

        if($rs) {
            while (!$rs->EOF) {
                $multiCaption = getCaption($rs->fields['caption']);
                $caption = $multiCaption[$sysSession->lang];
                $cid = checkCourseID($rs->fields['course_id']);
                $rs2 = $this->courseStudentNum($cid);
                $rs3 = $this->getCoursePassCnt($cid);
                $rs4 = $this->passPercent($cid);
                $rs5 = $this->calAllStdFinishNum($cid);
                $rs6 = $this->finishPercent($cid);
                $rs7 = $this->calAllStdPassNum($cid);

                $result[sprintf("'%d'",$cid)] = array(
                        'cid' => $cid, 
                        'caption' => $caption,
                        'studentCnt' => $rs2->fields['Number'],
                        'passCount' => $rs7,
                        'passPercent' => $rs4,
                        'finishCount' => $rs5,
                        'finishPercent' => $rs6
                    );

                $rs->MoveNext();
            }

        }

        return $result;

    }

    function getAllCourseInfo_Stat_count($p = ''){

        global $sysRoles, $sysSession, $MSG;
        global $sysConn;
    
        // search by course name.
        if($p['courseName'] != ''){
            $subWhere .= sprintf(" AND `caption` LIKE '%s'",
                    '%'.$p['courseName'].'%'
                );
        }

        // 課程結束區間
        if($p['switch_st_during'] != '') {
            if($p['st_begin']!='' || $p['st_end'] ){
                $subWhere .= sprintf(" AND `st_end` BETWEEN '%s' AND '%s'", 
                        mysql_real_escape_string($p['st_begin']),
                        mysql_real_escape_string($p['st_end'])
                    );
            }
        }

        // 課程 開課 結束 所有課程 條件搜雲
        if($p['course_stat'] != ''){
            switch ($p['course_stat']) {
                case '1':
                    // 只顯示目前開課課程
                    $subWhere .= sprintf(" AND `st_end` >= CURDATE()");
                    break;
                case '2':
                    // 只顯示已結束課程
                    $subWhere .= sprintf(" AND `st_end` < CURDATE()");
                    break;
                case '3':
                    // 顯示所有課程
                    $subWhere .= " ";
                    break;

                default:
                    # code...
                    break;
            }
        } else {
            // 預設為只顯示目前開課課程
            $subWhere .= sprintf(" AND `st_end` >= CURDATE()");
        }

        $tb='WM_term_course';
        $cols='COUNT(`course_id`) AS COUNT';
        $where='`kind` = "course" AND `status` in (1,2,3,4) ' .
            $subWhere .
            'ORDER BY `course_id` DESC, `en_begin` DESC';

        $result = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

        return $result;

    }


    function passPercent($cid){
        $rs = $this->courseStudentNum($cid);
        $rs2 = $this->calAllStdPassNum($cid);
        if($rs->fields['Number'] != 0){
            $percent = ($rs2 / $rs->fields['Number']);
        } else {
            $percent = 0;
        }
        $result = sprintf("%.2f%%", $percent * 100);

        return $result;

    }

    function finishPercent($cid){
        $rs = $this->courseStudentNum($cid);
        $rs2 = $this->calAllStdFinishNum($cid);
        if($rs->fields['Number'] != 0){
            $percent = ($rs2 / $rs->fields['Number']);
        } else {
            $percent = 0;
        }
        
        $result = sprintf("%.2f%%", $percent * 100);

        return $result;
    }

    // 修課學生數量
    function courseStudentNum($cid){

        global $sysRoles, $sysSession, $MSG;

        $rs = dbGetStMr('WM_term_major', 
                    'count(*) as Number', 
                    sprintf('`course_id` = %d and role&%d', $cid, ($sysRoles['student']|$sysRoles['auditor'])), 
                    ADODB_FETCH_ASSOC);

        return $rs;

    }

    // 課程老師數量
    function courseTeacherNum($cid){
        global $sysRoles, $sysSession, $MSG;

        $rs = dbGetStMr('WM_term_major', 
                    'count(*) as Number', 
                    sprintf('`course_id` = %d and role&%d', $cid,  ($sysRoles['teacher'])), 
                    ADODB_FETCH_ASSOC);

        return $rs;

    }

    // 課程助教數量
    function courseAsistNum($cid){

        global $sysRoles, $sysSession, $MSG;

        $rs = dbGetStMr('WM_term_major', 
                    'count(*) as Number', 
                    sprintf('`course_id` = %d and role&%d', $cid, ($sysRoles['assistant'])), 
                    ADODB_FETCH_ASSOC);

        return $rs;

    }

    // 課程講師數量
    function courseInstrNum($cid){
        global $sysRoles, $sysSession, $MSG;

        $tb='WM_term_major';
        $cols='count(*) as Number';
        $Where=sprintf('`course_id` = %d and role&%d', $cid, ($sysRoles['instructor']) );
        $rs = dbGetStMr($tb,$cols,$Where, ADODB_FETCH_ASSOC);

        return $rs;
    }

    // 取得修課學生基本資料列表
    // WM_term_major,WM_user_account
    // TODO
    function courseStudentInfoList($cid, $p='', $pos=''){
        global $sysRoles, $sysSession, $MSG, $sysConn;
        
        // 其他收尋條件
        $subWhere = "";

        // 以學生姓名或是學生ID做搜尋
        if(isset($p['search_opt'])){
            switch ($p['search_opt']){
                case 'srbyName':
                    if($p['std_id']!=''){
                        $subWhere = sprintf(" AND UPPER(CONCAT(b.last_name,b.first_name)) LIKE '%s' ", 
                            '%'.strtoupper(mysql_real_escape_string($p['std_id'])).'%');
                    }
                    break;
                case 'srbyId':
                    if($p['std_id']!=''){
                        $subWhere = sprintf(" AND UPPER(a.username) LIKE '%s' ", 
                            '%'.strtoupper(mysql_real_escape_string($p['std_id'])).'%' );
                    }
                    break;
                default:
            }
        }

        $tb = "WM_term_major as a RIGHT JOIN WM_user_account as b ON a.username = b.username";
        //$tb = "WM_term_major as a RIGHT JOIN WM5_TRUNK_ST_MASTER.WM_all_account as b ON a.username = b.username";
        $cols = "a.course_id, a.username, b.first_name, b.last_name, b.gender, b.birthday, b.education, b.user_status, b.country, CONCAT(b.last_name,b.first_name) AS fullname ";
        $Where = sprintf('`course_id` = %d and role&%d', $cid, ($sysRoles['student']|$sysRoles['auditor'])).$subWhere;

        // 每頁筆數
        if($pos['select_page']!=''){
            $Where .= " LIMIT ".($pos['start_row']).",".$pos['per_page'];
        }

        $rs = dbGetStMr($tb, $cols, $Where, ADODB_FETCH_ASSOC);

        if($rs){
            $i = 0;
            while(!$rs->EOF){
                $course_id =  $rs->fields['course_id'];
                $userName = $rs->fields['username'];
                $first_name = $rs->fields['first_name'];
                $last_name = $rs->fields['last_name'];
                $gender = $rs->fields['gender'];
                $age = $this->calAge($rs->fields['birthday']);
                $education = $rs->fields['education'];
                $user_status = $rs->fields['user_status'];
                $country = $rs->fields['country'];
                $userFinish = $this->calStdFinishNum($cid, $userName);
                $userPass = $this->calStdPassNum($cid, $userName);

                $result[$i] = array(
                        'cid' => $course_id,
                        'userName' => $userName,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'gender' => $gender,
                        'age' => $age,
                        'education' => $education,
                        'user_status' => $user_status,
                        'country' => $country,
                        'userFinish' => $userFinish,
                        'userPass' => $userPass
                    );

                $i++;
                $rs->MoveNext();
            }
        }

        return $result;

    }

    function courseStudentInfoList_count($cid, $p=''){
        global $sysRoles, $sysSession, $MSG, $sysConn;

        // 其他收尋條件
        $subWhere = "";

        // 以學生姓名或是學生ID做搜尋
        if(isset($p['search_opt'])){
            switch ($p['search_opt']){
                case 'srbyName':
                    if($p['std_id']!=''){
                        $subWhere = sprintf(" AND UPPER(CONCAT(b.last_name,b.first_name)) LIKE '%s' ", 
                            '%'.strtoupper(mysql_real_escape_string($p['std_id'])).'%');
                    }
                    break;
                case 'srbyId':
                    if($p['std_id']!=''){
                        $subWhere = sprintf(" AND UPPER(a.username) LIKE '%s' ", 
                            '%'.strtoupper(mysql_real_escape_string($p['std_id'])).'%' );
                    }
                    break;
                default:
            }
        }


        $tb = "WM_term_major as a RIGHT JOIN WM_user_account as b ON a.username = b.username";
        //$tb = "WM_term_major as a RIGHT JOIN WM5_TRUNK_ST_MASTER.WM_all_account as b ON a.username = b.username";
        $cols = "a.course_id, a.username, b.first_name, b.last_name, b.gender, b.birthday, b.education, b.user_status, b.country, CONCAT(b.last_name,b.first_name) AS fullname ";
        $Where = sprintf('`course_id` = %d and role&%d', $cid, ($sysRoles['student']|$sysRoles['auditor'])).$subWhere;

        $result = dbGetStMr($tb, $cols, $Where, ADODB_FETCH_ASSOC);

        return $result;

    }

    // 依西元年計算生日
    function calAge($Time) { 
      $UTime = strtotime($Time); 
      $age = date('Y') - date('Y',$UTime); 
        if(date('m') - date('m',$UTime) < 0){ // 月相減為 負值 沒超過生日 
          $age = $age - 1; 
        } else if(date('m') == date('m',$UTime) && date('d') - date('d',$UTime) < 0){ // 同月並且日相減為 負值 沒超過生日 
          $age = $age - 1; 
       } 
        if($age < 0){ // 判斷年齡為負值,表示為今年出生為零歲,修正負值為0 
          $age = 0; 
        } 
      return $age; 
    } 
     

    // 取得課程目標的開關設定值
    function getCourseGoalSwitch($cid){

        $tb = "WM_term_course";
        $cols = "`course_id`, `is_use`";
        $where = "`course_id` = '".$cid."' ".
            $subWhere.
            "ORDER BY `course_id` ASC";

        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);
        $is_use = getCaption($rs->fields['is_use']);

        foreach ($is_use as $key => $value) {
            $result[sprintf("'%d'", $cid)][$value] = array(
                'enable' => 1
                );
        }

        return $result;
    }


    // 取得完成條件門檻數值
    function getCourseFinishCdtAll($cid){
        $subWhere = "WM_term_course";
        $tb = "WM_term_course";
        $cols = "`course_id`, `gallery_pass`";
        $where = "`course_id` = '".$cid."' ";

        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

        if($rs){
            while(!$rs->EOF){
                $course_id = $rs->fields['course_id'];
                $gallery = getCaption($rs->fields['gallery_pass']);

                $result[sprintf("'%d'", $course_id)] = array('gallery' => $gallery);

                $rs->MoveNext();
            }
        }

        return $result;
        
    }

    // 取得通過條件門檻數值
    function getCoursePassCdtAll($cid){

        $subWhere = "";
        $tb = "WM_term_course";
        $cols = "`course_id`, `formal_pass`, `fair_grade`";
        $where = "`course_id` = '".$cid."' ";

        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

        if($rs){
            while (!$rs->EOF) {
                $course_id = $rs->fields['course_id'];
                $fair_grade = $rs->fields['fair_grade'];
                $formal_pass = getCaption($rs->fields['formal_pass']);

                $result[sprintf("'%d'", $course_id)] = array(
                        'fair_grade' => $fair_grade, 
                        'formal_pass' => $formal_pass
                    );

                $rs->MoveNext();
            }
        }

        return $result;
    }

    // 取得通過條件門檻數值(只有成績)
    function getCoursePassCdt($cid){
        
        $tb = "WM_term_course";
        $cols = "`course_id`, `fair_grade`";
        $where = "`course_id` = '".$cid."' ".
            $subWhere.
            "ORDER BY `course_id` ASC";

        $rs = dbGetStMr($tb, $cols, $where, ADOBE_FETCH_ASSOC);

        if($rs){
            while(!$rs->EOF){
                $course_id = $rs->fields['course_id'];
                $fair_grade = $rs->fields['fair_grade'];
                $result[sprintf("'%d'", $course_id)] =  array('fair_grade' => $fair_grade);
                $rs->MoveNext();
            }
        }

        return $result;

    }

    // 取得課程通過條件的總人數
    function getCoursePassCnt($cid){

        $tb = "WM_grade_stat";
        $cols = "`course_id`, `username`, `average`";
        $where = "`course_id` = '".$cid."' " . 
            $subWhere . 
            "ORDER BY `course_id` ASC";

        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);
        
        if($rs){
            $i = 0;
            $passCount = 0;
            while (!$rs->EOF) {
                $course_id = $rs->fields['course_id'];
                $username = $rs->fields['username'];
                $average = $rs->fields['average'];

                $result[sprintf("'%d'", $course_id)][$i] =  array( 
                        'username' => $username,
                        'average' => $average
                    );

                $rs2 = $this->getCoursePassCdt($cid);
                $temp_Mark = $result[sprintf("'%d'", $course_id)][$i]['average'];
                $temp_PassCdt = $rs2[sprintf("'%d'", $cid)]['fair_grade'];
                if($temp_Mark >= $temp_PassCdt){
                    $passCount++;
                }

                $i++;
                $rs->MoveNext();

            }
        }  

        return $passCount;
    }

    // 取得修課完成人數
    function calAllStdFinishNum($cid){
        $finish_count = 0;
        // 閱讀時間
        $rs = $this->getCourseGoalSwitch($cid);
        $cdn_studyTime = $rs[sprintf("'%d'", $cid)]['gallery_time']['enable'];

        $cdn_studyPorcess = $rs[sprintf("'%d'", $cid)]['gallery_process']['enable'];
        // 取得課程全部學生教材教度百分比
        $rs1 = $this->getStdStudyProgress($cid);
        // 取得課程全部學生閱讀時間
        $rs2 = $this->getStdStudyTime($cid);

        // 取得完成門檻數值
        $rs3 = $this->getCourseFinishCdtAll($cid);
        $finCdt_time = $rs3[sprintf("'%d'", $cid)]['gallery']['time']; // 閱讀時間 hr
        $finCdt_percent = $rs3[sprintf("'%d'", $cid)]['gallery']['percent']; //教材進度百分比
        
        for($i=0;$i<sizeof($rs1);$i++){
            $cdn1=0;
            $cdn2=0;
            // 教材進度
            if($cdn_studyPorcess == 1){
                if($rs1[$i]['studyProcessP'] >= $finCdt_percent) {
                    $cdn1 = 1;
                } else {
                    $cnd1 = 0;
                }
                
            } else {
                $cdn_studyPorcess = 0;
                $cdn1 = 0;
            }

            // 閱讀時間
            if($cdn_studyTime == 1){
                $userRss = number_format($rs2[$i]['rss'] /60 /60, 2);
                if($userRss >= $finCdt_time){
                    $cdn2 = 1;
                } else {
                    $cdn2 = 0;
                }
            } else {
                $cdn_studyTime = 0;
                $cdn2 = 0;
            }
            // 結果
            if($cdn_studyPorcess == '' && $cdn_studyTime == ''){
                $finish_count = 0;
            } else if($cdn_studyPorcess == 1 && $cdn_studyTime == ''){
                if($cdn1 == 1){
                  $finish_count++;
                } 
            } else if($cdn_studyPorcess == '' && $cdn_studyTime == 1){
                if($cdn2 == 1){
                  $finish_count++;
                } 
            } else if($cdn_studyPorcess == 1 && $cdn_studyTime == 1){
                if($cdn1&&$cdn2 == 1){
                  $finish_count++;
                } 
            } else {
            }
        }
       
        $result = $finish_count;

        return $result;
    }

    //取得修課通過人數
    function calAllStdPassNum($cid){
        $finish_count = 0;
        
        // 取得該課考試門檻有無啟用
        $rs = $this->getCourseGoalSwitch($cid);
        $cdn_studyTime = $rs[sprintf("'%d'", $cid)]['formal_time']['enable'];
        $cdn_studyPorcess = $rs[sprintf("'%d'", $cid)]['formal_process']['enable'];
        $cdn_studyMark = $rs[sprintf("'%d'", $cid)]['formal_score']['enable'];

        // 取得課程全部學生教材教度百分比
        $rs1 = $this->getStdStudyProgress($cid);
        // 取得課程全部學生閱讀時間
        $rs2 = $this->getStdStudyTime($cid);
        // 取得課程全部學生考試成績
        $rs3 = $this->getStdAllMark($cid);

        // 取得完成門檻數值
        $rs4 = $this->getCoursePassCdtAll($cid);
        // 閱讀時間 hr 門檻數值
        $passCdt_time = $rs4[sprintf("'%d'", $cid)]['formal_pass']['time']; 
        // 教材進度百分比門檻數值
        $passCdt_percent = $rs4[sprintf("'%d'", $cid)]['formal_pass']['percent']; 
        // 取得完成條件門檻數值
        $passCdt_mark = $rs4[sprintf("'%d'", $cid)]['fair_grade']; 
        
        for($i=0;$i<sizeof($rs1);$i++){
            //echo "<br>".$i."";
            $cdn1=0;
            $cdn2=0;

            // 比對教材進度
            if($cdn_studyPorcess == 1){
                if($rs1[$i]['studyProcessP'] >=  $passCdt_percent) {
                    $cdn1 = 1;
                } else {
                    $cnd1 = 0;
                }
                
            } else {
                $cdn_studyPorcess = 0;
                $cdn1 = 0;
            }
            // echo "<br>chd1 =".$cdn1;
            
            // 比對閱讀時間
            if($cdn_studyTime == 1){
                $userRss = number_format($rs2[$i]['rss'] /60 /60, 2);
                if($userRss >= $passCdt_time){
                    $cdn2 = 1;
                } else {
                    $cdn2 = 0;
                }
            } else {
                $cdn_studyTime = 0;
                $cdn2 = 0;
            }

            // 比對考試成績
            if($cdn_studyMark == 1){
                $userMark = $rs3[$i]['mark'];
                if($userMark >= $passCdt_mark){
                    $cdn3 = 1;
                } else {
                    $cdn3 = 0;
                }
            } else {
                $cdn_studyMark = 0;
                $cdn3 = 0;
            }
            // 結果
            if($cdn_studyPorcess == '' && $cdn_studyTime == '' && $cdn_studyMark == ''){
                $finish_count = 0;
            } else if($cdn_studyPorcess == '' && $cdn_studyTime == '' && $cdn_studyMark == 1){
                if($cdn3 == 1){
                  $finish_count++;
                } 
            } else if($cdn_studyPorcess == '' && $cdn_studyTime == 1 && $cdn_studyMark == ''){
                if($cdn2 == 1){
                  $finish_count++;
                } 
            } else if($cdn_studyPorcess == '' && $cdn_studyTime == 1 && $cdn_studyMark == 1){
                if($cdn2 && $cdn3 == 1){
                  $finish_count++;
                } 
            } else if($cdn_studyPorcess == 1 && $cdn_studyTime == '' && $cdn_studyMark == ''){
                if($cdn1 == 1){
                  $finish_count++;
                } 
            } else if($cdn_studyPorcess == 1 && $cdn_studyTime == '' && $cdn_studyMark == 1){
                if($cdn1 && $cdn3 == 1){
                  $finish_count++;
                } 
            } else if($cdn_studyPorcess == 1 && $cdn_studyTime == 1 && $cdn_studyMark == ''){
                if($cdn1 && $cdn2 == 1){
                  $finish_count++;
                } 
            } else if($cdn_studyPorcess == 1 && $cdn_studyTime == 1 && $cdn_studyMark == 1){
                if($cdn1 && $cdn2 && $cdn3 == 1){
                  $finish_count++;
                } 
            } else {
            }
        }
       
        $result = $finish_count;

        return $result;
    }

    // 取得個人是否修課完成
    function calStdFinishNum($cid, $username){
        $finish_count = 0;

        $rs = $this->getCourseGoalSwitch($cid);
        $cdn_studyTime = $rs[sprintf("'%d'", $cid)]['gallery_time']['enable'];
        $cdn_studyPorcess = $rs[sprintf("'%d'", $cid)]['gallery_process']['enable'];

        // 取得單一使用者成績
        $rs1_p['username'] = $username;
        $rs1_p['studyProcessP'] = $this->getStudyProgress($cid, $username);

        $rs2 = $this->getStdStudyTime($cid);
        for($i=0;$i<sizeof($rs2);$i++){
            if($rs2[$i]['username'] == $username){
                $rs2_p = $rs2[$i];
                break;
            }
        }

        $rs3 = $this->getCourseFinishCdtAll($cid);
        $finCdt_time = $rs3[sprintf("'%d'", $cid)]['gallery']['time']; // 閱讀時間 hr
        $finCdt_percent = $rs3[sprintf("'%d'", $cid)]['gallery']['percent']; //教材進度百分比

        $cdn1=0;
        $cdn2=0;
        // 教材進度
        if($cdn_studyPorcess == 1){
            if($rs1_p['studyProcessP'] >= $finCdt_percent) {
                $cdn1 = 1;
            } else {
                $cnd1 = 0;
            }
        } else {
            $cdn_studyPorcess = 0;
            $cdn1 = 0;
        }
        // 閱讀時間
        if($cdn_studyTime == 1){
            $userRss = number_format($rs2[$i]['rss'] /60 /60, 2);
            if($userRss >= $finCdt_time){
                $cdn2 = 1;
            } else {
                $cdn2 = 0;
            }
        } else {    
            $cdn_studyTime = 0;
            $cdn2 = 0;
        }

        if($cdn_studyPorcess == '' && $cdn_studyTime == ''){
            $finish_count = 0;
        } else if($cdn_studyPorcess == 1 && $cdn_studyTime == ''){
            if($cdn1 == 1){
              $finish_count++;
            } 
        } else if($cdn_studyPorcess == '' && $cdn_studyTime == 1){
            if($cdn2 == 1){
              $finish_count++;
            } 
        } else if($cdn_studyPorcess == 1 && $cdn_studyTime == 1){
            if($cdn1&&$cdn2 == 1){
              $finish_count++;
            } 
        } else {
        }
       
        $result = $finish_count;

        return $result;
    }

    //取得個人是否修課通過
    function calStdPassNum($cid, $username){
        $finish_count = 0;
        
        // 取得該課考試門檻有無啟用
        $rs = $this->getCourseGoalSwitch($cid);
        $cdn_studyTime = $rs[sprintf("'%d'", $cid)]['formal_time']['enable'];
        $cdn_studyPorcess = $rs[sprintf("'%d'", $cid)]['formal_process']['enable'];
        $cdn_studyMark = $rs[sprintf("'%d'", $cid)]['formal_score']['enable'];

        // 取得單一使用者成績
        $rs1_p['username'] = $username;
        $rs1_p['studyProcessP'] = $this->getStudyProgress($cid, $username);
 
        // 取得課程全部學生閱讀時間
        $rs2 = $this->getStdStudyTime($cid);
        for($i=0;$i<sizeof($rs2);$i++){
            if($rs2[$i]['username'] == $username){
                $rs2_p = $rs2[$i];
                break;
            }
        }

        // 取得課程全部學生考試成績
        $rs3 = $this->getStdAllMark($cid);
        for($i=0;$i<sizeof($rs3);$i++){
            if($rs3[$i]['userName'] == $username){
                $rs3_p = $rs3[$i];
                break;
            }
        }

        // 取得完成門檻數值
        $rs4 = $this->getCoursePassCdtAll($cid);
        // 閱讀時間 hr 門檻數值
        $passCdt_time = $rs4[sprintf("'%d'", $cid)]['formal_pass']['time']; 
        // 教材進度百分比門檻數值
        $passCdt_percent = $rs4[sprintf("'%d'", $cid)]['formal_pass']['percent']; 
        // 取得完成條件門檻數值
        $passCdt_mark = $rs4[sprintf("'%d'", $cid)]['fair_grade']; 

        $cdn1=0;
        $cdn2=0;
        // 比對教材進度
        if($cdn_studyPorcess == 1){
            if($rs1_p['studyProcessP'] >=  $passCdt_percent) {
                $cdn1 = 1;
            } else {
                $cnd1 = 0;
            }
            
        } else {
            $cdn_studyPorcess = 0;
            $cdn1 = 0;
        }
        
        // 比對閱讀時間
        if($cdn_studyTime == 1){
            $userRss = number_format($rs2[$i]['rss'] /60 /60, 2);
            if($userRss >= $passCdt_time){
                $cdn2 = 1;
            } else {
                $cdn2 = 0;
            }
        } else {
            $cdn_studyTime = 0;
            $cdn2 = 0;
        }

        // 比對考試成績
        if($cdn_studyMark == 1){
            $userMark = $rs3[$i]['mark'];
            if($userMark >= $passCdt_mark){
                $cdn3 = 1;
            } else {
                $cdn3 = 0;
            }
        } else {
            $cdn_studyMark = 0;
            $cdn3 = 0;
        }

        if($cdn_studyPorcess == '' && $cdn_studyTime == '' && $cdn_studyMark == ''){
            $finish_count = 0;
        } else if($cdn_studyPorcess == '' && $cdn_studyTime == '' && $cdn_studyMark == 1){
            if($cdn3 == 1){
              $finish_count++;
            } 
        } else if($cdn_studyPorcess == '' && $cdn_studyTime == 1 && $cdn_studyMark == ''){
            if($cdn2 == 1){
              $finish_count++;
            } 
        } else if($cdn_studyPorcess == '' && $cdn_studyTime == 1 && $cdn_studyMark == 1){
            if($cdn2 && $cdn3 == 1){
              $finish_count++;
            } 
        } else if($cdn_studyPorcess == 1 && $cdn_studyTime == '' && $cdn_studyMark == ''){
            if($cdn1 == 1){
              $finish_count++;
            } 
        } else if($cdn_studyPorcess == 1 && $cdn_studyTime == '' && $cdn_studyMark == 1){
            if($cdn1 && $cdn3 == 1){
              $finish_count++;
            } 
        } else if($cdn_studyPorcess == 1 && $cdn_studyTime == 1 && $cdn_studyMark == ''){
            if($cdn1 && $cdn2 == 1){
              $finish_count++;
            } 
        } else if($cdn_studyPorcess == 1 && $cdn_studyTime == 1 && $cdn_studyMark == 1){
            if($cdn1 && $cdn2 && $cdn3 == 1){
              $finish_count++;
            } 
        } else {
        }
       
        $result = $finish_count;

        return $result;
    }

    // 取得課程全部學生閱讀時間
    function getStdStudyTime($cid){
        global $sysConn,$sysSession,$sysRoles;

        $role_val = $sysRoles['student'];
        $sortby="T.username";
        $sqls = 'select T.*,U.first_name,U.last_name,U.email,sum(unix_timestamp(P.over_time)-unix_timestamp(P.begin_time)+1) as rss,' .
            'count(P.username) as page,S.login_times as Slogin_times,S.last_login as Slast_login ' .
            'from WM_term_major as T ' .
            'left join WM_user_account as U ' .
            'on T.username=U.username ' .
            'left join WM_record_reading as P ' .
            'on T.course_id=P.course_id  and T.username = P.username ' .
            'left join ' . sysDBname . '.WM_sch4user as S ' .
            'on S.school_id=' . $sysSession->school_id . ' and S.username=T.username ' .
            'where T.course_id=' . $cid . ($role == 'all' ? ' ' : (' and (T.role & ' . $role_val . ') ')) .
            'group by T.username ' .
            'order by ' . $sortby . ' ASC';

            $rs = $sysConn->Execute($sqls);

            if($rs){
                $i=0;
                while(!$rs->EOF){
                    $course_id = $rs->fields['course_id'];
                    $username = $rs->fields['username'];
                    $rss = $rs->fields['rss'];
                    if($rss == NULL){
                        $rss = 0;
                    }
                    $result[$i]= array(
                            'course_id' => $course_id,
                            'username' => $username,
                            'rss' =>  $rss,
                        );
                    $i++;
                    $rs->MoveNext();
                }
            }

            return $result;

    }


    // 取得課程全部學生教材教度百分比
    function getStdStudyProgress($cid){
        $rs = $this->courseStudentInfoList($cid);
        $rs2 = $this->getStudyProgress($cid, $username);

        if($rs){
            for($i=0;$i<sizeof($rs);$i++){
                $userName = $rs[$i]['userName'];
                $studyProcessP = $this->getStudyProgress($cid, $userName);

                $result[$i] = array(
                        'course_id' => $cid,
                        'username' => $userName, 
                        'studyProcessP' => $studyProcessP
                    );
            }
        }

        return $result;
    }



    //取得個人教材進度百分比
    public function getStudyProgress($cid, $username){
        $table = 'WM_term_path';
        $fields = 'content';
        $where = "course_id='{$cid}' ORDER by serial DESC LIMIT 1";
        list($courseXML) = dbGetStSr($table, $fields, $where, ADODB_FETCH_NUM);
        $getProgress = getProgress($cid, $courseXML, $username);
        $progress = intval($getProgress['progress']);

        return $progress;
    }

    // 取得所有學生考試成績
    function getStdAllMark($cid){

        $tb = "WM_grade_stat";
        $cols = "`course_id`, `username`, `average`";
        $where = "`course_id` = '".$cid."' " . 
            $subWhere . 
            "ORDER BY `course_id` ASC";

        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

        if($rs){
            $i=0;
            while (!$rs->EOF) {
                $course_id = $rs->fields['course_id'];
                $userName = $rs->fields['username'];
                $mark = $rs->fields['average'];
                $result[$i] = array(
                        'course_id' => $course_id, 
                        'userName' => $userName,
                        'mark' => $mark
                    );

                $i++;
                $rs->MoveNext();
            }
        }

        return $result;
    }

    // 取得個人考試成績
    function getStdMark($cid, $userName){

        $tb = "WM_grade_stat";
        $cols = "`course_id`, `username`, `average`";
        $subWhere = "";
        if($userName!=""){
            $subWhere .= "AND  `username` = '".$userName."' ";
        }
        $where = "`course_id` = '".$cid."' " . 
            $subWhere . 
            "ORDER BY `course_id` ASC";
        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

        if($rs){
            $i=0;
            while (!$rs->EOF) {
                $course_id = $rs->fields['course_id'];
                $userName = $rs->fields['username'];
                $mark = $rs->fields['average'];
                $result[$i] = array(
                        'course_id' => $course_id, 
                        'userName' => $userName,
                        'mark' => $mark
                    );

                $i++;
                $rs->MoveNext();
            }
        }

        return $result;
    }


    // Chart 需要用function
    // 性別
    // M 男, F女
    function getGenderPercent($cid){
        $rs1 = $this->courseStudentInfoList($cid);
        if($rs1){
            $count_m = 0;
            $count_f = 0;
            $count_notmark = 0;

            for($i=0;$i<sizeof($rs1);$i++){
                if($rs1[$i]['gender'] === 'N'){
                    $count_notmark = $count_notmark + 1;
                } else if($rs1[$i]['gender'] === 'M'){
                    $count_m = $count_m + 1;
                } else if($rs1[$i]['gender'] === 'F'){
                    $count_f = $count_f + 1;
                } 
            }
        }

        $sumArray=array($count_m, $count_f, $count_notmark);
        $sum = array_sum($sumArray);
        if($sum != 0){
            $count_m_p = number_format($count_m/$sum*100, 2);
            $count_f_p = number_format($count_f/$sum*100, 2);
            $count_notmark_p = number_format($count_notmark/$sum*100, 2);
        } else {
            $count_m_p = 0;
            $count_f_p = 0;
            $count_notmark_p = 0;
        }

        $result = array(
                'M' => $count_m_p, 
                'F' => $count_f_p,
                'NOT_MARKED' => $count_notmark_p
            );

        return $result;
    }

    /**
     * 年齡
     * @param  [type] $cid [description]
     * @return [type]      [description]
     *
     * 未標示 : notmark
     * 10歲以下 : a (含未滿一歲)
     * 11~15歲 : b
     * 16~20歲 : c
     * 21~25歲 : d
     * 26~30歲 : e
     * 31~35歲 : f
     * 35~40歲 : g
     * 41歲以上 : h
     * 
     */
    function getAgePercent($cid){

        $rs1 = $this->courseStudentInfoList($cid);
        if($rs1){
            $count_notmark = 0;
            $count_a = 0; // 10歲以下
            $count_b = 0; // 11~15歲
            $count_c = 0; // 16~20歲
            $count_d = 0; // 21~25歲
            $count_e = 0; // 26~30歲
            $count_f = 0; // 31~35歲
            $count_g = 0; // 35~40歲
            $count_h = 0; // 41歲以上 

            for($i=0;$i<sizeof($rs1);$i++){
                if($rs1[$i]['age'] == NULL || $rs1[$i]['age'] == '0000-00-00') {
                    $count_notmark = $count_notmark + 1;
                } else if($rs1[$i]['age'] <= 10){
                    $count_a = $count_a + 1;
                } else if(11 <= $rs1[$i]['age']  && $rs1[$i]['age'] <= 15){
                    $count_b = $count_b + 1;
                }  else if(16 <= $rs1[$i]['age'] && $rs1[$i]['age'] <= 20){
                    $count_c = $count_c + 1;
                }  else if(21 <= $rs1[$i]['age'] && $rs1[$i]['age'] <= 25){
                    $count_d = $count_d + 1;
                }  else if(26 <= $rs1[$i]['age'] && $rs1[$i]['age'] <= 30){
                    $count_e = $count_e + 1;
                }  else if(31 <= $rs1[$i]['age'] && $rs1[$i]['age'] <= 35){
                    $count_f = $count_f + 1;
                }  else if(36 <= $rs1[$i]['age'] && $rs1[$i]['age'] <= 40){
                    $count_g = $count_g + 1;
                } else if(41 <= $rs1[$i]['age']){
                    $count_h = $count_h + 1;
                } 
            }
        }

        $sumArray = array($count_a, $count_b, $count_c, $count_d, $count_e, $count_f, $count_g, $count_h, $count_notmark);
        $sum = array_sum($sumArray);
        if($sum != 0) {
            $count_a_p = number_format($count_a/$sum*100, 2);
            $count_b_p = number_format($count_b/$sum*100, 2);
            $count_c_p = number_format($count_c/$sum*100, 2);
            $count_d_p = number_format($count_d/$sum*100, 2);
            $count_e_p = number_format($count_e/$sum*100, 2);
            $count_f_p = number_format($count_f/$sum*100, 2);
            $count_g_p = number_format($count_g/$sum*100, 2);
            $count_h_p = number_format($count_h/$sum*100, 2);
            $count_notmark_p = number_format($count_notmark/$sum*100, 2);
        } else {
            $count_a_p = 0;
            $count_b_p = 0;
            $count_c_p = 0;
            $count_d_p = 0;
            $count_e_p = 0;
            $count_f_p = 0;
            $count_g_p = 0;
            $count_h_p = 0;
            $count_notmark_p = 0;
        }
        $result = array(
                'A' => $count_a_p, 
                'B' => $count_b_p, 
                'C' => $count_c_p, 
                'D' => $count_d_p, 
                'E' => $count_e_p, 
                'F' => $count_f_p, 
                'G' => $count_g_p,
                'H' => $count_h_p,
                'NOT_MARKED' => $count_notmark_p
            );

        return $result;

    }

    // 身分(學生S, 在職W)
    function getStatusPercent($cid){
        $rs1 = $this->courseStudentInfoList($cid);
        if($rs1){
            $count_s = 0;
            $count_w = 0;
            $count_notmark = 0;

            for($i=0;$i<sizeof($rs1);$i++){
                
                if($rs1[$i]['user_status'] == NULL){
                    $count_notmark = $count_notmark + 1;
                } else if($rs1[$i]['user_status'] == 'S'){
                    $count_s = $count_s + 1;
                } else if($rs1[$i]['user_status'] == 'W'){
                    $count_w = $count_w + 1;
                }
            }
        }

        $sumArray= array($count_s, $count_w, $count_notmark);
        $sum = array_sum($sumArray);
        if($sum != 0){
            $count_s_p = number_format($count_s/$sum*100, 2);
            $count_w_p = number_format($count_w/$sum*100, 2);
            $count_notmark_p = number_format($count_notmark/$sum*100, 2);
        } else {
            $count_s_p = 0;
            $count_w_p = 0;
            $count_notmark_p = 0;
        }

        $result = array('S' => $count_s_p, 
                'W' => $count_w_p,
                'NOT_MARKED' => $count_notmark_p
            );

        return $result;

    }

    // 學歷(小學P, 中學H, 高中S, 大學U, 碩士M, 博士D, 其他O)
    function getEduPercent($cid){

        $rs1 = $this->courseStudentInfoList($cid);

        if($rs1){
            $count_p = 0;
            $count_h = 0;
            $count_s = 0;
            $count_u = 0;
            $count_m = 0;
            $count_d = 0;
            $count_o = 0;
            $count_notmark = 0;

            for($i=0;$i<sizeof($rs1);$i++){
                if($rs1[$i]['education'] == NULL){
                    $count_notmark = $count_notmark + 1;
                } else if($rs1[$i]['education'] == 'P'){
                    $count_p = $count_p + 1;
                } else if($rs1[$i]['education'] == 'H'){
                    $count_h = $count_h + 1;
                }  else if($rs1[$i]['education'] == 'S'){
                    $count_s = $count_s + 1;
                }  else if($rs1[$i]['education'] == 'U'){
                    $count_u = $count_u + 1;
                }  else if($rs1[$i]['education'] == 'M'){
                    $count_m = $count_m + 1;
                }  else if($rs1[$i]['education'] == 'D'){
                    $count_d = $count_d + 1;
                }  else if($rs1[$i]['education'] == 'O'){
                    $count_o = $count_o + 1;
                } 
            }
        }

        $sumArray=array($count_p, $count_h, $count_s, $count_u, $count_m, $count_d, $count_o, $count_notmark);
        $sum = array_sum($sumArray);
        if($sum != 0){
            $count_p_p = number_format($count_p/$sum*100, 2);
            $count_h_p = number_format($count_h/$sum*100, 2);
            $count_s_p = number_format($count_s/$sum*100, 2);
            $count_u_p = number_format($count_u/$sum*100, 2);
            $count_m_p = number_format($count_m/$sum*100, 2);
            $count_d_p = number_format($count_d/$sum*100, 2);
            $count_o_p = number_format($count_o/$sum*100, 2);
            $count_notmark_p = number_format($count_notmark/$sum*100, 2);
        } else {
            $count_p_p = 0;
            $count_h_p = 0;
            $count_s_p = 0;
            $count_u_p = 0;
            $count_m_p = 0;
            $count_d_p = 0;
            $count_notmark_p = 0;
        }

        $result = array(
                'P' => $count_p_p, 
                'H' => $count_h_p, 
                'S' => $count_s_p, 
                'U' => $count_u_p, 
                'M' => $count_m_p, 
                'D' => $count_d_p, 
                'O' => $count_o_p,
                'NOT_MARKED' => $count_notmark_p
            );

        return $result;
    }

    //身分
    // function getStatusPercent($cid){
        
    // }
    
    /**
     * 來源地區(國家)
     * 
     * 台灣:TW,中國:CH,日本:JA,印度:IN,美國:US,澳洲:AS,其他:O;
     */
    function getCountryPercent($cid){
        $rs1 = $this->courseStudentInfoList($cid);
        if($rs1){
            $count_tw = 0;
            $count_ch = 0;
            $count_ja = 0;
            $count_in = 0;
            $count_us = 0;
            $count_as = 0;
            $count_o = 0;
            $count_notmark = 0;

            for($i=0;$i<sizeof($rs1);$i++){
                if($rs1[$i]['country'] == NULL){
                    $count_notmark = $count_notmark + 1;
                } else if($rs1[$i]['country'] == 'TW'){
                    $count_tw = $count_tw + 1;
                } else if($rs1[$i]['country'] == 'CH'){
                    $count_ch = $count_ch + 1;
                }  else if($rs1[$i]['country'] == 'JA'){
                    $count_ja = $count_ja + 1;
                }  else if($rs1[$i]['country'] == 'IN'){
                    $count_in = $count_in + 1;
                }  else if($rs1[$i]['country'] == 'US'){
                    $count_us = $count_us + 1;
                }  else if($rs1[$i]['country'] == 'AS'){
                    $count_as = $count_as + 1;
                }  else if($rs1[$i]['country'] == 'O'){
                    $count_o = $count_o + 1;
                } 
            }
        }

        $sumArray = array($count_tw, $count_ch, $count_ja, $count_in, $count_us, $count_as, $count_o, $count_notmark);
        $sum=array_sum($sumArray);
        if($sum != 0) {
            $count_tw_p = number_format($count_tw/$sum*100, 2);
            $count_ch_p = number_format($count_ch/$sum*100, 2);
            $count_ja_p = number_format($count_ja/$sum*100, 2);
            $count_in_p = number_format($count_in/$sum*100, 2);
            $count_us_p = number_format($count_us/$sum*100, 2);
            $count_as_p = number_format($count_as/$sum*100, 2);
            $count_o_p = number_format($count_o/$sum*100, 2);
            $count_notmark_p = number_format($count_notmark/$sum*100, 2);
        } else {
            $count_tw_p = 0;
            $count_ch_p = 0;
            $count_ja_p = 0;
            $count_in_p = 0;
            $count_us_p = 0;
            $count_as_p = 0;
            $count_o_p = 0;
            $count_notmark_p = 0;
        }

        $result = array(
                'TW' => $count_tw_p, 
                'CH' => $count_ch_p, 
                'JA' => $count_ja_p, 
                'IN' => $count_in_p, 
                'US' => $count_us_p, 
                'AS' => $count_as_p, 
                'O' => $count_o_p,
                'NOT_MARKED' => $count_notmark_p
            );

        return $result;
    }

    // 角色統計圖
    function getRolePercent($cid){

        // 學生
        $stdCount = $this->courseStudentNum($cid)->fields['Number'];

        // 老師
        $teaCount = $this->courseTeacherNum($cid)->fields['Number'];

        // 助教
        $asisCount = $this->courseAsistNum($cid)->fields['Number'];

        // 講師
        $instrCount = $this->courseInstrNum($cid)->fields['Number'];

        $sumCount = array($stdCount, $teaCount, $asisCount, $instrCount);
        $sum = array_sum($sumCount);
        if($sum != 0){
            $count_std_p = number_format($stdCount/$sum*100, 2);
            $count_tea_p = number_format($teaCount/$sum*100, 2);
            $count_asis_p = number_format($asisCount/$sum*100, 2);
            $count_instr_p = number_format($instrCount/$sum*100, 2);
        } else {
            $count_std_p = 0;
            $count_tea_p = 0;
            $count_asis_p = 0;
            $count_instr_p = 0;
        }

        $result = array('STD' => $count_std_p, 
                'TEA' => $count_tea_p,
                'ASIS' => $count_asis_p,
                'INSTR' => $count_instr_p
            );

        return $result;
    }
    // Chart funciton End

    /* 分頁模組
    *
    *  $data_count 資料量總數
    *  $pageAct 分頁動作參數
    *  $select_page 第幾頁參數
    *  $per_page 每頁分頁筆數
     */
    function pager($data_count, $pageAct, $select_page, $per_page='3'){

        global $_SESSION;
        $pos['per_page'] = $per_page;
        $pos['total_page'] = ceil($data_count/$pos['per_page']);
        $pos['pageAct'] = $_POST['pageAct'];
        $pos['select_page'] = $select_page;

         // 給預設頁值
        if($pos['select_page'] == '' || $pos['select_page'] == NULL || is_numeric((int)$pos['select_page']) == false || (int)$pos['select_page'] == 0){
            $pos['select_page']=1;
            $pos['start_row']=0;
        } else {
            $pos['select_page'] = $select_page;
        }

        // 分頁動作處理
        switch ($pos['pageAct']) {
            case 'mv_first_page':
                $pos['select_page'] = 1;
                $pos['start_row']= ($pos['select_page']-1)*$pos['per_page'];
                break;

            case 'mv_pre_page':
                $pos['select_page'] = $pos['select_page'] - 1;
                $pos['start_row']= ($pos['select_page']-1)*$pos['per_page'];
                break;

            case 'mv_next_page':
                $pos['select_page'] = $pos['select_page'] + 1;
                $pos['start_row']= ($pos['select_page']-1)*$pos['per_page'];
                break;

            case 'mv_last_page':
                $pos['select_page'] = $pos['total_page'];
                $pos['start_row']= ($pos['select_page']-1)*$pos['per_page'];
                break;

            case 'mv_type_page':
                $pos['select_page'] = $pos['select_page'];
                $pos['start_row']= ($pos['select_page']-1)*$pos['per_page'];
                break;

            default:
                // 當使用者輸入的值大於原本的總頁數的處理
                if($pos['select_page']<0){
                    $pos['select_page'] = '1';
                } else if($pos['select_page'] > $pos['total_page']){
                    $pos['select_page'] = $pos['total_page'];
                } else {
                    $pos['select_page'] = $pos['select_page'];
                }
                $pos['start_row']= ($pos['select_page']-1)*$pos['per_page'];
                break;
        }
        
        return $pos;
    }

}

