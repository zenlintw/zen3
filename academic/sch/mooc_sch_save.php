<?php
	/**
	 * 儲存學校設定
	 *
	 * 建立日期：2003
	 * @author  ShenTing Lin
	 * @version $Id: sch_save.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/sch_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
    require_once(sysDocumentRoot . '/lang/mooc.php');
	/**
	 * 1. 檢查車票是否正確
  	 * 2. 檢查車票的種類，是新增還是修改
	 **/
	$actType     = '';
	$title       = '';
	$isSingle    = '';
	$message     = '';
	$isError     = false;
	$mustRestart = false;
    /*
     * savePortal()
     * portal 參數儲存
     * @param string  $sch_dbpre  學校DB前綴
     * @param array   $dataArray  (portal_id, key, value)
     * @return X
     */
    function savePortal($sch_dbpre, $dataArray) {
        global $sysConn;
        $affectR = 0;
        foreach($dataArray as $k => $v) {
            foreach($v as $key => $val) {
                dbSet("`{$sch_dbpre}`.`WM_portal`", sprintf("`value`='%s'", $val), sprintf("`portal_id`='%s' and `key`='%s'", $k, $key));
                if (!$sysConn->Affected_Rows()) {
                    dbNew("`{$sch_dbpre}`.`WM_portal`", "`portal_id`, `key`, `value`", sprintf("'%s', '%s', '%s'", $k, $key, $val));
                }
                $affectR++;
            }
        }
        return $affectR;
    }
    /*
     * switchUrl()
     * 課程連結轉換
     * @param1 連結 url
     * @param2 連結方式 url type
     * @return string 轉換後的連結 url
     */
    function switchUrl($linkUrl, $urlType) {
        switch($urlType) {
            case '1':
                return '/info/'.intval($linkUrl[0]);
                break;
            /*
            case '2':
                return '/forum/570,1000000003,'.intval($linkUrl[1]).'.php';
                break;
             * 
             */
            default:
                return trim($linkUrl[intval($urlType)-1]);
        }
    }
    /*
    echo "<pre>";
    var_dump($_POST);
    echo "</pre>";
     * 
     */
    
	$_POST['ticket'] = trim($_POST['ticket']);
    $reset = md5('reset' . $sysSession->ticket . $sysSession->username . $_POST['sid'] . $_POST['shost']);
    /*
	$ticket = md5('Create' . $sysSession->ticket .  $sysSession->username);
	if ($_POST['ticket'] == $ticket) {
		$actType = 'Create';
		$title   = $MSG['btn_create_school'][$sysSession->lang];
	}
        */
	$ticket = md5('moocEdit' . $sysSession->ticket . $sysSession->username . $_POST['sid'] . $_POST['shost']);
	if ($_POST['ticket'] == $ticket) {
		$actType = 'Edit';
		$title   = $MSG['tabs_modify_school'][$sysSession->lang];
	}

	$ticket = md5('Single' . 'moocEdit' . $sysSession->ticket . $sysSession->username . $_POST['sid'] . $_POST['shost']);
	if ($_POST['ticket'] == $ticket) {
		$actType  = 'Edit';
		$isSingle = 'Single';
		$title    = $MSG['tabs_modify_school'][$sysSession->lang];
	}

	if ($actType == '') {
	    die($MSG['access_deny'][$sysSession->lang]);
	}
        
	/**
	 * 參數檢查
	 **/
        // 參數: *schname, *serhost, school_mail, *theme, *lang, *allow_guest, *guestLimit, *canReg, *courseQuota,
        //       *doorQuota, *icon, *logo, *share[], *canReg1[], *multi_login, *main_title, *sub_title, *bg_img, *feature_img,
        //       bottom_title, about_us, contact_us, faq, other_info
        //       *sid, shost, ticket, 以後再新增一個還原的參數
        //一般標題:share[], canReg1[], main_title, sub_title, bottom_title, about_us, contact_us, faq, other_info
        //圖片:icon, logo, bg_img, feature_img
	foreach ($_POST as $key => $val)
	{
        if ($key == 'icon' || $key == 'logo' || $key == 'bg_img' || $key == 'feature_img') continue;
		switch ($key)
		{
			case 'sid':
			case 'guestLimit':
			case 'courseQuota':
			case 'doorQuota':
            case 'all_brand_priority':
            case 'course_list_priority':
            case 'pubilc_forum_priority':
            case 'custom_1_priority':
            case 'custom_2_priority':
            case 'search_x':
            case 'search_y':
				$_POST[$key] = intval($val);
				break;
			case 'serhost':
				$res = preg_match('/^[\w-]+(\.[\w-]+)+$/', $val, $match);
				if (count($match) == 0)
				{
					$isError = true;
					$message = $MSG['access_deny'][$sysSession->lang];
				}
				break;
			case 'schname':
				$_POST[$key] = Filter_Spec_char($val, 'title');
				break;
			case 'lang':
				if (!in_array($val, $sysAvailableChars)) $_POST[$key] = $sysAvailableChars[0];
				break;
			case 'theme':
				$_POST[$key] = 'default';
				break;
			case 'allow_guest':
			case 'multi_login':
				$_POST[$key] = ($val == 'N') ? 'N' : 'Y';
				break;
			case 'canReg':
				$_POST[$key] = in_array($val, array('Y', 'N', 'C')) ? $val : 'Y';
				break;
            case 'style':
            case 'sub_style':
				$_POST[$key] = in_array($val, array('black', 'orange', 'blue')) ? $val : 'black';
				break;
			case 'instructRequire':
				$_POST[$key] = 'admonly';
				break;
            case 'share':
            case 'canReg1':
                if (is_array($_POST[$key])){
					$_POST[$key] = implode(',', $_POST[$key]);
				}else{
					$_POST[$key] = '';
				}
				break;
            case 'poster_1_link':
            case 'poster_2_link':
            case 'poster_3_link':
            case 'custom_1_link':
            case 'custom_2_link':
                $_POST[$key] = (is_array($_POST[$key])) ? $_POST[$key] : '';
                break;
			default:
				$_POST[$key] = trim($val);
		}
	}
        
	$sysSession->cur_func = ($actType == 'Edit') ? '100300200' : '100300100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 設定車票
	setTicket();
	if (!isset($_POST['instructRequire'])) $_POST['instructRequire'] = 'admonly';

        // 是否要還原預設值
	if (trim($_POST['reset']) == $reset) {
            $actType = 'Reset';
	}
	if (!$isError) {
		switch ($actType) {
                        /*
			case 'Create':
				// 檢查網域名稱是否已經有人使用了
				list($host_cnt) = dbGetStSr('WM_school', 'count(*)', "school_host='{$_POST[serhost]}'", ADODB_FETCH_NUM);
				if ($host_cnt > 0) {
					$message = $MSG['msg_domain_used'][$sysSession->lang];
					$isError = true;
				}

				// 取出 school_id
				list($sid) = dbGetStSr('WM_school', 'MAX(school_id) + 1 AS sid', '1', ADODB_FETCH_NUM);
				if ($sid <= 10001) $sid = 10002;
				// 開始新增一所學校

				// 複製相關的檔案

				// 將學校的設定寫到資料庫中
				$_POST['allow_guest'] = 'N';
				if ($isSingle == 'Single') {
					$fields = 'school_id, school_host, school_name, language, ' .
							  'theme, guest, multi_login, canReg, instructRequire, guestLimit, courseQuota,school_mail';
					$values = "'{$sid}', '{$_POST['serhost']}', '{$_POST['schname']}', " .
							  "'{$_POST['lang']}', '{$_POST['theme']}', '{$_POST['allow_guest']}', '{$_POST['multi_login']}', " .
							  "'{$_POST['canReg']}', '{$_POST['instructRequire']}', '{$_POST['guestLimit']}', '{$_POST['courseQuota']}','{$_POST['school_mail']}'";
				} else {
					$fields = 'school_id, school_host, school_name, language, ' .
							  'theme, guest, multi_login, canReg, instructRequire, guestLimit, courseQuota, quota_limit, school_mail';
					$values = "'{$sid}', '{$_POST['serhost']}', '{$_POST['schname']}', " .
							  "'{$_POST['lang']}', '{$_POST['theme']}', '{$_POST['allow_guest']}', '{$_POST['multi_login']}', " .
							  "'{$_POST['canReg']}', '{$_POST['instructRequire']}', '{$_POST['guestLimit']}', '{$_POST['courseQuota']}', '{$_POST['doorQuota']}', '{$_POST['school_mail']}'";
				}

				dbNew('WM_school', $fields, $values);
				if (!$sysConn->Affected_Rows()) {
					$message = $MSG['msg_create_sch_fail'][$sysSession->lang];
					$isError = true;
				} else {
					$message = $MSG['msg_create_sch_success'][$sysSession->lang];
				}

				// 取得 mysql 外部執行檔 begin
				list($foo, $mysql_basedir) = $sysConn->GetRow('show variables like "basedir"');

				$mysql = $mysql_basedir . 'bin/mysql';
				if (!file_exists($mysql) || !is_executable($mysql))
				{
					$mysql = exec("sh -c 'PATH=/usr/local/mysql/bin:/usr/bin:/usr/local/bin:/home/apps/mysql/bin which mysql'");
					if (!preg_match('!^(/\w+)+$!', $mysql)) die('"mysql" not found.');
				}
				if (!file_exists($mysql) || !is_executable($mysql)) die('"mysql" not found or not executable.');
				$mysql .= (sysDBhost == 'localhost' ? ' -S /tmp/mysql.sock' : (' -h ' . sysDBhost)) .' -u ' . sysDBaccoount . ' -p' . sysDBpassword . ' -B -r --set-variable=max_allowed_packet=64M -f';
				// 取得 mysql 外部執行檔 end

				// 將目前學校的 table schema 複製給新學校 begin
				if ($fp = popen($mysql, 'w'))
				{
					$keep = $ADODB_FETCH_MODE;
					$ADODB_FETCH_MODE = ADODB_FETCH_NUM;

					$cur_school = sysDBprefix . $sysSession->school_id;
					$new_school = sysDBprefix . $sid;
					$sysConn->Execute('use ' . $cur_school);
					list(,$DB) = $sysConn->GetRow("SHOW CREATE DATABASE {$cur_school};");
					fwrite($fp, str_replace($cur_school, $new_school, $DB . "; use {$new_school};"));
					echo '<!--create school DB : ', $new_school, "\n";

					if ($tables = $sysConn->GetCol("SHOW TABLES FROM {$cur_school};"))
					{
						foreach ($tables as $table)
						{
						    list($t,$TL) = $sysConn->GetRow("SHOW CREATE TABLE {$table};");
						    fwrite($fp, preg_replace('/ AUTO_INCREMENT=\d+/i', '', $TL) . ';');
						    echo 'create table : ', $t, "\n";
						}
					}
					else
						echo $sysConn->ErrorNo(), ': ', $sysConn->ErrorMsg();

                    $ADODB_FETCH_MODE = $keep;
					fwrite($fp, "INSERT INTO `{$new_school}`.`WM_user_account` select * from `{$cur_school}`.`WM_user_account` where username in ('root','{$sysSession->username}');");
					fwrite($fp, "INSERT INTO `{$new_school}`.`WM_acl_bindfile` select * from `{$cur_school}`.`WM_acl_bindfile`;");
					fwrite($fp, "INSERT INTO `{$new_school}`.`WM_acl_function` select * from `{$cur_school}`.`WM_acl_function`;");
					fwrite($fp, "INSERT INTO `{$new_school}`.`WM_review_syscont` select * from `{$cur_school}`.`WM_review_syscont`;");
					fwrite($fp, "INSERT INTO `{$new_school}`.`WM_bbs_boards` select * from `{$cur_school}`.`WM_bbs_boards` where board_id=1000000001;");
					fwrite($fp, "INSERT INTO `{$new_school}`.`WM_news_subject` (news_id, board_id, type, visibility) VALUES (1, 1000000001, 'suggest', 'visible');");
					fwrite($fp, "INSERT INTO `{$new_school}`.`WM_term_subject` (course_id,board_id) VALUES ({$sid}, 1000000001);");
					fwrite($fp, "ALTER TABLE `{$new_school}`.`WM_class_main`             AUTO_INCREMENT = 1000001;");
					fwrite($fp, "ALTER TABLE `{$new_school}`.`WM_content`                AUTO_INCREMENT = 100001;");
					fwrite($fp, "ALTER TABLE `{$new_school}`.`WM_qti_exam_test`          AUTO_INCREMENT = 100000001;");
					fwrite($fp, "ALTER TABLE `{$new_school}`.`WM_qti_homework_test`      AUTO_INCREMENT = 100000001;");
					fwrite($fp, "ALTER TABLE `{$new_school}`.`WM_qti_questionnaire_test` AUTO_INCREMENT = 100000001;");
					fwrite($fp, "ALTER TABLE `{$new_school}`.`WM_term_course`            AUTO_INCREMENT = 10000001;");
					fclose($fp);
					echo 'db creation is complete. -->';
				}
				else
					echo 'mysql pipe open failure.';
				// 將目前學校的 table schema 複製給新學校 end

				include_once sysDocumentRoot . '/lib/wm3_config_class.php';
				$wm3_config = new WM3config;
				$wm3_config->reGenerateVirtualHostConfig();

				break;
                        */
			case 'Remove':
			    // 刪除目錄			 exec('rm -rf ' . sysDocumentRoot . '/base/' . $sid);
			    // 刪除資料庫   		$sysConn->Execute('drop database ' . sysDBprefix . $sid);
			    // 刪除 MASTER 相關  dbDel('WM_school', 'school_id=' . $sid);
			    // 刪除 MASTER 相關  dbDel('WM_master', 'school_id=' . $sid);
			    // 刪除 MASTER 相關  dbDel('WM_sch4user', 'school_id=' . $sid);
				break;

			case 'Edit':
                    $saveblock = $_POST['saveblock'];
                    $sid = intval($_POST['sid']);
                    $cur_school = sysDBprefix . $sid;
                    $pAffectR = 0;
                    switch ($saveblock) {
                        case 'block-hd':
                            // 樣式
                            $pArray = array(
                                'theme' => array('style'=>$_POST['style'])
                            );
                            if (null != $_POST['sub_style']) {
                                $pArray['theme']['sub_style'] = $_POST['sub_style'];
                            }
                            $pAffectR = savePortal($cur_school, $pArray);
                            // 判斷是否開放註冊
                            $_POST['canReg1'] = explode(',', $_POST['canReg1']);
                            $canReg = (in_array('GENERAL', $_POST['canReg1']))? 'Y': 'N';
                            $canFbReg = (in_array('FB', $_POST['canReg1']))? 'FB': '';
                            // FB有開啟才修改id, secret
                            $FBAPI = ($canFbReg == 'FB')?sprintf("c.canReg_fb_id='%s', c.canReg_fb_secret='%s', ",$_POST['FB_id'] ,$_POST['FB_secret']) :"";
                            
                            $sqls = "w.school_host='{$_POST['serhost']}', w.school_name='{$_POST['schname']}', w.language='{$_POST['lang']}', " .
                                    // "w.theme='{$_POST['theme']}', guestLimit='{$_POST['guestLimit']}', " .
                                    // "w.guest='{$_POST['allow_guest']}', instructRequire='{$_POST['instructRequire']}', " .
                                    "w.canReg='{$canReg}', w.multi_login='{$_POST['multi_login']}', " .
                                    "w.courseQuota='{$_POST['courseQuota']}', w.quota_limit='{$_POST['doorQuota']}' , w.school_mail='{$_POST['school_mail']}', " .
                                    $FBAPI .
                                    "c.social_share='{$_POST['share']}', c.canReg_ext='{$canFbReg}' ";

                            dbSet('WM_school w, CO_school c', $sqls, "w.school_id = c.school_id and w.school_id='{$sid}' and w.school_host='{$_POST['shost']}'");
                            
                            break;
                        case 'block-br':
                            if ($_POST['search_bar'] == null) {
                                $_POST['search_bar'] = 'false';
                            }
                            
                            // 海報、搜尋介紹開關
                            $pArray = array(
                                    'ads001'        => array(
                                            'pic_path'      =>  "/base/{$sid}/door/tpl/ad001.png",
                                            'url_type'      =>  $_POST['poster_1_linktype'],
                                            'url_default'   =>  $_POST['poster_1_link'][intval($_POST['poster_1_linktype']-1)],
                                            'url'           =>  switchUrl($_POST['poster_1_link'], $_POST['poster_1_linktype'])
                                    ),
                                    'ads002'        => array(
                                            'pic_path'      =>  "/base/{$sid}/door/tpl/ad002.png",
                                            'url_type'      =>  $_POST['poster_2_linktype'],
                                            'url_default'   =>  $_POST['poster_2_link'][intval($_POST['poster_2_linktype'])-1],
                                            'url'           =>  switchUrl($_POST['poster_2_link'], $_POST['poster_2_linktype'])
                                    ),
                                    'ads003'        => array(
                                            'pic_path'      =>  "/base/{$sid}/door/tpl/ad003.png",
                                            'url_type'      =>  $_POST['poster_3_linktype'],
                                            'url_default'   =>  $_POST['poster_3_link'][intval($_POST['poster_3_linktype'])-1],
                                            'url'           =>  switchUrl($_POST['poster_3_link'], $_POST['poster_3_linktype'])
                                    ),
                                    'content_sw'    => array(
                                            'searchbar'     =>  $_POST['search_bar']
                                    ),
                                    'searchbar'    => array(
                                            'x'     =>  $_POST['search_x'],
                                            'y'     =>  $_POST['search_y']
                                    ),      
                                    'represent'    => array(
                                            'pic_path'     =>  "/base/{$sid}/door/tpl/rep_img.png"
                                    ),            
                                    'brand'    => array(
                                            'pic_path'     =>  "/base/{$sid}/door/tpl/brand_logo.png"
                                    )
                            );
                            $pAffectR = savePortal($cur_school, $pArray);
                            $sqls = sprintf("c.banner_title1='%s', c.banner_title2='%s', c.banner_title3='%s' ", $_POST['main_title'], $_POST['sub_title'], $_POST['bottom_title']);
                            dbSet('WM_school w, CO_school c', $sqls, "w.school_id = c.school_id and w.school_id='{$sid}' and w.school_host='{$_POST['shost']}'");
                            break;
                        case 'block-ct':
                            // 未勾選預設值
                            $bEnable = array('all_brand_enable', 'course_list_enable', 'pubilc_forum_enable', 'news_enable', 'calendar_enable', 'custom_1_enable', 'custom_2_enable');
                            foreach($bEnable as $v) {
                                if ($_POST[$v] == null) {
                                    $_POST[$v] = 'false';
                                }
                            }
                            
                            // 內容設定
                            $pArray = array(
                                    'content_sw'        => array(
                                            'franchisee'        =>  $_POST['all_brand_enable'],
                                            'courselist'        =>  $_POST['course_list_enable'],
                                            'forum'             =>  $_POST['pubilc_forum_enable'],
                                            'news'              =>  $_POST['news_enable'],
                                            'calendar'          =>  $_POST['calendar_enable'],
                                            'custom1'           =>  $_POST['custom_1_enable'],
                                            'custom2'           =>  $_POST['custom_2_enable']
                                    ),
                                    'content_pri'        => array(
                                            'franchisee'        =>  $_POST['all_brand_priority'],
                                            'courselist'        =>  $_POST['course_list_priority'],
                                            'forum'             =>  $_POST['pubilc_forum_priority'],
                                            'custom1'           =>  $_POST['custom_1_priority'],
                                            'custom2'           =>  $_POST['custom_2_priority']
                                    ),
                                    'franchisee'        => array('title'    =>  $_POST['all_brand_name']),
                                    'courselist'        => array('title'    =>  $_POST['course_list_name']),
                                    'forum'             => array('title'    =>  $_POST['pubilc_forum_name']),
                                    'news'              => array('title'    =>  $_POST['news_name']),
                                    'calendar'          => array('title'    =>  $_POST['calendar_name']),
                                    'custom1'           => array(
                                            'title'             =>  $_POST['custom_1_name'],
                                            'pic_path'          =>  "/base/{$sid}/door/tpl/cus001.png",
                                            'pic_style'         =>  $_POST['custom_1_pictype'],
                                            'url_type'          =>  $_POST['custom_1_linktype'],
                                            'url_default'       =>  $_POST['custom_1_link'][intval($_POST['custom_1_linktype'])-1],
                                            'url'               =>  switchUrl($_POST['custom_1_link'], $_POST['custom_1_linktype'])
                                    ),
                                    'custom2'           => array(
                                            'title'             =>  $_POST['custom_2_name'],
                                            'pic_path'          =>  "/base/{$sid}/door/tpl/cus002.png",
                                            'pic_style'         =>  $_POST['custom_2_pictype'],
                                            'url_type'          =>  $_POST['custom_2_linktype'],
                                            'url_default'       =>  $_POST['custom_2_link'][intval($_POST['custom_2_linktype'])-1],
                                            'url'               =>  switchUrl($_POST['custom_2_link'], $_POST['custom_2_linktype'])
                                    )
                            );
                            $pAffectR = savePortal($cur_school, $pArray);
                            break;
                        case 'block-qk':
                            // 未勾選預設值
                            $bEnable = array('online_chat_enable');
                            foreach($bEnable as $v) {
                                if ($_POST[$v] == null) {
                                    $_POST[$v] = 'false';
                                }
                            }
                            
                            // 快捷鍵設定
                            $pArray = array(
                                    'quick_sw'        => array(
                                            'onlinehelp'        =>  $_POST['online_chat_enable']
                                    ),
                                    'quick_pri'        => array(
                                            'onlinehelp'        =>  $_POST['online_chat_priority']
                                    ),
                                    'onlinehelp'        => array('title'    =>  $_POST['online_chat_name'])
                            );
                            $pAffectR = savePortal($cur_school, $pArray);
                            break;
                        case 'block-ft':
                            $sqls = "c.footer_about='{$_POST['about_us']}', c.footer_contact='{$_POST['contact_us']}', c.footer_faq='{$_POST['faq']}', c.footer_info='{$_POST['other_info']}' ";
                            dbSet('WM_school w, CO_school c', $sqls, "w.school_id = c.school_id and w.school_id='{$sid}' and w.school_host='{$_POST['shost']}'");
                            break;
                        case 'picdrop':
                            switch(trim($_POST['selectedpic'])) {
                                case 'poster_1':
                                    $delPic = sysDocumentRoot . "/base/" . $sid . '/door/tpl/ad001.png';
                                    $result['delpicid'] = 'poster_1';
                                    break;
                                case 'poster_2':
                                    $delPic = sysDocumentRoot . "/base/" . $sid . '/door/tpl/ad002.png';
                                    $result['delpicid'] = 'poster_2';
                                    break;
                                case 'poster_3':
                                    $delPic = sysDocumentRoot . "/base/" . $sid . '/door/tpl/ad003.png';
                                    $result['delpicid'] = 'poster_3';
                                    break;
                            }
                            if (isset($delPic)) {
                                if (is_file($delPic)) {
                                    unlink($delPic);
                                    $delImgMsg = $MSG['msg_del_pic_success'][$sysSession->lang];
                                }
                                wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], "刪除海報" . ' school_id=' . $sid);
                            } else {
                                $delImgMsg = $MSG['msg_add_pic_fail'][$sysSession->lang];
                            }
                            
                            // 回傳新的 ticket
                            if ($isSingle == 'Single') {
                                $reTicket = md5($isSingle . 'mooc' . $actType . $sysSession->ticket .  $sysSession->username . $_POST['sid'] . $_POST['shost']);
                            } else {
                                $reTicket = md5('mooc' . $actType . $sysSession->ticket .  $sysSession->username . $_POST['sid'] . $_POST['shost']);
                            }
                            $result['msg'] = $delImgMsg;
                            $result['ticket'] = $reTicket;
                            echo json_encode($result);
                            die();
                            break;
                        default:
                            die($MSG['access_deny'][$sysSession->lang]);
                    }


                        if (!$sysConn->Affected_Rows() && $pAffectR == 0) {
                            $message = $MSG['msg_update_sch_fail'][$sysSession->lang];
                            $isError = true;
                        } else {
                            $message = $MSG['msg_update_sch_success'][$sysSession->lang];
                            if ($_POST['serhost'] != null && $_POST['serhost'] != $_POST['shost'])
                            {
                                include_once sysDocumentRoot . '/lib/wm3_config_class.php';
                                $wm3_config = new WM3config;
                                $wm3_config->reGenerateVirtualHostConfig();
                                $mustRestart = true;
                            }
                        }

                        // 上傳圖片至 base/{sid}/door/tpl
                        $picUpdate = 0;
                        $fileArray = array(
                                'logo'          => 'logo.png',
                                'icon'          => 'icon.ico',
                                'bg_img_1'      => 'banner_bg.png',
                                'rep_img'       => 'rep_img.png',
                                'brand_img'     => 'brand_logo.png',
                                'poster_1_pic'  => 'ad001.png',
                                'poster_2_pic'  => 'ad002.png',
                                'poster_3_pic'  => 'ad003.png',
                                'custom_1_pic'  => 'cus001.png',
                                'custom_2_pic'  => 'cus002.png'
                                
                        );
                        
                        foreach ($fileArray as $k => $v) {
                                if (is_uploaded_file($_FILES[$k]["tmp_name"])) {
                                        $fileType=$_FILES[$k]["type"];
                                        if ($_FILES[$k]["error"] > 0 ) {
                                            $imgMsg = $MSG['msg_image_upload_fail'][$sysSession->lang];
                                            $isError = true;
                                        }
                                        if ($_FILES[$k]["type"] == 'image/png' || $_FILES[$k]["type"] == 'image/x-png' || $_FILES[$k]["type"] == 'image/pjpeg' || $_FILES[$k]["type"] == 'image/x-icon') {
                                               move_uploaded_file($_FILES[$k]["tmp_name"], sysDocumentRoot . "/base/" . $sid . '/door/tpl/' . $v);
                                               $result['addpicid'][] = $k;
                                               $picUpdate++;
                                        } else {
                                            $imgMsg = $MSG['msg_image_format_fail'][$sysSession->lang];
                                            $isError = true;
                                        }
                                }
                        }
                        if ($picUpdate > 0) {
                            $message = $MSG['msg_update_sch_success'][$sysSession->lang];
                        }
				break;
                case 'Reset':
                        // 還原預設值
                        $RS = dbGetStSr('`WM_school`', '*', "school_id='{$_POST['sid']}' and school_host='{$_POST['shost']}'", ADODB_FETCH_ASSOC);
                        $_POST['schname'] = $RS['school_name'];
                        $_POST['serhost'] = $RS['school_host'];
                        $_POST['school_mail'] = $RS['school_mail'];
                        $_POST['lang'] = $RS['language'];

                        $_POST['canReg1'] = explode(',', 'GENERAL');
                        $_POST['courseQuota'] = 204800;
                        $_POST['doorQuota'] = 204800;
                        $canReg = 'Y';
                        $canFbReg = '';
                        $_POST['multi_login'] = 'Y';
                        $_POST['share'] = 'FB,PLURK,TWITTER,LINE,WECHAT';
                        $_POST['main_title'] = $MSG['adv_title'][$sysSession->lang];
                        $_POST['sub_title'] = $MSG['adv_subtitle'][$sysSession->lang];
                        $_POST['bottom_title'] =$MSG['adv_description_1'][$sysSession->lang];
                        $_POST['about_us'] = $MSG['about_default_url'][$sysSession->lang];
                        $_POST['contact_us'] = $MSG['contact_default_url'][$sysSession->lang];
                        $_POST['faq'] = $MSG['faq_default_url'][$sysSession->lang];
                        $_POST['other_info'] = $MSG['footer_info'][$sysSession->lang];

                        $sqls = "w.canReg='{$canReg}', w.multi_login='{$_POST['multi_login']}', " .
                                "w.courseQuota='{$_POST['courseQuota']}', w.quota_limit='{$_POST['doorQuota']}', " .
                                "c.social_share='{$_POST['share']}', c.canReg_ext='{$canFbReg}', " .
                                "c.banner_title1='{$_POST['main_title']}', c.banner_title2='{$_POST['sub_title']}', c.banner_title3='{$_POST['bottom_title']}', " .
                                "c.footer_about='{$_POST['about_us']}', c.footer_contact='{$_POST['contact_us']}', c.footer_faq='{$_POST['faq']}', c.footer_info='{$_POST['other_info']}' ";

                        dbSet('WM_school w, CO_school c', $sqls, "w.school_id = c.school_id and w.school_id='{$_POST['sid']}' and w.school_host='{$_POST['shost']}'");

                        if (!$sysConn->Affected_Rows()) {
                            $message = $MSG['msg_reset_sch_fail'][$sysSession->lang];
                            $isError = true;
                        } else {
                            $message = $MSG['msg_reset_sch_success'][$sysSession->lang];
                            if ($_POST['serhost'] != $_POST['shost'])
                            {
                                include_once sysDocumentRoot . '/lib/wm3_config_class.php';
                                $wm3_config = new WM3config;
                                $wm3_config->reGenerateVirtualHostConfig();
                                $mustRestart = true;
                            }
                        }
                        // 將預設圖複製過去  : icon, logo, banner_bg, banner_logo
                        $source = sysDocumentRoot . "/theme/default/learn_mooc/";
                        $target = sysDocumentRoot . "/base/{$_POST['sid']}/door/tpl/";

                        // 要複製的檔案列表
                        $files = array(
                                'logo.png',
                                'banner_bg.png',
                                'banner_logo.png'
                        );
                        foreach ($files as $v) {
                            if(is_dir($target)) {
                                @copy($source . $v, $target . $v);
                             }
                        }
                        // favicon 另外在處理

                        $actType = 'Edit';
                break;

			default:
				die($MSG['access_deny'][$sysSession->lang]);
		} // End switch ($actType)

		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], $message . ' school_id=' . $sid);
	} // End if (!$isError)
	// 檢查學校目錄
	if (@mkdir(sysDocumentRoot . "/base/{$sid}"        , 0755)) {	// 建立學校的主目錄
		@mkdir(sysDocumentRoot . "/base/{$sid}/door"   , 0755);		// 建立學校的 door
		@mkdir(sysDocumentRoot . "/base/{$sid}/board"  , 0755);		// 建立學校討論版夾檔存放的目錄
		@mkdir(sysDocumentRoot . "/base/{$sid}/quint"  , 0755);		// 建立學校經華區夾檔存放的目錄
		@mkdir(sysDocumentRoot . "/base/{$sid}/system" , 0755);		// 建立選單存放資料的地方
		@mkdir(sysDocumentRoot . "/base/{$sid}/door"   , 0755);		// 建立學校的 door
		@mkdir(sysDocumentRoot . "/base/{$sid}/course" , 0755);		// 建立課程存放的目錄 (期別)
		@mkdir(sysDocumentRoot . "/base/{$sid}/content", 0755);		// 建立教材存放的目錄
		@mkdir(sysDocumentRoot . "/base/{$sid}/class"  , 0755);		// 建立班級(部門)存放的目錄
		if ($fp = fopen(sysDocumentRoot . "/base/{$sid}/system/faq.xml", 'w'))
		{
		    fwrite($fp, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<allfaq date=\"2004-10-10 10:10\" />\n");
		    fclose($fp);
		}
		if ($fp = fopen(sysDocumentRoot . "/base/{$sid}/system/news.xml", 'w'))
		{
		    fwrite($fp, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<allnews date=\"2004-10-10 10:10\" />\n");
		    fclose($fp);
		}
	}

	$allow = array(
		'Y' => $MSG['status_allow'][$sysSession->lang],
		'N' => $MSG['status_deny'][$sysSession->lang]
	);

	$reg_allow = array(
		'Y' => $MSG['status_allow'][$sysSession->lang],
		'N' => $MSG['status_deny'][$sysSession->lang],
		'C' => $MSG['reg_check'][$sysSession->lang]
	);

	$require = array(
		'noncheck' => $MSG['cs_allow'][$sysSession->lang],
		'check'    => $MSG['cs_check'][$sysSession->lang],
		'admonly'  => $MSG['cs_deny'][$sysSession->lang]
	);
        
        $register = array(
		'GENERAL'   => $MSG['mooc_reg_general'][$sysSession->lang],
		'FB'        => $MSG['mooc_reg_fb'][$sysSession->lang]
	);

        $indexImg = array(
		'icon'          => '/favicon.ico?'.time(),
		'logo'          => '/base/'.$_POST['sid'].'/door/tpl/logo.png?'.time().'" height="50px',
		'bg_img'        => '/base/'.$_POST['sid'].'/door/tpl/banner_bg.png?'.time().'" height="50px',
		'feature_img'   => '/base/'.$_POST['sid'].'/door/tpl/banner_logo.png?'.time().'" height="50px'
        );

	// array(型態, 長度, 名稱, id, value, default value, extra, 說明);
	$school = array(
                // 29 => array('首頁示意圖'                                  , '' ),
                // 30 => array('示意圖'                                      , 'schematic'),
                // 0 => array('基本資料'                                     , ''),
                 1 => array($MSG['mooc_school_name'][$sysSession->lang]     , 'schname'),
                 2 => array($MSG['mooc_school_website'][$sysSession->lang]  , 'serhost'),
                 3 => array($MSG['school_mail'][$sysSession->lang]          , 'school_mail'),
                // 4 => array($MSG['school_academic'][$sysSession->lang]    , 'manager'),
                // 5 => array($MSG['item_theme'][$sysSession->lang]         , 'theme'),
                 6 => array($MSG['item_language'][$sysSession->lang]        , 'lang'),
                // 7 => array($MSG['item_guest'][$sysSession->lang]         , 'allow_guest'),
                // 8 => array($MSG['item_guest_limit'][$sysSession->lang]   , 'guestLimit'),
                // 9 => array($MSG['item_register'][$sysSession->lang]      , 'canReg'),
                // 10 => array($MSG['item_require'][$sysSession->lang]      , 'instructRequire'),
                11 => array($MSG['item_quota'][$sysSession->lang]           , 'courseQuota'),
                12 => array($MSG['item_door_quota'][$sysSession->lang]      , 'doorQuota'),
                // 13 => array('ICON'                                          , 'icon'),
                14 => array('LOGO'                                          , 'logo'),
                15 => array($MSG['mooc_share'][$sysSession->lang]           , 'share'),
                16 => array($MSG['mooc_register'][$sysSession->lang]        , 'canReg1'),
                17 => array($MSG['item_multi_login'][$sysSession->lang]     , 'multi_login'),
                // 18 => array('Banner 區域'                                , ''),
                19 => array($MSG['mooc_main_title'][$sysSession->lang]      , 'main_title'),
                20 => array($MSG['mooc_sub_title'][$sysSession->lang]       , 'sub_title'),
                21 => array($MSG['mooc_bg_img'][$sysSession->lang]          , 'bg_img'),
                22 => array($MSG['mooc_features_figure'][$sysSession->lang] , 'feature_img'),
                23 => array($MSG['mooc_bottom_title'][$sysSession->lang]    , 'bottom_title'),
                //24 => array('Footer 區域'                                 , ''),
                25 => array($MSG['mooc_about_us'][$sysSession->lang]        , 'about_us'),
                26 => array($MSG['mooc_contact_us'][$sysSession->lang]      , 'contact_us'),
                27 => array($MSG['mooc_faq'][$sysSession->lang]             , 'faq'),
                28 => array($MSG['mooc_other_info'][$sysSession->lang]      , 'other_info'),
	);
    // 回傳新的 ticket
    if ($isSingle == 'Single') {
        $reTicket = md5($isSingle . 'mooc' . $actType . $sysSession->ticket .  $sysSession->username . $_POST['sid'] . $_POST['shost']);
    } else {
        $reTicket = md5('mooc' . $actType . $sysSession->ticket .  $sysSession->username . $_POST['sid'] . $_POST['shost']);
    }
    
    // echo "{\"msg\": \"{$message}\", \"ticket\": \"{$reTicket}\", \"restart\": \"{$mustRestart}\"}";
    if (false == $isError) {
        $result['msg'] = $MSG['msg_update_sch_success'][$sysSession->lang];
    } else {
        $result['msg'] = $MSG['msg_update_sch_fail'][$sysSession->lang];
        if (null != $imgMsg) {
            $result['msg'] .= $imgMsg;
        }
    }
    $result['ticket'] = $reTicket;
    $result['restart'] = $mustRestart;
    echo json_encode($result);
    die();
    /*
	showXHTML_head_B($MSG['html_title_save'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_head_E();
	showXHTML_body_B('');

		$ary = array();
		$ary[] = array($title, 'tabsTag');
		// $colspan = 'colspan="2"';
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'actForm', '', 'method="post" action="mooc_sch_priority.php" style="display: inline;"');
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHelp"');
					showXHTML_td('colspan="2"', $message);
				showXHTML_tr_E();

				//reset ($_POST);
				foreach ($school as $key => $val) {
					if (empty($val)) continue;
					//if ($key == 'ticket') $val = md5($actType . $sysSession->ticket .  $sysSession->username . $sid . $shost);
					$value = $_POST[$val[1]] . '&nbsp;';
					if ( ($val[1] == 'allow_guest')
						|| ($val[1] == 'multi_login')
						|| ($val[1] == 'canReg') ) {
						$value = $allow[$_POST[$val[1]]] . '&nbsp;';
					}
					if ($val[1] == 'canReg') {
						$value = $reg_allow[$_POST[$val[1]]] . '&nbsp;';
					}
					if ($val[1] == 'instructRequire') $value = $require[$_POST[$val[1]]] . '&nbsp;';
					if ($val[1] == 'courseQuota') $value .= 'KB';
					if ($val[1] == 'doorQuota')   $value .= 'KB';
                                        if ($val[1] == 'canReg1') {
                                            foreach ($register as $k => $v) {
                                                if (in_array($k, $_POST['canReg1'])) {
                                                    if ($k == 'FB') {
                                                        $regmethod .= $v .'(ID:'.$_POST['FB_id'].', Secret:'.$_POST['FB_secret'].'),';
                                                    } else {
                                                        $regmethod .= $v .',';
                                                    }
                                                }
                                            }
                                            $value =  substr($regmethod, 0, -1);;
                                        }
                                        if (($val[1] == 'icon') || ($val[1] == 'logo') || ($val[1] == 'bg_img') || ($val[1] == 'feature_img')) {
                                                foreach ($indexImg as $k => $v) { 
                                                    if ($val[1] == $k) {
                                                        $value ='<img src="'.$v.'" alt="'.$val[1].'" >';
                                                    }
                                                }
                                        }

					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $val[0]);
						showXHTML_td('width="70%"', $value);
					showXHTML_tr_E();
				}

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" align="center"');
						if ($isError) {
							reset ($_POST);
							while ( list($key, $val) = each($_POST) ) {
								if ($key == 'ticket') {
									if ($isSingle == 'Single') {
										$val = md5($isSingle . 'reMooc' . $actType . $sysSession->ticket .  $sysSession->username . $_POST[sid] . $_POST[shost]);
                                    } else {
										$val = md5('reMooc' . $actType . $sysSession->ticket .  $sysSession->username . $_POST[sid] . $_POST[shost]);
                                    }
								}
                                if ($key == 'canReg1'){
                                    $val = implode(',', $val);
                                }
                                if ($key == 'bottom_title') {
                                    $val = htmlspecialchars($val);
                                }
								showXHTML_input('hidden', $key, $val, '', '');
							}
							showXHTML_input('submit', '', $MSG['btn_return'][$sysSession->lang], '', 'class="button01"');
						}
						if ($isSingle == 'Single') {
							$location = 'sch_single.php';
							$msg = $MSG['btn_school_setting'][$sysSession->lang];
						} else {
							$location = 'sch_list.php';
							$msg = $MSG['btn_return_list'][$sysSession->lang];
						}
						//$location = ($isSingle == 'Single') ? 'sch_single.php' : 'sch_list.php';
						showXHTML_input('button', '', $msg, '', 'class="cssBtn" onclick="window.location.replace(\'' . $location . '\')"');
						if ($actType == 'Create' || $mustRestart)
						showXHTML_input('button', '', $MSG['restart_web_server'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'sch_restart.php?restart\')"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
     * 
     */
?>
