<?php
    /**
     * 教師交流
     *
     * @since   2004/10/19
     * @author  KuoYang Tsao
     * @version $Id: index_teacher.php,v 1.1 2010/02/24 02:39:09 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/acade_news.php');
    require_once(sysDocumentRoot . '/lib/lib_acade_news.php');
    require_once(sysDocumentRoot . '/lib/lib_forum.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    
    $sysSession->cur_func='300100600';
    $sysSession->restore();
    if (!aclVerifyPermission(300100600, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }

    // 是否為管理者
    $is_manager  = aclCheckRole($sysSession->username, $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $sysSession->school_id);
    // 是否為教師
    $is_teacher  = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);
    // 是否為導師
    $is_director = aclCheckRole($sysSession->username, $sysRoles['director']);

    if(!($is_manager || $is_teacher || $is_director )) { // 非管理者且非教師且非導師
        include_once(sysDocumentRoot . '/lib/interface.php');
        showXHTML_script('inline', "alert('{$MSG['msg_accessdeny'][$sysSession->lang]}');");
        die();
        exit;
    }

    if(!dbGetNewsBoard($result, 'teacher')) {
        echo 'System Error!';
        wmSysLog($sysSession->cur_func, $sysSession->course_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], 'System Error!');
        exit();
    }

    $sysSession->board_id        = $result['board_id'];
    $sysSession->news_board      = 0;        // 非含時間(開啟及關閉)欄位類型之討論版
    $sysSession->board_readonly  = 0;
    $sysSession->board_qonly     = 0;
    $sysSession->page_no         = '';
    $sysSession->post_no         = '';
    $sysSession->q_page_no       = '';
    $sysSession->q_post_no       = '';
    $sysSession->board_ownerid   = $sysSession->school_id;
    $sysSession->board_ownername = $sysSession->school_name;
    // 是否具刊登權限(含修改, 刪除)
    $sysSession->q_right         = $is_manager; // ChkRight($result['board_id']);
    $sysSession->b_right         = ($is_manager || $is_teacher || $is_director); //$sysSession->q_right;

    list ($bname, $default_order) = dbGetStSr('WM_bbs_boards', 'bname, default_order', 'board_id = ' . $sysSession->board_id, ADODB_FETCH_NUM);
    $sysSession->sortby	  = $default_order;
	$sysSession->q_sortby = $default_order;
    $bname                = unserialize($bname);
    dbSet('WM_session', 'board_name="'. addslashes($bname[$sysSession->lang]) .'",q_path=""', "idx='{$_COOKIE['idx']}'");
    
    // 回存 SESSION
    $sysSession->restore();
    
    // 清除 Cookie 所存搜尋條件
    ClearForumCookie();

    header('Location:/forum/index.php');
?>
