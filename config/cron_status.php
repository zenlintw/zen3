#!/usr/local/bin/php
<?php
	/**
	 *	※ 定時蒐集系統資訊 (目前是實驗性的，每 5 分鐘記錄系統狀態 just for linux)
	 *
	 * @since   2004/09/15
	 * @author  Wiseguy Liang
	 * @version $Id: cron_status.php,v 1.1 2010/02/24 02:38:56 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 *
	 **/

	// 系統設定
	require_once(dirname(__FILE__) . '/sys_config.php');
	require_once(sysDocumentRoot . '/lib/adodb/adodb.inc.php');
	
	// 資料庫連結初始化
	$sysConn = &ADONewConnection(sysDBtype);
	if (!$sysConn->PConnect(sysDBhost, sysDBaccoount, sysDBpassword, sysDBname))
		die('Database Connecting failure !');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

	// CPU
	if (($fp = popen('/usr/bin/top -bn 1', 'r')) !== false)
	{
	    while (!feof($fp))
	    {
	        if (preg_match('/^Cpu.* ([\d.]+)% id(le)?/', fgets($fp, 4096), $regs))
	        {
				$sysConn->AutoExecute('WM_status_cpu', array('log_time' => 'now()',
				                                             'idle'     => floatval($regs[1])));
				break;
			}
		}
		pclose($fp);
	}
	
	
	// 記憶體
	if (($fp = popen('/usr/bin/free', 'r')) !== false)
	{
	    while (!feof($fp))
	    {
	        $bufs = preg_split('/\s+/', fgets($fp, 4096), -1, PREG_SPLIT_NO_EMPTY);
	        if ($bufs[0] == 'Mem:')
	            $sysConn->AutoExecute('WM_status_mem', array('log_time'=> 'now()',
															 'total'   => intval($bufs[1]),
                                                             'used'    => intval($bufs[2]),
                                                             'free'    => intval($bufs[3]),
                                                             'shared'  => intval($bufs[4]),
                                                             'buffers' => intval($bufs[5]),
                                                             'buffers' => intval($bufs[6])));
			elseif ($bufs[0] == 'Swap:')
	            $sysConn->AutoExecute('WM_status_swap', array('log_time'=> 'now()',
															  'total'   => intval($bufs[1]),
                                                              'used'    => intval($bufs[2]),
                                                              'free'    => intval($bufs[3])));
		}
		pclose($fp);
	}
	
	//apache
	$count = `/bin/ps -ax | /bin/fgrep 'httpd' | /usr/bin/wc -l`;
	$sysConn->AutoExecute('WM_status_apache', array('log_time' => 'now()',
	                                                'amount'   => intval($count)-1));
	
	// mysql
	$rs = $sysConn->GetCol('show processlist');
	$count = (is_array($rs) && count($rs)) ? (count($rs)-1) : 0;
	$sysConn->AutoExecute('WM_status_mysql', array('log_time' => 'now()',
	                                                'amount'   => $count));
	
	// http
	$count = `/bin/netstat -a | /bin/fgrep ':http' | /usr/bin/wc -l`;
	$sysConn->AutoExecute('WM_status_http', array('log_time' => 'now()',
	                                              'amount'   => intval($count)-1));
?>
