<?php
    /**************************************************************************************************
     *                                                                                                *
     *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
     *                                                                                                *
     *        Programmer: Wiseguy Liang                                                         *
     *        Creation  : 2003/04/28                                                            *
     *        work for  : save the answer that submit from examinee                             *
     *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
     *                                                                                                *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
    
    ignore_user_abort(true);
    set_time_limit(0);

    // 依據測驗編號取課程編號
    $examId = htmlspecialchars($_POST['exam_id']);
    $QTI_which = QTI_which;
    $cid = $sysConn->GetOne("select course_id from WM_qti_{$QTI_which}_test where exam_id = {$examId}");

    $sysSession->cur_func='1600400200';
    $sysSession->restore();
    if (!aclVerifyPermission(1600400200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }

    /**
     * 取得 ISO8601 格式之時間日期
     */
    function getISO8601_datetime($now=null){
        return (is_null($now))?date('Y-m-d\TH:i:s'):date('Y-m-d\TH:i:s', $now);
    }

    /**
     * 換掉節點中的值
     */
    function replace_node_content(&$node, $value)
    {
        if (method_exists($node, 'has_child_nodes'))
        {
            if ($node->has_child_nodes())
                foreach ($node->child_nodes() as $child)
                    $node->remove_child($child);

            $doc = $node->owner_document();
            $node->append_child($doc->create_text_node($value));
        }
    }

    // 程式開始
    if ($_POST['ticket'] != md5(sysTicketSeed . $_POST['exam_id'] . $_POST['time_id'])) {
        wmSysLog($sysSession->cur_func, $sysSession->course_id , $_POST['exam_id'] , $_POST['time_id'], 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket! '.$_POST['ticket'].' '.sysTicketSeed);
    }

    if (10000000 > dbGetOne('WM_qti_' . QTI_which . '_test', 'course_id', 'exam_id=' . $_POST['exam_id']))
        $save_path = sprintf(sysDocumentRoot . '/base/%05u/%s/A/%09u/%s/%03u/',
                               $sysSession->school_id,
                               QTI_which,
                               $_POST['exam_id'],
                               $sysSession->username,
                             $_POST['time_id']);
    else
        $save_path = sprintf(sysDocumentRoot . '/base/%05u/course/%08u/%s/A/%09u/%s/%03u/',
                               $sysSession->school_id,
                               $cid,
                               QTI_which,
                               $_POST['exam_id'],
                               $sysSession->username,
                             $_POST['time_id']);
    $fpath = sysDocumentRoot . "/base/{$sysSession->school_id}/board/exam_audio/";

    $where = sprintf('exam_id=%u and examinee="%s" and time_id=%u', $_POST['exam_id'], $sysSession->username, $_POST['time_id']);
    list($content) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'content', $where, ADODB_FETCH_NUM);
    // 如果放棄作答
    if ($_SERVER['argv'][0] == 'over')
    {
        if ($_SERVER['argv'][1] == '1') {
             // 教師試作又放棄...
            dbDel('WM_qti_' . QTI_which . '_result', $where);
            // 清除測驗的額外資訊
            if (QTI_which == 'exam') {
                dbDel('WM_qti_' . QTI_which . '_result_extra', $where);
            }
        } else {
            dbSet('WM_qti_' . QTI_which . '_result', 'status="submit",submit_time=now(),content=replace(content, \'</questestinterop>\', \'<wm:submit_status>over</wm:submit_status></questestinterop>\')', $where);
            wmSysLog($sysSession->cur_func, $cid , $_POST['exam_id'] , 0, 'auto', $_SERVER['PHP_SELF'], QTI_which . ' give up! Num of times:' . $_POST['time_id']);
        }
        die('<script type="text/javascript">parent.closeMyself();</script>');
    }

    // 切換視窗，強制交卷也要批改與計算成績，故拿掉die、以及紀錄狀態改為chgWin By Small 2012/02/03
    if ($_SERVER['argv'][0] == 'chgWin')
    {
        if ($_SERVER['argv'][1] == '1') {
            // 教師試作又放棄...
            dbDel('WM_qti_' . QTI_which . '_result', $where);
            // 清除測驗的額外資訊
            if (QTI_which == 'exam') {
                dbDel('WM_qti_' . QTI_which . '_result_extra', $where);
            }
        } else {
            dbSet('WM_qti_' . QTI_which . '_result', 'status="chgWin",submit_time=now(),content=replace(content, \'</questestinterop>\', \'<wm:submit_status>chgWin</wm:submit_status></questestinterop>\')', $where);
            wmSysLog($sysSession->cur_func, $cid , $_POST['exam_id'] , 0, 'auto', $_SERVER['PHP_SELF'], QTI_which . 'Change Window! Num of times:' . $_POST['time_id']);
        }
        // die('<script type="text/javascript">parent.closeMyself();</script>');
    }

    if(!$dom = domxml_open_mem($content)) {
        wmSysLog($sysSession->cur_func, $cid , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
        die('<script>alert("Error while parsing the document.");</script>');
    }
    $ctx = xpath_new_context($dom);
    $root = $dom->document_element();

    // 對每一題作處理
    if (is_array($_POST['ans']))
    {
        foreach($_POST['ans'] as $item_id => $items){
            $nodes = $ctx->xpath_eval("//item_result[@ident_ref='$item_id']");
            if (is_null($nodes->nodeset[0])) die('<script>alert("No answer in this question.");</script>');
            // 存入解答時間
            $cur = $nodes->nodeset[0];
            $one = $cur->append_child($dom->create_element('date'));
            $two = $one->append_child($dom->create_element('type_label'));
            $two->append_child($dom->create_text_node('submit answer'));
            $two = $one->append_child($dom->create_element('datetime'));
            $two->append_child($dom->create_text_node(getISO8601_datetime()));

            $nodes = $ctx->xpath_eval("//item_result[@ident_ref='$item_id']/response[@ident_ref='$response_id']");
            $num = 0;
            $ret3 = $ctx->xpath_eval("//item[@ident='$item_id']/resprocessing/respcondition[1]/conditionvar/varequal");
            if (is_array($ret3->nodeset)) {
                $num = count($ret3->nodeset);
            }
            
            // 對每題的每個答案存入
            foreach($items as $response_id => $responses){
                $nodes = $ctx->xpath_eval("//item_result[@ident_ref='$item_id']/response[@ident_ref='$response_id']/response_value");
                if (is_null($nodes->nodeset[0])) die('<script>alert("Save failure."); parent.doFunc(2);</script>');
                $parent = $nodes->nodeset[0]->parent_node();
                // if ($parent->get_attribute('ident_ref') != $response_id) $parent->set_attribute('ident_ref', $response_id);

                // 把次數加一
                $prev_n = $ctx->xpath_eval('./num_attempts', $parent);
                if (is_object($prev_n->nodeset[0]))
                {
                    $o = $prev_n->nodeset[0];
                    $i = intval($o->get_content()) + 1;
                    replace_node_content($o, $i);
                }

                // 處理單選重複出現response問題 
                $response_form = $parent->first_child();
                if ($response_form->get_attribute('render_type') == 'choice' || $response_form->get_attribute('render_type') == 'extension' || ($response_form->get_attribute('render_type') == 'fib' && ($num == 1 || $response_form->get_attribute('cardinality') == 'Ordered'))) {
                    $old = $parent->parent_node()->get_elements_by_tagname('response');
                    if(count($old)>1) { 
                        foreach($old as $key => $val){
                            if($key>0){
                                $parent->parent_node()->remove_child($val);
                            }
                        }
                        $nodes = $ctx->xpath_eval("//item_result[@ident_ref='$item_id']/response[@ident_ref='$response_id']/response_value");
                    }
                }
                
                
                // 把前一次答案去掉
                if (count($nodes->nodeset) > 1)
                    foreach (array_slice($nodes->nodeset, 1) as $previous_ans)
                        $parent->remove_child($previous_ans);

                // 存本次答案
                if (is_array($responses)){
                    $response_form = $parent->first_child();
                    if ($response_form->get_attribute('cardinality') == 'Multiple' &&
                        $response_form->get_attribute('render_type') == 'choice')
                        sort($responses);
                    elseif($response_form->get_attribute('cardinality') == 'Multiple' &&
                           $response_form->get_attribute('render_type') == 'extension')
                        ksort($responses);
                    $i = 0; $f = 0; $saved = false;
                    exec("mkdir -p '$save_path' 2>/dev/null");
                    foreach($responses as $label => $piece){
                        $piece = removenonprintable($piece);
                        if ($response_id == 'WM01')
                        {
                            $piece = '';
                            if ($label == 'REC01')
                            {
                                if (($file = basename(trim($_POST['mp3s'][$item_id][$i]))) != '')
                                {
                                    $source = realpath($fpath . $file);
                                    if (@is_file($source))
                                    {
                                        if (!is_dir($save_path . $label)) mkdir($save_path . $label);
                                        rename($source, $save_path . $label . '/' . $file) AND
                                        ($piece = 'file://' . $label . '/' . $file) AND
                                        ($saved = true);
                                    }
                                }
                            }
                            elseif ($label == 'FILE01')
                            {
                                if (is_uploaded_file($_FILES['uploads']['tmp_name'][$item_id][$f]))
                                {
                                    if (!is_dir($save_path . $label)) mkdir($save_path . $label);
                                    move_uploaded_file($_FILES['uploads']['tmp_name'][$item_id][$f], $save_path . $label . '/' . un_adjust_char($_FILES['uploads']['name'][$item_id][$f])) AND
                                    ($piece = 'file://' . $label . '/' . $_FILES['uploads']['name'][$item_id][$f]) AND
                                    ($saved = true);
                                    $f++;
                                }
                            }
                        }

                        if($i)
                        {
                            $one = $parent->append_child($dom->create_element('response_value'));
                            $one->append_child($dom->create_text_node(htmlspecialchars(stripslashes(trim($piece)))));
                        }
                        else
                        {
                            replace_node_content($nodes->nodeset[0], htmlspecialchars(stripslashes(trim($piece))));
                        }
                        $i++;
                    }
                    if (!$saved) @rmdir($save_path);
                }
                else{
                    replace_node_content($nodes->nodeset[0], htmlspecialchars(stripslashes(trim($_POST['ans'][$item_id][$response_id]))));
                }
            }
        }
    }

    if (($_SERVER['argv'][0] != '') && ($_SERVER['argv'][0] != 'prepage'))
    {
        if ($_SERVER['argv'][1] == '1') // 教師試作
        {
            dbDel('WM_qti_' . QTI_which . '_result', $where);
            // 清除測驗的額外資訊
            if (QTI_which == 'exam') {
                dbDel('WM_qti_' . QTI_which . '_result_extra', $where);
            }
            die();
            die('<script type="text/javascript">parent.closeMyself();</script>');
        }
        $content = str_replace('</questestinterop>', '<wm:submit_status>' . $_SERVER['argv'][0] . '</wm:submit_status></questestinterop>', $dom->dump_mem());

        // 切換視窗強制交卷的狀態 By Small 2012/02/03
        $exam_status = ($_SERVER['argv'][0]=='chgWin')? 'chgWin' : 'submit';

        // dbSet('WM_qti_' . QTI_which . '_result', "status='submit',submit_time=now(),content='" . mysql_escape_string($content) . "'",
        dbSet('WM_qti_' . QTI_which . '_result', "status='{$exam_status}',submit_time=now(),content='" . mysql_escape_string($content) . "'",
              $where);
        wmSysLog($sysSession->cur_func, $cid , $_POST['exam_id'] , 0, 'auto', $_SERVER['PHP_SELF'], QTI_which . ' time is up('.$_SERVER['argv'][0].')! Num of times:' . $_POST['time_id']);
    
                // 增加答案LOG與EMAIL通知機制
                // 以追蹤QTI 答案XML格式錯誤的可能原因
                // 只記錄讀出後PARSE失敗的
                // log_qti_answer(QTI_which, $sysSession->school_id, $cid, htmlspecialchars($_POST['exam_id']), $sysSession->username, $content, mysql_escape_string($content));

        if ($_SERVER['argv'][0] == 'mark')
        {
            die('<script type="text/javascript">parent.doFunc(parent.total_page == parent.cur_page ? 3 : 2);</script>');
        }
        elseif ($_SERVER['argv'][0]=='chgWin')
        {
            // 切換視窗強制交卷視同繳交 => 仍須計算成績 by Small 2012/02/03
            die('<script type="text/javascript">parent.doFunc(-1);</script>');
        }
        elseif ($_SERVER['argv'][0] == 'timeout')
        {
            // 逾時自動繳交視同使用者自行繳交 => 仍須計算成績
            die('<script type="text/javascript">parent.doFunc(-1);</script>');
            // die('<script type="text/javascript">parent.closeMyself();</script>');
        }
    }
    else
    {
        dbSet('WM_qti_' . QTI_which . '_result', "content='" . mysql_escape_string($dom->dump_mem()) . "'",
              "exam_id={$_POST['exam_id']} and examinee='{$sysSession->username}' and time_id={$_POST['time_id']}");
    
                // 增加答案LOG與EMAIL通知機制
                // 以追蹤QTI 答案XML格式錯誤的可能原因
                // 只記錄讀出後PARSE失敗的
                // log_qti_answer(QTI_which, $sysSession->school_id, $cid, htmlspecialchars($_POST['exam_id']), $sysSession->username, $dom->dump_mem(), mysql_escape_string($dom->dump_mem()));
    
        if ($_SERVER['argv'][0] == '') {
            die('<script type="text/javascript">parent.doFunc(parent.total_page == parent.cur_page ? 3 : 2);</script>');
        }
    }

?>
