<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    
    // 是否有老師或開課者的身份
    if (!aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) && !aclCheckRole($sysSession->username, $sysRoles['course_opener'], $sysSession->school_id)) {
        header("LOCATION: /");
        exit;
    }
    
    if (isset($_GET['cid']) && $_GET['cid'] !== '') {
        // 取得點擊的課程名稱
        $course_id = intval($_GET['cid']);
        if (aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $course_id)) {
            $rs = dbGetOne('WM_term_course', 'caption', sprintf('course_id=%d',$course_id), ADODB_FETCH_ASSOC);
            if ($rs) {
                $cp =  unserialize($rs);

                $sysSession->course_id = $course_id;
                $sysSession->course_name = $cp[$sysSession->lang];
                $sysSession->env='teach';
                $sysSession->restore();
                dbSet('WM_session', sprintf('course_id=%d, course_name="%s"',$course_id,$cp[$sysSession->lang]), "idx='{$_COOKIE['idx']}'");
            }
            header('Location: /teach/index.php');
        } else {
            echo 'You doesn\'t teach this course.<br/>Click <a href="/teach/select.php">here</a> to select again.';
            die();
        }
    } else {
        // 取第一門課，設定為sysSession的course_id
        if ($rs = dbGetCourses('M.course_id, C.caption', $sysSession->username, $sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']))
        {
            while($fields = $rs->FetchRow()){
                $course_id = $fields['course_id'];
                $cp = unserialize($fields['caption']);
                break;
            }
        }
        $sysSession->course_id = $course_id;
        $sysSession->course_name = $cp[$sysSession->lang];
        $sysSession->env='teach';
        $sysSession->restore();
        dbSet('WM_session', sprintf('course_id=%d, course_name="%s"',$course_id,$cp[$sysSession->lang]), "idx='{$_COOKIE['idx']}'");
        header('Location: /teach/index.php');
    }