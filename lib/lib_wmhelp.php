<?php
	/**
	 * 處理 wm3 online help 的類別
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
		var $sid             = 0;	// 學校代號
		var $Directory       = '';	// 儲存online help的目錄，public
		var $AssetsDirectory = '';	// 儲存online help的目錄，public
		var $url;				    // 功能的url
		var $help_url;			    // 說明的url
		var $filename        = '';	// help filename
		var $filepath        = '';	// help filepath

		/**
		 * wmhelp()
		 *     建構式，啟始變數設定
		 **/

		function wmhelp($l_sid)
		{
			$this->sid = $l_sid;	//設定學校代號
			$this->initDirectory();	//啟始 online help 目錄
		}

		/**
		 * initDirectory()
		 *     設定online help的目錄，若目錄不存在，則建立之
		 **/

		 function initDirectory()
		 {
		 	global $_SERVER, $sysSession;

		 	//設定online help目錄
		 	$Dir = sysDocumentRoot . "/base/{$this->sid}/door/wmhelp/";
		 	$this->Directory = sysDocumentRoot . "/base/{$this->sid}/door/wmhelp/" . $sysSession->lang;
		 	$this->AssetsDirectory = $this->Directory . '/assets';

		 	//判斷是否存在，若不存在則新建online help目錄
		 	if (!file_exists($Dir))
		 	{
		 		if (!mkdir($Dir, 0777))
		 		{
		 			die('Fail to create directory for online help files.');
		 		}
		 	}

		 	//判斷是否存在，若不存在則新建online help目錄
		 	if (!file_exists($this->Directory))
		 	{
		 		if (!mkdir($this->Directory, 0777))
		 		{
		 			die('Fail to create directory for online help files.');
		 		}
		 	}

		 	//判斷是否存在，若不存在則新建online help夾檔目錄
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
		 *     由參數url,來設定help filename
		 * @param $url 所要讀取online help的url
		 * @return
	 	 *      false : 表示url有問題
	 	 *	    true  : 回傳HelpFilename
		 **/

		 function setHelpFilename($url)
		 {
		 	global $sysSession;

		 	//先判斷url的正確性
		 	if (!ereg('\.php', $url))	return false;

		 	$this->url = $url;

		 	//切割$url, 不需要參數
		 	$pos = strpos($url, '.php');
		 	$url = substr($url, 0, $pos+4);

		 	//將路徑的"/"字元轉換為"."
		 	if (substr($url,0,1) == '/') $url = substr($url, 1);
		 	$this->filename = str_replace('/', '.', $url) . '.htm';

		 	//設定help filepath
		 	$this->filepath = $this->Directory . '/' . $this->filename;

		 	//設定說明網頁的url
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
