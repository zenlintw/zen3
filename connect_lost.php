<?php
    /**
     * 連線逾時的畫面
     *
     * @since   2004/12/24
     * @author  ShenTing Lin
     * @version $Id: connect_lost.php,v 1.1 2010/02/24 02:38:54 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/connect_lost.php');

    // 移除舊的 sysSession
    dbDel('WM_session', "idx='{$_COOKIE['idx']}'");

        setcookie('wm_learning_hash_clean', 'Y', time() - 3600, '/');
        setcookie('wm_learning_hash_start', '', time() - 3600, '/');
        $pathWmCookieHash2Lcms = sysLcmsHost . '/lms/wmcookie/clear/' . rawurlencode(base64_url_encode($wmCookieHash));

    // 清除 Cookie
    header("Set-Cookie: idx=; path=/");
    header("Set-Cookie: sIdx=; path=/");
    header('Location: /');
    exit;

    $js = <<< BOF
    function gohome() {
        location.replace("/");
    }
BOF;

    showXHTML_head_B('');
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_script('inline', $js);
    showXHTML_head_E();
    showXHTML_body_B();
        $ary = array();
        // 用第三個參數設定「是否要FORM元素」，預設為1，代表要
        // 承上，失去連線因用不到表單功能，故設定為不要FORM元素，即0，避免被出資安單
        $ary[] = array($MSG['title'][$sysSession->lang], 'tabs1', 0);
        // $colspan = 'colspan="2"';
        echo '<div align="center">';
        showXHTML_tabFrame_B($ary, 1);
            showXHTML_table_B('width="380" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
                showXHTML_tr_B('class="cssTrHead"');
                    showXHTML_td('', $MSG['msg_title'][$sysSession->lang]);
                showXHTML_tr_E();
                showXHTML_tr_B('class="cssTrEvn"');
                    showXHTML_td('class="cssCaleFont01"', $MSG['msg_content'][$sysSession->lang]);
                showXHTML_tr_E();
                showXHTML_tr_B('class="cssTrOdd"');
                    showXHTML_td_B('align="center"');
                        showXHTML_input('button', 'btnHome', $MSG['btn_home'][$sysSession->lang], '', 'class="cssBtn" onclick="gohome();"');
                                                echo '<img src="' . $pathWmCookieHash2Lcms . '" style="display: none;">';
                    showXHTML_td_E();
                showXHTML_tr_E();
            showXHTML_table_E();
        showXHTML_tabFrame_E(0);
        echo '</div>';
    showXHTML_body_E();

?>
