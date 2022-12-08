<?php
/***********************************************************************
Class:		Mht File Maker
Version:	1.1 beta
Date:		08/20/2004
Author:		Wudi <wudicgi@yahoo.de>
Description:	The class can make .mht file.
***********************************************************************/

function quoted_printable_encode_character($matches) {
   $character = end($matches);
   return sprintf('=%02x', ord($character));
}
if (!function_exists('quoted_printable_encode')) {
    function quoted_printable_encode($string) {
       // rule #2, #3 (leaves space and tab characters in tact)
       $string = preg_replace_callback(
         '/[^\x21-\x3C\x3E-\x7E\x09\x20]/',
         'quoted_printable_encode_character',
         $string
       );

       $newline = "=\r\n"; 			// '=' + CRLF(rule #4)

       // make sure the splitting of lines does not interfere with escaped characters
       // (chunk_split fails here)
       preg_match_all('/.{73}([^=]{0,3})/', $string, $match ); 		// Rule #1
       return implode( $newline, $match[0]);
    }
}

class MhtFileMaker{
    var $config = array();
    var $headers = array();
    var $headers_exists = array();
    var $files = array();
    var $boundary;
    var $dir_base;
    var $page_first;

    function MhtFile($config = array()){

    }

    function SetHeader($header){
        $this->headers[] = $header;
        $key = strtolower(substr($header, 0, strpos($header, ':')));
        $this->headers_exists[$key] = TRUE;
    }

    function SetFrom($from){
        $this->SetHeader("From: $from");
    }

    function SetSubject($subject){
        $this->SetHeader("Subject: $subject");
    }

    function SetDate($date = NULL, $istimestamp = FALSE){
    	if ($date == NULL) {
    	    $date = time();
    	}
        if ($istimestamp == TRUE) {
            $date = date('D, d M Y H:i:s O', $date);
        }
        $this->SetHeader("Date: $date");
    }

    function SetBoundary($boundary = NULL){
        if ($boundary == NULL) {
            $this->boundary = '--' . strtoupper(md5(mt_rand())) . '_MULTIPART_MIXED';
        } else {
            $this->boundary = $boundary;
        }
    }

    function SetBaseDir($dir){
        $this->dir_base = str_replace("\\", "/", realpath($dir));
    }

    function SetFirstPage($filename){
        $this->page_first = str_replace("\\", "/", realpath("{$this->dir_base}/$filename"));
    }

    function AutoAddFiles(){
        if (!isset($this->page_first)) {
            exit ('Not set the first page.');
        }
        $filepath = str_replace($this->dir_base, '', $this->page_first);
        $filepath = 'http://mhtfile' . $filepath;
        $this->AddFile($this->page_first, $filepath, NULL);
        $this->AddDir($this->dir_base);
    }

    function AddDir($dir){
        $handle_dir = opendir($dir);
        while (($filename = readdir($handle_dir)) !== FALSE) {
            if (($filename!='.') && ($filename!='..') && ("$dir/$filename"!=$this->page_first)) {
                if (is_dir("$dir/$filename")) {
                    $this->AddDir("$dir/$filename");
                } elseif (is_file("$dir/$filename")) {
                    $filepath = str_replace($this->dir_base, '', "$dir/$filename");
                    $filepath = 'http://mhtfile' . $filepath;
                    $this->AddFile("$dir/$filename", $filepath, NULL);
                }
            }
        }
        closedir($handle_dir);
    }

    function AddFile($filename, $filepath = NULL, $encoding = NULL){
        if ($filepath == NULL) {
            $filepath = basename($filename);
        }
        $mimetype = $this->GetMimeType($filename);
        if (!file_exists($filename)) return;
        $filecont = file_get_contents($filename);
        $this->AddContents($filepath, $mimetype, $filecont, $encoding);
    }

	function getSuffix($fname)
	{
		if (empty($fname)) return '';
		if (($pos1=strpos($fname,'.')) === false) return '';
		return substr($fname,$pos1+1);
	}
	
	
    function AddContents($filepath, $mimetype, $filecont, $encoding = NULL){
    	$img_suffix = array('JPG','JPEG','GIF','PNG');
        if ($encoding == NULL) {
        	
//        	$suffix = strtoupper($this->getSuffix(basename($filepath)));
//        	if (in_array($suffix,$img_suffix))
//        	{
        		$filecont = chunk_split(base64_encode($filecont), 76);
            	$encoding = 'base64';
/*        	}else{
   	            $filecont = quoted_printable_encode($filecont);
	            $encoding = 'quoted-printable';
        	}
        	*/
        }
        $this->files[] = array('filepath' => $filepath,
                               'mimetype' => $mimetype,
                               'filecont' => $filecont,
                               'encoding' => $encoding);
    }

    function CheckHeaders(){
        if (!array_key_exists('date', $this->headers_exists)) {
            $this->SetDate(NULL, TRUE);
        }
        if ($this->boundary == NULL) {
            $this->SetBoundary();
        }
    }

    function CheckFiles(){
        if (count($this->files) == 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function GetFile(){
        $this->CheckHeaders();
        if (!$this->CheckFiles()) {
            exit ('No file was added.');
        }
        $contents = implode("\r\n", $this->headers) .
                    "\r\n" .
                    "MIME-Version: 1.0\r\n" .
                    "Content-Type: multipart/related;\r\n" .
                    "\tboundary=\"{$this->boundary}\";\r\n" .
                    "\ttype=\"" . $this->files[0]['mimetype'] . "\"\r\n" .
                    "X-MimeOLE: Produced By Mht File Maker v1.0 beta\r\n" .
                    "\r\n" .
                    "This is a multi-part message in MIME format.\r\n" .
                    "\r\n";
        foreach ($this->files as $file) {
            $contents .= "--{$this->boundary}\r\n" .
                         "Content-Type: $file[mimetype];\r\n". 'charset="utf-8"'."\r\n" .
                         "Content-Transfer-Encoding: $file[encoding]\r\n" .
                         "Content-Location: $file[filepath]\r\n" .
                         "\r\n" .
                         $file['filecont'] .
                         "\r\n";
        }
        $contents .= "--{$this->boundary}--\r\n";
        return $contents;
    }


    function MakeFile($filename){
        $contents = $this->GetFile();
        echo $contents;
        $fp = fopen($filename, 'w');
        fwrite($fp, $contents);
        fclose($fp);
    }

    function GetMimeType($filename){
        $pathinfo = pathinfo($filename);
        switch ($pathinfo['extension']) {
            case 'htm' :
            case 'html': $mimetype = 'text/html'; break;
            case 'txt' :
            case 'cgi' :
            case 'php' : $mimetype = 'text/plain'; break;
            case 'css' : $mimetype = 'text/css'; break;
            case 'jpg' :
            case 'jpeg':
            case 'jpe' : $mimetype = 'image/jpeg'; break;
            case 'gif' : $mimetype = 'image/gif'; break;
            case 'png' : $mimetype = 'image/png'; break;
            default: $mimetype = 'application/octet-stream'; break;
        }
        return $mimetype;
    }
}
?>
