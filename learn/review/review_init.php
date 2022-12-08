<?php
	/**
	 * 審核流程 - 選課流程
	 *
	 * @since   2003/09/29
	 * @author  ShenTing Lin
	 * @version $Id: review_init.php,v 1.1 2010/02/24 02:39:11 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	// $sysSession->cur_func = '1100100100';
	// $sysSession->restore();
	if (!aclVerifyPermission(1100100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}
	// 如果有其他程式要使用這支程式，請載入底下的訊息
	// require_once(sysDocumentRoot . '/lang/review.php');
	//require_once(sysDocumentRoot . '/lib/interface.php');

	function build_rule_xml($user, $email, $disid, $type, $val, $comment) {
		$comment  = htmlspecialchars($comment);
		$xmlStrs  = <<< EOB
<?xml version="1.0" encoding="UTF-8" ?>
<wm_flow>
	<starter account="{$user}" email="{$email}" />
	<content>
		<kind>course</kind>
		<account user="{$user}" email="{$email}" />
		<description></description>
		<discren_id>{$disid}</discren_id>
	</content>
	<flow>
		<activity id="WM_START" status="decide">
			<description></description>
			<to account="{$type}" email="">
				<agent account="" email=""></agent>
				<feedback param="{$val}">
					<param value="greater" activity="WM_DENY"></param>
					<param value="smaller" activity="WM_DENY"></param>
					<param value="differ" activity="WM_DENY"></param>
					<param value="equal" activity="WM_DENY"></param>
					<param value="deny" activity="WM_DENY"></param>
				</feedback>
				<comment type="text">{$comment}</comment>
				<arrive_time></arrive_time>
				<receive_time></receive_time>
				<decide_time></decide_time>
			</to>
		</activity>
		<activity id="WM_DENY" status="decide">
			<description></description>
			<to account="" email="">
				<agent account="" email=""></agent>
				<feedback param="deny">
					<param value="ok" activity=""></param>
					<param value="deny" activity=""></param>
				</feedback>
				<comment type="text">{$comment}</comment>
				<arrive_time></arrive_time>
				<receive_time></receive_time>
				<decide_time></decide_time>
			</to>
		</activity>
	</flow>
</wm_flow>
EOB;
		return $xmlStrs;
	}

	function init_rule($fwid, $disid, $user1, $email1, $user2, $email2, $cont, $stat='open', $param='', $result='') {
		// 排除還在審核中的課程
		list($cnt) = dbGetStSr('WM_review_flow', 'count(*)', "`flow_serial`={$fwid} AND `username`='{$user2}' AND `kind`='course' AND `discren_id`='{$disid}' AND `state`='open'", ADODB_FETCH_NUM);
		if ($cnt > 0) return '';

		$xmlStrs  = <<< EOB
<?xml version="1.0" encoding="UTF-8" ?>
<wm_flow>
	<starter account="{$user1}" email="{$email1}" />
	<content>
		<kind>course</kind>
		<account user="{$user2}" email="{$email2}" />
		<description></description>
		<discren_id>{$disid}</discren_id>
	</content>
{$cont}
</wm_flow>
EOB;
		$fields = '`flow_serial`,`username`,`create_time`,`kind`,`discren_id`,`state`, `param`, `result`,`content`';
		$values = "{$fwid}, '$user2', NOW(), 'course', {$disid}, '{$stat}', '{$param}', '{$result}', '{$xmlStrs}'";
		dbNew('WM_review_flow', $fields, $values);
	}

	function set_result($disid, $param, $result, $content='') {
		global $sysSession;

		$fields = '`flow_serial`,`username`,`create_time`,`kind`,`discren_id`,`state`, `param`, `result`,`content`';
		$values = "0, '{$sysSession->username}', NOW(), 'course', {$disid}, 'close', '{$param}', '{$result}', '{$content}'";
		dbNew('WM_review_flow', $fields, $values);
	}
?>
