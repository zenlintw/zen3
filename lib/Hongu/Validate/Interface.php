<?php
/**
 * 驗證器 Interface，實作這個 Interface 來擴充新的驗證規則
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 */
/*interface*/class Hongu_Validate_Interface
{
    /**
     * 驗證字串格式
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null) {
    }/*;*/
//    /**
//     * 處理 Array 資料
//     *
//     * @return boolean 允許或不允許
//     */
//    public static function isProcessArray();
}
