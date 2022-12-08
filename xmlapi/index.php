<?php
define('XMLAPI', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot .'/lib/acl_api.php');
require_once(sysDocumentRoot .'/lang/app_server_push.php');
require_once(sysDocumentRoot . '/xmlapi/config.php');
require_once(sysDocumentRoot . '/xmlapi/initialize.php');
require_once(sysDocumentRoot . '/xmlapi/lib/common.php');
require_once(sysDocumentRoot . '/xmlapi/lib/common-qti.php');
require_once(sysDocumentRoot . '/xmlapi/lib/JsonUtility.php');
require_once(sysDocumentRoot . '/xmlapi/lib/encryption.php');
require_once(sysDocumentRoot . '/xmlapi/lib/rollcall.php');
require_once(sysDocumentRoot . '/xmlapi/lib/log.php');
require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/DatabaseHandler.php');

if (PHP_VERSION >= '5') {
    include_once(sysDocumentRoot . '/lib/domxml-php4-to-php5.php');
}

//== main ======

if (isset($_GET['action']) && !empty($_GET['action']))
{
    //載入Action Handler程式
    $actionName = strtolower($_GET['action']);
    $actionFileName = $actionName.'.class.php';
    $actionFilePath = dirname(__FILE__) . '/actions/' . $actionFileName;
    if (!is_readable($actionFilePath)){
        die('illegeal action');
    }
    
    $className = '';
    $parts = explode('-',$actionName);
    for($i=0; $i<count($parts); $i++){
        $className .= strtoupper(substr($parts[$i],0,1)).strtolower(substr($parts[$i],1));
    }
    $className .= 'Action';
    
    include_once($actionFilePath);
    if (!class_exists($className)){
        die('error className:'.$className);
    }
    
    $oAction = new $className();
    $oAction->main();
}
