<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/scorm.php');
	
	function err_handle($msg) {
		echo <<< EOF
			<script language="javascript">
			parent.parent.s_main.location.replace('about:blank');
			parent.start = function(){};
			parent.doUnload = function(){};
			parent.disable_control(false);
			alert("{$msg}");
			</script>
EOF;
		die('');
	}
	
	// 判斷是否為正式生 credit or no-credit
	list ($role) = dbGetStSr('WM_term_major', 'role', 'username = "'.$sysSession->username.'" and course_id=' . $sysSession->course_id, ADODB_FETCH_NUM);
	$credit = $role & $sysRoles['student'] ? 'credit' : 'no-credit';
	
	// 取得學員first name與 last name，不能直接取$sysSession->realname，因為格式不符合scorm
	list($fname, $lname) = dbGetStSr('WM_user_account', 'first_name, last_name', 'username = "'.$sysSession->username.'"', ADODB_FETCH_NUM);
	
	list($xml) = dbGetStSr('WM_term_path', 'content', "course_id={$sysSession->course_id} order by serial desc", ADODB_FETCH_NUM);
	if ($xml) {
		if ($xmldoc = @domxml_open_mem($xml)) {
			$root = $xmldoc->document_element();
			if ($root && $root->tagname() == 'manifest') {
				if (($ver = $root->get_attribute('version')) == '1.2') {
					include_once(sysDocumentRoot . '/learn/scorm/script/API/1_2API.js');
				}
				else {
					include_once(sysDocumentRoot . '/learn/scorm/script/API/2004API.js');
				}
				include_once(sysDocumentRoot .'/learn/scorm/toc/toc.php');
			}
		}
		else {
			err_handle($MSG['catalog_error'][$sysSession->lang]);
		}
	}
	else {
		err_handle($MSG['node_error'] [$sysSession->lang]);
	}
?>
