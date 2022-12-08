<?php
/**************************************************************************************************
 *                                                                                                *
 *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
 *                                                                                                *
 *      Programmer: Wiseguy Liang                                                                 *
 *      Creation  : 2003/08/10                                                                    *
 *      work for  :                                                                               *
 *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
 *                                                                                                *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lang/teach_course.php');
require_once(sysDocumentRoot . '/lib/lib_encrypt.php');

define('path_amount_limit', 50);

function stripDefaultNamespace($str)
{
    return preg_replace('/\bxmlns\s*=\s*"[^"]*"/sU', '', $str);
}

$save_cid = sysNewDecode(trim($_GET['cid']));

$sysSession->cur_func = '1900100200';
$sysSession->restore();
if (!aclVerifyPermission(1900100200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
    $xmlstr = mb_convert_encoding(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]+/', ' ', $GLOBALS['HTTP_RAW_POST_DATA']), 'UTF-8', 'UTF-8');
    if (!$dom = domxml_open_mem($xmlstr)) die($MSG['No Save.'][$sysSession->lang]);

    $doc  = domxml_open_mem($xmlstr);
    $path = @domxml_open_mem(stripDefaultNamespace($xmlstr));
    $ctx  = xpath_new_context($path);


    $resource = '';
    if ($doc) {
        $getitems = $doc->get_elements_by_tagname('item');

        for ($i = 0; $i < count($getitems); $i++) {
            $a       = $getitems[$i]->get_elements_by_tagname('title');
            $sco_id  = $getitems[$i]->get_attribute('identifier');
            $sco_id2 = $getitems[$i]->get_attribute('identifierref');
            unset($href, $xml_base);


            $ret = $ctx->xpath_eval("/manifest/resources/resource[@identifier='{$sco_id2}']");
            if ($ret) {
                foreach ($ret->nodeset as $res) {
                    $href     = strip_tags($res->get_attribute('href'));
                    $href     = str_replace('<', '&lt;', $href);
                    $href     = str_replace('>', '&gt;', $href);
                    $xml_base = strip_tags($res->get_attribute('xml:base'));
                }
            }

            // 設定LCMS教材屬性
            if (preg_match('/.*\/courses\/play\/(\d*)/', $href) === 1 || preg_match('/.*\/unit\/view\/(\d*)/', $href) === 1 || preg_match('/.*\/asset\/detail\/(\d*)/', $href) === 1) {
                $resourceType = 'lcms';
            } else {
                $resourceType = 'webcontent';
            }

            if ($xml_base != '' && isset($xml_base)) {
                $resource .= '<resource identifier="' . $sco_id2 . '" type="' . $resourceType . '" xml:base="' . $xml_base . '" href="' . $href . '" adlcp:scormtype="asset"><file href="' . $href . '"/></resource>';
            } else {
                $resource .= '<resource identifier="' . $sco_id2 . '" type="' . $resourceType . '" href="' . $href . '" adlcp:scormtype="asset"><file href="' . $href . '"/></resource>';
            }

        }
    }


    $xrs = $ctx->xpath_eval('//manifest/resources');
    foreach ($xrs->nodeset as $resources) {
        $resources->unlink_node();
    }
    $content = $path->dump_mem();

    $resource = '<resources>' . $resource . '</resources></manifest>';
    $resource = str_replace('&', '&amp;', $resource);

    $xmlstr = str_replace('</manifest>', $resource, $content);


    $GLOBALS['HTTP_RAW_POST_DATA'] = $sysConn->qstr($xmlstr);

    chkSchoolId('WM_term_path');
    // $sysConn->Execute("insert into WM_term_path (course_id,serial,content, username, update_time) select {$sysSession->course_id},if(max(serial) IS NULL,1,max(serial)+1),{$GLOBALS['HTTP_RAW_POST_DATA']},'{$sysSession->username}', now() from WM_term_path where course_id={$sysSession->course_id} limit 1");

    // 節點上下左右移動所形成的自動存檔，不額外建立路徑備份
    if (isset($_GET['autoSave']) && (intval($_GET['autoSave'])===1)){
        $currPathSerail = intval(dbGetOne(
            'WM_term_path',
            'max(serial)',
            sprintf("course_id=%d",$save_cid)
        ));

        if ($currPathSerail > 0) {
            dbSet(
                'WM_term_path',
                sprintf("content=%s, username='%s', update_time=NOW()", $GLOBALS['HTTP_RAW_POST_DATA'], $sysSession->username),
                sprintf("course_id=%d AND serial=%d",$save_cid, $currPathSerail)
            );
        }
    }else{
        $sysConn->Execute("insert into WM_term_path (course_id,serial,content, username, update_time) select {$save_cid},if(max(serial) IS NULL,1,max(serial)+1),{$GLOBALS['HTTP_RAW_POST_DATA']},'{$sysSession->username}', now() from WM_term_path where course_id={$save_cid} limit 1");
    }

    if ($sysConn->ErrorNo()) {
        echo 'Error: ', $sysConn->ErrorNo(), ' = ', $sysConn->ErrorMsg();
        // wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'teacher', $_SERVER['PHP_SELF'], 'Error: '. $sysConn->ErrorMsg());
        wmSysLog($sysSession->cur_func, $save_cid, 0, 1, 'teacher', $_SERVER['PHP_SELF'], 'Error: ' . $sysConn->ErrorMsg());
    } else {
        /* [MOOC](B) #57696 儲存學習路徑類型 2014/12/30 By Spring */
        // 儲存自我評量題數 (0: 不啟用, 1~3: 幾題)
        if ($_GET['num'] != null && $_GET['num'] != '') {
            $asmt_num = intval($_GET['num']);
        } else {
            $asmt_num = 0;
        }
        // 學習路徑type: 0:未設定；1:自訂課程；2:短期課程；3:一般課程
        if ($_GET['typep'] != null && $_GET['typep'] != '') {
            dbSet('WM_term_course', '`path_type`=' . intval($_GET['typep']) . ', `exam_num`=' . $asmt_num, '`course_id`=' . $save_cid);
        } else {
            dbSet('WM_term_course', '`path_type`=1, `exam_num`=' . $asmt_num, '`course_id`=' . $save_cid);
        }
        /* [MOOC](E) #57696 */
        // $path_amount = $sysConn->GetCol("select serial from WM_term_path where course_id={$sysSession->course_id} order by serial DESC");
        $path_amount = $sysConn->GetCol("select serial from WM_term_path where course_id={$save_cid} order by serial DESC");

        if (count($path_amount) > path_amount_limit) // 如果存超過 50 個路徑
            {
            // 刪除 50 以前的
            // dbDel('WM_term_path', 'course_id=' . $sysSession->course_id . ' and serial in (' . implode(',', array_slice($path_amount, path_amount_limit)) . ')');
            dbDel('WM_term_path', 'course_id=' . $save_cid . ' and serial in (' . implode(',', array_slice($path_amount, path_amount_limit)) . ')');
            // 更改最近 50 個
            for ($i = path_amount_limit - 1; $i >= 0; $i--) {
                // dbSet('WM_term_path', 'serial=' . (path_amount_limit - $i), 'course_id=' . $sysSession->course_id . ' and serial=' . $path_amount[$i]);
                dbSet('WM_term_path', 'serial=' . (path_amount_limit - $i), 'course_id=' . $save_cid . ' and serial=' . $path_amount[$i]);
            }
        }

        $msg_echo = $MSG['Save Complete.'][$sysSession->lang];

        //			$msg_echo .= ($save_cid != $sysSession->course_id)? 'N' : 'Y';

        echo $msg_echo;
        // wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 0, 'teacher', $_SERVER['PHP_SELF'], 'update term path success');
        wmSysLog($sysSession->cur_func, $save_cid, 0, 0, 'teacher', $_SERVER['PHP_SELF'], 'update term path success');
    }
} else
    echo $MSG['No Save.'][$sysSession->lang];