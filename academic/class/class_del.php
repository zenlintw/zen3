<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/23                                                                      *
	*		work for  : �Z�Ŭd��  - �R���Z��                                                                      *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: class_del.php,v 1.1 2010/02/24 02:38:14 saly Exp $                                                                                          *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/class_group.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(PEAR_INSTALL_DIR . '/System.php');

	$sysSession->cur_func = '2400100300';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{
	}

	function destroyBoard($bid)
	{
		global $sysSession;
		// �R������ (Begin)
		$path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$sysSession->course_id}/board/{$bid}";
		if (is_dir($path)) @System::rm("-rf {$path}");
		$path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$sysSession->course_id}/quint/{$bid}";
		if (is_dir($path)) @System::rm("-rf {$path}");
		$path = sysDocumentRoot . "/base/{$sysSession->school_id}/board/{$bid}";
		if (is_dir($path)) @System::rm("-rf {$path}");
		$path = sysDocumentRoot . "/base/{$sysSession->school_id}/quint/{$bid}";
		if (is_dir($path)) @System::rm("-rf {$path}");
		// �R������ (End)
		// �R���i�K
		dbDel('WM_bbs_posts', "`board_id`={$bid}");
		dbDel('WM_bbs_order', "`board_id`={$bid}");
		dbDel('WM_bbs_collecting', "`board_id`={$bid}");
		dbDel('WM_bbs_ranking', "`board_id`={$bid}");
		dbDel('WM_bbs_readed', "`board_id`={$bid}");
	}

/**
   �d�ߪ� XML

< ?xml version="1.0" encoding="UTF-8" ? >
<manifest>
	<class_id></class_id>     <- �Z�ťN�X
</manifest>
**/

		// echo  $GLOBALS['HTTP_RAW_POST_DATA'];
		// exit();

	$query = '';
	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {

			$class_ids = getNodeValue($dom, 'class_id'); // ���X�Z�ťN�X

			// ��ϥ� explide ���ΡA��� preg_split �Ӥ��ΡA�P�ɤ]�o�����X�k���r��
			$class_id = preg_split('/[^\d@]+/', $class_ids, -1, PREG_SPLIT_NO_EMPTY);
			$num = count($class_id);

			$msg = '';
			for($i=0;$i < $num;$i++){   // for begin

				$class_id2 = explode('@',$class_id[$i]);

				// parent_id ($class_id2[0]) & child_id ($class_id2[1])

				// �Z�ŦW�� & discuss & bulletin
				list($v1,$discuss,$bulletin) = dbGetStSr('WM_class_main','caption,discuss,bulletin','class_id=' . $class_id2[1], ADODB_FETCH_NUM);
				$lang = unserialize($v1);
				$class_name = $lang[$sysSession->lang];

				// �P�_ �� �Z�� ���U  �O�_ �� �l�`�I (begin)
				list($child) = dbGetStSr('WM_class_group','child','parent=' . $class_id2[1], ADODB_FETCH_NUM);

				if ($child == 0){
					// �P�_ �� �Z�� ���U �O�_ �� �Ѯv�ΧU�� (begin)
					list($v3, $v4) = $sysConn->GetRow('SELECT SUM(IF(role&' . ($sysRoles['director'] | $sysRoles['assistant']) .
														',1,0)), SUM(IF(role&' . $sysRoles['student'] .
														',1,0)) FROM `WM_class_member` WHERE `class_id` =' . $class_id2[1]);

					if ($v3 == 0){
						// �P�_ �� �Z�� ���U �O�_ �� �ǥ� (begin)

						if ($v4 == 0){
							// �P�_ �� �Z�� ���U  �O�_ ���ݩ� �b �l�`�I (begin)

							list($v3) = dbGetStSr('WM_class_group','count(*)','child=' . $class_id2[1], ADODB_FETCH_NUM);

							if ($v3 == 1) { // �p�G �u�� ���ݦb 1 �� �l�`�I

								dbDel('WM_class_group', 'parent=' . $class_id2[1] . ' and child=0');

								dbSet('WM_class_group', 'child=0', 'parent=' . $class_id2[0] . ' and child=' . $class_id2[1]);

								dbDel('WM_bbs_boards','board_id in (' . $discuss . ',' . $bulletin . ')');
								destroyBoard($discuss);
								destroyBoard($bulletin);

								$RS = dbDel('WM_class_main', 'class_id=' . $class_id2[1]);

								if ($RS) {
									wmSysLog($sysSession->cur_func, $sysSession->school_id ,$class_id2[1] ,0, 'manager', $_SERVER['PHP_SELF'], '�R���Z��!');

									$msg .= '[ ' . $class_name . ' ] ' . $MSG['title79'][$sysSession->lang] . "\r\n";
								}else{
									$msg .= '[ ' . $class_name . ' ] ' . $MSG['title80'][$sysSession->lang] . "\r\n";
								}


							}else{
								// �u�R�� �Ŀ� �n�R���� �`�I

								dbDel('WM_class_group', 'parent=' . $class_id2[0] . ' and child=' . $class_id2[1]);
								$msg .= '[ ' . $class_name . ' ] '  . $MSG['title79'][$sysSession->lang] . "\r\n";

							}
							// �P�_ �� �Z�� ���U  �O�_ ���ݩ� �b �l�`�I (end)

						}else{
							$msg .= '[ ' . $class_name . ' ] '  . $MSG['title78'][$sysSession->lang] . "\r\n";
						}
						// �P�_ �� �Z�� ���U �O�_ �� �ǥ� (end)
					}else{
						$msg .= '[ ' . $class_name . ' ] '  . $MSG['title78'][$sysSession->lang] . "\r\n";
					}

					// �P�_ �� �Z�� ���U �O�_ �� �Ѯv�ΧU�� (end)

				}else{
					$msg .= '[ ' . $class_name . ' ] '  . $MSG['title77'][$sysSession->lang] . "\r\n";
				}

				// �P�_ �� �Z�� ���U  �O�_ �� �l�`�I (end)

			}   // for  end
			header("Content-type: text/xml");
			$result = "<manifest><result>$msg</result></manifest>";
			echo $result;
		}else{
			header("Content-type: text/xml");
			$result = "<manifest><result>1</result></manifest>";
			echo $result;
		}

	} else {
		header("Content-type: text/xml");
		$result = "<manifest><result>1</result></manifest>";
		echo $result;
	}

?>
