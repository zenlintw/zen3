<?php
	/**
	 * 學校統計資料 - 登入次數統計 - 年報表 -  長條圖
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: login_year_graph.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics1.php');
	require_once(sysDocumentRoot . '/lib/wm_graph.php');
	
	$datax = explode(',', $_POST['x_scale']);
	$datay = explode(',', $_POST['y_scale']);

	$graph = new WMGraph(550, count($datay)*20+200, true);
	$graph->setMargin(60, 40, 50, 50);
	$graph->setGraphTitle($MSG['title12'][$sysSession->lang]);
	$graph->setXaxisTitle($MSG['year'][$sysSession->lang], 35);
	$graph->setYaxisTitle($MSG['title7'][$sysSession->lang], 60);
	$graph->setGraphSubTitle($MSG['title10'][$sysSession->lang] . $_POST['period_date']);
	$graph->setXaxisData($datax);
	$graph->setYaxisData($datay);
	$graph->draw();
?>
