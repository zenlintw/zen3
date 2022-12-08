<?php
/**
 * 驗證規則：驗證Email輸入格式(英數小寫)
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_Email /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證Email輸入格式(小寫)
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        return preg_match('/^[_.0-9a-z-]+@([0-9a-z][0-9a-z-]+.)+[a-z]{2,3}$/', $input) > 0;
    }
}
