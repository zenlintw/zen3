<?php
	/**
	 * ※ 郵寄成績單
	 *
	 * @since   2004/08/20
	 * @author  Wiseguy Liang
	 * @version $Id: grade_mail.php,v 1.1 2010/02/24 02:40:27 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/

	ignore_user_abort(true);
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/grade.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/message/collect.php');

	$sysSession->cur_func = '1400200400';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	function htmlscs($str)
	{
		return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
	}

	// $_POST['grade_comment']
	if (!ereg('^[0-9]+(,[0-9]+)*$', $_POST['lists'])) {
	   wmSysLog($sysSession->cur_func, $sysSession->course_id , $source_id , 1, 'auto', $_SERVER['PHP_SELF'], 'Incorrect GRADE_ID(s):' . $_POST['lists']);
	   die('Incorrect GRADE_ID(s)!');
	}

	$mail_body_title = <<< EOB
<table border="1" cellpadding="3" cellspacing="0" bordercolor="black" style="border-collapse: collapse">
  <tr>
    <td>{$MSG['school_name'][$sysSession->lang]}</td>
    <td>%s</td>
  </tr>
  <tr>
    <td>{$MSG['course_name'][$sysSession->lang]}</td>
    <td>%s</td>
  </tr>
  <tr>
    <td>{$MSG['send_time'][$sysSession->lang]}</td>
    <td>%s</td>
  </tr>
  <tr>
    <td>{$MSG['teacher_comment'][$sysSession->lang]}</td>
    <td>%s</td>
  </tr>
</table><hr>
EOB;

	$mail_body_title = sprintf( $mail_body_title,
								$sysSession->school_name,
								$sysSession->course_name,
								date('Y-m-d G:i:s'),
								str_replace(array("\r\n", "\n", "\r"), '<br>', $_POST['grade_comment'])
							  );

	$name_format = (in_array($sysSession->lang, array('Big5', 'GB2312'))) ? 'concat(A.last_name,A.first_name)' : 'concat(A.first_name, " ", A.last_name)';

        $where = '';
	if ($_POST['grade_type'] === 'score') {
            $where = ' AND I.score IS NOT NULL ';
	}        
        
	$sqls = "select L.grade_id,L.title,A.username,{$name_format} as realname,A.email,I.score,I.comment " .
			'from WM_term_major as M left join WM_user_account as A ' .
			'on M.username=A.username left join WM_grade_list as L ' .
			"on M.course_id=L.course_id and L.grade_id in ({$_POST['lists']}) left join WM_grade_item as I " .
			'on L.grade_id=I.grade_id and A.username=I.username ' .
			"where M.course_id ={$sysSession->course_id} and M.role & {$sysRoles['student']} " . $where .
			'order by A.username,L.grade_id';

	$x = ''; $h = ''; $g = array();
	$all_email = array();
	$wm_body = sprintf('<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>%s (%s)</title><style>body{font-family: serif} td{font-family: serif}</style></head><body>%s<table border="1" cellpadding="3" cellspacing="0" bordercolor=gray style="border-collapse: collapse">',
						htmlscs($course_titles[$sysSession->lang]),
						$sysSession->course_id,
						preg_replace('/>\s+</sU', '><', $mail_body_title));
	// 產生全班成績單 啟始
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	if ($rs = $sysConn->Execute($sqls))
	{
		while($fields = $rs->FetchRow())
		{
			if (empty($fields['username'])) continue;
                        if(strpos($fields['email'], ',') !== FALSE)
                        {
                            $mail_ary = explode(',', $fields['email']);
                            $tmp_mail = array();
                            for ($i=0;$i<count($mail_ary);$i++) {
                                if (preg_match(sysMailRule, $mail_ary[$i])) $tmp_mail[] = $mail_ary[$i];
                            }
                            $all_email[$fields['username']] = implode(',',$tmp_mail);
                        }
                        else
                        {
                            if (preg_match(sysMailRule, $fields['email'])) $all_email[$fields['username']] = $fields['email'];
                        }
			if ($prev['username'] == $fields['username'])			// 同一人的各科成績
			{
				$x .= sprintf('<td align="right">%.2f</td><td>%s</td>', $fields['score'], $fields['comment']);
			}
			else
			{
				if ($prev['username']) $x .= "</tr>\r\n";
				$x .= sprintf('<tr><td>%s</td><td>%s</td><td align="right">%.2f</td><td>%s</td>', $fields['username'], $fields['realname'], $fields['score'], $fields['comment']);
			}

			if (!isset($g[$fields['grade_id']]) && $prev['grade_id'] != $fields['grade_id'])
			{
				$captions = unserialize($fields['title']);
				$h .= sprintf('<td colspan=2>%s (%s)</td>', htmlscs($captions[$sysSession->lang]), $fields['grade_id']);
				$g[$fields['grade_id']] = htmlscs($captions[$sysSession->lang]);
			}
			$prev = $fields;
		}
	}
	$wm_body .= '<tr><td colspan=2>&nbsp;</td>' . $h . "</tr>\r\n" . $x . "</tr>\r\n</table></body></html>";
	// 產生全班成績單 結束

	if ($_POST['grade_type'] == 'all')		// 寄給全班學生全班成績
	{
		$from = mailEncFrom($sysSession->realname,$sysSession->email);
		$subject = $MSG['mail_subject'][$sysSession->lang] . $sysSession->course_name;
		$mail = buildMail($from, $subject, $wm_body, 'html', '', '', '', '', false);
		$mail->to = $sysSession->email;
		$mail->headers = 'Bcc: ' . implode(',', array_unique($all_email));
		$mail->send();
                
                wmSysLog($sysSession->cur_func, $sysSession->course_id , $source_id , 0, 'auto', $_SERVER['PHP_SELF'], 'send all score grade id = ' . $_POST['lists']);
	}
	elseif($_POST['grade_type'] === 'per' || $_POST['grade_type'] === 'score')	// 寄給個人自己成績
	{
		$x = explode("\r\n", $wm_body);
		$l = count($x)-1;
		for($i=1; $i<$l; $i++)
		{
			if (preg_match('!^<tr><td>(.*)</td>!U', $x[$i], $regs) && isset($all_email[$regs[1]]))
			{
				$from = mailEncFrom($sysSession->realname,$sysSession->email);
				$subject = $MSG['mail_subject'][$sysSession->lang] . $sysSession->course_name;
				$mail = buildMail($from, $subject, $x[0] . $x[$i] . $x[$l], 'html', '', '', '', '', false);
				$mail->to = $all_email[$regs[1]];
				$mail->headers = 'Reply-to: '.$sysSession->email;
				$mail->send();
			}
		}
                wmSysLog($sysSession->cur_func, $sysSession->course_id , $source_id , 0, 'auto', $_SERVER['PHP_SELF'], 'send per score grade id = ' . $_POST['lists']);
	}

	echo <<< EOB
<script>
alert('{$MSG['send_complete'][$sysSession->lang]}');
</script>
EOB;
?>
