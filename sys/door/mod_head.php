<?php
	/**
	 * 頁首
	 *
	 *     所需樣板名稱：head.htm
	 *
	 * @since   2004/10/27
	 * @author  ShenTing Lin
	 * @version $Id: mod_head.php,v 1.2 2009-11-06 05:49:10 edi Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once(sysDocumentRoot . '/lang/multi_lang.php');

	function mod_head() {
		global $sysSession, $MSG;

		$tpl = getTemplate('head.htm');
		$myTemplate = new Wise_Template($tpl);

		if($_SERVER['SCRIPT_NAME'] == '/index.php') {
			$conf = getConstatnt($sysSession->school_id);
			$avln = explode(',', $conf['sysAvailableChars']);
			$tmp  = array();
			$out  = '';
			if (is_array($avln) && count($avln))
			    foreach ($avln as $l)
					$tmp[] = '<td class="fontTools" align="right" nowrap="nowrap"><a href="javascript:;" onclick="javascript:window.location.href=\'/index.php?lang=' . $l . '\'; return false;" class="cssToolsAnchor">' . $MSG['multi_lang_' . $l][$l] . '</a></td>';

			$out = implode('<td class="fontTools" align="right" nowrap="nowrap"><div class="divide"></div></td>', $tmp);
			$myTemplate->add_replacement('<%AvailableLang%>', '<table><tr>' . $out . '</tr></table>');
		}
		else
		{
			$myTemplate->add_replacement('<%AvailableLang%>', '');
		}

		genDefaultTrans($myTemplate);
		$head_content = $myTemplate->get_result(false);
		return $head_content;
	}

?>
