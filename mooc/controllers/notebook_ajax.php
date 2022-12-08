<?php
    /*
    * 邏輯層：功能處理
    * 接收中介層參數經處理後，傳回中介層
    *
    * @since   2015/4/27
    * @author  cch
    */
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/controllers/JSON.php');
    require_once(sysDocumentRoot . '/mooc/models/notebook.php');
   
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

    global $sysSession;

    switch($_POST['action']) {

        /*
         * 取得筆記本
         *
         * @return array $arr:
         */
        case "getRecentNotebook":
            
            if (isset($_POST['fid']) === false) {
                $data = "parameter error!!";
            } else {
                // 取得筆記本:
                $rs = new notebook();
                $data = $rs->getNotebookDtl(
                    $_POST['fid'],
                    $_POST['id']
                );
            }

            $msg = json_encode($data);
            break;
        
        case "getNotebookTitle":
            
            if (isset($_POST['fid']) === false) {
                $data = "parameter error!!";
            } else {
                // 取得筆記本:
                $rs = new notebook();
                $data = $rs->getNotebookTitleByFid($_POST['fid']);
            }

            $msg = json_encode($data);
            break;
        
        case "setNotebookLastRead":
            
            if (isset($_POST['fid']) === false || isset($_POST['id']) === false) {
                $data = "parameter error!!";
            } else {
                // 取得筆記本:
                $rs = new notebook();
                $data = $rs->setNotebookLastRead($_POST['fid'], $_POST['id']);
            }

            $msg = json_encode($data);
            break;
        
        case "searchNotebooks":
            
            if (isset($_POST['keyword']) === false) {
                $data = "parameter error!!";
            } else {
                // 取得筆記本:
                $rs = new notebook();
                $data = $rs->searchNotebooks($_POST['keyword']);
            }

            $msg = json_encode($data);
            break;
        
        default:
            $val = "action error!!";
            $msg = json_encode($val);
            break;
    }

    if ($msg != '') {
        echo $msg;
    }