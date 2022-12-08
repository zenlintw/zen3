<?php
    /**
     *  帳號是否超過使用期限
     *  1. 是否到了可用的日期
     *  2. 是否過期
     * @author  Amm Lee
     * @version $Id: use_date.php,v 1.1 2010/02/24 02:40:20 saly Exp $
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/pw_query.php');
    require_once(sysDocumentRoot . '/sys/syslib.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    $sysSession->cur_func = '400400100';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    $username = preg_replace('/[^\w.-]+/', '', $_SERVER['argv'][0]);
    $RS = dbGetStSr('WM_sch4user', 'begin_time,expire_time', "school_id={$sysSession->school_id} AND username='{$username}'", ADODB_FETCH_ASSOC);

        showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" align="center"');
            showXHTML_tr_B();
                showXHTML_td('width="100%" align="center" valign="middle" nowrap class="bgColor01"', '&nbsp;');
            showXHTML_tr_E();
            showXHTML_tr_B();
                showXHTML_td_B('width="100%" height="200" align="center" valign="middle" nowrap style="color : #FF0000"');
                    $begin_array = explode('-',$RS['begin_time']);
                    echo $MSG['deadline'][$sysSession->lang] ,
                         $MSG['A.D'][$sysSession->lang] ,  $begin_array[0] , $MSG['year'][$sysSession->lang] ,
                         $begin_array[1] , $MSG['month'][$sysSession->lang] ,
                         $begin_array[2] , $MSG['day'][$sysSession->lang];

                    $expire_array = explode('-',$RS['expire_time']);

                    echo ' ~ ' , $MSG['A.D'][$sysSession->lang] ,  $expire_array[0] , $MSG['year'][$sysSession->lang] ,
                         $expire_array[1] , $MSG['month'][$sysSession->lang] ,
                         $expire_array[2] , $MSG['day'][$sysSession->lang] ,
                         $MSG['deadline1'][$sysSession->lang];

                showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_tr_B();
                showXHTML_td_B('colspan="4" align="center" valign="middle" nowrap class="bgColor02"');
                    // #47276 Chrome 過期帳號通知頁面的回首頁按鈕 css怪異
                    showXHTML_input('button' , '', $MSG['home'][$sysSession->lang], '', 'onclick="location.replace(\'/\');"');
                showXHTML_td_E('');
            showXHTML_tr_E();
        showXHTML_table_E();