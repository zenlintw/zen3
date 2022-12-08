<?php
	require_once($DOCUMENT_ROOT . '/config/global_var.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/breeze/global.php');
	require_once(BREEZE_PHP_DIR . '/Actions/SessionManager.php');
	require_once(BREEZE_PHP_DIR . '/Actions/ScoInfo.php');
	require_once(BREEZE_PHP_DIR . '/Report/Meetings.php');
#========= Function =================


	function ISOmktime($str)
	{
		$arr = explode('T', $str);
		list($y, $m, $d) = explode('-',$arr[0]);
		list($H, $i, $s) = explode(':',substr($arr[1],0,8));
		return mktime($H, $i, $s, $m, $d, $y);
	}

	function printArchivesList($arr)
	{
		$rtns = '';
		for($i=0; $i<count($arr); $i++)
		{
			$obj = $arr[$i];
			$duration = ISOmktime($obj->date_modified) - ISOmktime($obj->date_created);
			$bg = ($bg == 'bg4')?'bg3':'bg4';
			$rtns .= '<tr class="'.$bg.'">'."\r\n";
			$rtns .= '<td rowspan="2" nowrap align="center" class="a01">'.($i+1).'</td>'."\r\n";
			$name = iconv('UTF-8', 'BIG5', $obj->name);
			if (($pos = strpos($name, '-')) !== false)
			{
				$name = substr($name, $pos+1);
			}
			if (($pos = strrpos($name, '[')) !== false)
			{
				$name = substr($name, 0, $pos);
			}
			$rtns .= '<td nowrap class="a01">'.$name.'</td>'."\r\n";
			$rtns .= '<td nowrap align="center" class="a01">'.getSimpleDateTimeExpress($obj->date_begin).'</td>'."\r\n";
			$rtns .= '<td rowspan="2" nowrap align="center" class="a01"><input type="button" onClick="doPlay(\''.$obj->scoId.'\');" class="box01" value="GO"></td>'."\r\n";
			$rtns .= '<td rowspan="2" nowrap align="center" class="a01"><input type="button" onClick="doDelete(\''.$obj->scoId.'\');" class="box01" value="Delete"></td>'."\r\n";
			$rtns .= '</tr>	'."\r\n";
			$rtns .= '<tr class="'.$bg.'">'."\r\n";
			$rtns .= '<td nowrap class="a01">'.$obj->urlpath.'</td>'."\r\n";
			$rtns .= '<td nowrap align="center" class="a01">'.getSimpleDateTimeExpress($obj->date_end).'</td>'."\r\n";
			$rtns .= '</tr>	'."\r\n";


		}
		return $rtns;
	}
#========= Main =================
require_once($DOCUMENT_ROOT . '/lang/' . (in_array($language, array('GB2312', 'en')) ? $language : 'default').'/compose_info.php');

if ($language == 'en')
{

	$lang = array(
		"seq"=>"NO.","meetingname"=>"Meeting Title","starttime"=>"Start Time","endtime"=>"End Time",
		"doPlay"=>"Join Meeting","doDelete"=>"Delete Meeting","doAdd"=>"Create New Meeting",
		"confirm_delete"=>'Are you sure to remove this Breeze Meeting?'
		);
}else{
	$lang = array(
		"seq"=>"序號","meetingname"=>"會議名稱","starttime"=>"開始時間","endtime"=>"結束時間",
		"doPlay"=>"播放","doDelete"=>"刪除","doAdd"=>"新增Breeze會議",
		"confirm_delete" => '確認要刪除此筆Breeze會議？'
		);
}

//1. Get Admin Session
	$sess = getEnableSessionId();
	if (empty($sess)) die("errcode:001");
//2. Get ActiveMeeting Array
	$Archives = getMeetingList($sess, getCUID($cid));
    $CSS   = $ThemePath . '/stud/student.css';
    $Theme = $ThemePath . '/stud';
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=in_array($language, array('GB2312', 'en')) ? $language : 'big5';?>">
<title><?=$MSG['personal_learning_history'];?></title>
<link rel="stylesheet" href="<? echo $CSS; ?>" type="text/css">
<script language="javascript">
function doPlay(id)
{
	var options = "toolbar=0,status=0,location=0,resizable=1";
	var url = "JoinMeeting.php?scoId="+id;
	var win = open(url, "", options);
}

function doDelete(id)
{
	if (confirm("<? echo $lang['confirm_delete'];?>"))
	{
		document.frmDelete.scoid.value = id;
		document.frmDelete.submit();
	}
}

function doAdd()
{
	document.location.href="CreateMeeting.php?mtype=forever";
}

</script>
</head>
<body>
<table border="1" cellspacing="1" cellpadding="0" align="center" class="border1" width="760">
	<tr class="bg1">
		<td class="border2" nowrap>
			<img src="<? echo $Theme; ?>/icon_01.gif" width="10" height="10" border="0" align="absmiddle">
			<span class="t02"><?=$MSG['online_chat'];?></span>&nbsp;
		</td>
	</tr>
	<tr class="bg2">
		<td class="border2" nowrap id="MyTd">
			<table width="100%" border="0" cellspacing="1" cellpadding="2" id="MyTable">
				<tr class="bg3">
					<td colspan="5" align="right"><input type="button" name="btnNew" value="<? echo $lang['doAdd'];?>" onClick="doAdd();" class="box01"></td>
				</tr>
				<tr class="bg3">
				    <td rowspan="2" nowrap align="center" class="t01"><? echo $lang['seq'];?></td>
				    <td nowrap align="center" class="t01"><? echo $lang['meetingname'];?></td>
				    <td nowrap align="center" class="t01"><? echo $lang['starttime'];?></td>
				    <td rowspan="2" nowrap align="center" class="t01"><? echo $lang['doPlay'];?></td>
				    <td rowspan="2" nowrap align="center" class="t01"><? echo $lang['doDelete'];?></td>
				</tr>
				<tr class="bg3">
					<td nowrap align="center" class="t01">URL</td>
				    <td nowrap align="center" class="t01"><? echo $lang['endtime'];?></td>
				</tr>
				<? echo printArchivesList($Archives);?>
			</table>
		</td>
	</tr>
</table>
<form name="frmDelete" method="post" action="MeetingDelete.php">
<input type="hidden" name="scoid" value="">
</form>
</body>
</html>

