<?php
	/**
	 * 學校統計資料 - 使用者人數統計 - 身份 - 長條圖
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: user_role_graph.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics1.php');
	require_once(sysDocumentRoot . '/lib/wm_graph.php');
	
	$datax = explode(',', $_POST['x_scale']);
	foreach($datax as $k => $v) 
		$datax[$k] = $MSG[$v][$sysSession->lang];
	
	$datay  = explode(',', $_POST['y_scale']);
		
	$graph = new WMGraph();
	$graph->setGraphTitle($MSG['title21'][$sysSession->lang]);
	$graph->setXaxisTitle($MSG['title25'][$sysSession->lang], 10);
	$graph->setYaxisTitle($MSG['title23'][$sysSession->lang], 20);
	$graph->setXaxisData($datax);
	$graph->setYaxisData($datay);
	$graph->draw();
?>
