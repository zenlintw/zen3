<?php
/**
 * 輸入文字驗證器，用來檢查輸入文字是否符合驗證條件
 *
 * @author sj
 * @copyright Copyright 2013 SUNNET LIMITED
 * @package Hongu/Validate
 */
class Hongu_Validate_Validator
{
    /**
     * result messages
     *
     * @var array
     */
    /*private*/var $_messages = array();

    /**
     * Check colums by rules
     *
     * @see Rule
     * @param array $columns
     * @param array $rules
     * @param boolean $columnBreak default is false [optional]<br>
     * if break set ture, the validator will break at any rule deny first.
     * @param boolean $ruleBreak default is true [optional]<br>
     * if break set ture, the rule as validator will be break at any rule deny first.
     * @return array
     */
    /*public*/ function check (/*array*/ &$columns, /*array*/ &$rules, $columnBreak=false, $ruleBreak=true)
    {
        $this->_messages = array();
        foreach ($rules as $id => $rule) {
            //run rule by step
            /*try {*/
                $this->_runRules($columns, $id, $rule, $ruleBreak);
            /*} catch (Exception $e) {*/
                if ($columnBreak) {
                    break;
                }
            /*}*/
        }
        return $this->_messages;
    }

    /**
     * Validate rules
     *
     * @param array $columns
     * @param string $id
     * @param array $rules
     * @param boolean $ruleBreak
     * @throws Exception
     */
    /*private*/ function _runRules (/*array*/ &$columns, $id, /*array*/ &$rules, $ruleBreak=true)
    {
        foreach ($rules as /*&*/$rule) {
            //check rule exist
            if (!class_exists($rule['rule_name'])) {
                   $this->_throwErrorMessage($id, 'checkRule', 'Validator not found');
            }

            // check rule
            /*try {*/
                $this->_runRule($columns, $id, $rule);
            /*} catch (Exception $exception) {*/
                if ($ruleBreak) {
                    /*throw $exception;*/
                }
            /*}*/
        }
    }

    /**
     * Validate rule
     *
     * @param array $columns
     * @param string $id
     * @param array $rule
     * @throws Exception
     */
    /*private*/ function _runRule (/*array*/ &$columns, $id, /*array*/ &$rule)
    {

        //process array colums
        $columnValue = '';
        if (isset($columns[$id])) {
            $columnValue = $columns[$id];
        }
        if (
            !in_array(
                $rule['rule_name'],
                array('Hongu_Validate_Validator_ArraySizeRange', 'Hongu_Validate_Validator_ArrayNotDuplicated')
            )
            && is_array($columnValue)
        ) {
            //TODO 應該把array處理邏輯交給checker處理
            foreach (array_keys($columnValue) as $key) {
                $this->_runRule($columnValue, $key, $rule);
            }
            return;
        }

        //check
        if (isset($rule['args'])) {
            $args = $rule['args'];
        } else {
            $args = null;
        }

        $result = true;
        $require = new Hongu_Validate_Validator_Required();
        eval('$validate = new ' . $rule['rule_name'] . '();');
        if ($rule['rule_name']==='Hongu_Validate_Validator_Required'
            && !/*Hongu_Validate_Validator_Required::*/$require->validate($columnValue)
        ) {
            $result = false;
        } elseif ($rule['rule_name'] === 'Hongu_Validate_Validator_ArraySizeRange') {
           if (isset($columns[$id]) && !Hongu_Validate_Validator_ArraySizeRange::validate($columnValue, $args)) {
               $result = false;
           }
        } elseif (
            strlen(chop($columnValue)) > 0
            && !$validate->validate($columnValue, $args)
        ) {
            $result = false;
        }

        if ((!$result && !$rule['resultFlip']) || ($result && $rule['resultFlip'])) {
            //invoke method to validate, and record the message
            $this->_throwErrorMessage($id, $rule['rule_name'], $rule['message']);
        }
    }

    /*private*/ function _convertValue (/*array*/ $args, /*array*/ $columns)
    {
        $key = array_pop(array_keys($args));
        $value = array_pop($args);
        if (!isset($columns[$key])) {
            return '';
        }
        if (is_array($columns[$key])&&is_array($value)) {
            return $this->_convertValue($value, $columns[$key]);
        }
        return $this->checkValues($columns[$key], array($value));
    }

    /*private*/ function _throwErrorMessage ($id, $rule, $message)
    {
        $this->_messages[] = array('id' => $id , 'rule' => $rule , 'message' => $message);
        /*throw new Exception('validator miss', 407);*/
    }

}