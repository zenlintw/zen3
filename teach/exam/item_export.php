<?php
    /**************************************************************************************************
     *                                                                                                *
     *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
     *                                                                                                *
     *        Programmer: Wiseguy Liang                                                         *
     *        Creation  : 2003/02/18                                                            *
     *        work for  : export Item                                                           *
     *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
     *                                                                                                *
     **************************************************************************************************/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/teach/exam/qti_xml_lib.php');
    require_once(sysDocumentRoot . '/lib/archive_api.php');
    //ACL begin
    if (QTI_which == 'exam') {
        $sysSession->cur_func = '1600100600';
    }
    else if (QTI_which == 'homework') {
        $sysSession->cur_func = '1700100600';
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

    // 判斷 ticket 是否正確 (開始)
    $ticket = md5($_POST['gets'] . sysTicketSeed . $course_id . $_COOKIE['idx']);
    if ($ticket != $_POST['ticket']) {
       wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Illegal Access!');
       die('Illegal Access !');
    }
    // 判斷 ticket 是否正確 (結束)
    if (!ereg('^[A-Z0-9_]+(,[A-Z0-9_]+)*$', $_POST['lists'])) {    // 判斷 ident 序列格式
       wmSysLog($sysSession->cur_func, $course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'ID format error:'.$_POST['lists']);
       die('ID format error !' . $_POST['lists']);
    }
// CUSTOM BY tn (B)
    $RS = dbGetStMr('WM_qti_' . QTI_which . '_item', 'ident,type,content,attach,version,volume,chapter,paragraph,section,level', 'ident in (\'' . str_replace(',', "','", $_POST['lists']) . "')" . (ExamPackLimit ? (' limit ' . ExamPackLimit) : ''), ADODB_FETCH_ASSOC);

    while (@ob_end_clean());
    //ob_start("ob_gzhandler");
    //$fname = sprintf('WM_qti_items_%s.xml', date('YmdHis'));
    //header('Content-Disposition: attachment; filename="' . $fname . '"');
    //header('Content-Transfer-Encoding: binary');
    //header('Content-Type: application/octet-stream; name="' . $fname . '"');
    $csv='';
    ob_start();
    $xml='<?xml version="1.0" encoding="UTF-8"?><questestinterop>';
    if ($RS->RecordCount() > 0)
    {
        while(!$RS->EOF)
        {
            $item_xml=get_qti_item_xml($RS->fields['ident'], $RS->fields['content'], $RS->fields['attach']);
            //echo $RS->fields['type'];
            $xml.=$item_xml;
            $dom = domxml_open_mem(preg_replace(array('/>\s+</', '/\s+xmlns="[^"]*"/'), array('><', ''), $RS->fields['content']));
            $ctx1 = xpath_new_context($dom);
            $nodes = $dom->get_elements_by_tagname('item');
            $node=$nodes[0];
            $question_str="";
            $ans_str="";
            $option_str="";
            $level='';
            switch(intval($RS->fields['type'])){
                case 1://是非
                    //題目
                    $ret = $ctx1->xpath_eval("//presentation/flow/material/mattext");
                    $question_str=getNodeContent($ret->nodeset[0]);
                    //答案
                    $ret = $ctx1->xpath_eval("//resprocessing/respcondition/conditionvar/varequal");
                    $ans_str=getNodeContent($ret->nodeset[0])=="F"?0:1;
                    break;
                case 2:
                case 3:
                    //題目
                    $ret = $ctx1->xpath_eval("//presentation/flow/material/mattext");
                    $question_str=getNodeContent($ret->nodeset[0]);
                    //選項
                    $ret = $ctx1->xpath_eval("//response_lid/render_choice/response_label/flow_mat/material/mattext");
                    $option_ary=array();
                    foreach($ret->nodeset as $option){
                        $option_ary[]=getNodeContent($option);
                    }
                    $option_str=implode(" || ",$option_ary);
                    //答案
                    $ret = $ctx1->xpath_eval("//resprocessing/respcondition/conditionvar/varequal");
                    $ans_ary=array();
                    foreach($ret->nodeset as $option){
                        $ans_ary[]=getNodeContent($option);
                    }
                    $ans_str=implode(",",$ans_ary);
                    break;
                    break;
                case 5://簡答
                    //題目
                    $ret = $ctx1->xpath_eval("//presentation/flow/material/mattext");
                    $question_str=getNodeContent($ret->nodeset[0]);
                    break;
                case 4://填充
                    //題目
                    $ret = $ctx1->xpath_eval("//presentation/flow/material/mattext");
                    $ans_ret = $ctx1->xpath_eval("//resprocessing/respcondition/conditionvar/varequal");
                    foreach($ret->nodeset as $i=>$option){
                        $question_str.=getNodeContent($option);
                        if(array_key_exists($i,$ans_ret->nodeset)){
                            $question_str.="((".getNodeContent($ans_ret->nodeset[$i])."))";
                        }
                    }
                    break;
                case 6://配合
                    //題目
                    $ret = $ctx1->xpath_eval("//presentation/flow/material/mattext");
                    $question_str=getNodeContent($ret->nodeset[0]);
                    //選項
                    $ret = $ctx1->xpath_eval("//response_grp/render_extension/ims_render_object/response_label");
                    //答案
                    $ans_ret = $ctx1->xpath_eval("//resprocessing/respcondition/varsubset");
                    $option_ary=array();
                    $option1_ary=array();
                    $option_ary1=array();
                    $ans_ary=array();
                    foreach($ret->nodeset as $i=>$option){
                        if($option->has_attribute("match_group")){
                            $option_ary[]=getNodeContent($option);
                            //答案
                            if(array_key_exists($i,$ans_ret->nodeset)){
                                $ans_ary[]=strtolower($option->get_attribute("ident"))."{".getNodeContent($ans_ret->nodeset[$i])."}";
                            }
                        }else{
                            $option_ary1[]=getNodeContent($option);
                        }
                    }
                    $option_str=implode(" || ",$option_ary)." @@ ".implode(" || ",$option_ary1);
                    $ans_str=implode(",",$ans_ary);
                    break;
                default:
            }
            $ret = $ctx1->xpath_eval("//itemfeedback/solution/solutionmaterial/material/mattext");;
            $itemfeedback=getNodeContent($ret->nodeset[0]);
            $class=$RS->fields['version']."-".$RS->fields['volume']."-".$RS->fields['chapter']."-".$RS->fields['paragraph']."-".$RS->fields['section'];
            $split_str=",";
            $ans_str=str_replace("\"","&quot;",$ans_str);
            $question_str=str_replace("\"","&quot;",$question_str);
            $option_str=str_replace("\"","&quot;",$option_str);
            $itemfeedback=str_replace("\"","&quot;",$itemfeedback);
            $level=$RS->fields['level']; //難易度
            $csv.="\"".$RS->fields['type']."\"".$split_str."\"".$ans_str."\"".$split_str."\"".$question_str."\"".$split_str."\"".$option_str."\"".$split_str."\"".$itemfeedback."\"".$split_str."\"".$class."\"".$split_str."\"".$level."\""."\n";
            $RS->MoveNext();
        }
    }
    $xml.='</questestinterop>';
    //$csv = chr(255) . chr(254) .mb_convert_encoding($csv, 'UTF-16LE', 'UTF-8');
    //$csv = chr(239) . chr(187) . chr(191).$csv;
    $csv = mb_convert_encoding($csv, 'big5', 'UTF-8');
    $export_obj = new ZipArchive_php4($fname);
    if (in_array('csv', $_POST['export_kinds'])) $export_obj->add_string($csv, 'WM_qti_items_Big5.csv');
    if (in_array('xml', $_POST['export_kinds'])) $export_obj->add_string($xml, 'WM_qti_items.xml');
    while (@ob_end_clean());
    $download_name = preg_replace(array('!\.\./!U', sprintf('!%s!U', preg_quote(DIRECTORY_SEPARATOR))),array('', ''),stripslashes($_POST['download_name']));
    $fname = $download_name;
    if (substr($fname,-4)!='.zip') {
        $fname = $fname . '.zip';
    }
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Type: application/zip; name="' . $fname . '"');
    $export_obj->readfile();
    $export_obj->delete();
// CUSTOM BY tn (E)
?>
