<?php
   /**
     * �έp����/�p��ʪ��禡�w
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
     * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
     * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
     *
     * @package     WM3
     * @author      jeff <jeff@sun.net.tw>
     * @copyright   2000-2005 SunNet Tech. INC.
     * @version     CVS: $Id: lib_statistics.php,v 1.1 2010/02/24 02:39:34 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2005-12-06
     */
   
// {{{ ��ƫŧi begin

	/**
     * �N���w������ӤH�\Ū�ɶ��O�J�ǲ߰O��
     *
     * @param string $thatday  ���w����r��
     * @param string $user �ϥΪ�
     * @return boolean ���\OR����
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
// }}} ��ƫŧi end
   
?>
