<?php
/**
 * 第二版 QTI parser   sence 2004-06-02	by Wiseguy Liang
 * 建立日期：2004/06/02
 * @author  Wiseguy Liang
 * @version $Id: QTI_transformer.php,v 1.4 2009-09-23 01:55:38 edi Exp $
 * @copyright 2003 SUNNET
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/lib/editor.php');

if (!function_exists('setEncoding')) {
    function setEncoding($xml, $encoding = 'UTF-8')
    {
        if (preg_match('/<\?xml\b[^>]*\?>/isU', $xml, $regs)) {
            if (preg_match('/\bencoding\s*=\s*/isU', $regs[0]))
                return $xml;
            else
                return preg_replace('/\?>/', ' encoding="' . $encoding . '"?>', $xml, 1);
        } else
            return '<?xml version="1.0" encoding="UTF-8"?>' . $xml;
    }
}

if (!isset($sysSession)) {
    $sysSession->theme = 'default';
}
define('Table_header', TRUE);
define('Table_footer', FALSE);
$sectionSerial = 0;
$itemSerial    = 1;

$qti_item_types = array(
    1 => './/response_lid/render_choice',
    2 => './/response_str/render_fib',
    3 => './/response_num/render_fib',
    4 => './/response_grp/render_extension',
    5 => './/response_lid/render_hotspot',
    6 => './/response_lid/render_extension',
    7 => './/response_num/render_slider',
    8 => './/response_xy/render_hotspot',
    9 => './/response_extension/render_extension'
);

$item_type_names = array(
    1 => $MSG['item_type1'][$sysSession->lang],
    2 => $MSG['item_type2'][$sysSession->lang],
    3 => $MSG['item_type3'][$sysSession->lang],
    4 => $MSG['item_type4'][$sysSession->lang],
    5 => $MSG['item_type5'][$sysSession->lang],
    6 => $MSG['item_type6'][$sysSession->lang],
    7 => $MSG['item_type7'][$sysSession->lang]
);

if (!defined('QTI_env'))
    list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
else
    $topDir = QTI_env;

if ($topDir == 'academic')
    $save_uri = sprintf('/base/%05d/%s/Q/', $sysSession->school_id, QTI_which);
else
    $save_uri = sprintf('/base/%05d/course/%08d/%s/Q/', $sysSession->school_id, $sysSession->course_id, QTI_which);

$attach_uri  = $save_uri;
$attach_path = sysDocumentRoot . $save_uri;
/**
 * 判斷檔名是否為圖檔
 * param string $fname 檔名字串
 * return bool 是否為圖檔
 */
function is_pic($fname)
{
    return eregi('\.(jpg|jpeg|jpe|gif|png|bmp)$', $fname);
}

/**
 * 判斷檔名是否為影像檔
 * param string $fname 檔名字串
 * return bool 是否為影像檔
 */
function is_avi($fname)
{
    return eregi('\.(wmv|asf|mpg|mpeg|avi|rm|ram|mov)$', $fname);
}

/**
 * 判斷檔名是否為音訊檔
 * param string $fname 檔名字串
 * return bool 是否為音訊檔
 */
function is_snd($fname)
{
    return eregi('\.(wma|mp3|wav|mid|ogg|ac3|ra)$', $fname);
}

function gen_link($id, $fname, $dname = '')
{
    global $MSG, $save_uri, $sysSession;
    
    if (empty($fname))
        return '';
    elseif (is_pic($fname))
        // 圖片最大到100％，避免ipad mini破版
        return sprintf('<img src="%s%s/%s" align="top" alt="%s" style="max-width: 100%%"/>', $save_uri, $id, $fname, $fname);
    elseif (is_avi($fname))
        return sprintf('<embed src="%s%s/%s" align="absmiddle" type="video/*" volume="0" mime-types="mime.types" %s autostart="false" title="%s">', $save_uri, $id, $fname, (eregi('\.(rm|ram)$', $fname) ? 'WIDTH=352 HEIGHT=276 NOJAVA=true CONTROLS="ImageWindow,ControlPanel"' : ''), $fname);
    elseif (is_snd($fname)) {
        /*********# 027189    begin 2012/10/08 mars chrome 支援播放mp3 加上判斷 ********/
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($agent, 'MSIE')) {
            $browser = 'ie';
        } else if (strpos($agent, 'Firefox')) {
            $browser = 'ff';
        } else if (strpos($agent, 'Chrome')) {
            $browser = 'chr';
        } else if (strpos($agent, 'Safari')) {
            $browser = 'sf';
        } else {
            $browser = 'op';
        }
        $win = strpos($agent, 'Windows') ? true : false;
        if (($browser === 'sf' && $win === false) || ((strpos($agent, 'Chrome') !== false || $browser === 'ff' || ($browser === 'sf' && $win === true)) && strrchr($fname, '.') === '.mp3')) {
            /*#48230 [IE][教室/評量區/測驗/進行測驗] 題目附檔若是mp3，會出現叉燒包：修改播放器程式碼寫法*/
                return sprintf('<audio src="%s%s/%s" preload="auto" controls></audio>',
                                $save_uri,
                                $id,
                                $fname
                              );
           
        } else {
            
            /*#483350 Chrome[教室/評量區/QTI/作業] 題目附檔若是wma，chrome無法播放。：修改寫法*/
            return sprintf('<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="400" height="64" > <param name="invokeURLs" value="0" > <param name="autostart" value="0" /> <param name="url" value="%s%s/%s"? id="abbc"/>  <embed src="%s%s/%s" autostart="0" type="application/x-mplayer2" width="400" height="64"></embed>  </object>', $save_uri, $id, $fname, $save_uri, $id, $fname);
        }
    } elseif (strrchr($fname, '.') == '.swf') /*            return sprintf('
    <OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
    codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0">
    <PARAM NAME="movie" VALUE="%s%s/%s">
    <PARAM NAME="quality" VALUE="high">
    <PARAM NAME="valign" VALUE="absmiddle">
    <EMBED src="%s%s/%s" quality="high" VALIGN="absmiddle" TYPE="application/x-shockwave-flash"
    PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer">
    </EMBED>
    </OBJECT>', $save_uri, $id, $fname, $save_uri, $id, $fname);
    */ 
        return sprintf('<object type="application/x-shockwave-flash" data="%s%s/%s"><param name="movie" value="%s%s/%s" /></object>', $save_uri, $id, $fname, $save_uri, $id, $fname);
    else
        return sprintf('<a href="%s%s/%s" target=_blank" class="cssAnchor">%s</a>', $save_uri, $id, $fname, $dname == '' ? $fname : $dname);
}


/**
 * 以 table 顯示各 container (questestinterop, objectbank, assessment, section)
 */
function drawTable($isBegin, $blockType)
{
    global $exam_id, $time_id, $ticket, $sectionSerial, $itemSerial, $MSG, $sysSession;
    ob_start();
    if ($isBegin)
        switch ($blockType) {
            case 'questestinterop':
                $ary = array(
                    array(
                        $MSG['exam_context'][$sysSession->lang]
                    )
                );
                // 判斷是否內嵌在frame中，目前批改試卷頁面是設定0
                if (SINGLE === '0') {
                    $width = 800;
                } else {
                    // 因應 ipad mini 最大先設定到950
                    $width = 950;
                }
                $display_css['table'] = 'width="' . $width . '"';
                showXHTML_tabFrame_B($ary, 1, 'responseForm', '', 'method="POST" enctype="multipart/form-data" action="save_answer.php" target="submitTarget" style="display: inline"', false, false, $display_css);
                echo <<< EOB
            <input type="hidden" name="exam_id" value="$exam_id">
            <input type="hidden" name="time_id" value="$time_id">
            <input type="hidden" name="ticket" value="$ticket">
            <!-- questestinterop begin -->
EOB;
                showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable" style="width:100%;max-width:'.$width.'px;"');
                // Bug#1513 但判斷功能與環境，是否顯示『標準答案』與『學生答案』的圖示說明 by Small 2006/12/25
                $file     = basename($_SERVER['PHP_SELF']);
                // 若功能檔案有在底下的array中，則顯示圖示說明(exemplar->作業繳交狀態、result->察看得分、content->作業測驗批改)
                $arr_file = array(
                    'view_exemplar.php',
                    'view_result.php',
                    'exam_correct_content.php'
                );
                if (in_array($file, $arr_file)) {
                    showXHTML_tr_B('class="cssTrHead"');
                    showXHTML_td_B('colspan="4"');
                    if (QTI_DISPLAY_ANSWER){
                        echo '<span style="background-color: green"><input type=radio name="demo_radio_1"/></span>' . $MSG['demo_cor_answer'][$sysSession->lang];
                    }
                    echo '<input type=radio name="demo_radio_1" checked/>' . $MSG['demo_stud_answer'][$sysSession->lang];
                    showXHTML_td_E('');
                    showXHTML_tr_E('');
                }
                break;
            default:
                showXHTML_tr_B();
                showXHTML_td_B('colspan="4"');
                showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="100%" class="cssTable"');
                echo '<!-- ', $blockType, ' begin -->';
                break;
        } else
        switch ($blockType) {
            case 'questestinterop':
                showXHTML_table_E();
                echo '<script type="text/javascript" language="javascript" lang="zh-tw" charset="Big5" src="/lib/jquery/jquery-1.7.2.min.js"></script>';
                echo '<script type="text/javascript" language="javascript" lang="zh-tw" charset="Big5" src="/learn/exam/dropdown.js?' . filemtime(sysDocumentRoot . '/learn/exam/dropdown.js') . '"></script>';
                echo '<!-- questestinterop end -->';
                showXHTML_tabFrame_E();
                break;
            default:
                showXHTML_td_E();
                showXHTML_tr_E();
                echo '<!-- ', $blockType, ' end -->';
                showXHTML_table_E();
                break;
        }
    $ret = ob_get_contents();
    ob_end_clean();
    return $ret;
}


/**
 * 去掉 XML 的空白文字節點
 */
function rm_whitespace(&$node)
{
    foreach ($node->child_nodes() as $subnode) {
        switch ($subnode->node_type()) {
            case XML_ELEMENT_NODE:
                rm_whitespace($subnode);
                break;
            case XML_TEXT_NODE:
                if ('' == trim($subnode->node_value()))
                    $node->remove_child($subnode);
                break;
        }
    }
}

/**
 * transfer string from RTF to TEXT
 */
function rtf2txt($str)
{
    return preg_replace(array(
        '/\\\\[^;]*;/',
        '/\\\\par$/',
        '/(\\\\[^ ]*\s+)+/',
        '/[{}]/'
    ), array(
        '',
        '<br>',
        ' ',
        ''
    ), $str);
}

function listExtensionFiles($rander_id, $filename)
{
    global $sysSession, $exam_id, $time_id, $examinee;
    
    $path = sysDocumentRoot . sprintf('/base/%05u/course/%08u/exam/A/%09u/%s/%03u/' . $rander_id . '/' . un_adjust_char($filename), $sysSession->school_id, $sysSession->course_id, $exam_id, $examinee, $time_id);
    if (($files = glob($path)) === false || count($files) == 0)
        return '<BLOCKQUOTE>no response.</BLOCKQUOTE>';
    else {
        $ret = '<BLOCKQUOTE>';
        $l   = strlen(sysDocumentRoot);
        foreach ($files as $file) {
            $u8_file = adjust_char($file);
            $uri     = htmlspecialchars(substr($u8_file, $l));
            if ($rander_id == 'REC01')
                $ret .= '<span style="font-family: Webdings; font-size: 16pt; cursor: pointer; vertical-align: middle" title="play" onclick="this.outerHTML=\'<embed type=\\\'audio/mpeg\\\' src=\\\'' . $uri . '\\\'style=\\\'width: 66px; height: 26px; vertical-align: middle\\\'></embed>\';">4</span>' . '<a href="' . $uri . '" target="_blank" class="cssAnchor" title="download size=' . number_format(filesize($file)) . ' bytes" style="font-family: Wingdings; font-size: 18pt; vertical-align: middle; margin-left: 10px"><</a><br />';
            else
                $ret .= '<a href="' . $uri . '" target="_blank" class="cssAnchor">' . htmlspecialchars(basename($u8_file)) . ' (' . number_format(filesize($file)) . ')</a><br />';
        }
        
        return $ret . '</BLOCKQUOTE>';
    }
}

/**
 * 轉換 <Material>
 */
function travelMaterial($node, $html_replace = false, $ident = '')
{
    global $ctx;
    
    $ret = '';
    foreach ($node->child_nodes() as $subnode) {
        
        // 題目
        switch ($subnode->tagname()) {
            // 文字
            case 'mattext':
                $r = ($subnode->get_attribute('texttype') == 'text/rtf') ? rtf2txt((($html_replace) ? htmlspecialchars($subnode->get_content()) : $subnode->get_content())) : (($html_replace) ? htmlspecialchars($subnode->get_content()) : $subnode->get_content());
                if (in_array($ident, array(
                    'T',
                    'F'
                ))) {
                    if ($r == 'Agree')
                        $r = '<img src="/theme/default/teach/right.gif" align="absmiddle" style="position: relative; top: -0.5em;" />';
                    elseif ($r == 'Disagree')
                        $r = '<img src="/theme/default/teach/wrong.gif" align="absmiddle" style="position: relative; top: -0.5em;"/>';
                }
                $ret .= $r;
                break;
            case 'matemtext':
                $ret .= '<b><i>' . (($html_replace) ? htmlspecialchars($subnode->get_content()) : $subnode->get_content()) . '</i></b>';
                break;
            case 'matimage':
                $ret .= '<img src="' . $subnode->get_attribute('uri') . '" border="0" align="absmiddle" />';
                break;
            case 'mataudio':
                $ret .= '<embed src="' . $cur_node->get_attribute('uri') . '" width="70" height="26" valign="absmiddle">';
                break;
            case 'matvideo':
                $ret .= '<embed src="' . $cur_node->get_attribute('uri') . '" valign="absmiddle">';
                break;
            case 'matapplet':
                $ret .= '<applet code="' . $cur_node->get_attribute('uri') . '" valign="absmiddle">';
                break;
            case 'matapplication':
                break;
            case 'matref':
                $result = $ctx->xpath_eval('//mattext[@label="' . $subnode->get_attribute('linkrefid') . '"]');
                if (count($result->nodeset))
                    $ret .= ($result->nodeset[0]->get_attribute('texttype') == 'text/rtf') ? rtf2txt((($html_replace) ? htmlspecialchars($result->nodeset[0]->get_content()) : $result->nodeset[0]->get_content())) : (($html_replace) ? htmlspecialchars($result->nodeset[0]->get_content()) : $result->nodeset[0]->get_content());
                break;
            case 'matbreak':
                $ret .= '<br />';
                break;
            case 'mat_extension':
                break;
        }
    }
    $ret = str_replace('&lt;br&gt;', '<br />', $ret);
    return $ret;
}

/**
 * travel 選項內容結構
 */
function travelResponselabel($node, $ident = '')
{
    global $ctx;
    
    $ret = '';
    foreach ($node->child_nodes() as $subnode) {
        switch ($subnode->tagname()) {
            case 'material':
                $ret .= travelMaterial($subnode, true, $ident);
                break;
            case 'material_ref':
                $ref = $ctx->xpath_eval('//material[@label="' . $subnode->get_attribute('linkrefid') . '"]');
                if (count($ref->nodeset))
                    $ret .= travelMaterial($ref->nodeset[0]);
                break;
            case 'flow_mat':
                $ret .= travelResponselabel($subnode, $ident);
                break;
        }
    }
    return $ret;
}

/**
 * 產生選擇題
 */
function generateCHOICE($item, $node)
{
    global $ctx, $sysSession, $attachments, $itemSerial, $MSG, $item_types, $item_type_names, $ctrl_paging;
    
    $item_id     = $item->get_attribute('ident');
    $result      = $ctx->xpath_eval('./*[substring-before(name(), "_") = "response"]', $node);
    $response_id = $result->nodeset[0]->get_attribute('ident');
    
    $p = $node->parent_node();
    if ($p->tagname() == 'presentation') { // 取得配分 (在尚未遞迴的第一階時)
        $result  = $ctx->xpath_eval('./resprocessing/outcomes/decvar[@defaultval]', $item);
        $outcome = count($result->nodeset) ? floatval($result->nodeset[0]->get_attribute('defaultval')) : 0;
        $ret     = '';
        if ($item_type_names[$item_types[$item_id]])
            $ret .= '<b>' . $item_type_names[$item_types[$item_id]] . '</b><br>';
        $ret .= sprintf('<b><div>%s</div><div>[%.2f]</div></b></td><td valign="top" nowrap>', $MSG['score_assigned'][$sysSession->lang], $outcome);
        
        $student_ans = array();
        if (QTI_DISPLAY_RESPONSE) {
            $locking = false;
            $re      = $ctx->xpath_eval("//item_result[@ident_ref='$item_id']/response[@ident_ref='$response_id']/response_value");
            if (count($re->nodeset))
                foreach ($re->nodeset as $piece) {
                    $nd = $ctx->xpath_eval('./num_attempts/text()', $piece->parent_node());
                    if (intval($nd->nodeset[0]->node_value()) <= 1 && $piece->get_content() == '') {
                        continue;
                    }
                    $student_ans[$response_id][] = $piece->get_content();
                    /*
                    if ($piece->get_content() != '')
                    {
                    $student_ans[$response_id][] = $piece->get_content();
                    $locking = true;
                    }
                    */
                    if ($piece->get_content() != '')
                        $locking = true;
                }
            if ($ctrl_paging != 'can_return')
                $locking = false;
        }
        $correct_ans = array();
        
        if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER || (QTI_DISPLAY_RESPONSE && $_SERVER['SCRIPT_URL']=='/learn/exam/view_result.php')) {
            $as = $ctx->xpath_eval('./resprocessing/respcondition[1]/conditionvar/varequal', $item);
            if ((QTI_DISPLAY_ANSWER || QTI_DISPLAY_RESPONSE) && count($as->nodeset))
                foreach ($as->nodeset as $piece)
                    $correct_ans[$piece->get_attribute('respident')][] = $piece->get_content();
            $answerResult = (!preg_match('/^a:\d+:\{([^;]+;a:\d+:\{([^;]+;s:0:"";)*\})*\}$/', serialize($correct_ans)) && $correct_ans === $student_ans) ? true : false;
            if (QTI_DISPLAY_RESPONSE) {
                $score_node = $ctx->xpath_eval("//item_result[@ident_ref='$item_id']/outcomes/score/score_value");
                if (is_array($score_node->nodeset) && count($score_node->nodeset)) {
                    if ($score_node->nodeset[0]->has_child_nodes()) {
                        $corrected = true;
                        $outcome   = floatval($score_node->nodeset[0]->get_content());
                    }
                }
                
                $ret .= sprintf('<img src="/theme/%s/teach/%s" align="top" border="0" /><br>%s<input type="text" name="item_scores[%s]" size="3" value="%.2f" class="cssInput"></td>' . "\n\t\t\t\t\t<td align='left'>%d. ", $sysSession->theme, ($answerResult ? 'icon_currect.gif' : 'icon_wrong.gif'), $MSG['earn'][$sysSession->lang], $item_id, ($answerResult || $corrected ? $outcome : 0), $itemSerial++);
            } else
                $ret .= sprintf("&nbsp;</td>\n\t\t\t\t\t<td align='left'>%d. ", $itemSerial++);
        } else
            $ret .= sprintf("&nbsp;</td>\n\t\t\t\t\t<td align='left'>%d. ", $itemSerial++);
    } else
        $ret = '';
    
    foreach ($node->child_nodes() as $subnode) {
//        echo '<pre>';
//        var_dump('level-1: ' . $subnode->tagname());
//        echo '</pre>';
        switch ($subnode->tagname()) {
            case 'flow':
                $ret .= generateFILL($item_id, $subnode);
                break;
            // 題目
            case 'material':
                $ret .= travelMaterial($subnode);
                break;
            case 'material_ref':
                $ref = $ctx->xpath_eval('//material[@label="' . $subnode->get_attribute('linkrefid') . '"]');
                if (count($ref->nodeset))
                    $ret .= travelMaterial($ref->nodeset[0]);
                break;
            case 'response_lid':
                if (is_array($attachments[$item_id]['topic_files']))
                    foreach ($attachments[$item_id]['topic_files'] as $key => $value)
                        $ret .= ('<br />' . gen_link($item_id, $value, $key));
                
                $item_type = strtolower($subnode->get_attribute('rcardinality'));
                foreach ($subnode->child_nodes() as $subnode2) {
//                    echo '<pre>';
//                    var_dump('level-2: ' . $subnode2->tagname());
//                    echo '</pre>';
                    switch ($subnode2->tagname()) {
                        case 'material':
                            $ret .= travelMaterial($subnode2);
                            break;
                        case 'material_ref':
                            $ref = $ctx->xpath_eval('//material[@label="' . $subnode2->get_attribute('linkrefid') . '"]');
                            if (count($ref->nodeset))
                                $ret .= travelMaterial($ref->nodeset[0]);
                            break;
                        case 'render_choice':
                            $ret .= "\n\t\t\t\t\t\t<ol type=\"a\">\n";
                            $keys   = @array_keys($attachments[$item_id]['render_choice_files']);
                            $values = @array_values($attachments[$item_id]['render_choice_files']);
                            foreach ($subnode2->child_nodes() as $subnode3) {
//                                echo '<pre>';
//                                var_dump('level-3: ' . $subnode3->tagname());
//                                echo '</pre>';
                                if ($subnode3->tagname() == 'flow_label')
                                    $subnode3 = $subnode3->first_child();
                                switch ($subnode3->tagname()) {
                                    case 'material':
                                        $ret .= travelMaterial($subnode3);
                                        break;
                                    case 'material_ref':
                                        $ref = $ctx->xpath_eval('//material[@label="' . $subnode3->get_attribute('linkrefid') . '"]');
                                        if (count($ref->nodeset))
                                            $ret .= travelMaterial($ref->nodeset[0]);
                                        break;
                                    case 'response_label':
                                        $subnode3Ident = $subnode3->get_attribute('ident');
                                        $ret .= sprintf(
                                            "\t\t\t\t\t\t\t" . '<li><span%s><input type="%s" name="ans[%s][%s]%s" value="%s" %s ' . ($locking ? 'disabled' : '') . '></span>',
                                                ((QTI_DISPLAY_ANSWER && is_array($correct_ans[$response_id]) && in_array($subnode3->get_attribute('ident'), $correct_ans[$response_id])) ? ' style="background-color: green"' : ''),
                                                (($item_type == 'single') ? 'radio' : 'checkbox'), 
                                                $item_id, 
                                                $response_id, 
                                                (($item_type == 'single') ? '' : '[]'), 
                                                $subnode3Ident, 
                                                ((QTI_DISPLAY_RESPONSE && is_array($student_ans[$response_id]) && in_array($subnode3->get_attribute('ident'), $student_ans[$response_id])) ? ' checked' : '')
                                        ) . (travelResponselabel($subnode3, $subnode3Ident)) . gen_link($item_id, @array_shift($values), @array_shift($keys)) . "</li>\n";
                                        break;
                                }
                            }
                            $ret .= "\t\t\t\t\t\t</ol>\n";
                            break;
                    }
                }
                break;
        }
    }
    return $ret . "\t\t\t\t\t</td>\n\t\t\t\t\t<td>";
}

/**
 * 填充題比對 function
 */
function fillCompareMethod($ary1, $ary2, $method = false)
{
    if (!is_array($ary1) || !is_array($ary2) || count($ary1) != count($ary2))
        return false;
    switch ($method) {
        case 'case-insensitive':
            foreach ($ary2 as $k => $v)
                if (strcasecmp($ary1[$k], $v) !== 0)
                    return false;
            return true;
            break;
        
        case 'ignore-all-space':
            foreach ($ary2 as $k => $v)
                if (preg_replace('/\s+/', $ary1[$k]) != preg_replace('/\s+/', $v))
                    return false;
            return true;
            break;
        
        case 'case-insensitive && ignore-all-space':
            foreach ($ary2 as $k => $v)
                if (strcasecmp(preg_replace('/\s+/', $ary1[$k]), preg_replace('/\s+/', $v)) !== 0)
                    return false;
            return true;
            break;
        
        default:
            return ($ary1 === $ary2);
    }
}

/**
 * 產生填充題
 */
function generateFILL($item, $node)
{
    global $ctx, $attachments, $sysSession, $itemSerial, $MSG, $item_types, $item_type_names, $ctrl_paging;
    global $co_editor_idx;
    
    $item_id     = $item->get_attribute('ident');
    $result      = $ctx->xpath_eval('./*[substring-before(name(), "_") = "response"]', $node);
    $response_id = $result->nodeset[0]->get_attribute('ident');
    $correct_num = array();
    $k           = 0;
    $p           = $node->parent_node();
    if ($p->tagname() == 'presentation') { // 取得配分 (在尚未遞迴的第一階時)
        $result  = $ctx->xpath_eval('./resprocessing/outcomes/decvar[@defaultval]', $item);
        $outcome = count($result->nodeset) ? floatval($result->nodeset[0]->get_attribute('defaultval')) : 0;
        
        // 取得批改方式 (完全相同、大小不拘、空白忽略)
        $resprocessing  = $result->nodeset[0]->parent_node();
        $resprocessing  = $resprocessing->parent_node();
        $compare_method = $resprocessing->get_attribute('wm:compare_method');
        
        $ret = '';
        if ($item_type_names[$item_types[$item_id]])
            $ret .= '<b>' . $item_type_names[$item_types[$item_id]] . '</b><br>';
        $ret .= sprintf('<b><div>%s</div><div>[%.2f]</div></b></td><td valign="top" nowrap>', $MSG['score_assigned'][$sysSession->lang], $outcome);
        
        $student_ans = array();
        $com_ans = array();
        if (QTI_DISPLAY_RESPONSE) {
            $locking = false;
            $re      = $ctx->xpath_eval("//item_result[@ident_ref='$item_id']/response/response_value");
            if (count($re->nodeset))
                foreach ($re->nodeset as $piece) {
                    $p                                             = $piece->parent_node();
                    $student_ans[$p->get_attribute('ident_ref')][] = $piece->get_content();
                    $com_ans[$p->get_attribute('ident_ref')][] = strip_tags(htmlspecialchars_decode(stripslashes($piece->get_content())));
                    if ($piece->get_content() != '')
                        $locking = true;
                }
            if ($ctrl_paging != 'can_return')
                $locking = false;
        }
        
        
        $correct_ans = array();
        
        $as = $ctx->xpath_eval('./resprocessing/respcondition[1]/conditionvar/varequal', $item);
        if (count($as->nodeset)) {
            foreach ($as->nodeset as $piece)
                $correct_ans[$piece->get_attribute('respident')][] = $piece->get_content();
        } else {
            $as = $ctx->xpath_eval('./itemfeedback/solution/solutionmaterial/material/mattext', $item);
            if (count($as->nodeset))
                $correct_ans['FIB91'][] = $as->nodeset[0]->get_content();
        }
        foreach ($correct_ans as $index => $value) {
            $correct_ans[$index][$k] = strip_tags($value[0]);
            $correct_num[]           = strlen($correct_ans[$index][$k]);
        }
        
        if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER || (QTI_DISPLAY_RESPONSE && $_SERVER['SCRIPT_URL']=='/learn/exam/view_result.php')) {
            if (QTI_DISPLAY_ANSWER) {
                /*
                $as = $ctx->xpath_eval('./resprocessing/respcondition[1]/conditionvar/varequal', $item);
                if (count($as->nodeset))
                {
                foreach($as->nodeset as $piece) $correct_ans[$piece->get_attribute('respident')][] = $piece->get_content();
                }
                else
                {
                $as = $ctx->xpath_eval('./itemfeedback/solution/solutionmaterial/material/mattext', $item);
                if (count($as->nodeset)) $correct_ans['FIB91'][] = $as->nodeset[0]->get_content();
                }
                */
            }
            $answerResult = (!preg_match('/^a:\d+:\{([^;]+;a:\d+:\{([^;]+;s:0:"";)*\})*\}$/', serialize($correct_ans)) && fillCompareMethod($correct_ans, $com_ans)) ? true : false;
            
            if (QTI_DISPLAY_RESPONSE) {
                $score_node = $ctx->xpath_eval("//item_result[@ident_ref='$item_id']/outcomes/score/score_value");
                if (is_array($score_node->nodeset) && count($score_node->nodeset)) {
                    if ($score_node->nodeset[0]->has_child_nodes()) {
                        $corrected = true;
                        $outcome   = floatval($score_node->nodeset[0]->get_content());
                    }
                }
                
                $ret .= sprintf('<img src="/theme/%s/teach/%s" align="top" border="0" /><br>%s<input type="text" name="item_scores[%s]" size="3" value="%.2f" class="cssInput"></td>' . "\n\t\t\t\t\t<td align='left'>%d. ", $sysSession->theme, 'icon_hand.gif', // 不顯示對錯 ($answerResult ? 'icon_currect.gif' : 'icon_wrong.gif'),
                    $MSG['earn'][$sysSession->lang], $item_id, ($answerResult || $corrected ? $outcome : 0), $itemSerial++);
            } else
                $ret .= sprintf("&nbsp;</td>\n\t\t\t\t\t<td align='left'>%d. ", $itemSerial++);
        } else
            $ret .= sprintf("&nbsp;</td>\n\t\t\t\t\t<td align='left'>%d. ", $itemSerial++);
    } else
        $ret = '';
    
    foreach ($node->child_nodes() as $subnode) {
        switch ($subnode->tagname()) {
            case 'flow':
                $ret .= generateFILL($item_id, $subnode);
                break;
            case 'material':
                $ret .= travelMaterial($subnode);
                break;
            case 'material_ref':
                $ref = $ctx->xpath_eval('//material[@label="' . $subnode->get_attribute('linkrefid') . '"]');
                if (count($ref->nodeset))
                    $ret .= travelMaterial($ref->nodeset[0]);
                break;
            case 'response_str':
            case 'response_num':
            case 'response_extension':
                $res_id = $subnode->get_attribute('ident');
                foreach ($subnode->child_nodes() as $subnode2) {
                    switch ($subnode2->tagname()) {
                        case 'material':
                            $ret .= travelMaterial($subnode2);
                            break;
                        case 'material_ref':
                            $ref = $ctx->xpath_eval('//material[@label="' . $subnode2->get_attribute('linkrefid') . '"]');
                            if (count($ref->nodeset))
                                $ret .= travelMaterial($ref->nodeset[0]);
                            break;
                        case 'render_fib':
                            $maxchars = max(intval($subnode2->get_attribute('maxchars')), 4);
                            $columns  = max(intval($subnode2->get_attribute('columns')), $maxchars * 2);
                            $rows     = max(intval($subnode2->get_attribute('rows')), 1);
                            foreach ($subnode2->child_nodes() as $subnode3) {
                                if ($subnode3->tagname() == 'flow_label')
                                    $subnode3 = $subnode3->first_child();
                                switch ($subnode3->tagname()) {
                                    case 'material':
                                        $ret .= travelMaterial($subnode3);
                                        break;
                                    case 'material_ref':
                                        $ref = $ctx->xpath_eval('//material[@label="' . $subnode3->get_attribute('linkrefid') . '"]');
                                        if (count($ref->nodeset))
                                            $ret .= travelMaterial($ref->nodeset[0]);
                                        break;
                                    case 'response_label':
                                        $ansHtml = QTI_DISPLAY_ANSWER ? ('<div style="font-weight: bold; font-style: italic; color: white; background-color: green; width: 420px;">' . $correct_ans[$res_id][0] . '</div>') : '';
                                        
                                        
                                        if (isset($correct_num[$k])) {
                                            $columns = $correct_num[$k] * 2;
                                        } else if ($columns > 100) {
                                            $columns = 100;
                                        }
                                        
                                        $k++;
                                        
                                        if ($rows > 1) {
                                            $columns = 100;
                                            
                                            if (is_array($attachments[$item_id]['topic_files']))
                                                foreach ($attachments[$item_id]['topic_files'] as $key => $image)
                                                    $ret .= (gen_link($item_id, $image, $key) . '<br /><Br />');
                                            if (QTI_which == 'questionnaire') 
                                            {
                                                $ret .= sprintf('<br><textarea name="ans[%s][%s][%s]" rows="%d" cols="%d" ' . ($locking ? 'disabled' : '') . '>%s</textarea><br>%s', $item_id, $response_id, $subnode3->get_attribute('ident'), $rows, $columns, (QTI_DISPLAY_RESPONSE ? str_replace("&lt;br/&gt;", "\r\n", ($student_ans[$res_id][0])) : ''), $ansHtml);
                                            }else{
                                                                                            if($_SERVER['PHP_SELF']=='/teach/'.QTI_which.'/exam_correct_content.php' || $_SERVER['PHP_SELF']=='/learn/'.QTI_which.'/view_exemplar.php') {                                                    
                                                $ret .= sprintf('<br><div style="border-width: 1px; border-style: solid; border-color: #0db9bb; padding: 5px; min-height: 6em;">%s</div><br>%s',
                                                        (QTI_DISPLAY_RESPONSE ? preg_replace('@<a @i','<a target="_blank" ',nl2br(str_replace(' ', ' &nbsp; ', htmlspecialchars_decode($student_ans[$res_id][0])))) : ''),
                                                        $ansHtml);                                                
                                                } else {
                                                    if((basename($_SERVER['SCRIPT_FILENAME']) == 'homework_display.php') && ($_GET['preview']=='true')) {
                                                        if(!isset($co_editor_idx))
                                                            $co_editor_idx=1;
                                                        $ret .= sprintf('<br><textarea name="ans[%s][%s][%s]" id="content'.$co_editor_idx.'" rows="%d" cols="%d" ' . ($locking ? 'disabled' : '') . '>%s</textarea><br>%s', $item_id, $response_id, $subnode3->get_attribute('ident'), $rows, $columns, (QTI_DISPLAY_RESPONSE ? str_replace("&lt;br/&gt;", "\r\n", ($student_ans[$res_id][0])) : ''), $ansHtml);
                                                            
                                                        $co_editor_idx++;
                                                    } else {
                                                        if(!isset($co_editor_idx))
                                                            $co_editor_idx=1;
                                                        // 非作答階段
                                                        $arr_file = array(
                                                            'view_exemplar.php',
                                                            'view_result.php',
                                                            'exam_correct_content.php'
                                                        );
                                                        if (in_array(basename($_SERVER['PHP_SELF']), $arr_file)) {
                                                            $ret .= sprintf('<br><div class="view-result-textarea" style="min-height: 8em; word-break: break-all; border-color: darkgrey; border-width: 1px; border-style: solid; padding-left: 1em; padding-right: 1em;">%s</div><br>%s', html_entity_decode($student_ans[$res_id][0]), $ansHtml);                                                            
                                                        } else {
                                                            $ret .= sprintf('<br><textarea name="ans[%s][%s][%s]" id="content'.$co_editor_idx.'" rows="%d" cols="%d" ' . ($locking ? 'disabled' : '') . '>%s</textarea><br>%s', $item_id, $response_id, $subnode3->get_attribute('ident'), $rows, $columns, (QTI_DISPLAY_RESPONSE ? str_replace("&lt;br/&gt;", "\r\n", ($student_ans[$res_id][0])) : ''), $ansHtml);
                                                        }
                                                            
                                                        $co_editor_idx++;
                                                    }
                                                }
                                            }
                                        } else {
                                            
                                            $ret .= sprintf('<input name="ans[%s][%s][%s]" type="text" size="%d" value="%s" ' . ($locking ? 'disabled' : '') . '/>%s', $item_id, $res_id, $subnode3->get_attribute('ident'), $columns, (QTI_DISPLAY_RESPONSE ? $student_ans[$res_id][0] : ''), $ansHtml);
                                            // if (is_array($attachments[$item_id]['topic_files']))
                                                // foreach ($attachments[$item_id]['topic_files'] as $key => $image) $ret .= ('<br />' . gen_link($item_id, $image, $key));
                                        }
                                        break;
                                } // switch
                            } // foreach3
                            break;
                        case 'render_extension':
                            foreach ($subnode2->child_nodes() as $subnode3) {
                                if ($subnode3->tagname() == 'flow_label')
                                    $subnode3 = $subnode3->first_child();
                                switch ($subnode3->tagname()) {
                                    case 'material':
                                        $ret .= travelMaterial($subnode3);
                                        break;
                                    case 'material_ref':
                                        $ref = $ctx->xpath_eval('//material[@label="' . $subnode3->get_attribute('linkrefid') . '"]');
                                        if (count($ref->nodeset))
                                            $ret .= travelMaterial($ref->nodeset[0]);
                                        break;
                                    case 'response_label':
                                        $label = $subnode3->get_attribute('ident');
                                        if (QTI_DISPLAY_RESPONSE && basename($_SERVER['SCRIPT_NAME']) != 'item_fetch.php') {
                                            // $ret .= "<p>//item_result[@ident_ref='$item_id']/response[@ident_ref='$res_id']/response_value[starts-with(., 'file://{$label}/')]</p>";
                                            $re = $ctx->xpath_eval("//item_result[@ident_ref='$item_id']/response[@ident_ref='$res_id']/response_value[starts-with(., 'file://{$label}/')]");
                                            if (count($re->nodeset))
                                                $ret .= listExtensionFiles($label, basename($re->nodeset[0]->get_content()));
                                        } else
                                            foreach ($subnode3->child_nodes() as $subnode4)
                                                if ($subnode4->tagname() == 'material')
                                                    $ret .= '<input name="ans[' . $item_id . '][' . $res_id . '][' . $label . ']" type="hidden" value=""/>' . str_replace(array(
                                                        '<%EXAM_ID%>',
                                                        '<%COURSE_ID%>',
                                                        '<%ITEM_ID%>'
                                                    ), array(
                                                        'exam_audio',
                                                        $sysSession->course_id,
                                                        $item_id
                                                    ), travelMaterial($subnode4));
                                        break;
                                } // switch
                            } // foreach3
                            break;
                    } // switch
                } //foreach2
                break;
        } //switch
    } //foreach
     if (is_array($attachments[$item_id]['topic_files']))
        foreach($attachments[$item_id]['topic_files'] as $key => $image) $ret .= ('<br />' . gen_link($item_id, $image, $key));
    return $ret . "\t\t\t\t\t</td>\n\t\t\t\t\t<td>";
}

/**
 * 產生配合題
 */
function generatePAIR($item, $node)
{
    global $ctx, $attachments, $sysSession, $itemSerial, $MSG, $item_types, $item_type_names, $ctrl_paging;
    
    $item_id     = $item->get_attribute('ident');
    $result      = $ctx->xpath_eval('./*[substring-before(name(), "_") = "response"]', $node);
    $response_id = $result->nodeset[0]->get_attribute('ident');
    
    
    $p = $node->parent_node();
    if ($p->tagname() == 'presentation') { // 取得配分 (在尚未遞迴的第一階時)
        $result  = $ctx->xpath_eval('./resprocessing/outcomes/decvar[@defaultval]', $item);
        $outcome = count($result->nodeset) ? floatval($result->nodeset[0]->get_attribute('defaultval')) : 0;
        $ret     = '';
        if ($item_type_names[$item_types[$item_id]])
            $ret .= '<b>' . $item_type_names[$item_types[$item_id]] . '</b><br>';
        $ret .= sprintf('<b><div>%s</div><div>[%.2f]</div></b></td><td valign="top" nowrap>', $MSG['score_assigned'][$sysSession->lang], $outcome);
        
        $student_ans = array();
        if (QTI_DISPLAY_RESPONSE) {
            $locking = false;
            $re      = $ctx->xpath_eval("//item_result[@ident_ref='$item_id']/response[@ident_ref='$response_id']/response_value");
            if (count($re->nodeset))
                foreach ($re->nodeset as $piece) {
                    $student_ans[$response_id][] = $piece->get_content();
                    if ($piece->get_content() != '')
                        $locking = true;
                }
            if ($ctrl_paging != 'can_return')
                $locking = false;
        }
        $correct_ans = array();
        if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER || (QTI_DISPLAY_RESPONSE && $_SERVER['SCRIPT_URL']=='/learn/exam/view_result.php')) {
            $as = $ctx->xpath_eval('./resprocessing/respcondition[1]/varsubset', $item);
            if ((QTI_DISPLAY_ANSWER|| QTI_DISPLAY_RESPONSE) && count($as->nodeset))
                foreach ($as->nodeset as $piece)
                    $correct_ans[$piece->get_attribute('respident')][] = $piece->get_content();
            $answerResult = (!empty($student_ans) && $correct_ans === $student_ans) ? true : false;
            if (QTI_DISPLAY_RESPONSE) {
                $score_node = $ctx->xpath_eval("//item_result[@ident_ref='$item_id']/outcomes/score/score_value");
                if (is_array($score_node->nodeset) && count($score_node->nodeset)) {
                    if ($score_node->nodeset[0]->has_child_nodes()) {
                        $corrected = true;
                        $outcome   = floatval($score_node->nodeset[0]->get_content());
                    }
                }
                
                $ret .= sprintf('<img src="/theme/%s/teach/%s" align="top" border="0" /><br>%s<input type="text" name="item_scores[%s]" size="3" value="%.2f" class="cssInput"></td>' . "\n\t\t\t\t\t<td align='left'>%d. ", $sysSession->theme, ($answerResult ? 'icon_currect.gif' : 'icon_wrong.gif'), $MSG['earn'][$sysSession->lang], $item_id, ($answerResult || $corrected ? $outcome : 0), $itemSerial++);
            } else
                $ret .= sprintf("&nbsp;</td>\n\t\t\t\t\t<td align='left'>%d. ", $itemSerial++);
        } else
            $ret .= sprintf("&nbsp;</td>\n\t\t\t\t\t<td align='left'>%d. ", $itemSerial++);
    } else
        $ret = '';
    
    foreach ($node->child_nodes() as $subnode) {
        switch ($subnode->tagname()) {
            case 'flow':
                $ret .= generateFILL($item_id, $subnode);
                break;
            case 'material':
                $ret .= travelMaterial($subnode);
                break;
            case 'material_ref':
                $ref = $ctx->xpath_eval('//material[@label="' . $subnode->get_attribute('linkrefid') . '"]');
                if (count($ref->nodeset))
                    $ret .= travelMaterial($ref->nodeset[0]);
                break;
            case 'response_grp':
                if (is_array($attachments[$item_id]['topic_files']))
                    foreach ($attachments[$item_id]['topic_files'] as $key => $image)
                        $ret .= ('<br />' . gen_link($item_id, $image, $key));
                
                $res_id = $subnode->get_attribute('ident');
                foreach ($subnode->child_nodes() as $subnode2) {
                    switch ($subnode2->tagname()) {
                        case 'material':
                            $ret .= travelMaterial($subnode2);
                            break;
                        case 'material_ref':
                            $ref = $ctx->xpath_eval('//material[@label="' . $subnode2->get_attribute('linkrefid') . '"]');
                            if (count($ref->nodeset))
                                $ret .= travelMaterial($ref->nodeset[0]);
                            break;
                        case 'render_extension':
                            $x                    = $subnode2->first_child();
                            $response_label_count = 0;
                            if ($x->tagname() == 'ims_render_object')
                                $subnode2 = $x;
                            $pairs = array(
                                '<ol type="a">',
                                '<ol type="1">'
                            );
                            foreach ($subnode2->child_nodes() as $subnode3) {
                                if ($subnode3->tagname() == 'flow_label')
                                    $subnode3 = $subnode3->first_child();
                                switch ($subnode3->tagname()) {
                                    case 'material':
                                        $ret .= travelMaterial($subnode3);
                                        break;
                                    case 'material_ref':
                                        $ref = $ctx->xpath_eval('//material[@label="' . $subnode3->get_attribute('linkrefid') . '"]');
                                        if (count($ref->nodeset))
                                            $ret .= travelMaterial($ref->nodeset[0]);
                                        break;
                                    case 'response_label':
                                        if ($subnode3->get_attribute('match_max')) {
                                            $pairs[0] .= sprintf('<li><select size="1" name="ans[%s][%s][%s]" ' . ($locking ? 'disabled' : '') . '><option></option>', $item_id, $response_id, $response_label_count);
                                            foreach (explode(',', $subnode3->get_attribute('match_group')) as $v)
                                                $pairs[0] .= sprintf('<option value="%d"%s%s>%s%d%s</option>', $v, ((QTI_DISPLAY_ANSWER && $v == $correct_ans[$response_id][$response_label_count]) ? ' style="color: white; background-color: green" data-status="c"' : ''), ((QTI_DISPLAY_RESPONSE && $v == $student_ans[$response_id][$response_label_count]) ? ' selected' : ''),((QTI_DISPLAY_ANSWER && $v == $correct_ans[$response_id][$response_label_count]) ? '[' : ''), $v, ((QTI_DISPLAY_ANSWER && $v == $correct_ans[$response_id][$response_label_count]) ? ']' : ''));
                                            $pairs[0] .= '</select>' . (travelResponselabel($subnode3)) . gen_link($item_id, @array_shift($attachments[$item_id]['render1_choice_files'])) . '</li>';
                                        } else {
                                            $pairs[1] .= '<li>' . (travelResponselabel($subnode3)) . gen_link($item_id, @array_shift($attachments[$item_id]['render2_choice_files'])) . '</li>';
                                        }
                                        $response_label_count++;
                                        break;
                                } // switch
                            } // foreach3
                            $ret .= '<blockquote style="border-left: initial; padding-left: 0;"><table border="1" bordercolor="#D0D0D0" class="font01" style="border: 1px #D0D0D0 solid; word-break: break-all;"><tr><td>' . $pairs[0] . '</ol></td><td>' . $pairs[1] . '</ol></td></tr></table></blockquote>';
                            break;
                    } // switch
                } //foreach2
                break;
        } //switch
    } //foreach
    return $ret . "\t\t\t\t\t</td>\n\t\t\t\t\t<td>";
}

/**
 * 由 XML 結構，判斷題目類型
 */
function detect_item_type($node)
{
    global $ctx, $qti_item_types;
    
    foreach ($qti_item_types as $index => $kind) {
        $ret = $ctx->xpath_eval($kind, $node);
        if (count($ret->nodeset))
            return $index;
    }
}

/**
 * 轉換 <item>
 */
function transform_ITEM($node, $appQtiWhich = '')
{
    global $ctx, $attach_path, $attach_uri, $MSG, $sysSession, $sysConn;
    static $rowClass = 0;
    
    if ($node->get_attribute('visable') == 'invisible')
        return;
    
    $result = $ctx->xpath_eval('./presentation/flow', $node);
    if (count($result->nodeset) == 0)
        return 'unknown item type';
        
    $kind = detect_item_type($node); 

    $item_type = $kind;

    if ($kind == 2) {
        
        $tmp = $ctx->xpath_eval('.//response_str/render_fib[@prompt="Box"]', $node);
        if (count($tmp->nodeset)!=0) {
            $item_type = 5;    
        }
    }
    
    $ret = sprintf("\t\t\t\t<tr class=\"bg0%d font01 item \" item-type=\"$item_type\" >\n\t\t\t\t\t<td valign=\"top\" align=\"left\" nowrap>", $rowClass++ + 3);
    $rowClass %= 2;
    
    
    if (!in_array($kind, array(
        1,
        2,
        3,
        4,
        9
    )))
        return '<h2 style="color: red">Unsupported item type : ' . $node->get_attribute('ident') . '</h2>';
    switch ($kind) {
        case 1: // 選擇題 (是非、單選、複選)
            $ret .= generateCHOICE($node, $result->nodeset[0]);
            break;
        case 2: // 填充題 (字串)
        case 3: // 填充題 (數值)
        case 9:
            $ret .= generateFILL($node, $result->nodeset[0]);
            break;
        case 4: // 配合題
            $ret .= generatePAIR($node, $result->nodeset[0]);
            break;
        default:
            $ret .= '&nbsp;</td><td colspan="2"><h2 style="color: red">Unsupported item type : ' . $node->get_attribute('ident') . '</h2></td><td>';
            break;
    }
    if (QTI_DISPLAY_ANSWER) {
        $xx = $ctx->xpath_eval('./itemfeedback/solution', $node);
        if (is_array($xx->nodeset) && count($xx->nodeset)) {
            $x = $ctx->xpath_eval('./solutionmaterial/material/mattext', $xx->nodeset[0]);
            $x = $x->nodeset[0];
            if ($x->get_content())
                $ret .= $MSG['detail_answer'][$sysSession->lang] . '<br>' . $x->get_content();
            $child_nodes = $xx->nodeset[0]->child_nodes();
            foreach ($child_nodes as $value) {
                if ($value->tagname == 'refurl') {
                    if (method_exists($value, 'get_content') && ($value = $value->get_content()))
                        if ($value != 'http://') {
                            $urls1 = preg_split('/\s+/', str_replace('%', '%%', $value), -1, PREG_SPLIT_NO_EMPTY);
                            $urls2 = preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY);
                            $urlc  = count($urls1);
                            $ret .= '<br>' . $MSG['ref_url'][$sysSession->lang] . vsprintf(vsprintf(str_repeat('<br><a href="%%s" target="_blank">%s</a>', $urlc), $urls1), $urls2);
                        }
                    break;
                }
            }
        }
        
        $ident = $node->get_attribute('ident');
            $qtiWhich = ($appQtiWhich !== '')? $appQtiWhich : QTI_which;
            list($at) = dbGetStSr('WM_qti_' . $qtiWhich . '_item', 'attach', "ident='$ident'", ADODB_FETCH_NUM);
        $attach = ereg('^a:[0-9]+:{s:', $at) ? unserialize($at) : array();
        if ($attach['ans_files']) {
            $ret .= '<br>' . $MSG['ans_files'][$sysSession->lang];
            foreach ($attach['ans_files'] as $key => $value) {
                $entry = sprintf($attach_path . '%s/%s', $ident, $value);
                if (file_exists($entry))
                    $ret .= sprintf('<br><img src="/theme/%s/learn/file.gif" /><a href="%s%s/%s" target="_blank" class="cssAnchor">%s</a><br />', $sysSession->theme, $attach_uri, $ident, rawurlencode($value), $key);
            }
        }
    }
    
    $rsCourse = new course();
    $ret = $rsCourse->transform_LATEX($ret);
    
    return $ret . "</td>\n\t\t\t\t</tr>\n";
}

/**
 * 轉換 <section>
 */
function transform_SECTION($node, $appQtiWhich= '')
{
    global $ctx;
    
    if ($node->get_attribute('visable') == 'invisible')
        return;
    $ret .= drawTable(Table_header, 'section');
    
    $result = $ctx->xpath_eval('./presentation_material', $node); // 大題的話先output 大題題目
    if (count($result->nodeset))
        echo '<tr class="cssTrHelp" style="background-color:rgba(13, 185, 187, 1);color:#000000;font-weight:normal"><td colspan="4">' . travelResponselabel($result->nodeset[0]) . '</td></tr>';
    
    foreach ($node->child_nodes() as $subnode) {
        switch ($subnode->tagname()) {
            // case 'presentation_material':
            //     $ret .= '<tr class="bg02"><td colspan="4">' . travelResponselabel($subnode) . '</td></tr>';
            //    break;
            case 'itemref':
                $result = $ctx->xpath_eval('//item[@ident="' . $subnode->get_attribute('linkrefid') . '"]');
                if (count($result->nodeset))
                    $ret .= transform_ITEM($result->nodeset[0], $appQtiWhich);
                break;
            case 'item':
                $ret .= transform_ITEM($subnode, $appQtiWhich);
                break;
            case 'sectionref':
                $result = $ctx->xpath_eval('//section[@ident="' . $subnode->get_attribute('linkrefid') . '"]');
                if (count($result->nodeset))
                    $ret .= transform_SECTION($result->nodeset[0]);
                break;
            case 'section':
                $ret .= transform_SECTION($subnode);
                break;
        }
    }
    
    $ret .= drawTable(Table_footer, 'section');
    return $ret;
}

/**
 * 轉換 <assessment>
 */
function transform_ASSESSMENT($node)
{
    global $ctx;
    
    if ($node->get_attribute('visable') == 'invisible')
        return;
    $ret = drawTable(Table_header, 'assessment');
    
    foreach ($node->child_nodes() as $subnode) {
        switch ($subnode->tagname()) {
            case 'presentation_material':
                $ret .= '<tr class="bg02"><td colspan="4">' . travelResponselabel($subnode) . '</td></tr>';
                break;
            case 'sectionref':
                $result = $ctx->xpath_eval('//section[@ident="' . $subnode->get_attribute('linkrefid') . '"]');
                if (count($result->nodeset))
                    $ret .= transform_SECTION($result->nodeset[0]);
                break;
            case 'section':
                $ret .= transform_SECTION($subnode);
                break;
        }
    }
    
    $ret .= drawTable(Table_footer, 'assessment');
    return $ret;
}

/**
 * 轉換整份 ASI
 */
function travelQuestestinterop($node, $appQtiWhich = '')
{
    global $exam_id, $time_id, $ticket, $sectionSerial, $itemSerial;
    
    // showXHTML_css('include', '/theme/default/teach/wm.css');
    echo drawTable(Table_header, 'questestinterop');
    $qticommentOnce = TRUE;
    $objectOnce     = TRUE;
    $assessmentOnce = TRUE;
    
    foreach ($node->child_nodes() as $cur_node) {
        if (!method_exists($cur_node, 'tagname'))
            continue;
        switch ($cur_node->tagname()) {
            case 'assessment':
                if ($assessmentOnce) {
                    $sectionSerial++;
                    $itemSave   = $itemSerial;
                    $itemSerial = 1;
                    echo transform_ASSESSMENT($cur_node);
                    $sectionSerial--;
                    $itemSerial     = $itemSave;
                    $assessmentOnce = FALSE;
                } else
                    echo 'Warning: "&lt;assessment&gt;" appears more than once.<br>';
                break;
            case 'section':
                $sectionSerial++;
                $itemSave   = $itemSerial;
                $itemSerial = 1;
                echo transform_SECTION($cur_node);
                $sectionSerial--;
                $itemSerial = $itemSave;
                break;
            case 'item':
                echo transform_ITEM($cur_node, $appQtiWhich);
                break;
        }
    }
    //if (ereg('^/learn/', $_SERVER['PHP_SELF']))
    echo '<tr style="background-color: white;"><td colspan="4" align="center"><!--BUTTON_LINE--></td></tr>', "\n";
    //else
    //    echo "\n";
    
    echo drawTable(Table_footer, 'questestinterop');
}

/**
 * 去掉 default namespace 重新產生一份新的 dom
 */
function parseQuestestinterop($xmlstr, $appQtiWhich = '')
{
    global $parserDom, $ctx;
    if (!$parserDom = domxml_open_mem(setEncoding(preg_replace('/\sxmlns="[^"]*"/', '', $xmlstr))))
        return false;
    
    $p_dom = $parserDom->document_element();
    
    rm_whitespace($p_dom);
    $ctx = xpath_new_context($parserDom);
    $ctx->xpath_register_ns('wm', 'www.sun.net.tw/WisdomMaster');
    travelQuestestinterop($p_dom, $appQtiWhich);
}

// 處理 HTML 產生後的選項隨機排列
function cramble_choices($doc)
{
    if (preg_match_all('!<ol type="a">\s*((<li>.*</li>)+)\s*</ol>!sU', $doc, $regs)) {
        $replaces = array();
        foreach ($regs[1] as $choice) {
            preg_match_all('!<li>.*</li>!sU', $choice, $items);
            if (count($items[0]) > 2)
                shuffle($items[0]);
            $replaces[] = '<ol type="a">' . implode('', $items[0]) . '</ol>';
        }
        return str_replace($regs[0], $replaces, $doc);
    } else
        return $doc;
}

/**
 ***********************************  主程式 開始 *****************************************
 */

if (!defined('QTI_DISPLAY_ANSWER'))
    define('QTI_DISPLAY_ANSWER', false); // 是否顯示答案
if (!defined('QTI_DISPLAY_OUTCOME'))
    define('QTI_DISPLAY_OUTCOME', false); // 是否顯示得分
if (!defined('QTI_DISPLAY_RESPONSE'))
    define('QTI_DISPLAY_RESPONSE', false); // 是否顯示作答答案

if (basename($_SERVER['PHP_SELF']) == 'QTI_transformer.php') {
    $filename = $_SERVER['argv'][0];
    
    if (!file_exists($filename)) {
        wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 1, 'auto', $_SERVER['PHP_SELF'], 'File not found:' . $filename);
        die('File not found.');
    }
    $xmlDoc = domxml_open_mem(preg_replace('/\sxmlns="[^"]*"/', '', file_get_contents($filename)));
    rm_whitespace($xmlDoc->document_element());
    $ctx = xpath_new_context($xmlDoc);
    $ctx->xpath_register_ns('wm', 'www.sun.net.tw/WisdomMaster');
    travelQuestestinterop($xmlDoc->document_element());
}