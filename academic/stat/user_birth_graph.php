<?php
	/**
	 * 學校統計資料 - 使用者人數統計 - 年齡間距 - 長條圖
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: user_birth_graph.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics1.php');
	require_once(sysDocumentRoot . '/lib/wm_graph.php');
	
	$datax  = explode(',', $_POST['x_scale']);
	$datay  = explode(',', $_POST['y_scale']);
		
	$graph = new WMGraph(count($datay)*40+200, 500);
	$graph->setMargin(50,30,40,40);
	$graph->setGraphTitle($MSG['title21'][$sysSession->lang]);
	$graph->setXaxisTitle($MSG['title24'][$sysSession->lang], 5);
	$graph->setYaxisTitle($MSG['title23'][$sysSession->lang], 25);
	$graph->setXaxisData($datax);
	$graph->setYaxisData($datay);
	$graph->draw();
?>
