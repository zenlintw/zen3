<?php
/**
 * 提供與分享社群相關的函數
 *
 * 建立日期：2014/8/14
 * @author spring
 *
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once(sysDocumentRoot . '/lang/mooc.php');

class school
{

    /**
     * @name 取要開放分享的社群
     * @author spring
     *
     * @param string $sid:學校ID
     *
     * @return array
    */
    function getShareSocial($sid)
    {
        $data = array();
        $cols = '`social_share`';
        $tb = sysDBname . '.`CO_school`';
        $where  = '`school_id` = \'' . intval($sid) . '\' ';

        $rs = dbGetStSr($tb, $cols, $where, ADODB_FETCH_ASSOC);
        if ($rs >= 1 && $rs['social_share'] !== '') {
            $data = explode(',', $rs['social_share']);
            return $data;
        } else {
            return $data;
        }
    }

    /**
     * @name 取要開放分享的社群(HTML碼)
     * @author spring
     *
     * @param string $share:分享選項
     *
     * @param string $appRoot:網站網址
     *
     * @param string $cid:課程ID
     *
     * @param string $caption:課程名稱
     *
     * @param string $nowlang:語系
     *
     * @param string $sid:學校ID
     *
     * @return string $rtn: 成功:HTML字串、失敗:X
    */
    function getShareSocialHtml($share, $appRoot, $cid, $caption, $nowlang, $sid, $phone=false)
    {
        global $MSG;
        $shareUrl ='';
        $sidUrl = '';
        if ('' != $sid) {
            $sidUrl = '/' . $sid;
        }
        foreach ($share as $val) {
            $shareUrl .= '<div class="pic">';
            switch($val){
                case 'FB':
                    $shareUrl .= '<a href="javascript: void(window.open(\'http://www.facebook.com/share.php?u='.urlencode($appRoot.'/info/'.$cid.$sidUrl.'?lang='.$nowlang).'\'));"><div class="fb"></div></a>';
                    break;
                case 'PLURK':
                    if ($phone) {
                        $shareUrl .= '<a href="javascript: void(window.open(\'http://www.plurk.com/m?qualifier=shares&content=\'.concat(encodeURIComponent(\''.$caption.'\')).concat(\' \').concat(encodeURIComponent(\''.$appRoot.'/info/'.$cid.$sidUrl.'?lang='.$nowlang.'\'))));"><div class="plk"></div></a>';
                    } else {
                        $shareUrl .= '<a href="javascript: void(window.open(\'http://www.plurk.com/?qualifier=shares&status=\'.concat(encodeURIComponent(\''.$caption.'\')).concat(\' \').concat(encodeURIComponent(\''.$appRoot.'/info/'.$cid.$sidUrl.'?lang='.$nowlang.'\'))));"><div class="plk"></div></a>';
                    }
                    break;
                case 'TWITTER':
                    if ($phone) {
                        $shareUrl .= '<a href="javascript: void(window.open(\'https://mobile.twitter.com/compose/tweet?text=\'.concat(encodeURIComponent(\''.$caption.'\')).concat(\' \').concat(encodeURIComponent(\''.$appRoot.'/info/'.$cid.$sidUrl.'?lang='.$nowlang.'\'))));"><div class="tw"></div></a>';
                    } else {
                        $shareUrl .= '<a href="javascript: void(window.open(\'http://twitter.com/home/?status=\'.concat(encodeURIComponent(\''.$caption.'\')).concat(\' \').concat(encodeURIComponent(\''.$appRoot.'/info/'.$cid.$sidUrl.'?lang='.$nowlang.'\'))));"><div class="tw"></div></a>';
                    }
                    break;
                case 'LINE':
                    $shareUrl .= '<a id="share-ln-'.$cid.'" href="#inline-ln-'.$cid.'" title="LINE"><div class="ln"></div></a>';
                    break;
                case 'WECHAT':
                    $shareUrl .= '<a id="share-wct-'.$cid.'" data-fancybox-type="iframe" href="' . getQrcodePath($appRoot.'/info/'.$cid.$sidUrl.'?lang='.$nowlang) . '" title="'.$MSG['wechatsharenote'][$nowlang].'"><div class="wct"></div></a>';
                    break;
            }
            $shareUrl .= '</div>';
        }
        return $shareUrl;
    }

    /**
     * @name 取要開放註冊的社群
     * @author spring
     *
     * @param string $sid:學校ID
     *
     * @return array
    */
    function getRegisterSocial($sid)
    {
        $data = array();
        $cols = '`canReg`, `canReg_ext`';
        $tb = '`WM_school` w NATURAL LEFT JOIN `CO_school` c';
        $where  = 'w.`school_id` = \'' . intval($sid) . '\' and w.school_host="'.$_SERVER['HTTP_HOST'].'" and c.`school_id` = w.school_id ';

        $rs = dbGetStSr($tb, $cols, $where, ADODB_FETCH_ASSOC);
        if ($rs >= 1) {
            $data = explode(',', $rs['canReg_ext']);
            $data[] = $rs['canReg'];
            return $data;
        } else {
            return $data;
        }
    }

    /**
     * @name 取學校標題及頁尾資訊
     * @author spring
     *
     * @param string $sid:學校ID
     *
     * @return array
    */
    function getSchoolIndexInfo($sid)
    {
        $cols = '`banner_title1`, `banner_title2`, `banner_title3`, `footer_about`, `footer_contact`, `footer_faq`, `footer_info`';
        // CO_school 改移到 MASTER
        $tb = sysDBprefix . 'MASTER.`CO_school`';
        $where  = '`school_id` = ' . intval($sid) . ' ';

        $rs = dbGetStSr($tb, $cols, $where, ADODB_FETCH_ASSOC);
        if ($rs >= 1) {
            return $rs;
        } else {
            return 'X';
        }
    }


    /**
     * @name 取得學校是否打開 mooc 學習環境
     * @author spring
     *
     * @param string $sid:學校ID
     *
     * @return string $rtn: 成功:student_mooc的值、失敗:視為0
    */
    function getSchoolStudentMooc($sid)
    {
        $cols = '`student_mooc`';
        $tb = sysDBname . '.`CO_school`';
        $where  = '`school_id` = ' . intval($sid) . ' ';

        $rs = dbGetStSr($tb, $cols, $where, ADODB_FETCH_ASSOC);
        if ($rs >= 1) {
            return $rs['student_mooc'];
        } else {
            return 0;
        }
    }

    /**
     * @name 取得學校 FB 參數
     * @author spring
     *
     * @param string $sid:學校ID
     *
     * @return array $rtn: 成功:FB API參數(id,secret)、失敗:'X'
    */
    function getSchoolFBParameter($sid)
    {
        $cols = '`canReg_fb_id`, `canReg_fb_secret`';
        $tb = sysDBname . '.`CO_school`';
        $where  = '`school_id` = ' . intval($sid) . ' ';

        $rs = dbGetStSr($tb, $cols, $where, ADODB_FETCH_ASSOC);
        if ($rs >= 1) {
            return $rs;
        } else {
            return 'X';
        }
    }
    
    /**
     * 取得我的課程呈現方式
     * @param int $sid
     */
    function getMyCourseView($sid)
    {
        $rs = dbGetStSr(sysDBname . '.`CO_school`', 'mycourse_view', sprintf('school_id=%d',$sid), ADODB_FETCH_ASSOC);
        if (in_array($rs['mycourse_view'], array('T','G'))) return $rs['mycourse_view'];
        
        // 若沒有設定預設為文字模式
//        return 'T';
    }
}