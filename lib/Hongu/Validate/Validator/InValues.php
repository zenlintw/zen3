<?php
/**
 * 驗證規則：列舉驗證器，常用來驗證 Radio Input 是否被串改
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_InValues /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證字串必須為參數中的值 (列舉)
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * <pre>
     *   $args = (
     *   	'Enum_A',
     *      'Enum_B',
     *      'Enum_C',
     *   )
     * </pre>
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        return in_array(((string)$input), $args);;
    }
}
