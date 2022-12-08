<?php
	/**
	 * ���������� function
	 * @todo
	 *     1. ��檺����
	 *     2. ���F
	 *     3. ��z������ function �Ϩ��²��
	 *
	 * @author  ShenTing Lin
	 * @version $Id: syslib.php,v 1.1 2010/02/24 02:40:20 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/wise_template.php');	
	require_once(sysDocumentRoot . '/lang/sys_tools.php');		
	
	$lang = $sysSession->lang;
	$theme_lang = strtolower($lang);
	$tplSysPath = sysDocumentRoot . '/sys/tpl/';
	$tplSchPath = sysDocumentRoot . "/base/{$sysSession->school_id}/door/tpl/";
	$path_theme = "/theme/{$sysSession->theme}/sys/";
	$path_door  = "/base/{$sysSession->school_id}/door/";
	/**
	 * ���o�˪O�����|
	 * @param string $filename : �ɮצW�١A���t���|
	 * @return string $file : �]�t���|�P�ɦW���r��
	 **/
	function getTemplate($filename) {
		global $sysSession, $tplSysPath, $tplSchPath, $lang;

		$tpl_lang = strtolower($lang);
		$file = $tplSchPath . $tpl_lang . '/' . $filename;
		if (file_exists($file)) return $file;
		$file = $tplSchPath . $filename;
		if (file_exists($file)) return $file;
		$file = $tplSysPath . $filename;
		if (file_exists($file)) return $file;
	}
	
	/**
	 * �Ҧ��˪O�w�]�|�ഫ����r
	 * @param object $obj : �˪O����
	 * @return void
	 **/
	function genDefaultTrans(&$obj) {
		global $sysSession, $path_door, $path_theme, $lang, $theme_lang;
		$obj->add_replacement('<%DOOR_PATH%>'  , $path_door);
		$obj->add_replacement('<%THEME_PATH%>' , $path_theme);
		$obj->add_replacement('<%LANGUAGE%>'   , $theme_lang);
		$obj->add_replacement('<%USERNAME%>'   , $sysSession->username);
		$obj->add_replacement('<%SCHOOL_NAME%>', $sysSession->school_name);
		$obj->add_replacement('<!---->'        , '');
	}
	
	/**
	 * cleanSpace()
	 *     �M�� <TAG> �P </TAG> ���h�l���ť�
	 * @param $buffer �һݳB�z�����
	 * @return �B�z�᪺���G
	 **/
	function cleanSpace($buffer) {
		return (preg_replace('/>\s+</', '><', $buffer));
	}	

	/**
	 * showButton()
	 *     ��ܤ@�ӭI�����Ϫ����s
	 * @param $caption ���s�W������r
	 * @param $image ���s���Ϥ�
	 * @param $href �ҭn�s�������}
	 * @param $event �ҭn���檺�ʧ@
	 * @return �@�ӫ��s
	 **/
	function showButton($type, $caption, $image, $extra) {
		if (empty($type))
			$type = 'button';
		if (empty($caption))
			$caption = '&nbsp;&nbsp;';
		if (!empty($image)) {
			if (!file_exists(sysDocumentRoot . $image)) {
				$image = "/theme/{$sysSession->theme}/sys/button.gif";
			}
			$image = " style=\"background-image: url($image);\"";
		}

		//$result = "<button type=\"$type\" class=\"button\" $image $extra >$caption</button>\n";
		$result = "<input type=\"$type\" value=\"$caption\" $image $extra />\n";
		return $result;
	}		

	function layout($title, $content) {
		global $sysSession, $MSG, $lang, $sysConn;
		// ���� -----------------------------------------------------------------------
		include_once(sysDocumentRoot . '/sys/door/mod_head.php');
		$cont_head = mod_head();				
	
		// ���� -----------------------------------------------------------------------
		include_once(sysDocumentRoot . '/sys/door/mod_foot.php');
		$cont_foot = mod_foot();

		$tpl = getTemplate('reg_index.htm');
		$myTemplate = new Wise_Template($tpl);
		genDefaultTrans($myTemplate);
		
		$css = getTemplate('door.css');
		if (file_exists($css)) {
			$css = sprintf('<link rel="stylesheet" type="text/css" href="%s">', $path_door . 'tpl/door.css');
		} else {
			$css = '';
		}

		$myTemplate->add_replacement('<%USER_THEME%>'	, $css);
		$myTemplate->add_replacement('<%TITLE%>'     	, $title);	
		$myTemplate->add_replacement('<%MOD_HEAD%>'		, $cont_head);		
		$myTemplate->add_replacement('<%MOD_CONTENT%>'	, $content);
		$myTemplate->add_replacement('<%MOD_FOOT%>'     , $cont_foot);		
		$myTemplate->print_result(false);					
		return;		
	}

	/**
	 * getTheme()
	 *     ���o�t�ΩҦ����G��
	 * @return array �G��
	 **/
	function getTheme() {
		$theme = '';
		$dp = opendir(sysDocumentRoot . '/theme/');
		while ( $entry = readdir($dp) ) {
			if ( strpos($entry, '.') !== 0 ) {
				if (is_dir(sysDocumentRoot . '/theme/' . $entry)) $theme[$entry] = $entry;
			}
		}
		closedir($dp);
		return $theme;
	}
?>
