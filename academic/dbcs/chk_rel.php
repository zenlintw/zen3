<?php
/* $Id: chk_rel.php,v 1.1 2010/02/24 02:38:20 saly Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:


/**
 * Gets some core libraries
 */
require_once('./libraries/common.lib.php');
require_once('./libraries/db_common.inc.php');
require_once('./libraries/relation.lib.php');


/**
 * Gets the relation settings
 */
$cfgRelation = PMA_getRelationsParam(TRUE);


/**
 * Displays the footer
 */
require_once('./libraries/footer.inc.php');
?>
