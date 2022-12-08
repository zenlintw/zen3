<?php
	/**
	 * 訊息中心的目錄管理工具
	 *
	 * 建立日期：2003/10/16
	 * @author  ShenTing Lin
	 * @version $Id: msg_manage_tools.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/wm_toolbar.php');
	require_once(sysDocumentRoot . '/lang/msg_center.php');
	require_once(sysDocumentRoot . '/message/lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	// $sysSession->cur_func = '2200100200';
	// $sysSession->restore();
	if (!aclVerifyPermission(2200100200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$folder_id = getFolderId();
	if ($sysSession->cur_func == $msgFuncID['notebook']) {
		$btn = $MSG['return_notebook'][$sysSession->lang];
		$alt = $MSG['goto_nodebook'][$sysSession->lang];
	} else {
		$btn = $MSG['return_msg_center'][$sysSession->lang];
		$alt = $MSG['goto_msg_center'][$sysSession->lang];
	}

	$btns = array(
			array($MSG['btn_save'][$sysSession->lang]           , 'icon_save.gif'    , 'do_func(\'save\', \'\')"'    ),
			array('-'                                           , ''                 , ''                            ),
			array($MSG['btn_new_folder'][$sysSession->lang]     , 'icon_new.gif'     , 'do_func(\'add\', \'1\')"'    ),
			array($MSG['btn_insert_folder'][$sysSession->lang]  , 'icon_insert.gif'  , 'do_func(\'add\', \'0\')"'    ),
			array($MSG['btn_folder_property'][$sysSession->lang], 'icon_property.gif', 'do_func(\'set\', \'-1\')'    ),
			array($MSG['del_folder'][$sysSession->lang]         , 'icon_delete.gif'  , 'do_func(\'delete\', \'\')'   ),
			array($MSG['btn_cut'][$sysSession->lang]            , 'icon_cut.gif'     , 'do_func(\'copy_cut\', \'0\')'),
			array($MSG['btn_copy'][$sysSession->lang]           , 'icon_copy.gif'    , 'do_func(\'copy_cut\', \'1\')'),
			array($MSG['btn_paste'][$sysSession->lang]          , 'icon_paste.gif'   , 'do_func(\'paste\', \'\')'    ),
			array($MSG['btn_left'][$sysSession->lang]           , 'icon_left.gif'    , 'do_func(\'move\', \'3\')'    ),
			array($MSG['btn_right'][$sysSession->lang]          , 'icon_right.gif'   , 'do_func(\'move\', \'4\')'    ),
			array($MSG['btn_up'][$sysSession->lang]             , 'icon_up.gif'      , 'do_func(\'move\', \'1\')'    ),
			array($MSG['btn_down'][$sysSession->lang]           , 'icon_down.gif'    , 'do_func(\'move\', \'2\')'    ),
			array($MSG['select_all'][$sysSession->lang]         , 'icon_all_s.gif'   , 'do_func(\'select\', \'1\')'  ),
			array($MSG['select_cancel'][$sysSession->lang]      , 'icon_all_d.gif'   , 'do_func(\'select\', \'2\')'  ),
			// array($MSG['select_invert'][$sysSession->lang]      , 'icon_invert.gif'  , 'do_func(\'select\', \'3\')'  ),
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

	$extra = '<br/ >'.str_repeat('&nbsp;', 5).'<a href="javascript:;"  onclick="do_func(\'list\', \'\'); return false;" class="link_fnt01" title="' . $alt . '">' . $btn . '</a>';
	showXHTML_toolbar($MSG['mage_tools'][$sysSession->lang], $extra, $btns, $js, true, 'do_func("selectRang", ary);', 'icon_book.gif'); //, $showIcon=true, $headTitle='')
?>
