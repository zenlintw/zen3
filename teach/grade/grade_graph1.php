<?php
	/**
	 * ※ 產生組距圖表 (長條圖)
	 *
	 * @since   2004/08/31
	 * @author  Wiseguy Liang
	 * @version $Id: grade_graph1.php,v 1.1 2010/02/24 02:40:27 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics1.php');
	require_once(sysDocumentRoot . '/lib/wm_graph.php');

	$data = array();
	for($i=0; $i<=100; $i+=10) $data[$i] = 0;

	$scores = explode(',', $_POST['scores']);
	array_splice($scores, -2); // 高低標一起傳過來，所以砍掉。

	$sco_len = count($scores);
	for($i=count($scores)-1; $i>=0; $i--)
	{
		$data[10 * (int)floor($scores[$i] / 10)]++;
	}

	$leg  = array_keys($data);
	$data = array_values($data);

	$graph = new WMGraph();
	$graph->setMargin(80,30,50,50);
	$graph->setGraphTitle($MSG['grad_picture'][$sysSession->lang]);
	$graph->setXaxisTitle($MSG['grad_score'][$sysSession->lang], 5);
	$graph->setYaxisTitle($MSG['grad_person'][$sysSession->lang], 55);
	$graph->setXaxisData($leg);
	$graph->setYaxisData($data);
	$graph->draw();