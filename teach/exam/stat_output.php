<?php
    /**
     * 教材閱讀記錄詳細列表
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
     * @copyright   2000-2005 SunNet Tech. INC.
     * @version     CVS: $Id: stat_output.php,v 1.2 2010/04/13 07:21:08 small Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2005-09-23
     */

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/archive_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	if ($_POST['op'] != 'download') {
		define('QTI_STAT_EXPORT', true);
		if (!preg_match('/^[0-9A-Z_]+$/i', $_POST['lists'])) {	// 檢查 lists
			wmSysLog($sysSession->cur_func, $course_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], 'Fake lists!');
			die('<script>alert("Fake lists.");</script>');
		}
		ob_start();
		if ($_POST['detail']) {
			include_once(sysDocumentRoot . '/teach/exam/exam_statistics_result_detail_out.php');
		} else {
			include_once(sysDocumentRoot . '/teach/exam/exam_statistics_result_out.php');
		}
		$_POST['content'] = ob_get_contents();
//		$_POST['content'] = preg_replace('/<\/?form[^>]*>/i', '', $_POST['content']);
		ob_end_clean();
	}

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	if ($topDir == 'academic')
	{
		$save_dir = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09u/',
		  					 $sysSession->school_id,
		  					 QTI_which,
		  					 addslashes($_POST['content']));
	}
	else
	{
		$save_dir = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/',
		  					 $sysSession->school_id,
		  					 $sysSession->course_id,
		  					 QTI_which,
		  					 addslashes($_POST['content']));
	}

	function decodeHtmlSpecialChar($str) {
		$content = str_replace(
			array('&lt;', '&gt;', '&amp;'),
			array('<', '>', '&'),
			$str
		);
		return $content;
	}

	function tagStripEncode($str) {
		$txt = strip_tags($str);
		$txt = preg_replace(
			array('!<(\w+)\b[^>]*>!i'),
			array('<\1>'),
			$txt
		);
		return $txt;
//		return htmlspecialchars($txt);
	}

	function resStripTag($str, $kind) {
		$se = array(
			'&amp;ldquo;', '&amp;rdquo;', '&amp;hellip;',
			'&amp;quot;',
			'&amp;nbsp;', '&lt;br&gt;',
//			'&lt;a&gt;', '&lt;/a&gt;', '&lt;span&gt;', '&lt;/span&gt;',
//			'&lt;p&gt;', '&lt;/p&gt;', '&lt;div&gt;', '&lt;/div&gt;'
		);
		$rp = '';
		if ($kind == 'xml') {
			$rp = array(
				'“', '”', '…',
				'"',
				' ', "\n",
				'', '', '', '',
				"\n"
			);
		} else if ($kind == 'csv') {
			$rp = array(
				'“', '”', '…',
				'"',
				' ', ' ',
				'', '', '', '',
				' '
			);
		}

		$txt = str_replace($se, $rp, $str);
		if ($kind == 'csv') {
			$txt = str_replace('&amp;', '&', $txt);
			$txt = @html_entity_decode($txt, ENT_QUOTES, 'UTF-8');
		}
		return $txt;
	}

	function html2xml($htm, $root)
	{
        $htm = preg_replace_callback(
            '!<(input|img|br)\b([^>]*)>!i',
            function ($matches) {
                return "<" .strtolower($matches[1]) . "$matches[2]/>";
            }, $htm);
	    $xml = '<?xml version="1.0" encoding="UTF-8"?>' .
	            (empty($root) ? '' : "<{$root}>") .
				  preg_replace(array('!<img\b[^>]*>!i',
				  					 // '!</?[A-Z]+!e',                	// 把 tag名稱換為小寫
				  					 // '!<(input|img|br)\b([^>]*)>!ie',   // 把單tag 加上尾斜線
                                     '!<t(r|h|able)\b[^>]*>!i',
									 '!<td\b[^>]*((col|row)span=("[^"]*"|[^ ]+))[^>]*>!i',
									 '!<td class[^>]*>!',
				  					 '!&nbsp;!i',
				  					 '!\s+<!',
				  					 '!>\s+!'
									),
							   array('',
							   		 // 'strtolower("$0");',
							   		 // '"<" . strtolower("$1") . "$2/>";',
									 '<t\1>',
									 '<td \1>',
									 '<td>',
							   		 ' ',
							   		 '<',
							   		 '>'
									),
							   $htm) .
				(empty($root) ? '' : "</{$root}>");
        return str_ireplace('<br //>', '<br />', $xml);
	}
	
	function mailEncFrom($from='', $email='', $charset='utf-8') {
		$email = trim($email);
		$from  = trim($from);
		if (empty($email)) return false;
		if (empty($from)) return $email;

		$cset = strtolower($charset);
		$str  = '=?' . $cset . '?B?' . base64_encode($from) . '?= <' . $email . '>';
		return $str;
	}

	$d = array(array('text/css', '/theme/default/'.$sysSession->env.'/wm.css'),
               array('image/gif', '/theme/default/'.$sysSession->env.'/title_on_01.gif'),
               array('image/gif', '/theme/default/'.$sysSession->env.'/title_on_02.gif'),
               array('image/gif', '/theme/default/'.$sysSession->env.'/title_on_03.gif'),
               array('image/gif', '/theme/default/learn/bar-p.gif'),
               array('image/gif', '/theme/default/learn/bar-p-1.gif'));

	// 處理其它圖檔 Begin
	// if (preg_match_all('/(<img [^>]*\bsrc=")([^"]*)("[^>]*>)/isU', stripslashes($_POST['content']), $matches))
	if (preg_match_all('/(<img [^>]*\bsrc=")([^"]*)("[^>]*>)/isU', $_POST['content'], $matches))
	{
		foreach($matches[2] as $src)
		{
			if (strpos(realpath(sysDocumentRoot . $src), sysDocumentRoot . '/base/') === 0)
				$d[] = array('image/gif', $src);
		}
	}
	// 處理其它圖檔 End

    if ($_POST['op'] == 'mail')
    {
        if (!preg_match(sysMailsRule, $_POST['email']))
        {
            die('<script>alert("' . $MSG['Incorrect email format.'][$sysSession->lang] . '");</script>');
		}

		include_once(sysDocumentRoot . '/lib/Emogrifier.php');
		require_once(sysDocumentRoot . '/lib/mime_mail.php');

		$serverHost  = (strpos('https://', strtolower($_SERVER['SCRIPT_URI'])) === 0) ? 'https://' : 'http://';
		$serverHost .= $_SERVER['HTTP_HOST'] . '/';
		$body = <<< EOB
<HTML><HEAD><TITLE></TITLE>
<META http-equiv=Content-Type content="text/html; charset=UTF-8">
<META http-equiv=Content-Language content=zh-tw>
</HEAD>
<BODY>
<CENTER>
<TABLE id=ListTable cellSpacing=0 cellPadding=0 border=0>
{$_POST['content']}
</TABLE>
</CENTER>
</BODY>
</HTML>
EOB;
		echo $body;

		$css = file_get_contents(sysDocumentRoot . "/theme/default/{$sysSession->env}/wm.css");
		$css = preg_replace('/font-family:[^;]+;/i', '', $css);
		$body = str_replace('onselectstart="return false;"', '', $body);

		try {
			$emogrifier = new Emogrifier();
			$emogrifier->setHTML($body);
			$emogrifier->setCSS($css);
			$body = $emogrifier->emogrify();
		} catch(Exception $ex) {
			print_r($ex);
		}

		$subject = $MSG['exam_statistics'][$sysSession->lang] . ' - ' . stripslashes($_POST['title']);
            
                //寄信通知
                $mail            = new mime_mail;
                
                // *46172 急單：中原 - 無法寄送問卷管理結果 Begin
                list($school_mail) = dbGetStSr('WM_school', 'school_mail', "school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);
                $mail->from      = $school_mail;
                
                $mail->to        = htmlspecialchars($_POST['email']);
                $mail->charset   = 'utf-8';
                $mail->body_type = 'text/html';
                $mail->subject   = '=?utf-8?B?' . base64_encode($subject) . '?=';
                $mail->reply     = mailEncFrom($sysSession->realname, $sysSession->email, 'utf-8');
                foreach ($d as $a) {
                    $body = str_replace($a[1], $serverHost . $a[1], $body);
                }
                $mail->body = $body;
                if (!$mail->send()) {
                    die('<script>alert("' . $MSG['mail sent success.'][$sysSession->lang] . '");</script>');
                } else {
                    die('<script>alert("' . $MSG['mail sent failure.'][$sysSession->lang] . '");</script>');
                }
                // *46172 急單：中原 - 無法寄送問卷管理結果 End
	}
	elseif ($_POST['op'] == 'export')
	{
	    // $htm = stripslashes($_POST['content']);
	    $htm = $_POST['content'];
	    $xmlstr = html2xml($htm, $_POST['detail'] ? 'div' : '');
        $xmlstr = ClearWordHtml($xmlstr);
        $xmlstr = str_replace('&','&amp;',$xmlstr);
        if (!@$dom = domxml_open_mem($xmlstr)) die('<script>alert("error");</script>');

		$xpath = $dom->xpath_new_context();
		$ret = $xpath->xpath_eval('//attribute::*');
		if (is_array($ret->nodeset)) foreach($ret->nodeset as $attribute)
		{
		    if ($attribute->name == 'colspan' || $attribute->name == 'rowspan') continue;
		    $p = $attribute->parent_node();
			$p->remove_attribute($attribute->name);
		}

		if ($_POST['detail'])
		{
		    $xml = $dom->dump_mem(true);
		    $xml = str_replace(',', '@@###@@', $xml); // 將欲輸出的逗點轉換
			$xmldoc = domxml_open_mem($xml);
			$xsldoc = domxml_xslt_stylesheet(preg_replace(array('/>\s+</', '/\s+</', '/>\s+/'),
														  array('><', '<', '>'),
														  file_get_contents('../../teach/exam/stat2_xml2csv.xsl')
														 )
											);
		}
		else
		{
			$ret = $xpath->xpath_eval('//form/table');
			$xml = $dom->dump_node($ret->nodeset[0], true);
			$xml = str_replace(',', '@@###@@', $xml); // 將欲輸出的逗點轉換
			$xmldoc = domxml_open_mem($xml);
			$xsldoc = domxml_xslt_stylesheet(preg_replace(array('/>\s+</', '/\s+</', '/>\s+/'),
														  array('><', '<', '>'),
														  file_get_contents('../../teach/exam/stat1_xml2csv.xsl')
														 )
											);
		}
		$xml = resStripTag($xml, 'xml');

		$result = $xsldoc->process($xmldoc);
		$cvs = preg_replace('/[\r\n]{2}/', "\n", strip_tags($result->dump_mem()));
		if (preg_match_all('/%COLSPAN(\d+)%/', $cvs, $matches))
		{
			$replace = array();
			for($i = 0; $i < count($matches[1]); $i++)
				$replace[] = str_repeat(',', $matches[1][$i] - 1);
			$cvs = str_replace($matches[0], $replace, $cvs);
		}
		$cvs = resStripTag($cvs, 'csv');
                
            // 序號、待選項目右邊欄位應該是空白，避免誤導    
            $cvs = str_replace($MSG['serial_no'][$sysSession->lang] . ',0', $MSG['serial_no'][$sysSession->lang] . ',', $cvs);
            $cvs = str_replace($MSG['candidate_item'][$sysSession->lang] . ',0', $MSG['candidate_item'][$sysSession->lang] . ',', $cvs);
            
            // 轉html實體
            $cvs = html_entity_decode($cvs);
            
	    $cvs = str_replace(',', chr(9), $cvs);
	    $cvs = str_replace('@@###@@', ',', $cvs); // 將欲輸出的逗點轉換回來
    
	    $csv = chr(255) . chr(254) . mb_convert_encoding($cvs, 'UTF-16LE', 'UTF-8');

	    $download_name = preg_replace(array('!\.\./!U', sprintf('!%s!U', preg_quote(DIRECTORY_SEPARATOR))),
									  array('', ''),
									  stripslashes($_POST['download_name']));
		$fname = $download_name ? $download_name : ($course_id . '.zip');

		if (substr($fname,-4) !== '.zip') {
                    $fname = $fname . '.zip';
		}

		$export_obj = new ZipArchive_php4($fname);
		if (in_array('csv', $_POST['export_kinds'])) $export_obj->add_string($csv, $course_id . '.csv');
		if (in_array('xml', $_POST['export_kinds'])) $export_obj->add_string($xml, $course_id . '.xml');
		if (in_array('htm', $_POST['export_kinds']))
		{
			$content = $htm;
			$tmp_path = sysTempPath . DIRECTORY_SEPARATOR . uniqid('stat_');
			exec("mkdir {$tmp_path} {$tmp_path}/resources");

			foreach($d as $v)
			{
				if($sysSession->env=='academic')
					$v[1] = str_replace('teach','academic',$v[1]);
				$content = str_replace($v[1], 'resources/' . basename($v[1]), $content);
				exec('cp -R ' . sysDocumentRoot . "$v[1] {$tmp_path}/resources/");
			}

			$export_obj->add_dir($tmp_path . '/resources');
			@exec("rm -rf {$tmp_path}");
			$content = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
				        <link rel="stylesheet" type="text/css" href="resources/wm.css" >' . $content;
//			$content = decodeHtmlSpecialChar($content);
			$export_obj->add_string($content, $course_id . '.htm');
		}
		if (in_array('mht', $_POST['export_kinds']))
		{
			$content = $htm;
			include_once(sysDocumentRoot . '/lib/mht.lib.php');
			$objMHT = new MhtFileMaker();
			foreach($d as $v)
			{
				if($sysSession->env=='academic')
					$v[1] = str_replace('teach','academic',$v[1]);
				$content = str_replace($v[1], basename($v[1]), $content);
			}
			$content = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
			        <link rel="stylesheet" type="text/css" href="wm.css" >' . $content;
//			$content = decodeHtmlSpecialChar($content);
			$objMHT->AddContents("{$course_id}.mht", "text/html", $content);
			foreach($d as $v)
				$objMHT->AddFile(sysDocumentRoot . $v[1]);

			// 處理圖檔 End
			$export_obj->add_string($objMHT->GetFile(), $course_id . '.mht');
		}

        while (@ob_end_clean());
		header('Content-Disposition: attachment; filename="' . $fname . '"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Type: application/zip; name="' . $fname . '"');
		$export_obj->readfile();
		$export_obj->delete();
	}
	elseif ($_POST['op'] == 'download')
	{
	    if (!ereg('^[0-9]+$', $_POST['content']))
        {
            die('<script>alert("incorrect questionnaire_id.");</script>');
		}

        $zip = exec("sh -c 'PATH=/usr/bin:/usr/sbin:/sbin:/bin:/usr/local/bin which zip'");
        if (!preg_match('!^(/\w+)+$!', $zip)) die('Can not found zip');

        while (@ob_end_clean());
		header('Content-Disposition: attachment; filename="quest_attach-' . sprintf('%09u', $_POST['content']) . '.zip"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Type: application/zip; name="quest_attach-' . sprintf('%09u', $_POST['content']) . '.zip"');
        if (is_dir($save_dir)) {
            passthru("cd '$save_dir' && $zip -r -q -7 - *");
        }
	}
	else
	    die('<script>alert("' . $MSG['error operating.'][$sysSession->lang] . '");</script>');
?>
