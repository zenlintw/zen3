<?php
    /**
     * �}�񦡰ݨ����s��V�{��
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
     * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
     * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
     *
     * @package     WM3
     * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
     * @copyright   2000-2006 SunNet Tech. INC.
     * @version     CVS: $Id: forGuestQuest.php,v 1.1 2010/02/24 02:38:55 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2007-04-25
     */

	define('QTI_which', 'questionnaire');
    define('forGuestQuestionnaire', true);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/learn_homework.php');
	require_once(sysDocumentRoot . '/lang/questionnaire_learn.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	/**
	 * ���ͤU�@�B������ URL
	 *
	 * @param   int     $unit_id        �ǮաB�ҵ{�B�Z��ID
	 * @param   int     $instance       �ݨ�ID
	 * @param   int     $step           �ĴX�B�J
	 * @return  string                  URL
	 */
	function genForGuestLink($unit_id, $instance, $step)
	{
	    $salt = rand(100000, 999999);
	    $url  = sprintf('/Q/%u/%u/%u/%u/', $unit_id, $instance, $salt, $step);
	    return $url . md5($_SERVER['HTTP_HOST'] . $url);
	}
	
	/**
	 * �ˬd���ݨ��O�_�i��g (�O�_ publish �B�b�ɶ���)
	 */
	function checkWhetherAccessible($instance)
	{
	    return dbGetOne('WM_qti_questionnaire_test', 'count(*)', 'exam_id=' . intval($instance) .
						' and publish="action" and (begin_time IS NULL or begin_time < NOW()) and (close_time IS NULL or close_time >= NOW())');
	}

	/**
	 * =================================== �D�{���}�l ====================================
	 */
	$argv = explode('/', $_SERVER['REQUEST_URI']);
	if ($argv[1] == 'forGuestQuest.php') die('access denied.');
	if ($argv[1] != 'Q' ||
		!preg_match('/^\d{5}(\d{2,3})?$/', $argv[2]) ||  // �ҵ{�B�Z�šB�Ǯ� ID
		!preg_match('/^[1-9]\d*$/'       , $argv[3]) ||  // �ݨ� ID
		!preg_match('/^[1-9]\d{5}$/'     , $argv[4]) ||  // salt
		!preg_match('/^[1-9]$/'          , $argv[5]) ||  // step
		!preg_match('/^\w{32}$/'         , $argv[6]) ||  // ticket
		$argv[6] != md5($_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, -32)))
		die($MSG['incorrect url'][$sysSession->lang]);

	$unit_id  = $argv[2];
	$instance = $argv[3];
	$step     = $argv[5];

	if (!aclCheckWhetherForGuestQuest($unit_id, $instance)) die($MSG['not for guest'][$sysSession->lang]);
	if (!checkWhetherAccessible($instance)) die($MSG['not yet begun or closed'][$sysSession->lang]);

	switch ($step)
	{
		case 1: // ��g������
		    $ticket = md5(sysTicketSeed . $instance . 0 . $_COOKIE['idx']);
		    $_SERVER['argc'] = 3;
			$_SERVER['argv'] = array($instance, 0, $ticket);
			ob_start();
			include_once(sysDocumentRoot . '/learn/questionnaire/exam_pre_start.php');
			$buffers = ob_get_contents();
			ob_end_clean();
			echo preg_replace(array('/\bexam_start\.php.[^"]*/', '/location\.replace\("questionnaire_list\.php[^"]*"\)/'),
                              array(genForGuestLink($unit_id, $instance, 2), 'self.close()'),
                              $buffers);
			break;

		case 2: // �}�l��g
		    $ticket = md5(sysTicketSeed . $instance . 0 . $_COOKIE['idx']);
		    $_SERVER['argc'] = 3;
			$_SERVER['argv'] = array($instance, 0, $ticket);
			$forGuestRedir = '/learn/questionnaire/';
			switch(strlen($unit_id)) {
			    case 8:
					$k1 = $sysSession->course_id; $sysSession->course_id = $unit_id; break;
				case 5:
					$k2 = $sysSession->school_id; $sysSession->school_id = $unit_id; break;
			}
			ob_start();
			include_once(sysDocumentRoot . '/learn/questionnaire/exam_start.php');
			$buffers = ob_get_contents();
			ob_end_clean();
			switch(strlen($unit_id)) {
				case 8:
					$sysSession->course_id = $k1; break;
				case 5:
					$sysSession->school_id = $k2; break;
			}
			$sysSession->restore();
			echo preg_replace(array('/\bsave_answer\.php\b/', '/; examOver\(\);/'),
                              array(genForGuestLink($unit_id, $instance, 3), '; self.close();'),
                              $buffers);
			break;
			
		case 3: // �x�s
			switch(strlen($unit_id)) {
			    case 8:
					$k1 = $sysSession->course_id; $sysSession->course_id = $unit_id; break;
				case 5:
					$k2 = $sysSession->school_id; $sysSession->school_id = $unit_id; break;
			}
			ob_start();
			include_once(sysDocumentRoot . '/learn/questionnaire/save_answer.php');
			$buffers = ob_get_contents();
			ob_end_clean();
			switch(strlen($unit_id)) {
				case 8:
					$sysSession->course_id = $k1; break;
				case 5:
					$sysSession->school_id = $k2; break;
			}
			$sysSession->restore();
			echo preg_replace('/\blocation\.replace\("questionnaire_list\.php[^"]*"\)/',
                              'self.close()',
                              $buffers);
			break;
			
		default:
		    die($MSG['incorrect url'][$sysSession->lang]);
	}
?>
