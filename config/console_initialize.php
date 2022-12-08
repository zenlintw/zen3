<?php
	$isConsole = (empty($_SERVER['SERVER_PROTOCOL']) && $_SERVER['PHP_SELF'] == $_SERVER['argv'][0]);

	if (!$isConsole) die('just for console.');

	$_SERVER['DOCUMENT_ROOT']        = dirname(dirname(__FILE__));
	$_SERVER['HTTP_HOST']            = `hostname`;
	$_SERVER['PHP_SELF']             = preg_replace("|^{$_SERVER['DOCUMENT_ROOT']}|", '', $_ENV['PWD'] . DIRECTORY_SEPARATOR . $_SERVER['PHP_SELF']);
	$_SERVER['REMOTE_ADDR']          = '127.0.0.1';
	$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'zh-tw';
	$_SERVER['REQUEST_URI']          = $_SERVER['PHP_SELF'];

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');