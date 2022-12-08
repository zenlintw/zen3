<?php
/**
 * 偵測惡意資料程序
 *
 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @package     WM3
 * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
 * @copyright   2000-2006 SunNet Tech. INC.
 * @version     CVS: $Id: detect_malicious_data.php,v 1.1 2010/02/24 02:39:33 saly Exp $
 * @link        http://demo.learn.com.tw/1000110138/index.html
 * @since       2006-08-17
 */

require_once(dirname(__FILE__) . '/filter_spec_char.php');
require_once(sysDocumentRoot . '/lib/Hongu/Validate/Validator/XssAttack.php');

if (!function_exists('getallheaders')) {
    function getallheaders() {
    $headers = array();
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
    return $headers;
    }
}

if (!function_exists('array_intersect_key')) {
    function array_intersect_key()
    {
        if (($c = func_num_args()) < 2)
            return false;
        
        $argv = func_get_args();
        if (!is_array($argv[0]))
            return false;
        $a = array_keys($argv[0]);
        $org =& $argv[0];
        for ($i = 1; $i < $c; $i++) {
            if (is_array($argv[$i])) {
                $b = array_diff($a, array_keys($argv[$i]));
                foreach ($b as $k)
                    unset($org[$k]);
            }
        }
        return $argv[0];
    }
}

function detectAttackBlockedIP() {
    global $sysConn, $_GET, $_POST, $_REQUEST, $_COOKIE, $_SERVER, $_FILES;

    $address = wmGetUserIp();               // user IP

    // 不擋除高雄辦公室對外的ip
    if ($address == '220.133.229.253') return false;
    // 內部弱掃的IP
    if (($address == '192.168.11.190')||($_SERVER['REMOTE_ADDR'] == '192.168.11.190')) return false;
    // 客戶委外的廠商的ip
    if (($address == '60.248.220.233')||($_SERVER['REMOTE_ADDR'] == '60.248.220.233')) return false;

    if (isset($_COOKIE['school_hash'])) {
        $sid = substr($_COOKIE['school_hash'],17,5);
    }else{
        $sid = 10001;
    }
    $sysConn->Execute(sprintf('USE %s%d;',sysDBprefix,$sid));

    $username = $sysConn->GetOne(sprintf("SELECT username FROM WM_session WHERE idx='%s'",mysql_escape_string($_COOKIE['idx'])));
    if (empty($username)) $username='guest';

    // 帳號為root, sunnet不擋除
    if (in_array($username, array('root','sunnet'))) return false;

    $BlockedId = $sysConn->GetOne(sprintf("select blocked_id from WM_blocked_attack_ip where blocked_ip_address='%s' and NOW() between start_time and end_time",$address));
    if ($BlockedId) {
        // 寫入阻擋時，所有的傳參
        $blocked_log_filepath = $sysConn->GetOne(sprintf("select blocked_log_filepath from WM_blocked_attack_ip where blocked_id=%d",$BlockedId));

        // 每個log檔案只記錄10MB，免得被資安弱掃軟體將log檔案灌爆
        $logFilesize = round(filesize($blocked_log_filepath)/pow(1024, 2));  //filesize unit: bytes => MB
        if ($logFilesize < 10) {
            $fpBlockedLog = fopen($blocked_log_filepath, 'a+');

            $note = sprintf("%s\t%s\t%s\t%s\r\n==========\r\n",
                        $username,
                        date('Y-m-d H:i:s'),
                        $address,
                        mysql_escape_string($_SERVER['SCRIPT_NAME']));

            $note .= sprintf("HEADER\t%s\r\n",serialize(getallheaders()));
            if (isset($_GET)) $note .= sprintf("GET\t%s\r\n",serialize($_GET));
            if (isset($_POST)) $note .= sprintf("POST\t%s\r\n",serialize($_POST));
            if (isset($_REQUEST)) $note .= sprintf("REQUEST\t%s\r\n",serialize($_REQUEST));
            if (isset($_COOKIE)) $note .= sprintf("COOKIE\t%s\r\n",serialize($_COOKIE));
            $note .= "-----------\r\n";
            fwrite($fpBlockedLog, $note);
            fclose($fpBlockedLog);
        }

        // 累加阻擋的次數
        $sysConn->Execute(sprintf("update WM_blocked_attack_ip set blocked_count=blocked_count+1 where blocked_id=%d",$BlockedId));

        die('Your IP: '. htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES) .' is Blocked.Contact Web Administrator.');        
    }
}

function logUserAttach($note) {
    global $sysConn, $_GET, $_POST, $_REQUEST, $_FILES, $_SERVER, $_COOKIE, $UserAttackLog;

    $attackId = uniqid();
    $address = wmGetUserIp();               // user IP
    $buildOtherDbLink = false;

    // 自動回傳的內容 - 會被擋除，造成ip被擋
    if ( $_SERVER['REQUEST_URI'] == '/lib/save_temporary.server.php') return false;
    if ( $_SERVER['REQUEST_URI'] == '/teach/grade/grade_exportCSV.php') return false;

    $note = sprintf("%s\t%s\t%s\t%s\t%s\r\n",
            $attackId,
            date('Y-m-d H:i:s'),
            $address,
            $note,
            $_SERVER['REQUEST_URI']
        );

    $fp = fopen($UserAttackLog, 'a+');
    fwrite($fp, $note);
    fclose($fp);

    $link = mysql_connect(sysDBhost, sysDBaccoount, sysDBpassword);

    if (PHP_VERSION >= '7') {
        if (is_null($sysConn)) {
            $sysConn = new StdClass;
            $sysConn->_connectionID = $link;
            $buildOtherDbLink = true;
        }
    }

    if (!$link) {
        die('Database Connecting failure !');
    }
    if (isset($_COOKIE['school_hash'])) {
        $sid = substr($_COOKIE['school_hash'],17,5);
    }else{
        $sid = 10001;
    }

    mysql_query(sprintf('USE %s%d;',sysDBprefix,$sid), $link);

    $result = mysql_query(sprintf("SELECT username FROM WM_session WHERE idx='%s'",mysql_escape_string($_COOKIE['idx'])),$link);
    $row = mysql_fetch_assoc($result);
    $username = ($row === FALSE)?'guest':$row['username'];

    $sql = sprintf("INSERT INTO `WM_log_others` (function_id, username, log_time, department_id,instance,result_id, note, remote_address, user_agent, script_name) values (999403001, '%s', NOW(), 10001, 0, 1, 'Attack:%s', '%s',0,'%s')",$username,$attackId,$address,mysql_escape_string($_SERVER['SCRIPT_NAME']));
    mysql_query($sql, $link);

    // 是否已被阻擋過ip了
    $result = mysql_query(sprintf("select blocked_id from WM_blocked_attack_ip where blocked_ip_address='%s' and NOW() between start_time and end_time",$address),$link);
    $row = mysql_fetch_assoc($result);
    $BlockedId = ($row === FALSE)?false:$row['blocked_id'];
    if ($BlockedId) {
        // 寫入阻擋時，所有的傳參
        $result = mysql_query(sprintf("select blocked_log_filepath from WM_blocked_attack_ip where blocked_id=%d",$BlockedId),$link);
        $row = mysql_fetch_assoc($result);
        $blocked_log_filepath = $row['blocked_log_filepath'];

        // 每個log檔案只記錄10MB，免得被資安弱掃軟體將log檔案灌爆
        $logFilesize = round(filesize($blocked_log_filepath)/pow(1024, 2));  //filesize unit: bytes => MB
        if ($logFilesize < 10) {
            $fpBlockedLog = fopen($blocked_log_filepath, 'a+');

            $note = sprintf("%s\t%s\t%s\t%s\r\n==========\r\n",
                        $username,
                        date('Y-m-d H:i:s'),
                        $address,
                        mysql_escape_string($_SERVER['SCRIPT_NAME']));

            $note .= sprintf("HEADER\t%s\r\n",serialize(getallheaders()));
            if (isset($_GET)) $note .= sprintf("GET\t%s\r\n",serialize($_GET));
            if (isset($_POST)) $note .= sprintf("POST\t%s\r\n",serialize($_POST));
            if (isset($_REQUEST)) $note .= sprintf("REQUEST\t%s\r\n",serialize($_REQUEST));
            if (isset($_COOKIE)) $note .= sprintf("COOKIE\t%s\r\n",serialize($_COOKIE));
            $note .= "-----------\r\n";
            fwrite($fpBlockedLog, $note);
            fclose($fpBlockedLog);
        }
        // 累加阻擋的次數
        mysql_query(sprintf("update WM_blocked_attack_ip set blocked_count=blocked_count+1 where blocked_id=%d",$BlockedId),$link);
    }else{
        //1小時內超過10次，就擋IP
        $oneHourAgo = date('Y-m-d H:i:s',(time()-3600));
        $result = mysql_query(sprintf("SELECT COUNT(*) as acount FROM WM_log_others WHERE function_id=999403001 AND username='%s' AND remote_address='%s' AND log_time>'%s'",$username,$address,$oneHourAgo), $link);
        $row = mysql_fetch_assoc($result);
        $count = ($row === FALSE)?0:$row['acount'];

        if ($count >= 10) {
            $sql = sprintf("INSERT INTO `WM_log_others` (function_id, username, log_time, department_id,instance,result_id, note, remote_address, user_agent, script_name) values (999403403, '%s', NOW(), 10001, 0, 1, 'Attack:%s', '%s',0,'%s')",$username,$attackId,$address,mysql_escape_string($_SERVER['SCRIPT_NAME']));
            mysql_query($sql, $link);

            $blocked_log_rootDir = sysDocumentRoot.'/base/10001/door/attack_blocked_logs';
            if (!file_exists($blocked_log_rootDir)) @mkdir($blocked_log_rootDir, 0777);
            $blocked_log_TodayDir = $blocked_log_rootDir.'/'.date('Y-m-d');
            if (!file_exists($blocked_log_TodayDir)) @mkdir($blocked_log_TodayDir, 0777);
            $blocked_log_filepath = sprintf('%s/%s_%s_%s.log',$blocked_log_TodayDir,$address,$username,uniqid());

            // 先將前三次的攻擊字串寫入log
            $rs = mysql_query(sprintf("SELECT * FROM WM_log_others WHERE function_id=999403001 AND username='%s' AND remote_address='%s' AND log_time>'%s'",$username,$address,$oneHourAgo), $link);
            if ($rs) {
                $fpBlockedLog = fopen($blocked_log_filepath, 'a+');
                while($row = mysql_fetch_assoc($rs)) {
                    $note = sprintf("%s\t%s\t%s\t%s\t%s\r\n-----------\r\n",
                        $row['username'],
                        $row['log_time'],
                        $row['remote_address'],
                        $row['script_name'],
                        $row['note']
                    );
                    fwrite($fpBlockedLog, $note);
                }
                fclose($fpBlockedLog);
            }

            $sql = sprintf("INSERT INTO `WM_blocked_attack_ip` (username, start_time, end_time, blocked_ip_address, create_time, blocked_count, blocked_log_filepath)
                values ('%s', NOW(), DATE_ADD(NOW(), INTERVAL 2 HOUR), '%s', NOW(), 0, '%s')",
                $username,$address,$blocked_log_filepath);
            mysql_query($sql, $link);
        }
    }

    if ($buildOtherDbLink) {
        unset($sysConn);
    }
    mysql_close($link);
}

/**
 * 偵測惡意字串攻擊
 * @param  array  &$vals            輸入值
 * @param  boolean $sqlInectionForce 是否加強sqlinjection的檢查
 * @return void
 */
function detect_malicious_data(&$vals, $sqlInectionForce=false)
{
    global $hongu, $exposeSqlInjectionRules;
    
    if ( $_SERVER['REQUEST_URI'] == '/lib/save_temporary.server.php') return;
    
    if (is_array($vals))
        foreach ($vals as $k => $v) {
            if (is_array($v)) {
                detect_malicious_data($vals[$k]);
                continue;
            } else {
                // 如果是 xmlapi 上傳的附檔 (base64)，不作驗證
                if ($_SERVER['SCRIPT_NAME'] === '/xmlapi/index.php' && $k === 'base64') {
                    continue;
                }

                $s = stripslashes($v);
                // if (base64_decode($s, true) !== false){
                //    $s = trim(base64_decode($s, true));
                // }

                /* if (strlen($s) < 20) continue;
                if (preg_match('!^(<\?xml\b[^>]*\?>)?\s*<(\w+)\b[^>]*>.*</\2>\s*$!is', $s) && @domxml_open_mem($s) !== false)
                    return;
                */

                /* 044001 wmpro5註解導致 mis #044001 問題 (B)*/
                if(preg_match("/\試卷內容/i", $s) || preg_match("/\试卷内容/i", $s) || preg_match("/\Test Content/i", $s)){
                    if (preg_match('!^(<\?xml\b[^>]*\?>)?\s*<(\w+)\b[^>]*>.*</\2>\s*$!is', $s) && @domxml_open_mem($s) !== false)
                        return;
                }
                /* 044001 wmpro5註解導致 mis #044001 問題 (E)*/

                if (preg_match('/\/etc\/passwd/i', $s)){
                    logUserAttach($s);
                    header('HTTP/1.1 403 Forbidden');
                    exit;
                }

               if ( preg_match('/[\'"]?\)?\s+OR\s+.*\s--/i', $s) ||
                    preg_match('/^-1\s+OR\s+/i', $s) ||
                    preg_match('/SLEEP\s*\(\s*\d*\s*\)/i', $s) || 
                    preg_match('/[\'"]?\)?\s+OR\s+.*\'\w+\'=\'/i', $s) ||
                    preg_match('/^\w*[\'"]?\)?\s+OR\s+\(?((\d+)\s*=\s*\2\s*--|([\'"])(\w+)\3\s*=\s*\3\w+)/i', $s) ||
                    preg_match('/\w*[\'"]?\)?\s+OR\s+\(?SLEEP\s*\(\s*\d*\s*\)\s*--/i', $s) ||
                    preg_match('/^\w*[\'"]?\)?\s+AND\s+\(?((\d+)\s*=\s*\2\s*--|([\'"])(\w+)\3\s*=\s*\3\w+)/i', $s) ||
                    preg_match('/^\w*[\'"]?\)?\s+UNION\s+(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|SHOW|REPLACE|ALTER|GRANT|LOAD)\b/i', $s)
                   ){
                    logUserAttach($s);
                    header('HTTP/1.1 403 Forbidden');
                    exit;
                }

                // phpMyAdmin, xmlapi/index.php 不驗證sql injection
                // 若value是xml或是html也不驗sql injection
                if (($sqlInectionForce) &&
                    ($_SERVER['SCRIPT_NAME'] != '/xmlapi/index.php') && 
                    (strpos($_SERVER['SCRIPT_NAME'], '/academic/dbcs/') === FALSE) && 
                    (!$dom = @domxml_open_mem($s)) && 
                    ($s == strip_tags($s))
                ){
                    // 使用Expose專案的sql inject過濾字串
                    if (is_array($exposeSqlInjectionRules->filters) && count($exposeSqlInjectionRules->filters)){
                        $filterSqlInjects = $exposeSqlInjectionRules->filters;
                        foreach($filterSqlInjects as $objFilter){
                            if (preg_match('/'.$objFilter->rule.'/im', $s) === 1){
                                if (empty($_COOKIE['show_me_info']) === false) {
                                    echo $objFilter->id."\n";var_dump($s);exit;
                                }
                                logUserAttach($s);
                                header("HTTP/1.1 403 Forbidden");
                                exit;
                            }
                        }
                    }
                }

                //save_temporary.server.php 所傳入的值會被擋，若經過濾掉即可使用
                if(strpos($_SERVER['SCRIPT_NAME'],'item_choice_template.php')!==false){
                    return;
                }
                
                while (preg_match('/\b((dyn)?src|href|url|codebase)\s*=\s*"?(javascript|vbscript|mocha|livescript):/i', $s) || 
                    preg_match('/\b:\s*url\s*\((javascript|vbscript|mocha|livescript):/i', $s) || 
                    preg_match('/\b:\s*expression\s*\(/i', $s) || !$hongu->validate($s))
                {
                    logUserAttach($s);
                    header('HTTP/1.1 403 Forbidden');
                    exit;
                }
            }
        }
}

function escapeRequestParams(&$vals)
{
    if (get_magic_quotes_gpc()) return;
    if (is_array($vals)){
        foreach ($vals as $k => $v)
        {
            if (is_array($v)) {
                escapeRequestParams($vals[$k]);
                continue;
            } else {
                $vals[$k] = addslashes($v);
            }
        }
    }
}

if (is_array($_GET) && is_array($_POST) && count(array_intersect_key($_GET, $_POST))) {
    // JEFF指示可允許有相同的變數名稱，但不允許有不同的數值
    foreach (array_intersect_key($_GET, $_POST) as $k => $v) {
        if ($_GET[$k] !== $_POST[$k]) {
            die('Access Denied. Possible counterfeit variables( ' . htmlspecialchars($k, ENT_QUOTES) . ' )');
        }
    }
}

/* 若xxx.php之後的第1個參數是'/',則Forbidden(B) */
// echo 'SCRIPT_NAME:'.$_SERVER['SCRIPT_NAME'].'<br />';
// echo 'REQUEST_URI:'.$_SERVER['REQUEST_URI'].'<br />';exit;
if(preg_match('/\.php$/', $_SERVER['SCRIPT_NAME'])){
    // 從字首來比對是相同的
    if (substr($_SERVER['REQUEST_URI'],0,strlen($_SERVER['SCRIPT_NAME'])) == $_SERVER['SCRIPT_NAME']){
        if (substr($_SERVER['REQUEST_URI'],strlen($_SERVER['SCRIPT_NAME']),1) == '/'){
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
    }
}
/* 若xxx.php之後的第1個參數是'/',則Forbidden(E) */

$UserAttackLog = sysDocumentRoot.'/base/10001/door/WmProAttackLog_'.date('Ymd').'.log';

$hongu = new Hongu_Validate_Validator_XssAttack();

// 若POST的Body是json或xml，驗證方式不同
$postInputData = file_get_contents('php://input');
$postBodyType = 'KeyValue';
if (!empty($postInputData)){
    if (json_decode($postInputData)){
        $objPostInputData = json_decode($postInputData, true);
        detect_malicious_data($objPostInputData, true);
        unset($objPostInputData);
        $postBodyType = 'JSON';
    }else if(@domxml_open_mem($postInputData)) {
        $postBodyType = 'XML';
    }
}
unset($postInputData);


$exposeSqlInjectionRules = json_decode(file_get_contents(sysDocumentRoot."/lib/Expose/filter_sql_injection_rules.json"));
if (isset($_GET))            { escapeRequestParams($_GET); detect_malicious_data($_GET, true); reset($_GET); $safeGET=$_GET; }
if ($postBodyType == 'KeyValue'){
    if (isset($_POST))          { escapeRequestParams($_POST); detect_malicious_data($_POST, true); reset($_POST); $safePOST=$_POST; }
}
// if (isset($_REQUEST))        { escapeRequestParams($_REQUEST); detect_malicious_data($_REQUEST); reset($_REQUEST); $safeREQUEST=$_REQUEST; }
if (isset($_SERVER['argv'])) { escapeRequestParams($_SERVER['argv']); detect_malicious_data($_SERVER['argv']); reset($_SERVER['argv']); $safeARGV=$_SERVER['argv'];}
if (isset($_COOKIE))            { escapeRequestParams($_COOKIE); detect_malicious_data($_COOKIE); reset($_COOKIE); $safeCOOKIE=$_COOKIE; }
if (isset($_SERVER))            { escapeRequestParams($_SERVER); detect_malicious_data($_SERVER); reset($_SERVER); $safeSERVER=$_SERVER; }

//VIP#74834 Acunetix 10 弱掃發現，但 Acunetix 11卻沒有此項
//Reported by module Scripting (XSS_in_URI_File.script)
if (preg_match('/\.php\/%22/i', $_SERVER['REQUEST_URI'])){
    header('HTTP/1.1 403 Forbidden');
    exit;
}

//backup files
if (substr($_SERVER['REQUEST_URI'], -3) === "%23"){
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// 弱掃通過後，再來處理已被攻擊偵測所設定的阻擋ip
if ($sysConn) {
    detectAttackBlockedIP();
}
