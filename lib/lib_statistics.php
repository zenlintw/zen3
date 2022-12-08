<?php
   /**
     * 統計報表/計算性的函式庫
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      jeff <jeff@sun.net.tw>
     * @copyright   2000-2005 SunNet Tech. INC.
     * @version     CVS: $Id: lib_statistics.php,v 1.1 2010/02/24 02:39:34 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2005-12-06
     */
   
// {{{ 函數宣告 begin

	/**
     * 將指定日期的個人閱讀時間記入學習記錄
     *
     * @param string $thatday  指定日期字串
     * @param string $user 使用者
     * @return boolean 成功OR失敗
     */
	function setPersonalRecrd($thatday, $user)
	{
		global $sysConn;
		$stime = sprintf("%s 00:00:00",$thatday);
		$etime = sprintf("%s 23:59:59",$thatday);
		$sqls = 'replace into WM_record_daily_personal (username,course_id,thatday,reading_seconds) ' .
				"select username,course_id,'$thatday',sum(unix_timestamp(over_time)-unix_timestamp(begin_time)+1) " .
				'from WM_record_reading ' .
				"where username='{$user}' and over_time >= '$stime' and over_time < '$etime' " .
				'group by username,course_id';
        chkSchoolId('WM_record_daily_personal');
		return $sysConn->Execute($sqls);
	}
// }}} 函數宣告 end
   
?>
