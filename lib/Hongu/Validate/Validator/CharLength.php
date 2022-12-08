<?php
/**
 * 驗證規則：文字長度驗證器，考慮語系編碼實際的長度
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_CharLength /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證文字長度
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]<br />
     * <pre>
     *   $args['min'] = 最小長度
     *   $args['max'] = 最大長度
     *   $args['encoding'] = 文字編碼 default UTF-8
     * </pre>
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        $min = $args['min'];
        $max = $args['max'];
        if (!isset($args['encoding'])) {
            $encoding = 'UTF-8';
        } else {
            $encoding = $args['encoding'];
        }
        $count = /*iconv_strlen*/mb_strlen($input, $encoding);
        return ($count >= $min && $count <= $max);
    }
}
