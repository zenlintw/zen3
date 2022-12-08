<?
	/**
    * /�줽��/�ҵ{�޲z/�ҵ{²�� library
    *
    * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
    *
    * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
    * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
    * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
    *
    * @package     WM3
    * @author      Edi Chen <edi@sun.net.tw>
    * @copyright   2000-2005 SunNet Tech. INC.
    * @version     CVS: $Id: cour_intro_lib.php,v 1.1 2010/02/24 02:40:28 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2005-12-16
    */
    
    /**
	 *	���ointroduce�������e
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