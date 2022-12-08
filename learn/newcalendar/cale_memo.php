<?php
	/**
	 * 取得行事曆
	 *
	 * 建立日期：2003//
	 * @author  ShenTing Lin
	 * @version $Id: cale_memo.php,v 1.1 2010/02/24 02:39:04 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_calendar.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/lang/calendar.php');
    //$sysConn->debug=true;
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		// 檢查 Ticket
		$ticket = md5($sysSession->username . 'newCalendar' . $sysSession->ticket . $sysSession->school_id);
		if ($_POST['ticket'] != $ticket) {
            die($MSG['access_deny'][$sysSession->lang]);
        }
		switch($_POST['calEnv'])
		{
			case 'academic': $interface = 'school'; break;
			case 'teach':  	 $interface = 'course'; break;
			case 'learn':	 $interface = 'person';  break;
		}

		$result = '';
        $startDate     = $_POST['start'];
        $endDate    = $_POST['end'];
        $day      = $_POST['day'];
		switch ($_POST['action']) {
			case 'month' :
            case 'week'  :
                switch($interface){
                    case 'person':
                        $type=$_POST['type'];
                        if($type=="person") $rs=getPersonNewCalendar($startDate,$endDate);
                        if($type=="course") $rs=getMyCourseNewCalendar($startDate, $endDate);
                        if($type=="school") $rs=getSchoolNewCalendar($startDate, $endDate);
                    break;
                    case 'course':	// 課程行事曆介面
                        $rs=getCourseNewCalendar($startDate, $endDate);
                    break;
                    case 'school':	// 學校行事曆介面
                        $rs=getSchoolNewCalendar($startDate, $endDate);
                    break;
                }
                echo json_encode($rs);
                break;
			case 'day'   :
                $type=$_POST['type'];
                $rs=getMyDayNewCalendar($day,$interface,$type);
                echo json_encode($rs);
                break;
            case 'setting'   :
                $rs=setNewCalendarSetting($_POST['type']);
                break;

		}
	}
?>
