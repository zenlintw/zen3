<?php
/* $Id: memory.lib.php,v 1.1 2010/02/24 02:38:27 saly Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:
/**
 * the MEMORY (HEAP) storage engine
 */
class PMA_StorageEngine_memory extends PMA_StorageEngine
{
    /**
     * returns array with variable names dedicated to MyISAM storage engine
     *
     * @return  array   variable names
     */
    function getVariables()
    {
        return array(
            'max_heap_table_size' => array(
                'type'  => PMA_ENGINE_DETAILS_TYPE_SIZE,
            ),
        );
    }
}

?>
