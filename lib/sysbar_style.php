<?php
	/**
	 *
	 *
	 * @since   2005//
	 * @author  ShenTing Lin
	 * @version $Id: sysbar_style.php,v 1.1 2010/02/24 02:39:34 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');

	header("Content-type: text/css");
	$css = getThemeFile('sysbar.css');
	if (!empty($css) && ($css != $source)) {
		echo '/* user style sheet */';
		echo file_get_contents(sysDocumentRoot . '/' . $css);
	}
?>
