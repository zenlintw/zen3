<?php
/*
 * PHP QR Code encoder
 *
 * Exemplatory usage
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

    //set it to writable location, a place for temp generated PNG files
    $PNG_TEMP_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;

    //html PNG location prefix
    $PNG_WEB_DIR = 'temp/';

    include "qrlib.php";
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/lib_encrypt.php');

    // 避免外連，進行判斷
//    echo '<pre>';
//    var_dump($_REQUEST);
//    var_dump($_REQUEST['data']);
//    echo '</pre>';
    $url = $_REQUEST['data'];

    if ($_REQUEST['des'] === '1') {
//        echo '<pre>';
//        var_dump(sysTicketSeed, $_COOKIE['idx']);
//        var_dump(md5(sysTicketSeed . $_COOKIE['idx']));
//        echo '</pre>';

        $key = md5(sysTicketSeed . $_COOKIE['idx']);
        $path = sysNewDecode($url, $key, false);
//        echo '<pre>';
//        var_dump($path);
//        var_dump(strpos($path, '|qrcode') >= 1);
//        echo '</pre>';
        if (strpos($path, '|qrcode') >= 1) {
            $_REQUEST['data'] = str_replace('|qrcode', '', $path);
//            echo '<pre>';
//            var_dump($_REQUEST['data']);
//            echo '</pre>';
        } else {
            die('Access deny (decoding error).');
        }
    } else {
//        echo '<pre>';
        $parseurl = parse_url(rawurldecode($url));
        $pattern = $parseurl['host'];
//        var_dump($parseurl['host']);
//        var_dump($_SERVER['HTTP_HOST']);
//        var_dump($url);
//        var_dump(strpos($url, $_SERVER['HTTP_HOST']) >= 1);
//        echo '</pre>';
        if ($pattern === $_SERVER['HTTP_HOST']) {
            $_REQUEST['data'] = $url;
        } else {
            die('Access deny (domain error).');
        }

    }

    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR)) {
        mkdir($PNG_TEMP_DIR);
    }

    // 清除一天前的qrcode圖片
    if (is_dir($PNG_TEMP_DIR)) {
        if ($dh = opendir($PNG_TEMP_DIR)) {
            // 刪除1小時前的qrcode圖片
            $expireTime = time()-3600;
            while (($eachQrcodeFile = readdir($dh)) !== false) {
                if (($eachQrcodeFile=='.')||($eachQrcodeFile=='..')) continue;
                $eachQrcodeFilepath = $PNG_TEMP_DIR . '/' . $eachQrcodeFile;
                $ext = pathinfo($eachQrcodeFilepath, PATHINFO_EXTENSION);
                if ($ext != 'png') continue;
                if ($expireTime > filemtime($eachQrcodeFilepath)){
                    @unlink($eachQrcodeFilepath);
                }
            }
            closedir($dh);
        }
    }

    $filename = $PNG_TEMP_DIR . 'cid.png';

    //processing form input
    //remember to sanitize user input in real-life solution !!!
    $errorCorrectionLevel = 'L';
    if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L', 'M', 'Q', 'H'))) {
        $errorCorrectionLevel = $_REQUEST['level'];
    }

    $matrixPointSize = 4;
    if (isset($_REQUEST['size'])) {
        $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 20);
    }

    if (isset($_REQUEST['data'])) {
        //it's very important!
        if (trim($_REQUEST['data']) == '')
            die('data cannot be empty! <a href="?">back</a>');

        // user data
        $filename = $PNG_TEMP_DIR . 'cid_' . md5($_REQUEST['data'] . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
        if (file_exists($filename) === false) {
            QRcode::png($_REQUEST['data'], $filename, $errorCorrectionLevel, $matrixPointSize, 2);
        }

    } else {
        //default data
        echo 'You can provide data in GET parameter: <a href="?data=like_that">like that</a><hr/>';
        QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    }
    if (isset($_REQUEST['width'])) {
        $width = 'width="'.intval($_REQUEST['width']).'"';
    }
    
    if (isset($_REQUEST['height'])) {
        $height = 'height="'.intval($_REQUEST['height']).'"';
    }

    //display generated file
    if (intval($_REQUEST['size']) == 5) {
        echo '<img style="width:165px" src="'.$PNG_WEB_DIR . basename($filename).'" />';
    }else{
        echo '<img '.$width.' '.$height.' src="'.$PNG_WEB_DIR . basename($filename).'" />';
    }
    