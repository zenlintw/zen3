<?php
   /**
    * /辦公室/課程管理/課程簡介/套用網頁樣板儲存 上傳網頁儲存
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
    * @version     CVS: $Id: cour_intro_save.php,v 1.1 2010/02/24 02:40:28 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2005-12-13
    */

// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/cour_introduce.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
// }}} 函式庫引用 end

// {{{ 常數定義 begin

// }}} 常數定義 end
    
// {{{ 變數宣告 begin
    $cour_intro = array('cour_intro', 'cour_arrange', 'teach_intro');
    $types      = array('template', 'upload');
// }}} 變數宣告 end

// {{{ 函數宣告 begin
	/**
	 *	儲存 課程介紹/課程安排/教師介紹 內容
	 * @param string $func C, R, T
	 * @param string $type upload or template
	 * @param string $content content
	 */
	function saveCourIntro($func, $type, $cour_content) {
		global $sysSession,$sysConn;
		list($xml_content) = dbGetStSr('WM_term_introduce', 'content', 'course_id=' . $sysSession->course_id . ' and intro_type="'.$func.'"', ADODB_FETCH_NUM);
		if (empty($xml_content)) {	// 第一次新增資料
			$cour_content = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]+/', ' ', trim($cour_content)));
			$xml_content = <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
	<manifest>
		<intro type="$type" checked="true">$cour_content</intro>
	</manifest>
EOF;
			$xml_content = $sysConn->qstr($xml_content);
			dbNew('WM_term_introduce', 'course_id, intro_type, content', "{$sysSession->course_id}, '{$func}', {$xml_content}");
			return true;
		}
		else {	// 已經存在資料
			if ($xmldoc = @domxml_open_mem($xml_content)) {
				$ctx = xpath_new_context($xmldoc);
				$nodes = $ctx->xpath_eval('/manifest/intro[@type="'.$type.'"]');
				if (is_array($nodes->nodeset) && count($nodes->nodeset)) {
					$node = $nodes->nodeset[0];
					if ($node->has_child_nodes()) {
						$textNode = $node->first_child();
						$node->remove_child($textNode);
					}
					$textNode = $xmldoc->create_text_node($cour_content);
					$node->append_child($textNode);
				}
				else {	// 沒有對應的element
					$root = $xmldoc->first_child();
					$newNode = $xmldoc->create_element('intro');
					$newNode->set_attribute('type', $type);
					$newNode->set_attribute('checked', 'false');
					$newText = $xmldoc->create_text_node($cour_content);
					$newNode->append_child($newText);
					$root->append_child($newNode);
				}
				$xml_content = $sysConn->qstr($xmldoc->dump_mem(true));
				dbSet('WM_term_introduce', 'content='.$xml_content.'', 'course_id=' . $sysSession->course_id . ' and intro_type="'.$func.'"');
				return true;
			}
			else return false;
		}
		return false;
	}
	
	/**
	 * 取得現行目錄下所有目錄、檔案
	 * entry[0] 放置目錄
	 * entry[1] 放置檔案
	 */
	function getAllEntry(){
		global $fullPath, $entry;
		$entry = array(array(), array());
		clearstatcache();

		if ($dir = @opendir($fullPath)) {
			while (($file = readdir($dir)) !== false) {
				$fullfile = $fullPath . $file;
				if(is_dir($fullfile) && !ereg('^\.', $file)){
					$entry[0][] = $file;
				}
				elseif (is_file($fullfile)){
					$entry[1][] = $file;
				}
			}
			closedir($dir);
		}
		sort($entry[0]);
		sort($entry[1]);
	}
// }}} 函數宣告 end

// {{{ 主程式 begin
	if (empty($_POST['func']) || !in_array($_POST['func'], $cour_intro) || empty($_POST['type']) || !in_array($_POST['type'], $types))
		die('Illegal Access!');
	
	switch ($_POST['func']) {
		case 'cour_intro' :
			$intro_type = 'C';
			$function_id = $_POST['type'] == 'template' ? '0800100100' : '0800100200';
			break;
		case 'cour_arrange' :
			$intro_type = 'R';
			$function_id = $_POST['type'] == 'template' ? '0800200100' : '0800200200';
			break;
		case 'teach_intro' :
			$intro_type = 'T';
			$function_id = $_POST['type'] == 'template' ? '0800300100' : '0800300200';
			break;
	}
	$sysSession->cur_func=$function_id;
	$sysSession->restore();

	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	
	if ($_POST['type'] == 'template')
		$content = !empty($_POST['content']) ? strip_scr($_POST['content']) : '';
	else if ($_POST['file_entry'] && count($_POST['file_entry'])) {
		$basePath = sprintf('%s/base/%05d/course/%08d/content',				// 課程教材路徑
								sysDocumentRoot,
								$sysSession->school_id,
								$sysSession->course_id);
		$currPath = (empty($_POST['currPath']) || trim($_POST['currPath']) == '') ? '/' : rawurldecode($_POST['currPath']);	//	課程教材路徑底下的相對路徑
		// 檢查路徑是否在允許的路徑之內
		if (strpos(realpath($basePath . $currPath), realpath($basePath)) !== 0)
			$currPath = '/';
		$fullPath = $basePath . $currPath;	// 真實路徑	
		getAllEntry();
		$target = intval($_POST['file_entry'][0]) - 1;
		if ($target < count($entry[1])) {
			$targetPath = $currPath . $entry[1][$target];
			$content = adjust_char($targetPath);
			// $content = locale_conv($targetPath);
		}
	}
	
	$msg = saveCourIntro($intro_type, $_POST['type'], $content) ? $MSG['save_success'][$sysSession->lang] : $MSG['save_fail'][$sysSession->lang];
	wmSysLog($sysSession->cur_func, $sysSession->course_id ,0 ,0, 'auto', $_SERVER['PHP_SELF'], $msg);
	echo <<< EOB
	<script language="javascript">
		alert("{$msg}");
		location.replace('cour_introduce.php');
	</script>
EOB;
	
// }}} 主程式 end

?>
