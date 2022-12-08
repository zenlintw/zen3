<?php
set_time_limit(0);

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

/**
 // 2.2.3.2.5	“匯出” : 可以將依照條件搜尋後不分頁的所有結果(以人為單位)，匯出成CSV檔。資料需要包含 : “搜尋條件”及此頁面的欄位(人員名稱及帳號/答對率/答題數/答題時間總計)。
**/
function export($params){
    global $sysConn, $sysSession;
    
    //$sysConn->debug=true;
    
    //0 包含未答題  ,  1 不包含未答題
    $ansType = empty($params['ansType']) ? '0,1,-1': '0,1';
    $where  = '';
    $where .= empty($params['aid']) ? '': " and aid={$params['aid']}";
    $where .= empty($params['qid']) ? '': " and qid={$params['qid']}";
    
    
    $sqls = "select CONCAT(u.last_name,u.first_name) as realname, 
                user_id, 
                COUNT(CASE WHEN answer IN ({$ansType}) THEN 1 END) AS answerCnt ,
                COUNT(CASE WHEN answer = 1 THEN 1 END) AS rightCnt ,
                ROUND(IFNULL(COUNT(CASE WHEN answer = 1 THEN 1 END) / COUNT(CASE WHEN answer IN ({$ansType}) THEN 1 END) * 100,0),1) AS rightRate 
                from LM_quiz_log as ql left join WM_user_account as u on u.username = ql.user_id
                where ql.course_id={$sysSession->course_id} {$where}
                group by user_id
                {$orderby} ";
    //debug
    //$sqls = "select user_id as realname,user_id, '20' as rightRate from LM_quiz_log {$orderby} ";

    //$sysConn->debug=true;
    
    //花費時間
    $ansSumTime = $sysConn->GetArray("SELECT 
            user_id, UNIX_TIMESTAMP(time_end)-UNIX_TIMESTAMP(time_start) as readtime 
            FROM `LM_quiz_log` 
            where course_id={$sysSession->course_id} and answer IN ({$ansType}) and time_end IS NOT NULL and time_start IS NOT NULL {$where}");
    $userAnsTime = array();
    
    foreach($ansSumTime as $v){
        $userAnsTime[$v['user_id']] += (int)$v['readtime'];
    }
    
    $csv = array();
    $csv[]=mb_convert_encoding("姓名,帳號,答對率(%),答題數,答題時間總計(秒)", 'Big5' , 'UTF-8' );
    $data = $sysConn->GetArray($sqls);
    if(!empty($data )){
        foreach($data as &$v){
            $ut = (int)$userAnsTime[$v['user_id']];
            $csv[]= mb_convert_encoding( "{$v['realname']},{$v['user_id']},{$v['rightRate']},{$v['answerCnt']},{$ut}" , 'Big5' , 'UTF-8' );
        }
    } 
    $filename = 'Export_'.date('Ymd').'.csv';
    header('Content-type:application/force-download');
    header('Content-Transfer-Encoding: Binary');
    header('Content-Disposition:attachment;filename='.$filename); //檔名
    echo implode("\n",$csv);
    exit;
}

/**
// 2.2.3.2.6	“匯出明細” : 可以將依照條件搜尋後不分頁的所有結果”測驗明細”，匯出成CSV檔。資料要包含”搜尋條件”及依此搜尋條件所搜尋到的測驗明細的所有欄位(人員名稱及帳號/教材名稱/測驗題目/作答時間起迄/作答結果)。
**/
function export_detail($params){
    global $sysConn, $sysSession;
    
    //$sysConn->debug=true;
    
    //0 包含未答題  ,  1 不包含未答題
    $ansType = empty($params['ansType']) ? '0,1,-1': '0,1';
    $where  = '';
    $where .= empty($params['aid']) ? '': " and aid={$params['aid']}";
    $where .= empty($params['qid']) ? '': " and qid={$params['qid']}";
    
    //人員名稱及帳號/教材名稱/測驗題目/作答時間起迄/作答結果
    $sqls = "select CONCAT(u.last_name,u.first_name) as realname, 
                user_id, subject, question, time_start, time_end, answer
                from LM_quiz_log as ql left join WM_user_account as u on u.username = ql.user_id
                where ql.course_id={$sysSession->course_id} {$where}
                order by user_id";
    
    $csv = array();
    $csv[]=mb_convert_encoding("姓名,帳號,教材名稱,測驗題目,開始作答時間,結束作答時間,作答結果", 'Big5' , 'UTF-8' );
    $data = $sysConn->GetArray($sqls);
    if(!empty($data )){
        foreach($data as &$v){
            $ut = (int)$userAnsTime[$v['user_id']];
            if( $v['answer'] == -1 ){
                $ansResult = '未答';
            }else if( $v['answer'] == 0 ){
                $ansResult = '答錯';
            }else if( $v['answer'] == 1 ){
                $ansResult = '答對';
            }
            
            //csv 特殊字元過濾
            $v['subject'] = str_replace(array(',',"\n","\r\n",'"'),array('','','',''),strip_tags($v['subject']));
            $v['question'] = str_replace(array(',',"\n","\r\n",'"'),array('','','',''),strip_tags($v['question']));
            
            $csv[]= mb_convert_encoding( "\"{$v['realname']}\",\"{$v['user_id']}\",\"{$v['subject']}\",\"{$v['question']}\",\"{$v['time_start']}\",\"{$v['time_end']}\",\"$ansResult\"" , 'Big5' , 'UTF-8' );
        }
    }
    
    $filename = 'Export_Detail_'.date('Ymd').'.csv';
    header('Content-type:application/force-download');
    header('Content-Transfer-Encoding: Binary');
    header('Content-Disposition:attachment;filename='.$filename); //檔名
    echo @implode("\n",$csv);
    exit;
}

$api  = $_GET['func'];

chkSchoolId('LM_quiz_log');
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

if( function_exists($api) ){
    call_user_func($api, $_GET);
}
?>
