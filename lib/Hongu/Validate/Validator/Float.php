<?php
/**
 * 驗證規則：驗證字串為小數，可指定小數點位數
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_Float /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證字串為小數
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]<br />
     * <pre>
     *   $args['range'] = 小數點最高位數
     * </pre>
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        return preg_match("/^\d$|^\d+\.?\d{1,{$args['range']}}+$/", $input) > 0;
    }
}
