<?php
    /**
     * 試聽課程列表
     *
     * @since   2012/08/09
     * @author  ShenTing Lin
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/lib/multi_lang.php');
    require_once(sysDocumentRoot . '/lang/experience.php');
    require_once(sysDocumentRoot . '/academic/App_course/experience_init.php');

    $js = <<< BOF
    var
        lang = '{$sysSession->lang}',
        MSG_MUST_SELECT   = '{$MSG['msg_must_select'][$sysSession->lang]}',
        MSG_BTN_BROWSE    = '{$MSG['btn_browse'][$sysSession->lang]}',
        MSG_ON_TOP        = '{$MSG['msg_on_top'][$sysSession->lang]}',
        MSG_ON_BOTTOM     = '{$MSG['msg_on_bottom'][$sysSession->lang]}',
        MSG_INSERT_SELECT = '{$MSG['msg_insert_select'][$sysSession->lang]}',
        MSG_SAVE_SUCCESS  = '{$MSG['msg_save_success'][$sysSession->lang]}',
        MSG_SAVE_FAIL     = '{$MSG['msg_save_fail'][$sysSession->lang]}',
        PREFIX_URL = 'http://{$_SERVER['SERVER_NAME']}:{$_SERVER['SERVER_PORT']}/base/{$sysSession->school_id}/door/APP/wmmedia/video/',
        MSG_EXIT    	 = '{$MSG['msg_need_save'][$sysSession->lang]}';
BOF;


    showXHTML_head_B('');
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_CSS('include', '/lib/jquery/css/jquery-ui-1.8.22.custom.css');
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('include', '/lib/jquery/jquery-ui-1.8.22.custom.min.js');
    showXHTML_script('include', '/lib/dragLayer.js');
    showXHTML_script('include', '/academic/App_course/experience_list.js');
    showXHTML_script('inline', $js);
    showXHTML_head_E();
    showXHTML_body_B();
        $ary = array();
        $ary[] = array($MSG['experience_title'][$sysSession->lang]);
        // $colspan = 'colspan="2"';
        echo '<div align="left">';
        showXHTML_tabFrame_B($ary, 1);//, form_id, table_id, form_extra, isDragable);
            showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
                showXHTML_tr_B('class="cssTrEvn"');
                    showXHTML_td('id="mainPanel"', '&nbsp;');
                showXHTML_tr_E();
            showXHTML_table_E();
        showXHTML_tabFrame_E();
        echo '</div>';

        echo '<div id="langTemp" style="display: none;">';
        $multi_lang = new Multi_lang(false, ''); // 多語系輸入框
        $multi_lang->show(true, null, '');
        echo '</div>';

        $ary = array(
            array($MSG['experience_catalog_title'][$sysSession->lang])
        );
        showXHTML_tabFrame_B($ary, 1, '', 'propPanel', 'style="display: inline"', true, false);
            showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" class="cssTable"');
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td_B('rowspan="4" align="center" id="propCover" style="width: 180px;"');
                        showXHTML_input('hidden', 'nid'  , '', '', '');
                        showXHTML_input('hidden', 'posid', '', '', '');
                        echo <<< BOF
    <div class="cover" style="border: 1px solid #D4D4D4; width: 153px; height: 110px;">
        <div class="image" style="border: 4px solid #FFF; width: 145px; height: 102px; background-size: cover;"></div>
    </div>
BOF;
                    showXHTML_td_E();
                    showXHTML_td('align="right" class="cssTrHead"', $MSG['th_cate_name_colon'][$sysSession->lang]);
                    showXHTML_td_B('valign="top" id="propLang"');
                        $multi_lang = new Multi_lang(false, ''); // 多語系輸入框
                        $multi_lang->show(true, null, '');
                    showXHTML_td_E();
                showXHTML_tr_E();
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right" class="cssTrHead"', $MSG['th_cover_colon'][$sysSession->lang]);
                    showXHTML_td_B();
                        showXHTML_input('text', 'cover', '', '', 'class="cssInput" style="width: 300px;"');
                        showXHTML_input('button', '', $MSG['btn_browse'][$sysSession->lang], '', 'class="cssBtn" onclick="browseCatalogFile();"');
                        echo '<br><font color="red">'.$MSG['msg_chars_limit'][$sysSession->lang].'</font>';
                    showXHTML_td_E();
                showXHTML_tr_E();
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right" class="cssTrHead"', $MSG['th_enable_colon'][$sysSession->lang]);
                    showXHTML_td_B();
                        showXHTML_input('checkbox', 'enable', '', '', '');
                    showXHTML_td_E();
                showXHTML_tr_E();
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right" class="cssTrHead"', $MSG['th_cate_desc_colon'][$sysSession->lang]);
                    showXHTML_td_B();
                        showXHTML_input('textarea', 'desc', '', '', 'class="cssInput" style="width: 300px; height: 10em;"');
                    showXHTML_td_E();
                showXHTML_tr_E();

                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td_B('colspan="3"');
                        // URL 清單 (Begin)
                        showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="catalogUrls"');
                            $col1 = ($col1 == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                            showXHTML_tr_B($col1);
                                showXHTML_td_B('colspan="4"');
                                    showXHTML_input('button', '', $MSG['btn_add'][$sysSession->lang]      , '', 'onclick="addUrl();"');
                                    showXHTML_input('button', '', $MSG['btn_delete'][$sysSession->lang]   , '', 'onclick="delUrl();"');
                                    showXHTML_input('button', '', $MSG['btn_move_up'][$sysSession->lang]  , '', 'onclick="moveUrlUp();"');
                                    showXHTML_input('button', '', $MSG['btn_move_down'][$sysSession->lang], '', 'onclick="moveUrlDown();"');
                                showXHTML_td_E();
                            showXHTML_tr_E();
                            showXHTML_tr_B('class="cssTrHead"');
                                showXHTML_td_B('align="center" style="width: 5%;"');
                                    showXHTML_input('checkbox', 'ckall', '', '', 'title="' . $MSG['th_select_all'][$sysSession->lang] . '" onclick="selectUrlCheck();"');
                                showXHTML_td_E();
                                showXHTML_td('align="center" style="width: 5%;"', $MSG['th_enable'][$sysSession->lang]);
                                showXHTML_td('align="center"', $MSG['th_url_name'][$sysSession->lang]);
                                showXHTML_td('align="center"', $MSG['th_url'][$sysSession->lang]);
                            showXHTML_tr_E();
                            $col1 = ($col1 == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                            showXHTML_tr_B($col1 . ' id="catalogUrlsFooter"');
                                showXHTML_td_B('colspan="4"');
                                    showXHTML_input('button', '', $MSG['btn_add'][$sysSession->lang]      , '', 'onclick="addUrl();"');
                                    showXHTML_input('button', '', $MSG['btn_delete'][$sysSession->lang]   , '', 'onclick="delUrl();"');
                                    showXHTML_input('button', '', $MSG['btn_move_up'][$sysSession->lang]  , '', 'onclick="moveUrlUp();"');
                                    showXHTML_input('button', '', $MSG['btn_move_down'][$sysSession->lang], '', 'onclick="moveUrlDown();"');
                                showXHTML_td_E();
                            showXHTML_tr_E();
                        showXHTML_table_E();
                        // URL 清單 (End)
                    showXHTML_td_E();
                showXHTML_tr_E();
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td_B('align="center" colspan="3"');
                        showXHTML_input('button', '', $MSG['btn_ok'][$sysSession->lang]    , '', 'onclick="propSave();"');
                        showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'onclick="propHidden();"');
                    showXHTML_td_E();
                showXHTML_tr_E();
            showXHTML_table_E();
        showXHTML_tabFrame_E();

    showXHTML_body_E();