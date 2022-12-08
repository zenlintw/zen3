<?php
    /**
     * 課程列表
     *
     * @since   2004/11/16
     * @author  ShenTing Lin
     * @version $Id: course_list.php,v 1.1 2010/02/24 02:38:19 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/lstable.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/academic/course/course_lib.php');
    require_once(sysDocumentRoot . '/lang/course_manage.php');

    // 同步 checkbox (Begin)
    $lsList  = trim($_POST['lsList']);
    $lsAry   = explode(',', $lsList);
    $tmp     = array();
    foreach ($lsAry as $val) {
        $val = trim($val);
        if (empty($val)) continue;
        $tmp[] = '"' . $val . '" : true';
    }
    $lsStr = (count($tmp) > 0) ? implode(',', $tmp) : '';
    $lsStr = 'var lsObj = {' . $lsStr . '};';
    // 同步 checkbox (End)
    $keyword = $_POST['keyword'];

    // 解碼 $gid
    $gid = 10000000;
    if (isset($_POST['ticket']) && trim($_POST['ticket'])!='') {
        $enc = trim($_POST['ticket']);
        $gid = sysDecode($enc);
        $gid = intval($gid);
        $genc = '&ticket=' . $enc;
    } else if (isset($_GET['ticket']) && trim($_GET['ticket'])!='') {
        $enc = trim($_GET['ticket']);
        $gid = sysDecode($enc);
        $gid = intval($gid);
        $genc = '&ticket=' . $enc;
    } else {
        $genc = '';
    }
    if ($gid == 0) $gid = 10000000;

    $schG = sysEncode(10000000);
    $nowG = sysEncode($gid);
    $js = <<< BOF
    var gpsch = "{$schG}", gpidx = "{$nowG}";
    {$lsStr}

    var MSG_SEL_REMOVE     = "{$MSG['msg_sel_del'][$sysSession->lang]}";
    var MSG_SEL_ACTION     = "{$MSG['msg_sel_act'][$sysSession->lang]}";
    var MSG_SEL_GP_TARGET  = "{$MSG['msg_sel_target'][$sysSession->lang]}";
    var MSG_NOT_DELTET     = "{$MSG['msg_cant_delete'][$sysSession->lang]}";
    var MSG_NOT_MOVE_TO    = "{$MSG['msg_cant_move_to'][$sysSession->lang]}";
    var MSG_GP_REMOVE      = "{$MSG['msg_confirm_del'][$sysSession->lang]}";
    var MSG_MOT_MOVE       = "{$MSG['msg_cant_move'][$sysSession->lang]}";
    var MSG_SEL_MOVE       = "{$MSG['msg_sel_move'][$sysSession->lang]}";
    var MSG_NOT_UP         = "{$MSG['msg_cant_up'][$sysSession->lang]}";
    var MSG_NOT_DOWN       = "{$MSG['msg_cant_down'][$sysSession->lang]}";
    var MSG_APPEND_SUCCESS = "{$MSG['msg_append_success'][$sysSession->lang]}";
    var MSG_MOVE_SUCCESS   = "{$MSG['msg_move_success'][$sysSession->lang]}";
    var MSG_DEL_SUCCESS    = "{$MSG['msg_del_success'][$sysSession->lang]}";
    var MSG_DELETE_COURSE  = "{$MSG['msg_delete_course'][$sysSession->lang]}";
    var MSG_DEL_CS_SUCCESS = "{$MSG['msg_delete_course_success'][$sysSession->lang]}";
    var MSG_SELECT_ALL     = "{$MSG['title18'][$sysSession->lang]}";
    var MSG_SELECT_CANCEL  = "{$MSG['title19'][$sysSession->lang]}";
    var MSG_SYSBAR         = "{$MSG['btn_sysbar'][$sysSession->lang]}";
    var MSG_UNLIMIT        = "{$MSG['msg_unlimit'][$sysSession->lang]}";
    var MSG_UNKNOW         = "{$MSG['msg_unknow'][$sysSession->lang]}";
    var MSG_UNITS          = "KB";
    var MSG_SYSTEM_ERROR   = "{$MSG['msg_system_error'][$sysSession->lang]}";
    var MSG_GROUP_ID_ERROR = "{$MSG['msg_group_id_error'][$sysSession->lang]}";
    var MSG_SELECT_MOVE    = "{$MSG['cour_mail_help2'][$sysSession->lang]}";
    
    var MSG_MODIFY_CAPACITY= "{$MSG['msg_modify_course_capacity'][$sysSession->lang]}";
    var MSG_MODIFY_REVIEW  = "{$MSG['msg_modify_course_review'][$sysSession->lang]}";
    
    var MSG_FILL_CAPACITY  = "{$MSG['fill_capacity'][$sysSession->lang]}";
    var MSG_FILL_NUMBERS   = "{$MSG['fill_numbers'][$sysSession->lang]}";

    // 課程狀態
    var cs_status = new Array(
        "{$MSG['cs_state_close'][$sysSession->lang]}",
        "{$MSG['cs_state_open_a'][$sysSession->lang]}",
        "{$MSG['cs_state_open_a_date'][$sysSession->lang]}",
        "{$MSG['cs_state_open_n'][$sysSession->lang]}",
        "{$MSG['cs_state_open_n_date'][$sysSession->lang]}",
        "{$MSG['cs_state_prepare'][$sysSession->lang]}"
    );

    /**
     * 取得另外的視窗
     * @return Object 另外的視窗 (other frame)
     **/
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

    var aryBtns = ["btnSel", "btnPage", "btnFirst", "btnPrev", "btnNext", "btnLast", "btnUp", "btnDw", "btnAppend", "btnMove", "btnRemove", "btnCapacity", "btnVerify"];
    window.onload = function () {
        var obj1 = null, obj2 = null;
        var txt = "";
        var re = /\/course_tree\.php$/ig;

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
        if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
        txt = "<manifest></manifest>";
        xmlDocs.loadXML(txt);

        try {
            obj1 = getTarget();
            if (!re.test(obj1.location.href.toString())) {
                obj1.location.href = "./course_tree.php";
            } else {
                obj = getTarget();
                if (typeof(obj.winFolderExpand) == "function") obj.winFolderExpand(true);
            }
        } catch (e) {
            obj1.location.href = "./course_tree.php";
        }

        obj1 = document.getElementById("tools12");
        obj2 = document.getElementById("tools21");
        if ((obj1 == null) || (obj2 == null)) return false;
        txt = obj1.innerHTML;
        for (var i = 0; i < aryBtns.length; i++) {
            txt = txt.replace(aryBtns[i] + "1", aryBtns[i] + "2");
        }
        obj2.innerHTML = txt;
        chgCheckbox();

        // 先隱藏詳細資料的列表
        obj = document.getElementById("DetailTable");
        if (obj != null) obj.style.display = "none";
        
        // 審核及容量的 fancybox 
            $("a#fancy-settings").fancybox({
                'padding'    :    0,
                'margin'    :    0,
                'modal': true
            });
    };

    window.onunload = function () {
        var obj = null;
        obj = getTarget();
        if (obj != null) {
            obj.location.href = "about:blank";
        }
    };
BOF;

    if (defined('Mail2Group')) {
        $js .= <<< BOF
    /**
     * 寄送信件
     **/
    function doFunc() {
        var cnt1 = 0;
        var idx1 = new Array();
        var obj = document.getElementById("mainFm");
        var nodes = null;

        if (obj == null) return false;
        nodes = obj.getElementsByTagName("input");
        for (var i = 0; i < nodes.length; i++) {
            if ((nodes[i].type != "checkbox") || nodes[i].id == "ck") continue;
            if (nodes[i].checked) idx1[idx1.length] = nodes[i].value;
        }
        cnt1 = idx1.length;
        if (cnt1 <= 0) {
            alert(MSG_SELECT_MOVE);
            return false;
        }
        obj = document.getElementById("editFm");
        obj.action = "course_group_mail.php";
        obj.csid.value = idx1;
        obj.submit();
        return true;
    }
BOF;
    }

    function showSubject($csid, $subject) {
        global $sysSession;
        $csid = sysEncode($csid);
        $lang = getCaption($subject);
        // $str  = '<a href="javascript:void(null);" class="cssAnchor" onclick="showDetail(\'' . $csid . '\'); return false;">' . $lang[$sysSession->lang] . '</a>';
        $caption = ((isset($lang[$sysSession->lang]))?$lang[$sysSession->lang]:'undefined');
        $str  = '<a href="javascript:void(null);" class="cssAnchor" onclick="editCourse(\'' . $csid . '\'); return false;">' . $caption . '</a>';
        $str  = divMsg(150, $str, $caption);
        return $str;
    }

    function showCheckBox($val) {
        global $lsAry;
        $val = sysEncode($val);
        $ck = in_array($val, $lsAry) ? ' checked="checked"' : '';
        showXHTML_input('checkbox', '', $val, '', 'onclick="chgCheckbox(); event.cancelBubble=true;"' . $ck);
    }

    function showModifyIcon($val) {
        global $sysSession, $MSG;
        $val = sysEncode($val);
        $icon = '<img title="' . $MSG['msg_modify'][$sysSession->lang] . '" alt="' . $MSG['msg_modify'][$sysSession->lang] . '" src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/icon_property.gif" width="16" height="16" border="0">';
        // $str = '<a href="javascript:;" onclick="editCourse(\'' . $val . '\'); return false;" class="cssAnchor">' . $icon . '</a>';
        $str = '<a href="javascript:;" onclick="editSysbar(\'' . $val . '\'); return false;" class="cssAnchor">' . $MSG['link_sysbar'][$sysSession->lang] . '</a>';
        return $str;
    }
    
    function showUsername($val, $fn, $ln) {
        global $sysSession;
        if ($val !== '' && isset($val)) {
            $str  = $val.'('.$ln.$fn.')';
        } else {
            $str  = 'root(系統管理員)';
        }
        
        return $str;
    }
    // 取得審核機制名稱
    $rsSyscont = dbGetStMr('`WM_review_syscont`', '`flow_serial`, `title`', '`kind` = "course"');
    if ($rsSyscont) {
        while (!$rsSyscont->EOF) {
            $multiTitle = getCaption($rsSyscont->fields['title']);
            $sysCont[$rsSyscont->fields['flow_serial']] = $multiTitle[$sysSession->lang];
            $rsSyscont->MoveNext();
        }
    }
    unset($rsSyscont);
    function showReview($val) {
        global $sysCont;
        $str  = $sysCont[$val];
        return $str;
    }

    showXHTML_head_B($MSG['title_manage'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_CSS('include', "/theme/default/fancybox/jquery.fancybox.css");
    showXHTML_script('include', "/lib/jquery/jquery.min.js");
    showXHTML_script('include', "/theme/default/fancybox/jquery.fancybox.pack.js");
    showXHTML_script('include', '/lib/xmlextras.js');
    showXHTML_script('include', '/lib/common.js');
    showXHTML_script('include', '/academic/course/course_list.js');
    showXHTML_script('inline', $js);
    showXHTML_head_E('');

    showXHTML_body_B('');
        $ary = array();
        $ary[] = array($MSG['tabs_course_list'][$sysSession->lang], 'tabs');

        echo '<div align="center">';
        showXHTML_tabFrame_B($ary, 1, 'mainFm', 'ListTable', 'action="enroll_confirm.php" method="post" enctype="multipart/form-data" style="display: inline;" onsubmit="return false;"');
            showXHTML_input('hidden', 'folder_id');
            showXHTML_input('hidden', 'csids');

            $myTable = new table();
            $myTable->extra = 'width="760" border="0" cellspacing="1" cellpadding="3" id="" class="cssTable"';
            $myTable->display['help_class'] = 'cssTrHelp';
            $dir = $sysSession->school_name;
            if (empty($keyword)) {
                if ($gid > 10000000) {
                    $ary = getParents($gid, true);
                    $ary[] = $gid;
                    foreach ($ary as $key => $val) {
                        if ($val <= 10000000) continue;
                        $data = getCourseData($val);
                        $lang = getCaption($data['caption']);
                        $dir .= ' > ' . $lang[$sysSession->lang];
                    }
                } else {
                    $dir .= ' > ' . $MSG['msg_all_course'][$sysSession->lang];
                }
            } else {
                $dir .= ' > ' . $MSG['msg_query_result'][$sysSession->lang];
            }

            $myTable->add_help($dir);

            // 工具列
            $toolbar = new toolbar();
            if (defined('Mail2Group')) {
                $toolbar->add_input('button', '', $MSG['cour_mail_send'][$sysSession->lang], '', 'class="cssBtn" onclick="doFunc()"');
            } else {
                if ($gid != 10000000) {
                    $toolbar->add_caption('&nbsp;');
                    $toolbar->add_input('button', 'btnUp1'      , $MSG['btn_move_up'][$sysSession->lang]        , '', 'id="btnUp1"     class="cssBtn" onclick="funcGroup(\'up\')"     title="' . $MSG['btn_alt_move_up'][$sysSession->lang] . '"');
                    $toolbar->add_input('button', 'btnDw1'      , $MSG['btn_move_down'][$sysSession->lang]      , '', 'id="btnDw1"     class="cssBtn" onclick="funcGroup(\'down\')"   title="' . $MSG['btn_alt_move_down'][$sysSession->lang] . '"');
                    $toolbar->add_caption('&nbsp;');
                    $toolbar->add_input('button', 'btnAppend1'  , $MSG['btn_append'][$sysSession->lang]         , '', 'id="btnAppend1" class="cssBtn" onclick="funcGroup(\'append\')" title="' . $MSG['btn_alt_append'][$sysSession->lang] . '"');
                    $toolbar->add_input('button', 'btnMove1'    , $MSG['btn_move'][$sysSession->lang]           , '', 'id="btnMove1"   class="cssBtn" onclick="funcGroup(\'move\')"   title="' . $MSG['btn_alt_move'][$sysSession->lang] . '"');
                    $toolbar->add_input('button', 'btnRemove1'  , $MSG['btn_delete'][$sysSession->lang]         , '', 'id="btnRemove1" class="cssBtn" onclick="funcGroup(\'remove\')" title="' . $MSG['btn_alt_delete'][$sysSession->lang] . '"');
                    $toolbar->add_caption('&nbsp;');
                    $toolbar->add_input('button', 'btnCapacity1', $MSG['msg_set_capacity'][$sysSession->lang]   , '', 'id="btnCapacity1" class="cssBtn" onclick="openSetFancy(\'capacity\')" title="' . $MSG['msg_set_capacity'][$sysSession->lang] . '"');
                    $toolbar->add_input('button', 'btnVerify1'  , $MSG['msg_set_review'][$sysSession->lang]     , '', 'id="btnVerify1" class="cssBtn" onclick="openSetFancy(\'verify\')" title="' . $MSG['msg_set_review'][$sysSession->lang] . '"');
                } else {
                    $toolbar->add_caption('&nbsp;');
                    $toolbar->add_input('button', 'btnAppend1'  , $MSG['btn_append'][$sysSession->lang]         , '', 'id="btnAppend1" class="cssBtn" onclick="funcGroup(\'append\')" title="' . $MSG['btn_alt_append'][$sysSession->lang] . '"');
                    $toolbar->add_caption('&nbsp;');
                    $toolbar->add_input('button', 'btnCapacity1', $MSG['msg_set_capacity'][$sysSession->lang]   , '', 'id="btnCapacity1" class="cssBtn" onclick="openSetFancy(\'capacity\')" title="' . $MSG['msg_set_capacity'][$sysSession->lang] . '"');
                    $toolbar->add_input('button', 'btnVerify1'  , $MSG['msg_set_review'][$sysSession->lang]     , '', 'id="btnVerify1" class="cssBtn" onclick="openSetFancy(\'verify\')" title="' . $MSG['msg_set_review'][$sysSession->lang] . '"');
                }
            }
            $myTable->set_def_toolbar($toolbar);

            $toolbar = new toolbar();
            if (!defined('Mail2Group')) {
                $toolbar->add_input('button', '', $MSG['btn_add_course'][$sysSession->lang], '', 'id="newBtn11" class="cssBtn" onclick="addCourse()" title="' . $MSG['btn_alt_add_course'][$sysSession->lang] . '"');
                $toolbar->add_input('button', 'btnDel', $MSG['btn_del_course'][$sysSession->lang], '', 'id="btnDel"  class="cssBtn" onclick="delCourse()" title="' . $MSG['btn_alt_del_course'][$sysSession->lang] . '"');
                $toolbar->add_caption('&nbsp;');
            }
            $toolbar->add_caption($MSG['query_course'][$sysSession->lang]);
            $txt = empty($keyword) ? $MSG['query_string'][$sysSession->lang] : $keyword;
            $toolbar->add_input('text', 'queryTxt', '', '', 'id="queryTxt" class="cssInput" onmouseover="this.focus(); this.select();" placeholder="' . $txt . '"');
            $toolbar->add_input('button', '', $MSG['btn_ok'][$sysSession->lang], '', 'class="cssBtn" onclick="queryCourse(event)"');
            $myTable->add_toolbar($toolbar);

            // 全選全消的按鈕
            $myTable->set_select_btn(true, 'btnSel', $MSG['msg_select_all'][$sysSession->lang], 'onclick="selfunc()"');
            // 翻頁
            $myTable->display['page_func'] = 'rmUnload("page"); return true';
            // 資料
            $ck1 = new toolbar();
            $ck1->add_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');

            $ck2 = new toolbar();
            $ck2->add_input('checkbox', 'fid[]', '%0', '', 'onclick="chgCheckbox(); event.cancelBubble=true;"');

            $myTable->add_field($ck1, $MSG['select_all_msg'][$sysSession->lang] , ''                , '%0'                          , 'showCheckBox'    , 'width="20" align="center"');
            $myTable->add_field($MSG['td_course_id'][$sysSession->lang]         , '', 'csid'        , '%0'                          , ''                , 'align="center" nowrap="noWrap"');
            $myTable->add_field($MSG['td_course_name'][$sysSession->lang]       , '', 'caption'     , '%0 %2'                       , 'showSubject'     , 'nowrap="noWrap"');
            $myTable->add_field($MSG['td_enroll'][$sysSession->lang]            , '', 'enroll_date' , '%5 %6'                       , 'showDatetime'    , 'nowrap="noWrap"');
            $myTable->add_field($MSG['td_study'][$sysSession->lang]             , '', 'study_date'  , '%7 %8'                       , 'showDatetime'    , 'nowrap="noWrap"');
            $myTable->add_field($MSG['td_remain'][$sysSession->lang]            , '', ''            , '%18 %19'                     , 'showRemainByMb'  , 'align="right" nowrap="noWrap"');
            $myTable->add_field($MSG['td_review_status'][$sysSession->lang]     , '', 'study_date'  , '%flow_serial'                , 'showReview'      , 'nowrap="noWrap"');
            $myTable->add_field($MSG['td_creator'][$sysSession->lang]           , '', 'creator'     , '%26 %first_name %last_name'  , 'showUsername'    , 'nowrap="noWrap"');
            if (!defined('Mail2Group')) {
                $myTable->add_field($MSG['td_function'][$sysSession->lang]      , '', ''            , '%0'                          , 'showModifyIcon'  , 'align="center" nowrap="noWrap"');
            }

            if (empty($keyword)) {
                if ($gid < 10000000) {
                    $table  = '`WM_term_course`';
                    $fields = '`WM_term_course`.*';
                    $where  = '`course_id`!=10000000 AND `kind`="course" AND `status`<9 order by `course_id` DESC';
                } else if ($gid == 10000000) {
                    $table  = '`WM_term_course` LEFT JOIN `WM_user_account` ON `WM_user_account`.`username`=`WM_term_course`.`creator` LEFT JOIN `WM_review_sysidx` ON `WM_review_sysidx`.`discren_id`=`WM_term_course`.`course_id`';
                    $fields = '`WM_term_course`.*, `WM_user_account`.`first_name`, `WM_user_account`.`last_name`, `WM_review_sysidx`.`flow_serial`';
                    $where  = '`course_id`!=10000000 AND `kind`="course" AND `status`<9 AND `WM_review_sysidx`.`discren_id` IN (SELECT course_id FROM `WM_term_course`) order by `course_id` DESC';
                } else {
                    $table  = 'WM_term_group LEFT JOIN `WM_term_course` ON `WM_term_group`.`child`=`WM_term_course`.`course_id` LEFT JOIN `WM_user_account` ON `WM_user_account`.`username`=`WM_term_course`.`creator` LEFT JOIN `WM_review_sysidx` ON `WM_review_sysidx`.`discren_id`=`WM_term_course`.`course_id`';
                    $fields = '`WM_term_course`.*, `WM_user_account`.`first_name`, `WM_user_account`.`last_name`, `WM_term_group`.`child`, `WM_review_sysidx`.`flow_serial`';
                    $where  = "`WM_term_group`.`parent`={$gid} AND `WM_term_course`.`kind`='course' AND `WM_term_course`.`status`<9 AND `WM_review_sysidx`.`discren_id` IN (SELECT course_id FROM `WM_term_course`) ORDER BY `WM_term_course`.`course_id` DESC";
                }
            } else {
                $qtxt   = trim($keyword);
                $qtxt   = escape_LIKE_query_str($qtxt);
                $query  = 'caption like ' . $sysConn->qstr("%{$qtxt}%", get_magic_quotes_gpc());
                $table  = '`WM_term_course` LEFT JOIN `WM_review_sysidx` ON `WM_review_sysidx`.`discren_id`=`WM_term_course`.`course_id` LEFT JOIN `WM_user_account` ON `WM_user_account`.`username`=`WM_term_course`.`creator`';
                $fields = '`WM_term_course`.*, `WM_review_sysidx`.`flow_serial`, `WM_user_account`.`first_name`, `WM_user_account`.`last_name`';
                
                $where = '';
                /*Custom 2017-11-27 *048833 */
                if ($gid > 10000000) {
                    $table = 'WM_term_group LEFT JOIN `WM_term_course` ON `WM_term_group`.`child`=`WM_term_course`.`course_id` LEFT JOIN `WM_user_account` ON `WM_user_account`.`username`=`WM_term_course`.`creator` inner JOIN `WM_review_sysidx` ON `WM_review_sysidx`.`discren_id`=`WM_term_course`.`course_id`';
                    $where = "`WM_term_group`.`parent`={$gid} AND ";
                }
                /*Custom 2017-11-27 *048833 */
                
                /* Custom (B) by LGT MIS#045060
                修正課程名稱排序，預設依照學年期由大到小排序
                */
                $where .= $query . ' AND `kind`="course" AND `status`<9 AND `WM_review_sysidx`.`discren_id` IN (SELECT course_id FROM `WM_term_course`) ORDER BY `course_id` DESC';
                /* Custom (E) by LGT MIS#045060 */
            }
            $myTable->set_sqls($table, $fields, $where);
            $myTable->show();
            $_POST['page']   = $myTable->get_page();
            $_POST['sortby'] = $myTable->display['sort'];
        showXHTML_tabFrame_E();
        echo '</div>';

        $ticket  = preg_replace('![^\w+/=]!', '', $_POST['ticket']);
        $lsList  = preg_replace('![^\w+/=,]!', '', $_POST['lsList']);
        $sortby  = (isset($_POST['sortby'])) ? preg_replace('/[^\w, ]/', '', $_POST['sortby']) : 'csid';
        $order   = preg_replace('/\W/', '', $_POST['order']);
        $page    = intval($_POST['page']);
        showXHTML_form_B('action="' . $_SERVER['PHP_SELF'] . '" method="post" style="display: none;"', 'actFm');
            showXHTML_input('hidden', 'page'   , $page);
            showXHTML_input('hidden', 'sortby' , $sortby);
            showXHTML_input('hidden', 'order'  , $order);
            showXHTML_input('hidden', 'ticket' , $ticket);
            showXHTML_input('hidden', 'keyword', $keyword);
            showXHTML_input('hidden', 'lsList' , $lsList);
        showXHTML_form_E();

    showXHTML_form_B('action="sysbar.php" method="post" enctype="multipart/form-data" style="display:none" target="esBar"', 'sysbarFm');
        showXHTML_input('hidden', 'csid',0);
    showXHTML_form_E('');
    
    // 審核及空間限制
    // 直接引用 bootstrap 會造成 pro 舊版面走鐘，先將部分 css 移植，未來全部改版時在引用，並清除這邊的 css
    echo '<style>
        .group-box {
            width:270px;
            display:none;
        }

        .group-box-content {
            margin:20px 20px 20px 20px; 
        }

        .group-box-title {
            background-color:#F3800F;
            height:50px;
            line-height:50px;
            color:#ffffff;
            font-size:18px;
            font-weight:bold;
            border-top-left-radius:4px;
            border-top-right-radius:4px;
            padding-left:20px;
        }

        .group-box-button {
            margin:20px 20px 20px 20px;
            float :right; 
        }
        .btn {
            display: inline-block;
            padding: 4px 12px;
            margin-bottom: 0;
            font-size: 14px;
            line-height: 20px;
            color: #333;
            text-align: center;
            text-shadow: 0 1px 1px rgba(255,255,255,0.75);
            vertical-align: middle;
            cursor: pointer;
            background-color: #f5f5f5;
            background-image: -moz-linear-gradient(top,#fff,#e6e6e6);
            background-image: -webkit-gradient(linear,0 0,0 100%,from(#fff),to(#e6e6e6));
            background-image: -webkit-linear-gradient(top,#fff,#e6e6e6);
            background-image: -o-linear-gradient(top,#fff,#e6e6e6);
            background-image: linear-gradient(to bottom,#fff,#e6e6e6);
            background-repeat: repeat-x;
            border: 1px solid #ccc;
            border-color: #e6e6e6 #e6e6e6 #bfbfbf;
            border-color: rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);
            border-bottom-color: #b3b3b3;
            -webkit-border-radius: 4px;
            -moz-border-radius: 4px;
            border-radius: 4px;
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#ffffffff",endColorstr="#ffe6e6e6",GradientType=0);
            filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
            -webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);
            -moz-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);
        }
        .btn-warning {
            color: #fff;
            text-shadow: 0 -1px 0 rgba(0,0,0,0.25);
            background-color: #faa732;
            background-image: -moz-linear-gradient(top,#fbb450,#f89406);
            background-image: -webkit-gradient(linear,0 0,0 100%,from(#fbb450),to(#f89406));
            background-image: -webkit-linear-gradient(top,#fbb450,#f89406);
            background-image: -o-linear-gradient(top,#fbb450,#f89406);
            background-image: linear-gradient(to bottom,#fbb450,#f89406);
            background-repeat: repeat-x;
            border-color: #f89406 #f89406 #ad6704;
            border-color: rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#fffbb450",endColorstr="#fff89406",GradientType=0);
            filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
        }
        .btnNormal {
            width: 96px;
        }
    </style>

    <a id="fancy-settings" href="#course-set"></a>
    <div class="group-box" id="course-set">
        <form id="setFrm" name="setFrm" action="m_course_set.php" method="POST">
            <div class="group-box-title"></div>
            <div class="group-box-content" id="input-capacity">
                <input type="text" name="capacity" size="30" maxlength="4" class="cssInput" style="ime-mode:disabled;width:200px;height:30px;"> GB
            </div>
            <div class="group-box-content" id="input-verify">
                <select name="verify" class="cssInput" style="width:230px;height:30px;">';
                    foreach($sysCont as $key => $val) {
                        echo '<option value="'.$key.'" title="'.$val.'">'.$val.'</option>';
                    }
                echo '</select>
            </div>
            <div class="group-box-button">
                <button class="btn btn-warning btnNormal" onclick="setCourseProp(); return false;">'.$MSG['btn_ok'][$sysSession->lang].'</button>
                <button type="button" class="btn aNormal btnNormal margin-left-15" onclick="close_fancy()">'.$MSG['btn_cancel'][$sysSession->lang].'</button>
            </div>
            <input type="hidden" name="type" value="">  
        </form>        
    </div>';
    
    $ticket = md5($sysSession->school_id . $sysSession->school_name . 'Edit' . $sysSession->username);
    $gid = sysEncode($gid);
    showXHTML_form_B('action="/academic/course/course_property.php" method="post" enctype="multipart/form-data" style="display:none"', 'addFm');
        showXHTML_input('hidden', 'ticket', $ticket);
        showXHTML_input('hidden', 'csid'  , 0);
        showXHTML_input('hidden', 'gid'   , $gid);
        showXHTML_input('hidden', 'page'  , $page);
        showXHTML_input('hidden', 'sortby', $sortby);
    showXHTML_form_E('');
    showXHTML_form_B('action="/teach/course/m_course_property.php" method="post" enctype="multipart/form-data" style="display:none"', 'editFm');
        showXHTML_input('hidden', 'ticket', $ticket);
        showXHTML_input('hidden', 'csid'  , 0);
        showXHTML_input('hidden', 'gid'   , $gid);
        showXHTML_input('hidden', 'page'  , $page);
        showXHTML_input('hidden', 'sortby', $sortby);
        showXHTML_input('hidden', 'query_btn', empty($keyword) ? 0 : 1);
        showXHTML_input('hidden', 'keyword', $keyword);
    showXHTML_form_E('');

    showXHTML_body_E('');