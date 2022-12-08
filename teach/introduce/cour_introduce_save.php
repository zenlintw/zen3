<?php
   /**
    * /辦公室/課程管理/課程簡介/儲存設定
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
    * @version     CVS: $Id: cour_introduce_save.php,v 1.1 2010/02/24 02:40:29 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2005-12-15
    */

// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/cour_introduce.php');
// }}} 函式庫引用 end

// {{{ 常數定義 begin

// }}} 常數定義 end
    
// {{{ 變數宣告 begin
    $cour_intro = array('C'	=>	'cour_intro', 
    							'R'	=>	'cour_arrange', 
    							'T'	=>	'teach_intro'
    							);
// }}} 變數宣告 end

// {{{ 函數宣告 begin

// }}} 函數宣告 end

// {{{ 主程式 begin

// }}} 主程式 end
	foreach ($cour_intro as $func)
		if (!isSet($_POST[$func]))	
			die($MSG['Error'][$sysSession->lang]);
	
	// 取得原先的設定值
	$rs = dbGetStMr('WM_term_introduce', 'intro_type, content', 'course_id='.$sysSession->course_id, ADODB_FETCH_ASSOC);
	$contents = array();
	if ($rs)
		while ($row = $rs->FetchRow()) {
			$contents[$row['intro_type']] = $row['content'];
		}
		
	foreach ($cour_intro as $k => $v)	{
		$type = $_POST[$cour_intro[$k]];
		$modify = false; $found = false;
		if ($contents[$k]) {	// 已經有資料
			if ($xmldoc = domxml_open_mem($contents[$k])) {
				$root = $xmldoc->first_child();
				$nodes = $root->child_nodes();
				foreach ($nodes as $node) {
					if ($node->node_type() != 1) continue;
					if ($node->get_attribute('type') == $type) $found = true;
					if ($node->get_attribute('type') == $type && $node->get_attribute('checked') != 'true') {
						$node->set_attribute('checked', 'true');
						$modify = true;
					}
					else if ($node->get_attribute('type') != $type && $node->get_attribute('checked') == 'true') {
						$node->set_attribute('checked', 'false');
						$modify = true;
					}
				}
				
				if (!$found) {	// 沒找到對應的node
					$newNode = $xmldoc->create_element('intro');
					$newNode->set_attribute('type', $type);
					$newNode->set_attribute('checked', 'true');
					$root->append_child($newNode);
					$modify = true;
				}
			}
			if ($modify) {	// 如果有變更的才更新
				$xml_content = $sysConn->qstr($xmldoc->dump_mem(true));
				dbSet('WM_term_introduce', 'content='.$xml_content.'', 'course_id=' . $sysSession->course_id . ' and intro_type="'.$k.'"');
			}
		}
		else {	// 尚無資料
			$xml_content = <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
	<manifest>
		<intro type="{$type}" checked="true"></intro>
	</manifest>
EOF;
			$xml_content = $sysConn->qstr($xml_content);
			dbNew('WM_term_introduce', 'course_id, intro_type, content', "{$sysSession->course_id}, '{$k}', {$xml_content}");
		}
	}
	
	echo <<< EOB
	<script language="javascript">
		alert("{$MSG['save_success'][$sysSession->lang]}");
		location.replace('cour_introduce.php');
	</script>
EOB;
?>
