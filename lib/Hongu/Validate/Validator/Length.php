<?php
/**
 * 驗證規則：文字長度驗證器，以 byte 為單位，不考慮語系編碼關連
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_Length /*implements Hongu_Validate_Interface*/
{
    /**
     * 使用指定編碼驗證文字長度
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]<br />
     * <pre>
     *   $args['min'] = 最小長度
     *   $args['max'] = 最大長度
     * </pre>
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        $min = $args['min'];
        $max = $args['max'];
        $count = strlen($input);
        return ($count >= $min && $count <= $max);
    }
}
