<?php
/**
 * 驗證規則：XSS 驗證器，用來檢查 XSS 攻擊字串
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_XssAttack /*implements Hongu_Validate_Interface*/
{
    /*private static*/var $_attackStringPreg = array(
        '/<script\s*[^>]*>.*<\/script[^>]*>/isU',
        '/<script\s*[^>]*>/isU',
        '/%3Cscript\s*%3E/isU',
        '/<frameset\s[^>]*>.*<\/frameset[^>]*>/isU',
        '/<frame\s[^>]*>.*<\/frame[^>]*>/isU',
        '/<iframe\s(?!.*youtube.com)[^>]*>.*<\/iframe>/isU',
        '/^.*<body\s*[^>]*>/isU',
        '/<\/body>.*$/isU',
        '/<object\s*[^>]*>.*<\/object[^>]*>/isU',
        '/<applet\s*[^>]*>.*<\/applet[^>]*>/isU',
//        '/<form\s*[^>]*>.*<\/form[^>]*>/isU',  //由於隨機出題的xml中有form, 先備註此行，允許form
        '/<link\s*[^>]*>/isU',
        '/<[^<]+\son\w+\s*=\s*(".*[^\\]"|\'.*[^\\]\'|[^\s]*|\w+)[^>]*>+/isU',
        '/\s*onmouse\w+\s*=/isU',
        '/\w+\s*=\s*["\']?\s*(javascript|vbscript|mocha|livescript):[^\'">]*["\']?/isU',
        '/:\s*expression\s*\(/isU',
        '/&apos;/isU'
//        '/<input\s*[^>]*>/isU',
//        '/<textarea\s*[^>]*>.*<\/textarea[^>]*>/isU',
//        '/<select\s*[^>]*>.*<\/select[^>]*>/isU',
//        '/<!--.*-->/sU',
    );

    /**
     * 驗證 XSS 攻擊字串
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        $input = preg_replace('/<\?(php)?(\s*|=).*\?>/isU', '', $input);
        foreach (/*self::*/$this->_attackStringPreg as $pregString) {
            if (preg_match($pregString, $input) > 0) {
                return false;
            }
        }
        return true;
    }
}
