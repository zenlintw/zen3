<?php
/**
 * 驗證規則：驗證必須為阿拉伯數字型態（正整數）
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_HalfNumber /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證字串為半形數字 0~9 組成
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        return preg_match('/^[0-9]+$/', $input) > 0;
    }
}
