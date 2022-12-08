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
    //error_reporting(E_ALL);
    //ini_set('display_errors', 1);
    //$sysConn->debug=true;
	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}
    if($_POST &&$_POST['action']=="export"){
        header('Content-Disposition: attachment; filename="content_reading_detail.utf8.csv"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Type: text/plain; name="cour_statistics_utf8.csv"');
        ob_start();
        $csv_result = str_replace(
            '<br />',
            "\r\n",
            stripslashes($_POST['csv_content'])
        );
        die(utf8_to_excel_unicode($csv_result));
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

	// 主程式開始 /* max(begin_time) as first, min(begin_time) as last, */
	chkSchoolId('WM_record_reading');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

    $sqls = "select activity_id,count(DISTINCT username) as people,count(*) as amount,
                         sum(UNIX_TIMESTAMP(over_time) - UNIX_TIMESTAMP(begin_time) + 1) as sec
                     from WM_record_reading  where course_id={$sysSession->course_id}
                     group by activity_id"; /*!4 FORCE INDEX(idx2) */
    $data=$sysConn->GetAssoc($sqls);
    $pathContent=dbGetOne('WM_term_path', 'content', 'course_id=' . $sysSession->course_id . ' order by serial desc');
    if($pathContent){
        $xmldoc = @domxml_open_mem($pathContent);
        $ctx1 = xpath_new_context($xmldoc);
        $ret = $ctx1->xpath_eval("/manifest/organizations/organization/item");
    }
    showXHTML_head_B($MSG['msg_statistics'][$sysSession->lang], '8');
    showXHTML_CSS('include', "/theme/default/bootstrap/css/bootstrap.min.css");
    showXHTML_CSS('include', "/theme/default/learn_mooc/application.css");
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/learn_mooc/peer.css");
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('include', '/lib/Stupid-Table-Plugin/stupidtable.min.js');
    $js = <<< EOB
    function exportCSV(){
        var csv_content="";
        var jTable=$(".bttable");
        jTable.find('thead th').each(function( ) {
            csv_content+='"'+$(this).text()+'",';
        });
        csv_content=csv_content.substr(0,csv_content.length-1)+"<br />";
        jTable.find('tbody tr').each(function( ) {
            $(this).find('td').each(function( ) {
                csv_content+='"'+$(this).text()+'",';
            });
             csv_content=csv_content.substr(0,csv_content.length-1)+"<br />";
        });
        $('#exportForm').find('[name="csv_content"]').val(csv_content);
        $('#exportForm')[0].submit();
    }
    function detail(activity_id){
        location.href="co_cour_statistics_detail.php?activity_id="+activity_id;
    }
    $(function() {
        $(".bttable").stupidtable();
    });

EOB;
    showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
    showXHTML_form_B('id="exportForm" method="post" target="empty"');
    showXHTML_input('hidden', 'action','export');	// 目前為新增或修改
    showXHTML_input('hidden', 'csv_content');	// 目前為新增或修改
    showXHTML_form_E();


    echo <<<BOF
    <div style="width: 1100px; margin: auto auto;">
        <h3 style="margin-bottom: 0;">
            <span>{$MSG['msg_statistics'][$sysSession->lang]}</span>
            <div class="pull-right" style="margin-top: 5px;"><button type="button" class="btn btn-primary btn-blue add span2" onclick="exportCSV();">{$MSG['export'][$sysSession->lang]}</button></div>
        </h3>
        <div class="box" style="padding:3em; margin-bottom: 1em;">
            <div class="div-border">
                <table class="bttable" cellpadding="5">
                    <thead>
                        <tr>
                            <th class="text-left" data-sort="string">{$MSG['teaching_material'][$sysSession->lang]}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-right" data-sort="int">{$MSG['total_reading_people'][$sysSession->lang]}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-right" data-sort="int">{$MSG['total_reading_count'][$sysSession->lang]}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-right" data-sort="string">{$MSG['total_reading_time'][$sysSession->lang]}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-right" data-sort="string">{$MSG['averaging_reading_time'][$sysSession->lang]}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-right" data-sort="string">{$MSG['averaging_reading_time_one_time'][$sysSession->lang]}<i class='icon-arrow-up'></i><i class='icon-arrow-down'></i></th>
                            <th class="text-center">{$MSG['detail'][$sysSession->lang]}</th>
                        </tr>
                    </thead>
                    <tbody>
BOF;
    if ($ret)
    foreach($ret->nodeset as $res)
    {
        $itemid=$res->get_attribute('identifier');
        $title=trim(getTitle(getNodeValue($res,'title')));
        if(array_key_exists($itemid,$data)){
            $total_reading_time=zero2gray(sec2timestamp($data[$itemid]['sec']));
            $average=round($data[$itemid]['sec']/$data[$itemid]['people']); // 求出平均
            $average_reading_time=zero2gray(sec2timestamp($average));
            $average_amount=round($data[$itemid]['sec']/$data[$itemid]['amount']); // 求出平均
            $average_amount_reading_time=zero2gray(sec2timestamp($average_amount));
            echo <<<BOF
                        <tr>
                            <td class="text-left breakword" style="width:450px;">{$title}</td>
                            <td class="text-right">{$data[$itemid]['people']}</td>
                            <td class="text-right">{$data[$itemid]['amount']}</td>
                            <td class="text-right">{$total_reading_time}</td>
                            <td class="text-right">{$average_reading_time}</td>
                            <td class="text-right">{$average_amount_reading_time}</td>
                            <td class="text-center"><button type="button" class="btn btn-primary btn-blue" onclick="detail('{$itemid}');">{$MSG['detail'][$sysSession->lang]}</button></td>
                        </tr>
BOF;
        }
    }

    echo <<<BOF
                    </tbody>
                </table>
             </div>
        </div>
    </div>
BOF;
    showXHTML_body_E();
?>

