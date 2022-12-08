<?php
	/**
	 * �ڪ��ҵ{��ݵ{��
	 *
	 * @since   2004/09/16
	 * @author  ShenTing Lin
	 * @version $Id: mycourse_func.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/learn/mycourse/mycourse_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	// $sysSession->cur_func='2500100100';
	// $sysSession->restore();
	if (!aclVerifyPermission(2500100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	/**
	 * ���üҲ�
	 * @param string $id : �Ҳժ��s��
	 **/
	function modClose($id) {
		global $myConfig;
		$myConfig->setValues($id, 'visibility', 'hidden');
	}

	/**
	 * �ƦC�Ҳզ�m
	 * @param string $cid : ��m�b���� ID ����
	 * @param string $did : �즲������s��
	 **/
	function modPosition($cid, $did) {
		global $myConfig;

		$cid = trim($cid);
		$did = trim($did);

		
		$org['Col1'] = $myConfig->assoc_ary['MyConfig_Col1'];
		$org['Col2'] = $myConfig->assoc_ary['MyConfig_Col2'];
		$ary = array(
			'Col1' => array(),
			'Col2' => array()
		);
		if ($cid == 'lt') $ary['Col1'][] = $did;
		if ($cid == 'rt') $ary['Col2'][] = $did;
		foreach ($org['Col1'] as $val) {
			$val = trim($val);
			if ($did == $val) continue;

			$ary['Col1'][] = $val;
			if ($cid == $val) $ary['Col1'][] = $did;
		}
		foreach ($org['Col2'] as $val) {
			$val = trim($val);
			if ($did == $val) continue;

			$ary['Col2'][] = $val;
			if ($cid == $val) $ary['Col2'][] = $did;
		}
		$myConfig->setValues('MyConfig_Col1', '', $ary['Col1'], true);
		$myConfig->setValues('MyConfig_Col2', '', $ary['Col2'], true);
	}

	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			exit;
		}
		$xpath = xpath_new_context($dom);
		$obj = xpath_eval($xpath, '//action/text()');
		$nodes = $obj->nodeset;
		$action = trim($nodes[0]->node_value());
		switch ($action) {
			case 'close':
				$obj   = xpath_eval($xpath, '//curid/text()');
				$nodes = $obj->nodeset;
				$id    = trim($nodes[0]->node_value());
				modClose($id);
				break;
			case 'post' :
				$obj    = xpath_eval($xpath, '//curid/text()');
				$nodes  = $obj->nodeset;
				$curid  = trim($nodes[0]->node_value());
				$obj    = xpath_eval($xpath, '//dragid/text()');
				$nodes  = $obj->nodeset;
				$dragid = trim($nodes[0]->node_value());
				modPosition($curid, $dragid);
				break;
			default:
		}
		$myConfig->store();
	}
?>
