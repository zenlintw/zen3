<?php
    /**
     * 學校統計資料 - 登入次數統計 - 月報表 - 長條圖
     * 建立日期：2004/08/16
     * @author  Amm Lee
     * @version $Id: login_month_graph.php,v 1.1 2010/02/24 02:38:43 saly Exp $
     * @copyright 2003 SUNNET
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/sch_statistics1.php');
    require_once(sysDocumentRoot . '/lib/wm_graph.php');
    
    $datax = explode(',', $_POST['x_scale']);
    $datay = explode(',', $_POST['y_scale']);

    $graph = new WMGraph(760, count($datay)*20+200, true);
    $graph->setMargin(85, 40, 50, 50);
    $graph->setGraphTitle($MSG['title11'][$sysSession->lang]);
    $graph->setXaxisTitle($MSG['month'][$sysSession->lang], 60);
    $graph->setYaxisTitle($MSG['title7'][$sysSession->lang], -20);
    $graph->setGraphSubTitle($MSG['title10'][$sysSession->lang] . $_POST['period_date']);
    $graph->setXaxisData($datax);
    $graph->setYaxisData($datay);
    $graph->draw();
?>
