<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	 *                                                                                                *
	 *      Programmer: Wiseguy Liang                                                                 *
	 *      Creation  : 2003/09/25                                                                    *
	 *      work for  :                                                                               *
	 *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	 *                                                                                                *
	 **************************************************************************************************/

	ignore_user_abort(true);
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	if (eregi('^[0-9A-Z_.-]+$', $_GET['activity_id']))
	{
		if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA']))
		{
			$data = $sysConn->qstr(stripslashes($GLOBALS['HTTP_RAW_POST_DATA']));
			dbSet('WM_scorm_cmi',
				  'cmi_data=' . $data,
				  sprintf('course_id=%u and username="%s" and sco_id="%s"',
				  		  $sysSession->course_id, $sysSession->username, $_GET['activity_id']
				  		 )
				 );
			if ($sysConn->ErrorNo() == 0)
				if ($sysConn->Affected_Rows() < 1)
				{
					dbNew('WM_scorm_cmi',
						  'course_id,username,sco_id,cmi_data',
						  sprintf('%u,"%s","%s",%s',
						  		  $sysSession->course_id,
						  		  $sysSession->username,
						  		  $_GET['activity_id'],
						  		  $data
						  		 )
						 );
					die (($sysConn->ErrorNo() == 0 && $sysConn->Affected_Rows() == 1) ? '<errorlevel>0</errorlevel>' : '<errorlevel>2</errorlevel>');
				}
				else
					die('<errorlevel>0</errorlevel>');
			else
				die(sprintf('<errorlevel>%d - %s</errorlevel>', $sysConn->ErrorNo(), $sysConn->ErrorMsg()));
		}
	}
	die('<errorlevel>1</errorlevel>');
?>
