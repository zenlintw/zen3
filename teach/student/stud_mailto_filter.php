<?php
	/**
	 * 寄信點名的過濾條件
	 *
	 * @since   2004/05/13
	 * @author  ShenTing Lin
	 * @version $Id: stud_mailto_filter.php,v 1.1 2010/02/24 02:40:31 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/teach/student/stud_mailto_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func = '500300300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	function filter_kind_normal($ary) {
		$xmlText = '';
		foreach ($ary as $val) {
			$xmlText .= <<< BOF
	<filter key="{$val[0]}">
		<title>{$val[2]}</title>
		<operators>
			<operator value="equal"> = </operator>
			<operator value="greater"> &gt; </operator>
			<operator value="smaller"> &lt; </operator>
			<operator value="greater_equal"> &gt;= </operator>
			<operator value="smaller_equal"> &lt;= </operator>
			<operator value="differ"> != </operator>
		</operators>
		<values type="{$val[1]}"></values>
	</filter>
BOF;
		}
		return $xmlText;
	}

	function filter_kind_exam($ary, $type) {
		global $sysSession, $MSG;
		$xmlText = '';
		foreach ($ary as $val) {
			if ($val[1] == 'list') {
				$xmlText .= <<< BOF
	<filter key="{$val[0]}">
		<title>{$val[2]}</title>
		<operators>
			<operator value="no"> {$MSG['not_do'][$sysSession->lang]} </operator>
			<operator value="yes"> {$MSG['do_finish'][$sysSession->lang]} </operator>
		</operators>
BOF;
				$aray = getQTIPaperList($type);
				$xmlText .= '<values type="' . $val[1] . '">';
				foreach ($aray as $key => $value) {
					$xmlText .= '<value id="' . $key . '">' . $value[0] . '</value>';
				}
				$xmlText .= '</values></filter>';
			} else {
				$xmlText .= <<< BOF
	<filter key="{$val[0]}">
		<title>{$val[2]}</title>
		<operators>
			<operator value="equal"> = </operator>
			<operator value="greater"> &gt; </operator>
			<operator value="smaller"> &lt; </operator>
			<operator value="greater_equal"> &gt;= </operator>
			<operator value="smaller_equal"> &lt;= </operator>
			<operator value="differ"> != </operator>
		</operators>
		<values type="{$val[1]}"></values>
	</filter>
BOF;
			}
		}
		return $xmlText;
	}

	/**
	 * 取得種類
	 * @param string $kind : 哪一種
	 * @return
	 **/
	function filter_kind($kind) {
		global $sysSession, $MSG;

		$normal = true;
		$kind = trim($kind);
		switch ($kind) {
			case 'login':          // 登入
				$ary = array( array('total', 'integer' , $MSG['login_total'][$sysSession->lang]),
				              array('off'  , 'integer' , $MSG['login_not_days'][$sysSession->lang]),
				              array('last' , 'date'    , $MSG['login_last_login'][$sysSession->lang])
				            );
				break;
			case 'lesson':         // 上課
				$ary = array( array('total', 'integer' , $MSG['lesson_total'][$sysSession->lang]),
				              array('off'  , 'integer' , $MSG['lesson_not_days'][$sysSession->lang]),
				              array('last' , 'date'    , $MSG['lesson_last_login'][$sysSession->lang])
				            );
				break;
			case 'progress':       // 學習進度
				$ary = array( array('total', 'integer' , $MSG['progress_total'][$sysSession->lang]),
				              array('page' , 'integer' , $MSG['progress_page'][$sysSession->lang])
				            );
				break;
			case 'chat':           // 討論
				$ary = array( array('total', 'integer' , $MSG['chat_total'][$sysSession->lang])
				            );
				break;
			case 'post':           // 張貼
				$ary = array( array('total', 'integer' , $MSG['post_total'][$sysSession->lang])
				            );
				break;
			case 'homework':       // 作業
				$ary = array( array('no'   , 'integer' , $MSG['homework_not_do'][$sysSession->lang]),
				              array('yes'  , 'integer' , $MSG['homework_do'][$sysSession->lang]),
				              array('some' , 'list'    , $MSG['homework_some'][$sysSession->lang])
				            );
				$normal = false;
				break;
			case 'exam':           // 測驗
				$ary = array( array('no'   , 'integer' , $MSG['exam_not_do'][$sysSession->lang]),
				              array('yes'  , 'integer' , $MSG['exam_do'][$sysSession->lang]),
				              array('some' , 'list'    , $MSG['exam_some'][$sysSession->lang])
				            );
				$normal = false;
				break;
			case 'questionnaire':  // 問卷
				$ary = array( array('no'   , 'integer' , $MSG['questionnaire_not_do'][$sysSession->lang]),
				              array('yes'  , 'integer' , $MSG['questionnaire_do'][$sysSession->lang]),
				              array('some' , 'list'    , $MSG['questionnaire_some'][$sysSession->lang])
				            );
				$normal = false;
				break;
		}

		if ($normal) {
			$xmlText = filter_kind_normal($ary);
		} else {
			// 三合一的部分
			$xmlText = filter_kind_exam($ary, $kind);
		}
		return '<kind type="' . $kind . '">' . $xmlText . '</kind>';
	}
	
	header("Content-type: text/xml");
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
			exit;
		}

		$filter = getNodeValue($dom, 'filter');
		echo '<manifest>', filter_kind($filter), '</manifest>';
	}

?>
