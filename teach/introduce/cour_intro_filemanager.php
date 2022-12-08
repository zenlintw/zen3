<?php
   /**
    * /�줽��/�ҵ{�޲z/�ҵ{²��/�W�Ǻ���
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
    * @version     CVS: $Id: cour_intro_filemanager.php,v 1.1 2010/02/24 02:40:28 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2005-12-12
    */

// {{{ �禡�w�ޥ� begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/cour_introduce.php');
	require_once(sysDocumentRoot . '/teach/introduce/cour_intro_lib.php');
	require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
// }}} �禡�w�ޥ� end

// {{{ �`�Ʃw�q begin

// }}} �`�Ʃw�q end
    
// {{{ �ܼƫŧi begin
	$cour_intro = array('cour_intro', 'cour_arrange', 'teach_intro');	// �ҵ{����,�ҵ{�w��,�Юv����
	$basePath   = sprintf('%s/base/%05d/course/%08d/content',			// �ҵ{�Ч����|
	                      sysDocumentRoot,
	                      $sysSession->school_id,
	                      $sysSession->course_id);
	$currPath = (empty($_POST['currPath']) || trim($_POST['currPath']) == '') ? '/' : rawurldecode($_POST['currPath']);	//	�ҵ{�Ч����|���U���۹���|
	// �ˬd���|�O�_�b���\�����|����
	if (strpos(realpath($basePath . $currPath), realpath($basePath)) !== 0)
		$currPath = '/';
	$fullPath = $basePath . $currPath;	// �u����|
	$withPath = substr($fullPath, strlen(sysDocumentRoot));	// �Ч����|
// }}} �ܼƫŧi end

// {{{ ��ƫŧi begin
	/**
	 * ���o�{��ؿ��U�Ҧ��ؿ��B�ɮ�
	 * entry[0] ��m�ؿ�
	 * entry[1] ��m�ɮ�
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
	 *	���o�ɮ�����
	 * @param string $fname �ɮצW��
	 * @return string �ɮ�����
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
	 * �L�X�ؿ��P�ɮ�
	 */
	function display_entry() {
		global $fullPath, $MSG, $sysSession, $entry, $withPath, $currPath, $selectFile;
		static $allow_types;
		
		// ���\��@���Э����ɮ�����
		if (!isset($allow_types)) $allow_types = array('htm', 'html', 'swf', 'mht');
		
		if ($currPath != '/') {	// �p�G���O�ڥؿ�,�h��� / �P ..
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
	
// }}} ��ƫŧi end

// {{{ �D�{�� begin
	if (empty($_POST['func']) || !in_array($_POST['func'], $cour_intro))
		die('Illegal Access!');
	
	// �P�_�ާ@���\�� �����ؿ��Ϊ̷s�إؿ��Ϊ̤W���ɮ�
	if (isset($_POST['op'])) {
		if ($_POST['op'] == 'cd' && !empty($_POST['target']) && trim($_POST['target']) != '') {	//	�����ؿ�
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
		else if ($_POST['op'] == 'md' && !empty($_POST['target']) && trim($_POST['target']) != '') {	// �s�إؿ�
			$newDir = un_adjust_char(stripslashes($_POST['target']));
			if (mb_ereg('[\\/:*?"<>|]', $newDir) === FALSE) {
					@chdir($fullPath);
					@mkdir($newDir, 0755);
				}
		}
		else if ($_POST['op'] == 'ul') { // �W���ɮ�
			$a = count($_FILES['upload']['tmp_name']);
			for($i = 0; $i< $a; $i++){
				$filename = mb_convert_encoding(stripslashes($_FILES['upload']['name'][$i]), 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win');
				if (is_uploaded_file($_FILES['upload']['tmp_name'][$i]))
				    move_uploaded_file($_FILES['upload']['tmp_name'][$i], $fullPath . $filename);
			}
		}
		$selectFile = stripslashes($_POST['selectFile']);
	}
	else {	// ���O�ާ@�o�����\��,�j�M�O�_���]�w�W�Ǻ������ɮ�
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
	 *	��ܷs�إؿ�����ܮ�
	 */
	function create_dir() {
		var obj = document.getElementById('mkDir');
		obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 520;
		obj.style.top   = document.body.scrollTop  + 10;
		obj.style.display = '';
	}
	
	/**
	 *	�����ؿ�
	 * @param string op �ާ@���\�� �����ؿ��ηs�إؿ�
	 * @param string target �ؿ��W��
	 */
	function chdir(op,target){
		var obj = document.getElementById('mainForm');
		obj.op.value = op;
		obj.target.value = target;
		obj.submit();
	}
	
	/**
	 * �x�s�]�w���W�Ǻ���
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
	 * �ˬd��J���ؿ��W�٬O�_���ťթΪ��٦��S��Ÿ��]���u�P����~�^
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
	
	// ������ /�줽��/�ҵ{�޲z/�Ч��ɮ׺޲z�\��
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
				// ������r
				showXHTML_tr_B('class="cssTrHelp"');
					$nowSelectFile = ($selectFile && $selectFile != '') ? $selectFile : $MSG['no_select_file'][$sysSession->lang];
			      showXHTML_td('colspan="5"', $MSG['file_text'][$sysSession->lang]. '<br /><font color="blue">'.$MSG['cur_path'][$sysSession->lang] . ' ' . iconv($sysSession->lang, 'UTF-8', $currPath). '<br/ >' . $MSG['selectFile'][$sysSession->lang] . $nowSelectFile . '</font>');
			   showXHTML_tr_E();
				// �u��C
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
				// ���D
				showXHTML_tr_B('class="bg02 font01"');
			   	showXHTML_td('align="center" width="50"  nowrap', $MSG['type'][$sysSession->lang]);
			   	showXHTML_td('align="center" width="50"  nowrap', $MSG['assign'][$sysSession->lang]);
			   	showXHTML_td('align="left"   width="330" nowrap', $MSG['filename'][$sysSession->lang]);
			   	showXHTML_td('align="center" width="110"  nowrap', $MSG['filesize'][$sysSession->lang]);
			   	showXHTML_td('align="center" width="180" nowrap', $MSG['filetime'][$sysSession->lang]);
				showXHTML_tr_E();
				// �ɮצC��
				getAllEntry();
				display_entry();
				// �u��C
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
			
			// �s�إؿ�
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
// }}} �D�{�� end

?>
