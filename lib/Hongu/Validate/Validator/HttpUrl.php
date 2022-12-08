<?php
/**
 * 驗證規則：驗證文字必須為 HTTP URL，支援 HTTP, HTTPS 開頭
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_HttpUrl /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證文字是否符合 HTTP URL 格式
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [optional]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        return preg_match('/(https?:\/\/[\w-\.]+(:\d+)?(\/[~\w\/\.]*)?(\?\S*)?(#\S*)?)/', $input) > 0;
    }
}
