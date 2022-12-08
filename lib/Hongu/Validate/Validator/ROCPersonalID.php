<?php
/**
 * 驗證規則：驗證中華民國身分證字號
 *
 * @author kuko
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Interface
 */
class Hongu_Validate_Validator_ROCPersonalID /*implements Hongu_Validate_Interface*/
{
    /**
     * 驗證身份證字號
     * http://twpug.net/modules/xfsnippet/
     *
     * @param string $input 驗證字串
     * @param array $args 驗證參數 [true or false]
     * @return boolean 通過或未通過驗證
     */
    /*public static*/ function validate($input, /*array*/ $args=null)
    {
        $flag = false;
        // 將英文字母全部轉成大寫
        $input = strtoupper($input);

        // 檢查字元長度
        if (strlen($input) !== 10) {
            return false;
        }

        //檢 查 第一個字母是否為英文字
        $idSub1 = ord(substr($input, 0, 1));
        if ($idSub1 > 90 || $idSub1 < 65) {
            return false;
        }

        //檢 查 身份證字號的 第二個字元 男生或女生
        $idSub2 = substr($input, 1, 1);

        if ($idSub2 !== '1' && $idSub2 !== '2') {
            return false;
        }

        for ($i = 1; $i < 10; $i++) {
            $idSub3 = substr($input, $i, 1);
            $idSub3 = ord($idSub3);
            if ($idSub3 > 57 || $idSub3 < 48) {
                $n=$i+1;
                return false;
            }
        }

        // 驗證格式
        $num = array(
            'A' => '10', 'B' => '11', 'C' => '12', 'D' => '13', 'E' => '14', 'F' => '15',
            'G' => '16', 'H' => '17', 'J' => '18', 'K' => '19', 'L' => '20', 'M' => '21',
            'N' => '22', 'P' => '23', 'Q' => '24', 'R' => '25', 'S' => '26', 'T' => '27',
            'U' => '28', 'V' => '29', 'X' => '30', 'Y' => '31', 'W' => '32', 'Z' => '33',
            'I' => '34', 'O' => '35'
        );

        $d1 = substr($input, 0, 1);
        $n1 = substr($num[$d1], 0, 1) + (substr($num[$d1], 1, 1) * 9);
        $n2 = 0;
        for ($j = 1; $j < 9; $j++) {
            $d4=substr($input, $j, 1);
            $n2=$n2+$d4*(9-$j);
        }
        $n3 = $n1 + $n2 + substr($input, 9, 1);
        if (($n3 % 10) !== 0) {
            return false;
        }
        return true;
    }
}
