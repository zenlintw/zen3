<?php
include_once(dirname(__FILE__).'/action.class.php');

class MediaCategoryAction extends baseAction
{
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        
        global $sysConn, $sysSession;
        
        chkSchoolId('APP_experience_catalog');

        // get data
        $sql  = 'SELECT catalog_id, caption, description, cover ';
        $sql .= 'FROM APP_experience_catalog WHERE enable=\'1\' ORDER BY permute';
        $rows = $sysConn->Execute($sql);
        $data = array();

        // make json
        if ($rows) {
            while ($row = $rows->FetchRow()) {
                // 處理語系
                $cps = getCaption($row['caption']);
                
                // 轉換圖片位置
                $imgUrl = sprintf(
                    'http://%s:%d/base/%05d/door/APP/wmmedia/cover/%s', 
                    $_SERVER['SERVER_NAME'],
                    $_SERVER['SERVER_PORT'],
                    $sysSession->school_id,
                    $row['cover']
                );

                // 建立資料
                array_push(
                    $data, 
                    array(
                        'category_id' => $row['catalog_id'],
                        'category_name' => $cps[$sysSession->lang],
                        'img_url' => $imgUrl,
                        'category_desc' => $row['description'],
                    )
                );
            }
        }
        
        $jsonObj = array(
            'code' => 0,
            'message' => 'success',
            'data' => array(
                'data' => $data,
            ),
        );

        $jsonEncode = JsonUtility::encode($jsonObj);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}