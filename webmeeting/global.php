<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/webmeeting/mmc_config.php');
require_once(sysDocumentRoot . '/lib/username.php');

#========= Function =====
	function getRealnameByUsername($user)
	{
		list($last_name,$first_name) = dbGetStSr('WM_user_account', 'last_name, first_name', "username='{$user}'", ADODB_FETCH_NUM);
		$rtns = checkRealname($first_name, $last_name);
		if (empty($rtns)) $rtns = $user;
		return $rtns;
	}


function buildMeetingChatroom($cid, $title, $id, $user, $mtype, $extra='')
{
	global $sysSession,$sysConn;

	$rid = uniqid('');
	$owner = preg_replace('/[^\d_]/', '', $cid);
	$title = stripslashes(trim($title));
	$lang = array('Big5'=>$title,'GB2312'=>$title,'en'=>$title);
	$chat_jump     = 'deny';
	$chat_open     = date("Y-m-d H:i:s");
	$chat_close    = '0000-00-00 00:00:00';
	$chat_media    = 'disable';
	$chat_protocol = 'TCP';
	$chat_login    = 'Y';
	$dd = array(
		'title'      => addslashes(serialize($lang)),
		'limit'      => 0,
		'exitAct'    => 'forum',
		'jump'       => $chat_jump,
		'status'     => 'open',
		'visibility' => 'visible',
		'media'      => $chat_media,
		'ip'         => '',
		'port'       => 0,
		'protocol'   => $chat_protocol,
		'host'       => $user,
		'login'      => $chat_login
	);

	dbNew('WM_chat_setting',
			'`rid`, `owner`, `title`, `host` , `get_host`, `maximum`,`exit_action`, `jump`,
			 `open_time`, `close_time`, `state`, `visibility`, `media`, `ip`,`port`, `protocol`',
			"'{$rid}', '{$owner}', '{$dd['title']}', '{$dd['host']}','{$dd['login']}', {$dd['limit']},
			 '{$dd['exitAct']}', '{$dd['jump']}', '{$chat_open}','{$chat_close}', '{$dd['status']}',
			 '{$dd['visibility']}', '{$dd['media']}', '{$dd['ip']}',{$dd['port']}, '{$dd['protocol']}'")
	or $sysConn->ErrorMsg();

	$title = addslashes($title);
	dbNew('WM_chat_mmc',
			'`rid`,`owner`,`title`,`meetingID`,`meetingType`,`extra`,`creator`',
			"'{$rid}','{$owner}','{$title}','{$id}','{$mtype}','{$extra}','{$user}'")
	or $sysConn->ErrorMsg();
}

function getChatroomMMCList($owner)
{
	global $sysConn;
	$rtnArray = array();
	chkSchoolId('WM_chat_mmc');
	$sqls = "select * from WM_chat_mmc where owner='{$owner}' ";
	$rs = $sysConn->Execute($sqls);
	if ($rs && $rs->RecordCount()>0)
	{
		while($obj=$rs->FetchNextObj())
		{
			$rtnArray[] = $obj;
		}
	}
	return $rtnArray;
}

//查詢這meetingID是否存在
function isChatroomMMCExists($meetingID, $mtype, &$rtnObj)
{
	global $sysConn,$sysSession;
	$rtnObj = null;
	chkSchoolId('WM_chat_mmc');
	$sqls = "select * from WM_chat_mmc where meetingID='{$meetingID}' and meetingType='{$mtype}' ";
	$rs = $sysConn->Execute($sqls);
	if ($rs && $rs->RecordCount()>0)
	{
		$rtnObj = $rs->FetchNextObj();
		return true;
	}
	return false;
}

//查詢這會議是不是MMC的會議
function isMMC_Chatroom($rid)
{
	global $sysConn, $sysSession;
	$rtnObj = null;
	chkSchoolId('WM_chat_mmc');
	$sqls = "select count(*) as ct from WM_chat_mmc where rid='{$rid}'";
	$rs = $sysConn->Execute($sqls);
	if ($rs)
	{
		$obj = $rs->FetchNextObj();
		if ($obj->ct > 0) return true;
	}
	return false;
}


//刪除過期的joinnet Meeting資料，而$id是現行的
	function DeleteExpireMeetingRid($id, $cid, $mtype)
	{
		global $sysConn, $sysSession;
		$arr = array();
		$arr1 = array();
		$sqls = "select T1.rid,T1.meetingID,T2.open_time from WM_chat_mmc as T1 inner join
				 WM_chat_setting as T2 on T1.rid=T2.rid
				 where T1.owner='{$cid}' and T1.meetingType='{$mtype}' and T1.extra <> 'eternal'";
        chkSchoolId('WM_chat_mmc');
		$rs = $sysConn->Execute($sqls);
		if ($rs && $rs->RecordCount())
		{
			while($obj = $rs->FetchNextObj())
			{
				$a = strtotime($obj->open_time);
				if ((time()-$a) <= 6*60*60) continue;
				if (strcmp($obj->rid, $id) != 0)
				{
					$arr[] = "'".$obj->rid."'";
					$arr1[] = $obj->meetingID;
				}
			}
		}
		if (count($arr) == 0) return;
		$rids = implode(',',$arr);

		dbDel('WM_chat_mmc',     "rid in ({$rids})");
		dbDel('WM_chat_setting', "rid in ({$rids})");

		if ($mtype == 'breeze')
		{
			include_once(sysDocumentRoot . '/breeze/global.php');
			for($i=0, $size=count($arr1); $i < $size; $i++)
			{
				list($scoid, $urlpath) = explode(':',$arr1[$i]);
				if (hasRecordingScos($scoid) == 0) deleteScoResource($scoid, $sess='');
			}
		}

	}

	function &getMeetingData($id)
	{
		global $sysConn;

        chkSchoolId('WM_chat_setting');
		$sqls = "select * from WM_chat_setting where rid='{$id}'";
		if ($rs = $sysConn->Execute($sqls))
		{
			return $rs->FetchNextObj();
		}
		return null;
	}

	// for joinnet & Breeze Live
	function &getMeetingRid($id)
	{
		if (list($rid) = dbGetStSr('WM_chat_mmc', 'rid', "meetingID='{$id}'", ADODB_FETCH_NUM))
		{
			return getMeetingData($rid);
		}
		return null;
	}
#========= Class ========
class MeetingServer
{
	var $IP;		//Server Primary IP
	var $Portm;		//Preferred connection port
	var $Portm2;	//Alternate connection port
	function MeetingServer($ls_ip, $l_port=443, $l_port2=2345)
	{
		$this->IP = $ls_ip;
		$this->Portm = $l_port;
		$this->Portm2 = $l_port2;
	}
}

class MeetingOwner
{
	var $ID;		//Owner ID
	var $Name;		//Owner Name
	var $Email;		//Owner Email

	function MeetingOwner($ls_id, $ls_name, $ls_email='')
	{
		$this->ID = $ls_id;
		$this->Name = $ls_name;
		$this->Email = $ls_email;
	}
}

class MeetingInfo
{
	var $ID;			    //Meeting ID
	var $Title;			    //Meeting Title
	var $MaxGuests=200;		//Guest Maxinum number
	var $Duration=0;		//based on person
	var $AutoExtension=1;	//1=Yes;0=NO;Whether the meeting can continue beyond the specified duration
	var $Recording=1;		//Whether the meeting will be recorded
	var $Password;			//Password required for entering the meeting

	function MeetingInfo($l_rid, $rname)
	{
		$this->ID = $l_rid."-".time();
		$this->Title = $rname;
	}
}

#======== Main ==============
$o_mserver = new MeetingServer($MMC_Server_addr,$MCU_Server_port,$MCU_Server_port1);
$o_mowner = new MeetingOwner($WM3_Meeting_Owner, $sysSession->realname, $sysSession->email);
$o_minfo = new MeetingInfo($WM3_Meeting_Owner, $sysSession->course_name);

?>
