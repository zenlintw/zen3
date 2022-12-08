<?php
	/**
	 * �x�s�ҵ{�s��
	 *
	 * �إߤ���G2002/12/11
	 * @author  ShenTing Lin
	 * @version $Id: course_group_save.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func = '700300100';
	$sysSession->restore();

	if (!aclVerifyPermission(700300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// PS�G�p���ֹ� DB ���s��
	/**
	 * �����ܼ�
	 **/
	$dbGroup = array();

	/**
	 * ���o�ҵ{�s�ժ����D
	 * @parm $node Object �n���o���D���`�I
	 * @return array ���ػy�t���}�C
	 **/
	function buildCaption($node) {
		// �ˬd�Ƕi�Ӫ��ѼƬO���O�W�w������
		if (!is_object($node) || !$node->has_child_nodes())
			return '';

		$lang = array('Big5'=>'', 'GB2312'=>'', 'en'=>'', 'EUC-JP'=>'', 'user_define'=>'');

		// �M�� title �`�I (Begin)
		$nodes = $node->child_nodes();
		$cnt = count($nodes);
		for ($i = 0; $i < $cnt; $i++) {
			// �P�_�O���O title �`�I (Begin)
			if (($nodes[$i]->node_type() == 1) && ($nodes[$i]->node_name() == 'title')) {
			$childs = $nodes[$i]->child_nodes();
				$count = count($childs);

				// ���X�U�ӻy�t���r�� (Begin)
				for ($j = 0; $j < $count; $j++) {
					if ($childs[$j]->node_type() != 1) continue;

					if ($childs[$j]->has_child_nodes()) {
						$child = $childs[$j]->first_child();
						$child_value = $child->node_value();
					} else {
						$child_value = '';
					}

					switch ($childs[$j]->node_name()) {
						case 'big5'  : $lang['Big5']   = stripslashes($child_value); break;
						case 'gb2312': $lang['GB2312'] = stripslashes($child_value); break;
						case 'en':     $lang['en']     = stripslashes($child_value); break;
						case 'euc-jp': $lang['EUC-JP'] = stripslashes($child_value); break;
						case 'user-define': $lang['user_define'] = stripslashes($child_value); break;
					}
				}   // End for ($j = 0; $j < $count; $j++)
				// ���X�U�ӻy�t���r�� (End)
				break;
			}   // End if (($nodes[$i]->node_type() == 1) && ($nodes[$i]->node_name() == 'title'))
			// �P�_�O���O title �`�I (End)
		}   // End for ($i = 0; $i < $cnt; $i++)
		// �M�� title �`�I (End)
		return $lang;
	}

	function parseCourseGroup($node) {
		global $sysConn, $dbGroup, $new_log_msg, $update_log_msg;
		// �ˬd�Ƕi�Ӫ��ѼƬO���O�W�w������
		if (!is_object($node) || !$node->has_child_nodes())
			return '';

		// ��s WM_term_course (Begin)
		if ($node->node_name() == 'manifest') {
			$nodeID = 10000000;
		} else {
			$nodeID = '';
			$id = trim($node->get_attribute('id'));
			if (!empty($id))
				$nodeID = sysDecode($id);
			$lang = buildCaption($node);   // ���X�y�t
			$caption = addslashes(serialize($lang));

			/**
			 * �s�W�Χ�s WM_term_course ���s�ժ����
			 *     �s�W�G�Ū��B���s�b�� ID �Τw�� ID �� ID ����
			 *     ��s�G�w�� ID �B ID ������
			 **/
			if (empty($nodeID) || !isset($dbGroup[$nodeID]) || $dbGroup[$nodeID]) {
				// �s�W
				dbNew('WM_term_course', '`caption`, `kind`, `status`', "'{$caption}', 'group', 1");
				$nodeID = $sysConn->Insert_ID();
				
				/* �] mysql5.7 ���ҫ�|�Nauto_increment �ܦ�1 �[�J���b*/
				if($nodeID < 10000001){
					$nodeID_auto = $nodeID + 10000000;
					dbSet('WM_term_course',"course_id = '{$nodeID_auto}'","course_id = {$nodeID}");		
					$sysConn->Execute('ALTER TABLE WM_term_course AUTO_INCREMENT ='.($nodeID_auto+1));
					$nodeID = $nodeID_auto;
				}
				/* �] mysql5.7 ���ҫ�|�Nauto_increment �ܦ�1 �[�J���b*/
				
				$new_log_msg .= $new_log_msg == '' ? $nodeID : (', ' . $nodeID);
			} else {
				// ��s
				dbSet('WM_term_course', "caption='{$caption}'", "course_id={$nodeID}");
				if ($sysConn->Affected_Rows())
					$update_log_msg .= $update_log_msg == '' ? $nodeID : (', ' . $nodeID);
			}
			$dbGroup[$nodeID] = true;
			$node->set_attribute('id', $nodeID);
		}
		// ��s WM_term_course (End)

		// ��s WM_term_group (Begin)
		$order = 0;   // �s�դ��A�l�s�թνҵ{��������
		$childs = $node->child_nodes();
		$cnt = count($childs);
		for ($i = 0; $i < $cnt; $i++) {
			$child = $childs[$i];
			if ($child->node_type() != 1) continue;
			if (($child->node_name() == 'courses') || ($child->node_name() == 'course')) {
				if ($child->node_name() == 'courses') {
					$childID = parseCourseGroup($child);
				} else {
					$id = trim($child->get_attribute('id'));
					$childID = (empty($id)) ? '' : sysDecode($id);
				}
				if (empty($childID)) continue;
				dbNew('WM_term_group', '`parent`, `child`, `permute`', "{$nodeID}, {$childID}, {$order}");
				$order++;
			}
		}

		//echo $cnt . '<br />';
		if ($order == 0)
			dbNew('WM_term_group', '`parent`, `child`, `permute`', "{$nodeID}, 0, 0");
		// ��s WM_term_group (End)

		return $nodeID;
	}
////////////////////////////////////////////////////////////////////////
	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$xmlDoc = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest><result>1</result></manifest>';
			exit;
		}

		// ���X�ثe�Ҧ��ҵ{�s�ժ� ID
		$RS = dbGetStMr('WM_term_group', 'distinct parent', '1', ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$dbGroup[$RS->fields['parent']] = false;
				$RS->MoveNext();
			}
		}
		$RS = dbGetStMr('WM_term_course', '`course_id`', '`kind`="group"', ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$dbGroup[$RS->fields['course_id']] = false;
				$RS->MoveNext();
			}
		}

		// �p�G�u�O�B�z�ҵ{�s�աA�h�O�d��l�s�� begin
	    $RS = dbGetStMr('WM_term_group as G,WM_term_course as C',
						'G.parent,G.child',
						'G.child=C.course_id and C.kind="course" order by G.parent,G.permute,G.child',
						ADODB_FETCH_ASSOC);
	    $origin_groups = array();
		if ($RS)
		    while ($fields = $RS->FetchRow())
			    $origin_groups[$fields['parent']][] = $fields['child'];
        // �p�G�u�O�B�z�ҵ{�s�աA�h�O�d��l�s�� end

		// �M�����ݭn�� Tag
		$nodes = $xmlDoc->get_elements_by_tagname('ticket');
		for ($i = count($nodes) - 1; $i >= 0; $i--) {
			$pnode = $nodes[$i]->parent_node();
			$pnode->remove_child($nodes[$i]);
		}

		// �M�� WM_term_group
		dbDel('WM_term_group', 1);
		
		$new_log_msg = '';
		$update_log_msg = '';
		parseCourseGroup($xmlDoc->document_element());
		if ($new_log_msg != '')
			wmSysLog('0700300100', $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], 'new course group: '. $new_log_msg);
		if ($update_log_msg != '')
			wmSysLog('0700300200', $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], 'update course group: '. $update_log_msg);
		// �M�� WM_term_course �S���Ϊ� group
		reset($dbGroup);
		$del_log_msg = '';
		if (is_array($dbGroup)) {
			foreach ($dbGroup as $key => $val) {
				if ($val) {
				    // ��ҵ{�ɦ^��
				    if (is_array($origin_groups[$key]))
				    {
				        $order = 10000;
				        foreach ($origin_groups[$key] as $child)
				        {
				            dbNew('WM_term_group', '`parent`, `child`, `permute`', "{$key}, {$child}, {$order}");
							$order++;
						}
					}
				} else {
					dbDel('WM_term_course', "course_id={$key}");
					if ($key != '10000000') $del_log_msg .= $del_log_msg == '' ? $key : (', ' . $key);
				}
			}
		}
		if ($del_log_msg != '')
			wmSysLog('0700300300', $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], 'delete course group: '. $del_log_msg);
		
		header("Content-type: text/xml");
		$result = "<manifest><ticket>{$ticket}</ticket><result>0</result></manifest>";
		echo $result;
	} else {
		header("Content-type: text/xml");
		$result = "<manifest><ticket>{$ticket}</ticket><result>1</result></manifest>";
		echo $result;
	}
?>
