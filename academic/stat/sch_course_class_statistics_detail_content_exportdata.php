<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/JSON.php');
	require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
	require_once(sysDocumentRoot . '/lang/personal.php');
	require_once(sysDocumentRoot . '/lang/teach_statistics.php');
	require_once(sysDocumentRoot . '/mooc/models/statistics.php');

	//setlocale(LC_ALL, 'UTF-8');
	$segment = ","; // 分隔符號
	
	$content = "No."		            								. $segment . // 項次
			   $MSG['account_name'][$sysSession->lang]		            . $segment . // 帳號(姓名)
			   $MSG['sex'][$sysSession->lang]	    					. $segment . // 性別
			   $MSG['age'][$sysSession->lang]							. $segment . // 年齡
			   $MSG['educational_background'][$sysSession->lang]		. $segment . // 學歷
			   $MSG['identity'][$sysSession->lang]						. $segment . // 身分
			   $MSG['source_region_country'][$sysSession->lang] 		. $segment . // 來源地區(國家)
			   $MSG['it_has_completed'][$sysSession->lang] 				. $segment . // 是否已完成
			   $MSG['it_has_been_through'][$sysSession->lang]; 						 // 是否已通過
	
	$stat = new Statistics();
	$data = $stat->courseStudentInfoList($_POST['cid']);

	if(sizeof($data)>0){
		// i為項次
		$i=1;
		foreach($data as $key => $value) {

			// 性別
			if($value['gender'] == 'M') {
				$gender = $MSG['male'][$sysSession->lang];
			} elseif($value['gender'] == 'F') {
				$gender = $MSG['female'][$sysSession->lang];
			} else {
				$gender = $MSG['not_marked'][$sysSession->lang];
			}

			// 年齡
			if($value['age']>0){
				$age = $value['age'];
			} else {
				$age = $MSG['not_marked'][$sysSession->lang];
			}

			//姓名
			$userName = $value['userName'] ."(".$value['last_name']."".$value['first_name'].")";

			// 學歷
			if($value['education'] == 'P') {
				$education = $MSG['elementary_school'][$sysSession->lang];
			} else if($value['education'] == 'H') {
				$education = $MSG['junior_high_school'][$sysSession->lang];
			} else if($value['education'] == 'S') {
				$education = $MSG['high_school'][$sysSession->lang];
			} else if($value['education'] == 'U') {
				$education = $MSG['university'][$sysSession->lang];
			} else if($value['education'] == 'M') {
				$education = $MSG['masters_degree'][$sysSession->lang];
			} else if($value['education'] == 'D') {
				$education = $MSG['doctoral_degree'][$sysSession->lang];
			} else if($value['education'] == 'O') {
				$education = $MSG['other'][$sysSession->lang];
			} else {
				$education = $MSG['not_marked'][$sysSession->lang];
			}

			// 身分
			if($value['user_status'] == 'S') {
				$user_status = $MSG['student'][$sysSession->lang];
			} else if($value['user_status'] == 'W'){
				$user_status = $MSG['at_work'][$sysSession->lang];
			} else {
				$user_status = $MSG['not_marked'][$sysSession->lang];
			}

			// 來源地區(國家)
			if($value['country'] == 'TW') {
				$country = $MSG['TW'][$sysSession->lang];
			} else if($value['country'] == 'CH') {
				$country = $MSG['CH'][$sysSession->lang];
			} else if($value['country'] == 'JA') {
				$country = $MSG['JA'][$sysSession->lang];
			} else if($value['country'] == 'IN') {
				$country = $MSG['IN'][$sysSession->lang];
			} else if($value['country'] == 'US') {
				$country = $MSG['US'][$sysSession->lang];
			} else if($value['country'] == 'AS') {
				$country = $MSG['AS'][$sysSession->lang];
			} else if($value['country'] == 'O') {
				$country = $MSG['other'][$sysSession->lang];
			} else {
				$country = $MSG['not_marked'][$sysSession->lang];
			}

			// 是否已完成
			if($value['userFinish'] == 1) {
				$userFinish = $MSG['already_finish'][$sysSession->lang];
			} else {
				$userFinish = $MSG['no_finish'][$sysSession->lang];
			}
			// 是否已通過 
			if($value['userPass'] == 1) {
				$userPass = $MSG['already_pass'][$sysSession->lang];
			} else {
				$userPass = $MSG['no_pass'][$sysSession->lang];
			}

			$content .= "\n".'"' . $i    		.'"'. $segment . // 項次
						 '"' . $userName	   	.'"'. $segment . // 帳號(姓名)
						 '"' . $gender   		.'"'. $segment . // 性別
						 '"' . $age   			.'"'. $segment . // 年齡
						 '"' . $education 		.'"'. $segment . // 學歷
						 '"' . $user_status     .'"'. $segment . // 身分
						 '"' . $country  		.'"'. $segment . // 來源地區(國家)
						 '"' . $userFinish  	.'"'. $segment . // 是否已完成
						 '"' . $userPass  		.'"';     		 // 是否已通過

			//需每次清空資料 避免下筆資料因為資料未洗掉而重複顯示
			$userName = '';
			$gender = '';
			$value['age'] = '';
			$education = '';
			$user_status = '';
			$country = '';
			$userFinish = '';
			$userPass = '';

			$i++;
		}
	}
	
	$today = date("Ymd");
	
	$file_name = urlencode($_POST['className']."_ClassDetail_".$today.".csv");

    header('Content-Transfer-Encoding: binary');
    header('Content-Type: text/plain;');
	header("Content-Disposition: attachment; filename={$file_name}");

	//$content = mb_convert_encoding($content, 'UTF-8', $sysSession->lang);
	$content = utf8_to_excel_unicode($content);

	echo $content;
	exit;
	
?>