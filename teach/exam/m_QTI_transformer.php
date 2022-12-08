<?php
/**
 * 第二版 QTI parser   sence 2004-06-02	by Wiseguy Liang
 * 建立日期：2004/06/02
 * @author  Wiseguy Liang
 * @version $Id: QTI_transformer.php,v 1.4 2009-09-23 01:55:38 edi Exp $
 * @copyright 2003 SUNNET
 **/
if (!defined('XMLAPI') || !XMLAPI) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
}
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');

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

if (!isset($sysSession))
    $sysSession->theme = 'default';
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
    1 => $MSG['item_type1'][$sysSession->lang], // 是非
    2 => $MSG['item_type2'][$sysSession->lang], // 單選
    3 => $MSG['item_type3'][$sysSession->lang], // 多選
    4 => $MSG['item_type4'][$sysSession->lang], // 填充
    5 => $MSG['item_type5'][$sysSession->lang], // 簡答/申論
    6 => $MSG['item_type6'][$sysSession->lang], // 配合
    7 => $MSG['item_type7'][$sysSession->lang] // 錄音/附檔
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
        return sprintf('<img src="%s%s/%s" align="top" alt="%s" />', $save_uri, $id, $fname, $fname);
    elseif (is_avi($fname))
        return sprintf('<embed src="%s%s/%s" align="absmiddle" type="video/*" volume="0" mime-types="mime.types" %s autostart="false" title="%s">', $save_uri, $id, $fname, (eregi('\.(rm|ram)$', $fname) ? 'WIDTH=352 HEIGHT=276 NOJAVA=true CONTROLS="ImageWindow,ControlPanel"' : ''), $fname);
    elseif (is_snd($fname)) {
        /*********# 027189	begin 2012/10/08 mars chrome 支援播放mp3 加上判斷 ********/
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
        if (strpos($agent, 'Chrome') !== false && strrchr($fname, '.') === '.mp3') {
            /*#48230 [IE][教室/評量區/測驗/進行測驗] 題目附檔若是mp3，會出現叉燒包：修改播放器程式碼寫法*/
            return sprintf('<object width="400" height="27"><param name="quality" value="best"><param name="flashvars" value="audioUrl=%s%s/%s"><param name="movie" value="http://www.google.com/reader/ui/3523697345-audio-player.swf"><embed type="application/x-shockwave-flash" width="400" height="27" src="http://www.google.com/reader/ui/3523697345-audio-player.swf" flashvars="audioUrl=%s%s/%s" quality="best" title="%s"></object>', $save_uri, $id, $fname, $save_uri, $id, $fname, $fname);
            /*#48462 safari[教室與教師辦公室]若遇到ios safari瀏覽器改用quicktime播放*/
        } else if (($browser === 'sf' && $win === false) || ($browser === 'sf' && $win === true && strrchr($fname, '.') === '.mp3')) {
            return sprintf('<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab"
        width="200" height="16">
            <param name="src" value="%s%s/%s" /><param name="autoplay" value="true" />
            <param name="pluginspage" value="http://www.apple.com/quicktime/download/" />
            <param name="controller" value="true" />
            <!--[if !IE]> <-->
                <object data="%s%s/%s" width="200" height="16" type="video/quicktime">
                    <param name="pluginurl" value="http://www.apple.com/quicktime/download/" />
                    <param name="controller" value="true" />
                    <param name="autoplay" value="false" />
                </object>
            <!--><![endif]-->
            </object>', $save_uri, $id, $fname, $save_uri, $id, $fname);
        } else {
            
            /*#483350 Chrome[教室/評量區/QTI/作業] 題目附檔若是wma，chrome無法播放。：修改寫法*/
            return sprintf('<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="400" height="64" > <param name="invokeURLs" value="0" > <param name="autostart" value="0" /> <param name="url" value="%s%s/%s"? id="abbc"/>  <embed src="%s%s/%s" autostart="0" type="application/x-mplayer2" width="400" height="64"></embed>  </object>', $save_uri, $id, $fname, $save_uri, $id, $fname);
        }
    } elseif (strrchr($fname, '.') == '.swf') /*			return sprintf('
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
                showXHTML_tabFrame_B($ary, 1, 'responseForm', '', 'method="POST" enctype="multipart/form-data" action="save_answer.php" target="submitTarget" style="display: inline"', false, false);
                echo <<< EOB
			<input type="hidden" name="exam_id" value="$exam_id">
			<input type="hidden" name="time_id" value="$time_id">
			<input type="hidden" name="ticket" value="$ticket">
			<!-- questestinterop begin -->
EOB;
                showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="766" class="cssTable"');
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
                    echo '<span style="background-color: green"><input type=radio name="demo_radio_1"/></span>' . $MSG['demo_cor_answer'][$sysSession->lang];
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
 * 新建drawTable_test
 * 以 table 顯示各 container (questestinterop, objectbank, assessment, section)
 */
function drawTable_new($isBegin, $blockType)
{
    global $exam_id, $time_id, $ticket, $sectionSerial, $itemSerial, $MSG, $sysSession;
    ob_start();
    if ($isBegin)
        switch ($blockType) {
            case 'questestinterop':
                $ary                  = array(
                    array(
                        $MSG['exam_context'][$sysSession->lang]
                    )
                );
                // showXHTML_tabFrame_B($ary, 1, 'responseForm', '', 'method="POST" enctype="multipart/form-data" action="save_answer.php" target="submitTarget" style="display: inline"',false,false);
                // showXHTML_tabFrame_B($ary, 1, 'responseForm', '', 'method="POST" enctype="multipart/form-data" action="save_answer.php" target="submitTarget" style="display: inline"',false,false,'style="display:none; background-color: white;"');
                $display_css['table'] = 'align="center"';
                $display_css['tab']   = 'style="display:none;"';
                showXHTML_tabFrame_B($ary, 1, 'responseForm', '', 'method="POST" enctype="multipart/form-data" action="save_answer.php" target="submitTarget" style="display: inline"', false, false, $display_css);
                echo <<< EOB
			<input type="hidden" name="exam_id" value="$exam_id">
			<input type="hidden" name="time_id" value="$time_id">
			<input type="hidden" name="ticket" value="$ticket">
			<!-- questestinterop begin test -->
EOB;
                //showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="760" class="cssTable"');
                if ($blockType == 'section') {
                    showXHTML_table_B('class="" style="width: 886px; background-color: white;"');
                } else {
                    showXHTML_table_B('class="table table-hover" style="width: 886px; background-color: white;"');
                }
                // showXHTML_table_B('class="" style="width: 886px; background-color: white;"');
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
                    echo '<span style="background-color: green"><input type=radio name="demo_radio_1"/></span>' . $MSG['demo_cor_answer'][$sysSession->lang];
                    echo '<input type=radio name="demo_radio_1" checked/>' . $MSG['demo_stud_answer'][$sysSession->lang];
                    showXHTML_td_E('');
                    showXHTML_tr_E('');
                }
                break;
            default:
                showXHTML_tr_B();
                showXHTML_td_B('colspan="5"');
                // showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="100%" class="cssTable"');
                showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="100%" class="table table-hover" ');
                echo '<!-- ', $blockType, ' begin -->';
                break;
        } else
        switch ($blockType) {
            case 'questestinterop':
                showXHTML_table_E();
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
        switch ($subnode->tagname()) {
            case 'mattext':
                $r = ($subnode->get_attribute('texttype') == 'text/rtf') ? rtf2txt((($html_replace) ? htmlspecialchars($subnode->get_content()) : $subnode->get_content())) : (($html_replace) ? htmlspecialchars($subnode->get_content()) : $subnode->get_content());
                if (in_array($ident, array(
                    'T',
                    'F'
                ))) {
                    if ($r == 'Agree')
                        $r = '<img src="/theme/default/teach/right.gif" align="absmiddle" />';
                    elseif ($r == 'Disagree')
                        $r = '<img src="/theme/default/teach/wrong.gif" align="absmiddle" />';
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
function generateCHOICE($item, $node, $node_for_ans)
{
    global $ctx, $sysSession, $attachments, $itemSerial, $MSG, $item_types, $item_type_names, $ctrl_paging, $simpXmlObject;
    
    $item_id = $item->get_attribute('ident');
    
    $result      = $ctx->xpath_eval('./*[substring-before(name(), "_") = "response"]', $node);
    $response_id = $result->nodeset[0]->get_attribute('ident');
    
    // 答題記錄
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
    
    // item 用
    if (isset($simpXmlObject->item)) {
        for ($i = 0; $i < count($simpXmlObject->item); $i++) {
            if ($simpXmlObject->item[$i]->attributes()->ident == $item_id) {
                switch ($simpXmlObject->item[$i]->presentation->flow->response_lid->attributes()->rcardinality) {
                    case 'Single':
                        $data        = $simpXmlObject->item[$i];
                        $data_result = $simpXmlObject->item_result[$i];
                        $ret         = generateSingleChoice($data, $data_result, $itemSerial++, $locking, $item, $node_for_ans);
                        break;
                    
                    case 'Multiple':
                        $data        = $simpXmlObject->item[$i];
                        $data_result = $simpXmlObject->item_result[$i];
                        $ret         = generateMultiChoice($data, $data_result, $itemSerial++, $locking, $node_for_ans);
                        break;
                    
                    default:
                        echo "error, the choice is not in the case.";
                        break;
                }
            }
        }
    }
    
    
    //section 用
    if (isset($simpXmlObject->section)) {
        for ($s = 0; $s < count($simpXmlObject->section); $s++) {
            for ($i = 0; $i < count($simpXmlObject->section[$s]->item); $i++) {
                if ((string) $simpXmlObject->section[$s]->item[$i]->attributes()->ident == $item_id) {
                    switch ($simpXmlObject->section[$s]->item[$i]->presentation->flow->response_lid->attributes()->rcardinality) {
                        case 'Single':
                            $data_result = $simpXmlObject->section[$s]->item_result[$i];
                            $data        = $simpXmlObject->section[$s]->item[$i];
                            $ret         = generateSingleChoice($data, $data_result, $itemSerial++, $locking, $item, $node_for_ans);
                            break;
                        
                        case 'Multiple':
                            $data_result = $simpXmlObject->section[$s]->item_result[$i];
                            $data        = $simpXmlObject->section[$s]->item[$i];
                            $ret         = generateMultiChoice($data, $data_result, $itemSerial++, $locking, $node_for_ans);
                            break;
                        
                        default:
                            echo "error, the choice is not in the case.";
                            break;
                    }
                }
            }
        }
    }
    
    return $ret;
}

/*
 * 新版 配合題
 */

function generateItemPAIR($item_obj, $item_obj_result, $itemSerial, $locking, $node_for_ans)
{
    global $attachments, $ctx, $item_type_names;
    
    $ret = "<td width='5%'><div style='margin-top:8px;'><strong><h4>" . $itemSerial . ".</h4></strong></div></td><td colspan=\"2\" style=\"padding-right: 86px;\">";
    
    $item_title = $item_obj->presentation->flow->material->mattext;
    $item_cat   = $item_obj->presentation->flow->response_grp->attributes()->rcardinality;
    if ($item_cat == 'Multiple') {
        $item_cat_msg = $item_type_names[6]; // 配合題
    }
    
    $item_score = (float) $item_obj->resprocessing->outcomes->decvar->attributes()->defaultval;
    
    $ret_orign_title = array(
        '%item_title%',
        '%item_cat%',
        '%item_score%',
        '%score_unit%'
    );
    
    $ret_resplace_title = array(
        $item_title,
        $item_cat_msg,
        $item_score,
        $MSG['score_unit'][$sysSession->lang]
    );
    
    $ret .= file_get_contents(sysDocumentRoot . '/mooc/smarty/templates/learn/exam/exam_title_item_pair.tpl');
    $ret = str_replace($ret_orign_title, $ret_resplace_title, $ret);
    
    //topic 附圖
    if (is_array($attachments[$item_id]['topic_files']))
        foreach ($attachments[$item_id]['topic_files'] as $key => $image)
            $ret .= ('<br />' . gen_link($item_id, $image, $key));
    
    // 顯示單選題 正解框框
    if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER) {
        $the_ans_value = array();
        for ($q = 0; $q < sizeof($item_obj_result->response->response_form->correct_response); $q++) {
            $the_ans_value[$q] = (string) $item_obj_result->response->response_form->correct_response[$q];
        }
    }
    
    $count_item_num = sizeof($item_obj->presentation->flow->response_grp->render_extension->ims_render_object->response_label);
    $count_item_obj = $item_obj->presentation->flow->response_grp->render_extension->ims_render_object;
    
    $item_ident = array();
    $item_value = array();
    $item_name  = array();
    
    $item_part_1        = $count_item_num / 2;
    $item_part_2        = $count_item_num;
    $item_part_2_offset = 0 + $item_part_1;
    
    $disabled_label = ($locking) ? 'disabled' : '';
    
    for ($i = 0; $i < $count_item_num; $i++) {
        
        if (isset($item_obj->presentation->flow->response_grp->render_extension->ims_render_object->response_label[$i]->attributes()->match_group)) {
            $item_value[$i]  = (string) $count_item_obj->response_label[$i]->attributes()->match_group;
            $item_show[$i]   = (string) $count_item_obj->response_label[$i]->material->mattext;
            $item_name_1[$i] = $item_obj->attributes()->ident;
            $item_name_2[$i] = (string) $item_obj->presentation->flow->response_grp->attributes()->ident;
            $item_name_3[$i] = (string) $i;
        } else {
            $item_value[$i]  = (string) $count_item_obj->response_label[$i]->attributes()->match_group;
            $item_show[$i]   = (string) $count_item_obj->response_label[$i]->material->mattext;
            $item_name_1[$i] = (string) $item_obj->attributes()->ident;
            $item_name_2[$i] = (string) $item_obj->presentation->flow->response_grp->attributes()->ident;
            $item_name_3[$i] = (string) $i;
        }
    }
    
    $item_value_response = array();
    for ($q = 0; $q < $item_part_1; $q++) {
        $item_value_response[$q] = (string) $item_obj_result->response->response_value[$q];
        
    }
    
    $ret .= "<div><table class=\"table table-bordered\"><tr><td><ol type=\"a\">";
    
    // part 1 logic start
    for ($s = 0; $s < $item_part_1; $s++) {
        
        $ret .= "<li><select name=\"ans[{$item_name_1[$s]}][{$item_name_2[$s]}][{$item_name_3[$s]}]\" {$disabled_label}>";
        
        $item_value_explode = explode(",", $item_value[$s]);
        
        $ret .= "<option></option>";
        // 被選取過的
        for ($k = 0; $k < sizeof($item_value_explode); $k++) {
            if ($item_value_explode[$k] == $item_value_response[$s]) {
                $select = "selected";
            } else {
                $select = "";
            }
            
            if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER) {
                if ($item_value_explode[$k] == $the_ans_value[$s]) {
                    $option_style = 'style="color: white; background-color: green;"';
                } else {
                    $option_style = "";
                }
            }
            
            $ret .= "<option value=\"{$item_value_explode[$k]}\" {$select} {$option_style}>{$item_value_explode[$k]}. {$item_show[($k+$item_part_2_offset)]}</option>";
        }
        
        
        $ret .= "</select>{$item_show[$s]}" . gen_link($item_id, @array_shift($attachments[$item_id]['render1_choice_files'])) . "</li>";
    }
    // part 1 logic end
    
    $ret .= "</ol></td><td><ol type=\"1\">";
    for ($i = $item_part_2_offset; $i < $item_part_2; $i++) {
        $ret .= "<li>" . $item_show[$i] . " " . gen_link($item_id, @array_shift($attachments[$item_id]['render2_choice_files'])) . "</li>";
    }
    
    $ret .= "</ol></td></tr></table></div>";
    $ret .= "<div><br />" . show_ans($node_for_ans) . "</div>";
    $ret .= "</td>";
    
    // 顯示答案對錯logo
    if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER) {
        $is_correct = false;
        
        // 學生作答
        
        // 正確解答 array
        $the_ans = array();
        for ($z = 0; $z < count($item_obj_result->response->response_form->correct_response); $z++) {
            $push_ans_value = (string) $item_obj_result->response->response_form->correct_response[$z];
            array_push($the_ans, $push_ans_value);
        }
        
        // 紀錄學生之前做的題目 array
        $student_ans = array();
        for ($j = 0; $j < count($item_obj_result->response->response_value); $j++) {
            $push_value = (string) $item_obj_result->response->response_value[$j];
            array_push($student_ans, $push_value);
        }
        
        // 判斷這台是否答對
        foreach ($the_ans as $v) {
            if (in_array($v, $student_ans) == false) {
                $is_correct = false;
                break;
            } else {
                $is_correct = true;
            }
        }
        
        $ret .= "<td style='vertical-align: middle;'><div style='position: relative;'>";
        if ($is_correct == true) {
            $ret .= "<div style='text-align: right;'><img src='/theme/default/learn_mooc/exam_sign_check.png'></div>";
        } else {
            $ret .= "<div style='text-align: right;'><img src='/theme/default/learn_mooc/exam_sign_error.png'></div>";
        }
        $ret .= "</div></td>";
    }
    
    return $ret;
}

/**
 *  新版 是非,單選題
 */
function generateSingleChoice($item_obj, $item_obj_result, $itemSerial, $locking, $item, $node_for_ans)
{
    global $attachments, $ctx, $item_type_names;
    
    $item_id   = (string) $item_obj->attributes()->ident;
    $ret       = "<td width='5%'><div style='margin-top:8px;'><strong><h4>" . $itemSerial . ".</h4></strong></div></td><td colspan=\"2\" style=\"padding-right: 86px;\">";
    $ret_orign = array(
        '%item_title%',
        '%item_cat%',
        '%item_score%',
        '%score_unit%'
    );
    
    $item_title = $item_obj->presentation->flow->material->mattext;
    $item_cat   = $item_obj->presentation->flow->response_lid->attributes()->rcardinality;
    
    // 單選題 或是 是非題 判斷
    $is_single = ($item_obj->presentation->flow->response_lid->render_choice->attributes()->shuffle == 'Yes') ? true : false;
    
    if ($item_cat == 'Single') {
        if ($is_single) {
            $item_cat_msg = $item_type_names[2]; // 單選題
        } else {
            $item_cat_msg = $item_type_names[1]; // 是非題
        }
    } else {
        $item_cat_msg = $item_type_names[3];
    }
    
    // 配分
    $item_score = $item_obj->resprocessing->outcomes->decvar->attributes()->defaultval;
    
    $ret_resplace = array(
        $item_title,
        $item_cat_msg,
        $item_score,
        $MSG['score_unit'][$sysSession->lang]
    );
    
    $ret .= file_get_contents(sysDocumentRoot . '/mooc/smarty/templates/learn/exam/exam_title_item_single.tpl');
    $ret = str_replace($ret_orign, $ret_resplace, $ret);
    
    // 附檔
    if (is_array($attachments[$item_id]['topic_files'])) {
        foreach ($attachments[$item_id]['topic_files'] as $key => $value)
            $ret .= ('<br />' . gen_link($item_id, $value, $key));
    }
    
    // 組選項
    // 題型abcd
    $disabled_label = ($locking) ? 'disabled' : '';
    $abcd_type      = array(
        '1' => 'A',
        '2' => 'B',
        '3' => 'C',
        '4' => 'D',
        '5' => 'E',
        '6' => 'F'
    );
    
    $ret .= "<ul class=\"nav nav-pills nav-stacked\">";
    
    for ($i = 0; $i < count($item_obj->presentation->flow->response_lid->render_choice->response_label); $i++) {
        $locking    = false; // 預設為false
        $item_anser = $item_obj->presentation->flow->response_lid->render_choice->response_label[$i]->flow_mat->material->mattext;
        
        $item_type_rcard = strtolower($item_obj->presentation->flow->response_lid->attributes()->rcardinality);
        $item_type       = ($item_type_rcard == 'single') ? 'radio' : 'checkbox';
        $li_class        = ($item_type_rcard == 'single') ? 'item' : 'multi_item';
        
        $item_name_1        = $item_obj->attributes()->ident;
        $item_name_2        = $item_obj->presentation->flow->response_lid->attributes()->ident;
        $item_name_3        = ($item_type_rcard == 'single') ? '' : '[]';
        $item_value         = $item_obj->presentation->flow->response_lid->render_choice->response_label[$i]->attributes()->ident;
        $checked_class      = '';
        $ans_correct        = '';
        $checked_ans        = (string) $item_value;
        $checked_item_value = (string) $item_obj_result->response->response_value;
        $checked_label      = ($checked_item_value == $checked_ans) ? ' checked' : '';
        
        if ($checked_item_value == $checked_ans) {
            $checked_class = "r-select";
        }
        
        // 顯示單選題 正解框框
        if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER) {
            // 正解
            $the_ans_value = (string) $item_obj_result->response->response_form->correct_response;
            if ($checked_item_value == $the_ans_value) {
            } else {
                if ($checked_ans == $the_ans_value) {
                    $checked_class = 'g-select';
                    $ans_correct   = '<div class=\'alert alert-success g_single_correct_ans\'><img src=\'/theme/default/learn_mooc/right_ans.png\'> <span>正解</span></div>';
                }
            }
            
        }
        
        // 是非題的圈圈叉叉圖案
        if ($is_single) {
            // 如果是單選
            $item_anser_pic = $item_anser;
        } else {
            // 如果不是單選
            switch ($item_anser) {
                case 'Agree':
                    $item_anser_pic = "<img src=\"/theme/default/teach/right.png\" align=\"absmiddle\" />";
                    break;
                case 'Disagree':
                    $item_anser_pic = "<img src=\"/theme/default/teach/wrong.png\" align=\"absmiddle\" />";
                    break;
                default:
                    $item_anser_pic = $item_anser;
            }
        }
        
        // 替換Tpl內容物
        $ret_orign_context = array(
            '%item_desc%',
            '%item_no%',
            '%item_type%',
            '%li_class%',
            '%item_name_1%',
            '%item_name_2%',
            '%item_name_3%',
            '%item_value%',
            '%checked_label%',
            '%checked_class%',
            '%disabled_label%',
            '%ans_correct%'
        );
        
        $ret_resplace_context = array(
            ($is_single) ? htmlspecialchars($item_anser_pic) : $item_anser_pic,
            $abcd_type[$i + 1] . ".",
            $item_type,
            $li_class,
            $item_name_1,
            $item_name_2,
            $item_name_3,
            $item_value,
            $checked_label,
            $checked_class,
            $disabled_label,
            $ans_correct
        );
        
        $ret .= file_get_contents(sysDocumentRoot . '/mooc/smarty/templates/learn/exam/exam_item_single.tpl');
        $ret = str_replace($ret_orign_context, $ret_resplace_context, $ret);
        
        // 內部附檔
        $ret .= "<div style='padding-left: 50px;'>" . gen_link($item_id, @array_shift($attachments[$item_id]['render_choice_files'])) . "</div>";
    }
    
    $ret .= "</ul>";
    
    // 詳細解答
    $ret .= "<div><br />" . show_ans($node_for_ans) . "</div>";
    $ret .= "</td>";
    
    // 顯示答案
    if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER) {
        $user_select = (string) $item_obj_result->response->response_value;
        $the_ans     = (string) $item_obj_result->response->response_form->correct_response;
        $ret .= "<td style='vertical-align: middle;'>";
        
        // 顯示答案對錯logo
        if ($checked_item_value == $the_ans_value) {
            $ret .= "<div style='position: relative;'><div style='text-align: right;'><img src='/theme/default/learn_mooc/exam_sign_check.png'></div></div>";
        } else {
            $ret .= "<div style='position: relative;'><div style='text-align: right;'><img src='/theme/default/learn_mooc/exam_sign_error.png'></div></div>";
        }
        $ret .= "</td>";
    }
    
    return $ret;
}

/**
 *  新版 多選題
 */
function generateMultiChoice($item_obj, $item_obj_result, $itemSerial, $locking, $node_for_ans = '')
{
    global $attachments, $item_type_names;
    
    // 組題目
    $item_id   = (string) $item_obj->attributes()->ident;
    $ret       = "<td width='5%'><div style='margin-top:8px;'><strong><h4>" . $itemSerial . ".</h4></strong></div></td><td colspan=\"2\" style=\"padding-right: 86px;\">";
    $ret_orign = array(
        '%item_title%',
        '%item_cat%',
        '%item_score%',
        '%score_unit%'
    );
    
    $item_title = $item_obj->presentation->flow->material->mattext;
    $item_cat   = $item_obj->presentation->flow->response_lid->attributes()->rcardinality;
    if ($item_cat == 'Single') {
        $item_cat_msg = $item_type_names[2]; // 單選題
    } else {
        $item_cat_msg = $item_type_names[3]; // 複選題
    }
    $item_score   = $item_obj->resprocessing->outcomes->decvar->attributes()->defaultval;
    $ret_resplace = array(
        $item_title,
        $item_cat_msg,
        $item_score,
        $MSG['score_unit'][$sysSession->lang]
    );
    
    $ret .= file_get_contents(sysDocumentRoot . '/mooc/smarty/templates/learn/exam/exam_title_item_single.tpl');
    $ret = str_replace($ret_orign, $ret_resplace, $ret);
    
    // 附檔
    if (is_array($attachments[$item_id]['topic_files'])) {
        foreach ($attachments[$item_id]['topic_files'] as $key => $value)
            $ret .= ('<br />' . gen_link($item_id, $value, $key));
    }
    
    // 組選項
    // 題型abcd
    
    $disabled_label = ($locking) ? 'disabled' : '';
    $abcd_type      = array(
        '1' => 'A',
        '2' => 'B',
        '3' => 'C',
        '4' => 'D',
        '5' => 'E'
    );
    
    $ret .= "<ul class=\"nav nav-pills nav-stacked radio\">";
    
    // 紀錄學生之前做的題目 array
    $student_ans = array();
    for ($j = 0; $j < count($item_obj_result->response->response_value); $j++) {
        $push_value = (string) $item_obj_result->response->response_value[$j];
        array_push($student_ans, $push_value);
    }
    
    // 顯示單選題 正解框框
    if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER) {
        $the_ans_value = array();
        for ($q = 0; $q < sizeof($item_obj_result->response->response_form->correct_response); $q++) {
            $the_ans_value[$q] = (string) $item_obj_result->response->response_form->correct_response[$q];
        }
        
        if ($checked_item_value == $the_ans_value) {
        } else {
            if ($checked_ans == $the_ans_value) {
                $checked_class = 'g-select';
                $ans_correct   = '<div class=\'alert alert-success g_single_correct_ans\'>正解</div>';
            }
        }
        
    }
    
    for ($i = 0; $i < count($item_obj->presentation->flow->response_lid->render_choice->response_label); $i++) {
        $item_anser         = $item_obj->presentation->flow->response_lid->render_choice->response_label[$i]->flow_mat->material->mattext;
        $item_type          = ($item_cat == 'Single') ? 'radio' : 'checkbox';
        $li_class           = ($item_cat == 'Single') ? 'item' : 'multi_item';
        $item_name_1        = $item_obj->attributes()->ident;
        $item_name_2        = $item_obj->presentation->flow->response_lid->attributes()->ident;
        $item_name_3        = ($item_type_rcard == 'single') ? '' : '[]';
        $item_value         = $item_obj->presentation->flow->response_lid->render_choice->response_label[$i]->attributes()->ident;
        $ans_correct        = '';
        $checked_ans        = (string) $item_value;
        $checked_item_value = (string) $item_obj_result->response->response_value[$i];
        
        if (in_array($checked_ans, $student_ans)) {
            $checked_class = "r-select";
            $checked_label = "checked";
        } else {
            $checked_class = "non-select";
            $checked_label = "";
        }
        
        // 正解
        if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER) {
            if (in_array($checked_ans, $the_ans_value)) {
                $ans_correct = '<div class=\'alert alert-success g_single_correct_ans\'><img src=\'/theme/default/learn_mooc/right_ans.png\'> <span>正解</span></div>';
            }
        }
        
        $ret_orign_context    = array(
            '%item_desc%',
            '%item_no%',
            '%item_type%',
            '%li_class%',
            '%item_name_1%',
            '%item_name_2%',
            '%item_name_3%',
            '%item_value%',
            '%checked_label%',
            '%checked_class%',
            '%disabled_label%',
            '%ans_correct%'
        );
        $ret_resplace_context = array(
            htmlspecialchars($item_anser),
            $abcd_type[$i + 1] . ".",
            $item_type,
            $li_class,
            $item_name_1,
            $item_name_2,
            $item_name_3,
            $item_value,
            $checked_label,
            $checked_class,
            $disabled_label,
            $ans_correct
        );
        $ret .= file_get_contents(sysDocumentRoot . '/mooc/smarty/templates/learn/exam/exam_item_single.tpl');
        $ret = str_replace($ret_orign_context, $ret_resplace_context, $ret);
        
        // 內部附檔
        $ret .= "<div style='padding-left: 50px;'>" . gen_link($item_id, @array_shift($attachments[$item_id]['render_choice_files'])) . "</div>";
    }
    
    $ret .= "</ul>";
    $ret .= "<div><br />" . show_ans($node_for_ans) . "</div>";
    $ret .= "</td>";
    
    // 顯示答案對錯logo
    if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER) {
        $is_correct = false;
        
        // 正確解答 array
        $the_ans = array();
        for ($z = 0; $z < count($item_obj_result->response->response_form->correct_response); $z++) {
            $push_ans_value = (string) $item_obj_result->response->response_form->correct_response[$z];
            array_push($the_ans, $push_ans_value);
        }
        
        // 判斷這台是否答對
        foreach ($the_ans as $v) {
            if (in_array($v, $student_ans) == false) {
                $is_correct = false;
                break;
            } else {
                $is_correct = true;
            }
        }
        
        $ret .= "<td style='vertical-align: middle;'><div style='position: relative;'>";
        if ($is_correct == true) {
            $ret .= "<div style='text-align: right;'><img src='/theme/default/learn_mooc/exam_sign_check.png'></div>";
        } else {
            $ret .= "<div style='text-align: right;'><img src='/theme/default/learn_mooc/exam_sign_error.png'></div>";
        }
        $ret .= "</div></td>";
    }
    return $ret;
}

/*
 * 填充題
 */
function generateFILLitem($item_obj, $item_obj_result, $itemSerial, $locking = '', $node_for_ans = '')
{
    global $attachments, $item_type_names;
    $item_id = (string) $item_obj->attributes()->ident;
    $ret     = "<td width='5%'><div style='margin-top:8px;'><strong><h4>" . $itemSerial . ".</h4></strong></div></td><td colspan=\"2\" style=\"padding-right: 70px;padding-top: 20px;\">";
    
    $item_cat   = $item_obj->presentation->flow->response_str->attributes()->rcardinality;
    $item_score = $item_obj->resprocessing->outcomes->decvar->attributes()->defaultval;
    if ($item_cat == 'Single') {
        $item_cat_msg = $item_type_names[4]; // 填充題
    } else {
        $item_cat_msg = $item_type_names[5]; // 簡答/申論題
    }
    
    // 顯示單選題 正解框框
    if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER) {
        $the_ans_value = array();
        for ($q = 0; $q < sizeof($item_obj_result->response); $q++) {
            $the_ans_value[$q] = (string) $item_obj_result->response[$q]->response_form->correct_response;
        }
    }
    
    
    // 組題目
    for ($i = 0; $i < sizeof($item_obj->presentation->flow->material); $i++) {
        $item_input     = "";
        $item_name1     = $item_obj->attributes()->ident;
        $disabled_label = ($locking) ? 'disabled' : '';
        if (isset($item_obj->presentation->flow->response_str[$i])) {
            $item_name2  = (string) $item_obj->presentation->flow->response_str[$i]->attributes()->ident;
            $item_name3  = (string) $item_obj->presentation->flow->response_str[$i]->render_fib->response_label->attributes()->ident;
            $item_value1 = (string) $item_obj_result->response[$i]->response_value;
            $item_input  = "<input name=\"ans[{$item_name1}][{$item_name2}][{$item_name3}]\" type=\"text\" size=\"18\" value=\"{$item_value1}\" autocomplete=\"off\" {$disabled_label}>";
        }
        $item_desc = (string) $item_obj->presentation->flow->material[$i]->mattext;
        
        $ret_orign_context = array(
            '%item_desc%',
            '%item_name1%',
            '%item_name2%',
            '%item_name3%',
            '%item_input%'
        );
        
        $ret_resplace_context = array(
            $item_desc,
            $item_name1,
            $item_name2,
            $item_name3,
            $item_input
        );
        
        $ret .= file_get_contents(sysDocumentRoot . '/mooc/smarty/templates/learn/exam/exam_title_item_Fill.tpl');
        
        // 正解
        if ($the_ans_value[$i] != '') {
            if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER) {
                $ret .= "<div style=\"font-weight: bold; border-radius:5px; padding: 5px;width: 420px;color: #468847; background-color: #dff0d8; border-color: #d6e9c6;\">{$the_ans_value[$i]}</div>";
            }
        }
        
        $ret = str_replace($ret_orign_context, $ret_resplace_context, $ret);
    }
    
    // 組分數
    $ret_orign_context = array(
        '%item_cat%',
        '%item_score%',
        '%score_unit%'
    );
    
    $ret_resplace_context = array(
        $item_cat_msg,
        $item_score,
        $MSG['score_unit'][$sysSession->lang]
    );
    
    $ret .= file_get_contents(sysDocumentRoot . '/mooc/smarty/templates/learn/exam/exam_score_item_Fill.tpl');
    $ret = str_replace($ret_orign_context, $ret_resplace_context, $ret);
    
    // 附檔
    if (is_array($attachments[$item_id]['topic_files'])) {
        foreach ($attachments[$item_id]['topic_files'] as $key => $value)
            $ret .= ('<br />' . gen_link($item_id, $value, $key));
    }
    
    // 詳解
    $ret .= "<div><br />" . show_ans($node_for_ans) . "</div>";
    $ret .= "</td>";
    
    // for check or error answer 
    if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER) {
        $is_correct  = true;
        // 使用者所填的答案 array
        $student_ans = array();
        for ($z = 0; $z < count($item_obj_result->response); $z++) {
            $push_ans_value = (string) $item_obj_result->response[$z]->response_value;
            array_push($student_ans, $push_ans_value);
        }
        
        // 判斷這台是否答對
        $ans_correct_array = array();
        for ($i = 0; $i < count($the_ans_value); $i++) {
            if ($student_ans[$i] == $the_ans_value[$i]) {
                $ans_correct_array[$i] = true;
            } else {
                $ans_correct_array[$i] = false;
            }
            $is_correct = $ans_correct_array[$i] && $is_correct;
        }
        
        if ($is_correct == true) {
            $ret .= "<td style='vertical-align: middle;'><div style='position: relative;'><div style='text-align: right;'><img src='/theme/default/learn_mooc/exam_sign_check.png'></div></div></td>";
        } else {
            $ret .= "<td style='vertical-align: middle;'><div style='position: relative;'><div style='text-align: right;'><img src='/theme/default/learn_mooc/exam_sign_error.png'></div></div></td>";
        }
    }
    
    return $ret;
}

/*
 * 簡答題
 */
function generateFILLAns($item_obj, $item_obj_result, $itemSerial, $locking = '', $node_for_ans = '')
{
    global $attachments, $item_type_names;
    
    $item_id = (string) $item_obj->attributes()->ident;
    $ret     = "<td width='5%'><div style='margin-top:8px;'><strong><h4>" . $itemSerial . ".</h4></strong></div></td><td colspan=\"2\" style=\"padding-right: 86px;\">";
    
    $item_cat       = $item_obj->presentation->flow->response_str->attributes()->rcardinality;
    $item_name1     = $item_obj->attributes()->ident;
    $item_name2     = $item_obj->presentation->flow->response_str->attributes()->ident;
    $item_name3     = $item_obj->presentation->flow->response_str->render_fib->response_label->attributes()->ident;
    $item_desc      = (string) $item_obj->presentation->flow->material->mattext;
    $item_score     = $item_obj->resprocessing->outcomes->decvar->attributes()->defaultval;
    $item_value1    = (string) $item_obj_result->response->response_value;
    $disabled_label = ($locking) ? 'disabled' : '';
    
    if ($item_cat == 'Single') {
        $item_cat_msg = $item_type_names[4]; // 填充題
    } else {
        $item_cat_msg = $item_type_names[5]; // 簡答/申論題
    }
    
    // 組題目
    $ret_orign_context    = array(
        '%item_desc%',
        '%item_cat_msg%',
        '%item_score%',
        '%score_unit%'
    );
    $ret_resplace_context = array(
        $item_desc,
        $item_cat_msg,
        $item_score,
        $MSG['score_unit'][$sysSession->lang]
    );
    
    $ret .= file_get_contents(sysDocumentRoot . '/mooc/smarty/templates/learn/exam/exam_title_item_Ans.tpl');
    $ret = str_replace($ret_orign_context, $ret_resplace_context, $ret);
    
    // 附檔
    $ret .= "<div>";
    if (is_array($attachments[$item_id]['topic_files'])) {
        foreach ($attachments[$item_id]['topic_files'] as $key => $value)
            $ret .= ('<br />' . gen_link($item_id, $value, $key));
    }
    $ret .= "</div>";
    
    // 組題目內容
    $ret_orign_context = array(
        '%item_name_1%',
        '%item_name_2%',
        '%item_name_3%',
        '%item_value_1%',
        '%disabled_label%'
    );
    
    $ret_resplace_context = array(
        $item_name1,
        $item_name2,
        $item_name3,
        $item_value1,
        $disabled_label
    );
    
    $ret .= file_get_contents(sysDocumentRoot . '/mooc/smarty/templates/learn/exam/exam_item_Ans.tpl');
    $ret = str_replace($ret_orign_context, $ret_resplace_context, $ret);
    
    // 詳解
    $ret .= "<div><br />" . show_ans($node_for_ans) . "</div>";
    
    $ret .= "</td>";
    
    // for check or error answer
    if (QTI_DISPLAY_OUTCOME || QTI_DISPLAY_ANSWER) {
        //$ret .="<td style='vertical-align: middle;'><div style='position: relative;'><div style='text-align: right;'><img src='/theme/default/learn_mooc/exam_sign_error.png'></div></div></td>";
        $ret .= "<td></td>";
    }
    
    return $ret;
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
function generateFILL($item, $node, $node_for_ans)
{
    
    global $ctx, $attachments, $sysSession, $itemSerial, $MSG, $item_types, $item_type_names, $ctrl_paging, $simpXmlObject;
    
    $item_id = $item->get_attribute('ident');
    
    // 答題記憶
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
        $ret .= sprintf('<b>%s[%.2f]</b></td><td valign="top" nowrap>', $MSG['score_assigned'][$sysSession->lang], $outcome);
        
        // 回上頁鎖定答案
        $student_ans = array();
        if (QTI_DISPLAY_RESPONSE) {
            $locking = false;
            $re      = $ctx->xpath_eval("//item_result[@ident_ref='$item_id']/response/response_value");
            if (count($re->nodeset))
                foreach ($re->nodeset as $piece) {
                    $p                                             = $piece->parent_node();
                    $student_ans[$p->get_attribute('ident_ref')][] = $piece->get_content();
                    if ($piece->get_content() != '')
                        $locking = true;
                }
            if ($ctrl_paging != 'can_return')
                $locking = false;
        }
    }
    
    // item 用
    if (isset($simpXmlObject->item)) {
        for ($i = 0; $i < count($simpXmlObject->item); $i++) {
            if ($simpXmlObject->item[$i]->attributes()->ident == $item_id) {
                switch ($simpXmlObject->item[$i]->presentation->flow->response_str->attributes()->rcardinality) {
                    case "Single":
                        //echo "這是填充題\n"; 
                        $data        = $simpXmlObject->item[$i];
                        $data_result = $simpXmlObject->item_result[$i];
                        $ret         = generateFILLitem($data, $data_result, $itemSerial++, $locking, $node_for_ans);
                        break;
                    case "Ordered":
                        //echo "這是簡答題\n"; 
                        $data        = $simpXmlObject->item[$i];
                        $data_result = $simpXmlObject->item_result[$i];
                        $ret         = generateFILLAns($data, $data_result, $itemSerial++, $locking, $node_for_ans);
                        break;
                    default:
                        echo "error, the choice is not in the case.";
                        break;
                }
            }
        }
    }
    
    //section 用
    if (isset($simpXmlObject->section)) {
        for ($s = 0; $s < count($simpXmlObject->section); $s++) {
            for ($i = 0; $i < count($simpXmlObject->section[$s]->item); $i++) {
                if ($simpXmlObject->section[$s]->item[$i]->attributes()->ident == $item_id) {
                    switch ((string) $simpXmlObject->section[$s]->item[$i]->presentation->flow->response_str->attributes()->rcardinality) {
                        case "Single":
                            $data        = $simpXmlObject->section[$s]->item[$i];
                            $data_result = $simpXmlObject->section[$s]->item_result[$i];
                            $ret         = generateFILLitem($data, $data_result, $itemSerial++, $locking, $node_for_ans);
                            break;
                        case "Ordered":
                            $data        = $simpXmlObject->section[$s]->item[$i];
                            $data_result = $simpXmlObject->section[$s]->item_result[$i];
                            $ret         = generateFILLAns($data, $data_result, $itemSerial++, $locking, $node_for_ans);
                            break;
                        default:
                            echo "error, the choice is not in the case.";
                            break;
                    }
                }
            }
        }
    }
    
    return $ret;
}

/**
 * 產生配合題
 */
function generatePAIR($item, $node, $node_for_ans)
{
    global $ctx, $attachments, $sysSession, $itemSerial, $MSG, $item_types, $item_type_names, $ctrl_paging, $simpXmlObject;
    
    $item_id     = $item->get_attribute('ident');
    $result      = $ctx->xpath_eval('./*[substring-before(name(), "_") = "response"]', $node);
    $response_id = $result->nodeset[0]->get_attribute('ident');
    
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
    
    // item 單題用
    if (isset($simpXmlObject->item)) {
        for ($i = 0; $i < count($simpXmlObject->item); $i++) {
            if ($simpXmlObject->item[$i]->attributes()->ident == $item_id) {
                $date        = $simpXmlObject->item[$i];
                $data_result = $data_result = $simpXmlObject->item_result[$i];
                $ret         = generateItemPAIR($date, $data_result, $itemSerial++, $locking, $node_for_ans);
            }
        }
    }
    
    //section 大題用
    if (isset($simpXmlObject->section)) {
        for ($s = 0; $s < count($simpXmlObject->section); $s++) {
            for ($i = 0; $i < count($simpXmlObject->section[$s]->item); $i++) {
                if ($simpXmlObject->section[$s]->item[$i]->attributes()->ident == $item_id) {
                    switch ((string) $simpXmlObject->section[$s]->item[$i]->presentation->flow->response_grp->attributes()->rcardinality) {
                        case "Multiple":
                            $data        = $simpXmlObject->section[$s]->item[$i];
                            $data_result = $simpXmlObject->section[$s]->item_result[$i];
                            $ret         = generateItemPAIR($data, $data_result, $itemSerial++, $locking, $node_for_ans);
                            break;
                        default:
                            echo "error, the choice is not in the case.";
                            break;
                    }
                }
            }
        }
    }
    
    return $ret;
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
 * 顯示答案
 * 
 */
function show_ans($node = '')
{
    global $ctx, $attach_path, $attach_uri, $MSG, $sysSession, $sysConn;
    $ret = '';
    
    //詳解的部分
    if (QTI_DISPLAY_ANSWER) {
        $ret .= "<div style='cursor:pointer;' class='show_ans_btn'><img class='imgClickAndChange' src='/theme/default/learn_mooc/icon_a_close.png'><span style='color:#35BFBF; font-size: 1em; vertical-align: middle; font-weight: bold;'>{$MSG['detail_answer'][$sysSession->lang]}</span></div>";
        $ret .= "<div class='show_ans' style='display:none;' ";
        
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
        list($at) = dbGetStSr('WM_qti_' . QTI_which . '_item', 'attach', "ident='$ident'", ADODB_FETCH_NUM);
        $attach = ereg('^a:[0-9]+:{s:', $at) ? unserialize($at) : array();
        if ($attach['ans_files']) {
            $ret .= '<br>' . $MSG['ans_files'][$sysSession->lang];
            foreach ($attach['ans_files'] as $key => $value) {
                $entry = sprintf($attach_path . '%s/%s', $ident, $value);
                if (file_exists($entry))
                    $ret .= sprintf('<br><img src="/theme/%s/learn/file.gif" /><a href="%s%s/%s" target="_blank" class="cssAnchor">%s</a><br />', $sysSession->theme, $attach_uri, $ident, rawurlencode($value), $key);
            }
        }
        $ret .= "</div>";
    }
    
    return $ret;
}


/**
 * 轉換 <item>
 */
function transform_ITEM($node)
{
    global $ctx, $attach_path, $attach_uri, $MSG, $sysSession, $sysConn;
    
    // 把最原始的東西存起來給答案用
    $node_for_ans = $node;
    
    if ($node->get_attribute('visable') == 'invisible')
        return;
    
    $result = $ctx->xpath_eval('./presentation/flow', $node);
    
    if (count($result->nodeset) == 0)
        return 'unknown item type';
    
    $ret = sprintf("\t\t\t\t<tr style=\"background-color: #fff; height:320px\">\n\t\t\t\t\t<td valign=\"top\" align=\"left\" nowrap>");
    
    $kind = detect_item_type($node);
    
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
            $ret .= generateCHOICE($node, $result->nodeset[0], $node_for_ans);
            break;
        case 2: // 填充題 (字串)
        case 3: // 填充題 (數值)
        case 9:
            $ret .= generateFILL($node, $result->nodeset[0], $node_for_ans);
            break;
        case 4: // 配合題
            $ret .= generatePAIR($node, $result->nodeset[0], $node_for_ans);
            break;
        default:
            $ret .= '&nbsp;</td><td colspan="2"><h2 style="color: red">Unsupported item type : ' . $node->get_attribute('ident') . '</h2></td><td>';
            break;
    }
    
    return $ret . "</td>\n\t\t\t\t</tr>\n";
}

/**
 * 轉換 <section>
 */
function transform_SECTION($node)
{
    global $ctx;
    
    if ($node->get_attribute('visable') == 'invisible')
        return;
    
    $ret .= drawTable_new(Table_header, 'section');
    
    $result = $ctx->xpath_eval('./presentation_material', $node); // 大題的話先output 大題題目
    if (count($result->nodeset))
        echo '<tr class=""><td colspan="5"><span style=\"\">' . travelResponselabel($result->nodeset[0]) . '</span></td></tr>';
    
    foreach ($node->child_nodes() as $subnode) {
        switch ($subnode->tagname()) {
            // case 'presentation_material':
            // 	$ret .= '<tr class="bg02"><td colspan="4">' . travelResponselabel($subnode) . '</td></tr>';
            //	break;
            case 'itemref':
                $result = $ctx->xpath_eval('//item[@ident="' . $subnode->get_attribute('linkrefid') . '"]');
                if (count($result->nodeset))
                    $ret .= transform_ITEM($result->nodeset[0]);
                break;
            case 'item':
                $ret .= transform_ITEM($subnode);
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
    
    $ret .= drawTable_new(Table_footer, 'section');
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
    $ret = drawTable_new(Table_header, 'assessment');
    
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
    
    $ret .= drawTable_new(Table_footer, 'assessment');
    return $ret;
}

/**
 * 轉換整份 ASI
 */
function travelQuestestinterop($node)
{
    global $exam_id, $time_id, $ticket, $sectionSerial, $itemSerial;
    
    // showXHTML_css('include', '/theme/default/teach/wm.css');
    echo drawTable_new(Table_header, 'questestinterop');
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
                echo transform_ITEM($cur_node);
                break;
        }
    }
    
    echo '<tr><td colspan="5" align="center"><!--BUTTON_LINE--></td></tr>', "\n";
    
    echo drawTable_new(Table_footer, 'questestinterop');
}

/**
 * 去掉 default namespace 重新產生一份新的 dom
 */
function parseQuestestinterop($xmlstr)
{
    global $parserDom, $ctx, $simpXmlObject, $simpleXMl_ctx;
    if (!$parserDom = domxml_open_mem(setEncoding(preg_replace('/\sxmlns="[^"]*"/', '', $xmlstr))))
        return false;
    
    $p_dom = $parserDom->document_element();
    
    rm_whitespace($p_dom);
    
    $simpXmlObject = simplexml_load_string($xmlstr);
    
    $ctx = xpath_new_context($parserDom);
    
    $ctx->xpath_register_ns('wm', 'www.sun.net.tw/WisdomMaster');
    travelQuestestinterop($p_dom);
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
    $simpXmlObject = simplexml_load_string($xmlDoc);
    
    $ctx = xpath_new_context($xmlDoc);
    $ctx->xpath_register_ns('wm', 'www.sun.net.tw/WisdomMaster');
    travelQuestestinterop($xmlDoc->document_element());
}