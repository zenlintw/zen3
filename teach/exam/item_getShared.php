<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2004/03/02                                                            *
	 *		work for  : get shared Item                                                           *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func = '1600100800';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func = '1700100800';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func = '1800100100';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	header('Content-type: text/xml');
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if(!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			error_log ('Error while parsing the document.', 0);
			die('<errorlevel>1</errorlevel>');
		}
		$root = $dom->document_element();
		if ($root->tagname() != 'form'){
			error_log ('XML root tag must be <form>.', 0);
			die('<errorlevel>2</errorlevel>');
		}

		$ctx = xpath_new_context($dom);
		$x_array = $ctx->xpath_eval('/form/item/attribute::ident');
		if (is_array($x_array->nodeset)){
			$sqls = 'select * from WM_qti_share_item where ident in ("';
			foreach($x_array->nodeset as $attr) $sqls .= $attr->node_value() . '","';
			$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC; // �u�� hash ���� array
			chkSchoolId('WM_qti_share_item');
			$rs = $sysConn->Execute(ereg_replace(',"$', ')', $sqls));
			if ($rs){
				$count = 0;
				$t = split('[. ]', microtime()); $ts = intval(substr($t[1],0,6));
				$ident = sprintf('WM_ITEM1_%05d_%06d_%d_', sysSiteUID, $course_id, $t[2]);
				$source = sprintf(sysDocumentRoot . '/base/%05d/QTI_share/%s/', $sysSession->school_id, QTI_which);
				if ($topDir == 'academic')
				{
					$target = sprintf(sysDocumentRoot . '/base/%05d/%s/Q/', $sysSession->school_id, QTI_which);
				}
				else
				{
					$target = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/Q/', $sysSession->school_id, $sysSession->course_id, QTI_which);
				}
				$sqls = 'insert into WM_qti_' . QTI_which . '_item values("';
				while ($fields = $rs->FetchRow()) {
					unset($fields['serial_no']); unset($fields['category']); // �h�������n�����
					$share_dir = $fields['ident'];
					$fields['content'] = str_replace($fields['ident'], ($ident . $ts), $fields['content']);	// �אּ�s ident
					$fields['ident'] = $ident . $ts;			// �אּ�s ident
					$fields['course_id'] = $course_id;			// �אּ�s�ҵ{ id
					// ���r��쪺�޸��[�ϱ׽u
					foreach(array('title', 'content', 'answer', 'attach') as $item) $fields[$item] = str_replace('"', '\\"', $fields[$item]);
					// �[��T�X�@ table ��
					$sysConn->Execute($sqls . implode('","', $fields) . '")');

					if ($sysConn->ErrorNo() == 0){
						if (is_dir($source . $share_dir) && chdir($source . $share_dir)){ // �p�G�����ɴN�ƻs
							exec("mkdir -p '{$target}{$ident}{$ts}'");
							if (is_dir($target . $ident . $ts)) exec("cp * '{$target}{$ident}{$ts}'");
						}
						$count++;
						$ts++;
					}
        		}
        		printf($MSG['picked_into_self'][$sysSession->lang], $count);
			}
			else{
				echo $MSG['picked_nothing'][$sysSession->lang];
			}
		}
	}
	else {
		 wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Illegal Access!');
		echo 'Illegal Access.';
	}
?>
