<?php
	/**
     * 網站連結管理
     * 處理資料庫運作, 新增, 刪除, 修改, 查詢
     *
     * @since   2012/02/08
     * @author  Kuko Wang
     * @version $Id: links_handler.php $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');


	$basePath = sprintf('%s/base/%05d/door/',
						sysDocumentRoot,
						$sysSession->school_id);
	$oDirPath='網站連結/';
    if (!file_exists($basePath.$oDirPath)) {
        mkdir($basePath.$oDirPath, 0777);
    }
	$dirPath = $oDirPath;
    /**
	 * 錯誤代碼
	 * @var string
	 */
    $errorCode='';

	/**
	 * 初始表單資料
	 *
	 * @param array $formData
	 * @return array $formData
	 */
	function initFormData($poster){
	    $formData['name']=htmlspecialchars(trim($_POST['name']));

	    if (isset($_POST['ck_open_date'])  && $_POST['ck_open_date' ]==1) {
            $formData['open_date_flag']=1;
            $formData['open_date']=trim($_POST['open_date']);
        } else {
            $formData['open_date_flag']=0;
            $formData['open_date']=date('Y-m-d');
        }

        if (isset($_POST['ck_close_date'])  && $_POST['ck_close_date']==1) {
            $formData['close_date_flag']=1;
            $formData['close_date']=trim($_POST['close_date']);
        } else {
            $formData['close_date_flag']=0;
            $formData['close_date']='';
        }
        $formData['url']=trim($_POST['url']);
        $formData['poster'] = $poster;
        $formData['create_datetime'] = date('Y-m-d H:i:s');

        return $formData;
	}

	/**
	 * 執行更新資料
	 * 進行檔案搬移，與資料更新
	 *
	 * @param array $formData
	 * @return string $errorCode
	 */
	function updateData($advId, $formData, $files) {
	    global  $basePath, $dirPath, $oDirPath;
        $errorCode='';
        $advId=intval(trim($advId));
        if ($advId>0) {
            $updateData=array();
            unset($formData['poster']);
            unset($formData['create_datetime']);
            foreach($formData as $key => $value) {
                $updateData[]=$key.'="'.$value.'"';
            }


            if ($files['size']>0) {
                $word = stripslashes($files['name']);
                $filename = $word;

                $imgPath = $oDirPath . $word;
                $fullPath = $basePath . $dirPath . $filename;

                $move = move_uploaded_file($files['tmp_name'], $fullPath);

            	if ($move) {
            	    $data = dbGetStSr('CO_links', 'img_path', "links_id='".$advId."'");
            	    $updateData[]="img_path='".$imgPath."'";
            	    @unlink($basePath . $data['img_path']);
            	} else {
            		$errorCode='upload_error';
            		return $errorCode;
            	}
            }
        	dbSet('CO_links', implode(',', $updateData), 'links_id='.$advId);

        } else {
            $errorCode='id_not_found';
        }

        return $errorCode;
	}


	/**
	 * 執行刪除資料
	 * 刪除檔案與資料
	 *
	 * @param int $advId
	 * @return string $errorCode
	 */
	function rmData($advId) {
	    global  $sysSession, $basePath, $dirPath, $sysConn;
	    $errorCode='';  //尚無制定刪除錯誤訊息
	    for($i=0, $size=count($advId); $i<$size; $i++) {
		   $data = dbGetStSr('CO_links', 'img_path', "links_id='".$advId[$i]."'", ADODB_FETCH_ASSOC);
		   $word = $data['img_path'];
           $imgPath = $word;

		   @unlink($basePath . $imgPath);
	       dbDel('CO_links', 'links_id='.$advId[$i]);
	    }

	    $sql = sprintf("select links_id from CO_links where 1 order by permute asc ", $sysSession->school_id);
	    $rs = $sysConn->Execute($sql);
	    $i =1;
	    while (!$rs->EOF) {
            $id = $rs->fields[0];
            $rs->MoveNext();

            $sql1 = "update CO_links set permute=$i where links_id=$id";
            $sysConn->Execute($sql1);
            $i++;
        }

	    return $errorCode;
	}

	/**
	 * 執行新增資料
	 * 檔案搬移與資料寫入
	 *
	 * @param array $formData, array $files
	 * @return string $errorCode
	 */
    function newData($formData, $file) {
        global  $sysSession, $basePath, $dirPath, $oDirPath;

        $word = stripslashes($file['name']);
        $filename = $word;

        $formData['img_path'] = $oDirPath . $word;
        $fullPath = $basePath . $dirPath . $filename;

        $move = move_uploaded_file($file['tmp_name'], $fullPath);

    	if ($move) {
    	    $data=dbGetStSr('CO_links', 'max(permute) as permute', sprintf('school_id=%d',$sysSession->school_id), ADODB_FETCH_ASSOC);
    	    $formData['permute']= intval($data['permute'])+1;
            $formData['school_id'] = $sysSession->school_id;

    	    foreach ($formData as $key => $value) {
    	        $fields[]=$key;
    	        $values[]="'".$value."'";
    	    }

    		dbNew('CO_links',
    			implode(',', $fields),
    			implode(',', $values)
    		);

    		// dbSet('CO_links', 'permute=permute+1', '1');

    	} else {
    		$errorCode='upload_error';
    	}


        return $errorCode;
    }

    /**
	 * 日期檢查
	 * 檢查開始日期與結束日期是否合法
	 *
	 * @param date $start, date $emd
	 * @return boolean
	 */
    function checkDateLimit($start, $end){
        if($start > $end) {
            return false;
        } else {
            return true;
        }
    }

    /**
	 * 驗證URL
	 *
	 * @param string $url
	 * @return boolean
	 */
    function checkURL($url) {
        if (preg_match("#^http(s)?://[a-z0-9-_.]+\.[a-z]{2,4}#i", $url)) {
            return true;
        } else {
            return false;
        }
    }


	/**
	 * 上傳資料格式驗證
	 * 驗證檔案名稱, 檔案大小, 檔案類型, (TODO:圖檔長寬)
	 *
	 * @param array $file
	 * @return string $errorCode
	 */
	function checkUploadFile($file){
	    global  $basePath, $dirPath;
	    $errorAry=array();

	    if(!file_exists($basePath.$dirPath)) {
            $errorAry[]='file_exist_error';
        }

		if ($file['name']==='') {
	        $errorAry[]='upload_empty_error';

	    } else {
            $fileSize=$file['size']/(1024*1024);
            $subAry=array('bmp','jpg','gif','png');
            $typeAry=array('image/jpeg', 'image/pjpeg', 'image/bmp', 'image/gif', 'image/png', 'image/x-png');
            $start= strrpos($file['name'], '.') + 1;
            $length= strlen($file['name']) - $start;
            $subName=strtolower(substr($file['name'], $start, $length));

            if ($fileSize>64) {
    	        $errorAry[]='upload_over_error';
            }

	        if (!in_array($file['type'], $typeAry) || !in_array($subName, $subAry)) {
    	        $errorAry[]='file_type_error';
            }
             //TODO: 驗證圖檔長寬 等候版型確定
	    }

	    if (count($errorAry)>0) {
            return implode(',', $errorAry);
        } else {
            return '';
        }
	}

    /**
	 * 驗證接收資料
	 *
	 * @param array $formData
	 * @return string errorCode List
	 */
    function checkFormData($formData, $file, $updateFlag=false) {
       $errorAry=array();
       $uploadErr='';

       if ($updateFlag==false || ($updateFlag==true && $file['size']>0)) {
           $uploadErr=checkUploadFile($file);
       }

       if ($formData['name']=='') {
           $errorAry[]='empty_links_name';
       } elseif (strlen($formData['name']) > 100) {
           $errorAry[]='string_over_100';
       }

       if ($formData['open_date_flag']==0 && $formData['close_date_flag']==1) {
           if (!checkDateLimit($formData['open_date'], $formData['close_date'])) {
               $errorAry[]='close_date_error';
           }
       } else if ($formData['open_date_flag']==1 && $formData['close_date_flag']==1) {
           if (!checkDateLimit($formData['open_date'], $formData['close_date'])) {
               $errorAry[]='close_date_error';
           }
       }

       if (!checkURL($formData['url'])) {
           $errorAry[]='check_url_error';
       } elseif (strlen($formData['url']) > 1000) {
           $errorAry[]='string_over_1000';
       }

       if(count($errorAry)>0) {
           return $uploadErr.implode(',', $errorAry);
       } else {
           return $uploadErr;
       }
    }
?>