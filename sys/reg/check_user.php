<?php
	/**
	 * �ˬd�b�����L���b�ϥ�
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
	 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
	 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
	 *
	 * @package     WM3
	 * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
	 * @copyright   2000-2005 SunNet Tech. INC.
	 * @version     CVS: $Id: check_user.php,v 1.1 2010/02/24 02:40:20 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2005-09-22
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '400200100';
	$sysSession->restore();

	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}
	// �ܼƫŧi begin
	// �ܼƫŧi end

	// �D�{�� begin
	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	header('Content-type: text/xml');
	echo '<' , '?xml version="1.0" encoding="UTF-8" ?' , '>' , "\n";
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$xmlDoc = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			echo '<manifest><result>1</result></manifest>';
			exit;
		}

		$user = getNodeValue($xmlDoc,'exist_user');

		$result_id = checkUsername($user);

		echo '<manifest><result>' , $result_id , '</result></manifest>';
	} else {
		echo '<manifest><result>F</result></manifest>';
	}

	// �D�{�� end
?>