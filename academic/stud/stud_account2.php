<?php
    /**
     * 新增不規則帳號或匯入帳號
     * $Id: stud_account2.php,v 1.1 2010/02/24 02:38:44 saly Exp $
     **/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/lang/stud_account.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    $actType = '';

    if (isset($_POST['ck_begin_date'])) {
        $begin_time = "'{$_POST['begin_date']}'";
    }
    else {
        $begin_time = 'NULL';
    }

    if (isset($_POST['ck_end_date'])) {
        $expire_time = "'{$_POST['end_date']}'";
    }
    else {
        $expire_time = 'NULL';
    }

    $sysCL = array('Big5'=>'zh-tw','en'=>'en','GB2312'=>'zh-cn');
    $ACCEPT_LANGUAGE = $sysCL[$sysSession->lang];
    if (empty($ACCEPT_LANGUAGE)) $ACCEPT_LANGUAGE = 'zh-tw';

    // 新增連續帳號
    $ticket = md5($sysSession->username . $sysSession->ticket. 'Auto' . $sysSession->school_id);
    if (trim($_POST['ticket']) == $ticket) {
        $actType      = 'Auto';
        $actMsg       = $MSG['create_serial_account'][$sysSession->lang];
        $back_href    = 'stud_account.php?msgtp=1';
        $act_back_Msg = $MSG['title74'][$sysSession->lang];
        $showpages    = 1;
        $function_id  = '0400300300';
    }

    // 新增不規則帳號
    $ticket = md5('AddManual' . $sysSession->ticket . $sysSession->school_id . $sysSession->username);
    if (trim($_POST['ticket']) == $ticket) {
        $actType      = 'Manual';
        $actMsg       = $MSG['create_discrete_account'][$sysSession->lang];
        $back_href    = 'stud_account.php?msgtp=2';
        $act_back_Msg = $MSG['title75'][$sysSession->lang];
        $showpages    = 2;
        $function_id  = '0400300100';
    }

    // 匯入帳號
    $ticket = md5($sysSession->ticket . 'AddImport' . $sysSession->school_id . $sysSession->username);
    if (trim($_POST['ticket']) == $ticket) {
        $actType      = 'Import';
        $actMsg       = $MSG['import_account'][$sysSession->lang];
        $act_back_Msg = $MSG['title76'][$sysSession->lang];
        $function_id  = '0400300500';
        die($actType);
    }

    if (empty($actType)) {
        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '拒絕存取!');
        die($MSG['access_deny'][$sysSession->lang]);
    }

    $sysSession->cur_func = $function_id;
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }

    /**
     * 安全性檢查
     *     1. 身份的檢查
     *     2. 權限的檢查
     *     3. .....
     **/

    $user = array();
    // 新增連續帳號
    if ($actType == 'Auto') {
        $header    = preg_replace('/[^A-Za-z0-9-_.]/', '', $_POST['header']);
        $tail      = preg_replace('/[^A-Za-z0-9-_.]/', '', $_POST['tail']);
        $first     = min(99999,max(0,intval($_POST['first'])));
        $last      = min(99999,max(0,intval($_POST['last'])));
        $len       = min(5,max(1,intval($_POST['len'])));
        $fmt       = "{$header}%0{$len}d{$tail}";
        $resString = trim($_POST['resString']);
        $resArray  = explode(',',$resString);
        for($i = $first; $i <= $last; $i++) {
            $ac = sprintf($fmt, $i);
            $passwd = Passwd();
            $user[] = array($ac, $passwd);

            // 寄個人的帳號及密碼 給使用者

            $m_user .= $ac . ',' . $passwd . "\t";
        }
        $m_user = substr($m_user,0,-1);
    }

    // 新增不規則帳號
    if ($actType == 'Manual') {
        if (preg_match_all('/^\s*([\w-]+)\s*(,\s*([^\x00-\x1F\x7F\xFF]*)\s*)?$/m', $_POST['userlist'], $temps, PREG_SET_ORDER))
        {
            $ic = 0;

            foreach($temps as $temp)
            {
                $passwd = isset($temp[3]) ? $temp[3] : Passwd();
                $user[] = array($temp[1], $passwd);
                $m_user .= $temp[1] . ',' . $passwd . "\t";
                $resArray[$ic++] = checkUsername($temp[1]);
            }
            $m_user = rtrim($m_user);
            unset($temps);
        }
        else
            die('incorrect format.');
    }

    // 匯入帳號
    if ($actType == 'Import') {
        echo '<pre>';
        print_r($_POST);
        echo '</pre>';
        die();
    }

    // mail rule
    $mail_rule = sysMailRule;

    // 開始新增帳號
    $js = <<< BOF

    var html_lang = "{$ACCEPT_LANGUAGE}";

    /* 如果是 Mozilla/Firefox 則加上 outerHTML/innerText 的支援 */
    if (navigator.userAgent.indexOf(' Gecko/') != -1)
    {
        HTMLElement.prototype.__defineSetter__('outerHTML', function(s){
           var range = this.ownerDocument.createRange();
           range.setStartBefore(this);
           var fragment = range.createContextualFragment(s);
           this.parentNode.replaceChild(fragment, this);
        });

        HTMLElement.prototype.__defineGetter__('outerHTML', function() {
           return new XMLSerializer().serializeToString(this);
        });

        HTMLElement.prototype.__defineGetter__('innerText', function() {
          return this.innerHTML.replace(/<[^>]+>/g, '');
        });
    }

    function listPrint() {
        var nodes = document.getElementsByTagName("input");
        var obj1  = document.getElementById("admin");
        var obj2  = document.getElementById("btn");
        obj1.style.display = "none";
        obj2.style.display = "none";
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].style.visibility = "hidden";
        }
        window.print();
        obj1.style.display = "block";
        obj2.style.display = "block";
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].style.visibility = "visible";
        }
    }

    function mailData(){
        var ml    = '';
        var ss    = '/,$/';
        var obj   = document.getElementById('send_user');
        var nodes = obj.getElementsByTagName('input');
        var col   = '', tmp = '';
        var re    = {$mail_rule};
        var email_count = 0;

        for(var i=0; i<nodes.length; i++){
            if (nodes.item(i).type == 'text'){
                if (nodes.item(i).value != ''){
                    if (!re.test(nodes.item(i).value)) {
                        alert("{$MSG['js_msg12'][$sysSession->lang]}");
                        return false;
                    }
                }
            }
        }


        /*  寄信給管理者備存 (begin)   */

        tmp = '<html>'+
              '<head>'+
              '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >'+
              '<meta http-equiv="Content-Language" content="' + html_lang + '" > '+
              '<title>' + "{$actMsg}" + '</title>'+
              '<style type="text/css">'+
              '.cssTrHead {' +
              '  font-size: 12px; ' +
              ' line-height: 16px; '+
              ' text-decoration: none; ' +
              ' letter-spacing: 2px; '+
              ' color: #000000; ' +
              ' background-color: #CCCCE6; ' +
              ' font-family: Tahoma, "Times New Roman", Times, serif;' +
              ' }' +
              ' .cssTrEvn { '+
              '      font-size: 12px;'+
              '      line-height: 16px;'+
              '      text-decoration: none;'+
              '      letter-spacing: 2px;'+
              '      color: #000000;'+
              '      background-color: #FFFFFF;'+
              '      font-family: Tahoma, "Times New Roman", Times, serif;'+
              '}'+
              '  .cssTrOdd {'+
              '      font-size: 12px;'+
              '      line-height: 16px;'+
              '      text-decoration: none;'+
              '      letter-spacing: 2px;'+
              '      color: #000000;'+
              '      background-color: #EAEAF4;'+
              '      font-family: Tahoma, "Times New Roman", Times, serif;'+
              '  }'+
              '.font01 {' +
              'font-size: 12px;' +
              'line-height: 16px;' +
              'color: #000000; ' +
              'text-decoration: none ;'+
              'letter-spacing: 2px;'+
              '}' +
              '</style>'+
              '</head>' +
              '<body  >'+
              obj.innerHTML +
              '</' + 'body>' +
              '</html>';
       document.mailFm.mail_txt.value = stringToBase64(encodeURIComponent(tmp));

       /*  寄信給管理者備存 (end)  */

       obj = document.getElementById('btn_submit');
       obj.disabled = true;

       return true;
   }

BOF;


showXHTML_head_B($MSG['create_account'][$sysSession->lang]);
showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
showXHTML_script('inline', $js);
showXHTML_script('include', '/lib/base64.js');
showXHTML_head_E();
showXHTML_body_B();
$arry[] = array($actMsg, 'addTable1');
    showXHTML_table_B(' border="0" cellspacing="0" cellpadding="0"');
        showXHTML_tr_B();
            showXHTML_td_B();
                showXHTML_tabs($arry, 1);
            showXHTML_td_E();
        showXHTML_tr_E();
        showXHTML_tr_B();
            showXHTML_form_B('action="send_register_mail.php" method="post" enctype="multipart/form-data" style="display:none" onsubmit="return mailData()"', 'mailFm');
                   showXHTML_td_B('valign="top" ');
                    $ticket2 = md5($sysSession->ticket . 'sendMail' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
                       showXHTML_input('hidden', 'ticket'   , $ticket2, '', '');
                       showXHTML_input('hidden', 'send_data', $m_user, '', '');
                       showXHTML_input('hidden', 'msgtp'    , $showpages, '', '');
                       showXHTML_input('hidden', 'mail_txt' , '', '', '');
                    echo '<span id="send_user">';
                       showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
                           showXHTML_tr_B('class="cssTrHead"');
                               showXHTML_td('', $MSG['account'][$sysSession->lang]);
                               showXHTML_td('', $MSG['password'][$sysSession->lang]);
                               showXHTML_td('', $MSG['status'][$sysSession->lang]);
                               showXHTML_td('', $MSG['email'][$sysSession->lang]);
                           showXHTML_tr_E();

                           // 帳號註冊上限：設定可註冊人數
                        if (sysMaxUser > 0)
                        {
                            list($now_maxuser) = dbGetStSr('WM_user_account','count(*)','1', ADODB_FETCH_NUM);
                            if ($now_maxuser >= sysMaxUser)
                            {
                                $canRegisterNum = 0;
                            }else{
                                $canRegisterNum = sysMaxUser - $now_maxuser;
                            }
                            list($admin_email) = dbGetStSr(sysDBname.'.WM_school','school_mail',"school_id='{$sysSession->school_id}'", ADODB_FETCH_NUM);
                            $msg_overMaxUser   = str_replace(array('%max_register_user%', '%admin_email%'),
                                                             array(sysMaxUser, 'mailto:'.$admin_email),
                                                             $MSG['overMaxUser'][$sysSession->lang]);
                        }
                        $overMaxUser = false;   //是否超過可註冊人數

                        $suc = 0;
                        $fau = 0;

                        // 一開始
                        if (!file_exists(sysDocumentRoot . "/base/{$sysSession->school_id}/account_{$time_stamp}.sql")) {
                            $time_stamp = time();
                            $res_no_begin = addUser('','','','','begin',$time_stamp);
                        }
                           for ($i = 0; $i < count($user); $i++) {
                               $col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';

                            if (sysMaxUser > 0)   //有註冊人數限制
                            {
                                --$canRegisterNum;    // 取得目前可註冊人數
                                if ($canRegisterNum < 0)
                                {
                                    $overMaxUser = true;
                                }
                            }

                            if ($overMaxUser)  //已超過註冊上限
                            {
                                $fau++;
                                showXHTML_tr_B('class="' . $col . '"');
                                   showXHTML_td('', $user[$i][0]);
                                      showXHTML_td('colspan="3"', $msg_overMaxUser);
                                   showXHTML_tr_E();
                                   continue;
                            }

                            $data['password'] = md5(trim($user[$i][1]));
                            // 帳號啟用時間、終止時間
                            $data['begin_time'] = $begin_time;
                            $data['expire_time'] = $expire_time;

                            // 塞資料
                            // 如果新增連續帳號那邊的審查結果是0 => 可新增(非保留字、非使用中)
                            if ($resArray[$i] == 0)
                                $res_no_middle = addUser(trim($user[$i][0]), $data,'','', 'middle',$time_stamp);
                            // 審查結果代表此階段的結果
                            $res_no = $resArray[$i];

                            // 最後一筆塞入後，將參數傳過去做ending
                            if ($i == (count($user)-1))
                                $res_no_final = addUser('', '','','', 'final',$time_stamp);

                            if ($res_no <= 0) {
                                $add_cal_user = trim($user[$i][0]);
                                dbNew('WM_cal_setting', 'username, also_show, login_alert', "'{$add_cal_user}', 'person,course,school', 'Y'");
                                $suc++;
                                   $msg = $MSG['add_success'][$sysSession->lang];
                               } else {
                                   $fau++;
                                   if ($res_no == 1) {
                                       $msg = $MSG['system_reserved'][$sysSession->lang] . ',' .  $MSG['add_fail'][$sysSession->lang];
                                   }else if ($res_no == 2){
                                    $msg = $MSG['account_used'][$sysSession->lang] . ',' . $MSG['add_fail'][$sysSession->lang];
                                   }else if ($res_no == 3){
                                    $msg = $MSG['format_not_match'][$sysSession->lang] . ',' . $MSG['add_fail'][$sysSession->lang];
                                   }else if ($res_no == 4){
                                    $msg = $MSG['system_reserved'][$sysSession->lang] . ',' . $MSG['add_fail'][$sysSession->lang];
                                   }

                               }
                               $log_msg .= $user[$i][0] . $msg . '; ';

                            $user_acc = trim($user[$i][0]);
                               showXHTML_tr_B('class="' . $col . '"');
                                   showXHTML_td('', $user[$i][0]);
                                   showXHTML_td('', $user[$i][1]);
                                   showXHTML_td('', $msg);
                                   showXHTML_td_B();
                                       showXHTML_input('text', "email[{$user_acc}]", '', '', 'size="50" class="cssInput"');
                                   showXHTML_td_E();
                               showXHTML_tr_E();
                           }
                        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], $actMsg . '; ' .  $log_msg);
                           $col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
                           showXHTML_tr_B('class="' . $col . '"');
                               showXHTML_td('colspan="2" ', $MSG['success'][$sysSession->lang]);
                               showXHTML_td('colspan="2" ', $suc);
                           showXHTML_tr_E();

                           $col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
                           showXHTML_tr_B('class="' . $col . '"');
                               showXHTML_td('colspan="2" ', $MSG['fail'][$sysSession->lang]);
                               showXHTML_td('colspan="2" ', $fau);
                           showXHTML_tr_E();

                           $col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
                           showXHTML_tr_B('id="admin" class="' . $col . '"');
                               showXHTML_td_B('colspan="4" ');
                                   showXHTML_input('checkbox', 'backup', '1', '', 'id="backup"');
                               showXHTML_td_E($MSG['mail_backup'][$sysSession->lang]);
                           showXHTML_tr_E();

                           $col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
                           showXHTML_tr_B('id="btn" class="' . $col . '"');
                               showXHTML_td_B('colspan="4" ');
                                   showXHTML_input('submit', '', $MSG['mail_student'][$sysSession->lang], '', 'id="btn_submit" class="cssBtn"');
                                   showXHTML_input('button', '', $MSG['print'][$sysSession->lang], '', 'class="cssBtn" onclick="listPrint()"');
                                   showXHTML_input('button', '', $act_back_Msg, '', 'class="cssBtn" onclick="window.location.replace(\'' . $back_href . '\');"');
                               showXHTML_td_E();
                           showXHTML_tr_E();
                       showXHTML_table_E();
                    echo '</span>';
                   showXHTML_td_E();
               showXHTML_form_E();

        showXHTML_tr_E();

    showXHTML_table_E();

showXHTML_body_E();