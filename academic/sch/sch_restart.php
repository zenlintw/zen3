<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

$httpd = getenv('_');
if (preg_match('!^(/\w+)+/httpd$!', $httpd))
{
	if ($_SERVER["QUERY_STRING"] == 'restart')
	{
		while (ob_end_clean());
		echo <<< EOB
	<table width="100%" height="80%">
	<tr><td align="center"><img src="/theme/default/academic/restart.gif"></td></tr>
	</table>
	<script>
	function wmReload()
	{
		location.replace('{$_SERVER["SCRIPT_NAME"]}');
	}
	setTimeout('wmReload()', 5000);
	</script>
EOB;
		flush();
		$apache_path = dirname($httpd);
		shell_exec("/usr/bin/sudo {$apache_path}/apachectl restart");
	}
	else
		echo <<< EOB
	<table width="100%" height="80%">
	<tr><td align="center"><img src="/theme/default/academic/complete.gif"></td></tr>
	</table>
EOB;
}
else
	die('Can not detect the httpd path.');
?>
