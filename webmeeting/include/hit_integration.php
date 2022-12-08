<?php

function get_online_meeting($server, $port, $path, $l_rid)
{
    $str = '';
    $fp  = @fsockopen($server, $port, $errno, $errstr, 90);
    if (!$fp) {
        die("$errstr ($errno)<br>\n");
    } else {
        fputs($fp, "GET {$path}mmc_online_meeting.php?rid=" . $l_rid . " HTTP/1.0\r\nHost: sun.net.tw\r\n\r\n");
        while (!feof($fp)) {
            $buffer = fgets($fp, 128);
            $str .= $buffer;
        }
    }
    fclose($fp);
    $arr = explode("\r\n\r\n", $str);
    $rtn = trim($arr[1]);
    return $rtn;
}

function get_record_list($server, $port, $path, $l_rid)
{
    $str = '';
    $fp  = @fsockopen($server, $port, $errno, $errstr, 90);
    if (!$fp) {
        die("$errstr ($errno)<br>\n");
    } else {
        fputs($fp, "GET {$path}mmc_recording_list.php?ownerId=" . $l_rid . " HTTP/1.0\r\nHost: sun.net.tw\r\n\r\n");
        while (!feof($fp)) {
            $buffer = fgets($fp, 128);
            $str .= $buffer;
        }
    }
    fclose($fp);
    $arr = explode("\r\n\r\n", $str);
    $rtn = trim($arr[1]);
    return $rtn;
}

function get_online_meeting_list($server, $port, $path)
{
    $str = '';
    $fp  = @fsockopen($server, $port, $errno, $errstr, 90);
    if (!$fp) {
        die("$errstr ($errno)<br>\n");
    } else {
        fputs($fp, "GET {$path}mmc_online_meeting_list.php HTTP/1.0\r\nHost: sun.net.tw\r\n\r\n");
        while (!feof($fp)) {
            $buffer = fgets($fp, 128);
            $str .= $buffer;
        }
    }
    fclose($fp);
    $arr = explode("\r\n\r\n", $str);
    $rtn = trim($arr[1]);
    return $rtn;
}

function setMeetingInfo($owner, $mID, $mName)
{
    global $Conn;
    $sqls = "insert into CO_chatroom (ownerID, meetingID, meetingName, CreateDateTime) values ('{$owner}','{$mID}','{$mName}',NOW())";
    mysql_query($sqls, $Conn);
}

function getMeetingName($owner, $mID)
{
    global $Conn;
    //todo 
    return $mID;
    $rtns = '';
    $sqls = "select meetingName from CO_chatroom where ownerID='{$owner}' and meetingID='{$mID}'";
    $rs   = mysql_query($sqls, $Conn);
    if ($rs) {
        list($rtns) = mysql_fetch_row($rs);
    }
    return $rtns;
}