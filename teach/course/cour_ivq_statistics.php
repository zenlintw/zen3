<?php
	/**
	 * ivq統計
     1.2	測驗結果從LCMS回傳到WMPro的時機 : 每次有使用者（老師）進入”自我評量統計”頁面時，會由WMPro系統背景回LCMS查詢，補上次到目前未更新的資料。頁面上只要有”資料同步中…”訊息即可，不影響正常操作
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
    
    function syncLcmsData(){
        global $sysConn,$sysSession;
        
        //1. 最後一次更新時間
        $lastTime = $sysConn->GetOne("select time_start from LM_quiz_log where course_id={$sysSession->course_id} order by time_start desc");
        
        if(!empty($lastTime)){
            $lastTime = date('Y-m-d 00:00:00', strtotime($lastTime));
        } else {
            $lastTime = gmdate("Y-m-d H:i:s", time() - 60*60*24*365);
        }
        
        //2. 向lcms詢問是否有新資料
        $params['course_id']  = $sysSession->course_id;
        $params['begin_time'] = $lastTime;
        $resData = lcms_api('api/record/getIVQRecordByCourse', array('data'=> json_encode($params)), 'post');
        
        $syncData = $resData['response']['data'];
        if( !empty($syncData) ){
            $insSql = 'insert ignore into LM_quiz_log (%s) values %s';
            $valSql = array();
            $fields = @implode(',',array_keys($syncData[0]));
            foreach($syncData as $d){
                $d['question'] = addslashes($d['question']);
                $d['subject'] = addslashes($d['subject']);
                $d['system_title'] = addslashes($d['system_title']);
                $valSql[]= "('".implode("','", $d)."')";
            }
            $insSql = sprintf($insSql, $fields, implode(',',$valSql));
            $sysConn->Execute($insSql);
        }
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
    
    syncLcmsData();
    
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
					$ary[] = array($MSG['ivq_statistics'][$sysSession->lang], 'tabs');
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
							showXHTML_td_B('colspan="4"');
								//查詢表單
                                showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="100%" style="border-collapse: collapse" class="cssTable"');
                                    showXHTML_tr_B('class="cssTrEvn font01"');
                                    showXHTML_td('class="cssTrOdd" width="100" align="right" nowrap', $MSG['teaching_material'][$sysSession->lang]);
                                    showXHTML_td_B('width="180" ');
                                        $assetList = getAssetList();
                                        $assetList[0] = $MSG['cour_ivq_msg1'][$sysSession->lang];
                                        ksort($assetList);
                                        echo "<select id='aid' class='cssInput' style='width:100%'>";
                                        if(!empty($assetList)){
                                            foreach($assetList as $k=>$v){
                                                $shortTxt = mb_substr($v,0,80);
                                                echo "<option value='$k' title='$v'>$shortTxt</option>";
                                            }
                                        }
                                        echo "</select>";
                                        
                                    showXHTML_td_E();
                                    showXHTML_td('class="cssTrOdd" align="right" nowrap', $MSG['cour_ivq_msg2'][$sysSession->lang]);
                                    showXHTML_td_B('width="320" ');
                                        $questList = getQuestionList();
                                        $questList[0] = $MSG['cour_ivq_msg1'][$sysSession->lang];
                                        ksort($questList);
                                        echo "<select id='qid' class='cssInput' style='width:100%'>";
                                        if(!empty($questList)){
                                            foreach($questList as $k=>$v){
                                                $shortTxt = mb_substr($v,0,80);
                                                echo "<option value='$k' title='$v'>$shortTxt</option>";
                                            }
                                        }
                                        echo "</select>";
                                        
                                    showXHTML_td_E();
                                    showXHTML_tr_E();
                                    
                                    showXHTML_tr_B('class="cssTrOdd font01"');
                                        showXHTML_td('class="cssTrOdd" align="right" nowrap', $MSG['cour_ivq_msg3'][$sysSession->lang]);
                                        showXHTML_td_B('class="cssTrOdd"');
                                            echo "<input type='radio' name='ans_com' value='0' checked />".$MSG['cour_ivq_msg4'][$sysSession->lang];
                                            echo "<input type='radio' name='ans_com' value='1' />".$MSG['cour_ivq_msg5'][$sysSession->lang];
                                            echo " ".$MSG['cour_ivq_msg6'][$sysSession->lang];
                                        showXHTML_td_E();
                                        showXHTML_td('class="cssTrOdd" align="right" nowrap', '');
                                        showXHTML_td_B('class="cssTrOdd"');
                                        showXHTML_td_E();
                                    showXHTML_tr_E();
                                    showXHTML_tr_B('class="cssTrOdd font01"');
                                        showXHTML_td_B('class="cssTrOdd" colspan="4" style="text-align:center"');
                                            showXHTML_input('button', '', $MSG['cour_ivq_msg7'][$sysSession->lang], '', 'id="search" class="cssBtn"');
                                            showXHTML_input('button', '', $MSG['cour_ivq_msg8'][$sysSession->lang], '', 'id="export" class="cssBtn"');
                                            showXHTML_input('button', '', $MSG['cour_ivq_msg9'][$sysSession->lang], '', 'id="export_detail" class="cssBtn"');
                                        showXHTML_td_E();
                                    showXHTML_tr_E();
                                showXHTML_table_E();
							showXHTML_td_E('');
						showXHTML_tr_E('');

						// 換頁與動作功能列
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td_B('colspan="4" nowrap id="toolbar1"');
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
							showXHTML_td_B('align="center" width="100" id="msg_realname" nowrap="noWrap" title="' . $MSG['msg_realname'][$sysSession->lang] . '"');
                                echo '<a align="center" class="cssAnchor" href="javascript:void(0)" onclick="chgPageSort(this,\'realname\');" >';
                                echo $MSG['msg_realname'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td_B('align="center" id="msg_username" width="100" nowrap="noWrap" title="' . $MSG['msg_username'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:void(0)" onclick="chgPageSort(this,\'user_id\');" >';
                                echo $MSG['msg_username'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td_B('align="center" width="40" id="msg_anspercent" nowrap="noWrap" title="' . $MSG['msg_anspercent'][$sysSession->lang] . '"');
                                echo '<a class="rightRate cssAnchor" href="javascript:void(0)" onclick="chgPageSort(this,\'rightRate\');" >';
                                echo $MSG['msg_anspercent'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td_B('align="center" width="60" id="msg_func" nowrap="noWrap" title="' . $MSG['msg_func'][$sysSession->lang] . '"');
                                echo $MSG['msg_func'][$sysSession->lang];
                            showXHTML_td_E('');
                            
						showXHTML_tr_E('');
                        
						showXHTML_tr_B('class="cssTrOdd" id="no-data-row"');
							showXHTML_td('align="center" colspan="4" ', $MSG['msg_empty_data'][$sysSession->lang]);
						showXHTML_tr_E('');

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						// 換頁與動作功能列
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="4" nowrap id="toolbar2"');
							showXHTML_td_E('&nbsp;');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_body_E('');
?>
<script>
var postParams = {};
var course_id = <?php echo $sysSession->course_id ?>;
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
        
        postParams = {
            course_id: course_id,
            begin: begin,
            limit: $('#page_num').val()
        };
        postParams = $.extend(p,postParams);
        
        postParams.qid = $('#qid').val();
        postParams.aid = $('#aid').val();
        
        $.post('cour_ivq_data.php?api=getIvqData', 
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
                        m.realname = m.realname ? m.realname : '';
                        dataHtml += '<tr class="cssTrEvn font01 data-row">'+
                            '<td width="100" align="left" nowrap>'+m.realname+'</td>'+
                            '<td width="100" align="left" nowrap>'+m.user_id+'</td>'+
                            //'<td width="300" align="center" nowrap>'+m.rightRate+'</td>'+
                            '<td width="300" align="center" nowrap>'+
                            "<div style='width:100%;height:20px;border:1px solid;text-align:right;'><div style='width:"+m.rightRate+"%;height:20px;background:rgb(177, 250, 177);float:left;display:inline-block;'></div><div style='position:absolute;margin-left:10px;'>"+m.rightRate+"% ("+m.rightCnt+"/"+m.answerCnt+") </div></div>"+
                            '</td>'+
                            '<td width="100" align="center" nowrap><input type="button" value="詳細" class="button01" style="width: 80px" onclick="showDetail(\''+m.user_id+'\')"></td>'+
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

function showDetail(user_id){
    var qid = postParams.qid;
    var aid = postParams.aid;
    var ansType = postParams.ansType;
    
    var log_window = window.open("cour_ivq_statistics_detail.php?user_id=" + user_id + "&course_id="+course_id+'&qid='+qid+'&aid='+aid+'&ansType='+ansType, "_blank", "width=800,height=600,toolbar=0,location=0,status=0,menubar=0,directories=0,scrollbars=1,resizable=1");
}

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
    store.load({begin: 0, order:'asc', order_field:'rightRate',ansType:0});
    $('a.rightRate').append('<div data-field="rightRate" class="order order-asc"></div>');
    
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
    
    $('#export').click(function(){
        // 2.2.3.2.5	“匯出” : 可以將依照條件搜尋後不分頁的所有結果(以人為單位)，匯出成CSV檔。資料需要包含 : “搜尋條件”及此頁面的欄位(人員名稱及帳號/答對率/答題數/答題時間總計)。
        var aid = $('#aid').val();
        var qid = $('#qid').val();
        var ansType = $('input[name="ans_com"]:checked').val();
        location.replace('cour_ivq_export.php?func=export&aid='+aid+'&qid='+qid+'&ansType='+ansType);
    });
    
    $('#export_detail').click(function(){
        // 2.2.3.2.6	“匯出明細” : 可以將依照條件搜尋後不分頁的所有結果”測驗明細”，匯出成CSV檔。資料要包含”搜尋條件”及依此搜尋條件所搜尋到的測驗明細的所有欄位(人員名稱及帳號/教材名稱/測驗題目/作答時間起迄/作答結果)。
        var aid = $('#aid').val();
        var qid = $('#qid').val();
        var ansType = $('input[name="ans_com"]:checked').val();
        location.replace('cour_ivq_export.php?func=export_detail&aid='+aid+'&qid='+qid+'&ansType='+ansType);
    });
    
});

</script>