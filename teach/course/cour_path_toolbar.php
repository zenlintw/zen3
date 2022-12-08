<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/05/12                                                            *
	 *		work for  : exam maintain toolbar                                                           *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/wm_toolbar.php');
	require_once(sysDocumentRoot . '/lang/teach_course.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '700500200';
	$sysSession->restore();
	if (!aclVerifyPermission(700500200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	$btms = array(
        array($MSG['toolbtm05'][$sysSession->lang], 'icon_save.gif', 'parent.c_main.executing(5)'),
        array($MSG['toolbtm18'][$sysSession->lang], 'icon_reload.gif', 'parent.c_main.executing(18)'),
        array('-', '', ''),
        array($MSG['toolbtm01'][$sysSession->lang], 'icon_new.gif', 'parent.c_main.executing(1)'),
        array($MSG['toolbtm02'][$sysSession->lang], 'icon_insert.gif', 'parent.c_main.executing(2)'),
        array($MSG['toolbtm03'][$sysSession->lang], 'icon_property.gif', 'parent.c_main.executing(3)'),
        array($MSG['toolbtm04'][$sysSession->lang], 'icon_delete.gif', 'parent.c_main.executing(4)'),
        array($MSG['toolbtm07'][$sysSession->lang], 'icon_cut.gif', 'parent.c_main.executing(7)'),
        array($MSG['toolbtm06'][$sysSession->lang], 'icon_copy.gif', 'parent.c_main.executing(6)'),
        array($MSG['toolbtm08'][$sysSession->lang], 'icon_paste.gif', 'parent.c_main.executing(8)'),
        array($MSG['title_visibility'][$sysSession->lang], 'icon_show.gif', 'parent.c_main.executing(17)'),
        array($MSG['toolbtm09'][$sysSession->lang], 'icon_left.gif', 'parent.c_main.moveNodeByUser=true;parent.c_main.executing(9)'),
        array($MSG['toolbtm10'][$sysSession->lang], 'icon_right.gif', 'parent.c_main.moveNodeByUser=true;parent.c_main.executing(10)'),
        array($MSG['toolbtm11'][$sysSession->lang], 'icon_up.gif', 'parent.c_main.moveNodeByUser=true;parent.c_main.executing(11)'),
        array($MSG['toolbtm12'][$sysSession->lang], 'icon_down.gif', 'parent.c_main.moveNodeByUser=true;parent.c_main.executing(12)'),
        array($MSG['toolbtm14'][$sysSession->lang], 'icon_import.gif', 'parent.c_main.executing(14)'),
        array($MSG['toolbtm13'][$sysSession->lang], 'icon_export.gif', 'parent.c_main.executing(13)'),
        array($MSG['toolbtm17'][$sysSession->lang], 'icon_import.gif', 'parent.c_main.executing(19)'),
        array($MSG['toolbtm15'][$sysSession->lang], 'icon_all_s.gif', 'parent.c_main.executing(15)'),
        array($MSG['toolbtm16'][$sysSession->lang], 'icon_all_d.gif', 'parent.c_main.executing(16)')
    );
    // 檢查是否啟用 LCMS
    if (defined('sysLcmsEnable') && !sysLcmsEnable) {
        // 沒有啟用 LCMS 時，則移除 LCMS 的功能按鈕
        array_splice($btms, -3, 1);
    }

    showXHTML_toolbar($MSG['toolbar'][$sysSession->lang], $extra, $btms, $js, true, 'parent.c_main.selectRang(val1,val2);', 'icon_book.gif'); //, $showIcon=true, $headTitle='')
?>
