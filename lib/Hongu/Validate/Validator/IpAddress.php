<?php
/**
 * 驗證規則：驗證 IP Address 以 IPv4 為驗證原則
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_IpAddress /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證Ip Address
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        $regex  = '/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])';
        $regex .= '(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/';
        return preg_match($regex, $input) > 0;
    }
}
