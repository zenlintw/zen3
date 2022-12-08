<?php
/**
 * 課程包裝
 *
 * 建立日期：2008/08/29
 * @author  wing
 * @version $Id: co_qti_pack1.php,v 1.10.24.5 2006/09/19 09:45:34 wing Exp $
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/quota.php');
require_once(sysDocumentRoot . '/lang/course_pack_install.php');
/* Custom By TN 20120117(B)MIS#023740*/
require_once(sysDocumentRoot . '/teach/course/import_imsmanifest.lib.php');
ignore_user_abort(1); // run script in background
set_time_limit(0); // run script forever
/* Custom By TN 20120117(E)MIS#023740*/
//$sysConn->debug=true;
define('path_amount_limit', 49);

$course_elements     = $_POST['course_elements']; // 包裝內容選項
    $subjectBoardMapping = array();
    $qtiExamIdMapping = array();
    function mkdirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
        if (!mkdirs(dirname($dir), $mode)) return FALSE;
        return @mkdir($dir, $mode);
    }
    function cloneCourse($old_cid, $new_cid, $replace)
    {
        global $course_elements,$sysConn,$sysSession;
        $note=array();
    $old_cid     = intval($old_cid);
        $new_cid = intval($new_cid);
        //複製記錄(B)
        $old_caption=dbGetOne("WM_term_course","caption","course_id={$old_cid}");
        $rs=dbNew("CO_qti_pack_log","username,log_stime,source_course_id,source_caption,destination_course_id","'{$sysSession->username}',NOW(),'{$old_cid}','{$old_caption}','{$new_cid}'");
        if($rs) $logId=$sysConn->Insert_ID();
        //複製記錄(E)
        if (in_array("subject_board", $course_elements)) // 議題討論板 有勾選
            _processBD($old_cid, $new_cid);                 // 議題討論

    if (in_array("course_board", $course_elements)) // 課程討論板 有勾選
        _processCD($old_cid, $new_cid); // 課程討論板

    $note = _processQTI($old_cid, $new_cid); // 三合一

    if (in_array("node", $course_elements)) // 教材節點 有勾選
        {
        $note['path'] = _processTermPath($old_cid, $new_cid, $replace); // 學習路徑
        _processContent($old_cid, $new_cid); // 教材
    }

    /* Custom By TN 20110222(B)MIS#020180 */
    wmSysLog($sysSession->cur_func, $new_cid, 0, 0, 'auto', $_SERVER['PHP_SELF'], 'cloneCourse ' . $old_cid . ' to ' . $new_cid);
    /* Custom By TN 20110222(E)MIS#020180 */
    // 重新計算quota
    dbSet("CO_qti_pack_log", "log_etime=NOW(),state='1',note=" . $sysConn->qstr(serialize($note)) . "", "serial_no={$logId}");
    getCalQuota($new_cid, $quota_used, $quota_limit);
    setQuota($new_cid, $quota_used);
}

/**
 * 處理三合一
 * @param int $old_cid 舊課程course_id
 * @param int $new_cid 新課程course_id
 */
function _processQTI($old_cid, $new_cid)
{
    $old_cid = intval($old_cid);
    $new_cid = intval($new_cid);
    global $sysConn, $sysSession, $course_elements, $qtiExamIdMapping;
    $note=array();
    foreach(array('exam', 'homework', 'questionnaire') as $qti_which)
    {
        if (!in_array($qti_which, $course_elements)) continue;

        $i = 0;
        $old_ident = array();
        $new_ident = array();
        $old_path  = sprintf('%s/base/%05d/course/%08d/%s/Q/', sysDocumentRoot, $sysSession->school_id, $old_cid, $qti_which);
        $new_path  = sprintf('%s/base/%05d/course/%08d/%s/Q/', sysDocumentRoot, $sysSession->school_id, $new_cid, $qti_which);

        $t     = split('[. ]', microtime());
        $ident = sprintf('WM_ITEM1_%s_%u_%s_', sysSiteUID, $new_cid, $t[2]);
        $count = intval(substr($t[1],0,6));
        // 複製題目
        $rs = dbGetStMr('WM_qti_' . $qti_which . '_item', '*', 'course_id=' . $old_cid, ADODB_FETCH_ASSOC);
        if ($rs) while ($row = $rs->FetchRow())
        {
            $old_ident[$i]    = $row['ident'];

            // 解決發生題庫的ident和content裡的ident不一致問題
            preg_match('/\sident="(WM_ITEM[0-9_]*)"/', $row['content'], $matches);
//                echo '<pre>';
//                var_dump('content ident', $matches[1]);
//                echo '</pre>';

            $row['ident']     = $ident . ($count++);
            $new_ident[$i]    = $row['ident'];
            $row['content']   = str_replace($old_ident[$i], $new_ident[$i], $row['content']);
//                echo '<pre>';
//                var_dump('_item', htmlentities($matches[1]), htmlentities($new_ident[$i]), htmlentities($row['content']));
//                echo '</pre>';
            $row['content']   = str_replace($matches[1], $new_ident[$i], $row['content']);


            $row['course_id'] = $new_cid;

            $row['create_time'] = date('Y-m-d H:i:s', time());
            $row['last_modify'] = date('Y-m-d H:i:s', time());

//                echo '<pre>';
//                var_dump('content', htmlentities($row['content']));
//                echo '</pre>';

//                global $sysConn;
//                $sysConn->debug = true;

            if ($sysConn->AutoExecute('WM_qti_' . $qti_which . '_item', $row, 'INSERT')) // 複製夾檔
                {
                /* Custom By TN 20120117(B)MIS#023740*/
                //記錄
                $note[$qti_which]['item']+=intval($sysConn->Affected_Rows());
                /* Custom By TN 20120117(E)MIS#023740*/
                if (is_dir("{$old_path}/{$old_ident[$i]}"))
                {
                    if (!is_dir($new_path)) @exec('mkdir -p ' . $new_path);
                    @exec("cp -Rf {$old_path}/{$old_ident[$i]} {$new_path}/{$new_ident[$i]}");
                }
            }
//                global $sysConn;
//                $sysConn->debug = 0;

            $i++;
        }

        // 複製試卷
        $rs = dbGetStMr('WM_qti_' . $qti_which . '_test', '*', 'course_id=' . $old_cid . ' order by sort, exam_id desc', ADODB_FETCH_ASSOC);
        if ($rs) while ($row = $rs->FetchRow())
        {
                            $oldNode = $row['exam_id'];
            $row['exam_id']   = 'NULL';
            $row['course_id'] = $new_cid;
            $row['content']   = str_replace($old_ident, $new_ident, $row['content']);
            /* Custom By TN 20120117(B)MIS#023740*/
            if($sysConn->AutoExecute('WM_qti_' . $qti_which . '_test', $row, 'INSERT')){
                $newNode = $sysConn->Insert_ID();
                $qtiExamIdMapping[$qti_which][] = array($oldNode,$newNode);
        //記錄
                $note[$qti_which]['test']+=intval($sysConn->Affected_Rows());

            }
            /* Custom By TN 20120117(E)MIS#023740*/
        }
    }
    return $note;
}

/**
 * 處理議題討論
 * @param int $old_cid 舊課程course_id
 * @param int $new_cid 新課程course_id
 */
function _processBD($old_cid, $new_cid)
{
    global $sysConn, $sysSession, $MSG, $subjectBoardMapping;
    list($discuss, $bulletin) = dbGetStSr('WM_term_course', 'discuss,bulletin', "course_id=$old_cid");

        $RS = dbGetStMr('WM_bbs_boards','*',"owner_id=$old_cid and board_id not in ($discuss,$bulletin)");
        while ($fields = $RS->FetchRow())
        {
            /* Custom By TN 20110922(B)MIS#022433 */
            $old_bname=unserialize($fields['bname']);
            $new_bname_ary=array();
            foreach($old_bname as $lang=>$val){
                $new_bname_ary[$lang]=$MSG['msg_copy'][$lang].$val;
            }
            $new_bname=serialize($new_bname_ary);
            $bbs_sql = 'insert into WM_bbs_boards (bname, manager, title, owner_id, open_time, close_time, share_time, switch, with_attach, vpost, default_order, post_times, extras) values '.
                                     "('{$new_bname}','{$fields['manager']}','{$fields['title']}','{$new_cid}','{$fields['open_time']}','{$fields['close_time']}','{$fields['share_time']}',".
                                     "'{$fields['switch']}','{$fields['with_attach']}','{$fields['vpost']}','{$fields['default_order']}','{$fields['post_times']}','{$fields['extras']}')";
            /* Custom By TN 20110922(E)MIS#022433 */
            //echo $bbs_sql."<br>";
            $sysConn->Execute($bbs_sql);
            // echo $sysConn->ErrorMsg();
            $board_id = $sysConn->Insert_ID();
            $RS1 = dbGetStMr('WM_term_subject','*',"course_id=$old_cid and board_id = '{$fields['board_id']}'");
            while ($fields1 = $RS1->FetchRow())
            {
                $sub_sql = 'insert into WM_term_subject (course_id, board_id, state, visibility, permute) values '.
                                     "('{$new_cid}','{$board_id}','{$fields1['state']}','{$fields1['visibility']}','{$fields1['permute']}')";
                // echo $sub_sql."<br>";
                $sysConn->Execute($sub_sql);
                // echo $sysConn->ErrorMsg();

                $newSubjectBoardNode = $sysConn->Insert_ID();
                $oldSubjectBoardNode = dbGetOne('WM_term_subject','node_id',sprintf('course_id=%d and board_id=%d',$old_cid, $fields['board_id']));
                $subjectBoardMapping[] = array($oldSubjectBoardNode, $newSubjectBoardNode);
            }
        }
    }

    /**
 * 處理課程討論
 * @param int $old_cid 舊課程course_id
 * @param int $new_cid 新課程course_id
 */
function _processCD($old_cid, $new_cid)
{
    $old_cid = intval($old_cid);
    $new_cid = intval($new_cid);
    global $sysConn, $sysSession, $MSG;
    list($discuss, $bulletin) = dbGetStSr('WM_term_course', 'discuss,bulletin', "course_id=$old_cid");

        // 複製課程討論版/課程公告版
        $RS = dbGetStMr('WM_bbs_boards','*',"owner_id=$old_cid and board_id in ($discuss,$bulletin)");

        while ($fields = $RS->FetchRow())
        {
            /* Custom By TN 20110922(B)MIS#022433 */
            $old_bname=unserialize($fields['bname']);
            $new_bname_ary=array();
            foreach($old_bname as $lang=>$val){
                $new_bname_ary[$lang]=$MSG['msg_copy'][$lang].$val;
            }
            $new_bname=serialize($new_bname_ary);
            $bbs_sql = 'insert into WM_bbs_boards (bname, manager, title, owner_id, open_time, close_time, share_time, switch, with_attach, vpost, default_order, post_times, extras) values '.
                                     "('{$new_bname}','{$fields['manager']}','{$fields['title']}','{$new_cid}','{$fields['open_time']}','{$fields['close_time']}','{$fields['share_time']}',".
                                     "'{$fields['switch']}','{$fields['with_attach']}','{$fields['vpost']}','{$fields['default_order']}','{$fields['post_times']}','{$fields['extras']}')";
            //echo $bbs_sql;
            /* Custom By TN 20110922(E)MIS#022433 */
            $sysConn->Execute($bbs_sql);
            $board_id = $sysConn->Insert_ID();
            $RS1 = dbGetStMr('WM_term_subject','*',"course_id=$old_cid and board_id = '{$fields['board_id']}'");
            while ($fields1 = $RS1->FetchRow())
            {
                $sub_sql = 'insert into WM_term_subject (course_id, board_id, state, visibility, permute) values '.
                                     "('{$new_cid}','{$board_id}','{$fields1['state']}','{$fields1['visibility']}','{$fields1['permute']}')";
                $sysConn->Execute($sub_sql);


            }

            // 複製課程討論版/課程公告版文章
            $RS2 = dbGetStMr('WM_bbs_posts','*',"board_id in ({$fields['board_id']})");
            while ($fields2 = $RS2->FetchRow())
            {
                /* Custom By TN 20110323(B)MIS#020573 */
                $fields2['content']=addslashes($fields2['content']);
                /* Custom By TN 20110323(E)MIS#020573 */
                $posts_sql = 'insert into WM_bbs_posts (board_id, node, site, pt, poster, realname, email, homepage, subject, content, attach, rcount, rank, hit, lang) values '.
                             "('{$board_id}','{$fields2['node']}','{$fields2['site']}','{$fields2['pt']}','{$fields2['poster']}','{$fields2['realname']}','{$fields2['email']}','{$fields2['homepage']}','{$fields2['subject']}','{$fields2['content']}','{$fields2['attach']}','{$fields2['rcount']}','{$fields2['rank']}','{$fields2['hit']}','{$fields2['lang']}')";
                $sysConn->Execute($posts_sql);
            }

            // 處理夾檔
            $old_path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$old_cid}";
            $new_path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$new_cid}";
            if (!is_dir($old_path)) exec('mkdir -p ' . $old_path);
            if (!is_dir($new_path)) exec('mkdir -p ' . $new_path);
            @exec("cp -Rf {$old_path}/board/{$fields['board_id']} {$new_path}/board/{$board_id}");
        }
    }

/**
 * 處理學習路徑
 * @param int $old_cid 舊課程course_id 來源課程
 * @param int $new_cid 新課程course_id 目的地課程
 */
function _processTermPath($old_cid, $new_cid, $replace)
{
    global $sysConn, $sysSession, $subjectBoardMapping, $qtiExamIdMapping;

    /*** CUSTOM (B) by Yea for MIS#9336 ***/
    $path_amount = $sysConn->GetCol("select serial from WM_term_path where course_id={$new_cid} order by serial DESC");

    if (count($path_amount) > path_amount_limit) // 如果存超過 50 個路徑
        {
        // 刪除 50 以前的
        dbDel('WM_term_path', 'course_id=' . $new_cid . ' and serial in (' . implode(',', array_slice($path_amount, path_amount_limit)) . ')');
            // 更改最近 50 個
            for($i=path_amount_limit-1; $i>=0; $i--)
            {
                dbSet('WM_term_path', 'serial=' . (path_amount_limit - $i), 'course_id=' . $new_cid . ' and serial=' . $path_amount[$i]);
            }
        }
        /*** CUSTOM (E) by Yea ***/

        // 處理課程公告、課程討論板
    list($discuss, $bulletin) = dbGetStSr('WM_term_course', 'discuss,bulletin', "course_id=$old_cid");
    list($discuss1, $bulletin1) = dbGetStSr('WM_term_course', 'discuss,bulletin', "course_id=$new_cid");

    //課程公告的對應
        list($oldSubjectBoard,$oldSubjectNode) = dbGetStSr('WM_term_subject','board_id,node_id',sprintf('course_id=%d and board_id=%d',$old_cid, $discuss));
        list($newSubjectBoard,$newSubjectNode) = dbGetStSr('WM_term_subject','board_id,node_id',sprintf('course_id=%d and board_id=%d',$new_cid, $discuss1));
        $subjectBoardMapping[] = array($oldSubjectNode, $newSubjectNode,$oldSubjectBoard,$newSubjectBoard);

        //課程討論板的對應
        list($oldSubjectBoard,$oldSubjectNode) = dbGetStSr('WM_term_subject','board_id,node_id',sprintf('course_id=%d and board_id=%d',$old_cid, $bulletin));
        list($newSubjectBoard,$newSubjectNode) = dbGetStSr('WM_term_subject','board_id,node_id',sprintf('course_id=%d and board_id=%d',$new_cid, $bulletin1));
        $subjectBoardMapping[] = array($oldSubjectNode, $newSubjectNode,$oldSubjectBoard,$newSubjectBoard);

        // 處理學習路徑內的議題討論的編號
        // $newTermPath = dbGetRow('WM_term_path','serial, content',"course_id={$new_cid} order by serial desc limit 1",ADODB_FETCH_ASSOC);
    // dbSet('WM_term_path',"content='".mysql_escape_string($newTermPath['content'])."'",sprintf('course_id=%d and serial=%d',$new_cid,$newTermPath['serial']));


        // 取得舊課程的學習路徑
        /* Custom By TN 20120117(B)MIS#023740*/
        $content = dbGetOne('WM_term_path', 'content', "course_id={$old_cid} order by serial desc");
        $content =str_replace(
            array("default=\"Course{$old_cid}\"","identifier=\"Course{$old_cid}\""),
            array("default=\"Course{$new_cid}\"","identifier=\"Course{$new_cid}\""),
            $content
            );

        for ($i = 0, $size=count($subjectBoardMapping); $i < $size; $i++) {
            $content = str_replace('fetchWMinstance(5,'.$subjectBoardMapping[$i][0].')','fetchWMinstance(5,'.$subjectBoardMapping[$i][1].')',$content);
        $content = str_replace('fetchWMinstance(6,' . $subjectBoardMapping[$i][2] . ')', 'fetchWMinstance(6,' . $subjectBoardMapping[$i][3] . ')', $content);
        }

        foreach(array('exam', 'homework', 'questionnaire') as $key => $qti_which)
        {
            if($qti_which == 'exam'){
                $key = 3;
            }else if($qti_which == 'homework'){
                $key = 2;
        } else if ($qti_which == 'questionnaire') {
            $key = 4;
        }

        for ($i = 0, $size = count($qtiExamIdMapping[$qti_which]); $i < $size; $i++) {
            $content = str_replace('fetchWMinstance(' . $key . ',' . $qtiExamIdMapping[$qti_which][$i][0] . ')', 'fetchWMinstance(' . $key . ',' . $qtiExamIdMapping[$qti_which][$i][1] . ')', $content);
        }
    }

//    // 取目的課程目前學習路徑內容
//    if (!$pathExist = @domxml_open_mem($contentExist)) {
//        return;
//    }
//    $ctxExist = xpath_new_context($pathExist);
//    xpath_register_ns($ctxExist, "xml", "");
//    $retExist = $ctxExist->xpath_eval("/manifest/resources/resource");
//    $identifierExist = array();
//    if ($retExist) {
//        foreach ($retExist->nodeset as $resExist) {
//            $identifierExist[] = $resExist->get_attribute('identifier');
//        }
//    }

        //補檔修正(B)
        if (!$newpath = @domxml_open_mem($content)) return;
        $ctx1 = xpath_new_context($newpath);
        xpath_register_ns($ctx1, "xml", ""); // sets the prefix "pre" for the namespace
    $ret = $ctx1->xpath_eval("/manifest/resources/resource");
//    echo '<pre>';
//    var_dump(htmlentities($content));
//    echo '</pre>';
    if ($ret)
        foreach ($ret->nodeset as $res) {
            if ($res->get_attribute('base') && preg_match('/\/base\/10001\/course\/[0-9]{8}\/content\/(.*)$/', $res->get_attribute('base'), $match)) {
                $href = str_replace("\\", "/", $res->get_attribute('href'));
                if (mkdirs(dirname(sysDocumentRoot . "/base/10001/course/{$new_cid}/content/" . $match[1] . $href))) {
                    // 標準來源路徑
                    $source_course_path      = sysDocumentRoot . $res->get_attribute('base') . $href;
                    // 實際來源路徑
                    $real_source_course_path = realpath($source_course_path);
                    // 標準目的路徑
                    $dest_course_path        = sysDocumentRoot . "/base/10001/course/{$new_cid}/content/" . $match[1] . $href;
                    // 實際目的路徑
                    $real_dest_course_path   = realpath($dest_course_path);
                    copy($real_source_course_path, $real_dest_course_path);
                }
            }

//            // 來源課程的 identifier 是否已經存在於目的課程
//            $identifierExistItem = $res->get_attribute('identifier');
//            echo '<pre>';
//            var_dump($identifierExistItem);
//            echo '</pre>';
//            if (in_array($res->get_attribute('identifier'), $identifierExist)) {
//                $identifierNewItem = 'SCO_' . $new_cid . '_' . time() . rand(111111,999999);
//                echo '<pre>';
//                var_dump('exist->' . $identifierNewItem);
//                echo '</pre>';
//                $content = str_replace(array(
//                    $identifierExistItem
//                ), array(
//                    $identifierNewItem
//                ), $content);
//            }
        }
    //補檔修正(E)

    // 註解下列，為中原跨課才需要進行的替代
    // $content = preg_replace('/\/base\/10001\/course\/[0-9]{8}\/content\//', '', $content);
//    echo '<pre>';
//    var_dump(htmlentities($content));
//    echo '</pre>';
    return processNewImsmanifest($content, $replace, false); //第3個參數是客製的by lubo
    /* Custom By TN 20120117(E)MIS#023740*/



}

/**
 * 處理教材
     * @param int $old_cid 舊課程course_id
     * @param int $new_cid 新課程course_id
     */
    function _processContent($old_cid, $new_cid)
    {
        global $sysSession;
        $old_cid = intval($old_cid);
        $new_cid = intval($new_cid);
        $old_path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$old_cid}";
        $new_path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$new_cid}";
        if (!is_dir($old_path)) exec('mkdir -p ' . $old_path);
        if (!is_dir($new_path)) exec('mkdir -p ' . $new_path);
        @exec("cp -Rf {$old_path}/content {$new_path}");
    }

    showXHTML_head_B($MSG['course_copy_wizard'][$sysSession->lang]);
    showXHTML_head_E();
showXHTML_body_B();
//主程式(B)
// 輸出空白，避免 timeout
echo '<div id="flushMsg" style="width:100%"><h2 align="center">' . $MSG['co_btn_pack'][$sysSession->lang] . '</h2><br />';
echo '<h2 align="center">' . $MSG['co_msg_wait'][$sysSession->lang] . '</h2><br /></div>';
// echo '<h2 align="center">'.$MSG['msg_service'][$sysSession->lang].'</h2><br />';
echo str_pad("", 4096);
// 從PHP的緩衝區中釋放出來
ob_flush();
// 把不在緩衝區中的或者說是被釋放出來的資料發送到瀏覽器
    flush();
    if ($sysConn->GetOne('select count(*) from WM_term_course where course_id=' . intval($_POST['course_id'])))
    {
        $old_cid = intval($_POST['course_id']);
        $new_cid = $sysSession->course_id;

        $replace = intval($_POST['course_path_replace']) == 0 ? false: true;
        if(is_array($course_elements)) cloneCourse($old_cid, $new_cid, $replace);
        //printf('<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body><h2 align="center"><br /><br />%s</h2></body></html>', $MSG['co_msg_pack'][$sysSession->lang]);
    }
    echo '<h2 align="center">'.$MSG['co_msg_pack'][$sysSession->lang].'</h2><br />';
    showXHTML_body_E();
    echo <<<BOF
    <script type="text/javascript">
    //<![CDATA[
    document.getElementById("flushMsg").style.display = "none";alert("{$MSG['co_msg_pack'][$sysSession->lang]}");
    //]]>
    </script>
BOF;

//主程式(E)