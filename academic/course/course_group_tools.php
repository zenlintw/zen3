<?php
	/**
	 * 課程群組管理工具
	 * $Id: course_group_tools.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/wm_toolbar.php');
	require_once(sysDocumentRoot . '/lang/course_group.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '700300400';
	$sysSession->restore();

	if (!aclVerifyPermission(700300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}
	
	$btns = array(
			array($MSG['toolbtn01'][$sysSession->lang], 'icon_save.gif',     'parent.main.saveGP()'          ),
			array('-'                                 , ''             ,     ''                              ),
			array($MSG['toolbtn02'][$sysSession->lang], 'icon_new.gif',      'parent.main.addNode(true)'     ),
			array($MSG['toolbtn03'][$sysSession->lang], 'icon_insert.gif',   'parent.main.addNode(false)'    ),
			array($MSG['toolbtn04'][$sysSession->lang], 'icon_property.gif', 'parent.main.displaySetPage(-1)'),
			array($MSG['toolbtn05'][$sysSession->lang], 'icon_cut.gif',      'parent.main.cpmvNode(false)'   ),
			array($MSG['toolbtn06'][$sysSession->lang], 'icon_copy.gif',     'parent.main.cpmvNode(true)'    ),
			array($MSG['toolbtn07'][$sysSession->lang], 'icon_paste.gif',    'parent.main.pasteNode()'       ),
			array($MSG['toolbtn12'][$sysSession->lang], 'icon_delete.gif',   'parent.main.delNode(false)'    ),
			array($MSG['toolbtn08'][$sysSession->lang], 'icon_left.gif',     'parent.main.moveNode(3)'       ),
			array($MSG['toolbtn09'][$sysSession->lang], 'icon_right.gif',    'parent.main.moveNode(4)'       ),
			array($MSG['toolbtn10'][$sysSession->lang], 'icon_up.gif',       'parent.main.moveNode(1)'       ),
			array($MSG['toolbtn11'][$sysSession->lang], 'icon_down.gif',     'parent.main.moveNode(2)'       ),
			array($MSG['toolbtn13'][$sysSession->lang], 'icon_all_s.gif',    'parent.main.selectPoint(1)'    ),
			array($MSG['toolbtn14'][$sysSession->lang], 'icon_all_d.gif',    'parent.main.selectPoint(2)'    ),
			// array($MSG['toolbtn15'][$sysSession->lang], 'icon_invert.gif',   'parent.main.selectPoint(3)'    ),
		);

	$js = <<< BOF
	parent.main.loadGP();
BOF;

	$extra = '<a href="javascript:;"  onclick="do_func(\'list\', \'\'); return false;" class="link_fnt01" title="' . $alt . '">' . $btn . '</a>';
	$func  = 'parent.main.selectRang(val1, val2);';
	showXHTML_toolbar($MSG['toolbar_title'][$sysSession->lang], '', $btns, $js, true, $func, 'icon_book.gif', true, $MSG['toolbar_title'][$sysSession->lang]);
?>
