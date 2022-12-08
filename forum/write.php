<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '900200500';
	$sysSession->restore();
	if (!aclVerifyPermission(900200500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if(!empty($_POST['writeError']))
	{
		echo <<< EOB
<script>
	alert('{$_POST['writeError']}');
	location.replace('/forum/');
</script>
EOB;
		exit;
	}
	if ($_GET['bTicket'] != md5(sysTicketSeed . 'Board' . $_COOKIE['idx'] . $sysSession->board_id))
	{
	    echo <<< EOB
<script>
	alert('Incorrect board id.');
	location.replace('/forum/');
</script>
EOB;
		exit;
	}

	$title     = $MSG['modify'][$sysSession->lang];
	$tabs_name = $MSG['post'][$sysSession->lang];
	$st_id     = $sysSession->cur_func . $sysSession->board_id . '0';
	$referurl  = 'index.php';
	$action    = 'writing.php?' . $_SERVER["QUERY_STRING"];
	$ticket    = md5(sysTicketSeed . 'board' . $_COOKIE['idx'] . $sysSession->board_id);
	$bn_extra  = '';

	$subject   = trim(stripslashes($_POST['subject']));
	$content   = trim(stripslashes($_POST['content']));

	define('EDIT_MODE', 'write');
	require_once(sysDocumentRoot . '/forum/lib_edit.php');
?>
