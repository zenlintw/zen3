<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	$sortBy = isset($_POST['sortBy']) ? $_POST['sortBy'] : $_GET['sortby'];
	$order = isset($_POST['order']) ? $_POST['order'] : $_GET['order'];
	$page = isset($_POST['page']) ? $_POST['page'] : $_GET['page'];
	$title = isset($_POST['theme']) ? addslashes($_POST['theme']) : '';
	if($_POST['type'] != 'delete'){
		if(mb_strlen($title, 'utf-8') > 255 || empty($sortBy) || empty($order) || empty($title) || empty($page)){
			echo "<script>alert('title exceed 255');location.href='co_download_manage.php'</script>";
			return;
		}
	}
	$kindType = intval($_POST['category']);
    $openflog = intval($_POST['ckopen']);
    $closeflog = intval($_POST['ckclose']);
	if($openflog == 1){
		//時間格式錯誤直接給無期限
		$opnbegintime = (preg_match("/^[\d]{4}-[\d]{2}-[\d]{2}$/i",trim($_POST['timeopen']))) ? $_POST['timeopen'] : "";
	}
	if($closeflog == 1){
		$opnendtime = preg_match("/^\d{4}\-\d{2}\-\d{2}$/i",trim($_POST['timeclose'])) ? $_POST['timeclose'] : "";
	}
	if(isset($opnbegintime) && isset($opnendtime)){
		if(strtotime($opnbegintime) > strtotime($opnendtime)){
			die("<script>alert('結束時間不能小於開始時間');location.href='co_download_manage.php';</script>");
		}
	}
	
	$filename = "";
	
	//檔案路徑
	$saveSpace = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR ."10001" . DIRECTORY_SEPARATOR ."download";
	
	function removeHandle($fileArr, $id){
		global $saveSpace;
		if(count($fileArr) != 0){
			foreach ($fileArr as $file) {
				$fileName = $saveSpace . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $file;
				if(file_exists($fileName)){
					unlink($fileName);
				}
			}
		}
		
	}
	function addHandle($type,$id){
		global $saveSpace;
		$parentSpace = $saveSpace . DIRECTORY_SEPARATOR . $id;
		if(!file_exists($parentSpace)){
			mkdir($parentSpace);
		}
		$fileArr = array();
		foreach ($_FILES['file']['error'] as $key => $value) {
			if($value == 0){
				$fileArr [] = array('tmp_name' => $_FILES['file']['tmp_name'][$key] , 'name' => $_FILES['file']['name'][$key]);
			}
			
		}
		$fileNames = array();
		if(count($fileArr) != 0){
			foreach ($fileArr as $file) {
				if(is_uploaded_file($file['tmp_name'])){
					$filename = $parentSpace . DIRECTORY_SEPARATOR . $file['name'];
					move_uploaded_file($file['tmp_name'] , $filename);
				}
			}
		}
		
		//讀取資料夾裡的檔案 
		if ($dh = opendir($parentSpace)) {
			while (($file = readdir($dh)) !== false) {
				if($file == '.' || $file == '..'){
					continue;
				}else{
					$fileNames[] = $parentSpace . DIRECTORY_SEPARATOR . $file;
					
				}
			}
		}
		
		$fileStr = implode(chr(9), $fileNames);
		return $fileStr;
	}
	function fileParentdir(){
		global $saveSpace;
		if(!file_exists($saveSpace)){
			mkdir($saveSpace);
		}
	}
	function mkdirDirForId($type){
		if($type == 'update'){
			$id = intval($_POST['id']);
		}else{
			$id = dbGetOne('CO_download','MAX(id)','1=1');
		}
		return $id;
	}
	function filterInt($val){
		return intval($val);
	}
	switch($_POST['type']){
		case 'update':
			$id = mkdirDirForId('update');
			removeHandle($_POST['del'], $id);
			$fileStr = addHandle('update',$id);
			if($id){
				$field = sprintf("title='%s',attach_path='%s',open_date_flag='%s',open_date='%s',close_date_flag='%s',close_date='%s',kind='%d',modifier='%s',modify_time=now()",$title,$fileStr,$openflog,$opnbegintime,$closeflog,$opnendtime,
					$kindType,$sysSession->username);
				dbSet("CO_download",$field,"id=$id");
			}
			header("location:co_download_manage.php?sortby={$sortBy}&order={$order}&page={$page}");
		break;
		case 'new':
			fileParentdir();
			$downloadId = mkdirDirForId("new");
			$downloadId ++;
			$fileStr = addHandle('new',$downloadId);
			dbNew('CO_download', 'id,title,attach_path,open_date_flag,open_date,close_date_flag,close_date,kind,creator,create_time' , "\"{$downloadId}\",\"{$title}\",\"{$fileStr}\",\"{$openflog}\",\"{$opnbegintime}\",\"{$closeflog}\",\"{$opnendtime}\",\"{$kindType}\",\"{$sysSession->username}\",now()");
			header("location:co_download_manage.php?sortby={$sortBy}&order={$order}&page={$page}");
		break;
		case 'delete':
			$idArr = array_map("filterInt", $_POST['ids']);
			$idStr = implode(',', $idArr);
			dbSet('CO_download', 'delete_flag=1', "id in ({$idStr})");
		break;
		default:
			header("location:co_download_manage.php?sortby={$sortBy}&order={$order}&page={$page}");
			break;
	}
