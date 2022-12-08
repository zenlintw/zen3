<?php
	/**
	 * 【程式功能】
	 * 建立日期：2004/08/16
	 * @author  Wiseguy Liang
	 * @version $Id: cour_path_export.php,v 1.1 2010/02/24 02:40:23 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '700600300';
	$sysSession->restore();
	if (!aclVerifyPermission(700600300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	$fname = 'imsmanifest_' . $sysSession->course_id . '.xml';
	header('Content-Disposition: attachment; filename="' . $fname . '"');
	header('Content-Transfer-Encoding: binary');
	header('Content-Type: application/xml; name="' . $fname . '"');

	if ($_SERVER['argv'][0])
	{
		$_SERVER['argv'][0] = intval($_SERVER['argv'][0]);
		$content = dbGetOne('WM_term_path', 'content', 'course_id=' . $sysSession->course_id . ' and serial=' . $_SERVER['argv'][0]);
	}
	else
		$content = dbGetOne('WM_term_path', 'content', 'course_id=' . $sysSession->course_id . ' order by serial desc');
    
    // for Reload Editor MIS#042145
    $content = preg_replace("/<manifest .(.*?)>/", '<manifest xmlns="http://www.imsglobal.org/xsd/imscp_v1p1" xmlns:imsmd="http://ltsc.ieee.org/xsd/LOM" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_v1p3" xmlns:imsss="http://www.imsglobal.org/xsd/imsss" xmlns:adlseq="http://www.adlnet.org/xsd/adlseq_v1p3" xmlns:adlnav="http://www.adlnet.org/xsd/adlnav_v1p3" identifier="MANIFEST-6B62BE6554B760392AE13DE5DBAAA545" xsi:schemaLocation="http://www.imsglobal.org/xsd/imscp_v1p1 imscp_v1p1.xsd http://ltsc.ieee.org/xsd/LOM lom.xsd http://www.adlnet.org/xsd/adlcp_v1p3 adlcp_v1p3.xsd http://www.imsglobal.org/xsd/imsss imsss_v1p0.xsd http://www.adlnet.org/xsd/adlseq_v1p3 adlseq_v1p3.xsd http://www.adlnet.org/xsd/adlnav_v1p3 adlnav_v1p3.xsd">', $content);
    echo str_replace('<resource xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_v1p3" identifier=', '<resource xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_v1p3" identifier=', $content);
?>
