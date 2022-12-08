<?php

/**
 * Configuration of validator's rules generator
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 * @see Hongu_Validate_Validator
 */
class Hongu_Validate_Rule
{
    /**
     * Make REQUIRED configuration rule.<br />
     * The colums must be entered and not null.
     *
     * @param string $ruleName 驗證規則的名稱(Validator Class Name)
     * @param string $args 驗證需要的參數 [optional]
     * @param string $message error message [optional]
     * @param boolean $resultFlip 驗證結果翻轉 [optional]
     * @return array = configuration rule
     */
    /*public static*/ function MAKE_RULE($ruleName, /*array*/ $args=null, $message=null, $resultFlip=false)
    {
        // fix rule name
        if (strpos($ruleName, '_') === false) {
            $ruleName = 'Hongu_Validate_Validator_' . $ruleName;
        }
        return array(
            'rule_name' => $ruleName,
            'args' => $args,
            'message' => $message,
            'resultFlip' => $resultFlip
        );
    }
}