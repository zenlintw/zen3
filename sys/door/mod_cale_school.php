<?php
	/**
	 * 校務行事曆
	 *
	 *     所需樣板名稱：cale_school.htm
	 *
	 * @since   2004/10/27
	 * @author  ShenTing Lin
	 * @version $Id: mod_cale_school.php,v 1.1 2010/02/24 02:40:20 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/learn/mycourse/modules/mod_calendar_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '2300300400';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	function mod_cale_school() {
		global $sysConn, $sysSession, $MSG, $lang;
		$ly = array();
		$days = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		$str = date('Y-n-j', time());
		$date = explode('-', $str);
		if (intval($date[1]) == 2) {
			if (( (($date[0] % 4) == 0) && (($date[0] % 100) != 0) ) || (($date[0] % 400) == 0))
				$days[2] = 29;
			else
				$days[2] = 28;
		}
		$aryEvnt = getCaleMemo($sysSession->school_id, $date[0], $date[1], 0, 'school');
		$time = mktime(0, 0, 0, $date[1], 1, $date[0]);
		$wk = date('w', $time);   // 取得當月第一天是星期幾
		$end = intval($days[$date[1]]) + $wk;   // 當月的天數加上需要網後移動的天數
		$wk = 0 - intval($wk);
		$sary = array_keys($aryEvnt);
		$r = 0;
		for ($i = 1, $j = 0; $i <= $end; $i++) {
			$day = ($wk++ >= 0) ? ++$j : '&nbsp;';   // 幾日

			// 處理有記事的日子 (Begin)
			$ary = array();
			$idx = sprintf('%04d_%02d_%02d', $date[0], $date[1], $wk);
			if (isset($aryEvnt[$idx])) {
				foreach ($aryEvnt[$idx] as $val) {
					if (is_array($val)) {
						$ary[] = trim(htmlspecialchars($val['subject']));
					}
				}
			}

			$cnt = count($ary);
			$isE = ($cnt > 0);
			$tmp = array();
			$style = '';
			if ($isE) {
				$k   = 0;
				$tmp[] = sprintf($MSG['msg_school_cale_title'][$sysSession->lang], $cnt);
				foreach ($ary as $val) {
					// $tmp[] = (($cnt > 1) ? (++$k . '. ') : '') . $val;
					$tmp[] = ++$k . '. ' . $val;
				}
				$style = 'cursor: pointer; cursor: hand;';
			}
			$title = implode("\n", $tmp);
			// 處理有記事的日子 (End)

			// 切換字型顏色與背景色 (Begin)
			switch ($i % 7) {
				case 0 : // 星期六
					$css = 'cssCaleFont02';
					break;
				case 1 : // 星期天
					$css = 'cssCaleFont01';
					break;
				default:
					$css = '';
			}

			if (($date[2] == $wk) && $isE) {
				$css = 'cssCaleFont04';
			} else if ($date[2] == $wk) {
				$css .= ' cssCaleBg01';
			} else if ($isE) {
				$css .= ' cssCaleBg02';
			}
			// 切換字型顏色與背景色 (End)
			$ly[$r][] = array($css, $style, $title, $day);
			if (($i != 0) && (($i % 7) == 0)) $r++;
		}
		// 補足空白 (Begin)
		for ($k = ($i - 1) % 7; $k < 7; $k++) {
			$ly[$r][] = array('&nbsp;', '', '', '');
		}
		$cont_cale_item = '';
		$tpl = '<td align="center" class="%s" style="%s" title="%s">%s</td>';
		foreach ($ly as $d) {
			$cont_cale_item .= "\n" . '<tr class="cssTrEvn">';
			$cont_cale_item .= '<td>&nbsp;</td>';
			foreach ($d as $val) {
				$cont_cale_item .= vsprintf($tpl, $val);
			}
			$cont_cale_item .= '<td>&nbsp;</td>';
			$cont_cale_item .= '</tr>' . "\n";
		}

		$tpl = getTemplate('cale_school.htm');
		$myTemplate = new Wise_Template($tpl);
		$msg = sprintf($MSG['mod_cale_year_month'][$sysSession->lang], $date[0], $date[1]);
		$myTemplate->add_replacement('<%CALE_SCHOOL_TITLE%>', $MSG['cale_school_title'][$sysSession->lang]);
		$myTemplate->add_replacement('<%MSG_YEAR_MONTH%>'   , $msg);
		$myTemplate->add_replacement('<%S_SUN%>'            , $MSG['short_sunday'][$sysSession->lang]);
		$myTemplate->add_replacement('<%S_MON%>'            , $MSG['short_monday'][$sysSession->lang]);
		$myTemplate->add_replacement('<%S_TUE%>'            , $MSG['short_tuesday'][$sysSession->lang]);
		$myTemplate->add_replacement('<%S_WED%>'            , $MSG['short_wednesday'][$sysSession->lang]);
		$myTemplate->add_replacement('<%S_THU%>'            , $MSG['short_thursday'][$sysSession->lang]);
		$myTemplate->add_replacement('<%S_FRI%>'            , $MSG['short_friday'][$sysSession->lang]);
		$myTemplate->add_replacement('<%S_SAT%>'            , $MSG['short_saturday'][$sysSession->lang]);
		$myTemplate->add_replacement('<%CALE_ITEM%>'        , $cont_cale_item);
		genDefaultTrans($myTemplate);
		return $myTemplate->get_result(false);
	}

?>
