<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/file_api.php');
    require_once(sysDocumentRoot . '/mooc/controllers/JSON.php');
    require_once(sysDocumentRoot . '/lang/mooc_upload.php');

    /*
    * $json->encode, $json->decode 宣告，以利後續使用
    */
    if (!function_exists('json_encode')) {

        function json_encode($val)
        {
            $json = new Services_JSON();
            return $json->encode($val);
        }

        function json_decode($val)
        {
            $json = new Services_JSON();
            return $json->decode($val);
        }
    }
    
    // 判斷檔案大小
    $data = array();
    if (detectUploadSizeExceed() === true) {
        global $MSG, $sysSession;
        
        $data['textStatus'] = 'fail';
        $data['files'][] = array(
            'original_name' => $path_parts['basename'],
            'name' => $fileName,
            'size' => $_FILES["files"]["size"][0],
            'type' => get_mime_type($desPath . $fileName),
            'error' => $MSG['uploaded_over'][$sysSession->lang] . ' (' . ini_get('post_max_size') . 'B )'
        );
    } else {

    //    function create_folders($dir){
    //        return is_dir($dir) or (create_folders(dirname($dir)) and mkdir($dir, 0777));
    //    }

        $path_parts = pathinfo($_FILES['files']['name'][0]);
        $fileName = 'WM' . uniqid('') . '.' .$path_parts['extension'];

    //    $desPath = '/tmp/' . $_POST['tmp'] . '/';
        $desPath = getUserBasePath();
        mkdirs($desPath);

        if (rename($_FILES["files"]["tmp_name"][0], $desPath . $fileName) === true) {
            $data['textStatus'] = 'success';
            $data['files'][] = array(
                'original_name' => $path_parts['basename'],
                'name' => $fileName,
                'size' => $_FILES["files"]["size"][0],
                'type' => get_mime_type($desPath . $fileName)
            );
        } else {
            $data['textStatus'] = 'fail';
            $data['files'][] = array(
                'original_name' => $path_parts['basename'],
                'name' => $fileName,
                'size' => $_FILES["files"]["size"][0],
                'type' => get_mime_type($desPath . $fileName),
                'error' => 'Rename Fail!'
            );
        }
    }

    $msg = json_encode($data);

    if ($msg !== '') {
        echo $msg;
    }