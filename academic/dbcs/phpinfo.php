<?php
/* $Id: phpinfo.php,v 1.1 2010/02/24 02:38:21 saly Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:


/**
 * Gets core libraries and defines some variables
 */
define( 'PMA_MINIMUM_COMMON', true );
require_once('./libraries/common.lib.php');


/**
 * Displays PHP information
 */
if ( $GLOBALS['cfg']['ShowPhpInfo'] ) {
    phpinfo();
}
?>
