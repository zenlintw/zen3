<?php
/**
 * 驗證規則：驗證字串必須為半形英數字
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_HalfAlnum /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證字串必須為半形英數字
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        return preg_match('/^[a-zA-Z0-9]+$/', $input) > 0;
    }
}
