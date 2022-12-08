<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/lib_lcms.php');
    require_once(sysDocumentRoot . '/lang/learn_path.php');
    require_once(sysDocumentRoot . '/lib/Mobile_Detect.php');
    
    // 判斷使用者是否使用行動裝置
    $detect = new Mobile_Detect;
    $isMobile = '0';
    if($detect->isMobile() && !$detect->isTablet()){
        $isMobile = '1';
    }

    header('P3P: CP=CAO PSA OUR');
    
    // 將 XPath 分離，以利 reuse
    function getXPath() {
        global $sysSession, $sysConn, $MSG;

        list($xml) = dbGetStSr('WM_term_path', 'content', "course_id={$sysSession->course_id} order by serial desc", ADODB_FETCH_NUM);
        if ($sysConn->ErrorNo()) {
            die(sprintf('Query Error: %d => %s', $sysConn->ErrorNo(), $sysConn->ErrMsg()));
        }

        $xml = preg_replace(
            array('/<resource( [^>]+)?>\s*(<file [^>]*>)*\s*<\/resource>/sU','/\bxsi:schemaLocation\s*=\s*"[^"]*"/'),
            array('<resource\1></resource>',''),
            mb_convert_encoding($xml, 'UTF-8', 'UTF-8')
        ); // 去掉 <resource><file>

        if (empty($xml)) {
            wmSysLog('1900200100', $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $MSG['node_error'][$sysSession->lang]);
            die('<manifest><organizations default="'.$sysSession->course_id.'"><organization identifier="'.$sysSession->course_id.'"><title>' . $MSG['node_error1'][$sysSession->lang] . '</title></organization></organizations><resources /></manifest>');
        } elseif (!($xmlDoc = domxml_open_mem(preg_replace('/xmlns\s*=\s*"[^"]+"/', '', $xml, 1)))) {
            wmSysLog('1900200100', $sysSession->course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], $MSG['catalog_error'][$sysSession->lang]);
            die('<manifest><organizations default="'.$sysSession->course_id.'"><organization identifier="'.$sysSession->course_id.'"><title>' . $MSG['catalog_error1'][$sysSession->lang] . '</title></organization></organizations><resources /></manifest>');
        }
        return xpath_new_context($xmlDoc);
    }

    function getResourceHref($rid) {
        global $ctx;
        $xrs = $ctx->xpath_eval('//manifest/resources/resource[@identifier="' . $rid . '"]');

        if (count($xrs->nodeset) <= 0) {
            return '';
        }

        $node = $xrs->nodeset[0];
        $href = $node->get_attribute('href');

        $lcmsHost = sysLcmsHost . (substr(sysLcmsHost, -1) === '/' ? '' : '/');
        $map = array(
            $lcmsHost . 'courses/view/' => $lcmsHost . 'courses/play/',
            $lcmsHost . 'asset/detail/' => $lcmsHost . 'asset/play/',
            $lcmsHost . 'asset/view/'   => $lcmsHost . 'asset/play/',
            $lcmsHost . 'unit/view/'    => $lcmsHost . 'unit/play/',
        );
        $href = str_replace(array_keys($map), $map, $href);
        return $href;
    }
    
    // 取得節點裡 LCMS 的素材ID
    function getAssetId($rid) {
        global $ctx;
        $rtn_asset = '';
        if ($rid == null || $rid == '') {
            $xrs = $ctx->xpath_eval('//manifest/resources/resource');
        } else {
            $xrs = $ctx->xpath_eval('//manifest/resources/resource[@identifier="' . $rid . '"]');
        }
        if (count($xrs->nodeset) <= 0) {
            return '';
        }
        $lcmsHost = sysLcmsHost . (substr(sysLcmsHost, -1) === '/' ? '' : '/');
        $map = array(
            $lcmsHost . 'asset/detail/' => '',
            $lcmsHost . 'asset/view/'   => '',
            $lcmsHost . 'unit/view/'    => '',
        );
        
        foreach($xrs->nodeset as $content){
            $aid = str_replace(array_keys($map), $map, $content->get_attribute('href'));
            $rtn_asset[] = intval($aid);
        }
        
        return implode(",", $rtn_asset);
    }
    
    
    
    
    
    
    
    
    
    
    
    if (empty($_COOKIE['showmeiframelink']) === FALSE) {
        echo '<pre>';
        var_dump('$_GET[\'rid\']', $_GET['rid']);
        echo '</pre>';
    }    
    if ($_GET['rid']) {
        $rid = $_GET['rid'];
        
        // 動作  (action 已被 app 使用，改用 motion)
        $motion = ($_GET['motion'] !== null) ? $_GET['motion'] : '';
        // APP讀取LCMS教材，須給予課程編號
        $courseId = substr($rid, 4, 8);
        if (($motion === 'review' || $_GET['action'] === 'app') && $courseId >= 10000001 && strlen($courseId) === 8) {
            $sysSession->course_id = $courseId;
        } 

        $ctx = getXPath();
        $item   = $ctx->xpath_eval("//item[@identifierref='$rid']");
        $item   = $item->nodeset[0];
        if ($motion === 'review') {
            if (isset($item)) {
                $sco_id = $item->get_attribute('identifier');
                // $screenshot = ($_GET['screenshot'] == 0) ? '0' : '1';
                // $startTime = ($_GET['screenshot'] !== null) ? "{$_GET['stime']}" : '0';
                $where = sprintf('`note_id` = %d AND `sco_id` = "%s" AND `course_id` = %d',
                                    intval($_GET['nid']),
                                    $sco_id,
                                    $sysSession->course_id
                                );
                // 累加複習次數
                dbSet('`WM_user_note`', '`review_cnt`=`review_cnt`+1', $where);
                // 抓取該筆記的截取時間、素材網址
                list($startTime, $assetUrl) = dbGetStSr('`WM_user_note`',
                                                        '`point_time`, `url`',
                                                        $where, ADODB_FETCH_NUM);
                $data = getLcmsVerifyData($sysSession->course_id, $otherData=array('sco_id' => $sco_id, 'screenshot' => '0', 'starttime' => $startTime));
            } else {
                // 學習節點已無此教材
                die($MSG['no_material'][$sysSession->lang]);
            }        
        } else {
            $sco_id = $item->get_attribute('identifier');
            $screenshot = '0';
            if (defined('enableQuickReview') && enableQuickReview == true) {
                $screenshot = (isset($_GET['screenshot']) && $_GET['screenshot'] == 0) ? '0' : '1';
            }
            $data = getLcmsVerifyData($sysSession->course_id, $otherData=array('sco_id'=> $sco_id, 'screenshot' => $screenshot));
        }

        $enc = '';
        if ($data !== false) {
            $key = 'wmpro_lcms_pqal' . $data['ticket'];
            $enc = sysNewEncode(serialize($data), $key, true, MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        } else {
            $data = array(
                'idx'      => $_COOKIE['idx'],
                'teachers' => array(),
                'ticket'   => ''
            );
        }

        /* [MOOC](B) # 為取得自我評量所需 LCMS ID 改寫 2014/01/15 By Spring */
        if (empty($_COOKIE['showmeiframelink']) === FALSE) {
            echo '<pre>';
            var_dump('$motion', $motion);
            echo '</pre>';
        }
        if ('exam' === $motion) {
            $type = $_GET['type'];
            $lcmsHost = sysLcmsHost . (substr(sysLcmsHost, -1) === '/' ? '' : '/');
            $href = $lcmsHost . 'asset/exam/';
            // 自我評量   lcms domain /asset/exam/(0:素材, 1:單元)/題數
            if ($type === 'u') { // 取單元的自我評量
                $asset_id = getAssetId($_GET['rid']);
                $href .= '1/'.(($_GET['num'] == '')? 1 : $_GET['num']);
            } else if ($type === 'a') { // 取素材的自我評量
                $asset_id = getAssetId('');
                $href .= '0/'.(($_GET['num'] == '')? 1 : $_GET['num']);
            }
            $href .= '?token=' . $data['ticket'] . '&idx=' . $_COOKIE['idx'] . '&data=' . $enc;
        } else if ('review' === $motion) {
            $href = $assetUrl;
            if ($href !== '') {
                $href .= '?token=' . $data['ticket'] . '&idx=' . $_COOKIE['idx'] . '&data=' . $enc;
            }
        } else {
            $href = getResourceHref($_GET['rid']);
            if (empty($_COOKIE['showmeiframelink']) === FALSE) {
                echo '<pre>';
                var_dump('getResourceHref', $href);
                echo '</pre>';
            }
            if ($href !== '') {
                $href .= '/?token=' . $data['ticket'] . '&idx=' . $_COOKIE['idx'] . '&data=' . $enc;
            }
        }
        /* [MOOC](E) #  */

    } else if ($_GET['href']) {
        $href = sprintf('%s&idx=%s&data=%s&m=%s', $_GET['href'], $_GET['idx'], $_GET['data'], $isMobile);
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>LCMS</title>
    
    <style type="text/css">
        html, body {
            /* width: 100%;
            height: 100%;*/
            margin: 0;
            padding: 0;
        }

        iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
            padding: 0;
            margin: 0;
        }
    </style>
    <script src="/public/js/third_party/crypto-js/3.1.2/rollups/aes.js"></script>
    <script src='/lib/jquery/jquery.min.js'></script>
    <script src="lcms.js?<?php echo time();?>"></script>
</head>
<body>
<?php
    if (empty($_COOKIE['showmeiframelink']) === FALSE) {
        echo '<pre>';
        var_dump($href);
        echo '</pre>';
    }

    if ($data === false) {
        echo '<h1>' . $MSG['no_permission'][$sysSession->lang] . '</h1>';
    } else {
        if ($motion === 'exam') {
            // 自我評量
            echo '<form action="' . $href . '" name="referer" method="post">
                <input type="hidden" name="exam_id" value="'. $asset_id .'"/>
            </form>';
            echo '<script>document.referer.submit();</script>';
        } else {
            echo '
            <script>            
                var lcmsRoot = \'' . sysLcmsHost . '\';
                function receiveMessage(event) {
                        
                    if (window.console) {console.log("wm-receiveMessage", event.origin);}
                    if (window.console) {console.log("lcmsRoot", lcmsRoot);}
                    if (event.origin === lcmsRoot) {
                    
                        function touchfullscreen() {
                            if (!top.document.fullscreenElement &&
                                !top.document.mozFullScreenElement && !top.document.webkitFullscreenElement && !top.document.msFullscreenElement ) {
                                launchFullscreen();
                            } else {
                                exitFullscreen();
                            }
                        }

                        function launchFullscreen() {
                            if (top.document.documentElement.requestFullscreen) {

                                top.document.documentElement.requestFullscreen();
                            } else if (top.document.documentElement.mozRequestFullScreen) {

                                top.document.documentElement.mozRequestFullScreen();
                            } else if (top.document.documentElement.webkitRequestFullscreen) {

                                top.document.documentElement.webkitRequestFullScreen();
                            // IE11不支援全螢幕
                            } else if (top.document.documentElement.msRequestFullscreen) {
                                top.document.documentElement.msRequestFullscreen();
                            }
                        }

                        function exitFullscreen() {
                            if (top.document.exitFullscreen) {
                                top.document.exitFullscreen();
                            } else if (top.document.mozCancelFullScreen) {
                                top.document.mozCancelFullScreen();
                            } else if (top.document.webkitExitFullscreen) {
                                top.document.webkitExitFullscreen();
                            } else if (top.document.msExitFullscreen) {
                                top.document.msExitFullscreen();
                            }
                        }

                        top.document.addEventListener(\'webkitfullscreenchange\', fullscreenChange);
                        top.document.addEventListener(\'mozfullscreenchange\', fullscreenChange);
                        top.document.addEventListener(\'fullscreenchange\', fullscreenChange);
                        top.document.addEventListener(\'MSFullscreenChange\', fullscreenChange);

                        function fullscreenChange() {
                            if (!top.document.fullscreenElement &&
                                !top.document.mozFullScreenElement && !top.document.webkitFullscreenElement && !top.document.msFullscreenElement ) {
                                parent.document.getElementById("envClassRoom").cols = "312,*";
                                parent.document.getElementById("envStudent").rows = "93,*";
                                parent.document.getElementById("envMooc").cols = "250,*";
                            } else {
                                parent.document.getElementById("envMooc").cols = "0,*";
                                parent.document.getElementById("envStudent").rows = "0,*";
                                parent.document.getElementById("envClassRoom").cols = "0,*";
                            }
                        }
                        
                        // 解密資料
                        // if (window.console) {console.log(\'event.data\' , event.data);}      
                        var decrypted = CryptoJS.AES.decrypt(event.data.toString(), "lcmsPostMessageLms");
                        // if (window.console) {console.log(\'decrypted\', CryptoJS.enc.Utf8.stringify(decrypted));}
                        var decryptedMsg = CryptoJS.enc.Utf8.stringify(decrypted).split(\'|\');
                        var msg = decryptedMsg[0];
                        // if (window.console) {console.log(\'decryptedMsg\' , msg);}
                        
                        // 分解資料 
                        msg = JSON.parse(msg);
                        // if (window.console) {console.log(\'parseMsg\' , msg);}
                        if (window.console) {console.log(\'parseMsg-action_type\' , msg["action_type"]);}
                        // if (window.console) {console.log(\'parseMsg-action_id\' , msg["action_id"]);}
                        
                        switch (msg["action_type"]) {
                            case "wm_frame":
                                if (window.console) {console.log(\'收到來自lcmsl的監聽結果：\' , msg["action_id"]);}
                                switch (msg["action_id"]) {
                                    case "enhancedfullscreen":
                                        touchfullscreen();
                                        break;

                                    case "enhancedrestore":
                                        exitFullscreen();
                                        break;
                                }
                                break;
                                
                            case "read_video":
                                if (window.console) {console.log(\'收到來自lcmsl的監聽結果：\' , msg["action_id"]);}
                                    
                                // if (window.console) {console.log(\'msg\' , msg);}   
                                
                                //var rid = \'' . htmlspecialchars($_GET['rid']) . '\';
                                //msg["rid"] = rid;
                                    
                                // if (window.console) {console.log(\'msg-rid\' , msg);} 
                                
                                setReadVideoLog(msg);
                                    
                                break;
                        }
                    } else {
                        return;
                    }
                }
                window.addEventListener("message", receiveMessage, false);
            </script>';
            // 觀看素材            
            echo '<iframe allowfullscreen="allowfullscreen" webkitallowfullscreen="" mozallowfullscreen="" frameborder="0" src="' . $href . '"></iframe>';
        }
    }
?>
</body>
</html>