<?php
/**
 * 取得節點資源下載列表
 */
include_once(dirname(__FILE__).'/action.class.php');

class CourseNodeResourcesAction extends baseAction
{

    /**
     * 透過scoId，取得相對應的resource id
     *
     * @param string $scoId: 節點ID
     **/
    function getScoIdRef($scoId)
    {
        global $ctx;

        $scoIdRefPath = "//manifest/organizations/organization//item[@identifier='{$scoId}']";
        $xrs = $ctx->xpath_eval($scoIdRefPath);
        foreach ($xrs->nodeset as $identifierref) {
            $identifierRefs[] = $identifierref->get_attribute('identifierref');
        }

        return $identifierRefs;
    }

    /**
     * 透過resource id，取得相對應的教材檔案
     *
     * @param array $socIdRefs: 教材所引用的檔案
     **/
    function getResourceHref($scoIdRefs, $courseId, $baseUrl, $relPath)
    {
        global $ctx, $sysSession;

        $resources = array();

        $comparePath1 = '/base/'.$sysSession->school_id.'/course/'.$courseId.'/content/';
        $comparePath2 = '/base/'.$sysSession->school_id.'/course/';
        $comparePath3 = '/base/'.$sysSession->school_id.'/door/';
        $comparePath4 = '/base/'.$sysSession->school_id.'/content/';

        for ($i=0; $i<count($scoIdRefs); $i++) {
            $scoIdRef = $scoIdRefs[$i];
            $resourcePath = "//manifest/resources/resource[@identifier='{$scoIdRef}']";
            $xrs = $ctx->xpath_eval($resourcePath);
            foreach ($xrs->nodeset as $href) {
                // 教材檔案
                $resourceHref = $href->get_attribute('href');
                // 教材檔案的所在目錄(不一定會有)
                $resourceBase = '';
                if ($href->get_attribute('base') !== '') {
                    $resourceBase = $href->get_attribute('base');
                } else if ($href->get_attribute('xml:base') !== '') {
                    $resourceBase = $href->get_attribute('xml:base');
                }

                $fileResource = $resourceBase . $resourceHref;

                if (($resourceBase == '') || ($resourceBase === $comparePath1)) {
                    // 如果xml中的xml:base是空值，或是xml:base與本門課目錄路徑相同
                    if (strstr($resourceHref, $comparePath3)) {
                        // href是學校的door下，直接使用$resourceHref
                        $relativePath = $resourceHref;
                        
                    } else {
                        // href不是學校的door下，回傳路徑只需要用$resourceHref去變更$relPath
                        $relativePath = sprintf($relPath, $courseId, $resourceHref);
                    }

                } else if (!strstr($resourceBase, $comparePath2)) {
                    // 如果xml:base 與 /base/10001/course 不類似的話
                    if (strstr($resourceBase, $comparePath3) || strstr($resourceBase, $comparePath4)) {
                        // 如果base是學校的door或是學校的教材庫，則回傳路徑用$resources
                        $relativePath = $resources;
                    } else {
                        // 不是則回傳路徑需要使用$resources去變更$relPath
                        $relativePath = sprintf($relPath, $courseId, $resources);
                    }
                } else {
                    // 如果xml:base 與 /base/10001/course 類似的話，則回傳路徑則直接使用xml:base與xml:href 組合
                    $relativePath = $fileResource;
                }

                $relativeURLPath = rawurlencode($relativePath);

                $data = array (
                    'url' => $baseUrl . str_replace('%2F', '/', $relativeURLPath),
                    'file' => $relativePath
                );

                $resources[] = $data;
            }
        }
        return $resources;
    }

    /**
     * 將網址轉成urlencode
     *
     * @param string $path: 網址
     **/
    function transUrlEncode($path)
    {
        global $ctx;

        $oldPath = explode('/', $path);
        for ($i=0; $i<count($oldPath); $i++) {
            $newPath[] = rawurlencode($oldPath[$i]);
        }
        $transPath = implode('/', $newPath);

        return $transPath;
    }
    
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        global $sysSession, $ctx;

        // 從網址取得參數
        $courseId = intval(trim($_GET['cid']));
        $scoId = trim($_GET['scoid']);


        // 教材路徑前置參數
        $downloadPathParent = WM_SERVER_HOST;
        $relativePathParent = '/base/'.$sysSession->school_id.'/course/%s/content/%s';
        
        if (eregi('^[0-9A-Z_.-]+$', $scoId)) {
            $field = '`content`, `update_time`';
            $table = '`WM_term_path`';
            $where = "`course_id` = {$courseId} AND LOCATE('{$scoId}', `content`) ".
                     'ORDER BY `serial` DESC';

            $content = dbGetOne($table, $field, $where);
        }

        if (!empty($content)) {
            $xmlDoc = domxml_open_mem($content);
            $ctx = xpath_new_context($xmlDoc);

            $scoIdRefs = $this->getScoIdRef($scoId);
            $resources = $this->getResourceHref($scoIdRefs, $courseId, $downloadPathParent, $relativePathParent);

            for ($i=0; $i<count($resources); $i++) {
                $filePath = $resources[$i]['file'];
                $fileHref = $resources[$i]['url'];

                if (WM_OS_FILE_ENCODING === 'Big5') {
                    // 因為檔案在OS底下是BIG5，故做轉碼的動作
                    $filePath = sysDocumentRoot . mb_convert_encoding($filePath, 'Big5', 'UTF-8');
                }

                // 清除檔案status cache，並重新取得檔案大小
                clearstatcache();
                $fileSize = intval(filesize(sysDocumentRoot . $filePath));
                $updateDatetime = intval(filemtime(sysDocumentRoot . $filePath));

                $data['download_path'] = $fileHref;
                $data['relative_path'] = $filePath;
                $data['update_datetime'] = $updateDatetime;
                $data['size'] = $fileSize;
                $data['metadata'] = '';

                $datas[] = $data;
            }

            $code = intval(0);
            $message = 'success';
        } else {
            // 如果沒有撈到content，則回傳失敗 (執行失敗)
            $code = intval(2);
            $message = 'fail';
        }

        // make json
        $jsonObj = array(
            'code' => $code,
            'message' => $message,
            'data' => array(
                'list' => $datas,
            ),
        );

        $jsonEncode = JsonUtility::encode($jsonObj);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}