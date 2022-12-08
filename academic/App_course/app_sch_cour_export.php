<?php
    /**
     * 匯出 - 登入次數統計 (APP 專用)
     **/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/archive_api.php');

    $sysSession->cur_func = '1500200100';
    $sysSession->restore();
    if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    $type_len        = count($_POST['type_kinds']);
    $store_result    = array();
    $sysCL           = array('Big5' => 'zh-tw', 'en' => 'en', 'GB2312' => 'zh-cn');
    $ACCEPT_LANGUAGE = $sysCL[$sysSession->lang];
    if (empty($ACCEPT_LANGUAGE)) $ACCEPT_LANGUAGE = 'zh-tw';

    $title_name = stripslashes($_POST['function_name']);
    for ($i = 0; $i < $type_len; $i++)
    {
        switch ($_POST['type_kinds'][$i])
        {
            case 'csv':
                $csv_result = str_replace(
                    array('<FONT color=red>', '</FONT>', '<br>', '<BR>'),
                    array('', '', "\r\n", "\r\n"),
                    stripslashes($_POST['csv_content'])
                );
                $store_result['csv'] = strip_tags($title_name . "\r\n" . $csv_result);
                break;

            case 'htm':
                $_POST['htm_content'] = stripslashes($_POST['htm_content']);
                $store_result['htm'] = <<< BOF
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
        <meta http-equiv="Content-Language" content="{$ACCEPT_LANGUAGE}" >
        <title>{$title_name}</title>
        <style type="text/css">
            .bg01 { background-color: #E3E9F2; }
            .bg02 { background-color: #CCCCE6; }
            .cssTrEvn { background-color: #FFFFFF; }
            .cssTrOdd { background-color: #EAEAF4; }
            .font01 { font-size: 12px; line-height: 16px; color: #000000; text-decoration: none; letter-spacing: 2px; }
            .cssTable { background-color: #E4E2F3; border: 1px solid #7070B8; }
        </style>
    </head>
    <body>
        <table id="stud_list" width="50%" border="0" cellspacing="1" cellpadding="3" id="delTable2" style="display:block" class="cssTable" >
            <tr class="bg02"><td colspan="6">{$title_name}</td></tr>
            {$_POST['htm_content']}
        </table>
    </body>
    </html>
BOF;
                break;

            case 'xml':
                $xml_result = str_replace(
                    array('<FONT color=red>', '<font color=#A0A0A0>', '<FONT color=#a0a0a0>', '</FONT>', '<BR>'),
                    array('', '', '', '', "\r\n"),
                    stripslashes($_POST['xml_content'])
                );
                $store_result['xml'] = '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n" .
                    '<manifest><function_name>' . $title_name . '</function_name>' .
                    $xml_result . '</manifest>';
                break;
        }
    }

    // 下載檔案的檔名
    $temp_file = preg_replace(
        array('/\.zip$/', '/[^\w@#$%^()=+.,-]+/', '/\.\.+/', '/^\.+/'),
        array('', '', '.', ''),
        stripslashes($_POST['download_name'])
    );
    $fname = $temp_file ? ($temp_file . '.zip') : 'app_login_stat.zip';

    //  夾檔 的檔名
    if (empty($_POST['adv_file']))
    {
        $attach_file_name = 'statistics';
    }
    else
    {
        $attach_file_name = preg_replace('/\W+/', '', stripslashes($_POST['adv_file']));
    }

    // 修正管理端-行動學習-登入次數統計-匯出功能沒有反應
    $export_obj = new ZipArchive_php4($fname);
    foreach ($store_result as $key => $val)
    {
        switch ($key)
        {
            case 'csv':
                $export_obj->add_string(utf8_to_excel_unicode($val), $attach_file_name . '.csv');
                break;

            case 'htm':
                $export_obj->add_string($val, $attach_file_name . '.htm');
                break;

            case 'xml':
                $export_obj->add_string($val, $attach_file_name . '.xml');
                break;
        }
    }

    header('Content-Disposition: attachment; filename="' . $fname . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Type: application/zip; name="' . $fname . '"');
    
    $export_obj->readfile();
    $export_obj->delete();
?>