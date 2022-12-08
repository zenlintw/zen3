<?php
    /**************************************************************************************************
     *                                                                                                *
     *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
     *                                                                                                *
     *      Programmer: Wiseguy Liang                                                                 *
     *      Creation  : 2003/09/23                                                                    *
     *      work for  :                                                                               *
     *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
     *                                                                                                *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/learn_path.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');

    $last_activity = dbGetOne('WM_record_reading', 'activity_id', 'course_id=' . $sysSession->course_id . ' and username="' . $sysSession->username . '" order by over_time desc', ADODB_FETCH_NUM);

    // assign
    $smarty->assign('post', $_POST);
    $smarty->assign('MSG', $MSG);
    $smarty->assign('sysSession', $sysSession);
    $smarty->assign('last_activity', $last_activity);
    $smarty->assign('themePath', sprintf('/theme/%s/learn/', $sysSession->theme));
    $smarty->assign('pathSerial', $pathSerial);
    $smarty->assign('sLang', $sLang);
    $smarty->assign('justPreview', $justPreview);
    $smarty->assign('pTicket', $_COOKIE["idx"]);
    $smarty->assign('begin_time', date('Y-m-d H:i:s'));
    $smarty->assign('enc_course_id', sysNewEncode($sysSession->course_id));
    $smarty->assign('read_key', md5(time()));
    $smarty->assign('pathNodeTimeShortlimit', pathNodeTimeShortlimit);

    $smarty->display('phone/learn/pathtree.tpl');
