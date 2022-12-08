<?php
/**
 * 產生 QR Code
 * TODO:
 *     wm3 沒有 QR code lib
 *
 * return
 *      2: 圖片產生失敗
 *      3: 沒有教師(助教、講師)權限
 *      4: 課程編號錯誤
 *      5: 沒有給予編碼資料
 */

include_once(dirname(__FILE__) . '/action.class.php');
// 產生 QR code
require_once(sysDocumentRoot . '/lib/phpqrcode/qrlib.php');

class GetQrCodeAction extends baseAction
{
    var $_mysqlUsername = '',
        $_courseId = 0,
        $_types = array('default', 'google'),
        $_error_correction_level = array('L', 'M', 'Q', 'H');

    /**
     * 資料處理及驗證
     * @param $data
     * @return array 處理後的資料
     */
    function dataHandler($data) {
        // 驗證課程編號是否正確
        $cid = $data['course_id'];
        if (!isset($cid) || !($cid > 10000000 && $cid < 99999999)) {
            $this->returnHandler(4, 'fail', array('errMsg'=> 'course_id error!'), 403);
        } else {
            $this->_courseId = $cid;
        }

        // 驗證要編碼的資料
        if (!isset($data['encData']) || $data['encData'] === '') {
            $this->returnHandler(5, 'fail', array('errMsg'=> 'need data!'), 403);
        }

        // 設定預設值
        $data['type'] = in_array($data['type'], $this->_types) ? $data['type'] : 'default';
        $data['size'] = (isset($data['size']) && is_numeric($data['size'])) ? intval($data['size']) : 10;
        $data['margin'] = (isset($data['margin']) && is_numeric ($data['margin'])) ? intval($data['margin']) : 1;
        $data['ec_level'] = (isset($data['ec_level']) && in_array($data['ec_level'], $this->_error_correction_level)) ? trim($data['ec_level']) : 'L';

        return $data;
    }

    /**
     * 身分權限驗證
     * @param $username
     */
    function aclCheck($username, $courseId) {
        global $sysRoles;

        // 確認使用權限
        $aclCheck = aclCheckRole($username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $courseId);
        if (!$aclCheck) {
            $this->returnHandler(3, 'fail', array(), 403);
        }
    }

    function transLevel2QRCodeConstant($level) {
        // 常數在 /lib/phpqrcode/qrlib.php
        $constant = array(
            'L' => QR_ECLEVEL_L,
            'M' => QR_ECLEVEL_M,
            'Q' => QR_ECLEVEL_Q,
            'H' => QR_ECLEVEL_H
        );
        return $constant[$level];
    }

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        // 資料處理
        global $sysSession;
        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);
        $getData = $_GET;

        // 確認資料
        $getData = $this->dataHandler($getData);

        // 確認使用者權限
        $this->aclCheck($this->_mysqlUsername, $this->_courseId);

        // 取得 QR Code
        switch ($getData['type']) {
            case 'google':
                // for wm3 使用 google chart 產生 QR code
                $googleChartAPI = 'https://chart.googleapis.com/chart?cht=qr&choe=UTF-8&chs=%s&chl=%s&chld=%s';
                $googleCHS = sprintf('%dx%d', $getData['size'] * 21, $getData['size'] * 21);
                $googleCHLD = sprintf('%s|%d', $getData['ec_level'], $getData['margin']);
                $qrCodeImage = sprintf(
                    $googleChartAPI,
                    $googleCHS,             // 大小
                    urlencode($getData['encData']),    // 資料
                    $googleCHLD
                );
                break;
            default:
                ob_start();
                QRcode::png(
                    $getData['encData'],
                    null,
                    $this->transLevel2QRCodeConstant($getData['ec_level']),
                    $getData['size'],
                    $getData['margin']
                );
                $qrCodeImage = 'data:image/jpeg;base64,' . base64_encode( ob_get_contents() );
                ob_end_clean();
        }

        // 回傳資料
        if ($qrCodeImage) {
            $this->returnHandler(0, 'success', array(
                'qr_code' => $qrCodeImage
            ));
        } else {
            $this->returnHandler(2, 'fail', array('errMsg' => 'QR code generate error!'), 404);
        }

    }
}