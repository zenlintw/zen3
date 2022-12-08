<?php

//-------------------------------------------------------------------- 
//The HOMEMEETING MMC PROGRAMMING License, version 1.0
//Copyright (c) 2004 HOMEMEETING INC. All rights reserved.
//-------------------------------------------------------------------- 


//////////////////////////////////////////////////////////////////////////////
//
//  hit_settings.php
//
//  Contains encryption settings
//
//  Note: thie file is *NOT* required for generating JNJ file.  Usually
//        you will put this settings in a central configuration file.
//
//////////////////////////////////////////////////////////////////////////////

// Note: settings in this file will be modified if you run the VBscript
//       new_mcu_key.vbs or new_webap_key.vbs in helper directory

define('DEFAULT_KEY_DIR'    , $_SERVER['DOCUMENT_ROOT'] . '/webmeeting/key/');
define('DEFAULT_PUBLIC_KEY' , 'key_mcu_localhost.x509');
define('DEFAULT_PRIVATE_KEY', 'key_web_localhost');
define('DEFAULT_SITE_ID'    , 'key_web_localhost');
define('DEFAULT_PASS_PHRASE', base64_decode('cGFzc3BocmFzZQ'));
//define('DEFAULT_PASS_PHRASE',  'secret');
    
?>