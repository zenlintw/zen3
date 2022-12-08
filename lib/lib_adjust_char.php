<?php
	/**
	 * 偵測檔名是何編碼的library
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Wing <wing@sun.net.tw>
	 * @copyright   2000-2005 SunNet Tech. INC.
	 * @version     CVS: $Id: lib_adjust_char.php,v 1.1 2010/02/24 02:39:33 saly Exp $
	 * @link       
	 * @since       2006-02-17
	 **/

	$GLOBALS['SERVER_ENCODINGS'] = array('Big5'   => array('Big5', 'BIG5-HKSCS', 'GB2312', 'GBK'),
                                         'GB2312' => array('GB2312', 'GBK', 'Big5', 'BIG5-HKSCS'),
                                         'EUC-JP' => array('SHIFT_JIS', 'GB2312', 'GBK', 'Big5', 'BIG5-HKSCS')
                                        );

	/**
	 * 偵測檔名是何編碼
	 * @param string $namd : 課程編號
	 * @return string true,false;
	 */
	function detect_chars($name)
	{
		global $sysSession;

		if (preg_match('/^[\x01-\x80]*$/', $name))
			return 'en';
		elseif (isset($GLOBALS['SERVER_ENCODINGS'][$sysSession->lang]))
		    foreach($GLOBALS['SERVER_ENCODINGS'][$sysSession->lang] as $charset)
				if (($w = iconv($charset, 'UTF-8', $name)) !== false) return $charset;

		return false;
	}

	/**
	 * 調整 locale 字串為正確的 UTF8 字串
	 * @param  string $word : 偵測字串
	 * @return string $word : 編碼字串
	 */
	function adjust_char($word)
	{
        //不需要轉碼了, 上傳檔案一律改為UTF-8, 實體檔案存放也是UTF-8編碼
        $word = mb_convert_encoding($word, 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win');
		return $word;
	}
	
	/**
	 * 偵測檔名是何編碼
	 *
	 * @param   string  $name   檔案名稱
	 * @return  string          字元集
	 *          bool(false)     無法偵測
	 */
	function un_detect_chars($name)
	{
		global $sysSession;

		if (preg_match('/^[\x01-\x80]*$/', $name))
			return 'en';
		elseif (isset($GLOBALS['SERVER_ENCODINGS'][$sysSession->lang]))
		    foreach($GLOBALS['SERVER_ENCODINGS'][$sysSession->lang] as $charset)
				if (($w = iconv('UTF-8', $charset, $name)) !== false) return $charset;

		return false;
	}

	/**
	 * 調整 UTF8 字串為正確的 locale 字串
	 *
	 * @param   string  $word   欲調整的字串
	 * @return  string          調整後的字串
	 */
	function un_adjust_char($word)
	{
        /*
        $char = detect_chars($word);
		switch($char)
		{
			case 'en':
				return $word;
				break;
			case 'big5':
			default:
				return iconv($char, 'UTF-8', $word);
				break;
		}
		*/
        $char = strtolower(detect_chars($word));
        if($char=='gb2312') //簡中
			return mb_convert_encoding($word, 'GBK', 'UTF-8');
        else if($char != 'en')  //繁中或日文其它
            return mb_convert_encoding($word, 'Big5', 'UTF-8');
        else
            return $word;
	}

	/**
	 * 檢查語系編碼
	 */
	function language_adjust($dir, $url)
	{
		global $sysSession, $language;

		if (file_exists($dir . $url))
		{
			$language = 'UTF-8';
			return $url;
		}
		elseif (isset($GLOBALS['SERVER_ENCODINGS'][$sysSession->lang]))
		    foreach($GLOBALS['SERVER_ENCODINGS'][$sysSession->lang] as $charset)
				if (($adjs = iconv('UTF-8', $charset, $url)) !== false && file_exists($dir . $adjs))
				{
					$language = $charset;
					return $adjs;
				}
		return iconv('UTF-8', 'BIG5', $url);
		// return false;
	}
	

    /**
     * 判斷字串是不是 utf8 編碼
     * @param string $str : 字串
     * @return boolen
     */
    function isUTF8($str) {
        if($str === mb_convert_encoding(mb_convert_encoding($str, "UTF-32", "UTF-8"), "UTF-8", "UTF-32"))
            return true;
        else
            return false;
    }
	
	function removenonprintable($strin){
			$strout = preg_replace('/[\x00-\x1F]/', '', $strin);
		return $strout;
	}
?>
