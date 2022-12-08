<?php
/**
 * sysbar 管理工具
 * @version $Id: sysbar_tools.php,v 1.1 2010/02/24 02:38:46 saly Exp $
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/wm_toolbar.php');
require_once(sysDocumentRoot . '/lang/sysbar_config.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

if (!aclVerifyPermission(1300300200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

if (!isset($Theme))
    $Theme = "/theme/{$sysSession->theme}/{$sysSession->env}";

$btns = array(
    array(
        $MSG['save'][$sysSession->lang],
        'icon_save.gif',
        'do_func(5)'
    ),
    array(
        '-',
        '',
        ''
    ),
    array(
        $MSG['folder_add'][$sysSession->lang],
        'icon_new.gif',
        'do_func(1)'
    ),
    array(
        $MSG['folder_modify'][$sysSession->lang],
        'icon_property.gif',
        'do_func(4)'
    ),
    array(
        $MSG['folder_delete'][$sysSession->lang],
        'icon_delete.gif',
        'do_func(2)'
    ),
    array(
        $MSG['folder_show_hide'][$sysSession->lang],
        'icon_show.gif',
        'do_func(3)'
    ),
    array(
        $MSG['folder_mv_left'][$sysSession->lang],
        'icon_left.gif',
        'do_func(6)'
    ),
    array(
        $MSG['folder_mv_right'][$sysSession->lang],
        'icon_right.gif',
        'do_func(7)'
    )
);

if (SYSBAR_LEVEL != 'root') {
    $btns[] = array(
        $MSG['folder_reload'][$sysSession->lang],
        'icon_reload.gif',
        'do_func(8)'
    );
}

$js = <<< BOF
function getTarget() {
    var obj = null;
    switch (this.name) {
        case "s_main":
            obj = parent.s_catalog;
            break;
        case "c_main":
            obj = parent.c_catalog;
            break;
        case "main":
            obj = parent.catalog;
            break;
        case "s_catalog":
            obj = parent.s_main;
            break;
        case "c_catalog":
            obj = parent.c_main;
            break;
        case "catalog":
            obj = parent.main;
            break;
    }
    return obj;
}

function do_func(val) {
    var obj = getTarget();
    if ((typeof(obj) != "object") || (obj == null)) return false;
    if (typeof(obj.doFunc) == "function") obj.doFunc(val);
}
BOF;
showXHTML_toolbar($MSG['toolbar'][$sysSession->lang], '', $btns, $js); //, $selRang=false, $rangFunc='', $icon='icon_book.gif', $showIcon=true, $headTitle='')
