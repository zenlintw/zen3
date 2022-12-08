<?php
set_time_limit(0);

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

function getIvqData($params){
    global $sysConn, $sysSession;
    //$sysConn->debug=true;
    $sc = $params['order'] == 'desc' ? 'desc' : 'asc';
    $orderby = empty($params['order_field']) ? "" : "order by {$params['order_field']} {$sc}";
    //0 包含未答題  ,  1 不包含未答題
    $ansType = empty($params['ansType']) ? '0,1,-1': '0,1';
    
    $where = $params['where'];
    
    $sqls = "select
                CONCAT(u.last_name,u.first_name) as realname, 
                user_id, 
                COUNT(CASE WHEN answer IN ({$ansType}) THEN 1 END) AS answerCnt ,
                COUNT(CASE WHEN answer = 1 THEN 1 END) AS rightCnt ,
                ROUND(IFNULL(COUNT(CASE WHEN answer = 1 THEN 1 END) / COUNT(CASE WHEN answer IN ({$ansType}) THEN 1 END) * 100,0),1) AS rightRate 
                from LM_quiz_log as ql left join WM_user_account as u on u.username = ql.user_id
                where ql.course_id={$params['course_id']} {$where}
                group by user_id
                {$orderby} ";
    //debug
    //$sqls = "select user_id as realname,user_id, '20' as rightRate from LM_quiz_log {$orderby} ";

    //取未分頁的總數
    $total = count($sysConn->GetCol($sqls));
    
    //資料分頁
    $sqls .= " limit {$params['begin']}, {$params['limit']} ";
    // $sysConn->debug=true;
    $data = $sysConn->GetArray($sqls);
    if(!empty($data )){
        foreach($data as &$v){
            $v['subject'] = strip_tags($v['subject']);
            $v['question'] = strip_tags($v['question']);
        }
    }
    return array('total'=> $total, 'data'=>$data);
}

function getIvqDetail($params){
    global $sysConn, $sysSession;
    //$sysConn->debug=true;
    $sc = $params['order'] == 'desc' ? 'desc' : 'asc';
    $orderby = empty($params['order_field']) ? "" : "order by {$params['order_field']} {$sc}";
    //0 包含未答題  ,  1 不包含未答題
    $ansType = empty($params['ansType']) ? '0,1,-1': '0,1';
    
    $where .= empty($params['qid'])?'': " and qid={$params['qid']}";
    $where .= empty($params['aid'])?'': " and aid={$params['aid']}";
    
    $sqls = "select
                CONCAT(u.last_name,u.first_name) as realname, 
                user_id, 
                subject ,
                question ,
                time_start,
                time_end,
                answer
                from LM_quiz_log as ql left join WM_user_account as u on u.username = ql.user_id
                where ql.course_id={$params['course_id']} and user_id='{$params['user_id']}' {$where}
                {$orderby} ";
    //debug
    //$sqls = "select user_id as realname,user_id, '20' as rightRate from LM_quiz_log {$orderby} ";

    //取未分頁的總數
    $total = count($sysConn->GetCol($sqls));
    
    //資料分頁
    $sqls .= " limit {$params['begin']}, {$params['limit']} ";
    // $sysConn->debug=true;
    $data = $sysConn->GetArray($sqls);
    if(!empty($data )){
        foreach($data as &$v){
            $v['question'] = strip_tags($v['question']);
        }
    }
    return array('total'=> $total, 'data'=>$data);
}

$api  = $_GET['api'];
$data = $_POST;
chkSchoolId('LM_quiz_log');
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

if( function_exists($api) ){
    $res = call_user_func($api, $data);
    $res['success'] = true;
    echo json_encode($res);
    exit;
}
?>
