<?php
/**
 * 驗證規則：驗證文字的附檔名格式，會自動切割出附檔名來進行驗證，可用於上傳檔名的驗證
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_SubFileName /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證字串副檔名(不分大小寫)
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * <pre>
     *   $args = (
     *   	'jpg',
     *      'jpeg',
     *      'png',
     *   )
     * </pre>
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        // check args
        if (is_null($args) || !is_array($args) || count($args) === 0) {
            return false;
        }

        $position = strrpos($input, '.');
        if ($position > 0) {
            $subname = strtolower(substr($input, $position + 1));
            foreach ($args as $subName) {
                if ($subname === strtolower($subName)) {
                    return true;
                }
            }
        }
        return false;
    }
}
