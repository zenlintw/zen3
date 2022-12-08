<?php
	/**
	 * 學校統計資料 - 使用者人數統計 - 性別 - 長條圖
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: user_gender_graph.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics1.php');
	require_once(sysDocumentRoot . '/lib/wm_graph.php');
	
	$datax = explode(',', $_POST['x_scale']);
	for ($i = 0; $i < count($datax); $i++)
		$datax[$i] = $datax[$i] == 'M' ? $MSG['title19'][$sysSession->lang] : ($datax[$i] == 'F' ? $MSG['title20'][$sysSession->lang]:$MSG['not_shown'][$sysSession->lang]);
		
	$datay  = explode(',', $_POST['y_scale']);
	
	$graph = new WMGraph();
	$graph->setMargin(60, 40, 40, 50);
	$graph->setGraphTitle($MSG['title21'][$sysSession->lang]);
	$graph->setXaxisTitle($MSG['title22'][$sysSession->lang], 10);
	$graph->setYaxisTitle($MSG['title23'][$sysSession->lang], 40);
	$graph->setXaxisData($datax);
	$graph->setYaxisData($datay);
	$graph->draw();