<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/lib_lcms.php');

    if (!sysLcmsEnable) {
        header('HTTP/1.1 404 Not Found');
        exit;
    }

    // 網址 GET 加值參數
    $otherGet = '';
    // 動作
    $action = $_GET['action'];
    $cid = (isset($_GET['cid']) === true) ? $_GET['cid'] : $sysSession->course_id;
    if ($action === 'login') {
        $lcms = sysLcmsHost . '/lms/login';
        if (isset($_GET['nodir']) && '1' == $_GET['nodir']) {
            // 登入 LCMS 後不導回首頁，避免載入 LCMS 首頁資料
            $otherGet .= '&redirect=0';
        }
    } else if ($action === 'import') {
        $lcms = sysLcmsHost . '/lms/import';
        
        // 單元素材選擇模式
        $mode = '0';
        if (isset($_GET['mode']) === true && $_GET['mode'] === '1') {
            $mode = '1';
        }

        $importType = $_GET['type'];
        if (isset($importType)) {
            $isUnit = ($importType == 'unit') ? '1' : '0';
            $isAsset = ($importType == 'asset') ? '1' : '0';
            $isCourse = ($importType == 'course') ? '1' : '0';
            $otherData = array('show_contents'  =>  '0',    //顯示教學資源庫
                            'show_favorites'    => '0',    //顯示我的收藏
                            'show_resources'    => '1',    //顯示我的資源
                            'show_course'         => $isCourse,    //顯示課程區塊
                            'show_unit'         => $isUnit,    //顯示單元區塊
                            'show_asset'        => $isAsset,   //顯示素材區塊
                            'sel_mode'         => $mode // 選擇模式：單選或多選，如果從「新增模式」，則單筆；若從「匯入教材資源庫（多筆）」，則可選多筆，預設是多筆
                        );
        } else {
            $otherData = array(
                            'sel_mode' => $mode // 選擇模式：單選或多選，如果從「新增模式」，則單筆；若從「匯入教材資源庫（多筆）」，則可選多筆，預設是多筆
                        );
        }
    // 我的資源/ 我的公開資源
    } else if ($action === 'resources' || $action === 'publicresource') {
        $lcms = sysLcmsHost . '/lms/resources';
    } else {
        die('Access Deny (action error) !!');
    }
    
    // 取得 LCMS 的 Token (Begin)
    if (null != $otherData) {
        $data = getLcmsVerifyData($sysSession->course_id, $otherData);
    } else {
        $data = getLcmsVerifyData($cid);
    }
    
    // 我的公開資源註記
    if ($action === 'publicresource') {
        $data['publicresource'] = '1';
    }

//    // 確認LCMS PHP版本
//    $lcmsPhpVersion = getReomteData(
//        sysLcmsHost . '/lms/getPhpVersion'
//    );
    
    $enc = '';
    if ($data !== false) {
        $key = 'wmpro_lcms_pqal' . $data['ticket'];

//        echo '<pre>';
//        var_dump('lcmsPhpVersion', $lcmsPhpVersion);
//        var_dump('wmPhpVersion', PHP_VERSION);
//        echo '</pre>';
        
//        if (version_compare(PHP_VERSION, '7.1.0') >= 0 && version_compare($lcmsPhpVersion, '7.1.0') >= 0) {
//            $enc  = opensslEncrypt(serialize($data), $key);
//        } else{
            $enc = sysNewEncode(serialize($data), $key, true, MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
//        }
//        echo '<pre>';
//        var_dump('lcms.php', 'key', $key, '$data', $data);
//        echo '</pre>';
//        die();
    } else {
        $data = array(
            'idx'      => $_COOKIE['idx'],
            'teachers' => array(),
            'ticket'   => ''
        );
    }
    // 取得 LCMS 的 Token (End)

    $token = $data['ticket'];
    $get = sprintf('?token=%s&idx=%s&data=%s'.$otherGet, $token, $_COOKIE['idx'], $enc);
//    $get = sprintf('?token=%s&idx=%s&data=%s&phpversion=%s'.$otherGet, $token, $_COOKIE['idx'], $enc, (min(PHP_VERSION, $lcmsPhpVersion)));
    
//    echo '<pre>';
//    var_dump($get);
//    echo '</pre>';

    // 因應 IE $_SERVER['HTTP_REFERER'] 無法識別HEADER轉向，改用FORM SUBMIT方法
    // echo '<form action="'.$lcms . $get . '" name="referer" method="post"></form>';
    // echo '<script>document.referer.submit();</script>';
    echo '<script>location.href="' . $lcms . $get . '";</script>';