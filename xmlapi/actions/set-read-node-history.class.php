<?php
/**
 * 進入課程上課，記錄最後登入時間，累加上課次數
 *
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @category    xmlapi
 * @package     WM25
 * @subpackage  WebServices
 * @author      Jeff Wang <jeff@sun.net.tw>
 * @copyright   2011 SunNet Tech. INC.
 * @version     1.0
 * @since       2012-12-28
 */
include_once(dirname(__FILE__).'/action.class.php');

class SetReadNodeHistoryAction extends baseAction
{
    var $courseId;
    var $nodeUrl;
    var $nodeTitle;
    var $startReadDatetime;
    var $endReadDatetime;
    var $activityId;
    var $originalActivityId;
    var $username;
    
    function findNodeTitle()
    {
        $title = '';

        $where = sprintf('course_id=%d ORDER BY `serial` DESC', $this->courseId);
        $path = dbGetOne('WM_term_path', 'content', $where);

        // 各節點只保留繁中語系
        $courseXML = preg_replace('/(<title>[^\t]+)(\t[^<]+)(<\/title>)/', '$1$3', $path);

        if ($xmlDoc = domxml_open_mem($courseXML)) {
            $xmlXpathContext = xpath_new_context($xmlDoc);
            $xpath = '/manifest/organizations/organization//item[@identifier="' . $this->activityId . '"]/title/text()';
            $titles = $xmlXpathContext->xpath_eval($xpath);
            foreach ($titles->nodeset as $content) {
                $title = $content->{content};
            }
        }

        return $title;
    }
    /**
     * 修正 user-course-path 針對相對路徑所加的domain
     * 
     * @param string $url
     * @return string
     */
    function fixNodeUrl($url)
    {
        global $sysSession, $_SERVER;

        $pattern1 = sprintf(
            'http://%s%s/%d%d/',
            $_SERVER['HTTP_HOST'],
            (($_SERVER['SERVER_PORT']=='80')?'':':'.$_SERVER['SERVER_PORT']),
            $sysSession->school_id,
            $this->courseId
        );
        $pattern2 = sprintf(
            'http://%s%s',
            $_SERVER['HTTP_HOST'],
            (($_SERVER['SERVER_PORT']=='80')?'':':'.$_SERVER['SERVER_PORT'])
        );
        foreach (array($pattern1, $pattern2) as $findStr) {
            if (strcmp(substr($url, 0, strlen($findStr)), $findStr) == 0) {
                return substr($url, strlen($findStr));
            }
        }
        return $url;
    }
        
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysConn, $sysSession;

	// TODO: LCMS 素材的判斷需討論後 mapping 回來(release 5.0)
        $readingLimit = 6*60*60;

        $da = getConstatnt($sysSession->school_id);
        $lcmsHost = $da['sysLcmsHost'] . (substr($da['sysLcmsHost'], -1) === '/' ? '' : '/');
        $activityId = $_REQUEST['activity_id'];
        if (strpos($activityId, 'I_SCO_')  !== false &&
                count($activityIdTmp = explode("_", $activityId)) === 5 &&
                strpos($_REQUEST['url'], $lcmsHost) !== false) {
            // 去除 LCMS UNIT SCORM ID 的素材編號 TODO:修正APP store ID 一樣不顯示問題後須改掉
            unset($activityIdTmp[count($activityIdTmp)-1]);
            $activityId = implode("_", $activityIdTmp);
        }

        // 設定資料
        $this->courseId = abs(intval($_REQUEST['cid']));
        $this->nodeUrl = $this->fixNodeUrl($_REQUEST['url']);
        // 查詢用
        $this->activityId = mysql_real_escape_string($activityId);
        // 寫入紀錄用
        $this->originalActivityId = mysql_real_escape_string($_REQUEST['activity_id']);
        $this->nodeTitle = $this->findNodeTitle();
        $this->startReadDatetime = trim($_REQUEST['st']);
        $st = intval(strtotime($_REQUEST['st']));
        if (isset($_REQUEST['et'])) {
            // 若有回傳結束時間
            $et = intval(strtotime($_REQUEST['et']));
            if ($et - $st <= $readingLimit) {
                // 若是在6小時之內，則結束時間照記
                $this->endReadDatetime = date('Y-m-d H:i:s', $et);
            } else {
                // 若是超過6小時，則起始時間加計六小時
                $this->endReadDatetime = date('Y-m-d H:i:s', $st + $readingLimit);
            }
        } else {
            // 若沒有回傳結束時間，則使用現在的時間
            $et = intval(time());
            if ($et - $st <= $readingLimit) {
                // 若是在6小時之內，則結束時間依現在時間計算
                $this->endReadDatetime = date('Y-m-d H:i:s', $et);
            } else {
                // 若是超過6小時，則起始時間加計六小時
                $this->endReadDatetime = date('Y-m-d H:i:s', $st + $readingLimit);
            }
        }
        $jsonData = array();

        if (strtotime($this->endReadDatetime) > strtotime($this->startReadDatetime) && $this->nodeTitle !== '') {
		    chkSchoolId('WM_record_reading');
            $sql = sprintf(
                    "INSERT INTO WM_record_reading(course_id, username, begin_time, over_time, title, url, activity_id)
                     VALUES('%d', '%s', '%s', '%s', '%s', '%s', '%s')",
                    $this->courseId,
                    mysql_real_escape_string($sysSession->username),
                    $this->startReadDatetime,
                    $this->endReadDatetime,
                    mysql_real_escape_string($this->nodeTitle),
                    mysql_real_escape_string($this->nodeUrl),
                    $this->originalActivityId
                );
            $readingSeconds = strtotime($this->endReadDatetime) - strtotime($this->startReadDatetime);
            $sysConn->Execute($sql);
            if ($sysConn->Affected_Rows() > 0) {
                $jsonData['seconds'] = intval($readingSeconds);
                $code = 0;
                $message = 'success';
            } else {
                $code = 2;
                $message = 'fail';
            }
        } else {
            $code = 3;
            $message = 'fail';
        }

        // 寫入閱讀紀錄的log
        $requestTimeMsg = $_REQUEST['st'] . ' ~ ' . $_REQUEST['et'];
        $strToTimeMsg = $this->startReadDatetime . ' ~ '. $this->endReadDatetime;
        $msg = 'App Reading Record[' . $code . '](' . $this->courseId . '-' . $this->originalActivityId . '): ' . $requestTimeMsg . '(' . $strToTimeMsg . ')';
        appSysLog(999999003, $sysSession->school_id , 0 , 1, 'classroom', $_SERVER['PHP_SELF'], $msg, $sysSession->username);

        $jsonObj = array(
            'code' => $code,
            'message' => $message,
            'data' => $jsonData
        );

        $jsonEncode = JsonUtility::encode($jsonObj);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}