<?php
   /**
    * /�줽��/�ҵ{�޲z/�ҵ{²��/�x�s�]�w
    *
    * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
    *
    * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
    * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
    * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
    *
    * @package     WM3
    * @author      Edi Chen <edi@sun.net.tw>
    * @copyright   2000-2005 SunNet Tech. INC.
    * @version     CVS: $Id: cour_introduce_save.php,v 1.1 2010/02/24 02:40:29 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2005-12-15
    */

// {{{ �禡�w�ޥ� begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/cour_introduce.php');
// }}} �禡�w�ޥ� end

// {{{ �`�Ʃw�q begin

// }}} �`�Ʃw�q end
    
// {{{ �ܼƫŧi begin
    $cour_intro = array('C'	=>	'cour_intro', 
    							'R'	=>	'cour_arrange', 
    							'T'	=>	'teach_intro'
    							);
// }}} �ܼƫŧi end

// {{{ ��ƫŧi begin

// }}} ��ƫŧi end

// {{{ �D�{�� begin

// }}} �D�{�� end
	foreach ($cour_intro as $func)
		if (!isSet($_POST[$func]))	
			die($MSG['Error'][$sysSession->lang]);
	
	// ���o������]�w��
	$rs = dbGetStMr('WM_term_introduce', 'intro_type, content', 'course_id='.$sysSession->course_id, ADODB_FETCH_ASSOC);
	$contents = array();
	if ($rs)
		while ($row = $rs->FetchRow()) {
			$contents[$row['intro_type']] = $row['content'];
		}
		
	foreach ($cour_intro as $k => $v)	{
		$type = $_POST[$cour_intro[$k]];
		$modify = false; $found = false;
		if ($contents[$k]) {	// �w�g�����
			if ($xmldoc = domxml_open_mem($contents[$k])) {
				$root = $xmldoc->first_child();
				$nodes = $root->child_nodes();
				foreach ($nodes as $node) {
					if ($node->node_type() != 1) continue;
					if ($node->get_attribute('type') == $type) $found = true;
					if ($node->get_attribute('type') == $type && $node->get_attribute('checked') != 'true') {
						$node->set_attribute('checked', 'true');
						$modify = true;
					}
					else if ($node->get_attribute('type') != $type && $node->get_attribute('checked') == 'true') {
						$node->set_attribute('checked', 'false');
						$modify = true;
					}
				}
				
				if (!$found) {	// �S��������node
					$newNode = $xmldoc->create_element('intro');
					$newNode->set_attribute('type', $type);
					$newNode->set_attribute('checked', 'true');
					$root->append_child($newNode);
					$modify = true;
				}
			}
			if ($modify) {	// �p�G���ܧ󪺤~��s
				$xml_content = $sysConn->qstr($xmldoc->dump_mem(true));
				dbSet('WM_term_introduce', 'content='.$xml_content.'', 'course_id=' . $sysSession->course_id . ' and intro_type="'.$k.'"');
			}
		}
		else {	// �|�L���
			$xml_content = <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
	<manifest>
		<intro type="{$type}" checked="true"></intro>
	</manifest>
EOF;
			$xml_content = $sysConn->qstr($xml_content);
			dbNew('WM_term_introduce', 'course_id, intro_type, content', "{$sysSession->course_id}, '{$k}', {$xml_content}");
		}
	}
	
	echo <<< EOB
	<script language="javascript">
		alert("{$MSG['save_success'][$sysSession->lang]}");
		location.replace('cour_introduce.php');
	</script>
EOB;
?>
