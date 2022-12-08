<?php
   /**
    * 檔案說明
    *	產生多語系下的附檔連結
    * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
    *
    * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
    * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
    * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
    *
    * @package     WM3
    * @author      wiseguy liang <wiseguy.idv.tw>
    * @copyright   2000-2005 SunNet Tech. INC.
    * @version     CVS: $Id: attach_link.php,v 1.1 2010/02/24 02:39:32 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2006-04-17
    *
    */

	require_once (dirname(__FILE__) . '/lib_adjust_char.php');
        require_once(sysDocumentRoot . '/mooc/common/common.php');

    function genFileLink($save_uri, $entry)
    {
        $c = detect_chars($entry);
        // 轉換後長度不一樣，則不轉換
        if (htmlspecialchars(iconv('BIG5', 'UTF-8', $entry))=='' || mb_strlen($entry, 'UTF-8') !== iconv('BIG5', 'UTF-8', $entry)) {
        	$showname = $entry;
        } else {
        	$showname = htmlspecialchars(iconv('BIG5', 'UTF-8', $entry));
        }
		
		if (file_exists(sysDocumentRoot.$save_uri.$entry)) {
			$file_date=  date ("Y/m/d H:i:s -", filemtime(sysDocumentRoot.$save_uri.$entry));
		}
    
                $filesize = @filesize(sysDocumentRoot . $save_uri . $entry);
                if ($filesize === 0) {
                    global $MSG, $sysSession;
                    $filesizeNote  = '(' . $MSG['file_content_blank'][$sysSession->lang] . ')';
                    $filesizeColor = 'red';
                } else {
                    $filesizeNote  = '';
                    $filesizeColor = 'black';
                }
		return sprintf('%s <a href="%s%s" target="_blank" class="cssAnchor">%s</a> <span class="font01" style="color: %s;">%s (%s)</span><br />', $file_date, $save_uri, rawurlencode($entry), $showname, $filesizeColor, $filesizeNote, 
    // htmlspecialchars($c == 'Big5' || $c == 'GB2312' ? iconv($c, 'UTF-8', $entry) : iconv('BIG5', 'UTF-8', $entry)),
        FileSizeConvert($filesize));
    }

    function genPureFileLink($save_uri, $entry)
    {
        $c = detect_chars($entry);
        
		return sprintf('<a href="%s%s" target="_blank">%s</a> <span>(%s <span>bytes</span>)</span>',
					   $save_uri, rawurlencode($entry),
						htmlspecialchars($entry),
					   number_format(@filesize(sysDocumentRoot . $save_uri . $entry), 0, '.', ',')
					  );
    }
