<?php
   /**
    * /�줽��/�ҵ{�޲z/�ҵ{²��/�N�¸���ഫ���s��ơA�ഫ�e�����ƥ�WM_term_introduce
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
    * @version     CVS: $Id: V1.3#942_20060906_cour_intro_conv.php,v 1.1 2010/02/24 02:38:57 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2005-12-21
    * @usage	   Web����A�i�h�����ư���A����table : WM_term_introduce
    */

// {{{ �禡�w�ޥ� begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
// }}} �禡�w�ޥ� end
	
// {{{ �`�Ʃw�q begin
	$cour_format = array(1	=>	'/manifest/metadata',	// �ΨӧP�_�s�®榡
						 2	=>	'/manifest/intro');	
// }}} �`�Ʃw�q end
    
// {{{ �ܼƫŧi begin
    
// }}} �ܼƫŧi end

// {{{ ��ƫŧi begin
	/**
	 *	�P�_�O�s�®榡
	 * 0 : �榡���~
	 * 1 : ��
	 * 2 : �s
	 */
	function detect_cour_format() {
		global $cour_format, $ctx;
		foreach ($cour_format as $k => $v) {
			$ret = $ctx->xpath_eval($v);
			if (count($ret->nodeset)) return $k;
		}
		return 0;
	}
	
	/**
	 * parse�¸�ƪ�XML����һݭn�����
	 */
	function parseXML() {
		global $ctx, $show, $filename, $html;
		$show = 'template';
		$filename = $html = '';
		// �B�zmetadata
		$metadata  = $ctx->xpath_eval('/manifest/metadata');
		if (count($metadata->nodeset)) {
			$nodes = $metadata->nodeset[0]->child_nodes();
			foreach ($nodes as $node) {
				if ($node->node_type() != 1) continue;
				${$node->tagname()} = $node->get_content();
			}
		}
		// �B�zsection
		$sections = $ctx->xpath_eval('/manifest/section');
		if (count($sections->nodeset)) {
			foreach ($sections->nodeset as $section) {
				$title = $matter = '';
				$nodes = $section->child_nodes();
				foreach($nodes as $node) {
					if ($node->node_type() != 1) continue;
					${$node->tagname()} = $node->get_content();
				}
				$extra = $extra == 'bgcolor="#ffffff"' ? '' : 'bgcolor="#ffffff"';
				$matter = str_replace("\r\n", '<br>', $matter);
				$html .= <<< BOF
<tr {$extra}>
	<td style="WIDTH: 50%"><font size="2">{$title}</font></td>
	<td style="WIDTH: 50%"><font size="2">{$matter}</font></td>
</tr>
BOF;
			}
		}
	}
	
	/**
	 *	�Nparse��������Ʋ��ͷs��xml
	 */
	function buildXML() {
		global $content, $show, $filename, $html;
		$isTemplate = $show == 'template' ? 'true' : 'false';
		$isUpload = $show == 'upload' ? 'true' : 'false';
		$html = htmlspecialchars($html);
		$filename = preg_replace('/.*\/base\/[0-9]{5}\/course\/[0-9]{8}\/content/', '', $filename);
		$content = <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
	<manifest>
		<intro type="template" checked="{$isTemplate}">{$html}</intro>
		<intro type="upload" checked="{$isUpload}">{$filename}</intro>
	</manifest>
EOF;
	}
	
	/**
	 * ����template��HTML���e
	 */
	function buildHTML() {
		global $html;
		if (trim($html) != '') {
			$msg_title = iconv('Big5', 'UTF-8', '���D');
			$msg_content = iconv('Big5', 'UTF-8', '���e');
			$html = <<< BOE
<table bordercolor="#5275d6" cellspacing="0" cellpadding="0" width="660" bgcolor="#e7ebf7" border="1">
	<tbody>
		<tr>
			<td>
				<table style="WIDTH: 100%" bordercolor="#e7ebf7" cellspacing="1" cellpadding="5" border="0">
					<tbody>
						<tr bgcolor="#c6dbff">
							<td style="WIDTH: 50%">
								<p align="center"><font size="2">{$msg_title}</font></p>
							</td>
							<td style="WIDTH: 50%">
								<p align="center"><font size="2">{$msg_content}</font></p>
							</td>
						</tr>
						{$html}
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
BOE;
		}
	}

	
	/**
	 * �ഫ�W���ɮפ����ɮ׸��|���۹���|�ӫD������|
	 * $param $content String �ҵ{²����xml string
	 */
	function repair_cour_intro($content) {
		if ($xmldoc = @domxml_open_mem($content)) {
			$ctx = xpath_new_context($xmldoc);
			$nodes = $ctx->xpath_eval('/manifest/intro[@type="upload"]');
			if (count($nodes->nodeset)) {
				$node = $nodes->nodeset[0];
				if ($node->has_child_nodes()) {
					$textNode = $node->first_child();
					$old_filename = $textNode->get_content();
					$new_filename = preg_replace('/.*\/base\/[0-9]{5}\/course\/[0-9]{8}\/content/', '', $old_filename);
					$textNode->set_content($new_filename);
					if ($old_filename == $new_filename)	// ���ݭnupdate
						return false;
					else
						return $xmldoc->dump_mem(true);	// ���ק�L�A�ݭnupdate
				}
			}
		}
		return false;
	}
	
// }}} ��ƫŧi end

// {{{ �D�{�� begin
	$rs = dbGetStMr('WM_term_introduce', 'course_id, intro_type, content', '1=1 order by course_id');
	if ($rs)
		while ($row = $rs->FetchRow()) {
			if ($xmldoc = domxml_open_mem($row['content'])) {
				$ctx = xpath_new_context($xmldoc);
				switch(detect_cour_format()) {
					case 0 :	// �榡���~
						echo locale_conv('�榡���~');
						break;
					case 1 :	// �ഫ�¸��
						parseXML();
						buildHTML();
						buildXML();
						dbSet('WM_term_introduce', 'content=' . $sysConn->qstr($content), 'course_id=' . $row['course_id'] . ' and intro_type="'.$row['intro_type'].'"');
						if ($sysConn->ErrorNo())
							die('course_id= ' . $row['course_id'] . ', intro_type=' . $row['intro_type'] . ' and Error Message=' . $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());
						else
							echo locale_conv('�ഫ ') . $row['course_id'] . ' : ' . $row['intro_type']. locale_conv(' ���\ '). '<br />';
						break;
					case 2 :	// �ഫ�W���ɮפ����ɮ׸��|���۹���|�ӫD������|
						if ($content = repair_cour_intro($row['content'])) {
							dbSet('WM_term_introduce', 'content=' . $sysConn->qstr($content), 'course_id=' . $row['course_id'] . ' and intro_type="'.$row['intro_type'].'"');
							if ($sysConn->ErrorNo())
								die('course_id= ' . $row['course_id'] . ', intro_type=' . $row['intro_type'] . ' and Error Message=' . $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());
							else
								echo locale_conv('�ഫ ') . $row['course_id'] . ' : ' . $row['intro_type']. locale_conv(' ���\ '). '<br />';
						}
						break;
				}
			}
		}
// }}} �D�{�� end

?>