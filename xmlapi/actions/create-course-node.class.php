<?php
/**
 * 接收到教材檔案後，設定成教材節點
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');

class CreateCourseNodeAction extends baseAction
{
    var $username = null;

    /**
     * 新增課程節點
     *
     * @param {Number} $courseId 課程ID
     * @param {String} $title 教材節點的名稱
     * @param {String} $attachPath 教材路徑
     * @param {String} $username 編輯節點的帳號
     *
     * @return 處理結果 InsertDBFail(新增失敗) | InsertDBSuccess(新增成功) | NewFormatError(新教材路徑錯誤) | OriginalFormatError(原教材路徑錯誤)
     **/
    function addCoursePathNode ($courseId, $title, $filename, $username) {
        global $sysConn;
        // 預設只保留50個課程節點路徑(參考/teach/course/cour_path_save.php)
        $pathLimit = 50;

        list($pathSerial, $pathContent) = dbGetStSr('WM_term_path', 'serial, content', "course_id={$courseId} ORDER by serial DESC LIMIT 1", ADODB_FETCH_NUM);

        if ($pathSerial === 0 || strlen($pathContent) === 0) {
            $pathContent = '<?xml version="1.0"?><manifest xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1.3" xsi:schemaLocation="http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd                            http://www.imsglobal.org/xsd/imsmd_rootv1p2p1 imsmd_rootv1p2p1.xsd                            http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd"><organizations default="Course'.$sysSession->course_id.'"><organization identifier="Course'.$sysSession->course_id.'"><title>'.$sysSession->course_name.'</title></organization></organizations><resources></resources></manifest>';
        }

        if (domxml_open_mem($pathContent)) {
            $newSCOId = 'SCO_' . $courseId . '_' . time();

            // 繁體、簡體、英文、日文、自定(預設前三個要填入標題)
            $arrayTitle = array ();
            for ($i = 0; $i < 5; $i++) {
                if ($i < 3) {
                    $arrayTitle[] = htmlspecialchars($title, ENT_QUOTES);
                } else {
                    $arrayTitle[] = '';
                }
            }
            $xmlTitle = implode('	', $arrayTitle);

            // 製作item與resource
            $contentNode = '<item identifier="I_' . $newSCOId . '" identifierref="' . $newSCOId . '"><title>' . $xmlTitle . '</title></item>';
            $contentRef = '<resource identifier="' . $newSCOId . '" type="webcontent" href="' . $filename . '"><file href="' . $filename . '"/></resource>';

            // 轉成UTF-8
            $contentNode = mb_convert_encoding($contentNode, 'UTF-8', 'Big5');
            $contentRef = mb_convert_encoding($contentRef, 'UTF-8', 'Big5');

            // 搜尋並加入新item取代
            $newPathContent = str_replace("</organization>", $contentNode . "</organization>", $pathContent);

            // 搜尋並加入新resource取代
            $arrayRefFind = array('</resources>', '<resources/>');
            $arrayRefReplace = array($contentRef . '</resources>', '<resources>' . $contentRef . '</resources>');
            $newPathContent = str_replace($arrayRefFind, $arrayRefReplace, $newPathContent);

            if (domxml_open_mem($newPathContent)) {
                // 新 xml 格式無誤
                $sysConn->Execute("insert into WM_term_path (course_id,serial,content, username, update_time) ".
                                  "select {$courseId},if(max(serial) IS NULL,1,max(serial)+1),'{$newPathContent}','{$username}', now() from WM_term_path where course_id={$courseId} limit 1");
                if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0){
                    // 新增失敗
                    $result = array('insert db fail', '');
                } else {
                    // 新增成功
                    $result = array('insert db success', $newSCOId);
                    // 如果最新的路徑編號已經到50以上，則進行移除的動作
                    if ($pathSerial >= 50) {
                        $this->pathSerialSlip($courseId, $pathLimit);
                    }
                }
            } else {
                // 新 xml 格式錯誤
                $result = array('new format error', '');
            }
        } else {
            // 原先 xml 格式錯誤
            $result = array('original format error', '');
        }

        return $result;
    }

    /**
     * 遞移課程教材節點備份序號
     *
     * @param {Number} $courseId 課程編號
     * @param {Number} $pathLimit
     **/
    function pathSerialSlip ($courseId, $pathLimit) {
        global $sysConn;

        $pathAmount = $sysConn->GetCol("SELECT serial FROM WM_term_path WHERE course_id={$courseId} ORDER BY serial DESC");

        if (count($pathAmount) > $pathLimit) {
            // 刪除以前的
            dbDel('WM_term_path', 'course_id=' . $courseId . ' AND serial IN (' . implode(',', array_slice($pathAmount, $pathLimit)) . ')');
            // 更改最近的
            for($i = $pathLimit - 1; $i >= 0; $i--) {
                dbSet('WM_term_path', 'serial=' . ($pathLimit - $i), 'course_id=' . $courseId . ' AND serial=' . $pathAmount[$i]);
            }
        }
    }

    function main()
    {
        global $sysRoles;
        $scoId = '';

        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;
        
        $username = trim($sysSession->username);
        $schoolId = trim($sysSession->school_id);

        // 處理接收的資料 - Begin
        $courseId = intval(trim($_GET['cid']));
        $courseContentPath = sysDocumentRoot . '/base/' . $schoolId . '/course/' . $courseId .'/content/';
        // 節點標題
        $title = trim($_GET['title']);

        // 確認課程是否存在且未被刪除(9)或關閉(0)
        $courseExist = dbGetOne('WM_term_course', '*', "`course_id` = $courseId AND `status` NOT IN (0, 9)");
        if ($courseExist === 0) {
            $message = 'Course Not Exist.';
            $code = 3;
        } else {
            // 確認是否為教師、助教、講師
            $role = dbGetOne('WM_term_major','role',"`course_id`={$courseId} AND `username`='{$username}'");
            $permission = $role & ($sysRoles['teacher']|$sysRoles['assistant']|$sysRoles['instructor']);
            if ($permission) {
                // 是否有教材資訊：檔名、內容
                if (!isset($_FILES['uploadFile']) || !is_uploaded_file($_FILES['uploadFile']['tmp_name'])) {
                    $message = 'No material data.';
                    $code = 4;
                } else {
                    if (!is_dir($courseContentPath)) {
                        $message = 'No Course Directory';
                        $code = 5;
                    } else {
                        // 檔名增加時間戳記與標註來自APP
                        $filename = date('YmdHis', time()) . '_APP_' . correctFilename($_FILES['uploadFile']['name']);
                        $attachPath = $courseContentPath . $filename;

                        if (move_uploaded_file($_FILES['uploadFile']['tmp_name'], $attachPath)) {
                            // 儲存檔案成功，又如果標題不為空的話，要增加節點
                            if (!empty($title)) {
                                $contentHandleResult = $this->addCoursePathNode($courseId, $title, $filename, $username);

                                $message = $contentHandleResult[0];
                                $scoId = $contentHandleResult[1];
                                if ($scoId === '') {
                                    $code = 6;
                                } else {
                                    $code = 0;
                                }
                            } else {
                                $message = 'upload success';
                                $code = 0;
                            }
                        } else {
                            // 儲存失敗
                            $message = 'save file fail';
                            $code = 7;
                        }
                    }
                }

            } else {
                $message = 'permission denied';
                $code = 2;
            }
        }
        
        /**
         * make json
         *
         * code: 0(success) | 2(非教師、助教、講師) | 3(課程不存在) | 4(沒有收到檔案資料) | 5(沒有課程目錄) | 6(新增節點失敗) | 7(存檔失敗)
         **/
        $jsonObj = array(
            'code' => $code,
            'message' => $message,
            'data' => array(
                'sco_id' => $scoId
            )
        );

        $jsonEncode = JsonUtility::encode($jsonObj);
        
        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}