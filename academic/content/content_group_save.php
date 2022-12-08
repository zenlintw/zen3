<?php
	/**
	 * �x�s�ҵ{�s��
	 *
	 * �إߤ���G2002/12/11
	 * @author  ShenTing Lin
	 * @version $Id: content_group_save.php,v 1.1 2010/02/24 02:38:16 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');

	$sysSession->cur_func='2400100100';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}

	/**
	 * �ˬd�Ǯժ��t�θ�Ƨ����S���إߡA�Y�S���N�۰ʫإ�
	 **/
	function checkSchSysDir() {
		global $sysSession;
		$dir = sysDocumentRoot . "/base/{$sysSession->school_id}/system";
		if (!@is_dir($dir)) @mkdir($dir, 0755);

		$dir .= '/default';
		if (!@is_dir($dir)) @mkdir($dir, 0755);
	}

	/**
	 * �ƥ���Ӫ��ҵ{�s��
	 *     �O�d�Q�����ƥ��A�s���V�p���V�s
	 **/
	function backupFile($fname) {
		@unlink("{$fname}.bk9");
		for ($i = 8; $i >= 0; $i--) {
			@rename("{$fname}.bk{$i}", "{$fname}.bk" . ($i + 1));
		}
		@rename($fname, "{$fname}.bk0");
	}

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
						case 'big5'       : $lang['Big5']        = Filter_Spec_char(stripslashes($child_value)); break;
						case 'gb2312'     : $lang['GB2312']      = Filter_Spec_char(stripslashes($child_value)); break;
						case 'en'         : $lang['en']          = Filter_Spec_char(stripslashes($child_value)); break;
						case 'euc-jp'     : $lang['EUC-JP']      = Filter_Spec_char(stripslashes($child_value)); break;
						case 'user-define': $lang['user_define'] = Filter_Spec_char(stripslashes($child_value)); break;
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

	function parseContentGroup($node) {
		global $sysConn, $sysSession, $_SERVER;
		// �ˬd�Ƕi�Ӫ��ѼƬO���O�W�w������
		if (!is_object($node) || !$node->has_child_nodes())
			return '';

		// ��s WM_content (Begin)
		if ($node->node_name() == 'manifest') {
			$nodeID = 100000;
		} else {
			$nodeID  = intval($node->get_attribute('id'));
			$lang    = buildCaption($node);   // ���X�y�t
			$caption = addslashes(serialize($lang));
			$nodes   = $node->child_nodes();
            $cnt     = count($nodes);

			/**
			 * �s�W�Χ�s WM_content ���s�ժ����
			 *     �s�W�G�Ū��B���s�b�� ID �Τw�� ID �� ID ����
			 *     ��s�G�w�� ID �B ID ������
			 **/
		    if (empty($nodeID)) {
				// �s�W
				dbNew('WM_content', 'caption,kind,path', "'$caption','group', ''");
				$nodeID = $sysConn->Insert_ID();
				wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], '�s�WWM_content���s�ո�� content_id = ' . $nodeID);

    			// �N�Z�Ū��ؿ��x�s���Ʈw
    			dbSet('WM_content', "path='/base/{$sysSession->school_id}/content/{$nodeID}'", "content_id={$nodeID}");
			} else {
				// ��s
				dbSet('WM_content', "caption='{$caption}'", "content_id={$nodeID}");
			}
			$node->set_attribute('id', $nodeID);

		}
		// ��s WM_content (End)


			// ��s content_group (Begin)
			$order = 0;   // �s�դ��A�l�s�թνҵ{��������
			$childs = $node->child_nodes();
			$cnt = count($childs);
			for ($i = 0; $i < $cnt; $i++) {
				$child = $childs[$i];
				if ($child->node_type() != 1) continue;

				if (($child->node_name() == 'contents') || ($child->node_name() == 'content')) {
					if ($child->node_name() == 'contents') {
						$childID = intval(parseContentGroup($child));
					} else {
						$childID = intval($child->get_attribute('id'));
					}
					if (empty($childID)) continue;
					dbNew('WM_content_group', '`parent`, `child`, `permute`', "{$nodeID}, {$childID}, {$order}");

					$order++;
				}
			}
			wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], '�s�WWM_content_group');
			//echo $cnt . '<br />';
			if ($order == 0) {
				dbNew('WM_content_group', '`parent`, `child`, `permute`', "{$nodeID}, 0, 0");
			}
			// ��s WM_content_group (End)

		return $nodeID;
	}

////////////////////////////////////////////////////////////////////////
	header("Content-type: text/xml");
	echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if ($xmlDoc = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			// �M�����ݭn�� Tag
			$nodes = $xmlDoc->get_elements_by_tagname('ticket');
			for ($i = count($nodes) - 1; $i >= 0; $i--) {
				$pnode = $nodes[$i]->parent_node();
				$pnode->remove_child($nodes[$i]);
			}

			// �M�� WM_content_group
    		dbDel('WM_content_group', 1);

			parseContentGroup($xmlDoc->document_element());

			// �ƥ��ɮ�
			$filename = sysDocumentRoot . "/base/{$sysSession->school_id}/system/default/content_group.xml";
			checkSchSysDir();
			backupFile($filename);
			// �^�s xml ��
			$xmlDoc->dump_file($filename, false, true);

			die('<manifest><result>0</result></manifest>');
		}
	}

	die('<manifest><result>1</result></manifest>');
?>
