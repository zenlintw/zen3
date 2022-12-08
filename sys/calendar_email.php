<?php
	/**
	 * 行事曆寄發 EMail 提醒(Alert)函式庫
	 *
	 * 建立日期：2004/4/1
	 * @author  KuoYang Tsao
	 * @version $Id: calendar_email.php
	 * @copyright 2004 SUNNET
	 **/
	// require_once(sysDocumentRoot . '/config/sys_config.php');
	require_once(sysDocumentRoot . '/lib/mime_mail.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lang/calendar_email.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$lang     = '';
	$url_str  = 'URL: <a target="_blank" href="http://%s">%s</a>';

/*************************************
	副程式群開始
 *************************************/
	/**
	 * 將 mail 的標題編碼
	 * @param string $subject : 標題
	 * @param string $charset : 字集
	 * @return string : 編碼後的標題
	 **/
	function mailEncSubject($subject='', $charset='utf-8') {
		if (empty($subject)) return false;
		$cset = strtolower($charset);
		$str  = '=?' . $cset . '?B?' . base64_encode($subject) . '?=';
		return $str;
	}
	
	/**
	 * 將 mail 的標題編碼
	 * @param string $from    : 顯示的名稱
	 * @param string $email   : Email
	 * @param string $charset : 字集
	 * @return string : 編碼後的 from
	 **/
	function mailEncFrom($from='', $email='', $charset='utf-8') {
		if (empty($email)) return false;
		if (empty($from)) return $email;

		$cset = strtolower($charset);
		$str  = '=?' . $cset . '?B?' . base64_encode($from) . '?= <' . $email . '>';
		return $str;
	}

	function each_base64dec(&$element){
		if (is_array($element))
			foreach($element as $k => $v) $element[$k] = each_base64dec($v);
		elseif(is_scalar($element)){
			return base64_decode($element);
		}
	}

	/**
	 * 取得個人的行事曆設定值(要不要顯示其他行事曆)
	 **/
	function GetCalendarSetting($username) {
		global $sysConn;
        //目前設定成都要顯示
        return array('course','school');
		$sql = "select also_show from WM_cal_setting where username='$username'";
		$_mySetting = $sysConn->GetOne($sql);

		return explode("," , $_mySetting);
	}


	/**
	 * 回圈列出行事曆內容
	 *	( 為以下Get XXXX Alert 而設 )
	 */
	function LoopMemoRS($RS, &$cnt,&$str,&$tempAr,$base64_conv=false) {
		global $MSG,$lang;
		$ok = true;
		if(!$RS)
			return;
		while (!$RS->EOF) {
			$id = IntVal($RS->fields['idx']);
			if(isset($tempAr)) {
				if(in_array($id, $tempAr)) {	// 此筆已經列過
					$ok = false;		        // 標示為不再列出
				} else {			            // 若未列過
					$ok = true;		            // 則標示為要列
					$tempAr[] = $id;	        // 並記入陣列中
				}
			}
			if($ok) {
				$cnt++;

				if($base64_conv) {
					$langstr = getCaption($RS->fields['caption']);
					$caption = $langstr[$lang];
				} else {
					$caption = isset($RS->fields['caption'])?$RS->fields['caption']:'&nbsp;';
				}
				$type = $RS->fields['ishtml'];
				$content = ($type && $type == 'text') ? htmlspecialchars($RS->fields['content']) : $RS->fields['content'];
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$str .= '<tr ' . $col . '><td>' . $caption . '</td>' .
					'<td>' . $RS->fields['memo_date'] . '</td>' .
					'<td>' ;
				if (empty($RS->fields['time_begin'])&&empty($RS->fields['time_end'])) {
					$str .= $MSG['allday'][$lang];
				} else {
					if (!empty($RS->fields['time_begin'])&&empty($RS->fields['time_end'])){
						$str .= date('H:i', strtotime($RS->fields['time_begin'])). $MSG['start'][$lang];
					} else if (empty($RS->fields['time_begin'])&&!empty($RS->fields['time_end'])) {
						$str .= date('H:i', strtotime($RS->fields['time_end'])). $MSG['end'][$lang];
					} else {
						$str .= date('H:i', strtotime($RS->fields['time_begin'])) .' ~ '. date('H:i', strtotime($RS->fields['time_end'])); 
					}
				}
				
				$str .=	'</td><td>' . htmlspecialchars($RS->fields['subject'])   . '</td>' .
					'<td>' . $content                 . '</td>' .
					'</tr>';
			}
			$RS->MoveNext();
		}
	}

	/**
	 * 取得個人的行事曆
	 * @parm string $username : 使用者名稱( 個人 )
	 **/
	function GetPersonAlert($username) {
		global $sysConn,$MSG,$lang;
		// 取個人行事曆
		$sql = "select c.* from WM_calendar c " .
			   "where FIND_IN_SET('email',c.alert_type) AND c.username='{$username}' and c.type='person' " .
			   "AND (CURRENT_DATE between (c.memo_date - INTERVAL c.alert_before DAY) and c.memo_date) order by c.memo_date, c.time_begin";
		$RS = $sysConn->Execute($sql);
		$cnt=0;
		$str = '';
		$tempArr = null;
		LoopMemoRS($RS, $cnt, $str, $tempArr);

		if($cnt > 0)
			$mlstr = "<tr class=\"cssTrHelp\"><td colspan='5'>".$MSG['cal_per'][$lang].'('.$cnt.$MSG['cal_num'][$lang].')'."</td></tr>{$str}";
		else
			$mlstr = '';

		return $mlstr;
	}

	/**
	 * 取得個人課程的行事曆
	 * @parm string $username : 使用者名稱( 個人 )
	 **/
	function GetCourseAlert($username, $settings) {
		global $sysConn,$MSG,$lang,$sysRoles;
		$tempArr = array();	// 避免重複列課程用( 詳見 LoopMemoRS() )
		$cnt = 0;
		$str = '';
		if(in_array('course', $settings)) {	// 學員設定要顯示課程行事曆
			// 取個人選課之課程行事曆
			$sql = "select c.*,m.caption from WM_calendar c INNER JOIN WM_term_major t " .
                   "ON c.username=t.course_id AND t.role >= 1 INNER JOIN WM_term_course m " .
				   "ON t.course_id=m.course_id " .
				   "where FIND_IN_SET('email',c.alert_type) AND t.username='{$username}' and c.type='course' " .
				   "AND (CURRENT_DATE between (c.memo_date - INTERVAL c.alert_before DAY) and c.memo_date) order by c.memo_date, c.time_begin";
			$RS = $sysConn->Execute($sql);
			LoopMemoRS($RS, $cnt, $str, $tempArr, true);
		}

		// 取個人任課之課程行事曆
		$sql = "select c.*,m.caption from WM_calendar c INNER JOIN WM_term_major t " .
			   "ON c.username=t.course_id AND t.username='{$username}' and t.role&" .
			   ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) .
			   ' INNER JOIN WM_term_course m ON t.course_id=m.course_id ' .
			   "where c.type='course' and FIND_IN_SET('email',c.alert_type) " .
			   "AND (CURRENT_DATE between (c.memo_date - INTERVAL c.alert_before DAY) and c.memo_date) order by c.memo_date, c.time_begin";
		$RS = $sysConn->Execute($sql);
		LoopMemoRS($RS, $cnt, $str, $tempArr, true);

		if($cnt > 0)
			$mlstr = "<tr class=\"cssTrHelp\"><td colspan='5'>".$MSG['cal_cou'][$lang].'('.$cnt.$MSG['cal_num'][$lang].')'."</td></tr>{$str}";
		else
			$mlstr = '';

		return $mlstr;
	}

	/**
	 * 取得學校的行事曆
	 * @parm string $username : 使用者名稱( 個人 )
	 **/
	function GetSchoolAlert($school_id,$username,$settings) {
		global $sysConn,$Managers,$MSG,$lang;
		$cnt = 0;
		$str = '';
		$tempArr = null;
		if(in_array('school', $settings) || in_array($username,$Managers)) {	// 學員設定要顯示學校行事曆或是管理者
			// 取學校行事曆
			$sql = "select c.* from WM_calendar c " .
				   "where FIND_IN_SET('email',c.alert_type) AND c.username='{$school_id}' and c.type='school' " .
				   "AND (CURRENT_DATE between (c.memo_date - INTERVAL c.alert_before DAY) and c.memo_date) order by c.memo_date, c.time_begin";
			$RS = $sysConn->CacheExecute($sql);
			LoopMemoRS($RS, $cnt, $str, $tempArr);
		}
		if($cnt > 0)
			$mlstr = "<tr class=\"cssTrHelp\"><td colspan='5'>".$MSG['cal_sch'][$lang].'('.$cnt.$MSG['cal_num'][$lang].')'."</td></tr>{$str}";
		else
			$mlstr = '';

		return $mlstr;
	}

/**
	 * 取得學校的行事曆for管理者
	 * @parm string $username : 使用者名稱( 個人 )
	 **/
	function GetMgrSchoolAlert() {
		global $sysConn,$Managers,$school_id,$school_name,$MSG,$lang;
		$cnt = 0;
		$str = '';
		$tempArr = null;
		// 取學校行事曆
		$sql = "select c.* from WM_calendar c " .
			   "where FIND_IN_SET('email',c.alert_type) AND c.username='{$school_id}' and c.type='school' " .
			   "AND (CURRENT_DATE between (c.memo_date - INTERVAL c.alert_before DAY) and c.memo_date) order by c.memo_date, c.time_begin";
		$RS = $sysConn->CacheExecute($sql);
		LoopMemoRS($RS, $cnt, $str, $tempArr);

		if($cnt > 0) {
			$_name    = $MSG['name'][$lang];
			$_date    = $MSG['date'][$lang];
			$_time    = $MSG['time'][$lang];
			$_subject = $MSG['subject'][$lang];
			$mlstr = "<tr class=\"cssTrHelp\"><td colspan='5'>".$MSG['cal_sch_1'][$lang]."($school_name)".$MSG['cal_sch_2'][$lang].'('.$cnt.$MSG['cal_num'][$lang].')'."</td></tr>{$str}";
		}
		else
			$mlstr = '';

		return $mlstr;
	}

	/**********************
	 * 取得個人 EMail
	 * @parm $location : MASTER or $school_id
	 * @parm $username : user id
	 **********************/
	function GetEMail($location, $username) {
		global $sysConn, $school_id;
		$sysConn->Execute('use ' . sysDBprefix . $location);
		if($location=='MASTER')
			$sql = "select email from WM_all_account where username='$username'";
		else
			$sql = "select email from WM_user_account where username='$username'";
		return $sysConn->GetOne($sql);
	}
	/**********************
	 * 取得個人語系
	 * @parm $location : MASTER or $school_id
	 * @parm $username : user id
	 **********************/
	function GetLanguage($location,$username) {
		global $sysConn;
		$sysConn->Execute('use ' . sysDBprefix . $location);
		if($location=='MASTER')
			$sql = "select language from WM_all_account where username='$username'";
		else
			$sql = "select language from WM_user_account where username='$username'";
		return $sysConn->GetOne($sql);
	}

	function GetAllUserInCalendar($school_id, &$Users, &$Managers) {
		global $sysConn, $sysRoles;
		
		if(!is_array($Users))	 $Users    = array();
		if(!is_array($Managers)) $Managers = array();

   		$sysConn->Execute('use ' . sysDBprefix . $school_id);
		// 1 從個人部分取得 user
		$sql = "select c.username,1 from WM_calendar c " .
			   "where c.type='person' and FIND_IN_SET('email',c.alert_type) and " .
			   "(CURRENT_DATE between (c.memo_date - INTERVAL c.alert_before DAY) and c.memo_date)"; // and c.username='tsaoyang'";
        $u = $sysConn->GetAssoc($sql);

		// 2.1 從選課部分取得課程 user
		$sql = "select t.username,1 ".
			   "from WM_calendar c INNER JOIN WM_term_major t ON c.username=t.course_id " .
               'AND t.role >= 1 ' .
			   //"LEFT JOIN WM_cal_setting s ON t.username=s.username " .
			   "where c.type='course' and FIND_IN_SET('email',c.alert_type) " .
			   "and (CURRENT_DATE between (c.memo_date - INTERVAL c.alert_before DAY) and c.memo_date) ";
			   //"and ( s.also_show is NULL or FIND_IN_SET('course',s.also_show))";
        $u += $sysConn->GetAssoc($sql);

		// 2.2 從任課部分取得課程 user
		$sql = "select t.username,1 " .
			   "from WM_calendar c INNER JOIN WM_term_major t ON c.username=t.course_id " .
			   'and t.role&' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) .
			   " where c.type='course' and FIND_IN_SET('email',c.alert_type) " .
			   "and (CURRENT_DATE between (c.memo_date - INTERVAL c.alert_before DAY) and c.memo_date)";
		$u += $sysConn->GetAssoc($sql);

		// 3.2 從班導部分取得班級導師 user
		$sql = "select t.username,1 " .
			   "from WM_calendar c INNER JOIN WM_class_member t ON c.username=t.class_id " .
			   "and t.role&" . ($sysRoles['director'] | $sysRoles['assistant']) .
			   " where c.type='class' and FIND_IN_SET('email',c.alert_type) " .
			   "and (CURRENT_DATE between (c.memo_date - INTERVAL c.alert_before DAY) and c.memo_date)";
		$u += $sysConn->GetAssoc($sql);

		// 4 從學校部分取得學校 user
		$sql = "select t.username,1 " .
			   "from WM_calendar c INNER JOIN WM_user_account t ON c.username='{$school_id}' " .
			   //"INNER JOIN WM_cal_setting s ON t.username=s.username " .
			   "where c.type='school' and FIND_IN_SET('email',c.alert_type) " .
			   //"and FIND_IN_SET('school',s.also_show) " .
			   "and (CURRENT_DATE between (c.memo_date - INTERVAL c.alert_before DAY) and c.memo_date)";
        $u += $sysConn->GetAssoc($sql);

		$Users = array_keys($u); unset($u);

		$sysConn->Execute('use ' . sysDBname);
		// 從學校部分取得管理者
		$sql = "select distinct t.username from WM_manager t where t.school_id='$school_id'";
		$Managers = $sysConn->GetCol($sql);
	}

	/**
	 *	送出我的行事曆EMail之提醒
	 * @return
	 **/
	function SendCalEmailAlert($v, $key, $school_id) {
		global $sysConn,$school_name,$MSG,$lang, $school_host, $school_mail, $url_str;
		$MySettings = GetCalendarSetting($v);
		$lang     = GetLanguage($school_id, $v);
		$_name    = $MSG['name'][$lang];
		$_date    = $MSG['date'][$lang];
		$_time    = $MSG['time'][$lang];
		$_subject = $MSG['subject'][$lang];
		$_content = $MSG['content'][$lang];
		$_url     = sprintf($url_str, $school_host, $school_host);
		$_from    = sprintf($MSG['from_calendar'][$lang], $school_name);
		$str  = <<< BOF
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style media="screen" type="text/css">
.cssTable {
	background-color: #E3E9F2;
	border: 1px solid #5176D2;
}

.cssTrHelp {
	font-size: 12px;
	line-height: 16px;
	text-decoration: none;
	letter-spacing: 2px;
	color: #000000;
	background-color: #C7D8FA;
	font-family: "Tahoma", "PMingliu", "MingLiU", "Times New Roman", "Times", "serif";
}

.cssTrHead {
	font-size: 12px;
	line-height: 16px;
	text-decoration: none;
	letter-spacing: 2px;
	color: #000000;
	background-color: #C7D8FA;
	font-family: "Tahoma", "PMingliu", "MingLiU", "Times New Roman", "Times", "serif";
}

.cssTrEvn {
	font-size: 12px;
	line-height: 16px;
	text-decoration: none;
	letter-spacing: 2px;
	color: #000000;
	background-color: #FFFFFF;
	font-family: "Tahoma", "PMingliu", "MingLiU", "Times New Roman", "Times", "serif";
}

.cssTrOdd {
	font-size: 12px;
	line-height: 16px;
	text-decoration: none;
	letter-spacing: 2px;
	color: #000000;
	background-color: #ECF1F7;
	font-family: "Tahoma", "PMingliu", "MingLiU", "Times New Roman", "Times", "serif";
}
</style>
</head>
<body>
	<span class="cssTrEvn">{$_from}<span><br /><br />
	<table border="0" cellspacing="1" cellpadding="2" width="100%" class="cssTable"><tr class="cssTrHead"><td align="center" width='15%'>{$_name}</td><td align="center" width='10%'>{$_date}</td><td align="center" width='15%'>{$_time}</td><td align="center">{$_subject}</td><td align="center">{$_content}</td></tr>
BOF;
		$str1  = GetPersonAlert($v);
		$str1 .= GetCourseAlert($v, $MySettings);
		//$str1 .= GetClassAlert($v, $MySettings);
		$str1 .= GetSchoolAlert($school_id, $v, $MySettings);
		$str2  = '</table></body><br /><span class="cssTrEvn">' . $_url . '<span></html>';
		if (!empty($str1))
		{
			$email= GetEMail( $school_id, $v );
			$mail = new mime_mail;
			$mail->subject = mailEncSubject($school_name . $MSG['calendar_alert'][$lang], 'utf-8');
			$mail->body    = $str.$str1.$str2;
			$mail->body_type='text/html';
			$mail->charset = 'utf-8'; //$school_lang;
			$mail->from    = mailEncFrom($school_name, $school_mail, 'utf-8');
			$mail->reply   = mailEncFrom($school_name, $school_mail, 'utf-8');
			$mail->to      = $email;
			$mail->send();
//echo "school_id=>{$school_id} / v=>{$v} / email=>{$email}<br>";
		}

	}

	/**
	 *  送出學校行事曆給管理員EMail之提醒
	 * @return
	 **/
	function SendSchMgrCalEmailAlert($v, $key, $mlstr) {
		global $sysConn,$school_name,$MSG,$lang,$school_host, $school_mail, $url_str;
		$_name    = $MSG['name'][$lang];
		$_date    = $MSG['date'][$lang];
		$_time    = $MSG['time'][$lang];
		$_subject = $MSG['subject'][$lang];
		$_content = $MSG['content'][$lang];
		$_url     = sprintf($url_str, $school_host, $school_host);
		$_from    = sprintf($MSG['from_calendar'][$lang], $school_name);
		if($mlstr=='')
			return;
		$str  = <<< BOF
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style media="screen" type="text/css">
.cssTable {
	background-color: #E3E9F2;
	border: 1px solid #5176D2;
}

.cssTrHelp {
	font-size: 12px;
	line-height: 16px;
	text-decoration: none;
	letter-spacing: 2px;
	color: #000000;
	background-color: #C7D8FA;
	font-family: "Tahoma", "PMingliu", "MingLiU", "Times New Roman", "Times", "serif";
}

.cssTrHead {
	font-size: 12px;
	line-height: 16px;
	text-decoration: none;
	letter-spacing: 2px;
	color: #000000;
	background-color: #C7D8FA;
	font-family: "Tahoma", "PMingliu", "MingLiU", "Times New Roman", "Times", "serif";
}

.cssTrEvn {
	font-size: 12px;
	line-height: 16px;
	text-decoration: none;
	letter-spacing: 2px;
	color: #000000;
	background-color: #FFFFFF;
	font-family: "Tahoma", "PMingliu", "MingLiU", "Times New Roman", "Times", "serif";
}

.cssTrOdd {
	font-size: 12px;
	line-height: 16px;
	text-decoration: none;
	letter-spacing: 2px;
	color: #000000;
	background-color: #ECF1F7;
	font-family: "Tahoma", "PMingliu", "MingLiU", "Times New Roman", "Times", "serif";
}
</style>
</head>
<body>
	<span class="cssTrEvn">{$_from}<span><br /><br />
	<table border="0" cellspacing="1" cellpadding="2" width="100%" class="cssTable"><tr class="cssTrHead"><td align="center" width='15%'>{$_name}</td><td align="center" width='10%'>{$_date}</td><td align="center" width='15%'>{$_time}</td><td align="center">{$_subject}</td><td align="center">{$_content}</td></tr>
{$mlstr}</table>
<br /><span class="cssTrEvn">{$_url}<span>
</body></html>
BOF;

		$email= GetEMail( 'MASTER',$v );
		$mail = new mime_mail;
		$mail->subject = mailEncSubject($school_name . $MSG['calendar_alert'][$lang], 'utf-8');
		$mail->body    = $str;
		$mail->body_type='text/html';
		$mail->charset = 'utf-8'; //$school_lang;
		$mail->from    = mailEncFrom($school_name, $school_mail, 'utf-8');
		$mail->reply   = mailEncFrom($school_name, $school_mail, 'utf-8');
		$mail->to      = $email;
		$mail->send();
//echo "school_id=>{$school_id} / v=>{$v} / email=>{$email}<br>";
	}

/*************************************
	副程式群結束
 *************************************/


/*************************************
	主程式
 *************************************/
	$sysConn->Execute('use ' . sysDBname);
	$sql = "select distinct school_id, school_host from WM_school where `school_host` not like '[delete]%'";
	$RS  = $sysConn->Execute($sql);
	while(!$RS->EOF) {	// 學校迴圈
        $school_host  = $RS->fields['school_host'];
		$school_id    = $RS->fields['school_id'];
		$sysConn->Execute('use ' . sysDBname);
		$RS1          = $sysConn->Execute("select school_name,theme, language, school_host, school_mail from WM_school where school_id='$school_id' and school_host='$school_host'");
		$school_name  = addslashes($RS1->fields['school_name']);
		$school_theme = $RS1->fields['theme'];
		$school_lang  = $RS1->fields['language'];
		$school_host  = $RS1->fields['school_host'];
		$school_mail  = $RS1->fields['school_mail'];

		$Users    = array();
		$Managers = array();
		GetAllUserInCalendar($school_id, $Users, $Managers);

		$sysConn->Execute('use ' . sysDBprefix . $school_id);
		array_walk($Users, 'SendCalEmailAlert', $school_id);

		$Managers = array_diff($Managers, $Users);
		$mlstr    = GetMgrSchoolAlert();

		$sysConn->Execute('use ' . sysDBname);
		if($mlstr!='')
			array_walk($Managers, 'SendSchMgrCalEmailAlert', $mlstr);

		$RS->MoveNext();

	}	// 學校迴圈結束

	// 中斷資料庫連線
	// $sysConn->Disconnect();
?>
