<?php
   /**
    * /辦公室/課程管理/課程簡介/預覽
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
    * @version     CVS: $Id: cour_intro_show.php,v 1.1 2010/02/24 02:40:28 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2005-12-12
    */

// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/cour_introduce.php');
	require_once(sysDocumentRoot . '/teach/introduce/cour_intro_lib.php');
	require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
// }}} 函式庫引用 end

// {{{ 常數定義 begin

// }}} 常數定義 end
    
// {{{ 變數宣告 begin
    $cour_intro = array('cour_intro', 'cour_arrange', 'teach_intro');
    $types      = array('template', 'upload');
// }}} 變數宣告 end

// {{{ 函數宣告 begin
	
// }}} 函數宣告 end

// {{{ 主程式 begin
	if (!empty($_GET['func']) && in_array($_GET['func'], $cour_intro) && !empty($_GET['type']) && in_array($_GET['type'], $types)) {
		switch ($_GET['func']) {
			case 'cour_intro' :
				$intro_type = 'C';
				break;
			case 'cour_arrange' :
				$intro_type = 'R';
				break;
			case 'teach_intro' :
				$intro_type = 'T';
				break;
		}
		$func = $_GET['func'];
	}
	else if ($_SERVER['argv'][0]) {
		switch ($_SERVER['argv'][0]){
		case '1' :
			$func = 'cour_intro';
			$intro_type = 'C';
			break;
		case '2' :
			$func = 'cour_arrange';
			$intro_type = 'R';
			break;
		case '3' :
			$func = 'teach_intro';
			$intro_type = 'T';
			break;
		default :
			$func = 'cour_intro';
			$intro_type = 'C';
		}
	}
	else
		die('Illegal Access!');

	
	list($content) = dbGetStSr('WM_term_introduce', 'content', 'course_id=' . $sysSession->course_id . ' and intro_type="'.$intro_type.'"', ADODB_FETCH_NUM);
	
	$html = '';
	if ($content) {
		if ($xmldoc = @domxml_open_mem($content)) {
			$ctx = xpath_new_context($xmldoc);
			if ($sysSession->env == 'teach')
				$nodes = $ctx->xpath_eval('/manifest/intro[@type="'.$_GET['type'].'"]');
			else
				$nodes = $ctx->xpath_eval('/manifest/intro[@checked="true"]');
			if (count($nodes->nodeset)) {
				$node = $nodes->nodeset[0];
				$type = $node->get_attribute('type');
				$text = $node->get_content();   
				if ($text && trim($text) != '') {  
					if ($type == 'template')
						$html = stripslashes($text);
					else if ($type == 'upload') {
						$basePath = sprintf('%s/base/%05d/course/%08d/content',				// 課程教材路徑
											sysDocumentRoot,
											$sysSession->school_id,
											$sysSession->course_id);
						$text = un_adjust_char($text);
						if (file_exists($basePath . $text)) {
							$headers = apache_request_headers();
							if (preg_match('/ MSIE (\d+)\./', $headers['User-Agent'], $regs) && intval($regs[1]) > 6)
							{
								$text = str_replace('%2F', '/', rawurlencode($text));
								header('Content-Disposition: filename=' . $text);
							}
							header('Content-Type: '); // 去掉 MIME
							header('Location: ' . substr($basePath . $text, strlen(sysDocumentRoot)));
							die(0);
						}
					}
				}
			}
		}
	}
	
	if ($html == '') 
		$html = stripslashes($MSG[$func . '_content'][$sysSession->lang]);
	
	
	
	showXHTML_head_B('');
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		echo '<center>';
		$ary = array(array($MSG[$func][$sysSession->lang]));
		showXHTML_tabFrame_B($ary, 1, 'mainForm', 'table1', 'action="" method="POST" style="display: inline"');
			if ($sysSession->env == 'teach') 
				showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="700" style="border-collapse: collapse" class="cssTable"');
			else
				showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse"');
				if ($sysSession->env == 'teach') {
					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td_B();
							showXHTML_input('button', '', $MSG['close_window'][$sysSession->lang], '', 'onclick="window.close();"');
						showXHTML_td_E();
					showXHTML_tr_E();
				}
				showXHTML_tr_B('class="cssTrEvn" ');
					showXHTML_td('', $html);
				showXHTML_tr_E();
				
				if ($sysSession->env == 'teach') {
					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td_B();
							showXHTML_input('button', '', $MSG['close_window'][$sysSession->lang], '', 'onclick="window.close();"');
						showXHTML_td_E();
					showXHTML_tr_E();
				}
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		
	showXHTML_body_E();
// }}} 主程式 end

?>
