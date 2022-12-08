<?php
    /**************************************************************************************************
     *                                                                                                *
     *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
     *                                                                                                *
     *        Programmer: Wiseguy Liang                                                         *
     *        Creation  : 2003/05/12                                                            *
     *        work for  : exam maintain toolbar                                                           *
     *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
     *                                                                                                *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/wm_toolbar.php');
    require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    //ACL begin
    if (QTI_which == 'exam') {
        $sysSession->cur_func='1600200200';
        $sysSession->restore();
        if (!aclVerifyPermission(1600200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

        }
    }
    else if (QTI_which == 'homework') {
        $sysSession->cur_func='1700200200';
        $sysSession->restore();
        if (!aclVerifyPermission(1700200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

        }
    }
    else if (QTI_which == 'questionnaire') {
        $sysSession->cur_func='1800200200';
        $sysSession->restore();
        if (!aclVerifyPermission(1800200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

        }
    }
    //ACL end

    if (!defined('QTI_env'))
        list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
    else
        $topDir = QTI_env;

    $course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;
    $random_seat = md5(uniqid(rand(), true));
    $ticket = md5(sysTicketSeed . $course_id . $random_seat);

    $btms = array(
        array($MSG['toolbtm01'][$sysSession->lang], 'icon_new.gif',         'getTarget().executing(1)' ),// 增
        array($MSG['toolbtm02'][$sysSession->lang], 'icon_property.gif', 'getTarget().executing(2)' ),// 修
        array($MSG['toolbtm07'][$sysSession->lang], 'icon_show.gif',     'getTarget().executing(7)' ),// 發
        array($MSG['toolbtm15'][$sysSession->lang], 'icon_copy.gif',     'getTarget().executing(15)'),// 複
        array('SPACE'                                 , ''             ,     ''                         ),
        array($MSG['toolbtm16'][$sysSession->lang], 'icon-c.gif', 'getTarget().executing(50)', '', 'display: block;' ),// 進階
        array($MSG['toolbtm08'][$sysSession->lang], 'icon_all_d.gif', 'getTarget().executing(8)', 'display: none;', 'padding-left: 0.4em;'),// 清
        array($MSG['toolbtm03'][$sysSession->lang], 'icon_delete.gif',   'getTarget().executing(3)', 'display: none;', 'padding-left: 0.4em;'),// 刪
        array($MSG['toolbtm09'][$sysSession->lang], 'icon_up.gif',       'getTarget().executing(9)', 'display: none;', 'padding-left: 0.4em;'),// 上
        array($MSG['toolbtm10'][$sysSession->lang], 'icon_down.gif',     'getTarget().executing(10)', 'display: none;', 'padding-left: 0.4em;'),// 下
        array($MSG['toolbtm12'][$sysSession->lang], 'icon_import.gif',   'getTarget().executing(12)', 'display: none;', 'padding-left: 0.4em;'),// 入
        array($MSG['toolbtm11'][$sysSession->lang], 'icon_export.gif',   'getTarget().executing(11)', 'display: none;', 'padding-left: 0.4em;'),// 出
        // array($MSG['toolbtm04'][$sysSession->lang], 'icon_property.gif', 'getTarget().executing(4)' ),
//        array($MSG['toolbtm06'][$sysSession->lang], 'icon_property.gif', ((QTI_which == 'questionnaire')?'view_result()':'getTarget().executing(6)') ),// 批
//        array($MSG['toolbtm13'][$sysSession->lang], 'icon_all_s.gif',    'getTarget().executing(13)'),// 選
//        array($MSG['toolbtm14'][$sysSession->lang], 'icon_all_d.gif',    'getTarget().executing(14)'),// 消
//        array($MSG['toolbtm05'][$sysSession->lang], 'icon_save.gif',     'getTarget().executing(5)' ),// 存
     );

    $tmps = $MSG['select_one_item_first'][$sysSession->lang];
    $js = <<< EOF


    function getTarget() {
        var obj = null;
        switch (this.name) {
            case "s_main"   : obj = parent.s_catalog; break;
            case "c_main"   : obj = parent.c_catalog; break;
            case "main"     : obj = parent.catalog;   break;
            case "s_catalog": obj = parent.s_main; break;
            case "c_catalog": obj = parent.c_main; break;
            case "catalog"  : obj = parent.main;   break;
            case "s_sysbar" : obj = parent.s_main; break;
            case "c_sysbar" : obj = parent.c_main; break;
            case "sysbar"   : obj = parent.main;   break;
        }
        return obj;
    }

    function selectRang(ary) {
        var fn = getTarget();
        fn.selectRang(ary[0], ary[1]);
    }

    function view_result(){
        var fobj = document.getElementById('procForm');
        var obj = getTarget();
        var nodes = obj.document.getElementsByTagName('input');
        fobj.target = obj.name;
        var cur = obj.getSelElement(1);
        if (cur === false || cur === '') { alert('$tmps'); return; }
        fobj.lists.value = parseInt(nodes.item(parseInt(cur)).value);
        fobj.submit();
    }
EOF;

    showXHTML_toolbar($MSG['exam_toolbar'][$sysSession->lang], NULL, $btms, $js, false, 'selectRang(ary);', 'icon_book.gif'); //, $showIcon=true, $headTitle='')

    //only for questionnaire to view result
    if (QTI_which == 'questionnaire')
    {
        $random_seat = md5(uniqid(rand(), true));
        $ticket = md5(sysTicketSeed . $course_id . $random_seat);
        showXHTML_form_B('method="POST" action="exam_statistics_result.php" style="display: none;"', 'procForm');
            showXHTML_input('hidden', 'ticket', $ticket);
            showXHTML_input('hidden', 'referer', $random_seat);
            showXHTML_input('hidden', 'lists', '');
        showXHTML_form_E();
    }

?>
