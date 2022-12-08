<?php
    /**
     * 切換課程與讀取選單共用函數
     *
     * @since   2004/10/21
     * @author  ShenTing Lin
     * @version $Id: goto.php,v 1.1 2010/02/24 02:38:39 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
    define('SYSBAR_MENU' , 'personal');
    define('SYSBAR_LEVEL', 'personal');

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
    require_once(sysDocumentRoot . '/lang/sysbar.php');
    require_once(sysDocumentRoot . '/academic/sysbar/main/sysbar_lib.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    /**
     * 環境字串說明
     *     academic : 管理者
     *     direct   : 導師
     *     teach    : 教師
     *     learn    : 學生
     **/
    // 共用變數
    $xmlDocs = null;

// ============================================================================
    if (!function_exists('getManagerLevel')) {
        /**
         * 取得管理者的權限
         * @param string  $username : 帳號，當帳號空白時，則讀取 $sysSession->username
         * @return level
         *        0: 不具備管理者的權限
         *     2048: 一般管理者
         *     4096: 進階管理者
         *     8192: 最高管理者 (一機只有一人)
         **/
        function getManagerLevel($username) {
            global $sysConn, $sysSession, $sysbarRoles, $sysRoles;

            $username = trim($username);
            if (empty($username)) $username = $sysSession->username;
            if (isset($sysbarRoles['admin'][$username])) return $sysbarRoles['admin'][$username];

            $level = 0;
            // 取得目前這位管理者的權限
            $level = aclCheckRole($username, ($sysRoles['manager']|$sysRoles['administrator']|$sysRoles['root']), $sysSession->school_id, true);
            $sysbarRoles['admin'][$username] = $sysConn->ErrorNo() ? 0 : intval($level);
            return $sysbarRoles['admin'][$username];
        }
    }

    /**
     * 取得導師的權限
     * @param string  $username : 帳號，當帳號空白時，則讀取 $sysSession->username
     * @param integer $caid     : 班級的編號
     * @return level
     *        0: 不具備導師的權限
     *     $sysRoles['assistant'] (64)  : 助教
     *     $sysRoles['director']  (1024): 導師
     **/
    function getDirectorLevel($username, $caid='') {
        global $sysConn, $sysSession, $sysRoles, $sysbarRoles;

        $username = trim($username);
        if (empty($username)) $username = $sysSession->username;
        if (isset($sysbarRoles['director'][$username])) return $sysbarRoles['director'][$username];

        if (empty($caid)) $caid = intval($sysSession->class_id);
        $caid = checkClassID($caid);
        if ($caid === false) return 0;

        $level = aclCheckRole($username, ($sysRoles['director']|$sysRoles['assistant']), $caid, true) &
                 ($sysRoles['director']|$sysRoles['assistant']);
        $sysbarRoles['director'][$username] = $sysConn->ErrorNo() ? 0 : intval($level);
        return $sysbarRoles['director'][$username];
    }

    /**
     * 取得教師的權限
     * @param string  $username : 帳號，當帳號空白時，則讀取 $sysSession->username
     * @param integer $csid     : 課程的編號
     * @return level
     *        0: 不具備教師的權限
     *     $sysRoles['assistant']  (64)  : 助教
     *     $sysRoles['instructor'] (128) : 講師
     *     $sysRoles['teacher']    (512) : 教師
     **/
    function getTeacherLevel($username, $csid='') {
        global $sysConn, $sysSession, $sysRoles, $sysbarRoles;

        $username = trim($username);
        if (empty($username)) $username = $sysSession->username;
        if (isset($sysbarRoles['teacher'][$username])) return $sysbarRoles['teacher'][$username];

        if (empty($csid)) $csid = intval($sysSession->course_id);
        $csid = checkCourseID($csid);
        if ($csid === false) return 0;

        $sl = aclCheckRole($username, ($sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant']), $csid, true);
        $sysbarRoles['teacher'][$username] = intval($sl);
        return $sysbarRoles['teacher'][$username];
    }

    /**
     * 取得學生的權限
     * @param string  $username : 帳號，當帳號空白時，則讀取 $sysSession->username
     * @param integer $csid     : 課程的編號
     * @return level
     *        0: 不具備該門課的權限
     *     $sysRoles['auditor']    (16)  : 旁聽生
     *     $sysRoles['student']    (32)  : 正式生
     *     $sysRoles['assistant']  (64)  : 助教
     *     $sysRoles['instructor'] (128) : 講師
     *     $sysRoles['teacher']    (512) : 教師
     **/
    function getStudentLevel($username, $csid='') {
        global $sysConn, $sysSession, $sysRoles, $sysbarRoles;

        $username = trim($username);
        if (empty($username)) $username = $sysSession->username;
        if (isset($sysbarRoles['student'][$username])) return $sysbarRoles['student'][$username];

        if (empty($csid)) $csid = intval($sysSession->course_id);
        if (intval($csid) == 10000000) {
            $roles = array(
                'senior'         => false, // 學長
                'paterfamilias'  => false, // 家長
                'superintendent' => false, // 長官/督學
                'auditor'        => false, // 旁聽生
                'student'        => false, // 正式生
                'assistant'      => false, // 助教
                'instructor'     => false, // 講師
                'teacher'        => false, // 教師 (通常比講師多具有教材管理編修權)
                'director'       => false, // 導師 (學生人員管理)
            );
            $RS = dbGetStMr('WM_term_major', '`role`', "`username`='{$username}'",ADODB_FETCH_ASSOC);
            if ($RS) {
                while (!$RS->EOF) {
                    $sl = $RS->fields['role'];
                    // $roles['senior']         = (($sl & $sysRoles['senior'])         || $roles['senior']);
                    // $roles['paterfamilias']  = (($sl & $sysRoles['paterfamilias'])  || $roles['paterfamilias']);
                    // $roles['superintendent'] = (($sl & $sysRoles['superintendent']) || $roles['superintendent']);
                    $roles['auditor']        = (($sl & $sysRoles['auditor'])        || $roles['auditor']);
                    $roles['student']        = (($sl & $sysRoles['student'])        || $roles['student']);
                    $roles['assistant']      = (($sl & $sysRoles['assistant'])      || $roles['assistant']);
                    $roles['instructor']     = (($sl & $sysRoles['instructor'])     || $roles['instructor']);
                    $roles['teacher']        = (($sl & $sysRoles['teacher'])        || $roles['teacher']);
                    $roles['director']       = (($sl & $sysRoles['director'])       || $roles['director']);
                    $RS->MoveNext();
                }
            }
            if ($username != 'guest') $roles['senior'] = true;
            $level = 0;
            foreach ($roles as $key => $val) {
                if ($val) $level += $sysRoles[$key];
            }
            return $level;
        } else {
            $csid = checkCourseID($csid);
            if ($csid === false) return 0;
            $level = 0;
            $level += getTeacherLevel($username, $csid);
            list($sl) = dbGetStSr('WM_term_major', '`role`', "`username`='{$username}' AND `course_id`={$csid}",ADODB_FETCH_NUM);
            if ($sysConn->ErrorNo() <= 0) {
                $sl = intval($sl);
                if ($username != 'guest') $level += $sysRoles['senior'];
                if ($sl & $sysRoles['auditor']) $level += $sysRoles['auditor'];
                if ($sl & $sysRoles['student']) $level += $sysRoles['student'];
            }
            $sysbarRoles['student'][$username] = $level;
        }

        return $level;
    }

// ----------------------------------------------------------------------------
    /**
     * 建立空白的 xml doc 物件
     **/
    function newXmlDocs() {
        global $xmlDocs;

        $xmlStrs = '<' . '?xml version="1.0" encoding="UTF-8"?' . '><manifest><items></items></manifest>';
        if(!$xmlDocs = domxml_open_mem($xmlStrs)) {
            $xmlDocs == null;
            return $xmlStrs;
        }
        return '';
    }

    /**
     * 取得此帳號所對應的身份清單
     * @param string $username : 帳號
     * @return array : 身份清單
     **/
    function getRoleListXML($username, $dsid) {
        global $xmlDocs, $envRead, $sysRoles;

        $root = $xmlDocs->document_element();
        $node = $xmlDocs->create_element('roles');
        $username = trim($username);
        $ary = array('learn', 'teach');

        $res = 'false';
        $lres = 'false';
        if (in_array($envRead, $ary)) {
            $dsid = checkCourseID($dsid);
            if ($dsid !== false) {
                $level = getTeacherLevel($username, $dsid);
                $res = ($level > 0) ? 'true' : 'false';
                // 增加學生身分的判斷
                if (aclCheckRole($username, $sysRoles['student'], $dsid) >= 1)
                    $lres = 'true';
            }
        }
        
        $cnode = $xmlDocs->create_element('teach');
        $cnode->set_attribute('have', $res);
        $node->append_child($cnode);

        $cnode = $xmlDocs->create_element('academic');
        $cnode->set_attribute('have', 'false');
        $node->append_child($cnode);

        $cnode = $xmlDocs->create_element('direct');
        $cnode->set_attribute('have', 'false');
        $node->append_child($cnode);
        $root->append_child($node);
        
        $cnode = $xmlDocs->create_element('learn');
        $cnode->set_attribute('have', $lres);
        $node->append_child($cnode);
    }

    /**
     * 讀取 xml
     **/
    function readXML($root, $filename, $env) {
        if ($xmlVars = domxml_open_file($filename)) {
            $items = $xmlVars->get_elements_by_tagname('item');
            if (is_array($items)) {
                $role1 = getManagerLevel($sysSession->username);
                $role2 = getDirectorLevel($sysSession->username);
                $role3 = getTeacherLevel($sysSession->username);
                $role4 = getStudentLevel($sysSession->username);
                $role5 = getStudentLevel($sysSession->username, 10000000); // 用於檢查個人區與校園廣場
                foreach ($items as $item) {
                    $isRemove = true;
                    $roles = intval($item->get_attribute('role'));
                    switch ($env) {
                        case 'academic':
                        case 'ep_academic':
                            $isRemove = !($roles & $role1);
                            break;
                        case 'learn':
                            $isRemove = !(($roles & $role3) || ($roles & $role4));
                            break;
                        case 'teach':
                            $isRemove = !($roles & $role3);
                            break;
                        case 'direct':
                            $isRemove = !($roles & $role2);
                            break;
                        case 'personal':
                        case 'school':
                            $isRemove = !(($roles & $role1) || ($roles & $role2) || ($roles & $role3) || ($roles & $role5) || ($roles & 1));
                            break;
                        default:
                    }
                    if ($isRemove) {
                        $pnode = $item->parent_node();
                        $pnode->remove_child($item);
                    }
                }
            }

            $nodes = $xmlVars->get_elements_by_tagname('items');
            if (count($nodes) == 0) return '';

            $childs = $nodes[0]->child_nodes();
            if (is_array($childs)) {
                foreach ($childs as $node) {
                    $root->append_child($node->clone_node(true));
                }
            }
        }
    }

    /**
     * 將 Node 中的文字做編碼
     * @param object $node
     * @return object
     **/
    function encodeNodeText(&$node, $isQTI=false) {
        global $xmlDocs;
        $nodes = $node->nodeset;
        if (is_array($nodes)) {
            foreach ($nodes as $key => $node) {
                $val = intval($node->node_value());
                if (($val == 1) || ($val == 2)) continue;

                $pnode = $node->parent_node();
                if ($isQTI)
                    $cnode = $xmlDocs->create_text_node(str_replace('+', '%252B', sysEncode($val)));
                else
                    $cnode = $xmlDocs->create_text_node(sysEncode($val));
                $pnode->remove_child($node);
                $pnode->append_child($cnode);
            }
        }
    }

    /**
     * 移除選單中的項目
     * @param $xptr :  PHP 的 xpath 的物件
     * @param $id : 選單的編號
     * @return void
     **/
    function removeSysbarItem($xptr, $id) {
        if (empty($id)) return false;
        $obj = xpath_eval($xptr, '//item[@id="' . $id . '"]');
        $nodes = $obj->nodeset;
        if (is_array($nodes)) {
            foreach ($nodes as $node) {
                $pnode = $node->parent_node();
                $pnode->remove_child($node);
            }
        }
        return true;
    }

    /**
     * 讀取 sysbar 的設定檔
     * 1. system.xml
     * 2. personal.xml
     * 3. course.xml
     **/
    function readSysbar($env, $extra='') {
        global $sysSession, $sysRoles, $xmlDocs, $_SERVER, $config_user;

        $nodes = $xmlDocs->get_elements_by_tagname('items');
        if (count($nodes) == 0) {
            wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,1, $env, $_SERVER['PHP_SELF'], 'Data Error!');
            die('DataError');
            exit;
        }
        $root = $nodes[0];

        switch (trim($env)) {
            case 'academic' :   // 管理者環境
                // 學校 (檢查方式：學校 -> 系統)
                $filename = getSysbarSetFile('academic', 'administrator', true);
                if (!empty($filename) && @file_exists($filename)) {
                    readXML($root, $filename, $env);
                }
                break;
            case 'ep_academic' :   // EP 管理者環境
                // 學校 (檢查方式：學校 -> 系統)
                $filename = getSysbarSetFile('ep_academic', 'administrator', true);
                if (!empty($filename) && @file_exists($filename)) {
                    readXML($root, $filename, $env);
                }
                return $xmlDocs->dump_mem(true);
                break;
            case 'learn'    :   // 教室
                // 課程 (檢查方式：課程 -> 學校 -> 系統)
                $extra = intval($extra);
                if ((10000000 < $extra) && ($extra < 100000000)) {
                    $filename = getSysbarSetFile('learn', 'manager_course', true);
                    if (!empty($filename) && @file_exists($filename)) {
                        readXML($root, $filename, $env);
                    }
                }
                break;
            case 'teach'    :   // 教師辦公室
                $extra = intval($extra);
                if ((10000000 < $extra) && ($extra < 100000000)) {
                    $filename = getSysbarSetFile('teach', 'manager_course', true);
                    if (!empty($filename) && @file_exists($filename)) {
                        readXML($root, $filename, $env);
                    }
                }
                break;
            case 'direct'   :   // 導師辦公室
                $filename = getSysbarSetFile('direct', 'manager_course', true);
                if (!empty($filename) && @file_exists($filename)) {
                    readXML($root, $filename, $env);
                }
                break;
            default:
        }

        // 個人區
        $filename = getSysbarSetFile('personal', 'manager', true);
        if (!empty($filename) && @file_exists($filename)) {
            readXML($root, $filename, 'personal');
        }

        // 系統建議區
        $filename = getSysbarSetFile('school', 'manager', true);
        if (!empty($filename) && @file_exists($filename)) {
            readXML($root, $filename, 'school');
        }

        $xptr = xpath_new_context($xmlDocs);
        // 檢查具不具備進階管理者的身份 (Begin)
        // 檢查身份的順序，由大至小
        $level = getManagerLevel($sysSession->username);
            // 不具最高管理者身份
        if (!($level & $sysRoles['root'])) {
            // 移除系統管理 (id: SYS_01_09_000)
            removeSysbarItem($xptr, 'SYS_01_09_000');
        }
            // 不具進階管理者身份
        if (!($level & $sysRoles['administrator']) && !($level & $sysRoles['root'])) {
            // 移除進階功能 (id: SYS_01_08_000)
            removeSysbarItem($xptr, 'SYS_01_08_000');
        }

        if ((anicam == 'N') && (joinet == 'N') && (breeze == 'N')) {
            // 移除語音設定
            removeSysbarItem($xptr, 'SYS_02_03_007');
        }

        if (joinet == 'N') {
            // 移除joinnet歷史會議錄影列表
            removeSysbarItem($xptr, 'SYS_02_03_008');
            removeSysbarItem($xptr, 'SYS_04_01_007');
        }

        if (breeze == 'N')
        {
            //移除Breeze live錄影
            removeSysbarItem($xptr, 'SYS_02_03_009');
            removeSysbarItem($xptr, 'SYS_04_01_008');
        }
        if (!defined('enableQuickReview') || false == enableQuickReview){
            // 移除學習快通車  
            removeSysbarItem($xptr, 'SYS_06_01_013');
        }
        
        if (!defined('enableLiveService') || false == enableLiveService){
            // 移除直播活動列表
            removeSysbarItem($xptr, 'SYS_04_01_009');
            removeSysbarItem($xptr, 'SYS_02_09_000');
        }

        if (!defined('sysEnableAppISunFuDon') || false == sysEnableAppISunFuDon) {
            removeSysbarItem($xptr, 'SYS_02_10_000');
            removeSysbarItem($xptr, 'SYS_04_01_010');

        }
        // 檢查具不具備進階管理者的身份 (End)

        // 編碼 (Begin)
        // 作業
        $obj = xpath_eval($xptr, '//item/href[@kind=3]/text()');
        encodeNodeText($obj, true);
        // 測驗
        $obj = xpath_eval($xptr, '//item/href[@kind=4]/text()');
        encodeNodeText($obj, true);
        // 問卷
        $obj = xpath_eval($xptr, '//item/href[@kind=5]/text()');
        encodeNodeText($obj, true);
        // 討論版
        $obj = xpath_eval($xptr, '//item/href[@kind=6]/text()');
        encodeNodeText($obj);
        $obj = xpath_eval($xptr, '//item/href[@kind=9]/text()');
        encodeNodeText($obj);
        // 編碼 (End)
        
        $xml = str_replace('/forum/m_board_list.php', '/forum/m_board_list.php?cid=' . $sysSession->course_id, $xmlDocs->dump_mem(true));
        return $xml;
    }
// ============================================================================

    // 檢查必要的變數 (Begin)
        // $envWork : 要切換到哪個環境去
        // $envRead : 要讀取哪個環境的選單
        // $spec_case : 遇到 StudentRole 則 sysbar加上個人區和校園廣場等功能選項，讓他可以回個人區
        // $error_msg : 儲存 StudentRole
    if (!isset($envWork) || !isset($envRead)) die('needVar');   // 必須設定這個變數
    if ($_GET['envWork']    == $envWork) $envWork = '';
    if ($_POST['envWork']   == $envWork) $envWork = '';
    if ($_COOKIE['envWork'] == $envWork) $envWork = '';

    if ($_GET['envRead']    == $envRead) $envRead = '';
    if ($_POST['envRead']   == $envRead) $envRead = '';
    if ($_COOKIE['envRead'] == $envRead) $envRead = '';
    if (empty($envWork) || empty($envRead)) die('needVar');    // 這個變數的值不得為空

    $spec_case = '';

    // 檢查必要的變數 (End)

    // 這邊的判斷可能會因為 PHP 版本的更改而有所變動
    if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
        if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
            wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,2, $envRead, $_SERVER['PHP_SELF'], 'Data Error!');
            die('DataError');
        }
        // 檢查身份 (Begin)
        switch ($envRead) {
            case 'academic' :   // 管理者
            case 'ep_academic' :   // EP 管理者
                $dsid = $sysSession->school_id;
                $level = getManagerLevel($sysSession->username);
                if (!($level & $sysRoles['manager'] ||
                      $level & $sysRoles['administrator'] ||
                      $level & $sysRoles['root']))
                {
                    die('AdminRole');
                }
                break;

            case 'direct'   :   // 導師
                $dsid = trim(getNodeValue($dom, 'class_id'));
                $dsid = checkClassID($dsid);
                if ($dsid === false) die('DirectIDError');
                $level = getDirectorLevel($sysSession->username, $dsid);
                if (!($level & $sysRoles['assistant'] || $level & $sysRoles['director'])) {
                    die('DirectorRole');
                }
                break;

            case 'teach'    :   // 教師
                $dsid = trim(getNodeValue($dom, 'course_id'));
                $dsid = checkCourseID($dsid);
                if ($dsid === false) $dsid = intval($sysSession->course_id);
                $dsid = checkCourseID($dsid);
                if ($dsid === false) die('CourseIDError');
                $level = getTeacherLevel($sysSession->username, $dsid);
                if (!($level & $sysRoles['assistant'] ||
                      $level & $sysRoles['instructor'] ||
                      $level & $sysRoles['teacher']))
                {
                    die('TeacherRole');
                }
                break;

            case 'learn'    :   // 學生
                $dsid = trim(getNodeValue($dom, 'course_id'));
                if (intval($dsid) != 10000000) {
                    $dsid = checkCourseID($dsid);
                    if ($dsid === false) $dsid = intval($sysSession->course_id);
                    $dsid = checkCourseID($dsid);
                    if ($dsid === false) {
                        $dsid = 10000000;
                    } else {
                        $level = getStudentLevel($sysSession->username, $dsid);
                        if (!($level & $sysRoles['auditor'] ||
                              $level & $sysRoles['student'] ||
                              $level & $sysRoles['assistant'] ||
                              $level & $sysRoles['instructor'] ||
                              $level & $sysRoles['teacher']))
                        {
                            // 遇到 StudentRole 則 sysbar加上個人區和校園廣場等功能選項，讓他可以回個人區
                            $spec_case = 'default_menu';
                            $error_msg = 'StudentRole';    // 只要是該門課的老師即可進入
                            $dsid = 10000000;
                            // die('StudentRole');   // 只要是該門課的老師即可進入
                        }
                    }
                }
                break;

            default:
                die('needVar');
        }
        // 檢查身份 (End)

        // 檢查 IP 限制 (Begin)
        $res = checkIPLimit($sysSession->username, $envRead, $dsid);
        switch (intval($res)) {
            case -4 : die('CourseIDError'); break;
            case -3 : die('DirectIDError'); break;
            case -2 : die('SchoolIDError'); break;
            case -1 : die('needVar');       break;
            case  0 : die('IPLimit');       break;
            case  1 : case  2 : break;
            default : die('needVar');
        }
        // 檢查 IP 限制 (End)

        // 檢查並切換課程資料 (Begin)
        $ary = array('learn', 'teach');
        if (in_array($envRead, $ary) && ($dsid > 10000000)) {
            $RS = dbGetStSr('WM_term_course', '`caption`,`st_begin`,`st_end`, `status`', "`course_id`={$dsid} AND `kind`='course'",ADODB_FETCH_ASSOC);
            if (!$RS) die('CourseDelete1');
            if (intval($RS['status']) >= 9) die('CourseDelete');
            $isTeacher = ($level & $sysRoles['assistant'] || $level & $sysRoles['instructor'] || $level & $sysRoles['teacher']);

            if (!$isTeacher)
            {
                $today = date('Y-m-d');
                if ($level & $sysRoles['student'])
                {
                    if ((( $RS['status']   == 1  || $RS['status'] == 3) ||
                         (($RS['status']   == 2  || $RS['status'] == 4) &&
                          ($RS['st_begin'] == '' || $RS['st_begin'] <= $today) &&
                          ($RS['st_end']   == '' || $RS['st_end']   >= $today)
                         )
                        )
                       )
                        $dsid = $dsid;
                    else
                    {
                        $dsid = 10000000;
                        $error_msg = $MSG['tabs_deny_title'][$sysSession->lang];
                    }
                }
                elseif ($level & $sysRoles['auditor'])
                {
                    if ((( $RS['status']   == 1) ||
                         (($RS['status']   == 2) &&
                          ($RS['st_begin'] == '' || $RS['st_begin'] <= $today) &&
                          ($RS['st_end']   == '' || $RS['st_end']   >= $today)
                         )
                        )
                       )
                        $dsid = $dsid;
                    else
                    {
                        $dsid = 10000000;
                        $error_msg = $MSG['tabs_deny_title'][$sysSession->lang];
                    }
                }
                else
                {
                    $dsid = 10000000;
                    $error_msg = $MSG['msg_student_role'][$sysSession->lang];
                }
            }

/*
            if (($envRead == 'learn') && !$isTeacher) {
                if ((intval($RS['status']) < 1) && (intval($RS['status']) > 4)) die('CourseClose');
            }
*/
            $lang   = getCaption($RS['caption']);
            $csname = addslashes($lang[$sysSession->lang]);

            // 設定進入的課程編號
            dbSet('WM_session', "`course_id`={$dsid}, `course_name`='{$csname}'", "`idx`='{$_COOKIE['idx']}'");

            if ($dsid != $sysSession->course_id) {
                // 增加登入次數
                dbSet('WM_term_major', '`login_times`=`login_times`+1, `last_login`=NOW()', "`username`='{$sysSession->username}' and `course_id`={$dsid}");
                dbSet('WM_term_course', '`login_times`=`login_times`+1', "`course_id`={$dsid}");
                
                // 記錄到 log 中(避免次數不一)
                if ($envRead == 'teach') {
                    wmSysLog('2500200200', $dsid, 0, '0', 'teacher', '', 'Goto office course_id=' . $dsid);
                } else {
                    wmSysLog('2500100200', $dsid, 0, '0', 'classroom', '', 'Goto course course_id=' . $dsid);
                }
            }
            // 修改 Session
            $sysSession->course_id = $dsid;
            $sysSession->course_name = $csname;
        }
        // 檢查並切換課程資料 (End)
        // 檢查並切換班及資料 (Begin)
        if ($envRead == 'direct') {
            /*
            $dsid = checkClassID($dsid);
            if ($dsid === false) die('DirectIDError');
            */
            $RS     = dbGetStSr('WM_class_main', 'caption', "class_id={$dsid}",ADODB_FETCH_ASSOC);
            $lang   = GetCaption($RS['caption']);
            $csname = addslashes($lang[$sysSession->lang]);

            // 設定進入的班級編號
            dbSet('WM_session', "class_id={$dsid}, class_name='{$csname}'", "idx='{$_COOKIE['idx']}'");
            // 紀錄
            wmSysLog('2400400100', $dsid, 0,'0', 'director', '', 'Goto direct class_id=' . $dsid);

            if ($sysSession->class_id != $dsid) {
                // 增加登入次數
                dbSet('WM_class_member', 'login_times=login_times+1, last_login=NOW()', "`class_id`={$dsid} AND `username`='{$sysSession->username}'");
            }
            // 修改 Session
            $sysSession->class_id = $dsid;
        }
        // 檢查並切換班及資料 (End)

        // 回存 session 的環境資料
        if ($sysSession->env != $envWork) {
            $sysSession->env = $envWork;
        }
        $sysSession->restore();

        // 清除討論版的 Cookie
        include_once(sysDocumentRoot . '/lib/lib_forum.php');
        ClearForumCookie();

        if (!$getSysbar) die();

        $result = '';
        newXmlDocs();
        getRoleListXML($sysSession->username, $dsid);

        if ($spec_case != ''){
            $envRead = $spec_case;
        }

        // 常數定義檔 - 使用者帳號 (定義有哪些使用者可以使用常數定義檔，如須定義超過一個使用者，請用半形逗號隔開)
        $config_user = explode(',',Access_constant);

        $result = readSysbar($envRead, $dsid);

        if (!empty($result)) {
            header("Content-type: text/xml");
            // 環境變數
            $env_id = "<school_id>{$sysSession->school_id}</school_id><course_id>{$sysSession->course_id}</course_id><class_id>{$sysSession->class_id}</class_id>";
            // 環境名稱
            $env_id .= sprintf(
                '<school_name>%s</school_name><course_name>%s</course_name><class_name>%s</class_name>',
                htmlspecialchars($sysSession->school_name),
                htmlspecialchars($sysSession->course_name),
                htmlspecialchars($sysSession->class_name)
            );
            if ($spec_case != ''){
                /*
                 * 遇到 StudentRole 則 sysbar加上個人區和校園廣場等功能選項，讓他可以回個人區
                 * 多傳個 <error_msg>StudentRole</error_msg>
                 */
                $result = str_replace('<manifest>', "<manifest><ticket>{$ticket}</ticket><dsid>{$dsid}</dsid>{$env_id}<error_msg>{$error_msg}</error_msg>", $result);
            }else{
                $result = str_replace('<manifest>', "<manifest><ticket>{$ticket}</ticket><dsid>{$dsid}</dsid>{$env_id}", $result);
            }
            echo $result;
        }
    }
?>
