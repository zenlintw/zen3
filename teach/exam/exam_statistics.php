<?php
    /**
     * �� �T�X�@�έp
     *
     * @since   2004/09/22
     * @author  Wiseguy Liang
     * @version $Id: exam_statistics.php,v 1.1 2010/02/24 02:40:25 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    //ACL begin
    if (QTI_which == 'exam') {
        $sysSession->cur_func = '1600100100';
    }
    else if (QTI_which == 'homework') {
        $sysSession->cur_func = '1700100100';
    }
    else if (QTI_which == 'questionnaire') {
        $sysSession->cur_func = '1800100100';
    }
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

    }
    //ACL end

    if (!defined('QTI_env'))
        list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
    else
        $topDir = QTI_env;

    $course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

    $random_seat = md5(uniqid(rand(), true));
    $ticket = md5(sysTicketSeed . $course_id . $random_seat);
    $exam_types = array($MSG['exam_type1'][$sysSession->lang],
                        $MSG['exam_type2'][$sysSession->lang],
                        $MSG['exam_type3'][$sysSession->lang],
                        $MSG['exam_type4'][$sysSession->lang],
                        $MSG['exam_type5'][$sysSession->lang],
                        $MSG['exam_type6'][$sysSession->lang]
                       );

    $publishes = array('prepare' => $MSG['publish_state1'][$sysSession->lang],
                       'action'  => $MSG['publish_state2'][$sysSession->lang],
                       'close'   => $MSG['publish_state3'][$sysSession->lang]
                      );

    $count_types = array('none'    => $MSG['count_type0'][$sysSession->lang],
                         'first'   => $MSG['count_type1'][$sysSession->lang],
                         'last'    => $MSG['count_type2'][$sysSession->lang],
                         'max'     => $MSG['count_type3'][$sysSession->lang],
                         'min'     => $MSG['count_type4'][$sysSession->lang],
                         'average' => $MSG['count_type5'][$sysSession->lang]
                        );
    $announce_types = array('never'       => $MSG['announce_type1'][$sysSession->lang],
                            'now'         => $MSG['announce_type2'][$sysSession->lang],
                            'close_time'  => $MSG['announce_type3'][$sysSession->lang],
                            'user_define' => $MSG['announce_type4'][$sysSession->lang]
                           );

    showXHTML_head_B($MSG['exam_correct'][$sysSession->lang]);
      showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/wm.css");
      $scr = <<< EOB

function chBgc(obj, mode){
    obj.style.backgroundColor = mode ? '#FFFFCC' : '';
}

function view_result(id){
    var obj = document.getElementById('procForm');
    obj.lists.value = id;
    obj.submit();
}

EOB;
      showXHTML_script('inline', $scr);
    showXHTML_head_E();
    showXHTML_body_B();
      $ary[] = array($MSG['exam_result'][$sysSession->lang], 'tabsSet',  '');
      echo "<div align=\"center\">\n";
      showXHTML_tabFrame_B($ary);
          showXHTML_table_B('id="displayPanel" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="box01"');
            showXHTML_tr_B('class="bg02 font01"');
              showXHTML_td('align="center" width="410"', $MSG['exam_name'][$sysSession->lang]);
              if ((QTI_which != 'questionnaire')||(sysEnableAppISunFuDon && QTI_which == 'questionnaire')){
                showXHTML_td('align="center" width="80" ', $MSG['exam_use'][$sysSession->lang]);
              }
              showXHTML_td('align="center" width="50" ', $MSG['exam_publish'][$sysSession->lang]);
              // showXHTML_td('align="center" width="32" ', $MSG['exam_percent'][$sysSession->lang]);
              // showXHTML_td('align="center" width="60" ', $MSG['count_type'][$sysSession->lang]);
              showXHTML_td('align="center" width="120"', $MSG['exam_duration'][$sysSession->lang]);
              showXHTML_td('align="center" width="80" ', $MSG['score_publish_' . QTI_which][$sysSession->lang]);
              showXHTML_td('align="center" width="36"', '&nbsp;');
            showXHTML_tr_E();

    $RS = dbGetStMr('WM_qti_' . QTI_which . '_test',
            'exam_id,title,type,publish,begin_time,close_time,count_type,percent,announce_type,announce_time',
            "type IN (1, 2, 3, 4) AND course_id={$course_id} order by sort,exam_id");
    if ($sysConn->ErrorNo() > 0) {
       $errMsg = $sysConn->ErrorMsg();
       wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $errMsg);
       die($errMsg);
    }
    if ($RS)
    while($fields = $RS->FetchRow()){
        $col = $col == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"';
            showXHTML_tr_B($col . ' onmouseover="chBgc(this, true);" onmouseout="chBgc(this, false);"');
              $title = (strpos($fields['title'], 'a:') === 0) ?
                       getCaption($fields['title']):
                       array('Big5'            => $fields['title'],
                             'GB2312'        => $fields['title'],
                             'en'            => $fields['title'],
                             'EUC-JP'        => $fields['title'],
                             'user_define'    => $fields['title']
                               );
              showXHTML_td('nowrap title="' . htmlspecialchars($title[$sysSession->lang]) . '"', sprintf('<span style="width:320px; overflow: hidden">%s</span>', htmlspecialchars($title[$sysSession->lang])));

              if ((QTI_which != 'questionnaire')||(sysEnableAppISunFuDon && QTI_which == 'questionnaire')){
                showXHTML_td('', $exam_types[$fields['type']]);
              }
              showXHTML_td('', $publishes[$fields['publish']]);
              // showXHTML_td('', $fields['percent'] . '%');
              // showXHTML_td('', $count_types[$fields['count_type']]);
              showXHTML_td('style="font-size: 10"', ($MSG['from'][$sysSession->lang] . (strpos($fields['begin_time'], '0000') === 0 ? $MSG['now'][$sysSession->lang] : date('Y-m-d H:i', strtotime($fields['begin_time'])) ) . '<br>' . $MSG['to2'][$sysSession->lang] . (strpos($fields['close_time'], '9999') === 0 ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', strtotime($fields['close_time'])) )));
              showXHTML_td('', ($fields['announce_type']=='user_define'? sprintf('<span style="font-size: 10">%s</span>', substr($fields['announce_time'],0,16)):$announce_types[$fields['announce_type']]));
              showXHTML_td_B();
                showXHTML_input('button', '', $MSG['view'][$sysSession->lang], '', 'onclick="view_result(' . $fields['exam_id'] . ');"');
              showXHTML_td_E();
            showXHTML_tr_E();
    }
          showXHTML_table_E();
      showXHTML_tabFrame_E();
      echo "</div>\n";
      showXHTML_form_B('method="POST" action="exam_statistics_result.php"', 'procForm');
        showXHTML_input('hidden', 'ticket', $ticket);
        showXHTML_input('hidden', 'referer', $random_seat);
        showXHTML_input('hidden', 'lists', '');
      showXHTML_form_E();

    showXHTML_body_E();
?>
