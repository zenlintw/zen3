<?php
include_once(dirname(__FILE__).'/action.class.php');

class MediaNodeAction extends baseAction
{
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        
        global $sysConn, $sysSession;
        
        $categoryId = intval($_REQUEST['category_id']);
        chkSchoolId('APP_experience_url');

        // get data
        $sql  = 'SELECT caption, url ';
        $sql .= 'FROM APP_experience_url WHERE enable=\'1\' AND catalog_id=\''.$categoryId.'\' ORDER BY permute';
        $rows = $sysConn->Execute($sql);

        // make json
        $data = array();
        if ($rows) {
            while ($row = $rows->FetchRow()) {
                // 處理語系
                $cps = getCaption($row['caption']);

                // 建立資料
                array_push(
                    $data, 
                    array(
                        'identifier' => 'id_'.count($data),
                        'text' => $cps[$sysSession->lang],
                        'href' => $row['url'],
                        'leaf' => true,
                    )
                );
            }
        }
        
        // 取得分類名稱
        $sql  = 'SELECT caption FROM APP_experience_catalog WHERE enable=\'1\' AND catalog_id=\''.$categoryId.'\'';
        $rows = $sysConn->Execute($sql);
        $row = $rows->FetchRow();
        $cps = getCaption($row['caption']);
        
        $jsonObj = array(
            'identifier' => $categoryId,
            'text' => $cps[$sysSession->lang],
            'item' => $data,
        );

        $jsonEncode = JsonUtility::encode($jsonObj);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}