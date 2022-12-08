<?php
    /**
     * @todo
       *     JavaScript 檢查輸入的資料
       *     新增校門
     *     語系分離
     **/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/sch_manage.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lang/mooc.php');
    
    $sysSession->cur_func='100300500';
    $sysSession->restore();
    if (!aclVerifyPermission(100300500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

    }
    
    /**
     * getTheme()
     *     取得系統所有的佈景
     * @return array 佈景
     **/
    function getTheme() {
        $theme = array();
        $dp = opendir(sysDocumentRoot . '/theme/');
        while ( ($entry = readdir($dp)) !== false ) {
            if ( !ereg("(^\.+)|(^CVS$)", $entry) ) {
                if (is_dir(sysDocumentRoot . '/theme/' . $entry)) $theme[$entry] = $entry;
            }
        }
        closedir($dp);
        return $theme;
    }
    
    /**
     * 1. 檢查車票是否正確
       * 2. 檢查車票的種類，是新增還是修改
     **/
    $actType         = '';
    $title           = '';
    $isSingle        = '';
    $location        = 'sch_list.php';
    $_POST['ticket'] = trim($_POST['ticket']);
    $_POST['sid']    = intval($_POST['sid']);
    $_POST['shost']  = trim($_POST['shost']);

    // 新增學校
    $ticket = md5($sysSession->ticket . 'Create' . $sysSession->username);
    if ($_POST['ticket'] == $ticket) {
        $actType = 'Create';
        $title = $MSG['btn_create_school'][$sysSession->lang];
    }

    // 修改學校
    $ticket = md5($sysSession->ticket . 'Edit' . $sysSession->username . $_POST['sid'] . $_POST['shost']);
    if ($_POST['ticket'] == $ticket) {
        $actType = 'Edit';
        $title   = $MSG['tabs_modify_school'][$sysSession->lang];

        $RS = dbGetStSr('`WM_school` w  NATURAL LEFT JOIN `CO_school` c', '*', "w.school_id='{$_POST['sid']}' and w.school_host='{$_POST['shost']}'", ADODB_FETCH_ASSOC);
        if (!$RS) {
            $errMsg = $sysConn->ErrorMsg();
            wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], $errMsg);
            die($errMsg);
        }
    }

    // 單一修改學校
    $ticket = md5('Single' . $sysSession->ticket . 'Edit' . $sysSession->username . $_POST['sid'] . $_POST['shost']);
    if ($_POST['ticket'] == $ticket) {
        $actType  = 'Edit';
        $isSingle = 'Single';
        $location = 'sch_single.php';
        $title    = $MSG['tabs_modify_school'][$sysSession->lang];

        $RS = dbGetStSr('`WM_school` w  NATURAL LEFT JOIN `CO_school` c','*', "w.school_id='{$_POST['sid']}' and w.school_host='{$_POST['shost']}'", ADODB_FETCH_ASSOC);
        if (!$RS) {
            $errMsg = $sysConn->ErrorMsg();
            wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 2, 'manager', $_SERVER['PHP_SELF'], $errMsg);
            die($errMsg);
        }
    }

    $ticket = md5('reCreate' . $sysSession->ticket .  $sysSession->username . $_POST['sid'] . $_POST['shost']);
    if ($_POST['ticket'] == $ticket) {
        $actType = 'reCreate';
        $title   = $MSG['btn_create_school'][$sysSession->lang];
    }

    $ticket = md5('reEdit' . $sysSession->ticket .  $sysSession->username . $_POST['sid'] . $_POST['shost']);
    if ($_POST['ticket'] == $ticket) {
        $actType = 'reEdit';
        $title   = $MSG['tabs_modify_school'][$sysSession->lang];
    }


    $ticket = md5('Single' . 'reCreate' . $sysSession->ticket .  $sysSession->username . $_POST['sid'] . $_POST['shost']);
    if ($_POST['ticket'] == $ticket) {
        $actType  = 'reCreate';
        $isSingle = 'Single';
        $location = 'sch_single.php';
        $title = $MSG['btn_create_school'][$sysSession->lang];
    }

    $ticket = md5('Single' . 'reEdit' . $sysSession->ticket .  $sysSession->username . $_POST['sid'] . $_POST['shost']);
    if ($_POST['ticket'] == $ticket) {
        $actType  = 'reEdit';
        $isSingle = 'Single';
        $location = 'sch_single.php';
        $title = $MSG['tabs_modify_school'][$sysSession->lang];
    }

    /*
    if ($actType == '') {
        die($MSG['access_deny'][$sysSession->lang]);
    }
    */

    // 取得系統所有的佈景
    $theme = getTheme();
    // 設定車票
    // add by jeff: temp remove for testing UI
    // setTicket();

    $sysMailRule = sysMailRule;
    $js = <<< BOF
    /**
     * checkData()
     *     check input data
     *
     * @return
     **/
    function checkData() {
        var obj = document.getElementById("actForm");
        var txt = "";
        var re  = /\s+/g;
        var em  = {$sysMailRule};

        if (obj != null) {
            txt = obj.schname.value.replace(re, '');
            if (!txt.length) {
                alert("{$MSG['msg_need_sch_name'][$sysSession->lang]}");
                obj.schname.value = txt;
                obj.schname.focus();
                return false;
            }

            txt = obj.serhost.value.replace(re, '');
            if (!txt.length) {
                alert("{$MSG['msg_need_domain'][$sysSession->lang]}");
                obj.serhost.value = txt;
                obj.serhost.focus();
                return false;
            }

            txt = obj.school_mail.value;
            if (txt.length > 0) {
                if (txt.search(em) == -1) {
                    alert("{$MSG['error_school_mail'][$sysSession->lang]}");
                    obj.school_mail.focus();
                    return false;
                }
            }

            txt = obj.courseQuota.value.replace(re, '');
            if (txt.length > 0) {
                em = /^[0-9]+$/ig;
                if (txt.match(em) == null) {
                    alert("{$MSG['msg_only_number'][$sysSession->lang]}");
                    obj.courseQuota.select();
                    obj.courseQuota.focus();
                    return false;
                }
            }

            txt = obj.doorQuota.value.replace(re, '');
            if (txt.length > 0) {
                em = /^[0-9]+$/ig;
                if (txt.match(em) == null) {
                    alert("{$MSG['msg_only_number'][$sysSession->lang]}");
                    obj.doorQuota.select();
                    obj.doorQuota.focus();
                    return false;
                }
            }
                                        
                        // 當有勾選fb註冊，則需要輸入fb帳密
                        if ($("input[name='fbregist']").attr('checked') === 'checked' && ($("input[name='FB_id']").val() === '' || $("input[name='FB_secret']").val() === '')) {
                            alert("{$MSG['msg_facebook_info_dtl'][$sysSession->lang]}");
                            if ($("input[name='FB_id']").val() === '') {
                                obj.FB_id.select();
                                obj.FB_id.focus();
                                return false;
                            }
                            if ($("input[name='FB_secret']").val() === '') {
                                obj.FB_secret.select();
                                obj.FB_secret.focus();
                                return false;
                            }
                        }

            txt = obj.courseQuota.value.replace(re, '');
            if (!txt.length) {
                alert("{$MSG['msg_need_course_quota'][$sysSession->lang]}");
                obj.courseQuota.value = txt;
                obj.courseQuota.focus();
                return false;
            }

            txt = obj.doorQuota.value.replace(re, '');
            if (!txt.length) {
                alert("{$MSG['msg_need_door_quota'][$sysSession->lang]}");
                obj.doorQuota.value = txt;
                obj.doorQuota.focus();
                return false;
            }
                                
            return true;
        }
        return false;
    }

    /**
     * guestNum()
     *     show or hidden limit online guest number
     *
     * @param string val : allow or deny guest login
     * @return
     **/
    function guestNum(val) {
        var obj = document.getElementById("guestLimit");
        if (obj != null) {
            if (val == 'Y') {
                obj.style.visibility = "visible";
            } else {
                obj.style.visibility = "hidden";
            }
        }
    }

    function cleanHistory() {
        window.location.replace("{$location}");
    }

    function switchFB(val) {
        if (val == 'N') {
            $('#canRegFacebook').hide();
                    document.getElementById("fbregist").checked = false;
                    chkFacebookInfo(true);
        }else{
            $('#canRegFacebook').show();
        }
    }
                
        function chkFacebookInfo(obj) {
            if (obj.checked === true) {
                $('#fbInfo').find('span').show();
            } else {
                $('#fbInfo').find('span').hide();
            }
    }
BOF;

    $lang = array(
            ''       =>$MSG['please_select'][$sysSession->lang],
            'Big5'       =>$MSG['lang_big5'][$sysSession->lang],
            'en'         =>$MSG['lang_en'][$sysSession->lang],
            'GB2312'     =>$MSG['lang_gb'][$sysSession->lang],
            'EUC-JP'     =>$MSG['lang_jp'][$sysSession->lang],
            'user_define'=>$MSG['lang_user'][$sysSession->lang]
        );
    removeUnAvailableChars($lang);

    $allow = array(
        'Y' => $MSG['status_allow'][$sysSession->lang],
        'N' => $MSG['status_deny'][$sysSession->lang]
    );

    $reg_allow = array(
        'Y' => $MSG['status_allow'][$sysSession->lang],
        'N' => $MSG['status_deny'][$sysSession->lang],
        'C' => $MSG['reg_check'][$sysSession->lang]
    );

    $require = array(
        'noncheck' => $MSG['cs_allow'][$sysSession->lang],
        'check'    => $MSG['cs_check'][$sysSession->lang],
        'admonly'  => $MSG['cs_deny'][$sysSession->lang]
    );
    
    $share = array(
        'FB'      => '/theme/default/learn_mooc/co_icon_fb_1.png',
        'PLURK'   => '/theme/default/learn_mooc/co_icon_plurk_1.png',
        'TWITTER' => '/theme/default/learn_mooc/co_icon_twitter_1.png',
        'LINE'    => '/theme/default/learn_mooc/co_icon_line_1.png',
        'WECHAT'  => '/theme/default/learn_mooc/co_icon_wchat_1.png'
    );
    
    // 如果有設定 FB 常數值才顯示
    $register = array(
        'GENERAL'   =>  $MSG['free_registion'][$sysSession->lang],
        'FB'        =>  array(
            'name'      => $MSG['use_fb_reg'][$sysSession->lang],
            'id'        => '',
            'secret'    => ''
           )
    );
    
    // 我的課程呈現模式
    $mycourse_views = array(
        'T' => $MSG['item_mycourse_view1'][$sysSession->lang],
        'G' => $MSG['item_mycourse_view2'][$sysSession->lang]
    );

    // array(型態, 長度, 名稱, id, value, default value, extra, 說明);
    $school = array(
         0 => array('text'  ,   20, $MSG['item_school_name'][$sysSession->lang], 'schname'        , ''        , '', ''                              , $MSG['msg_school_name'][$sysSession->lang]),
         1 => array('text'  ,   20, 'Domain Name'                              , 'serhost'        , ''        , '', ''                              , $MSG['msg_school_name'][$sysSession->lang]),
         2 => array('text'  ,   40, $MSG['school_mail'][$sysSession->lang]     , 'school_mail'    , ''        , '', ''                              , $MSG['msg_school_mail'][$sysSession->lang]),
//         3 => array('text'  ,   20, $MSG['school_academic'][$sysSession->lang]    , 'manager'        , ''        , '', ''                              , '&nbsp;'),
         4 => array('select',    0, $MSG['item_theme'][$sysSession->lang]      , 'theme'          , $theme    , '', ''                              , '&nbsp;'),
         5 => array('select',    0, $MSG['item_language'][$sysSession->lang]   , 'lang'           , $lang     , '', ''                              , '&nbsp;'),
         6 => array('radio' ,    0, $MSG['item_guest'][$sysSession->lang]      , 'allow_guest'    , $allow    , '', 'onclick="guestNum(this.value)"', '&nbsp;'),
         7 => array('text'  ,    6, $MSG['item_guest_limit'][$sysSession->lang], 'guestLimit'     , ''        , '', 'id="guestLimit" maxlength="9"' , $MSG['msg_guest_limit'][$sysSession->lang]),
         8 => array('radio' ,    0, $MSG['item_multi_login'][$sysSession->lang], 'multi_login'    , $allow    , '', ''                              , '&nbsp;'),
         9 => array('radio' ,    0, $MSG['item_register'][$sysSession->lang]   , 'canReg'         , $reg_allow, '', 'onclick="switchFB(this.value);"'                              , '&nbsp;'),
         17 => array('text'  ,   10, $MSG['item_facebook_info'][$sysSession->lang]      , 'facebookInfo'    , ''        , '', 'maxlength="10"'                , $MSG['msg_facebook_info'][$sysSession->lang]),
         16 => array('radio' ,   0, $MSG['item_mycourse_view'][$sysSession->lang] , 'mycourse_view', $mycourse_views, '', ''                              , '&nbsp;'),
    // 10 => array('radio' ,    0, $MSG['item_require'][$sysSession->lang]    , 'instructRequire', $require  , '', ''                              , '&nbsp;'),
        11 => array('text'  ,   10, $MSG['item_quota'][$sysSession->lang]      , 'courseQuota'    , ''        , '', 'maxlength="10"'                , $MSG['msg_school_name'][$sysSession->lang]),
        12 => array('text'  ,   10, $MSG['item_door_quota'][$sysSession->lang] , 'doorQuota'      , ''        , '', 'maxlength="10"'                , $MSG['msg_school_name'][$sysSession->lang]),
        14 => array('file'  ,   10, 'LOGO'                                     , 'logo'           , ''         , '', 'maxlength="10"'               , $MSG['msg_img_limit_logo'][$sysSession->lang] , '', ''),
        13 => array('file'    , 10, 'ICON'                                     , 'icon'            , ''         , '', 'maxlength="10"'              , $MSG['msg_img_limit_icon'][$sysSession->lang] , '', 'block-hd'),
        15 => array('checkbox', 10, $MSG['mooc_share'][$sysSession->lang]      , 'share'           , $share     , '', ''                            , '&nbsp;'                                      , '', 'block-hd')
    );

    $sid   = '';
    $shost = '';

    if (($actType == 'reCreate') || ($actType == 'reEdit')) {
        $school[0][4]  = trim($_POST['schname']);
        $school[1][4]  = trim($_POST['serhost']);
        $school[2][4]  = trim($_POST['school_mail']);
        $school[4][5]  = trim($_POST['theme']);
        $school[5][5]  = trim($_POST['lang']);
        $school[6][5]  = trim($_POST['allow_guest']);
        $school[7][4]  = trim($_POST['guestLimit']);
        $school[8][5]  = trim($_POST['multi_login']);
        $school[9][5]  = trim($_POST['canReg']);
        $school[17][5]  = trim($_POST['facebookInfo']);
        $facebookInfo = array(
            'id' => $_POST['FB_id'],
            'secret' => $_POST['FB_secret']
        );
        $school[10][5] = trim($_POST['instructRequire']);
        $school[11][4] = trim($_POST['courseQuota']);
        $school[12][4] = trim($_POST['doorQuota']);
        $FBSetting = array(
            'canReg' => ($_POST['fbregist']=='Y')?'Y':'N'
        );
        $school[16][5]  = trim($_POST['mycourse_view']);
    }

    switch ($actType) {
        case 'Create':
            $school[2][4]  = 'webmaster@'.$_SERVER['HTTP_HOST'];
            $school[4][5]  = 'default';
            $school[5][5]  = 'Big5';
            $school[6][5]  = 'Y';
            $school[7][4]  = '0';
            $school[8][5]  = 'Y';
            $school[9][5]  = 'Y';
            $school[10][5] = 'check';
            $school[11][4] = '102400';
            $school[12][4] = '102400';
            $school[16][5]  = 'T';
            break;

        case 'Add':
            break;

        case 'Edit':
            $sid   = $_POST['sid'];
            $shost = $_POST['shost'];

            $school[0][4]  = $RS['school_name'];
            $school[1][4]  = $RS['school_host'];
            $school[2][4]  = $RS['school_mail'];
            $school[4][5]  = $RS['theme'];
            $school[5][5]  = $RS['language'];
            $school[6][5]  = $RS['guest'];
            $school[7][4]  = $RS['guestLimit'];
            $school[8][5]  = $RS['multi_login'];
            $school[9][5]  = $RS['canReg'];
            $school[17][5]  = $RS['facebookInfo'];
            $facebookInfo = array(
                 'id' => $RS['canReg_fb_id'],
                 'secret' => $RS['canReg_fb_secret']
            );
            $school[10][5] = $RS['instructRequire'];
            $school[11][4] = ($RS['courseQuota'] / 1024);
            $school[12][4] = ($RS['quota_limit'] / 1024);
            $school[15][5]  = $RS['social_share'];
            $FBSetting = array(
                 'canReg' => ($RS['canReg_ext']=='FB')?'Y':'N'
            );
            $school[16][5]  = empty($RS['mycourse_view'])?'T':$RS['mycourse_view'];
            break;

        case 'reCreate':
            $actType = 'Create';
            break;

        case 'reEdit':
            $actType = 'Edit';
            $sid     = $_POST['sid'];
            $shost   = $_POST['shost'];
            break;

        default:
            die($MSG['access_deny'][$sysSession->lang]);
    }

    // 開始呈現 HTML
    showXHTML_head_B($MSG['html_title_modify'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('include', "/lib/jquery/jquery.min.js");
    showXHTML_script('inline', $js);
    showXHTML_head_E('');
    showXHTML_body_B('');
        $ary = array();
        $ary[] = array($title, 'tabsTag');
        // $colspan = 'colspan="2"';
        echo '<div align="center">';
        showXHTML_tabFrame_B($ary, 1, 'actForm', 'ListTable', 'method="post" enctype="multipart/form-data" action="sch_save.php" style="display: inline;" onsubmit="return checkData()"');
            showXHTML_table_B('width="790" border="0" cellspacing="1" cellpadding="3" id="MySet" class="cssTable"');
                        foreach ($school as $key => $val) {
                            if (empty($val[0])) continue;
                            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                            $extra  = ($val[0] != 'radio') ? 'class="cssInput" ' : '';
                            $extra .= $val[6];

                            if ($val[1] > 0) $extra = 'size="' . $val[1] . '" ' . $extra;

                            showXHTML_tr_B($col);
                                showXHTML_td('align="right" valign="top" nowrap', $val[2]);
                                showXHTML_td_B('');
                                    switch($val[3]) {
                                        case 'logo':
                                            echo '<div><img src="/base/'.$sid.'/door/tpl/logo.png?'.time().'" alt="'.$val[3].'" height="50px" ></div><BR />';
                                            showXHTML_input($val[0], $val[3], $val[4], $val[5], 'style="width:400px;"');
                                            break;
                                        case 'icon':
                                            echo '<div style="float:left; padding-top:5px"><img src="/base/'.$sid.'/door/tpl/icon.ico?'.time().'" / alt="'.$val[3].'" ></div>&nbsp;&nbsp;';
                                            showXHTML_input($val[0], $val[3], $val[4], $val[5], 'style="width:400px;"');
                                            break;
                                        case 'share':
                                            foreach($val[4] as $k => $v) {
                                                showXHTML_input($val[0], $val[3]."[]", $k, preg_match("/".$k."/i", trim($val[5])), $extra);
                                                echo '<img src="'.$v.'?" / alt="'.$k.'" >';
                                            }
                                            break;
                                        case 'canReg':
                                            showXHTML_input($val[0], $val[3], $val[4], $val[5], $extra);
                                            // 取得FB
                                            echo '<div id="canRegFacebook" style="display:'.(($val[5]=='N')?'none':'block').'">';
                                                                        echo '<input type="checkbox" name="fbregist" value="Y" '.(($FBSetting['canReg']=='Y')?' checked':'').' onClick="chkFacebookInfo(this);" />'.$MSG['use_fb_reg'][$sysSession->lang];
                                            break;
                                                                    
                                        case 'facebookInfo':
                                                                        if ($FBSetting['canReg'] === 'Y') {
                                                                            $facebookInfoRequired = '';
                                                                        } else {
                                                                            $facebookInfoRequired = 'display: none;';
                                                                        }
                                                                        // facebook 帳號密碼
                                                                        echo '<span id="fbInfo" style=""><span style="color:#FF0000; ' . $facebookInfoRequired . '">*</span>ID:';
                                                                        showXHTML_input('text', "FB_id", $facebookInfo['id'], '', 'style="width:120px;"');
                                                                        echo '<br><span style="color:#FF0000; ' . $facebookInfoRequired . '">*</span>Secret:';
                                                                        showXHTML_input('text', "FB_secret", $facebookInfo['secret'], '', 'style="width:230px;"');
                                                                        echo '<a href="/theme/default/learn_mooc/FB APP setting document.pdf" target=_blank><img border="0" src="/theme/default/learn_mooc/help.gif?"></a></span></div>';
                                            break;
                                                                    
                                        default:
                                            showXHTML_input($val[0], $val[3], $val[4], $val[5], $extra);
                                            break;
                                    }
                                    
                                    if (($val[3] == 'courseQuota') || ($val[3] == 'doorQuota')) 
                                    {
                                        showXHTML_td_E('MB');
                                    } else {
                                        showXHTML_td_E('');
                                    }
                                showXHTML_td('', $val[7]);
                            showXHTML_tr_E('');
                        }

                        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        showXHTML_tr_B($col);
                            showXHTML_td_B('colspan="3" align="center"');
                                $ticket = md5($isSingle . $actType . $sysSession->ticket .  $sysSession->username . $sid . $shost);
                                showXHTML_input('hidden', 'sid', $sid, '', '');
                                showXHTML_input('hidden', 'shost', $shost, '', '');
                                showXHTML_input('hidden', 'ticket', $ticket, '', '');
                                showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="cleanHistory()"');
                                showXHTML_input('submit', '', $MSG['btn_save'][$sysSession->lang], '', 'class="cssBtn"');
                            showXHTML_td_E('');
                        showXHTML_tr_E('');
            showXHTML_table_E();
        showXHTML_tabFrame_E();
        echo '</div>';
    showXHTML_body_E('');
?>
