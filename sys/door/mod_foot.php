<?php
	/**
	 * 頁尾
	 *
	 *     所需樣板名稱：foot.htm
	 *
	 * @since   2004/10/27
	 * @author  ShenTing Lin
	 * @version $Id: mod_foot.php,v 1.1 2010/02/24 02:40:20 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once(sysDocumentRoot . '/lang/sys.php');

	function mod_foot() {
		global $MSG, $lang;
		$tpl = getTemplate('foot.htm');
		$myTemplate = new Wise_Template($tpl);
		$myTemplate->add_replacement('<%MSG_OFFICE_INFO%>', $MSG['office_information'][$lang]);
		genDefaultTrans($myTemplate);
		return $myTemplate->get_result(false);
	}

?>
