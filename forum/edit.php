<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '900200600';
	$sysSession->restore();
	if (!aclVerifyPermission(900200600, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if ($_GET['bTicket'] != md5(sysTicketSeed . $sysSession->username . 'read' . $sysSession->ticket . $sysSession->board_id))
	{
	    echo <<< EOB
<script>
	alert('Incorrect board id.');
	location.replace('/forum/');
</script>
EOB;
		exit;
	}

	$title     = $MSG['post'][$sysSession->lang];
	$tabs_name = $MSG['title_modify'][$sysSession->lang];
	$st_id     = $sysSession->cur_func . $sysSession->board_id;
	$referurl  = 'read.php';
	$action    = 'writing.php';
	$ticket    = md5(sysTicketSeed . 'board' . $_COOKIE['idx'] . $sysSession->board_id);
	$bn_extra  = '';

	$subject   = trim(stripslashes($_POST['subject']));
	$content   = trim(stripslashes($_POST['content']));

	define('EDIT_MODE', 'edit');
	require_once(sysDocumentRoot . '/forum/lib_edit.php');
?>
