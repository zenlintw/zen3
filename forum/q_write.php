<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '900300400';
	$sysSession->restore();
	if (!aclVerifyPermission(900300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if ($_GET['bTicket'] != md5($sysSession->username . 'quint' . $sysSession->ticket . $sysSession->school_id))
	{
	    echo <<< EOB
<script>
	alert('Incorrect board id.');
	location.replace('/forum/q_index.php');
</script>
EOB;
		exit;
	}

	$title     = $MSG['modify'][$sysSession->lang];
	$tabs_name = $MSG['post'][$sysSession->lang];
	$st_id     = $sysSession->cur_func . $sysSession->board_id;
	$referurl  = 'q_index.php';
	$action    = 'q_writing.php';
	$ticket    = md5(sysTicketSeed . 'quint' . $_COOKIE['idx'] . $sysSession->board_id );
	$bn_extra  = '(' . $MSG['quint'][$sysSession->lang] . ')';

	$subject   = trim(stripslashes($_POST['subject']));
	$content   = trim(stripslashes($_POST['content']));

	define('EDIT_MODE', 'q_write');
	require_once(sysDocumentRoot . '/forum/lib_edit.php');
?>
