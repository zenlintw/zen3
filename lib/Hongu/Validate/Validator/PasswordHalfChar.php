<?php
/**
 * 驗證規則：密碼驗證器，驗證密碼格式，以半型可視字元作為驗證條件（鍵盤可以打出來，而且看的到的字元）
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_PasswordHalfChar /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證字串必須為半形可視符號字元 (用在密碼驗證)
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        return preg_match('/^[a-zA-Z0-9`~!@#$%^&*\(\)_\+-=\{\}\[\]|\\\\;\':",\.\/<>\?]+$/', $input) > 0;
    }
}
