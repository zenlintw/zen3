<?php
    /**************************************************************************************************
     *                                                                                                *
     *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
     *                                                                                                *
     *      Programmer: Wiseguy Liang                                                                 *
     *      Creation  : 2003/09/24                                                                    *
     *      work for  :                                                                               *
     *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
     *                                                                                                *
     **************************************************************************************************/

    ignore_user_abort(true);
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
    require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
    require_once(sysDocumentRoot . '/lang/learn_path.php');
    require_once(sysDocumentRoot . '/learn/path/qti_lib.php');
    require_once(sysDocumentRoot . '/lib/lib_lcms.php');
    require_once(sysDocumentRoot . '/lib/Mobile_Detect.php');

    $sysSession->cur_func = '1900200100';
//    $sysSession->env = 'learn';
    $sysSession->restore();

    $urlchars = array('%' => '%2525',
                      '#' => '%2523',
                      ' ' => '%20',
                      '"' => '%22',
                      '&' => '%26',
                      "'" => '%27',
                      '+' => '%2B',
                      '=' => '%3D',
                      '?' => '%3F',
                      '/' => '%2F');

    $course_id = trim(sysNewDecode($_POST['course_id']));

    /**
     * ?????????????????? URL
     *
     * @param    string    $url    ???????????? url
     * @param   bool    $last   ???????????????????????????????????? (??????????????????)
     * @return    string            ???????????? url
     */
    function decrypt_url($url, $last=false)
    {
            global $urlchars;
            static $decDev, $key, $iv_size, $first;

            $skey = md5(sysTicketSeed . $_COOKIE['idx']);
            $enc = explode('@', $url, 2);

            $base = trim($enc[0]);

            // ????????????????????????????????????
            if ($base !== '') {
                $base = trim(sysNewDecode($enc[0], $skey));
                $base = substr($base, 0, -1);
                $base = $base . '/';
            }

            $enc[1] = trim($enc[1]);
            $href = trim(sysNewDecode($enc[1], $skey));
            if ($href === FALSE) {
                die('incorrect url.');
            }
            /*
            $bol = preg_match(
            '/^(javascript:|http:\/\/|https:\/\/|ftp:\/\/|\/learn\/path\/lcms\.php\?rid\=)/',
            $href,
            $match
            );
            if (!sysLcmsEnable || $bol !== 1) {
            $href = rawurlencode($href);
            }
            */
            return $base . $href;
    }


    /**
     * =========================== ??????????????? ==============================
     */

    if (empty($_POST['href'])){
        die('No information.');
    }
    else{
        // ????????? href ?????? (3DES)
        $origin_href  = decrypt_url($_POST['href'], $_POST['prev_node_id'] ? false : true);
        $isOpenWindow = $_POST['isOpenWindow'] == 'true' ? true : false;
        // ???????????? node
        if ($_POST['prev_node_id'])
        {
            $prev_href = decrypt_url($_POST['prev_href'], true);
            $cur_time  = time();
            // $pre_time = strtotime($_POST['begin_time']);
            $pre_time = ($_POST['begin_time'] =='') ? ($cur_time-4) : strtotime($_POST['begin_time']);
            if (($cur_time - $pre_time) > pathNodeTimeShortlimit)
            {
                if (($cur_time - $pre_time) > pathNodeTimeLonglimit)
                {
                    $cur_time = $pre_time + pathNodeTimeLonglimit;
                }
                dbSet('WM_record_reading', 
                    'over_time = NOW()',
                    sprintf("course_id = %d AND username = '%s' AND begin_time = '%s' AND activity_id = '%s' AND (UNIX_TIMESTAMP(now())- UNIX_TIMESTAMP(over_time)) <= (%d * 60)", $course_id, $sysSession->username, htmlspecialchars($_POST['begin_time']), htmlspecialchars($_POST['prev_node_id']), 3));                
            }
        }
        else
            chkSchoolId('WM_record_reading');

        if (preg_match("/\bfetchWMinstance\((\d+),'?(\w+)'?\)/", $origin_href, $regs))
            switch(intval($regs[1]))
            {
                case 2:
                case 3:
                case 4:
                    $type = $regs[1] == '2' ? 'homework' : ($regs[1] == '3' ? 'exam' : 'questionnaire');
                    $canDo = check_qti_can_do($type, $regs[2]);
                    echo '<style>body{font-family: "???????????????", Arial, Helvetica, sans-serif;font-size:18px}</style>';
                    switch ($canDo) {
                        case -1 :
                            die('<span>'.$MSG['enter_'.$type.'_acl_error'][$sysSession->lang].'</span>'); break;
                        case -2 :
                            die('<span>'.$MSG['enter_'.$type.'_error'][$sysSession->lang].'</span>'); break;
                        case -3 :
                            die('<span>Program Error!</span>'); break;
                        case -4 :
                            $mobile_tip = str_replace("%TYPE%",$MSG[$type . '_title'][$sysSession->lang],$MSG['mobile_tip'][$sysSession->lang]);
                            echo '<link href="/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />';
                            die('<div class="content" style="width: 90%;margin: 20px;">
                         <div class="alert alert-danger" style="font-size:16px;color: #000000;text-align: left;font-weight: unset;line-height: 1.5em;opacity: .65;"><span class="lcms-red-starmark" style="color:red">* </span>' . $mobile_tip . '</div>
                     </div>'); break;
                        default :
                            if ($type == 'exam') {
                                                            $key = md5(sysTicketSeed . htmlspecialchars($_COOKIE['idx']));
                                                            header(sprintf('Location: /learn/exam/exam_list.php?exam_id=%s', sysNewEncode($regs[2], $key)));
                                                        }
                            else {
                                $detect = new Mobile_Detect;
                                if($detect->isMobile()) {
                                     printf('<script>window.open("%s");</script>', $canDo);
                                     exit;
                                } else {
                                                            header(sprintf('Location: %s', $canDo));}
                                                        }

                    }
                    exit;
                    break;
                case 5:    // subject discussion ???????????????
                    if ($bid = $sysConn->GetOne("select S.board_id from WM_term_subject as S,WM_bbs_boards as B
                                                 where S.course_id={$course_id} and S.node_id={$regs[2]}
                                                 and S.state != 'disable'
                                                 and S.board_id=B.board_id
                                                 and
                                                 ( (isnull(B.open_time) or B.open_time <= now() or B.open_time = '0000-00-00 00:00:00')
                                                    and (isnull(B.close_time) or B.close_time > now() or B.close_time = '0000-00-00 00:00:00')
                                                    or ( not isnull(B.share_time) and B.share_time != '0000-00-00 00:00:00' and B.share_time < now()))
                                                ")
                       )
                    {
                        include_once(sysDocumentRoot . '/lib/lib_encrypt.php');
                        if (strpos($_SERVER['PHP_SELF'], '/learn/scorm/') === false)
                            printf('<script>parent.s_sysbar.goBoard("%s");</script>', sysEncode($bid));
                        else
                            printf('<script>parent.parent.s_sysbar.goBoard("%s", "s_main");</script>', sysEncode($bid));
                        exit;
                    }
                    else
                        die($MSG['enter_forum_error'][$sysSession->lang]);
                    break;
                case 6:    // board ?????????
                    if ($owner_id = $sysConn->GetOne("select owner_id from WM_bbs_boards
                                                      where board_id={$regs[2]} and (owner_id={$course_id}  or (length(owner_id)=16 and left(owner_id,8)={$course_id})) and
                                                      ( (isnull(open_time) or open_time <= now() or open_time = '0000-00-00 00:00:00')
                                                         and (isnull(close_time) or close_time > now() or close_time = '0000-00-00 00:00:00')
                                                         or ( not isnull(share_time) and share_time != '0000-00-00 00:00:00' and share_time < now()))
                                                     ")
                       )
                    {
                        if ($owner_id != $course_id) {    // ????????????????????????,?????????????????????????????????????????????????????????
                            if (strlen($owner_id) != 16 ||
                                 ( !$sysConn->GetOne('select username from WM_student_div where course_id = ' . $course_id . ' and team_id = ' . substr($owner_id,8,4) . ' and group_id=' . substr($owner_id,12,4) . ' and username="' . $sysSession->username . '"') &&
                                    !aclCheckRole($sysSession->username,$sysRoles['teacher'],$course_id))
                               )
                               die($MSG['enter_forum_permission_denied'][$sysSession->lang]);
                        }
                        include_once(sysDocumentRoot . '/lib/lib_encrypt.php');
                        if (strpos($_SERVER['PHP_SELF'], '/learn/scorm/') === false)
                            printf('<script>parent.s_sysbar.goBoard("%s");</script>', sysEncode($regs[2]));
                        else
                            printf('<script>parent.parent.s_sysbar.goBoard("%s", "s_main");</script>', sysEncode($regs[2]));
                        exit;
                    }
                    else
                        die($MSG['enter_forum_error'][$sysSession->lang]);
                    break;
                case 7:
                    if ($owner = $sysConn->GetOne("select owner from WM_chat_setting
                                                   where rid='{$regs[2]}' and (owner='{$course_id}' or left(owner, instr(owner, '_')-1)='{$course_id}')
                                                   and (isnull(open_time) or open_time='0000-00-00 00:00:00' or open_time <= now())
                                                   and (isnull(close_time) or close_time='0000-00-00 00:00:00' or close_time > now())
                                                   and state != 'disable'")
                       )
                    {
                        if ($owner != $course_id) {    // ????????????????????????,?????????????????????????????????????????????????????????
                            if (count($owner = explode('_', $owner)) != 3 ||
                                 ( !$sysConn->GetOne('select username from WM_student_div where course_id = ' . $course_id . ' and team_id = ' . $owner[1] . ' and group_id=' . $owner[2] . ' and username="' . $sysSession->username . '"') &&
                                     !aclCheckRole($sysSession->username,$sysRoles['teacher'],$course_id))
                                )
                                die($MSG['enter_chatroom_permission_denied'][$sysSession->lang]);
                        }

                        printf('<script>parent.parent.s_sysbar.goChatroom("%s");</script>', $regs[2]);
                        exit;
                    }
                    else
                        die($MSG['enter_chatroom_error'][$sysSession->lang]);
                    break;
                case 8:
                    break;
            }
        // ?????????
        elseif ($origin_href == 'about:blank' || $origin_href == '')
            echo '';
        // ????????? html ?????????????????????????????????
        elseif ((preg_match('!^\w+://!', $origin_href) && !preg_match('!^\w+://[^?]*\.html?([?#][^?#]*)?$!', $origin_href)) ||
                (strpos($origin_href, '/') === 0 && strpos($origin_href, '/base/' . $sysSession->school_id . '/content/') !== 0)
               ){
            if (($char = un_detect_chars($origin_href)) !== false && $char != 'en')
            {
                header('Content-Type: text/html; Charset=' . $char);
                echo '<script>location.replace("', addslashes(iconv('UTF-8', $char, $origin_href)), '");</script>';
            }
            else
            {
                // ?????? wm learning hash cooke ??? lcms
                if (mb_strpos($origin_href, '/learn/path/lcms.php?rid=', 0, 'utf-8') === 0) {
                    // ?????? wm learning hash cooke ??? lcms
                    $pathWmCookieHash2Lcms = setWmLearningHashCookie('auto');
                }
                echo '<img src="' . $pathWmCookieHash2Lcms . '" style="display: none;"><script>location.replace("', addslashes($origin_href), '");</script>';
            }
        }
        // ?????? html ??????
        elseif (preg_match('!^\w+://!', $origin_href))
        {
            if (($char = un_detect_chars($origin_href)) !== false && $char != 'en')
            {
                header('Content-Type: text/html; Charset=' . $char);
                echo '<script>location.replace("', addslashes(iconv('UTF-8', $char, $origin_href)), '");</script>';
            }
            else
            {
                echo '<script>location.replace("', addslashes($origin_href), '");</script>';
            }
        }else{
            //??????????????????????????????
            $rawUrl = urldecode($origin_href);
            if( preg_match("|^/base/[\d]+/content/[\d]+/|",$rawUrl) ){
                if (preg_match("/\.(pdf)$/", $rawUrl)) {
                    $id = $rawUrl;
                    if ($_POST['is_download'] == 'false') {
                        $id = $id .'+1';
                    } else {
                        $id = $id .'+0';
                    }
                    $key = md5(sysTicketSeed . htmlspecialchars($_COOKIE['idx']));
                    $id = sysNewEncode($id, $key, true);
                    echo sprintf('<script>location.replace("viewPDF.php?id=%s");</script>',$id);
                } else {
                    echo "<script>location.replace('$rawUrl')</script>";
                }
                
                exit;
            }

            $localpath = sprintf('/base/%05d/course/%08d/content/', $sysSession->school_id, $course_id);

            // ?????????
            if (strpos(urldecode($origin_href), '/base/' . $sysSession->school_id . '/content/') === 0)
            {
                $dir_name = sysDocumentRoot;
                $isContent = true;
            }
            else
            {
                $dir_name = sysDocumentRoot . $localpath;
                $isContent = false;
            }

            $argument = '';
            if (preg_match('/(.+\.[a-z0-9]+)([?#][^?#]*)$/', $origin_href, $regs))
            {
                $origin_href = $regs[1];
                $argument = $regs[2];
            }

            $adj_filename = language_adjust($dir_name, $origin_href) . $argument;

            if ($adj_filename)
            {
                $userAgent = $_SERVER['HTTP_USER_AGENT'];
                $detect = new Mobile_Detect;
                if($detect->isMobile()) {
                    // ???????????? - phone or tablet
                    if(eregi('\.(mp4|mov|webmv|ogv|mp3|ogg|wav)$', $origin_href)){
                        echo sprintf('<script>location.replace("m_player.php?file=%s");</script>',
                            ($isContent ? '' : $localpath) . rawurlencode(rawurlencode($adj_filename))
                        );
                    }else if (preg_match("/\.(pdf)$/", $origin_href)){
                        $id = $adj_filename;
                        if ($_POST['is_download'] == 'false') {
                            $id = $id .'+1';
                        } else {
                            $id = $id .'+0';
                        }
                        $key = md5(sysTicketSeed . htmlspecialchars($_COOKIE['idx']));
                        $id = sysNewEncode($id, $key, true);
                        echo sprintf('<script>location.replace("viewPDF.php?id=%s");</script>',$id);
                    }else if (strpos($origin_href, '%') === FALSE){
                        if (strpos($origin_href, ',')) {
                            echo '<script>location.replace("', ($isContent ? '' : $localpath) . $adj_filename, '");</script>';
                            exit;
                        }
                        $adj_filename = str_replace('%2F', '/', $origin_href.rawurlencode($argument));
                        // MIS#23451 ???????????? URL ?????? html ???????????? by Small 2011/12/14
                        $adj_filename = str_replace('%23', '#', $origin_href.rawurlencode($argument));

                        if ((preg_match('/\.(html|htm|bmp|gif|ico|jpg|jpeg|png|svg)$/i', $adj_filename)) || 
                            (in_array(pathinfo($origin_href, PATHINFO_EXTENSION), array('doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'rtf')) && preg_match("/(iPod|iPad|iPhone)/i", $userAgent))
                        ){
                            // ?????? ????????????????????????????????? ??????/??????/??????.htm ??????????????????????????????
                            $urlSetting = explode('/', $adj_filename);
                            $adj_filename = '';
                            $urlParts = array();
                            foreach ($urlSetting as $v) {
                                // ??????EDGE?????????????????????????????????????????? rawurlencode
                                $urlParts[] = rawurlencode($v);
                            }
                            // ???????????????????????????
                            $adj_filename = implode('/', $urlParts);

                            $path = ($isContent ? '' : $localpath) . ($adj_filename) . (isset($_POST['parameter']) ? $_POST['parameter'] : '');
                            header('Content-Disposition: filename=' . basename($adj_filename));
                            header('Content-Type: '); // ?????? MIME
                            header('Location: ' . $path);
                        } else {
                            $path = ($isContent ? '' : $localpath) . $adj_filename . (isset($_POST['parameter']) ? $_POST['parameter'] : '');
                            $key = md5(sysTicketSeed . htmlspecialchars($_COOKIE['idx']));
                            $path = sysNewEncode($path, $key, true);
                            header('Location: download_preview.php?path=' . rawurlencode($path));
                            exit;
                        }
                    }
                    else
                    {
                        $f = str_replace(array_keys($urlchars), array_values($urlchars), adjust_char($adj_filename));
                        if ($language == 'en' || $language == 'UTF-8')
                            echo '<script>location.replace("', ($isContent ? '' : $localpath) . addslashes($f), '");</script>';
                        else
                            echo '<script>location.replace("', ($isContent ? '/' : $localpath) . addslashes(iconv($language, 'UTF-8', $f)), '");</script>';
                    }
                    /* ????????? office ?????????????????????????????????????????????????????? IE7 ??? webfolder ??????????????????????????? cookie */
                    /* ???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????? */
                    if (preg_match('/\.(ppt|doc|xls)$/i', $adj_filename))
                        setcookie('idx', $_COOKIE['idx'], time()+3600, dirname(($isContent ? '' : $localpath) . $adj_filename));
                }else{
                    // desktop PC ??????
                    if(eregi('\.(mp4|mov)$', $origin_href)){
                        echo sprintf('<script>location.replace("player.php?file=%s");</script>',
                            ($isContent ? '' : $localpath) . rawurlencode(rawurlencode($adj_filename))
                        );
                    } else if (eregi('\.(mp3)$', $origin_href)){
                         echo sprintf('<audio src="%s" preload="auto" controls></audio>',
                            ($isContent ? '' : $localpath) . $adj_filename
                        );
                    } else if (preg_match("/\.(pdf)$/", $origin_href,$matches) && !preg_match("/(iPod|iPad|iPhone|android)/i", $userAgent)){
                        $id = $adj_filename;
                        if ($_POST['is_download'] == 'false') {
                            $id = $id .'+1';
                        } else {
                            $id = $id .'+0';
                        }
                        $key = md5(sysTicketSeed . htmlspecialchars($_COOKIE['idx']));
                        $id = sysNewEncode($id, $key, true);
                        echo sprintf('<script>location.replace("viewPDF.php?id=%s");</script>',$id);
                    // swf??????IE11????????????????????????????????????????????????????????????????????????
                    } else if (strpos($origin_href, '%') === FALSE && !eregi('\.(wmv|avi|rm|rmvb|mov|wma|mpg|mpeg)$', $origin_href)) {
                        // MIS#21100 ?????? - ???????????? URL ??????????????? by Small 2011/5/18
                        // $adj_filename = str_replace('%2F', '/', rawurlencode($origin_href . $argument));
                        if (strpos($origin_href, ',')) {
                            echo '<script>location.replace("', ($isContent ? '' : $localpath) . $adj_filename, '");</script>';
                            exit;
                        }
                        $adj_filename = str_replace('%2F', '/', $origin_href . rawurlencode($argument));
                        // MIS#23451 ???????????? URL ?????? html ???????????? by Small 2011/12/14
                        $adj_filename = str_replace('%23', '#', $origin_href . rawurlencode($argument));

                        if (preg_match('/\.(pdf|html|htm|bmp|gif|ico|jpg|jpeg|png|svg|mht)$/i', $adj_filename)) {
                            // ?????? ????????????????????????????????? ??????/??????/??????.htm ??????????????????????????????
                            $urlSetting   = explode('/', $adj_filename);
                            $adj_filename = '';
                            $urlParts     = array();
                            foreach ($urlSetting as $v) {
                                // ??????EDGE?????????????????????????????????????????? rawurlencode
                                $urlParts[] = rawurlencode($v);
                            }
                            // ???????????????????????????
                            $adj_filename = implode('/', $urlParts);

                            $path = ($isContent ? '' : $localpath) . ($adj_filename) . (isset($_POST['parameter']) ? $_POST['parameter'] : '');
                            header('Content-Disposition: filename=' . basename($adj_filename));
                            header('Content-Type: '); // ?????? MIME
                            header('Location: ' . $path);
                        } else {
                            $path = ($isContent ? '' : $localpath) . $adj_filename . (isset($_POST['parameter']) ? $_POST['parameter'] : '');
                            $key  = md5(sysTicketSeed . htmlspecialchars($_COOKIE['idx']));
                            $path = sysNewEncode($path, $key, true);
                            header('Location: download_preview.php?path=' . rawurlencode($path));
                            exit;
                        }
                    } else {
                        $f = str_replace(array_keys($urlchars), array_values($urlchars), adjust_char($adj_filename));
                        if ($language == 'en' || $language == 'UTF-8')
                            echo '<script>location.replace("', ($isContent ? '' : $localpath) . addslashes($f), '");</script>';
                        else
                            echo '<script>location.replace("', ($isContent ? '/' : $localpath) . addslashes(iconv($language, 'UTF-8', $f)), '");</script>';
                    }

                    /* ????????? office ?????????????????????????????????????????????????????? IE7 ??? webfolder ??????????????????????????? cookie */
                    /* ???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????? */
                    if (preg_match('/\.(ppt|doc|xls)$/i', $adj_filename)){
                        setcookie('idx', $_COOKIE['idx'], time() + 3600, dirname(($isContent ? '' : $localpath) . $adj_filename));
                    }
                }
            }else{
                die($MSG['file_exist'][$sysSession->lang]);
            }
        }
    }