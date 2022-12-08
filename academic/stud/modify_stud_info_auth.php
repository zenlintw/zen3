<?php
    /**************************************************************************************************
    *                                                                                                 *
    *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
    *                                                                                                 *
    *		Programmer: Amm Lee                                                                       *
    *		Creation  : 2003/09/23                                                                    *
    *		work for  :  基本資料                                                                     *
    *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
    *       $Id: modify_stud_info.php,v 1.1 2010/02/24 02:38:44 saly Exp $                                                                                           *
    **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/lib/common.php');

    $sysSession->cur_func = '400500100';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    $username = $_POST['user'] ? preg_replace('/[^\w.-]+/', '', $_POST['user']) :
                ($_GET['username'] ? preg_replace('/[^\w.-]+/', '', $_GET['username']) : $sysSession->username);
    //$enc = @mcrypt_encrypt(MCRYPT_DES, sysTicketSeed . $_COOKIE['idx'], $username, 'ecb');
    $enc = sysEncode($username);
    $enc_username = base64_encode(urlencode($enc));

    list($last_login,$login_times,$begin_time,$expire_time) = dbGetStSr('WM_sch4user', 'last_login,login_times,begin_time,expire_time', 'school_id=' . $sysSession->school_id . " and username='". $username . "'", ADODB_FETCH_NUM);
    $RS = dbGetStSr('CO_mooc_account', '*', "username='" . $username . "'", ADODB_FETCH_ASSOC);

    showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="stud_info" class="cssTable"');
        //  個人(begin)
        showXHTML_tr_B('class="cssTrHead"');
                showXHTML_td_B('colspan="5"');

                //  管理者 - 班次管理 - 成員管理 - 個人資料 - 顯示所在班級
                if (! empty($ACADEMIC_CLASS_MEMBER)){
                    $class_id = max(1000000, intval($_POST['class_id']));
                    if ($class_id == 1000000){
                        $csname = $sysSession->school_name;
                    }else{
                        list($caption) = dbGetStSr('WM_class_main', 'caption', ' class_id = ' .  $class_id, ADODB_FETCH_NUM);
                        $lang   = getCaption($caption);
                        $csname = $lang[$sysSession->lang];
                    }
                    echo '<font color="#FF0000">', $MSG['title121'][$sysSession->lang], $csname . '</font><br>';
               }
                // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
                $realname = checkRealname($RS['first_name'],$RS['last_name']);
                echo $realname;
                showXHTML_td_E();
        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title34'][$sysSession->lang]);
                showXHTML_td('align="left" nowrap="noWrap"'  , $RS['username']);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title82'][$sysSession->lang]);
                showXHTML_td('align="left" nowrap="noWrap"'  , $last_login);
                showXHTML_td_B('align="center" rowspan="5" width="100"');
                    echo '<div align="center" valign="middle" style="width:110px; height:120px; overflow:hidden" id="divPic">',
                         '<img src="showpic.php?a=' , $enc_username , '" type="image/jpeg" id="MyPic" name="MyPic" borer="0" align="absmiddle" onload="picReSize()" loop="0">',
                         '</div>';
                showXHTML_td_E();
        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title33'][$sysSession->lang]);
                showXHTML_td('align="left" nowrap="noWrap"'  , $realname);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title83'][$sysSession->lang]);
                showXHTML_td('align="left" nowrap="noWrap"'  , $login_times);
        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
               showXHTML_td('align="center" nowrap="noWrap"', $MSG['title55'][$sysSession->lang]);
                $gender = $RS['gender'] == 'M' ? '/theme/default/academic/male.gif' : '/theme/default/academic/female.gif';
                showXHTML_td('align="left" nowrap="noWrap"', '<img src="' . $gender . '" type="image/jpeg" align="absmiddle">');
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title84'][$sysSession->lang]);
                $RS2 = dbGetCourses('count(*)', $username, $sysRoles['auditor']|$sysRoles['student']);
                showXHTML_td('align="left" nowrap="noWrap"', $RS2);
        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title85'][$sysSession->lang]);
                showXHTML_td('align="left" nowrap="noWrap"', $RS['birthday']);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title86'][$sysSession->lang]);
                $RS2 = dbGetCourses('count(*)', $username, $sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']);
                showXHTML_td('align="left" nowrap="noWrap"', $RS2);
        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title87'][$sysSession->lang]);
                showXHTML_td('align="left" nowrap="noWrap"', $RS['personal_id']);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title89'][$sysSession->lang]);
                showXHTML_td('align="left" nowrap="noWrap"', $RS['title']);
        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title132'][$sysSession->lang]);
                $bt = intval($begin_time);
                $et = intval($expire_time);
                $temp = $MSG['from2'][$sysSession->lang] . (empty($bt)?$MSG['now'][$sysSession->lang]:$begin_time) . '<br />' .
                        $MSG['to2'][$sysSession->lang] . (empty($et)?$MSG['forever'][$sysSession->lang]:$expire_time);
                showXHTML_td('align="left" nowrap="noWrap" colspan="4"', $temp);
        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title90'][$sysSession->lang]);
                showXHTML_td('align="left" nowrap="noWrap" colspan="4"', $RS['department']);
        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title126'][$sysSession->lang]);
                showXHTML_td('align="left" nowrap="noWrap" colspan="4"', $RS['company']);
        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title127'][$sysSession->lang]);
                showXHTML_td_B('align="left" colspan="4"');
                   echo '<a href="mailto:' . $RS['email'] . '">' . $RS['email'] . '</a>';
                showXHTML_td_E();

        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title128'][$sysSession->lang]);
                showXHTML_td('align="left" nowrap="noWrap" colspan="4"', $RS['cell_phone']);
        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title91'][$sysSession->lang]);
                showXHTML_td_B('align="left" nowrap="noWrap" colspan="4"');
                    echo $MSG['title97'][$sysSession->lang] . $RS['office_tel'] . '&nbsp;&nbsp;&nbsp; ' . $MSG['title96'][$sysSession->lang] . $RS['home_tel'];
                showXHTML_td_E();
        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title92'][$sysSession->lang]);
                showXHTML_td_B('align="left" nowrap="noWrap" colspan="4"');
                    echo $MSG['title97'][$sysSession->lang] . $RS['office_fax'] . '&nbsp;&nbsp;&nbsp; ' . $MSG['title96'][$sysSession->lang] . $RS['home_fax'];
                showXHTML_td_E();
        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title93'][$sysSession->lang]);
                showXHTML_td_B('align="left" nowrap="noWrap" colspan="4"');
                    echo '<a href="' . $RS['homepage'] . '">' . $RS['homepage']  . '</a>';
                showXHTML_td_E();
        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title94'][$sysSession->lang]);
                showXHTML_td('align="left" nowrap="noWrap" colspan="4"', $RS['home_address']);
        showXHTML_tr_E();

        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title95'][$sysSession->lang]);
                showXHTML_td('align="left" nowrap="noWrap" colspan="4"', $RS['office_address']);
        showXHTML_tr_E();
        // 個人 (end)

        //  回人員列表 (begin)
        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        showXHTML_tr_B($col);
            showXHTML_td_B('colspan="5" align="center"');
                if (! empty($ACADEMIC_CLASS_MEMBER)){		 //  管理者 - 班級管理 - 成員管理 - 個人資料
                    showXHTML_input('button', '', $MSG['title98'][$sysSession->lang], '', 'class="cssBtn" onclick="go_list(' . intval($_POST['class_id']) . ');"');
                    showXHTML_input('button', '', $MSG['title131'][$sysSession->lang], '', 'class="cssBtn" onclick="modify(\'' . $username . '\');"');
                }else if (! empty($DIRECT_MEMBER)) {		//  導師 - 成員管理 - 成員資料  - 個人資料
                    $btn = $MSG['btn_return_direct_member'][$sysSession->lang];
                    showXHTML_input('button', '', $btn, '', 'class="cssBtn" onclick="go_list();"');
                }else if (! empty($ENROLL_MEMBER)){    //  導師 - 學員修課管理 - 修課指派 - 挑選人員 - 個人資料
                    $btn = $MSG['btn_direct_enroll_member'][$sysSession->lang];
                    showXHTML_input('button', '', $btn, '', 'class="cssBtn" onclick="go_list();"');
                }else if (! empty($ACADEMIC_DELETE_MEMBER)){		// 管理者 - 帳號管理 - 刪除帳號 - 刪除不規則帳號 - 個人資料
                    $btn = $MSG['title138'][$sysSession->lang];
                    showXHTML_input('button', '', $btn, '', 'class="cssBtn" onclick="go_list();"');
                }else if (! empty($ACADEMIC_AUTH_MEMBER)){		//  管理者 - 帳號管理 - 審核帳號 - 個人資料
                    $btn = $MSG['btn_authorisation_member'][$sysSession->lang];
                    showXHTML_input('button', '', $btn, '', 'class="cssBtn" onclick="go_list();"');
                }else if (! empty($ACADEMIC_MODIFY_MEMBER)){		//  管理者 - 帳號管理 - 查詢人員 - 個人資料
                    $btn = $MSG['title130'][$sysSession->lang];
                    showXHTML_input('button', '', $btn, '', 'class="cssBtn" onclick="go_list();"');
                    showXHTML_input('button', '', $MSG['title131'][$sysSession->lang], '', 'class="cssBtn" onclick="modify(\'' . $username . '\');"');
                }
            showXHTML_td_E();
        showXHTML_tr_E();
        //  回人員列表 (end)
    showXHTML_table_E();