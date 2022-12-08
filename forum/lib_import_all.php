<?php
    /**
     * 匯入文章函式庫
     *
     * 建立日期：2004/05/06
     * @author  KuoYang Tsao
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/file_api.php');
    require_once(sysDocumentRoot . '/lang/forum_io.php');
    require_once('System.php');

    // 版本 ID
    define('ImportVersion', '3.0');
    // 匯入資料種類
    define('ImportType', 'board');
    define('ImportPostType', 'post');
    $lang = Array(
            0=>'UTF-8',
            1=>'Big5',
            2=>'en',
            3=>'GB2312',
            4=>'EUC'
            );

    // 錯誤訊息對應表
    // s_ok(0) 成功
    // e_file (-1)~ e_miss_attach (-8) 為 initial() 之錯誤訊息
    //
    $import_err = Array(
            's_ok'            => 0,
            's_attach'        => 9,    // 此處為方便處理, 改為"正", 整批匯入不因夾檔而中斷
            's_quota_full'    =>15,

            'e_file'        =>-1,
            'e_xml_parse'    =>-2,
            'e_wrong_root'    =>-3,
            'e_wrong_ver'    =>-4,
            'e_wrong_type'    =>-5,
            'e_no_child'    =>-6,
            'e_miss_attach'    =>-8,
            'e_not_init'    =>-10,
            'e_save_attach'    =>-11,
            'e_db'            =>-12,
            'e_unknown_type'=>-13,
            'e_save_news'    =>-14
            );
    
    global $import_errmsg;
    $import_errmsg = Array(
             0    =>$MSG['msg_imp_err_0'][$sysSession->lang],
             9    =>$MSG['msg_imp_err_9'][$sysSession->lang],
            15    =>$MSG['msg_imp_err_15'][$sysSession->lang],

            -1    =>$MSG['msg_imp_err_1'][$sysSession->lang],
            -2    =>$MSG['msg_imp_err_2'][$sysSession->lang],
            -3    =>$MSG['msg_imp_err_3'][$sysSession->lang],
            -4    =>str_replace('%s' ,ImportVersion, $MSG['msg_imp_err_4'][$sysSession->lang]),
            -5    =>str_replace('%s' ,ImportType, $MSG['msg_imp_err_5'][$sysSession->lang]),
            -6    =>$MSG['msg_imp_err_6'][$sysSession->lang],
            -8    =>$MSG['msg_imp_err_8'][$sysSession->lang],
            -10    =>$MSG['msg_imp_err_10'][$sysSession->lang],
            -11    =>$MSG['msg_imp_err_11'][$sysSession->lang],
            -12    =>$MSG['msg_imp_err_12'][$sysSession->lang],
            -13    =>$MSG['msg_imp_err_13'][$sysSession->lang],
            -14    =>$MSG['msg_imp_err_14'][$sysSession->lang],
            );

    /**
     * q_mkdir()
     *     建立精華區資料夾
     * @pram string $board_id : 版號
     * @pram string $path : 父資料夾名稱
     * @pram string $dir : 要建立的資料夾名稱
     * @return bool : 成功 true/  失敗 false
     **/
    function q_mkdir($board_id, $path, $dir) {
        global $sysSession, $sysConn, $sysSiteNo, $MSG;

        $RS = dbGetStSr('WM_bbs_collecting','count(*) as total' ,"board_id='{$board_id}' and path='{$path}' and subject='{$dir}'", ADODB_FETCH_ASSOC);
        $err_code = 0;    // 0 為成功 , 其餘失敗
        $message = '';
        $extra = '';
        if($RS) {
            if($RS['total']>0) {
                $message = $MSG['folder'][$sysSession->lang] .' "' .$dir .'" ' .$MSG['already_existed'][$sysSession->lang];
                $err_code = 0;    // 若目錄已存在, 視為成功
            } else {
                $dir = mysql_escape_string($dir);
                $node = md5(uniqid(""));

                dbNew('WM_bbs_collecting',
                      'board_id,node,site,subject,picker,ctime,path,type',
                      "$board_id,'{$node}',$sysSiteNo,'$dir','$sysSession->username',now(),'$path','D'"
                     );
                if($sysConn->Affected_Rows()==0) {
                    $err_code = 3;
                    $message  = $MSG['db_busy'][$sysSession->lang];
                }
          }
        } else {
            $message = $MSG['query_fail'][$sysSession->lang].$MSG['try_later'][$sysSession->lang];
            $err_code = 2;
        }
        return Array($err_code, $message);
    }

    /*****************************
     * 單篇文章匯入類別(包含精華區)
     *****************************/
    class bbsPost {
        var $m_inited  = false;
        var $m_type    = '';
        var $filename  = '';
        var $m_post    = Array(
                'board_id'  =>0,
                'node_id'   =>0,
                'site'      =>0,
                'open_time' =>'0000-00-00',
                'close_time'=>'0000-00-00',
                'board_name'=>'',
                'poster'    =>'',
                'realname'  =>'',
                'email'     =>'',
                'homepage'  =>'',
                'subject'   =>'',
                'content'   =>'',
                'attach'    =>'',
                'lang'      =>'',
                'lang_name' =>''
                );
        var $m_saved     = false;
        var $last_errmsg = '';
        var $tmp_dir     = '';
        var $save_path   = '';

        /**
         * 設定資料( 此物件應最先被呼叫處 )
         * 請於外部先確認 $xml_file 存在
         * @param string $xml_file : 文章資料檔
         * @param string $baoard_id : 文章資料匯入之版號
         * @return int 成功 : >=0  失敗 : 負值 (詳見 $import_err 定義)
         */
        function initial($xml_file, $board_id=''){
            global $import_err,$sysSession;

            $this->m_inited = false;

            if(!$post_xml = domxml_open_file( $xml_file ))    return $import_err['e_xml_parse'];
            $root = $post_xml->document_element();
            if($root->tagname != 'data')    {
                // echo "<!-- tag name is not 'data'(={$root->tagname} -->\r\n";
                return $import_err['e_wrong_root'];
            }

            // Check Version and Type
            if($root->get_attribute('version') != ImportVersion )    return $import_err['e_wrong_ver'];
            if($root->get_attribute('type') != ImportPostType )    return $import_err['e_wrong_type'];
            if(count($root->child_nodes())==0)    return $import_err['e_no_child'];
            foreach($root->child_nodes() as $child) {
                $this->m_post[$child->tagname] = $child->get_content();
            }

            // 若未給版號 , 預設為目前所在討論版
            if(empty($board_id)) {
                $this->m_post['board_id'] = $sysSession->board_id;
                // echo "<!-- in bbsPost: board_id use default {$sysSession->board_id} -->\r\n";
            }
            else {
                // echo "<!-- in bbsPost: board_id use given {$board_id} -->\r\n";
                $this->m_post['board_id'] = $board_id;
            }

            // 取得種類( 一般區或討論版 )
            $this->filename = basename($xml_file);
            $this->m_type   = (substr($this->filename,0,1)=='q'?'quint':'board');

            // 檢驗夾檔
            $attach = trim($this->m_post['attach']);
            $old_attach = $attach;    // 查驗夾檔是否成功用
            // echo "<!-- attach = {$attach} -->\r\n";
            if($attach != '') {
                $attaches = explode(chr(9), $attach);
                if(count($attaches)%2 == 1)    { // 夾檔格式錯誤( 需成對 )
                    // return $import_err['s_attach'];
                    // echo "<!-- wrong attach pair -->\r\n";
                    $attach = '';
                }
                else {
                    // 檢查檔案是否存在
                    $this->attach_dir = dirname(dirname($xml_file)) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR .
                            $this->m_type . DIRECTORY_SEPARATOR . $this->m_post['node'];    // 取得資料夾位置
                    // echo "<!-- this->attach_dir : {$this->attach_dir} -->\r\n";
                    for($i=0; $i<count($attaches); $i+=2){
                        if( !is_file($this->attach_dir. DIRECTORY_SEPARATOR .$attaches[$i+1]) ) {
                            // echo "<!-- attach {$this->attach_dir}/" . $attaches[$i+1]. " -->\r\n";

                            unset($attaches[$i]);    // return $import_err['e_miss_attach'];
                            unset($attaches[$i+1]);
                        }
                    }
                    $attach = implode(Chr(9), $attaches);
                }
            }

            $this->m_post['attach'] = $attach;
            $this->m_inited = true;
            return ($attach==$old_attach?0:$import_err['s_attach']);    // 夾檔錯誤為可允許之錯誤
        }

        /********************
         * 儲存本匯入
         * 1.資料庫
         * 2.夾檔檔案
         * @param string $board_ownerid : 討論版 Owner ID ( 識別檔案存放路徑用 )
         * @param int &$quota_limit : Quota 限制 (=0 為不限)
         * @param int &$quota_used  : 已使用掉之 Quota
         * @return int 成功 : >=0  失敗 : 負值 (詳見 $import_err 定義)
         */
        function save($board_ownerid, &$quota_limit, &$quota_used, $isNews=false, $news_id='', $post_id='') {    // $this->m_type:'board'(一般區)  'quint'(精華區)
            global $sysConn, $sysSession, $sysSiteNo;
            if(!$this->m_inited) {
                // echo "<!-- Not Initialized -->\r\n";
                return $import_err['e_not_init'];
            }
            // echo "\r\n<!-- bbsPost->save (type={$this->m_type}):\r\n";

            $this->m_saved = false;

            $table_name = $this->m_type=='board'?'WM_bbs_posts':'WM_bbs_collecting';

            if ($post_id!='') {
                $nnode = $post_id;
                $this->m_post['node_id'] = $post_id;
            } else {
                // 取得目前板中最大的 node
                list($mnode) = dbGetStSr($table_name, 'MAX(node)', "board_id={$this->m_post['board_id']} and length(node) = 9", ADODB_FETCH_NUM);
                // 產生本篇的 node
                $nnode = empty($mnode)?'000000001':sprintf('%09d', $mnode+1);
                $this->m_post['node_id'] = $nnode;
            }

            
            // 加上 Quota 判斷
            if($quota_limit==0 || ($quota_limit > $quota_used)) {    // 判斷 quota
                if($this->m_post['attach']) {    // 有夾檔才需處理這一段
                    $base_path = get_attach_file_path($this->m_type, $board_ownerid, $this->m_post['board_id']);

                    $this->save_path = $base_path . DIRECTORY_SEPARATOR . $nnode;

                    // Make sure folder existed
                    if (!mkdirs($this->save_path)) return $import_err['e_save_attach'];
                    $attaches = explode(Chr(9), $this->m_post['attach']);

                    for($i=0;$i<count($attaches);$i+=2) {

                        $do_copy = false;
                        if($quota_limit==0)
                            $do_copy = true;
                        else {
                            $filesize = filesize($this->attach_dir.DIRECTORY_SEPARATOR.$attaches[$i+1]);
                            $filesize /= 1024;    // 換算為 KB
                            $do_copy = ($filesize<= ($quota_limit-$quota_used));
                        }
                        if($do_copy) {    // 需要複製檔案
                            $source = $this->attach_dir . DIRECTORY_SEPARATOR . $attaches[$i+1];
                            $target_file = uniqid('WM') . strrchr($attaches[$i+1], '.');
                            $target = $this->save_path  . DIRECTORY_SEPARATOR . $target_file;
                            if(copy( $source, $target ))    {    // 複製檔案成功
                                $attaches[$i+1] = $target_file;
                                // 更新 Quota used
                                $quota_used += $filesize;
                                // echo "\tcopy file from '{$source}' to '{$target}' success\r\n";
                            } else {                            // 複製檔案失敗
                                unset($attaches[$i]);
                                unset($attaches[$i+1]);
                                // echo "\tcopy file from '{$source}' to '{$target}' failed\r\n";
                            }
                        } else {                    // 不需要複製
                            unset($attaches[$i]);
                            unset($attaches[$i+1]);
                            // echo "\tDO NOT copy file from '{$source}' to '{$target}' failed\r\n";
                        }

                    }    // end of foreach

                    // 再組合字串回來
                    $this->m_post['attach'] = implode(Chr(9), $attaches);

                }

            } else {
                $this->m_post['attach'] = '';
            }
            
            // MIS#18184 輔英 - 討論版匯入功能問題 by Small 2010-09-23
            /*
            $username = mysql_escape_string($sysSession->username);
            $realname = mysql_escape_string($sysSession->realname);
            $email    = mysql_escape_string($sysSession->email);
            $homepage = mysql_escape_string($sysSession->homepage);
            */
            $username = mysql_escape_string($this->m_post['poster']);
            $realname = mysql_escape_string($this->m_post['realname']);
            $email    = mysql_escape_string($this->m_post['email']);
            $homepage = mysql_escape_string($this->m_post['homepage']);
            $subject  = mysql_escape_string($this->m_post['subject']);
            $content  = mysql_escape_string($this->m_post['content']);

            // 加入資料庫
            if($this->m_type=='board') {
                $fields = 'board_id,node,site,pt,poster,realname,email,homepage,subject,content,attach,lang';
                $values = "{$this->m_post['board_id']}, '$nnode',$sysSiteNo".
                          ", NOW(), '$username', '$realname ', ".
                          "'$email', '$homepage ', '$subject ', '$content ',".
                          ($this->m_post['attach']?"'{$this->m_post['attach']}'":'NULL') . ',' . $this->m_post['lang'];
            } else {
                $fields = 'board_id,node,site,path,pt,poster,picker,realname,email,homepage,subject,content,attach,lang';
                $path   = ($this->m_post['path']?$this->m_post['path']:'/');    // ($sysSession->q_path?$sysSession->q_path:'/');
                $values = "{$this->m_post['board_id']}, '$nnode',$sysSiteNo,'$path'".
                          ", NOW(), '$username', '$username', '$realname ', ".
                          "'$email', '$homepage ', '$subject ', '$content ',".
                          ($this->m_post['attach']?"'{$this->m_post['attach']}'":'NULL') . ',' . $this->m_post['lang'];
            }

            dbNew($table_name, $fields,    $values);
            
            if ($isNews && $this->m_type=='board')
            {
                dbNew('WM_news_posts', 'news_id,board_id,node', "{$news_id}, {$this->m_post['board_id']}, '{$nnode}'");
            }

            if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0){    // 若有錯誤

                if (file_exists($this->save_path)){
                    exec("rm -rf {$this->save_path}");                          // 清除資料夾
                }
                return $import_err['e_db'];
            }
            $this->m_saved = true;
            return 0;
        }
    }

    /**
     * 建立討論板
     * @param array  $bname : 討論版名稱(各語系)
     * @param array  $result: 版號及新知(news)節點號陣列
     * @return string 所取得的值
     **/
    function NewBoard($b_serialname, $title, $board_ownerid) {
        global $sysConn, $sysSession;
        list($cnt) = dbGetStSr('WM_bbs_boards', 'count(*) as cnt', '1', ADODB_FETCH_NUM);
        if ($cnt == 0) {
            $RS = dbNew('WM_bbs_boards', 'board_id', '1000000000');
        }

        // $boardName = serialize($bname);
        $result = Array('board_id'=>0, 'news_id'=>0);
        // 建立討論板
        $RS = dbNew('WM_bbs_boards', 'bname, title, owner_id', "'{$b_serialname}', '{$title}',{$board_ownerid}");
        if ($RS) {
            $board_id = $sysConn->Insert_ID();

            // 建立討論板存放夾檔的目錄
            $BoardPath =getOwnerDir($board_ownerid); // "/base/{$sysSession->school_id}/board/{$result['board_id']}";
            @mkdir($BoardPath, 0755);

            // 加入 WM_term_subject
            if(strlen($board_ownerid)==8) {    // Currently on for course bbs
                dbNew('WM_term_subject',
                      '`course_id`, `board_id`, `state`, `visibility`',
                      "'{$board_ownerid}', {$board_id}, 'open', 'visible'");

                $nid = $sysConn->Insert_ID();
                dbSet('WM_term_subject', "`permute`={$nid}", "node_id={$nid}");
            }
            return $board_id;
        } else {
            // echo "<!-- 建立討論版失敗 -->\r\n";
            return 0;
        }
    }

    /*
     *    取得Owner 所在目錄( 學校, 班級, 課程, 小組 )
     * @param int $owner_id : 討論板 owner_id , 若不給則抓 $sysSession->board_ownerid
     * @return string: 路徑
     */
    function getOwnerDir($owner_id='') {
        global $sysSession, $sysConn;
        if(empty($owner_id)) {
            if(empty($sysSession->board_ownerid))
                return -1;
            $owner_id = $sysSession->board_ownerid;
        }

        switch(strlen($owner_id)) {
            case 5:// 學校討論版
                return sysDocumentRoot . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR . $owner_id;
                break;

            case 7:// Class
            case 15:// Class Group
                return sysDocumentRoot . DIRECTORY_SEPARATOR . 'base'. DIRECTORY_SEPARATOR . $sysSession->school_id. DIRECTORY_SEPARATOR . 'class'. DIRECTORY_SEPARATOR . $sysSession->class_id;
                break;

            case 8:// Course
            case 16:// Course Group
                return sysDocumentRoot . DIRECTORY_SEPARATOR . 'base'. DIRECTORY_SEPARATOR . $sysSession->school_id . DIRECTORY_SEPARATOR . 'course'. DIRECTORY_SEPARATOR . $sysSession->course_id;
                break;
            default:
                return '';
        }
    }
?>
