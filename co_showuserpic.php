<?php
    /**
     * 顯示圖形嵌入頁面
     *
     * 建立日期：2002/02/24
     * @author  ShenTing Lin
     * @version $Id: showpic.php,v 1.1 2010/02/24 02:39:10 saly Exp $
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/username.php');

    if ($_GET[a] == 'undefined' || $_GET[a] == '') {
        $username = 'undefined';
    } else {
        $username = base64_decode(urldecode($_GET[a]));    
    }
    
    $res = checkUsername($username);
    if (($res != 1) && ($res != 2) && ($res !=4) ) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }

    getUserPic($username);