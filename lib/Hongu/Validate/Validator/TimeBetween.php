<?php
/**
 * 驗證規則：用來檢查輸入的時間區間是否在驗證範圍內
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_TimeBetween /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證時間範圍 (格式 = H:i:s | H:i)
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * <pre>
     *   $args['start'] = 開始時間
     *   $args['end'] = 結束時間
     * </pre>
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        //check agrs
        if (!isset($args['start']) || !isset($args['end'])) {
            return false;
        }

        // make vars
        $time = strtotime($input);
        $start = strtotime($args['start']);
        $end = strtotime($args['end']);
        return ($start <= $time && $time <= $end);
    }
}
