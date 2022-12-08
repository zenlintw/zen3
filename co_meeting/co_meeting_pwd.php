<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php';


$js = <<<BOF
    function checkForm()
    {
        if($('#password').val()=='')
        {
            alert('請輸入密碼!!');
            $('#password').focus();
            return false;
        }

        $('#frmSend').submit();
    }

BOF;

showXHTML_head_B($MSG['head'][$sysSession->lang]);
showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
showXHTML_script('include', "/lib/jquery/jquery-1.7.2.min.js");
showXHTML_script('inline', $js);
showXHTML_head_E('');
showXHTML_body_B();


function maskPassword($pwd)
{
    $first = substr($pwd,0,1);
    $last  = substr($pwd,-1,1);
    $len   = strlen($pwd);
    $mask = '';
    for($i=0;$i<($len-2);$i++)
    {
        $mask .= '*';
    }
    return $first . $mask . $last;
}


showXHTML_table_B('align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;width:100%;" ');
showXHTML_tr_B();
showXHTML_td_B();
$ary[] = array("設定寶訊通密碼", 'tabsSet', '');
showXHTML_tabs($ary, 1);
showXHTML_td_E();
showXHTML_tr_E();

showXHTML_form_B('method="POST" action="/co_meeting/module/controller.php"', 'frmSend');
showXHTML_input('hidden','action','setUserPwd','','');
showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');

    $pwd = dbGetOne("CO_meeting_user","password",sprintf("username='%s'",$sysSession->username));
    $col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
    showXHTML_tr_B($col);
        showXHTML_td_B('colspan="2" ');
            echo "目前密碼 : " . maskPassword($pwd);
        showXHTML_td_E();
    showXHTML_tr_E();

    $col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
    showXHTML_tr_B($col);
        showXHTML_td_B();
            echo "新密碼";
        showXHTML_td_E();
        showXHTML_td_B();
            showXHTML_input("text","password","","","class='cssInput' id='password'");
        showXHTML_td_E();
    showXHTML_tr_E();

    $col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
    showXHTML_tr_B($col);
        showXHTML_td_B('colspan="2" align="center"');
            showXHTML_input('button', '', "設定", '', 'class="cssBtn" onclick="checkForm()"');
        showXHTML_td_E();
    showXHTML_tr_E();

showXHTML_table_E();



showXHTML_form_E();
showXHTML_table_E();

showXHTML_body_E();
