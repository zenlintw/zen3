<?php
/* $Id: ndbcluster.lib.php,v 1.1 2010/02/24 02:38:27 saly Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:

class PMA_StorageEngine_ndbcluster extends PMA_StorageEngine
{
    /**
     * @return  array
     */
    function getVariables()
    {
        return array(
            'ndb_connectstring' => array(
            ),
         );
    }

    /**
     * @return  string  SQL query LIKE pattern
     */
    function getVariablesLikePattern()
    {
        return 'ndb\\_%';
    }

    /**
     * returns string with filename for the MySQL helppage
     * about this storage engne
     *
     * @return  string  mysql helppage filename
     */
    function getMysqlHelpPage()
    {
        return 'ndbcluster';
    }
}

?>
