<?php
/**
 * 提供與討論版相關的函數
 *
 * 建立日期：2014/10/17
 * @author spring
 *
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once(sysDocumentRoot . '/lang/mooc.php');
require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
require_once(sysDocumentRoot . '/lib/lib_forum.php');
require_once(sysDocumentRoot . '/lib/lib_acade_news.php');

class forum
{
    /************************************************
     *       是否另建一個 php 處理討論版資料          *
     ************************************************/
    /**
     *
     * 取得討論版內容
     *
     * @param string/ array $bid 討論版版號
     * @param string $curPage 第幾頁
     * @param string $perPage 每頁幾筆
     * @param string $keyword 關鍵字
     * @param string $sort 排序欄位
     * @param string $order 升冪、降冪
     */
    function getBbsPosts($bid, $nid = array(), $onlyTopic = '0', $curPage = 1, $perPage = 10, $keyword = '',
        $sort = '', $order = '')
    {
        global $sysSession, $MSG, $sysConn;

        if (!(is_array($bid))) {
            // 補救開板時，owner_id=0的問題，只針對單一討論板
            $oriOwner_id = dbGetOne('WM_bbs_boards','owner_id',sprintf('board_id=%d',$bid));
            if ($oriOwner_id == 0) {
                $newOwner_id = intval(dbGetOne('WM_term_subject','course_id',sprintf('board_id=%d',$bid)));
                if ($newOwner_id > 0) {
                    dbSet('WM_bbs_boards',sprintf('owner_id=%d',$newOwner_id),sprintf('board_id=%d',$bid));
                }
            }
            $bid = array($bid);
        }
        $boardId = implode(',', $bid);

        $where = sprintf('`WM_bbs_posts`.`board_id` in (%s)', $boardId);

        if (!(is_array($nid))) {
            $nid = array($nid);
        }
        $tmpNid = $nid;
        $nid = array();
        foreach ($tmpNid as $k => $v) {
            $nid[] = sprintf("'%s'", $v);
        }
        $nodeId = implode(',', $nid);
        if (count($nid) >= 1) {
            $where .= sprintf("AND CONCAT(`WM_bbs_posts`.`board_id`, '|', `WM_bbs_posts`.`node`) in (%s) ", $nodeId);
        }

        if ($onlyTopic === '1') {
            $where .= sprintf("AND LENGTH(`WM_bbs_posts`.`node`) = 9 ");
        }

        $subWhere = ''; //分頁

        // 關鍵字搜尋
        if ($keyword !== '') {
            $where .= sprintf(
                'AND (`WM_bbs_posts`.`poster` LIKE \'%%%s%%\' OR `WM_bbs_posts`.`subject` LIKE \'%%%s%%\' OR `WM_bbs_posts`.`content` LIKE \'%%%s%%\') ',
                $keyword,
                $keyword,
                $keyword
            );
        }

        // 排序 (預備用)
        if ($sort !== '') {
            $subWhere .= sprintf('ORDER BY %s %s ', $sort, $order);
        }

        // 最新消息有設定文章起迄
        // 取最新消息版號
        dbGetNewsBoard($newsData);
        $newBid = $newsData['board_id'];
        if ($newBid === $boardId) {
        $where .= "
            AND concat(`WM_bbs_posts`.`board_id`, `WM_bbs_posts`.`node`) IN
                   (SELECT concat(board_id, node)
                    FROM WM_news_posts
                    WHERE ((NOW() >= open_time
                            AND NOW() < close_time)
                           OR (NOW() >= open_time
                               AND close_time IS NULL)
                           OR (NOW() < close_time
                               AND open_time IS NULL)
                           OR (NOW() >= open_time
                               AND close_time = '0000-00-00 00:00:00')
                           OR (NOW() < close_time
                               AND open_time = '0000-00-00 00:00:00')
                           OR (open_time IS NULL
                               AND close_time IS NULL)
                           OR (open_time = '0000-00-00 00:00:00'
                               AND close_time = '0000-00-00 00:00:00')))";
        }

        // 校正起始頁
        if ($curPage <= 0) {
            $curPage = 1;
        }

        // 取得筆數（沒有指定NODEID才使用LIMIT）
        if (count($nid) === 0) {
            $subWhere .= 'LIMIT ' . ($curPage-1) * $perPage . ',' . $perPage . ' ';
        }

        // 取得筆數
        $ct = dbGetOne('`WM_bbs_posts`', 'count(`WM_bbs_posts`.`board_id`)', $where, ADODB_FETCH_ASSOC);

        // 取得討論版內容
        $rs = dbGetStMr(
            '`WM_bbs_posts` left join `WM_bbs_boards` on `WM_bbs_boards`.`board_id` = `WM_bbs_posts`.board_id',
            'SQL_CALC_FOUND_ROWS `WM_bbs_posts`.board_id, `WM_bbs_posts`.`node`, `WM_bbs_posts`.site, `WM_bbs_posts`.pt, WM_bbs_posts.poster,
                `WM_bbs_posts`.realname, `WM_bbs_posts`.email, `WM_bbs_posts`.subject, `WM_bbs_posts`.content,
                `WM_bbs_posts`.attach, `WM_bbs_posts`.rcount, `WM_bbs_posts`.rank, `WM_bbs_posts`.hit, `WM_bbs_posts`.lang,
                `WM_bbs_boards`.owner_id, `WM_bbs_posts`.attach',
            $where . $subWhere,
            ADODB_FETCH_ASSOC
        );

        // 改為抓取全部資料取需要的回傳 (cnt 及資料一同撈)
        if ($ct > 0) {
            $postList['total'] = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));
            $postList['code'] = 0;
            $postList['total_rows'] = $ct;// 全部筆數
            $postList['limit_rows'] = $perPage;// 一頁幾筆
            $postList['current_page'] = (String)$curPage;// 目前第幾頁
            $postList['editEnable'] = $sysSession->q_right;
            $nodeIdList = array();

            $parseurl = parse_url($_SERVER['SCRIPT_URI'] );
            $host = $parseurl['scheme'] . '://' . $parseurl['host'];

            $i = 1;
            while (!$rs->EOF) {
                $postList['data'][$rs->fields['board_id'] . '|' . $rs->fields['node']] = array(
                    'boardid'       => $rs->fields['board_id'],
                    'encbid'        => sysEncode($rs->fields['board_id']),
                    'node'          => $rs->fields['node'],
                    'encnid'        => sysEncode($rs->fields['node']),
                    'cid'           => $rs->fields['owner_id'],
                    'enccid'        => sysEncode($rs->fields['owner_id']),
                    'poster'        => $rs->fields['poster'],
                    'realname'      => $rs->fields['realname'],
                    'cpic'          => base64_encode(urlencode($rs->fields['poster'])),
                    'subject'       => $rs->fields['subject'],
                    'postdate'      => substr($rs->fields['pt'], 0, 16),
                    'postdatelen'   => $this->getLengthOfTime($rs->fields['pt']),
                    'postcontent'   => $rs->fields['content'],//內文需防止xss
                    'postcontenttext' => strip_tags($rs->fields['content']),//內文需防止xss
                    'hit'           => $rs->fields['hit'],
                    'qrcode_url'    => getQrcodePath($host . '/forum/m_node_chain.php?cid=' . $rs->fields['owner_id'] . '&bid=' . $rs->fields['board_id'] .  '&nid=' . $rs->fields['node'], '1', 'L', 9),
                    'floor'         => (($curPage-1) * $perPage) + $i,
                    'attach'        => $rs->fields['attach'],
                );

                // 公告的附檔 ("/base/10001/course/10000155/board/1000000469/000000002/WM543e24457300b.png") 名稱、連結、大小
                if ($sysSession->course_id === "0" || $sysSession->course_id === null || $rs->fields['owner_id']==10001) { // 公告的最新消息會沒有cid
                    $announcement_file_path = sprintf ( sysDocumentRoot . '/base/%05d/%s/%10d/%s/', $sysSession->school_id, 'board', $rs->fields ['board_id'], $rs->fields ['node'] );
                } else {
                    $announcement_file_path = sprintf ( sysDocumentRoot . '/base/%05d/course/%08d/%s/%10d/%s/', $sysSession->school_id, $sysSession->course_id, 'board', $rs->fields ['board_id'], $rs->fields ['node'] );
                }

                // 檢視單一討論區時，才取附件資訊
                if (is_array($bid) && count($bid) === 1) {
                    $postList['data'][$rs->fields['board_id'] . '|' . $rs->fields['node']]['postfilelink'] =
                        $this->getFileLink($rs->fields['attach'], $announcement_file_path);
                    $postList['data'][$rs->fields['board_id'] . '|' . $rs->fields['node']]['attachment'] = $rs->fields['attach'];
                }

                // 增加點閱數
                // dbSet('LOW_PRIORITY WM_bbs_posts', 'hit=hit+1', "board_id={$bid} and node='{$rs->fields['node']}' and site={$rs->fields['site']} limit 1");

                // 寫下閱讀紀錄
                /* 這幾篇標記已讀
                $readcnt = dbNew('WM_bbs_readed','type,board_id,node,username,read_time',"'b', {$bid},'{$rs->fields['node']}','{$sysSession->username}',Now()");
                if($readcnt == 0) {
                    dbSet('LOW_PRIORITY WM_bbs_readed', 'read_time=Now()', "type='b' and board_id={$bid} and node='{$rs->fields['node']}' and username='{$sysSession->username}'");
                }
                 *
                 */

                // 如果有編輯權限的話 (q_right == 1) ，給予 node site 的值，到前端 編輯刪除使用
                $postList['data'][$rs->fields['board_id'] . '|' . $rs->fields['node']]['n'] = $rs->fields['node'];
                $postList['data'][$rs->fields['board_id'] . '|' . $rs->fields['node']]['s'] = $rs->fields['site'];

                // 設定文章節點陣列，供按讚數使用
                $nodeIdList[] = array('bid' => $rs->fields['board_id'], 'nid' => $rs->fields['node']);

                $i++;
                $rs->MoveNext();
            }

            // 取登入者讀取數
            if ($onlyTopic === '1') {
                $read = $this->getTopicSummaryByNid($nodeIdList, $sysSession->username);
                foreach ($read as $k => $v) {
                    $postList['data'][$k]['readflag'] = ($read[$k]['read_cnt'] - $read[$k]['article_cnt'] <= -1) ? 0 : 1;

                    // 如果回應都有讀取，增加判斷留言有沒有讀取
                    if ($postList['data'][$k]['readflag'] === 1) {

                        // 最後留言時間
                        $lastWhispersTime = dbGetOne(
                            'WM_bbs_whispers',
                            'MAX(create_time) create_time',
                            'board_id = ' . $postList['data'][$k]['boardid'] . ' AND substr(`node`, 1, 9) = ' . $postList['data'][$k]['node'],
                            ADODB_FETCH_ASSOC
                        );

                        // 最後讀取時間
                        $lastReadTime = dbGetOne(
                            'WM_bbs_readed',
                            'MAX(read_time) read_time',
                            'username = "' . $sysSession->username . '" AND board_id = ' . $postList['data'][$k]['boardid'] . ' AND substr(`node`, 1, 9) = ' . $postList['data'][$k]['node'],
                            ADODB_FETCH_ASSOC
                        );

                        if (empty($_COOKIE['VKcxpNwu5XXAHfSf']) === FALSE) {
                            echo '<pre>';
                            var_dump('bid', $postList['data'][$k]['boardid']);
                            var_dump('nid', $postList['data'][$k]['node']);
                            var_dump('readflag', $postList['data'][$k]['readflag']);
                            var_dump('last_whispers_time', $lastWhispersTime);
                            var_dump('last_readtime', $lastReadTime);
                            echo '</pre>';
                        }

                        if ($lastWhispersTime > $lastReadTime) {
                            $postList['data'][$k]['readflag'] = 0;
                        }

                        if (empty($_COOKIE['VKcxpNwu5XXAHfSf']) === FALSE) {
                            echo '<pre>';
                            var_dump($postList['data'][$k]['readflag']);
                            echo '</pre>';
                        }
                    }
                }
            }

            // 取登入者按讚數
            $push = $this->getPush(array(), $nodeIdList, array($sysSession->username));
            foreach ($push as $k => $v) {
                $postList['data'][$k]['pushflag'] = $push[$k]['push'];
            }
        } else {
            $postList ['code'] = - 1;
            $postList ['message'] = $MSG ['msg_no_course'] [$sysSession->lang];
            $postList ['total_rows'] = $ct;
            $postList ['limit_rows'] = $perPage;
        }

        // cookie 存取每頁筆數?
        return $postList;
    }

    /**
     *
     * 取得課程公告文章
     *
     * @param string $bid 討論版版號
     */
    function getCourseAnnouncement($bid, $curPage, $perPage, $keyword = '', $sort = 'pt', $order = 'desc') {
        global $sysRoles, $sysSession;
        // 取版擁有者(cid)
        // 判斷是否該課學生及該課公告
        if (! aclCheckRole ( $sysSession->username, $sysRoles ['auditor'] | $sysRoles ['student'] | $sysRoles ['assistant'] | $sysRoles ['instructor'] | $sysRoles ['teacher'], $sysSession->course_id )) {
                $postList ['code'] = 1;
                $postList ['message'] = $MSG ['course_not_apply'] [$sysSession->lang];
                $postList ['total_rows'] = 0;
                $postList ['limit_rows'] = $perPage;
                return $postList;
        }

        return $this->getBbsPosts ( $bid, array (), false, $curPage, $perPage, $keyword, $sort, $order );
    }

    /**
     *
     * 取得課程討論區最新文章
     *
     * @param string $cid 課程編號
     * @param string $bid 討論版版號
     * @param string $curPage 第幾頁
     * @param string $perPage 每頁幾筆
     * @param string $keyword 關鍵字
     * @param string $sort 排序欄位
     * @param string $order 升冪、降冪
     */
    function getCourseForumNews($cid, $bid = array(), $bnid = array(), $onlyTopic = '0', $curPage = 1,
        $perPage = 5, $keyword = '',
        $sort = 'node', $order = 'desc')
    {
        global $sysRoles, $sysSession;
//        echo '<pre>';
//        var_dump('getCourseForumNews');
//        var_dump($cid, $bid, $bnid);
//        echo '</pre>';

        // 取版擁有者(cid)
        // 判斷是否該課學生及該課公告
        if (strlen($cid) === 5 || $cid === '0') {
        } else {
            if (!aclCheckRole(
                $sysSession->username,
                $sysRoles['auditor']|$sysRoles['student']|$sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher'],
                $cid)
            ) {
                // 判斷是否公開
                $public = $this->getCourseForumList($cid, array(), false, $bid);
                if (count($public) === 0) {
                    die('Deny access!! (Incorrect course)');
                }
            }
        }

        if (count($bid) >= 1) {
            $forumListBid = $bid;
        } else {
            // 取課程公告版編號
           $courseAnnId = $this->getCourseAnnId($cid);

           // 取討論版列表編號，排除課程公告版
           $forumListBid = array_keys($this->getCourseForumList($cid, array($courseAnnId)));
        }

        $forumList = $this->getBbsPosts(
            $forumListBid,
            $bnid,
            $onlyTopic,
            $curPage,
            $perPage,
            $keyword,
            $sort,
            $order
        );

        $data = $forumList['data'];

        // 取讀取數
        // 組討論版編號與文章編號陣列
        $node = array();
        if (count($data) >= 1) {
            foreach ($data as $k => $v) {
                $node[] = array('bid' => $v['boardid'], 'nid' => $v['node']);
            }

            /*$read = $this->getRead($forumListBid, $node);
            foreach ($read as $k => $v) {
                $data[$k]['read'] = $read[$k]['read'];
            }*/

            // 取按讚數
            $push = $this->getPush($forumListBid, $node);
            foreach ($push as $k => $v) {
                // $bnid 0 文章列表、1討論串
                if (count($bnid) === 0) {
                    $data[substr($k, 0, 20)]['push'] = $data[substr($k, 0, 20)]['push'] + $push[$k]['push'];
                } else {
                    $data[$k]['push'] = $push[$k]['push'];
                }
            }


            // 取回覆數
            $reply = $this->getReply($forumListBid, $node);
            foreach ($reply as $k => $v) {
                $data[$k]['reply'] = $reply[$k]['reply'];

                $bnid2 = array(
                    array(
                    '0' => substr($k, 0, 10),// 版號
                    '1' => substr($k, 11, 10)// 主題編號
                    )
                );
                $whisper = $this->getWhisper($bnid2);
                $whisperCnt = 0;
                foreach ($whisper as $v) {
                    $whisperCnt += count($v);
                }
                $data[$k]['whisper'] = $whisperCnt;
            }

            // 客製 - 張貼者的身份是否為管理者、教師或助教
            $AcademicArray=GetAcaArray($sysSession->school_id);
            $TeacherArray = array();
//            if(isCourseBoard($sysSession->board_ownerid,$sysSession->board_id)){
                $TeacherArray=getCourseTeacherLevel();
//            }

            $postRoles = array();
            foreach ($data as $k => $v) {
                $postRoles = array();
                if (empty($v['cid'])) {
                    $data[$k]['postRoles'] = '';
                }else{
                    if ( in_array($v['poster'], $AcademicArray)){
                        $postRoles[] = 'academic';
                    }
                    if ( is_array($TeacherArray) && array_key_exists($v['poster'], $TeacherArray)){
                        if($TeacherArray[$v['poster']]['level']&($sysRoles['teacher']|$sysRoles['instructor'])){
                            $postRoles[] = 'teach';
                        }
                        if($TeacherArray[$v['poster']]['level']&$sysRoles['assistant']){
                            $postRoles[] = 'assistant';
                        }
                    }
                    $data[$k]['postRoles'] = implode(',',$postRoles);
                }
            }

            $forumList['data'] = $data;
        }

        return $forumList;
    }

    /**
     *
     * 取得課程討論區最熱門
     *
     * @param string $cid 課程編號
     * @param string $bid 討論版版號
     * @param string $curPage 第幾頁
     * @param string $perPage 每頁幾筆
     * @param string $keyword 關鍵字
     * @param string $sort 排序欄位
     * @param string $order 升冪、降冪
     */
    function getCourseForumHot($cid, $bid = array(), $onlyTopic = '0', $curPage = 1, $perPage = 5, $keyword = '',
        $sort = 'hit', $order = 'desc')
    {
        global $sysSession, $sysRoles;

        if (count($bid) >= 1) {
            $forumListBid = $bid;
        } else {
            // 取課程公告版編號
           $courseAnnId = $this->getCourseAnnId($cid);

           // 取討論版列表編號，排除課程公告版
           $forumListBid = array_keys($this->getCourseForumList($cid, array($courseAnnId)));
        }

        $forumList = $this->getBbsPosts(
            $forumListBid,
            array(),
            $onlyTopic,
            $curPage,
            $perPage,
            $keyword,
            $sort,
            $order
        );

        $data = $forumList['data'];

        // 取讀取數
        // 組討論版編號與文章編號陣列
        if (count($data) >= 1) {
            $node = array();
            foreach ($data as $k => $v) {
                $node[] = array('bid' => $v['boardid'], 'nid' => $v['node']);
            }

            /*$read = $this->getRead($forumListBid, $node);
            foreach ($read as $k => $v) {
                $data[$k]['read'] = $read[$k]['read'];
            }*/

            // 取按讚數
            $push = $this->getPush($forumListBid, $node);
            foreach ($push as $k => $v) {
                $data[$k]['push'] = $push[$k]['push'];
            }

            // 取回覆數
            $reply = $this->getReply($forumListBid, $node);
            foreach ($reply as $k => $v) {
                $data[$k]['reply'] = $reply[$k]['reply'];
            }

            // 客製 - 張貼者的身份是否為管理者、教師或助教
            $AcademicArray=GetAcaArray($sysSession->school_id);
            $TeacherArray = array();
            if(isCourseBoard($sysSession->board_ownerid,$sysSession->board_id)){
                $TeacherArray=getCourseTeacherLevel();
            }

            $postRoles = array();
            foreach ($data as $k => $v) {
                $postRoles = array();
                if (empty($v['cid'])) {
                    $data[$k]['postRoles'] = '';
                }else{
                    if ( in_array($v['poster'], $AcademicArray)){
                        $postRoles[] = 'academic';
                    }
                    if ( array_key_exists($v['poster'], $TeacherArray)){
                        if($TeacherArray[$v['poster']]['level']&($sysRoles['teacher']|$sysRoles['instructor'])){
                            $postRoles[] = 'teach';
                        }
                        if($TeacherArray[$v['poster']]['level']&$sysRoles['assistant']){
                            $postRoles[] = 'assistant';
                        }
                    }
                    $data[$k]['postRoles'] = implode(',',$postRoles);
                }
            }
            $forumList['data'] = $data;
        }

        return $forumList;
    }

    /**
     *
     * 取得課程討論區最佳
     *
     * @param string $cid 課程編號
     * @param string $bid 討論版版號
     * @param string $curPage 第幾頁
     * @param string $perPage 每頁幾筆
     * @param string $keyword 關鍵字
     * @param string $sort 排序欄位
     * @param string $order 升冪、降冪
     */
    function getCourseForumPush($cid, $bid = array(), $onlyTopic = '0', $curPage = 1, $perPage = 5, $keyword = '',
        $sort = 'pt', $order = 'desc')
    {
        global $sysSession, $sysRoles;

        if (count($bid) >= 1) {
            $forumListBid = $bid;
        } else {
            // 取課程公告版編號
           $courseAnnId = $this->getCourseAnnId($cid);

           // 取討論版列表編號，排除課程公告版
           $forumListBid = array_keys($this->getCourseForumList($cid, array($courseAnnId)));
        }

        // 取按讚數前N名
        $push = $this->getPush($forumListBid, array(), array(), $onlyTopic, 'push', 'DESC', $curPage, $perPage, $keyword);

        // 取按讚數前N名相關資訊
        $nid = array_keys($push);
        $bestList = $this->getBbsPosts($forumListBid, $nid, $onlyTopic, $curPage, $perPage, $keyword, $sort, $order);
        $best = $bestList['data'];

        // 取讀取數
        // 組討論版編號與文章編號陣列
        if (count($best) >= 1) {
            $node = array();
            foreach ($best as $k => $v) {
                $node[] = array($v['boardid'], $v['node']);
            }

            /*$read = $this->getRead($forumListBid, $node);
            foreach ($read as $k => $v) {
                $best[$k]['read'] = $read[$k]['read'];
            }*/

            // 取回覆數
            $reply = $this->getReply($forumListBid, $node);
            foreach ($reply as $k => $v) {
                $best[$k]['reply'] = $reply[$k]['reply'];
            }

            // 客製 - 張貼者的身份是否為管理者、教師或助教
            $AcademicArray=GetAcaArray($sysSession->school_id);
            $TeacherArray = array();
            if(isCourseBoard($sysSession->board_ownerid,$sysSession->board_id)){
                $TeacherArray=getCourseTeacherLevel();
            }

            $postRoles = array();
            foreach ($data as $k => $v) {
                $postRoles = array();
                if (empty($v['cid'])) {
                    $data[$k]['postRoles'] = '';
                }else{
                    if ( in_array($v['poster'], $AcademicArray)){
                        $postRoles[] = 'academic';
                    }
                    if ( array_key_exists($v['poster'], $TeacherArray)){
                        if($TeacherArray[$v['poster']]['level']&($sysRoles['teacher']|$sysRoles['instructor'])){
                            $postRoles[] = 'teach';
                        }
                        if($TeacherArray[$v['poster']]['level']&$sysRoles['assistant']){
                            $postRoles[] = 'assistant';
                        }
                    }
                    $data[$k]['postRoles'] = implode(',',$postRoles);
                }
            }
            // 重組按讚數前N名相關資訊
            $crashPush5 = array();
            foreach ($push as $k => $v) {
                // 如果有按讚，但主文已被刪除，則跳過
                if (isset($best[$k]['boardid']) === false) {
                    $crashPush5[] = $k;
                    continue;
                }
                $push[$k]['cid'] = $best[$k]['cid'];
                $push[$k]['boardid'] = $best[$k]['boardid'];
                $push[$k]['node'] = $best[$k]['node'];
                $push[$k]['poster'] = $best[$k]['poster'];
                $push[$k]['realname'] = $best[$k]['realname'];
                $push[$k]['subject'] = $best[$k]['subject'];
                $push[$k]['postdate'] = $best[$k]['postdate'];
                $push[$k]['postdatelen'] = $best[$k]['postdatelen'];
                $push[$k]['postcontent'] = $best[$k]['postcontent'];
                $push[$k]['postcontenttext'] = $best[$k]['postcontenttext'];
                $push[$k]['hit'] = $best[$k]['hit'];
                $push[$k]['n'] = $best[$k]['n'];
                $push[$k]['s'] = $best[$k]['s'];
                $push[$k]['read'] = $best[$k]['read'];
                $push[$k]['pushflag'] = $best[$k]['pushflag'];
                $push[$k]['readflag'] = $best[$k]['readflag'];
                $push[$k]['reply'] = $best[$k]['reply'];
            }

            // 移除主文章不存在的前N名
            foreach ($crashPush5 as $v) {
                unset($push[$v]);
            }

            // 取總文章數
            $total = $this->getBbsPosts($forumListBid, array(), $onlyTopic, $curPage, $perPage, $keyword, $sort, $order);
        }

//        // 仍取5筆，以避免沒有人任何按讚的狀況
//        $forumList = $this->getBbsPosts(
//            $forumListBid,
//            array(),
//            $onlyTopic,
//            $curPage,
//            $perPage,
//            $keyword,
//            $sort,
//            $order
//        );
//
//        $data = $forumList['data'];
//
//        // 取讀取數
//        // 組討論版編號與文章編號陣列
//        $node = array();
//        foreach ($data as $k => $v) {
//            $node[] = array($v['boardid'], $v['node']);
//        }
//
//        $read = $this->getRead($forumListBid, $node);
//        foreach ($read as $k => $v) {
//            $data[$k]['read'] = $read[$k]['read'];
//        }
//
//        // 取按讚數
//        $push = $this->getPush($forumListBid, $node);
//        foreach ($push as $k => $v) {
//            $data[$k]['push'] = $push[$k]['push'];
//        }
//
//        // 取回覆數
//        $reply = $this->getReply($forumListBid, $node);
//        foreach ($reply as $k => $v) {
//            $data[$k]['reply'] = $reply[$k]['reply'];
//        }
//
//        // 移除沒有讚的陣列中與TOP5重複的
//        foreach ($push5 as $k => $v) {
//            unset($data[$k]);
//        }
//
//        // 移除沒有讚的陣列多餘的筆數
//        $removeStart = $perPage - count($push5);
//        $i = 0;
//        foreach ($data as $k => $v) {
//            if ($i >= $removeStart) {
//                unset($data[$k]);
//            }
//            $i = $i + 1;
//        };
//
//        $merge = array_merge($push5, $data);

        if (count($push) >= 1) {
            $result['data'] = $push;
            $result['code'] = 0;
            $result['current_page'] = $curPage;
            $result['editEnable'] = '1';
            $result['limit_rows'] = $perPage;
            $result['total_rows'] = $total['total_rows'];
        } else {
            $result['code'] = -1;
            $result['message'] = '沒有任何資料';
            $result['total_rows'] = 0;
            $result['limit_rows'] = $perPage;
        }

        return $result;
    }

    /**
     * 取得指定討論版文章
     *
     * @param string $bid 討論版版號
     * @param integer $curPage 第幾頁
     * @param integer $perPage 每頁幾筆
     * @param string $keyword 關鍵字
     * @param string $sort 排序欄位
     * @param string $order 升冪、降冪
     */
    function getForumData($bid)
    {
        global $sysRoles, $sysSession;

        // TODO：根據有無登入判斷是否有權限
        $forumList = $this->getBbsPosts(
            $bid,
            array(),
            '0',
            1,
            5
        );

        $data = $forumList['data'];

        // 取讀取數
        // 組討論版編號與文章編號陣列
        $node = array();
        if (count($data) >= 1) {
            foreach ($data as $k => $v) {
                $node[] = array('bid' => $v['boardid'], 'nid' => $v['node']);
            }

            /*$read = $this->getRead($bid, $node);
            foreach ($read as $k => $v) {
                $data[$k]['read'] = $read[$k]['read'];
            }*/

            // 取按讚數
            $push = $this->getPush($bid, $node);
            foreach ($push as $k => $v) {
                $data[$k]['push'] = $push[$k]['push'];
            }

            // 取回覆數
            $reply = $this->getReply($bid, $node);
            foreach ($reply as $k => $v) {
                $data[$k]['reply'] = $reply[$k]['reply'];
            }

            $forumList['data'] = $data;
        }

        return $forumList;
    }

    /**
     * 取得讀取次數
     *
     * @param array $bid 討論版編號
     * @param array $nid array(討論版編號, 文章編號)
     * @param array $user 使用者帳號
     */
    function getRead($bid = array(), $nid = array(), $user = array())
    {
        if (!(is_array($bid))) {
            $bid = array($bid);
        }
        $boardId = implode(',', $bid);

        $node = array();
        if (!(is_array($nid))) {
            $nid = array($nid);
        }
        foreach ($nid as $v) {
            $node[] = sprintf("'%s'", mysql_real_escape_string(implode('|', $v)));
        }
        $nodeId = implode(',', $node);

        if (!(is_array($user))) {
            $user = array($user);
        }
        $username = implode(',', $user);

        $whr = '';
        if (count($nid) >= 1 && count($user) >= 1) {
            $whr = sprintf(
                "type = 'b' AND `board_id` in (%s) AND CONCAT(`board_id`, '|', `node`) in (%s) AND `username` in (%s)",
                $boardId,
                $nodeId,
                mysql_real_escape_string($username)
            );
        } else if (count($nid) >= 0 && count($user) === 0) {
            $whr = sprintf(
                "type = 'b' AND `board_id` in (%s) AND CONCAT(`board_id`, '|', `node`) in (%s)",
                $boardId,
                $nodeId
            );
        } else if (count($nid) === 0 && count($user) >= 1) {
            $whr = sprintf(
                "type = 'b' AND `board_id` in (%s) AND `username` in (%s)",
                $boardId,
                mysql_real_escape_string($username)
            );
        } else if (count($nid) === 0 && count($user) === 0) {
            $whr = sprintf(
                "type = 'b' AND `board_id` in (%s)",
                $boardId
            );
        }

        $rsRead = dbGetStMr(
            'WM_bbs_readed',
            '`board_id`, `node`, `username`',
            $whr,
            ADODB_FETCH_ASSOC
        );

        $read = array();
        if ($rsRead) {
            while (!$rsRead->EOF) {
                $read[$rsRead->fields['board_id'] . '|' . $rsRead->fields['node']]['read']
                    = $read[$rsRead->fields['board_id'] . '|' . $rsRead->fields['node']]['read'] + 1;

                $rsRead->MoveNext();
            }
        }

        return $read;
    }

    /**
     * 取得按讚次數
     *
     * @param array $bid 討論版編號
     * @param array $nid array(討論版編號, 文章編號)
     * @param array $user 使用者帳號
     */
    function getPush($bid = array(), $nid = array(), $user = array(),
        $onlyTopic = '0', $sort = 'push', $order = 'DESC', $curPage = '', $perPage = '', $keyword = '')
    {
//        echo '<pre>';
//        var_dump('getPush');
//        var_dump(count($bid));
//        var_dump(count($nid));
//        echo '</pre>';
        if (!(is_array($bid))) {
            $bid = array($bid);
        }
        $boardId = implode(',', $bid);

        $node = array();
        if (!(is_array($nid))) {
            $nid = array($nid);
        }

        foreach ($nid as $v) {
            $node[] = sprintf("'%s'", mysql_real_escape_string(implode('|', $v)));
        }
        $nodeId = implode(',', $node);

        if (!(is_array($user))) {
            $user = array($user);
        }
        $username = implode("','", $user);

        $whr = '';
//        echo '<pre>';
//        var_dump($bid);
//        var_dump($nid);
//        var_dump('--------------');
//        echo '</pre>';

        //
        if (count($bid) >= 1 && count($nid) >= 1 && count($user) >= 1) {
            $whr = sprintf(
                "type = 'b' AND `board_id` in (%s) AND CONCAT(`board_id`, '|', `node`) in (%s) AND `username` in ('%s')",
                $boardId,
                $nodeId,
                ($username)
            );
        } else if (count($nid) >= 1 && count($user) >= 1) {

            $bids = array();
            foreach ($nid as $v) {
                if (!in_array($v['bid'], $bids)) {
                    $bids[] = $v['bid'];
                }
            }

            $whr = sprintf(
                "type = 'b' AND `board_id` in (%s) AND CONCAT(`board_id`, '|', `node`) in (%s) AND `username` in ('%s')",
                implode(',',$bids),
                $nodeId,
                ($username)
            );
        // 討論文章列表、討論串主題、討論串回應 的按讚數
        } else if (count($bid) >= 1 && count($nid) >= 1 && count($user) === 0) {
            // 討論文章列表、討論串主題
            if (strlen($nid[0]['nid']) === 9) {
                $whr = sprintf(
                    "type = 'b' AND `board_id` in (%s) AND CONCAT(`board_id`, '|', substring( `node` , 1, 9 )) in (%s)",
                    $boardId,
                    $nodeId
                );
            // 討論串回應
            } else {
                $whr = sprintf(
                    "type = 'b' AND `board_id` in (%s) AND CONCAT(`board_id`, '|', `node`) in (%s)",
                    $boardId,
                    $nodeId
                );
            }
        } else if (count($nid) === 0 && count($user) >= 1) {
            $whr = sprintf(
                "type = 'b' AND `board_id` in (%s) AND `username` in (%s)",
                $boardId,
                mysql_real_escape_string($username)
            );
        } else if (count($nid) === 0 && count($user) === 0) {
            $whr = sprintf(
                "type = 'b' AND `board_id` in (%s)",
                $boardId
            );
        }

        if ($onlyTopic === '1') {
            $whr .= sprintf("AND LENGTH(`node`) = 9 ");
        }

        // 排序 (預備用)
        if ($sort !== '') {
            $subWhr .= sprintf('ORDER BY %s %s ', $sort, $order);
        }

        // 取得筆數
        if ($perPage !== '') {
            $subWhr .= 'LIMIT ' . ($curPage - 1) * $perPage . ',' . $perPage . ' ';
        }

        // 關鍵字搜尋
        if ($keyword !== '') {
            $whrKeyword .= sprintf(
                ' WHERE (`poster` LIKE \'%%%s%%\' OR `subject` LIKE \'%%%s%%\' OR `content` LIKE \'%%%s%%\') ',
                $keyword,
                $keyword,
                $keyword
            );
        }

        $exists = 'AND (site, board_id, node) in
                (SELECT site, board_id, node from WM_bbs_posts' . $whrKeyword . ') ';

        if (count($user) >= 1) {
            $rsRead = dbGetStMr(
                'WM_bbs_ranking',
                '`board_id`, `node`, count(`score`) push',
                $whr . $exists . ' group by `board_id`, `node` ' . $subWhr,
                ADODB_FETCH_ASSOC
            );
        } else {
            $rsRead = dbGetStMr(
                'WM_bbs_push',
                '`board_id`, `node`, `push` push',
                $whr . $exists . $subWhr,
                ADODB_FETCH_ASSOC
            );
        }

        $read = array();
        if ($rsRead) {
            while (!$rsRead->EOF) {
                $nid = sprintf(
                    "%s|%s",
                    mysql_real_escape_string($rsRead->fields['board_id']),
                    mysql_real_escape_string($rsRead->fields['node'])
                );
                $read[$nid]['push']
                    = $rsRead->fields['push'];

                $rsRead->MoveNext();
            }
        }

        return $read;
    }

    /**
     * 取得文章節點回覆數
     *
     * @param array $bid 討論版編號
     * @param array $nid array(討論版編號, 文章編號)
     * @param array $user 使用者帳號
     */
    function getReply($bid = array(), $nid = array(), $user = array(), $curPage = '', $perPage = '', $keyword = '',
        $sort = 'pt', $order = 'desc', $dtlFlag = false)
    {
        // 判斷是否為檢視單一回覆
        if (strlen($nid[0][1]) === 18) {
            $nidlen = 18;
        } else {
            $nidlen = 9;
        }


        if (!(is_array($bid))) {
            $bid = array($bid);
        }
        $boardId = implode(',', $bid);

        $node = array();
        if (!(is_array($nid))) {
            $nid = array($nid);
        }
        foreach ($nid as $v) {
            $node[] = sprintf("'%s'", mysql_real_escape_string(implode('|', $v)));
        }
        $nodeId = implode(',', $node);

        if (!(is_array($user))) {
            $user = array($user);
        }
        $username = implode(',', $user);

        $whr = '';
        if (count($nid) >= 1 && count($user) >= 1) {
            $whr = sprintf(
                "`board_id` in (%s) AND CONCAT(`board_id`, '|', substr(`node`, 1, {$nidlen})) in (%s) AND `username` in (%s)",
                $boardId,
                $nodeId,
                mysql_real_escape_string($username)
            );
        } else if (count($nid) >= 0 && count($user) === 0) {
            $whr = sprintf(
                "`board_id` in (%s) AND CONCAT(`board_id`, '|', substr(`node`, 1, {$nidlen})) in (%s)",
                $boardId,
                $nodeId
            );
        } else if (count($nid) === 0 && count($user) >= 1) {
            $whr = sprintf(
                "`board_id` in (%s) AND `poster` in (%s)",
                $boardId,
                mysql_real_escape_string($username)
            );
        } else if (count($nid) === 0 && count($user) === 0) {
            $whr = sprintf(
                "`board_id` in (%s)",
                $boardId
            );
        }

        // 校正起始頁
        if ($curPage <= 0) {
            $curPage = 1;
        }

        // 排序 (預備用)
        if ($sort !== '') {
            $subWhere .= sprintf('ORDER BY %s %s ', $sort, $order);
        }

        // 取得筆數（沒有指定NODEID才使用LIMIT）
        if ($perPage >= 1) {
            $subWhere .= 'LIMIT ' . ($curPage-1) * $perPage . ',' . $perPage . ' ';
        }

        $rsReply = dbGetStMr(
            'WM_bbs_posts',
            '`board_id`, `node`, `poster`',
            $whr . 'AND LENGTH(`node`) = 18 ' . $subWhere,
            ADODB_FETCH_ASSOC
        );

        $reply = array();
        $bnid = array();
        if ($rsReply) {
            while (!$rsReply->EOF) {
//                $tpcId = $rsReply->fields['board_id'] . '|' . substr($rsReply->fields['node'], 0, 9);
                $tpcId = $rsReply->fields['board_id'] . '|' . substr($rsReply->fields['node'], 0, $nidlen);

                $reply[$tpcId]['reply']
                    = $reply[$tpcId]['reply'] + 1;
                $bnid[] = $rsReply->fields['board_id'] . '|' .$rsReply->fields['node'];
                $bnid2[] = array('bid' => $rsReply->fields['board_id'], 'nid' => $rsReply->fields['node']);

                $rsReply->MoveNext();
            }

            if ($dtlFlag === true) {
                // 取文章詳細資料
                $rsReplyDtl = $this->getBbsPosts($bid, $bnid, false, $curPage, $perPage, $keyword, $sort, $order);
                $reply[$tpcId]['data'] = $rsReplyDtl['data'];

                // 取按讚數
                if (count($bnid2) >= 1) {

                    global $sysSession;

                    // 取登入者按讚數
                    $pushSelf = $this->getPush(array(), $bnid2, array($sysSession->username));
                    foreach ($pushSelf as $k => $v) {
                        $reply['data'][$k]['pushflag'] = $pushSelf[$k]['push'];
                    }

                    // 取按讚數
                    $push = $this->getPush($bid, $bnid2);
                    foreach ($push as $k => $v) {
                        $reply[$tpcId]['data'][$k]['push'] = $push[$k]['push'];
                    }

                    // 寫下閱讀紀錄
                    global $sysConn;
                    foreach ($bnid2 as $v) {
                        dbNew('WM_bbs_readed','type, board_id, node, username, read_time', "'b', {$v['bid']}, '{$v['nid']}', '{$sysSession->username}', Now()");
                        if($sysConn->Affected_Rows() === false) {
                            dbSet('LOW_PRIORITY WM_bbs_readed', 'read_time = Now()', "type = 'b' and board_id = {$v['bid']} and node = '{$v['nid']}' and username = '{$sysSession->username}'");
                        }
                    }
                }

                // 取回覆的留言數
                if (count($bnid2) >= 1) {
                    $whisper = $this->getWhisper($bnid2, array(), $dtlFlag);
                    $reply[$tpcId]['data'][$k]['whisper'] = $whisper;
                    foreach ($whisper as $k => $v) {
                        $reply[$tpcId]['data'][$k]['whispercnt'] = count($v);
                    }
                }

                // 取得總筆數
                $ct = dbGetOne('WM_bbs_posts', 'count(board_id)', $whr . 'AND LENGTH(`node`) = 18 ', ADODB_FETCH_ASSOC);

                $reply['code'] = 0;
                $reply['total_rows'] = $ct;// 全部筆數（錯誤取法）
                $reply['limit_rows'] = $perPage;// 一頁幾筆
                $reply['current_page'] = (String)$curPage;// 目前第幾頁
            }
        }

        return $reply;
    }

    /**
     * 取得附註內容
     *
     * @param array $nid array(討論版編號, 文章編號)
     */
    function getWhisper($bnid = array(),  $wid = array(), $dtlFlag = false, $sort = 'DESC')
    {
        $node = array();
        if (!(is_array($bnid))) {
            $bnid = array($bnid);
        }
        foreach ($bnid as $v) {
            $node[] = sprintf("'%s'", mysql_real_escape_string(implode('|', $v)));
        }
        $nodeId = implode(',', $node);
        if (strlen($nodeId) >= 1) {
            // 整個主題
            if (strlen($nodeId) === 22) {
                $whr = sprintf(
                    "and CONCAT(`board_id`, '|', substr(`node`, 1, 9)) in (%s)",
                    $nodeId
                );
            } else {
                $whr = sprintf(
                    "and CONCAT(`board_id`, '|', `node`) in (%s)",
                    $nodeId
                );
            }
        }

        if (is_array($wid) === true && count($wid) >= 1) {
            $wids = implode(',', $wid);
            $whr .= sprintf(
                "and `wid` in (%s)",
                $wids
            );
        }

        if ($dtlFlag === true) {
            $cols = '`wid`, `site`, `board_id`, `node`, `content`, `creator`, `creator_realname`, `creator_email`, `create_time`';
        } else {
            $cols = '`wid`, `site`, `board_id`, `node`';
        }

        $rsWhisper = dbGetStMr(
            'WM_bbs_whispers',
            $cols,
            '1 ' . $whr . 'ORDER BY `create_time` ' . $sort,
            ADODB_FETCH_ASSOC
        );

        $whisper = array();
        $i = 0;
        if ($rsWhisper) {
            while (!$rsWhisper->EOF) {
                $bnid = $rsWhisper->fields['board_id']  . '|' . $rsWhisper->fields['node'];
                $whisper[$bnid][$i]['wid'] = $rsWhisper->fields['wid'];
                $whisper[$bnid][$i]['sid'] = $rsWhisper->fields['site'];
                $whisper[$bnid][$i]['board_id'] = $rsWhisper->fields['board_id'];
                $whisper[$bnid][$i]['node'] = $rsWhisper->fields['node'];
                $whisper[$bnid][$i]['creator'] = $rsWhisper->fields['creator'];
                $whisper[$bnid][$i]['cpic'] = base64_encode(urlencode($rsWhisper->fields['creator']));
                $whisper[$bnid][$i]['creator_realname'] = $rsWhisper->fields['creator_realname'];
                $whisper[$bnid][$i]['content'] = nl2br(htmlspecialchars($rsWhisper->fields['content']));
                $whisper[$bnid][$i]['create_time'] = substr($rsWhisper->fields['create_time'], 0, 16);
                $whisper[$bnid][$i]['create_time_length'] = $this->getLengthOfTime($rsWhisper->fields['create_time']);

                $i = $i + 1;
                $rsWhisper->MoveNext();
            }
        }

        return $whisper;
    }

    /**
     * 取得課程公告版或預設討論版編號
     *
     * @param integer $courseId 課程編號
     *
     * @param string $type bulletin:公告版、discuss:預設討論版
     */
    function getCourseAnnId($courseId = 0, $type=1)
    {
        $defaultBoard = array(
            1   =>  '`bulletin`',
            2   =>  '`discuss`'
        );
        $type = ($type != 2) ? 1 : $type;
        $bid = dbGetOne(
            'WM_term_course',
            $defaultBoard[$type],
            sprintf("`course_id`='%d'", mysql_real_escape_string($courseId)),
            ADODB_FETCH_ASSOC
        );

        return $bid;
    }

    /**
     * 取得討論區列表資訊（討論版名稱、主題數、回覆數、按讚數、最後文章時間）
     *
     * @param integer $courseId 課程編號
     * @param array $excBids 不包含的版號
     * @param boolean $extraFlag 額外資訊
     */
    function getCourseForumList($courseId = 0, $excBids = array(), $extraFlag = false, $bid = array())
    {
        global $sysSession;
        $whr = '';
        
//        echo '<pre>';
//        var_dump('$courseId', $courseId);
//        echo '</pre>';

        // 指定版號
        if (count($bid) >= 1) {
            $boardId = implode(',', $bid);
            $whr .= sprintf("WM_bbs_boards.board_id IN (%s)", $boardId);
        }
        
//        echo '<pre>';
//        var_dump('$bid', $bid);
//        var_dump('$boardId', $boardId);
//        echo '</pre>';
        
        $studentGroup = 0;
        if (preg_match('/^[\d]{10}$/', $boardId)) {
            $rsStudentGroup = dbGetStMr(
                'WM_student_group',
                '`board_id`',
                sprintf("board_id = %d", $boardId),
                ADODB_FETCH_ASSOC
            );
            if ($rsStudentGroup) {
                $studentGroup = $rsStudentGroup->RecordCount();
            }
        }
//        echo '<pre>';
//        var_dump('$studentGroup', $studentGroup);
//        echo '</pre>';

        // 判斷權限
        if ($studentGroup === 0 && (strlen($courseId) === 8)) {
            $whr .= (($whr >= '0')?' AND' : '') . $this->courseForumAcl($courseId, $sysSession->username);
        }
        
//        echo '<pre>';
//        var_dump('$whr', $whr);
//        echo '</pre>';
        
        
        // 理論上討論版都會有參數傳入，為避免沒有參數時，撈全站討論版，造成效能低落，改顯示學校的系統建議版，待發現是哪個頁面沒有傳入參數時，再進行修正
        $rs = dbGetStMr(
            'WM_bbs_boards left join WM_term_subject on WM_bbs_boards.board_id = WM_term_subject.board_id',
            "`WM_bbs_boards`.`board_id`, `bname`, `owner_id`, `title`, `manager`, `poster`, `open_time`, `close_time`, `after_finish`, `fb_comment`, `WM_term_subject`.`state`",
            ($whr === '') ? "`visibility` = 'visible' AND WM_bbs_boards.board_id = (SELECT board_id FROM WM_news_subject WHERE type ='suggest' AND visibility='visible')" : $whr,
            ADODB_FETCH_ASSOC
        );
        
        $forums = array();
        if ($rs) {
            while (!$rs->EOF) {
                // 討論版名稱、主題數、回覆數、按讚數、最後文章時間
                $bid = sprintf("'%d'", mysql_real_escape_string($rs->fields['board_id']));
                // 排除指定不包含的版號
                if ($rs->fields['board_id'] >= 1 && !(in_array($rs->fields['board_id'], $excBids))) {
                    $forums[$bid]['board_id'] = $rs->fields['board_id'];

                    $multiCaption = getCaption($rs->fields['bname']);
                    $bname = $multiCaption[$sysSession->lang];
                    $forums[$bid]['board_name'] = $bname;
                    $forums[$bid]['state'] = $rs->fields['state'];
                    $forums[$bid]['title'] = $rs->fields['title'];
                    $forums[$bid]['owner_id'] = $rs->fields['owner_id'];
                    $forums[$bid]['manager'] = $rs->fields['manager'];
                    $forums[$bid]['open_time'] = substr($rs->fields['open_time'], 0, 16);
                    $forums[$bid]['close_time'] = substr($rs->fields['close_time'], 0, 16);
                    $forums[$bid]['share_time'] = substr($rs->fields['share_time'], 0, 16);
                    $forums[$bid]['after_finish'] = $rs->fields['after_finish'];
                    $forums[$bid]['fb_comment'] = $rs->fields['fb_comment'];
                    $forums[$bid]['poster'] = $rs->fields['poster'];

                    if ($extraFlag === true) {
                        // 取指定討論版的主題數、回覆數、按讚數、最後文章時間
                        $summary = $this->getForumSummaryByBid($bid);
                        $forums[$bid]['subject_cnt'] = $summary['subject_cnt'];
//                        $forums[$bid]['reply_cnt'] = $summary['reply_cnt'];
//                        $forums[$bid]['push_cnt'] = $summary['push_cnt'];
                        $forums[$bid]['read_cnt'] = $summary['read_cnt'];
                        $forums[$bid]['read_cnt_myself'] = $summary['read_cnt_myself'];
                        $forums[$bid]['read_flag'] = ($summary['subject_cnt'] + $summary['reply_cnt'] - $summary['read_cnt_myself'] >= 1) ? false : true;
                        $forums[$bid]['update_date'] = substr($summary['update_date'], 0, 16);
                        // 時間長度表示式，參考「MOOC-共通要件定義書」
                        $forums[$bid]['update_date_lengh'] = $this->getLengthOfTime($summary['update_date']);
                    }
                }

                $rs->MoveNext();
            }
        }

        return $forums;
    }

    /**
     * 取得學員分組討論區列表資訊（討論版名稱、主題數、回覆數、按讚數、最後文章時間）
     *
     * @param integer $courseId 課程編號
     * @param array $excBids 不包含的版號
     * @param boolean $extraFlag 額外資訊
     */
    function getCourseGroupForumList($courseId = 0, $excBids = array(), $extraFlag = false, $bid = array())
    {
        global $sysSession;

        // 指定版號
        if (count($bid) >= 1) {
            $boardId = implode(',', $bid);
            $whr .= sprintf("WM_bbs_boards.board_id IN (%s)", $boardId);
        }

        // 判斷權限
        if (isset($courseId)) {
            $whr .= (($whr >= '0')?' AND' : '').sprintf("`course_id`='%d'",$courseId);
        }

        $rs = dbGetStMr(
            'WM_student_group left join WM_bbs_boards on WM_bbs_boards.board_id = WM_student_group.board_id',
            "`WM_student_group`.`board_id`, `bname`, `title`, `manager`, `poster`, `open_time`, `close_time`, `after_finish`",
            $whr,
            ADODB_FETCH_ASSOC
        );

        $forums = array();
        if ($rs) {
            while (!$rs->EOF) {
                // 討論版名稱、主題數、回覆數、按讚數、最後文章時間
                $bid = sprintf("'%d'", mysql_real_escape_string($rs->fields['board_id']));
                // 排除指定不包含的版號
                if ($rs->fields['board_id'] >= 1 && !(in_array($rs->fields['board_id'], $excBids))) {
                    $forums[$bid]['board_id'] = $rs->fields['board_id'];

                    $multiCaption = getCaption($rs->fields['bname']);
                    $bname = $multiCaption[$sysSession->lang];
                    $forums[$bid]['board_name'] = $bname;
                    $forums[$bid]['title'] = $rs->fields['title'];
                    $forums[$bid]['manager'] = $rs->fields['manager'];
                    $forums[$bid]['open_time'] = $rs->fields['open_time'];
                    $forums[$bid]['close_time'] = $rs->fields['close_time'];
                    $forums[$bid]['after_finish'] = $rs->fields['after_finish'];
                    $forums[$bid]['poster'] = $rs->fields['poster'];

                    if ($extraFlag === true) {
                        // 取指定討論版的主題數、回覆數、按讚數、最後文章時間
                        $summary = $this->getForumSummaryByBid($bid);
                        $forums[$bid]['subject_cnt'] = $summary['subject_cnt'];
//                        $forums[$bid]['reply_cnt'] = $summary['reply_cnt'];
//                        $forums[$bid]['push_cnt'] = $summary['push_cnt'];
                        $forums[$bid]['read_cnt'] = $summary['read_cnt'];
                        $forums[$bid]['read_cnt_myself'] = $summary['read_cnt_myself'];
                        $forums[$bid]['read_flag'] = ($summary['subject_cnt'] + $summary['reply_cnt'] - $summary['read_cnt_myself'] >= 1) ? false : true;
                        $forums[$bid]['update_date'] = substr($summary['update_date'], 0, 16);
                        // 時間長度表示式，參考「MOOC-共通要件定義書」
                        $forums[$bid]['update_date_lengh'] = $this->getLengthOfTime($summary['update_date']);
                    }
                }

                $rs->MoveNext();
            }
        }

        return $forums;
    }

    function courseForumAcl($courseId, $username)
    {
        global $sysRoles;

        // 老師群、正式生、旁聽生
        if (aclCheckRole(
            $username,
            $sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']|$sysRoles['student']|$sysRoles['auditor'], $courseId) === '1') {
            $whr = sprintf(
                "`course_id`='%d' AND `visibility`='visible'",
                mysql_real_escape_string($courseId)
            );
        } else {    // 不是老師也不是學生
//          die('Deny access!! (Incorrect course)');
            $whr = sprintf(
                "`course_id`='%d' AND `visibility`='visible' AND `state` in ('public') ",
                mysql_real_escape_string($courseId)
            );
        }

//        $whr .= " AND (after_finish = '' OR (NOW() >= close_time AND close_time != '0000-00-00 00:00:00' AND after_finish = 'public') OR (NOW() >= open_time AND NOW() <= close_time) OR (after_finish = 'closed' AND (close_time is null OR close_time = '0000-00-00 00:00:00' OR NOW() <= close_time)))";

//        $whr .= ' AND ((NOW() >= open_time AND NOW() < close_time) OR
//                    (NOW() >= open_time AND close_time is null) OR
//                    (NOW() < close_time AND open_time is null) OR
//                    (NOW() >= open_time AND close_time = \'0000-00-00 00:00:00\') OR
//                    (NOW() < close_time AND open_time = \'0000-00-00 00:00:00\') OR
//                    (open_time is null AND close_time is null) OR
//                    (open_time = \'0000-00-00 00:00:00\' AND close_time = \'0000-00-00 00:00:00\')
//                    )';

        $whr .= ' order by `permute`';

        return $whr;
    }

    function schoolForumAcl()
    {
        // 學校討論版僅顯示公開
//        if ($username === '' || $username === 'guest') {
            $whr = sprintf(
                "`state` in ('open') AND `visibility`='visible'"
            );
//        } else if ($username >= '0' || $username !== 'guest') {
//            $whr = sprintf(
//                "`state` in ('open', 'taonly') AND `visibility`='visible'"
//            );
//        }

        /*
         * 1.沒有設定結束時間
         * 2.結束後公開，現在時間超過結束時間
         * 3.結束後關閉，但沒有設定結束時間或還沒有到結束時間
         */
        $whr .= " AND (after_finish = '' OR (NOW() >= close_time AND close_time != '0000-00-00 00:00:00' AND after_finish = 'public') OR (after_finish = 'closed' AND (close_time = '0000-00-00 00:00:00' OR NOW() < close_time)))";

//        $whr .= ' AND ((NOW() >= open_time AND NOW() < close_time) OR
//                    (NOW() >= open_time AND close_time is null) OR
//                    (NOW() < close_time AND open_time is null) OR
//                    (NOW() >= open_time AND close_time = \'0000-00-00 00:00:00\') OR
//                    (NOW() < close_time AND open_time = \'0000-00-00 00:00:00\') OR
//                    (open_time is null AND close_time is null) OR
//                    (open_time = \'0000-00-00 00:00:00\' AND close_time = \'0000-00-00 00:00:00\'))';

        $whr .= ' order by `board_id` DESC';

        return $whr;
    }

    /**
     * 取得指定討論版的主題數、回覆數、按讚數、最後文章時間
     *
     * @param string $bid 版號
     */
    function getForumSummaryByBid($bid = 0)
    {
        global $sysSession, $sysSiteNo;

        // 主題數、回覆數、最後文章時間
        $rs = dbGetStMr(
            'WM_bbs_posts',
            '`node`, `pt`',
            sprintf("`board_id`=%s AND site = %d ORDER BY `pt` ASC", $bid, $sysSiteNo),
            ADODB_FETCH_ASSOC
        );

        $summary = array();
        $i = $j = 0;
        if ($rs) {
            while (!$rs->EOF) {
                if ($rs->fields['node'] >= 1) {
                    switch (strlen($rs->fields['node'])) {
                        // 主題數
                        case 9:
                            $i = $i + 1;
                            break;

                        // 回覆數
                        case 18:
                            $j = $j + 1;
                            break;
                    }

                    // 最後異動時間（主題或回覆，不含附註）
                    $summary['update_date'] = $rs->fields['pt'];
                }

                $rs->MoveNext();
            }
            $summary['subject_cnt'] = $i;
            $summary['reply_cnt'] = $j;
        }

//        //按讚數
//        $rsPush = dbGetStMr(
//            'WM_bbs_ranking',
//            '`score`',
//            sprintf("`board_id`=%s AND site = %d", $bid, $sysSiteNo),
//            ADODB_FETCH_ASSOC
//        );

//        if ($rsPush) {
//            $summary['push_cnt'] = $rsPush->RecordCount();
//        }

        // 文章數：主題數+回覆數
        $summary['article_cnt'] = $i + $j;

        // 討論版讀取數（僅加總主題）
        $rsRead = dbGetStMr(
            'WM_bbs_readed',
            '`node`',
            sprintf("`board_id`=%s and length(node) = 9", $bid),
            ADODB_FETCH_ASSOC
        );

        if ($rsRead) {
            $summary['read_cnt'] = $rsRead->RecordCount();
        }

        // 個人讀取數
        $rsReadMyself = dbGetStMr(
            'WM_bbs_readed',
            '`node`',
            sprintf("`board_id`=%s AND username = '%s'", $bid, $sysSession->username),
            ADODB_FETCH_ASSOC
        );

        if ($rsReadMyself) {
            $summary['read_cnt_myself'] = $rsReadMyself->RecordCount();
        }

        return $summary;
    }

    /**
     * 取得指定討論串的讀取狀況
     *
     * @param array $nid 文章節點編號
     * @param string $username 使用者編號
     */
    function getTopicSummaryByNid($nid, $username)
    {
        global $sysSiteNo;

        $tmpNodeId = array();
        $bid = array();
        foreach ($nid as $v) {
            $tmpNodeId[] = $v['bid'] . '|' . $v['nid'];
            if (!in_array($v['bid'], $bid)) {
                $bid[] = $v['bid'];
            }
        }
        $nodeId = implode("','", $tmpNodeId);
        $boardId = implode(',', $bid);

        // 主題數、回覆數、最後文章時間
        $rs = dbGetStMr(
            'WM_bbs_posts',
            '`board_id`, `node`, pt',
            sprintf(
                "site = %d AND `board_id` in (%s) AND CONCAT(`board_id`, '|', substr(`node`, 1, 9)) IN ('%s') order by `pt` ASC",
                $sysSiteNo,
                $boardId,
                $nodeId
            ),
            ADODB_FETCH_ASSOC
        );

        $summary = array();
        if ($rs) {
            while (!$rs->EOF) {
                $bnid = $rs->fields['board_id'] . '|' . substr($rs->fields['node'], 0, 9);
                if ($rs->fields['node'] >= 1) {
                    switch (strlen($rs->fields['node'])) {
                        // 主題數
                        case 9:
                            $summary[$bnid]['subject_cnt'] = $summary[$bnid]['subject_cnt'] + 1;
                            break;

                        // 回覆數
                        case 18:
                            $summary[$bnid]['reply_cnt'] = $summary[$bnid]['reply_cnt'] + 1;
                            break;
                    }
                    // 主題+回覆
                    $summary[$bnid]['article_cnt'] = $summary[$bnid]['article_cnt'] + 1;

                    // 最後異動時間（主題或回覆，不含附註）
                    $summary[$bnid]['last_pt'] = $rs->fields['pt'];
                }

                $rs->MoveNext();
            }
        }

        // 讀取數
        $rsRead = dbGetStMr(
            'WM_bbs_readed',
            '`board_id`, `node`, `read_time`',
            sprintf(
                "type = 'b' AND `board_id` in (%s) AND CONCAT(`board_id`, '|', substr(`node`, 1, 9)) IN ('%s') AND username = '%s' order by read_time ASC",
                $boardId,
                $nodeId,
                $username
            ),
            ADODB_FETCH_ASSOC
        );

        if ($rsRead) {
            while (!$rsRead->EOF) {
                $bnid = $rsRead->fields['board_id'] . '|' . substr($rsRead->fields['node'], 0, 9);
                if ($rsRead->fields['node'] >= 1) {
                    $summary[$bnid]['read_cnt'] = $summary[$bnid]['read_cnt'] + 1;
                    $summary[$bnid]['last_read_time'] = substr($rsRead->fields['read_time'], 0, 16);
                }

                $rsRead->MoveNext();
            }
        }

        return $summary;
    }

    /**
     * 按讚/ 取消按讚
     *
     * @param string $bid 版號
     * @param string $node 文章節點編號
     * @param string $sysSiteNo 站編號
     * @param string $username 使用者帳號
     * @param boolean $firstFlag 是否為第一次
     */
    function setPush($bid, $nid, $sid, $username, $firstFlag = '0')
    {
        global $sysConn;

        if ($firstFlag === '1') {
            // 如果沒有新增ranking資料，按讚數不能一直+1 => 改先判斷是否已有資料
            $bid = intval($bid);
            $nid = mysql_real_escape_string($nid);
            $username = mysql_real_escape_string($username);
            $rankingISExist = dbGetOne('`WM_bbs_ranking`', 'COUNT(*)', "`type` = 'b' AND `board_id` = {$bid} AND `node` = '{$nid}' AND `username` = '{$username}'");

            if (intval($rankingISExist) === 0) {
                // 對文章按讚
                dbNew(
                    '`WM_bbs_ranking`',
                    '`board_id`, `node`, `site`, `username`, `score`',
                    "{$bid}, '$nid', $sid, '{$username}', 7"
                );

                $result = array();
                if ($sysConn->Affected_Rows() === 1) {
                    $result = array('code' => 1);

                    // 統計表
                    dbSet(
                        'WM_bbs_push',
                        'push = push + 1',
                        sprintf(
                            "type='b' and board_id = '%s' and node = '%s' and site = '%s'",
                            $bid,
                            $nid,
                            $sid
                        )
                    );

                    // 如果更新失敗代表沒有這筆資料，進行新增
                    if ($sysConn->Affected_Rows() === 0) {
                        dbNew(
                            '`WM_bbs_push`',
                            '`type`, `board_id`, `node`, `site`, `push`',
                            "'b', {$bid}, '$nid', $sid, '1'"
                        );
                    }
                } else if ($sysConn->ErrorNo() === 1062) {
                    $result = array('code' => 0);
                }
            }
        } else {
            dbDel(
                'WM_bbs_ranking',
                sprintf(
                    "type='b' and board_id = '%s' and node = '%s' and site = '%s' and username = '%s'",
                    $bid,
                    $nid,
                    $sid,
                    mysql_real_escape_string($username)
                )
            );

            // 統計表
            dbSet(
                'WM_bbs_push',
                'push = (case when push >= 1 then push - 1 else 0 end)',
                sprintf(
                    "type='b' and board_id = '%s' and node = '%s' and site = '%s'",
                    $bid,
                    $nid,
                    $sid
                )
            );

            $result = array('code' => 1);
        }

        return $result;
    }

    /**
     * 寫入留言
     *
     * @param string $bid 版號
     * @param string $nid 文章編號
     * @param string $sid 網站編號
     * @param string $username 使用者帳號
     * @param string $content 留言內容
     */
    function setWhisper($sid, $bid, $nid, $content, $username, $realname, $email = '')
    {
        global $sysConn;
        $now = date("Y-m-d H:i:s");

        dbNew(
            '`WM_bbs_whispers`',
            '`site`, `board_id`, `node`, `content`, `lang`, creator, creator_realname, creator_email, create_time',
            "$sid, {$bid}, '{$nid}', '{$content}', '1', '{$username}', '{$realname}', '{$email}', '{$now}'"
        );

        // 取 wid流水號
        $wid = $sysConn->Insert_ID();

        $result = array();
        if ($sysConn->Affected_Rows() === 1) {
            wmSysLog('2700200500', $bid , $nid, 1, 'auto', $_SERVER['PHP_SELF'], 'new board whisper(site:' . $sid . ', wid:' . $wid . ')');

            $result = array(
                'code' => 1,
                'data' => array(
                    'wid' => $wid,
                    'content' => nl2br(htmlspecialchars($content)),
                    'creator' => $username,
                    'cpic' => base64_encode(urlencode($username)),
                    'realname' => $realname,
                    'create_time' => substr($now, 0, 16),
                    'create_time_length' => $this->getLengthOfTime($now),
                )
            );
        } else if ($sysConn->ErrorNo() === 1062) {
            wmSysLog('2700200500', $bid , $nid, 0, 'auto', $_SERVER['PHP_SELF'], 'new board whisper(site:' . $sid . ', wid:' . $wid . ')');
            $result = array('code' => 0);
        }

        return $result;
    }

    /**
     * 編輯留言
     *
     * @param string $wid 留言編號
     * @param string $content 留言內容
     * @param string $username 使用者帳號
     * @param string $realname 使用者姓名
     * @param string $email 使用者信箱
     */
    function modWhisper($wid, $content, $username, $realname, $email = '')
    {
        global $sysConn;
        $now = date("Y-m-d H:i:s");

        // 取版號
        $rsWhisper = $this->getWhisper(array(), array($wid));
        foreach ($rsWhisper as $v) {
            $sid = $v[0]['sid'];
            $bid = $v[0]['board_id'];
            $nid = $v[0]['node'];
        }

        dbSet(
            'WM_bbs_whispers',
            sprintf(
                "content = '%s', operator = '%s', operator_realname = '%s', operator_email = '%s', upd_time = '%s'",
                $content,
                $username,
                $realname,
                $email,
                $now
            ),
            sprintf(
                "wid = %d",
                $wid
            )
        );

        $result = array();
        if ($sysConn->Affected_Rows() === 1) {
            wmSysLog('2700200600', $bid , $nid, 1, 'auto', $_SERVER['PHP_SELF'], 'edit board whisper(site:' . $sid . ', wid:' . $wid . ')');

            $result = array(
                'code' => 1,
                'data' => array(
                    'wid' => $wid,
                    'content' => nl2br(htmlspecialchars($content)),
                    'creator' => $username,
                    'cpic' => base64_encode(urlencode($username)),
                    'realname' => $realname,
                    'create_time' => substr($now, 0, 16),
                    'create_time_length' => $this->getLengthOfTime($now),
                )
            );
        } else if ($sysConn->ErrorNo() === 1062) {
            wmSysLog('2700200600', $bid , $nid, 0, 'auto', $_SERVER['PHP_SELF'], 'edit board whisper(site:' . $sid . ', wid:' . $wid . ')');
            $result = array('code' => 0);
        }

        return $result;
    }

    /**
     * 刪除指定留言
     *
     * @param string $wid 留言流水號
     * @param string $username 使用者帳號
     */
    function delWhisper($wid, $username)
    {
        global $sysConn;

        // 取是否有該版特權
        // 取版號
        $rsWhisper = $this->getWhisper(array(), array($wid));
        foreach ($rsWhisper as $v) {
            $sid = $v[0]['sid'];
            $bid = $v[0]['board_id'];
            $nid = $v[0]['node'];
        }
        // 取是否有特權
        $updRight = ChkRight($bid);

        dbDel(
            'WM_bbs_whispers',
            sprintf(
                "wid= '%s' and (creator = '%s' or 1 = %s)",
                $wid,
                mysql_real_escape_string($username),
                $updRight
            )
        );

        if ($sysConn->Affected_Rows() === 1) {
            wmSysLog('2700200700', $bid , $nid, 1, 'auto', $_SERVER['PHP_SELF'], 'delete board whisper(site:' . $sid . ', wid:' . $wid . ')');
            $result = array('code' => 1);
        } else {
            wmSysLog('2700200700', $bid , $nid, 0, 'auto', $_SERVER['PHP_SELF'], 'delete board whisper(site:' . $sid . ', wid:' . $wid . ')');
            $result = array('code' => 0);
        }

        return $result;
    }

    /**
     * 取得指定時間的時間長度表示式
     *
     * @param string $time 指定年月日時分秒
     */
    function getLengthOfTime($time = '1980-01-01')
    {
        global $MSG, $sysSession;

        if (isset($time)) {
            // 現在減指定時間（小時）
            $diff = (time() - strtotime($time)) / (60 * 60);
            if ($diff <= 12) {
                $length = str_replace('%N', ceil($diff), $MSG['within%Nhour'][$sysSession->lang]);
            } else if ($diff <= 24) {
                $length = $MSG['within1day'][$sysSession->lang];
            } else if ($diff <= 24 * 7) {
                $length = $MSG['within1week'][$sysSession->lang];
            } else if ($diff <= 24 * 30) {
                $length = $MSG['within1month'][$sysSession->lang];
            } else if ($diff <= 24 * 91) {
                $length = $MSG['within3months'][$sysSession->lang];
            } else if ($diff <= 24 * 122) {
                $length = $MSG['within6months'][$sysSession->lang];
            } else {
                $length = $MSG['after6months'][$sysSession->lang];
            }
        } else {
            $length = null;
        }


        return $length;
    }

    /**
     * 取得學校討論區列表資訊（討論版名稱、主題數、回覆數、按讚數、最後文章時間）
     *
     * @param array $excBids 不包含的版號
     * @param boolean $extraFlag 額外資訊
     * @param array $bid 指定的版號
     */
    function getSchoolForumList($extraFlag = false, $sids = array(), $bids = array(), $excBids = array(),
        $curPage = 1, $perPage = 3, $sort = '', $order = '')
    {
        global $sysSession;
        // 指定學校
        if (count($sids) >= 1) {
            $schoolId = implode(',', $sids);
            $whr .= sprintf("`owner_id` IN (%s) AND ", $schoolId);
        } else {
            $whr .= sprintf(
                "owner_id = '%s' AND",
                $sysSession->school_id
            );
        }

        // 指定版號
        if (count($bids) >= 1) {
            $boardId = implode(',', $bids);
            $whr .= sprintf("`WM_term_subject`.`board_id` IN (%s) AND ", $boardId);
        }

        // 指定不要的版號
        if (count($excBids) >= 1) {
            $excBoardId = implode(',', $excBids);
            $whr .= sprintf("`WM_term_subject`.`board_id` NOT IN (%s) AND ", $excBoardId);
        }

        // 判斷權限
        $whr .= $this->schoolForumAcl();

        // 校正起始頁
        if ($curPage <= 0) {
            $curPage = 1;
        }

        // 排序 (預備用)
        if ($sort !== '') {
            $subWhere .= sprintf(' ORDER BY %s %s', $sort, $order);
        }

        $subWhere .= ' LIMIT ' . ($curPage-1) * $perPage . ',' . $perPage . ' ';

        $rs = dbGetStMr(
            'WM_term_subject left join WM_bbs_boards on WM_bbs_boards.board_id = WM_term_subject.board_id',
            "`WM_term_subject`.`board_id`, `bname`, `title`",
            $whr . $subWhere,
            ADODB_FETCH_ASSOC
        );

        $forums = array();
        if ($rs) {
            while (!$rs->EOF) {
                // 討論版名稱、主題數、回覆數、按讚數、最後文章時間
                $bid = sprintf("'%d'", mysql_real_escape_string($rs->fields['board_id']));
                // 排除指定不包含的版號
                if ($rs->fields['board_id'] >= 1) {
                    $forums[$bid]['board_id'] = $rs->fields['board_id'];

                    $multiCaption = getCaption($rs->fields['bname']);
                    $bname = $multiCaption[$sysSession->lang];
                    $forums[$bid]['board_name'] = $bname;
                    $forums[$bid]['title'] = $rs->fields['title'];

                    if ($extraFlag === true) {
                        // 取指定討論版的主題數、回覆數、按讚數、最後文章時間
                        $summary = $this->getForumSummaryByBid($bid);
                        $forums[$bid]['subject_cnt'] = $summary['subject_cnt'];
//                        $forums[$bid]['reply_cnt'] = $summary['reply_cnt'];
//                        $forums[$bid]['push_cnt'] = $summary['push_cnt'];
                        $forums[$bid]['read_cnt'] = $summary['read_cnt'];
                        $forums[$bid]['read_cnt_myself'] = $summary['read_cnt_myself'];
                        $forums[$bid]['read_flag'] = ($summary['subject_cnt'] + $summary['reply_cnt'] - $summary['read_cnt_myself'] >= 1) ? false : true;
                        $forums[$bid]['update_date'] = substr($summary['update_date'], 0, 16);
                        // 時間長度表示式，參考「MOOC-共通要件定義書」
                        $forums[$bid]['update_date_lengh'] = $this->getLengthOfTime($summary['update_date']);
                    }
                }

                $rs->MoveNext();
            }
        }

        return $forums;
    }

    /************************************************
     *       暫且置於此以後有用到時根據共通處修改      *
     ************************************************/

    /**
     * 取得夾檔連結
     *
     * @param string $attach 夾檔
     */
    function getFileLink($attach, $pre)
    {
        if (empty($attach)) return null;

        //設定單位
        $size_unit = array('Bytes','KB','MB','GB','TB','PB','EB','ZB','YB');

        $uri = substr($pre, strlen(sysDocumentRoot));
        $a = explode(chr(9), trim($attach));
        $r = '';
        for ($i=0; $i < count($a); $i+=2) {
            $flag = 0;
            /* 計算檔案大小 */
            $size = @filesize(sysDocumentRoot . $uri . $a[$i+1]);
            while ($size >= 1024) {
                $size = $size / 1024;
                $flag++;
            }
            /* 修正檔名過長? */
            if (mb_strlen($a[$i], 'utf-8') > 20) {
                $a1 = mb_substr($a[$i], 0, 20, 'utf-8') . '...' . mb_substr($a[$i], -7, 7, 'utf-8');
            } else {
                $a1 = htmlspecialchars($a[$i]);
            }
            $r .= sprintf('<span class="filePlayer"></span><i class="icon-file"></i><a href="%s" target="_blank" title="%s" class="attach-file-link">%s</a> <span>(%s)</span><BR />',
                $uri . $a[$i+1],
                htmlspecialchars($a[$i]),
                $a1,
                number_format($size, 0) . $size_unit[$flag]);
        }
        return $r;
    }

    /**
    *
    * 取得學校公告文章不需登入即可看到
    *
    * @param string $bid
    *        	討論版版號
    */
    function getSchoolAnnouncement($bid, $curPage, $perPage, $keyword = '', $sort = 'pt', $order = 'desc') {
            global $sysRoles, $sysSession;

            return $this->getBbsPosts ( $bid, array (), false, $curPage, $perPage, $keyword, $sort, $order );
    }

    /**
     * 取得討論版名稱
     *
     * @param string $bid 討論版編號
     */
    function getForumNameByBid($bid){
        global $sysSession;

        $whr = sprintf('`board_id` = %s', $bid);
        $binfo = dbGetStSr(
                'WM_bbs_boards',
                '`board_id`,`title`, bname',
                $whr,
                ADODB_FETCH_ASSOC
                );

        // 討論版名稱
        $multiCaption = getCaption($binfo['bname']);
        $bname = $multiCaption[$sysSession->lang];
        $binfo['bname'] = $bname;

        return $binfo;
    }

     /**
     * 取得討論版最新消息文章
     *
     * @param string $bid 討論版版號
     * @param integer $curPage 第幾頁
     * @param integer $perPage 每頁幾筆
     * @param string $keyword 關鍵字
     * @param string $sort 排序欄位
     * @param string $order 升冪、降冪
     */
    function getNewsForumData($bid)
    {
        global $sysRoles, $sysSession, $sysConn;
        //$sysConn->debug=true;
        // TODO：根據有無登入判斷是否有權限
        $forumList = $this->getBbsPosts(
            $bid,
            array(),
            '0',
            1,
            10,
            '',
            'pt',
            'desc'
        );

        $data = $forumList['data'];

        // 取讀取數
        // 組討論版編號與文章編號陣列
        $node = array();
        if (count($data) >= 1) {
            foreach ($data as $k => $v) {
                $node[] = array('bid' => $v['boardid'], 'nid' => $v['node']);
            }

            /*$read = $this->getRead($bid, $node);
            foreach ($read as $k => $v) {
                $data[$k]['read'] = $read[$k]['read'];
            }*/

            // 取按讚數
            $push = $this->getPush($bid, $node);
            foreach ($push as $k => $v) {
                $data[$k]['push'] = $push[$k]['push'];
            }

            // 取回覆數
            $reply = $this->getReply($bid, $node);
            foreach ($reply as $k => $v) {
                $data[$k]['reply'] = $reply[$k]['reply'];
            }

            $forumList['data'] = $data;
        }

        return $forumList;
    }

     /**
      * 取得討論版編號歸屬的課程編號
      *
      * @param int $bid 討論版編號
      * @return int $cid 課程編號
      */
    function getCidByBid($bid) {
        $whr = sprintf('`board_id` = %d', $bid);

        $cid = dbGetStSr(
                'WM_term_subject',
                '`course_id`',
                $whr,
                ADODB_FETCH_ASSOC
                );

        return $cid['course_id'];
    }

    function cpy($source, $dest)
    {
        if (is_dir($source)) {
            $dir_handle = opendir($source);
            while ($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (is_dir($source . "/" . $file)) {
                        if (!is_dir($dest . "/" . $file)) {
                            mkdir($dest . "/" . $file);
                        }
                        cpy($source . "/" . $file, $dest . "/" . $file);
                    } else {
                        copy($source . "/" . $file, $dest . "/" . $file);
                    }
                }
            }
            closedir($dir_handle);
        } else {
            copy($source, $dest);
        }
    }

    /*
     * 轉貼文章
     */
    function setRepost($sid, $bid, $nid, $toSid, $toCid, $toBid) {

        global $sysSession, $sysRoles, $MSG, $sysConn;

        // 驗證存取權
        if (!aclCheckRole($sysSession->username, $sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher'] | $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $sysSession->course_id)) {
            $result['code'] = -1;
            $result['message'] = $MSG ['msg_move_4'] [$sysSession->lang];

            return $result;
        }

        if (!aclCheckRole($sysSession->username, $sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher'] | $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $toCid)) {
            $result['code'] = -2;
            $result['message'] = $MSG ['msg_move_4'] [$sysSession->lang];

            return $result;
        }

        // 取文章
        $rsNode = $this->getBbsPosts($bid, array($nid), '1');

        $toBid = stripcslashes($toBid);
//        echo '<pre>';
//        var_dump($rsNode);
//        var_dump($toBid);
//        echo '</pre>';

        if ($rsNode['total_rows'] === '1') {

            // 取得目前板中最大的 node
            list($mnode) = dbGetStSr('WM_bbs_posts', 'MAX(node)', "board_id = {$toBid} and length(node) = 9", ADODB_FETCH_NUM);

            // 產生本篇的 node
            $nnode = empty($mnode) ? '000000001' : sprintf("%09d", $mnode + 1);

            $subject = '['.$MSG ['repost'] [$sysSession->lang].']'.$rsNode['data'][$nid]['subject'];
            $content = $rsNode['data'][$nid]['postcontent'];
            $attach = $rsNode['data'][$nid]['attach'];
            $ll = array(
                'Big5'   => 1,
                'en'	 => 2,
                'GB2312' => 3,
                'EUC-JP' => 4,
                'user_define' => 5
            );

            $fields = 'board_id, node,site, pt, poster, realname, email, homepage, subject, content, attach,lang';
            $values = "$toBid, '$nnode', $toSid".
                      ", NOW(), '$sysSession->username', '$sysSession->realname', ".
                      "'$sysSession->email', '$sysSession->homepage ', '$subject', '$content',".
                      ($attach ? "'$attach'" : "NULL") . "," . $ll[$sysSession->lang];

            dbNew('WM_bbs_posts', $fields, $values);

            // 取 id流水號
            $newNid = $sysConn->Insert_ID();

            if ($newNid >= '0') {
                // 複製檔案
                // base/10001/course/10058136/board/1000003971/000000011/WM5abf756cac447.jpg
//                echo '<pre>';
//                var_dump($nid, $toBid);
//                echo '</pre>';
                $source = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/%10d/%s/', $sysSession->school_id, $sysSession->course_id, 'board', $bid, substr($nid, 11, 10));
                $target = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/%10d/%s/', $sysSession->school_id, $toCid, 'board', str_replace("'", '', $toBid), $nnode);
                @mkdir($target, 0777, TRUE);
                if (empty($_COOKIE['VKcxpNwu5XXAHfSf']) === FALSE) {
                    echo '<pre>';
                    var_dump($source, $target);
                    echo '</pre>';
                }
                $this->cpy($source, $target);

                $result['code'] = 1;
            } else {
                $result['code'] = -3;
            }
        } else {
            $result['code'] = -4;
        }
        return $result;
    }
}
