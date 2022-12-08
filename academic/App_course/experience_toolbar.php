<?php
	/**
	 * 試聽課程工具列
	 *
	 * @since   2012/08/09
	 * @author  ShenTing Lin
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/wm_toolbar.php');
	require_once(sysDocumentRoot . '/lang/experience.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '700500200';
	$sysSession->restore();
		if (!aclVerifyPermission(700500200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	$js = <<< BOF
	function getTarget() {
		var obj = null;
		switch (this.name) {
			case "s_main": obj = parent.s_catalog; break;
			case "c_main": obj = parent.c_catalog; break;
			case "main"  : obj = parent.catalog;   break;
			case "s_catalog": obj = parent.s_main; break;
			case "c_catalog": obj = parent.c_main; break;
			case "catalog"  : obj = parent.main;   break;
		}
		return obj;
	}

	function doFunction(act) {
		var fm = getTarget();
		switch (act) {
		case 1: // save
			fm.save();
			break;
		case 2: // add
			fm.insert(false);
			break;
		case 3: // indert
			fm.insert(true);
			break;
		case 4: // edit
			fm.edit();
			break;
		case 5: // delete
			fm.remove();
			break;
		case 6: // visibility
			fm.visibility();
			break;
		case 7: // up
			fm.moveUp();
			break;
		case 8: // down
			fm.moveDown();
			break;
		case 9: // down
			fm.selectAll();
			break;
		case 10: // down
			fm.cancelAll();
			break;
		}
	}
BOF;


	$btns = array(
		array($MSG['btn_save'][$sysSession->lang]      , 'icon_save.gif'    , 'doFunction(1)' ),
		array('-'                                      , ''                 , ''              ),
		array($MSG['btn_add'][$sysSession->lang]       , 'icon_new.gif'     , 'doFunction(2)' ),
		array($MSG['btn_insert'][$sysSession->lang]    , 'icon_insert.gif'  , 'doFunction(3)' ),
		array($MSG['btn_edit'][$sysSession->lang]      , 'icon_property.gif', 'doFunction(4)' ),
		array($MSG['btn_delete'][$sysSession->lang]    , 'icon_delete.gif'  , 'doFunction(5)' ),
		array($MSG['btn_visibility'][$sysSession->lang], 'icon_show.gif'    , 'doFunction(6)' ),
		array($MSG['btn_move_up'][$sysSession->lang]   , 'icon_up.gif'      , 'doFunction(7)' ),
		array($MSG['btn_move_down'][$sysSession->lang] , 'icon_down.gif'    , 'doFunction(8)' ),
		array($MSG['btn_select_all'][$sysSession->lang], 'icon_all_s.gif'   , 'doFunction(9)' ),
		array($MSG['btn_cancel_all'][$sysSession->lang], 'icon_all_d.gif'   , 'doFunction(10)')
	);

	showXHTML_toolbar($MSG['toolbar'][$sysSession->lang], '', $btns, $js);