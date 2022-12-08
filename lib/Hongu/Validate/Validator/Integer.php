<?php
/**
 * 驗證規則：驗證必須為阿拉伯數字型態（整數/負數）
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_Integer /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證字串必須為整數 (包含負數)
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        return preg_match('/^-?[0-9]+$/', $input) > 0;
    }
}
