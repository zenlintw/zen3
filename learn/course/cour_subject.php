<?php
	/**
	 * 學生端的議題討論列表
	 *
	 * @since   2004/01/20
	 * @author  ShenTing Lin
	 * @version $Id: cour_subject.php,v 1.1 2010/02/24 02:39:07 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
    require_once(sysDocumentRoot . '/lib/lstable.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/lang/teach_course.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');

    $sysSession->cur_func = '900200100';
    $sysSession->restore();
    if (!aclVerifyPermission(900200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    // add by jeff : 2006-04-27, for agent 直接切到討論板
    if ((isset($_COOKIE['go_board'])) && (!empty($_COOKIE['go_board'])))
    {
        setcookie('go_board', '', time() - 3600, '/');
        $gb = sysEncode($_COOKIE['go_board']);
        echo <<< BOF
<html><head><script language="javascript">
	if ((typeof(parent.s_sysbar) == "object") && (typeof(parent.s_sysbar.goBoard) == "function")) {
		parent.s_sysbar.goBoard("{$gb}");
	}
</script></head></html>
BOF;
        exit;
    }

    // 計算討論版是否有未讀文章
    $un_read = array();
    $arrBid = $sysConn->GetCol('select board_id from WM_term_subject where course_id=' . $sysSession->course_id);
    if (is_array($arrBid)) {
        if (empty($arrBid))
            $bids = '';
        else
        {
            $bids = implode(',', $arrBid);
            $un_read = $sysConn->GetCol('select distinct T1.board_id from WM_bbs_posts as T1 left join WM_bbs_readed as T2 on T1.board_id=T2.board_id and T1.node=T2.node and T2.type="b" and T2.username = "'.$sysSession->username.'" where T1.board_id in('.$bids.')  and T2.username is NULL');
            $bids = implode(',', array_diff($arrBid, $un_read));
        }
        // 精華區是否有未讀文章，應該是判斷WM_bbs_collecting，而非WM_bbs_posts by Small 2006/11/8
        if ($bids)
            $un_read += $sysConn->GetCol('select distinct T1.board_id from WM_bbs_collecting as T1 left join WM_bbs_readed as T2 on T1.board_id=T2.board_id and T1.node=T2.node and T2.type="q" and T2.username = "'.$sysSession->username.'" where T1.board_id in('.$bids.')  and T1.type="F" and T2.username is NULL');
    }

    function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
        if (empty($title)) $title = $caption;
        return $without_title ? ('<div style="width: ' . $width . 'px; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $width . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
    }

    function showSubject($val, $act, $type, $ott, $ctt, $stt) {
        global $sysSession, $sysConn, $MSG;

        $lang = getCaption($val);
        if ($type == 'open') {
            $open = false;
            $nt = time();
            $ot = $sysConn->UnixTimeStamp($ott);
            $ct = $sysConn->UnixTimeStamp($ctt);
            $st = $sysConn->UnixTimeStamp($stt);

            $status = getBoardStatus($nt, $ot, $ct, $st);
            $open = ($status!='close');

            if ($open) {
                $bid = sysEncode($act);
                echo divMsg(250, '<a href="javascript:void(null);" onclick="return false;" class="cssAnchor">' . htmlspecialchars_decode($lang[$sysSession->lang]) . '</a>', strip_tags(htmlspecialchars_decode($lang[$sysSession->lang])) . '" onclick="goBoard(\'' . $bid . '\')');
            } else {
                echo divMsg(250, htmlspecialchars_decode($lang[$sysSession->lang]));
            }
        } else {
            echo divMsg(250, htmlspecialchars_decode($lang[$sysSession->lang]));
        }
    }

    function showStatus($val) {
        global $titleStatus;
        return divMsg(50, $titleStatus[$val]);
    }

    function showOpentime($val) {
        global $sysSession, $sysConn, $MSG;
        $time = $sysConn->UnixTimeStamp($val);
        $data = intval($val);
        $msg = $MSG['from2'][$sysSession->lang] . ((empty($time)) ? $MSG['now'][$sysSession->lang] : date('Y-m-d H:i', $time));
        return divMsg(150, $msg);
    }

    function showClosetime($val) {
        global $sysSession, $sysConn, $MSG;
        $time = $sysConn->UnixTimeStamp($val);
        $data = intval($val);
        $msg = $MSG['to2'][$sysSession->lang] . ((empty($time)) ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', $time));
        return divMsg(150, $msg);
    }

    function showSharetime($val) {
        global $sysSession, $sysConn, $MSG;
        $time = $sysConn->UnixTimeStamp($val);
        $data = intval($val);
        return (empty($time)) ? divMsg(135, $MSG['unlimit'][$sysSession->lang]) : divMsg(135, date('Y-m-d H:i', $time));
    }

    function showReaded($bid) {
        global $sysSession, $un_read, $MSG;

        if (in_array($bid, $un_read)){
            $img = '<img src="/theme/' . $sysSession->theme . '/learn/article1.gif">';
            $un_read_flag = true;
        }
        else{
            $img = '<img src="/theme/' . $sysSession->theme . '/learn/article2.gif">';
            $un_read_flag = false;
        }
        return divMsg(50, $img, $un_read_flag ? $MSG['unread_articles'][$sysSession->lang] : $MSG['no_unread_articles'][$sysSession->lang]);
    }

    function showBtn($nid, $type, $ott, $ctt, $stt) {
        global $sysSession, $sysConn, $MSG;

        if ($type == 'open') {
            $open = false;
            $nt = time();
            $ot = $sysConn->UnixTimeStamp($ott);
            $ct = $sysConn->UnixTimeStamp($ctt);
            $st = $sysConn->UnixTimeStamp($stt);

            $status = getBoardStatus($nt, $ot, $ct, $st);
            $open = ($status!='close');

            if ($open) {
                $bid = sysEncode($nid);
                showXHTML_input('button', '', $MSG['btn_enter'][$sysSession->lang], '', 'class="cssBtn" onclick="goBoard(' . $bid . ')"');
            }
        } else {
            echo '&nbsp;';
        }
    }

    $js = <<< BOF
    function goBoard(val) {
        // 學生
        if ((typeof(parent.s_sysbar) == "object") && (typeof(parent.s_sysbar.goBoard) == "function")) {
            parent.s_sysbar.goBoard(val);
        }
    }

BOF;

    showXHTML_head_B('');
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_script('inline', $js);
    showXHTML_head_E();
    showXHTML_body_B('');

        $ary = array();
        $ary[] = array($MSG['subject_title'][$sysSession->lang], 'tabsSet1',  '');
        echo '<div align="center">';
        showXHTML_tabFrame_B($ary, 1);
            $myTable = new table();
            $myTable->extra = 'width="760" align="center" border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable"';

            // 工具列
            $toolbar = new toolbar();
            $toolbar->add_caption(str_repeat('&nbsp;', 40));
            $toolbar->add_caption('<img src="/theme/' . $sysSession->theme . '/learn/article1.gif">' . $MSG['unread_articles'][$sysSession->lang]);
            $toolbar->add_caption('<img src="/theme/' . $sysSession->theme . '/learn/article2.gif">' . $MSG['no_unread_articles'][$sysSession->lang]);
            $myTable->set_def_toolbar($toolbar);

            $myTable->add_field($MSG['title_status'][$sysSession->lang]    , '', '', '%2'               , 'showStatus'   , 'align="center" nowrap="noWrap"');
            $myTable->add_field($MSG['title_read'][$sysSession->lang]	   , '', '', '%1'               , 'showReaded'   , 'align="center" nowrap="noWrap"');
            $myTable->add_field($MSG['title_subject'][$sysSession->lang]   , '', '', '%5 %1 %2 %6 %7 %8', 'showSubject'  , '');
            $myTable->add_field($MSG['title_open_time'][$sysSession->lang] , '', '', '%6'               , 'showOpentime' , 'nowrap="noWrap"');
            $myTable->add_field($MSG['title_close_time'][$sysSession->lang], '', '', '%7'               , 'showClosetime' , 'nowrap="noWrap"');
            $myTable->add_field($MSG['title_share_time'][$sysSession->lang], '', '', '%8'               , 'showSharetime' , 'nowrap="noWrap"');
            // $myTable->add_field($MSG['title_action'][$sysSession->lang]    , '', '', '%1 %2 %6 %7 %8', 'showBtn'      , 'align="center"' );

            // $myTable->set_page(false);
            $tab    = 'WM_term_subject left join WM_bbs_boards on WM_bbs_boards.board_id = WM_term_subject.board_id';
            $fields = '`node_id`, `WM_term_subject`.`board_id`, `state`, `visibility`, `permute`, `bname`, `open_time`, `close_time`, `share_time`';
            $where  = "`course_id`={$sysSession->course_id} AND `state` in ('disable', 'open') AND `visibility`='visible' order by `permute`";
            $myTable->set_sqls($tab, $fields, $where);
            $myTable->show();
        showXHTML_tabFrame_E();
        echo '</div>';
    showXHTML_body_E();