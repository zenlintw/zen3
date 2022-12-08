<?php
/**
 * 驗證規則：提供開發人員透過自定的正規表達式來驗證文字
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_Regular /*implements Hongu_Validate_Interface*/
{
    /**
     * 使用指定正規表達式來驗證
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]<br />
     * <pre>
     *   $args['pattern'] = 正規表達式 (Pear Style)
     * </pre>
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        $pattern = $args['pattern'];
        return preg_match($pattern, $input) > 0;
    }
}
