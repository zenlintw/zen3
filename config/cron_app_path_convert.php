#!/usr/local/bin/php
123
<?php
require_once(dirname(__FILE__) . '/console_initialize.php');
include_once(sysDocumentRoot . '/xmlapi/config.php');
include_once(sysDocumentRoot . '/xmlapi/lib/JsonUtility.php');

function login ($username, $password) {
    $url = "http://192.168.10.155:5004/xmlapi/index.php?action=login&username={$username}&password={$password}";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:19.0) Gecko/20100101 Firefox/19.0");
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //curl error SSL certificate problem, verify that the CA cert is OK
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 600);

    $result = JsonUtility::decode(curl_exec($curl));
    if ($result['code'] === 0) {
        return $result['data']['session_data']['ticket'];
    } else {
        return '';
    }
}

$sids = $sysConn->GetCol(sprintf('SELECT DISTINCT `school_id` FROM %s.`WM_school` WHERE `school_host` NOT LIKE "[delete]%%"', sysDBname));
if (is_array($sids) && count($sids)) {
    $loginTicket = login('root', 'wm3.3mw');
    if ($loginTicket) {
        $curl = curl_init();
        $callURI = "http://192.168.10.155:5004/xmlapi/index.php?action=my-course-path-info&sid=%s&cid=%s&sunnetAPPSystemCron=1&ticket={$loginTicket}";
        foreach($sids as $schoolId) {
            $sysDBprefix = sysDBprefix;
            $sysConn->Execute("USE {$sysDBprefix}{$schoolId}");

            $courseSQL = "SELECT `course_id` FROM `WM_term_course` WHERE `status` BETWEEN 1 AND 5 LIMIT 0, 5000";
            $courseRS = $sysConn->Execute($courseSQL);
            if ($courseRS) {
                while ($course = $courseRS->FetchRow()) {
                    $courseId = $course['course_id'];
                    $url = sprintf($callURI, $schoolId, $courseId);
                    exec('echo ' . $courseId . ' >> /home/WM3_APP/config/cron_app_path_convert.html');

                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_HEADER, false);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
                    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:19.0) Gecko/20100101 Firefox/19.0");
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //curl error SSL certificate problem, verify that the CA cert is OK
                    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 600);
                    curl_exec($curl);
                }
            }
        }
        curl_close($curl);
    }
}
