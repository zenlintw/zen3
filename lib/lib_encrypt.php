<?php
    /**
     * 編碼或解碼
     *
     * @since   2004/10/15
     * @author  ShenTing Lin
     * @version $Id: lib_encrypt.php,v 1.1 2010/02/24 02:39:33 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
    // require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

    // 預設的編碼或解碼所需的 key
    $sysDefaultKey = $_COOKIE['idx'];
    
    define('ExKeysize', 'PortalEx');

    function base64_url_encode($input)
    {
        return strtr(base64_encode($input), '+/=', '-_,');
    }

    function base64_url_decode($input)
    {
        return base64_decode(strtr($input, '-_,', '+/='));
    }

    /**
     * 編碼，將所需的資料編碼
     * @param string $val : 所要編碼的資料
     * @param string $key : 編碼的 key，如果空白，則使用 global 中的 $default_key
     * @param Constants $chiper : 這個常數，請參考 Mcrypt Encryption Functions (http://www.php.net/manual/en/ref.mcrypt.php) 中的 Mcrypt ciphers 段落
     * @return string $res : 編碼後的資料
     **/
    function sysEncode($val, $key='', $cipher=MCRYPT_RIJNDAEL_256)
    {
        global $sysDefaultKey;
        if (empty($key)) $key = $sysDefaultKey;

        srand((double) microtime() * 1000000); //for sake of MCRYPT_RAND
        $iv      = @mcrypt_create_iv(mcrypt_get_iv_size($cipher, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $encrypt = @mcrypt_encrypt($cipher, $key, $val, MCRYPT_MODE_ECB, $iv);
        // $encrypt = @mcrypt_encrypt(MCRYPT_DES, $key, $val, 'ecb');
        $encode  = base64_encode($encrypt);
        return $encode;
    }

    /**
     * 解碼，將所需的資料解碼
     * @param string $val : 所要解碼的資料
     * @param string $key : 解碼的 key，如果空白，則使用 global 中的 $default_key
     * @param Constants $chiper : 這個常數，請參考 Mcrypt Encryption Functions (http://www.php.net/manual/en/ref.mcrypt.php) 中的 Mcrypt ciphers 段落
     * @return string $res : 解碼後的資料
     **/
    function sysDecode($val, $key='', $cipher=MCRYPT_RIJNDAEL_256)
    {
        global $sysDefaultKey;
        if (empty($key)) $key = $sysDefaultKey;

        $decode  = base64_decode(trim($val));
        $iv      = @mcrypt_create_iv(mcrypt_get_iv_size($cipher, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $decrypt = @mcrypt_decrypt($cipher, $key, $decode, MCRYPT_MODE_ECB, $iv);
        // $res = trim(@mcrypt_decrypt(MCRYPT_DES, $key, $decode, 'ecb'));
        $res = trim($decrypt);
        return $res;
    }

    /**
     * 編碼，將所需的資料編碼
     * @param string $val : 所要編碼的資料
     * @param string $key : 編碼的 key，如果空白，則使用 global 中的 $default_key
     * @param bool   $isForUrl : 是否放在 URL 來傳遞
     * @param string $cipher   : 這個常數，請參考 Mcrypt Encryption Functions (http://www.php.net/manual/en/ref.mcrypt.php) 中的 Mcrypt ciphers 段落
     * @param string $mode     :
     * @return string $res : 編碼後的資料
     **/
    function sysNewEncode($val, $key='', $isForUrl=true, $cipher=MCRYPT_DES, $mode='ecb')
    {
        global $sysDefaultKey;
        if (empty($key)) $key = $sysDefaultKey;

        $td      = mcrypt_module_open($cipher, '', $mode, '');
        $key     = substr($key, 0, mcrypt_enc_get_key_size($td));
        $iv_size = mcrypt_enc_get_iv_size($td);
        $iv      = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        if (mcrypt_generic_init($td, $key, $iv) != -1) {
            /* Encrypt data */
            if($val==''){
                return false;
            }else{
                $c_t = mcrypt_generic($td, $val);
            }
            /* Clean up */
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);

            if ($isForUrl) {
                return base64_url_encode($c_t);
            } else {
                return base64_encode($c_t);
            }
        }

        return false;
    }

    /**
     * 解碼，將所需的資料解碼
     * @param string $val : 所要解碼的資料
     * @param string $key : 解碼的 key，如果空白，則使用 global 中的 $default_key
     * @param bool   $isForUrl : 是否放在 URL 來傳遞
     * @param string $cipher   : 這個常數，請參考 Mcrypt Encryption Functions (http://www.php.net/manual/en/ref.mcrypt.php) 中的 Mcrypt ciphers 段落
     * @param string $mode     :
     * @return string $res : 解碼後的資料
     **/
    function sysNewDecode($val, $key='', $isForUrl=true, $cipher=MCRYPT_DES, $mode='ecb')
    {
        global $sysDefaultKey;
        if (empty($key)) $key = $sysDefaultKey;

        $td      = mcrypt_module_open($cipher, '', $mode, '');
        $key     = substr($key, 0, mcrypt_enc_get_key_size($td));
        $iv_size = mcrypt_enc_get_iv_size($td);
        $iv      = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        if (mcrypt_generic_init($td, $key, $iv) != -1) {
            if ($isForUrl) {
                $c_t = base64_url_decode($val);
            } else {
                $c_t = base64_decode($val);
            }

            if ($c_t === '' || $c_t === false) {
                return '';
            }

            /* Decrypt data */
            $p_t = trim(mdecrypt_generic($td, $c_t));

            /* Clean up */
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            return $p_t;
        }

        return false;
    }

    //編碼
    function other_enc($data, $key='')
    {
        if (empty($key))
        {
            $crypttext = @mcrypt_encrypt(MCRYPT_DES, ExKeysize, $data, 'ecb');
            $hextext   = bin2hex($crypttext);
        }
        else
        {
            if (strlen($key) > 8) $key = substr($key, 0, 8);
            $crypttext = @mcrypt_encrypt(MCRYPT_DES, $key, $data, 'ecb');
            $hextext   = base64_encode($crypttext);
        }

        return $hextext;
    }

    //解碼
    function other_dec($data, $key='')
    {
        if (empty($key))
        {
            $dectext = @mcrypt_decrypt (MCRYPT_DES, ExKeysize, hex2bin($data), MCRYPT_MODE_ECB);
        }
        else
        {
            $iv_size = mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB);
            $iv      = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            if (strlen($key) > 8) $key = substr($key, 0, 8);
            $dectext = mcrypt_decrypt(MCRYPT_DES, $key, base64_decode($data), MCRYPT_MODE_ECB, $iv);
        }
        $dectext = chop($dectext);

        return $dectext;
    }

    //16進位轉成2進位
    if (!function_exists('hex2bin')) {
        function hex2bin($data) {
            $len = strlen($data);
            return pack('H' . $len, $data);
        }
    }

    //2進位轉成16進位
    function bin_hex($data)
    {
        $hextext = bin2hex($data);
        return $hextext;
    }
    
    /*
     * mcrypt has been DEPRECATED as of PHP 7.1.0
     * This function for LCMS
     */
    function opensslEncrypt($data, $key = '', $isForUrl = true)
    {
        $salt      = 'SUNNETcH!swe!retReGu7W6bEDRup7usuDUh9THeD2CHeGE*ewr4n39=E@rAsp7c-Ph@pH';
        $iv_size   = openssl_cipher_iv_length("AES-256-CBC-HMAC-SHA256");
        $hash      = hash('sha256', $salt . $key . $salt);
        $iv        = substr($hash, strlen($hash) - $iv_size);
        $key       = substr($hash, 0, 32);
        
        $encrypted = openssl_encrypt($data, "AES-256-CBC-HMAC-SHA256", $key, OPENSSL_RAW_DATA, $iv);

        if ($isForUrl) {
            return base64_url_encode($encrypted);
        } else {
            return base64_encode($encrypted);
        }
    }

    /*
     * mcrypt has been DEPRECATED as of PHP 7.1.0
     * This function for LCMS
     */    
    function opensslDecrypt($data, $key = '', $isForUrl = true)
    {
        $salt      = 'SUNNETcH!swe!retReGu7W6bEDRup7usuDUh9THeD2CHeGE*ewr4n39=E@rAsp7c-Ph@pH';
        $iv_size   = openssl_cipher_iv_length("AES-256-CBC-HMAC-SHA256");
        $hash      = hash('sha256', $salt . $key . $salt);
        $iv        = substr($hash, strlen($hash) - $iv_size);
        $key       = substr($hash, 0, 32);
        
        if ($isForUrl) {
            $c_t = base64_url_decode($data);
        } else {
            $c_t = base64_decode($data);
        }
            
        $decrypted = openssl_decrypt($c_t, "AES-256-CBC-HMAC-SHA256", $key, OPENSSL_RAW_DATA, $iv);
        $decrypted = rtrim($decrypted, "\0");

        return $decrypted;
    }
