<?
	/**
	 * �~ - �g �� �~-��-��
	 * @author  Jeff Wang
	 * @version $Id: Week2YearMonthDay.php,v 1.1 2010/02/24 02:39:32 saly Exp $:
	 * @copyright 2003 SUNNET
	 **/
	/*
	 * �~ - �g �� �~-��-��
	 * Week2YearMonthDay
	 * para:
	 * $year => �~
	 * $week => �g
	 */
	function Week2YearMonthDay($year, $week)
	{
		$w = date("w",mktime (0,0,0,1,1,$year));

		$baseWeek = ($w == 0) ? 1 : 0;
		$days = ($week - $baseWeek) * 7;
		if ($w != 0) $days = $days - $w;

		$arr[0] = strtotime("+{$days} day", mktime(0,0,0,1,1,$year));
		for($i = 1; $i <= 6; $i++)
		{
			$arr[$i] = strtotime("+{$i} day", $arr[0]);
		}
		return $arr;

	}

?>