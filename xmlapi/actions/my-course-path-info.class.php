<?php
/**
 * 取得課程學習路徑
 */
// 設定環境供 acl 判斷
define('QTI_env', 'learn');
include_once(dirname(__FILE__).'/action.class.php');
include_once(dirname(__FILE__).'/my-course-history.class.php');
include_once(sysDocumentRoot . '/lib/common.php');
include_once(sysDocumentRoot . '/lib/course.php');
include_once(sysDocumentRoot . '/lang/learn_path.php');
include_once(sysDocumentRoot . '/learn/path/qti_lib.php');
require_once(sysDocumentRoot . '/lib/lib_lcms.php');
require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');

set_time_limit(0);
ignore_user_abort(true);

class MyCoursePathInfoAction extends baseAction
{
    var $_username = null;
    var $_jsonPath = null;
    var $_progress = array();
    var $_lcmsCoursesPath = array();
    var $_lcmsUnitsPath = array();
    var $_lcmsAssetsPath = array();
    var $_resourceNodes = null;
    var $_LCMSVerifyData = array();

    /**
     * 遞迴找出所有節點的資料與建置json結構
     *
     * @param array $itemNodes: 學習路徑的NODE
     * @param int  $courseId 課程ID
     * @param string $baseUrl 平台網址(http://...)
     * @param string $relPath 相對路徑(base/...)
     * @return array 學習路徑的節點資料與結構
     **/
    function getItem($itemNodes, $courseId, $baseUrl, $relPath)
    {
        global $sysSession;

        $xmlLang = array('Big5' => 0, 'GB2312' => 1, 'en' => 2);

        if (!array_key_exists($sysSession->lang, $xmlLang)) {
            $lang = 0;
        } else {
            $lang = $xmlLang[$sysSession->lang];
        }

        $items = array();
        if (!empty($itemNodes)) {
            foreach ($itemNodes as $singleItem) {
                // 過濾非 item 的 Tag (EX. title)
                if ($singleItem->tagname !== "item") {
                    continue;
                }   

                // 判斷節點是否隱藏(false:隱藏，true:未隱藏)
                $thisItemVisible = $this->checkVisible($singleItem->get_attribute('isvisible'));

                // 未隱藏"才有輸出成json的必要
                if ($thisItemVisible === true) {
                    // 取得子節點
                    $childNodes = $singleItem->child_nodes();

                    // 節點 SCOID
                    $item['identifier'] = $singleItem->get_attribute('identifier');

                    // 節點名稱
                    $title = "";
                    foreach ($childNodes as $value) {
                        if ($value->tagname === "title") {
                            $contentLang = explode("\t", $value->get_content());
                            $titleContent = (empty($contentLang[$lang])  || $contentLang[$lang] === 'undefined') ? $contentLang[0] : $contentLang[$lang];
                            // 將&lt;轉成<，&gt;轉成>
                            $title = htmlspecialchars_decode($titleContent);
                            break;
                        }
                    }

                    // 濾掉<font></font>的tag，function寫在common.php
                    $title = nodeTitleStrip($title);
                    // 濾掉<p>等特殊tag
                    $item['text'] = strip_tags($title);

                    // 節點教材
                    $itemHref = $singleItem->get_attribute('identifierref');
                    $item['href'] = $this->getResourceHref($itemHref, $courseId, $baseUrl, $relPath);

                    // 節點可用狀態
                    $item['itemDisabled'] = $this->checkDisabled($singleItem->get_attribute('disabled'));

                    // 如果是 qti 節點，將能不能作答的判斷補回 itemDisabled
                    if (preg_match("/\bfetchWMinstance,(exam|questionnaire|homework),(-?\d+)/", $item['href'], $regs)) {
                        if (intval($regs[2]) < 0) {
                            $item['itemDisabled'] = true;
                        }
                    }

                    // 閱讀狀態:1:已讀取,0:未讀取
                    $item['readed'] = $this->checkReaded($item['identifier']);

                    // 找尋下一層節點
                    $item['item'] = $this->getItem(
                        $childNodes,
                        $courseId,
                        $baseUrl,
                        $relPath
                    );

                    // 子節點狀態，確定是否為葉子（沒有子節點）
                    if (!is_array($item['item']) || count($item['item']) === 0) {
                        $item['leaf'] = true;
                    } else {
                        $item['leaf'] = false;
                    }

                    $items[] = $item;
                }
            }
        }
        return $items;
    }

    /**
     * 取得節點的名稱
     *
     * @param string $node: 節點路徑
     * @param string $attribute: 預設content
     * @return string 節點名稱
     **/
    function getTitle($node, $attribute='content')
    {
        foreach ($node->nodeset as $content) {
            $title = $content->{$attribute};
        }
        return $title;
    }

    /**
     * 透過scoId，取得相對應的resource id
     *
     * @param string $scoId: 節點ID
     * @param string $xmlpathContext: 學習路徑的xpath_new_context
     * @return string 節點教材的ID
     **/
    function getScoIdRef($scoId, $xmlXpathContext)
    {
        $scoIdRefPath = "//manifest/organizations/organization//item[@identifier='{$scoId}']";
        $xrs = $xmlXpathContext->xpath_eval($scoIdRefPath);
        foreach ($xrs->nodeset as $identifierref) {
            $identifierRefs = $identifierref->get_attribute('identifierref');
        }

        return $identifierRefs;
    }

    /**
     * 透過resource id，取得相對應的教材檔案
     *
     * @param string  $scoIdRef: 節點教材的ID
     * @param string  $xmlpathContext: 學習路徑的xpath_new_context
     * @param integer $courseId: 課程ID
     * @param string  $baseUrl: 教材節點的base url (例如http://192.168.10.155:5004)
     * @param string $relPath: 教材節點路徑(從base開始，例如base/10001/course/10000006/content/)
     * @return string 教材檔案(包含路徑)
     **/
    function getResourceHref($scoIdRef, $courseId, $baseUrl, $relPath)
    {
        global $sysSession;
        $comparePath1 = '/base/'.$sysSession->school_id.'/course/'.$courseId.'/content/';
        $comparePath2 = '/base/'.$sysSession->school_id.'/course/';
        $comparePath3 = '/base/'.$sysSession->school_id.'/door/';
        $comparePath4 = '/base/'.$sysSession->school_id.'/content/';
        $qtiFlag = 'fetchWMinstance';
        $subjectFlag = 'subjectFetchWMinstance';

        $href = $this->_resourceNodes[$scoIdRef];

        $downloadPath = 'about:blank';
        if (isset($href)) {
            // 教材檔案
            $resourceHref = $href->get_attribute('href');
            // 修正教材路徑為空白時，get_attribute('href') 會取到 NULL或空值
            $resourceHref = ($resourceHref !== NULL && $resourceHref !== "") ? $resourceHref : "about:blank";
            // 教材檔案的所在目錄(不一定會有)
            $resourceBase = '';
            // 教材檔案類型
            $resourceType = $href->get_attribute('type');

            if ($href->get_attribute('base') !== '') {
                $resourceBase = $href->get_attribute('base');
            } else if ($href->get_attribute('xml:base') !== '') {
                $resourceBase = $href->get_attribute('xml:base');
            }

            $resources = $resourceBase . $resourceHref;
            if (preg_match("/\bfetchWMinstance\((\d+),'?(\w+)'?\)/", $resourceHref, $regs)) {
                // 問卷測驗判斷 From SCORM_fetchResource.php
                switch(intval($regs[1])) {
                    case 2:
                    case 3:
                    case 4:
                        $type = $regs[1] == '2' ? 'homework' : ($regs[1] == '3' ? 'exam' : 'questionnaire');
                        $eid = intval($regs[2]);
                        // TODO: 網頁版有修改公開型問卷的話，就可以將判斷拿掉
                        // 取得 QTI owner
                        list($unitId, $qtiPublish) = dbGetStSr("WM_qti_".$type."_test",
                            "`course_id`, `publish`",
                            sprintf("exam_id = %d", $eid));
                        if ($type === 'questionnaire' && aclCheckWhetherForGuestQuest($unitId, $eid)) {
                            // 公開型問卷
                            $canDo = ($qtiPublish === 'action')? 1 : -2;
                        } else {
                            $canDo = check_qti_can_do($type, $eid);
                        }

                        // 回傳格視為 "fetchWMinstance","QTI 類型","QTI_ID"(or 錯誤訊息編號)
                        if ($canDo < 0) {
                            $downloadPath = implode(",", array($qtiFlag, $type, $canDo));
                        } else {
                            $downloadPath = implode(",", array($qtiFlag, $type, $eid));
                        }
                        break;
                    case 5:
                        $subjectNodeId = intval($regs[2]);
                        $courseId = intval($courseId);
                        $table = '`WM_term_subject` AS TS LEFT JOIN `WM_bbs_boards` AS BB ON TS.`board_id` = BB.`board_id`';
                        $fields = 'BB.*, TS.`state`';
                        $where = "TS.`course_id` = {$courseId} AND TS.`node_id` = {$subjectNodeId}";
                        $RS = dbGetStSr($table, $fields, $where, ADODB_FETCH_ASSOC);
                        $bulletinId = dbGetOne('`WM_term_course`', '`bulletin`', "`course_id` = {$courseId}");
                        if ($RS) {
                            // 如果不是公告板，則可以在APP上透過節點點擊後切換功能
                            if (intval($bulletinId) !== intval($RS['board_id'])) {
                                $canRead = ChkBoardReadRight($RS['board_id']) ? 'Y' : 'N';
                                $boardId = intval($RS['board_id']);
                                $title = $RS['title'];
                                $boardName = getCaption($RS['bname']);
                                $subjectName= $boardName['Big5'];
                                $status = boardStatus($RS['open_time'], $RS['close_time'], $RS['share_time']);
                                $state = strtoupper(($RS['state'] === '') ? 'open' : trim($RS['state']));
                                $boardShareTime = (!empty($RS['share_time']) && $RS['share_time'] !== '0000-00-00 00:00:00') ? $RS['share_time'] : '';
                                $isBulletin = 0;
                                $isManager = intval(checkBoardManager($sysSession->username, $courseId, $boardId));
                                $downloadPath = implode('#sjt#', array($subjectFlag, $boardId, $subjectName, $title, $status, $canRead, $boardShareTime, $state, $isBulletin, $isManager));
                            }
                        }
                        break;
                    default:
                        // 其餘功能暫不納入web service (議題討論:5、討論版:6、討論室:7)
                }
            } else if (!(strstr($resources, '://')) && ($resourceHref !== 'about:blank')) {
                // 如果href不是http://、https://、mms://...之類的，就要組成wmpro的路徑
                if (($resourceBase == '') || ($resourceBase === $comparePath1)) {
                    // 如果xml中的xml:base是空值，或是xml:base與本門課目錄路徑相同
                    if (strstr($resourceHref, $comparePath3) || strstr($resourceHref, $comparePath4)) {
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
                    $relativePath = $resources;
                }
                // 回傳路徑加上http://平台網址
                $downloadPath = $baseUrl . $relativePath;
            } else {
                $downloadPath = $resources;
                if (sysLcmsHost !== '') {
                    // 判斷是否為 LCMS 教材
                    $isLcms = ($resourceType === 'lcms') || (@strpos(strtolower($resources), strtolower(sysLcmsHost)) === 0);
                    // 是否有啟用 LCMS
                    if (defined('sysLcmsEnable') && sysLcmsEnable && $isLcms) {
                        // 課程路徑
                        $downloadPath = $baseUrl . '/learn/path/lcms.php?action=app&rid=' . $scoIdRef;
                        // TODO: LCMS 課程收集及轉換檔案位址
                        if (preg_match('/.*\/courses\/play\/(\d*)/', $resources, $match) === 1) {
//                            $downloadPath = $baseUrl . '/learn/path/lcms.php?action=app&rid=' . $scoIdRef;
                            $this->_lcmsCoursesPath[] = $match[1];
                            $downloadPath = "LCMS,course," . $match[1] . "," . $downloadPath;
                        }
                        // 收集單元路徑
                        if (preg_match('/.*\/unit\/view\/(\d*)/', $resources, $match) === 1) {
                            $this->_lcmsUnitsPath[] = $match[1];
                            $downloadPath = "LCMS,unit," . $match[1] . "," . $downloadPath;
                        }
                        // 收集素材路徑
                        if (preg_match('/.*\/asset\/detail\/(\d*)/', $resources, $match) === 1) {
                            $this->_lcmsAssetsPath[] = $match[1];
                            $downloadPath = "LCMS,asset," . $match[1] . "," . $downloadPath;
                        }
                    }
                }
            }
        }
        return $downloadPath;
    }

    /**
     * 檢查節點是否可用
     *
     * @param string $disabled: 節點的disabled狀態值
     * @return boolean 0:enable, 1:disabled
     **/
    function checkDisabled($disabled)
    {
        if (!empty($disabled)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 檢查節點是否隱藏
     *
     * @param string $disabled: 節點的isvisible狀態值
     * @return boolean 0:unvisible, 1:visible
     **/
    function checkVisible($visible)
    {
        if ($visible === 'false') {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 檢查節點是否閱讀過
     *
     * @param string $activityId: 教材節點ID
     * @param array $recordReading: 教材閱讀紀錄
     * @return boolean false:未讀, true:已讀
     **/
    function checkReaded($activityId, $recordReading = array())
    {
        // LCMS 的閱讀紀錄會從參數傳入
        if (count($recordReading) === 0) {
            $recordReading = $this->_progress['recordReading'];
        }
        // 沒有已讀的節點資料，直接回傳false
        if (count($recordReading) === 0) {
            return false;
        }

        if (in_array($activityId, $recordReading)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 取得 LCMS 單元底下素材的閱讀紀錄
     * @param $cid
     * @param $user
     * @param $item
     * @return array
     */
    function getLcmsUnitRecord($cid, $user, $item) {
        $table = '`WM_record_reading`';
        $fields = 'activity_id';
        $where = 'course_id="' . $cid . '" and username="' . $user . '" '.
            'and locate("'.$item.'_", activity_id) group by activity_id';
        $rs = dbGetStMr($table, $fields, $where);
        $recordReading = array();
        if ($rs) {
            while ($row = $rs->FetchRow()) {
                $recordReading[] = $row['activity_id'];
            }
        }
        return $recordReading;
    }

    /**
     * 到 LCMS 站台取得單元底下素材的實體檔案路徑
     * @param $resources
     */
    function getLCMSCoursePath($username, $resources) {
        $json = getReomteData(
            sysLcmsHost . '/lms/getCourseInfo/multiple', array(
            'username' => $username,
            'courses' => $resources
        ));
        $courses = json_decode($json, true);
        return $courses;
    }
    /**
     * 到 LCMS 站台取得單元底下素材的實體檔案路徑
     * @param array $resources LCMS單元編號
     * @return mixed
     */
    function getLCMSUnitPath($resources) {
        // 取素材列表
        $json = getReomteData(
            sysLcmsHost . '/lms/getUnitInfo/multiple', array(
            'username' => $this->_username,
            'units' => $resources
        ));

        $units = json_decode($json, true);
        return $units;

        // 正規寫法，但取參數於第一階段加密函數時失敗
//        return 'http://lcms-cch.sun.net.tw/asset/play/3421/?token=3f1df1b08e3766f9cd354e7e5381ab4bd97de0c7&idx=0iufvKpw0I01BrbQ5fF58VTpfYkbuC7u&data=LwI7SGx4BvjaTzkZSsz3TARQFOF715ERliD3_Nthf0XLg34kXn7cnxiJ9lB4fUdNK36iPtVArqF3t-Yvdie9vaevGFotU_vEXxunTDEi8oTpZ_Vv9dTGorB2yjHXNXmB0aVxPFWfY1Q28KR-N8yaEXnmTNChG46_PbB6H3Srw-07AjPflIQZm9iDZeNiCUP8ZMhZWBWn3bZjrgFB5vw7GaIfKnMrFxS3pOQ4uWQo0BponGTJEMcmWRZqj6I5opmQJDUhz-lAQBrgms-W39d4wneKp-_7zHCp773dsbcU1OuiJKtzELp0UrKEoDdFYWPX3auHrnAs6ontRlBnHjSIRSyUFLxKnWnz-pNEMsqNmpT-gyMX6BuFQ13gPtqFMmeRnis7Vob2ClDmj6G5pCLs6M_aejE-E9iPkBMCcv-iL-EOfU325UJkKjlUET4fIVoIVyMKeHJuyibgqqP1JeZGRCekjcXk0_JCA3lLkQsClQ628x_x-wjmpTb4w0gHIaWCEDM2ZzokXWjUUHlmWsRhfzkepAxvPNS5bxDWLfmN254I7pUUdpkKErdVGMIL150x35eBuX5iP_5oWa49twK-fNaw6SkpWJSjneekGtmhhoLF5RrvkNVxGMW0eebxsCjSCKIuztaoNfv0i6lCFmOFbxBLWf_8912rhpP1MH7f8SioUwLTZKCp1I_pKnc2I-qogG7lB-6b75_UryHWA82FPCSvMosXtDOepWvu6uOi96fouAvIJZWlXooOLY0mJNAix9fM6gcDz86vYySaByxDACMGO-xd3UayVAlyBX6nzWbIk7L8kZuS6wPfI-KFQSPxhpRWFV6qsD52SLm1r5XzYw,,/';
    }
    /**
     * 到 LCMS 站台取得素材的實體檔案路徑
     * @param  array $resources LCMS素材編號
     * @return array
     */
    function getLCMSAssetPath($resources) {
        // LCMS教材給予自動轉址的網址
        $lcmsHost     = sysLcmsHost . (substr(sysLcmsHost, -1) === '/' ? '' : '/');
        
        // 取副檔名
        $json = getReomteData(sysLcmsHost . '/lms/getAssetInfo/multiple', array(
            'username' => $this->_username,
            'assets' => $resources
        ));
        $assets = json_decode($json);

        $downloadPath = array();
        foreach ($assets as $v) {
            $ext         = pathinfo($v->disk_filename, PATHINFO_EXTENSION);
            // 目前不判斷權限，先設定為1
            $v->read = 1;
            /* if (isset($v->disk_filename) === true && (in_array(strtolower($ext), array('mp4', 'mp3')))) {
            // 如果 vlc 無法撥放 mp3 mp4 則使用 web view 開啟
            $downloadPath = $lcmsHost . 'asset/play/' . $match[1] . '/file?read=' . $v->read . '&filetype=lcms.' . "html";
            } else */
            
            if (isset($v->disk_filename) === true && (in_array(strtolower($ext), array(
                'html',
                'htm',
                'pdf',
                'doc',
                'docx',
                'ppt',
                'pptx',
                'pps',
                'ppsx',
                'xls',
                'xlsx',
                'mov',
                'svg',
                'mp3',
                'wav',
                'jpg',
                'jpeg',
                'png',
                'bmp',
                'gif',
                'mp4',
                'txt',
                '3gp',
                'asp',
                'jsp',
                'php'
            )))) {
                $downloadPath[$v->aid] = array(
                    "href" => $lcmsHost . 'asset/play/' . $v->aid . '/file?read=' . $v->read . '&filetype=lcms.' . $ext,
                    "ivq" => $v->ivq
                );
            } else {
                $downloadPath[$v->aid] = array(
                    "href" => $lcmsHost . 'asset/play/' . $v->aid . '/file?read=' . $v->read . '&filetype=none.' . (empty($ext) === false ? $ext : 'none'),
                    "ivq" => $v->ivq
                );
            }
        }
        
        return $downloadPath;

        // 正規寫法，但取參數於第一階段加密函數時失敗
//        return 'http://lcms-cch.sun.net.tw/asset/play/3421/?token=3f1df1b08e3766f9cd354e7e5381ab4bd97de0c7&idx=0iufvKpw0I01BrbQ5fF58VTpfYkbuC7u&data=LwI7SGx4BvjaTzkZSsz3TARQFOF715ERliD3_Nthf0XLg34kXn7cnxiJ9lB4fUdNK36iPtVArqF3t-Yvdie9vaevGFotU_vEXxunTDEi8oTpZ_Vv9dTGorB2yjHXNXmB0aVxPFWfY1Q28KR-N8yaEXnmTNChG46_PbB6H3Srw-07AjPflIQZm9iDZeNiCUP8ZMhZWBWn3bZjrgFB5vw7GaIfKnMrFxS3pOQ4uWQo0BponGTJEMcmWRZqj6I5opmQJDUhz-lAQBrgms-W39d4wneKp-_7zHCp773dsbcU1OuiJKtzELp0UrKEoDdFYWPX3auHrnAs6ontRlBnHjSIRSyUFLxKnWnz-pNEMsqNmpT-gyMX6BuFQ13gPtqFMmeRnis7Vob2ClDmj6G5pCLs6M_aejE-E9iPkBMCcv-iL-EOfU325UJkKjlUET4fIVoIVyMKeHJuyibgqqP1JeZGRCekjcXk0_JCA3lLkQsClQ628x_x-wjmpTb4w0gHIaWCEDM2ZzokXWjUUHlmWsRhfzkepAxvPNS5bxDWLfmN254I7pUUdpkKErdVGMIL150x35eBuX5iP_5oWa49twK-fNaw6SkpWJSjneekGtmhhoLF5RrvkNVxGMW0eebxsCjSCKIuztaoNfv0i6lCFmOFbxBLWf_8912rhpP1MH7f8SioUwLTZKCp1I_pKnc2I-qogG7lB-6b75_UryHWA82FPCSvMosXtDOepWvu6uOi96fouAvIJZWlXooOLY0mJNAix9fM6gcDz86vYySaByxDACMGO-xd3UayVAlyBX6nzWbIk7L8kZuS6wPfI-KFQSPxhpRWFV6qsD52SLm1r5XzYw,,/';
    }

    /**
     * 替換 LCMS 教材路徑為實體檔案路徑
     * @param $items array 節點陣列
     * @param $unitPath array 單元實體路徑
     * @param $assetPath array 素材實體路徑
     */
    function replaceLCMSHref($items, $courseId, $coursePath, $unitPath, $assetPath) {
        global $sysSession;
        // LCMS教材給予自動轉址的網址
        $lcmsHost = sysLcmsHost . (substr(sysLcmsHost, -1) === '/' ? '' : '/');

        foreach($items AS $k => $v) {
            // 遞迴
            if (count($v['item']) > 0) {
                $v['item'] = $this->replaceLCMSHref($v['item'], $courseId, $coursePath, $unitPath, $assetPath);
            }
            $href = explode(",", $v['href']);
            // 判斷是否為 LCMS 教材($href 格式 LCMS,{unit|asset},{LCMS編號},{原 PRO 透通 LCMS 網址}
            if ($href[0] === "LCMS") {
                if ($href[1] === 'course') {
                    $courseData = $coursePath[$href[2]];
                    if (isset($courseData)) {
                        $unitItems = array();
                        foreach ($courseData AS $unitId => $unitData) {
                            // 課程須先建立單元
                            $lcmsUnit = array(
                                'identifier' => $v['identifier'] . '_unit_' . $unitId,
                                'text' => $unitData['unitname'],
                                'href' => "about:blank",
                                'itemDisabled' => false,
                                'readed' => $v['readed'],
                                'item' => $this->createAssetItems($this->_username, $courseId, $v, $unitData["asset"]),
                                'leaf' => count($unitData["asset"]) === 0
                            );
                            array_push(
                                $unitItems,
                                $lcmsUnit
                            );
                        }
                        $v['item'] = array_merge(
                            $unitItems,
                            $v['item']
                        );
                        $v['href'] = "about:blank";
                        $v['leaf'] = count($v['item']) === 0;
                    } else {
                        // 沒有可置換的路徑時，使用原本 PRO 的 LCMS 導向網址
                        $v['href'] = $href[3];
                    }
                } else if ($href[1] === 'unit') {
                    // 單元需將素材建立到當前子節點下
                    $unitData = $unitPath[$href[2]];
                    if (isset($unitData)) {
                        $assetItems = $this->createAssetItems($this->_username, $courseId, $v, $unitData);
                        $v['item'] = array_merge(
                            $assetItems,
                            $v['item']
                        );
                        $v['href'] = "about:blank";
                        $v['leaf'] = count($v['item']) === 0;
                    } else {
                        // 沒有可置換的路徑時，使用原本 PRO 的 LCMS 導向網址
                        $v['href'] = $href[3];
                    }
                } else if ($href[1] === 'asset') {
                    $assetData = $assetPath[$href[2]];
                    if (isset($assetData)) {
                        // mp4 要使用 ivq 所以仍使用網頁觀看
                        if ($assetData["ivq"] === 'Y') {
                            $v['href'] = $lcmsHost . 'asset/play/' . $href[2] . '?token=' . $this->_LCMSVerifyData["data"]['ticket'] . '&idx=' . $sysSession->ticket . '&data=' . $this->_LCMSVerifyData["enc"];
                        } else {
                            $v['href'] = $assetData["href"];
                        }
                    } else {
                        // 素材將網址置換成實體路徑，沒有可置換的路徑時，使用原本 PRO 的 LCMS 導向網址
                        $v['href'] = $href[3];
                    }
                }
            }
            // 寫回原陣列
            $items[$k] = $v;
        }
        return $items;
    }

    /**
     * 將 LCMS 傳來的單元資訊轉換為素材節點
     * @param $username
     * @param $courseId
     * @param $currItem
     * @param $unitData
     * @return array
     */
    function createAssetItems($username, $courseId, $currItem, $unitData) {
        global $sysSession;

        $assetAry = array();
        $lcmsHost = sysLcmsHost . (substr(sysLcmsHost, -1) === '/' ? '' : '/');
        $assetUrl = $lcmsHost . 'asset/play/';

        // 取得單元內的素材閱讀狀態
        $unitReading = $this->getLcmsUnitRecord($courseId, $username, $currItem['identifier']);
        foreach($unitData AS $unitK => $unitV) {
            // TODO: 原單元的閱讀狀態會不一致，使用 APP 觀看和 WEB 觀看會有差異
            $assetIdentifier = $currItem['identifier'] . "_" . $unitV['aid'];
            // mp4 要使用 ivq 所以仍使用網頁觀看
            if ($unitV['ivq'] === 'Y') {
                $assetHref = sprintf(
                    '%s%d?token=%s&idx=%s&data=%s',
                    $assetUrl,
                    $unitV['aid'],
                    $this->_LCMSVerifyData["data"]['ticket'],
                    $sysSession->ticket,
                    $this->_LCMSVerifyData["enc"]
                );
            } else {
                $assetHref = $unitV['url'];
            }

            $lcmsAsset = array(
                'identifier' => $assetIdentifier,
                'text' => $unitV['subject'],
                'href' => $assetHref,
                'itemDisabled' => $currItem['itemDisabled'],
                'readed' => $this->checkReaded($assetIdentifier, $unitReading),
                'item' => array(),
                'leaf' => true
            );

            array_push(
                $assetAry,
                $lcmsAsset
            );
        }

        return $assetAry;
    }

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;
        $coursePath = array();
        $unitPath = array();
        $assetPath = array();
        // 從網址取得參數
        $courseId = intval($_GET['cid']);
        $onlyProgress = (trim($_GET['onlyProgress']) === "1") ? true : false;

        $this->_username = $sysSession->username;
        // 變更session 的 course id
        $sysSession->course_id = $courseId;
        $sysSession->restore();
        $schoolId = $sysSession->school_id;

        // 取得課程學習路徑 - XML
        $courseXPath = dbGetOne('`WM_term_path`', '`content`', "`course_id` = {$courseId} ORDER BY `serial` DESC ");

        $items = array();

        // 教材路徑前置參數
        $baseUrl = WM_SERVER_HOST;
        $relativePath = '/base/'.$schoolId.'/course/%s/content/%s';

        // 取得課程名稱
        $table = 'WM_term_course';
        $fields = 'caption';
        $where = "course_id={$courseId}";
        $courseCaption = dbGetOne($table, $fields, $where);
        $captions = getCaption($courseCaption);
        $courseName = $captions[$sysSession->lang];

        // 取LCMS 的網址
        $da = getConstatnt($sysSession->school_id);
        define('sysLcmsHost', $da['sysLcmsHost']);
        define('sysLcmsEnable', $da['sysLcmsEnable'] ? true : false);

        // 取得節點
        if ($xmlDoc = domxml_open_mem($courseXPath)) {
            $xmlXpathContext = xpath_new_context($xmlDoc);

            // 儲存 resource 供 item 取得
            $resourcePath = "//manifest/resources/resource";
            $resourceNodes = $xmlXpathContext->xpath_eval($resourcePath);

            foreach ($resourceNodes->nodeset as $value) {
                $this->_resourceNodes[$value->get_attribute("identifier")] = $value;
            }

            // 取得修課進度 (getProgress寫在wmpro的lib/course.php)
            $this->_progress = getProgress($courseId, $courseXPath, $this->_username);

            $organizationPath = "//manifest/organizations/organization/item";
            $itemNodes = $xmlXpathContext->xpath_eval($organizationPath);

            $items = $this->getItem($itemNodes->nodeset, $courseId, $baseUrl, $relativePath);
            $resultCode = intval(0);
            $message = 'success';

            // 網頁端不多作取得實體檔案路徑的動作
            if (!$onlyProgress) {
                // LCMS 課程路徑轉換
                if (count($this->_lcmsCoursesPath) >= 1) {
                    $coursePath = $this->getLCMSCoursePath($this->_username, $this->_lcmsCoursesPath);
                }

                // LCMS 單元路徑轉換
                if (count($this->_lcmsUnitsPath) >= 1) {
                    $unitPath = $this->getLCMSUnitPath($this->_lcmsUnitsPath);
                }

                // LCMS 素材路徑轉換
                if (count($this->_lcmsAssetsPath) >= 1) {
                    $assetPath = $this->getLCMSAssetPath($this->_lcmsAssetsPath);
                }

                if (count($coursePath) >= 1 || count($unitPath) >= 1 || count($assetPath) >= 1) {
                    // 取得 LCMS 驗證 token
                    $data = getLcmsVerifyData($courseId, array('screenshot' => '0'));
                    $enc = '';
                    if ($data !== false) {
                        $key = 'wmpro_lcms_pqal' . $data['ticket'];
                        $enc = sysNewEncode(serialize($data), $key, true, MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
                    } else {
                        $data = array(
                            'idx'      => $_COOKIE['idx'],
                            'teachers' => array(),
                            'ticket'   => ''
                        );
                    }
                    $this->_LCMSVerifyData = array(
                        "enc" => $enc,
                        "data" => $data
                    );
                }
                // 替換 LCMS 實體檔案路徑
                $items = $this->replaceLCMSHref($items, $courseId, $coursePath, $unitPath, $assetPath);
            }
        } else {
            $resultCode = intval(2);
            $message = 'fail';
        }
        // make json
        $jsonObj = array(
            'code' => $resultCode,
            'message' => $message,
            'data' => array(
                'course_id' => $courseId,
                'base_url' => $baseUrl,
                'progress' => $this->_progress['progress'],
                'path' => array(
                    'text' => $courseName,
                    'item' => $items
                )
            )
        );

        $jsonEncode = JsonUtility::encode($jsonObj);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}