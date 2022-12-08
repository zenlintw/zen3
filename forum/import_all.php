<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/forum/lib_import_all.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/lib/archive_api.php');
    require_once(sysDocumentRoot . '/lib/quota.php');

    $sysSession->cur_func = '900100800';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    if($_POST['import_id']!=''){        
        $board_id = $_POST['import_id'];
        $sysSession->q_right=1;
        $sysSession->b_right         = $sysSession->q_right; // 目前兩者一樣
        $ticket=md5(sysTicketSeed . 'Board' . $_COOKIE['idx']);
    }else{
        $board_id = $sysSession->board_id;
        $ticket = md5(sysTicketSeed . 'Board' . $_COOKIE['idx'] .$board_id);
    }
    if( ($ticket != $_POST['ticket']) || !$sysSession->b_right ) {
        wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '拒絕存取!');
        die('Access Deny');
    }

    function do_import($board='board')
    {
        global $import_errmsg;
        global $sysSession, $MSG, $board_dom, $tem_path, $board_id, $TOTAL_ERROR, $post_o, $q_folders, $quota_limit, $quota_used, $import_errmsg;
        
        list($news_id) = dbGetStSr('WM_news_subject', 'news_id', 'board_id='.$board_id.' and type="news"', ADODB_FETCH_NUM);    // 判斷是否為最新消息版
        $isNews = empty($news_id) ? false : true;
        
        $exp   = ($board == 'board') ? '/data/board/node' : '/data/quint/node[@type="F"]';
        $ctx   = xpath_new_context($board_dom);
        $posts = $ctx->xpath_eval($exp);
        if (is_array($posts->nodeset)) {
            foreach ($posts->nodeset as $post )
            {
                $post_id   = $post->get_attribute('id');
                $post_file = $post->get_attribute('data');
    
                // 資料檔位置
                $xml_file  = $tem_path . DIRECTORY_SEPARATOR . $board . DIRECTORY_SEPARATOR . $post_file;
                $post_o    = new bbsPost;
                $ret       = $post_o->initial($xml_file, $board_id);
                if ($ret < 0)
                {
                    $TOTAL_ERROR[$board][] = $MSG[$board][$sysSession->lang] . $MSG['post'][$sysSession->lang] . ' ' . $post_id . ':' . $import_errmsg[$ret];
                }
                else
                {
                    if ($ret > 0)    // 發生允許之錯誤(如:夾檔存放失敗), 一樣要存入資料庫, 但需回報有錯誤
                    {
                        $TOTAL_ERROR[$board][] = $MSG[$board][$sysSession->lang] . $MSG['post'][$sysSession->lang] . ' ' . $post_id . ':' . $import_errmsg[$ret];
                    }
                    if ($board == 'quint')
                    {
                        //  檢查將存入之路徑是否存在, 若不存在則設為根目錄
                        if (!in_array($post_o->m_post['path'], $q_folders))
                        {
                            $post_o->m_post['path'] = '/';
                            $TOTAL_ERROR['quint'][] = $MSG['quint'][$sysSession->lang] . $MSG['post'][$sysSession->lang] . ' ' . $post_id . ':' .
                                sprintf($MSG['msg_folder_notexist'][$sysSession->lang], $post_o->m_post['path'] , '/');
                        }
                    }
                    $ret = $post_o->save($sysSession->board_ownerid , $quota_limit, $quota_used, $isNews, $news_id);
                    if ($ret != 0) // 錯誤
                    {
                        $TOTAL_ERROR[$board][] = $MSG[$board][$sysSession->lang] . $MSG['post'][$sysSession->lang] . ' ' . $post_id . $import_errmsg[$ret];
                    }
                }
            }
        }
    }

    $D_R = DIRECTORY_SEPARATOR;

    // 1.解開壓縮檔
    $up_file     = $_FILES['file_import']['tmp_name'];
    $up_filename = basename($up_file);
    $up_dir      = dirname($up_file);

    // 處理檔案的暫存路徑 ( 系統暫存路徑下 )
    $tem_path = sysTempPath . $D_R . uniqid('BBS');
    mkdir($tem_path);
    $import_zip = new Archive();
    $import_zip->extract_it($up_file, $tem_path, '.zip');
    
    if (!is_file($tem_path . $D_R . 'list.xml'))
    {
        wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], $MSG['no_import_data'][$sysSession->lang] . $up_file);
        die( sprintf($MSG['no_import_data'][$sysSession->lang], 'list.xml') );
    }

    // 2.載入清單 list.xml
    $board_dom = domxml_open_file( $tem_path . $D_R . 'list.xml');
    if(!$board_dom) {
        wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], 'domxml open error!');
        die($import_errmsg[$import_err['e_xml_parse']]);
    }

    $board_root = $board_dom->document_element();
    if(count($board_root->child_nodes())==0)    return $import_err['e_no_child'];
    $board_data = Array();
    foreach($board_root->child_nodes() as $v) {
        if (!method_exists($v, 'tagname')) continue;
        $tagname = $v->tagname();
        if($tagname!= 'board' && $tagname != 'quint')
            $board_data[$tagname] = getNodeValue($board_root, $tagname);
    }

    // 3.討論版資料
    //   決定要放置的版號
    if($_POST['import_choice']=='new') { // 需匯入至新討論版

        $bname = getCaption($board_data['bname']);
        $board_name = $bname[$sysSession->lang];

        $board_id = NewBoard($board_data['bname'], $board_data['title'], $sysSession->board_ownerid );
        if(!$board_id) die( sprintf($MSG['msg_create_board'][$sysSession->lang], $board_name) );

    } else {
        
            if($_POST['import_id']!=''){        
                $board_name = dbGetOne('WM_bbs_boards','bname','board_id="'.$board_id.'"');
                $arr=getCaption($board_name);
                $board_name=$arr[$sysSession->lang];
                
            }else{
                $board_name = $sysSession->board_name;
            }
            
    }
    
    // echo "<!-- =========== BOARD ID = {$board_id} ============ -->\r\n";

    // 3.1 取得 Quota 設定
    $quota_limit = 0;
    $quota_used  = 0;
    getQuota($board_id, $quota_used, $quota_limit);

    $TOTAL_ERROR = Array(
                'board'=>Array(),
                'quint'=>Array()
                );

    // 4.0 匯入一般區文章
    // echo "<!-- =========== Copy BOARD START ============ -->\r\n";
    if (!$sysSession->board_qonly) // 如果此版不是只有精華區形式的話, 才匯入一般區文章
        do_import('board');
    // echo "<!-- =========== Copy BOARD STOP ============ -->\r\n";

    // 4.1 建立精華區目錄 ( 並把目前所有目錄記錄下來 )
    // echo "<!-- =========== Create QUINT FOLDER START ============ -->\r\n";
    $q_folders = Array('/');    // 紀錄所有的精華區目錄之陣列 ( 因為 / 不在資料中, 故先加入 )

    $qd = dbGetStMr('WM_bbs_collecting','path,subject',"board_id='{$board_id}' and type='D'", ADODB_FETCH_ASSOC);
    while(!$qd->EOF) {
        $q_folders[] = ($qd->fields['path']=='/'?'':$qd->fields['path']). '/' . $qd->fields['subject'];
        $qd->MoveNext();
    }

    $ctx = xpath_new_context($board_dom);
    $qd = $ctx->xpath_eval("/data/quint/node[@type='D']");
    
    if (is_array($qd->nodeset)) {
        foreach($qd->nodeset as $qd_item) {
            $q_path =  getNodeValue($qd_item, 'path');
            $q_dir  =  getNodeValue($qd_item, 'subject');
            $q_fullpath = ($q_path=='/'?'':$q_path).'/'.$q_dir;
            
            if( !in_array($q_fullpath, $q_folders) )
            {
                $res = q_mkdir($board_id, $q_path, $q_dir);
                if($res[0] == 0)     // 建立精華區目錄成功
                    $q_folders[] = $q_fullpath;
                else
                    $TOTAL_ERROR['quint'][] = sprintf( $MSG['msg_create_qfolder'][$sysSession->lang] , $q_fullpath );
            }
        }
    }

    // echo "<!-- =========== Create QUINT FOLDER STOP ============ -->\r\n";

    // 4.2 匯入精華區文章
    // echo "<!-- =========== Copy QUINT START ============ -->\r\n";
    do_import('quint');
    // echo "<!-- =========== Copy QUINT STOP ============ -->\r\n";

    // 5 更新 Quota
    getCalQuota($board_id, $new_quota_used, $new_quota_limit);        // 檔案處理後該文章節點所用掉空間
    if($new_quota_used != $quota_used)    // 看是否要更新 Quota 資訊
        setQuota($board_id,$new_quota_used);

    // 6 清除暫存空間
    if (file_exists($tem_path)) {
        exec("rm -rf {$tem_path}");                         // 清除資料夾
    }

    // 7 顯示結果
    showXHTML_head_B('');
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_script('inline', $js);
    // Bug#1489：點選『進入此討論板』無反應 by Small 2006/10/31
    showXHTML_script('inline','
    function goBoard(val) {
        if ((typeof(parent.s_sysbar) == "object") && (typeof(parent.s_sysbar.goBoard) == "function")) {
            parent.s_sysbar.goBoard(val);
        }
        if ((typeof(parent.c_sysbar) == "object") && (typeof(parent.c_sysbar.goBoard) == "function")) {
            parent.c_sysbar.goBoard(val);
        }
    }

    function co_goBoard(val) {
        obj = document.getElementById("goBd");
        obj.xbid.value = val;
        obj.submit();
    }
    ');
    showXHTML_head_E();
    showXHTML_body_B();
        $ary = array();
        $ary[] = array($MSG['import_all'][$sysSession->lang], 'tabs1');
        showXHTML_tabFrame_B($ary, 1);
            showXHTML_table_B('width="500" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
                showXHTML_tr_B('class="cssTrHead"');
                if($_POST['import_choice']=='new') { // 匯入至新討論版
                    showXHTML_td_B('');
                    $title = sprintf($MSG['import_all_success'][$sysSession->lang], $board_name );
                    echo "$title<br/>";
                    showXHTML_input('button', '', $MSG['goto_import_board'][$sysSession->lang], '', 'class="cssBtn" onclick="co_goBoard(\'' . sysEncode($board_id) .'\')"');
                    showXHTML_td_E('');
                } else {    // 匯入目前討論版
                    showXHTML_td('', $MSG['import_all'][$sysSession->lang] .'"'. $board_name .'"'. $MSG['finished'][$sysSession->lang]);
                }
                showXHTML_tr_E();

            // 討論版一般區(精華區)匯入細節
            
            $Err_Type = Array('board', 'quint');
            foreach($Err_Type as $errType)
            {
                if($errType=='quint'){continue;}
                showXHTML_tr_B('class="cssTrHead"');
                    showXHTML_td('', $MSG[$errType][$sysSession->lang] );
                showXHTML_tr_E();

                $bcount = count($TOTAL_ERROR[$errType]);
                $bmsg = '';
                if($bcount==0) {
                    $bmsg = $MSG['msg_imp_err_0'][$sysSession->lang];
                    $col = 'class="cssTrEvn"';
                    showXHTML_tr_B($col);
                        showXHTML_td('', $bmsg );
                    showXHTML_tr_E();
                }
                else {
                    $col = 'class="cssTrOdd"';
                    for($i=0;$i<$bcount;$i++) {
                        $bmsg .= $TOTAL_ERROR[$errType][$i]."<br>\r\n";
                        $col = ($col=='class="cssTrOdd"'?'class="cssTrEvn"':'class="cssTrOdd"');
                        showXHTML_tr_B($col);
                            showXHTML_td('', $bmsg );
                        showXHTML_tr_E();
                    }
                }
            }

                // 離開按鈕
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td_B('colspan="3" align="center"');
                        if($_POST['import_id']!=''){        
                            showXHTML_input('button', '', $MSG['backto_list'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\''.('/teach/course/cour_subject.php').'\')"');
                        }else{
                            showXHTML_input('button', '', $MSG['backto_list'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\''.($sysSession->board_qonly?'index.php':'index.php?').'\')"');
                        }
                showXHTML_td_E();
                showXHTML_tr_E();
            showXHTML_table_E();
        showXHTML_tabFrame_E();
        showXHTML_form_B('action="/forum/m_node_list.php" method="post" enctype="multipart/form-data" style="display:none"', 'goBd');
            showXHTML_input('hidden', 'cid', $sysSession->course_id, '', '');
            showXHTML_input('hidden', 'xbid', '', '', '');
        showXHTML_form_E();
    showXHTML_body_E();
?>
