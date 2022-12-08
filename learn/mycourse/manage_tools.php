<?php
	/**
	 * 訊息中心的目錄管理工具
	 *
	 * 建立日期：2003//
	 * @author  ShenTing Lin
	 * @version $Id: manage_tools.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/wm_toolbar.php');
	require_once(sysDocumentRoot . '/lang/mycourse.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	if (!aclVerifyPermission(2200100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$btns = array(
			array($MSG['toolbtn01'][$sysSession->lang], 'icon_save.gif'    , 'do_func(\'save\', \'\')'     ),
			array('-'                                 , ''                 , ''                            ),
			array($MSG['toolbtn02'][$sysSession->lang], 'icon_new.gif'     , 'do_func(\'add\', \'1\')'     ),
			array($MSG['toolbtn03'][$sysSession->lang], 'icon_insert.gif'  , 'do_func(\'add\', \'0\')'     ),
			array($MSG['toolbtn04'][$sysSession->lang], 'icon_property.gif', 'do_func(\'set\', \'-1\')'    ),
			array($MSG['toolbtn12'][$sysSession->lang], 'icon_delete.gif'  , 'do_func(\'delete\', \'\')'   ),
			array($MSG['toolbtn05'][$sysSession->lang], 'icon_cut.gif'     , 'do_func(\'copy_cut\', \'0\')'),
			array($MSG['toolbtn06'][$sysSession->lang], 'icon_copy.gif'    , 'do_func(\'copy_cut\', \'1\')'),
			array($MSG['toolbtn07'][$sysSession->lang], 'icon_paste.gif'   , 'do_func(\'paste\', \'\')'    ),
			array($MSG['toolbtn08'][$sysSession->lang], 'icon_left.gif'    , 'do_func(\'move\', \'3\')'    ),
			array($MSG['toolbtn09'][$sysSession->lang], 'icon_right.gif'   , 'do_func(\'move\', \'4\')'    ),
			array($MSG['toolbtn10'][$sysSession->lang], 'icon_up.gif'      , 'do_func(\'move\', \'1\')'    ),
			array($MSG['toolbtn11'][$sysSession->lang], 'icon_down.gif'    , 'do_func(\'move\', \'2\')'    ),
			array($MSG['toolbtn13'][$sysSession->lang], 'icon_all_s.gif'   , 'do_func(\'select\', \'1\')'  ),
			array($MSG['toolbtn14'][$sysSession->lang], 'icon_all_d.gif'   , 'do_func(\'select\', \'2\')'  ),
			// array($MSG['toolbtn15'][$sysSession->lang], 'icon_invert.gif'  , 'do_func(\'select\', \'3\')'  )
		);

	$js = <<< BOF
	function do_func(act, extra) {
		var obj = null;
		switch (this.name) {
			case "s_catalog": obj = parent.s_main; break;
			case "c_catalog": obj = parent.c_main; break;
			case "catalog"  : obj = parent.main;   break;
		}
		if (obj != null) obj.do_func(act, extra);
	}
BOF;

	$extra = '<br/ >'.str_repeat('&nbsp;', 5).'<a href="javascript:;"  onclick="do_func(\'list\', \'\'); return false;" class="cssAnchor" title="' . $MSG['btn_alt_return'][$sysSession->lang] . '">' . $MSG['btn_return'][$sysSession->lang] . '</a>';
	showXHTML_toolbar($MSG['title_toolbar'][$sysSession->lang], $extra, $btns, $js, true, 'do_func("selectRang", ary);', 'icon_book.gif'); //, $showIcon=true, $headTitle='')
?>
