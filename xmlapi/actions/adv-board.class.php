<?php
/**
 * 取得廣告輪播清單
 *
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @category    xmlapi
 * @package     WM25
 * @subpackage  WebServices
 * @author      sj <sj@sun.net.tw>
 * @copyright   2012 SunNet Tech. INC.
 * @version     1.0
 * @since       2012-10-25
 */
include_once(dirname(__FILE__).'/action.class.php');

class AdvBoardAction extends baseAction
{
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        // 從資料庫取出活動圖片
        $rsActPic = dbGetStMr('CO_activities', 'picture', 'status="Y" order by permute asc');
        if ($rsActPic) {
            while (!$rsActPic->EOF) {
                $aryActPics[] = WM_SERVER_HOST . $rsActPic->fields['picture'];
                $rsActPic->MoveNext();
            }
        }
        
        // TODO 尚未實作
        // make json
        $jsonObj = array(
            'code' => 0,
            'message' => 'success',
            'data' => array(
                'ipad' => $aryActPics,
                'iphone' => array(),
            ),
        );

        $jsonEncode = JsonUtility::encode($jsonObj);
        
        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}