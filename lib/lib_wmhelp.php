<?php
	/**
	 * �B�z wm3 online help �����O
	 *
	 * @author  $Author: saly $
	 * @version $Id: lib_wmhelp.php,v 1.1 2010/02/24 02:39:34 saly Exp $
	 * $State: Exp $
	 */

	function isHelpWriter()
	{
		global $sysConn, $sysSession, $sysRoles;

        chkSchoolId('WM_manager');
        $cm = $sysConn->GetOne("select count(*) from WM_manager where username = '{$sysSession->username}' and (school_id = {$sysSession->school_id} or level & {$sysRoles['root']})");
		return (bool)$cm;
	}

	class wmhelp
	{
		var $sid             = 0;	// �ǮեN��
		var $Directory       = '';	// �x�sonline help���ؿ��Apublic
		var $AssetsDirectory = '';	// �x�sonline help���ؿ��Apublic
		var $url;				    // �\�઺url
		var $help_url;			    // ������url
		var $filename        = '';	// help filename
		var $filepath        = '';	// help filepath

		/**
		 * wmhelp()
		 *     �غc���A�ҩl�ܼƳ]�w
		 **/

		function wmhelp($l_sid)
		{
			$this->sid = $l_sid;	//�]�w�ǮեN��
			$this->initDirectory();	//�ҩl online help �ؿ�
		}

		/**
		 * initDirectory()
		 *     �]�wonline help���ؿ��A�Y�ؿ����s�b�A�h�إߤ�
		 **/

		 function initDirectory()
		 {
		 	global $_SERVER, $sysSession;

		 	//�]�wonline help�ؿ�
		 	$Dir = sysDocumentRoot . "/base/{$this->sid}/door/wmhelp/";
		 	$this->Directory = sysDocumentRoot . "/base/{$this->sid}/door/wmhelp/" . $sysSession->lang;
		 	$this->AssetsDirectory = $this->Directory . '/assets';

		 	//�P�_�O�_�s�b�A�Y���s�b�h�s��online help�ؿ�
		 	if (!file_exists($Dir))
		 	{
		 		if (!mkdir($Dir, 0777))
		 		{
		 			die('Fail to create directory for online help files.');
		 		}
		 	}

		 	//�P�_�O�_�s�b�A�Y���s�b�h�s��online help�ؿ�
		 	if (!file_exists($this->Directory))
		 	{
		 		if (!mkdir($this->Directory, 0777))
		 		{
		 			die('Fail to create directory for online help files.');
		 		}
		 	}

		 	//�P�_�O�_�s�b�A�Y���s�b�h�s��online help���ɥؿ�
		 	if (!file_exists($this->AssetsDirectory))
		 	{
		 		if (!mkdir($this->AssetsDirectory, 0777))
		 		{
		 			die('Fail to create directory for online help Assets files.');
		 		}
		 	}
		 }

		 /**
		 * setHelpFilename()
		 *     �ѰѼ�url,�ӳ]�whelp filename
		 * @param $url �ҭnŪ��online help��url
		 * @return
	 	 *      false : ���url�����D
	 	 *	    true  : �^��HelpFilename
		 **/

		 function setHelpFilename($url)
		 {
		 	global $sysSession;

		 	//���P�_url�����T��
		 	if (!ereg('\.php', $url))	return false;

		 	$this->url = $url;

		 	//����$url, ���ݭn�Ѽ�
		 	$pos = strpos($url, '.php');
		 	$url = substr($url, 0, $pos+4);

		 	//�N���|��"/"�r���ഫ��"."
		 	if (substr($url,0,1) == '/') $url = substr($url, 1);
		 	$this->filename = str_replace('/', '.', $url) . '.htm';

		 	//�]�whelp filepath
		 	$this->filepath = $this->Directory . '/' . $this->filename;

		 	//�]�w����������url
		 	$this->help_url = "/base/{$this->sid}/door/wmhelp/{$sysSession->lang}/" . $this->filename;
		 }

		 function isHelpfileExists()
		 {
		 	return file_exists($this->filepath);
		 }

		 function writeHelpfile($str)
		 {
		 	$fp = fopen($this->filepath, "w+");
		 	$newHead = '<head>' .
		 	           '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >' .
		 	           '<meta http-equiv="Content-Language" content="zh-tw" >' .
		 	           '<title>online help</title>' .
		 	           '<head>';
            $content = ereg_replace('<head>.*</head>', $newHead, stripslashes($str));
		 	fputs($fp, $content);
		 	fclose($fp);
		 }
	}
?>
