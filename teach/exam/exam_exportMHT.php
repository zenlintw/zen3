<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/mht.lib.php');
#======= function ========
	function getImageArray($str)
	{
		$regs = array();
		$tok = explode('<IMG',$str);
		for($i=0, $size=count($tok); $i<$size; $i++)
		{
			if (($pos1 = strpos($tok[$i],'src="')) === false) continue;
			$pos1 += 5;
			$pos2 = strpos($tok[$i],'"',$pos1);
			$tmps = substr($tok[$i],$pos1, $pos2-$pos1);
			if (ereg("/base/",$tmps)) $regs[] = $tmps;		//只替換題目中的圖檔
		}
		return $regs;
	}
#====== main =============
	$_POST['table_html'] = str_replace(array('\"', 'http://'.$_SERVER['SERVER_NAME'], '/theme/default/teach/', '/theme/default/learn/',"\'"), 
	                                   array('"', '', '', '',"'"), 
	                                   $_POST['table_html']);
	                                   

	if (preg_match_all('/(<img [^>]*\bsrc=")([^"]*)("[^>]*>)/isU', $_POST['table_html'], $matches))
	{
		foreach($matches[2] as $src)
		{
			if (strpos($src,'title_on_03.gif')!==false) {
			    $_POST['table_html'] = str_replace($src, 'title_on_03.gif', $_POST['table_html']);	
			}
		    if (strpos($src,'title_on_01.gif')!==false) {
			    $_POST['table_html'] = str_replace($src, 'title_on_01.gif', $_POST['table_html']);	
			}
			
			if (strpos(sysDocumentRoot . $src, sysDocumentRoot . '/base/') === 0)
				$d[] = array('image/gif', $src);
		}
	}
	
	if (preg_match_all('/[(]{1}(.*)[)]{1}/isU', $_POST['table_html'], $matches))
	{
		
		if(strpos($matches[0][0],'title_on_02.gif')!=false) {
		    $_POST['table_html'] = str_replace($matches[0][0], '(title_on_02.gif)', $_POST['table_html']);	
		}
		
	}
	
	foreach($d as $v) {
	    $_POST['table_html'] = str_replace($v[1], basename($v[1]), $_POST['table_html']);
	}

		
	$cont = '<html>
			 <head>
			 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
			 <meta http-equiv="Content-Language" content="zh-tw" >
			 <title></title>
			 <link rel="stylesheet" type="text/css" href="wm.css">
			 </head>
			 <body>' . $_POST['table_html'] . 
			 '</body></html>';

	$objMHT = new MhtFileMaker();
	$objMHT->SetFrom("WM3 TNA");
	$objMHT->SetSubject("Export MHT");
	$objMHT->SetDate();
	$objMHT->SetBoundary();

//加上圖檔及CSS
	$objMHT->AddContents("paper.html", "text/html", $cont);
	$objMHT->AddFile(sysDocumentRoot .'/theme/default/teach/title_on_01.gif');
	$objMHT->AddFile(sysDocumentRoot .'/theme/default/teach/title_on_02.gif');
	$objMHT->AddFile(sysDocumentRoot .'/theme/default/teach/title_on_03.gif');
	$objMHT->AddFile(sysDocumentRoot .'/theme/default/teach/right.gif');
	$objMHT->AddFile(sysDocumentRoot .'/theme/default/teach/wrong.gif');
	$objMHT->AddFile(sysDocumentRoot .'/theme/default/teach/file.gif');
	
	foreach($d as $v) {
	    $objMHT->AddFile(sysDocumentRoot . $v[1]);
	}
	
	
	$objMHT->AddFile(sysDocumentRoot .'/theme/default/teach/wm.css');
	
	$contents = $objMHT->GetFile();
	
	header('Content-Disposition: attachment; filename="export.mht"');
	header('Content-Type: application/octet-stream');
	echo $contents;
?>
