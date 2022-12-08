<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '900200500';
	$sysSession->restore();
	if (!aclVerifyPermission(900200500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if ($_GET['bTicket'] != md5(sysTicketSeed . $sysSession->username . 'read' . $sysSession->ticket . $sysSession->board_id))
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
	$tabs_name = $MSG['reply'][$sysSession->lang];
	$st_id     = $sysSession->cur_func . $sysSession->board_id . '1';
	$referurl  = 'index.php';
	if (preg_match('/^\d{9,}$/', $_POST['node']))
	{
	    $_POST['page_no']       = abs($_POST['page_no']);
	    $_POST['post_per_page'] = abs($_POST['post_per_page']);
	    $_POST['curr_page']     = abs($_POST['curr_page']);
	    $_POST['item_per_page'] = abs($_POST['item_per_page']);
	$action    = "writing.php?threadSequence=1&page_no={$_POST['page_no']}&post_per_page={$_POST['post_per_page']}&curr_page={$_POST['curr_page']}&item_per_page={$_POST['item_per_page']}";
	}
	else
	$action    = 'writing.php';
	$ticket    = md5(sysTicketSeed . 'board' . $_COOKIE['idx'] . $sysSession->board_id);
	$bn_extra  = '';

	$subject   = strpos(($sj = htmlspecialchars(trim(stripslashes($_POST['subject'])), ENT_QUOTES)), 'Re: ') === 0 ? $sj : ('Re: ' . $sj);

	// 去掉最後面沒有用的 <p> 及 <br>
    $ct = stripslashes(trim($_POST['content']));
	do
	{
	    $content = $ct;
		$ct      = preg_replace('!\s*(<(\w)+\b[^>]*>(\s|&nbsp;)*</\2>|<br\b[^>]*>)$!isU', '', $content);
	}
	while($ct != $content);

	if (preg_match('/<blockquote\b[^>]*\bborder-left: gray 2px dotted\b[^>]*\bbackground-color: #([0-9A-Fa-f]{6})\b[^>]*>/iU', $content, $regs))
	{
	    $bgc = (strtolower($regs[1]) == 'eeeeee') ? 'CFCFB6' : 'EEEEEE';
	}
	else
	    $bgc = 'EEEEEE';

	$content = '<p>&nbsp;</p><blockquote style="margin-left: 1em; margin-right: 0; margin-bottom: 0; padding: 5px; border-left: 2px gray dotted; background-color: #' . $bgc . '">' .
		$content .
		'</blockquote>';

	define('EDIT_MODE', 'reply');
	require_once(sysDocumentRoot . '/forum/lib_edit.php');
?>
