<?php
/**************************************************************************************************
 *                                                                                                *
 *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
 *                                                                                                *
 *        Programmer: Wiseguy Liang                                                                  *
 *        Creation  : 2004/06/29 integrate                                                          *
 *        work for  : create & modify exam                                                          *
 *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
 *        identification : $Id: exam_create.php,v 1.3 2010-09-17 03:50:12 lst Exp $              *
 *                                                                                                *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
require_once(sysDocumentRoot . '/lib/multi_lang.php');
require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/editor.php');

//ACL begin
include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');
$sysSession->cur_func='1710200100';

$sysSession->restore();
if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

}
//ACL end


function fetch_variable($match)
{
    global $MSG, $sysSession;

    return $MSG[$match[1]][$sysSession->lang];
}


if (!defined('QTI_env'))
    list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
else
    $topDir = QTI_env;

$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

// 日期
$date = getdate();

$prog_type = basename($_SERVER['PHP_SELF']);
if (!in_array($prog_type, array('exam_create.php', 'exam_modify.php'))) {
   wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Illegal including.');
   die('Illegal including.');
}

$sectionIdx = 1;
if ($prog_type == 'exam_modify.php'){
    if (!isset($_POST['ticket'])) {
       wmSysLog($sysSession->cur_func, $course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'Ticket is missing!');
       die('Ticket is missing.');
    }

    $ticket = md5(sysTicketSeed . $course_id . $_POST['referer']);        // 產生 ticket
    if ($ticket != $_POST['ticket']) {// 檢查 ticket
       wmSysLog($sysSession->cur_func, $course_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
       die('Fake ticket.');
    }
    if (!ereg('^[0-9]+$', $_POST['lists'])) {    // 檢查 lists
       wmSysLog($sysSession->cur_func, $course_id , 0 , 4, 'auto', $_SERVER['PHP_SELF'], 'Fake lists!');
       die('Fake lists.');
    }
    // 產生新 ticket
    $ticket = md5(sysTicketSeed . $_COOKIE['idx'] . $_POST['lists']);

    // 取得本測驗內容
    $RS = dbGetStSr('WM_qti_' . QTI_which . '_test', '*', "exam_id={$_POST['lists']}");// 資料庫, ADODB_FETCH_ASSOC存取

    $sectionIdx = substr_count($RS['content'],'<mattext>')+1;// 計算<mattext>出現的次數+1

    if ($RS === false){
       $errMsg = $sysConn->ErrorNo() . ' : ' . $sysConn->ErrorMsg();
       wmSysLog($sysSession->cur_func, $course_id , 0 , 5, 'auto', $_SERVER['PHP_SELF'], $errMsg);
       die($errMsg);
    }

    $title = (strpos($RS['title'], 'a:') === 0) ?
             unserialize($RS['title']) :
             array('Big5'        => $RS['title'],
                   'GB2312'      => $RS['title'],
                   'en'          => $RS['title'],
                   'EUC-JP'      => $RS['title'],
                   'user_define' => $RS['title']) ;

    $immediate_random_pick = false;// 隨機選題為否,則不秀wm_immediate_random_generate_qti
    if (empty($RS['content']))
        $examDetail = '<questestinterop />';
    elseif(strpos($RS['content'], '<wm_immediate_random_generate_qti') !== FALSE)
    {
        $immediate_random_pick = true;// 隨機選題為真,則判斷配分並取值$regs[1]
        $threshold_score = preg_match('/\bthreshold_score="([0-9]*)"/', $RS['content'], $regs) ? $regs[1] : '';

        if(strpos($RS['content'], '<conditions>') !== FALSE)
        {
            $xslt_buf = preg_replace_callback('/\{\$MSG\[\'([^\]]+)\'\]\[[^\]]+\]\}/', 'fetch_variable', file_get_contents('condition.xsl'));
            $xsl = domxml_xslt_stylesheet($xslt_buf);// Creates a DomXsltStylesheet Object from a xml document in a string.
            $xml = domxml_open_mem(preg_replace('/(<fulltext\b[^>]*>)[^\t]*\t/', '\1', $RS['content']));// Creates a DOM object of an XML document
            $result =  $xsl->process($xml);
            $recall = preg_replace(array('/>\s+</', '/\s+</', '/>\s+/'), array('><', '<', '>'), $xsl->result_dump_mem($result));// Dumps the result from a XSLT-Transformation back into a string
        }
        else
        {
            if (preg_match_all('!<([^>]+)\s+selected="(true|false)">(.*)</[^>]+>!sU', $RS['content'], $regs))// 將所有試題中的TF取出
                foreach($regs[1] as $k => $v)
                    $irgs[$v] = array(($regs[2][$k]=='true'), $regs[3][$k]);
            if (isset($irgs['fulltext']))
            {
                list($foo, $irgs['fulltext'][1]) = explode("\t", $irgs['fulltext'][1]);
                if (empty($irgs['fulltext'][1])) $irgs['fulltext'][1] = $foo;
            }
        }

        $examDetail = '<questestinterop />';
    }
    else{
        $examDetail = $RS['content'];

        // 將已刪除的題目，自卷中移除
        if (preg_match_all('/<item [^>]*id="(\w+)"/U', $examDetail, $regs, PREG_PATTERN_ORDER))
        {
            $exists_item = $sysConn->GetCol(sprintf('select ident from WM_qti_%s_item where ident in ("%s")', QTI_which, implode('","', $regs[1])));
            if (is_array($exists_item)) {
                $removed = array_diff($regs[1], $exists_item);
                if (count($removed))
                {
                    $pattern = explode(chr(9), '!<item [^>]*id="' . implode('"[^>]*>[^<]*</item>!isU' . chr(9) . '!<item [^>]*id="', $removed) . '"[^>]*>[^<]*</item>!isU');
                    $replace = array_pad(array(), count($pattern), '');
                    $examDetail = preg_replace($pattern, $replace, $examDetail);
                }
            }
        }

        $examDetail = strtr(
            $examDetail,
            array(
                "'"  => "&#39;",
                "\n" => '',
                "\r" => '',
                '\\' => '\\\\'
            )
        );
    }
    $caption = $MSG['exam_modify'][$sysSession->lang];
    $examinee_perm = array('homework' => 1700400200, 'exam' => 1600400200, 'questionnaire' => 1800300200, 'peer' => 1710400200);
    $examiner_perm = array('homework' => 1700300100, 'exam' => 1600300100, 'questionnaire' => 0, 'peer' => 1710300100);

    $forGuest = false;
    $acl0 = aclGetAclArrayByInstance($examinee_perm[QTI_which], $course_id, $_POST['lists']);
    $acl1 = aclGetAclArrayByInstance($examiner_perm[QTI_which], $course_id, $_POST['lists']);
    if(!empty($acl0) || !empty($acl1))
        $acl_lists = 'var acl_lists = new Array(new Array(' . implode(",\n", $acl0) . '), new Array(' . implode(",\n", $acl1) .  "));\n";
    else
        $acl_lists = '';
}
elseif ($prog_type == 'exam_create.php'){
    $examDetail = '<questestinterop xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd" xmlns:wm="http://www.sun.net.tw/WisdomMaster"></questestinterop>';
    $caption = $MSG['exam_create'][$sysSession->lang];
}
else {
    wmSysLog($sysSession->cur_func, $course_id , 0 , 6, 'auto', $_SERVER['PHP_SELF'], 'Incorrect passing argument.');
    die('Incorrect passing argument.');
}

showXHTML_head_B($caption);
  showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/wm.css");
  showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/peer.css");
  showXHTML_script('include', '/lib/dragLayer.js');
  showXHTML_script('include', '/lib/xmlextras.js');
  $calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
  $calendar->load_files();
  $cur_tab = (ereg('^[0-4]$',$_SERVER['argv'][0])?$_SERVER['argv'][0]:0);
  $qti_which =  QTI_which;
  $CourseExamQuestionsLimit = CourseExamQuestionsLimit;
  $msg_over_question_number = str_replace('%num_limit%', CourseExamQuestionsLimit, $MSG['msg_over_questions_number'][$sysSession->lang]);
  $hiddenRandomTab = (QTI_which == 'exam') ? '' : "if ((_tab4 = document.getElementById('TitleID4')) != null) { var x = _tab4.cellIndex; _tab4.style.display='none'; _tab4.parentNode.cells[x-1].style.display='none'; _tab4.parentNode.cells[x+1].style.display='none'; }";
  $xx = isset($RS) ? ($immediate_random_pick ? 'selectRandomMode(2);' : 'selectRandomMode(1);') : (QTI_which == 'exam' ? '' : 'selectRandomMode(1);');

  $scr = <<< EOB
var qti_which='{$qti_which}';
var types = new Array('',
                  '{$MSG['item_type1'][$sysSession->lang]}',
                  '{$MSG['item_type2'][$sysSession->lang]}',
                  '{$MSG['item_type3'][$sysSession->lang]}',
                  '{$MSG['item_type4'][$sysSession->lang]}',
                  '{$MSG['item_type5'][$sysSession->lang]}',
                  '{$MSG['item_type6'][$sysSession->lang]}',
                  '{$MSG['item_type7'][$sysSession->lang]}'
                 );
if (qti_which!='questionnaire'){
var srTables = new Array('{$MSG['select'][$sysSession->lang]}',
                         'No.',
                         '{$MSG['item_type'][$sysSession->lang]}',
                         '{$MSG['item_desc'][$sysSession->lang]}',
                         '{$MSG['version'][$sysSession->lang]}',
                         '{$MSG['volume'][$sysSession->lang]}',
                         '{$MSG['chapter'][$sysSession->lang]}',
                         '{$MSG['paragraph'][$sysSession->lang]}',
                         '{$MSG['section'][$sysSession->lang]}',
                         '{$MSG['hard_level'][$sysSession->lang]}',
                         '{$MSG['hard_level'][$sysSession->lang]}'
                        );
}else{
var srTables = new Array('{$MSG['select'][$sysSession->lang]}',
                         'No.',
                         '{$MSG['item_type'][$sysSession->lang]}',
                         '{$MSG['item_desc'][$sysSession->lang]}',
                         '{$MSG['version'][$sysSession->lang]}',
                         '{$MSG['volume'][$sysSession->lang]}',
                         '{$MSG['chapter'][$sysSession->lang]}',
                         '{$MSG['paragraph'][$sysSession->lang]}',
                         '{$MSG['section'][$sysSession->lang]}'
                        );
}
var btms = new Array('{$MSG['select'][$sysSession->lang]}',
         '{$MSG['cancel'][$sysSession->lang]}',
         '{$MSG['prev_step'][$sysSession->lang]}',
         '{$MSG['next_step'][$sysSession->lang]}'
        );

var rowspages = new Array(-1,20,50,100,200,400
        );
var rowspagesn = new Array('{$MSG['default'][$sysSession->lang]}',20,50,100,200,400
        );
var pickbtm                = '{$MSG['select'][$sysSession->lang]}';
var nowPickedNum           = 0;       // 目前此試卷已選取的題目
var MaxPickedNum           = parseInt('{$CourseExamQuestionsLimit}');        // 一份試卷最多可選取的題目
var msg_overNumber         = '{$msg_over_question_number}';
var MSG_SELECT_ALL         = "{$MSG['select_all'][$sysSession->lang]}";
var MSG_SELECT_CANCEL      = "{$MSG['cancel_all'][$sysSession->lang]}";
var MSG_SEARCHPAGE_TOP     = "{$MSG['page_first'][$sysSession->lang]}";
var MSG_SEARCHPAGE_UP      = "{$MSG['page_prev'][$sysSession->lang]}";
var MSG_SEARCHPAGE_DOWN    = "{$MSG['page_next'][$sysSession->lang]}";
var MSG_SEARCHPAGE_END     = "{$MSG['page_last'][$sysSession->lang]}";
var MSG_PAGE_NUM           = "{$MSG['page'][$sysSession->lang]}";
var MSG_PAGE_EACH          = "{$MSG['each_page'][$sysSession->lang]}";
var MSG_PAGE_ITEM          = "{$MSG['item'][$sysSession->lang]}";
var MSG_MV_SEC             = "{$MSG['move_item_to_section'][$sysSession->lang]}";
var MSG_X_MV_CHD           = "{$MSG['dont_mv_to_child'][$sysSession->lang]}";
var MSG_SEL_SEC            = "{$MSG['select_section_first'][$sysSession->lang]}";
var MSG_SEL_FIRST          = "{$MSG['select_first'][$sysSession->lang]}";
var MSG_SEL_BEFORE         = "{$MSG['select_before_assign'][$sysSession->lang]}";
var MSG_INPUT_SCORE        = "{$MSG['input_score'][$sysSession->lang]}";
var MSG_INPUT_TOTAL        = "{$MSG['input_total'][$sysSession->lang]}";
var MSG_IGN_REPEAT         = "{$MSG['ignore_repeat'][$sysSession->lang]}";
var MSG_NOT_XML            = "{$MSG['return_not_xml'][$sysSession->lang]}";
var MSG_INCR_XML           = "{$MSG['incorrect_xml'][$sysSession->lang]}";
var MSG_INCR_FORM          = "{$MSG['incorrect_form'][$sysSession->lang]}";
var MSG_NO_RESULT          = "{$MSG['no_result'][$sysSession->lang]}";
var MSG_NO_ITEMS           = "{$MSG['no_items'][$sysSession->lang]}";
var MSG_UNKNOW_ERR         = "{$MSG['unknown_err'][$sysSession->lang]}";
var MSG_SCORE_REM          = "{$MSG['score_remind'][$sysSession->lang]}";
var MSG_LANG_HINT          = "{$MSG['lnguage_hint'][$sysSession->lang]}";
var MSG_DATE_ERR           = "{$MSG['msg_date_error'][$sysSession->lang]}";
var MSG_IRGA_REQ           = "{$MSG['immediate_random_generate_amount_request'][$sysSession->lang]}";
var MSG_IRGS_REQ           = "{$MSG['immediate_random_generate_score_request'][$sysSession->lang]}";
var MSG_PICK_ITEM_CUE      = "{$MSG['pick_item_cue'][$sysSession->lang]}";
var MSG_GROUP_REQ        = "{$MSG['msg_teach_create_group_error'][$sysSession->lang]}";

var MSG_RATING_NEED        = "{$MSG['rating_answer_required'][$sysSession->lang]}";
var MSG_PUB_GRE_RATING     = "{$MSG['publish_greater_rating'][$sysSession->lang]}";
var MSG_PUB_GRE_ANS        = "{$MSG['publish_greater_answer'][$sysSession->lang]}";
var MSG_ANS_DATE_ERR       = "{$MSG['msg_answer_date_error'][$sysSession->lang]}";
var MSG_RATE_DATE_ERR      = "{$MSG['msg_rating_date_error'][$sysSession->lang]}";
var MSG_SCR_DATE_ERR       = "{$MSG['msg_score_date_error'][$sysSession->lang]}";
var MSG_MAX_100            = "{$MSG['msg_max_100'][$sysSession->lang]}";
var MSG_RATING_NOTICE_NEED = "{$MSG['need_rating_notice'][$sysSession->lang]}";
var MSG_PEERSELF_NEED      = "{$MSG['need_peer_self'][$sysSession->lang]}";
var MSG_PEER_PERCENT      = "{$MSG['need_peer_percent'][$sysSession->lang]}";
var MSG_SELF_PERCENT      = "{$MSG['need_self_percent'][$sysSession->lang]}";

{$acl_lists}

var st_id       = '{$sysSession->cur_func}{$sysSession->course_id}{$_POST['lists']}';
var hide_answer = '{$MSG['hide answer'][$sysSession->lang]}';
var show_answer = '{$MSG['show answer'][$sysSession->lang]}';

if (qti_which!='questionnaire'){
var hard_levels = new Array('', '{$MSG['hard_level1'][$sysSession->lang]}', '{$MSG['hard_level2'][$sysSession->lang]}', '{$MSG['hard_level3'][$sysSession->lang]}', '{$MSG['hard_level4'][$sysSession->lang]}', '{$MSG['hard_level5'][$sysSession->lang]}');
}else{
var hard_levels = new Array('', '{$MSG['hard_level1'][$sysSession->lang]}', '{$MSG['hard_level2'][$sysSession->lang]}', '{$MSG['hard_level3'][$sysSession->lang]}', '{$MSG['hard_level4'][$sysSession->lang]}');
}

var block_title = new Array('{$MSG['exam_paper'][$sysSession->lang]}', '{$MSG['exam_section'][$sysSession->lang]}', '{$MSG['exam_item'][$sysSession->lang]}');
var lang        = '{$sysSession->lang}';
var sectionIdx  = $sectionIdx;
var cur_tab     = $cur_tab;
var theme       = '{$sysSession->theme}';
var examDetail  = XmlDocument.create();
examDetail.loadXML('$examDetail ');

function hiddenRandomTab()
{
$hiddenRandomTab
}

function xx()
{
$xx
}

EOB;
  showXHTML_script('inline', $scr);
  showXHTML_script('include', '/lib/jquery/jquery.min.js', true, null, 'UTF-8');
  showXHTML_script('include', '/lib/common.js');
  showXHTML_script('include', '/teach/peer/exam_create.js');
  showXHTML_script('include', '/teach/peer/exam_create2.js');
  $xajax_save_temp->printJavascript('/lib/xajax/');
showXHTML_head_E();
showXHTML_body_B(' onclick="if (!acl_hidden_flags) {hide_acl_dialog(); acl_hidden_flags = false;}"');
  showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" width="1000" style="border-collapse: collapse"');
    showXHTML_tr_B();
      showXHTML_td_B();
            $ary = array(array($MSG['exam_info'][$sysSession->lang],     '',  'switchTab(0);'),
                         array($MSG['exam_preview'][$sysSession->lang],  '',  'switchTab(4);')
                        );
        showXHTML_tabs($ary, $cur_tab + 1);
      showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_tr_B();
      showXHTML_td_B('class="bg01"');
// TAB-1
        echo '<div id="tabContent0" style="display: none">', "\n";
          showXHTML_form_B('method="POST" action="exam_save.php" style="display:inline"', 'saveForm');
            showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="1000" style="border-collapse: collapse" class="box01" id="tab1Table"');

              showXHTML_tr_B('class="bg02 font01" nowrap');
                showXHTML_td_B('align="right" colspan="3"');
                  showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]   , '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                        showXHTML_input('button', '', $MSG['next_step'][$sysSession->lang], '', 'class="cssBtn" onclick="saveContent(4);"');
                  if (isset($RS)) showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="cssBtn" onclick="saveContent();"');
                showXHTML_td_E();
              showXHTML_tr_E();

              /*分區-基本資料*/
                    showXHTML_tr_B('class="bg03 font01"');
                        showXHTML_td('class="cssTabs" width="117"', $MSG['basic_info'][$sysSession->lang]);
                        showXHTML_td('class="font06 strong-note" align="right" colspan="2"', $MSG['required'][$sysSession->lang]);
                    showXHTML_tr_E();
              $arr_names = array('Big5'           => 'title[Big5]',
                                 'GB2312'       => 'title[GB2312]',
                                 'en'           => 'title[en]',
                                 'EUC-JP'       => 'title[EUC-JP]',
                                 'user_define' => 'title[user_define]'
                                );
                showXHTML_tr_B('class="bg03 font01"');
                    showXHTML_td_B('');
                        echo '<span class="strong-note">*</span>';
                        echo $MSG['exam_name'][$sysSession->lang];
                    showXHTML_td_E();
                    showXHTML_td_B('width="450"');
                        $multi_lang = new Multi_lang(false, $title); // 多語系輸入框
                        $multi_lang->show(true, $arr_names);
                    showXHTML_td_E();
                    showXHTML_td('class="font06"', $MSG['lnguage_hint'][$sysSession->lang]);
                showXHTML_tr_E();

              showXHTML_tr_B('class="bg04 font01"');
                showXHTML_td('', $MSG['pre-notice'][$sysSession->lang]);
                showXHTML_td_B();
                    $oEditor = new wmEditor;
                    $oEditor->addContType('isHTML', 1);
                    $oEditor->setValue(($RS['notice'])?($RS['notice']):'&nbsp;');
                    $oEditor->setConfig('ToolbarStartExpanded', false);
                    $oEditor->generate('notice_1', '440', '205');
                    showXHTML_input('hidden', 'notice');
                showXHTML_td_E();
                showXHTML_td('class="font06"', $MSG['pre-notice1'][$sysSession->lang]);
              showXHTML_tr_E();

              showXHTML_tr_B('class="bg03 font01"' . (QTI_which == 'exam' ? '' : ' style="display: none"'));
                showXHTML_td('', $MSG['exam_use'][$sysSession->lang]);
                showXHTML_td_B();
                  showXHTML_input('select', 'ex_type', array(1 => $MSG['exam_type2'][$sysSession->lang],
                                                             2 => $MSG['exam_type3'][$sysSession->lang],
                                                             3 => $MSG['exam_type4'][$sysSession->lang],
                                                             4 => $MSG['exam_type5'][$sysSession->lang]
                                                            ), intval($RS['type']), 'class="box02"');
                showXHTML_td_E();
                showXHTML_td('class="font06"', $MSG['exam_use1'][$sysSession->lang]);
              showXHTML_tr_E();

              /*發佈*/
              showXHTML_tr_B('class="bg04 font01" id="trStatus"');
                showXHTML_td_B('');
                    echo '<span class="strong-note">*</span>';
                    echo $MSG['exam_publish'][$sysSession->lang];
                showXHTML_td_E();
                showXHTML_td_B();
                    $rdoPublishes[1] = $MSG['exam_rdoPublish_1'][$sysSession->lang];
                    $rdoPublishes[2] = $MSG['exam_rdoPublish_2'][$sysSession->lang];
                    switch($RS['publish'])
                    {
                        case 'prepare':    $rdoPublishValue=1; break;
                        case 'action':    $rdoPublishValue=2; break;
                        case 'close':    $rdoPublishValue=3; break;
                        default:    $rdoPublishValue=1;
                    }
                        showXHTML_input('radio', 'rdoPublish', $rdoPublishes, $rdoPublishValue, 'class="box02"');
                showXHTML_td_E();
                showXHTML_td('class="font06"', '');
              showXHTML_tr_E();
                $dis = '';
                if (($prog_type == 'exam_create.php') || ($RS['publish'] == 'prepare') || ($RS['publish'] == 'close')) {
                    $dis = ' style="display: none;"';
                }

                /*比重*/
                showXHTML_tr_B('class="bg03 font01"');
                  showXHTML_td_B('');
                      echo '<span class="strong-note">*</span>';
                      echo $MSG['exam_percent'][$sysSession->lang];
                  showXHTML_td_E();
                  showXHTML_td_B();
                    showXHTML_input('text', 'percent', (isset($RS) ? floatval($RS['percent']) : '0.0'), '', (($RS['count_type']!=none) ? 'size="5" class="box02" onchange="typeCheck(this, \'float\');" onkeyup="float6Only(this, value);"':'size="5" class="box02"  disabled onchange="typeCheck(this, \'float\');" onkeypress="float6Only(this, value);"')); echo '%';
                  showXHTML_td_E();
                  showXHTML_td('class="font06"', $MSG['exam_percent_hint'][$sysSession->lang]);
                showXHTML_tr_E();

              /*對象*/
              showXHTML_tr_B((QTI_which == 'homework' ? 'class="bg04 font01"' : 'class="bg03 font01"') . ($forGuest ? ' style="display: none"' : ''));
                showXHTML_td_B('');
                    echo '<span class="strong-note">*</span>';
                    echo $MSG['for_target'][$sysSession->lang];
                showXHTML_td_E();
                showXHTML_td('id="aclDisplayPanel_0" ', (QTI_which == 'questionnaire' && $topDir == 'academic') ? $MSG['default_all'][$sysSession->lang] : $MSG['default_student'][$sysSession->lang]);
                showXHTML_td_B();
                  showXHTML_input('button', '', $MSG['toolbtm02'][$sysSession->lang], '', 'id="addACLbtn" class="cssBtn" onclick="acl_hidden_flags = true; init_add_list(0); event.cancelBubble = true;"' . ($forGuest ? ' disabled' : ''));
                showXHTML_td_E();
              showXHTML_tr_E();


              /*分區-繳交作答*/
              showXHTML_tr_B('class="bg03 font01"');
                   showXHTML_td('class="cssTabs" width="117"', $MSG['paying_answer'][$sysSession->lang]);
                   showXHTML_td('class="font06 strong-note" align="right" colspan="2"', $MSG['required'][$sysSession->lang]);
              showXHTML_tr_E();

              showXHTML_tr_B('id="trOpen" class="bg0' . (QTI_which == 'exam' ? 3 : 4) . ' font01"');
                showXHTML_td('', $MSG['enable_duration1'][$sysSession->lang]);
                showXHTML_td_B();
                    $tmp = $sysConn->UnixTimeStamp($RS['begin_time']);
                    $isCheck = (!empty($tmp)) ? true : false;
                    $val = $isCheck ? substr($RS['begin_time'], 0, 16) : sprintf('%04d-%02d-%02d %02d:%02d', $date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes']);
                    $ck = $isCheck ? ' checked' : '';
                    $ds = $isCheck ? '' : ' style="display: none;"';
                    showXHTML_input('checkbox', 'ck_begin_time', '', '', 'id="ck_begin_time' . '" onclick="showDateInput(\'span_begin_time' . '\', this.checked)"'. $ck);
                    echo $MSG['btn_enable'][$sysSession->lang];
                    echo '<span id="span_begin_time'.'"'. $ds .'>' . $MSG['msg_enable_date'][$sysSession->lang];
                    showXHTML_input('text', 'begin_time', $val, '', 'id="begin_time" readonly="readonly" class="cssInput"');
                    echo '</span>';
                showXHTML_td_E();
                showXHTML_td('class="font06"', $MSG['exam_duration1_1'][$sysSession->lang]);
              showXHTML_tr_E();

              showXHTML_tr_B('id="trClose" class="bg0' . (QTI_which == 'exam' ? 4 : 3) . ' font01"');
                showXHTML_td('', $MSG['enable_duration2'][$sysSession->lang]);
                showXHTML_td_B();
                    $tmp = $sysConn->UnixTimeStamp($RS['close_time']);
                    $isCheck = (($tmp<253402185600) && (!empty($tmp))) ? true : false;    // 9999-12-31 00:00:00 表不限
                    $val = $isCheck ? substr($RS['close_time'], 0, 16) : sprintf('%04d-%02d-%02d %02d:%02d', $date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes']);
                    $ck = $isCheck ? ' checked' : '';
                    $ds = $isCheck ? '' : ' style="display: none;"';
                    showXHTML_input('checkbox', 'ck_close_time', '', '', 'id="ck_close_time' . '" onclick="showDateInput(\'span_close_time' . '\', this.checked)"'. $ck);
                    echo $MSG['btn_enable'][$sysSession->lang];
                    echo '<span id="span_close_time'.'"'. $ds .'>' . $MSG['msg_enable_date'][$sysSession->lang];
                    showXHTML_input('text', 'close_time', $val, '', 'id="close_time" readonly="readonly" class="cssInput"');
                    echo '</span>';
                showXHTML_td_E();
                showXHTML_td('class="font06"', $MSG['exam_duration1_2'][$sysSession->lang]);
              showXHTML_tr_E();

              /*可重複繳交*/
              showXHTML_tr_B('class="bg03 font01"' . ($forGuest ? ' style="display: none"' : ''));
                showXHTML_td('', $MSG['rehandin'][$sysSession->lang]);
                showXHTML_td_B();
                  showXHTML_input('checkbox', 'modifiable', 'Y', '', ((isset($RS['modifiable']) && $RS['modifiable'] == 'N') ? '':' checked'));
                  $m = (($RS && strpos($RS['setting'], 'upload') !== FALSE) || (!$RS && QTI_which == 'peer')) ? 1 : 0;
                  showXHTML_input('hidden', 'setting[upload]', $m);
                showXHTML_td_E();
                showXHTML_td('', $MSG['uploading_attachments_always'][$sysSession->lang]);
              showXHTML_tr_E();

              /*分區-評分*/
              showXHTML_tr_B('class="bg03 font01"');
                    showXHTML_td('class="cssTabs" width="117"', $MSG['rating'][$sysSession->lang]);
                    showXHTML_td('class="font06 strong-note" align="right" colspan="2"', $MSG['required'][$sysSession->lang]);
                showXHTML_tr_E();

              /*評分標準說明*/
              showXHTML_tr_B('class="bg04 font01"');
                showXHTML_td_B('');
                    echo '<span class="strong-note">*</span>';
                    echo $MSG['rating_notice'][$sysSession->lang];
                showXHTML_td_E();
                showXHTML_td_B();
                    $oEditor = new wmEditor;
                    $oEditor->addContType('isHTML', 1);
                    $oEditor->setValue(($RS['assess'])?htmlspecialchars($RS['assess']):'&nbsp;');
                    $oEditor->setConfig('ToolbarStartExpanded', false);
                    $oEditor->generate('rating_criteria_1', '440', '205');
                    showXHTML_input('hidden', 'rating_criteria');
                showXHTML_td_E();
                showXHTML_td('class="font06"', $MSG['rating criteria'][$sysSession->lang]);
              showXHTML_tr_E();

              /*評分人員*/
              showXHTML_tr_B('class="bg0' . (QTI_which == 'exam' ? 3 : 4) . ' font01"');
                showXHTML_td('', $MSG['rating_member'][$sysSession->lang]);
                showXHTML_td_B();
                    $isCheck = ($RS['peer_percent'] >= 1) ? true : false;
                    $ck = $isCheck ? ' checked' : '';
                    $ds = $isCheck ? '' : ' style="display: none;"';
                    echo '<label for="ck_peer_assessment">';
                        showXHTML_input('checkbox', 'ck_peer_assessment', '', '', 'id="ck_peer_assessment' . '" onclick="statListAsseShow(\'spanPeerAsse' . '\', this.checked)"'. $ck);
                        echo $MSG['peer_assessment'][$sysSession->lang];
                    echo '</label>';
                    echo '<span id="spanPeerAsse"'. $ds .'>';
                        echo '，' . $MSG['exam_percent'][$sysSession->lang];
                        showXHTML_input('text', 'peer_percent', (isset($RS) ? floatval($RS['peer_percent']) : '0'), '', 'id="peer_percent" class="cssInput" maxlength="3" size="4" onblur="return sumPercent(this,value)"');
                        echo '%，' . $MSG['minimum_number'][$sysSession->lang];
                        showXHTML_input('select', 'peer_times', array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5), (isset($RS) ? floatval($RS['peer_times']) : 3), 'id="peer_times" size="1" style="width: 38px" class="box02"');
                    echo "</span>";

                    echo '<br>';

                    $isCheck = ($RS['self_percent'] >= 1) ? true : false;
                    $ck = $isCheck ? ' checked' : '';
                    $ds = $isCheck ? '' : ' style="display: none;"';
                    echo '<label for="ck_self_assessment">';
                        showXHTML_input('checkbox', 'ck_self_assessment', '', '', 'id="ck_self_assessment' . '" onclick="statListAsseShow(\'spanSelfAsse' . '\', this.checked)"'. $ck);
                        echo $MSG['self_assessment'][$sysSession->lang];
                    echo '</label>';
                    echo '<span id="spanSelfAsse"'. $ds .'>';
                        echo '，' . $MSG['exam_percent'][$sysSession->lang];
                        showXHTML_input('text', 'self_percent', (isset($RS) ? floatval($RS['self_percent']) : '0'), '', 'id="self_percent" class="cssInput" maxlength="3" size="4" onblur="return sumPercent(this,value)"');
                        echo '%';
                    echo "</span>";

                    echo '<br>';

                    /*互評順序*/
                    $isCheck = ($RS['peer_percent'] >= 1 && $RS['self_percent'] >= 1) ? true : false;
                    $ds = $isCheck ? '' : ' style="display: none;"';

                    $assess_relation_flag = ($RS['assess_relation'] === null)? 1 : $RS['assess_relation'];

                    echo '<span id="spanAssessRation"'. $ds .'>';
                        showXHTML_input('radio', 'assess_relation', array(
                            '1' => $MSG['peer_first'][$sysSession->lang],
                            '2' => $MSG['self_first'][$sysSession->lang],
                            '0' => $MSG['no_priority'][$sysSession->lang]
                            ), $assess_relation_flag, 'size="5" class="box02"');
                    echo '</span>';

                    echo '<br>';

                    echo '<label id="lb_teacher_assessment">';
                        echo $MSG['teacher_assessment'][$sysSession->lang] . '，' . $MSG['exam_percent'][$sysSession->lang];
                        echo '<span id="span_teacher_percent">' . ((isset($RS['teacher_percent']) === true)?floatval($RS['teacher_percent']):100) . '</span>';
                        echo '%';
                    echo '</label>';

                showXHTML_td_E();
                showXHTML_td('class="font06"', '');
              showXHTML_tr_E();

              /*開放日期*/
              showXHTML_tr_B('id="trOpen" class="bg0' . (QTI_which == 'exam' ? 3 : 4) . ' font01"');
                showXHTML_td_B('');
                    echo '<span class="strong-note" style="display: none;">*</span>';
                    echo $MSG['rating_open_date'][$sysSession->lang];
                showXHTML_td_E();
                showXHTML_td_B();
                    $tmp = $sysConn->UnixTimeStamp($RS['start_date']);
                    $isCheck = (!empty($tmp)) ? true : false;
                    $val = $isCheck ? substr($RS['start_date'], 0, 16) : sprintf('%04d-%02d-%02d %02d:%02d', $date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes']);
                    $ck = $isCheck ? ' checked' : '';
                    $ds = $isCheck ? '' : ' style="display: none;"';
                    showXHTML_input('checkbox', 'ck_rating_begin_time', '', '', 'id="ck_rating_begin_time' . '" onclick="showDateInput(\'span_rating_begin_time' . '\', this.checked)"'. $ck);
                    echo $MSG['btn_enable'][$sysSession->lang];
                    echo '<span id="span_rating_begin_time'.'"'. $ds .'>' . $MSG['msg_enable_date'][$sysSession->lang];
                    showXHTML_input('text', 'rating_begin_time', $val, '', 'id="rating_begin_time" readonly="readonly" class="cssInput"');
                    echo '</span>';
                showXHTML_td_E();
                showXHTML_td('class="font06"', '');
              showXHTML_tr_E();

              showXHTML_tr_B('id="trClose" class="bg0' . (QTI_which == 'exam' ? 4 : 3) . ' font01"');
                showXHTML_td_B('');
                    echo '<span class="strong-note" style="display: none;">*</span>';
                    echo $MSG['rating_close_date'][$sysSession->lang];
                showXHTML_td_E();
                showXHTML_td_B();
                    $tmp = $sysConn->UnixTimeStamp($RS['end_date']);
                    $isCheck = (($tmp<253402185600) && (!empty($tmp))) ? true : false;    // 9999-12-31 00:00:00 表不限
                    $val = $isCheck ? substr($RS['end_date'], 0, 16) : sprintf('%04d-%02d-%02d %02d:%02d', $date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes']);
                    $ck = $isCheck ? ' checked' : '';
                    $ds = $isCheck ? '' : ' style="display: none;"';
                    showXHTML_input('checkbox', 'ck_rating_close_time', '', '', 'id="ck_rating_close_time' . '" onclick="showDateInput(\'span_rating_close_time' . '\', this.checked)"'. $ck);
                    echo $MSG['btn_enable'][$sysSession->lang];
                    echo '<span id="span_rating_close_time"'. $ds .'>' . $MSG['msg_enable_date'][$sysSession->lang];
                    showXHTML_input('text', 'rating_close_time', $val, '', 'id="rating_close_time" readonly="readonly" class="cssInput"');
                    echo '</span>';
                showXHTML_td_E();
                showXHTML_td('class="font06"', '');
              showXHTML_tr_E();

              /*評分方式*/
              showXHTML_tr_B((QTI_which == 'homework' ? 'class="bg04 font01"' : 'class="bg03 font01"'));
                showXHTML_td_B('');
                    echo '<span class="strong-note">*</span>';
                    echo $MSG['rating_mode'][$sysSession->lang];
                showXHTML_td_E();
                showXHTML_td_B();

                    // 取本課程所有老師設定有啟用的的評量表 B
                    $rsActivities = dbGetStMr('WM_term_major','distinct username','course_id = ' . sprintf('%08u', $sysSession->course_id) . ' and role&' . ($sysRoles['teacher'] | $sysRoles['assistant']));
                    $teacher = array();
                    if($rsActivities) {
                        while(!$rsActivities->EOF) {
                            $teacher[] = $rsActivities->fields['username'];
                            $rsActivities->MoveNext();
                        }
                    }
                    $assess_way = array();
                    $assess_way[0] = $MSG['score_publish'][$sysSession->lang];
                    if (count($teacher) >= 1) {
                        $rsAssess_way = dbGetStMr('WM_evaluation','eva_id, caption, creator','creator in (\'' . sprintf('%s', implode("','", $teacher)) . '\') and enable = 1 order by eva_id');
                        $teacher = array();
                        if($rsAssess_way) {
                            while(!$rsAssess_way->EOF) {
                                $user = getUserDetailData($rsAssess_way->fields['creator']);
                                $assess_way[$rsAssess_way->fields['eva_id']] = $rsAssess_way->fields['caption'] . ' - ' . $user['realname'];
                                $rsAssess_way->MoveNext();
                            }
                        }
                    }
                    // 取本課程所有老師設定有啟用的的評量表 E

                  showXHTML_input('select', 'assess_way', $assess_way, $RS['assess_way'], 'size="1" class="box02" style="width:160px"');
                showXHTML_td_E();
                showXHTML_td('class="font06"', $MSG['count_type_hint2'][$sysSession->lang]);
              showXHTML_tr_E();

              showXHTML_tr_B('class="bg04 font01"' . (QTI_which == 'exam' ? '' : ' style="display: none"'));
                showXHTML_td('', $MSG['count_type'][$sysSession->lang]);
                showXHTML_td_B();
                  showXHTML_input('select', 'count_type', array('none'      => $MSG['count_type0'][$sysSession->lang],
                                                                'first'      => $MSG['count_type1'][$sysSession->lang],
                                                                'last'      => $MSG['count_type2'][$sysSession->lang],
                                                                'max'      => $MSG['count_type3'][$sysSession->lang],
                                                                'min'      => $MSG['count_type4'][$sysSession->lang],
                                                                'average' => $MSG['count_type5'][$sysSession->lang]
                                                               ), $RS['count_type'], 'style="width: 160px" class="box02" onChange="document.getElementById(\'percent\').disabled=(this.value==\'none\');if(this.value==\'none\'){document.getElementById(\'percent\').value=0.0;}"');
                showXHTML_td_E();
                showXHTML_td('class="font06"', $MSG['count_type_hint'][$sysSession->lang]);
              showXHTML_tr_E();

              showXHTML_tr_B((QTI_which == 'homework' ? 'class="bg03 font01"' : 'class="bg04 font01"') . ' style="display: none"');
                showXHTML_td('', $MSG['corrector'][$sysSession->lang]);
                if (QTI_which == 'questionnaire' && $topDir == 'academic')
                    showXHTML_td('id="aclDisplayPanel_1" ', $MSG['default_manager'][$sysSession->lang]);
                else
                    showXHTML_td('id="aclDisplayPanel_1" ', $MSG['default_teacher'][$sysSession->lang]);
                showXHTML_td_B();
                  showXHTML_input('button', '', $MSG['toolbtm01'][$sysSession->lang], '', 'class="cssBtn" onclick="acl_hidden_flags = true; init_add_list(1); event.cancelBubble = true;"');
                showXHTML_td_E();
              showXHTML_tr_E();

              showXHTML_tr_B('class="bg03 font01"' . (QTI_which == 'exam' ? '' : ' style="display: none"'));
                showXHTML_td('', $MSG['exam_times'][$sysSession->lang]);
                showXHTML_td_B();
                showXHTML_input('text', 'do_times', (isset($RS['do_times']) ? intval($RS['do_times']) : 1), '', 'size="5" class="box02" onchange="typeCheck(this, \'int\');" onkeypress="intOnly();"'); echo $MSG['exam_times1'][$sysSession->lang];
                showXHTML_td_E();
                showXHTML_td('class="font06"', $MSG['exam_times_hint'][$sysSession->lang]);
              showXHTML_tr_E();

              showXHTML_tr_B('class="bg04 font01"' . (QTI_which == 'exam' ? '' : ' style="display: none"'));
                showXHTML_td('', $MSG['exam_duration'][$sysSession->lang]);
                showXHTML_td_B();
                  showXHTML_input('text', 'do_interval', (isset($RS['do_interval']) ? intval($RS['do_interval']) : 60), '', 'size="5" class="box02" onchange="typeCheck(this, \'int\');" onkeypress="intOnly();"'); echo $MSG['minute'][$sysSession->lang];
                showXHTML_td_E();
                showXHTML_td('class="font06"', $MSG['exam_duration_hint'][$sysSession->lang]);
              showXHTML_tr_E();

              showXHTML_tr_B('class="bg03 font01"' . (QTI_which == 'exam' ? '' : ' style="display: none"'));
                showXHTML_td('', $MSG['item_per_page'][$sysSession->lang]);
                showXHTML_td_B();
                  echo $MSG['each_page'][$sysSession->lang]; showXHTML_input('text', 'item_per_page', (isset($RS['item_per_page']) ? intval($RS['item_per_page']) : 0), '', 'size="5" class="box02" onchange="typeCheck(this, \'int\');" onkeypress="intOnly();"'); echo $MSG['item'][$sysSession->lang];
                showXHTML_td_E();
                showXHTML_td('class="font06"', $MSG['space_is_all'][$sysSession->lang]);
              showXHTML_tr_E();

              showXHTML_tr_B('class="bg04 font01"' . (QTI_which == 'exam' ? '' : ' style="display: none"'));
                showXHTML_td('', $MSG['flip_control'][$sysSession->lang]);
                showXHTML_td_B();
                  showXHTML_input('select', 'ctrl_paging', array('none'       => $MSG['unlimited'][$sysSession->lang],
                                                                 'can_return' => $MSG['flip_control1'][$sysSession->lang],
                                                                 'lock'       => $MSG['flip_control2'][$sysSession->lang]
                                                                ), $RS['ctrl_paging'], 'size="1" style="width: 160px" class="box02"');
                showXHTML_td_E();
                showXHTML_td('class="font06"', $MSG['flip_control_hint'][$sysSession->lang]);
              showXHTML_tr_E();

              showXHTML_tr_B('class="bg03 font01"' . (QTI_which == 'exam' ? '' : ' style="display: none"'));
                showXHTML_td('', $MSG['window_control'][$sysSession->lang]);
                showXHTML_td_B();
                  showXHTML_input('select', 'ctrl_window', array('none' => $MSG['unlimited'][$sysSession->lang],
                                                                 'lock' => $MSG['window_control1'][$sysSession->lang]
                                                                ), $RS['ctrl_window'], 'size="1" style="width: 160px" class="box02"');
                showXHTML_td_E();
                showXHTML_td('class="font06"', $MSG['window_control_hint'][$sysSession->lang]);
              showXHTML_tr_E();

              showXHTML_tr_B('class="bg04 font01"' . (QTI_which == 'exam' ? '' : ' style="display: none"'));
                showXHTML_td('', $MSG['timeout_control'][$sysSession->lang]);
                showXHTML_td_B();
                  showXHTML_input('select', 'ctrl_timeout', array('none'        => $MSG['nop'][$sysSession->lang],
                                                                  'mark'        => $MSG['timeout_control1'][$sysSession->lang],
                                                                  'auto_submit' => $MSG['timeout_control2'][$sysSession->lang]
                                                                  ), $RS['ctrl_timeout'], 'size="1" style="width: 160px" class="box02"');
                showXHTML_td_E();
                showXHTML_td('class="font06"', $MSG['timeout_control_hint'][$sysSession->lang]);
              showXHTML_tr_E();

            /*分區-公告*/
                showXHTML_tr_B('class="bg03 font01"');
                    showXHTML_td('class="cssTabs" width="117"', $MSG['chat_tone06'][$sysSession->lang]);
                    showXHTML_td('class="font06 strong-note" align="right" colspan="2"', $MSG['required'][$sysSession->lang]);
                showXHTML_tr_E();

              showXHTML_tr_B((QTI_which == 'homework' ? 'class="bg04 font01"' : 'class="bg03 font01"'));
                showXHTML_td('', $MSG['score_publish_' . QTI_which][$sysSession->lang]);
                showXHTML_td_B();

                  showXHTML_input('select', 'announce_type', array('never'       => $MSG['score_publish0'][$sysSession->lang],
                                                                   'now'         => $MSG['score_publish1'][$sysSession->lang],
                                                                   'close_time'  => $MSG['score_publish2'][$sysSession->lang],
                                                                   'user_define' => $MSG['score_publish3'][$sysSession->lang]
                                                                  ), $RS['announce_type'], 'size="1" style="width: 160px" class="box02" onchange="customTime(this.value);"');
                  echo '<span id="customTimePal" style="display:'.(($RS['announce_type'] == 'user_define')?'':'none').'">';
                    $tmp = $sysConn->UnixTimeStamp($RS['announce_time']);
                    $val = (!empty($tmp)) ? substr($RS['announce_time'], 0, 16) : sprintf('%04d-%02d-%02d %02d:%02d', $date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes']);
                    echo $MSG['msg_enable_date'][$sysSession->lang];
                    showXHTML_input('text', 'announce_time', $val, '', 'id="announce_time" readonly="readonly" class="cssInput"');
                  echo '</span>';

                showXHTML_td_E();
                showXHTML_td('class="font06"', $MSG['score_publish_hint'][$sysSession->lang]);
              showXHTML_tr_E();

              showXHTML_tr_B('class="bg04 font01" id="trStatus"');
                showXHTML_td('', $MSG['achievement_results'][$sysSession->lang]);
                showXHTML_td_B();
                    $rdoScorePublishes[1] = $MSG['announce_type1'][$sysSession->lang];
                    $rdoScorePublishes[2] = $MSG['msgPublish'][$sysSession->lang];

                    // 取成績設定
                    $rsGradeList = dbGetStMr('WM_grade_list','publish_begin, publish_end','course_id = ' . sprintf('%08u', $sysSession->course_id) . ' and property = ' . sprintf('%09u', $_POST['lists']) . ' and source = 4');

                    if($rsGradeList) {
                        while(!$rsGradeList->EOF) {
                            $score_begin_time = $rsGradeList->fields['publish_begin'];
                            $score_close_time = $rsGradeList->fields['publish_end'];

                            $rsGradeList->MoveNext();
                        }
                    }

                    if (($score_begin_time === '0000-00-00 00:00:00' && $score_close_time === '0000-00-00 00:00:00') || $rsGradeList->RecordCount() === 0) {
                        $rdoScorePublishValue = 1;
                    } else {
                        $rdoScorePublishValue = 2;
                    }

                    showXHTML_input('radio', 'rdoScorePublish', $rdoScorePublishes, $rdoScorePublishValue, 'onClick="statListScoreDateShow(this.value);" size="5" class="box02"');

                    $ds = $rdoScorePublishValue === 2 ? '' : ' style="display: none;"';

                    echo '<br>';
                    echo '<div id="divScore" ' . $ds . '>';

                        $tmp = $sysConn->UnixTimeStamp($score_begin_time);
                        $isCheck = (!empty($tmp)) ? true : false;
                        $val = $isCheck ? substr($score_begin_time, 0, 16) : sprintf('%04d-%02d-%02d %02d:%02d', $date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes']);
                        $ck = $isCheck ? ' checked' : '';
                        $ds = $isCheck ? '' : ' style="display: none;"';
                        showXHTML_input('checkbox', 'ck_score_begin_time', '', '', 'id="ck_score_begin_time' . '" onclick="showDateInput(\'span_score_begin_time' . '\', this.checked)"'. $ck);
                        echo $MSG['msg_enable_begin'][$sysSession->lang];
                        echo '<span id="span_score_begin_time'.'"'. $ds .'>' . $MSG['msg_enable_date'][$sysSession->lang];
                        showXHTML_input('text', 'score_begin_time', $val, '', 'id="score_begin_time" readonly="readonly" class="cssInput"');
                        echo '</span>';

                        echo '<br>';

                        $tmp = $sysConn->UnixTimeStamp($score_close_time);
                        $isCheck = (($tmp<253402185600) && (!empty($tmp))) ? true : false;    // 9999-12-31 00:00:00 表不限
                        $val = $isCheck ? substr($score_close_time, 0, 16) : sprintf('%04d-%02d-%02d %02d:%02d', $date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes']);
                        $ck = $isCheck ? ' checked' : '';
                        $ds = $isCheck ? '' : ' style="display: none;"';
                        showXHTML_input('checkbox', 'ck_score_close_time', '', '', 'id="ck_score_close_time' . '" onclick="showDateInput(\'span_score_close_time' . '\', this.checked)"'. $ck);
                        echo $MSG['msg_enable_end'][$sysSession->lang];
                        echo '<span id="span_score_close_time'.'"'. $ds .'>' . $MSG['msg_enable_date'][$sysSession->lang];
                        showXHTML_input('text', 'score_close_time', $val, '', 'id="score_close_time" readonly="readonly" class="cssInput"');
                        echo '</span>';

                    echo '</div>';
                showXHTML_td_E();
                showXHTML_td('class="font06"', '');
              showXHTML_tr_E();

              showXHTML_tr_B('class="bg02 font01"');
                showXHTML_td_B('align="right" colspan="3" nowrap');
                  showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]   , '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                    showXHTML_input('button', '', $MSG['next_step'][$sysSession->lang], '', 'class="cssBtn" onclick="saveContent(4);"');
                  if (isset($RS)) showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="cssBtn" onclick="saveContent();"');
                  showXHTML_input('hidden', 'content');
                  showXHTML_input('hidden', 'acl_lists');
                  showXHTML_input('hidden', 'item_cramble');
                  showXHTML_input('hidden', 'random_pick');
if ($prog_type == 'exam_modify.php')
{
                  showXHTML_input('hidden', 'exam_id', $RS['exam_id']);
                  showXHTML_input('hidden', 'ticket', $ticket);
}
                showXHTML_td_E();
              showXHTML_tr_E();

            showXHTML_table_E();
            showXHTML_script('inline', 'checkedTab1();');
          showXHTML_form_E();
        echo "</div>\n";

// TAB-2
        echo '<div id="tabContent1" style="display: none">', "\n";
            showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="1000" style="border-collapse: collapse" class="box01"');
                showXHTML_tr_B('class="bg03 font01"');
                  showXHTML_td_B('height="80"');
                  showXHTML_input('radio', 'randomMode', array(1 => "{$MSG['msg_select'][$sysSession->lang]}",
                                                               2 => "{$MSG['msg_system'][$sysSession->lang]}",
                                                              ), (isset($RS) ? ($xx == 'selectRandomMode(2);' ? 2 : 1) : (QTI_which == 'exam' ? 0 : 1)), 'id="randomMode" onclick="selectRandomMode(this.value);"', '<br>');
                showXHTML_td_E();
                showXHTML_tr_E();
            showXHTML_table_E();

          showXHTML_form_B('method="POST" style="display:none"', 'searchForm');
            showXHTML_input('hidden', 'pages', '1');
            showXHTML_input('hidden', 'rows_page_share', (isset($rows_page_share)?$rows_page_share:-1));
            showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="1000" style="border-collapse: collapse" class="box01"');

              showXHTML_tr_B('class="bg02 font01"');
                showXHTML_td('colspan="2"', $MSG['search_hint'][$sysSession->lang]);
                showXHTML_td_B('align="right" nowrap');
                  showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]   , '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                  showXHTML_input('button', '', $MSG['prev_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(0);"');
                  showXHTML_input('button', '', $MSG['next_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(2);"');
                  if (isset($RS)) showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="cssBtn" onclick="saveContent();"');
                showXHTML_td_E();
              showXHTML_tr_E();

ob_start();
              showXHTML_tr_B('class="bg03 font01"');
                showXHTML_td('align="right" rowspan="3"', $MSG['search_proviso'][$sysSession->lang]);
                showXHTML_td_B('colspan="2"');
                  showXHTML_input('checkbox', 'isVersion', 'ON', '', ($irgs['version'][0] ? ' checked':'')); echo $MSG['version'][$sysSession->lang];
                  showXHTML_input('text', 'version', $irgs['version'][1], '', 'size="4" onkeyup="checkSelect(this);" class="box02"'); echo str_repeat('&nbsp;', 5);
                  showXHTML_input('checkbox', 'isVolume', 'ON', '', ($irgs['volume'][0] ? ' checked':'')); echo $MSG['volume'][$sysSession->lang];
                  showXHTML_input('text', 'volume', $irgs['volume'][1], '', 'size="4" onkeyup="checkSelect(this);" class="box02"'); echo str_repeat('&nbsp;', 5);
                  showXHTML_input('checkbox', 'isChapter', 'ON', '', ($irgs['chapter'][0] ? ' checked':'')); echo $MSG['chapter'][$sysSession->lang];
                  showXHTML_input('text', 'chapter', $irgs['chapter'][1], '', 'size="4" onkeyup="checkSelect(this);" class="box02"'); echo str_repeat('&nbsp;', 5);
                  showXHTML_input('checkbox', 'isParagraph', 'ON', '', ($irgs['paragraph'][0] ? ' checked':'')); echo $MSG['paragraph'][$sysSession->lang];
                  showXHTML_input('text', 'paragraph', $irgs['paragraph'][1], '', 'size="4" onkeyup="checkSelect(this);" class="box02"'); echo str_repeat('&nbsp;', 5);
                  showXHTML_input('checkbox', 'isSection', 'ON', '', ($irgs['section'][0] ? ' checked':'')); echo $MSG['section'][$sysSession->lang];
                  showXHTML_input('text', 'section', $irgs['section'][1], '', 'size="4" onkeyup="checkSelect(this);" class="box02"'); echo str_repeat('&nbsp;', 3);
                showXHTML_td_E();
              showXHTML_tr_E();

              showXHTML_tr_B('class="bg04 font01"');
                showXHTML_td_B('colspan="2"');
                  echo '<table class="font01" style="display: inline"><tr><td>';
                  showXHTML_input('checkbox', 'isType', 'ON', '', ($irgs['type'][0] ? ' checked':'')); echo $MSG['item_type'][$sysSession->lang];
                  echo '</td><td class="item_type">';
                  $item_types = array(1 => $MSG['item_type1'][$sysSession->lang],
                                      2 => $MSG['item_type2'][$sysSession->lang],
                                      3 => $MSG['item_type3'][$sysSession->lang],
                                      4 => $MSG['item_type4'][$sysSession->lang],
                                      5 => $MSG['item_type5'][$sysSession->lang],
                                      6 => $MSG['item_type6'][$sysSession->lang],
                                      7 => $MSG['item_type7'][$sysSession->lang]
                                     );
                  if (QTI_which != 'exam' || !defined('sysEnableRecordingAttachmentExamType') || !sysEnableRecordingAttachmentExamType) array_pop($item_types);
                  foreach ($item_types as $k => $v) {
                    $checked = ($k == $irgs['type'][1]) ? ' checked' : '';
                    showXHTML_input('checkbox', 'type', $k, '', 'onclick="checkSelect2(this)"' . $checked);
                    echo $v . '<br />';
                  }
                  echo '</td><td>', str_repeat('&nbsp;', 5), '</td><td>';
                    showXHTML_input('checkbox', 'isLevel', 'ON', '', ($irgs['level'][0] ? ' checked':'')); echo $MSG['hard_level'][$sysSession->lang];
                    echo '</td><td class="hard_level">';
                    $item_levels = array(
                        1 => $MSG['hard_level1'][$sysSession->lang],
                        2 => $MSG['hard_level2'][$sysSession->lang],
                        3 => $MSG['hard_level3'][$sysSession->lang],
                        4 => $MSG['hard_level4'][$sysSession->lang],
                        5 => $MSG['hard_level5'][$sysSession->lang]
                    );
                    foreach ($item_levels as $k => $v) {
                        $checked = ($k == $irgs['level'][1]) ? ' checked' : '';
                        showXHTML_input('checkbox', 'type', $k, '', 'onclick="checkSelect2(this)"' . $checked);
                        echo $v . '<br />';
                    }
                  echo '</td></tr></table>';
                showXHTML_td_E();
              showXHTML_tr_E();

              showXHTML_tr_B('class="bg03 font01"');
                showXHTML_td_B('colspan="2"');
                  showXHTML_input('checkbox', 'isFulltext', 'ON', '', ($irgs['fulltext'][0] ? ' checked':'')); echo $MSG['key_words'][$sysSession->lang];
                  showXHTML_input('text', 'fulltext', ($irgs['fulltext'][1] ? $irgs['fulltext'][1] : $MSG['key_words_hint'][$sysSession->lang]), '', 'size="30" class="box02" onfocus="this.value=\'\';" onkeyup="checkSelect(this);"');
                showXHTML_td_E();
              showXHTML_tr_E();
                $search_panel = ob_get_contents();
                ob_end_flush();

                if (preg_match_all('!(<input type="text" name="\w+" value="[\w,]*" size=")4(" [^>]*>)\s&nbsp;&nbsp;&nbsp;!isU', $search_panel, $regs)) {
                    foreach($regs[1] as $k => $v) $regs[1][$k] .= '10' . $regs[2][$k];
                    $search_panel = str_replace($regs[0], $regs[1], $search_panel);
                }

                if (preg_match('!<select name="type" [^>]*>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*</select>!isU', $search_panel, $regs)) {        $org = array_shift($regs);
                    $x = array();
                    for($i=0; $i<18; $i+=3) $x[$regs[$i]] = $regs[$i+2];
                    ob_start();
                    showXHTML_input('checkboxes', 'type[]', $x, explode(',',$irgs['type'][1]), 'onclick="if (this.checked) parentNode.previousSibling.firstChild.checked=true;"', '<br>');
                    $replace_type = ob_get_contents();
                    ob_end_clean();
                    $search_panel = str_replace($org, $replace_type, $search_panel);
                    unset($regs, $org, $x, $replace_type);
                }
                if (preg_match('!<select name="level" [^>]*>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*</select>!isU', $search_panel, $regs)) {
                    $org = array_shift($regs);
                    $x = array();
                    for($i=0; $i<15; $i+=3) $x[$regs[$i]] = $regs[$i+2];
                    ob_start();
                    showXHTML_input('checkboxes', 'level[]', $x, explode(',',$irgs['level'][1]), 'onclick="if (this.checked) parentNode.previousSibling.firstChild.checked=true;"', '<br>');
                    $replace_type = ob_get_contents();
                    ob_end_clean();
                    $search_panel = str_replace($org, $replace_type, $search_panel);
                    unset($regs, $org, $x, $replace_type);
                }

              showXHTML_tr_B('class="bg04 font01"');
                showXHTML_td('align="right"', $MSG['search_scope'][$sysSession->lang]);
                showXHTML_td_B('colspan="2"');
                  showXHTML_input('select', 'scope', array(1 => $MSG['search_scope1'][$sysSession->lang],
                                                           2 => $MSG['search_scope2'][$sysSession->lang],
                                                           3 => $MSG['search_scope3'][$sysSession->lang]
                                                          ), 1, 'class="box02" style="display: none"');
                  showXHTML_input('button', '', $MSG['start_search'][$sysSession->lang], '', 'class="cssBtn" onclick="this.disabled=true; search_item(); this.disabled=false;"');
                showXHTML_td_E();
              showXHTML_tr_E();

              showXHTML_tr_B('class="bg02 font01"');
                showXHTML_td('colspan="2"', $MSG['search_hint'][$sysSession->lang]);
                showXHTML_td_B('align="right" nowrap');
                  showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]   , '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                  showXHTML_input('button', '', $MSG['prev_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(0);"');
                  showXHTML_input('button', '', $MSG['next_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(2);"');
                  if (isset($RS)) showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="cssBtn" onclick="saveContent();"');
                showXHTML_td_E();
              showXHTML_tr_E();

              showXHTML_tr_B();
                showXHTML_td('width="80"', '');
                showXHTML_td('width="540"', '');
                showXHTML_td('width="180"', '');
              showXHTML_tr_E();

            showXHTML_table_E();
          showXHTML_form_E();

          showXHTML_form_B('method="POST" style="display:none"', 'randomForm');

          if (isset($recall))
          {
            echo str_replace('%num_limit%', $CourseExamQuestionsLimit, $recall);
          }else{

            showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="1000" style="border-collapse: collapse" class="box01"');

              showXHTML_tr_B('class="bg02 font01"');
                showXHTML_td_B('colspan="3" align="right" nowrap');
                  showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]   , '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                  showXHTML_input('button', '', $MSG['prev_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(0);"');
                  showXHTML_input('button', '', $MSG['next_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(4);"');
                  if (isset($RS)) showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="cssBtn" onclick="saveContent();"');
                showXHTML_td_E();
              showXHTML_tr_E();

            showXHTML_tr_B('class="bg03 font01"');
            $newmsg = str_replace('%num_limit%', $CourseExamQuestionsLimit, $MSG['message'][$sysSession->lang]);
            showXHTML_td('colspan="3"', $newmsg);
            showXHTML_tr_E();

            showXHTML_tr_B('class="bg04 font01"');
              showXHTML_td('align="right"', $MSG['msg_random'][$sysSession->lang]);
              showXHTML_td_B('colspan="2"');
                showXHTML_input('checkbox', 'immediate_random_pick', 'immediate_random_pick', '', 'onclick="randomCheck2(this);"' . (isset($irgs)?' checked':'')); echo $MSG['total'][$sysSession->lang];
                showXHTML_input('text', 'immediate_random_pick_amount', $irgs['amount'][1], '', 'size="5" onchange="typeCheck(this, \'int\');if (parseInt(this.value)>MaxPickedNum){alert(msg_overNumber); this.value=MaxPickedNum; this.form.immediate_random_pick_amount.focus();event.cancelBubble = true;}" onkeyup="intOnly();"'); echo $MSG['item'][$sysSession->lang];
                showXHTML_input('button', '', $MSG['msg_more'][$sysSession->lang], '', 'class="cssBtn" onclick="parentNode.parentNode.parentNode.parentNode.rows[3].cells[0].appendChild(parentNode.parentNode.parentNode.parentNode.rows[3].cells[0].lastChild.cloneNode(true));"');
              showXHTML_td_E();
            showXHTML_tr_E();

        echo '<tr><td colspan="3" width="100%"><table style="display: inline">', $search_panel , '</table></td></tr>';

            showXHTML_tr_B('class="bg04 font01"');
              showXHTML_td('align="right"', $MSG['score_assigned1'][$sysSession->lang]);
              showXHTML_td_B('colspan="2"');
                echo $MSG['total_score'][$sysSession->lang];
                showXHTML_input('text', 'immediate_random_pick_score', $irgs['score'][1], '', 'size="5" onchange="typeCheck(this, \'float\');" onkeyup="float6Only(this, value);"');
              showXHTML_td_E();
            showXHTML_tr_E();

              showXHTML_tr_B('class="bg02 font01"');
                showXHTML_td_B('colspan="3" align="right" nowrap');
                  showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]   , '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                  showXHTML_input('button', '', $MSG['prev_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(0);"');
                  showXHTML_input('button', '', $MSG['next_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(4);"');
                  if (isset($RS)) showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="cssBtn" onclick="saveContent();"');
                showXHTML_td_E();
              showXHTML_tr_E();

              showXHTML_tr_B();
                showXHTML_td('width="140"', '');
                showXHTML_td('width="480"', '');
                showXHTML_td('width="180"', '');
              showXHTML_tr_E();

            showXHTML_table_E();
          }
          showXHTML_form_E();

        echo "</div>\n";

// TAB-3
        echo '<div id="tabContent2" style="display: none">', "\n";

          showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="1000" style="border-collapse: collapse" class="box01"');

            showXHTML_tr_B('class="bg02 font01"');
              showXHTML_td_B('colspan="2" align="right" nowrap');
                showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]  , '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                showXHTML_input('button', '', $MSG['prev_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(1);"');
                showXHTML_input('button', '', $MSG['next_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(' . (QTI_which == 'exam' ? 3 : 4) . ');"');
                if (isset($RS)) showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="cssBtn" onclick="saveContent();"');
              showXHTML_td_E();
            showXHTML_tr_E();
/*
            showXHTML_tr_B('class="bg03 font01"');
              showXHTML_td('colspan="2"', '<a href="javascript:;" onclick="this.nextSibling.style.display=(this.nextSibling.style.display==\'\'?\'none\':\'\'); return false;">help</a><ul style="display: none">' . $MSG['exam_list_help'][$sysSession->lang] . '</ul>');
            showXHTML_tr_E();
*/
            showXHTML_tr_B('class="bg03 font01"');
              showXHTML_td_B('width="110" height="220" align="center"');
                echo "<div id=\"toolPanel\" style=\"position: absolute; left: 20px; top: 100px; width: 90px\">\n";
                showXHTML_form_B('style="display:inline;"');
                  showXHTML_input('button', '', $MSG['block_btm1'][$sysSession->lang]    , '', 'onclick="paperTuning(1);" style="width: 90px" class="cssBtn"');
                  showXHTML_input('button', '', $MSG['block_btm2'][$sysSession->lang]    , '', 'onclick="paperTuning(2);" style="width: 90px" class="cssBtn"');
                  showXHTML_input('button', '', $MSG['block_btm3'][$sysSession->lang]    , '', 'onclick="paperTuning(3);" style="width: 90px" class="cssBtn"');
                  showXHTML_input('button', '', $MSG['block_btm4'][$sysSession->lang]    , '', 'onclick="paperTuning(4);" style="width: 90px" class="cssBtn"');
                  if (QTI_which != 'questionnaire'){
                  showXHTML_input('button', '', $MSG['assign_score'][$sysSession->lang]  , '', 'onclick="paperTuning(7);" style="width: 90px" class="cssBtn"');
                  showXHTML_input('button', '', $MSG['assign_average'][$sysSession->lang], '', 'onclick="paperTuning(8);" style="width: 90px" class="cssBtn"');
                  }
                  showXHTML_input('button', '', $MSG['block_btm5'][$sysSession->lang]    , '', 'onclick="paperTuning(5);" style="width: 90px" class="cssBtn"');
                  showXHTML_input('button', '', $MSG['block_btm6'][$sysSession->lang]    , '', 'onclick="paperTuning(6);" style="width: 90px" class="cssBtn"');
                  showXHTML_input('button', '', $MSG['msg_select_all'][$sysSession->lang]    , '', 'onclick="paperTuning(9);" style="width: 90px" class="cssBtn"');
                  showXHTML_input('button', '', $MSG['msg_cancel_all'][$sysSession->lang]    , '', 'onclick="paperTuning(10);" style="width: 90px" class="cssBtn"');
                showXHTML_form_E();
                echo "</div>\n";
              showXHTML_td_E();
              showXHTML_td_B('width="690" valign="top"');
                showXHTML_form_B('style="display:inline"', 'paperPanel'); showXHTML_form_E();
              showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="bg02 font01"');
              showXHTML_td_B('colspan="2" align="right" nowrap');
                showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]  , '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                showXHTML_input('button', '', $MSG['prev_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(1);"');
                showXHTML_input('button', '', $MSG['next_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(' . (QTI_which == 'exam' ? 3 : 4) . ');"');
                if (isset($RS)) showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="cssBtn" onclick="saveContent();"');
              showXHTML_td_E();
            showXHTML_tr_E();

          showXHTML_table_E();

        echo "</div>\n";

// TAB-4
        echo '<div id="tabContent3" style="display: none">', "\n";
        $item_cramble = isset($RS['item_cramble']) ? explode(',', $RS['item_cramble']) : array();

        showXHTML_form_B('method="POST" style="display:inline"', 'randomSetup');
          showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="1000" style="border-collapse: collapse" class="box01"');

            showXHTML_tr_B('class="bg02 font01"');
              showXHTML_td_B('colspan="2" align="right" nowrap');
                showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]  , '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                showXHTML_input('button', '', $MSG['prev_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(2);"');
                showXHTML_input('button', '', $MSG['next_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(4);"');
                if (isset($RS)) showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="cssBtn" onclick="saveContent();"');
              showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="bg03 font01"');
              showXHTML_td('width="150" align="right"', $MSG['shuffle_enable'][$sysSession->lang]);
              showXHTML_td_B('width="610"');
                showXHTML_input('checkbox', 'enableRandom', 'enable', '', 'onclick="randomCheck(this);"' . (in_array('enable', $item_cramble) ? ' checked' : ''));
              showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="bg04 font01"');
              showXHTML_td('rowspan="3" align="right"', $MSG['shuffle_item'][$sysSession->lang]);
              showXHTML_td_B();
                showXHTML_input('checkbox', 'random_mode[]', 'section', '', (in_array('enable', $item_cramble) ? '' : ' disabled') . (in_array('section', $item_cramble) ? ' checked' : '')); echo $MSG['shuffle1'][$sysSession->lang], '<br>';
              showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="bg03 font01"');
              showXHTML_td_B();
                showXHTML_input('checkbox', 'random_mode[]', 'item', '', (in_array('enable', $item_cramble) ? '' : ' disabled') . (in_array('item', $item_cramble) ? ' checked' : '')); echo $MSG['shuffle2'][$sysSession->lang], '<br>';
              showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="bg04 font01"');
              showXHTML_td_B();
                showXHTML_input('checkbox', 'random_mode[]', 'choice', '', (in_array('enable', $item_cramble) ? '' : ' disabled') . (in_array('choice', $item_cramble) ? ' checked' : '')); echo $MSG['shuffle3'][$sysSession->lang];
              showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="bg03 font01"');
              showXHTML_td('align="right"', $MSG['random_select'][$sysSession->lang]);
              showXHTML_td_B();
                showXHTML_input('checkbox', 'randomPick', 'random_pick', '', 'onclick="if (this.checked) {alert(\'' . $MSG['message1'][$sysSession->lang] . '\'); document.getElementById(\'random\').disabled = false;} else document.getElementById(\'random\').disabled = true;" ' . (in_array('enable', $item_cramble) ? '' : ' disabled') . (in_array('random_pick', $item_cramble) ? ' checked' : '')); echo $MSG['total'][$sysSession->lang];
                showXHTML_input('text', 'random', $RS['random_pick'], '', 'size="5" onchange="typeCheck(this, \'int\');" onkeypress="intOnly();"' . (in_array('enable', $item_cramble) && in_array('random_pick', $item_cramble) ? '' : ' disabled')); echo $MSG['item'][$sysSession->lang];
              showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="bg02 font01"');
              showXHTML_td_B('colspan="2" align="right" nowrap');
                showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]  , '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                showXHTML_input('button', '', $MSG['prev_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(2);"');
                showXHTML_input('button', '', $MSG['next_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(4);"');
                if (isset($RS)) showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="cssBtn" onclick="saveContent();"');
              showXHTML_td_E();
            showXHTML_tr_E();

          showXHTML_table_E();
        showXHTML_form_E();
        echo "</div>\n";

// TAB-5 預覽
        echo '<div id="tabContent4" style="display: none">', "\n";

          showXHTML_table_B('id="previewTable" border="0" cellpadding="3" cellspacing="1" width="1000" style="border-collapse: collapse" class="box01"');

            showXHTML_tr_B('class="bg02 font01"');
                  $mergeCols = ' colspan="3"';
              showXHTML_td_B('align="right" nowrap' . $mergeCols);
                showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]  ,  '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                    $tab = 0;
                showXHTML_input('button', '', $MSG['prev_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(' . $tab . ');"');
                showXHTML_input('button', '', $MSG['complete'][$sysSession->lang],  '', 'class="cssBtn save-content" onclick="saveContent();"');
                if (QTI_which != 'questionnaire' && QTI_which != 'peer') showXHTML_input('button', '', $MSG['hide answer'][$sysSession->lang], '', 'class="cssBtn" onclick="prevExamPaper();"');
                if (QTI_which == 'exam')          showXHTML_input('button', '', $MSG['Export_MHT'][$sysSession->lang],  '', 'class="cssBtn" onclick="ExportContent();"');
              showXHTML_td_E();
            showXHTML_tr_E();

                showXHTML_tr_B('class="bg03 font01"');
                  showXHTML_td_B('nowrap id="examPreview" style="display: none;"');
                  showXHTML_td_E();
                showXHTML_tr_E();
                showXHTML_tr_B('class="bg03 font01"');
                    showXHTML_td_B('width="100"');
                        echo $MSG['exam_name'][$sysSession->lang];
                    showXHTML_td_E();
                    showXHTML_td_B('width="500" colspan="2"');
                        echo '<span id="preview_title">' . $title[$sysSession->lang] . '</sapn>';
                    showXHTML_td_E();
                showXHTML_tr_E();
                showXHTML_tr_B('class="bg04 font01"');
                    showXHTML_td_B('width="100"');
                        echo $MSG['upload_attach'][$sysSession->lang];
                    showXHTML_td_E();
                    showXHTML_td_B('width="400"');
                        showXHTML_input('file', 'cvsfile', '', '', 'size="27" class="cssInput" disabled');
                        showXHTML_input('button', '', $MSG['peer_cede'][$sysSession->lang], '', 'class="cssBtn"');
                    showXHTML_td_E();
                    showXHTML_td_B('');
                        $msgAry = array('%MIN_SIZE%'    =>     '<span style="color: red; font-weight:bold">' . ini_get('upload_max_filesize') . '</span>',
                                        '%MAX_SIZE%'    =>    '<span style="color: red; font-weight:bold">' . ini_get('post_max_size') . '</span>'
                            );
                        echo strtr($MSG['attachement_msg'][$sysSession->lang], $msgAry);
                    showXHTML_td_E();
                showXHTML_tr_E();

            showXHTML_tr_B('class="bg02 font01"');
              showXHTML_td_B('align="right" nowrap' . $mergeCols);
                showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]  ,  '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                showXHTML_input('button', '', $MSG['prev_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(' . (QTI_which == 'exam' ? 'exam_default' : 0) . ');"');
                showXHTML_input('button', '', $MSG['complete'][$sysSession->lang],  '', 'class="cssBtn save-content" onclick="saveContent();"');
              showXHTML_td_E();
            showXHTML_tr_E();

          showXHTML_table_E();
        echo "</div>\n";

      showXHTML_td_E();
    showXHTML_tr_E();
  showXHTML_table_E();

echo '<form id="exportForm" method="POST" action="exam_exportMHT.php" target="empty">';
echo '<input type="hidden" name="table_html" value="">';
echo '</form>';

// TAB 到此為止

// 搜尋結果
  showXHTML_table_B('id="srTable" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; display:none" id="srTable"');
    showXHTML_tr_B();
      showXHTML_td_B();
        $ary = array(array($MSG['search_result'][$sysSession->lang], 'tabsSet',  '')
                    );
        showXHTML_tabs($ary, 1);
      showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_tr_B();
      showXHTML_td_B('class="bg01" id="searchResult"', '&nbsp;');
      showXHTML_td_E();
    showXHTML_tr_E();
  showXHTML_table_E();

// 段落說明文字
  echo '<div id="sectionTextDialog" style="position: absolute; right: 10px; top: 10px; border-width:3; display: none">', "\n";
  showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse"');
    showXHTML_tr_B();
      showXHTML_td_B();
        $ary = array(array($MSG['section_notice'][$sysSession->lang], 'tabsSet',  ''));
        showXHTML_tabs($ary, 1);
      showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_tr_B();
      showXHTML_td_B('class="bg01"');
            showXHTML_form_B('style="display: inline"', 'pmForm');
      showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="box01"');

        showXHTML_tr_B('class="bg02"');
          showXHTML_td_B('align="right"');
            showXHTML_input('button', '', $MSG['sure'][$sysSession->lang], '', 'class="cssBtn" onclick="enterPM();"');
            showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="document.getElementById(\'sectionTextDialog\').style.display=\'none\';"');
          showXHTML_td_E();
        showXHTML_tr_E();

        showXHTML_tr_B('class="bg03"');
          showXHTML_td_B();
            showXHTML_input('textarea', 'presentation_material', '', '', 'rows="6" cols="40" class="box02"');
            showXHTML_input('hidden', 'ident');
          showXHTML_td_E();
        showXHTML_tr_E();

      showXHTML_table_E();
        showXHTML_form_E();
          showXHTML_td_E();
        showXHTML_tr_E();

      showXHTML_table_E();
  echo "</div>\n";

  aclGenerateAclControlPanel2();

echo '<form style="display: none"><input type=hidden" name="saveTemporaryContent" id="saveTemporaryContent"></form>';
showXHTML_body_E();