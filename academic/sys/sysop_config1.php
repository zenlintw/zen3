<?php
	/**
	 * 常數定義檔
	 *
	 * @since   2005/05/20
	 * @author  ShenTing Lin
	 * @version $Id: sysop_config1.php,v 1.1 2010/02/24 02:38:45 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lang/sysop_config.php');
    require_once(sysDocumentRoot . '/lang/mooc.php');
    /*
     * savePortal()
     * portal 參數儲存
     * @param string  $sch_dbpre  學校DB前綴
     * @param array   $dataArray  (portal_id, key, value)
     * @return X
     */
    function savePortal($sch_dbpre, $dataArray) {
        foreach($dataArray as $k => $v) {
            foreach($v as $key => $val) {
                dbNew("`{$sch_dbpre}`.`WM_portal`", "`portal_id`, `key`, `value`", sprintf("'%s','%s','%s'", $k, $key, $val));
            }
        }
    }  
	// 判斷是否為管理者 BEGIN
	chkSchoolId('WM_manager');
	$cm = $sysConn->GetOne("select count(*) from WM_manager where username = '{$sysSession->username}' and (school_id = " . intval($_POST['sid']) . " or level & {$sysRoles['root']})");
	if ($cm == 0)
		die($MSG['illegal_access'][$sysSession->lang]);

	// 判斷是否為管理者 END
	$file_content = '';
    // 給予 checkbox 未勾選的直預設值 0
    $chkDefault = array('sysEnableAppServerPush', 'sysEnableAppCourseExam', 'sysEnableAppBackgroundLogo');
    foreach($chkDefault as $v) {
        if (!isset($_POST[$v])) {
            $_POST[$v] = '0';
        }
    }

	// 多站台主機設定 (wm3update)
	if (!empty($_POST['MulitServer_content'])) {
        $allowips = explode("\n",$_POST['MulitServer_content']);
        if (!is_array($allowips)){
            $_POST['MulitServer_content'] = '';
        }else{
            for($i=0, $size=count($allowips); $i<$size; $i++) {
                $allowips[$i] = trim($allowips[$i]);
            }
            $_POST['MulitServer_content'] = implode(';', $allowips);
        }
    }
	// 多站台主機設定 (wm3update)

    // 停機公告
    if (!empty($_POST['system_pause_allowip'])){
        $allowips = explode("\n",$_POST['system_pause_allowip']);
        if (!is_array($allowips)){
            $_POST['system_pause_allowip'] = $_SERVER['REMOTE_ADDR'];
        }else{
            for($i=0, $size=count($allowips); $i<$size; $i++) {
                $allowips[$i] = trim($allowips[$i]);
            }
            if (!in_array($_SERVER['REMOTE_ADDR'], $allowips)){
                $allowips[] = $_SERVER['REMOTE_ADDR'];
            }
            $_POST['system_pause_allowip'] = implode(';', $allowips);
        }
    }

    if (!empty($_POST['system_pause_content'])) {
        $_POST['system_pause_content'] = str_replace("\n",'[newline]',$_POST['system_pause_content']);
    }

    $system_pause_file = sysDocumentRoot . '/base/' . intval($_POST['sid']) . '/system_pause.txt';
    if ($_POST['system_pause'] == 'Y'){
        if (empty($_POST['system_pause_allowip'])){
            $_POST['system_pause_allowip'] = $_SERVER['REMOTE_ADDR'];
        }

        if (empty($_POST['system_pause_content'])) {
            $_POST['system_pause_content'] = $MSG['td_system_pause_content_default'][$sysSession->lang];
        }

        $system_pause_data = array(
            'start_time' => $_POST['system_pause_start_time'].':00',
            'end_time' => $_POST['system_pause_end_time'].':00',
            'allow_ip' => $_POST['system_pause_allowip'],
            'content' => $_POST['system_pause_content'],
        );
        configWrite($system_pause_file, serialize($system_pause_data));
    }else{
        if (file_exists($system_pause_file)){
            unlink($system_pause_file);
        }
    }

	foreach ($_POST as $field => $val){        
		if (!is_array($val) && false !== strpos($val, '@')) $val = str_replace('@', '(at)', $val);
		if ($field == 'Access_constant')
		{
			/*
			$trans = array(';' => ',', ' ' => ',');
			$temp_str = strtr(trim($val), $trans);
			$file_content .= $field . '@' . $temp_str . "\r\n";
			*/
		}
		elseif ($field == 'sysAvailableChars')
		{
			$file_content .= $field . '@' . implode(',', $val) . "\r\n";
		}
		elseif ($field == 'enablePaid')
		{
			$master_file_content .= $field . '@' . trim($val) . "\r\n";
		}
		elseif ($field == 'love_active')
		{
			$file_content .= $field . '@' . trim($val) . "\r\n";
		}
		else
		{
            if ($field == 'is_portal') {
                if ($val == '0') {
                    // 非入口網校時，品牌大街開關設為 false
                    $sid = intval($_POST['sid']);
                    $cur_school = sysDBprefix . $sid;
                    dbSet("`{$cur_school}`.`WM_portal`", "`value`='false'", "`portal_id` = 'content_sw' AND `key` = 'franchisee' ");
                } else if ($val == '1') {
                    // 在 /base 底下建立 master 來儲存所有學校設定
                    $master_file_content .= 'portal_school' . '@' . intval($_POST['sid']) . "\r\n";
                }
            } else if ($field == 'sysEnableMooc') {
                $sid = intval($_POST['sid']);
                $cur_school = sysDBprefix . $sid;
                if ($val=='0') {
                    // 關閉 mooc 時，`student_mooc`維持原狀，`social_share`, `canreg_ext` 清空
                    $sqls = "social_share = '',  canReg_ext = ''";
                    dbSet('`CO_school`', $sqls, "`school_id` = ".intval($_POST['sid']));
                } else if ($val=='1') {
                    $sch_name = dbGetOne('`WM_school`', 'school_name', "`school_id` = ".$sid);
                    // 開啟 mooc 時，將預設值新增到 CO_school
                    if (dbGetStSr('`CO_school`', '*', "`school_id` = ".$sid)==false) {
                        $sShare = 'FB,PLURK,TWITTER,LINE,WECHAT';
                        $canregE = '';
                        dbNew(
                            '`CO_school`',
                            "`school_id`, `social_share`, `canReg_ext`, `banner_title1`, `banner_title2`, `banner_title3`, `footer_about`, `footer_contact`, `footer_faq`, `footer_info`",
                            sprintf(
                                "%d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'",
                                $sid, $sShare, $canregE, 
                                $sch_name, $MSG['adv_subtitle'][$sysSession->lang], $MSG['adv_description_1'][$sysSession->lang],
                                $MSG['about_default_url'][$sysSession->lang], $MSG['contact_default_url'][$sysSession->lang], $MSG['faq_default_url'][$sysSession->lang],
                                $MSG['footer_info'][$sysSession->lang]
                            )
                        );
                        // 儲存 portal 預設值
                        $pArray = array(
                            'content_sw'    => array(
                                    'searchbar'     =>  'true',
                                    'franchisee'    =>  'false',
                                    'courselist'    =>  'true',
                                    'forum'         =>  'false',
                                    'custom1'       =>  'false',
                                    'custom2'       =>  'false'
                            ),
                            'content_pri'   => array(
                                    'franchisee'    =>  '1',
                                    'courselist'    =>  '2',
                                    'forum'         =>  '3',
                                    'custom1'       =>  '4',
                                    'custom2'       =>  '5'
                            ),
                            'quick_sw'      => array(
                                    'onlinehelp'       =>  'false'
                            ),
                            'quick_pri'     => array(
                                    'onlinehelp'       =>  '1'
                            ),
                            'represent'     => array('pic_path'         =>  "/base/{$sid}/door/tpl/rep_img.png"),
                            'brand'         => array('pic_path'         =>  "/base/{$sid}/door/tpl/brand_logo.png"),
                            'theme'         => array('style'            =>  'orange'),
                            'franchisee'    => array('title'            =>  "品牌大街"),
                            'courselist'    => array('title'            =>  "熱門課程"),
                            'forum'         => array('title'            =>  "社區互動"),
                            'custom1'       => array(
                                    'title'             =>  '自訂區域1',
                                    'pic_path'          =>  "",
                                    'pic_style'         =>  "full",
                                    'url_type'          =>  "3",
                                    'url_default'       =>  "",
                                    'url'               =>  ""
                            ),
                            'custom2'       => array(
                                    'title'             =>  '自訂區域2',
                                    'pic_path'          =>  "",
                                    'pic_style'         =>  "full",
                                    'url_type'          =>  "3",
                                    'url_default'       =>  "",
                                    'url'               =>  ""
                            ),
                            'onlinehelp'    => array('title'       =>  "線上客服"),
                            'ads001'        => array(
                                    'pic_path'      =>  "/base/{$sid}/door/tpl/ad001.png",
                                    'url_type'      =>  '3',
                                    'url_default'   =>  '',
                                    'url'           =>  ''
                            ),
                            'ads002'        => array(
                                    'pic_path'      =>  "/base/{$sid}/door/tpl/ad002.png",
                                    'url_type'      =>  '3',
                                    'url_default'   =>  '',
                                    'url'           =>  ''
                            ),
                            'ads003'        => array(
                                    'pic_path'      =>  "/base/{$sid}/door/tpl/ad003.png",
                                    'url_type'      =>  '3',
                                    'url_default'   =>  '',
                                    'url'           =>  ''
                            ),

                        );
                        if ($_POST['is_portal'] == '1') {
                            $pArray = array('theme' =>  array('sub_style'   =>  'blue'));
                        } else {
                            if ($_POST['is_independent'] == '1') {
                                $pArray = array('theme' =>  array('style'   =>  'black'));
                            }
                        }
                        savePortal($cur_school, $pArray);

                    } else {
                        // CO_school 已有資料時，將`student_mooc`設為1，`social_share`設為全部， `canreg_ext` 清空
                        $sqls = "student_mooc = 1, social_share = 'FB,PLURK,TWITTER,LINE,WECHAT',  canReg_ext = ''";
                        dbSet('`CO_school`', $sqls, "`school_id` = ".$sid);
                    }
                    // WM_school : feedback : NULL, theme : default, guest : Y, guestLimit : 0
                    $sqls = "feedback=NULL, theme='default', " .
                            "guest='Y', guestLimit=0 ";
                    dbSet('`WM_school`', $sqls, "`school_id` = ".$sid);
                    // 如果/base/$sid/door/tpl/裡沒有圖，將預設圖複製過去  : icon, logo, banner_bg, banner_logo
                    $source = sysDocumentRoot . "/theme/default/learn_mooc/";
                    $target = sysDocumentRoot . "/base/".$sid."/door/tpl/";
                    // 如果沒有 tpl 目錄
                    if (!is_dir($target)){
                        mkdir($target, 0755);
                    }
                    // 要複製的檔案列表
                    $files = array(
                            'logo.png',
                            'banner_bg.png',
                            'ad001.png',
                            'banner_logo.png',
                            'icon.ico'
                    );
                    foreach ($files as $v) {
                        if(!is_file($target . $v)) {
                            @copy($source . $v, $target . $v);
                         }
                    }                                
                }

            } else if ($field === 'sysEnableAppCourseExam' || $field === 'sysEnableAppQuestionnaire') {
                require_once(sysDocumentRoot . '/app_install/qti_patch.php');
            }
                        
			$file_content .= $field . '@' . trim($val) . "\r\n";
		}
	}

    function configWrite($file, $content) {
        // 編碼
        $encrypt_data = '';
        if (strlen($content) <= 0){
            return;
        }
        $encrypt_data = other_enc($content);

        if($fp = @fopen($file,'w')) {
            fwrite($fp,$encrypt_data);
            fclose($fp);
        }
    }
    
    // 當前學校常數設定
    $config_file = sysDocumentRoot . '/base/' . intval($_POST['sid']) . '/config.txt';
    configWrite($config_file, $file_content);
    
    // 所有學校設定
    $master_config_file = sysDocumentRoot . '/base/config.txt';
    configWrite($master_config_file, $master_file_content);

	echo <<< BOF
	<script language="javascript">
	window.onload = function () {
		alert("{$MSG['save_sucess'][$sysSession->lang]}");
		window.location.replace('/academic/sch/sch_list.php');
	};
	</script>
BOF;
?>
