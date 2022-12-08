#!/usr/local/bin/php
<?php
	/**
	 *	�� WM hourly �w�ɰ���{��
	 *
	 * @since   2004/09/24
	 * @author  Yang
	 * @version $Id: cron_hourly.php,v 1.1 2010/02/24 02:38:56 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 *
	 **/

	// �t�γ]�w
	require_once(dirname(__FILE__) . '/console_initialize.php');
	require_once(dirname(__FILE__) . '/sys_config.php');
	require_once(sysDocumentRoot . '/lib/adodb/adodb.inc.php');
    require_once(sysDocumentRoot . '/xmlapi/config.php');
    require_once(sysDocumentRoot . '/xmlapi/lib/JsonUtility.php');

	// ��Ʈw�s����l��
	$sysConn = &ADONewConnection(sysDBtype);
	if (!$sysConn->PConnect(sysDBhost, sysDBaccoount, sysDBpassword))
		die('Database Connecting failure !');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

	$sysConn->Execute('use ' . sysDBname);
	$school_ids = $sysConn->GetCol('SELECT DISTINCT `school_id` FROM `WM_school`');
	if (is_array($school_ids) && count($school_ids)) {
        foreach ($school_ids as $school_id) {
            $sysConn->Execute('use ' . sysDBprefix . $school_id);
            if ($sysConn->ErrorNo() == 0) {
                // �h�������n���ŧ@�~�P�Űݨ��O�� begin
                $sysConn->Execute('DELETE FROM `WM_qti_homework_result`      WHERE ISNULL(status) AND ISNULL(content)');
                $sysConn->Execute('DELETE FROM `WM_qti_questionnaire_result` WHERE ISNULL(status) AND ISNULL(content)');
                // �h�������n���ŧ@�~�P�Űݨ��O�� end


                // �̷s�������� - Begin => �����Ƶ{����e29����?��30�������̷s����
                $nowTime = time();
                $sql = "SELECT
                        BP.`subject`, BP.`content`, BP.`poster`
                    FROM
                        `WM_news_subject` AS NS
                    LEFT JOIN
                        `WM_news_posts` AS NP ON NS.`news_id` = NP.`news_id` AND NS.`board_id` = NP.`board_id`
                    LEFT JOIN
                        `WM_bbs_posts` AS BP ON NP.`board_id` = BP.`board_id` AND NP.`node` = BP.`node`
                    WHERE
                        NS.`type` = 'news' AND
                        UNIX_TIMESTAMP(NP.open_time) <= UNIX_TIMESTAMP() + 1800 AND
                        UNIX_TIMESTAMP(NP.open_time) > UNIX_TIMESTAMP() - 1800";
                $RS = $sysConn->Execute($sql);
                if ($RS) {
                    while ($news = $RS->FetchRow()) {
                        $channels = DatabaseHandler::getAllUsers();
                        $pushData = JsonUtility::encode(
                            array(
                                'sender' => $news['poster'],
                                'content' => $news['content'],
                                'alert' => $news['subject'],
                                'channel' => $channels,
                                'alertType' => 'NEWS'
                            )
                        );
                        require_once(sysDocumentRoot . '/xmlapi/push-handler.php');
                    }
                }
                // �̷s�������� - End

                // ���Z���G���� - Begin
//                $sql = "SELECT
//                    `course_id`, `grade_id`
//                FROM
//                    `WM_grade_list`
//                WHERE
//                    UNIX_TIMESTAMP(publish_begin) <= UNIX_TIMESTAMP()
//                    AND
//                    UNIX_TIMESTAMP() < UNIX_TIMESTAMP(publish_begin) + 3600";
//                $RS = $sysConn->Execute($sql);
//                if ($RS) {
//                    while ($grade = $RS->FetchRow()) {
//                        $_POST['courseID'] = $grade['course_id'];
//                        $_POST['gradeID'] = $grade['grade_id'];
//                        require_once(sysDocumentRoot . '/lib/app_course_grade_push_handler.php');
//                    }
//                }
                // ���Z���G���� - End
            }
        }
    }
