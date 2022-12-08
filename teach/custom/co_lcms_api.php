<?php

if (!function_exists('curl_setopt_array')) {
   function curl_setopt_array(&$ch, $curl_options)
   {
       foreach ($curl_options as $option => $value) {
           if (!curl_setopt($ch, $option, $value)) {
               return false;
           } 
       }
       return true;
   }
}
 
function lcms_api($api, $params = array(), $method = 'get') {
    if (strcmp($method, 'get') == 0) {
        if (count($params) > 0 && is_array($params)) {
            $api_params = array();
            foreach ($params as $k => $v) {
                $api_params[] = "$k=$v";
            }
            $api .= count($api_params) > 0 ? '?' . implode('&', $api_params) : '';
        } else if (!empty($params)) {
            $api .= '?' . $params;
        }
    }else{
        $data = "";
        while(list($k,$v)=each($params)){
          $data .= ($data?'&':'');
          $data .= rawurlencode($k)."=".rawurlencode($v);
        }
    }
    $url = sprintf('%s/%s', sysLcmsHost, $api);
    $content = coPostDataFromURL($url, $method, $data, $timeout = 28800);
    
    return array(
        'response' => json_decode($content, true),
        'raw' => $content,
    );
}


function getSupportFileType(){
    $rtn = array();
    $resData = lcms_api('api/filetype/import?size=999');
    if(is_array($resData['response']['data']['list']) && count($resData['response']['data']['list']) > 0){
        foreach($resData['response']['data']['list'] as $v) {
            $rtn[] = $v['extension'];
        }
    }
    return $rtn;
}

function mbPathinfo($filepath, $getType=null) {
    preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im',$filepath,$m);
    if($m[1]) $ret['dirname']=$m[1];
    if($m[2]) $ret['basename']=$m[2];
    if($m[5]) $ret['extension']=$m[5];
    if($m[3]) $ret['filename']=$m[3];
    $res = '';
    switch ($getType) {
        case PATHINFO_BASENAME:
            $res = $ret['basename'];
            break;
        case PATHINFO_DIRNAME:
            $res = $ret['dirname'];
            break;
        case PATHINFO_EXTENSION:
            $res = $ret['extension'];
            break;
        case PATHINFO_FILENAME:
            $res = $ret['filename'];
            break;
        default:
            $res = $ret;
    }
    return $res;
}

function coPostDataFromURL($url, $method='POST', $data = '', $timeout = 30) {
    $method = strtoupper($method);
    $urls = parse_url($url);
    if (!$urls) {
        return "-500";
    }
    $port = isset($urls['port']) ? $urls['port'] : null;
    if (!$port) {
        $port = "80";
    }
    $host = $urls['host'];
    $httpheader = "$method " . $url . " HTTP/1.0" . "\r\n" . "Accept:*/*" . "\r\n" . "Accept-Language:zh-cn" . "\r\n" . "Referer:" . $url . "\r\n" . "Content-Type:application/x-www-form-urlencoded" . "\r\n" . "User-Agent:Mozilla/4.0(compatible;MSIE 7.0;Windows NT 5.1)" . "\r\n" . "Host:" . $host . "\r\n" . "Content-Length:" . strlen($data) . "\r\n" . "\r\n" . $data;
    $fd = fsockopen($host, $port);
    if (!is_resource($fd)) {
        return "fsockopen failed";
    }
    fwrite($fd, $httpheader);
    stream_set_blocking($fd, TRUE);
    stream_set_timeout($fd, $timeout);
    $info = stream_get_meta_data($fd);
    $gets = "";
    $response='';
    while ((!feof($fd)) && (!$info['timed_out'])) {
        $response .= fgets($fd, 8192);
        $info = stream_get_meta_data($fd);
        @ob_flush();
        flush();
    }
    if ($info['timed_out']) {
        return "timeout";
    } else {
        $contentInfo = explode("\n\n", str_replace("\r", "", $response));
        
        if (!strstr($contentInfo[0], "HTTP/1.1 200 OK")) {
            return -10;
        }
        
        return trim($contentInfo[count($contentInfo)-1]);
    }
}

if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data) {
        $f = @fopen($filename, 'w');
        if (!$f) {
            return false;
        } else {
            $bytes = fwrite($f, $data);
            fclose($f);
            return $bytes;
        }
    }
}
?>