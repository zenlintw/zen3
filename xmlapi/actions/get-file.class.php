<?php
/**
 * 取得檔案
 */
include_once(dirname(__FILE__).'/action.class.php');

class GetFileAction extends baseAction
{
    var $_whoGetFile = array('GoOgLe', 'WmPrO');

    function main()
    {
//        $_REQUEST['token'] = encryptImmediately('GoOgLe' . '#SUNNET#' . time());
//        $_REQUEST['fp'] = encryptImmediately('/base/10001/course/10000001/content/1512537603874-1.png');
        // 驗證 Token
        $whoGetFile = parent::checkToken();

        if (!in_array($whoGetFile, $this->_whoGetFile)) {
            $this->returnHandler(2, 'fail', array('errorMsg' => 'Illegal Access'));
        }
        // 取得POST Data > AES 解密 > JSON Decode
        $filePath = decryptImmediately(trim($_REQUEST['fp']));
        if (substr($filePath, 0, 6) !== '/base/' && strstr($filePath, '/../')) {
            // 不是/base/開頭，或是有/../的路徑，都視為不合法
            $this->returnHandler(3, 'fail', array('errorMsg' => 'Illegal File Path'));
        }
        $file = sysDocumentRoot . $filePath;
        if (!file_exists($file)) {
            $this->returnHandler(4, 'fail', array('errorMsg' => 'File Not found'));
        }

        if ($whoGetFile === 'GoOgLe') {
            // 製作google表單取檔，只能取圖檔的base64或實體檔案網址
            $fileContent = base64_encode(file_get_contents($file));
            echo $fileContent;
        } else {
            // 離線模組取平台教材檔案
            set_time_limit(0);
            ignore_user_abort(false);
            ini_set('output_buffering', 0);
            ini_set('zlib.output_compression', 0);

            $chunk = 1 * 1024 * 1024; // bytes per chunk (10 MB)

            $handle = fopen($file, "rb");

            if ($handle === false) {
                $this->returnHandler(4, 'fail', array('errorMsg' => 'Unable open file.'));
            }

            header('Content-Description: File Transfer');
            header('Content-Type: ' . getFileMimeType($file));
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));

            header('Content-Transfer-Encoding: binary');
            header('Content-Type: application/octet-stream;');

            // Repeat reading until EOF
            while (!feof($handle)) {
                echo fread($handle, $chunk);

                ob_flush();  // flush output
                flush();
            }
        }
    }
}