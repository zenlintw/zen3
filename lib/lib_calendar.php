<?php
	/**
	 * ��ƾ� Libraries
	 *
	 * �إߤ���G2004/3/25
	 * @author  Yang
	 * @version $Id: lib_calendar.php,v 1.0
	 * @copyright 2004 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');
    //$sysConn->debug=true;
	/**
	 * ���o�ӤH����ƾ�]�w��(�n���n��ܨ�L��ƾ�)
	 **/
	function LoadMyCalSetting() {
		global $sysSession;

		list($also_show) = dbGetStSr('WM_cal_setting', 'also_show', 'username="' . $sysSession->username . '"', ADODB_FETCH_NUM);
		return isSet($also_show) ? explode(',', $also_show) : Array('person','course','school'); // �w�]����������
	}

	$MyCalendarSettings = LoadMyCalSetting();


/**************************************************************************
 *      ��ƾ���ܳ]�w�Ƶ{���s
 **************************************************************************/

	/**
	 * ���J��ƾ�]�w
	 * @return string $xmlstr : ���o�O�_��ܨ�L��ƾ䪺�]�w
	 **/
	function getCalendarSetting() {
		global $ticket, $MyCalendarSettings, $_mySetting;
		$xmlstr = '<?xml version="1.0" encoding="UTF-8"?><manifest>' .
		          '<ticket>'    . $ticket . '</ticket>' .
		          '<also_show>' . implode(',', $MyCalendarSettings) . '</also_show>' .
		          '</manifest>';
		return $xmlstr;
	}

	/**
	 * �x�s��ƾ�]�w
	 * @return string $xmlstr : �x�s�O�_��ܨ�L��ƾ䪺�]�w
	 **/
	function setCalendarSetting(&$dom) {
		global $sysConn, $sysSession, $ticket, $MyCalendarSettings;

		$also_show   = getNodeValue($dom, 'also_show');
		$login_alert = getNodeValue($dom, 'login_alert');

		$RS = dbSet('WM_cal_setting',"also_show='{$also_show}',login_alert='{$login_alert}'", "username='{$sysSession->username}'");
		if($sysConn->Affected_Rows()==0) {
			$RS = dbNew('WM_cal_setting', 'username,also_show,login_alert', "'{$sysSession->username}','{$also_show}','{$login_alert}'");
		}
		$status = $sysConn->Affected_Rows();

		$MyCalendarSettings = explode(',', $also_show);

		if ($login_alert == 'N'){
			list($alert_num,$alert_date) = dbGetStSr('WM_cal_setting','alert_num,alert_date',"username='{$sysSession->username}'", ADODB_FETCH_NUM);
			if ($alert_num > 0){
				dbSet('WM_cal_setting',"alert_num=0,alert_date=NOW()", "username='{$sysSession->username}'");
			}
		}

		return '<?xml version="1.0" encoding="UTF-8"?><manifest>' .
		       '<ticket>' . $ticket . '</ticket>' .
		       '<status>' . $status . '</status>' .
		       '</manifest>';
	}

    function setNewCalendarSetting($type) {
        global $sysConn, $sysSession, $ticket, $MyCalendarSettings;
        $also_show=implode(",",$type);
        $RS = dbSet('WM_cal_setting',"also_show='{$also_show}'", "username='{$sysSession->username}'");
        if($sysConn->Affected_Rows()==0) {
            $RS = dbNew('WM_cal_setting', 'username,also_show', "'{$sysSession->username}','{$also_show}'");
        }
        $MyCalendarSettings = $type;
    }

/**************************************************************************
 *      ��ƾ���ܳ]�w�Ƶ{���s ����
 **************************************************************************/

/**************************************************************************
 *      ��ƾ�C��M��Ƶ{���s
 **************************************************************************/

	/**
	 * getPersonMonthMemo()
	 *     ���o�ڪ�����ƾ�
	 * @pram string $bdate : �}�l���
	 * @pram string $edate : �������
	 * @pram Array  $retArr: �^�ǰ}�C
	 * @return
	 **/
	function getPersonMonthMemo($bdate,$edate,&$retArr) {
		global $sysSession, $sysConn;
		if(!is_array($retArr))
			return;

		// ���o�ӤH����ƾ�
		$RS = dbGetStMr('WM_calendar', 'count(*) AS cnt, DATE_FORMAT(memo_date, \'%e\') AS day', "username='{$sysSession->username}' AND memo_date>='{$bdate}' AND memo_date<='{$edate}' group by (memo_date)", ADODB_FETCH_ASSOC);
		while (!$RS->EOF) {
			$retArr[$RS->fields['day']] = $RS->fields['cnt'];
			$RS->MoveNext();
		}
	}

	/**
	 * getMyCourseMonthMemo()
	 *     ���o�کҿ�Ҫ�����ƾ�
	 * @pram string $bdate : �}�l���
	 * @pram string $edate : �������
	 * @pram Array  $retArr: �^�ǰ}�C
	 * @return
	 **/
	function getMyCourseMonthMemo($bdate,$edate,&$retArr) {
		global $sysSession, $sysConn, $ADODB_FETCH_MODE;
		if(!is_array($retArr))
			return;

        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        chkSchoolId('WM_calendar');
		// �����ڪ��ҵ{
		$sql = "select count(*) AS cnt, DATE_FORMAT(memo_date, '%e') AS day from WM_calendar c ".
			   "INNER JOIN WM_term_major t ON c.username=t.course_id " .
			   "where t.username='{$sysSession->username}' AND c.memo_date>='{$bdate}' AND c.memo_date<='{$edate}'and c.type='course' group by (c.memo_date)";
		$RS = $sysConn->Execute($sql);
		while (!$RS->EOF) {
			$retArr[$RS->fields['day']] = $RS->fields['cnt'];
			$RS->MoveNext();
		}
		// �A���ڥ��Ъ��ҵ{
		$sql = 'select count(*) AS cnt, DATE_FORMAT(memo_date, "%e") AS day ' .
			   'from WM_calendar c INNER JOIN WM_term_major t ON c.username=t.course_id ' .
			   "and t.username='{$sysSession->username}' and t.role&" .
			   ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) .
			   " where c.memo_date>='{$bdate}' AND c.memo_date<='{$edate}'and c.type='course' group by (c.memo_date)";
		$RS = $sysConn->Execute($sql);
		while (!$RS->EOF) {
			$retArr[$RS->fields['day']] = $RS->fields['cnt'];
			$RS->MoveNext();
		}
	}

	/**
	 * getCourseMonthMemo()
	 *     ���o�ҵ{������ƾ�
	 * @pram string $bdate : �}�l���
	 * @pram string $edate : �������
	 * @pram Array  $retArr: �^�ǰ}�C
	 * @return
	 **/
	function getCourseMonthMemo($bdate,$edate,&$retArr) {
		global $sysSession, $sysConn;
		$RS = dbGetStMr('WM_calendar', 'count(*) AS cnt, DATE_FORMAT(memo_date, \'%e\') AS day', "username='{$sysSession->course_id}' AND memo_date>='{$bdate}' AND memo_date<='{$edate}' group by (memo_date)", ADODB_FETCH_ASSOC);
		while (!$RS->EOF) {
			$retArr[$RS->fields['day']] = $RS->fields['cnt'];
			$RS->MoveNext();
		}
	}


	/**
	 * getMyClassMonthMemo()
	 *     ���o�ڪ��Z�Ū�����ƾ�
	 * @pram string $bdate : �}�l���
	 * @pram string $edate : �������
	 * @pram Array  $retArr: �^�ǰ}�C
	 * @return
	 **/
	function getMyClassMonthMemo($bdate,$edate,&$retArr) {
		global $sysSession, $sysConn, $sysRoles, $ADODB_FETCH_MODE;
		if(!is_array($retArr))
			return;

        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        chkSchoolId('WM_calendar');
		// ���p�ڪ��Z��
		$sql = "select count(*) AS cnt, DATE_FORMAT(memo_date, '%e') AS day from WM_calendar c INNER JOIN WM_class_member t ON c.username=t.class_id " .
			   "where t.username='{$sysSession->username}' and t.role&" . $sysRoles['student'] .
			   " AND c.memo_date>='{$bdate}' AND c.memo_date<='{$edate}'and c.type='class' group by (c.memo_date)";
		$RS = $sysConn->Execute($sql);
		while (!$RS->EOF) {
			$retArr[$RS->fields['day']] = $RS->fields['cnt'];
			$RS->MoveNext();
		}
		// ���p�ڥ��ɪ��Z��
		$sql = "select count(*) AS cnt, DATE_FORMAT(memo_date, '%e') AS day from WM_calendar c INNER JOIN WM_class_member t ON c.username=t.class_id " .
			   "where t.username='{$sysSession->username}' and t.role&" . ($sysRoles['director'] | $sysRoles['assistant']) .
			   " AND c.memo_date>='{$bdate}' AND c.memo_date<='{$edate}'and c.type='class' group by (c.memo_date)";
		$RS = $sysConn->Execute($sql);
		while (!$RS->EOF) {
			$retArr[$RS->fields['day']] = $RS->fields['cnt'];
			$RS->MoveNext();
		}
	}

	/**
	 * getClassMonthMemo()
	 *     ���o�Z�Ū�����ƾ�
	 * @pram string $bdate : �}�l���
	 * @pram string $edate : �������
	 * @pram Array  $retArr: �^�ǰ}�C
	 * @return
	 **/
	function getClassMonthMemo($bdate,$edate,&$retArr) {
		global $sysSession, $sysConn;
		$RS = dbGetStMr('WM_calendar', 'count(*) AS cnt, DATE_FORMAT(memo_date, \'%e\') AS day', "username='{$sysSession->class_id}' AND memo_date>='{$bdate}' AND memo_date<='{$edate}' group by (memo_date)", ADODB_FETCH_ASSOC);
		while (!$RS->EOF) {
			$retArr[$RS->fields['day']] = $RS->fields['cnt'];
			$RS->MoveNext();
		}
	}



	/**
	 * getSchoolMonthMemo()
	 *     ���o�Z�Ū�����ƾ�
	 * @pram string $bdate : �}�l���
	 * @pram string $edate : �������
	 * @pram Array  $retArr: �^�ǰ}�C
	 * @return
	 **/
	function getSchoolMonthMemo($bdate,$edate,&$retArr) {
		global $sysSession, $sysConn;
		$RS = dbGetStMr('WM_calendar', 'count(*) AS cnt, DATE_FORMAT(memo_date, \'%e\') AS day',
						"username='{$sysSession->school_id}' AND type='school' AND memo_date>='{$bdate}' AND memo_date<='{$edate}' group by (memo_date)", ADODB_FETCH_ASSOC);
		while (!$RS->EOF) {
			$retArr[$RS->fields['day']] = $RS->fields['cnt'];
			$RS->MoveNext();
		}
	}

	// �Ǯեu��ثe�Ҧb�Ǯ�, ��e�� Course �H�� Class ���P, �ҥH getMySchoolMonthMemo() �I�s getSchoolMonthMemo() �Y�i
	function getMySchoolMonthMemo($bdate,$edate,&$retArr) {
		global $sysSession, $sysConn;
		getSchoolMonthMemo($bdate, $edate, $retArr);
	}




	/**
	 * ���o��Ӥ몺��ƾ�
	 * @pram string $interface  : �Ҧb�����Φ�('person'(default),'course','class','school')
	 * @return string $xmlstr : �@�����㪺 XML ��r
	 **/
	function getMonthMemo(&$dom, $interface='person') {
		global $sysSession, $sysConn, $ticket, $MyCalendarSettings;

		$year     = getNodeValue($dom, 'year');
		$month    = intval(getNodeValue($dom, 'month')) + 1;
		$day      = getNodeValue($dom, 'day');

		$bdate    = "{$year}/{$month}/1";
		$edate    = "{$year}/{$month}/31";

		$personal = array();
		$course   = array();
		$class    = array();
		$school   = array();

		$xmlstr   = '<?xml version="1.0" encoding="UTF-8"?><manifest>' .
		            '<ticket>'    . $ticket    . '</ticket>' .
		            '<interface>' . $interface . '</interface>';

		$show_person= false;	// �O�_�n��ܭӤH��ƾ�
		$show_school= false;	// �O�_�n��ܾǮզ�ƾ�
		$show_course= false;	// ..........�ҵ{......
		$show_class = false;	// ..........�Z��......
		switch($interface) {
			case 'person':
				$show_person= true;
				$show_school= in_array('school',$MyCalendarSettings);
				$show_course= in_array('course',$MyCalendarSettings);
				$show_class = in_array('class' ,$MyCalendarSettings);
				getPersonMonthMemo($bdate, $edate, $personal);
				if($show_school) getMySchoolMonthMemo($bdate, $edate, $school);
				if($show_course) getMyCourseMonthMemo($bdate, $edate, $course);
				if($show_class) getMyClassMonthMemo(  $bdate, $edate, $class);
				break;

			case 'school':
				$show_school= true;
				getSchoolMonthMemo($bdate, $edate, $school);
				break;
			case 'course':
				$show_course= true;
				getCourseMonthMemo($bdate, $edate, $course);
				break;
			case 'class':
				$show_class = true;
				getClassMonthMemo(  $bdate, $edate, $class);
				break;
		}

		for ($i = 1; $i <= 31; $i++) {
			$xmlstr .= '<date day="'     . $i                                 . '">' .
			           '<personal num="' . intval($personal[$i])              . '"> </personal>' .
			           '<course num="'   . intval($show_course?$course[$i]:0) . '"> </course>'   .
			           '<class num="'    . intval($show_class ?$class[$i] :0) . '"> </class>'    .
			           '<school num="'   . intval($show_school?$school[$i]:0) . '"> </school>'   .
			           '</date>';
		}

		$xmlstr .= '</manifest>';
		return $xmlstr;
	}


/**************************************************************************
 *      ��ƾ�C��M��Ƶ{���s ����
 **************************************************************************/



/**************************************************************************
 *      ��ƾ�C�餺�e�Ƶ{���s �}�l
 **************************************************************************/

	/**
	 * �^��C�X��ƾ䤺�e
	 *	( ���H�UgetDayMemo �ӳ] )
	 */
	function LoopMemoRS($RS,&$cnt,&$str,$def_caption='') {
		global $sysSession;
		while (!$RS->EOF) {
			$cnt++;
			if ($def_caption == '' && isSet($RS->fields['caption']))
			{
				$caption = getCaption($RS->fields['caption']);
				$caption = $caption[$sysSession->lang];
			}
			else
			{
				$caption = $def_caption;
			}
			$str .= '<memo id="'      . $RS->fields['idx']                       . '">'              .
					'<username>'      . $RS->fields['username']                  . '</username>'     .
					'<caption>'       . $caption                                 . '</caption>'      .
					'<type>'          . $RS->fields['type']                      . '</type>'         .
					'<time_begin>'    . $RS->fields['time_begin']                . '</time_begin>'   .
					'<time_end>'      . $RS->fields['time_end']                  . '</time_end>'     .
					'<repeat>'        . $RS->fields['repeat']                    . '</repeat>'       .
					'<repeat_end>'    . $RS->fields['repeat_end']                . '</repeat_end>'   .
					'<subject>'       . htmlspecialchars($RS->fields['subject']) . '</subject>'      .
					'<alert_type>'    . $RS->fields['alert_type']                . '</alert_type>'   .
					'<alert_before>'  . $RS->fields['alert_before']              . '</alert_before>' .
					'<content type="' . $RS->fields['ishtml']                    . '">'              . htmlspecialchars($RS->fields['content']) . '</content>' .
					'<upd_time>'      . $RS->fields['upd_time']                  . "</upd_time></memo>\n";
			$RS->MoveNext();
		}
	}

// **************** �ӤH���� ************************

	/**
	 * ���o�ӤH����ƾ�
	 * @pram string  $date		: ��ƾ���
	 * @pram int     $cnt		: ��ƾ�ƥحp��
	 * @pram string  $str		: �Ȧs XML ���r���ܼ�
	 * @return $cnt & $str		: �H�ѼƤ覡�^�ǭ�
	 **/
	function GetPersonDayMemo($date, &$cnt, &$str) {
		global $sysSession, $sysConn;
		$RS = dbGetStMr('WM_calendar', '*', "username='{$sysSession->username}' AND type='person' AND memo_date='{$date}' order by time_begin, idx", ADODB_FETCH_ASSOC);
		LoopMemoRS($RS, $cnt, $str);
	}

// **************** �ҵ{���� ************************
	/**
	 * ���o�ӤH�ҿ�Ҫ��ҵ{��ƾ�
	 * @pram string  $date		: ��ƾ���
	 * @pram int     $cnt		: ��ƾ�ƥحp��
	 * @pram string  $str		: �Ȧs XML ���r���ܼ�
	 * @return $cnt & $str		: �H�ѼƤ覡�^�ǭ�
	 **/
	function GetMyCourseDayMemo($date, &$cnt, &$str) {
		global $sysSession, $sysConn;

		$sql = "select c.*,x.caption from WM_calendar c INNER JOIN WM_term_major t ON c.username=t.course_id " .
			   "INNER JOIN WM_term_course x ON t.course_id=x.course_id ".
			   "where t.username='{$sysSession->username}' and c.memo_date='{$date}' and c.type='course' order by c.time_begin, c.idx";
        chkSchoolId('WM_calendar');
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sql);
		LoopMemoRS($RS, $cnt, $str);
	}
	/**
	 * ���o�ӤH�ұнҪ��ҵ{��ƾ�
	 * @pram string  $date		: ��ƾ���
	 * @pram int     $cnt		: ��ƾ�ƥحp��
	 * @pram string  $str		: �Ȧs XML ���r���ܼ�
	 * @return $cnt & $str		: �H�ѼƤ覡�^�ǭ�
	 **/
	function GetTeachCourseDayMemo($date, &$cnt, &$str) {
		global $sysSession, $sysConn, $sysRoles, $ADODB_FETCH_MODE;
		$sql = "select c.*,x.caption from WM_calendar c INNER JOIN WM_term_major t " .
			   "ON c.username=t.course_id and t.username='{$sysSession->username}' and t.role&" .
			   ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) .
			   ' INNER JOIN WM_term_course x ON t.course_id=x.course_id ' .
			   "where c.memo_date='{$date}' and c.type='course' order by c.time_begin, c.idx";
        chkSchoolId('WM_calendar');
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sql);
		LoopMemoRS($RS, $cnt, $str);
	}
	/**
	 * ���o�ҵ{��ƾ�
	 * @pram string  $course_id	: �ҵ{�s��
	 * @pram string  $date		: ��ƾ���
	 * @pram int     $cnt		: ��ƾ�ƥحp��
	 * @pram string  $str		: �Ȧs XML ���r���ܼ�
	 * @return $cnt & $str		: �H�ѼƤ覡�^�ǭ�
	 **/
	function GetCourseDayMemo($course_id, $date, &$cnt, &$str) {
		global $sysSession, $sysConn, $ADODB_FETCH_MODE;
		$sql = "select c.*,x.caption from WM_calendar c " .
			   "INNER JOIN WM_term_course x ON c.username=x.course_id ".
			   "where c.username='{$course_id}' and c.memo_date='{$date}' and c.type='course' order by c.time_begin, c.idx";
        chkSchoolId('WM_calendar');
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sql);
		LoopMemoRS($RS, $cnt, $str);
	}



// **************** �Z�ų��� ************************
	/**
	 * ���o�ӤH�Z�Ū��Z�Ŧ�ƾ�
	 * @pram string  $date		: ��ƾ���
	 * @pram int     $cnt		: ��ƾ�ƥحp��
	 * @pram string  $str		: �Ȧs XML ���r���ܼ�
	 * @return $cnt & $str		: �H�ѼƤ覡�^�ǭ�
	 **/
	function GetMyClassDayMemo($date, &$cnt, &$str) {
		global $sysSession, $sysConn, $ADODB_FETCH_MODE;

		$sql = "select c.*,x.caption from WM_calendar c INNER JOIN WM_class_member t ON c.username=t.class_id " .
			   "INNER JOIN WM_class_main x ON t.class_id=x.class_id ".
			   "where t.username='{$sysSession->username}' and c.memo_date='{$date}' and c.type='class' order by c.time_begin, c.idx";
        chkSchoolId('WM_calendar');
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sql);
		LoopMemoRS($RS, $cnt, $str);
	}
	/**
	 * ���o�ӤH�ҥ��ɮv���Z�Ŧ�ƾ�
	 * @pram string  $date		: ��ƾ���
	 * @pram int     $cnt		: ��ƾ�ƥحp��
	 * @pram string  $str		: �Ȧs XML ���r���ܼ�
	 * @return $cnt & $str		: �H�ѼƤ覡�^�ǭ�
	 **/
	function GetTeachClassDayMemo($date, &$cnt, &$str) {
		global $sysSession, $sysConn, $sysRoles, $ADODB_FETCH_MODE;
		$sql = "select c.*,x.caption from WM_calendar c INNER JOIN WM_class_member t ON c.username=t.class_id " .
			   "INNER JOIN WM_class_main x ON t.class_id=x.class_id ".
			   "where t.username='{$sysSession->username}' and t.role&" . ($sysRoles['director'] | $sysRoles['assistant']) .
			   " and c.memo_date='{$date}' and c.type='class' order by c.time_begin, c.idx";
        chkSchoolId('WM_calendar');
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sql);
		LoopMemoRS($RS, $cnt, $str);
	}
	/**
	 * ���o�Z�Ŧ�ƾ�
	 * @pram string  $class_id	: �Z�Žs��
	 * @pram string  $date		: ��ƾ���
	 * @pram int     $cnt		: ��ƾ�ƥحp��
	 * @pram string  $str		: �Ȧs XML ���r���ܼ�
	 * @return $cnt & $str		: �H�ѼƤ覡�^�ǭ�
	 **/
	function GetClassDayMemo($class_id,$date, &$cnt, &$str) {
		global $sysSession, $sysConn, $ADODB_FETCH_MODE;
		$sql = "select c.*,x.caption from WM_calendar c " .
			   "INNER JOIN WM_class_main x ON c.username=x.class_id ".
			   "where c.username='{$class_id}' and c.memo_date='{$date}' and c.type='class' order by c.time_begin, c.idx";
        chkSchoolId('WM_calendar');
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sql);
		LoopMemoRS($RS, $cnt, $str);
	}


// ****************  �Ǯճ��� ************************
	/**
	 * ���o�Ǯզ�ƾ�
	 * @pram string  $school_id	: �Ǯսs��
	 * @pram string  $date		: ��ƾ���
	 * @pram int     $cnt		: ��ƾ�ƥحp��
	 * @pram string  $str		: �Ȧs XML ���r���ܼ�
	 * @return $cnt & $str		: �H�ѼƤ覡�^�ǭ�
	 **/
	function GetSchoolDayMemo($school_id,$date, &$cnt, &$str) {
		global $sysSession, $sysConn;
		$RS = dbGetStMr('WM_calendar', '*', "username='{$school_id}' AND type='school' AND memo_date='{$date}' order by time_begin, idx", ADODB_FETCH_ASSOC);
		LoopMemoRS($RS, $cnt, $str, $sysSession->school_name);
	}



	/**
	 * ���o�@�Ѫ���ƾ�
	 * @pram xmldoc  $dom 		: �ݸѪR�� XML ����
	 * @pram string  $interface	: �ثe�Ҧb��������( 'person', 'course', 'class', 'school' )
	 * @pram bool    $list_course   : �ݸѪR�� XML ����
	 * @return string $xmlstr	: �@�����㪺 XML ��r
	 **/
	function getDayMemo(&$dom,$interface='person') {
		global $_mySetting,$MyCalendarSettings,$sysSession, $sysConn, $ticket;

		$year    = getNodeValue($dom, 'year');
		$month   = intval(getNodeValue($dom, 'month')) + 1;
		$day     = getNodeValue($dom, 'day');

		$date    = "{$year}/{$month}/{$day}";

		$xmlstr  = '<?xml version="1.0" encoding="UTF-8"?><manifest>' .
				   '<ticket>' . $ticket . '</ticket>';

		$str     = '';
		$cnt     = 0;

		$msg     = $_mySetting;
		switch($interface)
		{
			case 'person':	// �ӤH��ƾ䤶��
				getPersonDayMemo($date,$cnt, $str);
				$xmlstr .= '<personal num="' . intval($cnt) . '">' . $str . '</personal>';
				$str = '';
				$cnt = 0;
				if(in_array('course', $MyCalendarSettings)) {	// User �]�w�n��ܽҵ{��ƾ�
					getMyCourseDayMemo($date,$cnt, $str);
					// getTeachCourseDayMemo($date,$cnt,$str);
					$xmlstr .= '<course num="' . intval($cnt) . '">' . $str . '</course>';
					$msg .=" Show_course ";
				}
				$str = '';
				$cnt = 0;
				if(in_array('class', $MyCalendarSettings)) {	// User �]�w�n��ܯZ�Ŧ�ƾ�
					getMyClassDayMemo($date,$cnt, $str);
					getTeachClassDayMemo($date,$cnt,$str);
					$xmlstr .= '<class num="' . intval($cnt) . '">' . $str . '</class>';
					$msg .=" Show_class ";
				}
				$str = '';
				$cnt = 0;
				if(in_array('school', $MyCalendarSettings)) {	// User �]�w�n��ܾǮզ�ƾ�
					getSchoolDayMemo($sysSession->school_id, $date,$cnt, $str);
					$xmlstr .= '<school num="' . intval($cnt) . '">' . $str . '</school>';
					$msg .=" Show_school ";
				}
				break;

			case 'course':	// �ҵ{��ƾ䤶��
				getCourseDayMemo($sysSession->course_id,$date,$cnt, $str);
				$xmlstr .= '<course num="' . intval($cnt) . '">' . $str . '</course>';
				break;

			case 'class':	// �Z�Ŧ�ƾ䤶��
				getClassDayMemo($sysSession->class_id,$date,$cnt, $str);
				$xmlstr .= '<class num="' . intval($cnt) . '">' . $str . '</class>';
				break;

			case 'school':	// �Ǯզ�ƾ䤶��
				getSchoolDayMemo($sysSession->school_id,$date,$cnt, $str);
				$xmlstr .= '<school num="' . intval($cnt) . '">' . $str . '</school>';
				break;
		}
		$xmlstr .= "<message>$msg</message></manifest>";
		return $xmlstr;
	}

/**************************************************************************
 *      ��ƾ�C�餺�e�Ƶ{���s ����
 **************************************************************************/

	/**
	 * �R����ƾ�
	 * @return string $xmlstr : �x�s�����A
	 * @note: �Y���g���ƥ�, ������ƾ�N�@�֧R��
	 **/
	function delMemo(&$dom) {
		global $sysConn, $sysSession, $ticket;

		$idx =  getNodeValue($dom, 'idx');
		if(intval($idx) > 0)
		{
			$affected_row = 0;

			// ����O�_���l�`�I (���o���`�I�s��)(�g���ƥ�)
			$RS  = dbGetStSr('WM_calendar','`parent_idx`,`repeat`', "idx={$idx}", ADODB_FETCH_ASSOC);
			$parent_idx = $RS['parent_idx'];
			$repeat     = $RS['repeat'];	// ������g���]�w
			if($repeat=='none') {			// ����N���O�g���ƥ�
				dbDel('WM_calendar',"idx={$idx}");
				$affected_row = $sysConn->Affected_Rows();
			} else {
				$delType =  getNodeValue($dom, 'type');

				if($parent_idx > 0) {		// ���O���`�I
					if($delType=='single') {	// �u�R�@�ƥ�
						dbDel('WM_calendar', "idx={$idx}");
							$affected_row = $sysConn->Affected_Rows();
					} else if($delType=='all') {	// �R����Ӷg��
						dbDel('WM_calendar', "parent_idx={$parent_idx}");
							$affected_row = $sysConn->Affected_Rows();
						dbDel('WM_calendar', "idx={$parent_idx}");
							$affected_row += $sysConn->Affected_Rows();
					}
				} else {			// �O���`�I

					if($delType=='single') {	// �u�R�@�ƥ�
						// ��X�̤p�s�����@�I�ܬ����`�I
						$min_idx = dbGetOne('WM_calendar', 'min(idx) as min_idx', "parent_idx={$idx}");
						dbSet('WM_calendar', "parent_idx={$min_idx}", "parent_idx={$idx}");
						dbSet('WM_calendar', "parent_idx=0","idx={$min_idx}");
						// �R���n�R���`�I
						dbDel('WM_calendar', "idx={$idx}");
							$affected_row = $sysConn->Affected_Rows();
					} else if($delType=='all') {	// �R����Ӷg��
						dbDel('WM_calendar', "parent_idx={$idx}");
							$affected_row = $sysConn->Affected_Rows();
						dbDel('WM_calendar', "idx={$idx}");
							$affected_row += $sysConn->Affected_Rows();
					}
				}
			}

			$status = ($affected_row) ? '0' : '1';
		} else {
			$status = '-1';
		}

		return '<?xml version="1.0" encoding="UTF-8"?><manifest>' .
		       '<ticket>' . $ticket . '</ticket>' .
		       '<status>' . $status . '</status>' .
		       '</manifest>';
	}


	/**
	 * �x�s��ƾ�
	 * @parm xmldom   $dom		: DOM Object
	 * @parm string   $interface	: �ثe�Ҧb��������( 'person'(default), 'course', 'class', 'school' )
	 * @return string $xmlstr	: �x�s�����A
	 **/
	function saveMemo(&$dom, $interface='person') {
		global $sysConn, $sysSession, $ticket;

		$year  = getNodeValue($dom, 'year');
		$month = intval(getNodeValue($dom, 'month')) + 1;
		$day   = getNodeValue($dom, 'day');

		switch($interface) {
			case 'school' :
				$username = $sysSession->school_id;
				$type     = 'school';
				break;
			case 'class'   :
				$username = $sysSession->class_id;
				$type     = 'class';
				break;
			case 'course'    :
				$username = $sysSession->course_id;
				$type     = 'course';
				break;
			case 'person':
				$username = $sysSession->username;
				$type     = 'person';
				break;
			default:
				return '<?xml version="1.0" encoding="UTF-8"?><manifest>' .
				       '<ticket>' . $ticket . '</ticket>' .
				       '<status>5</status>' .
				       '<env>{$sysSession->env}</env>' .
				       '</manifest>';
		}

		$timeBegin = getNodeValue($dom, 'time_begin');
		$timeEnd   = getNodeValue($dom, 'time_end');

		$tmp1      = explode(':', $timeBegin);
		$tmp2      = explode(':', $timeEnd);
		if (($tmp1[0] == '-1') || ($tmp2[0] == '-1')) {
			$timeBegin = 'NULL';
			$timeEnd   = 'NULL';
		} else {
			$timeBegin = "'" . $timeBegin . "'";
			$timeEnd   = "'" . $timeEnd . "'";
		}

		$repeat = getNodeValue($dom, 'repeat');
		$repeat_end = '0000-00-00';
		if($repeat!='none')
			$repeat_end = getNodeValue($dom, 'repeat_endY') ."-" . getNodeValue($dom, 'repeat_endM') ."-" . getNodeValue($dom, 'repeat_endD');

		$alertType = getNodeValue($dom, 'alert_type');
		$alert_before = getNodeValue($dom, 'alert_before');
		if ($alertType == 'none') {
			$alertType = '';
			$alter_before = 0;
		}

		$subject = getNodeValue($dom, 'subject');
		$subject = addslashes(trim($subject));

		$ary     = $dom->get_elements_by_tagname('content');
		$tmp     = $ary[0]->first_child();
		$content = $tmp->node_value();
		$content = addslashes($content);
		$ishtml  = $ary[0]->get_attribute('type');

		if ($ishtml == 'html') {
			$content = str_replace('&lt;', '<', $content);
			$content = str_replace('&gt;', '>', $content);
		}

		$idx =  getNodeValue($dom, 'idx');
		$upd_mode = false;
		$msg = '';
		if (empty($idx)) {
			$fields = '`username`, `type`, `memo_date`, `time_begin`, `time_end`, '.
					  '`repeat`, `repeat_freq`, `repeat_end`, ' .
					  '`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`';
			$values = "'{$username}', '{$type}','{$year}-{$month}-{$day}', {$timeBegin}, {$timeEnd}" .
					  ", '{$repeat}',0,'{$repeat_end}'" .
					  ", '{$alertType}', {$alert_before}, '{$ishtml}', '{$subject}', '{$content}', NULL";
			$msg .= "[$fields][$values]";
			dbNew('WM_calendar', $fields, $values);
			$status = ($sysConn->Affected_Rows()) ? '1' : '2';
			$idx    = $sysConn->Insert_ID();
		} else {
			$upd_mode = true;
			// �ˬd�O�_�㦳���`�I
			list($parent_idx) = dbGetStSr('WM_calendar','parent_idx', "idx={$idx}", ADODB_FETCH_NUM);
			if($parent_idx) { // ���u���`�I (�H���`�I���N����)
				$idx = $parent_idx;
				list($memo_date) = dbGetStSr('WM_calendar','memo_date', "idx={$idx}", ADODB_FETCH_NUM);
				if (isSet($memo_date))
				{
					$memo_date = strtotime($memo_date);
					$year = Date('Y',$memo_date);
					$month= Date('m',$memo_date);
					$day  = Date('d',$memo_date);
				}
			}

			$sqls = "`memo_date`='{$year}-{$month}-{$day}', `time_begin`={$timeBegin}, `time_end`={$timeEnd}" .
					", `repeat`='{$repeat}', `repeat_end`='{$repeat_end}' " .
					", `alert_type`='{$alertType}', `alert_before`='{$alert_before}', `ishtml`='{$ishtml}'" .
					", `subject`='{$subject}', `content`='{$content}', `upd_time`=Now()";
			dbSet('WM_calendar', $sqls, "idx={$idx}");
			$Affected_Rows = ($sysConn->Affected_Rows());
			dbDel('WM_calendar',"parent_idx={$idx}");
			$status = ($sysConn->Affected_Rows()+$Affected_Rows) ? '3' : '4';
		}


		// �g������
		if($idx>0)
		{
			$parent_idx = $idx;
			$rpt_from = strtotime("{$year}-{$month}-{$day}");
			$rpt_end  = strtotime($repeat_end);
			$fields   = '`parent_idx`, `username`, `type`, `memo_date`, `time_begin`, `time_end`, '.
				'`repeat`, `repeat_freq`, `repeat_end`, ' .
				'`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`';
		   switch($repeat) {
			case 'day':
				$interval = 86400;
				for($thedate=$rpt_from+$interval;$thedate<=$rpt_end;$thedate+=$interval)
				{
					$values = "{$parent_idx},'{$username}', '{$type}','". Date('Y-m-d',$thedate). "', {$timeBegin}, {$timeEnd}" .
							  ", '{$repeat}',0,'{$repeat_end}'" .
							  ", '{$alertType}', {$alert_before}, '{$ishtml}', '{$subject}', '{$content}', NULL";
					dbNew('WM_calendar', $fields, $values);
				}
			break;
			case 'week':
				$interval = 86400*7;
				for($thedate=$rpt_from+$interval;$thedate<=$rpt_end;$thedate+=$interval)
				{
					$values = "{$parent_idx},'{$username}', '{$type}','". Date('Y-m-d',$thedate). "', {$timeBegin}, {$timeEnd}" .
							  ", '{$repeat}',0,'{$repeat_end}'" .
							  ", '{$alertType}', {$alert_before}, '{$ishtml}', '{$subject}', '{$content}', NULL";
					dbNew('WM_calendar', $fields, $values);
				}
			break;
			case 'month':
				for($y=$year,$m=$month+1;strtotime("$y-$m-$day")<=$rpt_end;$m++)
				{
					if($m>12)
					{
						$y++;
						$m=1;
					}

					$values = "{$parent_idx},'{$username}', '{$type}','{$y}-{$m}-{$day}', {$timeBegin}, {$timeEnd}" .
							  ", '{$repeat}',0,'{$repeat_end}'" .
							  ", '{$alertType}', {$alert_before}, '{$ishtml}', '{$subject}', '{$content}', NULL";
					dbNew('WM_calendar', $fields, $values);
				}
			break;
		   }
		}

		return '<?xml version="1.0" encoding="UTF-8"?><manifest id="' . $idx . '">' .
               '<ticket>' . $ticket . '</ticket>' .
               '<status>' . $status . '</status>' .
               '<msg>'    . $status . '</msg></manifest>';
	}

    function getPersonNewCalendar($bdate,$edate) {
        global $sysSession;
        // ���o�ӤH����ƾ�
        $RS = dbGetAll('WM_calendar', '*,DATE_FORMAT(memo_date, \'%e\') AS day', "type='person' AND username='{$sysSession->username}' AND memo_date>='{$bdate}' AND memo_date<='{$edate}'  order by memo_date asc", ADODB_FETCH_ASSOC);
        return $RS;
    }

    function getSchoolNewCalendar($bdate,$edate) {
        global $sysSession;
        $RS = dbGetAll('WM_calendar', '*,DATE_FORMAT(memo_date, \'%e\') AS day', "type='school'  AND username='{$sysSession->school_id}' AND memo_date>='{$bdate}' AND memo_date<='{$edate}'  order by memo_date asc", ADODB_FETCH_ASSOC);
        return $RS;
    }

    function getMyCourseNewCalendar($bdate,$edate) {
        global $sysSession, $sysConn, $ADODB_FETCH_MODE,$sysRoles;
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        chkSchoolId('WM_calendar');
        // �����ڪ��ҵ{
        $sql = "SELECT c.*,x.caption,DATE_FORMAT(memo_date, '%e') AS day from WM_calendar c ".
            "INNER JOIN WM_term_major t ON c.username=t.course_id " .
            "INNER JOIN WM_term_course x ON t.course_id=x.course_id ".
            "where t.username='{$sysSession->username}' AND c.memo_date>='{$bdate}' AND c.memo_date<='{$edate}' AND t.role&" .
            $sysRoles['student'] ." AND c.type='course'";
        $RS = $sysConn->GetAll($sql);
        $RS = array_map(function($value){
            global $sysSession;
            $caption=getCaption($value['caption']);
            $value['subject']="({$caption[$sysSession->lang]}){$value['subject']}";
            return $value;
        }, $RS);
        return $RS;
    }

    function getCourseNewCalendar($bdate,$edate) {
        global $sysSession, $sysConn, $ADODB_FETCH_MODE;
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        chkSchoolId('WM_calendar');
        // �����ڪ��ҵ{
        $RS = dbGetAll('WM_calendar', '*, DATE_FORMAT(memo_date, \'%e\') AS day', "username='{$sysSession->course_id}' AND memo_date>='{$bdate}' AND memo_date<='{$edate}' AND type='course'", ADODB_FETCH_ASSOC);
        return $RS;
    }


    function getMyDayNewCalendar($day,$interface,$type) {
        global $sysSession, $sysConn, $ADODB_FETCH_MODE;
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        chkSchoolId('WM_calendar');
        switch($interface)
        {
            case 'person':	// �ӤH��ƾ䤶��
                if($type=="person") $RS = dbGetAll('WM_calendar', '*,DATE_FORMAT(memo_date, \'%e\') AS day', "type='person' AND username='{$sysSession->username}' AND memo_date='{$day}' order by memo_date asc", ADODB_FETCH_ASSOC);
                if($type=="course") {
                    $course = $sysConn->GetAll("SELECT c.*,x.caption, DATE_FORMAT(memo_date, '%e') AS day from WM_calendar c ".
                        "INNER JOIN WM_term_major t ON c.username=t.course_id " .
                        "INNER JOIN WM_term_course x ON t.course_id=x.course_id ".
                        "where t.username='{$sysSession->username}' AND c.memo_date='{$day}'");
                    $RS = array_map(function($value){
                        global $sysSession;
                        $caption=getCaption($value['caption']);
                        $value['subject']="({$caption[$sysSession->lang]}){$value['subject']}";
                        return $value;
                    }, $course);
                }
                if($type=="school") {
                    $RS = dbGetAll('WM_calendar', '*,DATE_FORMAT(memo_date, \'%e\') AS day', "type='school'  AND username='{$sysSession->school_id}' AND memo_date='{$day}' order by memo_date asc", ADODB_FETCH_ASSOC);
                }
                break;
            case 'course':	// �ҵ{��ƾ䤶��
                $RS = $sysConn->GetAll("SELECT c.*,DATE_FORMAT(memo_date, '%e') AS day from WM_calendar c ".
                "INNER JOIN WM_term_major t ON c.username=t.course_id " .
                "where t.username='{$sysSession->username}' AND c.username='{$sysSession->course_id}' AND c.memo_date='{$day}'");
                break;
            case 'school':	// �Ǯզ�ƾ䤶��
                $RS = dbGetAll('WM_calendar', '*,DATE_FORMAT(memo_date, \'%e\') AS day', "type='school'  AND username='{$sysSession->school_id}' AND memo_date='{$day}' order by memo_date asc", ADODB_FETCH_ASSOC);
                break;
        }
        return $RS;
    }

?>
