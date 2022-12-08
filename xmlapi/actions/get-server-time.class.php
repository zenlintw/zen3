<?php
/**
 * 取得Server的日期時間字串
 *
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @category    xmlapi
 * @package     WM25
 * @subpackage  WebServices
 * @author      Jeff Wang <jeff@sun.net.tw>
 * @copyright   2011 SunNet Tech. INC.
 * @version     1.0
 * @since       2012-01-03
 */
include_once(dirname(__FILE__).'/action.class.php');

class GetServerTimeAction extends baseAction
{
    function main()
    {
        // 透過php函式time()取時間
        $serverTime = date('Y-m-d H:i:s', time());

        // output
        header('Content-Type: application/json');
        echo sprintf('{"code":0,"message":"success","data":{"server_time":"%s"}}', $serverTime);
        exit();
    }
}