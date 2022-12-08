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
    //$sysConn->debug=true;
    //error_reporting(E_ALL);
    //ini_set('display_errors', 1);
    $permute = array('over_time', 'username', 'realname', 'begin_time', 'over_time', 'duration');
	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

    function getTitle($str) {
        global $sysSession;
        if($str){
            $a = explode("\t",$str);
            switch($sysSession->lang){
                case 'GB2312'		: return $a[1] ? $a[1] : $a[0];
                case 'en'			: return $a[2] ? $a[2] : $a[0];
                case 'EUC-JP'		: return $a[3] ? $a[3] : $a[0];
                case 'user_define'	: return $a[4] ? $a[4] : $a[0];
                default: return $a[0];
            }
        }else{
            return 'No Title';
        }
    }
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
    $_REQUEST['search_value'] = trim($_REQUEST['search_value']);
    if ($_REQUEST['search_key'] == 'name' && $_REQUEST['search_value'])
    {
        $realnames = $sysConn->GetAssoc('select username,if(language="Big5" || language="GB2312",concat(IFNULL(`last_name`,""),IFNULL(`first_name`,"")),concat(IFNULL(`first_name`,"")," ",IFNULL(`last_name`,""))) as realname from WM_user_account group by username having realname like "%' . escape_LIKE_query_str($_REQUEST['search_value']) . '%"');
        if (is_array($realnames))
        {
            $condition = sprintf('from WM_record_reading where course_id=%u and activity_id="%s"',
                    $sysSession->course_id,
                    $_REQUEST['activity_id']
                ). ' and username in ("' . implode('","', array_keys($realnames)) . '")';
        }
        else
        {
            $condition = sprintf('from WM_record_reading where course_id=%u and activity_id="%s"',
                $sysSession->course_id,
                $_REQUEST['activity_id']
            );
        }
    }
    elseif ($_REQUEST['search_key'] == 'account' && $_REQUEST['search_value'])
    {
        $condition = sprintf('from WM_record_reading where course_id=%u and activity_id="%s"',
                $sysSession->course_id,
                $_REQUEST['activity_id']
            ) . ' and username like "%' . escape_LIKE_query_str($_REQUEST['search_value']) . '%"';
    }
    else
    {
        $condition = sprintf('from WM_record_reading where course_id=%u and activity_id="%s"',
            $sysSession->course_id,
            $_REQUEST['activity_id']
        );
    }
    $keep = $ADODB_FETCH_MODE;
    $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
    list($amount, $sec) =
        $sysConn->GetRow('select count(*), sum(UNIX_TIMESTAMP(over_time) - UNIX_TIMESTAMP(begin_time) + 1) ' . $condition . ' group by activity_id');

    $ADODB_FETCH_MODE = $keep;
    // title額外取，避免老師修改過節點名稱後取到舊的名稱
    $pathContent=dbGetOne('WM_term_path', 'content', 'course_id=' . $sysSession->course_id . ' order by serial desc');
    $xmldoc = @domxml_open_mem($pathContent);
    $ctx1 = xpath_new_context($xmldoc);
    $ret = $ctx1->xpath_eval("//item[@identifier='{$_REQUEST['activity_id']}']");
    $title=trim(getTitle(getNodeValue($ret->nodeset[0],'title')));
    $total_item    = (int)$sysConn->GetOne('select count(*) ' . $condition);
    $item_per_page = max((int)$_REQUEST['ipp'], 30);
    $total_page    = max(ceil($total_item / $item_per_page), 1);
    $curr_page     = min(max((int)$_REQUEST['cp'], 1), $total_page);
    $sort          = in_array($_REQUEST['sort'],$permute)?$permute[array_search($_REQUEST['sort'],$permute)]:$permute[0];
    $direct        = eregi('^(ASC|DESC)$', $_REQUEST['direct']) ? $_REQUEST['direct'] : 'DESC';
    $sort          = $sort ? (' order by ' . $sort . ' ' . $direct) : '';
    $pages         = range(0, $total_page); unset($pages[0]);

    $rs = $sysConn->GetArray('select username, begin_time, over_time, (UNIX_TIMESTAMP(over_time) - UNIX_TIMESTAMP(begin_time) + 1) as duration ' .
        $condition . $sort .
        ($_REQUEST['expo']? '' : (' limit ' . (($curr_page - 1) * $item_per_page) . ',' . $item_per_page))
    );

    if(!isset($realnames)){
        if (is_array($rs) && count($rs)) {
            foreach($rs as $row) $names[] = $row[0];
            $realnames = $sysConn->GetAssoc('select username, if(first_name REGEXP "^[0-9A-Za-z _-]*$" && last_name REGEXP "^[0-9A-Za-z _-]*$", concat(IFNULL(`first_name`,""), " ", IFNULL(`last_name`,"")), concat(IFNULL(`last_name`,""), IFNULL(`first_name`,""))) from WM_user_account where username in ("' . implode('","', array_unique($names)) . '")');
        }
    }
    if ($_REQUEST['expo'])
    {
        header('Content-Disposition: attachment; filename="content_reading_detail.utf8.csv"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Type: text/plain; name="content_reading_detail.utf8.csv"');
        ob_start();
        printf(('%s > %s > ' . $MSG['msg_time3'][$sysSession->lang] . ' %u ' . $MSG['hits'][$sysSession->lang] . ' %s' . $MSG['detail_is_following'][$sysSession->lang]) . "\r\n", $sysSession->course_name, $title, $amount, sec2timestamp($sec));
        printf("\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\r\n", $MSG['account'][$sysSession->lang],$MSG['realname'][$sysSession->lang], $MSG['begin_time'][$sysSession->lang], $MSG['end_time'][$sysSession->lang], $MSG['duration'][$sysSession->lang]);
        foreach($rs as $v)
        {
            printf("\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\r\n", $v[0], str_replace('"', '\\"', $realnames[$v[0]]), $v[1], $v[2], sec2timestamp($v[3]));
        }
        $result = ob_get_contents();
        ob_end_clean();
        die(utf8_to_excel_unicode($result));
    }

    showXHTML_head_B($MSG['msg_statistics'][$sysSession->lang], '8');
    showXHTML_CSS('include', "/theme/default/bootstrap/css/bootstrap.min.css");
    showXHTML_CSS('include', "/theme/default/learn_mooc/application.css");
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/learn_mooc/peer.css");
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('include', '/lib/kc-paginate.js');
    $js = <<< EOB

function exportCSV(){
    $("#expo").val(1);
    $('#detailForm')[0].submit();
}

function doSearch(){
    $("#expo").val(0);
    $('#detailForm')[0].submit();
}
$(function () {
    var sort='{$_REQUEST['sort']}';
    var sortingClass='{$_REQUEST['direct']}'=="DESC"?"sorting-desc":"sorting-asc";
    $("#detailTable").find('th[data-sort-key="'+sort+'"]').addClass(sortingClass);
    // 分頁工具列
    $('#pageToolbar').paginate({
        'total': {$total_item},
        'pageSize': {$item_per_page},
        'pageNumber': {$curr_page},
        'showPageList': false,
        'showRefresh': false,
        'showSeparator': false,
        'btnTitleFirst': '{$MSG['first_page'][$sysSession->lang]}',
        'btnTitlePrev': '{$MSG['prev_page'][$sysSession->lang]}',
        'btnTitleNext': '{$MSG['next_page'][$sysSession->lang]}',
        'btnTitleLast': '{$MSG['last_page'][$sysSession->lang]}',
        'btnTitleRefresh': '',
        'beforePageText': '',
        'afterPageText': '/ {pages}',
        'beforePerPageText': '',
        'afterPerPageText': '',
        'displayMsg': '{from} - {to} 共 {total} 筆',
        'buttonCls': '',
        'onSelectPage': function (num, size) {
            $('#cp').val(num);
            doSearch();
        }
    });

    $("#searchBtn").click(function (e) {
            $('#pageToolbar').paginate('select', 1);
            $('#cp').val(1);
    });
    // Enter等同送出
    $("#search_value").keypress(function (e) {
        if (e.keyCode == 13) {
            $("#searchBtn").click();
        }
    });

    // 指定分頁
    $(".paginate-number").keypress(function (e) {
        if (e.keyCode == 13) {
            $('#pageToolbar').paginate('select', $(this).val());
        }
    });
        
    $("#detailTable").find('th').on('click',function(){
        var sortkey=$(this).data('sort-key');
        if(!sortkey) return;
        var sortdirect=$(this).hasClass('sorting-asc')?'DESC':'ASC';
        $("#sort").val(sortkey);
        $("#direct").val(sortdirect);
        doSearch();
    });
});

EOB;
    showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
    $action_form=htmlentities($_SERVER['REQUEST_URI']);
    $detail_msg=sprintf(($MSG['msg_time3'][$sysSession->lang] . ' <span style="color: red">%u</span> ' . $MSG['hits'][$sysSession->lang] . ' <span style="color: red">%s</span>' . $MSG['detail_is_following'][$sysSession->lang]), $amount, zero2gray(sec2timestamp($sec)));
    $search_key_account_selected=$_REQUEST['search_key']=="account"?'selected="selected"':'';
    $search_key_name_selected=$_REQUEST['search_key']=="name"?'selected="selected"':'';
    echo <<<BOF
    <div style="width: 1100px; margin: auto auto;">
        <h3 style="margin-bottom: 0;">
            <span style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;width: 1100px;display: inline-block;"><a href="co_cour_statistics.php">{$MSG['msg_statistics'][$sysSession->lang]}</a>>{$title}</span>
        </h3>
        <div style="height: 40px;">
            <div class="pull-left" style="margin-top: 10px;">{$detail_msg}</div>
            <div class="pull-right" style="margin-top: 5px;">
                <form id="detailForm" class="form-search" method="post" action="{$action_form}">
                <input type="hidden" id="expo" name="expo" value="" />
                <input type="hidden" id="cp"  name="cp" value="{$curr_page}" />
                <input type="hidden" id="ipp" name="ipp" value="{$item_per_page}" />
                <input type="hidden" id="sort" name="sort" value="{$_REQUEST['sort']}" />
                <input type="hidden" id="direct" name="direct" value="{$direct}" />
                <select class="pull-left input-small" id="search_key" name="search_key" style=" height: 28px;">
                    <option value="account" {$search_key_account_selected}>{$MSG['account'][$sysSession->lang]}</option>
                    <option value="name" {$search_key_name_selected}>{$MSG['realname'][$sysSession->lang]}</option>
                </select>
                <input type="text" class="pull-left input-small" id="search_value"  name="search_value" size="20" maxlength="32" style="height: 28px;" value="{$_REQUEST['search_value']}" />
                <button type="button" class="btn btn-primary btn-blue add span2" id="searchBtn">{$MSG['search'][$sysSession->lang]}</button>
                <button type="button" class="btn btn-primary btn-blue add span2" onclick="exportCSV();">{$MSG['export'][$sysSession->lang]}</button>
            </form>
            </div>
        </div>
        <div class="box" style="padding:3em; margin-bottom: 1em;">
            <div class="div-border">
                <table id="detailTable" class="bttable" cellpadding="5">
                    <thead>
                        <tr>
                            <th class="text-left" data-sort-key="username">{$MSG['account'][$sysSession->lang]}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-left">{$MSG['realname'][$sysSession->lang]}</i></th>
                            <th class="text-right" data-sort-key="begin_time">{$MSG['begin_time'][$sysSession->lang]}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-right" data-sort-key="end_time">{$MSG['end_time'][$sysSession->lang]}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-right" data-sort-key="duration">{$MSG['duration'][$sysSession->lang]}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                        </tr>
                    </thead>
                    <tbody>
BOF;
        foreach($rs as $v)
        {
            $realname=htmlspecialchars($realnames[$v[0]]);
            $time=zero2gray(sec2timestamp($v[3]));
            echo <<<BOF
                        <tr>
                            <td class="text-left breakword">$v[0]</td>
                            <td class="text-left">{$realname}</td>
                            <td class="text-right">{$v[1]}</td>
                            <td class="text-right">{$v[2]}</td>
                            <td class="text-right">{$time}</td>
                        </tr>
BOF;
        }

    echo <<<BOF
                    </tbody>
                </table>
             </div>
             <div id='pageToolbar'></div>
        </div>

    </div>

BOF;
    showXHTML_body_E();
?>

