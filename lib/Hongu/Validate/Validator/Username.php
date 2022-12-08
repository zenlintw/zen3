<?php
/**
 * 驗證規則：驗證帳號字串，必須為「半形英文大小寫」或者「半形英文大小寫與數字」的組合
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_Username /*implements Hongu_Validate_Interface*/
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
        // 只有數字就驗證失敗
        if (preg_match('/^[0-9]+$/', $input) > 0) {
            return false;
        }
        // 驗證半形英文大小寫與數字
        return preg_match('/^[a-zA-Z0-9]+$/', $input) > 0;
    }
}
