<?php
/**
 * 驗證規則：驗證文字必須半型英文小寫字元 (A-Z)
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_HalfLowerAlphabet /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證字串必須為半形英文小寫
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        return preg_match('/^[a-z]+$/', $input) > 0;
    }
}
