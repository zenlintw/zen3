<?php
	/**
	 * 工具列
	 *
	 *     所需樣板名稱：tools.htm
	 *
	 * @since   2004/10/27
	 * @author  ShenTing Lin
	 * @version $Id: mod_tools.php,v 1.1 2010/02/24 02:40:20 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	function mod_tools() {
		global $MSG, $lang;
		$tpl = getTemplate('tools.htm');
		$myTemplate = new Wise_Template($tpl);
		$myTemplate->add_replacement('<%MSG_SITE_MAP%>', $MSG['tool_site_map'][$lang]);
		$myTemplate->add_replacement('<%MSG_PETITION%>', $MSG['tool_petition'][$lang]);
		$myTemplate->add_replacement('<%MSG_SEARCH%>'  , $MSG['tool_course_search'][$lang]);
		$myTemplate->add_replacement('<%MSG_FAQ%>'     , $MSG['tool_faq'][$lang]);
		$myTemplate->add_replacement('<%MSG_ABOUT%>'   , $MSG['tool_about'][$lang]);
		$myTemplate->add_replacement('<%MSG_NEWS%>'    , $MSG['tool_news'][$lang]);
		$myTemplate->add_replacement('<%MSG_URL%>'     , 'parent.location.replace("/learn/mycourse/index.php");');

		genDefaultTrans($myTemplate);
		return $myTemplate->get_result(false);
	}

?>
