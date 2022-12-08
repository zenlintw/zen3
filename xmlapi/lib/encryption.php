<?php
class APPEncrypt {
    /**
     * 解密
     * @param String $data 欲解密的資料
     * @param Integer $aesCode
     * @return String 解密後的資料
     **/
    function decrypt($data, $aesCode) {
        $aesKey = APPEncrypt::makeAesKey($aesCode);
        if ($data === "") {
            return "";
        }
        return base64_decode(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $aesKey, base64_decode($data), MCRYPT_MODE_CBC, AES_APP_IV));
    }

    /**
     * 加密
     * @param Object $data 欲加密的資料
     * @param Integer $aesCode AES_APP_STRING要取到哪裡
     * @return String 加密後的資料
     **/
    function encrypt($data, $aesCode) {
        $aesKey = APPEncrypt::makeAesKey($aesCode);
        if ($data === "") {
            return "";
        }
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $aesKey, $data, MCRYPT_MODE_CBC, AES_APP_IV));
    }

    /**
     * 解密(進去跟出來都有base64_decode處理)
     * @param String $data 欲解密的資料
     * @param Integer $aesCode
     * @return String 解密後的資料
     **/
    function decryptNew($data, $aesCode) {
        $aesKey = APPEncrypt::makeAesKey($aesCode);
        if ($data === "") {
            return "";
        }
        return base64_decode(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $aesKey, base64_decode($data), MCRYPT_MODE_CBC, AES_APP_IV));
    }

    /**
     * 加密(進去跟出來都有base64_encode處理)
     * @param Object $data 欲加密的資料
     * @param Integer $aesCode AES_APP_STRING要取到哪裡
     * @return String 加密後的資料
     **/
    function encryptNew($data, $aesCode) {
        $aesKey = APPEncrypt::makeAesKey($aesCode);
        if ($data === "") {
            return "";
        }
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $aesKey, base64_encode($data), MCRYPT_MODE_CBC, AES_APP_IV));
    }

    /**
     * 在8 到 AES_APP_STRING的字串長度之間隨機取出一個數字
     *
     * @return Integer 數字
     **/
    function makeAesCode() {
        $aesStringLength = strlen(AES_APP_STRING);
        return rand(8, $aesStringLength);
    }

    /**
     * 透過$aesCode製作加/解密的key
     *
     * @param Integer $aesCode
     * @return String 加/解密的key
     **/
    function makeAesKey($aesCode) {
        $md5String = md5(substr(AES_APP_STRING, 0, $aesCode));
        return md5(substr($md5String, 0, 4) . substr($md5String, -4));
    }
}