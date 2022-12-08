<?php
/**
 * 提供與課程相關的函數
 *
 * 建立日期：2014/2/11
 * @author cch
 *
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once(sysDocumentRoot . '/lib/course.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/quota.php'); // 計算教師使用容量
require_once(sysDocumentRoot . '/lang/mooc.php');
require_once(sysDocumentRoot . '/lang/sysbar.php');
require_once(sysDocumentRoot . '/lib/file_api.php');

class course
{
    var $courseGroup = array();
    var $groupId = array();
    var $groupName = array();
    var $htmlCourseGroup = '';
    var $i = 1;
    var $sons = array();

    var $useCourseTable = '';
    var $courseColumnPlus = '';
    var $courseColumnPlus2 = '';
    var $courseWhere = '';
    var $useMajorTable = '';
    var $majorWhere = '';
    var $useGroupTable = '';

    public function __construct()
    {
        global $sysSession;
        // 依據入口網校與(內容商、獨立校)決定讀取資料使用的 TABLE
        $this->useCourseTable = (is_portal_school) ? sysDBprefix.'MASTER.`CO_all_course`' : '`WM_term_course`';
        $this->useMajorTable = (is_portal_school) ? sysDBprefix.'MASTER.`CO_all_major`' : '`WM_term_major`';
        $this->useGroupTable = (is_portal_school) ? sysDBprefix.'MASTER.`CO_all_group`' : '`WM_term_group`';
        $this->courseColumnPlus = (is_portal_school) ? ', `school` AS `school_id` ' : ', '.$sysSession->school_id.' AS `school_id`';
        $this->courseColumnPlus2 = (is_portal_school) ? ', CS.`school` AS `school_id` ' : ', '.$sysSession->school_id.' AS `school_id`';
        $this->courseWhere = (is_portal_school) ? '`school`' : $sysSession->school_id;
        $this->majorWhere = (is_portal_school) ? 'AND CS.`school` = MJ.`school` ' : '';
    }
    
    /**
     *取所有課程筆數
    */
    function getAllCourseNum($type = '', $tmpCourseId = array(), $keywords = '')
    {
        $subWhere = '';
        global $sysConn, $sysSession, $MSG;
        //$sysConn->debug=true;
        switch ($type) {
            // 取可報名課程
            case 'signing':
                $subWhere .=
                    'and ('.
                    '((status = 1 or status = 3) and ( Isnull(en_begin) or en_begin <= curdate()) and (Isnull(en_end) or en_end >= curdate())) '.
                    'or '.
                    '((status = 2 or status = 4) and ( Isnull(en_begin) or en_begin <= curdate()) and (Isnull(en_end) or en_end >= curdate()) and (Isnull(st_end) or st_end >= curdate())) '.
                    ') ';
                break;

            // 歷史課程
            case 'history':
                $subWhere .= 'and (`st_end` is not null and curdate() > `st_end`) ';
                break;
        }

        // 若有指定課程編號
        if (is_array($tmpCourseId) && count($tmpCourseId) >= 1) {
//            $courseId = implode(',', $tmpCourseId);
//            $subWhere .= 'and `course_id` in (' . $courseId . ') ';
            $subWhere .= ' AND ( 0 ';
            foreach($tmpCourseId as $k => $v) {
                $courseId = implode(',', $v);
                $subWhere .= 'OR ('.$this->courseWhere.' = '.$k.' and `course_id` in (' . $courseId . ')) ';
            }
            $subWhere .= ') ';
        }

        if ($keywords !== '') {
            $subWhere .= sprintf('and (`caption` LIKE "%%%s%%" or `teacher` LIKE "%%%s%%") ', $keywords, $keywords);
        }

        // 取報名中的課程筆數
        $sql = "select count(*) from ".$this->useCourseTable." where kind = 'course' AND status in (1, 2, 3, 4) ".$subWhere;
            
        $count = $sysConn->GetOne($sql);
        
        return $count;
    }
    
    
    
    /**
     *  @name 取所有課程：課程編號、課程名稱、課程狀態、老師群姓名、其中一位老師大頭照加密編號
     *  @order 報名日期：未來到過去
     *
     *  @author cch
     *  @since 2014/2/11
    */

    function getAllCourse($type = '', $tmpCourseId = array(), $keywords = '',$page = array(), $orderby = '')
    {
        $subWhere = '';
        global $sysRoles, $sysSession, $MSG;

        switch ($type) {
            // 取可報名課程
            case 'signing':
                $subWhere .=
                    'and ('.
                    '((status = 1 or status = 3) and ( Isnull(en_begin) or en_begin <= curdate()) and (Isnull(en_end) or en_end >= curdate())) '.
                    'or '.
                    '((status = 2 or status = 4) and ( Isnull(en_begin) or en_begin <= curdate()) and (Isnull(en_end) or en_end >= curdate()) and (Isnull(st_end) or st_end >= curdate())) '.
                    ') ';
                break;

            // 歷史課程
            case 'history':
                $subWhere .= 'and (`st_end` is not null and curdate() > `st_end`) ';
                break;
        }

        // 若有指定課程編號
        if (is_array($tmpCourseId) && count($tmpCourseId) >= 1) {
            $subWhere .= ' AND ( 0 ';
            foreach($tmpCourseId as $k => $v) {
                $courseId = implode(',', $v);
                $subWhere .= 'OR ('.$this->courseWhere.' = '.$k.' and `course_id` in (' . $courseId . ')) ';
            }
            $subWhere .= ') ';
        }

        if ($keywords !== '') {
//            $subWhere .= sprintf('and (`caption` LIKE "%%%s%%" or `teacher` LIKE "%%%s%%") ', $keywords, $keywords);
            $captionQuery = getColumnSerialQuery('caption', $keywords);
            $subWhere .= sprintf('and (%s or `teacher` LIKE "%%%s%%") ', $captionQuery, $keywords);
        }

        // 最新課程 or 熱門課程 排序
        if ($orderby == 'hot') {
            // 取得所有可報名的課程編號
            $allSignCourseIds = dbGetCol('WM_term_course','course_id','`kind` = "course" AND `status` in (1, 2, 3, 4) '.$subWhere);

            // 取得該頁熱門課程的編號
            $hotCourseIds = dbGetCol(
                'WM_term_major',
                'course_id', 
                sprintf(
                    "course_id in (%s) group by course_id order by count(*) desc LIMIT %d, %d", 
                    implode(',',$allSignCourseIds), 
                    $page['p1'], 
                    $page['p2']
                )
            );

            if (is_array($hotCourseIds) && count($hotCourseIds)) {
                $subWhere .= sprintf(" AND course_id in (%s) ", implode(',', $hotCourseIds));
            }

            // 先取得熱門課程排序後的編號
            $cols = '`caption`, `course_id`, `en_begin`, `teacher`, `st_begin`, `st_end`, `status`, `content`' . $this->courseColumnPlus;
            $tb = $this->useCourseTable; // 'WM_term_course';
            $where  = '`kind` = "course" AND `status` in (1, 2, 3, 4) ' .
            $subWhere .
            'order by '.sprintf("field(course_id,%s)", implode(',', $hotCourseIds));

        }else{
            // 取報名中的課程：課程編號、課程名稱、課程狀態、課程簡介
            $cols = '`caption`, `course_id`, `en_begin`, `teacher`, `st_begin`, `st_end`, `status`, `content`' . $this->courseColumnPlus;
            $tb = $this->useCourseTable; // 'WM_term_course';
            $where  = '`kind` = "course" AND `status` in (1, 2, 3, 4) ' .
            $subWhere .
            'order by `en_begin` desc, `course_id` desc ';

            if (0 < count($page)) {
                $where .= 'LIMIT '.$page['p1'].','.$page['p2'].' ';
            }
        }

        chkSchoolId('WM_term_course');
        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);
        if ($rs) {
            $parseurl = parse_url($_SERVER['SCRIPT_URI'] );
            $host = $parseurl['scheme'] . '://' . $parseurl['host'];
            
            while (!$rs->EOF) {
                $multiCaption = getCaption($rs->fields['caption']);
                $caption = $multiCaption[$sysSession->lang];
                $cid = checkCourseID($rs->fields['course_id']);
                $sid = $rs->fields['school_id'];
                $arrCid[$sid][] = $rs->fields['course_id'];
                $status = $rs->fields['status'];
                $content = strip_tags($rs->fields['content']);
                $teacher = $rs->fields['teacher'];
                $stBegin = $rs->fields['st_begin'];
                $now = date('Y-m-d H:i:s');
                $stEnd = $rs->fields['st_end'];
                $enBegin = $rs->fields['en_begin'];
                if (($now >= $stBegin and $now <= $stEnd) or ($stBegin === null and $stEnd === null) or
                    ($stBegin === null and $now <= $stEnd) or ($now >= $stBegin and $stEnd === null)) {
                    $isClassing = true;
                } else {
                    $isClassing = false;
                }
                if ($stBegin === null && $stEnd !== null) {
                    $stBegin = $MSG['rightnow'][$sysSession->lang];
                }
                if ($stBegin === null && $stEnd === null) {
                    $classPeriod = $MSG['notset'][$sysSession->lang];
                } else {
                    $classPeriod = $stBegin . '~' . $stEnd;
                }
                
                list($picture) = dbGetStSr('CO_course_picture', '`picture`', "course_id='{$cid}'", ADODB_FETCH_NUM);
                
                $courseList[sprintf("'%d%d'",$sid,$cid)] = array(
                    'sid' => $sid,
                    'cid' => $cid,
                    'caption' => $caption,
                    'status' => $status,
                    'content' => $content,
                    'teacher' => $teacher,
                    'classPeriod' => $classPeriod,
                    'isClassing' => $isClassing,
                    'spic' => base64_encode($sid),
                    'cpic' => base64_encode($cid),
                    'enBegin' => $enBegin,
                    'qrcode_url' => getQrcodePath($host . '/info/'  . $cid . '/' . $sid . '?lang=' . $sysSession->lang),
                    'hasCoursePic' => (!isset($picture)?'N':'Y')
                );
                $rs->MoveNext();
            }
        }
        if (is_portal_school) {
            $groupPlus = ', `school_id`';
        }
        if(count($arrCid) >= 1) {
            // 取課程老師姓名、大頭照編號
            $where  = '`course_id` != 10000000 AND ( 0 ';
            foreach($arrCid as $k => $v) {
                $where .= 'OR ('.$this->courseWhere.' = '.$k.' AND `course_id` in (\'' . implode('\',\'', $v) . '\')) ';
            }
            $where .= ') ';
            $where .= sprintf(' AND m.role&%d ',$sysRoles['teacher']);
            // $where .= 'group by m.course_id '.$groupPlus.', m.add_time, m.username';

            $rs = dbGetStMr($this->useMajorTable.' as m',
                    'm.course_id, m.username, m.role&' . $sysRoles['teacher'] . ' as level'. $this->courseColumnPlus, $where, ADODB_FETCH_ASSOC);
            if ($rs) {
                $teacherRealnames = array();
                while (!$rs->EOF) {
                    if ($rs->fields['level'] >= 1) {
                        if (isset($teacherRealnames[$rs->fields['username']]) === FALSE) {
                            $teacherRealname = dbGetone('WM_user_account','CONCAT(IFNULL(`last_name`,""), IFNULL(`first_name`,""))',sprintf("username='%s'",$rs->fields['username']));
                            $teacherRealnames[$rs->fields['username']] = ($teacherRealname === FALSE)? '' : $teacherRealname;
                        }
                        if (!isset($courseList[sprintf("'%d%d'",$rs->fields['school_id'],$rs->fields['course_id'])]['teacherPic']) || $courseList[sprintf("'%d%d'",$rs->fields['school_id'],$rs->fields['course_id'])]['teacherPic'] == '') {
                            $courseList[sprintf("'%d%d'",$rs->fields['school_id'],$rs->fields['course_id'])]['teacherPic'] = urlencode(base64_encode($rs->fields['username']));
                            $courseList[sprintf("'%d%d'",$rs->fields['school_id'],$rs->fields['course_id'])]['teacher'] = $teacherRealnames[$rs->fields['username']];
                        }
                    }

                    $rs->MoveNext();
                }
            }
        }

        return $courseList;
    }


    /**
     *  @name 取課程群組，與getTmpCourseGroup搭配使用
     *  @order 優先權
     *
     *  @author cch
     *  @since 2014/2/14
    */
    function getCourseGroup()
    {
        global $sysSession;
        $this->getTmpCourseGroup();

        // 取群組名稱
        $cols = '`course_id`, `caption`';
        $tb = 'WM_term_course';
        $where  = '`course_id` in (' . implode(',', $this->groupId) . ')';

        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);
        if ($rs && $rs->RecordCount() >= 1) {
            while (!$rs->EOF) {
                $multiCaption = getCaption($rs->fields['caption']);
                $caption = $multiCaption[$sysSession->lang];
                $this->groupName[$rs->fields['course_id']] = $caption;
                $rs->MoveNext();
            }
        }

        return array('groupName' => $this->groupName, 'courseGroup' => $this->courseGroup);
    }

    function getTmpCourseGroup($gId = 10000000)
    {
        $cols = '`parent`, `child`, `permute`';
        $tb = 'WM_term_group';
        $where  = '`parent` = '. $gId . ' order by `permute`';

        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);
        if ($rs && $rs->RecordCount() >= 1) {
            while (!$rs->EOF) {
                $child = $rs->fields['child'];
                if ($child >= '1') {
                    if ($gId !== 10000000) {
                        $this->courseGroup[$gId][] = $child;
                        $this->groupId[] = "'" . $child . "'";
                    }
                    $this->getTmpCourseGroup($child);
                }
                $rs->MoveNext();
            }
        }
    }


    /**
     *  @name 取課程群組HTML，與getTmpHtmlCourseGroup搭配使用
     *  @order 優先權
     *
     *  @author cch
     *  @since 2014/2/17
    */
    function getHtmlCourseGroup()
    {
        $this->getTmpHtmlCourseGroup();

        return $this->htmlCourseGroup;
    }

    function getTmpHtmlCourseGroup($gId = 10000000, $p = 1)
    {
        global $sysSession;

        $cols = 'WM_term_group.`parent`, WM_term_group.`child`, WM_term_group.`permute`, WM_term_course.`caption`';
        $tb = 'WM_term_group left join WM_term_course on WM_term_group.`child` = WM_term_course.`course_id`';
        $where  = 'WM_term_group.`parent` = '. $gId . ' and WM_term_course.kind = \'group\'
            order by WM_term_group.`permute`';

        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);
        if ($rs && $rs->RecordCount() >= 1) {
            while (!$rs->EOF) {
                $child = $rs->fields['child'];
                if ($child >= '1') {
                    $multiCaption = getCaption($rs->fields['caption']);
                    $caption = $multiCaption[$sysSession->lang];
                    if ($gId !== 10000000) {
                        $this->htmlCourseGroup .=  '<tr class="treegrid-' . $this->i . ' treegrid-parent-' . $p . '">
                            <td><span id=\'' . $rs->fields['child'] . '\' class="link" style="">' . $caption . '<div></div></span>
                                </td></tr>';
                    } else {
                        $this->htmlCourseGroup .=  '<tr class="treegrid-' . $this->i . ' root">
                            <td><span id=\'' . $rs->fields['child'] . '\' class="link" style="">' . $caption . '<div></div></span>
                                </td></tr>';
                    }
                    $this->i ++;
                    $this->getTmpHtmlCourseGroup($child, ($this->i - 1));
                }
                $rs->MoveNext();
            }
        }
    }
    
    // 取樹狀結構下的所有課程筆數，與getTmpCourseTreeFamily搭配使用
    function getCourseTreeFamilyNum($parentId = array(array('')))
    {
        $this->getTmpCourseTreeFamily($parentId);
        if (count($this->sons) >=1 ) {
            $num = $this->getAllCourseNum('signing', $this->sons);
        } else {
            $num = 0;
        }

        return $num;
    }
    
    
    // 取樹狀結構下的所有課程，與getTmpCourseTreeFamily搭配使用
    function getCourseTreeFamily($parentId = array(array('')),$page = array(), $isDtl = TRUE)
    {
        $course = array();

        $this->getTmpCourseTreeFamily($parentId);
        if (count($this->sons) >=1 ) {
            if ($isDtl === TRUE) {
                $course = $this->getAllCourse('signing', $this->sons,'',$page);
            } else {
                $course = $this->sons;
            }
        } else {
            $course = null;
        }

        return $course;
    }

    function getTmpCourseTreeFamily($parentId = array(array('')))
    {
        global $sysSession;
        $sons = array();

        if (count($parentId) >= 1) {
            // $strParentId = implode(',', $parentId);

            // 取所有兒子課程
            $cols = '`parent`, `child`'.$this->courseColumnPlus;
            $tb = $this->useGroupTable;
            $where ='0 ';
            foreach($parentId as $k => $v) {
                $whereColumn = $this->courseWhere;
                if (null == $k) {
                    $k = $sysSession->school_id;
                    $whereColumn = $sysSession->school_id;
                }
                $where .= 'OR ('.$whereColumn.' = '.$k.' AND `parent` in (\'' . implode('\',\'', $v) . '\')) ';
            }
            $where  .= 'order by `permute`';

            $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);
            if ($rs) {
                while (!$rs->EOF) {
                    if ($rs->fields['child'] !== '0') {
                        if (!in_array($rs->fields['child'], $sons)){
                            $sons[$rs->fields['school_id']][] = $rs->fields['child'];
                        }

                        if (!in_array($rs->fields['child'], $this->sons))
                        {
                            $this->sons[$rs->fields['school_id']][] = $rs->fields['child'];
                        }
                    }
                    $rs->MoveNext();
                }

                if (count($sons) >= 1) {
                    $this->getTmpCourseTreeFamily($sons);
                }
            }
        }
    }

    /**
     *
     * 課程是否存在
     * @param int $courseId 課程編號
     */
    function isCourseExists($courseId, $schoolId='')
    {
        $where = '';
        $courseId = intval($courseId);
        if (strlen($courseId) != 8) return false;
        if ('' != $schoolId) {
            $schoolId = intval($schoolId);
            if (strlen($schoolId) != 5) return false;
            $where = sprintf('AND `school` = %d', $schoolId);
        }
        $ct = dbGetOne($this->useCourseTable, 'count(*)', sprintf('course_id=%d ',$courseId) . $where);
        return ($ct==1);
    }

    /**
     *
     * 以課程編號取得WM_term_course的資料
     * @param int $courseId 課程編號
     */
    function getCourseById($courseId, $schoolId='')
    {
        if ('' != $schoolId) {
            $where = sprintf('AND `school` = %d', $schoolId);
        }
        return dbGetRow($this->useCourseTable, '*', sprintf('course_id=%d ',$courseId) . $where, ADODB_FETCH_ASSOC);
    }

    /**
     *
     * 取得課程所歸屬的父群組資料
     * @param int $courseId 課程編號
     */
    function getBelongCourseGroups($courseId)
    {
        $tables = 'WM_term_group as T1 INNER JOIN WM_term_course as T2 on T1.parent=T2.course_id';
        $fields = 'T1.parent, T2.caption';
        $where = sprintf('T1.child=%d',$courseId);
        $data = dbGetAll($tables, $fields, $where);
        return $data;
    }

    /**
     *
     * 取得學員修的課程
     * @param string $user 帳號
     */
    function getUserCourses($user='', $roles=0, $onlyNum=false)
    {
        global $sysConn, $sysRoles, $sysSession, $MSG;
        $roles = $roles ? $roles : $sysRoles['student'];
        $csTable = $this->useCourseTable;
        $mjTable = $this->useMajorTable;
        $joinOn = $this->majorWhere;
        $csColumn = $this->courseColumnPlus;
        $csColumn2 = $this->courseColumnPlus2;
        $csWhere = $this->courseWhere;
        $groupPlus = '';
        // 我的課程需包含內容商及入口網校課程
        if (!is_independent_school && $roles == $sysRoles['auditor']|$sysRoles['student']) {
            $csTable = sysDBprefix.'MASTER.`CO_all_course`';
            $mjTable = sysDBprefix.'MASTER.`CO_all_major`';
            $joinOn = 'AND CS.`school` = MJ.`school` ';
            $csColumn = ', `school` AS `school_id` ';
            $csColumn2 = ', CS.`school` AS `school_id` ';
            $csWhere = '`school`';
            $groupPlus = ', school_id';
        }
        switch($roles) {
            case $sysRoles['student']:
            case $sysRoles['auditor']|$sysRoles['student']:
                $subWhere = 'AND ' . 'MJ.role&' . ($sysRoles['student']|$sysRoles['auditor']) . ' ' .
                            'AND (CS.status = 1 OR ((CS.status = 2 OR CS.status = 4) AND (isnull(CS.st_end) or CS.st_end>=CURDATE())) OR CS.status = 3) ';
                break;
            case $sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']:
                $subWhere = 'AND (' . ($roles & $sysRoles['auditor'] ? ('(MJ.role&' . $sysRoles['auditor'] .
                ' and (CS.status=1 or (CS.status=2 and (isnull(CS.st_begin) or CS.st_begin<=CURDATE()) and (isnull(CS.st_end) or CS.st_end>=CURDATE()))))') : '') .
                ($roles & $sysRoles['student'] ? ( ($roles & $sysRoles['auditor'] ? ' or ' : '') . '(MJ.role&' . $sysRoles['student'] .
                ' and (CS.status=1 or CS.status=3 or ((CS.status=2 or CS.status=4) and (isnull(CS.st_begin) or CS.st_begin<=CURDATE()) and (isnull(CS.st_end) or CS.st_end>=CURDATE()))))') : '') .
                ($roles & ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']) ? ( ($roles & $sysRoles['student'] ? ' or ' : '') .
                '(MJ.role&' . ($roles & ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher'])) .
                ' and (CS.status between 1 and 5))') : '') .
                ') ';
                $csTable = '`WM_term_course`';
                $mjTable = '`WM_term_major`';
                $joinOn = '';
                $csColumn = ', '.$sysSession->school_id.' AS `school_id`';
                $csColumn2 = ', '.$sysSession->school_id.' AS `school_id`';
                $csWhere = $sysSession->school_id;
                break;
        }

        if ($onlyNum) {
            $RS = $sysConn->Execute('use '.sysDBschool.';');
            $sql =  'select CS.course_id ' .$csColumn2.' '.
                    'from '.$mjTable.' as MJ left outer join WM_record_reading as P '.
                    'on MJ.course_id=P.course_id and P.username="'.$user.'" ' .
                    'left join '.$csTable.' as CS on MJ.course_id=CS.course_id '. $joinOn .
                    'where MJ.username="'.$user.'" AND CS.kind="course" ' .
                    $subWhere .
                    'group by MJ.course_id '.$groupPlus;
            $RS = $sysConn->Execute($sql);
            if ($RS) {
                $rtn = 0;
                while (!$RS->EOF) {
                    $rtn += 1;
                    $RS->MoveNext();
                }
            }
            return $rtn;
        }
        $sql = 'select CS.course_id, CS.caption, CS.st_begin, CS.st_end,CS.status,CS.content,CS.teacher,CS.credit,CS.fair_grade,'.
              'MJ.last_login, MJ.login_times, MJ.post_times, MJ.dsc_times, MJ.role&'.$sysRoles['auditor'].' as isAuditor, '.
              'sum(unix_timestamp(P.over_time) - unix_timestamp(P.begin_time)+1) as rss,count(P.username) as page ' . $csColumn2 .
              'from '.$mjTable.' as MJ left outer join WM_record_reading as P '.
              'on MJ.course_id=P.course_id and P.username="'.$user.'" ' .
              'left join '.$csTable.' as CS on MJ.course_id=CS.course_id '. $joinOn .
              'where MJ.username="'.$user.'" AND CS.kind="course" ' .
              $subWhere .
              'group by MJ.course_id '.$groupPlus;

        $RS = $sysConn->Execute($sql);

        if ($RS) {
                $courseData = array();
                while($fields = $RS->FetchRow()){

                    $arrCourseId[$fields['school_id']][] = $fields['course_id'];

                    // 開課期間
                    $stBegin = $fields['st_begin'];
                    $now = date('Y-m-d H:i:s');
                    $stEnd = $fields['st_end'];
                    if (($now >= $stBegin and $now <= $stEnd) or ($stBegin === null and $stEnd === null) or
                        ($stBegin === null and $now <= $stEnd) or ($now >= $stBegin and $stEnd === null)) {
                        $fields['isClassing'] = true;
                    } else {
                        $fields['isClassing'] = false;
                    }
                    if ($stBegin === null && $stEnd !== null) {
                        $stBegin = $MSG['rightnow'][$sysSession->lang];
                    }
                    if ($stBegin === null && $stEnd === null) {
                        $fields['classPeriod'] = $MSG['notset'][$sysSession->lang];
                    } else {
                        $fields['classPeriod'] = $stBegin . '~' . $stEnd;
                    }
                    if ($fields['isAuditor'] == 16) {
                        $fields['isAuditor'] = true;
                    } else if ($fields['isAuditor'] == 0) {
                        $fields['isAuditor'] = false;
                    }

                    $fields['content'] = strip_tags($fields['content']);
                    $fields['spic'] = base64_encode($fields['school_id']);
                    $fields['cpic'] = base64_encode($fields['course_id']);
                $courseData[$fields['school_id'].$fields['course_id']] = $fields;
            }

            if(count($arrCourseId) >= 1) {
                // 取課程老師大頭照編號
                $where  = '`course_id` != 10000000 AND ( 0 ';
                foreach($arrCourseId as $k=>$v) {
                    $where .= 'OR ('.$csWhere.' = '.$k.' AND `course_id` in (\'' . implode('\',\'', $v) . '\')) ';
                }
                $where  .= ') ';
                $where .= sprintf(' AND m.role&%d ',$sysRoles['teacher']);
                // $where .= 'group by m.course_id '.$groupPlus.', m.add_time, m.username';
                
                $rs = dbGetStMr($mjTable.' as m'
                        , 'm.course_id, m.username, m.role&' . $sysRoles['teacher'] . ' as level' . $csColumn, $where, ADODB_FETCH_ASSOC);
                if ($rs) {
                    $teacherRealnames = array();
                    while (!$rs->EOF) {
                        if ($rs->fields['level'] >= 1) {
                            if (isset($teacherRealnames[$rs->fields['username']]) === FALSE) {
                                $teacherRealname = dbGetone('WM_user_account','CONCAT(IFNULL(`last_name`,""), IFNULL(`first_name`,""))',sprintf("username='%s'",$rs->fields['username']));
                                $teacherRealnames[$rs->fields['username']] = ($teacherRealname === FALSE)? '' : $teacherRealname;
                            }
                            if (!isset($courseData[$rs->fields['school_id'].$rs->fields['course_id']]['teacherPic']) || $courseData[$rs->fields['school_id'].$rs->fields['course_id']]['teacherPic'] == '') {
                                $courseData[$rs->fields['school_id'].$rs->fields['course_id']]['teacherPic'] = urlencode(base64_encode($rs->fields['username']));
                                $courseData[$rs->fields['school_id'].$rs->fields['course_id']]['teacher'] = $teacherRealnames[$rs->fields['username']];
                            }
                        }
                        $rs->MoveNext();
                    }              
                }
            }
        }
        return $courseData;
    }



    function getUserCoursesDetail($user='', $roles=0, $onlyNum=false, $query = '',$page = array())
    {
        global $sysConn, $sysRoles, $sysSession, $MSG;
        $roles = $roles ? $roles : $sysRoles['student'];
        $csTable = $this->useCourseTable;
        $mjTable = $this->useMajorTable;
        $joinOn = $this->majorWhere;
        $csColumn = $this->courseColumnPlus;
        $csColumn2 = $this->courseColumnPlus2;
        $csWhere = $this->courseWhere;
        $groupPlus = '';
        // 我的課程需包含內容商及入口網校課程
        if (!is_independent_school && $roles == $sysRoles['auditor']|$sysRoles['student']) {
            $csTable = sysDBprefix.'MASTER.`CO_all_course`';
            $mjTable = sysDBprefix.'MASTER.`CO_all_major`';
            $joinOn = 'AND CS.`school` = MJ.`school` ';
            $csColumn = ', `school` AS `school_id` ';
            $csColumn2 = ', CS.`school` AS `school_id` ';
            $csWhere = '`school`';
            $schoolWhere = 'CS.`school`';
            $groupPlus = ', school_id';
        }
        switch($roles) {
            case $sysRoles['student']:
            case $sysRoles['auditor']|$sysRoles['student']:
                $subWhere = 'AND ' . 'MJ.role&' . ($sysRoles['student']|$sysRoles['auditor']) . ' ' .
                    'AND (CS.status = 1 OR ((CS.status = 2 OR CS.status = 4) AND (isnull(CS.st_end) or CS.st_end>=CURDATE())) OR CS.status = 3) ';
                break;
            case $sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']:
                $subWhere = 'AND (' . ($roles & $sysRoles['auditor'] ? ('(MJ.role&' . $sysRoles['auditor'] .
                        ' and (CS.status=1 or (CS.status=2 and (isnull(CS.st_begin) or CS.st_begin<=CURDATE()) and (isnull(CS.st_end) or CS.st_end>=CURDATE()))))') : '') .
                    ($roles & $sysRoles['student'] ? ( ($roles & $sysRoles['auditor'] ? ' or ' : '') . '(MJ.role&' . $sysRoles['student'] .
                        ' and (CS.status=1 or CS.status=3 or ((CS.status=2 or CS.status=4) and (isnull(CS.st_begin) or CS.st_begin<=CURDATE()) and (isnull(CS.st_end) or CS.st_end>=CURDATE()))))') : '') .
                    ($roles & ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']) ? ( ($roles & $sysRoles['student'] ? ' or ' : '') .
                        '(MJ.role&' . ($roles & ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher'])) .
                        ' and (CS.status between 1 and 5))') : '') .
                    ') ';
                $csTable = '`WM_term_course`';
                $mjTable = '`WM_term_major`';
                $joinOn = '';
                $csColumn = ', '.$sysSession->school_id.' AS `school_id`';
                $csColumn2 = ', '.$sysSession->school_id.' AS `school_id`';
                $schoolWhere = $sysSession->school_id;
                $csWhere = $sysSession->school_id;
                break;
        }
        if($query){
            if(preg_match("/^(\d{5})(\d{8})$/",$query,$matches)){
                $subWhere.=' AND CS.course_id=' . $matches[2].' AND '.$schoolWhere.'='.$matches[1];
            }else{
                // 限定只勾選繁簡體語系 
                // a:2:{s:4:"Big5";s:6:"數學";s:6:"GB2312";s:6:"数学";}
                $subWhere.=' AND ' . getColumnSerialQuery('CS.caption', $query);
            }

        }
        if ($onlyNum) {
            $RS = $sysConn->Execute('use '.sysDBschool.';');
            $sql =  'select CS.course_id ' .$csColumn2.' '.
                'from '.$mjTable.' as MJ left outer join WM_record_reading as P '.
                'on MJ.course_id=P.course_id and P.username="'.$user.'" ' .
                'left join '.$csTable.' as CS on MJ.course_id=CS.course_id '. $joinOn .
                'where MJ.username="'.$user.'" AND CS.kind="course" ' .
                $subWhere.' '.
                ' group by MJ.course_id '.$groupPlus;
            $RS = $sysConn->Execute($sql);
            if ($RS) {
                $rtn = 0;
                while (!$RS->EOF) {
                    $rtn += 1;
                    $RS->MoveNext();
                }
            }
            return $rtn;
        }
        $sql = 'select CS.course_id, CS.caption, CS.st_begin, CS.st_end,CS.status,CS.content,CS.teacher,CS.credit,CS.fair_grade,'.
            'MJ.last_login, MJ.login_times, MJ.post_times, MJ.dsc_times, MJ.role, '.
            'sum(unix_timestamp(P.over_time) - unix_timestamp(P.begin_time)+1) as rss,count(P.username) as page ' . $csColumn2 .
            'from '.$mjTable.' as MJ left outer join WM_record_reading as P '.
            'on MJ.course_id=P.course_id and P.username="'.$user.'" ' .
            'left join '.$csTable.' as CS on MJ.course_id=CS.course_id '. $joinOn .
            'where MJ.username="'.$user.'" AND CS.kind="course" ' .
            $subWhere.' '.
            'group by MJ.course_id '.$groupPlus;
        if (0 < count($page)) {
            $sql .= ' LIMIT '.$page['p1'].','.$page['p2'].' ';
        }
        $RS = $sysConn->Execute($sql);

        if ($RS) {
            $courseData = array();
            while($fields = $RS->FetchRow()){

                $arrCourseId[$fields['school_id']][] = $fields['course_id'];

                // 開課期間
                $stBegin = $fields['st_begin'];
                $now = date('Y-m-d H:i:s');
                $stEnd = $fields['st_end'];
                if (($now >= $stBegin and $now <= $stEnd) or ($stBegin === null and $stEnd === null) or
                    ($stBegin === null and $now <= $stEnd) or ($now >= $stBegin and $stEnd === null)) {
                    $fields['isClassing'] = true;
                } else {
                    $fields['isClassing'] = false;
                }
                if ($stBegin === null && $stEnd !== null) {
                    $stBegin = $MSG['rightnow'][$sysSession->lang];
                }
                if ($stBegin === null && $stEnd === null) {
                    $fields['classPeriod'] = $MSG['notset'][$sysSession->lang];
                } else {
                    $fields['classPeriod'] = $stBegin . '~' . $stEnd;
                }
                if ($fields['role']&$sysRoles['auditor']) {
                    $fields['isAuditor'] = true;
                } else{
                    $fields['isAuditor'] = false;
                }
                $fields['sid'] = $fields['school_id'];
                $fields['cid'] = $fields['course_id'];
                $multiCaption = getCaption($fields['caption']);
                $caption = $multiCaption[$sysSession->lang];
                $fields['caption'] = $caption;
                $fields['content'] = strip_tags($fields['content']);
                $fields['spic'] = base64_encode($fields['school_id']);
                $fields['cpic'] = base64_encode($fields['course_id']);
                $fields['qrcode_url'] = getQrcodePath($_SERVER['HTTP_ORIGIN'] . '/info/'  . $fields['course_id'] . '/' . $fields['school_id'] . '?lang=' . $sysSession->lang);

                if($roles==$sysRoles['auditor']|$sysRoles['student']){
                    // 三合一的種類
                    $isStudent = ($fields['role'] & $sysRoles['student']); // 判斷是否為正式生
                    $type_array = array('homework','exam','questionnaire','peer');
                    $QTI_undo = array();
                    for ($q_i=0;$q_i < count($type_array);$q_i++){
                        // 取得本門課 三合一的 施測中的試卷 的 exam_id
                        $ary_action = dbGetCol(sysDBprefix.$fields['school_id'].'.WM_qti_' . $type_array[$q_i] . '_test','exam_id','course_id=' . $fields['course_id'] . ' and publish="action"');
                        // 判斷本門課是否有 「施測中」的 三合一 begin
                        if (count($ary_action) > 0){
                            // 取得學員已做的作業、測驗與問卷
                            $arydo = array();
                            $table = sysDBprefix.$fields['school_id'].'.WM_qti_' . $type_array[$q_i] . '_result';
                            $field = 'DISTINCT `exam_id`';
                            $where = '`exam_id` in (' . implode(',',$ary_action) . ") and `examinee`='{$user}'";
                            $arydo = dbGetCol($table, $field, $where);
                            $examinee_perm = array('homework' => 1700400200, 'exam' => 1600400200, 'questionnaire' => 1800300200, 'peer' => 1710400200);
                            $p = aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable');
                            // for begin
                            for ($r_i=0;$r_i < count($ary_action);$r_i++){
                                // if begin
                                $aclVerified = aclVerifyPermission($examinee_perm[$type_array[$q_i]], $p, $fields['course_id'], $ary_action[$r_i]);
                                if (!$aclVerified || ($aclVerified === 'WM2' && !$isStudent )){
                                    continue;
                                }
                                // if end
                                if (! in_array($ary_action[$r_i], $arydo)){
                                    $QTI_undo[$type_array[$q_i]]++;
                                }else{
                                    $QTI_undo[$type_array[$q_i]] = $QTI_undo[$type_array[$q_i]] +0;
                                }
                            }
                            // for end
                        }else{
                            $QTI_undo[$type_array[$q_i]] = 0;
                        }
                        // 判斷本門課是否有 「施測中」的 三合一 end
                    }
                    $fields["QTI_undo"]=$QTI_undo;
                    // 三合一 end
                    // 學習進度 begin
                    // 取得修課進度 (getProgress寫在wmpro的lib/course.php)
                    list($courseXML) = dbGetStSr(sysDBprefix.$fields['school_id'].'.WM_term_path', 'content', "course_id='{$fields['course_id']}' ORDER by serial DESC LIMIT 1", ADODB_FETCH_NUM);
                    $getProgress = getScoolCourseProgress($fields['school_id'],$fields['course_id'], $courseXML, $user);
                    $fields["progress"] = intval($getProgress['progress']);
                    // 學習進度 end
                }
                if($roles=$sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']){
                    // 取課程人數(正式生+旁聽生)
                    $rs = dbGetStMr('WM_term_major', 'count(*) as Number', sprintf('`course_id` = %d and role&%d', $fields['course_id'], ($sysRoles['student']|$sysRoles['auditor'])), ADODB_FETCH_ASSOC);
                    if ($rs) {
                        $studentCnt = $rs->fields['Number'];
                        $fields['student_number'] = $studentCnt;
                    }
                }
                // group begin
                $groupData=getSchoolCourseParents($fields['school_id'],$fields['course_id']);
                $fields["group"]=$groupData;
                // group end

                $courseData[$fields['school_id'].$fields['course_id']] = $fields;
            }

            if(count($arrCourseId) >= 1) {
                // 取課程老師大頭照編號
                $where  = '`course_id` != 10000000 AND ( 0 ';
                foreach($arrCourseId as $k=>$v) {
                    $where .= 'OR ('.$csWhere.' = '.$k.' AND `course_id` in (\'' . implode('\',\'', $v) . '\')) ';
                }
                $where .= ') ';
                $where .= sprintf(' AND m.role&%d ',$sysRoles['teacher']);
                // $where .= 'group by m.course_id '.$groupPlus.', m.add_time, m.username';
            
                $rs = dbGetStMr($mjTable.' as m'
                        , 'm.course_id, m.username, m.role&' . $sysRoles['teacher'] . ' as level' . $csColumn, $where, ADODB_FETCH_ASSOC);
                if ($rs) {
                    while (!$rs->EOF) {
                        if ($rs->fields['level'] >= 1) {
                            $teacherRealname = dbGetone('WM_user_account','CONCAT(IFNULL(`last_name`,""), IFNULL(`first_name`,""))',sprintf("username='%s'",$rs->fields['username']));
                            if (!isset($courseData[$rs->fields['school_id'].$rs->fields['course_id']]['teacherPic']) || $courseData[$rs->fields['school_id'].$rs->fields['course_id']]['teacherPic'] == '') {
                                $courseData[$rs->fields['school_id'].$rs->fields['course_id']]['teacherPic'] = urlencode(base64_encode($rs->fields['username']));
                                $courseData[$rs->fields['school_id'].$rs->fields['course_id']]['teacher'] = $teacherRealname;
                            }
                        }
                        $rs->MoveNext();
                    }
                }
            }
        }
        return $courseData;
    }

    // 我的課程
    function getUserCoursesSimple($user='', $roles=0)
    {
        global $sysConn, $sysRoles, $sysSession, $MSG;
        $roles = $roles ? $roles : $sysRoles['student'];
        $csTable = $this->useCourseTable;
        $mjTable = $this->useMajorTable;
        $joinOn = $this->majorWhere;
        $csColumn = $this->courseColumnPlus;
        $csColumn2 = $this->courseColumnPlus2;
        $csWhere = $this->courseWhere;
        $groupPlus = '';
        
        // 我的課程需包含內容商及入口網校課程
        if (!is_independent_school && $roles == $sysRoles['auditor']|$sysRoles['student']) {
            $csTable = sysDBprefix.'MASTER.`CO_all_course`';
            $mjTable = sysDBprefix.'MASTER.`CO_all_major`';
            $joinOn = 'AND CS.`school` = MJ.`school` ';
            $csColumn = ', `school` AS `school_id` ';
            $csColumn2 = ', CS.`school` AS `school_id` ';
            $csWhere = '`school`';
            $groupPlus = ', school_id';
        }
        switch($roles) {
            case $sysRoles['student']:
            case $sysRoles['auditor']|$sysRoles['student']:
                $subWhere = 'AND ' . 'MJ.role&' . ($sysRoles['student']|$sysRoles['auditor']) . ' ' .
                    'AND (CS.status IN (1, 2, 3, 4)) ';
                break;
            
            case $sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']:
                $subWhere = 'AND (' . ($roles & $sysRoles['auditor'] ? ('(MJ.role&' . $sysRoles['auditor'] .
                        ' and (CS.status=1 or (CS.status=2 and (isnull(CS.st_begin) or CS.st_begin<=CURDATE()) and (isnull(CS.st_end) or CS.st_end>=CURDATE()))))') : '') .
                    ($roles & $sysRoles['student'] ? ( ($roles & $sysRoles['auditor'] ? ' or ' : '') . '(MJ.role&' . $sysRoles['student'] .
                        ' and (CS.status=1 or CS.status=3 or ((CS.status=2 or CS.status=4) and (isnull(CS.st_begin) or CS.st_begin<=CURDATE()) and (isnull(CS.st_end) or CS.st_end>=CURDATE()))))') : '') .
                    ($roles & ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']) ? ( ($roles & $sysRoles['student'] ? ' or ' : '') .
                        '(MJ.role&' . ($roles & ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher'])) .
                        ' and (CS.status between 1 and 5))') : '') .
                    ') ';
                $csTable = '`WM_term_course`';
                $mjTable = '`WM_term_major`';
                $joinOn = '';
                $csColumn = ', '.$sysSession->school_id.' AS `school_id`';
                $csColumn2 = ', '.$sysSession->school_id.' AS `school_id`';
                $csWhere = $sysSession->school_id;
                break;
        }

        chkSchoolId('WM_term_course');
        $sql = 'select CS.course_id, CS.caption'.$csColumn2.
            'from '.$mjTable.' as MJ '.
            'left join '.$csTable.' as CS on MJ.course_id=CS.course_id '. $joinOn .
            'where MJ.username="'.$user.'" AND CS.kind="course" ' .
            $subWhere .
            'group by MJ.course_id '.$groupPlus;
        $RS = $sysConn->Execute($sql);

        if ($RS) {
            $courseData = array();
            while($fields = $RS->FetchRow()){
                $fields['sid'] = $fields['school_id'];
                $fields['cid'] = $fields['course_id'];
                $multiCaption = getCaption($fields['caption']);
                $caption = $multiCaption[$sysSession->lang];
                $fields['caption'] = $caption;
                $fields['content'] = strip_tags($fields['content']);
                $fields['spic'] = base64_encode($fields['school_id']);
                $fields['cpic'] = base64_encode($fields['course_id']);
                $courseData[$fields['school_id'].$fields['course_id']] = $fields;
            }
        }
        return $courseData;
    }

    /**
     *
     * 取得學員修的課程
     * @param string $user 帳號
     */
    function getUserStudyCourses($user)
    {
        global $sysRoles;
        return $this->getUserCourses($user, $sysRoles['auditor']|$sysRoles['student']);

    }

    /**
     *
     * 取得老師教授的課程
     * @param string $user 帳號
     */
    function getUserTeachCourses($user)
    {
        global $sysRoles;
        return $this->getUserCourses($user, $sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']);
    }

    /**
     *
     * 依據課程ID取得課程的資料及老師資料
     * @param int $courseId 課程ID
     **/
    function getCourseInfo($cid) {
        global  $sysRoles, $sysSession;

        if ($sysSession->username == 'guest') {
            return;
        }

        // 取課程資訊：課程名稱
        $rs = dbGetStMr('WM_term_course', '`caption`, `content`', sprintf('`course_id` = %d', $cid), ADODB_FETCH_ASSOC);
        if ($rs) {
            $multiCaption = getCaption($rs->fields['caption']);
            $caption = $multiCaption[$sysSession->lang];
            $content = $rs->fields['content'];
            $courseList['course']['caption'] = $caption;
            $courseList['course']['content'] = htmlspecialchars($content);
        }
        // 取課程人數(正式生+旁聽生)
        $rs = dbGetStMr('WM_term_major', 'count(*) as Number', sprintf('`course_id` = %d and role&%d', $cid, ($sysRoles['student']|$sysRoles['auditor'])), ADODB_FETCH_ASSOC);
        if ($rs) {
            $studentCnt = $rs->fields['Number'];
            $courseList['course']['number'] = $studentCnt;
        }
        // 取課程老師姓名、大頭照編號
        $rs = dbGetStMr('`WM_term_major` as m INNER JOIN `WM_user_account` as a ON m.username = a.username',
                        'm.username, CONCAT(a.last_name, a.first_name) as realname, a.department, a.title',
                        sprintf('`course_id` = %d and role&%d', $cid, $sysRoles['teacher']), ADODB_FETCH_ASSOC);
        if ($rs) {
            while (!$rs->EOF) {
                $courseList['course']['teachers'][] = array(
                    'name'       => htmlspecialchars($rs->fields['realname']),
                    'department' => htmlspecialchars($rs->fields['department']),
                    'title'      => htmlspecialchars($rs->fields['title']),
                    'teacherpic' => urlencode(base64_encode($rs->fields['username']))
                );

                $rs->MoveNext();
            }
        }
        return $courseList;
    }

    /**
     *
     * 取得課程老師群資料
     * @param string $cid 課程ID
     * @param string $sid 學校ID
     */
    function getTeachersByCId($cid, $sid)
    {
        global $sysRoles;
        $addWhere = '';
        if ('' != $sid) {
            $addWhere = sprintf(' AND T1.school = %d', intval($sid));
        }
        $rs = dbGetAll($this->useMajorTable . ' as T1 INNER JOIN WM_user_account as T2 on T1.username=T2.username',
                'T1.username, CONCAT(T2.last_name,T2.first_name) as realname, T2.department, ROLE, T2.title, T2.email',
                sprintf("T1.course_id=%d and role&%d".$addWhere.' ORDER BY add_time ', $cid, $sysRoles['teacher']),
                ADODB_FETCH_ASSOC);
        return $rs;
    }

    // 用途：將 latex.codecogs.com latex路徑轉換成本地端圖片的路徑回傳
    // 目的：以利學生端考試時，改讀取本地端儲存的圖片，減少讀取次數，圖片不存在的改讀取 latex.codecogs.com，並嘗試重新下載圖片
    function transform_LATEX($data, $isReturn = true) 
    {
//        echo '<pre>';
//        var_dump('-------------------transform_LATEX---------------------------');
//        var_dump(htmlspecialchars($data));
//        echo '</pre>';

        // 是否有用到方程式
//        preg_match_all('/<img alt=".*" src="http:\/\/latex.codecogs.com\/gif.latex\?(.*)" \/>/', $data, $match);
//        preg_match_all('/src=.*?http:\/\/latex.codecogs.com\/gif.latex\?([~!%*()_.&;0-9a-zA-Z\*]*).*? \//', $data, $match);// 為了相容<>"會被轉換成entity code
        // MIS#43450 中原輸入的函式含有+-符號，於原來的判斷出錯了
        preg_match_all('/src=.*?http:\/\/latex.codecogs.com\/gif.latex\?(.*?)"\s/', htmlspecialchars_decode($data), $match);
        if (empty($_COOKIE['show_me_info']) === false) {
            echo '<pre>';
            var_dump($match);
            var_dump('--------------------------------------------------');
            echo '</pre>';
        }
        // 有用到方程式
        if (is_array($match[1]) === true && count($match[1]) >= 1) {
            global $sysSession;
            foreach ($match[1] as $k => $latexcode) {
                // 判斷檔名長度，應小於等於251
                if (empty($_COOKIE['show_me_info']) === false) {
                    echo '<pre>';
                    var_dump($latexcode);
                    var_dump(md5($latexcode));
                    var_dump(rawurldecode($latexcode));
                    var_dump(md5(rawurldecode($latexcode)));
                    var_dump(base64_encode(rawurldecode($latexcode)));
                    var_dump(md5(base64_encode(rawurldecode($latexcode))));
                    var_dump(strlen(base64_encode(rawurldecode($latexcode))));
                    echo '</pre>';
                }
                
                // 取檔名，使用base64_encode可能產生...30gPSA/.gif的檔名，因此都改用md5處理
//                if (strlen(base64_encode(rawurldecode($latexcode))) >= 252) {
                    $filename = md5($latexcode) . md5(rawurldecode($latexcode)) . md5(base64_encode(rawurldecode($latexcode))) . '.gif';
//                } else {
//                    $filename = base64_encode(rawurldecode($latexcode)) . '.gif';
//                }
                if (empty($_COOKIE['show_me_info']) === false) {
                    echo '<pre>';
                    var_dump($filename);
                    echo '</pre>';
                }
//                echo '<pre>';
//                var_dump($latexcode);
//                var_dump(rawurldecode($latexcode));
//                var_dump('http://latex.codecogs.com/gif.latex?' . $latexcode, '/base/' . $sysSession->school_id . '/latex/' . $filename);
//                echo '</pre>';
                // 置換url
                $local_file = '/base/' . $sysSession->school_id . '/latex/' . $filename;
                if (empty($_COOKIE['show_me_info']) === false) {
                    echo '<pre>';
//                    var_dump($latexcode);
//                    var_dump(rawurldecode($latexcode));
//                    var_dump(sysDocumentRoot . $local_file);
                    var_dump(file_exists(sysDocumentRoot . $local_file));
                    echo '</pre>';
                }
                if (file_exists(sysDocumentRoot . $local_file) === true) {
                    $remoteLatexImage = 'src="http://latex.codecogs.com/gif.latex?' . $latexcode . '"';
                    $localLatexImage = 'src="/base/' . $sysSession->school_id . '/latex/' . $filename . '"';
                    $data = str_replace($remoteLatexImage, $localLatexImage, $data);
                    if (empty($_COOKIE['show_me_info']) === false) {
                        echo '<pre>';
                        var_dump($remoteLatexImage);
                        var_dump($localLatexImage);
                        var_dump(strpos($data, $remoteLatexImage));
                        echo '</pre>';
                    }
                    
                    $remoteLatexImage = 'src=&quot;http://latex.codecogs.com/gif.latex?' . $latexcode . '"';
                    $localLatexImage = 'src=&quot;/base/' . $sysSession->school_id . '/latex/' . $filename . '"';
                    $data = str_replace($remoteLatexImage, $localLatexImage, $data);
                    if (empty($_COOKIE['show_me_info']) === false) {
                        echo '<pre>';
                        var_dump($remoteLatexImage);
                        var_dump($localLatexImage);
                        var_dump(strpos($data, $remoteLatexImage));
                        echo '</pre>';
                    }
                } else {
                    // 建立latex目錄
                    $folder_latex = sysDocumentRoot . '/base/' . $sysSession->school_id . '/latex';
                    if (is_dir($folder_latex) === false) {
                        mkdir($folder_latex, 0755, true);
                    }
//                    copy('http://latex.codecogs.com/gif.latex?' . $latexcode, sysDocumentRoot . '/base/' . $sysSession->school_id . '/latex/' . substr(base64_encode(rawurldecode($latexcode)), 0, 251) . '.gif');
                    copy('http://latex.codecogs.com/gif.latex?' . $latexcode, sysDocumentRoot . '/base/' . $sysSession->school_id . '/latex/' . $filename);
                }
                if (empty($_COOKIE['show_me_info']) === false) {
                    echo '<pre>';
                    var_dump('-------------------------');
                    echo '</pre>';
                }
            }
//            var_dump(htmlspecialchars($data));
        }
//        echo '<pre>';
//        var_dump('轉換後');
//        var_dump(htmlspecialchars($data));
//        echo '</pre>';
        
        if ($isReturn === true) {
            return $data;
        }
    }
    
    // 取課程容量資訊
    function getCourseQuota($isUpdate = '0') {
        global $sysSession, $MSG;

        // 更新quota資訊
        if ($isUpdate === '1') {
            getCalQuota($sysSession->course_id, $real_used, $quota_limit);
            setQuota($sysSession->course_id, $real_used);
        }
        
        getQuota($sysSession->course_id, $real_used, $quota_limit);
        $sysbar_quota_str = str_replace('%quota%', format_size($real_used) . '/' . format_size($quota_limit), $MSG['msg_course_quota'][$sysSession->lang]);
        
        $data = array(
            'limit' => $quota_limit,
            'used' => $real_used,
            'sysbar_quota_str' => $sysbar_quota_str
        );
        
        $result ['code'] = 1;
        $result ['message'] = 'success';
        $result ['data'] = $data;
        
        return $result;
    }
    
    /**
     * @name 刪除課程代表圖
     * @author cch
     *
     * @param string $cid: 課程編號
     *
     * @return array $data: code:-1失敗 0無異動 1成功, msg: 訊息
    */
    function delCoursePic($cid = null)
    {
        $data = array();
        
        if ($cid === null || $cid === '') {
            global $sysSession;
            $cid = $sysSession->course_id;
        } else {
            $cid = sysDecode($cid);
        }
        
        // 課程編號
        if (preg_match('/^\d{8}$/', $cid) === 0) {
            $data['code'] = -2;
            $data['msg'] = 'course id error';
        
            return $data;
        }
        
        $cnt = dbGetOne('CO_course_picture', 'course_id', sprintf('course_id = "%s"', $cid));
        $RS = dbDel('`CO_course_picture`', sprintf('course_id = "%s"', $cid));
        if ($RS) {
            if ($cnt === false) {
                $data['code'] = 0;
                $data['msg'] = 'nothing';
            } else {
                $data['code'] = 1;
                $data['msg'] = 'success';
            }
        } else {
            $data['code'] = -1;
            $data['msg'] = 'fail';
        }
        
        return $data;
    }
    
    /**
     * @name 刪除課程影片
     * @author cch
     *
     * @param string $cid: 課程編號
     *
     * @return array $data: code:-1失敗 0無異動 1成功, msg: 訊息
    */
    function delCourseMv($cid = null)
    {
        global $sysSession;
        $data = array();
        $flag = false;
        
        if ($cid === null || $cid === '') {
            $cid = $sysSession->course_id;
        } else {
            $cid = sysDecode($cid);
        }
        
        // 課程編號
        if (preg_match('/^\d{8}$/', $cid) === 0) {
            $data['code'] = -2;
            $data['msg'] = 'course id error';
        
            return $data;
        }
        
        $basePath = sprintf('%s/base/%05d/course/%08d/content', sysDocumentRoot, $sysSession->school_id, $cid);
        if (file_exists($basePath . '/public/course_introduce.mp4') === true) {
            unlink($basePath . '/public/course_introduce.mp4');
            $flag = true;
        }
        if (file_exists($basePath . '/public/course_introduce.jpg') === true) {
            unlink($basePath . '/public/course_introduce.jpg');
        }
        if (file_exists($basePath . '/public/course_introduce_2.jpg') === true) {
            unlink($basePath . '/public/course_introduce_2.jpg');
        }
        if (file_exists($basePath . '/public/course_introduce_3.jpg') === true) {
            unlink($basePath . '/public/course_introduce_3.jpg');
        }
        if ($flag === true) {
            if (file_exists($basePath . '/public/course_introduce.mp4') === false) {
                $data['code'] = 1;
                $data['msg'] = 'success';
            } else {
                $data['code'] = -1;
                $data['msg'] = 'fail';
            }
        } else {
            $data['code'] = 0;
            $data['msg'] = 'nothing';
        }
        
        return $data;
    }
    
    /* 取QTI作答數量 */
    function getQTIResultNum($type, $examIds, $examinee = '') {
        if (empty($examIds) === true) {
            die('error: no parameter');
        }
        
        if (in_array($type, array('questionnaire', 'homework', 'exam')) === false) {
            die('error: no parameter');
        }
        
        if ($examinee === '') {
            $whr = sprintf("`exam_id` IN (%s) AND status IN ('revised', 'submit', 'break')", $examIds);
        } else {
            $whr = sprintf("`exam_id` IN (%s) AND status IN ('revised', 'submit', 'break') AND examinee = '%s'", $examIds, $examinee);
        }
        
        $rs = dbGetStMr(
            '`WM_qti_' . $type . '_result`',
            'exam_id',
            $whr,
            ADODB_FETCH_ASSOC
        );
        
        $data = array();
        $data['code'] = 1;
        $data['msg'] = 'success';
        $data['data'] = $rs->RecordCount();
        
        return $data;
    }

    /*
     * 取lcms影片教材是否閱讀完畢
     */
    function isReadLcmsVideoDone($inputRid = '') {
        
        $rid = htmlspecialchars($inputRid);
        
        if ($rid === '') {
            $data['code'] = -1;
            $data['msg'] = 'rid error';
        
            return $data;
        }
        
        $rid = 'I_' . $rid;
        
        $data['code'] = 0;
        $data['msg'] = 'Not finished yet';
        
        global $sysSession;
        $data = dbGetOne('`LM_read_video_log`', '`course_id`', sprintf("`course_id` = '%s' AND `username` = '%s' AND `rid` = '%s' AND action_id = 'ended'", $sysSession->course_id, $sysSession->username, $rid));
        
        $data['code'] = 1;
        $data['msg'] = 'done';
        
        return $data;
    }

    /*
     * 寫入觀看lcms影片的行為事件（僅紀錄 快轉、重播、暫停、觀看結束）
     */
    function setReadLcmsVideoLog($msg = '') {
        
        if ($msg === '') {
            $data['code'] = -1;
            $data['msg'] = 'msg error';
        
            return $data;
        
        // 解密    
        } else {
            $key = 'readlcmsvideolog';
            $iv = 'fXyFiQCfgiKcyuVNCGoILQ==';

            while (strlen($key) < 16) {
                $key = $key . "\0";
            }

            if (strlen($iv) != strlen(base64_encode(mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND)))) {
                exit();
            }

            $iv_base64_decode = base64_decode($iv);
            $plaintext = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($msg), MCRYPT_MODE_CBC, $iv_base64_decode);
            
            // 僅取{}之前的字串
            preg_match('/{.*}/', $plaintext, $matches);
            $plaintext = $matches[0];
            
            // json_decode後為物件
            $msg = json_decode($plaintext);
            
            // 物件轉陣列
            $msg = (array) $msg;
        
            // 處理後驗證
            if (!(is_array($msg)) || (is_array($msg) === TRUE && count($msg) === 0)) {
                $data['code'] = -3;
                $data['msg'] = 'msg error';

                return $data;
            }
            
            if (!($msg['action_type'] === 'read_video' && (in_array($msg['action_id'], array('seekbar', 'reload', 'pause', 'ended', 'play-1st', 'play', 'do-ivq'))))) {
                $data['code'] = -2;
                $data['msg'] = 'decrypt data error';

                return $data;
            }
        }
        
        global $sysSession, $sysConn;
        
//        $cid = $sysSession->course_id;
        $cid = (int)(trim(sysNewDecode($msg['encid'])));
        if ($cid === '' || isset($cid) === FALSE) {
            $data['code'] = -4;
            $data['msg'] = 'cid error';
        
            return $data;
        }
        
        $username = $sysSession->username;
        
//        echo '<pre>';
//        var_dump($cid);
//        var_dump($username);
//        var_dump($msg);
//        echo '</pre>';
        
        $rid = $msg['rid'];
        if ($rid === '' || isset($rid) === FALSE) {
            $data['code'] = -5;
            $data['msg'] = 'rid error';
        
            return $data;
        }
//        $rid = 'I_' . $rid;
        
        
        $action_id = $msg['action_id'];
        if (!(in_array($action_id, array('seekbar', 'reload', 'pause', 'ended', 'play', 'play-1st', 'do-ivq')))) {
            $data['code'] = -6;
            $data['msg'] = 'action error (' . $action_id . ')';
        
            return $data;
        }
        
        $start_time = $this->get_second_to_his($msg['start_time']);
        $end_time = $this->get_second_to_his($msg['end_time']);
        
        $title = addslashes($msg['title']);
        $url = $msg['url'];
        
        $target_type = $msg['target_type'];
        if (!(in_array($target_type, array('course', 'unit', 'asset')))) {
            $data['code'] = -7;
            $data['msg'] = 'atarget_type error (' . $target_type . ')';
        
            return $data;
        }
        
        $target_id = $msg['target_id'];
        $session_id = $msg['session_id'];
        $system_ip = $msg['server_ip'];
        $from_ip = $msg['client_ip'];
        $duration = $this->get_second_to_his($msg['duration']);
        
//        global $sysConn;
//        $sysConn->debug = true;
        
        // 新增事件（放棄紀錄 begin_time, begin_time_ms, over_time, over_time_ms，好像沒什麼意義）
        dbNew('LM_read_video_log',
            'course_id, username, start_time, end_time, title, url, activity_id, target_type, target_id, action_id, session_id, system_ip, from_ip, create_time, duration', 
            sprintf("'%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', NOW(), '%s'", $cid, $username, $start_time, $end_time, $title, $url, $rid, $target_type, $target_id, ($action_id === 'ended') ? 'play' : $action_id, $session_id, $system_ip, $from_ip, $duration)
        );
        
        $data = array();
        if($sysConn->Affected_Rows() === false) {
            $data['code'] = 0;
            $data['msg'] = 'nothing';
        } else {
            $data['code'] = 1;
            $data['msg'] = 'success';
        }
        
        if ($action_id === 'ended') {
            // 新增觀看結束事件
            dbNew('LM_read_video_log',
                'course_id, username, start_time, end_time, title, url, activity_id, target_type, target_id, action_id, session_id, system_ip, from_ip, create_time', 
                sprintf("'%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', NOW()", $cid, $username, $end_time, $end_time, $title, $url, $rid, $target_type, $target_id, $action_id, $session_id, $system_ip, $from_ip)
            );
        }
        
        // 記錄熱區
        
        return $data;
    }

    /**
     * 解開編碼過的 URL
     *
     * @param string $url  編碼過的 url
     * @param bool $last 是否是本程式最後一次使用 (要關閉解碼器)
     * @return string 解碼過的 url
     */
    function decrypt_url($url, $last=false)
    {
            global $urlchars;
            static $decDev, $key, $iv_size, $first;

            $skey = md5(sysTicketSeed . $_COOKIE['idx']);
            $enc = explode('@', $url, 2);

            $base = trim($enc[0]);
            
            // 資料夾部分，不需要再解碼
            if ($base !== '') {
                $base = trim(sysNewDecode($enc[0], $skey));
                $base = substr($base, 0, -1);
                $base = $base . '/';
            }

            $enc[1] = trim($enc[1]);
            $href = trim(sysNewDecode($enc[1], $skey));
            if ($href === FALSE) {
                die('incorrect url.');
            }
            
            return $base . $href;
    }
    
    function setReading($type, $period, $ticket, $enCid, $beginTime, $title, $enUrl, $activeId) {
        
//        echo '<pre>';
//        var_dump($ticket, $enCid, $beginTime, $title, $enUrl, $activeId);
//        echo '</pre>';
        
        $data = array();
        
        // 驗證IDX
        $session = dbGetOne('WM_session', 'count(idx)', "idx = '{$ticket}'");
//        echo '<pre>';
//        var_dump((int)$session);
//        echo '</pre>';

        if ((int)$session === 0) {
            $data['code'] = -1;
            $data['msg'] = 'ticket error';
        
            return $data;
        } 

        // 驗證課程編號
        $cid = trim(sysNewDecode($enCid));
//        echo '<pre>';
//        var_dump($cid);
//        echo '</pre>';
        
//        echo '<pre>';
//        var_dump(preg_match('/^[\d]{8}$/', $cid));
//        echo '</pre>';
        if (preg_match('/^[\d]{8}$/', $cid) === 0) {
            $data['code'] = -2;
            $data['msg'] = 'cid is error';
        
            return $data;
        }
        
        // 驗證時間
//        echo '<pre>';
//        var_dump($beginTime);
//        var_dump(strtotime($beginTime));
//        var_dump(time());
//        var_dump(date('Y-m-d H:i:s'), time());
//        var_dump(abs(strtotime($beginTime) - time()));
//        echo '</pre>';
        
        // clinet端與server端時間差不得超過3分鐘，判定為偷改時間
//        if (abs(strtotime($beginTime) - time()) >= 181) {
//            $data['code'] = -3;
//            $data['msg'] = 'begin time is fake';
//        
//            return $data;
//        }
        
        
        // 驗證資源URL
        $url = $this->decrypt_url($enUrl, true);
        
        global $sysSession;
        switch ($type) {
            case 'start':
                dbNew('WM_record_reading', 'course_id, username, begin_time, over_time, title, url, activity_id',
                    sprintf('%08d, "%s", "%s", "%s", "%s", "%s", "%s"',
                        $cid,
                        $sysSession->username,
                        $beginTime,
                        date('Y-m-d H:i:s', strtotime($beginTime) + 3),
                        $title,
                        $url,
                        $activeId
                    )
                );
                break;
            
            case 'end':
                $period = $period / 1000;
                
                // 判斷是否讀太久
//                echo '<pre>';
//                var_dump(pathNodeTimeLonglimit + 1);
//                var_dump($beginTime);
//                var_dump(strtotime($beginTime));
//                var_dump(date('Y-m-d H:i:s', time()));
//                var_dump(time());
//                echo '</pre>';
                if (time() - strtotime($beginTime) > (pathNodeTimeLonglimit + 1)) {
                    $data['code'] = -99;
                    $data['msg'] = '閱讀時間過長，超過系統預期閱讀長度（' . pathNodeTimeLonglimit . '秒）';
                    $data['data'] = 'reading time exceeds standard value';

                    return $data;
                }
                
                // 判斷是否存在此idx或離站太久，皆不採計
                list($chance) = dbGetStSr('WM_session', 'chance', "idx = '" . htmlspecialchars($_COOKIE['idx']) . "'", ADODB_FETCH_NUM);
                if ((int)$chance >= 5 && (int)$chance <= 119) {
                    $data['code'] = -98;
                    $data['msg'] = '閱讀離線過久';
                    $data['data'] = 'offline too long, but within standard value(chance: ' . $chance . ')';

                    return $data;
                }
                
                if ((int)$chance >= 120) {
                    $data['code'] = -3;
                    $data['msg'] = '閱讀離線過久，超過標準值';
                    $data['data'] = 'offline too long, exceeds standard value(chance: ' . $chance . ')';

                    return $data;
                }
                
                if (isset($chance) === FALSE) {
                    $data['code'] = -4;
                    $data['msg'] = '此idx不存在';
                    $data['data'] = 'maybe ghost';

                    return $data;
                }
                
//                // 嚴謹政策：每次只加週期秒數
//                dbSet('WM_record_reading', 
//                    "over_time = FROM_UNIXTIME(UNIX_TIMESTAMP(over_time) + {$period})",
//                    sprintf("course_id = %d AND username = '%s' AND begin_time = '%s' AND activity_id = '%s'", $cid, $sysSession->username, $beginTime, $activeId));
                    
//                global $sysConn;
//                $sysConn->debug = true;
                // 寬鬆政策：結束時間是當下時間
                // 限制：最後一次 overtime 距今不能超過3分鐘
                // SELECT over_time, UNIX_TIMESTAMP(over_time), now(), UNIX_TIMESTAMP(now()),UNIX_TIMESTAMP(now())- UNIX_TIMESTAMP(over_time) FROM `WM_record_reading` WHERE `username` = 'teach' AND UNIX_TIMESTAMP(now())- UNIX_TIMESTAMP(over_time)<=3*60
                global $sysConn;
                dbSet('WM_record_reading', 
                    'over_time = NOW()',
                    sprintf("course_id = %d AND username = '%s' AND begin_time = '%s' AND activity_id = '%s' AND (UNIX_TIMESTAMP(now())- UNIX_TIMESTAMP(over_time)) <= (%d * 60)", $cid, $sysSession->username, $beginTime, $activeId, 3));
                
//                echo '<pre>';
//                var_dump('更新筆數', $sysConn->Affected_Rows());
//                echo '</pre>';
                if ($sysConn->Affected_Rows() === 0) {
                    
                    dbNew('WM_record_reading', 'course_id, username, begin_time, over_time, title, url, activity_id',
                        sprintf('%08d, "%s", "%s", "%s", "%s", "%s", "%s"',
                            $cid,
                            $sysSession->username,
                            $beginTime,
                            date('Y-m-d H:i:s', strtotime($beginTime) + 3),
                            $title,
                            $url,
                            $activeId
                        )
                    );
                    
//                    echo '<pre>';
//                    var_dump('沒有更新閱讀結束時間');
//                    echo '</pre>';
                    $data['code'] = -97;
                    $data['msg'] = '閱讀離線過久.';
                    $data['data'] = 'offline too long.';

                    return $data;
                }                
                break;
        }
        
        $data['code'] = 1;
        $data['msg'] = 'success';
        $data['data'] = date('Y-m-d H:i:s', time());
        
        return $data;
    }
    
    // 秒數轉時分秒（含小數點）
    function get_second_to_his($s)
    {
        $float = '';
        if (preg_match('/(\.[\d]*)/', $s, $matches)) {
            $float = $matches[1];
        }
        
        return str_pad(floor(($s % 86400) / 3600), 2, '0', STR_PAD_LEFT) . ':' . str_pad(floor((($s % 86400) % 3600) / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad(floor((($s % 86400) % 3600) % 60), 2, '0', STR_PAD_LEFT) . $float;
    }

    /**
     * 取得本門課QTI三合一試卷資料
     * @param  [int] $course_id 課程編號
     * @param  [string] $type  questionnaire|homework|exam
     * @return [array]         
     */
    function getQTITestData($course_id, $type, $where='') {
        if (empty($course_id) === true) {
            die('error: no parameter');
        }

        if (in_array($type, array('questionnaire', 'homework', 'exam')) === false) {
            die('error: no parameter');
        }

        $data = dbGetAll(
            '`WM_qti_' . $type . '_test`',
            '*',
            sprintf("`course_id`=%d %s", $course_id, (empty($where)?'':'and '.$where)).' order by exam_id desc',
            ADODB_FETCH_ASSOC
        );
        
        return $data;
    }

    /**
     * 取得該門課IRS問卷的列表資料
     * @param  int $course_id 課程編號
     * @return array
     */
    function getIRSquestionnaireData($course_id,$type){
        // IRS問卷名稱的識別字串
        $where = "type=5";
        return $this->getQTITestData($course_id, $type, $where);
    }
}