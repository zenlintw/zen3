<?php
/**
 * 驗證規則：驗證陣列元素數量，常用在 CheckBox 驗證勾選數量
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_ArraySizeRange /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證陣列元素數量
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * <pre>
     *   $args['min'] = 最小數量
     *   $args['max'] = 最大數量
     * </pre>
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        $min = $args['min'];
        $max = $args['max'];
        return is_array($input) && count($input) >= $min && count($input) <= $max;
    }
}
