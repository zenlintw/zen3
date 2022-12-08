<?php
/**
 * 取得首頁計數圖示的html表示字串
 *
 * 建立日期：2005/08/17
 * @author Hubert
 * @version $Id: lib_counter.php,v 1.0
 * @copyright 2004 SUNNET
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

function getCountHtml()
{
    global $sysSession;
    
    // 由於放在counter.txt仍有歸零的情況，故寫至資料庫 by Small 2007/3/20
    dbSet('WM_school', 'counter=counter+1', "school_id = {$sysSession->school_id}");
    
    // 從資料庫撈counter值出來 by Small 2007/3/20
    list($counter) = dbGetStSr('WM_school', 'counter', "school_id = {$sysSession->school_id}");
    
    return $counter;
}

function CounterIncrement()
{
    $o_counter = new counter();
    $o_counter->increment();
}

/**
*  @name 計數器類別
*  @abstract 提供首頁計數器使用
*  @author jeff
*  @since 2005-07-29
*/
class counter
{
    var $_file;
    var $_count;
    var $_failure;

    function counter()
    {
        global $sysSession;
        $this->_file = sysDocumentRoot . "/base/{$sysSession->school_id}/counter.txt";
        if (!file_exists($this->_file))
        {
            list($con) = dbGetStSr('WM_log_others', 'count(note) as con', "note='login success'", ADODB_FETCH_NUM);// 指定欄位ㄧ
            $this->_count = $con;
            $this->updateCountFile();
        }else{
            $this->setCount();// 設定計數內容
        }
        $this->_failure = false;
    }

    /**
        @name increment
        @abstract 計數加入
    */
    function increment()
    {
        $this->_count++;
        $this->updateCountFile();
    }

    /**
        @name getCount()
        @abstract 取出計數器檔案的值
    */
    function setCount()
    {
        if (($str = file_get_contents($this->_file)) === FALSE || empty($str)) {
            $this->_failure = true;
        } else {
            $this->_count = intval(trim($str));
        }
    }

    /**
        @name updateCountFile
        @abstract 更新計數檔案
    */
    function updateCountFile()
    {
        if ($this->_failure) return;

        $fp = fopen($this->_file, "w");
        fputs($fp,$this->_count);
        fclose($fp);
    }
}