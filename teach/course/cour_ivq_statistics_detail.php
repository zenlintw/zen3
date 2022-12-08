<?php
    /**
     * ivq統計
     1.2    測驗結果從LCMS回傳到WMPro的時機 : 每次有使用者（老師）進入”自我評量統計”頁面時，會由WMPro系統背景回LCMS查詢，補上次到目前未更新的資料。頁面上只要有”資料同步中…”訊息即可，不影響正常操作
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lang/teach_statistics.php');
    require_once(sysDocumentRoot . '/lib/lib_logs.php');
    require_once(sysDocumentRoot . '/teach/custom/co_lcms_api.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
     require_once(sysDocumentRoot . '/lang/people_manager.php');
     require_once(sysDocumentRoot . '/lib/acl_api.php');
    
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
    
    function getRealName($username){
        global $sysConn,$sysSession;
        return $sysConn->GetOne("select CONCAT(IFNULL(`last_name`,''),IFNULL(`first_name`,'')) as realname from WM_user_account where username='{$username}'");
    }
    
    function getAssetList(){
        global $sysConn,$sysSession;
        return $sysConn->GetAssoc("select distinct aid, subject from LM_quiz_log where course_id={$sysSession->course_id}");
    }
    
    function getQuestionList(){
        global $sysConn,$sysSession;
        $data = $sysConn->GetAssoc("select distinct qid, question from LM_quiz_log where course_id={$sysSession->course_id}");
        if(count($data) > 0){
            foreach($data as &$v){
                $v = strip_tags($v);
            }
        }
        return $data;
    }
    
    $assetList = getAssetList();
    $questionList = getQuestionList();
    
    showXHTML_head_B($MSG['ivq_statistics'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('include', '/lib/dragLayer.js');
    showXHTML_script('include', '/lib/common.js');
    showXHTML_script('include', '/public/js/jquery.js');
    echo <<< BOF
    <style>
        .order-desc{
            background:url(/theme/default/academic/dude07232001down.gif);
            width:25px;
            height:11px;
            display:inline-block;
        }
        .order-asc{
            background:url(/theme/default/academic/dude07232001up.gif);
            width:25px;
            height:11px;
            display:inline-block;
        }
    </style>
BOF;
    showXHTML_head_E('');

    showXHTML_body_B('');

        showXHTML_table_B('width="760" align="center" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
            showXHTML_tr_B('');
                showXHTML_td_B('');
                    $ary = array();
                    $ary[] = array($MSG['ivq_statistics_detail'][$sysSession->lang], 'tabs');
                    showXHTML_tabs($ary, 1);
                showXHTML_td_E('');
            showXHTML_tr_E('');
            showXHTML_tr_B('');
                showXHTML_td_B('valign="top" id="CGroup"');
                    showXHTML_input('hidden', 'gpName'    , '', '', 'id="gpName"');
                    showXHTML_input('hidden', 'sby1'      , '', '', 'id="sby1"');
                    showXHTML_input('hidden', 'oby1'      , '', '', 'id="oby1"');
                    // 目前正在第幾頁
                    showXHTML_input('hidden', 'where_page', '', '', 'id="where_page"');
                    showXHTML_input('hidden', 'query_btn' , '', '', 'id="query_btn"');
                    showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="ClassList" class="cssTable"');
                        // 查詢搜尋
                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td_B('colspan="5"');
                                //查詢表單
                                showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="100%" style="border-collapse: collapse" class="cssTable"');
                                    showXHTML_tr_B('class="cssTrEvn font01"');
                                        showXHTML_td('class="cssTrOdd" width="100" align="right" nowrap', '帳號');
                                        showXHTML_td_B('width="180" ');
                                            $realname = getRealName($_GET['user_id']);
                                            $realname = empty($realname) ? '' : "({$realname})";
                                            echo $_GET['user_id'].$realname;
                                        showXHTML_td_E();
                                    showXHTML_tr_E();  
                                    
                                    showXHTML_tr_B('class="cssTrEvn font01"');
                                        showXHTML_td('class="cssTrOdd" align="right" nowrap', $MSG['teaching_material'][$sysSession->lang]);
                                        showXHTML_td_B('width="320" ');
                                            echo !empty($_GET['aid']) ? $assetList[$_GET['aid']]: $MSG['cour_ivq_msg1'][$sysSession->lang];
                                        showXHTML_td_E();
                                    showXHTML_tr_E();  
                                    
                                    showXHTML_tr_B('class="cssTrEvn font01"');
                                        showXHTML_td('class="cssTrOdd" align="right" nowrap', $MSG['field_question'][$sysSession->lang]);
                                        showXHTML_td_B('width="320" ');
                                            echo !empty($_GET['qid']) ? $questionList[$_GET['qid']]:$MSG['cour_ivq_msg1'][$sysSession->lang];
                                        showXHTML_td_E();
                                    showXHTML_tr_E();
                                    
                                    showXHTML_tr_B('class="cssTrEvn font01"');
                                        showXHTML_td('class="cssTrOdd" align="right" nowrap', $MSG['cour_ivq_msg6'][$sysSession->lang]);
                                        showXHTML_td_B('width="320" ');
                                            echo empty($_GET['ansType']) ? $MSG['cour_ivq_msg4'][$sysSession->lang]:$MSG['cour_ivq_msg5'][$sysSession->lang];
                                        showXHTML_td_E();
                                    showXHTML_tr_E();
                                    
                                showXHTML_table_E();
                            showXHTML_td_E('');
                        showXHTML_tr_E('');
                        
                        // 換頁與動作功能列
                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td_B('colspan="5" nowrap id="toolbar1"');
                                showXHTML_table_B('width="760" border="0" cellspacing="0" cellpadding="0"');
                                    showXHTML_tr_B('class="cssTrEvn"');
                                        showXHTML_td_B('nowrap');
                                            
                                            $ary = array();
                                            echo '&nbsp;' , $MSG['page'][$sysSession->lang], '<span id="spanSel1">';
                                            showXHTML_input('select', 'selBtn1', $ary, '1', 'id="selBtn1" class="cssInput"');
                                            echo '</span>&nbsp;';
                                            // 手動輸入 page 的數目
                                            echo $MSG['go_page_no'][$sysSession->lang];
                                            showXHTML_input('text', 'input_page', '', '', 'id="input_page" size="3" class="cssInput"');
                                            echo $MSG['go_page_title'][$sysSession->lang];
                                            showXHTML_input('button', 'btn_go_page1', 'Go', '', 'class="cssBtn" id="btn_go_page1" onclick="go_page_btn(this)"');
                                            // 每頁顯示幾筆
                                            echo '&nbsp;' . $MSG['title134'][$sysSession->lang];
                                            $page_array = array(10=> $MSG['title136'][$sysSession->lang],20 => 20,50 => 50,100 => 100,200 => 200,400 => 400);
                                            showXHTML_input('select', 'page_num', $page_array,10, 'class="cssInput" id="page_num" ');
                                            echo $MSG['title135'][$sysSession->lang];

                                            showXHTML_input('button', 'firstBtn1', $MSG['first'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" onclick="goPage(-1)" title=' . $MSG['title40'][$sysSession->lang]);
                                            showXHTML_input('button', 'prevBtn1',  $MSG['prev'][$sysSession->lang],  '', 'id="prevBtn1"  class="cssBtn" onclick="goPage(-2)" title=' . $MSG['title41'][$sysSession->lang]);
                                            showXHTML_input('button', 'nextBtn1',  $MSG['next'][$sysSession->lang],  '', 'id="nextBtn1"  class="cssBtn" onclick="goPage(-3)" title=' . $MSG['title42'][$sysSession->lang]);
                                            showXHTML_input('button', 'lastBtn1',  $MSG['last'][$sysSession->lang],  '', 'id="lastBtn1"  class="cssBtn" onclick="goPage(-4)" title=' . $MSG['page_end'][$sysSession->lang]);
                                        showXHTML_td_E('');
                                    showXHTML_tr_E('');
                                showXHTML_table_E('');
                            showXHTML_td_E('');
                        showXHTML_tr_E('');

                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td_B('align="center" width="100" id="teaching_material" nowrap="noWrap" title="' . $MSG['teaching_material'][$sysSession->lang] . '"');
                                echo '<a align="center" class="cssAnchor" href="javascript:void(0)" onclick="chgPageSort(this,\'subject\');" >';
                                echo $MSG['teaching_material'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td_B('align="center" id="field_question" width="100" nowrap="noWrap" title="' . $MSG['field_question'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:void(0)" onclick="chgPageSort(this,\'question\');" >';
                                echo $MSG['field_question'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td_B('align="center" width="40" id="begin_time" nowrap="noWrap" title="' . $MSG['begin_time'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:void(0)" onclick="chgPageSort(this,\'time_start\');" >';
                                echo $MSG['begin_time'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td_B('align="center" width="60" id="end_time" nowrap="noWrap" title="' . $MSG['end_time'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:void(0)" onclick="chgPageSort(this,\'time_end\');" >';
                                echo $MSG['end_time'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');
                            
                            showXHTML_td_B('align="center" width="60" id="ans_result" nowrap="noWrap" title="' . $MSG['ans_result'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:void(0)" onclick="chgPageSort(this,\'answer\');" >';
                                echo $MSG['ans_result'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');
                            
                        showXHTML_tr_E('');
                        
                        showXHTML_tr_B('class="cssTrOdd" id="no-data-row"');
                            showXHTML_td('align="center" colspan="5" ', $MSG['msg_empty_data'][$sysSession->lang]);
                        showXHTML_tr_E('');

                        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        // 換頁與動作功能列
                        showXHTML_tr_B($col);
                            showXHTML_td_B('colspan="5" nowrap id="toolbar2"');
                            showXHTML_td_E('&nbsp;');
                        showXHTML_tr_E('');
                    showXHTML_table_E('');
                showXHTML_td_E('');
            showXHTML_tr_E('');
        showXHTML_table_E('');
    showXHTML_body_E('');
?>
<script>
var course_id = <?php echo $sysSession->course_id ?>;
var user_id = "<?php echo $_GET['user_id'] ?>";
var qid = "<?php echo $_GET['qid'] ?>";
var aid = "<?php echo $_GET['aid'] ?>";
var ansType = "<?php echo $_GET['ansType'] ?>";
var store = {
    load: function(p){
        begin = p.begin == null ? 0: p.begin;
        currentPage = $('#selBtn1').val();
        
        if( p.order == null && p.order_field == null ){
            //取得介面上目前的排序
            if( $('.order').length > 0 ){
                p.order_field = $('.order').attr('data-field');
                p.order = $('.order').hasClass('order-asc') ? 'asc' : 'desc';
            }
        }
        
        var postParams = {
            qid:qid,
            aid:aid,
            ansType:ansType,
            user_id:user_id,
            course_id: course_id,
            begin: begin,
            limit: $('#page_num').val()
        };
        postParams = $.extend(p,postParams);
        
        $.post('cour_ivq_data.php?api=getIvqDetail', 
            postParams, 
            function(res){
            if( res.success ){
                $('.data-row').remove();
                
                //渲染頁數Toolbar
                var total = res.total; //未分頁總筆數
                var viewPageNumCnt = $('#page_num').val();  //一頁顯示幾筆
                var totalPageCnt = parseInt(total/viewPageNumCnt);  //查詢後有幾頁
                $('#selBtn1').children().remove();
                if(totalPageCnt > 0){
                    for(var i=1;i<=totalPageCnt;i++){
                        $('#selBtn1').append('<option value="'+i+'">'+i+'</option>');
                    }
                }else{
                    $('#selBtn1').append('<option value="1">1</option>');
                }
                // $('#selBtn1').val(2)  //目前 在or 設定 第幾頁 
                if( begin == 0 ){
                    $('#selBtn1').val(1);
                }else{
                    $('#selBtn1').val(currentPage);
                }
                
                if( res.data.length > 0){
                    $('#no-data-row').hide();
                    var dataHtml = '';
                    $.each(res.data, function(i, m){
                        //$('#data-row').append('');
                        //cln = cln == 'cssTrEvn' ? 'cssTrOdd' : 'cssTrEvn';
                        var ansResult = parseInt(m.answer);
                        switch(ansResult){
                            case -1:
                                ansResult = '未答';
                            break;
                            case 0:
                                ansResult = '<font color=red>答錯</font>';
                            break;
                            case 1:
                                ansResult = '<font color=green>答對</font>';
                            break;
                        }
                        
                        m.time_end = m.time_end == null ? '':m.time_end;
                        
                        dataHtml += '<tr class="cssTrEvn font01 data-row">'+
                            '<td align="left" nowrap>'+m.subject+'</td>'+
                            '<td align="left" nowrap><div style="overflow:hidden;width:200px" title="'+m.question+'">'+m.question+'</div></td>'+
                            '<td align="left" nowrap>'+m.time_start+'</td>'+
                            '<td align="left" nowrap>'+m.time_end+'</td>'+
                            '<td align="left" nowrap>'+ansResult+'</td>'+
                        '</tr>';
                    });
                    
                    $('#no-data-row').after(dataHtml);
                }else{
                    $('#no-data-row').show();
                }
            }
        }, 'json');
    }
};

function goPage(f){
    p = 1;
    switch(f){
        case -1: //第一頁
            p = 1;
        break;
        case -2: //上一頁
            p = parseInt($('#selBtn1').val()) - 1;
            p = p < 1 ? 1: p;
        break;
        case -3: //下一頁
            p = $('#selBtn1 > option:selected + option').val();
            p = p == undefined ? $('#selBtn1 > option:last').val() : p;
        break;
        case -4: //最末
            p = $('#selBtn1 > option:last').val();
    }
    p = parseInt(p);
    $('#selBtn1').val(p);
    begin = ((p-1)*$('#page_num').val());
    store.load({begin: begin });
}

function go_page_btn(el){
    var p = $('#input_page').val();
    p = p < 1 ? 1: p;
    p = p > parseInt($('#selBtn1 > option:last').val()) ? $('#selBtn1 > option:last').val() : p;
    
    $('#selBtn1').val(p);
    $('#input_page').val(p);
    begin = ((p-1)*$('#page_num').val());
    store.load({begin: begin });
}

function chgPageSort(targetEl, field){
    var order = 'asc';
    if( $(targetEl).find('.order-asc').length > 0 ){
        order = 'desc';
    }
    $('a.cssAnchor').find('.order').remove();
    $(targetEl).append('<div data-field="'+field+'" class="order order-'+order+'"></div>');
    store.load({begin: 0, order: order, order_field:field });
}

$(function(){
    store.load({});
    
    $('#selBtn1').change(function(res){
        begin = ((parseInt(this.value)-1)*$('#page_num').val());
        store.load({begin: begin });
    });
    
    $('#page_num').change(function(res){
        store.load({begin: 0 });
    });
    
    $('#search').click(function(){
        var where = '';
        var aid = $('#aid').val();
        if( aid > 0 ){
            where += ' and aid = ' + aid;
        }
        
        var qid = $('#qid').val();
        if( qid > 0 ){
            where += ' and qid = ' + qid;
        }
        
        var ansType = $('input[name="ans_com"]:checked').val();
        
        store.load({begin: 0, where: where, ansType: ansType });
    });
    
});

</script>