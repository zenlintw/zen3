<?php
	/**
	 * ��ƾ�@�Ψ��
	 *
	 * @since   2004/09/02
	 * @author  ShenTing Lin
	 * @version $Id: mod_calendar_lib.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	/**
	 * ���o��ƾ䪺�O��
	 * @param string  $username : �b���A�w�]�� $sysSession->username
	 * @param integer $year  : �~�A�nŪ�����@�~���O��
	 * @param integer $month : ��A�nŪ�����@�몺�O��
	 * @param integer $day   : ��A�nŪ�����@�骺�O��
	 *     $year�B$month �P $day �u�n�䤤�@�����s�ɡA�h�����Ӹ��
	 *     �� $year�B$month �P $day �Ҭ��s�ɡA�hŪ���ӤH�Ҧ�����ƾ�O��
	 * @param string  $type  : ��ƾ䪺����
	 * @return array $res : ���G
	 **/
	if (!function_exists('getCaleMemo'))
	{
		function getCaleMemo($username='', $year=0, $month=0, $day=0, $type='person') {
			global $sysSession;

			$username = trim($username);
			if (empty($username)) $username = $sysSession->username;
			$year  = intval($year);
			$month = intval($month);
			$day   = intval($day);
			$table = 'WM_calendar';
			$field = 'DATE_FORMAT(`memo_date`, "%Y_%m_%d") AS mdate, `ishtml`, `subject`, `content`';
			$where = "`username`='{$username}' AND `type`='{$type}'";
			if ($year  != 0) $where .= ' AND YEAR(`memo_date`)='  . $year;
			if ($month != 0) $where .= ' AND MONTH(`memo_date`)=' . $month;
			if ($day   != 0) $where .= ' AND DATE_FORMAT(`memo_date`, "%e")=' . $day;
			$RS = dbGetStMr('WM_calendar', $field, $where, ADODB_FETCH_ASSOC);
			$res = array();
			if ($RS) {
				while (!$RS->EOF) {
					$res[$RS->fields['mdate']][] = $RS->fields;
					$RS->MoveNext();
				}
			}
			return $res;
		}
	}

	if (strpos($_SERVER['PHP_SELF'], '/learn/mycourse/') !== false)
	{
	$isEdit = ($sysSession->username != 'guest');
	$wd = $defSize - 10;   // �D�n�����j�p���]�w
	$id = showXHTML_mytitle_B($id, $title_caption, $wd, $isEdit);
		showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="0" class="cssTable" id="tab_' . $id . '"');
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td_B('nowrap="nowrap"');
					$days = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
					$str  = date('Y-n-j', time());
					$date = explode('-', $str);
					$time = mktime(0, 0, 0, $date[1], 1, $date[0]);
					$wk   = date('w', $time);
					showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="1"');
						showXHTML_tr_B('class="cssTrHead"');
							$msg = sprintf('%d' . $MSG['mod_cale_year'][$sysSession->lang] . '%02d' . $MSG['mod_cale_month'][$sysSession->lang], $date[0], $date[1]);
							showXHTML_td('align="center" colspan="9"', $msg);
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="center" width="5%"', '&nbsp;');
							showXHTML_td('align="center" width="13%" class="cssCaleFont01"', $MSG['short_sunday'][$sysSession->lang]);
							showXHTML_td('align="center" width="13%" class="cssCaleFont03"', $MSG['short_monday'][$sysSession->lang]);
							showXHTML_td('align="center" width="13%" class="cssCaleFont03"', $MSG['short_tuesday'][$sysSession->lang]);
							showXHTML_td('align="center" width="13%" class="cssCaleFont03"', $MSG['short_wednesday'][$sysSession->lang]);
							showXHTML_td('align="center" width="13%" class="cssCaleFont03"', $MSG['short_thursday'][$sysSession->lang]);
							showXHTML_td('align="center" width="13%" class="cssCaleFont03"', $MSG['short_friday'][$sysSession->lang]);
							showXHTML_td('align="center" width="13%" class="cssCaleFont02"', $MSG['short_saturday'][$sysSession->lang]);
							showXHTML_td('align="center" width="4%"', '&nbsp;');
						showXHTML_tr_E();

						if (intval($date[1]) == 2) {
							if (( (($date[0] % 4) == 0) && (($date[0] % 100) != 0) ) || (($date[0] % 400) == 0))
								$days[2] = 29;
							else
								$days[2] = 28;
						}
						$aryEvnt = getCaleMemo($get_who_memo, $date[0], $date[1], 0, $get_type);

						$wk = 0 - intval($wk);
						$end = intval($days[$date[1]]) - $wk;
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="center"', '&nbsp;');
							$sary = array_keys($aryEvnt);
							for ($i = 1, $j = 0; $i <= $end; $i++) {
								$str = ($wk++ >= 0) ? ++$j : '&nbsp;';
								// �B�z���O�ƪ���l (Begin)
								$ary = array();
								$idx = sprintf('%04d_%02d_%02d', $date[0], $date[1], $wk);
								if (isset($aryEvnt[$idx])) {
									$k = 0;
									foreach ($aryEvnt[$idx] as $val) {
										if (is_array($val)) {
											$ary[] = ++$k . '. '. trim(htmlspecialchars($val['subject']));
										}
									}
									$isEvnt = true;
								} else {
									$isEvnt = false;
								}
								$title = ' title="' . implode("\n", $ary) . '"';
								// �B�z���O�ƪ���l (End)
								// �����r���C��P�I���� (Begin)
								switch ($i % 7) {
									case 0 : // �P����
										$css = 'cssCaleFont02';
										break;
									case 1 : // �P����
										$css = 'cssCaleFont01';
										break;
									default:
										$css = '';
								}
								if (($date[2] == $wk) && $isEvnt) {
									$css = 'cssCaleFont04';
								} else if ($date[2] == $wk) {
									$css .= ' cssCaleBg01';
								} else if ($isEvnt) {
									$css .= ' cssCaleBg02';
								}
								$css = ' class="' . $css . '"';
								// �����r���C��P�I���� (End)
								showXHTML_td('align="center"' . $css . $title, $str);

								if (($i != 0) && (($i % 7) == 0)) {
										showXHTML_td('align="center"', '&nbsp;');
									showXHTML_tr_E();
									showXHTML_tr_B('class="cssTrEvn"');
										showXHTML_td('align="center"', '&nbsp;');
								}
							}
							// �ɨ��ť� (Begin)
							for ($k = ($i - 1) % 7; $k < 7; $k++) {
								showXHTML_td('align="center"', '&nbsp;');
							}
							showXHTML_td('align="center"', '&nbsp;');
							// �ɨ��ť� (End)
						showXHTML_tr_E();
					showXHTML_table_E();
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
		$msg = ($isEdit) ? $MSG['msg_reposition_here'][$sysSession->lang] : '&nbsp;';
		showXHTML_mytitle_postit($id, $msg);
	showXHTML_mytitle_E();

	$js = <<< BOF
	// �Y�n resize�A�h function name ������ mod_{id}_resize
	function mod_{$id}_resize() {
		if (dragID != "{$id}") return false;
	}
BOF;
	showXHTML_script('inline', $js);
	}
?>
