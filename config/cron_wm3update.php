#!/usr/local/bin/php
<?php
    /**
     *  wm3update 常駐執行線上更新的排程程式
     *
     * @since   2019/08/01
     * @author  Jeff Wang
     * @version $Id: cron_daily.php,v 1.1 2010/02/24 02:38:56 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     *
     **/

    // 系統設定
    set_time_limit(0);
    require_once(dirname(__FILE__) . '/console_initialize.php');
    require_once(sysDocumentRoot . '/academic/wm3update/lib.php');

    $oUpdSess = new WM3UpdateSession("cronUpdate");
    if ($oUpdSess->hasInstruction()) {
        exec("/bin/mount -o remount,rw,bind ".sysDocumentRoot);
        $oUpdSess->doInstruction();
        exec("/bin/mount -o remount,ro,bind ".sysDocumentRoot);
    }
