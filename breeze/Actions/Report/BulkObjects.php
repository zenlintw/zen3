<?php
require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class BulkObjects extends Action
{
	var $result = array();
	function BulkObjects($sess)
	{
		parent::action('report-bulk-objects', $sess);
	}
}

?>