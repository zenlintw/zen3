<?php
/**
 * 資料暫存機制
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
 * @version     CVS: $Id: save_temporary.server.php,v 1.1 2010/02/24 02:39:34 saly Exp $
 * @link        http://demo.learn.com.tw/1000110138/index.html
 * @since       2006-07-06
 */

ignore_user_abort(true);
ob_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
while (@ob_end_clean());
require_once(sysDocumentRoot . '/lib/xajax/xajax.inc.php');

$xajax_save_temp = new xajax('/lib/save_temporary.server.php');
$xajax_save_temp->registerFunction('save_temp');
$xajax_save_temp->registerFunction('check_temp');
$xajax_save_temp->registerFunction('clean_temp');
$xajax_save_temp->registerFunction('restore_temp');

$MSG['has temporary storing data'] = array(
    'Big5' => '您有上一次未完成的暫存資料，要使用它嗎？',
    'GB2312' => '您有上一次未完成的暂存资料，要使用它吗？',
    'en' => 'There is a imcomplete temporary data. Do you want to use it ?',
    'EUC-JP' => 'There is a imcomplete temporary data. Do you want to use it ?',
    'user_define' => 'There is a imcomplete temporary data. Do you want to use it ?'
);

/**
 * 資料暫存
 *
 * @param   int     $function_id    功能編號
 * @return  int    					0=失敗；1=update；2=insert
 */
function save_temp($function_id, $content)
{
    global $sysConn, $sysSession;
    
    chkSchoolId('WM_save_temporary');
    $sysConn->Replace('WM_save_temporary', array(
        'function_id' => $function_id,
        'username' => $sysSession->username,
        'save_time' => date('Y-m-d H:i:s'),
        'content' => $content
    ), array(
        'function_id',
        'username'
    ), true);
    $objResponse = new xajaxResponse();
    $objResponse->addScript('window.status="auto store temporary data";');
    return $objResponse;
}

/**
 * 檢查是否有暫存的資料
 *
 * @param   int     $function_id    功能編號
 * @return  int                     0=沒有；1=有
 */
function check_temp($function_id, $element_id)
{
    global $sysConn, $sysSession, $MSG;
    
    chkSchoolId('WM_save_temporary');
    $content     = $sysConn->GetOne('select content from WM_save_temporary where function_id="' . $function_id . '" and username="' . $sysSession->username . '"');
    $objResponse = new xajaxResponse();
    if (!empty($content)) {
        if (strpos($element_id, 'FCK.') === 0) {
            $objResponse->addConfirmCommands(2, $MSG['has temporary storing data'][$sysSession->lang]);
            $objResponse->addAssign('saveTemporaryContent', 'value', $content);
            $objResponse->addScript(substr($element_id, 4) . '.setHTML(document.getElementById("saveTemporaryContent").value);');
            return $objResponse->getXML();
        } else {
            $objResponse->addConfirmCommands(1, $MSG['has temporary storing data'][$sysSession->lang]);
            $objResponse->addAssign($element_id, 'innerHTML', $content);
        }
    } else
        $objResponse->addScript('window.status="empty temporary data";');
    return $objResponse;
}

/**
 * 清除暫存資料
 */
function clean_temp($function_id)
{
    global $sysConn, $sysSession;
    
    chkSchoolId('WM_save_temporary');
    $sysConn->Execute('delete from WM_save_temporary where function_id="' . $function_id . '" and username="' . $sysSession->username . '"');
    $objResponse = new xajaxResponse();
    return $objResponse;
}

/**
 * 復原暫存資料
 *
 * @param   int     $function_id    功能編號
 * @return  bool    				true=完成；false=失敗
 */
function restore_temp($function_id, $element_id)
{
    global $sysConn, $sysSession;
    
    chkSchoolId('WM_save_temporary');
    $content     = $sysConn->GetOne('select content from WM_save_temporary where function_id="' . $function_id . '" and username="' . $sysSession->username . '"');
    $objResponse = new xajaxResponse();
    $objResponse->addAssign($element_id, 'innerHTML', $content);
    return $objResponse;
}

$xajax_save_temp->processRequests();