<?php
   /**************************************************************************************************
    *                                                                                                 *
    *		Wisdom Master 5(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
    *                                                                                                 *
    *		Programmer: spring
    *       SA        : saly                                                                         *
    *		Creation  : 2014/10/13                                                                      *
    *		work for  : 課程公告
    *		work on   : Apache 1.3.41, MySQL 5.1.59 , PHP 4.4.9                                          *
    *                                                                                                 *
    **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lang/forum.php');
    require_once(sysDocumentRoot . '/lang/mooc_forum.php');

    $sysSession->cur_func = '900200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

    // 取公告版編號
	$bid = dbGetOne('`WM_term_course`', 'bulletin', '`course_id` = ' . $sysSession->course_id);

    $sysSession->post_no = '';
	// 如果用 alias link，抓取各項參數
	if (ereg('^(50[0-9]),([0-9]{10}),([0-9]+),([a-z_]+)\.php$', basename($_SERVER['PHP_SELF']), $reg)){
		if ($reg[2] != $bid) die('Error Board id: '.$reg[2]);
		$sysSession->page_no = intval($reg[3]);
		$user_sort = $reg[4];
	}

    // 如果板號不對，則停止
	if (!ereg('^[0-9]{10}$', $bid)) {
		wmSysLog($sysSession->cur_func, $sysSession->class_id , $bid , 1, 'auto', $_SERVER['PHP_SELF'], 'Error Board id');
		die('Error Board id: '.$bid);
	}

    // 教師權限功能開關
    $manageEnable = ($sysSession->q_right==1)?true:false;
    $smarty->assign('manageEnable', $manageEnable);

    $smarty->assign('anntMsg', array(
        'cid'           => $sysSession->course_id,
        'bid'           => $bid,
        'title'         => $sysSession->board_name,
        'subscribe'     => $MSG['subscribe'][$sysSession->lang],
        'unsubscribe'   => $MSG['unsubscribe'][$sysSession->lang],
        'post'          => $MSG['post'][$sysSession->lang],
        'manage'        => $MSG['forum_manage'][$sysSession->lang],
        'attach'        => $MSG['forum_attachment'][$sysSession->lang],
        'successful'    => $MSG['successful'][$sysSession->lang],
        'failed'    => $MSG['failed'][$sysSession->lang]
    ));

    // 是否已訂閱
    list($myorder) = dbGetStSr('WM_bbs_order','count(*)',"board_id={$bid} and username='{$sysSession->username}'", ADODB_FETCH_NUM);
    $smarty->assign('mySubscribe', ($myorder==0)? $MSG['subscribe'][$sysSession->lang] : $MSG['unsubscribe'][$sysSession->lang]);

    // 'write.php?bTicket=' + bTicket  張貼
    $bticket = md5(sysTicketSeed . 'Board' . $_COOKIE['idx'] . $bid);
    // 'edit.php?bTicket=' + bTicket  編輯
    $ticket = md5(sysTicketSeed . 'read' . $_COOKIE['idx'] . $bid);

    $smarty->assign('bTicket', $bticket);
    $smarty->assign('ticket', $ticket);

    // 訂閱功能顯示與否 ， 如果有開自動轉寄功能 ，則不顯示 ，WM_bbs_boards
    $swiEmail = dbGetOne('WM_bbs_boards','`switch`',"board_id={$bid}", ADODB_FETCH_NUM);
    $subscribeEnable = (strpos($swiEmail, 'mailfollow') === false) ? true : false;
    $smarty->assign('subscribeEnable', $subscribeEnable);

    // 是否顯示 more 按鈕，目前根據管理及訂閱作判斷，日後如有新增功能需再修改
    $smarty->assign('moreDisplay', $subscribeEnable||$manageEnable);

    $smarty->assign('msg', $MSG);

    // Header
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('course_announcement.tpl');
    // Footer
    $smarty->display('common/tiny_footer.tpl');