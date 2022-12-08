<?php
    /**
     * 教材閱讀記錄詳細列表
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
     * @copyright   2000-2005 SunNet Tech. INC.
     * @version     CVS: $Id: cour_stat_detail.php,v 1.6 2007/04/27 04:01:13 wiseguy Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2005-09-23
     */

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lang/teach_statistics.php');
    require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');

// {{{ 變數宣告 begin

    $permute = array('', 'username', 'realname', 'begin_time', 'over_time', 'duration');

// }}} 變數宣告 end


// {{{ 函數宣告 begin
    
// }}} 函數宣告 end

// {{{ 主程式 begin

    $sysSession->cur_func = 1500200100;
    $sysSession->restore();
    if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    $original_url = rawurldecode($_REQUEST['url']);

    // MIS#16063 教材節點不見，不應該擋掉匯出的動作 by Small 2010/4/8
    /*
    if (empty($_REQUEST['url']))
        die('url request.');
    else
    */
    // #47324 Chrome [教師/課程管理/教材統計] 進行查詢後，查詢結果不正確-->編碼問題
        if ($_REQUEST['value'] === null) {
            $_REQUEST['url'] = ($tmp = adjust_char(rawurldecode($_REQUEST['url']))) ? $tmp : $_REQUEST['url'];
        }
        
    if ($_REQUEST['key'] == 'account' && !preg_match('/^\w*$/', $_REQUEST['value']))
        die('account text contains illegal chars.');

    chkSchoolId('WM_record_reading');
    // 不同的搜尋條件，組成不同的 SQL where
    if ($_REQUEST['key'] == 'name' && $_REQUEST['value'])
    {
        $realnames = $sysConn->GetAssoc('select username,if(language="Big5" || language="GB2312",concat(IFNULL(`last_name`,""),IFNULL(`first_name`,"")),concat(IFNULL(`first_name`,"")," ",IFNULL(`last_name`,""))) as realname from WM_user_account group by username having realname like "%' . escape_LIKE_query_str($_REQUEST['value']) . '%"');
        if (is_array($realnames) && count($realnames))
        {
            /*
            $condition = sprintf('from WM_record_reading where course_id=%u and url="%s"',
                                 $sysSession->course_id,
                                 $_REQUEST['url']
                                ) . ' and username in ("' . implode('","', array_keys($realnames)) . '")';
            */
            $condition = sprintf('from WM_record_reading where course_id=%u and activity_id="%s"',
                             $sysSession->course_id,
                             $_REQUEST['activity_id']
                            ). ' and username in ("' . implode('","', array_keys($realnames)) . '")';
        }
        else
        {
            /*
            $condition = sprintf('from WM_record_reading where course_id=%u and url="%s"',
                                 $sysSession->course_id,
                                 $_REQUEST['url']
                                );
            */
            $condition = sprintf('from WM_record_reading where course_id=%u and activity_id="%s"',
                             $sysSession->course_id,
                             $_REQUEST['activity_id']
                            );
        }
    }
    elseif ($_REQUEST['key'] == 'account' && $_REQUEST['value'])
    {
        /*
        $condition = sprintf('from WM_record_reading where course_id=%u and url="%s"',
                             $sysSession->course_id,
                             $_REQUEST['url']
                            ) . ' and username like "%' . escape_LIKE_query_str($_REQUEST['value']) . '%"';
        */
        $condition = sprintf('from WM_record_reading where course_id=%u and activity_id="%s"',
                             $sysSession->course_id,
                             $_REQUEST['activity_id']
                            ) . ' and username like "%' . escape_LIKE_query_str($_REQUEST['value']) . '%"';
    }
    else
    {
        /*
        $condition = sprintf('from WM_record_reading where course_id=%u and url="%s"',
                             $sysSession->course_id,
                             $_REQUEST['url']
                            );
                            */
        $condition = sprintf('from WM_record_reading where course_id=%u and activity_id="%s"',
                             $sysSession->course_id,
                             $_REQUEST['activity_id']
                            );
    }


    $keep = $ADODB_FETCH_MODE;
    $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
    list($amount, $sec) =
        $sysConn->CacheGetRow('SELECT COUNT(*), SUM(UNIX_TIMESTAMP(over_time) - UNIX_TIMESTAMP(begin_time) + 1) ' . $condition . ' GROUP BY activity_id');
    $ADODB_FETCH_MODE = $keep;
    
    // title額外取，避免老師修改過節點名稱後取到舊的名稱
    $title = $sysConn->CacheGetOne('select title ' . $condition . ' order by over_time desc');
    
    $total_item    = (int)$sysConn->GetOne('select count(*) ' . $condition);
    $item_per_page = max((int)$_REQUEST['ipp'], sysPostPerPage);
    $total_page    = max(ceil($total_item / $item_per_page), 1);
    $curr_page     = min(max((int)$_REQUEST['cp'], 1), $total_page);
    $sort          = $permute[ereg('^[12345]$', $_REQUEST['sort']) ? $_REQUEST['sort'] : 0];
    $direct        = eregi('^(ASC|DESC)$', $_REQUEST['direct']) ? $_REQUEST['direct'] : 'ASC';
    $sort          = $sort ? (' order by ' . $sort . ' ' . $direct) : '';
    $pages         = range(0, $total_page); unset($pages[0]);

    $rs = $sysConn->GetArray('select username, begin_time, over_time, (UNIX_TIMESTAMP(over_time) - UNIX_TIMESTAMP(begin_time) + 1) as duration ' .
                             $condition . $sort .
                             ($_REQUEST['expo'] ? '' : (' limit ' . (($curr_page - 1) * $item_per_page) . ',' . $item_per_page))
                            );
    $names = array();
    if (isset($realnames) && is_array($realnames))
    {
        $names = $realnames;
    }
    else
    {
        if (is_array($rs) && count($rs)) foreach($rs as $row) $names[] = $row[0];
        $names = $sysConn->GetAssoc('select username, if(first_name REGEXP "^[0-9A-Za-z _-]*$" && last_name REGEXP "^[0-9A-Za-z _-]*$", concat(IFNULL(`first_name`,""), " ", IFNULL(`last_name`,"")), concat(IFNULL(`last_name`,""), IFNULL(`first_name`,""))) from WM_user_account where username in ("' . implode('","', array_unique($names)) . '")');
    }

    if ($_REQUEST['expo'])
    {
           header('Content-Disposition: attachment; filename="content_reading_detail.utf8.csv"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Type: text/plain; name="content_reading_detail.utf8.csv"');
        ob_start();
        printf(('%s > %s > ' . $MSG['msg_time3'][$sysSession->lang] . ' %u ' . $MSG['hits'][$sysSession->lang] . ' %s' . $MSG['detail_is_following'][$sysSession->lang]) . "\r\n", $sysSession->course_name, $title, $amount, sec2timestamp($sec));
        foreach($rs as $v)
        {
            printf("\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\r\n", $v[0], str_replace('"', '\\"', $names[$v[0]]), $v[1], $v[2], sec2timestamp($v[3]));
        }
        $result = ob_get_contents();
        ob_end_clean();
        die(utf8_to_excel_unicode($result));
    }

    showXHTML_head_B('');
      showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
      showXHTML_script('inline', "
var T = {$total_page};

function sortBy(n)
{
    var form = document.getElementById('mainForm');
    if (form.sort.value == n)
    {
        form.direct.value = (form.direct.value != 'DESC' ? 'DESC' : 'ASC');
    }
    else
        form.sort.value = n;
    form.submit();
}
");
    showXHTML_head_E();
    showXHTML_body_B();
      $ary = array(array($MSG['content_reading_log_detail'][$sysSession->lang]));
      echo "<center>\n";
      showXHTML_tabFrame_B($ary, 1, 'mainForm', 'table1', 'action="cour_stat_detail.php" method="POST" style="display: inline"');
        showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="760" style="border-collapse: collapse" class="cssTable"');
          showXHTML_tr_B('class="cssTrEvn font01"');
            showXHTML_td('colspan="5"', sprintf(('%s > %s > ' . $MSG['msg_time3'][$sysSession->lang] . ' <span style="color: red">%u</span> ' . $MSG['hits'][$sysSession->lang] . ' <span style="color: red">%s</span>' . $MSG['detail_is_following'][$sysSession->lang]), $sysSession->course_name, $title, $amount, zero2gray(sec2timestamp($sec))));
          showXHTML_tr_E();
          showXHTML_tr_B('class="cssTrOdd font01"');
            showXHTML_td_B('colspan="5"');
              echo $MSG['search'][$sysSession->lang];
              showXHTML_input('select', 'key', array('account' => $MSG['account'][$sysSession->lang],
                                                     'name'    => $MSG['realname'][$sysSession->lang]
                                                    ), $_REQUEST['key'], '');
              showXHTML_input('text', 'value', stripslashes($_REQUEST['value']), '', 'size="20" maxlength="32" class="cssInput"');
              showXHTML_input('submit', '', $MSG['search'][$sysSession->lang], '', 'class="cssBtn"');
              // showXHTML_input('hidden', 'url'   , htmlspecialchars(stripslashes($_REQUEST['url'])));
              showXHTML_input('hidden', 'url'   , rawurlencode($original_url));
              showXHTML_input('hidden', 'activity_id'   , $_REQUEST['activity_id']);
              showXHTML_input('hidden', 'cp'    , $curr_page);
              showXHTML_input('hidden', 'ipp'   , $item_per_page);
              showXHTML_input('hidden', 'sort'  , $_REQUEST['sort']);
              showXHTML_input('hidden', 'direct', $direct);
              showXHTML_input('hidden', 'expo'  , 0);
            showXHTML_td_E();
          showXHTML_tr_E();
          showXHTML_tr_B('id="toolbar1" class="cssTrEvn font01"');
            showXHTML_td_B('colspan="4"');
              echo $MSG['page'][$sysSession->lang];
              showXHTML_input('select', '', $pages, $curr_page, 'onchange="this.form.cp.value=this.value; this.form.submit();"');
              echo $MSG['page_items'][$sysSession->lang];
              showXHTML_input('select', '', array(sysPostPerPage => $MSG['default'][$sysSession->lang],
                                                  20             => 20,
                                                  50             => 50,
                                                  100            => 100,
                                                  200            => 200,
                                                  400            => 400
                                                 ), $item_per_page, 'onchange="this.form.ipp.value=this.value; this.form.submit();"');
              echo $MSG['s'][$sysSession->lang];
              showXHTML_input('button', '', $MSG['first_page'][$sysSession->lang], '', 'class="cssBtn" ' . ($curr_page == 1           ? 'disabled' : 'onclick="this.form.cp.value=1; this.form.submit();"'));
              showXHTML_input('button', '', $MSG['prev_page'][$sysSession->lang] , '', 'class="cssBtn" ' . ($curr_page == 1           ? 'disabled' : 'onclick="this.form.cp.value--; this.form.submit();"'));
              showXHTML_input('button', '', $MSG['next_page'][$sysSession->lang] , '', 'class="cssBtn" ' . ($curr_page == $total_page ? 'disabled' : 'onclick="this.form.cp.value++; this.form.submit();"'));
              showXHTML_input('button', '', $MSG['last_page'][$sysSession->lang] , '', 'class="cssBtn" ' . ($curr_page == $total_page ? 'disabled' : 'onclick="this.form.cp.value=T; this.form.submit();"'));
            showXHTML_td_E();
            showXHTML_td_B('align="right"');
              showXHTML_input('button', '', $MSG['export_all'][$sysSession->lang], '', 'class="cssBtn" onclick="this.form.expo.value=1; this.form.submit(); this.form.expo.value=0;"');
            showXHTML_td_E();
          showXHTML_tr_E();
          showXHTML_tr_B('class="cssTrHead font01"');
            showXHTML_td('align="center" style="cursor: pointer" onmouseover="this.style.textDecoration=\'underline\'; this.style.color=\'#0000FF\';" onmouseout="this.style.textDecoration=\'none\'; this.style.color=\'\';" onclick="sortBy(1);"', $MSG['account'][$sysSession->lang]    . ($_REQUEST['sort'] == 1 ? ('<img src="/theme/default/learn/dude07232001' . ($_REQUEST['direct'] == 'DESC' ? 'down' : 'up') . '.gif" valign="absmiddle">') : ''));
            showXHTML_td('align="center"', $MSG['realname'][$sysSession->lang]);
            showXHTML_td('align="center" style="cursor: pointer" onmouseover="this.style.textDecoration=\'underline\'; this.style.color=\'#0000FF\';" onmouseout="this.style.textDecoration=\'none\'; this.style.color=\'\';" onclick="sortBy(3);"', $MSG['begin_time'][$sysSession->lang] . ($_REQUEST['sort'] == 3 ? ('<img src="/theme/default/learn/dude07232001' . ($_REQUEST['direct'] == 'DESC' ? 'down' : 'up') . '.gif" valign="absmiddle">') : ''));
            showXHTML_td('align="center" style="cursor: pointer" onmouseover="this.style.textDecoration=\'underline\'; this.style.color=\'#0000FF\';" onmouseout="this.style.textDecoration=\'none\'; this.style.color=\'\';" onclick="sortBy(4);"', $MSG['end_time'][$sysSession->lang]   . ($_REQUEST['sort'] == 4 ? ('<img src="/theme/default/learn/dude07232001' . ($_REQUEST['direct'] == 'DESC' ? 'down' : 'up') . '.gif" valign="absmiddle">') : ''));
            showXHTML_td('align="center" style="cursor: pointer" onmouseover="this.style.textDecoration=\'underline\'; this.style.color=\'#0000FF\';" onmouseout="this.style.textDecoration=\'none\'; this.style.color=\'\';" onclick="sortBy(5);"', $MSG['duration'][$sysSession->lang]   . ($_REQUEST['sort'] == 5 ? ('<img src="/theme/default/learn/dude07232001' . ($_REQUEST['direct'] == 'DESC' ? 'down' : 'up') . '.gif" valign="absmiddle">') : ''));
          showXHTML_tr_E();
          $i = 1;
        foreach($rs as $v)
        {
          $i ^= 1;
          showXHTML_tr_B('class="cssTr' . ($i ? 'Odd' : 'Evn') . ' font01"');
            showXHTML_td('', $v[0]);
            showXHTML_td('', htmlspecialchars($names[$v[0]]));
            showXHTML_td('align="center"', $v[1]);
            showXHTML_td('align="center"', $v[2]);
            showXHTML_td('align="center"', zero2gray(sec2timestamp($v[3])));
          showXHTML_tr_E();
        }
          showXHTML_tr_B('id="toolbar2" class="cssTrEvn font01"');
            showXHTML_td('colspan="4"', '');
            showXHTML_td('align="right"', '');
          showXHTML_tr_E();
        showXHTML_table_E();
        showXHTML_script('inline', "
          var t1 = document.getElementById('toolbar1');
          var t2 = document.getElementById('toolbar2');
          t2.cells[0].innerHTML = t1.cells[0].innerHTML;
          t2.cells[1].innerHTML = t1.cells[1].innerHTML;"
        );
      showXHTML_tabFrame_E();
      echo "</center>\n";
    showXHTML_body_E();

// }}} 主程式 end
?>
