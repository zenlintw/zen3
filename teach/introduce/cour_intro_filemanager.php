<?php
   /**
    * /辦公室/課程管理/課程簡介/上傳網頁
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
    * @version     CVS: $Id: cour_intro_filemanager.php,v 1.1 2010/02/24 02:40:28 saly Exp $
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
	$cour_intro = array('cour_intro', 'cour_arrange', 'teach_intro');	// 課程介紹,課程安排,教師介紹
	$basePath   = sprintf('%s/base/%05d/course/%08d/content',			// 課程教材路徑
	                      sysDocumentRoot,
	                      $sysSession->school_id,
	                      $sysSession->course_id);
	$currPath = (empty($_POST['currPath']) || trim($_POST['currPath']) == '') ? '/' : rawurldecode($_POST['currPath']);	//	課程教材路徑底下的相對路徑
	// 檢查路徑是否在允許的路徑之內
	if (strpos(realpath($basePath . $currPath), realpath($basePath)) !== 0)
		$currPath = '/';
	$fullPath = $basePath . $currPath;	// 真實路徑
	$withPath = substr($fullPath, strlen(sysDocumentRoot));	// 教材路徑
// }}} 變數宣告 end

// {{{ 函數宣告 begin
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
	
	/**
	 *	取得檔案類型
	 * @param string $fname 檔案名稱
	 * @return string 檔案類型
	 */
	function getFileType($fname){
		$ext = @strtolower(strrchr($fname, '.'));
		switch($ext){
			case '.avi' :
			case '.bmp' :
			case '.doc' :
			case '.gif' :
			case '.htm' :
			case '.html':
			case '.jpg' :
			case '.mht' :
			case '.mp3' :
			case '.pdf' :
			case '.ppt' :
			case '.swf' :
			case '.txt' :
			case '.wav' :
			case '.xls' :
			case '.zip' :
				return substr($ext, 1);
			default:
				return 'default';
		}
	}
	
	/**
	 * 印出目錄與檔案
	 */
	function display_entry() {
		global $fullPath, $MSG, $sysSession, $entry, $withPath, $currPath, $selectFile;
		static $allow_types;
		
		// 允許當作介紹頁的檔案類型
		if (!isset($allow_types)) $allow_types = array('htm', 'html', 'swf', 'mht');
		
		if ($currPath != '/') {	// 如果不是根目錄,則顯示 / 與 ..
			showXHTML_tr_B('class="' . $css = $css == 'cssTrEvn' ? 'cssTrOdd' : 'cssTrEvn' . '"');
				showXHTML_td('align="center"', '<big>&#8629;</big>');
				showXHTML_td('align="center"', '<a href="javascript:chdir(\'cd\',\'/\')" title="' . $MSG['return_root'][$sysSession->lang] . '" class="cssAnchor"><big><b>&nbsp;/&nbsp;</b></big></a>');
				showXHTML_td('colspan="3"', '<a href="javascript:chdir(\'cd\',\'..\')" title="' . $MSG['return_parent'][$sysSession->lang] . '" class="cssAnchor"><big><b>. .</b></big></a>');
			showXHTML_tr_E();
		}
		
		for ($i = 0; $i < count($entry); $i++) {
			for ($j = 0; $j < count($entry[$i]); $j++) {
				$fileName = $entry[$i][$j];
				$fullfile = $fullPath . $fileName;
				$fileType = $i ? getFileType($fileName): 'folder';
				showXHTML_tr_B('class="' . ($css = $css == 'cssTrEvn' ? 'cssTrOdd' : 'cssTrEvn') . '"');
					showXHTML_td('align="center"', '<img src="/theme/default/filetype/' . $fileType . '.gif" valign="absmiddle">');
					showXHTML_td('align="center"', in_array($fileType, $allow_types) ? '<input type="radio" name="file_entry[]" value="'.($j + 1).'" '.(locale_conv($currPath . $fileName) == $selectFile ? 'checked' : '').'>' : '&nbsp;');
					showXHTML_td('', '<a class="cssAnchor" href="'. ($i?(str_replace('%2F', '/', rawurlencode($withPath . $fileName)) . '" target="_blank">'):('javascript:chdir(\'cd\',\'' . ($j+1) . '\')" title="' . $MSG['chdir'][$sysSession->lang] . '">')) . adjust_char($fileName) . '</a>');
					showXHTML_td('align="right"', number_format(filesize($fullfile)) . '&nbsp;&nbsp;&nbsp;&nbsp;');
					showXHTML_td('align="center"', date('Y-m-d H:i:s', filemtime($fullfile)));
				showXHTML_tr_E();
			}
		}
		
	}
	
// }}} 函數宣告 end

// {{{ 主程式 begin
	if (empty($_POST['func']) || !in_array($_POST['func'], $cour_intro))
		die('Illegal Access!');
	
	// 判斷操作的功能 切換目錄或者新建目錄或者上傳檔案
	if (isset($_POST['op'])) {
		if ($_POST['op'] == 'cd' && !empty($_POST['target']) && trim($_POST['target']) != '') {	//	切換目錄
			switch($_POST['target']){
				case '/':
					$currPath = '/';
					break;
				case '..':
					if ($currPath != '/') $currPath = str_replace('//', '/', dirname($currPath) . '/');
					break;
				default:
					$target = intval($_POST['target']);
					if ($target-- > 0){
						getAllEntry();
						if ($target < count($entry[0])){
							$targetPath = $basePath . $currPath . $entry[0][$target];
							if (is_dir($targetPath))
								$currPath .= ($entry[0][$target] . '/');
						}
					}
					break;
			}
			$fullPath = $basePath . $currPath;
			$withPath = substr($fullPath, strlen(sysDocumentRoot));
		} 
		else if ($_POST['op'] == 'md' && !empty($_POST['target']) && trim($_POST['target']) != '') {	// 新建目錄
			$newDir = un_adjust_char(stripslashes($_POST['target']));
			if (mb_ereg('[\\/:*?"<>|]', $newDir) === FALSE) {
					@chdir($fullPath);
					@mkdir($newDir, 0755);
				}
		}
		else if ($_POST['op'] == 'ul') { // 上傳檔案
			$a = count($_FILES['upload']['tmp_name']);
			for($i = 0; $i< $a; $i++){
				$filename = mb_convert_encoding(stripslashes($_FILES['upload']['name'][$i]), 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win');
				if (is_uploaded_file($_FILES['upload']['tmp_name'][$i]))
				    move_uploaded_file($_FILES['upload']['tmp_name'][$i], $fullPath . $filename);
			}
		}
		$selectFile = stripslashes($_POST['selectFile']);
	}
	else {	// 不是操作這頁的功能,搜尋是否有設定上傳網頁的檔案
		switch ($_POST['func']) {
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
		list($content) = dbGetStSr('WM_term_introduce', 'content', 'course_id=' . $sysSession->course_id . ' and intro_type="'.$intro_type.'"', ADODB_FETCH_NUM);
		$selectFile = empty($content) ? '' : trim(getContent($content, 'upload'));
	}
	
	
	$js = <<< EOB
	/**
	 *	顯示新建目錄的對話框
	 */
	function create_dir() {
		var obj = document.getElementById('mkDir');
		obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 520;
		obj.style.top   = document.body.scrollTop  + 10;
		obj.style.display = '';
	}
	
	/**
	 *	切換目錄
	 * @param string op 操作的功能 切換目錄或新建目錄
	 * @param string target 目錄名稱
	 */
	function chdir(op,target){
		var obj = document.getElementById('mainForm');
		obj.op.value = op;
		obj.target.value = target;
		obj.submit();
	}
	
	/**
	 * 儲存設定的上傳網頁
	 */
	function assign() {
		var obj = document.getElementById('mainForm');
		var sel = false;
		var nodes = obj.getElementsByTagName('input');
		for(var i=0; i<nodes.length; i++){
			if (nodes[i].type == 'radio'){
				if (nodes[i].checked){
					sel = true;
					break;
				}
			}
		}
		
		if (!sel) {
			alert("{$MSG['msg_no_select_file'][$sysSession->lang]}");
			return;
		}
			
		if (obj) {
			obj.action = 'cour_intro_save.php';
			obj.submit();
		}
	}
	
	/**
	 * 檢查輸入的目錄名稱是否為空白或者還有特殊符號（底線與減號除外）
	 */
	function chkNewDir() {
		var val = document.getElementById('newdir').value.replace(/\s/g, '');
		var reg = /^[^\\x00-\\x2C\\x2E\\x2F\\x3A-\\x40\\x5B-\\x5E\\x60\\x7B-\\x80]+$/;
		
		if (val == '')
			alert("{$MSG['empty_dir_name'][$sysSession->lang]}");
		else if (!reg.test(val))
			alert("{$MSG['msg_illegalchars'][$sysSession->lang]}");
		else
			chdir('md', val);
	}
	
	window.onload = function() {
		document.getElementById("toolbar2").innerHTML = document.getElementById("toolbar1").innerHTML;
	};
	
	// 切換到 /辦公室/課程管理/教材檔案管理功能
	function goto_fileManager() {
		var sysbar = parent.c_sysbar;
		if (typeof(sysbar) != 'object') return;
		
		sysbar.chgMenuItem('SYS_02_02_002');
	}
	
EOB;

	showXHTML_head_B('');
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		showXHTML_script('inline', $js);
		showXHTML_head_E();
	showXHTML_body_B();
		$ary = array(array($MSG[$_POST['func']][$sysSession->lang] . '_' . $MSG['intro_upload'][$sysSession->lang]));
		showXHTML_tabFrame_B($ary, 1, 'mainForm', 'table1', 'action="'.$_SERVER['PHP_SELF'].'" method="POST" enctype="multipart/form-data" onsubmit="disableAll();" style="display: inline"');
			showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="760" style="border-collapse: collapse" class="cssTable"');
				// 說明文字
				showXHTML_tr_B('class="cssTrHelp"');
					$nowSelectFile = ($selectFile && $selectFile != '') ? $selectFile : $MSG['no_select_file'][$sysSession->lang];
			      showXHTML_td('colspan="5"', $MSG['file_text'][$sysSession->lang]. '<br /><font color="blue">'.$MSG['cur_path'][$sysSession->lang] . ' ' . iconv($sysSession->lang, 'UTF-8', $currPath). '<br/ >' . $MSG['selectFile'][$sysSession->lang] . $nowSelectFile . '</font>');
			   showXHTML_tr_E();
				// 工具列
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('colspan="5" id="toolbar1"');
						showXHTML_input('button', '', $MSG['save'][$sysSession->lang], '', 'class="cssBtn" onclick="assign();"');
						showXHTML_input('button', '', $MSG['back'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\'cour_introduce.php\');"');
						showXHTML_input('button', '', $MSG['create_dir'][$sysSession->lang], '', 'class="cssBtn" onclick="create_dir();"');
						showXHTML_input('button', '', $MSG['filemana'][$sysSession->lang], '', 'class="button01" onclick="goto_fileManager();"');
						echo str_repeat('&nbsp;',5);
						showXHTML_input('file', 'upload[]', '', '', 'size="20" class="box02"');
						showXHTML_input('button', '', $MSG['upload'][$sysSession->lang], '', 'class="cssBtn" onclick="chdir(\'ul\',\'\')"');
					showXHTML_td_E();
				showXHTML_tr_E();
				// 標題
				showXHTML_tr_B('class="bg02 font01"');
			   	showXHTML_td('align="center" width="50"  nowrap', $MSG['type'][$sysSession->lang]);
			   	showXHTML_td('align="center" width="50"  nowrap', $MSG['assign'][$sysSession->lang]);
			   	showXHTML_td('align="left"   width="330" nowrap', $MSG['filename'][$sysSession->lang]);
			   	showXHTML_td('align="center" width="110"  nowrap', $MSG['filesize'][$sysSession->lang]);
			   	showXHTML_td('align="center" width="180" nowrap', $MSG['filetime'][$sysSession->lang]);
				showXHTML_tr_E();
				// 檔案列表
				getAllEntry();
				display_entry();
				// 工具列
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('colspan="5" id="toolbar2"');
					showXHTML_td_E();
				showXHTML_tr_E();				
			showXHTML_table_E();
			
			showXHTML_input('hidden', 'func', $_POST['func']);
			showXHTML_input('hidden', 'op', '');
			showXHTML_input('hidden', 'target', '');
			showXHTML_input('hidden', 'type', 'upload');
			showXHTML_input('hidden', 'currPath', rawurlencode($currPath));
			showXHTML_input('hidden', 'selectFile', $selectFile);
			
			// 新建目錄
			$ary = array(array($MSG['create_dir'][$sysSession->lang], '', ''));
			showXHTML_tabFrame_B($ary, 1, 'addForm', 'mkDir', '', true);
  			showXHTML_script('include', '/lib/dragLayer.js');
			showXHTML_table_B('id="mdir" width="400" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse;" class="box01"');
			    showXHTML_tr_B('class="cssTrOdd"');
			      showXHTML_td_B();
			        echo $MSG['create_dir'][$sysSession->lang];
			        showXHTML_input('text', 'newdir', '', '', 'id="newdir" size="20" class="box02"');
			        showXHTML_input('button', '', $MSG['sure_create'][$sysSession->lang], '', 'class="cssBtn" onclick="chkNewDir();"');
			        showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="document.getElementById(\'mkDir\').style.display = \'none\';"');
			      showXHTML_td_E();
			    showXHTML_tr_E();
			showXHTML_table_E();
			showXHTML_tabFrame_E();
			
		showXHTML_tabFrame_E();
		
	showXHTML_body_E();
// }}} 主程式 end

?>
