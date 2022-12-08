<?php
/**
 * 驗證規則：驗證行動電話號碼格式，(例：0912345678)
 *
 * @author kuko
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_CellPhone /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證行動電話號碼格式
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        return preg_match('/^(\+|\d)[0-9\-]{7,16}$/', $input) > 0;
    }
}
