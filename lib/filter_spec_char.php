<?php
	/**
	 * 將字串的特殊字元移除
	 * @author  Amm Lee
	 * @version $Id: filter_spec_char.php,v 1.1 2010/02/24 02:39:33 saly Exp $:
	 * @copyright 2003 SUNNET
	 **/

	/*
	 * 將字串的特殊字元移除
	 * Filter_Spec_char
	 * para:
     * str => 字串
	 */
	function Filter_Spec_char($str, $type='title', $encoding='utf-8')
	{
	    mb_regex_encoding($encoding);
	    switch($type)
	    {
	        case 'title':	// 去掉控制字元、!#$%:;?\
	        case 'caption':
	            return mb_ereg_replace('[\x01-\x1F\x22\x27\x3A\x3B\x5C\x7B\x7D]', '', $str);
			case 'username':// 去掉不是數字、英文字母、底線、減號
			    return preg_replace('/[^\w.-]/', '', $str);
			case 'realname':// 去掉控制字元、標點符號(只允許 -._ 這三個)
			    return mb_ereg_replace('[\x01-\x1F\x21-\x2C\x2F\x3A-\x40\x5B-\x5E\x60\x7B-\x7F]', '', $str);
			case 'filename':// 去掉控制字元、"%*/:<>?\| 這 10 個不允許做檔名的字元
			    return mb_ereg_replace('[\x01-\x1F\x22\x25\x2A\x2F\x3A\x3C\x3E\x3F\x5C\x7C]', '', $str);
			case 'float':   // 去掉不是數字、正負號、小數點
			case 'score':
			    return preg_replace('/[^0-9.+-]/', '', $str);
			case 'int':     // 去掉不是數字、正負號
			case 'integer':
			case 'times':
			    return preg_replace('/[^0-9+-]/', '', $str);
			case 'no_punct':// 去掉控制字元、所有標點符號
			case 'search':
			    return mb_ereg_replace('[\x01-\x1F\x21-\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]', '', $str);
			default:
			    return $str;
		}
	}
	
	/**
	 * 去除不必要的 script 碼
	 *
	 * @param   string      $origin     原始內容
	 * @return  string                  去除後的內容
	 */
	function strip_scr($origin){
		static 	$pattern = array('/<\?(php)?(\s*|=).*\?>/isU',
		                         '/<frameset\s[^>]*>.*<\/frameset[^>]*>/isU',
		                         '/<frame\s[^>]*>.*<\/frame[^>]*>/isU',
		                         '/<iframe\s(?!.*youtube.com)[^>]*>.*<\/iframe>/isU',
		                         '/^.*<body\s*[^>]*>/isU',
		                         '/<\/body>.*$/isU',
		                         '/<object\s*[^>]*>.*<\/object[^>]*>/isU',
		                         '/<applet\s*[^>]*>.*<\/applet[^>]*>/isU',
		                         '/<form\s*[^>]*>.*<\/form[^>]*>/isU',
		                         '/<input\s*[^>]*>/isU',
		                         '/<textarea\s*[^>]*>.*<\/textarea[^>]*>/isU',
		                         '/<select\s*[^>]*>.*<\/select[^>]*>/isU',
		                         '/<script\s*[^>]*>.*<\/script[^>]*>/isU',
		                         '/<link\s*[^>]*>/isU',
		                         '/<!--.*-->/sU',
		                         '/\bon\w+\s*=\s*(".*[^\\]"|\'.*[^\\]\'|[^\s]*)/isU',
		                         '/\w+\s*=\s*["\']?\s*(javascript|vbscript):[^\'">]*["\']?/isU',
		                         '/:\s*expression\s*\(/isU'
		                         ),
		        $replace = array('',	 // 移除 PHP 碼
		                         '',	 // 移除 <frameset></frameset>
		                         '',	 // 移除 <frame></frame>
		                         '',	 // 移除 <iframe></iframe>
		                         '',	 // 移除從 <html> 到 <body>
		                         '',	 // 移除從 </body> 到 </html>
		                         '',	 // 移除 <object></object>
		                         '',	 // 移除 <applet></applet
		                         '',	 // 移除 <form></form>
		                         '',	 // 移除 <input .... >
		                         '',	 // 移除 <textarea></textarea>
		                         '',	 // 移除 <select></select>
		                         '',	 // 移除 <script></script>
		                         '',	 // 移除 <link>
		                         '',	 // 移除 <!-- -->
		                         '',	 // 移除 on[事件]=""
		                         '',	 // 移除 href=javascript:
		                         ': expressi0n(' // 取消 css 自定標籤
		                         );

		$curr = $origin;
		do
		{
		    $prev = $curr;
		    $curr = preg_replace($pattern, $replace, $curr);
		}
		while ($prev != $curr);
		return $curr;
	}

	/**
	 * 去除 FCKeditor 會自動加入的空白行
	 *
	 * @param   string      $str    原始內容
	 * @return  string              去除後內容
	 */
	function trimHtml($str)
	{
		do {
            $prev = trim($str);
            $str = preg_replace(array('!^<p>(<br />(\s|&nbsp;)*)*</p>\s*!iU',
									  '!(\s*<p>(<br />(\s|&nbsp;)*)*</p>|(\s*<br />)+)$!iU',
									  '!\s*<p>&nbsp;</p>!i'),
								array('', '', ''),
								$prev);
		}
		while ($prev != $str);
		return $str;
	}
	
	function ClearWordHtml($content,$allowtags='') {
			
			mb_regex_encoding('UTF-8');
			//replace MS special characters first
			$search = array('/&lsquo;/u', '/&rsquo;/u', '/&ldquo;/u', '/&rdquo;/u', '/&ndash;/u','/&mdash;/u','/&hellip;/u');
			$replace = array('\'', '\'', '"', '"', '-','-','...');
			$content = preg_replace($search, $replace, $content);
			return $content;
	}
?>
