<?php
   /**
    * /辦公室/課程管理/課程簡介/將舊資料轉換成新資料，轉換前須先備份WM_term_introduce
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
    * @version     CVS: $Id: V1.3#942_20060906_cour_intro_conv.php,v 1.1 2010/02/24 02:38:57 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2005-12-21
    * @usage	   Web執行，可多次重複執行，異動table : WM_term_introduce
    */

// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
// }}} 函式庫引用 end
	
// {{{ 常數定義 begin
	$cour_format = array(1	=>	'/manifest/metadata',	// 用來判斷新舊格式
						 2	=>	'/manifest/intro');	
// }}} 常數定義 end
    
// {{{ 變數宣告 begin
    
// }}} 變數宣告 end

// {{{ 函數宣告 begin
	/**
	 *	判斷是新舊格式
	 * 0 : 格式錯誤
	 * 1 : 舊
	 * 2 : 新
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
	 * parse舊資料的XML抓取所需要的資料
	 */
	function parseXML() {
		global $ctx, $show, $filename, $html;
		$show = 'template';
		$filename = $html = '';
		// 處理metadata
		$metadata  = $ctx->xpath_eval('/manifest/metadata');
		if (count($metadata->nodeset)) {
			$nodes = $metadata->nodeset[0]->child_nodes();
			foreach ($nodes as $node) {
				if ($node->node_type() != 1) continue;
				${$node->tagname()} = $node->get_content();
			}
		}
		// 處理section
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
	 *	將parse後抓取的資料產生新的xml
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
	 * 產生template的HTML內容
	 */
	function buildHTML() {
		global $html;
		if (trim($html) != '') {
			$msg_title = iconv('Big5', 'UTF-8', '標題');
			$msg_content = iconv('Big5', 'UTF-8', '內容');
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
	 * 轉換上傳檔案中的檔案路徑為相對路徑而非絕對路徑
	 * $param $content String 課程簡介的xml string
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
					if ($old_filename == $new_filename)	// 不需要update
						return false;
					else
						return $xmldoc->dump_mem(true);	// 有修改過，需要update
				}
			}
		}
		return false;
	}
	
// }}} 函數宣告 end

// {{{ 主程式 begin
	$rs = dbGetStMr('WM_term_introduce', 'course_id, intro_type, content', '1=1 order by course_id');
	if ($rs)
		while ($row = $rs->FetchRow()) {
			if ($xmldoc = domxml_open_mem($row['content'])) {
				$ctx = xpath_new_context($xmldoc);
				switch(detect_cour_format()) {
					case 0 :	// 格式錯誤
						echo locale_conv('格式錯誤');
						break;
					case 1 :	// 轉換舊資料
						parseXML();
						buildHTML();
						buildXML();
						dbSet('WM_term_introduce', 'content=' . $sysConn->qstr($content), 'course_id=' . $row['course_id'] . ' and intro_type="'.$row['intro_type'].'"');
						if ($sysConn->ErrorNo())
							die('course_id= ' . $row['course_id'] . ', intro_type=' . $row['intro_type'] . ' and Error Message=' . $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());
						else
							echo locale_conv('轉換 ') . $row['course_id'] . ' : ' . $row['intro_type']. locale_conv(' 成功 '). '<br />';
						break;
					case 2 :	// 轉換上傳檔案中的檔案路徑為相對路徑而非絕對路徑
						if ($content = repair_cour_intro($row['content'])) {
							dbSet('WM_term_introduce', 'content=' . $sysConn->qstr($content), 'course_id=' . $row['course_id'] . ' and intro_type="'.$row['intro_type'].'"');
							if ($sysConn->ErrorNo())
								die('course_id= ' . $row['course_id'] . ', intro_type=' . $row['intro_type'] . ' and Error Message=' . $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());
							else
								echo locale_conv('轉換 ') . $row['course_id'] . ' : ' . $row['intro_type']. locale_conv(' 成功 '). '<br />';
						}
						break;
				}
			}
		}
// }}} 主程式 end

?>