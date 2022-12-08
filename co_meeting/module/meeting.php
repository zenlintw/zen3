<?php

class Meeting {

    const adm_user = "admin";
    const adm_pwd  = "admin";
    const serverIP = "192.168.10.224";
    const teacher_pwd = 'teacher123';
    const student_pwd = 'student123';
    // $GLOBALS['sysConn']->debug = true;

    /**
     * 呼叫 WebService
     */
    private function DoSOAP($code,$url,$xml,$pwd="")
    {
        $soap = new SoapClient($url);
        $result = $soap->getWebServiceResult($code,$xml,$pwd);
        echo "<xmp>" . $res ."</xmp>";
        return $result;
    }

    /**
     * 解析XML
     */
    private function parserXML($xml)
    {
        $xml = simplexml_load_string($xml);
        $xml = json_decode(json_encode($xml),true);
        return $xml;
    }
    
    /**
     * 檢查用戶
     */
    function accountExist($username)
    {
        $url = "http://" . self::serverIP ."/Conf/services/UserWebService?wsdl";
        $xml = '<?xml version="1.0" encoding="UTF-8"?><Package query="username"  key="'.$username.'" />';
        $res = $this->DoSOAP('300',$url,$xml);
        $res = $this->parserXML($res);
        if($res['@attributes']['errorcode']!='6001')
        {
            $ck = dbGetOne("CO_meeting_user","count(*)","username='{$username}'");
            if($ck==0)
            {
                dbNew("CO_meeting_user","username,add_time","'{$username}',NOW()");
            }
            return true;
        }
        //無此帳號，需建立
        return false;
    }

    /**
     * 取得寶訊通個人帳號密碼
     */
    function getUser($username)
    {
        $pwd = dbGetOne("CO_meeting_user","password","username='{$username}'");
        return $pwd;
    }

    /**
     * 建立用戶
     */
    function createAccount($username)
    {
        $url = "http://" . self::serverIP ."/Conf/services/UserWebService?wsdl";
        $info = dbGetRow("WM_all_account","username,personal_id,CONCAT(last_name,first_name) as realname,email,gender","username='{$username}'");
        $gender = ($info['gender']=='M')?"1":"0";
        $email  = (empty($info['email']))?"none@mail.com":$info['email'];
        $info['realname'] = (empty(trim($info['realname'])))?$username:$info['realname'];
        //藉由目前註冊人數計算ID
        $id = dbGetOne("CO_meeting_user","IFNULL(MAX(id),0)+1","1");
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <Package username="'.$username.'" userpasswd="0000" realname="'.$info['realname'].'" role="2" 
        thirduserid="'.$id.'" usernum="4" sex="'.$gender.'" email="'.$email.'" />';
        $res = $this->DoSOAP('100',$url,$xml);
        $res = $this->parserXML($res);
        if($res['@attributes']['code']==='0')
        {
            //成功
            dbNew("CO_meeting_user","username,add_time","'{$username}',NOW()");
            return 1;
        }
        //失敗
        return 0;
    }

    /**
     * 確認會議室是否存在
     */
    function isMeetingExist($course_id) 
    {
        $url = "http://" . self::serverIP ."/Conf/services/ConferenceWebService?wsdl";
        $data = dbGetRow("CO_meeting_list","*,0 as isExist","course_id={$course_id} AND end_date>NOW() order by add_time desc limit 1");
        if($data)
        {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <Package confid="'.$data['confid'].'" />';
            $res = $this->DoSOAP('8000',$url,$xml);
            $res = $this->parserXML($res);
            if($res['conf']['@attributes']['confid']==$data['confid'])
            {
                $data['isExist'] = 1;
            }
        }
        return $data;
    }

    /**
     * 建立會議
     */
    function createMeeting($course_id,$topic,$begin,$end)
    {
        $url = "http://" . self::serverIP ."/Conf/services/ConferenceWebService?wsdl";
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <Package username="'.self::adm_user.'" userpassword="'.self::adm_pwd.'" 
        topic="'.$topic.'" confuserpwd="'.self::student_pwd.'" chairmanpwd="'.self::teacher_pwd.'"
        begintime="'.$begin.'" endtime="'.$end.'"
        reservetype="1" encrypt="0"
        mustreguser="0"  musthavepwd="1" confshow="0"
        attendnum="2" />';
        $res = $this->DoSOAP('1000',$url,$xml);
        $res = $this->parserXML($res);
        if($res['@attributes']['code']=='1')
        {
            $value = sprintf("%s,%s,'%s','%s','%s',NOW(),'%s','%s'",$course_id,$res['confid'],$topic,$begin,$end,$GLOBALS['sysSession']->username,$res['enterurl']);
            $GLOBALS['sysConn']->Execute("REPLACE INTO CO_meeting_list (course_id,confid,topic,start_date,end_date,add_time,creator,url) VALUES ($value)");
            return $res['@attributes']['code'];
        }
        return $res['@attributes']['errorcode'];
    }

    function getListForMainList($username, $course_id) 
    {
        $isExist = $this->isMeetingExist($course_id);
        if($isExist['isExist']!==1)
        {
            return array();
        }
        $data = dbGetRow('CO_meeting_list', 'topic as title,start_date as open_time_view, url', 'course_id=' . $course_id . ' and confid="' . $isExist['confid'] . '"');
        $data['close_time_view'] = $isExist['end_date'];
        $data['state_view'] = '啟用';
        $data['onclick'] = 'joinMeeting()';
        return array($data);
    }
}