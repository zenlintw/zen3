<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lang/online_chat_list.php');
require_once(BREEZE_PHP_DIR . '/global.php');
require_once(BREEZE_PHP_DIR . '/Actions/SessionManager.php');
require_once(BREEZE_PHP_DIR . '/Actions/ScoInfo.php');
require_once(BREEZE_PHP_DIR . '/Actions/ScoContents.php');
require_once(BREEZE_PHP_DIR . '/Actions/Report/Meetings.php');

#======= class & functions ==============
function getRecordingList($sess, $idx)
{
    $idx      = substr($idx, -5) . '-';
    $rtnArray = array();
    $arr      = getCourseMeetingsList($sess, $idx);
    $new_arr  = getCourseMeetingsList($sess, $idx, BREEZE_WM_MEETING_FOLDER_ID1);
    $arr      = array_merge($arr, $new_arr);
    // if (count($arr) == 0) return array();
    for ($i = 0, $size = count($arr); $i < $size; $i++) {
        $obj =& $arr[$i];
        $action = new ScoContents($sess, $obj->scoId);
        $action->addParameters('filter-icon', 'archive');
        $action->run();
        // echo $action->conn->HTTP_RESPONSE_BODY;
        $xmlarr = explode('</sco><sco', $action->conn->HTTP_RESPONSE_BODY);
        for ($j = 0; $j < count($xmlarr); $j++) {
				$obj1 = new MeetingRecorderXML($xmlarr[$j]);
				if (empty($obj1->scoId)) continue;
				$action = new ScoInfo($sess, $obj1->scoId);
				$action->run();
				if (strpos($action->conn->HTTP_RESPONSE_BODY,'code="ok"') === false) continue;
				$offshift = 0;
				$obj1->urlpath = getBetweenInnerString($action->conn->HTTP_RESPONSE_BODY,'<url-path>/','/</url-path>',$offshift);
				$rtnArray[] = $obj1;
        }
    }
    /*
    if (strlen($idx) > 5){
    
    if (count($rtnArray)>0){
    $rtnArray = array_merge($rtnArray, getRecordingList($sess, $idx));
    }else{
    $rtnArray = getRecordingList($sess, $idx);
    }
    
    }*/
    return $rtnArray;
}

function ISOmktime($str)
{
    if ($str != '') {
        $arr = explode('T', $str);
        list($y, $m, $d) = explode('-', $arr[0]);
        list($H, $i, $s) = explode(':', substr($arr[1], 0, 8));
        return mktime($H, $i, $s, $m, $d, $y);
    } else {
        
        return '';
    }
}

function printArchivesList($arr)
{
    global $sysSession, $MSG;
    $rtns = '';
    for ($i = 0; $i < count($arr); $i++) {
        $obj      = $arr[$i];
        $duration = ISOmktime($obj->date_end) - ISOmktime($obj->date_created);
        $bg       = ($bg == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
        $rtns .= '<tr class="' . $bg . '">' . "\r\n";
        $rtns .= '<td nowrap align="center" class="a01">' . ($i + 1) . '</td>' . "\r\n";
        $rtns .= '<td nowrap align="left" class="a01">' . $obj->name . '</td>' . "\r\n";
        $rtns .= '<td nowrap align="center" class="a01">' . $obj->date_begin . '</td>' . "\r\n";
        $rtns .= '<td nowrap align="center" class="a01">' . $obj->duration . '</td>' . "\r\n";
        $rtns .= '<td nowrap align="center"><input type="button" name="btnPlay" value="' . $MSG['title8'][$sysSession->lang] . '" onClick="PlayRecord(\'' . $obj->urlpath . '\',\'' . $obj->scoId . '\');" class="cssBtn">';
        if ($sysSession->env == 'teach') {
            $rtns .= '<td nowrap align="center"><input type="button" name="btnRemove" value="' . $MSG['title10'][$sysSession->lang] . '" onClick="doDelete(\'' . $obj->scoId . '\')" class="cssBtn">';
        }
        $rtns .= '</tr>	' . "\r\n";
    }
    return $rtns;
}
#========= Main =================
//1. Get Admin Session
	$sess = getEnableSessionId();
	if (empty($sess)) die("errcode:001");
//2. Get ActiveMeeting Array
	$Archives = getRecordingList($sess, getCUID($sysSession->course_id));
#======== html output ============
$js = <<< BOF
    function PlayRecord(urlpath, scoid) {
        var options = "toolbar=0,status=0,location=0,resizable=1";
        var url = "PlayRecord.php?urlpath=" + urlpath + "&scoId=" + scoid;
        var win = open(url, "", options);
    }

    function doDelete(id) {
        if (confirm("{$MSG['delete_confirm'][$sysSession->lang]}")) {
            document.frmDelete.scoid.value = id;
            document.frmDelete.submit();
        }
    }
BOF;

// 學習環境套橘白色版型，教師環境就繼續執行原本的介面
if ($sysSession->env == 'learn') {
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    $smarty->assign('inlineJS', $js);
    $smarty->assign('datalist', $Archives);
    
    // output
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('learn/breeze/meeting_archives.tpl');
    $smarty->display('common/tiny_footer.tpl');
    exit;
}


#======= html output ==============
showXHTML_head_B('Online Meeting');
showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
showXHTML_script('inline', $js);
showXHTML_head_E('');
showXHTML_body_B('');
showXHTML_table_B('align="center" border="0" cellpadding="0" cellspacing="0" width="90%" style="border-collapse: collapse"');
    showXHTML_tr_B();
        showXHTML_td_B();
        	$ary[] = array($MSG['breeze_record_list'][$sysSession->lang], 'tabsSet',  '');
        	showXHTML_tabs($ary, 1);
      	showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_tr_B();
		showXHTML_td_B('');
		showXHTML_table_B('id="tbl_message" width="100%" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse;display:none" class="cssTable"');
        		showXHTML_tr_B('class="cssTrHead"');
        		showXHTML_td('align="center"' , $MSG['deleting_record_list'][$sysSession->lang]);
        showXHTML_table_E();

            showXHTML_table_B('id="tbl_list" width="100%" border="0" cellpadding="3" cellspacing="1" style="/*border-collapse: collapse;display:block*/" class="cssTable" ');
                showXHTML_tr_B('class="cssTrHead"');
                    showXHTML_td('align="center" nowrap', $MSG['title21'][$sysSession->lang]);
                    showXHTML_td('align="center" nowrap', $MSG['title25'][$sysSession->lang]);
                    showXHTML_td('align="center" nowrap', $MSG['title2'][$sysSession->lang]);
                    showXHTML_td('align="center" nowrap', $MSG['title7'][$sysSession->lang]);
                    showXHTML_td('align="center" nowrap', $MSG['title8'][$sysSession->lang]);
                    if ($sysSession->env == 'teach') {
                        showXHTML_td('align="center" nowrap', $MSG['co_path_insert'][$sysSession->lang]);
                        showXHTML_td('align="center" nowrap', $MSG['title5'][$sysSession->lang]);
                    }
                showXHTML_tr_E();
                echo printArchivesList($Archives);
            showXHTML_table_E();
        showXHTML_td_E();
    showXHTML_tr_E();
showXHTML_table_E();

if ($sysSession->env == 'teach')
{
    showXHTML_form_B('method="post" action="MeetingDelete.php"', 'frmDelete');
    showXHTML_input('hidden', 'scoid', '', '', '');
    showXHTML_input('hidden', 'op_from', 'archives', '', '');
    showXHTML_form_E();
}

showXHTML_body_E();