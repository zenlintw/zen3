<?php
    /**
     * 學校統計資料 - 上課次數統計 - 單日報表 - 長條圖
     * 建立日期：2004/08/16
     * @author  Amm Lee
     * @version $Id: cour_login_single_graph.php,v 1.1 2010/02/24 02:38:42 saly Exp $
     * @copyright 2003 SUNNET
     **/
    
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/sch_statistics1.php');
    require_once(sysDocumentRoot . '/lib/wm_graph.php');
    
    $datax = explode(',', $_POST['x_scale']);
    $datay = explode(',', $_POST['y_scale']);

    $graph = new WMGraph(760, 300);
    $graph->setMargin(65, 30, 60, 50);
    $graph->setGraphTitle($MSG['title13'][$sysSession->lang]);
    $graph->setXaxisTitle($MSG['hour'][$sysSession->lang], 10);
    $graph->setYaxisTitle($MSG['title18'][$sysSession->lang], 40);
    $graph->setGraphSubTitle($MSG['title10'][$sysSession->lang] . $_POST['period_date']);
    $graph->setXaxisData($datax);
    $graph->setYaxisData($datay);
    $graph->draw();
?>
