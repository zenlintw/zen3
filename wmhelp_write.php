<?php
	/**
	 * 線上說明 - 寫入
	 *
	 * 建立日期：2004/11/24
	 * @author  Jeff Wang
	 * @version $Id: wmhelp_write.php,v 1.1 2010/02/24 02:38:55 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_wmhelp.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	 
	
#======= Function ==========
	function isAdmin()
	{
		global $sysConn, $sysSession, $sysRoles;
		$sysConn->Execute('use ' . sysDBname);
		return (bool)$sysConn->GetOne("select count(*) from WM_manager where username = '{$sysSession->username}' and (school_id = {$sysSession->school_id} or level & {$sysRoles['root']})");
	}
#======= Main ==============
	if (!isAdmin())		//沒有管理者權限
	{
		die("Page Not Found!");
		exit;
	}

	if (empty($_POST['helpfile'])) die("request error!");
	$o_help = new wmhelp($sysSession->school_id);
	$o_help->setHelpFilename($_POST['helpfile']);
	$wmhelp_base = "/base/{$sysSession->school_id}/door/wmhelp/{$sysSession->lang}/";
	$content = str_replace($wmhelp_base, '', trim($_POST['content']));
	$o_help->writeHelpfile($content);
	if (!$o_help->isHelpfileExists()) die("fail to write help file");

#===== 開始呈現 HTML
	showXHTML_head_B("");
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E('');
	showXHTML_body_B(' onload="document.forms[0].submit();" ');
	showXHTML_form_B('method="get" action="wmhelp.php"', 'post1');
	showXHTML_input('hidden', 'url', $o_help->url, '', '');
	showXHTML_form_E('');
	showXHTML_body_E('');
?>
