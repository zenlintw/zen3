<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/08/05                                                            *
	 *		work for  : content directory building (imsmanifest.xml editor)                   *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/teach/course/import_imsmanifest.lib.php');

	$sysSession->cur_func = '700600100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	header('Content-type: application/xml; charset=UTF-8');
	
	// 有指定某次學習路徑或者沒指定就直接選擇最新的學習路徑
	if ($_SERVER['argv'][0])
	{
		$_SERVER['argv'][0] = intval($_SERVER['argv'][0]);
		list($pathContent) = dbGetStSr('WM_term_path', 'content', "course_id={$sysSession->course_id} and serial={$_SERVER['argv'][0]}", ADODB_FETCH_NUM);
	}
	else
		list($pathContent) = dbGetStSr('WM_term_path', 'content', "course_id={$sysSession->course_id} order by serial desc", ADODB_FETCH_NUM);

	if (empty($pathContent))
	{
		$course_name = htmlspecialchars($sysSession->course_name);
		echo <<< EOB
<?xml version="1.0" encoding="UTF-8"?>
<manifest version="1.3"
       xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2"
       xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
       xsi:schemaLocation="http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd
                           http://www.imsglobal.org/xsd/imsmd_rootv1p2p1 imsmd_rootv1p2p1.xsd
                           http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd">
   <organizations default = "Course{$sysSession->course_id}">
      <organization identifier = "Course{$sysSession->course_id}">
         <title>{$course_name}</title>
      </organization>
   </organizations>
   <resources />
</manifest>
EOB;
	}
	else {
	    $pathContent = mb_convert_encoding($pathContent, 'UTF-8', 'UTF-8');
		$xmldoc = @domxml_open_mem(stripDefaultNamespace(convEncodingValue($pathContent)));
		$ctx1 = xpath_new_context($xmldoc);
		// 代換課程路徑的中的課程名稱為該門課的課程名稱 Start
		$ret = $ctx1->xpath_eval('manifest/organizations/@default');
		$default_org_id = $ret->nodeset[0]->value;
		if (empty($default_org_id))
			$ret = $ctx1->xpath_eval("/manifest/organizations/organization[1]/title");
		else
			$ret = $ctx1->xpath_eval("/manifest/organizations/organization[@identifier='{$default_org_id}']/title");
		
		if (count($ret->nodeset)>0) {
			$oldnode = $ret->nodeset[0];
			$doc = $oldnode->owner_document();
			$newnode = $doc->create_element('title');
			$newnode->append_child($doc->create_text_node($sysSession->course_name));
			$oldnode->replace_node($newnode);
		}
		else {
			if (empty($default_org_id))
				$ret = $ctx1->xpath_eval("/manifest/organizations/organization[1]");
			else
				$ret = $ctx1->xpath_eval("/manifest/organizations/organization[@identifier='{$default_org_id}']");
			$org = $ret->nodeset[0];
			$doc = $org->owner_document();
			$newnode = $doc->create_element('title');
			$newnode->append_child($doc->create_text_node($sysSession->course_name));
			$org = $ret->nodeset[0];
			$org->append_child($newnode);
		}
		// 代換課程路徑的中的課程名稱為該門課的課程名稱 End
		echo $xmldoc->dump_mem(true, 'UTF-8');
	}
?>
