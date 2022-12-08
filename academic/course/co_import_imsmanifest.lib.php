<?php
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
		return preg_replace('/\bxmlns\s*=\s*"[^"]*"/sU','', $str);
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
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], 'save path :' . $path);
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
		  if ($encoding != 'UTF-8'){
		  	$xmlstr = preg_replace("/{$ary[1]}/", 'UTF-8', $ary[0]);
		  	$rtn = preg_replace($reg, $xmlstr, $rtn);
		  	return mb_convert_encoding($rtn, 'UTF-8', $encoding);
			}
		}
		return mb_convert_encoding($rtn, 'UTF-8', 'UTF-8');
	}

	/**
	 * process the new imsmanifest.xml
	 */
	//function processNewImsmanifest($imsmanifest, $replace=false,$check_file=true)  //custom by lubo
	function processNewImsmanifest($imsmanifest, $src_school_id, $src_course_id, $tar_course_id)
	{
		global $sysConn, $sysSession, $MSG;
		
        $replace=false;
        $check_file=false;
		$newpath = @domxml_open_mem($imsmanifest);
		if ($newpath == false) {
		    return '';
		}
		$ctx1 = xpath_new_context($newpath);
        
        $sysConn->Execute(sprintf('use %s%s',sysDBprefix,$src_school_id ));
		$old = $sysConn->GetOne("select content from WM_term_path where course_id={$src_course_id} order by serial desc");
		if (empty($old) || $replace)
		{
			savePath($sysConn->qstr($newpath->dump_mem()));
			return;
		}

		$oldpath = @domxml_open_mem(stripDefaultNamespace($old));
		if ($oldpath == false){
		    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 4, 'auto', $_SERVER['PHP_SELF'], $MSG['error2'][$sysSession->lang]);
			die($MSG['error2'][$sysSession->lang]);
		}
		$ctx2 = xpath_new_context($oldpath);

		$ret = $ctx2->xpath_eval('/manifest/organizations/@default');
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
		if ($ret)
		foreach($ret->nodeset as $res)
		{
			if (in_array($res->get_attribute('identifier'), $old_resource_ids)) continue;
			$old_resources->append_child($res->clone_node(true));
		}

		// 合併 <organization>
		$ret = $ctx1->xpath_eval('/manifest/organizations/@default');
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
		return;
		//} //custom by lubo
	}

?>
