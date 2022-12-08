<?php
	/**
	 * �x�s�ҵ{�s��
	 *
	 * �إߤ���G2002/12/11
	 * @author  ShenTing Lin
	 * @version $Id: class_group_save.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/class_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');

	$sysSession->cur_func='2400100100';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}

	// PS�G�p���ֹ� DB ���s��
	/**
	 * �����ܼ�
	 **/
	$dbGroup = array();
	
	/**
	 * �إ߰Q�תO
	 **/
	function addBoards($class_id, $bname) {
		global $sysConn;
		$RS = dbGetStSr('WM_bbs_boards', 'count(*) as cnt', '1', ADODB_FETCH_ASSOC);
		if ($RS['cnt'] == 0) {
			$RS = dbNew('WM_bbs_boards', 'board_id', '1000000000');
		}
		$boardName = addslashes(serialize($bname));
		$board_id = 0;
		// �إ߰Q�תO
		$RS = dbNew('WM_bbs_boards', 'bname, owner_id', "'{$boardName}', {$class_id}");

		if ($RS) {
			$board_id = $sysConn->Insert_ID();

			// �[�J WM_term_subject
			dbNew('WM_term_subject','course_id,board_id',"$class_id, $board_id");
		}

		return $board_id;
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
						$child_value = trim($child->node_value());
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

	function parseClassGroup($node) {
		global $sysConn, $dbGroup,$sysSession,$MSG, $_SERVER;
		// �ˬd�Ƕi�Ӫ��ѼƬO���O�W�w������
		if (!is_object($node) || !$node->has_child_nodes())
			return '';

		// ��s WM_class_main (Begin)
		if ($node->node_name() == 'manifest') {
			$nodeID = 1000000;
		} else {
			$nodeID  = $node->get_attribute('id');
			$lang    = buildCaption($node);   // ���X�y�t
			$caption = addslashes(serialize($lang));

			$nodes   = $node->child_nodes();

            $cnt     = count($nodes);
            // for begin
            for ($i = 0; $i < $cnt; $i++) {

            	// if begin
                if (($nodes[$i]->node_type() == 1) && ($nodes[$i]->node_name() != 'title')) {

					// switch begin
                    switch ($nodes[$i]->node_name()){
                        case 'dep_id':
                                if ($nodes[$i]->has_child_nodes()){
                                    $child = $nodes[$i]->first_child();
                		            $dep_id_value = $child->node_value();
                                }else{
                                    $dep_id_value = '';
                                }
                                break;
                        case 'director':
                                if ($nodes[$i]->has_child_nodes()){
                                    $child = $nodes[$i]->first_child();
                		            $director_value = $child->node_value();
                                }else{
                                    $director_value = '';
                                }
                                break;
                        case 'people_limit':
                                if ($nodes[$i]->has_child_nodes()){
                                    $child = $nodes[$i]->first_child();
                		            $people_limit_value = $child->node_value();
            		            }else{
            		                $people_limit_value = 0;
            		            }
                                break;
                        case 'quota_limit':
                                if ($nodes[$i]->has_child_nodes()){
                                    $child = $nodes[$i]->first_child();
                		            $quota_limit_value = $child->node_value();
                                }else{
                                    $quota_limit_value = 102400;
                                }
                                break;
                    }
                    // switch end

                }
                // if end

                if ( ($dep_id_value == '') && ($director_value == '') && ($people_limit_value == '') && ($people_limit_value == '')){
                	$people_limit_value = 0;
                	$quota_limit_value = 102400;
	            }
			}
			// for end

			/**
			 * �s�W�Χ�s WM_class_main ���s�ժ����
			 *     �s�W�G�Ū��B���s�b�� ID �Τw�� ID �� ID ����
			 *     ��s�G�w�� ID �B ID ������
			 **/
		    if (empty($nodeID)) {
				// �s�W
				dbNew('WM_class_main', 'caption,dep_id,director,people_limit,quota_limit', "'$caption','$dep_id_value','$director_value',$people_limit_value,$quota_limit_value");
				$nodeID = $sysConn->Insert_ID();
				
								
				/* �] mysql5.7 ���ҫ�|�Nauto_increment �ܦ�1 �[�J���b*/
				if($nodeID < 1000001){
					$nodeID_auto = $nodeID + 1000000;
					dbSet('WM_class_main',"class_id = '{$nodeID_auto}'","class_id = {$nodeID}");		
					$sysConn->Execute('ALTER TABLE WM_class_main AUTO_INCREMENT ='.($nodeID_auto+1));
					$nodeID = $nodeID_auto;
				}
				/* �] mysql5.7 ���ҫ�|�Nauto_increment �ܦ�1 �[�J���b*/
				
				wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], '�s�WWM_class_main���s�ո�� class_id = ' . $nodeID);

    			// �N�Z�Ū��ؿ��x�s���Ʈw
    			$RS = dbSet('WM_class_main', "path=''", "class_id={$nodeID}");

    			// �إ߯Z�ŰQ�תO
    			$bname['Big5']        = stripslashes($MSG['discuss']['Big5']);
    			$bname['en']          = stripslashes($MSG['discuss']['en']);
    			$bname['EUC-JP']      = stripslashes($MSG['discuss']['EUC-JP']);
    			$bname['user_define'] = stripslashes($MSG['discuss']['user_define']);
    			$bname['GB2312']      = stripslashes($MSG['discuss']['GB2312']);

    			$board_id1            = addBoards($nodeID, $bname);

    			// �إ߯Z�Ť��i�O
    			$bname['Big5']        = stripslashes($MSG['bulletin']['Big5']);
    			$bname['en']          = stripslashes($MSG['bulletin']['en']);
    			$bname['EUC-JP']      = stripslashes($MSG['bulletin']['EUC-JP']);
    			$bname['user_define'] = stripslashes($MSG['bulletin']['user_define']);
    			$bname['GB2312']      = stripslashes($MSG['bulletin']['GB2312']);

    			$board_id2            = addBoards($nodeID, $bname);

    			if (!$board_id2) $board_id2 = 'NULL';

                // �x�s�Q�תO�� board_id
			    dbSet('WM_class_main', "discuss={$board_id1}, bulletin={$board_id2}", "class_id={$nodeID}");
			} else {
				// ��s
				$update_sqls = 'update WM_class_main ' .
							   " set  caption='" . $caption . "'," .
							   "dep_id='$dep_id_value'," .
							   "director='$director_value'," .
							   "people_limit=$people_limit_value," .
							   "quota_limit=$quota_limit_value" .
							   " where class_id={$nodeID}";

				$sysConn->Execute($update_sqls);

				wmSysLog('2400100200', $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], '��sWM_class_main���s�ո�� class_id = ' . $nodeID);
			}
			$dbGroup[$nodeID] = true;
			$node->set_attribute('id', $nodeID);

		}
		// ��s WM_class_main (End)

// 2005-2-22 begin

			// ��s WM_class_group (Begin)
			$order = 0;   // �s�դ��A�l�s�թνҵ{��������
			$childs = $node->child_nodes();
			$cnt = count($childs);
			for ($i = 0; $i < $cnt; $i++) {
				$child = $childs[$i];
				if ($child->node_type() != 1) continue;
				if (($child->node_name() == 'classes') || ($child->node_name() == 'class')) {
					if ($child->node_name() == 'classes') {
						$childID = parseClassGroup($child);
					} else {
						$childID = $child->get_attribute('id');
					}
					if (empty($childID)) continue;
					dbNew('WM_class_group', '`parent`, `child`, `permute`', "{$nodeID}, {$childID}, {$order}");

					$order++;
				}
			}
			wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], '�s�WWM_class_group');
			//echo $cnt . '<br />';
			if ($order == 0) {
				dbNew('WM_class_group', '`parent`, `child`, `permute`', "{$nodeID}, 0, 0");
			}
			// ��s WM_class_group (End)

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
		$RS = dbGetStMr('WM_class_group', 'distinct parent', '1', ADODB_FETCH_ASSOC);
		while (!$RS->EOF) {
			$dbGroup[$RS->fields['parent']] = false;
			$RS->MoveNext();
		}

		// �M�����ݭn�� Tag
		$nodes = $xmlDoc->get_elements_by_tagname('ticket');
		for ($i = count($nodes) - 1; $i >= 0; $i--) {
			$pnode = $nodes[$i]->parent_node();
			$pnode->remove_child($nodes[$i]);
		}

		// �M�� WM_class_group
    	dbDel('WM_class_group', 1);

		parseClassGroup($xmlDoc->document_element());

		// �M�� WM_class_main �S���Ϊ� group
		reset($dbGroup);

		header("Content-type: text/xml");
		echo '<manifest><result>0</result></manifest>';
	} else {
		header("Content-type: text/xml");
		echo '<manifest><result>1</result></manifest>';
	}
?>
