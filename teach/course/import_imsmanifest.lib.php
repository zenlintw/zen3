<?php
/* Custom By TN 20120117(B)MIS#023740*/
require_once(sysDocumentRoot . '/lang/course_pack_install.php');
/* Custom By TN 20120117(E)MIS#023740*/

/**
 * 匯入 imsmanifest.xml 公用函式 (cour_import1.php, cour_path_import.php)
 *
 * @version  $Id: import_imsmanifest.lib.php,v 1.1 2010/02/24 02:40:24 saly Exp $
 *
 
 /**
 * strip the default namespace (xmlns="") of xml content
 */
function stripDefaultNamespace($str)
{
    return preg_replace('/\bxmlns\s*=\s*"[^"]*"/sU', '', $str);
}

function savePath($path)
{
		global $sysSession, $sysConn, $MSG;
		list($serial) = dbGetStSr('WM_term_path', 'max(serial)', "course_id={$sysSession->course_id}", ADODB_FETCH_NUM);
		if (!$serial || $serial < 1) $serial = 1;
		else $serial++;
		dbNew('WM_term_path', 'course_id,serial,content,username,update_time', "{$sysSession->course_id},{$serial},{$path}, '{$sysSession->username}', now()");

		/* dbSet('WM_term_path', 'content=' . $path, "course_id={$sysSession->course_id} and serial=1");
		if ($sysConn->Affected_Rows() == 0)
    {
    dbNew('WM_term_path', 'course_id,serial,content', $sysSession->course_id . ',1,' . $path);
    if ($sysConn->Affected_Rows() == 0)
    {
    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '錯誤！無法儲存新路徑:' . $path);
    die($MSG['error1'][$sysSession->lang]);
    }
    } */
    wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 0, 'auto', $_SERVER['PHP_SELF'], 'save path :' . "\'" . substr($path, 1, strlen($path) - 2) . "\'");
}

/**
 * get XML encoding value & iconv
 */
function convEncodingValue($str)
{
    $rtn = $str;
    $reg = "/<?xml.*encoding=['\"](.*?)['\"].*?>/";
    if (preg_match($reg, $rtn, $ary)) {
        $encoding = strtoupper($ary[1]);
        if ($encoding != 'UTF-8') {
            $xmlstr = preg_replace("/{$ary[1]}/", 'UTF-8', $ary[0]);
            $rtn    = preg_replace($reg, $xmlstr, $rtn);
            return mb_convert_encoding($rtn, 'UTF-8', $encoding);
        }
    }
    return mb_convert_encoding($rtn, 'UTF-8', 'UTF-8');
}

/**
 * process the new imsmanifest.xml
 */
function processNewImsmanifest($imsmanifest, $replace = false, $check_file = true) //custom by lubo
{
    
    global $sysConn, $sysSession, $MSG;
        $organizationCount=0;
        //if (file_exists($imsmanifest)){
		/*Custom by lubo (B) -- 擴增check file參數,讓 $imsmanifest 可直接吃xml字串*/
		if ($check_file){
            if(!file_exists($imsmanifest))  return false;
            $newpath = @domxml_open_mem(stripDefaultNamespace(convEncodingValue(file_get_contents($imsmanifest))));
        }else{
            $newpath = @domxml_open_mem($imsmanifest);
        }
        /*Custom by lubo (E) */
    // $newpath = @domxml_open_mem(stripDefaultNamespace(convEncodingValue(file_get_contents($imsmanifest))));
			if ($newpath == false) {
        wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 4, 'auto', $_SERVER['PHP_SELF'], '錯誤！imsmanifest.xml無法辨識！');
        die($MSG['error2'][$sysSession->lang]);
    }
    $ctx1 = xpath_new_context($newpath);
    
    // 匯入時代換課程路徑的中的課程名稱為該門課的課程名稱 Start
    $ret            = $ctx1->xpath_eval('manifest/organizations/@default');
    $default_org_id = $ret->nodeset[0]->value;
    if (empty($default_org_id))
        $ret = $ctx1->xpath_eval("/manifest/organizations/organization[1]/title");
    else
        $ret = $ctx1->xpath_eval("/manifest/organizations/organization[@identifier='{$default_org_id}']/title");
    
    if (is_array($ret->nodeset) && count($ret->nodeset)) {
        $oldnode = $ret->nodeset[0];
        $doc     = $oldnode->owner_document();
        $newnode = $doc->create_element('title');
				$newnode->append_child($doc->create_text_node($sysSession->course_name));
				$oldnode->replace_node($newnode);
			}
			else {
				if (empty($default_org_id))
					$ret = $ctx1->xpath_eval("/manifest/organizations/organization[1]");
				else
					$ret = $ctx1->xpath_eval("/manifest/organizations/organization[@identifier='{$default_org_id}']");
            if (!is_object($ret))
            {
                wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 6, 'auto', $_SERVER['PHP_SELF'], $MSG['error3'][$sysSession->lang]);
                die($MSG['error11'][$sysSession->lang]);
            }
				$org = $ret->nodeset[0];
				$doc = $org->owner_document();
        $newnode = $doc->create_element('title');
        $newnode->append_child($doc->create_text_node($sysSession->course_name));
        $org = $ret->nodeset[0];
        $org->append_child($newnode);
    }
    // 匯入時代換課程路徑的中的課程名稱為該門課的課程名稱 End
    //計算複製節點數(B)
    
            $ret = $ctx1->xpath_eval('/manifest/organizations/@default');
			$default_org_id = $ret->nodeset[0]->value;
            if (empty($default_org_id))
				$ret = $ctx1->xpath_eval("/manifest/organizations/organization[1]//item");
			else
				$ret = $ctx1->xpath_eval("/manifest/organizations/organization[@identifier='{$default_org_id}']//item");        
            if ($ret) $organizationCount=count($ret->nodeset);    
		
            //計算複製節點數(E)
			$old = $sysConn->GetOne("select content from WM_term_path where course_id={$sysSession->course_id} order by serial desc");
			if (empty($old) || $replace)
			{
				/* Custom By TN 20120117(B)MIS#023740*/
                savePath($sysConn->qstr($newpath->dump_mem()));
                /* Custom By TN 20120117(E)MIS#023740*/
				return $organizationCount;
			}

			$oldpath = @domxml_open_mem(stripDefaultNamespace($old));
			if ($oldpath == false){
        wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 4, 'auto', $_SERVER['PHP_SELF'], $MSG['error2'][$sysSession->lang]);
        die($MSG['error2'][$sysSession->lang]);
    }
    $ctx2 = xpath_new_context($oldpath);
    
    $ret            = $ctx2->xpath_eval('/manifest/organizations/@default');
    $default_org_id = $ret->nodeset[0]->value;
    if (empty($default_org_id))
        $ret = $ctx2->xpath_eval("/manifest/organizations/organization[1]");
			else
				$ret = $ctx2->xpath_eval("/manifest/organizations/organization[@identifier='{$default_org_id}']");
			$old_organization = $ret->nodeset[0];
			if (!is_object($old_organization))
			{
			    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 6, 'auto', $_SERVER['PHP_SELF'], $MSG['error3'][$sysSession->lang]);
				die($MSG['error3'][$sysSession->lang]);
			}

			$ret = $ctx2->xpath_eval("/manifest/resources");
			$old_resources = $ret->nodeset[0];
			if (!is_object($old_resources))
			{
			    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 7, 'auto', $_SERVER['PHP_SELF'], $MSG['error4'][$sysSession->lang]);
				die($MSG['error4'][$sysSession->lang]);
			}

			// 開始處理合併

			$ret = $ctx2->xpath_eval("/manifest/resources/resource/@identifier");
			$old_resource_ids = array();	// 舊路徑中所有的 resource 的 identifier
			if ($ret)
			foreach($ret->nodeset as $res) $old_resource_ids[] = $res->value;

			// 合併 <resources>，同 identifier 的 <resource> 忽略
			$ret = $ctx1->xpath_eval("/manifest/resources/resource");
			if ($ret) {
        
        $sn = 1;
        foreach ($ret->nodeset as $res) {
            $identifierRawItem = $res->get_attribute('identifier');
            // 新值
            $identifierNewItem = 'SCO_' . $sysSession->course_id . '_' . time() . sprintf("%06d", $sn);
            // 異動表：定義舊轉新，但舊的編號要給予流水號
            $changeList[$identifierRawItem][count($changeList[$identifierRawItem])] = $identifierNewItem;
            $res->set_attribute('identifier', $identifierNewItem);
//            if (in_array($res->get_attribute('identifier'), $old_resource_ids))
//                continue;
            $old_resources->append_child($res->clone_node(true));
//            echo '<pre>';
//            var_dump('resources');
//            var_dump($sn);
//            echo '</pre>';
            $sn++;
        }
                        }
    // 異動表
//    echo '<pre>';
//    var_dump($changeList);
//    echo '</pre>';
        
    // 置換新加入的節點    
    $ret            = $ctx1->xpath_eval('/manifest/organizations/@default');
    $default_org_id = $ret->nodeset[0]->value;
    if (empty($default_org_id))
        $ret = $ctx1->xpath_eval("/manifest/organizations/organization[1]//item");
    else
        $ret = $ctx1->xpath_eval("/manifest/organizations/organization[@identifier='{$default_org_id}']//item");
        
    if ($ret) {
        $replaced = array();
        foreach ($ret->nodeset as $res) {
            $identifierRawItem = $res->get_attribute('identifierref');
            // 從異動表取要換成什麼新值
            $identifierNewItem = $changeList[$identifierRawItem][count($replaced[$identifierRawItem])];
           
            // 由於資源共用，導致資源結構少一個，故新增一個節點
//            echo '<pre>';
//            var_dump($identifierRawItem);
//            var_dump($identifierNewItem);
//            echo '</pre>';
            if ($identifierNewItem === null) {
                // 組織結構
//                echo '<pre>';
//                var_dump($sn);
//                echo '</pre>';
                $identifierNewItem = 'SCO_' . $sysSession->course_id . '_' . time() . sprintf("%06d", $sn);
//                echo '<pre>';
//                var_dump($identifierNewItem);
//                echo '</pre>';
//                
                // 資源結構
                $ret21 = $ctx2->xpath_eval("/manifest/resources/resource");
                if ($ret21) {
//                    echo '<pre>';
//                    var_dump("/manifest/resources/resource[@identifier='{$changeList[$identifierRawItem][0]}']");
//                    echo '</pre>';
                    $ret22 = $ctx2->xpath_eval("/manifest/resources/resource[@identifier='{$changeList[$identifierRawItem][0]}']");
                    if ($ret22) {
                        foreach($ret22->nodeset as $res22) {
                            // 目前的 identifier名稱 換成新的
                            $res22->set_attribute('identifier', $identifierNewItem);
                            // 產生新節點
                            $old_resources->append_child($res22->clone_node(true));
                            // 目前節點換回原 identifier名稱
                            $res22->set_attribute('identifier', $changeList[$identifierRawItem][0]);
                        }
                    }
                }
                $sn++;
            }
//            echo '<pre>';
//            var_dump('--------------------------------------------------------');
//            echo '</pre>';
            
            // 替換後做記號，以利知道哪些編號的流水號被換過了
            $replaced[$identifierRawItem][count($replaced[$identifierRawItem])] = 'replaced';
            if (in_array($identifierRawItem, array_keys($changeList))) {
                $res->set_attribute('identifierref', $identifierNewItem);
                $res->set_attribute('identifier', 'I_' . $identifierNewItem);
            }
        }
    }
    
    // 合併 <organization>
    $ret            = $ctx1->xpath_eval('/manifest/organizations/@default');
    $default_org_id = $ret->nodeset[0]->value;
    if (empty($default_org_id))
        $ret = $ctx1->xpath_eval("/manifest/organizations/organization[1]/item");
    else
        $ret = $ctx1->xpath_eval("/manifest/organizations/organization[@identifier='{$default_org_id}']/item");
			if ($ret)
			foreach($ret->nodeset as $res)
			{
				$old_organization->append_child($res->clone_node(true));
			}

		savePath($sysConn->qstr($oldpath->dump_mem()));
		return $organizationCount;
		//} //custom by lubo
	}
	function co_content($imsmanifest){
		
		$newpath = domxml_open_mem(str_replace('xml:base','xml_base',$imsmanifest));
				
		if ($newpath == false) {
		    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 4, 'auto', $_SERVER['PHP_SELF'], '錯誤！imsmanifest.xml無法辨識！');
			die($MSG['error2'][$sysSession->lang]);
		}
    
    $ctx1                  = xpath_new_context($newpath);
    $ret                   = $ctx1->xpath_eval("/manifest/resources/resource");
		// 取得該份 xml 是否有使用資源庫
		$subjectContentMapping = array();
		if ($ret){
			foreach($ret->nodeset as $res)
			{
				$base = $res->get_attribute('xml_base');
				$href = $res->get_attribute('href');
				$rid  = $res->get_attribute('identifier');
				preg_match('/\/base\/\d{5}\/content\/(\d{6})/', $base, $regs);
				if(strlen($regs[1])==6){
					if (!in_array($regs[1], $subjectContentMapping)) {					
						$subjectContentMapping[]=$regs[1];
                }
            }
        }
    }
    
    if ($subjectContentMapping) {
        foreach ($subjectContentMapping as $sub_key => $sub_value) {
            $lcms_cotent = dbGetStMr('WM_content_relation', 'filepath,learning_path', "content_id={$sub_value}");
            if ($lcms_cotent)
                while (!$lcms_cotent->EOF) {
                    $filepath      = $lcms_cotent->fields['filepath'];
					$learning_path = $lcms_cotent->fields['learning_path'];
						
					if ($ret){
						foreach($ret->nodeset as $res)
						{
							$base = $res->get_attribute('xml_base');
							$href = $res->get_attribute('href');
							$rid  = $res->get_attribute('identifier');
							preg_match('/\/base\/\d{5}\/content\/(\d{6})/', $base, $regs);
							if(strlen($regs[1])==6){		
								if($filepath == $base.$href){
                                    $res->set_attribute('xml_base', '');
                                    $res->set_attribute('href', $learning_path);
                                }
                            }
                        }
                    }
                    $lcms_cotent->MoveNext();
                }
        }
    }
    $newpath = str_replace('xml_base', 'xml:base', $newpath->dump_mem());
    return $newpath;
}