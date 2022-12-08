<?php
    /**
     * �������s���� login_key
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
     * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
     * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
     *
     * @package     WM3
     * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
     * @copyright   2000-2006 SunNet Tech. INC.
     * @version     CVS: $Id: re_gen_loginkey.php,v 1.1 2010/02/24 02:40:20 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2006-08-09
     */

    ignore_user_abort(true);
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    while (@ob_end_clean());
    require_once(sysDocumentRoot . '/lib/xajax/xajax.inc.php');

	$xajax_rgk = new xajax('/sys/door/re_gen_loginkey.php');
	$xajax_rgk->registerFunction('reGenLoginKey');

	function reGenLoginKey()
	{
		$uid = md5(uniqid(rand(),1));
		$login_key = md5(sysSiteUID . sysTicketSeed . $uid);
		dbDel('WM_prelogin', 'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(log_time) > 1200');
		dbNew('WM_prelogin', 'login_seed,uid,log_time', "'{$login_key}','{$uid}',NOW()");
		
        $objResponse = new xajaxResponse();
        $objResponse->addScript('if ((loginForm = document.getElementById("fmLogin")) != null) loginForm.login_key.value = "' . $login_key . '";');
        return $objResponse;
	}

    $xajax_rgk->processRequests();
?>
