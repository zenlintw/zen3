<?php
set_time_limit(0);
ignore_user_abort(true);
define('path_amount_limit', 49);
 
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/quota.php');
require_once(sysDocumentRoot . '/lang/lcms_course_copy.php');
require_once(sysDocumentRoot . '/teach/course/import_imsmanifest.lib.php');
require_once(sysDocumentRoot . '/teach/custom/co_lcms_api.php');

define('LCMS_LOCK_PATH', sysDocumentRoot. "/base/{$sysSession->school_id}/door/LCMS/lock" );
if( !is_dir(LCMS_LOCK_PATH) ){
	@exec('mkdir -p ' . LCMS_LOCK_PATH);
}

if( !defined('IMPORT_MB_SEC') || !defined('ENCODE_FILE') ){
    $importMbSec = 1.36;    //default
    $encodeFile = 'Big5';    //default
    $iniFile = LCMS_LOCK_PATH.'/../import_config.ini';
    if( file_exists($iniFile) ){
        $iniData = parse_ini_file($iniFile);
        $importMbSec = empty($iniData['IMPORT_MB_SEC']) ? $importMbSec : $iniData['IMPORT_MB_SEC'];
        $encodeFile = empty($iniData['ENCODE_FILE']) ? $encodeFile : $iniData['ENCODE_FILE'];
    }else{
        //ini檔不存在則建立ini檔案
        file_put_contents($iniFile,"IMPORT_MB_SEC = {$importMbSec}\nENCODE_FILE = {$encodeFile}");
    }
    if( !defined('IMPORT_MB_SEC') )
        define('IMPORT_MB_SEC', $importMbSec);
    if( !defined('ENCODE_FILE') )
        define('ENCODE_FILE', $encodeFile); 
}
// 檢查是否啟用 LCMS
if (defined('sysLcmsEnable') && !sysLcmsEnable) {
    header("HTTP/1.0 404 Not Found");
    echo 'Not Found!';
    die();
}


if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data) {
        $f = @fopen($filename, 'w');
        if (!$f) {
            return false;
        } else {
            $bytes = fwrite($f, $data);
            fclose($f);
            return $bytes;
        }
    }
}

function saveCourseRelation($old_cid, $new_cid, $lcms_id, $updateCoursePath, $beforeCoursePath, $postParams){
	global $sysConn, $sysSession;
	
	$updateCoursePath = $updateCoursePath;
	$beforeCoursePath = $beforeCoursePath;
	$postParams = json_encode($postParams);
	
	$sql = "REPLACE INTO `lcms_course_wizard` (
			`source_course_id` ,
			`target_course_id` ,
			`lcms_course_id` ,
			`trans_course_path` ,
			`before_course_path`,
			`creater` ,
			`editor` ,
			`post_data`,
			`add_time` ,
			`upd_time`
			)
			VALUES (
			?,  ?,  ?,  ?, ?, ?, ?, ?, NOW(), NOW()
			)";
	$sysConn->Execute($sql,array($old_cid, $new_cid, $lcms_id, $updateCoursePath, $beforeCoursePath,$sysSession->username, $sysSession->username, $postParams));
	
}

function getUserInfo($username){
    global $sysConn;
    $sqls = "select username,first_name,last_name ".
            "from WM_user_account ".
            "where username='$username'";

    return $sysConn->GetRow($sqls);
}

function cloneCourse($old_cid, $new_cid, $replace) {
    global $course_elements;
    $isImportLCMS = false;
    $lcmsImportResult = -1;
    $old_cid = intval($old_cid);
    $new_cid = intval($new_cid);
    $ownerInfo = getUserInfo($_POST['course_owner']);
    $course_owner = array('username'=> $ownerInfo['username'], 'realname'=> "{$ownerInfo['last_name']}{$ownerInfo['first_name']}");
    $lcms_grade = $_POST['lcms_grade'];
    $lcms_subject = intval($_POST['lcms_subject']);
    $lcms_subject_child = intval($_POST['lcms_subject_child']);
    
    if (in_array("subject_board", $course_elements)) // 議題討論板 有勾選
        _processBD($old_cid, $new_cid);     // 議題討論  
    _processQTI($old_cid, $new_cid);                 // 三合一
    
    if (in_array("node", $course_elements)) {          // 教材節點 有勾選
        $content = dbGetOne('WM_term_path', 'content', "course_id={$old_cid} order by serial desc");
		// 將來源課程的教材複製到目的課程
		if($_POST['copy_target_type'] == 0){
			// 先將原本課程的教材複製一份到新課程教材目錄下
			_processContent($old_cid, $new_cid);
			_processTermPath($content, $new_cid, $replace);
		// 將來源課程的教材複製到iSUNTW 課程(學習路徑的節點會一併做轉換)
		}else{
			$isImportLCMS = true;
			list($lcmsImportResult,$updateCoursePath, $lcms_id, $postParams) = _processLCMSContent($old_cid,$content, $new_cid,$course_owner, $lcms_grade, $lcms_subject, $lcms_subject_child);
			// echo "updateCoursePath = $updateCoursePath";
			if(!empty($updateCoursePath)){
				_processTermPath($updateCoursePath, $new_cid, $replace);
				//儲存到 lcms_course_wizard table裡
				saveCourseRelation($old_cid, $new_cid, $lcms_id, $updateCoursePath, $content, $postParams); 
			}else{
                            global $sysSession;
                            $sysSession->cur_func = 800400100;
				_processTermPath($content, $new_cid, $replace);
			}
		}
    }
    
    // 重新計算quota
    getCalQuota($new_cid, $quota_used, $quota_limit);
    setQuota($new_cid, $quota_used);
    return array('isImportLCMS'=>$isImportLCMS,'lcmsImportResult'=>$lcmsImportResult);
}

/**
 * 處理三合一
 * @param int $old_cid 舊課程course_id
 * @param int $new_cid 新課程course_id
 */
function _processQTI($old_cid, $new_cid) {
    global $sysConn, $sysSession, $course_elements;

    foreach (array('exam', 'homework', 'questionnaire') as $qti_which) {
        if (!in_array($qti_which, $course_elements))
            continue;

        $i = 0;
        $old_ident = array();
        $new_ident = array();
        $old_path = sprintf('%s/base/%05d/course/%08d/%s/Q/', sysDocumentRoot, $sysSession->school_id, $old_cid, $qti_which);
        $new_path = sprintf('%s/base/%05d/course/%08d/%s/Q/', sysDocumentRoot, $sysSession->school_id, $new_cid, $qti_which);

        $t = split('[. ]', microtime());
        $ident = sprintf('WM_ITEM1_%s_%u_%s_', sysSiteUID, $new_cid, $t[2]);
        $count = intval(substr($t[1], 0, 6));
        // 複製題目
        $rs = dbGetStMr('WM_qti_' . $qti_which . '_item', '*', 'course_id=' . $old_cid, ADODB_FETCH_ASSOC);
        if ($rs)
            while ($row = $rs->FetchRow()) {
                $old_ident[$i] = $row['ident'];
                $row['ident'] = $ident . ($count++);
                $new_ident[$i] = $row['ident'];
                $row['content'] = str_replace($old_ident[$i], $new_ident[$i], $row['content']);
                $row['course_id'] = $new_cid;

                if ($sysConn->AutoExecute('WM_qti_' . $qti_which . '_item', $row, 'INSERT')) { // 複製夾檔
                    if (is_dir("{$old_path}/{$old_ident[$i]}")) {
                        if (!is_dir($new_path))
                            @exec('mkdir -p ' . $new_path);
                        @exec("cp -Rf {$old_path}/{$old_ident[$i]} {$new_path}/{$new_ident[$i]}");
                    }
                }

                $i++;
            }

        // 複製試卷
        $rs = dbGetStMr('WM_qti_' . $qti_which . '_test', '*', 'course_id=' . $old_cid, ADODB_FETCH_ASSOC);
        if ($rs)
            while ($row = $rs->FetchRow()) {
                $row['exam_id'] = 'NULL';
                $row['course_id'] = $new_cid;
                $row['content'] = str_replace($old_ident, $new_ident, $row['content']);
                $sysConn->AutoExecute('WM_qti_' . $qti_which . '_test', $row, 'INSERT');
            }
    }
}

/**
 * 處理議題討論
 * @param int $old_cid 舊課程course_id
 * @param int $new_cid 新課程course_id
 */
function _processBD($old_cid, $new_cid) {
    global $sysConn, $sysSession;
    list($discuss, $bulletin) = dbGetStSr('WM_term_course', 'discuss,bulletin', "course_id=$old_cid");
    if( is_numeric($discuss) && is_numeric($bulletin) ){
        $RS = dbGetStMr('WM_bbs_boards', '*', "owner_id=$old_cid and board_id not in ($discuss,$bulletin) and `title` != '討論室紀錄'");
        while ($fields = $RS->FetchRow()) {
            $bbs_sql = 'insert into WM_bbs_boards (bname, manager, title, owner_id, open_time, close_time, share_time, switch, with_attach, vpost, default_order, post_times, extras) values ' .
                    "('" . addslashes($fields['bname']) . "','{$fields['manager']}','" . addslashes($fields['title']) . "','{$new_cid}','{$fields['open_time']}','{$fields['close_time']}','{$fields['share_time']}'," .
                    "'{$fields['switch']}','{$fields['with_attach']}','{$fields['vpost']}','{$fields['default_order']}','{$fields['post_times']}','{$fields['extras']}')";
            $sysConn->Execute($bbs_sql);
            $board_id = $sysConn->Insert_ID();
            $RS1 = dbGetStMr('WM_term_subject', '*', "course_id=$old_cid and board_id = '{$fields['board_id']}'");
            while ($fields1 = $RS1->FetchRow()) {
                $sub_sql = 'insert into WM_term_subject (course_id, board_id, state, visibility, permute) values ' .
                        "('{$new_cid}','{$board_id}','{$fields1['state']}','{$fields1['visibility']}','{$fields1['permute']}')";
                $sysConn->Execute($sub_sql);
            } 
        }
    }
}

/**
 * 處理學習路徑
 * @param int $old_cid 舊課程course_id
 * @param int $new_cid 新課程course_id
 */
function _processTermPath($content, $new_cid, $replace) {
    global $sysConn;
    if(empty($content)) return;
    list($new_content, $path_amount) = dbGetStSr('WM_term_path', 'content, serial', "course_id={$new_cid} order by serial DESC limit 1", ADODB_FETCH_NUM);

    if (count($path_amount) > path_amount_limit) { // 如果存超過 50 個路徑
        // 刪除 50 以前的
        dbDel('WM_term_path', 'course_id=' . $new_cid . ' and serial in (' . implode(',', array_slice($path_amount, path_amount_limit)) . ')');
        // 更改最近 50 個
        for ($i = path_amount_limit - 1; $i >= 0; $i--) {
            dbSet('WM_term_path', 'serial=' . (path_amount_limit - $i), 'course_id=' . $new_cid . ' and serial=' . $path_amount[$i]);
        }
    }

    // 取得舊課程的學習路徑
    processNewImsmanifest($content, $replace, false);
}

function getCourseNameById($courseId){
    global $sysSession,$sysConn;
    $caption = $sysConn->GetOne("select caption from WM_term_course where course_id=$courseId");
    $cnames = unserialize($caption);
    return $cnames[$sysSession->lang];
}

/**
 * 處理教材
 * @param int $old_cid 舊課程course_id
 * @param int $new_cid 新課程course_id
 */
function _processContent($old_cid, $new_cid) {
    global $sysSession;
    $old_path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$old_cid}";
    $new_path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$new_cid}";
    if (!is_dir($old_path))
        exec('mkdir -p ' . $old_path);
    if (!is_dir($new_path))
        exec('mkdir -p ' . $new_path);
    @exec("cp -Rf {$old_path}/content {$new_path}");
}

/**
 * 處理LCMS教材
 * @param type $old_cid
 * @param type $new_cid
 * @param type $course_owner
 * @param type $lcms_grade
 * @param type $lcms_subject
 * @param type $lcms_subject_child
 */
function _processLCMSContent($old_cid,$content, $new_cid, $course_owner, $lcms_grade, $lcms_subject, $lcms_subject_child){
    global $sysSession, $sysConn;
    
	
    $code = 0;
    $xml = '';
	$id = '';
    
	$old_path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$old_cid}/content";
	$new_path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$new_cid}/content";
	//要暫存的課程包路徑
	$coursePackageName = "/base/{$sysSession->school_id}/door/lcms_course_package_{$new_cid}.zip";
	$lcmsCourseFile = sysDocumentRoot.$coursePackageName;
	
	// 若已匯入過LCMS, 則只補上或更新缺少的節點, 異動素材內容
	$relData = getRelation($new_cid);
        if (empty($_COOKIE['show_me_info']) === false) {
            echo '<pre>';
            var_dump('relData', count($relData) > 0);
            echo '</pre>';
        }
	if(count($relData) > 0){
		//建立課程包
		_processContent($old_cid, $new_cid);
        $needExportToLCMS = buildDiffContent($content,$relData['trans_course_path'],$relData['before_course_path'],$relData['lcms_course_id'], $old_path, $new_path, $lcmsCourseFile);
	}else{
		// 先將原本課程的教材複製一份到新課程教材目錄下
		_processContent($old_cid, $new_cid);
		//開始打包LCMS課程包
		$needExportToLCMS = lcmsContentPackage($content, $old_path, $new_path, $lcmsCourseFile);
	}
    //3. 送出到lcms 佇列處理
    if($needExportToLCMS && file_exists($lcmsCourseFile)){
		$teachers = getTeachers($sysSession->course_id);
		$mail_list = array();
		$editors   = array();
		foreach($teachers as $v){
			$mail_list[]=$v['email'];
			$editors[]=array('username'=> $v['username'], 'realname'=> "{$v['last_name']}{$v['first_name']}");
		}
		$wmproHostPort = $_SERVER['SERVER_PORT'] == 80 ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'];
		
		//已有匯入過就取之前的設定重送
		if(!empty($relData['post_data'])){
			$params = json_decode($relData['post_data'],true);
			$params['jobs']['export_url'] = 'http://'.$wmproHostPort.$coursePackageName;
			
		}else{
			$params = array(
				'jobs' => array(
					'export_url' => 'http://'.$wmproHostPort.$coursePackageName,
					'exportor' => $course_owner,
					'editors' => $editors,
					'grade' => $lcms_grade,
					'subject' => array($lcms_subject, $lcms_subject_child),
					'mail_list' => $mail_list,
					'course_name' => getCourseNameById($old_cid),
                    'school_name' => $sysSession->school_name,
                    'encode_file' => ENCODE_FILE
				)
			);
		}
        
		$resData = lcms_api('api/import/queue', array('data'=> json_encode($params)), 'post');
		$code = $resData['response']['code'];
		$xml = $resData['response']['data']['xml'];
		$id = $resData['response']['data']['task_id'];
		
		//remove coures package
		@unlink($lcmsCourseFile); 
    } else {
        $code = -1;
    }
    return array($code, $xml, $id, $params);
}

function buildDiffContent($content, $trans_course_path, $before_course_path, $lcms_course_id,$contentPath, $new_path, $saveFileFullPath ){
	global $supportFileType;
    $log = '';
    $needExportToLCMS = false;
    
	if(file_exists($saveFileFullPath))	@unlink($saveFileFullPath);
	
     //建立壓縮檔暫存路徑
    $zipTmpDirName = 'tmp_lcms_zip';
    $zipTmpDir = $new_path.'/'.$zipTmpDirName;
	if( !is_dir($zipTmpDir) ){
		@exec('mkdir -p '.$zipTmpDir);
	}
    //比對教材節點
    //$content - 來源課程目前節點 vs $before_course_path - 來源課程當初匯入到LCMS的節點
	
	//來源XML
    $newpath = domxml_open_mem(str_replace('xml:base','xml_base',$content));
    $ctx1 = xpath_new_context($newpath);
    
	//set lcms course id
	$organizations = $ctx1->xpath_eval("/manifest/organizations");
	$organizations->nodeset[0]->set_attribute('lcms_course_id',$lcms_course_id);
	
	//匯入前XML
    $befpath = domxml_open_mem(str_replace('xml:base','xml_base',$before_course_path));
    $ctx2 = xpath_new_context($befpath);
    
	//匯入後XML
    $lcmspath = domxml_open_mem(str_replace('xml:base','xml_base',$trans_course_path));
    $ctx3 = xpath_new_context($lcmspath);
	
	//處理item的unit id
	$tmpItems = $ctx3->xpath_eval("/manifest/organizations/organization/item");
	foreach($tmpItems->nodeset as $tmpItem){
		$unit_id = $tmpItem->get_attribute('unit_id');
		$item_id = $tmpItem->get_attribute('identifier');
		if(!empty($unit_id)){
			$findItem=$ctx1->xpath_eval("//item[@identifier='$item_id']");
			if( !empty($findItem->nodeset) ){
				$findItem->nodeset[0]->set_attribute('unit_id', $unit_id);
			}
		}
	}
	
    $ret = $ctx1->xpath_eval("/manifest/resources/resource");
    if ($ret)
    foreach($ret->nodeset as $res)
    {
		$base = $res->get_attribute('xml_base');
        $href = $res->get_attribute('href');
        $rid  = $res->get_attribute('identifier');
		
		//路徑若有中文需要轉碼, 否則會找不到該檔案
        /* 
        1. 從標準版-[教材庫]來的, URL為以下
        /base/10001/content/100002/ISUNTW架構.png
        2. 從客製功能-[教材沿用]來的, URL為如下
        /base/10001/course/10000001/content/功能確認_960816.doc
        3. 從客製功能-[個人教材庫]來的, URL為如下
        /base/10001/content/100032/CCNA/Network5_02Communication.ppt
        4. 標準課程教材 
        sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$old_cid}/content"
         */
        preg_match("|/base/([\d]+)/content/([\d]+)/|", $base . $href,$mth); //case 1,3
        preg_match("|/base/([\d]+)/course/([\d]+)/content/|",$base . $href,$mth2);  //case 2
        $old_path = $contentPath;
        if(!empty($mth[1])){ //case 1,3
            $old_path = sysDocumentRoot."/base/{$mth[1]}/content/{$mth[2]}";
            $realHref = mb_convert_encoding(sysDocumentRoot.$base . $href, ENCODE_FILE, 'UTF-8');
        }else if(!empty($mth2[1])){ //case 2
            $old_path = sysDocumentRoot."/base/{$mth2[1]}/course/{$mth2[2]}/content";
            $realHref = mb_convert_encoding(sysDocumentRoot.$base . $href, ENCODE_FILE, 'UTF-8');
        }else{  //case 4 標準課程庫
            $realHref = mb_convert_encoding($old_path .'/'. $base . $href, ENCODE_FILE, 'UTF-8');
        }
        $resInfo = mbPathinfo($realHref);
		
        $tmpR = $ctx2->xpath_eval("/manifest/resources/resource[@identifier='$rid']");
		$tmp3 = $ctx3->xpath_eval("/manifest/resources/resource[@identifier='$rid']");
				
		if( !empty($tmpR->nodeset) ){	//此為原有節點
			
			//原節點未更新
			if( $tmpR->nodeset[0]->get_attribute('href') == $href && $tmpR->nodeset[0]->get_attribute('xml_base') == $base){
				//節點的檔案並沒改變, 則在屬性加入 NO_NEED_UPDATE
				//並將href改為lcms asset play url
				$assetId  = '';
                
				if( !empty($tmp3->nodeset)){
					$tmp3Href = $tmp3->nodeset[0]->get_attribute('href');
                    preg_match('|asset/play/([\d]+)/|',$tmp3Href,$mth);
					if( is_numeric($mth[1]) ){
                        $assetId = ',' . $mth[1];
                        // 更新unit_id
                        // 新增的node反查item-id, 用item-id往上層追查到unit_id
                        $tmp4 = $ctx1->xpath_eval("//item[@identifierref='$rid']/ancestor-or-self::item");
                        if( !empty($tmp4->nodeset)){
                            $lastId = $tmp4->nodeset[0]->get_attribute('identifier');
                            $tmp5=$ctx3->xpath_eval("//item[@identifier='$lastId']");
                            if( !empty($tmp5->nodeset)){
                                $unitId = $tmp5->nodeset[0]->get_attribute('unit_id');
                                if( is_numeric($unitId) ){
                                    $res->set_attribute('unit_id',$unitId);
                                }
                            }
                        }
                    }
				}
                
                $res->set_attribute('asset_id', 'NO_NEED_UPDATE'.$assetId);
			//原節點有更新
			}else{
				if( !empty($tmp3->nodeset)){
					$tmp3Href = $tmp3->nodeset[0]->get_attribute('href');
					//lcms url format: http://lcms-lubo.sun.net.tw/asset/play/233/
					preg_match('|asset/play/([\d]+)/|',$tmp3Href,$mth);
					if( is_numeric($mth[1]) ){
						$res->set_attribute('asset_id', $mth[1]);
                        // 更新unit_id
                        // 新增的node反查item-id, 用item-id往上層追查到unit_id
                        $tmp4 = $ctx1->xpath_eval("//item[@identifierref='$rid']/ancestor-or-self::item");
                        if( !empty($tmp4->nodeset)){
                            $lastId = $tmp4->nodeset[0]->get_attribute('identifier');
                            $tmp5=$ctx3->xpath_eval("//item[@identifier='$lastId']");
                            if( !empty($tmp5->nodeset)){
                                $unitId = $tmp5->nodeset[0]->get_attribute('unit_id');
                                if( is_numeric($unitId) ){
                                    $res->set_attribute('unit_id',$unitId);
                                }
                            }
                        }
					}
				}
			}
		}
		//copy檔案
		if(file_exists($realHref)){
			// 更新unit_id
			// 新增的node反查item-id, 用item-id往上層追查到unit_id
			$tmp4 = $ctx1->xpath_eval("//item[@identifierref='$rid']/ancestor-or-self::item");
			if( !empty($tmp4->nodeset)){
				$lastId = $tmp4->nodeset[0]->get_attribute('identifier');
				$tmp5=$ctx3->xpath_eval("//item[@identifier='$lastId']");
				if( !empty($tmp5->nodeset)){
					$unitId = $tmp5->nodeset[0]->get_attribute('unit_id');
					if( is_numeric($unitId) ){
						$res->set_attribute('unit_id',$unitId);
					}
				}
			}
			// 偵測 PowerCam6的規格:判斷 fsc.js 與 index.html 這兩支檔案
			// 若powercam的檔案位於教材根目錄則不符合規則, 不處理
			if( $resInfo['dirname'] != dirname($old_path) && strcmp($resInfo['basename'],'index.html') == 0 && is_powercam($realHref) ){
				@mkdir($zipTmpDir);
				
				$folder = str_replace($old_path,'',$resInfo['dirname']);
				if(!empty($folder)){
					$log .= "\n create $zipTmpDir{$folder}";
					@exec('mkdir -p "'. $zipTmpDir.$folder.'"');
				}
				
				// $sourceDir 是放置已從原課程複制過來的檔案
				$sourceDir = str_replace($old_path,$new_path,$resInfo['dirname']);
				rename($sourceDir, $zipTmpDir.$folder);
				$log .= "\n[powercam]rename {$sourceDir} to $zipTmpDir{$folder}";
				$needExportToLCMS = true;
			//只打包LCMS支援的檔案類型
			}else if (in_array(strtolower($resInfo['extension']), $supportFileType)){
				@mkdir($zipTmpDir);
			   
				$folder = str_replace($old_path,'',$resInfo['dirname']);
				if(!empty($folder)){
					$log .= "\n create $zipTmpDir{$folder}";
					@exec('mkdir -p "'. $zipTmpDir.$folder.'"');
				}
				$log .= "copy from $realHref to {$zipTmpDir}{$folder}/{$resInfo['basename']}";
				$sourceFile = str_replace($old_path,$new_path,$realHref);
                if( file_exists($sourceFile) ){
                    @rename($sourceFile, "{$zipTmpDir}{$folder}/{$resInfo['basename']}");
                }else{
                    @copy($realHref, "{$zipTmpDir}{$folder}/{$resInfo['basename']}");
                }
				$needExportToLCMS = true;
			}
		}else{
			$log .= "\n[file_not_exists] $realHref\n";
		}
	}
	$newpath->dump_file("$zipTmpDir/wm_course_path.xml");
	//zip
	//$cmd = "cd $new_path;zip -m -q -r $saveFileFullPath $zipTmpDirName";    //-m參數: 壓縮後自動刪除來源目錄or檔案
	$cmd = "cd $new_path;tar zcvf $saveFileFullPath $zipTmpDirName";    //-m參數: 壓縮後自動刪除來源目錄or檔案
        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0, 1, 'teacher',$_SERVER['PHP_SELF'], $cmd);
	@exec($cmd);
	exec("rm -Rf $zipTmpDirName");
	if(file_exists($saveFileFullPath))	return true;
	return false;
}

/**
 * 解悉xml , 取出要上傳到lcms的檔案, 不需上傳的要copy回新課程目錄
 * @param type $content
 * @param type $old_path
 * @param type $new_path
 * @param type $saveFileFullPath
 * @return boolean
 */
function lcmsContentPackage($content, $sourcePath, $new_path, $saveFileFullPath ){
    global $supportFileType;
    $log = '';
    $needExportToLCMS = false;
    if (empty($_COOKIE['show_me_info']) === false) {
        echo '<pre>';
        var_dump(empty($content));
        echo '</pre>';
    }
    if(empty($content)) return false;
    
     //建立壓縮檔暫存路徑
    $zipTmpDirName = 'tmp_lcms_zip';
    $zipTmpDir = $new_path.'/'.$zipTmpDirName;
    
    //解悉教材
    
    $newpath = @domxml_open_mem(str_replace('xml:base','xml_base',$content));
    $ctx1 = xpath_new_context($newpath);
    
    $ret = $ctx1->xpath_eval("/manifest/resources/resource");
    if ($ret)
    foreach($ret->nodeset as $res)
    {
        $old_path = $sourcePath;
        $base = $res->get_attribute('xml_base');
        $href = $res->get_attribute('href');
        //路徑若有中文需要轉碼, 否則會找不到該檔案
        /* 
        1. 從標準版-[教材庫]來的, URL為以下
        /base/10001/content/100002/ISUNTW架構.png
        2. 從客製功能-[教材沿用]來的, URL為如下
        /base/10001/course/10000001/content/功能確認_960816.doc
        3. 從客製功能-[個人教材庫]來的, URL為如下
        /base/10001/content/100032/CCNA/Network5_02Communication.ppt
        4. 標準課程教材 
        sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$old_cid}/content"
         */
        preg_match("|/base/([\d]+)/content/([\d]+)/|", $base . $href,$mth); //case 1,3
        preg_match("|/base/([\d]+)/course/([\d]+)/content/|",$base . $href,$mth2);  //case 2
        
        if(!empty($mth[1])){ //case 1,3
            $old_path = sysDocumentRoot."/base/{$mth[1]}/content/{$mth[2]}";
            $realHref = mb_convert_encoding(sysDocumentRoot.$base . $href, ENCODE_FILE, 'UTF-8');
        }else if(!empty($mth2[1])){ //case 2
            $old_path = sysDocumentRoot."/base/{$mth2[1]}/course/{$mth2[2]}/content";
            $realHref = mb_convert_encoding(sysDocumentRoot.$base . $href, ENCODE_FILE, 'UTF-8');
        }else{  //case 4 標準課程庫
            $realHref = mb_convert_encoding($old_path .'/'. $base . $href, ENCODE_FILE, 'UTF-8');
        }
        $resInfo = mbPathinfo($realHref);
        $log .= "\n".json_encode($resInfo);
        $log .= "\nbase=$base, href=$href, old_path=$old_path";
        $log .= "\n[start]$realHref";
        if (empty($_COOKIE['show_me_info']) === false) {
            echo '<pre>';
            var_dump('realHref', $realHref, file_exists($realHref));
            echo '</pre>';
        }
        if(file_exists($realHref)){
            // 偵測 PowerCam6的規格:判斷 fsc.js 與 index.html 這兩支檔案
            // 若powercam的檔案位於教材根目錄則不符合規則, 不處理
            if( $resInfo['dirname'] != dirname($old_path) && strcmp($resInfo['basename'],'index.html') == 0 && is_powercam($realHref) ){
                @mkdir($zipTmpDir);
                
                $folder = str_replace($old_path,'',$resInfo['dirname']);
                if(!empty($folder)){
                    $log .= "\n create $zipTmpDir{$folder}";
                    @exec('mkdir -p "'. $zipTmpDir.$folder.'"');
                }
                
                // $sourceDir 是放置已從原課程複制過來的檔案
                $sourceDir = str_replace($old_path,$new_path,$resInfo['dirname']);
                rename($sourceDir, $zipTmpDir.$folder);
                $log .= "\n[powercam]rename {$sourceDir} to $zipTmpDir{$folder}";
                $needExportToLCMS = true;
            //只打包LCMS支援的檔案類型
            }else if (in_array(strtolower($resInfo['extension']), $supportFileType)){
                @mkdir($zipTmpDir);
               
                $folder = str_replace($old_path,'',$resInfo['dirname']);
                if(!empty($folder)){
                    $log .= "\n create $zipTmpDir{$folder}";
                    @exec('mkdir -p "'. $zipTmpDir.$folder.'"');
                }
                $log .= "copy from $realHref to {$zipTmpDir}{$folder}/{$resInfo['basename']}";
                $sourceFile = str_replace($old_path,$new_path,$realHref);
                if( file_exists($sourceFile) ){
                    @rename($sourceFile, "{$zipTmpDir}{$folder}/{$resInfo['basename']}");
                }else{
                    @copy($realHref, "{$zipTmpDir}{$folder}/{$resInfo['basename']}");
                }
                $log .= "\n[files]copy {$sourceFile} to {$zipTmpDir}{$folder}/{$resInfo['basename']}";
                $needExportToLCMS = true;
            //其餘的已在新課程目錄下，所以不處理
            }
            
        }else{
            $log .= "\n[file_not_exists]";
        }
        $log .= '------------------------';
    }
    if (empty($_COOKIE['show_me_info']) === false) {
        echo '<pre>';
        var_dump('needExportToLCMS', $needExportToLCMS);
        echo '</pre>';
    }
    if($needExportToLCMS){
        //put xml
        file_put_contents("$zipTmpDir/wm_course_path.xml", $content);
        //zip
        // $cmd = "cd $new_path;zip -m -q -r $saveFileFullPath $zipTmpDirName";    //-m參數: 壓縮後自動刪除來源目錄or檔案
        // @exec($cmd);
        $cmd = "cd $new_path;tar zcvf $saveFileFullPath $zipTmpDirName";    //-m參數: 壓縮後自動刪除來源目錄or檔案
        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0, 1, 'teacher',$_SERVER['PHP_SELF'], $cmd);
        @exec($cmd);
        exec("rm -Rf $zipTmpDirName");
        $log .= "\n[zip-lcms-package]$cmd";
    }
    //save log
    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'],  $log );
    return $needExportToLCMS;
}

/**
 * 檢查是否為PowerCam的教材檔案
 * @param type $file
 * @return boolean
 */
function is_powercam($file){
    if(file_exists($file)){
        $content = file_get_contents($file);
        //只要檔案內容有fsc.js即當作powercam6的格式 , fsctrl.js 當作powercam5的格式
        if(strpos($content, 'fsc.js') !== false || strpos($content, 'fsctrl.js') !== false){
            return true;
        }
    }
    return false;
}

function getTeachers($course_id){
    global $sysConn;
//    $sysSession->course_id
    $sqls = "select b.username,b.role&704 as level,c.course_id,c.caption,a.first_name,a.last_name,a.email ".
            "from WM_term_major as b, WM_term_course as c ,WM_user_account as a ".
            "where b.course_id = {$course_id} and b.course_id = c.course_id and a.username = b.username and b.role&704";

    return $sysConn->GetAssoc($sqls);
}

function removeRelation($course_id){
	global $sysConn,$sysSession;
	if(!is_numeric($course_id)) return false;
	
	$sysConn->Execute("delete from lcms_course_wizard where target_course_id=$course_id");
}

function getRelation($course_id){
	global $sysConn,$sysSession;
	if(!is_numeric($course_id)) return false;
	
	return $sysConn->GetRow("select * from lcms_course_wizard where target_course_id=$course_id");
}

function estimateImportTime($courseId){
	global $sysSession;
	
	$contentPath = sprintf('%s/base/%05d/course/%08d/content/', sysDocumentRoot, $sysSession->school_id, $courseId);
	$totalBytes = dirSize($contentPath);
	
	//計算預估匯入時間
	// 目前是有實測轉2.4G要30分鐘,所以換算下來的公式是1mb=1.36秒,10mb=13秒
	//Example: 1 Bytes 需費時 
	if( !empty($totalBytes) ){
		$mb = round($totalBytes/1024/1024, 2);
		$expireSec = round($mb * IMPORT_MB_SEC, 0);
		// echo 'now='.date('Y/m/d H:i:s').",mb=$mb,expire=".date('Y/m/d H:i:s', time()+$expireSec)."\n";
		// now=2014/04/20 22:35:44,mb=20.26,expire=2014/04/20 22:36:12
		return $expireSec;
	}
	return 0;
}

function lockCourseStep1($old_cid, $new_cid){
	$lockSource = LCMS_LOCK_PATH."/{$old_cid}.txt";
	$lockTarget = LCMS_LOCK_PATH."/{$new_cid}.txt";
	
	if( file_exists($lockSource) ){
		$expire = intval(@file_get_contents($lockSource));
		if( $expire < time() ){
			//過期, 刪除掉lock file
			@unlink($lockSource);
		}else{
			return false;	//有人使用中, 無法lock
		}
	}
	
	if( file_exists($lockTarget) ){
		$expire = intval(@file_get_contents($lockTarget));
		if( $expire < time() ){
			//過期, 刪除掉lock file
			@unlink($lockTarget);
		}else{
			return false;	//有人使用中, 無法lock
		}
	}
	
	//將來源、目的課程鎖住"5秒"不讓別人操作
	$expire = time()+5;
	file_put_contents($lockSource, $expire);
	file_put_contents($lockTarget, $expire);
	
	return true;
}
function lockCourseStep2($old_cid, $new_cid, $size){
	$lockSource = LCMS_LOCK_PATH."/{$old_cid}.txt";
	$lockTarget = LCMS_LOCK_PATH."/{$new_cid}.txt";
	
	//將來源、目的課程鎖住"預估匯入完成秒"不讓別人操作
	$expire = time()+estimateImportTime($old_cid);
	file_put_contents($lockSource, $expire);
	file_put_contents($lockTarget, $expire);
}
function unlockCourse($old_cid, $new_cid){
	$lockSource = LCMS_LOCK_PATH."/{$old_cid}.txt";
	$lockTarget = LCMS_LOCK_PATH."/{$new_cid}.txt";
	@unlink($lockSource);
	@unlink($lockTarget);
}

function dirSize($dir){
	
    $io = popen ( '/usr/bin/du -skb ' . $dir, 'r' );
    $size = fgets ( $io, 4096);
    $size = substr ( $size, 0, strpos ( $size, "\t" ) );
    pclose ( $io );
    
	return $size;
}

switch($_GET['api']) {
    case 'getSupportFileType':
        echo json_encode(getSupportFileType());
    break;
	case 'cloneCourse':
        $old_cid = intval($_POST['course_id']);
		$new_cid = $sysSession->course_id;
		//鎖定來源及目的課程
		if( lockCourseStep1($old_cid, $new_cid) ){
			$supportFileType = getSupportFileType();
			$course_elements = $_POST['course_elements']; // 包裝內容選項    
			
			$replace = intval($_POST['course_path_replace']) == 0 ? false: true;
			if (is_array($course_elements)) {
				
				lockCourseStep2($old_cid, $new_cid, $size);
				$data = cloneCourse($old_cid, $new_cid, $replace);
				echo json_encode($data);
			}
			unlockCourse($old_cid, $new_cid);
		}else{
			echo json_encode(array('Error'=>true,'Message'=> $MSG['lcms_locked'][$sysSession->lang] ));
		}
	break; 
	case 'getQuota':
		$data = array();
        //取得要匯進來的課程大小
		//getCalQuota($_GET['course_id'], $quota_used, $quota_limit);
        list($lang,$quota_used,$quota_limit) =  dbGetStSr('WM_term_course','caption,quota_used,quota_limit ',"course_id ='{$_GET['course_id']}'", ADODB_FETCH_NUM);
        //取得目前剩餘容量
        //getCalQuota($sysSession->course_id, $self_quota, $self_quota_limit);
        //$empty_space = $self_quota_limit-$self_quota;
        
		//$data['used'] = round(($quota_used/1024),3) . 'MB';
		$data['used'] = ($quota_used/1000) . 'MB';
		//$data['limit'] = intval($empty_space);
		//$data['sys_limit'] = CoursePackLimit;
		echo json_encode($data);
	break;
	case 'removeRelation':
		removeRelation($sysSession->course_id);
	break;
}
?>
