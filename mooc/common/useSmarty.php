<?php
// put full path to Smarty.class.php
define('moocRoot', dirname(dirname(__FILE__)));
require(moocRoot . '/smarty/libs/Smarty.class.php');

$smarty = new Smarty();
$smarty->template_dir  = moocRoot . '/smarty/templates';
$smarty->compile_dir   = moocRoot . '/smarty/templates_c';
$smarty->cache_dir     = moocRoot . '/smarty/cache';
$smarty->config_dir    = moocRoot . '/smarty/configs';
$smarty->plugins_dir[] = moocRoot . '/smarty/plugins';