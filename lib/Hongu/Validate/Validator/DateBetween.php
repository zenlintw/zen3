<?php
/**
 * 驗證規則：驗證日期範圍 （不包含時間，時間請用 Hongu_Validate_Validator_TimeBetween）
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 * @see Hongu_Validate_Validator_TimeBetween
 */
class Hongu_Validate_Validator_DateBetween /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證日期範圍 (格式 = Y-m-d)
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * <pre>
     *   $args['start'] = 開始日期
     *   $args['end'] = 結束日期
     * </pre>
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        //check agrs
        if (!isset($args['start']) || !isset($args['end'])) {
            return false;
        }

        $time = strtotime($input);
        $start = strtotime($args['start']);
        $end = strtotime($args['end']);
        return ($start <= $time && $time <= $end);
    }
}
