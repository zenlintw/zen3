<?php
/**
 * 取得首頁Logo
 */
include_once(dirname(__FILE__).'/action.class.php');

class GetBackgroundLogoAction extends baseAction
{
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        global $sysSession;

        $devicePermit = array('PHONE', 'TABLET');

        // 從網址取得參數
        $device = trim($_GET['device']);

        // 判斷是否是允許的裝置
        if (in_array($device, $devicePermit)) {
            // 目前logo圖片都一樣，未來有異動，就直接修改檔名即可
            if ($device === 'PHONE') {
                // iphone 圖片檔案路徑
                $pictureFile = getImageFilename('background-logo', $sysSession->school_id);
            } else if ($device === 'TABLET') {
                // ipad 圖片檔案路徑
                $pictureFile = getImageFilename('background-logo', $sysSession->school_id);
            }

            if (is_file($pictureFile)) {
                $img = 'data:' . getFileMimeType($pictureFile) . ';base64,' .
                       base64_encode(file_get_contents($pictureFile));
                $code = 0;
                $message = 'success';
            } else {
                $img = '';
                $code = 2;
                $message = 'fail';
            }
        } else {
            $img = '';
            $code = 3;
            $message = 'fail';
        }

        /**
         * code:
         *      0: 成功
         *      1: ticket error
         *      2: 找不到檔案
         *      3: 裝置不在允許清單內
         **/
        // make json
        $jsonObj = array(
            'code' => $code,
            'message' => $message,
            'data' => array(
                'img' => $img
            ),
        );

        $jsonEncode = JsonUtility::encode($jsonObj);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}