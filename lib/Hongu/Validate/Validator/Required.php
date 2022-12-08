<?php
/**
 * 驗證規則：必填驗證器，驗證輸入欄位比需填入資料
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_Required /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證必須不為空
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        return (isset($input) && strlen(chop($input)) > 0);
    }
}
