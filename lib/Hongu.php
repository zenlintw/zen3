<?php /*if (!defined('BASEPATH')) exit('No direct script access allowed');*/
/*
 * PHP4沒有PHP5的autoload，故全部載入
 */
$validatorPath = '/lib/Hongu/Validate/';
foreach (array_merge(
    glob(sysDocumentRoot . $validatorPath . '*.php'),
    glob(sysDocumentRoot . $validatorPath . 'Validator/*.php')
    ) as $filename) {
    require_once $filename;
}

/**
 * 用來整合 Hongu Library
 *
 * @author sj
 *
 */

class Hongu
{
    /**
     * @var Hongu_Validate_Validator
     */
    /*private*/ var $_validator;

    /*public*/ function Hongu/*__construct*/()
    {
        // 註冊自動載入 Class 的機制
        /*spl_autoload_unregister('Hongu::autoload');
        spl_autoload_register('Hongu::autoload', true, true);*/
    }

    /*public static function autoload($className)
    {
        if (preg_match('/^Hongu_.*$/', $className) > 0) {
            $filename = sprintf('%sapplication/libraries/%s.php', ROOT_PATH, str_replace('_', DS, $className));
            require($filename);
            return true;
        }
        return false;
    }*/

    /**
     * 取得 Hongu 驗證器
     *
     * return Hongu_Validate_Validator
     */
    /*public*/ function getValidator()
    {
        if (is_null($this->_validator)) {
            $this->_validator = new Hongu_Validate_Validator();
        }
        return $this->_validator;
    }
}
