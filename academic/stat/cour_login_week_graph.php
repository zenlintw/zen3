<?php
    /**
     * 學校統計資料 - 上課次數統計 - 週報表 - 長條圖
     * 建立日期：2004/08/16
     * @author  Amm Lee
     * @version $Id: cour_login_week_graph.php,v 1.1 2010/02/24 02:38:42 saly Exp $
     * @copyright 2003 SUNNET
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/sch_statistics1.php');
    require_once(sysDocumentRoot . '/lib/wm_graph.php');
    
    $datax = explode(',', $_POST['x_scale']);
    $datay = explode(',', $_POST['y_scale']);

    $graph = new WMGraph(760, count($datay)*20+200, true);
    $graph->setMargin(180, 40, 50, 50);
    $graph->setGraphTitle($MSG['title14'][$sysSession->lang]);
    $graph->setXaxisTitle($MSG['week'][$sysSession->lang], 155);
    $graph->setYaxisTitle($MSG['title17'][$sysSession->lang], -20);
    $graph->setGraphSubTitle($MSG['title10'][$sysSession->lang] . $_POST['period_date']);
    $graph->setXaxisData($datax);
    $graph->setYaxisData($datay);
    $graph->draw();
?>
