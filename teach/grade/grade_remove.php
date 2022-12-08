<?php
    /**************************************************************************************************
     *                                                                                                *
     *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
     *                                                                                                *
     *      Programmer: Wiseguy Liang                                                         *
     *      Creation  : 2003/06/06                                                            *
     *      work for  : grade manage                                                          *
     *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
     *                                                                                                *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/teach/grade/grade_recal.php');
    
    $sysSession->cur_func = '1400100300';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }

    if ($_GET['gid'] && ereg('^[0-9]+(,[0-9]+)*$', $_GET['gid'])){
//        $gids = dbGetCol('WM_grade_list', 'grade_id, source', 'grade_id in (' . $_GET['gid'] . ') and source=9');
        
        $rs = dbGetStMr('WM_grade_list', 'grade_id, source, property', 'grade_id in (' . $_GET['gid'] . ')', ADODB_FETCH_ASSOC);
        $gids = array();
        if ($rs) {
            while (!$rs->EOF) {
                // 自訂
                if ($rs->fields['source'] === '9') {
                    $gids[] = $rs->fields['grade_id'];
                // 同儕互評
                } else if ($rs->fields['source'] === '4') {
                    $exam_id = dbGetOne('WM_qti_peer_test', 'exam_id', 'exam_id = ' . $rs->fields['property']);
                    // 如果同儕互評作業不存在了，則可以刪掉
                    if ($exam_id === false) {
                        $gids[] = $rs->fields['grade_id'];
                    }
                }
                
                $rs->MoveNext();
            }
        }
        
        $gs = implode(',', $gids);
        
        dbDel('WM_grade_list', "grade_id in ({$gs})");
        dbDel('WM_grade_item', "grade_id in ({$gs})");
        reCalculateGrades($sysSession->course_id);
        wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], "grade remove {$_GET['gid']}");
    }
    echo <<< EOB
    <script>
        location.replace('grade_maintain.php');
    </script>
EOB;
?>