<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php';
require_once sysDocumentRoot . '/lib/interface.php';
require_once sysDocumentRoot . '/lang/co_meeting.php';
require_once sysDocumentRoot . '/co_meeting/module/meeting.php';
showXHTML_head_B($MSG['head'][$sysSession->lang]);
showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
showXHTML_script('include', "/lib/jquery/jquery-1.7.2.min.js");
header("Pragma: no-cache");


$js = <<<EOF

function JoinMeeting(enterurl)
{
    $('#loginFm').submit();
}

function pwdSetting()
{
    url = '/co_meeting/co_meeting_pwd.php';
    var pwdWin = window.open(url,'pwdWin',config='height=300,width=400');
    pwdWin.focus();
}

function createFunc()
{
  $('#submitBtn').attr('disabled',true);
  $('#submitBtn').val('建立中..');
  $('#frmSend').submit();
}
EOF;
showXHTML_script('inline', $js);
showXHTML_head_E('');
showXHTML_body_B('style="overflow: hidden;"');


showXHTML_table_B('align="center" border="0" cellpadding="0" cellspacing="0" width="780" style="border-collapse: collapse"');
showXHTML_tr_B();
showXHTML_td_B();
$ary[] = array("影音互動設定", 'tabsSet', '');
showXHTML_tabs($ary, 1);
showXHTML_td_E();
showXHTML_tr_E();
showXHTML_tr_B();
showXHTML_td_B();
showXHTML_table_B('width="780" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');
showXHTML_tr_B('class="cssTrHead"');
showXHTML_td('align="center" ', $MSG['item'][$sysSession->lang]);
showXHTML_td('align="center" width="300"', $MSG['content'][$sysSession->lang]);
showXHTML_td('align="center" ', $MSG['desc'][$sysSession->lang]);
showXHTML_tr_E();
$meeting = new Meeting();
//檢查用戶
$accountExist = $meeting->accountExist($sysSession->username);
if(!$accountExist)
{
  $meeting->createAccount($sysSession->username);
}
// http://192.168.10.224/Conf/jsp/user/gotocms.jsp?username=admin&userpass=admin&logintype=master
$MTinfo = $meeting->isMeetingExist($sysSession->course_id);
//若會議存在
if ($MTinfo['isExist']=='1') {

  $MTuserPWD = $meeting->getUser($sysSession->username);
  //加入會議 form
  showXHTML_form_B('method="POST" target="_blank" action="http://'.$meeting::serverIP.'/Conf/jsp/conference/enterMeetingAction.do"', 'loginFm');
      showXHTML_input('hidden','username', $sysSession->username ,'','');
      showXHTML_input('hidden','userpass', $MTuserPWD ,'','');
      showXHTML_input('hidden','confpass', $meeting::teacher_pwd ,'','');
      showXHTML_input('hidden','confid', $MTinfo['confid']."@slavemcu_1.machine1.v2c" ,'','');
      showXHTML_input('hidden','cid', $MTinfo['confid'] ,'','');
      showXHTML_input('hidden','conftype','publicchat','','');
      showXHTML_input('hidden','encrypt','0','','');
      showXHTML_input('hidden','parmeter','go_entermeeting','','');
      showXHTML_input('hidden','email','@','','');
      showXHTML_input('hidden','entertype','auto','','');
      showXHTML_input('hidden','serverid', "1:".$meeting::serverIP.":80:443" ,'','');
  showXHTML_form_E();


  showXHTML_form_B('method="POST" action="/co_meeting/module/controller.php"', 'frmSend');
  showXHTML_input('hidden','action','HostMeeting','','');
  showXHTML_input('hidden','url',$isExist['url'],'','');
  $confid = $MTinfo['confid'];
  
  showXHTML_script('inline', $frontEndCode);
  $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
  showXHTML_tr_B($col);
  showXHTML_td('align="center"', $MSG['title_26'][$sysSession->lang]);
  showXHTML_td_B();

  echo '<div style="text-align: center; width: 100%">' . $confid . '</div>';
  showXHTML_td_E();
  showXHTML_td('align="center"', $MSG['title_27'][$sysSession->lang]);
  showXHTML_tr_E('');

  $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
  showXHTML_tr_B($col);
  showXHTML_td('align="center"', $MSG['title25'][$sysSession->lang]);
  showXHTML_td_B();
  echo '<div style="text-align: center; width: 100%">' . $MTinfo['topic'] . '</div>';
  showXHTML_td_E();
  showXHTML_td('', '');
  showXHTML_tr_E('');

  $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
  showXHTML_tr_B($col);
  showXHTML_td('align="center"', $MSG['anchorman'][$sysSession->lang]);
  showXHTML_td_B();
  $realName = dbGetOne('WM_user_account', 'CONCAT(last_name, first_name)', 'username="' . $MTinfo['creator'] . '"');
  echo '<div style="text-align: center; width: 100%">' . $MTinfo['creator'] . '(' . $realName . ')</div>';
  showXHTML_td_E();
  showXHTML_td('', '');
  showXHTML_tr_E('');

  $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
  showXHTML_tr_B($col);
  showXHTML_td('align="center"', $MSG['meeting_duration'][$sysSession->lang]);
  showXHTML_td_B();

  echo '<div style="text-align: center; width: 100%">' . date('Y-m-d H:i',strtotime($MTinfo['start_date'])) .'~'. date("H:i",strtotime($MTinfo['end_date'])) . '</div>';
  showXHTML_td_E();
  showXHTML_td('', '');
  showXHTML_tr_E('');

  $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
  showXHTML_tr_B($col);
  showXHTML_td('align="center"', $MSG['desc'][$sysSession->lang]);
  showXHTML_td_B();
  echo '<div style="text-align: center; width: 100%">若您有在寶訊通管理介面修改個人密碼<br>請於此處修改成對應的密碼</div>';
  showXHTML_td_E();
  showXHTML_td('align="center"', '<button type="button" class="cssBtn" onclick="pwdSetting()">設定個人密碼</button>');
  showXHTML_tr_E('');

  $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
  showXHTML_tr_B($col);
  showXHTML_td_B('align="center" colspan=3');
  showXHTML_input('button', '', $MSG['title_29'][$sysSession->lang], '', 'onclick="JoinMeeting()"');
  showXHTML_td_E();
  showXHTML_tr_E('');
} else {
  showXHTML_form_B('method="post" action="module/controller.php"', 'frmSend');

  $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
  showXHTML_tr_B($col);
  showXHTML_td('align="center" style="width: 120px;"', $MSG['title25'][$sysSession->lang]);
  showXHTML_td_B();

  showXHTML_input('text', 'name', $sysSession->course_name . $defaultCourseSuffix, '', 'style="width: 80%"');

  showXHTML_td_E();
  showXHTML_td('style="width: 120px;"', $MSG['desc_meeting_title'][$sysSession->lang]);
  showXHTML_tr_E('');

  $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
  showXHTML_tr_B($col);
  showXHTML_td('align="center"', $MSG['anchorman'][$sysSession->lang]);
  showXHTML_td('', $sysSession->username . '(' . $sysSession->realname . ')');
  showXHTML_td('align="center"');
  showXHTML_tr_E('');


  $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
  showXHTML_tr_B($col);
  showXHTML_td('align="center"', $MSG['meeting_duration'][$sysSession->lang]);
  showXHTML_td_B("");
  showXHTML_input('select', 'duration', array('60' => '1', '120' => '2', '180' => '3', '240' => '4', '300' => '5', '360' => '6'), "180", 'id="duration"');
  echo $MSG['hour'][$sysSession->lang];
  showXHTML_td_E();
  showXHTML_td('', $MSG['meeting_duration_tip'][$sysSession->lang]);
  showXHTML_tr_E('');

  $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
  showXHTML_tr_B($col);
  showXHTML_td_B('align="center" colspan=3');
  showXHTML_input('hidden', 'action', 'createMeeting', '', '');
  showXHTML_input('button', '', $MSG['create_metting_btn'][$sysSession->lang], '', 'id="submitBtn" onclick="createFunc()"');
  showXHTML_td_E();
  showXHTML_tr_E('');
}

showXHTML_table_E();
showXHTML_form_E();
showXHTML_tr_E();
showXHTML_table_E();


showXHTML_body_E();
?>
