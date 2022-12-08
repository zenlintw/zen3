<?php
/**
 * 驗證規則：驗證電話號碼格式，許允使用「0~9, -, +, #」這些字元
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_Telephone /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證電話號碼格式
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        return preg_match('/^[0-9\-\+#]+$/', $input) > 0;
    }
}
