<?php
/**
 * 驗證規則：驗證阿拉伯數字範圍
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_IntRangeEqualerThan /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證數字範圍
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * <pre>
     *   $args['min'] = 最小值
     *   $args['max'] = 最大值
     * </pre>
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        $min = $args['min'];
        $max = $args['max'];
        return ($input >= $min && $input <= $max);
    }
}
