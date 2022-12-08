<?
	/**
    * /辦公室/課程管理/課程簡介 library
    *
    * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
    *
    * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
    * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
    * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
    *
    * @package     WM3
    * @author      Edi Chen <edi@sun.net.tw>
    * @copyright   2000-2005 SunNet Tech. INC.
    * @version     CVS: $Id: cour_intro_lib.php,v 1.1 2010/02/24 02:40:28 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2005-12-16
    */
    
    /**
	 *	取得introduce中的內容
	 * @param string $content xml string
	 * @param string template or upload
	 * @return string template content
	 */
	function getContent($content, $type = false) {
		if ($xmldoc = @domxml_open_mem($content)) {
			$ctx = xpath_new_context($xmldoc);
			if ($type)
				$nodes = $ctx->xpath_eval('/manifest/intro[@type="'.$type.'"]');
			else
				$nodes = $ctx->xpath_eval('/manifest/intro[@checked="true"]');
			if (count($nodes->nodeset)) {
				$node = $nodes->nodeset[0];
				return $node->get_content();
			}	
		}
		return '';
	}
?>