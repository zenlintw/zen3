<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/controllers/JSON.php');
    require_once(sysDocumentRoot . '/mooc/models/course.php');

    if ($_POST['bid'] === NULL || $_POST['bid'] === 0) {
        die('course id error!!');
    }

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

    function create_folders($dir){
        return is_dir($dir) or (create_folders(dirname($dir)) and mkdir($dir, 0777));
    }

    $path_parts = pathinfo($_FILES['files']['name'][0]);
    if (strlen($path_parts['extension']) > 0) {
        if (preg_match('/^\w+$/', $path_parts['extension']) === 0) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
    }
    $fileName = 'WM' . uniqid('') . '.' .$path_parts['extension'];

//    // 新增模式/ 回應模式
//    if ($_POST['tmp'] !== '') {
//        $dirPersonal = md5($sysSession->username);
//        $dirPath = "/base/{$sysSession->school_id}/course/{$sysSession->course_id}/board/temp/{$dirPersonal}/{$_POST['tmp']}/";
//    // 編輯模式
//    } else if ($_POST['tmp'] === '' && $_POST['mnode'] !== '') {
//        $dirPath = "/base/{$sysSession->school_id}/course/{$sysSession->course_id}/board/{$_POST['bid']}/{$_POST['mnode']}/";
//    }
//echo '<pre>';
//var_dump($_FILES["files"]["tmp_name"][0], $desPath . '/tmp/' . $_POST['tmp'] . '/' . $fileName);
//echo '</pre>';
//    $desPath = $_SERVER['DOCUMENT_ROOT'] . $dirPath;
    $desPath = '/tmp/' . md5($_COOKIE['idx']) . '/';
    $data = array();
    create_folders($desPath);
//    if (copy($_FILES["files"]["tmp_name"][0], $desPath . $fileName) === true) {
    if (move_uploaded_file($_FILES["files"]["tmp_name"][0], $desPath . $fileName) === true) {
        $data['files'][] = array(
            'original_name' => $path_parts['basename'],
            'name' => $fileName,
            'size' => $_FILES["files"]["size"][0],
            'type' => get_mime_type($desPath . $fileName),
            'url' => '',
            'deleteUrl' => '',
            'deleteType' => 'DELETE'
        );
    } else {
        $data['textStatus'] = 'fail';
    }

    $msg = json_encode($data);

    if ($msg != '') {
        echo $msg;
    }