<?php
    /**************************************************************************************************
     *                                                                                                *
     *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
     *                                                                                                *
     *      Programmer: Wiseguy Liang                                                                 *
     *      Creation  : 2003/09/23                                                                    *
     *      work for  :                                                                               *
     *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
     *                                                                                                *
     **************************************************************************************************/

    // header('Content-Disposition: attachment; filename="manifest.xml"');

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
    require_once(sysDocumentRoot . '/lang/learn_path.php');
        require_once(sysDocumentRoot . '/lib/lib_lcms.php');
    header('Content-type: application/xml');

    /**
     * 第一種方法，直接 load XML 檔
     */
    // if(!$xmlDoc = domxml_open_file('./imsmanifest_knowledgepaced.xml')) {
    //     die("Error while parsing the document\n");
    // }
    /**
     * 第二種方法，去掉 default namespace 宣告
     */
    // $xml = @implode('', @file('./imsmanifest_knowledgepaced.xml'));


    if ($_SERVER['argv'][1]) {
        $search_course_id = $_SERVER['argv'][1];
    } else {
        $search_course_id = $sysSession->course_id;
    }

    if ($_SERVER['argv'][0])
        list($xml) = dbGetStSr('WM_term_path', 'content', "course_id={$search_course_id} and serial={$_SERVER['argv'][0]}", ADODB_FETCH_NUM);
    else
        list($xml) = dbGetStSr('WM_term_path', 'content', "course_id={$search_course_id} order by serial desc", ADODB_FETCH_NUM);
    if ($sysConn->ErrorNo()) die(sprintf('Query Error: %d => %s', $sysConn->ErrorNo(), $sysConn->ErrorMsg()));
    $xml = preg_replace(array('/<resource( [^>]+)?>\s*(<file [^>]*>)*\s*<\/resource>/sU','/\bxsi:schemaLocation\s*=\s*"[^"]*"/'),
                        array('<resource\1></resource>',''),
                        mb_convert_encoding($xml, 'UTF-8', 'UTF-8')); // 去掉 <resource><file>

    if (empty($xml)){
        wmSysLog('1900200100', $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $MSG['node_error'][$sysSession->lang]);
        die('<manifest><organizations default="'.$sysSession->course_id.'"><organization identifier="'.$sysSession->course_id.'"><title>' . $MSG['node_error1'][$sysSession->lang] . '</title></organization></organizations><resources /></manifest>');
    }
    elseif (!($xmlDoc = domxml_open_mem(preg_replace('/xmlns\s*=\s*"[^"]+"/', '', $xml, 1))) ){
        wmSysLog('1900200100', $sysSession->course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], $MSG['catalog_error'][$sysSession->lang]);
        die('<manifest><organizations default="'.$sysSession->course_id.'"><organization identifier="'.$sysSession->course_id.'"><title>' . $MSG['catalog_error1'][$sysSession->lang] . '</title></organization></organizations><resources /></manifest>');
    }
    $root = $xmlDoc->document_element();
    $ctx = xpath_new_context($xmlDoc);
    // 去掉 default namespace 宣告，就不必執行此行
    // $ctx->xpath_register_ns('default','http://www.imsglobal.org/xsd/imscp_v1p1');

    // 準備將 resource.href 加密 (3DES)
    $decDev  = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CFB, '');

    // 準備將 resource.href 加密 (AES)
    // $decDev = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CFB, '');

    $key     = substr(md5(sysTicketSeed . $_COOKIE['idx']), 0, mcrypt_enc_get_key_size($decDev));
    $skey    = md5(sysTicketSeed . $_COOKIE['idx']);
    $iv_size = mcrypt_enc_get_iv_size($decDev);

    // 去掉 default namespace 宣告，就不必加上 default:
    // $xrs = $ctx->xpath_eval('/default:manifest/default:resources/default:resource');
    $xrs = $ctx->xpath_eval('//manifest/resources/resource');
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
     if(is_array($xrs->nodeset) && count($xrs->nodeset) >= 1) {
        $lcmsNodes = array();
    $lcmsCourseNodes = array();
    $lcmsUnitNodes = array();
    foreach($xrs->nodeset as $resource){
        $attr = $resource->get_attribute('xml:base');
        $href = $resource->get_attribute('href');
        $rid  = $resource->get_attribute('identifier');
        $type = $resource->get_attribute('type');

        if ((($attr == '') && ($href == '')) || $href == 'about:blank')
        {
            $ret = $ctx->xpath_eval('//item[@identifierref="' . $rid . '"]');
            if (is_array($ret->nodeset) && count($ret->nodeset))
                foreach($ret->nodeset as $node)
                    $node->remove_attribute('identifierref');
            $resource->unlink_node();
            continue;
        }

        if (is_array($resource->child_nodes()) && count($resource->child_nodes())) foreach($resource->child_nodes() as $file) $file->unlink_node(); // 去掉 <resource><file>
        /*if (preg_match("/\bfetchWMinstance\(([0-9]+),'?([0-9A-Za-z]+)'?\)/", $resource->get_attribute('href'), $regs)) {
            if ($regs[1] == 3 || $regs[1] == 7) {
                $ret = $ctx->xpath_eval('//item[@identifierref="'.$resource->get_attribute('identifier').'"]');
                if (is_array($ret->nodeset) && count($ret->nodeset))
                    foreach($ret->nodeset as $node)
                        $node->set_attribute('target', '_blank');
            }
        }*/

        if (in_array(pathinfo($resource->get_attribute('href'), PATHINFO_EXTENSION), array('doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'rtf')) && preg_match("/(iPod|iPad|iPhone)/i", $userAgent)){
            $ret = $ctx->xpath_eval('//item[@identifierref="'.$resource->get_attribute('identifier').'"]');
                if (is_array($ret->nodeset) && count($ret->nodeset))
                    foreach($ret->nodeset as $node)
                        $node->set_attribute('target', '_blank');
        }

        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        mcrypt_generic_init($decDev, $key, $iv);
        if ($attr) {
            $resource->remove_attribute('xml:base');
            $resource->set_attribute('xml:base', sysNewEncode($attr, $skey));
        }
        $type = 'webcontent';


        // 全校性問卷增加傳遞課程編號
        preg_match('/^\/Q\/[\d]{5}\/[\d]+\/[\d]+\/[\d]+\/[0-9a-z]+$/', $href);
        if (preg_match('/^\/Q\/[\d]{5}\/[\d]+\/[\d]+\/[\d]+\/[0-9a-z]+$/', $href)) {
            $href = $href . '/' . $sysSession->course_id;
        }


        if (sysLcmsHost !== '') {
            $isLcms = ($type === 'lcms') || (@strpos(strtolower($href), strtolower(sysLcmsHost)) === 0);
        }
        // 檢查是否啟用 LCMS
        if (defined('sysLcmsEnable') && sysLcmsEnable && $isLcms) {
            $lcmsHerf = $href;
            $href = '/learn/path/lcms.php?rid=' . $rid;

            if (preg_match('/\/([courses|unit]*)\/[play|view]*\/([\d]*)$/', $lcmsHerf, $matches)) {
                $lcmsNodes[] = array('lcmslink' => $lcmsHerf, 'lcmstype' => $matches[1], 'sco' => $rid, 'wmlink' => $href);
//                echo '<pre>';
//                var_dump('類別', $matches[1]);
//                echo '</pre>';
                switch($matches[1]) {
                    case 'courses':
                        $lcmsCourseNodes[$matches[2]] = array('lcmslink' => $lcmsHerf, 'lcmstype' => $matches[1], 'sco' => $rid, 'wmlink' => $href);
                        break;

                    case 'unit':
                        $lcmsUnitNodes[$matches[2]] = array('lcmslink' => $lcmsHerf, 'lcmstype' => $matches[1], 'sco' => $rid, 'wmlink' => $href);
                        break;
                }
            }
        }

        // *046174 將 URL 轉成使用 Youtube 自己內嵌網頁 Begin
        $urlYoutubeEmbed = 'https://www.youtube.com/embed/';
        if (preg_match('|//youtu.be/(.*)$|', $href, $matches)) {
            $href = 'https://www.youtube.com/embed/'.$matches[1].'?autoplay=1';
//            $resource->set_attribute('type', 'youtube');
        } else if (preg_match('|//www.youtube.com/watch\?(.*)$|', $href, $matches)) {
            $arr_p = explode('&', $matches[1]);

            $arr_other = array();
            $v         = '';
            $list      = '';

            if (is_array($arr_p) === TRUE) {
                foreach ($arr_p as $key => $value) {
                    if (strpos($value, 'v=') !== false) {
                        $arr_v = explode('=', $value);
                        $v     = $arr_v[1];
                    }

                    if (strpos($value, 'list=') !== false) {
                        $list = $value;
                    }

                }
                $href = $urlYoutubeEmbed . $v;
                if ($list != '') {
                    $href .= '?' . $list;
                }
            }
        }
        // *046174 將 URL 轉成使用 Youtube 自己內嵌網頁 End

        $resource->set_attribute('href', sysNewEncode($href, $skey));
        mcrypt_generic_deinit($decDev);
    }
     }
    mcrypt_module_close($decDev);

    /*
     * 展開LCMS課程與單元節點，替換掉XML
     */
    if (is_array($lcmsNodes) && count($lcmsNodes) >= 1) {
//        echo '<pre>';
//        var_dump('lcms節點',$lcmsNodes, 'lcms課程節點', $lcmsCourseNodes, 'lcms單元節點', $lcmsUnitNodes);
//        echo '</pre>';

        $screenshot = '0';
        if (defined('enableQuickReview') && enableQuickReview == true) {
            $screenshot = (isset($_GET['screenshot']) && $_GET['screenshot'] == 0) ? '0' : '1';
        }
        $data = getLcmsVerifyData($sysSession->course_id, $otherData = array('sco_id' => 'I_'.$rid,'screenshot' => $screenshot));

        if (empty($_COOKIE['showmeinfo']) === FALSE) {
            echo '<pre>';
            var_dump('learn\path\SCORM_loadCA.php $data', $data);
            echo '</pre>';
        }

        $key = 'wmpro_lcms_pqal' . $data['ticket'];
        $enc = sysNewEncode(serialize($data), $key, true, MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $lcmsTicket = '/?token=' . $data['ticket'] . '&idx=' . htmlspecialchars($_COOKIE['idx']) . '&data=' . $enc;

        if (empty($_COOKIE['showmeinfo']) === FALSE) {
            echo '<pre>';
            var_dump('$lcmsTicket', $lcmsTicket);
            echo '</pre>';
            // die('123');
        }

        global $sysRoles;
        $xml = $xmlDoc->dump_mem(true);

        // 課程
        if (is_array($lcmsCourseNodes) && count($lcmsCourseNodes) >= 1) {

            $lcmsCourseIds = array_keys($lcmsCourseNodes);
//            echo '<pre>';
//            var_dump($lcmsCourseIds);
//            var_dump($lcmsCourseIds[0]);
//            echo '</pre>';

            // 取第一個課程的教材擁有者
            $rs = getReomteData(
                sysLcmsHost . '/lms/getCoursesInfoById/' . $lcmsCourseIds[0]
            );
            $lcmsCourseInfo = json_decode($rs, true);
//            echo '<pre>';
//            var_dump('第一個課程的教材擁有者', $lcmsCourseInfo);
//            echo '</pre>';

            // 取本課老師
            $rs = dbGetStMr(
               'WM_term_major',
               'username, role',
                'course_id = ' . $sysSession->course_id . ' AND role & ' . ($sysRoles['teacher'] | $sysRoles['assistant']),
                ADODB_FETCH_ASSOC
            );
            if ($rs !== FALSE) {
                while($fields = $rs->FetchRow()){
                    $teachers[] = $fields['username'];
                }
            }
//            echo '<pre>';
//            var_dump('取老師群', $teachers);
//            echo '</pre>';

            // 取交集後第一個老師
            $teacher = current(array_intersect($teachers, $lcmsCourseInfo['data']));
//            echo '<pre>';
//            var_dump('取交集後第一個老師', $teacher);
//            echo '</pre>';

            $rs = getReomteData(
                sysLcmsHost . '/lms/getCourseInfo/multiple', array(
                'username' => $teacher,
                'courses' => $lcmsCourseIds
            ));
            $lcmsCourseAssets = json_decode($rs, true);

//            echo '<pre>';
//            var_dump('lcms課程資訊', $lcmsCourseAssets);
//            echo '</pre>';

            $wmCourseAssets = array();
            foreach ($lcmsCourseAssets as $c => $cu) {
                $unitAssets = '';
                foreach ($cu as $k => $v) {
                    $wmCourseAssets['organizations'][$c] = '';
                    foreach ($v['asset'] as $i => $j) {
                        $wmCourseAssets['organizations'][$c] .= sprintf('<item identifier="I_%s" identifierref="%s"><title>%s</title></item>', $lcmsCourseNodes[$c]['sco'] . '_' . $j['aid'], $lcmsCourseNodes[$c]['sco'] . '_' . $j['aid'], htmlspecialchars(strip_tags($j['subject'])));
                        $wmCourseAssets['resources'][$c] .= sprintf('<resource identifier="%s" type="webcontent" href="%s"/>', $lcmsCourseNodes[$c]['sco'] . '_' . $j['aid'], sysNewEncode('/learn/path/lcms.php?href=' . sysLcmsHost . '/asset/play/' . $j['aid'] . $lcmsTicket, $skey));
                    }

//                    echo '<pre>';
//                    var_dump('單元XML', sprintf('<title>%s</title><item identifier="I_%s"></item>', $v['unitname'], $lcmsCourseNodes[$c]['sco'] . '_' . $k));
//                    echo '</pre>';

//                    echo '<pre>';
//                    var_dump('素材XML', $wmCourseAssets);
//                    echo '</pre>';

                    $unitAssets = $unitAssets . sprintf('<item identifier="I_%s"><title>%s</title>', $lcmsCourseNodes[$c]['sco'] . '_unit_' . $k, htmlspecialchars(strip_tags($v['unitname']))) . $wmCourseAssets['organizations'][$c] . '</item>';
//                    echo '<pre>';
//                    var_dump('所有單元+素材XML'$unitAssets);
//                    echo '</pre>';
                }

                // <item identifier="I_SCO_10024605_153922787989395">.*</item>
                $xml = preg_replace('/(<item identifier="I_' . $lcmsCourseNodes[$c]['sco'] . '")( identifierref="' . $lcmsCourseNodes[$c]['sco'] . '">)([.\s\S\w\W]*?[\r\n]*?)(<\/title>)/', '$1>$3$4' . "\r\n        " . $unitAssets . "\r\n      ", $xml);
                $xml = preg_replace('/(<\/resources>)/', "\r\n    " . $wmCourseAssets['resources'][$c] . '$1', $xml);

//                echo '<pre>';
//                var_dump($xml);
//                echo '</pre>';
            }
        }

        // 單元
        if (is_array($lcmsUnitNodes) && count($lcmsUnitNodes) >= 1) {

            $lcmsUnitIds = array_keys($lcmsUnitNodes);
//            echo '<pre>';
//            var_dump($lcmsUnitIds);
//            var_dump($lcmsUnitIds[0]);
//            echo '</pre>';

            // 取第一個單元的教材擁有者
            $rs = getReomteData(
                sysLcmsHost . '/lms/getUnitInfoById/' . $lcmsUnitIds[0]
            );
            $lcmsUnitInfo = json_decode($rs, true);
//            echo '<pre>';
//            var_dump('第一個單元的教材擁有者', $lcmsUnitInfo);
//            echo '</pre>';

            // 取本課老師
            $rs = dbGetStMr(
               'WM_term_major',
               'username, role',
                'course_id = ' . $sysSession->course_id . ' AND role & ' . ($sysRoles['teacher'] | $sysRoles['assistant']),
                ADODB_FETCH_ASSOC
            );
            if ($rs !== FALSE) {
                while($fields = $rs->FetchRow()){
                    $teachers[] = $fields['username'];
                }
            }
//            echo '<pre>';
//            var_dump('取老師群', $teachers);
//            echo '</pre>';

            // 取交集後第一個老師
            $teacher = current(array_intersect($teachers, $lcmsUnitInfo['data']));
//            echo '<pre>';
//            var_dump('取交集後第一個老師', $teacher);
//            echo '</pre>';

            $rs = getReomteData(
                sysLcmsHost . '/lms/getUnitInfo/multiple', array(
                'username' => $teacher,
                'units' => $lcmsUnitIds
            ));
            $lcmsUnitAssets = json_decode($rs, true);

//            echo '<pre>';
//            var_dump($lcmsUnitAssets);
//            echo '</pre>';

            $wmUnitAssets = array();

            foreach ($lcmsUnitAssets as $k => $v) {
                foreach ($v as $i => $j) {
                    $wmUnitAssets['organizations'][$k] .= sprintf('<item identifier="I_%s" identifierref="%s"><title>%s</title></item>', $lcmsUnitNodes[$k]['sco'] . '_' . $j['aid'], $lcmsUnitNodes[$k]['sco'] . '_' . $j['aid'], htmlspecialchars(strip_tags($j['subject'])));
                    $wmUnitAssets['resources'][$k] .= sprintf('<resource identifier="%s" type="webcontent" href="%s"/>', $lcmsUnitNodes[$k]['sco'] . '_' . $j['aid'], sysNewEncode('/learn/path/lcms.php?href=' . sysLcmsHost . '/asset/play/' . $j['aid'] . $lcmsTicket, $skey));
                }
//                echo '<pre>';
//                var_dump('素材XML', $wmUnitAssets);
//                echo '</pre>';

                // <item identifier="I_SCO_10024605_153922787989395">.*</item>
                $xml = preg_replace('/(<item identifier="I_' . $lcmsUnitNodes[$k]['sco'] . '")( identifierref="' . $lcmsUnitNodes[$k]['sco'] . '">)([.\s\S\r\n]*?)(<\/title>)/', '$1>$3$4' . "\r\n        " . $wmUnitAssets['organizations'][$k] . "\r\n      ", $xml);

                $xml = preg_replace('/(<\/resources>)/', "\r\n    " . $wmUnitAssets['resources'][$k] . '$1', $xml);
//                echo '<pre>';
//                var_dump(htmlentities($xml));
//                echo '</pre>';
            }



        }
        echo $xml;
    } else {
        echo $xmlDoc->dump_mem(true);
    }
?>
