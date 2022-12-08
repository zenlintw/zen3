<?php
/* $Id: db_import.php,v 1.1 2010/02/24 02:38:20 saly Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:

require_once('./libraries/common.lib.php');

/**
 * Gets tables informations and displays top links
 */
require('./libraries/db_common.inc.php');
require('./libraries/db_info.inc.php');

$import_type = 'database';
require('./libraries/display_import.lib.php');

/**
 * Displays the footer
 */
require('./libraries/footer.inc.php');
?>

