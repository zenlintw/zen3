<?php
    /**
     * 教材統計
     * $Id: cour_statistics.php,v 1.1 2010/01/08 03:23:37 yea Exp $
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lang/teach_statistics.php');
    require_once(sysDocumentRoot . '/lib/lib_logs.php');

    $sysSession->cur_func = '1500200100';
    $sysSession->restore();
    if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    }

    $topics = array("{$MSG['msg_title'][$sysSession->lang]}", /* '第一次讀', '最近一次讀', */ "{$MSG['msg_time1'][$sysSession->lang]}", "{$MSG['msg_time2'][$sysSession->lang]}", "{$MSG['msg_time3'][$sysSession->lang]}", "{$MSG['msg_time4'][$sysSession->lang]}", "{$MSG['msg_time5'][$sysSession->lang]}");
    $sk = array(null, 'title', /* 'first', 'last', */ 'maxi', 'mini', 'amount', 'sec', 'average');
    $n_key = array('maxi', 'mini', 'amount', 'sec', 'average');

    $i = min(max(intval($_GET['i']), 1), 8);
    $d = intval($_GET['d']) ^ 1;
    if (!function_exists('getTitle')) {
    function getTitle($str) {
            global $sysSession;
            if($str){
                $a = explode("\t",$str);
                switch($sysSession->lang){
                    case 'GB2312'        : return $a[1] ? $a[1] : $a[0];
                    case 'en'            : return $a[2] ? $a[2] : $a[0];
                    case 'EUC-JP'        : return $a[3] ? $a[3] : $a[0];
                    case 'user_define'    : return $a[4] ? $a[4] : $a[0];

                    default: return $a[0];
                }
            }else{
                return 'No Title';
            }
        }
    }
    
    if (!function_exists('getNodeValue')) {
        function getNodeValue($node, $tagName)
        {
            $result = '';
            $tmp = $node->get_elements_by_tagname($tagName);
            if (count($tmp) <= 0) return '';
            if ($tmp[0]->has_child_nodes())
            {
                $child = $tmp[0]->first_child();
                $result = $child->node_value();
            } else {
                $result = '';
            }
            return $result;
        }
    }

    // 數值排序
    function num_sort($a, $b)
    {
        global $order_idx;
        return $a[$order_idx] - $b[$order_idx];
    }

    // 字串排序
    function str_sort($a, $b)
    {
        global $order_idx;
        return strcmp($a[$order_idx], $b[$order_idx]);
    }

    // 產生 URL
    function gen_url($url)
    {
        global $sysSession;

        if (strpos($url, 'javascript:') === 0)
            return implode(':;" onclick="', explode(':', $url, 2)) . '; return false;"';
        elseif (eregi('^([a-z]+:(//)?|/|\.\.)', $url))
            return $url . '" target="_blank"';
        else
            return sprintf('/base/%05d/course/%08d/content/%s', $sysSession->school_id, $sysSession->course_id, $url) . '" target="_blank"';
    }

    // 主程式開始 /* max(begin_time) as first, min(begin_time) as last, */
    chkSchoolId('WM_record_reading');
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
    $sqls = "select activity_id,title,url from WM_record_reading where course_id={$sysSession->course_id} group by activity_id order by over_time desc ";
    if ($urls = $sysConn->CacheGetAssoc($sqls))
    {
        $sqls = "select activity_id,count(*) as amount,url,
                 max(UNIX_TIMESTAMP(over_time) - UNIX_TIMESTAMP(begin_time) + 1) as maxi,
                 min(UNIX_TIMESTAMP(over_time) - UNIX_TIMESTAMP(begin_time) + 1) as mini,
                 sum(UNIX_TIMESTAMP(over_time) - UNIX_TIMESTAMP(begin_time) + 1) as sec
                 from WM_record_reading  where course_id={$sysSession->course_id}
                 group by activity_id"; /*!4 FORCE INDEX(idx2) */
    
        if ($all = $sysConn->CacheGetArray($sqls))
        {
            foreach($all as $x => $item)
            {
                $all[$x]['average'] = round($item['sec'] / $item['amount'], 1); // 求出平均
                if (isset($urls[$item['activity_id']])){
                    $all[$x] = array_merge($all[$x], $urls[$item['activity_id']]);
                }else{
                    $all[$x] = array_merge($all[$x], array('title'=>'', 'activity_id'=>''));
                }
                $all[$x]['activity_id']=$item['activity_id'];
                
            }
            $order_idx = $sk[$i];
            if (in_array($order_idx, $n_key))
                usort($all, 'num_sort');
            else
                usort($all, 'str_sort');
            if ($d == 0) $all = array_reverse($all);
        }
    }
    
    $pathContent=dbGetOne('WM_term_path', 'content', 'course_id=' . $sysSession->course_id . ' order by serial desc');
        if($pathContent){
            $xmldoc = @domxml_open_mem($pathContent);
            $ctx1 = xpath_new_context($xmldoc);
        }

    showXHTML_head_B('');
      showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
      $scr = <<< EOB
var MSG_OBJECT        = "{$MSG['msg_object'][$sysSession->lang]}";
var MSG_HOMEWORK      = "{$MSG['msg_homework'][$sysSession->lang]}";
var MSG_EXAM          = "{$MSG['msg_exam'][$sysSession->lang]}";
var MSG_QUESTIONNAIRE = "{$MSG['msg_questionnaire'][$sysSession->lang]}";
var MSG_SUBJECT       = "{$MSG['msg_subject'][$sysSession->lang]}";
var MSG_FORUM         = "{$MSG['msg_forum'][$sysSession->lang]}";
var MSG_CHAT          = "{$MSG['msg_chat'][$sysSession->lang]}";
var MSG_WM            = "{$MSG['msg_wm'][$sysSession->lang]}";

function s(i)
{
    location.replace('{$_SERVER['PHP_SELF']}?i=' + i + '&d=$d');
}

function fetchWMinstance(type, id)
{
    var instance = MSG_OBJECT;
    switch(type){
        case 2: instance = MSG_HOMEWORK; break;
        case 3: instance = MSG_EXAM; break;
        case 4: instance = MSG_QUESTIONNAIRE; break;
        case 5: instance = MSG_SUBJECT; break;
        case 6: instance = MSG_FORUM; break;
        case 7: instance = MSG_CHAT; break;
    }
    alert(MSG_WM + instance);
    return false;
}

EOB;
      showXHTML_script('inline', $scr);
    showXHTML_head_E();
    showXHTML_body_B();
      $ary = array(array($MSG['msg_statistics'][$sysSession->lang], ''));
      echo "<center>\n";
      showXHTML_tabFrame_B($ary);
        showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="760" style="border-collapse: collapse" class="cssTable"');
        showXHTML_tr_B('class="cssTrEvn font01"');
        // #47322 Chrome 課程管理/教材統計 右邊框不見，變更colspan
        showXHTML_td_B('colspan="6"');
        $lasttime = getCronDailyLastExecuteTime();
        if ($lasttime == 0)
        {
            echo $MSG['msg_cron_daily_fail'][$sysSession->lang];
        }else{
            echo $MSG['msg_last_updated_time'][$sysSession->lang].'<font color="red">'.$lasttime.'</font>';
        }
        showXHTML_td_E();
        showXHTML_tr_E();
          showXHTML_tr_B('class="cssTrHead font01"');
          foreach($topics as $x => $item) showXHTML_td('align="center" style="font-weight: bold" nowrap', sprintf('<a href="javascript:s(%d)">%s%s</a>', $x+1, $item, ($x+1==$i ? sprintf('<img src="/theme/default/learn/dude07232001%s.gif" border="0" align="absmiddl">', $d ? 'up' : 'down'):'')));
          showXHTML_tr_E();
          if (is_array($all))
              foreach($all as $fields){
                            $title = $fields['title'];
                            $ret = $ctx1->xpath_eval("/manifest/organizations/organization//item[@identifier='{$fields['activity_id']}']");
                            if($ret){                             
                                  foreach($ret->nodeset as $res)
                                  {
                                    $title=trim(getTitle(getNodeValue($res,'title')));
                                  }
                            }    
                $cln = $cln == 'class="cssTrEvn font01"' ? 'class="cssTrOdd font01"' : 'class="cssTrEvn font01"';
              showXHTML_tr_B($cln);
                $nodeTitle = (empty($title)) ? $fields['title'] : htmlspecialchars($title);
                showXHTML_td('nowrap', '<span style="width: 300px; overflow: hidden"><a href="' . gen_url($fields['url']) . ' title="' . htmlspecialchars($fields['activity_id']) . '" class="link_fnt01">' . $nodeTitle . '</a></spen>');
                showXHTML_td('nowrap style="display: none"', $fields['first']);
                showXHTML_td('nowrap style="display: none"', $fields['last']);
                // #47323 Chrome [教師/課程管理/教材統計] 進行排序時，欄寬會變大-->調整適當欄位寬度
                        showXHTML_td('width="100" align="right" nowrap', zero2gray(sec2timestamp($fields['maxi'])));
                showXHTML_td('width="100" align="right" nowrap', zero2gray(sec2timestamp($fields['mini'])));
                showXHTML_td('width="30" align="right" style="cursor: pointer" onmouseover="this.style.textDecoration=\'underline\';" onmouseout="this.style.textDecoration=\'none\';" onclick="location.href=\'cour_stat_detail.php?activity_id=' . $fields['activity_id'] . '\';" nowrap', $fields['amount']);
                showXHTML_td('width="100" align="right" style="cursor: pointer" onmouseover="this.style.textDecoration=\'underline\';" onmouseout="this.style.textDecoration=\'none\';" onclick="location.href=\'cour_stat_detail.php?activity_id=' . $fields['activity_id'] . '\';" nowrap', zero2gray(sec2timestamp($fields['sec'])));
                showXHTML_td('width="100" align="right" nowrap', zero2gray(sec2timestamp($fields['average'])));
              showXHTML_tr_E();
              }
          else{
            showXHTML_tr_B('class="cssTrOdd font01"');
              $sysConn->ErrorNo() ? showXHTML_td('colspan="7" align="center"', $MSG['msg_error'][$sysSession->lang] . $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg())
                                    : showXHTML_td('colspan="7" align="center"', $MSG['msg_empty'][$sysSession->lang]);
            showXHTML_tr_E();
          }
        showXHTML_table_E();
      showXHTML_tabFrame_E();
      echo "</center>\n";
    showXHTML_body_E();
?>

