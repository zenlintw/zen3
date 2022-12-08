<?php
/**
 * Class EmojiHandler
 *  處理舊版 mysql 無法存入 4 字符的 utf8
 */

class EmojiHandler
{

    /**
     * 將文字內 4 字符的字作 json_encode
     * @param $text
     * @return string
     */
    function mb4ToUnicode($text) {
        $output = array();
        $charAry = preg_split('//u', $text);
//        $textAry = preg_split('/(?<!^)(?!$)/u', $text);

        foreach($charAry AS $char) {
            if (mb_strlen($char) > 3) {
                $output[] = json_encode($char);
            } else {
                $output[] = $char;
            }
        }

        return implode($output);
    }

    /**
     * 將字串內有 json_encode 的文字，轉回原本的字
     * @param $text
     * @return mixed
     */
    function unicodeToMb4($text) {
        return preg_replace_callback(
            '/(\"(\\\u[a-z0-9]{4})+\")/i',
            function($str) {
                return json_decode($str[0]);
            },
            $text
        );
    }
}
