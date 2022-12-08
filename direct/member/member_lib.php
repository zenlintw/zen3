<?php
	/**
	 * �@�Ψ禡
	 *
	 * @since   2004/06/30
	 * @author  ShenTing Lin
	 * @version $Id: member_lib.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lang/direct_member_manage.php');

	$directRoles = array(
		'student'       => $MSG['role_student'][$sysSession->lang],
		'assistant'     => $MSG['role_assistant'][$sysSession->lang],
		'director'      => $MSG['role_director'][$sysSession->lang]
	);

	$aryUser = array(
		'username' => $MSG['search_kind_username'][$sysSession->lang],
		'realname' => $MSG['search_kind_realname'][$sysSession->lang],
		'email'    => 'E-mail',
	);

	if (!function_exists('divMsg')) {
		/**
		 * �B�z��ơA�L������������
		 * @param integer $width   : �n��ܪ��e��
		 * @param string  $caption : ��ܪ���r
		 * @param string  $title   : �B�ʪ����ܤ�r�A�Y�S���]�w�A�h�� $caption �̼�
		 * @return string : �B�z�᪺��r
		 **/
		function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
			if (empty($title)) $title = $caption;
			return $without_title ? ('<div style="width: ' . $width . 'px; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $width . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
		}
	}

	/**
	 * ���o�ɮv�ΧU�Ъ��H��
	 * @param string $role : ����
	 *     all       : �ɮv�P�U��
	 *     director  : �ɮv
	 *     assistant : �U��
	 * @param string $kind : �j�M������
	 * @param string $keyword : �j�M����r
	 * @return array $director
	 **/
	function getClassDirector($role, $kind, $keyword) {
		return getClassMember($role, $kind, $keyword, true);
	}

	/**
	 * ���o��L����
	 * @param string $role : ����
	 *     all           : ����
	 *     guest         : ���[��
	 *     senior        : �Ǫ�
	 *     paterfamilias : �a��
	 *     student       : ������
	 *     auditor       : ��ť��
	 * @param string $kind : �j�M������
	 * @param string $keyword : �j�M����r
	 * @return array $member
	 **/
	function getClassMember($role, $kind, $keyword, $onlyTA=false) {
		global $sysSession, $sysRoles, $aryAccount;

		$caid = intval($sysSession->class_id);
		// ���o��L����
		$ary = $onlyTA ? array('all', 'assistant', 'director') : array('all', 'guest', 'senior', 'paterfamilias', 'student', 'auditor');
		$member = array();
		if (in_array($role, $ary)) {
			$sqls = "`class_id`={$caid}";
			if ($role != 'all')
				$sqls .= " AND (`role` & {$sysRoles[$role]})";
			elseif ($onlyTA)
			    $sqls .= ' AND (`role` & ' . ($sysRoles['assistant'] | $sysRoles['director']) . ')';
			$RS = dbGetStMr('WM_class_member', '*', $sqls . ' order by `username`', ADODB_FETCH_ASSOC);
			if ($RS) {
				while (!$RS->EOF) {
					$key = strval($RS->fields['username']);
					if (!isset($aryAccount[$key])) {
						$aryAccount[$key] = getUserDetailData($key);   // ���禡�g�b /lib/username.php ��
					}
					if (!empty($keyword)) {
						switch ($kind) {
							case 'username' :
								if (strpos($key, $keyword) !== FALSE) $member[$key] = $RS->fields;
								break;
							case 'realname' :
								if (strpos($aryAccount[$key]['realname'], $keyword) !== FALSE) $member[$key] = $RS->fields;
								break;
							case 'email' :
								if (strpos($aryAccount[$key]['email'], $keyword) !== FALSE) $member[$key] = $RS->fields;
								break;
							default:
						}
					} else {
						$member[$key] = $RS->fields;
					}
					$RS->MoveNext();
				}
			}
		}
		return $member;
	}

	/**
	 * ���o�ӤH�׽Ҫ��ԲӸ��
	 * @param array  $username : �b��
	 * @return array $result : �@�ǭ׽Ҹ��
	 **/
	function getClassGrade($username) {
		global $sysSession, $sysConn, $Sqls, $ADODB_FETCH_MODE;

		$table_cond = 'WM_class_member as CM left join WM_term_major as TM  on CM.username = TM.username ';
		$other_cond = 'CM.class_id=' . $sysSession->class_id;
		
		if (is_array($username) && count($username) < 200)	// �b���Ӧh���ܴN�����F, �H�Ksql statement��ƶq�L���e�j
			$other_cond .= ' and CM.username in("' . implode('","', $username) . '")';
		
		$sqls = str_replace(array('%TABLE_ALIS%', '%TABLE_LEFT%', '%OTHER_CONDITION%'),
		                    array('CM'          , $table_cond   , $other_cond        ),
		                    $Sqls['get_student_grade_list']);

		if (Grade_Calculate == 'Y') {	// Y : �H�Ǥ��Ƭ��[�v��
			$sqls = str_replace('%TOTAL_AVG%','round(sum(IF((GS.total > 0) && (TC.credit > 0),TC.credit * GS.total,0)) / sum(if(GS.total > 0,TC.credit,0)),2)',$sqls);
		}else{	// N : ���H�Ǥ��Ƭ��[�v�� sum(course score) / count(course ��)
			$sqls = str_replace('%TOTAL_AVG%','round(sum(GS.total)/count(TM.course_id),2)',$sqls);
		}
		$sqls .= 'order by username';

		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		chkSchoolId('WM_class_member');
		return $sysConn->GetAssoc($sqls);
	}
?>
