<?php
/**
 * 驗證規則：驗證日期格式，有判斷閏年的能力，範例: (1999-01-31, 2008-02-29)
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_Date /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證日期範圍 (格式 = Y-m-d)
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        $pattern = '/((^((1[8-9]\d{2})|([2-9]\d{3}))([-])(10|12|0?[13578])([-])(3[01]|[12][0-9]|0?[1-9])$)|';
        $pattern .= '(^((1[8-9]\d{2})|([2-9]\d{3}))([-])(11|0?[469])([-])(30|[12][0-9]|0?[1-9])$)|(^((1[8-9]\d{2})|';
        $pattern .= '([2-9]\d{3}))([-])(0?2)([-])(2[0-8]|1[0-9]|0?[1-9])$)|(^([2468][048]00)([-])(0?2)([-])(29)$)|';
        $pattern .= '(^([3579][26]00)([-])(0?2)([-])(29)$)|(^([1][89][0][48])([-])(0?2)([-])(29)$)|';
        $pattern .= '(^([2-9][0-9][0][48])([-])(0?2)([-])(29)$)|(^([1][89][2468][048])([-])(0?2)([-])(29)$)|';
        $pattern .= '(^([2-9][0-9][2468][048])([-])(0?2)([-])(29)$)|(^([1][89][13579][26])([-])(0?2)([-])(29)$)|';
        $pattern .= '(^([2-9][0-9][13579][26])([-])(0?2)([-])(29)$))/';
        return preg_match($pattern, $input) > 0;
    }
}
