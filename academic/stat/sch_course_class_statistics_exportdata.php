<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	// require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/JSON.php');
	require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
	// require_once(sysDocumentRoot . '/lang/co_classrecord_manage.php');
	// require_once(sysDocumentRoot . '/lang/co_boe.php');
	require_once(sysDocumentRoot . '/lang/teach_statistics.php');
	require_once(sysDocumentRoot . '/mooc/models/statistics.php');
	
	// 查詢條件
	$condition = $_GET['C'];
	
	// 關鍵字
	$keyword_post = urldecode($_GET['K']);
	$keyword      = mb_convert_encoding($keyword_post,'UTF-8','Big5');
	
	$year      = intval($_GET['Y']);
	$picks     = intval($_GET['P']);
	$success   = intval($_GET['S']);

	if($year == 0)    $year    = '';
	if($picks == 0)   $picks   = '';
	if($success == 0) $success = '';

	$segment = ","; // 分隔符號

	$data = dbGetAll('CO_classrecord T1 LEFT JOIN CO_class T2 ON T1.CategoryID = T2.id',
					 'T1.*',
					  $condition .' LIKE \'%'. $keyword .'%\' AND 
					  T2.open_date  LIKE \'%'. $year    .'%\' AND
					  T1.isSuccess  LIKE \'%'. $success .'%\' AND 
					  T1.Picks      LIKE \'%'. $picks   .'%\'',
					  ADODB_FETCH_ASSOC);
	
	$content = "No."		            								. $segment . // 項次
			   $MSG['course_title'][$sysSession->lang]		            . $segment . // 課程名稱
			   $MSG['enrollment'][$sysSession->lang]	    			. $segment . // 報名人數
			   $MSG['after_the_number_of_classes'][$sysSession->lang]	. $segment . // 完課人數
			   $MSG['after_class_rate'][$sysSession->lang]				. $segment . // 完課率
			   $MSG['by_number_of_people'][$sysSession->lang]			. $segment . // 通過人數
			   $MSG['by_rate'][$sysSession->lang]; // 通過率

	
	$stat = new Statistics();
	$data = $stat->getAllCourseInfo_Stat();

	if(sizeof($data)>0){
		// i為項次
		$i=1;
		foreach($data as $key => $value){
			$content .= "\n".'"' . $i    .'"'. $segment . // 項次
						 '"' . $value['caption']		.'"'. $segment . // 課程名稱
						 '"' . $value['studentCnt']		.'"'. $segment . // 報名人數
						 '"' . $value['finishCount']      .'"'. $segment . // 完課人數
						 '"' . $value['finishPercent']  	.'"'. $segment . // 完課率
						 '"' . $value['passCount']    .'"'. $segment . // 通過人數
						 '"' . $value['passPercent']  .'"';     		 // 通過率

			$value['caption'] = '';
			$value['studentCnt'] = '';
			$value['finishCount'] = '';
			$value['finishPercent'] = '';
			$value['passCount'] = '';
			$value['passPercent'] = '';

			$i++;
		}
	}
	
	$today = date("Ymd");
	
	$file_name = "ClassReport_".$today.".csv";
		
	header('Content-Transfer-Encoding: binary');
    header('Content-Type: text/plain;');
	header("Content-Disposition: attachment; filename={$file_name}");

	//$content = mb_convert_encoding($content, $sysSession->lang, 'UTF-8');
	$content = utf8_to_excel_unicode($content);
	
	echo $content;
	exit;
	
?>