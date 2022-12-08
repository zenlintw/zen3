<?php
    /**
     * 匯出文章函式庫
     *
     * 建立日期：2004/05/05
     * @author  KuoYang Tsao
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/forum.php');

    // 版本 ID
    define('ExportVersion', '3.0');
    // 匯出資料種類
    define('ExportType', 'post');
    $lang = Array(
            0=>'UTF-8',
            1=>'Big5',
            2=>'en',
            3=>'GB2312',
            4=>'EUC'
            );

    class bbsPost {
        var $m_getPost = false;
        var $filename  = '';
        var $m_post    = Array('board_id'  =>0,
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
                               'lang_name' =>'',
                               'path'      =>''
                               );
        var $m_getBoard = false;
        var $m_type     = 'board';    // 'board':一般區 , 'quint':精華區
        var $IsNews     = FALSE;    // 是否為具有啟用時間欄位之討論板文章

        /**
         * 建構子
         */
        function bbsPost($board_id,$node_id,$site,$board_name,$path='',$board_type='board'){
            global $sysConn,$lang;
            $this->m_type = $board_type;

            if($board_id && $node_id && $site) {
                $this->m_post['board_id'] = $board_id;
                $this->m_post['node_id'] = $node_id;
                $this->m_post['site'] = $site;
                $this->m_post['path'] = $path;
                if($board_name) {
                    $this->m_post['board_name'] = $board_name;
                    $this->m_getBoard = true;
                }

                list(
                    $this->m_post['poster'],
                    $this->m_post['realname'],
                    $this->m_post['email'],
                    $this->m_post['homepage'],
                    $this->m_post['subject'],
                    $this->m_post['content'],
                    $this->m_post['attach'],
                    $this->m_post['lang']) = ($board_type=='board'?
                    $rsBbsPosts = dbGetStSr('WM_bbs_posts',
                    'poster,realname,email,homepage,subject,content,attach,lang',
                    "board_id={$board_id} and site={$site} and node='{$node_id}'", ADODB_FETCH_NUM):
                    $rsBbsPosts = dbGetStSr('WM_bbs_collecting',
                    'poster,realname,email,homepage,subject,content,attach,lang',
                    "board_id={$board_id} and site={$site} and node='{$node_id}'", ADODB_FETCH_NUM));
                    
                $this->m_getPost = (count($rsBbsPosts) > 0);
                if($this->m_getPost) $this->m_post['lang_name'] = $lang[$this->m_post['lang']];
            }
        }

        function getNewsFields() {
            global $sysSession, $sysConn;
            if($this->m_getPost)    { // 要上述程序先完成
                $RS = dbGetStSr('WM_news_posts','open_time,close_time',"board_id={$this->m_post['board_id']} and node='{$this->m_post['node_id']}'", ADODB_FETCH_ASSOC);
                if(!$RS) return false;
                $this->m_post['open_time'] = $RS['open_time'];
                $this->m_post['close_time']= $RS['close_time'];
                return true;
            }
        }

        function exportXML(&$str_header,&$str) {
            global $lang;
            if(!$this->m_getPost) return false;

            //$str_header='Content-Disposition: attachment; filename="' . $$this->filename . '"\r\n';
            //$str_header='Content-Transfer-Encoding: binary\r\n';
            //$str_header='Content-Type: application/octet-stream; name="' . $$this->filename . '"\r\n';

            $this->filename = 'post.xml';
            $post = $this->m_post;

            $str = '<?xml version="1.0"?><data version="'. ExportVersion . '" time="'.Date('Y-m-d h:i:s',time()).'" type="'.ExportType.'" filename="'.$this->filename . '"></data>';
            $dom = domxml_open_mem($str);
            $root = $dom->document_element();
            foreach($post as $k=>$v) {
                $node = $dom->create_element($k);
                $text = $dom->create_text_node($v);
                $text = $node->append_child($text);
                $root->append_child($node);

            }
            $str = @$dom->dump_mem(true);
            return true;
        }

        function exportHTML(&$str) {
            global $MSG,$sysSession,$sysIndent,$uniqObjId,$_SERVER;
            if(!$this->m_getBoard) return false;


    include_once(sysDocumentRoot . '/lib/interface.php');
    $base_url = "http://{$_SERVER['HTTP_HOST']}:{$_SERVER['SERVER_PORT']}";
            ob_start();
    // 以下取自 read.php , 若 read.php 變更, 則此處也應變動相關部分
    // 開始呈現 HTML
    showXHTML_head_B("[".$this->m_post['subject']."]".$MSG['read'][$sysSession->lang]);
    showXHTML_CSS('include', $base_url."/theme/{$sysSession->theme}/{$sysSession->env}/wm.css"); //  此處要修改
    showXHTML_head_E();

    showXHTML_body_B('');
        showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" align="center"');
            showXHTML_tr_B();
                showXHTML_td_B('valign="top" class="cssTable"');
                    showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" class="cssTable"');

                        showXHTML_tr_B('class="cssTrOdd"');
                            showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['bname'][$sysSession->lang]);
                            showXHTML_td('width="640"', $this->m_post['board_name'].($this->m_type=='quint'?" - {$MSG['quint'][$sysSession->lang]}":''));
                        showXHTML_tr_E();

                    if($this->m_type=='quint') {
                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td('align="right" nowrap="nowrap" width="100"', 'PATH :');
                            showXHTML_td('width="640"', $this->m_post['path']);
                        showXHTML_tr_E('');
                    }

                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['posters'][$sysSession->lang]);
                            showXHTML_td('id="poster" width="640"', "<a href=\"mailto:{$this->m_post['email']}\" class=\"cssAnchor\">{$this->m_post['poster']}</a> ".($this->m_post['homepage']?("<a href=\"{$this->m_post['homepage']}\" target=\"_blank\">{$this->m_post['realname']} </a>"):"({$this->m_post['realname']} )"));
                        showXHTML_tr_E();
                        showXHTML_tr_B('class="cssTrOdd"');
                            showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['subjs'][$sysSession->lang]);
                            showXHTML_td('id="o_subject" width="640"',$this->m_post['subject']);
                        showXHTML_tr_E();

                        if($this->IsNews) {
                            // $NEWS = dbGetStSr('WM_news_posts','open_time,close_time',"board_id={$sysSession->board_id} and node='{$RS['node']}'");
// echo "<!-- board_id={$sysSession->board_id} and node='{$RS['node']}' ::: {$NEWS['open_time']}, {$NEWS['close_time']} -->\r\n";
                            $ot = $sysConn->UnixTimeStamp($this->m_post['open_time']);
                            $ct = $sysConn->UnixTimeStamp($this->m_post['close_time']);
                            $openT = empty($ot)?$MSG['unlimit'][$sysSession->lang]:$NEWS['open_time'];
                            $closeT= empty($ct)?$MSG['unlimit'][$sysSession->lang]:$NEWS['close_time'];

                            showXHTML_tr_B('class="cssTrEvn"');
                                showXHTML_td('align="right"', $MSG['start_time'][$sysSession->lang]);
                                showXHTML_td('id="o_open_time" width="640"',$openT);
                            showXHTML_tr_E();
                            showXHTML_tr_B('class="cssTrOdd"');
                                showXHTML_td('align="right" nowrap="nowrap"', $MSG['end_time'][$sysSession->lang]);
                                showXHTML_td('id="o_close_time" width="640"',$closeT);
                            showXHTML_tr_E();
                        }

                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['contents'][$sysSession->lang]);
                            showXHTML_td('width="640"','<table><tr><td id="o_content"><br />'.$this->m_post['content'].'<p /></td></tr></table>');
                        showXHTML_tr_E();
                        showXHTML_tr_B('class="cssTrOdd"');
                            showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['attach'][$sysSession->lang]);
                            showXHTML_td('width="640"', gen_attach_link( $this->m_post['attach'] ));
                        showXHTML_tr_E();
                    showXHTML_table_E();
                showXHTML_td_E();
            showXHTML_tr_E();
        showXHTML_table_E();

    showXHTML_body_E();
            $str = ob_get_contents();
            ob_end_clean();
            $this->filename = 'post.htm';
            return true;
        }
    }


    /* 將夾檔字串轉為 Link ( 修改自 /lib/file_api.txt 之 function generate_attach_link(...))
     * input : $attach 以 Tab 隔開的夾檔字串
     * return: 一串 Link (在列表時不分行，在單一POST裡會分行)
     */
    function gen_attach_link($attach){
        if (empty($attach)) return null;

        $a = explode(chr(9), trim($attach));
        $r = '';
        for($i=0; $i<count($a); $i+=2){
            $r .= '<a href="' .$a[$i+1] .'" target="_blank">'. $a[$i] .'</a><br />';
        }
        return $r;
    }

?>
