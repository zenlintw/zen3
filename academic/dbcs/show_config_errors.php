<?php
/* $Id: show_config_errors.php,v 1.1 2010/02/24 02:38:22 saly Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:

/* Simple wrapper just to enable error reporting and include config */

echo "Starting to parse config file...\n";

error_reporting(E_ALL);
require('./config.inc.php');

?>
