<?php
	/**
	 * �Ǯղέp��� - �n�J���Ʋέp - ������ - ������
	 * �إߤ���G2004/08/16
	 * @author  Amm Lee
	 * @version $Id: login_single_graph.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics1.php');
	require_once(sysDocumentRoot . '/lib/wm_graph.php');
	
	$datax = explode(',', $_POST['x_scale']);
	$datay = explode(',', $_POST['y_scale']);

	$graph = new WMGraph(500, 300);
	$graph->setMargin(80, 30, 60, 50);
	$graph->setGraphTitle($MSG['title9'][$sysSession->lang]);
	$graph->setXaxisTitle($MSG['hour'][$sysSession->lang], 5);
	$graph->setYaxisTitle($MSG['title7'][$sysSession->lang], 60);
	$graph->setGraphSubTitle($MSG['title10'][$sysSession->lang] . $_POST['period_date']);
	$graph->setXaxisData($datax);
	$graph->setYaxisData($datay);
	$graph->draw();
?>
