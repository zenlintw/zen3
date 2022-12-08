<?php
/*
* 邏輯層：功能處理
* 接收中介層參數經處理後，傳回中介層
*
* @since   2014/3/4
* @author  cch
*/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/lib/jsonwrapper/jsonwrapper.php');
require_once(sysDocumentRoot . '/lib/co_chkform.php');
require_once(sysDocumentRoot . '/mooc/models/user.php');
require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

switch($_POST['action']) {

    /*
    * 確認電子信箱有沒有重複
    * @param string $_POST['email']:電子信箱
    *
    * @return array $arr:
    */
    case "getEmailDuplicate":
        $rs = checkUserEmail($_POST['email'], false, trim($_POST['username']));

        $msg = json_encode($rs);
        break;

    /*
    * 確認帳號有無存在
    * @param string $_POST['username']:帳號
    *
    * @return array $arr:
    */
    case "getTmpAccount":
        $rs = checkTmpAccount(trim($_POST['username']));

        $msg = json_encode($rs);
        break;

    /*
    * 新增使用者筆記
    * @param string $_POST['data']:加密資料
    *
    * @param string $_POST['token']:鑰匙
    *
    * @param string $_POST['ticket']:idx
    * 
    * @return array $arr:
    */
    case "addUserNote":
        if (isset($_POST['data']) && isset($_POST['token'])) {
            // LCMS 快照筆記
            $dec = sysNewDecode($_POST['data'], 'wmpro_lcms_pqal'.$_POST['token'], true, MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
            $data = unserialize($dec);
            if (($_POST['token'] !== $data['ticket']) && false) {
                $data = 'error data!';
                return;
            }
            // 儲存筆記
            $rsSnapshot = new snapshot();
            $rtn = $rsSnapshot->addNoteByUsername( $data['username'], 
                                        $data['course_id'],
                                        $data['course_name'], 
                                        $data['sco_id'],
                                        $data['videoName'],
                                        $data["videoUrl"],
                                        $data['point_time'],
                                        $data['imageUrl'],
                                        $data['aid']);
        } else {
            // mooc 撰寫筆記
        }
        $msg = json_encode($rtn);
        break;

    
    /*
    * 取得使用者筆記
    * @param string $_POST['key']:關鍵字
    *
    * @return array $arr:
    */
    case "getUserNote":
        $rsSnapshot = new snapshot();
        if (isset($_POST['key']) || intval($_POST['cid']) != '0') {
            $filter = array(
                        'keyword'   =>      trim($_POST['key']),
                        'course_id' =>      intval($_POST['cid'])
                    );
        } else {
            $filter = '';
        }

        $rs = $rsSnapshot->getNoteByUsername($sysSession->username, $filter);

        $msg = json_encode($rs);
        break;
    
    /*
    * 修改使用者筆記
    * @param string $_POST['note_id']:筆記id
    *
    * @param string $_POST['content']:筆記內容
    *
    * @return array $arr:
    */
    case "setUserNote":
        $rsSnapshot = new snapshot();
        $rs = $rsSnapshot->setNoteByNoteId(intval($_POST['note_id']), $sysSession->username, trim($_POST['content']));

        $msg = json_encode($rs);
        break;

    /*
    * 刪除使用者筆記
    * @param string $_POST['note_id']:筆記id
    *
    * @return array $arr:
    */
    case "delUserNote":
        $rsSnapshot = new snapshot();
        $nId = explode(",", $_POST['note_id']);
        $rs = $rsSnapshot->delNoteById($sysSession->username, $nId);

        $msg = json_encode($rs);
        break;

    /*
    * 取得筆記討論筆數
    * @param string $_POST['note_id']:筆記id
    *
    * @return array $arr:
    */
    case "getNoteReplyNum":
        $rsSnapshot = new snapshot();
        $nId = explode(",", $_POST['note_id']);
        $rs = $rsSnapshot->getReplyNumByNid($nId);

        $msg = json_encode($rs);
        break;

    /*
    * 新增使用者筆記
    * @param string $_POST['data']:加密資料
    * @param string $_POST['token']:鑰匙
    * @param string $_POST['ticket']:idx
    * 
    * @return array $arr:
    */
    case "setUserNoteFromWebService":
        
        $rtn = array();
        $rtn['code'] = '0';
  
        if (isset($_POST['data']) && isset($_POST['token'])) {
            // LCMS 快照筆記
            $dec = sysNewDecode($_POST['data'], 'wmpro_lcms_pqal' . $_POST['token'], true, MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
            $data = unserialize($dec);
            if (($_POST['token'] !== $data['ticket']) && false) {
                $data = 'error data!';
                return;
            }
            
            // 儲存筆記
            $rsSnapshot = new snapshot();
            $result = $rsSnapshot->setNoteByNoteId(intval($_POST['note_id']), $data['username'], trim($_POST['content']));
            
            if ($result === '1') {
                $rtn['code'] = '1';
            } else {
                $rtn['code'] = '0';
            }
        } 
        
        $msg = json_encode($rtn);
        break;

    /*
    * 刪除大頭照
    * @param string $_POST['username'] 使用者帳號
    *
    * @return array $arr:
    */
    case "delUserPic":
        $rsUser = new user();
        $rtn = $rsUser->delUserPic();

        $msg = json_encode($rtn);
        break;

    /**
     * 取得使用者詳細的資料
     */
    case "getUserDetail":
        if (empty($_POST['user'])) {
            die(json_encode('Error Params0.'));
        }

        // 帳號是否不存在
        $username = trim(sysNewDecode(trim($_POST['user'])));
        $rtnCK = checkUsername($username);
        if (($rtnCK !== 2)&&($rtnCK !== 4)) {
            die(json_encode('Error Params1.'));
        }

        // 此動作需要管理者或是使用者自己
        if ($username != $sysSession->username){
            if (!aclCheckRole($sysSession->username, $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $sysSession->school_id)){
                die(json_encode('illegeal Access.'));
            }
        }

        require_once(sysDocumentRoot . '/lang/people_manager.php');
        $userData = getUserDetailData($username);
        list($userData['last-login'],$userData['login-times'],$begin_time,$expire_time) = dbGetStSr('WM_sch4user', 'last_login,login_times,begin_time,expire_time', 'school_id=' . $sysSession->school_id . " and username='". $username . "'", ADODB_FETCH_NUM);

        $bt = intval($begin_time);
        $et = intval($expire_time);
        $userData['enable-status'] = ($userData['enable']=='Y')?$MSG['value_account_enabled'][$sysSession->lang]:$MSG['value_account_disabled'][$sysSession->lang];
        $userData['enable-during'] = $MSG['from2'][$sysSession->lang] . (empty($bt)?$MSG['now'][$sysSession->lang]:$begin_time) .
                $MSG['to2'][$sysSession->lang] . (empty($et)?$MSG['forever'][$sysSession->lang]:$expire_time);
        $userData['major-count'] = dbGetCourses('count(*)', $username, $sysRoles['auditor']|$sysRoles['student']);
        $userData['teach-count'] = dbGetCourses('count(*)', $username, $sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']);
        $userData['fda-member'] = ($userData['CO_fda_member'] == 'Y')?$MSG['lbl_unit_in'][$sysSession->lang]:$MSG['lbl_unit_out'][$sysSession->lang];
        $userData['gender'] = $userData['gender'] == 'M' ? '<img src="/theme/default/academic/male.gif" />' : '<img src="/theme/default/academic/female.gif" />';
        unset($userData['password'],$userData['personal_id']);

        if ($userData['username'] === $username) {
            $rtn['code'] = '1';
            $rtn['data'] = $userData;
        } else {
            $rtn['code'] = '0';
        }
        $msg = json_encode($rtn);
        break;
    case 'enableUser':
        if (empty($_POST['user'])) {
            die(json_encode('Error Params0.'));
        }

        // 帳號是否不存在
        $username = trim(sysNewDecode(trim($_POST['user'])));
        $rtnCK = checkUsername($username);
        if ($rtnCK !== 2) {
            die(json_encode('Error Params1.'));
        }

        // 此動作需要管理者
        if (!aclCheckRole($sysSession->username, $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $sysSession->school_id)){
            die(json_encode('illegeal Access.'));
        }

        dbSet('WM_all_account',"enable='Y'",sprintf("username='%s'", $username));
        $ct = intval(dbGetOne('WM_all_account','count(*)',sprintf("username='%s' and enable='Y'", $username)));
        if ($ct === 1) {
            $rtn['code'] = '1';
        } else {
            $rtn['code'] = '0';
            $rtn['data'] = 'fail to enable';
        }
        $msg = json_encode($rtn);
        break;
    case 'checkQrcodeLogin':
        if ($sysSession->username != 'guest'){
            $rtn['code'] = '1';
        }else{
            $rtn['code'] = '0';
            $rtn['data'] = 'still guest';
        }
        $msg = json_encode($rtn);
        break;
    case 'LoginQrcode4me':
        if ($sysSession->username == 'guest'){
            $parseurl = parse_url($_SERVER['SCRIPT_URI'] );
            $host = $parseurl['scheme'] . '://' . $parseurl['host'];
            $qrcodeLoginTicket = sysNewEncode(serialize(array($_COOKIE['idx'], $sysSession->username, time())), 'SunWm51');
            $qrCodeLoginUrl = getQrcodePath($host . '/login.php?spotlight=' . $qrcodeLoginTicket, '1', 'L', 4);

            $rtn['code'] = '1';
            $rtn['data'] = $qrCodeLoginUrl;
        }else{
            $rtn['code'] = '0';
            $rtn['data'] = 'logined username';
        }
        $msg = json_encode($rtn);
        break;
    case 'getMyLoginQrcodeUrl':
        if ($sysSession->username != 'guest'){
            $parseurl = parse_url($_SERVER['SCRIPT_URI'] );
            $host = $parseurl['scheme'] . '://' . $parseurl['host'];
            $qrcodeLoginTicket = sysNewEncode(serialize(array($_COOKIE['idx'], $sysSession->username, time())), 'SunWm51');
            $qrCodeLoginUrl = getQrcodePath($host . '/login.php?movelight=' . $qrcodeLoginTicket, '1', 'L', 7);
            $rtn['code'] = '1';
            $rtn['data'] = $qrCodeLoginUrl;
        }else{
            $rtn['code'] = '0';
            $rtn['data'] = 'still guest';
        }
        $msg = json_encode($rtn);
        break;
    default:
        $val = "無此動作";
        $msg = json_encode($val);
        break;
}

if ($msg != '') {
    echo $msg;
}