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
    require_once(sysDocumentRoot . '/lib/filter_spec_char.php');

    if ((QTI_which == 'exam' && sysEnableAppCourseExam == true) || (QTI_which == 'questionnaire' && sysEnableAppQuestionnaire == true) || sysEnableAppISunFuDon) {
        require_once(sysDocumentRoot . '/lang/app_exam.php');
    }

    //ACL begin
    if (QTI_which == 'exam') {
        $sysSession->cur_func='1600200100';
    }
    else if (QTI_which == 'homework') {
        include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');
        $sysSession->cur_func='1700200100';
    }
    else if (QTI_which == 'questionnaire') {
        $sysSession->cur_func = '1800200100';
    }
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

    }
    //ACL end

    function fetch_variable($match)
    {
        global $MSG, $sysSession;

        return $MSG[$match[1]][$sysSession->lang];
    }
    
        /**
         * 取某節點裡的最底層文字
         * param element $element 節點
         * return string 節點文字
         */
        function getNodeContent($element)
        {
            if (!is_object($element))
                return ''; //判斷$element是否為物件
            $node = $element;
            while ($node->has_child_nodes()) {
                $node = $node->first_child();
            }
            return $node->node_value();
        }

        /**
         *取出節點中的resprocessing標籤中的文字
         *return array 節點文字陣列
         */
        function getFillContent($node)
        {
            global $ctx1;
            $id  = $node->get_attribute('ident');
            $ret = $ctx1->xpath_eval("/item/resprocessing/respcondition/conditionvar/varequal[@respident='$id']"); //Evaluates the XPath Location Path in the given string->秀出答案與配分
            if (is_array($ret->nodeset) && count($ret->nodeset)) //確認$ret是否為陣列並計算其元素數目
                return '((' . $ret->nodeset[0]->get_content() . '))';
            else
                return '(())';
        }

    // 產生 xslt 的下拉選單的選項
    function genSelectItem($vname, $list) {
        $txt = '';
        foreach ($list as $key => $val) {
            // $optVersion .= sprintf('<option value="%s">%s</option>', $key, $val);
            $txt .= sprintf(
                '<xsl:element name="option"><xsl:attribute name="value">%d</xsl:attribute>' .
                '<xsl:if test="%s=%d"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>' .
                '%s</xsl:element>',
                $key, $vname, $key, htmlspecialchars($val)
            );
        }
        return $txt;
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
                $RS['content'] = ClearWordHtml($RS['content']); 

        if (sysEnableAppCourseExam === true && QTI_which === 'exam') {
            // 取得本測驗是否支援行動測驗
            $appSupport = dbGetOne('APP_qti_support_app', 'support', "exam_id={$_POST['lists']} AND type='". QTI_which . "' AND course_id={$course_id}");
        }

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

        $answer_publish_type = 'complete';        //公佈答案的內容預設為 公佈成績、作答結果及標準答案
        if (empty($RS['content']))
            $examDetail = '<questestinterop />';
        elseif(strpos($RS['content'], '<wm_immediate_random_generate_qti') !== FALSE)
        {
            $immediate_random_pick = true;// 隨機選題為真,則判斷配分並取值$regs[1]
            $threshold_score = preg_match('/\bthreshold_score="([0-9]*)"/', $RS['content'], $regs) ? $regs[1] : '';

            if (preg_match('/\bscore_publish_type="(\w*)"/', $RS['content'], $regs)) {
                $answer_publish_type = $regs[1];
            }

            if(strpos($RS['content'], '<conditions>') !== FALSE)
            {
                $xsl = (QTI_which == 'exam') ? 'condition_exam.xsl' : 'condition.xsl';
                $xslt_buf = preg_replace_callback('/\{\$MSG\[\'([^\]]+)\'\]\[[^\]]+\]\}/', 'fetch_variable', file_get_contents($xsl));
                                
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
            function replace_content( &$node, $new_content )
            {
                $dom = &$node->owner_document();
                $kids = &$node->child_nodes();
                foreach ( $kids as $kid )
                    if ( $kid->node_type() == XML_TEXT_NODE )
                        $node->remove_child ($kid);
                $node->set_content($new_content);
            }
            $examDetail = $RS['content'];
            $dom = @domxml_open_mem(preg_replace('/xmlns="[^"]+"/', '', $examDetail));
            if ($dom) {                                
                    $ctx = xpath_new_context($dom);                                        
                    $node = $ctx->xpath_eval('/questestinterop//item');    
                    
                    if(is_array($node->nodeset)) { 
                        foreach($node->nodeset as $nodes){
                            //echo $nodes->get_attribute('id').'[=====]';                                            
                            $nodes_2 = $nodes->get_content();
                            $pattern = "/<td>(.*?)<\/td>/i";
                            preg_match($pattern,$nodes_2,$out);
                            list($id_content, $type) = $sysConn->GetRow(sprintf('select content,type from WM_qti_%s_item where ident in ("%s") ', QTI_which, $nodes->get_attribute('id')));
                                                        $id_content = @domxml_open_mem(preg_replace('/xmlns="[^"]+"/', '', $id_content));
                                                        if (isset($id_content)) {
                                                            $topic = '';
                                                            $ctx1  = xpath_new_context($id_content);

                                                            $ret     = $ctx1->xpath_eval('/item/presentation//mattext');
                                                            $id_node = is_array($ret->nodeset) ? $ret->nodeset : array(
                                                                null
                                                            );
                                                            switch ($type) {

                                                                case 4: //題型為填充題的話
                                                                    $topic = '';
                                                                    foreach ($id_node as $node) {
                                                                        $topic .= getNodeContent($node); //取節點(/item/presentation//mattext)裡的最底層文字
                                                                        $n = $node->parent_node(); //到父節點
                                                                        $n = $n->next_sibling(); //到旁節點
                                                                        if (is_object($n) && $n->node_name() == 'response_str') {
                                                                            $topic .= getFillContent($n); //'response_str->文字填充
                                                                        }
                                                                    }
                                                                    break;
                                                                default:
                                                                    $topic = getNodeContent($id_node[0]); //取節點裡的最底層文字
                                                                    break;
                                                            }


                                                            $new_content = str_replace($out[0], '<td>' . strip_tags($topic) . '</td>', $nodes_2);
                                                            replace_content($nodes, $new_content);
                                                        }
                        }
                    }
                $examDetail = $dom->dump_mem(true);                                        
            }

            // 將已刪除的題目，自卷中移除
            if (preg_match_all('/<item [^>]*id="(\w+)"/U', $examDetail, $regs, PREG_PATTERN_ORDER))
            {
                $exists_item = $sysConn->GetCol(sprintf('select ident from WM_qti_%s_item where ident in ("%s")', QTI_which, implode('","', $regs[1])));
                $removed = array_diff($regs[1], $exists_item);
                if (count($removed))
                {
                    $pattern = explode(chr(9), '!<item [^>]*id="' . implode('"[^>]*>[^<]*</item>!isU' . chr(9) . '!<item [^>]*id="', $removed) . '"[^>]*>[^<]*</item>!isU');
                    $replace = array_pad(array(), count($pattern), '');
                    $examDetail = preg_replace($pattern, $replace, $examDetail);
                }
            }

            $examDetail = strtr(
                $examDetail,
                array(
                    "'"  => "&#39;",
                    "\n" => '',
                    "\r" => '',
                    '\\' => '\\\\',
                    '//' => '\/\/',
                    'item id' => 'item xmlns="" id',
                    'section id' => 'section xmlns="" id'
                )
            );

            if (preg_match('/\bscore_publish_type="(\w*)"/', $examDetail, $regs)) {
                $answer_publish_type = $regs[1];
            }
        }
        $examDetail = str_replace('item id','item xmlns="" id',$examDetail);
        $examDetail = str_replace("&amp;lt;p&amp;gt;&amp;lt;!--[if--&amp;gt;&amp;lt;!--[endif]--&amp;gt;&amp;lt;/p&amp;gt;","",$examDetail);
        $caption = $MSG['exam_modify'][$sysSession->lang];
        $examinee_perm = array('homework' => 1700400200, 'exam' => 1600400200, 'questionnaire' => 1800300200);
        $examiner_perm = array('homework' => 1700300100, 'exam' => 1600300100, 'questionnaire' => 0);

        if (QTI_which == 'questionnaire' && aclCheckWhetherForGuestQuest($course_id, $_POST['lists']))
        {
            $forGuest = true;
            $acl_lists = '';
        }
        else
        {
            $forGuest = false;
            $acl0 = aclGetAclArrayByInstance($examinee_perm[QTI_which], $course_id, $_POST['lists']);
            $acl1 = aclGetAclArrayByInstance($examiner_perm[QTI_which], $course_id, $_POST['lists']);
            if(!empty($acl0) || !empty($acl1))
                $acl_lists = 'var acl_lists = new Array(new Array(' . implode(",\n", $acl0) . '), new Array(' . implode(",\n", $acl1) .  "));\n";
            else
                $acl_lists = '';
        }
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
          $msg_pick_item_cue = (QTI_which === 'questionnaire') ? $MSG['pick_item_cue_questionnaire'][$sysSession->lang] : $MSG['pick_item_cue'][$sysSession->lang];
$examType = intval($RS['type']);
$sysEnableAppISunFuDon = sysEnableAppISunFuDon ? 1 : 0;
      $scr = <<< EOB
var examType = {$examType};
var qti_which ='{$qti_which}';
var sysEnableAppISunFuDon = {$sysEnableAppISunFuDon};
var prog_type = '{$prog_type}';
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
var pickbtm             = '{$MSG['select'][$sysSession->lang]}';
var nowPickedNum        = 0;       // 目前此試卷已選取的題目
var MaxPickedNum        = parseInt('{$CourseExamQuestionsLimit}');        // 一份試卷最多可選取的題目
var msg_overNumber      = '{$msg_over_question_number}';
var MSG_SELECT_ALL      = "{$MSG['select_all'][$sysSession->lang]}";
var MSG_SELECT_CANCEL   = "{$MSG['cancel_all'][$sysSession->lang]}";
var MSG_SEARCHPAGE_TOP  = "{$MSG['page_first'][$sysSession->lang]}";
var MSG_SEARCHPAGE_UP   = "{$MSG['page_prev'][$sysSession->lang]}";
var MSG_SEARCHPAGE_DOWN = "{$MSG['page_next'][$sysSession->lang]}";
var MSG_SEARCHPAGE_END  = "{$MSG['page_last'][$sysSession->lang]}";
var MSG_PAGE_NUM        = "{$MSG['page'][$sysSession->lang]}";
var MSG_PAGE_EACH       = "{$MSG['each_page'][$sysSession->lang]}";
var MSG_PAGE_ITEM       = "{$MSG['item'][$sysSession->lang]}";
var MSG_MV_SEC          = "{$MSG['move_item_to_section'][$sysSession->lang]}";
var MSG_X_MV_CHD        = "{$MSG['dont_mv_to_child'][$sysSession->lang]}";
var MSG_SEL_SEC         = "{$MSG['select_section_first'][$sysSession->lang]}";
var MSG_SEL_FIRST       = "{$MSG['select_first'][$sysSession->lang]}";
var MSG_SEL_BEFORE      = "{$MSG['select_before_assign'][$sysSession->lang]}";
var MSG_INPUT_SCORE     = "{$MSG['input_score'][$sysSession->lang]}";
var MSG_INPUT_TOTAL     = "{$MSG['input_total'][$sysSession->lang]}";
var MSG_INPUT_TOTAL2     = "{$MSG['input_total2'][$sysSession->lang]}";
var MSG_IGN_REPEAT      = "{$MSG['ignore_repeat'][$sysSession->lang]}";
var MSG_NOT_XML         = "{$MSG['return_not_xml'][$sysSession->lang]}";
var MSG_INCR_XML        = "{$MSG['incorrect_xml'][$sysSession->lang]}";
var MSG_INCR_FORM       = "{$MSG['incorrect_form'][$sysSession->lang]}";
var MSG_NO_RESULT       = "{$MSG['no_result'][$sysSession->lang]}";
var MSG_NO_ITEMS        = "{$MSG['no_items'][$sysSession->lang]}";
var MSG_UNKNOW_ERR      = "{$MSG['unknown_err'][$sysSession->lang]}";
var MSG_SCORE_REM       = "{$MSG['score_remind'][$sysSession->lang]}";
var MSG_LANG_HINT       = "{$MSG['lnguage_hint'][$sysSession->lang]}";
var MSG_DATE_ERR        = "{$MSG['msg_date_error'][$sysSession->lang]}";
var MSG_DATE_ERR2        = "{$MSG['msg_date_error2'][$sysSession->lang]}";
var MSG_DATE_ERR3        = "{$MSG['msg_date_error3'][$sysSession->lang]}";
var MSG_IRGA_REQ        = "{$MSG['immediate_random_generate_amount_request'][$sysSession->lang]}";
var MSG_IRGS_REQ        = "{$MSG['immediate_random_generate_score_request'][$sysSession->lang]}";
var MSG_PICK_ITEM_CUE   = "{$msg_pick_item_cue}";
var MSG_GROUP_REQ       = "{$MSG['msg_teach_create_group_error'][$sysSession->lang]}";
var MSG_CLOSE_TIME    = "{$MSG['msg_close_time_isnot_set'][$sysSession->lang]}";
var MSG_NO_ITEMS_IN_EXAM = "{$MSG['no_items_in_exam'][$sysSession->lang]}";
var MSG_IRGA_REQ_ZERO    = "{$MSG['immediate_random_generate_amount_request_zero'][$sysSession->lang]}";
var DO_INTERVAL_TIP     = "{$MSG['do_interval_tip'][$sysSession->lang]}";
var MSG_CUT        = "{$MSG['msg_cut'][$sysSession->lang]}";
var CO_TOTAL_SORCE      = "{$MSG['co_total_sorce'][$sysSession->lang]}";
var MSG_NO_ITEMS_IN_EXAM2 = "{$MSG['error_complete'][$sysSession->lang]}";
var MSG_COUNT_TYPE_CHANGE = "{$MSG['msg_count_type_change'][$sysSession->lang]}";
var MSG_ALERT_FILL = "{$MSG['msg_alert_fill'][$sysSession->lang]}";
var MSG_ALERT_FILL1 = "{$MSG['msg_alert_fill1'][$sysSession->lang]}";
var MSG_ITEM_TYPE_ERROR = "{$MSG['msg_item_type_error'][$sysSession->lang]}";

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


function alert_enable(obj)
{
    if($("#"+obj).prop("checked")){
        $("#"+obj+"_field").show();
    }else{
        $("#"+obj+"_field").hide();
    }
}
EOB;
      showXHTML_script('inline', $scr);
      showXHTML_script('include', '/lib/jquery/jquery.min.js', true, null, 'UTF-8');
      showXHTML_script('include', '/lib/common.js');
	  showXHTML_script('include', '/teach/exam/exam_create.js?'.time(), true, null, 'UTF-8');
      $xajax_save_temp->printJavascript('/lib/xajax/');
    showXHTML_head_E();
    showXHTML_body_B(' onclick="if (!acl_hidden_flags) {hide_acl_dialog(); acl_hidden_flags = false;}"');
    echo"<style>
        #immediate_random_pick_amount {
            background: transparent;
            box-shadow: transparent 0 0 0 inset;
            border: 0px;
            cursor: default;
            text-align: center;
        }
    </style>
    ";
          // 因應 ipad mini 最大先設定到950
      showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" width="1000" style="border-collapse: collapse"');
        showXHTML_tr_B();
          showXHTML_td_B();
            $ary = array(array($MSG['exam_info'][$sysSession->lang],     '',  'switchTab(0);'),
                         array($MSG['pick_item'][$sysSession->lang],     '',  'switchTab(1);'),
                         array(((QTI_which === 'questionnaire') ? $MSG['order_item'][$sysSession->lang] : $MSG['selected_item'][$sysSession->lang]), '',  'switchTab(2);'),
                         array($MSG['shuffle_item'][$sysSession->lang],  '',  'switchTab(3);'),
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
                showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="100%" style="border-collapse: collapse" class="box01" id="tab1Table"');

                  showXHTML_tr_B('class="bg02 font01" nowrap');
                    showXHTML_td_B('align="right" colspan="3"');
                      showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]   , '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                      showXHTML_input('button', '', $MSG['next_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(1);"');
                      if (isset($RS)) showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="cssBtn" onclick="saveContent();"');
                    showXHTML_td_E();
                  showXHTML_tr_E();
                    if ((QTI_which==='exam' && sysEnableAppCourseExam === true)) {
                        // 測驗才顯示是否支援行動測驗的選項
                        showXHTML_tr_B('class="bg04 font01"');
                            if (QTI_which == 'exam') {
                                $supportMsg = $MSG['exam_support_app'][$sysSession->lang];
                            } else if (QTI_which == 'questionnaire') {
                                $supportMsg = $MSG['questionnaire_support_app'][$sysSession->lang];
                            }
                            showXHTML_td('', $supportMsg);
                            showXHTML_td_B();
                                if (($_POST['lists'] === '') || (isset($appSupport) && $appSupport === 'Y')) {
                                    // 才剛要建立 或是 既存試卷且有設定支援
                                    $appSupportCheck = ' checked';
                                } else {
                                    $appSupportCheck = '';
                                }
                                showXHTML_input('checkbox', 'qti_support_app', 'Y', '', $appSupportCheck . ' id="qti_support_app" onclick=supportApp(this.checked,\'' . QTI_which. '\');');
                                echo $MSG['exam_app_support_checkbox'][$sysSession->lang];
                            showXHTML_td_E();
                                if (QTI_which == 'exam') {
                                    $supportMsg = $MSG['exam_support_app_msg'][$sysSession->lang];
                                } else if (QTI_which == 'questionnaire') {
                                    $supportMsg = $MSG['questionnaire_support_app_msg'][$sysSession->lang];
                                }
                            showXHTML_td('class="font06"', $supportMsg);
                        showXHTML_tr_E();
                    }
                  $arr_names = array('Big5'           => 'title[Big5]',
                                     'GB2312'       => 'title[GB2312]',
                                     'en'           => 'title[en]',
                                     'EUC-JP'       => 'title[EUC-JP]',
                                     'user_define' => 'title[user_define]'
                                    );
                    showXHTML_tr_B('class="bg03 font01"');
                        showXHTML_td('width="120"', $MSG['exam_name'][$sysSession->lang]);
                        showXHTML_td_B('width="470"');// 避免 開放作答日期等6個字的欄位換行，影響視覺
                            $multi_lang = new Multi_lang(false, $title); // 多語系輸入框
                            $multi_lang->show(true, $arr_names);
                        showXHTML_td_E();
                        showXHTML_td('class="font06" width="405"', $MSG['lnguage_hint'][$sysSession->lang]);
                    showXHTML_tr_E();

                  showXHTML_tr_B('class="bg04 font01"');
                    showXHTML_td('', $MSG['pre-notice'][$sysSession->lang]);
                    showXHTML_td_B();
                      showXHTML_input('textarea', 'notice', $RS['notice'], '', 'rows="8" cols="40" class="box02"');
                    showXHTML_td_E();
                    showXHTML_td('class="font06"', $MSG['pre-notice1'][$sysSession->lang]);
                  showXHTML_tr_E();

                  showXHTML_tr_B('class="bg03 font01"' . ((QTI_which == 'homework') || (QTI_which == 'questionnaire' && !sysEnableAppISunFuDon) || ($topDir == 'academic')? ' style="display: none"' : ''));
                    showXHTML_td('', $MSG['exam_use'][$sysSession->lang]);
                    showXHTML_td_B();
                      if (!sysEnableAppISunFuDon || (intval($_POST['lists']) > 0 && intval($RS['type']) !== 5)) {
                          // 沒有啟用愛上互動或是已經是存在的非愛上互動試卷
                          if (QTI_which === 'exam') {
                            $typeArray = array(1 => $MSG['exam_type2'][$sysSession->lang],
                                               2 => $MSG['exam_type3'][$sysSession->lang],
                                               3 => $MSG['exam_type4'][$sysSession->lang],
                                               4 => $MSG['exam_type5'][$sysSession->lang]
                                         );
                            showXHTML_input('select', 'ex_type', $typeArray, intval($RS['type']), 'id="ex_type" class="box02"');
                        } else {
                            $typeArray = array(1 => $MSG['exam_type3'][$sysSession->lang]
                            );

                            showXHTML_input('select', 'ex_type', $typeArray, intval($RS['type']), 'id="ex_type" class="box02" disabled="true"');
                        }
                      } else {
                            if (QTI_which === 'exam') {
                              $typeArray = array(1 => $MSG['exam_type2'][$sysSession->lang],
                                                 2 => $MSG['exam_type3'][$sysSession->lang],
                                                 3 => $MSG['exam_type4'][$sysSession->lang],
                                                 4 => $MSG['exam_type5'][$sysSession->lang],
                                                 // 5 => $MSG['exam_type_isunfudon'][$sysSession->lang]
                                           );
                              showXHTML_input('select', 'ex_type', $typeArray, intval($RS['type']), 'id="ex_type" class="box02" onchange="iSunFuDonSupportExam(this.value)"');
                          } else {
                              $typeArray = array(1 => $MSG['exam_type3'][$sysSession->lang],
                                                 // 5 => $MSG['exam_type_isunfudon'][$sysSession->lang]
                                           );
                              showXHTML_input('select', 'ex_type', $typeArray, intval($RS['type']), 'id="ex_type" class="box02" onchange="iSunFuDonSupportQuestionnaire(this.value)"');
                          }
                      }
                    showXHTML_td_E();
                    showXHTML_td('class="font06"', $MSG['exam_use1'][$sysSession->lang]);
                  showXHTML_tr_E();

                  showXHTML_tr_B('class="bg04 font01" id="trStatus"');
                    showXHTML_td('', $MSG['exam_publish'][$sysSession->lang]);
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
                        showXHTML_input('radio', 'rdoPublish', $rdoPublishes, $rdoPublishValue, 'onClick="statListDateShow(this.value);" size="5" class="box02"');
                    showXHTML_td_E();
                    showXHTML_td('class="font06"', '');
                  showXHTML_tr_E();
                      
                        $dis = '';
                        if (($prog_type == 'exam_create.php') || ($RS['publish'] == 'prepare') || ($RS['publish'] == 'close')) {
                            $dis = ' style="display: none;"';
                        }

                        $beforeAry = array(
                            "{$MSG['zero_day'][$sysSession->lang]}",
                            "1{$MSG['msg_alert_before1'][$sysSession->lang]}",
                            "2{$MSG['msg_alert_before1'][$sysSession->lang]}",
                            "3{$MSG['msg_alert_before1'][$sysSession->lang]}",
                            "4{$MSG['msg_alert_before1'][$sysSession->lang]}",
                            "5{$MSG['msg_alert_before1'][$sysSession->lang]}",
                            "6{$MSG['msg_alert_before1'][$sysSession->lang]}",
                            "7{$MSG['msg_alert_before1'][$sysSession->lang]}"
                        );
                            
                  showXHTML_tr_B('id="trOpen" class="bg0' . (QTI_which == 'exam' ? 3 : 4) . ' font01"' . $dis);
                    showXHTML_td('', $MSG['enable_duration1'][$sysSession->lang]);
                    showXHTML_td_B();
                        
                        $tmp = $sysConn->UnixTimeStamp($RS['begin_time']);
                        $isCheck = (!empty($tmp)) ? true : false;
                        $val = $isCheck ? substr($RS['begin_time'], 0, 16) : sprintf('%04d-%02d-%02d 00:00', $date['year'], $date['mon'], $date['mday']);
                        $ck = $isCheck ? ' checked' : '';
                        $ds = $isCheck ? '' : ' style="display: none;"';
                        showXHTML_input('checkbox', 'ck_begin_time', '', '', 'id="ck_begin_time' . '" onclick="showDateInput(\'span_begin_time' . '\', this.checked)"'. $ck);
                        echo $MSG['btn_enable'][$sysSession->lang];
                        echo '<span id="span_begin_time'.'"'. $ds .'>' . $MSG['msg_enable_date'][$sysSession->lang];
                        showXHTML_input('text', 'begin_time', $val, '', 'id="begin_time" readonly="readonly" class="cssInput"');
                                                
                                                
                        
                            // email提醒
                            $calendar_begin_type = QTI_which . '_begin';
                            $calendar_end_type   = QTI_which . '_end';
                            if ($_POST['lists']) {
                                $instance = $_POST['lists'];

                                list($begin_cal_idx, $begin_alert_type, $begin_alert_before) = dbGetStSr('WM_calendar', 'idx,alert_type,alert_before', "relative_type='{$calendar_begin_type}' and relative_id={$instance}");
                                if (!$begin_cal_idx) {
                                    $begin_alert_type   = 'login,email';
                                    $begin_alert_before = 3;
                                } else if (!$begin_alert_type)
                                    $begin_alert_before = 3;

                                list($end_cal_idx, $end_alert_type, $end_alert_before) = dbGetStSr('WM_calendar', 'idx,alert_type,alert_before', "relative_type='{$calendar_end_type}' and relative_id={$instance}");
                                if ($begin_cal_idx && !$end_cal_idx) {
                                $end_alert_type = $begin_alert_type;
                                $end_alert_before = $begin_alert_before;    
                                } else if (!$end_cal_idx) {
                                    $end_alert_type   = 'login,email';
                                    $end_alert_before = 3;
                                } else if (!$end_alert_type)
                                    $end_alert_before = 3;
                            } else {
                                $begin_alert_type   = 'login,email';
                                $begin_alert_before = 3;
                                $end_alert_type     = 'login,email';
                                $end_alert_before   = 3;
                            }

                            echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                            if ($begin_alert_type != '') {
                                $chk = 'checked="checked"';
                                $display = '';
                            } else {
                                $display = 'style="display:none"';
                            }
                            echo $MSG['title_alert'][$sysSession->lang] . '<input type="checkbox" id="alert_check" name="alert_check" value="1" ' . $chk . ' onclick="alert_enable(\'alert_check\');">';
                            echo $MSG['title_enable_alert'][$sysSession->lang] . '&nbsp;';
                            echo '<span id="alert_check_field" '.$display.'>';
                            echo '<select id="alert_before" name="alert_before" class="input-mini">';
                            foreach ($beforeAry as $key => $v) {
                                $sel = '';
                                if ($begin_alert_before == $key)
                                    $sel = 'selected="selected"';
                                echo '<option value=' . $key . ' ' . $sel . '>' . $v . '</option>';
                            }
                            echo '</select>&nbsp;&nbsp;&nbsp;';
                            if (strpos($begin_alert_type, 'login') !== false)
                                $chk_login = 'checked="checked"';
                            echo '<input type="checkbox" id="alert_login" name="alert_login" value="1" ' . $chk_login . '>';
                            echo $MSG['title_login_alert'][$sysSession->lang] . '&nbsp;&nbsp;&nbsp;';
                            if (strpos($begin_alert_type, 'email') !== false)
                                $chk_email = 'checked="checked"';
                            echo '<input type="checkbox" id="alert_email" name="alert_email" value="1" ' . $chk_email . '>';
                            echo $MSG['title_email_alert'][$sysSession->lang] . '&nbsp;&nbsp;&nbsp;';
                            echo '</span>';

                            showXHTML_input('hidden', 'ck_sync_begin_time', 1);
                        
                        echo '</span>';
                    showXHTML_td_E();
                    showXHTML_td('class="font06"', $MSG['exam_duration1_1'][$sysSession->lang]);
                  showXHTML_tr_E();

                  showXHTML_tr_B('id="trClose" class="bg0' . (QTI_which == 'exam' ? 4 : 3) . ' font01"' . $dis);
                    showXHTML_td('', $MSG['enable_duration2'][$sysSession->lang]);
                    showXHTML_td_B();
                        $tmp = $sysConn->UnixTimeStamp($RS['close_time']);
                        $isCheck = (($tmp<253402185600) && (!empty($tmp))) ? true : false;    // 9999-12-31 00:00:00 表不限
                        $val = $isCheck ? substr($RS['close_time'], 0, 16) : sprintf('%04d-%02d-%02d 23:59', $date['year'], $date['mon'], $date['mday']);
                        $ck = $isCheck ? ' checked' : '';
                        $ds = $isCheck ? '' : ' style="display: none;"';
                        showXHTML_input('checkbox', 'ck_close_time', '', '', 'id="ck_close_time' . '" onclick="showDateInput(\'span_close_time' . '\', this.checked)"'. $ck);
                        echo $MSG['btn_enable'][$sysSession->lang];
                        echo '<span id="span_close_time'.'"'. $ds .'>' . $MSG['msg_enable_date'][$sysSession->lang];
                        showXHTML_input('text', 'close_time', $val, '', 'id="close_time" readonly="readonly" class="cssInput"');
                        
                        
                            // email提醒
                            echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                            if ($end_alert_type != '') {
                                $chk1 = 'checked="checked"';
                                $display1 = '';
                            } else {
                                $display1 = 'style="display:none"';
                            }
                            echo $MSG['title_alert'][$sysSession->lang] . '<input type="checkbox" id="alert_check1" name="alert_check1" value="1" ' . $chk1 . ' onclick="alert_enable(\'alert_check1\');">';
                            echo $MSG['title_enable_alert'][$sysSession->lang] . '&nbsp;';
                            echo '<span id="alert_check1_field" '.$display1.'>';
                            echo '<select id="alert_before1" name="alert_before1" class="input-mini">';
                            foreach ($beforeAry as $key => $v) {
                                $sel1 = '';
                                if ($end_alert_before == $key)
                                    $sel1 = 'selected="selected"';
                                echo '<option value=' . $key . ' ' . $sel1 . '>' . $v . '</option>';
                            }
                            echo '</select>&nbsp;&nbsp;&nbsp;';
                            if (strpos($end_alert_type, 'login') !== false)
                                $chk_login1 = 'checked="checked"';
                            echo '<input type="checkbox" id="alert_login1" name="alert_login1" value="1" ' . $chk_login1 . '>';
                            echo $MSG['title_login_alert'][$sysSession->lang] . '&nbsp;&nbsp;&nbsp;';
                            if (strpos($end_alert_type, 'email') !== false)
                                $chk_email1 = 'checked="checked"';
                            echo '<input type="checkbox" id="alert_email1" name="alert_email1" value="1" ' . $chk_email1 . '>';
                            echo $MSG['title_email_alert'][$sysSession->lang] . '&nbsp;&nbsp;&nbsp;';
                            echo '</span>';
                        
                        
                            showXHTML_input('hidden', 'ck_sync_begin_time', 1);
                                                
                        
                        
                            showXHTML_input('hidden', 'ck_sync_end_time', 1);
                        
                        echo '</span>';
                    showXHTML_td_E();
                    showXHTML_td('class="font06"', $MSG['exam_duration1_2'][$sysSession->lang]);
                  showXHTML_tr_E();
                      
                    // 繳交次數
                    if (QTI_which === "homework") {
                        showXHTML_tr_B('class="bg03 font01"');
                            showXHTML_td('', $MSG['pay_times'][$sysSession->lang]);
                            
                            showXHTML_td_B();
                                if (isset($RS['modifiable']) === false) {
                                    $modifiable = 'Y';
                                } else {
                                    $modifiable = $RS['modifiable'];
                                }
                                showXHTML_input('radio', 'modifiable', array('N' => $MSG['once'][$sysSession->lang], 'Y' => $MSG['allow_repeated'][$sysSession->lang]), $modifiable);
                            showXHTML_td_E();
                            
                            showXHTML_td('class="font06"', $MSG['whether_repay_within_delay_time'][$sysSession->lang]);
                        showXHTML_tr_E();
                    }

                    // 開放補繳
                    if (QTI_which === "homework") {
                        showXHTML_tr_B('id="trDelay" class="bg0' . (QTI_which == 'exam' ? 4 : 3) . ' font01"' . $dis);
                            showXHTML_td('', $MSG['enable_duration3'][$sysSession->lang]);

                            showXHTML_td_B();
                                $tmp = $sysConn->UnixTimeStamp($RS['delay_time']);
                                $isCheck = (($tmp < 253402185600) && (!empty($tmp))) ? true : false;    // 9999-12-31 00:00:00 表不限
                                $val = $isCheck ? substr($RS['delay_time'], 0, 16) : sprintf('%04d-%02d-%02d 23:59', $date['year'], $date['mon'], $date['mday']);
                                $ck = $isCheck ? ' checked' : '';
                                $ds = $isCheck ? '' : ' style="display: none;"';
                                showXHTML_input('checkbox', 'ck_delay_time', '', '', 'id="ck_delay_time' . '" onclick="showDateInput(\'span_delay_time' . '\', this.checked)"'. $ck);
                                echo $MSG['btn_enable'][$sysSession->lang];
                                echo '<span id="span_delay_time'.'"'. $ds .'>' . $MSG['msg_enable_date'][$sysSession->lang];
                                showXHTML_input('text', 'delay_time', $val, '', 'id="delay_time" readonly="readonly" class="cssInput"');
                                showXHTML_input('hidden', 'ck_sync_delay_time', 1);
                                echo '</span>';
                            showXHTML_td_E();

                            showXHTML_td('class="font06"', $MSG['exam_duration2_2'][$sysSession->lang]);
                        showXHTML_tr_E();
                    }

                    showXHTML_tr_B('class="bg03 font01"' . (QTI_which == 'exam' ? ' style="display: none"' : ''));
                        showXHTML_td('', $MSG['response by attachment'][$sysSession->lang]);
                        
                        showXHTML_td_B();
                            $m = (($RS && strpos($RS['setting'], 'upload') !== FALSE) || (!$RS && QTI_which == 'homework')) ? 1 : 0;
                            showXHTML_input('radio', 'setting[upload]', array(
                                1 => $MSG['true'][$sysSession->lang],
                                0 => $MSG['false'][$sysSession->lang]
                            ), $m, 'onclick="setAllowAttachment2();"');
                            
                            if (QTI_which === "homework") {
                                // 必須要有附件
                                $ck = (strpos($RS['setting'], 'required') !== FALSE || empty($RS)) ? ' checked' : '';
                                showXHTML_input('checkbox', 'ck_attachment_required', 'Y', '', 'id="ck_attachment_required' . '" onclick="setAllowAttachment();"' . $ck);
                                echo $MSG['attachment_required'][$sysSession->lang];
                            }
                            
                        showXHTML_td_E();
                        
                        if (QTI_which === 'questionnaire' && sysEnableAppQuestionnaire) {
                            $appMessage = $MSG['questionnaire_upload_app_hint'][$sysSession->lang];
                        } else {
                            $appMessage = '';
                        }
                        showXHTML_td('class="font06" width="100"', $MSG['response by attachment hint'][$sysSession->lang] . $appMessage);
                    showXHTML_tr_E();

if (QTI_which == 'questionnaire')
{
                  showXHTML_tr_B('class="bg04 font01"' . ($forGuest ? ' style="display: none"' : ''));
                    showXHTML_td('', $MSG['anonymous or not'][$sysSession->lang]);
                    showXHTML_td_B();
                      $m = ($RS && strpos($RS['setting'], 'anonymity') !== false) ? 1 : 0;
                      showXHTML_input('radio', 'setting[anonymity]', array(0 => $MSG['named'][$sysSession->lang],
                                                                           1 => $MSG['anonymous'][$sysSession->lang]), $m);
                    showXHTML_td_E();
                    showXHTML_td('class="font06"', $MSG['anonymous or not hint'][$sysSession->lang]);
                  showXHTML_tr_E();
}
if (QTI_which == 'questionnaire')
{
                  showXHTML_tr_B('class="bg03 font01"' . ($forGuest ? ' style="display: none"' : ''));
                    showXHTML_td('', $MSG['msg_modify'][$sysSession->lang]);
                    showXHTML_td_B();
                      showXHTML_input('checkbox', 'modifiable', 'Y', '', 'id="modifiable"' . ((isset($RS['modifiable']) && $RS['modifiable'] == 'N') ? '':' checked')); echo $MSG['modifiable'][$sysSession->lang];
                    showXHTML_td_E();
                    showXHTML_td('', $MSG['msg_exam_again'][$sysSession->lang]);
                  showXHTML_tr_E();
}

                  showXHTML_tr_B('class="bg04 font01"' . (QTI_which == 'exam' ? '' : ' style="display: none"'));
                    showXHTML_td('', $MSG['count_type'][$sysSession->lang]);
                    showXHTML_td_B();
                      // MIS#26031 如果已設定計分，則不再顯示不計分 by Small 2012/07/18
                      if($RS['count_type']=='none' || empty($RS['count_type']))
                        $ary_count_type = array('none'      => $MSG['count_type0'][$sysSession->lang],
                                                'first'      => $MSG['count_type1'][$sysSession->lang],
                                                'last'      => $MSG['count_type2'][$sysSession->lang],
                                                'max'      => $MSG['count_type3'][$sysSession->lang],
                                                'min'      => $MSG['count_type4'][$sysSession->lang],
                                                'average' => $MSG['count_type5'][$sysSession->lang]
                                               );
                      else
                        $ary_count_type = array('none'      => $MSG['count_type0'][$sysSession->lang],'first'      => $MSG['count_type1'][$sysSession->lang],
                                                'last'      => $MSG['count_type2'][$sysSession->lang],
                                                'max'      => $MSG['count_type3'][$sysSession->lang],
                                                'min'      => $MSG['count_type4'][$sysSession->lang],
                                                'average' => $MSG['count_type5'][$sysSession->lang]
                                               );
                      if (sysEnableAppISunFuDon && intval($RS['type']) === 5 && !array_key_exists('none', $ary_count_type)) {
                          $ary_count_type['none'] = $MSG['count_type0'][$sysSession->lang];
                      }
                      showXHTML_input('select', 'count_type', $ary_count_type, isset($RS['count_type'])? $RS['count_type'] : 'max', 'id="count_type" style="width: 160px" class="box02" onChange="document.getElementById(\'percent\').disabled=(this.value==\'none\');if(this.value==\'none\'){document.getElementById(\'percent\').value=0.0;}" data-old-value="' . (isset($RS['count_type'])? $RS['count_type'] : 'max') .'"');
                    showXHTML_td_E();
                    showXHTML_td('class="font06"', $MSG['count_type_hint'][$sysSession->lang]);
                  showXHTML_tr_E();

                  switch (QTI_which) {
                      case 'exam' :
                          showXHTML_tr_B('class="bg04 font01"');
                          break;
                      case 'homework' :
                          showXHTML_tr_B('class="bg03 font01"');
                          break;
                      default :
                          showXHTML_tr_B('class="bg04 font01" style="display: none"');
                  }
                    showXHTML_td('', $MSG['exam_percent'][$sysSession->lang]);
                    showXHTML_td_B();
                      showXHTML_input('text', 'percent', (isset($RS) ? floatval($RS['percent']) : '100.0'), '', (($RS['count_type']!=none) ? 'id="percent" size="5" class="box02" onchange="typeCheck(this, \'float\');" onkeypress="floatOnly();"':'size="5" class="box02"  disabled onchange="typeCheck(this, \'float\');" onkeypress="floatOnly();"')); echo '%';
                    showXHTML_td_E();
                    showXHTML_td('class="font06"', $MSG['exam_percent_hint'][$sysSession->lang]);
                  showXHTML_tr_E();

if (QTI_which == 'questionnaire')
{
                  showXHTML_tr_B();
                    showXHTML_td('', $MSG['access mode'][$sysSession->lang]);
                    showXHTML_td_B('');
                      showXHTML_input('radio', 'forGuest', array(0 => $MSG['private access tip'][$sysSession->lang],
                                                                   1 => $MSG['public access tip'][$sysSession->lang]), $forGuest ? 1 : 0, 'onclick="switchForGuest(this);"', '<br>');
                    showXHTML_td_E();
                    showXHTML_td('', '&nbsp;');
                  showXHTML_tr_E();
}

                  showXHTML_tr_B((QTI_which == 'homework' ? 'class="bg04 font01"' : 'class="bg03 font01"') . ($forGuest ? ' style="display: none"' : ''));
                    showXHTML_td('', $MSG['examinee'][$sysSession->lang]);
                    showXHTML_td('id="aclDisplayPanel_0" ', (QTI_which == 'questionnaire' && $topDir == 'academic') ? $MSG['default_all'][$sysSession->lang] : $MSG['default_student'][$sysSession->lang]);
                    showXHTML_td_B();
                      showXHTML_input('button', '', $MSG['toolbtm02'][$sysSession->lang], '', 'id="addACLbtn" class="cssBtn" onclick="acl_hidden_flags = true; init_add_list(0); event.cancelBubble = true;"' . ($forGuest ? ' disabled' : ''));
                    showXHTML_td_E();
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
                    showXHTML_input('text', 'do_times', (isset($RS['do_times']) ? intval($RS['do_times']) : 1), '', 'id="do_times" size="5" class="box02" onchange="typeCheck(this, \'int\');" onkeypress="intOnly();"'); echo $MSG['exam_times1'][$sysSession->lang];
                    showXHTML_td_E();
                    showXHTML_td('class="font06"', $MSG['exam_times_hint'][$sysSession->lang]);
                  showXHTML_tr_E();

                  showXHTML_tr_B('class="bg04 font01"' . (QTI_which == 'exam' ? '' : ' style="display: none"'));
                    showXHTML_td('', $MSG['exam_duration'][$sysSession->lang]);
                    showXHTML_td_B();
                      showXHTML_input('text', 'do_interval', (isset($RS['do_interval']) ? intval($RS['do_interval']) : 60), '', 'size="5" class="box02" onchange="typeCheck(this, \'int\');" onkeypress="intOnly();"'); echo $MSG['minute'][$sysSession->lang];
                    showXHTML_td_E();
                    if (QTI_which === 'exam' && sysEnableAppCourseExam) {
                        $appMessage = $MSG['exam_do_interval_app_hint'][$sysSession->lang];
                    } else {
                        $appMessage = '';
                    }
                    showXHTML_td('class="font06"', $MSG['exam_duration_hint'][$sysSession->lang] . $appMessage);
                  showXHTML_tr_E();

                  showXHTML_tr_B('class="bg03 font01"' . (QTI_which == 'exam' ? '' : ' style="display: none"'));
                    showXHTML_td('', $MSG['item_per_page'][$sysSession->lang]);
                    showXHTML_td_B();
                        $pageValue = isset($RS['item_per_page'])? intval($RS['item_per_page']) : 0;
                        echo $MSG['each_page'][$sysSession->lang]; showXHTML_input('text', 'item_per_page', $pageValue, '', 'id="item_per_page" size="5" class="box02" onchange="typeCheck(this, \'int\');" onkeypress="intOnly();"'); echo $MSG['item'][$sysSession->lang];
                    showXHTML_td_E();
                    if (QTI_which === 'exam' && sysEnableAppCourseExam) {
                        $appMessage = $MSG['space_is_all_app'][$sysSession->lang];
                    } else {
                        $appMessage = '';
                    }
                    showXHTML_td('class="font06"', $MSG['space_is_all'][$sysSession->lang] . $appMessage);
                  showXHTML_tr_E();

                  showXHTML_tr_B('class="bg04 font01"' . (QTI_which == 'exam' ? '' : ' style="display: none"'));
                    showXHTML_td('', $MSG['flip_control'][$sysSession->lang]);
                    showXHTML_td_B();
                      showXHTML_input('select', 'ctrl_paging', array('none'       => $MSG['unlimited'][$sysSession->lang],
                                                                     'can_return' => $MSG['flip_control1'][$sysSession->lang],
                                                                     'lock'       => $MSG['flip_control2'][$sysSession->lang]
                                                                    ), $RS['ctrl_paging'], 'id="ctrl_paging" size="1" style="width: 160px" class="box02"');
                    showXHTML_td_E();
                    showXHTML_td('class="font06"', $MSG['flip_control_hint'][$sysSession->lang]);
                  showXHTML_tr_E();

                  showXHTML_tr_B('class="bg03 font01"' . (QTI_which == 'exam' ? '' : ' style="display: none"'));
                    showXHTML_td('', $MSG['window_control'][$sysSession->lang]);
                    showXHTML_td_B();
                      showXHTML_input('select', 'ctrl_window', array('none' => $MSG['unlimited'][$sysSession->lang],
                                                                     'lock' => $MSG['window_control1'][$sysSession->lang],
                                                                     'lock2' => $MSG['window_control12'][$sysSession->lang]
                                                                    ), $RS['ctrl_window'], 'id="ctrl_window" size="1" class="box02"');
                    showXHTML_td_E();
                    if (QTI_which === 'exam' && sysEnableAppCourseExam) {
                        $appMessage = $MSG['window_control_hint_app'][$sysSession->lang];
                    } else {
                        $appMessage = '';
                    }
                    showXHTML_td('class="font06"', $MSG['window_control_hint'][$sysSession->lang] . $appMessage);
                  showXHTML_tr_E();

                  showXHTML_tr_B('class="bg04 font01"' . (QTI_which == 'exam' ? '' : ' style="display: none"'));
                    showXHTML_td('', $MSG['timeout_control'][$sysSession->lang]);
                    showXHTML_td_B();
                      showXHTML_input('select', 'ctrl_timeout', array('none'        => $MSG['nop'][$sysSession->lang],
                                                                      'mark'        => $MSG['timeout_control1'][$sysSession->lang],
                                                                      'auto_submit' => $MSG['timeout_control2'][$sysSession->lang]
                                                                      ), $RS['ctrl_timeout'], 'id="ctrl_timeout" size="1" style="width: 190px" class="box02"');
                    showXHTML_td_E();
                    if (QTI_which === 'exam' && sysEnableAppCourseExam) {
                        $appMessage = $MSG['timeout_control_hint_app'][$sysSession->lang];
                    } else {
                        $appMessage = '';
                    }
                    showXHTML_td('class="font06"', $MSG['timeout_control_hint'][$sysSession->lang] . $appMessage);
                  showXHTML_tr_E();

                  showXHTML_tr_B((QTI_which == 'homework' ? 'class="bg04 font01"' : 'class="bg03 font01"'));
                    showXHTML_td('', $MSG['score_publish_' . QTI_which][$sysSession->lang]);
                    showXHTML_td_B();

                      showXHTML_input('select', 'announce_type', array('never'       => $MSG['score_publish0'][$sysSession->lang],
                                                                       'now'         => $MSG['score_publish1'][$sysSession->lang],
                                                                       'close_time'  => $MSG['score_publish2'][$sysSession->lang],
                                                                       'user_define' => $MSG['score_publish3'][$sysSession->lang]
                                                                      ), $RS['announce_type'], 'id="announce_type" size="1" style="width: 160px" class="box02" onchange="customTime(this.value);"');
                      echo '<span id="customTimePal" style="display:'.(($RS['announce_type'] == 'user_define')?'':'none').'">';
                        $tmp = $sysConn->UnixTimeStamp($RS['announce_time']);
                        $val = (!empty($tmp)) ? substr($RS['announce_time'], 0, 16) : sprintf('%04d-%02d-%02d %02d:%02d', $date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes']);
                        echo $MSG['msg_enable_date'][$sysSession->lang];
                        showXHTML_input('text', 'announce_time', $val, '', 'id="announce_time" readonly="readonly" class="cssInput"');
                      echo '</span>';
if(QTI_which=='exam'){
                    showXHTML_input('select', 'score_publish_type', array('simple'   => $MSG['score_publish_type1'][$sysSession->lang],
                                                                     'detailed' => $MSG['score_publish_type2'][$sysSession->lang],
                                                                     'complete' => $MSG['score_publish_type3'][$sysSession->lang]
                                                                      ), $answer_publish_type, 'id="score_publish_type" size="1" style="width: 260px;margin-top:15px;display:'.((($RS['announce_type'] == 'never')||($prog_type == 'exam_create.php'))?'none':'block').'" class="box02"');
}
                      echo '<br>';
                      $grade_types = array('homework' => 1, 'exam' => 2, 'questionnaire' => 3);
                      list($grade_pb) = dbGetStSr('WM_grade_list','publish_begin','source="' . $grade_types[QTI_which] . '" and property=' . $_POST['lists']);
                      switch ($grade_pb)
                      {
                          case '1970-01-01 00:00:00':
                              $grade_pb_string = $MSG['now'][$sysSession->lang];
                              break;
                          case '0000-00-00 00:00:00':
                              $grade_pb_string = $MSG['announce_type1'][$sysSession->lang];
                              break;
                          default:
                              $grade_pb_string = $grade_pb;
                      }
                      if(!empty($grade_pb) && QTI_which=='exam') echo '('.$MSG['grade_publish_time'][$sysSession->lang].$grade_pb_string.')';
                      if (sysEnableAppCourseExam || sysEnableAppQuestionnaire) {
                        $appMessage = $MSG['questionnaire_publish_app_hint'][$sysSession->lang];
                      } else {
                        $appMessage = '';
                      }
                    showXHTML_td_E();
                    showXHTML_td('class="font06"', $MSG['score_publish_hint'][$sysSession->lang] . $appMessage);
                  showXHTML_tr_E();

if ($qti_which == 'exam')
{
                  showXHTML_tr_B('class="bg04 font01"');
                    showXHTML_td('', $MSG['msg_score'][$sysSession->lang]);
                    showXHTML_td_B();
                      showXHTML_input('text', 'threshold_score', (preg_match('/\bthreshold_score="([0-9]*)"/', $examDetail, $regs) ? $regs[1] : $threshold_score), '', 'size="6" class="cssInput" onchange="typeCheck(this, \'float\');" onkeypress="floatOnly();"');
                    showXHTML_td_E();
                    showXHTML_td('class="font06"', $MSG['msg_pass_score'][$sysSession->lang]);
                  showXHTML_tr_E();
}
                  showXHTML_tr_B('class="bg02 font01"');
                    showXHTML_td_B('align="right" colspan="3" nowrap');
                      showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]   , '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                      showXHTML_input('button', '', $MSG['next_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(1);"');
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

                  showXHTML_tr_B('class="bg03 font01" style="display:none;"');
                    showXHTML_td_B('colspan="3"');
                    echo "<hr style='border-top: 1px solid ;'>";
                    showXHTML_td_E('');
                  showXHTML_tr_E('');
                  
                  showXHTML_tr_B('class="bg03 font01"');
                    showXHTML_td_B('align="right" width="89" rowspan="3"');
                    echo  $MSG['search_proviso'][$sysSession->lang]."<br>";
                    showXHTML_input('button', '', $MSG['msg_cut'][$sysSession->lang], '', '  class="cssBtn" name="cutRad" ');
                    showXHTML_td_E('');
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
                          echo '<span class="item_type_' . $k . '">';
                        showXHTML_input('checkbox', 'type', $k, '', 'onclick="checkSelect2(this)"' . $checked);
                        echo $v . '<br /></span>';
                      }
                        echo '</td><td>', str_repeat('&nbsp;', 5), '</td><td>';
                        if (QTI_which != 'questionnaire') {
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
                            echo '<span class="item_level_' . $k . '">';
                            showXHTML_input('checkbox', 'level', $k, '', 'onclick="checkSelect2(this)"' . $checked);
                            echo $v . '<br /></span>';
                        }
                }else{
                              echo '</td><td colspan="2">',str_repeat('&nbsp;', 5);
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
                  
                  if (QTI_which == 'exam') {
                      showXHTML_tr_B('class="bg03 font01" id="sum_view"');
                        showXHTML_td_B('colspan="3"');
                        echo $MSG['co_random_amonut'][$sysSession->lang];
                        showXHTML_input('text', 'num',($irgs['fulltext'][1] ? $irgs['fulltext'][1] : 0), '', 'size="6" class="box02" onblur="calculation()" onkeyup="this.value=this.value.replace(/[^\d]/,\'\')"');
                        echo $MSG['co_random_amonut1'][$sysSession->lang];
                        
                        showXHTML_td_E('');
                      showXHTML_tr_E('');
                                          
                                          // 增加條件區塊分隔線，以利辨識
                      showXHTML_tr_B('class="bg03 font01" style="background-color: #dddddd;"');
                        showXHTML_td_B('colspan="3"');
                                                    echo '<hr style="margin-top: 0.3em; margin-bottom: 0.1em;">';
                        showXHTML_td_E('');
                      showXHTML_tr_E('');
                  }
$search_panel = ob_get_contents();
ob_end_flush();

if (preg_match_all('!(<input type="text" name="\w+" value="[\w,]*" size=")4(" [^>]*>)\s&nbsp;&nbsp;&nbsp;!isU', $search_panel, $regs))
{
    foreach($regs[1] as $k => $v) $regs[1][$k] .= '10' . $regs[2][$k];
    $search_panel = str_replace($regs[0], $regs[1], $search_panel);
}

if (preg_match('!<select name="type" [^>]*>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*</select>!isU', $search_panel, $regs))
{
    $org = array_shift($regs);
    $x = array();
    for($i=0; $i<18; $i+=3) $x[$regs[$i]] = $regs[$i+2];
    ob_start();
    showXHTML_input('checkboxes', 'type[]', $x, explode(',',$irgs['type'][1]), 'onclick="if (this.checked) parentNode.previousSibling.firstChild.checked=true;"', '<br>');
    $replace_type = ob_get_contents();
    ob_end_clean();
    $search_panel = str_replace($org, $replace_type, $search_panel);
    unset($regs, $org, $x, $replace_type);
}
if (preg_match('!<select name="level" [^>]*>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*<option value="(\d+)"( selected="selected")?>([^<]*) </option>\s*</select>!isU', $search_panel, $regs))
{
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

                showXHTML_tr_B('class="bg04 font01 new"');
                  showXHTML_td();
                  showXHTML_td_B('colspan="2"');
                    showXHTML_input('checkbox', 'immediate_random_pick', 'immediate_random_pick', '', 'onclick="randomCheck2(this);" style="display: none;" ' . (isset($irgs)?' checked':'')); echo $MSG['total'][$sysSession->lang];
                    showXHTML_input('text', 'immediate_random_pick_amount', ($irgs['amount'][1])?$irgs['amount'][1]:0, '', 'id="immediate_random_pick_amount" style="background: transparent;box-shadow: transparent 0 0 0 inset;border: 0px;cursor: default;text-align: center;" size="5" readonly onchange="typeCheck(this, \'int\');if (parseInt(this.value)>MaxPickedNum){alert(msg_overNumber); this.value=MaxPickedNum; this.form.immediate_random_pick_amount.focus();event.cancelBubble = true;}" onkeypress="intOnly();"'); echo $MSG['item'][$sysSession->lang];
                    showXHTML_input('button', '', $MSG['msg_more'][$sysSession->lang], '', 'class="cssBtn" onclick="createRadomItem(this);"');
                  showXHTML_td_E();
                showXHTML_tr_E();

            $tmp_search_panel = str_replace(array('chgParagraph(this.value, 1);', 'spanParagraph1'), array('chgParagraph(this.value, \'R1\');', 'spanParagraphR1'), $search_panel);
            echo '<tr><td colspan="3" width="100%"><table style="/* display: inline */" width="960">', $tmp_search_panel , '</table></td></tr>';

                showXHTML_tr_B('class="bg04 font01"');
                  showXHTML_td('align="right"', $MSG['score_assigned1'][$sysSession->lang]);
                  showXHTML_td_B('colspan="2"');
                    echo $MSG['total_score'][$sysSession->lang];
                    showXHTML_input('text', 'immediate_random_pick_score', $irgs['score'][1], '', 'id="immediate_random_pick_score" size="5" onchange="typeCheck(this, \'float\');" onkeypress="floatOnly();"');
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
                  showXHTML_td_B('width="110" height="320" align="center"');
                    echo "<div id=\"toolPanel\" style=\"position: absolute; left: 35px; top: 100px; width: 90px\">\n";
                    showXHTML_form_B('style="display:inline;"');
                      showXHTML_input('button', '', $MSG['block_btm1'][$sysSession->lang]    , '', 'onclick="paperTuning(1);" style="width: 90px" class="cssBtn"');
                      showXHTML_input('button', '', $MSG['block_btm2'][$sysSession->lang]    , '', 'onclick="paperTuning(2);" style="width: 90px" class="cssBtn"');
                      showXHTML_input('button', '', $MSG['block_btm3'][$sysSession->lang]    , '', 'onclick="paperTuning(3);" style="width: 90px" class="cssBtn"');
                      showXHTML_input('button', '', $MSG['block_btm4'][$sysSession->lang]    , '', 'onclick="paperTuning(4);" style="width: 90px" class="cssBtn"');
                      if (QTI_which != 'questionnaire'){
                      showXHTML_input('button', '', $MSG['assign_score'][$sysSession->lang]  , '', 'onclick="displayDynamicDialogWindow(\'sectionScoreDialog\', \'' . $MSG['input_score'][$sysSession->lang] . '\', 7);" style="width: 90px" class="cssBtn"');// 指定分數 paperTuning(7);
                      showXHTML_input('button', '', $MSG['assign_average'][$sysSession->lang], '', 'onclick="displayDynamicDialogWindow(\'sectionScoreDialog\', \'' . $MSG['input_total'][$sysSession->lang] . '\', 8);" style="width: 90px" class="cssBtn"');// 平均配方 paperTuning(8);
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

// TAB-5
            echo '<div id="tabContent4" style="display: none">', "\n";

              showXHTML_table_B('id="previewTable" border="0" cellpadding="3" cellspacing="1" width="1000" style="border-collapse: collapse" class="box01"');

                showXHTML_tr_B('class="bg02 font01"');
                  showXHTML_td_B('align="right" nowrap');
                    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]  ,  '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                    showXHTML_input('button', '', $MSG['prev_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(' . (QTI_which == 'exam' ? 'exam_default' : 2) . ');"');
                    showXHTML_input('button', '', $MSG['complete'][$sysSession->lang],  '', 'class="cssBtn save-content" onclick="saveContent();"');
                    if (QTI_which != 'questionnaire') showXHTML_input('button', '', $MSG['hide answer'][$sysSession->lang], '', 'class="cssBtn" onclick="prevExamPaper();"');
                    if (QTI_which == 'exam')          showXHTML_input('button', '', $MSG['Export_MHT'][$sysSession->lang],  '', 'class="cssBtn" onclick="ExportContent();"');
                  showXHTML_td_E();
                showXHTML_tr_E();

                showXHTML_tr_B('class="bg03 font01"');
                  showXHTML_td_B('nowrap id="examPreview"');
                  showXHTML_td_E();
                showXHTML_tr_E();

                showXHTML_tr_B('class="bg02 font01"');
                  showXHTML_td_B('align="right" nowrap');
                    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]  ,  '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\'exam_maintain.php\');"');
                    showXHTML_input('button', '', $MSG['prev_step'][$sysSession->lang], '', 'class="cssBtn" onclick="switchTab(' . (QTI_which == 'exam' ? 'exam_default' : 2) . ');"');
                    showXHTML_input('button', '', $MSG['complete'][$sysSession->lang],  '', 'class="cssBtn save-content" onclick="saveContent();"');
                    if (QTI_which != 'questionnaire') showXHTML_input('button', '', $MSG['hide answer'][$sysSession->lang], '', 'class="cssBtn" onclick="prevExamPaper();"');
                    if (QTI_which == 'exam')          showXHTML_input('button', '', $MSG['Export_MHT'][$sysSession->lang],  '', 'class="cssBtn" onclick="ExportContent();"');
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

// 指定分數、平均配分
      echo '<div id="sectionScoreDialog" name="sectionScoreDialog" style="position: absolute; top: 10px; border-width:3; display: none">', "\n";
      showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse"');
        showXHTML_tr_B();
          showXHTML_td_B();
          // 新增作業不知道何故，變成下載
//            $ary = array(array($MSG['input_score'][$sysSession->lang], 'tabsSet',  ''));
//            showXHTML_tabs($ary, 1);
            echo '
                <table border="0" cellspacing="0" cellpadding="0">
                        <tbody><tr class="cssTr">
                                <td class="disable-select"><img onselectstart="return false;" id="ImgL17" myattr="17" tabsidx="5" src="/theme/default/teach/title_on_01.gif?1509878127" width="25" height="30" border="0" align="absbottom"> </td>
                                <td align="center" valign="bottom" nowrap="NoWrap" id="TitleID17" myattr="17" tabsidx="5" style="cursor: default; background-image: url(\'/theme/default/teach/title_on_02.gif?1509878127\');" class="cssTabs" onselectstart="return false;">' . $MSG['input_score'][$sysSession->lang] . '</td>
                                <td class="disable-select"><img onselectstart="return false;" id="ImgR17" myattr="17" tabsidx="5" src="/theme/default/teach/title_on_03.gif?1509878127" width="28" height="30" border="0" align="absbottom"> </td>
                                <td class="cssTd" width="100%">&nbsp; </td>
                        </tr>
                </tbody></table>                
            ';
          showXHTML_td_E();
        showXHTML_tr_E();
        showXHTML_tr_B();
          showXHTML_td_B('class="bg01"');
          showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="box01"');

            showXHTML_tr_B('class="bg02"');
              showXHTML_td_B('align="right"');
                showXHTML_input('button', '', $MSG['sure'][$sysSession->lang], '', 'class="cssBtn" onclick="paperTuning(parseInt(document.getElementById(\'action\').value, 10));"');
                showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="document.getElementById(\'sectionScoreDialog\').style.display=\'none\';"');
              showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="bg03"');
              showXHTML_td_B();
                showXHTML_input('text', 'score', '', '', 'class="box02"');
                showXHTML_input('hidden', 'action');
              showXHTML_td_E();
            showXHTML_tr_E();

          showXHTML_table_E();
              showXHTML_td_E();
            showXHTML_tr_E();

          showXHTML_table_E();
      echo "</div>\n";

    echo '<form style="display: none"><input type=hidden" name="saveTemporaryContent" id="saveTemporaryContent"></form>';
    showXHTML_body_E();
?>
