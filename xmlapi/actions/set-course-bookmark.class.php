<?php
/**
 * 設定我的最愛
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');

class SetCourseBookmarkAction extends baseAction
{
    var $username = null;
    
    /**
     * 將課程加到我的最愛中
     * @param string $courseId : 課程編號
     * @param string $username: 學員帳號
     * @return integer (6: xml 無法 parse; 2: mark success; 3: mark nothing)
     **/
    function markFavorite($courseId, $username)
    {
        $userDir = $this->getUserDir($username);
        $favoriteXML = $userDir . '/my_course_favorite.xml';

        if (!$xmlVars = domxml_open_file($favoriteXML)) {
            return 6;
        } else {
            // 判斷是否在XML中有資料
            $nodeCount = $this->checkCourseIdExist($xmlVars, $courseId);
            if ($nodeCount  ===  0) {
                $root = $xmlVars->document_element();
                $node = $xmlVars->create_element('course');
                $node->set_attribute('id', $courseId);
                $root->append_child($node);

                $xmlVars->dump_file($favoriteXML);

                return 2;
            }
        }
        return 3;
    }

    /**
     * 將課程移出我的最愛中
     * @param string $courseId : 課程編號
     * @param string $username: 學員帳號
     * @return integer (6: xml 無法 parse; 4: unmark success; 5: unmark nothing)
     **/
    function unmarkFavorite($courseId, $username)
    {
        $userDir = $this->getUserDir($username);
        $favoriteXML = $userDir.'/my_course_favorite.xml';

        if (!$xmlVars = domxml_open_file($favoriteXML)) {
            return 6;
        } else {
            // 判斷是否在XML中有資料
            $nodeCount = $this->checkCourseIdExist($xmlVars, $courseId);
            if ($nodeCount > 0) {
                $xpath = '/manifest/course[@id="' . $courseId . '"]';
                $ctx = xpath_new_context($xmlVars);
                $nodes = xpath_eval($ctx, $xpath);

                $source = $nodes->nodeset[0];
                $parent = $source->parent_node();
                $parent->remove_child($source);

                $xmlVars->dump_file($favoriteXML);

                return 4;
            }
        }
        return 5;
    }

    function checkCourseIdExist($xmlDocs, $courseId)
    {
        $ctx = xpath_new_context($xmlDocs);
        $xpath = '/manifest/course[@id="' . $courseId . '"]';
        $nodes = xpath_eval($ctx, $xpath);

        return count($nodes->nodeset);
    }

    /**
    *  getUserDir():取得取得學員的user目錄路徑
    *  @param $username : string 學員帳號
    *  @return : user directory path
    */
    function getUserDir($username)
    {
        $username = trim($username);
        // 取出前兩個字元
        $one = substr($username, 0, 1);
        $two = substr($username, 1, 1);

        $userDir = sysDocumentRoot . '/user/'. $one . '/' . $two . '/' . $username;
        return $userDir;
    }

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;

        $username = mysql_real_escape_string($sysSession->username);

        // 從網址取得參數
        $courseId = intval($_GET['cid']);
        if (isset($_GET['act'])) {
            $act = strtoupper(trim($_GET['act']));
        } else {
            $act = 'MARK';
        }

        $majorWhere = "course_id={$courseId} and username='{$username}'";
        $isMajor = dbGetOne('WM_term_major', 'count(*)', $majorWhere);

        if ($isMajor) {
            // 若是課程成員，才可進行mark | unmark
            switch ($act) {
                case 'UNMARK' :
                    $executeResult = $this->unmarkFavorite($courseId, $username);
                    break;
                case 'MARK' :
                default :
                $executeResult = $this->markFavorite($courseId, $username);
                    break;
            }

            if ( ($executeResult === 2) || ($executeResult === 4) ) {
                // 2:mark | 4:unmark；但只要成功，都改回傳0
                $executeResult = 0;
                $message = 'success';
            } else {
                // 6:xml read error | 3:mark nothing | 5:unmark nothing
                $message = 'fail';
            }
        } else {
            // 7: 非課程成員
            $executeResult = 7;
            $message = 'fail';
        }
        
        // make json
        $jsonObj = array(
            'code' => intval($executeResult),
            'message' => $message,
            'data' => array()
        );

        $jsonEncode = JsonUtility::encode($jsonObj);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}