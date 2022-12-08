<?php
/**
 * 提供與IRS相關的函數
 *
 * 建立日期：2018/01/25
 * @author jeff
 *
**/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/common.php');

class irs
{

    /**
     * 取得IRS前端要回應的url
     * @param  [type] $courseId [description]
     * @param  [type] $type     [description]
     * @param  [type] $examId   [description]
     * @return [type]           [description]
     */
    function getIrsResponseUrl($courseId, $type, $examId){
        global $_SERVER, $_COOKIE;
        $parseurl = parse_url($_SERVER['SCRIPT_URI'] );
        $host = $parseurl['scheme'] . '://' . $parseurl['host'];
        // $goto = sysNewEncode(sprintf('course_id=%d&type=%s&exam_id=%d',$courseId, $type, $examId), 'wm5IRS');
        $goto = sysNewEncode(serialize(array('course_id'=>$courseId, 'type'=>$type, 'exam_id'=>$examId)), 'wm5IRS');
        $url = sprintf('%s/mooc/irs/check.php?action=start&goto=%s',$host,$goto);
        return getQrcodePath($url,'1','L', 10,465,465);
    }
}
